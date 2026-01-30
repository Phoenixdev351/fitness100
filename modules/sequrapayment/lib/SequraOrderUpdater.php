<?php

/*
*  @author SeQura <info@sequra.es>
*  @copyright  SeQura Worldwide S.L.
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class SequraOrderUpdater
{
    private $order;
    private $suborders;
    private $suborder_ids;
    private $client;
    private $sequra;

    public function __construct($module, $order_id)
    {
        $this->module       = $module;
        $this->order        = new Order($order_id);
        $this->suborders    = array($this->order);
        $this->suborder_ids = array();
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $this->suborders = Order::getByReference($this->order->reference);
        }
        foreach ($this->suborders as $order) {
            $this->suborder_ids[] = (int)$order->id;
        }
    }

    /**
     * Call this method to get singleton
     *
     * @return SequraOrderUpdater
     */
    public static function getInstance($module, $order_id = null)
    {
        static $inst = null;
        if ($inst === null) {
            $inst = new SequraOrderUpdater($module, $order_id);
        }

        return $inst;
    }

    public function orderHasBeenInformedToSequra()
    {
        $sql = 'SELECT `order_id` FROM `' . _DB_PREFIX_ . 'sequra_order`' .
               'WHERE `order_id` = ' . (int)$this->order->id . ' ' .
               'AND `sent_to_sequra` = 1';
        Db::getInstance()->execute($sql);

        return Db::getInstance()->numRows() > 0;
    }

    public function someSubOrderHasBeenInformedToSequra()
    {
        $sql = 'SELECT `order_id` FROM `' . _DB_PREFIX_ . 'sequra_order`' .
               'WHERE `order_id` in (' . implode(',', $this->suborder_ids) . ') ' .
               'AND `sent_to_sequra` = 1';
        Db::getInstance()->execute($sql);

        return Db::getInstance()->numRows() > 0;
    }

    public function orderUpdateIfNeeded()
    {
        if (! $this->checkIfPreconditionsAreMet()) {
            return;
        }
        if ($this->updateWithSequra()) {
            SequraReporter::addMessageToOrder(
                array($this->order->id)
            );
        }
    }

    public function checkIfPreconditionsAreMet()
    {
        if (
            $this->wasPaidWithSequra() &&
            !$this->orderHasBeenInformedToSequra() &&
            $this->someSubOrderHasBeenInformedToSequra()
        ) {
            return true;
        }

        return false;
    }

    protected function wasPaidWithSequra()
    {
        $sql = 'SELECT order_id FROM ' . _DB_PREFIX_ . 'sequra_order WHERE `order_id` in (' . implode(
            ',',
            $this->suborder_ids
        ) . ') ';
        Db::getInstance()->execute($sql);

        return Db::getInstance()->numRows() > 0;
    }
    
    protected function getMerchantIdUsed()
    {
        $sql   = 'SELECT merchant_id FROM ' . _DB_PREFIX_ . 'sequra_order WHERE order_id = '.(int)$this->order->id.' LIMIT 1';
        return Db::getInstance()->getValue($sql);
    }

    public function updateWithSequra()
    {
        $this->prepareSequra();

        $this->client->orderUpdate($this->sequra_order);
        if ($this->client->succeeded()) {
            return true;
        } else {
            //@todo add some comment to the order or log
        }

        return false;
    }

    public function prepareSequra()
    {
        $this->client       = $this->getSequraCore()->getClient();
        $pm                 = Module::getInstanceByName($this->order->module);
        $builder            = new SequraReportBuilder($this->getMerchantIdUsed(), array($this->order), array());
        $this->sequra_order = $builder->buildSingleOrder($this->order);
    }

    protected function getSequraCore()
    {
        if (is_null($this->sequra)) {
            $sequra_class = ucfirst(SEQURA_CORE);
            $this->sequra = new $sequra_class();
        }

        return $this->sequra;
    }

    protected function emptyCart()
    {
        unset($this->sequra_order['cart']);
        $this->sequra_order['shipped_cart'] =
        $this->sequra_order['unshipped_cart'] = array(
            'items'                   => array(),
            'order_total_without_tax' => 0,
            'order_total_with_tax'    => 0,
            'currency'                => 'EUR'
        );
    }
}

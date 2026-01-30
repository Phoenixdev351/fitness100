<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class SequraReporter
{
    const SEQURA_DR_SENT_TXT = "Se ha informado a SeQura sobre el envío realizado.";

    public function __construct($module)
    {
        $this->module = $module;
    }

    public function registerSequraOrder($order_id, $merchant_id)
    {
        $sql = 'INSERT IGNORE INTO ' . _DB_PREFIX_ .
            'sequra_order (`order_id`, `sent_to_sequra`, `merchant_id`) VALUES '.
            '(' . $order_id . ', 0, "' . $merchant_id . '")';
        Db::getInstance()->execute($sql);
    }

    public function submitDailyReport()
    {
        Configuration::updateValue('SEQURA_REPORT_ERROR', 'Not sent yet');
        $reports = array_map(
            array($this,'submitDailyReportForMerchant'),
            $this->merchantIdsToReport()
        );
        return $reports;
    }

    public function submitDailyReportForMerchant($merchant_id)
    {   
        if(!$merchant_id){
            $merchant_id = Configuration::get('SEQURA_MERCHANT_ID'); 
        }
        $orders = $this->ordersToReport($this->orderIdsToReport($merchant_id));
        $stats = $this->statsToReport(7);
        $builder = $this->getReportBuilder($merchant_id, $orders, $stats);
        $report = $builder->build();
        $client = $this->module->getClient();
        $client->sendDeliveryReport($report);
        if ($client->succeeded()) {
            $this->dequeueOrders($builder->getReportOrderIds());
            $this->addMessageToOrder($builder->getReportOrderIds());
            Configuration::updateValue('SEQURA_REPORT_ERROR', '');
        } else {
            Configuration::updateValue('SEQURA_REPORT_ERROR', 'Faulty report: ' . print_r($client->getJson(), true));
        }
        Configuration::updateGlobalValue('SEQURA_AUTOCRON_NEXT', SequraCrontab::calcNextExecutionTime());

        return $report;
    }

    public function ordersToReport($order_ids)
    {
        $objects = array();
        $id_shop = (int)Context::getContext()->shop->id;
        foreach ($order_ids as $id) {
            $object = new Order((int)$id);
            if ($object->id && $this->orderOrRelatedOrderHasBeenShipped($object)) {
                if ($id_shop == $object->id_shop || version_compare(_PS_VERSION_, '1.5', '<')) {
                    $objects[] = $object;
                }
            }
        }

        return $objects;
    }

    private function orderOrRelatedOrderHasBeenShipped($primary_order)
    {
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            return $primary_order->hasBeenShipped();
        }

        $orders = Order::getByReference($primary_order->reference);
        foreach ($orders as $order) {
            if ($order->hasBeenShipped()) {
                return true;
            }
        }

        return false;
    }

    public function merchantIdsToReport()
    {
        $sql = 'SELECT distinct merchant_id FROM ' . _DB_PREFIX_ . 'sequra_order WHERE sent_to_sequra = 0';
        $assoc = Db::getInstance()->executeS($sql);
        if(count($assoc) == 0){
            return array(Configuration::get('SEQURA_MERCHANT_ID_ES'));
        }
        foreach ($assoc as $row) {
            $res[] = $row['merchant_id']?$row['merchant_id']:Configuration::get('SEQURA_MERCHANT_ID_ES');
        }

        return $res;
    }

    public function orderIdsToReport($merchant_id)
    {
        $sql = 'SELECT order_id FROM ' . _DB_PREFIX_ . 'sequra_order WHERE sent_to_sequra = 0 and ';
        if($merchant_id==Configuration::get('SEQURA_MERCHANT_ID_ES')){ //FIXME: fix for bad deploy could be removed in some weeks
            $sql .= ' merchant_id in (null,0,"","'.addslashes($merchant_id).'")';
        } else {
            $sql .= 'merchant_id="'.addslashes($merchant_id).'"';
        }
        $assoc = Db::getInstance()->executeS($sql);
        $res = array();
        foreach ($assoc as $row) {
            $res[] = $row['order_id'];
        }

        return $res;
    }

    public function statsToReport($days)
    {
        $sql = 'SELECT id_order FROM ' . _DB_PREFIX_ . 'orders WHERE date_add > DATE_SUB(NOW(), INTERVAL ' . $days . ' day)';
        $assoc = Db::getInstance()->executeS($sql);
        $res = array();
        foreach ($assoc as $row) {
            $res[] = new Order($row['id_order']);
        }

        return $res;
    }

    private function dequeueOrders($order_ids)
    {
        if (empty($order_ids)) {
            return;
        }
        $sql = 'UPDATE ' . _DB_PREFIX_ . 'sequra_order SET sent_to_sequra = 1 WHERE order_id IN ('
            . join($order_ids, ',') . ')';

        return Db::getInstance()->execute($sql);
    }

    public static function addMessageToOrder($order_ids)
    {
        foreach ($order_ids as $id) {
            $order = new Order((int)$id);
            if (version_compare(_PS_VERSION_, '1.7', '<')) {
                self::addPrivateMessage($order);
            } else {
                self::addPrivateMessage17($order);
            }
        }
    }

    private static function addPrivateMessage($order)
    {
        $message = new Message();
        $message->message = htmlentities(self::SEQURA_DR_SENT_TXT, ENT_COMPAT, 'UTF-8');
        $message->id_order = (int)$order->id;
        $message->private = 1;
        if (!$message->add()) {
            echo 'An error occurred while sending message.' .(int)$order->id;
        }
    }

    private static function addPrivateMessage17($order)
    {
        // Add this message in the customer thread
        $customer_thread = new CustomerThread();
        $customer_thread->id_contact = 0;
        $customer_thread->id_customer = (int)$order->id_customer;
        $customer_thread->id_shop = (int)$order->id_shop;
        $customer_thread->id_order = (int)$order->id;
        $customer_thread->id_lang = (int)$order->id_lang;
        //$customer_thread->email = 'clientes@sequra.es';
        $customer_thread->status = 'closed';
        $customer_thread->token = Tools::passwdGen(12);
        $customer_thread->add();

        $customer_message = new CustomerMessage();
        $customer_message->id_customer_thread = $customer_thread->id;
        $customer_message->id_employee = 0;
        $customer_message->message = self::SEQURA_DR_SENT_TXT;
        $customer_message->private = 1;

        if (!$customer_message->add()) {
            $this->errors[] = $this->trans('An error occurred while saving message', array(), 'Admin.Payment.Notification');
        }
    }

    public function getReportBuilder($merchant_id, $orders, $stats)
    {
        return new SequraReportBuilder($merchant_id, $orders, $stats);
    }
}

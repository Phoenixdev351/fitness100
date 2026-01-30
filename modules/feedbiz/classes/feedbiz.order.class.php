<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Feed.Biz, Ltd.
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Feed.Biz, Ltd. is strictly forbidden.
 * In order to obtain a license, please contact us: contact@feed.biz
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Feed.Biz, Ltd.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Feed.Biz, Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Feed.Biz, Ltd. a l'adresse: contact@feed.biz
 * ...........................................................................
 *
 * @author    Olivier B.
 * @copyright Copyright (c) 2011-2022 Feed.Biz - Hong Kong - Head Quarters - Room 1408, 14/F,
 *            Tak Shing House - Theatre Lane - 20 des Voeux Road Central, -Central, Hong Kong
 * @license   Commercial license
 * Support by mail  :  support@feed.biz
 */

/**
 * Class FeedBizOrder
 */
class FeedBizOrder extends Order
{
    /**
     * @var null
     */
    public $Shipping = null;
    /**
     * @var null
     */
    public $Items = null;
    /**
     * @var array
     */
    public $Feedbiz = array();
    const PENDING = 1;
    const UNSHIPPED = 2;
    const PARTIALLYSHIPPED = 3;
    const SHIPPED = 4;
    const CANCELED = 5;
    const CHECKED = 6;  // Checked by the module for "status.php" automaton
    const TO_CANCEL = 7;
    const PROCESS_CANCEL = 8;
    const REVERT_CANCEL = 9;
    const DEFAULT_PERIOD_IN_DAYS = 15;

    /**
     * FeedBizOrder constructor.
     *
     * @param null $id
     * @param null $id_lang
     */
    public function __construct($id = null, $id_lang = null)
    {
        parent::__construct($id, $id_lang);

        if ($id) {
            $this->getMpFields();
        }
    }

    /**
     * @param $OrderId
     * @param bool|false $debug
     * @return bool
     */
    public static function checkByMpId($OrderId, $debug = false)
    {
        /* prevent duplicate imports for Prestashop */
        if (FeedbizTools::fieldExists(_DB_PREFIX_.'orders', 'mp_order_id')) {
            $sql = 'SELECT `id_order`, `mp_order_id` FROM `'._DB_PREFIX_.'orders`
			where `mp_order_id` = "'.pSQL($OrderId).'" ORDER BY `id_order` DESC ;';


            $result = Db::getInstance()->executeS($sql, true, false);
            if ($debug) {
                echo "<pre>";
                printf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
                echo "</pre>";
                echo "checkByMpId table "._DB_PREFIX_."orders \n<br/>";
                echo $sql."\n<br/>";
                print_r($result);
                echo "---------------------------------------------------------------- \n<br/>";
            }
            if (is_array($result) && count($result)) {
                return ($result[0]);
            }
        }

        /* prevent duplicate imports with other module for Prestashop */
        if (FeedbizTools::tableExists(_DB_PREFIX_.Feedbiz::TABLE_MARKETPLACE_ORDERS)) {
            if (FeedbizTools::fieldExists(_DB_PREFIX_.Feedbiz::TABLE_MARKETPLACE_ORDERS, 'mp_order_id')) {
                $sql = 'SELECT `id_order`, `mp_order_id` FROM `'._DB_PREFIX_.Feedbiz::TABLE_MARKETPLACE_ORDERS.'`
                WHERE `mp_order_id` = "'.pSQL($OrderId).'" ;';



                $result = Db::getInstance()->executeS($sql, true, false);
                if ($debug) {
                    echo "<pre>";
                    printf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
                    echo "</pre>";
                    echo "checkByMpId table "._DB_PREFIX_.Feedbiz::TABLE_MARKETPLACE_ORDERS." \n<br/>";
                    echo $sql."\n<br/>";
                    print_r($result);
                    echo "---------------------------------------------------------------- \n<br/>";
                }
                if (is_array($result) && count($result)) {
                    return ($result[0]);
                }
            }
        }

        /* prevent duplicate imports with other module for Prestashop */
        $exception_marketplace = array('amazon', 'ebay'); // except Amazon, eBay : check by marketplace_orders.
        $marketplace_tab = Configuration::get('FEEDBIZ_MARKETPLACE_TAB');

        if ($marketplace_tab) {
            $marketplace_tab_config = unserialize($marketplace_tab);

            foreach (array_keys($marketplace_tab_config) as $marketplace) {
                if (!in_array($marketplace, $exception_marketplace)) {
                    $branded_module = Tools::strtolower($marketplace);
                    $marketplace_table = _DB_PREFIX_.$branded_module.'_orders';

                    if (FeedbizTools::tableExists($marketplace_table) && FeedbizTools::fieldExists($marketplace_table, 'mp_order_id')) {
                        $sql = 'SELECT `id_order`, `mp_order_id` FROM `'.$marketplace_table.'`
                        WHERE `mp_order_id` = "'.pSQL($OrderId).'" ;';



                        $result = Db::getInstance()->executeS($sql, true, false);
                        if ($debug) {
                            echo "<pre>";
                            printf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
                            echo "</pre>";
                            echo "checkByMpId table ".$marketplace_table." \n<br/>";
                            echo $sql."\n<br/>";
                            print_r($result);
                            echo "---------------------------------------------------------------- \n<br/>";
                        }

                        if (is_array($result) && count($result)) {
                            return ($result[0]);
                        }
                    }
                }
            }
        }

        /* prevent duplicate imports with Feed.biz module for Prestashop */
        $sql = 'SELECT `id_order`, `mp_order_id`, `mp_reference` FROM `'._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_ORDERS.'`
            WHERE `mp_reference` = "'.pSQL($OrderId).'" ;';


        $result = Db::getInstance()->executeS($sql);
        if ($debug) {
            echo "<pre>";
            printf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
            echo "</pre>";
            echo "checkByMpId \n<br/>";
            echo $sql."\n<br/>";
            print_r($result);
            echo "---------------------------------------------------------------- \n<br/>";
        }

        if (!is_array($result) || !count($result)) {
            return (false);
        } else {
            return ($result [0]);
        }
    }

    /**
     * @param $OrderId
     * @param bool|false $debug
     * @return bool
     */
    public static function checkByOrderId($OrderId, $debug = false)
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_ORDERS.'` WHERE id_order = "'.pSQL($OrderId).'"';
        $result = Db::getInstance()->executeS($sql);
        if ($debug) {
            echo "<pre>";
            printf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
            echo "</pre>";
            echo "checkByMpId \n<br/>";
            echo $sql."\n<br/>";
            print_r($result);
            echo "---------------------------------------------------------------- \n<br/>";
        }
        if (!($result)) {
            return (false);
        } else {
            return ($result [0]);
        }
    }

    /**
     * @param $SellerOrderId
     * @param bool|false $debug
     * @return bool
     */
    public static function checkBySellerOrderId($SellerOrderId, $debug = false)
    {
        if (FeedbizTools::fieldExists(_DB_PREFIX_.'orders', 'id_order')) {
            $sql = 'SELECT `id_order` FROM `'._DB_PREFIX_.'orders` WHERE `id_order` = "'.pSQL($SellerOrderId).'" ORDER BY `id_order` DESC ;';

            $result = Db::getInstance()->executeS($sql, true, false);
            if ($debug) {
                echo "<pre>";
                printf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
                echo "</pre>";
                echo "checkBySellerOrderId table "._DB_PREFIX_."orders \n<br/>";
                echo $sql."\n<br/>";
                print_r($result);
                echo "---------------------------------------------------------------- \n<br/>";
            }

            if (is_array($result) && count($result)) {
                return ($result[0]);
            }
        }

        return (false);
    }

    /**
     * @return bool
     */
    private function getMpFields()
    {
        $sql = 'SELECT id_order, mp_order_id, channel_id, channel_name, mp_reference, mp_number, mp_status,
            multichannel FROM `'._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_ORDERS.'` where `id_order` = '.(int)$this->id;

        $result = Db::getInstance()->getRow($sql);
        if (is_array($result) && count($result)) {
            $this->Feedbiz ['id_order'] = $result ['id_order'];
            $this->Feedbiz ['mp_order_id'] = $result ['mp_order_id'];
            $this->Feedbiz ['channel_id'] = $result ['channel_id'];
            $this->Feedbiz ['channel_name'] = $result ['channel_name'];
            $this->Feedbiz ['mp_reference'] = $result ['mp_reference'];
            $this->Feedbiz ['mp_number'] = $result ['mp_number'];
            $this->Feedbiz ['mp_status'] = $result ['mp_status'];
            $this->Feedbiz ['multichannel'] = $result ['multichannel'];

            return (true);
        } else {
            return (false);
        }
    }

    /**
     * @param $id_order
     * @return mixed
     */
    public static function orderedItems($id_order)
    {
        $sql = 'SELECT od.product_reference as SKU, od.product_quantity as Quantity FROM `'.
            _DB_PREFIX_.'order_detail` od LEFT JOIN `'._DB_PREFIX_.'product` p on (p.id_product = od.product_id)
            WHERE od.id_order = '.(int)$id_order;

        return (Db::getInstance()->executeS($sql));
    }

    /**
     * @param $id_order_state
     * @param bool|false $debug
     * @return bool
     */
    public static function getOrdersByState($id_order_state, $debug = false)
    {
        $sql = 'SELECT o.`id_order`, o.`id_lang`, fo.`mp_reference` as mp_order_id, o.`id_carrier`, cl.`name` as
            name_carrier, o.`shipping_number`, oh.`date_add` as shipping_date
            FROM `'._DB_PREFIX_.'orders` o
            LEFT JOIN `'._DB_PREFIX_.'order_history` oh ON (o.`id_order` = oh.`id_order`)
            LEFT JOIN `'._DB_PREFIX_.'carrier` cl ON (o.`id_carrier` = cl.`id_carrier`)
            LEFT JOIN `'._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_ORDERS.'` fo ON (o.`id_order` = fo.`id_order`)
            WHERE oh.`id_order_state` = '.(int)$id_order_state.'
            AND o.`date_add` > DATE_ADD(NOW(), INTERVAL -'.self::DEFAULT_PERIOD_IN_DAYS.' DAY)
            GROUP by o.`id_order` ; ';

        $result = Db::getInstance()->executeS($sql);

        if ($debug) {
            echo '<pre>'.$sql.'</pre><pre>'.print_r($result, true).'</pre>';
        }

        if (!$result) {
            return (false);
        }

        return ($result);
    }

    public function changeOrderStatus($id_order, $status, $reason = null, $debug = false)
    {
        $params = array();

        if (!($order = new FeedBizOrder($id_order))) {
            if ($debug) {
                echo '<pre>';
                printf('%s - %s::%s - line #%d : ', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
                echo 'Unable to load order, id: '.$id_order;
                echo '</pre>';
            }

            return (false);
        }

        if (!Validate::isLoadedObject($order)) {
            if ($debug) {
                echo '<pre>';
                printf('%s - %s::%s - line #%d : ', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
                echo 'Unable to load order, id: '.$id_order;
                echo '</pre>';
            }

            return (false);
        }

        if (!Tools::strlen($order->Feedbiz['mp_order_id'])) {
            if ($debug) {
                echo '<pre>';
                printf('%s - %s::%s - line #%d : ', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
                echo 'Missing marketplace order id: '.$id_order;
                echo '</pre>';
            }

            return (false);
        }

        if ($status == $order->Feedbiz['mp_status']) {
            if ($debug) {
                echo '<pre>';
                printf('%s - %s::%s - line #%d : ', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
                echo 'Order has already the same status: '.$id_order;
                echo '</pre>';
            }

            return (false);
        }
        if ($order->Feedbiz['multichannel'] == 'AFN') {
            return (false);
        }

        $in_array = in_array(
            $status,
            array(
                FeedbizOrder::TO_CANCEL,
                FeedbizOrder::CANCELED,
                FeedbizOrder::PROCESS_CANCEL,
                FeedbizOrder::REVERT_CANCEL
            )
        );
        if (!$in_array) {
            if ($debug) {
                echo '<pre>';
                printf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
                echo 'Wrong status for order id: '.$id_order;
                echo '</pre>';
            }

            return (false);
        }

        $params['id_order'] = $order->Feedbiz['id_order'];

        switch ($status) {
            case FeedbizOrder::TO_CANCEL:
            case FeedbizOrder::CANCELED:
                // Flag the order as to be canceled
                $params['mp_status'] = $status;
                break;

            case FeedbizOrder::PROCESS_CANCEL:
                if (!$reason || empty($reason)) {
                    if ($debug) {
                        echo '<pre>';
                        printf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
                        echo 'Wrong reason for order id: '.$id_order.' reason: '.print_r($reason, true);
                        echo '</pre>';
                    }

                    return (false);
                }
                $params['mp_status'] = $status;
                $params['cancelled_reason'] = $reason;
                break;

            case FeedbizOrder::REVERT_CANCEL:
                $params['mp_status'] = $status;
                $params['cancelled_reason'] = null;
                break;

            default:
                if ($debug) {
                    echo '<pre>';
                    printf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
                    echo 'Wrong status for order id: '.$id_order;
                    echo '</pre>';
                }

                return (false);
        }

        return ($order->addOrderExt($params, $debug));
    }

    /*
     * OrderHistory::changeIdOrderState loads payment_method name by Module::getInstanceByName(payment_module)
     * But payment_method from FeedBizOrder->sales_channel (e.g. amazon.de) is not 100% match with payment_module's name
     * Thus it need to do manual update to database directly to show sales_channel as payment_method on invoice.
     */
    public function updatePaymentMethod($debug = false,$orderID=0)
    {
        // case: ref by reference
        $sql_check_payment = "SELECT count(*) as 'rows' FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = '"._DB_NAME_."'
            AND TABLE_NAME = '"._DB_PREFIX_."order_payment'
            AND COLUMN_NAME IN ('payment_method', 'order_reference')";

        $result = Db::getInstance()->getRow($sql_check_payment);
        if($debug){
            echo sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__)."\n<br>";
            echo $sql_check_payment."\n<br>";
        }
        if ($result ['rows'] == 2) {
            $sql = 'UPDATE `'._DB_PREFIX_.'order_payment` SET `payment_method` = "'.pSQL($this->payment).
                '" WHERE order_reference = "'.pSQL($this->reference).'"';
            if($debug){
                echo sprintf('%s - line #%d',  __FUNCTION__, __LINE__)."\n<br>";
                echo $sql."\n<br>";
            }
            Db::getInstance()->execute(
                $sql 
            );
        } else {
            // case: ref by order id
            $sql_check_payment
                = "SELECT count(*) as 'rows' FROM information_schema.COLUMNS
								WHERE TABLE_SCHEMA = '"._DB_NAME_."'
								AND TABLE_NAME = '"._DB_PREFIX_."order_payment'
								AND COLUMN_NAME IN ('payment_method', 'id_order')";

            $result = Db::getInstance()->getRow($sql_check_payment);
            if($debug){
                echo sprintf('%s - line #%d',  __FUNCTION__, __LINE__)."\n<br>";
                echo $sql_check_payment."\n<br>";
            }
            if ($result ['rows'] == 2) {
                $sql =  'UPDATE `'._DB_PREFIX_.'order_payment` SET `payment_method` = "'.pSQL($this->payment).
                    '" WHERE id_order = '.(int)$this->id;
                if($debug){
                    echo sprintf('%s - line #%d',  __FUNCTION__, __LINE__)."\n<br>";
                    echo $sql."\n<br>";
                }
                Db::getInstance()->execute(
                   $sql
                );
            }
        }
        $oRef ='';
        if(!empty($orderID)){ 
            $sql
                = "SELECT * from "._DB_PREFIX_."orders  where id_order = '".$orderID."'" ;
            $result = Db::getInstance()->executeS($sql, true, false);
            if ($debug) {
                echo "<pre>";
                printf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
                echo "</pre>";
                echo "order table "._DB_PREFIX_."orders \n<br/>";
                echo $sql."\n<br/>";
                print_r($result);
                echo "---------------------------------------------------------------- \n<br/>";
            }
            if (is_array($result) && count($result)) {
                if(!empty($result[0]['reference'])){
                    $oRef = $result[0]['reference'];
                }
            }
        
        
        
        $sql = 
                'Delete `'._DB_PREFIX_.'order_invoice_payment` from '._DB_PREFIX_.'order_invoice_payment  where id_order_payment in (
                    SELECT
                            max( p.id_order_payment ) AS id_order_payment 
                    FROM
                            `'._DB_PREFIX_.'order_payment` p,
                            ( SELECT order_reference FROM `'._DB_PREFIX_.'order_payment` WHERE payment_method = "Feed.biz" ) p2 
                    WHERE
                            p2.order_reference = p.order_reference and p.order_reference = "'.$oRef.'"
                    GROUP BY
                            p.order_reference 
                    HAVING
                            count( p.id_order_payment ) > 1 );';
        if($debug){
                echo sprintf('%s - line #%d',  __FUNCTION__, __LINE__)."\n<br>";
                echo $sql."\n<br>";
            }
            $sql = 
                'Delete `'._DB_PREFIX_.'order_invoice_payment` from '._DB_PREFIX_.'order_invoice_payment  where id_order_payment in (
                    SELECT
                            max( p.id_order_payment ) AS id_order_payment 
                    FROM
                            `'._DB_PREFIX_.'order_payment` p  
                    Where   p.order_reference = "'.$oRef.'"
                    GROUP BY
                            p.order_reference,p.payment_method,p.amount 
                    HAVING
                            count( p.id_order_payment ) > 1 ) and id_order = "'.$orderID.'";';
        if($debug){
                echo sprintf('%s - line #%d',  __FUNCTION__, __LINE__)."\n<br>";
                echo $sql."\n<br>";
            }
        Db::getInstance()->execute( $sql );
        $sql =   'Delete '._DB_PREFIX_.'order_payment from '._DB_PREFIX_.'order_payment  where id_order_payment not in (
                SELECT
                          id_order_payment 
                FROM
                          '._DB_PREFIX_.'order_invoice_payment  where  id_order = "'.$orderID.'"  ) and order_reference = "'.$oRef.'" ;';
        if($debug){
                echo sprintf('%s - line #%d',  __FUNCTION__, __LINE__)."\n<br>";
                echo $sql."\n<br>";
            }
        Db::getInstance()->execute(
              $sql
            );
        }
            
    }

    public function updateEmptyOrderInvoice($order_id, $debug = false)
    {
        Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'orders` as o ,`'._DB_PREFIX_.'order_invoice` as i SET o.invoice_date = o.date_add, o.`invoice_number` = '
                . ' i.id_order_invoice, o.valid = 1 WHERE o.id_order = "'.pSQL($order_id).'" and (o.invoice_number = 0 or o.valid = 0 ) and o.id_order = i.id_order ');
        $sql = "select count(*) as 'rows'  from `"._DB_PREFIX_."orders` as o where o.id_order = '".pSQL($order_id)."' and o.delivery_number = 0 ";
        if ($debug) {
            echo "<br>debug sql fine deliver num=0".__FUNCTION__.' '.__FILE__.' '.__LINE__.' <br>'.$sql;
        }
        $result = Db::getInstance()->getRow($sql);
        if ($result ['rows'] > 0) {
            $order_states = unserialize(Configuration::get('FEEDBIZ_ORDERS_STATES'));
            $id_order_state = $order_states ['FEEDBIZ_CA'];
            $id_employee = (int)Configuration::get('FEEDBIZ_ID_EMPLOYEE');
            if ($debug) {
                echo "<br>Line: ".__LINE__.'  order_states ,  id_order_state, id_employee<br>';
                print_r($order_states);
                var_dump($id_order_state);
                var_dump($id_employee);
            }

            if (!(empty($order_states) || empty($id_employee) || empty($order_id))) {
                $new_history = new FeedBizOrderHistory();
                $new_history->id_order = (int)$order_id;
                $new_history->id_employee = $id_employee ? $id_employee : 1;
                $new_history->changeIdOrderState($id_order_state, $order_id);
                $new_history->addWithOutEmail(true);
            }
        }
    }

    /**
     * FeedBiz_Order::addOrderExt
     * insert MP information into feedbiz_orders
     *
     * @param null $params
     * @param bool|false $debug
     * @return bool|string
     */
    public static function addOrderExt($params = null, $debug = false)
    {
        if (!is_array($params) || !count($params)) {
            return "Empty addOrderExt parameter.";
        }

        if (!FeedbizTools::tableExists(_DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_ORDERS)) {
            return "Table `"._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_ORDERS."` does not exist.";
        }

        // Order Existing Validation
        $existingOrder = FeedBizOrder::checkByMpId((int)$params ['mp_order_id'], $debug);

        if (!isset($existingOrder) || !$existingOrder || empty($existingOrder)) {
            $seller_order_id = isset($params ['id_order']) ? (int)$params ['id_order'] : null ;
            // Check Order Existing by Seller Order ID
            if (isset($seller_order_id) && Tools::strlen($seller_order_id)) {
                $existingOrder = FeedBizOrder::checkByOrderId($seller_order_id, $debug);
            }
        }

        if (FeedbizTools::tableExists(_DB_PREFIX_.Feedbiz::TABLE_MARKETPLACE_ORDERS)) {
            if (FeedbizTools::fieldExists(_DB_PREFIX_.Feedbiz::TABLE_MARKETPLACE_ORDERS, 'mp_order_id')) {
                $seller_order_id = isset($params ['id_order']) ? (int)$params ['id_order'] : null ;
                $mp_order_id = isset($params ['mp_reference']) ? $params ['mp_reference'] : null ;
                $sql = 'SELECT `id_order`, `mp_order_id` FROM `'._DB_PREFIX_.Feedbiz::TABLE_MARKETPLACE_ORDERS.'`
                WHERE `id_order` = "'.pSQL($seller_order_id).'" ;';
                $result = Db::getInstance()->executeS($sql, true, false);

                if (is_array($result) && count($result)) {
                    $update_sql = 'UPDATE `'._DB_PREFIX_.Feedbiz::TABLE_MARKETPLACE_ORDERS.'` SET mp_order_id = "'.pSQL($mp_order_id)
                            .'" WHERE `id_order` = '.(int)$seller_order_id.' ;';
                } else {
                    $update_sql = 'INSERT INTO `'._DB_PREFIX_.Feedbiz::TABLE_MARKETPLACE_ORDERS.
                    '` (`id_order`, `mp_order_id`) VALUES ('. (int)$seller_order_id .',"'.pSQL($mp_order_id).'");';
                }
                if (!$debug) {
                    Db::getInstance()->execute($update_sql);
                } else {
                    echo "addOrderExt add marketplace orders table \n<br/>";
                    echo $update_sql."\n<br/>";
                    echo "---------------------------------------------------------------- \n<br/>";
                }
            }
        }

        $field_exists = FeedbizTools::showColumnExists(_DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_ORDERS);

        if (is_array($existingOrder) && count($existingOrder)) {
            $fields_sql = null;

            foreach ($params as $field => $value) {
                if (array_key_exists($field, $field_exists)) {
                    // skip key : id_order, mp_order_id
                    if ($field == 'id_order' || $field == 'mp_order_id') {
                        continue;
                    }
                    // `mp_reference`, `mp_number` Field string exception
                    if ($field == 'mp_reference' || $field == 'mp_number') {
                        $insert_statement = '"'.pSQL($value).'"';
                    } else {
                        if (is_bool($value)) {
                            $insert_statement = ((bool)$value ? 1 : 0);
                        } elseif (is_float($value)) {
                            $insert_statement = (float)$value;
                        } elseif (is_int($value)) {
                            $insert_statement = (int)$value;
                        } elseif (is_numeric($value)) {
                            $insert_statement = $value;
                        } elseif (empty($value)) {
                            $insert_statement = 'null';
                        } else {
                            $insert_statement = '"'.pSQL($value).'"';
                        }
                    }

                    $fields_sql .= sprintf('`%s`', $field)."=".$insert_statement.", ";
                }
            }

            $fields_sql = rtrim($fields_sql, ', ');

            $update_sql = 'UPDATE `'._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_ORDERS.'` SET '.$fields_sql.
                ' WHERE `id_order` = '.(int)$params['id_order'].' ;';
        } else {
            $fields_sql = $insert_statement = null;

            foreach ($params as $field => $value) {
                if (array_key_exists($field, $field_exists)) {
                    $fields_sql .= sprintf('`%s`, ', pSQL($field));

                    // `mp_reference`, `mp_number`   Field string exception
                    if ($field == 'mp_reference' || $field == 'mp_number') {
                        $insert_statement .=  '"'.pSQL($value).'", ';
                    } else {
                        if (is_bool($value)) {
                            $insert_statement .= ((bool)$value ? 1 : 0).', ';
                        } elseif (is_float($value)) {
                            $insert_statement .= (float)$value.', ';
                        } elseif (is_int($value)) {
                            $insert_statement .= (int)$value.', ';
                        } elseif (is_numeric($value)) {
                            $insert_statement .= $value.', ';
                        } elseif (empty($value)) {
                            $insert_statement .= 'null, ';
                        } else {
                            $insert_statement .= '"'.pSQL($value).'", ';
                        }
                    }
                }
            }

            $insert_statement = rtrim($insert_statement, ', ');
            $fields_sql = rtrim($fields_sql, ', ');

            $update_sql = 'INSERT INTO `'._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_ORDERS.
                '` ('.$fields_sql.') VALUES ('.$insert_statement.');';
        }

        if (!$debug) {
            if (!Db::getInstance()->execute($update_sql)) {
                return ('Error : '.$update_sql);
            }

            return true;
//            "Record in `"._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_ORDERS."` has been created already.".
//                print_r($existingOrder, true);
        } else {
            echo "addOrderExt \n<br/>";
            echo $update_sql."\n<br/>";
            echo "---------------------------------------------------------------- \n<br/>";

            return "Debug Record in `"._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_ORDERS."` has been created already.";
        }
    }

    /**
     * FeedBiz_Order::getOrderExt
     * get MP information from feedbiz_orders
     *
     * @param $id_order
     * @return bool|mixed
     */
    public static function getOrderExt($id_order)
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_ORDERS.'`
          where `id_order` = '.(int)$id_order.' LIMIT 1 ;';

        $result = Db::getInstance()->executeS($sql);
        if (is_array($result) && count($result)) {
            return array_shift($result);
        } else {
            return (false);
        }
    }

    /**
     * @param $id_order
     * @return int|null
     */
    public static function getShippingNumber($id_order)
    {
        if (version_compare(_PS_VERSION_, '1.5', '>')) {
            $id_order_carrier = Db::getInstance()->getValue(
                'SELECT `id_order_carrier` FROM `'._DB_PREFIX_.'order_carrier` WHERE `id_order` = '.(int)$id_order
            );

            if ($id_order_carrier) {
                $order_carrier = new OrderCarrier($id_order_carrier);

                if (Validate::isLoadedObject($order_carrier)) {
                    if (!empty($order_carrier->tracking_number)) {
                        return ($order_carrier->tracking_number);
                    }
                }
            }
        }

        return (null);
    }
}

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
 * @copyright Copyright (c) 2011-2022 Feed.Biz - Hong Kong - Head Quarters - Room 1408, 14/F, Tak Shing House - Theatre
 *     Lane - 20 des Voeux Road Central, -Central, Hong Kong
 * @license   Commercial license
 * Support by mail  :  support@feed.biz
 */

require_once(dirname(__FILE__).'/env.php');
require_once(dirname(__FILE__).'/../feedbiz.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.tools.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.webservice.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.context.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.product.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.address.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.cart.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.order.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.orderhistory.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.payment.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.tax.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.log.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.pickupcarrier.class.php');
require_once(dirname(__FILE__).'/../common/tools.class.php');

/**
 * Class FeedBizOrdersImport
 */
class FeedBizOrdersImport extends Feedbiz
{
    /** @var array */
    private $errors = array();
    /** @var int */
    private $fborderID;
    /** @var string Feed.biz auth */
    private $username;
    /** @var */
    private $token;
    /** @var bool */
    private $debug;
    /** @var */
    private $preproduction;
    /** @var array */
    private $return_data = array();
    /** @var string */
    private $status_code;
    /** @var string */
    private $status;
    /** @var bool */
    private $forceimport = false;
    /** @var Cart */
    private $cart;

    /**
     * FeedBizOrdersImport constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->forceimport = (int)Configuration::get('FEEDBIZ_FORCEIMPORT') ? true : false;

        register_shutdown_function(array(
            $this,
            'FBShutdowFunction'
        ));
//        ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
        ob_start();
        Feedbiz::$debug_mode = $this->debug = (bool)Configuration::get('FEEDBIZ_DEBUG') || Tools::getValue('debug');
        if (version_compare(_PS_VERSION_, '1.5', '>')) {
            $employee = null;
            $id_employee = (int)Configuration::get('FEEDBIZ_ID_EMPLOYEE');
            $id_employee = empty($id_employee) ? 1 : $id_employee;
            if ($id_employee) {
                $employee = new Employee($id_employee);
            }

            if (!Validate::isLoadedObject($employee)) {
                $this->errors [] = array(
                    'file' => basename(__FILE__),
                    'line' => __LINE__,
                    'message' => sprintf($this->l('Wrong Employee, please save the module configuration'))
                );
                die();
            }

            $this->context = Context::getContext();
            $this->context->customer->is_guest = true;
            $this->context->customer->id_default_group = (int)Configuration::get('FEEDBIZ_CUSTOMER_GROUP');

            $this->context->cart = new Cart();
            $this->context->employee = $employee;
            $this->context->currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
            FeedbizContext::restore($this->context);
            if (version_compare(_PS_VERSION_, '1.7.6', '>=') && empty($this->context->currentLocale)) {
                if (class_exists('\PrestaShop\PrestaShop\Adapter\ContainerBuilder')
                  &&  method_exists('\PrestaShop\PrestaShop\Adapter\ContainerBuilder', 'getContainer')) {
                    $container = \PrestaShop\PrestaShop\Adapter\ContainerBuilder::getContainer('front', _PS_MODE_DEV_);
                    $localeRepository = $container->get(Tools::SERVICE_LOCALE_REPOSITORY);
                    $this->context->currentLocale = $localeRepository->getLocale(
                        $this->context->language->getLocale()
                    );
                }
//                $this->context->currentLocale =  'en-US' ;
//                $this->context->currentLocale = Tools::getContextLocale($this->context);
//                if ($this->debug) {
//                    echo 'getcontextLocale :';
//                    var_dump($this->context->currentLocale);
//                            echo "<br>\n";
//                }
            }
        }



        $this->status_code = '0';
        $this->status = $this->l('Fail');



        FeedbizTools::getConfig($this->debug);
        if ($this->debug) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        }
    }

    /**
     * @throws PrestaShopException
     */
    public function dispatch()
    {
        FeedbizTools::securityCheck();

        $fborder = Tools::getValue('fborder');
        $otp = Tools::getValue('otp');

        // Check Access Tokens
        $this->token = Configuration::get('FEEDBIZ_TOKEN');
        $this->preproduction = Configuration::get('FEEDBIZ_PREPRODUCTION') ? true : false;

        if ($otp) {
            $otp = md5(md5($otp));
            $FeedBizWS = new FeedBizWebService($this->username, $this->token, $this->preproduction, $this->debug);

            $params = array(
                'token' => $this->token,
                'id_order' => $fborder,
                'otp' => $otp
            );

            $order = $FeedBizWS->getOrder($params, 'getOrders', 'GET', true, $this->debug);

            if (isset($order->Order)) {
                $this->_importOrder($order->Order);
            }

            if ($this->return_data) {
                $this->status_code = '1';
                $this->status = $this->l('Success');
            } else {
                //delete cart if import fails.
                if (Validate::isLoadedObject($this->cart)) {
                    $this->cart->delete();
                }

                $outputMessages = array();
                foreach ($this->errors as $errorEle) {
                    $outputMessages [] = $errorEle ['message'];
                }

                $subject = sprintf(
                    'Feed.biz Can\'t import Feed.biz order%s.',
                    (empty($this->fborderID) ? '' : '#'.$this->fborderID)
                );
                array_unshift($outputMessages, $subject);

                if (method_exists('CustomerThread', 'getCustomerMessages')) {
                    // crc32 instead of md5 as the token length is 12 chars.
                    $thread_identifier = sprintf('%u', crc32($subject));

                    $customer_messages = CustomerThread::getCustomerMessages(
                        (int)Configuration::get('FEEDBIZ_CUSTOMER_ID')
                    );

                    $pass = true;

                    if (is_array($customer_messages) && count($customer_messages)) {
                        foreach ($customer_messages as $customer_message) {
                            if ($customer_message['token'] == $thread_identifier) {
                                $pass = false;
                            }
                        }
                    }

                    if ($pass) {
                        $customer_thread = new CustomerThread();
                        $customer_thread->id_contact = 0;
                        $customer_thread->id_customer = (int)Configuration::get('FEEDBIZ_CUSTOMER_ID');
                        $customer_thread->id_shop = (int)$this->context->shop->id;
                        $customer_thread->id_order = $fborder;
                        $customer_thread->id_lang = $this->id_lang;
                        $customer_thread->email = 'no-reply@feedbiz.com';
                        $customer_thread->status = 'open';
                        $customer_thread->token = $thread_identifier;
                        $customer_thread->add();

                        $customer_message = new CustomerMessage();
                        $customer_message->id_customer_thread = $customer_thread->id;
                        $customer_message->id_employee = (int)Configuration::get('FEEDBIZ_ID_EMPLOYEE');
                        $customer_message->message = implode("\n", $outputMessages);
                        $customer_message->private = 0;
                        $customer_message->add();
                    }
                }
            }
        } else {
            $this->status_code = '-1';
            $this->status = $this->l('Access denied ');
        }
        // Look register_shutdown_function
    }

    /**
     * @param $order
     * @return array|bool
     * @throws PrestaShopException
     */
    private function _importOrder($order)
    {
        if (method_exists('Address', 'initialize')) {
            Address::initialize();
        }

        $validation_message = '' ;
        $stock_management = (bool)Configuration::get('PS_STOCK_MANAGEMENT');
        $use_taxes = Configuration::get('FEEDBIZ_USE_TAXES') ? true : false;
        $ps_invoice_prefix = Configuration::get('PS_INVOICE_PREFIX');

        // Import unknown products as a new product
        $auto_create = (bool)Configuration::get('FEEDBIZ_AUTO_CREATE');
        $is_afn_order = false;

        $order_states = unserialize(Configuration::get('FEEDBIZ_ORDERS_STATES'));
        $order_state = $order_states ['FEEDBIZ_CA']; // commande acceptee
        if ($this->debug) {
            echo "-----------------------LINE : ".__LINE__."----------------------------\n<br/>";
            echo '<pre>';
            $results = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'configuration` WHERE  `name` LIKE "FEEDBIZ_%"');
            print_r($results);
            echo '</pre>';
        }
        if (empty($order_state) && !empty(FeedbizTools::$feed_config['FEEDBIZ_ORDERS_STATES'])) {
            $order_states = unserialize(FeedbizTools::$feed_config['FEEDBIZ_ORDERS_STATES']);
            $order_state = $order_states ['FEEDBIZ_CA']; // commande acceptee
            if ($this->debug) {
                echo "-----------------------LINE : ".__LINE__."----------------------------\n<br/>";
                echo '<pre>';
                print_r($order_states);
                print_r($order_state);
                echo '</pre>';
            }
        }
        if (!isset($order_state) || empty($order_state) || (int)$order_state <= 0) {
            $this->errors [] = array(
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'message' => sprintf($this->l('Please choose the default order state for new incoming orders in module configuration'))
            );

            return (false);
        }
        $catalog_mode = (bool)Configuration::get('PS_CATALOG_MODE');

        if ($catalog_mode) {
            $this->errors [] = array(
            'file' => basename(__FILE__),
            'line' => __LINE__,
            'message' => sprintf($this->l('Importing orders is impossible because your shop is in catalog mode'))
            );

            return (false);
        }
        $id_shop_group = 1;
        $id_shop = 1;
        $id_warehouse = 0;

        $this->cart = new FeedBizCart();

        // debug
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $id_shop_group = $this->context->shop->id_shop_group;
            $id_shop = $this->context->shop->id;
            $id_warehouse = (int)Configuration::get('FEEDBIZ_WAREHOUSE');
        }

        // Configuration customer Group or default customer group

        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $id_default_customer_group = Configuration::get('PS_CUSTOMER_GROUP');
        } else {
            $id_default_customer_group = (int)_PS_DEFAULT_CUSTOMER_GROUP_;
        }

        // Customer Group
        $id_customer_group = (int)Configuration::get('FEEDBIZ_CUSTOMER_GROUP');

        if ((int)$id_customer_group && is_numeric($id_customer_group)) {
            $group = new Group($id_customer_group);

            if (!Validate::isLoadedObject($group)) {
                $id_customer_group = $id_default_customer_group;
            }

            unset($group);
        } else {
            $id_customer_group = $id_default_customer_group;
        }

        $fb_carrier_id = isset($order->Carrier['ID']) && ($order->Carrier['ID'] > 0) ? (int)$order->Carrier['ID'] : Configuration::get('FEEDBIZ_CARRIER');
        $fb_shipping_type = isset($order->Carrier->ShippingServicesLevel) ? (string)$order->Carrier->ShippingServicesLevel : null;
        $fb_earliest_ship_date = isset($order->Date->ShippingDate) ? (string)$order->Date->ShippingDate : null;
        $fb_latest_ship_date = isset($order->Date->LatestShipDate) ? (string)$order->Date->LatestShipDate : null;
        if (empty($fb_carrier_id) && !empty(FeedbizTools::$feed_config['FEEDBIZ_CARRIER'])) {
            $fb_carrier_id = FeedbizTools::$feed_config['FEEDBIZ_CARRIER'];
        }
        // get id_lang from iso code
        if (isset($order['Language']) && !empty($order['Language'])) {
            $id_lang = Language::getIdByIso((string)$order['Language']);
            $order_lang_id = !empty($id_lang) ? $id_lang : (isset($order['LanguageID']) ? (int)$order['LanguageID'] : $this->id_lang);
        } else {
            $order_lang_id = isset($order['LanguageID']) ? (int)$order['LanguageID'] : $this->id_lang;
        }

        // Order XML Validation
        if (empty($order) || !(int)$order->References->Id) {
            $this->errors [] = array(
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'message' => $this->l('Unable to read orders.')
            );

            return false;
        } else {
            $this->fborderID = (int)$order->References->Id;
        }

        // isPrime
        $isPrime = isset($order['isPrime']) ? (bool)$order['isPrime'] : false;

        // isPremium
        $isPremium = isset($order['isPremium']) ? (bool)$order['isPremium'] : false;

        // isBusiness
        $isBusiness = isset($order['isBusiness']) ? (bool)$order['isBusiness'] : false;

        $multichannel = '-';

        if (isset($order['Multichannel']) && Tools::strlen($order['Multichannel'])) {
            $order_state = $order_states['FEEDBIZ_CA'];

            switch ($order['Multichannel']) {
                case 'MCAFN':
                    $order_state = array_key_exists('FEEDBIZ_MC', $order_states) && (int)$order_states['FEEDBIZ_MC'] && is_numeric($order_states['FEEDBIZ_MC']) ? $order_states['FEEDBIZ_MC'] : $order_states['FEEDBIZ_CA'];
                    break;
                case 'AFN':
                    $order_state = array_key_exists('FEEDBIZ_FBA', $order_states) && (int)$order_states['FEEDBIZ_FBA'] && is_numeric($order_states['FEEDBIZ_FBA']) ? $order_states['FEEDBIZ_FBA'] : $order_states['FEEDBIZ_CA'];
                    $is_afn_order = true;
                    break;
                case 'MFN':
                    if ($isPrime) {
                        $order_state = array_key_exists('FEEDBIZ_UR', $order_states) && (int)$order_states['FEEDBIZ_UR'] && is_numeric($order_states['FEEDBIZ_UR']) ? $order_states['FEEDBIZ_UR'] : $order_states['FEEDBIZ_CA'];
                    }
                    break;
            }
            $multichannel = (string)$order['Multichannel'];
        }
        if (!empty($fb_shipping_type) && $fb_shipping_type == 'clogistique') {
            $order_state = array_key_exists('FEEDBIZ_FBA', $order_states) && (int)$order_states['FEEDBIZ_FBA'] && is_numeric($order_states['FEEDBIZ_FBA']) ? $order_states['FEEDBIZ_FBA'] : $order_state;
        }

        $fulfillment_center_id = null;
        if (isset($order['FulfillmentCenterId']) && Tools::strlen($order['FulfillmentCenterId'])) {
            $fulfillment_center_id = (string)$order['FulfillmentCenterId'];
        }

        $mp_reference = (string)$order->References->MPReference;
        $mp_number = isset($order->References->MPNumber) && $order->References->MPNumber ? (string)$order->References->MPNumber : $mp_reference;

        $force_import = Tools::getValue('force_import');
        $force_import = empty($force_import) ? 0 : 1;
        // Order Existing Validation
        $existingOrder = FeedBizOrder::checkByMpId($mp_reference, $this->debug);

        if (!isset($existingOrder) || !$existingOrder || empty($existingOrder)) {
            $seller_order_id = isset($order->Invoices['SellerOrderId']) ? (string)$order->Invoices['SellerOrderId'] : null ;
            // Check Order Existing by Seller Order ID
            if (isset($seller_order_id) && Tools::strlen($seller_order_id)) {
                $existingOrder = FeedBizOrder::checkBySellerOrderId($seller_order_id, $this->debug);
            }
        }
        $oldExistOrder = $existingOrder;

        if ($this->debug) {
            echo "-----------------------LINE : ".__LINE__."----------------------------\n<br/>";
            echo "Force import ?\n<br/>";
            echo "---------------------------------------------------\n<br/>";
            echo '<pre>';
            var_dump($force_import);
            echo "Old order ?\n<br/>";
            echo "---------------------------------------------------\n<br/>";
            var_dump($oldExistOrder);
            echo '</pre>\n<br/>';
        }

        if (is_array($existingOrder) && count($existingOrder) && !$force_import) {
            $params = array(
                'id_order' => (int)$existingOrder['id_order'],
                'mp_order_id' => (string)$this->fborderID,
                'channel_id' => (int)$order['SalesChannelID'],
                'channel_name' => (string)$order['SalesChannel'],
                'mp_reference' => $mp_reference,
                'mp_number' => $mp_number,
                'multichannel' => $multichannel,
                'fulfillment_center_id' => $fulfillment_center_id,
                'shipping_type' => $fb_shipping_type,
                'is_prime' => $isPrime,
                'is_premium' => $isPremium,
                'is_business' => $isBusiness,
                'earliest_ship_date' => $fb_earliest_ship_date,
                'latest_ship_date' => $fb_latest_ship_date,
                'mp_status' => $order_state,
            );

            FeedBizOrder::addOrderExt($params, $this->debug);

            // special case: success even the order already have been imported.
            $this->errors [] = array(
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'message' => $this->l('Order already imported.'),
                'existingOrder' => $existingOrder,
                'params' => $params,
            );

            if ($this->debug) {
                echo "-----------------------LINE : ".__LINE__."----------------------------\n<br/>";
                echo '<pre>';
                print_r($params);
                $oid = (int)$existingOrder['id_order'];
                $results = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'orders` WHERE  `id_order` = "'.$oid.'"');
                print_r($results);
                $results = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'order_invoice` WHERE  `id_order` = "'.$oid.'"');
                print_r($results);
                $results = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'order_detail` WHERE  `id_order` = "'.$oid.'"');
                print_r($results);
                echo '</pre>';
            }

            $feedbizOrder = new FeedBizOrder($existingOrder ['id_order']); 
            $feedbizOrder->updatePaymentMethod($this->debug,$existingOrder ['id_order']);
            if (method_exists($feedbizOrder, 'getInvoicesCollection')) {
                foreach ($feedbizOrder->getInvoicesCollection() as $invoice) {
                    $invoiceNumber = $invoice->getInvoiceNumberFormatted($this->id_lang, (int)$feedbizOrder->id_shop);
                }
            } else {
                $invoiceNumber = $ps_invoice_prefix.sprintf('%06d', $feedbizOrder->invoice_number);
            }

            $this->return_data = array(
                'OrderNumber' => $existingOrder ['id_order']
            );

            if (!empty($invoiceNumber)) {
                $this->return_data ['InvoiceNumber'] = $invoiceNumber;
            }
            $feedbizOrder->updateEmptyOrderInvoice($existingOrder ['id_order'], $this->debug);

            return $this->return_data;
        }

        // Require Carrier Validation
        if (!(int)$fb_carrier_id) {
            $this->errors [] = array(
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'message' => sprintf($this->l('Please configure the carrier in module configuration'))
            );

            return (false);
        }

        if (empty($order->Shipping->Address->CountryCode) ||
            (empty($order->Shipping->Address->Address1) && empty($order->Shipping->Address->Address2)) ||
            empty($order->Shipping->Address->City)) {
            $this->errors [] = array(
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'message' => $this->l('Invalid shipping address')
            );

            return false;
        }

        // Product Validation
        $id_employee = (int)Configuration::get('FEEDBIZ_ID_EMPLOYEE');
        if (empty($id_employee) && !empty(FeedbizTools::$feed_config['FEEDBIZ_ID_EMPLOYEE'])) {
            $id_employee = FeedbizTools::$feed_config['FEEDBIZ_ID_EMPLOYEE'];
        }
        $employee = new Employee($id_employee);

        if (!Validate::isLoadedObject($employee)) {
            $this->errors [] = array(
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'message' => $this->l('Invalid Employee')
            );

            return false;
        }

        $id_customer = (int)Configuration::get('FEEDBIZ_CUSTOMER_ID');
        if (empty($id_customer) && !empty(FeedbizTools::$feed_config['FEEDBIZ_CUSTOMER_ID'])) {
            $id_customer = FeedbizTools::$feed_config['FEEDBIZ_CUSTOMER_ID'];
        }
        $customer = new Customer($id_customer);

        if (!Validate::isLoadedObject($customer)) {
            $this->createCustomer();
            $id_customer = (int)Configuration::get('FEEDBIZ_CUSTOMER_ID');
            $customer = new Customer($id_customer);
        }

        if (!Validate::isLoadedObject($customer)) {
            $this->errors [] = array(
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'message' => $this->l('Invalid Customer')
            );
            if ($this->debug) {
                echo "-----------------------LINE : ".__LINE__."----------------------------\n<br/>";
                echo "Customer\n<br/>";
                echo "---------------------------------------------------\n<br/>";
                echo '<pre>'.print_r(array($id_customer,$customer), true).'</pre>\n<br/>';
            }

            return false;
        }

        $this->context->employee = $employee;
        $this->context->customer->id = $customer->id;
        $backup_name = array();
        foreach ($order->Items->children() as $item) {
            if ($this->debug) {
                echo "-----------------------LINE : ".__LINE__."----------------------------\n<br/>";
                echo "item\n<br/>";
                echo "---------------------------------------------------\n<br/>";
                echo '<pre>'.print_r($item, true).'</pre>\n<br/>';
            }
            $sku = (string)$item->Product->Reference;
            $product_name = (string)$item->Product->Name;
            $backup_name[$sku] = $product_name;
            if ($this->debug) {
                echo "-----------------------LINE : ".__LINE__."----------------------------\n<br/>";
                echo "product_name\n<br/>";
                echo "---------------------------------------------------\n<br/>";
                echo '<pre>'.print_r($product_name, true).'</pre>\n<br/>';
                var_dump($product_name);
                var_dump($item->Product->Name);
            }
            $id_product = (int)$item->Product->ID;
            $id_combination = isset($item->Product->AttributeID) ? (int)$item->Product->AttributeID : false;
            $price = (float)$item->Price->PerUnit->TaxIncl;
            $quantity = (int)$item->Quantity->Ordered;

            if (!$quantity) {
                $this->errors [] = array(
                    'file' => basename(__FILE__),
                    'line' => __LINE__,
                    'message' => sprintf($this->l('Unable to import a zero quantity for order'), $id_product, $id_combination)
                );

                return false;
            }

            // Load Product
            $product = new FeedBizProduct($id_product, true, $order_lang_id);

            if ($this->debug) {
                $vali = Validate::isLoadedObject($product);
                echo "-----------------------LINE : ".__LINE__."----------------------------\n<br/>";
                echo "check auto create product 1\n<br/>";
                echo "---------------------------------------------------\n<br/>";
                echo '<pre>'.print_r($product, true);
                var_dump($vali);
                var_dump($auto_create);
                echo '</pre>\n<br/>';
            }

            if ($auto_create && !Validate::isLoadedObject($product)) {
                $new_product = $this->createProduct($sku, $product_name, $price, $id_shop);

                if ($this->debug) {
                    $vali = Validate::isLoadedObject($new_product);
                    echo "-----------------------LINE : ".__LINE__."----------------------------\n<br/>";
                    echo "check auto create product 2\n<br/>";
                    echo "---------------------------------------------------\n<br/>";
                    echo '<pre>'.print_r($new_product, true);
                    var_dump($vali);
                    var_dump($auto_create);
                    echo '</pre>\n<br/>';
                }
                if (!Validate::isLoadedObject($new_product)) {
                    $this->errors []= array(
                        'file' => basename(__FILE__),
                        'line' => __LINE__,
                        'message' => sprintf($this->l('Unable to create product (%d/%d) for order #%s product SKU: %s'), $id_product, $id_combination, $mp_reference, $sku)
                    );
                    continue;
                }

                $product = $new_product;
                $id_product = (int) $product->id;

                $this->forceimport = true;
            }
            if ($this->debug) {
                echo "-----------------------LINE : ".__LINE__."----------------------------\n<br/>";
                echo "before validate product\n<br/>";
                echo "PID: ".$id_product."  order_lang_id:".$order_lang_id."\n<br>";
                echo "---------------------------------------------------\n<br/>";
            }
            if (!$id_product) {
                continue;
            }

            if (!Validate::isLoadedObject($product)) {
                if ($this->debug) {
                    echo "-----------------------LINE : ".__LINE__."----------------------------\n<br/>";
                    echo "product unable load\n<br/>";
                    echo "---------------------------------------------------\n<br/>";
                    echo '<pre>'.print_r($product, true);
                    var_dump($id_product);
                    var_dump($item->Product);
                    echo '</pre>\n<br/>';
                }
                $this->errors [] = array(
                    'file' => basename(__FILE__),
                    'line' => __LINE__,
                    'message' => $this->l('Unable to import product')
                );

                return false;
            }
            if ($this->debug) {
                echo "-----------------------LINE : ".__LINE__."----------------------------\n<br/>";
                echo "after validate product\n<br/>";
                echo "---------------------------------------------------\n<br/>";
            }

            $id_product_attribute = false;

            // Load Combination
            if ($id_combination) {
                if (version_compare(_PS_VERSION_, '1.5', '<')) {
                    $combinations = $product->getAttributeCombinaisons($order_lang_id);
                } else {
                    $combinations = $product->getAttributeCombinations($order_lang_id);
                }

                if ($combinations) {
                    foreach ($combinations as $combination) {
                        if ($combination ['id_product_attribute'] == $id_combination) {
                            $id_product_attribute = $combination ['id_product_attribute'];
                        }
                    }
                }

                if (!$id_product_attribute) {
                    $this->errors [] = array(
                        'file' => basename(__FILE__),
                        'line' => __LINE__,
                        'message' => sprintf($this->l('Couldn\'t match product attributes for product (%s/%s)'), $id_product, $id_combination)
                    );

                    return false;
                }
            }
            if ($this->debug) {
                echo "-----------------------LINE : ".__LINE__."----------------------------\n<br/>";
                echo "before get online stock\n<br/>";
                echo "---------------------------------------------------\n<br/>";
            }
            // Validate stock
            if ($stock_management) {
                if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                    $stockQty = Product::getRealQuantity($id_product, $id_combination ? $id_combination : null, $id_warehouse, (int)$id_shop);
                } else {
                    $stockQty = Product::getQuantity($id_product, $id_combination ? $id_combination : null);
                }
            } else {
                $stockQty = 100;
            }
            if ($this->debug) {
                echo "-----------------------LINE : ".__LINE__."----------------------------\n<br/>";
                echo "after get online stock\n<br/>";
                var_dump($stockQty);
                echo "---------------------------------------------------\n<br/>";
            }

            $orderForceimport = isset($order->Info->ForceImport) && (int)$order->Info->ForceImport ? true : false;
            $this->forceimport = $this->forceimport || $orderForceimport;
            if ($this->debug) {
                echo "-----------------------LINE : ".__LINE__."----------------------------\n<br/>";
                echo "force import?<br/>";
                var_dump($this->forceimport);
                echo 'advance stock enable?<br>';
                echo Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')."<br>";
                echo "---------------------------------------------------\n<br/>";
            }
            // Case Advanced Stock Management: update date_upd if user enabled Advanced Stock Management.
            if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') &&
                    StockAvailable::dependsOnStock($id_product) == 1
                ) {
                    $product->updateProductDate($id_product);
                }
            }
            if ($this->debug) {
                echo "-----------------------LINE : ".__LINE__."----------------------------\n<br/>";
                echo "After update product last update<br/>";
                echo "---------------------------------------------------\n<br/>";
            }
            if ($quantity > $stockQty && isset($product->out_of_stock) && !Product::isAvailableWhenOutOfStock($product->out_of_stock) && !$this->forceimport) {
                $this->errors [] = array(
                    'file' => basename(__FILE__),
                    'line' => __LINE__,
                    'message' => sprintf($this->l('Product is out of stock or order qty greater than stock (%s/%s)'), $id_product, $id_combination)
                );
                if ($this->debug) {
                    echo "-----------------------LINE : ".__LINE__."----------------------------\n<br/>";
                    echo "out of stock<br/>".sprintf($this->l('Product is out of stock or order qty greater than stock (%s/%s)'), $id_product, $id_combination);

                    echo "---------------------------------------------------\n<br/>";
                }
                return false;
            }
        }

        //$customerFirstname = $customerLastname = $customerCompany = $shippingFirstname = $shippingLastname = $shippingCompany = '';
        $itemDetails = array();
        $fees = 0;
        $date_add = date('Y-m-d H:i:s', strtotime((string)$order->Date->OrderDate));

        $shippingFirstname = null;
        $shippingLastname = null;
        $shippingCompany = null;

        if (isset($order->Shipping->Name->Firstname) && !empty($order->Shipping->Name->Firstname) && isset($order->Shipping->Name->Lastname) && !empty($order->Shipping->Name->Lastname)) {
            $shippingFirstname = (string)$order->Shipping->Name->Firstname;
            $shippingLastname = (string)$order->Shipping->Name->Lastname;
            $shippingCompany = isset($order->Shipping->Name->Company) ? (string)$order->Shipping->Name->Company : '';
        }

        if (empty($shippingFirstname) || empty($shippingLastname)) {
            $this->errors [] = array(
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'message' => $this->l('Invalid shipping name')
            );

            if ($this->debug) {
                echo "-----------------------LINE : ".__LINE__."----------------------------\n<br/>";
                echo "Shipping ame fail\n<br/>";
                echo "---------------------------------------------------\n<br/>";
                echo '<pre>'.print_r($order->Shipping, true);
                echo '</pre>\n<br/>';
            }

            return false;
        }

        $customerFirstname = null;
        $customerLastname = null;
        $customerCompany = null;

        if (isset($order->Buyer->FirstName) && !empty($order->Buyer->FirstName) && isset($order->Buyer->Lastname) && !empty($order->Buyer->Lastname)) {
            $customerFirstname = (string)$order->Buyer->FirstName;
            $customerLastname = (string)$order->Buyer->Lastname;
            $customerCompany = $shippingCompany;
        }

        if (empty($customerFirstname)) {
            $customerFirstname = $shippingFirstname;
        }
        if (empty($customerLastname)) {
            $customerLastname = $shippingLastname;
        }
        if (empty($customerCompany)) {
            $customerCompany = $shippingCompany;
        }

        $customerPhone = preg_replace('/[^0-9]/', '', isset($order->Shipping->Address->Phone) ? (string)$order->Shipping->Address->Phone : null);
        $shippingPhone = $customerPhone;
        $shippingPhoneMobile = preg_replace('/[^0-9]/', '', isset($order->Shipping->Address->PhoneMobile) ? (string)$order->Shipping->Address->PhoneMobile : null);

        $email_address = isset($order->Buyer->Email) ? (string)$order->Buyer->Email : '';
        $email_address = empty($email_address) ? 'no-reply@feedbiz.com' : $email_address;

        $customer = new Customer();
        $customer->getByEmail($email_address);
        if ($this->debug) {
            echo "---------------------------------------------------\n<br/>";
            echo "Customer ID : \n<br/>";
            echo "$shippingFirstname/$shippingLastname\n<br/>";
            var_dump($customer->id);
            echo "\n<br/>";
        }
        if ($customer->id) {
            $id_customer = $customer->id;
        } else {
            if ($this->debug) {
                echo "---------------------------------------------------\n<br/>";
                echo "Add Customer : \n<br/>";
                echo "$shippingFirstname/$shippingLastname\n<br/>";
                echo "\n<br/>";
            }

            $customer->firstname = $customerFirstname;
            $customer->lastname = $customerLastname;
            $customer->company = $customerCompany;
            $customer->email = $email_address;
            $customer->passwd = md5(rand());
            $customer->id_default_group = $id_customer_group;

            $pass = true;
            $line = false;

            if (!Validate::isName($customer->firstname) || !Validate::isName($customer->lastname) || !Validate::isEmail($customer->email)) {
                $pass = false;
                $line = __LINE__;
            } elseif (($validation_message = $customer->validateFields(false, true)) !== true) {
                $pass = false;
                $line = __LINE__;
            } elseif (!$customer->add()) {
                $pass = false;
                $line = __LINE__;
            }

            if (!$pass) {
                $this->errors [] = array(
                    'file' => basename(__FILE__),
                    'line' => $line,
                    'message' => sprintf($this->l('Couldn\'t add this customer %s.'), $email_address . $validation_message)
                );
                if ($this->debug) {
                    echo "-----------------------LINE : ".__LINE__."----------------------------\n<br/>";
                    echo sprintf($this->l('Couldn\'t add this customer %s.'), $email_address . $validation_message);

                    echo "---------------------------------------------------\n<br/>";
                }
            } else {
                $id_customer = $customer->id;
            }
        }

        // Address components :
        // City, Country, ZipCode, ApartmentNumber, Building, Civility, CompanyName, FirstName, Instructions, LastName, PlaceName, Street
        $shippingAddress = array();
        $shippingAddress ['country_iso'] = (string)$order->Shipping->Address->CountryCode;
        $shippingAddress ['lastname'] = (string)$shippingLastname;
        $shippingAddress ['firstname'] = (string)$shippingFirstname;
        $shippingAddress ['company'] = (string)$shippingCompany;
        $shippingAddress ['address_1'] = (string)$order->Shipping->Address->Address1;
        $shippingAddress ['address_2'] = isset($order->Shipping->Address->Address2) ? (string)$order->Shipping->Address->Address2 : '';
        $shippingAddress ['zipcode'] = empty($order->Shipping->Address->PostalCode) ? 'unknown' : (string)$order->Shipping->Address->PostalCode;
        $shippingAddress ['city'] = (string)$order->Shipping->Address->City;
        $shippingAddress ['phone'] = $shippingPhone;
        $shippingAddress ['phone_mobile'] = $shippingPhoneMobile;
        $shippingAddress ['state_region'] = (string)$order->Shipping->Address->StateRegion;
        $shippingAddress ['vat_number'] = isset($order->Shipping->Address->vat_number) ? (string) $order->Shipping->Address->vat_number : '';
        $shippingAddress ['other'] = isset($order->Shipping->Address->Other) ? (string)$order->Shipping->Address->Other : '';
        $shipping_address = new FeedBizAddress();
        $shipping_address->id_customer = (int)$id_customer;
        $shipping_address_id = $shipping_address->lookupOrCreateAddress($shippingAddress, $shippingPhone, $this->debug);

        $billingAddress = array();
        if (isset($order->Billing) && !empty($order->Billing)) {
            $billingAddress ['country_iso'] = (string)$order->Billing->Address->CountryCode;
            $billingAddress ['lastname'] = (string)$order->Billing->Name->Lastname;
            $billingAddress ['firstname'] = (string)$order->Billing->Name->Firstname;
            $billingAddress ['company'] = isset($order->Billing->Name->Company) ? (string)$order->Billing->Name->Company : '';
            $billingAddress ['address_1'] = isset($order->Billing->Address->Address1) ? (string)$order->Billing->Address->Address1 : '';
            $billingAddress ['address_2'] = isset($order->Billing->Address->Address2) ? (string)$order->Billing->Address->Address2 : '';
            $billingAddress ['zipcode'] = empty($order->Billing->Address->PostalCode) ? 'unknown' : (string)$order->Billing->Address->PostalCode;
            $billingAddress ['city'] = (string)$order->Billing->Address->City;
            $billingAddress ['phone'] = preg_replace('/[^0-9]/', '', isset($order->Billing->Address->Phone) ? (string)$order->Billing->Address->Phone : null);
            $billingAddress ['phone_mobile'] = preg_replace('/[^0-9]/', '', isset($order->Billing->Address->PhoneMobile) ? (string)$order->Billing->Address->PhoneMobile : null);
            $billingAddress ['state_region'] = (string)$order->Billing->Address->StateRegion;
            $billingAddress ['other'] = isset($order->Billing->Address->Other) ? (string)$order->Billing->Address->Other : '';
            $billingAddress ['vat_number'] = isset($order->Billing->Address->vat_number) ? (string) $order->Billing->Address->vat_number : '';
        } else {
            $billingAddress ['country_iso'] = $shippingAddress['country_iso'];
            $billingAddress ['lastname'] = (string)$customerLastname;
            $billingAddress ['firstname'] = (string)$customerFirstname;
            $billingAddress ['company'] = (string)$customerCompany;
            $billingAddress ['address_1'] = $shippingAddress ['address_1'];
            $billingAddress ['address_2'] = $shippingAddress ['address_2'];
            $billingAddress ['zipcode'] = $shippingAddress ['zipcode'];
            $billingAddress ['city'] = $shippingAddress ['city'];
            $billingAddress ['phone'] = $customerPhone;
            $billingAddress ['phone_mobile'] = $shippingAddress ['phone_mobile'];
            $billingAddress ['state_region'] = $shippingAddress ['state_region'];
            $billingAddress ['other'] = $shippingAddress ['other'];
            $billingAddress ['vat_number'] = $shippingAddress ['vat_number'];
        }

        $billing_address = new FeedBizAddress();
        $billing_address->id_customer = (int)$id_customer;
        $billing_address_id = $billing_address->lookupOrCreateAddress($billingAddress, $billingAddress ['phone'], $this->debug);

        if ($this->debug) {
            echo "---------------------------------------------------\n<br/>";
            echo "addressId : $shipping_address_id / $billing_address_id \n<br/>";
            echo "$customerFirstname/$customerLastname/$customerPhone\n<br/>";
            echo "\n<br/>";
        }
        if ($this->debug) {
            echo "---------------------------".__LINE__."------------------------\n<br/>";
        }
        // Id Currency
        if (!empty($order['Currency']) && Currency::getIdByIsoCode((string)$order['Currency'])) {
            $id_currency = Currency::getIdByIsoCode((string)$order['Currency']);
        } else {
            $id_currency = isset($order->Payment->CurrencyID) ? (int)$order->Payment->CurrencyID : null;
        }

        // Building Cart
        $this->cart = new FeedBizCart();
        $this->cart->id_address_delivery = $shipping_address_id;
        $this->cart->id_address_invoice = $billing_address_id;
        $this->cart->id_carrier = $fb_carrier_id;
        $this->cart->id_currency = $id_currency;
        $this->cart->id_customer = $id_customer;
        $this->cart->id_shop_group = (int)$id_shop_group;
        $this->cart->id_shop = (int)$id_shop;

        if ($this->debug) {
            echo "---------------------------".__LINE__."------------------------\n<br/>";
            echo "this->cart->id_address_delivery = $shipping_address_id; \n<br/>
        this->cart->id_address_invoice = $billing_address_id; \n<br/>
        this->cart->id_carrier = $fb_carrier_id; \n<br/>
        this->cart->id_currency = $id_currency; \n<br/>
        this->cart->id_customer = $id_customer; \n<br/>
        this->cart->id_shop_group =  $id_shop_group; \n<br/>
        this->cart->id_shop = $id_shop; \n<br/>"
              . " this->cart->id_address_invoice = ".$this->cart->id_address_invoice."\n<br>"
              . " order_lang_id ". $order_lang_id."\n<br>" ;
            echo "\n<br/>";
        }
        $addressLangId='';
        if (empty($order_lang_id)) {
            if (($orderBillingAddress = new Address($this->cart->id_address_invoice))) {
                if (($addressCountry = new Country($orderBillingAddress->id_country))) {
                    if (($addressLangId = Language::getIdByIso(Tools::strtolower($addressCountry->iso_code)))) {
                        $order_lang_id = (int)$addressLangId;
                    }
                }
            }
        }

        if ($this->debug) {
            echo "---------------------------".__LINE__."------------------------\n<br/>";
            echo "addressLangId = $addressLangId; \n<br/>
         order_lang_id= $order_lang_id; \n<br/> ";
            echo "\n<br/>";
        }

        $this->cart->id_lang = $order_lang_id;
        if ($this->debug) {
            echo "---------------------------".__LINE__."------------------------\n<br/>";
            echo "before Cart create ; \n<br/>";
            var_dump($this->cart);
            echo "\n<br/>";
        }
        $this->cart->add();

        if ($this->debug) {
            echo "---------------------------".__LINE__."------------------------\n<br/>";
            echo "Cart create pass \n<br/>";
            var_dump($this->cart);
            echo "\n<br/>";
        }

        $shipping_tax_incl = (float)$order->Invoices->Total->Shipping->TaxIncl;
        $shipping_tax_excl = !empty($order->Invoices->Total->Shipping->TaxExcl) ? (float)$order->Invoices->Total->Shipping->TaxExcl : 0;

        // Product Loop
        foreach ($order->Items->children() as $item) {
            $sku = (string)$item->Product->Reference;
            $product_name = (string)$item->Product->Name;

            $id_product = (int)$item->Product->ID;
            $id_combination = isset($item->Product->AttributeID) ? (int)$item->Product->AttributeID : false;

            $price = (float)$item->Price->PerUnit->TaxIncl;
            $quantity = (int)$item->Quantity->Ordered;

            if ($this->debug) {
                echo "\n<br/>Items\n<br/>";
                echo "---------------------------------------------------\n<br/>";
                echo nl2br(print_r($id_combination, true));
                echo nl2br(print_r($item, true));
                echo "\n<br/>";
            }

            // Load Product
            $product = new FeedBizProduct($id_product, true, $order_lang_id);
            $id_product_attribute = false;

            if ($auto_create && !Validate::isLoadedObject($product)) {
                $new_product = $this->createProduct($sku, $product_name, $price, $id_shop);

                if (!Validate::isLoadedObject($new_product)) {
                    $this->errors []= array(
                        'file' => basename(__FILE__),
                        'line' => __LINE__,
                        'message' => sprintf($this->l('Unable to create product (%d/%d) for order #%s product SKU: %s'), $id_product, $id_product_attribute, $mp_reference, $sku)
                    );
                    continue;
                }
                $product = $new_product;
                $id_product = (int) $product->id;
                $product_name = pSQL($product->name);

                $this->forceimport = true;
            }

            if (!$id_product) {
                $this->errors [] = array(
                    'file' => basename(__FILE__),
                    'line' => __LINE__,
                    'message' => sprintf($this->l('Unable to load product (%s)'), $sku)
                );
                continue;
            }

            if (empty($product->price) && !empty($price)) {
                $product->price = $price;
            }

            if (!Validate::isLoadedObject($product)) {
                $this->errors [] = array(
                    'file' => basename(__FILE__),
                    'line' => __LINE__,
                    'message' => sprintf($this->l('Unable to load product (%d/%d)'), $id_product, $id_product_attribute)
                );

                return false;
            }

            if (($validation_message = $product->validateFields(false, true)) !== true) {
                $this->errors [] = array(
                    'file' => basename(__FILE__),
                    'line' => __LINE__,
                    'message' => sprintf($this->l('Product validation failed (%d/%d) - Reason: %s'), $id_product, $id_product_attribute, $validation_message)
                );

                return false;
            }

            // Load Combination
            if ($id_combination) {
                if (version_compare(_PS_VERSION_, '1.5', '<')) {
                    $combinations = $product->getAttributeCombinaisons($order_lang_id);
                } else {
                    $combinations = $product->getAttributeCombinations($order_lang_id);
                }
                if ($combinations) {
                    foreach ($combinations as $combination) {
                        if ($combination ['id_product_attribute'] == $id_combination) {
                            $id_product_attribute = (int)$combination ['id_product_attribute'];
                        }
                    }
                }
            }

            // restock product
            $restockQty = 0;

            if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                $currentQty = Product::getRealQuantity($id_product, $id_combination, $id_warehouse, (int)$order['ShopID']);
                $restockQty = $currentQty < 0 ? ($currentQty * -1) + $quantity : $quantity;
                $restock_quantity = StockAvailable::updateQuantity($id_product, $id_combination, $restockQty);
            } else {
                $currentQty = Product::getQuantity($id_product, $id_product_attribute);
                $restock_quantity = Product::updateQuantity(array(
                    'id_product' => $id_product,
                    'id_product_attribute' => $id_combination,
                    'cart_quantity' => $quantity,
                    'out_of_stock' => 0
                ));
            }

            // set product to available for order
            $product_available_for_order = $product->available_for_order;

            if (!$product_available_for_order) {
                $this->errors [] = array(
                    'file' => basename(__FILE__),
                    'line' => __LINE__,
                    'message' => sprintf($this->l('Unable to import unavailable product "%s/%s" - Please set "available product" to yes for this product prior to import the order.'), $id_product, $id_product_attribute)
                );
                return false;
            }

            if ($restock_quantity) {
                if (!$product_available_for_order) {
                    $product->available_for_order = 1;
                    $product->update();
                }
            }

            $cart_quantity = $this->cart->updateQty($quantity, $id_product, $id_product_attribute);
            if ($this->debug) {
                echo "\n<br/>Cart updateQty\n<br/>";
                echo "------------------qty id_product id_atr = cart_qty---------------------------------\n<br/>";
                echo nl2br(print_r(array($quantity, $id_product, $id_product_attribute), true));
                echo nl2br(print_r($cart_quantity, true));
                echo "\n<br/>";
            }
            // rollback restock product
            if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                // $rollback_quantity can not be used, only true of false is returned, not the quantity
                StockAvailable::updateQuantity($id_product, $id_combination, ($restockQty * -1));
            } else {
                Product::updateQuantity(array(
                    'id_product' => $id_product,
                    'id_product_attribute' => $id_combination,
                    'cart_quantity' => $currentQty,
                    'out_of_stock' => 0
                ));
            }

            // set product to available for order
            if (!$product_available_for_order) {
                $product->available_for_order = $product_available_for_order;
                $product->update();
            }

            if ($product->active != 1) {
                $this->errors [] = array(
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'message' => sprintf($this->l('Product ID %d is inactive, import aborted'), $id_product)
                );

                return false;
            }
            if ($cart_quantity < 0) {
                $minimal_quantity = ($id_product_attribute) ? Attribute::getAttributeMinimalQty($id_product_attribute) : $product->minimal_quantity;
                $this->errors [] = array(
                    'file' => basename(__FILE__),
                    'line' => __LINE__,
                    'message' => sprintf($this->l('You must add (%d) minimum quantity for this product (%s/%s).'), $minimal_quantity, $id_product, $id_product_attribute)
                );

                return false;
            } elseif (!$cart_quantity && !$this->forceimport) {
                $this->errors [] = array(
                    'file' => basename(__FILE__),
                    'line' => __LINE__,
                    'message' => sprintf($this->l('You already have the maximum quantity available for this product (%s/%s).'), $id_product, $id_product_attribute)
                );

                return false;
            }

            $use_taxes = $isBusiness ? $isBusiness : $use_taxes;//;$use_taxes && !$isBusiness;
            $product_tax_rate = 0;
            if ($use_taxes) {
                if ($is_afn_order && isset($item->Price->TaxRate) && (float)$item->Price->TaxRate > 0) {
                    $product_tax_rate = (float)$item->Price->TaxRate ;
                } elseif (isset($item->Price->TaxRate) && (float)$item->Price->TaxRate > 0) {
                    // when order item include tax

                    // PS 1.4 sinon 1.3
                    if (method_exists('Tax', 'getProductTaxRate')) {
                        $product_tax_rate = (float)(Tax::getProductTaxRate($product->id, $shipping_address_id));
                        if ($this->debug) {
                            echo "-----------------------getProductTaxRate LINE : ".__LINE__."----------------------------\n<br/>";
                            echo '$product->id '.$product->id."\n<br>";
                            echo '$shipping_address_id '.$shipping_address_id."\n<br>";
                        }
                    } else {
                        $product_tax_rate = (float)(Tax::getApplicableTax($product->id_tax, $product->tax_rate, $shipping_address_id));
                        if ($this->debug) {
                            echo "-----------------------getProductTaxRate LINE : ".__LINE__."----------------------------\n<br/>";
                            echo '$product->id_tax '.$product->id_tax."\n<br>";
                            echo '$product->tax_rate '.$product->tax_rate."\n<br>";
                            echo '$shipping_address_id '.$shipping_address_id."\n<br>";
                        }
                    }
                    if (isset($item->Price->TaxRate) && (float)$item->Price->TaxRate > 0 && empty($product_tax_rate)) {
                        $product_tax_rate = (float)$item->Price->TaxRate;
                    }
                } else {
                    $use_taxes = false;
                    $product_tax_rate = 0;
                }
            } else {
                $product_tax_rate = 0;
            }

            if ($this->debug) {
                echo "-----------------------import_order.php LINE : ".__LINE__."----------------------------\n<br/>";
                echo "TAXES\n<br/>";
                echo "---------------------------------------------------\n<br/>";
                echo "Use Tax enabled : \n<br/>";
                var_dump($use_taxes);
                echo "\n<br>Afn order : \n<br/>";
                var_dump($is_afn_order);
                echo "\n<br/> product_tax_rate : $product_tax_rate \n<br/>";
            }

            $product_identifier = sprintf('%d_%d', $id_product, $id_product_attribute);

            if (isset($itemDetails [$product_identifier])) {
                $itemDetails [$product_identifier] ['qty'] += $quantity;
            } else {
                $itemDetails [$product_identifier] = array(
                    'id_product' => $id_product,
                    'id_product_attribute' => $id_product_attribute,
                    'qty' => $quantity,
                    'sku' => $sku,
                    'price' => (float)$price,
                    'name' => (string)$product_name,
                    'is_afn_order' => $is_afn_order,
                    'tax_rate' => $product_tax_rate,
                    'id_tax' => isset($product->id_tax) ? $product->id_tax : false,
                    'id_tax_rules_group' => isset($product->id_tax_rules_group) ? $product->id_tax_rules_group : false,
                    'id_address_delivery' => $shipping_address_id
                );
            }

            if ($this->debug) {
                echo "---------------------------------------------------\n<br/>";
                echo "Product\n<br/>";
                echo "---------------------------------------------------\n<br/>";
                echo '<pre>'.print_r($itemDetails [$product_identifier], true).'</pre>\n<br/>';
            }

            $cart_result = $this->cart->getProducts(false, $id_product);
            if ($this->debug) {
                echo "---------------------------------------------------\n<br/>";
                echo "Cart get Product\n<br/>";
                echo "-----------------------Cart validate----------------------------\n<br/>";
                var_dump(Validate::isLoadedObject($this->cart));
                echo '<pre>'.print_r(array($this->cart,$cart_result), true).'</pre>\n<br/>';
            }
            if (!is_array($cart_result) || ! count($cart_result)) {
                $this->errors [] = array(
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'message' => sprintf('%s#%d: '.$this->l('Cart validation failed for order: %s, product: %s - please type to purchase this product on the front-office'), basename(__FILE__), __LINE__, (string)$order->References->MPReference, $sku)
                );
                if (Validate::isLoadedObject($this->cart)) {
                    $this->cart->delete();
                }
                return(false);
            }
        }

        if (!count($itemDetails)) {
            $this->errors [] = array(
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'message' => $this->l('Cart empty, could not save order')
            );
        }

        // Using price, shipping details etc...
        $this->cart->mp_products = $itemDetails;
        $this->cart->mp_shipping = $shipping_tax_incl;
        $this->cart->mp_shipping_excl = $shipping_tax_excl;
        $this->cart->mp_date = $date_add;
        $this->cart->mp_fees = $fees;

        $this->cart->id_shop_group = (int)$id_shop_group;
        $this->cart->id_shop = (int)$id_shop;

        // Gift
        $this->cart->gift = isset($order->Gift->GiftMessage) && Tools::strlen($order->Gift->GiftMessage) ? (true) : (false);
        $this->cart->gift_message = isset($order->Gift->GiftMessage) ? (string)$order->Gift->GiftMessage : null;
        $this->cart->gift_wrap = isset($order->Total->GiftAmount) ? (float)$order->Gift->GiftAmount : 0;

        $this->cart->subtotal_discount = isset($order->Total->Discount) ? (float)$order->Total->Discount : 0;

        $acart = $this->cart;
        if ($this->debug && isset($acart)) {
            echo "---------------------------------------------------\n<br/>";
            echo "Cart\n<br/>";
            echo "---------------------------------------------------\n<br/>";
            echo '<pre>'.print_r(get_object_vars($acart), true).'</pre>\b<br/>';

            //return false;
        }
        // duplication du panier, important !!!

        if (($validation_message = $acart->validateFields(false, true)) !== true) {
            $this->errors [] = array(
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'message' => sprintf('%s#%d: '.'Field Validation failed for cart (Order: %s) - Reason: %s', basename(__FILE__), __LINE__, (string)$order->References->MPReference, $validation_message)
            );

            if (Validate::isLoadedObject($acart)) {
                $acart->delete();
            }

            return false;
        }

        $payment = new FeedBizPaymentModule();

        $newOrderId = null;

        if (Tools::strlen((string)$order['SalesChannel'])) {
            $payment_method = (string)$order['SalesChannel'];
        } else {
            $payment_method = 'Feed.biz';
        }

        if ($this->debug) {
            echo "---------------------------------------------------\n<br/>";
            echo "Cart\n<br/>";
            echo "---------------------------------------------------\n<br/>";
            echo '<pre>'.print_r(get_object_vars($acart), true).'</pre>\b<br/>';

            //return false;
        }
        $param_o=array('product_name'=>$backup_name,'force_import'=>$force_import);

        if (($newOrderId = $payment->feedbizValidateOrder($order_state, $payment_method, (string)$order->References->MPReference, $acart, $use_taxes, $param_o))) {
            Configuration::updateValue('FEEDBIZ_LAST_IMPORT', date('Y-m-d H:i:s'));
            $invoiceNumber = '';
            $feedbizOrder = new FeedBizOrder($newOrderId);
            $feedbizOrder->updatePaymentMethod($this->debug,$newOrderId);

            if (method_exists($feedbizOrder, 'getInvoicesCollection')) {
                foreach ($feedbizOrder->getInvoicesCollection() as $invoice) {
                    $invoiceNumber = $invoice->getInvoiceNumberFormatted($this->id_lang, (int)$feedbizOrder->id_shop);
                }
            } else {
                $invoiceNumber = $ps_invoice_prefix.sprintf('%06d', $feedbizOrder->invoice_number);
            }

            $mp_reference = (string)$order->References->MPReference;
            $mp_number = isset($order->References->MPNumber) && $order->References->MPNumber ? (string)$order->References->MPNumber : $mp_reference;

            $params = array(
                'id_order' => $newOrderId,
                'mp_order_id' => (string)$order->References->Id,
                'channel_id' => (int)$order['SalesChannelID'],
                'channel_name' => (string)$order['SalesChannel'],
                'mp_reference' => $mp_reference,
                'mp_number' => $mp_number,
                'multichannel' => $multichannel,
                'fulfillment_center_id' => $fulfillment_center_id,
                'shipping_type' => $fb_shipping_type,
                'is_prime' => $isPrime,
                'is_premium' => $isPremium,
                'is_business' => $isBusiness,
                'earliest_ship_date' => $fb_earliest_ship_date,
                'latest_ship_date' => $fb_latest_ship_date,
                'mp_status' => $order_state,
            );

            $errorMessage = FeedBizOrder::addOrderExt($params, $this->debug);
            if (!$errorMessage || Tools::strlen($errorMessage) > 1) {
                $this->errors [] = array(
                    'file' => basename(__FILE__),
                    'line' => __LINE__,
                    'message' => $this->l($errorMessage).'('.nl2br(print_r($params, true)).')'
                );
                $params ['addOrderExt'] = $errorMessage;
            }
            $params['items']=$itemDetails;
            FeedBizLog::log(FeedBizLog::FILE_LOG_ORDER_IMPORT, $params);

            $this->return_data = array(
                'OrderNumber' => $newOrderId
            );
            if (!empty($invoiceNumber)) {
                $this->return_data ['InvoiceNumber'] = $invoiceNumber;

                $feedbizOrder->updateEmptyOrderInvoice($newOrderId, $this->debug) ;
            }

            // debuss-a
            // If $shippingAddress['other'] == 'RELAY_ID_062324' then RELAY or Colissimo
            $tmp_carrier = new Carrier((int)$fb_carrier_id);
            if (Validate::isLoadedObject($tmp_carrier) && // check we actually have a relay ID
                Tools::strlen(filter_var($shippingAddress['other'], FILTER_SANITIZE_NUMBER_INT)) > 4) {
                if (Tools::substr($tmp_carrier->external_module_name, 0, 12) == 'mondialrelay') {
                    // Mondial Relay
                    $parameters = array(
                        'id_customer' => (int)$shipping_address->id_customer,
                        'id_method' => (int)Db::getInstance()->getValue(
                            'SELECT `id_mr_method`
                            FROM `'._DB_PREFIX_.'mr_method`
                            WHERE `id_carrier` = '.(int)$fb_carrier_id
                        ),
                        'id_cart' => (int)$acart->id,
                        'id_order' => (int)$newOrderId,
                        'MR_poids' => pSQL('00100'),
                        'MR_insurance' => 0,
                        'MR_Selected_Num' => pSQL(sprintf(
                            '%06s',
                            filter_var($shippingAddress['other'], FILTER_SANITIZE_NUMBER_INT)
                        )),
                        'MR_Selected_LgAdr1' => pSQL($shippingAddress['company']),
                        'MR_Selected_LgAdr2' => null,
                        'MR_Selected_LgAdr3' => pSQL($shippingAddress['address_1']),
                        'MR_Selected_LgAdr4' => null,
                        'MR_Selected_CP' => pSQL($shippingAddress['zipcode']),
                        'MR_Selected_Ville' => pSQL($shippingAddress['city']),
                        'MR_Selected_Pays' => pSQL($shippingAddress['country_iso']),
                        'url_suivi' => null,
                        'url_etiquette' => null,
                        'exp_number' => null,
                        'date_add' => date('Y-m-d H:i:s'),
                        'date_upd' => date('Y-m-d H:i:s')
                    );

                    FeedbizPickUpCarrier::saveMondialRelayInformations($parameters);
                // TODO Log
                } elseif (in_array($tmp_carrier->external_module_name, array('socolissimo', 'soliberte', 'soflexibilite'))) {
                    // Colissimo
                    $table = 'socolissimo_delivery_info';
                    if ($tmp_carrier->external_module_name == 'soliberte' && Configuration::get('SOLIBERTE_MODE') == 2 ||
                        $tmp_carrier->external_module_name == 'soflexibilite' && Configuration::get('SOFLEXIBILITE_MODE') == 2) {
                        $table = 'so_delivery';
                    }

                    $relay_id = sprintf(
                        '%06s',
                        filter_var($shippingAddress['other'], FILTER_SANITIZE_NUMBER_INT)
                    );

                    if ($table == 'socolissimo_delivery_info') {
                        $parameters = array(
                            'id_cart' => (int)$acart->id,
                            'id_customer' => (int)$shipping_address->id_customer,
                            'delivery_mode' => 'A2P',
                            'prid' => pSQL($relay_id),
                            'prname' => pSQL($shippingAddress['company']),
                            'pradress1' => pSQL($shippingAddress['address_1']),
                            'pradress2' => null,
                            'pradress3' => null,
                            'pradress4' => null,
                            'przipcode' => pSQL($shippingAddress['zipcode']),
                            'prtown' => pSQL($shippingAddress['city']),
                            'cecountry' => pSQL($shippingAddress['country_iso']),
                            'cephonenumber' => pSQL($shippingAddress['phone_mobile']),
                            'ceemail' => pSQL($customer->email),
                            'cecompanyname' => null,
                        );
                    } else {
                        $parameters = array(
                            'cart_id' => (int)$acart->id,
                            'order_id' => $newOrderId,
                            'customer_id' => (int)$shipping_address->id_customer,
                            'type' => 'A2P',
                            'point_id' => pSQL($relay_id),
                            'libelle' => pSQL($shippingAddress['company']),
                            'firstname' => pSQL($customer->firstname),
                            'lastname' => pSQL($customer->lastname),
                            'adresse1' => pSQL($shippingAddress['address_1']),
                            'adresse2' => null,
                            'lieudit' => null,
                            'code_postal' => pSQL($shippingAddress['zipcode']),
                            'commune' => pSQL($shippingAddress['city']),
                            'pays' => pSQL($shippingAddress['country_iso']),
                            'telephone' => pSQL(str_replace(' ', '', $shippingAddress['phone_mobile'])),
                            'email' => pSQL($customer->email),
                            'company' => null
                        );
                    }

                    FeedbizPickUpCarrier::saveColissimoInformations($parameters, $table);
                    // TODO Log
                }
            }

            // Hook new order
            $orderStatus = new OrderState((int)$order_state);
            $currency = new Currency($id_currency);

            if (Validate::isLoadedObject($orderStatus)) {
                if (version_compare(_PS_VERSION_, '1.5', '<')) {
                    Hook::newOrder($acart, $feedbizOrder, $customer, $currency, $orderStatus);
                } else {
                    // Hook validate order
                    Hook::exec('actionValidateOrder', array(
                        'cart' => $acart,
                        'order' => $feedbizOrder,
                        'customer' => $customer,
                        'currency' => $currency,
                        'orderStatus' => $orderStatus
                    ));
                }
            }
            
            $feedbizOrder->updatePaymentMethod($this->debug,$newOrderId);
            
        } else {
            $this->errors [] = array(
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'message' => sprintf($this->l('1 or more error occurs, unable to import order ID : %s'), (string)$order->References->Id)
            );

            return false;
        }
    }

    private function createProduct($sku, $name, $price, $id_shop)
    {
        $product = FeedBizProduct::chcekProductBySKU($sku, false, $this->id_lang, 'reference', $id_shop, $this->debug);

        if (Validate::isLoadedObject($product)) {
            return($product);
        }

        $id_lang_default = (int)Configuration::get('PS_LANG_DEFAULT');
        $languages = Language::getLanguages(false);
        $language_array = array();
        $language_array[$id_lang_default] = null;

        $name_array = array();
        $link_array = array();

        foreach ($languages as $language) {
            $id_lang = (int)$language['id_lang'];
            $name_array[$id_lang] = Tools::substr(str_replace(array('<', '>', ';', '=', '#', '{', '}'), '/', $name), 0, 128);
            $link_array[$id_lang] = Tools::substr(Tools::link_rewrite($name_array[$id_lang]), 0, 128) ;
        }

        $reference = Tools::substr($sku, 0, 32);

        if (!Validate::isReference($reference)) {
            return(false);
        }

        $product = new Product();
        $product->name = $name_array;
        $product->reference = $reference;
        $product->active = true;
        $product->available_for_order = true;
        $product->visibility = 'none';
        $product->id_tax_rules_group = 0;
        $product->is_virtual = 0;
        $product->tax_name = null;
        $product->tax_rate = 0;
        $product->price = (float)$price;
        $product->link_rewrite = $link_array;
        $product->id_product_attribute = null;
        if (method_exists('Product', 'getIdTaxRulesGroupMostUsed')) {
            $product->id_tax_rules_group = (int)Product::getIdTaxRulesGroupMostUsed();
        }

        if ($this->debug) {
            CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
            CommonTools::p("New Product: ".print_r(get_object_vars($product), true));
        }

        if ($product->validateFields(false, true)) {
            $product->add();

            if (!Validate::isLoadedObject($product)) {
                if ($this->debug) {
                    CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                    CommonTools::p("Add new product false: ".print_r(get_object_vars($product), true));
                }
                return(false);
            }

            if (method_exists('StockAvailable', 'setProductOutOfStock')) {
                StockAvailable::setProductOutOfStock((int)$product->id, 1);
            }

            return($product);
        } else {
            if ($this->debug) {
                CommonTools::p(sprintf('%s - %s::%s - line #%d', basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__));
                CommonTools::p("Validate new product false: ".print_r(get_object_vars($product), true));
            }
            return(false);
        }
    }

    /**
     *
     */
    public function FBShutdowFunction()
    {
        if (!$this->debug) {
            if (!FeedbizTools::$security_passed) {
                return false;
            }

            $outputMessages = array();
            foreach ($this->errors as $errorEle) {
                $outputMessages [] = $errorEle ['message'];
            }

            $Document = new DOMDocument();
            $Document->preserveWhiteSpace = true;
            $Document->formatOutput = true;
            $Document->encoding = 'utf-8';
            $Document->version = '1.0';

            $OfferPackage = $Document->appendChild($Document->createElement('Result'));
            $OfferPackage->setAttribute('ShopName', Configuration::get('PS_SHOP_NAME'));
            $OfferPackage->appendChild($StatusDoc = $Document->createElement('Status', ''));
            $StatusDoc->appendChild($Document->createElement('Code', $this->status_code));
            $StatusDoc->appendChild($Document->createElement('Message', $this->status));

            $outputDoc = $StatusDoc->appendChild($Document->createElement('Output'));
            $outputDoc->appendChild($Document->createCDATASection(implode("\n", $outputMessages)));

            if (isset($this->return_data ['InvoiceNumber'])) {
                $StatusDoc->appendChild($Document->createElement('InvoiceNumber', $this->return_data ['InvoiceNumber']));
            }
            if (isset($this->return_data ['OrderNumber'])) {
                $StatusDoc->appendChild($Document->createElement('OrderNumber', $this->return_data ['OrderNumber']));
            }

            $outBuffer = ob_get_contents();
            $errorDoc = $StatusDoc->appendChild($Document->createElement('Error'));
            $errorDoc->appendChild($Document->createCDATASection($outBuffer));

            header("Content-Type: application/xml; charset=utf-8");
            ob_end_clean();

            echo $Document->saveXML();
        } else {
            $out = ob_end_flush();
            echo "---------------------------------------------------\n<br/>";
            echo "Errors\n<br/>";
            echo "---------------------------------------------------\n<br/>";
            echo '<pre>'.print_r($this->errors, true).'</pre> \n<br/>';

            echo $out;
        }
        exit(1);
    }
}

$feedbizordersimport = new FeedBizOrdersImport();
$feedbizordersimport->dispatch();

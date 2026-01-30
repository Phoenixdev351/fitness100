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
 *
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

require_once(dirname(__FILE__).'/env.php');
require_once(dirname(__FILE__).'/../feedbiz.php');

require_once(dirname(__FILE__).'/../classes/feedbiz.tools.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.webservice.class.php');

require_once(dirname(__FILE__).'/../classes/feedbiz.order.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.log.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.exportcontext.class.php');

/**
 * Class FeedBizFeedbizTool
 */
class FeedBizFeedbizTool extends Feedbiz
{
    /**
     * @var
     */
    private $username;
    /**
     * @var string
     */
    private $token;
    /**
     * @var bool
     */
    private $debug;
    /**
     * @var bool
     */
    private $preproduction;
    /**
     * @var array
     */
    private $errors = array();
    /**
     * @var string
     */
    private $cr     = "\n<br/>";

    /**
     * FeedBizFeedbizTool constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->token = Configuration::get('FEEDBIZ_TOKEN');
        $this->preproduction = Configuration::get('FEEDBIZ_PREPRODUCTION') ? true : false;

        $this->debug = (bool)Configuration::get('FEEDBIZ_DEBUG') || Tools::getValue('debug');

        if ($this->debug) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        }
    }

    /**
     *
     */
    public function dispatch()
    {
        FeedbizTools::securityCheck();

        $fbaction = Tools::getValue('fbaction');
        $fborder = Tools::getValue('fborder');
        $psorder = Tools::getValue('psorder');
        $otp = Tools::getValue('otp');
        $fbtoken = null; // TODO

        switch ($fbaction) {
            case "get_order":
                $params = array(
                    'token' => $fbtoken,
                    'id_order' => $fborder,
                    'otp' => md5(md5($otp))
                );

                $FeedBizWS = new FeedBizWebService($this->username, $this->token, $this->preproduction, true);
                $order = $FeedBizWS->getOrder($params, 'getOrders', 'GET', true, $this->debug);

                if (empty($order->Order) || !(int)$order->Order->References->Id) {
                    $this->errors [] = sprintf('%s/%s: %s', basename(__FILE__), __LINE__, $this->l('Unable to read orders...')).$this->cr;
                } else {
                    $psorder = new FeedBizOrder($psorder);

                    if ($psorder) {
                        echo '-------------------GET------------------'.$this->cr;
                        echo '<pre>'.print_r($psorder, true).'</pre>'.$this->cr;
                    }
                }
                break;
            case "hide_order":
                $params = array(
                    'token' => $fbtoken,
                    'id_order' => $fborder,
                    'otp' => md5(md5($otp))
                );

                $FeedBizWS = new FeedBizWebService($this->username, $this->token, $this->preproduction, true);
                $order = $FeedBizWS->getOrder($params, 'getOrders', 'GET', true, $this->debug);

                if (empty($order->Order) || !(int)$order->Order->References->Id) {
                    $this->errors [] = sprintf('%s/%s: %s', basename(__FILE__), __LINE__, $this->l('Unable to read orders...')).$this->cr;
                } else {
                    echo '-------------------UPDATE------------------'.$this->cr;

                    $sql = "DELETE FROM `"._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_ORDERS."` WHERE mp_order_id = '".pSQL((int)$order->Order->References->Id)."'";
                    Db::getInstance()->execute($sql);
                }
                break;
            case "bind_order":
                $params = array(
                    'token' => $fbtoken,
                    'id_order' => $fborder,
                    'otp' => md5(md5($otp))
                );

                $FeedBizWS = new FeedBizWebService($this->username, $this->token, $this->preproduction, true);
                $order = $FeedBizWS->getOrder($params, 'getOrders', 'GET', true, $this->debug);

                if (empty($order->Order) || !(int)$order->Order->References->Id) {
                    $this->errors [] = sprintf('%s/%s: %s', basename(__FILE__), __LINE__, $this->l('Unable to read orders...')).$this->cr;
                } else {
                    $psorder = new FeedBizOrder($psorder);

                    if ($psorder) {
                        $id_invoice = (string)$order->Order->Invoices['InvoiceNo'];
                        $id_invoice = ltrim($id_invoice, '#');

                        if (is_numeric($id_invoice)) {
                            $id_invoice = (int)($id_invoice);
                        } elseif (is_string($id_invoice)) {
                            $matches = array();
                            if (preg_match('/^(?:'.Configuration::get('PS_INVOICE_PREFIX', $this->id_lang).')\s*([0-9]+)$/i', $id_invoice, $matches)) {
                                $id_invoice = $matches [1];
                            }
                        }

                        if ($id_invoice) {
                            $id_order_invoice = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
													SELECT `id_order_invoice`
													FROM `'._DB_PREFIX_.'order_invoice`
													WHERE number = '.(int)$id_invoice);

                            $orderInvoice = ($id_order_invoice ? new OrderInvoice($id_order_invoice) : false);

                            if ($orderInvoice) {
                                if ($this->debug) {
                                    echo '---------------ORDER INVOICE---------------'.$this->cr;
                                    echo '<pre>'.print_r($orderInvoice, true).'</pre>'.$this->cr;
                                    echo '-----------------------------------------'.$this->cr;
                                    break;
                                } else {
                                    $params = array(
                                        'id_order' => $orderInvoice->id_order,
                                        'mp_order_id' => (string)$order->Order->References->Id,
                                        'channel_id' => (int)$order->Order->References->ChannelId,
                                        'channel_name' => (string)$order->Order['SalesChannel'],
                                        'mp_reference' => (string)$order->Order->References->MPReference
                                    );
                                    $errMessage = FeedBizOrder::addOrderExt($params, $this->debug);
                                    if ($errMessage) {
                                        $this->errors [] = $errMessage;
                                    } else {
                                        echo "Insert ".print_r($params, true).$this->cr;
                                    }
                                }
                            } else {
                                $this->errors [] = sprintf("Order invoice not found %s/%s/%s.", (string)$order->Order->Invoices['InvoiceNo'], $id_invoice, '/^(?:'.Configuration::get('PS_INVOICE_PREFIX', Context::getContext()->language->id).')\s*([0-9]+)$/i');
                            }
                        }
                    }
                }
                break;
            case "clear_log":
                $fbtype = Tools::getValue('fbtype');
                switch ($fbtype) {
                    case "stockmovement":
                        FeedBizLog::clear(FeedBizLog::FILE_LOG_STOCK_MOVEMENT);
                        break;
                    case "ordersimport":
                        FeedBizLog::clear(FeedBizLog::FILE_LOG_ORDER_IMPORT);
                        break;
                    case "all":
                        FeedBizLog::clear(FeedBizLog::FILE_LOG_ORDER_IMPORT);
                        FeedBizLog::clear(FeedBizLog::FILE_LOG_STOCK_MOVEMENT);
                        break;
                }
                break;
            case "alloffers":
                $fbvalue = Tools::getValue('fbvalue');
                Configuration::updateValue('FEEDBIZ_ALL_OFFERS', (int)$fbvalue);
                break;
            case "addMPOrderNumber":
                if (!$this->isColumnExist(_DB_PREFIX_.self::TABLE_FEEDBIZ_ORDERS, 'mp_number')) {
                    Db::getInstance()->query("ALTER TABLE `"._DB_PREFIX_.self::TABLE_FEEDBIZ_ORDERS."` ADD COLUMN `mp_number` VARCHAR( 32 ) NOT NULL");
                } else {
                    echo 'already exist';
                }
                break;
            case "clearproductsexportcontext":
                Configuration::updateValue(FeedBizExportContext::CONF_FEEDBIZ_PRODUCTS_EXPORT_CONTEXT, '');
                break;
            case "clearoffersexportcontext":
                Configuration::updateValue(FeedBizExportContext::CONF_FEEDBIZ_OFFERS_EXPORT_CONTEXT, '');
                break;
        }

        echo '-------------------ERROR------------------'.$this->cr;
        echo '<pre>'.print_r($this->errors, true).'</pre>';
    }

    /**
     * @param $table
     * @param $column
     *
     * @return bool
     */
    public function isColumnExist($table, $column)
    {
        $sql
            = "select count(*) as cnt
					from information_schema.`COLUMNS`
					where TABLE_SCHEMA = '"._DB_NAME_."'
					AND TABLE_NAME = '".pSQL($table)."'
					AND COLUMN_NAME = '".pSQL($column)."'";
        $result = Db::getInstance()->getRow($sql);
        return $result['cnt'] > 0;
    }
}

$feedbiztool = new FeedBizFeedbizTool();
$feedbiztool->dispatch();

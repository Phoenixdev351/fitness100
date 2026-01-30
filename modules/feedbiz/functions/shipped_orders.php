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

/**
 * Class FeedBizShippedOrders
 */
class FeedBizShippedOrders extends Feedbiz
{
    /**
     * @var array
     */
    private $errors = array();
    /**
     * @var
     */
    private $token;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @var
     */
    private $statusCode;
    /**
     * @var
     */
    private $status;

    /**
     * @var array
     */
    private $prepared_data = array();

    /**
     * FeedBizShippedOrders constructor.
     */
    public function __construct()
    {
        $this->debug = (bool)Configuration::get('FEEDBIZ_DEBUG') || Tools::getValue('debug');

        if ($this->debug) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        } else {
            register_shutdown_function(array(
                $this,
                'FBShutdowFunction'
            ));
        }
        parent::__construct();
    }

    /**
     *
     */
    public function dispatch()
    {
        FeedbizTools::securityCheck();

        ob_start();

        // Check Access Tokens
        //
        $this->token = Configuration::get('FEEDBIZ_TOKEN');


        $order_states = unserialize(Configuration::get('FEEDBIZ_ORDERS_STATES'));

        if (!$order_states ['FEEDBIZ_CE']) {
            $this->errors [] = $this->l('Order state for sent orders on configuration page is required.');
        }

        if (!$order_states ['FEEDBIZ_CL']) {
            $this->errors [] = $this->l('Order state for Delivered orders on configuration page is required.');
        }

        if (empty($this->errors)) {
            $shipped_orders = FeedBizOrder::getOrdersByState(( int )$order_states ['FEEDBIZ_CE'], $this->debug);
            if (is_array($shipped_orders) && count($shipped_orders)) {
                foreach ($shipped_orders as $shipped_order) {
                    $this->prepared_data [] = array(
                        'id_order' => $shipped_order ['id_order'],
                        'mp_order_id' => $shipped_order ['mp_order_id'],
                        'id_carrier' => $shipped_order ['id_carrier'],
                        'name_carrier' => $shipped_order ['name_carrier'],
                        'shipping_number' => $shipped_order ['shipping_number'] ? $shipped_order ['shipping_number'] : FeedBizOrder::getShippingNumber($shipped_order['id_order']),
                        'shipping_date' => $shipped_order ['shipping_date'],
                        'delivered' => ''
                    );
                }
            }

            $delivered_orders = FeedBizOrder::getOrdersByState(( int )$order_states ['FEEDBIZ_CL'], $this->debug);
            if (is_array($delivered_orders) && count($delivered_orders)) {
                foreach ($delivered_orders as $delivered_order) {
                    $this->prepared_data [] = array(
                        'id_order' => $delivered_order ['id_order'],
                        'mp_order_id' => $delivered_order ['mp_order_id'],
                        'id_carrier' => $delivered_order ['id_carrier'],
                        'name_carrier' => $delivered_order ['name_carrier'],
                        'shipping_number' => $delivered_order ['shipping_number'] ? $delivered_order ['shipping_number'] : FeedBizOrder::getShippingNumber($delivered_order['id_order']),
                        'shipping_date' => $shipped_order ['shipping_date'],
                        'delivered' => 'delivered'
                    );
                }
            }

            $this->statusCode = '1';
            $this->status = $this->l('Success');
        } else {
            echo implode("\n", $this->errors);
            $this->statusCode = '0';
            $this->status = $this->l('Fail');
        }

        // Look register_shutdown_function
    }

    /**
     *
     */
    public function FBShutdowFunction()
    {
        if (!FeedbizTools::$security_passed) {
            return false;
        }

        $Document = new DOMDocument();
        $Document->preserveWhiteSpace = true;
        $Document->formatOutput = true;
        $Document->encoding = 'utf-8';
        $Document->version = '1.0';

        $ExportDataPackage = $Document->appendChild($Document->createElement('ExportData'));
        $ExportDataPackage->setAttribute('ShopName', Configuration::get('PS_SHOP_NAME'));

        $OrdersDOM = $ExportDataPackage->appendChild($Document->createElement('Orders'));

        foreach ($this->prepared_data as $order) {
            $OrderDOM = $OrdersDOM->appendChild($Document->createElement('Order'));
            $OrderDOM->setAttribute('ID', $order ['id_order']);
            $OrderDOM->appendChild($Document->createElement('MPOrderID', $order ['mp_order_id']));
            $OrderDOM->appendChild($Document->createElement('CarrierID', $order ['id_carrier']));
            $OrderDOM->appendChild($Document->createElement('CarrierName', $order ['name_carrier']));
            $OrderDOM->appendChild($Document->createElement('ShippingNumber', $order ['shipping_number']));
            $OrderDOM->appendChild($Document->createElement('ShippingDate', $order ['shipping_date']));
            $OrderDOM->appendChild($Document->createElement('DeliveredStatus', $order ['delivered']));
        }

        $ExportDataPackage->appendChild($StatusDoc = $Document->createElement('Status', ''));
        $StatusDoc->appendChild($Document->createElement('Code', $this->statusCode));
        $StatusDoc->appendChild($Document->createElement('Message', $this->status));
        $StatusDoc->appendChild($Document->createElement('Output', implode(", ", $this->errors)));
        $errorDoc = $StatusDoc->appendChild($Document->createElement('Error'));

        $outBuffer = ob_get_contents();
        $errorDoc->appendChild($Document->createCDATASection($outBuffer));

        if (!$this->debug) {
            header("Content-Type: application/xml; charset=utf-8");
            ob_end_clean();

            echo $Document->saveXML();
            exit(1);
        }
    }
}

$feedbizshippedorders = new FeedBizShippedOrders();
$feedbizshippedorders->dispatch();

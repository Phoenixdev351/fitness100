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
require_once(dirname(__FILE__).'/../classes/feedbiz.context.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.product.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.address.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.cart.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.order.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.orderhistory.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.payment.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.tax.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.log.class.php');

/**
 * Class FeedBizOrdersCancel
 */
class FeedBizOrdersCancel extends Feedbiz
{
    /**
     * @var array
     */
    private $errors = array();

    // FeedBiz auth
    //
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
    private $preproduction;
    /**
     * @var array
     */
    private $returnData  = array();
    /**
     * @var string
     */
    private $statusCode;
    /**
     * @var string
     */
    private $status;
    /**
     * @var bool
     */
    private $forceimport = false;

    /**
     * FeedBizOrdersCancel constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->forceimport = (int)Configuration::get('FEEDBIZ_FORCEIMPORT') ? true : false;

        register_shutdown_function(array(
            $this,
            'FBShutdowFunction'
        ));

        ob_start();

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
            $this->context->employee = $employee;
        }

        FeedbizContext::restore($this->context);

        $this->statusCode = '0';
        $this->status = $this->l('Fail');

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

        $fborder = Tools::getValue('fborder');

        // Check Access Tokens
        $this->token = Configuration::get('FEEDBIZ_TOKEN');
        $this->preproduction = Configuration::get('FEEDBIZ_PREPRODUCTION') ? true : false;

        if ($this->debug) {
            @define('_PS_DEBUG_SQL_', true);
            @error_reporting(E_ALL | E_STRICT);
        }

        $success = false;
        $order_states = unserialize(Configuration::get('FEEDBIZ_ORDERS_STATES'));
        $order_state_canceled = $order_states ['FEEDBIZ_CR']; // Commande resilier
        $order_state = new OrderState($order_state_canceled);

        $existingOrder = FeedBizOrder::checkByMpId((string)$fborder, $this->debug);
        $order = !empty($existingOrder) && isset($existingOrder['id_order']) ? new Order($existingOrder['id_order']) : null;

        if ($order == null) {
            $this->errors[] = array(
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'message' => $this->l('Order not found.')
            );
        } elseif (!Validate::isLoadedObject($order)) {
            $this->errors[] = array(
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'message' => $this->l('Order object is invalid.')
            );
        } elseif (!Validate::isLoadedObject($order_state)) {
            $this->errors[] = array(
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'message' => $this->l('The new order status is invalid.')
            );
        } else {
            $current_order_state = $order->getCurrentOrderState();
            if ($current_order_state->id != $order_state->id) {
                // Create new OrderHistory
                $history = new OrderHistory();
                $history->id_order = $order->id;
                $history->id_employee = (int)$this->context->employee->id;

                $use_existings_payment = false;
                if (!$order->hasInvoice()) {
                    $use_existings_payment = true;
                }
                $history->changeIdOrderState((int)$order_state->id, $order, $use_existings_payment);
                $history->addWs();
                $success = true;
            } else {
                $this->statusCode = '-1';
                $this->status = $this->l('Fail');
                $this->errors[] = array(
                    'file' => basename(__FILE__),
                    'line' => __LINE__,
                    'message' => $this->l('The order has already been assigned this status.')
                );
            }
        }

        if ($success) {
            $this->statusCode = '1';
            $this->status = $this->l('Success');
        }

        // Look register_shutdown_function
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
            $StatusDoc->appendChild($Document->createElement('Code', $this->statusCode));
            $StatusDoc->appendChild($Document->createElement('Message', $this->status));
            $StatusDoc->appendChild($Document->createElement('Output', implode("\n", $outputMessages)));
            $errorDoc = $StatusDoc->appendChild($Document->createElement('Error'));

            $outBuffer = ob_get_contents();
            $errorDoc->appendChild($Document->createCDATASection($outBuffer));

            if (isset($this->returnData ['InvoiceNumber'])) {
                $StatusDoc->appendChild($Document->createElement('InvoiceNumber', $this->returnData ['InvoiceNumber']));
            }
            if (isset($this->returnData ['OrderNumber'])) {
                $StatusDoc->appendChild($Document->createElement('OrderNumber', $this->returnData ['OrderNumber']));
            }

            header("Content-Type: application/xml; charset=utf-8");
            ob_end_clean();

            echo $Document->saveXML();
        } else {
            echo "---------------------------------------------------\n<br/>";
            echo "Errors\n<br/>";
            echo "---------------------------------------------------\n<br/>";
            echo '<pre>'.print_r($this->errors, true).'</pre> \n<br/>';
        }
        exit(1);
    }
}

$feedbizorderscancel = new FeedBizOrdersCancel();
$feedbizorderscancel->dispatch();

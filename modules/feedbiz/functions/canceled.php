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
require_once(dirname(__FILE__).'/../classes/feedbiz.carrier.class.php');

class FeedbizCancelMode extends Feedbiz
{
    /** @var string Feed.biz auth */
    private $username;

    public function __construct()
    {
        parent::__construct();

        $this->debug = (bool)Configuration::get('FEEDBIZ_DEBUG') || Tools::getValue('debug');

        if ($this->debug) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        }
    }

    public function dispatch()
    {
        $action = Tools::getValue('action');

        switch ($action) {
            case 'cancel':
                $this->cancelOrder();
                break;
        }
    }

    public function cancelOrder()
    {
        $fbtoken = Tools::getValue('fbtoken');
        if (!FeedbizTools::checkToken($fbtoken)) {
            die(Tools::displayError('Wrong Feedbiz token, please check the connection'));
        }

        $debug = $this->debug;
        $error = false;
        $pass = true;
        $message = null;

        $id_order = (int)Tools::getValue('id_order');
        $mp_order_id = (int)Tools::getValue('mp_order_id');
        $reason = Tools::getValue('reason');
        $status = Tools::getValue('cancel_status');

        if (!$status) {
            die(Tools::displayError('Missing status'));
        }
        if (!$id_order) {
            die(Tools::displayError('Missing id_order'));
        }
        if (!$reason && $status == FeedbizOrder::PROCESS_CANCEL) {
            die(Tools::displayError('Missing reason'));
        }

        $order = new FeedbizOrder($id_order);

        if (!Validate::isLoadedObject($order)) {
            die(Tools::displayError('Unable to load order id:'.$id_order));
        }

        // Orders States
        //
        $order_states = unserialize(Configuration::get('FEEDBIZ_ORDERS_STATES'));
        $id_canceled_state = $order_states ['FEEDBIZ_CR'];

        if ($id_canceled_state) {
            $order_state = new OrderState($id_canceled_state);

            if (!Validate::isLoadedObject($order_state)) {
                $pass = false;
            }
        } else {
            $pass = false;
        }

        if (!$pass) {
            die(Tools::displayError('Please configure canceled order state in your module configuration first.'));
        }

        if ($debug) {
            printf('Parameters: %s', $id_canceled_state);
        }

        switch ($status) {
            case FeedbizOrder::PROCESS_CANCEL:
                if (!$result = $order->changeOrderStatus($id_order, $status, $reason, $debug)) {
                    $message = $this->l('Unable to change the status');
                    $error = true;
                } else {
                    $message = $this->l('Order cancellation has been successfully scheduled');
                }
                break;
            case FeedbizOrder::REVERT_CANCEL:
                if (!$result = $order->changeOrderStatus($id_order, $status, null, $debug)) {
                    $message = $this->l('Unable to change the status');
                    $error = true;
                } else {
                    $message = $this->l('Order cancellation has been suspended');
                }
                break;
        }

        if (!$error) {
            // Check Access Tokens
            $this->token = Configuration::get('FEEDBIZ_TOKEN');
            $this->preproduction = Configuration::get('FEEDBIZ_PREPRODUCTION') ? true : false;

            $FeedBizWS = new FeedBizWebService($this->username, $this->token, $this->preproduction, $this->debug);

            $params = array(
                'token' => $this->token,
                'seller_order_id' => $id_order,
                'order_id' => $mp_order_id,
                'reason_id' => $reason
            );

            $result = $FeedBizWS->cancelOrder($params, 'cancelOrder', 'GET', true, $this->debug);

            $pass = isset($result->pass) ? trim((string)$result->pass) : false ;
            $response = isset($result->output) ? trim((string) $result->output) : null ;

            if (!isset($pass) || !$pass || $pass == 'false') {
                $message = $this->l('Error:') . $response;
                $error = true;
            } else {
                if (!$result = $order->changeOrderStatus($id_order, FeedBizOrder::CANCELED, null, $debug)) {
                    $message = $this->l('Unable to change the status to cancelled');
                    $error = true;
                }
            }
        }

        $json = Tools::jsonEncode(array(
            'error' => !$result || $error,
            'response' =>  isset($response) ? $response : $result,
            'result' => $message ? $message : ob_get_clean(),
        ));

        header('Content-Type: application/json');

        die($json);
    }
}

$feedbizCancelMode = new FeedbizCancelMode();
$feedbizCancelMode->dispatch();

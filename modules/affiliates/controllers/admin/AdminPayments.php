<?php
/**
* Affiliates
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
*
* @author    FMM Modules
* @copyright © Copyright 2021 - All right reserved
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* @category  FMM Modules
* @package   affiliates
*/

require(_PS_MODULE_DIR_.'affiliates/classes/paypal/PPBootStrap.php');

use PayPal\Types\AP\PayRequest;
use PayPal\Types\AP\Receiver;
use PayPal\Types\AP\ReceiverList;
use PayPal\Types\common\RequestEnvelope;
use PayPal\Service\AdaptivePaymentsService;
use PayPal\Types\AP\PaymentDetailsRequest;

class AdminPaymentsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'affiliate_payment';
        $this->className = 'Payment';
        $this->identifier = 'id_affiliate_payment';
        $this->lang = false;
        $this->deleted = false;
        $this->bootstrap = true;

        parent::__construct();
        $this->context = Context::getContext();

        $this->_select = 'SUM(rew.`ord_reward_value` + rew.`reg_reward_value`) AS request_amount, c.`email`, c.id_shop,
            CONCAT(c.`firstname`, \' \', c.`lastname`) AS affiliate, rew.`id_affiliate`';
        $this->_join = 'LEFT JOIN `'._DB_PREFIX_.'affiliate_reward` rew ON (a.`id_affiliate_reward` = rew.`id_affiliate_reward`)
            LEFT JOIN `'._DB_PREFIX_.'customer` c ON (c.`id_customer` = rew.`id_customer`)
            LEFT JOIN `'._DB_PREFIX_.'affiliate_referral` r ON (r.`id_affiliate_referral` = rew.`id_affiliate_referral`)';
        $this->_where = ' AND a.`status` = "pending" OR a.`status` = "cancelled" GROUP BY a.id_affiliate';

        $this->_use_found_rows = true;

        $this->fields_list = array(
            'id_affiliate'      => array(
                'title'         => $this->l('ID'),
                'width'         => 25,
                'orderby'       => true,
                'filter_key'    => 'a!id_affiliate',
                'filter_type'   => 'int',
                'order_key'     => 'id_affiliate',
                'orderway'      => 'desc'
            ),
            'affiliate'         => array(
                'title'         => $this->l('Affiliate'),
                'width'         => 'auto',
            ),
            'email'             => array(
                'title'         => $this->l('Email'),
                'width'         => 'auto',
            ),
            'request_amount'    => array(
                'type'          => 'price',
                'title'         => $this->l('Requested Amount'),
                'class'         => 'right',
                'badge_success' => true
            )
        );

        if (Shop::isFeatureActive()) {
            $this->fields_list['id_shop'] = array(
                'title'     => $this->l('Shop'),
                'width'     => 25,
                'align'     => 'center',
                'callback'  => 'getShopName',
            );
        }
    }

    public function getShopName($id_shop)
    {
        $shop = new Shop($id_shop);
        return $shop->name;
    }

    public function renderList()
    {
        $this->addRowAction('view');
        $this->tpl_list_vars['dashboard_link'] = $this->module->getAffiliateUrl();
        $this->context->smarty->assign('currentMenuTab', 'rewards');
        $this->context->smarty->assign('subMenuTab', 'withdraw');
        $menu = $this->module->getMenu();
        return $menu.parent::renderList();
    }

    public function initToolbar()
    {
        parent::initToolbar();
        $this->toolbar_title[] = $this->l('Pending Withdraw Requests');
        unset($this->toolbar_btn['new']);
    }

    public function renderView()
    {
        $wd_requests = array();
        $payment = new Payment((int)Tools::getValue('id_affiliate_payment'));
        if (isset($payment) && $payment) {
            $id_affiliate = $payment->id_affiliate;
            $wd_requests = Payment::getWdRequestsByAffiliate($id_affiliate);
        }

        $this->context->smarty->assign('wd_requests', $wd_requests);
        $this->context->smarty->assign('PAYPAL_USERNAME', Configuration::get('PAYPAL_USERNAME'));
        $this->context->smarty->assign('PAYPAL_API_PASSWORD', Configuration::get('PAYPAL_USERNAME'));

        $menu = $this->module->getMenu();
        return $menu.parent::renderView();
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addJs(_PS_MODULE_DIR_.'affiliates/views/js/jquery.form.js');
    }

    public function postProcess()
    {
        parent::postProcess();
        if (Tools::isSubmit('changeBulkStatus')) {
            $pay_request = 'not sent';
            $bulk_requests = Tools::getValue('withdraw_requests');
            $status = Tools::getValue('bulk_status');
            if (is_array($bulk_requests) && $bulk_requests) {
                foreach ($bulk_requests as $id_affiliate_payment) {
                    $pmUpd = false;
                    $payment = new Payment((int)$id_affiliate_payment);
                    $payment->status = $status;
                    $payment->upd_date = date('Y-m-d H:i:s');
                    $paid = 0;
                    if ($status == 'accepted') {
                        $paid = 1;
                        $pay_request = 'paid';
                    }

                    $id_affiliate_reward = (int)$payment->id_affiliate_reward;
                    if ($status == 'cancelled') {
                        $pmUpd = $payment->delete();
                    } else {
                        $pmUpd = $payment->update();
                    }

                    if ($pmUpd) {
                        $reward = new Rewards((int)$id_affiliate_reward);
                        $reward->is_paid = $paid;
                        $reward->pay_request = $pay_request;
                        $reward->update();
                    }

                    if ($paid == 1) {
                        $this->sendPaidAlert($payment);
                    }
                }
                $this->confirmations[] = $this->l('Payment status has been successfully updated.');
            } else {
                $this->errors[] = $this->l('Please select a request(s)');
            }
        }
    }

    public function ajaxProcessChangeStatus()
    {
        $pay_request = 'not sent';
        $id_affiliate_payment = (int)Tools::getValue('id_affiliate_payment');
        $status = (string)Tools::getValue('status');
        $payment = new Payment((int)$id_affiliate_payment);
        $payment->status = $status;
        $payment->upd_date = date('Y-m-d H:i:s');

        $paid = 0;
        $id = 0;
        $err = true;
        $msg = '';

        if ($status == 'accepted') {
            $paid = 1;
            $id = (int)$id_affiliate_payment;
            $pay_request = 'paid';
        }
        $reward = new Rewards((int)$payment->id_affiliate_reward);
        $reward->is_paid = $paid;
        $reward->pay_request = $pay_request;
        if ($payment->update() && $reward->update()) {
            $err = true;
            $msg = $this->l('Status successfully updated.');
        } else {
            $err = false;
            $msg = $this->l('Operation failed');
        }

        if ($paid == 1) {
            $this->sendPaidAlert($payment);
        }
        die(json_encode(array('res' => $err, 'message' => $msg, 'rid' => $id)));
    }

    /**
     * Process payment via Paypal API
     * @return json
     */
    public function ajaxProcessPayNow()
    {
        $msg = '';
        $id = 0;
        $paid = 0;
        $up = false;
        $res = true;
        $id_affiliate_payment = (int)Tools::getValue('id_affiliate_payment');
        $status = (string)Tools::getValue('status');

        $payment = new Payment((int)$id_affiliate_payment);
        $reward = new Rewards((int)$payment->id_affiliate_reward);
        $details = PaymentDetails::getPaymentDetailsByMethod($payment->id_affiliate, $payment->type);
        if (isset($details) && $payment->type == 1 && Validate::isEmail($details)) {
            $payment->status = $status;
            $payment->upd_date = date('Y-m-d H:i:s');
            $reward_amount = (!empty($reward->reg_reward_value))? (float)$reward->reg_reward_value : (float)$reward->ord_reward_value;
            if ($status == 'accepted') {
                // pay using paypal
                $result = $this->payPalPayment($details, $reward_amount, $payment->id_affiliate);
                if (empty($result)) {
                    $up = false;
                    $res = false;
                    $msg = $this->l('Operation Failed');
                } elseif ($result->responseEnvelope->ack == 'Success') {
                    $up = true;
                    $paid = 1;
                    $id = (int)$id_affiliate_payment;
                    $msg = $this->l('Transaction completed successfully.');
                } elseif ($result->responseEnvelope->ack == 'Failure') {
                    $up = false;
                    $msg = $result->error[0]->message;
                }

                $reward->is_paid = $paid;
                $reward->pay_request = $status;
                if ($up == true && ($payment->update() && $reward->update())) {
                    $res = true;
                } else {
                    $res = false;
                }
            }

            if ($paid == 1) {
                $this->sendPaidAlert($payment);
            }
        } else {
            $res = false;
            $msg = $this->l('Invalid Payment details');
        }
        die(json_encode(array('res' => $res, 'message' => $msg, 'rid' => $id)));
    }

    public function payPalPayment($receiver_email, $amount, $id_affiliate)
    {
        $c_token = Tools::getAdminTokenLite('AdminPayments');
        $c_index = $this->context->link->getAdminLink('AdminPayments', false);
        $params = '&id_affiliate='.(int)$id_affiliate.'&viewaffiliate_payment&';
        $protocol_link = (Configuration::get('PS_SSL_ENABLED') || Tools::usingSecureMode()) ? 'https://' : 'http://';
        $return_url = $protocol_link.Tools::getShopDomainSsl().__PS_BASE_URI__.(!Configuration::get('PS_REWRITING_SETTINGS') ? 'index.php' : '');

        // API details
        $config = array(
            'mode' => (Configuration::get('PAYPAL_MODE') == 1)? 'sandbox' : 'live',
            'acct1.UserName' => Configuration::get('PAYPAL_USERNAME'),
            'acct1.Password' => Configuration::get('PAYPAL_API_PASSWORD'),
            'acct1.Signature' => Configuration::get('PAYPAL_API_SIGNATURE'),
            'acct1.AppId' => Configuration::get('PAYPAL_APP_ID')
        );

        $receiver = array();
        $pay_request = new PayRequest();
        $receiver[0] = new Receiver();
        $receiver[0]->amount = $amount;
        $receiver[0]->email = (string)$receiver_email;

        $receiver_list = new ReceiverList($receiver);
        $pay_request->receiverList = $receiver_list;
        $pay_request->senderEmail = (Configuration::get('PAYPAL_EMAIL')? Configuration::get('PAYPAL_EMAIL') : (Configuration::get('PS_SHOP_EMAIL')? Configuration::get('PS_SHOP_EMAIL') : ''));

        $request_envelope = new RequestEnvelope('en_US');
        $pay_request->requestEnvelope = $request_envelope;
        $pay_request->actionType = 'PAY';
        $pay_request->cancelUrl = $return_url.'?'.$c_index.$params.$c_token;
        $pay_request->returnUrl = $return_url.'?'.$c_index.$params.$c_token;
        $pay_request->currencyCode = $this->context->currency->iso_code;
        $pay_request->ipnNotificationUrl = '';

        $adaptive_payments_service = new AdaptivePaymentsService($config);
        $pay_response = $adaptive_payments_service->Pay($pay_request);

        if ($pay_response->responseEnvelope->ack == 'Failure') {
            return $pay_response;
        }

        //Payment Request Details
        $payment_details_request = new PaymentDetailsRequest($request_envelope);
        $payment_details_request->payKey = $pay_response->payKey;
        $payment_details_response = $adaptive_payments_service->PaymentDetails($payment_details_request);

        return $payment_details_response;
    }

    protected function sendPaidAlert($payment)
    {
        $result = false;
        $affiliate = Affiliation::getAffiliateById($payment->id_affiliate);
        if (isset($payment) && isset($affiliate) && $affiliate) {
            if ($affiliate['email'] && Validate::isEmail($affiliate['email'])) {
                $vars = array(
                    '{email}' => (string)$affiliate['email'],
                    '{lastname_affiliate}' => (string)$affiliate['lastname'],
                    '{firstname_affiliate}' => (string)$affiliate['firstname'],
                    '{paid_date}' => date('Y-m-d H:i:s'),
                    '{reward_amount}' => Tools::displayPrice(Rewards::getRewardTotalById($payment->id_affiliate_reward), $this->context->currency),
                    '{payment_details}' => $payment->details,
                    '{payment_method}' => ($payment->type == 1)? 'Paypal' : 'Bank Wire',
                    '{my_account_url}' => $this->context->link->getPageLink('my-account', true, $this->context->language->id, null, false, $this->context->shop->id),
                );

                $result = Mail::Send(
                    (int)$this->context->language->id,
                    'reward_paid',
                    Mail::l('Congratulations, your reward has been paid', (int)$this->context->language->id),
                    $vars,
                    $affiliate['email'],
                    null,
                    null,
                    null,
                    null,
                    null,
                    dirname(__FILE__).'/../../mails/',
                    false
                );
            }
        }
        return $result;
    }
}

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

class AffiliatesMyAffiliatesModuleFrontController extends ModuleFrontController
{
    public $error = '';

    public function __construct()
    {
        parent::__construct();
        $this->display_column_left = false;
        $this->display_column_right = false;
        $this->context = Context::getContext();
    }

    public function initContent()
    {
        parent::initContent();
        $isBlocked = false;
        $isAffiliate = false;
        $id_customer = (int) $this->context->customer->id;
        $this->context->smarty->assign('id_module', $this->module->id);
        $affiliate = Affiliation::getAffiliateByCustomer($id_customer);
        if (isset($affiliate) && $affiliate) {
            $affiliate['sponsor'] = array();
            if (($referralCustomer = Referrals::getReferralByCustomer($id_customer))) {
                if (isset($referralCustomer['id_affiliate'])) {
                    $affiliate['sponsor'] = Affiliation::getAffiliateById($referralCustomer['id_affiliate']);
                }
            }
        }

        $request = '';
        $pdetails = '';
        $payment_request = '';
        $active_tab = '#affiliation_tab_1';
        $base_url = $this->context->shop->getBaseURL(false);

        if (!defined('_PS_BASE_URL_SSL_')) {
            $base_url = $this->context->shop->getBaseURL(true);
        }

        $url_email = (isset($affiliate) && $affiliate)? $base_url . '?src=email&ref=' . $affiliate['ref_key'] : '';

        $auto_approve = (int) Configuration::get(
            'AFFILIATE_AUTO_APPROVAL',
            null,
            $this->context->shop->id_shop_group,
            $this->context->shop->id
        );

        if (isset($affiliate) && $affiliate['id_affiliate']) {
            $isAffiliate = true;
            $isBlocked = (bool)!$affiliate['active'];
        }

        if (!($this->context->customer->logged) || (true === $isAffiliate && true === $isBlocked)) {
            Tools::redirect($this->context->link->getPageLink('my-account'));
        }

        // affiliation request
        if (Tools::isSubmit('sendAffiliateRequest') && Tools::getValue('sendAffiliateRequest')) {
            if (!empty($affiliate)) {
                $request = 'already sent';
            } else {
                $affiliation = new Affiliation();
                $id_affiliate_referral_aff = (int) $affiliation->getRefferalAffiliateId((int) $id_customer);
                if ($id_affiliate_referral_aff > 0) { //the user is already a referral - change level now
                    $count_level = (int) $affiliation->countAffiliateLevel((int) $id_affiliate_referral_aff);
                    if ($count_level <= 3) {
                        $count_level = $count_level + 1;
                    }
                    $affiliation->level = $count_level;
                }
                $affiliation->id_customer = (int) $id_customer;
                $affiliation->id_guest = (int) $this->context->cookie->id_guest;
                $affiliation->ref_key = Tools::passwdGen((Configuration::get('REFERAK_KEY_LEN', null, $this->context->shop->id_shop_group, $this->context->shop->id) ? (int) Configuration::get('REFERAK_KEY_LEN', null, $this->context->shop->id_shop_group, $this->context->shop->id) : 16), 'ALPHANUMERIC');
                $affiliation->active = 1;
                $affiliation->approved = ($auto_approve) ? 1 : 0;
                $affiliation->date_from = date('Y-m-d H:i:s');

                if ($affiliation->add()) {
                    $request = 'request success';
                    if ($auto_approve) {
                        $affiliate = Affiliation::getAffiliateByCustomer($id_customer);
                    }
                    // sending customer affiliate request to store admin
                    if (Configuration::get('PS_SHOP_EMAIL') && Validate::isEmail(Configuration::get('PS_SHOP_EMAIL'))) {
                        $vars = array(
                            '{email}' => (string) $this->context->customer->email,
                            '{lastname}' => (string) $this->context->customer->lastname,
                            '{firstname}' => (string) $this->context->customer->firstname,
                            '{req_date}' => date('Y-m-d H:i:s'),
                        );

                        Mail::Send(
                            (int) $this->context->language->id,
                            'affiliation_request',
                            Mail::l('New affiliation request', (int) $this->context->language->id),
                            $vars,
                            Configuration::get('PS_SHOP_EMAIL'),
                            null,
                            null,
                            null,
                            null,
                            null,
                            dirname(__FILE__) . '/../../mails/',
                            $this->context->shop->id
                        );
                    }
                } else {
                    $request = 'request failed';
                }
            }
        }

        // Mailing invitation to referral sponsor
        $invitation_sent = false;
        $nb_invitation = 0;
        if (Tools::isSubmit('inviteReferral')) {
            $active_tab = '#affiliation_tab_1';
            $referral_email = Tools::getValue('referralEmail');
            if ($referral_email && count($referral_email) >= 1) {
                if (!Tools::getValue('conditionsValided')) {
                    $this->error = 'conditions not valided';
                } else {
                    $referral_last_name = Tools::getValue('referralLastName');
                    $referral_first_name = Tools::getValue('referralFirstName');
                    $mails_exists = array();

                    foreach ($referral_email as $key => $ref_email) {
                        $ref_email = (string) $ref_email;
                        $ref_lastname = (string) $referral_last_name[$key];
                        $ref_firstname = (string) $referral_first_name[$key];

                        if (empty($ref_email) && empty($ref_lastname) && empty($ref_firstname)) {
                            continue;
                        } elseif (empty($ref_email) || !Validate::isEmail($ref_email)) {
                            $this->error = 'email invalid';
                        } elseif (empty($ref_firstname) || empty($ref_lastname) || !Validate::isName($ref_lastname) || !Validate::isName($ref_firstname)) {
                            $this->error = 'name invalid';
                        } elseif (AffiliateInvitations::isEmailExists($ref_email) || Customer::customerExists($ref_email)) {
                            $mails_exists[] = $ref_email;
                        } else {
                            $aff_customer = Affiliation::getAffiliateByCustomer((int) $id_customer);
                            $referral = new AffiliateInvitations();
                            $referral->id_affiliate = (int) $aff_customer['id_affiliate'];
                            $referral->firstname = $ref_firstname;
                            $referral->lastname = $ref_lastname;
                            $referral->email = $ref_email;
                            $referral->id_customer = (int) $id_customer;
                            $referral->date_add = date('Y:m:d H:i:s');

                            if (!$referral->validateFields(false)) {
                                $this->error = 'name invalid';
                            } else {
                                if ($referral->add()) {
                                    $vars = array(
                                        '{email}' => (string) $this->context->customer->email,
                                        '{lastname}' => (string) $this->context->customer->lastname,
                                        '{firstname}' => (string) $this->context->customer->firstname,
                                        '{email_referral}' => $ref_email,
                                        '{lastname_referral}' => $ref_lastname,
                                        '{firstname_referral}' => $ref_firstname,
                                        '{link}' => $url_email . '&inv=' . ((int) $referral->id) . '&t=' . md5((string) $ref_email),
                                        '{message}' => (Tools::getValue('ref_msg') ? Tools::getValue('ref_msg') : ''),
                                    );
                                    Mail::Send(
                                        (int) $this->context->language->id,
                                        'referral_invitation',
                                        Mail::l('You got an Invitation', (int) $this->context->language->id),
                                        $vars,
                                        $ref_email,
                                        $ref_firstname . ' ' . $ref_lastname,
                                        (string) Configuration::get('PS_SHOP_EMAIL'),
                                        (string) Configuration::get('PS_SHOP_NAME'),
                                        null,
                                        null,
                                        dirname(__FILE__) . '/../../mails/',
                                        false,
                                        $this->context->shop->id
                                    );
                                    $invitation_sent = true;
                                    $nb_invitation++;
                                } else {
                                    $this->error = 'cannot add referrals';
                                }
                            }
                        }

                        if ($this->error) {
                            break;
                        }
                    }

                    if ($nb_invitation > 0) {
                        unset($_POST);
                    }

                    //Not to stop the sending of e-mails in case of doubloon
                    if (count($mails_exists)) {
                        $this->error = 'email exists';
                    }
                }
            }
        }

        // Mailing revive
        $revive_sent = false;
        $nb_revive = 0;
        if (Tools::isSubmit('reviveReferral')) {
            $active_tab = '#affiliation_tab_2';
            if (Tools::getValue('referralChecked') && count($referrals_checked = Tools::getValue('referralChecked')) >= 1) {
                foreach ($referrals_checked as $id_affiliate_invitation) {
                    if (AffiliateInvitations::isSponsorReferral((int) $id_customer, (int) $id_affiliate_invitation)) {
                        $referral = new AffiliateInvitations((int) $id_affiliate_invitation);
                        $vars = array(
                            '{email}' => $this->context->customer->email,
                            '{lastname}' => $this->context->customer->lastname,
                            '{firstname}' => $this->context->customer->firstname,
                            '{email_referral}' => $referral->email,
                            '{lastname_referral}' => $referral->lastname,
                            '{firstname_referral}' => $referral->firstname,
                            '{link}' => $url_email . '&inv=' . ((int) $referral->id) . '&t=' . md5((string) $referral->email),
                        );
                        $referral->date_upd = date('Y-m-d H:i:s');
                        $referral->update();
                        Mail::Send(
                            (int) $this->context->language->id,
                            'referral_invitation',
                            Mail::l('You got an Invitation', (int) $this->context->language->id),
                            $vars,
                            $referral->email,
                            $referral->firstname . ' ' . $referral->lastname,
                            (string) Configuration::get('PS_SHOP_EMAIL'),
                            (string) Configuration::get('PS_SHOP_NAME'),
                            null,
                            null,
                            dirname(__FILE__) . '/../../mails/',
                            false,
                            $this->context->shop->id
                        );
                        $revive_sent = true;
                        $nb_revive++;
                    }
                }
            } else {
                $this->error = 'no revive checked';
            }
        }

        if (Tools::isSubmit('withdraw')) {
            $active_tab = '#affiliation_tab_4';
            $requested_payments = Tools::getValue('valid_rewards');
            $payment_type = (int) Tools::getValue('payment_detail');
            $payment_details = PaymentDetails::getPaymentDetailsByMethod((int) $affiliate['id_affiliate'], $payment_type);
            if (empty($payment_details) || !isset($payment_details)) {
                $payment_request = 'no_payment_method';
            }

            if (!isset($requested_payments) || !$requested_payments) {
                $payment_request = 'no_amount';
            } else {
                //$payment_type = (int)Configuration::get('PAYMENT_METHOD');
                foreach ($requested_payments as $id_affiliate_reward) {
                    $reward = new Rewards((int) $id_affiliate_reward);
                    $prev_request = Payment::getPaymentByReward($id_affiliate_reward, $reward->id_affiliate);
                    if (isset($prev_request)) {
                        $prev_request['type'] = (int) $prev_request['type'];
                    }

                    if ($reward->pay_request == 'accepted') {
                        $payment_request = 'paid';
                    } elseif ($reward->pay_request == 'pending' && isset($prev_request) && $prev_request['type'] == $payment_type) {
                        $payment_request = 'request_already_sent';
                    } elseif ($reward->pay_request == 'cancelled' && isset($prev_request) && $prev_request['type'] == $payment_type) {
                        $payment_request = 'request_cancelled';
                    } elseif ($payment_type && isset($prev_request) && $prev_request['type'] != $payment_type && isset($prev_request['id_affiliate_payment'])) {
                        $reward->pay_request = 'pending';
                        $payment = new Payment((int) $prev_request['id_affiliate_payment']);
                        $payment->type = (int) $payment_type;
                        $payment->details = (string) $payment_details;
                        $payment->status = 'pending';
                        $payment->requested_date = date('Y-m-d H:i:s');
                        $payment->upd_date = date('Y-m-d H:i:s');

                        if ($payment->update() && $reward->update()) {
                            $payment_request = 'request_success';
                        } else {
                            $payment_request = 'request_error';
                        }
                    } else {
                        $reward->pay_request = 'pending';
                        $payment = new Payment();
                        $payment->id_affiliate_reward = (int) $id_affiliate_reward;
                        $payment->id_affiliate = (int) $reward->id_affiliate;
                        $payment->type = (int) $payment_type;
                        $payment->details = (string) $payment_details;
                        $payment->status = 'pending';
                        $payment->requested_date = date('Y-m-d H:i:s');

                        if ($payment->add() && $reward->update()) {
                            $payment_request = 'request_success';
                        } else {
                            $payment_request = 'request_error';
                        }
                    }
                }
            }

            Tools::redirect(
                $this->context->link->getModuleLink(
                    'affiliates',
                    'myaffiliates',
                    array('payment_request' => $payment_request)
                )
            );
        }

        if (Tools::isSubmit('paymentDetails')) {
            $active_tab = '#affiliation_tab_5';
            $payment_details = Tools::getValue('payment_details');
            if (!isset($payment_details) || !$payment_details) {
                $pdetails = 'empty details';
            } else {
                foreach ($payment_details as $type => $detail) {
                    $id_affiliate_payment_details = (int) PaymentDetails::getPaymentIdByType((int) $affiliate['id_affiliate'], $type);
                    if (!empty($id_affiliate_payment_details)) {
                        $pd = new PaymentDetails($id_affiliate_payment_details);
                        $pd->upd_date = date('Y-m-d H:i:s');
                    } else {
                        $pd = new PaymentDetails();
                        $pd->date_add = date('Y-m-d H:i:s');
                    }

                    $pd->type = (int) $type;
                    $pd->details = (string) $detail;
                    $pd->id_affiliate = (int) $affiliate['id_affiliate'];
                    if (!empty($id_affiliate_payment_details) && $pd->update()) {
                        $pdetails = 'details saved';
                    } elseif ($pd->add()) {
                        $pdetails = 'details saved';
                    } else {
                        $pdetails = 'details error';
                    }
                }
            }
        }

        $total_rewards = Rewards::getCustomerTotalApprovedReward((int) $id_customer);
        $total_paid = Rewards::getCustomerTotalPaidRewards((int) $id_customer);
        $pending_rewards = Rewards::getCustomerPendingRewards((int) $id_customer);
        $awaiting_payments = Rewards::getCustomerAwaitingPayments($id_customer);
        $av_balance = (float) ($total_rewards - $total_paid - $pending_rewards - $awaiting_payments);

        $rewards = Rewards::getCustomerRewards((int) $id_customer);
        $valid_rewards = Rewards::getCustomerValidRewards($id_customer);

        $cms_link = '';
        if (Configuration::getGlobalValue('AFFILIATE_CONDITION')) {
            $cms_link = new CMS((int) Configuration::getGlobalValue('AFFILIATE_CONDITION'), $this->context->cookie->id_lang);
        }
        $ps_17 = (Tools::version_compare(_PS_VERSION_, '1.7', '>=') == true) ? 1 : 0;
        $banners = AffiliationBanners::getAllBanners();
        if (!empty($banners)) {
            foreach ($banners as &$banner) {
                if (preg_match('/\?/', $banner['href']) && !empty($banner['href'])) {
                    $banner['href'] = $banner['href'] . '&src=link&ref=' . $affiliate['ref_key'];
                } elseif (!preg_match('/\?/', $banner['href']) && !empty($banner['href'])) {
                    $banner['href'] = $banner['href'] . '?src=link&ref=' . $affiliate['ref_key'];
                }
            }
        }

        $dic_type = (string) Configuration::get(
            'REFERRAL_DISCOUNT_TYPE',
            null,
            $this->context->shop->id_shop_group,
            $this->context->shop->id
        );

        $ini_affiliate = (isset($affiliate) && $affiliate)? Affiliation::getAffiliateByRef($affiliate['ref_key']) : array();
        $coupon_core = new CartRule((int) Configuration::get(
            'ID_AFFILIATE_DISCOUNT_RULE',
            null,
            $this->context->shop->id_shop_group,
            $this->context->shop->id
        ));
        $code = $coupon_core->code;
        $discount = Tools::ps_round((float) Configuration::get(
            'REFERRAL_DISCOUNT_VALUE',
            null,
            $this->context->shop->id_shop_group,
            $this->context->shop->id
        ));
        $voucher_currency = Configuration::get(
            'REFERRAL_DISCOUNT_CURRENCY',
            null,
            $this->context->shop->id_shop_group,
            $this->context->shop->id
        );
        $voucher_currency = new Currency($voucher_currency);
        $voucher_currency = $voucher_currency->sign;
        if (!empty($ini_affiliate)) { //If affiliate has individual voucher settings
            $affiliate_cls = new Affiliation((int) $ini_affiliate['id_affiliate']);
            if ((int) $affiliate_cls->id_voucher > 0) {
                $ini_voucher = new CartRule((int) $affiliate_cls->id_voucher);
                $code = $ini_voucher->code;
                $discount = Tools::ps_round($affiliate_cls->individual_voucher, 2);
                $dic_type = 'amount';
                $voucher_currency = (int) Configuration::get('PS_CURRENCY_DEFAULT');
                $voucher_currency = new Currency($voucher_currency);
                $voucher_currency = $voucher_currency->sign;
            }
        }

        $wire_transfer_fee = (float) Configuration::get(
            'WIRETRANS_FEE',
            null,
            $this->context->shop->id_shop_group,
            $this->context->shop->id
        );
        $default_currency = (int) Configuration::get('PS_CURRENCY_DEFAULT');
        $store_currency = new Currency($default_currency);
        $wire_transfer_fee = Tools::displayPrice($wire_transfer_fee, $store_currency);

        $url_link = (isset($affiliate) && $affiliate)? $base_url . '?src=link&ref=' . $affiliate['ref_key'] : '';

        $this->context->smarty->assign(array(
            'error' => $this->error,
            'invitation_sent' => $invitation_sent,
            'revive_sent' => $revive_sent,
            'nbRevive' => $nb_revive,
            'ref_link' => (!empty($url_link)) ? $url_link : '',
            'nbInvitation' => $nb_invitation,
            'affiliations' => $affiliate,
            'request' => $request,
            'auto_approve' => $auto_approve,
            'ps_version' => _PS_VERSION_,
            'total_rewards' => $total_rewards,
            'total_paid' => $total_paid,
            'rewards' => $rewards,
            'pdetails' => $pdetails,
            'valid_rewards' => $valid_rewards,
            'pending_rewards' => $pending_rewards,
            'cms' => $cms_link,
            'av_balance' => ((isset($av_balance) && $av_balance > 0.00) ? $av_balance : 0.00),
            'payment_request' => $payment_request,
            'awaiting_payments' => $awaiting_payments,
            'wire_transfer_fee' => (float) Configuration::get('WIRETRANS_FEE', null, $this->context->shop->id_shop_group, $this->context->shop->id),
            'wire_transfer_fee_formatted' => $wire_transfer_fee,
            'PAYMENT_METHOD' => (Configuration::get('PAYMENT_METHOD', null, $this->context->shop->id_shop_group, $this->context->shop->id) ? explode(',', Configuration::get('PAYMENT_METHOD', null, $this->context->shop->id_shop_group, $this->context->shop->id)) : ''),
            'PAYMENT_DELAY_TIME' => Configuration::get('PAYMENT_DELAY_TIME', null, $this->context->shop->id_shop_group, $this->context->shop->id),
            'MINIMUM_AMOUNT' => Configuration::get('MINIMUM_AMOUNT', null, $this->context->shop->id_shop_group, $this->context->shop->id),
            'DELAY_TYPE' => Configuration::get('DELAY_TYPE', null, $this->context->shop->id_shop_group, $this->context->shop->id),
            'refferal_coupon_state' => (int) Configuration::get('REFERRAL_DISCOUNT_STATUS', null, $this->context->shop->id_shop_group, $this->context->shop->id),
            'ALL_CUSTOM_PAYMENTS' => PaymentMethod:: getPaymenMethods($this->context->language->id),
            'selected_custom_pm' => (Configuration::get('AFFILIATE_CUSTOM_PAYMENTS', null, $this->context->shop->id_shop_group, $this->context->shop->id) ? explode(',', Configuration::get('AFFILIATE_CUSTOM_PAYMENTS', null, $this->context->shop->id_shop_group, $this->context->shop->id)) : array()),
            'refferal_coupon_type' => $dic_type,
            'refferal_coupon_code' => $code,
            'refferal_coupon_disc' => $discount,
            'refferal_coupon_curr' => $voucher_currency,
            'active_tab' => (($active_tab) ? $active_tab : ''),
            'affCurrencySign' => $this->context->currency->iso_code,
            'currency' => $this->context->currency,
            'affCurrencyRate' => $this->context->currency->conversion_rate,
            'affCurrencyFormat' => $this->context->currency->format,
            'affCurrencyBlank' => $this->context->currency->blank,
            'mails_exists' => (isset($mails_exists) ? $mails_exists : array()),
            'pendingReferrals' => AffiliateInvitations::getPendingInvitations((int) $id_customer),
            'myReferrals' => Referrals::getApprovedReferralsByCustomer((int) $id_customer),
            'wire_transfer_details' => ((isset($affiliate) && $affiliate)? PaymentDetails::getPaymentDetailByType($affiliate['id_affiliate'], 3) : array()),
            'bankwire_details' => ((isset($affiliate) && $affiliate)? PaymentDetails::getPaymentDetailByType($affiliate['id_affiliate'], 2) : array()),
            'paypal_details' => ((isset($affiliate) && $affiliate)? PaymentDetails::getPaymentDetailByType($affiliate['id_affiliate'], 1) : array()),
            'AFFILIATE_FACEBOOK' => Configuration::get('AFFILIATE_FACEBOOK', null, $this->context->shop->id_shop_group, $this->context->shop->id),
            'AFFILIATE_TWITTER' => Configuration::get('AFFILIATE_TWITTER', null, $this->context->shop->id_shop_group, $this->context->shop->id),
            'AFFILIATE_GOOGLE' => Configuration::get('AFFILIATE_GOOGLE', null, $this->context->shop->id_shop_group, $this->context->shop->id),
            'AFFILIATE_DIGG' => Configuration::get('AFFILIATE_DIGG', null, $this->context->shop->id_shop_group, $this->context->shop->id),
            'banners' => $banners,
            'levels' => Levels:: getLevels(),
            'level_types' => $this->module->getLevelTypes(),
            'affiliate_img_dir' => $base_url . 'img/uploads' . DIRECTORY_SEPARATOR . 'affiliates/',
        ));
        $force_ssl = (Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE'));
        $this->context->smarty->assign('base_dir', _PS_BASE_URL_ . __PS_BASE_URI__);
        $this->context->smarty->assign('base_dir_ssl', _PS_BASE_URL_SSL_ . __PS_BASE_URI__);
        $this->context->smarty->assign('force_ssl', $force_ssl);

        $jquery_array = array();
        if (_PS_VERSION_ >= '8.0') {
            $folder = _PS_JS_DIR_ . 'jquery/';
            $component = '3.4.1';
            $file = 'jquery-' . $component . '.min.js';

            $jq_path = Media::getJSPath($folder . $file);
            $jquery_array[] = $jq_path;
            $this->context->smarty->assign([
                'jQuery_path' => $jquery_array[0],
            ]);
        } else {
            $jQuery_path = Media::getJqueryPath(_PS_JQUERY_VERSION_);
            if (is_array($jQuery_path) && isset($jQuery_path[0])) {
                $jQuery_path = $jQuery_path[0];
            }
            $this->context->smarty->assign(array('jQuery_path' => $jQuery_path));
        }

        $socials = (Configuration::get('AFFILIATE_SOCIALS', null, $this->context->shop->id_shop_group, $this->context->shop->id) ? explode(',', Configuration::get('AFFILIATE_SOCIALS', null, $this->context->shop->id_shop_group, $this->context->shop->id)) : array());
        // if (!$socials) {
        //     $socials = array('email', 'twitter', 'facebook', 'googleplus', 'linkedin', 'pinterest', 'stumbleupon', 'pocket', 'viber', 'messenger', 'vkontakte', 'telegram', 'line', 'whatsapp');
        // }

        $totalOrders = (int)Order::getCustomerNbOrders($this->context->customer->id);
        $allowedOrders = (int) Configuration::get('AFFILIATE_PROGRAM_ORDERS', null, $this->context->shop->id_shop_group, $this->context->shop->id);

        $this->context->smarty->assign('socials', json_encode($socials));
        $this->context->smarty->assign('social_label', (bool) Configuration::get('AFFILIATE_SOCIAL_LABELS', null, $this->context->shop->id_shop_group, $this->context->shop->id));
        $this->context->smarty->assign('isAccess', (0 === $allowedOrders || $totalOrders >= $allowedOrders)? true : false);
        if ($ps_17 > 0) {
            $this->context->smarty->assign('errors', $this->error);
            $this->context->smarty->assign(array('base_dir' => _PS_BASE_URL_ . __PS_BASE_URI__));
            $this->setTemplate('module:affiliates/views/templates/front/affiliations-17.tpl');
        } else {
            $this->setTemplate('affiliations.tpl');
        }
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addJqueryPlugin('fancybox');
        if (true === Tools::version_compare(_PS_VERSION_, '1.7', '>=')) {
            $this->addCSS(_PS_MODULE_DIR_ . $this->module->name . '/views/css/affiliate_tabcontent.css');
            $this->addJS(_PS_MODULE_DIR_ . $this->module->name . '/views/js/affiliate_tabcontent.js');
        }
        $this->addCSS(_PS_MODULE_DIR_ . $this->module->name . '/views/css/js-social/jssocials.css');
        $this->addCSS(_PS_MODULE_DIR_ . $this->module->name . '/views/css/js-social/jssocials-theme-' . Configuration::get(
            'AFFILIATE_SOCIAL_THEME',
            null,
            $this->context->shop->id_shop_group,
            $this->context->shop->id
        ) . '.css');
        $this->addJS(_PS_MODULE_DIR_ . $this->module->name . '/views/js/js-social/jssocials.js');
    }
}

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

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once dirname(__FILE__) . '/models/Affiliation.php';
include_once dirname(__FILE__) . '/models/Referrals.php';
include_once dirname(__FILE__) . '/models/Rules.php';
include_once dirname(__FILE__) . '/models/Levels.php';
include_once dirname(__FILE__) . '/models/AffiliationBanners.php';
include_once dirname(__FILE__) . '/models/Rewards.php';
include_once dirname(__FILE__) . '/models/Payment.php';
include_once dirname(__FILE__) . '/models/PaymentDetails.php';
include_once dirname(__FILE__) . '/models/Affiliate_Invitations.php';
include_once dirname(__FILE__) . '/models/PaymentMethod.php';

class Affiliates extends Module
{
    protected $id_shop = null;

    protected $id_shop_group = null;

    protected static $subMenuTab = 'home';

    protected static $currentFormTab = 'home';

    private $tab_class = 'AdminAffiliation';

    private $modHooks = array();

    public function __construct()
    {
        $this->name = 'affiliates';
        $this->tab = 'advertising_marketing';
        $this->version = '2.3.0';
        $this->author = 'FMM Modules';
        $this->bootstrap = true;
        $this->module_key = '76b627baaa2c580cf2ee0bc281786da7';
        $this->author_address = '0xcC5e76A6182fa47eD831E43d80Cd0985a14BB095';

        parent::__construct();

        $this->displayName = $this->l('Affiliates');
        $this->description = $this->l('This module enables an affiliate program in your site.');

        $this->modHooks = array(
            'footer',
            'ModuleRoutes',
            'displayHeader',
            'actionValidateOrder',
            'actionCustomerAccountAdd',
            'actionOrderStatusPostUpdate',
            'displayCustomerAccount',
            'CreateAccountForm',
            'displayProductButtons',
            'actionAuthentication',
            'displayBackOfficeHeader',
            'displayAdminListBefore',
            'displayAdminListAfter',
            'displayRightColumnProduct',
            /* GDPR compliant hooks */
            'registerGDPRConsent',
            'actionDeleteGDPRCustomer',
            'actionExportGDPRData'
        );

        if ($this->id_shop === null || !Shop::isFeatureActive()) {
            $this->id_shop = Shop::getContextShopID();
        } else {
            $this->id_shop = $this->context->shop->id;
        }
        if ($this->id_shop_group === null || !Shop::isFeatureActive()) {
            $this->id_shop_group = Shop::getContextShopGroupID();
        } else {
            $this->id_shop_group = $this->context->shop->id_shop_group;
        }
    }

    public function install()
    {
        if (!Affiliation::existsTab($this->tab_class)) {
            if (!$this->addTab()) {
                return false;
            }
        }

        include dirname(__FILE__) . '/sql/install.php';

        if (parent::install()
            && $this->registerHook($this->modHooks)
            && $this->installConfigurations()
            && $this->createAffiliateGroup()) {
            if (!is_dir(_PS_IMG_DIR_ . 'uploads')) {
                mkdir(_PS_IMG_DIR_ . 'uploads', 0777, true);
            }
            if (!is_dir(_PS_IMG_DIR_ . 'uploads' . DIRECTORY_SEPARATOR . 'affiliates')) {
                mkdir(_PS_IMG_DIR_ . 'uploads' . DIRECTORY_SEPARATOR . 'affiliates', 0777, true);
            }
            return true;
        }
        return false;
    }

    public function uninstall()
    {
        if (!$this->removeTab()) {
            return false;
        }

        if (parent::uninstall()
            && $this->unregisterHook('footer')
            && $this->unregisterHook('newOrder')
            && $this->unregisterHook('ModuleRoutes')
            && $this->unregisterHook('displayHeader')
            && $this->unregisterHook('actionCustomerAccountAdd')
            && $this->unregisterHook('actionOrderStatusPostUpdate')
            && $this->unregisterHook('displayBackOfficeHeader')
            && $this->unregisterHook('displayProductButtons')
            && $this->unregisterHook('displayRightColumnProduct')
            && $this->unregisterHook('ActionAuthentication')
            && $this->unregisterHook('displayCustomerAccount')
            && $this->unregisterHook('CreateAccountForm')
            && $this->unregisterHook('registerGDPRConsent')
            && $this->unregisterHook('actionDeleteGDPRCustomer')
            && $this->unregisterHook('actionExportGDPRData')
            && $this->unregisterHook('displayAdminListBefore')
            && $this->unregisterHook('displayAdminListAfter')
            && $this->unInstallConfiguration()
            && $this->deleteAffiliateGroup()
            && $this->deleteDiscountVoucher()) {
                include dirname(__FILE__) . '/sql/uninstall.php';
                return true;
        }
        return false;
    }

    private function installConfigurations()
    {
        Configuration::updateValue(
            'AFFILIATE_GROUPS',
            (int) Configuration::get('PS_CUSTOMER_GROUP'),
            false,
            $this->context->shop->id_shop_group,
            $this->context->shop->id
        );
        Configuration::updateValue(
            'AFFILIATE_CONDITION',
            (int) Configuration::get('PS_CONDITIONS_CMS_ID'),
            false,
            $this->context->shop->id_shop_group,
            $this->context->shop->id
        );
        Configuration::updateValue(
            'AFFILIATE_SHOPS',
            $this->context->shop->id
        );
        Configuration::updateValue(
            'PAYMENT_METHOD',
            '2',
            false,
            $this->context->shop->id_shop_group,
            $this->context->shop->id
        );
        Configuration::updateValue(
            'APPROVAL_STATES',
            '2',
            false,
            $this->context->shop->id_shop_group,
            $this->context->shop->id
        );
        Configuration::updateValue(
            'CANCEL_STATES',
            '6',
            false,
            $this->context->shop->id_shop_group,
            $this->context->shop->id
        );
        Configuration::updateValue(
            'MINIMUM_AMOUNT',
            50,
            false,
            $this->context->shop->id_shop_group,
            $this->context->shop->id
        );
        Configuration::updateValue(
            'PAYMENT_DELAY_TIME',
            45,
            false,
            $this->context->shop->id_shop_group,
            $this->context->shop->id
        );
        Configuration::updateValue(
            'REFERAK_KEY_LEN',
            16,
            false,
            $this->context->shop->id_shop_group,
            $this->context->shop->id
        );
        Configuration::updateValue(
            'AFFILIATE_PROGRAM_ORDERS',
            0,
            false,
            $this->context->shop->id_shop_group,
            $this->context->shop->id
        );
        Configuration::updateValue(
            'REFERRAL_REWARD_VORDERS',
            1,
            false,
            $this->context->shop->id_shop_group,
            $this->context->shop->id
        );
        Configuration::updateValue(
            'REFERRAL_REWARD_SPPRODUCTS',
            1,
            false,
            $this->context->shop->id_shop_group,
            $this->context->shop->id
        );
        Configuration::updateValue(
            'DELAY_TYPE',
            'd',
            false,
            $this->context->shop->id_shop_group,
            $this->context->shop->id
        );
        Configuration::updateValue(
            'ID_AFFILIATE_DISCOUNT_RULE',
            0,
            false,
            $this->context->shop->id_shop_group,
            $this->context->shop->id
        );
        Configuration::updateValue(
            'AFFILIATE_FACEBOOK',
            1,
            false,
            $this->context->shop->id_shop_group,
            $this->context->shop->id
        );
        Configuration::updateValue(
            'AFFILIATE_TWITTER',
            1,
            false,
            $this->context->shop->id_shop_group,
            $this->context->shop->id
        );
        Configuration::updateValue(
            'AFFILIATE_GOOGLE',
            1,
            false,
            $this->context->shop->id_shop_group,
            $this->context->shop->id
        );
        Configuration::updateValue(
            'AFFILIATE_DIGG',
            1,
            false,
            $this->context->shop->id_shop_group,
            $this->context->shop->id
        );
        Configuration::updateValue(
            'REFERRAL_DISCOUNT_STATUS',
            0,
            false,
            $this->context->shop->id_shop_group,
            $this->context->shop->id
        );
        Configuration::updateValue(
            'PAYPAL_MODE',
            1,
            false,
            $this->context->shop->id_shop_group,
            $this->context->shop->id
        );
        Configuration::updateValue(
            'AFFILIATE_AUTO_APPROVAL',
            0,
            false,
            $this->context->shop->id_shop_group,
            $this->context->shop->id
        );
        Configuration::updateValue(
            'AFFILIATE_SOCIAL_THEME',
            'classic',
            false,
            $this->context->shop->id_shop_group,
            $this->context->shop->id
        );
        Configuration::updateValue(
            'AFFILIATE_SOCIAL_LABELS',
            1,
            false,
            $this->context->shop->id_shop_group,
            $this->context->shop->id
        );
        Configuration::updateValue(
            'AFFILIATE_SOCIALS',
            1,
            false,
            $this->context->shop->id_shop_group,
            $this->context->shop->id
        );
        return true;
    }

    private function unInstallConfiguration()
    {
        Configuration::deleteByName('AFFILIATE_GROUPS');
        Configuration::deleteByName('AFFILIATE_CONDITION');
        Configuration::deleteByName('PAYMENT_METHOD');
        Configuration::deleteByName('MINIMUM_AMOUNT');
        Configuration::deleteByName('PAYMENT_DELAY_TIME');
        Configuration::deleteByName('REFERAK_KEY_LEN');
        Configuration::deleteByName('AFFILIATE_PROGRAM_ORDERS');
        Configuration::deleteByName('REFERRAL_REWARD_VORDERS');
        Configuration::deleteByName('REFERRAL_REWARD_SPPRODUCTS');
        Configuration::deleteByName('DELAY_TYPE');
        Configuration::deleteByName('APPROVAL_STATES');
        Configuration::deleteByName('CANCEL_STATES');
        Configuration::deleteByName('AFFILIATE_FACEBOOK');
        Configuration::deleteByName('AFFILIATE_TWITTER');
        Configuration::deleteByName('AFFILIATE_GOOGLE');
        Configuration::deleteByName('AFFILIATE_DIGG');
        Configuration::deleteByName('referral_welcom_msg');
        Configuration::deleteByName('REFERRAL_DISCOUNT_STATUS');
        Configuration::deleteByName('REFERRAL_DISCOUNT_TYPE');
        Configuration::deleteByName('REFERRAL_DISCOUNT_VALUE');
        Configuration::deleteByName('REFERRAL_DISCOUNT_CURRENCY');
        Configuration::deleteByName('PAYPAL_MODE');
        Configuration::deleteByName('PAYPAL_EMAIL');
        Configuration::deleteByName('PAYPAL_USERNAME');
        Configuration::deleteByName('PAYPAL_API_PASSWORD');
        Configuration::deleteByName('PAYPAL_API_SIGNATURE');
        Configuration::deleteByName('PAYPAL_APP_ID');
        Configuration::deleteByName('AFFILIATE_AUTO_APPROVAL');
        Configuration::deleteByName('AFFILIATE_SOCIAL_THEME');
        Configuration::deleteByName('AFFILIATE_SOCIAL_LABELS');
        Configuration::deleteByName('AFFILIATE_SOCIALS');
        Configuration::deleteByName('AFFILIATE_SHOPS');
        return true;
    }

    private function addTab()
    {
        $return = true;
        $tab = new Tab();
        $tab->id_parent = 0;
        $tab->module = $this->name;
        $tab->class_name = $this->tab_class;
        $tab->name[(int) Configuration::get('PS_LANG_DEFAULT')] = $this->l('Affiliates');
        $return &= $tab->add();

        $subtab1 = new Tab();
        $subtab1->class_name = 'AdminAffiliates';
        $subtab1->id_parent = $tab->id;
        $subtab1->module = $this->name;
        $subtab1->name[(int) Configuration::get('PS_LANG_DEFAULT')] = $this->l('Manage Affiliates');
        $return &= $subtab1->add();

        $tab_002 = new Tab();
        $tab_002->class_name = 'AdminAffiliatesConversion';
        $tab_002->id_parent = $tab->id;
        $tab_002->module = $this->name;
        $tab_002->name[(int) Configuration::get('PS_LANG_DEFAULT')] = $this->l('Convert Affiliates');
        $return &= $tab_002->add();

        $subtab2 = new Tab();
        $subtab2->class_name = 'AdminReferrals';
        $subtab2->id_parent = $tab->id;
        $subtab2->module = $this->name;
        $subtab2->name[(int) Configuration::get('PS_LANG_DEFAULT')] = $this->l('Referrals');
        $return &= $subtab2->add();

        $subtab3 = new Tab();
        $subtab3->class_name = 'AdminLevels';
        $subtab3->id_parent = $tab->id;
        $subtab3->module = $this->name;
        $subtab3->name[(int) (Configuration::get('PS_LANG_DEFAULT'))] = $this->l('Order Reward');
        $return &= $subtab3->add();

        $subtab4 = new Tab();
        $subtab4->class_name = 'AdminRules';
        $subtab4->id_parent = $tab->id;
        $subtab4->module = $this->name;
        $subtab4->name[(int) Configuration::get('PS_LANG_DEFAULT')] = $this->l('Rules');
        $return &= $subtab4->add();

        $subtab001 = new Tab();
        $subtab001->class_name = 'AdminAffiliationBanners';
        $subtab001->id_parent = $tab->id;
        $subtab001->module = $this->name;
        $subtab001->name[(int) Configuration::get('PS_LANG_DEFAULT')] = $this->l('Banners');
        $return &= $subtab001->add();

        $subtab002 = new Tab();
        $subtab002->class_name = 'AdminAffiliationDiscounts';
        $subtab002->id_parent = $tab->id;
        $subtab002->module = $this->name;
        $subtab002->name[(int) Configuration::get('PS_LANG_DEFAULT')] = $this->l('Discounts');
        $return &= $subtab002->add();

        $subtab5 = new Tab();
        $subtab5->class_name = 'AdminPayments';
        $subtab5->id_parent = $tab->id;
        $subtab5->module = $this->name;
        $subtab5->name[(int) (Configuration::get('PS_LANG_DEFAULT'))] = $this->l('Withdraw Requests');
        $return &= $subtab5->add();

        $subtab6 = new Tab();
        $subtab6->class_name = 'AdminAffiliateStates';
        $subtab6->id_parent = $tab->id;
        $subtab6->module = $this->name;
        $subtab6->name[(int) (Configuration::get('PS_LANG_DEFAULT'))] = $this->l('Statistics');
        $return &= $subtab6->add();
        
        return $return;
    }

    private function removeTab()
    {
        $tabClasses = [
            'AdminAffiliates',
            'AdminAffiliatesConversion',
            'AdminReferrals',
            'AdminLevels',
            'AdminRules',
            'AdminAffiliationBanners',
            'AdminAffiliationDiscounts',
            'AdminPayments',
            'AdminAffiliateStates',
            $this->tab_class,
        ];

        $return = true;
        foreach ($tabClasses as $class) {
            if (Validate::isLoadedObject($tab = Tab::getInstanceFromClassName($class))) {
                $return &= $tab->delete();
            }
        }
        return $return;
    }

    public function hookModuleRoutes()
    {
        return array(
            'module-affiliates-myaffiliates' => array(
                'controller' => 'myaffiliates',
                'rule' => 'affiliates',
                'keywords' => array(),
                'params' => array(
                    'fc' => 'module',
                    'module' => $this->name,
                ),
            ),
        );
    }

    public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addCSS($this->_path . 'views/css/admin.css');
        if ($this->context->controller->controller_name == 'AdminModules' && Tools::getValue('configure') == 'affiliates') {
            $this->context->controller->addJquery();
            $this->context->controller->addJqueryPlugin(array('fancybox'));
            $this->context->controller->addJS(array(
                _PS_JS_DIR_ . 'tiny_mce/tiny_mce.js',
                _PS_JS_DIR_ . 'admin/tinymce.inc.js',
                _PS_JS_DIR_ . 'admin.js',
                _PS_JS_DIR_ . 'admin/product.js',
            ));
        }
        if (get_class($this->context->controller) == 'AdminLevelsController') {
            $this->context->controller->addCSS($this->_path . 'views/css/bo_' . $this->name . '.css');
            $this->context->controller->addJquery();
            $this->context->controller->addJs($this->_path . 'views/js/' . $this->name . '.js');
        }
        if (get_class($this->context->controller) == 'AdminAffiliatesController') {
            $this->context->controller->addJquery();
            $this->context->controller->addJs($this->_path . 'views/js/gencode.js');
        }
        // show alerts
        $this->context->smarty->assign(array(
            'count' => abs(Affiliation::countAffiliates()),
            'controller' => $this->context->controller->controller_name,
            'id_affiliate' => (Tools::getValue('id_affiliate') ? (int) Tools::getValue('id_affiliate') : '0'),
            'key_len' => (Configuration::get('REFERAK_KEY_LEN') ? (int) Configuration::get('REFERAK_KEY_LEN') : 16),
            'ref_key' => ((Tools::getValue('id_affiliate')) ? Affiliation::getRefKey((int) Tools::getValue('id_affiliate')) : ''),
        ));
        return $this->display(__FILE__, 'views/templates/admin/alerts.tpl');
    }

    public function hookDisplayHeader()
    {
        if (Tools::version_compare(_PS_VERSION_, '1.7', '>=') == true) {
            $this->context->controller->addJqueryPlugin('fancybox');
        }

        if (Dispatcher::getInstance()->getController() == 'myaffiliates') {
            $this->context->controller->addJs($this->_path . 'views/js/tools.js');
        }
        $this->context->smarty->assign(array(
            'ok_label' => $this->l('Ok'),
            'req_error_msg' => $this->l('You must agree to the terms and condidions of Affiliate Program'),
            'active_tab' => '#affiliation_tab_1',
            'MINIMUM_AMOUNT' => Configuration::get('MINIMUM_AMOUNT', null, $this->context->shop->id_shop_group, $this->context->shop->id),
            'affCurrencySign' => $this->context->currency->sign,
            'affCurrencyRate' => $this->context->currency->conversion_rate,
            'affCurrencyFormat' => $this->context->currency->format,
            'affCurrencyBlank' => $this->context->currency->blank,
            'affcurrency' => $this->context->currency,
        ));
        return $this->display(__FILE__, 'views/templates/hook/aff_header.tpl');
    }

    public function hookFooter()
    {
        $id_guest = (int) Context::getContext()->cookie->id_guest;
        if ($id_guest <= 0) {
            $cookie = Context::getContext()->cookie;
            Guest::setNewGuest($cookie);
        }

        $selectedShops = Configuration::get('AFFILIATE_SHOPS');
        $shops = (!empty(trim($selectedShops)))? explode(',', $selectedShops) : [];
        if ((Shop::isFeatureActive() && isset($shops) && in_array($this->context->shop->id, $shops)) || !Shop::isFeatureActive() || empty($shops)) {
            $ref = (string) Tools::getValue('ref');
            $src = (string) Tools::getValue('src');
            $this->context->controller->addJqueryPlugin('fancybox');
            $this->context->controller->addCSS($this->_path . 'views/css/affiliate_tabcontent.css');
            $this->context->controller->addJS($this->_path . 'views/js/affiliate_tabcontent.js');
            $sp = false;
            $res = false;
            if (!empty($ref) && !Affiliation::affiliateExists($ref, $this->context->cookie->id_shop)) {
                $sp = true;
                $this->context->controller->errors[] = $this->l('Invalid referral link.');
            } elseif (!empty($ref) && $src == 'link' && Affiliation::affiliateExists($ref)) {
                $sp = true;
                $self_ref = Affiliation::SelfRef((int) $this->context->customer->id, (int) $this->context->cookie->id_guest);
                if (Referrals::isGuestReferralExists((int) $this->context->cookie->id_guest)) {
                    $this->context->controller->errors[] = $this->l('You have already been referred.');
                } elseif (isset($self_ref) && $self_ref == $ref) {
                    $this->context->controller->errors[] = $this->l('You cannot add yourself as a referral.');
                } else {
                    $aff_customer = Affiliation::getAffiliateByRef($ref);
                    if (isset($aff_customer)) {
                        $referral = new Referrals();
                        $referral->id_affiliate = (int) $aff_customer['id_affiliate'];
                        $referral->id_customer = (int) $this->context->cookie->id_customer;
                        $referral->id_guest = (int) $this->context->cookie->id_guest;
                        $referral->active = 1;
                        // $referral->approved = 0;
                        $referral->source = 'link';
                        $referral->date_from = date('Y-m-d H:i:s');
                        $referral->date_add = date('Y-m-d H:i:s');

                        if (!$referral->add()) {
                            $this->context->controller->errors[] = $this->l('Operation failed, something went wrong.');
                        } else {
                            $res = true;
                        }
                    }
                }
            } elseif (!empty($ref) && $src == 'email' && Affiliation::affiliateExists($ref)) {
                $sp = true;
                $token = (string) Tools::getValue('t');
                $id_invitation = (int) Tools::getValue('inv');

                if (AffiliateInvitations::isIdExists($id_invitation)) {
                    $ref_guest = new AffiliateInvitations((int) $id_invitation);
                    $hash = md5($ref_guest->email);
                    if ((isset($this->context->customer) && isset($this->context->customer->email) && $token != md5($this->context->customer->email))) {
                        $this->context->controller->errors[] = $this->l('Operation failed, mismatch email');
                    } elseif ($hash == $token) {
                        $referral = new Referrals();
                        $referral->id_affiliate = (int) $ref_guest->id_affiliate;
                        $referral->id_customer = (int) $this->context->customer->id;
                        $referral->id_guest = (int) $this->context->cookie->id_guest;
                        $referral->active = 1;
                        // $referral->approved = 0;
                        $referral->source = 'email';
                        $referral->date_from = date('Y-m-d H:i:s');
                        $referral->date_add = date('Y-m-d H:i:s');
                        if ($referral->add()) {
                            $res = true;
                            $ref_guest->id_affiliate_referral = $referral->id;
                            $ref_guest->update();
                        } else {
                            $this->context->controller->errors[] = $this->l('Operation failed, something went wrong.');
                        }
                    } else {
                        $this->context->controller->errors[] = $this->l('Operation failed, invalid token');
                    }
                }
            }
            if ($sp != false) {
                $discount = 0.00;
                $code = '';
                if (Configuration::get('REFERRAL_DISCOUNT_STATUS', null, $this->context->shop->id_shop_group, $this->context->shop->id)
                    && (Configuration::get('ID_AFFILIATE_DISCOUNT_RULE', null, $this->context->shop->id_shop_group, $this->context->shop->id) > 0)
                    && Affiliation::cartRuleExists(Configuration::get('ID_AFFILIATE_DISCOUNT_RULE', null, $this->context->shop->id_shop_group, $this->context->shop->id))) {
                    $coupon = new CartRule((int) Configuration::get('ID_AFFILIATE_DISCOUNT_RULE', null, $this->context->shop->id_shop_group, $this->context->shop->id));
                    $coupon->quantity = ($coupon->quantity <= 0) ? 1000 : $coupon->quantity;
                    $coupon->date_to = (strtotime($coupon->date_to) <= strtotime(date('Y:m:d H:i:s'))) ? date('Y-m-d H:i:s', strtotime('+5 years')) : $coupon->date_to;
                    $code = $coupon->code;
                    if ($coupon->update()) {
                        $discount = Tools::ps_round((float) Configuration::get('REFERRAL_DISCOUNT_VALUE', null, $this->context->shop->id_shop_group, $this->context->shop->id));
                    }
                }
                $dic_type = (string) Configuration::get('REFERRAL_DISCOUNT_TYPE', null, $this->context->shop->id_shop_group, $this->context->shop->id);
                $ini_affiliate = Affiliation::getAffiliateByRef($ref);
                if (!empty($ini_affiliate)) { //If affiliate has individual voucher settings
                    $affiliate = new Affiliation((int) $ini_affiliate['id_affiliate']);
                    if ((int) $affiliate->id_voucher > 0) {
                        $ini_voucher = new CartRule((int) $affiliate->id_voucher);
                        $code = $ini_voucher->code;
                        $discount = Tools::ps_round($affiliate->individual_voucher, 2);
                        $dic_type = 'amount';
                    }
                }
                $this->context->smarty->assign(array(
                    'errors' => $this->context->controller->errors,
                    'result' => (int) $res,
                    'welcom_message' => Configuration::get('referral_welcom_msg', $this->context->language->id, $this->context->shop->id_shop_group, $this->context->shop->id),
                    'discount' => $discount,
                    'code' => $code,
                    'ps_version' => _PS_VERSION_,
                    'discount_type' => $dic_type,
                ));
                
                $jquery_array = array();
                if (_PS_VERSION_ >= '8.0') {
                    $folder = _PS_JS_DIR_ . 'jquery/';
                    $component = '3.4.1';
                    $file = 'jquery-' . $component . '.min.js';

                    $jq_path = Media::getJSPath($folder . $file);
                    $jquery_array[] = $jq_path;
                    $this->context->smarty->assign([
                        'jQuery_path' => $jquery_array,
                    ]);
                } else {
                    $jQuery_path = Media::getJqueryPath(_PS_JQUERY_VERSION_);
                    if (is_array($jQuery_path) && isset($jQuery_path[0])) {
                        $jQuery_path = $jQuery_path[0];
                    }
                    $this->context->smarty->assign(array('jQuery_path' => $jQuery_path));
                }

                

                if (Tools::version_compare(_PS_VERSION_, '1.7', '>=') === true) {
                    $this->context->smarty->assign(array('base_dir' => _PS_BASE_URL_ . __PS_BASE_URI__));
                    return $this->display(__FILE__, 'views/templates/hook/aff_footer-17.tpl');
                } else {
                    return $this->display(__FILE__, 'views/templates/hook/aff_footer.tpl');
                }
            }
        }
    }

    public function hookCreateAccountForm()
    {
        if (isset($this->context->cookie->id_guest) && ($referral = Referrals::isGuestReferralExists($this->context->cookie->id_guest, true))) {
            $sponsor = Affiliation::getAffiliateById($referral['id_affiliate']);
            if (isset($sponsor) && $sponsor) {
                $this->context->smarty->assign('sponsor', $sponsor);
                return $this->display(__FILE__, 'referral-signup.tpl');
            }
        }
    }

    public function hookDisplayCustomerAccount()
    {
        $affiliate_groups = (Configuration::get('AFFILIATE_GROUPS', null, $this->context->shop->id_shop_group, $this->context->shop->id) ? explode(',', Configuration::get('AFFILIATE_GROUPS', null, $this->context->shop->id_shop_group, $this->context->shop->id)) : '');
        $this->context->smarty->assign('ps_version', _PS_VERSION_);
        $ps_17 = (Tools::version_compare(_PS_VERSION_, '1.7', '>=') == true) ? 1 : 0;
        $this->context->smarty->assign('ps_17', (int) $ps_17);
        $selectedShops = Configuration::get('AFFILIATE_SHOPS');
        $shops = (!empty(trim($selectedShops)))? explode(',', $selectedShops) : [];

        $isBlocked = false;
        $isAffiliate = false;
        if (($id_affiliate = Affiliation::getIdByCustomer($this->context->customer->id)) && Validate::isLoadedObject($affiliate = new Affiliation((int) $id_affiliate))) {
            $isAffiliate = true;
            $isBlocked = (bool) !$affiliate->active;
        }
        if (!$affiliate_groups) {
            $affiliate_groups = array();
        }
        if ((Shop::isFeatureActive() && isset($shops) && in_array($this->context->shop->id, $shops)) || !Shop::isFeatureActive() || empty($shops)) {
            $customerGroups = Customer::getGroupsStatic($this->context->customer->id);
           
            $groupExist = array_intersect($affiliate_groups, $customerGroups);
            if ((isset($affiliate_groups) && $affiliate_groups && isset($groupExist) && $groupExist) || empty($affiliate_groups)) {
                if ((true === $isAffiliate && false === $isBlocked) || false === $isAffiliate) {
                    return $this->display(__FILE__, 'my-account.tpl');
                }
            }
        }
    }

    public function hookActionCustomerAccountAdd($params)
    {
        $multishop = false;
        $selectedShops = Configuration::get('AFFILIATE_SHOPS');
        $shops = (!empty(trim($selectedShops)))? explode(',', $selectedShops) : [];
        if ((Shop::isFeatureActive() && isset($shops) && in_array($this->context->shop->id, $shops)) || !Shop::isFeatureActive() || empty($shops)) {
            $multishop = true;
        }

        if ($multishop && isset($params) && isset($params['newCustomer'])) {
            $id_customer = (int) $params['newCustomer']->id;
            $id_guest = (int) ($this->context->cookie->id_guest) ? $this->context->cookie->id_guest : AffiliateInvitations::getGuestId($id_customer);
            $email = (string) $params['newCustomer']->email;
            $reward_value = 0;

            $id_referral = ((AffiliateInvitations::getRefIdByEmail($email)) ? AffiliateInvitations::getRefIdByEmail($email) : Referrals::getReferralsGuestById($id_guest));
            // if referral registered without email link, add to referral
            if (!$id_referral && !Referrals::isReferralExists($id_customer, $id_guest)) {
                $inviteeData = AffiliateInvitations::getInviteeByEmail($email);
                if (isset($inviteeData) && $inviteeData) {
                    $newReferral = new Referrals();
                    $newReferral->id_affiliate = (int) $inviteeData['id_affiliate'];
                    $newReferral->id_customer = (int) $id_customer;
                    $newReferral->id_guest = (int) $id_guest;
                    $newReferral->active = 1;
                    $newReferral->source = 'email';
                    $newReferral->date_from = date('Y-m-d H:i:s');
                    $newReferral->date_add = date('Y-m-d H:i:s');
                    if ($newReferral->add()) {
                        $id_referral = $newReferral->id;
                        AffiliateInvitations::updateInviteeByEmail($email, $id_referral);
                    }
                }
            }

            if ($id_referral) {
                $referral = new Referrals((int) $id_referral);
                $referral->id_customer = (int) $id_customer;
                $referral->id_guest = (int) $id_guest;

                $nbr_referrals = (int) Referrals::countAffiliateRefs($referral->id_affiliate);
                $level = (int) Affiliation::countAffiliateLevel($referral->id_affiliate);
                $id_rule = (int) Rules::getApplicableRuleId($nbr_referrals, $level);
                $affiliate = new Affiliation((int) $referral->id_affiliate);
                if ($id_rule <= 0 && $level > 1) { //lets take previous level if return is null
                    $level = $level - 1;
                    $id_rule = (int) Rules::getApplicableRuleId($nbr_referrals, $level);
                    if ($id_rule <= 0 && $level > 1) {
                        $level = $level - 1;
                        $id_rule = (int) Rules::getApplicableRuleId($nbr_referrals, $level);
                        if ($id_rule <= 0 && $level > 1) {
                            $level = $level - 1;
                            $id_rule = (int) Rules::getApplicableRuleId($nbr_referrals, $level);
                        }
                    }
                }
                if ((isset($id_rule) && $id_rule) && $affiliate->rule != $id_rule) {
                    $rule = Rules::getRuleById($id_rule);
                    $reward_value = (float) $rule['reg_reward_value'];
                    $affiliate->rule = (int) $id_rule;
                }

                if ($referral->update()) {
                    $reward = new Rewards();
                    if (isset($reward_value) && $reward_value > 0.00) {
                        $reward->id_affiliate = (int) $affiliate->id_affiliate;
                        $reward->id_affiliate_referral = (int) $referral->id;
                        $reward->id_customer = (int) $affiliate->id_customer;
                        $reward->id_guest = (int) $affiliate->id_guest;
                        $reward->reward_by_reg = 1;
                        $reward->reward_by_ord = 0;
                        $reward->reg_reward_value = (float) $reward_value;
                        $reward->ord_reward_value = 0;
                        $reward->is_paid = 0;
                        $reward->status = 'approved';
                        $reward->pay_request = 'not sent';
                        $reward->reward_date = date('Y-m-d H:i:s');

                        if (!$reward->add() && !$affiliate->update()) {
                            $this->context->controller->errors[] = $this->l('Reward cannot be awarded');
                        }
                    }

                    if (isset($rule) && $rule) {
                        //Rewarding the parent affiliates - if any
                        if ((int) $rule['affiliate_level'] > 1 && (int) $rule['parent_reward_value'] > 0) {
                            $id_parent_affiliate = (int) Affiliation::getRefferalAffiliateId((int) $affiliate->id_customer);
                            if ($id_parent_affiliate) {
                                $reward = new Rewards();
                                $affiliate = new Affiliation($id_parent_affiliate);
                                $reward_value = 0;
                                $reward_value = ($rule['parent_reward_value'] / 100) * $rule['reg_reward_value'];
                                $reward->id_affiliate = (int) $affiliate->id_affiliate;
                                $reward->id_affiliate_referral = (int) $referral->id;
                                $reward->id_customer = (int) $affiliate->id_customer;
                                $reward->id_guest = (int) $affiliate->id_guest;
                                $reward->reward_by_reg = 1;
                                $reward->reward_by_ord = 0;
                                $reward->reg_reward_value = (float) $reward_value;
                                $reward->ord_reward_value = 0;
                                $reward->is_paid = 0;
                                $reward->status = 'approved';
                                $reward->pay_request = 'not sent';
                                $reward->reward_date = date('Y-m-d H:i:s');
                                $reward->add();
                                $id_parent_affiliate = (int) Affiliation::getRefferalAffiliateId((int) $affiliate->id_customer);
                                if ($id_parent_affiliate) {
                                    $reward = new Rewards();
                                    $affiliate = new Affiliation($id_parent_affiliate);
                                    $reward_value = 0;
                                    $reward_value = ($rule['parent_reward_value'] / 100) * $rule['reg_reward_value'];
                                    $reward->id_affiliate = (int) $affiliate->id_affiliate;
                                    $reward->id_affiliate_referral = (int) $referral->id;
                                    $reward->id_customer = (int) $affiliate->id_customer;
                                    $reward->id_guest = (int) $affiliate->id_guest;
                                    $reward->reward_by_reg = 1;
                                    $reward->reward_by_ord = 0;
                                    $reward->reg_reward_value = (float) $reward_value;
                                    $reward->ord_reward_value = 0;
                                    $reward->is_paid = 0;
                                    $reward->status = 'approved';
                                    $reward->pay_request = 'not sent';
                                    $reward->reward_date = date('Y-m-d H:i:s');
                                    $reward->add();
                                    $id_parent_affiliate = (int) Affiliation::getRefferalAffiliateId((int) $affiliate->id_customer);
                                    if ($id_parent_affiliate) {
                                        $reward = new Rewards();
                                        $affiliate = new Affiliation($id_parent_affiliate);
                                        $reward_value = 0;
                                        $reward_value = ($rule['parent_reward_value'] / 100) * $rule['reg_reward_value'];
                                        $reward->id_affiliate = (int) $affiliate->id_affiliate;
                                        $reward->id_affiliate_referral = (int) $referral->id;
                                        $reward->id_customer = (int) $affiliate->id_customer;
                                        $reward->id_guest = (int) $affiliate->id_guest;
                                        $reward->reward_by_reg = 1;
                                        $reward->reward_by_ord = 0;
                                        $reward->reg_reward_value = (float) $reward_value;
                                        $reward->ord_reward_value = 0;
                                        $reward->is_paid = 0;
                                        $reward->status = 'approved';
                                        $reward->pay_request = 'not sent';
                                        $reward->reward_date = date('Y-m-d H:i:s');
                                        $reward->add();
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $this->context->controller->errors[] = $this->l('Error referral association.');
                }
            }
        }
    }

    public function hookActionValidateOrder($params)
    {
        $id_customer = (int) $this->context->customer->id;
        $id_guest = (int) $this->context->cookie->id_guest;
        $order = $params['order'];
        $default_currency = (int) Configuration::get('PS_CURRENCY_DEFAULT');
        $id_affiliate = Referrals::getAffiliateByCustomer($id_customer);
        $referral = Referrals::getReferralByCustomer($id_customer);
        $level_obj = new Levels;
        $multishop = false;
        $selectedShops = Configuration::get('AFFILIATE_SHOPS');
        $shops = (!empty(trim($selectedShops)))? explode(',', $selectedShops) : [];

        //Fix For Guest users
        if (!$id_affiliate) {
            $customer = new Customer($order->id_customer);
            $id_affiliate = Referrals::getAffiliateByGuestOnly($customer->id_guest);
            if ($id_affiliate) {
                Referrals::updateCustomerIdOfGuest($id_affiliate, $customer->id_guest, $order->id_customer);
                $id_customer = $order->id_customer;
                $id_guest = $customer->id_guest;
            }
        }
        
        $hasVoucher = (bool) count($order->getCartRules());
        $voucherOrders = (bool) Configuration::get('REFERRAL_REWARD_VORDERS', null, $this->context->shop->id_shop_group, $this->context->shop->id);
        $specificProductsAllowed = (bool) Configuration::get('REFERRAL_REWARD_SPPRODUCTS', null, $this->context->shop->id_shop_group, $this->context->shop->id);
        if ((Shop::isFeatureActive() && isset($shops) && in_array($this->context->shop->id, $shops)) || !Shop::isFeatureActive() || empty($shops)) {
            $multishop = true;
        }
        if ($multishop && isset($id_affiliate) && isset($referral) && $id_affiliate) {
            // check if reward on orders with vouchers is allowed - new
            if (true === $voucherOrders || (false === $voucherOrders && false === $hasVoucher)) {
                $reward_value = 0;
                $affiliate = new Affiliation((int) $id_affiliate);
                $level = $level_obj->getDefaultLevel((int) $affiliate->level);

                if (empty($level) && $affiliate->level > 1) { //Find previous level if return is null
                    $affiliate->level = $affiliate->level - 1;
                    $level = $level_obj->getDefaultLevel((int) $affiliate->level);
                    if (empty($level) && $affiliate->level > 1) {
                        $affiliate->level = $affiliate->level - 1;
                        $level = $level_obj->getDefaultLevel((int) $affiliate->level);
                        if (empty($level) && $affiliate->level > 1) {
                            $affiliate->level = $affiliate->level - 1;
                            $level = $level_obj->getDefaultLevel((int) $affiliate->level);
                        }
                    }
                }
                $total_order = 0;
                if ($level) {
                    if ((int) $level['is_tax']) {
                        $total_order = $order->getTotalProductsWithTaxes();
                        $tax = true;
                    } else {
                        $total_order = $order->getTotalProductsWithoutTaxes();
                        $tax = false;
                    }

                    if ($total_order >= $level['min_order_value']) {
                        if ((int) $level['reward_type'] == 1) { //Percentage of order
                            $pc = ($level['reward_value'] / 100) * $total_order;
                            $reward_value = (float) $pc;
                        } elseif ((int) $level['reward_type'] == 2) { //Product Specific
                            $cart = new Cart($order->id_cart);
                            $products = $cart->getProducts(true);

                            foreach ($products as $product) {
                                $product_value = $level_obj->needleCheck(
                                    'affiliate_levels_products',
                                    'id_product',
                                    $product['id_product'],
                                    $level['id_affiliate_levels'],
                                    'value'
                                );
                                $product = new Product($product['id_product']);
                                $price = $product->getPrice($tax, null, 6);
                                // check if reward allowed on product's with specific price - new
                                $isDiscounted = Product::isDiscounted($product->id);
                                if (true === $specificProductsAllowed || (false === $specificProductsAllowed && false === $isDiscounted)) {
                                    if (!empty($product_value) && $product_value > 0) { //Make sure the product exist in rule
                                        if ((int) $level['value_type'] > 0) { //its percentage reward
                                            $single_reward = ($product_value / 100) * $price;
                                            $reward_value = $reward_value + $single_reward;
                                        } else { //fixed reward
                                            $reward_value = $reward_value + $product_value;
                                        }
                                    } elseif ((float) $level['reward_value'] > 0) { //See if global value exist
                                        $product_value = (float) $level['reward_value'];
                                        if ((int) $level['value_type'] > 0) { //its percentage reward
                                            $single_reward = ($product_value / 100) * $price;
                                            $reward_value = $reward_value + $single_reward;
                                        } else { //fixed reward
                                            $reward_value = $reward_value + $product_value;
                                        }
                                    }
                                }
                            }
                        } elseif ((int) $level['reward_type'] == 3) { //Category Specific
                            $cart = new Cart($order->id_cart);
                            $products = $cart->getProducts(true);
                            foreach ($products as $product) {
                                $product_categories = Product::getProductCategories($product['id_product']);
                                $category_value = $level_obj->getCategoryValue($level['id_affiliate_levels'], $product_categories);
                                $product = new Product($product['id_product']);
                                $price = $product->getPrice($tax, null, 6);
                                $isDiscounted = Product::isDiscounted($product->id);
                                // check if reward allowed on product's with specific price - new
                                if (true === $specificProductsAllowed || (false === $specificProductsAllowed && false === $isDiscounted)) {
                                    if (!empty($category_value) && $category_value > 0) { //Make sure the product exist in rule
                                        if ((int) $level['value_type'] > 0) { //its percentage reward
                                            $single_reward = ($category_value / 100) * $price;
                                            $reward_value = $reward_value + $single_reward;
                                        } else { //fixed reward
                                            $reward_value = $reward_value + $category_value;
                                        }
                                    } elseif ((float) $level['reward_value'] > 0) { //See if global value exist
                                        $category_value = (float) $level['reward_value'];
                                        if ((int) $level['value_type'] > 0) { //its percentage reward
                                            $single_reward = ($category_value / 100) * $price;
                                            $reward_value = $reward_value + $single_reward;
                                        } else { //fixed reward
                                            $reward_value = $reward_value + $category_value;
                                        }
                                    }
                                }
                            }
                        } else { //Fixed Amount
                            $reward_value = (float) $level['reward_value'];
                        }
                        //Now do the conversion if the currency is not default one.
                        if ($default_currency != $order->id_currency) {
                            $currency_current = new Currency($order->id_currency);
                            $store_currency = new Currency($default_currency);
                            $reward_value = Tools::convertPriceFull($reward_value, $currency_current, $store_currency);
                        }

                        // check if reward has a value
                        if (isset($reward_value) && $reward_value > 0.00) {
                            $reward = new Rewards();
                            $reward->id_affiliate = (int) $id_affiliate;
                            $reward->id_affiliate_referral = (int) $referral['id_affiliate_referral'];
                            $reward->id_customer = (int) $affiliate->id_customer;
                            $reward->id_guest = (int) $affiliate->id_guest;
                            $reward->reward_by_reg = 0;
                            $reward->reward_by_ord = 1;
                            $reward->reg_reward_value = 0;
                            $reward->id_order = (int) $order->id;
                            $reward->ord_reward_value = (float) $reward_value;
                            $reward->status = 'pending';
                            $reward->reward_date = date('Y-m-d H:i:s');
                            $reward->add();
                            //Rewarding the parent affiliates - if any
                            if ((int) $affiliate->level > 1 && (int) $level['parent_reward'] > 0) {
                                $id_parent_affiliate = (int) Affiliation::getRefferalAffiliateId((int) $affiliate->id_customer);
                                if ($id_parent_affiliate > 0) {
                                    $affiliate = new Affiliation((int) $id_parent_affiliate);
                                    $_reward_value = ($level['parent_reward'] / 100) * $reward_value;
                                    $reward = new Rewards();
                                    $reward->id_affiliate = (int) $id_parent_affiliate;
                                    $reward->id_affiliate_referral = (int) $referral['id_affiliate_referral'];
                                    $reward->id_customer = (int) $affiliate->id_customer;
                                    $reward->id_guest = (int) $affiliate->id_guest;
                                    $reward->reward_by_reg = 0;
                                    $reward->reward_by_ord = 1;
                                    $reward->reg_reward_value = 0;
                                    $reward->id_order = (int) $order->id;
                                    $reward->ord_reward_value = (float) $_reward_value;
                                    $reward->status = 'pending';
                                    $reward->reward_date = date('Y-m-d H:i:s');
                                    $reward->add();
                                    $id_parent_affiliate = (int) Affiliation::getRefferalAffiliateId((int) $affiliate->id_customer);
                                    if ($id_parent_affiliate > 0) {
                                        $affiliate = new Affiliation((int) $id_parent_affiliate);
                                        $_reward_value = ($level['parent_reward'] / 100) * $reward_value;
                                        $reward = new Rewards();
                                        $reward->id_affiliate = (int) $id_parent_affiliate;
                                        $reward->id_affiliate_referral = (int) $referral['id_affiliate_referral'];
                                        $reward->id_customer = (int) $affiliate->id_customer;
                                        $reward->id_guest = (int) $affiliate->id_guest;
                                        $reward->reward_by_reg = 0;
                                        $reward->reward_by_ord = 1;
                                        $reward->reg_reward_value = 0;
                                        $reward->id_order = (int) $order->id;
                                        $reward->ord_reward_value = (float) $_reward_value;
                                        $reward->status = 'pending';
                                        $reward->reward_date = date('Y-m-d H:i:s');
                                        $reward->add();
                                        $id_parent_affiliate = (int) Affiliation::getRefferalAffiliateId((int) $affiliate->id_customer);
                                        if ($id_parent_affiliate > 0) {
                                            $affiliate = new Affiliation((int) $id_parent_affiliate);
                                            $_reward_value = ($level['parent_reward'] / 100) * $reward_value;
                                            $reward = new Rewards();
                                            $reward->id_affiliate = (int) $id_parent_affiliate;
                                            $reward->id_affiliate_referral = (int) $referral['id_affiliate_referral'];
                                            $reward->id_customer = (int) $affiliate->id_customer;
                                            $reward->id_guest = (int) $affiliate->id_guest;
                                            $reward->reward_by_reg = 0;
                                            $reward->reward_by_ord = 1;
                                            $reward->reg_reward_value = 0;
                                            $reward->id_order = (int) $order->id;
                                            $reward->ord_reward_value = (float) $_reward_value;
                                            $reward->status = 'pending';
                                            $reward->reward_date = date('Y-m-d H:i:s');
                                            $reward->add();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function hookActionOrderStatusPostUpdate($params)
    {
        $newOrderStatus = $params['newOrderStatus'];
        $id_order = (int) Tools::getValue('id_order');
        $id_order = (!$id_order)? $params['id_order'] : $id_order;
        $id_order_state = (int) Tools::getValue('id_order_state');
        $id_order_state = (!$id_order_state)? $newOrderStatus->id : $id_order_state;
        
        $this->processOrderReward($id_order, $id_order_state);
    }

    public function getContent()
    {
        if (Tools::isSubmit('submitPaymentMethod') ||
            Tools::isSubmit('deletePayment') ||
            Tools::isSubmit('saveSettings')) {
            $this->postProcess();
        }

        $this->context->smarty->assign(array(
            'subMenuTab' => self::$subMenuTab,
            'currentMenuTab' => self::$currentFormTab,
        ));
        $this->html = $this->getMenu();


        return $this->html . $this->displayConfigForm();
    }

    public function getConfigFieldsValues()
    {
        $languages = Language::getLanguages(false);
        $return = array();
        foreach ($languages as $lang) {
            $return['referral_welcom_msg'][(int) $lang['id_lang']] = Tools::getValue('referral_welcom_msg' . (int) $lang['id_lang'], Configuration::get('referral_welcom_msg', (int) $lang['id_lang']));
        }
        return $return;
    }

    private function displayConfigForm()
    {
        $groups = Group::getGroups((int) $this->context->language->id, true);
        $affiliate_groups = (Configuration::get('AFFILIATE_GROUPS', null, $this->context->shop->id_shop_group, $this->context->shop->id)? explode(',', Configuration::get('AFFILIATE_GROUPS', null, $this->context->shop->id_shop_group, $this->context->shop->id)) : '');
        $approval_states = (Configuration::get('APPROVAL_STATES', null, $this->context->shop->id_shop_group, $this->context->shop->id)? explode(',', Configuration::get('APPROVAL_STATES', null, $this->context->shop->id_shop_group, $this->context->shop->id)) : '');
        $cancel_states = (Configuration::get('CANCEL_STATES', null, $this->context->shop->id_shop_group, $this->context->shop->id))? explode(',', Configuration::get('CANCEL_STATES', null, $this->context->shop->id_shop_group, $this->context->shop->id)) : '';

        // List of CMS tabs
        $cms_tab = array();
        foreach (CMS::listCms($this->context->language->id) as $cms_file) {
            $cms_tab[] = array(
                'id' => $cms_file['id_cms'],
                'name' => $cms_file['meta_title']
            );
        }

        $shops = '';
        $multishop = 0;

        if (Shop::isFeatureActive()) {
            $multishop = 1;
            $shops = $this->renderShops();
        }

        $this->context->smarty->assign([
            'shops' => $shops,
        ]);

        $iso_tiny_mce = $this->context->language->iso_code;
        $iso_tiny_mce = (file_exists(_PS_JS_DIR_ . 'tiny_mce/langs/' . $iso_tiny_mce . '.js') ? $iso_tiny_mce : 'en');

        $iconThemes = array(
            array(
                'value' => 'flat',
                'name' => $this->l('Flat'),
            ),
            array(
                'value' => 'classic',
                'name' => $this->l('Classical'),
            ),
            array(
                'value' => 'minima',
                'name' => $this->l('Minimalistic'),
            ),
            array(
                'value' => 'plain',
                'name' => $this->l('Monochromatic'),
            ),
        );

        $iso = $this->context->language->iso_code;
        $iso_lang = (file_exists(_PS_ROOT_DIR_ . '/js/tiny_mce/langs/' . $iso . '.js') ? $iso : 'en');
        $id_lang_default = Configuration::get('PS_LANG_DEFAULT');
        $theme_dir = _THEME_CSS_DIR_;
        $path = dirname($_SERVER['PHP_SELF']);
        $js_path = _PS_JS_DIR_;
        $this->smarty->assign(array(
            'path' => $path,
            'groups' => $groups,
            'js_path' => $js_path,
            'cms_tabs' => $cms_tab,
            'iso_lang' => $iso_lang,
            'multishop' => $multishop,
            'theme_dir' => $theme_dir,
            'ps_version' => _PS_VERSION_,
            'icon_themes' => $iconThemes,
            'iso_tiny_mce' => $iso_tiny_mce,
            'cancel_states' => $cancel_states,
            'approval_states' => $approval_states,
            'id_lang_default' => $id_lang_default,
            'currency' => $this->context->currency,
            'affiliate_groups' => $affiliate_groups,
            'action_url' => $this->getAffiliateUrl(),
            'social_networks' => $this->getSocialNetworks(),
            'referral_welcom_msg' => $this->getConfigFieldsValues(),
            'payment_methods' => PaymentMethod::getPaymenMethods($this->context->language->id),
            'MINIMUM_AMOUNT' => Configuration::get('MINIMUM_AMOUNT', null, $this->context->shop->id_shop_group, $this->context->shop->id),
            'WIRETRANS_FEE' => Configuration::get('WIRETRANS_FEE', null, $this->context->shop->id_shop_group, $this->context->shop->id),
            'PAYMENT_DELAY_TIME' => Configuration::get('PAYMENT_DELAY_TIME', null, $this->context->shop->id_shop_group, $this->context->shop->id),
            'REFERAK_KEY_LEN' => (int) Configuration::get('REFERAK_KEY_LEN', null, $this->context->shop->id_shop_group, $this->context->shop->id),
            'AFFILIATE_PROGRAM_ORDERS' => (int) Configuration::get('AFFILIATE_PROGRAM_ORDERS', null, $this->context->shop->id_shop_group, $this->context->shop->id),
            'REFERRAL_REWARD_VORDERS' => (int) Configuration::get('REFERRAL_REWARD_VORDERS', null, $this->context->shop->id_shop_group, $this->context->shop->id),
            'REFERRAL_REWARD_SPPRODUCTS' => (int) Configuration::get('REFERRAL_REWARD_SPPRODUCTS', null, $this->context->shop->id_shop_group, $this->context->shop->id),
            'PAYPAL_MODE' => Configuration::get('PAYPAL_MODE', null, $this->context->shop->id_shop_group, $this->context->shop->id),
            'PAYPAL_EMAIL' => Configuration::get('PAYPAL_EMAIL', null, $this->context->shop->id_shop_group, $this->context->shop->id),
            'PAYPAL_USERNAME' => Configuration::get('PAYPAL_USERNAME', null, $this->context->shop->id_shop_group, $this->context->shop->id),
            'PAYPAL_APP_ID' => Configuration::get('PAYPAL_APP_ID', null, $this->context->shop->id_shop_group, $this->context->shop->id),
            'PAYPAL_API_PASSWORD' => Configuration::get('PAYPAL_API_PASSWORD', null, $this->context->shop->id_shop_group, $this->context->shop->id),
            'PAYPAL_API_SIGNATURE' => Configuration::get('PAYPAL_API_SIGNATURE', null, $this->context->shop->id_shop_group, $this->context->shop->id),
            'REFERRAL_DISCOUNT_STATUS' => Configuration::get('REFERRAL_DISCOUNT_STATUS', null, $this->context->shop->id_shop_group, $this->context->shop->id),
            'REFERRAL_DISCOUNT_TYPE' => Configuration::get('REFERRAL_DISCOUNT_TYPE', null, $this->context->shop->id_shop_group, $this->context->shop->id),
            'REFERRAL_DISCOUNT_VALUE' => Configuration::get('REFERRAL_DISCOUNT_VALUE', null, $this->context->shop->id_shop_group, $this->context->shop->id),
            'REFERRAL_DISCOUNT_CURRENCY' => Configuration::get('REFERRAL_DISCOUNT_CURRENCY', null, $this->context->shop->id_shop_group, $this->context->shop->id),
            'AFFILIATE_FACEBOOK' => Configuration::get('AFFILIATE_FACEBOOK', null, $this->context->shop->id_shop_group, $this->context->shop->id),
            'AFFILIATE_TWITTER' => Configuration::get('AFFILIATE_TWITTER', null, $this->context->shop->id_shop_group, $this->context->shop->id),
            'AFFILIATE_GOOGLE' => Configuration::get('AFFILIATE_GOOGLE', null, $this->context->shop->id_shop_group, $this->context->shop->id),
            'AFFILIATE_DIGG' => Configuration::get('AFFILIATE_DIGG', null, $this->context->shop->id_shop_group, $this->context->shop->id),
            'AFFILIATE_AUTO_APPROVAL' => Configuration::get('AFFILIATE_AUTO_APPROVAL', null, $this->context->shop->id_shop_group, $this->context->shop->id),
            'states' => OrderState::getOrderStates($this->context->language->id),
            'currencies' => Currency::getCurrencies(false, true, true),
            'module' => new Affiliates(),
            'id_lang_default' => $this->context->language->id,
            'languages' => Language::getLanguages(false),
            'ad' => __PS_BASE_URI__ . basename(_PS_ADMIN_DIR_),
            'id_lang' => $this->context->language->id,
            'PAYMENT_METHOD' => (Configuration::get('PAYMENT_METHOD', null, $this->context->shop->id_shop_group, $this->context->shop->id) ? explode(',', Configuration::get('PAYMENT_METHOD', null, $this->context->shop->id_shop_group, $this->context->shop->id)) : ''),
            'AFFILIATE_SOCIAL_THEME' => Configuration::get('AFFILIATE_SOCIAL_THEME', null, $this->context->shop->id_shop_group, $this->context->shop->id),
            'AFFILIATE_SOCIAL_LABELS' => Configuration::get('AFFILIATE_SOCIAL_LABELS', null, $this->context->shop->id_shop_group, $this->context->shop->id),
            'selected_socials' => (Configuration::get('AFFILIATE_SOCIALS', null, $this->context->shop->id_shop_group, $this->context->shop->id) ? explode(',', Configuration::get('AFFILIATE_SOCIALS', null, $this->context->shop->id_shop_group, $this->context->shop->id)) : array()),
            'selected_payments' => (Configuration::get('AFFILIATE_CUSTOM_PAYMENTS', null, $this->context->shop->id_shop_group, $this->context->shop->id) ? explode(',', Configuration::get('AFFILIATE_CUSTOM_PAYMENTS', null, $this->context->shop->id_shop_group, $this->context->shop->id)) : array()),
        ));

        if (Tools::isSubmit('editPayment')) {
            self::$subMenuTab = 'payments';
            self::$currentFormTab = 'configuration';
            $this->context->smarty->assign('payment_form', $this->paymentMethodForm(Tools::getValue('id_payment_method')));
        } else {
            $this->context->smarty->assign('payment_form', $this->paymentMethodForm());
        }

        return $this->display(__FILE__, 'views/templates/admin/form.tpl');
    }

    protected function postProcess()
    {
        if (Tools::isSubmit('submitPaymentMethod')) {
            $pmDesc = array();
            $pmName = Tools::getValue('payment_name');
            self::$subMenuTab = 'payments';
            self::$currentFormTab = 'configuration';
            foreach (Language::getLanguages(false) as $lang) {
                if (Tools::getValue('payment_description_' . $lang['id_lang']) && !Validate::isString(Tools::getValue('payment_description_' . $lang['id_lang']))) {
                    $this->context->controller->errors[] = sprintf($this->l('Invalid payment description in %s.'), $lang['name']);
                } else {
                    $pmDesc[$lang['id_lang']] = Tools::getValue('payment_description_' . $lang['id_lang']);
                }
            }

            if (!count($this->context->controller->errors)) {
                $pm = new PaymentMethod(Tools::getValue('id_payment_method'));
                $pm->payment_name = $pmName;
                $pm->payment_description = $pmDesc;
                if (!$pm->save()) {
                    $this->context->controller->errors[] = $this->l('Operatio failed on Payment method.');
                } else {
                    $this->context->controller->confirmations[] = $this->l('OPeration successful on payment method.');
                }
            }
        }

        if (Tools::isSubmit('deletePayment')) {
            self::$subMenuTab = 'payments';
            self::$currentFormTab = 'configuration';
            if (!Validate::isLoadedObject($pm = new PaymentMethod(Tools::getValue('id_payment_method')))) {
                $this->context->controller->errors[] = $this->l('Payment method not found.');
            } elseif (!$pm->delete()) {
                $this->context->controller->errors[] = $this->l('Payment method cannot deleted.');
            } else {
                $this->context->controller->confirmations[] = $this->l('Payment method deleted successfully.');
            }
        }

        if (Tools::isSubmit('saveSettings')) {
            $pm_methods = Tools::getValue('PAYMENT_METHOD');
            $affiliateProgramOrders = (int) Tools::getValue('AFFILIATE_PROGRAM_ORDERS');
            $affiliate_groups = (Tools::getValue('affiliate_groups')) ? implode(',', Tools::getValue('affiliate_groups')) : '';
            $approval_states = (Tools::getValue('approval_states')) ? implode(',', Tools::getValue('approval_states')) : '';
            $cancel_states = (Tools::getValue('cancel_states')) ? implode(',', Tools::getValue('cancel_states')) : '';
            $selected_socials = Tools::getValue('selected_socials');

            self::$subMenuTab = Tools::getValue('subMenuTab');
            self::$currentFormTab = Tools::getValue('currentMenuTab');

            if (isset($selected_socials) && $selected_socials) {
                $selected_socials = implode(',', $selected_socials);
            }

            $selected_payments = Tools::getValue('selected_payments');
            if (isset($selected_payments) && $selected_payments) {
                $selected_payments = implode(',', $selected_payments);
            }

            if (!Validate::isFloat(Tools::getValue('MINIMUM_AMOUNT'))) {
                $this->context->controller->errors[] = $this->l('Inavlid minimum amount');
            }

            if (!Validate::isInt(Tools::getValue('PAYMENT_DELAY_TIME'))) {
                $this->context->controller->errors[] = $this->l('Inavlid Payment holding time');
            }

            if (!Validate::isInt(Tools::getValue('REFERAK_KEY_LEN'))) {
                $this->context->controller->errors[] = $this->l('Inavlid Referal Key length');
            }

            if (!Validate::isUnsignedInt($affiliateProgramOrders)) {
                $this->context->controller->errors[] = $this->l('Inavlid value for "No. of Orders to enroll in Affiliate Program"');
            }

            if (isset($pm_methods) && isset($pm_methods['paypal']) && !Validate::isEmail(Tools::getValue('PAYPAL_EMAIL'))) {
                $this->context->controller->errors[] = $this->l('Inavlid paypal email');
            }

            // saving Discount data
            $discount_status = (int) Tools::getValue('REFERRAL_DISCOUNT_STATUS');
            $id_cart_rule = (int) Configuration::get('ID_AFFILIATE_DISCOUNT_RULE', null, $this->context->shop->id_shop_group, $this->context->shop->id);

            switch ($discount_status) {
                case 1:
                    if (!Validate::isFloat(Tools::getValue('REFERRAL_DISCOUNT_VALUE')) || (float) Tools::getValue('REFERRAL_DISCOUNT_VALUE') < 0.00) {
                        return $this->context->controller->errors[] = $this->l('Invalid discount value');
                    }

                    $action = 'update';
                    if (!Validate::isLoadedObject($discount = new CartRule($id_cart_rule))) {
                        $discount = new CartRule();
                        $action = 'add';
                    }
                    $this->updateAffiliateDiscount($discount, $action, $discount_status);
                    break;
                case 0:
                    $action = 'update';
                    if (!Validate::isLoadedObject($discount = new CartRule($id_cart_rule))) {
                        $discount = new CartRule();
                        $action = 'add';
                    }
                    $this->updateAffiliateDiscount($discount, $action, $discount_status);
                    break;
            }

            // saving multilingual message content
            $message_trads = array('referral_welcom_msg' => array());
            foreach ($_POST as $key => $value) {
                if (preg_match('/referral_welcom_msg_/i', $key)) {
                    $id_lang = preg_split('/referral_welcom_msg_/i', $key);
                    $message_trads['referral_welcom_msg'][(int) $id_lang[1]] = $value;
                }
            }
            Configuration::updateValue(
                'referral_welcom_msg',
                $message_trads['referral_welcom_msg'],
                true,
                $this->context->shop->id_shop_group,
                $this->context->shop->id
            );

            // Multishop processing
            $selectedShops = $this->context->shop->id;
            if (Shop::isFeatureActive()) {
                $assoc_shops = Tools::getValue('checkBoxShopAsso_affiliate');

                //Db::getInstance()->delete('affiliate_shop');
                $affiliate_discount_rule = Configuration::get(
                    'ID_AFFILIATE_DISCOUNT_RULE',
                    null,
                    $this->context->shop->id_shop_group,
                    $this->context->shop->id
                );

                $selectedShops = implode(',', $assoc_shops);
                if (isset($assoc_shops) && $assoc_shops) {
                    if ($affiliate_discount_rule) {
                        Affiliation::deleteShopRestriction($affiliate_discount_rule);
                    }

                    foreach ($assoc_shops as $id_shop) {
                        // Db::getInstance()->insert(
                        //     'affiliate_shop',
                        //     array(
                        //         'id_shop' => (int) $id_shop,
                        //         'id_group' => (int) Shop::getGroupFromShop($id_shop),
                        //     )
                        // );

                        if ($affiliate_discount_rule) {
                            Affiliation::restrictVoucherToShop($affiliate_discount_rule, $id_shop);
                        }
                    }
                } else {
                    if ($affiliate_discount_rule) {
                        Affiliation::deleteShopRestriction($affiliate_discount_rule);
                        $shops = Shop::getShops();
                        foreach ($shops as $shop) {
                            Affiliation::restrictVoucherToShop($affiliate_discount_rule, $shop['id_shop']);
                        }
                    }
                }
            }

            if (!$this->context->controller->errors) {
                Configuration::updateValue('AFFILIATE_SHOPS', $selectedShops);

                Configuration::updateValue(
                    'AFFILIATE_GROUPS',
                    $affiliate_groups,
                    false,
                    $this->context->shop->id_shop_group,
                    $this->context->shop->id
                );
                Configuration::updateValue(
                    'APPROVAL_STATES',
                    $approval_states,
                    false,
                    $this->context->shop->id_shop_group,
                    $this->context->shop->id
                );
                Configuration::updateValue(
                    'CANCEL_STATES',
                    $cancel_states,
                    false,
                    $this->context->shop->id_shop_group,
                    $this->context->shop->id
                );
                Configuration::updateValue(
                    'AFFILIATE_CONDITION',
                    (int) Tools::getValue('AFFILIATE_CONDITION'),
                    false,
                    $this->context->shop->id_shop_group,
                    $this->context->shop->id
                );
                Configuration::updateValue(
                    'PAYMENT_METHOD',
                    ($pm_methods ? implode(',', $pm_methods) : ''),
                    false,
                    $this->context->shop->id_shop_group,
                    $this->context->shop->id
                );
                Configuration::updateValue(
                    'AFFILIATE_AUTO_APPROVAL',
                    Tools::getValue('AFFILIATE_AUTO_APPROVAL'),
                    false,
                    $this->context->shop->id_shop_group,
                    $this->context->shop->id
                );
                Configuration::updateValue(
                    'AFFILIATE_SOCIAL_THEME',
                    Tools::getValue('AFFILIATE_SOCIAL_THEME'),
                    false,
                    $this->context->shop->id_shop_group,
                    $this->context->shop->id
                );
                Configuration::updateValue(
                    'WIRETRANS_FEE',
                    (float) Tools::getValue('WIRETRANS_FEE'),
                    false,
                    $this->context->shop->id_shop_group,
                    $this->context->shop->id
                );
                Configuration::updateValue(
                    'AFFILIATE_SOCIAL_LABELS',
                    Tools::getValue('AFFILIATE_SOCIAL_LABELS'),
                    false,
                    $this->context->shop->id_shop_group,
                    $this->context->shop->id
                );
                Configuration::updateValue(
                    'AFFILIATE_SOCIALS',
                    $selected_socials,
                    false,
                    $this->context->shop->id_shop_group,
                    $this->context->shop->id
                );
                Configuration::updateValue(
                    'AFFILIATE_CUSTOM_PAYMENTS',
                    $selected_payments,
                    false,
                    $this->context->shop->id_shop_group,
                    $this->context->shop->id
                );
                Configuration::updateValue(
                    'MINIMUM_AMOUNT',
                    (float) Tools::getValue('MINIMUM_AMOUNT'),
                    false,
                    $this->context->shop->id_shop_group,
                    $this->context->shop->id
                );
                Configuration::updateValue(
                    'PAYMENT_DELAY_TIME',
                    (int) Tools::getValue('PAYMENT_DELAY_TIME'),
                    false,
                    $this->context->shop->id_shop_group,
                    $this->context->shop->id
                );
                Configuration::updateValue(
                    'REFERAK_KEY_LEN',
                    (int) Tools::getValue('REFERAK_KEY_LEN'),
                    false,
                    $this->context->shop->id_shop_group,
                    $this->context->shop->id
                );
                Configuration::updateValue(
                    'PAYPAL_EMAIL',
                    (string) Tools::getValue('PAYPAL_EMAIL'),
                    false,
                    $this->context->shop->id_shop_group,
                    $this->context->shop->id
                );
                Configuration::updateValue(
                    'DELAY_TYPE',
                    (string) Tools::getValue('DELAY_TYPE'),
                    false,
                    $this->context->shop->id_shop_group,
                    $this->context->shop->id
                );
                // PAYPAL API credentials
                Configuration::updateValue(
                    'PAYPAL_MODE',
                    (int) Tools::getValue('PAYPAL_MODE'),
                    false,
                    $this->context->shop->id_shop_group,
                    $this->context->shop->id
                );
                Configuration::updateValue(
                    'PAYPAL_USERNAME',
                    (string) Tools::getValue('PAYPAL_USERNAME'),
                    false,
                    $this->context->shop->id_shop_group,
                    $this->context->shop->id
                );
                Configuration::updateValue(
                    'PAYPAL_API_PASSWORD',
                    (string) Tools::getValue('PAYPAL_API_PASSWORD'),
                    false,
                    $this->context->shop->id_shop_group,
                    $this->context->shop->id
                );
                Configuration::updateValue(
                    'PAYPAL_APP_ID',
                    (string) Tools::getValue('PAYPAL_APP_ID'),
                    false,
                    $this->context->shop->id_shop_group,
                    $this->context->shop->id
                );
                Configuration::updateValue(
                    'PAYPAL_API_SIGNATURE',
                    (string) Tools::getValue('PAYPAL_API_SIGNATURE'),
                    false,
                    $this->context->shop->id_shop_group,
                    $this->context->shop->id
                );

                // social sharing buttons
                Configuration::updateValue(
                    'AFFILIATE_FACEBOOK',
                    (int) Tools::getValue('AFFILIATE_FACEBOOK'),
                    false,
                    $this->context->shop->id_shop_group,
                    $this->context->shop->id
                );
                Configuration::updateValue(
                    'AFFILIATE_TWITTER',
                    (int) Tools::getValue('AFFILIATE_TWITTER'),
                    false,
                    $this->context->shop->id_shop_group,
                    $this->context->shop->id
                );
                Configuration::updateValue(
                    'AFFILIATE_GOOGLE',
                    (int) Tools::getValue('AFFILIATE_GOOGLE'),
                    false,
                    $this->context->shop->id_shop_group,
                    $this->context->shop->id
                );
                Configuration::updateValue(
                    'AFFILIATE_DIGG',
                    (int) Tools::getValue('AFFILIATE_DIGG'),
                    false,
                    $this->context->shop->id_shop_group,
                    $this->context->shop->id
                );
                Configuration::updateValue(
                    'REFERRAL_DISCOUNT_STATUS',
                    (int) $discount_status,
                    false,
                    $this->context->shop->id_shop_group,
                    $this->context->shop->id
                );
                Configuration::updateValue(
                    'REFERRAL_DISCOUNT_TYPE',
                    (string) Tools::getValue('REFERRAL_DISCOUNT_TYPE'),
                    false,
                    $this->context->shop->id_shop_group,
                    $this->context->shop->id
                );
                Configuration::updateValue(
                    'REFERRAL_DISCOUNT_VALUE',
                    (float) Tools::getValue('REFERRAL_DISCOUNT_VALUE'),
                    false,
                    $this->context->shop->id_shop_group,
                    $this->context->shop->id
                );
                Configuration::updateValue(
                    'REFERRAL_DISCOUNT_CURRENCY',
                    (int) Tools::getValue('REFERRAL_DISCOUNT_CURRENCY'),
                    false,
                    $this->context->shop->id_shop_group,
                    $this->context->shop->id
                );
                Configuration::updateValue(
                    'AFFILIATE_PROGRAM_ORDERS',
                    (int) Tools::getValue('AFFILIATE_PROGRAM_ORDERS'),
                    false,
                    $this->context->shop->id_shop_group,
                    $this->context->shop->id
                );
                Configuration::updateValue(
                    'REFERRAL_REWARD_VORDERS',
                    (int) Tools::getValue('REFERRAL_REWARD_VORDERS'),
                    false,
                    $this->context->shop->id_shop_group,
                    $this->context->shop->id
                );
                Configuration::updateValue(
                    'REFERRAL_REWARD_SPPRODUCTS',
                    (int) Tools::getValue('REFERRAL_REWARD_SPPRODUCTS'),
                    false,
                    $this->context->shop->id_shop_group,
                    $this->context->shop->id
                );

                $this->context->controller->confirmations[] = $this->l('Updated Successfully');
            }
        }
    }

    private function renderShops()
    {
        $tree = new HelperTreeShops('affiliate-shop', $this->l('Affiliate Shops'));

        $selectedShops = Configuration::get('AFFILIATE_SHOPS');
        $assos = (!empty(trim($selectedShops)))? explode(',', $selectedShops) : [];
        $tree->setSelectedShops($assos);
        $tree->setAttribute('table', 'affiliate');

        return $tree->render();
    }

    private function _renderShops()
    {
        $shop_field = array(
            'form' => array(
                'input' => array(
                    array(
                        'type' => 'shop',
                        'name' => 'checkBoxShopAsso',
                        'label' => $this->l('Shop association:'),
                        'desc' => $this->l('Affiliation program will be available on selected shops (by default it will be available on all shops).'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save Settings'),
                ),
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $helper->submit_action = 'submitShops';
        $helper->table = 'affiliate_shop_association';
        $helper->tpl_vars = array_merge([
            'id_language' => $this->context->language->id,
            'language' => (int) Configuration::get('PS_LANG_DEFAULT'),
            'languages' => $this->context->controller->getLanguages(),
        ]);
        return $helper->generateForm([$shop_field]);
    }

    protected function updateAffiliateDiscount($discount, $action, $status = 1)
    {
        $languages = Language::getLanguages();
        foreach ($languages as $lang) {
            $discount->name[$lang['id_lang']] = $this->l('Affiliation Program');
        }

        $discount->highlight = 0;
        $discount->quantity = 1000;
        $discount->partial_use = 0;
        $discount->group_restriction = 1;
        $discount->active = (int) $status;
        $discount->date_add = date('Y-m-d H:i:s');
        $discount->date_from = date('Y-m-d H:i:s');
        $discount->code = Tools::passwdGen(12, 'ALPHANUMERIC');
        $discount->date_to = date('Y-m-d H:i:s', strtotime('+5 years'));
        $discount->reduction_currency = (int) Tools::getValue('REFERRAL_DISCOUNT_CURRENCY');

        if (Tools::getValue('REFERRAL_DISCOUNT_TYPE') == 'percent') {
            $discount->reduction_percent = (float) Tools::getValue('REFERRAL_DISCOUNT_VALUE');
        } else {
            $discount->reduction_amount = (float) Tools::getValue('REFERRAL_DISCOUNT_VALUE');
        }

        if (call_user_func(array($discount, $action))) {
            Configuration::updateValue(
                'ID_AFFILIATE_DISCOUNT_RULE',
                $discount->id,
                false,
                $this->context->shop->id_shop_group,
                $this->context->shop->id
            );
            $this->setGroupRestrictionOnVoucher($discount->id);
        }
    }

    public function setGroupRestrictionOnVoucher($id_voucher)
    {
        $groups = Group::getGroups((int) $this->context->language->id);
        Affiliation::deleteGroupRestriction($id_voucher);
        foreach ($groups as $group) {
            if ($group['id_group'] != Configuration::get('ID_AFFILIATE_GROUP', null, $this->context->shop->id_shop_group, $this->context->shop->id)) {
                Affiliation::restrictVoucherToAffiliateGroup($id_voucher, (int) $group['id_group']);
            }
        }
    }

    protected function createAffiliateGroup()
    {
        $affiliate_group = new Group();
        $affiliate_group->reduction = 0;
        $affiliate_group->price_display_method = 1;
        $affiliate_group->show_prices = 1;
        $affiliate_group->date_add = date('Y-m-d H:i:s');
        foreach (Language::getLanguages() as $lang) {
            $affiliate_group->name[$lang['id_lang']] = $this->l('Affiliates');
        }

        if ($affiliate_group->add()) {
            Configuration::updateValue(
                'ID_AFFILIATE_GROUP',
                $affiliate_group->id,
                false,
                $this->context->shop->id_shop_group,
                $this->context->shop->id
            );
            Configuration::updateValue(
                'AFFILIATE_GROUPS',
                Configuration::get('PS_CUSTOMER_GROUP') . ',' . $affiliate_group->id,
                false,
                $this->context->shop->id_shop_group,
                $this->context->shop->id
            );

            $shops = Shop::getShops(true, null, true);
            $modules = Module::getModulesInstalled();
            $auth_modules_tmp = array();
            foreach ($modules as $val) {
                $auth_modules_tmp[] = $val['id_module'];
            }

            Group::addModulesRestrictions((int) Configuration::get('ID_AFFILIATE_GROUP', null, $this->context->shop->id_shop_group, $this->context->shop->id), $auth_modules_tmp, $shops);
            $categories = Affiliation::getAllCategories();
            foreach ($categories as $id_category) {
                Affiliation::addAffiliateGroupToCategory($id_category, (int) Configuration::get('ID_AFFILIATE_GROUP', null, $this->context->shop->id_shop_group, $this->context->shop->id));
            }
            return true;
        }
        return false;
    }

    public function deleteAffiliateGroup()
    {
        $affiliate_group = new Group((int) Configuration::get('ID_AFFILIATE_GROUP', null, $this->context->shop->id_shop_group, $this->context->shop->id));
        if ($affiliate_group->delete()) {
            Configuration::deleteByName('ID_AFFILIATE_GROUP');
            return true;
        }
        return false;
    }

    protected function deleteDiscountVoucher()
    {
        if (Configuration::get('ID_AFFILIATE_DISCOUNT_RULE', null, $this->context->shop->id_shop_group, $this->context->shop->id)) {
            $discount_voucher = new CartRule(Configuration::get('ID_AFFILIATE_DISCOUNT_RULE', null, $this->context->shop->id_shop_group, $this->context->shop->id));
            if ($discount_voucher->delete()) {
                Configuration::deleteByName('ID_AFFILIATE_DISCOUNT_RULE');
                return true;
            }
            return false;
        }
        return true;
    }

    public function hookActionAuthentication($params)
    {
        $customer = $params['customer'];
        $cart = $params['cart'];
        if (isset($cart) && $cart) {
            $cart_rules = $cart->getCartRules();
            $groups = Customer::getGroupsStatic($customer->id);
            if (isset($cart_rules) && $cart_rules) {
                foreach ($cart_rules as $rule) {
                    if (in_array((int) Configuration::get('ID_AFFILIATE_GROUP', null, $this->context->shop->id_shop_group, $this->context->shop->id), $groups)
                        && $rule['id_cart_rule'] == Configuration::get('ID_AFFILIATE_DISCOUNT_RULE', null, $this->context->shop->id_shop_group, $this->context->shop->id)) {
                        $cart->removeCartRule($rule['id_cart_rule']);
                    }
                }
            }
        }
    }

    public function hookDisplayAdminListBefore($params)
    {
        if ('AdminAffiliateStates' === Tools::getValue('controller')) {
            return '<div class="col-lg-6">';
        }
    }

    public function hookDisplayAdminListAfter()
    {
        if ('AdminAffiliateStates' === Tools::getValue('controller')) {
            return '</div>';
        }
    }

    public function hookDisplayProductButtons()
    {
        if (true === Tools::version_compare(_PS_VERSION_, '1.7', '>=')) {
            return $this->getProductReward();
        }
    }

    public function hookDisplayRightColumnProduct()
    {
        if (true === Tools::version_compare(_PS_VERSION_, '1.7', '<')) {
            return $this->getProductReward();
        }
    }

    protected function processOrderReward($id_order, $id_order_state)
    {
        $multishop = false;
        $selectedShops = Configuration::get('AFFILIATE_SHOPS');
        $shops = (!empty(trim($selectedShops)))? explode(',', $selectedShops) : [];
        if ((Shop::isFeatureActive() && isset($shops) && in_array($this->context->shop->id, $shops)) || !Shop::isFeatureActive() || empty($shops)) {
            $multishop = true;
        }

        $reward_data = Rewards::getRewardByOrder($id_order);
        $approval_states = Configuration::get(
            'APPROVAL_STATES',
            null,
            $this->context->shop->id_shop_group,
            $this->context->shop->id
        );

        $cancel_states = Configuration::get(
            'CANCEL_STATES',
            null,
            $this->context->shop->id_shop_group,
            $this->context->shop->id
        );

        $approval_states = ($approval_states) ? explode(',', $approval_states) : array();
        $cancel_states = ($cancel_states) ? explode(',', $cancel_states) : array();

        if (!empty($reward_data) && $multishop) {
            foreach ($reward_data as $rew) {
                // getting order state history
                $history = (Affiliation::getOrderStateHistory($id_order)) ? Affiliation::getOrderStateHistory($id_order) : array();
                $reward = new Rewards((int) $rew['id_affiliate_reward']);
                if (!empty($approval_states) && in_array($id_order_state, $approval_states) && !in_array($id_order_state, $history)) {
                    $reward->status = 'approved';
                } elseif (!empty($cancel_states) && in_array($id_order_state, $cancel_states) && !in_array($id_order_state, $history)) {
                    $reward->status = 'cancel';
                }
                if ($reward->update()) {
                    Payment::deleteByReward($reward->id);
                }
            }
        }
    }

    private function getSocialNetworks()
    {
        return array(
            array(
                'id' => 'email',
                'name' => $this->l('Email'),
            ),
            array(
                'id' => 'twitter',
                'name' => $this->l('Twitter'),
            ),
            array(
                'id' => 'facebook',
                'name' => $this->l('Facebook'),
            ),
            array(
                'id' => 'googleplus',
                'name' => $this->l('Googleplus'),
            ),
            array(
                'id' => 'linkedin',
                'name' => $this->l('Linkedin'),
            ),
            array(
                'id' => 'pinterest',
                'name' => $this->l('Pinterest'),
            ),
            array(
                'id' => 'stumbleupon',
                'name' => $this->l('Stumbleupon'),
            ),
            array(
                'id' => 'pocket',
                'name' => $this->l('Pocket'),
            ),
            array(
                'id' => 'viber',
                'name' => $this->l('Viber'),
            ),
            array(
                'id' => 'messenger',
                'name' => $this->l('Facebook Messenger'),
            ),
            array(
                'id' => 'vkontakte',
                'name' => $this->l('VK'),
            ),
            array(
                'id' => 'telegram',
                'name' => $this->l('Telegram'),
            ),
            array(
                'id' => 'line',
                'name' => $this->l('Line'),
            ),
            array(
                'id' => 'whatsapp',
                'name' => $this->l('Whatsapp'),
            ),
        );
    }

    /**
     * GDPR Compliance Hooks
     */
    public function hookActionDeleteGDPRCustomer($customer)
    {
        if (!empty($customer['email']) && Validate::isEmail($customer['email'])) {
            if (Affiliation::deleteAffiliateCustomerData((int) $customer['id'])) {
                return json_decode(true);
            }
            return json_encode($this->l('Affiliate : Unable to delete customer data.'));
        }
    }

    public function hookActionExportGDPRData($customer)
    {
        if (!empty($customer['email']) && Validate::isEmail($customer['email'])) {
            $id_affiliate = Affiliation::getIdByCustomer($customer['id']);
            if (!$id_affiliate) {
                return json_encode($this->l('Affiliate : There is no data to export.'));
            }

            $paymentDetails = array();
            $pendingReferrals = AffiliateInvitations::getPendingInvitations((int) $customer['id']);
            $approvedReferrals = Referrals::getApprovedReferralsByCustomer((int) $customer['id']);
            $rewards = Rewards::getCustomerRewards($customer['id']);

            array_push($paymentDetails, PaymentDetails::getPaymentDetailByType($id_affiliate, 2), PaymentDetails::getPaymentDetailByType($id_affiliate, 1));

            $customerData = array();
            $customfieldsData = array();
            $counter = 0;
            if (isset($pendingReferrals) && count($pendingReferrals) >= 1) {
                foreach ($pendingReferrals as $data) {
                    $customerData[$counter][$this->l('Invitee First Name')] = $data['firstname'];
                    $customerData[$counter][$this->l('Invitee Last Name')] = $data['lastname'];
                    $customerData[$counter][$this->l('Invitee Email')] = $data['email'];
                    $counter++;
                }
            }

            if (isset($approvedReferrals) && count($approvedReferrals) >= 1) {
                foreach ($approvedReferrals as $data) {
                    $customerData[$counter][$this->l('Referral First Name')] = $data['firstname'];
                    $customerData[$counter][$this->l('Referral Last Name')] = $data['lastname'];
                    $customerData[$counter][$this->l('Referral Email')] = $data['email'];
                    $customerData[$counter][$this->l('Source')] = $data['source'];
                    $counter++;
                }
            }

            if (isset($rewards) && count($rewards) >= 1) {
                foreach ($rewards as $data) {
                    $customerData[$counter][$this->l('Registration Reward')] = Tools::displayPrice($data['reg_reward_value']);
                    $customerData[$counter][$this->l('Order Reward')] = Tools::displayPrice($data['ord_reward_value']);
                    $customerData[$counter][$this->l('Reward Paid')] = ($data['is_paid']) ? $this->l('Yes') : $this->l('No');
                    $counter++;
                }
            }
            if (isset($paymentDetails) && count($paymentDetails) >= 1) {
                foreach ($paymentDetails as $data) {
                    $customerData[$counter][$this->l('Payment Type')] = ($data['type'] == 1) ? $this->l('Paypal') : $this->l('Wire Bank');
                    $customerData[$counter][$this->l('Payment Details')] = $data['details'];
                    $counter++;
                }
            }

            if (isset($customerData) && $customerData) {
                foreach ($customerData as $cdata) {
                    array_push($customfieldsData, $cdata);
                }
            }
            if (isset($customfieldsData) && $customfieldsData) {
                return json_encode($customfieldsData);
            }
            return json_encode($this->l('Affiliate : There is no data to export.'));
        }
        return json_encode($this->l('Affiliate : Unable to export customer data.'));
    }

    public function paymentMethodForm($id_payment_method = null)
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => ($id_payment_method) ? $this->l('Update Payment Method') : $this->l('Add Payment Method'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'hidden',
                        'name' => 'id_payment_method',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Payment Name'),
                        'name' => 'payment_name',
                        'desc' => $this->l('Name of your payment method.'),
                        'lang' => false,
                        'required' => true,
                    ),
                    array(
                        'type' => 'text',
                        'lang' => true,
                        'label' => $this->l('Payment method description'),
                        'name' => 'payment_description',
                        'desc' => $this->l('Please enter a short but meaningful description for payment method.'),
                    ),
                ),
                'submit' => array(
                    'title' => ($id_payment_method) ? $this->l('Update Payment') : $this->l('Add Payment'),
                ),
                'buttons' => array(
                    array(
                        'href' => 'javascript:void(0);',
                        'title' => $this->l('Cancel'),
                        'icon' => 'process-icon-cancel',
                        'js' => '$.fancybox.close();window.location.reload(true);',
                    ),
                ),
            ),
        );

        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = 'payment_method';
        $helper->default_form_language = $lang->id;
        $helper->module = $this;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG')? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = 'id_' . $this->table;
        $helper->submit_action = 'submitPaymentMethod';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'uri' => $this->getPathUri(),
            'fields_value' => $this->getPaymentFormConfigs($id_payment_method),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getPaymentFormConfigs($id_payment_method = null)
    {
        $fields = [];
        $languages = Language::getLanguages(false);
        if (!$id_payment_method || !Validate::isLoadedObject($pm = new PaymentMethod((int) $id_payment_method))) {
            $fields['id_payment_method'] = Tools::getValue('id_payment_method', 0);
            $fields['payment_name'] = Tools::getValue('payment_name');
            foreach ($languages as $lang) {
                $fields['payment_description'][$lang['id_lang']] = Tools::getValue('payment_description_' . $lang['id_lang'], '');
            }
        } else {
            $fields['id_payment_method'] = $pm->id;
            $fields['payment_name'] = $pm->payment_name;
            foreach ($languages as $lang) {
                $fields['payment_description'][$lang['id_lang']] = $pm->payment_description[$lang['id_lang']];
            }
        }
        return $fields;
    }

    protected function getProductReward()
    {
        if (isset($this->context->customer) &&
        Validate::isLoadedObject($affiliate = new Affiliation(Affiliation::getIdByCustomer($this->context->customer->id)))) {
            $id_product = Tools::getValue('id_product');
            $productReward = Levels::getProductHighestReward($id_product);
            $productlink = $this->context->link->getProductLink($id_product).'&'.http_build_query(array('src' => 'link', 'ref' => $affiliate->ref_key));
            if (Configuration::get('PS_REWRITING_SETTINGS')) {
                $productlink = Tools::strReplaceFirst('&', '?', $productlink);
            }
            if (isset($productReward) && $productReward && isset($productReward['id_affiliate_levels']) && $productReward['id_affiliate_levels']) {
                $this->context->smarty->assign('reward', $productReward);
                $this->context->smarty->assign('productlink', $productlink);
                $this->context->smarty->assign('ps_17', (int) (Tools::version_compare(_PS_VERSION_, '1.7', '>=') == true) ? 1 : 0);
                return $this->display(__FILE__, 'product-reward.tpl');
            }
        }
    }

    public function getLevelTypes()
    {
        return array(
            $this->l('Fixed'),
            $this->l('Percentage of Order'),
            $this->l('Product Specific'),
            $this->l('Category Specific'),
        );
    }

    public function getMenu()
    {
        $this->context->smarty->assign(array(
            'link' => $this->context->link,
            'dashboard_link' => $this->getAffiliateUrl(),
            'multishop' => (bool) Shop::isFeatureActive(),
            'module_path' => __PS_BASE_URI__ . 'modules/' . $this->name . '/views/',
            'nbrAffiliates' => (int) count(Affiliation::getAllAffiliates()),
            'nbrReferrals' => (int) count(Referrals::getAllReferrals()),
            'nbrWithdrawals' => (int) Affiliation::getWithdrawCollection(),
            'nbrLevels' => (int)Levels::countLevels(),
            'nbrRules' => (int)Rules::countRules(),
        ));
        return $this->display(__FILE__, 'views/templates/admin/menu.tpl');
    }

    public function getAffiliateUrl()
    {
        return $this->context->link->getAdminLink('AdminModules') . '&' . http_build_query(array(
            'configure' => $this->name,
            'tab_module' => $this->tab,
            'module_name' => $this->name,
        ));
    }

    public function getAffiliateVouchers()
    {
        return Affiliation::getAffiliateVuchers();
    }

    public function isAffiliateCustomer($id_customer)
    {
        if (!$id_customer) {
            return false;
        }

        return (bool) Affiliation::getIdByCustomer($id_customer);
    }
    /**
     * alter pk @since 1.7.6.1
     * @return bool
     */
    public function alterKey()
    {
        $return = true;
        foreach ($this->getNewTableNames() as $primaryTable => $pk) {
            if (Affiliation::keyExists($primaryTable, $pk)) {
                $newPk = 'id_' . $primaryTable;
                $return &= Affiliation::alterPKey($primaryTable, $pk, $newPk);
                if ($return) {
                    foreach ($this->getAllTables() as $foreignTable) {
                        if ($primaryTable != $foreignTable && Affiliation::keyExists($foreignTable, $pk)) {
                            Affiliation::alterPKey($foreignTable, $pk, $newPk, false);
                        }
                    }
                }
            }
        }

        return $return;
    }

    /**
     * check if new table already exists
     * @param  string   $table
     * @return bool
     */
    public function tableExists($table)
    {
        return Affiliation::tableExists($table);
    }

    /**
     * create new payment tables
     * @param  string   $table
     * @return bool
     */
    public function createPmTables($table)
    {
        return Affiliation::createPmTables($table);
    }

    /**
     * get table old-keys
     * @return array
     */
    public function getNewTableNames()
    {
        return array(
            'affiliate_rules' => 'id_rule',
            'affiliate_levels' => 'id_level',
            'affiliate_reward' => 'id_reward',
            'affiliate_payment' => 'id_payment',
            'affiliate_referral' => 'id_referral',
            'affiliate_invitation' => 'id_invitation',
            'affiliate_payment_details' => 'id_detail',
        );
    }

    protected function getAllTables()
    {
        return array(
            'affiliate',
            'affiliate_rules',
            'affiliate_reward',
            'affiliate_levels',
            'affiliate_banners',
            'affiliate_payment',
            'affiliate_referral',
            'affiliate_invitation',
            'affiliate_levels_products',
            'affiliate_payment_details',
            'affiliate_levels_categories',
        );
    }

    public function removeOldFiles()
    {
        if (true == (bool) Tools::version_compare(_PS_VERSION_, '1.7', '<')) {
            if (file_exists(_PS_OVERRIDE_DIR_.'controllers/front/ParentOrderController.php')) {
                @unlink(_PS_OVERRIDE_DIR_.'controllers/front/ParentOrderController.php');
            }
        }
        return true;
    }
}

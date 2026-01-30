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

class AdminAffiliatesConversionController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'customer';
        $this->className = 'Customer';
        $this->identifier = 'id_customer';
        $this->explicitSelect = true;
        $this->deleted = false;
        $this->bootstrap = true;
        $this->addRowAction('convert');
        parent::__construct();
        $affiliate_collection = Affiliation::getAllAffiliates();
        if (!empty($affiliate_collection) && (int)count($affiliate_collection) > 0) {
            $affiliate_collection = implode(',', array_map('intval', $affiliate_collection));
        }
        if (isset($affiliate_collection) && $affiliate_collection) {
            $this->_join = '
            LEFT JOIN `'._DB_PREFIX_.'affiliate` af ON (a.`id_customer` = af.`id_customer`)';
            $this->_where = 'AND a.`id_customer` NOT IN ('.$affiliate_collection.')';
            $this->_group = 'GROUP BY a.id_customer';
            $this->_use_found_rows = true;
        }
        $this->fields_list = array(
            'id_customer'  => array(
                'title'     => $this->l('ID'),
                'width'     => 25
            ),
            'firstname' => array(
                'title' => $this->l('First name')
            ),
            'lastname' => array(
                'title' => $this->l('Last name')
            ),
            'email' => array(
                'title' => $this->l('Email address')
            ),
            'date_add' => array(
                'title' => $this->l('Date Created'),
                'type' => 'datetime',
                'orderby' => false
            ),
        );
        $this->bulk_actions = array(
            'updateAffiliatesStatus' => array('text' => $this->l('Convert into Affiliates'), 'icon' => 'icon-refresh')
        );
    }

    public function initToolbar()
    {
        $this->toolbar_title[] = $this->l('Convert Customers into Affiliates');
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
    }

    public function renderList()
    {
        $this->list_no_link = true;
        $this->tpl_list_vars['dashboard_link'] = $this->module->getAffiliateUrl();
        $this->context->smarty->assign('currentMenuTab', 'affiliations');
        $this->context->smarty->assign('subMenuTab', 'convert_affiliates');
        $menu = $this->module->getMenu();
        return $menu.parent::renderList();
    }

    public function displayConvertLink($token = null, $id)
    {
        if (!array_key_exists('convert', self::$cache_lang)) {
            self::$cache_lang['convert'] = $this->l('Convert');
        }

        $this->context->smarty->assign(array(
            'href' => self::$currentIndex.
                '&'.$this->identifier.'='.$id.
                '&convert=1&token='.($token != null ? $token : $this->token),
            'action' => self::$cache_lang['convert'],
        ));

        return $this->context->smarty->fetch(dirname(__FILE__).'/../../views/templates/admin/affiliates_conversion/helpers/list/list_action_convert.tpl');
    }

    public function postProcess()
    {
        // If id_customer is sent
        if (Tools::isSubmit('convert') && (int)Tools::getValue('id_customer') > 0) {
            $id_customer = (int)Tools::getValue('id_customer');
            // Redirect if no errors
            if (!count($this->errors)) {
                $affiliation = new Affiliation();
                $id_referral_aff = (int)$affiliation->getRefferalAffiliateId((int)$id_customer);
                if ($id_referral_aff > 0) {//the user is already a referral - change level now
                    $count_level = (int)$affiliation->countAffiliateLevel((int)$id_referral_aff);
                    if ($count_level <= 3) {
                        $count_level = $count_level + 1;
                    }
                    $affiliation->level = $count_level;
                }
                $affiliation->id_customer = (int)$id_customer;
                $affiliation->id_guest = (int)$this->context->cookie->id_guest;
                $affiliation->ref_key = Tools::passwdGen((Configuration::get('REFERAK_KEY_LEN', null, $this->context->shop->id_shop_group, $this->context->shop->id)? (int)Configuration::get('REFERAK_KEY_LEN', null, $this->context->shop->id_shop_group, $this->context->shop->id) : 16), 'ALPHANUMERIC');
                $affiliation->active = 1;
                $affiliation->approved = true;
                $affiliation->date_from = date('Y-m-d H:i:s');
                if ($affiliation->add()) {
                    if ($affiliation->id) {
                        $affiliate = new Affiliation($affiliation->id);
                        if ($affiliate->approved > 0) {
                            $affiliate->addToAffiliateGroup((int)$affiliate->id_customer, (int)Configuration::get('ID_AFFILIATE_GROUP'));
                            $this->sendApprovalAlert($affiliate->id);
                        }
                    }
                    Tools::redirectAdmin(self::$currentIndex.'&conf=5&token='.$this->token);
                }
            }
        } elseif (Tools::isSubmit('submitBulkupdateAffiliatesStatuscustomer')) {
            $customers = Tools::getValue('customerBox');
            if (!empty($customers)) {
                foreach ($customers as $cus) {
                    $affiliation = new Affiliation();
                    $id_referral_aff = (int)$affiliation->getRefferalAffiliateId((int)$cus);
                    if ($id_referral_aff > 0) {//the user is already a referral - change level now
                        $count_level = (int)$affiliation->countAffiliateLevel((int)$id_referral_aff);
                        if ($count_level <= 3) {
                            $count_level = $count_level + 1;
                        }
                        $affiliation->level = $count_level;
                    }
                    $affiliation->id_customer = (int)$cus;
                    $affiliation->id_guest = (int)$this->context->cookie->id_guest;
                    $affiliation->ref_key = Tools::passwdGen((Configuration::get('REFERAK_KEY_LEN', null, $this->context->shop->id_shop_group, $this->context->shop->id)? (int)Configuration::get('REFERAK_KEY_LEN', null, $this->context->shop->id_shop_group, $this->context->shop->id) : 16), 'ALPHANUMERIC');
                    $affiliation->active = 1;
                    $affiliation->approved = true;
                    $affiliation->date_from = date('Y-m-d H:i:s');
                    if ($affiliation->add()) {
                        if ($affiliation->id) {
                            $affiliate = new Affiliation($affiliation->id);
                            if ($affiliate->approved > 0) {
                                $affiliate->addToAffiliateGroup((int)$affiliate->id_customer, (int)Configuration::get('ID_AFFILIATE_GROUP'));
                                $this->sendApprovalAlert($affiliate->id);
                            }
                        }
                    }
                }
                Tools::redirectAdmin(self::$currentIndex.'&conf=5&token='.$this->token);
            }
        }
        parent::postProcess();
    }
    
    protected function sendApprovalAlert($id_affiliate)
    {
        $result = false;
        $affiliate = Affiliation::approveAffiliate($id_affiliate);
        if (isset($affiliate) && $affiliate) {
            // sending customer affiliate request to store admin
            if ($affiliate['email'] && Validate::isEmail($affiliate['email'])) {
                $vars = array(
                    '{email}' => (string)$affiliate['email'],
                    '{lastname_affiliate}' => (string)$affiliate['lastname'],
                    '{firstname_affiliate}' => (string)$affiliate['firstname'],
                    '{req_date}' => date('Y-m-d H:i:s'),
                    '{shop_url}' => Context::getContext()->link->getPageLink('index', true, $this->context->language->id, null, false, $affiliate['id_shop']),
                    '{my_account_url}' => $this->context->link->getPageLink('my-account', true, $this->context->language->id, null, false, $affiliate['id_shop']),
                );

                $result = Mail::Send(
                    (int)$this->context->language->id,
                    'affiliation_conversion_by_admin',
                    Mail::l('You are now an affiliate', (int)$this->context->language->id),
                    $vars,
                    $affiliate['email'],
                    null,
                    null,
                    null,
                    null,
                    null,
                    _PS_MODULE_DIR_.'affiliates/mails/',
                    false,
                    $affiliate['id_shop']
                );
            }
        }
        return $result;
    }
}

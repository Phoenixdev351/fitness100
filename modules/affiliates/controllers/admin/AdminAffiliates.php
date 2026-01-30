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

class AdminAffiliatesController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'affiliate';
        $this->className = 'Affiliation';
        $this->identifier = 'id_affiliate';
        $this->lang = false;
        $this->deleted = false;
        $this->colorOnBackground = false;
        $this->bootstrap = true;

        parent::__construct();

        $this->context = Context::getContext();
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Referrals associated with selected customer(s) will also deleted.Delete selected items?'),
            ),
            'enableSelection' => array(
                'text' => $this->l('Unblock selection'),
                'icon' => 'icon-power-off text-success',
            ),
            'disableSelection' => array(
                'text' => $this->l('Block selection'),
                'icon' => 'icon-power-off text-danger',
            ),
        );

        $this->_select = 'CONCAT(LEFT(c.`firstname`, 1), \'. \', c.`lastname`) AS `affiliate`, c.email, c.firstname, c.id_shop';
        $this->_join = 'LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON (c.`id_customer` = a.`id_customer`)';
        $this->_use_found_rows = true;
        $this->fields_list = array(
            'id_affiliate' => array(
                'title' => $this->l('ID'),
                'width' => 25,
            ),
            'firstname' => array(
                'title' => $this->l('Name'),
                'width' => 'auto',
            ),
            'email' => array(
                'title' => $this->l('Email'),
                'width' => 'auto',
            ),
            'level' => array(
                'title' => $this->l('Level'),
                'width' => 'auto',
            ),
            'date_from' => array(
                'title' => $this->l('Requested Date'),
                'width' => 'auto',
            ),
            'approved' => array(
                'title' => $this->l('Approved'),
                'width' => 70,
                'active' => 'approved',
                'type' => 'bool',
                'align' => 'center',
                'orderby' => false,
            ),
            'active' => array(
                'title' => $this->l('Status'),
                'width' => 70,
                'align' => 'center',
                'orderby' => false,
                'callback' => 'blockAffiliate',
            ),
        );

        if (Shop::isFeatureActive()) {
            $this->fields_list['id_shop'] = array(
                'title' => $this->l('Shop'),
                'width' => 25,
                'align' => 'center',
                'callback' => 'getShopName',
                'class' => 'active',
            );
        }
    }

    public function getShopName($id_shop)
    {
        $shop = new Shop($id_shop);
        return $shop->name;
    }

    public function blockAffiliate($active, $row)
    {
        $this->context->smarty->assign(array(
            'active' => $active,
            'uri' => $this->module->getPathUri(),
            'blockLink' => self::$currentIndex . '&token=' . $this->token . '&id_affiliate=' . (int) $row['id_affiliate'] . '&active' . $this->table,
        ));
        return $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/block-link.tpl');
    }

    public function renderList()
    {
        // Adds an Edit button for each result
        $this->addRowAction('edit');
        // Adds a Delete button for each result
        $this->addRowAction('delete');

        $menu = $this->getMenu();
        return $menu . parent::renderList();
    }

    public function initToolbar()
    {
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
    }

    public function renderForm()
    {
        $obj = $this->loadObject(true);
        $back = Tools::safeOutput(Tools::getValue('back', ''));
        if (empty($back)) {
            $back = self::$currentIndex . '&token=' . $this->token;
        }

        $type = 'switch';
        if (Tools::version_compare(_PS_VERSION_, '1.6.0.0', '<')) {
            $type = 'radio';
        }
        $default_currency = (int) Configuration::get('PS_CURRENCY_DEFAULT');
        $store_currency = new Currency($default_currency);
        $this->fields_form = array(
            'tinymce' => true,
            'legend' => array(
                'title' => $this->l('Edit Affiliate'),
                'icon' => 'icon-user',
            ),
            'input' => array(
                array(
                    'type' => $type,
                    'label' => $this->l('Approval'),
                    'name' => 'approved',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'approved_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ),
                        array(
                            'id' => 'approved_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ),
                    ),
                ),
                array(
                    'type' => 'text',
                    'prefix' => $store_currency->sign,
                    'col' => '4',
                    'label' => $this->l('Refferal Discount'),
                    'name' => 'individual_voucher',
                    'required' => false,
                    'desc' => $this->l('If this is filled than global discount to refferals will be ignored and this value will be used'),
                    'hint' => $this->l('Fill greater than zero value to activate.'),
                ),
                array(
                    'type' => 'textbutton',
                    'label' => $this->l('Voucher Code(Optional)'),
                    'name' => 'voucher_code',
                    'id' => 'voucher_code',
                    'required' => false,
                    'col' => '4',
                    'desc' => $this->l('Unique voucher for this affiliate to share. Leave empty to use global code from configuration page.'),
                    'hint' => $this->l('Leave empty to use global code from configuration page.'),
                    'button' => array(
                        'label' => $this->l('Generate!'),
                        'attributes' => array(
                            'onclick' => 'gencodeFmm(16)',
                        ),
                    ),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );
        if ($obj->id_voucher) {
            $voucher = new CartRule($obj->id_voucher);
            $voucher_code = $voucher->code;
            $this->fields_value = array(
                'voucher_code' => $voucher_code,
            );
        }

        $menu = $this->getMenu();
        return $menu . parent::renderForm();
    }

    public function postProcess()
    {
        $c_index = $this->context->link->getAdminLink('AdminAffiliates');
        if (Tools::isSubmit('approved' . $this->table)) {
            $id_affiliate = (int) Tools::getValue('id_affiliate');
            if ($id_affiliate) {
                $affiliate = new Affiliation($id_affiliate);
                $affiliate->approved = !$affiliate->approved;
                if ($affiliate->update()) {
                    if ($affiliate->approved == 1) {
                        $affiliate->addToAffiliateGroup((int) $affiliate->id_customer, (int) Configuration::get('ID_AFFILIATE_GROUP'));
                        $this->sendApprovalAlert($affiliate->id);
                    } elseif ($affiliate->approved == 0) {
                        $affiliate->cleanAffiliateGroup((int) $affiliate->id_customer, (int) Configuration::get('ID_AFFILIATE_GROUP'));
                    }
                    Tools::redirectAdmin($c_index . '&conf=4');
                } else {
                    $this->errors[] = $this->l('operation failed');
                }
            }
        }

        if (Tools::isSubmit('active' . $this->table)) {
            $id_affiliate = (int) Tools::getValue('id_affiliate');
            if ($id_affiliate) {
                $affiliate = new Affiliation($id_affiliate);
                $affiliate->active = !$affiliate->active;
                if (!$affiliate->update()) {
                    $this->errors[] = $this->l('Affiliate status update failed.');
                } else {
                    $this->confirmations[] = $this->l('Affiliate status updated successfully');
                }
            }
        }

        if (Tools::isSubmit('submitAdd' . $this->table)) {
            $id_affiliate = (int) Tools::getValue('id_affiliate');
            $approved = (int) Tools::getValue('approved');
            $ref_key = (string) Tools::getValue('ref_key');
            $voucher_code = Tools::getValue('voucher_code');
            $indiv_voucher = Tools::getValue('individual_voucher');
            $indiv_voucher = Tools::ps_round($indiv_voucher, 2);
            if ($id_affiliate) {
                $affiliate = new Affiliation($id_affiliate);
                $affiliate->approved = $approved;
                $affiliate->ref_key = $ref_key;
                $affiliate->individual_voucher = $indiv_voucher;
                if ($indiv_voucher > 0 && (int) $affiliate->id_voucher <= 0) { //we have to create individual voucher
                    if (empty($voucher_code)) {
                        $default_voucher = (int) Configuration::get('ID_AFFILIATE_DISCOUNT_RULE', null, $this->context->shop->id_shop_group, $this->context->shop->id);
                        if ($default_voucher > 0) {
                            $core_voucher = new CartRule($default_voucher);
                            $voucher_code = $core_voucher->code;
                        } else {
                            $voucher_code = Tools::passwdGen(12, 'ALPHANUMERIC');
                        }
                    }
                    $id_voucher = (int) $this->createVoucher($id_affiliate, $indiv_voucher, $voucher_code);
                    if ($id_voucher > 0) {
                        $affiliate->id_voucher = $id_voucher;
                    }
                } elseif ($indiv_voucher > 0 && (int) $affiliate->id_voucher > 0) { //update required
                    $ini_voucher = new CartRule((int) $affiliate->id_voucher);
                    $ini_voucher->reduction_amount = $indiv_voucher;
                    $ini_voucher->group_restriction = 1;
                    if (!empty($voucher_code)) {
                        $ini_voucher->code = $voucher_code;
                    }
                    if ($ini_voucher->update()) {
                        $this->module->setGroupRestrictionOnVoucher($ini_voucher->id);
                    }
                } elseif ((empty($indiv_voucher) || $indiv_voucher <= 0) && (int) $affiliate->id_voucher > 0) { //Delete voucher no longer needed
                    $ini_voucher = new CartRule((int) $affiliate->id_voucher);
                    $ini_voucher->delete();
                    $affiliate->id_voucher = false;
                }
                if ($affiliate->update()) {
                    if (isset($affiliate) && $affiliate->approved == 1) {
                        $affiliate->addToAffiliateGroup((int) $affiliate->id_customer, (int) Configuration::get('ID_AFFILIATE_GROUP'));
                        $this->sendApprovalAlert($affiliate->id);
                    } elseif (isset($affiliate) && $affiliate->approved == 0) {
                        $affiliate->cleanAffiliateGroup((int) $affiliate->id_customer, (int) Configuration::get('ID_AFFILIATE_GROUP'));
                    }
                    Tools::redirectAdmin($c_index . '&conf=4');
                } else {
                    $this->errors[] = $this->l('operation failed');
                }
            }
        }

        if (Tools::isSubmit('delete' . $this->table)) {
            $id_affiliate = (int) Tools::getValue('id_affiliate');
            if ($id_affiliate) {
                $affiliate = new Affiliation($id_affiliate);
                if ($affiliate->delete()) {
                    Tools::redirectAdmin($c_index . '&conf=1');
                } else {
                    $this->errors[] = $this->l('operation failed');
                }
            }
        }

        if (Tools::isSubmit('submitBulkdelete' . $this->table)) {
            $affiliates = Tools::getValue('affiliateBox');
            if (isset($affiliates) && is_array($affiliates)) {
                foreach ($affiliates as $id_affiliate) {
                    $affiliate = new Affiliation((int) $id_affiliate);
                    $affiliate->delete();
                }
            }
        }
        parent::postProcess();
    }

    private function createVoucher($id_affiliate, $amount, $code)
    {
        $languages = Language::getLanguages();
        $discount = new CartRule();
        foreach ($languages as $lang) {
            $discount->name[$lang['id_lang']] = $this->l('Member#') . $id_affiliate . ' ' . $this->l('Affiliate Coupon');
        }
        $discount->date_from = date('Y-m-d H:i:s');
        $discount->date_to = date('Y-m-d H:i:s', strtotime('+5 years'));
        $discount->date_add = date('Y-m-d H:i:s');
        $discount->code = $code;
        $discount->quantity = 1000;
        $discount->partial_use = 0;
        $discount->reduction_currency = (int) Configuration::get('PS_CURRENCY_DEFAULT');
        $discount->highlight = 0;
        $discount->group_restriction = 1;
        $discount->reduction_amount = $amount;
        if ($discount->add()) {
            $this->module->setGroupRestrictionOnVoucher($discount->id);
            return (int) $discount->id;
        } else {
            return false;
        }
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addJqueryUI(array('ui.datepicker'));
        $this->addJS(_PS_MODULE_DIR_ . 'affiliates/views/js/affiliate_admin.js');
    }

    protected function sendApprovalAlert($id_affiliate)
    {
        $result = false;
        $affiliate = Affiliation::approveAffiliate($id_affiliate);
        if (isset($affiliate) && $affiliate) {
            // sending customer affiliate request to store admin
            if ($affiliate['email'] && Validate::isEmail($affiliate['email'])) {
                $vars = array(
                    '{email}' => (string) $affiliate['email'],
                    '{lastname_affiliate}' => (string) $affiliate['lastname'],
                    '{firstname_affiliate}' => (string) $affiliate['firstname'],
                    '{req_date}' => date('Y-m-d H:i:s'),
                    '{shop_url}' => Context::getContext()->link->getPageLink('index', true, $this->context->language->id, null, false, $affiliate['id_shop']),
                    '{my_account_url}' => $this->context->link->getPageLink('my-account', true, $this->context->language->id, null, false, $affiliate['id_shop']),
                );

                $result = Mail::Send(
                    (int) $this->context->language->id,
                    'affiliation_approval',
                    Mail::l('Your affiliation request approved', (int) $this->context->language->id),
                    $vars,
                    $affiliate['email'],
                    null,
                    null,
                    null,
                    null,
                    null,
                    _PS_MODULE_DIR_ . 'affiliates/mails/',
                    false,
                    $affiliate['id_shop']
                );
            }
        }
        return $result;
    }

    protected function getMenu()
    {
        $this->tpl_list_vars['dashboard_link'] = $this->module->getAffiliateUrl();
        $this->context->smarty->assign('currentMenuTab', 'affiliations');
        $this->context->smarty->assign('subMenuTab', 'manage_affiliates');
        return $this->module->getMenu();
    }
}

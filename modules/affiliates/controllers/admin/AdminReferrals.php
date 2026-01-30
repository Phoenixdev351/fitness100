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

class AdminReferralsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'affiliate_referral';
        $this->className = 'Referrals';
        $this->identifier = 'id_affiliate_referral';
        $this->lang = false;
        $this->deleted = false;
        $this->colorOnBackground = false;
        $this->bootstrap = true;

        parent::__construct();

        $this->bulk_actions = array('delete' => array(
            'text' => $this->l('Delete selected'),
            'confirm' => $this->l('Delete selected items?'),
        ));
        $this->context = Context::getContext();
        $this->_select = 'IF(c.`firstname` IS NULL AND c.`lastname` IS NULL,
            (IF(inv.`firstname` IS NULL AND inv.`lastname` IS NULL,
                \'' . $this->l('Guest') . '\',
                CONCAT(LEFT(inv.`firstname`, 1), \'. \', inv.`lastname`))),CONCAT(LEFT(c.`firstname`, 1), \'. \', c.`lastname`)) AS `referral`,
            IF(c.`email` IS NULL, inv.email, c.email) AS email, co.id_shop';
        $this->_join = 'LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON (c.`id_customer` = a.`id_customer`)
        LEFT JOIN `' . _DB_PREFIX_ . 'affiliate_invitation` inv ON (inv.`id_affiliate_referral` = a.`id_affiliate_referral`)
        LEFT JOIN `' . _DB_PREFIX_ . 'connections` co ON (a.`id_guest` = co.`id_guest`)';
        $this->_group = 'GROUP BY a.id_affiliate_referral';

        $this->_use_found_rows = true;
        $this->fields_list = array(
            'id_affiliate_referral' => array(
                'title' => $this->l('ID'),
                'width' => 25,
            ),
            'referral' => array(
                'title' => $this->l('Name'),
                'width' => 'auto',
            ),
            'email' => array(
                'title' => $this->l('Email'),
                'width' => 'auto',
            ),
            'id_affiliate' => array(
                'title' => $this->l('Sponsored by'),
                'width' => 'auto',
                'callback' => 'getSponsor',
                'orderby' => false,
                'search' => false,
            ),
            'date_from' => array(
                'title' => $this->l('Date Added'),
                'width' => 'auto',
            ),
            'active' => array(
                'title' => $this->l('Status'),
                'width' => 70,
                'active' => 'active',
                'type' => 'bool',
                'align' => 'center',
                'orderby' => false,
            ),
        );

        if (Shop::isFeatureActive()) {
            $this->fields_list['id_shop'] = array(
                'title' => $this->l('Shop'),
                'width' => 25,
                'align' => 'center',
                'callback' => 'getShopName',
                'class' => 'alert-success',
            );
        }
    }

    public function getShopName($id_shop)
    {
        $shop = new Shop($id_shop);
        return $shop->name;
    }

    public function getSponsor($id_affiliate)
    {
        $sponsor = '--';
        if ($id_affiliate) {
            $sp = Affiliation::getAffiliateById($id_affiliate);
            if (isset($sp) && $sp) {
                $sponsor = $sp['email'];
            }
        }
        return $sponsor;
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
        $back = Tools::safeOutput(Tools::getValue('back', ''));
        if (empty($back)) {
            $back = self::$currentIndex . '&token=' . $this->token;
        }

        $type = 'switch';
        if (Tools::version_compare(_PS_VERSION_, '1.6.0.0', '<')) {
            $type = 'radio';
        }

        $this->fields_form = array(
            'tinymce' => true,
            'legend' => array(
                'title' => $this->l('Edit Referral'),
                'icon' => 'icon-user',
            ),
            'input' => array(
                array(
                    'type' => $type,
                    'label' => $this->l('Referral Status:'),
                    'name' => 'active',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ),
                    ),
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        $this->context->smarty->assign('is_customer', 0);
        if ($id_affiliate_referral = Tools::getValue('id_affiliate_referral')) {
            $referral = new Referrals((int) $id_affiliate_referral);

            if ($referral->id_customer) {
                $this->getDetails($referral->id_customer);
            }
        }
        $menu = $this->getMenu();
        return $menu . parent::renderForm();
    }

    public function postProcess()
    {
        $c_index = $this->context->link->getAdminLink('AdminReferrals');
        if (Tools::isSubmit('active' . $this->table)) {
            $id_affiliate_referral = (int) Tools::getValue('id_affiliate_referral');
            if ($id_affiliate_referral) {
                $referral = new Referrals($id_affiliate_referral);
                $referral->active = !$referral->active;
                if ($referral->update()) {
                    Tools::redirectAdmin($c_index . '&conf=4');
                } else {
                    $this->errors[] = Tools::displayError('operation failed');
                }
            }
        }

        if (Tools::isSubmit('approved' . $this->table)) {
            $id_affiliate_referral = (int) Tools::getValue('id_affiliate_referral');
            if ($id_affiliate_referral) {
                $referral = new Referrals($id_affiliate_referral);
                $referral->approved = !$referral->approved;
                if ($referral->update()) {
                    Tools::redirectAdmin($c_index . '&conf=4');
                } else {
                    $this->errors[] = Tools::displayError('operation failed');
                }
            }
        }

        if (Tools::isSubmit('submitAdd' . $this->table)) {
            $id_affiliate_referral = (int) Tools::getValue('id_affiliate_referral');
            $active = (int) Tools::getValue('active');
            // $approved = (int)Tools::getValue('approved');
            $date_from = (string) Tools::getValue('date_from');
            $date_to = (string) Tools::getValue('date_to');
            $id_affiliate = (int) Tools::getValue('id_affiliate');

            if ($id_affiliate_referral) {
                $referral = new Referrals($id_affiliate_referral);
                $referral->active = $active;
                $referral->approved = 1;
                $referral->id_affiliate = $id_affiliate;
                $referral->date_from = $date_from;
                $referral->date_to = $date_to;
                if ($referral->update()) {
                    Tools::redirectAdmin($c_index . '&conf=4');
                } else {
                    $this->errors[] = Tools::displayError('operation failed');
                }
            } else {
                $referral = new Referrals();
                $referral->active = $active;
                $referral->approved = 1;
                $referral->id_affiliate = $id_affiliate;
                $referral->date_from = $date_from;
                $referral->date_to = $date_to;
                if ($referral->add()) {
                    Tools::redirectAdmin($c_index . '&conf=3');
                } else {
                    $this->errors[] = Tools::displayError('operation failed');
                }
            }
        }

        if (Tools::isSubmit('delete' . $this->table)) {
            $id_affiliate_referral = (int) Tools::getValue('id_affiliate_referral');
            if ($id_affiliate_referral) {
                $referral = new Referrals($id_affiliate_referral);
                if ($referral->delete()) {
                    Tools::redirectAdmin($c_index . '&conf=1');
                } else {
                    $this->errors[] = Tools::displayError('operation failed');
                }
            }
        }

        if (Tools::isSubmit('submitBulkdelete' . $this->table)) {
            $referrals = Tools::getValue('affiliate_referralBox');
            if (isset($referrals) && is_array($referrals)) {
                foreach ($referrals as $id_affiliate_referral) {
                    $referral = new Referrals((int) $id_affiliate_referral);
                    $referral->delete();
                }
            }
        }
        parent::postProcess();
    }

    protected function getDetails($id_customer)
    {
        /** @var Customer $customer */
        $customer = new Customer((int) $id_customer);
        $gender = new Gender($customer->id_gender, $this->context->language->id);
        $gender_image = $gender->getImage();

        $customer_stats = $customer->getStats();
        $sql = 'SELECT SUM(total_paid_real) FROM ' . _DB_PREFIX_ . 'orders WHERE id_customer = %d AND valid = 1';
        if ($total_customer = Db::getInstance()->getValue(sprintf($sql, $customer->id))) {
            $sql = 'SELECT SQL_CALC_FOUND_ROWS COUNT(*) FROM ' . _DB_PREFIX_ . 'orders WHERE valid = 1 AND id_customer != ' . (int) $customer->id . ' GROUP BY id_customer HAVING SUM(total_paid_real) > %d';
            Db::getInstance()->getValue(sprintf($sql, (int) $total_customer));
            $count_better_customers = (int) Db::getInstance()->getValue('SELECT FOUND_ROWS()') + 1;
        } else {
            $count_better_customers = '-';
        }

        $orders = Order::getCustomerOrders($customer->id, true);
        $total_orders = count($orders);
        for ($i = 0; $i < $total_orders; $i++) {
            $orders[$i]['total_paid_real_not_formated'] = $orders[$i]['total_paid_real'];
            $orders[$i]['total_paid_real'] = Tools::displayPrice($orders[$i]['total_paid_real'], new Currency((int) $orders[$i]['id_currency']));
        }

        $groups = $customer->getGroups();
        $total_groups = count($groups);
        for ($i = 0; $i < $total_groups; $i++) {
            $group = new Group($groups[$i]);
            $groups[$i] = array();
            $groups[$i]['id_group'] = $group->id;
            $groups[$i]['name'] = $group->name[$this->default_form_language];
        }

        $total_ok = 0;
        $orders_ok = array();
        $orders_ko = array();
        foreach ($orders as $order) {
            if (!isset($order['order_state'])) {
                $order['order_state'] = $this->l('There is no status defined for this order.');
            }

            if ($order['valid']) {
                $orders_ok[] = $order;
                $total_ok += $order['total_paid_real_not_formated'];
            } else {
                $orders_ko[] = $order;
            }
        }

        $products = $customer->getBoughtProducts();

        $carts = Cart::getCustomerCarts($customer->id);
        $total_carts = count($carts);
        for ($i = 0; $i < $total_carts; $i++) {
            $cart = new Cart((int) $carts[$i]['id_cart']);
            $this->context->cart = $cart;
            $summary = $cart->getSummaryDetails();
            $currency = new Currency((int) $carts[$i]['id_currency']);
            $carrier = new Carrier((int) $carts[$i]['id_carrier']);
            $carts[$i]['id_cart'] = sprintf('%06d', $carts[$i]['id_cart']);
            $carts[$i]['date_add'] = Tools::displayDate($carts[$i]['date_add'], null, true);
            $carts[$i]['total_price'] = Tools::displayPrice($summary['total_price'], $currency);
            $carts[$i]['name'] = $carrier->name;
        }

        $sql = 'SELECT DISTINCT cp.id_product, c.id_cart, c.id_shop, cp.id_shop AS cp_id_shop
                FROM ' . _DB_PREFIX_ . 'cart_product cp
                JOIN ' . _DB_PREFIX_ . 'cart c ON (c.id_cart = cp.id_cart)
                JOIN ' . _DB_PREFIX_ . 'product p ON (cp.id_product = p.id_product)
                WHERE c.id_customer = ' . (int) $customer->id . '
                AND NOT EXISTS (
                    SELECT 1
                    FROM ' . _DB_PREFIX_ . 'orders o
                    JOIN ' . _DB_PREFIX_ . 'order_detail od ON (o.id_order = od.id_order)
                    WHERE product_id = cp.id_product AND o.valid = 1 AND o.id_customer = ' . (int) $customer->id . '
                )';
        $interested = Db::getInstance()->executeS($sql);
        $total_interested = count($interested);
        for ($i = 0; $i < $total_interested; $i++) {
            $product = new Product($interested[$i]['id_product'], false, $this->default_form_language, $interested[$i]['id_shop']);
            if (!Validate::isLoadedObject($product)) {
                continue;
            }
            $interested[$i]['url'] = $this->context->link->getProductLink(
                $product->id,
                $product->link_rewrite,
                Category::getLinkRewrite($product->id_category_default, $this->default_form_language),
                null,
                null,
                $interested[$i]['cp_id_shop']
            );
            $interested[$i]['id'] = (int) $product->id;
            $interested[$i]['name'] = Tools::htmlentitiesUTF8($product->name);
        }

        $connections = $customer->getLastConnections();
        if (!is_array($connections)) {
            $connections = array();
        }
        $total_connections = count($connections);
        for ($i = 0; $i < $total_connections; $i++) {
            $connections[$i]['http_referer'] = $connections[$i]['http_referer'] ? preg_replace('/^www./', '', parse_url($connections[$i]['http_referer'], PHP_URL_HOST)) : $this->l('Direct link');
        }

        // $referrers = Referrer::getReferrers($customer->id);
        // $total_referrers = count($referrers);
        // for ($i = 0; $i < $total_referrers; $i++) {
        //     $referrers[$i]['date_add'] = Tools::displayDate($referrers[$i]['date_add'], null, true);
        // }

        $customerLanguage = new Language($customer->id_lang);
        $shop = new Shop($customer->id_shop);
        $this->context->smarty->assign(array(
            'customer' => $customer,
            'cToken' => Tools::getAdminToken('AdminCustomers' . (int) Tab::getIdFromClassName('AdminCustomers') . (int) $this->context->employee->id),
            'gender' => $gender,
            'gender_image' => $gender_image,
            // General information of the customer
            'registration_date' => Tools::displayDate($customer->date_add, null, true),
            'customer_stats' => $customer_stats,
            'last_visit' => Tools::displayDate($customer_stats['last_visit'], null, true),
            'count_better_customers' => $count_better_customers,
            'shop_is_feature_active' => Shop::isFeatureActive(),
            'name_shop' => $shop->name,
            'customer_birthday' => Tools::displayDate($customer->birthday),
            'last_update' => Tools::displayDate($customer->date_upd, null, true),
            'customer_exists' => Customer::customerExists($customer->email),
            'id_lang' => $customer->id_lang,
            'customerLanguage' => $customerLanguage,
            // Add a Private note
            'customer_note' => Tools::htmlentitiesUTF8($customer->note),
            // Groups
            'groups' => $groups,
            // Orders
            'orders' => $orders,
            'orders_ok' => $orders_ok,
            'orders_ko' => $orders_ko,
            'total_ok' => Tools::displayPrice($total_ok, $this->context->currency->id),
            // Products
            'products' => $products,
            // Addresses
            'addresses' => $customer->getAddresses($this->default_form_language),
            // Discounts
            'discounts' => CartRule::getCustomerCartRules($this->default_form_language, $customer->id, false, false),
            // Carts
            'carts' => $carts,
            // Interested
            'interested' => $interested,
            // Connections
            'connections' => $connections,
            // Referrers
            'referrers' => '-',
            'show_toolbar' => true,
            'is_customer' => 1,
            'version' => _PS_VERSION_,
        ));
    }

    protected function getMenu()
    {
        $this->tpl_list_vars['dashboard_link'] = $this->module->getAffiliateUrl();
        $this->context->smarty->assign('currentMenuTab', 'affiliations');
        $this->context->smarty->assign('subMenuTab', 'referrals');
        return $this->module->getMenu();
    }
}

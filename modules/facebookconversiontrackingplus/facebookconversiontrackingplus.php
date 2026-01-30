<?php
/**
 *  Facebook Conversion Pixel Tracking Plus
 *
 * NOTICE OF LICENSE
 *
 * @author    Pol Rué
 * @copyright Smart Modules 2014
 * @license   One time purchase Licence (You can modify or resell the product but just one time per licence)
 * @version 2.3.3
 * @category Marketing & Advertising
 * Registered Trademark & Property of smart-modules.com
 *
 * ****************************************************
 * *                    Pixel Plus                    *
 * *          http://www.smart-modules.com            *
 * *                     V 2.3.3                      *
 * ****************************************************
 *
 * Versions:
 * To check the complete changelog. open versions.txt file
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/classes/google-categories.php';
require_once dirname(__FILE__) . '/classes/conversion-api.php';

class FacebookConversionTrackingPlus extends Module
{
    private $_html = '';
    public $type = '';
    public $pixelparams = '';
    private $pixels = array();
    private $extras_type = '';
    private $pixels_printed = false;
    private $schema = null;
    private $og = null;
    private $schema_structure = null;
    private $content_displayed = false;
    private $display_microdata = true;
    private $feed_id = 0;
    public static $feed_v2 = false;
    public function __construct()
    {
        $this->is_17 = version_compare(_PS_VERSION_, '1.7', '>=');
        /* $tab = $this->is_17 ? 'market_place' : 'advertising_marketing';*/
        $this->name = 'facebookconversiontrackingplus';
        $this->tab = 'advertising_marketing';
        $this->version = '2.3.3';
        $this->author = 'Smart Modules';
        $this->author_address = '0x29aAc34Cc2542b6816fF066E1Da67924EF9e56f6';
        $this->need_instance = 0;
        $this->module_key = '3e316ca70bb2494f37010fc46feb2f4d';
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Pixel Plus for Facebook: Events + Conversions API + Pixel Catalogue');
        $this->description = $this->l('Track Facebook events with the Pixel & Conversion API. Measure the ROI of your ads, create catalogues, dynamic ads, tag products on Instagram, create shops on Facebook, microdata... GDPR Ready, iOS 14.5 Ready');
        $this->type = array(
            1 => $this->l('Key Page')
        );
        $this->extras_type = array(
            1 => 'index',
            //2 => 'category',
            //3 => 'product',
            //4 => 'search',
            4 => 'cms',
            5 => 'contact'
        );
        $this->extras_type_lang = array(
            1 => $this->l('Index'),
            //2 => $this->l('Category'),
            //3 => $this->l('Product'),
            //4 => $this->l('Search'),
            4 => $this->l('CMS'),
            5 => $this->l('Contact')
        );
        $this->pixelparams = array('pixel_active' => '0', 'pixel_name' => '', 'pixel_extras' => '', 'pixel_extras_type' => '', 'pixel_extras_name' => '');
        $this->confirmUninstall = $this->l('Are you sure about removing all your Facebook Pixels?');
        if (Configuration::get('FCTP_CONVERSION_API') && (@$this->context->controller->controller_type == 'front' ||  @$this->context->controller->controller_type == 'modulefront')) {
            $this->tryLoadingAPI();
        } else {
            $this->api = false;
        }
        // Check if facebookproductsfeed is installed to improve the compatibility
        self::$feed_v2 = false;
        if (Module::isEnabled('facebookproductsfeed')) {
            include_once(dirname(__FILE__) . '/../facebookproductsfeed/facebookproductsfeed.php');
            //echo dirname(__FILE__).'/../facebookproductsfeed/facebookproductsfeed.php';
            $fpf = new FacebookProductsFeed();
            if (version_compare($fpf->version, '2.0.5', '>=')) {
                self::$feed_v2 = true;
            }
        }
        $this->form_fields = $this->getFormFields();
        /* Backward compatibility */
        if (_PS_VERSION_ < '1.5') {
            require(_PS_MODULE_DIR_ . $this->name . '/backward_compatibility/backward.php');
        }
        /* Microdata to Create Pixel Catalogues */
        $this->displayMicrodata = (bool)Configuration::get('FCTP_FILL_MICRO_DATA');
        $this->rmd = Tools::jsonDecode(Configuration::get('FCTP_MICRODATA'), true);
        $this->controllers = [
            'AdminExportCustomers',
        ];
    }
    public function install()
    {
        include(dirname(__FILE__) . '/sql/install.php');
        if (!parent::install()
            || !$this->registerHook('actionCustomerAccountAdd')
            || !$this->registerHook('createAccount')
            || !$this->registerHook('header')
            || !$this->registerHook('displayHeader')
            || !$this->registerHook('displayFooter')
            || !$this->registerHook('actionCartSave')
            || !$this->registerHook('orderConfirmation')
            || !$this->registerHook('paymentReturn')
            || !$this->registerHook('actionAdminControllerSetMedia')
            || !$this->registerHook('displayBeforeBodyClosingTag')
            || !$this->registerHook('displayOrderConfirmation')
            || !$this->registerHook('actionValidateOrder')
            // Micro Data Specific Hooks
            || !$this->registerHook('displayProductAdditionalInfo')
            || !$this->registerHook('displayProductButtons')
            || !$this->registerHook('displayRightColumnProduct')
            || !$this->registerHook('displayLeftColumnProduct')
            || !$this->registerHook('actionFrontControllerAfterInit')
        ) {
            return false;
        }
        foreach ($this->form_fields as $field) {
            if ($field['global']) {
                Configuration::updateGlobalValue($field['name'], $field['def']);
            }/* else {
                if ($field['name'] == 'FCTP_ORDER_STATUS_EXCLUDE') {
                    $field['name'] = 'FCTP_ORDER_STATUS_EXCLUDE';
                }
                Configuration::updateValue($field['name'], $field['def']);
            }*/
        }
        Configuration::updateValue('pixel_account_on', '');
        // TODO MultiShop with multiple themes
        $this->checkMicroData();
        $this->installTabs();
        return true;
    }
    public function uninstall($delete = false)
    {
        if ($delete == true) {
            include(dirname(__FILE__) . '/sql/uninstall.php');
        }
        foreach ($this->form_fields as $field) {
            Configuration::deleteByName($field['name']);
        }
        Configuration::deleteByName('pixel_account_on');
        return parent::uninstall() && $this->uninstallTabs();
    }
    public function reset()
    {
        if (!$this->uninstall(false)) {
            return false;
        }
        if (!$this->install()) {
            return false;
        }
        return true;
    }
    /**
     * This method is often use to create an ajax controller
     *
     * @return bool
     */
    public function installTabs()
    {
        $installTabCompleted = true;
        $tab = new Tab();
        foreach ($this->controllers as $controllerName) {
            if (Tab::getIdFromClassName($controllerName)) {
                continue;
            }
            $tab->class_name = $controllerName;
            $tab->active = true;
            $tab->name = [];
            foreach (Language::getLanguages(true) as $lang) {
                $tab->name[$lang['id_lang']] = $this->name;
            }
            $tab->id_parent = -1;
            $tab->module = $this->name;
            $installTabCompleted = $installTabCompleted && $tab->add();
        }
        return $installTabCompleted;
    }
    /**
     * uninstall tabs
     *
     * @return bool
     */
    public function uninstallTabs()
    {
        $uninstallTabCompleted = true;
        foreach ($this->controllers as $controllerName) {
            $id_tab = (int) Tab::getIdFromClassName($controllerName);
            $tab = new Tab($id_tab);
            if (Validate::isLoadedObject($tab)) {
                $uninstallTabCompleted = $uninstallTabCompleted && $tab->delete();
            }
        }
        return $uninstallTabCompleted;
    }
    public function tryLoadingAPI()
    {
        if (!class_exists('ConversionApi')) {
            require_once dirname(__FILE__) . '/classes/conversion-api.php';
        }
        $this->api = new ConversionApi();
    }
    public function getContent()
    {
        $css = '';
        /* Check custom auciences customer generation CSV folder permissions */
        if (!is_writable(dirname(__FILE__) . '/csv/')) {
            $this->context->controller->errors[] = Tools::displayError($this->l('Please make') . ' /modules/' . $this->name . '/csv/ ' . $this->l('folder writable'));
        }
        $this->context->controller->addJS(dirname(__FILE__) . 'views/js/download.js');
        $this->context->smarty->assign(
            array(
                'old_ps' => version_compare(_PS_VERSION_, '1.6', '<='),
                'is_17' => $this->is_17,
                'selected_menu' => Tools::getValue('selected_menu'),
            )
        );
        if (Tools::isSubmit('submit' . $this->name)) {
            if (Tools::getValue('FCTP_CHECK_MICRO_DATA')) {
                $this->checkMicroData();
                // Update the RMD value
                $this->rmd = Tools::jsonDecode(Configuration::get('FCTP_MICRODATA'), true);
            }
        }
        // If it's a submit
        if (Tools::isSubmit('add' . $this->name)) {
            return $this->createNewPixel() . $this->newpixelJS();
        } elseif (Tools::isSubmit('update' . $this->name)) {
            return $this->createNewPixel(Tools::getValue('id_facebookpixels')) . $this->newpixelJS();
        } else {
            // Show the basic configuration page
            // Check if Blocknewsletter Module is activated
            if (Module::isEnabled('blocknewsletter')) {
                $this->context->smarty->assign('newsletter', 1);
            } else {
                $this->context->smarty->assign('newsletter', 0);
            }
            if (version_compare(_PS_VERSION_, '1.6', '<')) {
                $this->context->smarty->assign(array('oldps' => 1));
            } else {
                $this->context->smarty->assign(array('oldps' => 0));
            }
            $this->context->smarty->assign('myurl', $this->getCurrentUrl());
            $this->context->smarty->assign('mytoken', Tools::getAdminTokenLite('AdminModules'));
            $export_customer_url = Context::getContext()->link->getAdminLink('AdminExportCustomers');
            //Download customer url
            $this->context->smarty->assign(
                array(
                    'export_customer_url' => $export_customer_url,
                    'remoteAddr' => Tools::getRemoteAddr(),
                )
            );
            $output = $this->display(__FILE__, '/views/templates/admin/configuration.tpl');
            $test_pixels = '';
            if (Configuration::get('FCTP_PIXEL_ID') != '') {
                $pixel_ids = explode(',', preg_replace('/[^,0-9]/', '', Configuration::get('FCTP_PIXEL_ID')));
                $this->context->smarty->assign(array(
                    'fctpid' => $pixel_ids,
                    'pixelsetup' => 1,
                    'fctp_test_values' => $this->getDefaultValuesForPixelTests(),
                    'product_catalog_id' => $this->getCatalogueIdForTest(),
                    'currency' => $this->context->currency->iso_code,
                ));
                $test_pixels = $this->display(__FILE__, '/views/templates/admin/test-pixels.tpl');
            }
            if (Tools::isSubmit('submit' . $this->name)) {
                $this->postProcess();
                if (version_compare(_PS_VERSION_, '1.6', '<')) {
                    // For 1.5 and below, redirect to avoid conflicts
                    $redUrl = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'] . __PS_BASE_URI__ . basename(_PS_ADMIN_DIR_) . '/index.php?controller=AdminModules&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules');
                    Tools::redirect($redUrl);
                }
            }
            if (Tools::isSubmit('submitnewpixel')) {
                $validation = $this->validatenewpixelform();
                if ($validation['ok'] == true) {
                    $output .= $validation['output'];
                } else {
                    return $validation['output'] . $this->createNewPixel(Tools::getValue('id_facebookpixels')) . $this->newpixelJS();
                }
            }
            if (Tools::isSubmit('delete' . $this->name)) {
                $output .= $this->deletepixel();
            }
            if (Tools::isSubmit('status' . $this->name)) {
                $output .= $this->statuspixel();
            }
            // Generate the basic Pixel configuration
            $output .= $this->getBasicForm();
            // Check if requiested to generate a CSV customer list
            $typexp = Tools::getValue('typexp');
            if ($typexp != '') {
                if (self::getProcess($typexp) == true) {
                    $relative_url = 'modules/facebookconversiontrackingplus/download.php?typexp=' . (int)Tools::getValue('typexp') . '&token=' . Tools::getAdminTokenLite('AdminModules');
                    $this->context->smarty->assign(
                        array(
                            'fctp_rurl' => '/' . $relative_url,
                            'fctp_url' => (Configuration::get('PS_SSL_ENABLED') ? 'https://' . $this->context->shop->domain_ssl : 'http://' . $this->context->shop->domain) . __PS_BASE_URI__ . $relative_url,
                        )
                    );
                    $output .= $this->display(__FILE__, '/views/templates/admin/clients-export.tpl');
                }
            }
            $output .= $this->getGoogleProductCategories();
            $output .= $test_pixels;
            $output .= $this->displayVideos();
            $output .= $this->displayFAQ();

            if (Configuration::get('FCTP_ENABLE_TEST_EVENTS') && Configuration::get('FCTP_CONVERSION_IP_LOG') == '') {
                $this->context->controller->warnings[] =
                    '<p>' . $this->l('You have globally enabled the test mode for your CAPI events, Facebook will not track any event outside the "Test Events" tool until you disable the test code events or use the IP restriction tool.') . '<p>';
            }
            // Check if test code has been set, but the feature isn't active.
            $pixel_count = count($pixel_ids) + 1;
            if ($pixel_count > 1) {
                if (!($tmode = Configuration::get('FCTP_ENABLE_TEST_EVENTS'))) {
                    for ($i = 1; $i < ($pixel_count); $i++) {
                        if (Configuration::get('FCTP_CONVERSION_API_TEST_'.$i) != '') {
                            $tmode = true;
                            break;
                        }
                    }
                    if ($tmode) {
                        $this->context->controller->warnings[] = $this->l('You have configured the test codes but you haven\'t enabled the test mode feature.').'<br>'.
                            $this->l('Go to the Conversion API section and scroll to the test & debugging options and enable the test mode');
                    }
                }
            }
            $output .= $this->display(__FILE__, '/views/templates/admin/js-vars.tpl');
            return '<div id="module-body" class="clearfix">' . $output . $this->displayExistingPixels() . $css . '</div>';
        }
    }
    private function getFormFields()
    {
        // Name: Name of the field
        // Def: Default Value
        // Type: Type of data
        // Global: Need to saved in the Global scope?
        $form_fields = array(
            array('name' => 'FCTP_CONV', 'def' => 1, 'type' => 'int', 'global' => 0),
            array('name' => 'FCTP_ADD_TO_CART', 'def' => 1, 'type' => 'int', 'global' => 0),
            array('name' => 'FCTP_SEARCH', 'def' => 1, 'type' => 'int', 'global' => 0),
            array('name' => 'FCTP_CATEGORY', 'def' => 1, 'type' => 'int', 'global' => 0),
            array('name' => 'FCTP_SEARCH_ITEMS', 'def' => 5, 'type' => 'int', 'global' => 0),
            array('name' => 'FCTP_CATEGORY_ITEMS', 'def' => 5, 'type' => 'int', 'global' => 0),
            array('name' => 'FCTP_CATEGORY_TOP_SALES', 'def' => 1, 'type' => 'int', 'global' => 0),
            array('name' => 'FCTP_WISH', 'def' => 1, 'type' => 'int', 'global' => 0),
            array('name' => 'FCTP_REG', 'def' => 1, 'type' => 'int', 'global' => 0),
            array('name' => 'FCTP_START', 'def' => 1, 'type' => 'int', 'global' => 0),
            array('name' => 'FCTP_START_ORD', 'def' => 1, 'type' => 'int', 'global' => 0),
            array('name' => 'FCTP_PIXEL_ID', 'def' => 1, 'type' => 'int', 'global' => 0),
            array('name' => 'FCTP_DYNAMIC_ADS', 'def' => 1, 'type' => 'int', 'global' => 0),
            array('name' => 'FCTP_VIEWED', 'def' => 1, 'type' => 'int', 'global' => 0),
            array('name' => 'FCTP_FORCE_HEADER', 'def' => 0, 'type' => 'int', 'global' => 0),
            array('name' => 'FCP_CUST_ADD_TO_CART', 'def' => '', 'type' => 'text', 'global' => 0),
            array('name' => 'FCP_PRODUCT_CUSTOM_SELECTOR', 'def' => '', 'type' => 'text', 'global' => 0),
            array('name' => 'FCP_CUST_SEARCH', 'def' => '', 'type' => 'text', 'global' => 0),
            array('name' => 'FCP_CUST_SEARCH_P', 'def' => '', 'type' => 'text', 'global' => 0),
            array('name' => 'FCP_CUST_CHECKOUT', 'def' => '', 'type' => 'text', 'global' => 0),
            array('name' => 'FCTP_LIMIT_CONF', 'def' => 0, 'type' => 'int', 'global' => 0),
            array('name' => 'FCTP_INIT_CHECKOUT_MODE', 'def' => 1, 'type' => 'int', 'global' => 0),
            array('name' => 'FCTP_AJAX', 'def' => 1, 'type' => 'int', 'global' => 0),
            array('name' => 'FCTP_AJAX_REG', 'def' => 1, 'type' => 'int', 'global' => 0),
            array('name' => 'FCTP_COOKIE_CONTROL', 'def' => 0, 'type' => 'int', 'global' => 1),
            array('name' => 'FCTP_PURCHASE_SHIPPING_EXCLUDE', 'def' => 0, 'type' => 'int', 'global' => 1),
            array('name' => 'FCTP_ORDER_STATUS_EXCLUDE', 'def' => '', 'type' => 'array', 'global' => 0),
            array('name' => 'FCTP_FORCE_BASIC', 'def' => 0, 'type' => 'int', 'global' => 0),
            array('name' => 'FCTP_REG_VALUE', 'def' => 1, 'type' => 'int', 'global' => 0),
            array('name' => 'FCTP_START_ORD_VALUE', 'def' => 1, 'type' => 'text', 'global' => 0),
            array('name' => 'FCTP_CATEGORY_VALUE', 'def' => 1, 'type' => 'int', 'global' => 0),
            array('name' => 'FCTP_SEARCH_VALUE', 'def' => 1, 'type' => 'int', 'global' => 0),
            array('name' => 'FCTP_WISH_VALUE', 'def' => 1, 'type' => 'int', 'global' => 0),
            array('name' => 'FCTP_START_VALUE', 'def' => 1, 'type' => 'int', 'global' => 0),
            array('name' => 'FCTP_FORCE_REFRESH_AFTER_ORDER', 'def' => 0, 'type' => 'int', 'global' => 0),
            array('name' => 'FCTP_CONVERSION_API', 'def' => 1, 'type' => 'int', 'global' => 0),
            //array('name' => 'FCTP_CONVERSION_API_ACCESS_TOKEN', 'def' => '', 'type' => 'text', 'global' => 0),
            array('name' => 'FCTP_VERIFY_DOMAIN', 'def' => '', 'type' => 'text', 'global' => 0),
            //array('name' => 'FCTP_CONVERSION_API_TEST', 'def' => '', 'type' => 'text', 'global' => 0),
            array('name' => 'FCTP_ADVANCE_MATCHING_OPTIONS', 'def' => 1, 'type' => 'int', 'global' => 0),
            array('name' => 'FCTP_FILL_MICRO_DATA', 'def' => 1, 'type' => 'int', 'global' => 0),
            array('name' => 'FCTP_BLOCK_SCRIPT', 'def' => 0, 'type' => 'int', 'global' => 0),
            array('name' => 'FCTP_COOKIE_NAME', 'def' => '', 'type' => 'text', 'global' => 0),
            array('name' => 'FCTP_COOKIE_VALUE', 'def' => '', 'type' => 'text', 'global' => 0),
            array('name' => 'FCTP_COOKIE_EXTERNAL', 'def' => 0, 'type' => 'int', 'global' => 0),
            array('name' => 'FCTP_COOKIE_RELOAD', 'def' => 1, 'type' => 'int', 'global' => 0),
            array('name' => 'FCTP_COOKIE_BUTTON', 'def' => '', 'type' => 'text', 'global' => 0),
            array('name' => 'FCTP_MICRO_IGNORE_COVER', 'def' => 0, 'type' => 'int', 'global' => 0),
            array('name' => 'FCTP_MICRO_IMG_LIMIT', 'def' => '', 'type' => 'text', 'global' => 0),
            array('name' => 'FCTP_CONVERSION_IP_LOG', 'def' => '', 'type' => 'text', 'global' => 0),
            array('name' => 'FCTP_CONVERSION_PAYLOAD', 'def' => '', 'type' => 'int', 'global' => 0),
            array('name' => 'FCP_CUST_WISHLIST_MODULE', 'def' => '', 'type' => 'int', 'global' => 0),
            array('name' => 'FCTP_ENABLE_TEST_EVENTS', 'def' => '', 'type' => 'int', 'global' => 0),
        );
        $form_fields[] = array('name' => 'FCTP_CONVERSION_LOG', 'def' => '', 'type' => 'text', 'global' => 0);
        $pixel_value = (Tools::getValue('FCTP_PIXEL_ID') != '') ? Tools::getValue('FCTP_PIXEL_ID') : Configuration::get('FCTP_PIXEL_ID');
        $pixels_ids = explode(",", $pixel_value);
        $pix_count = count($pixels_ids);
        if ($pix_count == 0) {
            $pixels_ids = array('');
        }
        $pix_count++;
        for ($i = 1; $i < $pix_count; $i++) {
            $form_fields[] = array('name' => 'FCTP_CONVERSION_API_ACCESS_TOKEN_' . $i, 'def' => '', 'type' => 'text', 'global' => 0);
            $form_fields[] = array('name' => 'FCTP_CONVERSION_API_TEST_' . $i, 'def' => '', 'type' => 'text', 'global' => 0);
        }

        $langs = Language::getLanguages();
        $shops = Shop::getShops();
        foreach ($shops as $shop) {
            $form_fields[] = array('name' => 'FPF_PREFIX_' . $shop['id_shop'], 'def' => '', 'type' => 'text', 'global' => 1);
            $form_fields[] = array('name' => 'FCTP_COMBI_' . $shop['id_shop'], 'def' => 0, 'type' => 'int', 'global' => 1);
            $form_fields[] = array('name' => 'FCTP_COMBI_PREFIX_' . $shop['id_shop'], 'def' => '', 'type' => 'text', 'global' => 1);
            if (self::$feed_v2) {
                $form_fields[] = array('name' => 'FCTP_FEED_' . $shop['id_shop'], 'def' => '', 'type' => 'text', 'global' => 1);
            } else {
                foreach ($langs as $lang) {
                    $form_fields[] = array('name' => 'FPF_' . $shop['id_shop'] . '_' . $lang['id_lang'], 'def' => '', 'type' => 'text', 'global' => 1);
                }
            }
        }
        return $form_fields;
    }
    private function getGoogleProductCategories()
    {
        $gc = new GoogleCategories();
        return $gc->buildGoogleCategories();
    }
    private function getDefaultValuesForPixelTests()
    {
        $sql = 'SELECT id_product, name, price FROM ' . _DB_PREFIX_ . 'product LEFT JOIN ' . _DB_PREFIX_ . 'product_lang USING (id_product) WHERE id_lang = ' . (int)$this->context->language->id . ' AND active = 1';
        return DB::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
    }
    private function getCatalogueIdForTest()
    {
        $languages = Language::getLanguages(true, false, true);
        if (self::$feed_v2) {
            return Configuration::get('FCTP_FEED_' . $this->context->shop->id);
        } else {
            if (count($languages) == 1) {
                return Configuration::get('FPF_' . $this->context->shop->id . '_' . $languages[0]);
            } else {
                for ($i = 0; $i < count($languages); $i++) {
                    $tmp = Configuration::get('FPF_' . $this->context->shop->id . '_' . $languages[$i]);
                    if ((int)$tmp > 0) {
                        return $tmp;
                    }
                }
            }
        }
        $this->context->controller->_warnings[] = $this->l('To send test pixels with Dynamic Ads features fill the catalog IDs fields by getting your catalog ID from Facebook');
        return 0;
    }
    /**
     * Save the form data.
     */
    protected function postProcess()
    {
        // Save the Google Categories Association
        $gc = new GoogleCategories();
        $gc->assignGoogleTaxonomies();
        // Save the form fields
        foreach ($this->form_fields as $field) {
            $key = $field['name'];

            if ($key == 'FCTP_ORDER_STATUS_EXCLUDE') {
                $order_states = OrderState::getOrderStates($this->context->language->id);
                $os = array();
                foreach ($order_states as $order_state) {
                    if ($order_state['logable'] != 1) {
                        if (Tools::getIsset('FCTP_ORDER_STATUS_EXCLUDE_'.$order_state['id_order_state'])) {
                            $os[] = $order_state['id_order_state'];
                        }
                    }
                }
                if (count($os) > 0) {
                    Configuration::updateValue('FCTP_ORDER_STATUS_EXCLUDE', implode(',', $os));
                }
            } elseif ($field['global']) {
                Configuration::updateGlobalValue($key, trim(Tools::getValue($key)));
            } else {
                Configuration::updateValue($key, trim(Tools::getValue($key)));
            }
        }
    }
    public function newpixelJS()
    {
        $this->context->smarty->assign(
            array(
                'old_ps' => version_compare(_PS_VERSION_, '1.6', '<'),
                /*'msg_automatic_value' => $this->l('Automatic Value'),
                'msg_must_be_number' => $this->l('Error: Value must be a number'),
                'msg_enter_id_cms' => $this->l('Enter the ID of the CMS you want to track'),
                'msg_enter_id_track' => $this->l('Enter the ID of the CMS you want to track'),*/
            )
        );
        return $this->display(__FILE__, '/views/templates/admin/add-extra-type.tpl');
    }
    /** List Items Creation **/
    public function displayExistingPixels()
    {
        $this->fields_list = array();
        $this->fields_list['id_facebookpixels'] = array(
            'title' => $this->l('ID'),
            'type' => 'int',
            'search' => false,
            'orderby' => false,
        );
        /*$this->fields_list['id_pixel'] = array(
                'title' => $this->l('Pixel Identifier'),
                'type' => 'int',
                'search' => false,
                'orderby' => false,
            );*/
        $this->fields_list['pixel_active'] = array(
            'title' => $this->l('Active'),
            'type' => 'bool',
            'search' => false,
            'orderby' => false,
            'align' => 'text-center',
            'active' => 'status',
        );
        $this->fields_list['pixel_name'] = array(
            'title' => $this->l('Name'),
            'type' => 'text',
            'search' => false,
            'orderby' => false,
        );
        $this->fields_list['pixel_type'] = array(
            'title' => $this->l('Type'),
            'type' => 'text',
            'search' => false,
            'orderby' => false,
        );
        $this->fields_list['pixel_extras_name'] = array(
            'title' => $this->l('What to track'),
            'type' => 'text',
            'search' => false,
            'orderby' => false,
        );
        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->identifier = 'id_facebookpixels';
        $helper->actions = array('edit', 'delete');
        $helper->show_toolbar = true;
        $helper->imageType = 'jpg';
        $helper->toolbar_btn['new'] = array(
            'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&add' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'),
            'desc' => $this->l('Add new Pixel')
        );
        $helper->title = $this->l('Keypage Views and Searches'); //.' - '.$this->displayName;
        $helper->table = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $content = $this->getListContent($this->context->language->id);
        $helper->listTotal = count($content);
        return $helper->generateList($content, $this->fields_list);
    }
    protected function getListContent($id_lang = null)
    {
        if (is_null($id_lang)) {
            $id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        }
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'facebookpixels`';
        $content = Db::getInstance()->executeS(pSQL($sql));
        $contentsize = count($content);
        for ($i = 0; $i < $contentsize; $i++) {
            $content[$i]['pixel_type'] = $this->extras_type_lang[$content[$i]['pixel_extras_type']];
        }
        return $content;
    }
    /** End List **/
    public function createNewPixel()
    {
        $select_options = array();
        $extra_options = array();
        $types = count($this->type);
        for ($i = 1; $i < $types + 1; $i++) {
            $select_options[] = array('id_option' => $i, 'name' => $this->type[$i]);
        }
        foreach ($this->extras_type as $k => $extra) {
            $extra_options[$k] = array('id_extra_option' => $k, 'extra' => $this->extras_type_lang[$k]);
        }
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Add New Conversion Tracking Pixel'),
                    'icon' => 'icon-plus',
                ),
                'input' => array(
                    array(
                        'label' => $this->l('Pixel Status'),
                        'type' => 'radio',
                        'name' => 'pixel_active',
                        'required' => true,
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'pixel_active_on',
                                'value' => 1,
                                'label' => $this->l('Active')
                            ),
                            array(
                                'id' => 'pixel_active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'label' => $this->l('Pixel Name') . ' (' . $this->l('optional') . ')',
                        'type' => 'text',
                        'name' => 'pixel_name',
                        'placeholder' => $this->l('Write a name that describes your pixel'),
                    ),
                    array(
                        'label' => $this->l('Type of Pixel'),
                        'type' => 'select',
                        'class' => '',
                        'name' => 'pixel_type',
                        'options' => array(
                            'query' => $select_options,
                            'id' => 'id_option',
                            'name' => 'name',
                        )
                    ),
                    /*array(
                        'label' => $this->l('Pixel\'s Value'),
                        'type' => 'text',
                        'name' => 'pixel_value',
                    ),*/
                    array(
                        'label' => $this->l('KeyPage Type'),
                        'type' => 'select',
                        'class' => '',
                        'name' => 'pixel_extras_type',
                        'options' => array(
                            'query' => $extra_options,
                            'id' => 'id_extra_option',
                            'name' => 'extra',
                        )
                    ),
                    array(
                        'label' => $this->l('Key page identifier'),
                        'type' => 'text',
                        'name' => 'pixel_extras',
                        'placeholder' => $this->l('Enter the ID of the Key page you want to track'),
                        'required' => true,
                        'desc' => $this->l('To know the ID go to Product/Category/CMS list and copy the first column parameter "ID" of the desired element, and copy it here.'),
                    ),
                    array(
                        'type' => 'hidden',
                        'name' => 'id_facebookpixels',
                    ),
                    array(
                        'type' => 'hidden',
                        'name' => 'pixel_extras_name',
                    ),
                ),
                'buttons' => array(
                    array(
                        'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                        'title' => $this->l('Back to list'),
                        'icon' => 'process-icon-back'
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submitnewpixel',
                    'id' => 'submitnewpixel'
                )
            )
        );
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->id = (int)Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitnewpixel';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        $fields_values = $this->getPixels(Tools::getValue('id_facebookpixels'));
        if ($fields_values != false) {
            foreach ($fields_values[0] as $key => $value) {
                if ($value != '' || isset($value)) {
                    $helper->fields_value[$key] = $value;
                } else {
                    $helper->fields_value[$key] = Tools::getValue($key);
                }
            }
            $helper->fields_value['pixel_code'] = Tools::getValue('pixel_code');
        } else { // Es un pixel nou o es error
            $helper->fields_value['pixel_active'] = Tools::getValue('pixel_active', 1);
            $helper->fields_value['pixel_name'] = Tools::getValue('pixel_name');
            $helper->fields_value['pixel_value'] = (float)Tools::getValue('pixel_value');
            $helper->fields_value['pixel_extras'] = Tools::getValue('pixel_extras');
            $helper->fields_value['pixel_type'] = Tools::getValue('pixel_type');
            $helper->fields_value['pixel_code'] = Tools::getValue('pixel_code');
            $helper->fields_value['id_pixel'] = Tools::getValue('id_pixel');
            $helper->fields_value['pixel_extras_type'] = Tools::getValue('pixel_extras_type');
            $helper->fields_value['id_facebookpixels'] = Tools::getValue('id_facebookpixels');
            $helper->fields_value['pixel_extras_name'] = Tools::getValue('pixel_extras_name', '');
        }
        $css = $this->display(__FILE__, '/views/templates/admin/special-pixel-styles.tpl');
        return $helper->generateForm(array($fields_form)) . $css;
    }
    private function getBasicForm()
    {
        $langs = Language::getLanguages();
        $shops = Shop::getShops();
        //$fields_value = array();
        $switch_options = array(
            array(
                'id' => 'active_on',
                'value' => 1,
                'label' => $this->l('Enabled')
            ),
            array(
                'id' => 'active_off',
                'value' => 0,
                'label' => $this->l('Disabled')
            )
        );
        $cookie_options = array(
            array(
                'id' => 'active_on',
                'value' => 1,
                'label' => $this->l('PrestaShop')
            ),
            array(
                'id' => 'active_off',
                'value' => 0,
                'label' => $this->l('External')
            )
        );
        $num_items_options = array();
        for ($i = 5; $i <= 10; $i++) {
            $num_items_options[] = array(
                'id_option' => $i,
                'name' => $i
            );
        }
        $checkout_options = array(
            array(
                'id_option' => '1',
                'name' => $this->l('Initial page of the checkout process'),
            ),
            array(
                'id_option' => '2',
                'name' => $this->l('Click on the button that leads to checkout page'),
            )
        );

        $wishlist_modules = array(
            array(
                'id_option' => '0',
                'name' => $this->l('<-- Select your wishlist module -->'),
            ),
            array(
                'id_option' => 'IqitWishlist',
                'name' => $this->l('IqitWishlist'),
            )
        );


        $order_states = OrderState::getOrderStates($this->context->language->id);
        $order_statuses = array();
        //$order_statuses = array(array('id_option' => '0', 'name' => '----' . $this->l('None') . '----'));
        foreach ($order_states as $order_state) {
            $os = array();
            //we filter the status that are not considerd as valid by settings, to avoid the too large lists in the selection
            if ($order_state['logable'] != 1) {
                $os['id_option'] = $order_state['id_order_state'];
                $os['name'] = $order_state['name'];
                $order_statuses[] = $os;
            }
        }

        $select_options = array();
        $extra_options = array();
        foreach ($this->type as $i => $type) {
            $select_options[] = array('id_option' => $i, 'name' => $this->type[$i]);
        }
        foreach ($this->extras_type as $i => $extra) {
            $extra_options[] = array('id_extra_option' => $i, 'extra' => $this->extras_type_lang[$i]);
        }
        $missing_micro = '';
        if (is_array($this->rmd) && count($this->rmd) > 0) {
            $this->context->smarty->assign('missing_micro', $this->rmd);
            $missing_micro = $this->display(__FILE__, '/views/templates/admin/form-missing-microdata.tpl');
        }
        $fields_form = array();
        $fields_form[] = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Facebook Pixel\'s ID'),
                    'icon' => 'icon-cog',
                ),
                'input' => array(
                    array(
                        'label' => '',
                        'type' => 'free',
                        'class' => '',
                        'name' => 'FCTP_FREE',
                        'desc' => '</p><h3 class="modal-title text-info">' . $this->l('Video: How to get the Pixel ID') . '</h3><div class="form-video-wrapper"><div class="form-video"><iframe width="560" height="420" src="https://www.youtube-nocookie.com/embed/KpuiRTUjGdM" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div></div>',
                    ),
                    array(
                        'label' => $this->l('Pixel Identifier'),
                        'type' => 'text',
                        'name' => 'FCTP_PIXEL_ID',
                        'desc' => $this->l('Here you have to put your Facebook\'s Pixel identifier, you can get it anytime from your ') . ' <a href="https://www.facebook.com/ads/manager/pixel/facebook_pixel/" title="' . $this->l('Facebook ads Manager') . '" target="_blank">' . $this->l('Facebook ads Manager') . '</a><br>' .
                            '<strong>' . $this->l('New:') . '</strong> ' . $this->l('Now you can add multiple IDs by separating them with a comma, although Facebook doesn\'t recommend it.'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submit' . $this->name,
                    'id' => 'submit' . $this->name
                ),
            ),
        );

        $pixels_ids = explode(",", Configuration::get('FCTP_PIXEL_ID'));
        if (count($pixels_ids) == 0) {
            $pixels_ids = array('');
        }
        $inputFields = array();

        $inputFields[] = array(
            'label' => $this->l('Enable/Disable the Conversion API'),
            'type' => 'switch',
            'name' => 'FCTP_CONVERSION_API',
            'desc' => $this->l('enable / disable the usage of the Conversion API to send the events'),
            'is_bool' => true,
            'values' => $switch_options
        );

        $pix_id = 1;
        foreach ($pixels_ids as $pixel_id) {
            $inputFields[] = array(
                'label' => '',
                'type' => 'free',
                'class' => '',
                'name' => 'FCTP_FREE',
                'desc' => '<h3 class="modal-title text-info">' . $this->l('Conversion API for Pixel ID:') . $pixel_id . '</h3>',
            );

            $inputFields[] = array(
                'label' => $this->l('Access Token'),
                'type' => 'text',
                'class' => '',
                'name' => 'FCTP_CONVERSION_API_ACCESS_TOKEN_' . $pix_id,
                'desc' => $this->l('When you configure the Conversion API for the first time, Facebook will generate an access token') . '.<br>' .
                    $this->l('Add the access token provided by Facebook') . '.<br>' .
                    $this->l('If the token is not added the conversions won\'t be sent.'),
            );


            $inputFields[] = array(
                'label' => $this->l('Test Code'),
                'type' => 'text',
                'class' => 'input fixed-width-lg mt-18',
                'name' => 'FCTP_CONVERSION_API_TEST_' . $pix_id,
                'desc' => '<strong>' . $this->l('Only for testing purposes') . '</strong><br>' .
                    $this->l('Facebook allows to test the Conversions API before going to production') .
                    sprintf($this->l('In the %s, go to the TEST Events section and click over the Test code for the Conversion API.'), '<a href="https://business.facebook.com/events_manager2" target="_blank">' . $this->l('Events Manager') . '</a>') . '<br>' .
                    $this->l('This will copy the code') . '.<br>' .
                    $this->l('Enter here your Test Event code. This will enable the test mode for the conversion API') . '<br>' .
                    $this->l('Empty this field to disable the test mode for the conversion API'),
            );
            $pix_id++;
        }


        $morefields =  array(
            array(
                'label' => $this->l('Advanced Matching Options'),
                'type' => 'switch',
                'name' => 'FCTP_ADVANCE_MATCHING_OPTIONS',
                'desc' => $this->l('The advanced matching options are useful to be able to properly match the Pixel Events and the Conversion API to make it easier for Facebook to deduplicate the events (prevent event duplications)') . '<br>' .
                    '<strong>' . $this->l('If you enable this setting, make sure you specify it on your Privacy Policy') . '</strong>.',
                'is_bool' => true,
                'values' => $switch_options
            ),
            array(
                'label' => '<h3 class="modal-title text-info">' . $this->l('Domain Verification') . '</h3>',
                'type' => 'free',
                'class' => '',
                'name' => 'FCTP_FREE',
                'desc' => '<br><hr>' . $this->l('In order to use the Aggregated Events Measurement and the Conversion API facebook will ask to validate the domain') . '.<br>' .
                    $this->l('The domain validation can be done by any of this 3 ways:') .
                    '<ul><li>' . $this->l('Upload a file') . '</li>' .
                    '<li>' . $this->l('Add a TXT register on the domain\'s DNS') . '</li>' .
                    '<li><strong>' . $this->l('Adding a Metadata with the validation key') . '</strong></li></ul>' .
                    $this->l('The module allows you to validate the domain by adding the validation key inside a specific a metadata, if you want to validate it this way just copy the content value of the meta-data in the following field') . '.',
            ),
            array(
                'label' => $this->l('Domain verification'),
                'type' => 'text',
                'class' => '',
                'name' => 'FCTP_VERIFY_DOMAIN',
                'desc' => $this->l('If your domain is not verified, Facebook will ask you to pass the verification') . '.<br>' .
                    $this->l('Go to the %s and click on the meta-tag verification, then copy the meta-tag and paste it here.') . '.<br>' .
                    $this->l('Save and go back to Facebook to validate the your domain') . '.',
            )
        );

        $inputFields = array_merge($inputFields, $morefields);

        $inputFields[] = array(
            'label' => '<h3 class="modal-title text-info">' . $this->l('Test & Debugging Options') . '</h3>',
            'type' => 'free',
            'class' => '',
            'name' => 'FCTP_FREE',
            'desc' => '<br><hr>',
        );
        $inputFields[] = array(
            'label' => $this->l('Restrict Tests & debug by IP').' ('.$this->l('Recommended').')',
            'type' => 'textbutton',
            'name' => 'FCTP_CONVERSION_IP_LOG',
            'desc' => '<strong>' . $this->l('Highly recommended') . '</strong>. ' . $this->l('Limit the logging feature to an IP and prevent excessive logs'),
            'validation' => 'isGenericName',
            'size' => 20,
            'button' => array(
                'attributes' => array(
                    'class' => 'btn btn-outline-primary add_ip_button',
                    'onclick' => 'addRemoteAddr();',
                ),
                'label' => $this->l('Add my IP'),
                'icon' => 'plus',
            ),
        );
        $inputFields[] = array(
            'label' => $this->l('Enable test code Events'),
            'type' => 'switch',
            'class' => '',
            'is_bool' => true,
            'values' => $switch_options,
            'name' => 'FCTP_ENABLE_TEST_EVENTS',
            'desc' => $this->l('Enable the settings to track test events. You have to fill the test code as well  before enabling this option.')
        );
        $inputFields[] = array(
            'label' => $this->l('Log API Events'),
            'type' => 'switch',
            'class' => '',
            'is_bool' => true,
            'values' => $switch_options,
            'name' => 'FCTP_CONVERSION_LOG',
            'desc' => $this->l('Enable this setting to generate a log on PrestaShop each time an event has been sent through the API. To access the logs go to Advanced Parameters > Logs')
        );
        $inputFields[] = array(
            'label' => $this->l('Save the Payload in the Log'),
            'type' => 'switch',
            'class' => '',
            'is_bool' => true,
            'values' => $switch_options,
            'name' => 'FCTP_CONVERSION_PAYLOAD',
            'desc' => $this->l('Save to the PrestaShop log the exact payload sent to Facebook with the API Conversion') . '. ' . $this->l('Should only be enabled for debug purposes')
        );
        $fields_form['conversion_api'] = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Conversion API'),
                    'icon' => 'icon-cog',
                ),
                'input' => $inputFields,
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submit' . $this->name,
                    'id' => 'submit' . $this->name
                ),
            ),
        );
        $fields_form[] = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Basic Trackable events'),
                    'icon' => 'icon-facebook',
                ),
                'input' => array(
                    array(
                        'label' => '<h3 class="modal-title text-info">' . $this->l('Dynamic Value Events') . '</h3>',
                        'type' => 'free',
                        'class' => '',
                        'name' => 'FCTP_FREE',
                        'desc' => '<br><hr>' . $this->l('Events that depends on the value of the product/s'),
                    ),
                    array(
                        'label' => 'ViewContent',
                        'type' => 'switch',
                        'name' => 'FCTP_VIEWED',
                        'desc' => '<strong>' . $this->l('Track all Viewed Products') . '</strong><br>' . $this->l('Enable this option to track all products viewed') . '. ' . $this->l('Product listings like category or search are excluded'),
                        'is_bool' => true,
                        'values' => $switch_options
                    ),
                    array(
                        'label' => 'AddToCart',
                        'type' => 'switch',
                        'name' => 'FCTP_ADD_TO_CART',
                        'desc' => '<strong>' . $this->l('Track add to cart') . '<br>' . $this->l('Dynamic Event:') . '</strong> ' . $this->l('See more information at the end of this page') . '<br>' . $this->l('Will trigger every time a user adds an item to their cart') . '.',
                        'is_bool' => true,
                        'values' => $switch_options
                    ),
                    array(
                        'label' => 'Purchase',
                        'type' => 'switch',
                        'name' => 'FCTP_CONV',
                        'desc' => '<strong>' . $this->l('Track Conversions') . '</strong><br>' . $this->l('Enable this option to track all conversions made from your site') . '.',
                        'is_bool' => true,
                        'values' => $switch_options
                    ),
                    array(
                        'label' => '<h3 class="modal-title text-info">' . $this->l('Custom Value Events') . '</h3>',
                        'type' => 'free',
                        'class' => '',
                        'name' => 'FCTP_FREE',
                        'desc' => '<br><hr>' . $this->l('Events that can send a static value each time they are performed'),
                    ),
                    array(
                        'label' => $this->l('Track Searches'),
                        'type' => 'switch',
                        'name' => 'FCTP_SEARCH',
                        'desc' => $this->l('Will trigger when a user performs a search on your site') . '.',
                        'is_bool' => true,
                        'values' => $switch_options
                    ),
                    array(
                        'label' => '',
                        'type' => 'text',
                        'class' => 'input fixed-width-lg mt-18',
                        'prefix' => $this->l('Value:'),
                        'suffix' => $this->l('€'),
                        'name' => 'FCTP_SEARCH' . '_VALUE',
                        'desc' => $this->l('Set a numeric value for this event.') . '<br>' .
                            $this->l('Positive numbers allowed, use points for decimal separator.') . '<br>' .
                            $this->l('Examples:') . ' 0, 1, 1.5, 1.8, 2.5, 3.99, ...',
                    ),
                    array(
                        'label' => 'ViewCategory',
                        'type' => 'switch',
                        'name' => 'FCTP_CATEGORY',
                        'desc' => '<strong>' . $this->l('Track Categories') . '</strong><br>' . $this->l('Enable this option to track all categories viewed') . '.',
                        'is_bool' => true,
                        'values' => $switch_options
                    ),
                    array(
                        'label' => '',
                        'type' => 'text',
                        'class' => 'input fixed-width-lg mt-18',
                        'prefix' => $this->l('Value:'),
                        'suffix' => $this->l('€'),
                        'name' => 'FCTP_CATEGORY' . '_VALUE',
                        'desc' => $this->l('Set a numeric value for this event.') . '<br>' .
                            $this->l('Positive numbers allowed, use points for decimal separator.') . '<br>' .
                            $this->l('Examples:') . ' 0, 1, 1.5, 1.8, 2.5, 3.99, ...',
                    ),
                    array(
                        'label' => $this->l('Start Order'),
                        'type' => 'switch',
                        'name' => 'FCTP_START_ORD',
                        'desc' => $this->l('Will trigger when a user starts the order\'s funnel process') . ' (' . $this->l('When a customer clicks on "Proceed to Checkout"') . ')',
                        'is_bool' => true,
                        'values' => $switch_options
                    ),
                    array(
                        'label' => '',
                        'type' => 'text',
                        'class' => 'input fixed-width-lg mt-18',
                        'prefix' => $this->l('Value:'),
                        'suffix' => $this->l('€'),
                        'name' => 'FCTP_START_ORD' . '_VALUE',
                        'desc' => $this->l('Set a numeric value for this event.') . '<br>' .
                            $this->l('Leave empty to use the current cart value') . '<br>' .
                            $this->l('Positive numbers allowed, use points for decimal separator.') . '<br>' .
                            $this->l('Examples:') . ' 0, 1, 1.5, 1.8, 2.5, 3.99, ...',
                    ),
                    array(
                        'label' => $this->l('Track Registrations'),
                        'type' => 'switch',
                        'name' => 'FCTP_REG',
                        'desc' => $this->l('Will trigger when a user registers to your site') . '.',
                        'is_bool' => true,
                        'values' => $switch_options
                    ),
                    array(
                        'label' => '',
                        'type' => 'text',
                        'class' => 'input fixed-width-lg mt-18',
                        'prefix' => $this->l('Value:'),
                        'suffix' => $this->l('€'),
                        'name' => 'FCTP_REG' . '_VALUE',
                        'desc' => $this->l('Set a numeric value for this event.') . '<br>' .
                            $this->l('Positive numbers allowed, use points for decimal separator.') . '<br>' .
                            $this->l('Examples:') . ' 0, 1, 1.5, 1.8, 2.5, 3.99, ...',
                    ),
                    array(
                        'label' => $this->l('Start Payment'),
                        'type' => 'switch',
                        'name' => 'FCTP_START',
                        'desc' => $this->l('Will trigger when a user starts the order\'s payment process') . ' (' . $this->l('beta feature, it may not work with all payment methods') . ')',
                        'is_bool' => true,
                        'values' => $switch_options
                    ),
                    array(
                        'label' => '',
                        'type' => 'text',
                        'class' => 'input fixed-width-lg mt-18',
                        'prefix' => $this->l('Value:'),
                        'suffix' => $this->l('€'),
                        'name' => 'FCTP_START' . '_VALUE',
                        'desc' => $this->l('Set a numeric value for this event.') . '<br>' .
                            $this->l('Positive numbers allowed, use points for decimal separator.') . '<br>' .
                            $this->l('Examples:') . ' 0, 1, 1.5, 1.8, 2.5, 3.99, ...',
                    ),
                    array(
                        'label' => $this->l('Add to Wishlist'),
                        'type' => 'switch',
                        'name' => 'FCTP_WISH',
                        'desc' => $this->l('Will trigger when a user adds an item to a wishlist') . '.<br/>' . $this->l('It\'s mandatory to have the Prestashop\'s Block Wishlist Module activated for this to work') . $this->l('Wishlist event will trigger only on the custom selector added in the Advanced options'),
                        'is_bool' => true,
                        'values' => $switch_options
                    ),
                    array(
                        'label' => '',
                        'type' => 'text',
                        'class' => 'input fixed-width-lg mt-18',
                        'prefix' => $this->l('Value:'),
                        'suffix' => $this->l('€'),
                        'name' => 'FCTP_WISH_VALUE',
                        'desc' => $this->l('Set a numeric value for this event.') . '<br>' .
                            $this->l('Positive numbers allowed, use points for decimal separator.') . '<br>' .
                            $this->l('Examples:') . ' 0, 1, 1.5, 1.8, 2.5, 3.99, ...',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submit' . $this->name,
                    'id' => 'submit' . $this->name
                ),
            ),
        );
        $fields_form['additional_events'] = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Additional options for events'),
                    'icon' => 'icon-sliders',
                ),
                'input' => array(
                    array(
                        'label' => $this->l('Search Results Items?'),
                        'type' => 'select',
                        'name' => 'FCTP_SEARCH_ITEMS',
                        'desc' => $this->l('Set up the number of items that will be sent to Facebook') . ' (' . $this->l('between 5 and 10') . ')',
                        'options' => array(
                            'query' => $num_items_options,
                            'id' => 'id_option',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'label' => $this->l('Category Results Items?'),
                        'type' => 'select',
                        'name' => 'FCTP_CATEGORY_ITEMS',
                        'desc' => $this->l('Set up the number of items that will be sent to Facebook') . ' (' . $this->l('between 5 and 10') . ')',
                        'options' => array(
                            'query' => $num_items_options,
                            'id' => 'id_option',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'label' => $this->l('Use Category Top sellers') . ' / ' . $this->l('Use Category default listing'),
                        'type' => 'switch',
                        'name' => 'FCTP_CATEGORY_TOP_SALES',
                        'desc' => $this->l('Choose yes if you want to send Facebook the top selling products for dynamic ads') . '<br />' .
                            $this->l('Choose no if you want to send the products ordered by position inside the category'),
                        'is_bool' => true,
                        'values' => $switch_options
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submit' . $this->name,
                    'id' => 'submit' . $this->name
                ),
            ),
        );
        $fields = array();
        foreach ($shops as $shop) {
            // Start with the Dynamic Product Ads options
            if (Shop::isFeatureActive()) {
                $fields[] = array(
                    'label' => '<h3 class="modal-title text-info">' . sprintf($this->l('%s Product Options'), $shop['name']) . '</h3>',
                    'type' => 'free',
                    'class' => '',
                    'name' => 'FCTP_FREE',
                    'desc' => '<br><hr>' . sprintf($this->l('Configure Product and combination options for Shop "%s"'), $shop['name']),
                );
            }
            $fields[] = array(
                'type' => 'text',
                'label' => $this->l('Product identifier Prefix'),
                'name' => 'FPF_PREFIX_' . $shop['id_shop'],
                'size' => 20,
                'desc' => $this->l('If your feed does have a Prefix for IDs just enter it here') . '. ' . $this->l('Otherwise you can leave it blank') . '.'
            );
            $fields[] = array(
                'label' => $this->l('Enable combinations tracking?'),
                'type' => 'switch',
                'name' => 'FCTP_COMBI_' . $shop['id_shop'],
                'is_bool' => true,
                'values' => $switch_options
            );
            $fields[] = array(
                'type' => 'text',
                'label' => $this->l('Combinations Prefix'),
                'name' => 'FCTP_COMBI_PREFIX_' . $shop['id_shop'],
                'size' => 20,
                'desc' => $this->l('If you want to use the combinations tracking they will be added after the product ID, use this prefix to separate the actual ID from the combination ID') . '. ' . $this->l('Otherwise you can leave it blank') . '.',
            );
        }
        $fields_form['product_options'] = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Product & Combination Options'),
                    'icon' => 'icon-sliders',
                ),
                'input' => $fields,
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submit' . $this->name,
                    'id' => 'submit' . $this->name
                ),
            ),
        );
        $fields_form['dynamic_ads'] = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Dynamic Product Ads'),
                    'icon' => 'icon-facebook',
                ),
                'input' => array(
                    array(
                        'label' => $this->l('Enable Dynamic Product Ads?'),
                        'type' => 'switch',
                        'name' => 'FCTP_DYNAMIC_ADS',
                        'desc' => sprintf($this->l('To use product Ads is required to have %s and %s enabled. It\'s also required to have a Product Catalogue and %s to match the products.'), $this->l('Track add to cart'), $this->l('Track Conversions'), '<a href="#fieldset_catalogue_ids_6" class="target-menu">' . $this->l('configure the Catalogue IDs') . '</a>') . '.',
                        'is_bool' => true,
                        'values' => $switch_options
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submit' . $this->name,
                    'id' => 'submit' . $this->name
                ),
            ),
        );
        // Start with the Dynamic Product Ads options
        $fields = array();
        foreach ($shops as $shop) {
            if (Shop::isFeatureActive()) {
                $fields[] = array(
                    'label' => '<h3 class="modal-title text-info">' . sprintf($this->l('%s Catalogue IDs'), $shop['name']) . '</h3>',
                    'type' => 'free',
                    'class' => '',
                    'name' => 'FCTP_FREE',
                    'desc' => '<br><hr>' . sprintf($this->l('Configure Product and combination options for Shop "%s"'), $shop['name']),
                );
            }
            if (self::$feed_v2) {
                $fields[] = array(
                    'type' => 'text',
                    'label' => sprintf($this->l('%s\'s Catalogue ID'), $shop['name']),
                    'name' => 'FCTP_FEED_' . $shop['id_shop'],
                    'size' => 20,
                    'placeholder' => $this->l('Facebook\'s catalogue identifier'),
                    'desc' => $this->l('Enter the ID of the Product Catalogue from Facebook in order to link your pixel data to a catalogue.') .
                        '<br>' . $this->l('To get your catalogue ID go to Facebook Ads Manager > Catalogues') . '.' . $this->l('Then under the catalogue name you will see a large number, that is the ID, copy the ID and paste it here'),
                );
            } else {
                foreach ($langs as $lang) {
                    //$fields_value[] = 'FPF_'.$shop['id_shop'].'_'.$lang['id_lang'];
                    // Init Fields form array
                    $fields[] = array(
                        'type' => 'text',
                        'label' => $lang['name'] . ' ' . $this->l('Catalogue ID'),
                        'name' => 'FPF_' . $shop['id_shop'] . '_' . $lang['id_lang'],
                        'size' => 20,
                        'placeholder' => $this->l('Facebook\'s feed identifier'),
                        'desc' => $this->l('Enter the ID of the Product Catalogue from Facebook in order to link your pixel data to a catalogue.') .
                            '<br>' . $this->l('To get your catalogue ID go to Facebook Ads Manager > Catalogues') . '.' .
                            $this->l('Then under the catalogue name you will see a large number, that is the ID, copy the ID and paste it here') . '<br>' .
                            $this->l('If your shop use a multi-language Feed, just fill all the languages with the same ID'),
                    );
                }
            }
        }
        // Generate a form for each Shop
        $fields_form['catalogue_ids']['form'] = array(
            'legend' => array(
                'title' => '<span class="shop_name">' . $this->l('Catalogue ID Association'),
                'icon' => 'icon-link',
            ),
            'input' => $fields,
            'submit' => array(
                'title' => $this->l('Save Configuration'),
                'name' => 'submit' . $this->name,
                'id' => 'submit' . $this->name,
                'class' => 'button'
            )
        );
        $random_product_url = $this->getRandomProductURL($this->context->shop->id, true);
        $fields_form['micro_data'] = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Micro Data'),
                    'icon' => 'icon-code',
                ),
                'input' => array(
                    array(
                        'label' => $this->l('Fill in the missing microdata?'),
                        'type' => 'switch',
                        'name' => 'FCTP_FILL_MICRO_DATA',
                        'desc' => $this->l('If this option is active, the module will fill in the missing microdata that has been detected.') . '</p>' .
                            '<p class="help-block">' .
                            $this->l('Disable this setting if you don\'t want the module to insert the missing microdata.') .
                            '</p>',
                        'is_bool' => true,
                        'values' => $switch_options
                    ),
                    array(
                        'label' => $this->l('Current Missing Micro Data detected:'),
                        'type' => 'free',
                        'name' => 'FCTP_FREE',
                        'desc' => '</p>' . $missing_micro,
                    ),
                    array(
                        'label' => $this->l('Review Micro Data?'),
                        'type' => 'switch',
                        'name' => 'FCTP_CHECK_MICRO_DATA',
                        'desc' => $this->l('Activate this option and save to force the module to check and fix the theme\'s microdata') . '</p>' .
                            '<p class="help-block"><strong>' . $this->l('This setting won\'t keep active, just activate it once to check your theme. After reviewing it the missing data will be displayed below') .
                            '</strong></p>' .
                            '<p class="help-block">' .
                            $this->l('This process is automatically done on module installation and you will only need to activate it if you have changed your theme or the theme structure.') .
                            '</p><hr>' .
                            '<p class="help-block">' .
                            $this->l('Micro Data is used by Facebook to generate a product catalogue from the pixel evens. Pixel Plus reviews the current theme and fix all the missing micro data.') . '. ' .
                            $this->l('This have several benefits besides being able to generate the Product Catalogues. Correct micro data will improve the page\'s SEO and the quality of the shared content.') .
                            '</p>',
                        'is_bool' => true,
                        'values' => $switch_options
                    ),
                    array(
                        'label' => '<h3 class="modal-title text-info">' . $this->l('Microdata manipulation options') . '</h3>',
                        'type' => 'free',
                        'class' => '',
                        'name' => 'FCTP_FREE',
                        'desc' => '<br><hr>' . $this->l('Alter the microdata generation to fit your needs'),
                    ),
                    array(
                        'label' => $this->l('Ignore cover?'),
                        'type' => 'switch',
                        'name' => 'FCTP_MICRO_IGNORE_COVER',
                        'desc' => $this->l('Activate this option to ignore the cover image and use the fist one as the cover') . ' (' . $this->l('use the product images order') . ')',
                        'is_bool' => true,
                        'values' => $switch_options
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Images limit'),
                        'name' => 'FCTP_MICRO_IMG_LIMIT',
                        'size' => 20,
                        'placeholder' => $this->l('Enter the number of images to export'),
                        'desc' => $this->l('Enter a value in this field if you need to limit the number of images you want to send to Facebook.'),
                    ),
                    array(
                        'label' => $this->l('Microdata Debug tool'),
                        'type' => 'free',
                        'name' => 'FCTP_FREE',
                        'desc' => '</p><p>' . $this->l('Facebook has a tool to debug the microdata, this tool will help you to find if the microdata from your products comply with Facebook requirements') . '</p>' .
                            '<p>' . $this->l('To use the tool you first need a product URL, like this one:') . ' <a class="badge link_copy" href="' . $random_product_url . '">' . $this->l('Click to copy the product URL') . '</a></p>' .
                            '<p>' . sprintf($this->l('Then, open the %s'), '<a href="https://business.facebook.com/ads/microdata/debug?url=' . urlencode($random_product_url) . '" target="_blank">' . $this->l('Microdata Debug Tool') . '</a>') .
                            '<p>' . $this->l('And run the test') . '</p>' .
                            '<p>' . $this->l('If you don\'t see any error message (in red), then your products microdata is ready and soon the pixel will be elegible as a product source') . '</p>',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submit' . $this->name,
                    'id' => 'submit' . $this->name
                ),
            ),
        );
        $fields_form['gdpr'] = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('GDPR & Cookies consent'),
                    'icon' => 'icon-lock',
                ),
                'input' => array(
                    array(
                        'label' => $this->l('Block the script?'),
                        'type' => 'switch',
                        'name' => 'FCTP_BLOCK_SCRIPT',
                        'desc' => $this->l('Block the Pixel Events script if the cookies are not allowed') . '<br/>' .
                            $this->l('Enabling this option will pause the Facebook Pixel Events until a certain cookie is found') . '. ' . $this->l(' You can configure the options in the following fields.') . '<br />',
                        'is_bool' => true,
                        'values' => $switch_options,
                    ),
                    array(
                        'form_group_class' => 'fctp_cookies',
                        'type' => 'text',
                        'label' => $this->l('Add the cookie name to look for'),
                        'name' => 'FCTP_COOKIE_NAME',
                        'size' => 20,
                        'desc' => $this->l('Enter the exact name of the cookie that will be set once the Cookie consent has been accepted'),
                    ),
                    array(
                        'form_group_class' => 'fctp_cookies',
                        'type' => 'text',
                        'label' => $this->l('Specific Cookie Value?'),
                        'name' => 'FCTP_COOKIE_VALUE',
                        'size' => 5,
                        'class' => 'fixed-width-xl',
                        'desc' => $this->l('Leave it blank if you don\'t need to look for a specific value inside the cookie') . '<br>' .
                            $this->l('Otherwise, enter the value to search inside the cookie'),
                    ),
                    array(
                        'form_group_class' => 'fctp_cookies',
                        'label' => $this->l('Is an External Cookie?'),
                        'type' => 'switch',
                        'name' => 'FCTP_COOKIE_EXTERNAL',
                        'desc' => $this->l('Enable this setting if you use an external service to handle the cookies') . '<br>' .
                            $this->l('Disable this setting if the confirmation cookie is set within the PrestaShop cookie'),
                        'is_bool' => true,
                        'values' => $cookie_options,
                    ),
                    array(
                        'form_group_class' => 'fctp_cookies',
                        'label' => $this->l('Page reloads after the consent?'),
                        'type' => 'switch',
                        'name' => 'FCTP_COOKIE_RELOAD',
                        'desc' => $this->l('Enable this setting if you use an external service to handle the cookies') . '<br>' .
                            $this->l('Disable this setting if the confirmation cookie is set within the PrestaShop cookie'),
                        'is_bool' => true,
                        'values' => $cookie_options,
                    ),
                    array(
                        'form_group_class' => 'fctp_cookies fctp_cookie_reload_inverted',
                        'label' => $this->l('Selector for the Cookies Button'),
                        'type' => 'text',
                        'name' => 'FCTP_COOKIE_BUTTON',
                        'desc' => $this->l('Only if the page does not reload') . '<br>' .
                            $this->l('Enter the unique selector for the consent button') . '<br>' .
                            $this->l('If your page does not reload, the module will need to know which button is pressed to accept or decline the cookies to dynamically check if the cookies have been accepted'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submit' . $this->name,
                    'id' => 'submit' . $this->name
                ),
            ),
        );

        $fields_form['advanced_options'] = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Advanced Options'),
                    'icon' => 'icon-warning',
                ),
                'input' => array(
                    array(
                        'label' => '<h3 class="modal-title text-info">' . $this->l('Display Options') . '</h3>',
                        'type' => 'free',
                        'class' => '',
                        'name' => 'FCTP_FREE',
                        'desc' => '<br><hr>',
                    ),
                    array(
                        'label' => $this->l('Force Pixels on Header'),
                        'type' => 'switch',
                        'name' => 'FCTP_FORCE_HEADER',
                        'desc' => $this->l('Disabled by default') . '<br/>' .
                            $this->l('The module tries to output the pixels on the footer of the page, this way the JS doesn\'t block the page render') . ', ' . $this->l(' this way the customer feels the page loading faster and you will also improve your page\'s SEO') . '<br />' .
                            $this->l('Some themes and customized pages do not have the footer hook, if it\'s your case enable this option') . '.',
                        'is_bool' => true,
                        'values' => $switch_options,
                    ),
                    array(
                        'label' => '<h3 class="modal-title text-info">' . $this->l('Custom add to cart') . '</h3>',
                        'type' => 'free',
                        'class' => '',
                        'name' => 'FCTP_FREE',
                        'desc' => '<br><hr>',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Add To Cart custom selector'),
                        'name' => 'FCP_CUST_ADD_TO_CART',
                        'size' => 20,
                        'desc' => $this->l('If your theme has a customized add to cart button use this box to enter the jQuery selector/s'),
                    ),
                    array(
                        'label' => '<h3 class="modal-title text-info">' . $this->l('Product customization') . '</h3>',
                        'type' => 'free',
                        'class' => '',
                        'name' => 'FCTP_FREE',
                        'desc' => '<br><hr>',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Start customization selector'),
                        'name' => 'FCP_PRODUCT_CUSTOM_SELECTOR',
                        'size' => 5,
                        'class' => 'fixed-width-xl',
                        'desc' => $this->l(' Set up this option if your users have to click a button prior to start the customization of a product. Use a CSS selector that matches the button to click'),
                    ),
                    array(
                        'label' => '<h3 class="modal-title text-info">' . $this->l('Custom Checkout Page') . '</h3>',
                        'type' => 'free',
                        'class' => '',
                        'name' => 'FCTP_FREE',
                        'desc' => '<br><hr>',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Module name'),
                        'name' => 'FCP_CUST_CHECKOUT',
                        'size' => 5,
                        'class' => 'fixed-width-xl',
                        'desc' => $this->l('Specify module name if you are using any custom module to trigger checkout events'),
                    ),
                    array(
                        'label' => '<h3 class="modal-title text-info">' . $this->l('Theme Supported Wishlist') . '</h3>',
                        'type' => 'free',
                        'class' => '',
                        'name' => 'FCTP_FREE',
                        'desc' => '<br><hr>',
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Wishlist module'),
                        'options' => array(
                            'query' => $wishlist_modules,
                            'id' => 'id_option',
                            'name' => 'name',
                        ),
                        'name' => 'FCP_CUST_WISHLIST_MODULE',
                        'class' => 'fixed-width-xl',
                        'desc' => $this->l('Select your installed wishlist modules.'),
                    ),
                    array(
                        'label' => '<h3 class="modal-title text-info">' . $this->l('Custom Search') . '</h3>',
                        'type' => 'free',
                        'class' => '',
                        'name' => 'FCTP_FREE',
                        'desc' => '<br><hr>',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Search controller'),
                        'name' => 'FCP_CUST_SEARCH',
                        'size' => 5,
                        'class' => 'fixed-width-xl',
                        'desc' => sprintf($this->l('If your theme has a customized search controller or it uses a module to perform the searches enter here the module\'s controller name. Usually visible when you perform a search in the URL after the keyword %s'), 'controller') .
                            '<br>' . $this->l('Leave it blank to use the default controller'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Search query parameter'),
                        'name' => 'FCP_CUST_SEARCH_P',
                        'size' => 5,
                        'class' => 'fixed-width-xl',
                        'desc' => $this->l('Usually identificable on the URL when you perform a search, the most common values for it are "s", "q", "term" (without the quotes)'),
                    ),
                    array(
                        'label' => '<h3 class="modal-title text-info">' . $this->l('Conversion options') . '</h3>',
                        'type' => 'free',
                        'class' => '',
                        'name' => 'FCTP_FREE',
                        'desc' => '<br><hr>',
                    ),
                    array(
                        'label' => $this->l('Force Basic Mode'),
                        'type' => 'switch',
                        'name' => 'FCTP_FORCE_BASIC',
                        'desc' => $this->l('Enable this setting to rely only on order confirmation page') . '. ' . $this->l('Enable this setting if you are getting duplicate conversions due to any cache system.'),
                        'is_bool' => true,
                        'values' => $switch_options,
                    ),
                    array(
                        'label' => $this->l('Use Ajax to confirm the conversion is sent'),
                        'type' => 'switch',
                        'name' => 'FCTP_AJAX',
                        'desc' => $this->l('Disable it to work directly with database'),
                        'is_bool' => true,
                        'values' => $switch_options,
                    ),
                    array(
                        'label' => $this->l('Use Cookies to prevent conversion duplicates'),
                        'type' => 'switch',
                        'name' => 'FCTP_COOKIE_CONTROL',
                        'desc' => $this->l('Activate this setting if you are receiving duplicated conversions due to a Cache Module or 3rd party Cache system like Cloudflare'),
                        'is_bool' => true,
                        'values' => $switch_options,
                    ),
                    array(
                        'label' => $this->l('Reload after order?'),
                        'type' => 'switch',
                        'name' => 'FCTP_FORCE_REFRESH_AFTER_ORDER',
                        'desc' => $this->l('In some minor payment modules the confirmation is displayed before the order is validated.') . '<br>' .
                            $this->l('Activate this setting to force a fast one time reload and the pixel will be tracked'),
                        'is_bool' => true,
                        'values' => $switch_options,
                    ),
                    array(
                        'label' => $this->l('Exclude shipping in the order amount'),
                        'type' => 'switch',
                        'name' => 'FCTP_PURCHASE_SHIPPING_EXCLUDE',
                        'desc' => $this->l('Enable to exclude the shipping price to the Purchase event.'),
                        'is_bool' => true,
                        'values' => $switch_options,
                    ),
                    array(
                        'type' => 'checkbox',
                        'label' => $this->l('Order states to exclude'),
                        'desc' => $this->l('Select the order states to exclude from the Purchase event.'),
                        'name' => 'FCTP_ORDER_STATUS_EXCLUDE',
                        'form_group_class' => 'os_checkbox',
                        'values' => array(
                            'query' => $order_statuses,
                            'id' => 'id_option',
                            'name' => 'name',
                        ),
                        'class' => 'chosen-container',
                    ),
                    array(
                        'label' => '<h3 class="modal-title text-info">' . $this->l('Registration Options') . '</h3>',
                        'type' => 'free',
                        'class' => '',
                        'name' => 'FCTP_FREE',
                        'desc' => '<br><hr>',
                    ),
                    array(
                        'label' => $this->l('Use Ajax to confirm Customer Registrations'),
                        'type' => 'switch',
                        'name' => 'FCTP_AJAX_REG',
                        'desc' => $this->l('Recommended'),
                        'is_bool' => true,
                        'values' => $switch_options,
                    ),
                    array(
                        'label' => '<h3 class="modal-title text-info">' . $this->l('Initiate Checkout detection') . '</h3>',
                        'type' => 'free',
                        'class' => '',
                        'name' => 'FCTP_FREE',
                        'desc' => '<br><hr>',
                    ),
                    array(
                        'label' => $this->l('Initiate Checkout Mode'),
                        'type' => 'select',
                        'name' => 'FCTP_INIT_CHECKOUT_MODE',
                        'desc' => sprintf($this->l('Choose what will trigger the %s event'), 'InitiateCheckout'),
                        'options' => array(
                            'query' => $checkout_options,
                            'id' => 'id_option',
                            'name' => 'name',
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submit' . $this->name,
                    'id' => 'submit' . $this->name
                ),
            ),
        );
        // Retrocompatibility for Switch in Prestashop 1.5
        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            foreach ($fields_form as &$form) {
                foreach ($form['form']['input'] as &$input) {
                    if ($input['type'] == 'switch') {
                        $input['type'] = 'radio';
                    }
                }
            }
        }
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit' . $this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $form_fields = $this->getConfigFormValues();
        $form_fields['FCTP_FREE'] = '';
        $helper->tpl_vars = array(
            'fields_value' => $form_fields, /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );
        // Generate the FORM
        return $helper->generateForm($fields_form);
    }
    public function getConfigFormValues()
    {
        $form_values = array();
        foreach ($this->form_fields as $field) {
            if ($field['name'] == 'FCTP_ORDER_STATUS_EXCLUDE') {
                $order_states = OrderState::getOrderStates($this->context->language->id);
                $disabled_os = explode(',', Configuration::get('FCTP_ORDER_STATUS_EXCLUDE'));
                foreach ($order_states as $order_state) {
                    if ($order_state['logable'] != 1) {
                        $id = $order_state['id_order_state'];
                        $form_values['FCTP_ORDER_STATUS_EXCLUDE_'.$id] = (in_array($id, $disabled_os)) ? 1 : 0;
                    }
                }
            } else {
                $form_values[$field['name']] = Configuration::get($field['name']);
            }
        }
        $form_values['FCTP_FREE'] = '';
        $form_values['FCTP_CHECK_MICRO_DATA'] = 0;
        return $form_values;
    }
    public function validatenewpixelform()
    {
        $res = array();
        $res['ok'] = true;
        $res['output'] = '';
        // Validation and Cast
        $this->pixelparams['pixel_active'] = (int)Tools::getValue('pixel_active');
        // Pixel name validation
        $this->pixelparams['pixel_name'] = pSQL(Tools::getValue('pixel_name'));
        $this->pixelparams['pixel_type'] = 1; //(int)Tools::getValue('pixel_type');
        $this->pixelparams['pixel_extras'] = pSQL(Tools::getValue('pixel_extras'));
        $this->pixelparams['pixel_extras_type'] = pSQL(Tools::getValue('pixel_extras_type'));
        // Validate Keypage
        if (Tools::getValue('pixel_type') == 1) {
            $keypageexists = $this->checkKeypageExists();
            if ($keypageexists == false) {
                $res['output'] .= $this->displayError($this->l('Error: Please enter a valid KeyPage ID, you can find the ID of your desired page in the product/category/cms list'));
                $res['ok'] = false;
            } else {
                if ($keypageexists[0]['name'] != '' && $keypageexists[0]['name'] != 1) {
                    $this->pixelparams['pixel_extras_name'] = pSQL($keypageexists[0]['name']);
                }
            }
        }
        if ($res['ok'] == true) {
            $this->updatepixel(Tools::getValue('id_facebookpixels'));
            if (Tools::getValue('id_facebookpixels') != '') {
                $res['output'] .= $this->displayConfirmation($this->l('Pixel Updated'));
            } else {
                $res['output'] .= $this->displayConfirmation($this->l('New Pixel Created'));
            }
        }
        return $res;
    }
    public function updatepixel($id = 0)
    {
        if ($id == 0) {
            Db::getInstance()->insert('facebookpixels', $this->pixelparams);
        } else {
            Db::getInstance()->update('facebookpixels', $this->pixelparams, 'id_facebookpixels = ' . (int)$id);
        }
    }
    public function deletepixel()
    {
        $id = (int)Tools::getValue('id_facebookpixels');
        if (Db::getInstance()->delete('facebookpixels', 'id_facebookpixels = ' . (int)$id)) {
            return $this->displayConfirmation($this->l('Pixel Deleted'));
        } else {
            return $this->displayWarning($this->l('There was an errror deleting the pixel it may be already deleted.'));
        }
    }
    public function statuspixel()
    {
        $id = Tools::getValue('id_facebookpixels');
        $sql = 'SELECT pixel_active FROM ' . _DB_PREFIX_ . 'facebookpixels WHERE id_facebookpixels = ' . (int)$id;
        if ($results = Db::getInstance()->executeS(pSQL($sql))) {
            $results = (bool)$results[0]['pixel_active'];
            $results = !$results;
            $results = Db::getInstance()->update('facebookpixels', array('pixel_active' => $results), 'id_facebookpixels = ' . (int)$id);
            return $this->displayConfirmation($this->l('Pixel Status Updated'));
        }
    }
    public function getPixels($id = 0)
    {
        if ($id != 0 && $id != '') {
            $sql = 'SELECT * from `' . _DB_PREFIX_ . 'facebookpixels`';
            if ($id != 0 && $id != '') {
                $sql .= ' WHERE id_facebookpixels = ' . (int)$id;
            }
            if ($results = Db::getInstance()->ExecuteS(pSQL($sql))) {
                return $results;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    private function prepareKeypages()
    {
        $curl = $_SERVER['REQUEST_URI'];
        $curl = explode('?', $_SERVER['REQUEST_URI'], 2);
        preg_match('/^[^\d]*(\d+)/', $curl[0], $cid);
        foreach ($cid as $value) {
            if ($value != '' && isset($value)) {
                $value = (int)($value);
                if (is_int($value)) {
                    $this->context->smarty->assign(array('product_id' => $value));
                }
            }
        }
        if (Tools::getValue('search_query')) {
            $this->context->smarty->assign(array('search_query' => Tools::getValue('search_query')));
        }
        $this->context->smarty->assign(array('extras_types' => $this->extras_type));
    }
    private function checkKeypageExists()
    {
        $sql = '';
        $results = false;
        $lang_id = (int)$this->context->language->id;
        $keypage = (int)Tools::getValue('pixel_extras');
        $type = Tools::getValue('pixel_extras_type');
        $type = $this->extras_type[$type];
        if (Shop::isFeatureActive()) {
            $id_shop = Shop::getContextShopID(true);
            // Check if we will need the id_shop_group
            $sql = 'SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'product_lang` LIKE "id_shop_group"';
            if ($results = Db::getInstance()->ExecuteS($sql)) {
                $id_shop_group = Shop::getContextShopGroupID(true);
            } else {
                $id_shop_group = '';
            }
            $shop = ' AND id_shop = ' . (int)$id_shop . ($id_shop_group != '' ? ' AND id_shop_group = ' . (int)$id_shop_group : '');
        } else {
            $shop = '';
        }
        //
        switch ($type) {
            case 'cms':
                $sql = 'SELECT meta_title AS name FROM `' . _DB_PREFIX_ . 'cms` LEFT JOIN `' . _DB_PREFIX_ . 'cms_lang` USING (id_cms) WHERE id_cms = ' . (int)$keypage . ' AND id_lang = ' . (int)$lang_id . $shop;
                break;
            case 'product':
                $sql = 'SELECT name FROM `' . _DB_PREFIX_ . 'product_lang` WHERE id_product = ' . (int)$keypage . ' AND id_lang = ' . (int)$lang_id . $shop;
                break;
            case 'category':
                $sql = 'SELECT name FROM ' . _DB_PREFIX_ . 'category LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` USING (id_category) WHERE id_category = ' . (int)$keypage . ' AND id_lang = ' . (int)$lang_id . $shop;
                break;
            case 'index':
            case 'contact':
            case 'search':
                return 1;
        }
        if ($results = Db::getInstance()->ExecuteS(pSQL($sql))) {
            return $results;
        } else {
            return false;
        }
    }
    private function prepareCustomer()
    {
        if (Configuration::get('pixel_account_on') == $this->context->cookie->id_customer) {
            $this->context->smarty->assign(array('registeron' => 1));
            return true;
        }
        return false;
    }
    public function hookPaymentReturn($params)
    {
        //return $this->hookDisplayOrderConfirmation($params);
    }
    public function hookOrderConfirmation($params)
    {
        return $this->hookDisplayOrderConfirmation($params);
    }

    public function hookActionFrontControllerAfterInit()
    {
        if (trim(Configuration::get('FCP_CUST_WISHLIST_MODULE')) != '' && Configuration::get('FCTP_WISH') == 1) {
            $module_name = Tools::getValue('module');
            switch (Tools::strtolower($module_name)) {
                case 'iqitwishlist':
                    $controller_name = Tools::getValue('controller');
                    $process = Tools::getValue('process');
                    if ($process == 'add' && $controller_name == 'actions') {
                        $idProduct = (int)Tools::getValue('idProduct');
                        $idProductAttribute = (int)Tools::getValue('idProductAttribute');
                        $fb_pixel_wishlist_event_id =  Tools::getValue('fb_pixel_wishlist_event_id');
                        if ($this->api !== false) {
                            $this->api->wishlistEventTrigger($idProduct, $idProductAttribute, $fb_pixel_wishlist_event_id);
                        }
                    }
                    break;
                default:
                    //nothing to do
                    break;
            }
            $process =  Tools::getValue('process');
        }
    }
    private function addConversionPixel()
    {
        // Check if it's an ajax request
        if (!$this->isAjaxRequest()) {
            // Order Successfull
            // from the previous hook actionvalidate order we will get the order id of the ast one from user
            // IF VALUE PRESENT FOR $this->context->cookie->FCP_ORDER_CONVERSION OR $this->context->cookie->fb_event_purchase_page refers the the order is not sent yet via pixel to the fb
            $pending_orders = Tools::jsonDecode(Configuration::get('FCP_ORDER_CONVERSION'), true);
            $conversion = $this->customerHasPendingOrder($pending_orders);
            if ($conversion !== false) {
                // What when is a guest order?
                $ordervars = $this->getOrdervars($conversion['id_order'], $conversion);
                if (Configuration::get('FCTP_AJAX')) {
                    $ordervars['aurl'] = $this->context->link->getModuleLink(
                        'facebookconversiontrackingplus',
                        'AjaxConversion'
                    );
                } else {
                    $this->clearPendingOrder($pending_orders, $conversion['id_customer']);
                }
                if ($conversion['id_customer'] == 0) {
                    $ordervars['id_customer'] = 0;
                }
                $this->getProductsCartSmarty();
                $this->smarty->assign(
                    array(
                        'ordervars' => $ordervars,
                        'fctp_cookie_control' => Configuration::get('FCTP_COOKIE_CONTROL'),
                        'purchase_token' => Tools::encrypt('Conversion' . $ordervars['id_customer'] . ':' . $ordervars['id_order']),
                        'fb_event_purchase_page' => $conversion['event_id']
                    )
                );
                return $this->display(__FILE__, '/views/templates/hook/purchase.tpl');
            }
        }
    }
    private function getOrderVars($id_order)
    {
        $order = new Order((int)$id_order);
        if ($order !== false) {
            $prefix = Configuration::getGlobalValue('FPF_PREFIX_' . $this->context->shop->id) ? Configuration::getGlobalValue('FPF_PREFIX_' . $this->context->shop->id) : '';
            $combi = (bool)Configuration::getGlobalValue('FCTP_COMBI_' . $this->context->shop->id);
            if ($combi !== false) {
                $combi = Configuration::getGlobalValue('FCTP_COMBI_PREFIX_' . $this->context->shop->id);
            }
            $product_quantity = 0;
            $product_list = array();
            //$prefix = Configuration::get('FPF_PREFIX');
            // Get the total numbers of products purchased
            $products = $order->getProducts();
            foreach ($products as $product) {
                $product_quantity += $product['product_quantity'];
                for ($i = 0; $i < $product['product_quantity']; $i++) {
                    if (isset($product['product_id'])) {
                        $id_product = $product['product_id'];
                        $id_product_attribute = $product['product_attribute_id'];
                    } elseif (isset($product['id_product'])) {
                        $id_product = $product['id_product'];
                        $id_product_attribute = $product['id_product_attribute'];
                    }
                    if (isset($id_product)) {
                        if ($combi !== false && $id_product_attribute > 0) {
                            $product_list[] = $prefix . $id_product . $combi . $id_product_attribute;
                        } else {
                            $product_list[] = $prefix . $id_product;
                        }
                    }
                }
            }
            $product_list = array_unique($product_list);

            $totalordervalue = $order->total_paid;
            if (Configuration::getGlobalValue('FCTP_PURCHASE_SHIPPING_EXCLUDE')) {
                //include shipping price
                $totalordervalue = $order->total_products_wt;
            }

            //var_dump($contents);
            return array(
                'ordervalue' => $totalordervalue,
                'currency' => $this->context->currency->iso_code,
                'product_quantity' => $product_quantity,
                'product_list' => Tools::jsonEncode($product_list),
                'id_order' => (int)$order->id,
                'id_customer' => (int)$order->id_customer,
            );
        }
    }
    private function customerHasPendingOrder($orders_list, $id_customer = 0)
    {
        //$this->context->cookie->id_guest = 0 after order created for guest users on version 1.7
        //$this->context->cookie->id_guest = 1 after order created for guest users on version 1.6
        //So we track guest account only on 1.6 version where 1.7 will be treated as customer account
        if ($id_customer == 0) {
            $id_customer = (int)$this->context->cookie->id_customer;
        }
        //var_dump($id_customer);
        if (!$this->is_17 && $this->context->cookie->is_guest == 1) {
            // It's a guest order on 1.6, return true to send the conversion
            if (isset($orders_list[0]) && is_array($orders_list)) {
                $last_order = end($orders_list);
                return array(
                    'id_customer' => 0,
                    'id_order' => $last_order[0],
                    'event_id' => $last_order[3],
                );
            }
            return false;
        }
        if (is_array($orders_list) && count($orders_list) > 0) {
            foreach ($orders_list as $idc => $details) {
                if ($id_customer == $idc) {
                    return array(
                        'id_customer' => $id_customer,
                        'id_order' => $details[0],
                        'event_id' => $details[3],
                    );
                }
            }
        }
        return false;
    }
    private function clearPendingOrder($orders_list, $id_customer)
    {
        if (isset($orders_list[$id_customer])) {
            unset($orders_list[$id_customer]);
            Configuration::updateValue('FCP_ORDER_CONVERSION', Tools::jsonEncode($orders_list));
            //$this->context->cookie->FCP_ORDER_CONVERSION = Tools::jsonEncode($orders_list);
            return true;
        }
        return false;
    }
    public function getCategoryTopSales($id_category)
    {

        $ret = array();
        $product_prefix = Configuration::get('FPF_PREFIX_' . (int)Context::getContext()->shop->id);
        $combi = (bool)Configuration::getGlobalValue('FCTP_COMBI_' . (int)Context::getContext()->shop->id);
        if ($combi !== false) {
            $combi = Configuration::getGlobalValue('FCTP_COMBI_PREFIX_' . (int)Context::getContext()->shop->id);
        }
        $sql = 'SELECT product_id, COUNT(product_id) AS sales FROM `' . _DB_PREFIX_ . 'order_detail` WHERE product_id IN (SELECT id_product FROM `' . _DB_PREFIX_ . 'category_product` LEFT JOIN ' . _DB_PREFIX_ . 'product USING (id_product) WHERE id_category = ' . (int)$id_category . ' AND active = 1) GROUP BY product_id ORDER BY sales DESC LIMIT ' . (int)Configuration::get('FCTP_CATEGORY_ITEMS');
        $results = DB::getInstance()->executeS(pSQL($sql));
        if ($results !== false) {
            //echo (count($results));
            foreach ($results as $result) {
                if ($combi === false) {
                    $ret[] = $product_prefix . $result['product_id'];
                } else {
                    $p = new Product($result['product_id']);
                    if ($p->cache_default_attribute > 0) {
                        $ret[] = $product_prefix . $p->id . $combi . $p->cache_default_attribute;
                    } else {
                        $ret[] = $product_prefix . $p->id;
                    }
                }
            }
        }
        //print_r($ret);
        return $ret;
    }
    private function displayPixels($typeid)
    {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'facebookpixels WHERE pixel_type = ' . (int)$typeid . ' AND pixel_active = 1';
        if ($results = Db::getInstance()->ExecuteS(pSQL($sql))) {
            $this->context->smarty->assign(array('pixels_' . $typeid => $results));
            return true;
        } else {
            return false;
        }
    }
    private function getCategories($id_lang, $id_shop)
    {
        $sql = 'SELECT id_category, id_parent, level_depth, name, is_root_category, active FROM ' . _DB_PREFIX_ . 'category LEFT JOIN ' . _DB_PREFIX_ . 'category_lang AS cl USING (id_category) LEFT JOIN ' . _DB_PREFIX_ . 'category_shop AS cs USING (id_category) WHERE cs.id_shop = ' . (int)$id_shop . ' AND cl.id_lang = ' . (int)$id_lang . ' ORDER BY `' . _DB_PREFIX_ . 'category`.`id_parent` ASC, `' . _DB_PREFIX_ . 'category`.`id_category` ASC';
        $cat = array();
        if (!($results = DB::getInstance(_PS_USE_SQL_SLAVE_)->executeS(pSQL($sql)))) {
            return '';
        } else {
            foreach ($results as $result) {
                $cat[$result['id_category']] = $result;
            }
        }
        return $cat;
    }
    public function tryGetBreadcrumb($id_product = 0)
    {
        $p = new Product($id_product);
        return str_replace('\'', '\\\'', self::getBreadcrumb($p->id_category_default));
    }
    private function getBreadcrumb($idCatToFind, $ret = '')
    {
        $categories = self::getCategories(Context::getContext()->cookie->id_lang, Context::getContext()->shop->getContextShopGroupID());
        if (isset($categories[$idCatToFind])) {
            if (is_numeric($idCatToFind)) {
                if ($ret != '') {
                    $ret = ' > ' . $ret;
                }
                $ret = str_replace('&', '&amp;', $categories[$idCatToFind]['name']) . $ret;
                if (!$categories[$idCatToFind]['is_root_category']) {
                    if (isset($categories[$idCatToFind]['id_parent']) && $categories[$idCatToFind]['id_parent'] != '') {
                        return self::getBreadcrumb($categories[$idCatToFind]['id_parent'], $ret);
                    }
                }
            }
            if ($categories[$idCatToFind]['is_root_category']) {
                if (function_exists('mb_convert_case')) {
                    return mb_convert_case($ret, MB_CASE_TITLE, "UTF-8");
                } else {
                    return $this->stringTitleFormat($ret, ' > ');
                }
            }
        }
        return '';
    }
    private function stringTitleFormat($text, $delimiter)
    {
        $text = explode($delimiter, $text);
        foreach ($text as $k => $v) {
            $text[$k] = ucwords(Tools::strtolower($v));
        }
        return implode($delimiter, $text);
    }
    public function printPixels($params)
    {
        if (!((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && Tools::strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || (Tools::getIsset('content_only') && Tools::getValue('content_only') == 1))) {
            $time = time();
            $output = '';
            //Get Current Controller (only in 1.6)
            $entity = $this->context->controller->php_self;
            $lang_id = $this->context->cookie->id_lang;
            // Get known One Page Checkout modules
            $opc_modules = $this->getOPCModules();
            $search = Configuration::get('FCP_CUST_SEARCH') != '' ? Configuration::get('FCP_CUST_SEARCH') : 'search';
            if ($entity == '') {
                $entity = Tools::getValue('controller');
            }
            $content_category = '';
            switch ($entity) {
                case 'product':
                    $pp = Configuration::get('PS_PRICE_DISPLAY_PRECISION') == false ? 2 : Configuration::get('PS_PRICE_DISPLAY_PRECISION');
                    $usetax = !Group::getPriceDisplayMethod(Group::getCurrent()->id);
                    $ipa = Tools::getIsset('id_product_attribute') ? Tools::getValue('id_product_attribute') : null;
                    $product = new Product(Tools::getValue('id_product'));
                    //$facebook_pixel_id = $this->getPixelFromCategoryId((int)$product->id_category_default);
                    $entityname = $product->name[$lang_id];
                    // Price calculation instead of $product->price since the results where not reliable enough
                    $entityprice = Product::getPriceStatic((int) $product->id, $usetax, $ipa, $pp, null, false, true, 1, false, null, null, null);
                    $combinations = $product->getAttributeCombinations($lang_id);
                    $hascombi = empty($combinations) ? '0' : '1';
                    $this->context->smarty->assign(array('hascombi' => $hascombi));
                    $content_category = str_replace('&amp;', '&', $this->tryGetBreadcrumb(Tools::getValue('id_product')));
                    break;
                case 'category':
                    $entityname = new Category(Tools::getValue('id_category'));
                    $entityname = $entityname->name[$lang_id];
                    //$facebook_pixel_id = $this->getPixelFromCategoryId((int)Tools::getValue('id_category'));
                    if (Configuration::get('FCTP_CATEGORY_TOP_SALES')) {
                        $this->context->smarty->assign(array('top_sell_ids' => $this->getCategoryTopSales((int)Tools::getValue('id_category'))));
                    }
                    $this->context->smarty->assign(array(
                        'max_cat_items' => Configuration::get('FCTP_CATEGORY_ITEMS'),
                        'category_value' => Configuration::get('FCTP_CATEGORY_VALUE'),
                    ));
                    $content_category = str_replace('&amp;', '&', $this->getBreadcrumb(Tools::getValue('id_category')));
                    break;
                case 'cms':
                    $entityname = new CMS(Tools::getValue('id_cms'));
                    $entityname = $entityname->meta_title[$lang_id];
                    break;
            }
            //set userdata
            if ($this->api !== false) {
                $user_data = $this->api->getUserData();
            } else {
                $conversionapi = new ConversionApi();
                $user_data = $conversionapi->getUserData();
            }
            $pageiew_event_id =  Tools::passwdGen(12);

            $custom_order_entity = false;
            if (Configuration::get('FCP_CUST_CHECKOUT') != '' && Validate::isModuleName(Tools::getValue('module'))) {
                array_unshift($opc_modules, array(Configuration::get('FCP_CUST_CHECKOUT'), Configuration::get('FCP_CUST_CHECKOUT')));
            }
            if (count($opc_modules) > 0 && !empty($opc_modules)) {
                foreach ($opc_modules as $opc) {
                    if ($opc == $entity) {
                        $custom_order_entity = true;
                    }
                }
            }
            $this->context->smarty->assign(
                array(
                    'is_17' => $this->is_17,
                    'fctpid' => explode(',', preg_replace('/[^,0-9]/', '', Configuration::get('FCTP_PIXEL_ID'))),
                    'entity' => $entity,
                    'entityname' => (isset($entityname) ? str_replace('\'', '\\\'', $entityname) : ''),
                    'entityprice' => (isset($entityprice) ? $entityprice : ''),
                    'fctp_currency' => $this->context->currency->iso_code,
                    'content_category' => $content_category,
                    'event_time' => $time,
                    'id_customer_or_guest' => (Context::getContext()->customer->isLogged() ? Context::getContext()->cookie->id_customer : Context::getContext()->cookie->id_guest),
                    'user_data' => Tools::jsonEncode($user_data),
                    'external_id' => $user_data['external_id'],
                    'combi_enabled' => Configuration::getGlobalValue('FCTP_COMBI_' . $this->context->shop->id),
                    'combi_prefix' => Configuration::getGlobalValue('FCTP_COMBI_PREFIX_' . $this->context->shop->id),
                    'product_combi' => Tools::getIsset('id_product_attribute') ? Tools::getValue('id_product_attribute') : 0,
                    'pageiew_event_id' => $pageiew_event_id,
                    'fbp_custom_checkout' => (int)$custom_order_entity
                )
            );
            $this->checkConsentSent();
            if ($this->api !== false && !in_array(Context::getContext()->controller->php_self, array('pagenotfound'))) {
                $this->api->pageViewTrigger($pageiew_event_id);
            }
            $output .= $this->display(__FILE__, '/views/templates/hook/pixelheader.tpl');
            // If there is a new order print it!

            if (Configuration::get('FCTP_ADD_TO_CART') == 1) {
                $cart_url = explode('/', $this->context->link->getPageLink('cart'));
                $ps_round_mode = Configuration::get('PS_PRICE_ROUND_MODE');
                $this->context->smarty->assign(array(
                    'custom_add_to_cart' => Configuration::get('FCP_CUST_ADD_TO_CART'),
                    'currency_format_add_tocart' => $this->context->currency->iso_code,
                    'fp_cart_endpoint' => end($cart_url),
                    'fp_round_mode' => $ps_round_mode
                ));
                if ($this->is_17) {
                    $output .= $this->display(__FILE__, '/views/templates/hook/addtocart17.tpl');
                } else {
                    // Ignore combination check for modules that dynamically generates the idCombination
                    if (Module::isEnabled('pm_advancedpack')) {
                        $this->context->smarty->assign('module_ignore_combi', 1);
                    }
                    $output .= $this->display(__FILE__, '/views/templates/hook/addtocart.tpl');
                }
            }

            $this->getProductsCartSmarty();
            // Order related
            if ($entity == 'order' || $entity == 'order-opc' || $custom_order_entity) {
                // Assign the Cart products into a json variable
                // Include the one page checkout template to control registering.
                if (Configuration::get('FCTP_REG') && ($entity == 'order-opc' || $custom_order_entity)) {
                    $this->context->smarty->assign('complete_registration_value', Configuration::get('FCTP_REG_VALUE'));
                    $output .= $this->display(__FILE__, '/views/templates/hook/opc-registration.tpl');
                }
                if (Configuration::get('FCTP_START_ORD')) {
                    // It just started the Order process
                    if (Configuration::get('FCTP_INIT_CHECKOUT_MODE') == 1) {
                        if (!$this->context->cookie->__isset('InitiateCheckout')) {
                            $fb_event_checkout_page = Tools::passwdGen(12);
                            $this->context->smarty->assign(
                                array('fb_event_checkout_page' => $fb_event_checkout_page)
                            );
                            //echo '<!-- MODE 1 -->';
                            $this->context->smarty->assign('initiate_checkout_value', Configuration::get('FCTP_START_ORD_VALUE'));
                            $output .= $this->display(__FILE__, '/views/templates/hook/initiate_checkout.tpl');
                            if ($this->api !== false) {
                                $this->api->initiateCheckoutTrigger($fb_event_checkout_page, (int)$this->context->cart->id);
                            }
                        }
                    } elseif (!Tools::getIsset('step') && Configuration::get('FCTP_INIT_CHECKOUT_MODE') == 2) {
                        // Trigger when click on start order
                        $this->context->smarty->assign('initiate_checkout_value', Configuration::get('FCTP_START_ORD_VALUE'));
                        $output .= $this->display(__FILE__, '/views/templates/hook/initiate_checkout.tpl');
                    }
                }
                // Choose payment method (start payment of the order)
                if (Tools::getValue('step') == 3 || ($entity == 'order' && $this->is_17) || $entity == 'order-opc' || $custom_order_entity) {
                    if (Configuration::get('FCTP_START') == 1) {
                        $fb_event =  Tools::passwdGen(12);
                        $this->context->smarty->assign(
                            array('fb_event_start_payment' => $fb_event)
                        );
                        $value = ((int)trim(Configuration::get('FCTP_START_VALUE') > 0)) ? Configuration::get('FCTP_START_VALUE') : $this->context->cart->getOrderTotal(true, Cart::BOTH);
                        $this->context->smarty->assign('initiate_payment_value', $value);
                        $output .= $this->display(__FILE__, '/views/templates/hook/add_payment_info.tpl');
                    }
                }
            }

            if (!Configuration::get('FCTP_FORCE_BASIC')) {
                $output .= $this->addConversionPixel($params);
            }

            // Wishlist
            if (trim(Configuration::get('FCP_CUST_WISHLIST_MODULE')) != '' && Configuration::get('FCTP_WISH') == 1) {
                $wishlist_value = !empty(Configuration::get('FCTP_WISH_VALUE')) ? Configuration::get('FCTP_WISH_VALUE') : 1;
                $this->context->cookie->fb_wishlist_event =  Tools::passwdGen(8);
                $this->context->smarty->assign(
                    array(
                        'wishlist_custom_button' => Configuration::get('FCP_CUST_WISHLIST_MODULE'),
                        'wishlist_value' => $wishlist_value,
                        'fb_wishlist_event' => $this->context->cookie->fb_wishlist_event,
                    )
                );
                $output .= $this->display(__FILE__, '/views/templates/hook/wishlist.tpl');
            }

            // Key Page || ViewContent
            // Toghether to avoid duplicates
            if ($this->displayPixels(1)) {
                $this->prepareKeypages();
                $output .= $this->display(__FILE__, '/views/templates/hook/keypage.tpl');
            }
            if (Configuration::get('FCTP_VIEWED') == 1 && $entity == 'product') {
                $nprod = new Product(Tools::getValue('id_product'));
                $this->context->smarty->assign(
                    array(
                        'product_id' => Tools::getValue('id_product'),
                        'price' => $nprod->getPrice(Group::getDefaultPriceDisplayMethod(), Tools::getIsset('id_product_attribute') ? Tools::getValue('id_product_attribute') : null, Configuration::get('PRICE_DISPLAY_PRECISION')),
                        'name' => str_replace('\'', '\\\'', $nprod->name[$this->context->language->id]),
                    )
                );
                $fb_pixel_event_id = Tools::passwdGen(12);
                $this->context->smarty->assign(
                    array('fb_pixel_event_id_view' => $fb_pixel_event_id)
                );
                if ($this->api !== false) {
                    $id_att = Tools::getIsset('id_product_attribute') ? Tools::getValue('id_product_attribute') : null;
                    $this->api->viewContentTrigger($fb_pixel_event_id, (int)$lang_id, 'product', (int)Tools::getValue('id_product'), $id_att);
                }
                $output .= $this->display(__FILE__, '/views/templates/hook/viewcontent.tpl');
            }
            // Registered Customer
            if (Configuration::get('FCTP_REG')) {
                if (Configuration::get('FCTP_AJAX_REG')) {
                    $this->context->smarty->assign(array('fctp_ajaxurl' => $this->context->link->getModuleLink('facebookconversiontrackingplus', 'AjaxConversion')));
                }
                if ($entity == 'guest-tracking') {
                    $this->context->smarty->assign('complete_registration_value', Configuration::get('FCTP_REG_VALUE'));
                    $output .= $this->display(__FILE__, '/views/templates/hook/registration.tpl');
                } elseif (Configuration::get('pixel_account_on') != '') {
                    if ($this->prepareCustomer()) {
                        $this->context->smarty->assign('complete_registration_value', Configuration::get('FCTP_REG_VALUE'));
                        $output .= $this->display(__FILE__, '/views/templates/hook/registration.tpl');
                        if (!Configuration::get('FCTP_AJAX_REG')) {
                            //Tools::dieObject(array('Registration complete'));
                            Configuration::updateValue('pixel_account_on', '');
                        }
                    }
                }
            }
            // is a search
            if ($entity == $search) {
                if (Configuration::get('FCTP_SEARCH') == 1) {
                    $max_cat_items = Configuration::get('FCTP_CATEGORY_ITEMS');
                    $this->context->smarty->assign(array(
                        'max_cat_items' => $max_cat_items,
                    ));
                    $fb_pixel_event_search =  Tools::passwdGen(12);
                    $this->context->smarty->assign(
                        array('fb_pixel_event_id_search' => $fb_pixel_event_search)
                    );
                    $search_query = $search == 'search' ? Tools::getValue('search_query') : Tools::getValue(Configuration::get('FCP_CUST_SEARCH_P'));
                    $this->context->smarty->assign(array('search_keywords' => $search_query));
                    $this->context->smarty->assign('search_value', Configuration::get('FCTP_SEARCH_VALUE'));
                    $search_value = Configuration::get('FCTP_SEARCH_VALUE');
                    $content_ids_list =  array();

                    if (isset($this->context->smarty->tpl_vars['listing'])) {
                        /* TODO MATHAN REVIEW */
                        $results  = Tools::jsonEncode($this->context->smarty->tpl_vars['listing']->value);
                        $results = Tools::jsonDecode($results, true);
                        $results = $results['products'];
                        $i = 0;
                        while ($i < $max_cat_items) {
                            if (isset($results[$i]['id'])) {
                                $content_ids_list[] =  $results[$i]['id'];
                            } else {
                                break;
                            }
                            $i++;
                        }
                    } elseif ($this->context->smarty->tpl_vars['search_products']) {
                        $results  = $this->context->smarty->tpl_vars['search_products']->value;
                        $i = 0;
                        while ($i < $max_cat_items) {
                            if (isset($results[$i]['id_product'])) {
                                $content_ids_list[] =  $results[$i]['id_product'];
                            } else {
                                break;
                            }
                            $i++;
                        }
                    }
                    if ($this->api !== false) {
                        $this->api->searchEventTrigger($fb_pixel_event_search, (int)$lang_id, $search_query, $search_value, $content_ids_list);
                    }

                    $output .= $this->display(__FILE__, '/views/templates/hook/search.tpl');
                }
            }

            // New event viewCategory
            if ($entity == 'category') {
                $id_category = (int)Tools::getValue('id_category');
                if ($id_category > 0) {
                    $fb_pixel_event_id = Tools::passwdGen(12);
                    $this->context->smarty->assign('fb_pixel_event_id_view', $fb_pixel_event_id);
                    if (Configuration::get('FCTP_CATEGORY') == 1) {
                        if ($this->api !== false) {
                            $this->api->viewContentTrigger($fb_pixel_event_id, (int)$lang_id, 'category', $id_category);
                        }
                        $this->context->smarty->assign('pix_category_id', $id_category);
                        $output .= $this->display(__FILE__, '/views/templates/hook/category.tpl');
                    }
                }
            }


            if (Configuration::get('FCP_PRODUCT_CUSTOM_SELECTOR') != "" && $entity == 'product') {
                $this->context->smarty->assign(array(
                    'fcp_product_custom_selector' => Configuration::get('FCP_PRODUCT_CUSTOM_SELECTOR')
                ));
                $output .= $this->display(__FILE__, '/views/templates/hook/product_custom.tpl');
            }


            $this->pixels_printed = true;
            return $output;
        }
        return '';
    }
    private function isAllowedControllersForPurchase($entity)
    {
        if (Configuration::get('FCTP_LIMIT_CONF') && $entity == 'order-confirmation') {
            return true;
        }
        return false;
    }
    private function getOPCModules()
    {
        $modules_list = array('supercheckout' => 'supercheckout', 'onepagecheckout' => 'order', 'onepagecheckoutps' => 'order', 'steasycheckout' => 'default', 'thecheckout' => 'order');
        foreach ($modules_list as $module => $controller) {
            if (!Module::isEnabled($module)) {
                unset($modules_list[$module]);
            }
        }
        /*if ($_SERVER['REMOTE_ADDR'] == '139.47.41.7') {
            print_r($modules_list);
        }*/
        return $modules_list;
    }
    private function checkConsentSent()
    {
        $consent = true;
        if (Configuration::get('FCTP_BLOCK_SCRIPT')) {
            $consent = false;
            $cookie = Configuration::get('FCTP_COOKIE_NAME');
            if ($cookie != '') {
                $value = Configuration::get('FCTP_COOKIE_VALUE');
                $consent = $this->checkCookies($cookie, $value);
            }
        }

        $this->context->smarty->assign(
            array(
                'pixel_consent' => $consent,
                'cookie_reload' => (int)Configuration::get('FCTP_COOKIE_RELOAD'),
                'cookie_check_button' => Configuration::get('FCTP_COOKIE_BUTTON'),
                'cookie_token' => Tools::encrypt('CookieValidate' . ($this->context->cookie->id_customer > 0 ? $this->context->cookie->id_customer : $this->context->cookie->id_guest))
            )
        );
    }
    public function checkCookies($cookie, $value)
    {
        if (Configuration::get('FCTP_COOKIE_EXTERNAL')) {
            if (isset($_COOKIE[$cookie])) {
                if ($value != '') {
                    if (Tools::strpos($_COOKIE[$cookie], $value) !== false ||
                        Tools::strpos(urlencode($_COOKIE[$cookie]), $value) !== false) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    return true;
                }
            }
        } else {
            if ($this->context->cookie->__isset($cookie)) {
                if ($value !== '') {
                    if (Tools::strpos($this->context->cookie->__get($cookie), $value) !== false) {
                        return true;
                    } else {
                        return false;
                    }
                }
            }
        }
        return false;
    }
    public function hookHeader($params)
    {
        return $this->hookDisplayHeader($params);
    }

    private function setFeedV2()
    {
        // TODO Change feed_ve from static to no static
    }

    public static function getFeedId()
    {
        if (self::$feed_v2) {
            $feed_id = Configuration::get('FCTP_FEED_' . Context::getContext()->shop->getContextShopGroupID());
        } else {
            $feed_id = Configuration::get('FPF_' . Context::getContext()->shop->getContextShopGroupID() . '_' . Context::getContext()->cookie->id_lang);
        }
        return $feed_id;
    }

    public function hookDisplayHeader($params)
    {
        $output = '';
        $url = Tools::getCurrentUrlProtocolPrefix() . $_SERVER['HTTP_HOST'] . '/' . ltrim($_SERVER['REQUEST_URI'], '/');
        if (!$this->isAjaxRequest() && $this->context->cookie->__isset('InitiateCheckout')) {
            if ($this->context->cookie->__get('InitiateCheckout') != $url) {
                $this->context->cookie->__unset('InitiateCheckout');
            }
        }
        if (Configuration::get('FCTP_VERIFY_DOMAIN') != '') {
            $output .= '<meta name="facebook-domain-verification" content="' . Configuration::get('FCTP_VERIFY_DOMAIN') . '" />';
        }
        $price_precision = Configuration::get('PS_PRICE_DISPLAY_PRECISION') == false ? 2 : Configuration::get('PS_PRICE_DISPLAY_PRECISION');
        // Add necessary variables to smarty
        $combi = Configuration::getGlobalValue('FCTP_COMBI_' . $this->context->shop->id);
        $this->context->smarty->assign(
            array(
                'dynamic_ads' => 1,
                'prefix' => Configuration::get('FPF_PREFIX_' . $this->context->shop->id),
                'id_prefix' => Configuration::get('FPF_PREFIX_' . $this->context->shop->id),
                'combi' => $combi,
                'combi_prefix' => ($combi ? Configuration::getGlobalValue('FCTP_COMBI_PREFIX_' . $this->context->shop->id) : ''),
                'price_precision' => $price_precision,
                'ajax_events_url' => $this->context->link->getModuleLink('facebookconversiontrackingplus', 'AjaxConversion'),
            )
        );
        $feed_id = 0;
        // Check If dynamic ads can be enabled
        if (Configuration::get('FCTP_ADD_TO_CART') == 1 && Configuration::get('FCTP_CONV') == 1 && Configuration::get('FCTP_DYNAMIC_ADS') == 1) {
            // Add the Feed ID for Dynamic Product Ads
            $feed_id = $this->getFeedId();
            if ($feed_id != '') {
                $this->fpf_id = $feed_id;
            }
        } else {
            $this->context->smarty->assign(array('dynamic_ads' => 0));
        }
        $this->context->smarty->assign(array('fpf_id' => $feed_id));
        if (Configuration::get('FCTP_FORCE_HEADER') == 1) {
            $output .= $this->printPixels($params);
        }
        if (Tools::getIsset('id_product')) {
            $p = new Product((int)Tools::getValue('id_product'));
            if (Validate::isLoadedObject($p) && $p->active) {
                /* Micro Data Init */
                if ($this->displayMicrodata && !isset($this->og)) {
                    $this->getRequiredProductData();
                    if (isset($this->og) && (count($this->og) > 0) && (!Tools::getIsset('mdata') || Tools::getValue('mdata') != 0)) {
                        $this->context->smarty->assign(
                            array(
                                'og_data' => $this->og,
                                'localization_info' => ' country: ' . $this->context->country->id . ' - Cust ID Zone' . $this->context->country->id_zone . '- Cust ISO Code' . (int) $this->context->country->iso_code,
                                'ip' => $_SERVER['REMOTE_ADDR'],
                                'country' => $this->context->country->id,
                                'req' => isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? $_SERVER['HTTP_X_REQUESTED_WITH'] : '',
                                'tax' => !Group::getPriceDisplayMethod(Group::getCurrent()->id),
                            )
                        );
                        $output .= $this->display(__FILE__, '/views/templates/front/add-missing-og.tpl');
                    }
                }
            }
        }
        if (Configuration::get('FCTP_FORCE_REFRESH_AFTER_ORDER') && $this->context->controller->php_self == 'order-confirmation' && !isset($_COOKIE['fctp_reload'])) {
            $output .= '<meta http-equiv="refresh" content="0.1">';
            // The cookie will last 60 seconds only, this is to prevent issues with new possible orders
            setcookie('fctp_reload', 1, time() + 60);
        }

        if (Configuration::get('FCP_PRODUCT_CUSTOM_SELECTOR') && isset($_COOKIE['CustomizeProductSent']) && $this->api !== false && $this->context->controller->php_self != 'pagenotfound' && in_array($this->context->controller->php_self, array('product', 'category', 'cms', 'order', 'cart'))) {
            $jsonObj = Tools::jsonDecode($_COOKIE['CustomizeProductSent'], true);
            if (is_array($jsonObj) && count($jsonObj)) {
                foreach ($jsonObj as $eventId => $data) {
                    $this->api->customizeProductTrigger($data, $eventId);
                }
                setcookie('CustomizeProductSent', "{}", time() - 12000);
            }
        }

        return $output;
    }
    private function getProductsCartSmarty($id_cart = 0)
    {
        if ($id_cart == 0) {
            if (Tools::getIsset('id_order') && (int)Tools::getValue('id_order') > 0) {
                $ordObj = new Order((int)Tools::getValue('id_order'));
            } elseif (isset($this->context->cart->id) && $this->context->cart->id > 0) {
                $cartObj = new Cart($this->context->cart->id);
            } else {
                return false;
            }
        } else {
            $cartObj = new Cart($id_cart);
        }
        $productsCart = isset($cartObj) ? $cartObj->getProducts() : $ordObj->getProducts();
        //var_dump($productsCart);
        $prefix = Configuration::get('FPF_PREFIX_' . $this->context->shop->id);
        $combi = Configuration::getGlobalValue('FCTP_COMBI_' . $this->context->shop->id);
        $combi_prefix = Configuration::getGlobalValue('FCTP_COMBI_PREFIX_' . $this->context->shop->id);
        $pcart = array();
        $content_products = array();
        foreach ($productsCart as $product) {
            $p_id = $prefix . $product['id_product'] . (isset($product['id_product_attribute']) && $product['id_product_attribute'] > 0 && $combi ? $combi_prefix . (int)$product['id_product_attribute'] : '');
            $pcart[] =  $p_id;
            $p_data = array(
                'id' =>  $p_id,
                'quantity' =>  isset($product['cart_quantity']) ? $product['cart_quantity'] : $product['product_quantity'],
                'category' => str_replace('&amp;', '&', $this->tryGetBreadcrumb($p_id)),
                'price' => Tools::ps_round($product['total_wt'], _PS_PRICE_DISPLAY_PRECISION_)
            );
            $content_products[] = $p_data;
        }
        $total = isset($cartObj) ? $cartObj->getOrderTotal(true, Cart::BOTH) : $ordObj->total_paid;
        $this->context->smarty->assign(
            array(
                'pcart' => Tools::jsonEncode($pcart),
                'pcart_value' => $total,
                'pcart_currency' => $this->context->currency->iso_code,
                'pcart_contents' => Tools::jsonEncode($content_products),
                'ic_mode' => Configuration::get('FCTP_INIT_CHECKOUT_MODE'),
            )
        );
    }
    public function hookDisplayFooter($params)
    {
        if (Configuration::get('FCTP_FORCE_HEADER') != 1) {
            return $this->printPixels($params);
        }
    }
    public function hookDisplayBeforeBodyClosingTag($params)
    {
        if ($this->pixels_printed == false) {
            return $this->printPixels($params);
        }
    }
    public function hookActionCartSave($params)
    {
        $entity = $this->context->controller->php_self;

        if (Configuration::get('FCTP_ADD_TO_CART') && ($entity == 'index' || $entity == 'product' || $entity == 'category' || $entity == 'search') || $entity == 'cart') {
            if ($this->api !== false && Tools::getValue('delete') != 1) {
                $this->api->addToCartTrigger();
            }
        }
    }
    public function hookActionCustomerAccountAdd($params)
    {
        Configuration::updateValue('pixel_account_on', $params['newCustomer']->id);
        $lang_id = $this->context->cookie->id_lang;
        if (Configuration::get('FCTP_REG')) {
            if ($this->api !== false) {
                $this->api->accountRegisterTrigger((int)$params['newCustomer']->id, (int)$lang_id);
            }
        }
    }
    public function hookCreateAccount($params)
    {
        return $this->hookActionCustomerAccountAdd($params);
    }
    public function hookActionValidateOrder($params)
    {
        $exluded_order_status = explode(',', Configuration::get('FCTP_ORDER_STATUS_EXCLUDE'));
        $order_status = (int)$params['orderStatus']->id;
        if (!in_array($order_status, $exluded_order_status)) {
            $fb_event_purchase_page =  Tools::passwdGen(12);
            $this->context->cookie->fb_event_purchase_page = $fb_event_purchase_page;
            $id = '';
            if (isset($params['objOrder'])) {
                $order = $params['objOrder'];
            } elseif (isset($params['order'])) {
                $order = $params['order'];
            }
            if (isset($order->id)) {
                $id = $order->id;
                // Compatibility check
                if ($id == '') {
                    $id = $order->id_order;
                }
                $pending_orders = Tools::jsonDecode(Configuration::get('FCP_ORDER_CONVERSION'), true);
                if (!is_array($pending_orders)) {
                    $pending_orders = array();
                }
                // Order Id Customer
                $oic = $this->context->cookie->is_guest == 1 ? 0 : $order->id_customer;
                $pending_orders[$oic] = array(
                    $id,
                    $oic,
                    $this->context->cookie->is_guest,
                    $fb_event_purchase_page
                );

                if (!empty(Configuration::get('FCTP_CONV'))) {
                    if ($this->api !== false) {
                        $this->api->purchaseEventTrigger($fb_event_purchase_page, $id);
                    } else {
                        $conversionapi = new ConversionApi();
                        $conversionapi->purchaseEventTrigger($fb_event_purchase_page, $id);
                    }
                }
                //setting the current order as pending order via cookie
                Configuration::updateValue('FCP_ORDER_CONVERSION', Tools::jsonEncode($pending_orders));
                //$this->context->cookie->FCP_ORDER_CONVERSION = Tools::jsonEncode($pending_orders);
            }
        } else {
            PrestaShopLogger::addLog('[Pixel Plus] Order State ('.$params['orderStatus']->name.') excluded by module configuration', 1, null, 'Pixel Plus');
        }
    }
    /* Customer CSV Export Start */
    private function getCurrentUrl()
    {
        $url = Tools::strlen($_SERVER['QUERY_STRING']) ? basename($_SERVER['PHP_SELF']) . '?' . $_SERVER['QUERY_STRING'] : basename($_SERVER['PHP_SELF']);
        $pos = strpos($url, 'typexp');
        if ($pos === false) {
            return $url;
        } else {
            return Tools::substr($url, 0, $pos - 1);
        }
    }
    public static function getProcess($typexp)
    {
        // Process the files
        $res = array();
        if ($typexp > 0 && $typexp <= 3) {
            if (class_exists('DbQuery')) {
                $dbquery = new DbQuery();
                $dbquery->select('c.`email`');
                if ($typexp == 1 || $typexp == 3) {
                    $dbquery->from('customer', 'c');
                }
                if ($typexp == 2) {
                    $dbquery->from('newsletter', 'c');
                }
                $dbquery->groupBy('c.`email`');
                if (Context::getContext()->cookie->shopContext) {
                    $dbquery->where('c.id_shop = ' . (int)Context::getContext()->shop->id);
                }
                $rq = Db::getInstance()->executeS($dbquery->build());
            } else {
                $dbquery = 'SELECT c.email ';
                if ($typexp == 1 || $typexp == 3) {
                    $dbquery .= 'FROM ' . _DB_PREFIX_ . 'customer c ';
                }
                if ($typexp == 2) {
                    $dbquery .= 'FROM ' . _DB_PREFIX_ . 'newsletter c ';
                }
                $dbquery .= 'GROUP BY c.email';
                $rq = Db::getInstance()->executeS($dbquery);
            }
            // Newsletter customers for Export all
            if ($typexp == 3) {
                if (class_exists('DbQuery')) {
                    $dbquery = new DbQuery();
                    $dbquery->select('c.`email`');
                    $dbquery->from('newsletter', 'c');
                    $dbquery->groupBy('c.`email`');
                    $dbquery->where('c.id_shop = ' . (int)Context::getContext()->shop->id);
                    $rs = Db::getInstance()->executeS($dbquery->build());
                } else {
                    $dbquery = 'SELECT c.email FROM ' . _DB_PREFIX_ . 'newsletter c GROUP BY c.email';
                    $rs = Db::getInstance()->executeS($dbquery);
                }
            }
            if (is_array($rq)) {
                // Initialize the arrays
                $array1 = array();
                $array2 = array();
                foreach ($rq as $item) {
                    $array1[] = $item['email'];
                }
                // If we have the Newsletter array, merge it
                if (!empty($rs)) {
                    if (is_array($rs)) {
                        foreach ($rs as $item) {
                            $array2[] = $item['email'];
                        }
                        $array1 = array_unique(array_merge($array1, $array2));
                    }
                }
                $res = self::createCSV($array1, $typexp);
            }
            return $res;
        }
    }
    public function hookActionAdminControllerSetMedia()
    {
        if (Tools::getValue('configure') == 'facebookconversiontrackingplus') {
            if (version_compare(_PS_VERSION_, '1.6', '<')) {
                $this->context->controller->addCSS($this->_path . 'views/css/back-1.5.css');
            } else {
                $this->context->controller->addCSS($this->_path . 'views/css/back.css');
            }
            $this->context->controller->addCSS($this->_path . 'views/css/' . $this->name . '.css');
            $this->context->controller->addJS($this->_path . 'views/js/bo-form.js');
            $this->context->controller->addJS($this->_path . 'views/js/tab-magic-menus.js');
        }
    }
    public function hookDisplayOrderConfirmation($params)
    {
        $order = $this->getOrderFromParams($params);
        $this->getProductsCartSmarty($order->id_cart);

        $this->context->cookie->__unset('fb_event_purchase_page');
        if (Configuration::get('FCTP_FORCE_BASIC')) {
            if ($order !== false) {
                return $this->addConversionPixel($params);
            }
        }
    }
    private function getOrderFromParams($params)
    {
        if (isset($params['id_order'])) {
            return new Order((int)$params['id_order']);
        } elseif (isset($params['order']->id)) {
            return  $params['order'];
        } elseif (isset($params['objOrder']->id)) {
            return  $params['objOrder'];
        }
        return false;
    }
    public function trackAjaxConversion($id_customer)
    {
        $pending_orders = Tools::jsonDecode(Configuration::get('FCP_ORDER_CONVERSION'), true);
        if (Tools::getValue('fctp_token') == Tools::encrypt('Conversion' . Tools::getValue('id_customer') . ':' . Tools::getValue('id_order'))) {
            //$conversion = $this->customerHasPendingOrder($pending_orders, $id_customer);
            return $this->clearPendingOrder($pending_orders, $id_customer);
        }
        return false;
    }

    public function trackAjaxRegistration()
    {
        return Configuration::deleteByName('pixel_account_on');
    }
    private function displayFAQ()
    {
        // FAQ Answers moved to faq-answers.tpl
        $this->context->smarty->assign(
            array(
                'faq' => array(
                    'all_yellow' => array(
                        'id' => 'all_yellow',
                        'question' => 'Pixel Helper: ' . $this->l('All events display a yellow triangle'),
                        'image' => 'all-yellow-events.jpg',
                    ),
                    'some_events_yellow' => array(
                        'id' => 'some_events_yellow',
                        'image' => 'some-events-yellow.jpg',
                        'question' => 'Pixel Helper: ' . $this->l('Some events are yellow, what to do?'),
                    ),
                    'dynamic_event' => array(
                        'id' => 'dynamic_event',
                        'image' => 'dynamic-event.jpg',
                        'question' => 'Pixel Helper: ' . $this->l('We detected event code but the pixel has not activated for this event...'),
                    ),
                    'wrong_catalogue_id' => array(
                        'id' => 'wrong_catalogue_id',
                        'image' => 'wrong-catalogue-id.jpg',
                        'question' => 'Pixel Helper :' . $this->l('The specified product catalog ID is not valid...'),
                    ),
                    'purchase_duplicates' => array(
                        'id' => 'purchase_duplicates',
                        'question' => $this->l('I see some duplicate / missing Purchases in Facebook'),
                    ),
                    'custom_events' => array(
                        'id' => 'custom_events',
                        'question' => $this->l('Can I use custom Events?'),
                    ),
                ),
                'img_path' => $this->_path . 'views/img/',
            )
        );
        return $this->display(__FILE__, '/views/templates/admin/faq.tpl');
    }
    private function displayVideos()
    {
        $this->context->smarty->assign(
            array(
                'fctp_videos' => array(
                    array(
                        'title' => $this->l('Installing Pixel Plus'),
                        'embed_url' => 'https://www.youtube.com/embed/KpuiRTUjGdM',
                    ),
                    array(
                        'title' => $this->l('Testing the events with Pixel Helper'),
                        'embed_url' => 'https://www.youtube.com/embed/fjbO2RA-OTc',
                    )
                )
            )
        );
        return $this->display(__FILE__, '/views/templates/admin/videos.tpl');
    }
    /* Customer Export to CSV */
    protected static function createCSV($res, $typexp)
    {
        $fctp = Module::getInstanceByName('facebookconversiontrackingplus');
        $_file = array(1 => 'export-customers.csv', 2 => 'export-newsletter.csv', 3 => 'export-all.csv');
        if (count($res) > 0) {
            $line = implode("\n", $res);
            if ($file = @fopen(dirname(__FILE__) . '/csv/' . (string)$_file[$typexp], 'w')) {
                if (!fwrite($file, $line)) {
                    echo Tools::displayError($fctp->l('Error: cannot write') . ' ' . dirname(__FILE__) . '/csv/' . $_file[$typexp] . ' !');
                    fclose($file);
                } else {
                    fclose($file);
                    return true;
                }
            } else {
                echo Tools::displayError($fctp->l('Bad permissions, can\'t create the file'));
            }
            return false;
        } else {
            return false;
            //echo $this->context->smarty->display(__FILE__, '/views/templates/admin/csv-creation-alert.tpl');
        }
    }
    /* Start Micro Data Features */
    public function checkMicroData()
    {
        $msg = array();
        $mdata = $this->getMetadataArray();
        if (Shop::isFeatureActive()) {
            $shops = Shop::getShops();
            foreach ($shops as $shop) {
                $url = $this->getRandomProductURL($shop['id_shop']);
                Configuration::updateValue('FCTP_MICRODATA', Tools::jsonEncode($this->reviewMicroData($mdata, $url)), false, $shop['id_shop_group'], $shop['id_shop']);
                $msg[] = sprintf($this->l('Microdata for Shop %s reviewed. Now product catalogues can be created with the Facebook\'s pixel events'), $shop['name']);
            }
        } else {
            $url = $this->getRandomProductURL();
            Configuration::updateValue('FCTP_MICRODATA', Tools::jsonEncode($this->reviewMicroData($mdata, $url)));
            $msg[] = $this->l('Microdata reviewed. Now product catalogues can be created with the Facebook\'s pixel events');
        }
        $this->context->controller->confirmations[] = implode('<br>', $msg);
    }
    private function reviewMicroData($mdata, $url)
    {
        $product_html = $this->getProductHTML($url);
        foreach ($mdata as $type => $fields) {
            foreach ($fields as $key => $item) {
                if ($type == 'og') {
                    $str = '&lt;meta property=&quot;' . $key . '&quot;';
                } elseif ($type == 'schema') {
                    $str = 'itemprop=&quot;' . $key . '&quot;';
                } else { // ItemType
                    $str = 'itemtype=&quot;' . $item . '&quot;';
                }
                // If it extists and we found it, we don't need to add this value
                if ($this->getStrPos($product_html, $str) !== false) {
                    unset($mdata[$type][$key]);
                }
            }
        }
        return $mdata;
    }
    private function getStrPos($haystack, $needle)
    {
        if (method_exists('Tools', 'strpos')) {
            return Tools::strpos($haystack, $needle);
        } else {
            return strpos($haystack, $needle);
        }
    }
    private function getRandomProductURL($id_shop = null, $mdata = false)
    {
        if ($id_shop === null) {
            $id_shop = Configuration::get('PS_SHOP_DEFAULT');
        }
        $sql = 'SELECT p.id_product 
                FROM `' . _DB_PREFIX_ . 'product` p' .
            Shop::addSqlAssociation('product', 'p') .
            ' WHERE product_shop.`visibility` IN ("both", "catalog")' .
            ' AND product_shop.`active` = 1' . '
                ORDER BY RAND()';
        $id_product = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
        if ($id_product > 0) {
            $p = new Product($id_product);
            $l = $this->context->language->id;
            $url = $this->context->link->getProductLink($p, $p->link_rewrite[$l], Category::getLinkRewrite($p->id_category_default, $l));
            if ($mdata == false) {
                $url .= ($this->getStrPos($url, '?') === false ? '?' : '&') . 'mdata=0';
            }
            return $url;
        }
        return false;
    }
    private function getProductHTML($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // follow redirects
        //avoid error on conversion api -user_agent variable
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        $output = curl_exec($ch);
        return htmlspecialchars($output);
    }
    private function getMetadataArray()
    {
        return array(
            'og' => array(
                'og:type' => 'type',
                'og:title' => 'title',
                'og:url' => 'url',
                'og:description' => 'description',
                'og:image' => 'image',
                'og:locale' => 'locale',
                'product:retailer_item_id' => 'retailer_item_id',
                'product:item_group_id' => 'groupID',
                'product:price:amount' => 'price',
                'product:price:currency' => 'priceCurrency',
                'product:condition' => 'condition',
                'product:availability' => 'availability',
                'brand' => 'brand',
                'product:category' => 'google_category',
                'product:sale_price:amount' => 'sale_price_amount',
                'product:sale_price:currency' => 'sale_price_currency',
                'product:sale_price_dates:start' => 'sale_price_dates_start',
                'product:sale_price_dates:end' => 'sale_price_dates_end',
            ),
            'schema' => array(
                'url' => 'url',
                'image' => 'image',
                'description' => 'description',
                'productID' => 'productID',
                'title' => 'title',
                'brand' => 'brand',
                'price' => 'price',
                'priceCurrency' => 'priceCurrency',
                'itemCondition' => 'conditionURL',
                'availability' => 'availability',
            ),
            'itemtype' => array(
                'product' => 'https://schema.org/Product',
                'offers' => 'https://schema.org/Offer'
            ),
        );
    }
    private function addMissingMicroData()
    {
        if ($this->displayMicrodata && $this->content_displayed === false && (!Tools::getIsset('mdata') || Tools::getValue('mdata') != 0)) {
            $this->content_displayed = true;
            // If not data is required return
            if (empty($this->schema)) {
                return;
            }
            $this->context->smarty->assign(
                array(
                    'micro_data' => $this->schema,
                    'schema' => $this->schema_structure
                )
            );
            return $this->display(dirname(__FILE__), '/views/templates/front/add-missing-microdata.tpl');
        }
    }
    private function addCombiInfo($params)
    {

        $ipa = false;
        $id_product = (int)Tools::getValue('id_product');
        if (is_object($params['product'])) {
            $ipa = $params['product']->id_product_attribute;
        } elseif (is_array($params['product'])) {
            $ipa = $params['product']['id_product_attribute'];
        } elseif (Tools::getIsset('id_product_attribute')) {
            $ipa = Tools::getValue('id_product_attribute');
        }
        $refresh_pixel_id = Tools::passwdGen(12);
        if ($this->api !== false && Tools::getValue('ajax') == 1) {
            $this->api->viewContentTrigger($refresh_pixel_id, $this->context->language->id, 'product', $id_product, $ipa);
        }

        if ($ipa !== false && $ipa > 0) { // WAS  && Tools::getValue('ajax') == 1
            $this->context->smarty->assign(array(
                'id_product_attribute' => $ipa,
                'refresh_pixel_id' =>  $refresh_pixel_id
            ));
            return $this->display(__FILE__, 'views/templates/front/productvars.tpl');
        }
    }

    private function dateFormatPixel($date)
    {
        $convertitme = strtotime($date);
        return date("Y-m-d", $convertitme) . 'T' . date("H:i:s", $convertitme) . '/' . date("Y-m-d", $convertitme) . 'T' . date("H:i:s", $convertitme);
    }

    private function getRequiredProductData()
    {
        if (empty($this->rmd) && !Tools::getValue('mdata')) {
            $this->content_displayed = true;
            return;
        }
        if (Tools::getIsset('id_product')) {
            $id = (int)Tools::getValue('id_product');
            $l = $this->context->language->id;
            $prefix = Configuration::getGlobalValue('FPF_PREFIX_' . $this->context->shop->id) ? Configuration::getGlobalValue('FPF_PREFIX_' . $this->context->shop->id) : '';
            $p = new Product($id);
            if (Configuration::getGlobalValue('FCTP_COMBI_' . $this->context->shop->id) && (Tools::getValue('id_product_attribute') > 0)) {
                $id = $prefix . $p->id . Configuration::getGlobalValue('FCTP_COMBI_PREFIX_' . $this->context->shop->id) . (int)Tools::getValue('id_product_attribute');
            }
            $this->image_format = $this->getImageFormat(ImageType::getImagesTypes('products', true));
            $images = $this->prepareProductImages($p, $p->getImages($this->context->language->id));
            $description = $this->prepareProductDescription($p);
            $pp = Configuration::get('PS_PRICE_DISPLAY_PRECISION') == false ? 2 : Configuration::get('PS_PRICE_DISPLAY_PRECISION');
            $usetax = !Group::getPriceDisplayMethod(Group::getCurrent()->id);
            $ipa = Tools::getIsset('id_product_attribute') ? Tools::getValue('id_product_attribute') : null;
            $brand = ($p->manufacturer_name != '' ? $p->manufacturer_name : ((int)$p->id_manufacturer > 0 ? Manufacturer::getNameById($p->id_manufacturer) : $this->context->shop->name));
            $priceDisplay = Product::getTaxCalculationMethod((int) $this->context->cookie->id_customer);
            if (!$priceDisplay || $priceDisplay == 2) {
                $productPriceWithoutReduction = $p->getPriceWithoutReduct(false, null);
            } elseif ($priceDisplay == 1) {
                $productPriceWithoutReduction = $p->getPriceWithoutReduct(true, null);
            }
            $productPriceWithoutReduction = Tools::ps_round($productPriceWithoutReduction, _PS_PRICE_DISPLAY_PRECISION_);
            $microdata = array(
                'productID' => $p->id,
                'groupID' => $prefix . $p->id,
                'retailer_item_id' => $id,
                'title' => $this->removeHTML($p->name[$this->context->language->id]),
                'description' => $description,
                'condition' => $p->condition,
                'itemCondition' => $p->condition == 'new' ? 'NewCondition' : ($p->condition == 'used' ? 'UsedCondition' : 'RefurbishedCondition'),
                'conditionURL' => $p->condition == 'new' ? 'http://schema.org/NewCondition' : ($p->condition == 'used' ? 'https://schema.org/UsedCondition' : 'https://schema.org/RefurbishedCondition'),
                'availability' => $this->getProductAvailability($id, $ipa),
                'url' => $this->context->link->getProductLink($p, $p->link_rewrite[$l], Category::getLinkRewrite($p->id_category_default, $l)),
                'image' => $images,
                'type' => 'product.item', //'og:product',
                'price' => $productPriceWithoutReduction,
                //'price' => Product::getPriceStatic((int) $p->id, $usetax, $ipa, $pp, null, false, true, 1, false, null, null, null, $p->specificPrice),
                //was $p->getPrice(!Group::getDefaultPriceDisplayMethod(), null, (int)Configuration::get('PS_PRICE_DISPLAY_PRECISION'))
                'priceCurrency' => $this->context->currency->iso_code,
                'google_category' => GoogleCategories::getCategoryNameById($p->id_category_default),
                'locale' => $this->getLocale(),
                'brand' => $brand
            );
            /*if (!Configuration::getGlobalValue('FCTP_COMBI_'.$this->context->shop->id)) {
                unset($data['groupID']);
            }*/
            $id_att = (int) Tools::getValue('id_product_attribute');
            if (Product::isDiscounted($p->id)) {
                $discountAll = SpecificPrice::getByProductId($p->id);
                //handle comibination products
                foreach ($discountAll as $discount) {
                    if ($id_att > 0 && $id_att != $discount['id_product_attribute']) {
                        continue;
                    }
                    $addfrom = false;
                    $addto = false;
                    if ($discount['to'] == '0000-00-00 00:00:00' && $discount['from'] == '0000-00-00 00:00:00') {
                        //no limit on both end so okay to conitnue the process
                        $addfrom = false;
                        $addto = false;
                    }
                    if (strtotime($discount['to']) >= strtotime(date("Y-m-d")) && strtotime($discount['from']) <= strtotime(date("Y-m-d"))) {
                        // coupon is valid
                        $addfrom = true;
                        $addto = true;
                    }
                    if (strtotime($discount['to']) <= strtotime(date("Y-m-d")) || strtotime($discount['from']) >= strtotime(date("Y-m-d"))) {
                        // coupon ends already
                        $addfrom = false;
                        $addto = false;
                    }
                    $microdata['sale_price_amount'] = Product::getPriceStatic((int) $p->id, $usetax, $ipa, $pp, null, false, true, 1, false, null, null, null, $p->specificPrice);
                    if ($addto) {
                        $microdata['sale_price_dates_end'] = $this->dateFormatPixel($discount['to']);
                    }
                    if ($addfrom) {
                        $microdata['sale_price_dates_start'] = $this->dateFormatPixel($discount['to']);
                    }
                    $microdata['sale_price_currency'] = $this->context->currency->iso_code;
                }
            }
            foreach ($this->rmd as $type => $fields) {
                foreach ($fields as $key => $item) {
                    if ($type == 'og') {
                        //ignore the item which are associated with sale but discount is not available for this product
                        if (isset($microdata[$item])) {
                            $this->og[$key] = $microdata[$item];
                        }
                    } elseif ($type == 'schema') {
                        if (!in_array($key, array('price', 'priceCurrency', 'itemCondition', 'availability'))) {
                            $base = 'product';
                        } else {
                            $base = 'offers';
                        }
                        $this->schema[$base][$key] = $microdata[$key];
                    } else { //ItemType
                        $this->schema_structure[$key] = 1;
                    }
                }
            }
        }
    }
    private function getImageFormat($images_types)
    {
        foreach ($images_types as $image_type) {
            if (preg_match('/large|thickbox/i', $image_type['name'])) {
                return $image_type;
            }
        }
        return $images_types[0]['name'];
    }
    private function getProductAvailability($id, $ipa)
    {
        $sa = StockAvailable::outOfStock($id);
        $quantity = StockAvailable::getQuantityAvailableByProduct($id, $ipa);
        if ($quantity <= 0) {
            if ($sa == 1 || ($sa == 2 && (int)Configuration::get('PS_ORDER_OUT_OF_STOCK'))) {
                return 'available for order';
            } else {
                return 'out of stock';
            }
        }
        return 'in stock';
    }
    private function prepareProductImages($p, $images)
    {
        if (Configuration::get('FCTP_MICRO_IGNORE_COVER')) {
            $cover = $images[0];
        } else {
            $cover = Image::getCover($p->id);
        }
        $total = count($images);
        $found = false;
        $ret = array();
        if ($total > 1) {
            for ($i = 0; $i < $total; $i++) {
                if ($images[$i]['id_image'] == $cover['id_image']) {
                    unset($images[$i]);
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                return false;
            }
            array_unshift($images, $cover);
        } else {
            $images = array($cover);
        }
        unset($cover, $total);
        if ((int)Configuration::get('FCTP_MICRO_IMG_LIMIT') > 0) {
            $images = array_slice($images, 0, (int)Configuration::get('FCTP_MICRO_IMG_LIMIT'));
        }
        foreach ($images as $image) {
            $ret[] = $this->context->link->getImageLink($p->link_rewrite[$this->context->language->id], $image['id_image'], $this->image_format['name']);
        }
        return $ret;
    }
    /**
     * Get the first description available between the short description
     * the long description or the product name in this order
     * It also removes all HTML and replaces the paragraphs for line breaks.
     * @param $p Product
     * @return the formatted text
     */
    private function prepareProductDescription($p)
    {
        $lang_id = (int)$this->context->language->id;
        $desc = $this->removeHTML($p->description_short[$lang_id]);
        if ($desc == '') {
            $desc = $this->removeHTML($p->description[$lang_id]);
            if ($desc == '') {
                $desc = $this->removeHTML($p->name[$lang_id]);
            }
        }
        return $desc;
    }
    private function removeHTML($string)
    {
        $search = array('</p>', '<br>', '<br/>', '<br />');
        $replace = array('</p>' . "\n\n", '<br>' . "\n", '<br/>' . "\n", '<br />' . "\n");
        $string = trim(strip_tags(str_replace($search, $replace, $string)));
        if ($this->countUpperCase($string) > (Tools::strlen($string) * 0.5)) {
            //return $this->removeUppercaseWords(strip_tags(str_replace($search, $replace, $str)));
            return $this->fixUppercase($string);
        }
        return $string;
    }
    /*private function removeUppercaseWords($desc)
    {
        $words = explode(' ', $desc);
        foreach ($words as &$w) {
            if ($this->countUpperCase($w) > (Tools::strlen($w) * 0.8)) {
                $w = $this->fixUppercase($w);
            }
        }
        return implode(' ', $words);
    }*/
    private function fixUppercase($string)
    {
        if (function_exists('mb_convert_case')) {
            return mb_convert_case($string, MB_CASE_TITLE, "UTF-8");
        } else {
            return ucwords($string);
        }
    }
    private function countUpperCase($string)
    {
        return Tools::strlen(preg_replace('![^A-Z]+!', '', $string));
    }
    private function getLocale()
    {
        $lang = $this->context->language;
        //$special_cases = array('en', 'es', 'fr', 'ja', 'nl', 'no', 'pt', 'tl');
        if (isset($lang->locale)) {
            $code = $lang->locale;
        } elseif (isset($lang->language_code)) {
            $code = $lang->language_code;
        } else {
            $code = $lang->iso_code;
        }
        $code = preg_split('/(\-|\_)/', $code);
        if (!isset($code[1])) {
            $code[1] = $code[0];
        }
        $code[1] = Tools::strtoupper($code[1]);
        return $code[0] . '_' . $code[1];
    }
    private function isAjaxRequest()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && Tools::strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
    public function hookDisplayProductAdditionalInfo($params)
    {
        $output = '';
        if ($this->isAjaxRequest()) {
            $output .= $this->addCombiInfo($params);
        }
        if (!$this->content_displayed) {
            $output .= $this->addMissingMicroData();
        }
        return $output;
    }
    public function hookDisplayProductButtons()
    {
        if (!$this->content_displayed) {
            return $this->addMissingMicroData();
        }
    }
    public function hookDisplayLeftColumnProduct()
    {
        if (!$this->content_displayed) {
            return $this->addMissingMicroData();
        }
    }
    public function hookDisplayRightColumnProduct()
    {
        if (!$this->content_displayed) {
            return $this->addMissingMicroData();
        }
    }
}

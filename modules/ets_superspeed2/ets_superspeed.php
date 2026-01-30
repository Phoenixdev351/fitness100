<?php
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */

if (!defined('_PS_VERSION_')) { exit; }
include_once(_PS_MODULE_DIR_ . 'ets_superspeed/classes/cache.php');
include_once(_PS_MODULE_DIR_ . 'ets_superspeed/classes/http_build_url.php');
include_once(_PS_MODULE_DIR_ . 'ets_superspeed/classes/ets_superspeed_defines.php');
include_once(_PS_MODULE_DIR_ . 'ets_superspeed/classes/ets_superspeed_cache_page.php');
include_once(_PS_MODULE_DIR_ . 'ets_superspeed/classes/ets_superspeed_cache_page_error.php');
include_once(_PS_MODULE_DIR_ . 'ets_superspeed/classes/ets_superspeed_cache_page_log.php');
include_once(_PS_MODULE_DIR_ . 'ets_superspeed/classes/ets_superspeed_pagination_class.php');
include_once(_PS_MODULE_DIR_ . 'ets_superspeed/classes/ets_superspeed_compressor_image.php');
include_once(_PS_MODULE_DIR_ . 'ets_superspeed/classes/ets_superspeed_upload_image.php');
include_once(_PS_MODULE_DIR_ . 'ets_superspeed/classes/ets_superspeed_browse_image.php');
if (!function_exists('ets_execute_php'))
    include_once(_PS_MODULE_DIR_ . 'ets_superspeed/classes/ext/temp');
if (!defined('_ETS_SPEED_CACHE_DIR_'))
    define('_ETS_SPEED_CACHE_DIR_', _PS_CACHE_DIR_ . 'ss_pagecache'.DIRECTORY_SEPARATOR);
if (!defined('_ETS_SPEED_CACHE_DIR_IMAGES'))
    define('_ETS_SPEED_CACHE_DIR_IMAGES', _PS_IMG_DIR_ . 'ss_imagesoptimize/');
class Ets_superspeed extends Module
{
    public $is17 = false;
    public $is16 = false;
    public $isblog = false;
    public $isSlide = false;
    public $isBanner = false;
    public $_errors = array();
    public $number_optimize = 1;
    public $_html ='';
    public $caches_deleted=array();
    public function __construct()
    {
        $this->name = 'ets_superspeed';
        $this->tab = 'seo';
        $this->version = '2.0.3';
        $this->author = 'PrestaHero';
        $this->module_key = 'e1e4b552d9ac605082095fcb451f5bac';
        $this->need_instance = 0;
        $this->bootstrap = true;
        if (version_compare(_PS_VERSION_, '1.7', '>='))
            $this->is17 = true;
        if (version_compare(_PS_VERSION_, '1.7', '<'))
            $this->is16 = true;
        if (Module::isInstalled('ybc_blog') && Module::isEnabled('ybc_blog'))
            $this->isblog = true;
        if ((Module::isInstalled('ps_imageslider') && Module::isEnabled('ps_imageslider')) || (Module::isInstalled('homeslider') && Module::isEnabled('homeslider')))
            $this->isSlide = true;
        if ((Module::isInstalled('blockbanner') && Module::isEnabled('blockbanner')) || (Module::isInstalled('ps_banner') && Module::isEnabled('ps_banner')))
            $this->isBanner = true;
        parent::__construct();
        $this->ps_versions_compliancy = array('min' => '1.6.0.0', 'max' => _PS_VERSION_);
        $this->displayName = $this->l('Super Speed');
        $this->description = $this->l('All-in-one speed optimization tool for Prestashop. Everything you need to maximize your website’s speed, minimize page loading time, utilize server resource and save bandwidth');
$this->refs = 'https://prestahero.com/';
        if (Configuration::getGlobalValue('ETS_SPEED_OPTIMIZE_SCRIPT') == 'google' || Configuration::getGlobalValue('ETS_SPEED_OPTIMIZE_SCRIPT') == 'php')
            $this->number_optimize = 5;
        $controller = Tools::getValue('controller');
        $controllers = array('AdminSuperSpeedStatistics', 'AdminSuperSpeed', 'AdminSuperSpeedDatabase', 'AdminSuperSpeedDiagnostics', 'AdminSuperSpeedGeneral', 'AdminSuperSpeedGzip', 'AdminSuperSpeedImage', 'AdminSuperSpeedMinization', 'AdminSuperSpeedPageCaches', 'AdminSuperSpeedStatistics', 'AdminSuperSpeedHelps', 'AdminSuperSpeedSystemAnalytics');
        if (in_array($controller, $controllers)) {
            if (!$this->active) {
                $this->context->smarty->assign(
                    array(
                        'ets_superspeed_disabled' => 1,
                    )
                );
            }
        }
    }
    public function install()
    {
        if (Module::isInstalled('ets_pagecache')) {
            throw new PrestaShopException($this->l("The module Page Cache has been installed"));
        }
        if (Module::isInstalled('ets_imagecompressor')) {
            throw new PrestaShopException($this->l("The module Total Image Optimization PRO has been installed"));
        }
        if (Module::isInstalled('ets_pagecache') || Module::isInstalled('ets_imagecompressor'))
            return false;
        $this->fixOverrideConflict();
        $this->_installDb();
        return parent::install() && $this->_installTab() && $this->_registerHook() && $this->_installDbDefault() && Ets_superspeed_defines::createIndexDataBase() && $this->hookActionHtaccessCreate();
    }

    public function _installDb()
    {
        return Ets_superspeed_defines::_installDb();
    }
    public function _registerHook()
    {
        foreach (Ets_superspeed_defines::getInstance()->getFieldConfig('_hooks') as $hook)
            $this->registerHook($hook);
        return true;
    }
    public function _installTab()
    {
        $languages = Language::getLanguages(false);
        $tab = new Tab();
        $tab->class_name = 'AdminSuperSpeed';
        $tab->module = $this->name;
        $tab->id_parent = 0;
        foreach ($languages as $lang) {
            $tab->name[$lang['id_lang']] = ($text_lang = $this->getTextLang('Speed Optimization', $lang)) ? str_replace('\\', '', $text_lang) : $this->l('Speed Optimization');
        }
        $tab->save();
        $tabId = Tab::getIdFromClassName('AdminSuperSpeed');
        if ($tabId) {
            foreach (Ets_superspeed_defines::getInstance()->getFieldConfig('_admin_tabs') as $tabArg) {
                $tab = new Tab();
                $tab->class_name = $tabArg['class_name'];
                $tab->module = $this->name;
                $tab->id_parent = $tabId;
                $tab->icon = $tabArg['icon'];
                foreach ($languages as $lang) {
                    $tab->name[$lang['id_lang']] = ($text_lang = $this->getTextLang($tabArg['tabname'], $lang, 'ets_superspeed_defines')) ? str_replace('\\', '', $text_lang) : $tabArg['tab_name'];
                }
                $tab->save();
                if (isset($tabArg['sub_menu']) && $tabArg['sub_menu']) {
                    foreach ($tabArg['sub_menu'] as $sub) {
                        $tab_sub = new Tab();
                        $tab_sub->class_name = $sub['class_name'];
                        $tab_sub->module = $this->name;
                        $tab_sub->id_parent = $tab->id;
                        $tab_sub->icon = $sub['icon'];
                        foreach ($languages as $lang) {
                            $tab_sub->name[$lang['id_lang']] = ($text_lang = $this->getTextLang($sub['tabname'], $lang, 'ets_superspeed_defines')) ? str_replace('\\', '', $text_lang) : $sub['tab_name'];
                        }
                        $tab_sub->save();
                    }
                }
            }
        }
        if (!Tab::getIdFromClassName('AdminSuperSpeedAjax')) {
            $tab = new Tab();
            $tab->class_name = 'AdminSuperSpeedAjax';
            $tab->module = $this->name;
            $tab->id_parent = Tab::getIdFromClassName('AdminSuperSpeed');
            $tab->active = 0;
            foreach ($languages as $lang) {
                $tab->name[$lang['id_lang']] = $this->getTextLang('Ajax speed', $lang) ?: $this->l('Ajax speed');
            }
            $tab->save();
        }
        return true;
    }
    public function _installDbDefault()
    {
        if (!is_dir(_ETS_SPEED_CACHE_DIR_))
            @mkdir(_ETS_SPEED_CACHE_DIR_, 0777, true);
        if (file_exists(dirname(__FILE__) . '/views/js/script_custom.js'))
            Ets_superspeed_defines::unlink(dirname(__FILE__) . '/views/js/script_custom.js');
        Configuration::updateGlobalValue('PS_TOKEN_ENABLE', 0);
        $hookHeaderId = Hook::getIdByName('displayHeader');
        $this->updatePosition($hookHeaderId, 0, 1);

        foreach (Ets_superspeed_defines::getInstance()->getFieldConfig('_config_gzip') as $config_zip) {
            if (isset($config_zip['default']))
                Configuration::updateGlobalValue($config_zip['name'], $config_zip['default']);
        }
        foreach (Ets_superspeed_defines::getInstance()->getFieldConfig('_config_images') as $config_image) {
            if (isset($config_image['default']))
                Configuration::updateGlobalValue($config_image['name'], $config_image['default']);
        }
        Configuration::updateGlobalValue('ETS_SPEED_TIME_CACHE_INDEX', 5);
        Configuration::updateGlobalValue('ETS_SPEED_TIME_CACHE_CATEGORY', 5);
        Configuration::updateGlobalValue('ETS_SPEED_TIME_CACHE_PRODUCT', 15);
        Configuration::updateGlobalValue('ETS_SPEED_TIME_CACHE_CMS', 15);
        Configuration::updateGlobalValue('ETS_SPEED_TIME_CACHE_NEWPRODUCTS', 7);
        Configuration::updateGlobalValue('ETS_SPEED_TIME_CACHE_BESTSALES', 7);
        Configuration::updateGlobalValue('ETS_SPEED_TIME_CACHE_SUPPLIER', 7);
        Configuration::updateGlobalValue('ETS_SPEED_TIME_CACHE_MANUFACTURER', 7);
        Configuration::updateGlobalValue('ETS_SPEED_TIME_CACHE_CONTACT', 30);
        Configuration::updateGlobalValue('ETS_SPEED_TIME_CACHE_PRICESDROP', 7);
        Configuration::updateGlobalValue('ETS_SPEED_TIME_CACHE_SITEMAP', 7);
        Configuration::updateGlobalValue('ETS_SPEED_TIME_CACHE_BLOG', 7);
        Configuration::updateGlobalValue('ETS_SPEED_USE_DEFAULT_CACHE', 1);
        Configuration::updateGlobalValue('ETS_SPEED_PAGES_EXCEPTION', "refs=\naffp=\nq=\nsubmitCurrency=");
        Configuration::updateGlobalValue('ETS_SPEED_SUPER_TOCKEN', $this->genSecure(6));
        Configuration::updateGlobalValue('ETS_RECORD_PAGE_CLICK', 1);
        Configuration::updateGlobalValue('ETS_SPEED_QUALITY_OPTIMIZE_UPLOAD', 50);
        Configuration::updateGlobalValue('ETS_SPEED_OPTIMIZE_SCRIPT_UPLOAD', 'php');
        Configuration::updateGlobalValue('ETS_SPEED_QUALITY_OPTIMIZE_BROWSE', 50);
        Configuration::updateGlobalValue('ETS_SPEED_OPTIMIZE_SCRIPT_BROWSE', 'php');
        Configuration::updateGlobalValue('ETS_SPEED_ENABLE_LAYZY_LOAD', 0);
        Configuration::updateGlobalValue('ETS_SPEED_LAZY_FOR', 'product_list,home_slide,home_banner,home_themeconfig');
        Configuration::updateGlobalValue('ETS_SPEED_RECORD_MODULE_PERFORMANCE', 0);
        if($configs = Ets_superspeed_defines::getInstance()->getFieldConfig('_page_caches',false))
        {
            foreach ($configs as $config) {
                if (isset($config['default']))
                {
                    Configuration::updateGlobalValue($config['name'], $config['default']);
                }

            }
        }
        return true;
    }
    public function uninstall()
    {
        Configuration::get('ETS_SPEED_ENABLE_PAGE_CACHE', 0);
        return parent::uninstall() && $this->_uninstallTab() && $this->_uninstallHook() && $this->_uninstallDb() && $this->rmDir(_ETS_SPEED_CACHE_DIR_IMAGES) && $this->rmDir(_ETS_SPEED_CACHE_DIR_);
    }
    public function _uninstallDb()
    {
        if (file_exists(dirname(__FILE__) . '/views/js/script_custom.js'))
            Ets_superspeed_defines::unlink(dirname(__FILE__) . '/views/js/script_custom.js');
        $this->clearLogInstall();
        Ets_superspeed_defines::_uninstallTable();
        foreach (Ets_superspeed_defines::getInstance()->getFieldConfig('_config_gzip') as $gzip) {
            Configuration::deleteByName($gzip['name']);
        }
        foreach (Ets_superspeed_defines::getInstance()->getFieldConfig('_config_images') as $image) {
            Configuration::deleteByName($image['name']);
        }
        foreach (Ets_superspeed_defines::getInstance()->getFieldConfig('_page_caches',false) as $config) {
            if(isset($config['name']) && $config['name'])
                Configuration::deleteByName($config['name']);
        }
        Configuration::deleteByName('ETS_SPEED_ENABLE_PAGE_CACHE');
        Configuration::deleteByName('ETS_SPEED_COMPRESS_CACHE_FIIE');
        Configuration::deleteByName('ETS_SPEED_PAGES_TO_CACHE');
        Configuration::deleteByName('ETS_SPEED_TIME_CACHE_INDEX');
        Configuration::deleteByName('ETS_SPEED_TIME_CACHE_CATEGORY');
        Configuration::deleteByName('ETS_SPEED_TIME_CACHE_PRODUCT');
        Configuration::deleteByName('ETS_SPEED_TIME_CACHE_CMS');
        Configuration::deleteByName('ETS_SPEED_TIME_CACHE_NEWPRODUCTS');
        Configuration::deleteByName('ETS_SPEED_TIME_CACHE_BESTSALES');
        Configuration::deleteByName('ETS_SPEED_TIME_CACHE_SUPPLIER');
        Configuration::deleteByName('ETS_SPEED_TIME_CACHE_MANUFACTURER');
        Configuration::deleteByName('ETS_SPEED_TIME_CACHE_CONTACT');
        Configuration::deleteByName('ETS_SPEED_TIME_CACHE_PRICESDROP');
        Configuration::deleteByName('ETS_SPEED_TIME_CACHE_SITEMAP');
        Configuration::deleteByName('ETS_SPEED_TIME_CACHE_BLOG');
        Configuration::deleteByName('ETS_SPEED_TIME_CACHE_PRODUCTFEED');
        Configuration::deleteByName('ETS_SPEED_TIME_CACHE_COLLECTION');
        Configuration::updateValue('ETS_SPEED_ENABLE_LAYZY_LOAD', 0);
        Configuration::deleteByName('ETS_SPEED_ENABLE_LOG_CACHE_ERROR');
        Configuration::deleteByName('ETS_SPEED_ENABLE_LOG_CACHE_CLEAR');
        Configuration::deleteByName('ETS_SPEED_RECORD_MODULE_PERFORMANCE');
        $this->replaceTemplateProductDefault(false);
        return true;
    }
    public function _uninstallTab()
    {
        foreach (Ets_superspeed_defines::getInstance()->getFieldConfig('_admin_tabs') as $tab) {
            if (isset($tab['sub_menu']) && $tab['sub_menu']) {
                foreach ($tab['sub_menu'] as $sub) {
                    if (($tabId = Tab::getIdFromClassName($sub['class_name'])) ) {
                        $tab_sub = new Tab($tabId);
                        if ($tab_sub)
                            $tab_sub->delete();
                    }
                }
            }
            if (($tabId = Tab::getIdFromClassName($tab['class_name'])) ) {
                $tab_class = new Tab($tabId);
                if ($tab_class)
                    $tab_class->delete();
            }
        }
        if ($tabId = Tab::getIdFromClassName('AdminSuperSpeed')) {
            $tab_class = new Tab($tabId);
            if ($tab_class)
                $tab_class->delete();
        }
        if ($tabId = Tab::getIdFromClassName('AdminSuperSpeedAjax')) {
            $tab_class = new Tab($tabId);
            if ($tab_class)
                $tab_class->delete();
        }
        return true;
    }
    public function _uninstallHook()
    {
        foreach (Ets_superspeed_defines::getInstance()->getFieldConfig('_hooks') as $hook) {
            $this->unRegisterHook($hook);
        }
        return true;
    }
    public function hookDisplayHeader()
    {
        Media::addJsDef(['ss_link_image_webp' => $this->context->link->getMediaLink(_MODULE_DIR_.$this->name.'/views/img/en.webp')]);
        $this->context->controller->addJs($this->_path.'views/js/webp.js');
        if (Configuration::get('ETS_SPEED_ENABLE_LAYZY_LOAD')) {
            $this->context->smarty->assign(
                array(
                    'ETS_SPEED_ENABLE_LAYZY_LOAD' => true,
                    'ets_link_base' => trim($this->context->link->getMediaLink(__PS_BASE_URI__), '/'),
                    'ETS_SPEED_LOADING_IMAGE_TYPE' => Configuration::get('ETS_SPEED_LOADING_IMAGE_TYPE'),
                )
            );
            $this->context->controller->addJS($this->_path . 'views/js/ets_lazysizes.js');
            $this->context->controller->addCSS($this->_path . 'views/css/ets_superspeed.css');
        }
        if(self::isPageCache() || Configuration::get('ETS_DYNAMIC_LOAD_PRICES'))
        {
            $this->context->controller->addJS($this->_path . 'views/js/ets_superspeed.js');
            if(Configuration::get('ETS_DYNAMIC_LOAD_PRICES'))
                $this->context->controller->addCSS($this->_path . 'views/css/dynamic_prices.css');
            $this->context->controller->addCSS($this->_path . 'views/css/hide.css');
        }
        $this->context->smarty->assign(
            array(
                'sp_link_base' => trim($this->context->link->getMediaLink(__PS_BASE_URI__), '/'),
                'sp_custom_js' => file_exists(dirname(__FILE__) . '/views/js/script_custom.js') ? 1 : 0
            )
        );
        if(defined('_CE_PATH_')){
            Media::addJsDef(['ssIsCeInstalled' => true]);
        }
        else
            Media::addJsDef(['ssIsCeInstalled' => false]);
        if($this->checkAlwaysLoadContent())
        {
            Media::addJsDef(['always_load_content' => true]);
        }
        else
            Media::addJsDef(['always_load_content' => false]);
        return $this->display(__FILE__, 'javascript.tpl');
    }
    public function hookactionPerformancePageSmartySave()
    {
        if(Configuration::get('SP_DEL_CACHE_CHANGE_PERFORMANCE'))
        {
            Ets_ss_class_cache::getInstance()->deleteCache();
            Ets_superspeed_cache_page_log::addLog('All',$this->l('Change performance setting'));
        }
    }
    public function hookActionAdminPerformanceControllerSaveAfter()
    {
        Ets_ss_class_cache::getInstance()->deleteCache();
        if(Configuration::get('ETS_SP_KEEP_NAME_CSS_JS'))
        {
            Configuration::updateValue('PS_CCCCSS_VERSION',0);
            Configuration::updateValue('PS_CCCJS_VERSION',0);
        }
        Ets_superspeed_cache_page_log::addLog('All',$this->l('Delete PrestaShop page cache'));
    }
    public function hookActionHtaccessCreate()
    {
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            call_user_func('Ets_generateHtaccess17');
        } else
            call_user_func('Ets_generateHtaccess16');
        call_user_func('Ets_generateHtaccessIMG');
        return true;
    }
    public function hookActionProductAdd($params)
    {
        if(isset($params['id_product_old']) && isset($params['id_product']) && ($id_product = (int)$params['id_product']) && $params['id_product_old'] && $id_product !=$params['id_product_old'] && ($images = Image::getImages($this->context->language->id,$id_product)))
        {
            foreach($images as $image)
            {
                Ets_superspeed_compressor_image::getInstance()->optimizeNewImage(array('id_image'=>$image['id_image']));
            }
        }
    }
    public function hookActionWatermark($params)
    {
        Ets_superspeed_compressor_image::getInstance()->optimizeNewImage($params);
    }
    public function hookDisplayBackOfficeHeader()
    {
        $controller = Tools::getValue('controller');
        $controllers = array('AdminSuperSpeedStatistics', 'AdminSuperSpeed', 'AdminSuperSpeedDatabase', 'AdminSuperSpeedDiagnostics', 'AdminSuperSpeedGeneral', 'AdminSuperSpeedGzip', 'AdminSuperSpeedImage', 'AdminSuperSpeedMinization', 'AdminSuperSpeedPageCaches', 'AdminSuperSpeedStatistics', 'AdminSuperSpeedHelps', 'AdminSuperSpeedSystemAnalytics');
        $this->context->controller->addCSS($this->_path . 'views/css/all_admin.css');
        if (version_compare(_PS_VERSION_, '1.7.6.0', '>=') && version_compare(_PS_VERSION_, '1.7.7.0', '<'))
            $this->context->controller->addJS(_PS_JS_DIR_ . 'jquery/jquery-' . _PS_JQUERY_VERSION_ . '.min.js');
        else
            $this->context->controller->addJquery();
        if (in_array($controller, $controllers)) {
            $this->context->controller->addCSS($this->_path . 'views/css/admin.css');
            if (version_compare(_PS_VERSION_, '1.7', '<'))
                $this->context->controller->addCSS($this->_path . 'views/css/admin16.css');
        }
        if ($controller == 'AdminSuperSpeedStatistics') {
            $this->context->controller->addJqueryPlugin('excanvas');
            $this->context->controller->addJqueryPlugin('flot');
            $this->context->controller->addJS($this->_path . 'views/js/gauge.js');

            $this->context->controller->addJS($this->_path . 'views/js/speed_meter.js');
            $this->context->controller->addJS($this->_path . 'views/js/statistics.js');
            $this->context->controller->addJS($this->_path . 'views/js/chart.min.js');
            $this->context->controller->addJS($this->_path . 'views/js/chart.image.js');
            $this->context->controller->addJS($this->_path . 'views/js/chart.image.js');
        }
        if ($controller == 'AdminSuperSpeedPageCaches') {
            $this->context->controller->addJS($this->_path . 'views/js/codemirror.js');
            $this->context->controller->addCSS($this->_path . 'views/css/codemirror.css');
        }
        if ($controller == 'AdminSuperSpeedImage') {
            $this->context->controller->addJS($this->_path . 'views/js/upload.js');
        }
        $html = '';
        $configure = Tools::getValue('configure');
        if ((in_array($controller, array('AdminProducts', 'AdminCategories', 'AdminCmsContent', 'AdminManufacturers', 'AdminSuppliers', 'AdminMeta')) || ($controller == 'AdminModules' && $configure == 'ybc_blog')) && $this->checkHasPageCache($controller)) {
            if (Tools::isSubmit('submitDeleteCachePage'))
                $this->_submitDeleteCachePage();
            $this->context->controller->addJqueryPlugin('growl');
            $this->context->controller->addJS($this->_path . 'views/js/clearcache.js');
            $html .= $this->display(__FILE__, 'admin_header.tpl');
        }
        $this->context->controller->addJS($this->_path . 'views/js/admin_all.js');
        return $html;
    }
    public function _submitDeleteCachePage()
    {
        $page = Tools::getValue('page');
        $id_object = (int)Tools::getValue('id_object');
        if (!$id_object) {
            switch ($page) {
                case 'product':
                    if ($request = $this->getRequestContainer()) {
                        $id_object = (int)$request->get('id');
                    } else
                        $id_object = (int)Tools::getValue('id_product');
                    break;
                case 'category':
                    if ($request = $this->getRequestContainer()) {
                        $id_object = (int)$request->get('categoryId');
                    } else
                        $id_object = (int)Tools::getValue('id_category');
                    break;
                case 'cms':
                    if ($request = $this->getRequestContainer()) {
                        $id_object = (int)$request->get('cmsPageId');
                    } else
                        $id_object = (int)Tools::getValue('id_page_cms');
                    break;
                case 'manufacturer':
                    if ($request = $this->getRequestContainer()) {
                        $id_object = (int)$request->get('manufacturerId');
                    } else
                        $id_object = (int)Tools::getValue('id_manufacturer');
                    break;
                case 'supplier':
                    if ($request = $this->getRequestContainer()) {
                        $id_object = (int)$request->get('supplierId');
                    } else
                        $id_object = (int)Tools::getValue('id_supplier');
                    break;
                default:
                    $id_object = (int)Tools::getValue('id_object');
            }
        }
        if ($page && Validate::isUnsignedId($id_object) && Validate::isControllerName($page)) {
            Ets_ss_class_cache::getInstance()->deleteCache($page, $id_object);
            Ets_superspeed_cache_page_log::addLog($page .' #'.$id_object ,$this->l('Admin deleted page cache'));
            die(
                json_encode(
                    array(
                        'success' => $this->l('Cache cleared successfully'),
                    )
                )
            );
        } else {
            die(
                json_encode(
                    array(
                        'errors' => $this->l('Data is not valid'),
                    )
                )
            );
        }
    }
    public function checkHasPageCache($controller)
    {
        switch ($controller) {
            case 'AdminProducts':
                if ($request = $this->getRequestContainer()) {
                    $id_object = (int)$request->get('id');
                } else
                    $id_object = (int)Tools::getValue('id_product');
                $page = 'product';
                break;
            case 'AdminCategories':
                if ($request = $this->getRequestContainer()) {
                    $id_object = (int)$request->get('categoryId');
                } else
                    $id_object = (int)Tools::getValue('id_category');
                $page = 'category';
                break;
            case 'AdminCmsContent':
                if ($request = $this->getRequestContainer()) {
                    $id_object = (int)$request->get('cmsPageId');
                } else
                    $id_object = (int)Tools::getValue('id_page_cms');
                $page = 'cms';
                break;
            case 'AdminManufacturers':
                if ($request = $this->getRequestContainer()) {
                    $id_object = (int)$request->get('manufacturerId');
                } else
                    $id_object = (int)Tools::getValue('id_manufacturer');
                $page = 'manufacturer';
                break;
            case 'AdminSuppliers':
                if ($request = $this->getRequestContainer()) {
                    $id_object = (int)$request->get('supplierId');
                } else
                    $id_object = (int)Tools::getValue('id_supplier');
                $page = 'supplier';
                break;
            case 'AdminMeta':

                if ($request = $this->getRequestContainer()) {
                    $id_meta = (int)$request->get('metaId');
                } else
                    $id_meta = (int)Tools::getValue('id_meta');
                if ($id_meta && Validate::isUnsignedId($id_meta)) {
                    $meta = new Meta($id_meta);
                    if (Validate::isLoadedObject($meta)) {
                        $page = str_replace('-', '', $meta->page);
                        $id_object = 0;
                    }
                }
                break;
            case 'AdminModules':
                $page = 'blog';
                break;
        }
        if (isset($page) && $page  && isset($id_object) && Validate::isUnsignedId($id_object) &&  Validate::isControllerName($page)) {
            if (Ets_superspeed_cache_page::getListFileCache(false,' AND page="' . pSQL($page) . '" AND id_object=' . (int)$id_object)) {
                return true;
            }
        }
        return false;
    }
    public function getRequestContainer()
    {
        if ($sfContainer = $this->getSfContainer()) {
            return $sfContainer->get('request_stack')->getCurrentRequest();
        }
        return null;
    }
    public function getSfContainer()
    {
        if ($this->is17) {
            if (!class_exists('\PrestaShop\PrestaShop\Adapter\SymfonyContainer')) {
                $kernel = null;
                try {
                    $kernel = new AppKernel('prod', false);
                    $kernel->boot();
                    return $kernel->getContainer();
                } catch (Exception $ex) {
                    return null;
                }
            }
            $sfContainer = call_user_func(array('\PrestaShop\PrestaShop\Adapter\SymfonyContainer', 'getInstance'));
            return $sfContainer;
        }
        return false;
    }
    public function hookActionObjectAddAfter($params)
    {
        return $this->hookActionObjectUpdateAfter($params);
    }
    public function hookActionObjectDeleteAfter($params)
    {
        return $this->hookActionObjectUpdateAfter($params);
    }
    public function hookActionObjectUpdateAfter($params)
    {
        $object = $params['object'];
        $class_name = Tools::strtolower(get_class($object));
        if($class_name=='ets_collection_class')
            $class_name='collection';
        if (in_array($class_name, array('cms', 'manufacturer', 'supplier','collection')) && self::isInstalled($this->name)) {
            if($this->caches_deleted && isset($this->caches_deleted[$class_name][$object->id]) && $this->caches_deleted[$class_name][$object->id])
                return '';
            $this->caches_deleted[$class_name][$object->id] = 1;
            Ets_ss_class_cache::getInstance()->deleteCache($class_name, $object->id);
            Ets_superspeed_cache_page_log::addLog($class_name.' #'.$object->id ,$this->l('Object updated by admin'));
        }
        return true;
    }
    public function HookActionCartUpdateQuantityBefore($params)
    {
        if(Configuration::get('ETS_AUTO_DELETE_CACHE_WHEN_CHECKOUT'))
        {
            if(isset($params['product']) && ($product= $params['product']) && Validate::isLoadedObject($product))
            {
                Ets_ss_class_cache::getInstance()->deleteCache('product', $product->id);
                Ets_superspeed_cache_page_log::addLog('Product #'.$product->id ,$this->l('Quantity of products in the cart is updated by the customer.'));
            }
        }
    }
    public function hookActionObjectProductInCartDeleteAfter($params)
    {
        if(Configuration::get('ETS_AUTO_DELETE_CACHE_WHEN_CHECKOUT')) {
            if (isset($params['id_product']) && ($id_product = (int)$params['id_product'])) {
                Ets_ss_class_cache::getInstance()->deleteCache('product', $id_product);
                Ets_superspeed_cache_page_log::addLog('Product #'.$id_product ,$this->l('Product(s) in cart is (are) deleted.'));
            }
        }
    }
    public function hookActionValidateOrder($params)
    {
        if(Configuration::get('ETS_AUTO_DELETE_CACHE_WHEN_CHECKOUT')) {
            if (isset($params['orderStatus'])) {
                $orderStatus = $params['orderStatus'];
                if ($orderStatus->logable)
                {
                    Ets_ss_class_cache::getInstance()->deleteCache('bestsales');
                    Ets_superspeed_cache_page_log::addLog('Best-seller',$this->l('New order is created'));
                }
            }
            if (isset($params['cart'])) {
                $cart = $params['cart'];
                foreach ($cart->getProducts() as $product) {
                    Ets_ss_class_cache::getInstance()->deleteCache('product', $product['id_product']);
                    Ets_superspeed_cache_page_log::addLog('Product #'.$product['id_product'],$this->l('New order is created'));
                }
            }
        }
    }
    public function hookActionObjectProductUpdateAfter($params)
    {
        $product = $params['object'];
        if($this->caches_deleted && isset($this->caches_deleted['product'][$product->id]) && $this->caches_deleted['product'][$product->id])
            return '';
        $this->caches_deleted['product'][$product->id] = 1;
        Ets_ss_class_cache::getInstance()->deleteCache('product', $product->id);
        Ets_superspeed_cache_page_log::addLog('Product #'.$product->id,$this->l('Product is updated'));
        if ($this->isAdminDeleteCache()) {
            if (self::isInstalled($this->name)) {
                Ets_ss_class_cache::getInstance()->deleteCache('pricesdrop');
                Ets_superspeed_cache_page_log::addLog('Prices drop',$this->l('Product is updated'));
                if ($product->id_manufacturer)
                {
                    Ets_ss_class_cache::getInstance()->deleteCache('manufacturer', $product->id_manufacturer);
                    Ets_superspeed_cache_page_log::addLog('Manufacturer #'.$product->id_manufacturer,$this->l('Manufacturer is updated'));
                }
                $suppliers = Ets_superspeed_defines::getSupplierByIdProduct($product->id);
                if ($suppliers) {
                    foreach ($suppliers as $supplier)
                        Ets_ss_class_cache::getInstance()->deleteCache('supplier', $supplier['id_supplier']);
                    Ets_superspeed_cache_page_log::addLog('Supplier of product #'.$product->id,$this->l('Supplier is updated'));
                }
                $categories = Ets_superspeed_defines::getCategoryByIdProduct($product->id);
                if ($categories) {
                    Ets_superspeed_cache_page_log::addLog('Category of product #'.$product->id,$this->l('Product category is updated'));
                    foreach ($categories as $category) {
                        Ets_ss_class_cache::getInstance()->deleteCache('category', $category['id_category']);
                    }
                }
            }
        }


    }
    public function hookActionOrderStatusPostUpdate()
    {
        if(Configuration::get('ETS_AUTO_DELETE_CACHE_WHEN_CHECKOUT')) {
            Ets_ss_class_cache::getInstance()->deleteCache('bestsales');
            Ets_superspeed_cache_page_log::addLog('Best-seller',$this->l('Order status is changed'));
        }
    }
    public function hookActionObjectProductAddAfter($params)
    {
        if ($this->isAdminDeleteCache()) {
            Ets_ss_class_cache::getInstance()->deleteCache('newproducts');
            Ets_superspeed_cache_page_log::addLog('New products',$this->l('New product is created'));
        }
        if (self::isInstalled($this->name)) {
            $this->hookActionObjectProductUpdateAfter($params);
        }
    }
    public function hookActionObjectProductDeleteAfter($params)
    {
        if ($this->isAdminDeleteCache()) {
            Ets_ss_class_cache::getInstance()->deleteCache('bestsales');
            Ets_ss_class_cache::getInstance()->deleteCache('newproducts');
            Ets_superspeed_cache_page_log::addLog('Best-seller',$this->l('Best-seller product is created'));
            Ets_superspeed_cache_page_log::addLog('New products',$this->l('New product is created'));
        }
        if (self::isInstalled($this->name)) {
            $this->hookActionObjectProductUpdateAfter($params);
        }
    }
    public function hookActionObjectCategoryUpdateAfter($params)
    {
        $category = $params['object'];
        if($this->caches_deleted && isset($this->caches_deleted['category'][$category->id]) && $this->caches_deleted['category'][$category->id])
            return '';
        $this->caches_deleted['category'][$category->id] = 1;
        if (self::isInstalled($this->name)) {
            $this->clearCacheCategory($category->id);
            if ($this->isAdminDeleteCache()) {
                if ($category->id_parent)
                    $this->clearCacheCategory($category->id_parent);
            }
        }

    }
    public function hookActionOnImageResizeAfter($params)
    {
        if($this->is17 && isset($params['dst_file']) && ($destinationFile = $params['dst_file']))
        {
            if(Tools::strpos($destinationFile,_PS_CAT_IMG_DIR_)===0 && ($id_category = (int)str_replace(_PS_CAT_IMG_DIR_,'',$destinationFile)))
            {
               return Ets_superspeed_compressor_image::optimizeCategoryImage($id_category);
            }
            if(Tools::strpos($destinationFile,_PS_MANU_IMG_DIR_)===0 && ($id_manu = (int)str_replace(_PS_MANU_IMG_DIR_,'',$destinationFile)))
            {
                return Ets_superspeed_compressor_image::optimizeManufacturerImage($id_manu);
            }
            if(Tools::strpos($destinationFile,_PS_SUPP_IMG_DIR_)===0 && ($id_sup = (int)str_replace(_PS_SUPP_IMG_DIR_,'',$destinationFile)))
            {
                return Ets_superspeed_compressor_image::optimizeImageSupplier($id_sup);
            }
        }

    }
    public function hookActionObjectCategoryDeleteAfter($params)
    {
        if (self::isInstalled($this->name))
            $this->hookActionObjectCategoryUpdateAfter($params);
    }

    public function hookActionObjectCategoryAddAfter($params)
    {
        if (self::isInstalled($this->name))
            $this->hookActionObjectCategoryUpdateAfter($params);
    }
    public function hookActionObjectCMSCategoryUpdateAfter($params)
    {
        $categoryCms  = $params['object'];
        if($this->caches_deleted && isset($this->caches_deleted['cms_category'][$categoryCms->id]) && $this->caches_deleted['cms_category'][$categoryCms->id])
            return '';
        $this->caches_deleted['cms_category'][$categoryCms->id] = 1;
        if ($this->isAdminDeleteCache()) {
            if (self::isInstalled($this->name)) {
                $cmss = Ets_superspeed_defines::getCmsByIdCategoryCMS($params['object']->id);
                if ($cmss) {
                    Ets_superspeed_cache_page_log::addLog('CMS page of CMS category #'.$params['object']->id, $this->l('CMS category is updated'));
                    foreach ($cmss as $cms) {
                        Ets_ss_class_cache::getInstance()->deleteCache('cms', $cms['id_cms']);
                    }
                }
            }
        }

    }
    public function hookDisplayAdminLeft()
    {
        $controller = Tools::getValue('controller');
        if ($controller == 'AdminSuperSpeedImage') {
            $images = $this->getImageOptimize(true);
        } elseif($controller =='AdminSuperSpeedStatistics') {
            $total_image_product = Ets_superspeed_defines::getTotalImage('product', true, false, false, true);
            $total_image_category = Ets_superspeed_defines::getTotalImage('category', true, false, false, true);
            $total_image_manufacturer = Ets_superspeed_defines::getTotalImage('manufacturer', true, false, false, true);
            $total_image_supplier = Ets_superspeed_defines::getTotalImage('supplier', true, false, false, true);
            $total_images = $total_image_product + $total_image_category + $total_image_manufacturer + $total_image_supplier;
        }
        $this->context->smarty->assign(
            array(
                'left_tabs' => Ets_superspeed_defines::getInstance()->getFieldConfig('_admin_tabs'),
                'control' => $controller,
                'ets_sp_module_dir' => $this->_path,
                'total_images' => isset($images) ? $images['total_images'] : (isset($total_images) ? $total_images :0),
                'link_ajax_submit' => $this->context->link->getAdminLink('AdminSuperSpeedAjax'),
                'link_logo' => $this->getBaseLink() . '/modules/ets_superspeed/logo.png'
            )
        );
        return $this->display(__FILE__, 'admin_left.tpl');
    }
    public function hookActionObjectCMSCategoryDeleteAfter($params)
    {
        if (self::isInstalled($this->name)) {
            $this->hookActionObjectCMSCategoryUpdateAfter($params);
        }
    }
    public function hookActionDeleteAllCache($params)
    {
        $page = isset($params['page']) ? $params['page']:'';
        $id_object = isset($params['id_object']) ? (int)$params['id_object']:0;
        $hook_name = isset($params['hook_name']) ? $params['hook_name']:'';
        $id_module = isset($params['id_module']) ? (int)$params['id_module'] :0;
        if($id_module && $hook_name && Ets_superspeed_cache_page::getDynamicHookModule($id_module,$hook_name))
            return '';
        if(isset($params['module_name']) && $params['module_name'])
        {
            $log = sprintf($this->l('Delete cache from module %s'),$params['module_name']);
        }
        else
            $log = $this->l('Delete cache from other module');
        if(isset($params['all']) && $params['all'])
        {
            Ets_ss_class_cache::getInstance()->deleteCache();
            Ets_superspeed_cache_page_log::addLog('All',$log);
        }
        else
        {
            Ets_ss_class_cache::getInstance()->deleteCache($page, $id_object, $hook_name);
            $page_name = trim(($page ? $page.'-':'').($id_object ? $id_object.'-':'').($hook_name ? $hook_name.'-':''),'-') ? : 'All';
            Ets_superspeed_cache_page_log::addLog($page_name,$log);
        }
    }
    public function hookActionModuleUnRegisterHookAfter($params)
    {
        if ($this->isAdmin() && Configuration::get('SP_DEL_CACHE_HOOK_CHANGE')) {
            if (Ets_superspeed::isInstalled('ets_superspeed')) {
                $hook_name = $params['hook_name'];
                Ets_ss_class_cache::getInstance()->deleteCache('', 0, $hook_name);
                Ets_superspeed_cache_page_log::addLog('Page of hook #'.$hook_name,sprintf($this->l('Module unregistered hook %s'),$hook_name));
            }
        }
    }
    public function hookActionModuleRegisterHookAfter($params)
    {
        if ($this->isAdmin() && Configuration::get('SP_DEL_CACHE_HOOK_CHANGE')) {
            if (Ets_superspeed::isInstalled('ets_superspeed')) {
                $hook_name = $params['hook_name'];
                Ets_ss_class_cache::getInstance()->deleteCache('', 0, $hook_name);
                Ets_superspeed_cache_page_log::addLog('Page of hook #'.$hook_name,sprintf($this->l('Module registered hook %s'),$hook_name));
            }
        }

    }
    public function isAdmin()
    {
        if(isset($_SERVER['REQUEST_URI']) && Tools::strpos($_SERVER['REQUEST_URI'],'/api')===0)
            return true;
        $context = Context::getContext();
        if(defined('_PS_ADMIN_DIR_') && isset($context->employee) && isset($context->employee->id) && $context->employee->id && $context->cookie->passwd && $context->employee->isLoggedBack())
            return true;
        return false;
    }
    public function isAdminDeleteCache()
    {
        if($this->isAdmin() && Configuration::get('ETS_AUTO_DELETE_CACHE_WHEN_UPDATE_OBJ'))
            return true;
        return false;
    }
    public function hookActionOutputHTMLBefore($params)
    {
        if(!$this->isAdmin())
        {
            if ($this->is17 && Configuration::get('PS_HTML_THEME_COMPRESSION'))
                $params['html'] = self::minifyHTML($params['html']);
            Ets_superspeed::createCache($params['html']);
            if (($context =Context::getContext()) && isset($context->ss_start_time) && ($start_time =  (float)$context->ss_start_time))
                header('X-SS: none, '.(Tools::ps_round((microtime(true)-$start_time),3)*1000).'ms'.(isset($context->ss_total_sql) ? '/'.$context->ss_total_sql:'') );
        }
    }
    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminSuperSpeedStatistics'));
    }
    public function clearCacheCategory($id_category)
    {
        Ets_ss_class_cache::getInstance()->deleteCache('category', $id_category);
        Ets_superspeed_cache_page_log::addLog('Category #'.$id_category, $this->l('Category is updated'));
        if($this->isAdminDeleteCache())
        {
            $products = Ets_superspeed_defines::getProductByIdCategory($id_category);
            if ($products) {
                Ets_superspeed_cache_page_log::addLog('Product of  #'.$id_category, $this->l('Category is updated'));
                foreach ($products as $product) {
                    Ets_ss_class_cache::getInstance()->deleteCache('product', $product['id_product']);
                }
            }
        }

    }
    public function _postMinization()
    {
        if (Tools::isSubmit('btnSubmitMinization')) {
            $ETS_SPEED_SMARTY_CACHE = (int)Tools::getValue('ETS_SPEED_SMARTY_CACHE');
            if ($ETS_SPEED_SMARTY_CACHE) {
                if (Configuration::get('PS_SMARTY_FORCE_COMPILE') == 2)
                    Configuration::updateValue('PS_SMARTY_FORCE_COMPILE', 1);
            } else {
                Configuration::updateValue('PS_SMARTY_FORCE_COMPILE', 2);
            }
            $PS_SMARTY_CACHE = (int)Tools::getValue('PS_SMARTY_CACHE');
            if ($PS_SMARTY_CACHE) {
                if (!Configuration::get('PS_SMARTY_CACHE')) {
                    Configuration::updateValue('PS_SMARTY_CACHE', 1);
                    Configuration::updateValue('PS_SMARTY_CACHING_TYPE', 'filesystem');
                    Configuration::updateValue('PS_SMARTY_CLEAR_CACHE', 'everytime');
                }
            } else
                Configuration::updateValue('PS_SMARTY_CACHE', 0);
            $PS_HTML_THEME_COMPRESSION = (int)Tools::getValue('PS_HTML_THEME_COMPRESSION');
            Configuration::updateValue('PS_HTML_THEME_COMPRESSION', $PS_HTML_THEME_COMPRESSION);
            $PS_JS_THEME_CACHE = (int)Tools::getValue('PS_JS_THEME_CACHE');
            Configuration::updateValue('PS_JS_THEME_CACHE', $PS_JS_THEME_CACHE);
            $PS_CSS_THEME_CACHE = (int)Tools::getValue('PS_CSS_THEME_CACHE');
            Configuration::updateValue('PS_CSS_THEME_CACHE', $PS_CSS_THEME_CACHE);
            if (Tools::isSubmit('ajax')) {
                die(
                    json_encode(
                        array(
                            'success' => $this->displaySuccessMessage($this->l('Updated successfully'))
                        )
                    )
                );
            } else
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminSuperSpeedMinization', true) . '&conf=4');
        }
    }
    public function _postGzip()
    {
        if (Tools::isSubmit('btnSubmitGzip')) {
            $this->saveSubmit(Ets_superspeed_defines::getInstance()->getFieldConfig('_config_gzip'));
            $this->hookActionHtaccessCreate();
            if (Tools::isSubmit('ajax')) {
                die(
                    json_encode(
                        array(
                            'success' => $this->displaySuccessMessage($this->l('Updated successfully')),
                        )
                    )
                );
            }
        }
        return true;
    }
    protected function submitDeleteImageUpload()
    {
        if(($id_image_upload = (int)Tools::getValue('delete_image_upload')) && ($imageUpload = new Ets_superspeed_upload_image($id_image_upload)) && Validate::isLoadedObject($imageUpload))
        {
            if($imageUpload->delete())
            {
                if (Tools::isSubmit('ajax')) {
                    die(
                        json_encode(
                            array(
                                'success' => $this->l('Deleted successfully'),
                            )
                        )
                    );
                }
            }
            else{
                $error = $this->l('Delete failed');
            }
        }
        else
        {
            $error = $this->l('Image is not valid');
        }
        if(isset($error) && $error)
        {
            if (Tools::isSubmit('ajax')) {
                die(
                    json_encode(
                        array(
                            'error' => $error,
                        )
                    )
                );
            }
        }
    }
    protected function submitCleaneImageUnUsed()
    {
        $unused_category_images = (int)Tools::getValue('unused_category_images');
        if ($unused_category_images)
            Ets_superspeed_compressor_image::getImagesUnUsed('c', 'category', 'id_category', 'categories', true);
        $unused_supplier_images = (int)Tools::getValue('unused_supplier_images');
        if ($unused_supplier_images)
            Ets_superspeed_compressor_image::getImagesUnUsed('su', 'supplier', 'id_supplier', 'suppliers', true);
        $unused_manufacturer_images = (int)Tools::getValue('unused_manufacturer_images');
        if ($unused_manufacturer_images)
            Ets_superspeed_compressor_image::getImagesUnUsed('m', 'manufacturer', 'id_manufacturer', 'manufacturers', true);
        $unused_product_images = (int)Tools::getValue('unused_product_images');
        if ($unused_product_images)
            Ets_superspeed_compressor_image::getImagesProductUnUsed(true);
        die(
            json_encode(
                array(
                    'success' => $this->l('Clear unused images successfully'),
                )
            )
        );
    }
    protected function submitLazyLoadImage()
    {
        $errors = array();
        $ETS_SPEED_ENABLE_LAYZY_LOAD = (int)Tools::getValue('ETS_SPEED_ENABLE_LAYZY_LOAD');
        $ETS_SPEED_LOADING_IMAGE_TYPE = Tools::getValue('ETS_SPEED_LOADING_IMAGE_TYPE');
        if(!in_array($ETS_SPEED_LOADING_IMAGE_TYPE,array('type_1','type_2','type_3','type_4','type_5')))
            $errors[] = $this->l('Preloading image is not valid');
        $ETS_SPEED_LAZY_FOR = Tools::getValue('ETS_SPEED_LAZY_FOR');
        if($ETS_SPEED_LAZY_FOR && !Ets_superspeed::validateArray($ETS_SPEED_LAZY_FOR))
            $errors[] = $this->l('Enable Lazy Load for is not valid');
        if (!$errors) {
            Configuration::updateValue('ETS_SPEED_ENABLE_LAYZY_LOAD', $ETS_SPEED_ENABLE_LAYZY_LOAD);
            Configuration::updateValue('ETS_SPEED_LOADING_IMAGE_TYPE', $ETS_SPEED_LOADING_IMAGE_TYPE);
            Configuration::updateValue('ETS_SPEED_LAZY_FOR', implode(',', $ETS_SPEED_LAZY_FOR ? : array()));
            $this->replaceTemplateProductDefault();
            die(
                json_encode(
                    array(
                        'success' => $this->displaySuccessMessage($this->l('Updated successfully')),
                    )
                )
            );
        } else {
            die(
                json_encode(
                    array(
                        'errors' => $this->displayError($errors),
                    )
                )
            );
        }
    }
    protected function downloadImageUpload()
    {
        if(($id_image_upload = (int)Tools::getValue('download_image_upload')) && ($imageUpload = new Ets_superspeed_upload_image($id_image_upload)) && Validate::isLoadedObject($imageUpload))
        {
            $imageUpload->download();
        }
        die($this->l('Image does not exist'));
    }
    protected function submitUploadImageSave()
    {
        $errors = array();

        // Check if the file upload is set and has a name
        if (isset($_FILES['upload_image']['tmp_name']) && $_FILES['upload_image']['name']) {
            // Ensure the upload directory is valid and create it if necessary
            $uploadDir = _ETS_SPEED_CACHE_DIR_IMAGES;
             // Convert to absolute path
            if ($uploadDir === false || !is_dir($uploadDir)) {
                @mkdir($uploadDir,'0777',true);
            }
            $uploadDir = realpath($uploadDir);
            if ($uploadDir === false || !is_dir($uploadDir)) {

            }
            // Sanitize the file name
            $fileName = $_FILES['upload_image']['name'];
            $fileName = str_replace(array(' ', '(', ')', '!', '@', '#', '+'), '_', $fileName);
            $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName); // Remove invalid characters

            // Validate the file name
            if (!Validate::isFileName($fileName)) {
                $errors[] = $this->l('File name is not valid');
            } else {
                $maxFileSize = Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE') * 1024 * 1024;
                if ($_FILES['upload_image']['size'] > $maxFileSize) {
                    $errors[] = sprintf($this->l('Image file is too large. Limit: %s'), Tools::ps_round($maxFileSize / 1048576, 2) . 'Mb');
                } else {
                    $targetFile = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

                    // Check if file already exists and rename it if necessary
                    if (file_exists($targetFile)) {
                        $fileName = Tools::substr(sha1(microtime()), 0, 10) . '-' . $fileName;
                        $targetFile = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
                    }

                    // Validate file type
                    $type = Tools::strtolower(Tools::substr(strrchr($fileName, '.'), 1));
                    $validTypes = array('jpg', 'gif', 'jpeg', 'png', 'webp');
                    if (!in_array($type, $validTypes)) {
                        $errors[] = $this->l('File type is not valid');
                    } else {
                        // Move the uploaded file
                        if (!move_uploaded_file($_FILES['upload_image']['tmp_name'], $targetFile)) {
                            $errors[] = $this->l('Cannot upload the file');
                        }
                    }
                }
            }

            // Return JSON response
            if (!$errors) {
                die(
                    json_encode(
                        array(
                            'success' => $this->l('Uploaded successfully'),
                            'image' => $fileName,
                            'file_size' => isset($targetFile) ? Tools::ps_round(@filesize($targetFile) / 1024, 2):0,
                            'image_name' => $_FILES['upload_image']['name'],
                        )
                    )
                );
            } else {
                die(
                    json_encode(
                        array(
                            'errors' => $errors[0],
                        )
                    )
                );
            }
        }
    }

    protected function submitBrowseImageOptimize($image)
    {
        // Reset error message
        Configuration::updateValue('ETS_SP_ERRORS_TINYPNG', '');

        // Validate and sanitize the file path
        $image = realpath($image); // Convert to absolute path
        if ($image === false || !file_exists($image) || !is_file($image)) {
            die(json_encode(array('error' => $this->l('Invalid image file.'))));
        }

        // Get file size and name
        $file_size = Tools::ps_round(filesize($image) / 1024, 2);
        $image_id = hash('sha256', str_replace('\\', '/', $image));
        $images = explode(DIRECTORY_SEPARATOR, $image);
        $imageName = end($images); // Get the file name
        $path = dirname($image) . DIRECTORY_SEPARATOR;

        // Get the optimization quality setting
        $quality = (int)Configuration::get('ETS_SPEED_QUALITY_OPTIMIZE_BROWSE');

        // Create the image and check if optimization should proceed
        if (Ets_superspeed_compressor_image::createBlogImage($path, $imageName, false)) {
            $url_image = Ets_superspeed_compressor_image::checkOptimizeImageResmush()
                ? $this->getBaseLink() . str_replace(realpath(_PS_ROOT_DIR_), '', $image)
                : null;

            // Compress the image
            $compress = Ets_superspeed_compressor_image::getInstance()->compress($path, $imageName, $quality, $url_image, false);
            while ($compress === false) {
                $compress = Ets_superspeed_compressor_image::getInstance()->compress($path, $imageName, $quality, $url_image, false);
            }

            // Save the image details
            $imageBrowse = new Ets_superspeed_browse_image();
            $imageBrowse->image_name = $imageName;
            $imageBrowse->image_dir = $image;
            $imageBrowse->image_id = $image_id;
            $imageBrowse->old_size = (float)$file_size;
            $imageBrowse->new_size = (float)$compress['file_size'];

            if ($imageBrowse->add()) {
                die(
                    json_encode(
                        array(
                            'success' => $this->l('Compress image successfully'),
                            'file_size' => $compress['file_size'] < 1024
                                ? $compress['file_size'] . 'KB'
                                : Tools::ps_round($compress['file_size'] / 1024, 2) . 'MB',
                            'saved' => Tools::ps_round(($file_size - $compress['file_size']) * 100 / $file_size, 2) . '%',
                            'image_dir' => str_replace(realpath(_PS_ROOT_DIR_), '', $image),
                            'link_download' => 'index.php?controller=AdminSuperSpeedImage&token=' . Tools::getAdminTokenLite('AdminSuperSpeedImage') . '&download_image_browse=' . $imageBrowse->id,
                            'link_restore' => 'index.php?controller=AdminSuperSpeedImage&token=' . Tools::getAdminTokenLite('AdminSuperSpeedImage') . '&restore_image_browse=' . $imageBrowse->id,
                        )
                    )
                );
            }
        } else {
            die(
                json_encode(
                    array(
                        'error' => $this->l('Create image failed'),
                    )
                )
            );
        }
    }

    protected  function submitUploadImageCompress($image)
    {
        Configuration::updateValue('ETS_SP_ERRORS_TINYPNG', '');
        $file_size = (float)Tools::getValue('file_size');
        $imageName = Tools::getValue('image_name');
        $compress = Ets_superspeed_compressor_image::getInstance()->compress(_ETS_SPEED_CACHE_DIR_IMAGES, $image, Configuration::get('ETS_SPEED_QUALITY_OPTIMIZE_UPLOAD'), $this->getBaseLink() . '/img/ss_imagesoptimize/' . $image, 0);
        while ($compress === false) {
            $compress = Ets_superspeed_compressor_image::getInstance()->compress(_ETS_SPEED_CACHE_DIR_IMAGES, $image, Configuration::get('ETS_SPEED_QUALITY_OPTIMIZE_UPLOAD'), $this->getBaseLink() . '/img/ss_imagesoptimize/' . $image, 0);
        }
        if($compress && Validate::isFileName($imageName))
        {
            $imageUpload = new Ets_superspeed_upload_image();
            $imageUpload->image_name = $imageName;
            $imageUpload->old_size = (float)$file_size;
            $imageUpload->new_size =(float)$compress['file_size'];
            $imageUpload->image_name_new = $image;
            if($imageUpload->add())
            {
                die(
                    json_encode(
                        array(
                            'success' => $this->l('Compress image successfully'),
                            'file_size' => $compress['file_size'] < 1024 ? $compress['file_size'] . 'KB' : Tools::ps_round($compress['file_size'] / 1024, 2) . 'MB',
                            'saved' => Tools::ps_round(($file_size - $compress['file_size']) * 100 / $file_size, 2) . '%',
                            'link_download' => 'index.php?controller=AdminSuperSpeedImage&token=' . Tools::getAdminTokenLite('AdminSuperSpeedImage') . '&download_image_upload=' . $imageUpload->id,
                            'link_delete' => 'index.php?controller=AdminSuperSpeedImage&token=' . Tools::getAdminTokenLite('AdminSuperSpeedImage') . '&delete_image_upload=' . $imageUpload->id,
                        )
                    )
                );
            }
        }
    }
    protected  function submitImageAllOptimize()
    {
        if (Configuration::getGlobalValue('ETS_SPEED_QUALITY_OPTIMIZE') == 100)
            Configuration::updateValue('ETS_SPEED_QUALITY_OPTIMIZE', 50);
        $this->ajaxSubmitOptimizeImage(true);
        if (Tools::isSubmit('ajax')) {
            $array2 = $this->getImageOptimize(true);
            $array1 = array(
                'success' => $this->displaySuccessMessage($this->l('Optimized images successfully')),
                'id_language' => $this->context->language->id,
                'configTabs' => Ets_superspeed_defines::getInstance()->getFieldConfig('_cache_image_tabs'),
            );
            die(
                json_encode(
                    array_merge($array1, $array2)
                )
            );
        }
    }
    protected  function saveOptimizeImageUpload()
    {
        $ETS_SPEED_OPTIMIZE_SCRIPT_UPLOAD = Tools::getValue('ETS_SPEED_OPTIMIZE_SCRIPT_UPLOAD');
        if ($ETS_SPEED_OPTIMIZE_SCRIPT_UPLOAD == 'tynypng') {
            $this->checkKeyTinyPNG();
        }
        if(Validate::isCleanHtml($ETS_SPEED_OPTIMIZE_SCRIPT_UPLOAD))
            Configuration::updateValue('ETS_SPEED_OPTIMIZE_SCRIPT_UPLOAD', $ETS_SPEED_OPTIMIZE_SCRIPT_UPLOAD);
        $ETS_SPEED_QUALITY_OPTIMIZE_UPLOAD = (int)Tools::getValue('ETS_SPEED_QUALITY_OPTIMIZE_UPLOAD');
        Configuration::updateValue('ETS_SPEED_QUALITY_OPTIMIZE_UPLOAD', $ETS_SPEED_QUALITY_OPTIMIZE_UPLOAD);
        die(
            json_encode(
                array(
                    'success' => $this->displaySuccessMessage($this->l('Saved successfully')),
                )
            )
        );
    }
    protected  function saveOptimizeImageBrowse()
    {
        $ETS_SPEED_OPTIMIZE_SCRIPT_BROWSE = Tools::getValue('ETS_SPEED_OPTIMIZE_SCRIPT_BROWSE');
        if ($ETS_SPEED_OPTIMIZE_SCRIPT_BROWSE == 'tynypng') {
            $this->checkKeyTinyPNG();
        }
        if(Validate::isCleanHtml($ETS_SPEED_OPTIMIZE_SCRIPT_BROWSE))
            Configuration::updateValue('ETS_SPEED_OPTIMIZE_SCRIPT_BROWSE', $ETS_SPEED_OPTIMIZE_SCRIPT_BROWSE);
        $ETS_SPEED_QUALITY_OPTIMIZE_BROWSE = (int)Tools::getValue('ETS_SPEED_QUALITY_OPTIMIZE_BROWSE');
        Configuration::updateValue('ETS_SPEED_QUALITY_OPTIMIZE_BROWSE', $ETS_SPEED_QUALITY_OPTIMIZE_BROWSE);
        die(
            json_encode(
                array(
                    'success' => $this->displaySuccessMessage($this->l('Saved successfully')),
                )
            )
        );
    }
    protected  function submitOldImageOptimize()
    {
        $ETS_SPEED_OPTIMIZE_SCRIPT = Tools::getValue('ETS_SPEED_OPTIMIZE_SCRIPT');
        $config_images = Ets_superspeed_defines::getInstance()->getFieldConfig('_config_images');
        if ($ETS_SPEED_OPTIMIZE_SCRIPT == 'tynypng') {
            $this->checkKeyTinyPNG();
        }
        foreach ($config_images as $config) {
            if (Tools::strpos($config['name'], '_NEW') === false) {
                $value = Tools::getValue($config['name']);
                if ($config['type'] == 'checkbox') {
                    if ($value && Ets_superspeed::validateArray($value))
                        Configuration::updateGlobalValue($config['name'], implode(',', $value));
                    else
                        Configuration::updateGlobalValue($config['name'], '');
                } elseif(Validate::isCleanHtml($value))
                    Configuration::updateGlobalValue($config['name'], $value);
            }

        }
        die(
            json_encode(
                array(
                    'success' => $this->displaySuccessMessage($this->l('Saved successfully')),
                )
            )
        );
    }
    protected  function submitImageOptimize(){
        $ETS_SPEED_OPTIMIZE_SCRIPT = Tools::getValue('ETS_SPEED_OPTIMIZE_SCRIPT');
        $config_images = Ets_superspeed_defines::getInstance()->getFieldConfig('_config_images');
        if ($ETS_SPEED_OPTIMIZE_SCRIPT == 'tynypng') {
            $this->checkKeyTinyPNG();
        }
        foreach ($config_images as $config) {
            if (Tools::strpos($config['name'], '_NEW') === false) {
                $value = Tools::getValue($config['name']);
                if ($config['type'] == 'checkbox') {
                    if ($value && Ets_superspeed::validateArray($value))
                        Configuration::updateGlobalValue($config['name'], implode(',', $value));
                    else
                        Configuration::updateGlobalValue($config['name'], '');
                } elseif(Validate::isCleanHtml($value))
                    Configuration::updateGlobalValue($config['name'], $value);
            }
        }
        $quality = ($quality = (int)Configuration::getGlobalValue('ETS_SPEED_QUALITY_OPTIMIZE')) ? $quality : 90;
        $this->ajaxSubmitOptimizeImage(false);
        $array2 = $this->getImageOptimize(false);
        $array1 = array(
            'success' => $this->displaySuccessMessage($quality == 100 ? $this->l('Restored images successfully') : $this->l('Optimized images successfully')),
            'id_language' => $this->context->language->id,
            'configTabs' => Ets_superspeed_defines::getInstance()->getFieldConfig('_cache_image_tabs'),
        );
        die(
            json_encode(
                array_merge($array1, $array2)
            )
        );
    }
    protected function downloadImageBrowse()
    {
        $id_image_browse = (int)Tools::getValue('download_image_browse');
        if($id_image_browse && ($image_browse = new Ets_superspeed_browse_image($id_image_browse)) && Validate::isLoadedObject($image_browse))
        {
            $image_browse->download();
        }
    }
    protected  function restoreImageBrowse()
    {
        if(($id_image = (int)Tools::getValue('restore_image_browse')) && ($imageBrowse = new Ets_superspeed_browse_image(($id_image))) && Validate::isLoadedObject($imageBrowse))
        {
            if($imageBrowse->restore())
            {
                die(
                    json_encode(
                        array(
                            'success' => $this->l('Restored successfully'),
                            'image_id' => $imageBrowse->image_dir ? hash('sha256',str_replace('\\', '/', $imageBrowse->image_dir)) : ''
                        )
                    )
                );
            }

        }
        die(
            json_encode(
                array(
                    'error' => $this->l('Restore failed'),
                )
            )
        );
    }
    public function _postImage()
    {
        if (Tools::isSubmit('btnSubmitLazyLoadImage')) {
            $this->submitLazyLoadImage();
        }
        if (Tools::isSubmit('btnSubmitCleaneImageUnUsed')) {
            $this->submitCleaneImageUnUsed();
        }
        if (Tools::isSubmit('btnSubmitGlobImagesToFolder') && ($folder = Tools::getValue('folder')) && is_dir($folder)) {
            die(
                json_encode(
                    array(
                        'list_files' => $this->globImagesToFolder($folder),
                    )
                )
            );
        }
        if (Tools::isSubmit('restore_image_browse')) {
            $this->restoreImageBrowse();
        }
        if (Tools::isSubmit('delete_image_upload')) {
            $this->submitDeleteImageUpload();
        }
        if (Tools::isSubmit('download_image_upload')) {
            $this->downloadImageUpload();
        }
        if (Tools::isSubmit('download_image_browse')) {
            $this->downloadImageBrowse();
        }
        if (Tools::isSubmit('submitBrowseImageOptimize') && ($image = Tools::getValue('image')) && Validate::isCleanHtml($image)) {
            $this->submitBrowseImageOptimize($image);
        }
        if (Tools::isSubmit('submitUploadImageCompress') && ($image = Tools::getValue('image')) && Validate::isFileName($image)) {
            $this->submitUploadImageCompress($image);
        }
        if (Tools::isSubmit('submitUploadImageSave')) {
            $this->submitUploadImageSave();
        }
        if (Tools::isSubmit('changeSubmitImageOptimize')) {
            die(
                json_encode(
                    $this->getImageOptimize(true)
                )
            );
        }
        if (Tools::isSubmit('btnSubmitImageAllOptimize')) {
            $this->submitImageAllOptimize();
        }
        if (Tools::isSubmit('btnSaveOptimizeImageUpload')) {
            $this->saveOptimizeImageUpload();

        }
        if (Tools::isSubmit('btnSaveOptimizeImageBrowse')) {
            $this->saveOptimizeImageBrowse();
        }
        if (Tools::isSubmit('btnSubmitOldImageOptimize')) {
            $this->submitOldImageOptimize();
        }
        if (Tools::isSubmit('btnSubmitImageOptimize')) {
            $this->submitImageOptimize();
        }
        return true;
    }
    public function displaySuccessMessage($msg, $title = false, $link = false)
    {
        $this->smarty->assign(array(
            'msg' => $msg,
            'title' => $title,
            'link' => $link
        ));
        if ($msg)
            return $this->display(__FILE__, 'success_message.tpl');
        return '';
    }
    protected function submitSaveConfigPageCache()
    {
        $live_script = Tools::getValue('live_script');
        if ($live_script && (Tools::strpos($live_script, '<script') !== false || Tools::strpos($live_script, 'script>') !== false)) {
            die(
                json_encode(
                    array(
                        'errors' => $this->displayError($this->l('Please enter JavaScript code without "script" tag. The tag will be automatically embedded to your code.')),
                    )
                )
            );
        }
        $inputs = Ets_superspeed_defines::getInstance()->getFieldConfig('_page_caches');
        $old_cache = Configuration::get('ETS_SPEED_PAGES_TO_CACHE');
        $this->saveSubmit($inputs);
        $ETS_SPEED_TIME_CACHE_INDEX = (int)Tools::getValue('ETS_SPEED_TIME_CACHE_INDEX');
        Configuration::updateValue('ETS_SPEED_TIME_CACHE_INDEX', $ETS_SPEED_TIME_CACHE_INDEX);
        $ETS_SPEED_TIME_CACHE_CATEGORY = (int)Tools::getValue('ETS_SPEED_TIME_CACHE_CATEGORY');
        Configuration::updateValue('ETS_SPEED_TIME_CACHE_CATEGORY', $ETS_SPEED_TIME_CACHE_CATEGORY);
        $ETS_SPEED_TIME_CACHE_CMS = (int)Tools::getValue('ETS_SPEED_TIME_CACHE_CMS');
        Configuration::updateValue('ETS_SPEED_TIME_CACHE_CMS', $ETS_SPEED_TIME_CACHE_CMS);
        $ETS_SPEED_TIME_CACHE_PRODUCT = (int)Tools::getValue('ETS_SPEED_TIME_CACHE_PRODUCT');
        Configuration::updateValue('ETS_SPEED_TIME_CACHE_PRODUCT', $ETS_SPEED_TIME_CACHE_PRODUCT);
        $ETS_SPEED_TIME_CACHE_NEWPRODUCTS = (int)Tools::getValue('ETS_SPEED_TIME_CACHE_NEWPRODUCTS');
        Configuration::updateValue('ETS_SPEED_TIME_CACHE_NEWPRODUCTS', $ETS_SPEED_TIME_CACHE_NEWPRODUCTS);
        $ETS_SPEED_TIME_CACHE_BESTSALES = (int)Tools::getValue('ETS_SPEED_TIME_CACHE_BESTSALES');
        Configuration::updateValue('ETS_SPEED_TIME_CACHE_BESTSALES', $ETS_SPEED_TIME_CACHE_BESTSALES);
        $ETS_SPEED_TIME_CACHE_SUPPLIER = (int)Tools::getValue('ETS_SPEED_TIME_CACHE_SUPPLIER');
        Configuration::updateValue('ETS_SPEED_TIME_CACHE_SUPPLIER', $ETS_SPEED_TIME_CACHE_SUPPLIER);
        $ETS_SPEED_TIME_CACHE_MANUFACTURER = (int)Tools::getValue('ETS_SPEED_TIME_CACHE_MANUFACTURER');
        Configuration::updateValue('ETS_SPEED_TIME_CACHE_MANUFACTURER', $ETS_SPEED_TIME_CACHE_MANUFACTURER);
        $ETS_SPEED_TIME_CACHE_CONTACT = (int)Tools::getValue('ETS_SPEED_TIME_CACHE_CONTACT');
        Configuration::updateValue('ETS_SPEED_TIME_CACHE_CONTACT', $ETS_SPEED_TIME_CACHE_CONTACT);
        $ETS_SPEED_TIME_CACHE_PRICESDROP = (int)Tools::getValue('ETS_SPEED_TIME_CACHE_PRICESDROP');
        Configuration::updateValue('ETS_SPEED_TIME_CACHE_PRICESDROP', $ETS_SPEED_TIME_CACHE_PRICESDROP);
        $ETS_SPEED_TIME_CACHE_SITEMAP = (int)Tools::getValue('ETS_SPEED_TIME_CACHE_SITEMAP');
        Configuration::updateValue('ETS_SPEED_TIME_CACHE_SITEMAP', $ETS_SPEED_TIME_CACHE_SITEMAP);
        $ETS_SPEED_TIME_CACHE_BLOG = (int)Tools::getValue('ETS_SPEED_TIME_CACHE_BLOG');
        Configuration::updateValue('ETS_SPEED_TIME_CACHE_BLOG', $ETS_SPEED_TIME_CACHE_BLOG);
        $ETS_SPEED_TIME_CACHE_PRODUCTFEED = (int)Tools::getValue('ETS_SPEED_TIME_CACHE_PRODUCTFEED');
        Configuration::updateValue('ETS_SPEED_TIME_CACHE_PRODUCTFEED',$ETS_SPEED_TIME_CACHE_PRODUCTFEED);
        $ETS_SPEED_TIME_CACHE_COLLECTION = (int)Tools::getValue('ETS_SPEED_TIME_CACHE_COLLECTION');
        Configuration::updateValue('ETS_SPEED_TIME_CACHE_COLLECTION',$ETS_SPEED_TIME_CACHE_COLLECTION);
        if (!$old_cache && Configuration::get('ETS_SPEED_PAGES_TO_CACHE')) {
            Ets_ss_class_cache::getInstance()->deleteCache();
            Ets_superspeed_cache_page_log::addLog('All', $this->l('Change settings page cache'));
        }
        if ($live_script && Validate::isString($live_script)) {
            Ets_superspeed_defines::file_put_contents(dirname(__FILE__) . '/views/js/script_custom.js', $live_script);
        } elseif (file_exists(dirname(__FILE__) . '/views/js/script_custom.js'))
            Ets_superspeed_defines::unlink(dirname(__FILE__) . '/views/js/script_custom.js');
        die(
            json_encode(
                array(
                    'success' => $this->displaySuccessMessage($this->l('Successfully saved')),
                )
            )
        );
    }
    protected function clearAllPageCaches()
    {
        Ets_ss_class_cache::getInstance()->deleteCache();
        Ets_superspeed_cache_page_log::addLog('All', $this->l('Clear all page caches'));
        Tools::clearXMLCache();
        Media::clearCache();
        Tools::clearSmartyCache();
        die(
            json_encode(
                array(
                    'success' => $this->displaySuccessMessage($this->l('Cache cleared successfully')),
                )
            )
        );
    }
    protected function submitPageCacheDashboard()
    {
        if (!Tools::isSubmit('resume')) {
            Configuration::updateValue('ETS_SP_TOTAL_IMAGE_OPTIMIZED', 0);
            $smarty_cache = (int)Tools::getValue('smarty_cache');
            $PS_SMARTY_FORCE_COMPILE = Configuration::get('PS_SMARTY_FORCE_COMPILE');
            $change = false;
            if ($smarty_cache) {
                if ($PS_SMARTY_FORCE_COMPILE == 2)
                {
                    Configuration::updateValue('PS_SMARTY_FORCE_COMPILE', 0);
                }
            }elseif($PS_SMARTY_FORCE_COMPILE!=2)
            {
                Configuration::updateValue('PS_SMARTY_FORCE_COMPILE', 2);
            }
            $server_cache = (int)Tools::getValue('server_cache');
            $PS_SMARTY_CACHE = Configuration::get('PS_SMARTY_CACHE');
            if ($server_cache) {
                if (!$PS_SMARTY_CACHE) {
                    Configuration::updateValue('PS_SMARTY_CACHE', 1);
                    Configuration::updateValue('PS_SMARTY_CACHING_TYPE', 'filesystem');
                    Configuration::updateValue('PS_SMARTY_CLEAR_CACHE', 'everytime');
                }
            } elseif($PS_SMARTY_CACHE)
            {
                Configuration::updateValue('PS_SMARTY_CACHE', 0);
            }
            $minify_html = (int)Tools::getValue('minify_html');
            $PS_HTML_THEME_COMPRESSION = Configuration::get('PS_HTML_THEME_COMPRESSION');
            if($minify_html!=$PS_HTML_THEME_COMPRESSION)
            {
                if ($minify_html)
                    Configuration::updateValue('PS_HTML_THEME_COMPRESSION', 1);
                else
                    Configuration::updateValue('PS_HTML_THEME_COMPRESSION', 0);
            }
            if(Tools::isSubmit('minify_javascript'))
            {
                $minify_javascript = (int)Tools::getValue('minify_javascript');
                $PS_JS_THEME_CACHE = Configuration::get('PS_JS_THEME_CACHE');
                if($minify_javascript!=$PS_JS_THEME_CACHE)
                {
                    if ($minify_javascript)
                        Configuration::updateValue('PS_JS_THEME_CACHE', 1);
                    else
                        Configuration::updateValue('PS_JS_THEME_CACHE', 0);
                }
            }
            if(Tools::isSubmit('minify_css'))
            {
                $minify_css = (int)Tools::getValue('minify_css');
                $PS_CSS_THEME_CACHE = Configuration::get('PS_CSS_THEME_CACHE');
                if($minify_css!=$PS_CSS_THEME_CACHE)
                {
                    if ($minify_css)
                        Configuration::updateValue('PS_CSS_THEME_CACHE', 1);
                    else
                        Configuration::updateValue('PS_CSS_THEME_CACHE', 0);
                }
            }
            $page_cache = (int)Tools::getValue('page_cache');
            $old_cache = Configuration::get('ETS_SPEED_ENABLE_PAGE_CACHE');
            if($page_cache != $old_cache)
            {
                if ($page_cache) {
                    $change = true;
                    Configuration::updateValue('ETS_SPEED_ENABLE_PAGE_CACHE', 1);
                    if (!Configuration::get('ETS_SPEED_PAGES_TO_CACHE')) {
                        $page_caches = 'index,category,product,cms,newproducts,bestsales,supplier,manufacturer,contact,pricesdrop,sitemap,blog';
                        Configuration::updateValue('ETS_SPEED_PAGES_TO_CACHE', $page_caches);
                    }
                } else {
                    Configuration::updateValue('ETS_SPEED_ENABLE_PAGE_CACHE', 0);
                    Configuration::updateValue('ETS_SPEED_PAGES_TO_CACHE', '');
                }
            }
            $browser_cache = (int)Tools::getValue('browser_cache');
            if ($browser_cache) {
                Configuration::updateValue('PS_HTACCESS_CACHE_CONTROL', 1);
            } else {
                Configuration::updateValue('PS_HTACCESS_CACHE_CONTROL', 0);
            }
            $this->hookActionHtaccessCreate();
            $production_mode = (int)Tools::getValue('production_mode');
            $this->updateDebugModeValueInCustomFile($production_mode ? 'false' : 'true');
            if(Tools::isSubmit('lazy_load'))
            {
                $lazy_load = (int)Tools::getValue('lazy_load');
                if ($lazy_load) {
                    Configuration::updateValue('ETS_SPEED_ENABLE_LAYZY_LOAD', 1);
                    if (!Configuration::get('ETS_SPEED_LAZY_FOR')) {
                        Configuration::updateValue('ETS_SPEED_LAZY_FOR', 'product_list,home_slide,home_banner');
                    }
                } else {
                    Configuration::updateValue('ETS_SPEED_ENABLE_LAYZY_LOAD', 0);
                }
                $this->replaceTemplateProductDefault(true);
            }
            if($change)
            {
                Ets_ss_class_cache::getInstance()->deleteCache();
                Ets_superspeed_cache_page_log::addLog('All', $this->l('Change settings in page dashboard'));
            }
        }
        $optimize_existing_images = (int)Tools::getValue('optimize_existing_images');
        if (Tools::isSubmit('percent_unoptimized_images') && $optimize_existing_images) {
            if (Configuration::getGlobalValue('ETS_SPEED_QUALITY_OPTIMIZE') == 100)
                Configuration::updateValue('ETS_SPEED_QUALITY_OPTIMIZE', 50);
            $this->ajaxSubmitOptimizeImage(true);
        }
        $quality = (int)Tools::getValue('ETS_SPEED_QUALITY_OPTIMIZE', Configuration::getGlobalValue('ETS_SPEED_QUALITY_OPTIMIZE'));
        die(
            json_encode(
                array(
                    'success' => $this->displaySuccessMessage($this->l('Configured successfully')),
                    'total_image_optimized_size' => Ets_superspeed_compressor_image::getInstance()->getTotalSizeSave($quality),
                )
            )
        );
    }
    protected  function submitDisabledPageCacheDashboard()
    {
        Configuration::updateValue('PS_SMARTY_FORCE_COMPILE', 2);
        Configuration::updateValue('PS_SMARTY_CACHE', 0);
        Configuration::updateValue('PS_HTML_THEME_COMPRESSION', 0);
        Configuration::updateValue('PS_JS_THEME_CACHE', 0);
        Configuration::updateValue('PS_CSS_THEME_CACHE', 0);
        Configuration::updateValue('ETS_SPEED_ENABLE_PAGE_CACHE', 0);
        Configuration::updateValue('PS_HTACCESS_CACHE_CONTROL', 0);
        Configuration::updateValue('ETS_SPEED_PAGES_TO_CACHE', '');
        $this->hookActionHtaccessCreate();
        die(
            json_encode(
                array(
                    'success' => $this->displaySuccessMessage($this->l('Successfully disable cache')),
                )
            )
        );
    }
    public function convertTime($time)
    {
        if($time)
        {
            $str ='';
            if($days = floor($time/86400))
            {
                $str .= $days.' '.($time >1 ? $this->l('days'): $this->l('day'));
            }
            elseif (($time = $time % 86400) && $hours = floor($time / 3600)) {
                $str .= $hours.' '. ($hours >1 ? $this->l('hours') : $this->l('hour'));
            }
            elseif (($time = $time % 3600) && $minutes = floor($time / 60)) {
                $str .= $minutes.' '.($minutes >1 ? $this->l('minutes'): $this->l('minute'));
            }
            elseif ($time = $time % 60)
                 $str.= $time .' '. ($time > 1 ? $this->l('seconds') : $this->l('second'));
            return $str;
        }
        return false;
    }
    protected  function submitRefreshSystemAnalyticsNew()
    {
        $check_points = array();
        $total_point = Ets_superspeed_cache_page::getTotalPoints();
        $check_points[] = array(
            'check_point' => $this->l('Number of module hooks have execution time greater than 1000 ms'),
            'number_data' => $total_point,
            'status' => $total_point ? $this->l('Bad') : $this->l('Good'),
            'class_status' => $total_point ? 'status-bab' : 'status-good',
        );
        $this->context->smarty->assign(
            array(
                'check_points' => array_merge($check_points, $this->getCheckPoints(false))
            )
        );
        die(
            json_encode(
                array(
                    'check_points' => $this->display(__FILE__, 'check_points.tpl'),
                )
            )
        );
    }
    public function _postPageCache()
    {
        if (Tools::isSubmit('btnSubmitPageCache')) {
            $this->submitSaveConfigPageCache();
        }
        if (Tools::isSubmit('clear_all_page_caches')) {
            $this->clearAllPageCaches();
        }
        if (Tools::isSubmit('btnSubmitPageCacheDashboard')) {
            $this->submitPageCacheDashboard();
        }
        if (Tools::isSubmit('btnSubmitDisabledPageCacheDashboard')) {
            $this->submitDisabledPageCacheDashboard();
        }
        if (Tools::isSubmit('btnRefreshSystemAnalyticsNew')) {
            $this->submitRefreshSystemAnalyticsNew();
        }
        return true;
    }
    public static function getModuleAuthor($module)
    {
        // Ensure module name is safe by removing potentially dangerous characters
        $module = basename($module);
        $iso = Tools::substr(Context::getContext()->language->iso_code, 0, 2);
        // Config file
        $config_file = _PS_MODULE_DIR_ . $module . '/config_' . $iso . '.xml';
        // For "en" iso code, we keep the default config.xml name
        if ($iso == 'en' || !file_exists($config_file)) {
            $config_file = _PS_MODULE_DIR_ . $module . '/config.xml';
            if (!file_exists($config_file)) {
                return 'Module ' . Tools::ucfirst($module);
            }
        }

        // Validate that the config file is within the expected directory
        $config_file_realpath = realpath($config_file);
        if (Tools::strpos($config_file_realpath, realpath(_PS_MODULE_DIR_ . $module)) !== 0) {
            return 'Module ' . Tools::ucfirst($module);
        }

        // Load config.xml
        libxml_use_internal_errors(true);
        $xml_module = @simplexml_load_file($config_file);
        if (!$xml_module) {
            return 'Module ' . Tools::ucfirst($module);
        }
        foreach (libxml_get_errors() as $error) {
            libxml_clear_errors();
            unset($error);
            return 'Module ' . Tools::ucfirst($module);
        }
        libxml_clear_errors();

        // Return Author
        return (string)$xml_module->author;
    }
    public static function displayContentCache($check_connect = false)
    {
        if (Configuration::get('ETS_SPEED_ENABLE_PAGE_CACHE') && (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] != 'POST')) {
            $cache = Ets_ss_class_cache::getInstance()->getCache($check_connect);
            if ($cache!==false) {
                return $cache;
            }
        }
        return false;
    }
    public static function isPageCache()
    {
        if(!Configuration::get('ETS_SPEED_ENABLE_PAGE_CACHE'))
            return false;
        $controller = Tools::getValue('controller');
        $fc = Tools::getValue('fc');
        $module = Tools::getValue('module');
        if (Module::isInstalled('ybc_blog') && Module::isEnabled('ybc_blog') && $fc == 'module' && $module == 'ybc_blog' && in_array($controller, array('blog', 'category', 'gallery', 'author')) && !Tools::isSubmit('edit_comment')) {
            $controller = 'blog';
        }
        $pages_cache = ($tocache = Configuration::get('ETS_SPEED_PAGES_TO_CACHE')) ? explode(',', $tocache) : array();
        if ($pages_cache && in_array($controller, $pages_cache)) {
            return true;
        }
        return false;
    }
    public static function createCache($html)
    {

        if (self::isPageCache()) {
            $controller = Tools::getValue('controller');
            $fc = Tools::getValue('fc');
            $module = Tools::getValue('module');
            $params = array(
                'controller' => Validate::isControllerName($controller) ? $controller:'',
                'id_object' => (int)Tools::getValue('id_'.$controller),
                'fc' => $fc && Validate::isCleanHtml($fc) ? $fc : '',
                'module' => $module && Validate::isModuleName($module) ? $module :'',
                'id_post' => (int)Tools::getValue('id_post'),
                'post_url_alias' => (string)Tools::getValue('post_url_alias'),
                'id_category' => (int)Tools::getValue('id_category'),
                'category_url_alias' => (string)Tools::getValue('category_url_alias'),
                'id_author' => (int)Tools::getValue('id_author'),
                'id_product_attribute' => (int)Tools::getValue('id_product_attribute'),
            );
            return Ets_ss_class_cache::getInstance()->setCache($html,$params);
        }
        return false;
    }
    public static function isInstalled($module_name)
    {
        $context = Context::getContext();
        if (!(isset($_SERVER['REQUEST_URI']) && Tools::strpos($_SERVER['REQUEST_URI'],'/api')===0) && !Tools::isSubmit('controller') && (!isset($context->employee) || !isset($context->employee->id) || !$context->employee->id))
            return false;
        return Ets_superspeed_defines::getIDModuleByName($module_name);
    }

    public static function isEnabled($module_name)
    {
        $active = false;
        $id_module = Ets_superspeed_defines::getIDModuleByName($module_name);
        if ($id_module && Ets_superspeed_defines::checkModuleIsActive($id_module)) {
            $active = true;
        }
        return (bool)$active;
    }

    public function getBaseLink($trim=true)
    {
        if(Configuration::hasKey('PS_SSL_ENABLED'))
            $link = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') . $this->context->shop->domain . $this->context->shop->getBaseURI();
        else
            $link = (Configuration::get('PS_SSL_ENABLED_EVERYWHERE') ? 'https://' : 'http://') . $this->context->shop->domain . $this->context->shop->getBaseURI();
        return $trim ? trim($link, '/'): $link;
    }
    public static function minifyHTML($html_content)
    {
        if (Tools::strlen($html_content) > 0) {
            include_once(_PS_MODULE_DIR_ . 'ets_superspeed/classes/ext/minify_html');
            $html_content = str_replace(chr(194) . chr(160), '&nbsp;', $html_content);
            if (trim($minified_content = Minify_HTML::minify($html_content, array('cssMinifier', 'jsMinifier'))) != '') {
                $html_content = $minified_content;
            }

            return $html_content;
        }
        return false;
    }

    public function autoRefreshCache()
    {
        Configuration::updateGlobalValue('ETS_SPEED_TIME_RUN_CRONJOB',date('Y-m-d H:i:s'));
        $pages_cache = Ets_superspeed_cache_page::getListFileCache(100,' AND date_expired < "' . pSQL(date('Y-m-d H:i:s')).'"','date_add ASC');
        if ($pages_cache) {
            foreach ($pages_cache as $page_cache) {
                Ets_superspeed_cache_page::deleteById($page_cache['id_cache_page'],$page_cache['id_shop']);
            }
        }
        if(Tools::isSubmit('cron'))
            die($this->l('Cronjob done. All expired caches was cleared.'));
        else
        {
            die(
                json_encode(
                    array(
                        'success' => $this->displaySuccessMessage($this->l('Cronjob done. All expired caches was cleared.')),
                        'message' =>$this->l('Last time cronjob executed: 1 second ago'),
                    )
                )
            );
        }    
    }

    public function getImageOptimize($check_quality = false, $total_all_type = true)
    {
        $array = array();
        $controller = Tools::getValue('controller');
        $image_types = ImageType::getImagesTypes();
        if ($image_types) {
            foreach ($image_types as $image_type) {
                if ($image_type['products']) {
                    $array['product_' . $image_type['name'] . '_optimized'] = Ets_superspeed_defines::getTotalImage('product', false, true, $check_quality, false, $image_type['name']);
                    $array['product_' . $image_type['name']] = Ets_superspeed_defines::getTotalImage('product', false, false, $check_quality, false, $image_type['name']) - $array['product_' . $image_type['name'] . '_optimized'];
                }
                if ($image_type['suppliers']) {
                    $array['supplier_' . $image_type['name'] . '_optimized'] = Ets_superspeed_defines::getTotalImage('supplier', false, true, $check_quality, false, $image_type['name']);
                    $array['supplier_' . $image_type['name']] = Ets_superspeed_defines::getTotalImage('supplier', false, false, $check_quality, false, $image_type['name']) - $array['supplier_' . $image_type['name'] . '_optimized'];
                }
                if ($image_type['manufacturers']) {
                    $array['manufacturer_' . $image_type['name'] . '_optimized'] = Ets_superspeed_defines::getTotalImage('manufacturer', false, true, $check_quality, false, $image_type['name']);
                    $array['manufacturer_' . $image_type['name']] = Ets_superspeed_defines::getTotalImage('manufacturer', false, false, $check_quality, false, $image_type['name']) - $array['manufacturer_' . $image_type['name'] . '_optimized'];
                }
                if ($image_type['categories']) {
                    $array['category_' . $image_type['name'] . '_optimized'] = Ets_superspeed_defines::getTotalImage('category', false, true, $check_quality, false, $image_type['name']);
                    $array['category_' . $image_type['name']] = Ets_superspeed_defines::getTotalImage('category', false, false, $check_quality, false, $image_type['name']) - $array['category_' . $image_type['name'] . '_optimized'];
                }
            }
        }
        if ($this->isblog) {
            $array['blog_post_image_optimized'] = Ets_superspeed_defines::getTotalImage('blog_post', false, true, $check_quality, false, 'image');
            $array['blog_post_thumb_optimized'] = Ets_superspeed_defines::getTotalImage('blog_post', false, true, $check_quality, false, 'thumb');
            $array['blog_post_image'] = Ets_superspeed_defines::getTotalImage('blog_post', false, false, $check_quality, false, 'image') - $array['blog_post_image_optimized'];
            $array['blog_post_thumb'] = Ets_superspeed_defines::getTotalImage('blog_post', false, false, $check_quality, false, 'thumb') - $array['blog_post_thumb_optimized'];
            $array['blog_category_image_optimized'] = Ets_superspeed_defines::getTotalImage('blog_category', false, true, $check_quality, false, 'image');
            $array['blog_category_thumb_optimized'] = Ets_superspeed_defines::getTotalImage('blog_category', false, true, $check_quality, false, 'thumb');
            $array['blog_category_image'] = Ets_superspeed_defines::getTotalImage('blog_category', false, false, $check_quality, false, 'image') - $array['blog_category_image_optimized'];
            $array['blog_category_thumb'] = Ets_superspeed_defines::getTotalImage('blog_category', false, false, $check_quality, false, 'thumb') - $array['blog_category_thumb_optimized'];
            $array['blog_gallery_image_optimized'] = Ets_superspeed_defines::getTotalImage('blog_gallery', false, true, $check_quality, false, 'image');
            $array['blog_gallery_thumb_optimized'] = Ets_superspeed_defines::getTotalImage('blog_gallery', false, true, $check_quality, false, 'thumb');
            $array['blog_gallery_image'] = Ets_superspeed_defines::getTotalImage('blog_gallery', false, false, $check_quality, false, 'image') - $array['blog_gallery_image_optimized'];
            $array['blog_gallery_thumb'] = Ets_superspeed_defines::getTotalImage('blog_gallery', false, false, $check_quality, false, 'thumb') - $array['blog_gallery_thumb_optimized'];
            $array['blog_slide_image_optimized'] = Ets_superspeed_defines::getTotalImage('blog_slide', false, true, $check_quality, false, 'image');
            $array['blog_slide_image'] = Ets_superspeed_defines::getTotalImage('blog_slide', false, false, $check_quality, false, 'image') - $array['blog_slide_image_optimized'];

        }
        if ($this->isSlide) {
            $array['home_slide_image_optimized'] = Ets_superspeed_defines::getTotalImage('home_slide', false, true, $check_quality, false, 'image');
            $array['home_slide_image'] = Ets_superspeed_defines::getTotalImage('home_slide', false, false, $check_quality, false, 'image') - $array['home_slide_image_optimized'];
        }
        $array['others_logo_optimized'] = Ets_superspeed_defines::getTotalImage('others', false, true, $check_quality, false, 'logo');
        $array['others_logo'] = Ets_superspeed_defines::getTotalImage('others', false, false, $check_quality, false, 'logo') - $array['others_logo_optimized'];
        $array['others_banner_optimized'] = Ets_superspeed_defines::getTotalImage('others', false, true, $check_quality, false, 'banner');
        $array['others_banner'] = Ets_superspeed_defines::getTotalImage('others', false, false, $check_quality, false, 'banner') - $array['others_banner_optimized'];
        $array['others_themeconfig_optimized'] = Ets_superspeed_defines::getTotalImage('others', false, true, $check_quality, false, 'themeconfig');
        $array['others_themeconfig'] = Ets_superspeed_defines::getTotalImage('others', false, false, $check_quality, false, 'themeconfig') - $array['others_themeconfig_optimized'];
        if (Tools::isSubmit('btnSubmitImageOptimize') || Tools::isSubmit('btnSubmitImageAllOptimize') || Tools::isSubmit('submitUploadImageSave') || Tools::isSubmit('submitUploadImageCompress') || Tools::isSubmit('submitBrowseImageOptimize') || Tools::isSubmit('btnSubmitCleaneImageUnUsed') || $controller == 'AdminSuperSpeedImage' || Tools::isSubmit('getPercentageImageOptimize'))
            $noconfig = false;
        else
            $noconfig = true;
        $total_image_product = Ets_superspeed_defines::getTotalImage('product', $total_all_type, false, $check_quality, $noconfig);
        $total_image_category = Ets_superspeed_defines::getTotalImage('category', $total_all_type, false, $check_quality, $noconfig);
        $total_image_manufacturer = Ets_superspeed_defines::getTotalImage('manufacturer', $total_all_type, false, $check_quality, $noconfig);
        $total_image_supplier = Ets_superspeed_defines::getTotalImage('supplier', $total_all_type, false, $check_quality, $noconfig);
        if ($this->isblog) {
            $total_image_blog_post = Ets_superspeed_defines::getTotalImage('blog_post', $total_all_type, false, $check_quality, $noconfig);
            $total_image_blog_category = Ets_superspeed_defines::getTotalImage('blog_category', $total_all_type, false, $check_quality, $noconfig);
            $total_image_blog_gallery = Ets_superspeed_defines::getTotalImage('blog_gallery', $total_all_type, false, $check_quality, $noconfig);
            $total_image_blog_slide = Ets_superspeed_defines::getTotalImage('blog_slide', $total_all_type, false, $check_quality, $noconfig);
        }
        if ($this->isSlide)
            $total_image_home_slide = Ets_superspeed_defines::getTotalImage('home_slide', $total_all_type, false, $check_quality, $noconfig);
        $total_image_product_optimizaed = Ets_superspeed_defines::getTotalImage('product', $total_all_type, true, $check_quality, $noconfig);
        $total_image_category_optimizaed = Ets_superspeed_defines::getTotalImage('category', $total_all_type, true, $check_quality, $noconfig);
        $total_image_manufacturer_optimizaed = Ets_superspeed_defines::getTotalImage('manufacturer', $total_all_type, true, $check_quality, $noconfig);
        $total_image_supplier_optimizaed = Ets_superspeed_defines::getTotalImage('supplier', $total_all_type, true, $check_quality, $noconfig);
        $total = ($total_image_product - $total_image_product_optimizaed) + ($total_image_category - $total_image_category_optimizaed) + ($total_image_manufacturer - $total_image_manufacturer_optimizaed) + ($total_image_supplier - $total_image_supplier_optimizaed);
        if ($this->isblog) {
            $total_image_blog_post_optimizaed = Ets_superspeed_defines::getTotalImage('blog_post', $total_all_type, true, $check_quality, $noconfig);
            $total_image_blog_category_optimizaed = Ets_superspeed_defines::getTotalImage('blog_category', $total_all_type, true, $check_quality, $noconfig);
            $total_image_blog_gallery_optimizaed = Ets_superspeed_defines::getTotalImage('blog_gallery', $total_all_type, true, $check_quality, $noconfig);
            $total_image_blog_slide_optimizaed = Ets_superspeed_defines::getTotalImage('blog_slide', $total_all_type, true, $check_quality, $noconfig);
            $total += ($total_image_blog_slide - $total_image_blog_slide_optimizaed) + ($total_image_blog_post - $total_image_blog_post_optimizaed) + ($total_image_blog_category - $total_image_blog_category_optimizaed) + ($total_image_blog_gallery - $total_image_blog_gallery_optimizaed);
        }
        if ($this->isSlide) {
            $total_image_home_slide_optimizaed = Ets_superspeed_defines::getTotalImage('home_slide', $total_all_type, true, $check_quality, $noconfig);
            $total += ($total_image_home_slide - $total_image_home_slide_optimizaed);
        }
        $total_image_others = Ets_superspeed_defines::getTotalImage('others', $total_all_type, false, $check_quality, $noconfig);
        $total_image_others_optimizaed = Ets_superspeed_defines::getTotalImage('others', $total_all_type, true, $check_quality, $noconfig);
        $total += ($total_image_others - $total_image_others_optimizaed);
        $quality = (int)Tools::getValue('ETS_SPEED_QUALITY_OPTIMIZE', Configuration::getGlobalValue('ETS_SPEED_QUALITY_OPTIMIZE'));
        $array['total_images'] = $total > 0 ? $total : 0;
        $array['quality_optimize'] = $quality;
        $array['total_images_optimized'] = $total_image_product_optimizaed + $total_image_category_optimizaed + $total_image_manufacturer_optimizaed + $total_image_supplier_optimizaed + ($this->isblog ? $total_image_blog_post_optimizaed + $total_image_blog_category_optimizaed + $total_image_blog_gallery_optimizaed + $total_image_blog_slide_optimizaed : 0) + ($this->isSlide ? $total_image_home_slide_optimizaed : 0);
        $array['total_size_save'] = Ets_superspeed_compressor_image::getInstance()->getTotalSizeSave($quality);
        $array['check_optimize'] = $this->checkOptimizeAllImage(true);
        return $array;
    }

    /**
     * @return array|null
     */
    public function getOverrides()
    {
        if (!$this->is17) {
            if (!is_dir($this->getLocalPath() . 'override')) {
                return null;
            }
            $result = array();
            foreach (Tools::scandir($this->getLocalPath() . 'override', 'php', '', true) as $file) {
                $class = basename($file, '.php');
                if (PrestaShopAutoload::getInstance()->getClassPath($class . 'Core') || Module::getModuleIdByName($class)) {
                    $result[] = $class;
                }
            }
            return $result;
        } else
            return parent::getOverrides();
    }

    /**
     * @param string $classname
     * @return bool
     * @throws ReflectionException
     */
    public function addOverride($classname)
    {
        $_errors = array();
        $autoload = PrestaShopAutoload::getInstance();
        $orig_path = $path = $autoload->getClassPath($classname . 'Core');

        if (!$path) {
            $path = 'modules' . DIRECTORY_SEPARATOR . $classname . DIRECTORY_SEPARATOR . $classname . '.php';
        }

        $path_override = realpath($this->getLocalPath() . 'override' . DIRECTORY_SEPARATOR . $path);

        // Check if path_override is valid and within the PrestaShop root directory
        if (Tools::strpos($path_override, realpath($this->getLocalPath() . 'override')) !== 0) {
            throw new Exception($path_override.'Invalid or dangerous file path detected.');
        }

        if (!@file_exists($path_override)) {
            return true;
        } else {
            Ets_superspeed_defines::file_put_contents($path_override, preg_replace('#(\r\n|\r)#ism', "\n", Ets_superspeed_defines::file_get_contents($path_override)));
        }

        $pattern_escape_com = '#(^\s*?\/\/.*?\n|\/\*(?!\n\s+\* module:.*?\* date:.*?\* version:.*?\*\/).*?\*\/)#ism';
        if (($file = $autoload->getClassPath($classname)) && ($override_path = realpath(_PS_ROOT_DIR_ . '/' . $file)) && file_exists($override_path)) {

            if ((!@file_exists($override_path) && !is_writable(dirname($override_path))) || (@file_exists($override_path) && !is_writable($override_path))) {
                $_errors[] = sprintf($this->l('file (%s) not writable'), $override_path);
            }

            do {
                $uniq = uniqid();
            } while (@class_exists($classname . 'OverrideOriginal_remove', false));

            $override_file = file($override_path);
            $override_file = array_diff($override_file, array("\n"));
            $this->execCode(preg_replace(array('#^\s*<\?(?:php)?#', '#class\s+' . $classname . '\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?#i'), array(' ', 'class ' . $classname . 'OverrideOriginal' . $uniq . ' extends \stdClass'), implode('', $override_file)));
            $override_class = new ReflectionClass($classname . 'OverrideOriginal' . $uniq);

            $module_file = file($path_override);
            $module_file = array_diff($module_file, array("\n"));
            $this->execCode(preg_replace(array('#^\s*<\?(?:php)?#', '#class\s+' . $classname . '(\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?)?#i'), array(' ', 'class ' . $classname . 'Override' . $uniq . ' extends \stdClass'), implode('', $module_file)));
            $module_class = new ReflectionClass($classname . 'Override' . $uniq);

            foreach ($module_class->getMethods() as $method) {
                if ($override_class->hasMethod($method->getName())) {
                    $method_override = $override_class->getMethod($method->getName());
                    if (preg_match('/module: (.*)/ism', $override_file[$method_override->getStartLine() - 5], $name) && preg_match('/date: (.*)/ism', $override_file[$method_override->getStartLine() - 4], $date) && preg_match('/version: ([0-9.]+)/ism', $override_file[$method_override->getStartLine() - 3], $version)) {
                        $_errors[] = sprintf($this->l('The method %1$s in the class %2$s is already overridden by the module %3$s version %4$s at %5$s.'), $method->getName(), $classname, $name[1], $version[1], $date[1]);
                    } else {
                        $_errors[] = sprintf($this->l('The method %1$s in the class %2$s is already overridden.'), $method->getName(), $classname);
                    }
                }
                $module_file = preg_replace('/((:?public|private|protected)\s+(static\s+)?function\s+(?:\b' . $method->getName() . '\b))/ism', "/*\n    * module: " . $this->name . "\n    * date: " . date('Y-m-d H:i:s') . "\n    * version: " . $this->version . "\n    */\n    $1", $module_file);
                if ($module_file === null) {
                    $_errors[] = sprintf($this->l('Failed to override method %1$s in class %2$s.'), $method->getName(), $classname);
                }
            }

            if (!$_errors) {
                $copy_from = array_slice($module_file, $module_class->getStartLine() + 1, $module_class->getEndLine() - $module_class->getStartLine() - 2);
                array_splice($override_file, $override_class->getEndLine() - 1, 0, $copy_from);
                $code = implode('', $override_file);

                Ets_superspeed_defines::file_put_contents($override_path, preg_replace($pattern_escape_com, '', $code));
            }
        } else {
            $override_src = $path_override;
            $override_dest = _PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'override' . DIRECTORY_SEPARATOR . $path;
            if ( Tools::strpos($override_dest, realpath(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'override')) !== 0) {
                throw new Exception($override_dest.'Invalid or dangerous file path detected2.'.realpath(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'override'));
            }
            $dir_name = dirname($override_dest);
            // Check if override_dest is valid and within the PrestaShop root directory


            if (!$orig_path && !is_dir($dir_name)) {
                $oldumask = umask(0000);
                @mkdir($dir_name, 0777);
                umask($oldumask);
            }

            if (!is_writable($dir_name)) {
                $_errors[] = sprintf($this->l('directory (%s) not writable'), $dir_name);
            }

            $module_file = file($override_src);
            $module_file = array_diff($module_file, array("\n"));

            if ($orig_path) {
                do {
                    $uniq = uniqid();
                } while (@class_exists($classname . 'OverrideOriginal_remove', false));
                $this->execCode(preg_replace(array('#^\s*<\?(?:php)?#', '#class\s+' . $classname . '(\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?)?#i'), array(' ', 'class ' . $classname . 'Override' . $uniq . ' extends \stdClass'), implode('', $module_file)));
                $module_class = new ReflectionClass($classname . 'Override' . $uniq);

                foreach ($module_class->getMethods() as $method) {
                    $module_file = preg_replace('/((:?public|private|protected)\s+(static\s+)?function\s+(?:\b' . $method->getName() . '\b))/ism', "/*\n    * module: " . $this->name . "\n    * date: " . date('Y-m-d H:i:s') . "\n    * version: " . $this->version . "\n    */\n    $1", $module_file);
                    if ($module_file === null) {
                        $_errors[] = sprintf($this->l('Failed to override method %1$s in class %2$s.'), $method->getName(), $classname);
                    }
                }
            }

            if (!$_errors) {
                Ets_superspeed_defines::file_put_contents($override_dest, preg_replace($pattern_escape_com, '', $module_file));
                Tools::generateIndex();
            }
        }

        if ($_errors) {
            $this->logInstall($classname, $_errors);
        }

        return true;
    }


    /**
     * @param $php_code
     */
    public function execCode($php_code)
    {
        // Check if the custom execution function exists
        if (function_exists('ets_execute_php')) {
            call_user_func('ets_execute_php', $php_code);
        } else {
            // Sanitize and validate the PHP code
            $php_code = trim($php_code);

            // List of dangerous functions that should be disabled
            $disabled_functions = [
                'system', 'exec', 'shell_exec', 'passthru', 'popen', 'proc_open',
                'eval', 'assert', 'create_function', 'include', 'include_once',
                'require', 'require_once'
            ];
            // Check if the code contains any dangerous functions
            foreach ($disabled_functions as $func) {
                if (stripos($php_code, $func) !== false) {
                    throw new Exception('Dangerous function detected in the PHP code.');
                }
            }

            // Create a temporary file to execute the PHP code
            $temp = @tempnam(sys_get_temp_dir(), 'execCode');
            if ($temp === false) {
                throw new Exception('Failed to create a temporary file.');
            }

            $handle = fopen($temp, "w+");
            if ($handle === false) {
                throw new Exception('Failed to open the temporary file for writing.');
            }

            fwrite($handle, "<?php\n" . $php_code);
            fclose($handle);

            // Validate that the file was created successfully
            if (file_exists($temp)) {
                include $temp;
                Ets_superspeed_defines::unlink($temp);
            } else {
                throw new Exception('Temporary file not found.');
            }
        }
    }
    /**
     * @param string $classname
     * @return bool
     */
    public function removeOverride($classname)
    {
        // Check if the override is already logged
        if ($this->isLogInstall($classname)) {
            return true;
        }

        // Get the original path for the class
        $orig_path = PrestaShopAutoload::getInstance()->getClassPath($classname . 'Core');

        // Determine the current path of the class
        if ($orig_path && !PrestaShopAutoload::getInstance()->getClassPath($classname)) {
            return true;
        } elseif (!$orig_path && Module::getModuleIdByName($classname)) {
            $path = 'modules' . DIRECTORY_SEPARATOR . $classname . DIRECTORY_SEPARATOR . $classname . '.php';
        } else {
            $path = $orig_path ? $orig_path : _PS_OVERRIDE_DIR_ . DIRECTORY_SEPARATOR . $classname . '.php';
        }

        // Construct the full path to the override file
        $override_path = _PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . $path;

        // Check if the file exists and is writable
        if (!file_exists($override_path) || !is_writable($override_path)) {
            return true;
        }
        return parent::removeOverride($classname);
    }
    public $log_file = 'install.log';

    /**
     * @param $classname
     * @param $_errors
     */
    public function logInstall($classname, $_errors)
    {
        // Ensure the log file path is safe
        $log_file = $this->getLocalPath() . $this->log_file;
        $log_file = realpath($log_file);
        if ($log_file === false || !is_writable(dirname($log_file))) {
            return false;
        }
        $data = array();

        // Read existing data if the file exists
        if (file_exists($log_file)) {
            $file_contents = Tools::file_get_contents($log_file);
            if ($file_contents !== false) {
                $decoded_data = json_decode($file_contents, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $data = (array)$decoded_data;
                }
            }
        }

        // Update data with new errors
        $data[$classname] = $_errors;

        // Write updated data back to the file
        $json_data = json_encode($data, JSON_PRETTY_PRINT);
        if ($json_data === false) {
            return false;
        }

        return file_put_contents($log_file, $json_data) !== false;
    }


    /**
     * @param $classname
     * @return bool
     */
    public function isLogInstall($classname)
    {
        $log_file = $this->getLocalPath() . $this->log_file;
        if (!@file_exists($log_file))
            return false;
        $cached = (array)json_decode(Ets_superspeed_defines::file_get_contents($log_file),true);
        if ($cached && !empty($cached[$classname]))
            return true;
        return false;
    }

    /**
     * @return bool
     */
    public function clearLogInstall()
    {
        // Securely construct the path to the log file
        $logFilePath = $this->getLocalPath() . $this->log_file;

        // Ensure the file path is within the expected directory to avoid path traversal
        if (Tools::strpos(realpath($logFilePath), _PS_ROOT_DIR_) !== 0) {
            // Log or handle the error if the path is not valid
            error_log('Invalid log file path: ' . $logFilePath);
            return false;
        }
        // Check if the file exists before attempting to delete
        if (file_exists($logFilePath)) {
            // Attempt to delete the file and handle any errors
            if (!Ets_superspeed_defines::unlink($logFilePath)) {
                // Log or handle the error if file deletion fails
                error_log('Failed to delete log file: ' . $logFilePath);
                return false;
            }
        }

        return true;
    }


    public function genSecure($size)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';
        for ($i = 1; $i <= $size; ++$i) {
            $char = Tools::substr($chars, rand(0, Tools::strlen($chars) - 1), 1);
            if ($char == 'e')
                $char = 'a';
            $code .= $char;
        }
        return $code;
    }
    public function checkOptimizeAllImage($check_quality = false)
    {
        $total_image_product = Ets_superspeed_defines::getTotalImage('product', true, false, $check_quality, true);
        $total_image_category = Ets_superspeed_defines::getTotalImage('category', true, false, $check_quality, true);
        $total_image_manufacturer = Ets_superspeed_defines::getTotalImage('manufacturer', true, false, $check_quality, true);
        $total_image_supplier = Ets_superspeed_defines::getTotalImage('supplier', true, false, $check_quality, true);
        $total_image_product_optimizaed = Ets_superspeed_defines::getTotalImage('product', true, true, $check_quality, true);
        $total_image_category_optimizaed = Ets_superspeed_defines::getTotalImage('category', true, true, $check_quality, true);
        $total_image_manufacturer_optimizaed = Ets_superspeed_defines::getTotalImage('manufacturer', true, true, $check_quality, true);
        $total_image_supplier_optimizaed = Ets_superspeed_defines::getTotalImage('supplier', true, true, $check_quality, true);
        $total_images = $total_image_product + $total_image_category + $total_image_manufacturer + $total_image_supplier;
        $total_optimized_images = $total_image_category_optimizaed + $total_image_product_optimizaed + $total_image_supplier_optimizaed + $total_image_manufacturer_optimizaed;
        if ($this->isblog) {
            $total_image_blog_post = Ets_superspeed_defines::getTotalImage('blog_post', true, false, $check_quality, true);
            $total_image_blog_category = Ets_superspeed_defines::getTotalImage('blog_category', true, false, $check_quality, true);
            $total_image_blog_gallery = Ets_superspeed_defines::getTotalImage('blog_gallery', true, false, $check_quality, true);
            $total_image_blog_slide = Ets_superspeed_defines::getTotalImage('blog_slide', true, false, $check_quality, true);
            $total_image_blog_post_optimizaed = Ets_superspeed_defines::getTotalImage('blog_post', true, true, $check_quality, true);
            $total_image_blog_category_optimizaed = Ets_superspeed_defines::getTotalImage('blog_category', true, true, $check_quality, true);
            $total_image_blog_gallery_optimizaed = Ets_superspeed_defines::getTotalImage('blog_gallery', true, true, $check_quality, true);
            $total_image_blog_slide_optimizaed = Ets_superspeed_defines::getTotalImage('blog_slide', true, true, $check_quality, true);
            $total_images += $total_image_blog_post + $total_image_blog_category + $total_image_blog_gallery + $total_image_blog_slide;
            $total_optimized_images += $total_image_blog_post_optimizaed + $total_image_blog_category_optimizaed + $total_image_blog_gallery_optimizaed + $total_image_blog_slide_optimizaed;
        }
        if ($this->isSlide) {
            $total_image_home_slide = Ets_superspeed_defines::getTotalImage('home_slide', true, false, $check_quality, true);
            $total_image_home_slide_optimizaed = Ets_superspeed_defines::getTotalImage('home_slide', true, true, $check_quality, true);
            $total_images += $total_image_home_slide;
            $total_optimized_images += $total_image_home_slide_optimizaed;
        }
        $total_image_others = Ets_superspeed_defines::getTotalImage('others', true, false, $check_quality, true);
        $total_image_others_optimizaed = Ets_superspeed_defines::getTotalImage('others', true, true, $check_quality, true);
        $total_images += $total_image_others;
        $total_optimized_images += $total_image_others_optimizaed;
        $total_unoptimized_images = $total_images - $total_optimized_images;
        if ($total_unoptimized_images == 0)
            return $total_images;
        else
            return false;
    }
    public function checkCreatedColumn($table, $column)
    {
        $fields = Ets_superspeed_defines::getColumnTable($table);
        $check_add = false;
        foreach ($fields as $field) {
            if ($field['Field'] == $column) {
                $check_add = true;
                break;
            }
        }
        return $check_add;
    }
    public function hookActionUpdateBlog()
    {
        Ets_ss_class_cache::getInstance()->deleteCache('blog');
        Ets_superspeed_cache_page_log::addLog('Blog', $this->l('Change blog settings'));
    }
    public function hookActionUpdateBlogImage($params)
    {
        Ets_superspeed_compressor_image::getInstance()->actionUpdateBlogImage($params);
    }
    public function ajaxSubmitOptimizeImage($all_type)
    {
        if (!Tools::isSubmit('resume')) {
            Configuration::updateValue('ETS_SPEEP_RESUMSH', 2);
            Configuration::updateValue('ETS_SP_ERRORS_TINYPNG', '');
            Configuration::updateValue('ETS_SP_TOTAL_IMAGE_OPTIMIZED', 0);
            Configuration::updateValue('ETS_SP_LIST_IMAGE_OPTIMIZED', '');
        }
        $optimize_type = Tools::getValue('optimize_type', 'products');
        $optimizeImage = Ets_superspeed_compressor_image::getInstance();
        switch ($optimize_type) {
            case 'products':
                $optimizeImage->optimizeProductImage($all_type);
            case 'categories':
                $optimizeImage->optimiziObjImage('category', 'categories', _PS_CAT_IMG_DIR_, $all_type, 'manufacturers');
            case 'manufacturers':
                $optimizeImage->optimiziObjImage('manufacturer', 'manufacturers', _PS_MANU_IMG_DIR_, $all_type, 'suppliers');
            case 'suppliers':
                $next = $this->isblog ? 'post' : ($this->isSlide ? 'home_slide' : 'other_image');
                $optimizeImage->optimiziObjImage('supplier', 'suppliers', _PS_SUPP_IMG_DIR_, $all_type, $next);
            case 'post':
                if ($this->isblog) {
                    $ybc_blog = Module::getInstanceByName('ybc_blog');
                    if (version_compare($ybc_blog->version, '3.2.1', '>='))
                        $optimizeImage->optimiziBlogImage('post', _PS_YBC_BLOG_IMG_DIR_ . 'post/', $all_type, 'category');
                    else
                        $optimizeImage->optimiziBlogImage('post', _PS_MODULE_DIR_ . 'ybc_blog/views/img/post/', $all_type, 'category');
                }
            case 'category':
                if ($this->isblog) {
                    $ybc_blog = Module::getInstanceByName('ybc_blog');
                    if (version_compare($ybc_blog->version, '3.2.1', '>='))
                        $optimizeImage->optimiziBlogImage('category', _PS_YBC_BLOG_IMG_DIR_ . 'category/', $all_type, 'gallery');
                    else
                        $optimizeImage->optimiziBlogImage('category', _PS_MODULE_DIR_ . 'ybc_blog/views/img/category/', $all_type, 'gallery');
                }

            case 'gallery':
                if ($this->isblog) {
                    $ybc_blog = Module::getInstanceByName('ybc_blog');
                    if (version_compare($ybc_blog->version, '3.2.1', '>='))
                        $optimizeImage->optimiziBlogImage('gallery', _PS_YBC_BLOG_IMG_DIR_ . 'gallery/', $all_type, 'slide');
                    else
                        $optimizeImage->optimiziBlogImage('gallery', _PS_MODULE_DIR_ . 'ybc_blog/views/img/gallery/', $all_type, 'slide');
                }
            case 'slide':
                if ($this->isblog) {
                    $next = $this->isSlide ? 'home_slide' : 'other_image';
                    $ybc_blog = Module::getInstanceByName('ybc_blog');
                    if (version_compare($ybc_blog->version, '3.2.1', '>='))
                        $optimizeImage->optimiziBlogImage('slide', _PS_YBC_BLOG_IMG_DIR_ . 'slide/', $all_type, $next);
                    else
                        $optimizeImage->optimiziBlogImage('slide', _PS_MODULE_DIR_ . 'ybc_blog/views/img/slide/', $all_type, $next);
                }
            case 'home_slide':
                if ($this->isSlide)
                    $optimizeImage->optimiziSlideImage($all_type);
            case 'other_image':
                $optimizeImage->optimiziOthersImage($all_type);
        }
    }

    public function displayError($errors, $popup = false)
    {
        $this->context->smarty->assign(
            array(
                'errors' => $errors,
                'popup' => $popup
            )
        );
        return $this->display(__FILE__, 'error.tpl');
    }
    public function getFilterValues($filter)
    {
        $text = '';
        if ($filter) {
            foreach ($filter as $key => $value) {
                $text .= '&' . $key . '=' . $value;
            }
        }
        return $text;
    }
    public function getImageInSite()
    {
        return array(
            'count_img' => '--',
            'count_css' => '--',
            'count_js' => '--',
        );
    }
    private function updateDebugModeValueInCustomFile($value)
    {
        if ($value !== 'true' && $value !== 'false') {
            throw new InvalidArgumentException('Invalid value for debug mode. Must be "true" or "false".');
        }
        $customFileName = realpath(_PS_ROOT_DIR_ . '/config/defines.inc.php');
        if ($customFileName === false || Tools::strpos($customFileName, realpath(_PS_ROOT_DIR_ . '/config')) !== 0) {
            throw new Exception('Invalid file path detected.');
        }
        $cleanedFileContent = php_strip_whitespace($customFileName);
        $fileContent = Ets_superspeed_defines::file_get_contents($customFileName);
        if (!empty($cleanedFileContent) && preg_match('/define\(\'_PS_MODE_DEV_\', ([a-zA-Z]+)\);/Ui', $cleanedFileContent)) {
            $fileContent = preg_replace('/define\(\'_PS_MODE_DEV_\', ([a-zA-Z]+)\);/Ui', 'define(\'_PS_MODE_DEV_\', ' . $value . ');', $fileContent);
            // Write the updated content back to the file
            if (!Ets_superspeed_defines::file_put_contents($customFileName, $fileContent)) {
                return false;
            }
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($customFileName);
            }
            return true;
        }

        return false;
    }
    public function getCheckPoints($after_ajax = true)
    {
        $extra_hooks = array();
        if (!$after_ajax)
            $totals = $this->getImageInSite();
        if (($this->is17 && Module::isEnabled('ps_imageslider')) || (!$this->is17 && Module::isEnabled('homeslider')))
            $extra_hooks[] = array(
                'name' => 'home_slider',
                'check_point' => $this->l('Home slider images'),
                'number_data' => Ets_superspeed_cache_page::getTotalHomeSlider(),
                'url_config' => $this->context->link->getAdminLink('AdminModules') . '&configure=' . ($this->is17 ? 'ps_imageslider' : 'homeslider'),
                'recommendation' => $this->l('Should not more than 3 items'),
                'default' => 3,
                'bad' => 6,
            );
        if (($this->is17 && Module::isEnabled('ps_featuredproducts')) || (!$this->is17 && Module::isEnabled('homefeatured')))
            $extra_hooks[] = array(
                'name' => 'popular_product',
                'check_point' => $this->l('Popular products'),
                'number_data' => Configuration::get('HOME_FEATURED_NBR'),
                'url_config' => $this->context->link->getAdminLink('AdminModules') . '&configure=' . ($this->is17 ? 'ps_featuredproducts' : 'homefeatured'),
                'recommendation' => $this->l('Should not more than 8 items'),
                'default' => 8,
                'bad' => 12,
            );
        if (Module::isEnabled('ps_newproducts') || Module::isEnabled('blocknewproducts'))
            $extra_hooks[] = array(
                'name' => 'new_product',
                'check_point' => $this->l('New products'),
                'number_data' => Configuration::get('NEW_PRODUCTS_NBR'),
                'url_config' => $this->context->link->getAdminLink('AdminModules') . '&configure=' . ($this->is17 ? 'ps_newproducts' : 'blocknewproducts'),
                'recommendation' => $this->l('Should not more than 8 items'),
                'default' => 8,
                'bad' => 12,
            );
        if (Module::isEnabled('blockspecials') || Module::isEnabled('ps_specials'))
            $extra_hooks[] = array(
                'name' => 'sepcials_product',
                'check_point' => $this->l('Specials'),
                'number_data' => Configuration::get('BLOCKSPECIALS_SPECIALS_NBR'),
                'url_config' => $this->context->link->getAdminLink('AdminModules') . '&configure=' . ($this->is17 ? 'ps_specials' : 'blockspecials'),
                'recommendation' => $this->l('Should not more than 8 items'),
                'default' => 8,
                'bad' => 12,
            );
        if (Module::isEnabled('blockbestsellers') || Module::isEnabled('ps_bestsellers'))
            $extra_hooks[] = array(
                'name' => 'best_seller',
                'check_point' => $this->l('Best seller'),
                'number_data' => Configuration::get('PS_BLOCK_BESTSELLERS_TO_DISPLAY'),
                'url_config' => $this->context->link->getAdminLink('AdminModules') . '&configure=' . ($this->is17 ? 'ps_bestsellers' : 'blockbestsellers'),
                'recommendation' => $this->l('Should not more than 8 items'),
                'default' => 8,
                'bad' => 16,
            );
        if (Module::isEnabled('ps_categoryproducts'))
            $extra_hooks[] = array(
                'name' => 'product_category',
                'check_point' => $this->l('Products in the same category'),
                'number_data' => Configuration::get('CATEGORYPRODUCTS_DISPLAY_PRODUCTS'),
                'url_config' => $this->context->link->getAdminLink('AdminModules') . '&configure=ps_categoryproducts',
                'recommendation' => $this->l('Should not more than 8 items'),
                'default' => 8,
                'bad' => 16,
            );
        $extra_hooks2 = array(
            array(
                'name' => 'category_page',
                'check_point' => $this->l('Products per page on category page'),
                'number_data' => Configuration::get('PS_PRODUCTS_PER_PAGE'),
                'url_config' => $this->context->link->getAdminLink('AdminPPreferences'),
                'recommendation' => $this->l('Should not more than 12 items'),
                'default' => 12,
                'bad' => 24,
            ),
            array(
                'name' => 'image_home',
                'check_point' => $this->l('Number of images on home page'),
                'number_data' => $after_ajax || !$totals['count_img'] ? '-' : $totals['count_img'],
                'url_config' => '',
                'recommendation' => $this->l('Should not more than 30 images. Consider to minimize the number of images displayed on home page.'),
                'default' => 30,
                'bad' => 50,
            ),
            array(
                'name' => 'css_home',
                'check_point' => $this->l('Number of CSS files (home page)'),
                'number_data' => $after_ajax || !$totals['count_css'] ? '-' : $totals['count_css'],
                'recommendation' => $this->l('Should not more than 5 files. Enable Minify CSS to combine all CSS files into 1 file'),
                'default' => 5,
                'bad' => 10,
                'url_config' => $this->context->link->getAdminLink('AdminSuperSpeedMinization'),
            ),
            array(
                'name' => 'script_home',
                'check_point' => $this->l('Number of JavaScript files (home page)'),
                'number_data' => $after_ajax || !$totals['count_js'] ? '-' : $totals['count_js'],
                'recommendation' => $this->l('Should not more than 5 files. Enable Minify JavaScript to combine all JavaScript files into 1 file'),
                'default' => 5,
                'bad' => 10,
                'url_config' => $this->context->link->getAdminLink('AdminSuperSpeedMinization'),
            ),
            array(
                'name' => 'media_server',
                'check_point' => $this->l('Media servers'),
                'url_config' => $this->context->link->getAdminLink('AdminPerformance') . '#fieldset_4_4',
                'recommendation' => $this->l('Configure Media servers in order to use cookieless static content'),
                'enabled' => Configuration::get('PS_MEDIA_SERVER_1') || Configuration::get('PS_MEDIA_SERVER_2') || Configuration::get('PS_MEDIA_SERVER_3'),
                'server' => Configuration::get('PS_MEDIA_SERVER_1') ? Configuration::get('PS_MEDIA_SERVER_1') : (Configuration::get('PS_MEDIA_SERVER_2') ? Configuration::get('PS_MEDIA_SERVER_2') : Configuration::get('PS_MEDIA_SERVER_3')),
                'bad' => 16,
            ),
            array(
                'name' => 'caching_server',
                'check_point' => $this->l('Caching system'),
                'url_config' => $this->context->link->getAdminLink('AdminPerformance') . '#fieldset_5_5',
                'recommendation' => $this->l('Enable Memcached, APC or Xcache (if they are supported by your server) to maximize website speed.'),
                'enabled' => _PS_CACHE_ENABLED_,
                'server' => _PS_CACHE_ENABLED_ ? _PS_CACHING_SYSTEM_ : '',
                'bad' => 16,
            )
        );
        return array_merge($extra_hooks, $extra_hooks2);
    }

    public function hookDisplayImagesBrowse()
    {
        $dir_files = $this->globImagesToFolder(_PS_ROOT_DIR_);
        $images = Ets_superspeed_compressor_image::getBrowseImages();
        if ($images) {
            foreach ($images as &$image) {
                $image['saved'] = Tools::ps_round(($image['old_size'] - $image['new_size']) * 100 / $image['old_size'], 2) . '%';
                $image['old_size'] = $image['old_size'] < 1024 ? $image['old_size'] . 'KB' : Tools::ps_round($image['old_size'] / 2014, 2) . 'MB';
                $image['new_size'] = $image['new_size'] < 1024 ? $image['new_size'] . 'KB' : Tools::ps_round($image['new_size'] / 2014, 2) . 'MB';
                $image['image_dir'] = str_replace(str_replace('\\', '/', _PS_ROOT_DIR_), '', $image['image_dir']);
                $image['image_name_hide'] = Tools::strlen($image['image_name']) > 23 ? Tools::substr($image['image_name'], 0, 11) . ' . . . ' . Tools::substr($image['image_name'], Tools::strlen($image['image_name']) - 12) : $image['image_name'];
            }
        }
        $this->context->smarty->assign(
            array(
                'dir_files' => $dir_files,
                'images' => $images,
            )
        );
        return $this->display(__FILE__, 'browse_images.tpl');
    }

    public function globImagesToFolder($folder)
    {
        // Convert to real path and check if the folder is valid and within the allowed base directory
        $baseDir = realpath(_PS_ROOT_DIR_);
        $folder = realpath($folder);

        if ($folder === false || Tools::strpos($folder, $baseDir) !== 0) {
            throw new Exception('Invalid or dangerous folder path detected.');
        }

        $files = glob($folder . '/*');
        $list_files = array();
        $list_folders = array();

        foreach ($files as $file) {
            $name = explode('/', $file);

            if (is_file($file)) {
                $type = Tools::strtolower(Tools::substr(strrchr($file, '.'), 1));

                if (in_array($type, array('jpg', 'jpeg', 'png')) && Tools::strpos($file, '_bk.' . $type) === false) {
                    $file_size = Tools::ps_round(@filesize($file) / 1024, 2);
                    $file_id = hash('sha256', str_replace('\\', '/', $file));

                    $uploaded = Ets_superspeed_compressor_image::getBrowseImages($file_id) ? true : false;

                    $list_files[] = array(
                        'dir' => str_replace('\\', '/', $file),
                        'id' => $file_id,
                        'name' => end($name),
                        'type' => 'file',
                        'uploaed' => $uploaded,
                        'file_size' => $file_size < 1024 ? $file_size . 'KB' : Tools::ps_round($file_size / 1024, 2) . 'MB',
                    );
                }
            } elseif (Tools::strpos($file, 'ss_imagesoptimize') === false) {
                $list_folders[] = array(
                    'dir' => str_replace('\\', '/', $file),
                    'name' => end($name),
                    'type' => 'folder',
                    'id' => hash('sha256', str_replace('\\', '/', $file)),
                    'has_file' => $this->checkHasFileInFolder($file),
                );
            }
        }

        $this->context->smarty->assign(
            array(
                'list_files' => array_merge($list_folders, $list_files),
            )
        );

        return $this->display(__FILE__, 'dir_list_files.tpl');
    }


    public function checkHasFileInFolder($folder)
    {
        // Validate and sanitize the folder path
        $folder = realpath($folder); // Convert to absolute path
        if ($folder === false || !is_dir($folder)) {
            return false;
        }
        $expectedDir = realpath(_PS_ROOT_DIR_ );
        if (Tools::strpos($folder, $expectedDir) !== 0) {
            return false;
        }

        $files = glob($folder . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                $type = Tools::strtolower(Tools::substr(strrchr($file, '.'), 1));
                if (in_array($type, array('jpg', 'gif', 'jpeg', 'png')) && Tools::strpos($file, '_bk.' . $type) === false) {
                    return true;
                }
            }
        }
        return false;
    }
    public function checkKeyTinyPNG()
    {
        $api_keys = Tools::getValue('ETS_SPEED_API_TYNY_KEY');
        if ($api_keys && Ets_superspeed::validateArray($api_keys)) {
            $keys = array();
            foreach ($api_keys as $key) {
                if (trim($key))
                    $keys[] = $key;
            }
            if ($keys) {
                Configuration::updateValue('ETS_SPEED_API_TYNY_KEY', implode(';', $keys));
                return true;
            }

        }
        die(
            json_encode(
                array(
                    'errors' => $this->displayError($this->l('Tinypng API key is required'))
                )
            )
        );
    }

    public function hookDisplayImagesUploaded()
    {
        $images = Ets_superspeed_compressor_image::getUploadImages();
        if ($images) {
            foreach ($images as &$image) {
                $image['saved'] = Tools::ps_round(($image['old_size'] - $image['new_size']) * 100 / $image['old_size'], 2) . '%';
                $image['old_size'] = $image['old_size'] < 1024 ? $image['old_size'] . 'KB' : Tools::ps_round($image['old_size'] / 1024, 2) . 'MB';
                $image['new_size'] = $image['new_size'] < 1024 ? $image['new_size'] . 'KB' : Tools::ps_round($image['new_size'] / 1024, 2) . 'MB';
                $image['image_name_hide'] = Tools::strlen($image['image_name']) > 23 ? Tools::substr($image['image_name'], 0, 11) . ' . . . ' . Tools::substr($image['image_name'], Tools::strlen($image['image_name']) - 12) : $image['image_name'];
            }
        }
        $this->context->smarty->assign(
            array(
                'images' => $images,
            )
        );
        return $this->display(__FILE__, 'images.tpl');
    }
    public function hookDisplayImagesCleaner()
    {
        $image_category = Ets_superspeed_compressor_image:: getImagesUnUsed();
        $image_supplier = Ets_superspeed_compressor_image::getImagesUnUsed('su', 'supplier', 'id_supplier', 'suppliers');
        $image_manufacturer = Ets_superspeed_compressor_image::getImagesUnUsed('m', 'manufacturer', 'id_manufacturer', 'manufacturers');
        $image_product = Ets_superspeed_compressor_image::getImagesProductUnUsed();
        $this->context->smarty->assign(
            array(
                'image_category' => $image_category,
                'image_supplier' => $image_supplier,
                'image_manufacturer' => $image_manufacturer,
                'image_product' => $image_product,
            )
        );
        return $this->display(__FILE__, 'image_cleaner.tpl');
    }

    public function replaceTemplateProductDefault($delete_cache = true)
    {
        $templates = [
            'product' => [
                'path' => $this->is17 ? _PS_THEME_DIR_ . 'templates/catalog/_partials/miniatures/product.tpl' : _PS_THEME_DIR_ . 'product-list.tpl',
                'backup' => $this->is17 ? _PS_THEME_DIR_ . 'templates/catalog/_partials/miniatures/product.ssbackup.tpl' : _PS_THEME_DIR_ . 'product-list.ssbackup.tpl'
            ],
        ];
        if($this->is17)
        {
            $templates['slider'] = [
                'path' => file_exists(_PS_THEME_DIR_ . 'modules/ps_imageslider/views/templates/hook/slider.tpl') ?
                    _PS_THEME_DIR_ . 'modules/ps_imageslider/views/templates/hook/slider.tpl' :
                    _PS_MODULE_DIR_ . 'ps_imageslider/views/templates/hook/slider.tpl',
                'backup' => _PS_MODULE_DIR_ . 'ps_imageslider/views/templates/hook/slider.ssbackup.tpl'
            ] ;
            $templates['banner'] = [
                'path' => file_exists(_PS_THEME_DIR_ . 'modules/ps_banner/ps_banner.tpl') ?
                    _PS_THEME_DIR_ . 'modules/ps_banner/ps_banner.tpl' :
                    _PS_MODULE_DIR_ . 'ps_banner/ps_banner.tpl',
                'backup' => _PS_MODULE_DIR_ . 'ps_banner/ps_banner.ssbackup.tpl'
            ];
        }
        if ($this->is16) {
            $templates['homeslider'] = [
                'path' => file_exists(_PS_THEME_DIR_ . 'modules/homeslider/homeslider.tpl') ?
                    _PS_THEME_DIR_ . 'modules/homeslider/homeslider.tpl' :
                    _PS_MODULE_DIR_ . 'homeslider/views/templates/hook/homeslider.tpl',
                'backup' => _PS_MODULE_DIR_ . 'homeslider/homeslider.ssbackup.tpl'
            ];
            $templates['blockbanner'] = [
                'path' => file_exists(_PS_THEME_DIR_ . 'modules/blockbanner/blockbanner.tpl') ?
                    _PS_THEME_DIR_ . 'modules/blockbanner/blockbanner.tpl' :
                    _PS_MODULE_DIR_ . 'blockbanner/blockbanner.tpl',
                'backup' => _PS_MODULE_DIR_ . 'blockbanner/blockbanner.ssbackup.tpl'
            ];
            $templates['themeconfig'] = [
                'path' => file_exists(_PS_THEME_DIR_ . 'modules/themeconfigurator/views/templates/hook/hook.tpl') ?
                    _PS_THEME_DIR_ . 'modules/themeconfigurator/views/templates/hook/hook.tpl' :
                    _PS_MODULE_DIR_ . 'themeconfigurator/views/templates/hook/hook.tpl',
                'backup' => _PS_MODULE_DIR_ . 'themeconfigurator/views/templates/hook/hook.ssbackup.tpl'
            ];
        }

        $lazy_load_enabled = (int)Configuration::get('ETS_SPEED_ENABLE_LAYZY_LOAD');
        $image_for = Configuration::get('ETS_SPEED_LAZY_FOR') ? explode(',', Configuration::get('ETS_SPEED_LAZY_FOR')) : [];
        $bloklazyload = Tools::file_get_contents(dirname(__FILE__) . '/views/templates/hook/blocklazyload.txt');
        $preg_replace_text = '/<' . 'img(.*?)\ssrc(.*?)=(.*?)(")(.*?)(")(.*?>)/is';

        foreach ($templates as $key => $template) {
            $path = $template['path'];
            $backup = $template['backup'];

            if ($lazy_load_enabled && in_array($key, $image_for)) {
                if (file_exists($path) && !file_exists($backup)) {
                    Tools::copy($path, $backup);
                    $content = file_exists(dirname(__FILE__) . "/views/templates/hook/{$key}_list.tpl") ?
                        Tools::file_get_contents(dirname(__FILE__) . "/views/templates/hook/{$key}_list.tpl") :
                        Tools::file_get_contents($path);
                    if ($this->is17) {
                        $content = preg_replace($preg_replace_text, '<' . 'img' . ' src="{if isset($ets_link_base)}{$ets_link_base}/modules/' . $this->name . '/views/img/preloading.png{/if}" class="lazyload" data-src="$5"$7' . $bloklazyload, $content);
                    } else {
                        $content = preg_replace($preg_replace_text, '<' . 'img' . ' src="{if isset($ets_link_base)}{$ets_link_base}/modules/' . $this->name . '/views/img/preloading.png{/if}" class="replace-2x img-responsive lazyload" data-src="$5"$7' . $bloklazyload, $content);
                    }
                    file_put_contents($path, $content);
                }
            } else {
                if (file_exists($backup)) {
                    Tools::copy($backup, $path);
                    Ets_superspeed_defines::unlink($backup);
                }
            }
        }

        if ($delete_cache) {
            Ets_ss_class_cache::getInstance()->deleteCache();
            Ets_superspeed_cache_page_log::addLog('All', $this->l('Change settings in page image optimization'));
            Tools::clearSmartyCache();
        }
        return true;
    }
    public function submitDeleteSystemAnalytics()
    {
        if(Ets_superspeed_defines::deleteHookTime())
        {
            die(
            json_encode(
                array(
                    'success' => $this->l('Clear successfully'),
                )
            )
            );
        }
    }

    public function hookActionPageCacheAjax()
    {
        $data = array();
        Media::addJsDef(array(
            'comparedProductsIds' => $this->context->smarty->getTemplateVars('compared_products'),
            'isLogged' => (bool)$this->context->customer->isLogged(),
            'isGuest' => (bool)$this->context->customer->isGuest(),
            'static_token' => Tools::getToken(false),
        ));
        $js_def = Media::getJsDef();
        if (isset($js_def['prestashop']))
            unset($js_def['prestashop']);
        $this->context->smarty->assign(array(
            'js_def' => $js_def,
        ));
        $javascript = $this->context->smarty->fetch(_PS_ALL_THEMES_DIR_ . 'javascript.tpl');
        $data['java_script'] = $javascript;
        if (file_exists(_PS_MODULE_DIR_ . 'ets_superspeed/views/js/script_custom.js'))
            $data['custom_js'] = true;
        $count_datas = (int)Tools::getValue('count_datas');
        if ((int)$count_datas) {
            for ($i = 0; $i < (int)$count_datas; $i++) {
                $hook_name = Tools::getValue('hook_' . $i);
                $id_module = (int)Tools::getValue('module_' . $i);
                $extra_params = json_decode(Tools::getValue('params_'.$i,''),true );
                if ($hook_name && Validate::isHookName($hook_name) && $id_module && Module::getInstanceById($id_module) && Ets_superspeed_cache_page::getDynamicHookModule($id_module,$hook_name)) {
                    $params = Tools::getAllValues();
                    $controller = Tools::getValue('controller');
                    if ($controller=='product' && ($id_product = (int)Tools::getValue('id_product')) && ($product = new Product($id_product,true,$this->context->language->id)))
                    {
                        $params['product'] = $product;
                    }
                    if ($controller=='category' && ($id_category = (int)Tools::getValue('id_category')) && ($category = new Category($id_category,$this->context->language->id)))
                    {
                        $params['category'] = $category;
                    }
                    if($extra_params && is_array($extra_params))
                        $params = array_merge($params,$extra_params);
                    $data[$id_module . $hook_name.(isset($extra_params['id_product']) ? '_'.$extra_params['id_product']:'').(isset($extra_params['id_product_attribute']) ? '_'.$extra_params['id_product_attribute']:'').(isset($extra_params['type']) ? '_'.$extra_params['type']:'')] = Hook::exec($hook_name, $params, $id_module);
                }
                elseif(Tools::strtolower($hook_name)=='cetemplate')
                {
                    $hid = Configuration::get('posthemeoptionsheader_template') ? : 7;
                    $params =array(
                        'id' => $hid,
                    );
                    $data[$id_module . $hook_name] = Hook::exec($hook_name, $params, $id_module);
                }
            }
        }
        if(Module::isEnabled('creativeelements') &&  ($creativeelements = Module::getInstanceByName('creativeelements')) && ($CE_HEADER = Configuration::get('CE_HEADER')) && ($uid = new CE\UId($CE_HEADER, CE\UId::THEME, $this->context->language->id, $this->context->shop->id)))
        {
            if(method_exists('Creativeelements','renderTheme'))
            {
                $data['creativeelements_header'] = array(
                    'content' => Creativeelements::renderTheme($uid),
                    'uid' => (string)$uid,
                );
            }
            elseif(method_exists($creativeelements,'hookCETemplate'))
            {
                $data['creativeelements_header'] = array(
                    'content' => $creativeelements->hookCETemplate(['uid'=>$uid]),
                    'uid' => (string)$uid,
                );
            }

        }
        if(Module::isEnabled('tdshoppingcart'))
        {
            $data['cart_products_count'] = $this->context->cart->nbProducts();
        }
        if($this->checkAlwaysLoadContent())
        {
            $id_customer = (int)Context::getContext()->customer->id;
            $id_group = null;
            if ($id_customer) {
                $id_group = Customer::getDefaultGroupId((int)$id_customer);
            }
            if (!$id_group) {
                $id_group = (int)Group::getCurrent()->id;
            }
            $group= new Group($id_group);
            if($group->price_display_method)
                $tax=false;
            else
                $tax=true;
            $controller = Tools::getValue('controller');
            if($controller=='product')
            {
                $id_product = (int)Tools::getValue('id_product');
                if(($product = new Product($id_product,true,$this->context->language->id)) && Validate::isLoadedObject($product) )
                {

                    $price = $product->getPrice($tax);
                    if($price >0 && $specificPrice = $product->specificPrice)
                    {
                        $price_without_reduction = Product::getPriceStatic(
                            (int) $id_product,
                            $tax,
                            0,
                            2,
                            null,
                            false,
                            false,
                            1
                        );
                    }
                    else
                        $price_without_reduction = 0;
                    $data['specificPrice'] = isset($specificPrice) && $specificPrice  ? true :false;
                    $data['percentage_specific'] = isset($specificPrice) && $specificPrice && $specificPrice['reduction_type'] =='percentage' ? Tools::ps_round($specificPrice['reduction'],2)*100:false;
                    $data['product_price'] = $price > 0 ? Tools::displayPrice($price):'';
                    $data['price_without_reduction'] = $price_without_reduction ? Tools::displayPrice($price_without_reduction):'';
                }
            }
            if(Validate::isControllerName($controller) && ($dataProducts = Tools::getValue('dataProducts')) && ($dataProducts = explode(',',$dataProducts)))
            {
                $products = array();
                foreach($dataProducts as $dataProduct)
                {
                    $ids = explode('-',$dataProduct);
                    $id_product = $ids && isset($ids[0]) ? $ids[0] :0;
                    $id_product_attribute = $ids && isset($ids[1]) ? $ids[1] :0;
                    $product = new Product($id_product,true,$this->context->language->id);
                    $price = $product->getPrice($tax,$id_product_attribute);
                    if($price > 0 && $specificPrice = $product->specificPrice)
                    {
                        $price_without_reduction = Product::getPriceStatic(
                            (int) $id_product,
                            $tax,
                            $id_product_attribute,
                            2,
                            null,
                            false,
                            false,
                            1
                        );
                    }
                    else
                        $price_without_reduction = 0;
                    $products[] = array(
                        'id_product' => $id_product,
                        'id_product_attribute' => $id_product_attribute,
                        'specificPrice' => isset($specificPrice) && $price > 0 && $specificPrice  ? true :false,
                        'percentage_specific' =>isset($specificPrice) && $price > 0 && $specificPrice && $specificPrice['reduction_type'] =='percentage' ? Tools::ps_round($specificPrice['reduction'],4)*100:false,
                        'price' =>$price > 0 ? Tools::displayPrice($price):'',
                        'price_without_reduction' => $price_without_reduction > 0 && $price > 0 ? Tools::displayPrice($price_without_reduction):'',
                    );
                }
                $data['dataProducts'] = $products;
            }

        }
        ob_clean();
        header('X-Robots-Tag: noindex, nofollow', true);
        die(json_encode($data));
    }
    public function checkAlwaysLoadContent(){
        return (int)Configuration::get('ETS_DYNAMIC_LOAD_PRICES');
    }
    public function getTextLang($text, $lang, $file = '')
    {
        return Translate::getModuleTranslation(
            $this,
            $text,
            $this->name,
            null,
            false,
            isset($lang['locale']) ? $lang['locale']:null
        );
    }
    public function rmDir($directory)
    {
        Ets_ss_class_cache::getInstance()->rmDir($directory);
        return true;
    }
    public static function validateArray($array,$validate='isCleanHtml')
    {
        if(!is_array($array))
            return false;
        if(method_exists('Validate',$validate))
        {
            if($array && is_array($array))
            {
                $ok= true;
                foreach($array as $val)
                {
                    if(!is_array($val))
                    {
                        if($val && !Validate::$validate($val))
                        {
                            $ok= false;
                            break;
                        }
                    }
                    else
                        $ok = self::validateArray($val,$validate);
                }
                return $ok;
            }
        }
        return true;
    }
    public function renderForm($inputs,$submit,$title,$configTabs=array())
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $title,
                    'icon' => ''
                ),
                'input' => $inputs,
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );
        $controller = Tools::getValue('controller');
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->id = $this->id;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->submit_action = $submit;
        $helper->currentIndex = $this->context->link->getAdminLink($controller, false);
        $helper->token = Tools::getAdminTokenLite($controller);
        $language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $language->id;
        $helper->tpl_vars = array(
            'base_url' => $this->context->shop->getBaseURL(),
            'language' => array(
                'id_lang' => $language->id,
                'iso_code' => $language->iso_code
            ),
            'PS_ALLOW_ACCENTED_CHARS_URL', (int)Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL'),
            'fields_value' => $this->getFieldsValues($inputs),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'link' => $this->context->link,
            'configTabs' => $configTabs,
            'current_currency'=> $this->context->currency,
        );
        $this->fields_form = array();
        return $helper->generateForm(array($fields_form));
    }
    public function getFieldsValues($formFields)
    {
        $values = array();
        $languages = Language::getLanguages();
        foreach ($formFields as $field) {
            if ($field['name'] == 'ETS_SPEED_SMARTY_CACHE') {
                $values['ETS_SPEED_SMARTY_CACHE'] = Configuration::get('PS_SMARTY_FORCE_COMPILE') == 2 ? false : true;
            } elseif ($field['name'] == 'PS_SMARTY_CACHE') {
                $PS_SMARTY_CACHE = (int)Tools::getValue('PS_SMARTY_CACHE', Configuration::get('PS_SMARTY_CACHE'));
                $values['PS_SMARTY_CACHE'] = $PS_SMARTY_CACHE;
            } elseif(isset($field['name']) && $field['name']) {
                if (!isset($field['lang'])) {
                    if ($field['type'] == 'checkbox') {
                        $values[$field['name']] = Tools::getValue($field['name'], explode(',', isset($field['isGlobal']) && $field['isGlobal'] ?  Configuration::getGlobalValue($field['name']) : Configuration::get($field['name']) ));
                    } else
                        $values[$field['name']] = Tools::getValue($field['name'], isset($field['isGlobal']) && $field['isGlobal'] ?  Configuration::getGlobalValue($field['name']) : Configuration::get($field['name']));
                } else {
                    foreach ($languages as $language) {
                        $values[$field['name']][$language['id_lang']] = Tools::getValue($field['name'] . '_' . $language['id_lang'], Configuration::get($field['name'], $language['id_lang']));
                    }
                }
            }
        }
        return $values;
    }
    public function saveSubmit($inputs)
    {
        $this->_postValidation($inputs);
        if (!count($this->_errors)) {
            $languages = Language::getLanguages(false);
            $id_lang_default = Configuration::get('PS_LANG_DEFAULT');
            if($inputs)
            {
                foreach($inputs as $input)
                {
                    if($input['type']!='html' && $input['name'] && $input['name']!='live_script' && $input['name']!='dynamic_modules' )
                    {
                        if(isset($input['lang']) && $input['lang'])
                        {
                            $values = array();
                            foreach($languages as $language)
                            {
                                $value_default = Tools::getValue($input['name'].'_'.$id_lang_default);
                                $value = Tools::getValue($input['name'].'_'.$language['id_lang']);
                                $values[$language['id_lang']] = ($value && Validate::isCleanHtml($value,true)) || !isset($input['required']) ? $value : (Validate::isCleanHtml($value_default,true) ? $value_default :'');
                            }
                            Configuration::updateValue($input['name'],$values,isset($input['autoload_rte']) && $input['autoload_rte'] ? true : false);
                        }
                        else
                        {

                            if($input['type']=='checkbox')
                            {
                                $val = Tools::getValue($input['name'],array());
                                if(is_array($val) && self::validateArray($val))
                                {
                                    Configuration::updateValue($input['name'],implode(',',$val));
                                }
                            }
                            else
                            {
                                $val = Tools::getValue($input['name']);
                                if(Validate::isCleanHtml($val))
                                    Configuration::updateValue($input['name'],$val);
                            }

                        }
                    }

                }
            }
            return true;
        } else {
            if(Tools::isSubmit('ajax'))
            {
                die(
                json_encode(
                    array(
                        'errors' => $this->displayError($this->_errors),
                    )
                )
                );
            }
            $this->_html .= $this->displayError($this->_errors);
        }
        return false;
    }
    public function _postValidation($inputs)
    {
        $languages = Language::getLanguages(false);
        $id_lang_default = Configuration::get('PS_LANG_DEFAULT');

        foreach ($inputs as $input) {
            if ($input['type'] === 'html') {
                continue;
            }

            if (isset($input['lang']) && $input['lang']) {
                $val_default = Tools::getValue($input['name'].'_'.$id_lang_default);

                if (isset($input['required']) && $input['required']) {
                    if (!$val_default) {
                        $this->_errors[] = sprintf($this->l('%s is required'), $input['label']);
                    } elseif (isset($input['validate']) && ($validate = $input['validate']) && method_exists('Validate', $validate) && !Validate::{$validate}($val_default, true)) {
                        $this->_errors[] = sprintf($this->l('%s is not valid'), $input['label']);
                    } elseif (!Validate::isCleanHtml($val_default, true)) {
                        $this->_errors[] = sprintf($this->l('%s is not valid'), $input['label']);
                    } else {
                        foreach ($languages as $language) {
                            $value = Tools::getValue($input['name'].'_'.$language['id_lang']);
                            if ($value && isset($input['validate']) && ($validate = $input['validate']) && method_exists('Validate', $validate) && !Validate::{$validate}($value, true)) {
                                $this->_errors[] = sprintf($this->l('%s is not valid in %s'), $input['label'], $language['iso_code']);
                            } elseif ($value && !Validate::isCleanHtml($value, true)) {
                                $this->_errors[] = sprintf($this->l('%s is not valid in %s'), $input['label'], $language['iso_code']);
                            }
                        }
                    }
                } else {
                    foreach ($languages as $language) {
                        $value = Tools::getValue($input['name'].'_'.$language['id_lang']);
                        if ($value && isset($input['validate']) && ($validate = $input['validate']) && method_exists('Validate', $validate) && !Validate::{$validate}($value, true)) {
                            $this->_errors[] = sprintf($this->l('%s is not valid in %s'), $input['label'], $language['iso_code']);
                        } elseif ($value && !Validate::isCleanHtml($value, true)) {
                            $this->_errors[] = sprintf($this->l('%s is not valid in %s'), $input['label'], $language['iso_code']);
                        }
                    }
                }
            } else {
                if ($input['type'] === 'file') {
                    if (isset($input['required']) && $input['required']) {
                        if (!isset($_FILES[$input['name']]) || !isset($_FILES[$input['name']]['name']) || !$_FILES[$input['name']]['name']) {
                            $this->_errors[] = sprintf($this->l('%s is required'), $input['label']);
                        } elseif (isset($_FILES[$input['name']]) && isset($_FILES[$input['name']]['name']) && isset($_FILES[$input['name']]['size']) && $_FILES[$input['name']]['size'] && $_FILES[$input['name']]['name']) {
                            $file_name = basename($_FILES[$input['name']]['name']);
                            $file_size = $_FILES[$input['name']]['size'];
                            $max_file_size = Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE') * 1024 * 1024;
                            $type = Tools::strtolower(Tools::substr(strrchr($file_name, '.'), 1));

                            if (isset($input['is_image']) && $input['is_image']) {
                                $file_types = array('jpg', 'png', 'gif', 'jpeg');
                            } else {
                                $file_types = array('jpg', 'png', 'gif', 'jpeg', 'zip', 'doc', 'docx');
                            }

                            if (!in_array($type, $file_types)) {
                                $this->_errors[] = sprintf($this->l('The file name "%s" is not in the correct format, accepted formats: %s'), $file_name, '.' . trim(implode(', .', $file_types), ', .'));
                            }

                            if ($file_size > $max_file_size) {
                                $this->_errors[] = sprintf($this->l('The file name "%s" is too large. Limit: %s'), $file_name, Tools::ps_round($max_file_size / 1048576, 2) . 'MB');
                            }
                        }
                    }
                } else {
                    $val = Tools::getValue($input['name']);

                    if ($input['type'] !== 'checkbox') {
                        if ($val === '' && isset($input['required']) && $input['required']) {
                            $this->_errors[] = sprintf($this->l('%s is required'), $input['label']);
                        } elseif ($val !== '' && isset($input['validate']) && ($validate = $input['validate']) && method_exists('Validate', $validate) && !Validate::{$validate}($val)) {
                            $this->_errors[] = sprintf($this->l('%s is not valid'), $input['label']);
                        } elseif ($val !== '' && $val <= 0 && isset($input['validate']) && ($validate = $input['validate']) && $validate === 'isUnsignedInt') {
                            $this->_errors[] = sprintf($this->l('%s is not valid'), $input['label']);
                        }
                    } else {
                        if (!$val && isset($input['required']) && $input['required']) {
                            $this->_errors[] = sprintf($this->l('%s is required'), $input['label']);
                        } elseif ($val && !self::validateArray($val, isset($input['validate']) ? $input['validate'] : '')) {
                            $this->_errors[] = sprintf($this->l('%s is not valid'), $input['label']);
                        }
                    }
                }
            }
        }
    }

    public static function getQantityOptimize()
    {
        return (int)Tools::getValue('ETS_SPEED_UPDATE_QUALITY', Configuration::getGlobalValue('ETS_SPEED_UPDATE_QUALITY'));
    }
    private function renderCartCreativeElement()
    {
        if(defined('_CE_PATH_') && extension_loaded('dom')){
            @error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED & ~E_WARNING & ~E_NOTICE);
            $outputs = [];
            \CE\add_filter('elementor/frontend/the_content', static function($content) use (&$outputs){
                if(Tools::strpos($content, 'elementor-cart__wrapper') !== false){
                    $outputs[] = $content;
                }
            });
            Hook::exec('overrideLayoutTemplate');
            $ceCartContent = null;
            foreach ($outputs as $output) {
                $output = preg_replace_callback('/[\x{80}-\x{10FFFF}]/u', static function ($match) {
                    list($utf8) = $match;
                    return mb_convert_encoding($utf8, 'HTML-ENTITIES', 'UTF-8');
                }, $output);
                $dom = new \DOMDocument();
                $dom->loadHTML($output);
                $selector = new \DOMXPath($dom);
                $list = $selector->query('.//*[contains(@class,"elementor-cart__wrapper")]');
                if($list->count() > 0){
                    $node = $list->item(0);
                    $ceCartContent = $node->ownerDocument->saveHTML($node);
                    break;
                }
            }
            return $ceCartContent;
        }
        return null;
    }
    public function replaceOverridesBeforeInstall()
    {
        // Ensure this runs only for versions before 8.0
        if (version_compare(_PS_VERSION_, '8.0', '<')) {
            $filePath = realpath(dirname(__FILE__).'/override/classes/Link.php');
            // Check if the file exists before attempting to read it
            if (file_exists($filePath)) {
                $file_link_content = Tools::file_get_contents($filePath);

                // Define the search and replace arrays
                $search = array(
                    'public function getImageLink($name, $ids, $type = null, string $extension = \'jpg\')'
                );
                $replace = array(
                    'public function getImageLink($name, $ids, $type = null, $extension = \'jpg\')'
                );

                // Replace content safely
                $file_link_content = str_replace($search, $replace, $file_link_content);

                // Write the updated content back to the file
                $result = @file_put_contents($filePath, $file_link_content);

                // Handle any potential errors
                if ($result === false) {
                    // Log or handle the error appropriately
                    error_log('Failed to write to file: ' . $filePath);
                }
            } else {
                // Log or handle the error if the file does not exist
                error_log('File not found: ' . $filePath);
            }
        }
    }
    public function replaceOverridesAfterInstall()
    {
        // Ensure this runs only for versions before 8.0
        if (version_compare(_PS_VERSION_, '8.0', '<')) {
            // Safely construct the file path
            $filePath = realpath(dirname(__FILE__).'/override/classes/Link.php');

            // Check if the file exists before attempting to read it
            if (file_exists($filePath)) {
                $file_link_content = Tools::file_get_contents($filePath);

                // Define the search and replace arrays
                $search = array(
                    'public function getImageLink($name, $ids, $type = null, $extension = \'jpg\')'
                );
                $replace = array(
                    'public function getImageLink($name, $ids, $type = null, string $extension = \'jpg\')'
                );

                // Replace content safely
                $file_link_content = str_replace($search, $replace, $file_link_content);

                // Write the updated content back to the file
                $result = @file_put_contents($filePath, $file_link_content);

                // Handle any potential errors
                if ($result === false) {
                    // Log or handle the error appropriately
                    error_log('Failed to write to file: ' . $filePath);
                }
            } else {
                // Log or handle the error if the file does not exist
                error_log('File not found: ' . $filePath);
            }
        }
    }
    public function replaceOverridesOtherModuleAfterInstall()
    {
        if(Module::isInstalled('ets_multilangimages') && ($ets_multilangimages = Module::getInstanceByName('ets_multilangimages')) && method_exists($ets_multilangimages,'replaceOverridesAfterInstall'))
        {
            $ets_multilangimages->replaceOverridesAfterInstall();
        }
    }
    public function replaceOverridesOtherModuleBeforeInstall()
    {
        if(Module::isInstalled('ets_multilangimages') && ($ets_multilangimages = Module::getInstanceByName('ets_multilangimages')) && method_exists($ets_multilangimages,'replaceOverridesBeforeInstall'))
        {
            $ets_multilangimages->replaceOverridesBeforeInstall();
        }
    }
    public function fixOverrideConflict(){
        require_once(dirname(__FILE__) . '/classes/OverrideUtil');
        $class= 'Ets_superspeed_overrideUtil';
        $method = 'resolveConflict';
        call_user_func_array(array($class, $method),array($this));
        return true;
    }
    public function uninstallOverrides(){
        $this->replaceOverridesBeforeInstall();
        $this->replaceOverridesOtherModuleBeforeInstall();
        if(parent::uninstallOverrides())
        {
            require_once(dirname(__FILE__) . '/classes/OverrideUtil');
            $class= 'Ets_superspeed_overrideUtil';
            $method = 'restoreReplacedMethod';
            call_user_func_array(array($class, $method),array($this));
            $this->replaceOverridesAfterInstall();
            $this->replaceOverridesOtherModuleAfterInstall();
            return true;
        }
        $this->replaceOverridesAfterInstall();
        $this->replaceOverridesOtherModuleAfterInstall();
        return false;
    }
    public function installOverrides()
    {
        $this->replaceOverridesBeforeInstall();
        $this->replaceOverridesOtherModuleBeforeInstall();
        require_once(dirname(__FILE__) . '/classes/OverrideUtil');
        $class= 'Ets_superspeed_overrideUtil';
        if(parent::installOverrides())
        {
            call_user_func_array(array($class, 'onModuleEnabled'),array($this));
            $this->replaceOverridesAfterInstall();
            $this->replaceOverridesOtherModuleAfterInstall();
            return true;
        }
        $this->replaceOverridesAfterInstall();
        $this->replaceOverridesOtherModuleAfterInstall();
        return false;
    }
    public function disable($force_all = false)
    {
        $res = parent::disable($force_all);
        if($res && !$force_all && Ets_superspeed_defines::checkEnableOtherShop($this->id))
        {
            if(property_exists('Tab','enabled') && method_exists($this, 'get') && $dispatcher = $this->get('event_dispatcher')){
                /** @var \Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher|\Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher */
                $dispatcher->addListener(\PrestaShopBundle\Event\ModuleManagementEvent::DISABLE, function (\PrestaShopBundle\Event\ModuleManagementEvent $event) {
                    Ets_superspeed_defines::activeTab($this->name);
                });
            }
            if($this->getOverrides() != null)
            {
                try {
                    $this->installOverrides();
                }
                catch (Exception $e)
                {
                    if($e)
                    {
                        //
                    }
                }
            }
        }
        return $res;
    }
    public function enable($force_all = false)
    {
        if(!$force_all && Ets_superspeed_defines::checkEnableOtherShop($this->id) && $this->getOverrides() != null)
        {
            try {
                $this->uninstallOverrides();
            }
            catch (Exception $e)
            {
                if($e)
                {
                    //
                }
            }
        }
        $this->checkOverrideDir();
        return $this->fixOverrideConflict() && parent::enable($force_all);
    }
    /**
     * @param string $path
     * @param int $permission
     *
     * @return bool
     *
     * @throws \PrestaShopException
     */
    private function safeMkDir($path, $permission = 0755)
    {
        if (!@mkdir($concurrentDirectory = $path, $permission) && !is_dir($concurrentDirectory)) {
            throw new \PrestaShopException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }

        return true;
    }
    private function checkOverrideDir()
    {
        if (defined('_PS_OVERRIDE_DIR_')) {
            $psOverride = realpath(_PS_OVERRIDE_DIR_) . DIRECTORY_SEPARATOR;
            if ($psOverride === false || !is_dir($psOverride)) {
                throw new Exception('Override directory is invalid or inaccessible.');
            }

            $base = str_replace('/', DIRECTORY_SEPARATOR, $this->getLocalPath() . 'override');
            if (!is_dir($base)) {
                throw new Exception('Base directory is invalid or inaccessible.');
            }

            $iterator = new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS);
            /** @var RecursiveIteratorIterator|\SplFileInfo[] $iterator */
            $iterator = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
            $iterator->setMaxDepth(4);

            foreach ($iterator as $k => $item) {
                if (!$item->isDir()) {
                    continue;
                }

                $path = str_replace($base . DIRECTORY_SEPARATOR, '', $item->getPathname());
                $fullPath = $psOverride . $path;
                if (Tools::strpos($fullPath, $psOverride) !== 0) {
                    throw new Exception('realpath($fullPath)'.realpath($fullPath).'$psOverride'.$psOverride.'Path traversal detected.'.$fullPath.'Tools::strpos($fullPath, $psOverride)'.Tools::strpos($fullPath, $psOverride));
                }

                if (!is_dir($fullPath)) {
                    $this->safeMkDir($fullPath);
                    @touch($fullPath . DIRECTORY_SEPARATOR . '_do_not_remove');
                }
            }

            $indexPath = $psOverride . 'index.php';
            if (!file_exists($indexPath)) {
                Tools::copy($this->getLocalPath() . 'index.php', $indexPath);
            }
        }
    }

    public function renderList($listData)
    {
        if(isset($listData['fields_list']) && $listData['fields_list'])
        {
            foreach($listData['fields_list'] as $key => &$val)
            {
                $value_key = (string)Tools::getValue($key);
                $value_key_max = (string)Tools::getValue($key.'_max');
                $value_key_min = (string)Tools::getValue($key.'_min');
                if(isset($val['filter']) && $val['filter'] && ($val['type']=='int' || $val['type']=='date'))
                {
                    if(Tools::isSubmit('ets_sp_submit_'.$listData['name']))
                    {
                        $val['active']['max'] =  trim($value_key_max);
                        $val['active']['min'] =  trim($value_key_min);
                    }
                    else
                    {
                        $val['active']['max']='';
                        $val['active']['min']='';
                    }
                }
                elseif(!Tools::isSubmit('del') && Tools::isSubmit('ets_sp_submit_'.$listData['name']))
                    $val['active'] = trim($value_key);
                else
                    $val['active']='';
            }
        }
        $this->smarty->assign($listData);
        return $this->display(__FILE__, 'list_helper.tpl');
    }
    public function getFilterParams($field_list,$table='')
    {
        $params = '';
        if($field_list)
        {
            if(Tools::isSubmit('ets_sp_submit_'.$table))
                $params .='&ets_sp_submit_'.$table.='=1';
            foreach($field_list as $key => $val)
            {
                $value_key = (string)Tools::getValue($key);
                $value_key_max = (string)Tools::getValue($key.'_max');
                $value_key_min = (string)Tools::getValue($key.'_min');
                if($value_key!='')
                {
                    $params .= '&'.$key.'='.urlencode($value_key);
                }
                if($value_key_max!='')
                {
                    $params .= '&'.$key.'_max='.urlencode($value_key_max);
                }
                if($value_key_min!='')
                {
                    $params .= '&'.$key.'_min='.urlencode($value_key_min);
                }
            }
            unset($val);
        }
        return $params;
    }
    public function getLinkHomePage()
    {
        $id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        $url = $this->context->link->getPageLink('index', null, $id_lang);
        $dispatcher = Dispatcher::getInstance();
        $url_path = $dispatcher->createUrl('index', $id_lang);
        if (filter_var($url, FILTER_VALIDATE_URL) === false || (!preg_match('/^https?:\/\//i', $url) && !preg_match('/^http?:\/\//i', $url)) ) {
            throw new Exception('Invalid URL generated.');
        }
        $url_path = rtrim(preg_replace('/[^a-zA-Z0-9\/_\-]/', '', $url_path), '/');
        return rtrim($url, $url_path);
    }
}
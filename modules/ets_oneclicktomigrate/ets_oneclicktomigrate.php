<?php
/**
 * 2007-2019 ETS-Soft ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 * @author ETS-Soft <etssoft.jsc@gmail.com>
 * @copyright  2007-2019 ETS-Soft ETS-Soft
 * @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

if (!defined('_PS_VERSION_'))
    exit;
include_once(_PS_MODULE_DIR_ . 'ets_oneclicktomigrate/classes/ExtraExport.php');
include_once(_PS_MODULE_DIR_ . 'ets_oneclicktomigrate/classes/DataExport.php');
include_once(_PS_MODULE_DIR_ . 'ets_oneclicktomigrate/classes/ExtraImport.php');
include_once(_PS_MODULE_DIR_ . 'ets_oneclicktomigrate/classes/DataImport.php');
include_once(_PS_MODULE_DIR_ . 'ets_oneclicktomigrate/classes/ImportHistory.php');
include_once(_PS_MODULE_DIR_ . 'ets_oneclicktomigrate/classes/ExportHistory.php');
if (!class_exists('Uploader'))
    include_once(_PS_MODULE_DIR_ . 'ets_oneclicktomigrate/classes/Uploader.php');
if (version_compare(_PS_VERSION_, '1.5', '<') && !class_exists('Context'))
    require_once(_PS_MODULE_DIR_ . '/ets_oneclicktomigrate/backward_compatibility/Context.php');

class Ets_oneclicktomigrate extends Module
{
    private $errorMessage;
    public $configs;
    public $baseAdminPath;
    private $_html;
    public $emotions = array();
    public $url_module;
    public $errors = array();
    public $tables;
    public $categoryDropDown;
    private $depthLevel = false;
    private $excludedCats = array();
    private $categoryPrefix = '- ';
    private $cmsCategoryDropDown;
    public $pres_version;
    public $context;

    public function __construct()
    {
        $this->name = 'ets_oneclicktomigrate';
        $this->tab = 'front_office_features';
        $this->version = '1.2.7';
        $this->author = 'ETS-Soft';
        $this->need_instance = 0;
        $this->secure_key = Tools::encrypt($this->name);
        $this->bootstrap = true;
        $this->module_key = '8c4686a2fe6d643fe0dea93e2e0a7082';
        $this->author_address = '0xd81C21A85a637315C623D9c1F9D4f5Bb3144A617';
        if (version_compare(_PS_VERSION_, '1.7', '>='))
            $this->pres_version = 1.7;
        elseif (version_compare(_PS_VERSION_, '1.7', '<') && version_compare(_PS_VERSION_,
                '1.6', '>='))
            $this->pres_version = 1.6;
        elseif (version_compare(_PS_VERSION_, '1.6', '<') && version_compare(_PS_VERSION_,
                '1.5', '>='))
            $this->pres_version = 1.5;
        elseif (version_compare(_PS_VERSION_, '1.5', '<') && version_compare(_PS_VERSION_,
                '1.4', '>='))
            $this->pres_version = 1.4;
        else
            $this->pres_version = 1.3;
        parent::__construct();
        $this->context = Context::getContext();
        $this->url_module = $this->_path;
        $this->displayName = $this->l('One click to migrate');
        $this->description = $this->l('Migrate data between Prestashop websites, Migrate Prestashop to latest version');
        if (isset($this->context->controller->controller_type) && $this->context->
            controller->controller_type == 'admin')
            $this->baseAdminPath = $this->context->link->getAdminLink('AdminModules') . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $this->shortlink = 'https://mf.short-link.org/';
        if(Tools::getValue('configure')==$this->name && Tools::isSubmit('othermodules'))
        {
            $this->displayRecommendedModules();
        }
        $this->tables = array(
            'employee',
            'shop_group',
            'shop',
            'lang',
            'currency',
            'category',
            'image',
            'product_attribute',
            'attribute',
            'attribute_group',
            'feature_value',
            'feature',
            'product',
            'customer',
            'group',
            'supplier',
            'manufacturer',
            'tax_rule',
            'tax_rules_group',
            'tax',
            'specific_price_rule',
            'cart_rule',
            'cart_rule_product_rule_group',
            'cart_rule_product_rule',
            'cart_rule_product_rule_value',
            'carrier',
            'address',
            'specific_price',
            'order_state',
            'cart',
            'orders',
            'order_invoice',
            'order_slip',
            'order_detail',
            'order_carrier',
            'order_cart_rule',
            'order_history',
            'order_message',
            'order_payment',
            'order_return',
            'range_price',
            'range_weight',
            'delivery',
            'zone',
            'country',
            'state',
            'reference',
            'stock',
            'stock_available',
            'warehouse',
            'warehouse_product_location',
            'cms_category',
            'cms',
            'message',
            'discount',
            'discount_type',
            'customization_field',
            'customization',
            'tag',
            'contact',
            'customer_thread',
            'customer_message');
        $this->context->smarty->assign(array('mod_dr_onclickmigrate' => $this->_path,));
    }

    /**
     * @see Module::install()
     */
    public function install()
    {
        if ($this->pres_version == 1.4) {
            if (parent::install() && $this->_installDb()) {
                chmod(dirname(__FILE__) . '/ajax.php', 0644);
                chmod(dirname(__FILE__) . '/ajax_init.php', 0644);
                chmod(dirname(__FILE__) . '/../ets_oneclicktomigrate', 0755);
                return true;
            } else
                return false;
        } else {
            if (parent::install() && $this->registerHook('displayBackOfficeHeader') && $this->
                registerHook('displayBackOfficeFooter') && $this->registerHook('datamasterLeftBlok') &&
                $this->_installDb() && $this->_installTabs()) {
                chmod(dirname(__FILE__) . '/ajax.php', 0644);
                chmod(dirname(__FILE__) . '/ajax_init.php', 0644);
                chmod(dirname(__FILE__) . '/../ets_oneclicktomigrate', 0755);
                return true;
            } else
                return false;
        }
    }

    /**
     * @see Module::uninstall()
     */
    public function uninstall()
    {
        return parent::uninstall() && $this->_uninstallTabs() && $this->_uninstallDb();
    }

    public function _installDb()
    {
        $this->context->cookie->zip_file_name = '';
        $this->context->cookie->write();
        Configuration::updateValue('ETS_DATAMASTER_NEW_PASSWD', 1);
        Configuration::updateValue('ETS_DATAMASTER_DIVIDE_FILE', 0);
        Configuration::updateValue('ETS_DT_NUMBER_RECORD', 500);
        $data = 'shops,employees,categories,customers,manufactures,suppliers,carriers,cart_rules,catelog_rules,vouchers,products,orders,CMS_categories,CMS,messages';
        Configuration::updateValue('ETS_DATAMASTER_EXPORT', $data);
        $res = Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_export_history` ( 
        `id_export_history` INT(11) NOT NULL AUTO_INCREMENT, 
        `file_name` VARCHAR(222) NOT NULL ,
        `content` TEXT NOT NULL, 
        `date_export` datetime NOT NULL,
        PRIMARY KEY (`id_export_history`)) ENGINE = InnoDB;');
        $res &= Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_import_history` ( 
        `id_import_history` INT(11) NOT NULL AUTO_INCREMENT, 
        `file_name` VARCHAR(222) NOT NULL ,`data` TEXT NOT NULL,
        `id_category_default` INT(11) NOT NULL,
        `id_manufacture` INT(11) NOT NULL,
        `id_supplier` INT(11) NOT NULL,
        `id_category_cms` INT(11) NOT NULL,
        `import_multi_shop` INT(11) NOT NULL,
        `delete_before_importing` INT(11) NOT NULL,
        `force_all_id_number` INT(11) NOT NULL,
        `content` TEXT NOT NULL, 
        `currentindex` INT(11) NOT NULL,
        `number_import` INT(11) NOT NULL,
        `number_import2` INT(11) NOT NULL,
        `cookie_key` text,
        `date_import` datetime NOT NULL,
        PRIMARY KEY (`id_import_history`) ) ENGINE = InnoDB');
        $res &= Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_datamaster_customer_pasword` ( 
        `id_ets_datamaster_customer_pasword` INT(11) NOT NULL AUTO_INCREMENT , 
        `id_import_history` INT(11) NOT NULL , 
        `first_name` VARCHAR(222) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL , 
        `last_name` VARCHAR(222) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL , 
        `email` VARCHAR(222) NOT NULL ,
        `passwd` VARCHAR(222) NOT NULL ,  
        PRIMARY KEY (`id_ets_datamaster_customer_pasword`)) ENGINE = InnoDB');
        $res &= Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_datamaster_employee_pasword` ( 
        `id_ets_datamaster_employee_pasword` INT(11) NOT NULL AUTO_INCREMENT , 
        `id_import_history` INT(11) NOT NULL , 
        `first_name` VARCHAR(222) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL , 
        `last_name` VARCHAR(222) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL , 
        `email` VARCHAR(222) NOT NULL , 
        `passwd` VARCHAR(222) NOT NULL , 
        PRIMARY KEY (`id_ets_datamaster_employee_pasword`)) ENGINE = InnoDB');
        if ($this->tables) {
            foreach ($this->tables as $table) {
                $res &= Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_' . pSQL($table) . '_import`(
                `id_import` INT(11) NOT NULL AUTO_INCREMENT , 
                `id_old` INT(11) NOT NULL , 
                `id_new` INT(11) NOT NULL,
                `id_import_history` INT(11) NOT NULL,
                PRIMARY KEY (`id_import`) ) ENGINE = InnoDB');
            }
        }
        return $res;
    }

    private function _installTabs()
    {
        if ($this->pres_version == 1.4)
            return true;
        $languages = Language::getLanguages(false);
        $tab = new Tab();
        $tab->class_name = 'AdminDataMaster';
        $tab->module = 'ets_oneclicktomigrate';
        $tab->id_parent = 0;
        foreach ($languages as $lang) {
            $tab->name[$lang['id_lang']] = $this->l('One click to migrate');
        }
        $tab->save();
        $tabId = Tab::getIdFromClassName('AdminDataMaster');
        if ($tabId) {
            $subTabs = array(
                array(
                    'class_name' => 'AdminDataMasterGeneral',
                    'tab_name' => $this->l('Dashboard'),
                    'icon' => 'icon icon-database'),
                array(
                    'class_name' => 'AdminDataMasterExport',
                    'tab_name' => $this->l('Export data'),
                    'icon' => 'icon icon-download',
                ),
                array(
                    'class_name' => 'AdminDataMasterImport',
                    'tab_name' => $this->l('Import data'),
                    'icon' => 'icon icon-cloud-upload',
                ),
                array(
                    'class_name' => 'AdminDataMasterHistory',
                    'tab_name' => $this->l('History'),
                    'icon' => 'icon icon-history',
                ),
                array(
                    'class_name' => 'AdminDataMasterClean',
                    'tab_name' => $this->l('Clean-up'),
                    'icon' => 'icon icon-eraser',
                ),
                array(
                    'class_name' => 'AdminDataMasterHelp',
                    'tab_name' => $this->l('Help'),
                    'icon' => 'icon icon-question-circle',
                ),
            );
            foreach ($subTabs as $tabArg) {
                $tab = new Tab();
                $tab->class_name = $tabArg['class_name'];
                $tab->module = 'ets_oneclicktomigrate';
                $tab->id_parent = $tabId;
                $tab->icon = $tabArg['icon'];
                foreach ($languages as $lang) {
                    $tab->name[$lang['id_lang']] = $tabArg['tab_name'];
                }
                $tab->save();
            }
        }
        return true;
    }

    private function _uninstallTabs()
    {
        if ($this->pres_version == 1.4)
            return true;
        $tabs = array(
            'AdminDataMaster',
            'AdminDataMasterGeneral',
            'AdminDataMasterImport',
            'AdminDataMasterExport',
            'AdminDataMasterHistory',
            'AdminDataMasterHelp',
            'AdminDataMasterClean');
        if ($tabs)
            foreach ($tabs as $classname) {
                if ($tabId = Tab::getIdFromClassName($classname)) {
                    $tab = new Tab($tabId);
                    if ($tab)
                        $tab->delete();
                }
            }
        return true;
    }

    private function _uninstallDb()
    {
        foreach (glob(dirname(__file__) . '/cache/export/*.*') as $filename) {
            if ($filename != dirname(__file__) . '/cache/export/index.php')
                @unlink($filename);
        }
        foreach (glob(dirname(__file__) . '/cache/import/*.*') as $filename) {
            if ($filename != dirname(__file__) . '/cache/import/index.php')
                @unlink($filename);
        }
        foreach (glob(dirname(__file__) . '/xml/*', GLOB_ONLYDIR) as $folder) {
            foreach (glob($folder . '/*.*') as $filename) {
                @unlink($filename);
            }
            @rmdir($folder);
        }
        $res = Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ets_export_history`');
        $res &= Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ets_import_history`');
        $res &= Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ets_datamaster_customer_pasword`');
        $res &= Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ets_datamaster_employee_pasword`');
        if ($this->tables) {
            foreach ($this->tables as $table) {
                $res &= Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ets_' . pSQL($table) . '_import`');
            }
        }
        $this->context->cookie->zip_file_name = '';
        $this->context->cookie->write();
        return $res;
    }

    public function getContent()
    {
        @ini_set('display_errors', 'off');
        if (!$this->active)
            return '';
        if ($this->pres_version == 1.4) {
            if (Tools::getValue('presconnector') && Tools::getValue('zip_file_name') && Tools::getValue('ajaxPercentageExport') && Tools::getValue('link_site')) {
                $url = Tools::getValue('link_site') . (strpos(Tools::getValue('link_site'), '?') === false ? '?' : '&') . 'presconnector=1&ajaxPercentageExport=1&zip_file_name=' . Tools::getValue('zip_file_name');
                $content = Tools::file_get_contents($url);
                die($content);
            }
            if (Tools::getValue('presconnector') && Tools::getValue('pres2prestocken') && Tools::getValue('zip_file_name') && Tools::getValue('link_site')) {
                $url = Tools::getValue('link_site') . (strpos(Tools::getValue('link_site'), '?') === false ? '?' : '&') . 'presconnector=1&pres2prestocken=' . Tools::getValue('pres2prestocken') . '&zip_file_name=' . Tools::getValue('zip_file_name');
                $content = Tools::file_get_contents($url);
                if ($content) {
                    $content = Tools::jsonDecode($content);
                    if (!is_array($content))
                        $content = (array)$content;
                    if (is_array($content)) {
                        if (isset($content['link_site_connector']) && $content['link_site_connector']) {
                            die(Tools::jsonEncode($content));
                        }
                    }
                }
                die(
                Tools::jsonEncode(
                    array(
                        'tieptuc' => true,
                    )
                )
                );
            }
            include(dirname(__file__) . '/importer.php');
            if (Tools::isSubmit('ajax_percentage_import')) {
                if (ob_get_length() > 0) {
                    ob_end_clean();
                }
                $this->processAjaxImport();
            }
            if (Tools::isSubmit('ajax_change_data_import')) {
                if (ob_get_length() > 0) {
                    ob_end_clean();
                }
                $id_import_history = $this->context->cookie->id_import_history;
                $importHistory = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'ets_import_history WHERE id_import_history=' . (int)$id_import_history);
                if ($importHistory['file_name'] && file_exists(dirname(__file__) .
                        '/cache/import/' . $importHistory['file_name'] . '.zip'))
                    @unlink(dirname(__file__) . '/cache/import/' . $importHistory['file_name'] .
                        '.zip');
                foreach (glob(dirname(__file__) . '/xml/' . $importHistory['file_name'] . '/*.*') as
                         $filename) {
                    @unlink($filename);
                }
                @rmdir(dirname(__file__) . '/xml/' . $importHistory['file_name']);
                Db::getInstance()->Execute('UPDATE ' . _DB_PREFIX_ . 'ets_import_history set file_name="" WHERE id_import_history="' . (int)$id_import_history .
                    '"');
                die(Tools::jsonEncode(array('upload_form' => $this->displayFromUloadLoad(),)));
            }
            if (Tools::isSubmit('submitExport')) {
                if (ob_get_length() > 0) {
                    ob_end_clean();
                }
                $this->processExport();
            }
            if (Tools::isSubmit('ajax_percentage_export')) {
                if (ob_get_length() > 0) {
                    ob_end_clean();
                }
                $this->ajaxPercentageExport();
            }
            $step = Tools::getValue('step');
            $intro = true;
            $localIps = array(
                '127.0.0.1',
                '::1'
            );
            $baseURL = Tools::strtolower(self::getBaseModLink());
            if(!Tools::isSubmit('intro') && (in_array(Tools::getRemoteAddr(), $localIps) || preg_match('/^.*(localhost|demo|test|dev|:\d+).*$/i', $baseURL)))
                $intro = false;
            $this->context->smarty->assign(array(
                'token' => Tools::getValue('token'),
                'tabmodule' => Tools::getValue('tabmodule'),
                'dir_path' => $this->_path,
                'step' => isset($step) && (int)$step ? (int)$step : 1,
                'errors' => $this->_errors,
                'ets_datamaster_export' => Tools::isSubmit('submitExport') ? Tools::getValue('data_export', array()) : explode(',', Configuration::get('ETS_DATAMASTER_EXPORT')),
                'ets_datamaster_format' => Configuration::get('ETS_DATAMASTER_FORMAT'),
                'other_modules_link' => $this->context->link->getAdminLink('AdminModules', true) . '&configure=' . $this->name.'&othermodules=1',
                'intro' => $intro,
            ));
            $this->_html = $this->display(__file__, 'views/templates/hook/admin_left_block.tpl');
            if (!Tools::getValue('tabmodule') || Tools::getValue('tabmodule') == 'general')
                return $this->_html . $this->display(__file__, 'views/templates/hook/admin_general.tpl');
            elseif (Tools::getValue('tabmodule') == 'export') {
                $this->processAssignExport();
                return $this->_html . $this->display(__file__, 'views/templates/hook/admin_export.tpl');
            } elseif (Tools::getValue('tabmodule') == 'import') {
                $this->processAssignImport();
                return $this->_html . $this->display(__file__,
                        'views/templates/hook/admin_import.tpl');
            } elseif (Tools::getValue('tabmodule') == 'history') {
                if (Tools::isSubmit('deleteexporthistory') && Tools::isSubmit('id_export_history') &&
                    $id_export_history = Tools::getValue('id_export_history')) {
                    $file_name = Db::getInstance()->getValue('SELECT file_name FROM ' . _DB_PREFIX_ . 'ets_export_history WHERE id_export_history=' . (int)$id_export_history);
                    if (file_exists(dirname(__file__) . '/cache/export/' . $file_name))
                        @unlink(dirname(__file__) . '/cache/export/' . $file_name);
                    Db::getInstance()->Execute('DELETE FROM ' . _DB_PREFIX_ .
                        'ets_export_history WHERE id_export_history=' . (int)$id_export_history);
                    Tools::redirectAdmin('index.php?tab=AdminModules&configure=ets_oneclicktomigrate&token=' .
                        Tools::getValue('token') .
                        '&tab_module=front_office_features&module_name=ets_oneclicktomigrate&tabmodule=history&conf=1&tabhistory=export');
                }
                if (Tools::isSubmit('deleteimporthistory') && Tools::isSubmit('id_import_history') &&
                    $id_import_history = Tools::getValue('id_import_history')) {
                    $file_name = Db::getInstance()->getValue('SELECT file_name FROM ' . _DB_PREFIX_ .
                        'ets_import_history WHERE id_import_history=' . (int)$id_import_history);
                    if (file_exists(dirname(__file__) . '/cache/import/' . $file_name . '.zip'))
                        @unlink(dirname(__file__) . '/cache/import/' . $file_name . '.zip');
                    foreach (glob(dirname(__file__) . '/cache/import/' . $file_name . '/*.*') as $filename) {
                        @unlink($filename);
                    }
                    @rmdir(dirname(__file__) . '/cache/import/' . $file_name);
                    Db::getInstance()->Execute('DELETE FROM ' . _DB_PREFIX_ .
                        'ets_import_history WHERE id_import_history=' . (int)$id_import_history);
                    Tools::redirectAdmin('index.php?tab=AdminModules&configure=ets_oneclicktomigrate&token=' .
                        Tools::getValue('token') .
                        '&tab_module=front_office_features&module_name=ets_oneclicktomigrate&tabmodule=history&conf=1&tabhistory=import');
                }
                $this->assignHistory();
                return $this->_html . $this->display(__file__, 'views/templates/hook/admin_history.tpl');
            } elseif (Tools::getValue('tabmodule') == 'help') {
                return $this->_html . $this->display(__file__,
                        'views/templates/hook/admin_help.tpl');
            } elseif (Tools::getValue('tabmodule') == 'clear_up') {
                $this->processClean();
                return $this->_html . $this->display(__file__,
                        'views/templates/hook/admin_clear.tpl');
            }
        } else {
            $token = Tools::getAdminTokenLite('AdminDataMasterGeneral');
            Tools::redirectAdmin('index.php?controller=AdminDataMasterGeneral&token=' . $token);
        }
    }

    public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addCSS($this->_path . 'views/css/admin-icon.css', 'all');
        $this->context->controller->addCSS($this->_path . 'views/css/font-awesome.css', 'all');
        if (version_compare(_PS_VERSION_, '1.7.4', '>='))
            $this->context->controller->addCSS($this->_path . 'views/css/ps1.7.4.css', 'all');
        if (Tools::isSubmit('controller') && (Tools::getValue('controller') ==
                'AdminDataMasterExport' || Tools::getValue('controller') ==
                'AdminDataMasterImport' || Tools::getValue('controller') ==
                'AdminDataMasterHistory') || Tools::getValue('controller') ==
            'AdminDataMasterGeneral' || Tools::getValue('controller') ==
            'AdminDataMasterHelp' || Tools::getValue('controller') == 'AdminDataMasterClean') {
            $this->context->controller->addCSS($this->_path . 'views/css/datamaster.admin.css', 'all');
            $this->context->controller->addCSS($this->_path . 'views/css/other.css', 'all');
            if ($this->pres_version == 1.5) {
                $this->context->controller->addCSS($this->_path . 'views/css/fic14.css', 'all');
            }
            $this->context->controller->addJquery();
            if ($this->pres_version <= 1.5) {
                $this->context->controller->addJqueryUI('ui.datepicker');
            }
            $this->context->controller->addJS($this->_path . 'views/js/datamaster.admin.js');
            $this->context->controller->addJS($this->_path . 'views/js/easytimer.min.js');
            $this->context->controller->addJS($this->_path . 'views/js/tree.js');
            $this->context->controller->addJS($this->_path . 'views/js/other.js');
            return $this->display(__FILE__,'head.tpl');
        }
      
    }

    public function getNewID($table_import, $id_old)
    {
        return (int)Db::getInstance()->getValue('
            SELECT id_new FROM ' . _DB_PREFIX_ . 'ets_' . pSQL($table_import) . '_import 
            WHERE id_old = ' . (int)$id_old . ' AND id_import_history=' . (int)$this->context->cookie->id_import_history, false
        );
    }

    public function displayError($error)
    {
        $this->context->smarty->assign(array('ybc_errors' => $error,));
        return $this->display(__file__, 'views/templates/hook/errors.tpl');
    }
    public function displayRecommendedModules()
    {
        $cacheDir = dirname(__file__) . '/../../cache/'.$this->name.'/';
        $cacheFile = $cacheDir.'module-list.xml';
        $cacheLifeTime = 24;
        $cacheTime = (int)Configuration::getGlobalValue('ETS_MOD_CACHE_'.$this->name);
        $profileLinks = array(
            'en' => 'https://addons.prestashop.com/en/207_ets-soft',
            'fr' => 'https://addons.prestashop.com/fr/207_ets-soft',
            'it' => 'https://addons.prestashop.com/it/207_ets-soft',
            'es' => 'https://addons.prestashop.com/es/207_ets-soft',
        );
        if(!is_dir($cacheDir))
        {
            @mkdir($cacheDir, 0755,true);
            if ( @file_exists(dirname(__file__).'/index.php')){
                @copy(dirname(__file__).'/index.php', $cacheDir.'index.php');
            }
        }
        if(!file_exists($cacheFile) || !$cacheTime || time()-$cacheTime > $cacheLifeTime * 60 * 60)
        {
            if(file_exists($cacheFile))
                @unlink($cacheFile);
            if($xml = self::file_get_contents($this->shortlink.'ml.xml'))
            {
                $xmlData = @simplexml_load_string($xml);
                if($xmlData && (!isset($xmlData->enable_cache) || (int)$xmlData->enable_cache))
                {
                    @file_put_contents($cacheFile,$xml);
                    Configuration::updateGlobalValue('ETS_MOD_CACHE_'.$this->name,time());
                }
            }
        }
        else
            $xml = Tools::file_get_contents($cacheFile);
        $modules = array();
        $categories = array();
        $categories[] = array('id'=>0,'title' => $this->l('All categories'));
        $enabled = true;
        $iso = Tools::strtolower($this->context->language->iso_code);
        $moduleName = $this->displayName;
        $contactUrl = '';
        if($xml && ($xmlData = @simplexml_load_string($xml)))
        {
            if(isset($xmlData->modules->item) && $xmlData->modules->item)
            {
                foreach($xmlData->modules->item as $arg)
                {
                    if($arg)
                    {
                        if(isset($arg->module_id) && (string)$arg->module_id==$this->name && isset($arg->{'title'.($iso=='en' ? '' : '_'.$iso)}) && (string)$arg->{'title'.($iso=='en' ? '' : '_'.$iso)})
                            $moduleName = (string)$arg->{'title'.($iso=='en' ? '' : '_'.$iso)};
                        if(isset($arg->module_id) && (string)$arg->module_id==$this->name && isset($arg->contact_url) && (string)$arg->contact_url)
                            $contactUrl = $iso!='en' ? str_replace('/en/','/'.$iso.'/',(string)$arg->contact_url) : (string)$arg->contact_url;
                        $temp = array();
                        foreach($arg as $key=>$val)
                        {
                            if($key=='price' || $key=='download')
                                $temp[$key] = (int)$val;
                            elseif($key=='rating')
                            {
                                $rating = (float)$val;
                                if($rating > 0)
                                {
                                    $ratingInt = (int)$rating;
                                    $ratingDec = $rating-$ratingInt;
                                    $startClass = $ratingDec >= 0.5 ? ceil($rating) : ($ratingDec > 0 ? $ratingInt.'5' : $ratingInt);
                                    $temp['ratingClass'] = 'mod-start-'.$startClass;
                                }
                                else
                                    $temp['ratingClass'] = '';
                            }
                            elseif($key=='rating_count')
                                $temp[$key] = (int)$val;
                            else
                                $temp[$key] = (string)strip_tags($val);
                        }
                        if($iso)
                        {
                            if(isset($temp['link_'.$iso]) && isset($temp['link_'.$iso]))
                                $temp['link'] = $temp['link_'.$iso];
                            if(isset($temp['title_'.$iso]) && isset($temp['title_'.$iso]))
                                $temp['title'] = $temp['title_'.$iso];
                            if(isset($temp['desc_'.$iso]) && isset($temp['desc_'.$iso]))
                                $temp['desc'] = $temp['desc_'.$iso];
                        }
                        $modules[] = $temp;
                    }
                }
            }
            if(isset($xmlData->categories->item) && $xmlData->categories->item)
            {
                foreach($xmlData->categories->item as $arg)
                {
                    if($arg)
                    {
                        $temp = array();
                        foreach($arg as $key=>$val)
                        {
                            $temp[$key] = (string)strip_tags($val);
                        }
                        if(isset($temp['title_'.$iso]) && $temp['title_'.$iso])
                                $temp['title'] = $temp['title_'.$iso];
                        $categories[] = $temp;
                    }
                }
            }
        }
        if(isset($xmlData->{'intro_'.$iso}))
            $intro = $xmlData->{'intro_'.$iso};
        else
            $intro = isset($xmlData->intro_en) ? $xmlData->intro_en : false;
        $this->smarty->assign(array(
            'modules' => $modules,
            'enabled' => $enabled,
            'module_name' => $moduleName,
            'categories' => $categories,
            'img_dir' => $this->_path . 'views/img/',
            'intro' => $intro,
            'shortlink' => $this->shortlink,
            'ets_profile_url' => isset($profileLinks[$iso]) ? $profileLinks[$iso] : $profileLinks['en'],
            'trans' => array(
                'txt_must_have' => $this->l('Must-Have'),
                'txt_downloads' => $this->l('Downloads!'),
                'txt_view_all' => $this->l('View all our modules'),
                'txt_fav' => $this->l('Prestashop\'s favourite'),
                'txt_elected' => $this->l('Elected by merchants'),
                'txt_superhero' => $this->l('Superhero Seller'),
                'txt_partner' => $this->l('Module Partner Creator'),
                'txt_contact' => $this->l('Contact us'),
                'txt_close' => $this->l('Close'),
            ),
            'contactUrl' => $contactUrl,
         ));
         echo $this->display(__FILE__, 'module-list.tpl');
         die;
    }
    public static function file_get_contents($url, $use_include_path = false, $stream_context = null, $curl_timeout = 60)
    {
        if ($stream_context == null && preg_match('/^https?:\/\//', $url)) {
            $stream_context = stream_context_create(array(
                "http" => array(
                    "timeout" => $curl_timeout,
                    "max_redirects" => 101,
                    "header" => 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36'
                ),
                "ssl"=>array(
                    "allow_self_signed"=>true,
                    "verify_peer"=>false,
                    "verify_peer_name"=>false,
                ),
            ));
        }
        if (function_exists('curl_init')) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => html_entity_decode($url),
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36',
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT => $curl_timeout,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_FOLLOWLOCATION => true,
            ));
            $content = curl_exec($curl);
            curl_close($curl);
            return $content;
        } elseif (in_array(ini_get('allow_url_fopen'), array('On', 'on', '1')) || !preg_match('/^https?:\/\//', $url)) {
            return Tools::file_get_contents($url, $use_include_path, $stream_context);
        } else {
            return false;
        }
    }
    public static function getBaseModLink()
    {
        $context = Context::getContext();
        return (Configuration::get('PS_SSL_ENABLED_EVERYWHERE')?'https://':'http://').$context->shop->domain.$context->shop->getBaseURI();
    }
    public function exportContent($save = true)
    {
        $data_exports = explode(',', Configuration::get('ETS_DATAMASTER_EXPORT'));
        $multishop = (int)in_array('shops', $data_exports) && Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE');
        $contents = array();
        $totaldatas = array();
        if (in_array('shops', $data_exports)) {
            $countShop = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ .
                'shop');
            $contents[] = array(
                'title' => $this->l('Shops:'),
                'count' => $countShop,
            );
            $totaldatas['shops'] = (int)$countShop;
        }
        if (in_array('employees', $data_exports)) {
            $countEmployee = Db::getInstance()->getValue('SELECT count(*) FROM ' .
                _DB_PREFIX_ . 'employee');
            $contents[] = array(
                'title' => $this->l('Employees:'),
                'count' => $countEmployee,
            );
            $totaldatas['employees'] = (int)$countEmployee;
        }
        if (in_array('categories', $data_exports)) {
            $countCategory = Db::getInstance()->getValue('
            SELECT count(DISTINCT c.id_category) FROM ' . _DB_PREFIX_ . 'category c
            ' . (version_compare(_PS_VERSION_, '1.5', '>=') ? ' INNER JOIN ' . _DB_PREFIX_ . 'category_shop cs ON (c.id_category=cs.id_category)' : '') . (!$multishop && version_compare(_PS_VERSION_, '1.5', '>=') ? ' WHERE cs.id_shop="' . (int)$this->context->shop->id . '"' : ''));
            $contents[] = array(
                'title' => $this->l('Categories:'),
                'count' => $countCategory,
            );
            $totaldatas['categories'] = (int)$countCategory;
        }
        if (in_array('products', $data_exports)) {
            $countProduct = Db::getInstance()->getValue('SELECT count(DISTINCT p.id_product) FROM ' . _DB_PREFIX_ . 'product p
            ' . (version_compare(_PS_VERSION_, '1.5', '>=') ? ' INNER JOIN ' . _DB_PREFIX_ . 'product_shop ps ON (p.id_product=ps.id_product)' : '') . (!$multishop && version_compare(_PS_VERSION_, '1.5', '>=') ? ' WHERE ps.id_shop="' . (int)$this->context->shop->id . '"' : ''));
            $contents[] = array(
                'title' => $this->l('Products:'),
                'count' => $countProduct,
            );
            $totaldatas['products'] = (int)$countProduct;
        }
        if (in_array('customers', $data_exports)) {
            $countCustomer = Db::getInstance()->getValue('SELECT count(DISTINCT c.id_customer) FROM ' . _DB_PREFIX_ . 'customer c
            ' . (!$multishop && version_compare(_PS_VERSION_, '1.5', '>=') ? ' WHERE c.id_shop="' . (int)$this->context->shop->id . '" AND c.deleted=0' : ' WHERE c.deleted=0'));
            $contents[] = array(
                'title' => $this->l('Customers:'),
                'count' => $countCustomer,
            );
            $totaldatas['customers'] = (int)$countCustomer;
        }
        if (in_array('orders', $data_exports)) {
            $countOrder = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'orders WHERE 1' .
                (!$multishop && version_compare(_PS_VERSION_, '1.5', '>=') ? ' AND id_shop="' . (int)$this->context->shop->id . '"' : '')
                . (Tools::getValue('ETS_PRES2PRES_ORDER_FROM') ? ' AND date_add >="' . pSQL(Tools::getValue('ETS_PRES2PRES_ORDER_FROM')) . '"' : '')
                . (Tools::getValue('ETS_PRES2PRES_ORDER_TO') ? ' AND date_add <="' . pSQL(Tools::getValue('ETS_PRES2PRES_ORDER_TO')) . '"' : '')
            );
            $contents[] = array(
                'title' => $this->l('Orders:'),
                'count' => $countOrder,
            );
            $totaldatas['orders'] = (int)$countOrder;
        }
        if (in_array('manufactures', $data_exports)) {
            $countManufacturer = Db::getInstance()->getValue('SELECT count(DISTINCT m.id_manufacturer) FROM ' . _DB_PREFIX_ . 'manufacturer m
            ' . (version_compare(_PS_VERSION_, '1.5', '>=') ? ' INNER JOIN ' . _DB_PREFIX_ . 'manufacturer_shop ms ON (m.id_manufacturer=ms.id_manufacturer)' : '') . (!$multishop && version_compare(_PS_VERSION_, '1.5', '>=') ? ' WHERE ms.id_shop="' . (int)$this->context->shop->id . '"' : ''));
            $contents[] = array(
                'title' => $this->l('Manufacturers:'),
                'count' => $countManufacturer,
            );
            $totaldatas['manufactures'] = (int)$countManufacturer;
        }
        if (in_array('suppliers', $data_exports)) {
            $countSupplier = Db::getInstance()->getValue('SELECT count(DISTINCT s.id_supplier) FROM ' . _DB_PREFIX_ . 'supplier s
            ' . (version_compare(_PS_VERSION_, '1.5', '>=') ? ' INNER JOIN ' . _DB_PREFIX_ . 'supplier_shop ss ON (s.id_supplier=ss.id_supplier)' : '') . (!$multishop && version_compare(_PS_VERSION_, '1.5', '>=') ? ' WHERE ss.id_shop="' . (int)$this->context->shop->id . '"' : ''));
            $contents[] = array(
                'title' => $this->l('Suppliers:'),
                'count' => $countSupplier,
            );
            $totaldatas['suppliers'] = (int)$countSupplier;
        }
        if (in_array('carriers', $data_exports)) {
            $countCarrier = Db::getInstance()->getValue('SELECT COUNT(DISTINCT c.id_carrier) FROM ' . _DB_PREFIX_ . 'carrier c
            ' . (version_compare(_PS_VERSION_, '1.5', '>=') ? ' INNER JOIN ' . _DB_PREFIX_ . 'carrier_shop cs ON (c.id_carrier=cs.id_carrier)' : '') . (!$multishop && version_compare(_PS_VERSION_, '1.5', '>=') ? ' WHERE cs.id_shop="' . (int)$this->context->shop->id . '" AND c.deleted=0' : ' WHERE c.deleted=0'));
            $contents[] = array(
                'title' => $this->l('Carriers:'),
                'count' => $countCarrier,
            );
            $totaldatas['carriers'] = (int)$countCarrier;
        }
        if (in_array('cart_rules', $data_exports)) {
            $countCartRule = Db::getInstance()->getValue('SELECT count(*) FROM ' .
                _DB_PREFIX_ . 'cart_rule');
            $contents[] = array(
                'title' => $this->l('Cart rules:'),
                'count' => $countCartRule,
            );
            $totaldatas['cart_rules'] = (int)$countCartRule;
        }
        if (in_array('catelog_rules', $data_exports)) {
            $countSpecificPriceRule = Db::getInstance()->getValue('SELECT count(*) FROM ' .
                _DB_PREFIX_ . 'specific_price_rule');
            $contents[] = array(
                'title' => $this->l('Catalog rules:'),
                'count' => $countSpecificPriceRule,
            );
            $totaldatas['catelog_rules'] = (int)$countSpecificPriceRule;
        }
        if (in_array('vouchers', $data_exports)) {
            $countVoucher = Db::getInstance()->getValue('SELECT count(*) FROM ' .
                _DB_PREFIX_ . 'discount');
            $contents[] = array(
                'title' => $this->l('Vouchers:'),
                'count' => $countVoucher,
            );
            $totaldatas['vouchers'] = (int)$countVoucher;
        }
        if (in_array('CMS_categories', $data_exports)) {
            $countcmscategory = Db::getInstance()->getValue('SELECT count(*) FROM ' .
                _DB_PREFIX_ . 'cms_category');
            $contents[] = array(
                'title' => $this->l('CMS categories:'),
                'count' => $countcmscategory,
            );
            $totaldatas['CMS_categories'] = (int)$countcmscategory;
        }
        if (in_array('CMS', $data_exports)) {
            $countcms = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ .
                'cms');
            $contents[] = array(
                'title' => $this->l('CMSs:'),
                'count' => $countcms,
            );
            $totaldatas['CMS'] = (int)$countcms;
        }
        if (in_array('messages', $data_exports)) {
            $countMessage = Db::getInstance()->getValue('SELECT count(*) FROM ' .
                _DB_PREFIX_ . 'customer_thread');
            $contents[] = array(
                'title' => $this->l('Contact form messages:'),
                'count' => $countMessage,
            );
            $totaldatas['messages'] = (int)$countMessage;
        }
        if ($save) {
            $this->context->smarty->assign(array('contents' => $contents));
            return $this->display(__file__, 'views/templates/hook/contents.tpl');
        } else
            return $totaldatas;
    }

    public function displayUploadSussecfull($file_name, $file_size)
    {
        $this->context->smarty->assign(array(
            'file_name' => $file_name,
            'file_size' => $file_size,
        ));
        return $this->display(__file__, 'upload_sussecfully.tpl');
    }

    public function displayPopupHtml()
    {
        $id_import_history = $this->context->cookie->id_import_history;
        if ($id_import_history) {
            $import_history = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ .
                'ets_import_history WHERE id_import_history=' . (int)$id_import_history);;
            $xml = simplexml_load_file(dirname(__file__) . '/xml/' . $import_history['file_name'] .
                '/DataInfo.xml');
            $export_datas = explode(',', (string )$xml->exporteddata);
            $this->context->smarty->assign(array(
                'assign' => $this->getInformationImport($export_datas, $xml),
                'export_datas' => $export_datas,
                'ets_datamaster_import' => explode(',', $import_history['data']),
            ));
            return $this->display(__file__, 'views/templates/hook/popup_import.tpl');
        }
    }

    public function displayFromStep($step)
    {
        $id_import_history = $this->context->cookie->id_import_history;

        if ($id_import_history) {
            $import_history = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'ets_import_history WHERE id_import_history=' . (int)$id_import_history);
            $xml = simplexml_load_file(dirname(__file__) . '/xml/' . $import_history['file_name'] . '/DataInfo.xml');
            $export_datas = explode(',', (string )$xml->exporteddata);
            $this->context->smarty->assign(array(
                'assign' => $this->getInformationImport($export_datas, $xml),
                'export_datas' => $export_datas,
                'link_sites' => isset($xml->link_site) ? explode(',', (string )$xml->link_site) : '',
                'link_history' => 'index.php?controller=AdminDataMasterHistory&token=' . Tools::getAdminTokenLite('AdminDataMasterHistory'),
                'vertion' => isset($xml->pres_version) ? (string )$xml->pres_version : '',
                'platform' => isset($xml->platform) ? (string )$xml->platform : 'Prestashop',
                'OLD_COOKIE_KEY' => isset($xml->cookie_key) ? (string )$xml->cookie_key : '',
                'ets_datamaster_import' => explode(',', $import_history['data']),
                'ets_datamaster_import_delete' => $import_history['delete_before_importing'],
                'ets_datamaster_import_multi_shop' => (int)$import_history['import_multi_shop'],
                'ets_datamaster_import_force_all_id' => (int)$import_history['force_all_id_number'],
                'ets_regenerate_customer_passwords' => Configuration::get('ETS_DATAMASTER_NEW_PASSWD'),
                'resumeImport' => Tools::isSubmit('resumeImport'),
                'pres_version' => $this->pres_version));
            switch ($step) {
                case 1:
                    $fileSize = filesize(dirname(__file__) . '/cache/import/' . $import_history['file_name'] . '.zip') / 1024;
                    $this->context->smarty->assign(array(
                        'file_name' => $import_history['file_name'],
                        'file_size' => $fileSize > 1024 ? round($fileSize / 1024, 2) . 'MB' : round($fileSize, 2) . 'Kb',
                    ));
                    return $this->display(__file__, 'views/templates/hook/upload_sussecfully.tpl');
                case 2:
                    return $this->display(__file__, 'views/templates/hook/import_step2.tpl');
                case 3:
                    if (in_array('products', $export_datas) && (int)$xml->countproduct) {
                        $root_id = Db::getInstance()->getValue('SELECT id_category from ' . _DB_PREFIX_ . 'category where id_parent=0');
                        $categoriesTree = $this->getCategoriesTree($root_id, false);
                        $depth_level = -1;
                        $this->getCategoriesDropdown($categoriesTree, $depth_level, $import_history['id_category_default']);
                        $categoryotpionsHtml = $this->categoryDropDown;
                        $suppliers = Db::getInstance()->executeS('SELECT s.id_supplier,s.name FROM ' . _DB_PREFIX_ . 'supplier s INNER JOIN ' . _DB_PREFIX_ . 'supplier_shop ss ON (s.id_supplier = ss.id_supplier AND ss.id_shop="' . (int)$this->context->shop->id . '") GROUP  BY s.id_supplier');
                        $manufacturers = Db::getInstance()->executeS('SELECT m.id_manufacturer, m.name FROM ' . _DB_PREFIX_ . 'manufacturer m, ' . _DB_PREFIX_ . 'manufacturer_shop ms WHERE m.id_manufacturer= ms.id_manufacturer AND ms.id_shop="' . (int)$this->context->shop->id . '"');
                        $this->context->smarty->assign(array(
                            'categoryotpionsHtml' => $categoryotpionsHtml,
                            'suppliers' => $suppliers,
                            'manufacturers' => $manufacturers,
                            'selected_id_supplier' => $import_history['id_supplier'],
                            'selected_id_manufacturer' => $import_history['id_manufacture'],
                            'import_product' => 1,
                        ));
                    }
                    if (in_array('cms', $export_datas) && (int)$xml->countcms) {
                        $id_root_cms_category = (int)Db::getInstance()->getValue('SELECT id_cms_category FROM ' .
                            _DB_PREFIX_ . 'cms_category WHERE id_parent=0');
                        $cmscategoriesTree = $this->getCmsCategoriesTree($id_root_cms_category);
                        $depth_level = -1;
                        $this->getCMSCategoriesDropdown($cmscategoriesTree, $depth_level, $import_history['id_category_cms']);
                        $cmsCategoryotpionsHtml = $this->cmsCategoryDropDown;
                        $this->context->smarty->assign(array('import_cms' => 1, 'cmsCategoryotpionsHtml' =>
                            $cmsCategoryotpionsHtml));
                    }
                    return $this->display(__file__, 'views/templates/hook/import_step3.tpl');
                case 4:
                    {
                        if (!Configuration::get('ETS_DATAMASTER_NEW_PASSWD'))
                            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'ets_import_history SET cookie_key="' . pSQL((string )$xml->cookie_key) . '" WHERE id_import_history=' . (int)$id_import_history);
                        else
                            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'ets_import_history SET cookie_key="" WHERE id_import_history=' . (int)$id_import_history);
                        return $this->display(__file__, 'views/templates/hook/import_step4.tpl');
                    }
                case 5:
                    {
                        $new_passwd_customer = count(Db::getInstance()->executeS('SELECT * FROM ' .
                            _DB_PREFIX_ . 'ets_datamaster_customer_pasword WHERE id_import_history=' . (int)
                            $id_import_history));
                        $new_passwd_employee = count(Db::getInstance()->executeS('SELECT * FROM ' .
                            _DB_PREFIX_ . 'ets_datamaster_employee_pasword WHERE id_import_history=' . (int)
                            $id_import_history));
                        $this->cleanForderImported($id_import_history);
                        $import_history = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'ets_import_history WHERE id_import_history=' . (int)$id_import_history);;
                        $this->context->smarty->assign(array(
                            'new_passwd_customer' => $new_passwd_customer,
                            'new_passwd_employee' => $new_passwd_employee,
                            'id_import_history' => $id_import_history,
                            'OLD_COOKIE_KEY' => $import_history['cookie_key'],
                            'module_dir' => $this->url_module,
                            'error_log' => file_exists(dirname(__FILE__) . '/xml/' . $import_history['file_name'] . '/errors.log') ? $this->_path . '/xml/' . $import_history['file_name'] . '/errors.log' : '',
                        ));
                        return $this->display(__file__, 'views/templates/hook/import_step5.tpl');
                    }
            }
        }
    }

    public function hookDatamasterLeftBlok()
    {
        $intro = true;
        $localIps = array(
            '127.0.0.1',
            '::1'
        );
		$baseURL = Tools::strtolower(self::getBaseModLink());
		if(!Tools::isSubmit('intro') && (in_array(Tools::getRemoteAddr(), $localIps) || preg_match('/^.*(localhost|demo|test|dev|:\d+).*$/i', $baseURL)))
		    $intro = false;
        $this->context->smarty->assign(array(
            'controller' => Tools::getValue('controller'),
            'link' => $this->context->link,
            'other_modules_link' => $this->context->link->getAdminLink('AdminModules', true) . '&configure=' . $this->name.'&othermodules=1',
            'intro' => $intro,
        ));
        return $this->display(__file__, 'left_block.tpl');
    }

    public function displayFormStepExport($step, $file_name = '')
    {
        Context::getContext()->smarty->assign(array(
            'ets_datamaster_export' => Tools::isSubmit('submitExport') ? Tools::getValue('data_export', array()) : explode(',', Configuration::get('ETS_DATAMASTER_EXPORT')),
            'ets_datamaster_format' => Configuration::get('ETS_DATAMASTER_FORMAT'),
            'divide_file' => Configuration::get('ETS_DATAMASTER_DIVIDE_FILE'),
            'number_record' => (int)Configuration::get('ETS_DT_NUMBER_RECORD'),
            'ETS_PRES2PRES_ORDER_FROM' => Configuration::get('ETS_PRES2PRES_ORDER_FROM'),
            'ETS_PRES2PRES_ORDER_TO' => Configuration::get('ETS_PRES2PRES_ORDER_TO'),
            'link' => $this->context->link,
        ));
        if ($file_name) {
            $this->context->smarty->assign(array(
                'file_zise_export' => ($fileSize = filesize(dirname(__file__) . '/cache/export/' . $file_name) / 1024) && $fileSize > 1024 ? round($fileSize / 1024, 2) . 'MB' : round($fileSize, 2) . 'Kb',
                'url_export' => $this->pres_version == 1.4 ? Tools::getShopDomainSsl(true) . __PS_BASE_URI__ . 'modules/ets_oneclicktomigrate/cache/export/' . $file_name : Tools::getShopDomainSsl(true) . Context::getContext()->shop->getBaseURI() . 'modules/ets_oneclicktomigrate/cache/export/' . $file_name,
            ));
        }
        switch ($step) {
            case 2:
                return $this->display(__file__, 'views/templates/hook/export_step2.tpl');
            case 3:
                $this->context->smarty->assign(array('totalDatas' => $this->exportContent(false),));
                return $this->display(__file__, 'views/templates/hook/export_step3.tpl');
            case 4:
                return $this->display(__file__, 'views/templates/hook/export_step4.tpl');
        }
    }

    public function displayFromUloadLoad()
    {
        $this->context->smarty->assign(array('id_import_history' => $this->context->
        cookie->id_import_history,));
        return $this->display(__file__, 'views/templates/hook/upload_form.tpl');
    }

    public function getCategoriesTree($id_root, $active = true, $id_lang = null)
    {
        $tree = array();
        if (is_null($id_lang))
            $id_lang = (int)$this->context->language->id;
        $sql = "SELECT c.id_category, cl.name
                FROM " . _DB_PREFIX_ . "category c
                LEFT JOIN " . _DB_PREFIX_ .
            "category_lang cl ON c.id_category = cl.id_category AND cl.id_lang = " . (int)$id_lang .
            "
                WHERE c.id_category = " . (int)$id_root . " " . ($active ?
                " AND  c.active = 1" : "") . " GROUP BY c.id_category";
        if ($category = Db::getInstance()->getRow($sql)) {
            $cat = array('id_category' => $id_root, 'name' => $category['name']);
            $children = $this->getChildrenCategories($id_root, $active, $id_lang);
            $temp = array();
            if ($children) {
                foreach ($children as $child) {
                    $arg = $this->getCategoriesTree($child['id_category'], $active, $id_lang);
                    if ($arg && isset($arg[0]))
                        $temp[] = $arg[0];
                }
            }
            $cat['children'] = $temp;
            $tree[] = $cat;
        }
        return $tree;
    }

    public function getChildrenCategories($id_root, $active = true, $id_lang = null)
    {
        if (is_null($id_lang))
            $id_lang = (int)$this->context->language->id;
        $sql = "SELECT c.id_category, cl.name
                FROM " . _DB_PREFIX_ . "category c
                LEFT JOIN " . _DB_PREFIX_ .
            "category_lang cl ON c.id_category = cl.id_category AND cl.id_lang = " . (int)$id_lang .
            "
                WHERE c.id_parent = " . (int)$id_root . " " . ($active ?
                " AND  c.active = 1" : "") . " GROUP BY c.id_category";
        return Db::getInstance()->executeS($sql);
    }

    public function displayOption($selected_category, $id_category, $depth_level, $levelSeparator,
                                  $name)
    {
        $this->context->smarty->assign(array(
            'selected_category' => $selected_category,
            'id_category' => $id_category,
            'depth_level' => $depth_level,
            'levelSeparator' => $levelSeparator,
            'name' => $name,
        ));
        return $this->display(__file__, 'views/templates/hook/option.tpl');
    }

    public function getCategoriesDropdown($categories, &$depth_level = -1, $selected_category =
    0)
    {
        if ($categories) {
            $depth_level++;
            foreach ($categories as $category) {
                if ((!$this->depthLevel || $this->depthLevel && (int)$depth_level <= $this->
                    depthLevel)) {
                    $levelSeparator = '';
                    if ($depth_level >= 2) {
                        for ($i = 1; $i <= $depth_level - 1; $i++) {
                            $levelSeparator .= $this->categoryPrefix;
                        }
                    }
                    if (isset($category['id_category']) && $category['id_category'] > 1)
                        $this->categoryDropDown .= $this->displayOption((int)$selected_category, (int)$category['id_category'],
                            $depth_level, $levelSeparator, $category['name']);
                    if (isset($category['children']) && $category['children']) {
                        $this->getCategoriesDropdown($category['children'], $depth_level, $selected_category);
                    }
                }
            }
            $depth_level--;
        }
    }

    public function getCmsCategoriesTree($id_root, $active = true, $id_lang = null)
    {
        $tree = array();
        if (is_null($id_lang))
            $id_lang = (int)$this->context->language->id;
        $sql = "SELECT c.id_cms_category, cl.name
                FROM " . _DB_PREFIX_ . "cms_category c
                LEFT JOIN " . _DB_PREFIX_ .
            "cms_category_lang cl ON c.id_cms_category = cl.id_cms_category AND cl.id_lang = " . (int)
            $id_lang . "
                WHERE c.id_cms_category = " . (int)$id_root . " " . ($active ?
                " AND  c.active = 1" : "") . " GROUP BY c.id_cms_category";
        if ($category = Db::getInstance()->getRow($sql)) {
            $cat = array('id_cms_category' => $id_root, 'name' => $category['name']);
            $children = $this->getChildrenCSMCategories($id_root, $active, $id_lang);
            $temp = array();
            if ($children) {
                foreach ($children as $child) {
                    $arg = $this->getCmsCategoriesTree($child['id_cms_category'], $active, $id_lang);
                    if ($arg && isset($arg[0]))
                        $temp[] = $arg[0];
                }
            }
            $cat['children'] = $temp;
            $tree[] = $cat;
        }
        return $tree;
    }

    public function getChildrenCSMCategories($id_root, $active = true, $id_lang = null)
    {
        if (is_null($id_lang))
            $id_lang = (int)$this->context->language->id;
        $sql = "SELECT c.id_cms_category, cl.name
                FROM " . _DB_PREFIX_ . "cms_category c
                LEFT JOIN " . _DB_PREFIX_ .
            "cms_category_lang cl ON c.id_cms_category = cl.id_cms_category AND cl.id_lang = " . (int)
            $id_lang . "
                WHERE c.id_parent = " . (int)$id_root . " " . ($active ?
                " AND  c.active = 1" : "") . " GROUP BY c.id_cms_category";
        return Db::getInstance()->executeS($sql);
    }

    public function getCMSCategoriesDropdown($cmscategories, &$depth_level = -1, $selected_cms_category =
    0)
    {
        if ($cmscategories) {
            $depth_level++;
            foreach ($cmscategories as $category) {
                if ((!$this->depthLevel || $this->depthLevel && (int)$depth_level <= $this->
                    depthLevel)) {
                    $levelSeparator = '';
                    if ($depth_level >= 2) {
                        for ($i = 1; $i <= $depth_level - 1; $i++) {
                            $levelSeparator .= $this->categoryPrefix;
                        }
                    }
                    if ($category['id_cms_category'] > 0)
                        $this->cmsCategoryDropDown .= $this->displayCSMOption((int)$selected_cms_category,
                            (int)$category['id_cms_category'], $depth_level, $levelSeparator, $category['name']);
                    if (isset($category['children']) && $category['children']) {
                        $this->getCMSCategoriesDropdown($category['children'], $depth_level, $selected_cms_category);
                    }
                }
            }
            $depth_level--;
        }
    }

    public function displayCSMOption($selected_cms_category, $id_cms_category, $depth_level,
                                     $levelSeparator, $name)
    {
        $this->context->smarty->assign(array(
            'selected_cms_category' => $selected_cms_category,
            'id_cms_category' => $id_cms_category,
            'depth_level' => $depth_level,
            'levelSeparator' => $levelSeparator,
            'name' => $name,
        ));
        return $this->display(__file__, 'cmsoption.tpl');
    }

    public function processExport()
    {
        if (ob_get_length() > 0) {
            ob_end_clean();
        }
        $step = (int)Tools::getValue('step');
        switch ($step) {
            case 1:
                if (Tools::getValue('data_export')) {
                    Configuration::updateValue('ETS_DATAMASTER_EXPORTED', 0);
                    Configuration::updateValue('ETS_DATAMASTER_EXPORT', implode(',', Tools::
                    getValue('data_export')));
                    $step++;
                    die(Tools::jsonEncode(array(
                        'error' => false,
                        'step' => $step,
                        'form_step' => $this->displayFormStepExport($step),
                    )));
                } else
                    $this->_errors[] = $this->l('Please select kinds of data you want to export');
                break;
            case 2:
                if ((int)Tools::getValue('divide_file') && ((int)Tools::getValue('number_record') < 100 || (int)Tools::getValue('number_record') > 5000)) {
                    $this->_errors[] = $this->l('Maximum number of lines per data file must be a number between 100 and 5000');
                    break;
                } elseif ((Tools::getValue('ETS_PRES2PRES_ORDER_FROM') && !Validate::isDate(Tools::getValue('ETS_PRES2PRES_ORDER_FROM')))) {
                    $this->_errors[] = $this->l('"Orders added from" is valid');
                } elseif ((Tools::getValue('ETS_PRES2PRES_ORDER_TO') && !Validate::isDate(Tools::getValue('ETS_PRES2PRES_ORDER_TO')))) {
                    $this->_errors[] = $this->l('"Orders added to" is valid');
                } else {
                    Configuration::updateValue('ETS_DATAMASTER_FORMAT', Tools::getValue('data_format'));
                    Configuration::updateValue('ETS_DATAMASTER_DIVIDE_FILE', (int)Tools::getValue('divide_file'));
                    Configuration::updateValue('ETS_DT_NUMBER_RECORD', (int)Tools::getValue('number_record'));
                    $step++;
                    die(Tools::jsonEncode(array(
                        'error' => false,
                        'step' => $step,
                        'form_step' => $this->displayFormStepExport($step),
                    )));
                }
            case 3:
                if (Configuration::get('ETS_DATAMASTER_FORMAT') == 'xml' || true) {
                    if ($this->pres_version == 1.4)
                        $file_name_export = $this->exportDataXML14();
                    else
                        $file_name_export = $this->exportDataXML();
                } else
                    $file_name_export = $this->exportDataCSV();
                $step++;
                if (!$this->_errors) {
                    die(Tools::jsonEncode(array(
                        'error' => false,
                        'step' => $step,
                        'form_step' => $this->displayFormStepExport($step, $file_name_export),
                    )));
                }
            case 4:
                break;
        }
        if ($this->_errors) {
            die(Tools::jsonEncode(array(
                'error' => true,
                'errors' => $this->displayError($this->_errors),
            )));
        }
    }

    public function exportDataXML()
    {
        if (!Tools::getValue('submitExportReload')) {
            $this->context->cookie->zip_file_name = '';
            $this->context->cookie->export_sucss = '';
            $this->context->cookie->write();
        }
        $cacheDir = dirname(__file__) . '/cache/export/';
        if (isset($this->context->cookie->zip_file_name) && $this->context->cookie->zip_file_name) {
            $zip_file_name = $this->context->cookie->zip_file_name;
        } else {
            $zip_file_name = 'oc2m_data_' . $this->genSecure(7);
            $this->context->cookie->zip_file_name = $zip_file_name;
            $this->context->cookie->write();
            die('Oops. Your jQuery is out of date. Please upgrade your jQuery to jQuery -1.1.11');
        }
        $dir = $cacheDir . $zip_file_name;
        if ($this->context->cookie->export_sucss) {
            $this->deleteForderXml($zip_file_name);
            $this->context->cookie->zip_file_name = '';
            $this->context->cookie->export_sucss = '';
            $this->context->cookie->write();
            $content = $this->exportContent();
            $export_history = new ExportHistory();
            $export_history->file_name = $zip_file_name . '.zip';
            $export_history->content = $content;
            $export_history->date_export = date('Y-m-d h:i:s');
            $export_history->add();
            return $zip_file_name . '.zip';
        }
        if (!is_dir($dir)) {
            @mkdir($dir, 0777);
        }
        $data_exports = explode(',', Configuration::get('ETS_DATAMASTER_EXPORT'));
        $export = new DataExport();
        $extra_export = new ExtraExport();
        $multishop = in_array('shops', $data_exports);
        if (!file_exists($dir . '/DataInfo.xml'))
            file_put_contents($dir . '/DataInfo.xml', $extra_export->exportInfo($dir));
        if ($data_exports) {
            if ($multishop) {
                if ($this->checkTableExported($dir, 'ShopGroup')) {
                    if (!$export->addFileXMl($dir, 'ShopGroup', ShopGroup::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create ShopGroup.xml');
                    else
                        $this->insertTableExported($dir, 'ShopGroup');
                }
                if ($this->checkTableExported($dir, 'shop')) {
                    if (!$export->addFileXMl($dir, 'Shop', Shop::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create Shop.xml');
                    else
                        $this->insertTableExported($dir, 'shop');
                }
                if ($this->checkTableExported($dir, 'ShopUrl')) {
                    if (!$export->addFileXMl($dir, 'ShopUrl', ShopUrl::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create ShopUrl.xml');
                    else
                        $this->insertTableExported($dir, 'ShopUrl');
                }
            }
            if ($this->checkTableExported($dir, 'Language')) {
                if (!$export->addFileXMl($dir, 'Language', Language::$definition, $multishop))
                    $this->_errors[] = $this->l('Cannot create Language.xml');
                else
                    $this->insertTableExported($dir, 'Language');
            }
            if ($this->checkTableExported($dir, 'Currency')) {
                if (!$export->addFileXMl($dir, 'Currency', Currency::$definition, $multishop))
                    $this->_errors[] = $this->l('Cannot create Currency.xml');
                else
                    $this->insertTableExported($dir, 'Currency');
            }
            if ($this->checkTableExported($dir, 'Zone')) {
                if (!$export->addFileXMl($dir, 'Zone', Zone::$definition, $multishop))
                    $this->_errors[] = $this->l('Cannot create Zone.xml');
                else
                    $this->insertTableExported($dir, 'Zone');
            }
            if ($this->checkTableExported($dir, 'Country')) {
                if (!$export->addFileXMl($dir, 'Country', Country::$definition, $multishop))
                    $this->_errors[] = $this->l('Cannot create Country.xml');
                else
                    $this->insertTableExported($dir, 'Country');
            }
            if ($this->checkTableExported($dir, 'State')) {
                if (!$export->addFileXMl($dir, 'State', State::$definition, $multishop))
                    $this->_errors[] = $this->l('Cannot create State.xml');
                else
                    $this->insertTableExported($dir, 'State');
            }
            if ($this->checkTableExported($dir, 'Employee')) {
                if (!$export->addFileXMl($dir, 'Employee', Employee::$definition, $multishop))
                    $this->_errors[] = $this->l('Cannot create Employee.xml');
                else
                    $this->insertTableExported($dir, 'Employee');
            }

            if (in_array('categories', $data_exports)) {
                if ($this->checkTableExported($dir, 'Group')) {
                    if (!$export->addFileXMl($dir, 'Group', Group::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create Group.xml');
                    else
                        $this->insertTableExported($dir, 'Group');
                }
                if ($this->checkTableExported($dir, 'Category')) {
                    if (!$this->addCategoryFileXMl($dir, $multishop))
                        $this->_errors[] = $this->l('Cannot create Category.xml');
                    else
                        $this->insertTableExported($dir, 'Category');
                }
            }
            if (in_array('customers', $data_exports)) {
                if ($this->checkTableExported($dir, 'Group')) {
                    if (!$export->addFileXMl($dir, 'Group', Group::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create Group.xml');
                    else
                        $this->insertTableExported($dir, 'Group');
                }
                if ($this->checkTableExported($dir, 'Customer')) {
                    if (!$export->addFileXMl($dir, 'Customer', Customer::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create Customer.xml');
                    else
                        $this->insertTableExported($dir, 'Customer');
                }
                if ($this->checkTableExported($dir, 'customer_group')) {
                    if (!$export->addFileXMl14($dir, 'customergroup', 'customer_group')) {
                        $this->_errors[] = $this->l('Cannot create customergroup.xml');
                    } else
                        $this->insertTableExported($dir, 'customer_group');
                }
                if ($this->checkTableExported($dir, 'Address')) {
                    if (!$export->addFileXMl($dir, 'Address', Address::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create Address.xml');
                    else
                        $this->insertTableExported($dir, 'Address');
                }
            }
            if (in_array('manufactures', $data_exports)) {
                if ($this->checkTableExported($dir, 'Manufacturer')) {
                    if (!$export->addFileXMl($dir, 'Manufacturer', Manufacturer::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create Manufacturer.xml');
                    else
                        $this->insertTableExported($dir, 'Manufacturer');
                }
            }
            if (in_array('suppliers', $data_exports)) {
                if ($this->checkTableExported($dir, 'Supplier')) {
                    if (!$export->addFileXMl($dir, 'Supplier', Supplier::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create Supplier.xml');
                    else
                        $this->insertTableExported($dir, 'Supplier');
                }
            }
            if (in_array('carriers', $data_exports)) {
                if ($this->checkTableExported($dir, 'Carrier')) {
                    if (!$export->addFileXMl($dir, 'Carrier', Carrier::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create Carrier.xml');
                    else
                        $this->insertTableExported($dir, 'Carrier');
                }
                if ($this->checkTableExported($dir, 'carrierzone')) {
                    if (!$export->addFileXMl14($dir, 'carrierzone', 'carrier_zone')) {
                        $this->_errors[] = $this->l('Cannot create carrierzone.xml');
                    } else
                        $this->insertTableExported($dir, 'carrierzone');
                }
                if ($this->checkTableExported($dir, 'RangePrice')) {
                    if (!$export->addFileXMl($dir, 'RangePrice', RangePrice::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create RangePrice.xml');
                    else
                        $this->insertTableExported($dir, 'RangePrice');
                }
                if ($this->checkTableExported($dir, 'RangeWeight')) {
                    if (!$export->addFileXMl($dir, 'RangeWeight', RangeWeight::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create RangeWeight.xml');
                    else
                        $this->insertTableExported($dir, 'RangeWeight');
                }
                if ($this->checkTableExported($dir, 'Delivery')) {
                    if (!$export->addFileXMl($dir, 'Delivery', Delivery::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create Delivery.xml');
                    else
                        $this->insertTableExported($dir, 'Delivery');
                }
            }
            if (in_array('cart_rules', $data_exports)) {
                if ($this->checkTableExported($dir, 'CartRule')) {
                    if (!$export->addFileXMl($dir, 'CartRule', CartRule::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create CartRule.xml');
                    else
                        $this->insertTableExported($dir, 'CartRule');
                }
                if ($this->checkTableExported($dir, 'cartrulecarrier')) {
                    if (!$export->addFileXMl14($dir, 'cartrulecarrier', 'cart_rule_carrier')) {
                        $this->_errors[] = $this->l('Cannot create cartrulecarrier.xml');  //extra
                    } else
                        $this->insertTableExported($dir, 'cartrulecarrier');
                }
                if (!$export->addFileXMl14($dir, 'cartrulecombination ', 'cart_rule_combination')) {
                    $this->_errors[] = $this->l('Cannot create cartrulecombination.xml');  //extra
                } else
                    $this->insertTableExported($dir, 'cartrulecombination');
                if (!$export->addFileXMl14($dir, 'cartrulecountry ', 'cart_rule_country ')) {
                    $this->_errors[] = $this->l('Cannot create cartrulecountry.xml');  //extra
                } else
                    $this->insertTableExported($dir, 'cartrulecountry');
                if (!$export->addFileXMl14($dir, 'cartrulegroup', 'cart_rule_group ')) {
                    $this->_errors[] = $this->l('Cannot create cartrulegroup.xml');  //extra
                } else
                    $this->insertTableExported($dir, 'cartrulegroup');
                if (!$export->addFileXMl14($dir, 'cartruleproductrulegroup', 'cart_rule_product_rule_group')) {
                    $this->_errors[] = $this->l('Cannot create cartruleproductrulegroup.xml');  //extra
                } else
                    $this->insertTableExported($dir, 'cartruleproductrulegroup');

                if (!$export->addFileXMl14($dir, 'cartruleproductrule', 'cart_rule_product_rule')) {
                    $this->_errors[] = $this->l('Cannot create cartruleproductrule.xml');  //extra
                } else
                    $this->insertTableExported($dir, 'cartruleproductrule');

                if (!$export->addFileXMl14($dir, 'cartruleproductrulevalue', 'cart_rule_product_rule_value')) {
                    $this->_errors[] = $this->l('Cannot create cartruleproductrulevalue.xml');  //extra
                } else
                    $this->insertTableExported($dir, 'cartruleproductrulevalue');
            }
            if (in_array('catelog_rules', $data_exports)) {
                if ($this->checkTableExported($dir, 'SpecificPriceRule')) {
                    if (!$export->addFileXMl($dir, 'SpecificPriceRule', SpecificPriceRule::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create SpecificPriceRule.xml');
                    else
                        $this->insertTableExported($dir, 'SpecificPriceRule');
                }
            }
            if (in_array('products', $data_exports)) {
                if ($this->checkTableExported($dir, 'categoryproduct')) {
                    if (!$export->addFileXMl14($dir, 'categoryproduct', 'category_product')) {
                        $this->_errors[] = $this->l('Cannot create categoryproduct.xml');  //extra
                    } else
                        $this->insertTableExported($dir, 'categoryproduct');
                }
                if ($this->checkTableExported($dir, 'Product')) {
                    if (!$export->addFileXMl($dir, 'Product', Product::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create Product.xml');
                    else
                        $this->insertTableExported($dir, 'Product');
                }
                if ($this->checkTableExported($dir, 'Tag')) {
                    if (!$export->addFileXMl($dir, 'Tag', Tag::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create tag.xml');
                    else
                        $this->insertTableExported($dir, 'Tag');
                }
                if ($this->checkTableExported($dir, 'Image')) {
                    if (!$export->addFileXMl($dir, 'Image', Image::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create Image.xml');
                    else
                        $this->insertTableExported($dir, 'Image');
                }
                if ($this->checkTableExported($dir, 'Combination')) {
                    if (!$export->addFileXMl($dir, 'Combination', Combination::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create Combination.xml');
                    else
                        $this->insertTableExported($dir, 'Combination');
                }
                if ($this->checkTableExported($dir, 'AttributeGroup')) {
                    if (!$export->addFileXMl($dir, 'AttributeGroup', AttributeGroup::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create AttributeGroup.xml');
                    else
                        $this->insertTableExported($dir, 'AttributeGroup');
                }
                if ($this->checkTableExported($dir, 'Attribute')) {
                    if (!$export->addFileXMl($dir, 'Attribute', Attribute::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create Attribute.xml');
                    else
                        $this->insertTableExported($dir, 'Attribute');
                }
                if ($this->checkTableExported($dir, 'productattributecombination')) {
                    if (!$export->addFileXMl14($dir, 'productattributecombination', 'product_attribute_combination')) {
                        $this->_errors[] = $this->l('Cannot create productattributecombination.xml'); //extra
                    } else
                        $this->insertTableExported($dir, 'productattributecombination');
                }
                if ($this->checkTableExported($dir, 'productattributeimage')) {
                    if (!$export->addFileXMl14($dir, 'productattributeimage', 'product_attribute_image')) {
                        $this->_errors[] = $this->l('Cannot create productattributeimage.xml'); //extra
                    } else
                        $this->insertTableExported($dir, 'productattributeimage');
                }
                if ($this->checkTableExported($dir, 'producttag')) {
                    if (!$export->addFileXMl14($dir, 'producttag', 'product_tag')) {
                        $this->_errors[] = $this->l('Cannot create producttag.xml'); //extra
                    } else
                        $this->insertTableExported($dir, 'producttag');
                }
                if ($this->checkTableExported($dir, 'Feature')) {
                    if (!$export->addFileXMl($dir, 'Feature', Feature::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create Feature.xml');
                    else
                        $this->insertTableExported($dir, 'Feature');
                }
                if ($this->checkTableExported($dir, 'FeatureValue')) {
                    if (!$export->addFileXMl($dir, 'FeatureValue', FeatureValue::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create FeatureValue.xml');
                    else
                        $this->insertTableExported($dir, 'FeatureValue');
                }
                if ($this->checkTableExported($dir, 'featureproduct')) {
                    if (!$export->addFileXMl14($dir, 'featureproduct', 'feature_product')) {
                        $this->_errors[] = $this->l('Cannot create featureproduct.xml'); // extra feature_product;
                    } else
                        $this->insertTableExported($dir, 'featureproduct');
                }
                if ($this->checkTableExported($dir, 'SpecificPrice')) {
                    if (!$export->addFileXMl($dir, 'SpecificPrice', SpecificPrice::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create SpecificPrice.xml');
                    else
                        $this->insertTableExported($dir, 'SpecificPrice');
                }
                if ($this->checkTableExported($dir, 'Tax')) {
                    if (!$export->addFileXMl($dir, 'Tax', Tax::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create Tax.xml');
                    else
                        $this->insertTableExported($dir, 'Tax');
                }
                if ($this->checkTableExported($dir, 'TaxRulesGroup')) {
                    if (!$export->addFileXMl($dir, 'TaxRulesGroup', TaxRulesGroup::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create TaxRulesGroup.xml');
                    else
                        $this->insertTableExported($dir, 'TaxRulesGroup');
                }
                if ($this->checkTableExported($dir, 'TaxRule')) {
                    if (!$export->addFileXMl($dir, 'TaxRule', TaxRule::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create TaxRule.xml');
                    else
                        $this->insertTableExported($dir, 'TaxRule');
                }
                if ($this->checkTableExported($dir, 'productsupplier')) {
                    if (!$export->addFileXMl14($dir, 'productsupplier', 'product_supplier')) {
                        $this->_errors[] = $this->l('Cannot create productsupplier.xml'); // id_tax, id_rules_group
                    } else
                        $this->insertTableExported($dir, 'productsupplier');
                }
                if ($this->checkTableExported($dir, 'StockAvailable')) {
                    if (!$export->addFileXMl($dir, 'StockAvailable', StockAvailable::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create StockAvailable.xml');
                    else
                        $this->insertTableExported($dir, 'StockAvailable');
                }
                if ($this->checkTableExported($dir, 'Warehouse')) {
                    if (!$export->addFileXMl($dir, 'Warehouse', Warehouse::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create Warehouse.xml');
                    else
                        $this->insertTableExported($dir, 'Warehouse');
                }
                if ($this->checkTableExported($dir, 'WarehouseProductLocation')) {
                    if (!$export->addFileXMl($dir, 'WarehouseProductLocation', WarehouseProductLocation::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create WarehouseProductLocation.xml');
                    else
                        $this->insertTableExported($dir, 'WarehouseProductLocation');
                }
                if ($this->checkTableExported($dir, 'Stock')) {
                    if (!$export->addFileXMl($dir, 'Stock', Stock::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create Stock.xml');
                    else
                        $this->insertTableExported($dir, 'Stock');
                }
                if ($this->checkTableExported($dir, 'warehousecarrier')) {
                    if (in_array('carriers', $data_exports)) {
                        if (!$export->addFileXMl14($dir, 'warehousecarrier', 'warehouse_carrier')) {
                            $this->_errors[] = $this->l('Cannot create warehousecarrier.xml');
                        } else
                            $this->insertTableExported($dir, 'warehousecarrier');
                    }
                }
                if ($this->checkTableExported($dir, 'CustomizationField')) {
                    if (!$export->addFileXMl14($dir, 'CustomizationField', 'customization_field', 'id_customization_field', true))
                        $this->_errors[] = $this->l('Cannot create CustomizationField.xml');
                    else
                        $this->insertTableExported($dir, 'CustomizationField');
                }
                if ($this->checkTableExported($dir, 'productcarrier')) {
                    if (in_array('carriers', $data_exports)) {
                        if (!$export->addFileXMl14($dir, 'productcarrier', 'product_carrier')) {
                            $this->_errors[] = $this->l('Cannot create productcarrier.xml');
                        } else
                            $this->insertTableExported($dir, 'productcarrier');
                    }
                }
                if ($this->checkTableExported($dir, 'accessory')) {
                    if (!$export->addFileXMl14($dir, 'accessory', 'accessory')) {
                        $this->_errors[] = $this->l('Cannot create accessory.xml'); //extra
                    } else
                        $this->insertTableExported($dir, 'accessory');
                }
                if ($this->checkTableExported($dir, 'pack')) {
                    if (!$export->addFileXMl14($dir, 'pack', 'pack')) {
                        $this->_errors[] = $this->l('Cannot create pack.xml'); //extra
                    } else
                        $this->insertTableExported($dir, 'pack');
                }
            }
            if (in_array('orders', $data_exports)) {
                if ($this->checkTableExported($dir, 'OrderState')) {
                    if (!$export->addFileXMl($dir, 'OrderState', OrderState::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create OrderState.xml');
                    else
                        $this->insertTableExported($dir, 'OrderState');
                }
                if ($this->checkTableExported($dir, 'Cart')) {
                    if (!$export->addFileXMl($dir, 'Cart', Cart::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create Cart.xml');
                    else
                        $this->insertTableExported($dir, 'Cart');
                }
                if ($this->checkTableExported($dir, 'Customization')) {
                    if (!$export->addFileXMl14($dir, 'Customization', 'customization', 'id_customization'))
                        $this->_errors[] = $this->l('Cannot create CustomizationField.xml');
                    else
                        $this->insertTableExported($dir, 'Customization');
                }
                if ($this->checkTableExported($dir, 'customizeddata')) {
                    if (!$export->addFileXMl14($dir, 'customizeddata', 'customized_data'))
                        $this->_errors[] = $this->l('Cannot create customizeddata.xml');
                    else
                        $this->insertTableExported($dir, 'customizeddata');
                }
                if ($this->checkTableExported($dir, 'Order')) {
                    if (!$export->addFileXMl($dir, 'Order', Order::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create Order.xml');
                    else
                        $this->insertTableExported($dir, 'Order');
                }
                if ($this->checkTableExported($dir, 'OrderDetail')) {
                    if (!$export->addFileXMl($dir, 'OrderDetail', OrderDetail::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create OrderDetail.xml');
                    else
                        $this->insertTableExported($dir, 'OrderDetail');
                }
                if ($this->checkTableExported($dir, 'OrderInvoice')) {
                    if (!$export->addFileXMl($dir, 'OrderInvoice', OrderInvoice::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create OrderInvoice.xml');
                    else
                        $this->insertTableExported($dir, 'OrderInvoice');
                }
                if ($this->checkTableExported($dir, 'order_detail_tax')) {
                    if (!$export->addFileXMl14($dir, 'order_detail_tax', 'order_detail_tax'))
                        $this->_errors[] = $this->l('Cannot create order_detail_tax.xml');
                    else
                        $this->insertTableExported($dir, 'order_detail_tax');
                }
                if ($this->checkTableExported($dir, 'order_invoice_tax')) {
                    if (!$export->addFileXMl14($dir, 'order_invoice_tax', 'order_invoice_tax'))
                        $this->_errors[] = $this->l('Cannot create order_invoice_tax.xml');
                    else
                        $this->insertTableExported($dir, 'order_invoice_tax');
                }
                if ($this->checkTableExported($dir, 'order_invoice_payment')) {
                    if (!$export->addFileXMl14($dir, 'order_invoice_payment', 'order_invoice_payment'))
                        $this->_errors[] = $this->l('Cannot create order_invoice_payment.xml');
                    else
                        $this->insertTableExported($dir, 'order_invoice_payment');
                }
                if ($this->checkTableExported($dir, 'OrderSlip')) {
                    if (!$export->addFileXMl($dir, 'OrderSlip', OrderSlip::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create OrderSlip.xml');
                    else
                        $this->insertTableExported($dir, 'OrderSlip');
                }
                if ($this->checkTableExported($dir, 'OrderCarrier')) {
                    if (!$export->addFileXMl($dir, 'OrderCarrier', OrderCarrier::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create OrderCarrier.xml');
                    else
                        $this->insertTableExported($dir, 'OrderCarrier');
                }
                if ($this->checkTableExported($dir, 'OrderCartRule')) {
                    if (!$export->addFileXMl($dir, 'OrderCartRule', OrderCartRule::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create OrderCartRule.xml');
                    else
                        $this->insertTableExported($dir, 'OrderCartRule');
                }
                if ($this->checkTableExported($dir, 'OrderHistory')) {
                    if (!$export->addFileXMl($dir, 'OrderHistory', OrderHistory::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create OrderHistory.xml');
                    else
                        $this->insertTableExported($dir, 'OrderHistory');
                }
                if ($this->checkTableExported($dir, 'OrderMessage')) {
                    if (!$export->addFileXMl($dir, 'OrderMessage', OrderMessage::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create OrderMessage.xml');
                    else
                        $this->insertTableExported($dir, 'OrderMessage');
                }
                if ($this->checkTableExported($dir, 'OrderPayment')) {
                    if (!$export->addFileXMl($dir, 'OrderPayment', OrderPayment::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create OrderPayment.xml');
                    else
                        $this->insertTableExported($dir, 'OrderPayment');
                }
                if ($this->checkTableExported($dir, 'OrderReturn')) {
                    if (!$export->addFileXMl($dir, 'OrderReturn', OrderReturn::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create OrderReturn.xml');
                    else
                        $this->insertTableExported($dir, 'OrderReturn');
                }
                if ($this->checkTableExported($dir, 'Message')) {
                    if (!$export->addFileXMl($dir, 'Message', Message::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create Message.xml');
                    else
                        $this->insertTableExported($dir, 'Message');
                }
            }
            if (in_array('CMS_categories', $data_exports)) {
                if ($this->checkTableExported($dir, 'CMSCategory')) {
                    if (!$export->addFileXMl($dir, 'CMSCategory', CMSCategory::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create CMSCategory.xml');
                    else
                        $this->insertTableExported($dir, 'CMSCategory');
                }
            }
            if (in_array('CMS', $data_exports)) {
                if ($this->checkTableExported($dir, 'CMS')) {
                    if (!$export->addFileXMl($dir, 'CMS', CMS::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create CMS.xml');
                    else
                        $this->insertTableExported($dir, 'CMS');
                }

            }
            if (in_array('messages', $data_exports)) {
                if ($this->checkTableExported($dir, 'Contact')) {
                    if (!$export->addFileXMl($dir, 'Contact', Contact::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create Contact.xml');
                    else
                        $this->insertTableExported($dir, 'Contact');
                }
                if ($this->checkTableExported($dir, 'CustomerThread')) {
                    if (!$export->addFileXMl($dir, 'CustomerThread', CustomerThread::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create CustomerThread.xml');
                    else
                        $this->insertTableExported($dir, 'CustomerThread');
                }
                if ($this->checkTableExported($dir, 'CustomerMessage')) {
                    if (!$export->addFileXMl($dir, 'CustomerMessage', CustomerMessage::$definition, $multishop))
                        $this->_errors[] = $this->l('Cannot create CustomerMessage.xml');
                    else
                        $this->insertTableExported($dir, 'CustomerMessage');
                }
            }
            if (!$this->_errors) {
                $this->zipForderXml($zip_file_name);
                $this->deleteForderXml($zip_file_name);
                $this->context->cookie->zip_file_name = '';
                $this->context->cookie->export_sucss = '';
                $this->context->cookie->write();
                $content = $this->exportContent();
                $export_history = new ExportHistory();
                $export_history->file_name = $zip_file_name . '.zip';
                $export_history->content = $content;
                $export_history->date_export = date('Y-m-d h:i:s');
                $export_history->add();
                return $zip_file_name . '.zip';
            }
        }
        return $this->_errors;
    }

    public function exportDataXML14()
    {
        if (!Tools::getValue('submitExportReload')) {
            $this->context->cookie->zip_file_name = '';
            $this->context->cookie->export_sucss = '';
            $this->context->cookie->write();
        }
        $cacheDir = dirname(__file__) . '/cache/export/';
        if (isset($this->context->cookie->zip_file_name) && $this->context->cookie->
            zip_file_name) {
            $zip_file_name = $this->context->cookie->zip_file_name;
        } else {
            $zip_file_name = 'oc2m_data_' . $this->genSecure(7);
            $this->context->cookie->zip_file_name = $zip_file_name;
            $this->context->cookie->write();
            die('Oops. Your jQuery is out of date. Please upgrade your jQuery to jQuery -1.1.11');
        }
        $dir = $cacheDir . $zip_file_name;
        if ($this->context->cookie->export_sucss) {
            $this->deleteForderXml($zip_file_name);
            $this->context->cookie->zip_file_name = '';
            $this->context->cookie->write();
            $this->context->cookie->export_sucss = '';
            $content = $this->exportContent();
            $zip_file_name = $zip_file_name . '.zip';
            Db::getInstance()->Execute('INSERT INTO ' . _DB_PREFIX_ . 'ets_export_history (file_name,content,date_export) values("' . pSQL($zip_file_name) . '","' . pSQL($content, true) . '",NOW())');
            return $zip_file_name;
        }
        if (!is_dir($dir)) {
            @mkdir($dir, 0777);
        }
        $data_exports = explode(',', Configuration::get('ETS_DATAMASTER_EXPORT'));
        $export = new DataExport();
        $extra_export = new ExtraExport();
        file_put_contents($dir . '/DataInfo.xml', $extra_export->exportInfo($dir));
        if ($data_exports) {

            if ($this->checkTableExported($dir, 'lang')) {
                if (!$export->addFileXMl14($dir, 'Language', 'lang', 'id_lang', false))
                    $this->_errors[] = $this->l('Cannot create Language.xml');
                else
                    $this->insertTableExported($dir, 'lang');
            }
            if ($this->checkTableExported($dir, 'currency')) {
                if (!$export->addFileXMl14($dir, 'Currency', 'currency', 'id_currency', false))
                    $this->_errors[] = $this->l('Cannot create Currency.xml');
                else
                    $this->insertTableExported($dir, 'currency');
            }
            if ($this->checkTableExported($dir, 'zone')) {
                if (!$export->addFileXMl14($dir, 'Zone', 'zone', 'id_zone', false))
                    $this->_errors[] = $this->l('Cannot create Zone.xml');
                else
                    $this->insertTableExported($dir, 'zone');
            }
            if ($this->checkTableExported($dir, 'country')) {
                if (!$export->addFileXMl14($dir, 'Country', 'country', 'id_country', true))
                    $this->_errors[] = $this->l('Cannot create Country.xml');
                else
                    $this->insertTableExported($dir, 'country');
            }
            if ($this->checkTableExported($dir, 'state')) {
                if (!$export->addFileXMl14($dir, 'State', 'state', 'id_state', false))
                    $this->_errors[] = $this->l('Cannot create State.xml');
                else
                    $this->insertTableExported($dir, 'state');
            }
            if (in_array('employees', $data_exports)) {
                if ($this->checkTableExported($dir, 'employee')) {
                    if (!$export->addFileXMl14($dir, 'Employee', 'employee', 'id_employee', false))
                        $this->_errors[] = $this->l('Cannot create Employee.xml');
                    else
                        $this->insertTableExported($dir, 'employee');
                }

            }
            if (in_array('categories', $data_exports)) {
                if ($this->checkTableExported($dir, 'group')) {
                    if (!$export->addFileXMl14($dir, 'Group', 'group', 'id_group', true))
                        $this->_errors[] = $this->l('Cannot create Group.xml');
                    else
                        $this->insertTableExported($dir, 'group');
                }
                if ($this->checkTableExported($dir, 'category_group')) {
                    if (!$export->addFileXMl14($dir, 'categorygroup', 'category_group')) {
                        $this->_errors[] = $this->l('Cannot create categorygroup.xml');
                    } else
                        $this->insertTableExported($dir, 'category_group');
                }
                if ($this->checkTableExported($dir, 'category')) {
                    if (!$this->addCategoryFileXMl14($dir, false))
                        $this->_errors[] = $this->l('Cannot create Category.xml');
                    else
                        $this->insertTableExported($dir, 'category');
                }
            }
            if (in_array('customers', $data_exports)) {
                if ($this->checkTableExported($dir, 'group')) {
                    if (!$export->addFileXMl14($dir, 'Group', 'group', 'id_group', true))
                        $this->_errors[] = $this->l('Cannot create Group.xml');
                    else
                        $this->insertTableExported($dir, 'group');
                }
                if ($this->checkTableExported($dir, 'customer')) {
                    if (!$export->addFileXMl14($dir, 'Customer', 'customer', 'id_customer', false))
                        $this->_errors[] = $this->l('Cannot create Customer.xml');
                    else
                        $this->insertTableExported($dir, 'customer');
                }
                if ($this->checkTableExported($dir, 'customer_group')) {
                    if (!$export->addFileXMl14($dir, 'customergroup', 'customer_group')) {
                        $this->_errors[] = $this->l('Cannot create customergroup.xml');
                    } else
                        $this->insertTableExported($dir, 'customer_group');
                }
                if ($this->checkTableExported($dir, 'address')) {
                    if (!$export->addFileXMl14($dir, 'Address', 'address', 'id_address', false))
                        $this->_errors[] = $this->l('Cannot create Address.xml');
                    else
                        $this->insertTableExported($dir, 'address');
                }
            }
            if (in_array('manufactures', $data_exports)) {
                if ($this->checkTableExported($dir, 'manufacturer')) {
                    if (!$export->addFileXMl14($dir, 'Manufacturer', 'manufacturer', 'id_manufacturer', true))
                        $this->_errors[] = $this->l('Cannot create Manufacturer.xml');
                    else
                        $this->insertTableExported($dir, 'manufacturer');
                }
            }
            if (in_array('suppliers', $data_exports)) {
                if ($this->checkTableExported($dir, 'supplier')) {
                    if (!$export->addFileXMl14($dir, 'Supplier', 'supplier', 'id_supplier', true))
                        $this->_errors[] = $this->l('Cannot create Supplier.xml');
                    else
                        $this->insertTableExported($dir, 'supplier');
                }
            }
            if (in_array('carriers', $data_exports)) {
                if ($this->checkTableExported($dir, 'carrier')) {
                    if (!$export->addFileXMl14($dir, 'Carrier', 'carrier', 'id_carrier', true))
                        $this->_errors[] = $this->l('Cannot create Carrier.xml');
                    else
                        $this->insertTableExported($dir, 'carrier');
                }
                if ($this->checkTableExported($dir, 'carrier_zone')) {
                    if (!$export->addFileXMl14($dir, 'carrierzone', 'carrier_zone')) {
                        $this->_errors[] = $this->l('Cannot create carrierzone.xml');
                    } else
                        $this->insertTableExported($dir, 'carrier_zone');
                }
                if ($this->checkTableExported($dir, 'carrier_group')) {
                    if (!$export->addFileXMl14($dir, 'carriergroup', 'carrier_group')) {
                        $this->_errors[] = $this->l('Cannot create carriergroup.xml');
                    } else
                        $this->insertTableExported($dir, 'carrier_group');
                }
                if ($this->checkTableExported($dir, 'range_price')) {
                    if (!$export->addFileXMl14($dir, 'RangePrice', 'range_price', 'id_range_price', false))
                        $this->_errors[] = $this->l('Cannot create RangePrice.xml');
                    else
                        $this->insertTableExported($dir, 'range_price');
                }
                if ($this->checkTableExported($dir, 'range_weight')) {
                    if (!$export->addFileXMl14($dir, 'RangeWeight', 'range_weight', 'id_range_weight', false))
                        $this->_errors[] = $this->l('Cannot create RangeWeight.xml');
                    else
                        $this->insertTableExported($dir, 'range_weight');
                }
                if ($this->checkTableExported($dir, 'delivery')) {
                    if (!$export->addFileXMl14($dir, 'Delivery', 'delivery', 'id_delivery', false))
                        $this->_errors[] = $this->l('Cannot create Delivery.xml');
                    else
                        $this->insertTableExported($dir, 'delivery');
                }
            }
            if (in_array('vouchers', $data_exports)) {
                if ($this->checkTableExported($dir, 'discount')) {
                    if (!$export->addFileXMl14($dir, 'Discount', 'discount', 'id_discount', true))
                        $this->_errors[] = $this->l('Cannot create Discount.xml');
                    else
                        $this->insertTableExported($dir, 'discount');
                }
                if ($this->checkTableExported($dir, 'discount_type')) {
                    if (!$export->addFileXMl14($dir, 'DiscountType', 'discount_type', 'id_discount_type', true))
                        $this->_errors[] = $this->l('Cannot create DiscountType.xml');
                    else
                        $this->insertTableExported($dir, 'discount_type');
                }
            }
            if (in_array('products', $data_exports)) {
                if ($this->checkTableExported($dir, 'category_product')) {
                    if (!$export->addFileXMl14($dir, 'categoryproduct', 'category_product')) {
                        $this->_errors[] = $this->l('Cannot create categoryproduct.xml');  //extra
                    } else
                        $this->insertTableExported($dir, 'category_product');
                }
                if ($this->checkTableExported($dir, 'product')) {
                    if (!$export->addFileXMl14($dir, 'Product', 'product', 'id_product', true))
                        $this->_errors[] = $this->l('Cannot create Product.xml');
                    else
                        $this->insertTableExported($dir, 'product');
                }
                if ($this->checkTableExported($dir, 'tag')) {
                    if (!$export->addFileXMl14($dir, 'Tag', 'tag', 'id_tag'))
                        $this->_errors[] = $this->l('Cannot create Tag.xml');
                    else
                        $this->insertTableExported($dir, 'tag');
                }
                if ($this->checkTableExported($dir, 'image')) {
                    if (!$export->addFileXMl14($dir, 'Image', 'image', 'id_image', true))
                        $this->_errors[] = $this->l('Cannot create Image.xml');
                    else
                        $this->insertTableExported($dir, 'image');
                }
                if ($this->checkTableExported($dir, 'product_attribute')) {
                    if (!$export->addFileXMl14($dir, 'Combination', 'product_attribute', 'id_product_attribute', false))
                        $this->_errors[] = $this->l('Cannot create Combination.xml');
                    else
                        $this->insertTableExported($dir, 'product_attribute');
                }
                if ($this->checkTableExported($dir, 'attribute_group')) {
                    if (!$export->addFileXMl14($dir, 'AttributeGroup', 'attribute_group', 'id_attribute_group', true))
                        $this->_errors[] = $this->l('Cannot create AttributeGroup.xml');
                    else
                        $this->insertTableExported($dir, 'attribute_group');
                }
                if ($this->checkTableExported($dir, 'attribute')) {
                    if (!$export->addFileXMl14($dir, 'Attribute', 'attribute', 'id_attribute', true))
                        $this->_errors[] = $this->l('Cannot create Attribute.xml');
                    else
                        $this->insertTableExported($dir, 'attribute');
                }
                if ($this->checkTableExported($dir, 'product_attribute_combination')) {
                    if (!$export->addFileXMl14($dir, 'productattributecombination', 'product_attribute_combination')) {
                        $this->_errors[] = $this->l('Cannot create productattributecombination.xml'); //extra
                    } else
                        $this->insertTableExported($dir, 'product_attribute_combination');
                }
                if ($this->checkTableExported($dir, 'product_attribute_image')) {
                    if (!$export->addFileXMl14($dir, 'productattributeimage', 'product_attribute_image')) {
                        $this->_errors[] = $this->l('Cannot create productattributeimage.xml'); //extra
                    } else
                        $this->insertTableExported($dir, 'product_attribute_image');
                }
                if ($this->checkTableExported($dir, 'product_tag')) {
                    if (!$export->addFileXMl14($dir, 'producttag', 'product_tag')) {
                        $this->_errors[] = $this->l('Cannot create producttag.xml'); //extra
                    } else
                        $this->insertTableExported($dir, 'product_tag');
                }
                if ($this->checkTableExported($dir, 'feature')) {
                    if (!$export->addFileXMl14($dir, 'Feature', 'feature', 'id_feature', true))
                        $this->_errors[] = $this->l('Cannot create Feature.xml');
                    else
                        $this->insertTableExported($dir, 'feature');
                }
                if ($this->checkTableExported($dir, 'feature_value')) {
                    if (!$export->addFileXMl14($dir, 'FeatureValue', 'feature_value', 'id_feature_value', true))
                        $this->_errors[] = $this->l('Cannot create FeatureValue.xml');
                    else
                        $this->insertTableExported($dir, 'feature_value');
                }
                if ($this->checkTableExported($dir, 'feature_product')) {
                    if (!$export->addFileXMl14($dir, 'featureproduct', 'feature_product')) {
                        $this->_errors[] = $this->l('Cannot create featureproduct.xml'); // extra feature_product;
                    } else
                        $this->insertTableExported($dir, 'feature_product');
                }
                if ($this->checkTableExported($dir, 'specific_price')) {
                    if (!$export->addFileXMl14($dir, 'SpecificPrice', 'specific_price', 'id_specific_price', false))
                        $this->_errors[] = $this->l('Cannot create SpecificPrice.xml');
                    else
                        $this->insertTableExported($dir, 'specific_price');
                }
                if ($this->checkTableExported($dir, 'tax')) {
                    if (!$export->addFileXMl14($dir, 'Tax', 'tax', 'id_tax', true))
                        $this->_errors[] = $this->l('Cannot create Tax.xml');
                    else
                        $this->insertTableExported($dir, 'tax');
                }
                if ($this->checkTableExported($dir, 'tax_rules_group')) {
                    if (!$export->addFileXMl14($dir, 'TaxRulesGroup', 'tax_rules_group', 'id_tax_rules_group', false))
                        $this->_errors[] = $this->l('Cannot create TaxRulesGroup.xml');
                    else
                        $this->insertTableExported($dir, 'tax_rules_group');
                }
                if ($this->checkTableExported($dir, 'tax_rule')) {
                    if (!$export->addFileXMl14($dir, 'TaxRule', 'tax_rule', 'id_tax_rule', false))
                        $this->_errors[] = $this->l('Cannot create TaxRule.xml');
                    else
                        $this->insertTableExported($dir, 'tax_rule');
                }

                if ($this->checkTableExported($dir, 'customization_field')) {
                    if (!$export->addFileXMl14($dir, 'CustomizationField', 'customization_field', 'id_customization_field', true))
                        $this->_errors[] = $this->l('Cannot create CustomizationField.xml');
                    else
                        $this->insertTableExported($dir, 'customization_field');
                }
                if ($this->checkTableExported($dir, 'accessory')) {
                    if (!$export->addFileXMl14($dir, 'accessory', 'accessory')) {
                        $this->_errors[] = $this->l('Cannot create accessory.xml'); //extra
                    } else
                        $this->insertTableExported($dir, 'accessory');
                }
            }
            if (in_array('orders', $data_exports)) {
                if ($this->checkTableExported($dir, 'order_state')) {
                    if (!$export->addFileXMl14($dir, 'OrderState', 'order_state', 'id_order_state', true))
                        $this->_errors[] = $this->l('Cannot create OrderState.xml');
                    else
                        $this->insertTableExported($dir, 'order_state');
                }
                if ($this->checkTableExported($dir, 'cart')) {
                    if (!$export->addFileXMl14($dir, 'Cart', 'cart', 'id_cart', false))
                        $this->_errors[] = $this->l('Cannot create Cart.xml');
                    else
                        $this->insertTableExported($dir, 'cart');
                }
                if ($this->checkTableExported($dir, 'customization')) {
                    if (!$export->addFileXMl14($dir, 'Customization', 'customization', 'id_customization'))
                        $this->_errors[] = $this->l('Cannot create CustomizationField.xml');
                    else
                        $this->insertTableExported($dir, 'customization');
                }
                if ($this->checkTableExported($dir, 'customized_data')) {
                    if (!$export->addFileXMl14($dir, 'customizeddata', 'customized_data'))
                        $this->_errors[] = $this->l('Cannot create customizeddata.xml');
                    else
                        $this->insertTableExported($dir, 'customized_data');
                }
                if ($this->checkTableExported($dir, 'orders')) {
                    if (!$export->addFileXMl14($dir, 'Order', 'orders', 'id_order', false))
                        $this->_errors[] = $this->l('Cannot create Order.xml');
                    else
                        $this->insertTableExported($dir, 'orders');
                }
                if ($this->checkTableExported($dir, 'order_detail')) {
                    if (!$export->addFileXMl14($dir, 'OrderDetail', 'order_detail', 'id_order_detail', false))
                        $this->_errors[] = $this->l('Cannot create OrderDetail.xml');
                    else
                        $this->insertTableExported($dir, 'order_detail');
                }
                if ($this->checkTableExported($dir, 'order_slip')) {
                    if (!$export->addFileXMl14($dir, 'OrderSlip', 'order_slip', 'id_order_slip', false))
                        $this->_errors[] = $this->l('Cannot create OrderSlip.xml');
                    else
                        $this->insertTableExported($dir, 'order_slip');
                }
                if ($this->checkTableExported($dir, 'order_history')) {
                    if (!$export->addFileXMl14($dir, 'OrderHistory', 'order_history', 'id_order_history', false))
                        $this->_errors[] = $this->l('Cannot create OrderHistory.xml');
                    else
                        $this->insertTableExported($dir, 'order_history');
                }
                if ($this->checkTableExported($dir, 'order_message')) {
                    if (!$export->addFileXMl14($dir, 'OrderMessage', 'order_message', 'id_order_message', true))
                        $this->_errors[] = $this->l('Cannot create OrderMessage.xml');
                    else
                        $this->insertTableExported($dir, 'order_message');
                }
                if ($this->checkTableExported($dir, 'order_return')) {
                    if (!$export->addFileXMl14($dir, 'OrderReturn', 'order_return', 'id_order_return', false))
                        $this->_errors[] = $this->l('Cannot create OrderReturn.xml');
                    else
                        $this->insertTableExported($dir, 'order_return');
                }
                if ($this->checkTableExported($dir, 'message')) {
                    if (!$export->addFileXMl14($dir, 'Message', 'message', 'id_message', false))
                        $this->_errors[] = $this->l('Cannot create Message.xml');
                    else
                        $this->insertTableExported($dir, 'message');
                }
            }
            if (in_array('CMS_categories', $data_exports)) {
                if ($this->checkTableExported($dir, 'cms_category')) {
                    if (!$export->addFileXMl14($dir, 'CMSCategory', 'cms_category', 'id_cms_category', true))
                        $this->_errors[] = $this->l('Cannot create CMSCategory.xml');
                    else
                        $this->insertTableExported($dir, 'cms_category');
                }

            }
            if (in_array('CMS', $data_exports)) {
                if ($this->checkTableExported($dir, 'cms')) {
                    if (!$export->addFileXMl14($dir, 'CMS', 'cms', 'id_cms', true))
                        $this->_errors[] = $this->l('Cannot create CMS.xml');
                    else
                        $this->insertTableExported($dir, 'cms');
                }

            }
            if (in_array('messages', $data_exports)) {
                if ($this->checkTableExported($dir, 'contact')) {
                    if (!$export->addFileXMl14($dir, 'Contact', 'contact', 'id_contact', true))
                        $this->_errors[] = $this->l('Cannot create Contact.xml');
                    else
                        $this->insertTableExported($dir, 'contact');
                }
                if ($this->checkTableExported($dir, 'customer_thread')) {
                    if (!$export->addFileXMl14($dir, 'CustomerThread', 'customer_thread', 'id_customer_thread', false))
                        $this->_errors[] = $this->l('Cannot create CustomerThread.xml');
                    else
                        $this->insertTableExported($dir, 'customer_thread');
                }
                if ($this->checkTableExported($dir, 'customer_message')) {
                    if (!$export->addFileXMl14($dir, 'CustomerMessage', 'customer_message', 'id_customer_message', false))
                        $this->_errors[] = $this->l('Cannot create CustomerMessage.xml');
                    else
                        $this->insertTableExported($dir, 'customer_message');
                }

            }
            if (!$this->_errors) {
                $this->zipForderXml($zip_file_name);
                $this->deleteForderXml($zip_file_name);
                $this->context->cookie->zip_file_name = '';
                $this->context->cookie->write();
                $this->context->cookie->export_sucss = '';
                $content = $this->exportContent();
                $zip_file_name = $zip_file_name . '.zip';
                Db::getInstance()->Execute('INSERT INTO ' . _DB_PREFIX_ . 'ets_export_history (file_name,content,date_export) values("' . pSQL($zip_file_name) . '","' . pSQL($content, true) . '",NOW())');
                return $zip_file_name;
            }
        }
        return $this->_errors;
    }

    public function exportDataCSV()
    {
        $zip = new ZipArchive();
        $cacheDir = dirname(__file__) . '/cache/export/';
        $zip_file_name = 'ets_datamaster_' . $this->genSecure(7) . '.zip';
        $data_exports = explode(',', Configuration::get('ETS_DATAMASTER_EXPORT'));
        $export = new DataExport();
        $extra_export = new ExtraExport();
        if ($zip->open($cacheDir . $zip_file_name, ZipArchive::OVERWRITE | ZipArchive::
                CREATE) === true && $data_exports) {
            if (in_array('categories', $data_exports)) {
                $categories = $extra_export->exportCategoryData();
                if (!$zip->addFromString('Category.xls', $export->createDataCSV($categories))) {
                    $this->_errors[] = $this->l('Cannot create Category.xls'); //extra
                }
            }
            if (in_array('shops', $data_exports)) {
                if (!$zip->addFromString('Shop.xls', $export->exportDataCSV(Shop::$definition,
                    array()))) {
                    $this->_errors[] = $this->l('Cannot create Shop.xls'); //extra
                }
            }
            if (in_array('employees', $data_exports)) {
                $employees = $extra_export->exportEmployeeData();
                if (!$zip->addFromString('Employee.xls', $export->createDataCSV($employees))) {
                    $this->_errors[] = $this->l('Cannot create Employee.xls'); //extra
                }
            }
            if (in_array('messages', $data_exports)) {
                $messages = $extra_export->exportMessageData();
                if (!$zip->addFromString('Message.xls', $export->createDataCSV($messages))) {
                    $this->_errors[] = $this->l('Cannot create Message.xls'); //extra
                }
            }
            if (in_array('CMS_categories', $data_exports)) {
                $cmscategories = $extra_export->exportCMSCategoryData();
                if (!$zip->addFromString('CMSCategory.xls', $export->createDataCSV($cmscategories))) {
                    $this->_errors[] = $this->l('Cannot create CMSCategory.xls'); //extra
                }
            }
            if (in_array('CMS', $data_exports)) {
                $cms = $extra_export->exportCMSData();
                if (!$zip->addFromString('CMS.xls', $export->createDataCSV($cms))) {
                    $this->_errors[] = $this->l('Cannot create CMS.xls'); //extra
                }
            }
            if (in_array('manufactures', $data_exports)) {
                $manufacturers = $extra_export->exportDataManufacturer();
                if (!$zip->addFromString('Manufacturer.xls', $export->createDataCSV($manufacturers))) {
                    $this->_errors[] = $this->l('Cannot create Manufacturer.xls');
                }
            }
            if (in_array('suppliers', $data_exports)) {
                $suppliers = $extra_export->exportDataSupplier();
                if (!$zip->addFromString('Supplier.xls', $export->createDataCSV($suppliers))) {
                    $this->_errors[] = $this->l('Cannot create Supplier.xls');
                }
            }
            if (in_array('products', $data_exports)) {
                $products = $extra_export->exportDataProduct();
                if (!$zip->addFromString('Product.xls', $export->createDataCSV($products))) {
                    $this->_errors[] = $this->l('Cannot create Product.xls');
                }
                $productattributes = $extra_export->exportDataProductAttribute();
                if (!$zip->addFromString('ProductAttribute.xls', $export->createDataCSV($productattributes))) {
                    $this->_errors[] = $this->l('Cannot create ProductAttribute.xls');
                }
            }
            if (in_array('customers', $data_exports)) {
                $customers = $extra_export->exportDataCustomer();
                if (!$zip->addFromString('Customer.xls', $export->createDataCSV($customers))) {
                    $this->_errors[] = $this->l('Cannot create Customer.xls');
                }
            }
            if (in_array('carriers', $data_exports)) {
                $carriers = $extra_export->exportCarrierData();
                if (!$zip->addFromString('Carrier.xls', $export->createDataCSV($carriers))) {
                    $this->_errors[] = $this->l('Cannot create Carrier.xls');
                }
                $carrier_deliveries = $extra_export->exportDataCarrierRangePrice();
                if (!$zip->addFromString('CarrierPriceDelivery.xls', $export->createDataCSV($carrier_deliveries))) {
                    $this->_errors[] = $this->l('Cannot create CarrierPriceDelivery.xls');
                }
            }
            if (in_array('cart_rules', $data_exports)) {
                if (!$zip->addFromString('CartRule.xls', $export->exportDataCSV(CartRule::$definition))) {
                    $this->_errors[] = $this->l('Cannot create CartRule.xls');
                }
            }
            if (in_array('catelog_rules', $data_exports)) {
                if (!$zip->addFromString('Catelog_rules.xls', $export->exportDataCSV(SpecificPriceRule::$definition))) {
                    $this->_errors[] = $this->l('Cannot create Catelog_rules.xls');
                }
            }
            if (in_array('orders', $data_exports)) {
                $orders = $extra_export->exportDataOrder();
                if (!$zip->addFromString('Order.xls', $export->createDataCSV($orders))) {
                    $this->_errors[] = $this->l('Cannot create Order.xls');
                }
                $orderdetails = $extra_export->exportDataOrderDetail();
                if (!$zip->addFromString('OrderDetails.xls', $export->createDataCSV($orderdetails))) {
                    $this->_errors[] = $this->l('Cannot create OrderDetails.xls');
                }
            }
            $zip->close();
            if (!is_file($cacheDir . $zip_file_name)) {
                $this->_errors[] = $this->l(sprintf('Could not create %1s', _PS_CACHE_DIR_ . $zip_file_name));
            }
            if (!$this->_errors) {
                $content = $this->exportContent();
                $zip_file_name;
                Db::getInstance()->Execute('INSERT INTO ' . _DB_PREFIX_ .
                    'ets_export_history (file_name,content,date_export) values("' . pSQL($zip_file_name) .
                    '","' . pSQL($content, true) . '",NOW())');
                return $zip_file_name;
            }
        }
    }

    public function ajaxPercentageExport()
    {
        if (ob_get_length() > 0) {
            ob_end_clean();
        }
        $totalItemExport = (int)Configuration::get('ETS_DATAMASTER_TOTAL_EXPORT') * 4;
        $totalItemExported = Configuration::get('ETS_DATAMASTER_EXPORTED');
        if (!$totalItemExport || !$totalItemExported)
            die('total' . $totalItemExport . 'exported' . $totalItemExported);
        if ($totalItemExport && $totalItemExported) {
            die(Tools::jsonEncode(array(
                'percent' => (float)round($totalItemExported * 100 / $totalItemExport, 2),
                'table' => Configuration::get('ETS_TABLE_EXPORT'),
                'totalItemExport' => $totalItemExport,
                'totalItemExported' => $totalItemExported,
                'file' => $this->context->cookie->zip_file_name,
            )));
        } else
            die(die(Tools::jsonEncode(array('percent' => 1,))));
    }

    public function assignHistory()
    {
        if (Tools::isSubmit('downloadpasscustomer') && Tools::isSubmit('id_import_history') &&
            $id_import_history = Tools::getValue('id_import_history')) {
            $customers = Db::getInstance()->executeS('SELECT first_name,last_name,email,passwd FROM ' .
                _DB_PREFIX_ . 'ets_datamaster_customer_pasword WHERE id_import_history=' . (int)
                $id_import_history);
            ob_get_clean();
            ob_start();
            $filename = 'list_new_customer_' . time() . '.csv';
            header('Content-Encoding: UTF-8');
            header("Content-type: text/csv; charset=UTF-8");
            header("Content-Disposition: attachment; filename=$filename");
            header("Pragma: no-cache");
            header("Expires: 0");
            echo "\xEF\xBB\xBF";
            $file = fopen('php://output', 'w');
            fputcsv($file, array(
                'First name',
                'Last name',
                'Email',
                'Password'));
            foreach ($customers as $row) {
                fputcsv($file, $row);
            }
            exit();
        }
        if (Tools::isSubmit('downloadpassemployee') && Tools::isSubmit('id_import_history') &&
            $id_import_history = Tools::getValue('id_import_history')) {
            $employees = Db::getInstance()->executeS('SELECT first_name,last_name,email,passwd FROM ' .
                _DB_PREFIX_ . 'ets_datamaster_employee_pasword WHERE id_import_history=' . (int)
                $id_import_history);
            ob_get_clean();
            ob_start();
            $filename = 'list_new_employee_' . time() . '.csv';
            header('Content-Encoding: UTF-8');
            header("Content-type: text/csv; charset=UTF-8");
            header("Content-Disposition: attachment; filename=$filename");
            header("Pragma: no-cache");
            header("Expires: 0");
            echo "\xEF\xBB\xBF";
            $file = fopen('php://output', 'w');
            fputcsv($file, array(
                'First name',
                'Last name',
                'Email',
                'Password'));
            foreach ($employees as $row) {
                fputcsv($file, $row);
            }
            exit();
        }
        $exports = $this->getExports();
        $imports = $this->getImports();
        $this->context->smarty->assign(array(
            'per_page' => $this->per_page,
            'link_more' => $this->context->link->getAdminLink('AdminDataMasterHistory', true) . '&start=' . $this->per_page,
            'exports' => $exports,
            'imports' => $imports,
            'link' => $this->context->link,
            'tab_history' => Tools::getValue('tabhistory', 'import'),
            'datamaster_import_last' => Configuration::get('ETS_DATAMASTER_IMPORT_LAST'),
            'url_cache' => Tools::getShopDomainSsl(true) . ($this->pres_version != 1.4 ? Context::getContext()->shop->getBaseURI() : __PS_BASE_URI__) . 'modules/ets_oneclicktomigrate/cache/',
        ));
    }

    public function getExports($args = array())
    {
        if (!isset($args['start']) || $args['start'] < 0) {
            $args['start'] = 0;
        }
        $end = (int)$args['start'] + $this->per_page;
        $exports = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'ets_export_history ORDER BY id_export_history DESC LIMIT ' . (int)$args['start'] . ',' . (int)$this->per_page);
        if (isset($args['load_more']) && $args['load_more']) {
            $this->smarty->assign(array(
                'exports' => $exports,
            ));
            return array(
                'html' => $this->display(__FILE__, 'item_exports.tpl'),
                'link_more' => count($exports) >= $this->per_page ? $this->context->link->getAdminLink('AdminDataMasterHistory', true) . '&load_more=' . $args['load_more'] . '&start=' . $end : '',
            );
        }
        return $exports;
    }

    public $per_page = 10;

    public function getImports($args = array())
    {
        if (!isset($args['start']) || $args['start'] < 0) {
            $args['start'] = 0;
        }
        $end = (int)$args['start'] + $this->per_page;
        $imports = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'ets_import_history ORDER BY id_import_history DESC LIMIT ' . (int)$args['start'] . ',' . (int)$this->per_page);
        if ($imports) {
            foreach ($imports as $key => &$import) {
                if ($import['file_name'] && file_exists(dirname(__file__) . '/cache/import/' . $import['file_name'] . '.zip')) {
                    $import['import_ok'] = $this->cleanForderImported($import['id_import_history']);
                    $import['content'] = Db::getInstance()->getValue('SELECT content FROM ' . _DB_PREFIX_ . 'ets_import_history WHERE id_import_history=' . (int)$import['id_import_history']);
                    $import['new_passwd_customer'] = count(Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'ets_datamaster_customer_pasword WHERE id_import_history=' . (int)$import['id_import_history']));
                    $import['new_passwd_employee'] = count(Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'ets_datamaster_employee_pasword WHERE id_import_history=' . (int)$import['id_import_history']));
                } else {
                    unset($imports[$key]);
                }
            }
        }
        if (isset($args['load_more']) && $args['load_more']) {
            $this->smarty->assign(array(
                'imports' => $imports,
            ));
            return array(
                'html' => $this->display(__FILE__, 'item_imports.tpl'),
                'link_more' => count($imports) >= $this->per_page ? $this->context->link->getAdminLink('AdminDataMasterHistory', true) . '&load_more=' . $args['load_more'] . '&start=' . $end : '',
            );
        }
        return $imports;
    }

    public function processAssignExport()
    {
        $this->context->smarty->assign(array('ETS_DT_MODULE_URL_AJAX' => $this->_path .
            'ajax.php?token=' . Tools::getAdminTokenLite('AdminModules'),));
    }

    public function processAssignImport()
    {
        Configuration::updateValue('ETS_DATAMASTER_IMPORT', '');
        //restart.
        if (Tools::isSubmit('restartImport') && $id_import_history = Tools::getValue('id_import_history')) {
            $importHistory = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'ets_import_history WHERE id_import_history=' . (int)$id_import_history);
            if ($importHistory['file_name'] && file_exists(dirname(__file__) . '/cache/import/' . $importHistory['file_name'] . '.zip')) {
                if ($this->extractFileData($importHistory['file_name'])) {
                    if ($this->tables) {
                        foreach ($this->tables as $table) {
                            Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'ets_' . pSQL($table) . '_import where id_import_history="' . (int)$id_import_history . '"');
                        }
                    }
                    //hiep sua.
                    Configuration::updateValue('ETS_DATAMASTER_IMPORT', $importHistory['data']);
                    Configuration::updateValue('ETS_DATAMASTER_IMPORTED', 0);
                    Configuration::updateValue('ETS_DATAMASTER_IMPORTED2', 0);
                    Configuration::updateValue('ETS_DT_IMPORT_ACTIVE', '');
                    $this->context->smarty->assign(array(
                        'form_step1' => $this->displayFromStep(1),
                    ));
                }
            }
        }
        //resume.
        if (Tools::isSubmit('resumeImport') && $id_import_history = Tools::getValue('id_import_history')) {
            if ($id_import_history == (int)Configuration::get('ETS_DATAMASTER_IMPORT_LAST')) {
                $importHistory = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'ets_import_history WHERE id_import_history=' . (int)$id_import_history);
                if ($importHistory['file_name'] && file_exists(dirname(__file__) . '/cache/import/' . $importHistory['file_name'] . '.zip') && file_exists(dirname(__file__) . '/xml/' . $importHistory['file_name'] . '/DataInfo.xml')) {
                    Configuration::updateValue('ETS_DATAMASTER_IMPORT', $importHistory['data']);
                    Configuration::updateValue('ETS_DATAMASTER_IMPORTED', (Configuration::get('ETS_DATAMASTER_IMPORTED') ?: (int)$importHistory['number_import']));
                    Configuration::updateValue('ETS_DATAMASTER_IMPORTED2', (Configuration::get('ETS_DATAMASTER_IMPORTED2') ?: (int)$importHistory['number_import2']));
                    Configuration::updateValue('ETS_DT_IMPORT_ACTIVE', Configuration::get('ETS_DT_IMPORT_ACTIVE') ?: '');
                    $this->context->cookie->id_import_history = $id_import_history;
                    $this->context->cookie->write();
                    if ($importHistory['data']) {
                        $step = 3;
                        $this->context->smarty->assign(array(
                            'form_step1' => $this->displayFromStep(1),
                            'form_step2' => $this->displayFromStep(2),
                            'form_step3' => $this->displayFromStep($step),
                        ));
                    } else {
                        $step = 2;
                        $this->context->smarty->assign(array('form_step1' => $this->displayFromStep(1),
                            'form_step2' => $this->displayFromStep(2)));
                    }
                }
            }
        }
        $this->context->smarty->assign(array(
            'step' => isset($step) && (int)$step ? (int)$step : 1,
            'errors' => $this->errors,
            'link' => Context::getContext()->link,
            'token' => Tools::getValue('token'),
            'ETS_DT_MODULE_URL_AJAX' => $this->_path . 'ajax.php?token=' . Tools::getAdminTokenLite('AdminModules'),
            'ets_datamaster_import' => Tools::isSubmit('submitImport') ? Tools::getValue('data_import', array()) : explode(',', Configuration::get('ETS_DATAMASTER_IMPORT')),
            'ets_datamaster_import_delete' => isset($importHistory) ? $importHistory['delete_before_importing'] : 0,
            'ets_datamaster_import_multi_shop' => isset($importHistory) ? $importHistory['import_multi_shop'] : 0,
            'ets_datamaster_import_force_all_id' => isset($importHistory) ? $importHistory['import_multi_shop'] : 0,
        ));
    }

    public function extractFileData($file_name)
    {
        $savePath = dirname(__file__) . '/cache/import/';
        $extractUrl = $savePath . $file_name . '.zip';
        if (!@file_exists($extractUrl))
            $this->errors[] = $this->l('Zip file does not exist');
        if (!$this->errors) {
            $zip = new ZipArchive();
            if ($zip->open($extractUrl) === true) {
                if ($zip->locateName('DataInfo.xml') === false) {
                    $this->errors[] = $this->l('DataInfo.xml does not  exist');
                    if ($extractUrl) {
                        @unlink($extractUrl);
                    }
                }
            } else
                $this->errors[] = $this->l('Cannot open zip file. It might be broken or damaged');
        }
        if (!$this->errors) {
            if (!is_dir(dirname(__file__) . '/xml/' . $file_name . '/'))
                mkdir(dirname(__file__) . '/xml/' . $file_name . '/', 0755);
            if (!Tools::ZipExtract($extractUrl, dirname(__file__) . '/xml/' . $file_name .
                '/'))
                $this->errors[] = $this->l('Cannot extract zip data');
        }
        if (!$this->errors) {
            if ($id_import_history = (int)Tools::getValue('id_import_history')) {
                $sql = 'UPDATE ' . _DB_PREFIX_ . 'ets_import_history SET file_name="' . pSQL($file_name) .
                    '",date_import=NOW(),currentindex=1,number_import2=0,number_import=0 WHERE id_import_history=' . (int)
                    $id_import_history;
                Db::getInstance()->Execute($sql);
                $this->context->cookie->id_import_history = $id_import_history;
                $this->context->cookie->write();
                Configuration::updateValue('ETS_DATAMASTER_IMPORT_LAST', $id_import_history);
                return true;
            } else {
                $data = 'shops,employees,categories,customers,manufactures,suppliers,carriers,cart_rules,catelog_rules,vouchers,products,orders,CMS_categories,CMS,messages';
                $sql = 'INSERT INTO ' . _DB_PREFIX_ .
                    'ets_import_history (data,file_name,date_import,number_import,number_import2,currentindex,delete_before_importing,force_all_id_number) VALUES("' .
                    pSQL($data) . '","' . pSQL($file_name) . '",NOW(),0,0,1,0,1)';
                Db::getInstance()->Execute($sql);
                $id_import_history = Db::getInstance()->Insert_ID();
                $this->context->cookie->id_import_history = $id_import_history;
                $this->context->cookie->write();
                Configuration::updateValue('ETS_DATAMASTER_IMPORT_LAST', $id_import_history);
                return true;
            }
        } else
            return false;
    }

    public function processAjaxImport()
    {
        $id_import_history = $this->context->cookie->id_import_history;
        if ($id_import_history) {
            $import_history = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ .
                'ets_import_history WHERE id_import_history=' . (int)$id_import_history);
            $xml = simplexml_load_file(dirname(__file__) . '/xml/' . $import_history['file_name'] .
                '/DataInfo.xml');
            $export_datas = explode(',', (string )$xml->exporteddata);
            $datamaster_import = explode(',', $import_history['data']);
            $total_imported = (int)$import_history['number_import'];
            $total_imported2 = (int)$import_history['number_import2'];;
            Db::getInstance()->execute('update ' . _DB_PREFIX_ .
                'ets_import_history set number_import2="' . (int)$total_imported . '"');
            $total = 0;
            $total = (int)$xml->countlang + (int)$xml->countcurrency + (int)$xml->countzone + (int)
                $xml->countcountry + (int)$xml->countstate;
            if (in_array('employees', $export_datas) && in_array('employees', $datamaster_import)) {
                $total += (int)$xml->countemployee;
            }
            if (in_array('categories', $export_datas) && in_array('categories', $datamaster_import)) {
                $total += (int)$xml->counttotalcategory;
            }
            if (in_array('manufactures', $export_datas) && in_array('manufactures', $datamaster_import))
                $total += (int)$xml->countmanufacturer;
            if (in_array('suppliers', $export_datas) && in_array('suppliers', $datamaster_import))
                $total += (int)$xml->countsupplier;
            if (in_array('products', $export_datas) && in_array('products', $datamaster_import)) {
                $total += (int)$xml->counttotalproduct;
            }
            if (in_array('carriers', $export_datas) && in_array('carriers', $datamaster_import)) {
                $total += (int)$xml->counttotalcarrier;;
            }
            if (in_array('cart_rules', $export_datas) && in_array('cart_rules', $datamaster_import))
                $total += (int)$xml->countcartrule;
            if (in_array('catelog_rules', $export_datas) && in_array('catelog_rules', $datamaster_import))
                $total += (int)$xml->countspecificpriceRule;
            if (in_array('customers', $export_datas) && in_array('customers', $datamaster_import))
                $total += (int)$xml->counttotalcustomer;
            if (in_array('orders', $export_datas) && in_array('orders', $datamaster_import))
                $total += (int)$xml->countorder + (int)$xml->countorderstate + (int)$xml->
                    countcart + (int)$xml->countorderdetail + (int)$xml->countorderinvoice + (int)$xml->
                    countorderslip + (int)$xml->countordercarrier + (int)$xml->countordercartrule + (int)
                    $xml->countorderhistory + (int)$xml->countordermessage + (int)$xml->
                    countorderpayment + (int)$xml->countorderreturn;
            $total = $total * 2;
            if ($total_imported && $total) {
                $percent = $total_imported * 100 / $total;
                die(Tools::jsonEncode(array(
                    'percent' => $percent < 98 ? (float)round($percent, 2) : 98,
                    'list_import_active' => trim(Configuration::get('ETS_DT_IMPORT_ACTIVE'), ','),
                    'speed' => ($total_imported > $total_imported2 ? ceil(($total_imported - $total_imported2) / 3) : 1),
                    'totalItemImported' => (int)$total_imported,
                    'table_importing' => Configuration::get('PS_DATAMASTER_IMPORTING'),
                )));
            } else
                die(die(Tools::jsonEncode(array('percent' => 1,))));
        }
    }

    public function processImport($url = false)
    {
        if (!$url) {
            $file_name = 'oc2m_data_' . $this->genSecure(7);
            $savePath = dirname(__file__) . '/cache/import/';
            if (@file_exists($savePath . $file_name . '.zip'))
                @unlink($savePath . $file_name . '.zip');
            $uploader = new Uploader('file_import');
            $uploader->setMaxSize(1048576000);
            $uploader->setAcceptTypes(array('zip'));
            $uploader->setSavePath($savePath);
            $file = $uploader->process($file_name . '.zip');
            if ($file[0]['error'] === 0) {
                if (!Tools::ZipTest($savePath . $file_name . '.zip'))
                    $this->errors[] = $this->l('Zip file seems to be broken');
            } else {
                $this->errors[] = $file[0]['error'];
            }
            $this->extractFileData($file_name);
        } else {
            $url = urldecode(trim($url));
            $parced_url = parse_url($url);
            if (!function_exists('http_build_url')) {
                if (version_compare(_PS_VERSION_, '1.6', '<'))
                    include_once(_PS_MODULE_DIR_ . 'ets_oneclicktomigrate/classes/http_build_url.php');
                else
                    require_once(_PS_TOOL_DIR_ . 'http_build_url/http_build_url.php');
            }
            $url = http_build_url('', $parced_url);
            $file_name = 'oc2m_data_' . $this->genSecure(7);
            $savePath = dirname(__file__) . '/cache/import/';
            $context = stream_context_create(array('http' => array('header' =>
                'User-Agent: Mozilla compatible')));
            if (DataImport::copy($url, $savePath . $file_name . '.zip', $context)) {
                $this->extractFileData($file_name);
            } else {
                $this->errors[] = $this->l('Can not download data from source website. May be the source website is timed out. Please manually download data of source website using Prestashop Connector or <a href="' . $url . '" target="_blank" >click here</a> then select "Upload data file from computer" option to import the data into target website.');
            }

        }
        if ($this->errors) {
            die(Tools::jsonEncode(array(
                'error' => true,
                'errors' => $this->displayError($this->errors),
            )));
        }
    }

    public function processImportdata14()
    {
        $id_import_history = $this->context->cookie->id_import_history;
        $import_history = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ .
            'ets_import_history WHERE id_import_history=' . (int)$id_import_history);
        $file_name = $import_history['file_name'];
        Configuration::updateValue('PS_ALLOW_HTML_IFRAME', 1);
        if (!file_exists(dirname(__file__) . '/xml/' . $file_name . '/DataInfo.xml')) {
            $this->errors[] = $this->l('Data import not validate');
            return false;
        }
        $import = new DataImport();
        $extra_Import = new ExtraImport();
        $datas_import = explode(',', $import_history['data']);
        $import->importData14('Language', 'Language');
        $import->importData14('Currency', 'Currency');
        $foreign_key_country = array('id_zone' => array(
            'table_parent' => 'zone',
            'key' => 'id_zone',
        ));
        $import->importData14('Country', 'Country', $foreign_key_country);
        $foreign_key_state = array('id_country' => array(
            'table_parent' => 'country',
            'key' => 'id_country',
        ), 'id_zone' => array(
            'table_parent' => 'zone',
            'key' => 'id_zone',
        ));
        $import->importData14('State', 'State', $foreign_key_state);
        Configuration::updateValue('ETS_DT_IMPORT_ACTIVE', 'minor_data,');

        if (in_array('employees', $datas_import)) {
            $foreign_key_employee = array('id_lang' => array(
                'table_parent' => 'lang',
                'key' => 'id_lang',
            ));
            $import->importData14('Employee', 'Employee', $foreign_key_employee);
            $import_active = Configuration::get('ETS_DT_IMPORT_ACTIVE');
            $import_active .= 'employees,';
            Configuration::updateValue('ETS_DT_IMPORT_ACTIVE', $import_active);
        }
        if (in_array('categories', $datas_import)) {
            $import->importData14('Group', 'Group');
            $foreign_key_category = array('id_parent' => array(
                'table_parent' => 'category',
                'key' => 'id_category',
            ));
            $import->importData14('Category', 'Category', $foreign_key_category);
            $extra_Import->importCategoryGroup(true);
            $import_active = Configuration::get('ETS_DT_IMPORT_ACTIVE');
            $import_active .= 'categories,';
            Configuration::updateValue('ETS_DT_IMPORT_ACTIVE', $import_active);
        }
        if (in_array('manufactures', $datas_import)) {
            $import->importData14('Manufacturer', 'Manufacturer');
            $import_active = Configuration::get('ETS_DT_IMPORT_ACTIVE');
            $import_active .= 'manufactures,';
            Configuration::updateValue('ETS_DT_IMPORT_ACTIVE', $import_active);
        }
        if (in_array('suppliers', $datas_import)) {
            $import->importData14('Supplier', 'Supplier');
            $import_active = Configuration::get('ETS_DT_IMPORT_ACTIVE');
            $import_active .= 'suppliers,';
            Configuration::updateValue('ETS_DT_IMPORT_ACTIVE', $import_active);
        }
        if (in_array('customers', $datas_import) && !in_array('customers', explode(',', Configuration::get('ETS_DT_IMPORT_ACTIVE')))) {
            $import->importData14('Group', 'Group');
            $foreign_key_customer = array('id_default_group' => array(
                'table_parent' => 'group',
                'key' => 'id_group',
            ));
            $import->importData14('Customer', 'Customer', $foreign_key_customer);
            $extra_Import->importCustomerGroup('customergroup');
            $foreign_key_address = array(
                'id_customer' => array('table_parent' => 'customer', 'key' => 'id_customer'),
                'id_manufacturer' => array(
                    'table_parent' => 'manufacturer',
                    'key' => 'id_manufacturer',
                ),
                'id_supplier' => array('table_parent' => 'supplier', 'key' => 'id_supplier'),
                'id_country' => array('table_parent' => 'country', 'key' => 'id_country'),
                'id_state' => array('table_parent' => 'state', 'key' => 'id_state'));
            $import->importData14('Address', 'Address', $foreign_key_address);
            $import_active = Configuration::get('ETS_DT_IMPORT_ACTIVE');
            $extra_Import->importCategoryGroup(false);
            $import_active .= 'customers,';
            Configuration::updateValue('ETS_DT_IMPORT_ACTIVE', $import_active);
        }
        $this->addPaymentMethod();
        if (in_array('carriers', $datas_import)) {
            $import->importData14('Carrier', 'Carrier');
            $import->importData14('Zone', 'Zone');
            $extra_Import->importCarrierZone('carrierzone');
            $extra_Import->importCarrierGroup();
            $foreign_key_range = array('id_carrier' => array(
                'table_parent' => 'carrier',
                'key' => 'id_carrier',
            ));
            $import->importData14('RangePrice', 'RangePrice', $foreign_key_range);
            $import->importData14('RangeWeight', 'RangeWeight', $foreign_key_range);
            $foreign_key_delivery = array(
                'id_carrier' => array(
                    'table_parent' => 'carrier',
                    'key' => 'id_carrier',
                ),
                'id_range_price' => array(
                    'table_parent' => 'range_price',
                    'key' => 'id_range_price',
                ),
                'id_range_weight' => array(
                    'table_parent' => 'range_weight',
                    'key' => 'id_range_weight',
                ),
                'id_zone' => array(
                    'table_parent' => 'zone',
                    'key' => 'id_zone',
                ),
                'id_shop' => array(
                    'table_parent' => 'shop',
                    'key' => 'id_shop',
                ),
                'id_shop_group' => array(
                    'table_parent' => 'shop_group',
                    'key' => 'id_shop_group',
                ),
            );
            $import->importData14('Delivery', 'Delivery', $foreign_key_delivery);
            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ .
                'delivery SET id_shop=NULL WHERE id_shop=0');
            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ .
                'delivery SET id_shop_group=NULL WHERE id_shop_group=0');
            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'delivery SET id_range_price=NULL WHERE id_range_price=0');
            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'delivery SET id_range_weight=NULL WHERE id_range_weight=0');
            $import_active = Configuration::get('ETS_DT_IMPORT_ACTIVE');
            $import_active .= 'carriers,';
            Configuration::updateValue('ETS_DT_IMPORT_ACTIVE', $import_active);
        }
        if (in_array('vouchers', $datas_import)) {
            $import->importData14('Discount', 'Discount');
            $import_active = Configuration::get('ETS_DT_IMPORT_ACTIVE');
            $import_active .= 'vouchers,';
            Configuration::updateValue('ETS_DT_IMPORT_ACTIVE', $import_active);
        }
        if (in_array('products', $datas_import)) {
            $foreign_key_tag = array('id_lang' => array('table_parent' => 'lang', 'key' =>
                'id_lang'));
            $import->importData14('Tag', 'Tag', $foreign_key_tag);
            $import->importData14('Tax', 'Tax');
            $import->importData14('TaxRulesGroup', 'TaxRulesGroup');
            $foreign_key_tax_rule = array(
                'id_tax_rules_group' => array('table_parent' => 'tax_rules_group', 'key' =>
                    'id_tax_rules_group'),
                'id_tax' => array('table_parent' => 'tax', 'key' => 'id_tax'),
            );
            $import->importData14('TaxRule', 'TaxRule', $foreign_key_tax_rule);
            $foreign_key_product = array(
                'id_category_default' => array(
                    'table_parent' => 'category',
                    'key' => 'id_category',
                ),
                'id_tax_rules_group' => array(
                    'table_parent' => 'tax_rules_group',
                    'key' => 'id_tax_rules_group',
                ),
                'id_manufacturer' => array('table_parent' => 'manufacturer', 'key' =>
                    'id_manufacturer'),
                'id_supplier' => array(
                    'table_parent' => 'supplier',
                    'key' => 'id_supplier',
                ),
            );
            $import->importData14('Product', 'Product', $foreign_key_product);
            $extra_Import->importProductCategory('categoryproduct');
            $extra_Import->importAccessory('accessory');
            $import->importData14('Feature', 'Feature');
            $foreign_key_feature_value = array('id_feature' => array(
                'table_parent' => 'feature',
                'key' => 'id_feature',
            ));
            $import->importData14('FeatureValue', 'FeatureValue', $foreign_key_feature_value);
            $extra_Import->importFeatureProduct('featureproduct');
            $import->importData14('AttributeGroup', 'AttributeGroup');
            $foreign_key_attribute = array('id_attribute_group' => array('table_parent' =>
                'attribute_group', 'key' => 'id_attribute_group'),);
            $import->importData14('Attribute', 'Attribute', $foreign_key_attribute);
            $foreign_key_product_attribute = array('id_product' => array(
                'table_parent' => 'product',
                'key' => 'id_product',
            ),);
            $import->importData14('Combination', 'Combination', $foreign_key_product_attribute);
            $extra_Import->importProductAttributeCombination('productattributecombination');
            $foreign_key_image = array('id_product' => array(
                'table_parent' => 'product',
                'key' => 'id_product',
            ),);
            $import->importData14('Image', 'Image', $foreign_key_image);
            $extra_Import->ImportProductAttributeImages('productattributeimage');
            $extra_Import->importProductTag('producttag');
            $import_active = Configuration::get('ETS_DT_IMPORT_ACTIVE');
            $currentindex = Db::getInstance()->getValue('SELECT currentindex FROM ' . _DB_PREFIX_ . 'ets_import_history WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
            if (file_exists(dirname(__file__) . '/xml/' . $file_name . '/StockAvailable.xml') ||
                file_exists(dirname(__file__) . '/xml/' . $file_name . '/StockAvailable_' . $currentindex .
                    '.xml')) {
                $extra_Import->importDataQuantity14('StockAvailable');
            }
            $import_active .= 'products,';
            Configuration::updateValue('ETS_DT_IMPORT_ACTIVE', $import_active);
        }
        if (in_array('orders', $datas_import)) {
            $import->importData14('OrderState', 'OrderState');
            $foreign_key_cart = array(
                'id_shop_group' => array('table_parent' => 'shop_group', 'key' =>
                    'id_shop_group'),
                'id_shop' => array(
                    'table_parent' => 'shop',
                    'key' => 'id_shop',
                ),
                'id_address_delivery' => array(
                    'table_parent' => 'address',
                    'key' => 'id_address',
                ),
                'id_address_invoice' => array(
                    'table_parent' => 'address',
                    'key' => 'id_address',
                ),
                'id_carrier' => array('table_parent' => 'carrier', 'key' => 'id_carrier'),
                'id_currency' => array('table_parent' => 'currency', 'key' => 'id_currency'),
                'id_customer' => array('table_parent' => 'customer', 'key' => 'id_customer'),
                'id_lang' => array('table_parent' => 'lang', 'key' => 'id_lang'),
            );
            $import->importData14('Cart', 'Cart', $foreign_key_cart);
            $foreign_key_order = array(
                'id_address_delivery' => array(
                    'table_parent' => 'address',
                    'key' => 'id_address',
                ),
                'id_address_invoice' => array('table_parent' => 'address', 'key' => 'id_address'),
                'id_cart' => array(
                    'table_parent' => 'cart',
                    'key' => 'id_cart',
                ),
                'id_currency' => array('table_parent' => 'currency', 'key' => 'id_currency'),
                'id_shop_group' => array('table_parent' => 'shop_group', 'key' =>
                    'id_shop_group'),
                'id_shop' => array(
                    'table_parent' => 'shop',
                    'key' => 'id_shop',
                ),
                'id_customer' => array('table_parent' => 'customer', 'key' => 'id_customer'),
                'id_carrier' => array('table_parent' => 'carrier', 'key' => 'id_carrier'),
                'current_state' => array(
                    'table_parent' => 'order_state',
                    'key' => 'id_order_state',
                ));
            $import->importData14('Order', 'Order', $foreign_key_order);
            $foreign_key_order_slip = array('id_customer' => array(
                'table_parent' => 'customer',
                'key' => 'id_customer',
            ), 'id_order' => array(
                'table_parent' => 'orders',
                'key' => 'id_order',
            ));
            $import->importData14('OrderSlip', 'OrderSlip', $foreign_key_order_slip);
            $foreign_key_order_detail = array(
                'id_order' => array(
                    'table_parent' => 'orders',
                    'key' => 'id_order',
                ),
                'id_order_invoice' => array(
                    'table_parent' => 'order_invoice',
                    'key' => 'id_order_invoice',
                ),
                'id_shop' => array(
                    'table_parent' => 'shop',
                    'key' => 'id_shop',
                ),
                'product_id' => array(
                    'table_parent' => 'product',
                    'key' => 'id_product',
                ),
                'product_attribute_id' => array(
                    'table_parent' => 'product_attribute',
                    'key' => 'id_product_attribute',
                ),
            );
            $import->importData14('OrderDetail', 'OrderDetail', $foreign_key_order_detail);
            $foreign_key_order_history = array('id_order' => array(
                'table_parent' => 'orders',
                'key' => 'id_order',
            ), 'id_order_state' => array(
                'table_parent' => 'order_state',
                'key' => 'id_order_state',
            ));
            $import->importData14('OrderHistory', 'OrderHistory', $foreign_key_order_history);
            $import->importData14('OrderMessage', 'OrderMessage');
            $foreign_key_order_return = array('id_customer' => array(
                'table_parent' => 'customer',
                'key' => 'id_customer',
            ), 'id_order' => array(
                'table_parent' => 'orders',
                'key' => 'id_order',
            ));
            $import->importData14('OrderReturn', 'OrderReturn', $foreign_key_order_return);
            $foreign_key_message = array(
                'id_cart' => array('table_parent' => 'cart', 'key' => 'id_cart'),
                'id_order' => array('table_parent' => 'orders', 'key' => 'id_order'),
                'id_customer' => array('table_parent' => 'customer', 'key' => 'id_customer'),
                'id_employee' => array('table_parent' => 'employee', 'key' => 'id_employee'),
            );
            $import->importData14('Message', 'Message', $foreign_key_message);
            $import_active = Configuration::get('ETS_DT_IMPORT_ACTIVE');
            $import_active .= 'orders,';
            Configuration::updateValue('ETS_DT_IMPORT_ACTIVE', $import_active);
            $orders = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'orders');
            foreach ($orders as $o) {
                $order = new Order($o['id_order']);
                $order_state = new OrderState($order->current_state);
                if ($order_state->invoice && !$order->hasInvoice()) {
                    $order->setInvoice(true);
                }
            }
        }
        if (in_array('CMS_categories', $datas_import)) {
            $import->importData14('CMSCategory', 'CMSCategory');
            $import_active = Configuration::get('ETS_DT_IMPORT_ACTIVE');
            $import_active .= 'CMS_categories,';
            Configuration::updateValue('ETS_DT_IMPORT_ACTIVE', $import_active);
        }
        if (in_array('CMS', $datas_import)) {
            $foreign_key_cms = array('id_cms_category' => array('table_parent' =>
                'cms_category', 'key' => 'id_cms_category'),);
            $import->importData14('CMS', 'CMS', $foreign_key_cms);
            $import_active = Configuration::get('ETS_DT_IMPORT_ACTIVE');
            $import_active .= 'CMS,';
            Configuration::updateValue('ETS_DT_IMPORT_ACTIVE', $import_active);
        }
        if (in_array('messages', $datas_import)) {
            $import->importData14('Contact', 'Contact');
            $foreign_key_customer_thread = array(
                'id_lang' => array('table_parent' => 'lang', 'key' => 'id_lang'),
                'id_contact' => array('table_parent' => 'contact', 'key' => 'id_contact'),
                'id_customer' => array('table_parent' => 'customer', 'key' => 'id_customer'),
                'id_order' => array('table_parent' => 'orders', 'key' => 'id_order'),
                'id_product' => array('table_parent' => 'product', 'key' => 'id_product'),
            );
            $import->importData14('CustomerThread', 'CustomerThread', $foreign_key_customer_thread);
            $foreign_key_customer_message = array(
                'id_employee' => array(
                    'table_parent' => 'employee',
                    'key' => 'id_employee',
                ),
                'id_customer_thread' => array(
                    'table_parent' => 'customer_thread',
                    'key' => 'id_customer_thread',
                ),
            );
            $import->importData14('CustomerMessage', 'CustomerMessage', $foreign_key_customer_message);
            $import_active = Configuration::get('ETS_DT_IMPORT_ACTIVE');
            $import_active .= 'messages,';
            Configuration::updateValue('ETS_DT_IMPORT_ACTIVE', $import_active);
        }
    }

    public function getImportHistory()
    {
        if (isset($this->context->cookie->id_import_history) && $this->context->cookie->id_import_history) {
            return Db::getInstance()->getRow('
                SELECT * FROM ' . _DB_PREFIX_ . 'ets_import_history 
                WHERE id_import_history=' . (int)$this->context->cookie->id_import_history
                , false);
        } else {
            die(Tools::jsonEncode(array(
                'error' => true,
                'import_history' => $this->l('Import history is null'),
            )));
        }
    }

    public function processImportdata()
    {
        if (ob_get_length() > 0) {
            ob_end_clean();
        }
        $id_import_history = $this->context->cookie->id_import_history;
        $import_history = $this->getImportHistory();
        $file_name = $import_history['file_name'];
        Configuration::updateValue('PS_ALLOW_HTML_IFRAME', 1);
        if (!Tools::isSubmit('resumeImport') && !Tools::isSubmit('restartImport')) {
            Configuration::updateValue('ETS_DATAMASTER_IMPORTED', 0);
            Configuration::updateValue('ETS_DATAMASTER_IMPORTED2', 0);
            Configuration::updateValue('ETS_DT_IMPORT_ACTIVE', '');
        }
        Configuration::updateValue('PS_PRODUCT_SHORT_DESC_LIMIT', 10000);
        if (!file_exists(dirname(__file__) . '/xml/' . $file_name . '/DataInfo.xml')) {
            $this->errors[] = $this->l('Data import not validate');
            return false;
        }
        $import = new DataImport();
        $extra_Import = new ExtraImport();
        $datas_import = explode(',', $import_history['data']);

        if (in_array('shops', $datas_import) && !in_array('shops', explode(',', Configuration::get('ETS_DT_IMPORT_ACTIVE')))) {
            Configuration::updateValue('PS_MULTISHOP_FEATURE_ACTIVE', 1);
            if ($tab = Tab::getInstanceFromClassName('AdminShopGroup')) {
                $tab->active = (bool)Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE');
                $tab->update();
            }
            $multishop = true;
            $import->importData('ShopGroup', 'ShopGroup', ShopGroup::$definition);
            $foreign_key_shop = array('id_shop_group' => array('table_parent' => 'shop_group', 'key' => 'id_shop_group'));
            $import->importData('Shop', 'Shop', Shop::$definition, $foreign_key_shop, $multishop);
            $import_active = Configuration::get('ETS_DT_IMPORT_ACTIVE');
            $import_active .= 'shops,';
            Configuration::updateValue('ETS_DT_IMPORT_ACTIVE', $import_active);
        } else
            $multishop = false;
        $import->importData('Language', 'Language', Language::$definition);
        $import->importData('Currency', 'Currency', Currency::$definition, array(), $multishop);
        $import->importData('Zone', 'Zone', Zone::$definition, array(), $multishop);
        $foreign_key_country = array('id_zone' => array(
            'table_parent' => 'zone',
            'key' => 'id_zone',
        ));
        $import->importData('Country', 'Country', Country::$definition, $foreign_key_country, $multishop);
        $foreign_key_state = array('id_country' => array(
            'table_parent' => 'country',
            'key' => 'id_country',
        ), 'id_zone' => array(
            'table_parent' => 'zone',
            'key' => 'id_zone',
        ));
        $import->importData('State', 'State', State::$definition, $foreign_key_state, $multishop);
        if (!in_array('shops', $datas_import))
            Configuration::updateValue('ETS_DT_IMPORT_ACTIVE', 'minor_data,');

        if (in_array('employees', $datas_import) && !in_array('employees', explode(',', Configuration::get('ETS_DT_IMPORT_ACTIVE')))) {
            $foreign_key_employee = array('id_lang' => array(
                'table_parent' => 'lang',
                'key' => 'id_lang',
            ));
            $import->importData('Employee', 'Employee', Employee::$definition, $foreign_key_employee,
                $multishop);
            $import_active = Configuration::get('ETS_DT_IMPORT_ACTIVE');
            $import_active .= 'employees,';
            Configuration::updateValue('ETS_DT_IMPORT_ACTIVE', $import_active);
        }
        if (in_array('categories', $datas_import) && !in_array('categories', explode(',', Configuration::get('ETS_DT_IMPORT_ACTIVE')))) {
            $import->importData('Group', 'Group', Group::$definition, array(), $multishop);
            $foreign_key_category = array('id_parent' => array(
                'table_parent' => 'category',
                'key' => 'id_category',
            ));
            $import->importData('Category', 'Category', Category::$definition, $foreign_key_category,
                $multishop);
            $extra_Import->importCategoryGroup(true);
            $import_active = Configuration::get('ETS_DT_IMPORT_ACTIVE');
            $import_active .= 'categories,';
            Configuration::updateValue('ETS_DT_IMPORT_ACTIVE', $import_active);
            Category::regenerateEntireNtree();
        }
        if (in_array('manufactures', $datas_import) && !in_array('manufactures', explode(',', Configuration::get('ETS_DT_IMPORT_ACTIVE')))) {
            $import->importData('Manufacturer', 'Manufacturer', Manufacturer::$definition, array(), $multishop);
            $import_active = Configuration::get('ETS_DT_IMPORT_ACTIVE');
            $import_active .= 'manufactures,';
            Configuration::updateValue('ETS_DT_IMPORT_ACTIVE', $import_active);
        }
        if (in_array('suppliers', $datas_import) && !in_array('suppliers', explode(',', Configuration::get('ETS_DT_IMPORT_ACTIVE')))) {
            $import->importData('Supplier', 'Supplier', Supplier::$definition, array(), $multishop);
            $import_active = Configuration::get('ETS_DT_IMPORT_ACTIVE');
            $import_active .= 'suppliers,';
            Configuration::updateValue('ETS_DT_IMPORT_ACTIVE', $import_active);
        }
        if (in_array('customers', $datas_import) && !in_array('customers', explode(',', Configuration::get('ETS_DT_IMPORT_ACTIVE')))) {
            $import->importData('Group', 'Group', Group::$definition, array(), $multishop);
            $foreign_key_customer = array('id_default_group' => array(
                'table_parent' => 'group',
                'key' => 'id_group',
            ), 'id_lang' => array(
                'table_parent' => 'lang',
                'key' => 'id_lang',
            ));
            $import->importData('Customer', 'Customer', Customer::$definition, $foreign_key_customer, $multishop);
            $extra_Import->importCustomerGroup('customergroup');
            $foreign_key_address = array(
                'id_customer' => array('table_parent' => 'customer', 'key' => 'id_customer'),
                'id_manufacturer' => array(
                    'table_parent' => 'manufacturer',
                    'key' => 'id_manufacturer',
                ),
                'id_supplier' => array('table_parent' => 'supplier', 'key' => 'id_supplier'),
                'id_country' => array('table_parent' => 'country', 'key' => 'id_country'),
                'id_state' => array('table_parent' => 'state', 'key' => 'id_state'));
            $import->importData('Address', 'Address', Address::$definition, $foreign_key_address, $multishop);
            $extra_Import->importCategoryGroup(false);
            $import_active = Configuration::get('ETS_DT_IMPORT_ACTIVE');
            $import_active .= 'customers,';
            Configuration::updateValue('ETS_DT_IMPORT_ACTIVE', $import_active);
        }

        if (in_array('carriers', $datas_import) && !in_array('carriers', explode(',', Configuration::get('ETS_DT_IMPORT_ACTIVE')))) {
            $import->importData('Carrier', 'Carrier', Carrier::$definition, array(), $multishop);
            $extra_Import->importCarrierZone('carrierzone');
            $extra_Import->importCarrierGroup();
            $foreign_key_range = array('id_carrier' => array(
                'table_parent' => 'carrier',
                'key' => 'id_carrier',
            ));
            $import->importData('RangePrice', 'RangePrice', RangePrice::$definition, $foreign_key_range,
                $multishop);
            $import->importData('RangeWeight', 'RangeWeight', RangeWeight::$definition, $foreign_key_range,
                $multishop);
            $foreign_key_delivery = array(
                'id_carrier' => array(
                    'table_parent' => 'carrier',
                    'key' => 'id_carrier',
                ),
                'id_range_price' => array(
                    'table_parent' => 'range_price',
                    'key' => 'id_range_price',
                ),
                'id_range_weight' => array(
                    'table_parent' => 'range_weight',
                    'key' => 'id_range_weight',
                ),
                'id_zone' => array(
                    'table_parent' => 'zone',
                    'key' => 'id_zone',
                ),
                'id_shop' => array(
                    'table_parent' => 'shop',
                    'key' => 'id_shop',
                ),
                'id_shop_group' => array(
                    'table_parent' => 'shop_group',
                    'key' => 'id_shop_group',
                ),
            );
            $import->importData('Delivery', 'Delivery', Delivery::$definition, $foreign_key_delivery, $multishop);
            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'delivery SET id_shop=NULL WHERE id_shop=0');
            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'delivery SET id_shop_group=NULL WHERE id_shop_group=0');
            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'delivery SET id_range_price=NULL WHERE id_range_price=0');
            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'delivery SET id_range_weight=NULL WHERE id_range_weight=0');
            $import_active = Configuration::get('ETS_DT_IMPORT_ACTIVE');
            $import_active .= 'carriers,';
            Configuration::updateValue('ETS_DT_IMPORT_ACTIVE', $import_active);
        }
        if (in_array('products', $datas_import) && !in_array('products', explode(',', Configuration::get('ETS_DT_IMPORT_ACTIVE')))) {
            $foreign_key_tag = array('id_lang' => array('table_parent' => 'lang', 'key' => 'id_lang'));
            $import->importData('Tag', 'Tag', Tag::$definition, $foreign_key_tag, $multishop);
            $import->importData('Tax', 'Tax', Tax::$definition, array(), $multishop);
            $import->importData('TaxRulesGroup', 'TaxRulesGroup', TaxRulesGroup::$definition, array(), $multishop);
            $foreign_key_tax_rule = array(
                'id_tax_rules_group' => array('table_parent' => 'tax_rules_group', 'key' =>
                    'id_tax_rules_group'),
                'id_tax' => array('table_parent' => 'tax', 'key' => 'id_tax'),
            );
            $import->importData('TaxRule', 'TaxRule', TaxRule::$definition, $foreign_key_tax_rule, $multishop);
            $foreign_key_product = array(
                'id_category_default' => array(
                    'table_parent' => 'category',
                    'key' => 'id_category',
                ),
                'id_tax_rules_group' => array(
                    'table_parent' => 'tax_rules_group',
                    'key' => 'id_tax_rules_group',
                ),
                'id_manufacturer' => array('table_parent' => 'manufacturer', 'key' =>
                    'id_manufacturer'),
                'id_supplier' => array(
                    'table_parent' => 'supplier',
                    'key' => 'id_supplier',
                ),
            );
            $import->importData('Product', 'Product', Product::$definition, $foreign_key_product, $multishop);
            $extra_Import->importProductCategory('categoryproduct');
            $extra_Import->importAccessory('accessory');
            $extra_Import->importProductTag('producttag');
            $extra_Import->importProductPack('pack');
            $import->importData('Feature', 'Feature', Feature::$definition, array(), $multishop);
            $foreign_key_feature_value = array('id_feature' => array(
                'table_parent' => 'feature',
                'key' => 'id_feature',
            ));
            $import->importData('FeatureValue', 'FeatureValue', FeatureValue::$definition, $foreign_key_feature_value, $multishop);
            $extra_Import->importFeatureProduct('featureproduct');
            $import->importData('AttributeGroup', 'AttributeGroup', AttributeGroup::$definition, array(), $multishop);
            $foreign_key_attribute = array('id_attribute_group' => array('table_parent' => 'attribute_group', 'key' => 'id_attribute_group'),);
            $import->importData('Attribute', 'Attribute', Attribute::$definition, $foreign_key_attribute, $multishop);
            $foreign_key_product_attribute = array('id_product' => array(
                'table_parent' => 'product',
                'key' => 'id_product',
            ),);
            $import->importData('Combination', 'Combination', Combination::$definition, $foreign_key_product_attribute, $multishop);
            $extra_Import->importProductAttributeCombination('productattributecombination');
            $extra_Import->importProductSupplier('productsupplier');
            $extra_Import->importProductPack('pack');
            if (in_array('carriers', $datas_import)) {
                $extra_Import->importProductCarrier('productcarrier');
            }
            $foreign_key_image = array('id_product' => array(
                'table_parent' => 'product',
                'key' => 'id_product',
            ),);
            $import->importData('Image', 'Image', Image::$definition, $foreign_key_image, $multishop);
            $extra_Import->ImportProductAttributeImages('productattributeimage');
            $foreign_key_specific_price = array('id_product' => array(
                'table_parent' => 'product',
                'key' => 'id_product',
            ),);
            $import->importData('SpecificPrice', 'SpecificPrice', SpecificPrice::$definition, $foreign_key_specific_price, $multishop);
            Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'specific_price WHERE id_product=0');

            $currentindex = Db::getInstance()->getValue('
                SELECT currentindex 
                FROM ' . _DB_PREFIX_ . 'ets_import_history 
                WHERE id_import_history=' . (int)$this->context->cookie->id_import_history
            );
            if (file_exists(dirname(__file__) . '/xml/' . $file_name . '/StockAvailable.xml') || file_exists(dirname(__file__) . '/xml/' . $file_name . '/StockAvailable_' . $currentindex . '.xml')) {
                $foreign_key_stock_available = array(
                    'id_product' => array('table_parent' => 'product', 'key' => 'id_product'),
                    'id_product_attribute' => array('table_parent' => 'product_attribute', 'key' => 'id_product_attribute'),
                    'id_shop' => array('table_parent' => 'shop', 'key' => 'id_shop'),
                    'id_shop_group' => array('table_parent' => 'shop_group', 'key' => 'id_shop_group'),
                );
                $import->importData('StockAvailable', 'StockAvailable', StockAvailable::$definition, $foreign_key_stock_available, $multishop);
            } else {
                if (!Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'ets_stock_available_import where id_import_history=' . (int)$id_import_history)) {
                    $stockAvailables = Db::getInstance()->executeS('SELECT pa.id_product,pa.id_product_attribute,pas.id_shop,pa.quantity FROM ' . _DB_PREFIX_ . 'product_attribute pa,' . _DB_PREFIX_ . 'product_attribute_shop pas WHERE pa.id_product_attribute =pas.id_product_attribute AND pa.id_product IN (SELECT id_new FROM ' . _DB_PREFIX_ . 'ets_product_import WHERE id_import_history=' . (int)$id_import_history . ') GROUP BY pa.id_product,pa.id_product_attribute,pas.id_shop');
                    if ($stockAvailables) {
                        foreach ($stockAvailables as $stockAvailable) {
                            if (Shop::getContext() == Shop::CONTEXT_ALL) {
                                $shops = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'shop');
                                foreach ($shops as $shop) {
                                    if ($id_stock_available = Db::getInstance()->getValue('SELECT id_stock_available FROM ' . _DB_PREFIX_ . 'stock_available WHERE id_product="' . (int)$stockAvailable['id_product'] .
                                        '" AND id_product_attribute="' . (int)$stockAvailable['id_product_attribute'] .
                                        '" AND id_shop="' . (int)$shop['id_shop'] . '"')) {
                                        $class_stock = new StockAvailable($id_stock_available);
                                        $class_stock->quantity = (int)$stockAvailable['quantity'];
                                        $class_stock->update();
                                    } else {
                                        $class_stock = new StockAvailable();
                                        $class_stock->id_product = (int)$stockAvailable['id_product'];
                                        $class_stock->id_product_attribute = (int)$stockAvailable['id_product_attribute'];
                                        $class_stock->quantity = (int)$stockAvailable['quantity'];
                                        $class_stock->id_shop = (int)$shop['id_shop'];
                                        $class_stock->out_of_stock = 2;
                                        $class_stock->add();
                                    }
                                }
                            } else {
                                if ($id_stock_available = Db::getInstance()->getValue('SELECT id_stock_available FROM ' . _DB_PREFIX_ . 'stock_available WHERE id_product="' . (int)$stockAvailable['id_product'] .
                                    '" AND id_product_attribute="' . (int)$stockAvailable['id_product_attribute'] .
                                    '" AND id_shop="' . (int)$stockAvailable['id_shop'] . '"')) {
                                    $class_stock = new StockAvailable($id_stock_available);
                                    $class_stock->quantity = (int)$stockAvailable['quantity'];
                                    $class_stock->update();
                                } else {
                                    $class_stock = new StockAvailable();
                                    $class_stock->id_product = (int)$stockAvailable['id_product'];
                                    $class_stock->id_product_attribute = (int)$stockAvailable['id_product_attribute'];
                                    $class_stock->quantity = (int)$stockAvailable['quantity'];
                                    $class_stock->id_shop = (int)$stockAvailable['id_shop'];
                                    $class_stock->out_of_stock = 2;
                                    $class_stock->add();
                                }
                            }
                        }
                    }
                }
            }
            if (file_exists(dirname(__FILE__) . '/xml/' . $file_name . '/Warehouse.xml') || file_exists(dirname(__FILE__) . '/xml/' . $file_name . '/Warehouse_' . $currentindex . '.xml')) {
                $foreign_key_warehouse = array(
                    'id_address' => array(
                        'table_parent' => 'address',
                        'key' => 'id_address'
                    ),
                    'id_employee' => array(
                        'table_parent' => 'employee',
                        'key' => 'id_employee'
                    ),
                    'id_currency' => array(
                        'table_parent' => 'currency',
                        'key' => 'id_currency'
                    ),
                );
                $import->importData('Warehouse', 'Warehouse', Warehouse::$definition, $foreign_key_warehouse, $multishop);
            }
            if (file_exists(dirname(__FILE__) . '/xml/' . $file_name . '/WarehouseProductLocation.xml') || file_exists(dirname(__FILE__) . '/xml/' . $file_name . '/WarehouseProductLocation_' . $currentindex . '.xml')) {
                $foreign_key_warehouseProductLocation = array(
                    'id_product' => array(
                        'table_parent' => 'product',
                        'key' => 'id_product'
                    ),
                    'id_product_attribute' => array(
                        'table_parent' => 'product_attribute',
                        'key' => 'id_product_attribute'
                    ),
                    'id_warehouse' => array(
                        'table_parent' => 'warehouse',
                        'key' => 'id_warehouse'
                    ),
                );
                $import->importData('WarehouseProductLocation', 'WarehouseProductLocation', WarehouseProductLocation::$definition, $foreign_key_warehouseProductLocation, $multishop);
            }
            $extra_Import->importWarehouseCarrier('warehousecarrier');
            if (file_exists(dirname(__FILE__) . '/xml/' . $file_name . '/Stock.xml') || file_exists(dirname(__FILE__) . '/xml/' . $file_name . '/Stock_' . $currentindex . '.xml')) {
                $foreign_key_sotck = array(
                    'id_product' => array(
                        'table_parent' => 'product',
                        'key' => 'id_product'
                    ),
                    'id_warehouse' => array(
                        'table_parent' => 'warehouse',
                        'key' => 'id_warehouse'
                    ),
                    'id_product_attribute' => array(

                        'table_parent' => 'product_attribute',

                        'key' => 'id_product_attribute'

                    ),
                );
                $import->importData('Stock', 'Stock', Stock::$definition, $foreign_key_sotck, $multishop);
            }
            if (version_compare(_PS_VERSION_, '1.6.1', '>=')) {
                $foreign_key_customization_field = array('id_product' => array('table_parent' => 'product', 'key' => 'id_product'),);
                $import->importData('CustomizationField', 'CustomizationField', CustomizationField::$definition, $foreign_key_customization_field, $multishop);
            }
            $import_active = Configuration::get('ETS_DT_IMPORT_ACTIVE');
            $import_active .= 'products,';
            Configuration::updateValue('ETS_DT_IMPORT_ACTIVE', $import_active);
        }
        if (in_array('cart_rules', $datas_import) && !in_array('cart_rules', explode(',', Configuration::get('ETS_DT_IMPORT_ACTIVE')))) {
            $foreign_key_cart_rule = array('id_customer' => array(
                'table_parent' => 'customer',
                'key' => 'id_customer',
            ));
            $import->importData('CartRule', 'CartRule', CartRule::$definition, $foreign_key_cart_rule, $multishop);
            $extra_Import->importCartRuleCarrier('cartrulecarrier');
            $extra_Import->importCartRuleCombination('cartrulecombination');
            $extra_Import->importCartRuleCountry('cartrulecountry');
            $extra_Import->importCartRuleGroup('cartrulegroup');
            $extra_Import->importCartRuleProductRuleGroup('cartruleproductrulegroup');
            $extra_Import->importCartRuleProductRule('cartruleproductrule');
            $extra_Import->importCartRuleProductRuleValue('cartruleproductrulevalue');
            $import_active = Configuration::get('ETS_DT_IMPORT_ACTIVE');
            $import_active .= 'cart_rules,';
            Configuration::updateValue('ETS_DT_IMPORT_ACTIVE', $import_active);
        }
        if (in_array('catelog_rules', $datas_import) && !in_array('catelog_rules', explode(',', Configuration::get('ETS_DT_IMPORT_ACTIVE')))) {
            $import->importData('SpecificPriceRule', 'SpecificPriceRule', SpecificPriceRule::$definition, array(), $multishop);
            $import_active = Configuration::get('ETS_DT_IMPORT_ACTIVE');
            $import_active .= 'catelog_rules,';
            Configuration::updateValue('ETS_DT_IMPORT_ACTIVE', $import_active);
        }
        if (in_array('orders', $datas_import) && !in_array('orders', explode(',', Configuration::get('ETS_DT_IMPORT_ACTIVE')))) {
            $import->importData('OrderState', 'OrderState', OrderState::$definition, array(), $multishop);
            $foreign_key_cart = array(
                'id_shop_group' => array('table_parent' => 'shop_group', 'key' =>
                    'id_shop_group'),
                'id_shop' => array(
                    'table_parent' => 'shop',
                    'key' => 'id_shop',
                ),
                'id_address_delivery' => array(
                    'table_parent' => 'address',
                    'key' => 'id_address',
                ),
                'id_address_invoice' => array(
                    'table_parent' => 'address',
                    'key' => 'id_address',
                ),
                'id_carrier' => array('table_parent' => 'carrier', 'key' => 'id_carrier'),
                'id_currency' => array('table_parent' => 'currency', 'key' => 'id_currency'),
                'id_customer' => array('table_parent' => 'customer', 'key' => 'id_customer'),
                'id_lang' => array('table_parent' => 'lang', 'key' => 'id_lang'),
            );
            $import->importData('Cart', 'Cart', Cart::$definition, $foreign_key_cart, $multishop);
            if (version_compare(_PS_VERSION_, '1.6.1', '>=')) {
                $foreign_key_customization = array(
                    'id_product_attribute' => array(
                        'table_parent' => 'product_attribute',
                        'key' => 'id_product_attribute',
                    ),
                    'id_product' => array(
                        'table_parent' => 'product',
                        'key' => 'id_product',
                    ),
                    'id_address_delivery' => array(
                        'table' => 'address',
                        'key' => 'id_address',
                    ),
                    'id_cart' => array(
                        'table' => 'cart',
                        'key' => 'id_cart',
                    ));
                $import->importData('Customization', 'Customization', Customization::$definition,
                    $foreign_key_customization, $multishop);
                $extra_Import->ImportCustomizedData('customizeddata');
            }
            $foreign_key_order = array(
                'id_address_delivery' => array(
                    'table_parent' => 'address',
                    'key' => 'id_address',
                ),
                'id_address_invoice' => array('table_parent' => 'address', 'key' => 'id_address'),
                'id_cart' => array(
                    'table_parent' => 'cart',
                    'key' => 'id_cart',
                ),
                'id_currency' => array('table_parent' => 'currency', 'key' => 'id_currency'),
                'id_shop_group' => array('table_parent' => 'shop_group', 'key' =>
                    'id_shop_group'),
                'id_shop' => array(
                    'table_parent' => 'shop',
                    'key' => 'id_shop',
                ),
                'id_customer' => array('table_parent' => 'customer', 'key' => 'id_customer'),
                'id_carrier' => array('table_parent' => 'carrier', 'key' => 'id_carrier'),
                'current_state' => array(
                    'table_parent' => 'order_state',
                    'key' => 'id_order_state',
                ));
            $import->importData('Order', 'Order', Order::$definition, $foreign_key_order, $multishop);

            $foreign_key_order_invoice = array('id_order' => array(
                'table_parent' => 'orders',
                'key' => 'id_order',
            ));
            $import->importData('OrderInvoice', 'OrderInvoice', OrderInvoice::$definition, $foreign_key_order_invoice, $multishop);
            $extra_Import->importOrderInvoiceTax('order_invoice_tax');

            $foreign_key_order_slip = array('id_customer' => array(
                'table_parent' => 'customer',
                'key' => 'id_customer',
            ), 'id_order' => array(
                'table_parent' => 'orders',
                'key' => 'id_order',
            ));
            $import->importData('OrderSlip', 'OrderSlip', OrderSlip::$definition, $foreign_key_order_slip, $multishop);

            $foreign_key_order_detail = array(
                'id_order' => array(
                    'table_parent' => 'orders',
                    'key' => 'id_order',
                ),
                'id_order_invoice' => array(
                    'table_parent' => 'order_invoice',
                    'key' => 'id_order_invoice',
                ),
                'id_shop' => array(
                    'table_parent' => 'shop',
                    'key' => 'id_shop',
                ),
                'product_id' => array(
                    'table_parent' => 'product',
                    'key' => 'id_product',
                ),
                'product_attribute_id' => array(
                    'table_parent' => 'product_attribute',
                    'key' => 'id_product_attribute',
                ),
            );
            $import->importData('OrderDetail', 'OrderDetail', OrderDetail::$definition, $foreign_key_order_detail, $multishop);
            $extra_Import->importOrderDetailTax('order_detail_tax');

            $foreign_key_order_carrier = array(
                'id_carrier' => array(
                    'table_parent' => 'carrier',
                    'key' => 'id_carrier',
                ),
                'id_order' => array(
                    'table_parent' => 'orders',
                    'key' => 'id_order'
                ),
                'id_order_invoice' => array(
                    'table_parent' => 'order_invoice',
                    'key' => 'id_order_invoice'
                )
            );
            $import->importData('OrderCarrier', 'OrderCarrier', OrderCarrier::$definition, $foreign_key_order_carrier, $multishop);

            $foreign_key_order_cart_rule = array(
                'id_order' => array(
                    'table_parent' => 'orders',
                    'key' => 'id_order',
                ),
                'id_cart_rule' => array(
                    'table_parent' => 'cart_rule',
                    'key' => "id_cart_rule",
                ),
                'id_order_invoice' => array(
                    'table_parent' => 'order_invoice',
                    'key' => 'id_order_invoice'
                )
            );
            $import->importData('OrderCartRule', 'OrderCartRule', OrderCartRule::$definition, $foreign_key_order_cart_rule, $multishop);

            $foreign_key_order_history = array(
                'id_order' => array(
                    'table_parent' => 'orders',
                    'key' => 'id_order',
                ),
                'id_order_state' => array(
                    'table_parent' => 'order_state',
                    'key' => 'id_order_state',
                )
            );
            $import->importData('OrderHistory', 'OrderHistory', OrderHistory::$definition, $foreign_key_order_history, $multishop);

            $import->importData('OrderMessage', 'OrderMessage', OrderMessage::$definition, array(), $multishop);

            $foreign_key_order_payment = array(
                'id_currency' => array(
                    'table_parent' => 'currency',
                    'key' => 'id_currency'
                )
            );
            $import->importData('OrderPayment', 'OrderPayment', OrderPayment::$definition, $foreign_key_order_payment);
            $extra_Import->importOrderInvoicePayment('order_invoice_payment');

            $foreign_key_order_return = array('id_customer' => array(
                'table_parent' => 'customer',
                'key' => 'id_customer',
            ), 'id_order' => array(
                'table_parent' => 'orders',
                'key' => 'id_order',
            ));
            $import->importData('OrderReturn', 'OrderReturn', OrderReturn::$definition, $foreign_key_order_return, $multishop);

            $foreign_key_message = array(
                'id_cart' => array('table_parent' => 'cart', 'key' => 'id_cart'),
                'id_order' => array('table_parent' => 'orders', 'key' => 'id_order'),
                'id_customer' => array('table_parent' => 'customer', 'key' => 'id_customer'),
                'id_employee' => array('table_parent' => 'employee', 'key' => 'id_employee'),
            );
            $import->importData('Message', 'Message', Message::$definition, $foreign_key_message, $multishop);

            $import_active = Configuration::get('ETS_DT_IMPORT_ACTIVE');
            $import_active .= 'orders,';
            Configuration::updateValue('ETS_DT_IMPORT_ACTIVE', $import_active);
        }
        if (in_array('CMS_categories', $datas_import) && !in_array('CMS_categories', explode(',', Configuration::get('ETS_DT_IMPORT_ACTIVE')))) {
            $import->importData('CMSCategory', 'CMSCategory', CMSCategory::$definition, array(), $multishop);
            $import_active = Configuration::get('ETS_DT_IMPORT_ACTIVE');
            $import_active .= 'CMS_categories,';
            Configuration::updateValue('ETS_DT_IMPORT_ACTIVE', $import_active);
        }
        if (in_array('CMS', $datas_import) && !in_array('CMS', explode(',', Configuration::get('ETS_DT_IMPORT_ACTIVE')))) {
            $foreign_key_cms = array('id_cms_category' => array('table_parent' => 'cms_category', 'key' => 'id_cms_category'),);
            $import->importData('CMS', 'CMS', CMS::$definition, $foreign_key_cms, $multishop);
            $import_active = Configuration::get('ETS_DT_IMPORT_ACTIVE');
            $import_active .= 'CMS,';
            Configuration::updateValue('ETS_DT_IMPORT_ACTIVE', $import_active);
        }
        if (in_array('messages', $datas_import) && !in_array('messages', explode(',', Configuration::get('ETS_DT_IMPORT_ACTIVE')))) {

            $import->importData('Contact', 'Contact', Contact::$definition, array(), $multishop);
            $foreign_key_customer_thread = array(
                'id_lang' => array('table_parent' => 'lang', 'key' => 'id_lang'),
                'id_contact' => array('table_parent' => 'contact', 'key' => 'id_contact'),
                'id_customer' => array('table_parent' => 'customer', 'key' => 'id_customer'),
                'id_order' => array('table_parent' => 'orders', 'key' => 'id_order'),
                'id_product' => array('table_parent' => 'product', 'key' => 'id_product'),
                'id_shop' => array(
                    'table_parent' => 'shop',
                    'key' => 'id_shop',
                ));
            $import->importData('CustomerThread', 'CustomerThread', CustomerThread::$definition, $foreign_key_customer_thread, $multishop);

            $foreign_key_customer_message = array(
                'id_employee' => array(
                    'table_parent' => 'employee',
                    'key' => 'id_employee',
                ),
                'id_customer_thread' => array(
                    'table_parent' => 'customer_thread',
                    'key' => 'id_customer_thread',
                ),
            );
            $import->importData('CustomerMessage', 'CustomerMessage', CustomerMessage::$definition, $foreign_key_customer_message, $multishop);

            $import_active = Configuration::get('ETS_DT_IMPORT_ACTIVE');
            $import_active .= 'messages,';
            Configuration::updateValue('ETS_DT_IMPORT_ACTIVE', $import_active);
        }
        $this->addPaymentMethod();
    }

    public function getInformationImport($export_datas, $xml)
    {
        $assign = array();
        if (in_array('shops', $export_datas))
            $assign['shops'] = (int)$xml->countshop;
        if (in_array('employees', $export_datas))
            $assign['employees'] = (int)$xml->countemployee;
        if (in_array('categories', $export_datas))
            $assign['categories'] = (int)$xml->countcategory;
        if (in_array('manufactures', $export_datas))
            $assign['manufactures'] = (int)$xml->countmanufacturer;
        if (in_array('suppliers', $export_datas))
            $assign['suppliers'] = (int)$xml->countsupplier;
        if (in_array('products', $export_datas))
            $assign['products'] = (int)$xml->countproduct;
        if (in_array('carriers', $export_datas))
            $assign['carriers'] = (int)$xml->countcarrier;
        if (in_array('cart_rules', $export_datas))
            $assign['cart_rules'] = (int)$xml->countcartrule;
        if (in_array('catelog_rules', $export_datas))
            $assign['catelog_rules'] = (int)$xml->countspecificpriceRule;
        if (in_array('vouchers', $export_datas))
            $assign['vouchers'] = (int)$xml->countvoucher;
        if (in_array('customers', $export_datas))
            $assign['customers'] = (int)$xml->countcustomer;
        if (in_array('orders', $export_datas))
            $assign['orders'] = (int)$xml->countorder;
        if (in_array('CMS_categories', $export_datas))
            $assign['CMS_categories'] = (int)$xml->countcmscategory;
        if (in_array('CMS', $export_datas))
            $assign['CMS'] = (int)$xml->countcms;
        if (in_array('messages', $export_datas))
            $assign['messages'] = (int)$xml->countmessage;
        return $assign;
    }

    public function getBaseLink()
    {
        if ($this->pres_version == 1.4) {
            $url = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') . Configuration::get('PS_SHOP_DOMAIN') . __PS_BASE_URI__;
        } else
            $url = (Configuration::get('PS_SSL_ENABLED_EVERYWHERE') ? 'https://' : 'http://') . $this->context->shop->domain . $this->context->shop->getBaseURI();
        return trim($url, '/') . '/';
    }

    public function genSecure($size)
    {
        $chars = md5(time());
        $code = '';
        for ($i = 1; $i <= $size; ++$i) {
            $char = Tools::substr($chars, rand(0, Tools::strlen($chars) - 1), 1);
            if ($char == 'e')
                $char = 'a';
            $code .= $char;
        }
        return $code;
    }

    public static function upperFirstChar($t)
    {
        return Tools::ucfirst($t);
    }

    public function processClean()
    {
        $errors = array();
        if (Tools::isSubmit('submit_clear_history')) {
            if (!Tools::getValue('import_history') && !Tools::getValue('export_history')) {
                $errors[] = $this->l('Please select a type of history data to delete');
            }
            $clear = Tools::getValue('ETS_DATAMATER_CLEAR');
            switch ($clear) {
                case 'last_hour':
                    $date = date('Y-m-d h:i:s', strtotime('-1 HOUR'));
                    break;
                case 'last_tow_hours':
                    $date = date('Y-m-d h:i:s', strtotime('-2 HOUR'));
                    break;
                case 'last_four_hours':
                    $date = date('Y-m-d h:i:s', strtotime('-4 HOUR'));
                    break;
                case 'today':
                    $date = date('Y-m-d');
                    break;
                case '1_week':
                    $date = date('Y-m-d', strtotime('-1 WEEK'));
                    break;
                case '1_month_ago':
                    $date = date('Y-m-d', strtotime('-1 MONTH'));
                    break;
                case '1_year_ago':
                    $date = date('Y-m-d h:i:s', strtotime('-4 YEAR'));
                    break;
                case 'everything':
                    $date = '';
                    break;
            }
            if (Tools::getValue('import_history')) {
                $sql_import = 'SELECT * FROM ' . _DB_PREFIX_ . 'ets_import_history' . ($date !=
                    '' ? ' WHERE date_import>="' . pSQL($date) . '"' : '');
                $imports = Db::getInstance()->executeS($sql_import);
                if ($imports) {
                    foreach ($imports as $import) {
                        if ($import['file_name'] && file_exists(dirname(__file__) . '/cache/import/' . $import['file_name'] .
                                '.zip'))
                            @unlink(dirname(__file__) . '/cache/import/' . $import['file_name'] . '.zip');
                        foreach (glob(dirname(__file__) . '/xml/' . $import['file_name'] . '/*.*') as $filename) {
                            @unlink($filename);
                        }
                        @rmdir(dirname(__file__) . '/xml/' . $import['file_name']);
                        Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'ets_import_history WHERE id_import_history=' . (int)$import['id_import_history']);
                    }
                }
            }
            if (Tools::getValue('export_history')) {
                $sql_export = 'SELECT * FROM ' . _DB_PREFIX_ . 'ets_export_history' . ($date !=
                    '' ? ' WHERE date_export>="' . pSQL($date) . '"' : '');
                $exports = Db::getInstance()->executeS($sql_export);
                if ($exports) {
                    foreach ($exports as $export) {
                        if ($export['file_name'] && file_exists(dirname(__file__) . '/cache/export/' . $export['file_name']))
                            @unlink(dirname(__file__) . '/cache/import/' . $export['file_name']);
                        Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'ets_export_history where id_export_history=' . (int)$export['id_export_history']);
                    }
                }
            }
        }
        $this->context->smarty->assign(array(
            'link' => $this->context->link,
            'submit_clear_history' => Tools::isSubmit('submit_clear_history'),
            'message_error' => $errors ? $this->displayError($errors) : false,
        ));
    }

    public function cleanForderImported($id_history)
    {
        $sql_import = 'SELECT * FROM ' . _DB_PREFIX_ . 'ets_import_history where id_import_history=' . (int)$id_history;
        $import = Db::getInstance()->getRow($sql_import);
        $currentindex = $import['currentindex'];
        $ok = true;
        if (!file_exists(dirname(__file__) . '/xml/' . $import['file_name'] .
            '/DataInfo.xml'))
            return true;
        $xml = simplexml_load_file(dirname(__file__) . '/xml/' . $import['file_name'] .
            '/DataInfo.xml');
        $data_exports = explode(',', (string )$xml->exporteddata);
        $contents = array();
        if (in_array('shops', $data_exports)) {
            $countShop = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'ets_shop_import where id_import_history="' . (int)$import['id_import_history'] .
                '"');
            $contents[] = array(
                'title' => $this->l('Shops:'),
                'count' => $countShop,
                'count_xml' => (int)$xml->countshop,
            );
            if (file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/Shop.xml'))
                $ok = false;
        }
        if (in_array('employees', $data_exports)) {
            $countEmployee = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'ets_employee_import where id_import_history="' . (int)$import['id_import_history'] .
                '"');
            $contents[] = array(
                'title' => $this->l('Employees:'),
                'count' => $countEmployee,
                'count_xml' => (int)$xml->countemployee,
            );
            if (file_exists(dirname(__file__) . '/xml/' . $import['file_name'] .
                '/Employee.xml'))
                $ok = false;
        }
        if (in_array('categories', $data_exports)) {
            $countCategory = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'ets_category_import where id_import_history="' . (int)$import['id_import_history'] . '"');
            $contents[] = array(
                'title' => $this->l('Categories:'),
                'count' => $countCategory,
                'count_xml' => (int)$xml->countcategory,
            );
            if (file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/Category.xml') || file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/Category_1.xml') || file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/Category_' . $currentindex . '.xml'))
                $ok = false;
        }
        if (in_array('products', $data_exports)) {
            $countProduct = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'ets_product_import where id_import_history="' . (int)$import['id_import_history'] .
                '"');
            $contents[] = array(
                'title' => $this->l('Products:'),
                'count' => $countProduct,
                'count_xml' => (int)$xml->countproduct,
            );
            if (file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/Image.xml') || file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/Product.xml') || file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/Image_1.xml') || file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/Product_1.xml') || file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/Image_' . $currentindex . '.xml') || file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/Product_' . $currentindex . '.xml'))
                $ok = false;
        }
        if (in_array('customers', $data_exports)) {
            $countCustomer = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'ets_customer_import where id_import_history="' . (int)$import['id_import_history'] .
                '"');
            $contents[] = array(
                'title' => $this->l('Customers:'),
                'count' => $countCustomer,
                'count_xml' => (int)$xml->countcustomer,
            );
            if (file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/Address.xml') || file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/Address_1.xml') || file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/Address_' . $currentindex . '.xml'))
                $ok = false;
        }
        if (in_array('orders', $data_exports)) {
            $countOrder = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'ets_orders_import where id_import_history="' . (int)$import['id_import_history'] .
                '"');
            $contents[] = array(
                'title' => $this->l('Orders:'),
                'count' => $countOrder,
                'count_xml' => (int)$xml->countorder,
            );
            if (file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/OrderHistory.xml') || file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/OrderHistory_1.xml') || file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/OrderHistory_' . $currentindex . '.xml'))
                $ok = false;
        }
        if (in_array('manufactures', $data_exports)) {
            $countManufacturer = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'ets_manufacturer_import where id_import_history="' . (int)$import['id_import_history'] .
                '"');
            $contents[] = array(
                'title' => $this->l('Manufacturers:'),
                'count' => $countManufacturer,
                'count_xml' => (int)$xml->countmanufacturer,
            );
            if (file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/Manufacturer.xml') || file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/Manufacturer1.xml') || file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/Manufacturer_' . $currentindex . '.xml'))
                $ok = false;
        }
        if (in_array('suppliers', $data_exports)) {
            $countSupplier = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'ets_supplier_import where id_import_history="' . (int)$import['id_import_history'] .
                '"');
            $contents[] = array(
                'title' => $this->l('Suppliers:'),
                'count' => $countSupplier,
                'count_xml' => (int)$xml->countsupplier,
            );
            if (file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/Supplier.xml') || file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/Supplier1.xml') || file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/Supplier_' . (int)$currentindex . '.xml'))
                $ok = false;
        }
        if (in_array('carriers', $data_exports)) {
            $countCarrier = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'ets_carrier_import where id_import_history="' . (int)$import['id_import_history'] .
                '"');
            $contents[] = array(
                'title' => $this->l('Carriers'),
                'count' => $countCarrier,
                'count_xml' => (int)$xml->countcarrier,
            );
            if (file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/Carrier.xml') || file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/Carrier1.xml') || file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/Carrier_' . $currentindex . '.xml'))
                $ok = false;
        }
        if (in_array('cart_rules', $data_exports)) {
            if ($this->pres_version > 1.4) {
                $countCartRule = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'ets_cart_rule_import where id_import_history="' . (int)$import['id_import_history'] . '"');
                $contents[] = array(
                    'title' => $this->l('Cart rules:'),
                    'count' => $countCartRule,
                    'count_xml' => (int)$xml->countcartrule,
                );
                if (file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/CartRule.xml') || file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/CartRule_1.xml') || file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/CartRule_' . $currentindex . '.xml'))
                    $ok = false;
            }
        }
        if (in_array('catelog_rules', $data_exports)) {
            if ($this->pres_version > 1.4) {
                $countSpecificPriceRule = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'ets_specific_price_rule_import where id_import_history="' . (int)
                    $import['id_import_history'] . '"');
                $contents[] = array(
                    'title' => $this->l('Catalog rules:'),
                    'count' => $countSpecificPriceRule,
                    'count_xml' => (int)$xml->countspecificpriceRule,
                );
                if (file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/SpecificPriceRule.xml') || file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/SpecificPriceRule_1.xml') || file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/SpecificPriceRule_' . $currentindex . '.xml'))
                    $ok = false;
            }
        }
        if (in_array('CMS_categories', $data_exports)) {
            $countCMSCategory = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'ets_cms_category_import where id_import_history="' . (int)$import['id_import_history'] .
                '"');
            $contents[] = array(
                'title' => $this->l('CMS categories:'),
                'count' => $countCMSCategory,
                'count_xml' => (int)$xml->countcmscategory,
            );
            if (file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/CMSCategory.xml') || file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/CMSCategory_1.xml') || file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/CMSCategory_' . $currentindex . '.xml'))
                $ok = false;
        }
        if (in_array('CMS', $data_exports)) {
            $countCMS = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'ets_cms_import where id_import_history="' . (int)$import['id_import_history'] . '"');
            $contents[] = array(
                'title' => $this->l('CMSs:'),
                'count' => $countCMS,
                'count_xml' => (int)$xml->countcms,
            );
            if (file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/CMS.xml') || file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/CMS_1.xml') || file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/CMS_' . $currentindex . '.xml'))
                $ok = false;
        }
        if (in_array('messages', $data_exports)) {
            $countMessage = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'ets_customer_thread_import where id_import_history="' . (int)$import['id_import_history'] .
                '"');
            $contents[] = array(
                'title' => $this->l('Contact form messages:'),
                'count' => $countMessage,
                'count_xml' => (int)$xml->countmessage,
            );
            if (file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/CustomerThread.xml') || file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/CustomerThread_1.xml') || file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/CustomerThread_' . (int)$currentindex . '.xml'))
                $ok = false;
        }
        if (in_array('vouchers', $data_exports)) {
            if ($this->pres_version == 1.4) {
                $countVoucher = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'ets_discount_import where id_import_history="' . (int)$import['id_import_history'] .
                    '"');
                $contents[] = array(
                    'title' => $this->l('Voucher:'),
                    'count' => $countVoucher,
                    'count_xml' => (int)$xml->countvoucher,
                );
            }
            if ((file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/Discount.xml') || file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/Discount_1.xml') || file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/Discount_' . (int)$currentindex . '.xml')))
                $ok = false;
        }
        $this->context->smarty->assign(array('contents' => $contents,));
        $content = $this->display(__file__, 'views/templates/hook/contents.tpl');
        Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'ets_import_history SET content="' . pSQL($content, true) . '" WHERE id_import_history=' . (int)$id_history);
        if ($ok && !file_exists(dirname(__file__) . '/xml/' . $import['file_name'] . '/errors.log')) {
            foreach ($this->tables as $table) {
                Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'ets_' . pSQL($table) . '_import` WHERE id_import_history="' . (int)$id_history . '"');
            }
            foreach (glob(dirname(__file__) . '/xml/' . $import['file_name'] . '/*.*') as $filename) {
                @unlink($filename);
            }
            @rmdir(dirname(__file__) . '/xml/' . $import['file_name']);
        }
        return $ok;
    }

    public function addPaymentMethod()
    {
        $sql = 'SELECT m.id_module FROM ' . _DB_PREFIX_ . 'module m 
        INNER JOIN ' . _DB_PREFIX_ . 'hook_module hm ON (m.id_module = hm.id_module)
        INNER JOIN ' . _DB_PREFIX_ . 'hook h ON (hm.id_hook=h.id_hook)
        WHERE m.active=1 AND (h.name="paymentOptions" OR h.name="displayPayment")
        ';
        $modules = Db::getInstance()->executeS($sql);
        if ($modules) {
            foreach ($modules as $module) {
                if ($id_module = $module['id_module']) ;
                {
                    $countries = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'country');
                    $groups = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'group');
                    $currencies = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'currency');
                    if ($this->pres_version != 1.4) {
                        $shops = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'shop');
                        if ($countries) {
                            foreach ($countries as $country) {
                                foreach ($shops as $shop) {
                                    if (!Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'module_country WHERE id_shop="' . (int)$shop['id_shop'] . '" AND id_country ="' . (int)$country['id_country'] . '" AND id_module=' . (int)$id_module)) {
                                        Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'module_country(id_country,id_module,id_shop) values("' . (int)$country['id_country'] . '","' . (int)$id_module . '","' . (int)$shop['id_shop'] . '")');
                                    }
                                }
                            }
                        }
                        if ($currencies) {
                            foreach ($currencies as $currency) {
                                foreach ($shops as $shop) {
                                    if (!Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'module_currency WHERE id_module="' . (int)$id_module . '" AND id_currency="' . (int)$currency['id_currency'] . '" AND id_shop="' . (int)$shop['id_shop'] . '"')) {
                                        Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'module_currency(id_module,id_currency,id_shop) values("' . (int)$id_module . '","' . (int)$currency['id_currency'] . '","' . (int)$shop['id_shop'] . '")');
                                    }
                                }
                            }
                        }
                        if ($groups) {
                            foreach ($groups as $group) {
                                foreach ($shops as $shop) {
                                    if (!Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'module_group where id_group="' . (int)$group['id_group'] . '" AND id_shop="' . (int)$shop['id_shop'] . '" AND id_module="' . (int)$id_module . '"')) {
                                        Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'module_group (id_module,id_group,id_shop) values("' . (int)$id_module . '","' . (int)$group['id_group'] . '","' . (int)$shop['id_shop'] . '")');
                                    }
                                }
                            }
                        }
                    } else {
                        if ($countries) {
                            foreach ($countries as $country) {
                                if (!Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'module_country WHERE id_country ="' . (int)$country['id_country'] . '" AND id_module=' . (int)$id_module)) {
                                    Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'module_country(id_country,id_module) values("' . (int)$country['id_country'] . '","' . (int)$id_module . '")');
                                }
                            }
                        }
                        if ($currencies) {
                            foreach ($currencies as $currency) {
                                if (!Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'module_currency WHERE id_module="' . (int)$id_module . '" AND id_currency="' . (int)$currency['id_currency'] . '"')) {
                                    Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'module_currency(id_module,id_currency) values("' . (int)$id_module . '","' . (int)$currency['id_currency'] . '")');
                                }
                            }
                        }
                        if ($groups) {
                            foreach ($groups as $group) {
                                if (!Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'module_group where id_group="' . (int)$group['id_group'] . '" AND id_module="' . (int)$id_module . '"')) {
                                    Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'module_group (id_module,id_group) values("' . (int)$id_module . '","' . (int)$group['id_group'] . '")');
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function zipForderXml($file_name)
    {
        if ($this->context->cookie->export_sucss)
            return true;
        $dir_forder = dirname(__file__) . '/cache/export/';
        $rootPath = realpath($dir_forder . $file_name);
        $zip = new ZipArchive();
        $zip->open($dir_forder . $file_name . '.zip', ZipArchive::CREATE | ZipArchive::
            OVERWRITE);
        // Create recursive directory iterator
        /**
         * @var SplFileInfo[] $files
         */
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootPath),
            RecursiveIteratorIterator::LEAVES_ONLY);
        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = Tools::substr($filePath, Tools::strlen($rootPath) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
        $zip->close();
    }

    public function deleteForderXml($file_name)
    {
        $this->context->cookie->export_sucss = 1;
        $this->context->cookie->write();
        $dir_forder = dirname(__file__) . '/cache/export/';
        foreach (glob($dir_forder . $file_name . '/*.*') as $filename) {
            @unlink($filename);
        }
        @rmdir($dir_forder . $file_name);
    }

    public function addCategoryFileXMl($dir, $multishop)
    {
        $roots = Db::getInstance()->executeS('SELECT c.id_category FROM ' . _DB_PREFIX_ . 'category c,' . _DB_PREFIX_ . 'category_shop cs  where c.id_category=cs.id_category AND c.id_parent=0 group by c.id_category');
        $xml_output = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml_output .= '<entity_profile>' . "\n";
        if ($roots) {
            foreach ($roots as $root) {
                $this->getXMlCategoriesTree($root['id_category'], $xml_output, $multishop);
            }
        }
        $xml_output .= '</entity_profile>';
        if (!file_exists($dir . '/Category.xml')) {
            Configuration::updateValue('ETS_TABLE_EXPORT', 'category');
            file_put_contents($dir . '/Category.xml', $xml_output);
        }
        return true;
    }

    public function addCategoryFileXMl14($dir)
    {
        $roots = Db::getInstance()->executeS('SELECT c.id_category FROM ' . _DB_PREFIX_ . 'category c  WHERE c.id_parent=0 group by c.id_category');
        $xml_output = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml_output .= '<entity_profile>' . "\n";
        if ($roots) {
            foreach ($roots as $root) {
                $this->getXMlCategoriesTree($root['id_category'], $xml_output, true);
            }
        }
        $xml_output .= '</entity_profile>';
        if (!file_exists($dir . '/Category.xml')) {
            Configuration::updateValue('ETS_TABLE_EXPORT', 'category');
            file_put_contents($dir . '/Category.xml', $xml_output);
        }
        return true;
    }

    public function getXMlCategoriesTree($id_root, &$xml_output, $multishop)
    {
        $sql = "SELECT c.*
        FROM " . _DB_PREFIX_ . "category c " . (version_compare(_PS_VERSION_, '1.5', '>=') ? "INNER JOIN " . _DB_PREFIX_ . "category_shop cs on (c.id_category =cs.id_category " . (!$multishop ?
                    ' AND cs.id_shop="' . (int)$this->context->shop->id . '"' : '') . ")" : "") . "
        WHERE c.id_category = " . (int)$id_root .
            " GROUP BY c.id_category";
        $ssl = (Configuration::get('PS_SSL_ENABLED'));
        if ($this->pres_version == 1.4)
            $base = $ssl ? 'https://' . Configuration::get('PS_SHOP_DOMAIN_SSL') : 'http://' . Configuration::get('PS_SHOP_DOMAIN');
        else
            $base = $ssl ? 'https://' . $this->context->shop->domain_ssl : 'http://' . $this->context->shop->domain;


        if ($category = Db::getInstance()->getRow($sql)) {
            $xml_output .= '<category>';
            if ($this->pres_version == 1.4)
                $type_category_default = '';
            else
                $type_category_default = $this->pres_version == 1.7 ? ImageType::getFormattedName('category') : ImageType::getFormatedName('category');
            if ($this->pres_version == 1.4)
                $type_medium_default = '';
            else
                $type_medium_default = $this->pres_version == 1.7 ? ImageType::getFormattedName('medium') : ImageType::getFormatedName('medium');
            if (file_exists(_PS_CAT_IMG_DIR_ . (int)$id_root . '.jpg')) {
                $url = $base . __PS_BASE_URI__ . 'img/c/' . $id_root . '.jpg';
                $xml_output .= '<link_image><![CDATA[' . $url . ']]></link_image>' . "\n";
            } elseif (file_exists(_PS_CAT_IMG_DIR_ . (int)$id_root . '-' . $type_category_default . '.jpg')) {
                $url = $base . __PS_BASE_URI__ . 'img/c/' . $id_root . '-' . $type_category_default . '.jpg';
                $xml_output .= '<link_image><![CDATA[' . $url . ']]></link_image>' . "\n";
            } elseif (file_exists(_PS_CAT_IMG_DIR_ . (int)$id_root . '-' . $type_medium_default . '.jpg')) {
                $url = $base . __PS_BASE_URI__ . 'img/c/' . $id_root . '-' . $type_medium_default . '.jpg';
                $xml_output .= '<link_image><![CDATA[' . $url . ']]></link_image>' . "\n";
            }
            if (file_exists(_PS_CAT_IMG_DIR_ . (int)$id_root . '_thumb.jpg')) {
                $url = $base . __PS_BASE_URI__ . 'img/c/' . $id_root . '_thumb.jpg';
                $xml_output .= '<link_thumb><![CDATA[' . $url . ']]></link_thumb>' . "\n";
            }
            foreach ($category as $key => $value) {
                $xml_output .= '<' . $key . '><![CDATA[' . $value . ']]></' . $key . '>' . "\n";
            }
            $datalanguages = Db::getInstance()->executeS('SELECT cl.*,l.iso_code FROM ' . _DB_PREFIX_ . 'category_lang cl,' . _DB_PREFIX_ .
                'lang l  WHERE cl.id_lang=l.id_lang AND cl.id_category=' . (int)$id_root . (!$multishop ?
                    ' AND cl.id_shop="' . (int)Context::getContext()->shop->id . '"' : ''));
            if ($datalanguages && $datalanguages) {
                foreach ($datalanguages as $datalanguage) {
                    $xml_output .= '<datalanguage iso_code="' . $datalanguage['iso_code'] . '"' . ($datalanguage['id_lang'] ==
                        Configuration::get('PS_LANG_DEFAULT') ? ' default="1"' : '') . ' >' . "\n";
                    if ($datalanguage) {
                        foreach ($datalanguage as $key => $value) {
                            if ($key != 'iso_code') {
                                $xml_output .= '<' . $key . '><![CDATA[' . $value . ']]></' . $key . '>' . "\n";
                            }
                        }
                    }
                    $xml_output .= '</datalanguage>' . "\n";
                }
            }
            if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                $datashops = Db::getInstance()->executeS('SELECT s.id_shop,cs.* FROM ' . _DB_PREFIX_ . 'shop s ,' . _DB_PREFIX_ .
                    'category_shop cs WHERE s.id_shop=cs.id_shop AND cs.id_category=' . (int)$id_root .
                    (!$multishop ? ' AND cs.id_shop="' . (int)Context::getContext()->shop->id . '"' :
                        ''));
                if ($datashops && $datashops) {
                    foreach ($datashops as $shop) {
                        $xml_output .= '<datashop id_shop="' . $shop['id_shop'] . '"';
                        $xml_output .= '></datashop>' . "\n";
                    }
                }
            }
            $xml_output .= '</category>';
            $children = $this->getChildrenCategories2($id_root, $multishop);
            if ($children) {
                foreach ($children as $child) {
                    $this->getXMlCategoriesTree($child['id_category'], $xml_output, $multishop);
                }
            }
        }
    }

    public function getChildrenCategories2($id_root, $multishop)
    {
        $sql = "SELECT c.id_category
                FROM " . _DB_PREFIX_ . "category c
                " . (version_compare(_PS_VERSION_, '1.5', '>=') ? "INNER JOIN " . _DB_PREFIX_ . "category_shop cs on (c.id_category =cs.id_category " . (!$multishop ?
                    ' AND cs.id_shop="' . (int)$this->context->shop->id . '"' : '') . ")" : "") . "
                WHERE c.id_parent = " . (int)$id_root .
            " GROUP BY c.id_category";
        return Db::getInstance()->executeS($sql);
    }

    public function checkTableExported($dir, $table)
    {
        if (file_exists($dir . '/table_exported.txt')) {
            $imported = Tools::file_get_contents($dir . '/table_exported.txt');
            if (in_array($table, explode(',', $imported)))
                return false;
        }
        return true;
    }

    public function insertTableExported($dir, $table)
    {
        if (file_exists($dir . '/table_exported.txt')) {
            $imported = Tools::file_get_contents($dir . '/table_exported.txt');
            $imported .= ',' . $table;
            file_put_contents($dir . '/table_exported.txt', $imported);

        } else
            file_put_contents($dir . '/table_exported.txt', $table);
    }
}

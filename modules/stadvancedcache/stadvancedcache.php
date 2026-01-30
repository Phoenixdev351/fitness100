<?php
/**
* 2007-2018 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2018 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}
require_once dirname(__FILE__).'/classes/StAdvancedCacheClass.php';
class StAdvancedCache extends Module
{
    public $_html = '';
    protected static $access_rights = 0775;
    protected $secure_key;
    public $fields_form;
    public $fields_value;
    public $cache_row = array();
    public $validation_errors = array();
    public $_prefix_st = 'ST_ADVCACHE_';
    private $_st_is_16;
    public $default_dyn_modules;
    public $debug_html = '';
    public function __construct()
    {
        $this->name          = 'stadvancedcache';
        $this->tab           = 'front_office_features';
        $this->version       = '1.0.4';
        $this->author        = 'ST-themes';
        $this->need_instance = 0;
        $this->bootstrap     = true;
        
        $this->secure_key = Tools::encrypt($this->name);
        parent::__construct();
        
        $this->displayName = $this->l('Advanced page cache');
        $this->description = $this->l('Reduce server side page loading time by caching pages.');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->_st_is_16   = Tools::version_compare(_PS_VERSION_, '1.7');
        $this->controllers = array('cron');

        $this->default_dyn_modules = array(
            'blockuserinfo' => array('*'),
            'blockviewed' => array('*'),
            'blockcart' => array('*'),
            'blockmyaccount' => array('*'),
            'blockwishlist' => array('*'),
            'ps_shoppingcart' => array('*'),
            'ps_customersignin' => array('*'),
            // ST-Themes modules
            'blockcart_mod' => array('*'),
            'blockviewed_mod' => array('*'),
            'blockuserinfo_mod' => array('*'),
            'stcustomersignin' => array('*'),
            'stshoppingcart' => array('*'),
            'stwishlist' => array('*'),
            'stviewedproducts' => array('*'),
            'stcountdown' => array('*'),
            'strightbarcart' => array('*'),
            'stlovedproduct' => array('*'),
            'stsidebar' => array('*'),
            'stthemeeditor' => array('displayHeader'),
            'stblogeditor' => array('displayHeader'),
        );
        if($this->_st_is_16){
            $this->default_dyn_modules = array_merge($this->default_dyn_modules, array(
                'stcompare' => array('displaySideBar','displaySideBarRight'),
            ));
        }else{
            $this->default_dyn_modules = array_merge($this->default_dyn_modules, array(
                'stcompare' => array('*'),
            ));
        }
    }
    public function install()
    {
        if (!parent::install()
            || !$this->installDB()
            || !$this->registerHook('actionProductUpdate')
            || !$this->registerHook('actionProductDelete')
            || !$this->registerHook('actionCategoryUpdate')
            || !$this->registerHook('actionCategoryDelete')
            || !$this->registerHook('actionPaymentConfirmation')
            || !$this->registerHook('actionObjectCmsUpdateAfter')
            || !$this->registerHook('actionObjectCmsDeleteAfter')
            // For warehause theme. it requries to add the stadvancedcache to the dynamic displayWidgetBlock hook.
            || !$this->registerHook('displayWidgetBlock')
            || !$this->registerHook('litespeedEsiBegin')
            || !$this->registerHook('litespeedEsiEnd')
        ) {
            return false;
        }
        $result = true;
        foreach ($this->getFormFieldsDefault() as $k => $v) {
            $result &= Configuration::updateValue($this->_prefix_st.Tools::strtoupper($k), $v);
        }
        $this->installDynModules();
        // Return vaule must be a bool type.
        return (bool)$result;
    }
    public function installDb()
    {
        $return = true;
        $return &= Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'st_advanced_cache` (
            `id_st_advanced_cache` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `id_sign` varchar(32) NOT NULL,
            `url` varchar(1024) NOT NULL,
            `file_name` varchar(256) NOT NULL,
            `id_shop` int(11) unsigned DEFAULT NULL,
            `id_language` int(11) unsigned DEFAULT NULL,
            `id_currency` int(11) unsigned DEFAULT NULL,
            `id_country` int(11) unsigned DEFAULT NULL,
            `is_mobile` int(1) unsigned DEFAULT 0,
            `controller` varchar(30) NOT NULL,
            `id_object` int(11) unsigned DEFAULT 0,
            `is_module` int(1) unsigned DEFAULT 0,
            `module_name` varchar(64) DEFAULT NULL,
            `customer_groups` varchar(255) DEFAULT NULL,
            `cache` mediumtext default NULL,
            `cache_size` int(10) unsigned DEFAULT 0,
            `hits` int(10) unsigned DEFAULT 0,
            `misses` int(10) unsigned DEFAULT 0,
            `hit_time` float(10,5) unsigned DEFAULT NULL,
            `miss_time` float(10,5) unsigned DEFAULT NULL,
            `last_updated` int(11) unsigned DEFAULT NULL,
            PRIMARY KEY (`id_st_advanced_cache`),
            UNIQUE KEY `id_sign_cache` (`id_sign`,`id_currency`,`id_language`,`id_country`,`id_shop`,`is_mobile`,`is_module`,`customer_groups`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;');
        
        return $return;
    }
    public function installController()
    {
        return $this->installControllers();
    }
    public function installDynModules()
    {
        $dyn_hooks = array();
        $module_hooks = Hook::getHookModuleList();
        foreach ($module_hooks as $module_hook) {
           foreach ($module_hook as $m) {
                if (array_key_exists($m['name'], $this->default_dyn_modules) && Module::isEnabled($m['name'])) {
                    $hook_name = Hook::getNameById($m['id_hook']);
                    if ($hook_name != 'displayHeader' && $hook_name != 'Header' && $hook_name != 'displayBeforeBodyClosingTag' && strpos($hook_name, 'display') !== false) {
                        $dyn_hooks[$m['name']][] = $hook_name;
                    }
                }
            } 
        }
        foreach($dyn_hooks as $m_name => &$hooks) {
            $default = $this->default_dyn_modules[$m_name];
            if (count($default) == 1) {
                if ($default[0] == '*') {
                    continue;
                } else {
                    $hooks = $default;
                }
            } else {
                $hooks = array_intersect($default, $hooks);
            }
        }
        if ($dyn_hooks) {
            Configuration::updateValue($this->_prefix_st.'DYN_HOOKS', serialize($dyn_hooks));
        }
        return true;
    }
    public function uninstall()
    {
        if (!parent::uninstall()
            || !$this->uninstallDB()
        ) {
            return false;
        }
        return true;
    }
    private function uninstallDb()
    {
        return Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'st_advanced_cache`');
    }
    public function getContent()
    {
        $this->_postProcess();
        $this->context->controller->addCSS($this->_path.'views/css/admin.css');
        $this->context->controller->addJS($this->_path.'views/js/admin.js');
        $content = '';
        $tabs = array();
        $tabs = array(
            array('id'  => '0', 'name' => $this->l('Settings')),
            array('id'  => '3', 'name' => $this->l('Dynamic content')),
            array('id'  => '5', 'name' => $this->l('Statistic')),
            array('id'  => '10', 'name' => $this->l('About ST-themes')),
        );
        $content .= $this->renderConfigForm();
        $this->smarty->assign(array(
            'current_index' => AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
            'bo_tabs' => $tabs,
            'bo_tab_content' => $content,
        ));
        return $this->_html.$this->display(__FILE__, 'bo_tab_layout.tpl');
        
    }
    private function _postProcess()
    {
        if (Tools::getIsset('save'.$this->name)) {
            $old_cache_type =Configuration::get($this->_prefix_st . 'CACHE_TYPE');
            $this->initConfigFormFields();
            foreach ($this->fields_form as $form) {
                foreach ($form['form']['input'] as $field) {
                    if (isset($field['validation'])) {
                        $errors = array();
                        $value = Tools::getValue($field['name']);
                        if (isset($field['required']) && $field['required'] && $value==false && (string)$value != '0') {
                            $errors[] = $this->l(vsprintf('Field "%s" is required.', $field['label']));
                        } elseif ($value) {
                            $field_validation = $field['validation'];
                            if ($field_validation == 'isColor' && !self::isColor($value)) {
                                $errors[] = $this->l(vsprintf('Field "%s" is invalid.', $field['label']));
                            } elseif (!Validate::$field_validation($value)) {
                                $errors[] = $this->l(vsprintf('Field "%s" is invalid.', $field['label']));
                            }
                        }
                        // Set default value
                        if ($value === false && isset($field['default_value'])) {
                            $value = $field['default_value'];
                        }
                        
                        if (count($errors)) {
                            $this->validation_errors = array_merge($this->validation_errors, $errors);
                        } elseif ($value==false) {
                            switch ($field['validation']) {
                                case 'isUnsignedId':
                                case 'isUnsignedInt':
                                case 'isInt':
                                case 'isBool':
                                    $value = 0;
                                    break;
                                default:
                                    $value = '';
                                    break;
                            }
                            Configuration::updateValue($this->_prefix_st.Tools::strtoupper($field['name']), $value);
                        } else {
                            Configuration::updateValue($this->_prefix_st.Tools::strtoupper($field['name']), $value);
                        }
                    }
                }
            }
            // Save hooks.
            if ($hooks = Tools::getValue('dyn_hooks')) {
                $dyn_hooks = array();
                foreach($hooks as $hook) {
                    list($mod, $hk) = explode(':', $hook);
                    if ($mod & $hk) {
                        $dyn_hooks[$mod][] = $hk;
                    }
                }
                Configuration::updateValue($this->_prefix_st.'DYN_HOOKS', serialize($dyn_hooks));
            }
            // If database cache enabled, disable the cache compress.
            if (Configuration::get($this->_prefix_st . 'CACHE_TYPE')) {
                Configuration::updateValue($this->_prefix_st.'COMPRESS_CACHE', 0);
            }
            // Switch cache type? clear cache.
            if (Configuration::get($this->_prefix_st . 'CACHE_TYPE') != $old_cache_type) {
                $this->getCacheInstance()->flush();
            }
            
            $this->_clearCache('*');
            if (count($this->validation_errors)) {
                $this->_html .= $this->displayError(implode('<br/>', $this->validation_errors));
            } else {
                Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&'.$this->name.'&conf=6&token='.Tools::getAdminTokenLite('AdminModules'));
            }
        }
        if (Tools::getValue('clear_cache')) {
            $this->clearAllCache();
            Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&conf=4&token='.Tools::getAdminTokenLite('AdminModules')); 
        }
    }
    protected function initConfigFormFields()
    {
        $fields = $this->getFormFields();
        $this->fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('General'),
                'icon' => 'icon-cogs'
            ),
            'description' => '<a href="https://www.sunnytoo.com/product/advanced-page-cache-module-for-prestashop" target="_blank">'.$this->l('Online docuemntaion').'</a> '.$this->l('That is the module\'s detail page, you may find information you need there.'),
            'input' => $fields['general'],
            'submit' => array(
                'title' => $this->l('Save'),
            )
        );
        
        $this->fields_form[3]['form'] = array(
            'legend' => array(
                'title' => $this->l('Dynamic content'),
                'icon' => 'icon-cogs'
            ),
            'input' => $fields['modules'],
            'submit' => array(
                'title' => $this->l('Save')
            ),
        );

        $this->fields_form[5]['form'] = array(
            'legend' => array(
                'title' => $this->l('Statistic'),
                'icon' => 'icon-cogs'
            ),
            'description' => $this->l('The same url may show for several times on this list, because they are for different devices or different user groups.'),
            'input' => $fields['stats'],
            'submit' => array(
                'title' => $this->l('Save')
            ),
        );
        $this->fields_form[10]['form'] = array(
            'legend' => array(
                'title' => $this->l('About ST-THEMES'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'html',
                    'id' => '',
                    'label' => '',
                    'name' => 'This module was created by <a href="https://www.sunnytoo.com" target="_blank">ST-THEMES</a>. <br/>Check more <a href="https://www.sunnytoo.com/blogs?term=743&orderby=date&order=desc" target="_blank">free modules</a>, <a href="https://www.sunnytoo.com/product-category/prestashop-modules" target="_blank">advanced paid modules</a> and <a href="https://www.sunnytoo.com/product-category/prestashop-themes" target="_blank">themes(transformer theme and panda  theme)</a> created by <a href="https://www.sunnytoo.com" target="_blank">ST-THEMES</a>.',
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save')
            ),
        );
    }
    protected function renderConfigForm()
    {
        $this->initConfigFormFields();
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->table =  $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'save'.$this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        return $helper->generateForm($this->fields_form);
    }
    public function getFormFields()
    {
        require_once(dirname(__FILE__).'/formFields.php');
        return getFormFields($this);
    }
    public function getFormFieldsDefault()
    {
        $default = array();
        foreach ($this->getFormFields() as $key => $value) {
            
            foreach ($value as $k => $v) {
                if (!$k || !is_array($v)) {
                    continue;
                }
                if ($k == 'html') {
                    continue;
                }
                $default[$k] = isset($v['default_value']) ? $v['default_value'] : '';
            }
        }
        return $default;
    }
    private function getConfigFieldsValues()
    {
        $fields_value = array();
        foreach ($this->getFormFieldsDefault() as $k => $v) {
            $fields_value[$k] = Configuration::get($this->_prefix_st.Tools::strtoupper($k));
        }
        return $fields_value;
    }
    public function getDynModulesHTML()
    {
        Cache::clean('hook_module_list');
        $module_hooks = Hook::getHookModuleList();
        $html = '';
        $modules = array();
        $dyn_hooks = unserialize(Configuration::get($this->_prefix_st.'DYN_HOOKS'));
        $dyn_hooks || $dyn_hooks = array();
        foreach ($module_hooks as $module_hook) {
           foreach ($module_hook as $m) {
                $hook_name = Hook::getNameById($m['id_hook']);
                $hook = strtolower($hook_name);
                if (strpos($hook, 'action') !== false || strpos($hook, 'admin') !== false || strpos($hook, 'backoffice') !== false || strpos($hook, 'dashboard') !== false || in_array($hook_name, array('moduleRoutes'))) {
                    continue;
                }
                if (in_array($hook_name, array('Header', 'Top'))) {
                    $hook_name = 'display'.$hook_name;
                }
                $m['m_name'] = Module::getModuleName($m['name']);
                $m['h_name'] = $hook_name;
                $m['checked'] = false;
                if (key_exists($m['name'], $dyn_hooks) && in_array($hook_name, $dyn_hooks[$m['name']])) {
                    $m['checked'] = true;
                }
                $modules[$m['name']][] = $m;
            } 
        }
        $html = '<div class="warn alert alert-info">'.sprintf($this->l('Module content from checked hooks will not be cached. please %sCLEAR CACHE%s after modifications.'), '<a href="'.AdminController::$currentIndex.'&configure='.$this->name.'&clear_cache=1&token='.Tools::getAdminTokenLite('AdminModules').'">','</a>').'</div>';
        $html .= '<table class="table '.$this->name.' tab-dynhook">';
        $html .= '<tr><th width="20%">'.$this->l('Module name').'</th><th>'.$this->l('Hooks').'</th></tr>';
        foreach($modules as $module_name => $hooks) {
            if (!count($hooks)) {
                continue;
            }
            $html .= '<tr><td class="module-name"><img src="../modules/'.$module_name.'/logo.png" width="18" />'.$hooks[0]['m_name'].'</td><td class="hook-name">';
            foreach($hooks as $hook) {
                $html .= '<label><input type="checkbox" name="dyn_hooks[]" value="'.$module_name.':'.$hook['h_name'].'"'.($hook['checked']?' checked="checked"':'').' />'.$hook['h_name'].'</label>';
            }
            $html .= '</td></tr>';
        }
        $html .= '</table>';
        return $html;
    }
    public function getStatsHtml()
    {
        $data = StAdvancedCacheClass::getAll($this->context->shop->id);
        $html = '';
        $html = '<table class="table '.$this->name.' tab-stats">';
        $html .= '<tr><th width="40%">'.$this->l('URL').'</th>
            <th width="12%">'.$this->l('Hits').'</th>
            <th width="18%">'.$this->l('Hit time (s)').'</th>
            <th width="12%">'.$this->l('Misses').'</th>
            <th width="18%">'.$this->l('Miss time (s)').'</th>
            </tr>';
        foreach($data as $row) {
            $html .= '<tr><td>'.$row['url'].'</td>
                <td>'.$row['hits'].'</td>
                <td>'.($row['hit_time']).'</td>
                <td>'.$row['misses'].'</td>
                <td>'.($row['miss_time']).'</td>
                </tr>';
        }
        $html .= '</table>';
        return $html;
    }
    public function isMaintenance()
    {
        if (!(int)Configuration::get('PS_SHOP_ENABLE')) {
            if (!in_array(Tools::getRemoteAddr(), explode(',', Configuration::get('PS_MAINTENANCE_IP')))) {
                return true;
            }
        }
        return false;
    }
    public function isNotCode200() {
        if (function_exists('http_response_code') && !defined('HHVM_VERSION')) {
            $code = http_response_code();
            if (!empty($code)) {
                if (http_response_code() !== 200) {
                    return true;
                }
            }
        }
        return false;
    }
    public function isSSLRedirected() {
        return (Configuration::get('PS_SSL_ENABLED') && $_SERVER['REQUEST_METHOD'] != 'POST' && Configuration::get('PS_SSL_ENABLED_EVERYWHERE') && !Tools::usingSecureMode());
    }
    public function isLogged()
    {
        return $this->context->customer ? $this->context->customer->isLogged() && Configuration::get($this->_prefix_st.'SKIP_LOGGED') : false;
    }
    public function isMobile()
    {
        return Configuration::get($this->_prefix_st.'CACHE_MOBILE') && ($this->context->isMobile() || $this->context->isTablet()) ? 1 : 0;
    }
    public function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && Tools::strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' || $this->context->controller->ajax;
    }
    public function isRestrictedCountry()
    {
        return Configuration::get($this->_prefix_st.'CACHE_RESTRICTED_COUNTRY') && $this->context->controller->getRestrictedCountry();
    }
    public function canCacheByModule()
    {
        if (_PS_MODE_DEV_ && !Configuration::get($this->_prefix_st.'CACHE_DEBUG')) {
            return false;
        }
        if (Configuration::get('PS_TOKEN_ENABLE') && !Configuration::get($this->_prefix_st.'CACHE_FRONT_TOKEN')) {
            return false;
        }
        if (Tools::getValue('fc') == 'module' && !Configuration::get($this->_prefix_st.'CACHE_MODULE')) {
            return false;
        }
        return true;
    }
    public function isCachedController()
    {
        $page_name = Dispatcher::getInstance()->getController();
        if (in_array($page_name, array('order','cart','orderopc','authentication','contact','orderconfirmation'))) {
            return false;
        }
        $controllers = explode(',', Configuration::get($this->_prefix_st.'CONTROLLERS'));
        if (in_array($page_name, $controllers)) {
            return true;
        }
        // Check modules controller.
        foreach($controllers as $controller) {
            if (strpos($controller, ':') !== false) {
                list($m, $c) = explode(':', $controller);
                if ($page_name == $c && Tools::getValue('module') == $m) {
                    return true;
                }
            }
        }
        return false;
    }
    public function getCurrentURL()
    {
        $base_url = Tools::getCurrentUrlProtocolPrefix().$_SERVER['HTTP_HOST'];
        //ignored parameters
        list($uri, $query_str) = array_pad(explode('?', $_SERVER['REQUEST_URI']), 2, '');
        parse_str($query_str, $query);
        $ignore_params = Configuration::get('ST_ADVCACHE_IGNORES');
        $ignore_params = explode(',', $ignore_params);
        foreach ($ignore_params as $param) {
            $param = trim($param);
            unset($query[$param]);
        }
        $query = http_build_query($query);
        if ($query == '') {
            $url = $base_url.$uri;
        } else {
            $url = $base_url.$uri.'?'.$query;
        }
        return $url;
    }
    public function cacheAble()
    {
        if (Tools::isSubmit('live_edit') || Tools::isSubmit('no_cache') || defined('PS_ADMIN_DIR') || count($_POST) > 0 || Tools::getValue('SubmitCurrency')) {
            return false;
        }
        if (Tools::getValue('logout') || Tools::getValue('mylogout')) {
            return false;
        }
        if ($this->isAjax() || $this->isLogged() || !$this->isCachedController()) {
            return false;
        }
        if ($this->isMaintenance() || $this->isNotCode200() || $this->isSSLRedirected() || !$this->canCacheByModule() || $this->isRestrictedCountry()) {
            return false;
        }
        if ($this->isbot()) {
            return false;
        }
        return true;
    }
    public function getCacheInstance()
    {
        return StAdvancedCacheClass::getInstance($this);
    }
    public function getCache($controller)
    {
        // Clear cache from front office
        if (Tools::getValue('clear_cache') == 1) {
            $controller = Dispatcher::getInstance()->getController();
            $id_object = null;
            if (Tools::getValue('id_'.$controller)) {
                $id_object = Tools::getValue('id_'.$controller);
            }
            $this->refreshControllerCache($controller, $id_object);
            // Redirect current URL.
            Tools::redirect(str_replace(array('?clear_cache=1','&clear_cache=1'), '', $this->getCurrentURL()));
        }
        $cache_inst = $this->getCacheInstance();
        extract($this->getCacheParameters());

        if($this->cache_row = $cache_inst->getCacheRow($id_sign, $id_shop, $id_language, $id_currency, $id_country, $is_mobile)) {
            if (!$this->cache_row['file_name'] || $this->isCacheExpired($this->cache_row['last_updated']) || (Configuration::get($this->_prefix_st . 'CACHE_TYPE') && !$this->cache_row['cache'])) {
                return false;
            }
            // Use FS cache or DB cache?
            // Cache type: 0=FS 1=DB
            if (Configuration::get($this->_prefix_st . 'CACHE_TYPE')) {
                $content = $this->cache_row['cache'];
            } else {
                $content = $cache_inst->getFsCache($this->cache_row['file_name']);
            }
            if ($content) {
                if (Configuration::get($this->_prefix_st . 'COMPRESS_CACHE') || !strstr($content, 'body')) {
                    $content = gzinflate($content);
                }
                return $content;
            }
        } else {
            return false;
        }
    }
    public function execDynamic(&$content, $controller)
    {
        // Execute dynamic hooks
        $this->execDynHook($content);
        $this->replaceToken($content);
        // Update hits.
        $now = microtime(true);
        $cache_time = round($now - $controller->start_time, 3);
        $this->getCacheInstance()->updateHits($this->cache_row['id_st_advanced_cache'], $cache_time);
        $this->displayDebug($content);
    }
    public function execDynHook(&$content)
    {
        $dyn_hooks = unserialize(Configuration::get($this->_prefix_st.'DYN_HOOKS'));
        $dyn_hooks || $dyn_hooks = array();

        // Master hooks.
        $hook_master_array =array();
        if($hook_master = Configuration::get($this->_prefix_st.'HOOK_MASTER')) {
            $hook_master = trim(trim($hook_master), ',');
            foreach(explode(',', $hook_master) as $item) {
                list($mod, $hook) = explode(':', $item);
                if ($mod && isset($hook) && $hook) {
                    $hook_master_array[$mod][] = $hook;
                }
            }    
        }
        $dyn_hooks =array_merge($hook_master_array, $dyn_hooks);

        if ($dyn_hooks) {
            $debug = Configuration::get($this->_prefix_st.'SHOW_DEBUG');
            foreach ($dyn_hooks as $module_name => $hooks) {
                if (!Module::isEnabled($module_name)) {
                    continue;
                }
                foreach ($hooks as $hook_name) {
                    $pattern = "/<!--stadvcache:$module_name:$hook_name\[(.*?)\]-->(.*?)<!--stadvcache:$module_name:$hook_name-->/s";
                    $matchs = array();
                    if (strpos($hook_master, $module_name.':'.$hook_name) === false && !preg_match_all($pattern, $content, $matchs)) {
                        if ($debug) {
                            $this->debug_html .= '<div class="st-cache-item"><span class="st-cache-item-k">'.$this->l('Not execed hook').'</span><span class="st-cache-item-v">'.$module_name.':'.$hook_name.' ['.(strpos($content, $module_name.':'.$hook_name) !== false ? 'Sig' : 'No-Sig').']'.'</span></div>';
                        }
                        continue;
                    }

                    if (!isset($matchs[1]) || !is_array($matchs[1])) {
                        continue;
                    }

                    foreach($matchs[1] as $str) {
                        $hook_args = array();
                        // Parse arguments.
                        if ($str) {
                            foreach(explode('*', $str) as $val) {
                                list($p_k, $p_v) = explode('=', $val);
                                if ($p_k && isset($p_v)) {
                                    if ($p_k == 'ip_o') {
                                        $hook_args['product'] = new Product((int)$p_v);
                                    } elseif ($p_k == 'ip') {
                                        $product = (array)new Product((int)$p_v);
                                        $product['id_product'] = (int)$p_v;
                                        $product['quantity'] = Product::getQuantity((int)$p_v, 0 , isset($product['cache_is_pack']) ? $product['cache_is_pack'] : null, $this->context->cart);
                                        $product['quantity_all_versions'] = $product['quantity'];
                                        $hook_args['product'] = $product;
                                    } elseif ($p_k == 'ic_o') {
                                        $hook_args['category'] = new Category((int)$p_v);
                                    } elseif ($p_k == 'ic') {
                                        $hook_args['category'] = (array)new Category((int)$p_v);
                                        $hook_args['category']['id_category'] = (int)$p_v;
                                    } else {
                                        $hook_args[$p_k] = urldecode($p_v);
                                    }
                                }
                            }
                        }

                        $id_module = Module::getModuleIdByName($module_name);
                        $hook_content = Hook::exec($hook_name, $hook_args, $id_module, false, true, false, null);
                        $pattern = "#<!--stadvcache:$module_name:$hook_name\[".preg_quote($str)."\]-->(.*?)<!--stadvcache:$module_name:$hook_name-->#s";
                        if (_PS_MODE_DEV_ || Configuration::get($this->_prefix_st.'SHOW_DEBUG')) {
                            $hook_content = preg_replace('/\$(\d)/', '\\\$$1', $hook_content);
                        } else {
                            $hook_content = preg_replace(array("/<!--stadvcache:$module_name:$hook_name\[(.*?)\]-->/s","/<!--stadvcache:$module_name:$hook_name-->/s",'/\$(\d)/'), array('', '', '\\\$$1'), $hook_content);
                        }
                        $count = 0;
                        
                        $p_content = preg_replace($pattern, $hook_content, $content, 1, $count);
                        if (preg_last_error() === PREG_NO_ERROR && $count > 0) {
                            $content = $p_content;
                            if ($debug) {
                                $this->debug_html .= '<div class="st-cache-item"><span class="st-cache-item-k">'.$this->l('Execed hook').'</span><span class="st-cache-item-v">'.$module_name.':'.$hook_name.'</span></div>';
                            }
                        }
                    }
                }
            }
        }
    }
    public function replaceToken(&$content)
    {
        if (Configuration::get('PS_TOKEN_ENABLE')) {
            $new_token = Tools::getToken(false);
            //HTML Tokens
            $content = preg_replace('/name\s*=\s*"token" value\s*=\s*"[a-f0-9]{32}"/', 'name="token" value="'.$new_token.'"', $content);
            $content = preg_replace('/token=[a-f0-9]{32}/', 'token='.$new_token, $content);
            //JS Token
            $content = preg_replace('/"static_token"\s*:\s*"[a-f0-9]{32}"/', '"static_token":"'.$new_token.'"', $content);
            $content = preg_replace('/"token"\s*:\s*"[a-f0-9]{32}"/', '"token":"'.$new_token.'"', $content);
        }
    }
    public function displayDebug(&$content)
    {
        if (Configuration::get($this->_prefix_st.'SHOW_DEBUG')) {
            $cache_inst = $this->getCacheInstance();
            extract($this->getCacheParameters());
            $cache_row = $cache_inst->getCacheRow($id_sign, $id_shop, $id_language, $id_currency, $id_country, $is_mobile);
            $url = $this->getCurrentURL();
            $html = '<div id="st-cache-wrap"><div id="st-cache-heading">'.$this->l('Cache debug info').' [<a href="'.(strpos($url, '?') !== false ? $url.'&clear_cache=1' : $url.'?clear_cache=1').'">'.$this->l(' Clear cache ').'</a>]</div>
                <div class="st-cache-item"><span class="st-cache-item-k">'.$this->l('Cache served').'</span><span class="st-cache-item-v">'.($this->cacheAble() ? $this->l('Yes') : $this->l('No')).'</td></tr>
                <div class="st-cache-item"><span class="st-cache-item-k">'.$this->l('Cache type').'</span><span class="st-cache-item-v">'.(Configuration::get($this->_prefix_st.'CACHE_TYPE') ? $this->l('Database') : $this->l('File system')).'</span></div>
                <div class="st-cache-item"><span class="st-cache-item-k">'.$this->l('Cache duration').'</span><span class="st-cache-item-v">'.Configuration::get($this->_prefix_st.'TIMEOUT').'(mins)</span></div>
                <div class="st-cache-item"><span class="st-cache-item-k">'.$this->l('Controller').'</span><span class="st-cache-item-v">'.Dispatcher::getInstance()->getController().'</span></div>
                <div class="st-cache-item"><span class="st-cache-item-k">'.$this->l('Country').'</span><span class="st-cache-item-v">'.(is_array($this->context->country->name)?$this->context->country->name[$this->context->language->id]:$this->context->country->name).'</span></div>
                <div class="st-cache-item"><span class="st-cache-item-k">'.$this->l('Lanauge').'</span><span class="st-cache-item-v">'.$this->context->language->name.'</span></div>
                <div class="st-cache-item"><span class="st-cache-item-k">'.$this->l('Currency').'</span><span class="st-cache-item-v">'.$this->context->currency->iso_code.'</span></div>
                <div class="st-cache-item"><span class="st-cache-item-k">'.$this->l('Hits').' / '.$this->l('Misses').'</span><span class="st-cache-item-v">'.$cache_row['hits'].' / '.$cache_row['misses'].'</span></div>
                <div class="st-cache-item"><span class="st-cache-item-k">'.$this->l('Load time with cache').'</span><span class="st-cache-item-v">'.($cache_row['hit_time']*1000).'ms</span></div>
                <div class="st-cache-item"><span class="st-cache-item-k">'.$this->l('Load time without cache').'</span><span class="st-cache-item-v">'.($cache_row['miss_time']*1000).'ms</span></div></div>
                '.(Configuration::get('PS_HTML_THEME_COMPRESSION') ? '
                <div class="st-cache-item"><span class="st-cache-item-k" style="">'.$this->l('Minify HTML').'</span><span class="st-cache-item-v c_red">'.$this->l('Enabled').'</span></div> 
                    ' : '')
                .$this->debug_html.'
            <style>#st-cache-wrap{position:fixed;left:0;bottom:0;z-index:99999;background:#444;padding:10px;color:#F5F5F5;font-size:12px;font-family:Arial;text-align:left;height:360px;overflow-y:auto;}#st-cache-heading{font-weight:bold;}.st-cache-item-k{color:#aaa;min-width:120px;display:inline-block;margin-right:6px;}.c_red{color:#f00;}#st-cache-wrap a{color:#99CDD8;text-decoration: underline;}#st-cache-wrap a:hover{text-decoration: none;}</style>
            ';
            $content = str_replace('</body>', $html.'</body>', $content);
        }
    }
    public function setCache($content, $controller)
    {
        $cache_inst = $this->getCacheInstance();
        extract($this->getCacheParameters());

        if ($this->isPostData()) {
            $cache_inst->emptyDbFileName($id_sign, $id_shop, $id_language, $id_currency, $id_country);
        } else {
            $controller_name = Dispatcher::getInstance()->getController();
            if (Configuration::get($this->_prefix_st . 'COMPRESS_CACHE')) {
                $content = gzdeflate($content);
            }
            $cache = '';
            // FS or DB cache: 0=FS 1=DB
            // cache field can store $content if cache type is DB.
            if (Configuration::get($this->_prefix_st . 'CACHE_TYPE')) {
                $cache = $content;
            }
            $cache_size = Tools::strlen($content);
            $id_object = Tools::getValue('id_'.$controller_name, 0);
            $cache_time = round(microtime(true) - ($controller->start_time), 3);

            if ($cache || $cache_inst->setFsCache($id_sign, $content)) {
                return $cache_inst->updateDbCache(
                    $id_sign,
                    $url,
                    $cache,
                    $id_shop,
                    $id_language,
                    $id_currency,
                    $id_country,
                    $is_mobile,
                    $controller_name,
                    $id_object,
                    $cache_size,
                    $cache_time
                );
            }
        }
        return false;
    }
    public function getCacheParameters()
    {
        $currency = Tools::setCurrency($this->context->cookie);
        $url      = $this->getCurrentURL();
        return array(
            'url'            => $url,
            'id_sign'        => md5($url),
            'id_language'    => (int)$this->context->language->id,
            'id_country'     => (int)$this->context->country->id,
            'id_shop'        => (int)$this->context->shop->id,
            'id_currency'    => $currency->id,
            'is_mobile'      => $this->isMobile(),
        );
    }
    public function isBot()
    {
        // search engine bots/spiders
        // http://www.robotstxt.org/db.html
        // https://github.com/monperrus/crawler-user-agents/blob/master/crawler-user-agents.json
        if (Configuration::get($this->_prefix_st.'ROBOTS') && array_key_exists('HTTP_USER_AGENT', $_SERVER)
        && preg_match('/bot|crawl|spider|mediapartners|slurp|patrol/i', $_SERVER['HTTP_USER_AGENT'])) {
            return true;
        }
        return false;
    }
    public function isCacheExpired($last_updated)
    {
        return round(abs(time()- $last_updated) / 60, 2) > Configuration::get($this->_prefix_st.'TIMEOUT');
    }
    public function isPostData()
    {
        $controller = Dispatcher::getInstance()->getController();
        $POST = $_POST;
        if (array_key_exists('id_'.$controller, $POST)) {
            unset($POST['id_'.$controller]);
        }
        return count($POST) > 0;
    }
    public function hookActionObjectCmsUpdateAfter($params)
    {
        $cms = $params['object'];
        $id_cms = $cms->id;
        if ($id_cms) {
            $this->refreshControllerCache('cms', $id_cms);
        }
    }
    public function hookActionObjectCmsDeleteAfter($params)
    {
        $this->hookActionObjectCmsUpdateAfter($params);
    }
    public function hookActionProductUpdate($params)
    {
        $id_product = $params['id_product'];
        $this->refreshControllerCache('product', $id_product);
    }
    public function hookActionProductDelete($params)
    {
        $this->hookActionProductUpdate($params);
    }
    public function hookActionCategoryUpdate($params)
    {
        $category = $params['category'];
        $id_category = $category->id;
        $this->refreshControllerCache('category', $id_category);
    }
    public function hookActionCategoryDelete($params)
    {
        $this->hookActionCategoryUpdate($params);
    }
    public function hookActionPaymentConfirmation($params)
    {
        $id_order = $params['id_order'];
        $order = new Order((int) $id_order);
        foreach ($order->getProducts() as $product) {
            $this->refreshControllerCache('product', (int)$product['product_id']);
        }
    }
    public function refreshControllerCache($controller, $id_object = null)
    {
        $cache_inst = $this->getCacheInstance();
        $cache_inst->deleteCacheBycontroller($controller, $id_object);
    }
    public function clearAllCache()
    {
        $this->getCacheInstance()->flush();
    }
    public function hookLitespeedEsiBegin($params)
    {
        $mod_name = $params['m'];
        $field = $params['field'];
        $tpl = isset($params['tpl']) && $params['tpl'] ? $params['tpl'] : '';
        $hook = isset($params['hook']) && $params['hook'] ? $params['hook'] : '';
        return '<!--stadvcache:'.$this->name.':displayWidgetBlock[m='.$mod_name .'*field='.$field.'*tpl='.$tpl.'*hook='.$hook.']-->';
    }
    public function hookLitespeedEsiEnd($params)
    {
        return '<!--stadvcache:'.$this->name.':displayWidgetBlock-->';
    }
    public function hookDisplayWidgetBlock($params)
    {
        $mod_name = $params['m'];
        $field = $params['field'];
        $tpl = isset($params['tpl']) && $params['tpl'] ? $params['tpl'] : '';
        // If has a hook, use the cache dynamic hook to load it.
        $hook = isset($params['hook']) && $params['hook'] ? $params['hook'] : null;
        unset($params['m'], $params['field'], $params['tpl'], $params['hook']);
        if ($hook) {
            return;
        }
        $module = Module::getInstanceByName($mod_name);
        if ($field == 'widget') {
            return $module->renderWidget($hook, $params);
        }
        if ($field == 'widget_block') {
             $variables = $module->getWidgetVariables($hook, $params);
             foreach ($variables as $key => $value) {
                $this->context->smarty->assign($key, $value);
            }
            return $this->fetch(str_replace('%2F', '/', $tpl));
        }
    }
}

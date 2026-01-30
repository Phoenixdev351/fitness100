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
class Ets_ss_class_cache
{
    public $user_agent = '';
    public $context;
    protected static $instance;
    public function __construct()
    {
        $this->context = Context::getContext();
        if (Configuration::get('ETS_SPEED_CHECK_USER_AGENT')) {
            if(Context::getContext()->isMobile())
                $this->user_agent ='Mobile';
            elseif(Context::getContext()->isTablet())
                $this->user_agent ='Tablet';
            else
                $this->user_agent ='Desktop';
        }
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Ets_ss_class_cache();
        }
        return self::$instance;
    }

    public function getFileCacheByUrl()
    {
        if (!isset($_SERVER['SERVER_PORT']))
            return '';
        if ($_SERVER['SERVER_PORT'] != "80") {
            $url = $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
        } else
            $url = $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) {
            $url = 'https://' . $url;
        } else
            $url = 'http://' . $url;
        if (Tools::strpos($url, '#') !== FALSE) {
            $url = Tools::substr($url, 0, Tools::strpos($url, '#'));
        }
        $this->context = Context::getContext();
        $query_string = parse_url($url, PHP_URL_QUERY);
        $params = '&ets_currency=' . ((Tools::isSubmit('submitCurrency') || Tools::isSubmit('SubmitCurrency')) && ($id_currency = (int)Tools::getValue('id_currency')) ? $id_currency:  ($this->context->cookie->id_currency ? $this->context->cookie->id_currency : Configuration::get('PS_CURRENCY_DEFAULT')));
        $id_customer = (isset($this->context->customer->id)) ? (int)($this->context->customer->id) : 0;
        $id_group = null;
        if ($id_customer) {
            $id_group = Customer::getDefaultGroupId((int)$id_customer);
        }
        if (!$id_group) {
            $id_group = (int)Group::getCurrent()->id;
        } 
        $params .= '&ets_group='.(int)$id_group; 
        $id_country = $this->getIDCountry();
        $params .='&ets_country='.($id_country ? $id_country : (int)$this->context->country->id);
        if(isset($this->context->cookie->id_cart) && $this->context->cookie->id_cart)
            $params .='&hascart=1';
        $params .='&user_agent='.$this->user_agent;
        if ($query_string == '') {
            $query_string .= Tools::substr($params, 1);
        } else {
            $query_string .= $params;
        }
        $uri = http_build_url($url, array("query" => $query_string));
        return hash('sha256', _COOKIE_KEY_ . $uri);
    }
    public function getIDCountry()
    {
        $context = Context::getContext();
        if($context->cookie->id_cart && ($cart = new Cart($context->cookie->id_cart)) && Validate::isLoadedObject($cart) && ($id_address = $cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}) && ($address = new Address($id_address)) && Validate::isLoadedObject($address))
        {
            return $address->id_country;
        }
        if(isset($context->cart) && isset($context->cart->id) && ($id_address = $context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}) && ($address = new Address($id_address)) && Validate::isLoadedObject($address))
        {
            return $address->id_country;
        }
        elseif(isset($this->context->cookie->iso_code_country) && $this->context->cookie->iso_code_country && Validate::isLanguageIsoCode($this->context->cookie->iso_code_country) && ($idCountry = Country::getByIso(Tools::strtoupper($this->context->cookie->iso_code_country))))
        {
            return $idCountry;
        }
        elseif (Configuration::get('PS_GEOLOCATION_ENABLED')) {
            if (($idCountry = $this->geolocationManagement())) {
                return $idCountry;
            }
        } elseif (Configuration::get('PS_DETECT_COUNTRY')) {
            if (Configuration::get('PS_DETECT_COUNTRY') && isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])
                && preg_match('#(?<=-)\w\w|\w\w(?!-)#', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $array)
                && Validate::isLanguageIsoCode($array[0]) && ($idCountry = (int) Country::getByIso($array[0], true))) {
                return $idCountry;
            }
        }
        return Tools::getCountry();
    }
    protected function geolocationManagement()
    {
        if (!in_array(Tools::getRemoteAddr(), ['127.0.0.1', '::1'])) {
            /* Check if Maxmind Database exists */
            if (@filemtime(_PS_GEOIP_DIR_ . _PS_GEOIP_CITY_FILE_)) {
                if (!isset($this->context->cookie->iso_code_country) || (isset($this->context->cookie->iso_code_country) && !in_array(strtoupper($this->context->cookie->iso_code_country), explode(';', Configuration::get('PS_ALLOWED_COUNTRIES'))))) {
                    if (version_compare(_PS_VERSION_,'1.7','<') && !class_exists('\GeoIp2\Database\Reader')) {
                        include_once(_PS_GEOIP_DIR_.'geoipcity.inc');
                        $gi = geoip_open(realpath(_PS_GEOIP_DIR_._PS_GEOIP_CITY_FILE_), GEOIP_STANDARD);
                        $record = geoip_record_by_addr($gi, Tools::getRemoteAddr());
                        if (is_object($record) && isset($record->country_code) && Validate::isLanguageIsoCode($record->country_code) && ($idCountry =(int) Country::getByIso(strtoupper($record->country_code))) != 0) {
                            if (!(!in_array(strtoupper($record->country_code), explode(';', Configuration::get('PS_ALLOWED_COUNTRIES'))) && !self::isInWhitelistForGeolocation())) {
                                return $idCountry;
                            }
                        }
                    }
                    else
                    {
                        $reader = new GeoIp2\Database\Reader(_PS_GEOIP_DIR_ . _PS_GEOIP_CITY_FILE_);
                        try {
                            $record = $reader->city(Tools::getRemoteAddr());
                        } catch (\GeoIp2\Exception\AddressNotFoundException $e) {
                            $record = null;
                        }
                        if (is_object($record) && Validate::isLanguageIsoCode($record->country->isoCode) && ($idCountry =(int) Country::getByIso(strtoupper($record->country->isoCode))) != 0) {
                            if (!(!in_array(strtoupper($record->country->isoCode), explode(';', Configuration::get('PS_ALLOWED_COUNTRIES'))) && !self::isInWhitelistForGeolocation())) {
                                return $idCountry;
                            }
                        }
                    }
                }
            }
        }

        return false;
    }
    public static function isInWhitelistForGeolocation()
    {
        static $allowed = null;

        if ($allowed !== null) {
            return $allowed;
        }

        $allowed = false;
        $user_ip = Tools::getRemoteAddr();
        $ips = [];

        // retrocompatibility
        $ips_old = explode(';', Configuration::get('PS_GEOLOCATION_WHITELIST'));
        if (is_array($ips_old) && count($ips_old)) {
            foreach ($ips_old as $ip) {
                $ips = array_merge($ips, explode("\n", $ip));
            }
        }

        $ips = array_map('trim', $ips);
        if (is_array($ips) && count($ips)) {
            foreach ($ips as $ip) {
                if (!empty($ip) && preg_match('/^' . $ip . '.*/', $user_ip)) {
                    $allowed = true;
                }
            }
        }

        return $allowed;
    }
    public function getCache($check_connect=false)
    {
        $context = Context::getContext();
        if(defined('_PS_ADMIN_DIR_') && isset($context->employee) && isset($context->employee->id) && $context->employee->id)
            return ;
        if((isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD']=='POST') || self::isAjax() || !Configuration::get('ETS_SPEED_ENABLE_PAGE_CACHE') || !Configuration::get('PS_SHOP_ENABLE') )
            return false;
        $file_name=$this->getFileCacheByUrl();
        if(($pageCache = Ets_superspeed_cache_page::getCacheContent($file_name))!==false)
        {
            if(!Ets_ss_class_cache::isCheckSpeed() && $check_connect && (int)Configuration::get('ETS_RECORD_PAGE_CLICK'))
            {
                Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'ets_superspeed_cache_page` SET click = click+1 WHERE file_cache="'.pSQL($file_name).'"');
            }
            if (!defined('_PS_ADMIN_DIR_') && ($context =Context::getContext()) && isset($context->ss_start_time) && ($start_time =  (float)$context->ss_start_time))
            {
                header('X-SS: cached at ' .$pageCache['date_add']. ', '.(Tools::ps_round((microtime(true)-$start_time),3)*1000).'ms'.(isset($context->ss_total_sql) ? '/'.$context->ss_total_sql:'') );
            }
            return $pageCache['cache_content'];
        }
        return false;
    }
    public function setCache($value,$params)
    {
        if($_SERVER['REQUEST_METHOD']=='POST' || self::isAjax() || !Configuration::get('ETS_SPEED_ENABLE_PAGE_CACHE') || !Configuration::get('PS_SHOP_ENABLE') )
            return false;

        if(($pages_exception = Tools::strtolower(trim(Configuration::get('ETS_SPEED_PAGES_EXCEPTION')))) )
        {
            if($pages_exception = explode("\n",$pages_exception))
            foreach($pages_exception as $page_exception)
            {
                $page_exception = trim($page_exception);
                if($page_exception && Tools::strpos(Tools::strtolower($_SERVER['REQUEST_URI']),$page_exception)!==false)
                    return false;
            }
        }

        $controller = isset($params['controller']) ? $params['controller']:'';
        if(!$controller || !Validate::isControllerName($controller))
            return false;
        $id_object = isset($params['id_object']) ? (int)$params['id_object']:0;
        $fc = isset($params['fc']) ? $params['fc']:'';
        $module = isset($params['module']) ? $params['module']:'';
        if($module=='ybc_blog' && Module::isInstalled('ybc_blog') && Module::isEnabled('ybc_blog') && $fc=='module' && in_array($controller,array('blog','category','gallery','author')))
        {
            if($controller=='blog')
            {
                $id_post = isset($params['id_post']) ? (int)$params['id_post']:0 ;
                $post_url_alias = isset($params['post_url_alias']) ? $params['post_url_alias']:'';
                if(!$id_post && $post_url_alias && Validate::isLinkRewrite($post_url_alias))
                {
                    $id_post = (int)Db::getInstance()->getValue('SELECT ps.id_post FROM `'._DB_PREFIX_.'ybc_blog_post_lang` pl ,`'._DB_PREFIX_.'ybc_blog_post_shop` ps  WHERE ps.id_shop="'.(int)$this->context->shop->id.'" AND ps.id_post=pl.id_post AND pl.url_alias ="'.pSQL($post_url_alias).'"');
                }
                if($id_post)
                    $id_object=$id_post;
                else
                {
                    $id_category = isset($params['id_category']) ? (int)$params['id_category']:0;
                    $category_url_alias = isset($params['category_url_alias']) ? $params['category_url_alias']:'';
                    if(!$id_category && $category_url_alias && Validate::isLinkRewrite($category_url_alias))
                    {
                        $id_category = (int)Db::getInstance()->getValue('SELECT cs.id_category FROM `'._DB_PREFIX_.'ybc_blog_category_lang` cl,`'._DB_PREFIX_.'ybc_blog_category_shop` cs WHERE cs.id_category=cl.id_category AND cs.id_shop="'.(int)$this->context->shop->id.'" AND cl.url_alias ="'.pSQL($category_url_alias).'"');    
                    }
                    if($id_category)
                        $id_object=$id_category;
                    elseif(isset($params['id_author']) && ($id_author = (int)$params['id_author']))
                        $id_object=$id_author;
                }
                
            }
            $controller = 'blog';
        }
        $id_currency = ($this->context->cookie->id_currency ? $this->context->cookie->id_currency : Configuration::get('PS_CURRENCY_DEFAULT'));
        $id_lang = $this->context->language->id;
        $id_country = $this->getIDCountry();
        $id_shop= $this->context->shop->id;
        $file_name = $this->getFileCacheByUrl();
        $ip = Tools::getRemoteAddr();
        if($id_cache_page = (int)Db::getInstance()->getValue('
            SELECT id_cache_page FROM `'._DB_PREFIX_.'ets_superspeed_cache_page`
            WHERE page="'.pSQL($controller).'" 
            AND id_lang="'.(int)$id_lang.'"
            AND id_country = "'.(int)$id_country.'"
            AND id_currency = "'.(int)$id_currency.'"
            AND id_shop="'.(int)$id_shop.'"
            AND user_agent="'.pSQL($this->user_agent).'"
            AND date_add > "'.pSQL(date('Y-m-d H:i:s', strtotime('-'.($controller=='blog' ? '1':'15').' minutes'))).'"'
            .((int)$id_object ? 'AND id_object="'.(int)$id_object.'"':'')
            .($this->context->customer->id ? ' AND has_customer=1':' AND has_customer=0')
            .($this->context->cart->id ? ' AND has_cart=1':' AND has_cart=0')
        ))
        {
            if(Configuration::get('ETS_SPEED_ENABLE_LOG_CACHE_ERROR'))
            {
                $pageError = new Ets_superspeed_cache_page_error();
                $pageError->id_cache_page = $id_cache_page;
                $pageError->page = $controller;
                $pageError->id_object = (int)$id_object;
                $pageError->id_product_attribute = isset($params['id_product_attribute']) ? (int)$params['id_product_attribute']:0;
                $pageError->id_lang = (int)$id_lang;
                $pageError->id_country = (int)$id_country;
                $pageError->id_currency = (int)$id_currency;
                $pageError->id_shop = (int)$id_shop;
                $pageError->has_cart = $this->context->cart->id ? 1:0;
                $pageError->has_customer = $this->context->customer->id ? 1 :0;
                $pageError->ip = !self::isCheckSpeed() ? $ip:'';
                $pageError->file_cache = $file_name;
                $pageError->request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
                $pageError->error = sprintf($this->l('Cache created #%d'),$id_cache_page);
                $pageError->add();
            }
            return false;
        }
        if(!is_dir(_ETS_SPEED_CACHE_DIR_))
            @mkdir(_ETS_SPEED_CACHE_DIR_,0777,true);
        if(!is_dir(_ETS_SPEED_CACHE_DIR_.$id_shop))
            @mkdir(_ETS_SPEED_CACHE_DIR_.$id_shop,0777,true);

        if($id_page_cache = Db::getInstance()->getValue('SELECT id_cache_page FROM `'._DB_PREFIX_.'ets_superspeed_cache_page` WHERE file_cache = "'.pSQL($file_name).'" AND id_shop="'.(int)Context::getContext()->shop->id.'"'))
        {
            $page_cache = new Ets_superspeed_cache_page($id_page_cache);
        }
        else
        {
            $page_cache= new Ets_superspeed_cache_page();
            $page_cache->click=1;
        }
        $page_cache->page = $controller;
        $page_cache->id_object = (int)$id_object;
        $page_cache->id_country = (int)$id_country;
        $page_cache->id_lang = (int)$id_lang;
        $page_cache->id_currency = (int)$id_currency;
        $page_cache->ip = !self::isCheckSpeed() ? Tools::getRemoteAddr():'';
        $page_cache->id_product_attribute = isset($params['id_product_attribute']) ? (int)$params['id_product_attribute']:0;
        $page_cache->id_shop = Context::getContext()->shop->id;
        $page_cache->file_cache= $file_name;
        $page_cache->has_customer = $this->context->customer->id ? 1 : 0;
        $page_cache->has_cart = $this->context->cart->id ? 1 : 0;
        $page_cache->request_uri = $_SERVER['REQUEST_URI'];
        $page_cache->user_agent = $this->user_agent;
        $page_cache->date_add = date('Y-m-d H:i:s');
        if(($lifetime = (int)Configuration::get('ETS_SPEED_TIME_CACHE_' . Tools::strtoupper($controller))) && $lifetime != 31)
        {
            $page_cache->date_expired = date('Y-m-d H:i:s',strtotime('+'.($lifetime ? $lifetime : 1).' DAY'));
        }
        else
            $page_cache->date_expired ='';
        $error ='';
        if($page_cache->id)
        {
            $page_cache->file_size = $page_cache->setFileCache($value);
            if($page_cache->file_size && Validate::isUnsignedFloat($page_cache->file_size))
            {
                if($page_cache->update())
                {
                    Db::getInstance()->delete('ets_superspeed_cache_page_hook', 'id_cache_page = ' . (int)$id_page_cache);
                }
                else
                    $error = $this->l('Create cache for object failed');
            }
            else
                $error = $this->l('Create cache file failed');

        }
        elseif($page_cache->add())
        {
            $page_cache->file_size = $page_cache->setFileCache($value);
            if($page_cache->file_size && Validate::isUnsignedFloat($page_cache->file_size))
            {
                if(!$page_cache->update())
                    $error = $this->l('Create cache for object failed');
                $id_page_cache = $page_cache->id;
            }
            else
                $error = $this->l('Create cache file failed');
        }
        else
            $error = $this->l('Create cache for object failed');
        if($error)
        {
            if(Configuration::get('ETS_SPEED_ENABLE_LOG_CACHE_ERROR'))
            {
                $pageError = new Ets_superspeed_cache_page_error();
                $pageError->id_cache_page = $id_cache_page;
                $pageError->page = $controller;
                $pageError->id_object = (int)$id_object;
                $pageError->id_product_attribute = isset($params['id_product_attribute']) ? (int)$params['id_product_attribute']:0;
                $pageError->id_lang = (int)$id_lang;
                $pageError->id_country = (int)$id_country;
                $pageError->id_currency = (int)$id_currency;
                $pageError->id_shop = (int)$id_shop;
                $pageError->has_cart = $this->context->cart->id ? 1:0;
                $pageError->has_customer = $this->context->customer->id ? 1 :0;
                $pageError->ip = !self::isCheckSpeed() ? Tools::getRemoteAddr():'';
                $pageError->file_cache = $file_name;
                $pageError->request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
                $pageError->error = $error;
                $pageError->add();
            }
        }
        else
        {
            if(Hook::$executed_hooks && $id_page_cache)
            {
                foreach(Hook::$executed_hooks as $hook_name)
                    Db::getInstance()->execute('INSERT IGNORE INTO `'._DB_PREFIX_.'ets_superspeed_cache_page_hook` set id_cache_page="'.(int)$id_page_cache.'", hook_name="'.pSQL($hook_name).'"');
            }
        }

    }
    public function deleteCache($page='',$id_object=0,$hook_name='')
    {
        if(Db::getInstance()->executeS('SHOW TABLES LIKE "'._DB_PREFIX_.'ets_superspeed_cache_page"'))
        {
            if(!$page && !$id_object && !$hook_name)
            {
                Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'ets_superspeed_cache_page`');
                Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'ets_superspeed_cache_page_hook`');
                Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'ets_superspeed_cache_page_error`');
                $this->rmDir(_ETS_SPEED_CACHE_DIR_);
            }
            else
            {
               $pageCaches = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'ets_superspeed_cache_page`
                    WHERE 1 '.($page ? ' AND page="'.pSQL($page).'"':'').($id_object ? ' AND id_object="'.(int)$id_object.'"':'').($hook_name ? ' AND id_cache_page IN (SELECT id_cache_page FROM `'._DB_PREFIX_.'ets_superspeed_cache_page_hook` WHERE hook_name="'.pSQL($hook_name).'")':''));
               if($pageCaches)
               {
                    foreach($pageCaches as $pageCache)
                    {
                        $pageCacheObj = new Ets_superspeed_cache_page($pageCache['id_cache_page']);
                        $pageCacheObj->delete();
                    }
               }
            }
        }
        return true;
    }
    public function rmDir($directory)
    {
        $baseDir = realpath(_PS_ROOT_DIR_);
        $directory = realpath($directory);
        if ($directory === false || Tools::strpos($directory, $baseDir) !== 0) {
            return false;
        }
        if(is_dir($directory))
        {
            $dir = @opendir(trim($directory));
            while (false !== ($file = @readdir($dir))) {
                if (($file != '.') && ($file != '..') &&$file!=='') {
                    if (is_file($directory .DIRECTORY_SEPARATOR. $file)) {
                        if (file_exists($directory  .DIRECTORY_SEPARATOR. $file)) {
                            Ets_superspeed_defines::unlink($directory.DIRECTORY_SEPARATOR . $file);
                        }
                    } else {
                        $this->rmDir($directory .DIRECTORY_SEPARATOR. $file.DIRECTORY_SEPARATOR);
                    }
                }
            }
            @closedir($dir);
            @rmdir($directory); 
        }
        return true;
    }
    public static function getallheaders()
    {
        $headers = [];
        if($_SERVER)
        {
            foreach ($_SERVER as $name => $value)
            {
                if (Tools::substr($name, 0, 5) == 'HTTP_')
                {
                    $headers[str_replace(' ', '-', ucwords(Tools::strtolower(str_replace('_', ' ', Tools::substr($name, 5)))))] = $value;
                }
            }
        }
        return $headers;
    }
    public static function isCheckSpeed()
    {
        $headers = self::getallheaders();
        if($headers && ((isset($headers['XTestSS']) && $headers['XTestSS']=='click') || (isset($headers['Xtestss']) && $headers['Xtestss']=='click') || (isset($headers['xtestss']) && $headers['xtestss']=='click') ))
            return true;
        else
            return false;
    }
    public function l($string)
    {
        return Translate::getModuleTranslation('ets_superspeed', $string, pathinfo(__FILE__, PATHINFO_FILENAME));
    }
    public static function displayDate($date, $full = false)
    {
        if (!$date || !($time = strtotime($date))) {
            return $date;
        }

        if ($date == '0000-00-00 00:00:00' || $date == '0000-00-00') {
            return '';
        }
        $context = Context::getContext();
        $date_format = ($full ? $context->language->date_format_full : $context->language->date_format_lite);

        return date($date_format, $time);
    }
    public static function isAjax()
    {
        if(self::isCheckSpeed())
            return false;
        $isAjax = Tools::isSubmit('ajax') || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest');
        if (isset($_SERVER['HTTP_ACCEPT'])) {
            $isAjax = $isAjax || preg_match(
                    '#\bapplication/json\b#',
                    $_SERVER['HTTP_ACCEPT']
                );
        }
        return $isAjax;
    }
}
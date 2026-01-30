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
class Ets_superspeed_cache_page extends ObjectModel
{
    public $page;
    public $id_object;
    public $id_product_attribute;
    public $ip;
	public $file_cache;
    public $request_uri;
	public $id_shop;
    public $id_lang;
    public $id_currency;
    public $id_country;
    public $file_size;
    public $user_agent;
    public $has_customer;
    public $has_cart;
    public $date_add;
    public $date_expired;
    public $_dir;
    public $click;
    public static $definition = array(
		'table' => 'ets_superspeed_cache_page',
		'primary' => 'id_cache_page',
		'multilang' => false,
		'fields' => array(
			'page' => array('type' => self::TYPE_STRING),
            'id_object' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'id_product_attribute' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'ip' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),  
            'file_cache' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),            
            'request_uri' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
            'id_shop'  => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'), 
            'id_lang'  => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'), 
            'id_currency'  => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'), 
            'id_country'  => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'), 
            'has_customer'  => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'), 
            'has_cart'  => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'click'  => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'file_size' => array('type' =>   self::TYPE_FLOAT),   
            'user_agent' => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
            'date_add' =>	array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
            'date_expired' =>array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
        )
	);
    public static function getFolderStatic($file_name, $length = 6)
    {
        // Ensure file_name is a string and sanitize it
        $file_name = (string) $file_name;

        // Limit the length of the file_name and sanitize
        $file_name = Tools::substr($file_name, 0, $length);
        $file_name = preg_replace('/[^a-zA-Z0-9]/', '', $file_name); // Remove non-alphanumeric characters

        // Split the sanitized file_name into individual characters
        $folders = str_split($file_name, 1);

        // Create a path with sanitized folders
        return implode(DIRECTORY_SEPARATOR, $folders) . '/';
    }
    public	function __construct($id_item = null, $id_lang = null, $id_shop = null)
	{
		parent::__construct($id_item, $id_lang, $id_shop);
        $this->_dir = _ETS_SPEED_CACHE_DIR_.(int)$this->id_shop.'/'.self::getFolderStatic($this->file_cache);
	}
    public function add($auto_date = true,$null_value=false)
    {
        if(parent::add($auto_date,$null_value))
        {
            $this->_dir = _ETS_SPEED_CACHE_DIR_.(int)$this->id_shop.DIRECTORY_SEPARATOR.self::getFolderStatic($this->file_cache);
            return true;
        }
    }
    public function setFileCache($value)
    {
        if (!$this->id) {
            return false;
        }

        // Ensure the directory is valid and sanitize the path
        if (Tools::strpos($this->_dir,_ETS_SPEED_CACHE_DIR_)!==0) {
            throw new Exception('Invalid cache directory.');
        }
        if(!is_dir($this->_dir))
        {
            @mkdir($this->_dir,0777,true);
        }
        $fileName = preg_replace('/[^a-zA-Z0-9_-]/', '', $this->file_cache);
        if ($this->date_expired) {
            $fileName .= strtotime($this->date_expired);
        }

        // Use a valid extension based on the type of cache
        if (Configuration::get('ETS_SPEED_COMPRESS_CACHE_FIIE') && class_exists('ZipArchive')) {
            $cache_file = $this->_dir . DIRECTORY_SEPARATOR . $fileName . '.zip';
            $zip = new ZipArchive();

            if (!file_exists($cache_file)) {
                if ($zip->open($cache_file, ZipArchive::CREATE | ZipArchive::CHECKCONS) === true) {
                    $zip->addFromString($fileName, $value);
                }
            } else {
                if ($zip->open($cache_file)) {
                    $zip->addFromString($fileName, $value);
                }
            }
            $zip->close();
        } else {
            $cache_file = $this->_dir . DIRECTORY_SEPARATOR . $fileName . '.html';
            file_put_contents($cache_file, $value);
        }

        return Tools::ps_round(@filesize($cache_file) / 1024, 2);
    }
    public static function getCacheContent($file_name)
    {
        $context = Context::getContext();
        $shop_id = (int)$context->shop->id;
        $base_dir = realpath(_ETS_SPEED_CACHE_DIR_ . $shop_id) ?: _ETS_SPEED_CACHE_DIR_ . $shop_id;
        $file_dir = $base_dir . DIRECTORY_SEPARATOR . self::getFolderStatic($file_name);

        // Ensure file_name is safe
        $file_name = basename($file_name);

        // Check if the directory is within allowed directory
        if (Tools::strpos(realpath($file_dir), $base_dir) !== 0) {
            return false;
        }

        if (Configuration::get('ETS_SPEED_COMPRESS_CACHE_FIIE') && class_exists('ZipArchive')) {
            if (is_dir($file_dir)) {
                foreach (glob($file_dir . $file_name . '*.zip') as $filename) {
                    if (($time = str_replace('.zip', '', Tools::substr(basename($filename), 64)))) {
                        if ($time >= time() && ($zip = new ZipArchive()) && $zip->open($filename)) {
                            return array(
                                'date_add' => Ets_ss_class_cache::displayDate(date('Y-m-d H:i:s', filemtime($filename)), true),
                                'cache_content' => $zip->getFromName(str_replace('.zip', '', basename($filename))),
                            );
                        } else {
                            Ets_superspeed_defines::unlink($filename);
                            return false;
                        }
                    } elseif (($zip = new ZipArchive()) && $zip->open($filename)) {
                        return array(
                            'date_add' => Ets_ss_class_cache::displayDate(date('Y-m-d H:i:s', filemtime($filename)), true),
                            'cache_content' => $zip->getFromName(str_replace('.zip', '', basename($filename))),
                        );
                    }
                }
            }
        } else {
            if (is_dir($file_dir)) {
                foreach (glob($file_dir . $file_name . '*.html') as $filename) {
                    if (($time = str_replace('.html', '', Tools::substr(basename($filename), 64)))) {
                        if ($time >= time()) {
                            return array(
                                'date_add' => Ets_ss_class_cache::displayDate(date('Y-m-d H:i:s', filemtime($filename)), true),
                                'cache_content' => Tools::file_get_contents($filename),
                            );
                        } else {
                            Ets_superspeed_defines::unlink($filename);
                            return false;
                        }
                    }
                    return array(
                        'date_add' => Ets_ss_class_cache::displayDate(date('Y-m-d H:i:s', filemtime($filename)), true),
                        'cache_content' => Tools::file_get_contents($filename),
                    );
                }
            }
        }
        return false;
    }
    public function delete()
    {
        if (parent::delete()) {
            Db::getInstance()->delete('ets_superspeed_cache_page_hook','id_cache_page =' . (int)$this->id);
            $dir = rtrim($this->_dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            $safe_dir = realpath($dir);
            // Check if the directory exists and is a valid directory
            if ($safe_dir === false || Tools::strpos($safe_dir, realpath(_ETS_SPEED_CACHE_DIR_)) !== 0) {
                return false;
            }
            if (is_dir($safe_dir)) {
                // Get all files in the directory matching the pattern
                foreach (glob($safe_dir .DIRECTORY_SEPARATOR. $this->file_cache . '*.*') as $filename) {
                    // Ensure file is within the intended directory
                    if (Tools::strpos(realpath($filename), $safe_dir) === 0) {

                        Ets_superspeed_defines::unlink($filename);
                    }
                }
            }
            self::deleteFolder($this->file_cache, $this->id_shop);
            return true;
        }
        return false;
    }
    public static function deleteById($id,$id_shop)
    {
        $pageCache = new Ets_superspeed_cache_page($id,$id_shop);
        return $pageCache->delete();
    }
    public static function deleteByWhere($where='')
    {
        return Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'ets_superspeed_cache_page` WHERE 1 '.($where ? (string)$where :''));
    }
    public static function deleteFolder($file_cache, $id_shop, $length = 6)
    {
        // Sanitize inputs
        $file_cache = preg_replace('/[^a-zA-Z0-9_]/', '', $file_cache);
        $id_shop = (int)$id_shop;
        // Construct the directory path securely

        $dir = _ETS_SPEED_CACHE_DIR_ . $id_shop . DIRECTORY_SEPARATOR . self::getFolderStatic($file_cache, $length);
        // Ensure directory path is within the expected cache directory

        if (Tools::strpos(realpath($dir), realpath(_ETS_SPEED_CACHE_DIR_)) !== 0) {
            throw new Exception('Invalid directory path.');
        }

        if (is_dir($dir)) {
            $deleteFolder = true;

            // Check if the directory is empty
            foreach (scandir($dir, SCANDIR_SORT_NONE) as $file) {
                if ($file != '.' && $file != '..') {
                    $deleteFolder = false;
                    break;
                }
            }

            if ($deleteFolder) {
                if (@rmdir($dir)) {
                    // Recur if there are parent directories to delete

                    if ($length > 1) {
                        $length--;
                        self::deleteFolder($file_cache, $id_shop, $length);
                    }
                } else {
                    // Handle potential rmdir failure
                    throw new Exception('Failed to remove directory: ' . $dir);
                }
            }
        }
        return true;
    }
    public static function getTotalPageCaches($filter='')
    {
        $sql ='SELECT COUNT(cache.id_cache_page) FROM `' . _DB_PREFIX_ . 'ets_superspeed_cache_page` cache';
        if($filter)
        {
            $sql .=' LEFT JOIN `'._DB_PREFIX_.'currency` currency ON (cache.id_currency = currency.id_currency)
            LEFT JOIN `'._DB_PREFIX_.'country` country ON (country.id_country=cache.id_country)
            LEFT JOIN `'._DB_PREFIX_.'country_lang` country_lang ON (country_lang.id_country = country.id_country AND country_lang.id_lang="'.(int)Context::getContext()->language->id.'")
            LEFT JOIN `'._DB_PREFIX_.'lang` lang ON (lang.id_lang=cache.id_lang)';
    }
        $sql .=' WHERE cache.id_shop=' . (int)Context::getContext()->shop->id.($filter ? (string)$filter:'');
        return (int)Db::getInstance()->getValue($sql);
    }
    public static function getListPageCaches($start=0,$limit=20,$sql_sort='',$filter='')
    {
        $sql ='SELECT cache.*,currency.iso_code,country_lang.name as country_name,lang.name as lang_name FROM `' . _DB_PREFIX_ . 'ets_superspeed_cache_page` cache
        LEFT JOIN `'._DB_PREFIX_.'currency` currency ON (cache.id_currency = currency.id_currency)
        LEFT JOIN `'._DB_PREFIX_.'country` country ON (country.id_country=cache.id_country)
        LEFT JOIN `'._DB_PREFIX_.'country_lang` country_lang ON (country_lang.id_country = country.id_country AND country_lang.id_lang="'.(int)Context::getContext()->language->id.'")
        LEFT JOIN `'._DB_PREFIX_.'lang` lang ON (lang.id_lang=cache.id_lang)
        WHERE cache.id_shop="' . (int)Context::getContext()->shop->id . '"'.($filter ? (string)$filter:'') .($sql_sort ? ' ORDER BY '.bqSQL($sql_sort) : '').' LIMIT ' . (int)$start . ',' . (int)$limit;
        return Db::getInstance()->executeS($sql);
    }
    public static function getListFileCache($limit = 10,$filter='',$sort= '')
    {
        return Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'ets_superspeed_cache_page` WHERE 1 '.($filter ? (string)$filter:' AND id_shop="' . (int)Context::getContext()->shop->id . '"').' ORDER BY '.($sort  ? bqSQL($sort) : 'date_add desc').($limit ? ' LIMIT 0,'.(int)$limit:''));
    }
    public static function getRowCache()
    {
        return Db::getInstance()->getRow('SELECT SUM(file_size) as total_cache,COUNT(file_cache) as total_file FROM `'._DB_PREFIX_.'ets_superspeed_cache_page` WHERE id_shop='.(int)Context::getContext()->shop->id);
    }
    public static function getTotalHomeSlider()
    {
        return Db::getInstance()->getValue('SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'homeslider_slides` hs
                INNER JOIN `' . _DB_PREFIX_ . 'homeslider` h ON (hs.id_homeslider_slides = h.id_homeslider_slides)
                WHERE hs.active=1 AND h.id_shop=' . (int)Context::getContext()->shop->id);
    }
    public static function getTotalPoints()
    {
        return (int)Db::getInstance()->getValue('SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'ets_superspeed_hook_time` pht
            INNER JOIN `' . _DB_PREFIX_ . 'hook` h ON (pht.hook_name = h.name)
            INNER JOIN `' . _DB_PREFIX_ . 'hook_module` hm ON (hm.id_hook=h.id_hook AND hm.id_module=pht.id_module)
            WHERE hm.id_shop="' . (int)Context::getContext()->shop->id . '" AND pht.time >1');
    }
    public static function getDynamicHookModule($id_module, $hook_name)
    {
        $context = Context::getContext();
        $always_load_content = Configuration::get('ETS_ALWAYS_LOAD_DYNAMIC_CONTENT');
        if ($id_module && ($id_module == Module::getModuleIdByName('blockcart') || $id_module == Module::getModuleIdByName('ps_shoppingcart') || $id_module == Module::getModuleIdByName('tdshoppingcart')) && $hook_name != 'header' && $hook_name != 'displayHeader' && ($always_load_content || (isset($context->cookie->id_cart) && $context->cookie->id_cart))) {
            return array(
                'empty_content' => 0,
            );
        }
        if ($id_module && ($id_module == Module::getModuleIdByName('ps_customersignin') || $id_module == Module::getModuleIdByName('blockuserinfo')) && $hook_name != 'header' && $hook_name != 'displayHeader' && ($always_load_content || (isset($context->customer->id) && $context->customer->id && $context->customer->logged))) {
            return array(
                'empty_content' => 1,
            );
        }
        if(($hooks = Ets_superspeed_defines::getInstance()->getFieldConfig('_dynamic_hooks'))  && ($hooks = array_map('strtolower',$hooks)) && in_array(Tools::strtolower($hook_name),$hooks))
        {
            $sql = 'SELECT d.* FROM `' . _DB_PREFIX_ . 'ets_superspeed_dynamic` d
            LEFT JOIN `' . _DB_PREFIX_ . 'hook` h ON (h.name=d.hook_name)
            LEFT JOIN `' . _DB_PREFIX_ . 'hook_alias` ha ON (d.hook_name=ha.name OR d.hook_name = ha.alias)
            WHERE (h.name="' . pSQL($hook_name) . '" OR ha.alias ="' . pSQL($hook_name) . '" OR ha.name="'.pSQL($hook_name).'") AND d.id_module="' . (int)$id_module . '"';
            return Db::getInstance()->getRow($sql);
        }
    }
    public static function updateDynamicHook($add,$action,$id_module,$hook_name,$empty_content)
    {
        if ($add || $action == 'update_dynamic_modules') {
            if (!Db::getInstance()->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'ets_superspeed_dynamic` WHERE id_module="' . (int)$id_module . '" AND hook_name="' . pSQL($hook_name) . '"')) {
                Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'ets_superspeed_dynamic` (id_module,hook_name,empty_content) VALUES("' . (int)$id_module . '","' . pSQL($hook_name) . '","' . (int)$empty_content . '")');
            } else {
                Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'ets_superspeed_dynamic` SET empty_content="' . (int)$empty_content . '" WHERE id_module="' . (int)$id_module . '" AND hook_name="' . pSQL($hook_name) . '"');
            }
        }
        else
        {
            if(Validate::isHookName($hook_name))
                Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'ets_superspeed_dynamic` WHERE id_module="' . (int)$id_module . '" AND hook_name="' . pSQL($hook_name) . '"');
        }
        return true;
    }
    public static function submitTimeSpeed($request_time){
        $id_shop = Context::getContext()->shop->id;
        if($request_time)
        {
            $request_time = Tools::ps_round($request_time/1000,2);
            Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'ets_superspeed_time`(id_shop,`date`,`time`) VALUES("'.(int)$id_shop.'","'.pSQL(date('Y-m-d H:i:s')).'","'.(float)$request_time.'")');
            $count= Db::getInstance()->getValue('SELECT COUNT(*) FROM `'._DB_PREFIX_.'ets_superspeed_time` WHERE id_shop="'.(int)$id_shop.'"');
            if($count > 150)
            {
                $mintime= Db::getInstance()->getValue('SELECT MIN(`date`) FROM `'._DB_PREFIX_.'ets_superspeed_time` WHERE id_shop="'.(int)$id_shop.'"');
                Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'ets_superspeed_time` WHERE id_shop="'.(int)$id_shop.'" AND `date` ="'.pSQL($mintime).'"');
            }
        }
    }
    public static function getTimeSpeed()
    {
        return Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'ets_superspeed_time` WHERE id_shop="' . (int)Context::getContext()->shop->id . '" AND `date`<="' . pSQL(date('Y-m-d H:i:s')) . '" ORDER BY `date` DESC LIMIT 0,150');
    }
    public static function deleteAllCache()
    {
        Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'ets_superspeed_cache_page`');
        Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'ets_superspeed_cache_page_hook`');
        Ets_ss_class_cache::getInstance()->rmDir(_ETS_SPEED_CACHE_DIR_);
        return true;
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
    public static function isCheckSpeed()
    {
        $headers = self::getallheaders();
        if($headers && ((isset($headers['XTestSS']) && $headers['XTestSS']=='click') || (isset($headers['Xtestss']) && $headers['Xtestss']=='click') || (isset($headers['xtestss']) && $headers['xtestss']=='click') ))
            return true;
        else
            return false;
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
    public static function checkPageNoCache()
    {
        if($pages_exception = Configuration::get('ETS_SPEED_PAGES_EXCEPTION'))
        {
            $pages_exception = explode("\n",$pages_exception);
            foreach($pages_exception as $page_exception)
            {

                if($page_exception && isset($_SERVER['REQUEST_URI']) && Tools::strpos(Tools::strtolower($_SERVER['REQUEST_URI']),Tools::strtolower($page_exception))!==false)
                    return true;
            }
        }
        if(Ets_superspeed::isPageCache())
        {
            return false;
        }
        return true;
    }
}
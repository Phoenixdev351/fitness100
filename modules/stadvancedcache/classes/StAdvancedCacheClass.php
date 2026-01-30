<?php

class StAdvancedCacheClass
{
    private $cache_dir;
    private $module;
    public static $instance = null;

    public function __construct($module)
    {
        $this->module = $module;
        $this->cache_dir = _PS_CACHE_DIR_ . $this->module->name . '/' . Context::getContext()->shop->id . '/';
    }
    public static function getInstance($module)
    {
        if (!self::$instance) {
            self::$instance = new StAdvancedCacheClass($module);
        }
        return self::$instance;
    }
    public function getCacheDir($key)
    {
        $sub_dir = str_split((string)Tools::substr($key, 0, 2));
        $sub_dir = implode('/', $sub_dir);
        return $this->cache_dir.$sub_dir.'/';
    }
    public function getFsCache($key, $ttl = -1)
    {
        if (!$key) {
            return false;
        }
        $cache_file = $this->getCacheDir($key).$key;
        $filemtime = @filemtime($cache_file);
        if ($filemtime && ($ttl < 0 || (microtime(true) - $filemtime < $ttl))) {
            if (file_exists($cache_file)) {
                return Tools::file_get_contents($cache_file);
            }
        }
        return false;
    }
    public function setFsCache($id_sign, $content)
    {
        if (!$id_sign) {
            return false;
        }
        $key = $this->getKey($id_sign);
        $cache_dir = $this->getCacheDir($key);

        if (!file_exists($cache_dir)) {
            @mkdir($cache_dir, 0777, true);
        }

        return file_put_contents($cache_dir.$key, $content, LOCK_EX) !== false;
    }
    public function deleteFsCache($id_sign)
    {
        $key = $this->getKey($id_sign);
        $cache_file = $this->getCacheDir($key).$key;
        if (file_exists($cache_file)) {
            @unlink($cache_file);
        }
    }
    public function flushFsCache()
    {
        if ($this->cache_dir && file_exists($this->cache_dir)) {
            Tools::deleteDirectory($this->cache_dir, true);
        }
    }
    public function getCustomGroups()
    {
        return FrontController::getCurrentCustomerGroups();
    }
    public static function getAll($id_shop)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executes('SELECT * FROM ' ._DB_PREFIX_.'st_advanced_cache WHERE `id_shop`='.(int)$id_shop);
    }
    public function getCacheRow(
        $id_sign, 
        $id_shop, 
        $id_language, 
        $id_currency, 
        $id_country, 
        $is_mobile
    ){
        $where_customer_groups = '';
        if (Configuration::get($this->module->_prefix_st.'ENABLE_CUSTOMGRP')) {
            $where_customer_groups = ' AND `customer_groups` = "'.implode('|', Context::getContext()->controller->getCurrentCustomerGroups()).'"';
        }
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
            SELECT `id_st_advanced_cache`, `file_name`, `cache`, `hits`, `misses`,`hit_time`, `miss_time`, `last_updated`
            FROM ' ._DB_PREFIX_.'st_advanced_cache 
            WHERE `id_sign` = "'.pSQL($id_sign).'" 
            AND `id_shop` = '.(int)$id_shop.' 
            AND `id_language` = '.(int)$id_language.' 
            AND `id_currency` = '.(int)$id_currency.' 
            AND `id_country` = '.(int)$id_country.' 
            AND `is_mobile` = '.(int)$is_mobile.'
            AND `is_module` = '.(int)(Tools::getValue('fc') == 'module').
            $where_customer_groups
        );
    }
    public function updateHits($id_st_advanced_cache, $cache_time)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->execute(
            'UPDATE '._DB_PREFIX_.'st_advanced_cache set `hits` = `hits` + 1, 
            `hit_time` = ((hit_time * hits) + '.$cache_time.') / (hits + 1)
            WHERE `id_st_advanced_cache` = '.(int)$id_st_advanced_cache
        );
    }
    public function emptyDbFileName(
        $id_sign,
        $id_shop,
        $id_language,
        $id_currency,
        $id_country
    ){
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->execute('
            UPDATE ' ._DB_PREFIX_.'st_advanced_cache set `file_name` = ""
            WHERE `id_sign` = "' .pSQL($id_sign).'" 
            AND `id_language` = '.(int)$id_language.' 
            AND `id_currency` = '.(int)$id_currency.' 
            AND `id_country` = '.(int)$id_country.' 
            AND `id_shop` = '.(int)$id_shop.'
            AND `is_module` = '.(int)(Tools::getValue('fc') == 'module')
        );
    }
    public function updateDbCache(
        $id_sign,
        $url,
        $cache,
        $id_shop,
        $id_language,
        $id_currency,
        $id_country,
        $is_mobile,
        $controller,
        $id_object,
        $cache_size,
        $cache_time
    ){
        $customer_groups = '';
        if (Configuration::get($this->module->_prefix_st.'ENABLE_CUSTOMGRP')) {
            $customer_groups = implode('|', Context::getContext()->controller->getCurrentCustomerGroups());
        }
        $file_name = $this->getkey($id_sign);
        if ($cache) {
            $cache = pSQL($cache, true);
        }
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->execute(
            'INSERT INTO '._DB_PREFIX_.'st_advanced_cache 
            (`id_sign`, `url`, `file_name`, `id_shop`, `id_language`, 
            `id_currency`, `id_country`, `is_mobile`, `controller`, `id_object`,
            `is_module`, `module_name`, `customer_groups`, `cache`, `cache_size`,
            `hits`, `misses`, `hit_time`, `miss_time`, `last_updated`)
            VALUES ("'.pSQL($id_sign).'", "'.pSQL($url).'", "'.pSQL($file_name).'", "'.(int)$id_shop.'", "'.(int)$id_language.'", 
            "'.(int)$id_currency.'", "'.(int)$id_country.'", "'.(int)$is_mobile.'", "'.pSQL($controller).'", "'.(int)$id_object.'",
            "'.(int)(Tools::getValue('fc') == 'module').'", "'.pSQL(Tools::getValue('module')).'", 
            "'.pSQL($customer_groups).'", "'.$cache.'", "'.$cache_size.'",
            "0", "1", "0", "'.$cache_time.'", "'.time().'")
            ON DUPLICATE KEY UPDATE `misses` = `misses` + 1,
            `last_updated` ="'.time().'",
            `file_name` = "'.$file_name.'", `cache` = "'.$cache.'", `cache_size` = "'.$cache_size.'",
            `miss_time` = ((`miss_time` * `misses`) + "'.$cache_time.'") / (`misses` + 1)'
        );
    }
    public function flushDb()
    {
        return Db::getInstance()->execute('TRUNCATE TABLE '._DB_PREFIX_.'st_advanced_cache');
    }
    public static function deleteCacheByController($controller, $id_object = null)
    {
        if ($controller) {
            Db::getInstance()->execute(
                'DELETE FROM '._DB_PREFIX_.'st_advanced_cache
                WHERE `controller` = "'.$controller.'"
                '.($id_object ? 'AND `id_object` = '.($id_object) : '')
            );
            Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'st_advanced_cache set `file_name` = "", `cache` = "", `cache_size` = 0 where `controller` = "index"');
            if ($controller == 'product') {
                $product = new Product((int)$id_object);
                if ($product) {
                    $categories = $product->getCategories();
                    foreach ($categories as $id_category) {
                        Db::getInstance()->execute(
                            'UPDATE '._DB_PREFIX_.'st_advanced_cache set `file_name` = "", `cache` = "", `cache_size` = 0 
                            where `controller` = "category" AND `id_object` = '.(int)$id_category
                        );
                    }
                }
            }
        }
    }
    public function getKey($id_sign)
    {
        $context        = Context::getContext();
        $currency = Tools::setCurrency($context->cookie);
        $id_shop        = (int)$context->shop->id;
        $id_langauge    = (int)$context->language->id;
        $id_currency    = (int)$currency->id;
        $id_country     = (int)$context->country->id;
        $is_mobile      = $this->module->isMobile();
        $is_module      = Tools::getValue('fc') == 'module';

        return $id_sign.$id_shop.$id_langauge.$id_currency.$id_country.$is_mobile.$is_module;
    }
    public function flush()
    {
        $this->flushDb();
        $this->flushFsCache();
    }
}

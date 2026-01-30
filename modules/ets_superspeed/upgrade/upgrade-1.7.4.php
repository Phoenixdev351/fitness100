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
/**
 * @param Ets_superspeed $module
 * @return bool
 */
function upgrade_module_1_7_4($module)
{
    $sqls = array();
    if(!$module->checkCreatedColumn('ets_superspeed_cache_page','date_expired'))
    {
        $sqls[] ='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_cache_page` ADD `date_expired` datetime NULL AFTER `date_add`';
        $pages = Ets_superspeed_defines::getInstance()->getPages();
        if($pages)
        {
            foreach($pages as $page)
            {
                if (($lifetime = (int)Configuration::get('ETS_SPEED_TIME_CACHE_' . Tools::strtoupper($page['id']),null,null,null)) && $lifetime != 31) {
                    $sqls[] ='UPDATE `'._DB_PREFIX_.'ets_superspeed_cache_page` SET `date_expired` = FROM_UNIXTIME(UNIX_TIMESTAMP(date_add) + 86400 * '.(int)$lifetime.', "%Y-%m-%d %H:%i:%s") WHERE page= "'.pSQL($page['id']).'"';
                }
            }
        }
    }
    $sqls[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_superspeed_cache_page_error` (
        `id_ets_superspeed_cache_page_error` int(11) NOT NULL AUTO_INCREMENT,
        `id_cache_page` int(11) NOT NULL,
        `page` varchar(50) NOT NULL,
        `id_object` int(11) NOT NULL,
        `id_product_attribute` INT(11) NOT NULL,
        `ip` varchar(40) NOT NULL,
        `request_uri` VARCHAR(256) NOT NULL,
        `file_cache`  VARCHAR(33),
        `id_shop` int(11) NOT NULL,
        `id_lang` int(11) NOT NULL,
        `id_currency` int(11) NOT NULL,
        `id_country` int(11) NOT NULL,
        `has_customer` int(1) NOT NULL,
        `has_cart` int(1) NOT NULL,
        `error` TEXT,
        `date_add` datetime NOT NULL, INDEX(file_cache),
        PRIMARY KEY (`id_ets_superspeed_cache_page_error`))  ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';
    if($sqls)
    {
        foreach($sqls as $sql)
            Db::getInstance()->execute($sql);
    }
    Ets_ss_class_cache::getInstance()->deleteCache();
    $module->uninstallOverrides();
    $module->installOverrides();
    return true;
}
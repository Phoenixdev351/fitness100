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

function upgrade_module_1_4_7()
{
    try{
        Ets_ss_class_cache::getInstance()->deleteCache();
        Configuration::deleteByName('ETS_SPEED_AUTO_CACHE');
        Configuration::deleteByName('ETS_SPEED_CACHE_TIME');
        $sqls = array();
        $sqls[] ='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_cache_page` CHANGE `file_cache` `file_cache` VARCHAR(33) NULL DEFAULT NULL';
        $sqls[] ='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_cache_page` CHANGE `request_uri` `request_uri` VARCHAR(256) NULL DEFAULT NULL';
        $sqls[] ='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_hook_time` CHANGE `page` `page` VARCHAR(256) NULL DEFAULT NULL';
        $sqls[] ='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_cache_page` DROP INDEX `index_cache_page`';
        $sqls[] ='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_cache_page` ADD INDEX (`date_add`, `page`, `id_object`, `id_product_attribute`, `ip`, `file_cache`, `id_shop`, `id_lang`, `id_currency`, `id_country`, `has_customer`, `has_cart`)';
        $sqls[] ='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_product_image_lang` DROP INDEX `index_ets_superspeed_product_image_lang`';
        $sqls[] ='ALTER TABLE `'._DB_PREFIX_.'ps_ets_superspeed_product_image_lang` ADD INDEX (`id_lang`)';
        $sqls[] ='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_cache_page` DROP `date_upd`';
        foreach($sqls as $sql)
        {
            Db::getInstance()->execute($sql);
        }
    }
    catch(Exception $ex){
        if($ex){
            //
        }
    }
    return true;
}
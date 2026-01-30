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
function upgrade_module_1_0_4($object)
{
    $sqls = array();
    if(!$object->checkCreatedColumn('ets_superspeed_cache_page','click'))
        $sqls[]='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_cache_page` ADD COLUMN `click` int(11) NOT NULL';
    if(!$object->checkCreatedColumn('ets_superspeed_cache_page','has_customer'))
        $sqls[]='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_cache_page` ADD COLUMN `has_customer` int(1) NOT NULL';
    if(!$object->checkCreatedColumn('ets_superspeed_cache_page','has_cart'))
        $sqls[]='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_cache_page` ADD COLUMN `has_cart` int(1) NOT NULL';
    $sqls[] ='CREATE TABLE IF NOT EXISTS  `'._DB_PREFIX_.'ets_superspeed_hook_time` ( 
        `id_module` INT(11) NOT NULL , 
        `hook_name` VARCHAR(111) NOT NULL , 
        `page` VARCHAR(1000) NOT NULL , 
        `id_shop` INT(11) NOT NULL,
        `date_add` datetime NOT NULL , 
        `time` float(10,4) NOT NULL , 
        PRIMARY KEY (`id_module`, `hook_name`,`id_shop`)) ENGINE = InnoDB';
    $sqls[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ets_superspeed_hook_module` ( 
        `id_module` INT(11) NOT NULL , 
        `id_shop` INT(11) NOT NULL , 
        `id_hook` INT(11) NOT NULL , 
        `position` INT(2) NOT NULL , 
        PRIMARY KEY (`id_module`, `id_shop`, `id_hook`)) ENGINE = InnoDB';
    $sqls[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ets_superspeed_upload_image` (
          `id_ets_superspeed_upload_image` int(22) NOT NULL AUTO_INCREMENT,
          `image_name` varchar(222) NOT NULL,
          `old_size` float(10,2) NOT NULL,
          `new_size` float(10,2) NOT NULL,
          `image_name_new` varchar(222) NOT NULL,
          `date_add` datetime NOT NULL,
           PRIMARY KEY (`id_ets_superspeed_upload_image`))  ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci';
    $sqls[] ='CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ets_superspeed_browse_image` (
          `id_ets_superspeed_browse_image` int(11) NOT NULL AUTO_INCREMENT,
          `image_name` varchar(222) NOT NULL,
          `image_dir` text,
          `image_id` text,
          `old_size` float(10,2) NOT NULL,
          `new_size` float(10,2) NOT NULL,
          `date_add` datetime NOT NULL,
        PRIMARY KEY (`id_ets_superspeed_browse_image`))  ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci';
    if($sqls)
        foreach($sqls as $sql)
            Db::getInstance()->execute($sql);
    $tabId = Tab::getIdFromClassName('AdminSuperSpeed');
    if($tabId)
    {
        $tab = new Tab();
        $tab->class_name = 'AdminSuperSpeedSystemAnalytics';
        $tab->module = $object->name;
        $tab->id_parent = $tabId; 
        $tab->icon='icon icon-analytics'; 
        $languages = Language::getLanguages(false);          
        foreach($languages as $lang){
                $tab->name[$lang['id_lang']] = 'System Analytics';
        }
        $tab->save();               
    } 
    $object->registerHook('displayImagesBrowse');
    $object->registerHook('displayImagesUploaded');
    $object->registerHook('displayImagesCleaner');
    Configuration::updateValue('ETS_SPEED_QUALITY_OPTIMIZE_UPLOAD',50);
    Configuration::updateValue('ETS_SPEED_OPTIMIZE_SCRIPT_UPLOAD','php');
    Configuration::updateValue('ETS_SPEED_QUALITY_OPTIMIZE_BROWSE',50);
    Configuration::updateValue('ETS_SPEED_OPTIMIZE_SCRIPT_BROWSE','php');
    if(Configuration::getGlobalValue('ETS_SPEED_OPTIMIZE_SCRIPT')=='google')
        Configuration::updateValue('ETS_SPEED_OPTIMIZE_SCRIPT','php');
    return true;
}
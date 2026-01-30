<?php
/**
* DISCLAIMER
*
* Do not edit or add to this file.
* You are not authorized to modify, copy or redistribute this file.
* Permissions are reserved by FME Modules.
*
*  @author    FMM Modules
*  @copyright FME Modules 2021
*  @license   Single domain
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_0_0($module)
{
    Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'affiliate_banners(
        `id_affiliate_banners` int(11) unsigned NOT NULL auto_increment,
        `title` varchar(255),
        `path_url` varchar(255),
        `href` varchar(255),
        `active` tinyint(1) default 1,
        PRIMARY KEY (`id_affiliate_banners`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8');
    Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'affiliate_levels_categories` (
        `id_level` int(10) NOT NULL,
        `id_category` int(10) NOT NULL,
        `value` DECIMAL(20,6),
        PRIMARY KEY (`id_level`, `id_category`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;');
    Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'affiliate_levels_products` (
        `id_level` int(10) NOT NULL,
        `id_product` int(10) NOT NULL,
        `value` DECIMAL(20,6),
        PRIMARY KEY (`id_level`, `id_product`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;');
    if (columnExist('value_type', 'affiliate_levels')) {
        return true;
    } else {
        Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'affiliate_levels`
            ADD `value_type` INT(10) NOT NULL DEFAULT 0');
    }
    if (columnExist('level', 'affiliate_levels')) {
        return true;
    } else {
        Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'affiliate_levels`
            ADD `level` tinyint(1) DEFAULT 1');
    }
    if (columnExist('parent_reward', 'affiliate_levels')) {
        return true;
    } else {
        Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'affiliate_levels`
            ADD `parent_reward` int(11) unsigned NOT NULL default 0');
    }
    if (columnExist('individual_voucher', 'affiliate')) {
        return true;
    } else {
        Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'affiliate`
            ADD `individual_voucher` DECIMAL(20,6) NOT NULL DEFAULT 0');
    }
    if (columnExist('level', 'affiliate')) {
        return true;
    } else {
        Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'affiliate`
            ADD `level` tinyint(1) DEFAULT 1');
    }
    if (columnExist('id_voucher', 'affiliate')) {
        return true;
    } else {
        Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'affiliate`
            ADD `id_voucher` unsigned NOT NULL DEFAULT 0');
    }
    if (columnExist('affiliate_level', 'affiliate_rules')) {
        return true;
    } else {
        Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'affiliate_rules`
            ADD `affiliate_level` tinyint(1) DEFAULT 1');
    }
    if (columnExist('parent_reward_value', 'affiliate_rules')) {
        return true;
    } else {
        Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'affiliate_rules`
            ADD `parent_reward_value` int(10) NOT NULL');
    }
    $id = (int)Tab::getIdFromClassName('AdminAffiliation');
    $tab_003 = new Tab();
    $tab_003->class_name = 'AdminAffiliatesConversion';
    $tab_003->id_parent = $id;
    $tab_003->module = 'affiliates';
    $tab_003->name[(int)Configuration::get('PS_LANG_DEFAULT')] = $module->l('Convert Affiliates');
    $tab_003->add();
    $subtab001 = new Tab();
    $subtab001->class_name = 'AdminAffiliationBanners';
    $subtab001->id_parent = $id;
    $subtab001->module = 'affiliates';
    $subtab001->name[(int)Configuration::get('PS_LANG_DEFAULT')] = $module->l('Banners');
    $subtab001->add();
    $subtab002 = new Tab();
    $subtab002->class_name = 'AdminAffiliationDiscounts';
    $subtab002->id_parent = $id;
    $subtab002->module = 'affiliates';
    $subtab002->name[(int)Configuration::get('PS_LANG_DEFAULT')] = $module->l('Discounts');
    $subtab002->add();
    $tab_002 = new Tab();
    $tab_002->class_name = 'AdminAffiliatesConversion';
    $tab_002->id_parent = $id;
    $tab_002->module = 'affiliates';
    $tab_002->name[(int)Configuration::get('PS_LANG_DEFAULT')] = $module->l('Convert Affiliates');
    $tab_002->add();
    @mkdir(_PS_IMG_DIR_.'uploads', 0777, true);
    @mkdir(_PS_IMG_DIR_.'uploads'.DIRECTORY_SEPARATOR.'affiliates', 0777, true);
    return true;
}

function columnExist($column_name, $table)
{
    $columns = Db::getInstance()->ExecuteS('SELECT COLUMN_NAME FROM information_schema.columns
        WHERE table_schema = "'._DB_NAME_.'" AND table_name = "'._DB_PREFIX_.$table.'"');
    if (isset($columns) && $columns) {
        foreach ($columns as $column) {
            if ($column['COLUMN_NAME'] == $column_name) {
                return true;
            }
        }
    }
    return false;
}

<?php
/**
 * Facebook Conversion Pixel Tracking Plus
 *
 * NOTICE OF LICENSE
 *
 * @author    Pol Rué
 * @copyright Smart Modules 2014
 * @license   One time purchase Licence (You can modify or resell the product but just one time per licence)
 * @version 2.3.3
 * @category Marketing & Advertising
 * Registered Trademark & Property of smart-modules.com
 *
 * ***************************************************
 * *     Facebook Conversion Trackcing Pixel Plus    *
 * *          http://www.smart-modules.com           *
 * *                     V 2.3.3                     *
 * ***************************************************
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_1_5()
{
    // Create the category Table, will be the same used on Products Feed to share it's contents in case both modules are installed
    $sql = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'fpf_cat` (
        `id_category` int(11) NOT NULL,
        `id_shop` int(11) NOT NULL,
        `google_taxonomy_id` int(4),
        `age_group` CHAR(8) DEFAULT \'adult\',
        `gender` CHAR(6) DEFAULT \'unisex\',
        `excluded` TINYINT(1) DEFAULT 0,
        UNIQUE KEY `id_category` (`id_category`,`id_shop`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
    Db::getInstance()->execute($sql);
    return true;
}

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

$sql = array();
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'facebookpixels` (
    `id_facebookpixels` int(11) NOT NULL AUTO_INCREMENT,
     `pixel_active` int(1) NOT NULL,
     `pixel_name` text NULL,
     `pixel_type` int(2) NOT NULL,
     `pixel_extras` text(255) NULL,
     `pixel_extras_type` int(2) NULL,
     `pixel_extras_name` text(255) NULL,
    PRIMARY KEY  (`id_facebookpixels`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

// Create the category Table, will be the same used on Products Feed to share it's contents in case both modules are installed
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'fpf_cat` (
    `id_category` int(11) NOT NULL,
    `id_shop` int(11) NOT NULL,
    `google_taxonomy_id` int(4),
    `age_group` CHAR(8) DEFAULT \'adult\',
    `gender` CHAR(6) DEFAULT \'unisex\',
    `excluded` TINYINT(1) DEFAULT 0,
    UNIQUE KEY `id_category` (`id_category`,`id_shop`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}

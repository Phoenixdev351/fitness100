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
function upgrade_module_1_0_3($object)
{
    $sqls = array();
    $sqls[] ='CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ets_superspeed_blog_post_image` (
          `id_post` int(11) NOT NULL,
          `type_image` varchar(64) NOT NULL,
          `quality` int(11) NOT NULL,
          `size_old` float(10,2),
          `size_new` float(10,2),
          `optimize_type` VARCHAR(8),
          PRIMARY KEY( `id_post`, `type_image`)
        )  ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci';
    $sqls[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ets_superspeed_blog_category_image` (
          `id_category` int(11) NOT NULL,
          `type_image` varchar(64) NOT NULL,
          `quality` int(11) NOT NULL,
          `size_old` float(10,2),
          `size_new` float(10,2),
          `optimize_type` VARCHAR(8),
          PRIMARY KEY( `id_category`, `type_image`)
        )  ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci';
    $sqls[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ets_superspeed_blog_gallery_image` (
          `id_gallery` int(11) NOT NULL,
          `type_image` varchar(64) NOT NULL,
          `quality` int(11) NOT NULL,
          `size_old` float(10,2),
          `size_new` float(10,2),
          `optimize_type` VARCHAR(8),
          PRIMARY KEY( `id_gallery`, `type_image`)
        )  ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci';
    $sqls[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ets_superspeed_blog_slide_image` (
          `id_slide` int(11) NOT NULL,
          `type_image` varchar(64) NOT NULL,
          `quality` int(11) NOT NULL,
          `size_old` float(10,2),
          `size_new` float(10,2),
          `optimize_type` VARCHAR(8),
          PRIMARY KEY( `id_slide`, `type_image`)
        )  ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci';
    $sqls[]='CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ets_superspeed_home_slide_image` (
          `id_homeslider_slides` int(11) NOT NULL,
          `image` varchar(165) NOT NULL,
          `type_image` varchar(64) NOT NULL,
          `quality` int(11) NOT NULL,
          `size_old` float(10,2),
          `size_new` float(10,2),
          `optimize_type` VARCHAR(8),
          PRIMARY KEY( `id_homeslider_slides`, `type_image`,`image`)
        )  ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci';
    $sqls[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'ets_superspeed_others_image`(
          `image` varchar(165) NOT NULL,
          `type_image` varchar(64) NOT NULL,
          `quality` int(11) NOT NULL,
          `size_old` float(10,2),
          `size_new` float(10,2),
          `optimize_type` VARCHAR(8),
          PRIMARY KEY(`type_image`,`image`)
        )  ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci';
    if($sqls)
        foreach($sqls as $sql)
            Db::getInstance()->execute($sql);
    $object->registerHook('actionUpdateBlog');
    $object->registerHook('actionUpdateBlogImage');
    return true;
}
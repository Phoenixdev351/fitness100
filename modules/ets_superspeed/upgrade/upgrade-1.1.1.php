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
 * @param Ets_superspeed $object
 * @return bool
 */
function upgrade_module_1_1_1($object)
{
    $sqls = array();
    if(!$object->checkCreatedColumn('ets_superspeed_blog_slide_image','image'))
    {
        $sqls[]='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_blog_slide_image` ADD `image` VARCHAR(222) NOT NULL AFTER `type_image`';
        $sqls[] ='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_blog_slide_image` DROP PRIMARY KEY, ADD PRIMARY KEY (`id_slide`, `type_image`, `image`) USING BTREE';
    }
    if(!$object->checkCreatedColumn('ets_superspeed_blog_gallery_image','image'))
    {
        $sqls[]='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_blog_gallery_image` ADD `image` VARCHAR(222) NOT NULL AFTER `type_image`, ADD `thumb` VARCHAR(222) NOT NULL AFTER `image`';
        $sqls[] ='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_blog_gallery_image` DROP PRIMARY KEY, ADD PRIMARY KEY (`id_gallery`, `type_image`, `image`,`thumb`) USING BTREE';
    }
    if(!$object->checkCreatedColumn('ets_superspeed_blog_category_image','image'))
    {
        $sqls[]='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_blog_category_image` ADD `image` VARCHAR(222) NOT NULL AFTER `type_image`, ADD `thumb` VARCHAR(222) NOT NULL AFTER `image`';
        $sqls[] ='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_blog_category_image` DROP PRIMARY KEY, ADD PRIMARY KEY (`id_category`, `type_image`, `image`,`thumb`) USING BTREE';
    }    
    if(!$object->checkCreatedColumn('ets_superspeed_blog_post_image','image'))
    {
        $sqls[] ='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_blog_post_image` ADD `image` VARCHAR(222) NOT NULL AFTER `type_image`, ADD `thumb` VARCHAR(222) NOT NULL AFTER `image`';
        $sqls[] ='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_blog_post_image` DROP PRIMARY KEY, ADD PRIMARY KEY (`id_post`, `type_image`, `image`,`thumb`) USING BTREE';
    }    
    if($sqls)
    {
        foreach($sqls as $sql)
            Db::getInstance()->execute($sql);
    }
    Configuration::updateValue('ETS_TIME_AJAX_CHECK_SPEED',5);
    return true;
}
<?php

if (!defined('_PS_VERSION_'))
	exit;

    function upgrade_module_1_0_9($object)
    {
        $result = Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'st_instagram_img` MODIFY thumbnail_url TEXT');
        $result &= Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'st_instagram_img` MODIFY media_url TEXT');
        $result &= Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'st_instagram_img` MODIFY permalink TEXT');
        $result &= Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'st_instagram_img` MODIFY inst_time VARCHAR(32)');
        $result &= Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'st_instagram_img` MODIFY username VARCHAR(32)');
        $result &= Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'st_instagram_img` MODIFY caption TEXT');
        $result &= Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'st_instagram_img` ADD `id_shop` int(11) unsigned NOT NULL DEFAULT 0');
        $result &= Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'st_instagram_img` set `id_shop`='.(int)Context::getContext()->shop->id);
        return $result;
    }
<?php

if (!defined('_PS_VERSION_'))
	exit;

    function upgrade_module_1_0_8($object)
    {
        $result = Db::getInstance()->Execute('
                                CREATE TABLE `'. _DB_PREFIX_ .'st_instagram_img` (
                                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                                    `instagram_id` BIGINT(30) NOT NULL,
                                    `media_type` VARCHAR(60) DEFAULT NULL,
                                    `thumbnail_url` VARCHAR(500) DEFAULT NULL,
                                    `media_url` VARCHAR(500) DEFAULT NULL,
                                    `permalink` VARCHAR(120) DEFAULT NULL,
                                    `inst_time` VARCHAR(255) DEFAULT NULL,
                                    `username` VARCHAR(255) DEFAULT NULL,
                                    `caption` VARCHAR(500) DEFAULT NULL,
                                    `ctime` INT(11) DEFAULT NULL,
                                    `utime` INT(11) DEFAULT NULL,
                                    PRIMARY KEY (`id`),
                                    UNIQUE KEY `NewIndex1` (`instagram_id`),
                                    FULLTEXT KEY `NewIndex2` (`caption`)
                                ) ENGINE='._MYSQL_ENGINE_.' AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
                            ');
        $result &= Configuration::updateValue('ST_INSTAGRAM_CACHE_IMG_DATE', 24*60);
        $result &= Configuration::updateValue('ST_INSTAGRAM_CACHE_IMG_DATE_LEN', time()+24*60*60);
        $result &=Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'st_instagram` MODIFY user_id VARCHAR(32)');
        return $result;
    }
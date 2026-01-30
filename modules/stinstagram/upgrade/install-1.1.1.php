<?php

if (!defined('_PS_VERSION_'))
	exit;

    function upgrade_module_1_1_1($object)
    {
        $result = Db::getInstance()->execute('ALTER TABLE ps_st_instagram_img CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
        $result &= Db::getInstance()->execute('ALTER TABLE ps_st_instagram_img MODIFY caption TEXT CHARSET utf8mb4;');

        return (bool)$result;
    }
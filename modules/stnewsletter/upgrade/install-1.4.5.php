<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_4_5($object)
{
	$field = Db::getInstance()->executeS('Describe `'._DB_PREFIX_.'emailsubscription` `voucher_sent`');
    if(!is_array($field) || !count($field)) {
        return Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'emailsubscription` ADD `voucher_sent` tinyint(1) unsigned NOT NULL DEFAULT 0');    
    }
    return true;
}

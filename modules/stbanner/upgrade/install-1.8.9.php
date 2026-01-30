<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_8_9($object)
{
	$result = true;
    
    $field = Db::getInstance()->executeS('Describe `'._DB_PREFIX_.'st_banner_group` `lazy_loading`');  
      
    if(!is_array($field) || !count($field)) {
        if (!Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'st_banner_group` ADD `lazy_loading` tinyint(1) unsigned NOT NULL DEFAULT 1')) {
            $result &= false;
        }
    }
        
    return $result;
}

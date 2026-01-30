<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_0_3($object)
{
    $result = true;
    
    $field = Db::getInstance()->executeS('Describe `'._DB_PREFIX_.'st_social` `st_social`');  
      
    if(!is_array($field) || !count($field)) {
        if (!Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'st_social` ADD `new_window` tinyint(1) unsigned NOT NULL DEFAULT 1')) {
            $result &= false;
        }
    }
    
    return $result;
}

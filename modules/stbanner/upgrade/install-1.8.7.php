<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_8_7($object)
{
	$result = true;
    
    $field = Db::getInstance()->executeS('Describe `'._DB_PREFIX_.'st_banner_group` `width_md`');  
      
    if(!is_array($field) || !count($field))
        if (!Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'st_banner_group` ADD `width_md` tinyint(2) unsigned NOT NULL DEFAULT 0'))
            $result &= false;
            
    $field = Db::getInstance()->executeS('Describe `'._DB_PREFIX_.'st_banner_group` `width_xs`');  
      
    if(is_array($field) && count($field))
        return $result;

    if (!Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'st_banner_group` ADD `width_xs` tinyint(2) unsigned NOT NULL DEFAULT 12'))
            $result &= false;
        
    return $result;
}

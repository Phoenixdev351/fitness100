<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_8_8($object)
{
	$result = true;
    
    $field = Db::getInstance()->executeS('Describe `'._DB_PREFIX_.'st_banner_group` `display_on`');  
      
    if(!is_array($field) || !count($field)) {
        if (!Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'st_banner_group` ADD `display_on` int(10) unsigned NOT NULL DEFAULT 0')) {
            $result &= false;
        } else {
          $result &= Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'st_banner_group` set `display_on` = 1 WHERE `location` IN(23,26,28,29,24,27,60,61) AND `id_parent` = 0');
        }
    }
        
    return $result;
}

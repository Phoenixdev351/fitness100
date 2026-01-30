<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_0_2($object)
{
    $result = true;
    
    $field = Db::getInstance()->executeS('Describe `'._DB_PREFIX_.'st_social_lang` `description`');  
      
    if(!is_array($field) || !count($field)) {
        if (!Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'st_social_lang` ADD `description` varchar(255) DEFAULT NULL')) {
            $result &= false;
        }
    }
    
    return $result;
}

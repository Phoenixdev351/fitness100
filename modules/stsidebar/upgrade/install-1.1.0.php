<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_1_0($object)
{
    $result = true;
    
    $field = Db::getInstance()->executeS('Describe `'._DB_PREFIX_.'st_sidebar_lang` `url`');  
   
    if(is_array($field) && count($field))
        return $result;

    if (!Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'st_sidebar_lang` 
        ADD `url` varchar(255) DEFAULT NULL'
        ))
        $result &= false;
    
    return $result;
}

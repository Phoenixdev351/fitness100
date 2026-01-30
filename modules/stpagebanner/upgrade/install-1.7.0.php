<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_7_0($object)
{
	$result = true;
    
    $field = Db::getInstance()->executeS('Describe `'._DB_PREFIX_.'st_page_banner` `show_subcate`');  
      
    if(!is_array($field) || !count($field))
        if (!Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'st_page_banner` ADD `show_subcate` tinyint(1) unsigned NOT NULL DEFAULT 0'))
            $result &= false;
        
    return $result;
}

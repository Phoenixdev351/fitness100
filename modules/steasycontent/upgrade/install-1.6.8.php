<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_6_8($object)
{
	$result = true;
    
    $field = Db::getInstance()->executeS('Describe `'._DB_PREFIX_.'st_easy_content_lang` `name`');  
      
    if(!is_array($field) || !count($field))
        if (!Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'st_easy_content_lang` ADD `name` varchar(255) DEFAULT NULL after `title`'))
            $result &= false;
        
    return $result;
}

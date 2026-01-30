<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_7_0($object)
{
	$result = true;
    
    $result &= $object->registerHook('displayAdminProductsExtra');
    $result &= $object->registerHook('actionProductSave');

    $field = Db::getInstance()->executeS('Describe `'._DB_PREFIX_.'st_easy_content` `bo_tab`');  
      
    if(!is_array($field) || !count($field))
        if (!Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'st_easy_content` ADD `bo_tab` tinyint(1) unsigned NOT NULL DEFAULT 0'))
            $result &= false;
        
    return $result;
}

<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_4_2($object)
{
    $result = true;
            
    $field = Db::getInstance()->executeS('Describe `'._DB_PREFIX_.'emailsubscription` `id_gender`');  
   
    if(!is_array($field) || !count($field))
        if (!Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'emailsubscription` 
            ADD `id_gender` int(10) unsigned NOT NULL DEFAULT 0'))
            $result &= false;

	return $result;
}

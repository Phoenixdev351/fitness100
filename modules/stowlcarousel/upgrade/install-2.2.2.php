<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_2_2_2($object)
{
	$result = true;
    
    $field = Db::getInstance()->executeS('Describe `'._DB_PREFIX_.'st_owl_carousel_lang` `image_multi_lang_sm`');  
      
    if(!is_array($field) || !count($field)) {
        if (!Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'st_owl_carousel_lang` 
            ADD `image_multi_lang_sm` varchar(255) DEFAULT NULL,
            ADD `image_multi_lang_xs` varchar(255) DEFAULT NULL,
            ADD `image_lang_default_sm` varchar(255) DEFAULT NULL,
            ADD `image_lang_default_xs` varchar(255) DEFAULT NULL,
            ADD `width_sm` int(10) unsigned NOT NULL DEFAULT 0,
            ADD `width_xs` int(10) unsigned NOT NULL DEFAULT 0,
            ADD `height_sm` int(10) unsigned NOT NULL DEFAULT 0,
            ADD `height_xs` int(10) unsigned NOT NULL DEFAULT 0
        	')) {
            $result &= false;
        }
    }
        
    return $result;
}

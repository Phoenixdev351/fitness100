<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_1_1($object)
{
    $result = true;
    
    $field = Db::getInstance()->executeS('Describe `'._DB_PREFIX_.'st_sticker_map` `id_products`');  
   
    if(is_array($field) && count($field)) {
        if (!Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'st_sticker_map` MODIFY id_products text DEFAULT NULL')) {
            $result &= false;
        }
    }
    return $result;
}

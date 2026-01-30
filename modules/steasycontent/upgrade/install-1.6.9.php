<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_6_9($object)
{
	$result = true;
    
    if (!Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'st_easy_content` MODIFY id_product VARCHAR(255) DEFAULT NULL'))
            $result &= false;
        
    return $result;
}

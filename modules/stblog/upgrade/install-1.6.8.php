<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_6_8($object)
{
    $result = true;
    $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
    Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'st_blog_image_lang` 
        WHERE id_lang != '.(int)$default_lang.'
        ');
    
    return $result;
}

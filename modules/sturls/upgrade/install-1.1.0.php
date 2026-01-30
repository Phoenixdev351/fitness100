<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_1_0($object)
{
    $result = true;
    
    foreach(array('product','category','manufacturer','supplier','cms_category','cms','st_blog','st_blog_category') as $v) {
        Configuration::updateValue($object->_prefix_st.strtoupper('page_'.$v), 1);
    }
    
    return $result;
}

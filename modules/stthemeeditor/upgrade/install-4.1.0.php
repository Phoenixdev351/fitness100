<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_4_1_0($object)
{
    $result = true;

    $result &= Configuration::updateGlobalValue('STSN_HIDE_GENDER', 0);
    $result &= Configuration::updateGlobalValue('STSN_CHECKOUT_SAME_HEADER', 0);
    $result &= Configuration::updateGlobalValue('STSN_CHECKOUT_SAME_FOOTER', 0);
    $result &= Configuration::updateGlobalValue('STSN_MAIN_MENU_SPACING_LG', 0);
    $result &= Configuration::updateGlobalValue('STSN_MAIN_MENU_SPACING_MD', 0);
    $result &= Configuration::updateGlobalValue('STSN_CMS_H1_SIZE', 0);
    $result &= Configuration::updateGlobalValue('STSN_CMS_H2_SIZE', 0);
    $result &= Configuration::updateGlobalValue('STSN_CMS_H3_SIZE', 0);
    $result &= Configuration::updateGlobalValue('STSN_MOBILE_LOGO', '');
    $result &= Configuration::updateGlobalValue('STSN_MOBILE_LOGO_WIDTH', 0);
    $result &= Configuration::updateGlobalValue('STSN_MOBILE_LOGO_HEIGHT', 0);
    
    $module_list = array(
        'stbanner',
        'stbestsellers',
        'stfeaturedslider',
        'sthomenew',
        'stviewedproducts',
        'stblogfeaturedarticles',
        'stblogrecentarticles',
        'stbrandsslider',
        'stfblikebox',
        'steasycontent',
        'stfeaturedcategories',
        'stinstagram',
        'stnewsletter',
        'stowlcarousel',
        'stproductcomments',
        'stswiper',
    );
    foreach($module_list AS $name) {
        $module = Module::getInstanceByName($name);
        $module->registerHook('vcBeforeInit');
    }
    
	return $result;
}

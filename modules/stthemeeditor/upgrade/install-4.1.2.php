<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_4_1_2($object)
{
    $result = true;

    $result &= Configuration::updateGlobalValue('STSN_TOP_EXTRA_BG_COLOR', '');
    $result &= Configuration::updateGlobalValue('STSN_TOP_EXTRA_TOP_SPACING', '');
    $result &= Configuration::updateGlobalValue('STSN_TOP_EXTRA_BOTTOM_SPACING', '');
    $result &= Configuration::updateGlobalValue('STSN_TOP_EXTRA_BOTTOM_BORDER_COLOR', '');
    $result &= Configuration::updateGlobalValue('STSN_TOP_EXTRA_BOTTOM_BORDER', 0);
    $result &= Configuration::updateValue('jscomposer_status', '1');

    $module_list = array(
        'stproductcategoriesslider',
        'stspecialslider',
        'sttags',
        'sttwitterembeddedtimelines',
        'stmultilink',
    );
    foreach($module_list AS $name) {
        $module = Module::getInstanceByName($name);
        $module->registerHook('vcBeforeInit');
    }
    
    // Move CategoryController.php to listing folder
    $listing = _PS_OVERRIDE_DIR_.'controllers/front/listing/';
    if (!file_exists($listing)) {
        mkdir($listing, 0777);
        @copy(_PS_OVERRIDE_DIR_.'controllers/front/CategoryController.php', $listing);
        @unlink(_PS_OVERRIDE_DIR_.'controllers/front/CategoryController.php');
    }
    foreach(Shop::getCompleteListOfShopsID() AS $id_shop)
    {
        $cssFile = _PS_MODULE_DIR_.'stthemeeditor/views/css/customer-s'.(int)$id_shop.'.css';
        @unlink($cssFile);    
    }
    
	return $result;
}

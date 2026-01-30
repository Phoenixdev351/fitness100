<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_4_1_4($object)
{
    $result = true;

    $result &= Configuration::updateGlobalValue('STSN_PRO_AVAILABLE_COLOR', '');
    $result &= Configuration::updateGlobalValue('STSN_PRO_UNAVAILABLE_COLOR', '');
    $result &= Configuration::updateGlobalValue('STSN_PRO_LAST_ITEMS', '');
    $result &= Configuration::updateGlobalValue('STSN_FONT_MAIN_PRICE_SIZE', 0);

    // Remove CategoryController.php in stoverride
    $file = _PS_MODULE_DIR_.'stoverride/override/controllers/front/listing/CategoryController.php';
    if (file_exists($file)) {
        @unlink(_PS_MODULE_DIR_.'stoverride/override/controllers/front/CategoryController.php');
    }
    
	return $result;
}

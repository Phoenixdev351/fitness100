<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_4_3_6($object)
{
    $result = true;

    $result &= Configuration::updateGlobalValue('STSN_WISHLIST_ICON', '59616');
    $result &= Configuration::updateGlobalValue('STSN_LOVE_ICON', '59618');
    $result &= Configuration::updateGlobalValue('STSN_COMPARE_ICON', '59422');
    $result &= Configuration::updateGlobalValue('STSN_VIEWED_ICON', '59514');
    
	return $result;
}

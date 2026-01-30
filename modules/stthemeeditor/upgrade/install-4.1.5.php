<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_4_1_5($object)
{
    $result = true;

    $result &= Configuration::updateGlobalValue('STSN_BTN_FONT_SIZE', 0);
    $result &= Configuration::updateGlobalValue('STSN_FLYOUT_FONT_SIZE', 0);
    $result &= Configuration::updateGlobalValue('STSN_DROP_DOWN', 0);
    $result &= Configuration::updateGlobalValue('STSN_BUY_NOW', 0);
    
	return $result;
}

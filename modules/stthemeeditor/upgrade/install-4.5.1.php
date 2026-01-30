<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_4_5_1($object)
{
    $result = true;

    $result &= Configuration::updateValue('STSN_BTN_HOVER_BORDER_COLOR', Configuration::get('STSN_BTN_HOVER_BG_COLOR'));
    $result &= Configuration::updateValue('STSN_PRIMARY_BTN_HOVER_BORDER_COLOR', Configuration::get('STSN_PRIMARY_BTN_HOVER_BG_COLOR'));
    $result &= Configuration::updateValue('STSN_SIDE_PANEL_HEADING_BORDER', Configuration::get('STSN_SIDE_PANEL_HEADING_BG'));
    $result &= Configuration::updateValue('STSN_RESPONSIVE', 1);
    $result &= Configuration::updateValue('STSN_HIDE_HEADER', 0);
    
	return $result;
}

<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_4_2_7($object)
{
    $result = true;

    $result &= Configuration::updateGlobalValue('STSN_ICON_IPHONE_16', 'img/favicon-16.png');
    $result &= Configuration::updateGlobalValue('STSN_ICON_IPHONE_32', 'img/favicon-32.png');
    $result &= Configuration::updateGlobalValue('STSN_ICON_IPHONE_150', 'img/favicon-150.png');
    $result &= Configuration::updateGlobalValue('STSN_ICON_IPHONE_180', 'img/favicon-180.png');
    $result &= Configuration::updateGlobalValue('STSN_ICON_IPHONE_192', 'img/favicon-192.png');
    $result &= Configuration::updateGlobalValue('STSN_ICON_IPHONE_512', 'img/favicon-512.png');
    $result &= Configuration::updateGlobalValue('STSN_ICON_IPHONE_SVG', 'img/favicon-svg.svg');
    $result &= Configuration::updateGlobalValue('STSN_FAVICON_SVG_COLOR', '#222222');

	return $result;
}

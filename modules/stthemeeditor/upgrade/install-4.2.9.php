<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_4_2_9($object)
{
    $result = true;

    $result &= Configuration::updateGlobalValue('STSN_BRAND_PAGE_IMAGE', 1);
    $result &= Configuration::updateGlobalValue('STSN_BRAND_PAGE_SHORT_DESC', 0);
    $result &= Configuration::updateGlobalValue('STSN_BRAND_PAGE_DESC', 0);
    $result &= Configuration::updateGlobalValue('STSN_SUPPLIER_PAGE_IMAGE', 1);
    $result &= Configuration::updateGlobalValue('STSN_SUPPLIER_PAGE_DESC', 0);

	return $result;
}

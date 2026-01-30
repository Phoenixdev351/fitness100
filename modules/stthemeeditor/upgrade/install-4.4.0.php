<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_4_4_0($object)
{
    $result = true;

    $result &= Configuration::updateGlobalValue('STSN_CLEAR_LIST_VIEW', 18);
    
	return $result;
}

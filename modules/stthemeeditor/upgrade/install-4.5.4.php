<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_4_5_4($object)
{
    $result = true;

    $result &= Configuration::updateValue('STSN_ENABLE_NUMBER_PER_PAGE', 0);
    $result &= Configuration::updateValue('STSN_NUMBER_PER_PAGE', '20,40,60,10000');
    
	return $result;
}

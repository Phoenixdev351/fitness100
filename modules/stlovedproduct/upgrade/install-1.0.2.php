<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_0_2($object)
{
	$result = true;
    $result &= $object->registerHook('actionAuthentication');
        
    return $result;
}

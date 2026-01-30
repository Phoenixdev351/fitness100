<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_0_5($object)
{
    $result = true;
    
    $result &= $object->registerHook('actionOutputHTMLBefore');
    
    return $result;
}

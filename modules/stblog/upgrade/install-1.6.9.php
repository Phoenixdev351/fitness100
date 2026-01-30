<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_6_9($object)
{
    $result = $object->registerHook('gSitemapAppendUrls');
    
    return $result;
}

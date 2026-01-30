<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_4_4($object)
{
	return $object->registerHook('registerGDPRConsent') &&
            $object->registerHook('actionDeleteGDPRCustomer') &&
            $object->registerHook('actionExportGDPRData');
}

<?php

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_1_0_7($object)
{
    Configuration::updateValue('ST_INSTAGRAM_CLIENT_ID', '252820955722250');
    Configuration::updateValue('ST_INSTAGRAM_ACCESS_TOKEN', '');
    return true;
}

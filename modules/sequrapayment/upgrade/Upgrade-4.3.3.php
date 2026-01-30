<?php
if (!defined('_PS_VERSION_')) {
    exit;
}
function upgrade_module_4_3_3($module)
{
    $res = $module->registerHook('displayAdminOrder');
    return $res;
}

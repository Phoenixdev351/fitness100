<?php
if (!defined('_PS_VERSION_')) {
    exit;
}
function upgrade_module_4_1_0($module)
{
    $module->registerHook('actionOrderStatusPostUpdate');
    $module->registerHook('postUpdateOrderStatus');
    return true;
}
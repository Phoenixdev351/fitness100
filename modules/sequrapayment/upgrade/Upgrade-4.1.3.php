<?php
if (!defined('_PS_VERSION_')) {
    exit;
}
function upgrade_module_4_1_3($module)
{
    $orderState       = new OrderState(Configuration::get('SEQURA_OS_SENT'));
    $orderState->shipped = true;
    $orderState->paid = true;
    $orderState->save();

    $orderState       = new OrderState(Configuration::get('SEQURA_OS_CONFIRMED'));
    $orderState->paid = true;
    $orderState->save();

    return true;
}
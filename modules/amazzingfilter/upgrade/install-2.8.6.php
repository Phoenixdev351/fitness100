<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_8_6($module_obj)
{
    if (!defined('_PS_VERSION_')) {
        exit;
    }
    $all_shop_ids = Shop::getShops(false, null, true);
    $module_obj->saveSettings('caching', [], $all_shop_ids);
    $module_obj->registerHook('actionObjectAddAfter');
    $module_obj->registerHook('actionObjectDeleteAfter');
    $module_obj->registerHook('actionObjectUpdateAfter');

    return true;
}

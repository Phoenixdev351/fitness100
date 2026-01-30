<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_2_0($module_obj)
{
    if (!defined('_PS_VERSION_')) {
        exit;
    }
    $module_obj->registerHook('actionAdminTagsControllerSaveAfter');
    $module_obj->registerHook('actionAdminTagsControllerDeleteBefore');
    $module_obj->registerHook('actionAdminTagsControllerDeleteAfter');

    return true;
}

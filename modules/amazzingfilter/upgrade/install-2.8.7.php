<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_8_7($module_obj)
{
    if (!defined('_PS_VERSION_')) {
        exit;
    }
    $module_obj->cache('clear', 'c_list');

    return true;
}

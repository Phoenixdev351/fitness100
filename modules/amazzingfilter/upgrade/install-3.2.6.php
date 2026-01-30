<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_3_2_6($module_obj)
{
    // Media::clearCache(); // cleared in 3.2.7
    $module_obj->log('add', 'auto-upgrade applied for v3.2.6');

    return true;
}

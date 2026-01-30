<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_3_1_6($module_obj)
{
    if (!defined('_PS_VERSION_')) {
        exit;
    }
    // Media::clearCache(); // cleared in 3.1.7
    $file_to_remove = _PS_MODULE_DIR_ . $module_obj->name . '/views/js/attribute-indexer.js';
    if (file_exists($file_to_remove)) {
        unlink($file_to_remove);
    }

    return true;
}

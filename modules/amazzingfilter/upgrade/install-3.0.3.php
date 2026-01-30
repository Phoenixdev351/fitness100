<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_3_0_3($module_obj)
{
    if (!defined('_PS_VERSION_')) {
        exit;
    }
    // Media::clearCache(); // cleared in 3.1.0
    foreach ($module_obj->shopIDs('all') as $id_shop) {
        $all_saved_settings = $module_obj->getSavedSettings($id_shop);
        foreach ($module_obj->getSettingsKeys() as $type) {
            $settings = isset($all_saved_settings[$type]) ? $all_saved_settings[$type] : [];
            $module_obj->saveSettings($type, $settings, [$id_shop]);
        }
    }

    return true;
}

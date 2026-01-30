<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_3_0_0($module_obj)
{
    if (!defined('_PS_VERSION_')) {
        exit;
    }
    // Media::clearCache(); // cleared in 3.0.3
    $module_obj->installation_process = true;
    $move_settings = [
        'subcat_products' => ['from' => 'general', 'to' => 'indexation', 'key' => 'subcat_products'],
        'load_icons' => ['from' => 'general', 'to' => 'iconclass', 'key' => 'load_font'],
        'include_sorting' => ['from' => 'general', 'to' => 'general', 'key' => 'url_sorting'],
    ];
    $update_settings = [
        'general' => ['compact_btn' => 3],
    ];
    foreach ($module_obj->shopIDs('all') as $id_shop) {
        $all_saved_settings = $module_obj->getSavedSettings($id_shop);
        foreach ($move_settings as $key => $move) {
            if (isset($all_saved_settings[$move['from']][$key])) {
                $all_saved_settings[$move['to']][$move['key']] = $all_saved_settings[$move['from']][$key];
            }
        }
        foreach ($update_settings as $type => $settings) {
            foreach ($settings as $name => $value) {
                $all_saved_settings[$type][$name] = $value;
            }
        }
        foreach ($module_obj->getSettingsKeys() as $type) {
            $settings = isset($all_saved_settings[$type]) ? $all_saved_settings[$type] : [];
            $module_obj->saveSettings($type, $settings, [$id_shop]);
        }
    }
    $module_obj->indexationTable('install');

    return true;
}

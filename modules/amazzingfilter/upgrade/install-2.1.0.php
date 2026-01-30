<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_1_0($module_obj)
{
    if (!defined('_PS_VERSION_')) {
        exit;
    }
    $all_settings = $module_obj->db->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'af_general_settings');
    $updated_rows = [];
    foreach ($all_settings as $settings_row) {
        $new_row = [];
        foreach ($settings_row as $name => $value) {
            if ($name == 'settings') {
                $settings = json_decode($settings_row['settings'], true);
                if (!isset($settings['oos_behaviour']) && !empty($settings['combinations_stock'])) {
                    $settings['oos_behaviour'] = 2;
                }
                $value = json_encode($settings);
            }
            $new_row[$name] = '\'' . pSQL($value) . '\'';
        }
        $updated_rows[] = '(' . implode(', ', $new_row) . ')';
    }
    if ($updated_rows) {
        $module_obj->db->execute('
            REPLACE INTO ' . _DB_PREFIX_ . 'af_general_settings VALUES ' . implode(', ', $updated_rows) . '
        ');
    }

    return true;
}

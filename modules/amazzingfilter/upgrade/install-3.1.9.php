<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_3_1_9($module_obj)
{
    if (!defined('_PS_VERSION_')) {
        exit;
    }
    // Media::clearCache(); // cleared in 3.2.0
    // save sales_days, new_days, sorting_options, default_sorting
    foreach ($module_obj->shopIDs('all') as $id_shop) {
        $general_settings = $module_obj->getSavedSettings($id_shop, 'general');
        $general_settings = updateDefaultSorting($general_settings);
        $module_obj->saveSettings('general', $general_settings, [$id_shop]);
    }
    // update default_sorting in additional_settings of templates
    $upd_template_rows = [];
    foreach ($module_obj->db->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'af_templates') as $row) {
        $settings = json_decode($row['additional_settings'], true);
        $upd_settings = updateDefaultSorting($settings);
        if ($row['template_controller'] == 'search' && !empty($upd_settings['default_sorting'])
            && $upd_settings['default_sorting'] == 'position.desc') {
            $upd_settings['default_sorting'] = 'position.asc';
        }
        if ($upd_settings != $settings) {
            $row['additional_settings'] = json_encode($upd_settings);
            $upd_template_rows[] = '(\'' . implode('\', \'', array_map('pSQL', $row)) . '\')';
        }
    }
    if ($upd_template_rows) {
        $module_obj->db->execute('
            REPLACE INTO ' . _DB_PREFIX_ . 'af_templates VALUES ' . implode(', ', $upd_template_rows) . '
        ');
    }
    // remove unused directories
    $unused_dirs = ['/override_files/classes/', '/override_files/controllers/', '/indexes/'];
    foreach ($unused_dirs as $dir) {
        recursiveRemove(_PS_MODULE_DIR_ . $module_obj->name . $dir);
    }

    return true;
}

function updateDefaultSorting($settings)
{
    if (!empty($settings['default_order_by']) || !empty($settings['default_order_way'])) {
        $by = !empty($settings['default_order_by']) ? $settings['default_order_by'] : 'position';
        $way = !empty($settings['default_order_way']) ? $settings['default_order_way'] : 'asc';
        if ($by == 'random') {
            $way = 'desc';
        }
        $settings['default_sorting'] = $by . '.' . $way;
        unset($settings['default_order_by']);
        unset($settings['default_order_way']);
    }

    return $settings;
}

function recursiveRemove($dir, $keep_top_level_dir = false)
{
    $dir = rtrim($dir, '/') . '/';
    $structure = glob($dir . '*');
    if (is_array($structure)) {
        foreach ($structure as $path) {
            if (is_dir($path)) {
                recursiveRemove($path);
            } elseif (is_file($path)) {
                unlink($path);
            }
        }
    }
    if (!$keep_top_level_dir && is_dir($dir)) {
        $possible_htaccess_file = $dir . '.htaccess'; // not detected in glob()
        if (is_file($possible_htaccess_file)) {
            unlink($possible_htaccess_file);
        }
        rmdir($dir);
    }
}

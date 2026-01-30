<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_8_4($module_obj)
{
    if (!defined('_PS_VERSION_')) {
        exit;
    }
    // update settings for compact offset direction
    $rows_to_update = [];
    foreach ($module_obj->db->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'af_general_settings') as $row) {
        $settings = json_decode($row['settings'], true);
        $settings['compact_offset'] = 2;
        $row['settings'] = json_encode($settings);
        $rows_to_update[] = '(\'' . implode('\', \'', array_map('pSQL', $row)) . '\')';
    }
    if ($rows_to_update) {
        $module_obj->db->execute('
            REPLACE INTO ' . _DB_PREFIX_ . 'af_general_settings VALUES ' . implode(', ', $rows_to_update) . '
        ');
    }

    return true;
}

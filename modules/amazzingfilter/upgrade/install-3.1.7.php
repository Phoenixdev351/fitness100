<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_3_1_7($module_obj)
{
    if (!defined('_PS_VERSION_')) {
        exit;
    }
    // Media::clearCache(); // cleared in 3.1.8
    $i_settings_rows = $module_obj->db->executeS('
        SELECT * FROM ' . _DB_PREFIX_ . 'af_settings WHERE type = \'indexation\'
    ');
    $upd_rows = [];
    foreach ($i_settings_rows as $row) {
        $v = json_decode($row['value'], true);
        $row['value'] = pSQL(json_encode($v + ['c_active' => 0, 'p_comb' => 0]));
        $upd_rows[] = '(\'' . implode('\', \'', $row) . '\')';
    }
    if ($upd_rows) {
        $module_obj->db->execute('
            REPLACE INTO ' . _DB_PREFIX_ . 'af_settings VALUES ' . implode(', ', $upd_rows) . '
        ');
    }

    return true;
}

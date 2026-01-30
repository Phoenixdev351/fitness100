<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_3_1_2($module_obj)
{
    if (!defined('_PS_VERSION_')) {
        exit;
    }
    // Media::clearCache(); // cleared in 3.1.3
    // set positions for merged attributes based on positions of first matching original attributes
    $rows = $module_obj->db->executeS('
        SELECT m.*, a.position FROM ' . _DB_PREFIX_ . 'af_merged_attribute m
        INNER JOIN ' . _DB_PREFIX_ . 'af_merged_attribute_map map ON map.id_merged = m.id_merged
        INNER JOIN ' . _DB_PREFIX_ . 'attribute a ON a.id_attribute = map.id_original
        GROUP BY id_merged
    ');
    if ($rows) {
        foreach ($rows as &$row) {
            $row = '(' . implode(',', array_map('intval', $row)) . ')'; // formatIDs not suitable
        }
        $module_obj->db->execute('REPLACE INTO ' . _DB_PREFIX_ . 'af_merged_attribute VALUES ' . implode(', ', $rows));
    }

    return true;
}

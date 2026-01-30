<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_3_1_3($module_obj)
{
    if (!defined('_PS_VERSION_')) {
        exit;
    }
    // Media::clearCache(); // cleared in 3.1.5
    $color_groups = array_column($module_obj->db->executeS('
        SELECT CONCAT(\'a\', id_attribute_group) as f_key
        FROM ' . _DB_PREFIX_ . 'attribute_group WHERE is_color_group = 1
    '), 'f_key', 'f_key');
    $upd_template_rows = [];
    foreach ($module_obj->db->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'af_templates') as $row) {
        $filters = json_decode($row['template_filters'], true);
        foreach (array_keys($filters) as $key) {
            if (isset($color_groups[$key])) {
                $filters[$key]['color_display'] = 1;
            }
            $first_char = Tools::substr($key, 0, 1);
            $filters[$key]['visible_items'] = in_array($first_char, ['a', 'f', 'm', 's', 't']) ? 15 : '';
        }
        $row['template_filters'] = json_encode($filters);
        $upd_template_rows[] = '(\'' . implode('\', \'', array_map('pSQL', $row)) . '\')';
    }
    if ($upd_template_rows) {
        $module_obj->db->execute('
            REPLACE INTO ' . _DB_PREFIX_ . 'af_templates VALUES ' . implode(', ', $upd_template_rows) . '
        ');
    }

    return true;
}

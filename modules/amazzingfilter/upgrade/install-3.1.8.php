<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_3_1_8($module_obj)
{
    if (!defined('_PS_VERSION_')) {
        exit;
    }
    // Media::clearCache(); // cleared in 3.1.9
    $i_settings_rows = $module_obj->db->executeS('
        SELECT * FROM ' . _DB_PREFIX_ . 'af_settings WHERE type = \'indexation\'
    ');
    foreach ($i_settings_rows as $k => $row) {
        $v = json_decode($row['value'], true) + ['dynamic_tax' => 0];
        $row['value'] = pSQL(json_encode($v));
        $i_settings_rows[$k] = '(\'' . implode('\', \'', $row) . '\')';
    }
    if ($i_settings_rows) {
        $module_obj->db->execute('
            REPLACE INTO ' . _DB_PREFIX_ . 'af_settings VALUES ' . implode(', ', $i_settings_rows) . '
        ');
    }
    // add id_product, id_country in af_p_comb
    if ($module_obj->db->executeS('SHOW TABLES LIKE \'' . _DB_PREFIX_ . 'af_p_comb\'')) {
        $module_obj->db->execute('
            ALTER TABLE ' . _DB_PREFIX_ . 'af_p_comb
            ADD COLUMN id_product int(10) unsigned NOT NULL FIRST,
            ADD COLUMN id_country int(10) unsigned NOT NULL AFTER id_shop,
            DROP PRIMARY KEY,
            ADD PRIMARY KEY (id_product,id_comb,id_shop,id_country)
        ');
        $p_comb_rows = $module_obj->db->executeS('
            SELECT pc.*, pa.id_product FROM ' . _DB_PREFIX_ . 'af_p_comb pc
            LEFT JOIN  ' . _DB_PREFIX_ . 'product_attribute pa
                ON pa.id_product_attribute = pc.id_comb
        ');
        if ($p_comb_rows) {
            foreach ($p_comb_rows as $k => $row) {
                $p_comb_rows[$k] = '(\'' . implode('\', \'', array_map('pSQL', $row)) . '\')';
            }
            $module_obj->db->execute('
                DELETE FROM ' . _DB_PREFIX_ . 'af_p_comb;
                REPLACE INTO ' . _DB_PREFIX_ . 'af_p_comb VALUES ' . implode(', ', $p_comb_rows) . '
            ');
        }
    }

    return true;
}

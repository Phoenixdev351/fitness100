<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_8_0($module_obj)
{
    if (!defined('_PS_VERSION_')) {
        exit;
    }
    // prepare multilang data for templates
    $module_obj->prepareDatabaseTables();
    $available_templates = $module_obj->db->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'af_templates');
    $multilang_rows = [];
    foreach ($available_templates as $t) {
        $template_data_multilang = [];
        $filters = json_decode($t['template_filters'], true);
        foreach ($filters as $key => $f) {
            if ($f['type'] == '4' && $key != 'p') { // no need to add slider extensions for price
                if ($slider_extensions = $module_obj->getSliderExtensions($key)) {
                    foreach ($slider_extensions as $ext_type => $ext_multilang) { // $ext_type = prefix or suffix
                        foreach ($ext_multilang as $id_lang => $value) {
                            $template_data_multilang[$id_lang][$key]['slider_' . $ext_type] = $value;
                        }
                    }
                }
            }
        }
        foreach ($template_data_multilang as $id_lang => $data) {
            $encoded_data = json_encode($data);
            $row = (int) $t['id_template'] . ', ' . (int) $t['id_shop'] . ', ' . (int) $id_lang
                . ', \'' . pSQL($encoded_data) . '\'';
            $multilang_rows[] = '(' . $row . ')';
        }
    }
    if ($multilang_rows) {
        $module_obj->db->execute('
            REPLACE INTO ' . _DB_PREFIX_ . 'af_templates_lang VALUES ' . implode(', ', $multilang_rows) . '
        ');
    }

    return true;
}

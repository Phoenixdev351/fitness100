<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_3_1_0($module_obj)
{
    if (!defined('_PS_VERSION_')) {
        exit;
    }
    // Media::clearCache(); // cleared in 3.1.2
    $module_obj->prepareDatabaseTables(); // add af_seopage_templates
    $module_obj->saveTemplate(0, 'seopage', 'Template for SEO Pages');

    $template_rows = [];
    foreach ($module_obj->db->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'af_templates') as $row) {
        $filters = json_decode($row['template_filters'], true);
        foreach ($filters as $key => $f) {
            if ($f['type'] == 4) {
                $filters[$key]['slider_step'] = 1;
            }
        }
        $row['template_filters'] = json_encode($filters);
        $template_rows[] = '(\'' . implode('\', \'', array_map('pSQL', $row)) . '\')';
    }
    $module_obj->db->execute('REPLACE INTO ' . _DB_PREFIX_ . 'af_templates VALUES ' . implode(', ', $template_rows));

    return true;
}

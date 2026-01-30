<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_3_2_5($module_obj)
{
    // Media::clearCache(); // cleared in 3.2.6
    $module_obj->cache('clear', '');
    $module_obj->unregisterHook('displayCustomerAccount');
    $cf_settings = [];
    if ($retro_cf = Configuration::get('AF_SAVED_CUSTOMER_FILTERS')) {
        $cf_settings['keys'] = implode(',', json_decode($retro_cf, true));
    }
    $module_obj->saveSettings('cf', $cf_settings);
    Configuration::deleteByName('AF_SAVED_CUSTOMER_FILTERS');
    if ($saved_cf_rows = $module_obj->db->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'af_customer_filters')) {
        $upd_rows = $cf_backup = [];
        foreach ($saved_cf_rows as $row) {
            if ($row['filters']) {
                $row['filters'] = $backup = json_decode($row['filters'], true);
                foreach ($row['filters'] as $key => $ids) {
                    $row['filters'][$key] = current($ids);
                    if (count($ids) > 1 && !isset($cf_backup[$row['id_customer']])) {
                        $cf_backup[$row['id_customer']] = $backup;
                    }
                }
                $row['filters'] = json_encode($row['filters']);
                $upd_rows[] = '(\'' . implode('\', \'', array_map('pSQL', $row)) . '\')';
            }
        }
        if ($upd_rows) {
            if ($cf_backup) {
                Configuration::updateValue('AF_CF_BACKUP', json_encode($cf_backup));
            }
            $module_obj->db->execute('
                REPLACE INTO ' . _DB_PREFIX_ . 'af_customer_filters VALUES ' . implode(',', $upd_rows) . '
            ');
        }
    }
    $to_unlink = [
        '/controllers/front/myfilters.php',
        '/views/css/my-filters.css',
        '/views/js/my-filters.js',
        '/views/templates/front/content-17.tpl',
        '/views/templates/front/my-filters.tpl',
        '/views/templates/hook/my-filters-tab.tpl',
    ];
    foreach ($to_unlink as $relative_path) {
        $path = _PS_MODULE_DIR_ . $module_obj->name . $relative_path;
        if (is_file($path)) {
            unlink($path);
        }
    }
    $module_obj->log('add', 'auto-upgrade applied for v3.2.5');

    return true;
}

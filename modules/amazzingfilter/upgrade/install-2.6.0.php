<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_6_0($module_obj)
{
    if (!defined('_PS_VERSION_')) {
        exit;
    }
    // update hooks
    $module_obj->registerHook('actionProductAdd');
    $module_obj->registerHook('actionProductUpdate');
    $module_obj->unregisterHook('actionProductSave');
    // add condition to index
    $sorted = [];
    $rows = $module_obj->db->executeS('
        SELECT id_product, id_shop, `condition`
        FROM ' . _DB_PREFIX_ . 'product_shop
    ');
    foreach ($rows as $row) {
        $sorted[$row['id_shop']][$row['id_product']] = $row['condition'];
    }
    $index_files = glob(_PS_MODULE_DIR_ . $module_obj->name . '/indexes/*.csv');
    foreach ($index_files as $file_path) {
        $name = basename($file_path);
        $name = explode('_', $name);
        $id_shop = $name[1];
        $lines = file($file_path);
        $updated_lines = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (!$line) {
                continue;
            }
            $id_product = current(explode('|', $line));
            $line .= '|' . (isset($sorted[$id_shop][$id_product]) ? $sorted[$id_shop][$id_product] : 'new');
            $updated_lines[] = $line;
        }
        $updated_lines = implode("\n", $updated_lines);
        file_put_contents($file_path, $updated_lines);
    }
    // remove unrequired override files
    $override_dir = _PS_MODULE_DIR_ . $module_obj->name . '/override/';
    $subdirs = ['classes/', 'controllers/admin/', 'constollers/front/'];
    foreach ($subdirs as $subdir) {
        $files = glob($override_dir . $subdir . '*.php');
        foreach ($files as $file) {
            if (basename($file) != 'index.php') {
                unlink($file);
            }
        }
    }
    if (!$module_obj->is_modern) {
        $module_obj->relatedOverrides()->process('removeOverride', 'controllers/front/ProductController.php');
        $module_obj->relatedOverrides()->process('addOverride', 'controllers/front/ProductController.php');
    }

    return true;
}

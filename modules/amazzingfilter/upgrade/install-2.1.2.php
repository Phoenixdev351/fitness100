<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_1_2($module_obj)
{
    if (!defined('_PS_VERSION_')) {
        exit;
    }
    $sorted_dates = [];
    $dates = $module_obj->db->executeS('
        SELECT id_product, id_shop, date_upd
        FROM ' . _DB_PREFIX_ . 'product_shop
    ');
    foreach ($dates as $d) {
        $sorted_dates[$d['id_shop']][$d['id_product']] = $d['date_upd'];
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
            $line = explode('|', $line);
            $id_product = $line[0];
            $date_upd = $line[11];
            if (!empty($sorted_dates[$id_shop][$id_product])) {
                $date_upd = $sorted_dates[$id_shop][$id_product];
            }
            array_splice($line, 12, 0, $date_upd);
            $updated_lines[] = implode('|', $line);
        }
        $updated_lines = implode("\n", $updated_lines);
        file_put_contents($file_path, $updated_lines);
    }
    $prev_doc_file = _PS_MODULE_DIR_ . $module_obj->name . '/documentation_en.pdf';
    if (file_exists($prev_doc_file)) {
        unlink($prev_doc_file);
    }

    return true;
}

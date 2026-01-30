<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_5_3($module_obj)
{
    if (!defined('_PS_VERSION_')) {
        exit;
    }
    $index_files = glob(_PS_MODULE_DIR_ . $module_obj->name . '/indexes/*.csv');
    foreach ($index_files as $file_path) {
        $lines = file($file_path);
        $updated_lines = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (!$line) {
                continue;
            }
            $line = explode('|', $line);
            array_splice($line, 3, 0, '');
            $updated_lines[] = implode('|', $line);
        }
        $updated_lines = implode("\n", $updated_lines);
        file_put_contents($file_path, $updated_lines);
    }

    return true;
}

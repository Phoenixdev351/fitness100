<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_3_2_0($module_obj)
{
    if (!defined('_PS_VERSION_')) {
        exit;
    }
    // Media::clearCache(); // cleared in 3.2.3
    $outdated_files = [
        'css' => [
            'length' => 369,
            'start' => "/*\n* Override this file",
            'end' => "padding-left: 10px;\n\t}\n}\n",
        ],
        'js' => [
            'length' => 703,
            'start' => "/**\n*  2007-",
            'end' => "boxes.length - maxColorBoxes));\n\t\t\t}\n\t\t});\n\t}\n}\n",
        ],
    ];
    foreach ($outdated_files as $type => $data) {
        $outdated_path = _PS_MODULE_DIR_ . $module_obj->name . '/views/' . $type . '/custom.' . $type;
        if (file_exists($outdated_path)) {
            $code = Tools::file_get_contents($outdated_path);
            if (Tools::strlen($code) == $data['length']
                && Tools::substr($code, 0, Tools::strlen($data['start'])) === $data['start']
                && Tools::substr($code, -Tools::strlen($data['end'])) == $data['end']) {
                unlink($outdated_path);
            }
        }
    }
    foreach (['css', 'js'] as $type) {
        $subdir = $module_obj->is_modern ? '' : $type . '/';
        $possible_custom_file = _PS_THEME_DIR_ . $subdir . 'modules/' . $module_obj->name
            . '/views/' . $type . '/custom.' . $type;
        if (file_exists($possible_custom_file)) {
            $dest_path = $module_obj->bo()->customCode('getFilePath', ['type' => $type]);
            Tools::copy($possible_custom_file, $dest_path);
            unlink($possible_custom_file);
            $parent_dir = dirname($possible_custom_file);
            $i = 0;
            do {
                if (!glob($parent_dir . '/*')) {
                    Tools::deleteDirectory($parent_dir);
                    $parent_dir = dirname($parent_dir);
                } else {
                    break;
                }
            } while (++$i < 3);
        }
    }

    return true;
}

<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_7_0($module_obj)
{
    if (!defined('_PS_VERSION_')) {
        exit;
    }
    $module_obj->relatedOverrides()->process('removeOverride', 'classes/Product.php');
    $module_obj->relatedOverrides()->process('addOverride', 'classes/Product.php');
    if (!$module_obj->is_modern) {
        $module_obj->relatedOverrides()->process('removeOverride', 'controllers/front/ProductController.php');
    }

    return true;
}

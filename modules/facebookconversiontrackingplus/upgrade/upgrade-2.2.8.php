<?php
/**
 * Facebook Conversion Pixel Tracking Plus
 *
 * NOTICE OF LICENSE
 *
 * @author    Pol Rué
 * @copyright Smart Modules 2014
 * @license   One time purchase Licence (You can modify or resell the product but just one time per licence)
 * @version 2.3.3
 * @category Marketing & Advertising
 * Registered Trademark & Property of smart-modules.com
 *
 * ***************************************************
 * *     Facebook Conversion Trackcing Pixel Plus    *
 * *          http://www.smart-modules.com           *
 * *                     V 2.3.3                     *
 * ***************************************************
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_2_8($module)
{
    // Update the name of the templates
    $old_tpls = array('start_order', 'start_payment', 'checkoutpixel');
    $base_url = _MODULE_DIR_.$module->name.'/views/templates/hook/';

    foreach ($old_tpls as $tpl) {
        if (file_exists($base_url.$tpl)) {
            unlink($base_url.$tpl);
        }
    }
    return true;
}

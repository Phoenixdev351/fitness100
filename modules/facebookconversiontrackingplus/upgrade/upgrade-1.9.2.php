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

function upgrade_module_1_9_2($module)
{
    $langs = Language::getLanguages();
    $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
    $shops = Shop::getShops();
    // Update the Catalogue Ids for new Feed V2 generation system
    foreach ($shops as $shop) {
        if ($module::$feed_v2) {
            Configuration::updateGlobalValue('FCTP_FEED_'.$shop['id_shop'], Configuration::get('FCTP_FEED_'.$shop['id_shop'].'_'.$default_lang));
            foreach ($langs as $lang) {
                Configuration::deleteByName('FPF_'.$shop['id_shop'].'_'.$lang['id_lang']);
            }
        }
    }
    // Update the configuration parameters that need a global scope
    foreach ($module->form_fields as $field) {
        if ($field['global'] == 1) {
            $old_val = Configuration::get($field['name']);
            Configuration::deleteByName($field['name']);
            Configuration::updateGlobalValue($field['name'], $old_val);
        }
    }
    return true;
}

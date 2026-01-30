<?php
/**
 * Facebook Products Feed catalogue export for Prestashop
 *
 * NOTICE OF LICENSE
 *
 * @author    Pol Rué
 * @copyright Smart Modules 2016
 * @license   One time purchase Licence (You can modify or resell the product but just one time per licence)
 * @version 2.3.3
 * @category Advertising & Marketing
 * Registered Trademark & Property of smart-modules.com
 *
 * ****************************************
 * *        Facebook Products Feed        *
 * *   http://www.smart-modules.com       *
 * *               V 2.3.3                *
 * ****************************************
*/

require_once(dirname(__FILE__).'/../../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../../init.php');
require_once(dirname(__FILE__).'/../facebookproductsfeed.php');
if (Tools::getValue('lang_code') != '') {
    header('Access-Control-Allow-Origin: '._PS_BASE_URL_, false);
    header('Access-Control-Allow-Origin: '._PS_BASE_URL_SSL_, false);
    $iso = preg_replace('/[^-a-zA-Z0-9_]/', '', Tools::getValue('lang_code'));
    $module = new FacebookProductsFeed();
    echo Tools::jsonEncode($module->prepareGoogleTaxonomies($iso));
} else {
    echo '[{}]';
}

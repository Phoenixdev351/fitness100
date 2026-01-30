<?php
/**
 * Facebook Conversion Pixel Tracking Plus
 *
 * NOTICE OF LICENSE
 *
 * @author    Pol Rué
 * @copyright Smart Modules 2014
 * @license   One time purchase Licence (You can modify or resell the product but just one time per licence)
 * @version 2.0.7
 * @category Marketing & Advertising
 * Registered Trademark & Property of Smart-Modules.pro
 *
 * ***************************************************
 * *     Facebook Conversion Trackcing Pixel Plus    *
 * *          http://www.smart-modules.pro           *
 * *                     V 2.0.7                     *
 * ***************************************************
*/

// Prevent indexing
header("X-Robots-Tag: noindex, nofollow", true);
require(dirname(__FILE__).'/../../config/config.inc.php');
require(dirname(__FILE__).'/facebookconversiontrackingplus.php');
$tmp = new FacebookConversionTrackingPlus();
$return = '';
if (Tools::getValue('trackRegister')) {
    $return =  $tmp->trackAjaxRegistration() ? '{"return":"ok"}' : '{"return":"error"}';
} else {
    $return =  $tmp->trackAjaxConversion(Tools::getValue('id_customer')) ? '{"return":"ok"}' : '{"return":"error"}';
}

echo $return;

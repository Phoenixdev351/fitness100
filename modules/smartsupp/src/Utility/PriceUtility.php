<?php
/**
 * Smartsupp Live Chat integration module.
 *
 * @package   Smartsupp
 * @author    Smartsupp <vladimir@smartsupp.com>
 * @link      http://www.smartsupp.com
 * @copyright 2016 Smartsupp.com
 * @license   GPL-2.0+
 *
 * Plugin Name:       Smartsupp Live Chat
 * Plugin URI:        http://www.smartsupp.com
 * Description:       Adds Smartsupp Live Chat code to PrestaShop.
 * Version:           2.2.0
 * Author:            Smartsupp
 * Author URI:        http://www.smartsupp.com
 * Text Domain:       smartsupp
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

namespace Smartsupp\LiveChat\Utility;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PriceUtility
{
    public static function displayPrice($price, $currency = null)
    {
        if (VersionUtility::isPsVersionGreaterOrEqualTo('9.0.0')) {
            $context = \Context::getContext();
            $isoCode = $currency && isset($currency->iso_code)
                ? $currency->iso_code
                : $context->currency->iso_code;

            $locale = $context->getCurrentLocale();

            if (!$locale) {
                return (string) $price;
            }

            return $locale->formatPrice(
                $price,
                $isoCode
            );
        }

        return \Tools::displayPrice($price, $currency);
    }
}
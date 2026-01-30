<?php
/**
 * This file is part of the securitypro package.
 *
 * @author Mathias Reker
 * @copyright Mathias Reker
 * @license Commercial Software License
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (false === \defined('_PS_VERSION_')) {
    exit;
}

/**
 * File: /upgrade/upgrade-6.1.0.php
 *
 * @param object $module
 */
function upgrade_module_6_1_0($module)
{
    $module->registerHook(
        [
            'displayMaintenance',
        ]
    );

    Configuration::updateValue('PRO_ADVANCED_MAINTENANCE_MODE_COMPANY', 'My Company');
    Configuration::updateValue('PRO_ADVANCED_MAINTENANCE_MODE_ADDRESS', '123 Main St, Anytown, USA');
    Configuration::updateValue('PRO_ADVANCED_MAINTENANCE_MODE_PHONE', '000-000-0000');
    Configuration::updateValue('PRO_ADVANCED_MAINTENANCE_MODE_EMAIL', Configuration::get('PS_SHOP_EMAIL'));
    Configuration::updateValue('PRO_ADVANCED_MAINTENANCE_MODE_FACEBOOK', '#');
    Configuration::updateValue('PRO_ADVANCED_MAINTENANCE_MODE_TWITTER', '#');
    Configuration::updateValue('PRO_ADVANCED_MAINTENANCE_MODE_INSTAGRAM', '#');
    Configuration::updateValue('PRO_ADVANCED_MAINTENANCE_MODE_PINTEREST', '#');
    Configuration::updateValue('PRO_ADVANCED_MAINTENANCE_MODE_YOUTUBE', '#');
    Configuration::updateValue('PRO_ADVANCED_MAINTENANCE_MODE_COPYRIGHT', true);
    Configuration::updateValue('PRO_ADVANCED_MAINTENANCE_MODE_LOGO_PATH', _PS_IMG_ . Configuration::get('PS_LOGO'));
    Configuration::updateValue('PRO_ADVANCED_MAINTENANCE_MODE_LOGO', true);
    Configuration::updateValue('PRO_ANTI_FRAUD_UNIT', 'km');
    Configuration::updateValue('PRO_ANTI_FRAUD_HOOK', 'left');

    return true;
}

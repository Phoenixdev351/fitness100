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
 * File: /upgrade/upgrade-5.1.0.php
 *
 * @param object $module
 */
function upgrade_module_5_1_0($module)
{
    $module->registerHook(
        [
            'displayCustomerAccountForm',
        ]
    );

    return true;
}

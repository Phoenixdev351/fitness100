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
 * File: /upgrade/upgrade-6.2.0.php
 */
function upgrade_module_6_2_0()
{
    Configuration::updateValue('PRO_FIREWALL_SQL_CHECK', 0);
    Configuration::updateValue('PRO_FIREWALL_XXS_CHECK', 0);
    Configuration::updateValue('PRO_FIREWALL_HTML_CHECK', 0);
    Configuration::updateValue('PRO_FIREWALL_SHELL_CHECK', 0);

    return true;
}

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
 * File: /upgrade/upgrade-4.2.0.php
 *
 * @param object $module
 */
function upgrade_module_4_2_0($module)
{
    $module->registerHook(
        [
            'displayAdminLogin',
            'actionBeforeSubmitAccount',
        ]
    );

    $sql = [];
    $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'securitypro_tfa` (
        `enabled` int(1) NOT NULL,
        `secret` varchar(32) NOT NULL
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ';

    foreach ($sql as $query) {
        if (false === Db::getInstance()->execute($query)) {
            return false;
        }
    }

    return true;
}

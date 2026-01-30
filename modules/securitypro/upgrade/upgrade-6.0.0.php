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
 * File: /upgrade/upgrade-6.0.0.php
 *
 * @param object $module
 */
function upgrade_module_6_0_0($module)
{
    $module->registerHook(
        [
            'actionValidateOrder',
            'displayAdminOrderSide',
            'displayAdminOrderRight',
        ]
    );

    $sql = [];
    $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'securitypro_af` (
        `id_order` INT(10) UNSIGNED NOT NULL,
        `ip` varchar(64) NOT NULL,
        `ua` varchar(512) NOT NULL,
        `proxy` INT(1) UNSIGNED NULL DEFAULT NULL
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ';

    foreach ($sql as $query) {
        if (false === Db::getInstance()->execute($query)) {
            return false;
        }
    }

    return true;
}

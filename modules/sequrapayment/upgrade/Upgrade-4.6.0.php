<?php
/*
*  @author SeQura <info@sequra.es>
*  @copyright  SeQura Worldwide S.L.
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/
if (!defined('_PS_VERSION_')) {
    exit;
}
function upgrade_module_4_6_0($module)
{
    $db = Db::getInstance();
    $db->execute(
        'UPDATE `' . _DB_PREFIX_ . 'configuration` set `name` = "SEQURA_OS_CANCELED" where `name` = "SEQURA_OS_CANCELLED"'
    );
    $db->execute(
        'UPDATE `' . _DB_PREFIX_ . 'configuration` set `name` = "SEQURA_OS_APPROVED" where `name` = "SEQURA_OS_CONFIRMED"'
    );
    $db->execute(
        'UPDATE `' . _DB_PREFIX_ . 'configuration` set `name` = "SEQURA_OS_NEEDS_REVIEW" where `name` = "SEQURA_OS_PENDING"'
    );
    $db->execute(
        'DELETE from `' . _DB_PREFIX_ . 'configuration` where `name` in ("SEQURA_WEBHOOK","SEQURA_OS_SENT")'
    );
    //Create pending state, using install method to make sure it is created the same.
    $installer = new SequraInstaller($module);
    return $installer->install();
}

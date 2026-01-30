<?php
/**
*
* NOTICE OF LICENSE
*
* This product is licensed for one customer to use on one installation (test stores and multishop included).
* Site developer has the right to modify this module to suit their needs, but can not redistribute the module in
* whole or in part. Any other use of this module constitutes a violation of the user agreement.
*
* DISCLAIMER
*
* NO WARRANTIES OF DATA SAFETY OR MODULE SECURITY
* ARE EXPRESSED OR IMPLIED. USE THIS MODULE IN ACCORDANCE
* WITH YOUR MERCHANT AGREEMENT, KNOWING THAT VIOLATIONS OF
* PCI COMPLIANCY OR A DATA BREACH CAN COST THOUSANDS OF DOLLARS
* IN FINES AND DAMAGE A STORES REPUTATION. USE AT YOUR OWN RISK.
*
*  @author    idnovate.com <info@idnovate.com>
*  @copyright 2020 idnovate.com
*  @license   See above
*/

function upgrade_module_1_0_13($module)
{
    Db::getInstance()->execute(
        "ALTER TABLE `".pSQL(_DB_PREFIX_.$module->name)."_configuration`
        ADD `close_on_background` tinyint(1) NULL AFTER `display_on_exit`,
        ADD `show_customer_not_newsletter` tinyint(1) NULL DEFAULT '0' AFTER `show_customer_newsletter`;"
    );

    Db::getInstance()->execute(
        "UPDATE `".pSQL(_DB_PREFIX_.$module->name)."_configuration`
        SET `close_on_background` = 1;"
    );

    return true;
}

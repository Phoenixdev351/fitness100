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

function upgrade_module_1_1_1($module)
{
    Db::getInstance()->execute("
        ALTER TABLE `"._DB_PREFIX_."advancedpopup`
        CHANGE `color_background` `color_background` varchar(32) NULL AFTER `blur_background`;
    ");

    Db::getInstance()->execute("
        ALTER TABLE `"._DB_PREFIX_."advancedpopup`
        DROP `close_effect`;
    ");

    Db::getInstance()->execute("
        ALTER TABLE `"._DB_PREFIX_."advancedpopup`
        CHANGE `open_effect` `open_effect` varchar(32) NOT NULL AFTER `color_background`;
    ");

    Db::getInstance()->execute("
        UPDATE `"._DB_PREFIX_."advancedpopup`
        SET `open_effect` = 'zoom';
    ");

    $module->uninstallTabs();
    $module->installTabs();

    return true;
}

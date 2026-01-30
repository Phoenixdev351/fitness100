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

function upgrade_module_1_0_3($module)
{
    Db::getInstance()->execute(
        'ALTER TABLE `'.pSQL(_DB_PREFIX_.$module->name).'_configuration`
        ADD `nb_products` text COLLATE "utf8_general_ci" NULL DEFAULT "" AFTER `show_every_nbr_hours`,
        ADD `categories_selected` text COLLATE "utf8_general_ci" NULL AFTER `categories`,
        ADD `display_after_cart` int NOT NULL AFTER `active`;
        CHANGE `show_every_nbr_hours` `show_every_nbr_hours` text COLLATE "utf8_general_ci" NULL DEFAULT "",
        CHANGE `secs_to_display` `secs_to_display` text COLLATE "utf8_general_ci" NULL DEFAULT "",
        CHANGE `secs_to_close` `secs_to_close` text COLLATE "utf8_general_ci" NULL DEFAULT "";'
    );

    Db::getInstance()->execute(
        'ALTER TABLE `'.pSQL(_DB_PREFIX_.$module->name).'_configuration_lang`
        ADD `responsive_min` int NULL,
        ADD `responsive_max` int NULL AFTER `responsive_min`;'
    );

    return $module;
}

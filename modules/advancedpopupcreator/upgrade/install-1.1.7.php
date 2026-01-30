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

function upgrade_module_1_1_7()
{
    Db::getInstance()->execute("
        ALTER TABLE `"._DB_PREFIX_."advancedpopup`
        ADD `display_ip_string` text NULL AFTER `display_desktop`;
    ");

     Db::getInstance()->execute("
        UPDATE `"._DB_PREFIX_."advancedpopup` a
        SET `display_ip_string` = (
            SELECT `display_ip_string`
            FROM `"._DB_PREFIX_."advancedpopup_lang` al
            WHERE a.`id_advancedpopup` = al.`id_advancedpopup`
             AND al.`id_lang` = ".(int)Configuration::get('PS_LANG_DEFAULT')."
        );
    ");

     Db::getInstance()->execute("
        ALTER TABLE `"._DB_PREFIX_."advancedpopup_lang`
        DROP `display_ip_string`;
    ");

     Db::getInstance()->execute("
        UPDATE `"._DB_PREFIX_."advancedpopup`
        SET `controller_exceptions` = REPLACE(`controller_exceptions`, ';', ','),
            `groups` = REPLACE(`groups`, ';', ','),
            `customers` = REPLACE(`customers`, ';', ','),
            `products` = REPLACE(`products`, ';', ','),
            `countries` = REPLACE(`countries`, ';', ','),
            `zones` = REPLACE(`zones`, ';', ','),
            `categories` = REPLACE(`categories`, ';', ','),
            `categories_selected` = REPLACE(`categories_selected`, ';', ','),
            `manufacturers` = REPLACE(`manufacturers`, ';', ','),
            `suppliers` = REPLACE(`suppliers`, ';', ','),
            `cms` = REPLACE(`cms`, ';', ','),
            `languages` = REPLACE(`languages`, ';', ','),
            `display_ip_string` = REPLACE(`display_ip_string`, ';', ',');
    ");

    return true;
}

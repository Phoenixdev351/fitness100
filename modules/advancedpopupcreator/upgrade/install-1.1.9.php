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

function upgrade_module_1_1_9()
{
    $sql = "ALTER TABLE `"._DB_PREFIX_."advancedpopup`
        CHANGE `controller_exceptions` `controller_exceptions` text COLLATE 'utf8_general_ci' NOT NULL AFTER `back_opacity_value`,
        CHANGE `groups` `groups` text COLLATE 'utf8_general_ci' NOT NULL AFTER `controller_exceptions`,
        CHANGE `customers` `customers` text COLLATE 'utf8_general_ci' NOT NULL AFTER `groups`,
        CHANGE `products` `products` text COLLATE 'utf8_general_ci' NOT NULL AFTER `customers`,
        CHANGE `countries` `countries` text COLLATE 'utf8_general_ci' NOT NULL AFTER `products`,
        CHANGE `zones` `zones` text COLLATE 'utf8_general_ci' NOT NULL AFTER `countries`,
        CHANGE `categories` `categories` text COLLATE 'utf8_general_ci' NOT NULL AFTER `zones`,
        CHANGE `manufacturers` `manufacturers` text COLLATE 'utf8_general_ci' NOT NULL AFTER `categories_selected`,
        CHANGE `suppliers` `suppliers` text COLLATE 'utf8_general_ci' NOT NULL AFTER `manufacturers`,
        CHANGE `cms` `cms` text COLLATE 'utf8_general_ci' NOT NULL AFTER `suppliers`,
        CHANGE `languages` `languages` text COLLATE 'utf8_general_ci' NOT NULL AFTER `cms`,
        CHANGE `display_ip_string` `display_ip_string` text COLLATE 'utf8_general_ci' NOT NULL AFTER `display_desktop`;";

    Db::getInstance()->execute($sql);

    $sql = "ALTER TABLE `"._DB_PREFIX_."advancedpopup_lang`
        CHANGE `responsive_min` `responsive_min` int(11) NOT NULL AFTER `popup_padding`,
        CHANGE `responsive_max` `responsive_max` int(11) NOT NULL AFTER `responsive_min`,
        CHANGE `display_url_string` `display_url_string` varchar(150) COLLATE 'utf8_general_ci' NOT NULL AFTER `responsive_max`,
        CHANGE `display_referrer_string` `display_referrer_string` varchar(150) COLLATE 'utf8_general_ci' NOT NULL AFTER `display_url_string`";

    Db::getInstance()->execute($sql);

    return true;
}

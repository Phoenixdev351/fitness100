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

function upgrade_module_1_1_0()
{
    Db::getInstance()->execute("
        ALTER TABLE `"._DB_PREFIX_."advancedpopupcreator_configuration`
        ADD `display_on_click` tinyint(1) NOT NULL AFTER `display_on_exit`,
        ADD `display_on_click_selector` VARCHAR(150) NULL AFTER `display_on_click`,
        ADD `blur_background` tinyint(1) NOT NULL AFTER `close_on_background`,
        ADD `open_effect` int(11) NOT NULL AFTER `blur_background`,
        ADD `close_effect` int(11) NOT NULL AFTER `open_effect`,
        ADD `position` int(11) NOT NULL AFTER `close_effect`,
        ADD `dont_display_again` tinyint(1) NOT NULL AFTER `secs_to_close`,
        ADD `languages` TEXT NULL AFTER `cms`,
        ADD `cart_amount` TINYINT(1) NOT NULL AFTER `position`,
        ADD `cart_amount_from` DECIMAL(20,2) NULL AFTER `cart_amount`,
        ADD `cart_amount_to` DECIMAL(20,2) NULL AFTER `cart_amount_from`,
        ADD `color_background` VARCHAR(32) NULL AFTER `blur_background`,
        ADD `display_mobile` TINYINT(1) NOT NULL AFTER `cart_amount_to`,
        ADD `display_tablet` TINYINT(1) NOT NULL AFTER `display_mobile`,
        ADD `display_desktop` TINYINT(1) NOT NULL AFTER `display_tablet`;
        ");

    Db::getInstance()->execute("
        UPDATE `"._DB_PREFIX_."advancedpopupcreator_configuration`
        SET `display_mobile` = 1, `display_tablet` = 1, `display_desktop` = 1, `position` = 5;
    ");

    Db::getInstance()->execute("
        ALTER TABLE `"._DB_PREFIX_."advancedpopupcreator_configuration_lang`
        ADD `image_background` VARCHAR(150) NULL AFTER `content`,
        ADD `display_url_string` VARCHAR(150) NULL AFTER `responsive_max`,
        ADD `display_referrer_string` VARCHAR(150) NULL AFTER `display_url_string`,
        ADD `display_ip_string` VARCHAR(150) NULL AFTER `display_referrer_string`;
    ");

    Db::getInstance()->execute("
        UPDATE `"._DB_PREFIX_."advancedpopupcreator_configuration`
        SET `date_init` = '".date('Y-m-d H:i:s', 0)."'
        WHERE `date_init` = '0000-00-00 00:00:00';
    ");

    Db::getInstance()->execute("
        UPDATE `"._DB_PREFIX_."advancedpopupcreator_configuration`
        SET `date_end` = '".date('Y-m-d H:i:s', 0)."'
        WHERE `date_end` = '0000-00-00 00:00:00';
    ");

    Db::getInstance()->execute("
        RENAME TABLE `"._DB_PREFIX_."advancedpopupcreator_configuration` TO `"._DB_PREFIX_."advancedpopup`;
        RENAME TABLE `"._DB_PREFIX_."advancedpopupcreator_configuration_lang` TO `"._DB_PREFIX_."advancedpopup_lang`;
        ALTER TABLE `"._DB_PREFIX_."advancedpopup`
        CHANGE `id_advancedpopupcreator_configuration` `id_advancedpopup` int(10) unsigned NOT NULL AUTO_INCREMENT FIRST;
        ALTER TABLE `"._DB_PREFIX_."advancedpopup_lang`
        CHANGE `id_advancedpopupcreator_configuration` `id_advancedpopup` int(10) unsigned NOT NULL AUTO_INCREMENT FIRST;
    ");

    return true;
}

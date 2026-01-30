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

function upgrade_module_1_0_4($module)
{
    $module->registerHook('header');
    $module->registerHook('footer');

    Db::getInstance()->execute(
        'ALTER TABLE `'.pSQL(_DB_PREFIX_.$module->name).'_configuration`
        ADD `name` VARCHAR(150) NOT NULL AFTER `id_shop`,
        ADD `display_on_load` tinyint(1) NOT NULL AFTER `active`,
        ADD `locked` tinyint(1) NOT NULL AFTER `priority`,
        CHANGE `display_after_cart` `display_after_cart` tinyint(1) NOT NULL AFTER `display_on_load`;'
    );

    $popups = Db::getInstance()->executeS(
        'SELECT *
        FROM `'.pSQL(_DB_PREFIX_.$module->name).'_configuration_lang`
        WHERE `id_lang` = '.(int)Configuration::get('PS_LANG_DEFAULT')
    );

    Db::getInstance()->execute(
        'ALTER TABLE `'.pSQL(_DB_PREFIX_.$module->name).'_configuration_lang`
        ADD `css_class` VARCHAR(150) NULL AFTER `name`;'
    );

    Db::getInstance()->execute(
        'ALTER TABLE `'.pSQL(_DB_PREFIX_.$module->name).'_configuration_lang`
        DROP COLUMN `name`;'
    );

    foreach ($popups as $popup) {
        Db::getInstance()->execute(
            'UPDATE `'.pSQL(_DB_PREFIX_.$module->name).'_configuration`
            SET `name` = \''.pSQL($popup['name']).'\'
            WHERE `id_advancedpopup` = '.(int)$popup['id_advancedpopup']
        );
    }

    return $module;
}

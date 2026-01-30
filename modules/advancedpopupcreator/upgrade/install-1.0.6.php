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

function upgrade_module_1_0_6($module)
{
    $result = true;

    $result &= Db::getInstance()->execute(
        'ALTER TABLE `'.pSQL(_DB_PREFIX_.$module->name).'_configuration`
        ADD `display_on_exit` tinyint(1) NOT NULL AFTER `display_after_cart`;'
    );

    $fields = array('controller_exceptions', 'groups', 'customers', 'products', 'countries', 'zones', 'categories', 'categories_selected', 'manufacturers', 'suppliers');
    foreach ($fields as $field) {
        $result &= Db::getInstance()->execute(
            'UPDATE `'.pSQL(_DB_PREFIX_.$module->name).'_configuration`
            SET `'.$field.'` = \'\'
            WHERE `'.$field.'` = \'all\';'
        );
    }

    return $module;
}

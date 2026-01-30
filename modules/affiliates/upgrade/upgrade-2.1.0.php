<?php
/**
* DISCLAIMER
*
* Do not edit or add to this file.
* You are not authorized to modify, copy or redistribute this file.
* Permissions are reserved by FME Modules.
*
*  @author    FMM Modules
*  @copyright FME Modules 2021
*  @license   Single domain
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_1_0($module)
{
    $return = true;
    $return &= $module->alterKey();
    $return &= $module->registerHook(array(
        'displayAdminListBefore',
        'displayAdminListAfter',
        'displayProductButtons',
        'CreateAccountForm',
        'displayRightColumnProduct'
    ));

    if (false === tableExists('payment_method')) {
        $return &= createPmTables('payment_method');
    }

    if (false === tableExists('payment_method_lang')) {
        $return &= createPmTables('payment_method_lang');
    }

    $return &= (bool)Configuration::updateValue(
        'AFFILIATE_PROGRAM_ORDERS',
        0,
        false,
        Context::getContext()->shop->id_shop_group,
        Context::getContext()->shop->id
    );
    $return &= (bool)Configuration::updateValue(
        'REFERRAL_REWARD_VORDERS',
        1,
        false,
        Context::getContext()->shop->id_shop_group,
        Context::getContext()->shop->id
    );
    $return &= (bool)Configuration::updateValue(
        'REFERRAL_REWARD_SPPRODUCTS',
        1,
        false,
        Context::getContext()->shop->id_shop_group,
        Context::getContext()->shop->id
    );

    return $return;
}

function tableExists($table)
{
    return (bool)Db::getInstance()->executeS('SHOW TABLES LIKE \''._DB_PREFIX_.pSQL($table).'\'');
}

function createPmTables($table)
{
    $sql = '';
    switch ($table) {
        case 'payment_method':
            $sql = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'payment_method(
                `id_payment_method`             int(11) NOT NULL auto_increment,
                `payment_name`                  varchar(128),
                `date_add`                      datetime default NULL,
                `date_upd`                      datetime default NULL,
                PRIMARY KEY                     (`id_payment_method`)
                ) ENGINE=InnoDB                 AUTO_INCREMENT=4 DEFAULT CHARSET=utf8';
            break;
        case 'payment_method_lang':
            $sql = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'payment_method_lang(
                `id_payment_method`             int(11) NOT NULL,
                `id_lang`                       int(11) default 1,
                `payment_description`           varchar(250),
                PRIMARY KEY                     (`id_payment_method`, `id_lang`)
                ) ENGINE=InnoDB                 DEFAULT CHARSET=utf8';
            break;
    }

    $return = true;
    if (!empty($sql)) {
        $return &= Db::getInstance()->execute($sql);
    }
    return $return;
}

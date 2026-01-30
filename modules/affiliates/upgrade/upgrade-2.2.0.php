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

function upgrade_module_2_2_0($module)
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

    if (false === $module->tableExists('payment_method')) {
        $return &= $module->createPmTables('payment_method');
    }

    if (false === $module->tableExists('payment_method_lang')) {
        $return &= $module->createPmTables('payment_method_lang');
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

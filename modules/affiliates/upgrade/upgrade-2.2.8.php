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

function upgrade_module_2_2_8($module)
{
    $result = true;
    $result &= (bool) Configuration::updateValue(
        'AFFILIATE_SHOPS',
        Context::getContext()->shop->id
    );
    
    $result &= $module->removeOldFiles();
    // clear cache after removing overrides
    if ($module->uninstallOverrides()) {
        $module->installOverrides();
        $module->removeCache();
        $result &= true;
    }
    
    return $result;
}

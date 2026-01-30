<?php
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */

if (!defined('_PS_VERSION_')) { exit; }

function upgrade_module_1_5_8($module)
{
    try{
        Configuration::updateGlobalValue('ETS_AUTO_DELETE_CACHE_WHEN_UPDATE_OBJ',1);
        $module->registerHook('actionCartUpdateQuantityBefore');
        $module->registerHook('actionObjectProductInCartDeleteAfter');
        Db::getInstance()->execute(' ALTER TABLE `ets_superspeed_cache_page` DROP INDEX `date_add`');
        Ets_superspeed_defines::createIndexDataBase();
        $module->uninstallOverrides();
        $module->installOverrides();
        if(file_exists((__FILE__) . '/../cronjob_log.txt'))
            @unlink((__FILE__) . '/../cronjob_log.txt');
        return true;
    }
    catch(Exception $ex){
        if($ex){
            //
        }
    }
    return true;
}
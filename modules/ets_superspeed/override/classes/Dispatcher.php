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
class Dispatcher extends DispatcherCore
{
    public function dispatch() {
        if(Module::isEnabled('ets_superspeed')) {
            $start_time = microtime(true);
            Context::getContext()->ss_start_time = $start_time;
            if (@file_exists(dirname(__FILE__) . '/../../modules/ets_superspeed/ets_superspeed.php')) {
                require_once(dirname(__FILE__) . '/../../modules/ets_superspeed/ets_superspeed.php');
                if ($cache = Ets_superspeed::displayContentCache(true)) {
                    echo $cache;
                    exit;
                }
            }
        }
        parent::dispatch();
    }
}
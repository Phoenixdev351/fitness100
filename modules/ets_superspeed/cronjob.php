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

// if (!defined('_PS_VERSION_')) { exit; }

$_SERVER['REQUEST_METHOD'] ='POST';
$_GET['fc'] = 'module';
$_GET['module'] = 'ets_superspeed';
$_GET['controller'] = 'cron';
$_GET['cron'] =1;
if(php_sapi_name() == "cli"){
    foreach ($argv as $item) {
        if(strpos($item, 'token=') !== false){
            $str = explode('token=', $item);
            $_GET['token'] = end($str);
        }
    }
}
require_once dirname(__FILE__) . '/../../index.php';

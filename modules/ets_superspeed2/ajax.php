<?php
/**
 * 2007-2018 ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 * @author ETS-Soft <etssoft.jsc@gmail.com>
 * @copyright  2007-2018 ETS-Soft
 * @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */
 
if (!defined('_PS_ADMIN_DIR_')) {
    define('_PS_ADMIN_DIR_', getcwd());
}
include(_PS_ADMIN_DIR_ . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/ajax_init.php');
$ets_superspeed = Module::getInstanceByName('ets_superspeed');
if(Tools::getValue('token')!=md5($ets_superspeed->id))
   exit;
ini_set('memory_limit', '1280M');
ini_set('max_execution_time', '300');
ini_set('upload_max_filesize', '128M');
ini_set('post_max_size', '128M');
$context = Context::getContext();

if(Tools::isSubmit('btnSubmitImageOptimize') || Tools::isSubmit('btnSubmitImageAllOptimize') || Tools::isSubmit('submitUploadImageSave')||Tools::isSubmit('submitUploadImageCompress') || Tools::isSubmit('submitBrowseImageOptimize') || Tools::isSubmit('btnSubmitCleaneImageUnUsed'))
    $ets_superspeed->_postImage();
if(Tools::isSubmit('btnSubmitPageCache') || Tools::isSubmit('clear_all_page_caches') || Tools::isSubmit('btnSubmitPageCacheDashboard') || Tools::isSubmit('btnRefreshSystemAnalyticsNew'))
    $ets_superspeed->_postPageCache();
if(Tools::isSubmit('btnSubmitMinization'))
    $ets_superspeed->_postMinization();
if(Tools::isSubmit('btnSubmitGzip'))
    $ets_superspeed->_postGzip();
if(Tools::isSubmit('submitDeleteSystemAnalytics'))
{
    $ets_superspeed->submitDeleteSystemAnalytics();
}

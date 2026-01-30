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
class AdminSuperSpeedAjaxController extends ModuleAdminController
{
    public function init()
    {

       parent::init();
       if(Tools::isSubmit('changeSubmitImageOptimize') || Tools::isSubmit('btnSubmitImageOptimize') || Tools::isSubmit('btnSubmitImageAllOptimize') || Tools::isSubmit('submitUploadImageSave')||Tools::isSubmit('submitUploadImageCompress') || Tools::isSubmit('submitBrowseImageOptimize') || Tools::isSubmit('btnSubmitCleaneImageUnUsed'))
            $this->module->_postImage();
       if(Tools::isSubmit('btnSubmitPageCache') || Tools::isSubmit('clear_all_page_caches') || Tools::isSubmit('btnSubmitPageCacheDashboard') || Tools::isSubmit('btnRefreshSystemAnalyticsNew'))
            $this->module->_postPageCache();
       if(Tools::isSubmit('btnSubmitMinization'))
            $this->module->_postMinization();
       if(Tools::isSubmit('btnSubmitGzip'))
            $this->module->_postGzip();
       if(Tools::isSubmit('submitDeleteSystemAnalytics'))
       {
            $this->module->submitDeleteSystemAnalytics();
       }
       if(Tools::isSubmit('submitRunCronJob'))
       {
            $this->module->autoRefreshCache();
       }
    }
}
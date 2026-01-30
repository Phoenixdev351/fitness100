<?php
/**
 * 2007-2019 ETS-Soft ETS-Soft
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
 *  @author ETS-Soft <etssoft.jsc@gmail.com>
 *  @copyright  2007-2019 ETS-Soft ETS-Soft
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

if (!defined('_PS_VERSION_'))
    	exit;
include_once(_PS_MODULE_DIR_.'ets_oneclicktomigrate/classes/ExportHistory.php');
include_once(_PS_MODULE_DIR_.'ets_oneclicktomigrate/classes/ExtraExport.php');
include_once(_PS_MODULE_DIR_.'ets_oneclicktomigrate/classes/DataExport.php');
class AdminDataMasterExportController extends ModuleAdminController
{
    public $_errors=array();
    public $_module;
    public function __construct()
    {
       parent::__construct();
       $this->bootstrap = true;
       $this->_module = Module::getInstanceByName('ets_oneclicktomigrate');
       if(Tools::isSubmit('submitExport'))
       {
            $this->_module->processExport();
       }
       if(Tools::isSubmit('ajax_percentage_export'))
       {
            $this->_module->ajaxPercentageExport();
       }
   }
   public function initContent()
   {
        parent::initContent();
   }
   public function renderList()
   {
        $step = (int)Tools::getValue('step');
        $this->context->smarty->assign(
            array(
                'step' => (int)$step?(int)$step:1,
                'errors' => $this->_errors,
                'ets_datamaster_export' =>Tools::isSubmit('submitExport')? Tools::getValue('data_export',array()):explode(',',Configuration::get('ETS_DATAMASTER_EXPORT')),
                'ets_datamaster_format' => Configuration::get('ETS_DATAMASTER_FORMAT'),
                'divide_file' => Configuration::get('ETS_DATAMASTER_DIVIDE_FILE'),
                'number_record' => (int)Configuration::get('ETS_DT_NUMBER_RECORD'),
                'link' => $this->context->link,
            )
       );
       $this->_module->processAssignExport();
       return $this->module->display(_PS_MODULE_DIR_.$this->module->name.DIRECTORY_SEPARATOR.$this->module->name.'.php', 'export.tpl');
   }
}

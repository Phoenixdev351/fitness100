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

if (!defined('_PS_ADMIN_DIR_')) {
    define('_PS_ADMIN_DIR_', getcwd());
}
if(!defined('PS_INSTALLATION_IN_PROGRESS'))
{
    define('PS_INSTALLATION_IN_PROGRESS',1);
}
include(_PS_ADMIN_DIR_.'/../../config/config.inc.php');
include(dirname(__FILE__).'/ajax_init.php');
@ini_set('display_errors', 'off');
ini_set('memory_limit','1600M');
ini_set('max_execution_time','300');
$context = Context::getContext();
$ets_datamaster = Module::getInstanceByName('ets_oneclicktomigrate');

if (Shop::getContext() == Shop::CONTEXT_ALL && Configuration::getGlobalValue('PS_SMARTY_CACHE')) {
    Configuration::updateGlobalValue('PS_SMARTY_CACHE', 0);
} elseif (Configuration::get('PS_SMARTY_CACHE')){
    Configuration::updateValue('PS_SMARTY_CACHE', 0);
}

if(Tools::getValue('presconnector') && Tools::getValue('zip_file_name')&&Tools::getValue('ajaxPercentageExport')&&Tools::getValue('link_site'))
{
    $url = Tools::getValue('link_site').(strpos(Tools::getValue('link_site'),'?')===false ? '?' :'&').'presconnector=1&ajaxPercentageExport=1&zip_file_name='.Tools::getValue('zip_file_name');
    $content = DataImport::file_get_contents($url);
    die($content);
}
if(Tools::getValue('presconnector') && Tools::getValue('pres2prestocken') && Tools::getValue('zip_file_name') && Tools::getValue('link_site'))
{
    $url = Tools::getValue('link_site').(strpos(Tools::getValue('link_site'),'?')===false ? '?' :'&').'presconnector=1&pres2prestocken='.Tools::getValue('pres2prestocken').'&zip_file_name='.Tools::getValue('zip_file_name');
    $content = DataImport::file_get_contents($url);
    if($content)
    {
        $content= Tools::jsonDecode($content);
        if(!is_array($content))
            $content =(array)$content;
        if(is_array($content))
        {
            if(isset($content['link_site_connector']) && $content['link_site_connector'])
            {
                die(Tools::jsonEncode($content));
            }
        }
    }
    die(
        Tools::jsonEncode(
            array(
                'tieptuc'=>true,
            )
        )
    );
}
if($context->employee->id && Tools::getValue('token')==Tools::getAdminTokenLite('AdminModules'))
{
    $ets_datamaster = Module::getInstanceByName('ets_oneclicktomigrate');
    if(Tools::isSubmit('submitExport'))
    {
        $ets_datamaster->processExport();
    }
    if(Tools::isSubmit('ajax_percentage_export'))
    {
        $ets_datamaster->ajaxPercentageExport();
    }
    include(dirname(__FILE__).'/importer.php');
}else
{
    $ets_datamaster = Module::getInstanceByName('ets_oneclicktomigrate');
    $errors=array();
    $errors[]=$ets_datamaster->l('You have been logged out. Please login then resume the import');
    die(
        Tools::jsonEncode(
            array(
                'error'=>true,
                'errors'=>$ets_datamaster->displayError($errors),
            )
        )
    );
}
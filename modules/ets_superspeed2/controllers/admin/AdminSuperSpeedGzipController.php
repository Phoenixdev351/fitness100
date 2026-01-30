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
class AdminSuperSpeedGzipController extends ModuleAdminController
{
    public function __construct()
    {
       parent::__construct();
       $this->context= Context::getContext();
       $this->bootstrap = true;
    }
    public function initContent()
    {
        parent::initContent();
    }
    public function renderList()
    {
        $this->context->smarty->assign(
            array(
                'html_form' =>$this->renderFormGzip(),
            )
        );
        return $this->module->display(_PS_MODULE_DIR_.$this->module->name.DIRECTORY_SEPARATOR.$this->module->name.'.php', 'admin.tpl');
    }
    public function renderFormGzip()
    {
        $config_gzip = Ets_superspeed_defines::getInstance()->getFieldConfig('_config_gzip');
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('GZIP & browser cache'),
                    'icon' => 'icon-envelope'
                ),
                'input' => $config_gzip,
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->id = 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmitGzip';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminSuperSpeedGzip', false);
        $helper->token = Tools::getAdminTokenLite('AdminSuperSpeedGzip');
        $helper->module = $this->module;
        $helper->tpl_vars = array(
            'fields_value' => $this->module->getFieldsValues($config_gzip),
            'no_mod_deflate' => Tools::strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate') === false ? true : false,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        return $helper->generateForm(array($fields_form));
    }
}
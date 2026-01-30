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
class AdminSuperSpeedMinizationController extends ModuleAdminController
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
                'html_form' =>$this->rederFormMinization(),
            )
        );
        return $this->module->display(_PS_MODULE_DIR_.$this->module->name.DIRECTORY_SEPARATOR.$this->module->name.'.php', 'admin.tpl');
    }
    public function rederFormMinization()
    {
        $inputs = Ets_superspeed_defines::getInstance()->getFieldConfig('_minization');
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Server cache and minification'),
                    'icon' => 'icon-envelope'
                ),
                'input' => $inputs,
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );
        $id_carrier = (int)Tools::getValue('id_carrier');
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->id = $id_carrier;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmitMinization';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminSuperSpeedMinization', false);
        $helper->token = Tools::getAdminTokenLite('AdminSuperSpeedMinization');
        $helper->module = $this->module;
        $helper->tpl_vars = array(
            'fields_value' => $this->getMinizationFieldsValues($inputs),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        return $helper->generateForm(array($fields_form));
    }

    public function getMinizationFieldsValues($inputs)
    {
        return array_merge($this->module->getFieldsValues($inputs),array(
            'ETS_SPEED_SMARTY_CACHE' => Configuration::get('PS_SMARTY_FORCE_COMPILE') == 2 ? false : true,
            )
        );
    }
}
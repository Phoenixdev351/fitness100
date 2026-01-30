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
class AdminSuperSpeedHelpsController extends ModuleAdminController
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
        if(Tools::isSubmit('update_tocken_sp'))
        {
            $ETS_SPEED_SUPER_TOCKEN = Tools::getValue('ETS_SPEED_SUPER_TOCKEN');
            if($ETS_SPEED_SUPER_TOCKEN)
            {
                if(Tools::strlen($ETS_SPEED_SUPER_TOCKEN)>=6 && Validate::isTableOrIdentifier($ETS_SPEED_SUPER_TOCKEN))
                {
                    Configuration::updateGlobalValue('ETS_SPEED_SUPER_TOCKEN',$ETS_SPEED_SUPER_TOCKEN);
                    die(
                        json_encode(
                            array(
                                'success' => $this->module->displaySuccessMessage($this->module->l('Secure token updated successfully')),
                                'link_cronjob'=> $this->context->link->getAdminLink('AdminSuperSpeedAjax').'&submitRunCronJob=1&token=' . $ETS_SPEED_SUPER_TOCKEN,
                            )
                        )
                    );
                }
                else
                {
                    die(
                        json_encode(
                            array(
                                'errors' => $this->module->displayError($this->module->l('Secure token is not valid')),
                            )
                        )
                    );
                }
            }
            else
            {
                die(
                    json_encode(
                        array(
                            'errors' => $this->module->displayError($this->module->l('Token is required')),
                        )
                    )
                );
            }
        }
    }
    public function renderList()
    {
        $this->context->smarty->assign(
            array(
                'html_form' =>$this->renderSpeedHelps(),
            )
        );
        return $this->module->display(_PS_MODULE_DIR_.$this->module->name.DIRECTORY_SEPARATOR.$this->module->name.'.php', 'admin.tpl');
    }
    public function renderSpeedHelps()
    {
        $cronjob_last = '';
        if (($cronjob_time = Configuration::getGlobalValue('ETS_SPEED_TIME_RUN_CRONJOB')) && Validate::isDate($cronjob_time) ) {
            $last_time = strtotime($cronjob_time);
            $time = strtotime(date('Y-m-d H:i:s')) - $last_time;
            if($time  <= 86400) {
                if ($hours = floor($time / 3600)) {
                    $cronjob_last .= $hours . ' ' . $this->l('hours') . ' ';
                    $time = $time % 3600;
                }
                if ($minutes = floor($time / 60)) {
                    $cronjob_last .= $minutes . ' ' . $this->l('minutes') . ' ';
                    $time = $time % 60;
                }
                if ($time)
                    $cronjob_last .= $time . ' ' . $this->l('seconds') . ' ';
                $cronjob_last .= $this->l('ago');
            }
        }
        $this->context->smarty->assign(
            array(
                'link_cronjob' => $this->module->getBaseLink() . '/modules/' . $this->module->name . '/cronjob.php?token=' . Configuration::getGlobalValue('ETS_SPEED_SUPER_TOCKEN'),
                'link_cronjob_run' => $this->context->link->getAdminLink('AdminSuperSpeedAjax').'&submitRunCronJob=1&token=' . Configuration::getGlobalValue('ETS_SPEED_SUPER_TOCKEN'),
                'dir_cronjob' => _PS_ROOT_DIR_ . '/modules/'.$this->module->name.'/cronjob.php',
                'ETS_SPEED_SUPER_TOCKEN' => Configuration::getGlobalValue('ETS_SPEED_SUPER_TOCKEN'),
                'link_base' => $this->module->getBaseLink(),
                'cronjob_last' => trim($cronjob_last, ', '),
                'php_path' => (defined('PHP_BINDIR') && PHP_BINDIR && is_string(PHP_BINDIR) ? PHP_BINDIR.'/' : '').'php',
            )
        );
        return $this->module->display(_PS_MODULE_DIR_.$this->module->name.DIRECTORY_SEPARATOR.$this->module->name.'.php', 'helps.tpl');
    }
}
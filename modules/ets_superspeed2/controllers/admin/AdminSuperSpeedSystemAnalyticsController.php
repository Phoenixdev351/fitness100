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

/**
 * Class AdminSuperSpeedSystemAnalyticsController
 * @property Ets_superspeed $module
 */
class AdminSuperSpeedSystemAnalyticsController extends ModuleAdminController
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
        $change_register_option = (int)Tools::getValue('change_register_option');
        if(Tools::isSubmit('change_register_option') && ($id_module = (int)Tools::getValue('id_module')) && ($hook_name = Tools::getValue('hook_name')) && Validate::isHookName($hook_name))
        {
            $id_hook = Hook::getIdByName($hook_name);
            if($id_hook && $id_module && Validate::isUnsignedId($id_hook) && Validate::isUnsignedId($id_module))
            {
                Ets_superspeed_defines::changeRegisterHook($change_register_option,$hook_name,$id_module,$id_hook);
                if(Tools::isSubmit('ajax'))
                {
                    die(
                        json_encode(
                            array(
                                'success' =>$change_register_option ? $this->l('Hook registered successfully. Clear cache to see changes in front office.'): $this->l('Hook unregistered'),
                                'url'=> $this->context->link->getAdminLink('AdminSuperSpeedSystemAnalytics').'&change_register_option='.($change_register_option?'0':'1').'&id_module='.(int)$id_module.'&hook_name='.$hook_name,
                            )
                        )
                    );
                }
                else
                    Tools::redirectAdmin($this->context->link->getAdminLink('AdminSuperSpeedSystemAnalytics').'&conf='.($change_register_option ? 16:17));
            }
            else
            {
                if(Tools::isSubmit('ajax'))
                    die(
                        json_encode(
                            array(
                                'error' => $this->l('Module or hook does not exist'),
                            )
                        )
                    );
                else   
                    $this->context->controller->errors[] = $this->l('Module or hook does not exist');
            }
        }
        if(Tools::isSubmit('paggination_ajax'))
        {
            die(
                json_encode(
                    array(
                        'html' =>$this->renderSpeedSystemAnalytics(),
                    )
                )
            );
        }
        if(Tools::isSubmit('ETS_SPEED_RECORD_MODULE_PERFORMANCE') && !Tools::isSubmit('submitFilterModule') && !Tools::isSubmit('submitResetModule'))
        {
            $ETS_SPEED_RECORD_MODULE_PERFORMANCE = (int)Tools::getValue('ETS_SPEED_RECORD_MODULE_PERFORMANCE');
            Configuration::updateValue('ETS_SPEED_RECORD_MODULE_PERFORMANCE',$ETS_SPEED_RECORD_MODULE_PERFORMANCE);
            die(
                json_encode(
                    array(
                        'success' => $this->l('Updated successfully'),
                    )
                )
            );
        }
    }
    public function renderList()
    {
        $this->context->smarty->assign(
            array(
                'site_url_home' => $this->module->getLinkHomePage(),
                'html_form' =>$this->renderSpeedSystemAnalytics(),
            )
        );
        return $this->module->display(_PS_MODULE_DIR_.$this->module->name.DIRECTORY_SEPARATOR.$this->module->name.'.php', 'admin.tpl');
    }
    public function renderSpeedSystemAnalytics()
    {
        $sql_filter = '';
        $orderby = Tools::getValue('Orderby', 'pht.time');
        if(!in_array($orderby,array('pht.time','pht.hook_name','pht.date_add','phm.id_module')))
            $orderby ='pht.time';
        $orderway = Tools::strtolower(Tools::getValue('Orderway', 'desc'));
        if(!in_array($orderway,array('desc','asc')))
            $orderway ='desc';
        if (Tools::isSubmit('submitFilterModule')) {
            $filter = array();
            if (($module_name = trim(Tools::getValue('module_name'))) || $module_name!='') {
                $filter['module_name'] = $module_name;
                if(Validate::isCleanHtml($module_name))
                    $sql_filter .= ' AND m.name like "%' . pSQL($module_name) . '%"';

            }
            if (($hook_name = trim(Tools::getValue('hook_name'))) || $hook_name!='') {
                $filter['hook_name'] = $hook_name;
                if(Validate::isCleanHtml($hook_name))
                    $sql_filter .= ' AND pht.hook_name like "%' . pSQL($hook_name) . '%"';
            }
            if (trim(Tools::isSubmit('disabled'))) {
                $filter['disabled'] = Tools::getValue('disabled');
                if ($filter['disabled'] != '' && Validate::isInt($filter['disabled'])) {
                    if ($filter['disabled'] == 1) {
                        $sql_filter .= ' AND phm.id_module is not null';
                    } else {
                        unset($filter['disabled']);
                        $sql_filter .= ' AND phm.id_module is null';
                    }
                }
            }
            if (($date_add_from = Tools::getValue('date_add_from')) || $date_add_from!='') {
                $filter['date_add_from'] = $date_add_from;
                if(Validate::isDate($date_add_from))
                    $sql_filter .= ' AND pht.date_add >= "' . pSQL($date_add_from) . ' 00:00:00"';
            }
            if (($date_add_to = Tools::getValue('date_add_to')) || $date_add_to!='') {
                $filter['date_add_to'] = $date_add_to;
                if(Validate::isDate($date_add_to))
                    $sql_filter .= ' AND pht.date_add <= "' . pSQL($date_add_to) . ' 23:59:59"';
            }
            if (($module_page = Tools::getValue('module_page')) && $module_page!='') {
                $filter['module_page'] = $module_page;
                if(Validate::isCleanHtml($module_page))
                    $sql_filter .= ' AND pht.page LIKE "%' . pSQL($module_page) . '%"';
            }
            if (($module_time_min = Tools::getValue('module_time_min')) || $module_time_min!='' ) {
                $filter['module_time_min'] = (float)$module_time_min;
                $sql_filter .= ' AND pht.time >="' . ((float)$module_time_min / 1000) . '"';
            }
            if (($module_time_max =Tools::getValue('module_time_max')) || $module_time_max!='' ) {
                $filter['module_time_max'] = (float)$module_time_max;
                $sql_filter .= ' AND pht.time <= "' . ((float)$module_time_max / 1000) . '"';
            }
            if ($filter) {
                $filter['submitFilterModule'] = 1;
                $this->context->smarty->assign(
                    array(
                        'filter' => $filter,
                    )
                );
            }
        } else
            $sql_filter = 'AND phm.id_module is null';
        $page = (int)Tools::getValue('page');
        if($page<1)
            $page =1;
        $totalRecords = (int)Ets_superspeed_defines::getHookTimeByFilter($sql_filter,true);
        $paggination = new Ets_superspeed_pagination_class();
        $paggination->total = $totalRecords;
        $paggination->url = $this->context->link->getAdminLink('AdminSuperSpeedSystemAnalytics', true) . '&page=_page_' . (isset($filter) ? $this->module->getFilterValues($filter) : '') . '&Orderby=' . $orderby . '&OrderWay=' . $orderway;
        $paggination->limit = 20;
        $totalPages = ceil($totalRecords / $paggination->limit);
        if ($page > $totalPages)
            $page = $totalPages;
        $paggination->page = $page;
        $start = (int)$paggination->limit * ((int)$page - 1);
        if ($start < 0)
            $start = 0;
        $paggination->text = $this->l('Showing {start} to {end} of {total} ({pages} Pages)');
        $paggination->style_links = $this->l('links');
        $module_hooks = Ets_superspeed_defines::getHookTimeByFilter($sql_filter,false,$orderby,$orderway,$start,$paggination->limit);
        if ($module_hooks) {
            foreach ($module_hooks as $key=> &$module_hook) {
                $module = Module::getInstanceById($module_hook['id_module']);
                if($module)
                {
                    $module_hook['display_name'] = $module->displayName;
                    $module_hook['logo'] = $this->module->getBaseLink() . '/modules/' . $module->name . '/logo.png';
                }
                else
                    unset($module_hooks[$key]);

            }
        }
        $tab_current = Tools::getValue('tab_current', 'module_performance');
        $this->context->smarty->assign(
            array(
                'module_hooks' => $module_hooks,
                'extra_hooks' => $this->module->getCheckPoints(),
                'orderby' => Validate::isCleanHtml($orderby) ? $orderby:'pht.time',
                'orderway' => in_array($orderway,array('desc','asc')) ? $orderway :'desc',
                'tab_current' => Validate::isCleanHtml($tab_current) ? $tab_current:'module_performance',
                'ETS_SPEED_RECORD_MODULE_PERFORMANCE' => Configuration::get('ETS_SPEED_RECORD_MODULE_PERFORMANCE'),
                'url_base' => $this->context->link->getAdminLink('AdminSuperSpeedSystemAnalytics', true) . '&page=' . (int)$page . (isset($filter) ? $this->module->getFilterValues($filter) : ''),
                'url_base_sort' => $this->context->link->getAdminLink('AdminSuperSpeedSystemAnalytics', true) . '&page=1'. (isset($filter) ? $this->module->getFilterValues($filter) : ''),
                'paggination' => $paggination->render(),
            )
        );
        if (Tools::isSubmit('paggination_ajax')) {
            $this->context->smarty->assign(
                array(
                    'ajax' => 1,
                )
            );
        }
        return $this->module->display(_PS_MODULE_DIR_.$this->module->name.DIRECTORY_SEPARATOR.$this->module->name.'.php', 'system_analytics.tpl');
    }
}
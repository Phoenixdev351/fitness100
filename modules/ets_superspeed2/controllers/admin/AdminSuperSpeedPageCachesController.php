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
 * Class AdminSuperSpeedPageCachesController
 * @property Ets_superspeed $module
 */
class AdminSuperSpeedPageCachesController extends ModuleAdminController
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
        $this->module->_postPageCache();
        $this->_postProcess();
    }
    public function _postProcess()
    {
        if(Tools::isSubmit('deleteLogError'))
        {
            if(Ets_superspeed_cache_page_error::deleteAllLog())
            {
                die(
                    json_encode(
                        array(
                            'success' => $this->l('Deleted successfully'),
                        )
                    )
                );
            }
        }
        if(Tools::isSubmit('enableLogError') && Tools::isSubmit('ETS_SPEED_ENABLE_LOG_CACHE_ERROR'))
        {
            $ETS_SPEED_ENABLE_LOG_CACHE_ERROR = (int)Tools::getValue('ETS_SPEED_ENABLE_LOG_CACHE_ERROR');
            Configuration::updateValue('ETS_SPEED_ENABLE_LOG_CACHE_ERROR',$ETS_SPEED_ENABLE_LOG_CACHE_ERROR);
            die(
                json_encode(
                    array(
                        'success' => $this->l('Updated successfully'),
                    )
                )
            );
        }
        if(Tools::isSubmit('deleteLogClear'))
        {
            if(Ets_superspeed_cache_page_log::deleteAllLog())
            {
                die(
                    json_encode(
                        array(
                            'success' => $this->l('Deleted successfully'),
                        )
                    )
                );
            }
        }
        if(Tools::isSubmit('enableLogClear') && Tools::isSubmit('ETS_SPEED_ENABLE_LOG_CACHE_CLEAR'))
        {
            $ETS_SPEED_ENABLE_LOG_CACHE_CLEAR = (int)Tools::getValue('ETS_SPEED_ENABLE_LOG_CACHE_CLEAR');
            Configuration::updateValue('ETS_SPEED_ENABLE_LOG_CACHE_CLEAR',$ETS_SPEED_ENABLE_LOG_CACHE_CLEAR);
            die(
                json_encode(
                    array(
                        'success' => $this->l('Updated successfully'),
                    )
                )
            );
        }
        if(Tools::isSubmit('deleteAllCache'))
        {
            if(Ets_superspeed_cache_page::deleteAllCache())
            {
                die(
                    json_encode(
                        array(
                            'success' => $this->l('Deleted successfully'),
                        )
                    )
                );
            }
        }
        if (Tools::isSubmit('btnSubmitSuperSpeedException')) {
            $ETS_SPEED_PAGES_EXCEPTION = Tools::getValue('ETS_SPEED_PAGES_EXCEPTION');
            if (Validate::isCleanHtml($ETS_SPEED_PAGES_EXCEPTION)) {
                Configuration::updateValue('ETS_SPEED_PAGES_EXCEPTION', $ETS_SPEED_PAGES_EXCEPTION);
                if ($pages_exception = Configuration::get('ETS_SPEED_PAGES_EXCEPTION')) {
                    $pages_exception = explode("\n", $pages_exception);
                    foreach ($pages_exception as $page_exception) {
                        if ($page_exception)
                            Ets_superspeed_cache_page::deleteByWhere(' AND request_uri LIKE "%' . pSQL($page_exception) . '%"');
                    }
                }
                die(
                    json_encode(
                        array(
                            'success' => $this->l('Updated successfully')
                        )
                    )
                );
            } else {
                die(
                    json_encode(
                        array(
                            'errors' => $this->l('Exception is not valid'),
                        )
                    )
                );
            }
        }
        if (($action = Tools::getValue('action')) &&  ($action == 'add_dynamic_modules' || $action == 'update_dynamic_modules') && ($hook_name = Tools::getValue('hook_name')) && Validate::isHookName($hook_name)) {
            $empty_content = (int)Tools::getValue('empty_content');
            $add = (int)Tools::getValue('add');
            $id_module = (int)Tools::getValue('id_module');
            if(Module::getInstanceById($id_module))
            {
                Ets_superspeed_cache_page::updateDynamicHook($add,$action,$id_module,$hook_name,$empty_content);
                die(
                    json_encode(
                        array(
                            'success' => $this->module->displaySuccessMessage($this->l('Successfully saved')),
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
                'html_form' =>$this->renderFormPageCache(),
            )
        );
        return $this->module->display(_PS_MODULE_DIR_.$this->module->name.DIRECTORY_SEPARATOR.$this->module->name.'.php', 'admin.tpl');
    }
    public function renderFormPageCache()
    {
        $inputs = Ets_superspeed_defines::getInstance()->getFieldConfig('_page_caches');
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Page cache'),
                    'icon' => 'icon-envelope'
                ),
                'input' => $inputs,
            ),
        );
        if (!is_dir(_ETS_SPEED_CACHE_DIR_))
            mkdir(_ETS_SPEED_CACHE_DIR_, 0777, true);
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->id = (int)Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmitPageCache';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminSuperSpeedPageCaches', false);
        $helper->token = Tools::getAdminTokenLite('AdminSuperSpeedPageCaches');
        $helper->module = $this->module;
        $install_logs = file_exists(dirname(__FILE__) . '/../../cache/install.log') ? array_keys(json_decode(Tools::file_get_contents(dirname(__FILE__) . '/../../cache/install.log'), true)) : false;
        if ($install_logs) {
            foreach ($install_logs as $key => $log)
                if (in_array($log, array('AdminCategoriesController', 'AdminManufacturersController', 'AdminSuppliersController')))
                    unset($install_logs[$key]);
                else
                    $install_logs[$key] .= '.php';
        }
        $current_tab = Tools::getValue('current_tab', 'page_setting');
        $helper->tpl_vars = array(
            'file_caches' => $this->displayPageCaches(),
            'file_no_caches' => $this->displayPageNoCaches(),
            'list_log_clear_history' => $this->displayListLogClearHistory(),
            'fields_value' => $this->getCachePageFieldsValues($inputs),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'configTabs' => Ets_superspeed_defines::getInstance()->getFieldConfig('_cache_page_tabs'),
            'current_tab' => Validate::isCleanHtml($current_tab) ? $current_tab :'page_setting',
            'is_dir_cache' => is_dir(_ETS_SPEED_CACHE_DIR_),
            'dir_cache' => _PS_CACHE_DIR_,
            'sp_dir_cache' => _ETS_SPEED_CACHE_DIR_,
            'dir_override' => _PS_OVERRIDE_DIR_,
            'sp_dir_override' => dirname(__FILE__) . '/../../override/',
            'is_blog_installed' => $this->module->isblog,
            'install_log_file_url' => dirname(__FILE__) . '/../../cache/install.log',
            'install_logs' => $install_logs ? implode(', ', $install_logs) : false,
        );
        return $helper->generateForm(array($fields_form));
    }
    public function displayListLogClearHistory()
    {
        $fields_list = array(
            'page_name' => array(
                'title' => $this->l('Page'),
                'type' => 'text',
                'sort' => true,
                'filter' => true,
                'strip_tag' => false,
            ),
            'reason' => array(
                'title' => $this->l('Reason'),
                'type' => 'text',
                'sort' => true,
                'filter' => true,
            ),
            'date_add' => array(
                'title' => $this->l('Date deleted'),
                'type' => 'date',
                'sort' => true,
                'filter' => true,
            ),
        );

        $page = max(1, (int)Tools::getValue('page'));
        $show_reset = false;
        $filter = [];
        if (Tools::isSubmit('ets_sp_submit_clear_history')) {
            $page_name = trim(Tools::getValue('page_name'));
            if ($page_name && Validate::isCleanHtml($page_name)) {
                $filter[] = 'page LIKE ' . pSQL('%' . $page_name . '%');
                $show_reset = true;
            }
            $reason = trim(Tools::getValue('reason'));
            if ($reason && Validate::isCleanHtml($reason)) {
                $filter[] = 'reason LIKE ' . pSQL('%' . $reason . '%');
                $show_reset = true;
            }

            $date_add_min = Tools::getValue('date_add_min');
            if ($date_add_min && Validate::isDate($date_add_min)) {
                $filter[] = 'date_add >= ' . pSQL($date_add_min . ' 00:00:00');
                $show_reset = true;
            }

            $date_add_max = Tools::getValue('date_add_max');
            if ($date_add_max && Validate::isDate($date_add_max)) {
                $filter[] = 'date_add <= ' . pSQL($date_add_max . ' 23:59:59');
                $show_reset = true;
            }
        }

        $filter = implode(' AND ', $filter);

        $totalRecords = (int)Ets_superspeed_cache_page_log::getTotalLogs($filter);

        $pagination = new Ets_superspeed_pagination_class();
        $pagination->total = $totalRecords;

        $sort = Tools::getValue('sort', 'date_add');
        $sort = $sort === 'page_name' ? 'page' : $sort;
        $sort = in_array($sort, ['page', 'reason', 'date_add']) ? $sort : 'date_add';

        $sort_type = Tools::strtolower(Tools::getValue('sort_type', 'desc'));
        $sort_type = $sort_type === 'desc' || $sort_type === 'asc' ? $sort_type : 'desc';

        $sql_sort = $sort . ' ' . $sort_type;

        $pagination->url = $this->context->link->getAdminLink('AdminSuperSpeedPageCaches', true) .
            '&current_tab=page-list-log-clear-history&page=_page_' .
            ($sort ? '&sort=' . $sort : '') .
            ($sort_type ? '&sort_type=' . $sort_type : '') .
            $this->module->getFilterParams($fields_list, 'clear_history');

        $pagination->limit = 20;
        $totalPages = ceil($totalRecords / $pagination->limit);
        $page = min($page, $totalPages);
        $pagination->page = $page;

        $start = max(0, (int)$pagination->limit * ((int)$page - 1));
        $pagination->text = $this->l('Showing {start} to {end} of {total} ({pages} Pages)');
        $file_caches = Ets_superspeed_cache_page_log::getListLogs($start, $pagination->limit, $sql_sort, $filter);
        $listData = array(
            'name' => 'clear_history',
            'icon' => 'fa fa-product',
            'actions' => array(),
            'currentIndex' => Context::getContext()->link->getAdminLink('AdminSuperSpeedPageCaches') . '&current_tab=page-list-log-clear-history',
            'identifier' => 'id_ets_superspeed_cache_page_log',
            'show_toolbar' => true,
            'show_action' => true,
            'title' => $this->l('Clearing cache history'),
            'fields_list' => $fields_list,
            'field_values' => $file_caches,
            'pagination' => $pagination->render(),
            'filter_params' => $this->module->getFilterParams($fields_list, 'clear_history'),
            'show_reset' => $show_reset,
            'totalRecords' => $totalRecords,
            'sort' => $sort,
            'show_add_new' => false,
            'link_new' => '',
            'sort_type' => $sort_type,
        );

        return $this->module->renderList($listData);
    }

    public function displayPageNoCaches()
    {
        $fields_list = array(
            'request_uri' => array(
                'title' => $this->l('URL'),
                'type' => 'text',
                'sort' => true,
                'filter' => true,
                'strip_tag' => false,
            ),
            'lang_name' => array(
                'title' => $this->l('Language'),
                'type' => 'text',
                'sort' => true,
                'filter' => true,
            ),
            'iso_code' => array(
                'title' => $this->l('Currency'),
                'type' => 'text',
                'sort' => false,
                'filter' => true,
            ),
            'country_name' => array(
                'title' => $this->l('Country'),
                'type' => 'text',
                'sort' => true,
                'filter' => true,
            ),
            'ip' => array(
                'title' => $this->l('IP'),
                'type' => 'text',
                'sort' => true,
                'filter' => true,
                'strip_tag' => false,
            ),
            'user_agent' => array(
                'title' => $this->l('Agent'),
                'type' => 'text',
                'sort' => false,
                'filter' => false,
            ),
            'has_customer' => array(
                'title' => $this->l('Sign in'),
                'type' => 'select',
                'sort' => true,
                'filter' => true,
                'filter_list' => array(
                    'id_option' => 'active',
                    'value' => 'title',
                    'list' => array(
                        array('active' => 0, 'title' => $this->l('No')),
                        array('active' => 1, 'title' => $this->l('Yes'))
                    )
                )
            ),
            'has_cart' => array(
                'title' => $this->l('Has cart'),
                'type' => 'select',
                'sort' => true,
                'filter' => true,
                'filter_list' => array(
                    'id_option' => 'active',
                    'value' => 'title',
                    'list' => array(
                        array('active' => 0, 'title' => $this->l('No')),
                        array('active' => 1, 'title' => $this->l('Yes'))
                    )
                )
            ),
            'error' => array(
                'title' => $this->l('Error'),
                'type' => 'text',
                'sort' => true,
                'filter' => false,
            ),
            'date_add' => array(
                'title' => $this->l('Date created'),
                'type' => 'date',
                'sort' => true,
                'filter' => false,
            ),
        );

        $page = max(1, (int)Tools::getValue('page'));
        $show_reset = false;
        $filter = "";

        if (Tools::isSubmit('ets_sp_submit_file_no_caches')) {
            $filter = $this->buildFilterQuery();
            $show_reset = true;
        }

        $totalRecords = (int)Ets_superspeed_cache_page_error::getTotalPageNoCaches($filter);
        $pagination = new Ets_superspeed_pagination_class();
        $pagination->total = $totalRecords;

        $sort = $this->getSortColumn();
        $sort_type = $this->getSortType();

        $sql_sort = $this->getSortSql($sort) . ' ' . $sort_type;
        $pagination->url = $this->buildPaginationUrl($sort, $sort_type,$fields_list);
        $pagination->limit = 20;

        $totalPages = ceil($totalRecords / $pagination->limit);
        $page = min($page, $totalPages);
        $pagination->page = $page;
        $start = max(0, (int)$pagination->limit * ((int)$page - 1));
        $pagination->text = $this->l('Showing {start} to {end} of {total} ({pages} Pages)');

        $file_caches = Ets_superspeed_cache_page_error::getListPageNoCaches($start, $pagination->limit, $sql_sort, $filter);
        $file_caches = $this->processFileCaches($file_caches);

        $listData = array(
            'name' => 'file_no_caches',
            'icon' => 'fa fa-product',
            'actions' => array(),
            'currentIndex' => Context::getContext()->link->getAdminLink('AdminSuperSpeedPageCaches') . '&current_tab=page-list-no-caches',
            'identifier' => 'id_cache_page',
            'show_toolbar' => true,
            'show_action' => true,
            'title' => $this->l('List of cache errors'),
            'fields_list' => $fields_list,
            'field_values' => $file_caches,
            'pagination' => $pagination->render(),
            'filter_params' => $this->module->getFilterParams($fields_list, 'file_no_caches'),
            'show_reset' => $show_reset,
            'totalRecords' => $totalRecords,
            'sort' => $sort,
            'show_add_new' => false,
            'link_new' => '',
            'sort_type' => $sort_type,
        );

        return $this->module->renderList($listData);
    }

    private function buildFilterQuery()
    {
        $filter = "";
        $filterParams = array(
            'request_uri' => 'cache.request_uri LIKE "%' . pSQL(Tools::getValue('request_uri')) . '%"',
            'lang_name' => 'lang.name LIKE "%' . pSQL(Tools::getValue('lang_name')) . '%"',
            'iso_code' => 'currency.iso_code LIKE "%' . pSQL(Tools::getValue('iso_code')) . '%"',
            'country_name' => 'country_lang.name LIKE "%' . pSQL(Tools::getValue('country_name')) . '%"',
            'ip' => 'cache.ip LIKE "%' . pSQL(Tools::getValue('ip')) . '%"',
            'has_customer' => 'cache.has_customer = "' . (int)Tools::getValue('has_customer') . '"',
            'has_cart' => 'cache.has_cart = "' . (int)Tools::getValue('has_cart') . '"'
        );

        foreach ($filterParams as $param => $query) {
            $value = Tools::getValue($param);
            if (!empty($value)) {
                $filter .= ' AND ' . $query;
            }
        }

        return $filter;
    }

    private function getSortColumn()
    {
        $sort = Tools::getValue('sort', 'date_add');
        $validSortColumns = array('request_uri', 'ip', 'has_cart', 'has_customer', 'date_add', 'lang_name', 'iso_code', 'country_name');
        if (!in_array($sort, $validSortColumns)) {
            $sort = 'date_add';
        }
        return $sort;
    }

    private function getSortType()
    {
        $sort_type = Tools::strtolower(Tools::getValue('sort_type', 'desc'));
        return ($sort_type == 'desc' || $sort_type == 'asc') ? $sort_type : 'desc';
    }

    private function getSortSql($sort)
    {
        $sortMapping = array(
            'request_uri' => 'request_uri',
            'ip' => 'ip',
            'has_customer' => 'has_customer',
            'has_cart' => 'has_cart',
            'date_add' => 'date_add',
            'lang_name' => 'lang.name',
            'iso_code' => 'currency.iso_code',
            'country_name' => 'country_lang.name'
        );

        return isset($sortMapping[$sort]) ? $sortMapping[$sort] : 'date_add';
    }

    private function buildPaginationUrl($sort, $sort_type,$fields_list)
    {
        return $this->context->link->getAdminLink('AdminSuperSpeedPageCaches', true) .
            '&current_tab=page-list-no-caches&page=_page_' .
            ($sort ? '&sort=' . $sort : '') .
            ($sort_type ? '&sort_type=' . $sort_type : '') .
            $this->module->getFilterParams($fields_list, 'file_no_caches');
    }

    private function processFileCaches($file_caches)
    {
        if ($file_caches) {
            foreach ($file_caches as &$file_cache) {
                $file_cache['date_add'] = Ets_superspeed_defines::displayText(
                    $this->module->convertTime(strtotime(date('Y-m-d H:i:s')) - strtotime($file_cache['date_add'])) . ' ' . $this->l('ago'),
                    'span',
                    array('title' => Ets_ss_class_cache::displayDate($file_cache['date_add'], true))
                );
                $file_cache['request_uri'] = Ets_superspeed_defines::displayText(
                    $file_cache['request_uri'],
                    'a',
                    array('href' => '/..' . $file_cache['request_uri'], 'target' => '_blank')
                );
                $file_cache['has_cart'] = $file_cache['has_cart'] ? $this->l('Yes') : $this->l('No');
                $file_cache['has_customer'] = $file_cache['has_customer'] ? $this->l('Yes') : $this->l('No');
                $file_cache['ip'] = Ets_superspeed_defines::displayText(
                    $file_cache['ip'],
                    'a',
                    array('href' => 'https://www.infobyip.com/ip-' . $file_cache['ip'] . '.html', 'target' => '_blank')
                );
            }
        }
        return $file_caches;
    }
    public function displayPageCaches()
    {
        $fields_list = array(
            'id_cache_page' => array(
                'title' => $this->l('ID'),
                'type' => 'text',
                'sort' => true,
                'filter' => true,
            ),
            'request_uri' => array(
                'title' => $this->l('URL'),
                'type' => 'text',
                'sort' => true,
                'filter' => true,
                'strip_tag' => false,
            ),
            'lang_name'=>array(
                'title' => $this->l('Language'),
                'type' => 'text',
                'sort' => true,
                'filter' => true,
            ),
            'iso_code'=>array(
                'title' => $this->l('Currency'),
                'type' => 'text',
                'sort' => false,
                'filter' => true,
            ),
            'country_name' => array(
                'title' => $this->l('Country'),
                'type' => 'text',
                'sort' => true,
                'filter' => true,
            ),
            'file_size' => array(
                'title' => $this->l('Size'),
                'type' => 'text',
                'sort' => true,
                'filter' => false,
            ),
            'click' => array(
                'title' => $this->l('Cache hit'),
                'type' => 'text',
                'sort' => true,
                'filter' => false,
            ),
            'ip' => array(
                'title' => $this->l('IP'),
                'type' => 'text',
                'sort' => true,
                'filter' => true,
                'strip_tag' => false,
            ),
            'user_agent' => array(
                'title' => $this->l('Agent'),
                'type' => 'text',
                'sort' => false,
                'filter' => false,
            ),
            'has_customer' => array(
                'title' => $this->l('Sign in'),
                'type' => 'select',
                'sort' => true,
                'filter' => true,
                'filter_list' => array(
                    'id_option' => 'active',
                    'value' => 'title',
                    'list' => array(
                        array(
                            'active' => 0,
                            'title' => $this->l('No')
                        ),
                        array(
                            'active' => 1,
                            'title' => $this->l('Yes')
                        )
                    )
                )
            ),
            'has_cart' => array(
                'title' => $this->l('Has cart'),
                'type' => 'select',
                'sort' => true,
                'filter' => true,
                'filter_list' => array(
                    'id_option' => 'active',
                    'value' => 'title',
                    'list' => array(
                        array(
                            'active' => 0,
                            'title' => $this->l('No')
                        ),
                        array(
                            'active' => 1,
                            'title' => $this->l('Yes')
                        )
                    )
                )
            ),
            'date_expired' => array(
                'title' => $this->l('Date expired'),
                'type' => 'date',
                'sort' => true,
                'filter' => false,
            ),
            'date_add' => array(
                'title' => $this->l('Date created'),
                'type' => 'date',
                'sort' => true,
                'filter' => false,
            ),
        );

        $page = max(1, (int)Tools::getValue('page'));
        $show_reset = false;
        $filter = "";

        if (Tools::isSubmit('ets_sp_submit_file_caches')) {
            if (($id_cache_page = Tools::getValue('id_cache_page')) && Validate::isUnsignedId($id_cache_page)) {
                $filter .= ' AND cache.id_cache_page=' . (int)$id_cache_page;
                $show_reset = true;
            }
            if (($request_uri = Tools::getValue('request_uri')) && Validate::isCleanHtml($request_uri)) {
                $filter .= ' AND cache.request_uri LIKE "%' . pSQL($request_uri) . '%"';
                $show_reset = true;
            }
            if (($lang_name = trim(Tools::getValue('lang_name'))) && Validate::isCleanHtml($lang_name)) {
                $filter .= ' AND lang.name LIKE "%' . pSQL($lang_name) . '%"';
                $show_reset = true;
            }
            if (($iso_code = trim(Tools::getValue('iso_code'))) && Validate::isCleanHtml($iso_code)) {
                $filter .= ' AND currency.iso_code LIKE "%' . pSQL($iso_code) . '%"';
                $show_reset = true;
            }
            if (($country_name = trim(Tools::getValue('country_name'))) && Validate::isCleanHtml($country_name)) {
                $filter .= ' AND country_lang.name LIKE "%' . pSQL($country_name) . '%"';
                $show_reset = true;
            }
            if (($ip = trim(Tools::getValue('ip'))) != '' && Validate::isCleanHtml($ip)) {
                $filter .= ' AND cache.ip LIKE "%' . pSQL($ip) . '%"';
                $show_reset = true;
            }
            if (($has_customer = Tools::getValue('has_customer')) !== '' && Validate::isInt($has_customer)) {
                $filter .= ' AND cache.has_customer = ' . (int)$has_customer;
                $show_reset = true;
            }
            if (($has_cart = Tools::getValue('has_cart')) !== '' && Validate::isInt($has_cart)) {
                $filter .= ' AND cache.has_cart = ' . (int)$has_cart;
                $show_reset = true;
            }
        }

        $totalRecords = (int)Ets_superspeed_cache_page::getTotalPageCaches($filter);
        $pagination = new Ets_superspeed_pagination_class();
        $pagination->total = $totalRecords;

        $sort = Tools::getValue('sort', 'date_add');
        $validSortFields = array('request_uri', 'ip', 'has_cart', 'has_customer', 'file_size', 'click', 'date_add', 'date_expired', 'lang_name', 'iso_code', 'country_name');
        if (!in_array($sort, $validSortFields)) {
            $sort = 'date_add';
        }
        $sort_type = Tools::strtolower(Tools::getValue('sort_type', 'desc'));
        if ($sort_type !== 'desc' && $sort_type !== 'asc') {
            $sort_type = 'desc';
        }

        $sql_sort = '';
        switch ($sort) {
            case 'request_uri':
            case 'file_size':
            case 'click':
            case 'date_expired':
            case 'ip':
            case 'has_customer':
            case 'has_cart':
            case 'date_add':
                $sql_sort = $sort;
                break;
            case 'lang_name':
                $sql_sort = 'lang.name';
                break;
            case 'iso_code':
                $sql_sort = 'currency.iso_code';
                break;
            case 'country_name':
                $sql_sort = 'country_lang.name';
                break;
        }
        if ($sql_sort) {
            $sql_sort .= ' ' . $sort_type;
        }

        $pagination->url = $this->context->link->getAdminLink('AdminSuperSpeedPageCaches', true) . '&current_tab=page-list-caches&page=_page_'.($sort ? '&sort='.$sort: '').($sort_type ? '&sort_type='.$sort_type:'').$this->module->getFilterParams($fields_list, 'file_caches');
        $pagination->limit = 20;
        $totalPages = ceil($totalRecords / $pagination->limit);
        if ($page > $totalPages) {
            $page = $totalPages;
        }
        $pagination->page = $page;
        $start = (int)$pagination->limit * ((int)$page - 1);
        $start = max(0, $start);
        $pagination->text = $this->l('Showing {start} to {end} of {total} ({pages} Pages)');

        $file_caches = Ets_superspeed_cache_page::getListPageCaches($start, $pagination->limit, $sql_sort, $filter);

        if ($file_caches) {
            foreach ($file_caches as &$file_cache) {
                $file_cache['basename'] = basename($file_cache['file_cache']);
                if ($file_cache['file_size'] == 0) {
                    $file_cache['file_size'] = Tools::ps_round(@filesize($file_cache['file_cache']) / 1024, 2);
                }
                if ($file_cache['date_expired'] && $file_cache['date_expired'] != '0000-00-00 00:00:00') {
                    $time = strtotime($file_cache['date_expired']) - strtotime(date('Y-m-d H:i:s'));
                    $file_cache['date_expired'] = Ets_superspeed_defines::displayText(($time > 0 ? $this->module->convertTime($time) : $this->l('Expired')), 'span', array('title' => Ets_ss_class_cache::displayDate($file_cache['date_expired'], true)));
                    if ($time <= 0) {
                        $file_cache['expired'] = true;
                    }
                } else {
                    $file_cache['date_expired'] = $this->l('Forever');
                }
                $file_cache['date_add'] = Ets_superspeed_defines::displayText($this->module->convertTime(strtotime(date('Y-m-d H:i:s')) - strtotime($file_cache['date_add'])) . ' ' . $this->l('ago'), 'span', array('title' => Ets_ss_class_cache::displayDate($file_cache['date_add'], true)));
                $file_cache['request_uri'] = Ets_superspeed_defines::displayText($file_cache['request_uri'], 'a', array('href' => '/..' . htmlspecialchars($file_cache['request_uri'], ENT_QUOTES, 'UTF-8'), 'target' => '_blank'));
                $file_cache['has_cart'] = $file_cache['has_cart'] ? $this->l('Yes') : $this->l('No');
                $file_cache['has_customer'] = $file_cache['has_customer'] ? $this->l('Yes') : $this->l('No');
                $file_cache['ip'] = Ets_superspeed_defines::displayText($file_cache['ip'], 'a', array('href' => 'https://www.infobyip.com/ip-' . htmlspecialchars($file_cache['ip'], ENT_QUOTES, 'UTF-8') . '.html', 'target' => '_blank'));
            }
        }

        $listData = array(
            'name' => 'file_caches',
            'icon' => 'fa fa-product',
            'actions' => array(),
            'currentIndex' => Context::getContext()->link->getAdminLink('AdminSuperSpeedPageCaches') . '&current_tab=page-list-caches',
            'identifier' => 'id_cache_page',
            'show_toolbar' => true,
            'show_action' => true,
            'title' => $this->l('Cached urls'),
            'fields_list' => $fields_list,
            'field_values' => $file_caches,
            'pagination' => $pagination->render(),
            'filter_params' => $this->module->getFilterParams($fields_list, 'file_no_caches'),
            'show_reset' => $show_reset,
            'totalRecords' => $totalRecords,
            'sort' => $sort,
            'show_add_new' => false,
            'link_new' => '',
            'sort_type' => $sort_type,
        );

        return $this->module->renderList($listData);
    }

    public function getCachePageFieldsValues($inputs)
    {
        $fields = array(
            'ETS_SPEED_TIME_CACHE_INDEX' => Configuration::get('ETS_SPEED_TIME_CACHE_INDEX'),
            'ETS_SPEED_TIME_CACHE_CATEGORY' => Configuration::get('ETS_SPEED_TIME_CACHE_CATEGORY'),
            'ETS_SPEED_TIME_CACHE_PRODUCT' => Configuration::get('ETS_SPEED_TIME_CACHE_PRODUCT'),
            'ETS_SPEED_TIME_CACHE_CMS' => Configuration::get('ETS_SPEED_TIME_CACHE_CMS'),
            'ETS_SPEED_TIME_CACHE_NEWPRODUCTS' => Configuration::get('ETS_SPEED_TIME_CACHE_NEWPRODUCTS'),
            'ETS_SPEED_TIME_CACHE_BESTSALES' => Configuration::get('ETS_SPEED_TIME_CACHE_BESTSALES'),
            'ETS_SPEED_TIME_CACHE_SUPPLIER' => Configuration::get('ETS_SPEED_TIME_CACHE_SUPPLIER'),
            'ETS_SPEED_TIME_CACHE_MANUFACTURER' => Configuration::get('ETS_SPEED_TIME_CACHE_MANUFACTURER'),
            'ETS_SPEED_TIME_CACHE_CONTACT' => Configuration::get('ETS_SPEED_TIME_CACHE_CONTACT'),
            'ETS_SPEED_TIME_CACHE_PRICESDROP' => Configuration::get('ETS_SPEED_TIME_CACHE_PRICESDROP'),
            'ETS_SPEED_TIME_CACHE_SITEMAP' => Configuration::get('ETS_SPEED_TIME_CACHE_SITEMAP'),
            'ETS_SPEED_TIME_CACHE_BLOG' => Configuration::get('ETS_SPEED_TIME_CACHE_BLOG'),
            'ETS_SPEED_TIME_CACHE_PRODUCTFEED' => Configuration::get('ETS_SPEED_TIME_CACHE_PRODUCTFEED'),
            'ETS_SPEED_TIME_CACHE_COLLECTION' =>Configuration::get('ETS_SPEED_TIME_CACHE_COLLECTION'),
            'live_script' => file_exists(dirname(__FILE__) . '/../../views/js/script_custom.js') ? Tools::file_get_contents(dirname(__FILE__) . '/../../views/js/script_custom.js') : '',
        );
        return array_merge($this->module->getFieldsValues($inputs),$fields);
    }
}
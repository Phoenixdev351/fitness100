<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class AmazzingFilterAjaxModuleFrontController extends ModuleFrontControllerCore
{
    public function initContent()
    {
        if (Tools::getValue('token') != $this->module->ajaxToken()) {
            exit('403 Forbidden');
        }
        $this->module->defineSettings();
        switch (Tools::getValue('action')) {
            case 'getFilteredProducts':
                if ($params = $this->module->parseStr(Tools::getValue('params'), true)) {
                    $params['ajax'] = 1;
                    exit(json_encode($this->module->prepareAjaxResponse($params)));
                }
                break;
            case 'customerFilterAction':
                $this->module->customerFilters()->ajaxAction();
                break;
        }
    }
}

<?php

class SequrapaymentCancelModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function initContent()
    {
        $this->module = Module::getInstanceByName('sequrainvoice');
        if ($method = Tools::getValue('m_method', Tools::getValue('method', false))) {
            $this->module = Module::getInstanceByName($method);
        }
        $res = $this->module->cancelOrderFromWebhook();
    }
}

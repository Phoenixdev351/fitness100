<?php

class SequrapaymentIpnModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function initContent()
    {
        if ($method = Tools::getValue('method', false)) {
            $this->module = Module::getInstanceByName($method);
        }
        $this->module->confirmOrderFromIpn();
        die('Done!');
    }
}

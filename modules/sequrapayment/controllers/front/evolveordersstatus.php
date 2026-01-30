<?php

class SequrapaymentEvolveorderstatusModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function initContent()
    {
        $this->module = Module::getInstanceByName('sequra');
        $res = $this->module->evolveOrderStatus();
    }
}

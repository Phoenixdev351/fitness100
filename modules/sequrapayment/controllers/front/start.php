<?php

class SequrapaymentStartModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function initContent()
    {
        $this->module->startSolicitation();
    }

}

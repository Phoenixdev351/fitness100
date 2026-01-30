<?php

class SequrapaymentCustomModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function initContent()
    {
        var_dump(Configuration::get('SEQURA_REPORT_ERROR'));
    }

}

<?php

class SequrapaymentTriggerreportModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function initContent()
    {
        $this->module->submitDailyReport();
        if ('' == Configuration::get('SEQURA_REPORT_ERROR')) {
            die('ok');
        }
        http_response_code(599);
        die('ko');
    }

}

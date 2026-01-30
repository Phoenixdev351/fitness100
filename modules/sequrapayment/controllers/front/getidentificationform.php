<?php

class SequrapaymentGetIdentificationFormModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function displayAjax()
    {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        $params = array(
            'product' => $this->module->getProduct(),
            'campaign' => $this->module->getCampaign(),
            'ajax' => true
        );
        echo $this->module->getIdentificationForm($params);
    }
}

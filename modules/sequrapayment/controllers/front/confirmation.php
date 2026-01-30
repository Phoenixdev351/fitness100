<?php

class SequrapaymentConfirmationModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function initContent()
    {
        $this->display_column_left = false;
        $this->display_column_right = false;
        parent::initContent();
        $this->module->confirmOrder();
    }
}

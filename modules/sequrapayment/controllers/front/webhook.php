<?php

class SequrapaymentWebhookModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function initContent()
    {
        $event = Tools::getValue('event','cancel');
        switch ($event) {
            case 'cancelled':
                $this->module->cancelledOrderFromWebhook();
                break;
            case 'risk_assessment':
                $this->module->setRiskLevelToOrder();
                break;
            default:
                $this->module->cancelOrderFromWebhook();
        }
        die('Message received!');
    }
}

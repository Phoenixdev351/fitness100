<?php

class AdminAjaxSequrapaymentController extends ModuleAdminController
{
    /**
     * AJAX: Change prestashop rounding settings
     *
     * PS_ROUND_TYPE need to be set to 1 (Round on each item)
     * PS_PRICE_ROUND_MODE need to be set to 2 (Round up away from zero, wh
     */
    public function ajaxSendPaymentEmail()
    {
        Configuration::updateValue('PS_ROUND_TYPE', '1');
        Configuration::updateValue('PS_PRICE_ROUND_MODE', '2');

        $this->ajaxDie(json_encode(true));
    }

    /**
     * AJAX: Change prestashop rounding settings
     *
     * PS_ROUND_TYPE need to be set to 1 (Round on each item)
     * PS_PRICE_ROUND_MODE need to be set to 2 (Round up away from zero, wh
     */
    public function ajaxProcessEditRoundingSettings()
    {
        Configuration::updateValue('PS_ROUND_TYPE', '1');
        Configuration::updateValue('PS_PRICE_ROUND_MODE', '2');

        $this->ajaxDie(json_encode(true));
    }
}

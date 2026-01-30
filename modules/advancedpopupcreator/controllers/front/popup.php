<?php
/**
*
* NOTICE OF LICENSE
*
* This product is licensed for one customer to use on one installation (test stores and multishop included).
* Site developer has the right to modify this module to suit their needs, but can not redistribute the module in
* whole or in part. Any other use of this module constitutes a violation of the user agreement.
*
* DISCLAIMER
*
* NO WARRANTIES OF DATA SAFETY OR MODULE SECURITY
* ARE EXPRESSED OR IMPLIED. USE THIS MODULE IN ACCORDANCE
* WITH YOUR MERCHANT AGREEMENT, KNOWING THAT VIOLATIONS OF
* PCI COMPLIANCY OR A DATA BREACH CAN COST THOUSANDS OF DOLLARS
* IN FINES AND DAMAGE A STORES REPUTATION. USE AT YOUR OWN RISK.
*
*  @author    idnovate.com <info@idnovate.com>
*  @copyright 2020 idnovate.com
*  @license   See above
*/

class AdvancedPopupCreatorPopupModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        parent::init();

        header('X-Robots-Tag: noindex, nofollow', true);
    }

    public function postProcess()
    {
        // For powerfulformgenerator module
        if (Tools::getValue('pfg_form_id')) {
            require_once(_PS_MODULE_DIR_.'powerfulformgenerator/classes/PFGRenderer.php');
            $renderer = new PFGRenderer(1);
            Tools::redirect('index');
        }

        if ($this->isTokenValid()) {
            if (Tools::getValue('getPopup')) {
                $loAPC = new AdvancedPopup();
                $laPopups = $loAPC->getPopupToDisplay(Tools::getValue('availablePopups'));

                $popups = array();

                if ($laPopups) {
                    foreach ($laPopups as $laPopup) {
                        // Render and return data
                        $popups[] = json_encode(array(
                            'selector'  => (Tools::getValue('event') == 4 && $laPopup['display_on_click'] == 1) ? $laPopup['display_on_click_selector'] : '',
                            'id'        => $laPopup['id_advancedpopup']
                        ));
                    }
                }

                die(json_encode(array(
                    'hasError' => false,
                    'popups' => $popups,
                )));
            } elseif (Tools::getValue('updateVisits')) {
                $loAPC = new AdvancedPopup();
                $laPopups = $loAPC->updateVisits();
            } elseif (Tools::getValue('markAsSeen')) {
                $loAPC = new AdvancedPopup();
                $popupId = (int)Tools::getValue('popupId');
                if (!$popupId
                    || $popupId <= 0) {
                    return false;
                }

                $popups = $loAPC->getApcCookiePopups();

                $liNow = time();

                $popups[$popupId]['last_displayed'] = $liNow;
                $popups[$popupId]['visits'] = 0;

                $loAPC->setApcCookiePopups($popups);

                die(json_encode(array('hasError' => false, 'errors' => '')));
            } elseif (Tools::getValue('dontDisplayAgain')) {
                $loAPC = new AdvancedPopup();
                $popups = $loAPC->getApcCookiePopups();

                $lbFound = false;
                if (!empty($popups)) {
                    foreach ($popups as $laPopupId => &$popup) {
                        if ((int)$laPopupId === (int)Tools::getValue('popupId')) {
                            $lbFound = true;
                            $popup['last_displayed'] = PHP_INT_MAX-1;
                        }
                    }

                    // Save if found id
                    if ($lbFound) {
                        $loAPC->setApcCookiePopups($popups);
                    }
                }
            }

            die(json_encode(array('hasError' => false, 'errors' => '')));
        } else {
            die(json_encode(array('hasError' => true, 'errors' => 'Token not valid')));
        }
    }

    private function generatePFG($id_pfg)
    {
        $renderer = new PFGRenderer($id_pfg);
        if ($renderer->isAllowed(true)) {
            return $renderer->displayForm();
        }
    }
}

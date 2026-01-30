<?php
/** * Facebook Conversion Pixel Tracking Plus
*
* NOTICE OF LICENSE
*
* @author    Pol Rué
* @copyright Smart Modules 2014
* @license   One time purchase Licence (You can modify or resell the product but just one time per licence)
* @version 2.3.3
* @category Marketing & Advertising
* Registered Trademark & Property of smart-modules.com
*
* ****************************************************
* *                    Pixel Plus                    *
* *          http://www.smart-modules.com            *
* *                     V 2.3.3                      *
* ****************************************************
*
* Versions:
* To check the complete changelog. open versions.txt file
*/

class FacebookConversionTrackingPlusAjaxConversionModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        // Prevent indexing
        header('Content-Type: application/json');
        header('X-Robots-Tag: noindex, nofollow', true);
        $tmp = $this->module;
        $context = $this->context;
        $return = '';
        if (Tools::getValue('trackRegister')) {
            $return =  $tmp->trackAjaxRegistration();
        } elseif (Tools::getIsset('cookieConsent') && Tools::getValue('token') == Tools::encrypt('CookieValidate'.($context->cookie->id_customer > 0 ? $context->cookie->id_customer : $context->cookie->id_guest))) {
            $cookie = '';
            $value = '';
            if (Configuration::get('FCTP_BLOCK_SCRIPT')) {
                $cookie = Configuration::get('FCTP_COOKIE_NAME');
                if ($cookie != '') {
                    $value = Configuration::get('FCTP_COOKIE_VALUE');
                }
            }
            $return =  $tmp->checkCookies($cookie, $value);
        } elseif (Tools::getIsset('event')) {
            $e = Tools::getValue('event');
            $event_id = Tools::passwdGen(12);
            $this->api = new ConversionApi();
            if ($this->api != false) {
                switch ($e) {
                    case 'Purchase':
                        if (Tools::getIsset('id_customer')) {
                            $id_customer = (int) Tools::getValue('id_customer');
                            $return = $tmp->trackAjaxConversion($id_customer);
                        }
                        break;
                    case 'InitiateCheckout':
                        if (Tools::getIsset('id_cart')) {
                            $id_cart = (int)Tools::getValue('id_cart');
                            $return = $this->api->initiateCheckoutTrigger($event_id, $id_cart, true);
                        }
                }
            }
        }
        echo $return ? '{"return":"ok"}' : '{"return":"error"}';
        exit;
    }
}

<?php
/**
 * Smartsupp Live Chat integration module.
 *
 * @package   Smartsupp
 * @author    Smartsupp <vladimir@smartsupp.com>
 * @link      http://www.smartsupp.com
 * @copyright 2016 Smartsupp.com
 * @license   GPL-2.0+
 *
 * Plugin Name:       Smartsupp Live Chat
 * Plugin URI:        http://www.smartsupp.com
 * Description:       Adds Smartsupp Live Chat code to PrestaShop.
 * Version:           2.2.0
 * Author:            Smartsupp
 * Author URI:        http://www.smartsupp.com
 * Text Domain:       smartsupp
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

use Smartsupp\Auth\Api;
use Smartsupp\LiveChat\Validator\UserCredentialsValidator;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminSmartsuppAjaxController extends ModuleAdminController
{
    const FILE_NAME = 'AdminSmartsuppAjaxController';

    private $partnerKey = 'h4w6t8hln9';
    private $api;

    const LOGIN_ACTION = 'login';
    const CREATE_ACTION = 'create';
    const DEACTIVATE_ACTION = 'deactivate';

    public function init()
    {
        $validator = new UserCredentialsValidator($this->module);

        $validator->validate(Tools::getAllValues());
        $action = Tools::getValue('action');

        if ($validator->getError() && $action !== self::DEACTIVATE_ACTION) {
            $this->handleError($validator->getMessage(), $validator->getError());
        }

        try {
            $this->api = new Api();

            switch ($action) {
                case self::LOGIN_ACTION:
                    $this->handleLoginAction();
                    break;
                case self::CREATE_ACTION:
                    $this->handleCreateAction();
                    break;
                case self::DEACTIVATE_ACTION:
                    $this->handleDeactivateAction();
                    break;
                default:
                    throw new Exception('Invalid action');
            }
        } catch (Exception $e) {
            $this->handleError($e->getMessage());
        }

        $this->sendResponse();
    }

    private function handleLoginAction()
    {
        $this->response = $this->api->login([
            'email' => Tools::getValue('email'),
            'password' => Tools::getValue('password'),
            'platform' => 'Prestashop ' . _PS_VERSION_,
        ]);

        $this->updateCredentials();
    }

    private function handleCreateAction()
    {
        $this->response = $this->api->create([
            'email' => Tools::getValue('email'),
            'password' => Tools::getValue('password'),
            'partnerKey' => $this->partnerKey,
            'consentTerms' => 1,
            'platform' => 'Prestashop ' . _PS_VERSION_,
        ]);

        $this->updateCredentials();
    }

    private function handleDeactivateAction()
    {
        Configuration::updateValue('SMARTSUPP_KEY', '');
        Configuration::updateValue('SMARTSUPP_EMAIL', '');

        $this->sendResponse();
    }

    private function updateCredentials()
    {
        if (isset($this->response['account']['key'])) {
            Configuration::updateValue('SMARTSUPP_KEY', $this->response['account']['key']);
            Configuration::updateValue('SMARTSUPP_EMAIL', Tools::getValue('email'));
            return;
        }

        if (isset($this->response['error'])) {
            $this->sendResponse();
        }

        $this->handleError($this->module->l('Unknown error occurred while processing your request.', self::FILE_NAME));
    }

    private function handleError($message, $error = 'error')
    {
        $this->response['key'] = Configuration::get('SMARTSUPP_KEY');
        $this->response['email'] = Configuration::get('SMARTSUPP_EMAIL');

        $this->response['error'] = $error;
        $this->response['message'] = $message;

        die(json_encode($this->response));
    }

    private function sendResponse()
    {
        header('Content-Type: application/json');

        $responseData = [
            'key' => Configuration::get('SMARTSUPP_KEY'),
            'email' => Configuration::get('SMARTSUPP_EMAIL'),
            'error' => isset($this->response['error']) ? $this->response['error'] : null,
            'message' => isset($this->response['message']) ? $this->response['message'] : null,
        ];

        $responseData = array_filter($responseData, function ($val) {
            return $val !== null;
        });

        die(json_encode($responseData));
    }
}
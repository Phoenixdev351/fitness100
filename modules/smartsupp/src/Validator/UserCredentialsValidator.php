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

namespace Smartsupp\LiveChat\Validator;

if (!defined('_PS_VERSION_')) {
    exit;
}

class UserCredentialsValidator
{
    const FILE_NAME = 'UserCredentialsValidator';

    /**
     * @var \Smartsupp $module
     */
    private $module;

    /**
     * @var string
     */
    private $error = '';

    /**
     * @var string
     */
    private $message = '';

    public function __construct($module)
    {
        $this->module = $module;
    }

    /**
     * @param array $data
     *
     * @return void
     */
    public function validate($data)
    {
        $email = isset($data['email']) ? $data['email'] : '';
        $password = isset($data['password']) ? $data['password'] : '';

        if (empty($email) || empty($password)) {
                $this->error = $this->module->l('Empty values provided', self::FILE_NAME);
                $this->message = $this->module->l('Email and password fields can not be empty', self::FILE_NAME);

                return;
        }

        if (!$this->validateEmail($email)) {
            $this->error = $this->module->l('Invalid email format', self::FILE_NAME);
            $this->message =  $this->module->l('Invalid email address', self::FILE_NAME);

            return;
        }

        // Validate password
        if (!$this->validatePassword($password)) {
            $this->error = $this->module->l('Password length is invalid', self::FILE_NAME);
            $this->message = $this->module->l('Password must be between 6-255 characters long', self::FILE_NAME);
        }
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $email
     * @return array|false
     */
    private function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * @param string $password
     * @return bool
     */
    private function validatePassword($password)
    {
        return strlen($password) >= 6 && strlen($password) <= 255;
    }
}

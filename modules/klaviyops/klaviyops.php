<?php

/**
 * Klaviyo
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Commercial License
 * you can't distribute, modify or sell this code
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file
 * If you need help please contact extensions@klaviyo.com
 *
 * @author    Klaviyo
 * @copyright Klaviyo
 * @license   commercial
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Necessary to access namespaced module classes in the main module file.
 */
require_once(__DIR__ . '/vendor/autoload.php');

class KlaviyoPs extends KlaviyoPsModule
{
    /**
     * Klaviyo constructor.
     */
    public function __construct()
    {
        $this->module_key = '8cbae1889fefef3589d3dcb95c0818aa';
        $this->name = 'klaviyops';
        $this->author = 'Klaviyo';
        $this->version = '1.12.5';
        $this->ps_versions_compliancy = [
            'min' => '1.7',
            'max' => _PS_VERSION_
        ];

        parent::__construct();

        $this->displayName = 'Klaviyo';
        $this->description = $this->l('Klaviyo module to integrate PrestaShop with Klaviyo.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        if (!Configuration::get('KLAVIYO')) {
            $this->warning = $this->l('No name provided');
        }
    }
}

<?php
/**
 * Smartsupp Live Chat integration module.
 *
 * @author    Smartsupp <vladimir@smartsupp.com>
 * @copyright 2016 Smartsupp.com
 * @license   GPL-2.0+
 * @package   Smartsupp
 * @link      http://www.smartsupp.com
 *
 * Plugin Name:       Smartsupp Live Chat
 * Plugin URI:        http://www.smartsupp.com
 * Description:       Adds Smartsupp Live Chat code to PrestaShop.
 * Version:           2.2.5
 * Author:            Smartsupp
 * Author URI:        http://www.smartsupp.com
 * Text Domain:       smartsupp
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

use PrestaShop\Module\PsEventbus\Service\PresenterService;
use PrestaShop\PsAccountsInstaller\Installer\Facade\PsAccounts;
use Smartsupp\LiveChat\Utility\PriceUtility;
use Smartsupp\LiveChat\Utility\VersionUtility;

if (!defined('_PS_VERSION_')) {
    exit;
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

class Smartsupp extends Module
{
    const PRESTASHOP_CLOUDSYNC_CDC = 'https://assets.prestashop3.com/ext/cloudsync-merchant-sync-consent/latest/cloudsync-cdc.js';

    /**
     * @var PrestaShop\ModuleLibServiceContainer\DependencyInjection\ServiceContainer
     */
    private $serviceContainer;

    public function __construct()
    {
        // Parameter for cloudsync consent component
        $this->useLightMode = true;

        $this->name = 'smartsupp';
        $this->tab = 'advertising_marketing';
        $this->version = '2.2.5';
        $this->author = 'Smartsupp';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => _PS_VERSION_];
        $this->bootstrap = true;
        $this->module_key = 'da5110815a9ea717be24a57b804d24fb';

        parent::__construct();

        $this->displayName = $this->l('Smartsupp Live Chat & AI Chatbots');
        $this->description = $this->l('Smartsupp is your personal online shopping assistant, built to increase conversion rates and sales via visitor engagement in real-time, at the right time.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall Smartsupp Live Chat? You will lose all the data related to this module.');

        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            include _PS_MODULE_DIR_ . $this->name . '/backward_compatibility/backward.php';
        }

        if (!Configuration::get('SMARTSUPP_KEY')) {
            $this->warning = $this->l('No Smartsupp key provided.');
        }
    }

    public function install()
    {
        // Must succeed before proceeding
        if (!parent::install()) {
            return false;
        }

        if (version_compare(_PS_VERSION_, '1.6', '>=') && Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        // Register appropriate hook
        $hookRegistered = version_compare(_PS_VERSION_, '1.6', '>=')
            ? $this->registerHook('displayBackOfficeHeader')
            : $this->registerHook('backOfficeHeader');

        if (!$hookRegistered || !$this->registerHook('displayHeader')) {
            return false;
        }

        // Create Tab
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminSmartsuppAjax';
        $tab->name = [];

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Smartsupp';
        }

        $tab->id_parent = -1;
        $tab->module = $this->name;

        if (!$tab->add()) {
            return false;
        }

        // Configuration values to be stored
        $configValues = [
            'SMARTSUPP_KEY'                => '',
            'SMARTSUPP_EMAIL'              => '',
            'SMARTSUPP_CUSTOMER_ID'        => '1',
            'SMARTSUPP_CUSTOMER_NAME'      => '1',
            'SMARTSUPP_CUSTOMER_EMAIL'     => '1',
            'SMARTSUPP_CUSTOMER_PHONE'     => '1',
            'SMARTSUPP_CUSTOMER_ROLE'      => '1',
            'SMARTSUPP_CUSTOMER_SPENDINGS' => '1',
            'SMARTSUPP_CUSTOMER_ORDERS'    => '1',
            'SMARTSUPP_OPTIONAL_API'       => '',
        ];

        foreach ($configValues as $key => $value) {
            if (!Configuration::updateValue($key, $value)) {
                return false;
            }
        }

        return true;
    }

    public function uninstall()
    {
        if (VersionUtility::isPsVersionGreaterOrEqualTo('1.7.1')) {
            $id_tab = $this->get('prestashop.core.admin.tab.repository')->findOneIdByClassName('AdminSmartsuppAjax');
        } else {
            $id_tab = (int) Tab::getIdFromClassName('AdminSmartsuppAjax');
        }

        if ($id_tab) {
            $tab = new Tab($id_tab);
            $tab->delete();
        }

        if (VersionUtility::isPsVersionGreaterOrEqualTo('1.6')) {
            $this->unregisterHook('displayBackOfficeHeader');
        } else {
            $this->unregisterHook('backOfficeHeader');
        }

        if (!parent::uninstall()
            || !$this->unregisterHook('displayHeader')
            || !Configuration::deleteByName('SMARTSUPP_KEY')
            || !Configuration::deleteByName('SMARTSUPP_EMAIL')
            || !Configuration::deleteByName('SMARTSUPP_CUSTOMER_ID')
            || !Configuration::deleteByName('SMARTSUPP_CUSTOMER_NAME')
            || !Configuration::deleteByName('SMARTSUPP_CUSTOMER_EMAIL')
            || !Configuration::deleteByName('SMARTSUPP_CUSTOMER_PHONE')
            || !Configuration::deleteByName('SMARTSUPP_CUSTOMER_ROLE')
            || !Configuration::deleteByName('SMARTSUPP_CUSTOMER_SPENDINGS')
            || !Configuration::deleteByName('SMARTSUPP_CUSTOMER_ORDERS')
            || !Configuration::deleteByName('SMARTSUPP_OPTIONAL_API')
        ) {
            return false;
        }

        return true;
    }

    public function displayForm()
    {
        $default_lang = (int) Configuration::get('PS_LANG_DEFAULT');

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

        // Language
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit' . $this->name;
        $helper->toolbar_btn = array(
            'save' =>
            array(
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name .
                '&token=' . Tools::getAdminTokenLite('AdminModules'),
            ),
            'back' => array(
                'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            )
        );

        $fields_form = array();

        $fields_desc = $this->l('Don\'t put the chat code here - this box is for ');
        $fields_desc .= $this->l('(optional) advanced customizations via ') . '#.';

        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Settings')
            ),
            'input' => array(
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Optional API'),
                    'name' => 'SMARTSUPP_OPTIONAL_API',
                    'desc' => $this->l($fields_desc),
                    'autoload_rte' => false,
                    'rows' => 5
                )
            ),
            'submit' => array(
                'title' => $this->l('Save')
            )
        );

        $helper->fields_value['SMARTSUPP_OPTIONAL_API'] = Configuration::get('SMARTSUPP_OPTIONAL_API');

        return $helper->generateForm($fields_form);
    }

    public function getContent()
    {
        $psDependencies = '';

        try {
            if (VersionUtility::isPsVersionGreaterOrEqualTo('1.7.0')) {
                $mboInstaller = new Prestashop\ModuleLibMboInstaller\DependencyBuilder($this);

                if (!$mboInstaller->areDependenciesMet()) {
                    $dependencies = $mboInstaller->handleDependencies();
                    $this->context->smarty->assign('dependencies', $dependencies);

                    $psDependencies = $this->context->smarty->fetch($this->getLocalPath() . 'views/templates/admin/dependency_builder.tpl');
                }
            }

            $this->loadPsAccounts();
            $this->loadCloudSync();

            $psDependencies .= $this->context->smarty->fetch($this->getLocalPath() . 'views/templates/admin/ps_accounts.tpl');
            $psDependencies .= $this->context->smarty->fetch($this->getLocalPath() . '/views/templates/admin/cloudsync.tpl');

        } catch (\Exception $exception) {
            $this->errors[] = $this->l('Unable to load your PrestaShop accounts details.', 'SmartsUpp');
            \PrestaShopLogger::addLog($exception->getMessage(), 3, null, 'SmartsUpp', null, true);
        }

        $output = '';
        if (Tools::isSubmit('submit' . $this->name)) {
            $smartsupp_key = Configuration::get('SMARTSUPP_KEY');
            if ($smartsupp_key) {
                $output .= $this->displayConfirmation($this->l('Settings updated successfully'));
            }
            Configuration::updateValue('SMARTSUPP_OPTIONAL_API', Tools::getValue('SMARTSUPP_OPTIONAL_API'));
        }

        if (version_compare(_PS_VERSION_, '1.6', '>=')) {
            $output .= $this->displayForm();
        }

        $ajax_controller_url = $this->context->link->getAdminLink('AdminSmartsuppAjax');
        $this->context->smarty->assign(
            array(
            'ajax_controller_url' => $ajax_controller_url,
            'smartsupp_key' => Configuration::get('SMARTSUPP_KEY'),
            'smartsupp_email' => Configuration::get('SMARTSUPP_EMAIL'),
            )
        );

        return $psDependencies .
                $this->display(__FILE__, 'views/templates/admin/landing_page.tpl') .
                $this->display(__FILE__, 'views/templates/admin/connect_account.tpl') .
                $this->display(__FILE__, 'views/templates/admin/configuration.tpl') .
                $output;
    }

    /**
     * @param  $smartsupp_key
     * @return string
     * @throws Exception
     */
    protected function getSmartsuppJs($smartsupp_key)
    {
        if (empty($smartsupp_key)) {
            return '';
        }

        /*
        NEVER ever put it into use statement as this will break Presta 1.6 installation
        - they use eval which does not run PSR autoloader
        */
        $chat = new \Smartsupp\ChatGenerator($smartsupp_key);
        $chat->setPlatform('Prestashop ' . _PS_VERSION_);
        $chat->setCookieDomain('.' . Tools::getHttpHost(false));

        $customer = $this->context->customer;

        if ($customer->id) {
            if (Configuration::get('SMARTSUPP_CUSTOMER_ID')) {
                $chat->setVariable('id', $this->l('ID'), $customer->id);
            }

            if (Configuration::get('SMARTSUPP_CUSTOMER_NAME')) {
                $customer_name = $customer->firstname . ' ' . $customer->lastname;
                $chat->setVariable('name', $this->l('Name'), $customer_name);
                $chat->setName($customer_name);
            }

            if (Configuration::get('SMARTSUPP_CUSTOMER_EMAIL')) {
                $chat->setVariable('email', $this->l('Email'), $customer->email);
                $chat->setEmail($customer->email);
            }

            if (Configuration::get('SMARTSUPP_CUSTOMER_PHONE')) {
                $addresses = $this->context->customer->getAddresses($this->context->language->id);
                if (!empty($addresses[0])) {
                    $first_address = $addresses[0];
                    $phone = !empty($first_address['phone_mobile'])
                        ? $first_address['phone_mobile'] : $first_address['phone'];
                    $chat->setVariable('phone', $this->l('Phone'), $phone);
                }
            }

            if (Configuration::get('SMARTSUPP_CUSTOMER_ROLE')) {
                $group = new Group($customer->id_default_group, $this->context->language->id, $this->context->shop->id);
                $chat->setVariable('role', $this->l('Role'), $group->name);
            }

            if (Configuration::get('SMARTSUPP_CUSTOMER_SPENDINGS') || Configuration::get('SMARTSUPP_CUSTOMER_ORDERS')) {
                $orders = Order::getCustomerOrders($customer->id, true);
                $count = 0;
                $spending = 0;
                foreach ($orders as $order) {
                    if ($order['valid']) {
                        $count++;
                        $spending += $order['total_paid_real'];
                    }
                }
                if (Configuration::get('SMARTSUPP_CUSTOMER_SPENDINGS')) {
                    $chat->setVariable(
                        'spending',
                        $this->l('Spendings'),
                        PriceUtility::displayPrice(
                            $spending,
                            $this->context->currency->id
                        )
                    );
                }
                if (Configuration::get('SMARTSUPP_CUSTOMER_ORDERS')) {
                    $chat->setVariable('orders', $this->l('Orders'), $count);
                }
            }
        }

        return $chat->render();
    }

    public function hookDisplayHeader()
    {
        $smartsupp_key = Configuration::get('SMARTSUPP_KEY');
        $this->smarty->assign([
            'smartsupp_js' => $this->getSmartsuppJs($smartsupp_key),
            'smartsupp_optional_api' => $smartsupp_key ? trim(Configuration::get('SMARTSUPP_OPTIONAL_API')) : '',
        ]);

        return $this->display(__FILE__, './views/templates/front/chat_widget.tpl');
    }

    public function hookBackOfficeHeader()
    {
        $js = '';

        if (strcmp(Tools::getValue('configure'), $this->name) === 0) {
            \Media::addJsDef([
                'smartsupp' => [
                    'genericAjaxErrorMessage' => $this->l('Unknown error occurred. Try again or contact support.'),
                ],
            ]);

            $this->context->smarty->assign([
                'smartsupp_module_path' => $this->_path,
            ]);

            $js .= $this->display(__FILE__, 'views/templates/admin/backoffice_header.tpl');
        }

        return $js;
    }

    public function hookDisplayBackOfficeHeader()
    {
        $js = '';

        if (strcmp(Tools::getValue('configure'), $this->name) === 0) {
            \Media::addJsDef([
                'smartsupp' => [
                    'genericAjaxErrorMessage' => $this->l('Unknown error occurred. Try again or contact support.'),
                ],
            ]);

            $path = $this->_path;

            if (!VersionUtility::isPsVersionGreaterOrEqualTo('9.0.0')) {
                $this->context->controller->addJquery();
            }

            $this->context->controller->addJs($path . 'views/js/smartsupp.js');
            $this->context->controller->addCSS($path . 'views/css/smartsupp.css');
            $this->context->controller->addCSS($path . 'views/css/smartsupp-nobootstrap.css');
        }

        return $js;
    }

    /**
     * @return string
     *
     * @throws \PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleNotInstalledException
     * @throws \PrestaShop\PsAccountsInstaller\Installer\Exception\ModuleVersionException
     */
    private function loadPsAccounts()
    {
        /** @var PsAccounts $accountsFacade */
        $accountsFacade = $this->getService(PsAccounts::class);

        $psAccountsPresenter = $accountsFacade->getPsAccountsPresenter();
        $psAccountsService = $accountsFacade->getPsAccountsService();

        $smartsuppVar = $this->context->smarty->getTemplateVars('smartsupp');
        $existing = $smartsuppVar !== null ? $smartsuppVar : [];

        $this->context->smarty->assign('smartsupp', array_merge_recursive($existing, [
            'url' => [
                'psAccountsCdnUrl' => $psAccountsService->getAccountsCdn(),
            ],
        ]));

        $previousJsDef = isset(\Media::getJsDef()['smartsupp']) ? \Media::getJsDef()['smartsupp'] : [];

        \Media::addJsDef([
            'contextPsAccounts' => $psAccountsPresenter->present(),
            'smartsupp' => array_merge($previousJsDef, [
                'isPsAccountsLinked' => $psAccountsService->isAccountLinked(),
            ]),
        ]);
    }

    /**
     * @return void
     *
     * @throws PrestaShopException
     */
    private function loadCloudSync()
    {
        $eventbusModule = \Module::getInstanceByName('ps_eventbus');

        if (!$eventbusModule) {
            \PrestaShopLogger::addLog('Module ps_eventbus not found', 3, null, 'SmartsUpp', null, true);
            return;
        }

        /** @var PresenterService $eventbusPresenterService */
        $eventbusPresenterService = $eventbusModule->getService(PresenterService::class);

        $smartsuppVar = $this->context->smarty->getTemplateVars('smartsupp');
        $existing = $smartsuppVar !== null ? $smartsuppVar : [];

        $this->context->smarty->assign('smartsupp', array_merge_recursive($existing, [
            'url' => [
                'cloudSyncPathCDC' => defined('self::PRESTASHOP_CLOUDSYNC_CDC')
                    ? self::PRESTASHOP_CLOUDSYNC_CDC
                    : '',
                ],
        ]));

        $previousJsDef = isset(\Media::getJsDef()['smartsupp']) ? \Media::getJsDef()['smartsupp'] : [];

        \Media::addJsDef([
            'contextPsEventbus' => $eventbusPresenterService->expose($this, ['info']),
            'smartsupp' => array_merge($previousJsDef, [
                'url' => [
                    'cloudSyncPathCDC' => defined('self::PRESTASHOP_CLOUDSYNC_CDC')
                        ? self::PRESTASHOP_CLOUDSYNC_CDC
                        : '',
                ],
            ]),
        ]);
    }

    /**
     * @param string $serviceName
     *
     * @return mixed
     */
    public function getService($serviceName)
    {
        if ($this->serviceContainer === null) {
            $this->serviceContainer = new \PrestaShop\ModuleLibServiceContainer\DependencyInjection\ServiceContainer(
                $this->name . str_replace(['.', '-', '+'], '', $this->version),
                $this->getLocalPath()
            );
        }

        return $this->serviceContainer->getService($serviceName);
    }
}

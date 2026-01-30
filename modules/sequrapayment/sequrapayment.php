<?php
if (!defined('_PS_VERSION_')) {
    exit;
}
require_once _PS_MODULE_DIR_ . '/sequrapayment/lib/SequraTools.php';
require_once _PS_MODULE_DIR_ . '/sequrapayment/lib/SequraOrderBuilder.php';
require_once _PS_MODULE_DIR_ . '/sequrapayment/lib/SequraReportBuilder.php';
require_once _PS_MODULE_DIR_ . '/sequrapayment/lib/SequraClient.php';
require_once _PS_MODULE_DIR_ . '/sequrapayment/lib/SequraCrontab.php';
require_once _PS_MODULE_DIR_ . '/sequrapayment/lib/SequraReporter.php';
require_once _PS_MODULE_DIR_ . '/sequrapayment/lib/SequraInstaller.php';
require_once _PS_MODULE_DIR_ . '/sequrapayment/lib/SequraConfig.php';
require_once _PS_MODULE_DIR_ . '/sequrapayment/lib/SequraFee.php';
require_once _PS_MODULE_DIR_ . '/sequrapayment/lib/SequraProductExtra.php';
require_once _PS_MODULE_DIR_ . '/sequrapayment/lib/SequraIdentification.php';
require_once _PS_MODULE_DIR_ . '/sequrapayment/lib/SequraOrderConfirmer.php';
require_once _PS_MODULE_DIR_ . '/sequrapayment/lib/SequraPSOrderUpdater.php';
require_once _PS_MODULE_DIR_ . '/sequrapayment/lib/SequraOrderUpdater.php';
require_once _PS_MODULE_DIR_ . '/sequrapayment/lib/SequraPreQualifier.php';
require_once _PS_MODULE_DIR_ . '/sequrapayment/lib/SequraPaymentModuleTrait.php';

define('SEQURA_ERROR_PAYMENT', 100);
define('SEQURA_ERROR_CART_CHANGED', 200);

/**
 * Core module for SeQura Payment
 */

class Sequrapayment extends Module
{
    /**
     * External entry points
     **/
    const CRONJOB_NAME = 'submitDailyReport';
    const CSS_FILE = 'css/custom.css';
    public static $VERSION = '5.0.4';
    public static $user_agent = null;

    // Protect against unexpected callers
    public static $thirdPartyOnePagers = array(
        'onepagecheckout',
        'onepagecheckoutps',
        'esp_1stepcheckout',
        'threepagecheckout'
    );
    public $secret_handshake = false;
    public $qualifier = null;
    private $client = null;
    private $reporter = null;

    public function __construct()
    {
        $this->name    = 'sequrapayment';
        $this->tab     = 'payments_gateways';
        $this->version = self::$VERSION;
        $this->author  = 'Sequra Engineering';
        parent::__construct();
        $this->bootstrap        = true;
        $this->description      = $this->l('Configuración general para los métodos de pago Sequra');
        $this->displayName      = $this->l('Sequra Payment Services');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        /* Backward compatibility */
        if (_PS_VERSION_ < 1.5) {
            include _PS_MODULE_DIR_ . $this->name . '/backward_compatibility/backward.php';
            $this->context->smarty->assign(
                array('base_dir' => __PS_BASE_URI__
            ));
        }
    }

    public static function needsBasicPresentation()
    {
        foreach (self::$thirdPartyOnePagers as $name) {
            if (SequraTools::isModuleActive($name)) {
                return $name;
            }
        }

        return false;
    }

    public static function removeSequraOrderFromSession()
    {
        if (version_compare(_PS_VERSION_, '1.6.1') >= 0) {
            $cookie_array = Context::getContext()->cookie->getAll();
            foreach ($cookie_array as $key => $value) {
                if (preg_match('/sequra(.*)_order/', $key)) {
                    Context::getContext()->cookie->__unset($key);
                }
            }
        } else {
            $inferred_country = self::inferCountry();
            $active_methods = unserialize(Configuration::get('SEQURA_ACTIVE_METHODS_'.$inferred_country));
            foreach ($active_methods as $key => $value) {
                Context::getContext()->cookie->__unset($key . '_order');
            }
        }
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }
        $installer = new SequraInstaller($this);
        $res       = $installer->install();

        return $res;
    }

    public function uninstall()
    {
        $installer = new SequraInstaller($this);
        if (! $installer->uninstall()) {
            return false;
        }

        return parent::uninstall();
    }

    public function refuse()
    {
        echo '<p>' .
            $this->l('Para pagar con Sequra, el mismo cliente tiene que pasar por la pasarela de pago.') .
            '</p>';
        exit;
    }

    public function addCSS($uri)
    {
        if (version_compare(_PS_VERSION_, '1.7', '>=') == true) {
            $server = 'remote';
            if (strpos($uri, __PS_BASE_URI__) === 0) {
                $uri    = substr($uri, strlen(__PS_BASE_URI__));
                $server = 'local';
            }

            return $this->context->controller->registerStylesheet(
                sha1(self::$VERSION . $uri),
                $uri,
                array('media' => 'all', 'priority' => 80, 'server' => $server)
            );
        }

        return $this->context->controller->addCSS($uri);
    }

    //Preparing data for the form

    public function addJS($uri, $priority = 80)
    {
        if (version_compare(_PS_VERSION_, '1.7', '>=') == true) {
            $server = 'remote';
            if (strpos($uri, __PS_BASE_URI__) == 0) {
                $uri    = substr($uri, strlen(__PS_BASE_URI__));
                $server = 'local';
            }

            return $this->context->controller->registerJavascript(
                sha1(self::$VERSION . $uri),
                $uri,
                array('position' => 'bottom', 'priority' => $priority, 'server' => $server)
            );
        }

        return $this->context->controller->addJS($uri);
    }

    //Saving Data

    //HOOKS
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
            if (version_compare(_PS_VERSION_, '1.6', '<') == true) {
                $this->context->controller->addCSS($this->_path . 'css/bootstrap.min.css');
                $this->context->controller->addCSS($this->_path . 'css/configure-ps-15.css');
            } else {
                $this->context->controller->addCSS($this->_path . 'css/configure-ps-16.css');
            }
        }
    }

    public function hookHeader($params)
    {
        return $this->hookDisplayHeader($params);
    }

    // For PS version 1.4

    public function hookDisplayHeader($params)
    {
        SequraCrontab::poolCron();
        $scriptBaseUri = self::getScriptBaseUri();
        if (_PS_VERSION_ >= 1.6) {
            $this->addCSS($scriptBaseUri . 'css/prestashop_rebranded_16.css', 'all');
        } else {
            $this->addCSS($scriptBaseUri . 'css/prestashop_rebranded_15.css', 'all');
        }
        $config = new SequraConfig($this);
        if (file_exists($config->getCustomCssPath())) {
            $this->context->controller->addCSS($this->_path . self::CSS_FILE);
        }
        $this->addCSS($this->_path . 'css/banner.css', 'all');
        $this->addJS($this->_path . 'js/sequrapaymentpscheckout.js');
        $inferred_country = self::inferCountry();
        if(!in_array(
            $inferred_country,
            $this->getCountries()
        )){
            return;
        }
        $this->context->smarty->assign(
            array(
                'merchant'          => Configuration::get('SEQURA_MERCHANT_ID_'.$inferred_country),
                'assetKey'          => Configuration::get('SEQURA_ASSETS_KEY'),
                'sequra_products'   => unserialize(Configuration::get('SEQURA_ACTIVE_METHODS_'.$inferred_country)),
                'scriptBaseUri'     => $scriptBaseUri,
                'light_design'      => Configuration::get('SEQURA_CHECKOUT_SERVICE_NAME')?'true':'false', //@todo by now
                'locale'            => $this->context->language->iso_code,
                'silent'            => $this->context->language->iso_code=='es'?'false':'true'
            )
        );
        if (version_compare(_PS_VERSION_, '1.7', '>=') == true) {
            $formatter = new PrestaShop\PrestaShop\Adapter\Product\PriceFormatter();
            $format = $formatter->format(1234.56); //@todo: Find smarter way to get decimal separator
            preg_match('/1([^2]*)234(.)56/', $format, $sep);
            $this->context->smarty->assign(
                array(
                'decimalSeparator'  => $sep[2]?$sep[2]:',',
                'thousandSeparator' => $sep[1]
                )
            );
        } else {
            $currency_fomats = array(
                1 => array(',', '.', 1),
                2 => array(' ', ',', 0),
                3 => array('.', ',', 1),
                4 => array(',', '.', 0),
                5 => array('\'', '.', 0)
            );
            $currency        = new Currency((int)$this->context->currency->id);
            $this->context->smarty->assign(
                array(
                'decimalSeparator'  => $currency_fomats[$currency->format][1]?
                        $currency_fomats[$currency->format][1]:
                        ',',
                'thousandSeparator' => $currency_fomats[$currency->format][0]
                )
            );
        }
        $tpl = 'header.tpl';

        return $this->display(__FILE__, 'views/' . $tpl);
    }

    public function hookDisplayAdminProductsExtra($params)
    {
        $id_product = isset($params['id_product']) ? $params['id_product'] : Tools::getValue('id_product');
        $sq_product_extra = new SequraProductExtra($id_product);
        $this->context->smarty->assign(
            array(
                'sequra_is_banned'                  => (bool)$sq_product_extra->getProductIsBanned(),
                'sequra_for_services'               => (bool)Configuration::get('SEQURA_FOR_SERVICES'),
                'sequra_allow_registration_items'   => (bool)Configuration::get('SEQURA_ALLOW_REGISTRATION_ITEMS'),
                'sequra_allow_payment_delay'        => (bool)Configuration::get('SEQURA_ALLOW_PAYMENT_DELAY'),
                'sequra_is_service'                 => (bool)$sq_product_extra->getProductIsService(),
                'sequra_service_end_date'           => $sq_product_extra->getProductServiceEndDate(),
                'sequra_desired_first_charge_date'  => $sq_product_extra->getProductFirstChargeDate(),
                'sequra_registration_amount'        => $sq_product_extra->getProductRegistrationAmount(),
                'ISO8601_PATTERN'                   => SequraTools::ISO8601_PATTERN
            )
        );
        $tpl = 'productsextra.tpl';
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $tpl = 'productsextra_17.tpl';
        }

        return $this->display(__FILE__, 'views/admin/' . $tpl);
    }

    public function hookActionProductUpdate($params)
    {
        $sq_product_extra = new SequraProductExtra($params['id_product']);
        $sq_product_extra->save($this);
    }

    public function hookPostUpdateOrderStatus($params)
    {
        return $this->hookActionOrderStatusPostUpdate($params);
    }

    public function hookActionOrderStatusPostUpdate($params)
    {
        if ($params['newOrderStatus']->shipped) {
            $updater = SequraOrderUpdater::getInstance($this, $params['id_order']);
            $updater->orderUpdateIfNeeded();
        }
        if (! Configuration::get('SEQURA_SEND_CANCELLATIONS')) {
            return;
        }
        $canceller = SequraPSOrderUpdater::getInstance('admin', $params['id_order']);
        if ($canceller->checkIfPreconditionsAreMissing($params['newOrderStatus']->id)) {
            return;
        }
        $canceller->cancelWithSequra($params['cart']);
    }

    public function hookDisplayAdminOrder($params)
    {
        $order = new Order((int) Tools::getValue('id_order'));
        if (strpos($order->module, 'sequra') === false &&
            count($order->getOrderPayments()) >= 1
        ) {
            return;
        }
        $tpl = 'sequrapayment_adminorder.tpl';
        if (count($order->getOrderPayments()) >= 1) {
            $endpoint = (
                Configuration::get('SEQURA_MODE') != 'live'?
                'https://simbox.sequrapi.com/orders/':'https://simba.sequra.es/orders/'
            );
            $payments = $order->getOrderPayments();
            $uuid = $payments[0]->transaction_id;
            $this->context->smarty->assign(array(
                'simba_link' => $endpoint . $uuid
            ));
            $tpl = 'sequrapayment_adminorder_simbalink.tpl';
        } elseif (count($order->getOrderPayments()) < 1) {
            $this->context->smarty->assign(array(
                'send_payment_button' => true
            ));
            $tpl = 'sequrapayment_adminorder_paymentbutton.tpl';
        }
        return $this->display(__FILE__, './views/admin/'.$tpl);
    }

    // END hooks and rendering

    // WebHook actions
    public function cancelOrderFromWebhook()
    {
        $updater = SequraPSOrderUpdater::getInstance("webhook");
        $updater->processCancellationRequest();
    }

    public function cancelledOrderFromWebhook()
    {
        $updater = SequraPSOrderUpdater::getInstance("webhook");
        $updater->cancelOrder();
    }

    public function setRiskLevelToOrder()
    {
        $updater = SequraPSOrderUpdater::getInstance("webhook");
        $updater->setRiskLevelToOrder();
    }

    // End WebHook actions

    public function getClient()
    {
        if (! $this->client) {
            SequraClient::$debug      = Configuration::get('SEQURA_MODE') != 'live';
            SequraClient::$endpoint   = Configuration::get('SEQURA_MODE') == 'live' ?
                Configuration::get('SEQURA_LIVE_ENDPOINT') : Configuration::get('SEQURA_SANDBOX_ENDPOINT');
            SequraClient::$user       = Configuration::get('SEQURA_USER');
            SequraClient::$password   = Configuration::get('SEQURA_PASS');
            SequraClient::$user_agent =
                'cURL PrestaShop ' . _PS_VERSION_ . ' plugin v' . $this->version . ' php ' . phpversion();
            $this->client            = new SequraClient();
        }

        return $this->client;
    }

    public function getContent()
    {
        return SequraConfig::getContent($this);
    }

    public function submitDailyReport()
    {
        if ($_SERVER['HTTP_USER_AGENT'] == 'sequra-cron') {
            ob_start();
            header('Location: /');
            echo ' ';
            // flush any buffers and send the headers
            while (@ob_end_flush()) {
            }
            flush();
        }

        // This would run in background if UA- sequra-cron
        return $this->reporter()->submitDailyReport();
    }

    public function reporter()
    {
        return $this->reporter ? $this->reporter : ($this->reporter = new SequraReporter($this));
    }

    /*** ***/

    public function getProduct($id_product = null, $full = true, $id_lang = null)
    {
        global $cookie;
        $id_product |= Tools::getValue('id_product');
        $id_lang    |= $cookie->id_lang;

        return new Product($id_product, true, $id_lang);
    }

    public function getModuleViewsDirectory()
    {
        return _PS_MODULE_DIR_ . $this->name . '/views';
    }

    public function getPath()
    {
        return $this->_path;
    }

    public static function getScriptBaseUri()
    {
        return Configuration::get('SEQURA_MODE') == 'live' ?
            Configuration::get('SEQURA_LIVE_SCRIPT_BASE_URI') : Configuration::get('SEQURA_SANDBOX_SCRIPT_BASE_URI');
    }

    public function getCountries(){
        $results = Db::getInstance()->executeS(
            'SELECT cc.iso_code from `'._DB_PREFIX_.'module_country` c 
                LEFT JOIN  `'._DB_PREFIX_.'module` m on m.id_module=c.id_module
                LEFT JOIN  `'._DB_PREFIX_.'country` cc on c.id_country=cc.id_country
            WHERE m.name="sequracheckout" and c.id_shop=' . (int) Context::getContext()->shop->id
        );
        if(count($results)<1){
            return array('ES');
        }
        return array_map(
            function ($item){
                return $item['iso_code'];
            },
            $results
        );
    }

    private static function inferCountry(){
        $ret = strtoupper(Context::getContext()->language->iso_code);
        if(in_array($ret, array('CA','EU','GA'))){
            return 'ES';
        }
        return $ret;
    }
    // Unexpected protections in prestashop forces us to add accessors to enable delegation

    private function usesMultipage()
    {
        return Configuration::get('PS_ORDER_PROCESS_TYPE') == 0;
    }

    private function renderView($name)
    {
        return $this->display(__FILE__, 'views/' . $name . '.tpl');
    }

    /**
     * @deprecated 3.0.0
     * Left here not to break compatibility with presteamshop's One Page Checkout
     */
    public function fee()
    {
        return new SequraFee();
    }
}

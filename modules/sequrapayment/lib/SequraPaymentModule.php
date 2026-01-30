<?php
/**
 * 
 * 
 * 
 * 
 * 
 * DEPRECATED PRESENT ONLY TO PREVENT BREAKING WHEN UPGRADING
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

abstract class SequraPaymentModule extends PaymentModule
{
    protected $sequra;
    protected $builder;
    protected $qualifier;
    protected $logo_url;

    public function __construct()
    {
        $this->tab = 'payments_gateways';
        $this->author = 'Sequra Engineering';
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->limited_countries = array('es');
        $this->need_instance = 0;
        $this->is_eu_compatible = 1;

        parent::__construct();
        $this->bootstrap = true;
        $this->ps_versions_compliancy = array('min' => '1.4', 'max' => '1.7.99.99');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstard compatibility');
        if (_PS_VERSION_ < 1.5) {
            include _PS_MODULE_DIR_ . SEQURA_CORE . '/backward_compatibility/backward.php';
            $this->context->smarty->assign('base_dir', __PS_BASE_URI__);
        }
        $this->uses_fee = false;
        //In case there is no cart in the context but we have the id_cart
        if (is_null($this->context->cart) && $this->context->cookie->id_cart) {
            $this->context->cart = new Cart($this->context->cookie->id_cart);
        }
    }

    /**
     * @return bool
     */
    public function install()
    {
        if (!parent::install()) {
            return false;
        }
        if (!Module::isInstalled(SEQURA_CORE)) {
            $sequra = $this->getSequraCore();
            if (!$sequra->install()) {
                return false;
            }
        }

        return $this->getInstaller()->install() &&
               $this->enable();
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        $installer = $this->getInstaller();
        if (!$installer->uninstall()) {
            return false;
        }

        return parent::uninstall() &&
            $this->disable();
    }

    public function getContent()
    {
        $this->html = null;
        if (Tools::isSubmit('btnSubmit')) {
            $this->postValidation();
            if (count($this->post_errors) == 0) {
                $this->postProcess();
            }
        }
        $this->displayForm();

        return $this->html;
    }

    public function setVariables($cart)
    {
        $linker = $this->context->link;
        if (_PS_VERSION_ >= 1.5) {
            $ajax_form_url = $linker->getModuleLink(
                $this->name,
                'getidentificationform',
                array(),
                true
            );
            $form_url = $linker->getModuleLink(
                $this->name,
                'identification',
                array(),
                true
            );
        } else {
            $id_lang = $this->context->language->id;
            $form_url = $linker->getPageLink(
                'modules/'.$this->name.'/identification.php',
                true,
                $id_lang,
                array()
            );
            $ajax_form_url = $linker->getPageLink(
                'modules/'.$this->name.'/getidentificationform.php',
                true,
                $id_lang,
                array()
            );
        }
        $vars = array(
            'ajax_form_url' => $ajax_form_url,
            'method' => $this->name,
            'form_url' => $form_url,
            'total_price' => $cart->getOrderTotal(),
            'call_to_action_text' => $this->getCallToActionText(),
            'sequrapayment_js' => __PS_BASE_URI__ . 'modules/' . SEQURA_CORE . '/js/sequrapayment.js'
        );
        $this->context->smarty->assign($vars);
    }

    /*HOOKS*/
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
            if (version_compare(_PS_VERSION_, '1.6', '<') == true) {
                $this->context->controller->addCSS($this->getSequraCore()->getPath() . 'css/bootstrap.min.css');
                $this->context->controller->addCSS($this->getSequraCore()->getPath() . 'css/configure-ps-15.css');
            } else {
                $this->context->controller->addCSS($this->getSequraCore()->getPath() . 'css/configure-ps-16.css');
            }
        }
    }

    public function hookPaymentReturn($params)
    {
        if (!$this->active || _PS_VERSION_ >= 1.7) {
            return null;
        }
        Sequrapayment::removeSequraOrderFromSession();

        $order = $params['objOrder'];

        $vars = array(
            'service_name' => $this->displayName,
            'id_order' => $order->id
        );
        $this->context->smarty->assign($vars);
        if (isset($order->reference) && !empty($order->reference)) {
            $this->smarty->assign('reference', $order->reference);
        }

        return $this->renderView('payment_return');
    }

    public function hookDisplayHeader($params)
    {
        return;
    }

    // For PS version 1.4
    public function hookHeader($params)
    {
        return $this->hookDisplayHeader($params);
    }

    //PS 1.7
    public function hookPaymentOptions($params)
    {
        if (!$this->active || !$this->checkCurrency($params['cart'])) {
            return;
        }
        $qualifier = $this->getQualifier($params['cart']);
        if (!$qualifier->passes()) {
            return;
        }
        $linker = $this->context->link;
        $this->setVariables($params['cart']);
        $payment_options = array();
        $ajax_form_url = $linker->getModuleLink(
            $this->name,
            'getidentificationform',
            array(),
            true
        );
        $sequra_product = $this->getProduct();
        if ($qualifier->priceWithinRange()) {
            $sequraConfig = new SequraConfig($this);
            $sequraOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
            $sequraOption->setCallToActionText($this->getCallToActionText())
                ->setAction(
                    "javascript:SequraIdentificationPopupLoader.url = '$ajax_form_url';" .
                    "SequraIdentificationPopupLoader.product = '$sequra_product';" .
                    "SequraIdentificationPopupLoader.showForm();"
                )->setAdditionalInformation(
                    $this->context->smarty->fetch(
                        $sequraConfig->getPaymentFormTplPath()
                    )
                )->setLogo(Media::getMediaPath($this->logo_url));
            $payment_options[] = $sequraOption;
        }

        return $payment_options;
    }

    public function hookDisplayHome($params)
    {
        return $this->renderWidget('home', $params);
    }

    public function hookFooter($params)
    {
        if (_PS_VERSION_ < 1.6 && $this->page_name == 'product') {
            return $this->renderWidget('product', $params);
        }

        return false;
    }

    public function hookDisplayFooter($params)
    {
        if (_PS_VERSION_ >= 1.6 && $this->page_name == 'product') {
            return $this->renderWidget('product', $params);
        }

        return false;
    }

    public function hookDisplayBeforeBodyClosingTag($params)
    {
        if (_PS_VERSION_ >= 1.7) {
            return $this->renderWidget('product', $params);
        }

        return false;
    }

    public function hookProductFooter($params)
    {
        if (_PS_VERSION_ < 1.7) {
            return $this->renderWidget('product', $params);
        }

        return false;
    }

    public function hookDisplayProductButtons($params)
    {
        return $this->renderWidget('product', $params);
    }

    /* Override */
    public function validateOrder(
        $id_cart,
        $id_order_state,
        $amount_paid,
        $payment_method = 'Unknown',
        $message = null,
        $extra_vars = array(),
        $currency_special = null,
        $dont_touch_amount = false,
        $secure_key = false,
        Shop $shop = null
    ) {
        if (!$this->active) {
            return;
        }
        // Can only be paid with SeQura after secret handshake.  This stops orders from backoffice.
        if (!$this->secret_handshake) {
            $this->refuse();
        }
        if (_PS_VERSION_ < 1.5) {
            parent::validateOrder(
                $id_cart,
                $id_order_state,
                $amount_paid,
                $payment_method,
                $message,
                $extra_vars,
                $currency_special,
                $dont_touch_amount,
                $secure_key
            );
        } else {
            parent::validateOrder(
                $id_cart,
                $id_order_state,
                $amount_paid,
                $payment_method,
                $message,
                $extra_vars,
                $currency_special,
                $dont_touch_amount,
                $secure_key,
                $shop
            );
        }
    }

    public function getIdentificationForm($options)
    {
        $name = $this->name . '_order';
        $identification = new SequraIdentification($this, $this->name);
        $retry = true;
        while ($identification->sequraIsReady()) {
            $client = $this->getSequraCore()->getClient();
            $uri = $this->context->cookie->$name;
            $result = $client->getIdentificationForm($uri, $options);
            if ($client->getStatus() == 200) {
                $this->context->cart->save();
                return $result;
            }
            if (!$retry) {
                $this->context->cookie->$name = '';
                http_response_code($client->getStatus());
                exit;
            }
            $retry = false;
        }
    }

    public function confirmOrder()
    {
        $confirmer = new SequraOrderConfirmer($this, $this->getContext());
        $confirmer->run();
    }

    public function confirmOrderFromIpn()
    {
        $confirmer = new SequraOrderConfirmer($this, "ipn");
        $confirmer->run();
    }

    public function cancelOrderFromWebhook()
    {
        $canceller = SequraOrderCanceller::getInstance($this, "webhook");
        $canceller->processCancellationRequest();
    }

    public function getContext()
    {
        return $this->context;
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getCallToActionText()
    {
        return Configuration::get($this->getConfigName().'_NAME');
    }

    public function displayIdentificationPage($controller = null)
    {
        $identification = new SequraIdentification($this);
        $identification->unsetUri();
        $identification->displayForStandardPurchase($controller);
    }

    //To implement in each method
    abstract public function getProduct();

    abstract public function getCampaign();

    public function renderWidget($hookName, array $params)
    {
        $isPS17 = version_compare(_PS_VERSION_, '1.7', '>=');
        include_once _PS_MODULE_DIR_ . '/' . $this->name . '/lib/' . get_class($this) . 'PreQualifier.php';
        $PreQualifier = get_class($this) . 'PreQualifier';
        if ($hookName=='home') {
            if (!$PreQualifier::canShowBanner($this->getConfigName().'_SHOW_BANNER')) {
                return null;
            }
        } elseif ($hookName=='product') {
            if ($isPS17 && Dispatcher::getInstance()->getController() != 'product') {
                return;
            }
            if (!$PreQualifier::canDisplayInfo($this->getSequraCore()->getProduct()->getPrice())) {
                return null;
            }
        }

        $this->smarty->assign($this->getWidgetVariables($hookName, $params));
        if ($isPS17) {
            return $this->fetch('module:' . $this->name . '/views/'.$hookName.'_widget_17.tpl');
        }

        return $this->renderView($hookName.'_widget');
    }

    public function getConfigName()
    {
        return strtoupper(str_replace('sequra', 'sequra_', $this->name));
    }

    public function getWidgetVariables($hookName, array $configuration)
    {
        $name = $this->getConfigName();
        return array(
            'service_name'       => Configuration::get($name.'_NAME'),
            'min_amount'         => Configuration::get($name.'_MIN'),
            'sequra_product'     => $this->getProduct(),
            'max_amount'         => Configuration::get($name.'_MAX'),
            'css_selector'       => Configuration::get($name.'_CSS_SEL'),
            'css_selector_price' => Configuration::get('SEQURA_CSS_SEL_PRICE'),
            'color'              => Configuration::get($name.'_BANNER_COLOR'),
            'theme'              => Configuration::get($name.'_WIDGET_THEME')
        );
    }

    //PRIVATE FUNCTIONS
    protected function renderView($name)
    {
        return $this->display(static::$FILE, 'views/' . $name . '.tpl');
    }

    protected function getSequraCore()
    {
        if (is_null($this->sequra)) {
            $this->sequra = Module::getInstanceByName(SEQURA_CORE);
        }

        return $this->sequra;
    }

    protected function renderPaymentForm($params)
    {
        switch (Tools::getValue('sequra_error')) {
            case SEQURA_ERROR_CART_CHANGED:
                $vars['sequra_error'] = 'cart_changed';
                break;
            case SEQURA_ERROR_PAYMENT:
                $vars['sequra_error'] = 'payment_error';
                break;
            default:
                $vars['sequra_error'] = false;
        }
        $vars['opc_module'] = Sequrapayment::needsBasicPresentation();
        $this->context->smarty->assign($vars);
        $this->setVariables($params['cart']);
        $sequraConfig = new SequraConfig($this);
        return $this->renderView(
            basename($sequraConfig->getPaymentFormTplPath(), ".tpl")
        );
    }

    public function getOrderBuilder()
    {
        if (!$this->builder) {
            $merchant_id = Configuration::get('SEQURA_MERCHANT_ID');
            $this->builder = new SequraOrderBuilder($merchant_id, $this->context->cart, $this);
        }

        return $this->builder;
    }

    public function enable($force_all = false)
    {
        parent::enable($force_all);
        $active_methods = unserialize(Configuration::get('SEQURA_ACTIVE_METHODS'));
        if (!$active_methods) {
            $active_methods = array();
        }
        $active_methods[$this->name] = $this->getProduct();
        Configuration::updateValue('SEQURA_ACTIVE_METHODS', serialize($active_methods));
        return true;
    }

    public function disable($force_all = false)
    {
        parent::disable($force_all);
        $active_methods = unserialize(Configuration::get('SEQURA_ACTIVE_METHODS'));
        if ($active_methods) {
            unset($active_methods[$this->name]);
        }
        Configuration::updateValue('SEQURA_ACTIVE_METHODS', serialize($active_methods));
        return true;
    }

    //FEE FUNCTIONS
    private $fee = null;

    public function fee()
    {
        return $this->fee ? $this->fee : ($this->fee = new SequraFee($this));
    }

    private $_reporter = null;

    public function reporter()
    {
        return $this->_reporter ? $this->_reporter : ($this->_reporter = new SequraReporter($this));
    }

    public function getQualifier($cart)
    {
        if (is_null($this->qualifier)) {
            include_once _PS_MODULE_DIR_ . '/' . $this->name . '/lib/' . get_class($this) . 'PreQualifier.php';
            $qualifier_class = get_class($this) . 'PreQualifier';
            $this->qualifier = new $qualifier_class($cart);
        }

        return $this->qualifier;
    }

    protected function getInstaller()
    {
        include_once _PS_MODULE_DIR_ . '/' . $this->name . '/lib/' . get_class($this) . 'Installer.php';
        $installer_class = get_class($this) . 'Installer';

        return new $installer_class($this);
    }
}

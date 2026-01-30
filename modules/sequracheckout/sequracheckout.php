<?php
if (!defined('_PS_VERSION_')) {
    exit;
}
if (!defined('SEQURA_CORE')) {
    define('SEQURA_CORE', 'sequrapayment');
}
if (file_exists(_PS_MODULE_DIR_ . SEQURA_CORE . '/' . SEQURA_CORE . '.php')) {
    include_once _PS_MODULE_DIR_ . SEQURA_CORE . '/' . SEQURA_CORE . '.php';
    include_once _PS_MODULE_DIR_ . '/sequracheckout/lib/SequracheckoutConfigData.php';
    include_once _PS_MODULE_DIR_ . '/sequracheckout/lib/SequracheckoutPreQualifier.php';

    class Sequracheckout extends PaymentModule
    {
        use SequraPaymentModuleTrait;
        public static $VERSION = '2.0.2';
        protected static $FILE = __FILE__;
        protected static $footer_added = false;

        public function __construct()
        {
            $this->name    = 'sequracheckout';
            $this->version = self::$VERSION;
            $this->commonConstructor();
            $this->description = $this->l('SeQura Checkout');
            $this->displayName = $this->l('SeQura');
        }

        private function getPaymentMethods()
        {
            $name = $this->name . '_order';
            $identification = new SequraIdentification($this);
            if ($identification->sequraIsReady()) {
                $client = $this->getSequraCore()->getClient();
                $uri = $this->context->cookie->$name;
                $client->getPaymentMethods($uri);
                if ($client->succeeded()) {
                    $json = $client->getJson();
                    return array_reduce(
                        $json['payment_options'],
                        function ($methods, $family) {
                            return array_merge($methods, $family['methods']);
                        },
                        []
                    );
                }
            }
        }

        public function setVariables($cart, $payment_method = null)
        {
            $this->commonSetVariables($cart);
            if ($payment_method){
                $vars = array(
                    'payment_method' => $payment_method,
                );
                $this->context->smarty->assign($vars);
                $this->logo_url = $payment_method['icon'];
            }
        }

        //HOOKS
        //PS 1.6
        public function hookPayment($params)
        {
            if (!$this->active || !SequraPreQualifier::availableForIP()) {
                return;
            }
            $this->commonSetVariables($params['cart']);
            $payment_methods = $this->getPaymentMethods();
            array_walk(
                $payment_methods,
                function (&$method) {
                    $method['icon'] = $this->getLogoUlr($method['icon']);
                }
            );
            $vars = array(
                'payment_methods' => $payment_methods,
            );
            $this->context->smarty->assign($vars);
            return $this->renderPaymentForm($params);
        }

        //PS 1.7
        public function hookPaymentOptions($params)
        {
            //In this case checking IP is enogh everith else should be payment_methods API responsability.
            if (!$this->active || !SequraPreQualifier::availableForIP()) {
                return;
            }
            $payment_methods = $this->getPaymentMethods();
            if(!$payment_methods || count($payment_methods)<1){
                return;
            }
            foreach ($payment_methods as $payment_method) {
                $payment_options[] = $this->addPaymentOption($params['cart'], $payment_method);
            }
            return $payment_options;
        }

        public function hookDisplayFooter($params)
        {
            if (self::$footer_added) {
                return;
            }
            if (Tools::getValue('RESET_SEQURA_ACTIVE_METHODS')=='true') {
                $countries = $this->getSequraCore()->getCountries();
                array_walk(
                    $countries,
                    ['SequracheckoutConfigData','updateActivePaymentMethods']
                );
            }
            if( 
                !in_array(
                    strtoupper(substr($this->context->language->iso_code,-2)),
                    $this->getSequraCore()->getCountries()
                )
            ) {
                return;
            }
            $this->context->smarty->assign(
                array(
                    'css_selector_price' => Configuration::get('SEQURA_CSS_SEL_PRICE'),

                    'sq_pp_product' => Configuration::get('SEQURA_PARTPAYMENT_PRODUCT'),

                    'sq_categories_show'  => Configuration::get('SEQURA_PARTPAYMENT_CATEGORIES_SHOW'),
                    'sq_categories_css_sel_price' => Configuration::get('SEQURA_PARTPAYMENT_CATEGORIES_CSS_SEL_PRICE'),
                    'sq_categories_css_sel' => Configuration::get('SEQURA_PARTPAYMENT_CATEGORIES_CSS_SEL'),
                    'sq_categories_msg' => Configuration::get('SEQURA_PARTPAYMENT_CATEGORIES_TEASER_MSG'),

                    'sq_cart_show' => Configuration::get('SEQURA_PARTPAYMENT_CART_SHOW'),
                    'sq_cart_css_sel_price' => Configuration::get('SEQURA_PARTPAYMENT_CART_CSS_SEL_PRICE'),
                    'sq_cart_css_sel' => Configuration::get('SEQURA_PARTPAYMENT_CART_CSS_SEL'),
                    'sq_cart_msg' => Configuration::get('SEQURA_PARTPAYMENT_CART_TEASER_MSG'),

                    'sq_minicart_show' => Configuration::get('SEQURA_PARTPAYMENT_MINICART_SHOW'),
                    'sq_minicart_css_sel_price' => Configuration::get('SEQURA_PARTPAYMENT_MINICART_CSS_SEL_PRICE'),
                    'sq_minicart_css_sel' => Configuration::get('SEQURA_PARTPAYMENT_MINICART_CSS_SEL'),
                    'sq_mincart_msg' => Configuration::get('SEQURA_PARTPAYMENT_MINICART_TEASER_MSG'),

                    'sq_msg_below' => "Fracciona a partir de %s",
                    'widgets' => [],
                )
            );
            if($this->context->controller instanceof ProductController){
                $this->context->smarty->assign(
                    'widgets',
                    $this->getWidgetsForProductPage(
                        $this->context->controller->getProduct()->id
                    )
                );
            }
            $tpl = 'footer.tpl';
            self::$footer_added = true;
            return $this->display(__FILE__, 'views/' . $tpl);
        }

        // For PS version 1.4
        public function hookFooter($params)
        {
            return $this->hookDisplayFooter($params);
        }

        //for PS version 1.6
        public function hookProductFooter($params)
        {
            if (_PS_VERSION_ < 1.7) {
                return $this->hookDisplayFooter($params);
            }
            return false;
        }

        //for PS version 1.7
        public function hookDisplayFooterProduct($params)
        {
            return $this->hookDisplayFooter($params);
        }

        private function getWidgetsForProductPage($id_product)
        {
            $ret = array();
            if ($this->isProductPage() && SequracheckoutPreQualifier::canDisplayWidgetInProductPage($id_product)) {
                $price = $this->getSequraCore()->getProduct()->getPrice();
                include_once _PS_MODULE_DIR_ . '/' . $this->name . '/lib/' . get_class($this) . 'PreQualifier.php';
                $ret = array_map(
                    function ($method) {
                        $product = SequracheckoutConfigData::buildUniqueProductCode($method);
                        return array(
                            'css_sel'  => Configuration::get('SEQURA_'.$product.'_CSS_SEL'),
                            'product'  => $method['product'],
                            'theme'    => Configuration::get('SEQURA_'.$product.'_WIDGET_THEME'),
                            'campaign' => isset($method['campaign'])?$method['campaign']:'',
                        );
                    },
                    array_filter(
                        SequracheckoutConfigData::getMerchantPaymentMethods(false, strtoupper(substr(Context::getContext()->language->iso_code,-2))),
                        function ($method) use ($price) {
                            return
                                SequracheckoutConfigData::getFamilyFor($method) != 'CARD' &&
                                SequracheckoutPreQualifier::isDateInRange($method) &&
                                SequracheckoutPreQualifier::isPriceWithinMethodRange(
                                    $method,
                                    $price,
                                    SequracheckoutConfigData::getFamilyFor($method) != 'PARTPAYMENT'
                                );
                        }
                    )
                );
            }
            return array_reverse($ret);
        }
        private function getPaymentOptionAction($payment_method){
            $linker = $this->context->link;
            $params = [
                'product'=> $payment_method['product']
            ];
            if (isset($payment_method['campaign'])) {
                $params ['campaign'] = $payment_method['campaign'];
            }
            if(Configuration::get('SEQURA_FORCE_NEW_PAGE')==1){
                return $linker->getModuleLink(
                    $this->name,
                    'identification',
                    $params,
                    true
                );
            }
            $ajax_form_url = $linker->getModuleLink(
                $this->name,
                'getidentificationform',
                $params,
                true
            );
            return "javascript:SequraIdentificationPopupLoader.url = '$ajax_form_url';" .
                    "SequraIdentificationPopupLoader.product = '" . $payment_method['product'] . "';" .
                    "SequraIdentificationPopupLoader.campaign = '" . $payment_method['campaign'] . "';" .
                    "SequraIdentificationPopupLoader.closeCallback = SequraIdentificationPopupLoader.closeCallback = function() {window.location.reload();};" .
                    "SequraIdentificationPopupLoader.showForm();";

        }

        private function addPaymentOption($cart, $payment_method)
        {
            $this->setVariables($cart, $payment_method);

            $sequraConfig = new SequraConfig($this);
            $sequraOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
            $sequraOption
                ->setModuleName($this->name)
                ->setCallToActionText($payment_method['title'] . (isset($payment_method['cost_description'])?' ' . $payment_method['cost_description']:'') )
                ->setAction($this->getPaymentOptionAction($payment_method))
                ->setAdditionalInformation(
                    $this->context->smarty->fetch(
                        $sequraConfig->getPaymentFormTplPath()
                    )
                )->setLogo($this->getLogoUlr($payment_method['icon']));
            return $sequraOption;
        }

        public function enable($force_all = false)
        {
            parent::enable($force_all);
            $countries = $this->getSequraCore()->getCountries();
            array_walk(
                $countries,
                ['SequracheckoutConfigData','updateActivePaymentMethods']
            );
            return true;
        }
    
        public function disable($force_all = false)
        {
            parent::disable($force_all);
            $countries = $this->getSequraCore()->getCountries();
            array_walk(
                $countries,
                function ($country) {
                    Configuration::updateValue('SEQURA_ACTIVE_METHOD_' . $country, serialize([]));
                }
            );
            return true;
        }

        //Configuraion page
        public function getContent()
        {
            include_once _PS_MODULE_DIR_ . '/sequracheckout/lib/SequracheckoutConfig.php';
            return (new SequracheckoutConfig($this))->getContent();
        }

        public function getProduct()
        {
            return Tools::getValue('product','');
        }

        public function getCampaign()
        {
            return Tools::getValue('campaign','');
        }

        public function getDisplayName()
        {
            if (Tools::getValue('product_code', false)) {
                return SequracheckoutConfigData::getTitleFromUniqueProductCode(Tools::getValue('product_code'));
            }
            return $this->displayName;
        }

        //Build logo URL
        private function getLogoUlr($icon) {
            if (substr($icon, 0, 4) === "http") {
                return $icon;
            } else {
                return 'data:image/svg+xml;base64,'.base64_encode($icon);
            }
        }
    }
}

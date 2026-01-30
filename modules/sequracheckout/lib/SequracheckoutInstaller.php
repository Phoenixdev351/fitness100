<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class SequracheckoutInstaller extends SequraInstaller
{
    protected $_hook_list = array(
        'payment',
        'footer',
        'displayFooter',
        'displayFooterProduct',//< 1.7
        'displayBeforeBodyClosingTag',//>=1.7
        'displayPayment',
        'paymentReturn',
        'paymentOptions',
        'displayHome'
    );

    public function install()
    {
        $this->registerHooks();
        $this->putFirstAmongPaymentMethods();
        $this->setAllowedCurrencies();
        if (Module::isInstalled('onepagecheckoutps')) {
            $this->onepagecheckoutpsSetUp();
        }
        self::initConfigurationValue('SEQURA_CHECKOUT_SERVICE_NAME', 'Pago con SeQura');
        self::initConfigurationValue('SEQURA_FORCE_NEW_PAGE', '0');
        self::initConfigurationValue('SEQURA_PARTPAYMENT_CATEGORIES_TEASER_MSG', 'Desde %s/mes');
        self::initConfigurationValue('SEQURA_PARTPAYMENT_CART_TEASER_MSG', 'Desde %s/mes');
        self::initConfigurationValue('SEQURA_PARTPAYMENT_MINICART_TEASER_MSG', 'Desde %s/mes');
        if (_PS_VERSION_ < 1.5) { //For 1.4
            self::initConfigurationValue('SEQURA_pp3_CSS_SEL', 'p.price');
            self::initConfigurationValue('SEQURA_pp6_CSS_SEL', 'p.price');
            self::initConfigurationValue('SEQURA_pp9_CSS_SEL', 'p.price');
            self::initConfigurationValue('SEQURA_sp1_permanente_CSS_SEL', 'p.price');
            self::initConfigurationValue('SEQURA_CSS_SEL_PRICE', '#our_price_display');
            self::initConfigurationValue('SEQURA_INVOICE_CSS_SEL', '#add_to_cart');
            self::initConfigurationValue('SEQURA_CAMPAIGN_CSS_SEL', '#add_to_cart');
        } else {
            if (_PS_VERSION_ < 1.6) { //For 1.5
                self::initConfigurationValue('SEQURA_pp3_CSS_SEL', '.content_prices');
                self::initConfigurationValue('SEQURA_pp6_CSS_SEL', '.content_prices');
                self::initConfigurationValue('SEQURA_pp9_CSS_SEL', '.content_prices');
                self::initConfigurationValue('SEQURA_sp1_permanente_CSS_SEL', '.content_prices');
                self::initConfigurationValue('SEQURA_INVOICE_CSS_SEL', '#add_to_cart');
                self::initConfigurationValue('SEQURA_CAMPAIGN_CSS_SEL', '#add_to_cart');
                self::initConfigurationValue('SEQURA_CSS_SEL_PRICE', '#our_price_display');
            } else {
                if (_PS_VERSION_ < 1.7) {  //For 1.6
                    self::initConfigurationValue('SEQURA_pp3_CSS_SEL', '.content_prices');
                    self::initConfigurationValue('SEQURA_pp6_CSS_SEL', '.content_prices');
                    self::initConfigurationValue('SEQURA_pp9_CSS_SEL', '.content_prices');
                    self::initConfigurationValue('SEQURA_sp1_permanente_CSS_SEL', '.content_prices');
                    self::initConfigurationValue('SEQURA_CSS_SEL_PRICE', '#our_price_display');
                    self::initConfigurationValue('SEQURA_INVOICE_CSS_SEL', '#add_to_cart');
                    self::initConfigurationValue('SEQURA_CAMPAIGN_CSS_SEL', '#add_to_cart');
                    self::initConfigurationValue('SEQURA_PARTPAYMENT_CATEGORIES_CSS_SEL', '.content_price');
                    self::initConfigurationValue('SEQURA_PARTPAYMENT_CATEGORIES_CSS_SEL_PRICE', '.product-container .content_price');
                    self::initConfigurationValue('SEQURA_PARTPAYMENT_CART_CSS_SEL', '#total_price');
                    self::initConfigurationValue('SEQURA_PARTPAYMENT_CART_CSS_SEL_PRICE', '#total_price');
                } else { //For 1.7
                    self::initConfigurationValue('SEQURA_pp3_CSS_SEL', '.product-prices');
                    self::initConfigurationValue('SEQURA_pp6_CSS_SEL', '.product-prices');
                    self::initConfigurationValue('SEQURA_pp9_CSS_SEL', '.product-prices');
                    self::initConfigurationValue('SEQURA_sp1_permanente_CSS_SEL', '.product-prices');
                    self::initConfigurationValue('SEQURA_CSS_SEL_PRICE', '.product-prices  .current-price');
                    self::initConfigurationValue('SEQURA_INVOICE_CSS_SEL', '.product-add-to-cart');
                    self::initConfigurationValue('SEQURA_CAMPAIGN_CSS_SEL', '.product-add-to-cart');
                    self::initConfigurationValue('SEQURA_PARTPAYMENT_CATEGORIES_CSS_SEL', 'article.product-miniature div.product-price-and-shipping');
                    self::initConfigurationValue('SEQURA_PARTPAYMENT_CATEGORIES_CSS_SEL_PRICE', 'article.product-miniature [itemprop=price]');
                    self::initConfigurationValue('SEQURA_PARTPAYMENT_CART_CSS_SEL', 'div.cart-summary-line.cart-total span.value');
                    self::initConfigurationValue('SEQURA_PARTPAYMENT_CART_CSS_SEL_PRICE', 'div.cart-summary-line.cart-total');
                }
            }
        }
        $countries = self::getSequraCore()->getCountries();
        array_walk(
            $countries,
            ['SequracheckoutConfigData','updateActivePaymentMethods']
        );
        return true;
    }

    public function uninstall()
    {
        $this->unregisterHooks();

        return true;
    }

    protected static function getSequraCore()
    {
        return Module::getInstanceByName(SEQURA_CORE);
    }
}

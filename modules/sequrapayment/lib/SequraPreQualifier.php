<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

abstract class SequraPreQualifier
{
    protected static $MODULE_NAME = 'sequrapayment';
    protected static $SERVICE_COMPATIBLE = true;

    public function __construct($cart)
    {
        $this->cart = $cart;
        $this->module = Module::getInstanceByName(static::$MODULE_NAME);
    }

    public function passes()
    {
        return
            static::available($this->module) &&
            $this->isCartElegible() &&
            static::priceWithinRange() &&
            static::availableForIP();
    }

    public static function canShowBanner($key)
    {
        $show_banner = ConfigurationCore::get($key, null, null, null, 0);
        return $show_banner && self::canDisplayInfo();
    }

    public static function canDisplayInfo($price = null)
    {
        $module = Module::getInstanceByName(static::$MODULE_NAME);
        $currency = $module->getContext()->currency->iso_code;
        $language = $module->getContext()->language->iso_code;

        return static::available($module) &&
            (static::isPriceWithinRange($price, false) || is_null($price)) &&
            static::allowedCurrency($currency) &&
            static::allowedLanguage($language) &&
            static::availableForIP();
    }

    public static function available($module)
    {

        if ($module && Module::isInstalled(static::$MODULE_NAME)) {
            $available = true;
            if (Configuration::get('SEQURA_FOR_SERVICES')) {
                $available = static::$SERVICE_COMPATIBLE;
            }
            if (method_exists('Module', 'isEnabled')) {
                if (Module::isEnabled(static::$MODULE_NAME)) {
                    return $available;
                }
            } else {
                if ($module->active) {
                    return $available;
                }
            }
        }

        return false;
    }

    public static function availableForIP()
    {
        $allowed_ips = preg_split('/[\s*,]/', Configuration::get('SEQURA_ALLOW_IP'), null, PREG_SPLIT_NO_EMPTY);

        return empty($allowed_ips) || in_array($_SERVER['REMOTE_ADDR'], $allowed_ips) || isset($_COOKIE['SEQURA_INTEGRATOR']);
    }

    public function priceWithinRange()
    {
        $price = $this->cart->getOrderTotal();

        return static::isPriceWithinRange($price);
    }

    public function isCartElegible()
    {
        $banned_products = array_filter(
            $this->cart->getProducts(),
            function ($cart_item) {
                $sq_product_extra = new SequraProductExtra($cart_item['id_product']);
                return $sq_product_extra->getProductIsBanned();
            }
        );

        return count($banned_products) == 0;
    }

    //abstract public static function isPriceWithinRange($price);
    /*
     * Should be abstract, left for compatibility with OPC
     * */
    public static function isPriceWithinRange($price, $check_min = true)
    {
        $max = Configuration::get('SEQURA_INVOICE_MAX');
        $too_much = is_numeric($max) && $max > 0 && $price > $max;
        $too_low = $price <= 0 && $check_min;

        return !$too_much && !$too_low;
    }

    public static $allowed_languages = array('es','eu','ca','ga');
    public static $allowed_currencies = array('EUR');

    public function allowedCountry()
    {
        $address = new Address((int)$this->cart->id_address_delivery);
        $country = new Country((int)$address->id_country);

        return is_null($country->iso_code) || in_array($country->iso_code, $this->getSequraCore()->getCountries());
    }

    protected function getSequraCore()
    {
        return Module::getInstanceByName(SEQURA_CORE);
    }

    public static function allowedCurrency($currency)
    {
        return is_null($currency) || in_array($currency, self::$allowed_currencies);
    }

    public static function allowedLanguage($language)
    {
        return is_null($language) || in_array($language, self::$allowed_languages);
    }
}

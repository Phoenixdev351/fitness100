<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class SequracheckoutConfigData
{
    private static $RawMerchantPaymentMethods = [];
    private static $MerchantPaymentMethods = [];
    private static $productFamilyKeys = array(
        'pp10' => 'CARD',       //Paga Ahora
        'fp1'  => 'CARD',
        'i1'   => 'INVOICE',     //Paga después
        'pp5'  => 'INVOICE',
        'pp3'  => 'PARTPAYMENT', //Paga fraccionado
        'pp6'  => 'PARTPAYMENT',
        'pp9'  => 'PARTPAYMENT',
        'sp1'  => 'PARTPAYMENT',
    );

    public static function buildUniqueProductCode($method)
    {
        return $method['product'] . (isset($method['campaign'])?'_'.$method['campaign']:'');
    }

    /**
     * Get method title from unique product code
     *
     * @param string $product_campaign unique product code.
     * @return string
     */
    public static function getTitleFromUniqueProductCode($product_campaign)
    {
        $product = $product_campaign;
        $campaign = '';
        if (count(explode('_', $product_campaign))>1) {
            list($product,$campaign) = explode('_', $product_campaign);
        }
        return self::getTitleFromProductCampaign($product, $campaign);
    }

    /**
     *  Get method title from unique product and campaign
     *
     * @param string $product product.
     * @param string $campaign campaign.
     * @return string
     */
    public static function getTitleFromProductCampaign($product, $campaign = null)
    {
        $country_code = strtoupper(substr(Context::getContext()->language->iso_code, -2));
        foreach (self::getMerchantPaymentMethods(false, $country_code) as $method){
            if ($method['product'] == $product &&
                (!$campaign || !isset($method['campaign']) || $method['campaign'] == $campaign)
            ) {
                return $method['title'];
            }
        }
        return 'SeQura';
    }

    public static function getFamilyFor($method) {
        return self::$productFamilyKeys[$method['product']];
    }

    public static function updateActivePaymentMethods($country_code =  'ES'){
        self::getMerchantPaymentMethods(true, $country_code); //Download methods again.
        $sq_products = self::getMerchantActivePaymentProducts($country_code);
        Configuration::updateValue(
            'SEQURA_ACTIVE_METHODS_'.$country_code,
            serialize($sq_products)
        );
        if (in_array('i1', $sq_products)){
            Configuration::updateValue('SEQURA_INVOICE_PRODUCT','i1');
        }
        if (in_array('pp5', $sq_products)){
            Configuration::updateValue('SEQURA_CAMPAIGN_PRODUCT','pp5');
        }
        if (in_array('pp3', $sq_products)) {
            Configuration::updateValue('SEQURA_PARTPAYMENT_PRODUCT','pp3');
        } elseif (in_array('pp6', $sq_products)) {
            Configuration::updateValue('SEQURA_PARTPAYMENT_PRODUCT','pp6');
        } elseif (in_array('pp9', $sq_products)) {
            Configuration::updateValue('SEQURA_PARTPAYMENT_PRODUCT','pp9');
        }
    }

    public static function getMerchantActivePaymentProducts($country_code)
    {
        return array_map(
            function ($method) {
                return $method['product'];
            },
            self::getMerchantPaymentMethods(false, $country_code)
        );
    }

    public static function getMerchantPaymentMethods($force_refresh = false, $country_code = 'ES')
    {
        if ($force_refresh || !self::getStoredPaymentMethods($country_code)) {
            $sequra_class = ucfirst(SEQURA_CORE);
            $client = (new $sequra_class())->getClient();
            $client->getMerchantPaymentMethods(Configuration::get('SEQURA_MERCHANT_ID_'.$country_code));
            if ($client->succeeded()) {
                self::$RawMerchantPaymentMethods[$country_code] = $client->getRawResult();
                self::updateStoredPaymentMethods($country_code);
                $json = $client->getJson();
                self::$MerchantPaymentMethods[$country_code] = $json['payment_options'];
            }
        }
        if (!isset(self::$MerchantPaymentMethods[$country_code]) || !self::$MerchantPaymentMethods[$country_code]) {
            $json = self::getStoredPaymentMethods($country_code);
            self::$MerchantPaymentMethods[$country_code] = $json['payment_options'];
        }
        return self::flattenPaymentOptions(
            self::$MerchantPaymentMethods[$country_code]
        );
    }

    /**
     * Create a flat array with all methods in all options.
     *
     * @param array $options Payment options to faltten.
     * @return array
     */
    private static function flattenPaymentOptions($options) {
        return $options ?
            array_reduce(
                $options,
                function ($methods, $family) {
                    return array_merge(
                        $methods,
                        $family['methods']
                    );
                },
                []
            )
            :[];
    }

    private static function getStoredPaymentMethods($country_code) {
        if (!isset(self::$RawMerchantPaymentMethods[$country_code]) || !self::$RawMerchantPaymentMethods[$country_code]) {
            self::$RawMerchantPaymentMethods[$country_code] = Configuration::get('SEQURA_PAYMENT_METHODS_'.$country_code);
            if (mb_strlen(self::$RawMerchantPaymentMethods[$country_code], '8bit') < 4096 && file_exists(self::$RawMerchantPaymentMethods[$country_code])) {
                self::$RawMerchantPaymentMethods[$country_code] = file_get_contents(self::$RawMerchantPaymentMethods[$country_code]);
            }
        }
        return json_decode(self::$RawMerchantPaymentMethods[$country_code], true);
    }

    private static function updateStoredPaymentMethods($country_code) {
        if ( mb_strlen( self::$RawMerchantPaymentMethods[$country_code], '8bit') > 64000 ) {
            $tmp_file = tempnam(sys_get_temp_dir(), 'sq_pms_'.$country_code);
            file_put_contents($tmp_file, self::$RawMerchantPaymentMethods);
            self::$RawMerchantPaymentMethods[$country_code] = $tmp_file;
        }
        Configuration::updateValue(
            'SEQURA_PAYMENT_METHODS_'.$country_code,
            self::$RawMerchantPaymentMethods[$country_code]
        );
    }
}

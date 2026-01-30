<?php
/**
 * Facebook Conversion Pixel Tracking Plus
 *
 * NOTICE OF LICENSE
 *
 * @author    Pol Rué
 * @copyright Smart Modules 2014
 * @license   One time purchase Licence (You can modify or resell the product but just one time per licence)
 * @version 2.3.3
 * @category Marketing & Advertising
 * Registered Trademark & Property of smart-modules.com
 *
 * ***************************************************
 * *     Facebook Conversion Trackcing Pixel Plus    *
 * *          http://www.smart-modules.com           *
 * *                     V 2.3.3                     *
 * ***************************************************
 */

class ConversionApi extends ObjectModel
{
    const DEFAULT_ROUND = 10;
    public $access_token;
    public $user_email;
    public $id_guest;
    public $id_customer;
    public $ip;
    public $user_agent;
    public $country_iso_code;
    private $pixels_ids = array();
    private $_fbp = false;
    private $_fbc = false;
    private $context;
    public $url;
    public function __construct()
    {
        $this->context = Context::getContext();
        $this->access_token = Configuration::get('FCTP_CONVERSION_API_ACCESS_TOKEN');
        $this->advanced_matching = Configuration::get('FCTP_ADVANCE_MATCHING_OPTIONS');
        $this->test_event_code = Configuration::get('FCTP_CONVERSION_API_TEST');
        $pixels_ids = explode(",", preg_replace('/[^,0-9]/', '', Configuration::get('FCTP_PIXEL_ID')));
        $pix_count = count($pixels_ids);
        $this->logIps = Configuration::get('FCTP_CONVERSION_IP_LOG');
        $this->logEnabled = Configuration::get('FCTP_CONVERSION_LOG');
        $this->logPayload = Configuration::get('FCTP_CONVERSION_PAYLOAD');
        $this->testCodeEnabled = Configuration::get('FCTP_ENABLE_TEST_EVENTS');
        $this->ip = Tools::getRemoteAddr();
        if (filter_var($this->ip, FILTER_VALIDATE_IP) === false) {
            PrestaShopLogger::addLog('[Conversions API] Detected an invalid IP [' . $this->ip . '] <br>Conversions API will not send events for this user to prevent issues. <br>No action from the user is required', 2, 403, 'FB-Conversion');
            return false;
        } elseif ($pix_count == 0) {
            PrestaShopLogger::addLog('[Conversions API] No Pixel ID Configured. Set up the Pixel ID to be able to send the Events through the API', 2, 403, 'FB-Conversion');
            return false;
        }
        $allowed_ips = explode(",", $this->logIps);
        if ($this->logEnabled) {
            if ($this->logIps == '') {
                $this->logEnabled = true;
            } elseif (in_array($this->ip, $allowed_ips)) {
                $this->logEnabled = true;
            } else {
                $this->logEnabled = false;
            }
        }
        $this->access_token = false;
        $this->pp = Configuration::get('PS_PRICE_DISPLAY_PRECISION') == false ? 2 : Configuration::get('PS_PRICE_DISPLAY_PRECISION');
        $this->usetax = !Group::getPriceDisplayMethod(Group::getCurrent()->id);

        for ($i=0; $i < $pix_count; $i++) {
            $token = Configuration::get('FCTP_CONVERSION_API_ACCESS_TOKEN_'.($i+1));
            if (!empty($token)) {
                $this->pixels_ids[$i+1] = array('id' => $pixels_ids[$i], 'token' => $token);
            }
        }
        unset($pixels_ids, $pix_count);
        $this->user_agent = $_SERVER['HTTP_USER_AGENT'];
        if (count($this->pixels_ids) > 0) {
            $this->id_customer = 0;
            if (isset($this->context->customer) && $this->context->customer !== null) {
                if (Validate::isEmail($this->context->cookie->email)) {
                    $this->email = $this->context->cookie->email;
                }
                $this->id_customer = $this->context->cookie->id_customer;
            }
            $this->id_guest = $this->context->cookie->id_guest;
            $currency = new CurrencyCore($this->context->cookie->id_currency);
            $this->currency_iso_code = $currency->iso_code;
            $this->product_prefix = Configuration::get('FPF_PREFIX_' . $this->context->shop->id);
            $this->combination_prefix = Configuration::getGlobalValue('FCTP_COMBI_PREFIX_' . $this->context->shop->id);
            $this->combination = Configuration::getGlobalValue('FCTP_COMBI_' . $this->context->shop->id);
            if (Tools::getIsset('id_product')) {
                $p = new Product((int)Tools::getValue('id_product'));
                $l = $this->context->language->id;
                $ipa = Tools::getIsset('id_product_attribute') ? Tools::getValue('id_product_attribute') : 0;
                $this->url = $this->context->link->getProductLink($p, $p->link_rewrite[$l], Category::getLinkRewrite($p->id_category_default, $l), null, null, $this->context->shop->id, $ipa, false, false, true);
            } else {
                $this->url = Tools::getCurrentUrlProtocolPrefix() . $_SERVER['HTTP_HOST'] . '/' . ltrim($_SERVER['REQUEST_URI'], '/');
                if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != '' && Tools::strpos($this->url, $_SERVER['QUERY_STRING']) === false) {
                    $this->url .= $_SERVER['QUERY_STRING'];
                    $this->cleanUrlSensitiveParameters();
                }
            }
            $this->getFacebookCookies();
        } else {
            PrestaShopLogger::addLog('[Conversions API] Token not configured, events won\'t be sent through the CAPI', 2, 403, 'FB-Conversion');
            return false;
        }
    }
    public function getUserData()
    {
        $user_data = array(
            'external_id' => $this->getHashData($this->id_guest, 'g'),
            'client_ip_address' => $this->ip,
            'client_user_agent' => $this->user_agent,
        );
        if (isset($this->_fbc) && $this->_fbc != '') {
            $user_data['fbc'] = $this->_fbc;
        } elseif ($this->context->cookie->__isset('fctp_fbc')) {
            $user_data['fbc'] = $this->context->cookie->__get('fctp_fbc');
        }
        if (isset($this->_fbp) && $this->_fbp != '') {
            $user_data['fbp'] = $this->_fbp;
        }
        if ($this->advanced_matching) {
            if (!empty($this->id_customer)) {
                $customer = new Customer((int)$this->id_customer);
                if (!empty($customer->id_gender)) {
                    $gender = (int)$customer->id_gender == 2 ? 'f' : 'm';
                    $user_data['ge'] = $this->getHashData($gender);
                }
                if (Validate::isEmail($customer->email)) {
                    $user_data['em'] = $this->getHashData($customer->email);
                }
                $user_data['external_id'] = $this->getHashData($customer->id, 'c');
                if (Validate::isGenericName($customer->firstname)) {
                    $user_data['fn'] = $this->getHashData($customer->firstname);
                    if (Validate::isGenericName($customer->lastname)) {
                        $user_data['ln'] = $this->getHashData($customer->lastname);
                    }
                }
                if (isset(Context::getContext()->cart->id_address_delivery) && Context::getContext()->cart->id_address_delivery > 0) {
                    $address = new Address((int)Context::getContext()->cart->id_address_delivery);
                    if (!empty($address->phone) && Validate::isPhoneNumber($address->phone)) {
                        $user_data['ph'] = $this->getHashData($address->phone);
                    }
                    if (!empty($user_data['country']) && Validate::isCountryName($user_data['country'])) {
                        $user_data['country'] = $this->getHashData(Country::getIsoById($address->id_country));
                    }
                    if (!empty($user_data['st']) && Validate::isCountryName($user_data['st'])) {
                        $user_data['st'] = $this->getHashData(State::getNameById($address->id_state));
                    }
                    if (!empty($user_data['zp']) && Validate::isZipCodeFormat($user_data['zp'])) {
                        $user_data['zp'] = $this->getHashData($address->postcode);
                    }
                }
            }
        }
        return $user_data;
    }
    private function getFacebookCookies()
    {
        /* External cookies, can\'t be recovered with Cookie class */
        if (Tools::getIsset('fbclid') && !$this->context->cookie->__isset('fctp_fbc') ||
            Tools::getValue('fbclid') != $this->context->cookie->__get('fctp_fbc')) {
            // Generate custom fbc cookie
            $fbclid = Tools::getValue('fbclid');
            $this->context->cookie->__set('fctp_fbclid', $fbclid);
            $this->context->cookie->__set('fctp_fbc', 'fb.1.'.time().'.'.$fbclid);
        }
        $fb_cookies = array('_fbp', '_fbc');
        foreach ($fb_cookies as $cookie) {
            if (isset($_COOKIE[$cookie]) && $this->validateFbCookie($_COOKIE[$cookie], $cookie)) {
                $this->$cookie = $_COOKIE[$cookie];
            }
        }
    }
    /*
     * Internal Validation for Facebook cookies
     * @param $data the cookie value
     * @param $cookie the cookie name
     * @return Whenever if it's a valid Facebook cookie
     */
    private function validateFbCookie($data, $cookie)
    {
        $data = explode('.', $data);
        if ($data[0] != 'fb' ||
            ($data[1] > 2 || $data[1] < 0) ||
            !$this->isValidTimeStamp($data[2]) ||
            ($cookie == '_fbp' && !Validate::isInt($data[3])) ||
            ($cookie == '_fbc' && $data[3] != $this->context->cookie->__get('fctp_fbclid'))) {
            return false;
        }
        return true;
    }
    private function isValidTimeStamp($timestamp)
    {
        return ((string) (int) $timestamp === $timestamp)
            && ($timestamp <= PHP_INT_MAX)
            && ($timestamp >= ~PHP_INT_MAX);
    }
    public function viewContentTrigger($fb_pixel_event_id, $id_lang, $type = null, $id = null, $id_product_attribute = null)
    {
        $params = array();
        $event_name = 'ViewContent';
        $fbObj = new FacebookConversionTrackingPlus();
        $feed_id = $fbObj->getFeedId();
        if ($type == 'product' && $id > 0) {
            $url = Context::getContext()->link->getProductLink($id);
            $p = new Product($id);
            if ($p->id > 0) {
                $ipa = Tools::getIsset('id_product_attribute') ? Tools::getValue('id_product_attribute') : null;
                $entityprice = Product::getPriceStatic((int) $p->id, $this->usetax, $ipa, $this->pp, null, false, true, 1, false, null, null, null);
                $category = $fbObj->tryGetBreadcrumb($id);
                $content_ids = $this->product_prefix . $p->id;
                $combination = '';
                if ((int)$id_product_attribute > 0 && $this->combination) {
                    $combination = $this->combination_prefix . $id_product_attribute;
                }
                $content_ids = $content_ids . $combination;
                $params = array(
                    'content_name' => $p->name[$id_lang],
                    'content_category' => $category,
                    'value' => $entityprice,
                    'currency' => $this->currency_iso_code,
                    'content_ids' => array($content_ids),
                    'content_type' => 'product'
                );
            }
        }
        if ($type == 'category') {
            $event_name = 'ViewCategory';
            $url = Context::getContext()->link->getCategoryLink((int)$id);
            $entityname = new Category((int)$id);
            $entityname = $entityname->name[$id_lang];
            $content_ids = $fbObj->getCategoryTopSales((int)$id);
            $params = array(
                'content_name' => $entityname,
                'value' => Configuration::get('FCTP_CATEGORY_VALUE'),
                'currency' => $this->currency_iso_code,
                'content_ids' => $content_ids,
                'content_type' => 'product',
                'content_category' => $entityname,
            );
        }
        $data = array(
            'data' => array(
                array(
                    'event_name' => $event_name,
                    'event_time' => time(),
                    'event_id' => $fb_pixel_event_id,
                    'event_source_url' => $url,
                    'user_data' => $this->getUserData(),
                    'custom_data' => $params
                )
            ),
        );
        $feed_id = $fbObj->getFeedId();
        if ($feed_id > 0) {
            $data['data'][0]['custom_data']['product_catalog_id'] = $feed_id;
        }
        $this->sendEventToFacebook($data);
    }
    public function addToCartTrigger()
    {
        $fbObj = new FacebookConversionTrackingPlus();
        $this->context = Context::getContext();
        $cart = $this->context->cart;
        if (Tools::getIsset('delete') && (Tools::getValue('delete') == 1 || Tools::getValue('delete') == true || Tools::getValue('delete') == 'true')) {
            return;
        }
        if (!empty($cart) && Tools::getValue('fb_pixel_event_id') != '') {
            $cart_products = $cart->getProducts();
            if (!empty($cart_products)) {
                $content_products = array();
                $content_ids = array();
                $content_categories = array();
                $value = 0;
                $id_product_attribute = null;
                if (Tools::getIsset('id_product_attribute')) {
                    //incase of 1.7
                    $id_product_attribute = Tools::getValue('id_product_attribute');
                } elseif (Tools::getIsset('ipa')) {
                    //incase of 1.6
                    $id_product_attribute = Tools::getValue('ipa');
                }

                foreach ($cart_products as $val) {
                    if ($id_product_attribute > 0 && $id_product_attribute != $val['id_product_attribute']) {
                        continue;
                    }

                    if (Tools::getValue('id_product') == (int)$val['id_product']) {
                        $content_id = $this->product_prefix . (int)$val['id_product'];
                        $combination = '';
                        if ((int)$id_product_attribute > 0 && $this->combination) {
                            $combination = $this->combination_prefix . $id_product_attribute;
                        }
                        $content_id = $content_id . $combination;
                        $content_ids[] = $content_id;

                        $content_categories = $fbObj->tryGetBreadcrumb((int)$val['id_product']);
                        $content_name = $val['name'];

                        if (method_exists('Context', 'getComputingPrecision')) {
                            $price = Tools::ps_round($val['price_wt'], (int)$this->context->currency->decimals * Context::getContext()->getComputingPrecision());
                        } else {
                            $price = Product::getPriceStatic((int)$val['id_product'], $this->usetax, $id_product_attribute, $this->pp, null, false, true, 1, false, Context::getContext()->cookie->id_customer);
                        }

                        $data = array(
                            'id' => $content_id,
                            'quantity' => (int)$val['cart_quantity'],
                            'category' => pSQL($val['category']),
                            'price' => $price
                        );
                        $content_products[] = $data;
                    }
                }

                $data = array(
                    'data' => array(
                        array(
                            'event_name' => 'AddToCart',
                            'event_time' => time(),
                            'event_id' => Tools::getValue('fb_pixel_event_id'),
                            'event_source_url' => $this->url,
                            'user_data' => $this->getUserData(),
                            'custom_data' => array(
                                'contents' => $content_products,
                                'content_ids' => $content_ids,
                                'currency' => $this->currency_iso_code,
                                'content_type' => 'product',
                                'content_category' => $content_categories,
                                'value' => $value,
                                'content_name' => $content_name,
                            )
                        )
                    ),
                );
                $feed_id = $fbObj->getFeedId();
                if ($feed_id > 0) {
                    $data['data'][0]['custom_data']['product_catalog_id'] = $feed_id;
                }
                $this->sendEventToFacebook($data);
            }
        }
    }
    public function initiateCheckoutTrigger($fb_event_checkout_page, $id_cart, $ajax = false)
    {
        $cart = new Cart((int)$id_cart);
        $cart_products = $cart->getProducts();
        if (!empty($cart_products)) {
            $content_products = array();
            $content_ids = array();
            $content_categories = array();
            $num_items = 0;
            $value = 0;
            foreach ($cart_products as $val) {
                $num_items = $num_items + (int)$val['cart_quantity'];
                //content id generate
                $id_product_attribute = (int)$val['id_product_attribute'];
                $content_id = $this->product_prefix . (int)$val['id_product'];
                $combination = '';
                if ((int)$id_product_attribute > 0 && $this->combination) {
                    $combination = $this->combination_prefix . $id_product_attribute;
                }
                $content_id = $content_id . $combination;
                $content_ids[] = $content_id;
                if (!empty(pSQL($val['category']))) {
                    $content_categories[] = pSQL($val['category']);
                }
                $value = $value + $val['total_wt'];
                $p_data = array(
                    'id' => $content_id,
                    'quantity' => (int)$val['cart_quantity'],
                    'category' => pSQL($val['category']),
                    'price' => $val['total_wt']
                );
                $content_products[] = $p_data;
            }
            $value = (Configuration::get('FCTP_START_ORD_VALUE') != '') ? Configuration::get('FCTP_START_ORD_VALUE') : $value;

            $data = array(
                'data' => array(
                    array(
                        'event_name' => 'InitiateCheckout',
                        'event_time' => time(),
                        'event_id' => $fb_event_checkout_page,
                        'event_source_url' => $this->url,
                        'user_data' => $this->getUserData(),
                        'custom_data' => array(
                            'content_type' => 'product',
                            'contents' => $content_products,
                            'content_ids' => $content_ids,
                            'currency' => $this->currency_iso_code,
                            'num_items' => (int)$num_items,
                            'content_category' => 'Checkout',
                            'value' => $value,
                        )
                    )
                ),
            );
            $feed_id = FacebookConversionTrackingPlus::getFeedId();
            if ($feed_id > 0) {
                $data['data'][0]['custom_data']['product_catalog_id'] = $feed_id;
            }
            $this->sendEventToFacebook($data);
            if ($ajax) {
                //echo 'SetCookie';
                // External cookie is needed to prevent external cache systems to cache the event_id
                if ($this->context->cookie->__set('InitiateCheckout', $this->url)) {
                    echo 'Cookie could not be set';
                    return false;
                };
            } else {
                // Cookie to prevent duplicates
                $this->context->cookie->__set('InitiateCheckout', $this->url);
            }
            return true;
        }
        return false;
    }
    public function accountRegisterTrigger()
    {
        $data = array(
            'data' => array(
                array(
                    'event_name' => 'CompleteRegistration',
                    'event_time' => time(),
                    'event_id' => self::generateEventId('CompleteRegistration'),
                    'event_source_url' => $this->url,
                    'custom_data' => array(
                        'content_name' => 'Reistered Customer',
                        'currency' => $this->currency_iso_code,
                        'status' => 'registered',
                        'value' => Configuration::get('FCTP_REG_VALUE'),
                    ),
                    'user_data' => $this->getUserData()
                )
            ),
        );
        $this->sendEventToFacebook($data);
    }

    public function customizeProductTrigger($dataCat, $eventdid)
    {
        if (!isset(Context::getContext()->cookie->$eventdid)) {
            $data = array(
                'data' => array(
                    array(
                        'event_name' => 'CustomizeProduct',
                        'event_time' => time(),
                        'event_id' => $eventdid,
                        'event_source_url' => $this->url,
                        'custom_data' => $dataCat,
                        'user_data' => $this->getUserData()
                    )
                ),
            );
            Context::getContext()->cookie->$eventdid = true;
            $this->sendEventToFacebook($data);
        }
    }


    public function purchaseEventTrigger($fb_event_purchase_page, $id_order)
    {
        $order = new Order((int)$id_order);
        $cart = new Cart((int)$order->id_cart);
        $cart_products = $cart->getProducts();
        $content_products = array();
        $content_ids = array();
        $content_categories = array();
        $total_items = 0;
        foreach ($cart_products as $val) {
            $total_items = $total_items + (int)$val['cart_quantity'];
            //content id generate
            $id_product_attribute = (int)$val['id_product_attribute'];
            $content_id = $this->product_prefix . (int)$val['id_product'];
            $combination = '';
            if ((int)$id_product_attribute > 0 && $this->combination) {
                $combination = $this->combination_prefix . $id_product_attribute;
            }
            $content_id = $content_id . $combination;
            $content_ids[] = $content_id;
            if (!empty(pSQL($val['category']))) {
                $content_categories[] = pSQL($val['category']);
            }
            $data = array(
                'id' => $content_id,
                'quantity' => (int)$val['cart_quantity'],
                'category' => pSQL($val['category']),
                'price' => $val['total_wt']
            );
            $content_products[] = $data;
        }
        $data = array(
            'data' => array(
                array(
                    'event_name' => 'Purchase',
                    'event_time' => time(),
                    'event_id' => $fb_event_purchase_page,
                    'user_data' => $this->getUserData(),
                    'event_source_url' => $this->url,
                    'custom_data' => array(
                        'content_type' => 'product',
                        'currency' => $this->currency_iso_code,
                        'value' => $order->total_paid,
                        'content_ids' => $content_ids,
                        'contents' => $content_products,
                        'num_items' => (int)$total_items,
                        'content_category' => implode(',', $content_categories),
                        'order_id' => (int)$id_order
                    ),
                )
            ),
        );
        $this->sendEventToFacebook($data);
    }
    public function searchEventTrigger($fb_pixel_event_search, $id_lang, $search_query = '', $value = '', $content_ids_list = array())
    {
        $data = array(
            'data' => array(
                array(
                    'event_name' => 'Search',
                    'event_time' => time(),
                    'event_id' => $fb_pixel_event_search,
                    'event_source_url' => $this->url,
                    'user_data' => $this->getUserData(),
                    'custom_data' => array(
                        'currency' => $this->currency_iso_code,
                        'value' => $value,
                        'search_string' => $search_query,
                        'content_type' => 'product',
                        'content_ids' => implode(',', $content_ids_list),
                    ),
                )
            ),
        );
        $feed_id = FacebookConversionTrackingPlus::getFeedId();
        if ($feed_id > 0) {
            $data['data'][0]['custom_data']['product_catalog_id'] = $feed_id;
        }
        $this->sendEventToFacebook($data);
    }
    public function wishlistEventTrigger($idProduct, $id_product_attribute, $fb_pixel_wishlist_event_id)
    {
        $content_id = $this->product_prefix . $idProduct;
        $combination = '';
        if ((int)$id_product_attribute > 0 && $this->combination) {
            $combination = $this->combination_prefix . $id_product_attribute;
        }
        $content_id = $content_id . $combination;
        $value = Configuration::get('FCTP_WISH_VALUE');
        $data = array(
            'data' => array(
                array(
                    'event_name' => 'AddToWishlist',
                    'event_time' => time(),
                    'event_id' => $fb_pixel_wishlist_event_id,
                    'event_source_url' => $this->url,
                    'user_data' => $this->getUserData(),
                    'custom_data' => array(
                        'currency' => $this->currency_iso_code,
                        'value' => $value,
                        'content_ids' => array($content_id),
                        'content_type' => 'product'
                    ),
                )
            ),
        );
        $this->sendEventToFacebook($data);
    }

    public function pageViewTrigger($ev_id)
    {
        $data = array(
            'data' => array(
                array(
                    'event_name' => 'PageView',
                    'event_time' => time(),
                    'event_id' => $ev_id,
                    'event_source_url' => $this->url,
                    'user_data' => $this->getUserData()
                )
            ),
        );

        $this->sendEventToFacebook($data);
    }

    public function sendEventToFacebook($data)
    {
        // Prevent malformations on the generated JSON
        $data['data'] = array_values($data['data']);

        $consent = true;
        if (Configuration::get('FCTP_BLOCK_SCRIPT')) {
            $consent = false;
            $cookie = Configuration::get('FCTP_COOKIE_NAME');
            if ($cookie != '') {
                $value = Configuration::get('FCTP_COOKIE_VALUE');
                $module = new FacebookConversionTrackingPlus();
                $consent = $module->checkCookies($cookie, $value);
            }
        }
        if ($this->logEnabled && $consent == false) {
            PrestaShopLogger::addLog('[Conversions API] Consent check failed, conversion is not triggered.', 1, null, 'FB-Conversion');
        }
        if (filter_var($this->ip, FILTER_VALIDATE_IP)) {
            foreach ($this->pixels_ids as $inc => $pixel_id) {
                if (Configuration::get('FCTP_CONVERSION_API') && $consent) {
                    $data = $this->addCommonDataToEvent($data, $inc);
                    // $access_token = Configuration::get('FCTP_CONVERSION_API_ACCESS_TOKEN');
                    //echo Tools::jsonEncode($data); // it should be in array
                    // echo "<pre>"; print_r($data); echo "</pre>";
                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://graph.facebook.com/v12.0/' . pSQL($pixel_id['id']) . '/events?access_token=' . pSQL($pixel_id['token']),
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 2, // Was 30
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => Tools::jsonEncode($data),
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/json'
                        ),
                    ));
                    $ret = curl_exec($curl);
                    curl_close($curl);
                    if ($ret === false) {
                        if ($this->logEnabled) {
                            $formatResponse = $this->formatResponse($ret, $data, $pixel_id['id']);
                            PrestaShopLogger::addLog($formatResponse, 1, 403, 'FB-Conversion');
                        }
                    } else {
                        if ($this->logEnabled) {
                            $formatResponse = $this->formatResponse($ret, $data, $pixel_id['id']);
                            PrestaShopLogger::addLog($formatResponse, 1, 200, 'FB-Conversion');
                            if ($this->logPayload) {
                                PrestaShopLogger::addLog('Data Sent:' . Tools::jsonEncode($data), 1, 200, 'FB-Conversion');
                            }
                        }
                    }
                }
            }
        } else {
            if ($this->logEnabled) {
                $errorMessage = "Invalid IP found on cart id :" . (int)Context::getContext()->cart->id . '. Event not sent to FB:' . $this->formatResponse('{}', $data, 0);
                PrestaShopLogger::addLog($errorMessage, 1, 500, 'FB-Conversion');
            }
        }
    }

    public function formatResponse($response, $data, $pixel_id)
    {
        $output = array();
        $output[] = '[' . $data['data'][0]['event_name'] . ' - ' . $pixel_id . '] ';
        $output[] = '[EV-ID - ' . $data['data'][0]['event_id'] . '] ';
        $output[] = '[URL - ' . $this->url . '] ';
        $response = Tools::jsonDecode($response, true);
        foreach ($response as $key => $resp) {
            if (!is_array($resp)) {
                $output[] = $key . '=>' . $resp;
            } else {
                $output[] = $key . '=>' . implode(" ", $resp);
            }
        }
        return implode(" ", $output);
    }

    private function setNullTestCode($configName)
    {
        $Maxminute = 1440;
        $data = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'configuration WHERE name = \'' . pSQL($configName) . '\'');
        $from_time = strtotime($data['date_upd']);
        $to_time = strtotime(date("Y-m-d H:i:s"));
        $min = round(abs($to_time - $from_time) / 60, 2);
        if ($min > $Maxminute) {
            Configuration::updateValue($configName, '');
        }
    }

    private function addCommonDataToEvent($data, $inc = 1)
    {
        $testCode = Configuration::get('FCTP_CONVERSION_API_TEST_' . $inc);

        if ($testCode != '' && $this->testCodeEnabled) {
            $this->setNullTestCode('FCTP_CONVERSION_API_TEST_' . $inc);
            $data['test_event_code'] = $testCode;
        }

        if ($this->_fbp) {
            $data['data'][0]['user_data']['fbp'] = $this->_fbp;
        }
        if ($this->_fbc) {
            $data['data'][0]['user_data']['fbc'] = $this->_fbc;
        }
        // Add the website event to all $data events
        $data['data'][0]['action_source'] = 'website';
        if (!isset($data['event_source_url'])) {
            $data[0]['event_source_url'] = $this->url;
        }
        return $data;
    }
    public static function generateEventId($event)
    {
        $round = array(
            'ViewContent' => 10,
            'ViewCategory' => 10,
            'CompleteRegistration' => 10,
            'InitiateCheckout' => 10,
            'Purchase' => 10,
            'Search' => 10,
            'AddToCart' => 5,
            'AddToWishlist' => 5,
            'AddPaymentInfo' => 5,
            'CustomizeProduct' => 5,
        );
        if (isset($round[$event])) {
            $time_rounded = floor(time() / $round[$event]) * $round[$event];
        } else {
            $time_rounded = floor(time() / ConversionApi::DEFAULT_ROUND) * ConversionApi::DEFAULT_ROUND;
        }
        return (Context::getContext()->cookie->id_customer ? Context::getContext()->cookie->id_customer : Context::getContext()->cookie->id_guest) . '.' . $event . '.' . $time_rounded;
    }
    public function getHashData($val, $prefix = '')
    {
        $pre = !empty($prefix) ? $prefix . '_' : '';
        return hash('sha256', $pre . $val);
    }

    private function cleanUrlSensitiveParameters()
    {
        // Removes any sensitive data from the URL sent to Facebook
        $data = array('name', 'firstname', 'lastname', 'email', 'phone', 'tel', 'address', 'ip');
        $url = explode('?', $this->url);
        $query_url = array();
        if (count($url) > 1) {
            parse_str($url[1], $query_url);
            foreach ($data as $i) {
                if (in_array($i, $query_url)) {
                    unset($query_url[$i]);
                }
            }
            $this->url = $url[0].'?'.http_build_query($query_url);
        }
    }
}

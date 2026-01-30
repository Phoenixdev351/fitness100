<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class SequraReportBuilder
{
    private $_needs_adjust_totals = false;
    private $_unshipped_suborders = false;
    private $_shipped_suborders = false;
    private $_shipped_suborder_id = null;
    static $empty_cart = array(
                'items'=>array(),
                'order_total_without_tax'=>0,
                'order_total_with_tax'=>0,
                'currency' => 'EUR'
            );

    public function __construct($merchant_id, $orders, $stats)
    {
        $this->merchant_id = $merchant_id;
        $this->orders = $orders;
        $this->stats = $stats;
    }

    public function build()
    {
        $this->_report_ids = array();
        $report = array(
            'merchant' => $this->merchant(),
            'platform' => $this->platform()
        );
        list($orders, $broken_orders) = $this->ordersWithItems();
        $report['orders'] = $orders;
        $report['broken_orders'] = $broken_orders;
        $report['statistics'] = $this->statsWithData();
        return $report;
    }

    public function buildSingleOrder($shipped_suborder = null)
    {
        if ($shipped_suborder instanceof Order) {
            $this->_shipped_suborder_id = $shipped_suborder->id;
        }
        list($orders, $broken_orders) = $this->ordersWithItems();
        $orders[0]['merchant'] = $this->merchant();
        $orders[0]['platform'] = $this->platform();
        $orders[0]['shipped_cart'] = $orders[0]['cart'];
        unset($orders[0]['cart']);
        if (isset($orders[0]['remaining_cart'])) {
            $orders[0]['unshipped_cart'] = $orders[0]['remaining_cart'];
            unset($orders[0]['remaining_cart']);
        } else {
            $orders[0]['unshipped_cart'] = self::$empty_cart;
        }
        return $orders[0];
    }

    private $_report_ids = null;

    public function getReportOrderIds()
    {
        return $this->_report_ids;
    }

    public function merchant()
    {
        return array('id' => $this->merchant_id);
    }

    public function ordersWithItems()
    {
        $list = array();
        $broken_list = array();
        foreach ($this->orders as $order) {
            $data = $this->order($order);
            if (method_exists($order->module, 'customizeBuildedReport')) {
                $report = $order->module->customizeBuildedReport($report, $this);
            }
            $this->_report_ids[] = $order->id;
            if (self::isValidOrder($data)) {
                $list[] = $data;
            } else {
                $broken_list[] = $data;
            }
        }
        return array($list, $broken_list);
    }

    public function order($primary_order)
    {
        $this->setShippedSuborders($primary_order);
        $data = array(
            'state' => 'shipped', // PS is broken and doesn't by default make a difference between shipped and delivered
            'delivery_address' => $this->deliveryAddress($primary_order),
            'invoice_address' => $this->invoiceAddress($primary_order),
            'customer' => $this->customer($primary_order),
            'merchant_reference' => self::getMerchantRefs($primary_order),
            'cart' => $this->cart($this->_shipped_suborders),
        );

        if ($this->_shipped_suborders[0]->delivery_date > '1') {
            // Stupid MySQL thinks 0000 is a year
            $data['sent_at'] = $this->_shipped_suborders[0]->delivery_date;
        }
        $data['cart'] = $this->fixTotal($data['cart']);
        if (count($this->_unshipped_suborders)>0) {
            $data['remaining_cart'] = $this->cart($this->_unshipped_suborders);
            $data['remaining_cart'] = $this->fixTotal($data['remaining_cart']);
        } else {
            $data['remaining_cart'] = self::$empty_cart;
        }
        return $data;
    }

    protected function setShippedSuborders($primary_order)
    {
        $orders = array($primary_order);
        $this->_unshipped_suborders = $this->_shipped_suborders = array();
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $orders = Order::getByReference($primary_order->reference);
        }
        foreach ($orders as $order) {
            if (!$order->hasBeenShipped() && $order->id != $this->_shipped_suborder_id) {
                $this->_unshipped_suborders[] = $order;
            } else {
                $this->_shipped_suborders[] = $order;
            }
        }
    }

    protected function fixTotal($cart)
    {
        $cart = $this->fixRoundingProblems($cart);
        $totals = SequraTools::totals($cart);
        $cart['order_total_with_tax'] = $totals['with_tax'];
        $cart['order_total_without_tax'] = $totals['without_tax'];
        return $cart;
    }

    protected function fixRoundingProblems($cart)
    {
        $totals = SequraTools::totals($cart);
        $diff_with_tax = $cart['order_total_with_tax'] - $totals['with_tax'];
        $diff_without_tax = $cart['order_total_without_tax'] - $totals['without_tax'];
        /*Don't correct error bigger than 1 cent per line*/
        if ($diff_with_tax == 0 || count($cart['items']) < abs($diff_with_tax)) {
            return $cart;
        }

        $item['type'] = 'discount';
        $item['reference'] = 'Ajuste';
        $item['name'] = 'Ajuste';
        $item['total_with_tax'] = $diff_with_tax;
        if ($diff_with_tax > 0) {
            $item['type'] = 'handling';
            $item['tax_rate'] = $diff_without_tax ? round(abs(($diff_with_tax * $diff_without_tax)) - 1) * 100 : 0;
        }
        $cart['items'][] = $item;
        return $cart;
    }

    public function statsWithData()
    {
        $list = array();
        if (Configuration::get('SEQURA_STATS_ALLOW') != 'N') {
            foreach ($this->stats as $order) {
                $data = $order->validateFields($die = false) ? $order->getFields() : array();
                SequraTools::removeProtectedKeys($data, SequraOrderBuilder::$protectedCartKeys);
                SequraTools::translateKeys(
                    $data,
                    (_PS_VERSION_ >= 1.5) ? self::$standardCartKeys15 : self::$standardCartKeys14,
                    $order
                );
                SequraTools::makeIntegerPrices($data, self::$cartPriceKeys);
                if (is_null($order->date_add) || '' == $order->date_add) {
                    continue;
                }
                $stat = array(
                    "completed_at" => $order->date_add,
                    "merchant_reference" => $this->getMerchantRefs($order)
                );
                //Seems this is mandatory
                if (true || Configuration::get('SEQURA_STATS_AMOUNT') != 'N') {
                    $currency = new Currency($order->id_currency);
                    $stat['currency'] = ($currency->iso_code ? $currency->iso_code : 'EUR');
                    $stat['amount'] = $data['order_total_with_tax'];
                }
                if (Configuration::get('SEQURA_STATS_COUNTRIES') != 'N') {
                    $address = new Address((int)$order->id_address_delivery);
                    $country = new Country((int)$address->id_country);
                    $stat['country'] = $country->iso_code;
                }
                if (Configuration::get('SEQURA_STATS_PAYMENTMETHOD') != 'N') {
                    // CC, COD, PP, TR, SQ, O
                    $stat['payment_method'] = SequraTools::getPaymentMethod($order->module);
                    $stat['payment_method_raw'] = $order->payment;
                }
                if (Configuration::get('SEQURA_STATS_STATUS') != 'N') {
                    //processing, shipped and cancelled
                    if (_PS_VERSION_ >= 1.5) {
                        $orderstate = $order->getCurrentOrderState();
                        $name = array_pop($orderstate->name);
                    } else {
                        $orderstate = $order->getCurrentStateFull(1);
                        $name = $orderstate['name'];
                    }
                    $stat['status'] = SequraTools::getOrderStatus($orderstate, $name);
                    $stat['raw_status'] = is_null($name) ? '' : $name;
                }
                $list[] = $stat;
            }
        }
        return array('orders' => $list);
    }

    public static function getMerchantRefs($order)
    {
        if (_PS_VERSION_ < 1.5) {
            return array(
                "order_ref_1" => $order->id,
            );
        }
        if (0 == Configuration::get('SEQURA_ORDER_ID_FIELD')) {
            return array(
                "order_ref_1" => $order->reference,
                "order_ref_2" => $order->id
            );
        }
        return array(
            "order_ref_1" => $order->id,
        );
    }

    public static $protectedCartKeys = array("secure_key");
    public static $standardCartKeys15 = array(
        'order_total_with_tax' => 'total_paid_tax_incl',
        'order_total_without_tax' => 'total_paid_tax_excl',
        'total_shipping_tax_incl' => 'total_shipping_tax_incl',
        'total_shipping_tax_excl' => 'total_shipping_tax_excl',
        'total_wrapping_tax_incl' => 'total_wrapping_tax_incl',
        'total_wrapping_tax_excl' => 'total_wrapping_tax_excl',
        'carrier_tax_rate' => 'carrier_tax_rate',
    );
    public static $standardCartKeys14 = array(
        'order_total_with_tax' => 'total_paid',
        'total_shipping' => 'total_shipping',
        'total_wrapping' => 'total_wrapping',
        'carrier_tax_rate' => 'carrier_tax_rate',
    );
    public static $cartPriceKeys = array(
        "carrier_tax_rate",
        "total_shipping_tax_incl",
        "total_shipping_tax_excl",
        "total_wrapping_tax_incl",
        "total_wrapping_tax_excl",
        "order_total_with_tax",
        "order_total_without_tax",
        "total_discounts_tax_incl",
        "total_discounts_tax_excl",
        "total_shipping",
    );

    public function cart($suborders)
    {
        $order = $suborders[0];
        $data = $order->validateFields($die = false) ? $order->getFields() : array();
        SequraTools::removeProtectedKeys($data, SequraOrderBuilder::$protectedCartKeys);
        SequraTools::translateKeys(
            $data,
            (_PS_VERSION_ >= 1.5) ? self::$standardCartKeys15 : self::$standardCartKeys14,
            $order
        );
        SequraTools::makeIntegerPrices($data, self::$cartPriceKeys);
        $currency = new Currency((int)$order->id_currency);
        $data['currency'] = ($currency->iso_code ? $currency->iso_code : 'EUR');
        $carrier = new Carrier((int)$order->id_carrier, $this->getLangId($order));
        $data['delivery_method'] = array(
            "name" => SequraTools::notNull($carrier->name)
        );
        if (is_string($carrier->delay)) {
            $data['delivery_method']['days'] = SequraTools::notNull($carrier->delay);
        }
        $data['gift'] = $order->gift ? true : false;
        $data['items'] = array_merge(
            $this->items($suborders),
            $this->discounts($suborders),
            $this->handlingItems($suborders, $carrier)
        );
        $vouchers = self::getOrderVouchers($order->id_customer, $order->id);
        if ($this->_needs_adjust_totals) {
            if (0 < sizeof($vouchers)) {
                $this->_needs_adjust_totals = false;
                $voucher = $this->voucher($order, $vouchers);
                //Forcing t_wo_t = t_w_t in voucher creates a differenc in cart t_wo_t
                if (!isset($data['order_total_without_tax'])) {
                    $data['order_total_without_tax'] = 0;
                }
                $data['order_total_without_tax'] += $voucher['order_total_without_tax_diff'];
                unset($voucher['order_total_without_tax_diff']);
                $data['items'][] = $voucher;
            }
        }
        $this->possiblyAdjustTotals($data, $this->_needs_adjust_totals);
        $data['order_total_without_tax'] = $data['order_total_with_tax'];
        if (Configuration::get('SEQURA_ALLOW_REGISTRATION_ITEMS')) {
            $this->registrationItems($data);
        }
        return $data;
    }

    protected function registrationItems(&$data)
    {
        $items = $data['items'];
        foreach ($items as $key => $item) {
            if (!isset($item['product_id']) || !$item['product_id']) {
                continue;
            }
            $sq_product_extra = new SequraProductExtra($item['product_id']);
            $registration_amount = SequraTools::integerPrice(
                $sq_product_extra->getProductRegistrationAmount()
            );
            if ($registration_amount > 0) {
                $data['items'][] = array(
                    'type' => 'registration',
                    'reference'=> $item['reference'] . '-reg',
                    'name'=>'Reg. ' . $item['name'],
                    'total_with_tax' => $item['quantity'] * $registration_amount,
                );
                //Fix orginal item
                $data['items'][$key]['total_with_tax'] = max(
                    0,
                    $data['items'][$key]['total_with_tax'] - $item['quantity'] * $registration_amount
                );
                $data['items'][$key]['price_with_tax'] = max(
                    0,
                    $data['items'][$key]['price_with_tax'] - $registration_amount
                );
            }
        }
    }

    static function getOrderVouchers($id_customer, $id_order)
    {
        global $cookie;
        $vouchers = array();
        $needle = 'O' . (int)$id_order;
        if (_PS_VERSION_ >= 1.5) {
            $rules = CartRule::getCustomerCartRules((int)$cookie->id_lang, $id_customer, false, false);
        } else {
            $rules = Discount::getCustomerDiscounts((int)$cookie->id_lang, $id_customer, false, false);
        }

        foreach ($rules as $rule) {
            //Ends with needle
            if ($needle === "" || strpos($rule['name'], $needle, strlen($rule['name']) - strlen($needle)) !== false) {
                $vouchers[] = $rule;
            }
        }
        return $vouchers;
    }

    public function returned_cart($order)
    {
        $data = $order->validateFields($die = false) ? $order->getFields() : array();
        SequraTools::removeProtectedKeys($data, SequraOrderBuilder::$protectedCartKeys);
        SequraTools::translateKeys(
            $data,
            (_PS_VERSION_ >= 1.5) ? self::$standardCartKeys15 : self::$standardCartKeys14,
            $order
        );
        SequraTools::makeIntegerPrices($data, self::$cartPriceKeys);
        $currency = new Currency((int)$order->id_currency);
        $data['currency'] = ($currency->iso_code ? $currency->iso_code : 'EUR');
        $data['items'] = array_merge(
            $this->returned_items($order)
        );
        $this->possiblyAdjustTotals($data, true);
        return $data;
    }

    public static $protectedItemKeys = array("wholesale_price", "image", "tax_calculator");

    public static $priceKeys = array(
        "unit_price_tax_incl",
        "unit_price_tax_excl",
        "total_price_tax_incl",
        "total_price_tax_excl",
        "tax_rate",
        "total_shipping_price_tax_incl",
        "total_shipping_price_tax_excl",
        "additional_shipping_cost",
        // PS 1.4:
        'total_price',
        'total_wt',
        'product_price',
        'product_price_wt',
    );
    public static $standardItemKeys15 = array(
        "total_without_tax" => 'total_price_tax_excl',
        "total_with_tax" => 'total_price_tax_incl',
        "price_without_tax" => 'unit_price_tax_excl',
        "price_with_tax" => 'unit_price_tax_incl',
        "name" => 'product_name',
        "product_id" => 'id_product',
        "manufacturer" => 'id_manufacturer',
        "reference" => 'product_reference'
    );
    public static $standardItemKeys14 = array(
        "total_without_tax" => 'total_price',
        "total_with_tax" => 'total_wt',
        "price_without_tax" => 'product_price',
        "price_with_tax" => 'product_price_wt',
        "name" => 'product_name',
        "reference" => 'product_reference',
    );

    public static $necessaryItemKeys = array(
        "total_with_tax",
        "price_with_tax",
        "name",
        "reference",
        "quantity",
        "type",
        "ends_in",
        "ends_on",
        "downloadable"
    );

    public function items($suborders)
    {
        // TODO: handle customizations
        $items = array();
        foreach ($suborders as $order) {
            $items = array_merge($items, $this->itemsFromOrder($order));
        }
        return $items;
    }

    public function itemsFromOrder($order)
    {
        $items = array ();
        foreach ($order->getProducts() as $item) {
            $sq_product_extra = new SequraProductExtra($item['id_product']);
            if ($service_end_date = $sq_product_extra->shouldTreatAsService()) {
                $item["type"] = 'service';
                if (substr($service_end_date, 0, 1) == 'P') {
                    $item["ends_in"] = $service_end_date;
                } else {
                    $item["ends_on"] = $service_end_date;
                }
            } else {
                $item["type"] = 'product';
            }
            SequraTools::removeProtectedKeys($item, self::$protectedItemKeys);
            SequraTools::makeIntegerPrices($item, self::$priceKeys);
            SequraTools::translateKeys(
                $item,
                (version_compare(_PS_VERSION_, '1.5', '>=')) ?
                    self::$standardItemKeys15 :
                    self::$standardItemKeys14
            );
            if (!$item['product_id']) {
                $item['product_id'] = 'missing_id';
            } // if product has been deleted
            if (!isset($item['manufacturer']) || !$item['manufacturer']) {
                unset($item['manufacturer']);
            } // if product has been deleted
            if (!isset($item['reference']) || !$item['reference']) {
                $item['reference'] = $item['product_id'];
            }
            if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                $item['downloadable'] = $item['is_virtual'] ? true : false;
            } else {
                $item['downloadable'] = $item['download_hash'] ? true : false;
            }
            $remaining_quantity = (int)$item['product_quantity_refunded'] + (int)$item['product_quantity_return'];
            $quantity = (int)$item['product_quantity'] - $remaining_quantity;
            if ($remaining_quantity > 0) {
                $this->_needs_adjust_totals = true;
                $item['total_without_tax'] = $item['total_with_tax'] = $item['price_with_tax'] * $quantity;
            }

            $item['quantity'] = $quantity;
            $item['tax_rate'] = 0;
            $item['price_without_tax'] = $item['price_with_tax'];
            $item['total_without_tax'] = $item['total_with_tax'];
            SequraTools::removeUnnecessaryKeys($item, self::$necessaryItemKeys);
            $items[] = SequraTools::stripHTML($item);
        }
        return $items;
    }

    public function returned_items($order)
    {
        $items = array();
        foreach ($order->getProducts() as $item) {
            SequraTools::removeProtectedKeys($item, self::$protectedItemKeys);
            SequraTools::makeIntegerPrices($item, self::$priceKeys);
            SequraTools::translateKeys(
                $item,
                (_PS_VERSION_ >= 1.5) ? self::$standardItemKeys15 : self::$standardItemKeys14
            );
            $item['quantity'] = (int)$item['product_quantity_return'] + (int)$item['product_quantity_refunded'];
            if ($item['quantity'] <= 0) {
                continue;
            }
            $item['total_without_tax'] = $item['total_with_tax'] = $item['price_with_tax'] * $item['quantity'];
            if (!$item['product_id']) {
                $item['product_id'] = 'missing_id';
            } // if product has been deleted
            if (!isset($item['manufacturer']) || !$item['manufacturer']) {
                unset($item['manufacturer']);
            } // if product has been deleted
            if (!isset($item['reference']) || !$item['reference']) {
                $item['reference'] = $item['product_id'];
            }
            if (_PS_VERSION_ >= 1.5) {
                $item['downloadable'] = $item['is_virtual'] ? true : false;
            } else {
                $item['downloadable'] = $item['download_hash'] ? true : false;
            }

            $items[] = $item;
        }
        return $items;
    }

    public static $discountPriceKeys = array(
        // 1.5 => value_tax_excl but 1.4 => value_tax_exc.  The horror!
        'value',
        'value_tax_excl',
        'value_tax_exc',
        'reduction_percent',
        'reduction_amount',
        'value_real',
    );
    public static $standardDiscountKeys15 = array(
        "reference" => 'code',
    );
    public static $standardDiscountKeys14 = array(
        "reference" => 'name',
        "name" => 'description',
    );
    public static $discountItemKeys15 = array(
        "total_without_tax" => 'value',
        "total_with_tax" => 'value',
    );
    public static $discountItemKeys14 = array(
        "total_without_tax" => 'value_real',
        "total_with_tax" => 'value_real',
    );

    public function discounts($suborders)
    {
        $items = array();
        foreach ($suborders as $order) {
            $items = array_merge($items, $this->discounts_from_order($order));
        }
        return $items;
    }

    public function discounts_from_order($order)
    {
        $items = array();
        // Don't use cart's cart rules. They might not be available, e.g. if shop uses AwoCoupon.
        // In PS 1.4 the order's Discounts contain too little information to construct an invoice.
        $discount_items = (_PS_VERSION_ >= 1.5) ? $order->getCartRules() : Cart::getCartByOrderId($order->id)->getDiscounts();
        foreach ($discount_items as $item) {
            $item['type'] = 'discount';
            SequraTools::makeIntegerPrices($item, self::$discountPriceKeys);
            SequraTools::translateKeys(
                $item,
                (_PS_VERSION_ >= 1.5) ? self::$standardDiscountKeys15 : self::$standardDiscountKeys14
            );
            $discountItemKeys = (_PS_VERSION_ >= 1.5) ? self::$discountItemKeys15 : self::$discountItemKeys14;
            foreach ($discountItemKeys as $item_key => $discount_key) {
                $item[$item_key] = -$item[$discount_key];
            }
            // Reference is missing if original cart rule does not exist (AwoCoupon again).
            if (!isset($item['reference']) || !$item['reference']) {
                $item['reference'] = $item['name'];
            }
            // Discount might have "quantity" which is really "vouchers in stock"
            unset($item['quantity']);
            $items[] = $item;
        }
        return $items;
    }

    public function voucher($order, $vouchers)
    {
        //Get the price form remaining cart an value from credit slips
        $value = 0;
        foreach ($vouchers as $voucher) {
            if (_PS_VERSION_ >= 1.5) {
                $value += $voucher['reduction_amount'];
            } else {
                $value += $voucher['value'];
            }
        }
        $returned_items = $this->returned_cart($order);

        $item = array(
            'type' => 'voucher',
            'reference' => $vouchers[0]['name'],
            'name' => 'Vale de compra por ' . $value . "€",
            'total_without_tax' => $returned_items['order_total_with_tax'],
            'total_with_tax' => $returned_items['order_total_with_tax'],
            'order_total_without_tax_diff' => $returned_items['order_total_with_tax'] - $returned_items['order_total_without_tax']
        );
        return $item;
    }

    public function handlingItems($suborders, $carrier)
    {
        // TODO: handle customizations
        $items = array();
        foreach ($suborders as $order) {
            $data = $order->validateFields($die = false) ? $order->getFields() : array();
            SequraTools::makeIntegerPrices($data, self::$cartPriceKeys);
            $items = array_merge($items, $this->handlingItemsFromOrder($data, $carrier));
        }
        return $items;
    }

    public function handlingItemsFromOrder($data, $carrier)
    {
        return _PS_VERSION_ >= 1.5 ? $this->handlingItems15($data, $carrier) : $this->handlingItems14(
            $data,
            $carrier
        );
    }

    public function handlingItems15($order, $carrier)
    {
        $items = array();
        if ($order['total_shipping_tax_incl']) {
            $items[] = array(
                'type' => 'handling',
                'reference' => 'shipping',
                'name' => SequraTools::notNull($carrier->name),
                'tax_rate' => 0,
                'total_without_tax' => $order['total_shipping_tax_incl'],
                'total_with_tax' => $order['total_shipping_tax_incl']
            );
        }
        if ($order['gift'] && $order['total_wrapping_tax_incl']) {
            $items[] = array(
                'type' => 'handling',
                'reference' => 'giftwrapping',
                'name' => 'Envoltorio para regalo',
                'tax_rate' => 0,
                'total_without_tax' => $order['total_wrapping_tax_incl'],
                'total_with_tax' => $order['total_wrapping_tax_incl']
            );
        }
        return $items;
    }

    public function handlingItems14($order, $carrier)
    {
        $items = array();
        if (!$order['total_shipping']) {
            $items[] = array(
                'type' => 'handling',
                'reference' => 'shipping',
                'name' => SequraTools::notNull($carrier->name),
                'tax_rate' => 0,
                'total_without_tax' => $order['total_shipping'],
                'total_with_tax' => $order['total_shipping']
            );
        }
        if ($order['gift'] && $order['total_wrapping']) {
            $items[] = array(
                'type' => 'handling',
                'reference' => 'giftwrapping',
                'name' => 'Envoltorio para regalo',
                'tax_rate' => 0,
                'total_without_tax' => $order['total_wrapping'],
                'total_with_tax' => $order['total_wrapping']
            );
        }
        return $items;
    }

    public static function possiblyAdjustTotals(&$cart, $force = false)
    {
        if (!$force && _PS_VERSION_ >= 1.5) {
            return;
        }
        $totals = SequraTools::totals($cart);
        // 1.4 orders don't have total_without_tax
        $cart['order_total_without_tax'] = $totals['without_tax'];
        // total_with_tax may have rounding errors. Allow one cent rounding error per item in cart:
        $diff_cents = abs($cart['order_total_with_tax'] - $totals['with_tax']);
        if ($force || $diff_cents <= self::itemCount($cart)) {
            $cart['order_total_with_tax'] = $totals['with_tax'];
            $cart['order_total_without_tax'] = $totals['without_tax'];
        }
    }
    private function isValidOrder($order){
        return self::isConsistentCart($order['cart'])
            && self::isValidAddress($order['invoice_address'])
            && self::isValidAddress($order['delivery_address']);
    }

    private function isValidAddress($address){
        $mandatory_fields = [
            'given_names',
            'surnames',
            'company',
            'address_line_1',
            'address_line_2',
            'postal_code',
            'city',
            'country_code',
        ];
        foreach($mandatory_fields as $field) {
            if(!isset($address[$field]))
                return false;
        }
        $no_emptyable_fields = [
            'given_names',
            'surnames',
            'address_line_1',
            'postal_code',
            'city',
            'country_code',
        ];
        foreach($no_emptyable_fields as $field) {
            if(!isset($address[$field]))
                return false;
        }
        return true;
    }

    public static function isConsistentCart($cart)
    {
        $totals = SequraTools::totals($cart);
        return
            $cart['order_total_without_tax'] == $totals['without_tax'] && $cart['order_total_with_tax'] == $totals['with_tax']
            && self::areAllDiscountsNegative($cart);
    }

    private static function areAllDiscountsNegative($cart)
    {
        foreach ($cart['items'] as $item) {
            if (
                isset($item['type']) &&
                $item['type'] == 'discount' &&
                $item['total_with_tax'] > 0
            ) {
                return false;
            }
        }
        return true;
    }

    public static function itemCount($cart)
    {
        $count = 0;
        foreach ($cart['items'] as $item) {
            $count += isset($item['quantity']) ? $item['quantity'] : 1;
        }
        return $count;
    }

    public function deliveryAddress($order)
    {
        $address = new Address((int)$order->id_address_delivery);
        return SequraOrderBuilder::address($address);
    }

    public function invoiceAddress($order)
    {
        $address = new Address((int)$order->id_address_invoice);
        return SequraOrderBuilder::address($address);
    }

    public function customer($order)
    {
        $customer = new Customer((int)$order->id_customer);
        return SequraOrderBuilder::staticCustomerData($customer);
    }

    private function getLangId($order)
    {
        $customer = new Customer((int)$order->id_customer);
        return (isset($customer->id_lang)) ? (int)$customer->id_lang : (int)Configuration::get('PS_LANG_DEFAULT');
    }

    public function platform()
    {
        $data = array(
            'name' => 'PrestaShop',
            'version' => _PS_VERSION_,
            'plugin_version' => Sequrapayment::$VERSION,
            'php_version' => phpversion(),
            'php_os' => PHP_OS,
            'uname' => SequraTools::getUname(),
            'db_name' => 'mysql',
            'db_version' => (_PS_VERSION_ >= 1.5) ? Db::getInstance()->getVersion() : Db::getInstance()->getServerVersion(),
        );
        return $data;
    }
}

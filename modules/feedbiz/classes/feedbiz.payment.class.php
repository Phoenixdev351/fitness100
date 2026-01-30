<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Feed.Biz, Ltd.
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Feed.Biz, Ltd. is strictly forbidden.
 * In order to obtain a license, please contact us: contact@feed.biz
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Feed.Biz, Ltd.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Feed.Biz, Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Feed.Biz, Ltd. a l'adresse: contact@feed.biz
 * ...........................................................................
 *
 * @author    Olivier B.
 * @copyright Copyright (c) 2011-2022 Feed.Biz - Hong Kong - Head Quarters - Room 1408, 14/F,
 *            Tak Shing House - Theatre Lane - 20 des Voeux Road Central, -Central, Hong Kong
 * @license   Commercial license
 * Support by mail  :  support@feed.biz
 */

class FeedBizPaymentModule extends PaymentModule
{
    /**
     * @param $id_order_state
     * @param string $payment_method
     * @param null $marketplace_order_id
     * @param FeedBizCart $mp_cart
     * @param bool $use_taxes
     *
     * @return bool|int
     * @throws PrestaShopException
     */
    public function feedbizValidateOrder($id_order_state, $payment_method = 'Unknown', $marketplace_order_id = null, FeedBizCart $mp_cart = null, $use_taxes = true, $param = null)
    {
        $fb_product_name=array();
        $force_import = false;
        if (!empty($param['product_name'])) {
            $fb_product_name=$param['product_name'];
        }
        if (!empty($param['force_import'])) {
            $force_import=$param['force_import'];
        }
        if (Feedbiz::$debug_mode) {
            echo "<pre>FB product name(FN:".__FUNCTION__." LINE:".__LINE__."): \n";
            echo print_r($fb_product_name, true)."\n";
            echo "</pre>\n";
        }
        // Copying data from cart
        $order = new FeedBizOrder();

        $order->date_add = $mp_cart->mp_date;
        $order->date_upd = date('Y-m-d H:i:s');

        $order->id_carrier = (int)$mp_cart->id_carrier;
        $order->id_customer = (int)$mp_cart->id_customer;
        $order->id_address_invoice = (int)$mp_cart->id_address_invoice;
        $order->id_address_delivery = (int)$mp_cart->id_address_delivery;
        $order->id_currency = (int)$mp_cart->id_currency;
        $order->id_lang = (int)$mp_cart->id_lang;
        $order->id_cart = (int)$mp_cart->id;
        $customer = new Customer((int)$order->id_customer);
        $order->secure_key = pSQL($customer->secure_key);
        if (!$order->secure_key) {
            $order->secure_key = md5(time());
        }
        $order->payment = Tools::substr($payment_method, 0, 32);
        $order->module = Feedbiz::MODULE_NAME;
        $order->recyclable = (bool)Configuration::get('PS_RECYCLABLE_PACK');
        $order->gift = (int)$mp_cart->gift;
        $order->gift_message = $mp_cart->gift_message;

        $order->total_products = (float)$mp_cart->marketplaceGetOrderTotal(false, 1);
        $order->total_products_wt = (float)$mp_cart->marketplaceGetOrderTotal(true, 1);
        $order->total_discounts = (float)$mp_cart->marketplaceGetOrderTotal(true, 9);//isset($mp_cart->subtotal_discount) ? (float)$mp_cart->subtotal_discount : 0;

        //(float)abs($mp_cart->marketplaceGetOrderTotal(false, 2));
        $order->total_shipping = (float)$mp_cart->marketplaceGetOrderTotal(true, 5);
        $order->total_wrapping = (float)$mp_cart->marketplaceGetOrderTotal(true, 6);
        $order->total_paid_real = (float)$mp_cart->marketplaceGetOrderTotal(true, 3);
        $order->total_paid = (float)$mp_cart->marketplaceGetOrderTotal(true, 3);
        $order->carrier_tax_rate = $mp_cart->marketplaceGetCarrierTaxRate();

        $order->round_mode = Configuration::get('PS_PRICE_ROUND_MODE');
        $order->round_type = Configuration::get('PS_ROUND_TYPE');

        $null_date = '0000-00-00 00:00:00';
        $order->invoice_date = $null_date;
        $order->delivery_date = $null_date;

        $id_shop = (int)Validate::isLoadedObject($this->context->shop) ? $this->context->shop->id : 1;

        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $reference = Order::generateReference();

            $order->reference = $reference;

//            $order->total_paid_real = 0;
            $order->total_discounts_tax_incl = $order->total_discounts;
            $order->total_discounts_tax_excl = (float)$mp_cart->marketplaceGetOrderTotal(false, 9);
            ;

            $order->total_paid_tax_excl = (float)$mp_cart->marketplaceGetOrderTotal(false, 3);
            $order->total_paid_tax_incl = (float)$mp_cart->marketplaceGetOrderTotal(true, 3);

            $order->total_shipping_tax_excl = (float)$mp_cart->marketplaceGetOrderTotal(false, 5);
            $order->total_shipping_tax_incl = (float)$mp_cart->marketplaceGetOrderTotal(true, 5);

            $order->current_state = (int)$id_order_state;
            $id_warehouse = (int)Configuration::get('FEEDBIZ_WAREHOUSE');

            if ($id_shop) {
                $shop = new Shop($id_shop);
                $order->id_shop = $shop->id;
                $order->id_shop_group = $shop->id_shop_group;
            } else {
                $order->id_shop = 1;
                $order->id_shop_group = 1;
            }
        } else {
            $order->id_shop = 1;
            $id_warehouse = 0;
        }

        $currency = new Currency($order->id_currency);
        $order->conversion_rate = $currency->conversion_rate;

        if (!$order->total_products) {
//            ob_start();
//            echo "Order : \n";
//            var_dump($mp_cart->mp_products);
//            var_dump($order);
//            $dump = ob_get_clean();
            echo $this->l('Unable to import an empty order...')."\n";//.$dump."\n";

            return (false);
        }

        $products = array();
        foreach ($mp_cart->mp_products as $mp_product) {
            $id_product = $mp_product['id_product'];
            $id_product_attribute = $mp_product['id_product_attribute'];
            $sku = (string) $mp_product['sku'];
            $name = !empty($mp_product['name']) ? (string) $mp_product['name'] : '';
            if (!($product = $mp_cart->getProducts(true, $id_product))) {
                echo Tools::displayError('Unable to get product from cart.');
                return (false);
            }

            if (is_array($product)) {
                foreach ($product as $p) {
                    $product_attribute_id = $p['id_product_attribute'];
                    if ($product_attribute_id == $id_product_attribute) {
                        if (empty($p['name']) && !empty($name)) {
                            $p['name'] = $name;
                        }
                        $products[$sku.'-'.$id_product.'-'.$id_product_attribute] = $p;
                    }
                }
            } else {
                if (empty($product['name']) && !empty($name)) {
                    $product['name'] = $name;
                }
                $products[$sku.'-'.$id_product.'-'.$id_product_attribute] = $product;
            }

            if (!empty($fb_product_name[$sku]) && empty($product['name'])) {
                $product['name'] = $fb_product_name[$sku];
            }
        }

        if (Feedbiz::$debug_mode) {
            echo "<pre>products(FN:".__FUNCTION__." LINE:".__LINE__."): \n";
            echo print_r($products, true)."\n";
            echo "</pre>\n";
        }

        // Prevent to import duplicate order
        usleep(rand(100, 1000));

        if (!$force_import) {
            $existingOrder = FeedBizOrder::checkByMpId($marketplace_order_id);

            if (is_array($existingOrder) && count($existingOrder)) {
                echo Tools::displayError(__FILE__.'/'.__LINE__.':Order already imported.'.print_r($existingOrder, true));
                return (false);
            }
        }

        if (Validate::isDate($order->date_add)) {
            $autodate = false;
        } else {
            $autodate = true;
        }

        if (!$order->validateFields(false, false)) {
            if (Feedbiz::$debug_mode) {
                echo "<pre>Order validation fields1 (FN:".__FUNCTION__." LINE:".__LINE__."): \n";
                echo print_r(array($order->validateFields(false, false),$order), true)."\n";
                echo "</pre>\n";
            }
            echo Tools::displayError('Validation Failed.');
            return (false);
        }

        $res = $order->add($autodate);
        if (Feedbiz::$debug_mode) {
            echo "<pre>Order validation fields2 (FN:".__FUNCTION__." LINE:".__LINE__."): \n";
            echo print_r(array($order->validateFields(false, true)), true)."\n";
            echo "Order isLoadedObject (FN:".__FUNCTION__." LINE:".__LINE__."): \n";
            echo print_r(array(Validate::isLoadedObject($order) ), true)."\n";
            var_dump($order);
            if (isset($order->valid)) {
                echo "Order valid (FN:".__FUNCTION__." LINE:".__LINE__."): \n";
                var_dump($order->valid);
            }
            echo "Order Add result (FN:".__FUNCTION__." LINE:".__LINE__."): \n";
            var_dump($res);
            echo "Order Add sql error msg (FN:".__FUNCTION__." LINE:".__LINE__."): \n";
            $msg = Db::getInstance()->getMsgError();
            var_dump($msg);
            echo "</pre>\n";
        }
        if (Validate::isLoadedObject($order)) {
            if (Feedbiz::$debug_mode) {
                echo "<pre>Order validated pass (FN:".__FUNCTION__." LINE:".__LINE__."): \n";
            }
            foreach ($products as $product) {
                $id_product = (int)$product['id_product'];
                $id_product_attribute = $product['id_product_attribute'] ? (int)$product['id_product_attribute'] : null;

                if (version_compare(_PS_VERSION_, '1.5', '<')) {
                    $quantityInStock = $productQuantity = (int)Product::getQuantity($id_product, $id_product_attribute);
                } else {
                    //cut stock for $quantityInStock only if user enabled Advanced Stock Management.
                    $productQuantity = Product::getRealQuantity(
                        $id_product,
                        $id_product_attribute,
                        $id_warehouse ? $id_warehouse : null,
                        $order->id_shop
                    );
                    $quantityInStock = Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')
                        && StockAvailable::dependsOnStock($id_product) == 1 ?
                        $productQuantity - $product['cart_quantity'] : $productQuantity;
                }

                $quantity = (int)$product['cart_quantity'];
                $product_identifier = sprintf('%d_%d', $id_product, $id_product_attribute);
                $unitprice = Tools::ps_round($mp_cart->mp_products[$product_identifier]['price'], 4);

                if (!$unitprice) {
                    ob_start();
                    echo 'ID Product : '.$id_product.'\n';
                    var_dump($mp_cart->mp_products);
                    $dump = ob_get_clean();
                    echo $this->l('Product price is zero or null...')."\n".$dump;

                    return (false);
                }

                // default taxes informations
                $product['id_tax'] = 0;
                $product['tax'] = null;
                $product['rate'] = null;

                $id_tax_rules_group = 0;
                $tax_rate = 0;

                // Include VAT (Prestashop 1.5);
                if (!Tax::excludeTaxeOption() || $use_taxes == true) {
                    if (isset($mp_cart->mp_products[$product_identifier]['tax_rate'])
                        && $mp_cart->mp_products[$product_identifier]['tax_rate']) {
                        $id_tax_rules_group = $mp_cart->mp_products[$product_identifier]['id_tax_rules_group'];

                        $address_delivery = new Address($order->id_address_delivery);

                        if (Validate::isLoadedObject($address_delivery)) {
                            $id_tax = $this->getIdTax($address_delivery->id_country, $id_tax_rules_group);

                            if ($id_tax) {
                                $tax_rate = $mp_cart->mp_products[$product_identifier]['tax_rate'];
                                $product['id_tax'] = $mp_cart->mp_products[$product_identifier]['id_tax'];
                                $product['rate'] = $mp_cart->mp_products[$product_identifier]['tax_rate'];
                            }
                        }
                    }
                }

                $unit_price_tax_incl = (float)$unitprice;
                $unit_price_tax_excl = (float)Tools::ps_round($unit_price_tax_incl / (1 + ($tax_rate / 100)), 4);

                $total_price_tax_incl = (float)Tools::ps_round($unit_price_tax_incl, 4) * $quantity;
                $total_price_tax_excl = (float)Tools::ps_round($unit_price_tax_excl, 4) * $quantity;

                $product_name = $product['name'].((isset($product['attributes']) && $product['attributes'] != null) ?
                        ' - '.$product['attributes'] : '');

                //
                // Order Detail entry
                //
                $order_detail = new OrderDetail(null, null, $this->context);

                // order details
                $order_detail->id_order = (int)$order->id;

                // product informations
                $order_detail->product_name = pSQL($product_name);
                $order_detail->product_id = $id_product;
                $order_detail->product_attribute_id = $id_product_attribute;

                // quantities
                $order_detail->product_quantity = (int)$product['cart_quantity'];
                $order_detail->product_quantity_in_stock = (int)$quantityInStock;

                // product references
                $order_detail->product_price = (float)$unit_price_tax_excl;
                $order_detail->product_ean13 = $product['ean13'] ? $product['ean13'] : null;
                $order_detail->product_reference = $product['reference'];
                $order_detail->product_supplier_reference = $product['supplier_reference'] ?
                    $product['supplier_reference'] : null;
                $order_detail->product_weight = (float)Tools::ps_round(
                    $product['id_product_attribute'] ? $product['weight_attribute'] : $product['weight'],
                    2
                );

                // taxes
                $order_detail->tax_name = $product['tax']; // deprecated
                $order_detail->tax_rate = (float)$tax_rate;
                $order_detail->id_tax_rules_group = (int)$id_tax_rules_group;
                $order_detail->ecotax = $product['ecotax'];

                // For PS 1.4
                $order_detail->download_deadline = $null_date;

                // For PS 1.5+
                // price details
                $order_detail->total_price_tax_incl = (float)$total_price_tax_incl;
                $order_detail->total_price_tax_excl = (float)$total_price_tax_excl;
                $order_detail->unit_price_tax_incl = (float)$unit_price_tax_incl;
                $order_detail->unit_price_tax_excl = (float)$unit_price_tax_excl;
                $order_detail->tax_computation_method = $mp_cart->marketplaceCalculationMethod();

                $order_detail->original_product_price = (float)$unit_price_tax_excl;
                $order_detail->purchase_supplier_price = isset($product['wholesale_price']) ?
                    Tools::ps_round((float)$product['wholesale_price'], 4) : 0;

                // shop and warehouse
                if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                    $order_detail->id_shop = (int)$this->context->shop->id;
                    $order_detail->id_warehouse = (int)$id_warehouse;
                }

                if (Feedbiz::$debug_mode) {
                    echo "<pre>order_detail(FN:".__FUNCTION__." LINE:".__LINE__."): \n";
                    //echo print_r($order_detail, true)."\n";
                    echo "</pre>\n";
                }

                // add into db
                if (version_compare(_PS_VERSION_, '1.5', '<')) {
                    $order_detail->add();

                    if (!Validate::isLoadedObject($order_detail)) {
                        print Tools::displayError('OrderDetail::add() - Failed');
                        die;
                    }
                } else {
                    try {
                        $order_detail->add();
                    } catch (Exception $e) {
                        echo 'Caught order detail adding exception: ',  $e->getMessage(), "\n";
                        print_r($order_detail);
                        var_dump($order_detail);
                        die;
                    }

                    if (!Validate::isLoadedObject($order_detail)) {
                        print Tools::displayError('OrderDetail::add() - Failed');
                        die;
                    }

                    $id_order_detail = $order_detail->id;

                    if ($tax_rate) {
                        $address_delivery = new Address($order->id_address_delivery);

                        if (Validate::isLoadedObject($address_delivery)) {
                            $id_tax = $this->getIdTax($address_delivery->id_country, $id_tax_rules_group);

                            $values = sprintf(
                                '(%d, %d, %f, %f) ;',
                                (int)$id_order_detail,
                                (int)$id_tax,
                                (float)$unit_price_tax_excl,
                                (float)$unit_price_tax_incl - $unit_price_tax_excl
                            );

                            $tax_query = 'INSERT INTO `'._DB_PREFIX_.'order_detail_tax`
                            (id_order_detail, id_tax, unit_amount, total_amount)
                            VALUES '.$values;

                            if (!($tax_result = Db::getInstance()->execute($tax_query))) {
                                echo nl2br(print_r($tax_query, true));
                                print Tools::displayError('Failed to add tax details.');
                            }

                            if (Feedbiz::$debug_mode) {
                                echo "<pre>Tax Query:\n";
                                echo $tax_query."\n";
                                echo "Result:".(!$tax_result ? 'Failed' : 'OK')."\n";
                                echo "</pre>\n";
                            }
                        }
                    }
                }

                if (!Validate::isLoadedObject($order_detail)) {
                    print Tools::displayError('OrderDetail::add() - Failed');
                    die;
                }
            } // end foreach ($products)

            if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                // Adding an entry in order_carrier table
                if ($order->id_carrier) {
                    $order_carrier = new OrderCarrier();
                    $order_carrier->id_order = (int)$order->id;
                    $order_carrier->id_carrier = $order->id_carrier;
                    $order_carrier->weight = (float)$order->getTotalWeight();
                    $order_carrier->shipping_cost_tax_excl = $order->total_shipping_tax_excl;
                    $order_carrier->shipping_cost_tax_incl = $order->total_shipping_tax_incl;
                    $order_carrier->add();
                }
            }

            $orderStatus = new OrderState((int)$id_order_state);

            if (Validate::isLoadedObject($orderStatus)) {
                foreach ($mp_cart->getProducts(true) as $product) {
                    if ($orderStatus->logable) {
                        ProductSale::addProductSale((int)$product['id_product'], (int)$product['cart_quantity']);
                    }
                }
            }

            // Order is reloaded because the status just changed
            // @see class PaymentModule.php
            $order = new Order($order->id);

            if (!Validate::isLoadedObject($order)) {
                echo Tools::displayError(sprintf(
                    '%s(#%d): %s',
                    basename(__FILE__),
                    __LINE__,
                    'Order creation failed.'
                ));

                return (false);
            }
            $id_employee = (int)Configuration::get('FEEDBIZ_ID_EMPLOYEE');

            $new_history = new FeedBizOrderHistory();
            $new_history->id_order = (int)$order->id;
            $new_history->id_employee = $id_employee ? $id_employee : 1;
            $new_history->changeIdOrderState($id_order_state, $order->id);
            $new_history->addWithOutEmail(true);

            // updates stock in shops
            if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
                foreach ($products as $product) {
                    if (StockAvailable::dependsOnStock((int)$product['id_product'])) {
                        StockAvailable::synchronize((int)$product['id_product'], $order->id_shop);
                    }
                }
            }

            $this->currentOrder = (int)$order->id;

            return $this->currentOrder;
        } else {
            if (Feedbiz::$debug_mode) {
                echo "<pre>Order validated fail (FN:".__FUNCTION__." LINE:".__LINE__."): \n";
            }
            echo Tools::displayError(sprintf('%s(#%d): %s', basename(__FILE__), __LINE__, 'Order creation failed.'));

            return (false);
        }
    }

    /**
     * @param $id_country
     * @param $id_tax_rules_group
     *
     * @return false|null|string
     */
    public function getIdTax($id_country, $id_tax_rules_group)
    {
        static $id_taxes = array();

        if (array_key_exists($id_country, $id_taxes)) {
            return($id_taxes[$id_country]);
        }

        $sql = 'SELECT `id_tax` FROM `'._DB_PREFIX_.'tax_rule` WHERE `id_tax_rules_group`= '.
            (int)$id_tax_rules_group.' AND `id_country`= '.(int)$id_country;

        $id_tax = Db::getInstance()->getValue($sql);

        if (Feedbiz::$debug_mode) {
            echo "<pre>getIdTax:\n";
            print_r($id_tax);
            echo "</pre>\n";
        }
        $id_taxes[$id_country] = (int)$id_tax;

        return($id_tax);
    }
}

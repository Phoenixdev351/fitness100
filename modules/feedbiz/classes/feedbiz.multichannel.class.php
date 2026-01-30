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

require_once(dirname(__FILE__).'/../classes/feedbiz.order.class.php');

/**
 * Class FeedBizMultichannel
 */
class FeedBizMultichannel
{
    /**
     * @param $id_order
     * @param $id_shop
     * @param bool|false $debug
     * @return DOMDocument|bool
     */
    public function generateOrder($id_order, $id_shop, $debug = false)
    {
        $existingOrder = FeedBizOrder::checkByOrderId($id_order, $debug);
        if (is_array($existingOrder) && count($existingOrder)) {
            if ($debug) {
                echo Tools::displayError(sprintf(
                    '%s: %s::%s(%d) - Order from Feed.biz, %s',
                    basename(__FILE__),
                    __CLASS__,
                    __FUNCTION__,
                    __LINE__,
                    print_r($existingOrder, true)
                ));
            }

            return (false);
        }

        $feedbiz_amazon_features = Configuration::get('FEEDBIZ_AMAZON_FEATURES');
        if (!isset($feedbiz_amazon_features) || empty($feedbiz_amazon_features) || $feedbiz_amazon_features == '') {
            if ($debug) {
                echo Tools::displayError(sprintf(
                    '%s: %s::%s(%d) - Empty: Amazon Features',
                    basename(__FILE__),
                    __CLASS__,
                    __FUNCTION__,
                    __LINE__
                ));
            }

            return (false);
        }

        $amazon_features = unserialize($feedbiz_amazon_features);
        if (!$amazon_features instanceof stdClass) {
            if ($debug) {
                echo Tools::displayError(sprintf(
                    '%s: %s::%s(%d) - Unavailable: Amazon Features',
                    basename(__FILE__),
                    __CLASS__,
                    __FUNCTION__,
                    __LINE__
                ));
            }

            return (false);
        }

        if (!(isset($amazon_features->has_fba) && property_exists($amazon_features, 'has_fba') && $amazon_features->has_fba == true
            && isset($amazon_features->has_fba_multichannel) && property_exists($amazon_features, 'has_fba_multichannel')
            && $amazon_features->has_fba_multichannel == true)) {
            if ($debug) {
                echo Tools::displayError(sprintf(
                    '%s: %s::%s(%d) - FBA Multichannel is not active',
                    basename(__FILE__),
                    __CLASS__,
                    __FUNCTION__,
                    __LINE__
                ));
            }

            return (false);
        }

        $order = new Order($id_order);

        if (!Validate::isLoadedObject($order)) {
            if ($debug) {
                echo Tools::displayError(sprintf(
                    '%s: %s::%s(%d) - Unable to load Order id: %d',
                    basename(__FILE__),
                    __CLASS__,
                    __FUNCTION__,
                    __LINE__,
                    $id_order
                ));
            }

            return (false);
        }

        // check order not from module : Feedbiz
        if (isset($order->module) && $order->module == 'feedbiz') {
            if ($debug) {
                $payment = isset($order->payment) ? ', ' . $order->payment : '';
                echo Tools::displayError(sprintf(
                    '%s: %s::%s(%d) - Order from Feed.biz, %s%s',
                    basename(__FILE__),
                    __CLASS__,
                    __FUNCTION__,
                    __LINE__,
                    print_r($id_order, true),
                    $payment
                ));
            }
            return (false);
        }
        $ordered_products = $order->getProductsDetail();

        if (!is_array($ordered_products) || !count($ordered_products)) {
            if ($debug) {
                echo Tools::displayError(sprintf(
                    '%s: %s::%s(%d) - No products belonging to this order: %d',
                    basename(__FILE__),
                    __CLASS__,
                    __FUNCTION__,
                    __LINE__,
                    $id_order
                ));
            }

            return (false);
        }
        $pass = true;

        foreach ($ordered_products as $key => $product) {
            if (empty($product['product_reference'])) {
                $pass = false;
            }
        }

        if (!$pass) {
            if ($debug) {
                echo Tools::displayError(sprintf(
                    '%s: %s::%s(%d) - One or more products are not eligible for multichannel because of a missing reference: %d',
                    basename(__FILE__),
                    __CLASS__,
                    __FUNCTION__,
                    __LINE__,
                    $id_order
                ));
            }

            return (false);
        }

        $required_ids = array(
            'id_customer',
            'id_address_delivery',
            'id_address_invoice',
            'id_currency',
            'id_lang',
            'id_carrier'
        );
        foreach ($required_ids as $required_id) {
            if (empty($order->{$required_id})) {
                if ($debug) {
                    echo Tools::displayError(sprintf(
                        '%s: %s::%s(%d) - Invalid value for %s: "%s"',
                        basename(__FILE__),
                        __CLASS__,
                        __FUNCTION__,
                        __LINE__,
                        $required_id,
                        $order->{$required_id}
                    ));
                }

                return (false);
            }
        }

        $customer = new Customer($order->id_customer);
        $delivery_address = new Address($order->id_address_delivery);
        $invoice_address = new Address($order->id_address_invoice);
        $currency = new Currency($order->id_currency);
        $language = new Language($order->id_lang);
        $carrier = new Carrier($order->id_carrier);

        $required_classes = array(
            'customer',
            'delivery_address',
            'invoice_address',
            'currency',
            'language',
            'carrier'
        );
        foreach ($required_classes as $required_class) {
            if (!Validate::isLoadedObject($$required_class)) {
                if ($debug) {
                    echo Tools::displayError(sprintf(
                        '%s: %s::%s(%d) - Unable to load instance "%s", validation failed',
                        basename(__FILE__),
                        __CLASS__,
                        __FUNCTION__,
                        __LINE__,
                        $required_class
                    ));
                }

                return (false);
            }
        }

        $Document = new DOMDocument();
        $Document->preserveWhiteSpace = true;
        $Document->formatOutput = true;

        $Document->encoding = 'utf-8';
        $Document->version = '1.0';

        $OrderDom = $Document->createElement('Order');
        $OrderDom->setAttribute('ShopName', Configuration::get('PS_SHOP_NAME', null, null, $id_shop));
        $OrderDom->setAttribute('IdShop', $id_shop);
        $OrderDom->setAttribute('Created', $order->date_add);
        $OrderDom->setAttribute('Updated', $order->date_upd);
        $OrderDom->setAttribute('Rate', (float)$order->conversion_rate);
        $OrderDom->setAttribute('Recyclable', (bool)$order->recyclable);
        $OrderDom->setAttribute('CurrencyId', (int)$currency->id);
        $OrderDom->setAttribute('CurrencyCode', htmlentities($currency->iso_code));
        $OrderDom->setAttribute('CurrencyName', htmlentities($currency->name));
        $OrderDom->setAttribute('CurrencyRate', (float)$currency->conversion_rate);
        $OrderDom->setAttribute('LanguageId', (int)$language->id);
        $OrderDom->setAttribute('LanguageName', htmlentities($language->name));
        $OrderDom->setAttribute('LanguageCode', htmlentities($language->iso_code));

        $OrderReferences = $Document->createElement('References');
        $OrderReferences->appendChild($Document->createElement('InvoiceNumber', htmlentities($order->invoice_number)));
        $OrderReferences->appendChild($Document->createElement('Reference', htmlentities($order->reference)));
        $OrderReferences->appendChild($Document->createElement('Id', (int)$order->id));
        $OrderDom->appendChild($OrderReferences);

        $OrderTotal = $Document->createElement('Total');
        $OrderInfo = $Document->createElement('Info');
        $OrderTotalPaid = $Document->createElement('Paid');
        $OrderTotalProducts = $Document->createElement('Products');
        $OrderTotalDiscount = $Document->createElement('Discount');
        $OrderTotalWrapping = $Document->createElement('Shipping');
        $OrderTotalShipping = $Document->createElement('Wrapping');

        $Document->appendChild($OrderDom);
        $OrderDom->appendChild($OrderTotal);
        $OrderDom->appendChild($OrderInfo);
        $OrderTotal->appendChild($OrderTotalPaid);
        $OrderTotal->appendChild($OrderTotalProducts);
        $OrderTotal->appendChild($OrderTotalDiscount);
        $OrderTotal->appendChild($OrderTotalShipping);
        $OrderTotal->appendChild($OrderTotalWrapping);

        $CarrierInfo = $Document->createElement('Carrier');
        $CarrierInfo->setAttribute('id', (int)$carrier->id);
        $CarrierInfo->setAttribute('active', (bool)$carrier->active);
        $CarrierInfo->setAttribute('deleted', (bool)$carrier->deleted);
        $CarrierInfo->setAttribute('module', (bool)$carrier->is_module);
        $CarrierInfo->setAttribute('is_free', (bool)$carrier->is_free);
        $CarrierInfo->setAttribute('max_width', (bool)$carrier->max_width);
        $CarrierInfo->setAttribute('max_height', (bool)$carrier->max_height);
        $CarrierInfo->setAttribute('max_depth', (bool)$carrier->max_depth);
        $CarrierInfo->setAttribute('max_weight', (bool)$carrier->max_weight);
        $CarrierInfoData = $CarrierInfo->appendChild($Document->createElement('Name'));
        $CarrierInfoData->appendChild($Document->createCDATASection($carrier->name));
        $CarrierInfoData = $CarrierInfo->appendChild($Document->createElement('Module'));
        $CarrierInfoData->appendChild($Document->createCDATASection($carrier->external_module_name));
        $CarrierInfoData = $CarrierInfo->appendChild($Document->createElement('Url'));
        $CarrierInfoData->appendChild($Document->createCDATASection($carrier->url));
        $OrderDom->appendChild($CarrierInfo);

        $OrderInfoPayment = $Document->createElement('Payment');
        $OrderInfoPayment->appendChild($Document->createCDATASection($order->payment));
        $OrderInfo->appendChild($OrderInfoPayment);

        $OrderInfoModule = $Document->createElement('Module');
        $OrderInfoModule->appendChild($Document->createCDATASection($order->module));
        $OrderInfo->appendChild($OrderInfoModule);

        $OrderInfoGift = $Document->createElement('Gift');
        $OrderInfoGift->setAttribute('value', (bool)$order->gift ? 'Yes' : 'No');
        $OrderInfoGiftMessage = $OrderInfoGift->appendChild($Document->createElement('Message'));
        if ((bool)$order->gift) {
            $OrderInfoGiftMessage->appendChild($Document->createCDATASection($order->gift_message));
        }
        $OrderInfo->appendChild($OrderInfoGift);

        // Order Total
        //
        $OrderTotalPaid->appendChild($Document->createElement('Amount', sprintf('%.04f', (float)$order->total_paid)));
        $OrderTotalPaid->appendChild($Document->createElement('TaxIncl', sprintf('%.04f', (float)$order->total_paid_tax_incl)));
        $OrderTotalPaid->appendChild($Document->createElement('TaxExcl', sprintf('%.04f', (float)$order->total_paid_tax_excl)));
        $OrderTotalPaid->appendChild($Document->createElement('Real', sprintf('%.04f', (float)$order->total_paid_real)));

        $OrderTotalProducts->appendChild($Document->createElement('Amount', sprintf('%.04f', (float)$order->total_products)));
        $OrderTotalProducts->appendChild($Document->createElement('TaxIncl', sprintf('%.04f', (float)$order->total_products_wt)));
        $OrderTotalProducts->appendChild($Document->createElement('TaxExcl', null));

        $OrderTotalDiscount->appendChild($Document->createElement('Amount', sprintf('%.04f', (float)$order->total_discounts)));
        $OrderTotalDiscount->appendChild($Document->createElement('TaxIncl', sprintf('%.04f', (float)$order->total_discounts_tax_incl)));
        $OrderTotalDiscount->appendChild($Document->createElement('TaxExcl', sprintf('%.04f', (float)$order->total_discounts_tax_excl)));

        $OrderTotalShipping->appendChild($Document->createElement('Amount', sprintf('%.04f', (float)$order->total_shipping)));
        $OrderTotalShipping->appendChild($Document->createElement('TaxIncl', sprintf('%.04f', (float)$order->total_shipping_tax_incl)));
        $OrderTotalShipping->appendChild($Document->createElement('TaxExcl', sprintf('%.04f', (float)$order->total_shipping_tax_excl)));

        $OrderTotalShipping->appendChild($Document->createElement('Amount', sprintf('%.04f', (float)$order->total_wrapping)));
        $OrderTotalShipping->appendChild($Document->createElement('TaxIncl', sprintf('%.04f', (float)$order->total_wrapping_tax_incl)));
        $OrderTotalShipping->appendChild($Document->createElement('TaxExcl', sprintf('%.04f', (float)$order->total_wrapping_tax_excl)));

        // Customer's Info
        //
        $CustomerInfo = $OrderDom->appendChild($Document->createElement('Customer'));
        $CustomerInfo->setAttribute('id', (int)$customer->id);
        $CustomerInfo->setAttribute('id_shop', (int)$customer->id_shop);
        $CustomerInfo->setAttribute('id_shop_group', (int)$customer->id_shop_group);
        $CustomerInfo->setAttribute('id_default_group', (int)$customer->id_default_group);
        $CustomerInfo->setAttribute('id_lang', (int)$customer->id_lang);
        $CustomerInfo->setAttribute('optin', (int)$customer->optin);
        $CustomerInfo->setAttribute('active', (int)$customer->active);
        $CustomerInfo->setAttribute('is_guest', (int)$customer->is_guest);

        if ($customer->id_lang) {
            $CustomerInfo->setAttribute('lang', Language::getIsoById($customer->id_lang));
        }

        if ($customer->id_gender) {
            $CustomerInfo->setAttribute('gender', $customer->id_gender);
        }

        foreach (array(
            'firstname',
            'lastname',
            'birthday',
            'email',
            'website',
            'company',
            'siret',
            'ape',
            'date_add',
            'date_upd'
        ) as $field) {
            if (!property_exists($customer, $field)) {
                continue;
            }
            $CustomerInfoField = $CustomerInfo->appendChild($Document->createElement(self::toXmlName($field)));
            if (!empty($customer->{$field}) || is_numeric($customer->{$field})) {
                $CustomerInfoField->appendChild($Document->createCDATASection($customer->{$field}));
            }
        }

        // Delivery Address (Shipping)
        //
        $AddressInfo = $OrderDom->appendChild($Document->createElement('Delivery'));
        $AddressInfo->setAttribute('id', (int)$delivery_address->id);

        if (property_exists($delivery_address, 'id_manufacturer')) {
            $AddressInfo->setAttribute('id_manufacturer', (int)$delivery_address->id_manufacturer);
        }

        if (property_exists($delivery_address, 'id_supplier')) {
            $AddressInfo->setAttribute('id_supplier', (int)$delivery_address->id_supplier);
        }

        if (property_exists($delivery_address, 'id_warehouse')) {
            $AddressInfo->setAttribute('id_warehouse', (int)$delivery_address->id_warehouse);
        }

        $AddressInfo->setAttribute('country', htmlentities(Country::getIsoById((int)$delivery_address->id_country)));
        $AddressInfo->setAttribute('state', htmlentities(State::getNameById((int)$delivery_address->id_state)));
        $AddressInfo->setAttribute('deleted', (int)$delivery_address->deleted);

        foreach (array(
            'alias',
            'company',
            'lastname',
            'firstname',
            'address1',
            'address2',
            'postcode',
            'city',
            'other',
            'phone',
            'phone_mobile',
            'vat_number',
            'dni',
            'date_add',
            'date_upd',
            'deleted'
        ) as $field) {
            if (!property_exists($delivery_address, $field)) {
                continue;
            }
            $AddressInfoField = $AddressInfo->appendChild($Document->createElement(self::toXmlName($field)));
            if (!empty($delivery_address->{$field}) || is_numeric($delivery_address->{$field})) {
                $AddressInfoField->appendChild($Document->createCDATASection($delivery_address->{$field}));
            }
        }

        // Invoice Address (Billing)
        //
        $AddressInfo = $OrderDom->appendChild($Document->createElement('Billing'));
        $AddressInfo->setAttribute('id', (int)$invoice_address->id);

        if (property_exists($delivery_address, 'id_manufacturer')) {
            $AddressInfo->setAttribute('id_manufacturer', (int)$invoice_address->id_manufacturer);
        }

        if (property_exists($delivery_address, 'id_supplier')) {
            $AddressInfo->setAttribute('id_supplier', (int)$invoice_address->id_supplier);
        }

        if (property_exists($delivery_address, 'id_warehouse')) {
            $AddressInfo->setAttribute('id_warehouse', (int)$invoice_address->id_warehouse);
        }

        $AddressInfo->setAttribute('country', htmlentities(Country::getIsoById((int)$invoice_address->id_country)));
        $AddressInfo->setAttribute('state', htmlentities(State::getNameById((int)$invoice_address->id_state)));
        $AddressInfo->setAttribute('deleted', (int)$invoice_address->deleted);

        foreach (array(
            'alias',
            'company',
            'lastname',
            'firstname',
            'address1',
            'address2',
            'postcode',
            'city',
            'other',
            'phone',
            'phone_mobile',
            'vat_number',
            'dni',
            'date_add',
            'date_upd',
            'deleted'
        ) as $field) {
            if (!property_exists($invoice_address, $field)) {
                continue;
            }
            $AddressInfoField = $AddressInfo->appendChild($Document->createElement(self::toXmlName($field)));
            if (!empty($invoice_address->{$field}) || is_numeric($invoice_address->{$field})) {
                $AddressInfoField->appendChild($Document->createCDATASection($invoice_address->{$field}));
            }
        }

        // Extract Products
        //
        $ProductsInfo = $Document->createElement('Products');
        $OrderDom->appendChild($ProductsInfo);

        foreach ($ordered_products as $key => $product) {
            $ProductInfo = $Document->createElement('Product');
            $ProductInfo->setAttribute('index', (int)$key);
            $ProductInfo->setAttribute('id_shop', (int)$product['id_shop']);
            $ProductInfo->setAttribute('id_warehouse', (int)$product['id_warehouse']);
            $ProductInfo->setAttribute('id_order', (int)$product['id_order']);
            $ProductInfo->setAttribute('id_order_detail', (int)$product['id_order_detail']);
            $ProductInfo->setAttribute('condition', htmlentities($product['condition']));

            $ProductReferences = $ProductInfo->appendChild($Document->createElement('References'));
            $ProductReferences->appendChild($Document->createElement('ProductId', (int)$product['product_id']));
            $ProductReferences->appendChild($Document->createElement('ProductAttributeId', (int)$product['product_attribute_id']));

            foreach (array(
                'product_upc',
                'product_ean13',
                'product_name',
                'product_reference',
                'product_supplier_reference',
                'date_add',
                'date_upd'
            ) as $field) {
                if (!array_key_exists($field, $product)) {
                    continue;
                }
                $ProductRef = $ProductReferences->appendChild($Document->createElement(self::toXmlName($field)));
                if (!empty($product[$field]) || is_numeric($product[$field])) {
                    $ProductRef->appendChild($Document->createCDATASection($product[$field]));
                }
            }

            $Availability = $ProductInfo->appendChild($Document->createElement('Availability'));
            $Availability->appendChild($Document->createElement('Available', (int)$product['available_for_order']));
            $Availability->appendChild($Document->createElement('AvailableDate', htmlentities($product['available_date'])));
            $Availability->appendChild($Document->createElement('Active', (int)$product['active']));
            $Availability->appendChild($Document->createElement('Visibility', (int)$product['visibility']));

            $ProductQuantity = $ProductInfo->appendChild($Document->createElement('Quantity'));
            $ProductQuantity->appendChild($Document->createElement('Unity', (int)$product['unity']));
            $ProductQuantity->appendChild($Document->createElement('UnityRatio', (int)$product['unit_price_ratio']));
            $ProductQuantity->appendChild($Document->createElement('Minimal', (int)$product['minimal_quantity']));
            $ProductQuantity->appendChild($Document->createElement('Ordered', (int)$product['product_quantity']));
            $ProductQuantity->appendChild($Document->createElement('InStock', (int)$product['product_quantity_in_stock']));
            $ProductQuantity->appendChild($Document->createElement('Refunded', (int)$product['product_quantity_refunded']));
            $ProductQuantity->appendChild($Document->createElement('Reinjected', (int)$product['product_quantity_reinjected']));

            $ProductPrice = $ProductInfo->appendChild($Document->createElement('Price'));
            $ProductPrice->appendChild($Document->createElement('Price', sprintf('%.04f', (float)$product['product_price'])));
            $ProductPrice->appendChild($Document->createElement('OriginalPrice', sprintf('%.04f', (float)$product['original_product_price'])));
            $ProductPrice->appendChild($Document->createElement('TotalTaxIncl', sprintf('%.04f', (float)$product['total_price_tax_incl'])));
            $ProductPrice->appendChild($Document->createElement('TotalTaxExcl', sprintf('%.04f', (float)$product['total_price_tax_excl'])));
            $ProductPrice->appendChild($Document->createElement('UnitTaxIncl', sprintf('%.04f', (float)$product['unit_price_tax_incl'])));
            $ProductPrice->appendChild($Document->createElement('UnitTaxExcl', sprintf('%.04f', (float)$product['unit_price_tax_excl'])));
            $ProductPrice->appendChild($Document->createElement('Wholesale', sprintf('%.04f', (float)$product['wholesale_price'])));

            $ShippingPrice = $ProductInfo->appendChild($Document->createElement('Shipping'));
            $ShippingPrice->appendChild($Document->createElement('Price', sprintf('%.04f', (float)$product['product_price'])));
            $ShippingPrice->appendChild($Document->createElement('TotalTaxIncl', sprintf('%.04f', (float)$product['total_shipping_price_tax_incl'])));
            $ShippingPrice->appendChild($Document->createElement('TotalTaxExcl', sprintf('%.04f', (float)$product['total_shipping_price_tax_excl'])));
            $ShippingPrice->appendChild($Document->createElement('Additionnal', sprintf('%.04f', (float)$product['additional_shipping_cost'])));

            $Taxes = $ProductInfo->appendChild($Document->createElement('Tax'));
            $Taxes->appendChild($Document->createElement('Name', htmlentities($product['tax_name'])));
            $Taxes->appendChild($Document->createElement('Rate', sprintf('%.04f', (float)$product['tax_rate'])));
            $Taxes->appendChild($Document->createElement('EcoTax', sprintf('%.04f', (float)$product['ecotax'])));
            $Taxes->appendChild($Document->createElement('EcoTaxRate', sprintf('%.04f', (float)$product['ecotax_tax_rate'])));

            $Discount = $ProductInfo->appendChild($Document->createElement('Discount'));
            $Discount->appendChild($Document->createElement('DiscountQtyApplied', (int)$product['discount_quantity_applied']));
            $Discount->appendChild($Document->createElement('Quantity', (int)$product['quantity_discount']));
            $Discount->appendChild($Document->createElement('OnSale', (bool)$product['on_sale']));
            $Discount->appendChild($Document->createElement('Percent', sprintf('%.04f', (float)$product['reduction_percent'])));
            $Discount->appendChild($Document->createElement('Amount', sprintf('%.04f', (float)$product['reduction_amount'])));
            $Discount->appendChild($Document->createElement('AmountTaxIncl', sprintf('%.04f', (float)$product['reduction_amount_tax_incl'])));
            $Discount->appendChild($Document->createElement('AmountTaxExcl', sprintf('%.04f', (float)$product['reduction_amount_tax_excl'])));

            $ProductsInfo->appendChild($ProductInfo);
        }

        if ($debug) {
            echo "<pre>\n";
            echo htmlentities($Document->saveXML());
            echo "</pre>\n";
        }

        return ($Document);
    }

    /**
     * @param $field
     * @return mixed
     */
    public static function toXmlName($field)
    {
        return preg_replace('/[^A-Za-z0-9]/', '', ucwords(str_replace('_', ' ', $field)));
    }
}

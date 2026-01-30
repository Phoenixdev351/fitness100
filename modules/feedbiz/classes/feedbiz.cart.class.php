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

class FeedBizCart extends Cart
{
    /**
     * @var null
     */
    public $mp_products = null;
    /**
     * @var int
     */
    public $mp_shipping = 0;
    /**
     * @var int
     */
    public $mp_shipping_excl = 0;
    /**
     * @var int
     */
    public $mp_fees     = 0;
    /**
     * @var null
     */
    public $mp_date     = null;

    /**
     * @var int
     */
    public $taxCalculationMethod = PS_TAX_INC ;

    /**
     * This function returns the total cart amount
     *
     * type = 1 : only products
     * type = 2 : only discounts
     * type = 3 : both
     * type = 4 : both but without shipping
     * type = 5 : only shipping
     * type = 6 : only wrapping
     * type = 7 : only products without shipping
     *
     * @param boolean $withTaxes With or without taxes
     * @param integer $type Total type
     * @return float Order total
     */
    public function marketplaceGetOrderTotal($withTaxes = true, $type = 3)
    {
        $type = (int)$type;
        if (!in_array($type, array(1, 2, 3, 4, 5, 6, 7, 8,9))) {
            die(Tools::displayError('no type specified'));
        }

        $this->marketplaceCalculationMethod();

        $total_price_tax_incl = 0;
        $total_price_tax_excl = 0;
        $total_discount_tax_incl = 0;
        $total_discount_tax_excl = 0;
        $carrier_tax_rate = $this->marketplaceGetCarrierTaxRate();
        $product_tax_rate=0;
        foreach ($this->mp_products as $product) {
            $product_tax_rate = $this->marketplaceGetTaxRate($product);

            $unit_price_tax_incl = (float)Tools::ps_round($product['price'], 2);
            $unit_price_tax_excl = (float)Tools::ps_round($product['price'] / ((100 + $product_tax_rate) / 100), 2);

            $total_price_tax_incl += ($unit_price_tax_incl * $product['qty']);
            $total_price_tax_excl += ($unit_price_tax_excl * $product['qty']);
        }

        $wrapping_fees_withtaxes = (float)Tools::ps_round($this->gift_wrap, 2);
        $wrapping_fees_wot = (float)Tools::ps_round(($this->gift_wrap / ((100 + $product_tax_rate) / 100)), 2);

        $total_shipping_tax_incl = (float)Tools::ps_round($this->mp_shipping, 2);
        $total_shipping_tax_excl = (float)Tools::ps_round(($this->mp_shipping / ((100 + $carrier_tax_rate) / 100)), 2);

        if (empty($total_shipping_tax_excl) && !empty($this->mp_shipping_excl)) {
            $total_shipping_tax_excl = (float)Tools::ps_round($this->mp_shipping_excl, 2);
        }
        if (empty($total_discount_tax_incl) && !empty($this->subtotal_discount)) {
            $total_discount_tax_incl = $this->subtotal_discount;
            $total_discount_tax_excl = ($this->subtotal_discount / ((100 + $product_tax_rate) / 100));
        }

        if (Feedbiz::$debug_mode) {
            echo "<pre>Discount:\n";
            printf('$this->subtotal_discount: %s'."\n", $this->subtotal_discount);

            printf('$total_discount_tax_incl: %s'."\n", $total_discount_tax_incl);
            printf('$total_discount_tax_excl: %s'."\n", $total_discount_tax_excl);
            echo "</pre>\n";
        }

        switch ($type) {
            case 1:
            case 8:
                $amount = ($withTaxes ? $total_price_tax_incl : $total_price_tax_excl);
                break;
            case 9:
                $amount = ($withTaxes ? $total_discount_tax_incl : $total_discount_tax_excl);

                break;
            case 3:
                // Modif YB : mise en place de TVA sur les frais d'emballage
                // $amount = ($withTaxes ?
                //     $total_price_tax_incl : $total_price_tax_excl) + $this->mp_shipping + $this->mp_fees ;
                $amount = $withTaxes ?
                    ($total_price_tax_incl + $wrapping_fees_withtaxes + $total_shipping_tax_incl) - $total_discount_tax_incl :
                    ($total_price_tax_excl + $wrapping_fees_wot + $total_shipping_tax_excl - $total_discount_tax_excl);

                break;
            case 2:
                return (0);
            case 4:
                $amount = $withTaxes ? $total_price_tax_incl : $total_price_tax_excl;
                break;
            case 5:
                $amount = $withTaxes ? $total_shipping_tax_incl : $total_shipping_tax_excl;
                break;
            case 6:
                $amount = $withTaxes ? $wrapping_fees_withtaxes : $wrapping_fees_wot;
                break;
            case 7:
                $amount = $withTaxes ? $total_price_tax_incl : $total_price_tax_excl;
                break;
            default:
                $amount = 0;
        }
        return Tools::ps_round($amount, 2);
    }

    /**
     * @return int
     */
    public function marketplaceCalculationMethod()
    {
        if ($this->id_customer) {
            $customer = new Customer((int)($this->id_customer));
            $this->taxCalculationMethod = !Group::getPriceDisplayMethod((int)($customer->id_default_group));
        } else {
            $this->taxCalculationMethod = !Group::getDefaultPriceDisplayMethod();
        }

        if (Feedbiz::$debug_mode) {
            echo "<pre>marketplaceCalculationMethod:\n";
            printf('id_customer: %d'."\n", $this->id_customer);
            printf('taxCalculationMethod: %s'."\n", $this->taxCalculationMethod);
            echo "</pre>\n";
        }
        return((int)$this->taxCalculationMethod);
    }


    /**
     * @return float|int
     */
    public function marketplaceGetCarrierTaxRate()
    {
        $carrier_tax_rate = 0;
        $pass = true;

        if (!$this->id_carrier) {
            $pass = false;
        }

        $address_type = Configuration::get('PS_TAX_ADDRESS_TYPE');

        if (empty($address_type)) {
            $address_type = 'id_address_delivery';
        }

        $address = new Address($this->{$address_type});

        if (!Validate::isLoadedObject($address)) {
            $pass = false;
        }

        if ($pass && $this->taxCalculationMethod) {
            // Carrier Taxes
            //
            if (method_exists('Carrier', 'getTaxesRate')) {
                $carrier = new Carrier($this->id_carrier);

                if (Validate::isLoadedObject($carrier)) {
                    $carrier_tax_rate = (float)$carrier->getTaxesRate($address);
                }
            } elseif (method_exists('Tax', 'getCarrierTaxRate')) {
                $carrier_tax_rate = (float)Tax::getCarrierTaxRate($this->id_carrier, (int)$address->id);
            }
        }

        if (Feedbiz::$debug_mode) {
            echo "<pre>marketplaceGetCarrierTaxRate:\n";
            printf('taxCalculationMethod: %s'."\n", $this->taxCalculationMethod);
            printf('id_carrier: %d'."\n", $this->id_carrier);
            printf('address_type: %s'."\n", $address_type);
            printf('id_address: %d'."\n", $address->id);
            printf('carrier_tax_rate: %s'."\n", $carrier_tax_rate);
            echo "</pre>\n";
        }

        return ($carrier_tax_rate);
    }

    /**
     * @param $product
     *
     * @return float|int
     */
    private function marketplaceGetTaxRate($product)
    {
        $product_tax_rate = 0;

        if ($product['is_afn_order']) {
            $product_tax_rate = (float)$product['tax_rate'];
        } elseif ($product['tax_rate']) {
            if ($this->taxCalculationMethod) {
                if (method_exists('Tax', 'getProductTaxRate')) {
                    $product_tax_rate = (float)Tax::getProductTaxRate(
                        (int)$product['id_product'],
                        (int)$product['id_address_delivery']
                    );
                } else {
                    $product_tax_rate = (float)Tax::getApplicableTax(
                        (int)$product['id_tax'],
                        $product['tax_rate'],
                        (int)$product['id_address_delivery']
                    );
                }
            }
        }

        if (Feedbiz::$debug_mode) {
            echo "<pre>marketplaceGetTaxRate:\n";
            printf('taxCalculationMethod: %s'."\n", $this->taxCalculationMethod);
            printf('product/id_product: %d'."\n", $product['id_product']);
            printf('product/id_tax: %d'."\n", $product['id_tax']);
            printf('product/id_address_delivery: %d'."\n", $product['id_address_delivery']);
            printf('product_tax_rate: %s'."\n", $product_tax_rate);
            echo "</pre>\n";
        }

        return ($product_tax_rate);
    }
}

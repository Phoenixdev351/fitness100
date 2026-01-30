<?php
/**
 * 2010-2022 Bl Modules.
 *
 * If you wish to customize this module for your needs,
 * please contact the authors first for more information.
 *
 * It's not allowed selling, reselling or other ways to share
 * this file or any other module files without author permission.
 *
 * @author    Bl Modules
 * @copyright 2010-2022 Bl Modules
 * @license
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class FeedPrice
{
    public function getEditedPrice($priceValue, $priceName, $settings)
    {
        $editSettings = $this->getEditSettings($priceName, $settings);

        if (empty($editSettings['value'])) {
            return $priceValue;
        }

        return $this->editPriceAction($priceValue, $editSettings['type'], $editSettings['value']);
    }

    protected function getEditSettings($priceName, $settings)
    {
        $nameMap = [
            'product_price' => 'price+product',
            'sale_blmod' => 'price_sale_blmod+bl_extra',
            'product_wholesale_price' => 'wholesale_price+product',
            'shipping_price' => 'price_shipping_blmod+bl_extra',
            'price_wt_discount_blmod' => 'price_wt_discount_blmod+bl_extra',
            'sale_tax_excl_blmod' => 'price_sale_tax_excl_blmod+bl_extra',
        ];

        if (empty($nameMap[$priceName])) {
            return [
                'type' => '',
                'value' => 0,
            ];
        }

        $priceName = $nameMap[$priceName];

        if (empty($settings['edit_price_type']) || empty($settings['edit_price_value'])) {
            return [
                'type' => '',
                'value' => 0,
            ];
        }

        if (!empty($settings['edit_price_type'][$priceName]) && !empty($settings['edit_price_value'][$priceName])) {
            return [
                'type' => (int)$settings['edit_price_type'][$priceName],
                'value' => $settings['edit_price_value'][$priceName],
            ];
        }

        return [
            'type' => '',
            'value' => 0,
        ];
    }

    protected function editPriceAction($price, $type, $value)
    {
        switch ($type) {
            case 1:
                return $price + $value;
            case 2:
                return $price - $value;
            case 3:
                return $price * $value;
            case 4:
                return $price / $value;
            case 5:
                return $price * (1 + $value / 100);
            case 6:
                return $price * (1 - $value / 100);
        }

        return $price;
    }
}

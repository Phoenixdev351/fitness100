<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *
 *  INFO: This override is used for better performance on Prices drop page, New products page, native Search results
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class Product extends ProductCore
{
    public static function getPricesDrop(
        $id_lang,
        $page_number = 0,
        $nb_products = 10,
        $count = false,
        $order_by = null,
        $order_way = null,
        $beginning = false,
        $ending = false,
        Context $context = null
    ) {
        if (!$context) {
            $context = Context::getContext();
        }
        if (isset($context->filtered_result) && $context->filtered_result['controller'] == 'pricesdrop') {
            if ($count) {
                return $context->filtered_result['total'];
            }

            return $context->filtered_result['products'];
        } else {
            return parent::getPricesDrop(
                $id_lang,
                $page_number,
                $nb_products,
                $count,
                $order_by,
                $order_way,
                $beginning,
                $ending,
                $context
            );
        }
    }

    public static function getNewProducts(
        $id_lang,
        $page_number = 0,
        $nb_products = 10,
        $count = false,
        $order_by = null,
        $order_way = null,
        Context $context = null
    ) {
        if (!$context) {
            $context = Context::getContext();
        }
        if (isset($context->filtered_result) && $context->filtered_result['controller'] == 'newproducts') {
            if ($count) {
                return $context->filtered_result['total'];
            }

            return $context->filtered_result['products'];
        } else {
            return parent::getNewProducts(
                $id_lang,
                $page_number,
                $nb_products,
                $count,
                $order_by,
                $order_way,
                $context
            );
        }
    }

    /*
    * optimize filtering on search results page
    */
    public static function getProductsProperties($id_lang, $query_result)
    {
        if (!empty(Context::getContext()->properties_not_required)) {
            return $query_result;
        } else {
            return parent::getProductsProperties($id_lang, $query_result);
        }
    }
}

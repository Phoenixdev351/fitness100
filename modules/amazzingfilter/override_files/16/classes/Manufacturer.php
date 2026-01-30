<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *
 *  INFO: This override is used for better performance on Manufacturer pages
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class Manufacturer extends ManufacturerCore
{
    public static function getProducts(
        $id_manufacturer,
        $id_lang,
        $p,
        $n,
        $order_by = null,
        $order_way = null,
        $get_total = false,
        $active = true,
        $active_category = true,
        Context $context = null
    ) {
        if (!$context) {
            $context = Context::getContext();
        }
        if (isset($context->filtered_result) && $context->filtered_result['controller'] == 'manufacturer') {
            if ($get_total) {
                return $context->filtered_result['total'];
            }

            return $context->filtered_result['products'];
        } else {
            return parent::getProducts(
                $id_manufacturer,
                $id_lang,
                $p,
                $n,
                $order_by,
                $order_way,
                $get_total,
                $active,
                $active_category,
                $context
            );
        }
    }
}

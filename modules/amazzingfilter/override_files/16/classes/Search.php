<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *
 *  INFO: This override is used for better performance on native Search results page
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class Search extends SearchCore
{
    public static function find(
        $id_lang,
        $expr,
        $page_number = 1,
        $page_size = 1,
        $order_by = 'position',
        $order_way = 'desc',
        $ajax = false,
        $use_cookie = true,
        Context $context = null
    ) {
        if (!$context) {
            $context = Context::getContext();
        }
        if (isset($context->filtered_result) && $context->filtered_result['controller'] == 'search') {
            $ret = [
                'total' => $context->filtered_result['total'],
                'result' => $context->filtered_result['products'],
            ];

            return $ret;
        } else {
            return parent::find(
                $id_lang,
                $expr,
                $page_number,
                $page_size,
                $order_by,
                $order_way,
                $ajax,
                $use_cookie,
                $context
            );
        }
    }
}

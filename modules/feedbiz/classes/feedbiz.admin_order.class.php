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

require_once(dirname(__FILE__).'/../feedbiz.php');
require_once(dirname(__FILE__).'/feedbiz.order.class.php');
require_once(dirname(__FILE__).'/feedbiz.tools.class.php');
require_once(dirname(__FILE__).'/feedbiz.webservice.class.php');

/**
 * Class FeedbizAdminOrder
 */
class FeedbizAdminOrder extends Feedbiz
{
    /**
     * @var array
     */
    private static $orders_url = array(
        '2' => 'https://sellercentral.amazon.com/orders-v3/order/',
        '3' => 'http://www.ebay.com/',
        '5' => 'https://seller.cdiscount.com/'
    );
//    private static $orders_url = array(
//        '2' => 'https://sellercentral.amazon.com/gp/orders-v2/details/ref=ag_orddet_cont_myo?ie=UTF8&orderID=',
//        '3' => 'http://www.ebay.com/',
//        '5' => 'https://seller.cdiscount.com/'
//    );

    /**
     * @var array
     */
    private static $amazon_orders_url = array(
        //'amazon.com' =>
        // 'https://sellercentral-europe.amazon.com/orders-v3/order/',
        'amazon - com' =>
            'https://sellercentral.amazon.com/orders-v3/order/',
        'amazon - ca' => 'https://sellercentral.amazon.ca/orders-v3/order/',
        'amazon - fr' => 'https://sellercentral.amazon.fr/orders-v3/order/',
        'amazon - de' => 'https://sellercentral.amazon.de/orders-v3/order/',
        'amazon - es' => 'https://sellercentral.amazon.es/orders-v3/order/',
        'amazon - it' => 'https://sellercentral.amazon.it/orders-v3/order/',
        'amazon - mx' => 'https://sellercentral.amazon.com.mx/orders-v3/order/',
        'amazon - jp' =>
            'https://sellercentral.amazon.co.jp/orders-v3/order/',
        'amazon - uk' =>
            'https://sellercentral.amazon.co.uk/orders-v3/order/',
        'amazon - se' =>
            'https://sellercentral.amazon.se/orders-v3/order/'
    );
//    private static $amazon_orders_url = array(
//        //'amazon.com' =>
//        // 'https://sellercentral-europe.amazon.com/gp/orders-v2/details/ref=ag_orddet_cont_myo?ie=UTF8&orderID=',
//        'amazon - com' =>
//            'https://sellercentral.amazon.com/gp/orders-v2/details/ref=ag_orddet_cont_myo?ie=UTF8&orderID=',
//        'amazon - ca' => 'https://sellercentral.amazon.ca/gp/orders-v2/details/ref=ag_orddet_cont_myo?ie=UTF8&orderID=',
//        'amazon - fr' => 'https://sellercentral.amazon.fr/gp/orders-v2/details/ref=ag_orddet_cont_myo?ie=UTF8&orderID=',
//        'amazon - de' => 'https://sellercentral.amazon.de/gp/orders-v2/details/ref=ag_orddet_cont_myo?ie=UTF8&orderID=',
//        'amazon - es' => 'https://sellercentral.amazon.es/gp/orders-v2/details/ref=ag_orddet_cont_myo?ie=UTF8&orderID=',
//        'amazon - it' => 'https://sellercentral.amazon.it/gp/orders-v2/details/ref=ag_orddet_cont_myo?ie=UTF8&orderID=',
//        'amazon - mx' => 'https://sellercentral.amazon.com.mx/gp/orders-v2/details/ref=ag_orddet_cont_myo?ie=UTF8&orderID=',
//        'amazon - jp' =>
//            'https://sellercentral.amazon.co.jp/gp/orders-v2/details/ref=ag_orddet_cont_myo?ie=UTF8&orderID=',
//        'amazon - uk' =>
//            'https://sellercentral.amazon.co.uk/gp/orders-v2/details/ref=ag_orddet_cont_myo?ie=UTF8&orderID='
//    );

    /**
     * @param $params
     *
     * @return string
     */
    public function marketplaceOrderDisplay($params)
    {
        $id_order = (int)$params['id_order'];

        $order = new FeedBizOrder($id_order);

        if (!Validate::isLoadedObject($order)) {
            return (false);
        }

        // Not a parent order
        if ((Tools::strtolower($order->module) != Tools::strtolower(Feedbiz::MODULE_NAME) && !isset($order->Feedbiz))
            || !isset($order->Feedbiz['mp_order_id'])) {
            return false;
        }

        // For Quick Access
        $debug = (bool)Configuration::get('FEEDBIZ_DEBUG');
        $orders_url = self::$orders_url[$order->Feedbiz['channel_id']];
        $cancel_stage = false;
        $order_states = unserialize(Configuration::get('FEEDBIZ_ORDERS_STATES'));
        $canceled_state = $order_states ['FEEDBIZ_CR']; // Commande resilier

        if ($order->Feedbiz['channel_id'] == 2) {
            // Amazon
            $key = Tools::strtolower($order->Feedbiz['channel_name']);

            if (isset($order->Feedbiz['channel_name']) && array_key_exists($key, self::$amazon_orders_url)) {
                $orders_url = self::$amazon_orders_url[$key];
            }

            $orders_url .= $order->Feedbiz['mp_reference'];
        }

        $trackingNumber = FeedBizOrder::getShippingNumber($id_order);

        $view_params = array();
        $view_params['fbtoken'] = Configuration::get('FEEDBIZ_TOKEN');
        $view_params['debug'] = $debug;
        $view_params['orders_url'] = $orders_url;
        $view_params['cancel_url'] = $this->url.'functions/canceled.php?id_lang='.$this->id_lang;
        $view_params['css_url'] = $this->css.'orders_sheet.css';
        $view_params['js_url'] = $this->js.'orders_sheet.js';
        $view_params['images_url'] = $this->images;
        $view_params['ps_version_is_16'] = version_compare(_PS_VERSION_, '1.6', '>=');
        $view_params['ps_version_is_15'] = version_compare(_PS_VERSION_, '1.5', '>=') &&
            version_compare(_PS_VERSION_, '1.6', '<');
        $view_params['seller_order_id'] = $order->Feedbiz['id_order'];
        $view_params['marketplace_order_id'] = $order->Feedbiz['mp_order_id'];
        $view_params['marketplace_reference'] = $order->Feedbiz['mp_reference'];
        $view_params['marketplace_number'] = $order->Feedbiz['mp_number'];
        $view_params['tracking_number'] = $trackingNumber;

        if (FeedbizTools::tableExists(_DB_PREFIX_.self::TABLE_FEEDBIZ_ORDERS)) {
            $order_ext_data = FeedBizOrder::getOrderExt($id_order);

            if (is_array($order_ext_data) && count($order_ext_data)) {
                if (($channel_id = $order_ext_data['channel_id']) == 1) {
                    unset($order_ext_data['channel_id']);
                }

                $view_params['order_ext'] = $order_ext_data;
                $view_params['order_ext']['channel_color'] = 'black';

                if ($channel_id) {
                    if (isset(Feedbiz::$channel_colors[$channel_id])) {
                        $channel_color = Feedbiz::$channel_colors[$channel_id];
                    } else {
                        $channel_color = 'black';
                    }

                    if (isset(Feedbiz::$channels[$channel_id])) {
                        $channel = Feedbiz::$channels[$channel_id];
                    } else {
                        if (strpos($order->Feedbiz['channel_name'], ' - ') !== false) {
                            $channel_name = explode(' - ', $order->Feedbiz['channel_name']);
                            if (isset($channel_name[0])) {
                                $channel = Tools::strtolower($channel_name[0]);
                            }
                        } else {
                            $channel = '';
                        }
                    }

                    $view_params['order_ext']['channel_color'] = $channel_color;
                    $view_params['order_ext']['channel'] = $channel;
                }

                $in_array = in_array(
                    $order_ext_data['mp_status'],
                    array(
                        FeedbizOrder::TO_CANCEL,
                        FeedbizOrder::CANCELED,
                        FeedbizOrder::PROCESS_CANCEL
                    )
                );

                if (isset($order_ext_data['mp_status']) && $in_array) {
                    if ((int)$canceled_state && $order->current_state == $canceled_state) {
                        $cancel_stage = true;
                    }
                }
            }

            //multichannel
            if (isset($order_ext_data['multichannel']) && $order_ext_data['multichannel']) {
                $view_params['order_multichannel'] = $order_ext_data['multichannel'];
            }
            if (isset($order_ext_data['fulfillment_center_id']) && $order_ext_data['fulfillment_center_id']) {
                $view_params['order_fulfillment_center_id'] = $order_ext_data['fulfillment_center_id'];
            }
            //shipping_type
            if (isset($order_ext_data['shipping_type'])) {
                $view_params['order_shipping_type'] = $order_ext_data['shipping_type'];
            }
            if (isset($order_ext_data['is_prime'])) {
                $view_params['order_is_prime'] = $order_ext_data['is_prime'];
            }
            if (isset($order_ext_data['is_premium'])) {
                $view_params['order_is_premium'] = $order_ext_data['is_premium'];
            }
            if (isset($order_ext_data['is_business'])) {
                $view_params['order_is_business'] = $order_ext_data['is_business'];
            }
            if (isset($order_ext_data['earliest_ship_date'])) {
                $view_params['order_earliest_ship_date'] = $order_ext_data['earliest_ship_date'];
            }
            if (isset($order_ext_data['latest_ship_date'])) {
                $view_params['order_latest_ship_date'] = $order_ext_data['latest_ship_date'];
            }
        }

        if ($cancel_stage) {
            //
            // Standard Order
            //
            $view_params = $this->marketplaceOrderDisplayToCancelOrder($view_params, $order);
        }

        $this->context->smarty->assign($view_params);
        $html = $this->context->smarty->fetch($this->path.'views/templates/admin/admin_order/admin_order.tpl');

        return ($html);
    }

    /**
     * Displays a cancelable order
     * @param $view_params
     * @param $order
     * @return string
     * @throws Exception
     * @throws SmartyException
     */
    private function marketplaceOrderDisplayToCancelOrder($view_params, &$order)
    {
        switch ((int)$order->Feedbiz['mp_status']) {
            case FeedbizOrder::PROCESS_CANCEL:
                $view_params['scenario'] = 'cancel_cancel';
                $view_params['cancel_status'] = FeedbizOrder::REVERT_CANCEL;
                break;
            case FeedbizOrder::TO_CANCEL:
                $view_params['scenario'] = 'to_cancel';
                $view_params['cancel_status'] = FeedbizOrder::PROCESS_CANCEL;
                break;
            case FeedbizOrder::CANCELED:
                $view_params['scenario'] = 'canceled';
                $view_params['cancel_status'] = FeedbizOrder::CANCELED;
                break;
        }

        $cancelled_reasons = unserialize(Configuration::get('FEEDBIZ_ORDER_CANCEL_REASONS'));
        $marketplace = $view_params['order_ext']['channel'];
        $def_reason = array();
        foreach ($cancelled_reasons as $marketplace_key => $reasons) {
            if (Tools::strtolower($marketplace) == Tools::strtolower($marketplace_key)) {
                $view_params['reasons'] = $reasons ;
            }
            if (Tools::strtolower($marketplace_key) == 'ebay') {
                $def_reason = $reasons;
            }
        }
        if (empty($view_params['reasons'])) {
            $view_params['reasons'] = $def_reason;
        }

        if (Feedbiz::$debug_mode) {
            echo "<pre>Amazon, Debug Mode\n";
            printf('%s, line %d'."\n", basename(__FILE__), __LINE__);
            echo nl2br(print_r($view_params, true));
            echo "</pre>\n";
        }

        $this->context->smarty->assign($view_params);

        return ($view_params);
    }
}

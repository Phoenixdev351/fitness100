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

class FeedbizCarrier
{
    /**
     * @var array
     */
    private static $carrier_template = array(
        'name' => '',
        'id_tax' => 1,
        'id_tax_rules_group' => 1,
        'url' => null,
        'active' => true,
        'deleted' => 0,
        'shipping_handling' => false,
        'range_behavior' => 0,
        'is_module' => false,
        'id_zone' => 1,
        'shipping_external' => true,
        'external_module_name' => 'feedbiz',
        'need_range' => true
    );

    /**
     * @param $carrierName
     *
     * @return bool
     */
    public static function FBACarrier($carrierName)
    {
        $privateName = 'feedbiz_'.self::toPrivateName($carrierName);

        $sql = 'SELECT `id_carrier` FROM `'._DB_PREFIX_.'carrier` WHERE `external_module_name` = "'.pSQL($privateName).'"';

        $row = Db::getInstance()->getRow($sql);

        if (isset($row['id_carrier']) && (int)$row['id_carrier']) {
            return ($row['id_carrier']);
        }

        return (false);
    }

    /**
     * @param $carrierName
     * @param bool|false $state
     *
     * @return bool|int
     */
    public static function fbaCarrierCreate($carrierName, $state = false)
    {
        $privateName = 'feedbiz_'.self::toPrivateName($carrierName);

        $carrier = new Carrier();

        foreach (self::$carrier_template as $k => $v) {
            $carrier->{$k} = $v;
        }

        $carrier->name = self::toPublicName($carrierName);
        $carrier->active = (int)$state;
        $carrier->external_module_name = $privateName;

        foreach (Language::getLanguages(false) as $language) {
            $carrier->delay[$language['id_lang']] = $carrier->name.' via Feed.biz';
        }

        if (!$carrier->add()) {
            echo Tools::displayError('Unable to create carrier');

            return (false);
        }
        if (version_compare(_PS_VERSION_, '1.4', '<')) {
            Db::getInstance()->execute('UPDATE  `'._DB_PREFIX_.'carrier` SET `external_module_name`="'.pSQL($carrier->external_module_name).'" WHERE `id_carrier`='.(int)$carrier->id);
        }

        return ((int)$carrier->id);
    }

    /**
     * @param $order
     * @param $id_carrier
     * @param $trackingNumber
     * @param bool|false $debug
     *
     * @return bool
     * @throws PrestaShopException
     */
    public static function updateTrackingNumber($order, $id_carrier, $trackingNumber, $debug = false)
    {
        if (!$order->id) {
            if ($debug) {
                printf('%s:%d %s'."\n<br>", basename(__FILE__), __LINE__, 'Empty order');
            }

            return (false);
        }

        $id_order = $order->id;

        if (!$trackingNumber) {
            if ($debug) {
                printf('%s:%d %s id_order: %d'."\n<br>", basename(__FILE__), __LINE__, 'Empty tracking number', $id_order);
            }

            return (false);
        }

        // New fashioned
        //
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            // Adding an entry in order_carrier table
            if ($order->id_carrier != $id_carrier) {
                $order_carrier = new OrderCarrier();
                $order_carrier->id_order = (int) $id_order;
                $order_carrier->id_carrier = (int) $id_carrier;
                $order_carrier->weight = (float) $order->getTotalWeight();
                $order_carrier->shipping_cost_tax_excl = 0;
                $order_carrier->shipping_cost_tax_incl = 0;
                $order_carrier->tracking_number = $trackingNumber;
                $order_carrier->add();
            } else {
                // Update order_carrier
                $id_order_carrier = Db::getInstance()->getValue(' SELECT `id_order_carrier` FROM `'._DB_PREFIX_.'order_carrier` WHERE `id_order` = '.(int)$id_order.' AND (`id_order_invoice` IS NULL OR `id_order_invoice` = 0)');

                if ($id_order_carrier) {
                    $order_carrier = new OrderCarrier($id_order_carrier);
                    $order_carrier->id_carrier = $id_carrier;
                    $order_carrier->tracking_number = $trackingNumber;
                    $order_carrier->update();
                }
            }
        }

        // PS 1.5 < compat
        $order->id_carrier = (int)$id_carrier;
        $order->shipping_number = $trackingNumber;

        return ($order->update());
    }


    /**
     * @param $name
     *
     * @return bool|string
     */
    public static function toPrivateName($name)
    {
        $text = html_entity_decode($name, ENT_NOQUOTES, 'UTF-8');
        $text = mb_convert_encoding($text, 'HTML-ENTITIES', 'UTF-8');
        $text = preg_replace(array('/&szlig;/', '/&(..)lig;/', '/&([aoueAOUE])uml;/', '/&(.)[^;]*;/'), array(
                'ss',
                '$1',
                '$1'.'e',
                '$1'
            ), $text);
        $text = preg_replace('/[!<>?=+@{}_$%]*$/u', '', $text); // remove non printable
        $text = preg_replace('/\s+/', '_', $text);

        return (Tools::strtolower($text));
    }

    /**
     * @param $name
     *
     * @return string
     */
    public static function toPublicName($name)
    {
        return (html_entity_decode($name, ENT_NOQUOTES, 'UTF-8'));
    }
}

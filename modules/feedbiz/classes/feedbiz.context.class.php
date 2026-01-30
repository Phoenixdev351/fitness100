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

require_once(dirname(__FILE__).'/../classes/feedbiz.shop.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.configuration.class.php');

/**
 * Class FeedbizContext
 */
class FeedbizContext
{
    /**
     * Restore shop context for ajax scripts
     * @param $context
     * @param null $shop
     * @param bool|false $debug
     * @return bool
     */
    public static function restore(&$context, $shop = null, $debug = false)
    {
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            if (!Shop::isFeatureActive()) {
                $context = Context::getContext();
                if (!property_exists($context, 'controller') || !is_object($context->controller)) {
                    $context->controller = new FrontController();
                }

                return (true);
            }

            $storedContexts = FeedbizConfiguration::get('context');

            if ($shop instanceof Shop) {
                $context_key = self::getKey($shop);
            } else {
                $context_key = Tools::getValue('context_key');
            }

            if (!is_array($storedContexts) || !count($storedContexts) || !is_object($context_key)) {
                if ($debug) {
                    printf('%s(#%d): Wrong context, please configure your module first', basename(__FILE__), __LINE__);
                }

                return (false);
            }

            if (!isset($storedContexts[$context_key]) || !$storedContexts[$context_key] || !is_object($storedContexts[$context_key])) {
                if ($debug) {
                    printf('%s(#%d): Wrong context, please configure your module first', basename(__FILE__), __LINE__);
                }

                return (false);
            }

            $context->shop = $storedContexts[$context_key]->shop;
            $context->employee = $storedContexts[$context_key]->employee;
            $context->currency = $storedContexts[$context_key]->currency;
            $context->country = $storedContexts[$context_key]->country;
            $context->language = $storedContexts[$context_key]->language;
            //$context->controller = isset($storedContexts[$context_key]->controller) && is_object($storedContexts[$context_key]->controller) ? $storedContexts[$context_key]->controller : new FrontController();

            FeedbizShop::setShop($context->shop);
        }

        return (true);
    }

    /**
     * Generate an unique key to store the context
     * @param $shop
     * @return null|string
     */
    public static function getKey($shop)
    {
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            return (null);
        }

        if (!Shop::isFeatureActive()) {
            return (null);
        }

        if (!$shop instanceof Shop) {
            return (null);
        }

        $id_shop = (int)$shop->id;
        $id_shop_group = (int)$shop->id_shop_group;

        $context_key = dechex(crc32(sprintf('%02d_%02d', $id_shop, $id_shop_group))); // create a short key

        return ($context_key);
    }

    /**
     * Save store context
     * @param $context
     * @param null $employee
     * @return bool
     */
    public static function save($context, $employee = null)
    {
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $storedContexts = FeedbizConfiguration::get('context');

            if (is_array($storedContexts) && count($storedContexts)) {
                $feedbizContexts = $storedContexts;
            } else {
                $feedbizContexts = array();
            }

            $contextData = new Context();
            $contextData->shop = $context->shop;

            if (Validate::isLoadedObject($employee)) {
                $contextData->employee = $employee;
            } else {
                $contextData->employee = $context->employee;
            }

            $contextData->shop = $context->shop;
            $contextData->currency = $context->currency;
            $contextData->country = $context->country;
            $contextData->language = $context->language;

            $contextData = Tools::jsonDecode(Tools::jsonEncode($contextData));//convert all as a StdClass

            $contextKey = self::getKey($contextData->shop);

            if (!isset($feedbizContexts[$contextKey]) || !is_array($feedbizContexts[$contextKey])) {
                $feedbizContexts[$contextKey] = array();
            }

            $feedbizContexts[$contextKey] = $contextData;

            return (FeedbizConfiguration::updateValue('context', $feedbizContexts));
        }

        return (true);
    }
}

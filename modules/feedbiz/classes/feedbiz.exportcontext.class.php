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

class FeedBizExportContext
{
    /**
     *
     */
    const CONF_FEEDBIZ_PRODUCTS_EXPORT_CONTEXT = 'FEEDBIZ_PRODUCTS_EXPORT_CONTEXT';
    /**
     *
     */
    const CONF_FEEDBIZ_OFFERS_EXPORT_CONTEXT = 'FEEDBIZ_OFFERS_EXPORT_CONTEXT';
    /**
     *
     */
    const STATUS_COMPLETE = 'complete';
    /**
     *
     */
    const STATUS_INCOMPLETE = 'incomplete';
    /**
     *
     */
    const TIMESTAMP_MIN_EXPIRE = 5;

    /**
     * @var null
     */
    public $timestamp = null;
    /**
     * @var int
     */
    public $currentPage = 0;
    /**
     * @var int
     */
    public $currentProduct = 0;
    /**
     * @var int
     */
    public $maxProduct = 0;
    /**
     * @var int
     */
    public $minProduct = 0;
    /**
     * @var int
     */
    public $currentProductCount = 0;
    /**
     * @var string
     */
    public $status = self::STATUS_INCOMPLETE;

    /**
     * @param $context
     * @param $conf_key
     *
     * @return bool
     */
    public static function restore(&$context, $conf_key)
    {
        $rawContext = unserialize(Configuration::get($conf_key));
        $current = new DateTime();

        if ($rawContext instanceof self
            && self::minDiff($current, $rawContext->timestamp) < self::TIMESTAMP_MIN_EXPIRE) {
            $context = $rawContext;
        }

        return true;
    }

    /**
     * @param $context_type
     * @param $context
     *
     * @return bool
     */
    public static function save($context_type, $context)
    {
        return (Configuration::updateValue($context_type, serialize($context)));
    }

    /**
     * @param $dt01
     * @param $dt02
     *
     * @return float
     */
    public static function minDiff($dt01, $dt02)
    {
        if ($dt01 instanceof DateTime) {
            $dt01 = $dt01->format('Y-m-d H:i:s');
        }
        if ($dt02 instanceof DateTime) {
            $dt02 = $dt02->format('Y-m-d H:i:s');
        }
        $diff = strtotime($dt01) - strtotime($dt02);
        $result = $diff / 60;

        return $result;
    }
}

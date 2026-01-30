<?php
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */

if (!defined('_PS_VERSION_')) { exit; }
class Ets_superspeed_cache_page_error extends ObjectModel
{
    public $id_cache_page;
    public $page;
    public $id_object;
    public $id_product_attribute;
    public $ip;
    public $file_cache;
    public $request_uri;
    public $id_shop;
    public $id_lang;
    public $id_currency;
    public $id_country;
    public $file_size;
    public $user_agent;
    public $has_customer;
    public $error;
    public $has_cart;
    public $date_add;
    public static $definition = array(
        'table' => 'ets_superspeed_cache_page_error',
        'primary' => 'id_ets_superspeed_cache_page_error',
        'multilang' => false,
        'fields' => array(
            'id_cache_page' => array('type' => self::TYPE_INT),
            'page' => array('type' => self::TYPE_STRING),
            'id_object' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'id_product_attribute' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'ip' => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
            'request_uri' => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
            'file_cache' => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
            'id_shop' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'id_lang' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'id_currency' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'id_country' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'has_customer' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'has_cart' => array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
            'error' => array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'),
            'date_add' => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
        )
    );
    public static function getTotalPageNoCaches($filter='')
    {
        $sql ='SELECT COUNT(cache.id_ets_superspeed_cache_page_error) FROM `' . _DB_PREFIX_ . 'ets_superspeed_cache_page_error` cache';
        if($filter)
        {
            $sql .=' LEFT JOIN `'._DB_PREFIX_.'currency` currency ON (cache.id_currency = currency.id_currency)
            LEFT JOIN `'._DB_PREFIX_.'country` country ON (country.id_country=cache.id_country)
            LEFT JOIN `'._DB_PREFIX_.'country_lang` country_lang ON (country_lang.id_country = country.id_country AND country_lang.id_lang="'.(int)Context::getContext()->language->id.'")
            LEFT JOIN `'._DB_PREFIX_.'lang` lang ON (lang.id_lang=cache.id_lang)';
        }
        $sql .=' WHERE cache.id_shop=' . (int)Context::getContext()->shop->id.($filter ? (string)$filter:'');
        return (int)Db::getInstance()->getValue($sql);
    }
    public static function getListPageNoCaches($start=0,$limit=20,$sql_sort='',$filter='')
    {
        $sql ='SELECT cache.*,currency.iso_code,country_lang.name as country_name,lang.name as lang_name FROM `' . _DB_PREFIX_ . 'ets_superspeed_cache_page_error` cache
        LEFT JOIN `'._DB_PREFIX_.'currency` currency ON (cache.id_currency = currency.id_currency)
        LEFT JOIN `'._DB_PREFIX_.'country` country ON (country.id_country=cache.id_country)
        LEFT JOIN `'._DB_PREFIX_.'country_lang` country_lang ON (country_lang.id_country = country.id_country AND country_lang.id_lang="'.(int)Context::getContext()->language->id.'")
        LEFT JOIN `'._DB_PREFIX_.'lang` lang ON (lang.id_lang=cache.id_lang)
        WHERE cache.id_shop = "' . (int)Context::getContext()->shop->id . '" '.($filter ? (string)$filter:'').($sql_sort ? ' ORDER BY '.bqSQL($sql_sort) : '').' LIMIT ' . (int)$start . ',' . (int)$limit;
        return Db::getInstance()->executeS($sql);
    }
    public static function deleteAllLog()
    {
        return Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'ets_superspeed_cache_page_error`');
    }
}
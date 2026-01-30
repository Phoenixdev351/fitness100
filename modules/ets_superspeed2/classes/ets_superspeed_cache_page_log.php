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
class Ets_superspeed_cache_page_log extends ObjectModel
{
    public $page;
    public $reason;
    public $date_add;
    public static $definition = array(
        'table' => 'ets_superspeed_cache_page_log',
        'primary' => 'id_ets_superspeed_cache_page_log',
        'multilang' => false,
        'fields' => array(
            'page' => array('type' => self::TYPE_STRING),
            'reason' => array('type' => self::TYPE_STRING),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        )
    );
    public static function addLog($page,$reason)
    {
        if(Configuration::get('ETS_SPEED_ENABLE_LOG_CACHE_CLEAR'))
        {
            $log = new Ets_superspeed_cache_page_log();
            $log->page = $page;
            $log->reason = $reason;
            return $log->add();
        }
    }
    public static function getTotalLogs($filter='')
    {
        $sql ='SELECT COUNT(id_ets_superspeed_cache_page_log) FROM `'._DB_PREFIX_.'ets_superspeed_cache_page_log` WHERE 1'.($filter ? (string)$filter:'');
        return Db::getInstance()->getValue($sql);
    }
    public static function getListLogs($start=0,$limit=20,$sql_sort='',$filter='')
    {
        $sql ='SELECT *,page as page_name FROM `' . _DB_PREFIX_ . 'ets_superspeed_cache_page_log`
        WHERE 1 '.($filter ? (string)$filter:'').($sql_sort ? ' ORDER BY '.bqSQL($sql_sort) : '').' LIMIT ' . (int)$start . ',' . (int)$limit;
        return Db::getInstance()->executeS($sql);
    }
    public static function deleteAllLog()
    {
        return Db::getInstance()->execute('TRUNCATE TABLE `'._DB_PREFIX_.'ets_superspeed_cache_page_log`');
    }
}
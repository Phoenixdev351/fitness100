<?php
/**
 * Affiliates
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @author    FMM Modules
 * @copyright © Copyright 2021 - All right reserved
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @category  FMM Modules
 * @package   affiliates
 */

class PaymentDetails extends ObjectModel
{
    public $id;

    public $affiliate_payment_details;

    public $id_affiliate;

    public $type = 1;

    public $details;

    public $status = 1;

    public $date_add = '0000-00-00 00:00:00';

    public $upd_date = '0000-00-00 00:00:00';

    public static $definition = array(
        'table' => 'affiliate_payment_details',
        'primary' => 'id_affiliate_payment_details',
        'multilang' => false,
        'fields' => array(
            'id_affiliate' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'type' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'details' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'status' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'date_add' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'upd_date' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
        ),
    );

    public static function createTable()
    {
        // referral table
        Db::getInstance()->execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'affiliate_payment_details');
        Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'affiliate_payment_details(
            `affiliate_payment_details`         int(11) unsigned NOT NULL auto_increment,
            `id_affiliate`      int(11) unsigned NOT NULL,
            `type`              int(11) unsigned NOT NULL default 0,
            `details`           text,
            `status`            tinyint(2) DEFAULT 1,
            `date_add`          text,
            `upd_date`          text,
            PRIMARY KEY         (`affiliate_payment_details`)
            ) ENGINE=InnoDB     AUTO_INCREMENT=1 DEFAULT CHARSET=utf8');
        return true;
    }

    public static function removeTable()
    {
        // Delete Tables
        Db::getInstance()->Execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'affiliate_payment_details');
        return true;
    }

    public function add($autodate = true, $null_values = false)
    {
        if (!parent::add($autodate, $null_values)) {
            return false;
        }
        return true;
    }

    public function update($null_values = false)
    {
        if (parent::update($null_values)) {
            return true;
        }
        return false;
    }

    public function delete()
    {
        if (parent::delete()) {
            return true;
        }
        return false;
    }

    public static function getPaymentDetailsById($id_affiliate_payment_details)
    {
        return Db::getInstance()->ExecuteS('SELECT pd.*, a.`id_customer`, a.`id_guest`, a.`rule`, a.`ref_key`, a.`active`, a.`approved`, a.`date_from`, a.`date_to`
            FROM `' . _DB_PREFIX_ . 'affiliate_payment_details` pd
            LEFT JOIN `' . _DB_PREFIX_ . 'affiliate` a
                ON (pd.id_affiliate = a.id_affiliate)
            WHERE pd.id_affiliate_payment_details = ' . (int) $id_affiliate_payment_details);
    }

    public static function getPaymentDetailsByAffiliate($id_affiliate)
    {
        return Db::getInstance()->ExecuteS('SELECT pd.*, a.`id_customer`, a.`id_guest`, a.`rule`, a.`ref_key`, a.`active`, a.`approved`, a.`date_from`, a.`date_to`
            FROM `' . _DB_PREFIX_ . 'affiliate_payment_details` pd
            LEFT JOIN `' . _DB_PREFIX_ . 'affiliate` a
                ON (pd.id_affiliate = a.id_affiliate)
            WHERE pd.id_affiliate = ' . (int) $id_affiliate);
    }

    public static function getPaymentDetailByType($id_affiliate, $type)
    {
        return Db::getInstance()->getRow('SELECT pd.*, a.`id_customer`, a.`id_guest`, a.`rule`, a.`ref_key`, a.`active`, a.`approved`, a.`date_from`, a.`date_to`
            FROM `' . _DB_PREFIX_ . 'affiliate_payment_details` pd
            LEFT JOIN `' . _DB_PREFIX_ . 'affiliate` a
                ON (pd.id_affiliate = a.id_affiliate)
            WHERE pd.type = ' . (int) $type . '
            AND pd.id_affiliate = ' . (int) $id_affiliate);
    }

    public static function getPaymentDetailsByMethod($id_affiliate, $type)
    {
        return Db::getInstance()->getValue('SELECT `details`
            FROM `' . _DB_PREFIX_ . 'affiliate_payment_details`
            WHERE type = ' . (int) $type . '
            AND id_affiliate = ' . (int) $id_affiliate);
    }

    public static function getPaymentIdByType($id_affiliate, $type)
    {
        return Db::getInstance()->getValue('SELECT `id_affiliate_payment_details`
            FROM `' . _DB_PREFIX_ . 'affiliate_payment_details`
            WHERE type = ' . (int) $type . '
            AND id_affiliate = ' . (int) $id_affiliate);
    }
}

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

class Payment extends ObjectModel
{
    public $id;

    public $id_affiliate_payment;

    public $id_affiliate_reward;

    public $id_affiliate;

    public $type = 1;

    public $details;

    public $status;

    public $requested_date = '0000-00-00 00:00:00';

    public $upd_date = '0000-00-00 00:00:00';

    public static $definition = array(
        'table' => 'affiliate_payment',
        'primary' => 'id_affiliate_payment',
        'multilang' => false,
        'fields' => array(
            'id_affiliate_reward' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_affiliate' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'type' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'details' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'status' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'requested_date' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'upd_date' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
        ),
    );

    public static function createTable()
    {
        // referral table
        Db::getInstance()->execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'affiliate_payment');
        Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'affiliate_payment(
            `id_affiliate_payment`        int(11) unsigned NOT NULL auto_increment,
            `id_affiliate_reward`         int(11) unsigned NOT NULL,
            `id_affiliate`      int(11) unsigned NOT NULL,
            `type`              int(11) unsigned NOT NULL default 0,
            `details`           text,
            `upd_date`          text,
            `status`            text,
            `requested_date`    text,
            PRIMARY KEY         (`id_affiliate_payment`)
            ) ENGINE=InnoDB     AUTO_INCREMENT=1 DEFAULT CHARSET=utf8');
        return true;
    }

    public static function removeTable()
    {
        // Delete Tables
        Db::getInstance()->Execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'affiliate_payment');
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

    public static function getWdRequestsById($id_affiliate_payment)
    {
        return Db::getInstance()->getRow('SELECT pm.*, c.`email` AS customer_email, CONCAT(c.`firstname`, \' \', c.`lastname`) AS affiliate_customer,
            SUM(rew.`ord_reward_value` + rew.`reg_reward_value`) AS requested_amount
            FROM `' . _DB_PREFIX_ . 'affiliate_payment` pm
            LEFT JOIN `' . _DB_PREFIX_ . 'affiliate_reward` rew
                ON (rew.id_affiliate_reward = pm.id_affiliate_reward)
            LEFT JOIN `' . _DB_PREFIX_ . 'customer` c
                ON (c.id_customer = rew.id_customer)
            WHERE pm.id_affiliate_payment = ' . (int) $id_affiliate_payment . '
            GROUP BY rew.id_affiliate_reward');
    }

    public static function getWdRequestsByAffiliate($id_affiliate)
    {
        return Db::getInstance()->ExecuteS('SELECT pm.*,
        pd.`id_affiliate_payment_details`, pd.`id_affiliate`, pd.`type`, pd.`details`,
        c.`email` AS customer_email, CONCAT(c.`firstname`, \' \', c.`lastname`) AS affiliate_customer,
            SUM(rew.`ord_reward_value` + rew.`reg_reward_value`) AS requested_amount
            FROM `' . _DB_PREFIX_ . 'affiliate_payment` pm
            LEFT JOIN `' . _DB_PREFIX_ . 'affiliate_payment_details` pd
                ON (pm.`id_affiliate` = pd.`id_affiliate` AND pm.`type` = pd.`type`)
            LEFT JOIN `' . _DB_PREFIX_ . 'affiliate_reward` rew
                ON (rew.`id_affiliate_reward` = pm.`id_affiliate_reward` AND rew.`id_affiliate` = pm.`id_affiliate`)
            LEFT JOIN `' . _DB_PREFIX_ . 'customer` c
                ON (c.`id_customer` = rew.`id_customer`)
            WHERE pm.`id_affiliate` = ' . (int) $id_affiliate . '
            AND pm.status <> "accepted"
            GROUP BY pm.id_affiliate_payment');
    }

    public static function getPaymentByReward($id_affiliate_reward, $id_affiliate)
    {
        if (!$id_affiliate_reward) {
            return false;
        }

        return Db::getInstance()->getRow('SELECT `id_affiliate_payment`, `type`
            FROM `' . _DB_PREFIX_ . 'affiliate_payment`
            WHERE id_affiliate_reward = ' . (int) $id_affiliate_reward . '
            AND id_affiliate = ' . (int) $id_affiliate);
    }

    public static function deleteByReward($id_affiliate_reward)
    {
        return Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . 'affiliate_payment` WHERE id_affiliate_reward = ' . (int) $id_affiliate_reward);
    }
}

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

class Rewards extends ObjectModel
{
    public $id;

    public $id_affiliate_reward;

    public $id_affiliate;

    public $id_affiliate_referral;

    public $id_customer = 0;

    public $id_guest = 0;

    public $id_order = 0;

    public $reward_by_reg = 0;

    public $reward_by_ord = 0;

    public $pay_request = 'not sent';

    public $is_paid = 0;

    public $reg_reward_value;

    public $ord_reward_value;

    public $status = 'pending';

    public $reward_date = '0000-00-00 00:00:00';

    public static $definition = array(
        'table' => 'affiliate_reward',
        'primary' => 'id_affiliate_reward',
        'multilang' => false,
        'fields' => array(
            'id_affiliate' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_affiliate_referral' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_guest' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'reward_by_reg' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'pay_request' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'reward_by_ord' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'is_paid' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'reg_reward_value' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'ord_reward_value' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'status' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'reward_date' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
        ),
    );

    public static function createTable()
    {
        // referral table
        Db::getInstance()->execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'affiliate_reward');
        Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'affiliate_reward(
            `id_affiliate_reward`         int(11) unsigned NOT NULL auto_increment,
            `id_affiliate`      int(11) unsigned NOT NULL default 0,
            `id_affiliate_referral`       int(11) unsigned NOT NULL default 0,
            `id_customer`       int(11) unsigned NOT NULL default 0,
            `id_guest`          int(11) unsigned NOT NULL default 0,
            `id_order`          int(11) unsigned NOT NULL default 0,
            `reward_by_reg`     tinyint(1) default 0,
            `reward_by_ord`     tinyint(1) default 0,
            `pay_request`       text,
            `is_paid`           tinyint(1) default 0,
            `reg_reward_value`  DECIMAL(20,6),
            `ord_reward_value`  DECIMAL(20,6),
            `status`            text,
            `reward_date`       text,
            PRIMARY KEY         (`id_affiliate_reward`)
            ) ENGINE=InnoDB     AUTO_INCREMENT=1 DEFAULT CHARSET=utf8');
        return true;
    }

    public static function removeTable()
    {
        // Delete Tables
        return (bool) Db::getInstance()->execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'affiliate_reward');
    }

    public static function getCustomerRewards($id_customer)
    {
        if (!$id_customer) {
            return false;
        }

        return Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'affiliate_reward`
            WHERE `id_customer` = ' . (int) $id_customer);
    }

    public static function getRewardByOrder($id_order)
    {
        if (!$id_order) {
            return false;
        }

        return Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'affiliate_reward`
            WHERE `reward_by_ord` = 1
            AND `id_order` = ' . (int) $id_order);
    }

    public static function getRewardTotalById($id_affiliate_reward)
    {
        return Db::getInstance()->getValue('SELECT SUM(`ord_reward_value` + `reg_reward_value`) AS total_reward
            FROM `' . _DB_PREFIX_ . 'affiliate_reward` WHERE `id_affiliate_reward` = ' . (int) $id_affiliate_reward);
    }

    public static function getAffiliateFromReward($id_affiliate_reward)
    {
        if (!$id_affiliate_reward) {
            return false;
        }

        return (int) Db::getInstance()->getValue('SELECT `id_affiliate`
            FROM `' . _DB_PREFIX_ . 'affiliate_reward` WHERE `id_affiliate_reward` = ' . (int) $id_affiliate_reward);
    }

    public static function getRewards($id_affiliate)
    {
        if (!$id_affiliate) {
            return false;
        }

        $sql = 'SELECT rew.*, MONTH(rew.`reward_date`) AS month, r.`source`,
                IF(rew.`reward_by_ord` = 1, rew.`ord_reward_value`, rew.`reg_reward_value`) AS reward,
                CONCAT(LEFT(c.`firstname`, 1), \'. \', c.`lastname`) AS affiliate,
                CONCAT(LEFT(cu.`firstname`, 1), \'. \', cu.`lastname`) AS referral
                FROM `' . _DB_PREFIX_ . 'affiliate_reward` rew
                LEFT JOIN `' . _DB_PREFIX_ . 'customer` c
                ON (c.`id_customer` = rew.`id_customer`)
                LEFT JOIN `' . _DB_PREFIX_ . 'affiliate_referral` r
                ON (r.`id_affiliate_referral` = rew.`id_affiliate_referral`)
                JOIN `' . _DB_PREFIX_ . 'customer` cu
                ON (r.`id_customer` = cu.`id_customer` AND r.`id_affiliate_referral` = rew.`id_affiliate_referral`)
                WHERE rew.`id_affiliate` = ' . (int) $id_affiliate;

        $result = Db::getInstance()->ExecuteS($sql);

        $data = array();
        if ($result) {
            foreach ($result as &$res) {
                $data[$res['month']][] = $res;
            }
        }
        return $data;
    }

    public static function getRewardsByMonth($id_affiliate)
    {
        return Db::getInstance()->ExecuteS('SELECT (SUM(reg_reward_value) + SUM(ord_reward_value)) AS total,
            MONTH(reward_date) AS months
            FROM `' . _DB_PREFIX_ . 'affiliate_reward`
            WHERE `id_affiliate` = ' . (int) $id_affiliate . '
            AND `status` = "approved"
            GROUP BY months');
    }

    public static function getCustomerValidRewards($id_customer)
    {
        $delay_type = self::getDelayType();
        return Db::getInstance()->ExecuteS('SELECT `id_affiliate_reward`, `reward_date`, SUM(reg_reward_value + ord_reward_value) AS reward_total
            FROM `' . _DB_PREFIX_ . 'affiliate_reward`
            WHERE `is_paid` = 0
            AND ' . ((Configuration::get('PAYMENT_DELAY_TIME')) ? 'UNIX_TIMESTAMP(CAST(DATE_ADD(reward_date, INTERVAL ' . (int) Configuration::get('PAYMENT_DELAY_TIME') . ' ' . $delay_type . ') AS DATETIME)) < UNIX_TIMESTAMP(CAST("' . date('Y-m-d H:i:s') . '" AS DATETIME))' : '1') . '
            AND `status` = "approved"
            AND (`pay_request` = "not sent" OR `pay_request` = "cancelled")
            AND `id_customer` = ' . (int) $id_customer . '
            GROUP BY `id_affiliate_reward`');
    }

    public static function getCustomerTotalApprovedReward($id_customer)
    {
        if (!$id_customer) {
            return false;
        }

        return Db::getInstance()->getValue('SELECT (SUM(reg_reward_value) + SUM(ord_reward_value)) AS `total_rewards`
            FROM `' . _DB_PREFIX_ . 'affiliate_reward`
            WHERE `status` != "cancel"
            AND `id_customer` = ' . (int) $id_customer);
    }

    public static function getCustomerPendingRewards($id_customer)
    {
        if (!$id_customer) {
            return false;
        }

        $delay_type = self::getDelayType();
        return Db::getInstance()->getValue('SELECT (SUM(reg_reward_value) + SUM(ord_reward_value)) AS `pending_rewards`
            FROM `' . _DB_PREFIX_ . 'affiliate_reward`
            WHERE `is_paid` = 0
            AND (
                `status` = "pending" OR
                ' . ((Configuration::get('PAYMENT_DELAY_TIME')) ? 'UNIX_TIMESTAMP(CAST(DATE_ADD(reward_date, INTERVAL ' . (int) Configuration::get('PAYMENT_DELAY_TIME') . ' ' . $delay_type . ') AS DATETIME)) > UNIX_TIMESTAMP(CAST("' . date('Y-m-d H:i:s') . '" AS DATETIME))' : '1') . '
            ) AND `id_customer` = ' . (int) $id_customer);
    }

    public static function getCustomerTotalPaidRewards($id_customer)
    {
        if (!$id_customer) {
            return false;
        }

        return Db::getInstance()->getValue('SELECT (SUM(reg_reward_value) + SUM(ord_reward_value)) AS `total_rewards`
            FROM `' . _DB_PREFIX_ . 'affiliate_reward`
            WHERE `status` = "approved"
            AND `pay_request` = "paid"
            AND `is_paid` = 1
            AND `id_customer` = ' . (int) $id_customer);
    }

    public static function getCustomerAwaitingPayments($id_customer)
    {
        if (!$id_customer) {
            return false;
        }

        return Db::getInstance()->getValue('SELECT (SUM(reg_reward_value) + SUM(ord_reward_value)) AS `pending_rewards`
            FROM `' . _DB_PREFIX_ . 'affiliate_reward`
            WHERE `status` = "approved"
            AND (`pay_request` = "pending" OR `pay_request` = "cancelled")
            AND `is_paid` = 0
            AND `id_customer` = ' . (int) $id_customer);
    }

    public static function getTotalReward($id_affiliate)
    {
        return Db::getInstance()->getRow('SELECT SUM(reg_reward_value) AS `total_by_reg`, SUM(ord_reward_value) AS `total_by_ord`
            FROM `' . _DB_PREFIX_ . 'affiliate_reward`
            WHERE `id_affiliate` = ' . (int) $id_affiliate);
    }

    public static function getAffiliateReferralRewards($id_affiliate)
    {
        return Db::getInstance()->ExecuteS('SELECT rew.*, ref.`source`, o.`reference`, c.`email`,
            IF(c.`firstname` IS NULL AND c.`lastname` IS NULL, "guest", CONCAT(c.`firstname`, \' \', c.`lastname`)) AS ref_name
            FROM `' . _DB_PREFIX_ . 'affiliate_reward` rew
            LEFT JOIN `' . _DB_PREFIX_ . 'affiliate_referral` ref
                ON (rew.`id_affiliate_referral` = ref.`id_affiliate_referral` AND ref.`id_affiliate` = rew.`id_affiliate`)
            LEFT JOIN `' . _DB_PREFIX_ . 'orders` o
                ON (rew.`id_order` = o.`id_order` AND ref.`id_customer` = o.`id_customer`)
            LEFT JOIN `' . _DB_PREFIX_ . 'customer` c
                ON (c.id_customer = ref.id_customer AND o.`id_customer` = c.`id_customer`)
            WHERE rew.`id_affiliate` = ' . (int) $id_affiliate);
    }

    protected static function getDelayType()
    {
        $type = 'DAY';
        switch (Configuration::get('DELAY_TYPE')) {
            case 'd':
                $type = 'DAY';
                break;
            case 'm':
                $type = 'MINUTE';
                break;
            case 'h':
                $type = 'HOUR';
                break;
        }
        return $type;
    }
}

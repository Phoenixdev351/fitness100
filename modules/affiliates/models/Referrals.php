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

class Referrals extends ObjectModel
{
    public $id;

    public $id_affiliate_referral;

    public $id_affiliate;

    public $id_customer = 0;

    public $id_guest = 0;

    public $active = 1;

    public $approved = 1;

    public $source;

    public $date_from = '0000-00-00 00:00:00';

    public $date_to = '0000-00-00 00:00:00';

    public $date_add = '0000-00-00 00:00:00';

    public static $definition = array(
        'table' => 'affiliate_referral',
        'primary' => 'id_affiliate_referral',
        'multilang' => false,
        'fields' => array(
            'id_affiliate' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_guest' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'approved' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'source' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'date_from' => array('type' => self::TYPE_DATE, 'validate' => 'isString'),
            'date_to' => array('type' => self::TYPE_DATE, 'validate' => 'isString'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isString'),
        ),
    );

    public static function createTable()
    {
        // referral table
        Db::getInstance()->execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'affiliate_referral');
        Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'affiliate_referral(
            `id_affiliate_referral`       int(11) unsigned NOT NULL auto_increment,
            `id_affiliate`      int(11) unsigned NOT NULL default 0,
            `id_customer`       int(11) unsigned NOT NULL default 0,
            `id_guest`          int(11) unsigned NOT NULL default 0,
            `active`            tinyint(1) default 1,
            `approved`          tinyint(1) default 1,
            `source`            text,
            `date_add`          text,
            `date_from`         text,
            `date_to`           text,
            PRIMARY KEY         (`id_affiliate_referral`)
            ) ENGINE=InnoDB     AUTO_INCREMENT=1 DEFAULT CHARSET=utf8');
        return true;
    }

    public static function removeTable()
    {
        // Delete Tables
        Db::getInstance()->Execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'affiliate_referral');
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

    public static function getReferralsByCustomer($id_customer)
    {
        return Db::getInstance()->ExecuteS('SELECT r.`id_affiliate_referral`, a.`id_affiliate`, c.`id_customer`, g.`id_guest`, c.`firstname`, c.`lastname`, c.`email`
            FROM `' . _DB_PREFIX_ . 'affiliate_referral` r
            LEFT JOIN `' . _DB_PREFIX_ . 'affiliate` a
                ON (r.id_affiliate = a.id_affiliate)
            LEFT JOIN `' . _DB_PREFIX_ . 'customer` c
                ON (r.id_customer = c.id_customer)
            LEFT JOIN `' . _DB_PREFIX_ . 'guest` g
                ON r.id_guest = g.id_guest
            WHERE r.active = 1
            AND a.`id_customer` = ' . (int) $id_customer);
    }

    public static function getReferralsGuestById($id_guest)
    {
        return Db::getInstance()->getValue('SELECT `id_affiliate_referral`
            FROM `' . _DB_PREFIX_ . 'affiliate_referral`
            WHERE `id_guest` = ' . (int) $id_guest);
    }

    public static function getReferralByCustomer($id_customer, $id_guest = 0)
    {
        return Db::getInstance()->getRow('SELECT *
            FROM `' . _DB_PREFIX_ . 'affiliate_referral`
            WHERE ' . (($id_guest) ? '`id_guest` = ' . (int) $id_guest : '1') . '
            AND `id_customer` = ' . (int) $id_customer);
    }
    

    public static function getAffiliateByCustomer($id_customer, $id_guest = 0)
    {
        return Db::getInstance()->getValue('SELECT `id_affiliate`
            FROM `' . _DB_PREFIX_ . 'affiliate_referral`
            WHERE ' . (($id_guest) ? '`id_guest` = ' . (int) $id_guest : '1') . '
            AND `id_customer` = ' . (int) $id_customer);
    }
    
    public static function getAffiliateByGuestOnly($id_guest)
    {
        return Db::getInstance()->getValue('SELECT `id_affiliate`
            FROM `' . _DB_PREFIX_ . 'affiliate_referral`
            WHERE `id_guest` = ' . (int) $id_guest);
    }

    public static function getApprovedReferralsByCustomer($id_customer)
    {
        return Db::getInstance()->ExecuteS('SELECT r.`id_affiliate_referral`, r.`source`, r.`date_add`, a.`id_affiliate`,c.`id_customer`, g.`id_guest`, c.`firstname`, c.`lastname`, c.`email`,
            inv.`firstname` AS guest_fname, inv.`lastname` AS guest_lname, inv.`email` AS guest_email
            FROM `' . _DB_PREFIX_ . 'affiliate_referral` r
            LEFT JOIN `' . _DB_PREFIX_ . 'affiliate` a
                ON (r.id_affiliate = a.id_affiliate)
            LEFT JOIN `' . _DB_PREFIX_ . 'customer` c
                ON (r.id_customer = c.id_customer)
            LEFT JOIN `' . _DB_PREFIX_ . 'guest` g
                ON (r.id_guest = g.id_guest)
            LEFT JOIN `' . _DB_PREFIX_ . 'affiliate_invitation` inv
                ON (inv.id_affiliate_referral = r.id_affiliate_referral)
            WHERE r.active = 1
            AND a.`id_customer` = ' . (int) $id_customer);
    }

    public static function getReferralByAffiliate($id_affiliate)
    {
        return Db::getInstance()->ExecuteS('SELECT r.*, c.`email`,COUNT(co.`id_connections`) as visits,
            IFNULL((
                SELECT ROUND(SUM(IFNULL(op.`amount`, 0) / cu.`conversion_rate`), 2)
                FROM `' . _DB_PREFIX_ . 'orders` o
                LEFT JOIN `' . _DB_PREFIX_ . 'order_payment` op ON o.reference = op.order_reference
                LEFT JOIN `' . _DB_PREFIX_ . 'currency` cu ON o.id_currency = cu.id_currency
                WHERE o.id_customer = c.id_customer
                AND o.valid
            ), 0) as total_purchase,
            IFNULL((
                SELECT COUNT(*)
                FROM `' . _DB_PREFIX_ . 'orders` o
                WHERE o.id_customer = c.id_customer
                AND o.valid
            ), 0) as valid_orders,
            IF(c.`firstname` IS NULL AND c.`lastname` IS NULL, "guest", CONCAT(c.`firstname`, \' \', c.`lastname`)) AS ref_name
            FROM `' . _DB_PREFIX_ . 'affiliate_referral` r
            LEFT JOIN `' . _DB_PREFIX_ . 'customer` c
                ON (c.id_customer = r.id_customer)
            LEFT JOIN `' . _DB_PREFIX_ . 'orders` o
                ON (o.id_customer = r.id_customer)
            LEFT JOIN `' . _DB_PREFIX_ . 'guest` g
                ON (c.`id_customer` = g.`id_customer`)
            LEFT JOIN `' . _DB_PREFIX_ . 'connections` co
                ON (g.`id_guest` = co.`id_guest`)
            WHERE r.id_affiliate = ' . (int) $id_affiliate . '
            GROUP BY r.id_customer');
    }

    public static function isEmailExists($email, $getId = false, $checkCustomer = true)
    {
        if (empty($email) || !Validate::isEmail($email)) {
            die(Tools::displayError('The email address is invalid.'));
        }

        if ($checkCustomer === true && Customer::customerExists($email)) {
            return false;
        }

        $result = Db::getInstance()->getRow('SELECT r.`id_affiliate_referral`
            FROM `' . _DB_PREFIX_ . 'affiliate_referral` r
            LEFT JOIN `' . _DB_PREFIX_ . 'affiliate` a
                ON (r.id_affiliate = a.id_affiliate AND r.id_customer = a.id_customer)
            LEFT JOIN `' . _DB_PREFIX_ . 'customer` c
                ON (r.id_customer = c.id_customer)
            WHERE c.`email` = \'' . pSQL($email) . '\'');

        if ($getId) {
            return (int) $result['id_affiliate_referral'];
        }
        return isset($result['id_affiliate_referral']);
    }

    public static function isGuestReferralExists($id_guest, $get_data = false)
    {
        if (!$id_guest) {
            return false;
        }

        $sql = 'SELECT *
        FROM `' . _DB_PREFIX_ . 'affiliate_referral`
        WHERE id_guest = ' . (int) $id_guest;

        if ($get_data) {
            return Db::getInstance()->getRow($sql);
        } else {
            return (bool) Db::getInstance()->getRow($sql);
        }
    }

    public static function countAffiliateRefs($id_affiliate)
    {
        if (!$id_affiliate) {
            return false;
        }

        return Db::getInstance()->getValue('SELECT COUNT(`id_affiliate_referral`)
            FROM `' . _DB_PREFIX_ . 'affiliate_referral` WHERE `id_affiliate` = ' . (int) $id_affiliate);
    }

    public static function isReferralExists($id_customer, $id_guest)
    {
        if (!$id_guest && !$id_customer) {
            return false;
        }

        return (bool) Db::getInstance()->getRow('SELECT *
            FROM `' . _DB_PREFIX_ . 'affiliate_referral`
            WHERE id_customer = ' . (int) $id_customer . '
            AND id_guest = ' . (int) $id_guest);
    }

    public static function getAllReferrals()
    {
        $sql = new DbQuery();
        $sql->select(self::$definition['primary']);
        $sql->from(self::$definition['table']);
        return Db::getInstance()->executeS($sql);
    }
    
    public function updateCustomerIdOfGuest($id_affiliate, $id_guest, $id_customer)
    {
        return DB::getInstance(_PS_USE_SQL_SLAVE_)->execute('UPDATE '._DB_PREFIX_.'affiliate_referral
                    SET `id_customer` = "'.pSQL($id_customer).'"
                    WHERE `id_affiliate` = '.(int)$id_affiliate.'
                    AND `id_guest` = '.(int)$id_guest);
    }
}

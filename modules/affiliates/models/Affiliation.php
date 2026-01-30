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

class Affiliation extends ObjectModel
{
    public $id_affiliate;

    public $id_customer = 0;

    public $id_guest = 0;

    public $ref_key;

    public $active = 1;

    public $approved = 0;

    public $rule = 0;

    public $alert_sent = 0;

    public $individual_voucher = 0;

    public $date_from;

    public $date_to = '0000-00-00 00:00:00';

    public $id_voucher = 0;

    public $level = 1;

    public static $definition = array(
        'table' => 'affiliate',
        'primary' => 'id_affiliate',
        'multilang' => false,
        'fields' => array(
            'id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_guest' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_voucher' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'rule' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'level' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'ref_key' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'individual_voucher' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'approved' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'alert_sent' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'date_from' => array('type' => self::TYPE_DATE, 'validate' => 'isString'),
            'date_to' => array('type' => self::TYPE_DATE, 'validate' => 'isString'),
        ),
    );

    public static function createTable()
    {
        // affiliate table
        Db::getInstance()->execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'affiliate');
        Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'affiliate(
            `id_affiliate`      int(11) unsigned NOT NULL auto_increment,
            `id_customer`       int(11) unsigned NOT NULL DEFAULT 0,
            `id_guest`          int(11) unsigned NOT NULL DEFAULT 0,
            `rule`              int(11) unsigned NOT NULL DEFAULT 0,
            `ref_key`           varchar(64),
            `active`            tinyint(1) DEFAULT 1,
            `level`             tinyint(1) DEFAULT 1,
            `approved`          tinyint(1) DEFAULT 0,
            `alert_sent`        TINYINT(2) NOT NULL DEFAULT 0,
            `individual_voucher` DECIMAL(20,6) NOT NULL DEFAULT 0,
            `id_voucher`        int(11) unsigned NOT NULL DEFAULT 0,
            `date_from`         text,
            `date_to`           text,
            PRIMARY KEY         (`id_affiliate`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8');

        // multishop table
        Db::getInstance()->execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'affiliate_shop');
        Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'affiliate_shop`(
            `id_shop`           int(10) unsigned NOT NULL,
            `id_group`          int(10) unsigned NOT NULL,
            PRIMARY KEY         (`id_shop`, `id_group`))
            ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8');
        return true;
    }

    public static function removeTable()
    {
        // Delete Tables
        Db::getInstance()->Execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'affiliate');
        Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'affiliate_shop`');
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
        if ($this->deleteByAffiliate($this->id_affiliate)) {
            return parent::delete();
        }
        return false;
    }

    public static function getAssocShops()
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT id_shop FROM ' . _DB_PREFIX_ . 'affiliate_shop');
        $final = array();
        if (isset($result)) {
            foreach ($result as $res) {
                $final[] = $res['id_shop'];
            }
        }
        return $final;
    }

    public static function existsTab($tab_class)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
            SELECT id_tab AS id
            FROM `' . _DB_PREFIX_ . 'tab` t
            WHERE LOWER(t.`class_name`) = \'' . pSQL($tab_class) . '\'');

        if (count($result) == 0) {
            return false;
        }
        return true;
    }

    public static function countAffiliates()
    {
        return (int) Db::getInstance()->getValue('SELECT COUNT(id_affiliate) FROM ' . _DB_PREFIX_ . 'affiliate WHERE approved = 0 AND alert_sent = 0');
    }

    public static function getAffiliateByCustomer($id_customer)
    {
        return Db::getInstance()->getRow('SELECT a.*, c.`firstname`, c.`lastname`, c.`email`
            FROM `' . _DB_PREFIX_ . 'affiliate` a
            LEFT JOIN `' . _DB_PREFIX_ . 'customer` c
            ON (a.id_customer = c.id_customer)
            WHERE a.`id_customer` = ' . (int) $id_customer);
    }

    public static function getIdByCustomer($id_customer)
    {
        return (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT id_affiliate
            FROM `' . _DB_PREFIX_ . 'affiliate`
            WHERE `id_customer` = ' . (int) $id_customer);
    }

    public static function SelfRef($id_customer, $id_guest = 0)
    {
        return Db::getInstance()->getValue('SELECT `ref_key`
            FROM `' . _DB_PREFIX_ . 'affiliate`
            WHERE `id_customer` = ' . (int) $id_customer . '
            OR `id_guest` = ' . (int) $id_guest);
    }

    public static function getAffiliateById($id_affiliate)
    {
        return Db::getInstance()->getRow('SELECT a.*, c.`firstname`, c.`lastname`, c.`email`
            FROM `' . _DB_PREFIX_ . 'affiliate` a
            LEFT JOIN `' . _DB_PREFIX_ . 'customer` c
            ON (a.id_customer = c.id_customer)
            WHERE a.`id_affiliate` = ' . (int) $id_affiliate);
    }

    public static function approveAffiliate($id_affiliate)
    {
        return Db::getInstance()->getRow('SELECT a.*, c.`firstname`, c.`lastname`, c.`email`, c.`id_shop`
            FROM `' . _DB_PREFIX_ . 'affiliate` a
            LEFT JOIN `' . _DB_PREFIX_ . 'customer` c
            ON (a.id_customer = c.id_customer)
            WHERE a.`alert_sent` = 0
            AND a.`id_affiliate` = ' . (int) $id_affiliate);
    }

    public static function getAffiliateByRef($ref)
    {
        return Db::getInstance()->getRow('SELECT a.*, c.`firstname`, c.`lastname`, c.`email`
            FROM `' . _DB_PREFIX_ . 'affiliate` a
            LEFT JOIN `' . _DB_PREFIX_ . 'customer` c
            ON (a.id_customer = c.id_customer)
            WHERE a.`ref_key` = "' . pSQL($ref) . '"');
    }

    public static function getRefKey($id_affiliate)
    {
        return (string) Db::getInstance()->getValue('SELECT `ref_key` FROM ' . _DB_PREFIX_ . 'affiliate WHERE `id_affiliate` = ' . (int) $id_affiliate);
    }

    public static function getRefKeyByCustomer($id_customer)
    {
        return (string) Db::getInstance()->getValue('SELECT `ref_key` FROM ' . _DB_PREFIX_ . 'affiliate WHERE `id_customer` = ' . (int) $id_customer);
    }

    public static function affiliateExists($ref, $id_shop = null)
    {
        if (!$id_shop) {
            $id_shop = Context::getContext()->shop->id;
        }

        return (bool) Db::getInstance()->getValue('SELECT a.*, c.*
            FROM `' . _DB_PREFIX_ . 'affiliate` a
            LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON (a.id_customer = c.id_customer)
            WHERE a.`ref_key` = "' . pSQL($ref) . '"
            AND c.id_shop = ' . (int) $id_shop);
    }

    public function deleteByAffiliate($id_affiliate)
    {
        return Db::getInstance()->Execute('DELETE FROM ' . _DB_PREFIX_ . 'affiliate_referral WHERE `id_affiliate` = ' . (int) $id_affiliate);
    }

    public static function cartRuleExists($id_cart_rule)
    {
        if (!CartRule::isFeatureActive()) {
            return false;
        }

        return (bool) Db::getInstance()->getValue('SELECT `id_cart_rule`
            FROM `' . _DB_PREFIX_ . 'cart_rule`
            WHERE `id_cart_rule` = ' . (int) $id_cart_rule);
    }

    public static function getOrderStateHistory($id_order)
    {
        $result = Db::getInstance()->ExecuteS('SELECT `id_order_state`
            FROM `' . _DB_PREFIX_ . 'order_history`
            WHERE `id_order` = ' . (int) $id_order);

        if ($result) {
            foreach ($result as &$res) {
                $res = array_shift($res);
            }
        }
        return $result;
    }

    public static function cleanAffiliateGroup($id_customer, $id_group)
    {
        return Db::getInstance()->delete('customer_group', 'id_customer = ' . (int) $id_customer . ' AND id_group = ' . (int) $id_group);
    }

    public static function addToAffiliateGroup($id_customer, $id_group)
    {
        $row = array('id_customer' => (int) $id_customer, 'id_group' => (int) $id_group);
        return Db::getInstance()->insert('customer_group', $row, false, true, Db::INSERT_IGNORE);
    }

    public static function restrictVoucherToAffiliateGroup($id_cart_rule, $id_group)
    {
        $row = array('id_cart_rule' => (int) $id_cart_rule, 'id_group' => (int) $id_group);
        return Db::getInstance()->insert('cart_rule_group', $row, false, true, Db::INSERT_IGNORE);
    }

    public static function restrictVoucherToAffiliateCustomers($id_cart_rule, $id_group)
    {
        $row = array('id_cart_rule' => (int) $id_cart_rule, 'id_group' => (int) $id_group);
        return Db::getInstance()->insert('cart_rule_group', $row, false, true, Db::INSERT_IGNORE);
    }

    public static function restrictVoucherToShop($id_cart_rule, $id_shop)
    {
        $row = array('id_cart_rule' => (int) $id_cart_rule, 'id_shop' => (int) $id_shop);
        return Db::getInstance()->insert('cart_rule_shop', $row, false, true, Db::INSERT_IGNORE);
    }

    public static function deleteShopRestriction($id_cart_rule)
    {
        return Db::getInstance()->delete('cart_rule_shop', 'id_cart_rule = ' . (int) $id_cart_rule);
    }

    public static function deleteGroupRestriction($id_cart_rule)
    {
        return Db::getInstance()->delete('cart_rule_group', 'id_cart_rule = ' . (int) $id_cart_rule);
    }

    public static function getAllCategories()
    {
        $result = Db::getInstance()->ExecuteS('SELECT `id_category` FROM `' . _DB_PREFIX_ . 'category`');

        if ($result) {
            foreach ($result as &$res) {
                $res = array_shift($res);
            }
        }
        return $result;
    }

    public static function addAffiliateGroupToCategory($id_category, $id_group)
    {
        if (!Affiliation::isAlreadyAdded($id_category, $id_group)) {
            return Db::getInstance()->insert('category_group', array('id_category' => (int) $id_category, 'id_group' => (int) $id_group));
        }
        return true;
    }

    public static function isAlreadyAdded($id_category, $id_group)
    {
        return (bool) Db::getInstance()->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'category_group`
            WHERE id_category = ' . (int) $id_category . ' AND id_group = ' . (int) $id_group);
    }

    public static function deleteAffiliateCustomerData($id_customer)
    {
        if (!$id_customer) {
            return false;
        }
        return (bool) Db::getInstance()->execute('DELETE af.*, ar.*, ainv.*, arew.*, apd.*, ap.*
            FROM ' . _DB_PREFIX_ . 'affiliate af
            LEFT JOIN `' . _DB_PREFIX_ . 'affiliate_referral` ar
                ON (af.id_affiliate = ar.id_affiliate)
            LEFT JOIN `' . _DB_PREFIX_ . 'affiliate_referral` ainv
                ON (af.id_affiliate = ainv.id_affiliate)
            LEFT JOIN `' . _DB_PREFIX_ . 'affiliate_reward` arew
                ON (af.id_affiliate = arew.id_affiliate)
            LEFT JOIN `' . _DB_PREFIX_ . 'affiliate_payment` ap
                ON (af.id_affiliate = ap.id_affiliate)
            LEFT JOIN `' . _DB_PREFIX_ . 'affiliate_payment_details` apd
                ON (af.id_affiliate = apd.id_affiliate)
            WHERE af.`id_customer` = ' . (int) $id_customer);
    }

    public static function getAllAffiliates()
    {
        $sql = new DbQuery();
        $sql->select('`id_customer`');
        $sql->from('affiliate');
        $return = Db::getInstance()->executeS($sql);
        if (!empty($return)) {
            //Reset the array
            foreach ($return as $key => $aff) {
                $return[$key] = $aff['id_customer'];
            }
        }
        return $return;
    }

    public static function getAffiliatesCollection()
    {
        return Db::getInstance()->executeS('SELECT a.*, SUM(b.`ord_reward_value`) + SUM(b.`reg_reward_value`) AS total_reward,
        SUM(b.`ord_reward_value`) AS order_reward,
        SUM(b.`reg_reward_value`) AS reg_reward,
        CONCAT(c.`firstname`, \' \', c.`lastname`) AS affiliate
        FROM `' . _DB_PREFIX_ . 'affiliate` a
        INNER JOIN `' . _DB_PREFIX_ . 'affiliate_reward` b ON (a.`id_customer` = b.`id_customer`)
        LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON (c.`id_customer` = a.`id_customer`)
        GROUP BY a.`id_affiliate`
        ORDER BY a.id_affiliate ASC');
    }

    public static function getWithdrawCollection($status = array('pending', 'cancelled'))
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('affiliate_payment');
        $sql->where('status IN ("'.implode('","', $status).'")');
        return Db::getInstance()->executeS($sql);
    }

    public static function getRefferalAffiliateId($id_customer)
    {
        $sql = new DbQuery();
        $sql->select('`id_affiliate`');
        $sql->from('affiliate_referral');
        $sql->where('`id_customer` = ' . (int) $id_customer);
        return Db::getInstance()->getValue($sql);
    }

    public static function countAffiliateLevel($id_aff)
    {
        $sql = new DbQuery();
        $sql->select('`level`');
        $sql->from('affiliate');
        $sql->where('`id_affiliate` = ' . (int) $id_aff);
        return Db::getInstance()->getValue($sql);
    }

    public static function alterPKey($table, $pk, $new_pk, $auto_increment = true)
    {
        $sql = 'ALTER TABLE ' . _DB_PREFIX_ . pSQL($table) . ' CHANGE ' . pSQL($pk) . ' ' . pSQL($new_pk) . ' INT( 11 ) NOT NULL';
        if ($auto_increment) {
            $sql .= ' AUTO_INCREMENT';
        }
        return (bool) Db::getInstance()->execute($sql);
    }

    public static function keyExists($table, $column)
    {
        $columns = Db::getInstance()->executeS('SELECT COLUMN_NAME FROM information_schema.columns
            WHERE table_schema = "' . _DB_NAME_ . '" AND table_name = "' . _DB_PREFIX_ . pSQL($table) . '"');

        if (isset($columns) && $columns) {
            foreach ($columns as $col) {
                if ($col['COLUMN_NAME'] == $column) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function getAffiliateVuchers()
    {
        $sql = new DbQuery();
        $sql->select('`id_voucher`');
        $sql->from(self::$definition['table']);
        $result = Db::getInstance()->executeS($sql);

        $vouchers = array(
            (int) Configuration::get(
                'ID_AFFILIATE_DISCOUNT_RULE',
                null,
                Context::getContext()->shop->id_shop_group,
                Context::getContext()->shop->id
            ));
        if (!empty($result)) {
            //Reset the array
            foreach ($result as $voucher) {
                $vouchers[] = (int)$voucher['id_voucher'];
            }
        }
        return $vouchers;
    }

    public static function tableExists($table)
    {
        return (bool)Db::getInstance()->executeS('SHOW TABLES LIKE \''._DB_PREFIX_.pSQL($table).'\'');
    }

    public static function createPmTables($table)
    {
        $sql = '';
        switch ($table) {
            case 'payment_method':
                $sql = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'payment_method(
                    `id_payment_method`             int(11) NOT NULL auto_increment,
                    `payment_name`                  varchar(128),
                    `date_add`                      datetime default NULL,
                    `date_upd`                      datetime default NULL,
                    PRIMARY KEY                     (`id_payment_method`)
                    ) ENGINE=InnoDB                 AUTO_INCREMENT=4 DEFAULT CHARSET=utf8';
                break;
            case 'payment_method_lang':
                $sql = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'payment_method_lang(
                    `id_payment_method`             int(11) NOT NULL,
                    `id_lang`                       int(11) default 1,
                    `payment_description`           varchar(250),
                    PRIMARY KEY                     (`id_payment_method`, `id_lang`)
                    ) ENGINE=InnoDB                 DEFAULT CHARSET=utf8';
                break;
        }

        $return = true;
        if (!empty($sql)) {
            $return &= Db::getInstance()->execute($sql);
        }
        return $return;
    }
}

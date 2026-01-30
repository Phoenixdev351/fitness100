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

class AffiliateInvitations extends ObjectModel
{
    public $id_affiliate_invitation;

    public $id_affiliate;

    public $id_affiliate_referral;

    public $email;

    public $lastname;

    public $firstname;

    public $id_customer;

    public $date_add = '0000-00-00 00:00:00';

    public $date_upd = '0000-00-00 00:00:00';

    public static $definition = array(
        'table' => 'affiliate_invitation',
        'primary' => 'id_affiliate_invitation',
        'multilang' => false,
        'fields' => array(
            'id_affiliate' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_affiliate_referral' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'email' => array('type' => self::TYPE_STRING, 'validate' => 'isEmail', 'required' => true, 'size' => 255),
            'lastname' => array('type' => self::TYPE_STRING, 'validate' => 'isName', 'required' => true, 'size' => 128),
            'firstname' => array('type' => self::TYPE_STRING, 'validate' => 'isName', 'required' => true, 'size' => 128),
            'id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isString'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isString'),
        ),
    );

    public static function createTable()
    {
        // affiliate table
        Db::getInstance()->execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'affiliate_invitation');
        Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'affiliate_invitation(
            `id_affiliate_invitation`     int(11) unsigned NOT NULL auto_increment,
            `id_affiliate`      int(11) unsigned NOT NULL default 0,
            `id_affiliate_referral`       int(11) unsigned NOT NULL default 0,
            `email`             varchar(255),
            `lastname`          varchar(128),
            `firstname`         varchar(128),
            `id_customer`       int(11) unsigned NOT NULL default 0,
            `date_add`          text,
            `date_upd`          text,
            PRIMARY KEY         (`id_affiliate_invitation`)
            ) ENGINE=InnoDB     AUTO_INCREMENT=1 DEFAULT CHARSET=utf8');
        return true;
    }

    public static function removeTable()
    {
        // Delete Tables
        Db::getInstance()->Execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'affiliate_invitation');
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

    public static function getPendingInvitations($id_customer)
    {
        return Db::getInstance()->ExecuteS('SELECT inv.*, a.`id_affiliate`
            FROM `' . _DB_PREFIX_ . 'affiliate_invitation` inv
            LEFT JOIN `' . _DB_PREFIX_ . 'affiliate` a
                ON (inv.id_affiliate = a.id_affiliate)
            WHERE inv.id_affiliate_referral = 0
            AND inv.`id_customer` = ' . (int) $id_customer);
    }

    public static function getAffiliateByCustomer($id_customer)
    {
        return Db::getInstance()->getRow('SELECT a.*, c.`firstname`, c.`lastname`, c.`email`
            FROM `' . _DB_PREFIX_ . 'affiliate` a
            LEFT JOIN `' . _DB_PREFIX_ . 'customer` c
            ON (a.id_customer = c.id_customer)
            WHERE a.`id_customer` = ' . (int) $id_customer);
    }

    public static function getAffiliateByRef($ref)
    {
        return Db::getInstance()->getRow('SELECT a.*, c.`firstname`, c.`lastname`, c.`email`
            FROM `' . _DB_PREFIX_ . 'affiliate` a
            LEFT JOIN `' . _DB_PREFIX_ . 'customer` c
            ON (a.id_customer = c.id_customer)
            WHERE a.`ref_key` = "' . pSQL($ref) . '"');
    }

    public static function getRefIdByEmail($email)
    {
        if (empty($email) || !Validate::isEmail($email)) {
            die(Tools::displayError('The email address is invalid.'));
        }

        return Db::getInstance()->getValue('
            SELECT `id_affiliate_referral`
            FROM `' . _DB_PREFIX_ . 'affiliate_invitation`
            WHERE `email` = \'' . pSQL($email) . '\'');
    }

    public static function getRefKey($id_affiliate)
    {
        return (string) Db::getInstance()->getValue('SELECT `ref_key` FROM ' . _DB_PREFIX_ . 'affiliate WHERE `id_affiliate` = ' . (int) $id_affiliate);
    }

    public static function getRefKeyByCustomer($id_customer)
    {
        return (string) Db::getInstance()->getValue('SELECT `ref_key` FROM ' . _DB_PREFIX_ . 'affiliate WHERE `id_customer` = ' . (int) $id_customer);
    }

    public static function affiliateExists($ref)
    {
        return (bool) Db::getInstance()->getValue('SELECT * FROM `' . _DB_PREFIX_ . 'affiliate` WHERE `ref_key` = "' . pSQL($ref) . '"');
    }

    public function deleteByAffiliate($id_affiliate)
    {
        return Db::getInstance()->Execute('DELETE FROM ' . _DB_PREFIX_ . 'affiliate_referral WHERE `id_affiliate` = ' . (int) $id_affiliate);
    }

    public static function isEmailExists($email, $get_id = false, $check_customer = true)
    {
        if (empty($email) || !Validate::isEmail($email)) {
            die(Tools::displayError('The email address is invalid.'));
        }

        if ($check_customer === true && Customer::customerExists($email)) {
            return false;
        }
        $result = Db::getInstance()->getRow('
            SELECT s.`id_affiliate_invitation`, s.`email`
            FROM `' . _DB_PREFIX_ . 'affiliate_invitation` s
            WHERE s.`email` = \'' . pSQL($email) . '\'');
        if ($get_id) {
            return (int) $result['id_affiliate_invitation'];
        }
        return isset($result['id_affiliate_invitation']);
    }

    public static function isSponsorReferral($id_customer, $id_affiliate_invitation)
    {
        if (!(int) $id_customer || !(int) $id_affiliate_invitation) {
            return false;
        }

        return (bool) Db::getInstance()->getRow('
            SELECT inv.`id_affiliate_invitation`
            FROM `' . _DB_PREFIX_ . 'affiliate_invitation` inv
            WHERE inv.`id_customer` = ' . (int) $id_customer . '
            AND inv.`id_affiliate_invitation` = ' . (int) $id_affiliate_invitation);
    }

    public static function isIdExists($id_affiliate_invitation)
    {
        if (!(int) $id_affiliate_invitation) {
            return false;
        }

        return (bool) Db::getInstance()->getRow('
            SELECT inv.`id_affiliate_invitation`
            FROM `' . _DB_PREFIX_ . 'affiliate_invitation` inv
            WHERE inv.id_affiliate_referral = 0
            AND inv.`id_affiliate_invitation` = ' . (int) $id_affiliate_invitation);
    }

    public static function getGuestId($idCustomer)
    {
        if (!Validate::isUnsignedId($idCustomer)) {
            return false;
        }

        return (int) Db::getInstance()->getValue('SELECT `id_guest`
        FROM `' . _DB_PREFIX_ . 'guest` WHERE `id_customer` = ' . (int) $idCustomer);
    }

    public static function getInviteeByEmail($email)
    {
        if (!Validate::isEmail($email)) {
            return false;
        } else {
            return Db::getInstance()->getRow('
                SELECT * FROM `' . _DB_PREFIX_ . 'affiliate_invitation` WHERE `email` = \'' . pSQL($email) . '\'');
        }
    }

    public static function updateInviteeByEmail($email, $id_affiliate_referral)
    {
        if (!$id_affiliate_referral || !Validate::isEmail($email)) {
            return false;
        } else {
            return Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'affiliate_invitation`
                SET id_affiliate_referral = ' . (int) $id_affiliate_referral . '
                WHERE `email` = \'' . pSQL($email) . '\'');
        }
    }
}

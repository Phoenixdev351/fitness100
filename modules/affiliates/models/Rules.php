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

class Rules extends ObjectModel
{
    public $id;

    public $id_affiliate_rules;

    public $min_nb_ref;

    public $max_nb_ref;

    public $reg_reward_value;

    public $active = 0;

    public $affiliate_level = 1;

    public $parent_reward_value = 0;

    public static $definition = array(
        'table' => 'affiliate_rules',
        'primary' => 'id_affiliate_rules',
        'multilang' => false,
        'fields' => array(
            'affiliate_level' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'min_nb_ref' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'max_nb_ref' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'parent_reward_value' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'reg_reward_value' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
        ),
    );

    public static function createTable()
    {
        // rules table
        Db::getInstance()->execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'affiliate_rules');
        Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'affiliate_rules(
            `id_affiliate_rules`           int(11) unsigned NOT NULL auto_increment,
            `min_nb_ref`        int(11) unsigned NOT NULL default 10,
            `max_nb_ref`        int(11) unsigned NOT NULL default 0,
            `reg_reward_value`  DECIMAL(20,6),
            `parent_reward_value` int(10) NOT NULL,
            `affiliate_level`   tinyint(1) default 1,
            `active`            tinyint(1) default 0,
            PRIMARY KEY         (`id_affiliate_rules`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8');
        return true;
    }

    public static function removeTable()
    {
        return (bool) Db::getInstance()->Execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'affiliate_rules');
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

    public static function countRules()
    {
        return (int) Db::getInstance()->getValue('SELECT COUNT(`id_affiliate_rules`) FROM `' . _DB_PREFIX_ . 'affiliate_rules`');
    }

    public static function getActiveRules()
    {
        return Db::getInstance()->ExecuteS('SELECT *
            FROM `' . _DB_PREFIX_ . 'affiliate_rules` WHERE `active` = 1 ORDER BY id_affiliate_rules ASC');
    }

    public static function getRuleById($id_affiliate_rules)
    {
        return Db::getInstance()->getRow('SELECT *
            FROM `' . _DB_PREFIX_ . 'affiliate_rules`
            WHERE `active` = 1
            AND `id_affiliate_rules` = ' . (int) $id_affiliate_rules);
    }

    public static function getApplicableRuleId($nbr_referrals, $lvl)
    {
        if (!$nbr_referrals) {
            return false;
        }

        return Db::getInstance()->getValue('SELECT MAX(`id_affiliate_rules`)
            FROM `' . _DB_PREFIX_ . 'affiliate_rules`
            WHERE `active` = 1
            AND `min_nb_ref` <= ' . (int) $nbr_referrals . '
            AND `affiliate_level` = ' . (int) $lvl);
    }

    public static function getAffiliateLevelExistance($id_lvl)
    {
        $sql = new DbQuery();
        $sql->select('`id_affiliate_rules`');
        $sql->from('affiliate_rules');
        $sql->where('`affiliate_level` = ' . (int) $id_lvl);
        return Db::getInstance()->getValue($sql);
    }
}

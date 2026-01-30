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

class Levels extends ObjectModel
{
    public $id;

    public $id_affiliate_levels;

    public $reward_type;

    public $reward_value;

    public $is_tax = 0;

    public $is_default = 1;

    public $min_order_value = 0.0;

    public $active = 0;

    public $parent_reward;

    public $value_type;

    public $level;

    public static $definition = array(
        'table' => 'affiliate_levels',
        'primary' => 'id_affiliate_levels',
        'multilang' => false,
        'fields' => array(
            'reward_type' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'value_type' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'level' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'parent_reward' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'reward_value' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'is_tax' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'is_default' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'min_order_value' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
        ),
    );

    public static function createTable()
    {
        // levels table
        Db::getInstance()->execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'affiliate_levels');
        Db::getInstance()->execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'affiliate_levels_categories');
        Db::getInstance()->execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'affiliate_levels_products');
        Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'affiliate_levels(
            `id_affiliate_levels`          int(11) unsigned NOT NULL auto_increment,
            `reward_type`       int(11) unsigned NOT NULL default 0,
            `reward_value`      DECIMAL(20,6),
            `parent_reward`     int(11) unsigned NOT NULL default 0,
            `is_tax`            tinyint(1) default 0,
            `value_type`        tinyint(1) default 0,
            `is_default`        tinyint(1) default 0,
            `min_order_value`   DECIMAL(20,6) default 0.00,
            `active`            tinyint(1) default 1,
            `level`            tinyint(1) default 1,
            PRIMARY KEY         (`id_affiliate_levels`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '     AUTO_INCREMENT=1 DEFAULT CHARSET=utf8');
        Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'affiliate_levels_categories` (
        `id_affiliate_levels` int(10) NOT NULL,
        `id_category` int(10) NOT NULL,
        `value` DECIMAL(20,6),
        PRIMARY KEY (`id_affiliate_levels`, `id_category`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;');
        Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'affiliate_levels_products` (
        `id_affiliate_levels` int(10) NOT NULL,
        `id_product` int(10) NOT NULL,
        `value` DECIMAL(20,6),
        PRIMARY KEY (`id_affiliate_levels`, `id_product`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;');
        return true;
    }

    public static function removeTable()
    {
        Db::getInstance()->execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'affiliate_levels');
        Db::getInstance()->execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'affiliate_levels_categories');
        Db::getInstance()->execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'affiliate_levels_products');
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

    public static function countLevels()
    {
        return (int) Db::getInstance()->getValue('SELECT COUNT(`id_affiliate_levels`) FROM `' . _DB_PREFIX_ . 'affiliate_levels`');
    }

    public static function getOneLevel()
    {
        return Db::getInstance()->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'affiliate_levels`
            WHERE `id_affiliate_levels` = (SELECT MIN(`id_affiliate_levels`) FROM `' . _DB_PREFIX_ . 'affiliate_levels`)');
    }

    public static function getDefaultLevel($lvl)
    {
        return Db::getInstance()->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'affiliate_levels`
            WHERE active = 1
            AND `level` = ' . (int) $lvl);
    }

    public static function dumpCurrentData($id)
    {
        Db::getInstance()->delete('affiliate_levels_categories', 'id_affiliate_levels = ' . (int) $id);
        Db::getInstance()->delete('affiliate_levels_products', 'id_affiliate_levels = ' . (int) $id);
    }

    public static function populateTable($table, $key, $id, $raw, $class)
    {
        if (is_array($raw) && ($class == 'category' || $class == 'product')) {
            foreach ($raw as $k => $row) {
                Db::getInstance()->insert(
                    $table,
                    array(
                        'id_affiliate_levels' => (int) $id,
                        $key => $k,
                        'value' => $row)
                );
            }
        } elseif (is_array($raw) && $class = 'null') {
            foreach ($raw as $row) {
                Db::getInstance()->insert(
                    $table,
                    array(
                        'id_affiliate_levels' => (int) $id,
                        $key => $row)
                );
            }
        }
        $last_id = (int) Db::getInstance()->Insert_ID();
        return $last_id;
    }

    public static function needleCheck($table, $key, $id_key, $id_obj, $select)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `' . pSQL($select) . '`
            FROM `' . _DB_PREFIX_ . $table . '`
            WHERE `' . pSQL($key) . '` = ' . (int) $id_key . '
			AND `id_affiliate_levels` = ' . (int) $id_obj);
    }

    public static function getCollection($id, $key, $table)
    {
        $sql = new DbQuery();
        $sql->select('`' . pSQL($key) . '`');
        $sql->from($table);
        $sql->where('`id_affiliate_levels` = ' . (int) $id);
        $return = Db::getInstance()->executeS($sql);
        return $return;
    }

    public static function getProductHighestReward($id_product)
    {
        // get product based reward - if exists
        $sql = new DbQuery();
        $sql->select('l.*, MAX(lp.value) as value');
        $sql->from(self::$definition['table'], 'l');
        $sql->leftJoin(
            self::$definition['table'] .'_products',
            'lp',
            'l.'.self::$definition['primary'] .'= lp.'.self::$definition['primary']
        );
        $sql->where('l.active = 1');
        $sql->where('lp.id_product = '.(int)$id_product);

        $preward = Db::getInstance()->getRow($sql);

        // get category based reward - if exists
        $sql2 = new DbQuery();
        $sql2->select('l.*, lc.id_category, MAX(lc.value) as value');
        $sql2->from(self::$definition['table'], 'l');
        $sql2->leftJoin(
            self::$definition['table'] .'_categories',
            'lc',
            'l.'.self::$definition['primary'] .'= lc.'.self::$definition['primary']
        );
        $sql2->where('l.active = 1');
        if (isset($preward) && isset($preward['value'])) {
            $sql2->where('value >'.(float)$preward['value']);
        }
        $sql2->where('lc.id_category IN (SELECT id_category FROM `'._DB_PREFIX_.'category_product` WHERE id_product = '.(int)$id_product.')');

        $creward = Db::getInstance()->getRow($sql2);
        if ((isset($preward['value']) && isset($creward['value']) && $creward['value'] > $preward['value'])
        || (!isset($preward['id_affiliate_levels']) && $creward['id_affiliate_levels'])) {
            return $creward;
        }
        return $preward;
    }

    public static function getCategoryValue($id_affiliate_levels, $categories)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `value`
        FROM `' . _DB_PREFIX_ . 'affiliate_levels_categories`
		WHERE `id_category` IN (' . implode(',', array_map('intval', $categories)) . ')
		AND`id_affiliate_levels` = ' . (int) $id_affiliate_levels);
    }

    public static function getAffiliateLevelExistance($id_lvl)
    {
        $sql = new DbQuery();
        $sql->select('`id_affiliate_levels`');
        $sql->from('affiliate_levels');
        $sql->where('`level` = ' . (int) $id_lvl);
        return Db::getInstance()->getValue($sql);
    }

    public static function getLevels($active = true)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(self::$definition['table']);
        $sql->where('active = '.(int)$active);
        $sql->orderBy('level ASC');
        $levels = Db::getInstance()->executeS($sql);

        $id_lang = (int)Context::getContext()->language->id;
        if (isset($levels) && $levels) {
            foreach ($levels as &$level) {
                switch ((int)$level['reward_type']) {
                    case 2://products
                        if (($products = self::getTypeLevels($level['id_affiliate_levels']))) {
                            foreach ($products as &$product) {
                                $p = new Product($product['id_product'], false, $id_lang);
                                $product['name'] = $p->name;
                                $product['link'] = $p->getLink();
                            }
                            $level['products'] = $products;
                        }
                        break;
                    case 3://categories
                        if (($categories = $level['categories'] = self::getTypeLevels($level['id_affiliate_levels'], 'categories'))) {
                            foreach ($categories as &$category) {
                                $c = new Category($category['id_category'], $id_lang);
                                $category['name'] = $c->name;
                                $category['link'] = $c->getLink();
                            }
                            $level['categories'] = $categories;
                        }
                        break;
                }
            }
        }
        return $levels;
    }

    public static function getTypeLevels($id_level, $type = 'products')
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(self::$definition['table'].'_'.pSQL($type));
        $sql->where(self::$definition['primary'] .' = '.(int)$id_level);
        return Db::getInstance()->executeS($sql);
    }
}

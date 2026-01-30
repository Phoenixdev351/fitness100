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

class AffiliationBanners extends ObjectModel
{
    public $id;

    public $id_affiliate_banners;

    public $title;

    public $path_url;

    public $href;

    public $active = 0;

    public static $definition = array(
        'table' => 'affiliate_banners',
        'primary' => 'id_affiliate_banners',
        'multilang' => false,
        'fields' => array(
            'title' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'path_url' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'href' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
        ),
    );

    public static function createTable()
    {
        // levels table
        Db::getInstance()->execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'affiliate_banners');
        Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'affiliate_banners(
            `id_affiliate_banners` int(11) unsigned NOT NULL auto_increment,
            `title` varchar(255),
            `path_url` varchar(255),
            `href` varchar(255),
            `active` tinyint(1) default 1,
            PRIMARY KEY (`id_affiliate_banners`)
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8');
        return true;
    }

    public static function removeTable()
    {
        return (bool)Db::getInstance()->execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'affiliate_banners');
    }

    public static function getAllBanners()
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('affiliate_banners');
        $sql->where('`active` > 0');
        return Db::getInstance()->executeS($sql);
    }
}

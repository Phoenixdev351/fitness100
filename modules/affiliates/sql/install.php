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

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'affiliate_referral(
    `id_affiliate_referral`         int(11) unsigned NOT NULL auto_increment,
    `id_affiliate`                  int(11) unsigned NOT NULL default 0,
    `id_customer`                   int(11) unsigned NOT NULL default 0,
    `id_guest`                      int(11) unsigned NOT NULL default 0,
    `active`                        tinyint(1) default 1,
    `approved`                      tinyint(1) default 1,
    `source`                        text,
    `date_add`                      text,
    `date_from`                     text,
    `date_to`                       text,
    PRIMARY KEY                     (`id_affiliate_referral`)
    ) ENGINE=InnoDB                 AUTO_INCREMENT=1 DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'affiliate_invitation(
    `id_affiliate_invitation`       int(11) unsigned NOT NULL auto_increment,
    `id_affiliate`                  int(11) unsigned NOT NULL default 0,
    `id_affiliate_referral`         int(11) unsigned NOT NULL default 0,
    `email`                         varchar(255),
    `lastname`                      varchar(128),
    `firstname`                     varchar(128),
    `id_customer`                   int(11) unsigned NOT NULL default 0,
    `date_add`                      text,
    `date_upd`                      text,
    PRIMARY KEY                     (`id_affiliate_invitation`)
    ) ENGINE=InnoDB                 AUTO_INCREMENT=1 DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'affiliate(
    `id_affiliate`                  int(11) unsigned NOT NULL auto_increment,
    `id_customer`                   int(11) unsigned NOT NULL DEFAULT 0,
    `id_guest`                      int(11) unsigned NOT NULL DEFAULT 0,
    `rule`                          int(11) unsigned NOT NULL DEFAULT 0,
    `ref_key`                       varchar(64),
    `active`                        tinyint(1) DEFAULT 1,
    `level`                         tinyint(1) DEFAULT 1,
    `approved`                      tinyint(1) DEFAULT 0,
    `alert_sent`                    TINYINT(2) NOT NULL DEFAULT 0,
    `individual_voucher`            DECIMAL(20,6) NOT NULL DEFAULT 0,
    `id_voucher`                    int(11) unsigned NOT NULL DEFAULT 0,
    `date_from`                     text,
    `date_to`                       text,
    PRIMARY KEY                     (`id_affiliate`)
    ) ENGINE=InnoDB                 AUTO_INCREMENT=1 DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'affiliate_banners(
    `id_affiliate_banners` int(11)  unsigned NOT NULL auto_increment,
    `title`                         varchar(255),
    `path_url`                      varchar(255),
    `href`                          varchar(255),
    `active`                        tinyint(1) default 1,
    PRIMARY KEY                     (`id_affiliate_banners`)
    ) ENGINE=InnoDB                 AUTO_INCREMENT=1 DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'affiliate_levels(
    `id_affiliate_levels`           int(11) unsigned NOT NULL auto_increment,
    `reward_type`                   int(11) unsigned NOT NULL default 0,
    `reward_value`                  DECIMAL(20,6),
    `parent_reward`                 int(11) unsigned NOT NULL default 0,
    `is_tax`                        tinyint(1) default 0,
    `value_type`                    tinyint(1) default 0,
    `is_default`                    tinyint(1) default 0,
    `min_order_value`               DECIMAL(20,6) default 0.00,
    `active`                        tinyint(1) default 1,
    `level`                         tinyint(1) default 1,
    PRIMARY KEY                     (`id_affiliate_levels`)
    ) ENGINE='._MYSQL_ENGINE_.'     AUTO_INCREMENT=1 DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'affiliate_levels_categories(
    `id_affiliate_levels`           int(10) NOT NULL,
    `id_category`                   int(10) NOT NULL,
    `value`                         DECIMAL(20,6),
    PRIMARY KEY                     (`id_affiliate_levels`, `id_category`)
    ) ENGINE='._MYSQL_ENGINE_.'     DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'affiliate_levels_products (
    `id_affiliate_levels`           int(10) NOT NULL,
    `id_product`                    int(10) NOT NULL,
    `value`                         DECIMAL(20,6),
    PRIMARY KEY                     (`id_affiliate_levels`, `id_product`)
    ) ENGINE='._MYSQL_ENGINE_.'     DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'affiliate_payment(
    `id_affiliate_payment`          int(11) unsigned NOT NULL auto_increment,
    `id_affiliate_reward`           int(11) unsigned NOT NULL,
    `id_affiliate`                  int(11) unsigned NOT NULL,
    `type`                          int(11) unsigned NOT NULL default 0,
    `details`                       text,
    `upd_date`                      text,
    `status`                        text,
    `requested_date`                text,
    PRIMARY KEY                     (`id_affiliate_payment`)
    ) ENGINE=InnoDB                 AUTO_INCREMENT=1 DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'affiliate_payment_details(
    `id_affiliate_payment_details`  int(11) unsigned NOT NULL auto_increment,
    `id_affiliate`                  int(11) unsigned NOT NULL,
    `type`                          int(11) unsigned NOT NULL default 0,
    `details`                       text,
    `status`                        tinyint(2) DEFAULT 1,
    `date_add`                      text,
    `upd_date`                      text,
    PRIMARY KEY                     (`id_affiliate_payment_details`)
    ) ENGINE=InnoDB                 AUTO_INCREMENT=1 DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'affiliate_reward(
    `id_affiliate_reward`           int(11) unsigned NOT NULL auto_increment,
    `id_affiliate`                  int(11) unsigned NOT NULL default 0,
    `id_affiliate_referral`         int(11) unsigned NOT NULL default 0,
    `id_customer`                   int(11) unsigned NOT NULL default 0,
    `id_guest`                      int(11) unsigned NOT NULL default 0,
    `id_order`                      int(11) unsigned NOT NULL default 0,
    `reward_by_reg`                 tinyint(1) default 0,
    `reward_by_ord`                 tinyint(1) default 0,
    `pay_request`                   text,
    `is_paid`                       tinyint(1) default 0,
    `reg_reward_value`              DECIMAL(20,6),
    `ord_reward_value`              DECIMAL(20,6),
    `status`                        text,
    `reward_date`                   text,
    PRIMARY KEY                     (`id_affiliate_reward`)
    ) ENGINE=InnoDB                 AUTO_INCREMENT=1 DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'affiliate_rules(
    `id_affiliate_rules`            int(11) unsigned NOT NULL auto_increment,
    `min_nb_ref`                    int(11) unsigned NOT NULL default 10,
    `max_nb_ref`                    int(11) unsigned NOT NULL default 0,
    `reg_reward_value`              DECIMAL(20,6),
    `parent_reward_value`           int(10) NOT NULL,
    `affiliate_level`               tinyint(1) default 1,
    `active`                        tinyint(1) default 0,
    PRIMARY KEY                     (`id_affiliate_rules`)
    ) ENGINE=InnoDB                 AUTO_INCREMENT=1 DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'payment_method(
    `id_payment_method`             int(11) NOT NULL auto_increment,
    `payment_name`                  varchar(128),
    `date_add`                      datetime default NULL,
    `date_upd`                      datetime default NULL,
    PRIMARY KEY                     (`id_payment_method`)
    ) ENGINE=InnoDB                 AUTO_INCREMENT=4 DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'payment_method_lang(
    `id_payment_method`             int(11) NOT NULL,
    `id_lang`                       int(11) default 1,
    `payment_description`           varchar(250),
    PRIMARY KEY                     (`id_payment_method`, `id_lang`)
    ) ENGINE=InnoDB                 DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'affiliate_shop(
    `id_affiliate`                  int(11) NOT NULL,
    `id_shop`                       int(11) default 1,
    PRIMARY KEY                     (`id_affiliate`, `id_shop`)
    ) ENGINE=InnoDB                 DEFAULT CHARSET=utf8';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}

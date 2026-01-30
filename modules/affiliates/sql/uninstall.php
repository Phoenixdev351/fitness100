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
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'affiliate_referral`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'affiliate_invitation`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'affiliate`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'affiliate_banners`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'affiliate_levels`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'affiliate_levels_categories`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'affiliate_levels_products`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'affiliate_payment`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'affiliate_payment_details`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'affiliate_reward`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'affiliate_rules`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'payment_method`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'payment_method_lang`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'affiliate_shop`';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}

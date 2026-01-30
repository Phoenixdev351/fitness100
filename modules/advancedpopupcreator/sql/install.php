<?php
/**
*
* This product is licensed for one customer to use in one installation. Site developer has the
* right to modify this module to suit their needs, but can not redistribute the module in
* whole or in part. Any other use of this module constitutes a violation of the user agreement.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade module to newer
* versions in the future.
*
*  @author    idnovate.com <info@idnovate.com>
*  @copyright 2020 idnovate.com
*  @license   See above
*/

$sql = array();

$sql[] = "
	CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."advancedpopup` (
        `id_advancedpopup` int unsigned NOT NULL auto_increment,
        `id_shop` int unsigned NOT NULL DEFAULT '0',
        `name` VARCHAR(150) NOT NULL,
        `date_init` DATETIME NULL DEFAULT NULL,
        `date_end` DATETIME NULL DEFAULT NULL,
        `schedule` TEXT NULL,
        `secs_to_display` TEXT NULL,
        `secs_to_display_cart` TEXT NULL,
        `secs_to_close` TEXT NULL,
        `dont_display_again` tinyint(1) NOT NULL,
        `image_link_target` VARCHAR(32) NOT NULL,
        `priority` int NULL DEFAULT '0',
        `locked` tinyint(1) NOT NULL,
        `show_customer_newsletter` tinyint(1) NULL DEFAULT '0',
        `show_customer_not_newsletter` tinyint(1) NULL DEFAULT '0',
        `show_on_view_page_nbr` int NULL DEFAULT '0',
        `show_every_nbr_hours` TEXT NULL,
        `nb_products` TEXT NULL,
        `nb_products_comparator` tinyint(1) NULL DEFAULT '0',
        `back_opacity_value` decimal(10,2) NULL DEFAULT '0.00',
        `controller_exceptions` TEXT NULL,
        `groups` TEXT NULL,
        `genders` TEXT NULL,
        `customers` TEXT NULL,
        `products` TEXT NULL,
        `countries` TEXT NULL,
        `zones` TEXT NULL,
        `categories` TEXT NULL,
        `categories_selected` TEXT NULL,
        `manufacturers` TEXT NULL,
        `suppliers` TEXT NULL,
        `cms` TEXT NULL,
        `languages` TEXT NULL,
        `attributes` TEXT NULL,
        `features` TEXT NULL,
        `active` tinyint(1) NULL DEFAULT '0',
        `display_on_load` tinyint(1) NOT NULL,
        `display_after_cart` tinyint(1) NOT NULL,
        `display_on_exit` tinyint(1) NOT NULL,
        `display_on_click` tinyint(1) NOT NULL,
        `display_on_click_selector` VARCHAR(150) NULL,
        `close_on_background` tinyint(1) NOT NULL,
        `blur_background` tinyint(1) NOT NULL,
        `color_background` varchar(32) NULL,
        `open_effect` VARCHAR(32) NOT NULL,
        `position` INT(11) NOT NULL,
        `cart_amount` tinyint(1) NOT NULL,
        `cart_amount_from` decimal(20,2),
        `cart_amount_to` decimal(20,2),
        `display_mobile` tinyint(1) NOT NULL,
        `display_tablet` tinyint(1) NOT NULL,
        `display_desktop` tinyint(1) NOT NULL,
        `display_ip_string` TEXT NULL,
        `product_stock` tinyint(1) NOT NULL,
        `product_stock_from` INT(10) NULL,
        `product_stock_to` INT(10) NULL,
        `date_add` DATETIME NOT NULL,
        `date_upd` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id_advancedpopup`),
    KEY `id_advancedpopup` (`id_advancedpopup`)
    ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8;
    ";

$sql[] = "
    CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."advancedpopup_lang` (
        `id_advancedpopup` int unsigned NOT NULL,
        `id_lang` int unsigned NOT NULL,
        `css_class` VARCHAR(150) NULL,
        `css` TEXT NULL DEFAULT NULL,
        `content` TEXT NULL DEFAULT NULL,
        `image_background` VARCHAR(150) NULL,
        `image` VARCHAR(150) NULL DEFAULT NULL,
        `image_link` VARCHAR(250) NULL,
        `popup_height` TEXT NULL,
        `popup_width` TEXT NULL,
        `popup_padding` int NULL,
        `responsive_min` int NULL,
        `responsive_max` int NULL,
        `display_url_string` VARCHAR(150) NULL,
        `display_referrer_string` VARCHAR(150) NULL,
    PRIMARY KEY (`id_advancedpopup`, `id_lang`),
    KEY `id_advancedpopup` (`id_advancedpopup`)
    ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8;
	";

foreach ($sql as $query) {
    Db::getInstance()->execute($query);
}

return true;

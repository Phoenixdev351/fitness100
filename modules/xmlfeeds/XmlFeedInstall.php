<?php
/**
 * 2010-2022 Bl Modules.
 *
 * If you wish to customize this module for your needs,
 * please contact the authors first for more information.
 *
 * It's not allowed selling, reselling or other ways to share
 * this file or any other module files without author permission.
 *
 * @author    Bl Modules
 * @copyright 2010-2022 Bl Modules
 * @license
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class XmlFeedInstall
{
    const BLMOD_XML_FEEDS_COLUMNS = 72;

    public function installModuleSql()
    {
        $sql_blmod_block = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'blmod_xml_block
			(
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`name` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
				`value` varchar(3000) CHARACTER SET utf8 DEFAULT NULL,
				`status` tinyint(1) NOT NULL DEFAULT "1",
				`category` int(11) DEFAULT NULL,
				PRIMARY KEY (`id`)
			)';
        $sql_blmod_block_res = Db::getInstance()->Execute($sql_blmod_block);

        $sql_blmod_block_val = 'INSERT INTO ' . _DB_PREFIX_ . 'blmod_xml_block
			(`id`, `name`, `value`, `status`, `category`)
			VALUES
			(49, "desc-block-name", "descriptions", 1, 2),
			(48, "cat-block-name", "category", 1, 2),
			(47, "file-name", "categories", 1, 2),
			(53, "img-block-name", "images", 1, 1),
			(52, "desc-block-name", "descriptions", 1, 1),
			(51, "cat-block-name", "product", 1, 1),
			(50, "file-name", "products", 1, 1),
			(54, "def_cat-block-name", "default_category", 1, 1),
			(55, "attributes-block-name", "attributes", 1, 1)';
        Db::getInstance()->Execute($sql_blmod_block_val);

        $sql_blmod_feeds = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'blmod_xml_feeds
			(
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`name` varchar(150) CHARACTER SET utf8 DEFAULT NULL,
				`use_cache` tinyint(1) DEFAULT NULL,
				`cache_time` int(5) DEFAULT NULL,
				`use_password` tinyint(1) DEFAULT NULL,
				`password` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
				`status` tinyint(1) DEFAULT NULL,
				`cdata_status` tinyint(1) DEFAULT NULL,
				`html_tags_status` tinyint(1) DEFAULT NULL,
				`one_branch` tinyint(1) DEFAULT NULL,
				`header_information` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
				`footer_information` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
				`extra_feed_row` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
				`feed_type` tinyint(2) DEFAULT NULL,
				`only_enabled` tinyint(1) DEFAULT NULL,		
				`split_feed` tinyint(1) DEFAULT NULL,
				`split_feed_limit` int(6) DEFAULT NULL,
				`cat_list` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
				`categories` tinyint(1) DEFAULT NULL,
				`total_views` int(11) NOT NULL DEFAULT "0",
				`use_cron` tinyint(1) DEFAULT NULL,
				`last_cron_date` datetime DEFAULT NULL,
				`only_in_stock` tinyint(1) DEFAULT NULL,
				`manufacturer_list` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
				`manufacturer` tinyint(1) DEFAULT NULL,
				`supplier_list` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
				`supplier` tinyint(1) DEFAULT NULL,
				`attribute_as_product` tinyint(1) DEFAULT NULL,
				`price_range` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
				`price_with_currency` tinyint(1) DEFAULT NULL,
				`all_images` tinyint(1) DEFAULT NULL,
				`feed_mode` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
				`currency_id` int(11) NOT NULL DEFAULT "0",
				`feed_generation_time` tinyint(1) DEFAULT NULL,
				`feed_generation_time_name` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
				`split_by_combination` tinyint(1) DEFAULT NULL,
				`product_list` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
				`product_list_status` tinyint(1) DEFAULT NULL,
				`shipping_country` int(11) NOT NULL DEFAULT "0",
				`filter_discount` tinyint(2) NOT NULL DEFAULT "0",
				`filter_category_type` tinyint(1) NOT NULL DEFAULT "0",
				`product_settings_package_id` int(11) NOT NULL DEFAULT "0",
				`filter_qty_status` tinyint(1) NOT NULL DEFAULT "0",
				`filter_qty_type` varchar(5) CHARACTER SET utf8 DEFAULT NULL,
				`filter_qty_value` int(11) NOT NULL DEFAULT "0",
				`price_format_id` tinyint(2) NOT NULL DEFAULT "0",
				`in_stock_text` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
				`out_of_stock_text` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
				PRIMARY KEY (`id`)
			)';
        $sql_blmod_feeds_res = Db::getInstance()->Execute($sql_blmod_feeds);

        $sql_blmod_feeds_val = 'INSERT INTO ' . _DB_PREFIX_ . 'blmod_xml_feeds
            (`id`, `name`, `use_cache`, `cache_time`, `use_password`, `password`, `status`, `cdata_status`, `html_tags_status`,
            `header_information`, `footer_information`, `extra_feed_row`, `feed_type`, `only_enabled`, `split_feed`,
            `split_feed_limit`, `feed_mode`, `filter_qty_type`, `filter_qty_value`)
            VALUES
            (1, "Products", 1, 200, 0, "", 1, 1, 1,"","", "", 1, 0, 0, 500, "c", ">", 1),
            (2, "Categories", 1, 300, 0, "", 1, 1, 1, "", "","", 2, 0, 0, 0, "", ">", 1),
            (3, "Orders", 1, 200, 0, "", 1, 1, 1,"","", "", 3, 0, 0, 500, "", ">", 1),
            (4, "Customers", 0, 120, 0, "", 0, 1, 1,"","", "", 4, 0, 0, 500, "", ">", 1),
            (5, "Brands", 0, 120, 0, "", 0, 1, 1,"","", "", 5, 0, 0, 500, "", ">", 1),
            (6, "Suppliers", 0, 120, 0, "", 0, 1, 1,"","", "", 6, 0, 0, 500, "", ">", 1)';
        Db::getInstance()->Execute($sql_blmod_feeds_val);

        $sql_blmod_fields = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'blmod_xml_fields
			(
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`name` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
				`status` tinyint(1) DEFAULT NULL,
				`title_xml` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
				`table` varchar(150) CHARACTER SET utf8 DEFAULT NULL,
				`category` int(11) DEFAULT NULL,
				PRIMARY KEY (`id`)
			)';
        $sql_blmod_fields_res = Db::getInstance()->Execute($sql_blmod_fields);

        $sql_blmod_cache = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'blmod_xml_feeds_cache
			(
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`feed_id` int(11) NOT NULL,
				`feed_part` int(11) NOT NULL DEFAULT "0",
				`file_name` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
				`last_cache_time` datetime DEFAULT NULL,
				`affiliate_name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
				PRIMARY KEY (`id`)
			)';
        $sql_blmod_cache_res = Db::getInstance()->Execute($sql_blmod_cache);

        $sql_blmod_statistics = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'blmod_xml_statistics
			(
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`feed_id` int(11) NOT NULL,
				`affiliate_name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
				`date` datetime DEFAULT NULL,
				`ip_address` varchar(15) CHARACTER SET utf8 DEFAULT NULL,
				PRIMARY KEY (`id`)
			)';
        $sql_blmod_statistics_res = Db::getInstance()->Execute($sql_blmod_statistics);

        $sql_blmod_affliate_price = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'blmod_xml_affiliate_price
			(
				`affiliate_id` int(11) NOT NULL AUTO_INCREMENT,
				`affiliate_name` varchar(255) CHARACTER SET utf8 NOT NULL,
				`affiliate_formula` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
				`xml_name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,  
				PRIMARY KEY (`affiliate_id`)
			)';
        $sql_blmod_affliate_price_res = Db::getInstance()->Execute($sql_blmod_affliate_price);

        $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'blmod_xml_g_cat
			(
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `category_id` int(11) NOT NULL,
                `g_category_id` int(11) NOT NULL,
                `type` varchar(20) CHARACTER SET utf8 NOT NULL,
                PRIMARY KEY (`id`)
			)';
        $sql_blmod_google_cat_res = Db::getInstance()->Execute($sql);

        $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'blmod_xml_product_list
            (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(100) CHARACTER SET utf8 NOT NULL,
                PRIMARY KEY (`id`)
            )';

        $sqlProductListRes = Db::getInstance()->Execute($sql);

        $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'blmod_xml_product_list_product
            (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `product_list_id` int(11) NOT NULL,
                `product_id` int(11) NOT NULL,
                PRIMARY KEY (`id`)
            )';

        $sqlProductListProductRes = Db::getInstance()->Execute($sql);

        $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'blmod_xml_product_settings_package
            (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(100) CHARACTER SET utf8 NOT NULL,
                PRIMARY KEY (`id`)
            )';

        $sqlProductSettingsPackageRes = Db::getInstance()->Execute($sql);

        $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'blmod_xml_product_settings 
            (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `product_id` int(11) NOT NULL,
                `package_id` int(11) NOT NULL,
                `total_budget` varchar(255) CHARACTER SET utf8 NOT NULL,
                `daily_budget` varchar(255) CHARACTER SET utf8 NOT NULL,
                `cpc` varchar(255) CHARACTER SET utf8 NOT NULL,
                `price_type` varchar(255) CHARACTER SET utf8 NOT NULL,
                `xml_custom` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            )';

        $sqlProductSettingsRes = Db::getInstance()->Execute($sql);

        $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'blmod_xml_access_log 
            (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `feed_id` int(11) NOT NULL,
                `is_cron` tinyint(1) NOT NULL,
                `action` varchar(20) CHARACTER SET utf8 NOT NULL,
                `session_id` varchar(20) CHARACTER SET utf8 NOT NULL,
                `get_param` varchar(1000) CHARACTER SET utf8 NOT NULL,
                `argv_param` varchar(1000) CHARACTER SET utf8 NOT NULL,                
                `created_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            )';

        $sqlAccessLog = Db::getInstance()->Execute($sql);

        if (!$sql_blmod_block_res || !$sql_blmod_feeds_res || !$sql_blmod_fields_res || !$sql_blmod_cache_res ||
            !$sql_blmod_statistics_res || !$sql_blmod_affliate_price_res || !$sql_blmod_google_cat_res ||
            !$sqlProductListRes || !$sqlProductListProductRes || !$sqlProductSettingsPackageRes || !$sqlProductSettingsRes ||
            !$sqlAccessLog) {
            return false;
        }

        $this->upgrade2620();
        $this->upgrade272();
        $this->upgrade2712();
        $this->upgrade2714();
        $this->upgrade287();
        $this->upgrade293();
        $this->upgrade295();
        $this->upgrade303();
        $this->upgrade304();
        $this->upgrade315();
        $this->upgrade319();
        $this->upgrade3115();

        $this->installDefaultFeedProductSettings(1);
        $this->installDefaultFeedCategorySettings(2);
        $this->installDefaultFeedOrderSettings(3);

        return true;
    }

    public function installDefaultFeedProductSettings($feed_id = false, $feedMode = 'c')
    {
        $feedType = 'category';
        $defaultImage = $feedType . '_default_name';
        $sqlFeedType = 'feed_mode = "' . $feedMode . '"';
        $fields = array();
        $blocks = array();
        $options = array();
        $imageName = array();
        $feed_id = (int)$feed_id;

        if ($feedMode == 'g' || $feedMode == 'y' || $feedMode == 't' || $feedMode == 'pint' || $feedMode == 'ins'
            || $feedMode == 'onb' || $feedMode == 'cj' || $feedMode == 'fav' || $feedMode == 'tc' || $feedMode == 'lyst' || $feedMode == 'wb'
            || $feedMode == 'ikx' || $feedMode == 'pb' || $feedMode == 'wor'|| $feedMode == 'cri' || $feedMode == 'pm'
            || $feedMode == 'gei' || $feedMode == 'ski' || $feedMode == 'cew' || $feedMode == 'bi' || $feedMode == 'hb'
            || $feedMode == 'fc' || $feedMode == 'pl') {
            $feedMode = 'f';
        }

        if ($feedMode == 'bp') {
            $feedMode = 's';
        }

        if ($feedMode == 'pp' || $feedMode == 'hi' || $feedMode == 'ld' || $feedMode == 'pa' || $feedMode == 'ko' || $feedMode == 'pdk') {
            $feedMode = 'p';
        }

        if ($feedMode == 'lz') {
            $feedMode = 'sa';
        }

        if ($feedMode == 'tro' || $feedMode == 'ppy') {
            $feedMode = 'dre';
        }

        if ($feedMode == 'com' || $feedMode == 'paz') {
            $feedMode = 'aru';
        }

        if ($feedMode == 'sho') {
            $feedMode = 'bil';
        }

        if ($feedMode == 'zbo') {
            $feedMode = 'u';
        }

        if ($feedMode == 'hind') {
            $feedMode = 'k24';
        }

        if ($feedMode == 'kurp') {
            $feedMode = 'sal';
        }

        if ($feedMode == 'ver' || $feedMode == 'verk') {
            $feedMode = 'cr';
        }

        if ($feedMode == 'wes') {
            $feedMode = 'tov';
        }

        if ($feedMode == 'cat' || $feedMode == 'publ' || $feedMode == 'dar' || $feedMode == 'ibs' || $feedMode == 'ven') {
            $feedMode = 'mir';
        }

        $fields['m'] = '
            ("product_url_blmod", 1, "loc", "bl_extra", "' . $feed_id . '"),
            ("date_upd", 1, "lastmod", "product", "' . $feed_id . '")';

        $fields['f'] = '
            ("name", 1, "g:title", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "g:description", "product_lang", "' . $feed_id . '"),
            ("large", 1, "g:image_link", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "g:brand", "manufacturer", "' . $feed_id . '"),
            ("id_product", 1, "g:id", "product", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "g:price", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "g:link", "bl_extra", "' . $feed_id . '"),
            ("condition", 1, "g:condition", "product", "' . $feed_id . '"),
            ("ean13", 1, "g:gtin", "product", "' . $feed_id . '"),
            ("reference", 1, "g:mpn", "product", "' . $feed_id . '"),
            ("available_for_order", 1, "g:availability", "product", "' . $feed_id . '"),
            ("price_shipping_blmod", 0, "g:shipping/g:price", "bl_extra", "' . $feed_id . '"),
            ("shipping_country_code", 0, "g:shipping/g:country", "bl_extra", "' . $feed_id . '"),
            ("name", 1, "g:google_product_category", "category_lang", "' . $feed_id . '")';

        $fields['s'] = '
            ("name", 1, "name", "product_lang", "' . $feed_id . '"),
            ("large", 1, "image", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "manufacturer", "manufacturer", "' . $feed_id . '"),
            ("id_product", 1, "id", "product", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "price_with_vat", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "link", "bl_extra", "' . $feed_id . '"),
            ("ean13", 1, "ean", "product", "' . $feed_id . '"),
            ("product_categories_tree", 1, "category", "bl_extra", "' . $feed_id . '"),
            ("reference", 1, "mpn", "product", "' . $feed_id . '"),
            ("stock_status", 1, "InStock", "bl_extra", "' . $feed_id . '"),
            ("available_for_order", 1, "availability", "product", "' . $feed_id . '")';

        $fields['i'] = '
            ("name", 1, "g:title", "product_lang", "' . $feed_id . '"),
            ("description", 1, "s:description", "product_lang", "' . $feed_id . '"),
            ("large", 1, "g:image_link", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "g:brand", "manufacturer", "' . $feed_id . '"),
            ("id_product", 1, "g:id", "product", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "g:price", "bl_extra", "' . $feed_id . '"),
            ("quantity", 1, "s:quantity", "product", "' . $feed_id . '"),
            ("product_url_blmod", 0, "g:link", "bl_extra", "' . $feed_id . '"),
            ("condition", 0, "g:condition", "product", "' . $feed_id . '"),
            ("ean13", 1, "g:gtin", "product", "' . $feed_id . '"),
            ("reference", 1, "g:mpn", "product", "' . $feed_id . '"),
            ("available_for_order", 0, "g:availability", "product", "' . $feed_id . '"),
            ("product_categories_tree", 1, "s:siroop_category", "bl_extra", "' . $feed_id . '")';

        $fields['x'] = '
            ("name", 1, "PRODUCT", "product_lang", "' . $feed_id . '"),
            ("description", 1, "DESCRIPTION_LONG", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "DESCRIPTION_SHORT", "product_lang", "' . $feed_id . '"),
            ("large", 1, "IMGURL", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "MANUFACTURER", "manufacturer", "' . $feed_id . '"),
            ("id_product", 1, "PRODUCT_CODE", "product", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "PRICE", "bl_extra", "' . $feed_id . '"),
            ("name", 1, "CATEGORY", "category_lang", "' . $feed_id . '"),
            ("quantity", 1, "STOCK_AMOUNT", "product", "' . $feed_id . '"),
            ("product_url_blmod", 1, "URL", "bl_extra", "' . $feed_id . '"),
            ("ean13", 1, "EAN", "product", "' . $feed_id . '"),
            ("available_for_order", 1, "AVAILABILITY", "product", "' . $feed_id . '")';

        $fields['r'] = '
            ("name", 1, "Title", "product_lang", "' . $feed_id . '"),
            ("description", 1, "Description", "product_lang", "' . $feed_id . '"),
            ("large", 1, "Imageurl1", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "Manufacturer", "manufacturer", "' . $feed_id . '"),
            ("id_product", 1, "ProductId", "product", "' . $feed_id . '"),
            ("price", 1, "NormalPriceWithoutVAT", "product", "' . $feed_id . '"),
            ("name", 1, "Category", "category_lang", "' . $feed_id . '"),
            ("quantity", 1, "StockQuantity", "product", "' . $feed_id . '"),
            ("ean13", 1, "EAN", "product", "' . $feed_id . '"),
            ("reference", 1, "SkuId", "product", "' . $feed_id . '"),
            ("weight", 1, "PackageWeight", "product", "' . $feed_id . '"),
            ("available_for_order", 1, "StockStatus", "product", "' . $feed_id . '")';

        $fields['h'] = '
            ("name", 1, "Product_Name_", "product_lang", "' . $feed_id . '"),
            ("description", 1, "Description_", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "Short_description_", "product_lang", "' . $feed_id . '"),
            ("large", 1, "image", "img_blmod", "' . $feed_id . '"),
            ("id_product", 1, "Product_ID", "product", "' . $feed_id . '"),
            ("price", 1, "Base_price", "product", "' . $feed_id . '"),
            ("name", 1, "Category", "category_lang", "' . $feed_id . '"),
            ("quantity", 1, "Quantity", "product", "' . $feed_id . '"),
            ("ean13", 1, "EAN13", "product", "' . $feed_id . '"),
            ("reference", 1, "Product_code", "product", "' . $feed_id . '"),
            ("condition", 1, "Condition", "product", "' . $feed_id . '"),
            ("product_categories_tree", 1, "Category_tree", "bl_extra", "' . $feed_id . '"),
            ("width", 1, "Width", "product", "' . $feed_id . '"),
            ("height", 1, "Height", "product", "' . $feed_id . '"),
            ("depth", 1, "Depth", "product", "' . $feed_id . '"),
            ("weight", 1, "Weight", "product", "' . $feed_id . '")';

        $fields['a'] = '
            ("name", 1, "admarkt:title", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "admarkt:description", "product_lang", "' . $feed_id . '"),
            ("large", 1, "admarkt:media", "img_blmod", "' . $feed_id . '"),
            ("id_product", 1, "admarkt:id", "product", "' . $feed_id . '"),
            ("reference", 1, "admarkt:vendorId", "product", "' . $feed_id . '"),
            ("product_url_blmod", 1, "admarkt:url", "bl_extra", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "admarkt:price", "bl_extra", "' . $feed_id . '"),
            ("name", 1, "admarkt:categoryId", "category_lang", "' . $feed_id . '")';

        $fields['o'] = '("name", 1, "NAME", "product_lang", "' . $feed_id . '"),
            ("description", 1, "DESCRIPTION", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "SHORT_DESCRIPTION", "product_lang", "' . $feed_id . '"),
            ("large", 1, "IMAGES", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "MANUFACTURER", "manufacturer", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "PRICE", "bl_extra", "' . $feed_id . '"),
            ("name", 1, "CATEGORY", "category_lang", "' . $feed_id . '"),
            ("quantity", 1, "STOCK/AMOUNT", "product", "' . $feed_id . '"),
            ("reference", 1, "CODE", "product", "' . $feed_id . '"),
            ("available_for_order", 1, "AVAILABILITY", "product", "' . $feed_id . '")';

        $fields['c'] = '
			("available_later", 1, "available_later", "product_lang", "' . $feed_id . '"),
            ("available_now", 1, "available_now", "product_lang", "' . $feed_id . '"),
            ("name", 1, "name", "product_lang", "' . $feed_id . '"),
            ("meta_title", 1, "meta_title", "product_lang", "' . $feed_id . '"),
            ("meta_keywords", 1, "meta_keywords", "product_lang", "' . $feed_id . '"),
            ("meta_description", 1, "meta_description", "product_lang", "' . $feed_id . '"),
            ("link_rewrite", 1, "link_rewrite", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "description_short", "product_lang", "' . $feed_id . '"),
            ("description", 1, "description", "product_lang", "' . $feed_id . '"),
            ("large_scene", 1, "large_scene", "img_blmod", "' . $feed_id . '"),
            ("thumb_scene", 1, "thumb_scene", "img_blmod", "' . $feed_id . '"),
            ("home", 1, "home", "img_blmod", "' . $feed_id . '"),
            ("category", 1, "category", "img_blmod", "' . $feed_id . '"),
            ("thickbox", 1, "thickbox", "img_blmod", "' . $feed_id . '"),
            ("small", 1, "small", "img_blmod", "' . $feed_id . '"),
            ("medium", 1, "medium", "img_blmod", "' . $feed_id . '"),
            ("large", 1, "large", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "' . $defaultImage . '", "category_lang", "' . $feed_id . '"),
            ("out_of_stock", 1, "out_of_stock", "product", "' . $feed_id . '"),
            ("id_category_default", 1, "category_default_id", "product", "' . $feed_id . '"),
            ("quantity_discount", 1, "quantity_discount", "product", "' . $feed_id . '"),
            ("quantity", 1, "quantity", "product", "' . $feed_id . '"),
            ("ecotax", 1, "ecotax", "product", "' . $feed_id . '"),
            ("wholesale_price", 1, "wholesale_price", "product", "' . $feed_id . '"),
            ("price", 1, "price", "product", "' . $feed_id . '"),
            ("date_upd", 1, "date_upd", "product", "' . $feed_id . '"),
            ("date_add", 1, "date_add", "product", "' . $feed_id . '"),
            ("active", 1, "active", "product", "' . $feed_id . '"),
            ("on_sale", 1, "on_sale", "product", "' . $feed_id . '"),
            ("width", 1, "width", "product", "' . $feed_id . '"),
            ("height", 1, "height", "product", "' . $feed_id . '"),
            ("depth", 1, "depth", "product", "' . $feed_id . '"),
            ("weight", 1, "weight", "product", "' . $feed_id . '"),
            ("location", 1, "location", "product", "' . $feed_id . '"),
            ("name", 1, "manufacturer_name", "manufacturer", "' . $feed_id . '"),
            ("id_manufacturer", 1, "manufacturer_id", "product", "' . $feed_id . '"),
            ("id_product", 1, "product_id", "product", "' . $feed_id . '"),
            ("id_supplier", 1, "supplier_id", "product", "' . $feed_id . '"),
            ("price_shipping_blmod", 1, "price_shipping", "bl_extra", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "price_sale", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "product_url", "bl_extra", "' . $feed_id . '"),
            ("supplier_reference", 1, "supplier_reference", "product", "' . $feed_id . '"),
            ("ean13", 1, "ean13", "product", "' . $feed_id . '"),
            ("upc", 1, "upc", "product", "' . $feed_id . '"),
            ("reference", 1, "reference", "product", "' . $feed_id . '"),
            ("available_for_order", 1, "available_for_order", "product", "' . $feed_id . '")';

        $fields['e'] = '("name", 1, "titel", "product_lang", "' . $feed_id . '"), 
            ("reference", 1, "winkelproductcode", "product", "' . $feed_id . '"),
            ("product_url_blmod", 1, "url", "bl_extra", "' . $feed_id . '"),
            ("description", 1, "beschrijving", "product_lang", "' . $feed_id . '"),
            ("ean13", 1, "ean", "product", "' . $feed_id . '"),
            ("large", 1, "url_productplaatje", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "merk", "manufacturer", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "prijs", "bl_extra", "' . $feed_id . '"),
            ("price_shipping_blmod", 1, "verzendkosten", "bl_extra", "' . $feed_id . '"),
            ("upc", 1, "sku", "product", "' . $feed_id . '"),
            ("condition", 1, "conditie", "product", "' . $feed_id . '"),
            ("available_for_order", 1, "levertijd", "product", "' . $feed_id . '"),
            ("quantity", 1, "voorraad", "product", "' . $feed_id . '"),
            ("name", 1, "categorie", "category_lang", "' . $feed_id . '")';

        $fields['p'] = '
            ("name", 1, "title", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "description", "product_lang", "' . $feed_id . '"),
            ("large", 1, "image_link", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "brand", "manufacturer", "' . $feed_id . '"),
            ("id_product", 1, "id", "product", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "price", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "link", "bl_extra", "' . $feed_id . '"),
            ("condition", 1, "condition", "product", "' . $feed_id . '"),
            ("ean13", 1, "gtin", "product", "' . $feed_id . '"),
            ("reference", 1, "mpn", "product", "' . $feed_id . '"),
            ("available_for_order", 1, "availability", "product", "' . $feed_id . '")';

        $fields['u'] = '
            ("name", 1, "PRODUCTNAME", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "DESCRIPTION", "product_lang", "' . $feed_id . '"),
            ("large", 1, "IMGURL", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "MANUFACTURER", "manufacturer", "' . $feed_id . '"),
            ("id_product", 1, "ITEM_ID", "product", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "PRICE_VAT", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "URL", "bl_extra", "' . $feed_id . '"),
            ("condition", 0, "ITEM_TYPE", "product", "' . $feed_id . '"),
            ("ean13", 1, "EAN", "product", "' . $feed_id . '"),
            ("reference", 1, "PRODUCTNO", "product", "' . $feed_id . '")';

        $fields['n'] = '
            ("name", 1, "productName", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "productDescription", "product_lang", "' . $feed_id . '"),
            ("large", 1, "imageURL", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "manufacturer", "manufacturer", "' . $feed_id . '"),
            ("id_product", 1, "productSKU", "product", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "productPrice", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "productURL", "bl_extra", "' . $feed_id . '"),
            ("condition", 1, "productState", "product", "' . $feed_id . '"),
            ("ean13", 1, "productEAN", "product", "' . $feed_id . '"),
            ("reference", 1, "manufacturerSKU", "product", "' . $feed_id . '"),
            ("price_shipping_blmod", 0, "shippingCost", "bl_extra", "' . $feed_id . '"),
            ("available_for_order", 1, "productStockStatus", "product", "' . $feed_id . '"),
            ("name", 1, "category", "category_lang", "' . $feed_id . '")';


        $fields['k'] = '
            ("name", 1, "title", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "description", "product_lang", "' . $feed_id . '"),
            ("large", 1, "image-url", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "brand", "manufacturer", "' . $feed_id . '"),
            ("id_product", 1, "offer-id", "product", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "price", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "product-url", "bl_extra", "' . $feed_id . '"),
            ("ean13", 1, "ean", "product", "' . $feed_id . '"),
            ("reference", 1, "mpn", "product", "' . $feed_id . '"),
            ("price_shipping_blmod", 1, "delivery-cost", "bl_extra", "' . $feed_id . '"),
            ("available_for_order", 1, "availability", "product", "' . $feed_id . '"),
            ("name", 1, "merchant-category", "category_lang", "' . $feed_id . '")';

        $fields['d'] = '
            ("name", 1, "PRODUCT_NAME", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "DESCRIPTION", "product_lang", "' . $feed_id . '"),
            ("large", 1, "IMAGE_URL", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "MANUFACTURER", "manufacturer", "' . $feed_id . '"),
            ("id_product", 1, "PRODUCT_ID", "product", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "PRICE", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "PRODUCT_URL", "bl_extra", "' . $feed_id . '"),
            ("ean13", 1, "EAN", "product", "' . $feed_id . '"),
            ("reference", 1, "MANUFACTURER_ID", "product", "' . $feed_id . '"),
            ("available_for_order", 1, "DELIVERY_TIME", "product", "' . $feed_id . '"),
            ("price_shipping_blmod", 0, "DELIVERY_COST", "bl_extra", "' . $feed_id . '"),
            ("price_wt_discount_blmod", 0, "WAS_PRICE", "bl_extra", "' . $feed_id . '"),
            ("product_categories_tree", 1, "PRODUCT_GROUP", "bl_extra", "' . $feed_id . '")';

        $fields['gla'] = '
            ("name", 1, "PRODUCTNAME", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "DESCRIPTION", "product_lang", "' . $feed_id . '"),
            ("large", 1, "IMGURL", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "MANUFACTURER", "manufacturer", "' . $feed_id . '"),
            ("id_product", 1, "ITEM_ID", "product", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "PRICE_VAT", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "URL", "bl_extra", "' . $feed_id . '"),
            ("available_for_order", 1, "DELIVERY_DATE", "product", "' . $feed_id . '"),
            ("name", 1, "CATEGORYTEXT", "category_lang", "' . $feed_id . '")';

        $fields['sa'] = '
            ("name", 1, "name", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "description", "product_lang", "' . $feed_id . '"),
            ("large", 1, "image", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "brand", "manufacturer", "' . $feed_id . '"),
            ("id_product", 1, "itemId", "product", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "price", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "deepLink", "bl_extra", "' . $feed_id . '"),
            ("available_for_order", 1, "stock", "product", "' . $feed_id . '"),
            ("name", 1, "TopCategory", "category_lang", "' . $feed_id . '"),
            ("ean13", 1, "ean", "product", "' . $feed_id . '")';

        $fields['st'] = '
            ("name", 1, "name", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "desc", "product_lang", "' . $feed_id . '"),
            ("large", 1, "img", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "brand", "manufacturer", "' . $feed_id . '"),
            ("id_product", 1, "id", "product", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "price", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "url", "bl_extra", "' . $feed_id . '"),
            ("available_for_order", 1, "avail", "product", "' . $feed_id . '"),
            ("name", 1, "cat", "category_lang", "' . $feed_id . '"),
            ("ean13", 1, "GTIN", "product", "' . $feed_id . '")';

        $fields['mm'] = '
            ("name", 1, "title", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "description", "product_lang", "' . $feed_id . '"),
            ("large", 1, "image_1", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "brand", "manufacturer", "' . $feed_id . '"),
            ("id_product", 1, "id", "product", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "product_price_vat_inc", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "product_url", "bl_extra", "' . $feed_id . '"),
            ("quantity", 1, "quantity", "product", "' . $feed_id . '"),
            ("name", 1, "merchant_category", "category_lang", "' . $feed_id . '"),
            ("isbn", 1, "sku", "product", "' . $feed_id . '"),
            ("ean13", 1, "ean", "product", "' . $feed_id . '")';

        $fields['vi'] = '
            ("name", 1, "product-name", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "extras/description", "product_lang", "' . $feed_id . '"),
            ("name", 1, "extras/producer", "manufacturer", "' . $feed_id . '"),
            ("id_product", 1, "product-id", "product", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "price", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "link", "bl_extra", "' . $feed_id . '"),
            ("ean13", 1, "extras/ean", "product", "' . $feed_id . '"),
            ("price_wt_discount_blmod", 0, "price-discounted-from", "bl_extra", "' . $feed_id . '"),
            ("quantity", 1, "inventory-count", "product", "' . $feed_id . '")';

        $fields['sm'] = '
            ("name", 1, "Name", "product_lang", "' . $feed_id . '"),
            ("id_product", 1, "Code", "product", "' . $feed_id . '"),
            ("description_short", 1, "Description", "product_lang", "' . $feed_id . '"),
            ("large", 1, "Image", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "Manufacturer", "manufacturer", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "Price", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "URL", "bl_extra", "' . $feed_id . '"),
            ("product_categories_tree", 1, "Category", "bl_extra", "' . $feed_id . '"),
            ("reference", 1, "Model", "product", "' . $feed_id . '"),
            ("available_for_order", 1, "Availability", "product", "' . $feed_id . '"),
            ("isbn", 1, "ISBN", "product", "' . $feed_id . '"),
            ("ean13", 1, "EAN", "product", "' . $feed_id . '")';

        $fields['rd'] = '
            ("name", 1, "D__signation_courte", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "Description_courte", "product_lang", "' . $feed_id . '"),
            ("large", 1, "URL_image_1", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "brand", "manufacturer", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "Prix_Offre", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "product_url", "bl_extra", "' . $feed_id . '"),
            ("product_categories_tree", 1, "Cat__gorie_maitre", "bl_extra", "' . $feed_id . '"),
            ("reference", 1, "Identifiant_de_l_article", "product", "' . $feed_id . '"),
            ("quantity", 1, "Quantit___Offre", "product", "' . $feed_id . '"),
            ("ean13", 1, "EAN13", "product", "' . $feed_id . '"),
            ("condition", 1, "Etat_Offre", "product", "' . $feed_id . '")';

        $fields['pub'] = '
            ("name", 1, "product-title", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "product-description", "product_lang", "' . $feed_id . '"),
            ("large", 1, "mainImage", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "brand", "manufacturer", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "price", "bl_extra", "' . $feed_id . '"),
            ("name", 1, "product-category", "category_lang", "' . $feed_id . '"),
            ("quantity", 1, "quantity", "product", "' . $feed_id . '")';

        $fields['ws'] = '
            ("name", 1, "name", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "description", "product_lang", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "price", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "url", "bl_extra", "' . $feed_id . '"),
            ("quantity", 1, "stock-level", "product", "' . $feed_id . '"),
            ("available_for_order", 1, "delivery-time", "product", "' . $feed_id . '")';

        $fields['dre'] = '
            ("name", 1, "Name", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "Description", "product_lang", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "Price", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "Link", "bl_extra", "' . $feed_id . '"),
            ("quantity", 1, "Stock", "product", "' . $feed_id . '"),
            ("name", 1, "Brand", "manufacturer", "' . $feed_id . '"),
            ("product_categories_tree", 1, "Categories", "bl_extra", "' . $feed_id . '"),
            ("ean13", 1, "EanCode", "product", "' . $feed_id . '"),
            ("id_product", 1, "Code", "product", "' . $feed_id . '"),
            ("price_shipping_blmod", 0, "ShippingCost", "bl_extra", "' . $feed_id . '")';

        $fields['cen'] = '
            ("name", 1, "name", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "description", "product_lang", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "price", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "link", "bl_extra", "' . $feed_id . '"),
            ("quantity", 1, "quantity", "product", "' . $feed_id . '"),
            ("name", 1, "brand", "manufacturer", "' . $feed_id . '"),
            ("product_categories_tree", 1, "fileUnder", "bl_extra", "' . $feed_id . '"),
            ("ean13", 1, "EAN", "product", "' . $feed_id . '"),
            ("id_product", 1, "ID", "product", "' . $feed_id . '"),
            ("condition", 1, "condition", "product", "' . $feed_id . '"),
            ("available_for_order", 1, "availability", "product", "' . $feed_id . '"),
            ("price_shipping_blmod", 0, "deliveryCost", "bl_extra", "' . $feed_id . '")';

        $fields['twe'] = '
            ("name", 1, "Product_Name", "product_lang", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "Product_Price", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "Deeplink", "bl_extra", "' . $feed_id . '"),
            ("quantity", 1, "Product_in_stock", "product", "' . $feed_id . '"),
            ("name", 1, "Product_Brand", "manufacturer", "' . $feed_id . '"),
            ("ean13", 1, "Product_Ean", "product", "' . $feed_id . '"),
            ("available_for_order", 1, "Promotiezin", "product", "' . $feed_id . '"),
            ("reference", 1, "SKU_Code", "product", "' . $feed_id . '"),
            ("price_shipping_blmod", 0, "Delivery_Costs", "bl_extra", "' . $feed_id . '")';

        $fields['k24'] = '
            ("name", 1, "title", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "description", "product_lang", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "price", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "product_url", "bl_extra", "' . $feed_id . '"),
            ("quantity", 1, "stock", "product", "' . $feed_id . '"),
            ("name", 1, "manufacturer", "manufacturer", "' . $feed_id . '"),
            ("reference", 1, "manufacturer_code", "product", "' . $feed_id . '"),
            ("ean13", 1, "ean_code", "product", "' . $feed_id . '"),
            ("condition", 1, "condition", "product", "' . $feed_id . '"),
            ("available_for_order", 1, "delivery_time", "product", "' . $feed_id . '"),
            ("name", 1, "category_name", "category_lang", "' . $feed_id . '"),
            ("id_category_default", 1, "category_id", "product", "' . $feed_id . '"),
            ("price_shipping_blmod", 0, "delivery_price", "bl_extra", "' . $feed_id . '")';

        $fields['kos'] = '
            ("name", 1, "title", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "description", "product_lang", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "item_price", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "product_url", "bl_extra", "' . $feed_id . '"),
            ("quantity", 1, "stock", "product", "' . $feed_id . '"),
            ("name", 1, "manufacturer", "manufacturer", "' . $feed_id . '"),
            ("reference", 1, "manufacturer_code", "product", "' . $feed_id . '"),
            ("ean13", 1, "ean_code", "product", "' . $feed_id . '"),
            ("condition", 0, "condition", "product", "' . $feed_id . '"),
            ("available_for_order", 1, "delivery_time", "product", "' . $feed_id . '"),
            ("product_categories_tree", 1, "categories", "bl_extra", "' . $feed_id . '"),
            ("price_shipping_blmod", 0, "delivery_price", "bl_extra", "' . $feed_id . '")';

        $fields['plt'] = '
            ("name", 1, "title", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "description", "product_lang", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "price", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "product_url", "bl_extra", "' . $feed_id . '"),
            ("quantity", 1, "stock", "product", "' . $feed_id . '"),
            ("name", 1, "brand", "manufacturer", "' . $feed_id . '"),
            ("reference", 1, "sku", "product", "' . $feed_id . '"),
            ("ean13", 1, "ean_code", "product", "' . $feed_id . '"),
            ("condition", 0, "condition", "product", "' . $feed_id . '"),
            ("available_for_order", 1, "delivery_time", "product", "' . $feed_id . '"),
            ("product_categories_tree", 1, "categories", "bl_extra", "' . $feed_id . '"),
            ("price_shipping_blmod", 0, "delivery_price", "bl_extra", "' . $feed_id . '")';

        $fields['aru'] = '
            ("id_product", 1, "Identifier", "product", "' . $feed_id . '"),
            ("ean13", 1, "EAN_code", "product", "' . $feed_id . '"),
            ("name", 1, "Name", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "Description", "product_lang", "' . $feed_id . '"),
            ("name", 1, "Manufacturer", "manufacturer", "' . $feed_id . '"),
            ("product_categories_tree", 1, "Category", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "Product_url", "bl_extra", "' . $feed_id . '"),
            ("available_for_order", 1, "Delivery_Time", "product", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "Price", "bl_extra", "' . $feed_id . '"),
            ("reference", 1, "ProductNumber", "product", "' . $feed_id . '"),
            ("quantity", 0, "PPPMaxQuantity", "product", "' . $feed_id . '"),
            ("price_shipping_blmod", 0, "Delivery_Cost", "bl_extra", "' . $feed_id . '")';

        $fields['epr'] = '
            ("id_product", 1, "Item_sku", "product", "' . $feed_id . '"),
            ("reference", 1, "Part_number", "product", "' . $feed_id . '"),
            ("name", 1, "Item_name", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "Product_description", "product_lang", "' . $feed_id . '"), 
            ("name", 1, "Manufacturer", "manufacturer", "' . $feed_id . '"),
            ("ean13", 1, "External_product_id", "product", "' . $feed_id . '"),
            ("quantity", 0, "Quantity", "product", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "Price", "bl_extra", "' . $feed_id . '")';

        $fields['sez'] = '
            ("id_product", 1, "ITEM_ID", "product", "' . $feed_id . '"),
            ("ean13", 1, "EAN", "product", "' . $feed_id . '"),
            ("name", 1, "PRODUCTNAME", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "DESCRIPTION", "product_lang", "' . $feed_id . '"),
            ("name", 1, "MANUFACTURER", "manufacturer", "' . $feed_id . '"),
            ("product_categories_tree", 1, "CATEGORYTEXT", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "URL", "bl_extra", "' . $feed_id . '"),
            ("available_for_order", 1, "DELIVERY_DATE", "product", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "PRICE_VAT", "bl_extra", "' . $feed_id . '"),
            ("reference", 1, "PRODUCTNO", "product", "' . $feed_id . '"),
            ("price_shipping_blmod", 0, "DELIVERY/DELIVERY_PRICE", "bl_extra", "' . $feed_id . '")';

        $fields['pri'] = '
            ("id_product", 1, "butikkensVarenr", "product", "' . $feed_id . '"),
            ("ean13", 1, "EAN", "product", "' . $feed_id . '"),
            ("name", 1, "Produktnavn", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "Produktbeskrivelse", "product_lang", "' . $feed_id . '"),
            ("product_categories_tree", 1, "Produktkategori", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "URL", "bl_extra", "' . $feed_id . '"),
            ("available_for_order", 1, "Lagerstatus", "product", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "Pris", "bl_extra", "' . $feed_id . '"),
            ("reference", 1, "ProdusentensVarenummer", "product", "' . $feed_id . '"),
            ("quantity", 0, "AntallPaLager", "product", "' . $feed_id . '"),
            ("price_shipping_blmod", 0, "Fraktkostnad", "bl_extra", "' . $feed_id . '")';

        $fields['mal'] = '
            ("name", 1, "TITLE", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "SHORTDESC", "product_lang", "' . $feed_id . '"),
            ("description", 1, "LONGDESC", "product_lang", "' . $feed_id . '"),
            ("large", 1, "MEDIA", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "BRAND_ID", "manufacturer", "' . $feed_id . '"),
            ("id_product", 1, "ID", "product", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "PRICE", "bl_extra", "' . $feed_id . '"),
            ("tax_rate", 1, "VAT", "bl_extra", "' . $feed_id . '"),
            ("parent_id_product", 0, "ITEMGROUP_ID", "bl_extra", "' . $feed_id . '"),
            ("ean13", 1, "BARCODE", "product", "' . $feed_id . '"),
            ("available_for_order", 0, "DELIVERY_DELAY", "product", "' . $feed_id . '"),
            ("name", 1, "CATEGORY_ID", "category_lang", "' . $feed_id . '")';

        $fields['spa'] = '
            ("name", 1, "product_name", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "product_description", "product_lang", "' . $feed_id . '"),
            ("large", 1, "url", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "manufacturers_name", "manufacturer", "' . $feed_id . '"),
            ("id_product", 1, "reference_partenaire", "product", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "product_price", "bl_extra", "' . $feed_id . '"),
            ("ean13", 1, "ean", "product", "' . $feed_id . '"),
            ("quantity", 1, "product_quantity", "product", "' . $feed_id . '"),
            ("name", 1, "product_style", "category_lang", "' . $feed_id . '")';

        $fields['lw'] = '
            ("name", 1, "name", "product_lang", "' . $feed_id . '"),
            ("large", 1, "image", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "manufacturer", "manufacturer", "' . $feed_id . '"),
            ("id_product", 1, "id", "product", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "price_with_vat", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "link", "bl_extra", "' . $feed_id . '"),
            ("ean13", 1, "ean", "product", "' . $feed_id . '"),
            ("product_categories_tree", 1, "category", "bl_extra", "' . $feed_id . '"),
            ("reference", 1, "mpn", "product", "' . $feed_id . '"),
            ("stock_status", 1, "InStock", "bl_extra", "' . $feed_id . '"),
            ("available_for_order", 1, "availability", "product", "' . $feed_id . '"),
            ("discount_rate_blmod", 1, "discount", "bl_extra", "' . $feed_id . '"),
            ("price_wt_discount_blmod", 1, "full_price", "bl_extra", "' . $feed_id . '")';

        $fields['naj'] = '
            ("name", 1, "NAME", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "DESCRIPTION", "product_lang", "' . $feed_id . '"),
            ("large", 1, "IMAGE_URL", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "MANUFACTURER", "manufacturer", "' . $feed_id . '"),
            ("id_product", 1, "CODE", "product", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "PRICE", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "PRODUCT_URL", "bl_extra", "' . $feed_id . '"),
            ("ean13", 1, "EAN", "product", "' . $feed_id . '"),
            ("reference", 1, "PART_NUMBER", "product", "' . $feed_id . '"),
            ("available_for_order", 1, "AVAILABILITY", "product", "' . $feed_id . '"),
            ("price_shipping_blmod", 0, "SHIPPING", "bl_extra", "' . $feed_id . '"),
            ("product_categories_tree", 1, "CATEGORY", "bl_extra", "' . $feed_id . '")';

        $fields['tot'] = '
            ("name", 1, "name", "product_lang", "' . $feed_id . '"),
            ("description_short", 0, "description", "product_lang", "' . $feed_id . '"),
            ("large", 1, "image", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "manufacturer", "manufacturer", "' . $feed_id . '"),
            ("id_product", 1, "id", "product", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "price", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "link", "bl_extra", "' . $feed_id . '"),
            ("product_categories_tree", 1, "category_path", "bl_extra", "' . $feed_id . '"),
            ("isbn", 0, "sku", "product", "' . $feed_id . '"),
            ("available_for_order", 1, "availability", "product", "' . $feed_id . '"),
            ("price_shipping_blmod", 0, "shipping", "bl_extra", "' . $feed_id . '"),
            ("reference", 1, "mpn", "product", "' . $feed_id . '"),
            ("ean13", 1, "ean", "product", "' . $feed_id . '")';

        $fields['ceo'] = '
            ("name", 1, "name", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "desc", "product_lang", "' . $feed_id . '"),
            ("large", 1, "imgs", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "Manufacturer", "manufacturer", "' . $feed_id . '"),
            ("id_product", 1, "id", "product", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "price", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "url", "bl_extra", "' . $feed_id . '"),
            ("product_categories_tree", 1, "cat", "bl_extra", "' . $feed_id . '"),
            ("isbn", 0, "ISBN", "product", "' . $feed_id . '"),
            ("available_for_order", 1, "avail", "product", "' . $feed_id . '"),
            ("price_shipping_blmod", 0, "shipping", "bl_extra", "' . $feed_id . '"),
            ("reference", 1, "Manufacturer’s code", "product", "' . $feed_id . '"),
            ("quantity", 1, "stock", "product", "' . $feed_id . '"),
            ("ean13", 1, "EAN", "product", "' . $feed_id . '")';

        $fields['bil'] = '
            ("name", 1, "name", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "desc", "product_lang", "' . $feed_id . '"),
            ("large", 1, "images", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "brand", "manufacturer", "' . $feed_id . '"),
            ("id_product", 1, "aid", "product", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "price", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "link", "bl_extra", "' . $feed_id . '"),
            ("product_categories_tree", 1, "shop_cat", "bl_extra", "' . $feed_id . '"),
            ("available_for_order", 1, "dlv_time", "product", "' . $feed_id . '"),
            ("price_shipping_blmod", 0, "dlv_cost", "bl_extra", "' . $feed_id . '"),
            ("reference", 1, "mpn", "product", "' . $feed_id . '"),
            ("quantity", 1, "stock_quantity", "product", "' . $feed_id . '"),
            ("condition", 1, "condition", "product", "' . $feed_id . '"),
            ("ean13", 1, "EAN", "product", "' . $feed_id . '")';

        $fields['man'] = '
            ("name", 1, "name", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "description", "product_lang", "' . $feed_id . '"),
            ("large", 1, "picture", "img_blmod", "' . $feed_id . '"),
            ("id_product", 1, "id", "product", "' . $feed_id . '"),
            ("price", 1, "price", "product", "' . $feed_id . '"),
            ("ean13", 1, "ean", "product", "' . $feed_id . '"),
            ("reference", 1, "part_number", "product", "' . $feed_id . '"),
            ("name", 1, "manufacturer", "manufacturer", "' . $feed_id . '"),
            ("product_url_blmod", 1, "url", "bl_extra", "' . $feed_id . '"),
            ("price_shipping_blmod", 0, "shipping", "bl_extra", "' . $feed_id . '"),
            ("available_for_order", 1, "availability", "product", "' . $feed_id . '"),
            ("product_categories_tree", 1, "category", "bl_extra", "' . $feed_id . '")';

        $fields['sal'] = '
            ("name", 1, "name", "product_lang", "' . $feed_id . '"),
            ("large", 1, "image", "img_blmod", "' . $feed_id . '"),
            ("price", 1, "price", "product", "' . $feed_id . '"),
            ("ean13", 1, "gtin", "product", "' . $feed_id . '"),
            ("reference", 1, "mpn", "product", "' . $feed_id . '"),
            ("name", 1, "brand", "manufacturer", "' . $feed_id . '"),
            ("product_url_blmod", 1, "link", "bl_extra", "' . $feed_id . '"),
            ("quantity", 1, "in_stock", "product", "' . $feed_id . '"),
            ("price_shipping_blmod", 0, "delivery_latvija", "bl_extra", "' . $feed_id . '"),
            ("product_categories_tree", 1, "category_full", "bl_extra", "' . $feed_id . '"),
            ("category_url", 1, "category_link", "bl_extra", "' . $feed_id . '")';

        $fields['hinn'] = '
            ("name", 1, "Name", "product_lang", "' . $feed_id . '"),
            ("large", 1, "image", "img_blmod", "' . $feed_id . '"),
            ("description_short", 1, "Description", "product_lang", "' . $feed_id . '"),
            ("price", 1, "Price", "product", "' . $feed_id . '"),
            ("ean13", 1, "GTIN", "product", "' . $feed_id . '"),
            ("reference", 1, "Code", "product", "' . $feed_id . '"),
            ("name", 1, "Vendor", "manufacturer", "' . $feed_id . '"),
            ("product_url_blmod", 1, "URL", "bl_extra", "' . $feed_id . '"),
            ("name", 1, "Category", "category_lang", "' . $feed_id . '"),
            ("quantity", 1, "InStore", "product", "' . $feed_id . '")';

        $fields['wum'] = '
            ("name", 1, "title", "product_lang", "' . $feed_id . '"),
            ("large", 1, "img_url", "img_blmod", "' . $feed_id . '"),
            ("description_short", 1, "short_desc", "product_lang", "' . $feed_id . '"),
            ("price", 1, "price", "product", "' . $feed_id . '"),
            ("ean13", 1, "gtin", "product", "' . $feed_id . '"),
            ("reference", 1, "mpn", "product", "' . $feed_id . '"),
            ("product_url_blmod", 1, "url", "bl_extra", "' . $feed_id . '")';

        $fields['mala'] = '
            ("id_product", 1, "ProductID", "product", "' . $feed_id . '"),
            ("parent_id_product", 1, "ParentSKU", "bl_extra", "' . $feed_id . '"),
            ("additional_id_combination", 1, "CombinationSKU", "bl_extra", "' . $feed_id . '"),
            ("name", 1, "ProductName", "product_lang", "' . $feed_id . '"),
            ("large", 1, "Image", "img_blmod", "' . $feed_id . '"),
            ("description_short", 1, "ShortDescription", "product_lang", "' . $feed_id . '"),
            ("description", 1, "LongDescription", "product_lang", "' . $feed_id . '"),
            ("ean13", 1, "ProductEAN", "product", "' . $feed_id . '"),
            ("reference", 1, "ProductMPN", "product", "' . $feed_id . '"),
            ("name", 1, "ProductCategory", "category_lang", "' . $feed_id . '"),
            ("meta_title", 1, "MetaTitle", "product_lang", "' . $feed_id . '"),
            ("meta_description", 1, "MetaDescription", "product_lang", "' . $feed_id . '"),
            ("name", 1, "ProductManufacturer", "manufacturer", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "ProductSalePrice", "bl_extra", "' . $feed_id . '"),
            ("price", 1, "ProductRegularPrice", "product", "' . $feed_id . '"),
            ("quantity", 1, "ProductQuantity", "product", "' . $feed_id . '"),
            ("1", 1, "ProductAttributeSize", "bl_extra_attribute_group", "' . $feed_id . '"),
            ("2", 1, "ProductAttributeColor", "bl_extra_attribute_group", "' . $feed_id . '"),
            ("meta_keywords", 1, "MetaKeywords", "product_lang", "' . $feed_id . '")';

        $fields['cr'] = '
            ("id_product", 1, "RéférenceProduitMarchand", "product", "' . $feed_id . '"),
            ("name", 1, "NomProduit", "product_lang", "' . $feed_id . '"),
            ("large", 1, "URLImage", "img_blmod", "' . $feed_id . '"),
            ("description_short", 1, "TextePromotionnel", "product_lang", "' . $feed_id . '"),
            ("price", 1, "Prix", "product", "' . $feed_id . '"),
            ("ean13", 1, "CodeEAN", "product", "' . $feed_id . '"),
            ("reference", 1, "IdentifiantUnique", "product", "' . $feed_id . '"),
            ("name", 1, "Marque", "manufacturer", "' . $feed_id . '"),
            ("product_url_blmod", 1, "URLProduit", "bl_extra", "' . $feed_id . '"),
            ("name", 1, "Catégorie", "category_lang", "' . $feed_id . '"),
            ("available_for_order", 1, "DélaiLivraison", "product", "' . $feed_id . '"),
            ("price_shipping_blmod", 0, "FraisPort", "bl_extra", "' . $feed_id . '"),
            ("quantity", 1, "ProduitsEnStock", "product", "' . $feed_id . '")';

        $fields['tov'] = '
            ("name", 1, "PRODUCT", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "DESCRIPTION", "product_lang", "' . $feed_id . '"),
            ("large", 1, "IMGURL", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "MANUFACTURER", "manufacturer", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "PRICE_VAT", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "URL", "bl_extra", "' . $feed_id . '"),
            ("reference", 1, "PRODUCTNO", "product", "' . $feed_id . '"),
            ("available_for_order", 1, "AVAILABILITY", "product", "' . $feed_id . '"),
            ("ean13", 1, "EAN", "product", "' . $feed_id . '"),
            ("price_wt_discount_blmod", 0, "OLDPRICE_VAT", "bl_extra", "' . $feed_id . '"),
            ("product_categories_tree", 1, "CATEGORYTEXT", "bl_extra", "' . $feed_id . '")';

        $fields['che'] = '
            ("name", 1, "titre", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "description", "product_lang", "' . $feed_id . '"),
            ("large", 1, "image", "img_blmod", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "prix", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "url", "bl_extra", "' . $feed_id . '"),
            ("reference", 1, "partNumber", "product", "' . $feed_id . '"),
            ("available_for_order", 1, "delaisLivraison", "product", "' . $feed_id . '"),
            ("ean13", 1, "EAN", "product", "' . $feed_id . '"),
            ("price_wt_discount_blmod", 0, "prixBarre", "bl_extra", "' . $feed_id . '"),
            ("price_shipping_blmod", 0, "fraisLivraison", "bl_extra", "' . $feed_id . '"),
            ("condition", 1, "type", "product", "' . $feed_id . '"),
            ("quantity", 1, "stock", "product", "' . $feed_id . '"),
            ("name", 1, "Catégorie", "category_lang", "' . $feed_id . '")';

        $fields['kie'] = '
            ("id_product", 1, "id", "product", "' . $feed_id . '"),
            ("name", 1, "type", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "extra-productbeschrijving", "product_lang", "' . $feed_id . '"),
            ("large", 1, "imagelink", "img_blmod", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "prijs", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "deeplink", "bl_extra", "' . $feed_id . '"),
            ("reference", 1, "partnumber", "product", "' . $feed_id . '"),
            ("available_for_order", 1, "levertijd", "product", "' . $feed_id . '"),
            ("ean13", 1, "ean-code", "product", "' . $feed_id . '"),
            ("price_shipping_blmod", 0, "verzendkosten", "bl_extra", "' . $feed_id . '"),
            ("quantity", 1, "voorraad", "product", "' . $feed_id . '"),
            ("name", 1, "merk", "manufacturer", "' . $feed_id . '"),
            ("product_categories_tree", 1, "productgroep", "bl_extra", "' . $feed_id . '"),
            ("width", 1, "specs/breedte", "product", "' . $feed_id . '"),
            ("height", 1, "specs/hoogte", "product", "' . $feed_id . '"),
            ("depth", 1, "specs/diepte", "product", "' . $feed_id . '"),
            ("weight", 0, "specs/weight", "product", "' . $feed_id . '")';

        $fields['mir'] = '
            ("name", 1, "product-title", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "product-description", "product_lang", "' . $feed_id . '"),
            ("name", 1, "brand", "manufacturer", "' . $feed_id . '"),
            ("id_product", 1, "shop-sku", "product", "' . $feed_id . '"),
            ("additional_id_combination", 1, "unique-identifier", "bl_extra", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "price", "bl_extra", "' . $feed_id . '"),
            ("ean13", 1, "ean-13", "product", "' . $feed_id . '"),
            ("reference", 0, "product-reference", "product", "' . $feed_id . '"),
            ("quantity", 1, "quantity", "product", "' . $feed_id . '"),
            ("product_tags", 1, "keywords", "bl_extra", "' . $feed_id . '"),
            ("name", 1, "product-category", "category_lang", "' . $feed_id . '")';

        $fields['kog'] = '
            ("id_product", 1, "PRODUCT_SKU", "product", "' . $feed_id . '"),
            ("name", 1, "PRODUCT_TITLE", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "PRODUCT_DESCRIPTION", "product_lang", "' . $feed_id . '"),
            ("large", 1, "IMAGES", "img_blmod", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "PRICE", "bl_extra", "' . $feed_id . '"),
            ("available_for_order", 1, "HANDLING_DAYS", "product", "' . $feed_id . '"),
            ("ean13", 1, "PRODUCT_GTIN", "product", "' . $feed_id . '"),
            ("price_shipping_blmod", 1, "SHIPPING", "bl_extra", "' . $feed_id . '"),
            ("quantity", 1, "STOCK", "product", "' . $feed_id . '"),
            ("name", 1, "BRAND", "manufacturer", "' . $feed_id . '"),
            ("name", 1, "CATEGORY", "category_lang", "' . $feed_id . '")';

        $fields['rol'] = '
            ("id_product", 1, "id", "product", "' . $feed_id . '"),
            ("name", 1, "title", "product_lang", "' . $feed_id . '"),
            ("product_url_blmod", 1, "ProductURL", "bl_extra", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "Price", "bl_extra", "' . $feed_id . '"),
            ("description_short", 1, "Description", "product_lang", "' . $feed_id . '"),
            ("name", 1, "Category", "category_lang", "' . $feed_id . '"),
            ("product_tags", 1, "Taglines", "bl_extra", "' . $feed_id . '"),
            ("available_for_order", 1, "Availability", "product", "' . $feed_id . '")';

        $fields['kk'] = '
            ("product_url_blmod", 1, "deeplink", "bl_extra", "' . $feed_id . '"),
            ("id_product", 1, "id", "product", "' . $feed_id . '"),
            ("name", 1, "titel", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "productomschrijving", "product_lang", "' . $feed_id . '"),
            ("product_categories_tree", 1, "productgroep", "bl_extra", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "prijs", "bl_extra", "' . $feed_id . '"),
            ("name", 1, "merk", "manufacturer", "' . $feed_id . '"),
            ("quantity", 1, "voorraad", "product", "' . $feed_id . '"),
            ("ean13", 0, "ean-code", "product", "' . $feed_id . '"),
            ("reference", 1, "partnumber", "product", "' . $feed_id . '"),
            ("available_for_order", 1, "levertijd", "product", "' . $feed_id . '")';

        $fields['tt'] = '
            ("product_url_blmod", 1, "productURL", "bl_extra", "' . $feed_id . '"),
            ("name", 1, "name", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "description", "product_lang", "' . $feed_id . '"),
            ("name", 1, "categories", "category_lang", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "price", "bl_extra", "' . $feed_id . '"),
            ("ean13", 1, "EAN", "product", "' . $feed_id . '"),
            ("reference", 1, "SKU", "product", "' . $feed_id . '"),
            ("available_for_order", 1, "Delivery_time", "product", "' . $feed_id . '")';

        $fields['dm'] = '
            ("id_product", 1, "id", "product", "' . $feed_id . '"),
            ("name", 1, "name", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "description", "product_lang", "' . $feed_id . '"),
            ("product_categories_tree", 1, "category", "bl_extra", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "price", "bl_extra", "' . $feed_id . '"),
            ("name", 1, "manufacturer", "manufacturer", "' . $feed_id . '"),
            ("ean13", 1, "ean", "product", "' . $feed_id . '"),
            ("reference", 1, "mpn", "product", "' . $feed_id . '"),
            ("available_for_order", 1, "availability", "product", "' . $feed_id . '")';

        $fields['ep'] = '
            ("name", 1, "name", "product_lang", "' . $feed_id . '"),
            ("large", 1, "picture", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "vendor", "manufacturer", "' . $feed_id . '"),
            ("description_short", 1, "description", "product_lang", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "price", "bl_extra", "' . $feed_id . '"),
            ("reference", 1, "barcode", "product", "' . $feed_id . '"),
            ("name", 1, "category", "category_lang", "' . $feed_id . '"),
            ("price_wt_discount_blmod", 0, "price_old", "bl_extra", "' . $feed_id . '")';

        $fields['ro'] = '
            ("name", 1, "name", "product_lang", "' . $feed_id . '"),
            ("large", 1, "picture", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "vendor", "manufacturer", "' . $feed_id . '"),
            ("description_short", 1, "description", "product_lang", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "price", "bl_extra", "' . $feed_id . '"),
            ("id_category_default", 1, "category", "product", "' . $feed_id . '"),
            ("quantity", 1, "stock_quantity", "product", "' . $feed_id . '"),
            ("product_url_blmod", 1, "url", "bl_extra", "' . $feed_id . '"),
            ("price_wt_discount_blmod", 0, "price_old", "bl_extra", "' . $feed_id . '")';

        $fields['ar'] = '
            ("reference", 1, "cikkszam", "product", "' . $feed_id . '"),        
            ("name", 1, "nev", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "leiras", "product_lang", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "ar", "bl_extra", "' . $feed_id . '"),
            ("large", 1, "fotolink", "img_blmod", "' . $feed_id . '"),
            ("product_url_blmod", 1, "termeklink", "bl_extra", "' . $feed_id . '"),
            ("available_for_order", 1, "ido", "product", "' . $feed_id . '"),
            ("price_shipping_blmod", 0, "szallitas", "bl_extra", "' . $feed_id . '")';

        $fields['ho'] = '
            ("id_product", 1, "id", "product", "' . $feed_id . '"),
            ("name", 1, "name", "product_lang", "' . $feed_id . '"),
            ("large", 1, "image", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "vendor", "manufacturer", "' . $feed_id . '"),
            ("description_short", 1, "description", "product_lang", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "priceRUAH", "bl_extra", "' . $feed_id . '"),
            ("reference", 1, "barcode", "product", "' . $feed_id . '"),
            ("id_category_default", 1, "categoryId", "product", "' . $feed_id . '"),
            ("ean13", 1, "code", "product", "' . $feed_id . '"),
            ("condition", 1, "condition", "product", "' . $feed_id . '"),
            ("available_for_order", 1, "stock", "product", "' . $feed_id . '")';

        $fields['ek'] = '
            ("id_product", 1, "id", "product", "' . $feed_id . '"),
            ("name", 1, "name", "product_lang", "' . $feed_id . '"),
            ("large", 1, "image", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "vendor", "manufacturer", "' . $feed_id . '"),
            ("description_short", 1, "description", "product_lang", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "priceRUAH", "bl_extra", "' . $feed_id . '"),
            ("reference", 1, "barcode", "product", "' . $feed_id . '"),
            ("id_category_default", 1, "categoryId", "product", "' . $feed_id . '"),
            ("ean13", 1, "code", "product", "' . $feed_id . '"),
            ("condition", 1, "condition", "product", "' . $feed_id . '"),
            ("available_for_order", 1, "stock", "product", "' . $feed_id . '")';

        $fields['kuk'] = '
            ("id_product", 1, "id_product", "product", "' . $feed_id . '"),
            ("name", 1, "name", "product_lang", "' . $feed_id . '"),
            ("large", 1, "image_url", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "brand", "manufacturer", "' . $feed_id . '"),
            ("description_short", 1, "description", "product_lang", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "price", "bl_extra", "' . $feed_id . '"),
            ("reference", 1, "reference", "product", "' . $feed_id . '"),
            ("ean13", 1, "upc_ean", "product", "' . $feed_id . '"),
            ("name", 1, "category", "category_lang", "' . $feed_id . '"),
            ("available_for_order", 1, "stock", "product", "' . $feed_id . '")';

        $fields['dot'] = '
            ("name", 1, "name", "product_lang", "' . $feed_id . '"),
            ("product_url_blmod", 1, "product_url", "bl_extra", "' . $feed_id . '"),
            ("large", 1, "image_url", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "manufacturer", "manufacturer", "' . $feed_id . '"),
            ("description_short", 1, "description", "product_lang", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "price", "bl_extra", "' . $feed_id . '"),
            ("reference", 1, "reference", "product", "' . $feed_id . '"),
            ("ean13", 1, "ean13", "product", "' . $feed_id . '"),
            ("name", 1, "category", "category_lang", "' . $feed_id . '"),
            ("condition", 1, "state", "product", "' . $feed_id . '"),
            ("quantity", 1, "quantity", "product", "' . $feed_id . '")';

        $fields['pem'] = '
            ("name", 1, "nome", "product_lang", "' . $feed_id . '"),
            ("product_url_blmod", 1, "link", "bl_extra", "' . $feed_id . '"),
            ("large", 1, "image", "img_blmod", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "preco_de_venda", "bl_extra", "' . $feed_id . '"),
            ("reference", 1, "reference", "product", "' . $feed_id . '"),
            ("ean13", 1, "ean", "product", "' . $feed_id . '"),
            ("quantity", 1, "stock_quantidade", "product", "' . $feed_id . '")';

        $fields['cb'] = '
            ("name", 1, "Product_name", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "Product_summary", "product_lang", "' . $feed_id . '"),
            ("large", 1, "Image_url", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "Brand", "manufacturer", "' . $feed_id . '"),
            ("id_product", 1, "SKU", "product", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "Price", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "Product_url", "bl_extra", "' . $feed_id . '"),
            ("ean13", 1, "MPN", "product", "' . $feed_id . '"),
            ("reference", 1, "UPC", "product", "' . $feed_id . '"),
            ("available_for_order", 1, "Availability", "product", "' . $feed_id . '"),
            ("price_shipping_blmod", 0, "Shipping_cost", "bl_extra", "' . $feed_id . '"),
            ("condition", 1, "Condition", "product", "' . $feed_id . '"),
            ("name", 1, "Category", "category_lang", "' . $feed_id . '")';

        $fields['gu'] = '
            ("name", 1, "item_name", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "item_short_desc", "product_lang", "' . $feed_id . '"),
            ("large", 1, "item_image_url", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "item_manufacturer", "manufacturer", "' . $feed_id . '"),
            ("id_product", 1, "item_unique_id", "product", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "item_price", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "item_page_url", "bl_extra", "' . $feed_id . '"),
            ("ean13", 1, "item_upc", "product", "' . $feed_id . '"),
            ("reference", 1, "item_mpn", "product", "' . $feed_id . '"),
            ("available_for_order", 1, "item_inventory", "product", "' . $feed_id . '"),
            ("price_shipping_blmod", 0, "item_shipping_charge", "bl_extra", "' . $feed_id . '"),
            ("condition", 1, "item_condition", "product", "' . $feed_id . '"),
            ("name", 1, "item_category", "category_lang", "' . $feed_id . '")';

        $fields['sn'] = '
            ("name", 1, "title", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "description", "product_lang", "' . $feed_id . '"),
            ("large", 1, "image_link", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "brand", "manufacturer", "' . $feed_id . '"),
            ("id_product", 1, "id", "product", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "price", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "link", "bl_extra", "' . $feed_id . '"),
            ("ean13", 1, "gtin", "product", "' . $feed_id . '"),
            ("reference", 1, "mpn", "product", "' . $feed_id . '"),
            ("available_for_order", 1, "availability", "product", "' . $feed_id . '"),
            ("condition", 1, "condition", "product", "' . $feed_id . '"),
            ("name", 1, "google_product_category", "category_lang", "' . $feed_id . '")';

        $fields['gp'] = '
            ("name", 1, "PRODUCT_NAME", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "DESCRIPTION", "product_lang", "' . $feed_id . '"),
            ("large", 1, "IMAGE", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "MANUFACTURER", "manufacturer", "' . $feed_id . '"),
            ("id_product", 1, "PRODUCT_NUM", "product", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "PRICE", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "PRODUCT_URL", "bl_extra", "' . $feed_id . '"),
            ("ean13", 1, "UPC", "product", "' . $feed_id . '"),
            ("reference", 1, "MPN", "product", "' . $feed_id . '"),
            ("price_shipping_blmod", 0, "SHIPMENT_COST", "bl_extra", "' . $feed_id . '"),
            ("quantity", 1, "QUANTITY", "product", "' . $feed_id . '"),
            ("name", 1, "CATEGORY_NAME", "category_lang", "' . $feed_id . '")';

        $fields['sco'] = '
            ("name", 1, "TITLE", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "DESCRIPTION", "product_lang", "' . $feed_id . '"),
            ("large", 1, "IMAGE", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "BRAND", "manufacturer", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "PRICE", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "BUY_URL", "bl_extra", "' . $feed_id . '"),
            ("ean13", 1, "EAN_UPC", "product", "' . $feed_id . '"),
            ("reference", 1, "SKU", "product", "' . $feed_id . '"),
            ("price_shipping_blmod", 0, "SHIPPING_COSTS", "bl_extra", "' . $feed_id . '"),
            ("available_for_order", 1, "DELIVERYTIME", "product", "' . $feed_id . '"),
            ("quantity", 1, "STOCK", "product", "' . $feed_id . '"),
            ("name", 1, "MAIN_CATEGORY", "category_lang", "' . $feed_id . '")';

        $fields['lbb'] = '
            ("name", 1, "Titre_produit", "product_lang", "' . $feed_id . '"),
            ("id_product", 1, "Code_Produit", "product", "' . $feed_id . '"),
            ("description_short", 1, "description", "product_lang", "' . $feed_id . '"),
            ("large", 1, "URL_Image", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "Marque", "manufacturer", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "Prix_de_vente", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "URL_Produit", "bl_extra", "' . $feed_id . '"),
            ("ean13", 1, "gtin", "product", "' . $feed_id . '"),
            ("available_for_order", 1, "Disponibilité", "product", "' . $feed_id . '"),
            ("name", 1, "catégorie", "category_lang", "' . $feed_id . '")';

        $fields['ec'] = '
            ("name", 1, "Name", "product_lang", "' . $feed_id . '"),
            ("large", 1, "PictureUrl", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "Brand", "manufacturer", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "Price", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "ProductUrl", "bl_extra", "' . $feed_id . '"),
            ("ean13", 1, "Code", "product", "' . $feed_id . '"),
            ("reference", 1, "Sku", "product", "' . $feed_id . '"),
            ("price_shipping_blmod", 0, "ShippingCost", "bl_extra", "' . $feed_id . '"),
            ("available_for_order", 1, "DeliveryLabel", "product", "' . $feed_id . '"),
            ("quantity", 1, "Qty", "product", "' . $feed_id . '"),
            ("product_categories_tree", 1, "Category", "bl_extra", "' . $feed_id . '")';

        $fields['no'] = '
            ("id_product", 1, "id", "product", "' . $feed_id . '"),
            ("name", 1, "name", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "description", "product_lang", "' . $feed_id . '"),
            ("large", 1, "image", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "producer", "manufacturer", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "price", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "url", "bl_extra", "' . $feed_id . '"),
            ("available_for_order", 1, "availability", "product", "' . $feed_id . '"),
            ("quantity", 1, "instock", "product", "' . $feed_id . '"),
            ("price_shipping_blmod", 0, "shipping", "bl_extra", "' . $feed_id . '"),
            ("product_categories_tree", 1, "category", "bl_extra", "' . $feed_id . '")';

        $fields['gd'] = '
            ("id_product", 1, "id", "product", "' . $feed_id . '"),
            ("name", 1, "title", "product_lang", "' . $feed_id . '"),
            ("description_short", 1, "description", "product_lang", "' . $feed_id . '"),
            ("large", 1, "image", "img_blmod", "' . $feed_id . '"),
            ("name", 1, "brand", "manufacturer", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "price", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "link", "bl_extra", "' . $feed_id . '"),
            ("available_for_order", 1, "availability", "product", "' . $feed_id . '"),
            ("price_shipping_blmod", 0, "shipping_price", "bl_extra", "' . $feed_id . '"),
            ("ean13", 1, "upc", "product", "' . $feed_id . '"),
            ("reference", 1, "mpn", "product", "' . $feed_id . '"),
            ("product_categories_tree", 1, "product_type", "bl_extra", "' . $feed_id . '")';

        $fields['sfl'] = '
            ("name", 1, "name", "product_lang", "' . $feed_id . '"),
            ("large", 1, "image", "img_blmod", "' . $feed_id . '"),
            ("id_product", 1, "product_id", "product", "' . $feed_id . '"),
            ("price_sale_blmod", 1, "price", "bl_extra", "' . $feed_id . '"),
            ("product_url_blmod", 1, "url", "bl_extra", "' . $feed_id . '"),
            ("ean13", 1, "EAN", "product", "' . $feed_id . '"),
            ("reference", 1, "SKU", "product", "' . $feed_id . '"),
            ("available_for_order", 1, "shipping_lead_time", "product", "' . $feed_id . '"),
            ("quantity", 1, "quantity", "product", "' . $feed_id . '"),
            ("name", 1, "category", "category_lang", "' . $feed_id . '")';

        $blocks['m'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "images", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "url", "1", "' . $feed_id . '"),
            ("file-name", "urlset", "0", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '"),
            ("extra-product-rows", "' . htmlspecialchars('<changefreq>daily</changefreq><priority>0.9</priority>', ENT_QUOTES) . '", "1", "' . $feed_id . '")';

        $blocks['f'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "images", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "item", "1", "' . $feed_id . '"),
            ("file-name", "channel", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['s'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "images", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "product", "1", "' . $feed_id . '"),
            ("file-name", "products", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['i'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "images", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "item", "1", "' . $feed_id . '"),
            ("file-name", "channel", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['x'] = '("desc-block-name", "DESCRIPTIONS", "1", "' . $feed_id . '"),
            ("img-block-name", "IMAGES", "1", "' . $feed_id . '"),
            ("desc-block-name", "DESCRIPTIONS", "1", "' . $feed_id . '"),
            ("cat-block-name", "SHOPITEM", "1", "' . $feed_id . '"),
            ("file-name", "SHOP", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "CATEGORIES", "1", "' . $feed_id . '"),
            ("attributes-block-name", "ATTRIBUTES", "1", "' . $feed_id . '")';

        $blocks['r'] = '("desc-block-name", "Description", "1", "' . $feed_id . '"),
            ("img-block-name", "images", "1", "' . $feed_id . '"),
            ("desc-block-name", "Description", "1", "' . $feed_id . '"),
            ("cat-block-name", "Product", "1", "' . $feed_id . '"),
            ("file-name", "Products", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['c'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "images", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "product", "1", "' . $feed_id . '"),
            ("file-name", "products", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['h'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "images", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "PRODUCT", "1", "' . $feed_id . '"),
            ("file-name", "CATALOG", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['a'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "admarkt:ad", "1", "' . $feed_id . '"),
            ("img-block-name", "images", "1", "' . $feed_id . '"),
            ("file-name", "products", "0", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['o'] = '("desc-block-name", "DESCRIPTIONS", "1", "' . $feed_id . '"),
            ("img-block-name", "IMAGES", "1", "' . $feed_id . '"),
            ("desc-block-name", "DESCRIPTIONS", "1", "' . $feed_id . '"),
            ("cat-block-name", "SHOPITEM", "1", "' . $feed_id . '"),
            ("file-name", "SHOP", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "CATEGORIES", "1", "' . $feed_id . '"),
            ("attributes-block-name", "ATTRIBUTES", "1", "' . $feed_id . '")';

        $blocks['e'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "images", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "product", "1", "' . $feed_id . '"),
            ("file-name", "feed", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "categories", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['p'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "images", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "item", "1", "' . $feed_id . '"),
            ("file-name", "channel", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['u'] = '("desc-block-name", "DESCRIPTIONS", "1", "' . $feed_id . '"),
            ("img-block-name", "IMAGES", "1", "' . $feed_id . '"),
            ("desc-block-name", "DESCRIPTIONS", "1", "' . $feed_id . '"),
            ("cat-block-name", "SHOPITEM", "1", "' . $feed_id . '"),
            ("file-name", "SHOP", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "CATEGORIES", "1", "' . $feed_id . '"),
            ("attributes-block-name", "ATTRIBUTES", "1", "' . $feed_id . '")';

        $blocks['n'] = '("desc-block-name", "DESCRIPTIONS", "1", "' . $feed_id . '"),
            ("img-block-name", "IMAGES", "1", "' . $feed_id . '"),
            ("desc-block-name", "DESCRIPTIONS", "1", "' . $feed_id . '"),
            ("cat-block-name", "product", "1", "' . $feed_id . '"),
            ("file-name", "products", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "CATEGORIES", "1", "' . $feed_id . '"),
            ("attributes-block-name", "ATTRIBUTES", "1", "' . $feed_id . '")';

        $blocks['k'] = '("desc-block-name", "DESCRIPTIONS", "1", "' . $feed_id . '"),
            ("img-block-name", "IMAGES", "1", "' . $feed_id . '"),
            ("desc-block-name", "DESCRIPTIONS", "1", "' . $feed_id . '"),
            ("cat-block-name", "product", "1", "' . $feed_id . '"),
            ("file-name", "products", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "CATEGORIES", "1", "' . $feed_id . '"),
            ("attributes-block-name", "ATTRIBUTES", "1", "' . $feed_id . '")';

        $blocks['d'] = '("desc-block-name", "DESCRIPTIONS", "1", "' . $feed_id . '"),
            ("img-block-name", "IMAGES", "1", "' . $feed_id . '"),
            ("desc-block-name", "DESCRIPTIONS", "1", "' . $feed_id . '"),
            ("cat-block-name", "PRODUCT", "1", "' . $feed_id . '"),
            ("file-name", "LIST", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "CATEGORIES", "1", "' . $feed_id . '"),
            ("attributes-block-name", "ATTRIBUTES", "1", "' . $feed_id . '")';

        $blocks['gla'] = '("desc-block-name", "DESCRIPTIONS", "1", "' . $feed_id . '"),
            ("img-block-name", "IMAGES", "1", "' . $feed_id . '"),
            ("cat-block-name", "SHOPITEM", "1", "' . $feed_id . '"),
            ("file-name", "SHOP", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "CATEGORIES", "1", "' . $feed_id . '"),
            ("attributes-block-name", "ATTRIBUTES", "1", "' . $feed_id . '")';

        $blocks['sa'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "images", "1", "' . $feed_id . '"),
            ("cat-block-name", "offer", "1", "' . $feed_id . '"),
            ("file-name", "offers", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "categories", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['st'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "images", "1", "' . $feed_id . '"),
            ("cat-block-name", "offer", "1", "' . $feed_id . '"),
            ("file-name", "offers", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "categories", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['mm'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "images", "1", "' . $feed_id . '"),
            ("cat-block-name", "product", "1", "' . $feed_id . '"),
            ("file-name", "products", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "categories", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['vi'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "images", "1", "' . $feed_id . '"),
            ("cat-block-name", "product", "1", "' . $feed_id . '"),
            ("file-name", "vivino-product-list", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "categories", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['sm'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "Image", "1", "' . $feed_id . '"),
            ("cat-block-name", "Product", "1", "' . $feed_id . '"),
            ("file-name", "products", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "categories", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['rd'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "image", "1", "' . $feed_id . '"),
            ("cat-block-name", "product", "1", "' . $feed_id . '"),
            ("file-name", "products", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "categories", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['pub'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "image", "1", "' . $feed_id . '"),
            ("cat-block-name", "offer", "1", "' . $feed_id . '"),
            ("file-name", "offers", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "categories", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '"),
            ("extra-product-rows", "' . htmlspecialchars('<attribute><code>guarantee</code><value></value></attribute>', ENT_QUOTES) . '", "1", "' . $feed_id . '"),
            ("extra-offer-rows", "' . htmlspecialchars('<state>11</state><logistic-class></logistic-class><leadtime-to-ship></leadtime-to-ship><update-delete>Update</update-delete>', ENT_QUOTES) . '", "1", "' . $feed_id . '")';

        $blocks['ws'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "image", "1", "' . $feed_id . '"),
            ("cat-block-name", "row", "1", "' . $feed_id . '"),
            ("file-name", "product-list", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "categories", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '"),
            ("extra-product-rows", "' . htmlspecialchars('<min-order>1</min-order><tax>Inc</tax>', ENT_QUOTES) . '", "1", "' . $feed_id . '")';

        $blocks['dre'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "image", "1", "' . $feed_id . '"),
            ("cat-block-name", "offer", "1", "' . $feed_id . '"),
            ("file-name", "Products", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "categories", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['cen'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "image", "1", "' . $feed_id . '"),
            ("cat-block-name", "Item", "1", "' . $feed_id . '"),
            ("file-name", "CNJExport", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "categories", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['twe'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "Image", "1", "' . $feed_id . '"),
            ("cat-block-name", "Product", "1", "' . $feed_id . '"),
            ("file-name", "Products", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "categories", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['k24'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "image_url", "1", "' . $feed_id . '"),
            ("cat-block-name", "product", "1", "' . $feed_id . '"),
            ("file-name", "products", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "categories", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['kos'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "image_url", "1", "' . $feed_id . '"),
            ("cat-block-name", "product", "1", "' . $feed_id . '"),
            ("file-name", "products", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "categories", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['plt'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "image_url", "1", "' . $feed_id . '"),
            ("cat-block-name", "product", "1", "' . $feed_id . '"),
            ("file-name", "products", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "categories", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['aru'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "image_url", "1", "' . $feed_id . '"),
            ("cat-block-name", "Product", "1", "' . $feed_id . '"),
            ("file-name", "Products", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "categories", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['epr'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "image_url", "1", "' . $feed_id . '"),
            ("cat-block-name", "offer", "1", "' . $feed_id . '"),
            ("file-name", "offers", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "categories", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '"),
            ("extra-product-rows", "' . htmlspecialchars('<product-id-type>SKU</product-id-type>', ENT_QUOTES) . '", "1", "' . $feed_id . '")';

        $blocks['sez'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "image_url", "1", "' . $feed_id . '"),
            ("cat-block-name", "SHOPITEM", "1", "' . $feed_id . '"),
            ("file-name", "SHOP", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "categories", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['pri'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "BildeURL", "1", "' . $feed_id . '"),
            ("cat-block-name", "product", "1", "' . $feed_id . '"),
            ("file-name", "products", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "categories", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['mal'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "image_url", "1", "' . $feed_id . '"),
            ("cat-block-name", "ITEMS", "1", "' . $feed_id . '"),
            ("file-name", "ITEM", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "categories", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['spa'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "url", "1", "' . $feed_id . '"),
            ("cat-block-name", "product", "1", "' . $feed_id . '"),
            ("file-name", "products", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "categories", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['lw'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "images", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "product", "1", "' . $feed_id . '"),
            ("file-name", "products", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['naj'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "IMAGE_URL", "1", "' . $feed_id . '"),
            ("cat-block-name", "SHOPITEM", "1", "' . $feed_id . '"),
            ("file-name", "SHOP", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "categories", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['tot'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "IMAGE_URL", "1", "' . $feed_id . '"),
            ("cat-block-name", "product", "1", "' . $feed_id . '"),
            ("file-name", "products", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "categories", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['ceo'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "IMAGE_URL", "1", "' . $feed_id . '"),
            ("cat-block-name", "o", "1", "' . $feed_id . '"),
            ("file-name", "group", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "categories", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['bil'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "images", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "product", "1", "' . $feed_id . '"),
            ("file-name", "products", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['man'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "images", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "product", "1", "' . $feed_id . '"),
            ("file-name", "products", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['sal'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "images", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "item", "1", "' . $feed_id . '"),
            ("file-name", "root", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['hinn'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "images", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "Product", "1", "' . $feed_id . '"),
            ("file-name", "Pricelist", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['wum'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "img_url", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "offer", "1", "' . $feed_id . '"),
            ("file-name", "offers", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['mala'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "Image", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "Product", "1", "' . $feed_id . '"),
            ("file-name", "Products", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['cr'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "URLImage", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "Produit", "1", "' . $feed_id . '"),
            ("file-name", "Produits", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '"),
            ("extra-product-rows", "' . htmlspecialchars('<Disponibilité>1</Disponibilité>', ENT_QUOTES) . '", "1", "' . $feed_id . '")';

        $blocks['tov'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "Image", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "SHOPITEM", "1", "' . $feed_id . '"),
            ("file-name", "SHOP", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['che'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "Image", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "produit", "1", "' . $feed_id . '"),
            ("file-name", "produits", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['kie'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "Image", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "product", "1", "' . $feed_id . '"),
            ("file-name", "products", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['kog'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "Image", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "PRODUCT", "1", "' . $feed_id . '"),
            ("file-name", "PRODUCTS", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['mir'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "Image", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "product", "1", "' . $feed_id . '"),
            ("file-name", "products", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '"),
            ("extra-product-rows", "' . htmlspecialchars('<product-id-type>SHOP_SKU</product-id-type>', ENT_QUOTES) . '", "1", "' . $feed_id . '")';

        $blocks['rol'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "Image", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "entry", "1", "' . $feed_id . '"),
            ("file-name", "entries", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['kk'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "Image", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "product", "1", "' . $feed_id . '"),
            ("file-name", "products", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['tt'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "Image", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "product", "1", "' . $feed_id . '"),
            ("file-name", "productFeed", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['dm'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "Image", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "product", "1", "' . $feed_id . '"),
            ("file-name", "products", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['ep'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "picture", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "offer", "1", "' . $feed_id . '"),
            ("file-name", "offers", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['ro'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "picture", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "offer", "1", "' . $feed_id . '"),
            ("file-name", "offers", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['ar'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "picture", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "termek", "1", "' . $feed_id . '"),
            ("file-name", "termeklista", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['ho'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "picture", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "item", "1", "' . $feed_id . '"),
            ("file-name", "items", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['ek'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "picture", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "item", "1", "' . $feed_id . '"),
            ("file-name", "items", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['kuk'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "picture", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "product", "1", "' . $feed_id . '"),
            ("file-name", "products", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['dot'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "picture", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "product", "1", "' . $feed_id . '"),
            ("file-name", "products", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['pem'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "picture", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "item", "1", "' . $feed_id . '"),
            ("file-name", "produtos", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['cb'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "picture", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "product", "1", "' . $feed_id . '"),
            ("file-name", "products", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['gu'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "picture", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "item_data", "1", "' . $feed_id . '"),
            ("file-name", "DataFeeds", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['sn'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "picture", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "item", "1", "' . $feed_id . '"),
            ("file-name", "items", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['gp'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "picture", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "PRODUCT", "1", "' . $feed_id . '"),
            ("file-name", "PRODUCTS", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['sco'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "picture", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "Product", "1", "' . $feed_id . '"),
            ("file-name", "Products", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['lbb'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "picture", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "product", "1", "' . $feed_id . '"),
            ("file-name", "products", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['ec'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "picture", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "Product", "1", "' . $feed_id . '"),
            ("file-name", "Products", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['no'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "picture", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "product", "1", "' . $feed_id . '"),
            ("file-name", "products", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['gd'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "picture", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "offer", "1", "' . $feed_id . '"),
            ("file-name", "offers", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $blocks['sfl'] = '("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("img-block-name", "picture", "1", "' . $feed_id . '"),
            ("desc-block-name", "descriptions", "1", "' . $feed_id . '"),
            ("cat-block-name", "product", "1", "' . $feed_id . '"),
            ("file-name", "products", "1", "' . $feed_id . '"),
            ("def_cat-block-name", "default_category", "1", "' . $feed_id . '"),
            ("attributes-block-name", "attributes", "1", "' . $feed_id . '")';

        $options['m'] = 'header_information = "' . htmlspecialchars('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"> ', ENT_QUOTES) . '",
			footer_information = "' . htmlspecialchars('</urlset>', ENT_QUOTES) . '", one_branch = "1", cdata_status = "1"';
        $options['f'] = 'header_information = "' . htmlspecialchars('<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">', ENT_QUOTES) . '",
			footer_information = "' . htmlspecialchars('</rss>', ENT_QUOTES) . '", one_branch = "1", 
			price_with_currency = "1", cdata_status = "1"';
        $options['s'] = 'one_branch = "1", html_tags_status = "0", feed_generation_time = "1", cdata_status = "0",
        feed_generation_time_name = "created_at", in_stock_text = "Available in store / Delivery 1 to 3 days", out_of_stock_text = "Delivery 4 to 10 days"';
        $options['i'] = 'header_information = "' . htmlspecialchars('<rss xmlns:g="http://base.google.com/ns/1.0" xmlns:s="https://merchants.siroop.ch/" version="2.0">', ENT_QUOTES) . '",
			footer_information = "' . htmlspecialchars('</rss>', ENT_QUOTES) . '", one_branch = "1", price_with_currency = "1", cdata_status = "1"';
        $options['x'] = 'all_images = "1", filter_image = "1", in_stock_text = "1", out_of_stock_text = "0", cdata_status = "1"';
        $options['r'] = 'one_branch = "1", all_images = "0", in_stock_text = "INSTOCK", out_of_stock_text = "OUTOFSTOCK", cdata_status = "1"';
        $options['h'] = 'one_branch = "1", all_images = "1", cdata_status = "0"';
        $options['a'] = 'header_information = "' . htmlspecialchars('<admarkt:ads xmlns:admarkt="http://admarkt.marktplaats.nl/schemas/1.0">', ENT_QUOTES) . '",
			footer_information = "' . htmlspecialchars('</admarkt:ads>', ENT_QUOTES) . '", one_branch = "1", price_with_currency = "0", cdata_status = "1"';
        $options['o'] = 'all_images = "1", cdata_status = "1", in_stock_text = "Skladem", out_of_stock_text = "Skladem za 14 dní"';
        $options['e'] = 'one_branch = "1", all_images = "0", cdata_status = "1", in_stock_text = "1 tot 3 werkdagen", out_of_stock_text = "4 tot 10 werkdagen"';
        $options['p'] = 'one_branch = "1", price_with_currency = "1", cdata_status = "1"';
        $options['u'] = 'one_branch = "1", all_images = "0", cdata_status = "1"';
        $options['n'] = 'one_branch = "1", all_images = "0", cdata_status = "1", price_with_currency = "1"';
        $options['k'] = 'one_branch = "1", all_images = "0", cdata_status = "1", in_stock_text = "1", out_of_stock_text = "5"';
        $options['d'] = 'one_branch = "1", all_images = "0", cdata_status = "1", in_stock_text = "1 - 3 work days", out_of_stock_text = "Not available - preorder"';
        $options['gla'] = 'one_branch = "1", all_images = "0", cdata_status = "1", in_stock_text = "0", out_of_stock_text = "3"';
        $options['sa'] = 'one_branch = "1", all_images = "0", cdata_status = "1"';
        $options['st'] = 'one_branch = "1", all_images = "0", cdata_status = "1"';
        $options['mm'] = 'one_branch = "1", all_images = "0", cdata_status = "1"';
        $options['vi'] = 'one_branch = "1", all_images = "0", cdata_status = "1"';
        $options['sm'] = 'header_information = "' . htmlspecialchars('<store>', ENT_QUOTES) . '",
			footer_information = "' . htmlspecialchars('</store>', ENT_QUOTES) . '", one_branch = "1", cdata_status = "1"';
        $options['pub'] = 'header_information = "' . htmlspecialchars('<import>', ENT_QUOTES) . '",
			footer_information = "' . htmlspecialchars('</import>', ENT_QUOTES) . '", one_branch = "1", all_images = "0", cdata_status = "1"';
        $options['rd'] = 'one_branch = "1", all_images = "0", cdata_status = "1"';
        $options['ws'] = 'header_information = "' . htmlspecialchars('<wine-searcher-datafeed>', ENT_QUOTES) . '",
			footer_information = "' . htmlspecialchars('</wine-searcher-datafeed>', ENT_QUOTES) . '", one_branch = "1", all_images = "0", cdata_status = "0", 
			in_stock_text = "1 - 3 work days", out_of_stock_text = "Not available - preorder"';
        $options['dre'] = 'one_branch = "1", all_images = "0", cdata_status = "1"';
        $options['cen'] = 'one_branch = "1", all_images = "0", cdata_status = "1"';
        $options['twe'] = 'one_branch = "1", all_images = "0", cdata_status = "1", price_format_id="2", in_stock_text = "1 tot 3 dagen", out_of_stock_text = "10 werkdagen"';
        $options['k24'] = 'one_branch = "1", all_images = "0", cdata_status = "1", in_stock_text = "2", out_of_stock_text = "30"';
        $options['kos'] = 'one_branch = "1", all_images = "0", cdata_status = "1", in_stock_text = "2", out_of_stock_text = "30"';
        $options['plt'] = 'one_branch = "1", all_images = "0", cdata_status = "1", in_stock_text = "0-2", out_of_stock_text = "30-90"';
        $options['aru'] = 'one_branch = "1", all_images = "0", cdata_status = "1", in_stock_text = "Доставка: до 3 дн", out_of_stock_text = "Доставка: над 2 седмици"';
        $options['epr'] = 'header_information = "' . htmlspecialchars('<import>', ENT_QUOTES) . '",
			footer_information = "' . htmlspecialchars('</import>', ENT_QUOTES) . '", one_branch = "1", cdata_status = "1"';
        $options['sez'] = 'one_branch = "1", all_images = "0", cdata_status = "1", in_stock_text = "1-3 (do 3 dnů)", out_of_stock_text = "8 a více (více jak týden)"';
        $options['pri'] = 'one_branch = "1", all_images = "0", cdata_status = "1", in_stock_text = "in_stock", out_of_stock_text = "not_in_stock"';
        $options['mal'] = 'one_branch = "1", all_images = "1", cdata_status = "1", in_stock_text = "0", out_of_stock_text = "14"';
        $options['spa'] = 'one_branch = "1", all_images = "1", cdata_status = "1", header_information = "'.htmlspecialchars('<root>', ENT_QUOTES).'", footer_information = "'.htmlspecialchars('</root>', ENT_QUOTES).'"';
        $options['lw'] = 'one_branch = "1", html_tags_status = "0", cdata_status = "1", feed_generation_time = "1", feed_generation_time_name = "created_at", in_stock_text = "Available in store / Delivery 1 to 3 days", out_of_stock_text = "Delivery 4 to 10 days"';
        $options['naj'] = 'one_branch = "1", all_images = "0", cdata_status = "1", in_stock_text = "skladom", out_of_stock_text = "na otázku"';
        $options['tot'] = 'one_branch = "1", all_images = "0", cdata_status = "1", in_stock_text = "Διαθέσιμο", out_of_stock_text = "δεν υπάρχει", header_information = "'.htmlspecialchars('<catalog>', ENT_QUOTES).'", footer_information = "'.htmlspecialchars('</catalog>', ENT_QUOTES).'"';
        $options['ceo'] = 'one_branch = "1", all_images = "1", cdata_status = "1", in_stock_text = "1", out_of_stock_text = "7", header_information = "'.htmlspecialchars('<offers xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" version="1">', ENT_QUOTES).'", footer_information = "'.htmlspecialchars('</offers>', ENT_QUOTES).'"';
        $options['bil'] = 'one_branch = "1", all_images = "0", cdata_status = "1", in_stock_text = "1-3 days", out_of_stock_text = "20-30 days"';
        $options['man'] = 'one_branch = "1", all_images = "0", cdata_status = "1", in_stock_text = "2", out_of_stock_text = "50"';
        $options['sal'] = 'one_branch = "1", all_images = "0", cdata_status = "1"';
        $options['hinn'] = 'one_branch = "1", all_images = "0", cdata_status = "1"';
        $options['wum'] = 'one_branch = "1", all_images = "0", header_information = "'.htmlspecialchars('<wumler_feed version="0.1" language="en" currency="EUR">', ENT_QUOTES).'", 
        footer_information = "'.htmlspecialchars('</wumler_feed>', ENT_QUOTES).'", cdata_status = "1"';
        $options['mala'] = 'one_branch = "1", all_images = "1", split_by_combination = "1", cdata_status = "1"';
        $options['cr'] = 'one_branch = "1", all_images = "0", split_by_combination = "0", price_format_id = "2", cdata_status = "1"';
        $options['tov'] = 'one_branch = "1", all_images = "0", cdata_status = "1"';
        $options['che'] = 'one_branch = "1", all_images = "0", price_format_id = "2", cdata_status = "1"';
        $options['kie'] = 'one_branch = "1", all_images = "0", cdata_status = "1"';
        $options['kog'] = 'one_branch = "1", all_images = "1", in_stock_text = "3", out_of_stock_text = "14", cdata_status = "1"';
        $options['mir'] = 'one_branch = "1", all_images = "0", cdata_status = "1"';
        $options['rol'] = 'one_branch = "1", all_images = "0", cdata_status = "1"';
        $options['kk'] = 'one_branch = "1", all_images = "0", cdata_status = "1"';
        $options['tt'] = 'one_branch = "1", all_images = "0", cdata_status = "1"';
        $options['dm'] = 'one_branch = "1", all_images = "0", cdata_status = "1", header_information = "' . htmlspecialchars('<STORE xml_version="03">', ENT_QUOTES) . '", 
        footer_information = "' . htmlspecialchars('</STORE>', ENT_QUOTES) . '"';
        $options['ep'] = 'one_branch = "1", all_images = "0", cdata_status = "1"';
        $options['ro'] = 'one_branch = "1", all_images = "0", cdata_status = "1", header_information = "'.htmlspecialchars('<shop>
        <name>The short name of the store</name>
        <company>Full name of the company that owns the store</company>
        <url>https://www.your-store-address.ua/</url>
        <currencies>
            <currency id="UAH" rate="1"/>
        </currencies>', ENT_QUOTES).'", footer_information = "'.htmlspecialchars('</shop>', ENT_QUOTES).'"';
        $options['ar'] = 'one_branch = "1", all_images = "0", in_stock_text = "2-3 nap", out_of_stock_text = "10-14 nap", price_format_id="4", cdata_status = "1"';
        $options['ho'] = 'one_branch = "1", all_images = "0", cdata_status = "1", header_information = "'.htmlspecialchars('<price>
        <firmName>Store name</firmName>
        <firmId>Unique store ID (code)</firmId>', ENT_QUOTES).'", footer_information = "'.htmlspecialchars('</price>', ENT_QUOTES).'", in_stock_text = "In stock", out_of_stock_text = "Under the order"';
        $options['ek'] = 'one_branch = "1", all_images = "0", in_stock_text = "2-3", out_of_stock_text = "10-14", cdata_status = "1"';
        $options['kuk'] = 'one_branch = "1", all_images = "0", in_stock_text = "Y", out_of_stock_text = "N", cdata_status = "1"';
        $options['dot'] = 'one_branch = "1", all_images = "0", cdata_status = "1"';
        $options['pem'] = 'one_branch = "1", all_images = "0", cdata_status = "1"';
        $options['cb'] = 'one_branch = "1", all_images = "0", cdata_status = "1"';
        $options['gu'] = 'one_branch = "1", all_images = "0", cdata_status = "1"';
        $options['sn'] = 'one_branch = "1", all_images = "0", in_stock_text = "In stock", out_of_stock_text = "Out of stock", price_with_currency = "1", cdata_status = "1"';
        $options['gp'] = 'one_branch = "1", all_images = "0", cdata_status = "1"';
        $options['sco'] = 'one_branch = "1", all_images = "0", cdata_status = "1"';
        $options['lbb'] = 'one_branch = "1", all_images = "0", cdata_status = "1"';
        $options['ec'] = 'one_branch = "1", all_images = "0", in_stock_text = "1-3 days", out_of_stock_text = "7-14 days", cdata_status = "1"';
        $options['no'] = 'one_branch = "1", all_images = "0", in_stock_text = "0", out_of_stock_text = "1", cdata_status = "1"
        header_information = "' . htmlspecialchars('<!DOCTYPE nokaut SYSTEM "http://www.nokaut.pl/integracja/nokaut.dtd"><nokaut generator="BlModules">', ENT_QUOTES) . '", 
        footer_information = "' . htmlspecialchars('</nokaut>', ENT_QUOTES) . '"';
        $options['gd'] = 'one_branch = "1", all_images = "0", cdata_status = "1"';
        $options['sfl'] = 'header_information = "' . htmlspecialchars('<MPITEMS>', ENT_QUOTES) . '",
			footer_information = "' . htmlspecialchars('</MPITEMS>', ENT_QUOTES) . '", one_branch = "1", cdata_status = "1", all_images = "0", in_stock_text = "3", out_of_stock_text = "14"';

        $imageName['c'] = 'image';
        $imageName['f'] = 'g:image_link';
        $imageName['i'] = 'g:image_link';
        $imageName['s'] = 'image';
        $imageName['x'] = 'IMGURL';
        $imageName['r'] = 'Imageurl1';
        $imageName['h'] = 'image';
        $imageName['a'] = 'admarkt:media';
        $imageName['o'] = 'IMAGE';
        $imageName['e'] = 'url_productplaatje';
        $imageName['p'] = 'image_link';
        $imageName['u'] = 'IMGURL';
        $imageName['n'] = 'imageURL';
        $imageName['k'] = 'image-url';
        $imageName['d'] = 'IMAGE_URL';
        $imageName['gla'] = 'IMGURL';
        $imageName['sa'] = 'image';
        $imageName['st'] = 'img';
        $imageName['mm'] = 'image_1';
        $imageName['vi'] = '';
        $imageName['sm'] = 'Image';
        $imageName['pub'] = 'mainImage';
        $imageName['rd'] = 'URL_image_1';
        $imageName['ws'] = 'image';
        $imageName['dre'] = 'Image';
        $imageName['cen'] = 'mainImage';
        $imageName['twe'] = 'Image';
        $imageName['k24'] = 'image_url';
        $imageName['kos'] = 'image_url';
        $imageName['plt'] = 'image_url';
        $imageName['aru'] = 'Image_url';
        $imageName['epr'] = 'main_image_url';
        $imageName['sez'] = 'IMGURL';
        $imageName['pri'] = 'BildeURL';
        $imageName['mal'] = 'URL';
        $imageName['spa'] = 'url';
        $imageName['lw'] = 'image1';
        $imageName['naj'] = 'IMAGE_URL';
        $imageName['tot'] = 'image';
        $imageName['ceo'] = 'imgs';
        $imageName['bil'] = 'images';
        $imageName['man'] = 'picture';
        $imageName['sal'] = 'image';
        $imageName['hinn'] = 'Picture';
        $imageName['wum'] = 'img_url';
        $imageName['mala'] = 'Image';
        $imageName['cr'] = 'URLImage';
        $imageName['tov'] = 'IMGURL';
        $imageName['che'] = 'image';
        $imageName['kie'] = 'imagelink';
        $imageName['kog'] = 'IMAGES';
        $imageName['mir'] = 'mainImage';
        $imageName['rol'] = 'ImageURL';
        $imageName['kk'] = 'imagelink';
        $imageName['tt'] = 'imageURL';
        $imageName['dm'] = 'image';
        $imageName['ep'] = 'picture';
        $imageName['ro'] = 'picture';
        $imageName['ar'] = 'fotolink';
        $imageName['ho'] = 'image';
        $imageName['ek'] = 'image';
        $imageName['kuk'] = 'image_url';
        $imageName['dot'] = 'image_url';
        $imageName['pem'] = 'image';
        $imageName['cb'] = 'Image_url';
        $imageName['gu'] = 'item_image_url';
        $imageName['sn'] = 'image_link';
        $imageName['gp'] = 'IMAGE';
        $imageName['sco'] = 'IMAGE';
        $imageName['lbb'] = 'URL_Image';
        $imageName['ec'] = 'PictureUrl';
        $imageName['no'] = 'image';
        $imageName['gd'] = 'image';
        $imageName['sfl'] = 'image';

        $imageStatus = 1;

        if ($feedMode == 'twe' || $feedMode == 'sez') {
            $imageStatus = 0;
        }

        $image = $this->getBiggestImage();

        $id_lang = Configuration::get('PS_LANG_DEFAULT');

        if (!empty($id_lang)) {
            $fields[$feedMode] .= ', ("' . $id_lang . '", "1", "' . $id_lang . '", "lang", "' . $feed_id . '")';
        }

        if (!empty($image) && !empty($imageName[$feedMode])) {
            $fields[$feedMode] .= ', ("' . $image . '", "'.(int)$imageStatus.'", "' . $imageName[$feedMode] . '", "img_blmod", "' . $feed_id . '")';
        }

        $sql_blmod_fields_val = 'INSERT INTO ' . _DB_PREFIX_ . 'blmod_xml_fields
            (`name`, `status`, `title_xml`, `table`, `category`)
            VALUES ' . trim($fields[$feedMode], ',');
        Db::getInstance()->Execute($sql_blmod_fields_val);

        $sql_blmod_block_val = 'INSERT INTO ' . _DB_PREFIX_ . 'blmod_xml_block
            (`name`, `value`, `status`, `category`)
            VALUES ' . trim($blocks[$feedMode], ',');

        Db::getInstance()->Execute($sql_blmod_block_val);

        $options[$feedMode] = !empty($options[$feedMode]) ? $options[$feedMode] . ',' : false;
        $sql_blmod_option_val = 'UPDATE ' . _DB_PREFIX_ . 'blmod_xml_feeds SET ' . $options[$feedMode] . $sqlFeedType . ' WHERE id = "' . $feed_id . '"';
        Db::getInstance()->Execute($sql_blmod_option_val);

        if (in_array($feedMode, ['mala',])) {
            $productTitleEditor = new ProductTitleEditor();
            $_POST['title_editor_add_elements'][] = ProductTitleEditor::ADD_ALL_ATTRIBUTES;
            $_POST['title_editor_options'][] = 'attribute_name';
            $productTitleEditor->save($feed_id);
        }

        return true;
    }

    public function installDefaultFeedCategorySettings($feed_id)
    {
        $idLang = (int)Configuration::get('PS_LANG_DEFAULT');
        $feed_id = (int)$feed_id;

        $sql_blmod_fields_val = 'INSERT INTO ' . _DB_PREFIX_ . 'blmod_xml_fields
            (`name`, `status`, `title_xml`, `table`, `category`)
            VALUES
            ("meta_description", 1, "meta_description", "category_lang", "' . $feed_id . '"),
            ("meta_keywords", 0, "meta_keywords", "category_lang", "' . $feed_id . '"),
            ("meta_title", 0, "meta_title", "category_lang", "' . $feed_id . '"),
            ("link_rewrite", 0, "link_rewrite", "category_lang", "' . $feed_id . '"),
            ("description", 0, "description", "category_lang", "' . $feed_id . '"),
            ("name", 1, "name", "category_lang", "' . $feed_id . '"),
            ("id_lang", 1, "id_lang", "category_lang", "' . $feed_id . '"),
            ("date_upd", 0, "data_upd", "category", "' . $feed_id . '"),
            ("date_add", 0, "data_add", "category", "' . $feed_id . '"),
            ("active", 0, "active", "category", "' . $feed_id . '"),
            ("level_depth", 0, "level_depth", "category", "' . $feed_id . '"),
            ("id_parent", 1, "category_parent_id", "category", "' . $feed_id . '"),
            ("id_category", 1, "category_id", "category", "' . $feed_id . '"),
            ("' . $idLang . '", 1, "' . $idLang . '", "lang", "' . $feed_id . '")';

        Db::getInstance()->Execute($sql_blmod_fields_val);

        $sql_blmod_block_val = 'INSERT INTO ' . _DB_PREFIX_ . 'blmod_xml_block
            (`name`, `value`, `category`)
            VALUES
            ("desc-block-name", "descriptions", "' . $feed_id . '"),
            ("cat-block-name", "category", "' . $feed_id . '"),
            ("file-name", "categories", "' . $feed_id . '")';

        Db::getInstance()->Execute($sql_blmod_block_val);

        return true;
    }

    public function installDefaultFeedOrderSettings($feedId)
    {
        $feedId = (int)$feedId;

        $sql = 'INSERT INTO '._DB_PREFIX_.'blmod_xml_fields
            (`name`, `status`, `title_xml`, `table`, `category`)
            VALUES
            ("id_order", 1, "id_order", "orders", "'.$feedId.'"),
            ("invoice_number", 1, "invoice_number", "orders", "'.$feedId.'"),
            ("name", 1, "status_name", "order_state_lang", "'.$feedId.'"),
            ("payment", 1, "payment", "orders", "'.$feedId.'"),
            ("date_add", 1, "date_add", "orders", "'.$feedId.'"),
            ("total_paid", 1, "total_paid", "orders", "'.$feedId.'"),
            ("firstname", 1, "firstname", "customer", "'.$feedId.'"),
            ("lastname", 1, "lastname", "customer", "'.$feedId.'"),
            ("email", 1, "email", "customer", "'.$feedId.'"),
            ("phone", 1, "phone", "address", "'.$feedId.'"),
            ("product_id", 1, "product_id", "order_detail", "'.$feedId.'"),
            ("product_attribute_id", 1, "product_attribute_id", "order_detail", "'.$feedId.'"),
            ("product_quantity", 1, "product_quantity", "order_detail", "'.$feedId.'"),
            ("product_name", 1, "product_name", "order_detail", "'.$feedId.'"),
            ("product_price", 1, "product_price", "order_detail", "'.$feedId.'"),
            ("product_reference", 1, "product_reference", "order_detail", "'.$feedId.'")';

        Db::getInstance()->Execute($sql);

        $sql = 'INSERT INTO '._DB_PREFIX_.'blmod_xml_block
            (`name`, `value`, `category`)
            VALUES
            ("orders-branch-name", "orders", "'.$feedId.'"),
            ("order-branch-name", "order", "'.$feedId.'"),
            ("products-branch-name", "products", "'.$feedId.'"),
            ("product-branch-name", "product", "'.$feedId.'")';

        Db::getInstance()->Execute($sql);

        $sql = 'UPDATE '._DB_PREFIX_.'blmod_xml_feeds SET filter_date_type = "'.(int)OrderSettings::FILTER_DATE_THIS_MONTH.'" WHERE id = "'.$feedId.'"';
        Db::getInstance()->Execute($sql);

        return true;
    }

    public function getBiggestImage()
    {
        $images = Db::getInstance()->getRow('
			SELECT `name`
			FROM ' . _DB_PREFIX_ . 'image_type
			WHERE `products` = "1"
			ORDER BY `width` DESC, `height` DESC
		');

        if (empty($images['name'])) {
            return false;
        }

        return $images['name'];
    }

    public function upgrade262()
    {
        Db::getInstance()->Execute('ALTER TABLE ' . _DB_PREFIX_ . 'blmod_xml_feeds ADD `filter_qty_status` tinyint(1) NOT NULL DEFAULT "0"');
        Db::getInstance()->Execute('ALTER TABLE ' . _DB_PREFIX_ . 'blmod_xml_feeds ADD `filter_qty_type` varchar(5) CHARACTER SET utf8 DEFAULT NULL');
        Db::getInstance()->Execute('ALTER TABLE ' . _DB_PREFIX_ . 'blmod_xml_feeds ADD `filter_qty_value` int(11) NOT NULL DEFAULT "0"');

        Db::getInstance()->Execute('ALTER TABLE ' . _DB_PREFIX_ . 'blmod_xml_feeds ADD `price_format_id` tinyint(2) NOT NULL DEFAULT "0"');
    }

    public function upgrade263()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'blmod_xml_access_log 
            (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `feed_id` int(11) NOT NULL,
                `is_cron` tinyint(1) NOT NULL,
                `action` varchar(20) CHARACTER SET utf8 NOT NULL,
                `session_id` varchar(20) CHARACTER SET utf8 NOT NULL,
                `get_param` varchar(1000) CHARACTER SET utf8 NOT NULL,
                `argv_param` varchar(1000) CHARACTER SET utf8 NOT NULL,                
                `created_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            )';

        Db::getInstance()->Execute($sql);

        Db::getInstance()->Execute('ALTER TABLE ' . _DB_PREFIX_ . 'blmod_xml_feeds ADD `in_stock_text` varchar(255) CHARACTER SET utf8 DEFAULT NULL');
        Db::getInstance()->Execute('ALTER TABLE ' . _DB_PREFIX_ . 'blmod_xml_feeds ADD `out_of_stock_text` varchar(255) CHARACTER SET utf8 DEFAULT NULL');
    }

    public function upgrade2620()
    {
        Db::getInstance()->Execute('ALTER TABLE ' . _DB_PREFIX_ . 'blmod_xml_feeds ADD `categories_without` tinyint(1) NOT NULL DEFAULT "0"');
        Db::getInstance()->Execute('ALTER TABLE ' . _DB_PREFIX_ . 'blmod_xml_feeds ADD `filter_category_without_type` tinyint(1) NOT NULL DEFAULT "0"');
        Db::getInstance()->Execute('ALTER TABLE ' . _DB_PREFIX_ . 'blmod_xml_feeds ADD `cat_without_list` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL');
    }

    public function upgrade272()
    {
        Db::getInstance()->Execute('ALTER TABLE ' . _DB_PREFIX_ . 'blmod_xml_feeds ADD `order_state_status` tinyint(1) NOT NULL DEFAULT "0"');
        Db::getInstance()->Execute('ALTER TABLE ' . _DB_PREFIX_ . 'blmod_xml_feeds ADD `order_state` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL');
        Db::getInstance()->Execute('ALTER TABLE ' . _DB_PREFIX_ . 'blmod_xml_feeds ADD `order_payments_status` tinyint(1) NOT NULL DEFAULT "0"');
        Db::getInstance()->Execute('ALTER TABLE ' . _DB_PREFIX_ . 'blmod_xml_feeds ADD `order_payment` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL');

        Db::getInstance()->Execute('ALTER TABLE ' . _DB_PREFIX_ . 'blmod_xml_feeds ADD `filter_date_type` tinyint(2) NOT NULL DEFAULT "0"');
        Db::getInstance()->Execute('ALTER TABLE ' . _DB_PREFIX_ . 'blmod_xml_feeds ADD `filter_date_from` varchar(10) CHARACTER SET utf8 DEFAULT NULL');
        Db::getInstance()->Execute('ALTER TABLE ' . _DB_PREFIX_ . 'blmod_xml_feeds ADD `filter_date_to` varchar(255) CHARACTER SET utf8 DEFAULT NULL');
        Db::getInstance()->Execute('ALTER TABLE ' . _DB_PREFIX_ . 'blmod_xml_feeds ADD `filter_custom_days` int(11) NOT NULL DEFAULT "0"');
    }

    public function upgrade2712()
    {
        Db::getInstance()->Execute('ALTER TABLE ' . _DB_PREFIX_ . 'blmod_xml_feeds ADD `merge_attributes_by_group` tinyint(1) NOT NULL DEFAULT "0"');
        Db::getInstance()->Execute('ALTER TABLE ' . _DB_PREFIX_ . 'blmod_xml_feeds ADD `merge_attributes_parent` int(11) NOT NULL DEFAULT "0"');
        Db::getInstance()->Execute('ALTER TABLE ' . _DB_PREFIX_ . 'blmod_xml_feeds ADD `merge_attributes_child` int(11) NOT NULL DEFAULT "0"');
        Db::getInstance()->Execute('ALTER TABLE ' . _DB_PREFIX_ . 'blmod_xml_feeds ADD `only_with_attributes_status` tinyint(1) NOT NULL DEFAULT "0"');
        Db::getInstance()->Execute('ALTER TABLE ' . _DB_PREFIX_ . 'blmod_xml_feeds ADD `only_with_attributes` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL');
        Db::getInstance()->Execute('ALTER TABLE ' . _DB_PREFIX_ . 'blmod_xml_feeds ADD `only_without_attributes_status` tinyint(1) NOT NULL DEFAULT "0"');
        Db::getInstance()->Execute('ALTER TABLE ' . _DB_PREFIX_ . 'blmod_xml_feeds ADD `only_without_attributes` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL');
    }

    public function upgrade2714()
    {
        Db::getInstance()->Execute('ALTER TABLE ' . _DB_PREFIX_ . 'blmod_xml_feeds ADD `product_list_exclude` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL');
    }

    public function upgrade287()
    {
        Db::getInstance()->Execute('ALTER TABLE ' . _DB_PREFIX_ . 'blmod_xml_feeds ADD `filter_image` tinyint(2) NOT NULL DEFAULT "0"');
    }

    public function upgrade293()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'blmod_xml_category_map
            (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `title` varchar(255) CHARACTER SET utf8 NOT NULL,
                `file_name` varchar(255) CHARACTER SET utf8 NOT NULL,
                PRIMARY KEY (`id`)
            )';

        Db::getInstance()->Execute($sql);

        $sqlList = 'INSERT INTO ' . _DB_PREFIX_ . 'blmod_xml_category_map
            (`id`, `title`, `file_name`)
            VALUES
            ("1", "Beslist NL", "beslist-nl-NL.txt"),
            ("2", "Fruugo EN", "fruugo-en-US"),
            ("3", "Glami EN", "glami-en-US.txt"),
            ("4", "Google/Facebook EN", "google-en-US.txt"),
            ("5", "Google/Facebook FR", "google-fr-FR.txt"),
            ("6", "Google/Facebook IT", "google-it-IT.txt"),
            ("7", "Heureka CZ", "heureka-cz-CZ.txt"),
            ("8", "Marktplaats NL", "marktplaats-nl-NL.txt"),
            ("9", "MALL", "mall-sk-SK.txt"),
            ("10", "Spartoo", "spartoo-it-IT.txt")';
        Db::getInstance()->Execute($sqlList);

        Db::getInstance()->Execute('ALTER TABLE ' . _DB_PREFIX_ . 'blmod_xml_feeds ADD `category_map_id` int(11) NOT NULL DEFAULT "0"');
        Db::getInstance()->Execute('ALTER TABLE ' . _DB_PREFIX_ . 'blmod_xml_feeds ADD `encoding_text` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT "UTF-8"');
    }

    public function upgrade295()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'blmod_xml_feed_search_query
            (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `value` varchar(255) CHARACTER SET utf8 NOT NULL,                
				`ip_address` varchar(15) CHARACTER SET utf8 DEFAULT NULL,
				`date_add` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            )';

        Db::getInstance()->Execute($sql);
    }

    public function upgrade303()
    {
        Db::getInstance()->Execute('ALTER TABLE '._DB_PREFIX_.'blmod_xml_feeds ADD `only_on_sale` tinyint(1) NOT NULL DEFAULT "0"');
    }

    public function upgrade304()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'blmod_xml_product_property_map
            (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(255) CHARACTER SET utf8 NOT NULL,                
				`type_id` int(11) DEFAULT 0,
				`created_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            )';

        Db::getInstance()->Execute($sql);

        $sql = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'blmod_xml_product_property_map_value
            (
                `id` INT(11) NOT NULL AUTO_INCREMENT,	              
                `map_id` INT(11) DEFAULT 0,
                `group_id` INT(11) DEFAULT 0,
                `property_id` INT(11) DEFAULT 0,
                `value` VARCHAR(255) CHARACTER SET utf8 NOT NULL,
                `created_at` DATETIME DEFAULT NULL,
                PRIMARY KEY (`id`)
            )';

        Db::getInstance()->Execute($sql);

        Db::getInstance()->Execute('ALTER TABLE '._DB_PREFIX_.'blmod_xml_feeds ADD `attribute_map_id` int(11) NOT NULL DEFAULT "0"');
        Db::getInstance()->Execute('ALTER TABLE '._DB_PREFIX_.'blmod_xml_feeds ADD `feature_map_id` int(11) NOT NULL DEFAULT "0"');
    }

    public function upgrade315()
    {
        Db::getInstance()->Execute('ALTER TABLE '._DB_PREFIX_.'blmod_xml_feeds ADD `title_replace` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL');
        Db::getInstance()->Execute('ALTER TABLE '._DB_PREFIX_.'blmod_xml_feeds ADD `title_new_elements` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL');
    }

    public function upgrade319()
    {
        Db::getInstance()->Execute('ALTER TABLE '._DB_PREFIX_.'blmod_xml_product_list_product ADD `category_id` int(11) NOT NULL DEFAULT "0"');
    }

    public function upgrade3115()
    {
        Db::getInstance()->Execute('ALTER TABLE '._DB_PREFIX_.'blmod_xml_feeds ADD `on_demand_stock_text` VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL');
        Db::getInstance()->Execute('ALTER TABLE '._DB_PREFIX_.'blmod_xml_feeds ADD `protect_by_ip` varchar(1000) CHARACTER SET utf8 NOT NULL DEFAULT ""');
        Db::getInstance()->Execute('ALTER TABLE '._DB_PREFIX_.'blmod_xml_feeds ADD `filter_exclude_empty_params` varchar(100) CHARACTER SET utf8 NOT NULL DEFAULT ""');
        Db::getInstance()->Execute('ALTER TABLE '._DB_PREFIX_.'blmod_xml_feeds ADD `only_available_for_order` tinyint(1) NOT NULL DEFAULT "0"');
        Db::getInstance()->Execute('ALTER TABLE '._DB_PREFIX_.'blmod_xml_category_map ADD `key` VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL');

        $sqlList = 'INSERT INTO ' . _DB_PREFIX_ . 'blmod_xml_category_map
            (`title`, `file_name`, `key`)
            VALUES
            ("Kogan", "kogan_ebay-en-EN.txt", "kogan_ebay_en")';
        Db::getInstance()->Execute($sqlList);
    }

    public function isValidMainTable()
    {
        $totalColumns = Db::getInstance()->getValue('
            SELECT COUNT(*) AS total 
            FROM information_schema.columns
            WHERE table_name = "'._DB_PREFIX_.'blmod_xml_feeds" AND TABLE_SCHEMA = "'.htmlspecialchars(_DB_NAME_, ENT_QUOTES).'"
        ');

        return ($totalColumns == self::BLMOD_XML_FEEDS_COLUMNS) ? true : false;
    }

    public function runDatabaseUpgrade()
    {
        $methods = get_class_methods($this);

        try {
            foreach ($methods as $m) {
                if (Tools::substr($m, 0, 7) != 'upgrade') {
                    continue;
                }

                $this->$m();
            }
        } catch (Exception $e) {
        }
    }
}

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

$_SERVER['REQUEST_URI'] = !empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/modules/xmlfeeds/api/xml.php?id=0';
$_SERVER['SCRIPT_NAME'] = !empty($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '/modules/xmlfeeds/api/xml.php';
$_SERVER['REQUEST_METHOD'] = !empty($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
$_SERVER['REMOTE_ADDR'] = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '8.8.8.8';

if (!defined('_PS_VERSION_')) {
    require_once(dirname(__FILE__).'/../../../config/config.inc.php');
}

require_once(_PS_MODULE_DIR_.'/xmlfeeds/XmlFeedsTools.php');
require_once(_PS_MODULE_DIR_.'/xmlfeeds/googlecategory.php');
require_once(_PS_MODULE_DIR_.'/xmlfeeds/ProductList.php');
require_once(_PS_MODULE_DIR_.'/xmlfeeds/ProductSettings.php');
require_once(_PS_MODULE_DIR_.'/xmlfeeds/FeedType.php');
require_once(_PS_MODULE_DIR_.'/xmlfeeds/PriceFormat.php');
require_once(_PS_MODULE_DIR_.'/xmlfeeds/AccessLog.php');
require_once(_PS_MODULE_DIR_.'/xmlfeeds/OrderSettings.php');
require_once(_PS_MODULE_DIR_.'/xmlfeeds/api/OrderXmlApi.php');
require_once(_PS_MODULE_DIR_.'/xmlfeeds/MergeAttributesByGroup.php');
require_once(_PS_MODULE_DIR_.'/xmlfeeds/FilterByAttribute.php');
require_once(_PS_MODULE_DIR_.'/xmlfeeds/XmlFeedUrl.php');
require_once(_PS_MODULE_DIR_.'/xmlfeeds/CategoryMap.php');
require_once(_PS_MODULE_DIR_.'/xmlfeeds/ProductPropertyMap.php');
require_once(_PS_MODULE_DIR_.'/xmlfeeds/ProductTitleEditor.php');
require_once(_PS_MODULE_DIR_.'/xmlfeeds/FeedMeta.php');
require_once(_PS_MODULE_DIR_.'/xmlfeeds/FeedPrice.php');
require_once(_PS_MODULE_DIR_.'/xmlfeeds/ProductCombinations.php');
require_once(_PS_MODULE_DIR_.'/xmlfeeds/api/CustomerXmlApi.php');
require_once(_PS_MODULE_DIR_.'/xmlfeeds/api/BrandXmlApi.php');
require_once(_PS_MODULE_DIR_.'/xmlfeeds/api/SupplierXmlApi.php');
require_once(_PS_MODULE_DIR_.'/xmlfeeds/api/CategoryXmlApi.php');
require_once(_PS_MODULE_DIR_.'/xmlfeeds/api/ProductXmlApi.php');
require_once(_PS_MODULE_DIR_.'/xmlfeeds/vendor/FormulaParser.php');

$feedMeta = new FeedMeta();
$protocol = getShopProtocol();

const REPLACE_COMBINATION = 'BLMOD_REPLACE_COMBINATION;';

if (!defined('_PS_VERSION_')) {
    die('Not Allowed, api root');
}

$id = htmlspecialchars(Tools::getValue('id'), ENT_QUOTES);
$part = htmlspecialchars(Tools::getValue('part'), ENT_QUOTES);
$affiliateParam = Tools::getValue('affiliate');
$multistore = Tools::getValue('multistore');
$downloadAction = Tools::getValue('download');
$xmlFeedType = Tools::getValue('type');
$isCron = false;
$sessionId = Tools::substr(md5(microtime().rand(1, 9999999)), 0, 16);
$argv = !empty($argv) ? $argv : array();

$affiliate = htmlspecialchars((is_array($affiliateParam) ? implode(',', $affiliateParam) : $affiliateParam), ENT_QUOTES);

if (!empty($argv[1])) {
    /**
     * Reset currency id, for new PS
     */
    if (class_exists('Context', false)) {
        $context = Context::getContext();
        $context->currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        $context->link = _PS_BASE_URL_.'/modules/xmlfeeds/api/xml.php?id=l1';
        $context->shop = new Shop(Configuration::get('PS_SHOP_DEFAULT'));
        Context::getContext()->cart = new Cart();
    }

    $id = $argv[1];
    $isCron = true;
}

if (!empty($argv[2])) {
    $affiliate = $argv[2];
}

if (!empty($argv[3])) {
    $multistore = $argv[3];
}

if (!empty($argv[4])) {
    $xmlFeedType = $argv[4];
}

if (!is_numeric($id)) {
    die('empty id');
}

if ($affiliate == 'affiliate_name') {
    $affiliate = '';
}

if (!empty($multistore)) {
    $context->shop = new Shop((int)$multistore);
}

$check_affiliate = Db::getInstance()->getRow('SELECT `affiliate_name` FROM '._DB_PREFIX_.'blmod_xml_affiliate_price WHERE `affiliate_name` = "'.pSQL($affiliate).'"');

$affiliate_cache = '';

$permissions = Db::getInstance()->getRow('SELECT f.*, c.file_name AS file_name_n, c.last_cache_time AS last_cache_time_n
	FROM '._DB_PREFIX_.'blmod_xml_feeds f
	LEFT JOIN '._DB_PREFIX_.'blmod_xml_feeds_cache c ON
	(f.id = c.feed_id AND c.feed_part = "'.(int)$part.'" AND c.affiliate_name = "'.pSQL($affiliate_cache).'")
	WHERE f.id = "'.(int)$id.'"');

if (empty($permissions)) {
    die('empty settings');
}

AccessLog::save($id, 'start', $sessionId, $isCron, $_GET, $argv);
AccessLog::deleteOld();

$feed_name = 'archive_'.$id;

$permissions['use_cache'] = isset($permissions['use_cache']) ? $permissions['use_cache'] : false;
$permissions['cache_time'] = isset($permissions['cache_time']) ? $permissions['cache_time'] : false;
$permissions['last_cache_time'] = isset($permissions['last_cache_time_n']) ? $permissions['last_cache_time_n'] : '0000-00-00 00:00:00';
$permissions['use_password'] = isset($permissions['use_password']) ? $permissions['use_password'] : false;
$permissions['password'] = isset($permissions['password']) ? $permissions['password'] : false;
$permissions['status'] = isset($permissions['status']) ? $permissions['status'] : false;
$permissions['file_name'] = isset($permissions['file_name_n']) ? $permissions['file_name_n'] : false;
$permissions['html_tags_status'] = isset($permissions['html_tags_status']) ? $permissions['html_tags_status'] : false;
$permissions['one_branch'] = isset($permissions['one_branch']) ? $permissions['one_branch'] : false;
$permissions['header_information'] = isset($permissions['header_information']) ? htmlspecialchars_decode($permissions['header_information'], ENT_QUOTES) : false;
$permissions['footer_information'] = isset($permissions['footer_information']) ? htmlspecialchars_decode($permissions['footer_information'], ENT_QUOTES) : false;
$permissions['extra_feed_row'] = isset($permissions['extra_feed_row']) ? htmlspecialchars_decode($permissions['extra_feed_row'], ENT_QUOTES) : false;
$permissions['only_enabled'] = isset($permissions['only_enabled']) ? $permissions['only_enabled'] : false;
$permissions['split_feed'] = isset($permissions['split_feed']) ? $permissions['split_feed'] : false;
$permissions['split_feed_limit'] = isset($permissions['split_feed_limit']) ? $permissions['split_feed_limit'] : false;
$permissions['cat_list'] = isset($permissions['cat_list']) ? $permissions['cat_list'] : false;
$permissions['categories'] = isset($permissions['categories']) ? $permissions['categories'] : false;
$permissions['price_with_currency'] = isset($permissions['price_with_currency']) ? $permissions['price_with_currency'] : false;
$permissions['manufacturer_list'] = isset($permissions['manufacturer_list']) ? $permissions['manufacturer_list'] : false;
$permissions['manufacturer'] = isset($permissions['manufacturer']) ? $permissions['manufacturer'] : false;
$permissions['supplier_list'] = isset($permissions['supplier_list']) ? $permissions['supplier_list'] : false;
$permissions['supplier'] = isset($permissions['supplier']) ? $permissions['supplier'] : false;
$permissions['currency_id'] = isset($permissions['currency_id']) ? $permissions['currency_id'] : false;
$permissions['feed_generation_time'] = isset($permissions['feed_generation_time']) ? $permissions['feed_generation_time'] : false;
$permissions['feed_generation_time_name'] = isset($permissions['feed_generation_time_name']) ? $permissions['feed_generation_time_name'] : false;
$permissions['split_by_combination'] = isset($permissions['split_by_combination']) ? $permissions['split_by_combination'] : false;
$useCron = !empty($permissions['use_cron']) ? $permissions['use_cron'] : false;
$feed_type = isset($permissions['feed_type']) ? $permissions['feed_type'] : false;
$onlyInStock = !empty($permissions['only_in_stock']) ? $permissions['only_in_stock'] : false;
$priceRange = !empty($permissions['price_range']) ? $permissions['price_range'] : false;
$mode = !empty($permissions['feed_mode']) ? $permissions['feed_mode'] : false;
$allImages = !empty($permissions['all_images']) ? $permissions['all_images'] : false;
$productList = !empty($permissions['product_list']) ? explode(',', $permissions['product_list']) : array();
$productListStatus = !empty($permissions['product_list_status']) ? $permissions['product_list_status'] : false;
$shippingCountry = !empty($permissions['shipping_country']) ? $permissions['shipping_country'] : false;
$filterDiscount = !empty($permissions['filter_discount']) ? $permissions['filter_discount'] : 0;
$filterCategoryType = !empty($permissions['filter_category_type']) ? $permissions['filter_category_type'] : 0;
$productSettingsPackageId = !empty($permissions['product_settings_package_id']) ? $permissions['product_settings_package_id'] : 0;
$permissions['filter_qty_status'] = !empty($permissions['filter_qty_status']) ? $permissions['filter_qty_status'] : 0;
$permissions['filter_qty_type'] = !empty($permissions['filter_qty_type']) ? $permissions['filter_qty_type'] : 0;
$permissions['filter_qty_value'] = !empty($permissions['filter_qty_value']) ? $permissions['filter_qty_value'] : 0;
$permissions['price_format_id'] = !empty($permissions['price_format_id']) ? $permissions['price_format_id'] : 0;
$permissions['in_stock_text'] = isset($permissions['in_stock_text']) ? $permissions['in_stock_text'] : '';
$permissions['out_of_stock_text'] = isset($permissions['out_of_stock_text']) ? $permissions['out_of_stock_text'] : '';
$permissions['merge_attributes_by_group'] = !empty($permissions['merge_attributes_by_group']) ? $permissions['merge_attributes_by_group'] : 0;
$permissions['merge_attributes_parent'] = !empty($permissions['merge_attributes_parent']) ? $permissions['merge_attributes_parent'] : 0;
$permissions['merge_attributes_child'] = !empty($permissions['merge_attributes_child']) ? $permissions['merge_attributes_child'] : 0;
$permissions['only_with_attributes_status'] = !empty($permissions['only_with_attributes_status']) ? $permissions['only_with_attributes_status'] : 0;
$permissions['only_with_attributes'] = !empty($permissions['only_with_attributes']) ? explode(',', $permissions['only_with_attributes']) : array();
$permissions['only_without_attributes_status'] = !empty($permissions['only_without_attributes_status']) ? $permissions['only_without_attributes_status'] : 0;
$permissions['only_without_attributes'] = !empty($permissions['only_without_attributes']) ? explode(',', $permissions['only_without_attributes']) : array();
$permissions['product_list_exclude'] = !empty($permissions['product_list_exclude']) ? explode(',', $permissions['product_list_exclude']) : array();
$permissions['category_map_id'] = !empty($permissions['category_map_id']) ? $permissions['category_map_id'] : 0;
$permissions['encoding_text'] = !empty($permissions['encoding_text']) ? $permissions['encoding_text'] : 'UTF-8';
$permissions['only_on_sale'] = !empty($permissions['only_on_sale']) ? $permissions['only_on_sale'] : 0;
$permissions['filter_exclude_empty_params'] = !empty($permissions['filter_exclude_empty_params']) ? explode(',', $permissions['filter_exclude_empty_params']) : '';
$permissions['xml_type'] = $xmlFeedType;
$feedMetaValues = $feedMeta->getFeedMeta($id);
$permissions = array_merge($permissions, $feedMetaValues[$id]);
$taxRateList = array();

if (!empty($permissions['affiliate'])) {
    $affiliateList = Db::getInstance()->ExecuteS('SELECT a.affiliate_name
        FROM '._DB_PREFIX_.'blmod_xml_affiliate_price a
        WHERE a.affiliate_id IN ('.pSQL(implode($permissions['affiliate'], ',')).')');

    unset($affiliate);

    foreach ($affiliateList as $a) {
        $affiliate[] = $a['affiliate_name'];
    }
} else if (!empty($affiliate)) {
    $affiliate2 = $affiliate;
    unset($affiliate);

    $affiliate[] = $affiliate2;
}

$affiliateNameString = is_array($affiliate) ? implode(',', $affiliate) : '';

if (!empty($shippingCountry) && class_exists('Context', false)) {
    if (empty($context)) {
        $context = Context::getContext();
    }

    $context->country->id = $shippingCountry;
}

if ($useCron) {
    $permissions['split_feed'] = false;
}

$settings = $permissions;

if ($permissions['status'] != 1) {
    die('disabled');
}

if ($permissions['use_password'] == 1 && !empty($permissions['password']) && !$useCron) {
    $pass = Tools::getValue('password');

    if ($permissions['password'] != $pass) {
        die('permissions, password');
    }
}

if (!empty($permissions['protect_by_ip']) && !$useCron) {
    $ipList = explode(',', str_replace(' ', '', trim($permissions['protect_by_ip'])));

    if (!empty($ipList) && !in_array(get_ip(), $ipList)) {
        die('permissions, IP address');
    }
}

if (!$useCron) {
    insert_statistics($id, $affiliateNameString);
}

$now = date('Y-m-d h:i:s');
$cache_period = date('Y-m-d h:i:s', strtotime($permissions['last_cache_time'].'+ '.$permissions['cache_time'].' minutes'));

if ($permissions['use_cache'] && !$useCron) {
    $file_url = _PS_ROOT_DIR_.'/modules/xmlfeeds/xml_files/'.$permissions['file_name'].'.xml';

    if ($now < $cache_period) {
        if (!empty($permissions['file_name'])) {
            $xml = Tools::file_get_contents($file_url);
        }

        if (!empty($xml)) {
            header('Content-type: text/xml;charset:'.$permissions['encoding_text']);

            $download = Tools::getValue('download');

            if (!empty($download)) {
                header('Content-Disposition:attachment;filename='.$feed_name.'_feed.xml');
            }

            AccessLog::save($id, 'end_cache', $sessionId);

            echo '<?xml version="1.0" encoding="'.$permissions['encoding_text'].'"?>';
            echo $xml;
            die;
        }
    } else {
        Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'blmod_xml_feeds_cache WHERE `feed_id` = "'.(int)$id.'" AND `affiliate_name` = "'.pSQL($affiliate_cache).'"');
        @unlink($file_url);
    }
}

if (!empty($permissions['cdata_status'])) {
    $pref_s = '<![CDATA[';
    $pref_e = ']]>';
} else {
    $pref_s = '';
    $pref_e = '';
}

$multistoreArray = array();
$multistoreString = false;

if (!empty($multistore)) {
    if ($multistore == 'auto') {
        $multistoreString = Context::getContext()->shop->id;
    } else {
        $multistoreArrayCheck = explode(',', $multistore);

        foreach ($multistoreArrayCheck as $m) {
            $mId = (int) $m;

            if (empty($mId)) {
                continue;
            }

            $multistoreArray[] = $mId;
        }

        $multistoreString = implode(',', $multistoreArray);
    }
}

function create_split_xml_product(
    $only_enabled = false,
    $limit = 5000,
    $page = 1,
    $use_password = false,
    $password = false,
    $affiliate = false,
    $multistoreString = 0,
    $categories = false,
    $cat_list = '',
    $filterCategoryType = false,
    $permissions = array(),
    $settings = array(),
    $productList = array(),
    $productListStatus = false
) {
    $xmlFeedUrl = new XmlFeedUrl();
    $productListClass = new ProductList($settings['product_list_exclude']);
    $where_only_active = '';

    if (!empty($only_enabled)) {
        $where_only_active = 'WHERE '._DB_PREFIX_.'product.active = "1"';
    }

    $category_table = false;

    if (!empty($categories) && !empty($cat_list)) {
        if (empty($filterCategoryType)) {
            $category_table = '
                LEFT JOIN ' . _DB_PREFIX_ . 'category_product ON
                ' . _DB_PREFIX_ . 'category_product.id_product = ' . _DB_PREFIX_ . 'product.id_product ';

            $where_only_active .= whereType($where_only_active) . _DB_PREFIX_ . 'category_product.id_category IN ('.pSQL($cat_list).')';
        } else {
            $category_table = 'INNER JOIN '._DB_PREFIX_.'category_product ON
                ('._DB_PREFIX_.'category_product.id_product = '._DB_PREFIX_.'product.id_product AND '._DB_PREFIX_.'category_product.id_category IN ('.pSQL($cat_list).'))';
        }
    }

    $multistoreJoin = '';

    if (!empty($multistoreString)) {
        $multistoreJoin = ' INNER JOIN '._DB_PREFIX_.'product_shop ps ON
        (ps.id_product = '._DB_PREFIX_.'product.id_product AND ps.id_shop IN ('.(int)$multistoreString.')) AND ps.`active` = "1" ';
    }

    if (!empty($permissions['manufacturer']) && !empty($permissions['manufacturer_list'])) {
        $where_only_active .= whereType($where_only_active)._DB_PREFIX_.'product.id_manufacturer IN ('.pSQL($permissions['manufacturer_list']).')';
    }

    if (!empty($permissions['supplier']) && !empty($permissions['supplier_list'])) {
        $where_only_active .= whereType($where_only_active)._DB_PREFIX_.'product.id_supplier IN ('.pSQL($permissions['supplier_list']).')';
    }

    if ((!empty($settings['product_list_exclude']) || !empty($productList)) && !empty($productListStatus)) {
        $productListExcludeActive = $productListClass->getExcludeProductsByProductList();
        $productListActive = $productListClass->getProductsByProductList($productList, $productListExcludeActive);
        $productListActive = !empty($productListActive) ? $productListActive : array('"none_id"');

        $productListExcludeActive = $productListClass->getExcludeProductsByProductList();

        if (!empty($productList)) {
            $where_only_active .= whereType($where_only_active) . _DB_PREFIX_ . 'product.id_product IN (' . pSQL(implode(',', $productListActive)) . ')';
        }

        if (!empty($productListExcludeActive)) {
            $where_only_active .= whereType($where_only_active)._DB_PREFIX_.'product.id_product NOT IN ('.pSQL(implode(',', $productListExcludeActive)).')';
        }
    }

    $sql = 'SELECT COUNT(DISTINCT('._DB_PREFIX_.'product.id_product)) AS c
		FROM '._DB_PREFIX_.'product
		LEFT JOIN '._DB_PREFIX_.'manufacturer ON
		'._DB_PREFIX_.'manufacturer.id_manufacturer = '._DB_PREFIX_.'product.id_manufacturer
		'.$multistoreJoin.$category_table.$where_only_active;

    $product_total = Db::getInstance()->getRow($sql);

    if (empty($product_total['c'])) {
        return '<feeds><total>0</total></feeds>';
    }

    $parts = 1;

    if ($product_total['c'] > $limit) {
        $parts = ceil($product_total['c'] / $limit);
    }

    $pass_in_link = (!empty($use_password) && !empty($password)) ? '&password='.$password : '';

    $multistoreUrl = !empty($multistoreString) ? '&multistore='.$multistoreString : '';
    $link = $xmlFeedUrl->get('id='.$page.$pass_in_link.$multistoreUrl.'&part=');

    $xml = '<feeds>';
    $xml .= '<total>'.$parts.'</total>';

    for ($i = 1; $i <= $parts; ++$i) {
        $xml .= '<feed_'.$i.'><![CDATA['.$link.$i.']]></feed_'.$i.'>';
    }

    $xml .= '</feeds>';

    return $xml;
}

$xml = '';

if ($feed_type == 1) {
    if (empty($part) && !empty($permissions['split_feed']) && !empty($permissions['split_feed_limit'])) {
        $xml = create_split_xml_product(
            $permissions['only_enabled'],
            $permissions['split_feed_limit'],
            $id,
            $permissions['use_password'],
            $permissions['password'],
            $affiliate,
            $multistoreString,
            $permissions['categories'],
            $permissions['cat_list'],
            $filterCategoryType,
            $permissions,
            $settings,
            $productList,
            $productListStatus
        );
    } else {
        $productXmlApi = new ProductXmlApi();

        $xml = $productXmlApi->getFeed(
            $permissions,
            $id,
            $pref_s,
            $pref_e,
            $permissions['html_tags_status'],
            $permissions['extra_feed_row'],
            $permissions['one_branch'],
            $permissions['only_enabled'],
            $permissions['split_feed_limit'],
            $part,
            $permissions['categories'],
            $permissions['cat_list'],
            $multistoreString,
            $onlyInStock,
            $priceRange,
            $permissions['price_with_currency'],
            $mode,
            $allImages,
            $affiliate,
            $permissions['currency_id'],
            $permissions['feed_generation_time'],
            $permissions['feed_generation_time_name'],
            $permissions['split_by_combination'],
            $productList,
            $productListStatus,
            $shippingCountry,
            $filterDiscount,
            $filterCategoryType,
            $productSettingsPackageId,
            $settings,
            $permissions
        );
    }
} elseif ($feed_type == 2) {
    $categoryXmlApi = new CategoryXmlApi();

    $xml = $categoryXmlApi->getFeed(
        $id,
        $pref_s,
        $pref_e,
        $permissions['html_tags_status'],
        $permissions['extra_feed_row'],
        $permissions['one_branch'],
        $permissions['only_enabled'],
        $multistoreString
    );
} elseif ($feed_type == 3) {
    $orderXmlApi = new OrderXmlApi();
    $xml = $orderXmlApi->getFeed($permissions);
} elseif ($feed_type == 4) {
    $customerXmlApi = new CustomerXmlApi();
    $xml = $customerXmlApi->getFeed($permissions);
} elseif ($feed_type == 5) {
    $brandXmlApi = new BrandXmlApi();
    $xml = $brandXmlApi->getFeed($permissions, $protocol);
} elseif ($feed_type == 6) {
    $supplierXmlApi = new SupplierXmlApi();
    $xml = $supplierXmlApi->getFeed($permissions, $protocol);
}

if ($mode == 'tot') {
    $permissions['header_information'] .= '<created>'.date('Y-m-d').'</created>';
}

if ($mode == 'ep' || $mode == 'ro') {
    $permissions['header_information'] = '<yml_catalog date="'.date('Y-m-d H:i').'">'.$permissions['header_information'];
    $permissions['footer_information'] .= '</yml_catalog>';
}

$xml = $permissions['header_information'].$xml.$permissions['footer_information'];

if ($permissions['use_cache']) {
    if ($now > $cache_period) {
        if (empty($check_affiliate['affiliate_name'])) {
            $affiliate = false;
        }

        $create_name = '';

        if (empty($permissions['file_name'])) {
            $permissions['file_name'] = md5(md5(rand(99999, 99999999).'aKf5ad@d50gaq0sd'.date('Y-m-d H:i:s')));
            $create_name = 'file_name="'.$permissions['file_name'].'", ';
        }

        $file_url = _PS_ROOT_DIR_.'/modules/xmlfeeds/xml_files/'.$permissions['file_name'].'.xml';
        file_put_contents($file_url, $xml);

        if (file_exists($file_url)) {
            Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'blmod_xml_feeds_cache
				(`feed_id`, `feed_part`, `file_name`, `last_cache_time`, `affiliate_name`)
				VALUES
				("'.(int)$id.'", "'.(int)$part.'", "'.pSQL($permissions['file_name']).'", "'.pSQL($now).'", "")');
        }
    }
}

if ($useCron) {
    $file_url = _PS_ROOT_DIR_.'/modules/xmlfeeds/xml_files/feed_'.$id.'.xml';
    file_put_contents($file_url, '<?xml version="1.0" encoding="'.$permissions['encoding_text'].'"?>'.$xml);

    Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'blmod_xml_feeds SET `last_cron_date` = "'.pSQL(date('Y-m-d H:i:s')).'" WHERE id = "'.(int)$id.'"');

    AccessLog::save($id, 'end_cron', $sessionId);

    die('done');
}

function insert_statistics($feed_id = false, $affiliate = '')
{
    Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'blmod_xml_statistics WHERE `date` < "'.XmlFeedsTools::dateMinusDays(180).'"');

    Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'blmod_xml_statistics
		(`feed_id`, `affiliate_name`, `date`, `ip_address`)
		VALUES
		("'.(int)$feed_id.'", "'.pSQL(is_array($affiliate) ? implode(', ', $affiliate) : '').'", "'.pSQL(date('Y-m-d H:i:s')).'", "'.pSQL(get_ip()).'")');

    Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'blmod_xml_feeds SET total_views = total_views + 1 WHERE id = "'.(int)$feed_id.'"');
}

function get_ip()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    return $ip;
}

function getShopProtocol()
{
    if (method_exists('Tools', 'getShopProtocol')) {
        return Tools::getShopProtocol();
    }

    return (Configuration::get('PS_SSL_ENABLED') || (!empty($_SERVER['HTTPS'])
            && Tools::strtolower($_SERVER['HTTPS']) != 'off')) ? 'https://' : 'http://';
}

function whereType($type)
{
    if (!empty($type)) {
        return ' AND ';
    }

    return ' WHERE ';
}

if (!empty($permissions['last_modified_header'])) {
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
}

header('Content-type: text/xml;charset:'.$permissions['encoding_text']);

if (!empty($downloadAction)) {
    header('Content-Disposition:attachment;filename='.$feed_name.'_feed.xml');
}

AccessLog::save($id, 'end', $sessionId);

echo '<?xml version="1.0" encoding="'.$permissions['encoding_text'].'"?>';
echo $xml;
die;

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
    require_once(dirname(__FILE__).'/config/config.inc.php');
}

if (!class_exists('SkroutzAnalyticsXml', false)) {
    require_once(_PS_MODULE_DIR_.'/xmlfeeds/SkroutzAnalyticsXml.php');
}

$feedSettings = getSkroutzFeedSettings();

if (empty($feedSettings['skroutz_analytics_id'])) {
    die('Empty skroutz_analytics_id');
}

$skroutzAnalyticsXml = new SkroutzAnalyticsXml($feedSettings);

$productId = Tools::getValue('product_id');
$combinationId = Tools::getValue('combination_id');
$langId = Tools::getValue('lang_id', (int)Configuration::get('PS_LANG_DEFAULT'));

if (empty($productId)) {
    die('Empty URL param product_id');
}

if (empty($combinationId)) {
    echo '<div style="margin-bottom: 20px; color: #af0606;">Warning: Empty URL param combination_id</div>';
}

$product = new Product($productId, false);
$combinations = $product->getAttributesResume($langId, ' ', ', ');
$attributesGroups = AttributeGroupCore::getAttributesGroups($langId);

$groupName = '';

foreach ($attributesGroups as $a) {
    if ($a['id_attribute_group'] == $feedSettings['merge_attributes_parent']) {
        $groupName = $a['name'];
        break;
    }
}

echo 'Product: '.(!empty($product->name[$langId]) ? $product->name[$langId] : 'empty name').'<br>';
echo 'Product ID: '.$productId.'<br>';
echo 'Combination ID: '.$combinationId.'<br>';
echo 'Skroutz ID: '.$feedSettings['skroutz_analytics_id'].'<br>';
echo 'Merge by group: '.$groupName.'<br>';

foreach ($combinations as $c) {
    $skroutzCombinationID = $skroutzAnalyticsXml->getCombinationId($productId, $c['id_product_attribute']);

    echo '<div style="'.($combinationId == $c['id_product_attribute'] ? 'color: #f44336;' : '').'">';
    echo '<br>Combination ID: '.$c['id_product_attribute'].'<br>';
    echo 'Combination name: '.$c['attribute_designation'].'<br>';
    echo 'Skroutz combination ID: '.$skroutzCombinationID.'<br>';
    echo 'XML item ID: '.$productId.'-'.$skroutzCombinationID.'<br>';
    echo '</div>';
}

function getSkroutzFeedSettings()
{
    if (!class_exists('FeedMeta', false)) {
        require_once(_PS_MODULE_DIR_.'/xmlfeeds/FeedMeta.php');
    }

    $feeds = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'blmod_xml_feeds f
            WHERE f.feed_mode = "s"
            ORDER BY f.id DESC');

    if (empty($feeds)) {
        return [];
    }

    $feedMeta = new FeedMeta();

    foreach ($feeds as $f) {
        $meta = $feedMeta->getFeedMeta($f['id']);

        if (!empty($meta[$f['id']]['skroutz_analytics_id'])) {
            return $f+$meta[$f['id']];
        }
    }

    return [];
}

die('');

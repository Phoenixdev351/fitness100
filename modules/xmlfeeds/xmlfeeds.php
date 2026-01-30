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
    die('Not Allowed, Xmlfeeds');
}

class Xmlfeeds extends Module
{
    const CSS_VERSION = 'v26';

    public $tags_info = array();
    public $shopLang = false;
    public $checkedInput = false;
    public $moduleImgPath = false;
    public $googleCategories = array();
    public $googleCategoriesMap = array();
    public $rootFile = '';
    public $fullAdminUrl = '';
    public $contactUsUrl = 'https://addons.prestashop.com/en/contact-us?id_product=5732';
    public $recurseDone = [];
    private $htmlCategory = '';
    private $templates = '';
    private $moduleRootUrl = '';
    private $feedSettings;
    private $feedSettingsAdmin = [];

    /**
     * @var NotificationXml
     */
    private $notification;

    public function __construct()
    {
        $this->name = 'xmlfeeds';
        $this->full_name = $this->name.'_pro';
        $this->tab = 'export';
        $this->author = 'Bl Modules';
        $this->version = '3.6.1';
        $this->module_key = '3aa147ba51c7d9571b1838f24cfd131a';
        $this->moduleImgPath = '../modules/'.$this->name.'/views/img/';

        parent::__construct();

        $this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('XML feeds Pro');
        $this->description = $this->l('Export from Prestashop to XML');
        $this->confirmUninstall = $this->l('Are you sure you want to delete the module?');
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        if (!class_exists('XmlFeedInstall', false)) {
            include_once(dirname(__FILE__).'/XmlFeedInstall.php');
        }

        if (!class_exists('OrderSettings', false)) {
            include_once(dirname(__FILE__).'/OrderSettings.php');
        }

        $xmlFeedInstall = new XmlFeedInstall();

        if (!$xmlFeedInstall->installModuleSql()) {
            return false;
        }

        $this->registerHook('actionAdminControllerSetMedia');
        $this->registerHook('orderConfirmation');
        $this->registerHook('header');

        @copy('../modules/'.$this->name.'/root_file/xml_feeds.php', '../xml_feeds.php');

        Configuration::updateValue('BLMOD_XML_FEED_META', '');

        return true;
    }

    public function uninstall()
    {
        Db::getInstance()->Execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'blmod_xml_block');
        Db::getInstance()->Execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'blmod_xml_feeds');
        Db::getInstance()->Execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'blmod_xml_fields');
        Db::getInstance()->Execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'blmod_xml_feeds_cache');
        Db::getInstance()->Execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'blmod_xml_statistics');
        Db::getInstance()->Execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'blmod_xml_affiliate_price');
        Db::getInstance()->Execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'blmod_xml_g_cat');
        Db::getInstance()->Execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'blmod_xml_product_list');
        Db::getInstance()->Execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'blmod_xml_product_list_product');
        Db::getInstance()->Execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'blmod_xml_product_settings_package');
        Db::getInstance()->Execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'blmod_xml_product_settings');
        Db::getInstance()->Execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'blmod_xml_access_log');
        Db::getInstance()->Execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'blmod_xml_category_map');
        Db::getInstance()->Execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'blmod_xml_feed_search_query');
        Db::getInstance()->Execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'blmod_xml_product_property_map');
        Db::getInstance()->Execute('DROP TABLE IF EXISTS '._DB_PREFIX_.'blmod_xml_product_property_map_value');

        Configuration::deleteByName('BLMOD_XML_FEED_META');

        return parent::uninstall();
    }

    public function hookActionAdminControllerSetMedia()
    {
        $configure = Tools::getValue('configure');

        if ($configure != 'xmlfeeds') {
            return false;
        }

        $this->context->controller->addCSS($this->_path.'views/css/style_admin_'.self::CSS_VERSION.'.css', 'all');
        $this->context->controller->addCSS($this->_path.'views/css/xml_feeds_'.self::CSS_VERSION.'.css', 'all');
        $this->context->controller->addJS($this->_path.'views/js/xml_feeds_'.self::CSS_VERSION.'.js', 'all');
        $this->context->controller->addJS($this->_path.'views/js/search_'.self::CSS_VERSION.'.js', 'all');
        $this->context->controller->addJqueryUI('ui.sortable');

        if (_PS_VERSION_ < 1.7) {
            $this->context->controller->addCSS($this->_path.'views/css/ps16.css', 'all');
        }

        if (_PS_VERSION_ < 1.6) {
            $this->context->controller->addCSS($this->_path.'views/css/style_admin_ps_old.css', 'all');
        }

        return true;
    }

    public function loadModule()
    {
        include_once(dirname(__FILE__).'/googlecategory.php');
        include_once(dirname(__FILE__).'/ProductListAdmin.php');
        include_once(dirname(__FILE__).'/ProductSettingsAdmin.php');
        include_once(dirname(__FILE__).'/ProductSettings.php');
        include_once(dirname(__FILE__).'/XmlFeedsTools.php');
        include_once(dirname(__FILE__).'/FeedType.php');
        include_once(dirname(__FILE__).'/PriceFormat.php');
        include_once(dirname(__FILE__).'/AccessLogAdmin.php');
        include_once(dirname(__FILE__).'/FeedSettingsAdmin.php');
        include_once(dirname(__FILE__).'/OrderSettings.php');
        include_once(dirname(__FILE__).'/OrderFieldsAdmin.php');
        include_once(dirname(__FILE__).'/NotificationXml.php');
        include_once(dirname(__FILE__).'/XmlFeedUrl.php');
        include_once(dirname(__FILE__).'/XmlExportSettings.php');
        include_once(dirname(__FILE__).'/CategoryMap.php');
        include_once(dirname(__FILE__).'/ProductPropertyMap.php');
        include_once(dirname(__FILE__).'/ProductTitleEditor.php');
        include_once(dirname(__FILE__).'/FeedMeta.php');
        include_once(dirname(__FILE__).'/SkroutzAnalyticsXml.php');

        if (!class_exists('XmlFeedInstall', false)) {
            include_once(dirname(__FILE__).'/XmlFeedInstall.php');
        }

        $this->notification = new NotificationXml();
    }

    public function catchSaveAction()
    {
        $productListAdmin = new ProductListAdmin();
        $productSettingsAdmin = new ProductSettingsAdmin();
        $categoryMap = new CategoryMap();
        $productPropertyMap = new ProductPropertyMap();

        $addProductList = Tools::getValue('add_product_list');
        $updateProductList = Tools::getValue('update_product_list');
        $deleteProductList = Tools::getValue('delete_product_list');
        $updateProductSettings = Tools::getValue('update_product_settings');
        $deleteProductSettingsPackage = Tools::getValue('delete_product_setting_package');
        $categoryMapName = Tools::getValue('category_map_name');
        $categoryMapDelete = Tools::getValue('category_map_delete');
        $createProductPropertyMap = Tools::getValue('create_product_property_map');
        $updateProductPropertyMap = Tools::getValue('update_product_property_map');
        $deleteProductPropertyMap = Tools::getValue('product_property_map_delete');
        $updateProductListCategory = Tools::getValue('product_list_category_id');

        $res = false;
        $actionName = $this->l('Updated successfully');

        if (!empty($addProductList)) {
            $res = $productListAdmin->insertNewProductList();
        }

        if (!empty($updateProductList)) {
            $res = $productListAdmin->updateProductList();
        }

        if (!empty($deleteProductList)) {
            $res = $productListAdmin->deleteProductList();
            $actionName = $this->l('Deleted successfully');
        }

        if (!empty($updateProductListCategory)) {
            $res = $productListAdmin->updateProductListByCategory();
        }

        if (!empty($updateProductSettings)) {
            $res = $productSettingsAdmin->save();
        }

        if (!empty($deleteProductSettingsPackage)) {
            $res = $productSettingsAdmin->deleteProductSettingsPackage();
            $actionName = $this->l('Deleted successfully');
        }

        if (!empty($categoryMapName)) {
            $res = $categoryMap->saveMapFile();
            $actionName = $this->l('Category map file created successfully');
            $errors = $categoryMap->getErrors();

            if (!empty($errors)) {
                $this->notification->addWarn(implode('<br>', $errors));
            }
        }

        if (!empty($categoryMapDelete)) {
            $res = $categoryMap->delete($categoryMapDelete);
            $actionName = $this->l('Category map file deleted successfully');
        }

        if (!empty($createProductPropertyMap)) {
            $res = $productPropertyMap->createMap();
            $actionName = $this->l('Map created successfully');
        }

        if (!empty($updateProductPropertyMap)) {
            $res = $productPropertyMap->updateMapValues();
            $actionName = $this->l('Map updated successfully');
        }

        if (!empty($deleteProductPropertyMap)) {
            $res = $productPropertyMap->deleteMap($deleteProductPropertyMap);
            $actionName = $this->l('Map deleted successfully');
        }

        if ($res) {
            $this->notification->addConf($actionName);
        }
    }

    public function getContent()
    {
        $this->loadModule();

        $this->shopLang = (int)Configuration::get('PS_LANG_DEFAULT');
        $tab = Tools::getValue('tab');
        $full_address_no_t = $this->getShopProtocol().$_SERVER['HTTP_HOST'].__PS_BASE_URI__.Tools::substr($_SERVER['PHP_SELF'], Tools::strlen(__PS_BASE_URI__)).'?tab='.$tab.'&configure='.Tools::getValue('configure');

        if (_PS_VERSION_ > 1.6) {
            $full_address_no_t = $this->context->link->getAdminLink('', false).'index.php?tab='.$tab.'&configure='.Tools::getValue('configure');
        }

        $token = '&token='.Tools::getValue('token');
        $this->fullAdminUrl = $full_address_no_t.$token;
        $this->rootFile = $this->getShopProtocol().$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/'.$this->name.'/api/xml.php';
        $this->moduleRootUrl = $this->getShopProtocol().$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/'.$this->name.'/';
        $xmlExportSettings = new XmlExportSettings();
        $xmlExportSettings->isExportAction($this->version, $this->full_name);

        $duplicateId = Tools::getValue('duplicate');

        if (!empty($duplicateId)) {
            $this->duplicateFeed($duplicateId, $full_address_no_t, $token);
        }

        $this->catchSaveAction();
        $this->cleanCache();

        $POST = array();
        $POST['update_feeds_s'] = Tools::getValue('update_feeds_s');
        $POST['settings_cat'] = Tools::getValue('settings_cat');
        $POST['settings_prod'] = Tools::getValue('settings_prod');
        $POST['clear_cache'] = Tools::getValue('clear_cache');
        $POST['feeds_name'] = Tools::getValue('feeds_name');
        $POST['update_ga_cat'] = Tools::getValue('update_ga_cat');
        $POST['google_cat_map'] = Tools::getValue('google_cat_map');

        $is_product_feed = Tools::getValue('is_product_feed');
        $is_category_feed = Tools::getValue('is_category_feed');
        $isOrderFeed = Tools::getValue('is_order_feed');

        if (!empty($POST['clear_cache'])) {
            $this->deleteCache($POST['feeds_name']);
            $this->notification->addConf($this->l('Cache cleared successfully'));
        }

        $isValidSettingsUpdate = $this->validateFeedSettingsUpdate($POST);

        if ($isValidSettingsUpdate) {
            $POST['name'] = Tools::getValue('name', $this->l('Products feed'));
            $POST['status'] = Tools::getValue('status', 0);
            $POST['use_cache'] = Tools::getValue('use_cache', 0);
            $POST['cache_time'] = Tools::getValue('cache_time', 0);
            $POST['protect_by_ip'] = Tools::getValue('protect_by_ip', 0);
            $POST['use_password'] = Tools::getValue('use_password', 0);
            $POST['password'] = Tools::getValue('password');
            $POST['cdata_status'] = Tools::getValue('cdata_status', 0);
            $POST['html_tags_status'] = Tools::getValue('html_tags_status', 0);
            $POST['one_branch'] = Tools::getValue('one_branch', 0);
            $POST['header_information'] = Tools::getValue('header_information');
            $POST['footer_information'] = Tools::getValue('footer_information');
            $POST['extra_feed_row'] = Tools::getValue('extra_feed_row');
            $POST['only_enabled'] = Tools::getValue('only_enabled', 0);
            $POST['split_feed'] = Tools::getValue('split_feed', 0);
            $POST['split_feed_limit'] = Tools::getValue('split_feed_limit', 0);
            $POST['categories'] = Tools::getValue('categories', 0);
            $POST['use_cron'] = Tools::getValue('use_cron', 0);
            $POST['only_in_stock'] = Tools::getValue('only_in_stock', 0);
            $POST['attribute_as_product'] = Tools::getValue('attribute_as_product', 0);
            $POST['manufacturers'] = Tools::getValue('manufacturers', 0);
            $POST['suppliers'] = Tools::getValue('suppliers', 0);
            $POST['price_from'] = Tools::getValue('price_from', '');
            $POST['price_to'] = Tools::getValue('price_to', '');
            $POST['price_with_currency'] = Tools::getValue('price_with_currency', 0);
            $POST['all_images'] = Tools::getValue('all_images', 0);
            $POST['currency_id'] = Tools::getValue('currency_id', 0);
            $POST['feed_generation_time'] = Tools::getValue('feed_generation_time', 0);
            $POST['feed_generation_time_name'] = Tools::getValue('feed_generation_time_name', '');
            $POST['split_by_combination'] = Tools::getValue('split_by_combination', '');
            $POST['product_list'] = Tools::getValue('product_list', '');
            $POST['product_list_status'] = Tools::getValue('product_list_status', '');
            $POST['shipping_country'] = Tools::getValue('shipping_country', 0);
            $POST['filter_discount'] = Tools::getValue('filter_discount', 0);
            $POST['filter_category_type'] = Tools::getValue('filter_category_type', 0);
            $POST['product_settings_package_id'] = Tools::getValue('product_settings_package_id', 0);
            $POST['filter_qty_status'] = Tools::getValue('filter_qty_status', 0);
            $POST['filter_qty_type'] = Tools::getValue('filter_qty_type', 0);
            $POST['filter_qty_value'] = Tools::getValue('filter_qty_value', 0);
            $POST['price_format_id'] = Tools::getValue('price_format_id', 0);
            $POST['in_stock_text'] = Tools::getValue('in_stock_text', '');
            $POST['on_demand_stock_text'] = Tools::getValue('on_demand_stock_text', '');
            $POST['out_of_stock_text'] = Tools::getValue('out_of_stock_text', '');
            $POST['filter_category_without_type'] = Tools::getValue('filter_category_without_type', 0);
            $POST['categories_without'] = Tools::getValue('categories_without', 0);
            $POST['filter_image'] = Tools::getValue('filter_image', 0);
            $POST['only_available_for_order'] = Tools::getValue('only_available_for_order', 0);

            $POST['price_range'] = $POST['price_from'].';'.$POST['price_to'];
            $POST['price_range'] = str_replace(',', '.', $POST['price_range']);

            $cat_list = false;
            $manufacturerList = false;
            $supplierList = false;
            $productList = false;
            $catWithoutList = false;

            $POST['categoryBox'] = Tools::getValue('categoryBox');

            if (!empty($POST['categoryBox'])) {
                $cat_list = implode(',', $POST['categoryBox']);
            }

            $POST['categoryWithoutBox'] = Tools::getValue('categoryWithoutBox');

            if (!empty($POST['categoryWithoutBox'])) {
                $catWithoutList = implode(',', $POST['categoryWithoutBox']);
            }

            $POST['manufacturer'] = Tools::getValue('manufacturer');

            if (!empty($POST['manufacturer'])) {
                $manufacturerList = implode(',', $POST['manufacturer']);
            }

            $POST['supplier'] = Tools::getValue('supplier');

            if (!empty($POST['supplier'])) {
                $supplierList = implode(',', $POST['supplier']);
            }

            if (!empty($POST['product_list'])) {
                $productList = implode(',', $POST['product_list']);
            }

            $this->updateFeedsS(
                $POST['name'],
                $POST['status'],
                $POST['use_cache'],
                $POST['cache_time'],
                $POST['use_password'],
                $POST['password'],
                $POST['feeds_name'],
                $POST['cdata_status'],
                $POST['html_tags_status'],
                $POST['one_branch'],
                $POST['header_information'],
                $POST['footer_information'],
                $POST['extra_feed_row'],
                $POST['only_enabled'],
                $POST['split_feed'],
                $POST['split_feed_limit'],
                $cat_list,
                $POST['categories'],
                $POST['use_cron'],
                $POST['only_in_stock'],
                $POST['attribute_as_product'],
                $POST['manufacturers'],
                $manufacturerList,
                $POST['suppliers'],
                $supplierList,
                $POST['price_range'],
                $POST['price_with_currency'],
                $POST['all_images'],
                $POST['currency_id'],
                $POST['feed_generation_time'],
                $POST['feed_generation_time_name'],
                $POST['split_by_combination'],
                $productList,
                $POST['product_list_status'],
                $POST['shipping_country'],
                $POST['filter_discount'],
                $POST['filter_category_type'],
                $POST['product_settings_package_id'],
                $POST['filter_qty_status'],
                $POST['filter_qty_type'],
                $POST['filter_qty_value'],
                $POST['price_format_id'],
                $POST,
                $catWithoutList,
                $POST['filter_image']
            );
        }

        $delete_feed = Tools::getValue('delete_feed');

        if (!empty($delete_feed)) {
            $this->deleteFeed($delete_feed);
        }

        if ((!empty($POST['update_feeds_s']) || !empty($POST['settings_cat'])) && $is_category_feed) {
            $this->updateFields(2);
        }

        if ((!empty($POST['update_feeds_s']) || !empty($POST['settings_prod'])) && $is_product_feed) {
            $this->updateFields(1);
        }

        if ((!empty($POST['update_feeds_s']) || !empty($POST['settings_cat'])) && $isOrderFeed) {
            $this->updateFields(3);
        }

        if (!empty($POST['update_ga_cat'])) {
            $this->updateGoogleCat($POST['google_cat_map']);
        }

        $currentPage = $this->pageStructure($full_address_no_t, $token);

        $this->smarty->assign([
            '_PS_VERSION_' => _PS_VERSION_,
            'CSS_VERSION' => self::CSS_VERSION,
            'moduleImgPath' => $this->moduleImgPath,
            'displayName' => $this->displayName,
            'version' => $this->version,
            'full_address_no_t' => $full_address_no_t,
            'token' => $token,
            'contentHtml' => $this->templates,
            'currentPage' => $currentPage,
            'notifications' => $this->notification->getMessages(),
        ]);

        return $this->displaySmarty('views/templates/admin/body.tpl');
    }

    public function installDefaultProductsValues($feedId = false, $feedMode = 'c')
    {
        $xmlFeedInstall = new XmlFeedInstall();

        if (empty($feedId)) {
            return false;
        }

        return $xmlFeedInstall->installDefaultFeedProductSettings($feedId, $feedMode);
    }

    public function installDefaultCategoriesValues($feedId = false)
    {
        $xmlFeedInstall = new XmlFeedInstall();

        if (empty($feedId)) {
            return false;
        }

        return $xmlFeedInstall->installDefaultFeedCategorySettings($feedId);
    }

    public function duplicateFeed($feedId = 0, $full_address_no_t = '', $token = '')
    {
        $FeedMeta = new FeedMeta();
        $feedId = (int)$feedId;

        if (empty($feedId)) {
            return false;
        }


        $xmlFeed = Db::getInstance()->getRow('
			SELECT f.*
			FROM '._DB_PREFIX_.'blmod_xml_feeds f
			WHERE f.id = "'.(int)$feedId.'"
		');

        if (empty($xmlFeed)) {
            return false;
        }

        unset($xmlFeed['id']);
        $xmlFeed['name'] = $xmlFeed['name'].' duplicate';

        $result = Db::getInstance()->insert('blmod_xml_feeds', $xmlFeed);

        $newFeedId = (int)Db::getInstance()->Insert_ID();

        if (empty($result) || empty($newFeedId)) {
            return false;
        }

        $xmlBlocks = Db::getInstance()->ExecuteS('
			SELECT b.*
			FROM '._DB_PREFIX_.'blmod_xml_block b
			WHERE b.category = "'.(int)$feedId.'"
		');

        foreach ($xmlBlocks as $b) {
            Db::getInstance()->insert(
                'blmod_xml_block',
                array(
                    'name' => pSQL($b['name']),
                    'value' => pSQL($b['value']),
                    'category' => (int)$newFeedId,
                )
            );
        }

        $xmlFields = Db::getInstance()->ExecuteS('
			SELECT f.*
			FROM '._DB_PREFIX_.'blmod_xml_fields f
			WHERE f.category = "'.(int)$feedId.'"
		');

        foreach ($xmlFields as $f) {
            Db::getInstance()->insert(
                'blmod_xml_fields',
                array(
                    'name' => pSQL($f['name']),
                    'status' => pSQL($f['status']),
                    'title_xml' => pSQL($f['title_xml']),
                    'table' => pSQL($f['table']),
                    'category' => (int)$newFeedId,
                )
            );
        }

        $FeedMeta->duplicateValues($feedId, $newFeedId);

        Tools::redirectAdmin($full_address_no_t.'&display_dp_conf=1&page='.$newFeedId.$token);
        die;
    }

    public function getBiggestImage()
    {
        $images = Db::getInstance()->getRow('
			SELECT `name`
			FROM '._DB_PREFIX_.'image_type
			WHERE `products` = "1"
			ORDER BY `width` DESC, `height` DESC
		');

        if (empty($images['name'])) {
            return false;
        }

        return $images['name'];
    }

    public function cleanCache()
    {
        $cache = Db::getInstance()->ExecuteS('
			SELECT f.cache_time, c.feed_id, c.feed_part, c.file_name, c.last_cache_time
			FROM '._DB_PREFIX_.'blmod_xml_feeds f
			LEFT JOIN '._DB_PREFIX_.'blmod_xml_feeds_cache c ON
			f.id = c.feed_id
			WHERE c.feed_id > 0
		');

        if (empty($cache)) {
            return true;
        }

        $now = date('Y-m-d h:i:s');

        foreach ($cache as $c) {
            $cache_period = date('Y-m-d h:i:s', strtotime($c['last_cache_time'].'+'.$c['cache_time'].' minutes'));

            if ($now > $cache_period && !empty($c['file_name'])) {
                $file_url = 'xml_files/'.$c['file_name'].'.xml';

                @unlink('../modules/xmlfeeds/xml_files/'.$c['file_name'].'.xml');

                if (!file_exists($file_url)) {
                    Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'blmod_xml_feeds_cache WHERE feed_id = "'.(int)$c['feed_id'].'" AND feed_part = "'.(int)$c['feed_part'].'"');
                }
            }
        }

        return true;
    }

    public function status($status = false, $disabled = false)
    {
        if ($disabled) {
            $disabled = 'disabled';
        } else {
            $disabled = '';
        }

        if (isset($status) && $status == 1) {
            $status_text = ' value = "1" checked '.$disabled.' /> <img src="'.$this->moduleImgPath.'enabled.gif" alt = "'.$this->l('Enabled').'" />'.$this->l('Enabled');
        } else {
            $status_text = ' value = "1" '.$disabled.'/> <img src="'.$this->moduleImgPath.'disabled.gif" alt = "'.$this->l('Disabled').'" />'.$this->l('Disabled');
        }

        return $status_text;
    }

    public function pageStructure($full_address_no_t = false, $token = false)
    {
        $page = Tools::getValue('page');
        $add_feed = Tools::getValue('add_feed');
        $statistics = Tools::getValue('statistics');
        $add_affiliate_price = Tools::getValue('add_affiliate_price');
        $googleCatAssign = Tools::getValue('google_cat_assign');
        $attributesMapping = Tools::getValue('attributes_mapping');
        $featuresMapping = Tools::getValue('features_mapping');
        $productListPage = Tools::getValue('product_list_page');
        $productSettingsPage = Tools::getValue('product_settings_page');
        $about_page = Tools::getValue('about_page');
        $accessLogPage = Tools::getValue('access_log');
        $currentPage = array('type' => '', 'id' => 0,);

        if (empty($page) && empty($statistics) && empty($add_affiliate_price) && empty($add_feed)) {
            $page = $this->checkGetDefaultFeed();
        }

        $this->getMenuBox();

        if (!empty($accessLogPage)) {
            $accessLog = new AccessLogAdmin();
            $accessLog->setPageId($page);
            $currentPage['type'] = 'edit';
            $currentPage['id'] = $page;

            $this->smarty->assign($accessLog->getContent());
            $this->templates .= $this->displaySmarty('views/templates/admin/page/accessLog.tpl');
        } elseif (!empty($add_feed)) {
            $this->addNewFeed($add_feed, $full_address_no_t, $token);
            $currentPage['type'] = 'add_feed';
            $currentPage['id'] = $add_feed;
        } elseif (!empty($statistics)) {
            $this->statisticsOne($statistics, $full_address_no_t, $token);
            $currentPage['type'] = 'statistics';
        } elseif (!empty($add_affiliate_price)) {
            $this->addAffiliatePrice($full_address_no_t, $token);
            $currentPage['type'] = 'add_affiliate_price';
        } elseif (!empty($googleCatAssign)) {
            $this->assignGoogleCategoriesPage();
            $currentPage['type'] = 'google_cat_assign';
        } elseif (!empty($attributesMapping)) {
            $this->getAttributesMappingPage();
            $currentPage['type'] = 'attributes_mapping';
        } elseif (!empty($featuresMapping)) {
            $this->getFeaturesMappingPage();
            $currentPage['type'] = 'features_mapping';
        } elseif (!empty($productListPage)) {
            $productList = new ProductListAdmin();
            $currentPage['type'] = 'product_list_page';
            $productListId = Tools::getValue('product_list_id');
            $productListCategoryId = Tools::getValue('product_list_category_id');
            $productListByCategory = array();

            if (!empty($productListCategoryId)) {
                $productListByCategory = $productList->getProductsByCategoryId($productListCategoryId, $productListId, $this->shopLang);
            }

            $this->smarty->assign([
                'postUrl' => $_SERVER['REQUEST_URI'],
                'moduleImgPath' => $this->moduleImgPath,
                'token' => $token,
                'full_address_no_t' => $full_address_no_t,
                'productListGroup' => $productList->getProductList(),
                'productListId' => $productListId,
                'productList' => $productList->getProductListProducts($productListId),
                'productListCategories' => Category::getCategories($this->shopLang, false, false, '', 'ORDER BY cl.`name` ASC, category_shop.`position` ASC'),
                'totalProductsInCategory' => $productList->getTotalProductsInCategory($productListId),
                'productListByCategoryId' => $productListByCategory,
                'productListCategoryId' => $productListCategoryId,
            ]);
            $this->templates .= $this->displaySmarty('views/templates/admin/page/productList.tpl');
        } elseif (!empty($productSettingsPage)) {
            $currentPage['type'] = 'product_settings_page';
        } elseif (!empty($about_page)) {
            $this->aboutPage();
            $currentPage['type'] = 'about_page';
        } else {
            $this->feedsSettings($page);
            $currentPage['type'] = 'edit';
            $currentPage['id'] = $page;
        }

        return $currentPage;
    }

    public function aboutPage()
    {
        $xmlFeedInstall = new XmlFeedInstall();
        $isDbUpgrade = Tools::getValue('db_upgrade');

        if (!empty($isDbUpgrade)) {
            $xmlFeedInstall->runDatabaseUpgrade();

            $this->registerHook('actionAdminControllerSetMedia');
            $this->registerHook('orderConfirmation');
            $this->registerHook('header');

            $this->notification->addConf($this->l('Module database upgrade completed'));
        }

        $this->smarty->assign([
            'name' => $this->name,
            'version' => $this->version,
            'moduleImgPath' => $this->moduleImgPath,
            'databaseUpgradeUrl' => $this->getDatabaseUpgradeUrl(),
            'exportSettingsUrl' => $this->getExportSettingsUrl(),
            'contactUsUrl' => $this->contactUsUrl,
            'manualPdfUrl' => $this->getShopProtocol().$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/'.$this->name.'/readme_en.pdf'
        ]);

        $this->templates .= $this->displaySmarty('views/templates/admin/page/about.tpl');
    }

    public function getDatabaseUpgradeUrl()
    {
        return $this->fullAdminUrl.'&about_page=1&db_upgrade=1';
    }

    public function getExportSettingsUrl()
    {
        return $this->fullAdminUrl.'&export_settings=1';
    }

    public function checkGetDefaultFeed()
    {
        $feed = Db::getInstance()->getRow('
			SELECT `id`
			FROM '._DB_PREFIX_.'blmod_xml_feeds
			ORDER BY `id` DESC
		');

        if (!empty($feed['id'])) {
            return $feed['id'];
        }

        return false;
    }

    public function getMenuBox()
    {
        $feeds = Db::getInstance()->ExecuteS('
			SELECT `id`, `name`, `feed_type`
			FROM '._DB_PREFIX_.'blmod_xml_feeds
			ORDER BY `id` ASC
		');

        $products = array();
        $categories = array();
        $orders = array();

        if (!empty($feeds)) {
            foreach ($feeds as $f) {
                if ($f['feed_type'] == 1) {
                    $products[] = $f;
                } elseif ($f['feed_type'] == 2) {
                    $categories[] = $f;
                } elseif ($f['feed_type'] == 3) {
                    $orders[] = $f;
                }
            }
        }

        $this->smarty->assign([
            'products' => $products,
            'categories' => $categories,
            'orders' => $orders,
        ]);
    }

    public function addAffiliatePrice($full_address_no_t, $token)
    {
        $xmlFeedUrl = new XmlFeedUrl();
        $POST = array();

        $delete_affiliate_price = Tools::getValue('delete_affiliate_price');

        if (!empty($delete_affiliate_price)) {
            $get_affiliate_name = Db::getInstance()->getRow('SELECT `affiliate_name` FROM '._DB_PREFIX_.'blmod_xml_affiliate_price WHERE affiliate_id = "'.pSQL($delete_affiliate_price).'"');

            if (!empty($get_affiliate_name['affiliate_name'])) {
                $get_affiliate_info = Db::getInstance()->ExecuteS('SELECT `file_name` FROM '._DB_PREFIX_.'blmod_xml_feeds_cache WHERE affiliate_name = "'.pSQL($get_affiliate_name['affiliate_name']).'"');
            }

            if (Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'blmod_xml_affiliate_price WHERE affiliate_id = "'.pSQL($delete_affiliate_price).'"')) {
                if (!empty($get_affiliate_info)) {
                    Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'blmod_xml_feeds_cache WHERE affiliate_name = "'.pSQL($get_affiliate_name['affiliate_name']).'"');

                    foreach ($get_affiliate_info as $c) {
                        @unlink('../modules/xmlfeeds/xml_files/'.$c['file_name'].'.xml');
                    }
                }
            }

            $this->notification->addConf('Deleted successfully');
        }

        $POST['add_affiliate_price'] = Tools::getValue('add_affiliate_price');
        $isAddAffiliateAction = Tools::getValue('add_affiliate_action');

        if (!empty($POST['add_affiliate_price'])) {
            $POST['name'] = pSQL(Tools::getValue('xml_name'));
            $POST['price'] = pSQL(Tools::getValue('price'));
            $POST['xml_name'] = pSQL(Tools::getValue('xml_name'));

            if (empty($POST['name']) || empty($POST['price']) || empty($POST['xml_name'])) {
                if (!empty($isAddAffiliateAction)) {
                    $this->notification->addWarn($this->l('All fields are required'));
                }
            } else {
                $find_price = strpos(' '.$POST['price'], 'price');

                if (empty($find_price)) {
                    $this->notification->addWarn($this->l('Please insert the price constant. It will be replaced by the price of the product/combination'));
                } else {
                    Db::getInstance()->Execute('
						INSERT INTO '._DB_PREFIX_.'blmod_xml_affiliate_price
						(`affiliate_name`, `affiliate_formula`, `xml_name`)
						VALUE
						("'.pSQL($this->onSpecial($POST['name'])).'", "'.pSQL($POST['price']).'", "'.pSQL($this->onSpecial($POST['xml_name'])).'")
					');

                    $this->deleteCache(false, true);
                    $this->notification->addConf('Created successfully');
                }
            }
        }

        $prices = Db::getInstance()->ExecuteS('
			SELECT `affiliate_id`, `affiliate_name`, `affiliate_formula`, `xml_name`
			FROM '._DB_PREFIX_.'blmod_xml_affiliate_price
			ORDER BY affiliate_name ASC
		');

        $this->smarty->assign([
            'postUrl' => $_SERVER['REQUEST_URI'],
            'moduleImgPath' => $this->moduleImgPath,
            'prices' => $prices,
            'token' => $token,
            'full_address_no_t' => $full_address_no_t,
            'rootFile' => $xmlFeedUrl->get(''),
        ]);

        $this->templates .= $this->displaySmarty('views/templates/admin/page/addAffiliatePrice.tpl');
    }

    public function assignGoogleCategoriesPage()
    {
        $categoryMap = new CategoryMap();

        $type = Tools::getValue('category_type');
        $list = array();
        $type = !empty($type) ? $type : 4;
        $fileName = $categoryMap->getFileNameById($type);

        $googleCategory = new GoogleCategoryBlMod($fileName);
        $this->googleCategories = $googleCategory->getList();
        $this->setGoogleCatMap($type);

        if (!empty($this->googleCategories)) {
            foreach ($this->googleCategories as $c) {
                $list[] = '"' . $c . '"';
            }
        }

        $this->smarty->assign([
            'requestUri' => $_SERVER['REQUEST_URI'],
            'categoriesTree' => $this->categoriesTree(false, true),
            'type' => $type,
            'categoriesList' => implode(', ', $list),
            'list' => $categoryMap->getList(),
            'instructionUlr' => $this->moduleRootUrl.'tools/my_marketplace_name_En.txt',
            'fullAdminUrl' => $this->fullAdminUrl,
            'mapFileUrl' => $this->moduleRootUrl.'ga_categories/'.$fileName,
        ]);

        $this->templates .= $this->displaySmarty('views/templates/admin/page/addCategoryMapping.tpl');
    }

    public function getAttributesMappingPage()
    {
        $productPropertyMap = new ProductPropertyMap();
        $attributesGroups = AttributeGroup::getAttributesGroups($this->shopLang);
        $productProperties = array();
        $mapId = (int)Tools::getValue('map_id');
        $valuesByGroup = array();

        if (!empty($mapId)) {
            $valuesByGroup = $productPropertyMap->getMapValuesWithKey($mapId);
        }

        foreach ($attributesGroups as $g) {
            $productProperties[$g['id_attribute_group']] = array();
            $productProperties[$g['id_attribute_group']]['id'] = $g['id_attribute_group'];
            $productProperties[$g['id_attribute_group']]['name'] = $g['name'];
            $productProperties[$g['id_attribute_group']]['properties'] = array();

            $attributes = AttributeGroup::getAttributes($this->shopLang, $g['id_attribute_group']);

            foreach ($attributes as $a) {
                $productProperties[$g['id_attribute_group']]['properties'][] = array(
                    'id' => $a['id_attribute'],
                    'name' => $a['name'],
                    'value' => isset($valuesByGroup[$g['id_attribute_group'].'-'.$a['id_attribute']]) ? $valuesByGroup[$g['id_attribute_group'].'-'.$a['id_attribute']] : '',
                );
            }
        }

        $this->smarty->assign([
            'fullAdminUrl' => $this->fullAdminUrl,
            'requestUri' => $_SERVER['REQUEST_URI'],
            'typeId' => ProductPropertyMap::TYPE_ATTRIBUTE,
            'productProperties' => $productProperties,
            'mapList' => $productPropertyMap->getMaps(ProductPropertyMap::TYPE_ATTRIBUTE),
            'mapId' => $mapId,
            'typeName' => 'Attributes',
            'typeUrl' => 'attributes_mapping',
        ]);

        $this->templates .= $this->displaySmarty('views/templates/admin/page/productPropertyMap.tpl');
    }

    public function getFeaturesMappingPage()
    {
        $productPropertyMap = new ProductPropertyMap();
        $featuresGroups = Feature::getFeatures($this->shopLang);
        $productProperties = array();
        $mapId = (int)Tools::getValue('map_id');
        $valuesByGroup = array();

        if (!empty($mapId)) {
            $valuesByGroup = $productPropertyMap->getMapValuesWithKey($mapId);
        }

        foreach ($featuresGroups as $g) {
            $productProperties[$g['id_feature']] = array();
            $productProperties[$g['id_feature']]['id'] = $g['id_feature'];
            $productProperties[$g['id_feature']]['name'] = $g['name'];
            $productProperties[$g['id_feature']]['properties'] = array();

            $features = FeatureValue::getFeatureValuesWithLang($this->shopLang, $g['id_feature']);

            foreach ($features as $a) {
                $productProperties[$g['id_feature']]['properties'][] = array(
                    'id' => $a['id_feature_value'],
                    'name' => $a['value'],
                    'value' => isset($valuesByGroup[$g['id_feature'].'-'.$a['id_feature_value']]) ? $valuesByGroup[$g['id_feature'].'-'.$a['id_feature_value']] : '',
                );
            }
        }

        $this->smarty->assign([
            'fullAdminUrl' => $this->fullAdminUrl,
            'requestUri' => $_SERVER['REQUEST_URI'],
            'typeId' => ProductPropertyMap::TYPE_FEATURE,
            'productProperties' => $productProperties,
            'mapList' => $productPropertyMap->getMaps(ProductPropertyMap::TYPE_FEATURE),
            'mapId' => $mapId,
            'typeName' => 'Features',
            'typeUrl' => 'features_mapping',
        ]);

        $this->templates .= $this->displaySmarty('views/templates/admin/page/productPropertyMap.tpl');
    }

    private function setGoogleCatMap($type)
    {
        $categoriesMap = Db::getInstance()->ExecuteS('
			SELECT `category_id`, `g_category_id`
			FROM '._DB_PREFIX_.'blmod_xml_g_cat
			WHERE `type` = "'.pSQL($type).'"
		');

        if (!empty($categoriesMap)) {
            foreach ($categoriesMap as $m) {
                $this->googleCategoriesMap[$m['category_id']] = array(
                    'id' => $m['g_category_id'],
                    'name' => isset($this->googleCategories[$m['g_category_id']]) ? $this->googleCategories[$m['g_category_id']] : '',
                );
            }
        }
    }

    public function updateGoogleCat($categories = array())
    {
        $categoryMap = new CategoryMap();

        $type = htmlspecialchars(Tools::getValue('category_type'), ENT_QUOTES);

        Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'blmod_xml_g_cat WHERE type = "'.pSQL($type).'"');

        if (empty($categories)) {
            $this->notification->addConf($this->l('Google categories successfully removed'));
            return false;
        }

        $googleCategory = new GoogleCategoryBlMod($categoryMap->getFileNameById($type));
        $googleCategories = $googleCategory->getList();

        foreach ($categories as $id => $n) {
            $gId = array_search($n, $googleCategories);

            if (empty($gId)) {
                continue;
            }

            Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'blmod_xml_g_cat
                (`category_id`, `g_category_id`, `type`)
                VALUE
                ("'.pSQL($id).'", "'.pSQL($gId).'", "'.pSQL($type).'")');
        }

        $this->notification->addConf($this->l('Category map successfully updated'));

        return true;
    }

    public function addNewFeed($feed_type = 1, $full_address_no_t = '', $token = 0)
    {
        $xmlFeedInstall = new XmlFeedInstall();
        $feedTypeClass = new FeedType();
        $categoryMap = new CategoryMap();
        $feedTypeList = $feedTypeClass->getAllTypes();
        $mostPopularTypeList = $feedTypeClass->getMostPopularTypes();
        $feed_type = (int)$feed_type;

        uasort($feedTypeList, function ($a, $b) {
            return Tools::strtolower($a['name']) > Tools::strtolower($b['name']);
        });

        $POST = array();
        $POST['add_new_feed_insert'] = Tools::getValue('add_new_feed_insert');

        if (!empty($POST['add_new_feed_insert'])) {
            $POST['name'] = Tools::getValue('name');
            $POST['feed_type'] = Tools::getValue('feed_type');
            $POST['feed_mode'] = Tools::getValue('feed_mode');
            $feedDefaultSettings = $feedTypeList[$POST['feed_mode']];
            $defaultCategoryId = !empty($feedDefaultSettings['category_id']) ? $feedDefaultSettings['category_id'] : 0;

            if (empty($defaultCategoryId) && !empty($feedDefaultSettings['category_key'])) {
                $defaultCategoryId = $categoryMap->getIdByKey($feedDefaultSettings['category_key']);
            }

            if (!empty($POST['name'])) {
                Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'blmod_xml_feeds
					(`name`, `status`, `feed_type`, `cache_time`, `cdata_status`, `html_tags_status`, `split_feed_limit`, 
					`only_enabled`, `feed_generation_time_name`, `filter_qty_type`, `filter_qty_value`, `category_map_id`)
					VALUE
					("'.pSQL($POST['name']).'", "1", "'.pSQL($POST['feed_type']).'", "800", "1", "1", "500", "1", 
					"created_at", ">", 1, "'.(int)$defaultCategoryId.'")');

                $new_id = (int)Db::getInstance()->Insert_ID();

                if ($POST['feed_type'] == 1) {
                    $this->installDefaultProductsValues($new_id, $POST['feed_mode']);
                } elseif ($POST['feed_type'] == 3) {
                    $xmlFeedInstall->installDefaultFeedOrderSettings($new_id);
                } else {
                    $this->installDefaultCategoriesValues($new_id);
                }

                Tools::redirectAdmin($full_address_no_t.'&display_conf=1&page='.$new_id.$token);
            } else {
                $this->notification->addWarn($this->l('Error, empty feed name'));
            }
        }

        $this->smarty->assign([
            'requestUri' => $_SERVER['REQUEST_URI'],
            'feed_type' => $feed_type,
            'feedTypeList' => $feedTypeList,
            'mostPopularTypeList' => $mostPopularTypeList,
            'moduleImgPath' => $this->moduleImgPath,
        ]);

        $this->templates .= $this->displaySmarty('views/templates/admin/page/addNew.tpl');
    }

    public function statisticsOne($statistics, $full_address_no_t, $token)
    {
        $statistics = (int) $statistics;

        $order = pSQL(Tools::getValue('order'));
        $order_name = pSQL(Tools::getValue('order_name'));
        $page = pSQL(Tools::getValue('page_no'));

        $feed = Db::getInstance()->getRow('SELECT name, total_views FROM '._DB_PREFIX_.'blmod_xml_feeds WHERE id = "'.(int)$statistics.'"');

        if ($order != 'asc') {
            $order = 'desc';
        }

        if (empty($order_name) || $order_name != 'affiliate_name' || $order_name != 'date' || $order_name != 'ip_address') {
            $order_name = 'id';
        }

        $pag = XmlFeedsTools::pagination($page, XmlFeedsTools::ITEM_IN_PAGE, $feed['total_views'], $full_address_no_t.'&statistics='.$statistics.'&order_name='.$order_name.'&order='.$order.$token.'&', 'page_no');

        $stat = Db::getInstance()->ExecuteS('SELECT `affiliate_name`, `date`, `ip_address`
			FROM '._DB_PREFIX_.'blmod_xml_statistics
			WHERE feed_id = "'.(int)$statistics.'"
			ORDER BY '.$order_name.' '.$order.'
			LIMIT '.(int)$pag[0].', '.(int)$pag[1]);

        if ($order == 'desc') {
            $order = 'asc';
        } else {
            $order = 'desc';
        }

        $this->smarty->assign([
            'moduleImgPath' => $this->moduleImgPath,
            'stat' => $stat,
            'feed' => $feed,
            'full_address_no_t' => $full_address_no_t,
            'token' => $token,
            'order' => '&order='.$order,
            'statistics' => $statistics,
            'pag' => $pag,
        ]);

        $this->templates .= $this->displaySmarty('views/templates/admin/page/statistics.tpl');
    }

    public function feedsSettings($page)
    {
        $categoryMap = new CategoryMap();
        $xmlFeedUrl = new XmlFeedUrl();
        $productPropertyMap = new ProductPropertyMap();
        $feedMeta = new FeedMeta();
        $isNewFeed = Tools::getValue('display_conf');
        $isNewDuplicate = Tools::getValue('display_dp_conf');

        $s = Db::getInstance()->getRow('SELECT *
			FROM '._DB_PREFIX_.'blmod_xml_feeds
			WHERE id = "'.(int)$page.'"');

        $feedMetaValues = $feedMeta->getFeedMeta($page);

        if (empty($s)) {
            $this->notification->addWarn($this->l('The feed not found, please select from list'));
            return false;
        }

        $s = array_merge($s, $feedMetaValues[$page]);
        $s['name'] = isset($s['name']) ? $s['name'] : false;
        $s['status'] = isset($s['status']) ? $s['status'] : false;
        $s['use_cache'] = isset($s['use_cache']) ? $s['use_cache'] : false;
        $s['cache_time'] = !empty($s['cache_time']) ? $s['cache_time'] : 0;
        $s['protect_by_ip'] = !empty($s['protect_by_ip']) ? $s['protect_by_ip'] : '';
        $s['use_password'] = isset($s['use_password']) ? $s['use_password'] : false;
        $s['password'] = !empty($s['password']) ? $s['password'] : false;
        $s['cdata_status'] = isset($s['cdata_status']) ? $s['cdata_status'] : false;
        $s['html_tags_status'] = isset($s['html_tags_status']) ? $s['html_tags_status'] : false;
        $s['one_branch'] = isset($s['one_branch']) ? $s['one_branch'] : false;
        $s['header_information'] = isset($s['header_information']) ? htmlspecialchars_decode($s['header_information'], ENT_QUOTES) : false;
        $s['footer_information'] = isset($s['footer_information']) ? htmlspecialchars_decode($s['footer_information'], ENT_QUOTES) : false;
        $s['extra_feed_row'] = isset($s['extra_feed_row']) ? htmlspecialchars_decode($s['extra_feed_row'], ENT_QUOTES) : false;
        $s['feed_type'] = isset($s['feed_type']) ? $s['feed_type'] : false;
        $s['only_enabled'] = isset($s['only_enabled']) ? $s['only_enabled'] : false;
        $s['split_feed'] = isset($s['split_feed']) ? $s['split_feed'] : false;
        $s['split_feed_limit'] = isset($s['split_feed_limit']) ? $s['split_feed_limit'] : false;
        $s['categories'] = isset($s['categories']) ? $s['categories'] : false;
        $s['cat_list'] = isset($s['cat_list']) ? $s['cat_list'] : false;
        $s['use_cron'] = isset($s['use_cron']) ? $s['use_cron'] : false;
        $s['last_cron_date'] = isset($s['last_cron_date']) ? $s['last_cron_date'] : '-';
        $s['only_in_stock'] = isset($s['only_in_stock']) ? $s['only_in_stock'] : false;
        $s['attribute_as_product'] = isset($s['attribute_as_product']) ? $s['attribute_as_product'] : false;
        $s['manufacturer'] = isset($s['manufacturer']) ? $s['manufacturer'] : false;
        $s['manufacturer_list'] = isset($s['manufacturer_list']) ? $s['manufacturer_list'] : false;
        $s['supplier'] = isset($s['supplier']) ? $s['supplier'] : false;
        $s['supplier_list'] = isset($s['supplier_list']) ? $s['supplier_list'] : false;
        $s['price_with_currency'] = isset($s['price_with_currency']) ? $s['price_with_currency'] : false;
        $s['feed_mode'] = isset($s['feed_mode']) ? $s['feed_mode'] : false;
        $s['all_images'] = isset($s['all_images']) ? $s['all_images'] : false;
        $s['currency_id'] = isset($s['currency_id']) ? $s['currency_id'] : false;
        $s['feed_generation_time'] = isset($s['feed_generation_time']) ? $s['feed_generation_time'] : false;
        $s['feed_generation_time_time'] = isset($s['feed_generation_time_time']) ? $s['feed_generation_time_time'] : '';
        $s['split_by_combination'] = isset($s['split_by_combination']) ? $s['split_by_combination'] : '';
        $s['product_list_status'] = isset($s['product_list_status']) ? $s['product_list_status'] : '';
        $s['product_list'] = isset($s['product_list']) ? explode(',', $s['product_list']) : '';
        $s['shipping_country'] = isset($s['shipping_country']) ? $s['shipping_country'] : '';
        $s['filter_discount'] = isset($s['filter_discount']) ? $s['filter_discount'] : 0;
        $s['filter_category_type'] = isset($s['filter_category_type']) ? $s['filter_category_type'] : 0;
        $s['product_settings_package_id'] = isset($s['product_settings_package_id']) ? $s['product_settings_package_id'] : 0;
        $s['filter_qty_status'] = isset($s['filter_qty_status']) ? $s['filter_qty_status'] : 0;
        $s['filter_qty_type'] = isset($s['filter_qty_type']) ? $s['filter_qty_type'] : 0;
        $s['filter_qty_value'] = isset($s['filter_qty_value']) ? $s['filter_qty_value'] : 0;
        $s['price_format_id'] = isset($s['price_format_id']) ? $s['price_format_id'] : 0;
        $s['in_stock_text'] = isset($s['in_stock_text']) ? $s['in_stock_text'] : '';
        $s['on_demand_stock_text'] = isset($s['on_demand_stock_text']) ? $s['on_demand_stock_text'] : '';
        $s['out_of_stock_text'] = isset($s['out_of_stock_text']) ? $s['out_of_stock_text'] : '';
        $s['merge_attributes_by_group'] = isset($s['merge_attributes_by_group']) ? $s['merge_attributes_by_group'] : 0;
        $s['merge_attributes_parent'] = isset($s['merge_attributes_parent']) ? $s['merge_attributes_parent'] : 0;
        $s['merge_attributes_child'] = isset($s['merge_attributes_child']) ? $s['merge_attributes_child'] : 0;
        $s['product_list_exclude'] = isset($s['product_list_exclude']) ? explode(',', $s['product_list_exclude']) : array();
        $s['filter_image'] = isset($s['filter_image']) ? $s['filter_image'] : 0;
        $s['cat_without_list'] = isset($s['cat_without_list']) ? $s['cat_without_list'] : array();
        $s['category_map_id'] = isset($s['category_map_id']) ? $s['category_map_id'] : 0;
        $s['encoding_text'] = isset($s['encoding_text']) ? $s['encoding_text'] : '';
        $s['only_on_sale'] = isset($s['only_on_sale']) ? $s['only_on_sale'] : 0;
        $s['attribute_map_id'] = isset($s['attribute_map_id']) ? $s['attribute_map_id'] : 0;
        $s['feature_map_id'] = isset($s['feature_map_id']) ? $s['feature_map_id'] : 0;
        $s['only_available_for_order'] = isset($s['only_available_for_order']) ? $s['only_available_for_order'] : 0;
        $s['filter_exclude_empty_params'] = isset($s['filter_exclude_empty_params']) ? explode(',', $s['filter_exclude_empty_params']) : array();
        $s['shipping_price_mode'] = !empty($s['shipping_price_mode']) ? $s['shipping_price_mode'] : 0;
        $s['affiliate'] = !empty($s['affiliate']) ? $s['affiliate'] : [];
        $this->feedSettingsAdmin = $s;

        if ($s['feed_type'] == '1') {
            $prices_affiliate = Db::getInstance()->ExecuteS('SELECT `affiliate_id`, `affiliate_name`, `affiliate_formula`, `xml_name`
				FROM '._DB_PREFIX_.'blmod_xml_affiliate_price
				ORDER BY affiliate_name ASC');
        }

        if ($s['use_password']) {
            $pass_in_link = '&password=XML_PASSWORD';
        } else {
            $pass_in_link = '';
        }

        $multistore_status = false;
        $multistore = array();

        if (_PS_VERSION_ >= '1.5') {
            $multistore = Shop::getShops();

            if (count($multistore) > 1) {
                $multistore_status = true;
            }
        }

        $priceFormat = new PriceFormat();
        $currency = new CurrencyCore();
        $feedSettingsAdmin = new FeedSettingsAdmin($this->shopLang);
        $productSettingsAdmin = new ProductSettingsAdmin();
        $productTitleEditor = new ProductTitleEditor();
        $currencies = $currency->getCurrencies();
        $currencyActive = array();
        $currencyList = array();
        $priceFromList = $priceFormat->getList();
        $productListFeed = new ProductListAdmin();

        if (!empty($currencies)) {
            foreach ($currencies as $c) {
                if (in_array($c['id_currency'], $currencyActive)) {
                    continue;
                }

                $currencyList[] = array('id' => $c['id_currency'], 'name' => $c['name'].' ('.$c['sign'].')');
                $currencyActive[] = $c['id_currency'];
            }
        }

        $priceFrom = '';
        $priceTo = '';

        if (!empty($s['price_range'])) {
            list($priceFrom, $priceTo) = explode(';', $s['price_range']);
        }

        $link = $xmlFeedUrl->get('id='.$page.$pass_in_link);

        if (!empty($prices_affiliate)) {
            foreach ($prices_affiliate as $aKey => $p) {
                $linkAf = $xmlFeedUrl->get('id='.$page.$pass_in_link.'&affiliate='.$p['affiliate_name']);
                $prices_affiliate[$aKey]['link'] = $linkAf;
            }
        }

        if (!empty($isNewFeed)) {
            $this->notification->addConf($this->l('Feed created successfully'));
        }

        if (!empty($isNewDuplicate)) {
            $this->notification->addConf($this->l('Feed duplicated successfully'));
        }

        $currentUrl = str_replace('display_conf', 'display_ex_conf', $_SERVER['REQUEST_URI']);
        $currentUrl = str_replace('display_dp_conf', 'display_ex_dp_conf', $currentUrl);

        $this->smarty->assign([
            's' => $s,
            'moduleImgPath' => $this->moduleImgPath,
            'link' => $link,
            'multistore_status' => $multistore_status,
            'prices_affiliate' => !empty($prices_affiliate) ? $prices_affiliate : array(),
            'multistore' => $multistore,
            'cronCommand' => '19 */2 * * * /usr/bin/php -q '._PS_ROOT_DIR_.'/modules/xmlfeeds/api/xml.php '.$page,
            'cronXmlFile' => $this->getShopProtocol().$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/xmlfeeds/xml_files/feed_'.$page.'.xml',
            'page' => $page,
            'fullAdminUrl' => $this->fullAdminUrl,
            'requestUri' => $currentUrl,
            'name' => $this->name,
            'countries' => CountryCore::getCountries($this->shopLang),
            'priceFromList' => $priceFromList,
            'currencyList' => $currencyList,
            'priceFrom' => $priceFrom,
            'priceTo' => $priceTo,
            'supplierList' => $feedSettingsAdmin->supplierList($s['supplier_list']),
            'manufacturersList' => $feedSettingsAdmin->manufacturersList($s['manufacturer_list']),
            'categoriesTree' => $feedSettingsAdmin->categoriesTree($s['cat_list']),
            'categoriesTreeL' => $feedSettingsAdmin->categoriesTree($s['cat_without_list'], false, 'categoryWithoutBox'),
            'productSettingsPackagesList' => $productSettingsAdmin->getProductSettingsPackagesList(),
            'groups' => AttributeGroupCore::getAttributesGroups($this->shopLang),
            'productListSettingsPage' => $productListFeed->getProductListSettingsPage($s['product_list'], $s['product_list_exclude']),
            'filterAttributes' => $feedSettingsAdmin->getFilterAttributesHtml($s),
            'filterWithoutAttributes' => $feedSettingsAdmin->getFilterAttributesHtml($s, true),
            'categoryMapList' => $categoryMap->getList(),
            'attributeMapList' => $productPropertyMap->getMaps(ProductPropertyMap::TYPE_ATTRIBUTE),
            'featureMapList' => $productPropertyMap->getMaps(ProductPropertyMap::TYPE_FEATURE),
            'productTitleEditorValues' => $productTitleEditor->getByFeedId($page, true),
            'productTitleEditorElementsList' => $productTitleEditor->getAvailableNewTitleElementsList(),
            'productTitleEditorNewElements' => $productTitleEditor->getNewElementsByFeedId($page, true),
            'productFeatures' => $this->productFeatureList(),
            'productAttributes' => $this->getProductAttributeList(),
        ]);

        if ($s['feed_type'] == '3') {
            $this->smarty->assign([
                'filterDateTypes' => $feedSettingsAdmin->getFilterDateTypes(),
                'FILTER_DATE_DATE_RANGE' => OrderSettings::FILTER_DATE_DATE_RANGE,
                'FILTER_DATE_CUSTOM_DAYS' => OrderSettings::FILTER_DATE_CUSTOM_DAYS,
                'orderStatusList' => $feedSettingsAdmin->getOrderStatusList($s['order_state']),
                'orderPaymentsList' => $feedSettingsAdmin->getOrderPaymentsList($s['order_payment']),
            ]);
        }

        $this->templates .= $this->displaySmarty('views/templates/admin/page/editFeedSettings.tpl');

        if ($s['feed_type'] == '1') {
            $this->productsXml($page);
        } elseif ($s['feed_type'] == '2') {
            $this->categoriesXml($page);
        } elseif ($s['feed_type'] == '3') {
            $orderFieldsAdmin = new OrderFieldsAdmin();

            $this->smarty->assign([
                'moduleImgPath' => $this->moduleImgPath,
                'requestUri' => $_SERVER['REQUEST_URI'],
                'page' => $page,
                'inputsHtml' => $orderFieldsAdmin->getFieldSettings($page, $this->moduleImgPath),
            ]);

            $this->templates .= $this->displaySmarty('views/templates/admin/page/orderFields.tpl');
        }
    }

    public function recurseCategoryForIncludePref($indexedCategories, $categories, $current, $id_category = 1, $id_category_default = null, $selected = array(), $isGoogleCat = false, $checkboxName = 'categoryBox')
    {
        $img_type = 'png';

        static $irow;

        if (!isset($this->recurseDone[$current['infos']['id_parent']])) {
            $this->recurseDone[$current['infos']['id_parent']] = 0;
        }

        $this->recurseDone[$current['infos']['id_parent']] += 1;

        $categories[$current['infos']['id_parent']] = isset($categories[$current['infos']['id_parent']]) ? $categories[$current['infos']['id_parent']] : false;

        $todo = count($categories[$current['infos']['id_parent']]);
        $doneC = $this->recurseDone[$current['infos']['id_parent']];

        $level = $current['infos']['level_depth'] + 1;
        $img = $level == 1 ? 'lv1.'.$img_type : 'lv'.$level.'_'.($todo == $doneC ? 'f' : 'b').'.'.$img_type;
        $levelImg = '<img src="'.$this->moduleImgPath.''.$img.'" alt="" />';

        if ($level > 5) {
            $levelSpace = (($level - 2) * 24) - 12;
            $levelImg = '<div class="category-level" style="width: '.$levelSpace.'px;"><br></div>';
            $levelImg .= '<div class="category-level-'.($todo == $doneC ? 'f' : 'b').'"><br></div>';
        }

        $checked = false;

        if (in_array($id_category, $selected)) {
            $checked = 'checked="yes"';
        }

        $selectBox = '<td class="center">
				<input type="checkbox" id="'.$checkboxName.'_'.$id_category.'" name="'.$checkboxName.'[]" '.$checked.' value="'.$id_category.'" class="noborder">
			</td>';

        $selectBoxW = '';
        $inputField = '';

        if ($isGoogleCat) {
            $selectBox = '';
            $selectBoxW = 'width: 25px;';

            $googleCatValue = '';

            if (!empty($this->googleCategoriesMap[$id_category])) {
                $googleCatValue = $this->googleCategoriesMap[$id_category]['name'];
            }

            $inputField = '
                <div><input type="text" placeholder="'.$this->l('Enter category name').'" id="google_cat_map_'.$id_category.'" class="google_cat_map_blmod" name="google_cat_map['.$id_category.']" value="'.$googleCatValue.'"></div>
            <div style="clear: both;"></div>
            ';
        }

        $this->htmlCategory .= '<tr class="'.($irow++ % 2 ? 'alt_row' : '').'">
			'.$selectBox.'
			<td style="'.$selectBoxW.'">
				'.$id_category.'
			</td>
			<td>
				<div style="float: left;">'.$levelImg.'</div>
				<div style="float: left;">
				    <label style="line-height: 26px;" for="'.$checkboxName.'_'.$id_category.'" class="t">'.Tools::stripslashes($current['infos']['name']).'</label>
				</div>
                <div style="clear: both;"></div>
				'.$inputField.'
			</td>
		</tr>';

        if (isset($categories[$id_category])) {
            foreach ($categories[$id_category] as $key => $row) {
                if ($key != 'infos') {
                    $this->recurseCategoryForIncludePref($indexedCategories, $categories, $categories[$id_category][$key], $key, null, $selected, $isGoogleCat, $checkboxName);
                }
            }
        }
    }

    public static function getCategories($id_lang, $active = true, $order = true)
    {
        $result = Db::getInstance()->ExecuteS('
			SELECT *
			FROM `'._DB_PREFIX_.'category` c
			LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON c.`id_category` = cl.`id_category`
			WHERE `id_lang` = '.(int)$id_lang.'
			'.($active ? 'AND `active` = 1' : '').'
			ORDER BY `name` ASC');

        if (!$order) {
            return $result;
        }

        $categories = array();

        foreach ($result as $row) {
            $categories[$row['id_parent']][$row['id_category']]['infos'] = $row;
        }

        return $categories;
    }

    public function categoriesTree($selected = false, $isGoogleCat = false, $checkboxName = 'categoryBox')
    {
        $this->recurseDone = [];
        $langId = !empty($this->shopLang) ? $this->shopLang : $this->context->language->id;

        if (!empty($selected)) {
            $sel_array = explode(',', $selected);
        } else {
            $sel_array = array();
        }

        $margin = 'margin-right: 9px;';
        $border = '';
        $checkDelBoxes = "checkDelBoxes(this.form, '".$checkboxName."[]', this.checked)";
        $hideMessage = '<div class="categories_list_button" style="cursor: pointer; color: #268CCD; text-align: left; margin-top: 10px;">'.$this->l('[Hide]').'</div>';
        $selectBox = '<th><input type="checkbox" name="checkme" class="noborder" onclick="'.$checkDelBoxes.'"></th>';

        $this->htmlCategory = '';

        if ($isGoogleCat) {
            $margin = '';
            $border = 'border: 0px; margin-top: 6px;';
            $hideMessage = '';
            $selectBox = '';
        }

        $this->htmlCategory .= '<div style = "'.$margin.'">
				<table cellspacing="0" cellpadding="0" class="table blmod-table-light table-no-space" id = "radio_div" style="'.$border.'">
					<tr>
						'.$selectBox.'
						<th>'.$this->l('ID').'</th>
						<th style="width: 400px">'.$this->l('Name').'</th>
					</tr>';

        $categories = Category::getCategories($langId, false);

        if (!empty($categories)) {
            $categories[0][1] = isset($categories[0][1]) ? $categories[0][1] : false;
            $this->recurseCategoryForIncludePref(null, $categories, $categories[0][1], 1, null, $sel_array, $isGoogleCat, $checkboxName);
        }

        $this->htmlCategory .= '</table>
				'.$hideMessage.'
			</div>';

        return $this->htmlCategory;
    }

    public function productsXml($page)
    {
        $this->smarty->assign([
            'requestUri' => $_SERVER['REQUEST_URI'],
            'page' => $page,
            'inputsHtml' => $this->productsXmlSettings($page),
        ]);

        $this->templates .= $this->displaySmarty('views/templates/admin/page/productFields.tpl');
    }

    public function categoriesXml($page)
    {
        $this->smarty->assign([
            'requestUri' => $_SERVER['REQUEST_URI'],
            'page' => $page,
            'inputsHtml' => $this->categoriesXmlSettings($page),
        ]);

        $this->templates .= $this->displaySmarty('views/templates/admin/page/categoryFields.tpl');
    }

    public function printBlock($block_name = false, $info = array(), $only_checkbox = false)
    {
        if (empty($info)) {
            return false;
        }

        $scroll = '';

        if (count($info) > 40) {
            $scroll = ' attribute-box-scroll';
        }

        $editPriceFields = [
            'price+product',
            'price_sale_blmod+bl_extra',
            'price_wt_discount_blmod+bl_extra',
            'price_shipping_blmod+bl_extra',
            'wholesale_price+product',
            'price_sale_tax_excl_blmod+bl_extra',
        ];

        $fields = [];

        foreach ($info as $y => $i) {
            if ($i['name'] == 'isbn' && _PS_VERSION_ < 1.7) {
                continue;
            }

            $tagName = !empty($i['tagName']) ? $i['tagName'] : $i['title'];
            $fields[$y] = $i;
            $fields[$y]['status_value'] = isset($this->tags_info[$i['name'].'+'.$i['table'].'+status']) ? $this->tags_info[$i['name'].'+'.$i['table'].'+status'] : false;
            $fields[$y]['value'] = !empty($this->tags_info[$i['name'].'+'.$i['table']]) ? $this->tags_info[$i['name'].'+'.$i['table']] : (!empty($i['placeholder']) ? '' : $tagName);
            $fields[$y]['box_id'] = $i['name'].'_'.$i['table'].'_option';
            $fields[$y]['title'] = $this->l(str_replace('_', ' ', $i['title']));
            $fields[$y]['status_name'] = $i['name'].'+'.$i['table'].'+status';
            $fields[$y]['field_name'] = $i['name'].'+'.$i['table'];
            $fields[$y]['field_name_safe'] = $i['name'].'_'.$i['table'];
            $fields[$y]['is_only_checkbox'] = $only_checkbox;

            if ($i['name'].'+'.$i['table'] == 'id_product+product') {
                $fields[$y]['product_id_prefix'] = !empty($this->feedSettingsAdmin['product_id_prefix']) ? htmlspecialchars($this->feedSettingsAdmin['product_id_prefix'], ENT_QUOTES) : '';
                continue;
            }

            if ($i['name'].'+'.$i['table'] == 'product_categories_tree+bl_extra') {
                $fields[$y]['category_tree_separator'] = !empty($this->feedSettingsAdmin['category_tree_separator']) ? htmlspecialchars($this->feedSettingsAdmin['category_tree_separator'], ENT_QUOTES) : '';
                continue;
            }

            if (in_array($i['name'].'+'.$i['table'], $editPriceFields)) {
                $editPriceValue = '';
                $editPriceType = 1;
                $editPriceTypeList = [
                    1 => '+',
                    2 => '-',
                    3 => '*',
                    4 => '/',
                    5 => 'increase %',
                    6 => 'reduce %',
                ];

                $editPriceTypeListNameShort = [
                    1 => '+',
                    2 => '-',
                    3 => '*',
                    4 => '/',
                    5 => '+%',
                    6 => '-%',
                ];

                if (!empty($this->feedSettingsAdmin['edit_price_type'])) {
                    $editPriceType = !empty($this->feedSettingsAdmin['edit_price_type'][$i['name'] . '+' . $i['table']]) ? $this->feedSettingsAdmin['edit_price_type'][$i['name'] . '+' . $i['table']] : 1;
                }

                if (!empty($this->feedSettingsAdmin['edit_price_value'])) {
                    $editPriceValue = !empty($this->feedSettingsAdmin['edit_price_value'][$i['name'] . '+' . $i['table']]) ? $this->feedSettingsAdmin['edit_price_value'][$i['name'] . '+' . $i['table']] : '';
                }

                $fields[$y]['isEditPriceField'] = true;
                $fields[$y]['editPriceValue'] = $editPriceValue;
                $fields[$y]['editPriceType'] = $editPriceType;
                $fields[$y]['editPriceActionName'] = $editPriceTypeListNameShort[$editPriceType];
                $fields[$y]['editPriceTypeList'] = $editPriceTypeList;
            }
        }

        $this->smarty->assign([
            'fields' => $fields,
            'block_name' => $this->l($block_name),
            'scrollClass' => $scroll,
            'only_checkbox' => $only_checkbox,
            'blockClass' => Tools::strtolower(str_replace(' ', '_', $block_name)),
        ]);

        return $this->displaySmarty('views/templates/admin/element/fieldsSettingsBox.tpl');
    }

    public function productsXmlSettings($page_id = false)
    {
        $v = array();
        $b_name = array();
        $b_status = array();
        $lang_array = array();
        $page_id = (int)$page_id;

        $settings = Db::getInstance()->getRow('SELECT one_branch, split_by_combination, feed_mode
			FROM '._DB_PREFIX_.'blmod_xml_feeds
			WHERE id = "'.(int)$page_id.'"');

        $disabled_branch_name = '';

        if (!empty($settings['one_branch'])) {
            $disabled_branch_name = 'disabled="disabled"';
        }

        $r = Db::getInstance()->ExecuteS('SELECT `name`, `status`, `title_xml`, `table`
			FROM '._DB_PREFIX_.'blmod_xml_fields
			WHERE category = "'.$page_id.'"');

        foreach ($r as $k) {
            $v[$k['name'].'+'.$k['table']] = isset($k['title_xml']) ? $k['title_xml'] : false;
            $v[$k['name'].'+'.$k['table'].'+status'] = isset($k['status']) ? $k['status'] : false;
        }

        if (!empty($v)) {
            $this->tags_info = $v;
        }

        $r_b = Db::getInstance()->ExecuteS('SELECT `name`, `value`, `status`, `category`
			FROM '._DB_PREFIX_.'blmod_xml_block
			WHERE category = "'.(int)$page_id.'"');

        foreach ($r_b as $bl) {
            $b_name[$bl['name']] = isset($bl['value']) ? $bl['value'] : false;
            $b_status[$bl['name']] = isset($bl['status']) ? $bl['status'] : false;
        }

        $b_name['extra-product-rows'] = !empty($b_name['extra-product-rows']) ? htmlspecialchars_decode($b_name['extra-product-rows'], ENT_QUOTES) : false;
        $b_name['extra-offer-rows'] = !empty($b_name['extra-offer-rows']) ? htmlspecialchars_decode($b_name['extra-offer-rows'], ENT_QUOTES) : false;

        $this->smarty->assign([
            'settings' => $settings,
            'b_name' => $b_name,
            'b_status' => $b_status,
            'disabled_branch_name' => $disabled_branch_name,
            'fullAdminUrl' => $this->fullAdminUrl,
        ]);

        $html = $this->displaySmarty('views/templates/admin/element/productBlockSettings.tpl');

        $productBasicInformationFields = array(
            array('name' => 'product_url_blmod', 'title' => 'product_url', 'table' => 'bl_extra'),
            array('name' => 'product_url_utm_blmod', 'title' => 'product_url_utm_code', 'table' => 'bl_extra', 'placeholder' => 'Example: ?utm=eshop&med=xml'),
            array('name' => 'id_product', 'title' => 'id_product', 'table' => 'product'),
            array('name' => 'parent_id_product', 'title' => 'parent_id_product', 'table' => 'bl_extra'),
            array('name' => 'additional_id_product', 'title' => 'additional_id_product', 'table' => 'bl_extra'),
            array('name' => 'additional_id_combination', 'title' => 'additional_id_combination', 'table' => 'bl_extra'),
            array('name' => 'id_supplier', 'title' => 'id_supplier', 'table' => 'product'),
            array('name' => 'name', 'title' => 'supplier_name', 'table' => 'supplier'),
            array('name' => 'supplier_reference', 'title' => 'supplier_reference', 'table' => 'product'),
            array('name' => 'id_manufacturer', 'title' => 'id_manufacturer', 'table' => 'product'),
            array('name' => 'name', 'title' => 'manufacturer_name', 'table' => 'manufacturer'),
            array('name' => 'location', 'title' => 'location', 'table' => 'product'),
            array('name' => 'height', 'title' => 'height', 'table' => 'product'),
            array('name' => 'width', 'title' => 'width', 'table' => 'product'),
            array('name' => 'weight', 'title' => 'weight', 'table' => 'product'),
            array('name' => 'depth', 'title' => 'depth', 'table' => 'product'),
            array('name' => 'on_sale', 'title' => 'on_sale', 'table' => 'product'),
            array('name' => 'reference', 'title' => 'reference', 'table' => 'product'),
            array('name' => 'parent_reference', 'title' => 'parent_reference', 'table' => 'bl_extra'),
            array('name' => 'additional_reference', 'title' => 'additional_reference', 'table' => 'bl_extra'),
            array('name' => 'ean13', 'title' => 'ean-13 or jan barcode', 'table' => 'product'),
            array('name' => 'isbn', 'title' => 'isbn', 'table' => 'product'),
            array('name' => 'upc', 'title' => 'upc_barcode', 'table' => 'product'),
            array('name' => 'active', 'title' => 'active', 'table' => 'product'),
            array('name' => 'days_back_created', 'title' => 'days_back_created', 'table' => 'bl_extra'),
            array('name' => 'date_add', 'title' => 'date_add', 'table' => 'product'),
            array('name' => 'date_upd', 'title' => 'date_upd', 'table' => 'product'),
            array('name' => 'condition', 'title' => 'condition', 'table' => 'product'),
            array('name' => 'available_for_order', 'title' => 'available_for_order', 'table' => 'product'),
            array('name' => 'unit', 'title' => 'unit', 'table' => 'bl_extra'),
            array('name' => 'unit_price', 'title' => 'unit_price', 'table' => 'bl_extra'),
            array('name' => 'unit_price_e_tax', 'title' => 'unit_price_excl_tax', 'table' => 'bl_extra'),
            array('name' => 'shipping_country', 'title' => 'shipping_country', 'table' => 'bl_extra'),
            array('name' => 'shipping_country_code', 'title' => 'shipping_country_code', 'table' => 'bl_extra'),
            array('name' => 'product_tags', 'title' => 'product_tags', 'table' => 'bl_extra'),
            array('name' => 'visibility', 'title' => 'visibility', 'table' => 'product'),
        );

        $html .= $this->printBlock(
            'Product basic information',
            $productBasicInformationFields
        );

        $html .= $this->printBlock(
            'Prices, Tax',
            array(
                array('name' => 'price', 'title' => 'retail_/_base_price', 'table' => 'product', 'tagName' => 'retail_price',),
                array('name' => 'price_sale_blmod', 'title' => 'sale_price', 'table' => 'bl_extra'),
                array('name' => 'price_sale_tax_excl_blmod', 'title' => 'sale_price_tax_excl.', 'table' => 'bl_extra', 'tagName' => 'sale_price_tax_excl',),
                array('name' => 'price_wt_discount_blmod', 'title' => 'sale_price_discount_excl.', 'table' => 'bl_extra', 'tagName' => 'sale_price_discount_excl',),
                array('name' => 'wholesale_price', 'title' => 'cost_/_wholesale_price', 'table' => 'product', 'tagName' => 'cost_price',),
                array('name' => 'only_discount_blmod', 'title' => 'discount_amount_only', 'table' => 'bl_extra'),
                array('name' => 'discount_rate_blmod', 'title' => 'discount_rate', 'table' => 'bl_extra'),
                array('name' => 'ecotax', 'title' => 'ecotax', 'table' => 'product'),
                array('name' => 'tax_rate', 'title' => 'tax_rate', 'table' => 'bl_extra'),
                array('name' => 'price_shipping_blmod', 'title' => 'shipping_price', 'table' => 'bl_extra'),
            )
        );

        $html .= $this->printBlock(
            'Quantity',
            array(
                array('name' => 'quantity', 'title' => 'quantity', 'table' => 'product'),
                array('name' => 'quantity_discount', 'title' => 'quantity_discount', 'table' => 'product'),
                array('name' => 'out_of_stock', 'title' => 'out_of_stock', 'table' => 'product'),
                array('name' => 'stock_status', 'title' => 'stock_status', 'table' => 'bl_extra'),
                array('name' => 'minimal_quantity', 'title' => 'minimal_quantity', 'table' => 'product'),
            )
        );

        $feedType = 'category';
        $defaultImage = 'name_'.$feedType.'_default';

        $html .= $this->printBlock(
            'Categories',
            array(
                array('name' => 'id_category_default', 'title' => 'id_category_default', 'table' => 'product'),
                array('name' => 'id_category_all', 'title' => 'id_category_all', 'table' => 'bl_extra'),
                array('name' => 'name', 'title' => $defaultImage, 'table' => 'category_lang'),
                array('name' => 'category_url', 'title' => 'category_url', 'table' => 'bl_extra'),
                array('name' => 'product_categories_tree', 'title' => 'product_category_tree', 'table' => 'bl_extra'),
            )
        );

        //Grouped attributes
        $html .= $this->getGroupedAttributesBox();

        //Product feature
        $html .= $this->productFeatureBox();

        //Get images
        $img_array = array();

        $images = Db::getInstance()->ExecuteS('SELECT id_image_type, name FROM
			'._DB_PREFIX_.'image_type');

        if (!empty($images)) {
            foreach ($images as $img) {
                $img_array[] = array('name' => $img['name'], 'title' => $img['name'], 'table' => 'img_blmod');
            }

            $html .= $this->printBlock('Images', $img_array);
        }

        $html .= $this->printBlock(
            'Descriptions',
            array(
                array('name' => 'description', 'title' => 'description', 'table' => 'product_lang'),
                array('name' => 'description_short', 'title' => 'description_short', 'table' => 'product_lang'),
                array('name' => 'link_rewrite', 'title' => 'link_rewrite', 'table' => 'product_lang'),
                array('name' => 'meta_description', 'title' => 'meta_description', 'table' => 'product_lang'),
                array('name' => 'meta_keywords', 'title' => 'meta_keywords', 'table' => 'product_lang'),
                array('name' => 'meta_title', 'title' => 'meta_title', 'table' => 'product_lang'),
                array('name' => 'name', 'title' => 'name', 'table' => 'product_lang'),
                array('name' => 'available_now', 'title' => 'available_now', 'table' => 'product_lang'),
                array('name' => 'available_later', 'title' => 'available_later', 'table' => 'product_lang'),
            )
        );

        $languages = Db::getInstance()->ExecuteS('SELECT id_lang, name FROM
			'._DB_PREFIX_.'lang');

        $langBlock = '';
        $this->checkedInput = false;

        if (!empty($languages)) {
            foreach ($languages as $lan) {
                $lang_array[] = array('name' => $lan['id_lang'], 'title' => $lan['name'], 'table' => 'lang', 'only_checkbox' => 1);
            }

            $langBlock = $this->printBlock('Languages', $lang_array, 1);
        }

        $html .= $langBlock;

        return $html;
    }

    public function categoriesXmlSettings($page_id = false)
    {
        $b_name = array();
        $v = array();
        $lang_array = array();
        $page_id = (int)$page_id;

        $settings = Db::getInstance()->getRow('SELECT one_branch
			FROM '._DB_PREFIX_.'blmod_xml_feeds
			WHERE id = "'.(int)$page_id.'"');

        $disabled_branch_name = '';

        if (!empty($settings['one_branch'])) {
            $disabled_branch_name = 'disabled="disabled"';
        }

        $r = Db::getInstance()->ExecuteS('
			SELECT `name`, `status`, `title_xml`, `table`
			FROM '._DB_PREFIX_.'blmod_xml_fields
			WHERE category = "'.(int)$page_id.'"
		');

        foreach ($r as $k) {
            $v[$k['name'].'+'.$k['table']] = isset($k['title_xml']) ? $k['title_xml'] : false;
            $v[$k['name'].'+'.$k['table'].'+status'] = isset($k['status']) ? $k['status'] : false;
        }

        $this->tags_info = $v;

        $r_b = Db::getInstance()->ExecuteS('
			SELECT `name`, `value`, `category`
			FROM '._DB_PREFIX_.'blmod_xml_block
			WHERE category = "'.(int)$page_id.'"
		');

        foreach ($r_b as $bl) {
            $b_name[$bl['name']] = isset($bl['value']) ? $bl['value'] : false;
        }

        $this->smarty->assign([
            'b_name' => $b_name,
            'disabled_branch_name' => $disabled_branch_name,
        ]);

        $html = $this->displaySmarty('views/templates/admin/element/categoryBlockSettings.tpl');

        $html .= $this->printBlock(
            'Category basic information',
            array(
                array('name' => 'id_category', 'title' => 'id_category', 'table' => 'category'),
                array('name' => 'id_parent', 'title' => 'id_parent', 'table' => 'category'),
                array('name' => 'level_depth', 'title' => 'level_depth', 'table' => 'category'),
                array('name' => 'active', 'title' => 'active', 'table' => 'category'),
                array('name' => 'date_add', 'title' => 'date_add', 'table' => 'category'),
                array('name' => 'date_upd', 'title' => 'date_upd', 'table' => 'category'),
                array('name' => 'category_url_blmod', 'title' => 'category_url', 'table' => 'bl_extra'),
            )
        );

        $html .= $this->printBlock(
            'Descriptions',
            array(
                array('name' => 'id_lang', 'title' => 'id_lang', 'table' => 'category_lang'),
                array('name' => 'name', 'title' => 'name', 'table' => 'category_lang'),
                array('name' => 'description', 'title' => 'description', 'table' => 'category_lang'),
                array('name' => 'link_rewrite', 'title' => 'link_rewrite', 'table' => 'category_lang'),
                array('name' => 'meta_title', 'title' => 'meta_title', 'table' => 'category_lang'),
                array('name' => 'meta_keywords', 'title' => 'meta_keywords', 'table' => 'category_lang'),
                array('name' => 'meta_description', 'title' => 'meta_description', 'table' => 'category_lang'),
            )
        );

        //get languages
        $languages = Db::getInstance()->ExecuteS('
			SELECT id_lang, `name`
			FROM '._DB_PREFIX_.'lang
			ORDER BY `name` ASC
		');

        if (!empty($languages)) {
            foreach ($languages as $lan) {
                $lang_array[] = array('name' => $lan['id_lang'], 'title' => $lan['name'], 'table' => 'lang', 'only_checkbox' => 1);
            }

            $html .= $this->printBlock('Descriptions languages', $lang_array, 1);
        }

        return $html;
    }

    public function updateFeedsS(
        $name,
        $status,
        $use_cache,
        $cache_time,
        $use_password,
        $password,
        $id,
        $cdata_status,
        $html_tags_status,
        $one_branch,
        $header_information,
        $footer_information,
        $extra_feed_row,
        $only_enabled,
        $split_feed,
        $split_feed_limit,
        $cat_list,
        $categories,
        $use_cron,
        $only_in_stock,
        $attribute_as_product,
        $manufacturer,
        $manufacturerList,
        $supplier,
        $supplierList,
        $priceRange,
        $priceWithCurrency,
        $all_images,
        $currencyId,
        $feed_generation_time,
        $feed_generation_time_name,
        $split_by_combination,
        $productList,
        $productListStatus,
        $shippingCountry,
        $filterDiscount,
        $filterCategoryType,
        $productSettingsPackageId,
        $filterQtyStatus,
        $filterQtyType,
        $filterQtyValue,
        $priceFormatId,
        $POST,
        $catWithoutList,
        $filterImage
    ) {
        $xmlFeedInstall = new XmlFeedInstall();
        $productTitleEditor = new ProductTitleEditor();
        $feedMeta = new FeedMeta();

        $cache_time = (int) $cache_time;
        $split_feed_limit = (int) $split_feed_limit;
        $oldSettings = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'blmod_xml_feeds WHERE id = '.(int)$id);

        if (!empty($use_cron) || !empty($filterDiscount) || !empty($filterQtyStatus) || Tools::strlen($priceRange) > 3) {
            $split_feed = 0;
        }

        $POST['order_state_status'] = Tools::getValue('order_state_status', 0);
        $POST['order_state'] = Tools::getValue('order_state', '');

        if (!empty($POST['order_state'])) {
            $POST['order_state'] = implode(',', $POST['order_state']);
        }

        $POST['order_payments_status'] = Tools::getValue('order_payments_status', 0);
        $POST['order_payment'] = Tools::getValue('order_payment', '');

        if (!empty($POST['order_payment'])) {
            $POST['order_payment'] = implode(',', $POST['order_payment']);
        }

        $POST['filter_date_type'] = Tools::getValue('filter_date_type', 0);
        $POST['filter_date_from'] = Tools::getValue('filter_date_from', '');
        $POST['filter_date_to'] = Tools::getValue('filter_date_to', '');
        $POST['filter_custom_days'] = Tools::getValue('filter_custom_days', 0);

        $POST['merge_attributes_by_group'] = Tools::getValue('merge_attributes_by_group', 0);
        $POST['merge_attributes_parent'] = Tools::getValue('merge_attributes_parent', 0);
        $POST['merge_attributes_child'] = Tools::getValue('merge_attributes_child', 0);
        $POST['only_with_attributes'] = Tools::getValue('only_with_attributes', '');
        $POST['only_with_attributes'] = !empty($POST['only_with_attributes']) ? implode(',', $POST['only_with_attributes']) : '';
        $POST['only_with_attributes_status'] = Tools::getValue('only_with_attributes_status', 0);
        $POST['only_without_attributes'] = Tools::getValue('only_without_attributes', '');
        $POST['only_without_attributes'] = !empty($POST['only_without_attributes']) ? implode(',', $POST['only_without_attributes']) : '';
        $POST['only_without_attributes_status'] = Tools::getValue('only_without_attributes_status', 0);
        $POST['product_list_exclude'] = Tools::getValue('product_list_exclude', '');
        $POST['product_list_exclude'] = !empty($POST['product_list_exclude']) ? implode(',', $POST['product_list_exclude']) : '';
        $POST['category_map_id'] = Tools::getValue('category_map_id', 0);
        $POST['encoding_text'] = Tools::getValue('encoding_text', '');
        $POST['only_on_sale'] = Tools::getValue('only_on_sale', 0);
        $POST['attribute_map_id'] = Tools::getValue('attribute_map_id', 0);
        $POST['feature_map_id'] = Tools::getValue('feature_map_id', 0);
        $POST['only_available_for_order'] = Tools::getValue('only_available_for_order', 0);
        $filterExcludeEmptyParams = Tools::getValue('filter_exclude_empty_params', '');
        $POST['filter_exclude_empty_params'] = !empty($filterExcludeEmptyParams) ? implode(',', Tools::getValue('filter_exclude_empty_params', '')) : '';

        if (!empty($POST['merge_attributes_by_group'])) {
            $split_by_combination = 1;
        }

        $query = Db::getInstance()->Execute('UPDATE ' . _DB_PREFIX_ . 'blmod_xml_feeds SET
			name="' . pSQL($name) . '", status = "' .(int)$status . '", use_cache = "' . (int)$use_cache . '",
			cache_time = "' . pSQL($cache_time) . '", use_password = "' . (int)$use_password . '",
			password = "' . pSQL($password) . '", cdata_status = "' . (int)$cdata_status . '",
			html_tags_status = "' . (int)$html_tags_status . '", one_branch = "' . (int)$one_branch . '",
			header_information = "' . pSQL($header_information, true) . '",
			footer_information = "' . pSQL($footer_information, true) . '", extra_feed_row = "' . pSQL($extra_feed_row, true) . '",
			only_enabled = "' . (int)$only_enabled . '", split_feed = "' . (int)$split_feed . '", split_feed_limit = "' . (int)$split_feed_limit . '",
			cat_list = "' . pSQL($cat_list) . '", categories = "' . (int)$categories . '", use_cron = "' . (int)$use_cron . '", only_in_stock = "' .(int) $only_in_stock . '",
			manufacturer_list = "' . pSQL($manufacturerList) . '", manufacturer = "' . (int)$manufacturer . '", supplier_list = "' . pSQL($supplierList) . '", supplier = "'.(int)$supplier.'",
			attribute_as_product = "' . (int)$attribute_as_product . '", price_range = "' . pSQL($priceRange) . '", price_with_currency = "' .(int)$priceWithCurrency . '",
			all_images = "' . (int)$all_images . '", currency_id = "' . (int)$currencyId . '", feed_generation_time = "' . (int)$feed_generation_time . '",
			feed_generation_time_name = "' . pSQL($this->onSpecial($feed_generation_time_name)) . '", split_by_combination = "' . (int)$split_by_combination . '",
			product_list = "' . pSQL($productList) . '", product_list_status = "' . (int)$productListStatus . '", shipping_country = "' . (int)$shippingCountry . '",
			filter_discount = "' . (int)$filterDiscount . '", filter_category_type = "'.(int)$filterCategoryType.'", product_settings_package_id = "'.(int)$productSettingsPackageId.'",
			filter_qty_status = "'.(int)$filterQtyStatus.'", filter_qty_type = "'.pSQL($filterQtyType).'", filter_qty_value = "'.pSQL($filterQtyValue).'",
			price_format_id = "'.(int)$priceFormatId.'", in_stock_text = "'.pSQL($POST['in_stock_text']).'", on_demand_stock_text = "'.pSQL($POST['on_demand_stock_text']).'",
			out_of_stock_text = "'.pSQL($POST['out_of_stock_text']).'", cat_without_list = "' . pSQL($catWithoutList) . '", categories_without = "' . (int)$POST['categories_without'] . '", 
			filter_category_without_type = "'.(int)$POST['filter_category_without_type'].'", order_state_status = "'.(int)$POST['order_state_status'].'", order_state = "'.pSQL($POST['order_state']).'",
			order_payments_status = "'.(int)$POST['order_payments_status'].'", order_payment = "'.pSQL($POST['order_payment']).'",
			filter_date_type = "'.(int)$POST['filter_date_type'].'", filter_date_from = "'.pSQL($POST['filter_date_from']).'", filter_date_to = "'.pSQL($POST['filter_date_to']).'",
			filter_custom_days = "'.(int)$POST['filter_custom_days'].'", merge_attributes_by_group = "'.(int)$POST['merge_attributes_by_group'].'", 
			merge_attributes_parent = "'.(int)$POST['merge_attributes_parent'].'", merge_attributes_child = "'.(int)$POST['merge_attributes_child'].'",
			only_with_attributes_status = "'.(int)$POST['only_with_attributes_status'].'", only_with_attributes = "'.pSQL($POST['only_with_attributes']).'",
			only_without_attributes_status = "'.(int)$POST['only_without_attributes_status'].'", only_without_attributes = "'.pSQL($POST['only_without_attributes']).'",
			product_list_exclude = "'.pSQL($POST['product_list_exclude']).'", filter_image = "'.(int)$filterImage.'", category_map_id = "'.(int)$POST['category_map_id'].'",
			encoding_text = "'.pSQL($POST['encoding_text']).'",	only_on_sale = "'.(int)$POST['only_on_sale'].'", attribute_map_id = "'.(int)$POST['attribute_map_id'].'",
			feature_map_id = "'.(int)$POST['feature_map_id'].'", protect_by_ip = "'.pSQL($POST['protect_by_ip']).'",
			only_available_for_order = "'.(int)$POST['only_available_for_order'].'", filter_exclude_empty_params = "'.pSQL($POST['filter_exclude_empty_params']).'"
			WHERE id = "'.(int)$id.'"
		');

        $_POST['title_editor_add_elements'] = Tools::getValue('title_editor_add_elements', []);
        $_POST['title_editor_options'] = Tools::getValue('title_editor_options', []);

        if (!empty($split_by_combination) && empty($oldSettings['split_by_combination'])) {
            if (!in_array(ProductTitleEditor::ADD_ALL_ATTRIBUTES, $_POST['title_editor_add_elements'])) {
                $_POST['title_editor_add_elements'][] = ProductTitleEditor::ADD_ALL_ATTRIBUTES;
            }

            if (!in_array('attribute_name', $_POST['title_editor_options'])) {
                $_POST['title_editor_options'][] = 'attribute_name';
            }
        }

        if (empty($split_by_combination) && !empty($oldSettings['split_by_combination'])) {
            unset($_POST['title_editor_add_elements']);
            unset($_POST['title_editor_options']);
            unset($_POST['title_editor_add_attributes']);
        }

        $productTitleEditor->save($id);
        $feedMeta->save($id);

        $error = Db::getInstance()->getMsgError();

        if (!empty($id)) {
            $this->deleteCache($id);
        }

        if ($query) {
            $this->notification->addConf($this->l('Feed fields and settings successfully updated'));
        } else {
            $this->notification->addWarn($this->l('error, insert feed settings.').$error);

            if (!$xmlFeedInstall->isValidMainTable()) {
                $this->notification->addConf($this->l('Looks like there is an issue with database. Please try uninstall module and then install again.').' <a href="'.$this->getDatabaseUpgradeUrl().'">'.$this->l('Also you can try run module database upgrade').'.</a>');
            }
        }
    }

    public function updateFields($type)
    {
        $post = array();
        $category = (int)Tools::getValue('feeds_id');

        Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'blmod_xml_fields WHERE category = "'.(int)$category.'"');
        Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'blmod_xml_block WHERE category = "'.(int)$category.'"');

        if (!empty($category)) {
            $this->deleteCache($category);
        }

        $post['file-name'] = Tools::getValue('file-name');
        $post['cat-block-name'] = Tools::getValue('cat-block-name');
        $post['desc-block-name'] = Tools::getValue('desc-block-name');
        $post['file-name+status'] = Tools::getValue('file-name+status');
        $post['cat-block-name+status'] = Tools::getValue('cat-block-name+status');
        $post['desc-block-name+status'] = Tools::getValue('desc-block-name+status');

        $post['file-name'] = !empty($post['file-name']) ? $this->onSpecial($post['file-name']) : 'categories';
        $post['cat-block-name'] = !empty($post['cat-block-name']) ? $this->onSpecial($post['cat-block-name']) : 'category';
        $post['desc-block-name'] = !empty($post['desc-block-name']) ? $this->onSpecial($post['desc-block-name']) : 'description';

        if ($type == 2) {
            Db::getInstance()->Execute('
				INSERT INTO '._DB_PREFIX_.'blmod_xml_block
				(`name`, `value`, `status`, `category`)
				VALUE
				("file-name",  "'.pSQL($post['file-name']).'", "'.pSQL($post['file-name+status']).'", "'.(int)$category.'"),
				("cat-block-name", "'.pSQL($post['cat-block-name']).'", "'.pSQL($post['cat-block-name+status']).'", "'.(int)$category.'"),
				("desc-block-name", "'.pSQL($post['desc-block-name']).'", "'.pSQL($post['desc-block-name+status']).'", "'.(int)$category.'")
			');
        } elseif ($type == 1) {
            $post['img-block-name'] = Tools::getValue('img-block-name');
            $post['def_cat-block-name'] = Tools::getValue('def_cat-block-name');
            $post['attributes-block-name'] = Tools::getValue('attributes-block-name');
            $post['extra-product-rows'] = Tools::getValue('extra-product-rows');
            $post['extra-offer-rows'] = Tools::getValue('extra-offer-rows');
            $post['img-block-name+status'] = Tools::getValue('img-block-name+status');
            $post['def_cat-block-name+status'] = Tools::getValue('def_cat-block-name+status');
            $post['attributes-block-name+status'] = Tools::getValue('attributes-block-name+status');

            $post['img-block-name'] = !empty($post['img-block-name']) ? $this->onSpecial($post['img-block-name']) : 'images';
            $post['def_cat-block-name'] = !empty($post['def_cat-block-name']) ? $this->onSpecial($post['def_cat-block-name']) : 'default_cat';
            $post['attributes-block-name'] = !empty($post['attributes-block-name']) ? $this->onSpecial($post['attributes-block-name']) : 'attributes';
            $post['extra-product-rows'] = !empty($post['extra-product-rows']) ? $post['extra-product-rows'] : false;
            $post['extra-offer-rows'] = !empty($post['extra-offer-rows']) ? $post['extra-offer-rows'] : false;

            Db::getInstance()->Execute('
				INSERT INTO '._DB_PREFIX_.'blmod_xml_block
				(`name`, `value`, `status`, `category`)
				VALUE
				("file-name", "'.pSQL($post['file-name']).'", "'.pSQL($post['file-name+status']).'", "'.(int)$category.'"),
				("cat-block-name", "'.pSQL($post['cat-block-name']).'", "'.pSQL($post['cat-block-name+status']).'", "'.(int)$category.'"),
				("desc-block-name", "'.pSQL($post['desc-block-name']).'", "'.pSQL($post['desc-block-name+status']).'", "'.(int)$category.'"),
				("img-block-name", "'.pSQL($post['img-block-name']).'", "'.pSQL($post['img-block-name+status']).'", "'.(int)$category.'"),
				("def_cat-block-name", "'.pSQL($post['def_cat-block-name']).'", "'.pSQL($post['def_cat-block-name+status']).'", "'.(int)$category.'"),
				("attributes-block-name", "'.pSQL($post['attributes-block-name']).'", "'.pSQL($post['attributes-block-name+status']).'", "'.(int)$category.'"),
				("extra-product-rows", "'.pSQL($post['extra-product-rows'], true).'", "1", "'.$category.'"),
				("extra-offer-rows", "'.pSQL($post['extra-offer-rows'], true).'", "1", "'.$category.'")
			');
        } elseif ($type == 3) {
            Db::getInstance()->Execute('
				INSERT INTO '._DB_PREFIX_.'blmod_xml_block
				(`name`, `value`, `status`, `category`)
				VALUE
				("orders-branch-name",  "'.pSQL(Tools::getValue('orders-branch-name')).'", "1", "'.(int)$category.'"),
				("order-branch-name", "'.pSQL(Tools::getValue('order-branch-name')).'", "1", "'.(int)$category.'"),
				("products-branch-name", "'.pSQL(Tools::getValue('products-branch-name')).'", "1", "'.(int)$category.'"),
				("product-branch-name", "'.pSQL(Tools::getValue('product-branch-name')).'", "1", "'.(int)$category.'")
			');
        }

        $value = '';
        $insert = true;

        /*
         * We must get full post array, sorry but can't use PS Tool
         */
        $post = $_POST;

        foreach ($post as $id => $val) {
            $name = explode('+', $id);

            if (empty($name[1]) || (!empty($name[2]) && $name[1] != 'lang')) {
                continue;
            }

            $title = isset($val) ? $this->onSpecial($val, $name[0]) : false;
            $status = isset($post[$id.'+status']) ? $post[$id.'+status'] : 0;

            if ($name[1] == 'lang') {
                $status = !empty($post[$id]) ? $post[$id] : 0;
            }

            $value .= '("'.pSQL($name[0]).'", "'.(int)$status.'", "'.pSQL($title).'", "'.pSQL($name[1]).'", "'.(int)$category.'"),';
        }

        if (!empty($value)) {
            $value = trim($value, ',');

            $insert = Db::getInstance()->Execute('
				INSERT INTO '._DB_PREFIX_.'blmod_xml_fields
				(`name`, `status`, `title_xml`, `table`, `category`)
				VALUE
				'.$value.'
			');
        }

        if (!$insert) {
            $this->notification->addWarn($this->l('error, insert fields values'));
        }
    }

    public function deleteFeed($feed_id = 0)
    {
        $feed_id = (int)$feed_id;

        if (empty($feed_id)) {
            return false;
        }

        Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'blmod_xml_block WHERE category = "'.(int)$feed_id.'"');
        Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'blmod_xml_feeds WHERE id = "'.(int)$feed_id.'"');
        Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'blmod_xml_fields WHERE category = "'.(int)$feed_id.'"');
        Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'blmod_xml_statistics WHERE feed_id = "'.(int)$feed_id.'"');

        $this->deleteCache($feed_id);

        $this->notification->addConf($this->l('Deleted successfully'));

        return true;
    }

    public function deleteCache($feed_id = 0, $all_feeds = false)
    {
        $feed_id = (int)$feed_id;

        if (empty($feed_id) && empty($all_feeds)) {
            return false;
        }

        $where = false;

        if (!empty($feed_id)) {
            $where = ' WHERE feed_id = "'.(int)$feed_id.'"';
        }

        $cache = Db::getInstance()->ExecuteS('SELECT file_name FROM '._DB_PREFIX_.'blmod_xml_feeds_cache'.$where);

        if (!empty($cache)) {
            foreach ($cache as $c) {
                @unlink('../modules/xmlfeeds/xml_files/'.$c['file_name'].'.xml');
            }
        }

        Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'blmod_xml_feeds_cache'.$where);
    }

    public function onSpecial($v, $fieldName = '', $isLowerCase = false)
    {
        if ($fieldName == 'product_url_utm_blmod') {
            return $v;
        }

        if ($isLowerCase) {
            $v = Tools::strtolower($v);
        }

        return preg_replace('/[^a-zA-Z0-9_:\/-]/', '_', $v);
    }

    public function getRandProduct()
    {
        $random_product = Db::getInstance()->getRow('
			SELECT id_product
			FROM `'._DB_PREFIX_.'product_attribute`
			WHERE id_product != "0"
		');

        if (!empty($random_product['id_product'])) {
            return $random_product['id_product'];
        }

        return false;
    }

    public function getGroupedAttributesBox()
    {
        $groups = $this->getProductAttributeList();

        if (empty($groups)) {
            return false;
        }

        $html = '';
        $groupRow = array();

        foreach ($groups as $val) {
            $groupRow[] = array('name' => $val['id_attribute_group'], 'title' => $val['name'], 'table' => 'bl_extra_attribute_group',);
        }

        $html .= $this->printBlock('Attributes', $groupRow);

        return $html;
    }

    public function productFeatureBox()
    {
        $featureRow = array();
        $features = $this->productFeatureList();

        if (empty($features)) {
            return false;
        }

        $html = '';

        foreach ($features as $val) {
            $featureRow[] = array('name' => $val['id_feature'], 'title' => $val['name'], 'table' => 'bl_extra_feature',);
        }

        $html .= $this->printBlock('Features', $featureRow);

        return $html;
    }

    public function productFeatureList()
    {
        if (!class_exists('FeatureCore')) {
            return [];
        }

        $featureClass = new FeatureCore();
        $features = $featureClass->getFeatures($this->shopLang, true);

        if (empty($features)) {
            return [];
        }

        return $features;
    }

    public function getProductAttributeList()
    {
        return AttributeGroupCore::getAttributesGroups($this->shopLang);
    }

    public function getPHPExecutableFromPath()
    {
        $paths = explode(PATH_SEPARATOR, getenv('PATH'));

        foreach ($paths as $path) {
            //For windows
            if (strstr($path, 'php.exe') && isset($_SERVER['WINDIR']) && file_exists($path) && is_file($path)) {
                return $path;
            } else {
                $php_executable = $path.DIRECTORY_SEPARATOR.'php'.(isset($_SERVER['WINDIR']) ? '.exe' : '');

                if (file_exists($php_executable) && is_file($php_executable)) {
                    return $php_executable;
                }
            }
        }

        return false;
    }

    public function hideField($field, $table)
    {
        $version = 0;

        if (_PS_VERSION_ < 1.4) {
            $version = 13;
        }

        $fields = array();

        $fields[13] = array(
            'condition-product' => 1,
            'available_for_order-product' => 1,
        );

        if (empty($fields[$version])) {
            return false;
        }

        if (!empty($fields[$version][$field.'-'.$table])) {
            return true;
        }

        return false;
    }

    public function getShopProtocol()
    {
        if (method_exists('Tools', 'getShopProtocol')) {
            return Tools::getShopProtocol();
        }

        return (Configuration::get('PS_SSL_ENABLED') || (!empty($_SERVER['HTTPS'])
                && Tools::strtolower($_SERVER['HTTPS']) != 'off')) ? 'https://' : 'http://';
    }

    protected function validateFeedSettingsUpdate($POST)
    {
        if ((!empty($POST['update_feeds_s']) || !empty($POST['settings_cat']) || !empty($POST['settings_prod'])) && !empty($POST['feeds_name'])) {
            $name = Tools::getValue('name', '');
            $cacheTime = Tools::getValue('cache_time', 0);
            $priceFrom = Tools::getValue('price_from', 0);
            $priceTo = Tools::getValue('price_to', 0);
            $qty = Tools::getValue('filter_qty_value', 0);
            $customDays = Tools::getValue('filter_custom_days', 0);
            $dateFrom = Tools::getValue('filter_date_from', '');
            $dateTo = Tools::getValue('filter_date_to', '');
            $splitFeedLimit = Tools::getValue('split_feed_limit', 0);
            $excludeMinimumOrderQtyFrom = Tools::getValue('exclude_minimum_order_qty_from', 0);
            $excludeMinimumOrderQtyTo = Tools::getValue('exclude_minimum_order_qty_to', 0);
            $protectByIp = str_replace(' ', '', trim(Tools::getValue('protect_by_ip', '')));
            $errors = array();

            if (empty($name)) {
                $errors[] = $this->l('"Feed name" value is empty, please set feed name');
            }

            if (!empty($cacheTime) && !Validate::isInt($cacheTime)) {
                $errors[] = $this->l('"Cache time" value must be specified in minutes (integer number, example: 200)');
            }

            if (!empty($priceFrom) && !Validate::isPrice($priceFrom)) {
                $errors[] = $this->l('Filter by price range "from" value must contain numeric (example: 123.12 or 123)');
            }

            if (!empty($priceTo) && !Validate::isPrice($priceTo)) {
                $errors[] = $this->l('Filter by price range "to" value must contain numeric (example: 123.12 or 123)');
            }

            if (!empty($priceFrom) && !empty($priceTo) && $priceFrom > $priceTo) {
                $errors[] = $this->l('Filter by price range "to" value must be higher');
            }

            if (!empty($qty) && !Validate::isInt($qty)) {
                $errors[] = $this->l('"Filter by quantity" value must be integer (example: 10)');
            }

            if (!empty($customDays) && !Validate::isInt($customDays)) {
                $errors[] = $this->l('"Filter by date" value must be integer (example: 10)');
            }

            if (!empty($dateFrom) && !Validate::isDate($dateFrom)) {
                $errors[] = $this->l('Filter by date "from" value must be date (example: 2021-10-20)');
            }

            if (!empty($dateTo) && !Validate::isDate($dateTo)) {
                $errors[] = $this->l('Filter by date "to" value must be date (example: 2021-10-25)');
            }

            if (!empty($dateFrom) && !empty($dateTo) && $dateFrom > $dateTo) {
                $errors[] = $this->l('Filter by date "to" value must be higher');
            }

            if (!empty($splitFeedLimit) && !Validate::isInt($splitFeedLimit)) {
                $errors[] = $this->l('"Split feed limit" value must be integer (example: 300)');
            }

            if (!empty($protectByIp) && !preg_match('/^[0-9.,]+$/', $protectByIp)) {
                $errors[] = $this->l('Invalid IP address, please look at the example: 11.10.1.1, 22.2.2.3');
            }

            if (!empty($excludeMinimumOrderQtyFrom) && !Validate::isInt($excludeMinimumOrderQtyFrom)) {
                $errors[] = $this->l('Filter "Exclude by minimum order quantity from" value must be integer or empty (example: 1)');
            }

            if (!empty($excludeMinimumOrderQtyTo) && !Validate::isInt($excludeMinimumOrderQtyTo)) {
                $errors[] = $this->l('Filter "Exclude by minimum order quantity to" value must be integer or empty (example: 1)');
            }

            if (!empty($excludeMinimumOrderQtyFrom) && !empty($excludeMinimumOrderQtyTo) && $excludeMinimumOrderQtyFrom > $excludeMinimumOrderQtyTo) {
                $errors[] = $this->l('Filter "Exclude by minimum order quantity to" value must be higher');
            }

            if (!empty($errors)) {
                $this->notification->addWarn(implode('<br>', $errors));
                return false;
            }

            return true;
        }

        return false;
    }

    public function displaySmarty($path)
    {
        $this->smarty->assign('tpl_dir', _PS_MODULE_DIR_.'xmlfeeds/');

        return $this->display(__FILE__, $path);
    }

    public function hookHeader($params)
    {
        $this->feedSettings = $this->getSkroutzFeedSettings();

        if (empty($this->feedSettings['skroutz_analytics_id'])) {
            return false;
        }

        $this->context->smarty->assign(array(
            'skroutzId' => $this->feedSettings['skroutz_analytics_id']
        ));

        return $this->displaySmarty('views/templates/hook/skroutzAnalyticsMain.tpl');
    }

    public function hookOrderConfirmation($params)
    {
        if (!empty($params['order'])) {
            $order = $params['order'];
        } elseif (!empty($params['objOrder'])) {
            $order = $params['objOrder'];
        }

        if (empty($order) || !is_object($order)) {
            return false;
        }

        $this->feedSettings = $this->getSkroutzFeedSettings();

        if (empty($this->feedSettings['skroutz_analytics_id'])) {
            return false;
        }

        if (!class_exists('SkroutzAnalyticsXml', false)) {
            include_once(dirname(__FILE__).'/SkroutzAnalyticsXml.php');
        }

        $skroutzAnalyticsXml = new SkroutzAnalyticsXml($this->feedSettings);

        $products = $order->getProducts();

        foreach ($products as $k => $p) {
            $productAttributeId = $skroutzAnalyticsXml->getCombinationId($p['id_product'], $p['product_attribute_id']);
            $products[$k]['product_attribute_id'] = !empty($productAttributeId) ? '-'.$productAttributeId : '';
        }

        $this->context->smarty->assign(array(
            'order'=> $order,
            'order_products' => $products,
        ));

        return $this->displaySmarty('views/templates/hook/skroutzAnalyticsOrder.tpl');
    }

    protected function getSkroutzFeedSettings()
    {
        if (!class_exists('FeedMeta', false)) {
            include_once(dirname(__FILE__).'/FeedMeta.php');
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
}

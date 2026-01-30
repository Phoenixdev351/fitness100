<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Feed.Biz, Ltd.
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Feed.Biz, Ltd. is strictly forbidden.
 * In order to obtain a license, please contact us: contact@feed.biz
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Feed.Biz, Ltd.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Feed.Biz, Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Feed.Biz, Ltd. a l'adresse: contact@feed.biz
 * ...........................................................................
 *
 * @author    Olivier B.
 * @copyright Copyright (c) 2011-2022 Feed.Biz - Hong Kong - Head Quarters - Room 1408, 14/F,
 *            Tak Shing House - Theatre Lane - 20 des Voeux Road Central, -Central, Hong Kong
 * @license   Commercial license
 * Support by mail  :  support@feed.biz
 */

require_once(dirname(__FILE__) . '/env.php');
require_once(dirname(__FILE__) . '/../feedbiz.php');

require_once(dirname(__FILE__).'/../classes/feedbiz.tools.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.webservice.class.php');

require_once(dirname(__FILE__) . '/../classes/feedbiz.context.class.php');
require_once(dirname(__FILE__) . '/../classes/feedbiz.exportcontext.class.php');
require_once(dirname(__FILE__) . '/../classes/feedbiz.product.class.php');
require_once(dirname(__FILE__) . '/../classes/feedbiz.product_tab.class.php');
require_once(dirname(__FILE__) . '/../classes/feedbiz.product_tab.amazon.class.php');

/**
 * Class FeedBizExportProducts
 */
class FeedBizExportProducts extends Feedbiz
{
    /**
     * @var
     */
    public $directory;
    /**
     * @var
     */
    public $export;
    /**
     * @var array
     */
    private $errors = array();
    /**
     * @var string
     */
    private $cr = "\n";
    /**
     * @var null
     */
    private $exportContext = null;
    /**
     * @var bool
     */
    private $debug = false;

    /**
     * FeedBizExportProducts constructor.
     */
    public function __construct()
    {
        parent::__construct();

        FeedbizContext::restore($this->context);
        $protocal = @(!empty($_SERVER['HTTPS']) ? 'https://' : 'http://');
        $this->ps_images = $protocal . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') .
            __PS_BASE_URI__ . 'img/p/';

        $this->debug = (bool)Configuration::get('FEEDBIZ_DEBUG') || Tools::getValue('debug');

        if ($this->debug) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        } else {
            ob_start();
        }



        $id_employee = (int)Configuration::get('FEEDBIZ_ID_EMPLOYEE');
        $id_employee = empty($id_employee) ? 1 : $id_employee;
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $employee = null;

            if ($id_employee) {
                $employee = new Employee($id_employee);
            }

            if (!Validate::isLoadedObject($employee)) {
                $this->errors [] = array(
                    'file' => basename(__FILE__),
                    'line' => __LINE__,
                    'message' => sprintf($this->l('Wrong Employee, please save the module configuration'))
                );
                die();
            }

            $this->context = Context::getContext();
            $this->context->customer->is_guest = true;
            $this->context->cart = new Cart();
            $this->context->employee = $employee;
            $this->context->currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
            $this->context->customer->is_guest = true;
        }

        register_shutdown_function("fatalHandler");
    }


    /**
     * function fatalHandler
     */
    public function fatalHandler()
    {
        $errfile = "unknown file";
        $errstr  = "shutdown";
        $errno   = E_CORE_ERROR;
        $errline = 0;

        $error = error_get_last();

        if ($error !== null) {
            $errno   = $error["type"];
            $errfile = $error["file"];
            $errline = $error["line"];
            $errstr  = $error["message"];

            print_r(array(date('c'),$errno, $errstr, $errfile, $errline));
        }
    }

    public function viewMem($fn, $line, $track = false, $comment = '')
    {
        $tr = '';
        if (!($track)) {
            $e = new Exception();
            $tr = ($e->getTraceAsString());
        }
        printf('PIN: %s' . "\n", $fn." (".$line.") ".$comment."\n".$tr);
        return ;
//        $mem_usage_all = memory_get_usage(true);
//        $mem_usage = memory_get_usage();
//        $mem_peak = memory_get_peak_usage();
//        $mem_peak_all = memory_get_peak_usage(true);
//        $num = 0;
////        if(function_exists('gc_collect_cycles')){$num = gc_collect_cycles();}
//        $mem_all=array(
//            'Fn'=>$fn,
//            'Line'=>$line,
//            'mem usage'=>($mem_usage/1024) .' k',
//            'mem usage all'=>($mem_usage_all/1024) .' k',
//            'mem peak'=>($mem_peak/1024) .' k',
//            'mem peak all'=>($mem_peak_all/1024) .' k',
////            'mem clear no'=> $num,
//            'c'=>$comment
//
//        );
//        print_r($mem_all);
//        if(!($track)){
//            $e = new Exception();
//            print_r($e->getTraceAsString());
//        }
    }

    /**
     *
     */
    public function dispatch()
    {
        $this->productExport();
    }

    /**
     * @throws PrestaShopException
     */
    private function productExport()
    {
        FeedbizTools::securityCheck();

        $sku_history = array();
        $reference_history = array();
        $combination_history = array();
        $loadedProducts_history = array();
        $languages = Language::getLanguages();
        $avail_lang = array();
        if (is_array($languages)) {
            foreach ($languages as $language) {
                $avail_lang[$language['id_lang']]=$language['iso_code'];
            }
        }
        $conditions = FeedBizProduct::getConditionField();
        $arr_condition = array();
        preg_match_all("/'([\w ]*)'/", $conditions['Type'], $arr_condition);
        $arr_condition = $arr_condition[1];

        $id_country = (int)Configuration::get('PS_COUNTRY_DEFAULT');

        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $id_default_customer_group = Configuration::get('PS_CUSTOMER_GROUP');
        } else {
            $id_default_customer_group = (int)_PS_DEFAULT_CUSTOMER_GROUP_;
        }

        // Customer Group
        $id_customer_group = (int)Configuration::get('FEEDBIZ_CUSTOMER_GROUP');

        if ((int)$id_customer_group && is_numeric($id_customer_group)) {
            $group = new Group($id_customer_group);

            if (!Validate::isLoadedObject($group)) {
                $id_customer_group =  $id_default_customer_group;
            }

            unset($group);
        } else {
            $id_customer_group = $id_default_customer_group;
        }

        $this->context->customer->id = $id_customer_group ? true : false;

        // Feedbiz Amazon Features
        $feedbiz_amazon_features = Configuration::get('FEEDBIZ_AMAZON_FEATURES');

        if (isset($feedbiz_amazon_features) && $feedbiz_amazon_features != '') {
            $amazon_features = unserialize($feedbiz_amazon_features);

            if ($amazon_features instanceof stdClass) {
                // id business group
                if (isset($amazon_features->amazon_business_groups) && !empty($amazon_features->amazon_business_groups)) {
                    $amazon_business_groups = $amazon_features->amazon_business_groups;
                }
            }
        }

        // limit per page
        $limit = Feedbiz::DEFAULT_PRODUCTS_LIMIT;
        $feedbiz_export_limit_per_page = (int)Configuration::get('FEEDBIZ_EXPORT_LIMIT_PER_PAGE');
        if ($feedbiz_export_limit_per_page > 0) {
            $limit = (int)$feedbiz_export_limit_per_page;
        }

        $id_shop = 1;
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $id_shop = $this->context->shop->id;
        }

        $id_lang = $this->id_lang;

        $id_currency = (int)Configuration::get('PS_CURRENCY_DEFAULT');
        $cr = $this->cr;

        //Warehouse
        $id_warehouse = (int)Configuration::get('FEEDBIZ_WAREHOUSE');

        // create DOMDocument();
        $Document = new DOMDocument();
        $Document->preserveWhiteSpace = true;
        $Document->formatOutput = true;
        $Document->encoding = 'utf-8';
        $Document->version = '1.0';

        $ExportData = $Document->appendChild($Document->createElement('ExportData'));

        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $ExportData->setAttribute('ShopName', Configuration::get('PS_SHOP_NAME', null, null, $this->context->shop->id));
        }

        $Products = $ExportData->appendChild($Document->createElement('Products'));

        // Parameters
        $create_active = false;
        $create_in_stock = false;

        // Categories Settings
        $default_categories = array();
        $feedbiz_categories = FeedbizConfiguration::get('FEEDBIZ_CATEGORIES');

        if (method_exists('Category', 'getCategoryInformations')&& $feedbiz_categories!='all') {
            $categories = Category::getCategoryInformations($feedbiz_categories, $id_lang);
        } else {
            $categories = array();
            $categories_full = Category::getCategories($id_lang);
            foreach ($categories_full as $categories_relation) {
                foreach ($categories_relation as $categories_info) {
                    if (in_array($categories_info['infos']['id_category'], $feedbiz_categories)|| $feedbiz_categories=='all') {
                        $categories[] = $categories_info['infos'];
                    }
                }
            }
        }

        foreach ($categories as $category) {
            $default_categories[] = $category['id_category'];
        }

        if (!is_array($default_categories) || !count($default_categories) || !max($default_categories)) {
            $this->errors[] = sprintf('%s(%d):', basename(__FILE__), __LINE__, $this->l('You must configure the categories')).$cr;
            $this->errors[] = 'PS LANG : ' . $id_lang;
            $this->errors[] = 'FB Category : ' . Tools::jsonEncode($feedbiz_categories);
            $this->errors[] = 'PS Category : ' . Tools::jsonEncode($categories);
        }

        // Prices Parameters
        $useTaxes = Configuration::get('FEEDBIZ_USE_TAXES') ? true : false;
        $useSpecials = (Configuration::get('FEEDBIZ_USE_SPECIALS') == '1') ? true : false;

        // Exclusions
        $excluded_manufacturers = unserialize(FeedbizConfiguration::get('FEEDBIZ_FILTER_MANUFACTURERS'));
        $excluded_suppliers = unserialize(FeedbizConfiguration::get('FEEDBIZ_FILTER_SUPPLIERS'));

        // Carrier
        // $selected = Configuration::get ( 'FEEDBIZ_CARRIER' );
        if (defined('Carrier::ALL_CARRIERS')) {
            $all_carriers = Carrier::ALL_CARRIERS;
        } elseif (defined('ALL_CARRIERS')) {
            $all_carriers = ALL_CARRIERS;
        } else {
            $all_carriers = 5;
        }
        $carriers = Carrier::getCarriers($id_lang, false, false, false, null, $all_carriers);

        $id_carrier = null;
        $default_carrier = array();
        foreach ($carriers as $carrier) {
            $id_carrier = $carrier['id_carrier'];
            $default_carrier[] = array(
                'id_carrier' => $id_carrier,
                'id_reference' => $carrier['id_reference']
            );
        }
        $products_no = 0;
        $id_product = null;

        // Export Loop
        if ($default_categories) {
            $products_no = 0;
            $products_determine_no = 0;

            // CONTEXT CHECKING
            $this->exportContext = new FeedBizExportContext();
            FeedBizExportContext::restore($this->exportContext, FeedBizExportContext::CONF_FEEDBIZ_PRODUCTS_EXPORT_CONTEXT);

            if ($this->exportContext->status == FeedBizExportContext::STATUS_COMPLETE) {
                $this->exportContext->status = FeedBizExportContext::STATUS_INCOMPLETE;
                $this->exportContext->currentPage = 0;
                $this->exportContext->currentProduct = 0;
            }

            $products_max_min = FeedBizProduct::getExportProductsMaxMin($default_categories, $create_active, $create_in_stock, null, null, $this->debug, 0);
            $products = FeedBizProduct::getExportProducts($default_categories, $create_active, $create_in_stock, null, null, $this->debug, $this->exportContext->currentProduct);
            $p_sale_history = array();
            foreach ($products_max_min as $products_max_min_ele) {
                $this->exportContext->maxProduct = $products_max_min_ele['max_id_product'];
                $this->exportContext->minProduct = $products_max_min_ele['min_id_product'];
            }

            if ($this->debug) {
                echo 'Loaded context<pre>' . print_r($this->exportContext, true) . '</pre>';
            }

            if ($products) {
                $stock_avail = array();
                foreach ($products as $p) {
                    if (!empty($p['quantity']) && !empty($p['id_product'])) {
                        $stock_avail[(int)$p['id_product']][(int)$p['id_product_attribute']] = $p['quantity'];
                    }
                }

                if ($this->debug) {
                    echo 'Loaded stock_avail <pre>' . print_r($stock_avail, true) . '</pre>';
                }

                foreach ($products as $product) {
                    $products_determine_no++;

                    $id_product = $product['id_product'];

                    $details = new Product($id_product);
                    if (method_exists($details, 'loadStockData')) {
                        $details->loadStockData();
                    }
                    if ($this->debug) {
                        $loadedProducts_history[] = $id_product;
                    }

                    // object loader validation
                    if (!Validate::isLoadedObject($details)) {
                        $this->errors[] = sprintf($this->l('Could not load the product id: %d'), $id_product);
                        continue;
                    }

                    $idx = 0;
                    $product_combinations = array();
                    $ean13 = $details->ean13;
                    $upc = $details->upc;
                    $reference = trim($details->reference);

                    if ($details->id_manufacturer) {
                        if (is_array($excluded_manufacturers) && in_array($details->id_manufacturer, $excluded_manufacturers)) {
                            if ($this->debug) {
                                $this->errors[] = sprintf($this->l('Exclude manufacturer product id: %d'), $id_product);
                            }
                            continue;
                        }
                    }
                    // supplier validation
                    if ($details->id_supplier) {
                        if (is_array($excluded_suppliers) && in_array($details->id_supplier, $excluded_suppliers)) {
                            if ($this->debug) {
                                $this->errors[] = sprintf($this->l('Exclude supplier product id: %d'), $id_product);
                            }
                            continue;
                        }
                    }

                    // START PRODUCT
                    $products_no++;
                    $ProductDetails = $Products->appendChild($product_no = $Document->createElement('Product'));
                    $product_no->setAttribute('ID', $products_no);
                    $ProductData = $ProductDetails->appendChild($Document->createElement('ProductData'));
                    if ($this->debug) {
                        printf('PID: %s' . "\n", $id_product." (".$products_no.")");
                        $this->viewMem(__FUNCTION__, __LINE__);
                    }
                    //is virtual
                    if (isset($details->is_virtual)) {
                        $ProductData->appendChild($is_virtual = $Document->createElement('Virtual'));
                        $is_virtual->appendChild($Document->createCDATASection($details->is_virtual));
                    }
                    if ($this->debug) {
                        $this->viewMem(__FUNCTION__, __LINE__);
                    }
                    // Product Name
                    if (is_array($details->name) && count($details->name)) {
                        $ProductNames = $ProductData->appendChild($p_name = $Document->createElement('Names'));
                        foreach ($details->name as $product_id_lang => $product_name) {
                            if (empty($avail_lang[$product_id_lang])) {
                                continue;
                            }
                            $ProductName = $ProductNames->appendChild($p_name = $Document->createElement('Name'));
                            $name = rtrim($product_name, ' - ');
                            $ProductName->appendChild($Document->createCDATASection($name));
                            $p_name->setAttribute('lang', $product_id_lang);
                        }
                    }
                    if ($this->debug) {
                        $this->viewMem(__FUNCTION__, __LINE__);
                    }
                    // Product Description
                    if (is_array($details->description) && count($details->description)) {
                        $ProductDescriptions = $ProductData->appendChild($p_name = $Document->createElement('Descriptions'));

                        foreach ($details->description as $product_id_lang => $product_description) {
                            if (empty($avail_lang[$product_id_lang])) {
                                continue;
                            }
                            $description = $this->description($product_description);
                            $ProductDescription = $ProductDescriptions->appendChild($p_desc = $Document->createElement('Description'));
                            $ProductDescription->appendChild($Document->createCDATASection($description));
                            $p_desc->setAttribute('lang', $product_id_lang);
                        }
                    }
                    if ($this->debug) {
                        $this->viewMem(__FUNCTION__, __LINE__);
                    }

                    if (is_array($details->description_short) && count($details->description_short)) {
                        $ProductDescriptions = $ProductData->appendChild($p_name = $Document->createElement('ShortDescriptions'));

                        foreach ($details->description_short as $product_id_lang => $product_description) {
                            if (empty($avail_lang[$product_id_lang])) {
                                continue;
                            }
                            $description = $this->description($product_description);
                            $ProductDescription = $ProductDescriptions->appendChild($p_desc = $Document->createElement('ShortDescription'));
                            $ProductDescription->appendChild($Document->createCDATASection($description));
                            $p_desc->setAttribute('lang', $product_id_lang);
                        }
                    }
                    if (is_array($details->meta_title) && count($details->meta_title)) {
                        $ProductDescriptions = $ProductData->appendChild($p_name = $Document->createElement('MetaTitles'));

                        foreach ($details->meta_title as $product_id_lang => $product_description) {
                            if (empty($avail_lang[$product_id_lang])) {
                                continue;
                            }
                            $description = $this->description($product_description);
                            $ProductDescription = $ProductDescriptions->appendChild($p_desc = $Document->createElement('MetaTitle'));
                            $ProductDescription->appendChild($Document->createCDATASection($description));
                            $p_desc->setAttribute('lang', $product_id_lang);
                        }
                    }
                    if (is_array($details->meta_description) && count($details->meta_description)) {
                        $ProductDescriptions = $ProductData->appendChild($p_name = $Document->createElement('MetaDescriptions'));

                        foreach ($details->meta_description as $product_id_lang => $product_description) {
                            if (empty($avail_lang[$product_id_lang])) {
                                continue;
                            }
                            $description = $this->description($product_description);
                            $ProductDescription = $ProductDescriptions->appendChild($p_desc = $Document->createElement('MetaDescription'));
                            $ProductDescription->appendChild($Document->createCDATASection($description));
                            $p_desc->setAttribute('lang', $product_id_lang);
                        }
                    }


                    if ($this->debug) {
                        $this->viewMem(__FUNCTION__, __LINE__);
                    }
                    // url
                    $is_rewrite_active = (bool)Configuration::get('PS_REWRITING_SETTINGS');

                    $link_rewrite = Tools::getValue(
                        'link_rewrite' . ($id_lang ? '_' . $id_shop . '_' . $id_lang : ''),
                        isset($details->link_rewrite[$id_lang]) ? $details->link_rewrite[$id_lang] : ''
                    );

                    if ($this->debug) {
                        $this->viewMem(__FUNCTION__, __LINE__);
                    }
                    $category_default = Tools::getValue(
                        'id_category_default' . ($id_lang ? '_' . $id_shop . '_' . $id_lang : ''),
                        isset($details->id_category_default) ? $details->id_category_default : ''
                    );

                    if ($this->debug) {
                        $this->viewMem(__FUNCTION__, __LINE__);
                    }
                    $preview_url = $this->context->link->getProductLink(
                        $details,
                        $link_rewrite,
                        Category::getLinkRewrite($category_default, $this->context->language->id),
                        null,
                        $this->id_lang,
                        (int)Context::getContext()->shop->id,
                        0,
                        $is_rewrite_active
                    );
                    if ($this->debug) {
                        $this->viewMem(__FUNCTION__, __LINE__);
                    }
                    $ProductUrl = $ProductData->appendChild($Document->createElement('ProductLink'));
                    $ProductUrl->appendChild($Document->createCDATASection($preview_url));

                    // Tags
                    $tags = Tag::getProductTags($id_product);
                    if (isset($tags) && !empty($tags)) {
                        $ProductTags = $ProductData->appendChild($p_name = $Document->createElement('Tags'));

                        foreach ($tags as $tags_key => $tag) {
                            $tags_collection = array();
                            foreach ($tag as $tag_item) {
                                $tags_collection[] = $tag_item;
                            }

                            if ($tags_collection) {
                                $ProductTag = $ProductTags->appendChild($Document->createElement('Tag'));
                                $ProductTag->setAttribute('lang', $tags_key);
                                $ProductTag->appendChild($Document->createCDATASection(implode(', ', $tags_collection)));
                            }
                        }
                    }
                    if ($this->debug) {
                        $this->viewMem(__FUNCTION__, __LINE__);
                    }

                    // Identifier
                    $identifierElement = $ProductDetails->appendChild($Document->createElement('Identifier'));
                    $identifierElement->appendChild($i_id = $Document->createElement('Reference', (int)$id_product));
                    $i_id->setAttribute('type', 'ID');

                    $ProductSKU = $identifierElement->appendChild($id_Reference = $Document->createElement('Reference'));
                    $ProductSKU->appendChild($Document->createCDATASection($reference));
                    $id_Reference->setAttribute('type', 'Reference');

                    if (isset($ean13) && !empty($ean13)) {
                        $identifierElement->appendChild($i_ean = $Document->createElement('Code', sprintf('%013s', trim(trim($ean13)))));
                        $i_ean->setAttribute('type', 'EAN');
                    }

                    if (isset($upc) && !empty($upc)) {
                        $identifierElement->appendChild($i_upc = $Document->createElement('Code', sprintf('%012s', trim(trim($upc)))));
                        $i_upc->setAttribute('type', 'UPC');
                    }

                    // Create date
                    $identifierElement->appendChild($Document->createElement('CreateDate', date('c', strtotime($details->date_add))));

                    if ($this->debug) {
                        $this->viewMem(__FUNCTION__, __LINE__);
                    }
                    // Images Product
                    $images_product = FeedbizTools::getProductImages($details->id, 0, $id_lang);
//                    if ($images_product) {
//                        if ($this->debug) {
//                            printf('Products Images: %s' . "<br />\n", print_r($images_product, true));
//                        }
//                    }

                    if ($this->debug) {
                        $this->viewMem(__FUNCTION__, __LINE__);
                    }
                    $imagesElement = $ProductDetails->appendChild($Document->createElement('Images'));
                    $image_product_no = 1;
                    $arr_images = array(
                        ''
                    );

                    foreach ($images_product as $image_product) {
                        if (is_array($image_product)) {
                            array_push($arr_images, $image_product['id']);
                            $ProductImage = $imagesElement->appendChild($img_product = $Document->createElement('Image'));
                            $ProductImage->appendChild($Document->createCDATASection($this->ps_images . $image_product['name']));
                            $img_product->setAttribute('id', $image_product_no);

                            if (isset($image_product['default']) && ( int )$image_product['default'] == 1) {
                                $img_product->setAttribute('type', 'default');
                            } else {
                                $img_product->setAttribute('type', 'normal');
                            }

                            $file_image = _PS_PROD_IMG_DIR_ . $image_product['name'];

                            if (file_exists($file_image)) {
                                $md5_key = md5_file($file_image);

                                $img_product->setAttribute('availability', 1);
                                $img_product->setAttribute('md5', $md5_key);
                            } else {
                                $img_product->setAttribute('availability', 0);
                            }

                            $image_product_no++;
                        }
                    }

                    if ($this->debug) {
                        $this->viewMem(__FUNCTION__, __LINE__);
                    }
                    // Manufacturer
                    $ProductDetails->appendChild($id_manufacturer = $Document->createElement('Manufacturer'));
                    if (isset($details->id_manufacturer) && $details->id_manufacturer != 0) {
                        $id_manufacturer->setAttribute('ID', $details->id_manufacturer);
                    }

                    // Carrier
                    $ProductCarrier = $ProductDetails->appendChild($Document->createElement('Carriers'));
                    if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                        $product_carrier_id_reference = $details->getCarriers();
                    }

                    if ($this->debug) {
                        $this->viewMem(__FUNCTION__, __LINE__);
                    }
                    if (isset($product_carrier_id_reference) && !empty($product_carrier_id_reference)) {
                        $product_carriers = $product_carrier_id_reference;
                    } else {
                        $product_carriers = $default_carrier;
                    }

                    $on_sale = true;
                    if ($this->debug) {
                        $this->viewMem(__FUNCTION__, __LINE__);
                        var_dump(array($useTaxes, 0, 2, null, false, !$on_sale && $useSpecials,$useSpecials));
                    }
                    try {
                        if ($this->debug) {
                            var_dump($details);
                            $this->viewMem(__FUNCTION__, __LINE__);
                        }
                        $product_price = $details->getPrice($useTaxes, 0, 2, null, false, !$on_sale && $useSpecials);
//                        $product_price = Product::getPriceStatic($id_product);
                        if ($this->debug) {
                            $this->viewMem(__FUNCTION__, __LINE__);
                            var_dump($product_price);
                        }
                        $carrier_shipping_price = null;
                    } catch (Exception $e) {
                        echo 'Caught exception: ',  $e->getMessage(), "\n";
                        print_r($e);
                    }

                    if ($this->debug) {
                        $this->viewMem(__FUNCTION__, __LINE__);
                    }
                    $zone = Country::getIdZone($id_country);

                    if ($this->debug) {
                        $this->viewMem(__FUNCTION__, __LINE__, false, 'Chk carrier');
                    }
                    foreach ($product_carriers as $product_carrier) {
                        if (isset($product_carrier['id_carrier'])) {
                            if (method_exists('Carrier', 'getCarrierByReference')) {
                                $product_carrier_by_ref = Carrier::getCarrierByReference($product_carrier['id_reference']);
                            } else {
                                $product_carrier_by_ref = new Carrier($product_carrier['id_carrier']);
                            }
                            $DeliveryPriceBy = '';
                            // carrier calculation
                            if ($product_carrier_by_ref instanceof Carrier && method_exists('Carrier', 'getDeliveryPriceByWeight')) {
                                $carrier_shipping_price = $product_carrier_by_ref->getDeliveryPriceByWeight($details->weight, $zone);
                                $DeliveryPriceBy = 'Weight';
                            }
                            if (!$carrier_shipping_price && $product_carrier_by_ref instanceof Carrier && method_exists('Carrier', 'getDeliveryPriceByPrice')) {
                                $carrier_shipping_price = $product_carrier_by_ref->getDeliveryPriceByPrice($product_price, $zone);
                                $DeliveryPriceBy = 'Weight';
                            }

                            $ProductCarrierItem = $ProductCarrier->appendChild($carrier_att = $Document->createElement('Carrier'));
                            $carrier_att->setAttribute('ID', $product_carrier['id_carrier']);

                            if ($carrier_shipping_price) {
                                $CarrierZone = $ProductCarrierItem->appendChild($carrier_zone_att = $Document->createElement('Zone'));
                                $carrier_zone_att->setAttribute('ID', $zone);
                                $CarrierZone->appendChild($delivery_price_att = $Document->createElement('DeliveryPrice', $carrier_shipping_price));
                                $delivery_price_att->setAttribute('by', $DeliveryPriceBy);
                            }
                        }
                    }

                    if ($this->debug) {
                        $this->viewMem(__FUNCTION__, __LINE__);
                    }
                    $priceElement = $ProductDetails->appendChild($Document->createElement('Price'));
                    $standardPrice = $details->hasAttributes() == 0 ? $details->getPrice($useTaxes, null, 2, null, false, false) : 0;
                    $priceElement->appendChild($Document->createElement('Standard', round($standardPrice, 2)));

                    // Price
                    // Discount at product level
                    $p_sales = null;
                    if ($useSpecials && $details->hasAttributes() == 0) {
                        $specificPrice = null;
                        if (version_compare(_PS_VERSION_, '1.4.0.2', '>=')) {
                            $specificPrice = SpecificPrice::getSpecificPrice($id_product, $id_shop, $id_currency, (int)Configuration::get('PS_COUNTRY_DEFAULT'), (int)$id_customer_group, 1, 0);
                        }

                        if ($this->debug) {
                            $this->viewMem(__FUNCTION__, __LINE__);
                        }
                        if ($specificPrice && isset($specificPrice['reduction_type']) && isset($specificPrice['from']) && isset($specificPrice['to'])) {
                            $priceElement->appendChild($p_sales = $Document->createElement('Sales'));
                            $p_sales->appendChild($p_sale = $Document->createElement('Sale'));

                            $startDate = date('c', strtotime($specificPrice['from'])); // ISO 8601
                            $toDate = date('c', strtotime($specificPrice['to'])); // ISO 8601

                            if ($specificPrice['reduction_type'] == 'percentage') {
                                $saleValue = $specificPrice['reduction'] * 100;
                            } else {
                                $priceDiscount = $details->getPrice($useTaxes, null, 2, null, false, true);
                                $priceNoDiscount = $details->getPrice($useTaxes, null, 2, null, false, false);
                                $saleValue = $priceNoDiscount - $priceDiscount;
                            }

                            $p_sale->setAttribute('startDate', $startDate);
                            $p_sale->setAttribute('endDate', $toDate);
                            $p_sale->setAttribute('price', number_format($specificPrice['price'], 2));
                            $p_sale->setAttribute('value', number_format($saleValue, 2));
                            $p_sale->setAttribute('type', $specificPrice['reduction_type']);
                            $p_sale->setAttribute('CustomerGroupId', (int)$id_customer_group);
                            $p_sale->setAttribute('from_quantity', $specificPrice['from_quantity']);
                            $p_sale->setAttribute('id_currency', $specificPrice['id_currency']);
                        }

                        if ($this->debug) {
                            $this->viewMem(__FUNCTION__, __LINE__);
                        }
                        // Sales for Business Group
                        if (isset($amazon_business_groups) && is_array($amazon_business_groups)) {
                            foreach ($amazon_business_groups as $id_group) {
                                if ($id_customer_group == $id_group) {
                                    continue;
                                }

                                $specificPrice = SpecificPrice::getSpecificPrice($id_product, $id_shop, $id_currency, (int)Configuration::get('PS_COUNTRY_DEFAULT'), $id_group, 1);

                                if (is_array($specificPrice) && count($specificPrice)) {
                                    if ($p_sales == null) {
                                        $priceElement->appendChild($p_sales = $Document->createElement('Sales'));
                                    }
                                    $p_sales->appendChild($p_sale = $Document->createElement('Sale'));

                                    $startDate = date('c', strtotime($specificPrice['from'])); // ISO 8601
                                    $toDate = date('c', strtotime($specificPrice['to'])); // ISO 8601

                                    if ($specificPrice['reduction_type'] == 'percentage') {
                                        $saleValue = $specificPrice['reduction'] * 100;
                                    } else {
                                        $saleValue = $specificPrice['reduction'];
                                    }

                                    $p_sale->setAttribute('startDate', $startDate);
                                    $p_sale->setAttribute('endDate', $toDate);
                                    $p_sale->setAttribute('price', number_format($specificPrice['price'], 2));
                                    $p_sale->setAttribute('value', number_format($saleValue, 2));
                                    $p_sale->setAttribute('type', $specificPrice['reduction_type']);
                                    $p_sale->setAttribute('CustomerGroupId', (int)$id_group);
                                    $p_sale->setAttribute('from_quantity', $specificPrice['from_quantity']);
                                    $p_sale->setAttribute('id_currency', $specificPrice['id_currency']);
                                }

                                if ($this->debug) {
                                    $this->viewMem(__FUNCTION__, __LINE__);
                                }
                                $specificPrices = FeedBizProduct::getBusinessPriceRulesBreakdown($id_product, $id_shop, $id_group);

                                foreach ($specificPrices as $specificPrice) {
                                    if ($p_sales == null) {
                                        $priceElement->appendChild($p_sales = $Document->createElement('Sales'));
                                    }
                                    $p_sales->appendChild($p_sale = $Document->createElement('Sale'));

                                    $startDate = date('c', strtotime($specificPrice['from'])); // ISO 8601
                                    $toDate = date('c', strtotime($specificPrice['to'])); // ISO 8601

                                    if ($specificPrice['reduction_type'] == 'percentage') {
                                        $saleValue = $specificPrice['reduction'] * 100;
                                    } else {
                                        $saleValue = $specificPrice['reduction'];
                                    }

                                    $p_sale->setAttribute('startDate', $startDate);
                                    $p_sale->setAttribute('endDate', $toDate);
                                    $p_sale->setAttribute('price', number_format($specificPrice['price'], 2));
                                    $p_sale->setAttribute('value', number_format($saleValue, 2));
                                    $p_sale->setAttribute('type', $specificPrice['reduction_type']);
                                    $p_sale->setAttribute('CustomerGroupId', (int)$id_group);
                                    $p_sale->setAttribute('from_quantity', $specificPrice['from_quantity']);
                                    $p_sale->setAttribute('id_currency', $specificPrice['id_currency']);
                                }
                            }
                        }

                        if ($this->debug) {
                            $this->viewMem(__FUNCTION__, __LINE__);
                        }
                        // Price reduction in 1.4
                        if (property_exists($details, 'reduction_price')) {
                            if (Validate::isDate($details->reduction_from) or Validate::isDate($details->reduction_to)) {
                                $currentDate = date('Y-m-d H:m:i');
                                if ($details->reduction_from == $details->reduction_to || ($currentDate < $details->reduction_to and $currentDate > $details->reduction_from)) {
                                    if ($details->reduction_price > 0) {
                                        $priceElement->appendChild($p_sale = $Document->createElement('Sale'));
                                        $startDate = date('c', $details->reduction_from); // ISO 8601
                                        $toDate = date('c', $details->reduction_to); // ISO 8601
                                        $p_sale->setAttribute('startDate', $startDate);
                                        $p_sale->setAttribute('endDate', $toDate);
                                        $p_sale->setAttribute('value', number_format($details->reduction_price, 2));
                                        $p_sale->setAttribute('type', 'amount');
                                    }

                                    if ($details->reduction_percent > 0) {
                                        $priceElement->appendChild($p_sale = $Document->createElement('Sale'));
                                        $startDate = date('c', $details->reduction_from); // ISO 8601
                                        $toDate = date('c', $details->reduction_to); // ISO 8601
                                        $p_sale->setAttribute('startDate', $startDate);
                                        $p_sale->setAttribute('endDate', $toDate);
                                        $p_sale->setAttribute('value', number_format($details->reduction_percent, 2));
                                        $p_sale->setAttribute('type', 'percentage');
                                    }
                                }
                            }
                        }
                    }

                    if ($this->debug) {
                        $this->viewMem(__FUNCTION__, __LINE__);
                    }
                    if (property_exists('Product', 'id_tax_rules_group')) {
                        $id_tax = Db::getInstance()->getValue(
                            'SELECT `id_tax`
                                FROM `'._DB_PREFIX_.'tax_rule`
                                WHERE `id_tax_rules_group` = '.(int) $details->id_tax_rules_group.'
                                AND `id_country` = '.$id_country
                        );
                    } else {
                        $id_tax = $details->id_tax;
                    }

                    if ($useTaxes) {
                        $priceElement->appendChild($p_tax = $Document->createElement('Tax'));
                        $p_tax->setAttribute('ID', $id_tax);
                    }

                    if (isset($details->ecotax) && !empty($details->ecotax) && $details->ecotax != 0) {
                        $priceElement->appendChild($p_etax = $Document->createElement('Ecotax'));

                        $product_ecotax = $details->ecotax;
                        if (version_compare(_PS_VERSION_, '1.4', '>=')) {
                            if (method_exists('Tax', 'getProductEcotaxRate') && $useTaxes) {
                                $ecotax_rate = (float)Tax::getProductEcotaxRate();
                                $product_ecotax = $product_ecotax + (($product_ecotax * $ecotax_rate) / 100);
                            }
                        }
                        $p_etax->setAttribute('rate', $product_ecotax);
                    }

                    if ($this->debug) {
                        $this->viewMem(__FUNCTION__, __LINE__);
                    }
                    if ($useTaxes) {
                        // PS 1.4 sinon 1.3
                        if (method_exists('Tax', 'getProductTaxRate')) {
                            $tax = Tax::getProductTaxRate($id_product, (isset($this->id_address) && $this->id_address ? $this->id_address : null)); // TODO id_address
                        } else {
                            $tax = (float)(Tax::getApplicableTax($details->id_tax, $details->tax_rate, (isset($this->id_address) && $this->id_address ? $this->id_address : null))); // TODO id_address
                        }
                    } else {
                        $tax = 0;
                    }
                    if ($this->debug) {
                        $this->viewMem(__FUNCTION__, __LINE__, false, 'Chk currency');
                    }

                    $priceElement->appendChild($p_currency = $Document->createElement('Currency'));
                    $p_currency->setAttribute('ID', $id_currency);

                    if ($useTaxes) {
                        $wholesale_price = Tools::ps_round($details->wholesale_price * (1 + ($tax / 100)), 2);
                    } else {
                        $wholesale_price = Tools::ps_round(($details->wholesale_price), 2);
                    }

                    $priceElement->appendChild($Document->createElement('WholeSale', $wholesale_price));

                    // Category
                    if (version_compare(_PS_VERSION_, '1.4.2.4', '>=')) {
                        $product_categories = Product::getProductCategories($id_product);
                    } else {
                        $product_categories = array();
                        $product_categories_list = Product::getIndexedCategories($id_product);
                        foreach ($product_categories_list as $product_categories_ele) {
                            $product_categories[] = $product_categories_ele['id_category'];
                        }
                    }
                    $ProductCategories = $ProductDetails->appendChild($category_att = $Document->createElement('Categories'));

                    if ($this->debug) {
                        $this->viewMem(__FUNCTION__, __LINE__);
                    }
                    foreach ($product_categories as $product_categorie) {
                        $ProductCategories->appendChild($category_att = $Document->createElement('Category'));
                        $category_att->setAttribute('ID', $product_categorie);

                        if ($details->id_category_default == $product_categorie) {
                            $category_att->setAttribute('type', 'Default');
                        }
                    }

                    // Feature
                    $features = $details->getFeatures();

                    if (is_array($features) && !empty($features)) {
                        $ProductFeature = $ProductDetails->appendChild($Document->createElement('Features'));

                        foreach ($features as $feature) {
                            $ProductFeature->appendChild($feature_att = $Document->createElement('Feature'));
                            $feature_att->setAttribute('ID', $feature['id_feature']);
                            $feature_att->setAttribute('Option', $feature['id_feature_value']);
                        }
                    }

                    if ($this->debug) {
                        $this->viewMem(__FUNCTION__, __LINE__);
                    }
                    $sku_history[$reference] = true;

                    // Supplier
                    $ProductSupplier = array();
                    if (isset($details->id_supplier) && !empty($details->id_supplier)) {
                        $ProductSuppliers = $ProductDetails->appendChild($supplier_id = $Document->createElement('Suppliers'));
                        $ProductSupplier[$id_product] = $ProductSuppliers->appendChild($supplier_id = $Document->createElement('Supplier'));
                        $supplier_id->setAttribute('ID', $details->id_supplier);
                    }

                    // Condition
                    $ProductDetails->appendChild($condition = $Document->createElement('Condition'));
                    $condition->setAttribute('ID', array_search($details->condition, $arr_condition) + 1);
                    if ($this->debug) {
                        $this->viewMem(__FUNCTION__, __LINE__, false, 'Chk combi');
                    }
                    // Children
                    if ($details->hasAttributes() != 0) {
                        if (method_exists($details, 'getAttributeCombinations')) {
                            $combinations = $details->getAttributeCombinations($id_lang);
                        } else {
                            $combinations = $details->getAttributeCombinaisons($id_lang);
                        }

                        if (isset($combinations)) {
                            if (sizeof($combinations) > 1000) {
                                if ($this->debug) {
                                    $this->errors [] = sprintf($this->l('Skip too many combinations for product (%d/%s)').$cr, $reference, $id_product);
                                }
                                continue;
                            }

                            foreach ($combinations as $key => $combination_item) {
                                $arr_id_attribute = $combination_item['id_product_attribute'];

                                if ($this->debug) {
                                    printf('PAID: %s' . "\n", $id_product.'-'.$arr_id_attribute." (".$key.")");
                                    $this->viewMem(__FUNCTION__, __LINE__);
                                }
                                $product_combinations[$arr_id_attribute]['id'] = $combination_item['id_product_attribute'];
                                $product_combinations[$arr_id_attribute]['reference'] = $combination_item['reference'];
                                $product_combinations[$arr_id_attribute]['ean13'] = $combination_item['ean13'] == null || $combination_item['ean13'] == 'NULL' ? '' : $combination_item['ean13'];
                                $product_combinations[$arr_id_attribute]['upc'] = $combination_item['upc'] == null || $combination_item['upc'] == 'NULL' ? '' : $combination_item['upc'];
                                $product_combinations[$arr_id_attribute]['price'] = $combination_item['price'];

                                $product_combinations[$arr_id_attribute]['attribute'][$key]['attribute_id'] = $combination_item['id_attribute'];
                                $product_combinations[$arr_id_attribute]['attribute'][$key]['attribute_group_id'] = $combination_item['id_attribute_group'];

                                $image_no = 0;
                                $images = FeedbizTools::getProductImages($combination_item['id_product'], $combination_item['id_product_attribute'], $id_lang);

                                foreach ($images as $image_combination) {
                                    $product_combinations[$arr_id_attribute]['image'][$image_no]['id'] = $image_combination['id'];
                                    $image_no++;
                                }
                                if ($this->debug) {
                                    $this->viewMem(__FUNCTION__, __LINE__);
                                }
                                // Dimensions
                                if (version_compare(_PS_VERSION_, '1.4', '>=')) {
                                    $product_weight = 0;
                                    if (isset($details->width) && (float)$details->width) {
                                        $product_combinations[$arr_id_attribute]['width'] = (float)$details->width;
                                    }
                                    if (isset($details->height) && (float)$details->height) {
                                        $product_combinations[$arr_id_attribute]['height'] = (float)$details->height;
                                    }
                                    if (isset($details->depth) && (float)$details->depth) {
                                        $product_combinations[$arr_id_attribute]['depth'] = (float)$details->depth;
                                    }
                                    if (isset($details->weight) && (float)$details->weight) {
                                        $product_combinations[$arr_id_attribute]['weight'] = (float)$details->weight;
                                    }
                                    if (isset($combination_item['weight']) && (float)$combination_item['weight']) {
                                        // concern : Product weight + Atttribute weight
                                        $product_combinations[$arr_id_attribute]['weight'] = (float)$combination_item['weight'] + $product_weight;
                                    }
                                }

                                if (isset($combination_item['quantity'])) {
                                    if (!empty($stock_avail[$id_product][$arr_id_attribute]) && $stock_avail[$id_product][$arr_id_attribute]>$combination_item['quantity']) {
                                        $product_combinations[$arr_id_attribute]['stock'] = $stock_avail[$id_product][$arr_id_attribute];
                                    } else {
                                        $product_combinations[$arr_id_attribute]['stock'] = $combination_item['quantity'];
                                    }
                                }

                                if (isset($combination_item['available_date']) && $combination_item['available_date'] !== "0000-00-00") {
                                    $product_combinations[$arr_id_attribute]['available_date'] = $combination_item['available_date'];
                                }
                                if ($this->debug) {
                                    $this->viewMem(__FUNCTION__, __LINE__);
                                }
                                //sale
                                if ($useSpecials) {
                                    if (version_compare(_PS_VERSION_, '1.4.0.2', '>=') &&
                                        !isset($p_sale_history[$id_product]) ||
                                        !isset($p_sale_history[$id_product][$arr_id_attribute])
                                    ) {
                                        $p_sale_history[$id_product][$arr_id_attribute] = true;
                                        $specificPrice = null;
                                        $specificPrice = SpecificPrice::getSpecificPrice($id_product, $id_shop, $id_currency, (int)Configuration::get('PS_COUNTRY_DEFAULT'), (int)$id_customer_group, 1, $arr_id_attribute);

                                        if ($specificPrice && isset($specificPrice['reduction_type']) && isset($specificPrice['from']) && isset($specificPrice['to'])) {
                                            if ($p_sales == null) {
                                                $priceElement->appendChild($p_sales = $Document->createElement('Sales'));
                                            }
                                            $p_sales->appendChild($p_sale = $Document->createElement('Sale'));

                                            $startDate = date('c', strtotime($specificPrice['from'])); // ISO 8601
                                            $toDate = date('c', strtotime($specificPrice['to'])); // ISO 8601

                                            if ($specificPrice['reduction_type'] == 'percentage') {
                                                $saleValue = $specificPrice['reduction'] * 100;
                                            } else {
                                                $priceDiscount = $details->getPrice($useTaxes, null, 2, null, false, true);
                                                $priceNoDiscount = $details->getPrice($useTaxes, null, 2, null, false, false);
                                                $saleValue = $priceNoDiscount - $priceDiscount;
                                            }

                                            $p_sale->setAttribute('startDate', $startDate);
                                            $p_sale->setAttribute('endDate', $toDate);
                                            $p_sale->setAttribute('price', number_format($specificPrice['price'], 2));
                                            $p_sale->setAttribute('value', number_format($saleValue, 2));
                                            $p_sale->setAttribute('type', $specificPrice['reduction_type']);
                                            $p_sale->setAttribute('combination', $arr_id_attribute);
                                            $p_sale->setAttribute('CustomerGroupId', (int)$id_customer_group);
                                            $p_sale->setAttribute('from_quantity', $specificPrice['from_quantity']);
                                            $p_sale->setAttribute('id_currency', $specificPrice['id_currency']);
                                        }

                                        // Sales for Business Group
                                        if (isset($amazon_business_groups) && is_array($amazon_business_groups)) {
                                            foreach ($amazon_business_groups as $id_group) {
                                                if ($id_customer_group == $id_group) {
                                                    continue;
                                                }

                                                $specificPrice = SpecificPrice::getSpecificPrice($id_product, $id_shop, $id_currency, (int)Configuration::get('PS_COUNTRY_DEFAULT'), $id_group, 1, $arr_id_attribute, 0, 0, 1);

                                                if (is_array($specificPrice) && count($specificPrice)) {
                                                    if ($p_sales == null) {
                                                        $priceElement->appendChild($p_sales = $Document->createElement('Sales'));
                                                    }
                                                    $p_sales->appendChild($p_sale = $Document->createElement('Sale'));

                                                    $startDate = date('c', strtotime($specificPrice['from'])); // ISO 8601
                                                    $toDate = date('c', strtotime($specificPrice['to'])); // ISO 8601

                                                    if ($specificPrice['reduction_type'] == 'percentage') {
                                                        $saleValue = $specificPrice['reduction'] * 100;
                                                    } else {
                                                        $saleValue = $specificPrice['reduction'];
                                                    }

                                                    $p_sale->setAttribute('startDate', $startDate);
                                                    $p_sale->setAttribute('endDate', $toDate);
                                                    $p_sale->setAttribute('price', number_format($specificPrice['price'], 2));
                                                    $p_sale->setAttribute('value', number_format($saleValue, 2));
                                                    $p_sale->setAttribute('type', $specificPrice['reduction_type']);
                                                    $p_sale->setAttribute('CustomerGroupId', (int)$id_group);
                                                    $p_sale->setAttribute('from_quantity', $specificPrice['from_quantity']);
                                                    $p_sale->setAttribute('id_currency', $specificPrice['id_currency']);
                                                }

                                                $specificPrices = FeedBizProduct::getBusinessPriceRulesBreakdown($id_product, $id_shop, $id_group, $arr_id_attribute);

                                                foreach ($specificPrices as $specificPrice) {
                                                    if ($p_sales == null) {
                                                        $priceElement->appendChild($p_sales = $Document->createElement('Sales'));
                                                    }
                                                    $p_sales->appendChild($p_sale = $Document->createElement('Sale'));

                                                    $startDate = date('c', strtotime($specificPrice['from'])); // ISO 8601
                                                    $toDate = date('c', strtotime($specificPrice['to'])); // ISO 8601

                                                    if ($specificPrice['reduction_type'] == 'percentage') {
                                                        $saleValue = $specificPrice['reduction'] * 100;
                                                    } else {
                                                        $saleValue = $specificPrice['reduction'];
                                                    }

                                                    $p_sale->setAttribute('startDate', $startDate);
                                                    $p_sale->setAttribute('endDate', $toDate);
                                                    $p_sale->setAttribute('price', number_format($specificPrice['price'], 2));
                                                    $p_sale->setAttribute('value', number_format($saleValue, 2));
                                                    $p_sale->setAttribute('type', $specificPrice['reduction_type']);
                                                    $p_sale->setAttribute('CustomerGroupId', (int)$id_group);
                                                    $p_sale->setAttribute('from_quantity', $specificPrice['from_quantity']);
                                                    $p_sale->setAttribute('id_currency', $specificPrice['id_currency']);
                                                }
                                            }
                                        }
                                    }
                                }
                                if ($this->debug) {
                                    $this->viewMem(__FUNCTION__, __LINE__);
                                }
                                // 1.5 ($product, $alias = null, $category = null, $ean13 = null, $id_lang = null, $id_shop = null, $ipa = 0, $force_routes = false)
                                // 1.6 ($product, $alias = null, $category = null, $ean13 = null, $id_lang = null, $id_shop = null, $ipa = 0, $force_routes = false, $relative_protocol = false, $add_anchor = false)
                                if (version_compare(_PS_VERSION_, '1.6', '>=')) {
                                    $combinations_preview_url = $this->context->link->getProductLink(
                                        $details,
                                        $link_rewrite,
                                        Category::getLinkRewrite($category_default, $this->context->language->id),
                                        null,
                                        $this->id_lang,
                                        (int)Context::getContext()->shop->id,
                                        $arr_id_attribute,
                                        $is_rewrite_active,
                                        false,
                                        true
                                    );
                                } else {
                                    $combinations_preview_url = $this->context->link->getProductLink(
                                        $details,
                                        $link_rewrite,
                                        Category::getLinkRewrite($category_default, $this->context->language->id),
                                        null,
                                        $this->id_lang,
                                        (int)Context::getContext()->shop->id,
                                        $arr_id_attribute,
                                        $is_rewrite_active
                                    );
                                }

                                $product_combinations[$arr_id_attribute]['preview_url'] = $combinations_preview_url;
                            }
                        }
                        if ($this->debug) {
                            $this->viewMem(__FUNCTION__, __LINE__);
                        }
                    } else {
                        // Parent
                        $product_combinations[0]['ean13'] = $details->ean13 == null || $details->ean13 == 'NULL' ? '' : $details->ean13;
                        $product_combinations[0]['upc'] = $details->upc == null || $details->upc == 'NULL' ? '' : $details->upc;
                        $product_combinations[0]['reference'] = $details->reference == null || $details->reference == 'NULL' ? '' : $details->reference;
                    }
                    $combi_n=0;
                    foreach ($product_combinations as $id_product_attribute => $combination) {
                        // Reference validation
                        $combi_n++;
                        $reference_history[] = $combination['reference'];

                        // sku_manufacturer
                        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                            $supplier_reference = ProductSupplier::getProductSupplierReference($id_product, $id_product_attribute, $details->id_supplier);
                        } else {
                            $supplier_reference = $details->supplier_reference;
                        }

                        if (isset($supplier_reference) && !empty($supplier_reference)) {
                            if (version_compare(_PS_VERSION_, '1.5.0.2', '>=')) {
                                $supplier_reference_price = ProductSupplier::getProductSupplierPrice($id_product, $id_product_attribute, $details->id_supplier, true);
                            } else {
                                $supplier_reference_price = null;
                            }

                            if (isset($ProductSupplier[$id_product])) {
                                $ProductSupplierReference = $ProductSupplier[$id_product]->appendChild($supplier_ref = $Document->createElement('Reference'));
                                $ProductSupplierReference->appendChild($Document->createCDATASection($supplier_reference));

                                if (isset($supplier_reference_price)) {
                                    $supplier_ref->setAttribute('price', round($supplier_reference_price['product_supplier_price_te'], 2));
                                    $supplier_ref->setAttribute('currency', $supplier_reference_price['id_currency']);
                                }
                            }
                        }
                        if ($this->debug) {
                            $this->viewMem(__FUNCTION__, __LINE__);
                        }
                        $combination_id = isset($combination['id']) ? $combination['id'] : null;

                        if ((bool)Configuration::get('PS_STOCK_MANAGEMENT')) {
                            if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                                $quantity = Product::getRealQuantity($details->id, $id_product_attribute ? $id_product_attribute : null, $id_warehouse, $id_shop);
                            } else {
                                $quantity = Product::getQuantity($details->id, $id_product_attribute ? $id_product_attribute : null);
                            }
                        } else {
                            $quantity = 100;
                        }

                        // Combination
                        $ProductCombination = array();
                        if (isset($combination['id']) && !empty($combination['id'])) {
                            $idx++;

                            $ProductCombination[$id_product] = isset($ProductCombination[$id_product]) ? $ProductCombination[$id_product] : $ProductDetails->appendChild($Document->createElement('Combinations'));

                            $ProductCombinationID = $ProductCombination[$id_product]->appendChild($id_com = $Document->createElement('Combination'));
                            $id_com->setAttribute('id', ($idx));

                            $ProductCombinationIdentifier = $ProductCombinationID->appendChild($id_com = $Document->createElement('Identifier'));
                            $ProductCombinationIdentifier->appendChild($id_com_Ref = $Document->createElement('Reference', $combination['id']));
                            $id_com_Ref->setAttribute('type', 'ID');

                            if (isset($combination['reference']) && !empty($combination['reference'])) {
                                $ProductCombinationSKU = $ProductCombinationIdentifier->appendChild($id_com_Reference = $Document->createElement('Reference'));
                                $ProductCombinationSKU->appendChild($Document->createCDATASection($combination['reference']));
                                $id_com_Reference->setAttribute('type', 'Reference');
                            }

                            if (isset($combination['ean13']) && !empty($combination['ean13'])) {
                                $ProductCombinationIdentifier->appendChild($ean13_com_Ref = $Document->createElement('Code', sprintf('%013s', trim($combination['ean13']))));
                                $ean13_com_Ref->setAttribute('type', 'EAN');
                            }

                            if (isset($combination['upc']) && !empty($combination['upc'])) {
                                $ProductCombinationIdentifier->appendChild($upc_com_Ref = $Document->createElement('Code', sprintf('%012s', trim($combination['upc']))));
                                $upc_com_Ref->setAttribute('type', 'UPC');
                            }

                            $ProductCombinationAttributes = $ProductCombinationID->appendChild($id_com = $Document->createElement('Attributes'));

                            foreach ($combination['attribute'] as $combination_attribute) {
                                $ProductCombinationAttributes->appendChild($attribute_name = $Document->createElement('Attribute'));
                                $attribute_name->setAttribute('ID', $combination_attribute['attribute_group_id']);
                                $attribute_name->setAttribute('Option', $combination_attribute['attribute_id']);
                            }
                            if ($this->debug) {
                                $this->viewMem(__FUNCTION__, __LINE__);
                            }
                            // Images
                            if (isset($combination['image']) && !empty($combination['image'])) {
                                $ProductCombinationImage = $ProductCombinationID->appendChild($Document->createElement('Images'));

                                foreach ($combination['image'] as $comb_img) {
                                    if (isset($comb_img)) {
                                        $ProductCombinationImage->appendChild($img = $Document->createElement('Image'));
                                        $img->setAttribute('id', array_search($comb_img['id'], $arr_images));
                                    }
                                }
                            }

                            $combination_price = $details->getPrice($useTaxes, $id_product_attribute, 2, null, false, false);
                            $combination_striked_price = $details->getPrice($useTaxes, $id_product_attribute, 2, null, false, $useSpecials);

                            $ProductCombinationPriceOverride = $ProductCombinationID->appendChild($Document->createElement('PriceOverride'));
                            $ProductCombinationPriceOverride->appendChild($price_comb = $Document->createElement('Operation', round($combination_price - $standardPrice, 2)));
                            $ProductCombinationPriceOverride->appendChild($Document->createElement('Price', round($combination_price, 2)));
                            $ProductCombinationPriceOverride->appendChild($Document->createElement('Striked', round($combination_striked_price, 2)));

                            if (isset($combination['wholesale_price'])) {
                                if ($useTaxes) {
                                    $wholesale_price = Tools::ps_round($combination['wholesale_price'] * (1 + ($tax / 100)), 2);
                                } else {
                                    $wholesale_price = Tools::ps_round(($combination['wholesale_price']), 2);
                                }
                            }
                            if ($this->debug) {
                                $this->viewMem(__FUNCTION__, __LINE__);
                            }
                            $ProductCombinationPriceOverride->appendChild($Document->createElement('WholeSale', round($wholesale_price, 2)));

                            if ($combination['price'] < 0) {
                                $price_comb->setAttribute('type', 'Reduction');
                                $price_comb->setAttribute('value', '-1');
                            } elseif (!$combination['price']) {
                                $price_comb->setAttribute('type', 'None');
                                $price_comb->setAttribute('value', '0');
                            } elseif ($combination['price'] > 0) {
                                $price_comb->setAttribute('type', 'Increase');
                                $price_comb->setAttribute('value', '1');
                            }

                            $ProductCombinationTopology = $ProductCombinationID->appendChild($Document->createElement('Topology'));
                            if ($this->debug) {
                                echo 'Topology11 '.__LINE__;
                                print_r($combination);
                                var_dump($combination);
//                                    $tmm=true;
                            }
                            if (isset($combination['width'])) {
                                $ProductCombinationTopology->appendChild($width_unit = $Document->createElement('Unit', $combination['width']));
                                $width_unit->setAttribute('ID', '4');
                                $width_unit->setAttribute('Name', 'Width');
                            }
                            if (isset($combination['height'])) {
                                $ProductCombinationTopology->appendChild($height_unit = $Document->createElement('Unit', $combination['height']));
                                $height_unit->setAttribute('ID', '3');
                                $height_unit->setAttribute('Name', 'Height');
                            }
                            if (isset($combination['depth'])) {
                                $ProductCombinationTopology->appendChild($depth_unit = $Document->createElement('Unit', $combination['depth']));
                                $depth_unit->setAttribute('ID', '2');
                                $depth_unit->setAttribute('Name', 'Depth');
                            }
                            if (isset($combination['weight'])) {
                                $ProductCombinationTopology->appendChild($weight_unit = $Document->createElement('Unit', $combination['weight']));
                                $weight_unit->setAttribute('ID', '1');
                                $weight_unit->setAttribute('Name', 'Weight');
                            }
                            if ($this->debug) {
                                echo 'Topology21 '.__LINE__;
                                print_r($ProductCombinationTopology);
//                                    $tmm=true;
                            }
                            $q = $quantity;
                            if (!empty($stock_avail[$id_product][$id_product_attribute]) && $stock_avail[$id_product][$id_product_attribute]>$quantity) {
                                $q = $stock_avail[$id_product][$id_product_attribute] ;
                            }

                            $ProductCombinationAvailabilities = $ProductCombinationID->appendChild($Document->createElement('Availabilities'));
                            $ProductCombinationAvailabilities->appendChild($Document->createElement('Stock', $q));
                            $ProductCombinationAvailabilities->appendChild($Document->createElement('Active', $details->active));

                            if (isset($combination['available_date'])) {
                                $ProductCombinationAvailabilities->appendChild($Document->createElement('Date', date('c', strtotime($combination['available_date']))));
                            }

                            // Combination Link
                            if (isset($combination['preview_url'])) {
                                $ProductCombinationLink = $combination['preview_url'];
                                $ProductCombinationUrl = $ProductCombinationID->appendChild($Document->createElement('ProductCombinationLink'));
                                $ProductCombinationUrl->appendChild($Document->createCDATASection($ProductCombinationLink));
                            }

                            $combination_history[$combination_id] = true;
                        } else {
                            // Topology

                            if ($this->debug) {
                                echo 'Topology21 '.__LINE__;
                                print_r($details);
                                var_dump($details);
//                                    $tmm=true;
                            }
                            $ProductCombinationTopology = $ProductDetails->appendChild($Document->createElement('Topology'));

                            if (isset($details->width) && (float)$details->width) {
                                $ProductCombinationTopology->appendChild($width_unit = $Document->createElement('Unit', $details->width));
                                $width_unit->setAttribute('ID', '4');
                                $width_unit->setAttribute('Name', 'Width');
                            }
                            if (isset($details->height) && (float)$details->height) {
                                $ProductCombinationTopology->appendChild($height_unit = $Document->createElement('Unit', $details->height));
                                $height_unit->setAttribute('ID', '3');
                                $height_unit->setAttribute('Name', 'Height');
                            }
                            if (isset($details->depth) && (float)$details->depth) {
                                $ProductCombinationTopology->appendChild($depth_unit = $Document->createElement('Unit', $details->depth));
                                $depth_unit->setAttribute('ID', '2');
                                $depth_unit->setAttribute('Name', 'Depth');
                            }
                            if (isset($details->weight) && (float)$details->weight) {
                                $ProductCombinationTopology->appendChild($weight_unit = $Document->createElement('Unit', $details->weight));
                                $weight_unit->setAttribute('ID', '1');
                                $weight_unit->setAttribute('Name', 'Weight');
                            }
                            if ($this->debug) {
                                echo 'Topology22 '.__LINE__;
                                print_r($ProductCombinationTopology);
                            }

                            $q = $quantity;
                            if (!empty($stock_avail[$id_product][0]) && $stock_avail[$id_product][0]>$quantity) {
                                $q = $stock_avail[$id_product][0] ;
                            }
                            // Availabilities
                            $ProductAvailabilities = $ProductDetails->appendChild($Document->createElement('Availabilities'));
                            $ProductAvailabilities->appendChild($Document->createElement('Stock', $q));
                            $ProductAvailabilities->appendChild($Document->createElement('Active', $details->active));

                            if (isset($details->available_date) && ($details->available_date != '0000-00-00')) {
                                $ProductAvailabilities->appendChild($Document->createElement('Date', date('c', strtotime($details->available_date))));
                            }
                        }

                        if ($this->debug) {
                            $this->viewMem(__FUNCTION__, __LINE__, false, 'End combi '.$combination['reference'].' '.$combi_n.'/'.sizeof($product_combinations));
                        }
                    } // end Combination
                    if ($this->debug) {
                        $this->viewMem(__FUNCTION__, __LINE__, false, 'End prod '.$id_product);
                    }
                    if ($limit == $products_no) {
                        break 1;
                    } // check limit
                } // end foreach products
                // CONTEXT UPDATE
                $currentTimeStamp = new DateTime();
                $this->exportContext->timestamp = $currentTimeStamp->format('Y-m-d H:i:s');
                $this->exportContext->currentProduct = $id_product;
                $this->exportContext->currentPage = $this->exportContext->currentPage + 1;
                $this->exportContext->status = $products_determine_no == sizeof($products) ? FeedBizExportContext::STATUS_COMPLETE : FeedBizExportContext::STATUS_INCOMPLETE;
                FeedBizExportContext::save(FeedBizExportContext::CONF_FEEDBIZ_PRODUCTS_EXPORT_CONTEXT, $this->exportContext);

                if ($this->debug) {
                    echo 'Save context<pre>' . print_r($this->exportContext, true) . '</pre>';
                    $this->exportContextRes = new FeedBizExportContext();
                    FeedBizExportContext::restore($this->exportContextRes, FeedBizExportContext::CONF_FEEDBIZ_PRODUCTS_EXPORT_CONTEXT);
                    echo 'Restore context<pre>' . print_r($this->exportContextRes, true) . '</pre>';
                }
            } // end if product
        } // end if
        $ExportData->appendChild($statusDoc = $Document->createElement('Status', ''));
        $statusDoc->appendChild($Document->createElement('Code', $this->exportContext->status == FeedBizExportContext::STATUS_INCOMPLETE ? '-1' : '1'));
        $statusDoc->appendChild($Document->createElement('Message', $this->exportContext->status));
        $statusDoc->appendChild($Document->createElement('ExportTotal', $products_no));
        $statusDoc->appendChild($Document->createElement('CurrentPage', $this->exportContext->currentPage));
        $statusDoc->appendChild($Document->createElement('CurrentProductID', $this->exportContext->currentProduct));
        $statusDoc->appendChild($Document->createElement('MinProductID', $this->exportContext->minProduct));
        $statusDoc->appendChild($Document->createElement('MaxProductID', $this->exportContext->maxProduct));

        if ($this->debug) {
            print_r($this->errors);
            echo '<br/><br/><br/>Loaded:';
            print_r($loadedProducts_history);
            echo '<br/><br/><br/>Loaded context end:';
            print_r($this->exportContext);
            $this->exportContext2 = new FeedBizExportContext();
            FeedBizExportContext::restore($this->exportContext2, FeedBizExportContext::CONF_FEEDBIZ_PRODUCTS_EXPORT_CONTEXT);
            print_r($this->exportContext2);
        } else {
            ob_end_clean();
            header("Content-Type: application/xml; charset=utf-8");
            echo $Document->saveXML();
        }
    }

    /**
     * @param $html
     *
     * @return string
     */
    public function description($html)
    {
        $text = $html;

        $text = str_replace('</p>', "\n</p>", $text);
        $text = str_replace('</li>', "\n</li>", $text);
        $text = str_replace('<br', "\n<br", $text);

        $text = str_replace('&#39;', "'", $text);
        $text = str_replace('"', "'", $text);

        $text = mb_convert_encoding($text, 'HTML-ENTITIES');
        $text = str_replace('&nbsp;', ' ', $text);
        $text = html_entity_decode($text, ENT_NOQUOTES, 'UTF-8');
        $text = str_replace('&', '&amp;', $text);

        $text = preg_replace('#\s+[\n|\r]+$#i', '', $text); // empty
        $text = preg_replace('#[\n|\r]+#i', "\n", $text); // multiple-return
        $text = preg_replace('#\n+#i', "\n", $text); // multiple-return
        $text = preg_replace('#^[\n\r\s]#i', '', $text);

        $text = preg_replace('/[\x{0001}-\x{0009}]/u', '', $text);
        $text = preg_replace('/[\x{000b}-\x{001f}]/u', '', $text);
        $text = preg_replace('/[\x{0080}-\x{009F}]/u', '', $text);
//        $text = preg_replace('/[\x{0600}-\x{FFFF}]/u', '', $text);

        $text = preg_replace('/\x{000a}/', "\n", $text);
        return htmlspecialchars(trim($text));
    }
}

$FeedBizProductsCreate = new FeedBizExportProducts();
$FeedBizProductsCreate->dispatch();

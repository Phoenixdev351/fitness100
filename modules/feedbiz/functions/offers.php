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

require_once(dirname(__FILE__).'/env.php');
require_once(dirname(__FILE__).'/../feedbiz.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.tools.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.webservice.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.context.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.exportcontext.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.product.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.tax.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.product_tab.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.product_tab.amazon.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.product_tab.ebay.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.product_tab.cdiscount.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.product_tab.fnac.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.product_tab.mirakl.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.product_tab.rakuten.class.php');
/**
 * Class FeedBizExportOffers
 */
class FeedBizExportOffers extends Feedbiz
{
    /**
     * @var array
     */
    private $errors = array();
    /**
     * @var string
     */
    private $_cr = "\n";

    /**
     * @var
     */
    private $exportContext;
    /**
     * @var bool
     */
    private $enableValidation = false;

    /**
     * @var array
     */
    private static $exceptionRegionLabel = array('Main' => 'lang');

    /**
     * @var null|string
     */
    private $ps_images = null;

    /**
     * FeedBizExportOffers constructor.
     */
    public function __construct()
    {
        parent::__construct();

        FeedbizContext::restore($this->context);

        $this->ps_images = 'http://'.htmlspecialchars($_SERVER ['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'img/p/';

        ob_start();

        if (Feedbiz::$debug_mode) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
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
            $this->context->customer->id_default_group = (int)Configuration::get('FEEDBIZ_CUSTOMER_GROUP');

            $this->context->cart = new Cart();
            $this->context->employee = $employee;
            $this->context->currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        }
    }

    /**
     *
     */
    public function dispatch()
    {
        $this->offerExport();
    }

    /**
     * @param $offers
     */
    private function createOffers($offers)
    {
        $cr = $this->_cr;

        if (Feedbiz::$debug_mode) {
            echo nl2br(print_r($offers, true)).$cr;
        }

        $Document = new DOMDocument();
        $Document->preserveWhiteSpace = true;
        $Document->formatOutput = true;

        $Document->encoding = 'utf-8';
        $Document->version = '1.0';

        $OfferPackage = $Document->appendChild($Document->createElement('OfferPackage'));

        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $OfferPackage->setAttribute('ShopName', Configuration::get('PS_SHOP_NAME', null, null, $this->context->shop->id));
        }

        $id_currency = (int)Configuration::get('PS_CURRENCY_DEFAULT');
        $Offers = $OfferPackage->appendChild($Document->createElement('Offers'));
        $curr_id = $Offers->appendChild($Document->createElement('Currency'));
        $curr_id->setAttribute('ID', $id_currency);

        if (isset($offers ['Offers'])) {
            foreach ($offers ['Offers'] as $Product) {
                $Offer = $Offers->appendChild($Document->createElement('Offer'));
                ksort($Product);

                foreach ($Product as $attr => $value) {
                    if ($attr == 'Options') {
                        $OffersOptions = $Offer->appendChild($Document->createElement('OffersOptions'));

                        if (is_array($value) && count($value)) {
                            foreach ($value as $marketplace => $options) {
                                if (is_array($options) && count($options) && !empty($options)) {
                                    $Main = $OffersOptions->appendChild($Offers->appendChild($Document->createElement(Tools::ucfirst($marketplace))));
                                    // Main
                                    if ($marketplace == 'Main') {
                                        $Option = $Document->createElement('Option');
                                        $OffersOptions->appendChild($Main);
                                        $Main->appendChild($Option);

                                        if (is_array($options) && count($options) && !empty($options)) {
                                            foreach ($options as $field => $detail) {
                                                $OfferOption = $OffersOptions->appendChild($Document->createElement(Tools::ucfirst($field)));
                                                $OfferOption->appendChild($Document->createCDATASection($detail));
                                                $Option->appendChild($OfferOption);
                                            }
                                        }
                                        // Marketplace
                                    } else {
                                        foreach ($options as $lang => $option_detail) {
                                            $Option = $Document->createElement('Option');
                                            $Option->setAttribute(isset(self::$exceptionRegionLabel[$marketplace]) ? self::$exceptionRegionLabel[$marketplace] : 'region', $lang);
                                            $OffersOptions->appendChild($Main);
                                            $Main->appendChild($Option);

                                            if (is_array($option_detail) && count($option_detail) && !empty($option_detail)) {
                                                foreach ($option_detail as $field => $detail) {
                                                    $OfferOption = $OffersOptions->appendChild($Document->createElement(Tools::ucfirst($field)));
                                                    $OfferOption->appendChild($Document->createCDATASection($detail));
                                                    $Option->appendChild($OfferOption);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } elseif ($attr == 'items') {
                        $items = $Offer->appendChild($Document->createElement('Items'));

                        foreach ($value as $items_value) {
                            $items->appendChild($item = $Document->createElement('Item'));

                            foreach ($items_value as $item_attr => $item_value) {
                                if ($item_attr == "Options") {
                                    $ItemOffersOptions = $item->appendChild($Document->createElement('OffersOptions'));

                                    foreach ($item_value as $item_marketplace => $item_options) {
                                        $ItemMain = $ItemOffersOptions->appendChild($Offer->appendChild($Document->createElement(Tools::ucfirst($item_marketplace))));
                                        // Main
                                        if ($item_marketplace == 'Main') {
                                            $ItemOption = $Document->createElement('Option');
                                            $ItemOffersOptions->appendChild($ItemMain);
                                            $ItemMain->appendChild($ItemOption);

                                            foreach ($item_options as $item_field => $item_option_value) {
                                                $ItemOfferOption = $ItemMain->appendChild($Document->createElement(Tools::ucfirst($item_field)));
                                                $ItemOfferOption->appendChild($Document->createCDATASection($item_option_value));
                                                $ItemOption->appendChild($ItemOfferOption);
                                            }
                                            // Marketplace
                                        } else {
                                            foreach ($item_options as $item_lang => $item_option) {
                                                $ItemOption = $Document->createElement('Option');
                                                $ItemOption->setAttribute(isset(self::$exceptionRegionLabel[$item_marketplace]) ? self::$exceptionRegionLabel[$item_marketplace] : 'region', $item_lang);
                                                $ItemOffersOptions->appendChild($ItemMain);
                                                $ItemMain->appendChild($ItemOption);

                                                foreach ($item_option as $item_field => $item_option_value) {
                                                    $ItemOfferOption = $ItemMain->appendChild($Document->createElement(Tools::ucfirst($item_field)));
                                                    $ItemOfferOption->appendChild($Document->createCDATASection($item_option_value));
                                                    $ItemOption->appendChild($ItemOfferOption);
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    $item->setAttribute($item_attr, $item_value);
                                }
                            }
                        }
                    } elseif ($attr == 'Discount') {
                        $discountItems = $value;
                        $DiscountList = $Offer->appendChild($Document->createElement('DiscountList'));
                        foreach ($discountItems as $discount_Item) {
                            foreach ($discount_Item as $discountItem) {
                                $Discount = $DiscountList->appendChild($Document->createElement('Discount'));

                                $Discount->setAttribute('DiscountValue', $discountItem ['value']);
                                $Discount->setAttribute('DiscountType', $discountItem ['type']);
                                $Discount->setAttribute('Price', $discountItem ['price']);
                                $Discount->setAttribute('CustomerGroupId', $discountItem ['id_group']);
                                $Discount->setAttribute('StartDate', $discountItem ['dateStart']);
                                $Discount->setAttribute('EndDate', $discountItem ['dateEnd']);

                                if ($discountItem ['id_currency'] > 0) {
                                    $Discount->setAttribute('id_currency', $discountItem ['id_currency']);
                                }

                                if ($discountItem ['from_quantity'] > 0) {
                                    $Discount->setAttribute('from_quantity', $discountItem ['from_quantity']);
                                }

                                if ($discountItem ['combination'] > 0) {
                                    $Discount->setAttribute('Combination', $discountItem ['combination']);
                                }
                            }
                        }
                    } else { // Standard Offer
                        $Offer->setAttribute($attr, $value);
                    }
                }
            }
        }

        if (Feedbiz::$debug_mode) {
            echo $cr;
            echo "Memory: ".number_format(memory_get_usage() / 1024).'k'.$cr;
            echo $cr;
            print_r($this->errors);
        } else {
            $OfferPackage->appendChild($statusDoc = $Document->createElement('Status', ''));
            $statusDoc->appendChild($Document->createElement('Code', $this->exportContext->status == FeedBizExportContext::STATUS_INCOMPLETE ? '-1' : '1'));
            $statusDoc->appendChild($Document->createElement('Message', $this->exportContext->status));
            $statusDoc->appendChild($Document->createElement('ExportTotal', isset($offers ['Offers']) ? count($offers ['Offers']) : 0));
            $statusDoc->appendChild($Document->createElement('CurrentPage', $this->exportContext->currentPage));
            $statusDoc->appendChild($Document->createElement('CurrentProductID', $this->exportContext->currentProduct));
            $statusDoc->appendChild($Document->createElement('MinProductID', $this->exportContext->minProduct));
            $statusDoc->appendChild($Document->createElement('MaxProductID', $this->exportContext->maxProduct));

            ob_end_clean();
            header("Content-Type: application/xml; charset=utf-8");
            echo $Document->saveXML();
            exit();
        }
    }

    /**
     *
     */
    private function offerExport()
    {
        FeedbizTools::securityCheck();

        $error = false;
        $history = array();
        $reference_history = array();
        $id_lang = $this->id_lang;

        $toFeedBiz = array();
        $id_currency = (int)Configuration::get('PS_CURRENCY_DEFAULT');
        $id_warehouse = (int)Configuration::get('FEEDBIZ_WAREHOUSE');

        // Configuration customer Group or default customer group

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

        $this->exportContext = new FeedBizExportContext();

        $cr = $this->_cr;

        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $id_shop = $this->context->shop->id;
        } else {
            $id_shop = 1;
        }

        // limit per page
        $limit = Feedbiz::DEFAULT_OFFERS_LIMIT;
        if (Configuration::get('FEEDBIZ_EXPORT_LIMIT_PER_PAGE') && Configuration::get('FEEDBIZ_EXPORT_LIMIT_PER_PAGE') > 0) {
            $limit = Configuration::get('FEEDBIZ_EXPORT_LIMIT_PER_PAGE');
        }

        // load all products
        $allOffers = false;
        if (Configuration::get('FEEDBIZ_ALL_OFFERS') || Tools::getValue('force')) {
            $allOffers = (int)Configuration::get('FEEDBIZ_ALL_OFFERS');
        }

        $create_active = false;
        $create_in_stock = false;

        // Categories Settings
        $default_categories = array();
        $feedbiz_categories = FeedbizConfiguration::get('FEEDBIZ_CATEGORIES');

        if (method_exists('Category', 'getCategoryInformations') && $feedbiz_categories!='all') {
            $categories = Category::getCategoryInformations($feedbiz_categories, $id_lang);
        } else {
            $categories = array();
            $categories_full = Category::getCategories($id_lang);
            foreach ($categories_full as $categories_relation) {
                foreach ($categories_relation as $categories_info) {
                    if (in_array($categories_info['infos']['id_category'], $feedbiz_categories) || $feedbiz_categories=='all') {
                        $categories[] = $categories_info['infos'];
                    }
                }
            }
        }

        if (is_array($categories)) {
            foreach ($categories as $category) {
                $default_categories[] = $category['id_category'];
            }
        }

        if (!is_array($default_categories) || !count($default_categories) || !max($default_categories)) {
            $this->errors [] = sprintf('%s(%d):', basename(__FILE__), __LINE__, $this->l('You must configure the categories')).$cr;
            $error = true;
        }

        if (Feedbiz::$debug_mode) {
            echo "<pre>\n";
            echo 'Config categories:'.nl2br(print_r($feedbiz_categories, true));
            echo 'Categories:'.nl2br(print_r($default_categories, true));
            echo "</pre>\n";
        }

        // Options
        $marketplace_tab = Configuration::get('FEEDBIZ_MARKETPLACE_TAB');

        $ebay_products_options = false;
        $amazon_products_options = false;
        $cdiscount_products_options = false;
        $fnac_products_options = false;
        $mirakl_products_options = false;
        $rakuten_products_options = false;
        $amazon_tabs = array();
        $ebay_tabs = array();
        $cdiscount_tabs = array();
        $fnac_tabs = array();
//        $mirakl_tabs = array();
        $rakuten_tabs = array();

        if ($marketplace_tab) {
            $marketplace_tab_config = unserialize($marketplace_tab);
            if (is_array($marketplace_tab_config) && count($marketplace_tab_config)) {
                if (!$this->branded_module) {
                    if (array_key_exists('amazon', $marketplace_tab_config) && Tools::strlen($marketplace_tab_config['amazon'])) {
                        $amazon_products_options = true;
                        $amazon_tabs = explode(';', $marketplace_tab_config['amazon']);
                    }
                    if (array_key_exists('ebay', $marketplace_tab_config) && Tools::strlen($marketplace_tab_config['ebay'])) {
                        $ebay_products_options = true;
                        $ebay_tabs = explode(';', $marketplace_tab_config['ebay']);
                    }
                    if (array_key_exists('fnac', $marketplace_tab_config) && Tools::strlen($marketplace_tab_config['fnac'])) {
                        $fnac_products_options = true;
                        $fnac_tabs = explode(';', $marketplace_tab_config['fnac']);
                    }
                    if (array_key_exists('mirakl', $marketplace_tab_config) && Tools::strlen($marketplace_tab_config['mirakl'])) {
                        $mirakl_products_options = true;
//                        $mirakl_tabs = explode(';', $marketplace_tab_config['mirakl']);
                    }
                    if ((array_key_exists('rakuten', $marketplace_tab_config) && Tools::strlen($marketplace_tab_config['rakuten'])) ||
                        (array_key_exists('priceminister', $marketplace_tab_config) && Tools::strlen($marketplace_tab_config['priceminister']))) {
                        $rakuten_products_options = true;
                        $rakuten_tabs = explode(';', $marketplace_tab_config['rakuten']);
                    }
                }
                if (array_key_exists('cdiscount', $marketplace_tab_config) && Tools::strlen($marketplace_tab_config['cdiscount'])) {
                    $cdiscount_products_options = true;
                    $cdiscount_tabs = explode(';', $marketplace_tab_config['cdiscount']);
                }
            }
        }

        // Prices Parameters
        $useTaxes = (bool)Configuration::get('FEEDBIZ_USE_TAXES');
        $useSpecials = (bool)Configuration::get('FEEDBIZ_USE_SPECIALS');

        // Delivery Delays
        $MinDeliveryTime = 1;

        // Exclusions
        $excluded_manufacturers = unserialize(FeedbizConfiguration::get('FEEDBIZ_FILTER_MANUFACTURERS'));
        $excluded_suppliers = unserialize(FeedbizConfiguration::get('FEEDBIZ_FILTER_SUPPLIERS'));

        // Carrier Configuration
        $selected = Configuration::get('FEEDBIZ_CARRIER');


        // Taxes
        $tax = null;
        $taxes_arr = Tax::getTaxes($id_lang, true);
        foreach ($taxes_arr as $taxes_value) {
            $toFeedBiz ['Taxs'] [$taxes_value ['id_tax']] = $taxes_value ['rate'];
        }


        if (Feedbiz::$debug_mode) {
            echo "<pre>\n";
            echo 'Taxes:'.nl2br(print_r($toFeedBiz['Taxs'], true));
            echo "</pre>\n";
        }

        $conditions = FeedBizProduct::getConditionField();

        $arr_condition = array();
        preg_match_all("/'([\w ]*)'/", $conditions ['Type'], $arr_condition);
        $arr_condition = $arr_condition [1];

        if (Feedbiz::$debug_mode) {
            echo "<pre>\n";
            echo 'Conditions:'.nl2br(print_r($arr_condition, true));
            echo "</pre>\n";
        }

        // Export Loop
        if (!$error) {
            // CONTEXT CHECKING
            $products_determine_no = 0;
            $products_no = 0;

            $this->exportContext = new FeedBizExportContext();
            FeedBizExportContext::restore($this->exportContext, FeedBizExportContext::CONF_FEEDBIZ_OFFERS_EXPORT_CONTEXT);

            if ($this->exportContext->status == FeedBizExportContext::STATUS_COMPLETE) {
                $this->exportContext->status = FeedBizExportContext::STATUS_INCOMPLETE;
                $this->exportContext->currentProduct = 0;
                $this->exportContext->currentPage = 0;
            }

            $date_from = $allOffers ? '1997-01-01' : date('Y-m-d', strtotime('yesterday'));
            $products_max_min = FeedBizProduct::getUpdateProductsMaxMin($default_categories, $create_active, $create_in_stock, $date_from, null, null, Feedbiz::$debug_mode, 0);
            $products = FeedBizProduct::getUpdateProducts($default_categories, $create_active, $create_in_stock, $date_from, null, null, Feedbiz::$debug_mode, $this->exportContext->currentProduct);

            foreach ($products_max_min as $products_max_min_ele) {
                $this->exportContext->maxProduct = $products_max_min_ele ['max_id_product'];
                $this->exportContext->minProduct = $products_max_min_ele ['min_id_product'];
            }
            if (!count($products)) {
                if (Feedbiz::$debug_mode) {
                    nl2br(print_r($products, true));
                }
            }

            if ($products) {
                $stock_avail = array();
                foreach ($products as $p) {
                    if (!empty($p['quantity']) && !empty($p['id_product'])) {
                        $stock_avail[(int)$p['id_product']][(int)$p['id_product_attribute']] = $p['quantity'];
                    }
                }
                foreach ($products as $product) {
                    $products_determine_no++;
                    $id_product = $product ['id_product'];
                    $date_upd = $product ['date_upd'];

                    $details = new Product($id_product);
                    if (method_exists($details, 'loadStockData')) {
                        $details->loadStockData();
                    }

                    if (!Validate::isLoadedObject($details)) {
                        $this->errors [] = sprintf($this->l('Could not load the product id: %d'), $id_product);
                        continue;
                    }

                    // Filtering Manufacturer & Supplier
                    if ($details->id_manufacturer) {
                        if (is_array($excluded_manufacturers) && in_array($details->id_manufacturer, $excluded_manufacturers)) {
                            if (Feedbiz::$debug_mode) {
                                $this->errors [] = sprintf($this->l('Exclude manufacturer product id: %d'), $id_product);
                            }
                            continue;
                        }
                    }

                    if ($details->id_supplier) {
                        if (is_array($excluded_suppliers) && in_array($details->id_supplier, $excluded_suppliers)) {
                            if (Feedbiz::$debug_mode) {
                                $this->errors [] = sprintf($this->l('Exclude supplier product id: %d'), $id_product);
                            }
                            continue;
                        }
                    }

                    // Product Combinations
                    if (version_compare(_PS_VERSION_, '1.5', '<')) {
                        $combinations = $details->getAttributeCombinaisons($id_lang);
                    } else {
                        $combinations = $details->getAttributeCombinations($id_lang);
                    }

                    // Pas de combinaison, on en cr?e une fictive pour rentrer dans la boucle
                    if (!is_array($combinations) or empty($combinations)) {
                        $combinations = array(
                            0 => array(
                                'reference' => $details->reference,
                                'ecotax' => $details->ecotax,
                                'quantity' => $details->quantity,
                                'ean13' => $details->ean13,
                                'upc' => $details->upc,
                                'id_product_attribute' => 0
                            )
                        );
                    }

                    // Grouping Combinations
                    asort($combinations);
                    $group_details = array();

                    if (isset($combinations)) {
                        if (sizeof($combinations) > 1000) {
                            if (Feedbiz::$debug_mode) {
                                $this->errors [] = sprintf($this->l('Skip too many combinations for product (%d/%s)').$cr, $details->reference, $id_product);
                            }
                            continue;
                        }

                        foreach ($combinations as $combination) {
                            $id_product_attribute = isset($combination ['id_product_attribute']) ? $combination ['id_product_attribute'] : 0;
                            $id_attribute_group = isset($combination ['id_attribute_group']) ? $combination ['id_attribute_group'] : 0;

                            $group_details [$id_product_attribute] [$id_attribute_group] = array();
                            $group_details [$id_product_attribute] [$id_attribute_group] ['reference'] = $combination ['reference'];
                            $group_details [$id_product_attribute] [$id_attribute_group] ['ecotax'] = (float)$combination ['ecotax'] ? $combination ['ecotax'] : $details->ecotax;
                            $group_details [$id_product_attribute] [$id_attribute_group] ['ean13'] = $combination ['ean13'];
                            $group_details [$id_product_attribute] [$id_attribute_group] ['upc'] = $combination ['upc'];
                            $group_details [$id_product_attribute] [$id_attribute_group] ['quantity'] = $combination ['quantity'];
                            $group_details [$id_product_attribute] [$id_attribute_group] ['wholesale_price'] = isset($combination ['wholesale_price']) ? $combination ['wholesale_price'] : 0;

                            if (isset($combination ['price'])) {
                                $group_details [$id_product_attribute] [$id_attribute_group] ['price'] = $combination ['price'];
                            } else {
                                $group_details [$id_product_attribute] [$id_attribute_group] ['price'] = '';
                            }

                            if (isset($combination ['attribute_name'])) {
                                $group_details [$id_product_attribute] [$id_attribute_group] ['attribute_name'] = $combination ['attribute_name'];
                            } else {
                                $group_details [$id_product_attribute] [$id_attribute_group] ['attribute_name'] = '';
                            }

                            if (isset($combination ['group_name'])) {
                                $group_details [$id_product_attribute] [$id_attribute_group] ['group_name'] = $combination ['group_name'];
                            } else {
                                $group_details [$id_product_attribute] [$id_attribute_group] ['group_name'] = '';
                            }
                        }
                    }

                    $idx = 0;
                    // Export Combinations or Products Alone
                    foreach ($group_details as $id_product_attribute => $combination) {
                        $idx++;
                        $ean13 = $upc = '';
                        $reference = $ecotax = '';
                        $wholesale_price = '';

                        foreach ($combination as $group_detail) {
                            if (isset($group_detail ['reference']) && !empty($group_detail ['reference'])) {
                                $reference = $group_detail ['reference'];
                            }
                            if (isset($group_detail ['ean13'])) {
                                $ean13 = $group_detail ['ean13'];
                            }
                            if (isset($group_detail ['upc'])) {
                                $upc = $group_detail ['upc'];
                            }
                            if (isset($group_detail ['wholesale_price'])) {
                                $wholesale_price = $group_detail ['wholesale_price'];
                            }
                        }

                        // Reference validation
                        if (empty($reference) && $this->enableValidation) {
                            if (Feedbiz::$debug_mode) {
                                $this->errors [] = sprintf($this->l('Skipping combination without reference(%d)').$cr, $id_product);
                            }
                            continue;
                        }
                        if (in_array($reference, $reference_history) && $this->enableValidation) {
                            if (Feedbiz::$debug_mode) {
                                $this->errors [] = sprintf($this->l('Skipping combination duplicate reference(%d/%s/%s)').$cr, $id_product, $id_product_attribute, $reference);
                            }
                            continue;
                        }
                        $reference_history [] = $reference;

                        if ((bool)Configuration::get('PS_STOCK_MANAGEMENT')) {
                            if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                                $quantity = Product::getRealQuantity($details->id, $id_product_attribute ? $id_product_attribute : null, $id_warehouse, $id_shop);
                            } else {
                                $quantity = Product::getQuantity($details->id, $id_product_attribute ? $id_product_attribute : null);
                            }
                        } else {
                            $quantity = 100;
                        }

                        if ($ean13 && isset($history [$ean13]) && $this->enableValidation) {
                            if (Feedbiz::$debug_mode) {
                                $this->errors [] = sprintf($this->l('Duplicate record for product %s(%d/%d)').$cr, $reference, $id_product, $id_product_attribute);
                            }
                            continue;
                        }
                        $history [$ean13] = true;

                        if (isset($history [$details->id] [$id_product_attribute]) && $this->enableValidation) {
                            if (Feedbiz::$debug_mode) {
                                $this->errors [] = sprintf($this->l('Duplicate record for product %s(%d/%d)').$cr, $reference, $id_product, $id_product_attribute);
                            }
                            continue;
                        }
                        if ((!(int)$ean13 || empty($details->id_manufacturer)) && $this->enableValidation) {
                            if (Feedbiz::$debug_mode) {
                                $this->errors [] = sprintf($this->l('Missing EAN or Manufacturer References for Product %s(%d/%d)').$cr, $reference, $id_product, $id_product_attribute);
                            }
                            continue;
                        }

                        // Offer
                        if (!isset($toFeedBiz ['Offers'] [$id_product]) || empty($toFeedBiz ['Offers'] [$id_product])) {
                            $products_no++;
                            $toFeedBiz ['Offers'] [$id_product] ['ProductIdReference'] = (int)$details->id;

                            if (isset($details->reference) && !empty($details->reference)) {
                                $toFeedBiz ['Offers'] [$id_product] ['ProductReference'] = trim($details->reference);
                            }

                            if (isset($details->ean13) && !empty($details->ean13)) {
                                $toFeedBiz ['Offers'] [$id_product] ['ProductEan'] = sprintf('%013s', trim($details->ean13));
                            }

                            if (isset($details->upc) && !empty($details->upc)) {
                                $toFeedBiz ['Offers'] [$id_product] ['ProductUpc'] = sprintf('%012s', trim($details->upc));
                            }

                            $condition = array_search($details->condition, $arr_condition) + 1;
                            $toFeedBiz ['Offers'] [$id_product] ['ProductCondition'] = $condition;
                            $toFeedBiz ['Offers'] [$id_product] ['DefaultCategory'] = $details->id_category_default;
                            $toFeedBiz ['Offers'] [$id_product] ['Supplier'] = $details->id_supplier;
                            $toFeedBiz ['Offers'] [$id_product] ['Manufacturer'] = $details->id_manufacturer;

                            $tax = 0;//TODO: Fill tax
                            $wholesale = $details->wholesale_price;

                            if ($useTaxes) {
                                if (property_exists('Product', 'id_tax_rules_group')) {
                                    $toFeedBiz ['Offers'] [$id_product] ['Vat'] = $details->id_tax_rules_group;
                                    $wholesale = Tools::ps_round($details->wholesale_price * (1 + ($tax / 100)), 2);
                                } else {
                                    $toFeedBiz ['Offers'] [$id_product] ['Vat'] = isset($details->id_tax) ? $details->id_tax : null;
                                    $wholesale = Tools::ps_round(($details->wholesale_price), 2);
                                }
                            }

                            $toFeedBiz ['Offers'] [$id_product] ['WholeSalePrice'] = $wholesale;

                            $toFeedBiz ['Offers'] [$id_product] ['LastUpdated'] = $date_upd;

                            $ecotax = $details->ecotax;
                            if (version_compare(_PS_VERSION_, '1.4', '>=')) {
                                if (method_exists('Tax', 'getProductEcotaxRate') && $useTaxes) {
                                    $ecotax_rate = (float)Tax::getProductEcotaxRate();
                                    $ecotax = $ecotax + (($ecotax * $ecotax_rate) / 100);
                                }
                            }

                            $toFeedBiz ['Offers'] [$id_product] ['EcoPart'] = sprintf('%.02f', round($ecotax, 2));

                            // select default carrier
                            $id_carrier = $selected;
                            if (method_exists($details, 'getCarriers')) {
                                foreach ($details->getCarriers() as $carrier_ele) {
                                    $id_carrier = $carrier_ele ['id_carrier'];
                                    if ($selected == $carrier_ele ['id_carrier']) {
                                        break;
                                    }
                                }
                            }

                            $basePriceFinal = $details->hasAttributes() == 0 ? $details->getPrice($useTaxes, null, 2, null, false, false) : 0;
                            $toFeedBiz ['Offers'] [$id_product] ['BasePrice'] = sprintf('%.02f', round($basePriceFinal, 2));
                            $toFeedBiz ['Offers'] [$id_product] ['MinDeliveryTime'] = $MinDeliveryTime;
                            $toFeedBiz ['Offers'] [$id_product] ['DefaultCarrier'] = $id_carrier;
                            $toFeedBiz ['Offers'] [$id_product] ['Active'] = $details->active;
                            // Discount
                            if ($useSpecials) {
                                $discountItems = array();

                                if (version_compare(_PS_VERSION_, '1.4.0.2', '>=')) {
                                    foreach ($combinations as $discount_combination) {
                                        $discountItem = null;
                                        $combination_id = isset($discount_combination ['id_product_attribute']) ? (int)$discount_combination ['id_product_attribute'] : 0;

                                        $specificPrice = null;
                                        $specificPrice = SpecificPrice::getSpecificPrice($id_product, $id_shop, $id_currency, (int)Configuration::get('PS_COUNTRY_DEFAULT'), $id_customer_group, 1, $combination_id, 0, 0, 1);

                                        // Sales
                                        if ($specificPrice && isset($specificPrice ['reduction_type']) && isset($specificPrice ['from']) && isset($specificPrice ['to']) && (int)$specificPrice ['from'] && (int)$specificPrice ['to']) {
                                            $priceDiscount = $details->getPrice($useTaxes, ($id_product_attribute ? $id_product_attribute : null), 2, null, false, true);
                                            $priceNoDiscount = $details->getPrice($useTaxes, ($id_product_attribute ? $id_product_attribute : null), 2, null, false, false);

                                            if ($priceDiscount < $priceNoDiscount) {
                                                $price = $specificPrice ['reduction'];

                                                $discountItem ['id_group'] = $id_customer_group;
                                                $discountItem ['dateStart'] = date('c', strtotime($specificPrice ['from']));
                                                $discountItem ['dateEnd'] = date('c', strtotime($specificPrice ['to']));
                                                $discountItem ['price'] = $specificPrice ['price'];
                                                $discountItem ['from_quantity'] = $specificPrice ['from_quantity'];
                                                $discountItem ['id_currency'] = $specificPrice ['id_currency'];
                                                $discountItem ['type'] = $specificPrice ['reduction_type'];

                                                $discountItem ['combination'] = $combination_id;

                                                if ($discountItem ['type'] == "percentage") {
                                                    $percentage = round(($price * 100));
                                                    $discountItem ['value'] = $percentage;
                                                } else {
                                                    $discountItem ['value'] = number_format($priceNoDiscount - $priceDiscount, 2);
                                                }
                                            }

                                            if (isset($discountItem) && !empty($discountItem)) {
                                                $discountItems[$combination_id][$id_customer_group.$specificPrice ['from_quantity']] = $discountItem;
                                            }
                                        }

                                        // Sales for Business Group
                                        if (isset($amazon_business_groups) && is_array($amazon_business_groups)) {
                                            foreach ($amazon_business_groups as $id_group) {
                                                if ($id_customer_group == $id_group) {
                                                    continue;
                                                }

                                                $specificPrice = null;
                                                $specificPrice = SpecificPrice::getSpecificPrice($id_product, $id_shop, $id_currency, (int)Configuration::get('PS_COUNTRY_DEFAULT'), $id_group, 1, $combination_id);
                                                if (is_array($specificPrice) && count($specificPrice)) {
                                                    $price = $specificPrice ['reduction'];

                                                    $discountItem ['id_group'] = $id_group;
                                                    $discountItem ['dateStart'] = date('c', strtotime($specificPrice ['from']));
                                                    $discountItem ['dateEnd'] = date('c', strtotime($specificPrice ['to']));
                                                    $discountItem ['price'] = $specificPrice['price'];
                                                    $discountItem ['type'] = $specificPrice ['reduction_type'];
                                                    $discountItem ['from_quantity'] = $specificPrice ['from_quantity'];
                                                    $discountItem ['id_currency'] = $specificPrice ['id_currency'];
                                                    $discountItem ['combination'] = $combination_id;

                                                    if ($discountItem ['type'] == "percentage") {
                                                        $percentage = round(($price * 100));
                                                        $discountItem ['value'] = $percentage;
                                                    } else {
                                                        $discountItem ['value'] = number_format($price, 2);
                                                    }

                                                    $discountItems[$combination_id][$id_group.$specificPrice ['from_quantity']] = $discountItem;
                                                }

                                                $specificPrices = null;
                                                $specificPrices = FeedBizProduct::getBusinessPriceRulesBreakdown($id_product, $id_shop, $id_group, $combination_id);

                                                foreach ($specificPrices as $specificPrice) {
                                                    $price = $specificPrice ['reduction'];

                                                    $discountItem ['id_group'] = $id_group;
                                                    $discountItem ['dateStart'] = date('c', strtotime($specificPrice ['from']));
                                                    $discountItem ['dateEnd'] = date('c', strtotime($specificPrice ['to']));
                                                    $discountItem ['price'] = $specificPrice['price'];
                                                    $discountItem ['type'] = $specificPrice ['reduction_type'];
                                                    $discountItem ['from_quantity'] = $specificPrice ['from_quantity'];
                                                    $discountItem ['id_currency'] = $specificPrice ['id_currency'];
                                                    $discountItem ['combination'] = $combination_id;

                                                    if ($discountItem ['type'] == "percentage") {
                                                        $percentage = round(($price * 100));
                                                        $discountItem ['value'] = $percentage;
                                                    } else {
                                                        $discountItem ['value'] = number_format($price, 2);
                                                    }

                                                    $discountItems[$combination_id][$id_group.$specificPrice ['from_quantity']] = $discountItem;
                                                }
                                            }
                                        }
                                    }
                                }

                                // Price reduction in 1.4
                                $discountItem = null;
                                if (property_exists($details, 'reduction_price')) {
                                    if (Validate::isDate($details->reduction_from) or Validate::isDate($details->reduction_to)) {
                                        $currentDate = date('Y-m-d H:m:i');
                                        if ($details->reduction_from == $details->reduction_to || ($currentDate < $details->reduction_to and $currentDate > $details->reduction_from)) {
                                            if ($details->reduction_price > 0) {
                                                $discountItem ['dateStart'] = date('c', $details->reduction_from);
                                                $discountItem ['dateEnd'] = date('c', $details->reduction_to);
                                                $discountItem ['price'] = number_format($details->price, 2);
                                                $discountItem ['type'] = 'amount';
                                                $discountItem ['value'] = number_format($details->reduction_price, 2);
                                                $discountItems[0][] = $discountItem;
                                            }

                                            if ($details->reduction_percent > 0) {
                                                $discountItem ['dateStart'] = date('c', $details->reduction_from);
                                                $discountItem ['dateEnd'] = date('c', $details->reduction_to);
                                                $discountItem ['price'] = number_format($details->price, 2);
                                                $discountItem ['type'] = 'percentage';
                                                $discountItem ['value'] = number_format($details->reduction_percent, 2);
                                                $discountItems[0][] = $discountItem;
                                            }
                                        }
                                    }
                                }

                                if (isset($discountItems) && !empty($discountItems)) {
                                    $toFeedBiz ['Offers'] [$id_product] ['Discount'] = $discountItems;
                                }
                            }

                            // options
                            if (is_array($details->name) && count($details->name)) {
                                // main
                                $main_options = FeedBizProduct::getProductOptions($id_product, null);

                                if (is_array($main_options) && count($main_options)) {
                                    $product_options = reset($main_options);
                                } else {
                                    $product_options = array_fill_keys(FeedBizProduct::getProductOptionFields(), null);
                                }

                                foreach ($product_options as $field => $value) {
                                    if (in_array($field, array(
                                            'id_product',
                                            'id_product_attribute',
                                            'id_lang'
                                        )) || !Tools::strlen($value)
                                    ) {
                                        continue;
                                    }

                                    $toFeedBiz['Offers'][$id_product]['Options']['Main'][Tools::ucfirst($field)] = $value;
                                }

                                // if empty Main set empty XML to trunc offer option data in feed.biz
                                if (empty($toFeedBiz['Offers'][$id_product]['Options']['Main'])) {
                                    $toFeedBiz['Offers'][$id_product]['Options']['Main'] = '';
                                }
                            }

                            // Amazon
                            if ($amazon_products_options) {
                                foreach (Feedbiz::$amazon_regions as $domain => $region) {
                                    if (!in_array($domain, $amazon_tabs)) {
                                        continue;
                                    }

                                    $amazon_options = FeedBizProductTabAmazon::getProductOptions($id_product, null, $region);
                                    if (is_array($amazon_options) && count($amazon_options)) {
                                        $product_options = array();

                                        if (is_array($amazon_options) && count($amazon_options)) {
                                            $product_options = reset($amazon_options);
                                        }

                                        if (is_array($product_options) && count($product_options) && max($product_options)) {
                                            foreach ($product_options as $field => $value) {
                                                if (in_array($field, array('id_product', 'id_product_attribute', 'region')) || !Tools::strlen($value)) {
                                                    continue;
                                                }

                                                $toFeedBiz['Offers'][$id_product]['Options']['Amazon'][$region][Tools::ucfirst($field)] = $value;
                                            }
                                        }
                                    }

                                    // if empty Amazon set empty XML to trunc offer option data in feed.biz
                                    if (empty($toFeedBiz['Offers'][$id_product]['Options']['Amazon'][$region])) {
                                        $toFeedBiz['Offers'][$id_product]['Options']['Amazon'][$region] = '';
                                    }
                                }
                            }

                            // Ebay
                            if ($ebay_products_options) {
                                foreach (Feedbiz::$ebay_regions as $domain => $region) {
                                    if (!in_array($domain, $ebay_tabs)) {
                                        continue;
                                    }

                                    $ebay_options = FeedBizProductTabEbay::getProductOptions($id_product, null, $region);
                                    if (is_array($ebay_options) && count($ebay_options)) {
                                        $product_options = array();

                                        if (is_array($ebay_options) && count($ebay_options)) {
                                            $product_options = reset($ebay_options);
                                        }

                                        if (is_array($product_options) && count($product_options) && max($product_options)) {
                                            foreach ($product_options as $field => $value) {
                                                if (in_array($field, array('id_product', 'id_product_attribute', 'region')) || !Tools::strlen($value)) {
                                                    continue;
                                                }
                                                $toFeedBiz['Offers'][$id_product]['Options']['Ebay'][$region][Tools::ucfirst($field)] = $value;
                                            }
                                        }
                                    }

                                    // if empty Ebay set empty XML to trunc offer option data in feed.biz
                                    if (empty($toFeedBiz['Offers'][$id_product]['Options']['Ebay'][$region])) {
                                        $toFeedBiz['Offers'][$id_product]['Options']['Ebay'][$region] = '';
                                    }
                                }
                            }

                            // Cdiscount
                            if ($cdiscount_products_options) {
                                foreach (Feedbiz::$cdiscount_regions as $domain => $region) {
                                    if (!in_array($domain, $cdiscount_tabs)) {
                                        continue;
                                    }

                                    $cdiscount_options = FeedBizProductTabCdiscount::getProductOptions($id_product, null, $region);
                                    if (is_array($cdiscount_options) && count($cdiscount_options)) {
                                        $product_options = array();

                                        if (is_array($cdiscount_options) && count($cdiscount_options)) {
                                            $product_options = reset($cdiscount_options);
                                        }

                                        if (is_array($product_options) && count($product_options) && max($product_options)) {
                                            foreach ($product_options as $field => $value) {
                                                if (in_array($field, array('id_product', 'id_product_attribute', 'region')) || !Tools::strlen($value)) {
                                                    continue;
                                                }
                                                if (empty($toFeedBiz['Offers'][$id_product]['Options']['Cdiscount'][$region]['latency']) && $field=='shipping_delay') {
                                                    $field='latency';
                                                }
                                                $toFeedBiz['Offers'][$id_product]['Options']['Cdiscount'][$region][Tools::ucfirst($field)] = $value;
                                            }
                                        }
                                    }

                                    // if empty Cdiscount set empty XML to trunc offer option data in feed.biz
                                    if (empty($toFeedBiz['Offers'][$id_product]['Options']['Cdiscount'][$region])) {
                                        $toFeedBiz['Offers'][$id_product]['Options']['Cdiscount'][$region] = '';
                                    }
                                }
                            }

                            // Fnac
                            if ($fnac_products_options) {
                                foreach (Feedbiz::$fnac_regions as $domain => $region) {
                                    if (!in_array($domain, $fnac_tabs)) {
                                        continue;
                                    }

                                    $fnac_options = FeedBizProductTabFnac::getProductOptions($id_product, null, $region);
                                    if (is_array($fnac_options) && count($fnac_options)) {
                                        $product_options = array();

                                        if (is_array($fnac_options) && count($fnac_options)) {
                                            $product_options = reset($fnac_options);
                                        }

                                        if (is_array($product_options) && count($product_options) && max($product_options)) {
                                            foreach ($product_options as $field => $value) {
                                                if (in_array($field, array('id_product', 'id_product_attribute', 'region')) || !Tools::strlen($value)) {
                                                    continue;
                                                }
                                                $toFeedBiz['Offers'][$id_product]['Options']['Fnac'][$region][Tools::ucfirst($field)] = $value;
                                            }
                                        }
                                    }

                                    // if empty Ebay set empty XML to trunc offer option data in feed.biz
                                    if (empty($toFeedBiz['Offers'][$id_product]['Options']['Fnac'][$region])) {
                                        $toFeedBiz['Offers'][$id_product]['Options']['Fnac'][$region] = '';
                                    }
                                }
                            }
                            //Rakuten
                            if ($rakuten_products_options) {
                                foreach (Feedbiz::$rakuten_regions as $domain => $region) {
                                    if (!in_array($domain, $rakuten_tabs)) {
                                        continue;
                                    }

                                    $rakuten_options = FeedBizProductTabRakuten::getProductOptions($id_product, null, $region);
                                    if (is_array($rakuten_options) && count($rakuten_options)) {
                                        $product_options = array();

                                        if (is_array($rakuten_options) && count($rakuten_options)) {
                                            $product_options = reset($rakuten_options);
                                        }

                                        if (is_array($product_options) && count($product_options) && max($product_options)) {
                                            foreach ($product_options as $field => $value) {
                                                if (in_array($field, array('id_product', 'id_product_attribute', 'region')) || !Tools::strlen($value)) {
                                                    continue;
                                                }
                                                $toFeedBiz['Offers'][$id_product]['Options']['Rakuten'][$region][Tools::ucfirst($field)] = $value;
                                            }
                                        }
                                    }

                                    // if empty Ebay set empty XML to trunc offer option data in feed.biz
                                    if (empty($toFeedBiz['Offers'][$id_product]['Options']['Rakuten'][$region])) {
                                        $toFeedBiz['Offers'][$id_product]['Options']['Rakuten'][$region] = '';
                                    }
                                }
                            }

                            // Mirakl
                            if ($mirakl_products_options) {
                                $mirakl_options = FeedBizProductTabMirakl::getProductOptions('', $id_product);
                                $toFeedBiz['Offers'][$id_product]['Options']['Mirakl'] = $mirakl_options;
                            }
                        }

                        // Attribute
                        if (isset($toFeedBiz ['Offers'] [$id_product]) && !empty($toFeedBiz ['Offers'] [$id_product])) {
                            if (isset($id_product_attribute) && !empty($id_product_attribute)) {
                                $toFeedBiz ['Offers'] [$id_product] ['items'] [$id_product_attribute] ['AttributeIdReference'] = $id_product_attribute;

                                if ($reference) {
                                    $toFeedBiz ['Offers'] [$id_product] ['items'] [$id_product_attribute] ['AttributeReference'] = trim($reference);
                                }

                                if (( int )$ean13) {
                                    $toFeedBiz ['Offers'] [$id_product] ['items'] [$id_product_attribute] ['AttributeEAN'] = sprintf('%013s', trim($ean13));
                                }

                                if (( int )$upc) {
                                    $toFeedBiz ['Offers'] [$id_product] ['items'] [$id_product_attribute] ['AttributeUPC'] = sprintf('%012s', trim($upc));
                                }

                                if ($useTaxes) {
                                    $wholesale_price = Tools::ps_round($wholesale_price * (1 + ($tax / 100)), 2);
                                } else {
                                    $wholesale_price = Tools::ps_round(($wholesale_price), 2);
                                }

                                $toFeedBiz ['Offers'] [$id_product] ['items'] [$id_product_attribute] ['Stock'] = $quantity;
                                $on_sale = true;
                                $product_price = $details->getPrice($useTaxes, $id_product_attribute, 2, null, false, !$on_sale && $useSpecials);

                                $toFeedBiz ['Offers'] [$id_product] ['items'] [$id_product_attribute] ['AdditionalPrice'] = sprintf('%.02f', round($product_price, 2));
                                $toFeedBiz ['Offers'] [$id_product] ['items'] [$id_product_attribute] ['SalesReferencePrice'] = sprintf('%.02f', round($product_price, 2));
                                $toFeedBiz ['Offers'] [$id_product] ['items'] [$id_product_attribute] ['AdditionalWholeSalePrice'] = sprintf('%.02f', round($wholesale_price, 2));
                                // Options
                                if (is_array($details->name) && count($details->name)) {
                                    $combination_options = FeedBizProduct::getProductOptions($id_product, $id_product_attribute);

                                    if (is_array($combination_options) && count($combination_options)) {
                                        $options = reset($combination_options);
                                    } else {
                                        $options = array_fill_keys(FeedBizProduct::getProductOptionFields(), null);
                                    }

                                    foreach ($options as $field => $value) {
                                        if (in_array($field, array(
                                                'id_product',
                                                'id_product_attribute',
                                                'id_lang'
                                            )) || !Tools::strlen($value)
                                        ) {
                                            continue;
                                        }

                                        $toFeedBiz ['Offers'][$id_product]['items'][$id_product_attribute]['Options']['Main'][Tools::ucfirst($field)] = $value;
                                    }

                                    if ($amazon_products_options) {
                                        foreach (Feedbiz::$amazon_regions as $region) {
                                            $product_options = null;
                                            $amazon_options = FeedBizProductTabAmazon::getProductOptions($id_product, $id_product_attribute, $region);
                                            if ($amazon_options) {
                                                if (is_array($amazon_options) && count($amazon_options)) {
                                                    $product_options = reset($amazon_options);
                                                }

                                                if (is_array($product_options) && count($product_options) && max($product_options)) {
                                                    foreach ($product_options as $field => $value) {
                                                        if (in_array($field, array(
                                                                'id_product',
                                                                'id_product_attribute',
                                                                'region'
                                                            )) || !Tools::strlen($value)
                                                        ) {
                                                            continue;
                                                        }

                                                        $toFeedBiz ['Offers'][$id_product]['items'][$id_product_attribute]['Options']['Amazon'][$region][Tools::ucfirst($field)] = $value;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    if ($ebay_products_options) {
                                        foreach (Feedbiz::$ebay_regions as $region) {
                                            $product_options = null;
                                            $ebay_options = FeedBizProductTabEbay::getProductOptions($id_product, $id_product_attribute, $region);
                                            if ($ebay_options) {
                                                if (is_array($ebay_options) && count($ebay_options)) {
                                                    $product_options = reset($ebay_options);
                                                }

                                                if (is_array($product_options) && count($product_options) && max($product_options)) {
                                                    foreach ($product_options as $field => $value) {
                                                        if (in_array($field, array(
                                                                'id_product',
                                                                'id_product_attribute',
                                                                'region'
                                                            )) || !Tools::strlen($value)
                                                        ) {
                                                            continue;
                                                        }

                                                        $toFeedBiz ['Offers'][$id_product]['items'][$id_product_attribute]['Options']['Ebay'][$region][Tools::ucfirst($field)] = $value;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    if ($cdiscount_products_options) {
                                        foreach (Feedbiz::$cdiscount_regions as $region) {
                                            $product_options = null;
                                            $cdiscount_options = FeedBizProductTabCdiscount::getProductOptions($id_product, $id_product_attribute, $region);
                                            if ($cdiscount_options) {
                                                if (is_array($cdiscount_options) && count($cdiscount_options)) {
                                                    $product_options = reset($cdiscount_options);
                                                }

                                                if (is_array($product_options) && count($product_options) && max($product_options)) {
                                                    foreach ($product_options as $field => $value) {
                                                        if (in_array($field, array(
                                                                'id_product',
                                                                'id_product_attribute',
                                                                'region'
                                                            )) || !Tools::strlen($value)
                                                        ) {
                                                            continue;
                                                        }

                                                        $toFeedBiz ['Offers'][$id_product]['items'][$id_product_attribute]['Options']['Cdiscount'][$region][Tools::ucfirst($field)] = $value;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    if ($fnac_products_options) {
                                        foreach (Feedbiz::$fnac_regions as $region) {
                                            $product_options = null;
                                            $fnac_options = FeedBizProductTabFnac::getProductOptions($id_product, $id_product_attribute, $region);
                                            if ($fnac_options) {
                                                if (is_array($fnac_options) && count($fnac_options)) {
                                                    $product_options = reset($fnac_options);
                                                }

                                                if (is_array($product_options) && count($product_options) && max($product_options)) {
                                                    foreach ($product_options as $field => $value) {
                                                        if (in_array($field, array(
                                                                'id_product',
                                                                'id_product_attribute',
                                                                'region'
                                                            )) || !Tools::strlen($value)
                                                        ) {
                                                            continue;
                                                        }

                                                        $toFeedBiz ['Offers'][$id_product]['items'][$id_product_attribute]['Options']['Fnac'][$region][Tools::ucfirst($field)] = $value;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    if ($rakuten_products_options) {
                                        foreach (Feedbiz::$rakuten_regions as $region) {
                                            $product_options = null;
                                            $rakuten_options = FeedBizProductTabRakuten::getProductOptions($id_product, $id_product_attribute, $region);
                                            if ($rakuten_options) {
                                                if (is_array($rakuten_options) && count($rakuten_options)) {
                                                    $product_options = reset($rakuten_options);
                                                }

                                                if (is_array($product_options) && count($product_options) && max($product_options)) {
                                                    foreach ($product_options as $field => $value) {
                                                        if (in_array($field, array(
                                                                'id_product',
                                                                'id_product_attribute',
                                                                'region'
                                                            )) || !Tools::strlen($value)
                                                        ) {
                                                            continue;
                                                        }

                                                        $toFeedBiz ['Offers'][$id_product]['items'][$id_product_attribute]['Options']['Rakuten'][$region][Tools::ucfirst($field)] = $value;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    if ($mirakl_products_options) {
                                        $mirakl_options = FeedBizProductTabMirakl::getProductOptions('', $id_product, $id_product_attribute);
                                        $toFeedBiz ['Offers'][$id_product]['items'][$id_product_attribute]['Options']['Mirakl'] = $mirakl_options;
                                    }
                                }
                            } else {
                                $toFeedBiz ['Offers'] [$id_product] ['Stock'] = $quantity;
                            }
                        }

                        if (Feedbiz::$debug_mode) {
                            echo $cr;
                            echo "Memory: ".number_format(memory_get_usage() / 1024).'k'.$cr;
                        }

                        $history [$details->id] [$id_product_attribute] = true;

                        if (Feedbiz::$debug_mode) {
                            printf("Exporting Product: %d id: %d reference: %s %s", $idx, $details->id, $reference, $cr);
                        }
                    } // end foreach combinations
                    if ($limit == $products_no) {
                        break;
                    } // check limit
                }
            } // end foreach products

            // CONTEXT UPDATE
            $currentTimeStamp = new DateTime();
            $this->exportContext->timestamp = $currentTimeStamp->format('Y-m-d H:i:s');
            $this->exportContext->currentPage = $this->exportContext->currentPage + 1;
            $this->exportContext->currentProduct = isset($id_product) ? $id_product : 0;
            $this->exportContext->status = $products_determine_no == sizeof($products) ? FeedBizExportContext::STATUS_COMPLETE : FeedBizExportContext::STATUS_INCOMPLETE;
            FeedBizExportContext::save(FeedBizExportContext::CONF_FEEDBIZ_OFFERS_EXPORT_CONTEXT, $this->exportContext);
        } // end if

        $this->createOffers($toFeedBiz);
    }
}

$feedbiz_exportoffers = new FeedBizExportOffers();
$feedbiz_exportoffers->dispatch();

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

require_once(dirname(__FILE__).'/../feedbiz.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.tools.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.product.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.context.class.php');

/**
 * Class FeedBizProductTab
 */
class FeedBizProductTab extends Feedbiz
{
    /**
     * @return string HTML code of the page.
     */
    public function doIt($params = array())
    {
        require_once(dirname(__FILE__).'/../classes/feedbiz.product_tab.amazon.class.php');
        require_once(dirname(__FILE__).'/../classes/feedbiz.product_tab.ebay.class.php');
        require_once(dirname(__FILE__).'/../classes/feedbiz.product_tab.cdiscount.class.php');
        require_once(dirname(__FILE__).'/../classes/feedbiz.product_tab.fnac.class.php');
        require_once(dirname(__FILE__).'/../classes/feedbiz.product_tab.mirakl.class.php');
        require_once(dirname(__FILE__).'/../classes/feedbiz.product_tab.rakuten.class.php');
        if (Tools::getValue('debug')) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        }

        $id_product = (int)Tools::getValue(
            'id_product',
            array_key_exists('id_product', $params) ? $params['id_product'] : null
        );
        $active = true;

        if (!is_numeric($id_product)) {
            return null;
        }

        $product = new Product($id_product);

        if (!Validate::isLoadedObject($product)) {
            return null;
        }

        $view_params = array();

        if (version_compare(_PS_VERSION_, '1.5', '>=') && Shop::isFeatureActive() &&
            in_array($this->context->shop->getContext(), array(Shop::CONTEXT_GROUP, Shop::CONTEXT_ALL))) {
            $view_params['shop_warning'] = $this->l(
                'You are in multishop environment. To use this module, you must select a target shop.'
            );
            $active = false;
        }

        $combinations = array();
        $has_attributes = false;

        $this->context = Context::getContext();

        $id_lang = $this->context->language->id;

        $languages = FeedbizTools::languages();

        $view_params['id_lang'] = $this->id_lang;
        $view_params['img'] = $this->images;
        $view_params['id_product'] = (int)$id_product;
        $view_params['module_url'] = $this->url;
        $view_params['module_path'] = $this->path;
        $view_params['version'] = $this->version;
        $view_params['ps16x'] = version_compare(_PS_VERSION_, '1.6', '>=');
        $view_params['ps15'] = version_compare(_PS_VERSION_, '1.6', '<');
        $view_params['id_product'] = (int)$id_product;
        $view_params['token'] = Configuration::get('FEEDBIZ_PS_TOKEN');
        $view_params['json_url'] = $this->url.'functions/product_ext.json.php?context_key='.
            FeedbizContext::getKey($this->context->shop);

        $view_params['expert_mode'] = (bool)Configuration::get('FEEDBIZ_EXPERT');
        $view_params['repricing'] = isset($this->has_repricing) && $this->has_repricing ?
            (bool)$this->has_repricing : false;

        $view_params['active'] = $active;

        $view_params['class_warning'] = 'warn '.($this->ps16x ? 'alert alert-warning' : '');
        $view_params['class_error'] = 'error '.($this->ps16x ? 'alert alert-danger' : '');
        $view_params['class_success'] = 'confirm '.($this->ps16x ? 'alert alert-success' : 'conf');
        $view_params['class_info'] = 'hint '.($this->ps16x ? 'alert alert-info' : 'conf');


        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            $view_params["PS14"] = "1";
        }

        $view_params['product_tab'] = array();
        $view_params['product_tab']['id_product'] = $id_product;
        $view_params['product_tab']['id_manufacturer'] = $product->id_manufacturer;
        $view_params['product_tab']['id_category_default'] = $product->id_category_default;
        $view_params['product_tab']['id_supplier'] = $product->id_supplier;

        $product_name = $product->name[$id_lang];

        if ($active) {
            if (Combination::isFeatureActive() && $product->hasAttributes()) {
                $has_attributes = true;
                $combinations = array();

                $attributes_groups = $product->getAttributesGroups($id_lang);
                $attributes = $product->getProductAttributesIds($id_product);

                if (is_array($attributes_groups) && is_array($attributes)) {
                    foreach ($attributes as $attribute1) {
                        $id_product_attribute = $attribute1['id_product_attribute'];
                        $complex_id = sprintf('%d_%d', $id_product, $attribute1['id_product_attribute']);

                        $combinations[$complex_id] = array();

                        $combination = new Combination((int)$id_product_attribute);
                        $attributes = $combination->getAttributesName($id_lang);

                        foreach ($attributes as $attribute) {
                            $attribute_group_name = null;

                            foreach ($attributes_groups as $attribute_group) {
                                if ($attribute_group['id_attribute'] != $attribute['id_attribute']) {
                                    continue;
                                }
                                $attribute_group_name = $attribute_group['group_name'];
                            }
                            if (Tools::strlen($attribute_group_name)) {
                                $combination_pair = sprintf('%s - %s', $attribute_group_name, $attribute['name']);
                            } else {
                                $combination_pair = $attribute['name'];
                            }

                            $combinations[$complex_id]['complex_id'] = sprintf(
                                '%d_%d',
                                $product->id,
                                $id_product_attribute
                            );
                            $combinations[$complex_id]['id_product'] = (int)$id_product;
                            $combinations[$complex_id]['id_product_attribute'] = (int)$id_product_attribute;
                            $combinations[$complex_id]['reference'] = $combination->reference;
                            $combinations[$complex_id]['ean13'] = $combination->ean13;
                            $combinations[$complex_id]['upc'] = $combination->upc;

                            if (array_key_exists('name', $combinations[$complex_id])
                                && Tools::strlen($combinations[$complex_id]['name'])) {
                                $combinations[$complex_id]['name'] .= sprintf(', %s', $combination_pair);
                            } else {
                                $combinations[$complex_id]['name'] = $combination_pair;
                            }
                        }
                    }
                }
            }
            $view_params['product_tab']['product'] = array();
            $view_params['product_tab']['product']['name'] = $product_name;
            $view_params['product_tab']['product']['complex_id'] = sprintf('%d_0', $product->id);
            $view_params['product_tab']['product']['reference'] = $product->reference;
            $view_params['product_tab']['product']['ean13'] = $product->ean13;
            $view_params['product_tab']['product']['upc'] = $product->upc;
            $view_params['product_tab']['product']['id_product'] = (int)$id_product;

            $view_params['product_tab']['combinations'] = $combinations;
        }


        $view_params['product_tab']['feedbiz'] = array();
        $view_params['product_tab']['languages'] = array();
        $view_params['product_tab']['show_languages'] = false;

        $option_fields = FeedBizProduct::getProductOptionFields();

        if (count($option_fields)) {
            $view_params['product_tab']['languages'] = $languages;
            $complex_id = sprintf('%d_%d', $id_product, 0);

            $product_options = FeedBizProduct::getProductOptions($id_product, null);

            if (is_array($product_options) && count($product_options)) {
                $view_params['product_tab']['feedbiz'][$complex_id] = reset($product_options);
            } else {
                $view_params['product_tab']['feedbiz'][$complex_id] = array_fill_keys(
                    $option_fields,
                    null
                );
            }

            $title = sprintf(
                '%s - %s',
                !empty($product->reference) ? $product->reference : 'N/A',
                $product_name
            );

            $view_params['product_tab']['feedbiz'][$complex_id]['id_product'] = $id_product;
            $view_params['product_tab']['feedbiz'][$complex_id]['id_product_attribute'] = 0;
            $view_params['product_tab']['feedbiz'][$complex_id]['title'] = $title;

            if ($has_attributes && count($combinations)) {
                foreach ($combinations as $combination) {
                    $id_product_attribute = $combination['id_product_attribute'];
                    $id_product = $combination['id_product'];
                    $complex_id = sprintf('%d_%d', $id_product, $id_product_attribute);

                    $product_options = FeedBizProduct::getProductOptions(
                        $id_product,
                        $id_product_attribute
                    );

                    if (is_array($product_options) && count($product_options)) {
                        $view_params['product_tab']['feedbiz'][$complex_id] = reset($product_options);
                    } else {
                        $view_params['product_tab']['feedbiz'][$complex_id] = array_fill_keys(
                            $option_fields,
                            null
                        );
                    }

                    $title = sprintf(
                        '%s - %s',
                        $combinations[$complex_id]['reference'] ? $combinations[$complex_id]['reference'] : 'N/A',
                        $combinations[$complex_id]['name']
                    );

                    $view_params['product_tab']['feedbiz'][$complex_id]['id_product'] = $id_product;
                    $view_params['product_tab']['feedbiz'][$complex_id]['id_product_attribute'] = $id_product_attribute;
                    $view_params['product_tab']['feedbiz'][$complex_id]['id_lang'] = $id_lang;
                    $view_params['product_tab']['feedbiz'][$complex_id]['title'] = $title;
                }
            }
        }

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
        $mirakl_tabs = array();
        $rakuten_tabs = array();

        if ($marketplace_tab) {
            $marketplace_tab_config = unserialize($marketplace_tab);

            if (is_array($marketplace_tab_config) && count($marketplace_tab_config)) {
                if (!$this->branded_module) {
                    if (array_key_exists('amazon', $marketplace_tab_config)
                        && Tools::strlen($marketplace_tab_config['amazon'])) {
                        $amazon_products_options = true;
                        $amazon_tabs = explode(';', $marketplace_tab_config['amazon']);
                    }
                    if (array_key_exists('ebay', $marketplace_tab_config)
                        && Tools::strlen($marketplace_tab_config['ebay'])) {
                        $ebay_products_options = true;
                        $ebay_tabs = explode(';', $marketplace_tab_config['ebay']);
                    }
                    if (array_key_exists('fnac', $marketplace_tab_config)
                        && Tools::strlen($marketplace_tab_config['fnac'])) {
                        $fnac_products_options = true;
                        $fnac_tabs = explode(';', $marketplace_tab_config['fnac']);
                    }
                    if (array_key_exists('mirakl', $marketplace_tab_config)
                        && Tools::strlen($marketplace_tab_config['mirakl'])) {
                        $mirakl_products_options = true;
                        $mirakl_tabs = explode(';', $marketplace_tab_config['mirakl']);
                    }
                    if ((array_key_exists('rakuten', $marketplace_tab_config)
                        && Tools::strlen($marketplace_tab_config['rakuten'])) ||
                         (array_key_exists('priceminister', $marketplace_tab_config)
                        && Tools::strlen($marketplace_tab_config['priceminister']))) {
                        $rakuten_products_options = true;
                        $rakuten_tabs = explode(';', $marketplace_tab_config['rakuten']);
                    }
                }

                if (array_key_exists('cdiscount', $marketplace_tab_config)
                    && Tools::strlen($marketplace_tab_config['cdiscount'])) {
                    $cdiscount_products_options = true;
                    $cdiscount_tabs = explode(';', $marketplace_tab_config['cdiscount']);
                }
            }
        }

        if ($amazon_products_options) {
            $amazonTab = new FeedBizProductTabAmazon($amazon_tabs);
            $view_params['product_tab']['amazon'] = $amazonTab->marketplaceProductTabContent($product, $combinations);
        }

        if ($ebay_products_options) {
            $ebayTab = new FeedBizProductTabEbay($ebay_tabs);
            $view_params['product_tab']['ebay'] = $ebayTab->marketplaceProductTabContent($product, $combinations);
        }

        if ($fnac_products_options) {
            $fnacTab = new FeedBizProductTabFnac($fnac_tabs);
            $view_params['product_tab']['fnac'] = $fnacTab->marketplaceProductTabContent($product, $combinations);
        }

        if ($mirakl_products_options) {
            $miraklTab = new FeedBizProductTabMirakl($mirakl_tabs);
            $view_params['product_tab']['mirakl'] = $miraklTab->marketplaceProductTabContent($product, $combinations);
        }

        if ($rakuten_products_options) {
            $rakutenTab = new FeedBizProductTabRakuten($rakuten_tabs);
            $view_params['product_tab']['rakuten'] = $rakutenTab->marketplaceProductTabContent($product, $combinations);
        }

        if ($cdiscount_products_options) {
            $cdiscountTab = new FeedBizProductTabCdiscount($cdiscount_tabs);
            $view_params['product_tab']['cdiscount'] = $cdiscountTab->marketplaceProductTabContent($product, $combinations);
            $view_params['cdiscount_only'] = $this->branded_module == 'cdiscount';
        }
        // echo '<pre>'; print_r($view_params['product_tab']['mirakl']); exit;
        $this->context->smarty->assign($view_params);

        return $this->context->smarty->fetch($this->path.'views/templates/admin/catalog/product_tab.tpl');
    }
}

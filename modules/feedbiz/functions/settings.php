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
require_once(dirname(__FILE__).'/../classes/feedbiz.address.class.php');

/**
 * Class FeedBizSettings
 */
class FeedBizSettings extends Feedbiz
{
    /**
     * @var bool
     */
    public $debug = false;
    /**
     * @var array
     */
    public $_errors = array();
    /**
     * @var string
     */
    public $_cr = "<br />\n";

    /**
     * FeedBizSettings constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $id_shop = '';
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $id_shop = $this->context->shop->id;
        }

        FeedbizContext::restore($this->context);

        $this->debug = (bool)Configuration::get('FEEDBIZ_DEBUG') || (bool)Tools::getValue('debug');

        if ($this->debug) {
            echo "---------------------SETTING---------------------".$this->_cr;
            echo "SHOP:".$id_shop.$this->_cr;
            echo "LANG:".$this->id_lang.$this->_cr;
            echo "-------------------END SETTING-------------------".$this->_cr;
        }
    }

    /**
     *
     */
    public function dispatch()
    {
        $this->settingsExport();

        $this->settingsFetch();
        $this->addTables();
    }

    /**
     * @param $code
     * @param $message
     */
    public function endOnError($code, $message)
    {
        $Document = new DOMDocument();
        $Document->preserveWhiteSpace = true;
        $Document->formatOutput = true;
        $Document->encoding = 'utf-8';
        $Document->version = '1.0';

        $ExportData = $Document->appendChild($Document->createElement('Settings', ''));
        $ExportData->setAttribute('ShopName', Configuration::get('PS_SHOP_NAME'));
        $ExportData->appendChild($errorDoc = $Document->createElement('Status', ''));
        $errorDoc->appendChild($Document->createElement('Code', $code));
        $errorDoc->appendChild($Document->createElement('Message', $message));

        header("Content-Type: application/xml; charset=utf-8");
        echo $Document->saveXML();
        exit();
    }

    /**
     * @return bool
     */
    private function settingsFetch()
    {
        FeedbizTools::securityCheck();

        $amazon_domains = array();
        $ebay_domains = array();
        $cdiscount_domains = array();
        $fnac_domains = array();
        $mirakl_domains = array();
        $mirakl_regions = array();
        $rakuten_domains = array();
        $marketplace_tab = array();

        $token = Configuration::get('FEEDBIZ_TOKEN');
        $preproduction = Configuration::get('FEEDBIZ_PREPRODUCTION') ? true : false;

        $feedBizWS = new FeedBizWebService(null, $token, $preproduction, $this->debug);
        $get_configurations_result = $feedBizWS->getConfigurations();

        if (!$get_configurations_result instanceof SimpleXMLElement) {
            if ($this->debug) {
                $code = sprintf('%d/%d', basename(__FILE__), __LINE__);
                $this->endOnError($code, 'Unable to retrieve data from webservice');
            }

            return (false);
        }

        if ($this->debug) {
            echo "<pre>\n";
            $dom = dom_import_simplexml($get_configurations_result)->ownerDocument;
            $dom->formatOutput = true;
            echo htmlentities($dom->saveXML());
            echo "<pre>\n";
        }

        $amazon_configurations = $get_configurations_result->xpath('/Response/Marketplace/return/Amazon/*/domain');

        if (is_array($amazon_configurations) && count($amazon_configurations)) {
            foreach ($amazon_configurations as $amazon_configuration) {
                $amazon_domains[] = (string)$amazon_configuration;
            }


            $amazon_features = $get_configurations_result->xpath('/Response/Marketplace/return/Amazon/*/domain/..');

            if (is_array($amazon_features) && count($amazon_features)) {
                $features_array = array();
                $features_array['platforms'] = array();

                $fba_active = false;
                $fba_multichannel = false;
                $amazon_business_groups = array();

                foreach ($amazon_features as $amazon_feature) {
                    $features = Tools::jsonDecode(
                        str_replace('@attributes', 'attributes', Tools::jsonEncode($amazon_feature))
                    );

                    if ($features instanceof stdClass) {
                        $features->plaform = trim($features->ext, '.');

                        if (property_exists($features, 'fba')
                            && property_exists($features->fba->attributes, 'is_master_platform')
                            && $features->fba->attributes->is_master_platform == 'true'
                            && $features->fba->attributes->active == 'true') {
                            $fba_active = true;
                        }

                        if (property_exists($features, 'fba')
                            && property_exists($features->fba->attributes, 'multichannel')
                            && $features->fba->attributes->multichannel == 'true') {
                            $fba_multichannel = true;
                        }

                        if (property_exists($features, 'amazon_business_group')) {
                            $amazon_business_groups[] = (int) $features->amazon_business_group;
                        }

                        $features_array['platforms'][$features->plaform] = $features;
                    }
                }

                $features_array['has_fba'] = $fba_active;
                $features_array['has_fba_multichannel'] = $fba_multichannel;
                $features_array['amazon_business_groups'] = $amazon_business_groups;

                Configuration::updateValue('FEEDBIZ_AMAZON_FEATURES', serialize((object)$features_array));
            }
        }

        $ebay_configurations = $get_configurations_result->xpath('/Response/Marketplace/return/eBay/*/domain');

        if (is_array($ebay_configurations) && count($ebay_configurations)) {
            foreach ($ebay_configurations as $ebay_configuration) {
                $ebay_domains[] = (string)$ebay_configuration;
            }
        }

        $cdiscount_configurations = $get_configurations_result->xpath('/Response/Marketplace/return/Cdiscount/*/domain');

        if (is_array($cdiscount_configurations) && count($cdiscount_configurations)) {
            foreach ($cdiscount_configurations as $cdiscount_configuration) {
                $cdiscount_domains[] = (string)$cdiscount_configuration;
            }
        }

        $fnac_configurations = $get_configurations_result->xpath('/Response/Marketplace/return/Fnac/*/domain');

        if (is_array($fnac_configurations) && count($fnac_configurations)) {
            foreach ($fnac_configurations as $fnac_configuration) {
                $fnac_domains[] = (string)$fnac_configuration;
            }
        }

        $rakuten_configurations = $get_configurations_result->xpath('/Response/Marketplace/return/Rakuten/*/domain');

        if (is_array($rakuten_configurations) && count($rakuten_configurations)) {
            foreach ($rakuten_configurations as $rakuten_configuration) {
                $rakuten_domains[] = (string)$rakuten_configuration;
            }
        }

        $mirakl_configurations = $get_configurations_result->xpath('/Response/Marketplace/return/Mirakl/*/*/domain');
        if (empty($mirakl_configurations)) {
            $mirakl_configurations = $get_configurations_result->xpath('/Response/Marketplace/return/Mirakl/*/domain');
            $c=2;
        } else {
            $c=1;
        }


        if (!isset($mirakl_configurations) || empty($mirakl_configurations)) {
            $mirakl_configurations = $get_configurations_result->xpath('/Response/Marketplace/return/General/*/*/domain');

            if (empty($mirakl_configurations)) {
                $mirakl_configurations = $get_configurations_result->xpath('/Response/Marketplace/return/General/*/domain');
                $c=4;
            } else {
                $c=5;
            }
        }
        $mirakl_regions=array();
        if (is_array($mirakl_configurations) && count($mirakl_configurations)) {
            $mirakl = $get_configurations_result->xpath('/Response/Marketplace/return/Mirakl');

            if (!isset($mirakl) || empty($mirakl)) {
                $mirakl = $get_configurations_result->xpath('/Response/Marketplace/return/General');
            }

            if (is_array($mirakl) && count($mirakl)) {
                foreach ($mirakl as $domains) {
                    foreach ($domains as $name => $domain) {
                        if (empty($name)) {
                            continue;
                        }
                        $domain = Tools::jsonDecode(
                            str_replace('@attributes', 'attributes', Tools::jsonEncode($domain))
                        );
                        if ($c==2 || $c==4) {
                            $domain = array($name=>$domain);
                        }

                        foreach ($domain as $region => $data) {
                            $id=0;
                            if (isset($data->id)) {
                                $id = (string) $data->id ;
                            }
                            if (empty($id)&&!empty($data->sub_marketplace)) {
                                $id = $data->sub_marketplace;
                            }
                            if (property_exists($data, 'domain')) {
                                $region = (string) $region ;
                                $mirakl_regions[$name][$region]['domain'] =  (string) $data->domain;
                                $mirakl_regions[$name][$region]['region'] = $region;
                                $mirakl_regions[$name][$region]['sub_marketplace'] = $id;
                            }
                        }
                    }
                }

                Configuration::updateValue('FEEDBIZ_MIRAKL_REGION', serialize((object)$mirakl_regions));
            }

            foreach ($mirakl_configurations as $mirakl_configuration) {
                $mirakl_domains[] = (string)$mirakl_configuration;
            }
        }

        if (empty($mirakl_regions)) {
            Configuration::updateValue('FEEDBIZ_MIRAKL_REGION', serialize((object)array()));
        }

        if (count($amazon_domains)) {
            $marketplace_tab['amazon'] = implode(';', $amazon_domains);
        }
        if (count($ebay_domains)) {
            $marketplace_tab['ebay'] = implode(';', $ebay_domains);
        }
        if (count($cdiscount_domains)) {
            $marketplace_tab['cdiscount'] = implode(';', $cdiscount_domains);
        }
        if (count($fnac_domains)) {
            $marketplace_tab['fnac'] = implode(';', $fnac_domains);
        }
        if (count($mirakl_domains)) {
            $marketplace_tab['mirakl'] = implode(';', $mirakl_domains);
        }
        if (count($rakuten_domains)) {
            $marketplace_tab['rakuten'] = implode(';', $rakuten_domains);
        }

        if (count($marketplace_tab)) {
            Configuration::updateValue('FEEDBIZ_MARKETPLACE_TAB', serialize($marketplace_tab));
        } else {
            Configuration::deleteByName('FEEDBIZ_MARKETPLACE_TAB');
        }

        // Messaging
        $messaging_configurations = $get_configurations_result->xpath('/Response/Messaging/return');

        $feedbiz_features = unserialize(Configuration::get('FEEDBIZ_FEATURES'));

        if (!$feedbiz_features) {
            $feedbiz_features = array();
        }

        $messaging_lang = array();
        if (is_array($messaging_configurations) && count($messaging_configurations)) {
            foreach ($messaging_configurations as $messagings) {
                foreach ($messagings as $region => $messaging) {
                    $msg = Tools::jsonDecode(str_replace(
                        '@attributes',
                        'attributes',
                        Tools::jsonEncode($messaging)
                    ));

                    if (property_exists($msg, 'attributes') && property_exists($msg->attributes, 'active') &&
                        $msg->attributes->active == 'true' && property_exists($msg->attributes, 'id_lang')
                    ) {
                        $messaging_lang[$region] = $msg->attributes->id_lang;
                    }
                }
            }
        }

        if (count($messaging_lang)) {
            $feedbiz_features['messaging'] = implode(';', $messaging_lang);
        } else {
            $feedbiz_features['messaging'] = '';
        }

        if (count($feedbiz_features)) {
            Configuration::updateValue('FEEDBIZ_FEATURES', serialize($feedbiz_features));
        }

        // Order Cancel Reasons
        $feedbiz_order_cancel_reasons = array();

        $order_cancel_reasons = $get_configurations_result->xpath('/Response/OrderCancelReason/return');

        if (is_array($order_cancel_reasons) && count($order_cancel_reasons)) {
            foreach ($order_cancel_reasons as $order_cancel_reason) {
                foreach ($order_cancel_reason as $marketplace => $cancel_reasons) {
                    $feedbiz_order_cancel_reasons[$marketplace] = array();

                    if (isset($cancel_reasons->Reasons->Item)) {
                        foreach ($cancel_reasons->Reasons->Item as $reason_items) {
                            $reason_item = Tools::jsonDecode(
                                str_replace('@attributes', 'attributes', Tools::jsonEncode($reason_items))
                            );
                            $reason_id = isset($reason_item->attributes->id) ? $reason_item->attributes->id : null;
                            $reason_key = isset($reason_item->Key) ? $reason_item->Key : null;
                            $reason_text = isset($reason_item->Text) ? $reason_item->Text : null;

                            if (isset($reason_id)) {
                                $feedbiz_order_cancel_reasons[$marketplace][$reason_key] = $reason_text;
                            }
                        }
                    }
                }
            }
        }

        if (count($feedbiz_order_cancel_reasons)) {
            Configuration::updateValue('FEEDBIZ_ORDER_CANCEL_REASONS', serialize($feedbiz_order_cancel_reasons));
        }

        // Amazon Shipping Templates
        $feedbiz_amazon_shipping_templates = array();

        $amazon_shipping_templates = $get_configurations_result->xpath('/Response/AmazonShippingGroups/return');

        if (is_array($amazon_shipping_templates) && count($amazon_shipping_templates)) {
            foreach ($amazon_shipping_templates as $amazonshippingtemplate) {
                foreach ($amazonshippingtemplate as $region => $amazon_shipping_template) {
                    $feedbiz_amazon_shipping_templates[$region] = (array)$amazon_shipping_template;
                }
            }
        }

        if (count($feedbiz_amazon_shipping_templates)) {
            Configuration::updateValue('FEEDBIZ_AMAZON_SHIPPING_GROUP', serialize($feedbiz_amazon_shipping_templates));
        }

        return (true);
    }

    /**
     * Push Settings to Feed.biz
     */
    private function settingsExport()
    {
        FeedbizTools::securityCheck();

        $useTaxes = Configuration::get('FEEDBIZ_USE_TAXES') ? true : false;

        // Force French
        $id_lang = $this->id_lang;

        $id_currency = Configuration::get('PS_CURRENCY_DEFAULT');
        $all_avail_lang  = array();
        ob_start();

        // create DOMDocument();
        $Document = new DOMDocument();
        $Document->preserveWhiteSpace = true;
        $Document->formatOutput = true;
        $Document->encoding = 'utf-8';
        $Document->version = '1.0';

        $ExportData = $Document->appendChild($Document->createElement('ExportData'));

        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $ExportData->setAttribute(
                'ShopName',
                Configuration::get('PS_SHOP_NAME', null, null, $this->context->shop->id)
            );
        }

        // Language
        $languages = Language::getLanguages(true);
        if (is_array($languages)) {
            $LanguageElement = $ExportData->appendChild($Document->createElement('Language'));

            foreach ($languages as $language) {
                $LanguageNameElement = $LanguageElement->appendChild($language_id = $Document->createElement('Name'));
                $LanguageNameElement->appendChild($Document->createCDATASection($language['name']));

                $language_id->setAttribute('ID', $language['id_lang']);
                $language_id->setAttribute('iso_code', $language['iso_code']);

                if ($language['id_lang'] == $id_lang) {
                    $language_id->setAttribute('is_default', 1);
                } else {
                    $language_id->setAttribute('is_default', 0);
                }
                $all_avail_lang[$language['id_lang']] = $language['id_lang'];
            }
        }

        // Currencies
        $currencies = Currency::getCurrencies();
        if (is_array($currencies)) {
            $CurrenciesElement = $ExportData->appendChild($Document->createElement('Currencies'));
            foreach ($currencies as $currency) {
                $CurrenciesElement->appendChild($currency_id = $Document->createElement('Currency', $currency['name']));
                $currency_id->setAttribute('ID', $currency['id_currency']);
                $currency_id->setAttribute('iso_code', $currency['iso_code']);

                if ($currency['id_currency'] == $id_currency) {
                    $currency_id->setAttribute('is_default', 1);
                } else {
                    $currency_id->setAttribute('is_default', 0);
                }
            }
        }

        // Taxes
        $taxesElement = $ExportData->appendChild($Document->createElement('Taxes'));
        $history_taxs = array();
        $taxesTaxElement = array();

        foreach ($languages as $lang) {
            $taxes_arr = Tax::getTaxes($lang['id_lang'], true);
            foreach ($taxes_arr as $taxes_value) {
                if (!isset($history_taxs[$taxes_value['id_tax']]) || $history_taxs[$taxes_value['id_tax']] == false) {
                    $taxesTaxElement[$taxes_value['id_tax']] = $taxesElement->appendChild(
                        $t_tax = $Document->createElement('Tax')
                    );
                    $t_tax->setAttribute('type', 'vat');
                    $t_tax->setAttribute('id', $taxes_value['id_tax']);
                    $taxesTaxElement[$taxes_value['id_tax']]->appendChild(
                        $t_Rate = $Document->createElement('Rate', $taxes_value['rate'])
                    );
                    $t_Rate->setAttribute('type', 'percent');
                }

                $taxesTaxName = $taxesTaxElement[$taxes_value['id_tax']]->appendChild(
                    $tax_name = $Document->createElement('Name')
                );
                $taxesTaxName->appendChild($Document->createCDATASection($taxes_value['name']));
                $tax_name->setAttribute('lang', $taxes_value['id_lang']);

                $history_taxs[$taxes_value['id_tax']] = true;
            }
        }

        // Eco Taxes
        if (version_compare(_PS_VERSION_, '1.4', '>=')) {
            $ecotax_rate = 0;
            if (method_exists('Tax', 'getProductEcotaxRate') && $useTaxes) {
                $default_store_tax_calculator = FeedbizTax::getDefaultStoreTaxCalculator(
                    (int)Configuration::get('PS_ECOTAX_TAX_RULES_GROUP_ID')
                );
                $ecotax_rate = $default_store_tax_calculator == null ?
                    0 : $default_store_tax_calculator->getTotalRate();
            }

            if ($ecotax_rate != 0) {
                $ecotaxesElement = $ExportData->appendChild($Document->createElement('EcoTaxes'));
                $ecotaxesTaxElement = $ecotaxesElement->appendChild($t_tax = $Document->createElement('Tax'));
                $t_tax->setAttribute('ecotax', 'vat');
                $t_tax->setAttribute('name', 'Ecotaxe');

                $ecotaxesTaxElement->appendChild($t_Rate = $Document->createElement('Rate', $ecotax_rate));
                $t_Rate->setAttribute('type', 'fixed');
                $t_Rate->setAttribute('currency', $id_currency);
            }
        }

        // Carrier
        $id_carrier = null;
        $view_carriers = null;
        $url_carriers = null;
        $CarriersElement = $ExportData->appendChild($Document->createElement('Carriers'));

        $selected = Configuration::get('FEEDBIZ_CARRIER');

        if (defined('Carrier::ALL_CARRIERS')) {
            $all_carriers = Carrier::ALL_CARRIERS;
        } elseif (defined('ALL_CARRIERS')) {
            $all_carriers = ALL_CARRIERS;
        } else {
            $all_carriers = 5;
        }

        $carriers = Carrier::getCarriers($id_lang, false, false, false, null, $all_carriers);
        $carriers_tax = FeedBizProduct::getCarrierTax();
        if ($this->debug) {
            $carriers_Debug = Db::getInstance()->executeS('SELECT c.*
						FROM `'._DB_PREFIX_.'carrier` c');

            echo "---------------------ALL CARRIERS---------------------".$this->_cr;
            echo '<pre>'.print_r($carriers_Debug, true).'</pre>'.$this->_cr;
            echo "---------------------END CARRIERS---------------------".$this->_cr;

            echo "---------------------ALL LOADED CARRIERS---------------------".$this->_cr;
            echo '<pre>'.print_r($carriers, true).'</pre>'.$this->_cr;
            echo "---------------------END LOADED CARRIERS---------------------".$this->_cr;

            echo "---------------------ALL CARRIERS TAX---------------------".$this->_cr;
            echo '<pre>'.print_r($carriers_tax, true).'</pre>'.$this->_cr;
            echo "---------------------END CARRIERS TAX---------------------".$this->_cr;
        }
        
        $feedbiz_categories = FeedbizConfiguration::get('FEEDBIZ_CATEGORIES');
        $CategoryElement = array();
        if ($this->debug) {
            echo "---------------------CATEGORIES---------------------".$this->_cr;
            echo 'Categories selected debug : '.__LINE__."\n";
            print_r($feedbiz_categories);
        }

        foreach ($carriers as $carrier) {
            $view_carriers = $carrier['name'];
            $url_carriers = $carrier['url'];
            $deleted_state_carriers = isset($carrier['deleted']) ? (int)$carrier['deleted'] : 0 ; // to check the deleted carrier
            $id_carrier = (int)$carrier['id_carrier'];

            //auto select first carrier as default
            $selected = empty($selected) ? $carrier['id_carrier'] : $selected;
            $default_carrier = $selected == $carrier['id_carrier'] ? 1 : 0;
            $id_carrier_reference = isset($carrier['id_reference']) ? (int)$carrier['id_reference'] : (int)$carrier['id_carrier'];

            $CarrierElement = $CarriersElement->appendChild($carrier_id = $Document->createElement('Carrier'));
            $carrier_id->setAttribute('ID', $id_carrier);
            $carrier_id->setAttribute('ID_ref', $id_carrier_reference);
            $carrier_id->setAttribute('Default', $default_carrier);
            $carrier_id->setAttribute('Deleted', $deleted_state_carriers);

            $ProductCarrier = $CarrierElement->appendChild($Document->createElement('Name'));
            $ProductCarrier->appendChild($Document->createCDATASection($view_carriers));

            $ProductCarrierURL = $CarrierElement->appendChild($Document->createElement('Url'));
            $ProductCarrierURL->appendChild($Document->createCDATASection($url_carriers));

            if (!empty($carriers_tax[$id_carrier])) {
                $CarriersTax = $CarrierElement->appendChild($Document->createElement('Taxes'));
                foreach ($carriers_tax[$id_carrier] as /*$iso_code =>*/ $v) {
                    /*$CarrierTax = */
                    $CarriersTax->appendChild($ctax = $Document->createElement('Tax', $v['rate']));
                    $ctax->setAttribute('id_tax', $v['id_tax']);
                    $ctax->setAttribute('iso_code', $v['iso_code']);
                    $ctax->setAttribute('type', 'percent');
                }
            }
        }

        // Features
        $FeaturesElement = $ExportData->appendChild($Document->createElement('Features'));
        $FeatureElement = array();
        foreach ($languages as $lang) {
            $id_lang_ele = (int)$lang['id_lang'];
            $features = Feature::getFeatures($id_lang_ele);

            if (is_array($features)) {
                foreach ($features as $tab_features) {
                    $id_feature = $tab_features['id_feature'];
                    if (isset($FeatureElement[$id_feature])) {
                        //Add name lang
                        $FeatureElementName = $FeatureElement[$id_feature]->appendChild($Document->createElement('Name'));
                        $FeatureElementName->setAttribute('lang', $id_lang_ele);
                        $name = $tab_features['name'];
                        $san = $this->sanitizeXML($name);
                        if(Tools::strlen($name)!=Tools::strlen($san)){ 
                            $FeatureElementName->appendChild($Document->createCDATASection(htmlspecialchars($name, ENT_XML1, 'UTF-8')));
                        }else{
                            $FeatureElementName->appendChild($Document->createCDATASection($san));
                        }
                        if ($this->debug) {
                            echo 'Feature 1: '.$id_feature.'L'.$id_lang_ele.' N:'.$tab_features['name']. ' => '.$this->sanitizeXML($tab_features['name']);
                        }
                    } else {
                        //First round: not set feature
                        $FeatureElement[$id_feature] = $FeaturesElement->appendChild($feature_id = $Document->createElement('Feature'));
                        $feature_id->setAttribute('ID', $tab_features['id_feature']);

                        //feature values
                        $FeatureOptions = $FeatureElement[$id_feature]->appendChild($Document->createElement('Values'));
                        $tempvalue = FeatureValue::getFeatureValues(( int )$tab_features['id_feature']);
                        foreach ($tempvalue as $item) {
                            $tempoptionvalue = FeatureValue::getFeatureValueLang(( int )$item['id_feature_value']);
                            $FeatureOption = $FeatureOptions->appendChild($option_id = $Document->createElement('Value'));
                            $option_id->setAttribute('Option', $item['id_feature_value']);

                            foreach ($tempoptionvalue as $tempoptionvalue_lang) {
                                if (!in_array($tempoptionvalue_lang['id_lang'], $all_avail_lang)) {
                                    continue;
                                }
                                $FeatureOptionValue = $FeatureOption->appendChild($option_lang = $Document->createElement('Name'));
                                $option_lang->setAttribute('lang', $tempoptionvalue_lang['id_lang']);
                                
                                $name = $tempoptionvalue_lang['value'];
                                $san = $this->sanitizeXML($name);
                                if(Tools::strlen($name)!=Tools::strlen($san)){ 
                                    $FeatureOptionValue->appendChild($Document->createCDATASection(htmlspecialchars($name, ENT_XML1, 'UTF-8')));
                                }else{
                                    $FeatureOptionValue->appendChild($Document->createCDATASection($san));
                                }
                                
//                                $FeatureOptionValue->appendChild($Document->createCDATASection($this->sanitizeXML($tempoptionvalue_lang['value'])));
                            }
                        }

                        //Add name lang behide values
                        $FeatureElementName = $FeatureElement[$id_feature]->appendChild($Document->createElement('Name'));
                        $FeatureElementName->setAttribute('lang', $id_lang_ele);
                        $name = $tab_features['name'];
                        $san = $this->sanitizeXML($name);
                        if(Tools::strlen($name)!=Tools::strlen($san)){ 
                            $FeatureElementName->appendChild($Document->createCDATASection(htmlspecialchars($name, ENT_XML1, 'UTF-8')));
                        }else{
                            $FeatureElementName->appendChild($Document->createCDATASection($san));
                        }
                        if ($this->debug) {
                            echo 'Feature 2: '.$id_feature.'L'.$id_lang_ele.' N:'.$tab_features['name']. ' => '.$this->sanitizeXML($tab_features['name']);
                        }
//                        $FeatureElementName->appendChild($Document->createCDATASection($this->sanitizeXML($tab_features['name'])));
                    }
                }
            }
            unset($features);
        }

        // Units
        $UnitsElement = $ExportData->appendChild($Document->createElement('Units'));

        $dimension_unit = Configuration::get('PS_DIMENSION_UNIT');
        $weight_unit = Configuration::get('PS_WEIGHT_UNIT');
        $distance_unit = Configuration::get('PS_DISTANCE_UNIT');
        $volume_unit = Configuration::get('PS_VOLUME_UNIT');

        $UnitsElement->appendChild($weight_id = $Document->createElement('Unit', $weight_unit));
        $weight_id->setAttribute('ID', '1');
        $weight_id->setAttribute('Type', 'Weight');

        $UnitsElement->appendChild($distance_id = $Document->createElement('Unit', $distance_unit));
        $distance_id->setAttribute('ID', '2');
        $distance_id->setAttribute('Type', 'Distance');

        $UnitsElement->appendChild($volume_id = $Document->createElement('Unit', $volume_unit));
        $volume_id->setAttribute('ID', '3');
        $volume_id->setAttribute('Type', 'Volume');

        $UnitsElement->appendChild($dimension_id = $Document->createElement('Unit', $dimension_unit));
        $dimension_id->setAttribute('ID', '4');
        $dimension_id->setAttribute('Type', 'Dimension');

        // Conditions
        $arr_condition = array();
        $conditions = FeedBizProduct::getConditionField();
        $ps_conditions = array();
        preg_match_all("/'([\w ]*)'/", $conditions['Type'], $ps_conditions);
        $ConditionsElement = $ExportData->appendChild($Document->createElement('Conditions'));

        foreach ($ps_conditions[0] as $condition_key => $condition) {
            $condition = str_replace("'", "", $condition);
            $condition_key++;

            $ConditionElement = $ConditionsElement->appendChild($cond_new_id = $Document->createElement('Condition'));
            $ConditionElement->appendChild($Document->createCDATASection(str_replace("'", "", $condition)));
            $cond_new_id->setAttribute('ID', $condition_key);

            $arr_condition[$condition_key] = $condition;
        }

        // Suppliers
        $suppliers = Supplier::getSuppliers();

        if (is_array($suppliers)) {
            $SuppliersElement = $ExportData->appendChild($Document->createElement('Suppliers'));

            foreach ($suppliers as $supplier) {
                $SupplierElement = $SuppliersElement->appendChild($supplier_id = $Document->createElement('Supplier'));
                $SupplierElement->appendChild($Document->createCDATASection($supplier['name']));
                $supplier_id->setAttribute('ID', $supplier['id_supplier']);
            }
        }

        // Manufacturers
        $manufacturers = Manufacturer::getManufacturers();

        if (is_array($manufacturers)) {
            $ManufacturersElement = $ExportData->appendChild($Document->createElement('Manufacturers'));

            // $manufacturer_no = 1 ;
            foreach ($manufacturers as $manufacturer) {
                $ManufacturerElement = $ManufacturersElement->appendChild($manufacturer_id = $Document->createElement('Manufacturer'));
                $ManufacturerElement->appendChild($Document->createCDATASection($manufacturer['name']));
                $manufacturer_id->setAttribute('ID', $manufacturer['id_manufacturer']);
            }
        }

        // Categories
        $history_categories = array();
        $CategoriesElement = $ExportData->appendChild($Document->createElement('Categories'));

        $feedbiz_categories = FeedbizConfiguration::get('FEEDBIZ_CATEGORIES');
        $CategoryElement = array();
        if ($this->debug) {
            echo "---------------------CATEGORIES---------------------".$this->_cr;
            echo 'Categories selected debug : '.__LINE__."\n";
            print_r($feedbiz_categories);
        }
        foreach ($languages as $lang) {
            $Categories = Category::getCategories($lang['id_lang'], false);
            if ($this->debug) {
                 echo "Lang : ";
                 print_r($lang);
                 echo "Cat : ";
                 print_r($Categories);
            }
            if (isset($Categories) && is_array($Categories)) {
                foreach ($Categories as $Categorie) {
                    foreach ($Categorie as $Categ) {
                        $cate_parent = $Categ['infos']['id_parent'];

                        $cat_id = $Categ['infos']['id_category'];
                        if ($this->debug) {
                            echo 'CID: '.$cat_id.' p:'.$cate_parent." IN: ";
                            var_dump(in_array($cat_id, $feedbiz_categories));
                            echo "\n";
                        }
                        // get only selected category
                        if (in_array($cat_id, $feedbiz_categories) || $feedbiz_categories =='all') {
                            if ($Categ['infos']['is_root_category']) {
                                $cate_parent = 'root';
                            }

                            if (!isset($history_categories[$Categ['infos']['id_category']]) || $history_categories[$Categ['infos']['id_category']] == false) {
                                $CategoryElement[$Categ['infos']['id_category']] = $CategoriesElement->appendChild($category_id = $Document->createElement('Category'));
                                $category_id->setAttribute('ID', $Categ['infos']['id_category']);
                                $category_id->setAttribute('parent', $cate_parent);
                            }

                            $CategoryNameElement = $CategoryElement[$Categ['infos']['id_category']]->appendChild($cat_name = $Document->createElement('Name'));
                            $cat_name->setAttribute('lang', $Categ['infos']['id_lang']);
                            $CategoryNameElement->appendChild($Document->createCDATASection($Categ['infos']['name']));

                            $history_categories[$Categ['infos']['id_category']] = true;
                        }
                    }
                }
            }
        }

        // Attributes
        $history_attribute = array();
        $AttributesElement = $ExportData->appendChild($Document->createElement('Attributes'));
        $AttributeElement = array();
        $AttributeValueElement = null;

        foreach ($languages as $lang) {
            $AttributesGroups = AttributeGroup::getAttributesGroups($lang['id_lang']);

            if ($this->debug) {
                $this->debugs[] = 'Attribute Groups : '.Tools::jsonEncode($AttributesGroups);
            }

            if (is_array($AttributesGroups)) {
                foreach ($AttributesGroups as $AttributeGroup) {
                    if (!isset($history_attribute['attribute_group'][$AttributeGroup['id_attribute_group']])
                        || $history_attribute['attribute_group'][$AttributeGroup['id_attribute_group']] == false) {
                        $AttributeElement[$AttributeGroup['id_attribute_group']] = $AttributesElement->appendChild(
                            $Document->createElement('Attribute')
                        );
                        $AttributeValueElement = $AttributeElement[$AttributeGroup['id_attribute_group']]->appendChild(
                            $Document->createElement('Values')
                        );
                    }

                    $Attributes = AttributeGroup::getAttributes(
                        $lang['id_lang'],
                        $AttributeGroup['id_attribute_group']
                    );

                    foreach ($Attributes as $Attribute) {
                        if (!isset($history_attribute['attribute'][$Attribute['id_attribute']])
                            || $history_attribute['attribute'][$Attribute['id_attribute']] == false
                            && $AttributeValueElement) {
                            $AttributeElement['value'][$Attribute['id_attribute']] = $AttributeValueElement->appendChild(
                                $att_val_id = $Document->createElement('Value')
                            );
                            $att_val_id->setAttribute('Option', $Attribute['id_attribute']);

                            if ($AttributeGroup['group_type'] == 'color' && isset($Attribute['color'])) {
                                $att_val_id->setAttribute("code", $Attribute['color']);
                            }
                        }

                        $AttributeElementName = $AttributeElement['value'][$Attribute['id_attribute']]->appendChild(
                            $att_val_lang = $Document->createElement('Name')
                        );
                        $AttributeElementName->appendChild($Document->createCDATASection($Attribute['name']));

                        $att_val_lang->setAttribute("lang", $lang['id_lang']);
                        $history_attribute['attribute'][$Attribute['id_attribute']] = true;
                    }

                    $AttributeElement[$AttributeGroup['id_attribute_group']]->setAttribute(
                        'ID',
                        $AttributeGroup['id_attribute_group']
                    );
                    $AttributeElement[$AttributeGroup['id_attribute_group']]->setAttribute(
                        'type',
                        $AttributeGroup['group_type']
                    );

                    $AttributeGroupElement = $AttributeElement[$AttributeGroup['id_attribute_group']]->appendChild(
                        $name_lang = $Document->createElement('Name')
                    );
                    $AttributeGroupElement->appendChild($Document->createCDATASection($AttributeGroup['name']));
                    $name_lang->setAttribute('lang', $lang['id_lang']);

                    $history_attribute['attribute_group'][$AttributeGroup['id_attribute_group']] = true;
                } // foreach AttributesGroups
            } // if AttributesGroups
        } // if foreach language

        // Zones
        $ZonesElement = $ExportData->appendChild($Document->createElement('Zones'));
        $ZoneElement = array();

        $zones = Zone::getZones();
        foreach ($zones as $zone) {
            if (!isset($zone['id_zone']) || empty($zone['id_zone']) || !isset($zone['name']) || empty($zone['name'])) {
                continue;
            }

            $ZoneElement[$zone['id_zone']] = $ZonesElement->appendChild($zone_id = $Document->createElement('Zone'));
            $zone_id->setAttribute('ID', $zone['id_zone']);
            $zone_id->setAttribute('Name', $zone['name']);

            $Countries = Country::getCountriesByZoneId($zone['id_zone'], $id_lang);

            foreach ($Countries as $Country) {
                $ZoneElement[$zone['id_zone']]->appendChild($country_data = $Document->createElement('Country'));
                $country_data->setAttribute('ID', $Country['id_country']);
                $country_data->setAttribute('ISOCode', $Country['iso_code']);
            }
        }

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

        $GroupsElement = $ExportData->appendChild($Document->createElement('CustomerGroups'));
        $GroupElements = $GroupElement = array();

        foreach ($languages as $lang) {
            $groups = Group::getGroups($lang['id_lang']);

            if (is_array($groups)) {
                foreach ($groups as $group) {
                    if (empty($group['name'])) {
                        continue;
                    }

                    if (!isset($GroupElements[$group['id_group']])) {
                        $GroupElements[$group['id_group']] = $GroupsElement->appendChild($group_id = $Document->createElement('CustomerGroup'));
                        $group_id->setAttribute('ID', $group['id_group']);

                        if ($id_customer_group == $group['id_group']) {
                            $group_id->setAttribute('is_default', 1);
                        }
                    }

                    $GroupElement[$group['id_group']] = $GroupElements[$group['id_group']]->appendChild($name_lang = $Document->createElement('Name'));
                    $GroupElement[$group['id_group']]->appendChild($Document->createCDATASection($group['name']));
                    $name_lang->setAttribute('lang', $lang['id_lang']);
                }
            }
        } // if foreach language

        // Import unknown products as a new product
        $Orders = $ExportData->appendChild($Document->createElement('Orders'));
        $Orders->appendChild($auto_create_value = $Document->createElement('CreateProduct'));
        $auto_create_value->setAttribute('is_enabled', (int)Configuration::get('FEEDBIZ_AUTO_CREATE'));

        // Store
        if (isset($this->context->shop->id)) {
            $address = FeedBizAddress::getDefaultShopAddress($this->context->shop->id);

            $StoreAddress = $ExportData->appendChild($Document->createElement('StoreAddress'));

            $StoreAddressCompany = $StoreAddress->appendChild($Document->createElement('Company'));
            $StoreAddressCompany->appendChild($Document->createCDATASection($address->company));

            $StoreAddressAddress1 = $StoreAddress->appendChild($Document->createElement('Address1'));
            $StoreAddressAddress1->appendChild($Document->createCDATASection($address->address1));

            $StoreAddressAddress2 = $StoreAddress->appendChild($Document->createElement('Address2'));
            $StoreAddressAddress2->appendChild($Document->createCDATASection($address->address2));

            $StoreAddressCity = $StoreAddress->appendChild($Document->createElement('City'));
            $StoreAddressCity->appendChild($Document->createCDATASection($address->city));

            $StoreAddressCountry = $StoreAddress->appendChild($Document->createElement('Country'));
            $StoreAddressCountry->appendChild($Document->createCDATASection($address->country));

            $StoreAddressPostcode = $StoreAddress->appendChild($Document->createElement('Postcode'));
            $StoreAddressPostcode->appendChild($Document->createCDATASection($address->postcode));

            $StoreAddressPhone = $StoreAddress->appendChild($Document->createElement('Phone'));
            $StoreAddressPhone->appendChild($Document->createCDATASection($address->phone));
        }

        if ($this->debug) {
            echo '<pre>'.print_r($this->debugs, true).'</pre>';
        } else {
            ob_end_clean();
            header("Content-Type: application/xml; charset=utf-8");
            echo $Document->saveXML();
        }
    }

    public function sanitizeXML($string)
    {
        if (!empty($string)) {
            // remove EOT+NOREP+EOX|EOT+<char> sequence (FatturaPA)
            $string = preg_replace('/(\x{0004}(?:\x{201A}|\x{FFFD})(?:\x{0003}|\x{0004}).)/u', '', $string);

            $regex = '/(
                [\xC0-\xC1] # Invalid UTF-8 Bytes
                | [\xF5-\xFF] # Invalid UTF-8 Bytes
                | \xE0[\x80-\x9F] # Overlong encoding of prior code point
                | \xF0[\x80-\x8F] # Overlong encoding of prior code point
                | [\xC2-\xDF](?![\x80-\xBF]) # Invalid UTF-8 Sequence Start
                | [\xE0-\xEF](?![\x80-\xBF]{2}) # Invalid UTF-8 Sequence Start
                | [\xF0-\xF4](?![\x80-\xBF]{3}) # Invalid UTF-8 Sequence Start
                | (?<=[\x0-\x7F\xF5-\xFF])[\x80-\xBF] # Invalid UTF-8 Sequence Middle
                | (?<![\xC2-\xDF]|[\xE0-\xEF]|[\xE0-\xEF][\x80-\xBF]|[\xF0-\xF4]|[\xF0-\xF4][\x80-\xBF]|[\xF0-\xF4][\x80-\xBF]{2})[\x80-\xBF] # Overlong Sequence
                | (?<=[\xE0-\xEF])[\x80-\xBF](?![\x80-\xBF]) # Short 3 byte sequence
                | (?<=[\xF0-\xF4])[\x80-\xBF](?![\x80-\xBF]{2}) # Short 4 byte sequence
                | (?<=[\xF0-\xF4][\x80-\xBF])[\x80-\xBF](?![\x80-\xBF]) # Short 4 byte sequence (2)
            )/x';
            $string = preg_replace($regex, '', $string);

            $result = "";
            $current='';
            $length = Tools::strlen($string);
            for ($i=0; $i < $length; $i++) {
                $current = ord($string[$i]);
                if (($current == 0x9) ||
                    ($current == 0xA) ||
                    ($current == 0xD) ||
                    (($current >= 0x20) && ($current <= 0xD7FF)) ||
                    (($current >= 0xE000) && ($current <= 0xFFFD)) ||
                    (($current >= 0x10000) && ($current <= 0x10FFFF))) {
                    $result .= chr($current);
                } else {
                    //$ret;    // use this to strip invalid character(s)
                    // $ret .= " ";    // use this to replace them with spaces
                }
            }
            $string = $result;
        }
        return $string;
    }
}

$feedbizsettings = new FeedBizSettings();
$feedbizsettings->dispatch();

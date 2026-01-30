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
require_once(dirname(__FILE__).'/../classes/feedbiz.product.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.product_tab.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.product_tab.amazon.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.product_tab.ebay.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.product_tab.cdiscount.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.product_tab.fnac.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.product_tab.mirakl.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.product_tab.rakuten.class.php');
/**
 * Class ProductFeedBizExtManagerJSON
 */
class ProductFeedBizExtManagerJSON extends Feedbiz
{
    /**
     * @var array
     */
    private static $sustitute = array('price_override' => 'price');


    /**
     * ProductFeedBizExtManagerJSON constructor.
     */
    public function __construct()
    {
        parent::__construct();

        FeedbizContext::restore($this->context);

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
                die($this->l('Wrong Employee, please save the module configuration').' '.$id_employee.' '.print_r($employee, true));
            }

            $this->context = Context::getContext();
            $this->context->customer->is_guest = true;
            $this->context->cart = new Cart();
            $this->context->employee = $employee;
            $this->context->currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
            $this->context->customer->is_guest = true;
        }
    }

    /**
     *
     */
    public function doIt()
    {
        $field = null;
        $pass = true;
        ob_start();
        $callback = Tools::getValue('callback');
        $id_lang = Tools::getValue('id_lang');
        $id_product = Tools::getValue('id_product');
        $id_product_attribute = (int)Tools::getValue('id_product_attribute');
        $region = Tools::getValue('region');
        $id_category = Tools::getValue('feedbiz_id_category_default');
        $id_manufacturer = Tools::getValue('feedbiz_id_manufacturer');
        $id_supplier = Tools::getValue('feedbiz_id_supplier');
//        $token = Tools::getValue('feedbiz_token');
        $action = Tools::getValue('action');

//        if (!Tools::strlen($token) || $token !== Configuration::get('FEEDBIZ_PS_TOKEN')) {
//            if($token !== Configuration::get('FEEDBIZ_TOKEN')){
//                die('Wrong Token : '.$token.' psfb '.Configuration::get('FEEDBIZ_PS_TOKEN').' fb '.Configuration::get('FEEDBIZ_TOKEN'));
//            }
//        }
        if ($action == 'propagate') {
            $action = sprintf('%s-%s-%s', $action, Tools::getValue('scope'), Tools::getValue('entity'));

            $field = Tools::getValue('field');
        }

        switch ($action) {
            case 'update-field':
                $field = Tools::getValue('field');
                $value = Tools::getValue('value');
                $pass = false;

                switch ($field) {
                    case 'ean13':
                        /** @noinspection PhpMissingBreakStatementInspection */
                        // nobreak
                        // no break
                    case 'upc':
                        // nobreak
                        if (Tools::strlen($value) && !is_numeric($value)) {
                            die;
                        }
                        /** @noinspection PhpMissingBreakStatementInspection */
                        // nobreak
                        // no break
                    case 'reference':
                        // nobreak
                        /** @noinspection PhpMissingBreakStatementInspection */
                        $sql = null;

                        if ($id_product_attribute) {
                            $sql = 'UPDATE `'._DB_PREFIX_.'product_attribute` set `'.pSQL($field).'` = "'.pSQL($value).'" WHERE `id_product`='.(int)$id_product.' and `id_product_attribute` = '.(int)$id_product_attribute;
                        } elseif ($id_product) {
                            $sql = 'UPDATE `'._DB_PREFIX_.'product` set `'.pSQL($field).'` = "'.pSQL($value).'" WHERE `id_product`='.(int)$id_product;
                        }

                        if ($sql) {
                            if (Db::getInstance()->execute($sql)) {
                                $pass = true;
                            }
                        }
                        break;
                }

                if (!$pass) {
                    echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                }

                break;

            // Feedbiz
            case 'set-feedbiz':
                $fields = FeedBizProduct::getProductOptionFields();

                $product_options = array();
                $substitutions = array_flip(self::$sustitute);

                foreach ($fields as $field) {
                    if (array_key_exists($field, $substitutions)) {
                        $form_field = $substitutions[$field];
                    } else {
                        $form_field = $field;
                    }

                    $value = Tools::getValue($form_field);

                    if (Tools::strlen($value)) {
                        $product_options[$field] = $value;
                    } else {
                        $product_options[$field] = null;
                    }
                }

                $result = FeedBizProduct::setProductOptions($id_product, $id_lang, $product_options, $id_product_attribute ? $id_product_attribute : null);

                if ($result) {
                    FeedBizProduct::updateProductDate($id_product);
                } else {
                    $pass = false;
                }


                if (Feedbiz::$debug_mode) {
                    echo "<pre>\n";
                    echo "FeedBizProduct::setProductOptions returned: ".$result;
                    print_r($product_options);
                    echo "</pre>\n";
                }
                if (!$pass) {
                    echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                }

                break;

            case 'delete-feedbiz':
                $result = FeedBizProduct::deleteProductOptions($id_product, $id_lang, $id_product_attribute ? $id_product_attribute : null);

                if ($result) {
                    FeedBizProduct::updateProductDate($id_product);
                } else {
                    $pass = false;
                }

                if (Feedbiz::$debug_mode) {
                    echo "<pre>\n";
                    echo "FeedBizProduct::deleteProductOptions returned: ".$result;
                    echo "</pre>\n";
                }

                if (!$pass) {
                    echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                }

                break;

            case 'propagate-cat-feedbiz':
                $pass = true;
                $value = Tools::getValue($field);

                if (!FeedBizProduct::propagateProductOptionToCategory($id_category, $id_lang, $field, $value)) {
                    $pass = false;
                }

                if (!$pass) {
                    echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                }

                break;

            case 'propagate-shop-feedbiz':
                $pass = true;
                $value = Tools::getValue($field);

                if (!FeedBizProduct::propagateProductOptionToShop($id_lang, $field, $value)) {
                    $pass = false;
                }

                if (!$pass) {
                    echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                }

                break;

            case 'propagate-manufacturer-feedbiz':
                $pass = true;
                $value = Tools::getValue($field);

                if (!FeedBizProduct::propagateProductOptionToManufacturer($id_manufacturer, $id_lang, $field, $value)) {
                    $pass = false;
                }

                if (!$pass) {
                    echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                }

                break;

            // ebay
            case 'set-ebay':
                $fields = FeedBizProductTabEbay::getProductOptionFields();

                $product_options = array();
                $substitutions = array_flip(self::$sustitute);

                foreach ($fields as $field) {
                    if (array_key_exists($field, $substitutions)) {
                        $form_field = $substitutions[$field];
                    } else {
                        $form_field = $field;
                    }

                    $value = Tools::getValue($form_field);

                    if (Tools::strlen($value)) {
                        $product_options[$field] = $value;
                    } else {
                        $product_options[$field] = null;
                    }
                }
                $result = FeedBizProductTabEbay::setProductOptions($id_product, $region, $product_options, $id_product_attribute ? $id_product_attribute : null);

                if ($result) {
                    FeedBizProduct::updateProductDate($id_product);
                } else {
                    $pass = false;
                }

                if (Feedbiz::$debug_mode) {
                    echo "<pre>\n";
                    echo "FeedBizProductTabAmazon::setProductOptions returned: ".$result;
                    print_r($product_options);
                    echo "</pre>\n";
                }
                if (!$pass) {
                    echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                }

                break;

            case 'delete-ebay':
                $result = FeedBizProductTabEbay::deleteProductOptions($id_product, $region, $id_product_attribute ? $id_product_attribute : null);

                if ($result) {
                    FeedBizProduct::updateProductDate($id_product);
                } else {
                    $pass = false;
                }

                if (Feedbiz::$debug_mode) {
                    echo "<pre>\n";
                    echo "FeedBizProductTabEbay::deleteProductOptions returned: ".$result;
                    echo "</pre>\n";
                }
                if (!$pass) {
                    echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                }

                break;

            case 'propagate-cat-ebay':
                $pass = true;
                $value = Tools::getValue($field);

                foreach (self::adjustPropagationFields($field, $value) as $field => $value) {
                    if (!FeedBizProductTabEbay::propagateProductOptionToCategory($region, $id_category, $field, $value)) {
                        $pass = false;
                    }
                }


                if (!$pass) {
                    echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                }

                break;

            case 'propagate-shop-ebay':
                $pass = true;
                $value = Tools::getValue($field);

                foreach (self::adjustPropagationFields($field, $value) as $field => $value) {
                    if (!FeedBizProductTabEbay::propagateProductOptionToShop($region, $field, $value)) {
                        $pass = false;
                    }
                }


                if (!$pass) {
                    echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                }

                break;

            case 'propagate-manufacturer-ebay':
                $pass = true;
                $value = Tools::getValue($field);


                foreach (self::adjustPropagationFields($field, $value) as $field => $value) {
                    if (!FeedBizProductTabEbay::propagateProductOptionToManufacturer($id_manufacturer, $region, $field, $value)) {
                        $pass = false;
                    }
                }

                if (!$pass) {
                    echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                }

                break;

            case 'propagate-supplier-ebay':
                $pass = true;
                $value = Tools::getValue($field);


                foreach (self::adjustPropagationFields($field, $value) as $field => $value) {
                    if (!FeedBizProductTabEbay::propagateProductOptionToSupplier($id_supplier, $region, $field, $value)) {
                        $pass = false;
                    }
                }

                if (!$pass) {
                    echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                }

                break;

            // Amazon
            case 'set-amazon':
                $fields = FeedBizProductTabAmazon::getProductOptionFields();

                $product_options = array();
                $substitutions = array_flip(self::$sustitute);

                foreach ($fields as $field) {
                    if (array_key_exists($field, $substitutions)) {
                        $form_field = $substitutions[$field];
                    } else {
                        $form_field = $field;
                    }

                    $value = Tools::getValue($form_field);

                    if (Tools::strlen($value)) {
                        $product_options[$field] = $value;
                    } else {
                        $product_options[$field] = null;
                    }
                }
                $result = FeedBizProductTabAmazon::setProductOptions(
                    $id_product,
                    $region,
                    $product_options,
                    $id_product_attribute ? $id_product_attribute : null
                );

                if ($result) {
                    FeedBizProduct::updateProductDate($id_product);
                } else {
                    $pass = false;
                }

                if (Feedbiz::$debug_mode) {
                    echo "<pre>\n";
                    echo "FeedBizProductTabAmazon::setProductOptions returned: ".$result;
                    print_r($product_options);
                    echo "</pre>\n";
                }
                if (!$pass) {
                    echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                }

                break;

            case 'delete-amazon':
                $result = FeedBizProductTabAmazon::deleteProductOptions($id_product, $region, $id_product_attribute ? $id_product_attribute : null);

                if ($result) {
                    FeedBizProduct::updateProductDate($id_product);
                } else {
                    $pass = false;
                }

                if (Feedbiz::$debug_mode) {
                    echo "<pre>\n";
                    echo "FeedBizProductTabAmazon::deleteProductOptions returned: ".$result;
                    echo "</pre>\n";
                }
                if (!$pass) {
                    echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                }

                break;

            case 'propagate-cat-amazon':
                $pass = true;
                $value = Tools::getValue($field);

                foreach (self::adjustPropagationFields($field, $value) as $field => $value) {
                    if (!FeedBizProductTabAmazon::propagateProductOptionToCategory($id_category, $region, $field, $value)) {
                        $pass = false;
                    }
                }


                if (!$pass) {
                    echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                }

                break;

            case 'propagate-shop-amazon':
                $pass = true;
                $value = Tools::getValue($field);

                foreach (self::adjustPropagationFields($field, $value) as $field => $value) {
                    if (!FeedBizProductTabAmazon::propagateProductOptionToShop($region, $field, $value)) {
                        $pass = false;
                    }
                }


                if (!$pass) {
                    echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                }

                break;

            case 'propagate-manufacturer-amazon':
                $pass = true;
                $value = Tools::getValue($field);


                foreach (self::adjustPropagationFields($field, $value) as $field => $value) {
                    if (!FeedBizProductTabAmazon::propagateProductOptionToManufacturer($id_manufacturer, $region, $field, $value)) {
                        $pass = false;
                    }
                }

                if (!$pass) {
                    echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                }

                break;

            case 'propagate-supplier-amazon':
                $pass = true;
                $value = Tools::getValue($field);


                foreach (self::adjustPropagationFields($field, $value) as $field => $value) {
                    if (!FeedBizProductTabAmazon::propagateProductOptionToSupplier($id_supplier, $region, $field, $value)) {
                        $pass = false;
                    }
                }

                if (!$pass) {
                    echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                }

                break;

            // Cdiscount
            case 'set-cdiscount':
                $fields = FeedBizProductTabCdiscount::getProductOptionFields();
                $product_options = array();
                $substitutions = array_flip(self::$sustitute);

                foreach ($fields as $field) {
                    if (array_key_exists($field, $substitutions)) {
                        $form_field = $substitutions[$field];
                    } else {
                        $form_field = $field;
                    }

                    $value = Tools::getValue($form_field);

                    if (Tools::strlen($value)) {
                        $product_options[$field] = $value;
                    } else {
                        $product_options[$field] = null;
                    }
                }

                $result = FeedBizProductTabCdiscount::setProductOptions(
                    $id_product,
                    $region,
                    $product_options,
                    $id_product_attribute ? $id_product_attribute : null
                );

                if ($result) {
                    FeedBizProduct::updateProductDate($id_product);
                } else {
                    $pass = false;
                }

                if (Feedbiz::$debug_mode) {
                    echo "<pre>\n";
                    echo "FeedBizProductTabAmazon::setProductOptions returned: ".$result;
                    print_r($product_options);
                    echo "</pre>\n";
                }
                if (!$pass) {
                    echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                }
                break;

            case 'delete-cdiscount':
                $result = FeedBizProductTabCdiscount::deleteProductOptions($id_product, $region, $id_product_attribute ? $id_product_attribute : null);

                if ($result) {
                    FeedBizProduct::updateProductDate($id_product);
                } else {
                    $pass = false;
                }

                if (Feedbiz::$debug_mode) {
                    echo "<pre>\n";
                    echo "FeedBizProductTabCdiscount::deleteProductOptions returned: ".$result;
                    echo "</pre>\n";
                }
                if (!$pass) {
                    echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                }

                break;

            case 'propagate-cat-cdiscount':
                $pass = true;

                if ($field == 'alignment') {
                    $price_up = Tools::getValue('price_up');
                    $price_down = Tools::getValue('price_down');

                    if (!FeedBizProductTabCdiscount::propagateProductOptionToCategory($region, $id_category, 'price_up', $price_up)) {
                        $pass = false;
                        echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                    }
                    if (!FeedBizProductTabCdiscount::propagateProductOptionToCategory($region, $id_category, 'price_down', $price_down)) {
                        $pass = false;
                        echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                    }
                } else {
                    $substitutions = array_flip(self::$sustitute);

                    if (array_key_exists($field, $substitutions)) {
                        $form_field = $substitutions[$field];
                    } else {
                        $form_field = $field;
                    }

                    $value = Tools::getValue($form_field);

                    if (!FeedBizProductTabCdiscount::propagateProductOptionToCategory($region, $id_category, $field, $value)) {
                        $pass = false;
                        echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                    }
                }

                break;

            case 'propagate-shop-cdiscount':
                $pass = true;


                if ($field == 'alignment') {
                    $price_up = Tools::getValue('price_up');
                    $price_down = Tools::getValue('price_down');

                    if (!FeedBizProductTabCdiscount::propagateProductOptionToShop($region, 'price_up', $price_up)) {
                        $pass = false;
                        echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                    }
                    if (!FeedBizProductTabCdiscount::propagateProductOptionToShop($region, 'price_down', $price_down)) {
                        $pass = false;
                        echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                    }
                } else {
                    $substitutions = array_flip(self::$sustitute);

                    if (array_key_exists($field, $substitutions)) {
                        $form_field = $substitutions[$field];
                    } else {
                        $form_field = $field;
                    }

                    $value = Tools::getValue($form_field);

                    if (!FeedBizProductTabCdiscount::propagateProductOptionToShop($region, $field, $value)) {
                        $pass = false;
                        echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                    }
                }

                break;

            case 'propagate-manufacturer-cdiscount':
                $pass = true;

                if ($field == 'alignment') {
                    $price_up = Tools::getValue('price_up');
                    $price_down = Tools::getValue('price_down');

                    if (!FeedBizProductTabCdiscount::propagateProductOptionToManufacturer($id_manufacturer, $region, 'price_up', $price_up)) {
                        $pass = false;
                        echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                    }
                    if (!FeedBizProductTabCdiscount::propagateProductOptionToManufacturer($id_manufacturer, $region, 'price_down', $price_down)) {
                        $pass = false;
                        echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                    }
                } else {
                    $substitutions = array_flip(self::$sustitute);

                    if (array_key_exists($field, $substitutions)) {
                        $form_field = $substitutions[$field];
                    } else {
                        $form_field = $field;
                    }

                    $value = Tools::getValue($form_field);

                    if (!FeedBizProductTabCdiscount::propagateProductOptionToManufacturer($id_manufacturer, $region, $field, $value)) {
                        $pass = false;
                        echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                    }
                }

                break;

            case 'propagate-supplier-cdiscount':
                $pass = true;

                if ($field == 'alignment') {
                    $price_up = Tools::getValue('price_up');
                    $price_down = Tools::getValue('price_down');

                    if (!FeedBizProductTabCdiscount::propagateProductOptionToSupplier($id_supplier, $region, 'price_up', $price_up)) {
                        $pass = false;
                        echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                    }
                    if (!FeedBizProductTabCdiscount::propagateProductOptionToSupplier($id_supplier, $region, 'price_down', $price_down)) {
                        $pass = false;
                        echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                    }
                } else {
                    $substitutions = array_flip(self::$sustitute);

                    if (array_key_exists($field, $substitutions)) {
                        $form_field = $substitutions[$field];
                    } else {
                        $form_field = $field;
                    }

                    $value = Tools::getValue($form_field);

                    if (!FeedBizProductTabCdiscount::propagateProductOptionToSupplier($id_supplier, $region, $field, $value)) {
                        $pass = false;
                        echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                    }
                }

                break;

            // Fnac
            case 'set-fnac':
                $fields = FeedBizProductTabFnac::getProductOptionFields();
                $product_options = array();
                $substitutions = array_flip(self::$sustitute);

                foreach ($fields as $field) {
                    if (array_key_exists($field, $substitutions)) {
                        $form_field = $substitutions[$field];
                    } else {
                        $form_field = $field;
                    }

                    $value = Tools::getValue($form_field);

                    if (Tools::strlen($value)) {
                        $product_options[$field] = $value;
                    } else {
                        $product_options[$field] = null;
                    }
                }

                $result = FeedBizProductTabFnac::setProductOptions(
                    $id_product,
                    $region,
                    $product_options,
                    $id_product_attribute ? $id_product_attribute : null
                );

                if ($result) {
                    FeedBizProduct::updateProductDate($id_product);
                } else {
                    $pass = false;
                }

                if (Feedbiz::$debug_mode) {
                    echo "<pre>\n";
                    echo "FeedBizProductTabFnac::setProductOptions returned: ".$result;
                    print_r($product_options);
                    echo "</pre>\n";
                }
                if (!$pass) {
                    echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                }
                break;

            case 'delete-fnac':
                $result = FeedBizProductTabFnac::deleteProductOptions($id_product, $region, $id_product_attribute ? $id_product_attribute : null);

                if ($result) {
                    FeedBizProduct::updateProductDate($id_product);
                } else {
                    $pass = false;
                }

                if (Feedbiz::$debug_mode) {
                    echo "<pre>\n";
                    echo "FeedBizProductTabFnac::deleteProductOptions returned: ".$result;
                    echo "</pre>\n";
                }
                if (!$pass) {
                    echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                }

                break;

            case 'propagate-cat-fnac':
                $pass = true;

                if ($field == 'alignment') {
                    $price_up = Tools::getValue('price_up');
                    $price_down = Tools::getValue('price_down');

                    if (!FeedBizProductTabFnac::propagateProductOptionToCategory($region, $id_category, 'price_up', $price_up)) {
                        $pass = false;
                        echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                    }
                    if (!FeedBizProductTabFnac::propagateProductOptionToCategory($region, $id_category, 'price_down', $price_down)) {
                        $pass = false;
                        echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                    }
                } else {
                    $substitutions = array_flip(self::$sustitute);

                    if (array_key_exists($field, $substitutions)) {
                        $form_field = $substitutions[$field];
                    } else {
                        $form_field = $field;
                    }

                    $value = Tools::getValue($form_field);

                    if (!FeedBizProductTabFnac::propagateProductOptionToCategory($region, $id_category, $field, $value)) {
                        $pass = false;
                        echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                    }
                }

                break;

            case 'propagate-shop-fnac':
                $pass = true;

                if ($field == 'alignment') {
                    $price_up = Tools::getValue('price_up');
                    $price_down = Tools::getValue('price_down');

                    if (!FeedBizProductTabFnac::propagateProductOptionToShop($region, 'price_up', $price_up)) {
                        $pass = false;
                        echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                    }
                    if (!FeedBizProductTabFnac::propagateProductOptionToShop($region, 'price_down', $price_down)) {
                        $pass = false;
                        echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                    }
                } else {
                    $substitutions = array_flip(self::$sustitute);

                    if (array_key_exists($field, $substitutions)) {
                        $form_field = $substitutions[$field];
                    } else {
                        $form_field = $field;
                    }

                    $value = Tools::getValue($form_field);

                    if (!FeedBizProductTabFnac::propagateProductOptionToShop($region, $field, $value)) {
                        $pass = false;
                        echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                    }
                }

                break;

            case 'propagate-manufacturer-fnac':
                $pass = true;

                if ($field == 'alignment') {
                    $price_up = Tools::getValue('price_up');
                    $price_down = Tools::getValue('price_down');

                    if (!FeedBizProductTabFnac::propagateProductOptionToManufacturer($id_manufacturer, $region, 'price_up', $price_up)) {
                        $pass = false;
                        echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                    }
                    if (!FeedBizProductTabFnac::propagateProductOptionToManufacturer($id_manufacturer, $region, 'price_down', $price_down)) {
                        $pass = false;
                        echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                    }
                } else {
                    $substitutions = array_flip(self::$sustitute);

                    if (array_key_exists($field, $substitutions)) {
                        $form_field = $substitutions[$field];
                    } else {
                        $form_field = $field;
                    }

                    $value = Tools::getValue($form_field);

                    if (!FeedBizProductTabFnac::propagateProductOptionToManufacturer($id_manufacturer, $region, $field, $value)) {
                        $pass = false;
                        echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                    }
                }

                break;

            case 'propagate-supplier-fnac':
                $pass = true;

                if ($field == 'alignment') {
                    $price_up = Tools::getValue('price_up');
                    $price_down = Tools::getValue('price_down');

                    if (!FeedBizProductTabFnac::propagateProductOptionToSupplier($id_supplier, $region, 'price_up', $price_up)) {
                        $pass = false;
                        echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                    }
                    if (!FeedBizProductTabFnac::propagateProductOptionToSupplier($id_supplier, $region, 'price_down', $price_down)) {
                        $pass = false;
                        echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                    }
                } else {
                    $substitutions = array_flip(self::$sustitute);

                    if (array_key_exists($field, $substitutions)) {
                        $form_field = $substitutions[$field];
                    } else {
                        $form_field = $field;
                    }

                    $value = Tools::getValue($form_field);

                    if (!FeedBizProductTabFnac::propagateProductOptionToSupplier($id_supplier, $region, $field, $value)) {
                        $pass = false;
                        echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                    }
                }

                break;

             // Mirakl
            case 'set-mirakl':
                $fields = FeedBizProductTabMirakl::getProductOptionFields();
                $product_options = array();
                $substitutions = array_flip(self::$sustitute);

                foreach ($fields as $field) {
                    if (array_key_exists($field, $substitutions)) {
                        $form_field = $substitutions[$field];
                    } else {
                        $form_field = $field;
                    }

                    $value = Tools::getValue($form_field);

                    if (Tools::strlen($value)) {
                        $product_options[$field] = $value;
                    } else {
                        $product_options[$field] = null;
                    }
                }

                $result = FeedBizProductTabMirakl::setProductOptions(
                    $id_product,
                    $region,
                    $product_options,
                    $id_product_attribute ? $id_product_attribute : null
                );

                if ($result) {
                    FeedBizProduct::updateProductDate($id_product);
                } else {
                    $pass = false;
                }

                if (Feedbiz::$debug_mode) {
                    echo "<pre>\n";
                    echo "FeedBizProductTabMirakl::setProductOptions returned: ".$result;
                    print_r($product_options);
                    echo "</pre>\n";
                }
                if (!$pass) {
                    echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                }
                break;

            case 'delete-mirakl':
                $result = FeedBizProductTabMirakl::deleteProductOptions($id_product, $region, $id_product_attribute ? $id_product_attribute : null);

                if ($result) {
                    FeedBizProduct::updateProductDate($id_product);
                } else {
                    $pass = false;
                }

                if (Feedbiz::$debug_mode) {
                    echo "<pre>\n";
                    echo "FeedBizProductTabMirakl::deleteProductOptions returned: ".$result;
                    echo "</pre>\n";
                }
                if (!$pass) {
                    echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                }

                break;

            case 'propagate-cat-mirakl':
                $pass = true;
                $substitutions = array_flip(self::$sustitute);

                if (array_key_exists($field, $substitutions)) {
                    $form_field = $substitutions[$field];
                } else {
                    $form_field = $field;
                }

                $value = Tools::getValue($form_field);

                if (!FeedBizProductTabMirakl::propagateProductOptionToCategory($region, $id_category, $field, $value)) {
                    $pass = false;
                    echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                }

                break;

            case 'propagate-shop-mirakl':
                $pass = true;
                $substitutions = array_flip(self::$sustitute);

                if (array_key_exists($field, $substitutions)) {
                    $form_field = $substitutions[$field];
                } else {
                    $form_field = $field;
                }

                $value = Tools::getValue($form_field);

                if (!FeedBizProductTabMirakl::propagateProductOptionToShop($region, $field, $value)) {
                    $pass = false;
                    echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                }

                break;

            case 'propagate-manufacturer-mirakl':
                $pass = true;

                $substitutions = array_flip(self::$sustitute);

                if (array_key_exists($field, $substitutions)) {
                    $form_field = $substitutions[$field];
                } else {
                    $form_field = $field;
                }

                $value = Tools::getValue($form_field);

                if (!FeedBizProductTabMirakl::propagateProductOptionToManufacturer($id_manufacturer, $region, $field, $value)) {
                    $pass = false;
                    echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                }

                break;

            case 'propagate-supplier-mirakl':
                $pass = true;
                $substitutions = array_flip(self::$sustitute);

                if (array_key_exists($field, $substitutions)) {
                    $form_field = $substitutions[$field];
                } else {
                    $form_field = $field;
                }

                $value = Tools::getValue($form_field);

                if (!FeedBizProductTabMirakl::propagateProductOptionToSupplier($id_supplier, $region, $field, $value)) {
                    $pass = false;
                    echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                }

                break;


            // Rakuten
            case 'set-rakuten':
                $fields = FeedBizProductTabRakuten::getProductOptionFields();
                $product_options = array();
                $substitutions = array_flip(self::$sustitute);

                foreach ($fields as $field) {
                    if (array_key_exists($field, $substitutions)) {
                        $form_field = $substitutions[$field];
                    } else {
                        $form_field = $field;
                    }

                    $value = Tools::getValue($form_field);

                    if (Tools::strlen($value)) {
                        $product_options[$field] = $value;
                    } else {
                        $product_options[$field] = null;
                    }
                }

                $result = FeedBizProductTabRakuten::setProductOptions(
                    $id_product,
                    $region,
                    $product_options,
                    $id_product_attribute ? $id_product_attribute : null
                );

                if ($result) {
                    FeedBizProduct::updateProductDate($id_product);
                } else {
                    $pass = false;
                }

                if (Feedbiz::$debug_mode) {
                    echo "<pre>\n";
                    echo "FeedBizProductTabRakuten::setProductOptions returned: ".$result;
                    print_r($product_options);
                    echo "</pre>\n";
                }
                if (!$pass) {
                    echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                }
                break;

            case 'delete-rakuten':
                $result = FeedBizProductTabRakuten::deleteProductOptions($id_product, $region, $id_product_attribute ? $id_product_attribute : null);

                if ($result) {
                    FeedBizProduct::updateProductDate($id_product);
                } else {
                    $pass = false;
                }

                if (Feedbiz::$debug_mode) {
                    echo "<pre>\n";
                    echo "FeedBizProductTabRakuten::deleteProductOptions returned: ".$result;
                    echo "</pre>\n";
                }
                if (!$pass) {
                    echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                }

                break;

            case 'propagate-cat-rakuten':
                $pass = true;

                if ($field == 'alignment') {
                    $price_up = Tools::getValue('price_up');
                    $price_down = Tools::getValue('price_down');

                    if (!FeedBizProductTabRakuten::propagateProductOptionToCategory($region, $id_category, 'price_up', $price_up)) {
                        $pass = false;
                        echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                    }
                    if (!FeedBizProductTabRakuten::propagateProductOptionToCategory($region, $id_category, 'price_down', $price_down)) {
                        $pass = false;
                        echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                    }
                } else {
                    $substitutions = array_flip(self::$sustitute);

                    if (array_key_exists($field, $substitutions)) {
                        $form_field = $substitutions[$field];
                    } else {
                        $form_field = $field;
                    }

                    $value = Tools::getValue($form_field);

                    if (!FeedBizProductTabRakuten::propagateProductOptionToCategory($region, $id_category, $field, $value)) {
                        $pass = false;
                        echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                    }
                }

                break;

            case 'propagate-shop-rakuten':
                $pass = true;

                if ($field == 'alignment') {
                    $price_up = Tools::getValue('price_up');
                    $price_down = Tools::getValue('price_down');

                    if (!FeedBizProductTabRakuten::propagateProductOptionToShop($region, 'price_up', $price_up)) {
                        $pass = false;
                        echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                    }
                    if (!FeedBizProductTabRakuten::propagateProductOptionToShop($region, 'price_down', $price_down)) {
                        $pass = false;
                        echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                    }
                } else {
                    $substitutions = array_flip(self::$sustitute);

                    if (array_key_exists($field, $substitutions)) {
                        $form_field = $substitutions[$field];
                    } else {
                        $form_field = $field;
                    }

                    $value = Tools::getValue($form_field);

                    if (!FeedBizProductTabRakuten::propagateProductOptionToShop($region, $field, $value)) {
                        $pass = false;
                        echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                    }
                }

                break;

            case 'propagate-manufacturer-rakuten':
                $pass = true;

                if ($field == 'alignment') {
                    $price_up = Tools::getValue('price_up');
                    $price_down = Tools::getValue('price_down');

                    if (!FeedBizProductTabRakuten::propagateProductOptionToManufacturer($id_manufacturer, $region, 'price_up', $price_up)) {
                        $pass = false;
                        echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                    }
                    if (!FeedBizProductTabRakuten::propagateProductOptionToManufacturer($id_manufacturer, $region, 'price_down', $price_down)) {
                        $pass = false;
                        echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                    }
                } else {
                    $substitutions = array_flip(self::$sustitute);

                    if (array_key_exists($field, $substitutions)) {
                        $form_field = $substitutions[$field];
                    } else {
                        $form_field = $field;
                    }

                    $value = Tools::getValue($form_field);

                    if (!FeedBizProductTabRakuten::propagateProductOptionToManufacturer($id_manufacturer, $region, $field, $value)) {
                        $pass = false;
                        echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                    }
                }

                break;

            case 'propagate-supplier-rakuten':
                $pass = true;

                if ($field == 'alignment') {
                    $price_up = Tools::getValue('price_up');
                    $price_down = Tools::getValue('price_down');

                    if (!FeedBizProductTabRakuten::propagateProductOptionToSupplier($id_supplier, $region, 'price_up', $price_up)) {
                        $pass = false;
                        echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                    }
                    if (!FeedBizProductTabRakuten::propagateProductOptionToSupplier($id_supplier, $region, 'price_down', $price_down)) {
                        $pass = false;
                        echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                    }
                } else {
                    $substitutions = array_flip(self::$sustitute);

                    if (array_key_exists($field, $substitutions)) {
                        $form_field = $substitutions[$field];
                    } else {
                        $form_field = $field;
                    }

                    $value = Tools::getValue($form_field);

                    if (!FeedBizProductTabRakuten::propagateProductOptionToSupplier($id_supplier, $region, $field, $value)) {
                        $pass = false;
                        echo sprintf('%s(%d): %s', basename(__FILE__), __LINE__, $this->l('Unable to save parameters...'));
                    }
                }

                break;


            default:
                die('Unknown Action');
        }

        if ($pass&& !empty($id_product)) {
            FeedBizProduct::updateProductDate($id_product);
        }

        $json = Tools::jsonEncode(array('error' => !$pass, 'output' => ob_get_clean()));

        header('Content-Type: application/json');

        die($callback.'('.$json.')');
    }

    /**
     * @param $field
     * @param $value
     *
     * @return array
     */
    public static function adjustPropagationFields($field, $value)
    {
        $fields = array();

        switch ($field) {
            case 'bullet_point':
                foreach (array(
                             'bullet_point1',
                             'bullet_point2',
                             'bullet_point3',
                             'bullet_point4',
                             'bullet_point5'
                         ) as $field) {
                    $fields[$field] = Tools::getValue($field);
                }
                break;
            case 'gift':
                foreach (array('gift_wrap', 'gift_message') as $field) {
                    $fields[$field] = Tools::getValue($field);
                }
                break;
            case 'shipping':
                foreach (array('shipping', 'shipping_type') as $field) {
                    $fields[$field] = Tools::getValue($field);
                }
                break;
            default:
                $fields[$field] = $value;
        }
        return ($fields);
    }
}

$ext = new ProductFeedBizExtManagerJSON();
$ext->doIt();

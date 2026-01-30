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
require_once(dirname(__FILE__).'/../classes/feedbiz.log.class.php');

/**
 * Class FeedbizStockMovement
 */
class FeedbizStockMovement extends Feedbiz
{
    /**
     * @var array
     */
    private $errors = array();
    /**
     * @var
     */
    private $token;
    /**
     * @var bool
     */
    private $debug;
    /**
     * @var
     */
    private $preproduction;
    /**
     * @var string
     */
    private $statusCode;
    /**
     * @var string
     */
    private $status;

    /**
     * FeedbizStockMovement constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->debug = (bool)Configuration::get('FEEDBIZ_DEBUG') || Tools::getValue('debug');

        register_shutdown_function(array(
            $this,
            'FBShutdowFunction'
        ));

        ob_start();

        if (version_compare(_PS_VERSION_, '1.5', '>')) {
            $employee = null;
            $id_employee = (int)Configuration::get('FEEDBIZ_ID_EMPLOYEE');
            $id_employee = empty($id_employee) ? 1 : $id_employee;
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

        FeedbizContext::restore($this->context);

        $this->statusCode = '0';
        $this->status = $this->l('Fail');

        if ($this->debug) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        }
    }

    /**
     *
     */
    public function dispatch()
    {
        FeedbizTools::securityCheck();

        $id_shop = 1;

        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $id_shop = $this->context->shop->id;
            $id_warehouse = (int)Configuration::get('FEEDBIZ_WAREHOUSE');
        } else {
            $id_warehouse = null;
        }

        $id_product = (int)Tools::getValue('fbproduct');
        $id_combination = (int)Tools::getValue('fbcombination');
        $sold = (int)Tools::getValue('fbsold');

        // Check Access Tokens
        $this->token = Configuration::get('FEEDBIZ_TOKEN');
        $this->preproduction = Configuration::get('FEEDBIZ_PREPRODUCTION') ? true : false;

        $this->statusCode = '-1';
        $this->status = $this->l('Access denied');

        if ($id_product && $sold) {
            $this->statusCode = '0';
            $this->status = $this->l('Fail');

            // check combination
            $productObj = new FeedBizProduct($id_product);

            $combinationValid = !$id_combination;
            if ($id_combination) {
                if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                    $combinationObj = new Combination($id_combination);
                    $combinationValid = Validate::isLoadedObject($combinationObj);
                } else {
                    $rows = Db::getInstance()->getRow('SELECT id_product FROM `'._DB_PREFIX_.'product_attribute`
							WHERE `id_product` = '.(int)$id_product.' AND `id_product_attribute` = '.(int)$id_combination);
                    $combinationValid = (bool)$rows;
                }
            }

            $log = array(
                'Product' => $id_product,
                'Combination' => $id_combination
            );

            // check product
            if (Validate::isLoadedObject($productObj) && $combinationValid) {
                // Log before update
                if ((bool)Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') && StockAvailable::dependsOnStock($id_product) == 1) {
                    if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                        $currentQty = Product::getRealQuantity($id_product, $id_combination ? $id_combination : null, $id_warehouse, $id_shop);
                        //Case Advanced Stock Management: stock will be auto updated when order is imported.
                        if ($id_warehouse > 0) {
                            $this->errors [] = 'This product is enabled Advanced Stock Management.';

                            $this->statusCode = '1';
                            $this->status = $this->l('Success');
                            $log = array(
                                'Product' => $id_product,
                                'Combination' => $id_combination,
                                'Old Qty' => 'ADVANCED STOCK MANAGEMENT',
                                'New Qty' => ''
                            );
                            FeedBizLog::log(FeedBizLog::FILE_LOG_STOCK_MOVEMENT, $log);
                            return;
                        }
                    } else {
                        $currentQty = Product::getQuantity($id_product, $id_combination ? $id_combination : null);
                    }
                } else {
                    if ((bool)Configuration::get('PS_STOCK_MANAGEMENT')) {
                        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                            $currentQty = Product::getRealQuantity($id_product, $id_combination ? $id_combination : null, $id_warehouse, $id_shop);
                        } else {
                            $currentQty = Product::getQuantity($id_product, $id_combination ? $id_combination : null);
                        }
                    } else {
                        $currentQty = 100;
                    }
                }
                $log['Old Qty'] = $currentQty;

                // update stock
                if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                    StockAvailable::updateQuantity($id_product, $id_combination, (-1 * $sold));
                } else {
                    Product::updateQuantity(array(
                        'id_product' => $id_product,
                        'id_product_attribute' => $id_combination,
                        'cart_quantity' => $sold,
                        'out_of_stock' => 0
                    ));
                }

                // Log after updated
                if ((bool)Configuration::get('PS_STOCK_MANAGEMENT')) {
                    if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                        $currentQty = Product::getRealQuantity($id_product, $id_combination ? $id_combination : null, $id_warehouse, $id_shop);
                    } else {
                        $currentQty = Product::getQuantity($id_product, $id_combination ? $id_combination : null);
                    }
                } else {
                    $currentQty = 100;
                }

                $log['New Qty'] = $currentQty;

                $productObj->updateProductDate($id_product);

                $this->statusCode = '1';
                $this->status = $this->l('Success');
            } else {
                $log ['Error'] = 'Invalid Product Object';
                $this->errors [] = 'Invalid Product Object';
            }
            FeedBizLog::log(FeedBizLog::FILE_LOG_STOCK_MOVEMENT, $log);
        }
        // Look register_shutdown_function
    }

    /**
     *
     */
    public function FBShutdowFunction()
    {
        if (!FeedbizTools::$security_passed) {
            return false;
        }

        $Document = new DOMDocument();
        $Document->preserveWhiteSpace = true;
        $Document->formatOutput = true;
        $Document->encoding = 'utf-8';
        $Document->version = '1.0';

        $OfferPackage = $Document->appendChild($Document->createElement('Result'));
        $OfferPackage->setAttribute('ShopName', Configuration::get('PS_SHOP_NAME'));
        $OfferPackage->appendChild($StatusDoc = $Document->createElement('Status', ''));
        $StatusDoc->appendChild($Document->createElement('Code', $this->statusCode));
        $StatusDoc->appendChild($Document->createElement('Message', $this->status));
        $StatusDoc->appendChild($Document->createElement('Output', implode(", ", $this->errors)));
        $errorDoc = $StatusDoc->appendChild($Document->createElement('Error'));

        $outBuffer = ob_get_contents();
        $errorDoc->appendChild($Document->createCDATASection($outBuffer));

        header("Content-Type: application/xml; charset=utf-8");
        ob_end_clean();

        echo $Document->saveXML();
        exit(1);
    }
}

$feedBizStockMovement = new FeedBizStockMovement();
$feedBizStockMovement->dispatch();

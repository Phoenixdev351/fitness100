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
require_once(dirname(__FILE__).'/../classes/feedbiz.address.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.log.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.product_tab.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.product_tab.amazon.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.product_tab.ebay.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.product_tab.cdiscount.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.product_tab.fnac.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.product_tab.mirakl.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.product_tab.rakuten.class.php');

/**
 * Class FeedBizUpdateOptions
 */
class FeedBizUpdateOptions extends Feedbiz
{
    /**
     * @var array
     */
    private $errors = array();

    // FeedBiz auth
    /**
     * @var
     */
    private $username;
    /**
     * @var array
     */
    private $returnData  = array();
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
     * FeedBizUpdateOptions constructor.
     */
    public function __construct()
    {
        parent::__construct();

        register_shutdown_function(array(
            $this,
            'fbShutdowFunction'
        ));

        FeedbizContext::restore($this->context);

        $this->statusCode = '0';
        $this->status = $this->l('Fail');

        $this->debug = (bool)Configuration::get('FEEDBIZ_DEBUG') || Tools::getValue('debug');

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

        $id_marketplace = Tools::getValue('fbmarketplace');
        $id_shop = Tools::getValue('fbshop');
        $site = Tools::getValue('fbsite');

        ob_start();

        // Check Access Tokens
        $this->token = Configuration::get('FEEDBIZ_TOKEN');
        $this->preproduction = Configuration::get('FEEDBIZ_PREPRODUCTION') ? true : false;

        if ($this->debug) {
            @define('_PS_DEBUG_SQL_', true);
            @error_reporting(E_ALL | E_STRICT);
        }

        $FeedBizWS = new FeedBizWebService($this->username, $this->token, $this->preproduction, $this->debug);

        $params = array(
            'token' => $this->token,
            'id_shop' => $id_shop,
        );

        if (isset($id_marketplace) && $id_marketplace) {
            $params['id_marketplace'] = $id_marketplace;
        }

        if (isset($site) && $site) {
            $params['id_country'] = $site;
        }

        $offersOptions = $FeedBizWS->getUpdateOffersOptions($params, 'getUpdateOffersOptions', 'GET', true, $this->debug);

        if (!isset($offersOptions) || !$offersOptions) {
            $this->errors[] = array(
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'message' => $this->l('Get Offers Options error.')
            );
            exit;
        }

        foreach ($offersOptions as $offersOption) {
            foreach ($offersOption as $marketplace => $options) {
                switch (Tools::strtolower($marketplace)) {
                    case 'amazon':
                        foreach ($options as $region => $option) {
                            if (property_exists($option, 'Offers') && isset($option->Offers)) {
                                foreach ($option->Offers as $offers) {
                                    foreach ($offers as $offer) {
                                        if (!isset($offer['ProductId'])) {
                                            continue;
                                        }
                                        if (!isset($offer->Item)) {
                                            continue;
                                        }
                                        $id_product = (int)$offer['ProductId'];
                                        $offer_option = array();
                                        foreach ($offer->Item as $items) {
                                            $id_product_attribute = isset($items['ProductAttributeId']) ? (int)$items['ProductAttributeId'] : null;
                                            $reference = isset($items['Reference']) ? (string)$items['Reference'] : null;
                                            foreach ($items->Fields as $Fields) {
                                                foreach ($Fields as $Field => $Value) {
                                                    $offer_option[$Field] = (string)$Value['value'];
                                                }
                                            }
                                            $return = FeedBizProductTabAmazon::updateProductOptions($id_product, $offer_option, $region, $id_product_attribute);
                                            $this->returnData[$reference] = array('id_product' => $id_product, 'id_product_attribute' => $id_product_attribute, 'reference' => $reference, 'flagged' => (bool)$return);
                                        }
                                    }
                                }
                            }
                        }
                        break;
                    case 'ebay':
                        foreach ($options as $region => $option) {
                            if (property_exists($option, 'Offers') && isset($option->Offers)) {
                                foreach ($option->Offers as $offers) {
                                    foreach ($offers as $offer) {
                                        if (!isset($offer['ProductId'])) {
                                            continue;
                                        }
                                        if (!isset($offer->Item)) {
                                            continue;
                                        }
                                        $id_product = (int)$offer['ProductId'];
                                        $offer_option = array();
                                        foreach ($offer->Item as $items) {
                                            $id_product_attribute = isset($items['ProductAttributeId']) ? (int)$items['ProductAttributeId'] : null;
                                            $reference = isset($items['Reference']) ? (string)$items['Reference'] : null;
                                            foreach ($items->Fields as $Fields) {
                                                foreach ($Fields as $Field => $Value) {
                                                    $offer_option[$Field] = (string)$Value['value'];
                                                }
                                            }
                                            $return = FeedBizProductTabEbay::updateProductOptions($id_product, $offer_option, $region, $id_product_attribute);
                                            $this->returnData[$reference] = array('id_product' => $id_product, 'id_product_attribute' => $id_product_attribute, 'reference' => $reference, 'flagged' => (bool)$return);
                                        }
                                    }
                                }
                            }
                        }
                        break;
                    case 'cdiscount':
                        foreach ($options as $region => $option) {
                            if (property_exists($option, 'Offers') && isset($option->Offers)) {
                                foreach ($option->Offers as $offers) {
                                    foreach ($offers as $offer) {
                                        if (!isset($offer['ProductId'])) {
                                            continue;
                                        }
                                        if (!isset($offer->Item)) {
                                            continue;
                                        }
                                        $id_product = (int)$offer['ProductId'];
                                        $offer_option = array();
                                        foreach ($offer->Item as $items) {
                                            $id_product_attribute = isset($items['ProductAttributeId']) ?
                                                (int)$items['ProductAttributeId'] : null;
                                            $reference = isset($items['Reference']) ?
                                                (string)$items['Reference'] : null;
                                            foreach ($items->Fields as $Fields) {
                                                foreach ($Fields as $Field => $Value) {
                                                    $offer_option[$Field] = (string)$Value['value'];
                                                }
                                            }
                                            $return = FeedBizProductTabCdiscount::updateProductOptions(
                                                $id_product,
                                                $offer_option,
                                                $region,
                                                $id_product_attribute
                                            );
                                            $this->returnData[$reference] = array(
                                                'id_product' => $id_product,
                                                'id_product_attribute' => $id_product_attribute,
                                                'reference' => $reference,
                                                'flagged' => (bool)$return
                                            );
                                        }
                                    }
                                }
                            }
                        }
                        break;
                    case 'fnac':
                        foreach ($options as $region => $option) {
                            if (property_exists($option, 'Offers') && isset($option->Offers)) {
                                foreach ($option->Offers as $offers) {
                                    foreach ($offers as $offer) {
                                        if (!isset($offer['ProductId'])) {
                                            continue;
                                        }
                                        if (!isset($offer->Item)) {
                                            continue;
                                        }
                                        $id_product = (int)$offer['ProductId'];
                                        $offer_option = array();
                                        foreach ($offer->Item as $items) {
                                            $id_product_attribute = isset($items['ProductAttributeId']) ?
                                                (int)$items['ProductAttributeId'] : null;
                                            $reference = isset($items['Reference']) ?
                                                (string)$items['Reference'] : null;
                                            foreach ($items->Fields as $Fields) {
                                                foreach ($Fields as $Field => $Value) {
                                                    $offer_option[$Field] = (string)$Value['value'];
                                                }
                                            }
                                            $return = FeedBizProductTabFnac::updateProductOptions(
                                                $id_product,
                                                $offer_option,
                                                $region,
                                                $id_product_attribute
                                            );
                                            $this->returnData[$reference] = array(
                                                'id_product' => $id_product,
                                                'id_product_attribute' => $id_product_attribute,
                                                'reference' => $reference,
                                                'flagged' => (bool)$return
                                            );
                                        }
                                    }
                                }
                            }
                        }
                        break;
                    case 'mirakl':
                        foreach ($options as $sub_marketplace => $sub_marketplace_option) {
                            foreach ($sub_marketplace_option as $region => $option) {
                                if (property_exists($option, 'Offers') && isset($option->Offers)) {
                                    foreach ($option->Offers as $offers) {
                                        foreach ($offers as $offer) {
                                            if (!isset($offer['ProductId'])) {
                                                continue;
                                            }
                                            if (!isset($offer->Item)) {
                                                continue;
                                            }
                                            $id_product = (int)$offer['ProductId'];
                                            $offer_option = array();
                                            foreach ($offer->Item as $items) {
                                                $id_product_attribute = isset($items['ProductAttributeId']) ?
                                                    (int)$items['ProductAttributeId'] : null;
                                                $reference = isset($items['Reference']) ?
                                                    (string)$items['Reference'] : null;
                                                foreach ($items->Fields as $Fields) {
                                                    foreach ($Fields as $Field => $Value) {
                                                        $offer_option[$Field] = (string)$Value['value'];
                                                    }
                                                }
                                                $return = FeedBizProductTabMirakl::updateProductOptions(
                                                    $id_product,
                                                    $offer_option,
                                                    $region,
                                                    $id_product_attribute
                                                );
                                                $this->returnData[$sub_marketplace][$reference] = array(
                                                    'sub_marketplace' => $offer_option['sub_marketplace'],
                                                    'id_product' => $id_product,
                                                    'id_product_attribute' => $id_product_attribute,
                                                    'reference' => $reference,
                                                    'flagged' => (bool)$return
                                                );
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        break;
                    case 'rakuten':
                        foreach ($options as $region => $option) {
                            if (property_exists($option, 'Offers') && isset($option->Offers)) {
                                foreach ($option->Offers as $offers) {
                                    foreach ($offers as $offer) {
                                        if (!isset($offer['ProductId'])) {
                                            continue;
                                        }
                                        if (!isset($offer->Item)) {
                                            continue;
                                        }
                                        $id_product = (int)$offer['ProductId'];
                                        $offer_option = array();
                                        foreach ($offer->Item as $items) {
                                            $id_product_attribute = isset($items['ProductAttributeId']) ?
                                                (int)$items['ProductAttributeId'] : null;
                                            $reference = isset($items['Reference']) ?
                                                (string)$items['Reference'] : null;
                                            foreach ($items->Fields as $Fields) {
                                                foreach ($Fields as $Field => $Value) {
                                                    $offer_option[$Field] = (string)$Value['value'];
                                                }
                                            }
                                            $return = FeedBizProductTabRakuten::updateProductOptions(
                                                $id_product,
                                                $offer_option,
                                                $region,
                                                $id_product_attribute
                                            );
                                            $this->returnData[$reference] = array(
                                                'id_product' => $id_product,
                                                'id_product_attribute' => $id_product_attribute,
                                                'reference' => $reference,
                                                'flagged' => (bool)$return
                                            );
                                        }
                                    }
                                }
                            }
                        }
                        break;
                    case 'main':
                        if (property_exists($options, 'Offers') && isset($options->Offers)) {
                            foreach ($options->Offers as $offers) {
                                foreach ($offers as $offer) {
                                    if (!isset($offer['ProductId'])) {
                                        continue;
                                    }
                                    if (!isset($offer->Item)) {
                                        continue;
                                    }
                                    $id_product = (int)$offer['ProductId'];
                                    $id_lang = 0;
                                    $offer_option = array();
                                    foreach ($offer->Item as $items) {
                                        $id_product_attribute = isset($items['ProductAttributeId']) ? (int)$items['ProductAttributeId'] : null;
                                        $reference = isset($items['Reference']) ? (string)$items['Reference'] : null;
                                        foreach ($items->Fields as $Fields) {
                                            foreach ($Fields as $Field => $Value) {
                                                $offer_option[$Field] = (string)$Value['value'];
                                            }
                                        }
                                        $return = FeedBizProduct::updateProductOptions($id_product, $id_lang, $offer_option, $id_product_attribute);
                                        $this->returnData[$reference] = array('id_product' => $id_product, 'id_product_attribute' => $id_product_attribute, 'reference' => $reference, 'flagged' => (bool)$return);
                                    }
                                }
                            }
                        }
                        break;
                }
            }
        }
        if (isset($this->returnData) && count($this->returnData) && !empty($this->returnData)) {
            $this->statusCode = '1';
            $this->status = $this->l('Success');
        }

        // Look register_shutdown_function
    }

    /**
     *
     */
    public function fbShutdowFunction()
    {
        if (!$this->debug) {
            if (!FeedbizTools::$security_passed) {
                return false;
            }

            $outputMessages = array();
            foreach ($this->errors as $errorEle) {
                $outputMessages [] = $errorEle ['message'];
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

            if (count($outputMessages) && !empty($outputMessages)) {
                $StatusDoc->appendChild($Document->createElement('Output', implode("\n", $outputMessages)));
            }

            if (isset($this->returnData) && count($this->returnData) && !empty($this->returnData)) {
                $offersOptions = $OfferPackage->appendChild($Document->createElement('OffersOptions'));

                foreach ($this->returnData as $returnData) {
                    $offer = $offersOptions->appendChild($Document->createElement('Offer'));
                    $offer->setAttribute('ProductId', $returnData['id_product']);
                    $offer->setAttribute('ProductAttributeId', $returnData['id_product_attribute']);
                    $offer->setAttribute('Reference', $returnData['reference']);
                    $offer->setAttribute('Flagged', $returnData['flagged']);
                }
            }

            $outBuffer = ob_get_contents();

            if (Tools::strlen($outBuffer)) {
                $errorDoc = $StatusDoc->appendChild($Document->createElement('Error'));
                $errorDoc->appendChild($Document->createCDATASection($outBuffer));
            }

            header("Content-Type: application/xml; charset=utf-8");
            ob_end_clean();

            echo $Document->saveXML();
        } else {
            echo "---------------------------------------------------\n<br/>";
            echo "Errors\n<br/>";
            echo "---------------------------------------------------\n<br/>";
            echo '<pre>'.print_r($this->errors, true).'</pre> \n<br/>';
        }

        exit(1);
    }
}

$feedbizorderscancel = new FeedBizUpdateOptions();
$feedbizorderscancel->dispatch();

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

/**
 * Class FeedbizConnector
 */
class FeedbizConnector extends Feedbiz
{
    /**
     * @var string
     */
    public $feed_prefix_url;
    /**
     * @var string
     */
    public $feedbiz_ip = '';
    /**
     * @var string
     */
    public $message = '';
    /**
     * @var string
     */
    public $_cr = "<br />\n";

    /**
     * FeedbizConnector constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->feed_prefix_url = FeedbizTools::getHttpHost(true, true).
        __PS_BASE_URI__.basename(_PS_MODULE_DIR_).'/'.$this->name.'/functions/';

        FeedbizContext::restore($this->context);

        if (Feedbiz::$debug_mode) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        }
    }

    /**
     *
     */
    public function dispatch()
    {
        $Document = new DOMDocument();
        $Document->preserveWhiteSpace = true;
        $Document->formatOutput = true;
        $Document->encoding = 'utf-8';
        $Document->version = '1.0';

        if (Feedbiz::$debug_mode) {
            echo '------------------ALLOWED IP------------------'.$this->_cr;
            echo '<pre>'.print_r($this->feedbiz_ip, true).'</pre>';
            echo '------------------END ALLOWED IP------------------'.$this->_cr;
            $this->validate();
        } else {
            if ($this->validate()) {
                $DOMConnector = $Document->appendChild($Document->createElement('Connector', ''));
                $DOMConnector->setAttribute('ShopName', Configuration::get('PS_SHOP_NAME'));
                $DOMSoftware = $DOMConnector->appendChild($Document->createElement('Software', ''));
                $DOMSoftware->setAttribute('Name', 'Prestashop');
                $DOMSoftware->setAttribute('Version', _PS_VERSION_);

                $DOMUrl = $DOMConnector->appendChild($Document->createElement('Urls', ''));
                $DOMUrl->appendChild($Document->createElement('Settings', $this->feed_prefix_url.'settings.php'));
                $DOMUrl->appendChild($Document->createElement('Products', $this->feed_prefix_url.'products.php'));
                $DOMUrl->appendChild($Document->createElement('Offers', $this->feed_prefix_url.'offers.php'));
                $DOMUrl->appendChild($Document->createElement('StockMovement', $this->feed_prefix_url.'stockmovement.php'));
                $DOMUrl->appendChild($Document->createElement('OrderImport', $this->feed_prefix_url.'orders_import.php'));
                $DOMUrl->appendChild($Document->createElement('OrderCancel', $this->feed_prefix_url.'orders_cancel.php'));
                $DOMUrl->appendChild($Document->createElement('ShippedOrders', $this->feed_prefix_url.'shipped_orders.php'));
                $DOMUrl->appendChild($Document->createElement('OrderStatus', $this->feed_prefix_url.'orders_status.php'));
                $DOMUrl->appendChild($Document->createElement('UpdateOptions', $this->feed_prefix_url.'update_options.php'));
                $DOMUrl->appendChild($Document->createElement('Invoice', $this->feed_prefix_url.'invoice.php'));
                $DOMUrl->appendChild($Document->createElement('Messaging', $this->feed_prefix_url.'messaging.php'));
                $DOMUrl->appendChild($Document->createElement('StockmovementFba', $this->feed_prefix_url.'stockmovement_fba.php'));
                $DOMUrl->appendChild($Document->createElement('OrderData', $this->feed_prefix_url.'orders_data.php'));
            } else {
                $Document->appendChild($Document->createElement('Error', $this->message));
            }
            header("Content-Type: application/xml; charset=utf-8");
            echo $Document->saveXML();
        }
        exit();
    }

    /**
     * @return bool
     */
    public function validate()
    {
        $result = false;
        $fbtoken = trim(Tools::getValue('fbtoken'));
        $fbdomain = trim(Tools::getValue('fbdomain'));
        $otp = trim(Tools::getValue('otp'));

        if (Feedbiz::$debug_mode) {
            echo '------------------Validate------------------'.$this->_cr;
            echo '<br/>fbtoken<pre>'.print_r($fbtoken, true).'</pre>';
            echo '<br/>fbdomain<pre>'.print_r($fbdomain, true).'</pre>';
            echo '<br/>REMOTE_ADDR<pre>'.print_r($_SERVER ['REMOTE_ADDR'], true).'</pre>';
            echo '<br/>resolveFeedBizIps<pre>'.print_r(FeedbizTools::resolveFeedBizIps(), true).'</pre>';
            echo '<br/>otp<pre>'.print_r($otp, true).'</pre>';
            echo '<br/>Server param<pre>'.print_r($_SERVER, true).'</pre>\n';
            echo '------------------END Validate------------------'.$this->_cr;
        }

        if ($otp) {
            $preproduction = Configuration::get('FEEDBIZ_PREPRODUCTION') ? true : false;
            $otp = md5(md5($otp));
            $feedBizWS = new FeedBizWebService(null, $fbtoken, $preproduction, Feedbiz::$debug_mode);
            $params = array(
                'token' => $fbtoken,
                'otp' => $otp,
                'domain' => $fbdomain,
            );
            $connection_domains = $feedBizWS->getConnectionDomains($params);

            if (isset($connection_domains->error)) {
                $this->message = (string)$connection_domains->error;
            } else {
                Configuration::updateValue('FEEDBIZ_CONNECTION_DOMAINS', (string)$connection_domains);

                // Extra server variables that are available to cloud flare

                $pass = false;
                $fb_ips = FeedbizTools::resolveFeedBizIps();
                if (in_array($_SERVER ['REMOTE_ADDR'], $fb_ips)) {
                    $pass = true;
                }

                if (Feedbiz::$debug_mode) {
                    echo "\n".__LINE__."\n";
                    print_r(array($_SERVER ['REMOTE_ADDR'], $fb_ips), true);
                    var_dump($pass);
                    echo "\n------------------------\n";
                }

                if (!$pass) {
                    if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
                        $x_tmp = $_SERVER["HTTP_X_FORWARDED_FOR"];
                        $x_fwd = array();
                        if (strpos($x_tmp, ',')) {
                            $x_tmp = explode(',', $x_tmp);
                            foreach ($x_tmp as $x) {
                                $x_fwd[] = trim($x);
                            }
                        } else {
                            $x_fwd[] = $x_tmp;
                        }
                        if (Feedbiz::$debug_mode) {
                            echo '<br/>HTTP_X_FORWARDED_FOR<pre>'.print_r($x_fwd, true).'</pre>';
                        }
                        foreach ($x_fwd as $x) {
                            if (in_array($x, $fb_ips)) {
                                $pass = true;
                                break 1;
                            }
                        }
                    }
                }
                if (Feedbiz::$debug_mode && isset($_SERVER ['HTTP_X_FORWARDED_FOR'])) {
                    echo "\n".__LINE__."\n";
                    print_r(array($_SERVER ['HTTP_X_FORWARDED_FOR'], $fb_ips), true);
                    var_dump($pass);
                    echo "\n------------------------\n";
                }

                if (!$pass) {
                    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
                        $x_tmp = $_SERVER["HTTP_CF_CONNECTING_IP"];
                        $x_fwd = array();
                        if (strpos($x_tmp, ',')) {
                            $x_tmp = explode(',', $x_tmp);
                            foreach ($x_tmp as $x) {
                                $x_fwd[] = trim($x);
                            }
                        } else {
                            $x_fwd[] = $x_tmp;
                        }
                        if (Feedbiz::$debug_mode) {
                            echo '<br/>HTTP_CF_CONNECTING_IP<pre>'.print_r($x_fwd, true).'</pre>';
                        }
                        foreach ($x_fwd as $x) {
                            if (in_array($x, $fb_ips)) {
                                $pass = true;
                                break 1;
                            }
                        }
                    }
                }


                if (!$pass) {
                    if (isset($_SERVER["HTTP_X_REAL_IP"])) {
                        $x_tmp = $_SERVER["HTTP_X_REAL_IP"];
                        $x_fwd = array();
                        if (strpos($x_tmp, ',')) {
                            $x_tmp = explode(',', $x_tmp);
                            foreach ($x_tmp as $x) {
                                $x_fwd[] = trim($x);
                            }
                        } else {
                            $x_fwd[] = $x_tmp;
                        }
                        if (Feedbiz::$debug_mode) {
                            echo '<br/>HTTP_X_REAL_IP<pre>'.print_r($x_fwd, true).'</pre>';
                        }
                        foreach ($x_fwd as $x) {
                            if (in_array($x, $fb_ips)) {
                                $pass = true;
                                break 1;
                            }
                        }
                    }
                }

                if (!$pass) {
                    if (isset($_SERVER["HTTP_X_SUCURI_CLIENTIP"])) {
                        $x_tmp = $_SERVER["HTTP_X_SUCURI_CLIENTIP"];
                        $x_fwd = array();
                        if (strpos($x_tmp, ',')) {
                            $x_tmp = explode(',', $x_tmp);
                            foreach ($x_tmp as $x) {
                                $x_fwd[] = trim($x);
                            }
                        } else {
                            $x_fwd[] = $x_tmp;
                        }
                        if (Feedbiz::$debug_mode) {
                            echo '<br/>HTTP_X_SUCURI_CLIENTIP<pre>'.print_r($x_fwd, true).'</pre>';
                        }
                        foreach ($x_fwd as $x) {
                            if (in_array($x, $fb_ips)) {
                                $pass = true;
                                break 1;
                            }
                        }
                    }
                }

                if (Feedbiz::$debug_mode && isset($_SERVER ['HTTP_CF_CONNECTING_IP'])) {
                    echo "\n".__LINE__."\n";
                    print_r(array($_SERVER ['HTTP_CF_CONNECTING_IP'], $fb_ips), true);
                    var_dump($pass);

                    echo "\n------------------------\n";
                }

                if (!$pass) {
                    $this->message = 'The request does not come from Feed.biz.';
                } elseif (empty($fbtoken)) {
                    $this->message = 'Feed.biz token is required.';
                } else {
                    Configuration::updateValue('FEEDBIZ_TOKEN', $fbtoken);
                    Configuration::updateValue('FEEDBIZ_DOMAIN', $feedBizWS->URL_preprod);

                    $result = true;
                }
            }
        }

        return $result;
    }
}

$feedbizConnector = new FeedbizConnector();
$feedbizConnector->dispatch();

<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Feed.Biz, Ltd.
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Feed.Biz, Ltd. is strictly forbidden.f
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

class FeedBizWebService
{
    protected $login       = null;
    protected $password    = null;
    protected $preprod     = false;
    protected $debug       = false;

    public function __construct($login, $password, $preprod = false, $debug = false)
    {
        $this->debug = $debug;
        $this->login = $login;
        $this->password = $password;
        $this->preprod = $preprod;
        $this->URL_preprod = Configuration::get('FEEDBIZ_DOMAIN');
        $this->URL = $this->URL_preprod.'/webservice/feedbiz.php';
    }

    public function checkConnection()
    {
        $params = array(
            'token' => $this->password
        );

        $xmlDom = $this->callWs($params, 'check_connection');

        if (isset($xmlDom->connection->return)) {
            return (bool)$xmlDom->connection->return;
        }

        return (false);
    }

    public function getConnectionDomains($params)
    {
        if (isset($params['domain']) && Tools::strlen($params['domain'])) {
            $this->URL_preprod = $params['domain'];
        }

        $xmlDom = $this->callWs($params, 'get_connection_domains');

        if (isset($xmlDom->Domains->return)) {
            return $xmlDom->Domains->return;
        }

        return ($xmlDom);
    }

    public function getConfigurations()
    {
        $params = array(
            'token' => $this->password
        );

        $xmlDom = $this->callWs($params, 'get_configurations');

        return ($xmlDom);
    }

    public function getOrder($params, $API, $method, $returnXML = true, $debug = false)
    {
        $this->debug = $debug;

        if ($API != 'getOrders') {
            $API = 'getOrders';
        }

        $xmlDom = $this->callWs($params, $API, $returnXML, $method);

        if (isset($xmlDom->error)) {
            echo $xmlDom->error;
        }

        if (isset($xmlDom->Orders->return)) {
            return $xmlDom->Orders->return;
        }

        return (false);
    }

    public function sendOrder($orders, $returnXML = true)
    {
        $params = array(
            'token' => $this->password
        );

        $method = array();
        $method['CURLOPT_POST'] = 1;
        $method['CURLOPT_POSTFIELDS'] = 'orders='.urlencode($orders).'&token='.$this->password;

        $xmlDom = $this->callWs($params, 'setOrders', $returnXML, $method);

        if (isset($xmlDom->error)) {
            echo $xmlDom->error;
        }

        if (isset($xmlDom->Order->return)) {
            return $xmlDom->Order->return;
        }

        return ($xmlDom);
    }


    public function cancelOrder($params, $API, $method, $returnXML = true, $debug = false)
    {
        $this->debug = $debug;

        if ($API != 'cancelOrder') {
            $API = 'cancelOrder';
        }

        $xmlDom = $this->callWs($params, $API, $returnXML, $method);

        if (isset($xmlDom->error)) {
            echo $xmlDom->error;
        }

        if (isset($xmlDom->Order->return)) {
            return $xmlDom->Order->return; // ->pass
        }

        return ($xmlDom);
    }

    public function getUpdateOffersOptions($params, $API, $method, $returnXML = true, $debug = false)
    {
        $this->debug = $debug;

        if ($API != 'getUpdateOffersOptions') {
            $API = 'getUpdateOffersOptions';
        }

        $xmlDom = $this->callWs($params, $API, $returnXML, $method);

        if (isset($xmlDom->error)) {
            echo $xmlDom->error;
        }

        if (isset($xmlDom->OffersOptions->return)) {
            return $xmlDom->OffersOptions->return;
        }

        return (false);
    }

    public function stockmovementFba($params, $API, $method, $returnXML = true, $debug = false)
    {
        $this->debug = $debug;

        if ($API != 'stockmovementFba') {
            $API = 'stockmovementFba';
        }

        $xmlDom = $this->callWs($params, $API, $returnXML, $method);

        if (isset($xmlDom->error)) {
            echo $xmlDom->error;
        }

        if (isset($xmlDom->ListStock)) {
            return $xmlDom->ListStock;
        }

        return (false);
    }


    public function getCustomerMessages($params, $method, $returnXML = true, $debug = false)
    {
        $this->debug = $debug;
        $API = 'getCustomerMessages';

        $xmlDom = $this->callWs($params, $API, $returnXML, $method);

        if (isset($xmlDom->error)) {
            echo $xmlDom->error;
        }

        if (isset($xmlDom->CustomerMessages->return)) {
            return $xmlDom->CustomerMessages->return;
        }

        return (false);
    }

    public function getTemplates($params, $method, $returnXML = true, $debug = false)
    {
        $this->debug = $debug;
        $API = 'getTemplates';

        $xmlDom = $this->callWs($params, $API, $returnXML, $method);

        if (isset($xmlDom->error)) {
            echo $xmlDom->error;
        }

        if (isset($xmlDom->Template->return)) {
            return $xmlDom->Template->return;
        }

        return (false);
    }

    public function ping($params)
    {
        $last_ping = Configuration::get('FEEDBIZ_LAST_PING');
        if (empty($last_ping) || $last_ping > (time() - (60*60))) {
            if (Feedbiz::$debug_mode) {
                echo "<pre>\n";
                printf('%s - %s::%s()/#%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
                echo 'ping pass last :'.print_r($last_ping, true);
                echo "</pre>";
            }
            return true;
        }
        $API = 'ping';

        if (is_array($params) && count($params) && array_key_exists('host', $params)) {
            $ip = $params['ip'];
            $params = array();
        } else {
            if (Feedbiz::$debug_mode) {
                echo "<pre>\n";
                printf('%s - %s::%s()/#%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
                echo '$params false no host :'.print_r($params, true);
                echo "</pre>";
            }
            return(false);
        }
        $ping = $this->callWs($params, $API, true);

        if (!$ping instanceof SimpleXMLElement) {
            if (Feedbiz::$debug_mode) {
                echo "<pre>\n";
                printf('%s - %s::%s()/#%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
                echo 'Ping fail :'.print_r($ping, true);
                echo "</pre>";
            }
            return(false);
        }
        $remote_ip = (string)$ping->primary_ip;

        if (Feedbiz::$debug_mode) {
            echo "<pre>\n";
            printf('%s - %s::%s()/#%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
            echo 'Ping Result:'.print_r($remote_ip, true);
            echo "</pre>";
        }
        if ((string)$ip === (string)$remote_ip) {
            Configuration::updateValue('FEEDBIZ_LAST_PING', time());
            return(true);
        } else {
            if (Feedbiz::$debug_mode) {
                echo "<pre>\n";
                printf('%s - %s::%s()/#%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
                echo 'Ping not equal:'.print_r(array($ip,$remote_ip), true);
                echo "</pre>";
            }
            return (false);
        }
    }

    public function setCustomerSurvey($data)
    {
        $params = array('debug' => 0);

        if (isset($data['survey_marketplaces']) && is_array($data['survey_marketplaces'])) {
            $data['survey_marketplaces'] = implode(", ", $data['survey_marketplaces']);
        } else {
            $data['survey_marketplaces'] = '';
        }

        $url = $data['url_fb_customer_survey'];

        $method = array();
        $method['CURLOPT_POST'] = 1;
        $method['CURLOPT_POSTFIELDS'] = $data;

        $xmlDom = $this->callWs($params, 'setCustomerSurvey', true, $method, $url);

        if (isset($xmlDom->error)) {
            echo $xmlDom->error;
        }

        if (isset($xmlDom->CustomerSurvey->return)) {
            if (isset($xmlDom->CustomerSurvey->return->pass) && $xmlDom->CustomerSurvey->return->pass == 'true') {
                return (true) ;
            }
        }

        return (false);
    }

    public function getCustomerSurvey($url, $email)
    {
        $params = array(
            'email' => $email,
        );
        $xmlDom = $this->callWs($params, 'GetCustomerSurvey', false, 'GET', $url);

        return ($xmlDom);
    }

    protected function callWs($params = '', $API = '', $returnXML = true, $method = null, $url = null, $email = null)
    {
        $uri = null;
        $curlOptions = array();

        $canonicalized_query = array();
        if (!empty($params) && is_array($params)) {
            foreach ($params as $param => $value) {
                $param = str_replace("%7E", "~", rawurlencode($param));
                $value = str_replace("%7E", "~", rawurlencode($value));
                $canonicalized_query[] = $param."=".$value;
            }
        }

        $canonicalized_query = implode("&", $canonicalized_query);

        switch ($API) {
            case 'getOrders':
                $uri = '/webservice/WsFeedBiz/api/Order/getOrders';
                break;
            case 'setOrders':
                $uri = '/webservice/WsFeedBiz/api/Order/setOrders';
                break;
            case 'cancelOrder':
                $uri = '/webservice/WsFeedBiz/api/Order/cancelOrder';
                break;
            case 'check_connection':
                $uri = '/webservice/WsFeedBiz/api/Connection/checkConnection';
                break;
            case 'get_connection_domains':
                $uri = '/webservice/WsFeedBiz/api/Connection/getDomains';
                break;
            case 'get_configurations':
                $uri = '/webservice/WsFeedBiz/api/Marketplace/getConfigurations';
                break;
            case 'getUpdateOffersOptions':
                $uri = '/webservice/WsFeedBiz/api/OffersOptions/getUpdateOffersOptions';
                break;
            case 'stockmovementFba':
                $uri = '/webservice/WsFeedBiz/api/Stock/stockmovementFba';
                break;
            case 'getCustomerMessages':
                $uri = '/webservice/WsFeedBiz/api/CustomerMessages/getCustomerMessages';
                break;
            case 'getTemplates':
                $uri = '/webservice/WsFeedBiz/api/CustomerMessages/getTemplates';
                break;
            case 'ping':
                $uri = '/webservice/WsFeedBiz/api/ping';
                break;
            case 'setCustomerSurvey':
                $uri = '/webservice/WsFeedBiz/api/CustomerSurvey/setCustomerSurvey';
                break;
            case 'GetCustomerSurvey':
                $uri = '/webservice/WsFeedBiz/api/CustomerSurvey/getCustomerSurvey';
                break;
        }

        if (isset($url) && !empty($url)) {
            $curlOptions[CURLOPT_URL] = rtrim($url, '/').$uri."?".$canonicalized_query;
        } else {
            $curlOptions[CURLOPT_URL] = rtrim($this->URL_preprod, '/').$uri."?".$canonicalized_query;
        }

        if (!isset($uri) || empty($uri)) {
            return (false);
        }

        ksort($params);
        if ($this->debug) {
            echo "\n".__FILE__.' '.__FUNCTION__.' '.__LINE__.$email."\n";
        }
        $curlOptions[CURLOPT_RETURNTRANSFER] = true;

        if (isset($method['CURLOPT_POST']) && $method['CURLOPT_POST'] == 1) {
            $curlOptions[CURLOPT_POST] = $method['CURLOPT_POST'];
            $curlOptions[CURLOPT_POSTFIELDS] = $method['CURLOPT_POSTFIELDS'];
        } elseif (isset($method) && !empty($method)) { // get
            $curlOptions[CURLOPT_CUSTOMREQUEST] = $method;
        }

        $curlOptions[CURLOPT_SSL_VERIFYPEER] = true;
        $curlOptions[CURLOPT_SSL_VERIFYHOST] = 2;
        $curlOptions[CURLOPT_VERBOSE] = false;
        $curlOptions[CURLOPT_CAINFO] =  FeedbizCertificates::getCertificate();

        // Choose safest SSL connection depending on availability
        if (defined('CURL_SSLVERSION_TLSv1_2')) {
            $curlOptions[CURLOPT_SSLVERSION] = CURL_SSLVERSION_TLSv1_2;
        } elseif (defined('CURL_SSLVERSION_TLSv1_1')) {
            $curlOptions[CURLOPT_SSLVERSION] = CURL_SSLVERSION_TLSv1_1;
        } elseif (defined('CURL_SSLVERSION_TLSv1')) {
            $curlOptions[CURLOPT_SSLVERSION] = CURL_SSLVERSION_TLSv1;
        } else {
            $curlOptions[CURLOPT_SSLVERSION] = 1;
        }

        if ($this->debug) {
            echo "<pre>\n";
            print_r($curlOptions);
            echo "</pre>\n";
        }

        if (Feedbiz::$debug_mode) {
            echo "<pre>\n";
            printf('%s - %s::%s()/#%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
            printf('cert_file: %s'."\n", print_r($curlOptions, true));
            echo "</pre>";
        }

        $curlHandle = curl_init();

        curl_setopt_array($curlHandle, $curlOptions);

        if (!$result = curl_exec($curlHandle)) {
            $pass = false;
        } else {
            $pass = true;
        }

        if (Feedbiz::$debug_mode) {
            echo "<pre>\n";
            printf('%s - %s::%s()/#%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
            echo "Params: \n";
            echo nl2br(print_r($curlOptions, true));
            echo "CURL Data: \n";
            echo nl2br(print_r($params, true));
            echo nl2br(print_r(curl_getinfo($curlHandle), true));  // get error info
            echo "</pre>\n";
        }

        if (!$pass) {
            if ($this->debug) {
                echo "\n".__FILE__.' '.__FUNCTION__.' '.__LINE__."\n";
                var_dump($pass);
                var_dump($result);
                print_r($params);
                var_dump(curl_errno($curlHandle));
            }
            if (curl_errno($curlHandle) == 60) {
                FeedbizCertificates::updateCertificate();
            }
            if ($this->debug) {
                echo "SSL CERTIFICATE 's Feed.biz expired !! \n"
                               ."\n". ' CURL error:('.curl_errno($curlHandle).')'.curl_error($curlHandle);
            }
            return (false);
        }

        if ($this->debug) {
            print_r($result);
        }

        curl_close($curlHandle);

        if ($returnXML) {
            try {
                $xmlDom = new SimpleXMLElement($result);
                return $xmlDom;
            } catch (Exception $e) {
                print "Exception Caught: ".$e->getMessage()."\n";
            }
        } else {
            return $result;
        }
        return (false);
    }
}

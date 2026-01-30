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

if (!class_exists('Feedbiz')) {
    require_once dirname(__FILE__).'/../feedbiz.php';
}

class FeedbizTools extends Feedbiz
{
    public static $security_passed = true;

    public static function displayDate($date, $id_lang = null, $full = false, $separator = '-')
    {
        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $id_lang = null;
            return (Tools::displayDate($date, $id_lang, $full));
        } else {
            return (Tools::displayDate($date, $id_lang, $full, $separator));
        }
    }

    public static function oldest()
    {
        $sql = 'SELECT MIN(date_add) as date_min FROM `' . _DB_PREFIX_ . 'product`;';

        if (($rq = Db::getInstance()->executeS($sql)) && is_array($rq)) {
            $result = array_shift($rq);
            return (str_replace('-', '/', $result['date_min']));
        } else {
            return (false);
        }
    }

    public static function getFriendlyUrl($text)
    {
        $text = html_entity_decode($text);
        $text = preg_replace(array('/&szlig;/', '/&(..)lig;/', '/&([aouAOU])uml;/', '/&(.)[^;]*;/'), array(
            'ss',
            "$1",
            "$1" . 'e',
            "$1"
        ), $text);
        $text = preg_replace('/[\x00-\x1F\x21-\x2B\x3A-\x3F\x5B-\x60\x7B-\x7F]/', '', $text); // remove non printable
        $text = preg_replace('/[ \t]+/', '-', $text);
        $text = str_replace(array('_', ',', '.', '/', '+', '?', '&', '='), '-', $text);
        return Tools::strtolower(trim($text));
    }

    public static function getProductImages($id_product, $id_product_attribute, $id_lang)
    {
        $product = new Product($id_product);
        $id_images = null;

        if ((int)$id_product_attribute) {
            $images = $product->getCombinationImages($id_lang);
            $id_images = array();

            if (is_array($images) && count($images)) {
                if (isset($images[$id_product_attribute])) {
                    foreach ($images[$id_product_attribute] as $key => $image) {
                        $id_images[$key]['id'] = $image['id_image'];
                    }
                } else {
                    $id_images = false;
                }
            } else {
                $images = $product->getImages($id_lang);
                if (is_array($images) && count($images)) {
                    foreach ($images as $key => $image) {
                        $id_images[$key]['id'] = $image['id_image'];
                        $id_images[$key]['default'] = $image['cover'];
                    }
                } else {
                    $id_images = false;
                }
            }
        } else {
            $images = $product->getImages($id_lang);
            if (is_array($images) && count($images)) {
                foreach ($images as $key => $image) {
                    $id_images[$key]['id'] = $image['id_image'];
                    $id_images[$key]['default'] = $image['cover'];
                }
            } else {
                $id_images = false;
            }
        }

        $images = array();

        if ($id_images) {
            foreach ($id_images as $key_images => $id_image) {
                $images[$key_images]['id'] = $id_image['id'];
                $images[$key_images]['name'] = self::getImageUrl($id_image['id'], $id_product);
                if (isset($id_image['default'])) {
                    $images[$key_images]['default'] = $id_image['default'];
                }
            }
        }

        return ($images);
    }

    public static function getImageUrl($id_image, $productid)
    {
        $image_type = null;
        $ext = 'jpg';
        $image_obj = new Image($id_image);

        // PS > 1.4.3
        if (method_exists($image_obj, 'getExistingImgPath')) {
            $img_path = $image_obj->getExistingImgPath();
            $imageurl = $img_path;
        } else {
            $imageurl = $productid.'-'.$id_image;
        }

        // Always take the biggest image available (the original one)
        if (method_exists('ImageType', 'getFormatedName')) {
            $image_type = Configuration::get('FEEDBIZ_IMAGE_TYPE');
        }

        if (Tools::strlen($image_type)) {
            $imageurl = sprintf('%s-%s.%s', $imageurl, $image_type, $ext);
        } else {
            $imageurl = sprintf('%s.%s', $imageurl, $ext);
        }
        return $imageurl;
    }

    // Prestashop 1.2 / 1.3 compat
    public static function moduleIsInstalled($moduleName)
    {
        if (method_exists('Module', 'isInstalled')) {
            return (Module::isInstalled($moduleName));
        } else {
            Db::getInstance()->executeS('
                SELECT `id_module`
                FROM `' . _DB_PREFIX_ . 'module`
                WHERE `name` = \'' . pSQL($moduleName) . '\'');
            return (bool)Db::getInstance()->numRows();
        }
    }

    /*
     * For PS 1.2 compatibility
     */

    public static function getHttpHost($http = false, $entities = false, $ignore_port = false)
    {
        if (method_exists('Tools', 'getHttpHost')) {
            return (Tools::getHttpHost($http, $entities, $ignore_port));
        } else {
            $host = (isset($_SERVER['HTTP_X_FORWARDED_HOST']) ?
                $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST']);
            if ($entities) {
                $host = htmlspecialchars($host, ENT_COMPAT, 'UTF-8');
            }
            if ($http) {
                $host = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') . $host;
            }
            return $host;
        }
    }

    public static function getCustomerName($fullname)
    {
        $result = array();

        $result['company'] = '';

        if (preg_match('/,|\//', $fullname)) {
            // Cas ou le client mets Nom Prenom, Company
            //
            $parts = preg_split('/,|\//', $fullname);
            $var = trim(mb_substr($parts[0], 0, 32));
            $result['company'] = trim(mb_substr($parts[1], 0, 32));
        } else {
            $var = mb_substr($fullname, 0, 32);
        }

        $var = preg_replace('/[0-9!<>,;?=+()@#"{}_$%:]/', '', $var);

        $var = trim($var);
        $var = mb_substr($var, 0, 32);
        $var1 = explode(' ', $var);
        $sz = count($var1) - 1;

        $firstname = $var1[$sz];
        unset($var1[$sz]);
        $lastname = implode(' ', $var1);

        $firstname = empty($firstname) ? 'unknown' : $firstname;
        $lastname = empty($lastname) ? 'unknown' : $lastname;

        $result['firstname'] = Tools::ucfirst($firstname);
        $result['lastname'] = Tools::ucfirst($lastname);

        return ($result);
    }


    public static function resolveFeedBizIps()
    {
        static $allowedIP = array();
        $conf_feedbiz_domains = Configuration::get('FEEDBIZ_CONNECTION_DOMAINS');
        $feedbiz_domains = Tools::jsonDecode($conf_feedbiz_domains, true);

        if (!is_array($allowedIP) || !count($allowedIP)) {
            foreach (Feedbiz::$feedbiz_domains as $domain) { // original domains
                $allowedIP[$domain] = gethostbyname($domain);
            }
            if (isset($feedbiz_domains)) {
                foreach ($feedbiz_domains as $domain) {
                    $allowedIP[$domain] = gethostbyname($domain);
                }
            }
        }
        return $allowedIP;
    }

    public static function checkToken($token)
    {
        $feedbizTokens = Configuration::get('FEEDBIZ_TOKEN');
        if (!$token) {
            return (false);
        }
        if ((string)$feedbizTokens !== (string)$token) {
            return (false);
        }
        return (true);
    }

    public static function securityCheck()
    {
        static $security_passed = false;
        $code = null;
        $fbtoken = Tools::getValue('fbtoken', 'wrong');
        $token = Configuration::get('FEEDBIZ_TOKEN');
        $pass = true;
        $allowedIps = self::resolveFeedBizIps();
        $message = null;
        $allowedHosts = null;

        $remote_ip = $_SERVER ['REMOTE_ADDR'];
        $allowedHosts = array_flip($allowedIps);
        if (strpos($remote_ip, ',')!==false) {
            $tmp = explode(',', $remote_ip);
            $out = array();
            foreach ($tmp as $t) {
                $t = trim($t);
                $out[$t] = $t;
            }
            $ip='';
            foreach ($out as $i) {
                if (isset($allowedHosts[$ip])) {
                    if (empty($ip)) {
                        $ip = $i;
                    } elseif (!empty($ip) && $ip != $i) {
                        $ip = $i;
                        if (Feedbiz::$debug_mode) {
                            printf('%s - %s::%s()/#%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
                            echo 'Multitle remote IP:'.print_r($out, true).' Current : '.$i."\n";
                        }
                    }
                }
            }

            if (!empty($ip)) {
                $remote_ip = $ip;
            }
        }
        $ip_pass=false;
        if (in_array($remote_ip, $allowedIps)) {
            $ip_pass=true;
        }


        if (!$ip_pass) {
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
                    if (in_array($x, $allowedIps)) {
                        $ip_pass   = true;
                        $remote_ip = $x;
                        break 1;
                    }
                }
            }
        }

        if (!$ip_pass) {
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
                    if (in_array($x, $allowedIps)) {
                        $ip_pass   = true;
                        $remote_ip = $x;
                        break 1;
                    }
                }
            }
        }

        if (!$ip_pass) {
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
                    if (in_array($x, $allowedIps)) {
                        $ip_pass   = true;
                        $remote_ip = $x;
                        break 1;
                    }
                }
            }
        }

        if (!$ip_pass) {
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
                    if (in_array($x, $allowedIps)) {
                        $ip_pass   = true;
                        $remote_ip = $x;
                        break 1;
                    }
                }
            }
        }

        if (!$ip_pass) {
            if (isset($_SERVER["HTTP_REMOTE_ADDR"])) {
                $x_tmp = $_SERVER["HTTP_REMOTE_ADDR"];
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
                    echo '<br/>HTTP_REMOTE_ADDR<pre>'.print_r($x_fwd, true).'</pre>';
                }
                foreach ($x_fwd as $x) {
                    if (in_array($x, $allowedIps)) {
                        $ip_pass   = true;
                        $remote_ip = $x;
                        break 1;
                    }
                }
            }
        }

        if (Feedbiz::$debug_mode) {
            echo "<pre>\n";
            printf('%s - %s::%s()/#%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
            echo 'REMOTE_ADDR:'.print_r($remote_ip, true)."\n";
            echo 'Allowed IPs:'.print_r($allowedIps, true)."\n";
            echo 'Server param:'.print_r($_SERVER, true)."\n";
            echo 'Token check:'.print_r(array($fbtoken,$token), true)."\n";
            echo "</pre>";
        }

        if (!empty($token) && (string)$fbtoken !== (string)$token) {
            $code = '01';
            $message = 'Invalid Token';
            $pass = false;
        } elseif (!in_array($remote_ip, $allowedIps)) {
            $code = '02';
            $message = 'Invalid originating IP';
            $pass = false;
        }

        if (!$security_passed) {
            $username = '';
            $feedbiz_token = Configuration::get('FEEDBIZ_TOKEN');
            $preproduction = Configuration::get('FEEDBIZ_PREPRODUCTION');


            $host = isset($allowedHosts[$remote_ip]) ? $allowedHosts[$remote_ip] : null;
            $ip = isset($allowedIps[$host]) ? $allowedIps[$host] : null;

            if (Feedbiz::$debug_mode) {
                echo "<pre>\n";
                printf('%s - %s::%s()/#%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
                echo 'Allowed Hosts:'.print_r($allowedHosts, true);
                echo 'Resolved Host:'.print_r($host, true);
                echo "</pre>";
            }

            $ping = false;

            if ($host !== null) {
                $params = array();
                $params['host'] = $host;
                $params['ip'] = $ip;

                $ws = new FeedBizWebService($username, $feedbiz_token, $preproduction);
                if (Feedbiz::$debug_mode) {
                    echo "<pre>\n";
                    printf('%s - %s::%s()/#%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
                    echo 'Remote IP:'.$ip."\n";
                    echo 'Ping IP:'.$ping."\n";
                    echo 'User name:'.$username."\n";
                    echo 'Token:'.$feedbiz_token."\n";
                    echo 'params :'.print_r($params)."\n";
                    echo "</pre>";
                }
                $ping = $ws->ping($params);
            }

            if ($ping !== true) {
//                $message = 'Hack attempt';
//                $code = '03';
                $security_passed =$pass = true;
            } else {
                $security_passed = true;
            }
        }

        if (!$pass) {
            if (!Feedbiz::$debug_mode) {
                $Document = new DOMDocument();
                $Document->preserveWhiteSpace = true;
                $Document->formatOutput = true;
                $Document->encoding = 'utf-8';
                $Document->version = '1.0';

                $ExportData = $Document->appendChild($Document->createElement('ExportData', ''));
                $ExportData->setAttribute('ShopName', Configuration::get('PS_SHOP_NAME'));
                $ExportData->appendChild($errorDoc = $Document->createElement('Status', ''));
                $errorDoc->appendChild($Document->createElement('Code', $code));
                $errorDoc->appendChild($Document->createElement('Message', $message));

                header("Content-Type: application/xml; charset=utf-8");
                echo $Document->saveXML();
                FeedbizTools::$security_passed = false;
            } else {
                echo "<pre>\n";
                printf('%s - %s::%s()/#%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
                echo 'Message: '.$code .'-'.$message;
                echo 'Pass: '.$pass;
                echo "</pre>";
            }
            exit();
        }
    }

    public static function isDirWriteable($path)
    {
        $path = rtrim($path, '/\\');

        $testfile = sprintf('%s%stestfile_%s.chk', $path, DIRECTORY_SEPARATOR, uniqid());
        $timestamp = time();

        if (@file_put_contents($testfile, $timestamp)) {
            $result = trim(Tools::file_get_contents($testfile));
            @unlink($testfile);

            if ((int)$result == (int)$timestamp) {
                return (true);
            }
        }

        return (false);
    }

    public static function showColumnExists($table)
    {
        $fields = array();

        $sql = 'SHOW COLUMNS FROM  `' . pSQL($table) . '` IN `' . pSQL(_DB_NAME_) . '`';

        $query = Db::getInstance()->executeS($sql, true, false);

        if (!is_array($query) || !count($query)) {
            return (null);
        }

        foreach ($query as $row) {
            $fields[$row['Field']] = 1;
        }

        return $fields;
    }

    public static function fieldExists($table, $field)
    {
        static $field_exists = array();
        $fields = array();

        if (isset($field_exists[$table . $field])) {
            return $field_exists[$table . $field];
        }

        $sql = 'SHOW COLUMNS FROM  `' . pSQL($table) . '` IN `' . pSQL(_DB_NAME_) . '`';

        // Check if exists
        $query = Db::getInstance()->executeS($sql, true, false);

        if (!is_array($query) || !count($query)) {
            return (null);
        }

        foreach ($query as $row) {
            $fields[$row['Field']] = 1;
        }

        if (isset($fields[$field])) {
            $field_exists[$table . $field] = true;
        } else {
            $field_exists[$table . $field] = false;
        }

        return $field_exists[$table . $field];
    }

    public static function tableExists($table, $cache = true)
    {
        static $table_exists = array();
        static $show_tables_content = null;

        if (isset($table_exists[$table]) && $table_exists[$table]) {
            return $table_exists[$table];
        }

        // Check if exists
        if (!$cache || $show_tables_content === null) {
            $tables = array();

            $query_result = Db::getInstance()->ExecuteS('SHOW TABLES FROM `' . pSQL(_DB_NAME_) . '`', true, false);

            if (!is_array($query_result) || !count($query_result)) {
                return (null);
            }

            $show_tables_content = $query_result;
        }

        foreach ($show_tables_content as $rows) {
            foreach ($rows as $table_check) {
                $tables[$table_check] = 1;
            }
        }

        $table_exists[$table] = isset($tables[$table]);

        return $table_exists[$table];
    }


    public static function languages()
    {
        static $display_inactive = null;
        static $languages = null;
        static $available_languages = array();

        if ($available_languages) {
            return ($available_languages);
        }

        $context = Context::getContext();

        if (!$languages) {
            $languages = Language::getLanguages(false);
        }

        foreach ($languages as $language) {
            // For active languages
            if (!$display_inactive && $language['active'] == false) {
                continue;
            }

            $language['active'] = true;
            $language['default'] = ($language['id_lang'] == $context->language->id ? true : false);

            $image = sprintf('geo_flags_web2/flag_%s_64px.png', $language['iso_code']);
            $image_path = _PS_MODULE_DIR_ . 'feedbiz/views/img/' . $image;

            $image_native = 'img/p/' . sprintf('%d.jpg', $language['id_lang']);
            $image_native_path = _PS_ROOT_DIR_ . $image_native;

            if (file_exists($image_path)) {
                $language['image'] = $image;
            } elseif (file_exists($image_native_path)) {
                $language['image'] = $image_native;
            } else {
                $language['image'] = null;
            }

            $available_languages[$language['id_lang']] = $language;
        }

        return ($available_languages);
    }

    public static function amazonRegionToDomain($region)
    {
        switch ($region) {
            case 'uk':
                return ('co.uk');
            case 'us':
                return ('com');
            case 'jp':
                return ('co.jp');
            default:
                return ($region);
        }
    }

    //static public $ebay_regions =
    // array('fr', 'uk', 'de', 'it', 'es', 'ch', 'at', 'be-fr', 'be-nl', 'nl', 'ie', 'pl', 'se', 'ca', 'ca-fr', 'us');
    public static function ebayRegionToDomain($region)
    {
        switch ($region) {
            case 'uk':
                return ('ebay.co.uk');
            case 'be-fr':
                return ('befr.ebay.be');
            case 'be-nl':
                return ('benl.ebay.be');
            case 'se':
                return ('eim.ebay.se');
            case 'us':
                return ('ebay.com');
            default:
                return ($region);
        }
    }

    public static function cdiscountRegionToDomain($region)
    {
        return Tools::strtoupper($region);
    }

    public static function fnacRegionToDomain($region)
    {
        switch ($region) {
            case 'us':
                return ('fnac.com');
            case 'es':
                return ('fnac.es');
            case 'pt':
                return ('fnac.pt');
            case 'fr':
                return ('fr.fnac.be');
            default:
                return ($region);
        }
    }
    public static function rakutenRegionToDomain($region)
    {
        switch ($region) {
            case 'fr':
                return ('fr.shopping.rakuten.com');
            default:
                return ($region);
        }
    }

    public static function miraklRegionToDomain($region)
    {
        $mirakl_regions = array();
        $mirakl = Configuration::get('FEEDBIZ_MIRAKL_REGION');

        if (Tools::strlen($mirakl)) {
            $mirakl_regions = unserialize($mirakl);
        }
        foreach ($mirakl_regions as $mirakl_region) {
            if ($region == (string) $mirakl_region['region']) {
                return (string) $mirakl_region['domain'];
            }
        }
    }

    // http://jetpack.wp-a2z.org/oik_api/is_serialized/
    public static function isSerialized($data, $strict = true)
    {
        // if it isn't a string, it isn't serialized.
        if (!is_string($data)) {
            return false;
        }
        $data = trim($data);
        if ('N;' == $data) {
            return true;
        }
        if (Tools::strlen($data) < 4) {
            return false;
        }
        if (':' !== $data[1]) {
            return false;
        }
        if ($strict) {
            $lastc = Tools::substr($data, -1);
            if (';' !== $lastc && '}' !== $lastc) {
                return false;
            }
        } else {
            $semicolon = strpos($data, ';');
            $brace = strpos($data, '}');
            // Either ; or } must exist.
            if (false === $semicolon && false === $brace) {
                return false;
            }
            // But neither must be in the first X characters.
            if (false !== $semicolon && $semicolon < 3) {
                return false;
            }
            if (false !== $brace && $brace < 4) {
                return false;
            }
        }
        $token = $data[0];
        switch ($token) {
            /** @noinspection PhpMissingBreakStatementInspection */
            case 's':
                if ($strict) {
                    if ('"' !== Tools::substr($data, -2, 1)) {
                        return false;
                    }
                } elseif (false === strpos($data, '"')) {
                    return false;
                }
                /** @noinspection PhpMissingBreakStatementInspection */
                // nobreak;
                // no break
            case 'a':
                /** @noinspection PhpMissingBreakStatementInspection */
                // nobreak;
                // no break
            case 'O':
                return (bool)preg_match("/^{$token}:[0-9]+:/s", $data);
                // nobreak;
            case 'b':
                /** @noinspection PhpMissingBreakStatementInspection */
                // nobreak;
                // no break
            case 'i':
                /** @noinspection PhpMissingBreakStatementInspection */
                // nobreak;
                // no break
            case 'd':
                /** @noinspection PhpMissingBreakStatementInspection */
                $end = $strict ? '$' : '';
                return (bool)preg_match("/^{$token}:[0-9.E-]+;$end/", $data);
                // nobreak;
        }
        return false;
    }

    public static function fileGetContents($url, $use_include_path = false, $stream_context = null, $curl_timeout = 30)
    {
        if ($stream_context == null && preg_match('/^https?:\/\//', $url)) {
            if (preg_match('/^https:\/\//', $url)) {
                $contextOptions = array(
                    'ssl' => array(
                        'verify_peer' => true,
                        'cafile' => FeedbizCertificates::getCertificate()
                    )
                );
            } else {
                $contextOptions = array();
            }

            $stream_context = @stream_context_create(
                array('http' => array('timeout' => $curl_timeout)),
                $contextOptions
            );
        }
        if (in_array(ini_get('allow_url_fopen'), array('On', 'on', '1')) || !preg_match('/^https?:\/\//', $url)) {
            if (Feedbiz::$debug_mode) {
                // Validation: http://forge.prestashop.com/browse/PSCSX-7758
                return Tools::file_get_contents(
                    $url,
                    $use_include_path,
                    is_resource($stream_context) ? $stream_context : null
                );
            } else {
                // Validation: http://forge.prestashop.com/browse/PSCSX-7758
                return Tools::file_get_contents(
                    $url,
                    $use_include_path,
                    is_resource($stream_context) ? $stream_context : null
                );
            }
        } elseif (function_exists('curl_init')) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($curl, CURLOPT_TIMEOUT, $curl_timeout);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 2);
            curl_setopt($curl, CURLOPT_CAINFO, FeedbizCertificates::getCertificate());
            if ($stream_context != null) {
                $opts = stream_context_get_options($stream_context);
                if (isset($opts['http']['method']) && Tools::strtolower($opts['http']['method']) == 'post') {
                    curl_setopt($curl, CURLOPT_POST, true);
                    if (isset($opts['http']['content'])) {
                        parse_str($opts['http']['content'], $post_data);
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
                    }
                }
            }
            $content = curl_exec($curl);
            curl_close($curl);
            return $content;
        } else {
            return false;
        }
    }

    /**
     * Implementation of array_column function for PHP 5 < 5.5.0.
     *
     * @param array $input
     * @param mixed $column_key
     * @param mixed $index_key
     * @return array
     * @see http://php.net/manual/en/function.array-column.php
     */
    public static function arrayColumn($input, $column_key, $index_key = null)
    {
        if (function_exists('array_column')) {
            return array_column($input, $column_key, $index_key);
        }

        if (func_num_args() < 2) {
            trigger_error('array_column() expects at least 2 parameters, '.func_num_args().' given', E_USER_WARNING);

            return null;
        } elseif (!is_array($input)) {
            trigger_error('array_column() expects parameter 1 to be array, '.gettype($input).' given', E_USER_WARNING);

            return null;
        }

        if ($index_key) {
            return array_combine(
                self::arrayColumn($input, $index_key),
                self::arrayColumn($input, $column_key)
            );
        }

        return array_map(
            array('FeedbizTools', 'arrayColumnCallback'),
            $input,
            array_fill(0, count($input), $column_key)
        );
    }

    /**
     * @ignore
     * @param array $columns
     * @param int|string $column_key
     * @return mixed
     * @see FeedbizTools::arrayColumn()
     */
    private static function arrayColumnCallback($columns, $column_key)
    {
        return $columns[$column_key];
    }

    /**
     * Get the list of active overrides (realpath).
     *
     * @return array
     */
    public static function getShopOverrides()
    {
        $overrides = array();

        if (!Configuration::get('PS_DISABLE_OVERRIDES')) {
            $recursive_director_iterator = new RecursiveDirectoryIterator(_PS_OVERRIDE_DIR_);
            $recursive_iterator_iterator = new RecursiveIteratorIterator(
                $recursive_director_iterator,
                RecursiveIteratorIterator::SELF_FIRST
            );
            foreach ($recursive_iterator_iterator as $file) {
                if ($file->isDir() || pathinfo($file->getFilename(), PATHINFO_EXTENSION) == 'php' || $file->getBasename() == 'index.php') {
                    continue;
                }
                $overrides[] = ltrim(str_replace(array(_PS_ROOT_DIR_, '\\'), array('', '/'), $file->getRealpath()), '/');
            }
        }

        return $overrides;
    }

    public static function checkPermissions()
    {
        $folder_history = array();
        $permissions_errors = array();

        $recursive_director_iterator = new RecursiveDirectoryIterator(
            _PS_MODULE_DIR_.'feedbiz',
            FilesystemIterator::SKIP_DOTS
        );
        $recursive_iterator_iterator = new RecursiveIteratorIterator(
            $recursive_director_iterator,
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($recursive_iterator_iterator as $file) {
            if ($file->getBasename() == 'index.php' || $file->getBasename() == 'CHANGELOG'
                || strpos($file->getRealPath(), '.idea') !== false || strpos($file->getRealPath(), '.xml') !== false) {
                continue;
            }

            $perms = Tools::substr(sprintf('%o', $file->getPerms()), -4);
            $perms = ltrim($perms, '0');

            if ($file->isDir() && $perms !== "755") { // Folder
                $permissions_folder_error     = ltrim(str_replace(array(_PS_ROOT_DIR_,  '\\'), array('', '/'), $file->getRealpath()), '/');
                $permissions_folder_error     = explode('/', $permissions_folder_error);
                $folder_name                  = array_pop($permissions_folder_error);
                $folder_history[$folder_name] = 1;

                $permissions_errors[$folder_name][] = ltrim(str_replace(array(_PS_ROOT_DIR_, '\\'), array('', '/'), $file->getRealpath()), '/');
            } elseif ($file->isFile() && $perms !== "644") { // File
                $permissions_file_error = ltrim(str_replace(array(_PS_ROOT_DIR_,  '\\'), array('', '/'), $file->getRealpath()), '/');

                $file_names = explode('/', $permissions_file_error);
                if (count($file_names) > 3) {
                    foreach ($file_names as $file_name) {
                        if (isset($folder_history[$file_name])) {
                            $permissions_errors[$file_name][] = ltrim(str_replace(array( _PS_ROOT_DIR_, '\\'), array('', '/'), $file->getRealpath()), '/');
                        }
                    }
                } else {
                    $permissions_errors[0][] = ltrim(str_replace(array(_PS_ROOT_DIR_, '\\'), array('', '/'), $file->getRealpath()), '/');
                }
            }
        }

        return $permissions_errors;
    }

    /**
     * @param $source
     * @param $destination
     * @param null $stream_context
     *
     * @return bool|int
     */
    public static function copy($source, $destination, $stream_context = null)
    {
        if (method_exists('Tools', 'copy')) {
            if (is_null($stream_context) && !preg_match('/^https?:\/\//', $source)) {
                return @copy($source, $destination);
            } //TODO: Validation - PS1.4 compat
            return @file_put_contents($destination, FeedbizTools::fileGetContents($source, false, $stream_context));//TODO: Validation - PS1.4 compat
        } else {
            return @copy($source, $destination);
        }
    }
}

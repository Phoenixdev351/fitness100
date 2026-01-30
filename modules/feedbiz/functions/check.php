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
require_once(dirname(__FILE__).'/../classes/feedbiz.product.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.zip.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.tools.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.webservice.class.php');

/**
 * Class FeedBizConnectionCheck
 */
class FeedBizConnectionCheck extends Feedbiz
{
    /**
     * @var
     */
    private $callback;

    /**
     *
     */
    public function dispatch()
    {
        $callback = Tools::getValue('callback');

        if (empty($callback) || $callback == '?') {
            $this->callback = 'jsonp_'.time();
        } else {
            $this->callback = $callback;
        }

        if (Configuration::get('FEEDBIZ_PS_TOKEN') !== Tools::getValue('pstoken', 'wrong')) {
            die('Wrong Token');
        }

        $action = Tools::getValue('action');

        switch ($action) {
            case 'check':
                $this->check();
                break;
            case 'php-info':
                $this->phpInfo();
                break;
            case 'prestashop-info':
                $this->prestashopInfo();
                break;
            case 'support-infos':
                $this->supportInfo();
                break;
            case 'mode-dev':
                $this->prestashopModeDev();
                break;
            default:
                $this->dieAndAlert('Missing parameter, nothing to do !');
        }
    }

    public function prestashopModeDev()
    {
        $callback = Tools::getValue('callback');

        if ($callback == '?' || empty($callback)) {
            $callback = 'jsonp_'.time();
        }

        $message = null;
        $new_state = Tools::getValue('status');
        $new_state_text = !(bool)$new_state ? 'false' : 'true';

        if ($new_state !== '0' && $new_state !== '1') {
            die('Target status unknown');
        }

        if (!defined('_PS_CONFIG_DIR_')) {
            define('_PS_CONFIG_DIR_', _PS_ROOT_DIR_.'/config/');
        }

        $defines_inc_php = _PS_CONFIG_DIR_.'defines.inc.php';
        $defines_inc_php_bak = _PS_CONFIG_DIR_.'defines.inc.php.bak';

        if (!file_exists($defines_inc_php) || !is_writable($defines_inc_php)) {
            die('File doesnt exists or is not writeable');
        }

        if (!($md5_orig = md5_file($defines_inc_php))) {
            die(sprintf('Unable to generate md5 of file: %s', $defines_inc_php));
        }

        if (!FeedbizTools::copy($defines_inc_php, $defines_inc_php_bak)) {
            die(sprintf('Unable to create a backup (from %s to %s)', $defines_inc_php, $defines_inc_php_bak));
        }

        if (!($md5_dest = md5_file($defines_inc_php_bak))) {
            die(sprintf('Unable to generate md5 of file: %s', $defines_inc_php_bak));
        }

        if (!Tools::strlen($md5_dest) || $md5_orig != $md5_dest) {
            die('md5sum mismatch, operation aborted');
        }

        $defines_inc_contents = FeedbizTools::fileGetContents($defines_inc_php);

        if (!Tools::strlen($defines_inc_php)) {
            die('Unable to get file contents, operation aborted');
        }

        if (md5($defines_inc_contents) != $md5_dest) {
            die('md5sum mismatch, operation aborted');
        }

        $defines_inc_contents_out = preg_replace('/(_PS_MODE_DEV_[\"\'][\s,]*)(true|false|TRUE|FALSE)/', '$1'.$new_state_text, $defines_inc_contents);

        $length_diff = abs(Tools::strlen($defines_inc_contents) - Tools::strlen($defines_inc_contents_out));

        if ($length_diff > 1) {
            die('messup, operation aborted');
        }

        if (!file_put_contents($defines_inc_php, $defines_inc_contents_out)) {
            if (!FeedbizTools::copy($defines_inc_php_bak, $defines_inc_php)) {
                die('/!\\ huge trouble: operation failed, backup restore failed too !');
            } else {
                die('operation failed backup restored');
            }
        } else {
            $message = sprintf(html_entity_decode('_PS_MODE_DEV_ switched to &lt;b&gt;%s&lt;/b&gt; with success'), !(bool)$new_state ? 'Off' : 'On');
        }

        if ($new_state !== '1') {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        }

        $json = Tools::jsonEncode(array('status' => (bool)$new_state, 'message' => $message));

        echo (string)$callback.'('.$json.')';
        die;
    }

    public function supportInfo()
    {
        $errors = false;
        $prestashop_info = $this->prestashopInfo(true);
        $php_info = $this->phpInfo(true);
        $screenshot = Tools::getValue('screenshot');

        $file_ext = FeedbizTools::getFriendlyUrl(Configuration::get('PS_SHOP_NAME'));
        $f_psinfo = sprintf('%ssupport/%s-ps-info.txt', $this->path, $file_ext);
        $f_phpinfo = sprintf('%ssupport/%s-php-info.html', $this->path, $file_ext);
        $f_screenshot = sprintf('%ssupport/%s-screenshot.png', $this->path, $file_ext);
        $f_zipfile = sprintf('%ssupport/%s-support.zip', $this->path, $file_ext);
        $f_zipfile_url = sprintf('%ssupport/%s-support.zip', $this->url, $file_ext);

        $errors &= file_put_contents($f_psinfo, $prestashop_info);
        $errors &= file_put_contents($f_phpinfo, $php_info);
        $errors &= file_put_contents($f_screenshot, self::fbBase64Decode(preg_replace('#^data:image/\w+;base64,#i', '', $screenshot)));

        chdir(sprintf('%s/support', $this->path));

        if (file_exists($f_zipfile)) {
            @unlink($f_zipfile);
        }
        $zip = new FeedbizZip();
        $zip->createZip($f_zipfile, array(basename($f_psinfo),basename($f_phpinfo), basename($f_screenshot)));

        $json = Tools::jsonEncode(array('errors' => $errors, 'url' => $f_zipfile_url));

        if ($callback = Tools::getValue('callback')) {
            if ($callback == '?') {
                $callback = 'jsonp_'.time();
            }
            echo (string)$callback.'('.$json.')';
        } else {
            CommonTools::d($json);
        }
    }
    /**
     * @throws PrestaShopDatabaseException
     */
    public function prestashopInfo($return_data = false)
    {
        $header_errors = ob_get_clean();
        $output = null;

        if (version_compare(_PS_VERSION_, 1.5, '>=')) {
            $sort = ' ORDER by `name`,`id_shop`';
            $ps15 = true;
        } else {
            $sort = ' ORDER by `name`';
            $ps15 = false;
        }

        $results = Db::getInstance()->executeS('SELECT * FROM `'._DB_PREFIX_.'configuration` WHERE `name` LIKE "PS_%" OR `name` LIKE "FEEDBIZ_%"'.pSQL($sort));

        $ps_configuration = null;

        $feedbiz_conf_done = false;
        foreach ($results as $result) {
            if (strpos($result['name'], 'USERNAME') || strpos($result['name'], 'KEY') || strpos($result['name'], 'EMAIL') || strpos($result['name'], 'PASSWORD') || strpos($result['name'], 'PASSWD')) {
                continue;
            } elseif (!$feedbiz_conf_done && Tools::substr($result['name'], 0, 8) != 'FEEDBIZ_') {
                $feedbiz_conf_done = true;
                $ps_configuration .= '<hr>';
            }

            $value = $result['value'];

            if (FeedbizTools::isSerialized($value)) {
                $value = '<div class="print_r">'.print_r(unserialize($value), true).'</div>';
            } elseif (Tools::substr($result['name'], 0, 22) == 'FEEDBIZ_OPTION_FIELDS_' || $result['name'] == 'FEEDBIZ_PRODUCT_OPTION_FIELDS') {
                $value = '<div class="print_r">'.print_r(explode(',', $value), true).'</div>';
            } else {
                $value = Tools::strlen($result['value']) > 128 ? Tools::substr($result['value'], 0, 128).'...' : $result['value'];
            }

            if ($ps15) {
                $ps_configuration .= sprintf('%-50s %03d %03d : %s'."\n", $result['name'], $result['id_shop'], $result['id_shop_group'], $value);
            } else {
                $ps_configuration .= sprintf('%-50s : %s'."\n", $result['name'], $value);
            }
        }

        $output .=  '<h1>Prestashop</h1>';
        $output .=  '<pre>';
        $output .=  'Version: '._PS_VERSION_."\n";
        $output .=  sprintf('Module Version: %s/%s', $this->displayName, $this->version)."\n";
        $output .=  'Mode DEV: '.(defined('_PS_MODE_DEV_') && _PS_MODE_DEV_ ? 'Yes' : 'No')."\n\n";

        $output .=  'Max input vars: '.ini_get('max_input_vars')."\n";
        $output .=  'Max execution time: '.ini_get('max_execution_time')."\n";
        $output .=  'Memory limit: '.ini_get('memory_limit')."\n\n";

        $output .=  'Overrides:'."\n";
        if (count(FeedbizTools::getShopOverrides())) {
            foreach (FeedbizTools::getShopOverrides() as $override) {
                $output .=  $override."\n";
            }
        } else {
            $output .=  'N/A'."\n";
        }
        $output .=  "\n";

        $output .=  'Catalog: '."\n";
        $output .= sprintf('%-58s : <b>%s</b>'."\n", 'Categories', Db::getInstance()->getValue('SELECT count(`id_category`) as count FROM `'._DB_PREFIX_.'category`'));
        $output .= sprintf('%-58s : <b>%s</b>'."\n", 'Products', Db::getInstance()->getValue('SELECT count(`id_product`) as count FROM `'._DB_PREFIX_.'product`'));
        $output .= sprintf('%-58s : <b>%s</b>'."\n", 'Attributes', Db::getInstance()->getValue('SELECT count(`id_attribute`) as count FROM `'._DB_PREFIX_.'attribute`'));
        $output .= sprintf('%-58s : <b>%s</b>'."\n", 'Features', Db::getInstance()->getValue('SELECT count(`id_feature_value`) as count FROM `'._DB_PREFIX_.'feature_value`'));

        $output .=  "\n";
        $output .=  'Configuration: '."\n";
        $output .=  $ps_configuration;

        $output .=  '</pre>'."\n\n";

        $output .=  $header_errors;
        if ($return_data) {
            return($output);
        } else {
            echo $output;
            die;
        }
    }

    /**
     *
     */
    public function phpInfo($return_data = false)
    {
        $output = null;
        $header_errors = ob_get_clean();
        ob_start();
        phpinfo(INFO_ALL & ~INFO_CREDITS & ~INFO_LICENSE & ~INFO_ENVIRONMENT & ~INFO_VARIABLES);
        $phpinfos = ob_get_clean();

        $phpinfos = preg_replace('/(a:link.*)|(body, td, th, h1, h2.*)|(img.*)/', '', $phpinfos);

        $output .= '</pre>'."\n\n";

        $output .= '<h1>PHP</h1>'."\n";
        $output .= '<div class="phpinfo">';
        $output .= $phpinfos;
        $output .= '</div>';

        $output .= $header_errors;
        if ($return_data) {
            return($output);
        } else {
            echo $output;
            die;
        }
    }

    /**
     * @param $msg
     */
    private function dieAndAlert($msg)
    {
        $json = Tools::jsonEncode(array(
            'alert' => $msg
        ));

        header('Content-Type: application/json');

        echo (string)$this->callback.'('.$json.')';
        die();
    }

    /**
     *
     */
    private function check()
    {
        $username = '';
        $feedbiz_token = Configuration::get('FEEDBIZ_TOKEN');
        $preproduction = Tools::getValue('preprod');
        $debug = Tools::getValue('debug');

        if ($debug) {
            ob_start();
        }

        if (!empty($feedbiz_token)) {
            $FeedBiz = new FeedBizWebService($username, $feedbiz_token, $preproduction, $debug);
            $result = $FeedBiz->checkConnection();

            if ($result == true) {
                $message = sprintf($this->l('Connection test successfull to FeedBiz'));
                $error = false;
            } else {
                $message = $this->l('Failed to connect to FeedBiz');
                $error = true;
            }
        } else {
            $message = $this->l('Empty token');
            $error = true;
        }

        if ($debug) {
            $output = ob_get_clean();
        } else {
            $output = null;
        }

        $json = Tools::jsonEncode(array(
            'message' => $message,
            'error' => $error,
            'debug' => $output/*, 'username' => $username, 'token' => $feedbiz_token*/
        ));

        header('Content-Type: application/json');

        echo (string)$this->callback.'('.$json.')';
        die();
    }
    public static function fbBase64Decode($input)
    {
        $keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
        $chr1 = $chr2 = $chr3 = "";
        $enc1 = $enc2 = $enc3 = $enc4 = "";
        $i = 0;
        $output = "";

        $input = preg_replace("[^A-Za-z0-9\+\/\=]", "", $input);
        do {
            $enc1 = Tools::strpos($keyStr, Tools::substr($input, $i++, 1));
            $enc2 = Tools::strpos($keyStr, Tools::substr($input, $i++, 1));
            $enc3 = Tools::strpos($keyStr, Tools::substr($input, $i++, 1));
            $enc4 = Tools::strpos($keyStr, Tools::substr($input, $i++, 1));
            $chr1 = ($enc1 << 2) | ($enc2 >> 4);
            $chr2 = (($enc2 & 15) << 4) | ($enc3 >> 2);
            $chr3 = (($enc3 & 3) << 6) | $enc4;
            $output = $output . chr((int) $chr1);
            if ($enc3 != 64) {
                $output = $output . chr((int) $chr2);
            }
            if ($enc4 != 64) {
                $output = $output . chr((int) $chr3);
            }
            $chr1 = $chr2 = $chr3 = "";
            $enc1 = $enc2 = $enc3 = $enc4 = "";
        } while ($i < Tools::strlen($input));
        return urldecode($output);
    }
    public static function isBase64($s)
    {
        return (bool) preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $s);
    }
}

$feedbiz_connectioncheck = new FeedBizConnectionCheck();
$feedbiz_connectioncheck->dispatch();

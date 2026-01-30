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
require_once(dirname(__FILE__).'/../classes/feedbiz.order.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.tools.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.webservice.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.product_tab.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.product_tab.amazon.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.product_tab.ebay.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.product_tab.cdiscount.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.product_tab.fnac.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.product_tab.rakuten.class.php');
require_once(dirname(__FILE__).'/../classes/feedbiz.context.class.php');

/**
 * Class FeedBizMessaging
 */
class FeedBizMessaging extends Feedbiz
{
    /**
     * @var bool
     */
    private $debug;

    /**
     * @var string FeedBiz auth
     */
    private $username;

    /**
     * @var array
     */
    private $returnData = array();
    private $errors = array();

    const CUSTOMER_REGISTERED_ORDER_MESSAGE = 1;
    const CUSTOMER_REGISTERED_QUESTION = 2;
    const CUSTOMER_UNREGISTERED_QUESTION = 3;

    public $message_date = null;
    public $message_subject = null;
    public $message_body = null;
    public $message_info = null;
    public $message_id = null;
    public $message_id_lang = null;
    public $customer_name = null;
    public $customer_email = null;
    public $mp_order_id = null;
    public $id_order = null;
    public $id_product = null;
    public $customer = null;
    public $id_employee = null;

    /**
     * FeedBizMessaging constructor.
     */
    public function __construct()
    {
        parent::__construct();

        register_shutdown_function(array(
            $this,
            'FBShutdowFunction'
        ));

        ob_start();

        if (version_compare(_PS_VERSION_, '1.5', '>')) {
            $employee = null;
            $id_employee = (int)Configuration::get('FEEDBIZ_ID_EMPLOYEE');

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

        $this->debug = (bool)Configuration::get('FEEDBIZ_DEBUG') || (bool)Tools::getValue('debug');

        if ($this->debug) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL | E_STRICT);
        }
    }

    public function dispatch()
    {
        FeedbizTools::securityCheck();

        // Check Access Tokens
        $this->token = Configuration::get('FEEDBIZ_TOKEN');
        $this->preproduction = Configuration::get('FEEDBIZ_PREPRODUCTION') ? true : false;

        // get customer messages
        $FeedBizWS = new FeedBizWebService($this->username, $this->token, $this->preproduction, $this->debug);

        $params = array(
            'token' => $this->token,
        );

        $messages = $FeedBizWS->getCustomerMessages($params, 'GET', true, $this->debug);

        // save customer messages
        $this->_saveCustomerMessages($messages);

        if ($this->returnData) {
            $this->statusCode = '1';
            $this->status = $this->l('Success');
        }

        ob_end_clean();
    }

    public function _saveCustomerMessages($messages)
    {
        $id_default_customer = (int)Configuration::get('FEEDBIZ_CUSTOMER_ID');
        $default_customer = new Customer($id_default_customer);

        if (!Validate::isLoadedObject($default_customer)) {
            $this->errors [] = array(
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'message' => $this->l('Unable to load default customer:').$id_default_customer
            );

            return (false);
        }

        $this->id_employee = Configuration::get('FEEDBIZ_ID_EMPLOYEE') ? (int)Configuration::get('FEEDBIZ_ID_EMPLOYEE') : $this->context->employee->id;

        if (empty($messages) || !count($messages)) {
            $this->errors [] = array(
                'file' => basename(__FILE__),
                'line' => __LINE__,
                'message' => $this->l('No New messages')
            );
            return (false);
        }

        foreach ($messages as $marketplace_messages) {
            foreach ($marketplace_messages as $marketplace => $messages_list) {
                foreach ($messages_list as $lang => $messages_detail) {
                    if (!count($messages_detail->item)) {
                        $this->errors [] = array(
                            'file' => basename(__FILE__),
                            'line' => __LINE__,
                            'message' => $this->l('No messages for').' '.$marketplace.':'.$lang
                        );
                        continue;
                    }

                    foreach ($messages_detail->item as $message) {
                        // Matches: (Commande : 404-2241254-9291534)
                        if (isset($message->mp_order_id) && !empty($message->mp_order_id)) {
                            $mp_order_id = (string)$message->mp_order_id;
                        } else {
                            $mp_order_id = null;
                        }

                        $from_split = explode(' - ', $message->from);

                        $match_ok = false;

                        if (is_array($from_split) && count($from_split)) {
                            $customer_name = reset($from_split);
                            $additional_datas = end($from_split);

                            $match_ok = preg_match('/<([^>]+)>/', $additional_datas, $email_info);
                        }

                        if ($match_ok && is_array($email_info) && count($email_info)) {
                            $customer_email_address = end($email_info);
                        } else {
                            $this->errors [] = array(
                                'file' => basename(__FILE__),
                                'line' => __LINE__,
                                'message' => $this->l('Unable to find email info from the header:').(string)$message->from
                            );
                            continue;
                        }

                        $date = date('Y-m-d H:i:s', strtotime($message->date));

                        // get id_lang from iso code
                        $id_lang = $this->id_lang;
                        if (isset($messages_detail->iso_code) && !empty($messages_detail->iso_code)) {
                            $id_lang = Language::getIdByIso((string)$messages_detail->iso_code);
                        }

                        $this->message_id_lang = $id_lang;
                        $this->message_body = null;

                        $result = explode('-------------', $message->body);

                        if (is_array($result) && count($result) && array_key_exists(2, $result)) {
                            $this->message_subject = trim($message->subject);
                            $this->message_body .= trim($result[2]);
                            $this->message_info = trim(preg_replace('/[\n\r]/', '', $result[0]));
                        } else {
                            $this->message_subject = trim($message->subject);
                            $this->message_body .= trim($message->body);
                            $this->message_info = null;
                        }

                        $this->message_date = $date;
                        $this->message_id = sprintf('%u', crc32($message->message_id));
                        $this->customer = null;
                        $this->customer_name = $customer_name;
                        $this->customer_email = $customer_email_address;
                        $this->mp_order_id = (string)$mp_order_id;
                        $this->id_product = null;

                        if ($this->debug) {
                            echo "<pre>\n";
                            printf('%s - %s::%s - line #%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
                            printf('message_date: %s'."\n", $this->message_date);
                            printf('message_id: %s'."\n", $this->message_id);
                            printf('customer_email: %s'."\n", $customer_email_address);
                            printf('mp_order_id: %s'."\n", $this->mp_order_id);
                            echo "</pre>\n";
                        }

                        if (Tools::strlen($this->message_info)) {
                            $match_ok = preg_match('/\[ASIN : (\w+)\]/', $this->message_info, $matches);

                            if ($match_ok && is_array($matches) && count($matches)) {
                                $asin = end($matches);

                                if (Tools::strlen($asin)) {
                                    $product = FeedBizProductTabAmazon::getIdByAsin($lang, $asin);

                                    if (Validate::isLoadedObject($product)) {
                                        $this->id_product = $product->id;
                                    }
                                }
                            }
                        }

                        $scenario = null;
                        $id_order = null;

                        if (!empty($mp_order_id)) {
                            $mp_order = FeedBizOrder::checkByMpId($mp_order_id);
                            $id_order = $mp_order['id_order'];

                            if (!(int)$id_order) {
                                $this->errors [] = array(
                                    'file' => basename(__FILE__),
                                    'line' => __LINE__,
                                    'message' => $this->l('Order').' '.$mp_order_id.' '.$this->l('is not yet imported')
                                );
                                continue;
                            }
                            if ($this->debug) {
                                echo "<pre>\n";
                                printf('%s - %s::%s - line #%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
                                printf('Order: %d/%s'."\n", $id_order, $mp_order_id);
                                echo "</pre>\n";
                            }

                            $this->id_order = $id_order;

                            $order = new Order($id_order);

                            if (Validate::isLoadedObject($order)) {
                                $this->customer = new Customer($order->id_customer);
                                $this->message_id_lang = $order->id_lang;

                                if (!Validate::isLoadedObject($default_customer)) {
                                    $this->errors [] = array(
                                        'file' => basename(__FILE__),
                                        'line' => __LINE__,
                                        'message' => $this->l('Unable to load customer:').' '.$order->id_customer
                                    );
                                    return (false);
                                }
                            } else {
                                $this->customer = $default_customer;
                            }

                            $scenario = self::CUSTOMER_REGISTERED_ORDER_MESSAGE;
                        } else {
                            $customer = new Customer();
                            $customer->getByEmail($customer_email_address);

                            if (Validate::isLoadedObject($customer)) {
                                $scenario = self::CUSTOMER_REGISTERED_QUESTION;
                                $this->customer = $customer;
                            } else {
                                $this->customer = $default_customer;
                                $scenario = self::CUSTOMER_UNREGISTERED_QUESTION;
                            }
                        }

                        $this->saveCustomerMessage($scenario);
                    }
                }
            }
        }
    }

    private function saveCustomerMessage($scenario)
    {
        if (Validate::isLoadedObject($this->customer)) {
            $id_customer = $this->customer->id;
        } else {
            return (false);
        }

        switch ($scenario) {
            case self::CUSTOMER_REGISTERED_ORDER_MESSAGE:
                if ($this->debug) {
                    echo "<pre>\n";
                    printf('%s - %s::%s - line #%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
                    printf('scenario: %s'."\n", 'CUSTOMER_REGISTERED_ORDER_MESSAGE');
                    echo "</pre>\n";
                }
                break;
            case self::CUSTOMER_REGISTERED_QUESTION:
                if ($this->debug) {
                    echo "<pre>\n";
                    printf('%s - %s::%s - line #%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
                    printf('scenario: %s'."\n", 'CUSTOMER_REGISTERED_QUESTION');
                    echo "</pre>\n";
                }
                break;
            default: // CUSTOMER_UNREGISTERED_QUESTION
                if ($this->debug) {
                    echo "<pre>\n";
                    printf('%s - %s::%s - line #%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
                    printf('scenario: %s'."\n", 'CUSTOMER_UNREGISTERED_QUESTION');
                    echo "</pre>\n";
                }
                break;
        }

        $thread_identifier = $this->message_id;

        // prevent duplicated messages
        $previous_customer_messages = CustomerThread::getCustomerMessages($id_customer);
        $pass = true;

        if (is_array($previous_customer_messages) && count($previous_customer_messages)) {
            foreach ($previous_customer_messages as $previous_customer_message) {
                if ($previous_customer_message['token'] == $thread_identifier) {
                    $pass = false;
                }
            }
        }

        if ($pass) {
            $customer_thread = new CustomerThread();
            $customer_thread->id_contact = 0;
            $customer_thread->id_customer = $id_customer;
            $customer_thread->id_shop = (int)$this->context->shop->id;
            $customer_thread->id_order = $this->id_order;
            $customer_thread->id_product = $this->id_product;
            $customer_thread->id_lang = $this->message_id_lang;
            $customer_thread->email = $this->customer_email;
            $customer_thread->status = 'open';
            $customer_thread->token = $thread_identifier;
            $customer_thread->add();

            $customer_message = new CustomerMessage();
            $customer_message->id_customer_thread = $customer_thread->id;
            $customer_message->id_employee = $this->id_employee;
            $customer_message->message = $this->message_subject."\n";

            if ($this->message_info) {
                $customer_message->message .= $this->message_info."\n";
            }
            $customer_message->message .= $this->l('Message').':'."\n".$this->message_body;
            $customer_message->private = 0;

            if ($this->debug) {
                echo "<pre>\n";
                printf('%s - %s::%s - line #%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
                printf('customer_thread: %s', print_r(get_object_vars($customer_thread), true));
                printf('customer_message: %s', print_r(get_object_vars($customer_message), true));
                echo "</pre>\n";
            }

            if ($customer_message->validateFields(false, true)) {
                $customer_message->add();

                if (Validate::isLoadedObject($customer_message)) {
                    if ($this->debug) {
                        echo "<pre>\n";
                        echo str_repeat('-', 160)."\n";
                        printf('Message from: %s (%s)'."\n", $this->customer_name, $this->customer_email);
                        printf('Subject: %s'."\n", $this->message_subject);
                        printf('Added sucessfully: %s'."\n", $thread_identifier);
                        echo "<pre>\n";
                    } else {
                        $this->returnData['messages'][$thread_identifier] = 1;
                    }
                }
            } else {
                $this->errors [] = array(
                    'file' => basename(__FILE__),
                    'line' => __LINE__,
                    'message' => $this->l('validateFields: FAILED')
                );
            }
        } else {
            if ($this->debug) {
                echo "<pre>\n";
                echo str_repeat('-', 160)."\n";
                printf('%s - %s::%s - line #%d'."\n", basename(__FILE__), __CLASS__, __FUNCTION__, __LINE__);
                printf('Existing thread: %s', $thread_identifier);
                echo "</pre>\n";
            } else {
                $this->returnData['messages'][$thread_identifier] = -1;
            }
        }
        return ($pass);
    }

    /**
     *
     */
    public function FBShutdowFunction()
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
            $StatusDoc->appendChild($Document->createElement('Output', implode(", ", $outputMessages)));
            $errorDoc = $StatusDoc->appendChild($Document->createElement('Error'));

            if (isset($this->returnData ['messages'])) {
                $messagesDoc = $StatusDoc->appendChild($Document->createElement('Messages'));

                foreach ($this->returnData ['messages'] as $thread_identifier => $status) {
                    $messageDoc = $messagesDoc->appendChild($Document->createElement('Message'));
                    $messageDoc->appendChild($Document->createElement('MessageID', $thread_identifier));
                    $messageDoc->appendChild($Document->createElement('MessageStatus', $status));
                }
            }

            $outBuffer = ob_get_contents();
            $errorDoc->appendChild($Document->createCDATASection($outBuffer));

            header("Content-Type: application/xml; charset=utf-8");

            echo $Document->saveXML();
        } else {
            echo "---------------------------------------------------\n<br/>";
            echo "Errors\n<br/>";
            echo "---------------------------------------------------\n<br/>";
            echo '<pre>'.print_r($this->errors, true).'</pre> \n<br/>';
            echo "---------------------------------------------------\n<br/>";
            echo "Messages\n<br/>";
            echo "---------------------------------------------------\n<br/>";
            echo '<pre>'.print_r($this->returnData, true).'</pre> \n<br/>';
        }
        exit(1);
    }
}

$feedbizmessaging = new FeedBizMessaging();
$feedbizmessaging->dispatch();

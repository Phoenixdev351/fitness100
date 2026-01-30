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

class FeedBizAddress extends Address
{
    // Genere un cle qui sera le nom d'alias dans la table
    /**
     * @param $pmAddress
     *
     * @return string
     */
    public function hash($pmAddress)
    {
        $str = $pmAddress['lastname'].$pmAddress['firstname'].$pmAddress['address_1'].$pmAddress['address_2'].
            $pmAddress['zipcode'].$pmAddress['city'];

        return (md5($str));
    }

    /**
     * @param $alias
     *
     * @return false|null|string
     */
    public static function addressExistsByAlias($alias)
    {
        $id_address = Db::getInstance()->getValue('
             SELECT `id_address`
             FROM '._DB_PREFIX_.'address a
             WHERE a.`alias` = "'.pSQL($alias).'"');

        return ($id_address);
    }

    /**
     * @param $text
     *
     * @return mixed|string
     */
    public static function cleanLogin($text)
    {
        $text = mb_convert_encoding($text, 'HTML-ENTITIES', 'UTF-8');
        $text = preg_replace(array('/&szlig;/', '/&(..)lig;/', '/&([aoueAOUE])uml;/', '/&(.)[^;]*;/'), array(
            'ss',
            "$1",
            "$1".'e',
            "$1"
        ), $text);
        $text = preg_replace('/[!<>?=+@{}_$%]*$/u', '', $text); // remove non printable
        return $text;
    }

    /**
     * @param $meAddress
     * @param null $customerPhone
     *
     * @return bool|false|int|null|string
     * @throws PrestaShopException
     */
    public function lookupOrCreateAddress($meAddress, $customerPhone = null, $debug = false)
    {
//        if (version_compare(_PS_VERSION_, '1.5', '<')) {
//            require_once(dirname(__FILE__) . '/../backward_compatibility/backward.php');
//        }

        $cookie = Context::getContext()->cookie;

        $alias = $this->hash($meAddress);
        if($debug){
            $alias = md5(time().'X'.rand());
        }
        if (!($id_address = $this->addressExistsByAlias($alias))) {
            $cc = isset($meAddress['country_iso']) && Tools::strlen($meAddress['country_iso']) ? $meAddress['country_iso'] : 'FR';

            $this->language_id = (int) isset($cookie->language->id) ? $cookie->language->id : (isset(Context::getContext()->language->id) ?
                Context::getContext()->language->id : Configuration::get('PS_LANG_DEFAULT'));

            $this->id_country = Country::getByIso(Tools::strtoupper($cc));
            $this->country = Country::getNameById($this->language_id, $this->id_country);
            $this->alias = $alias;

            $this->lastname = !empty($meAddress['lastname']) ? (string)$meAddress['lastname'] : 'unknown';
            $this->firstname = !empty($meAddress['firstname']) ? (string)$meAddress['firstname'] : 'unknown';
            $this->company = !empty($meAddress['company']) ? (string)$meAddress['company'] : '';

            $this->address1 = (isset($meAddress['address_1']) && !empty($meAddress['address_1'])) ? (string)$this->_filter($meAddress['address_1'],$debug) : null;

            $this->address2 = (isset($meAddress['address_2']) && !empty($meAddress['address_2'])) ? (string)$this->_filter($meAddress['address_2'],$debug) : null;

            if (isset($meAddress['address_3']) && !empty($meAddress['address_3'])) {
                $this->address2 .= ' - '.$meAddress['address_3'];
                $addressRules = $this->getValidationRules('Address');
                $result = explode("|", wordwrap($this->address2, $addressRules['size']['address2'], "|", true));

                $this->address2 = rtrim($result[0], ' - ');

                if (isset($result[1])) {
                    $this->other = ' - '.$result[1];
                }
            }

            $this->postcode = !empty($meAddress['zipcode']) ? (string)$meAddress['zipcode'] : 'unknown';
            $this->city = (isset($meAddress['city']) && !empty($meAddress['city'])) ? $this->_filter((string)ucwords(Tools::strtolower($meAddress['city']))) : '-';

            // 2013-01-05 Olivier - getStatesByIdCountry is not supported by PS < 1.4
            if (version_compare(_PS_VERSION_, '1.4', '>=')) {
                /* Modif YB du 17/12/2012 - Mappage de l'etat pour les adresses US */
                if (isset($meAddress['state_region']) && $meAddress['state_region'] != '') {
                    if ($this->id_country) {
                        $statesList = State::getStatesByIdCountry($this->id_country);
                        if (is_array($statesList) && count($statesList)) {
                            foreach ($statesList as $curstate) {
                                $state_or_region = preg_replace('/[^A-Za-z0-9 -]/', '', Tools::strtolower($meAddress['state_region']));

                                if (Tools::strtolower($curstate['iso_code']) == $state_or_region || preg_replace('/[^A-Za-z0-9 -]/', '', Tools::strtolower($curstate['name'])) == $state_or_region
                                ) {
                                    $this->id_state = (int)$curstate['id_state'];
                                    break;
                                }
                            }
                        }
                        if (!$this->id_state && empty($this->address2)) {
                            $this->address2 = $this->_filter(trim($meAddress['state_region']));
                        }
                    }
                }
            }

            if (!is_array($meAddress['phone']) && !empty($meAddress['phone'])) {
                $this->phone = Tools::substr((string)$customerPhone, 0, 16);
            }

            if (!is_array($meAddress['phone_mobile']) && !empty($meAddress['phone_mobile'])) {
                $this->phone_mobile = Tools::substr((string)$meAddress['phone_mobile'], 0, 16);

                if (!empty($this->phone_mobile) && empty($this->phone) && $this->checkPhoneIsRequired()) {
                    $this->phone = $this->phone_mobile;
                }
            }

            $this->other = '';

            if (isset($this->other)) {
                $this->other = (isset($meAddress['other']) && !empty($meAddress['other'])) ?
                    (string)$meAddress['other'] : $this->other ;
            } else {
                $this->other = (isset($meAddress['other']) && !empty($meAddress['other'])) ?
                    (string)$meAddress['other'] : null ;
            }

            $this->vat_number = !empty($meAddress['vat_number']) ? (string)$meAddress['vat_number'] : '';

            //  fields sizes must match with parent Address class
            //
            if($debug){ 
                echo "-----------------------LINE : ".__FILE__.' '.__FUNCTION__.' '.__LINE__."----------------------------\n<br/>";
                echo "Address\n<br/>";
                echo "---------------------------------------------------\n<br/>";
                echo '<pre>'.print_r(array($meAddress,$this), true).'</pre>\n<br/>'; 
            }
            foreach (array(
                         'company',
                         'firstname',
                         'lastname',
                         'address1',
                         'address2',
                         'postcode',
                         'city',
                         'phone',
                         'phone_mobile'
                     ) as $field) {
                $this->{$field} = Tools::substr($this->{$field}, 0, $this->fieldsSize[$field]);
            }
            if($debug){ 
                echo "-----------------------LINE : ".__FILE__.' '.__FUNCTION__.' '.__LINE__."----------------------------\n<br/>";
                echo "Address\n<br/>";
                echo "---------------------------------------------------\n<br/>";
                echo '<pre>'.print_r($this, true).'</pre>\n<br/>'; 
            }
            /*if (!$this->validateFields(false, false)) {
                return (false);
            }*/

            $this->dni=0;
            try {
                $this->add();
            } catch (Exception $e) {
                if ($debug) {
                    print_r($e);
                }
                $this->dni=0;
                $this->add();
            }

            return ($this->id);
        } else {
            return ($id_address);
        }
    }

    public function checkPhoneIsRequired()
    {
        static $checkPhoneIsRequired = null;

        if ($checkPhoneIsRequired !== null) {
            return $checkPhoneIsRequired;
        }

        // Check if phone is mandatory
        //
        $addressCheck = new Address();
        $pass = true;

        if (version_compare(_PS_VERSION_, '1.5', '>=')) {
            $addressRequiredFields = $addressCheck->getfieldsRequiredDatabase();

            if (is_array($addressRequiredFields) && count($addressRequiredFields)) {
                foreach ($addressRequiredFields as $addressRequiredField) {
                    if (isset($addressRequiredField['field_name']) && ($addressRequiredField['field_name'] == 'phone_mobile' || $addressRequiredField['field_name'] == 'phone')) {
                        $pass = false;
                        break;
                    }
                }
            }
        }

        $addressRules = $addressCheck->getValidationRules('Address');
        $pass = $pass && !(is_array($addressRules['required']) && in_array(array(
                    'phone_mobile',
                    'phone'
                ), $addressRules['required']));

        return $checkPhoneIsRequired = $pass;
    }

    /**
     * @param $fullname
     *
     * @return array
     */
    public static function getCustomerName($fullname)
    {
        $result = array();
        $result['company'] = '';

        $fullname = self::_filter($fullname);

        if (preg_match('/,|\//', $fullname)) {
            // Cas ou le client mets Nom Prenom, Company
            //
            $parts = preg_split('/,|\//', $fullname);
            $var = trim(mb_substr(array_shift($parts), 0, 32));
            $result['company'] = trim(mb_substr(@implode(',', $parts), 0, 32));
        } else {
            $var = mb_substr($fullname, 0, 32);
        }

        $var = mb_ereg_replace('[0-9!<>,;?=+()@#"°{}_$%:]', '', $var);

        $reverse_fullname = self::mbStrrev($var);
        $name1 = trim(self::mbStrrev(Tools::substr($reverse_fullname, mb_strpos($reverse_fullname, ' ') + 1)));
        $name2 = trim(self::mbStrrev(Tools::substr($reverse_fullname, 0, mb_strpos($reverse_fullname, ' '))));

        if (empty($name1) && empty($name2)) {
            $name1 = 'unknown';
            $name2 = 'unknown';
        } elseif (empty($name1)) {
            $name1 = $name2;
        } elseif (empty($name2)) {
            $name2 = $name1;
        }

        $result['firstname'] = Tools::ucfirst($name1);
        $result['lastname'] = Tools::ucfirst($name2);

        return ($result);
    }


    /**
     * @param $str
     * @param string $encoding
     *
     * @return string
     */
    public static function mbStrrev($str, $encoding = 'UTF-8')
    {
        return mb_convert_encoding(strrev(mb_convert_encoding($str, 'UTF-16BE', $encoding)), $encoding, 'UTF-16LE');
    }

    /**
     * @param $text
     *
     * @return mixed|string
     */
    public static function _filter($text,$debug=false)
    {
        if($debug){
            echo '_filter before :'.$text.'<br>';
        }
        if (!self::isJapanese($text)) {
            $text = mb_convert_encoding($text, 'HTML-ENTITIES', 'UTF-8');

            $searches = array('&szlig;', '&(..)lig;', '&([aouAOU])uml;', '&(.)[^;]*;');
            $replacements = array('ss', '\\1', '\\1'.'e', '\\1');

            foreach ($searches as $key => $search) {
                $text = mb_ereg_replace($search, $replacements[$key], $text);
            }
        }
        if($debug){
            echo '_filter after0 :'.$text.'<br>';
        }
        $text = str_replace('_', '/', $text);
        $text = mb_ereg_replace('[\x00-\x1F\x21-\x2C\x3A-\x3F\x5B-\x60\x7B-\x7F\x2E\x2F]]', '', $text); // remove non printable
        if($debug){
            echo '_filter after1 :'.$text.'<br>';
        }
        $text = mb_ereg_replace('[!<>?=+@{}_$%]*$', '', $text); // remove chars rejected by Validate class
        if($debug){
            echo '_filter after2 :'.$text.'<br>';
        }
        return $text;
    }

    //http://stackoverflow.com/questions/2856942/how-to-check-if-the-word-is-japanese-or-english-using-php
    /**
     * @param $word
     *
     * @return int
     */
    public static function isJapanese($word)
    {
        return preg_match('/[\x{4E00}-\x{9FBF}\x{3040}-\x{309F}\x{30A0}-\x{30FF}]/u', $word);
    }

    public static function getDefaultShopAddress($id_shop = null)
    {
        $address = new Address();
        $address->company = Configuration::get('PS_SHOP_NAME', null, null, $id_shop);
        $address->address1 = Configuration::get('PS_SHOP_ADDR1', null, null, $id_shop);
        $address->address2 = Configuration::get('PS_SHOP_ADDR2', null, null, $id_shop);
        $address->postcode = Configuration::get('PS_SHOP_CODE', null, null, $id_shop);
        $address->city = Configuration::get('PS_SHOP_CITY', null, null, $id_shop);
        $address->phone = Configuration::get('PS_SHOP_PHONE', null, null, $id_shop);
        $address->country = Configuration::get('PS_SHOP_COUNTRY', null, null, $id_shop);

        return $address;
    }
}

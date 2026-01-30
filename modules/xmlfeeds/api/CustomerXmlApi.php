<?php
/**
 * 2010-2022 Bl Modules.
 *
 * If you wish to customize this module for your needs,
 * please contact the authors first for more information.
 *
 * It's not allowed selling, reselling or other ways to share
 * this file or any other module files without author permission.
 *
 * @author    Bl Modules
 * @copyright 2010-2022 Bl Modules
 * @license
 */

class CustomerXmlApi
{
    protected $settings = array();
    protected $feedLangId = 1;

    public function getFeed($settings)
    {
        $this->settings = $settings;
        $this->feedLangId = (int)Configuration::get('PS_LANG_DEFAULT');

        return $this->generateXml($this->getCustomers());
    }

    protected function generateXml($customers)
    {
        $xml = '<items>';

        if (empty($customers)) {
            return '</items>';
        }

        foreach ($customers as $b) {
            $xml .= '<item>';
            $xml .= '<id><![CDATA['.$b['id_customer'].']]></id>';
            $xml .= '<id_gender><![CDATA['.$b['id_gender'].']]></id_gender>';
            $xml .= '<social_title><![CDATA['.$b['social_title'].']]></social_title>';
            $xml .= '<firstname><![CDATA['.$b['firstname'].']]></firstname>';
            $xml .= '<lastname><![CDATA['.$b['lastname'].']]></lastname>';
            $xml .= '<email><![CDATA['.$b['email'].']]></email>';
            $xml .= '<birthday><![CDATA['.$b['birthday'].']]></birthday>';
            $xml .= '<newsletter><![CDATA['.$b['newsletter'].']]></newsletter>';
            $xml .= '<newsletter_date_add><![CDATA['.$b['newsletter_date_add'].']]></newsletter_date_add>';
            $xml .= '<website><![CDATA['.$b['website'].']]></website>';
            $xml .= '<is_guest><![CDATA['.$b['is_guest'].']]></is_guest>';
            $xml .= '<date_add><![CDATA['.$b['date_add'].']]></date_add>';
            $xml .= '<date_upd><![CDATA['.$b['date_upd'].']]></date_upd>';
            $xml .= '<active><![CDATA['.$b['active'].']]></active>';
            $xml .= '</item>';
        }

        $xml .= '</items>';

        return $xml;
    }

    protected function getCustomers()
    {
        return Db::getInstance()->ExecuteS('
            SELECT c.*, gl.name AS social_title
            FROM '._DB_PREFIX_.'customer c
            LEFT JOIN '._DB_PREFIX_.'gender_lang gl ON
            (c.id_gender = gl.id_gender AND gl.id_lang = "'.(int)$this->feedLangId.'")
            WHERE c.`deleted` = 0
            ORDER BY c.date_add DESC
        ');
    }
}

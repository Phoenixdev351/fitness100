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

class SupplierXmlApi
{
    protected $settings = array();
    protected $feedLangId = 1;
    protected $protocol = '';

    public function getFeed($settings, $protocol)
    {
        $this->settings = $settings;
        $this->feedLangId = (int)Configuration::get('PS_LANG_DEFAULT');
        $this->protocol = $protocol;

        return $this->generateXml($this->getSuppliers());
    }

    protected function generateXml($suppliers)
    {
        $xml = '<items>';

        if (empty($suppliers)) {
            return '</items>';
        }

        $link = new Link();

        $addImage = method_exists($link, 'getSupplierImageLink');

        foreach ($suppliers as $b) {
            if ($addImage) {
                $logoUrl = $link->getSupplierImageLink($b['id_supplier']);

                if (!empty($logoUrl) && Tools::substr($logoUrl, 0, 4) != 'http') {
                    $logoUrl = $this->protocol . $logoUrl;
                }
            }

            $xml .= '<item>';
            $xml .= '<id><![CDATA['.$b['id_supplier'].']]></id>';
            $xml .= '<name><![CDATA['.$b['name'].']]></name>';
            $xml .= '<url><![CDATA['.$link->getSupplierLink($b['id_supplier'], null, (int)$this->feedLangId).']]></url>';
            $xml .= '<date_add><![CDATA['.$b['date_add'].']]></date_add>';
            $xml .= '<date_upd><![CDATA['.$b['date_upd'].']]></date_upd>';
            $xml .= '<description><![CDATA['.strip_tags($b['description']).']]></description>';
            $xml .= !empty($logoUrl) ? '<logo><![CDATA['.$logoUrl.']]></logo>' : '';
            $xml .= '<active><![CDATA['.$b['active'].']]></active>';
            $xml .= '</item>';
        }

        $xml .= '</items>';

        return $xml;
    }

    protected function getSuppliers()
    {
        return Db::getInstance()->ExecuteS('
            SELECT m.*, l.description
            FROM '._DB_PREFIX_.'supplier m
            LEFT JOIN '._DB_PREFIX_.'supplier_lang l ON
            (l.id_supplier = m.id_supplier AND l.id_lang = "'.(int)$this->feedLangId.'")
            WHERE m.`active` = 1
            ORDER BY m.name ASC
        ');
    }
}

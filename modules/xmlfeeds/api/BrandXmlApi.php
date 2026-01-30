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

class BrandXmlApi
{
    protected $settings = array();
    protected $feedLangId = 1;
    protected $protocol = '';

    public function getFeed($settings, $protocol)
    {
        $this->settings = $settings;
        $this->feedLangId = (int)Configuration::get('PS_LANG_DEFAULT');
        $this->protocol = $protocol;

        return $this->generateXml($this->getBrands());
    }

    protected function generateXml($brands)
    {
        $xml = '<items>';

        if (empty($brands)) {
            return '</items>';
        }

        $link = new Link();

        $addImage = method_exists($link, 'getManufacturerImageLink');

        foreach ($brands as $b) {
            if ($addImage) {
                $logoUrl = $link->getManufacturerImageLink($b['id_manufacturer']);

                if (!empty($logoUrl) && Tools::substr($logoUrl, 0, 4) != 'http') {
                    $logoUrl = $this->protocol . $logoUrl;
                }
            }

            $xml .= '<item>';
            $xml .= '<id><![CDATA['.$b['id_manufacturer'].']]></id>';
            $xml .= '<name><![CDATA['.$b['name'].']]></name>';
            $xml .= '<url><![CDATA[' . $link->getManufacturerLink($b['id_manufacturer'], null, (int)$this->feedLangId) . ']]></url>';
            $xml .= '<date_add><![CDATA['.$b['date_add'].']]></date_add>';
            $xml .= '<date_upd><![CDATA['.$b['date_upd'].']]></date_upd>';
            $xml .= '<description><![CDATA['.strip_tags($b['description']).']]></description>';
            $xml .= '<short_description><![CDATA['.strip_tags($b['short_description']).']]></short_description>';
            $xml .= !empty($logoUrl) ? '<logo><![CDATA['.$logoUrl.']]></logo>' : '';
            $xml .= '<active><![CDATA['.$b['active'].']]></active>';
            $xml .= '</item>';
        }

        $xml .= '</items>';

        return $xml;
    }

    protected function getBrands()
    {
        return Db::getInstance()->ExecuteS('
            SELECT m.*, l.description, l.short_description
            FROM '._DB_PREFIX_.'manufacturer m
            LEFT JOIN '._DB_PREFIX_.'manufacturer_lang l ON
            (l.id_manufacturer = m.id_manufacturer AND l.id_lang = "'.(int)$this->feedLangId.'")
            WHERE m.`active` = 1
            ORDER BY m.name ASC
        ');
    }
}

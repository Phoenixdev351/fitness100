<?php
/**
 * 2007-2019 ETS-Soft ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 * @author ETS-Soft <etssoft.jsc@gmail.com>
 * @copyright  2007-2019 ETS-Soft ETS-Soft
 * @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

class DataExport extends Module
{
    public function __construct()
    {
        parent::__construct();
        $this->context = Context::getContext();
        $this->_module = Module::getInstanceByName('ets_oneclicktomigrate');
    }

    public function addFileXMl($dir, $file_name, $definition, $multishop = false)
    {
        $definition['multilang_temp'] = isset($definition['multilang_shop']) ? $definition['multilang_shop'] : false;
        $ok = true;
        if (!Db::getInstance()->getValue("SELECT count(*) FROM " . _DB_PREFIX_ . pSQL($definition['table']) . ' tb'))
            return $ok;
        if (Configuration::get('ETS_DATAMASTER_DIVIDE_FILE')) {
            $sql = "SELECT count(DISTINCT tb." . pSQL($definition['primary']) . ") FROM " . _DB_PREFIX_ . pSQL($definition['table']) . ' tb';
            if ($definition['table'] == 'product_attribute' || $definition['table'] == 'tax_rules_group' || $definition['table'] == 'manufacturer' || $definition['table'] == 'supplier' || $definition['table'] == 'group' || $definition['table'] == 'feature' || $definition['table'] == 'attribute_group' || $definition['table'] == 'attribute' || $definition['table'] == 'image' || $definition['table'] == 'employee' || $definition['table'] == 'contact')
                $definition['multilang_shop'] = true;
            if (isset($definition['multilang_shop']) && $definition['multilang_shop'])
                $sql .= " INNER JOIN " . _DB_PREFIX_ . pSQL($definition['table']) . "_shop tbs ON(tb." . pSQL($definition['primary']) . "= tbs." . pSQL($definition['primary']) . ($multishop == false ? ' AND tbs.id_shop =' . (int)Context::getContext()->shop->id : '') . ")";
            if (in_array($definition['table'], array('orders'))) {
                $sql .= ' WHERE 1 ' . (!$multishop && version_compare(_PS_VERSION_, '1.5', '>=') ? ' AND tb.id_shop="' . (int)Context::getContext()->shop->id . '"' : '') . (Tools::getValue('ETS_PRES2PRES_ORDER_FROM') ? ' AND tb.date_add >="' . pSQL(Tools::getValue('ETS_PRES2PRES_ORDER_FROM')) . '"' : '') . (Tools::getValue('ETS_PRES2PRES_ORDER_TO') ? ' AND tb.date_add <="' . pSQL(Tools::getValue('ETS_PRES2PRES_ORDER_TO')) . '"' : '');
            }
            if (in_array($definition['table'], array('order_history', 'order_invoice', 'order_slip', 'order_cart_rule', 'order_carrier'))) {
                $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'orders o ON (tb.id_order = o.id_order)';
                $sql . ' WHERE 1 ' . (!$multishop && version_compare(_PS_VERSION_, '1.5', '>=') ? ' AND o.id_shop="' . (int)Context::getContext()->shop->id . '"' : '') . (Tools::getValue('ETS_PRES2PRES_ORDER_FROM') ? ' AND o.date_add >="' . pSQL(Tools::getValue('ETS_PRES2PRES_ORDER_FROM')) . '"' : '') . (Tools::getValue('ETS_PRES2PRES_ORDER_TO') ? ' AND o.date_add <="' . pSQL(Tools::getValue('ETS_PRES2PRES_ORDER_TO')) . '"' : '');
            }
            if (in_array($definition['table'], array('cart'))) {
                $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'orders o ON (tb.id_cart = o.id_cart)';
                $sql . ' WHERE 1 ' . (!$multishop && version_compare(_PS_VERSION_, '1.5', '>=') ? ' AND o.id_shop="' . (int)Context::getContext()->shop->id . '"' : '') . (Tools::getValue('ETS_PRES2PRES_ORDER_FROM') ? ' AND o.date_add >="' . pSQL(Tools::getValue('ETS_PRES2PRES_ORDER_FROM')) . '"' : '') . (Tools::getValue('ETS_PRES2PRES_ORDER_TO') ? ' AND o.date_add <="' . pSQL(Tools::getValue('ETS_PRES2PRES_ORDER_TO')) . '"' : '');
            }
            $countTotal = Db::getInstance()->getValue($sql);
            if ((int)Configuration::get('ETS_DT_NUMBER_RECORD') > 0)
                $number_record = (int)Configuration::get('ETS_DT_NUMBER_RECORD');
            else
                $number_record = 200;
            if ($countTotal <= $number_record) {
                if (!file_exists($dir . '/' . $file_name . '.xml')) {
                    Configuration::updateValue('ETS_TABLE_EXPORT', $definition['table']);
                    file_put_contents($dir . '/' . $file_name . '.xml', $this->exportData($definition, $multishop));
                }
            } else {
                $number_file = ceil($countTotal / $number_record);
                if ($number_file) {
                    for ($i = 1; $i <= $number_file; $i++) {
                        if (!file_exists($dir . '/' . $file_name . '_' . $i . '.xml')) {
                            Configuration::updateValue('ETS_TABLE_EXPORT', $definition['table']);
                            file_put_contents($dir . '/' . $file_name . '_' . $i . '.xml', $this->exportData($definition, $multishop, true, $i, $number_record));
                        }
                    }
                }
            }
        } else {
            if (!file_exists($dir . '/' . $file_name . '.xml')) {
                Configuration::updateValue('ETS_TABLE_EXPORT', $definition['table']);
                file_put_contents($dir . '/' . $file_name . '.xml', $this->exportData($definition, $multishop));
            }

        }
        return $ok;
    }

    public function exportData($definition, $multishop = false, $divide_file = false, $page = 1, $number_record = 200)
    {
        $sql = "SELECT * FROM " . _DB_PREFIX_ . pSQL($definition['table']) . ' tb';
        if ($definition['table'] == 'product_attribute' || $definition['table'] == 'tax_rules_group' || $definition['table'] == 'manufacturer' || $definition['table'] == 'supplier' || $definition['table'] == 'group' || $definition['table'] == 'feature' || $definition['table'] == 'attribute_group' || $definition['table'] == 'attribute' || $definition['table'] == 'image' || $definition['table'] == 'employee' || $definition['table'] == 'country' || $definition['table'] == 'zone')
            $definition['multilang_shop'] = true;
        if (isset($definition['multilang_shop']) && $definition['multilang_shop'])
            $sql .= " INNER JOIN " . _DB_PREFIX_ . pSQL($definition['table']) . "_shop tbs ON(tb." . pSQL($definition['primary']) . "= tbs." . pSQL($definition['primary']) . ($multishop == false ? ' AND tbs.id_shop =' . (int)Context::getContext()->shop->id : '') . ") GROUP BY tb." . pSQL($definition['primary']);
        if (in_array($definition['table'], array('orders'))) {
            $sql .= ' WHERE 1 ' . (!$multishop && version_compare(_PS_VERSION_, '1.5', '>=') ? ' AND tb.id_shop="' . (int)Context::getContext()->shop->id . '"' : '') . (Tools::getValue('ETS_PRES2PRES_ORDER_FROM') ? ' AND tb.date_add >="' . pSQL(Tools::getValue('ETS_PRES2PRES_ORDER_FROM')) . '"' : '') . (Tools::getValue('ETS_PRES2PRES_ORDER_TO') ? ' AND tb.date_add <="' . pSQL(Tools::getValue('ETS_PRES2PRES_ORDER_TO')) . '"' : '');
        }
        if (in_array($definition['table'], array('order_history', 'order_invoice', 'order_slip', 'order_cart_rule', 'order_carrier'))) {
            $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'orders o ON (tb.id_order = o.id_order)';
            $sql .= ' WHERE 1 ' . (!$multishop && version_compare(_PS_VERSION_, '1.5', '>=') ? ' AND o.id_shop="' . (int)Context::getContext()->shop->id . '"' : '') . (Tools::getValue('ETS_PRES2PRES_ORDER_FROM') ? ' AND o.date_add >="' . pSQL(Tools::getValue('ETS_PRES2PRES_ORDER_FROM')) . '"' : '') . (Tools::getValue('ETS_PRES2PRES_ORDER_TO') ? ' AND o.date_add <="' . pSQL(Tools::getValue('ETS_PRES2PRES_ORDER_TO')) . '"' : '');
        }
        if (in_array($definition['table'], array('cart'))) {
            $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'orders o ON (tb.id_cart = o.id_cart)';
            $sql .= ' WHERE 1 ' . (!$multishop && version_compare(_PS_VERSION_, '1.5', '>=') ? ' AND o.id_shop="' . (int)Context::getContext()->shop->id . '"' : '') . (Tools::getValue('ETS_PRES2PRES_ORDER_FROM') ? ' AND o.date_add >="' . pSQL(Tools::getValue('ETS_PRES2PRES_ORDER_FROM')) . '"' : '') . (Tools::getValue('ETS_PRES2PRES_ORDER_TO') ? ' AND o.date_add <="' . pSQL(Tools::getValue('ETS_PRES2PRES_ORDER_TO')) . '"' : '');
        }
        if ($divide_file)
            $sql .= ' LIMIT ' . ((int)$page - 1) * (int)$number_record . ', ' . (int)$number_record;
        $datas = Db::getInstance()->executeS($sql);
        if ($datas) {
            foreach ($datas as &$data) {
                if (isset($definition['multilang_shop']) && $definition['multilang_shop']) {
                    $data['datashops'] = Db::getInstance()->executeS('SELECT s.id_shop,tbs.* FROM ' . _DB_PREFIX_ . 'shop s ,' . _DB_PREFIX_ . pSQL($definition['table']) . '_shop tbs WHERE s.id_shop=tbs.id_shop AND tbs.' . pSQL($definition['primary']) . '=' . (int)$data[$definition['primary']] . (!$multishop ? ' AND tbs.id_shop="' . (int)Context::getContext()->shop->id . '"' : ''));
                }
                if (isset($definition['multilang']) && $definition['multilang'])
                    $data['datalanguages'] = Db::getInstance()->executeS('SELECT tbl.*,l.iso_code FROM ' . _DB_PREFIX_ . pSQL($definition['table']) . '_lang tbl,' . _DB_PREFIX_ . 'lang l  WHERE tbl.id_lang=l.id_lang AND tbl.' . pSQL($definition['primary']) . ' =' . (int)$data[$definition['primary']] . ($definition['multilang_temp'] && !$multishop ? ' AND tbl.id_shop="' . (int)Context::getContext()->shop->id . '"' : ''));
            }
        }
        unset($data);
        $xml_output = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml_output .= '<entity_profile>' . "\n";
        $fields = $definition['fields'];
        if ($datas)
            foreach ($datas as $data) {
                if ($definition['table'] == 'stock_available' &&  isset($data['id_product_attribute']) && $data['id_product_attribute'] != 0) {
                    $id_product_attribute = (int)Db::getInstance()->execute('SELECT id_product_attribute FROM '._DB_PREFIX_.'product_attribute WHERE id_product_attribute = '.(int)$data['id_product_attribute'], false);
                    if (!$id_product_attribute)
                        continue;
                }
                $url = '';
                $xml_output .= '<' . $definition['table'] . '>';
                $xml_output .= '<' . $definition['primary'] . '><![CDATA[' . $data[$definition['primary']] . ']]></' . $definition['primary'] . '>' . "\n";
                if ($fields) {
                    foreach ($fields as $key => $field) {
                        if (!isset($field['lang']) || !$field['lang']) {
                            $xml_output .= '<' . $key . '><![CDATA[' . ($field['type'] == ObjectModel::TYPE_HTML? $this->strip_tags($data[$key]) : $data[$key]) . ']]></' . $key . '>' . "\n";
                        }
                    }
                }
                if ($definition['table'] == 'image') {
                    $folders = str_split((string)$data['id_image']);
                    $path = implode('/', $folders) . '/';
                    $url = $this->_module->getBaseLink() . 'img/p/' . $path . $data['id_image'] . '.jpg';
                } elseif ($definition['table'] == 'carrier' || $definition['table'] == 'product' || $definition['table'] == 'category' || $definition['table'] == 'supplier' || $definition['table'] == 'manufacturer') {
                    $ssl = (Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE'));
                    $base = $ssl ? 'https://' . $this->context->shop->domain_ssl : 'http://' . $this->context->shop->domain;
                    if ($definition['table'] == 'carrier' && file_exists(_PS_SHIP_IMG_DIR_ . (int)$data['id_carrier'] . '.jpg'))
                        $url = $base . $this->context->shop->getBaseURI() . 'img/s/' . $data['id_carrier'] . '.jpg';
                    elseif ($definition['table'] == 'category' && file_exists(_PS_CAT_IMG_DIR_ . (int)$data['id_category'] . '.jpg'))
                        $url = $base . $this->context->shop->getBaseURI() . 'img/c/' . $data['id_category'] . '.jpg';
                    elseif ($definition['table'] == 'supplier' && file_exists(_PS_SUPP_IMG_DIR_ . (int)$data['id_supplier'] . '.jpg'))
                        $url = $base . $this->context->shop->getBaseURI() . 'img/su/' . $data['id_supplier'] . '.jpg';
                    elseif ($definition['table'] == 'manufacturer' && file_exists(_PS_MANU_IMG_DIR_ . (int)$data['id_manufacturer'] . '.jpg'))
                        $url = $base . $this->context->shop->getBaseURI() . 'img/m/' . $data['id_manufacturer'] . '.jpg';
                }
                if ($url) {
                    $xml_output .= '<link_image><![CDATA[' . $url . ']]></link_image>' . "\n";
                }
                if ($definition['table'] == 'group') {
                    if ($data['id_group'] == Configuration::get('PS_UNIDENTIFIED_GROUP')) {
                        $xml_output .= '<visitor_group><![CDATA[1]]></visitor_group>' . "\n";
                    }
                    if ($data['id_group'] == Configuration::get('PS_GUEST_GROUP')) {
                        $xml_output .= '<guest_group><![CDATA[1]]></guest_group>' . "\n";
                    }
                    if ($data['id_group'] == Configuration::get('PS_CUSTOMER_GROUP')) {
                        $xml_output .= '<customer_group><![CDATA[1]]></customer_group>' . "\n";
                    }
                }
                if (isset($data['datalanguages']) && $data['datalanguages']) {
                    foreach ($data['datalanguages'] as $datalanguage) {
                        $xml_output .= '<datalanguage iso_code="' . $datalanguage['iso_code'] . '"' . ($datalanguage['id_lang'] == Configuration::get('PS_LANG_DEFAULT') ? ' default="1"' : '') . ' >' . "\n";
                        foreach ($fields as $key => $field)
                            if (isset($field['lang']) && $field['lang'])
                                $xml_output .= '<' . $key . '><![CDATA[' . ($field['type'] == ObjectModel::TYPE_HTML? $this->strip_tags($datalanguage[$key]) : $datalanguage[$key]) . ']]></' . $key . '>' . "\n";
                        if (isset($datalanguage['id_shop']))
                            $xml_output .= '<id_shop><![CDATA[' . $datalanguage['id_shop'] . ']]></id_shop>' . "\n";
                        $xml_output .= '</datalanguage>' . "\n";
                    }
                }
                if (isset($data['datashops']) && $data['datashops']) {
                    foreach ($data['datashops'] as $shop) {
                        $xml_output .= '<datashop id_shop="' . $shop['id_shop'] . '"';
                        if ($fields) {
                            foreach ($fields as $key => $field)
                                if (isset($field['shop']) && $field['shop'] && isset($shop[$key]))
                                    $xml_output .= ' ' . $key . '="' . str_replace('&', 'and', $shop[$key]) . '"';
                        }
                        $xml_output .= '></datashop>' . "\n";
                    }
                }
                $xml_output .= '</' . $definition['table'] . '>' . "\n";
                $exported = (int)Configuration::get('ETS_DATAMASTER_EXPORTED');
                $exported++;
                Configuration::updateValue('ETS_DATAMASTER_EXPORTED', $exported);
            }
        $xml_output .= '</entity_profile>' . "\n";
        return $this->_sanitizeXML($xml_output);
    }

    public function strip_tags($string)
    {
        $string =  preg_replace('/<script(.*?)>(.*?)<\/script>/is', "", $string);
        $string =  preg_replace('/<script(.*?)>(.*?)<(?<!\/script)(.+?)>/is', "<$3>", $string);
        return $string;
    }

    public function addFileXMl14($dir, $file_name, $table, $primary = '', $multilang = false)
    {
        $ok = true;
        if (!Db::getInstance()->getValue("SELECT count(*) FROM " . _DB_PREFIX_ . pSQL($table) . ' tb'))
            return $ok;
        if (Configuration::get('ETS_DATAMASTER_DIVIDE_FILE')) {
            $sql = "SELECT count(*) FROM " . _DB_PREFIX_ . pSQL($table) . ' tb';
            $countTotal = Db::getInstance()->getValue($sql);
            if ((int)Configuration::get('ETS_DT_NUMBER_RECORD') > 0)
                $number_record = (int)Configuration::get('ETS_DT_NUMBER_RECORD');
            else
                $number_record = 200;
            if ($countTotal <= $number_record) {
                if (!file_exists($dir . '/' . $file_name . '.xml')) {
                    Configuration::updateValue('ETS_TABLE_EXPORT', $table);
                    file_put_contents($dir . '/' . $file_name . '.xml', $this->exportData14($table, $primary, $multilang));

                }
            } else {
                $number_file = ceil($countTotal / $number_record);
                if ($number_file) {
                    for ($i = 1; $i <= $number_file; $i++) {
                        if (!file_exists($dir . '/' . $file_name . '_' . $i . '.xml')) {
                            Configuration::updateValue('ETS_TABLE_EXPORT', $table);
                            file_put_contents($dir . '/' . $file_name . '_' . $i . '.xml', $this->exportData14($table, $primary, $multilang, true, $i, $number_record));
                        }
                    }
                }
            }
        } else {
            if (!file_exists($dir . '/' . $file_name . '.xml')) {
                Configuration::updateValue('ETS_TABLE_EXPORT', $table);
                file_put_contents($dir . '/' . $file_name . '.xml', $this->exportData14($table, $primary, $multilang));
            }
        }
        return $ok;
    }

    public function exportData14($table, $primary = '', $multilang = false, $divide_file = false, $page = 1, $number_record = 200)
    {
        $sql = "SELECT * FROM " . _DB_PREFIX_ . pSQl($table) . ' tb' . ($divide_file == true ? ' LIMIT ' . ((int)$page - 1) * (int)$number_record . ',' . (int)$number_record : '');
        $datas = Db::getInstance()->executeS($sql);
        if ($datas && $multilang) {
            foreach ($datas as &$data) {
                $data['datalanguages'] = Db::getInstance()->executeS('SELECT tbl.*,l.iso_code FROM ' . _DB_PREFIX_ . pSQL($table) . '_lang tbl,' . _DB_PREFIX_ . 'lang l  WHERE tbl.id_lang=l.id_lang AND tbl.' . pSQL($primary) . ' =' . (int)$data[$primary]);
            }
        }
        unset($data);
        $xml_output = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml_output .= '<entity_profile>' . "\n";
        if ($datas)
            foreach ($datas as $data) {
                $url = '';
                $xml_output .= '<' . $table . '>';
                foreach ($data as $key => $val) {
                    if ($key != 'datalanguages') {
                        $xml_output .= '<' . $key . '><![CDATA[' . $val . ']]></' . $key . '>' . "\n";
                    }
                }
                if ($table == 'order_detail') {
                    $p = new Product((int)$data['product_id']);
                    $price = $p->getPrice(true, (int)$data['product_attribute_id']);
                    $price_wt = $p->getPrice(false, (int)$data['product_attribute_id']);
                    $xml_output .= '<total_price_tax_incl><![CDATA[' . ($price * (int)$data['product_quantity']) . ']]></total_price_tax_incl>' . "\n";
                    $xml_output .= '<total_price_tax_excl><![CDATA[' . ($price_wt * (int)$data['product_quantity']) . ']]></total_price_tax_excl>' . "\n";
                    $xml_output .= '<unit_price_tax_incl><![CDATA[' . $price . ']]></unit_price_tax_incl>' . "\n";
                    $xml_output .= '<unit_price_tax_excl><![CDATA[' . $price_wt . ']]></unit_price_tax_excl>' . "\n";
                    $xml_output .= '<total_shipping_price_tax_incl><![CDATA[0.000000]]></total_shipping_price_tax_incl>' . "\n";
                    $xml_output .= '<total_shipping_price_tax_excl><![CDATA[0.000000]]></total_shipping_price_tax_excl>' . "\n";
                    $xml_output .= '<purchase_supplier_price><![CDATA[0.000000]]></purchase_supplier_price>' . "\n";
                    $xml_output .= '<original_product_price><![CDATA[' . $p->price . ']]></original_product_price>' . "\n";
                    $xml_output .= '<original_wholesale_price><![CDATA[' . $p->wholesale_price . ']]></original_wholesale_price>' . "\n";
                }
                if ($table == 'attribute_group') {
                    if ($data['is_color_group'])
                        $xml_output .= '<group_type><![CDATA[color]]></group_type>' . "\n";
                    else
                        $xml_output .= '<group_type><![CDATA[select]]></group_type>' . "\n";
                }
                if ($table == 'image') {
                    if (file_exists(_PS_PROD_IMG_DIR_ . $data['id_product'] . '-' . (int)$data['id_image'] . '.jpg')) {
                        $ssl = (Configuration::get('PS_SSL_ENABLED'));
                        $base = $ssl ? 'https://' . Configuration::get('PS_SHOP_DOMAIN_SSL') : 'http://' . Configuration::get('PS_SHOP_DOMAIN');
                        $url = $base . __PS_BASE_URI__ . 'img/p/' . $data['id_product'] . '-' . (int)$data['id_image'] . '.jpg';
                    } else {
                        $folders = str_split((string)$data['id_image']);
                        $path = implode('/', $folders) . '/';
                        $url = $this->_module->getBaseLink() . 'img/p/' . $path . $data['id_image'] . '.jpg';
                    }
                } elseif ($table == 'carrier' || $table == 'category' || $table == 'supplier' || $table == 'manufacturer') {
                    $ssl = (Configuration::get('PS_SSL_ENABLED'));
                    $base = $ssl ? 'https://' . Configuration::get('PS_SHOP_DOMAIN_SSL') : 'http://' . Configuration::get('PS_SHOP_DOMAIN');
                    if ($table == 'carrier' && file_exists(_PS_SHIP_IMG_DIR_ . (int)$data['id_carrier'] . '.jpg'))
                        $url = $base . __PS_BASE_URI__ . 'img/s/' . $data['id_carrier'] . '.jpg';
                    elseif ($table == 'category' && file_exists(_PS_CAT_IMG_DIR_ . (int)$data['id_category'] . '.jpg'))
                        $url = $base . __PS_BASE_URI__ . 'img/c/' . $data['id_category'] . '.jpg';
                    elseif ($table == 'supplier' && file_exists(_PS_SUPP_IMG_DIR_ . (int)$data['id_supplier'] . '.jpg'))
                        $url = $base . __PS_BASE_URI__ . 'img/su/' . $data['id_supplier'] . '.jpg';
                    elseif ($table == 'manufacturer' && file_exists(_PS_SHIP_IMG_DIR_ . (int)$data['id_manufacturer'] . '.jpg'))
                        $url = $base . __PS_BASE_URI__ . 'img/m/' . $data['id_manufacturer'] . '.jpg';

                }
                if ($url) {
                    $xml_output .= '<link_image><![CDATA[' . $url . ']]></link_image>' . "\n";
                }
                if ($table == 'group') {
                    if ($data['id_group'] == 1) {
                        $xml_output .= '<default_group><![CDATA[1]]></default_group>' . "\n";
                    }
                }
                if (isset($data['datalanguages']) && $data['datalanguages']) {
                    foreach ($data['datalanguages'] as $datalanguage) {
                        $xml_output .= '<datalanguage iso_code="' . $datalanguage['iso_code'] . '"' . ($datalanguage['id_lang'] == Configuration::get('PS_LANG_DEFAULT') ? ' default="1"' : '') . '>' . "\n";
                        foreach ($datalanguage as $klang => $vlang)
                            if ($klang != 'iso_code' && $klang != $primary && $klang != 'id_lang')
                                $xml_output .= '<' . $klang . '><![CDATA[' . $vlang . ']]></' . $klang . '>' . "\n";
                        $xml_output .= '</datalanguage>' . "\n";
                    }
                }
                $xml_output .= '</' . $table . '>' . "\n";
                $exported = (int)Configuration::get('ETS_DATAMASTER_EXPORTED');
                $exported++;
                Configuration::updateValue('ETS_DATAMASTER_EXPORTED', $exported);
            }
        $xml_output .= '</entity_profile>' . "\n";
        $xml_output = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $xml_output);
        return $this->_sanitizeXML($xml_output);;
    }

    public function exportDataCSV($definition, $extra_field = array(), $no_select = array())
    {
        $fields = $definition['fields'];
        $left_join = '';
        $field_select = 'tb.' . $definition['primary'];
        if ($fields) {
            foreach ($fields as $key => $field) {
                if (in_array($key, $no_select))
                    continue;
                if (isset($field['lang']) && $field['lang'])
                    $field_select .= ',tbl.' . $key;
                elseif (isset($field['shop']) && $field['shop'])
                    $field_select .= ',tbs.' . $key;
                else
                    $field_select .= ',tb.' . $key;
                if (isset($extra_field[$key]) && $extra_field[$key]) {
                    $foreign_array = $extra_field[$key];
                    $left_join .= ' LEFT JOIN ' . _DB_PREFIX_ . $foreign_array['table_parent'] . ' ON (tb.' . $key . ' = ' . $foreign_array['table_as'] . '.' . $foreign_array['foreign_key'] . ')';
                    $field_select .= ',' . $foreign_array['table_as'] . '.' . $foreign_array['key'] . ' as ' . $foreign_array['name'];
                }
            }
        }
        if (!$field_select)
            return '';
        $sql = "SELECT " . pSQL(trim($field_select, ',')) . " FROM " . _DB_PREFIX_ . pSQL($definition['table']) . ' tb';
        if ($definition['table'] == 'product_attribute' || $definition['table'] == 'tax_rules_group' || $definition['table'] == 'manufacturer' || $definition['table'] == 'supplier')
            $definition['multilang_shop'] = true;
        if (isset($definition['multilang_shop']) && $definition['multilang_shop'])
            $sql .= " INNER JOIN " . _DB_PREFIX_ . pSQL($definition['table']) . "_shop tbs ON(tb." . pSQL($definition['primary']) . "= tbs." . pSQL($definition['primary']) . ' AND tbs.id_shop =' . (int)Context::getContext()->shop->id . ")";
        if (isset($definition['multilang']) && $definition['multilang'])
            $sql .= " LEFT JOIN " . _DB_PREFIX_ . pSQL($definition['table']) . "_lang tbl ON(tb." . pSQL($definition['primary']) . "= tbl." . pSQL($definition['primary']) . ' AND tbl.id_lang =' . (int)Context::getContext()->language->id . ")";
        $sql .= $left_join;
        $sql .= " GROUP BY tb." . pSQL($definition['primary']);
        $datas = Db::getInstance()->executeS($sql);
        return $this->createDataCSV($datas);
    }

    public function createDataCSV($datas)
    {
        $flag = false;
        $price_key = array('price', 'total_paid_tax_incl', 'total_paid_tax_excl', 'total_price_tax_excl', 'total_price_tax_incl');
        if ($datas) {
            $keys = array();
            $xls_output = '<table border="1">';
            foreach ($datas as $row) {
                if (!$flag) {
                    $keys = array_keys($row);
                    $xls_output .= '<tr>';
                    foreach ($keys as $key) {
                        $xls_output .= '<td>' . Ets_oneclicktomigrate::upperFirstChar(str_replace('_', ' ', $key)) . '</td>';
                        if (in_array($key, $price_key))
                            $xls_output .= '<td>' . Ets_oneclicktomigrate::upperFirstChar(str_replace('_', ' ', $key)) . '(currency)</td>';
                    }
                    $flag = true;
                }
                $values = array_values($row);
                $xls_output .= '<tr>';
                foreach ($values as $index => $value) {
                    $xls_output .= '<td>' . $value . '</td>';
                    if (in_array($keys[$index], $price_key))
                        $xls_output .= '<td>' . Tools::displayPrice($value) . '</td>';
                }
                $xls_output .= '</tr>';
            }
            $xls_output .= '</table>';
            return $xls_output;
        }
        return '';
    }

    public function check_file_xml_exists($file_name)
    {
        if (isset(Context::getContext()->cookie->zip_file_name) && Context::getContext()->cookie->zip_file_name) {
            $savePath = dirname(__FILE__) . '/../cache/export/';
            $extractUrl = $savePath . Context::getContext()->cookie->zip_file_name;
            if (@file_exists($extractUrl)) {
                $zip = new ZipArchive();
                if ($zip->open($extractUrl) === true) {
                    if ($zip->locateName($file_name) !== false) {
                        $z = new ZipArchive();
                        if ($z->open($extractUrl)) {
                            $content = $z->getFromName($file_name);
                            return $content;
                        }

                    }
                }
            }
        }
        return false;
    }

    public function _sanitizeXML($string)
    {
        if (!empty($string)) {
            // remove EOT+NOREP+EOX|EOT+<char> sequence (FatturaPA)
            $string = preg_replace('/(\x{0004}(?:\x{201A}|\x{FFFD})(?:\x{0003}|\x{0004}).)/u', '', $string);
            $regex = '/(
                [\xC0-\xC1] # Invalid UTF-8 Bytes
                | [\xF5-\xFF] # Invalid UTF-8 Bytes
                | \xE0[\x80-\x9F] # Overlong encoding of prior code point
                | \xF0[\x80-\x8F] # Overlong encoding of prior code point
                | [\xC2-\xDF](?![\x80-\xBF]) # Invalid UTF-8 Sequence Start
                | [\xE0-\xEF](?![\x80-\xBF]{2}) # Invalid UTF-8 Sequence Start
                | [\xF0-\xF4](?![\x80-\xBF]{3}) # Invalid UTF-8 Sequence Start
                | (?<=[\x0-\x7F\xF5-\xFF])[\x80-\xBF] # Invalid UTF-8 Sequence Middle
                | (?<![\xC2-\xDF]|[\xE0-\xEF]|[\xE0-\xEF][\x80-\xBF]|[\xF0-\xF4]|[\xF0-\xF4][\x80-\xBF]|[\xF0-\xF4][\x80-\xBF]{2})[\x80-\xBF] # Overlong Sequence
                | (?<=[\xE0-\xEF])[\x80-\xBF](?![\x80-\xBF]) # Short 3 byte sequence
                | (?<=[\xF0-\xF4])[\x80-\xBF](?![\x80-\xBF]{2}) # Short 4 byte sequence
                | (?<=[\xF0-\xF4][\x80-\xBF])[\x80-\xBF](?![\x80-\xBF]) # Short 4 byte sequence (2)
            )/x';
            $string = preg_replace($regex, '', $string);
            $string = $this->utf8_for_xml($string);
        }
        return $string;
    }

    public function utf8_for_xml($string)
    {
        return preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', '', $string);
    }
}
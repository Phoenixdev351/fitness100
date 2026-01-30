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

include_once(_PS_MODULE_DIR_ . 'ets_oneclicktomigrate/classes/ReadXMl.php');

class ExtraImport extends Module
{
    public $_module;
    public $imported;

    public function __construct()
    {
        parent::__construct();
        $this->context = Context::getContext();
        $this->_module = Module::getInstanceByName('ets_oneclicktomigrate');
    }

    // import
    public function importProductTag($file_xml)
    {
        $this->imported = (int)Db::getInstance()->getValue('SELECT number_import FROM ' . _DB_PREFIX_ . 'ets_import_history WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
        $readXML = new ReadXML($file_xml);
        while ($xml = $readXML->_readXML()) {
            Configuration::updateValue('PS_DATAMASTER_IMPORTING', _DB_PREFIX_ . 'product_tag');
            if ($xml->product_tag) {
                foreach ($xml->product_tag as $producttag_xml) {
                    $id_product = $this->_module->getNewID('product', (int)$producttag_xml->id_product);
                    $id_tag = $this->_module->getNewID('tag', (int)$producttag_xml->id_tag);
                    if (version_compare(_PS_VERSION_, '1.6', '>=')) {
                        if (isset($producttag_xml->id_lang) && (int)$producttag_xml->id_lang) {
                            $id_lang = $this->_module->getNewID('lang', $producttag_xml->id_lang);
                            if (!Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'product_tag WHERE id_product="' . (int)$id_product . '" AND id_tag="' . (int)$id_tag . '"', 0)) {
                                Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'product_tag(id_product,id_tag,id_lang) values("' . (int)$id_product . '","' . (int)$id_tag . '","' . (int)$id_lang . '")');
                                $this->stopResetImport();
                            }
                        } else {
                            if (!Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'product_tag WHERE id_product="' . (int)$id_product . '" AND id_tag="' . (int)$id_tag . '"', 0)) {
                                Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'product_tag(id_product,id_tag) values("' . (int)$id_product . '","' . (int)$id_tag . '")');
                                $this->stopResetImport();
                            }
                        }
                    } else {
                        if (!Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'product_tag WHERE id_product="' . (int)$id_product . '" AND id_tag="' . (int)$id_tag . '"', 0)) {
                            Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'product_tag(id_product,id_tag) values("' . (int)$id_product . '","' . (int)$id_tag . '")');
                            $this->stopResetImport();
                        }
                    }
                }
            }
            $readXML->deleteFileXML();
        }
        if ($readXML->imported)
            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'ets_import_history set currentindex=1 WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
    }

    public function importProductPack($file_xml)
    {
        $this->imported = (int)Db::getInstance()->getValue('SELECT number_import FROM ' . _DB_PREFIX_ . 'ets_import_history WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
        $readXML = new ReadXML($file_xml);
        while ($xml = $readXML->_readXML()) {
            Configuration::updateValue('PS_DATAMASTER_IMPORTING', _DB_PREFIX_ . 'pack');
            if ($xml->pack) {
                foreach ($xml->pack as $pack_xml) {
                    $id_product_pack = $this->_module->getNewID('product', (int)$pack_xml->id_product_pack);
                    $id_product_item = $this->_module->getNewID('product', (int)$pack_xml->id_product_item);
                    if (isset($pack_xml->id_product_attribute_item))
                        $id_product_attribute_item = $this->_module->getNewID('product_attribute', (int)$pack_xml->id_product_attribute_item);
                    else
                        $id_product_attribute_item = 0;
                    if (version_compare(_PS_VERSION_, '1.6', '>=')) {
                        if (!Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'pack WHERE id_product_pack="' . (int)$id_product_pack . '" AND id_product_item="' . (int)$id_product_item . '" AND id_product_attribute_item="' . (int)$id_product_attribute_item . '"', 0)) {
                            Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'pack(id_product_pack,id_product_item,id_product_attribute_item,quantity) values("' . (int)$id_product_pack . '","' . (int)$id_product_item . '","' . (int)$id_product_attribute_item . '","' . (int)$pack_xml->quantity . '")');
                            $this->stopResetImport();
                        }
                    } else {
                        if (!Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'pack WHERE id_product_pack="' . (int)$id_product_pack . '" AND id_product_item="' . (int)$id_product_item . '"', 0)) {
                            Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'pack(id_product_pack,id_product_item,quantity) values("' . (int)$id_product_pack . '","' . (int)$id_product_item . '","' . (int)$pack_xml->quantity . '")');
                            $this->stopResetImport();
                        }
                    }
                }
            }
            $readXML->deleteFileXML();
        }
        if ($readXML->imported)
            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'ets_import_history set currentindex=1 WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
    }

    public function ImportCustomizedData($file_xml)
    {
        $this->imported = (int)Db::getInstance()->getValue('SELECT number_import FROM ' . _DB_PREFIX_ . 'ets_import_history WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
        $readXML = new ReadXML($file_xml);
        while ($xml = $readXML->_readXML()) {
            Configuration::updateValue('PS_DATAMASTER_IMPORTING', _DB_PREFIX_ . 'customized_data');
            if ($xml->customized_data) {
                foreach ($xml->customized_data as $customizeddata_xml) {
                    if (isset($customizeddata_xml->id_module) && (int)$customizeddata_xml->id_module)
                        continue;
                    $id_customization = (int)$this->_module->getNewID('customization', (int)$customizeddata_xml->id_customization);
                    $index = (int)$this->_module->getNewID('customization_field', (int)$customizeddata_xml->index);
                    if (!Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'customized_data WHERE id_customization="' . (int)$id_customization . '" AND index="' . (int)$index . '"', 0) && $id_customization && $index) {
                        Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'customized_data(id_customization,type,index,value) VALUES("' . (int)$id_customization . '","' . (int)$customizeddata_xml->type . '","' . (int)$index . '","' . pSQL((string)$customizeddata_xml->value) . '")');
                    }
                }
            }
            $readXML->deleteFileXML();
        }
        if ($readXML->imported)
            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'ets_import_history set currentindex=1 WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
    }

    public function importCategoryGroup($category = false)
    {
        $this->imported = (int)Db::getInstance()->getValue('SELECT number_import FROM ' . _DB_PREFIX_ . 'ets_import_history WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
        if ($category) {
            $categories = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'ets_category_import WHERE id_import_history=' . (int)$this->context->cookie->id_import_history, true, 0);
            if ($categories) {
                foreach ($categories as $category) {
                    $id_category = $category['id_new'];
                    $groups = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'group');
                    if ($groups) {
                        foreach ($groups as $group) {
                            if (!Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'category_group WHERE id_category="' . (int)$id_category . '" AND id_group="' . (int)$group['id_group'] . '"', 0)) {
                                Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'category_group (id_category,id_group) VALUES("' . (int)$id_category . '","' . (int)$group['id_group'] . '")');
                                $this->stopResetImport();
                            }
                        }
                    }
                }
            }
        } else {
            $groups = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'ets_group_import WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
            if ($groups) {
                foreach ($groups as $group) {
                    $id_group = $group['id_new'];
                    $categories = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'category');
                    if ($categories) {
                        foreach ($categories as $category) {
                            if (!Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'category_group WHERE id_category="' . (int)$category['id_category'] . '" AND id_group="' . (int)$id_group . '"')) {
                                Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'category_group (id_category,id_group) VALUES("' . (int)$category['id_category'] . '","' . (int)$id_group . '")');
                                $this->stopResetImport();
                            }
                        }
                    }
                }
            }
        }
    }

    public function importOrderDetailTax($file_xml)
    {
        $this->imported = (int)Db::getInstance()->getValue('SELECT number_import FROM ' . _DB_PREFIX_ . 'ets_import_history WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
        $readXML = new ReadXML($file_xml);
        while ($xml = $readXML->_readXML()) {
            Configuration::updateValue('PS_DATAMASTER_IMPORTING', _DB_PREFIX_ . 'order_detail_tax');
            if ($xml->order_detail_tax) {
                foreach ($xml->order_detail_tax as $order_detail_tax) {
                    $id_order_detail = (int)$this->_module->getNewID('order_detail', (int)$order_detail_tax->id_order_detail);
                    if (!($id_tax = (int)$this->_module->getNewID('tax', (int)$order_detail_tax->id_tax))) {
                        $id_tax = (int)Db::getInstance()->getValue('SELECT id_tax FROM ' . _DB_PREFIX_ . 'tax WHERE id_tax = ' . (int)$order_detail_tax->id_tax);
                    }
                    if (!Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'order_detail_tax WHERE id_order_detail = "' . (int)$id_order_detail . '" AND id_tax = "' . (int)$id_tax . '"', 0)) {
                        Db::getInstance()->execute('
                            INSERT INTO ' . _DB_PREFIX_ . 'order_detail_tax (id_order_detail, id_tax, unit_amount, total_amount) 
                            VALUES(' . (int)$id_order_detail . ',' . (int)$id_tax . ', "' . pSQL($order_detail_tax->unit_amount) . '" ,' . (float)$order_detail_tax->total_amount . ')
                        ');
                        $this->stopResetImport('order_detail_tax');
                    }
                }
            }
            $readXML->deleteFileXML();
        }
        if ($readXML->imported)
            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'ets_import_history set currentindex=1 WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
    }

    public function importOrderInvoiceTax($file_xml)
    {
        $this->imported = (int)Db::getInstance()->getValue('SELECT number_import FROM ' . _DB_PREFIX_ . 'ets_import_history WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
        $readXML = new ReadXML($file_xml);
        while ($xml = $readXML->_readXML()) {
            Configuration::updateValue('PS_DATAMASTER_IMPORTING', _DB_PREFIX_ . 'order_invoice_tax');
            if ($xml->order_invoice_tax) {
                foreach ($xml->order_invoice_tax as $order_invoice_tax_xml) {
                    $id_order_invoice = (int)$this->_module->getNewID('order_invoice', (int)$order_invoice_tax_xml->id_order_invoice);
                    if (!($id_tax = (int)$this->_module->getNewID('tax', (int)$order_invoice_tax_xml->id_tax))) {
                        $id_tax = (int)Db::getInstance()->getValue('SELECT id_tax FROM ' . _DB_PREFIX_ . 'tax WHERE id_tax = ' . (int)$order_invoice_tax_xml->id_tax);
                    }
                    if (!Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'order_invoice_tax WHERE id_order_invoice = "' . (int)$id_order_invoice . '" AND id_tax = "' . (int)$id_tax . '"', 0)) {
                        Db::getInstance()->execute('
                            INSERT INTO ' . _DB_PREFIX_ . 'order_invoice_tax (id_order_invoice, `type`, id_tax, amount) 
                            VALUES(' . (int)$id_order_invoice . ' ,"' . pSQL($order_invoice_tax_xml->type) . '" ,' . (int)$id_tax . ' ,' . (float)$order_invoice_tax_xml->amount . ')
                        ');
                        $this->stopResetImport();
                    }
                }
            }
            $readXML->deleteFileXML();
        }
        if ($readXML->imported)
            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'ets_import_history set currentindex=1 WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
    }

    public function importOrderInvoicePayment($file_xml)
    {
        $this->imported = (int)Db::getInstance()->getValue('SELECT number_import FROM ' . _DB_PREFIX_ . 'ets_import_history WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
        $readXML = new ReadXML($file_xml);
        while ($xml = $readXML->_readXML()) {
            Configuration::updateValue('PS_DATAMASTER_IMPORTING', _DB_PREFIX_ . 'order_invoice_payment');
            if ($xml->order_invoice_payment) {
                foreach ($xml->order_invoice_payment as $order_invoice_payment_xml) {
                    $id_order_invoice = (int)$this->_module->getNewID('order_invoice', (int)$order_invoice_payment_xml->id_order_invoice);
                    $id_order_payment = (int)$this->_module->getNewID('order_payment', (int)$order_invoice_payment_xml->id_order_payment);
                    $id_order = (int)$this->_module->getNewID('orders', (int)$order_invoice_payment_xml->id_order);
                    if (!Db::getInstance()->getRow('
                            SELECT * FROM ' . _DB_PREFIX_ . 'order_invoice_payment 
                            WHERE id_order_invoice = ' . (int)$id_order_invoice . ' AND id_order_payment = ' . (int)$id_order_payment . ' AND id_order = ' . (int)$id_order , 0)
                    ) {
                        Db::getInstance()->execute('
                            INSERT INTO ' . _DB_PREFIX_ . 'order_invoice_payment (id_order_invoice, id_order_payment, id_order) 
                            VALUES(' . (int)$id_order_invoice . ' ,' . (int)$id_order_payment . ' ,' . (int)$id_order . ')
                        ');
                        $this->stopResetImport();
                    }
                }
            }
            $readXML->deleteFileXML();
        }
        if ($readXML->imported)
            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'ets_import_history set currentindex=1 WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
    }

    public function importCustomerGroup($file_xml)
    {
        $this->imported = (int)Db::getInstance()->getValue('SELECT number_import FROM ' . _DB_PREFIX_ . 'ets_import_history WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
        $readXML = new ReadXML($file_xml);
        while ($xml = $readXML->_readXML()) {
            Configuration::updateValue('PS_DATAMASTER_IMPORTING', _DB_PREFIX_ . 'customer_group');
            if ($xml->customer_group) {
                foreach ($xml->customer_group as $customergroup_xml) {
                    $id_customer = (int)$this->_module->getNewID('customer', (int)$customergroup_xml->id_customer);
                    $id_group = (int)$this->_module->getNewID('group', (int)$customergroup_xml->id_group);
                    if (!Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'customer_group WHERE id_customer="' . (int)$id_customer . '" AND id_group="' . (int)$id_group . '"', 0)) {
                        Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'customer_group (id_customer,id_group) VALUES("' . (int)$id_customer . '","' . (int)$id_group . '")');
                        $this->stopResetImport();
                    }
                }
            }
            $readXML->deleteFileXML();
        }
        if ($readXML->imported)
            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'ets_import_history set currentindex=1 WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
    }

    public function importProductCategory($file_xml)
    {
        $this->imported = (int)Db::getInstance()->getValue('SELECT number_import FROM ' . _DB_PREFIX_ . 'ets_import_history WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
        $readXML = new ReadXML($file_xml);
        while ($xml = $readXML->_readXML()) {
            Configuration::updateValue('PS_DATAMASTER_IMPORTING', _DB_PREFIX_ . 'category_product');
            if ($xml->category_product) {
                foreach ($xml->category_product as $productcategory_xml) {
                    $id_product = (int)$this->_module->getNewID('product', (int)$productcategory_xml->id_product);
                    $id_category = (int)$this->_module->getNewID('category', (int)$productcategory_xml->id_category);
                    if (!$id_category) {
                        $id_import_history = Context::getContext()->cookie->id_import_history;
                        $id_category = Db::getInstance()->getValue('SELECT id_category_default FROM ' . _DB_PREFIX_ . 'ets_import_history WHERE id_import_history ="' . (int)$id_import_history . '"');
                    }
                    if (!Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'category_product WHERE id_product="' . (int)$id_product . '" AND id_category="' . (int)$id_category . '"', 0)) {
                        Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'category_product (id_category, id_product, `position`) VALUES(' . (int)$id_category . ',' . (int)$id_product . ', '.(int)$productcategory_xml->position.')');
                        $this->stopResetImport();
                    }
                }
            }
            $readXML->deleteFileXML();
        }
        if ($readXML->imported)
            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'ets_import_history set currentindex=1 WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
    }

    public function importFeatureProduct($file_xml)
    {
        $this->imported = (int)Db::getInstance()->getValue('SELECT number_import FROM ' . _DB_PREFIX_ . 'ets_import_history WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
        $readXML = new ReadXML($file_xml);
        while ($xml = $readXML->_readXML()) {
            Configuration::updateValue('PS_DATAMASTER_IMPORTING', _DB_PREFIX_ . 'feature_product');
            if ($xml->feature_product) {
                foreach ($xml->feature_product as $featureproduct_xml) {
                    $id_product = (int)$this->_module->getNewID('product', (int)$featureproduct_xml->id_product);
                    $id_feature = (int)$this->_module->getNewID('feature', (int)$featureproduct_xml->id_feature);
                    $id_feature_value = (int)$this->_module->getNewID('feature_value', (int)$featureproduct_xml->id_feature_value);
                    if (!Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'feature_product WHERE id_product="' . (int)$id_product . '" AND id_feature="' . (int)$id_feature . '" AND id_feature_value ="' . (int)$id_feature_value . '"', 0)) {
                        Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'feature_product (id_feature,id_product,id_feature_value) VALUES ("' . (int)$id_feature . '","' . (int)$id_product . '","' . (int)$id_feature_value . '")');
                        $this->stopResetImport();
                    }
                }
            }
            $readXML->deleteFileXML();
        }
        if ($readXML->imported)
            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'ets_import_history set currentindex=1 WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
    }

    public function importProductAttributeCombination($file_xml)
    {
        $this->imported = (int)Db::getInstance()->getValue('SELECT number_import FROM ' . _DB_PREFIX_ . 'ets_import_history WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
        $readXML = new ReadXML($file_xml);
        while ($xml = $readXML->_readXML()) {
            Configuration::updateValue('PS_DATAMASTER_IMPORTING', _DB_PREFIX_ . 'product_attribute_combination');
            if ($xml->product_attribute_combination) {
                foreach ($xml->product_attribute_combination as $productatributecombination_xml) {
                    $id_product_attribute = (int)$this->_module->getNewID('product_attribute', (int)$productatributecombination_xml->id_product_attribute);
                    $id_attribute = (int)$this->_module->getNewID('attribute', (int)$productatributecombination_xml->id_attribute);
                    if (!Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'product_attribute_combination WHERE id_product_attribute ="' . (int)$id_product_attribute . '" AND id_attribute ="' . (int)$id_attribute . '"', 0)) {
                        Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'product_attribute_combination (id_product_attribute,id_attribute) VALUES("' . (int)$id_product_attribute . '","' . (int)$id_attribute . '")');
                        $this->stopResetImport();
                    }
                }
            }
            $readXML->deleteFileXML();
        }
        if ($readXML->imported)
            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'ets_import_history set currentindex=1 WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
    }

    public function importProductCarrier($file_xml)
    {
        $this->imported = (int)Db::getInstance()->getValue('SELECT number_import FROM ' . _DB_PREFIX_ . 'ets_import_history WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
        $readXML = new ReadXML($file_xml);
        while ($xml = $readXML->_readXML()) {
            Configuration::updateValue('PS_DATAMASTER_IMPORTING', _DB_PREFIX_ . 'product_carrier');
            if ($xml->product_carrier) {
                foreach ($xml->product_carrier as $productcarrier_xml) {
                    $id_product = (int)$this->_module->getNewID('product', (int)$productcarrier_xml->id_product);
                    $id_carrier_reference = (int)$this->_module->getNewID('reference', (int)$productcarrier_xml->id_carrier_reference);
                    $id_shop = (int)$this->_module->getNewID('shop', (int)$productcarrier_xml->id_shop);
                    if (!$id_shop)
                        $id_shop = $this->context->shop->id;
                    if ($id_product && $id_carrier_reference && $id_shop) {
                        if (!Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'product_carrier WHERE id_product="' . (int)$id_product . '" AND id_carrier_reference="' . (int)$id_carrier_reference . '" AND id_shop="' . (int)$id_shop . '"', 0)) {
                            Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'product_carrier(id_product,id_carrier_reference,id_shop) values("' . (int)$id_product . '","' . (int)$id_carrier_reference . '","' . (int)$id_shop . '")');
                            $this->stopResetImport();
                        }
                    }
                }
            }
            $readXML->deleteFileXML();
        }
        if ($readXML->imported)
            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'ets_import_history set currentindex=1 WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
    }

    public function importProductSupplier($file_xml)
    {
        $this->imported = (int)Db::getInstance()->getValue('SELECT number_import FROM ' . _DB_PREFIX_ . 'ets_import_history WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
        $readXML = new ReadXML($file_xml);
        while ($xml = $readXML->_readXML()) {
            Configuration::updateValue('PS_DATAMASTER_IMPORTING', _DB_PREFIX_ . 'product_supplier');
            if ($xml->product_supplier) {
                foreach ($xml->product_supplier as $productsupplier_xml) {
                    $id_product = (int)$this->_module->getNewID('product', (int)$productsupplier_xml->id_product);
                    $id_product_attribute = (int)$this->_module->getNewID('product_attribute', (int)$productsupplier_xml->id_product_attribute);
                    $id_supplier = (int)$this->_module->getNewID('supplier', (int)$productsupplier_xml->id_supplier);
                    if (!$id_supplier) {
                        $id_import_history = Context::getContext()->cookie->id_import_history;
                        $importHistory = new ImportHistory($id_import_history);
                        $id_supplier = $importHistory->id_supplier;
                    }
                    $id_currency = (int)$this->_module->getNewID('currency', (int)$productsupplier_xml->id_currency);
                    if (!Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'product_supplier WHERE id_supplier="' . (int)$id_supplier . '" AND id_product="' . (int)$id_product . '" AND id_product_attribute="' . (int)$id_product_attribute . '"', 0)) {
                        Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'product_supplier SET id_supplier="' . (int)$id_supplier . '", id_product="' . (int)$id_product . '", id_product_attribute="' . (int)$id_product_attribute . '", product_supplier_reference="' . pSQL((string)$productsupplier_xml->product_supplier_reference) . '", product_supplier_price_te="' . (float)$productsupplier_xml->product_supplier_price_te . '",id_currency ="' . (int)$id_currency . '"');
                        $this->stopResetImport();
                    }
                }
            }
            $readXML->deleteFileXML();
        }
        if ($readXML->imported)
            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'ets_import_history set currentindex=1 WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
    }

    public function ImportProductAttributeImages($file_xml)
    {
        $this->imported = (int)Db::getInstance()->getValue('SELECT number_import FROM ' . _DB_PREFIX_ . 'ets_import_history WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
        $readXML = new ReadXML($file_xml);
        while ($xml = $readXML->_readXML()) {
            Configuration::updateValue('PS_DATAMASTER_IMPORTING', _DB_PREFIX_ . 'product_attribute_image');
            if ($xml->product_attribute_image) {
                foreach ($xml->product_attribute_image as $productattributeimage_xml) {
                    $id_image = (int)$this->_module->getNewID('image', (int)$productattributeimage_xml->id_image);
                    $id_product_attribute = (int)$this->_module->getNewID('product_attribute', (int)$productattributeimage_xml->id_product_attribute);
                    if (!Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'product_attribute_image WHERE id_image="' . (int)$id_image . '" AND id_product_attribute="' . (int)$id_product_attribute . '"')) {
                        Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'product_attribute_image SET id_image="' . (int)$id_image . '", id_product_attribute="' . (int)$id_product_attribute . '"');
                        $this->stopResetImport();
                    }
                }
            }
            $readXML->deleteFileXML();
        }
        if ($readXML->imported)
            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'ets_import_history set currentindex=1 WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
    }

    public function importCarrierGroup()
    {
        $this->imported = (int)Db::getInstance()->getValue('SELECT number_import FROM ' . _DB_PREFIX_ . 'ets_import_history WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
        $carriers = Db::getInstance()->executeS('SELECT id_new FROM ' . _DB_PREFIX_ . 'ets_carrier_import WHERE id_import_history=' . (int)$this->context->cookie->id_import_history, true, 0);
        if ($carriers) {
            foreach ($carriers as $carrier) {
                $id_carrier = $carrier['id_new'];
                $groups = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'group', true, 0);
                if ($groups) {
                    foreach ($groups as $group) {
                        if (!Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'carrier_group WHERE id_group="' . (int)$group['id_group'] . '" AND id_carrier="' . (int)$id_carrier . '"', 0)) {
                            Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'carrier_group (id_group,id_carrier) values("' . (int)$group['id_group'] . '","' . (int)$id_carrier . '")');
                            $this->stopResetImport();
                        }
                    }
                }
            }
        }
    }

    public function importCarrierZone($file_xml)
    {
        $this->imported = (int)Db::getInstance()->getValue('SELECT number_import FROM ' . _DB_PREFIX_ . 'ets_import_history WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
        $readXML = new ReadXML($file_xml);
        while ($xml = $readXML->_readXML()) {
            Configuration::updateValue('PS_DATAMASTER_IMPORTING', _DB_PREFIX_ . 'carrier_zone');
            if ($xml->carrier_zone) {
                foreach ($xml->carrier_zone as $carrierzone_xml) {
                    $id_carrier = (int)$this->_module->getNewID('carrier', (int)$carrierzone_xml->id_carrier);
                    $id_zone = (int)$this->_module->getNewID('zone', (int)$carrierzone_xml->id_zone);
                    if (!Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'carrier_zone WHERE id_carrier="' . (int)$id_carrier . '" AND id_zone="' . (int)$id_zone . '"', 0)) {
                        Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'carrier_zone SET id_carrier="' . (int)$id_carrier . '", id_zone="' . (int)$id_zone . '"');
                        $this->stopResetImport();
                    }
                }
            }
            $readXML->deleteFileXML();
        }
        if ($readXML->imported)
            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'ets_import_history set currentindex=1 WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
    }

    public function importDataQuantity14($file_xml)
    {
        $this->imported = (int)Db::getInstance()->getValue('SELECT number_import FROM ' . _DB_PREFIX_ . 'ets_import_history WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
        $readXML = new ReadXML($file_xml);
        while ($xml = $readXML->_readXML()) {
            Configuration::updateValue('PS_DATAMASTER_IMPORTING', _DB_PREFIX_ . 'product_attribute');
            if ($xml->stock_available) {
                foreach ($xml->stock_available as $stock_available_xml) {
                    $id_product_attribute = (int)$stock_available_xml->id_product_attribute;
                    $quantity = (int)$stock_available_xml->quantity;
                    if ($id_product_attribute) {
                        $id_combination = $this->_module->getNewID('product_attribute', (int)$id_product_attribute);
                        if ($id_combination) {
                            $combination = new Combination($id_combination);
                            $combination->quantity = (int)$quantity;
                            $combination->update();
                        }
                    } else {
                        $id_product = $this->_module->getNewID('product', (int)$stock_available_xml->id_product);
                        if ($id_product) {
                            $product = new Product($id_product);
                            $product->quantity = $quantity;
                            $product->update();
                        }
                    }
                    //$this->stopResetImport();
                }
            }
            $readXML->deleteFileXML();
        }
        if ($readXML->imported)
            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'ets_import_history set currentindex=1 WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
    }

    public function stopResetImport()
    {
        $this->imported++;
        Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'ets_import_history SET number_import ="' . (int)$this->imported . '" WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
        if ($this->imported % 100 == 0) {
            die('imported=' . $this->imported);
        }
    }

    public function importAccessory($file_xml)
    {
        $readXML = new ReadXML($file_xml);
        while ($xml = $readXML->_readXML()) {
            Configuration::updateValue('PS_PRES2PRES_IMPORTING', _DB_PREFIX_ . 'accessory');
            if ($xml->accessory) {
                foreach ($xml->accessory as $accessory_xml) {
                    $id_product_1 = (int)$this->_module->getNewID('product', (int)$accessory_xml->id_product_1);
                    $id_product_2 = (int)$this->_module->getNewID('product', (int)$accessory_xml->id_product_2);
                    if (!Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'accessory WHERE id_product_1="' . (int)$id_product_1 . '" AND id_product_2="' . (int)$id_product_2 . '"', 0)) {
                        Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'accessory (id_product_1,id_product_2) VALUES ("' . (int)$id_product_1 . '","' . (int)$id_product_2 . '")');
                    }
                }
            }
            $readXML->deleteFileXML();
        }
        if ($readXML->imported)
            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'ets_import_history set currentindex=1 WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
    }

    public function importWarehouseCarrier($file_xml)
    {
        $this->imported = (int)Db::getInstance()->getValue('SELECT number_import FROM ' . _DB_PREFIX_ . 'ets_import_history WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
        $readXML = new ReadXML($file_xml);
        while ($xml = $readXML->_readXML()) {
            if ($xml->warehouse_carrier) {
                foreach ($xml->warehouse_carrier as $warehouse_carrier_xml) {
                    $id_carrier = (int)$this->_module->getNewID('carrier', (int)$warehouse_carrier_xml->id_carrier);
                    $id_warehouse = (int)$this->_module->getNewID('warehouse', (int)$warehouse_carrier_xml->id_warehouse);
                    if (!Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'warehouse_carrier WHERE id_carrier="' . (int)$id_carrier . '" AND id_warehouse="' . (int)$id_warehouse . '"', 0)) {
                        Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'warehouse_carrier SET id_carrier="' . (int)$id_carrier . '", id_warehouse="' . (int)$id_warehouse . '"');
                        $this->stopResetImport();
                    }
                }
            }
            $readXML->deleteFileXML();
        }
        if ($readXML->imported)
            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'ets_import_history set currentindex=1 WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
    }

    //---------
    public function importCartRuleCarrier($file_xml)
    {
        $this->imported = (int)Db::getInstance()->getValue('SELECT number_import FROM ' . _DB_PREFIX_ . 'ets_import_history WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
        $readXML = new ReadXML($file_xml);
        while ($xml = $readXML->_readXML()) {
            if ($xml->cart_rule_carrier) {
                foreach ($xml->cart_rule_carrier as $cart_rule_carrier_xml) {
                    $id_carrier = (int)$this->_module->getNewID('carrier', (int)$cart_rule_carrier_xml->id_carrier);
                    $id_cart_rule = (int)$this->_module->getNewID('cart_rule', (int)$cart_rule_carrier_xml->id_cart_rule);
                    if (!Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'cart_rule_carrier WHERE id_carrier="' . (int)$id_carrier . '" AND id_cart_rule="' . (int)$id_cart_rule . '"', 0)) {
                        Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'cart_rule_carrier SET id_carrier="' . (int)$id_carrier . '", id_cart_rule="' . (int)$id_cart_rule . '"');
                        $this->stopResetImport();
                    }
                }
            }
            $readXML->deleteFileXML();
        }
        if ($readXML->imported)
            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'ets_import_history set currentindex=1 WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
    }

    public function importCartRuleCombination($file_xml)
    {
        $this->imported = (int)Db::getInstance()->getValue('SELECT number_import FROM ' . _DB_PREFIX_ . 'ets_import_history WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
        $readXML = new ReadXML($file_xml);
        while ($xml = $readXML->_readXML()) {
            if ($xml->cart_rule_combination) {
                foreach ($xml->cart_rule_combination as $cart_rule_combination_xml) {
                    $id_cart_rule_1 = (int)$this->_module->getNewID('cart_rule', (int)$cart_rule_combination_xml->id_cart_rule_1);
                    $id_cart_rule_2 = (int)$this->_module->getNewID('cart_rule', (int)$cart_rule_combination_xml->id_cart_rule_2);
                    if (!Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'cart_rule_combination WHERE id_cart_rule_1="' . (int)$id_cart_rule_1 . '" AND id_cart_rule_2="' . (int)$id_cart_rule_2 . '"', 0)) {
                        Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'cart_rule_combination SET id_cart_rule_1="' . (int)$id_cart_rule_1 . '", id_cart_rule_2="' . (int)$id_cart_rule_2 . '"');
                        $this->stopResetImport();
                    }
                }
            }
            $readXML->deleteFileXML();
        }
        if ($readXML->imported)
            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'ets_import_history set currentindex=1 WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
    }

    public function importCartRuleCountry($file_xml)
    {
        $this->imported = (int)Db::getInstance()->getValue('SELECT number_import FROM ' . _DB_PREFIX_ . 'ets_import_history WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
        $readXML = new ReadXML($file_xml);
        while ($xml = $readXML->_readXML()) {
            if ($xml->cart_rule_country) {
                foreach ($xml->cart_rule_country as $cart_rule_country_xml) {
                    $id_cart_rule = (int)$this->_module->getNewID('cart_rule', (int)$cart_rule_country_xml->id_cart_rule);
                    $id_country = (int)$this->_module->getNewID('country', (int)$cart_rule_country_xml->id_country);
                    if (!Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'cart_rule_country WHERE id_cart_rule="' . (int)$id_cart_rule . '" AND id_country="' . (int)$id_country . '"', 0)) {
                        Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'cart_rule_country SET id_cart_rule="' . (int)$id_cart_rule . '", id_country="' . (int)$id_country . '"');
                        $this->stopResetImport();
                    }
                }
            }
            $readXML->deleteFileXML();
        }
        if ($readXML->imported)
            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'ets_import_history set currentindex=1 WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
    }

    public function importCartRuleGroup($file_xml)
    {
        $this->imported = (int)Db::getInstance()->getValue('SELECT number_import FROM ' . _DB_PREFIX_ . 'ets_import_history WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
        $readXML = new ReadXML($file_xml);
        while ($xml = $readXML->_readXML()) {
            if ($xml->cart_rule_group) {
                foreach ($xml->cart_rule_group as $cart_rule_group_xml) {
                    $id_cart_rule = (int)$this->_module->getNewID('cart_rule', (int)$cart_rule_group_xml->id_cart_rule);
                    $id_group = (int)$this->_module->getNewID('group', (int)$cart_rule_group_xml->id_group);
                    if (!Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'cart_rule_group WHERE id_cart_rule="' . (int)$id_cart_rule . '" AND id_group="' . (int)$id_group . '"', 0)) {
                        Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'cart_rule_group SET id_cart_rule="' . (int)$id_cart_rule . '", id_group="' . (int)$id_group . '"');
                        $this->stopResetImport();
                    }
                }
            }
            $readXML->deleteFileXML();
        }
        if ($readXML->imported)
            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'ets_import_history set currentindex=1 WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
    }

    public function importCartRuleProductRuleGroup($file_xml)
    {
        $this->imported = (int)Db::getInstance()->getValue('SELECT number_import FROM ' . _DB_PREFIX_ . 'ets_import_history WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
        $readXML = new ReadXML($file_xml);
        while ($xml = $readXML->_readXML()) {
            if ($xml->cart_rule_product_rule_group) {
                foreach ($xml->cart_rule_product_rule_group as $cart_rule_product_rule_group_xml) {
                    $id_cart_rule = (int)$this->_module->getNewID('cart_rule', (int)$cart_rule_product_rule_group_xml->id_cart_rule);
                    $quantity = (int)$this->_module->getNewID('group', (int)$cart_rule_product_rule_group_xml->quantity);
                    $id_product_rule_group = (int)$cart_rule_product_rule_group_xml->id_product_rule_group;
                    if (!(int)$this->_module->getNewID('cart_rule_product_rule_group', (int)$id_product_rule_group)) {
                        Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'cart_rule_product_rule_group SET id_cart_rule="' . (int)$id_cart_rule . '", quantity="' . (int)$quantity . '"');
                        $id_new = Db::getInstance()->Insert_ID();
                        Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'ets_cart_rule_product_rule_group_import(id_old,id_new,id_import_history) values("' . (int)$id_product_rule_group . '","' . (int)$id_new . '","' . (int)$this->context->cookie->id_import_history . '")');
                        $this->stopResetImport();
                    }
                }
            }
            $readXML->deleteFileXML();
        }
        if ($readXML->imported)
            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'ets_import_history set currentindex=1 WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
    }

    public function importCartRuleProductRule($file_xml)
    {
        $this->imported = (int)Db::getInstance()->getValue('SELECT number_import FROM ' . _DB_PREFIX_ . 'ets_import_history WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
        $readXML = new ReadXML($file_xml);
        while ($xml = $readXML->_readXML()) {
            if ($xml->cart_rule_product_rule) {
                foreach ($xml->cart_rule_product_rule as $cart_rule_product_rule_xml) {
                    $id_product_rule_group = (int)$cart_rule_product_rule_xml->id_product_rule_group;

                    $id_product_rule_group = (int)$this->_module->getNewID('cart_rule_product_rule_group', (int)$id_product_rule_group);
                    $type = (string)$cart_rule_product_rule_xml->type;
                    $id_product_rule = (int)$cart_rule_product_rule_xml->id_product_rule;
                    if (!(int)$this->_module->getNewID('cart_rule_product_rule', (int)$id_product_rule)) {
                        Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'cart_rule_product_rule SET id_product_rule_group ="' . (int)$id_product_rule_group . '", type="' . pSQL($type) . '"');
                        $id_new = Db::getInstance()->Insert_ID();
                        Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'ets_cart_rule_product_rule_import(id_old,id_new,id_import_history) values("' . (int)$id_product_rule . '","' . (int)$id_new . '","' . (int)$this->context->cookie->id_import_history . '")');
                        $this->stopResetImport();
                    }
                }
            }
            $readXML->deleteFileXML();
        }
        if ($readXML->imported)
            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'ets_import_history set currentindex=1 WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
    }

    public function importCartRuleProductRuleValue($file_xml)
    {
        $this->imported = (int)Db::getInstance()->getValue('SELECT number_import FROM ' . _DB_PREFIX_ . 'ets_import_history WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
        $readXML = new ReadXML($file_xml);
        while ($xml = $readXML->_readXML()) {
            if ($xml->cart_rule_product_rule_value) {
                foreach ($xml->cart_rule_product_rule_value as $cart_rule_product_rule_value_xml) {
                    $id_product_rule = (int)$cart_rule_product_rule_value_xml->id_product_rule;
                    $id_item = (int)$cart_rule_product_rule_value_xml->id_item;
                    if ($id_product_rule_new = (int)$this->_module->getNewID('cart_rule_product_rule', (int)$id_product_rule)) {
                        $product_rule = Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'cart_rule_product_rule WHERE id_product_rule=' . (int)$id_product_rule_new);
                        $id_item_new = 0;
                        if ($product_rule) {
                            if ($product_rule['type'] == 'products')
                                $id_item_new = (int)$this->_module->getNewID('product', (int)$id_item);
                            if ($product_rule['type'] == 'attributes')
                                $id_item_new = (int)$this->_module->getNewID('attribute', (int)$id_item);
                            if ($product_rule['type'] == 'categories')
                                $id_item_new = (int)$this->_module->getNewID('category', (int)$id_item);
                            if ($product_rule['type'] == 'manufacturers')
                                $id_item_new = (int)$this->_module->getNewID('manufacturer', (int)$id_item);
                            if ($product_rule['type'] == 'suppliers')
                                $id_item_new = (int)$this->_module->getNewID('supplier', (int)$id_item);
                            if ($id_item_new && !Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'cart_rule_product_rule_value WHERE id_product_rule="' . (int)$id_product_rule_new . '"')) {
                                Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'cart_rule_product_rule_value(id_product_rule,id_item) values("' . (int)$id_product_rule_new . '","' . (int)$id_item_new . '")');
                                $this->stopResetImport();
                            }
                        }
                    }
                }
            }
            $readXML->deleteFileXML();
        }
        if ($readXML->imported)
            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'ets_import_history set currentindex=1 WHERE id_import_history=' . (int)$this->context->cookie->id_import_history);
    }
}
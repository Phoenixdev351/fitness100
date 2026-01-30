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

class ExtraExport extends Module
{
    public $pres_version;

    public function __construct()
    {
        parent::__construct();
        if (version_compare(_PS_VERSION_, '1.7', '>='))
            $this->pres_version = 1.7;
        elseif (version_compare(_PS_VERSION_, '1.7', '<') && version_compare(_PS_VERSION_, '1.6', '>='))
            $this->pres_version = 1.6;
        elseif (version_compare(_PS_VERSION_, '1.6', '<') && version_compare(_PS_VERSION_, '1.5', '>='))
            $this->pres_version = 1.5;
        elseif (version_compare(_PS_VERSION_, '1.5', '<'))
            $this->pres_version = 1.4;
        $this->context = Context::getContext();
    }

    //customization_field
    public function exportInfo()
    {
        $data_exports = explode(',', Configuration::get('ETS_DATAMASTER_EXPORT'));
        $multishop = (int)in_array('shops', $data_exports) && Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE');
        $xml_output = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml_output .= '<entity_profile>' . "\n";
        $xml_output .= '<domain>' . (isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST']) . '</domain>' . "\n";
        $xml_output .= '<pres_version>' . _PS_VERSION_ . '</pres_version>' . "\n";
        $xml_output .= '<cookie_key>' . _COOKIE_KEY_ . '</cookie_key>' . "\n";
        $link_site = '';
        $totalItem = 0;
        $export_data = '';
        if ($multishop) {
            $shops = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'shop');
            if ($shops) {
                foreach ($shops as $shop) {
                    $shop_obj = new Shop($shop['id_shop']);
                    $base = (Configuration::get('PS_SSL_ENABLED') ? 'https://' . $shop_obj->domain_ssl : 'http://' . $shop_obj->domain);
                    $link_site .= $base . $shop_obj->getBaseURI() . ',';
                }
            }
            $countShop = count($shops);
            $xml_output .= '<countshop>' . (int)$countShop . '</countshop>' . "\n";
            $countShopGroup = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'shop_group');
            $xml_output .= '<countshopgroup>' . (int)$countShopGroup . '</countshopgroup>' . "\n";
            $countShopUrl = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'shop_url');
            $xml_output .= '<countshopurl>' . (int)$countShopUrl . '</countshopurl>' . "\n";
            $totalItem += $countShop + $countShopGroup + $countShopUrl;
            $export_data .= 'shops,';
        } else {
            if ($this->pres_version == 1.4) {
                $link_site = $this->context->link->getPageLink('index.php', true);
            } else {
                $base = (Configuration::get('PS_SSL_ENABLED') ? 'https://' . Context::getContext()->shop->domain_ssl : 'http://' . Context::getContext()->shop->domain);
                $link_site = $base . Context::getContext()->shop->getBaseURI();
            }
        }
        $xml_output .= '<link_site>' . trim($link_site, ',') . '</link_site>' . "\n";
        $countLang = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'lang');
        $xml_output .= '<countlang>' . (int)$countLang . '</countlang>' . "\n";
        $totalItem += $countLang;
        $countCurrency = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'currency');
        $xml_output .= '<countcurrency>' . (int)$countCurrency . '</countcurrency>' . "\n";
        $countZone = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'zone');
        $xml_output .= '<countzone>' . (int)$countZone . '</countzone>' . "\n";
        $countCountry = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'country');
        $xml_output .= '<countcountry>' . (int)$countCountry . '</countcountry>' . "\n";
        $countState = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'state');
        $xml_output .= '<countstate>' . (int)$countState . '</countstate>' . "\n";
        $totalItem += $countCurrency + $countZone + $countCountry + $countState;
        if (in_array('employees', $data_exports)) {
            $countEmployee = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'employee');
            $xml_output .= '<countemployee>' . (int)$countEmployee . '</countemployee>' . "\n";
            $totalItem += $countEmployee;
            $export_data .= 'employees,';
        }
        if (in_array('categories', $data_exports)) {
            $countCategory = Db::getInstance()->getValue('
            SELECT count(DISTINCT c.id_category) FROM ' . _DB_PREFIX_ . 'category c
            ' . (version_compare(_PS_VERSION_, '1.5', '>=') ? ' INNER JOIN ' . _DB_PREFIX_ . 'category_shop cs ON (c.id_category=cs.id_category)' : '') . (!$multishop && version_compare(_PS_VERSION_, '1.5', '>=') ? ' WHERE cs.id_shop="' . (int)Context::getContext()->shop->id . '"' : ''));
            $id_root_category = Db::getInstance()->getValue('SELECT id_category FROM ' . _DB_PREFIX_ . 'category WHERE is_root_category=1');
            $xml_output .= '<countcategory>' . (int)$countCategory . '</countcategory>' . "\n";
            $xml_output .= '<rootcategory>' . (int)$id_root_category . '</rootcategory>' . "\n";
            $count_category_group = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'category_group');
            $totalItem += $countCategory + $count_category_group;
            $xml_output .= '<counttotalcategory>' . ($countCategory + $count_category_group) . '</counttotalcategory>' . "\n";
            $export_data .= 'categories,';
        }
        if (in_array('customers', $data_exports)) {
            $countCustomer = Db::getInstance()->getValue('SELECT count(DISTINCT c.id_customer) FROM ' . _DB_PREFIX_ . 'customer c
            ' . (!$multishop && version_compare(_PS_VERSION_, '1.5', '>=') ? ' WHERE c.id_shop="' . (int)Context::getContext()->shop->id . '" AND c.deleted=0' : ' WHERE c.deleted=0'));
            $xml_output .= '<countcustomer>' . (int)$countCustomer . '</countcustomer>' . "\n";
            $countGroup = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'group');
            $xml_output .= '<countgroup>' . (int)$countGroup . '</countgroup>' . "\n";

            $countAddress = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'address');
            $xml_output .= '<countaddress>' . (int)$countAddress . '</countaddress>' . "\n";
            $count_category_group = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'category_group');
            $xml_output .= '<count_category_group_customer>' . (int)$count_category_group . '</count_category_group_customer>';
            $totatCustomer = $count_category_group + $countCustomer + $countGroup + $countAddress;
            $totalItem += $totatCustomer;
            $xml_output .= '<counttotalcustomer>' . (int)$totatCustomer . '</counttotalcustomer>' . "\n";
            $export_data .= 'customers,';
        }
        if (in_array('manufactures', $data_exports)) {
            $countManufacturer = Db::getInstance()->getValue('SELECT count(DISTINCT m.id_manufacturer) FROM ' . _DB_PREFIX_ . 'manufacturer m
            ' . (version_compare(_PS_VERSION_, '1.5', '>=') ? ' INNER JOIN ' . _DB_PREFIX_ . 'manufacturer_shop ms ON (m.id_manufacturer=ms.id_manufacturer)' : '') . (!$multishop && version_compare(_PS_VERSION_, '1.5', '>=') ? ' WHERE ms.id_shop="' . (int)Context::getContext()->shop->id . '"' : ''));
            $xml_output .= '<countmanufacturer>' . (int)$countManufacturer . '</countmanufacturer>' . "\n";
            $totalItem += $countManufacturer;
            $export_data .= 'manufactures,';
        }
        if (in_array('suppliers', $data_exports)) {
            $countSupplier = Db::getInstance()->getValue('SELECT count(DISTINCT s.id_supplier) FROM ' . _DB_PREFIX_ . 'supplier s
            ' . (version_compare(_PS_VERSION_, '1.5', '>=') ? ' INNER JOIN ' . _DB_PREFIX_ . 'supplier_shop ss ON (s.id_supplier=ss.id_supplier)' : '') . (!$multishop && version_compare(_PS_VERSION_, '1.5', '>=') ? ' WHERE ss.id_shop="' . (int)Context::getContext()->shop->id . '"' : ''));
            $xml_output .= '<countsupplier>' . (int)$countSupplier . '</countsupplier>' . "\n";
            $totalItem += $countSupplier;
            $export_data .= 'suppliers,';
        }
        if (in_array('carriers', $data_exports)) {
            $countCarrier = Db::getInstance()->getValue('SELECT COUNT(DISTINCT c.id_carrier) FROM ' . _DB_PREFIX_ . 'carrier c
            ' . (version_compare(_PS_VERSION_, '1.5', '>=') ? ' INNER JOIN ' . _DB_PREFIX_ . 'carrier_shop cs ON (c.id_carrier=cs.id_carrier)' : '') . (!$multishop && version_compare(_PS_VERSION_, '1.5', '>=') ? ' WHERE cs.id_shop="' . (int)Context::getContext()->shop->id . '" AND c.deleted=0' : ' WHERE c.deleted=0'));
            $xml_output .= '<countcarrier>' . (int)$countCarrier . '</countcarrier>' . "\n";
            $countCarrierZone = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'carrier_zone');
            $xml_output .= '<countcarrierzone>' . (int)$countCarrierZone . '</countcarrierzone>' . "\n";
            $countCarrierGroup = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'carrier_group');
            $countRangePrice = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'range_price');
            $xml_output .= '<countrangeprice>' . (int)$countRangePrice . '</countrangeprice>' . "\n";
            $xml_output .= '<countcarriergroup>' . (int)$countCarrierGroup . '</countcarriergroup>';
            $countRangeWeight = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'range_weight');
            $xml_output .= '<countrangeweight>' . (int)$countRangeWeight . '</countrangeweight>' . "\n";
            $countDelivery = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'delivery');
            $xml_output .= '<countdelivery>' . (int)$countDelivery . '</countdelivery>' . "\n";
            $totalCarrer = $countCarrierGroup + $countCarrierZone + $countCarrier + $countZone + $countRangePrice + $countRangeWeight + $countDelivery;
            $totalItem += $totalCarrer;
            $xml_output .= '<counttotalcarrier>' . (int)$totalCarrer . '</counttotalcarrier>' . "\n";
            $export_data .= 'carriers,';
        }
        if (in_array('cart_rules', $data_exports)) {
            $countCartRule = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'cart_rule');
            $xml_output .= '<countcartrule>' . (int)$countCartRule . '</countcartrule>' . "\n";
            $totalItem += $countCartRule;
            $export_data .= 'cart_rules,';
        }
        if (in_array('catelog_rules', $data_exports)) {
            $countSpecificPriceRule = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'specific_price_rule');
            $xml_output .= '<countspecificpriceRule>' . (int)$countSpecificPriceRule . '</countspecificpriceRule>' . "\n";
            $totalItem += $countSpecificPriceRule;
            $export_data .= 'catelog_rules,';
        }
        if (in_array('vouchers', $data_exports)) {
            $countvoucher = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'discount');
            $xml_output .= '<countvoucher>' . (int)$countvoucher . '</countvoucher>' . "\n";
            $totalItem += $countvoucher;
            $export_data .= 'vouchers,';
        }
        if (in_array('products', $data_exports)) {
            $countProduct = Db::getInstance()->getValue('SELECT count(DISTINCT p.id_product) FROM ' . _DB_PREFIX_ . 'product p
            ' . (version_compare(_PS_VERSION_, '1.5', '>=') ? ' INNER JOIN ' . _DB_PREFIX_ . 'product_shop ps ON (p.id_product=ps.id_product)' : '') . (!$multishop && version_compare(_PS_VERSION_, '1.5', '>=') ? ' WHERE ps.id_shop="' . (int)Context::getContext()->shop->id . '"' : ''));
            $countCombination = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'product_attribute');
            $countImage = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'image');
            $countAttributeGroup = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'attribute_group');
            $countAttribute = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'attribute');
            $countFeature = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'feature');
            $countFeatureValue = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'feature_value');
            $countSpecificPrice = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'specific_price');
            $countTaxRulesGroup = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'tax_rules_group');
            $countTaxRule = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'tax_rule');
            $countTag = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'tag');
            if (version_compare(_PS_VERSION_, '1.5', '>='))
                $countStockAvailable = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'stock_available');
            $countTax = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'tax');
            $xml_output .= '<countproduct>' . (int)$countProduct . '</countproduct>' . "\n";
            $xml_output .= '<countimage>' . (int)$countImage . '</countimage>' . "\n";
            $xml_output .= '<countcombination>' . (int)$countCombination . '</countcombination>' . "\n";
            $xml_output .= '<countattributegroup>' . (int)$countAttributeGroup . '</countattributegroup>' . "\n";
            $xml_output .= '<countattribute>' . (int)$countAttribute . '</countattribute>' . "\n";
            $xml_output .= '<countfeature>' . (int)$countFeature . '</countfeature>' . "\n";
            $xml_output .= '<countfeaturevalue>' . (int)$countFeatureValue . '</countfeaturevalue>' . "\n";
            $xml_output .= '<countspecificprice>' . (int)$countSpecificPrice . '</countspecificprice>' . "\n";
            $xml_output .= '<counttaxrulesgroup>' . (int)$countTaxRulesGroup . '</counttaxrulesgroup>' . "\n";
            $xml_output .= '<counttaxrule>' . (int)$countTaxRule . '</counttaxrule>' . "\n";
            $count_accessory = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'accessory');
            $xml_output .= '<counttag>' . (int)$countTag . '</counttag>' . "\n";
            if (version_compare(_PS_VERSION_, '1.5', '>='))
                $xml_output .= '<countstockavailable>' . (int)$countStockAvailable . '</countstockavailable>' . "\n";
            $xml_output .= '<counttax>' . (int)$countTax . '</counttax>' . "\n";
            $countProductCategory = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'category_product');
            $count_product_attribute_image = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'product_attribute_image');
            $count_product_tag = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'product_tag');
            $count_feature_product = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'feature_product');
            $count_product_supplier = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'product_supplier');
            $count_product_carrier = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'product_carrier');
            $countcustomization_field = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'customization_field');
            $countCustomization = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'customization');
            $countproductattributecombination = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'product_attribute_combination');
            $counttotalproduct = $countproductattributecombination + $countTag + $countCustomization + $countcustomization_field + $count_accessory + $count_product_supplier + $count_feature_product + $count_product_tag + $count_product_attribute_image + $countProduct + $countImage + $countCombination + $countAttributeGroup + $countAttribute + $countFeature + $countFeatureValue + $countSpecificPrice + $countTaxRulesGroup + $countTaxRule + $countTax + $count_product_carrier + $countProductCategory * 2;
            if (version_compare(_PS_VERSION_, '1.5', '>=')) {
                $count_warehouse = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'warehouse w ' . (version_compare(_PS_VERSION_, '1.6', '>=') ? ' INNER JOIN ' . _DB_PREFIX_ . 'warehouse_shop ws ON (w.id_warehouse=ws.id_warehouse)' : '') . (!$multishop && version_compare(_PS_VERSION_, '1.6', '>=') ? ' WHERE ws.id_shop="' . (int)Context::getContext()->shop->id . '"' : ''));
                $count_warehouse_product = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'warehouse_product_location');
                $count_warehouse_carrier = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'warehouse_carrier');
                $count_stock = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'stock');
                $counttotalproduct += $count_warehouse + $count_warehouse_product + $count_warehouse_carrier + $count_stock;
            }
            $totalItem += $counttotalproduct;
            $xml_output .= '<counttotalproduct>' . (int)$counttotalproduct . '</counttotalproduct>' . "\n";
            if (version_compare(_PS_VERSION_, '1.5', '>='))
                $totalItem += $countStockAvailable;
            $export_data .= 'products,';
        }
        if (in_array('orders', $data_exports)) {
            $countOrder = Db::getInstance()->getValue('
            SELECT count(*) FROM ' . _DB_PREFIX_ . 'orders WHERE 1' .
                (!$multishop && version_compare(_PS_VERSION_, '1.5', '>=') ? ' AND id_shop="' . (int)Context::getContext()->shop->id . '"' : '')
                . (Tools::getValue('ETS_PRES2PRES_ORDER_FROM') ? ' AND date_add >="' . pSQL(Tools::getValue('ETS_PRES2PRES_ORDER_FROM')) . '"' : '')
                . (Tools::getValue('ETS_PRES2PRES_ORDER_TO') ? ' AND date_add <="' . pSQL(Tools::getValue('ETS_PRES2PRES_ORDER_TO')) . '"' : '')
            );
            $xml_output .= '<countorder>' . (int)$countOrder . '</countorder>' . "\n";
            $countOrderState = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'order_state');
            $xml_output .= '<countorderstate>' . (int)$countOrderState . '</countorderstate>' . "\n";

            $countCart = Db::getInstance()->getValue('
            SELECT count(*) FROM ' . _DB_PREFIX_ . 'cart WHERE 1' .
                (!$multishop && version_compare(_PS_VERSION_, '1.5', '>=') ? ' AND id_shop="' . (int)Context::getContext()->shop->id . '"' : '')
                . (Tools::getValue('ETS_PRES2PRES_ORDER_FROM') ? ' AND date_add >="' . pSQL(Tools::getValue('ETS_PRES2PRES_ORDER_FROM')) . '"' : '')
                . (Tools::getValue('ETS_PRES2PRES_ORDER_TO') ? ' AND date_add <="' . pSQL(Tools::getValue('ETS_PRES2PRES_ORDER_TO')) . '"' : '')
            );
            $xml_output .= '<countcart>' . (int)$countCart . '</countcart>' . "\n";

            $countOrderDetail = Db::getInstance()->getValue('
                SELECT count(*) FROM ' . _DB_PREFIX_ . 'order_detail od
                INNER JOIN ' . _DB_PREFIX_ . 'orders o ON (od.id_order=o.id_order)
                WHERE 1' .
                (!$multishop && version_compare(_PS_VERSION_, '1.5', '>=') ? ' AND o.id_shop="' . (int)Context::getContext()->shop->id . '"' : '')
                . (Tools::getValue('ETS_PRES2PRES_ORDER_FROM') ? ' AND o.date_add >="' . pSQL(Tools::getValue('ETS_PRES2PRES_ORDER_FROM')) . '"' : '')
                . (Tools::getValue('ETS_PRES2PRES_ORDER_TO') ? ' AND o.date_add <="' . pSQL(Tools::getValue('ETS_PRES2PRES_ORDER_TO')) . '"' : '')
            );
            $xml_output .= '<countorderdetail>' . (int)$countOrderDetail . '</countorderdetail>' . "\n";

            $countOrderInvoice = Db::getInstance()->getValue('
                SELECT count(*) FROM ' . _DB_PREFIX_ . 'order_invoice oi
                INNER JOIN ' . _DB_PREFIX_ . 'orders o ON (oi.id_order=o.id_order)
                WHERE 1' .
                    (!$multishop && version_compare(_PS_VERSION_, '1.5', '>=') ? ' AND o.id_shop="' . (int)Context::getContext()->shop->id . '"' : '')
                    . (Tools::getValue('ETS_PRES2PRES_ORDER_FROM') ? ' AND o.date_add >="' . pSQL(Tools::getValue('ETS_PRES2PRES_ORDER_FROM')) . '"' : '')
                    . (Tools::getValue('ETS_PRES2PRES_ORDER_TO') ? ' AND o.date_add <="' . pSQL(Tools::getValue('ETS_PRES2PRES_ORDER_TO')) . '"' : '')
            );
            $xml_output .= '<countorderinvoice>' . (int)$countOrderInvoice . '</countorderinvoice>' . "\n";

            //countOrderDetailTax.
            $countOrderDetailTax = Db::getInstance()->getValue('
                SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'order_detail_tax odt
                LEFT JOIN ' . _DB_PREFIX_ . 'order_detail od ON (od.id_order_detail = odt.id_order_detail)
                INNER JOIN ' . _DB_PREFIX_ . 'orders o ON (o.id_order = od.id_order)
                WHERE 1' .
                (!$multishop && version_compare(_PS_VERSION_, '1.5', '>=') ? ' AND o.id_shop="' . (int)Context::getContext()->shop->id . '"' : '')
                . (Configuration::get('ETS_PRES2PRES_ORDER_FROM') ? ' AND o.date_add >="' . pSQL(Configuration::get('ETS_PRES2PRES_ORDER_FROM')) . '"' : '')
                . (Configuration::get('ETS_PRES2PRES_ORDER_TO') ? ' AND o.date_add <="' . pSQL(Configuration::get('ETS_PRES2PRES_ORDER_TO')) . '"' : '')
            );
            $xml_output .= '<countorderdetailtax>' . (int)$countOrderDetailTax . '</countorderdetailtax>' . "\n";

            //orderInvoicePayment.
            $countOrderInvoicePayment = Db::getInstance()->getValue('
                SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'order_invoice_payment oip
                LEFT JOIN ' . _DB_PREFIX_ . 'order_payment op ON (op.id_order_payment = oip.id_order_payment)
                INNER JOIN ' . _DB_PREFIX_ . 'orders o ON (oip.id_order = o.id_order)
                WHERE 1' .
                (!$multishop && version_compare(_PS_VERSION_, '1.5', '>=') ? ' AND o.id_shop="' . (int)Context::getContext()->shop->id . '"' : '')
                . (Configuration::get('ETS_PRES2PRES_ORDER_FROM') ? ' AND o.date_add >="' . pSQL(Configuration::get('ETS_PRES2PRES_ORDER_FROM')) . '"' : '')
                . (Configuration::get('ETS_PRES2PRES_ORDER_TO') ? ' AND o.date_add <="' . pSQL(Configuration::get('ETS_PRES2PRES_ORDER_TO')) . '"' : '')
            );
            $xml_output .= '<countorderinvoicepayment>' . (int)$countOrderInvoicePayment . '</countorderinvoicepayment>' . "\n";

            //orderInvoicePaymentTax.
            $countOrderInvoiceTax = Db::getInstance()->getValue('
                SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'order_invoice_tax oit
                LEFT JOIN ' . _DB_PREFIX_ . 'tax t ON (t.id_tax = oit.id_tax)
                INNER JOIN ' . _DB_PREFIX_ . 'order_invoice oi ON (oit.id_order_invoice = oi.id_order_invoice)
                INNER JOIN ' . _DB_PREFIX_ . 'orders o ON (oi.id_order = o.id_order)
                WHERE 1' .
                (!$multishop && version_compare(_PS_VERSION_, '1.5', '>=') ? ' AND o.id_shop="' . (int)Context::getContext()->shop->id . '"' : '')
                . (Configuration::get('ETS_PRES2PRES_ORDER_FROM') ? ' AND o.date_add >="' . pSQL(Configuration::get('ETS_PRES2PRES_ORDER_FROM')) . '"' : '')
                . (Configuration::get('ETS_PRES2PRES_ORDER_TO') ? ' AND o.date_add <="' . pSQL(Configuration::get('ETS_PRES2PRES_ORDER_TO')) . '"' : '')
            );
            $xml_output .= '<countorderinvoicetax>' . (int)$countOrderInvoiceTax . '</countorderinvoicetax>' . "\n";

            $countOrderSlip = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'order_slip');
            $xml_output .= '<countorderslip>' . (int)$countOrderSlip . '</countorderslip>' . "\n";
            $countOrderCarrier = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'order_carrier');
            $xml_output .= '<countordercarrier>' . (int)$countOrderCarrier . '</countordercarrier>' . "\n";
            $countOrderCartRule = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'order_cart_rule');
            $xml_output .= '<countordercartrule>' . (int)$countOrderCartRule . '</countordercartrule>' . "\n";
            $countOrderHistory = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'order_history');
            $xml_output .= '<countorderhistory>' . (int)$countOrderHistory . '</countorderhistory>' . "\n";
            $countOrderMessage = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'order_message');
            $xml_output .= '<countordermessage>' . (int)$countOrderMessage . '</countordermessage>' . "\n";
            $countOrderPayment = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'order_payment');
            $xml_output .= '<countorderpayment>' . (int)$countOrderPayment . '</countorderpayment>' . "\n";
            $countOrderReturn = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'order_return');
            $xml_output .= '<countorderreturn>' . (int)$countOrderReturn . '</countorderreturn>' . "\n";
            $countMessage = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'message');
            $xml_output .= '<countmessage>' . (int)$countMessage . '</countmessage>' . "\n";
            $totalItem += $countMessage + $countOrder + $countOrderState + $countCart + $countOrderDetail + $countOrderInvoice + $countOrderSlip + $countOrderCarrier + $countOrderCartRule + $countOrderHistory + $countOrderMessage + $countOrderPayment + $countOrderReturn;
            $export_data .= 'orders,';
        }
        if (in_array('CMS_categories', $data_exports)) {
            $countcmscategory = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'cms_category c');
            $xml_output .= '<countcmscategory>' . (int)$countcmscategory . '</countcmscategory>' . "\n";
            $export_data .= 'CMS_categories,';
            $totalItem += $countcmscategory;
        }
        if (in_array('CMS', $data_exports)) {
            $countcms = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'cms');
            $xml_output .= '<countcms>' . (int)$countcms . '</countcms>' . "\n";
            $export_data .= 'CMS,';
            $totalItem += $countcms;
        }
        if (in_array('messages', $data_exports)) {
            $countMessage = Db::getInstance()->getValue('SELECT count(*) FROM ' . _DB_PREFIX_ . 'customer_thread');
            $xml_output .= '<countmessage>' . (int)$countMessage . '</countmessage>' . "\n";
            $countContact = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'contact');
            $countcustomermessage = Db::getInstance()->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'customer_message');
            $export_data .= 'messages,';
            $totalItem += $countMessage + $countContact + $countcustomermessage;
        }
        $xml_output .= '<totalitem>' . (int)$totalItem . '</totalitem>' . "\n";
        $xml_output .= '<exporteddata>' . trim($export_data, ',') . '</exporteddata>' . "\n";
        Context::getContext()->cookie->totalItemExport = $totalItem;
        Context::getContext()->cookie->write();
        if (!Tools::getValue('submitExportReload'))
            Configuration::updateValue('ETS_DATAMASTER_EXPORTED', 0);
        Configuration::updateValue('ETS_DATAMASTER_TOTAL_EXPORT', $totalItem);
        $xml_output .= '</entity_profile>' . "\n";
        return $xml_output;
    }

    public function exportDataCustomer()
    {
        $sql = 'SELECT c.lastname,c.firstname,c.email,c.birthday,col.name as country,s.name as state,a.alias,a.company,a.vat_number,a.address1,a.address2,a.postcode,a.city,a.other,a.phone,a.phone_mobile,a.dni FROM ' . _DB_PREFIX_ . 'customer c';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'address a ON (c.id_customer =a.id_customer AND a.deleted=0)';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'country_lang col ON (col.id_country = a.id_country AND col.id_lang="' . (int)Context::getContext()->language->id . '")';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'state s ON (s.id_state =a.id_state)';
        if ($this->pres_version != 1.4)
            $sql .= ' WHERE c.id_shop="' . (int)Context::getContext()->shop->id . '"';
        return Db::getInstance()->executeS($sql);
    }

    public function exportDataProduct()
    {
        if ($this->pres_version == 1.4) {
            $sql = 'SELECT p.id_product,pl.name,cl.name as category_name, su.name as supplier_name, ma.name as manufacturer_name,pl.description_short,pl.description,p.price,p.quantity FROM ' . _DB_PREFIX_ . 'product p';
        } else {
            $sql = 'SELECT p.id_product,pl.name,cl.name as category_name, su.name as supplier_name, ma.name as manufacturer_name,pl.description_short,pl.description,ps.price,sa.quantity FROM ' . _DB_PREFIX_ . 'product p';
            $sql .= ' INNER JOIN ' . _DB_PREFIX_ . 'product_shop ps ON (p.id_product= ps.id_product)';
            $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'stock_available sa ON (sa.id_product=p.id_product AND sa.id_product_attribute=0)';
        }
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl ON (p.id_product=pl.id_product AND pl.id_lang="' . (int)Context::getContext()->language->id . '")';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'category_lang cl ON (p.id_category_default= cl.id_category AND cl.id_lang="' . (int)Context::getContext()->language->id . '")';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'supplier su ON (su.id_supplier = p.id_supplier)';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'manufacturer ma ON (ma.id_manufacturer = p.id_manufacturer)';
        if ($this->pres_version != 1.4)
            $sql .= ' WHERE ps.id_shop ="' . (int)Context::getContext()->shop->id . '"';
        $sql .= ' GROUP BY p.id_product';
        $products = Db::getInstance()->executeS($sql);
        return $products;
    }

    public function exportCategoryData()
    {
        $sql = 'SELECT c.id_category,c.id_parent,cl.name,cl.description,cl.link_rewrite FROM ' . _DB_PREFIX_ . 'category c';
        if ($this->pres_version != 1.4)
            $sql .= ' INNER JOIN ' . _DB_PREFIX_ . 'category_shop cs ON (c.id_category = cs.id_category AND cs.id_shop="' . (int)Context::getContext()->language->id . '")';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'category_lang cl ON (c.id_category= cl.id_category AND cl.id_lang="' . (int)Context::getContext()->language->id . '")';
        $sql .= ' GROUP BY c.id_category';
        return Db::getInstance()->ExecuteS($sql);
    }

    public function exportEmployeeData()
    {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'employee';
        return Db::getInstance()->ExecuteS($sql);
    }

    public function exportMessageData()
    {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'message';
        return Db::getInstance()->ExecuteS($sql);
    }

    public function exportCMSCategoryData()
    {
        $sql = 'SELECT c.id_cms_category,c.id_parent, cl.name,cl.description,link_rewrite FROM ' . _DB_PREFIX_ . 'cms_category c';
        if (version_compare(_PS_VERSION_, '1.6', '>='))
            $sql .= ' INNER JOIN ' . _DB_PREFIX_ . 'cms_category_shop cs ON (c.id_cms_category =cs.id_cms_category AND cs.id_shop="' . (int)Context::getContext()->shop->id . '")';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'cms_category_lang cl ON (c.id_cms_category=cl.id_cms_category AND cl.id_lang ="' . (int)Context::getContext()->language->id . '")';
        $sql .= ' GROUP BY c.id_cms_category';
        return Db::getInstance()->ExecuteS($sql);
    }

    public function exportCMSData()
    {
        $sql = 'SELECT c.id_cms,c.id_cms_category, cl.meta_title,ccl.name as name_cms_category,cl.meta_description,cl.meta_keywords,cl.content,cl.link_rewrite FROM ' . _DB_PREFIX_ . 'cms c';
        if ($this->pres_version != 1.4)
            $sql .= ' INNER JOIN ' . _DB_PREFIX_ . 'cms_shop cs ON (c.id_cms = cs.id_cms AND cs.id_shop="' . (int)Context::getContext()->shop->id . '")';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'cms_lang cl ON (c.id_cms=cl.id_cms AND cl.id_lang ="' . (int)Context::getContext()->language->id . '")';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'cms_category_lang ccl ON (c.id_cms_category = ccl.id_cms_category AND ccl.id_lang="' . (int)Context::getContext()->language->id . '")';
        $sql .= ' GROUP BY c.id_cms';
        return Db::getInstance()->ExecuteS($sql);
    }

    public function exportCarrierData()
    {
        $sql = 'SELECT c.id_carrier,c.name,cl.delay FROM ' . _DB_PREFIX_ . 'carrier c';
        if ($this->pres_version != 1.4)
            $sql .= ' INNER JOIN ' . _DB_PREFIX_ . 'carrier_shop cs ON (c.id_carrier= cs.id_carrier AND cs.id_shop="' . (int)Context::getContext()->shop->id . '")';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'carrier_lang cl ON (c.id_carrier = cl.id_carrier AND cl.id_lang ="' . (int)Context::getContext()->language->id . '")';
        $sql .= ' GROUP BY c.id_carrier';
        return Db::getInstance()->ExecuteS($sql);
    }

    public function exportDataProductAttribute()
    {
        if ($this->pres_version == 1.4) {
            $sql = 'SELECT p.id_product,pa.id_product_attribute,pl.name,cl.name as category_name, su.name as supplier_name, ma.name as manufacturer_name,pl.description_short,pl.description,p.price,p.quantity FROM ' . _DB_PREFIX_ . 'product p';
            $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'product_attribute pa ON (pa.id_product= p.id_product)';
        } else {
            $sql = 'SELECT p.id_product,pa.id_product_attribute,pl.name,cl.name as category_name, su.name as supplier_name, ma.name as manufacturer_name,pl.description_short,pl.description,ps.price,sa.quantity FROM ' . _DB_PREFIX_ . 'product p';
            $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'product_shop ps ON (p.id_product= ps.id_product)';
            $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'product_attribute pa ON (pa.id_product= p.id_product)';
            $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'stock_available sa ON (sa.id_product=p.id_product AND pa.id_product_attribute=sa.id_product_attribute)';
        }
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl ON (p.id_product=pl.id_product AND pl.id_lang="' . (int)Context::getContext()->language->id . '")';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'category_lang cl ON (p.id_category_default= cl.id_category AND cl.id_lang="' . (int)Context::getContext()->language->id . '")';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'supplier su ON (su.id_supplier = p.id_supplier)';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'manufacturer ma ON (ma.id_manufacturer = p.id_manufacturer)';
        if ($this->pres_version != 1.4)
            $sql .= ' WHERE ps.id_shop ="' . (int)Context::getContext()->shop->id . '"';
        $sql .= ' GROUP BY p.id_product,pa.id_product_attribute';
        $products = Db::getInstance()->executeS($sql);
        if ($products) {
            foreach ($products as &$product) {
                if ($product['id_product_attribute'])
                    $product['name'] = $product['name'] . ' - ' . trim($this->getAttributeName($product['id_product_attribute']), ', ');
            }
        }
        return $products;
    }

    public function getAttributeName($id_product_attribute)
    {
        $sql = 'SELECT pac.id_product_attribute,agl.public_name,al.name as attribute_name FROM ' . _DB_PREFIX_ . 'product_attribute_combination pac';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'attribute a ON (pac.id_attribute=a.id_attribute)';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'attribute_lang al ON (a.id_attribute=al.id_attribute AND al.id_lang ="' . (int)Context::getContext()->language->id . '")';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'attribute_group_lang agl ON (agl.id_attribute_group= a.id_attribute_group AND agl.id_lang="' . (int)Context::getContext()->language->id . '")';
        $sql .= ' WHERE pac.id_product_attribute ="' . (int)$id_product_attribute . '"';
        $attributes = Db::getInstance()->executeS($sql);
        if ($attributes) {
            $product_attribute_name = '';
            foreach ($attributes as $attribute) {
                $product_attribute_name .= $attribute['public_name'] . ': ' . $attribute['attribute_name'] . ', ';
            }
            return $product_attribute_name;
        }
        return '';
    }

    public function exportDataOrder()
    {
        if ($this->pres_version != 1.4)
            $sql = 'SELECT o.id_order,o.reference,o.total_paid_tax_incl,o.total_paid_tax_excl,c.email,CONCAT(c.firstname," ", c.lastname) as customer_name,osl.name as status FROM ' . _DB_PREFIX_ . 'orders o';
        else
            $sql = 'SELECT o.id_order,o.reference,o.total_paid,c.email,CONCAT(c.firstname," ", c.lastname) as customer_name,osl.name as status FROM ' . _DB_PREFIX_ . 'orders o';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'customer c ON (o.id_customer = c.id_customer)';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'order_state os ON (os.id_order_state = o.current_state)';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'order_state_lang osl ON (osl.id_order_state =os.id_order_state AND osl.id_lang="' . (int)Context::getContext()->language->id . '")';
        if ($this->pres_version != 1.4)
            $sql .= ' WHERE o.id_shop="' . (int)Context::getContext()->shop->id . '"';
        $sql .= ' GROUP BY o.id_order';
        return Db::getInstance()->executeS($sql);
    }

    public function exportDataOrderDetail()
    {
        if ($this->pres_version == 1.4)
            $sql = 'SELECT o.id_order,od.id_order_detail,o.reference,o.total_paid,c.email,CONCAT(c.firstname," ", c.lastname) as customer_name,osl.name as status,od.product_id,od.product_attribute_id, od.product_name,od.product_reference,od.product_price,od.product_quantity FROM ' . _DB_PREFIX_ . 'orders o';
        else
            $sql = 'SELECT o.id_order,od.id_order_detail,o.reference,o.total_paid_tax_incl,o.total_paid_tax_excl,c.email,CONCAT(c.firstname," ", c.lastname) as customer_name,osl.name as status,od.product_id,od.product_attribute_id, od.product_name,od.product_reference,od.product_price,od.product_quantity,od.total_price_tax_excl,od.total_price_tax_incl FROM ' . _DB_PREFIX_ . 'orders o';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'order_detail od ON (o.id_order =od.id_order)';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'customer c ON (o.id_customer = c.id_customer)';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'order_state os ON (os.id_order_state = o.current_state)';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'order_state_lang osl ON (osl.id_order_state =os.id_order_state AND osl.id_lang="' . (int)Context::getContext()->language->id . '")';
        if ($this->pres_version != 1.4)
            $sql .= ' WHERE o.id_shop ="' . (int)Context::getContext()->shop->id . '"';
        $sql .= ' GROUP BY o.id_order,od.id_order_detail';
        return Db::getInstance()->executeS($sql);
    }

    public function exportDataCarrierRangePrice()
    {
        $sql = 'SELECT c.id_carrier,d.id_delivery,c.name,cl.delay,z.name as zone_name,rp.delimiter1 as price_delimiter1,rp.delimiter2 as price_delimiter2,rw.delimiter1 as weight_delimiter1,rw.delimiter2 as weight_delimiter2,d.price FROM ' . _DB_PREFIX_ . 'carrier c';
        if ($this->pres_version != 1.4)
            $sql .= ' INNER JOIN ' . _DB_PREFIX_ . 'carrier_shop cs ON (c.id_carrier=cs.id_carrier AND cs.id_shop="' . (int)Context::getContext()->shop->id . '")';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'carrier_lang cl ON (c.id_carrier=cl.id_carrier AND cl.id_lang="' . (int)Context::getContext()->language->id . '")';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'delivery d ON (d.id_carrier=c.id_carrier)';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'zone z ON (z.id_zone = d.id_zone)';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'range_price rp ON (rp.id_range_price =d.id_range_price)';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'range_weight rw ON (rw.id_range_weight=d.id_range_weight)';
        $sql .= ' GROUP BY c.id_carrier,d.id_delivery';
        return Db::getInstance()->executeS($sql);
    }

    public function exportDataManufacturer()
    {
        $sql = 'SELECT m.id_manufacturer,a.id_address,m.name,m.active,ml.short_description,ml.description,a.address1,a.address2,a.postcode,a.city,a.phone,a.phone_mobile,a.dni FROM ' . _DB_PREFIX_ . 'manufacturer m';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'manufacturer_lang ml ON (m.id_manufacturer=ml.id_manufacturer AND ml.id_lang="' . (int)Context::getContext()->language->id . '")';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'address a ON (a.id_manufacturer= m.id_manufacturer)';
        $sql .= ' GROUP BY m.id_manufacturer,a.id_address';
        return Db::getInstance()->executeS($sql);
    }

    public function exportDataSupplier()
    {
        $sql = 'SELECT s.id_supplier,a.id_address,s.name,s.active,sl.description,a.address1,a.address2,a.postcode,a.city,a.phone,a.phone_mobile,a.dni FROM ' . _DB_PREFIX_ . 'supplier s';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'supplier_lang sl ON (s.id_supplier=sl.id_supplier AND sl.id_lang="' . (int)Context::getContext()->language->id . '")';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'address a ON (a.id_supplier = s.id_supplier)';
        $sql .= ' GROUP BY s.id_supplier, a.id_address';
        return Db::getInstance()->executeS($sql);
    }
}
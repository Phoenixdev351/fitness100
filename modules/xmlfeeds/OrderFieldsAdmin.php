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

if (!defined('_PS_VERSION_')) {
    exit;
}

class OrderFieldsAdmin extends Xmlfeeds
{
    public function getFieldSettings($page, $moduleImgPath)
    {
        $r = Db::getInstance()->ExecuteS('
			SELECT `name`, `status`, `title_xml`, `table`
			FROM '._DB_PREFIX_.'blmod_xml_fields
			WHERE category = "'.(int)$page.'"
		');

        $tags = array();
        $branchNamesKey = array();

        foreach ($r as $k) {
            $tags[$k['name'].'+'.$k['table']] = isset($k['title_xml']) ? $k['title_xml'] : false;
            $tags[$k['name'].'+'.$k['table'].'+status'] = isset($k['status']) ? $k['status'] : false;
        }

        if (!empty($tags)) {
            $this->tags_info = $tags;
        }

        $branchNames = Db::getInstance()->ExecuteS('
			SELECT `name`, `value`, `category`
			FROM '._DB_PREFIX_.'blmod_xml_block
			WHERE category = "'.(int)$page.'"
		');

        foreach ($branchNames as $bl) {
            $branchNamesKey[$bl['name']] = isset($bl['value']) ? $bl['value'] : false;
        }

        $this->smarty->assign([
            'branchNamesKey' => $branchNamesKey,
            'moduleImgPath' => $moduleImgPath,
        ]);

        $html = $this->displaySmarty('views/templates/admin/element/orderBlockSettings.tpl');

        $html .= $this->printBlock(
            'Order basic information',
            array(
                array('name' => 'id_order', 'title' => 'id_order', 'table' => 'orders'),
                array('name' => 'reference', 'title' => 'reference', 'table' => 'orders'),
                array('name' => 'invoice_number', 'title' => 'invoice_number', 'table' => 'orders'),
                array('name' => 'invoice_date', 'title' => 'invoice_date', 'table' => 'orders'),
                array('name' => 'gift', 'title' => 'is_gift', 'table' => 'orders'),
                array('name' => 'gift_message', 'title' => 'gift_message', 'table' => 'orders'),
                array('name' => 'current_state', 'title' => 'id_status', 'table' => 'orders'),
                array('name' => 'name', 'title' => 'status_name', 'table' => 'order_state_lang'),
                array('name' => 'payment', 'title' => 'payment', 'table' => 'orders'),
                array('name' => 'date_add', 'title' => 'date_add', 'table' => 'orders'),
                array('name' => 'date_upd', 'title' => 'date_upd', 'table' => 'orders'),
                array('name' => 'currency', 'title' => 'currency', 'table' => 'bl_extra'),
                array('name' => 'customer_message', 'title' => 'customer_messages', 'table' => 'bl_extra'),
                array('name' => 'employee_message', 'title' => 'employee_messages', 'table' => 'bl_extra'),
                array('name' => 'total_products', 'title' => 'total_products', 'table' => 'bl_extra'),
            )
        );

        $html .= $this->printBlock(
            'Prices, Tax',
            array(
                array('name' => 'total_paid', 'title' => 'total_paid', 'table' => 'orders'),
                array('name' => 'total_paid_tax_incl', 'title' => 'total_paid_tax_incl', 'table' => 'orders'),
                array('name' => 'total_paid_tax_excl', 'title' => 'total_paid_tax_excl', 'table' => 'orders'),
                array('name' => 'total_wrapping', 'title' => 'total_wrapping', 'table' => 'orders'),
                array('name' => 'total_discounts', 'title' => 'total_discounts', 'table' => 'orders'),
                array('name' => 'total_products', 'title' => 'total_products', 'table' => 'orders'),
                array('name' => 'tax_total_amount', 'title' => 'tax_total_amount', 'table' => 'bl_extra'),
            )
        );

        $html .= $this->printBlock(
            'Shipping, delivery',
            array(
                array('name' => 'shipping_number', 'title' => 'shipping_number', 'table' => 'orders'),
                array('name' => 'delivery_number', 'title' => 'delivery_number', 'table' => 'orders'),
                array('name' => 'delivery_date', 'title' => 'delivery_date', 'table' => 'orders'),
                array('name' => 'id_carrier', 'title' => 'id_carrier', 'table' => 'orders'),
                array('name' => 'name', 'title' => 'carrier_name', 'table' => 'carrier'),
                array('name' => 'total_shipping', 'title' => 'total_shipping', 'table' => 'orders'),
                array('name' => 'total_shipping_tax_incl', 'title' => 'total_shipping_tax_incl', 'table' => 'orders'),
                array('name' => 'total_shipping_tax_excl', 'title' => 'total_shipping_tax_excl', 'table' => 'orders'),
                array('name' => 'carrier_tax_rate', 'title' => 'carrier_tax_rate', 'table' => 'orders'),
                array('name' => 'invoice_address', 'title' => 'invoice_address', 'table' => 'bl_extra'),
                array('name' => 'delivery_address', 'title' => 'delivery_address', 'table' => 'bl_extra'),
            )
        );

        $html .= $this->printBlock(
            'Customer',
            array(
                array('name' => 'id_customer', 'title' => 'id_customer', 'table' => 'orders'),
                array('name' => 'id_default_group', 'title' => 'id_group', 'table' => 'customer'),
                array('name' => 'firstname', 'title' => 'firstname', 'table' => 'customer'),
                array('name' => 'lastname', 'title' => 'lastname', 'table' => 'customer'),
                array('name' => 'email', 'title' => 'email', 'table' => 'customer'),
                array('name' => 'phone', 'title' => 'phone', 'table' => 'address'),
                array('name' => 'birthday', 'title' => 'birthday', 'table' => 'customer'),
                array('name' => 'address', 'title' => 'full_address', 'table' => 'bl_extra'),
                array('name' => 'country', 'title' => 'country', 'table' => 'bl_extra'),
                array('name' => 'city', 'title' => 'city', 'table' => 'bl_extra'),
                array('name' => 'postcode', 'title' => 'postal_code', 'table' => 'bl_extra'),
            )
        );

        $html .= $this->printBlock(
            'Products',
            array(
                array('name' => 'product_id', 'title' => 'product_id', 'table' => 'order_detail'),
                array('name' => 'product_attribute_id', 'title' => 'product_attribute_id', 'table' => 'order_detail'),
                array('name' => 'product_quantity', 'title' => 'product_quantity', 'table' => 'order_detail'),
                array('name' => 'product_name', 'title' => 'product_name', 'table' => 'order_detail'),
                array('name' => 'total_price_tax_incl', 'title' => 'products_price_tax_incl', 'table' => 'order_detail'),
                array('name' => 'total_price_tax_excl', 'title' => 'products_price_tax_excl', 'table' => 'order_detail'),
                array('name' => 'unit_price_tax_incl', 'title' => 'unit_price_tax_incl', 'table' => 'order_detail'),
                array('name' => 'unit_price_tax_excl', 'title' => 'unit_price_tax_excl', 'table' => 'order_detail'),
                array('name' => 'rate', 'title' => 'tax_rate', 'table' => 'tax'),
                array('name' => 'product_ean13', 'title' => 'product_ean13', 'table' => 'order_detail'),
                array('name' => 'product_reference', 'title' => 'product_reference', 'table' => 'order_detail'),
                array('name' => 'product_upc', 'title' => 'product_upc', 'table' => 'order_detail'),
                array('name' => 'product_isbn', 'title' => 'product_isbn', 'table' => 'order_detail'),
                array('name' => 'product_supplier_reference', 'title' => 'supplier_reference', 'table' => 'order_detail'),
                array('name' => 'id_supplier', 'title' => 'id_supplier', 'table' => 'product'),
                array('name' => 'id_warehouse', 'title' => 'warehouse_id', 'table' => 'order_detail'),
            )
        );

        return $html;
    }
}

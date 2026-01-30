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

class OrderXmlApi
{
    protected $feedId = 0;
    protected $settings = array();
    protected $fieldsName = array();
    protected $fieldsNameAdditionTable = array();
    protected $feedLangId = 1;
    protected $countryList = array();
    protected $stateList = array();
    protected $branchNames = array();
    protected $prefixStart = '';
    protected $prefixEnd = '';
    protected $currency = false;
    protected $totalProducts = 0;

    public function getFeed($settings)
    {
        $this->settings = $settings;
        $this->feedLangId = (int)Configuration::get('PS_LANG_DEFAULT');
        $this->feedId = (int)$settings['id'];
        $this->currency = Currency::getCurrency(!empty($settings['currency_id']) ? $settings['currency_id'] : Configuration::get('PS_CURRENCY_DEFAULT'));
        $this->branchNames = $this->getBranchNames();

        $this->loadAddressData();
        $orders = $this->getOrders();

        return $this->generateXml($orders);
    }

    protected function generateXml($orders)
    {
        $xml = $this->getDeepTagName($this->branchNames['orders-branch-name']);

        if (!empty($this->settings['extra_feed_row'])) {
            $xml .= $this->settings['extra_feed_row'];
        }

        if (!empty($this->settings['cdata_status'])) {
            $this->prefixStart = '<![CDATA[';
            $this->prefixEnd = ']]>';
        }

        foreach ($orders as $order) {
            $xmlOrder = $this->getDeepTagName($this->branchNames['order-branch-name']);
            $productsBranch = $this->generateXmlAddProducts($order['order_id']);

            foreach ($order as $id => $val) {
                if (empty($this->fieldsName[$id])) {
                    continue;
                }

                if ($this->isPriceField($id)) {
                    $val = $this->getPriceFormat($val);
                }

                $xmlOrder .= $this->getDeepTagName($this->fieldsName[$id]).$this->prefixStart.$val.$this->prefixEnd.$this->getDeepTagName($this->fieldsName[$id], true);
            }

            foreach ($this->fieldsName as $fieldId => $fieldName) {
                if (strpos($fieldId, 'bl_extra_') !== 0) {
                    continue;
                }

                $method = 'extraField'.str_replace(' ', '', ucwords(str_replace(array('bl_extra_', '_'), array('', ' '), $fieldId)));
                $xmlOrder .= $this->getDeepTagName($fieldName).$this->$method($order).$this->getDeepTagName($fieldName, true);
            }

            $xml .= $this->replaceXmlTree($xmlOrder);

            $xml .= $productsBranch;
            $xml .= $this->getDeepTagName($this->branchNames['order-branch-name'], true);
        }

        $xml .= $this->getDeepTagName($this->branchNames['orders-branch-name'], true);

        return $xml;
    }

    protected function generateXmlAddProducts($orderId)
    {
        $xml = '';
        $this->totalProducts = 0;

        if (empty($this->fieldsNameAdditionTable['od'])) {
            return $xml;
        }

        $products = $this->getProducts($orderId);

        if (!empty($products)) {
            $this->totalProducts = count($products);
            $xml = $this->getDeepTagName($this->branchNames['products-branch-name']);

            foreach ($products as $product) {
                $xml .= $this->getDeepTagName($this->branchNames['product-branch-name']);
                $xmlOrder = '';

                foreach ($product as $id => $val) {
                    if ($this->isPriceField($id)) {
                        $val = $this->getPriceFormat($val);
                    }

                    if ($this->isNumberField($id)) {
                        $val = $this->getNumberFormat($val);
                    }

                    $xmlOrder .= $this->getDeepTagName($this->fieldsName[$id]).$this->prefixStart.$val.$this->prefixEnd.$this->getDeepTagName($this->fieldsName[$id], true);
                }

                $xml .= $this->replaceXmlTree($xmlOrder);
                $xml .= $this->getDeepTagName($this->branchNames['product-branch-name'], true);
            }

            $xml .= $this->getDeepTagName($this->branchNames['products-branch-name'], true);
        }

        return $xml;
    }

    protected function getOrders()
    {
        $field = $this->getFields();

        $sql = 'SELECT DISTINCT(o.id_order) AS order_id, ad.address1, ad.address2, ad.postcode, ad.city, ad.id_country, ad.id_state,
            ad.company, ad.phone, ad.phone_mobile, ad.vat_number, ad.firstname AS firstname_d, ad.lastname AS lastname_d,
            ai.address1 AS address1_i, ai.address2 AS address2_i, ai.postcode AS postcode_i, ai.city AS city_i, 
            ai.id_country AS id_country_i, ai.id_state AS id_state_i,
            ai.company AS company_i, ai.phone AS phone_i, ai.phone_mobile AS phone_mobile_i, ai.vat_number AS vat_number_i, 
            ai.firstname AS firstname_i, ai.lastname AS lastname_i, o.total_paid_tax_excl AS total_paid_t_e,
            o.total_paid_tax_incl AS total_paid_t_i'.pSQL($field).'        
            FROM '._DB_PREFIX_.'orders o
            LEFT JOIN '._DB_PREFIX_.'order_state_lang sl ON
            (sl.id_order_state = o.current_state AND sl.id_lang = "'.(int)$this->feedLangId.'")
            LEFT JOIN '._DB_PREFIX_.'customer c ON
            c.`id_customer` = o.`id_customer`
            LEFT JOIN '._DB_PREFIX_.'address ad ON
            ad.id_address = o.id_address_delivery
            LEFT JOIN '._DB_PREFIX_.'address ai ON
            ai.id_address = o.id_address_invoice
            LEFT JOIN '._DB_PREFIX_.'carrier cr ON
            cr.id_carrier = o.id_carrier'.$this->getOrdersFilter();

        return Db::getInstance()->ExecuteS($sql);
    }

    protected function getOrdersFilter()
    {
        $where = array();

        if (!empty($this->settings['order_state_status']) && !empty($this->settings['order_state'])) {
            $where[] = 'sl.id_order_state IN ('.pSQL($this->settings['order_state']).')';
        }

        if (!empty($this->settings['order_payments_status']) && !empty($this->settings['order_payment'])) {
            $where[] = 'o.module IN ("'.str_replace(',', '","', pSQL($this->settings['order_payment'])).'")';
        }

        if (!empty($this->settings['filter_date_type'])) {
            $dateToday = pSQL(date('Y-m-d').' 00:00:00');

            $filterByDate = array(
                OrderSettings::FILTER_DATE_NONE => '',
                OrderSettings::FILTER_DATE_TODAY => 'o.date_add >= "'.pSQL($dateToday).'"',
                OrderSettings::FILTER_DATE_YESTERDAY => 'o.date_add >= "'.pSQL(date('Y-m-d', strtotime('-1 day', strtotime($dateToday)))).' 00:00:00" AND o.date_add < "'.pSQL($dateToday).'"',
                OrderSettings::FILTER_DATE_THIS_WEEK => 'o.date_add >= "'.pSQL(date('Y-m-d', strtotime('this week', time()))).' 00:00:00"',
                OrderSettings::FILTER_DATE_THIS_MONTH => 'o.date_add >= "'.pSQL(date('Y-m-01')).' 00:00:00"',
                OrderSettings::FILTER_DATE_THIS_YEAR => 'o.date_add >= "'.pSQL(date('Y-01-01')).' 00:00:00"',
                OrderSettings::FILTER_DATE_CUSTOM_DAYS => 'o.date_add >= "'.pSQL(date('Y-m-d', strtotime('-'.(int)$this->settings['filter_custom_days'].' day', strtotime($dateToday)))).' 00:00:00"',
                OrderSettings::FILTER_DATE_DATE_RANGE => 'o.date_add >= "'.pSQL($this->settings['filter_date_from']).' 00:00:00" AND o.date_add <= "'.pSQL($this->settings['filter_date_to']).' 23:59:59" ',
            );

            $where[] = $filterByDate[$this->settings['filter_date_type']];
        }

        return !empty($where) ? ' WHERE '.implode(' AND ', $where) : '';
    }

    protected function getProducts($orderId)
    {
        return Db::getInstance()->ExecuteS('
            SELECT '.pSQL(trim($this->fieldsNameAdditionTable['od'], ',')).'
            FROM '._DB_PREFIX_.'order_detail od
            LEFT JOIN '._DB_PREFIX_.'product p ON
            p.`id_product` = od.`product_id`
            LEFT JOIN '._DB_PREFIX_.'order_detail_tax dt ON
            dt.id_order_detail = od.id_order_detail
            LEFT JOIN '._DB_PREFIX_.'tax t ON
            t.id_tax = dt.id_tax
            WHERE od.id_order = "'.(int)$orderId.'"
            ORDER BY od.id_order_detail ASC');
    }

    protected function getFields()
    {
        $fields = Db::getInstance()->ExecuteS('
            SELECT `name`, `status`, `title_xml`, `table`
            FROM '._DB_PREFIX_.'blmod_xml_fields
            WHERE category = "'.(int)$this->feedId.'" AND status = "1" 
            ORDER BY `table` ASC
        ');

        $field = '';
        $tableMap = array(
            'orders' => 'o',
            'order_state_lang' => 'sl',
            'customer' => 'c',
            'order_detail' => 'od',
            'address' => 'ad',
            'product' => 'p',
            'tax' => 't',
            'carrier' => 'cr',
        );

        if (!empty($fields)) {
            foreach ($fields as $f) {
                $this->fieldsName[$f['table'].'_'.$f['name']] = $f['title_xml'];

                if ($f['table'] == 'bl_extra') {
                    continue;
                }

                $table = $tableMap[$f['table']];

                if ($f['table'] == 'order_detail' || $f['table'] == 'product' || $f['table'] == 'tax') {
                    if (empty($this->fieldsNameAdditionTable['od'])) {
                        $this->fieldsNameAdditionTable['od'] = '';
                    }

                    $this->fieldsNameAdditionTable['od'] .= $table.'.`'.$f['name'].'` AS '.$f['table'].'_'.$f['name'].',';
                    continue;
                }

                $field .= $table.'.`'.$f['name'].'` AS '.$f['table'].'_'.$f['name'].',';
            }

            if (empty($field)) {
                return '';
            }

            $field = ','.trim($field, ',');
        }

        return $field;
    }

    protected function extraFieldAddress($order, $prefix = '')
    {
        $final = array();
        $final[] = $order['address1'.$prefix];
        $final[] = $order['address2'.$prefix];
        $final[] = $order['postcode'.$prefix];
        $final[] = $order['city'.$prefix];
        $final[] = !empty($this->stateList[$order['id_country'.$prefix]][$order['id_state'.$prefix]]) ? $this->stateList[$order['id_country'.$prefix]][$order['id_state'.$prefix]] : '';
        $final[] = $order['postcode'.$prefix];

        return implode(' ', array_filter($final)).(!empty($this->countryList[$order['id_country'.$prefix]]) ? ', '.$this->countryList[$order['id_country'.$prefix]] : '');
    }

    protected function extraFieldDeliveryAddress($order)
    {
        $country = !empty($this->countryList[$order['id_country']]) ? $this->countryList[$order['id_country']] : '';

        return '<firstname>'.$order['firstname_d'].'</firstname>
            <lastname>'.$order['lastname_d'].'</lastname>
            <company>'.$order['company'].'</company>
            <vat_number>'.$order['vat_number'].'</vat_number>
            <address_line1>'.$order['address1'].'</address_line1>
            <address_line2>'.$order['address2'].'</address_line2>
            <post_code>'.$order['postcode'].'</post_code>
            <city>'.$order['city'].'</city>
            <country>'.$country.'</country>
            <phone>'.$order['phone'].'</phone>
            <phone_mobile>'.$order['phone_mobile'].'</phone_mobile>';
    }

    protected function extraFieldInvoiceAddress($order)
    {
        $country = !empty($this->countryList[$order['id_country_i']]) ? $this->countryList[$order['id_country_i']] : '';

        return '<firstname>'.$order['firstname_i'].'</firstname>
            <lastname>'.$order['lastname_i'].'</lastname>
            <company>'.$order['company_i'].'</company>
            <vat_number>'.$order['vat_number_i'].'</vat_number>
            <address_line1>'.$order['address1_i'].'</address_line1>
            <address_line2>'.$order['address2_i'].'</address_line2>
            <post_code>'.$order['postcode_i'].'</post_code>
            <city>'.$order['city_i'].'</city>
            <country>'.$country.'</country>
            <phone>'.$order['phone_i'].'</phone>
            <phone_mobile>'.$order['phone_mobile_i'].'</phone_mobile>';
    }

    protected function extraFieldCountry($order)
    {
        return (!empty($this->countryList[$order['id_country']]) ? $this->countryList[$order['id_country']] : '');
    }

    protected function extraFieldCity($order)
    {
        return !empty($order['city']) ? $order['city'] : '';
    }

    protected function extraFieldPostcode($order)
    {
        return !empty($order['postcode']) ?  $order['postcode'] : '';
    }

    protected function extraFieldCurrency($order)
    {
        return !empty($this->currency['iso_code']) ? $this->currency['iso_code'] : '';
    }

    protected function extraFieldTaxTotalAmount($order)
    {
        return $this->getPriceFormat($order['total_paid_t_i'] - $order['total_paid_t_e']);
    }

    protected function extraFieldCustomerMessage($order)
    {
        return $this->getMessagesBranch($order['order_id']);
    }

    protected function extraFieldEmployeeMessage($order)
    {
        return $this->getMessagesBranch($order['order_id'], false);
    }

    protected function extraFieldTotalProducts($order)
    {
        if (!empty($this->totalProducts)) {
            return $this->totalProducts;
        }

        return Db::getInstance()->getValue('SELECT COUNt(od.id_order_detail)
            FROM '._DB_PREFIX_.'order_detail od
            WHERE od.id_order = '.(int)$order['order_id']);
    }

    protected function loadAddressData()
    {
        $countries = Db::getInstance()->ExecuteS('
            SELECT id_country, `name`
            FROM `'._DB_PREFIX_.'country_lang`
            WHERE `id_lang` = '.(int)$this->feedLangId);

        foreach ($countries as $c) {
            $this->countryList[$c['id_country']] = $c['name'];
        }

        $states = Db::getInstance()->ExecuteS('
            SELECT id_state, id_country, `name`
            FROM `'._DB_PREFIX_.'state`');

        foreach ($states as $s) {
            $this->stateList[$s['id_country']][$s['id_state']] = $s['name'];
        }
    }

    protected function getMessagesBranch($orderId, $isCustomerMessages = true)
    {
        $xml = '';

        $where = $isCustomerMessages ? '=' : '>';

        $messages = Db::getInstance()->ExecuteS('SELECT ct.id_customer_thread, cm.`message`, cm.`date_add`, cm.`private`
            FROM '._DB_PREFIX_.'customer_thread ct
            LEFT JOIN '._DB_PREFIX_.'customer_message cm ON
            cm.id_customer_thread = ct.id_customer_thread
            WHERE ct.id_order = '.(int)$orderId.' AND cm.`id_employee` '.pSQL($where).' 0
            ORDER BY cm.id_customer_message DESC');

        if (!empty($messages)) {
            foreach ($messages as $m) {
                $xml .= '<message>';
                $xml .= '<text>'.$this->prefixStart.$m['message'].$this->prefixEnd.'</text>';
                $xml .= '<date_add>'.$this->prefixStart.$m['date_add'].$this->prefixEnd.'</date_add>';
                $xml .= '<is_private>'.$this->prefixStart.$m['private'].$this->prefixEnd.'</is_private>';
                $xml .= '</message>';
            }
        }

        return $xml;
    }

    protected function getBranchNames()
    {
        $branchNamesKey = array();

        $branchNames = Db::getInstance()->ExecuteS('
			SELECT `name`, `value`, `category`
			FROM '._DB_PREFIX_.'blmod_xml_block
			WHERE category = "'.(int)$this->feedId.'"
		');

        foreach ($branchNames as $bl) {
            $branchNamesKey[$bl['name']] = isset($bl['value']) ? $bl['value'] : $bl['name'];
        }

        return $branchNamesKey;
    }

    protected function replaceXmlTree($xml)
    {
        preg_match_all("'<sBLMOD>(.*?)</sBLMOD>'si", $xml, $categories);

        $levels = array();

        if (empty($categories[1])) {
            return $xml;
        }

        foreach ($categories[1] as $k => $c) {
            preg_match("'<nBLMOD>(.*?)</nBLMOD>'si", $c, $name);
            $names = explode('_lBLMOD_', $name[1]);

            preg_match("'<vBLMOD>(.*?)</vBLMOD>'si", $c, $value);

            $levels[$names[0]][] = [
                'full' => $categories[0][$k],
                'name' => $names[1],
                'value' => $value[1],
            ];
        }

        foreach ($levels as $branchName => $branch) {
            $xmlN = '<'.$branchName.'>';
            $firstField = '';

            foreach ($branch as $b) {
                $xmlN .= '<'.$b['name'].'>';
                $xmlN .= $b['value'];
                $xmlN .= '</'.$b['name'].'>';

                if (empty($firstField)) {
                    $firstField = $b['full'];
                } else {
                    $xml = str_replace($b['full'], '', $xml);
                }
            }

            $xmlN .= '</'.$branchName.'>';

            $xml = str_replace($firstField, $xmlN, $xml);
        }

        return $xml;
    }

    protected function getDeepTagName($tag = '', $close = false)
    {
        if (empty($tag)) {
            return '';
        }

        if (strpos($tag, '/') === false) {
            return '<'.($close ? '/' : '').$tag.'>';
        }

        if ($close) {
            return '</vBLMOD></sBLMOD>';
        }

        return '<sBLMOD><nBLMOD>'.str_replace('/', '_lBLMOD_', $tag).'</nBLMOD><vBLMOD>';
    }

    protected function getPriceFormat($price = 0)
    {
        if (!empty($this->settings['currency_id'])) {
            $price = Tools::convertPrice($price, $this->settings['currency_id']);
        }

        $price = PriceFormat::convertByType($price, $this->settings['price_format_id']);

        return $price.(!empty($this->settings['price_with_currency']) ? ' '.$this->currency['iso_code'] : '');
    }

    protected function getNumberFormat($number = 0)
    {
        return Tools::ps_round($number, 3);
    }

    protected function isPriceField($field)
    {
        $priceFields = array(
            'orders_total_paid',
            'orders_total_paid_tax_incl',
            'orders_total_paid_tax_excl',
            'orders_total_wrapping',
            'orders_total_discounts',
            'orders_total_products',
            'orders_total_shipping',
            'orders_total_shipping_tax_incl',
            'orders_total_shipping_tax_excl',
            'order_detail_total_price_tax_incl',
            'order_detail_total_price_tax_excl',
            'order_detail_unit_price_tax_incl',
            'order_detail_unit_price_tax_excl',
        );

        return in_array($field, $priceFields);
    }

    protected function isNumberField($field)
    {
        $fields = array(
            'tax_rate'
        );

        return in_array($field, $fields);
    }
}

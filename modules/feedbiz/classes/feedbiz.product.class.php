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

class FeedBizProduct extends Product
{
    public static function chcekProductBySKU($SKU = null, $full = false, $id_lang = null, $reference = 'reference', $id_shop = null, $debug = false)
    {
        $id_product_attribute = null;

        // get combination first
        $sql = 'SELECT p.`id_product`, p.`id_product_attribute` FROM `'._DB_PREFIX_.'product_attribute` p ';
        $sql .= FeedBizProduct::getIdShopAssociation($id_shop);
        $sql .= 'WHERE `'.$reference.'` = "'.pSQL(trim($SKU)).'"';

        $result = Db::getInstance()->getRow($sql);

        if ($debug) {
            CommonTools::p(sprintf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql));
            CommonTools::p($result);
        }

        if (!$result || !$result['id_product']) {
            $sql = 'SELECT p.`id_product` FROM `'._DB_PREFIX_.'product` p ';
            $sql .= $id_shop ? ' JOIN `'._DB_PREFIX_.'product_shop` ps on (ps.`id_shop` = '.(int)$id_shop.' and ps.`id_product` = p.`id_product`) ' : null;
            $sql .= 'WHERE `'.$reference.'` = "'.pSQL(trim($SKU)).'"';

            $result = Db::getInstance()->getRow($sql);

            if ($debug) {
                CommonTools::p(sprintf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql));
                CommonTools::p($result);
            }

            if (!$result || !$result['id_product']) {
                return false;
            }
        } else {
            $id_product_attribute = (int)$result['id_product_attribute'];
        }

        $product = new FeedBizProduct((int)$result['id_product'], $full, $id_lang, $id_shop);
        if (Validate::isLoadedObject($product)) {
            $product->id_product_attribute = $id_product_attribute;

            return ($product);
        }

        return (false);
    }

    public static function getIdShopAssociation($id_shop = null)
    {
        /// Temporary workaround
        if (version_compare(_PS_VERSION_, '1.5', '>=') && !$id_shop) {
            if (Shop::isFeatureActive() && !$id_shop) {
                $context = Context::getContext();
                $id_shop = (int)Validate::isLoadedObject($context->shop) ? $context->shop->id : 1;
            } else {
                $id_shop = null;
            }
        }

        if ($id_shop) {
            return(' JOIN `'._DB_PREFIX_.'product_shop` ps on (ps.`id_shop` = '.(int)$id_shop.' and ps.`id_product` = p.`id_product`) ');
        }

        return(null);
    }

    public static function getProductBySKU($sku, $id_shop)
    {
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            $sql = ' SELECT IF (
                pa.`id_product`, concat(pa.`id_product`, "_", pa.`id_product_attribute`), p.`id_product`
            ) as id_product FROM `'._DB_PREFIX_.'product` p
            LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa on (p.id_product = pa.id_product)
            WHERE pa.`reference` = "'.pSQL($sku).'" or p.`reference` = "'.pSQL($sku).'" LIMIT 1 ; ';
        } else {
            $sql = 'SELECT IF (
                pa.`id_product`, concat(pa.`id_product`, "_", pa.`id_product_attribute`), p.`id_product`
            ) as id_product FROM `'._DB_PREFIX_.'product` p
            LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa on (p.id_product = pa.id_product)
            WHERE p.`id_shop_default` = "'.pSQL($id_shop).'"
            AND (pa.`reference` = "'.pSQL($sku).'" or p.`reference` = "'.pSQL($sku).'") LIMIT 1 ; ';
        }

        $ret = Db::getInstance()->executeS($sql);

        if (!$ret) {
            return (null);
        }

        $ret = array_shift($ret);

        return ($ret ['id_product']);
    }

    public static function getSimpleProductName($id_product, $id_lang = 1)
    {
        $sql = 'SELECT `name` FROM `'._DB_PREFIX_.'product_lang` WHERE `id_product` = '.
            (int)$id_product.' AND `id_lang`='.(int)$id_lang;

        $ret = Db::getInstance()->getRow($sql);

        if (isset($ret ['name'])) {
            return ($ret ['name']);
        } else {
            return (null);
        }
    }

    public static function getExportProductsMaxMin($category, $create_active, $create_in_stock, $date_from, $date_to, $debug = false, $id_product_start = null)
    {
        $condition = self::getExportProductsSQLCondition(
            $category,
            $create_active,
            $create_in_stock,
            $date_from,
            $date_to,
            $id_product_start
        );

        $sql = ' SELECT max(p.id_product) as max_id_product, min(p.id_product) as min_id_product '.$condition;

        if ($debug) {
            echo nl2br("\nSQL :".print_r($sql, true));
        }

        $rq = Db::getInstance()->executeS($sql);

        if ($debug) {
            echo nl2br("\nSQL :".print_r($rq, true));
        }

        return ($rq);
    }

    public static function getExportProducts($category, $create_active, $create_in_stock, $date_from, $date_to, $debug = false, $id_product_start = null)
    {
        $condition = self::getExportProductsSQLCondition(
            $category,
            $create_active,
            $create_in_stock,
            $date_from,
            $date_to,
            $id_product_start
        );

        $sql = 'SELECT p.id_product ,sa.`quantity`,sa.`id_product_attribute` '.$condition.' GROUP BY p.id_product ORDER BY p.id_product ASC';

        if ($debug) {
            echo nl2br("\nSQL :".print_r($sql, true));
        }

        $rq = Db::getInstance()->executeS($sql);

        if ($debug) {
            echo nl2br("\nSQL :".print_r($rq, true));
        }

        return ($rq);
    }

    public static function getExportProductsSQLCondition($category, $create_active, $create_in_stock, $date_from, $date_to, $id_product_start)
    {
        if ($create_active) {
            $create_active = ' p.`active` > 0 ';
        } else {
            $create_active = ' 1 ';
        }

        if ($date_from && $date_to) {
            $date_filter = '(`date_add` BETWEEN "'.pSQL($date_from).'" AND "'.pSQL($date_to).' 23:59:59")';
        } else {
            $date_filter = '1';
        }

        if ($id_product_start != null) {
            $product_con = ' p.id_product >  '.(int)$id_product_start;
        } else {
            $product_con = ' 1 ';
        }

        if ($category != null && is_array($category)) {
            $category_condition = 'p.`id_category_default` IN ('.implode(',', array_map('intval', $category)).')';
        } else {
            $category_condition = 'p.`id_category_default` = '.(int)$category;
        }

        if (version_compare(_PS_VERSION_, '1.5.0.5', '<')) {
            if ($create_in_stock) {
                $in_stock = ' p.`quantity` > 0 ';
            } else {
                $in_stock = ' 1 ';
            }

            $sql = ' FROM `'._DB_PREFIX_.'product` p LEFT JOIN `'._DB_PREFIX_.
                'category_product` cp on (cp.id_product = p.id_product) '.' WHERE '.
                $category_condition.' AND '.$create_active.'AND'.$in_stock.' AND '.$product_con.' AND '.$date_filter;
        } else {
            if ($create_in_stock) {
                $in_stock = ' sa.`quantity` > 0 ';
            } else {
                $in_stock = ' 1 ';
            }

            $sql = ' FROM `'._DB_PREFIX_.'product` p
                INNER JOIN `'._DB_PREFIX_.'product_shop` product_shop ON product_shop.id_product = p.id_product '.
                Shop::addSqlRestrictionOnLang('product_shop').'
                LEFT JOIN `'._DB_PREFIX_.'category_product` cp on (cp.id_product = p.id_product)
                LEFT JOIN `'._DB_PREFIX_.'stock_available` sa on (p.id_product = sa.id_product)
                WHERE '.$category_condition.' AND '.$create_active.'AND'.$in_stock.' AND '.$product_con.
                ' AND '.$date_filter;
        }

        return $sql;
    }

    public static function getProductQuantity($id_product, $id_product_attribute)
    {
        if ($id_product_attribute) {
            $sql
                = '
              SELECT pa.quantity FROM `'._DB_PREFIX_.'product` p
              LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa on (p.id_product = pa.id_product)
                  WHERE pa.id_product_attribute = '.(int)$id_product_attribute.' and p.id_product = '.(int)$id_product;
        } else {
            $sql
                = '
              SELECT p.quantity FROM `'._DB_PREFIX_.'product` p
                  WHERE id_product = '.(int)$id_product;
        }
        $result = Db::getInstance()->getRow($sql);
        if (is_array($result) && count($result)) {
            return ($result ['quantity']);
        } else {
            return (false);
        }
    }

    public static function updateProductDate($id_product)
    {
        return Db::getInstance()->execute(
            'UPDATE `'._DB_PREFIX_.'product`
            SET `date_upd` = "'.pSQL(date('Y-m-d H:i:s')).'"
            WHERE `id_product` = '.(int)$id_product
        );
    }

    public static function getProductOptions($id_product, $id_product_attribute = null, $id_lang = null)
    {
        $option_fields = self::getProductOptionFields();

        if (!is_array($option_fields) || !count($option_fields)) {
            return (false);
        }

        if ($id_lang !== null) {
            $sql_lang = ' AND `id_lang`="'.(int)$id_lang.'"';
        } else {
            $sql_lang = '';
        }

        if ($id_product_attribute !== null) {
            $sql_attribute = ' AND `id_product_attribute`='.(int)$id_product_attribute;
        } else {
            $sql_attribute = '';
        }

        $sql = 'SELECT * FROM `'._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_OPTIONS.'` p WHERE `id_product` = '.
            (int)$id_product.$sql_attribute.$sql_lang;

        $result = Db::getInstance()->executeS($sql);

        if (Feedbiz::$debug_mode) {
            echo "<pre>\n";
            printf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql);
            print_r($result);
            echo "</pre>\n";
        }

        return ($result);
    }

    public static function deleteProductOptions($id_product, $id_lang, $id_product_attribute = null)
    {
        $option_fields = self::getProductOptionFields();

        if (!is_array($option_fields) || !count($option_fields)) {
            return (false);
        }

        if (!$id_product) {
            return (false);
        }

        if ($id_product_attribute) {
            $sql_attribute = ' AND `id_product_attribute`='.(int)$id_product_attribute;
        } else {
            $sql_attribute = null;
        }

        $sql = 'DELETE FROM `'._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_OPTIONS.'` WHERE `id_product` ='.
            (int)$id_product.' AND `id_lang`="'.(int)$id_lang.'"'.$sql_attribute;

        $rq = Db::getInstance()->execute($sql);

        if (Feedbiz::$debug_mode) {
            echo "<pre>\n";
            printf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql);
            print_r($rq);
            echo "</pre>\n";
        }
        self::updateProductDate($id_product);
        return ($rq);
    }

    public static function getProductOptionFields()
    {
        static $option_fields = array();

        if (is_array($option_fields) && count($option_fields)) {
            return ($option_fields);
        }

        // Extra Fields / New Fields
        // id_product,id_product_attribute,id_lang,force,disable,price,shipping,text
        $option_fields_config = Configuration::get('FEEDBIZ_PRODUCT_OPTION_FIELDS', null, 0, 0);

        if ($option_fields_config && strpos($option_fields_config, ',')) {
            $option_fields = explode(',', $option_fields_config);

            if (!is_array($option_fields) || !count($option_fields)) {
                $option_fields = array();
            }
        }
        return ($option_fields);
    }

    public static function updateProductOptions($id_product, $id_lang, $options, $id_product_attribute = null)
    {
        $sql = '';

        // check is exist
        if (self::getProductOptions($id_product, $id_product_attribute, $id_lang)) {
            foreach ($options as $field => $value) {
                $field = sprintf('`%s`', Tools::strtolower($field));
                if (is_bool($value)) {
                    $sql .= $field.' = '.((bool)$value ? 1 : 0).', ';
                } elseif (is_float($value)) {
                    $sql .= $field.' = '.(float)$value.', ';
                } elseif (is_int($value)) {
                    $sql .= $field.' = '.(int)$value.', ';
                } elseif (is_numeric($value)) {
                    $sql .= $field.' = '.$value.', ';
                } elseif (empty($value)) {
                    $sql .= $field.' = null, ';
                } else {
                    $sql .= $field.' = "'.pSQL($value).'", ';
                }
            }
            $sql_update = 'UPDATE `'._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_OPTIONS.'` SET '.rtrim($sql, ' ,').
                ' WHERE '.'id_product = '.(int)$id_product.' AND id_lang = '.(int)$id_lang.' '.
                (isset($id_product_attribute) ? ' AND id_product_attribute = '.(int)$id_product_attribute : '').' ; ';
            $rq = Db::getInstance()->execute($sql_update);
            if (Feedbiz::$debug_mode) {
                echo "<pre>\n";
                printf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql_update);
                print_r($rq);
                echo "</pre>\n";
            }
            self::updateProductDate($id_product);
            return ($rq);
        } else {
            return self::setProductOptions($id_product, $id_lang, $options, $id_product_attribute);
        }
    }

    public static function setProductOptions($id_product, $id_lang, $options, $id_product_attributes = null)
    {
        $option_fields = self::getProductOptionFields();

        if (!is_array($option_fields) || !count($option_fields)) {
            return (false);
        }

        $fields_sql = null;

        foreach ($option_fields as $field) {
            $fields_sql .= sprintf('`%s`, ', $field);
        }
        $fields_sql = rtrim($fields_sql, ', ');

        $sql = 'REPLACE INTO `'._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_OPTIONS.'` ('.$fields_sql.') values(';

        $insert_statement = null;

        foreach ($option_fields as $field) {
            switch ($field) {
                case 'id_product':
                    $insert_statement .= (int)$id_product.', ';
                    break;
                case 'id_product_attribute':
                    $insert_statement .= (int)$id_product_attributes.', ';
                    break;
                case 'id_lang':
                    $insert_statement .= (int)$id_lang.', ';
                    break;
                default:
                    if (array_key_exists($field, $options)) {
                        // Can also use filter_var() for float and int
                        if (is_bool($options[$field])) {
                            $insert_statement .= ((bool)$options[$field] ? 1 : 0).', ';
                        } elseif (is_float($options[$field]) || is_numeric($options[$field])
                            && ((float)$options[$field] != (int)$options[$field])) {
                            $insert_statement .= (float)$options[$field].', ';
                        } elseif (is_int($options[$field])) {
                            $insert_statement .= (int)$options[$field].', ';
                        } elseif (is_numeric($options[$field])) {
                            $insert_statement .= (int)$options[$field].', ';
                        } elseif (empty($options[$field])) {
                            $insert_statement .= 'null, ';
                        } else {
                            $insert_statement .= '"'.pSQL($options[$field]).'", ';
                        }
                    } else {
                        $insert_statement .= 'null, ';
                    }
            }
        }

        $sql .= rtrim($insert_statement, ' ,').');';

        $rq = Db::getInstance()->execute($sql);


        if (Feedbiz::$debug_mode) {
            echo "<pre>\n";
            printf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql);
            print_r($rq);
            echo "</pre>\n";
        }
        self::updateProductDate($id_product);

        return ($rq);
    }

    // Propagate Option
    public static function propagateProductOptionToCategory($id_category, $id_lang, $field, $value)
    {
        $pass = self::initProductOptions($id_lang);

        $insert_statement = null;

        if (is_bool($value)) {
            $insert_statement .= sprintf('`%s`=%s', $field, ((bool)$value ? 1 : 0));
        } elseif (is_float($value)) {
            $insert_statement .= sprintf('`%s`=%s', $field, (float)$value);
        } elseif (is_int($value)) {
            $insert_statement .= sprintf('`%s`=%s', $field, (int)$value);
        } elseif (is_numeric($value)) {
            $insert_statement .= sprintf('`%s`=%s', $field, (int)$value);
        } elseif (empty($value)) {
            $insert_statement .= sprintf('`%s`=null', $field, (int)$value);
        } else {
            $insert_statement .= sprintf('`%s`="%s"', $field, pSQL($value));
        }
        $insert_statement .= ', p.`date_upd` = "'.date('Y-m-d H:i:s').'" ';

        $sql = 'UPDATE `'._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_OPTIONS.'` mpo, `'._DB_PREFIX_.'product` p SET '.
            $insert_statement.'WHERE p.`id_product` = mpo.`id_product` AND p.`id_category_default` = '.
            (int)$id_category.' AND mpo.`id_lang`='.(int)$id_lang;

        if (!$rq = Db::getInstance()->execute($sql)) {
            $pass = false;
        }

        if (Feedbiz::$debug_mode) {
            echo "<pre>\n";
            printf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql);
            print_r($rq);
            echo "</pre>\n";
        }

        return ($pass);
    }

    public static function propagateProductOptionToShop($id_lang, $field, $value)
    {
        $pass = self::initProductOptions($id_lang);

        $insert_statement = null;

        if (is_bool($value)) {
            $insert_statement .= sprintf('`%s`=%s', $field, ((bool)$value ? 1 : 0));
        } elseif (is_float($value)) {
            $insert_statement .= sprintf('`%s`=%s', $field, (float)$value);
        } elseif (is_int($value)) {
            $insert_statement .= sprintf('`%s`=%s', $field, (int)$value);
        } elseif (is_numeric($value)) {
            $insert_statement .= sprintf('`%s`=%s', $field, (int)$value);
        } elseif (empty($value)) {
            $insert_statement .= sprintf('`%s`=null', $field, (int)$value);
        } else {
            $insert_statement .= sprintf('`%s`="%s"', $field, pSQL($value));
        }
        $insert_statement .= ', p.`date_upd` = "'.date('Y-m-d H:i:s').'" ';

        $sql = 'UPDATE `'._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_OPTIONS.'` mpo, `'._DB_PREFIX_.'product` p SET '.
            $insert_statement.' WHERE p.`id_product`=mpo.`id_product` AND mpo.`id_lang`='.(int)$id_lang;

        if (!$rq = Db::getInstance()->execute($sql)) {
            $pass = $pass && false;
        }

        if (Feedbiz::$debug_mode) {
            echo "<pre>\n";
            printf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql);
            print_r($rq);
            echo "</pre>\n";
        }

        return ($pass);
    }


    public static function propagateProductOptionToManufacturer($id_manufacturer, $id_lang, $field, $value)
    {
        $pass = self::initProductOptions($id_lang);

        $insert_statement = null;

        if (is_bool($value)) {
            $insert_statement .= sprintf('`%s`=%s ', $field, ((bool)$value ? 1 : 0));
        } elseif (is_float($value)) {
            $insert_statement .= sprintf('`%s`=%s ', $field, (float)$value);
        } elseif (is_int($value)) {
            $insert_statement .= sprintf('`%s`=%s ', $field, (int)$value);
        } elseif (is_numeric($value)) {
            $insert_statement .= sprintf('`%s`=%s ', $field, (int)$value);
        } elseif (empty($value)) {
            $insert_statement .= sprintf('`%s`=null ', $field, (int)$value);
        } else {
            $insert_statement .= sprintf('`%s`="%s" ', $field, pSQL($value));
        }
        $insert_statement .= ', p.`date_upd` = "'.date('Y-m-d H:i:s').'" ';

        $sql = 'UPDATE `'._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_OPTIONS.'` mpo, `'._DB_PREFIX_.'product` p, `'.
            _DB_PREFIX_.'manufacturer` m SET '.$insert_statement.
            ' WHERE p.`id_product` = mpo.`id_product` AND mpo.`id_lang`='.(int)$id_lang.
            ' AND p.`id_manufacturer`=m.`id_manufacturer` AND p.`id_manufacturer`='.(int)$id_manufacturer;

        if (!$rq = Db::getInstance()->execute($sql)) {
            $pass = $pass && false;
        }

        if (Feedbiz::$debug_mode) {
            echo "<pre>\n";
            printf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql);
            print_r($rq);
            echo "</pre>\n";
        }

        return ($pass);
    }

    public static function initProductOptions($id_lang)
    {
        $pass = true;

        $categories = FeedbizConfiguration::get('FEEDBIZ_CATEGORIES');

        if (is_array($categories) && count($categories)) {
            $list = rtrim(implode(',', array_map('intval', $categories)), ',');

            $products = Db::getInstance()->executeS('
                SELECT p.`id_product` from `'._DB_PREFIX_.'product` p
                LEFT JOIN `'._DB_PREFIX_.'category_product` cp on (p.`id_product` = cp.`id_product`)
                WHERE cp.`id_category` IN ('.pSQL($list).')');


            if (Feedbiz::$debug_mode) {
                echo "<pre>\n";
                printf("%s(%d): Categories - '%s'\n", basename(__FILE__), __LINE__, $list);
                echo "</pre>\n";
            }

            foreach ($products as $product) {
                $sql = 'INSERT IGNORE `'._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_OPTIONS.'` (`id_product`,`id_lang`)
                values ('.(int)$product ['id_product'].', '.(int)$id_lang.')';

                if (!Db::getInstance()->execute($sql)) {
                    $pass = $pass && false;
                }
            }
        }
        return ($pass);
    }

    public static function getAttributeColor($id_attribute)
    {
        $color = null;
        $row_color = Db::getInstance()->executeS(
            'SELECT color FROM `'._DB_PREFIX_.'attribute` WHERE `id_attribute` = '.(int)$id_attribute.' LIMIT 0,1 '
        );

        foreach ($row_color as $val_color) {
            $color = $val_color ['color'];
        }

        return $color;
    }

    public static function getCarrierTax()
    {
        $tax_list = array();
        $carrier_tax = Db::getInstance()->executeS(
            'SELECT trgs.id_carrier, tr.id_tax, t.rate,c.iso_code from '._DB_PREFIX_.'carrier_tax_rules_group_shop trgs 
                join '._DB_PREFIX_.'tax_rule tr on tr.id_tax_rules_group = trgs.id_tax_rules_group
                join '._DB_PREFIX_.'country c on c.id_country = tr.id_country 
                join '._DB_PREFIX_.'tax t on t.id_tax = tr.id_tax
            '
        );

        foreach ($carrier_tax as $v) {
            $tax_list[$v['id_carrier']][$v['iso_code']] = $v;
        }

        return $tax_list;
    }

    // Check if the condition field is present in the DB (for Prestashop < 1.4)
    public static function getConditionField()
    {
        // Products Condition/State
        //
        $sql = 'SHOW COLUMNS FROM `'._DB_PREFIX_.'product` where Field = "condition"';
        $query = Db::getInstance()->executeS($sql);

        if (is_array($query)) {
            $query = array_shift($query);
        }

        if (isset($query ['Field']) && $query ['Field'] == 'condition') {
            return ($query);
        } else {
            return (false);
        }
    }

    public static function getUpdateProductsMaxMin($category, $create_active, $create_in_stock, $date_from, $date_range_from = null, $date_range_to = null, $debug = false, $last_exported_product = 0)
    {
        $condition = self::getUpdateProductsSQLCondition(
            $category,
            $create_active,
            $create_in_stock,
            $date_from,
            $date_range_from,
            $date_range_to,
            $last_exported_product
        );

        $sql = 'UPDATE '._DB_PREFIX_.'product 
            SET date_upd = now( ) 
            WHERE id_product IN 
            (SELECT i.product_id 
                FROM '._DB_PREFIX_.'orders o,'._DB_PREFIX_.'order_detail i WHERE
                    o.date_add > date_sub( now( ), INTERVAL 24 HOUR ) AND o.id_order = i.id_order )';
        if ($debug) {
            echo nl2br("\nSQL :".print_r($sql, true));
        }
        Db::getInstance()->execute($sql);

        $sql = 'SELECT max(p.id_product) as max_id_product, min(p.id_product) as min_id_product '.$condition;

        if ($debug) {
            echo nl2br("\nSQL :".print_r($sql, true));
        }

        $rq = Db::getInstance()->executeS($sql);

        if ($debug) {
            echo nl2br("\nSQL :".print_r($rq, true));
        }

        return ($rq);
    }

    public static function getUpdateProducts($category, $create_active, $create_in_stock, $date_from, $date_range_from = null, $date_range_to = null, $debug = false, $last_exported_product = 0)
    {
        $condition = self::getUpdateProductsSQLCondition(
            $category,
            $create_active,
            $create_in_stock,
            $date_from,
            $date_range_from,
            $date_range_to,
            $last_exported_product
        );

        $sql = 'SELECT p.id_product ,sa.`quantity`,sa.`id_product_attribute`, p.date_upd '.$condition.' GROUP by p.id_product';

        if ($debug) {
            echo nl2br("\nSQL :".print_r($sql, true));
            exit;
        }

        $rq = Db::getInstance()->executeS($sql);

        if ($debug) {
            echo nl2br("\nSQL :".print_r($rq, true));
        }

        return ($rq);
    }

    public static function getUpdateProductsSQLCondition($category, $create_active, $create_in_stock, $date_from, $date_range_from = null, $date_range_to = null, $last_exported_product = 0)
    {
        if ($create_active) {
            $create_active = ' p.`active` > 0 ';
        } else {
            $create_active = ' 1 ';
        }

        if (isset($date_range_from) && !empty($date_range_from) && isset($date_range_to) && !empty($date_range_to)) {
            $date_range = 'AND ((p.date_upd >= "'.pSQL($date_range_from).'" AND p.date_upd <= "'.
                pSQL($date_range_to).'") OR sp.`from` LIKE "'.pSQL($date_from).'%")';
        } else {
            $date_range = 'AND (p.date_upd >= "'.pSQL($date_from).'" OR sp.`from` LIKE "'.pSQL(date('Y-m-d')).'%")';
        }

        if ($last_exported_product > 0) {
            $product_condition = ' p.id_product > "'.($last_exported_product).'" ';
        } else {
            $product_condition = ' 1 ';
        }

        if (is_array($category) && sizeof($category) > 0) {
            $category_condition = ' cp.`id_category` IN ('.pSQL(implode(',', $category)).') ';
        } else {
            $category_condition = ' cp.`id_category` = "'.(int)$category.'" ';
        }

        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            if ($create_in_stock) {
                $in_stock = ' p.`quantity` > 0 ';
            } else {
                $in_stock = ' 1 ';
            }

            $sql = ' FROM `'._DB_PREFIX_.'product` p
                LEFT JOIN `'._DB_PREFIX_.'category_product` cp on (cp.id_product = p.id_product) '.' WHERE '.
                pSQL($category_condition).' AND '.pSQL($create_active).pSQL($date_range).' AND '.
                pSQL($product_condition).' AND '.pSQL($in_stock);
        } else {
            if ($create_in_stock) {
                $in_stock = ' sa.`quantity` > 0 ';
            } else {
                $in_stock = ' 1 ';
            }

            $sql = ' FROM `'._DB_PREFIX_.'product` p
                INNER JOIN `'._DB_PREFIX_.'product_shop` product_shop ON product_shop.id_product = p.id_product '.
                Shop::addSqlRestrictionOnLang('product_shop').' LEFT JOIN `'.
                _DB_PREFIX_.'category_product` cp on (cp.id_product = p.id_product) LEFT JOIN `'.
                _DB_PREFIX_.'stock_available` sa on (p.id_product = sa.id_product) LEFT JOIN `'.
                _DB_PREFIX_.'specific_price` sp ON sp.id_product = p.id_product AND sp.id_shop
                IN (product_shop.id_shop, 0) WHERE '.pSQL($category_condition).' AND '.
                pSQL($create_active).($date_range).' AND '. ($product_condition).' AND '.pSQL($in_stock);
        }

        return $sql;
    }

    public static function getSummaries($feedbiz_categories, $id_manufacturer = array(), $id_supplier = array())
    {
        $result = array(
            'total_products' => 0,
            'total_inactive' => 0,
            'total_filter' => 0
        );
        $sql = array();
        $sql ['total_products'] = "select count(*) as cnt from `"._DB_PREFIX_."product`";
        $sql ['total_inactive'] = "select count(*) as cnt from `"._DB_PREFIX_."product` where active <> '1'";
        $sql ['total_sent'] = "select Count(Distinct p.id_product)  as cnt from `"._DB_PREFIX_."product` p
					JOIN `"._DB_PREFIX_."category_product` cp on (cp.id_product = p.id_product)
					where cp.`id_category` IN ('".pSQL(implode("', '", $feedbiz_categories))."')";

        if ($id_manufacturer || $id_supplier) {
            $sql ['total_filter'] = "select Count(Distinct p.id_product)  as cnt from `"._DB_PREFIX_."product` p
					JOIN `"._DB_PREFIX_."category_product` cp on (cp.id_product = p.id_product)
					where cp.`id_category` IN ('".pSQL(implode("', '", $feedbiz_categories))."')";
            $causes = array();
            $causes_sent = array();
            if ($id_manufacturer) {
                $causes [] = "id_manufacturer IN ('".pSQL(implode("', '", $id_manufacturer))."')";
                $causes_sent [] = "id_manufacturer NOT IN ('".pSQL(implode("', '", $id_manufacturer))."')";
            }
            if ($id_supplier) {
                $causes [] = "id_supplier IN ('".pSQL(implode("', '", $id_supplier))."')";
                $causes_sent [] = "id_supplier NOT IN ('".pSQL(implode("', '", $id_supplier))."')";
            }
            $sql ['total_filter'] .= " AND (".pSQL(implode(" OR ", $causes)).")";
            $sql ['total_sent'] .= " AND (".pSQL(implode(" AND ", $causes_sent)).")";
        }

        foreach ($sql as $k => $v) {
            $rq = Db::getInstance()->executeS($v);
            foreach ($rq as $row) {
                $result [$k] = $row ['cnt'];
            }
        }

        return ($result);
    }

    public static function getBusinessPriceRulesBreakdown($id_product, $id_shop, $id_group, $id_product_attribute = null)
    {
        $attribute_id = '' ;
        if (isset($id_product_attribute)) {
            $attribute_id = ' AND `id_product_attribute` IN (0, '.(int)$id_product_attribute.') ' ;
        }
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'specific_price` WHERE `id_product` IN (0, '.(int)$id_product.')  AND `from_quantity` > 0
                ' . $attribute_id . ' 
                AND `id_shop` IN (0, '.(int)$id_shop.')               
                AND `id_group` IN (0, '.(int)$id_group.')
                AND NOW() > `from` AND `to` > NOW() + INTERVAL 1 DAY ORDER BY `from_quantity` LIMIT 3;
        ';

        return (Db::getInstance()->executeS($sql));
    }
}

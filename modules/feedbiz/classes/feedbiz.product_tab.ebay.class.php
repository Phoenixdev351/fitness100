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

class FeedBizProductTabEbay extends FeedBizProductTab
{
    /**
     * @var array
     */
    protected static $field_list       = array();
    /**
     * @var array
     */
    protected $marketplace_tabs = array();
    /**
     * @var bool
     */
    protected $has_repricing    = false;
    /**
     * @var bool
     */
    protected $has_europe       = false;

    /**
     * FeedBizProductTabEbay constructor.
     *
     * @param $marketplace_tabs
     */
    public function __construct($marketplace_tabs)
    {
        if (!is_array($marketplace_tabs) || !count($marketplace_tabs)) {
            $this->marketplace_tabs = null;
        } else {
            $this->marketplace_tabs = $marketplace_tabs;
        }

        parent::__construct();
    }


    /**
     * Generate product tab for eBay
     * @param $product
     * @param $combinations
     *
     * @return array|bool
     */
    public function marketplaceProductTabContent($product, $combinations)
    {
        if (!Validate::isLoadedObject($product)) {
            return (false);
        }

        self::$field_list = self::getProductOptionFields();

        $view_params = array();

        $marketplaces = $this->countrySelector();

        $view_params['active'] = true;

        $view_params['json_url'] = $this->url.'functions/product_options.json.php?context_key='.
            FeedbizContext::getKey($this->context->shop);

        $view_params['marketplaces'] = $marketplaces;
        $view_params['show_countries'] = count($marketplaces) > 1;
        $view_params['complex_id'] = sprintf('%d_0', $product->id);

        $view_params['product_options'] = $this->productOptions($product, $combinations);

        return ($view_params);
    }

    /**
     * @param $product
     * @param $combinations
     *
     * @return array
     */
    public function productOptions($product, $combinations)
    {
        $view_params = array();

        $id_lang = $this->context->language->id;

        foreach (Feedbiz::$ebay_regions as $domain => $region) {
            if (!in_array($domain, $this->marketplace_tabs)) {
                continue;
            }

            $product_options = self::getProductOptions($product->id, null, $region);

            if (is_array($product_options) && count($product_options)) {
                $product_option = reset($product_options);
            } else {
                $product_option = array_fill_keys(self::getProductOptionFields(), null);
            }

            $language = array();
            $language['europe'] = $this->has_europe;

            $view_params['options'][$region] = $this->productOptionsDetails($region, $product_option);
            $view_params['options'][$region]['name'] = sprintf(
                '%s (%s)',
                $product->name[$id_lang],
                $product->reference ? $product->reference : 'n/a'
            );
            $view_params['options'][$region]['complex_id'] = sprintf('%d_0', $product->id);
            $view_params['options'][$region]['region'] = $region;

            $view_params['combinations_options'] = array();

            if (is_array($combinations) && count($combinations)) {
                $view_params['combinations_options'][$region] = array();

                foreach ($combinations as $complex_id => $combination) {
                    $combination_options = self::getProductOptions(
                        $product->id,
                        (int)$combination['id_product_attribute'],
                        $region
                    );

                    if (is_array($combination_options) && count($combination_options)) {
                        $combination_option = reset($combination_options);
                    } else {
                        $combination_option = array_fill_keys(self::getProductOptionFields(), null);
                    }

                    $view_params['combinations_options'][$region][$complex_id] = $this->productOptionsDetails(
                        $region,
                        $combination_option
                    );
                    $view_params['combinations_options'][$region][$complex_id]['name'] = sprintf(
                        '%s - %s (%s)',
                        $product->name[$id_lang],
                        $combination['name'],
                        $combination['reference'] ? $combination['reference'] : 'n/a'
                    );
                    $view_params['combinations_options'][$region][$complex_id]['id_product_attribute'] =
                        (int)$combination['id_product_attribute'];
                    $view_params['combinations_options'][$region][$complex_id]['region'] = $region;
                }
            }
        }
        return ($view_params);
    }

    /**
     * @param $region
     * @param $defaults
     *
     * @return array
     */
    public function productOptionsDetails($region, $defaults)
    {
        $view_params = array();

        if (in_array('price', self::$field_list)) {
            $view_params['extra_price'] = $this->extraPrice($region, $defaults);
        }

        if (in_array('disable', self::$field_list)) {
            $view_params['unavailable'] = $this->unavailable($region, $defaults);
        }

        if (in_array('force', self::$field_list)) {
            $view_params['force_in_stock'] = $this->forceInStock($region, $defaults);
        }

        $view_params['go_ebay'] = $this->goEbay($region, $defaults);

        return ($view_params);
    }

    /**
     * @return array
     */
    public function countrySelector()
    {
        $marketplaces = array();

        foreach (Feedbiz::$ebay_regions as $domain => $region) {
            if (!in_array($domain, $this->marketplace_tabs)) {
                continue;
            }

            if (strstr($region, '-')) {
                $pieces = explode('-', $region);
                $country = $pieces[0];
                $lang = $pieces[1];
                $flag = $country;
            } else {
                $flag = $region;
                $lang = null;
            }
            $marketplaces[$region] = array();
            $marketplaces[$region]['default'] = $this->context->language->iso_code == $lang;
            $marketplaces[$region]['name'] = sprintf('%s', FeedbizTools::ebayRegionToDomain($region));
            $marketplaces[$region]['region'] = $region;
            $marketplaces[$region]['image'] = $this->images.'geo_flags_web2/flag_'.$flag.'_64px.png';

            if ($lang) {
                $marketplaces[$region]['lang_flag'] = $this->images.'geo_flags_web2/flag_'.$lang.'_32px.png';
                $marketplaces[$region]['lang_iso_code'] = $lang;
            }

            $marketplaces[$region]['name'] = sprintf('eBay %s', Tools::strtoupper($region));
        }
        return ($marketplaces);
    }

    /**
     * @param $region
     * @param null $defaults
     *
     * @return array
     */
    private function goEbay($region, &$defaults = null)
    {
        $view_params = array();
        $asin = isset($defaults['asin1']) && $defaults['asin1'] ? $defaults['asin1'] : null;

        if ($asin) {
            $view_params = array(
                'default' => self::goToProductPage($region, $asin),
                'region' => $region
            );
        }
        return ($view_params);
    }

    /**
     * @param $region
     * @param null $defaults
     *
     * @return array
     */
    private function forceInStock($region, &$defaults = null)
    {
        $default = isset($defaults['force']) && is_numeric($defaults['force']) && $defaults['force'] > 0 ?
            (int)$defaults['force'] : '';

        $view_params = array(
            'default' => $default,
            'region' => $region
        );

        return ($view_params);
    }

    /**
     * @param $region
     * @param null $defaults
     *
     * @return array
     */
    private function unavailable($region, &$defaults = null)
    {
        $checked = isset($defaults['disable']) && (bool)$defaults['disable'] ? 'checked="checked"' : '';

        $view_params = array(
            'checked' => $checked,
            'region' => $region
        );

        return ($view_params);
    }

    /**
     * @param $region
     * @param null $defaults
     *
     * @return array
     */
    private function extraPrice($region, &$defaults = null)
    {
        $view_params = array(
            'default' => isset($defaults['price']) ? $defaults['price'] : null,
            'region' => $region
        );

        return ($view_params);
    }

    /**
     * @param $region
     * @param $code
     *
     * @return bool|string
     */
    public static function goToProductPage($region, $code)
    {
        if (!($domain = FeedbizTools::ebayRegionToDomain($region))) {
            return (false);
        }

        return ('http://'.$domain.'/product/'.$code);
    }

    /**
     * @param $id_product
     * @param null $id_product_attribute
     * @param null $region
     *
     * @return array|bool|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function getProductOptions($id_product, $id_product_attribute = null, $region = null)
    {
        $option_fields = self::getProductOptionFields();

        if (!is_array($option_fields) || !count($option_fields)) {
            return (false);
        }

        if ($region !== null) {
            $sql_region = ' AND `region`="'.pSQL($region).'"';
        } else {
            $sql_region = '';
        }

        if ($id_product_attribute !== null) {
            $sql_id_product_attribute = ' AND `id_product_attribute`='.(int)$id_product_attribute;
        } else {
            $sql_id_product_attribute = ' AND `id_product_attribute`=0';
        }

        $sql = 'SELECT * FROM `'._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_EBAY.'` p WHERE `id_product` = '.
            (int)$id_product.$sql_id_product_attribute.$sql_region;

        $result = Db::getInstance()->executeS($sql);

        if (Feedbiz::$debug_mode) {
            echo "<pre>\n";
            printf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql);
            print_r($result);
            echo "</pre>\n";
        }

        return ($result);
    }

    /**
     * @return array
     */
    public static function getProductOptionFields()
    {
        static $option_fields = array();

        if (is_array($option_fields) && count($option_fields)) {
            return ($option_fields);
        }

        // Extra Fields / New Fields
        $option_fields_config = Configuration::get('FEEDBIZ_OPTION_FIELDS_EBAY', null, 0, 0);

        if ($option_fields_config && strpos($option_fields_config, ',')) {
            $option_fields = explode(',', $option_fields_config);

            if (!is_array($option_fields) || !count($option_fields)) {
                $option_fields = array();
            }
        }
        return ($option_fields);
    }


    /**
     * @param $id_product
     * @param $region
     * @param $options
     * @param null $id_product_attributes
     *
     * @return bool
     */
    public static function setProductOptions($id_product, $region, $options, $id_product_attributes = null)
    {
        $option_fields = self::getProductOptionFields();

        if (!is_array($option_fields) || !count($option_fields)) {
            return (false);
        }

        $fields_sql = null;

        foreach ($option_fields as $field) {
            $fields_sql .= sprintf('`%s`, ', pSQL($field));
        }
        $fields_sql = rtrim($fields_sql, ', ');

        $sql = 'REPLACE INTO `'._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_EBAY.'` ('.$fields_sql.') values(';

        $insert_statement = null;

        foreach ($option_fields as $field) {
            switch ($field) {
                case 'id_product':
                    $insert_statement .= (int)$id_product.', ';
                    break;
                case 'id_product_attribute':
                    $insert_statement .= (int)$id_product_attributes.', ';
                    break;
                case 'region':
                    $insert_statement .= '"'.pSQL($region).'", ';
                    break;
                default:
                    if (array_key_exists($field, $options)) {
                        if (is_bool($options[$field])) {
                            $insert_statement .= ((bool)$options[$field] ? 1 : 0).', ';
                        } elseif (is_float($options[$field])) {
                            $insert_statement .= (float)$options[$field].', ';
                        } elseif (is_int($options[$field])) {
                            $insert_statement .= (int)$options[$field].', ';
                        } elseif (is_numeric($options[$field])) {
                            $insert_statement .= $options[$field].', ';
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

        // Update child value
        if (!isset($id_product_attributes) || !$id_product_attributes || $id_product_attributes == 0) {
            $usql = '';
            foreach ($options as $field => $value) {
                if ($field != "id_product_attribute") {
                    $field = sprintf('`%s`', Tools::strtolower($field));
                    if (is_bool($value)) {
                        $usql .= $field.' = '.((bool)$value ? 1 : 0).', ';
                    } elseif (is_float($value)) {
                        $usql .= $field.' = '.(float)$value.', ';
                    } elseif (is_int($value)) {
                        $usql .= $field.' = '.(int)$value.', ';
                    } elseif (is_numeric($value)) {
                        $usql .= $field.' = '.$value.', ';
                    } elseif (empty($value)) {
                        $usql .= $field.' = null, ';
                    } else {
                        $usql .= $field.' = "'.pSQL($value).'", ';
                    }
                }
            }

            $update_sql = 'UPDATE `'._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_EBAY.'`
            SET '.rtrim($usql, ' ,').' WHERE '.'id_product = '.(int)$id_product.' AND region = "'.pSQL($region).'" ;';
            $update_rq = Db::getInstance()->execute($update_sql);

            if (Feedbiz::$debug_mode) {
                echo "<pre>\n";
                printf("%s(%d): UPDATE SQL - '%s'\n", basename(__FILE__), __LINE__, $update_sql);
                print_r($update_rq);
                echo "</pre>\n";
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
        FeedBizProduct::updateProductDate($id_product);
        return ($rq);
    }

    public static function updateProductOptions($id_product, $options, $region, $id_product_attribute = null)
    {
        $sql = '';

        // check is exist
        if (self::getProductOptions($id_product, $id_product_attribute, $region)) {
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

            $sql_update = 'UPDATE `'._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_EBAY.'` SET '.rtrim($sql, ' ,').
                ' WHERE '.'id_product = '.(int)$id_product.' AND region = "'.pSQL($region).'" '.
                (isset($id_product_attribute) ? ' AND id_product_attribute = '.(int)$id_product_attribute : '').' ; ';

            $rq = Db::getInstance()->Execute($sql_update);
            if (FeedBiz::$debug_mode) {
                echo "<pre>\n";
                printf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql_update);
                print_r($rq);
                echo "</pre>\n";
            }
            FeedBizProduct::updateProductDate($id_product);

            return ($rq);
        } else {
            return self::setProductOptions($id_product, $region, $options, $id_product_attribute);
        }
    }

    /**
     * @param $id_product
     * @param $region
     * @param null $id_product_attribute
     *
     * @return bool
     */
    public static function deleteProductOptions($id_product, $region, $id_product_attribute = null)
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

        $sql = 'DELETE FROM `'._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_EBAY.'` WHERE `id_product` ='.(int)$id_product.
            ' AND `region`="'.pSQL($region).'"'.$sql_attribute;

        $rq = Db::getInstance()->execute($sql);

        if (Feedbiz::$debug_mode) {
            echo "<pre>\n";
            printf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql);
            print_r($rq);
            echo "</pre>\n";
        }
        FeedBizProduct::updateProductDate($id_product);
        return ($rq);
    }

    /**
     * @param $region
     *
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public static function initProductOptions($region)
    {
        $pass = true;

        $categories = FeedbizConfiguration::get('FEEDBIZ_CATEGORIES');

        if (is_array($categories) && count($categories)) {
            $list = rtrim(implode(',', array_map('intval', $categories)), ',');

            $products = Db::getInstance()->executeS('
                SELECT p.`id_product` from `'._DB_PREFIX_.'product` p
                LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (p.id_product = cp.id_product)
                WHERE cp.`id_category` IN ('.pSQL($list).')');

            foreach ($products as $product) {
                $sql = 'INSERT IGNORE `'._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_EBAY.'` (`id_product`,`region`) values ('.
                    (int)$product['id_product'].', "'.pSQL($region).'")';

                if (!Db::getInstance()->execute($sql)) {
                    $pass = $pass && false;
                }
            }
        }

        return ($pass);
    }

    // Propagate Option
    /**
     * @param $region
     * @param $id_category
     * @param $field
     * @param $value
     *
     * @return bool
     */
    public static function propagateProductOptionToCategory($region, $id_category, $field, $value)
    {
        $pass = self::initProductOptions($region);

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

        $sql = 'UPDATE `'._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_EBAY.'` mpo, `'._DB_PREFIX_.'product` p SET '.
            $insert_statement.'
                WHERE p.`id_product` = mpo.`id_product` AND p.`id_category_default` = '.
            (int)$id_category.' and mpo.`region`="'.pSQL($region).'"';

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

    /**
     * @param $region
     * @param $field
     * @param $value
     *
     * @return bool
     */
    public static function propagateProductOptionToShop($region, $field, $value)
    {
        $pass = self::initProductOptions($region);

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

        $sql = 'UPDATE `'._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_EBAY.'` mpo, `'._DB_PREFIX_.'product` p SET '.
            $insert_statement.' WHERE p.`id_product`=mpo.`id_product` AND mpo.`region`="'.pSQL($region).'"';

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


    /**
     * @param $id_manufacturer
     * @param $region
     * @param $field
     * @param $value
     *
     * @return bool
     */
    public static function propagateProductOptionToManufacturer($id_manufacturer, $region, $field, $value)
    {
        $pass = self::initProductOptions($region);

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

        $sql = 'UPDATE `'._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_EBAY.'` mpo, `'._DB_PREFIX_.'product` p, `'.
            _DB_PREFIX_.'manufacturer` m SET '.
            $insert_statement.' WHERE p.`id_product` = mpo.`id_product` AND mpo.`region`="'.
            pSQL($region).'" AND p.`id_manufacturer`=m.`id_manufacturer` AND p.`id_manufacturer`='.
            (int)$id_manufacturer;

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


    /**
     * @param $id_supplier
     * @param $region
     * @param $field
     * @param $value
     *
     * @return bool
     */
    public static function propagateProductOptionToSupplier($id_supplier, $region, $field, $value)
    {
        $pass = self::initProductOptions($region);

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

        $sql = 'UPDATE `'._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_EBAY.'` mpo, `'._DB_PREFIX_.'product` p, `'
            ._DB_PREFIX_.'supplier` s SET '.$insert_statement.
            ' WHERE p.`id_product` = mpo.`id_product` AND mpo.`region`="'.pSQL($region).
            '" AND p.`id_supplier`=s.`id_supplier` AND p.`id_supplier`='.(int)$id_supplier;

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
}

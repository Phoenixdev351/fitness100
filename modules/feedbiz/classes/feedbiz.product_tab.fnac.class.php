<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Common-Services Co., Ltd.
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL SMC is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Common-Services Co., Ltd.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 * ...........................................................................
 *
 * @author    Alexandre D.
 * @copyright Copyright (c) 2011-2022 Common Services Co Ltd - 90/25 Sukhumvit 81 - 10260 Bangkok - Thailand
 * @license   Commercial license
 * Support by mail  :  support@feed.biz
 */

class FeedBizProductTabFnac extends FeedBizProductTab
{
    /** @var array */
    protected static $field_list = array();

    /** @var array */
    protected $marketplace_tabs = array();

    /** @var bool */
    protected $has_repricing    = false;

    /** @var bool */
    protected $has_europe       = false;


    /**
     * FeedBizProductTabFnac constructor.
     *
     * @param array $marketplace_tabs
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
     * Generate product tab for Fnac
     *
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
     * @return array
     */
    public static function getProductOptionFields()
    {
        static $option_fields = array();

        if (is_array($option_fields) && count($option_fields)) {
            return ($option_fields);
        }

        // Extra Fields / New Fields
        $option_fields_config = Configuration::get('FEEDBIZ_OPTION_FIELDS_FNAC', null, 0, 0);

        if ($option_fields_config && strpos($option_fields_config, ',')) {
            $option_fields = explode(',', $option_fields_config);

            if (!is_array($option_fields) || !count($option_fields)) {
                $option_fields = array();
            }
        }

        return $option_fields;
    }

    /**
     * @return array
     */
    public function countrySelector()
    {
        $marketplaces = array();

        foreach (Feedbiz::$fnac_regions as $domain => $region) {
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
            $marketplaces[$region]['name'] = sprintf('Fnac %s', FeedbizTools::fnacRegionToDomain($region));
            $marketplaces[$region]['region'] = $region;
            $marketplaces[$region]['image'] = $this->images.'geo_flags_web2/flag_'.$flag.'_64px.png';

            if ($lang) {
                $marketplaces[$region]['lang_flag'] = $this->images.'geo_flags_web2/flag_'.$lang.'_32px.png';
                $marketplaces[$region]['lang_iso_code'] = $lang;
            }
        }

        return $marketplaces;
    }

    /**
     * @param Product $product
     * @param array $combinations
     *
     * @return array
     */
    public function productOptions($product, $combinations)
    {
        $view_params = array();

        $id_lang = $this->context->language->id;

        foreach (Feedbiz::$fnac_regions as $domain => $region) {
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

        return $view_params;
    }

    /**
     * @param int $id_product
     * @param int|null $id_product_attribute
     * @param string|null $region
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
            $sql_region = ' AND `region`="'.pSQL($region).'" ';
        } else {
            $sql_region = '';
        }

        if ($id_product_attribute !== null) {
            $sql_id_product_attribute = ' AND `id_product_attribute` = '.(int)$id_product_attribute;
        } else {
            $sql_id_product_attribute = ' AND `id_product_attribute` = 0';
        }

        $sql = 'SELECT *
                FROM `'._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_FNAC.'` p
                WHERE `id_product` = '.(int)$id_product.$sql_id_product_attribute.$sql_region;

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
     * @param string $region
     * @param array $defaults
     *
     * @return array
     */
    public function productOptionsDetails($region, $defaults)
    {
        $view_params = array();

        if (in_array('price', self::$field_list)) {
            $view_params['extra_price'] = array(
                'default' => isset($defaults['price']) ? $defaults['price'] : null,
                'region' => $region
            );
        }

        if (in_array('disable', self::$field_list)) {
            $view_params['unavailable'] = array(
                'checked' => isset($defaults['disable']) && (bool)$defaults['disable'] ? 'checked="checked"' : '',
                'region' => $region
            );
        }

        if (in_array('force', self::$field_list)) {
            $view_params['force_in_stock'] = array(
                'default' => isset($defaults['force']) && is_numeric($defaults['force']) && $defaults['force'] > 0 ?
                    (int)$defaults['force'] : '',
                'region' => $region
            );
        }

        if (in_array('shipping', self::$field_list)) {
            $view_params['shipping_overrides'] = array(
                'default' =>
                    isset($defaults['shipping']) && (int)$defaults['shipping'] > 0 ? (float)$defaults['shipping'] : '',
                'region' => $region
            );
        }

        if (in_array('logistics_class', self::$field_list)) {
            $view_params['logistics_class'] = array(
                'default' =>
                    isset($defaults['logistics_class']) ? $defaults['logistics_class'] : null,
                'region' => $region
            );
        }

        return ($view_params);
    }

    /**
     * @param int $id_product
     * @param string $region
     * @param array $options
     * @param int|null $id_product_attribute
     *
     * @return bool
     */
    public static function setProductOptions($id_product, $region, $options, $id_product_attribute = null)
    {
        $options['id_product'] = isset($options['id_product']) && $options['id_product'] ?
            $options['id_product'] : (int)$id_product;

        $options['region'] = isset($options['region']) && $options['region'] ? $options['region'] : $region;

        $options['id_product_attribute'] = isset($options['id_product_attribute']) && $options['id_product_attribute'] ?
            $options['id_product_attribute'] : (int)$id_product_attribute;

        foreach ($options as $column => $value) {
            if ((empty($value)|| $value*1==0) && in_array($column, array('price'))) {
                $options[$column] = 'NULL';
            } elseif (is_numeric($value)) {
                if (is_float($value)) {
                    $options[$column] = (float)$value;
                } elseif (is_int($value)) {
                    $options[$column] = (int)$value;
                }
            } elseif ($value === null && !in_array($column, array('force', 'clogistique'))) {
                $options[$column] = 'NULL';
            } elseif (empty($value) && in_array($column, array('price'))) {
                $options[$column] = 'NULL';
            } elseif ($value === null) {
                $options[$column] = 0;
            } elseif (is_string($value)) {
                $options[$column] = sprintf('"%s"', pSQL(trim($value)));
            }
        }

        $sql = 'REPLACE INTO `'._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_FNAC.'`
            (`'.pSQL(implode('`, `', array_keys($options))).'`)
            VALUES ('.implode(', ', $options).')';

        $rq = Db::getInstance()->execute($sql);

        if (Feedbiz::$debug_mode) {
            echo "<pre>\n";
            printf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql);
            print_r($rq);
            echo "</pre>\n";
        }
        FeedBizProduct::updateProductDate($options['id_product']);

        return ($rq);
    }

    /**
     * Propagate option
     *
     * @param string $region
     * @param int $id_category
     * @param string $field
     * @param int|string $value
     * @return bool
     */
    public static function propagateProductOptionToCategory($region, $id_category, $field, $value)
    {
        $pass = true;

        if (!$id_category) {
            $product = new Product((int)Tools::getValue('id_product'));

            if (!Validate::isLoadedObject($product)) {
                echo 'Unable to load Product ID '.Tools::getValue('id_product');
                return false;
            }

            $id_category = $product->id_category_default;
        }

        if (!in_array($id_category, FeedbizConfiguration::get('FEEDBIZ_CATEGORIES'))) {
            return true;
        }

        if (is_numeric($value)) {
            if (is_float($value)) {
                $value = (float)$value;
            } elseif (is_int($value)) {
                $value = (int)$value;
            }
        } elseif (!$value && !in_array($field, array('force'))) {
            $value = 'NULL';
        } elseif (!$value) {
            $value = 0;
        } elseif (is_string($value)) {
            $value = sprintf('"%s"', pSQL(trim($value)));
        }

        $id_products_category = FeedbizTools::arrayColumn(Db::getInstance()->executes(
            'SELECT `id_product`
            FROM `'._DB_PREFIX_.'product`
            WHERE `id_category_default` = '.(int)$id_category
        ), 'id_product');

        $sql = 'INSERT INTO `'._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_FNAC.'`
        (`id_product`, `region`, `id_product_attribute`, `'.pSQL($field).'`) VALUES';

        foreach ($id_products_category as $id_product) {
            $sql .= ' ('.(int)$id_product.', "'.pSQL($region).'", 0, '.pSQL($value).'),';
        }

        $sql = rtrim(trim($sql), ',').' ON DUPLICATE KEY UPDATE `'.pSQL($field).'` = '.pSQL($value);

        if (!$rq = Db::getInstance()->execute($sql)) {
            $pass = false;
        }

        if (Feedbiz::$debug_mode) {
            echo "<pre>\n";
            printf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql);
            print_r($rq);
            echo "</pre>\n";
        }

        return $pass;
    }

    /**
     * @param string $region
     * @param string $field
     * @param int|string $value
     *
     * @return bool
     */
    public static function propagateProductOptionToShop($region, $field, $value)
    {
        $pass = true;
        $categories = FeedbizConfiguration::get('FEEDBIZ_CATEGORIES');

        if (is_numeric($value)) {
            if (is_float($value)) {
                $value = (float)$value;
            } elseif (is_int($value)) {
                $value = (int)$value;
            }
        } elseif (!$value && !in_array($field, array('force'))) {
            $value = 'NULL';
        } elseif (!$value) {
            $value = 0;
        } elseif (is_string($value)) {
            $value = sprintf('"%s"', pSQL(trim($value)));
        }

        $id_products = FeedbizTools::arrayColumn(Db::getInstance()->executes(
            'SELECT `id_product`
            FROM `'._DB_PREFIX_.'product`
            WHERE `id_category_default` IN ('.implode(', ', array_map('intval', $categories)).')'
        ), 'id_product');

        $id_products_chunk = array_chunk($id_products, 500);

        foreach ($id_products_chunk as $id_prds) {
            $sql = 'INSERT INTO `'._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_FNAC.'`
                (`id_product`, `region`, `id_product_attribute`, `'.$field.'`) VALUES';

            foreach ($id_prds as $id_product) {
                $sql .= ' ('.(int)$id_product.', "'.pSQL($region).'", 0, '.$value.'),';
            }

            $sql = rtrim(trim($sql), ',').' ON DUPLICATE KEY UPDATE `'.$field.'` = '.$value;

            if (!$rq = Db::getInstance()->execute($sql)) {
                $pass &= false;
            }

            if (Feedbiz::$debug_mode) {
                echo "<pre>\n";
                printf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql);
                print_r($rq);
                echo "</pre>\n";
            }
        }

        return $pass;
    }


    /**
     * @param int $id_manufacturer
     * @param string $region
     * @param string $field
     * @param int|string $value
     *
     * @return bool
     */
    public static function propagateProductOptionToManufacturer($id_manufacturer, $region, $field, $value)
    {
        $pass = true;

        if (is_numeric($value)) {
            if (is_float($value)) {
                $value = (float)$value;
            } elseif (is_int($value)) {
                $value = (int)$value;
            }
        } elseif (!$value && !in_array($field, array('force'))) {
            $value = 'NULL';
        } elseif (!$value) {
            $value = 0;
        } elseif (is_string($value)) {
            $value = sprintf('"%s"', pSQL(trim($value)));
        }

        $id_products = FeedbizTools::arrayColumn(Db::getInstance()->executes(
            'SELECT `id_product`
            FROM `'._DB_PREFIX_.'product`
            WHERE `id_manufacturer` = '.(int)$id_manufacturer
        ), 'id_product');

        $sql = 'INSERT INTO `'._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_FNAC.'`
        (`id_product`, `region`, `id_product_attribute`, `'.pSQL($field).'`) VALUES';

        foreach ($id_products as $id_product) {
            $sql .= ' ('.(int)$id_product.', "'.pSQL($region).'", 0, '.$value.'),';
        }

        $sql = rtrim(trim($sql), ',').' ON DUPLICATE KEY UPDATE `'.$field.'` = '.$value;

        if (!$rq = Db::getInstance()->execute($sql)) {
            $pass = false;
        }

        if (Feedbiz::$debug_mode) {
            echo "<pre>\n";
            printf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql);
            print_r($rq);
            echo "</pre>\n";
        }

        return $pass;
    }


    /**
     * @param int $id_supplier
     * @param string $region
     * @param string $field
     * @param int|string $value
     *
     * @return bool
     */
    public static function propagateProductOptionToSupplier($id_supplier, $region, $field, $value)
    {
        $pass = true;

        if (is_numeric($value)) {
            if (is_float($value)) {
                $value = (float)$value;
            } elseif (is_int($value)) {
                $value = (int)$value;
            }
        } elseif (!$value && !in_array($field, array('force'))) {
            $value = 'NULL';
        } elseif (!$value) {
            $value = 0;
        } elseif (is_string($value)) {
            $value = sprintf('"%s"', pSQL(trim($value)));
        }

        $id_products = FeedbizTools::arrayColumn(Db::getInstance()->executes(
            'SELECT `id_product`
            FROM `'._DB_PREFIX_.'product`
            WHERE `id_supplier` = '.(int)$id_supplier
        ), 'id_product');

        $sql = 'INSERT INTO `'._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_FNAC.'`
        (`id_product`, `region`, `id_product_attribute`, `'.pSQL($field).'`) VALUES';

        foreach ($id_products as $id_product) {
            $sql .= ' ('.(int)$id_product.', "'.pSQL($region).'", 0, '.$value.'),';
        }

        $sql = rtrim(trim($sql), ',').' ON DUPLICATE KEY UPDATE `'.$field.'` = '.$value;

        if (!$rq = Db::getInstance()->execute($sql)) {
            $pass = false;
        }

        if (Feedbiz::$debug_mode) {
            echo "<pre>\n";
            printf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql);
            print_r($rq);
            echo "</pre>\n";
        }

        return $pass;
    }

    /**
     * @param int $id_product
     * @param array $options
     * @param string $region
     * @param int|null $id_product_attribute
     * @return bool
     */
    public static function updateProductOptions($id_product, $options, $region, $id_product_attribute = null)
    {
        $sql = '';

        // check if exists
        if (self::getProductOptions($id_product, $id_product_attribute, $region)) {
            foreach ($options as $field => $value) {
                $field = sprintf('`%s`', Tools::strtolower($field));

                if (is_numeric($value)) {
                    if (is_float($value)) {
                        $sql .= $field.' = '.(float)$value.', ';
                    } else {
                        $sql .= $field.' = '.(int)$value.', ';
                    }
                } elseif (!$value && !in_array($field, array('force'))) {
                    $sql .= $field.' = NULL'.', ';
                } elseif (!$value) {
                    $sql .= $field.' = 0'.', ';
                } elseif (is_string($value)) {
                    $sql .= $field.' = '.sprintf('"%s"', pSQL(trim($value))).', ';
                }
            }

            $sql_update = 'UPDATE `'._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_FNAC.'` SET '.rtrim(trim($sql), ',').'
            WHERE `id_product` = '.(int)$id_product.'
            AND region = "'.pSQL($region).'" '.
                (isset($id_product_attribute) ? ' AND `id_product_attribute` = '.(int)$id_product_attribute : '0');

            $rq = Db::getInstance()->execute($sql_update);

            if (FeedBiz::$debug_mode) {
                echo "<pre>\n";
                printf("%s(%d): SQL - '%s'\n", basename(__FILE__), __LINE__, $sql_update);
                print_r($rq);
                echo "</pre>\n";
            }
            FeedBizProduct::updateProductDate($id_product);

            return $rq;
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

        $sql = 'DELETE FROM `'._DB_PREFIX_.Feedbiz::TABLE_FEEDBIZ_FNAC.'` WHERE `id_product` ='.(int)$id_product.
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
}

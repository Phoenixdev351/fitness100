<?php
/**
*
* NOTICE OF LICENSE
*
* This product is licensed for one customer to use on one installation (test stores and multishop included).
* Site developer has the right to modify this module to suit their needs, but can not redistribute the module in
* whole or in part. Any other use of this module constitutes a violation of the user agreement.
*
* DISCLAIMER
*
* NO WARRANTIES OF DATA SAFETY OR MODULE SECURITY
* ARE EXPRESSED OR IMPLIED. USE THIS MODULE IN ACCORDANCE
* WITH YOUR MERCHANT AGREEMENT, KNOWING THAT VIOLATIONS OF
* PCI COMPLIANCY OR A DATA BREACH CAN COST THOUSANDS OF DOLLARS
* IN FINES AND DAMAGE A STORES REPUTATION. USE AT YOUR OWN RISK.
*
*  @author    idnovate.com <info@idnovate.com>
*  @copyright 2020 idnovate.com
*  @license   See above
*/

class AdvancedPopup extends ObjectModel
{
    public $id_advancedpopup;
    public $id_shop;
    public $name;
    public $date_init;
    public $date_end;
    public $schedule;
    public $css_class;
    public $css;
    public $content;
    public $color_background;
    public $image_background;
    public $image;
    public $image_link;
    public $image_link_target;
    public $secs_to_display;
    public $secs_to_display_cart;
    public $secs_to_close;
    public $dont_display_again;
    public $priority = 0;
    public $back_opacity_value = 0.5;
    public $popup_height;
    public $popup_width;
    public $popup_padding;
    public $locked;
    public $responsive_min;
    public $responsive_max;
    public $show_customer_newsletter;
    public $show_customer_not_newsletter;
    public $show_on_view_page_nbr;
    public $show_every_nbr_hours;
    public $nb_products;
    public $nb_products_comparator;
    public $display_on_load;
    public $display_after_cart;
    public $display_on_exit;
    public $display_on_click;
    public $display_on_click_selector;
    public $controller_exceptions = '';
    public $groups = '';
    public $zones = '';
    public $countries = '';
    public $categories = '';
    public $categories_selected = '';
    public $manufacturers = '';
    public $products = '';
    public $genders = '';
    public $customers = '';
    public $suppliers = '';
    public $cms = '';
    public $languages = '';
    public $attributes;
    public $features;
    public $close_on_background;
    public $blur_background;
    public $open_effect;
    public $position = 5;
    public $cart_amount;
    public $cart_amount_from;
    public $cart_amount_to;
    public $display_url_string;
    public $display_referrer_string;
    public $display_ip_string;
    public $display_mobile = 1;
    public $display_tablet = 1;
    public $display_desktop = 1;
    public $product_stock;
    public $product_stock_from;
    public $product_stock_to;
    public $active;
    public $date_add;
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'advancedpopup',
        'primary' => 'id_advancedpopup',
        'multilang' => true,
        'fields' => array(
            'id_shop' =>                        array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false),
            'name' =>                           array('type' => self::TYPE_STRING, 'size' => 150, 'required' => true),
            'date_init' =>                      array('type' => self::TYPE_DATE),
            'date_end' =>                       array('type' => self::TYPE_DATE),
            'schedule' =>                       array('type' => self::TYPE_STRING),
            'css_class' =>                      array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isAnything'),
            'css' =>                            array('type' => self::TYPE_HTML, 'size' => 65534, 'lang' => true, 'validate' => 'isAnything'),
            'content' =>                        array('type' => self::TYPE_HTML, 'size' => 65534, 'lang' => true, 'validate' => 'isAnything'),
            'color_background' =>               array('type' => self::TYPE_STRING),
            'image_background' =>               array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isAnything'),
            'image' =>                          array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isAnything'),
            'image_link' =>                     array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isAnything'),
            'image_link_target' =>              array('type' => self::TYPE_STRING),
            'secs_to_display' =>                array('type' => self::TYPE_STRING),
            'secs_to_display_cart' =>           array('type' => self::TYPE_STRING),
            'secs_to_close' =>                  array('type' => self::TYPE_STRING),
            'dont_display_again' =>             array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'priority' =>                       array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
            'back_opacity_value' =>             array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'popup_height' =>                   array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isAnything'),
            'popup_width' =>                    array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isAnything'),
            'popup_padding' =>                  array('type' => self::TYPE_INT, 'validate' => 'isInt', 'lang' => true, 'validate' => 'isAnything'),
            'locked' =>                         array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'responsive_min' =>                 array('type' => self::TYPE_INT, 'validate' => 'isInt', 'lang' => true, 'validate' => 'isAnything'),
            'responsive_max' =>                 array('type' => self::TYPE_INT, 'validate' => 'isInt', 'lang' => true, 'validate' => 'isAnything'),
            'show_customer_newsletter' =>       array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'show_customer_not_newsletter' =>   array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'show_on_view_page_nbr' =>          array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'show_every_nbr_hours' =>           array('type' => self::TYPE_STRING),
            'nb_products' =>                    array('type' => self::TYPE_STRING),
            'nb_products_comparator' =>         array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'display_on_load' =>                array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'display_after_cart' =>             array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'display_on_exit' =>                array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'display_on_click' =>               array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'display_on_click_selector' =>      array('type' => self::TYPE_STRING, 'size' => 150),
            'controller_exceptions' =>          array('type' => self::TYPE_STRING, 'size' => 65534),
            'groups' =>                         array('type' => self::TYPE_STRING),
            'countries' =>                      array('type' => self::TYPE_STRING),
            'products' =>                       array('type' => self::TYPE_STRING),
            'genders' =>                        array('type' => self::TYPE_STRING),
            'customers' =>                      array('type' => self::TYPE_STRING),
            'zones' =>                          array('type' => self::TYPE_STRING),
            'categories' =>                     array('type' => self::TYPE_STRING),
            'categories_selected' =>            array('type' => self::TYPE_STRING),
            'manufacturers' =>                  array('type' => self::TYPE_STRING),
            'suppliers' =>                      array('type' => self::TYPE_STRING),
            'cms' =>                            array('type' => self::TYPE_STRING),
            'languages' =>                      array('type' => self::TYPE_STRING),
            'attributes' =>                     array('type' => self::TYPE_STRING),
            'features' =>                       array('type' => self::TYPE_STRING),
            'close_on_background' =>            array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'blur_background' =>                array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'open_effect' =>                    array('type' => self::TYPE_STRING),
            'position' =>                       array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'cart_amount' =>                    array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'cart_amount_from' =>               array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
            'cart_amount_to' =>                 array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
            'display_url_string' =>             array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isAnything'),
            'display_referrer_string' =>        array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isAnything'),
            'display_ip_string' =>              array('type' => self::TYPE_STRING),
            'display_mobile' =>                 array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'display_tablet' =>                 array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'display_desktop' =>                array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'product_stock' =>                  array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'product_stock_from' =>             array('type' => self::TYPE_NOTHING, 'validate' => 'isInt'),
            'product_stock_to' =>               array('type' => self::TYPE_NOTHING, 'validate' => 'isInt'),
            'active' =>                         array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'date_add' =>                       array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
            'date_upd' =>                       array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false)
        ),
    );

    public function __construct($id = null, $id_lang = null)
    {
        $this->context = Context::getContext();

        parent::__construct($id, $id_lang);

        if (!$id) {
            $this->date_init = date('Y-m-d H:i:s');
        }
    }

    public function add($autodate = true, $null_values = false)
    {
        $this->id_shop = ($this->id_shop) ? $this->id_shop : Context::getContext()->shop->id;

        return parent::add($autodate, true);
    }

    public function save($null_values = false, $auto_date = true)
    {
        return parent::save(true, $auto_date);
    }

    public function update($null_values = false)
    {
        return parent::update(true);
    }

    public function toggleStatus()
    {
        parent::toggleStatus();

        return Db::getInstance()->execute('
            UPDATE `'._DB_PREFIX_.bqSQL($this->def['table']).'`
            SET `date_upd` = NOW()
            WHERE `'.bqSQL($this->def['primary']).'` = '.(int)$this->id);
    }

    public function delete()
    {
        $languages = Language::getLanguages(false);
        foreach (array('image', 'image_background') as $type) {
            foreach ($languages as $language) {
                $image = $this->{$type}[(int)$language['id_lang']];
                if ($image) {
                    AdvancedPopupCreator::deleteImage(_PS_MODULE_DIR_.AdvancedPopupCreator::$image_dir.$image);
                }
            }
        }

        return parent::delete();
    }

    public function getPopups($preview = false)
    {
        $sql = 'SELECT *
            FROM `'._DB_PREFIX_.$this->def['table'].'`
            INNER JOIN `'. _DB_PREFIX_.$this->def['table'].'_lang` ON `'._DB_PREFIX_.$this->def['table'].'`.`id_advancedpopup` = `'._DB_PREFIX_.$this->def['table']. '_lang`.`id_advancedpopup`
            WHERE `id_shop` = '.(int)$this->context->shop->id.' AND `id_lang` = '.(int)$this->context->language->id;

        // Preview is the only one who asks for an specific popup, so no need to check filter neither mark it as seen
        if ($preview) {
            return Db::getInstance()->executeS($sql);
        }

        $sql .= ' AND `active` = 1
            AND (`date_init` <= "'.date("Y-m-d H:i:s"). '" OR `date_init` = "'.date('Y-m-d H:i:s', 0).'")
            AND (`date_end` >= "'.date("Y-m-d H:i:s").'" OR `date_end` = "'.date('Y-m-d H:i:s', 0).'")
            AND (`customers` = ""
                OR `customers` IS NULL
                OR FIND_IN_SET("'.(int)$this->context->customer->id.'", `customers`))
            AND (`languages` = ""
                OR `languages` IS NULL
                OR FIND_IN_SET("'.(int)$this->context->language->id.'", `languages`))
            AND (`display_ip_string` = ""
                OR `display_ip_string` IS NULL
                OR FIND_IN_SET("'.Tools::getRemoteAddr().'", `display_ip_string`))
            ';

        if ((int)$this->context->customer->id_gender) {
             $sql .= ' AND (`genders` = ""
                OR `genders` IS NULL
                OR FIND_IN_SET("'.(int)$this->context->customer->id_gender.'", `genders`))';
        } else {
            $sql .= ' AND (`genders` = ""
                OR `genders` IS NULL)';
        }

        $customerGroups = Customer::getGroupsStatic((int)$this->context->customer->id);
        $sql_groups = ' AND (`groups` = ""
            OR `groups` IS NULL ';
        foreach ($customerGroups as $customerGroup) {
            $sql_groups .= ' OR FIND_IN_SET('.$customerGroup.', `groups`)';
        }
        $sql_groups .= ')';
        $sql .= $sql_groups;

        if (version_compare(_PS_VERSION_, '1.6.0.11', '<')) {
            require_once(_PS_TOOL_DIR_.'mobile_Detect/Mobile_Detect.php');
            $this->mobile_detect = new Mobile_Detect();

            if ($this->mobile_detect->isMobile()) {
                $sql .= ' AND `display_mobile` = 1';
            }

            if ($this->mobile_detect->isTablet()) {
                $sql .= ' AND `display_tablet` = 1';
            }

            if (!$this->mobile_detect->isTablet() && !$this->mobile_detect->isMobile()) {
                $sql .= ' AND `display_desktop` = 1';
            }
        } else {
            if ($this->context->isMobile()) {
                $sql .= ' AND `display_mobile` = 1';
            }

            if ($this->context->isTablet()) {
                $sql .= ' AND `display_tablet` = 1';
            }

            if (!$this->context->isTablet() && !$this->context->isMobile()) {
                $sql .= ' AND `display_desktop` = 1';
            }
        }

        $controller = AdvancedPopupCreator::getController();

        if ($controller === 'cms') {
            $sql .= ' AND (`cms` = ""
                OR `cms` IS NULL
                OR FIND_IN_SET("'.(int)Tools::getValue('id_cms').'", `cms`))';
        } else {
            $sql .= ' AND (`cms` = ""
                OR `cms` IS NULL)';
        }

        $sql .= ' AND (`controller_exceptions` = ""
            OR `controller_exceptions` IS NULL
            OR FIND_IN_SET("'.$controller.'", `controller_exceptions`))';

        if ((int)Tools::getValue('id_product')) {
            $sql .= ' AND (`display_after_cart` = 1
                OR (`products` = ""
                OR `products` IS NULL
                OR FIND_IN_SET('.(int)Tools::getValue('id_product').', `products`)))';
        } else {
            $sql .= ' AND (`display_after_cart` = 1
                OR `products` = ""
                OR `products` IS NULL)';
        }

        if ($controller === 'product') {
            $laProductCategories = Product::getProductCategories((int)Tools::getValue('id_product'));
            $sql_groups = ' AND (`categories` = ""
                OR `categories` IS NULL ';
            foreach ($laProductCategories as $laProductCategory) {
                $sql_groups .= ' OR FIND_IN_SET('.$laProductCategory.', `categories`)';
            }
            $sql_groups .= ')';
            $sql .= $sql_groups;
        } elseif ($controller === 'category') {
            $sql .= ' AND (`display_after_cart` = 1
                OR (`categories` = ""
                OR `categories` IS NULL
                OR FIND_IN_SET("'.(int)Tools::getValue('id_category').'", `categories`)))';
        } else {
            $sql .= ' AND (`display_after_cart` = 1
                OR `categories` = ""
                OR `categories` IS NULL)';
        }

        if ($controller === 'product') {
            $product = new Product((int)Tools::getValue('id_product'));
        }

        if ($controller === 'product') {
            $sql .= ' AND (`display_after_cart` = 1
                OR (`manufacturers` = ""
                OR `manufacturers` IS NULL
                OR FIND_IN_SET("'.(int)$product->id_manufacturer.'", `manufacturers`)))';
        } elseif ($controller === 'manufacturer') {
            $sql .= ' AND (`display_after_cart` = 1
                OR (`manufacturers` = ""
                OR `manufacturers` IS NULL
                OR FIND_IN_SET("'.(int)Tools::getValue('id_manufacturer').'", `manufacturers`)))';
        } else {
            $sql .= ' AND (`display_after_cart` = 1
                OR `manufacturers` = ""
                OR `manufacturers` IS NULL)';
        }

        if ($controller === 'product') {
            $sql .= ' AND (`display_after_cart` = 1
                OR (`suppliers` = ""
                OR `suppliers` IS NULL
                OR FIND_IN_SET("'.(int)$product->id_supplier.'", `suppliers`)))';
        } elseif ($controller === 'supplier') {
            $sql .= ' AND (`display_after_cart` = 1
                OR (`suppliers` = ""
                OR `suppliers` IS NULL
                OR FIND_IN_SET("'.(int)Tools::getValue('id_supplier').'", `suppliers`)))';
        } else {
            $sql .= ' AND (`display_after_cart` = 1
                OR `suppliers` = ""
                OR `suppliers` IS NULL)';
        }

        $sql .= ' ORDER BY `priority` ASC;';

        $popups = Db::getInstance()->executeS($sql);

        $validPopups = array();

        // Check the page views for user
        $laPopupsVisited = $this->getApcCookiePopups();

        foreach ($popups as $popup) {
            /************* Pagenbr *************/
            $liShowOnPageView = (int)$popup['show_on_view_page_nbr'];
            $liPopupID = (int)$popup['id_advancedpopup'];

            // If 0 or 1, display the popup always
            if ((int)$liShowOnPageView > 1) {
                if (empty($laPopupsVisited)
                    || !isset($laPopupsVisited[$liPopupID])
                    || !isset($laPopupsVisited[$liPopupID]['visits'])) {
                    continue;
                }

                if ($laPopupsVisited[$liPopupID]['visits'] < $liShowOnPageView) {
                    continue;
                }
            }

            /************* Hoursnbr *************/
            // Run filter for without minutes defined
            if ((int)$popup['show_every_nbr_hours'] > 0) {
                $liNow = time();

                $liPopupID = (int)$popup['id_advancedpopup'];
                $liMinutesToShow = (int)$popup['show_every_nbr_hours'] * 60;

                if (!empty($laPopupsVisited)
                    && isset($laPopupsVisited[$liPopupID]['last_displayed'])) {
                    $liShowOnTime = $laPopupsVisited[$liPopupID]['last_displayed'] + $liMinutesToShow;

                    if ($liNow < $liShowOnTime) {
                        continue;
                    }
                }
            }

            // Customer newsletter
            if ((int)$this->context->customer->id && ((int)$popup['show_customer_newsletter'] || (int)$popup['show_customer_not_newsletter'])) {
                $loCustomer = new Customer((int)$this->context->customer->id);

                if ((int)$popup['show_customer_newsletter'] && !(bool)$loCustomer->newsletter) {
                    continue;
                }

                if ((int)$popup['show_customer_not_newsletter'] && (bool)$loCustomer->newsletter) {
                    continue;
                }
            }

            //Don't show this message again
            if (isset($laPopupsVisited[$liPopupID]['last_displayed']) &&
                $laPopupsVisited[$liPopupID]['last_displayed'] == (PHP_INT_MAX-1)) {
                continue;
            }

            // Schedule
            if (!self::isShowableBySchedule($popup['schedule'])) {
                continue;
            }

            // Filter by attributes
            $attributesSelected = explode(',', $popup['attributes']);
            if (array_filter($attributesSelected)) {
                if ($controller === 'product') {
                    //Get attributes group
                    $attributeGroups = array();
                    foreach ($attributesSelected as $attributeSelected) {
                        $attribute = new Attribute((int)$attributeSelected);
                        $attributeGroups[$attribute->id_attribute_group] = true;
                    }

                    $hasAttributeGroups = array();
                    $product = new Product(Tools::getValue('id_product'));
                    $productAttributeCombinations = $product->getAttributeCombinations();
                    foreach ($productAttributeCombinations as $productAttributeCombination) {
                        if (in_array((int)$productAttributeCombination['id_attribute'], $attributesSelected)) {
                            $hasAttributeGroups[(int)$productAttributeCombination['id_attribute_group']] = true;
                        }
                    }

                    if ($hasAttributeGroups !== $attributeGroups) {
                        continue;
                    }
                } else {
                    continue;
                }
            }

            //Filter by feature
            $featuresSelected = explode(',', $popup['features']);
            if (array_filter($featuresSelected)) {
                if ($controller === 'product') {
                    //Get features group
                    $featureGroups = array();
                    foreach ($featuresSelected as $featureSelected) {
                        $feature = new FeatureValue((int)$featureSelected);
                        $featureGroups[$feature->id_feature] = true;
                    }

                    $hasFeatureGroups = array();
                    $productFeatures = Product::getFeaturesStatic((int)Tools::getValue('id_product'));
                    foreach ($productFeatures as $productFeature) {
                        if (in_array($productFeature['id_feature_value'], $featuresSelected)) {
                            $hasFeatureGroups[(int)$productFeature['id_feature']] = true;
                        }
                    }

                    if ($hasFeatureGroups !== $featureGroups) {
                        continue;
                    }
                } else {
                    continue;
                }
            }

            if ($controller === 'product') {
                if ($popup['product_stock']) {
                    $stock = (int)Product::getQuantity((int)Tools::getValue('id_product'));
                    $productStockFrom = is_null($popup['product_stock_from']) ? PHP_INT_MIN : (int)$popup['product_stock_from'];
                    $productStockTo = is_null($popup['product_stock_to']) ? PHP_INT_MAX : (int)$popup['product_stock_to'];

                    if ($productStockFrom > $stock
                        || $productStockTo < $stock) {
                        continue;
                    }
                }
            }

            $validPopups[] = $popup;
        }

        return $validPopups;
    }

    public function getPopupToDisplay($availablePopups)
    {
        if (isset($this->context->cart) && isset($this->context->cart->id_address_delivery) && $this->context->cart->id_address_delivery != 0) {
            $zone = Address::getZoneById($this->context->cart->id_address_delivery);
        } else {
            $zone = Country::getIdZone($this->context->country->id);
        }

        $sql = 'SELECT *
            FROM `'._DB_PREFIX_.$this->def['table'].'`
            INNER JOIN `'. _DB_PREFIX_.$this->def['table'].'_lang` ON `'._DB_PREFIX_.$this->def['table'].'`.`id_advancedpopup` = `'._DB_PREFIX_.$this->def['table']. '_lang`.`id_advancedpopup`
            WHERE `id_shop` = '.(int)$this->context->shop->id.' AND `id_lang` = '.(int)$this->context->language->id
           .' AND '._DB_PREFIX_.$this->def['table'].'.'.$this->def['primary']. ' IN ('.$availablePopups.')'
           .' AND `active` = 1
            AND (`countries` = ""
                OR `countries` IS NULL
                OR FIND_IN_SET("'.(int)$this->context->country->id.'", `countries`))
            AND (`zones` = ""
                OR `zones` IS NULL
                OR FIND_IN_SET("'.(int)$zone.'", `zones`))
            AND (`responsive_min` = ""
                OR `responsive_min` IS NULL
                OR responsive_min > '.(int)Tools::getValue('responsiveWidth').')
            AND (`responsive_max` = ""
                OR `responsive_max` IS NULL
                OR responsive_max < '.(int)Tools::getValue('responsiveWidth').')
            AND (`display_url_string` = ""
                OR `display_url_string` IS NULL
                OR INSTR("'.urldecode(Tools::getValue('url')).'", `display_url_string`) > 0)
           AND (`display_referrer_string` = ""
                OR `display_referrer_string` IS NULL
                OR INSTR("'.urldecode(Tools::getValue('referrer')).'", `display_referrer_string`) > 0)';

        switch ((int)Tools::getValue('event')) {
            case 1:
                $sql .= ' AND `display_on_load` = 1';
                break;

            case 2:
                $sql .= ' AND `display_after_cart` = 1';
                break;

            case 3:
                $sql .= ' AND `display_on_exit` = 1';
                break;

            case 4:
                $sql .= ' AND `display_on_click` = 1';
                break;
        }

        if ((int)Tools::getValue('event') !== 2) {
            $controller = Tools::getValue('fromController');

            if ((int)Tools::getValue('id_product')) {
                $sql .= ' AND (`products` = ""
                    OR `products` IS NULL
                    OR FIND_IN_SET('.(int)Tools::getValue('id_product').', `products`))';
            } else {
                $sql .= ' AND (`products` = ""
                    OR `products` IS NULL)';
            }

            if ($controller === 'product') {
                $laProductCategories = Product::getProductCategories((int)Tools::getValue('id_product'));
                $sql_groups = ' AND (`categories` = ""
                    OR `categories` IS NULL ';
                foreach ($laProductCategories as $laProductCategory) {
                    $sql_groups .= ' OR FIND_IN_SET('.$laProductCategory.', `categories`)';
                }
                $sql_groups .= ')';
                $sql .= $sql_groups;
            } elseif ($controller === 'category') {
                $sql .= ' AND (`categories` = ""
                    OR `categories` IS NULL
                    OR FIND_IN_SET("'.(int)Tools::getValue('id_category').'", `categories`))';
            } else {
                $sql .= ' AND (`categories` = ""
                    OR `categories` IS NULL )';
            }

            if ($controller === 'product') {
                $product = new Product((int)Tools::getValue('id_product'));
            }

            if ($controller === 'product') {
                $sql .= ' AND (`manufacturers` = ""
                    OR `manufacturers` IS NULL
                    OR FIND_IN_SET("'.(int)$product->id_manufacturer.'", `manufacturers`))';
            } elseif ($controller === 'manufacturer') {
                $sql .= ' AND (`manufacturers` = ""
                    OR `manufacturers` IS NULL
                    OR FIND_IN_SET("'.(int)Tools::getValue('id_manufacturer').'", `manufacturers`))';
            } else {
                $sql .= ' AND (`manufacturers` = ""
                    OR `manufacturers` IS NULL)';
            }

            if ($controller === 'product') {
                $sql .= ' AND (`suppliers` = ""
                    OR `suppliers` IS NULL
                    OR FIND_IN_SET("'.(int)$product->id_supplier.'", `suppliers`))';
            } elseif ($controller === 'supplier') {
                $sql .= ' AND (`suppliers` = ""
                    OR `suppliers` IS NULL
                    OR FIND_IN_SET("'.(int)Tools::getValue('id_supplier').'", `suppliers`))';
            } else {
                $sql .= ' AND (`suppliers` = ""
                    OR `suppliers` IS NULL)';
            }
        } else {
            $product = new Product(Tools::getValue('id_product'));
            $sql .= ' AND (`products` = ""
                OR `products` IS NULL
                OR FIND_IN_SET('.(int)Tools::getValue('id_product').', `products`))';

            $laProductCategories = Product::getProductCategories((int)Tools::getValue('id_product'));
            $sql .= ' AND (`categories` = ""
                OR `categories` IS NULL ';
            foreach ($laProductCategories as $laProductCategory) {
                $sql .= ' OR FIND_IN_SET('.$laProductCategory.', `categories`)';
            }
            $sql .= ')';

            $sql .= ' AND (`manufacturers` = ""
                OR `manufacturers` IS NULL
                OR FIND_IN_SET("'.(int)$product->id_manufacturer.'", `manufacturers`))';
            $sql .= ' AND (`suppliers` = ""
                OR `suppliers` IS NULL
                OR FIND_IN_SET("'.(int)$product->id_supplier.'", `suppliers`))';
        }

        $sql .= ' ORDER BY `priority` ASC;';

        $popups = Db::getInstance()->executeS($sql);

        $laPopupsVisited = $this->getApcCookiePopups();

        $validPopups = array();

        $cartAmount = $this->context->cart->getOrderTotal();
        $liNow = time();

        foreach ($popups as $popup) {
            /************* Hoursnbr *************/
            // Run filter for without minutes defined
            if ((int)$popup['show_every_nbr_hours'] > 0) {
                $liMinutesToShow = (int)$popup['show_every_nbr_hours'] * 60;

                if (!empty($laPopupsVisited)
                    && isset($laPopupsVisited[(int)$popup['id_advancedpopup']]['last_displayed'])) {
                    $liShowOnTime = $laPopupsVisited[(int)$popup['id_advancedpopup']]['last_displayed'] + $liMinutesToShow;

                    if ($liNow < $liShowOnTime) {
                        continue;
                    }
                }
            }

            //Don't show this message again
            if (isset($laPopupsVisited[(int)$popup['id_advancedpopup']]['last_displayed']) &&
                $laPopupsVisited[(int)$popup['id_advancedpopup']]['last_displayed'] == (PHP_INT_MAX-1)) {
                continue;
            }

            /************* Cart amount *************/
            if ($popup['cart_amount']) {
                if ($popup['cart_amount_from']) {
                    $cartAmountFromConverted = AdvancedPopupCreator::convertPriceFull($popup['cart_amount_from'], null, $this->context->currency);
                    if ($cartAmount < $cartAmountFromConverted) {
                        continue;
                    }
                }

                if ($popup['cart_amount_to']) {
                    $cartAmountToConverted = AdvancedPopupCreator::convertPriceFull($popup['cart_amount_to'], null, $this->context->currency);
                    if ($cartAmount > $cartAmountToConverted) {
                        continue;
                    }
                }
            }

            /************* Number of products in the cart *************/
            if ($popup['nb_products']) {
                $productsInCart = 0;
                foreach ($this->context->cart->getProducts() as $cartProduct) {
                    //Check category
                    $productCategories = Product::getProductCategories($cartProduct['id_product']);

                    if ($popup['categories_selected']) {
                        foreach ($productCategories as $productCategory) {
                            if (in_array($productCategory, explode(',', $popup['categories_selected']))) {
                                $productsInCart += $cartProduct['cart_quantity'];
                            }
                        }
                    } else {
                        $productsInCart += $cartProduct['cart_quantity'];
                    }
                }

                switch ($popup['nb_products_comparator']) {
                    case 1:
                        if ($productsInCart <= $popup['nb_products']) {
                            continue 2;
                        }
                        break;

                    case 2:
                        if ($productsInCart != $popup['nb_products']) {
                            continue 2;
                        }
                        break;

                    case 3:
                        if ($productsInCart >= $popup['nb_products']) {
                            continue 2;
                        }
                        break;
                }
            }

            if ((int)Tools::getValue('event') != 1
                && (int)Tools::getValue('event') != 4) {
                $validPopups[] = $popup;
                return $validPopups;
            }

            //Check if popup is diplayed with different delay
            if ((int)Tools::getValue('event') == 1) {
                foreach ($validPopups as $validPopup) {
                    if ((int)$popup['secs_to_display'] == (int)$validPopup['secs_to_display']) {
                        continue;
                    }
                }
            }

            $validPopups[] = $popup;
        }

        return $validPopups;
    }

    public static function isShowableBySchedule($schedule)
    {
        $schedule = json_decode($schedule);
        $dayOfWeek = date('w') - 1;
        if ($dayOfWeek < 0) {
            $dayOfWeek = 6;
        }

        if (is_array($schedule)) {
            if (is_object($schedule[$dayOfWeek]) && $schedule[$dayOfWeek]->isActive === true) {
                if ($schedule[$dayOfWeek]->timeFrom <= date('H:i')
                    && $schedule[$dayOfWeek]->timeTill > date('H:i')) {
                    return true;
                }
                return false;
            }
            return false;
        }

        return true;
    }

    /**
     * Update the visits from passed popups on each loading.
     *
     * array $laPopupsVisited The popups with visits
     * array The popups with visits +1 on all
     */
    public function updateVisits()
    {
        $sql = 'SELECT *
            FROM `'._DB_PREFIX_.$this->def['table'].'`
            INNER JOIN `'. _DB_PREFIX_.$this->def['table'].'_lang` ON `'._DB_PREFIX_.$this->def['table'].'`.`id_advancedpopup` = `'._DB_PREFIX_.$this->def['table']. '_lang`.`id_advancedpopup`
            WHERE `id_shop` = '.(int)$this->context->shop->id.' AND `id_lang` = '.(int)$this->context->language->id;

        $sql .= ' AND `active` = 1
            AND (`date_init` <= "'.date("Y-m-d H:i:s"). '" OR `date_init` = "'.date('Y-m-d H:i:s', 0).'")
            AND (`date_end` >= "'.date("Y-m-d H:i:s").'" OR `date_end` = "'.date('Y-m-d H:i:s', 0).'")
            AND (`customers` = ""
                OR `customers` IS NULL
                OR FIND_IN_SET("'.(int)$this->context->customer->id.'", `customers`))
            AND (`languages` = ""
                OR `languages` IS NULL
                OR FIND_IN_SET("'.(int)$this->context->language->id.'", `languages`))
            AND (`display_ip_string` = ""
                OR `display_ip_string` IS NULL
                OR FIND_IN_SET("'.Tools::getRemoteAddr().'", `display_ip_string`))
            ';

        if ((int)$this->context->customer->id_gender) {
             $sql .= ' AND (`genders` = ""
                OR `genders` IS NULL
                OR FIND_IN_SET("'.(int)$this->context->customer->id_gender.'", `genders`))';
        } else {
            $sql .= ' AND (`genders` = ""
                OR `genders` IS NULL)';
        }

        $customerGroups = Customer::getGroupsStatic((int)$this->context->customer->id);
        $sql_groups = ' AND (`groups` = ""
            OR `groups` IS NULL ';
        foreach ($customerGroups as $customerGroup) {
            $sql_groups .= ' OR FIND_IN_SET('.$customerGroup.', `groups`)';
        }
        $sql_groups .= ')';
        $sql .= $sql_groups;

        if (version_compare(_PS_VERSION_, '1.6.0.11', '<')) {
            require_once(_PS_TOOL_DIR_.'mobile_Detect/Mobile_Detect.php');
            $this->mobile_detect = new Mobile_Detect();

            if ($this->mobile_detect->isMobile()) {
                $sql .= ' AND `display_mobile` = 1';
            }

            if ($this->mobile_detect->isTablet()) {
                $sql .= ' AND `display_tablet` = 1';
            }

            if (!$this->mobile_detect->isTablet() && !$this->mobile_detect->isMobile()) {
                $sql .= ' AND `display_desktop` = 1';
            }
        } else {
            if ($this->context->isMobile()) {
                $sql .= ' AND `display_mobile` = 1';
            }

            if ($this->context->isTablet()) {
                $sql .= ' AND `display_tablet` = 1';
            }

            if (!$this->context->isTablet() && !$this->context->isMobile()) {
                $sql .= ' AND `display_desktop` = 1';
            }
        }

        $controller = AdvancedPopupCreator::getController();

        if ($controller === 'cms') {
            $sql .= ' AND (`cms` = ""
                OR `cms` IS NULL
                OR FIND_IN_SET("'.(int)Tools::getValue('id_cms').'", `cms`))';
        } else {
            $sql .= ' AND (`cms` = ""
                OR `cms` IS NULL)';
        }

        $sql .= ' AND (`controller_exceptions` = ""
            OR `controller_exceptions` IS NULL
            OR FIND_IN_SET("'.$controller.'", `controller_exceptions`))';

        if ((int)Tools::getValue('id_product')) {
            $sql .= ' AND (`display_after_cart` = 1
                OR (`products` = ""
                    OR `products` IS NULL
                    OR FIND_IN_SET('.(int)Tools::getValue('id_product').', `products`)))';
        } else {
            $sql .= ' AND (`display_after_cart` = 1
                OR `products` = ""
                OR `products` IS NULL)';
        }

        if ($controller === 'product') {
            $laProductCategories = Product::getProductCategories((int)Tools::getValue('id_product'));
            $sql_groups = ' AND (`categories` = ""
                OR `categories` IS NULL ';
            foreach ($laProductCategories as $laProductCategory) {
                $sql_groups .= ' OR FIND_IN_SET('.$laProductCategory.', `categories`)';
            }
            $sql_groups .= ')';
            $sql .= $sql_groups;
        } elseif ($controller === 'category') {
            $sql .= ' AND (`display_after_cart` = 1
                OR (`categories` = ""
                    OR `categories` IS NULL
                    OR FIND_IN_SET("'.(int)Tools::getValue('id_category').'", `categories`)))';
        } else {
            $sql .= ' AND (`display_after_cart` = 1
                OR `categories` = ""
                OR `categories` IS NULL)';
        }

        if ($controller === 'product') {
            $product = new Product((int)Tools::getValue('id_product'));
        }

        if ($controller === 'product') {
            $sql .= ' AND (`display_after_cart` = 1
                OR (`manufacturers` = ""
                    OR `manufacturers` IS NULL
                    OR FIND_IN_SET("'.(int)$product->id_manufacturer.'", `manufacturers`)))';
        } elseif ($controller === 'manufacturer') {
            $sql .= ' AND (`display_after_cart` = 1
                OR (`manufacturers` = ""
                    OR `manufacturers` IS NULL
                    OR FIND_IN_SET("'.(int)Tools::getValue('id_manufacturer').'", `manufacturers`)))';
        } else {
            $sql .= ' AND (`display_after_cart` = 1
                OR `manufacturers` = ""
                OR `manufacturers` IS NULL)';
        }

        if ($controller === 'product') {
            $sql .= ' AND (`display_after_cart` = 1
                OR (`suppliers` = ""
                    OR `suppliers` IS NULL
                    OR FIND_IN_SET("'.(int)$product->id_supplier.'", `suppliers`)))';
        } elseif ($controller === 'supplier') {
            $sql .= ' AND (`display_after_cart` = 1
                OR (`suppliers` = ""
                    OR `suppliers` IS NULL
                    OR FIND_IN_SET("'.(int)Tools::getValue('id_supplier').'", `suppliers`)))';
        } else {
            $sql .= ' AND (`display_after_cart` = 1
                OR `suppliers` = ""
                OR `suppliers` IS NULL)';
        }

        $sql .= ' ORDER BY `priority` ASC;';

        $popups = Db::getInstance()->executeS($sql);

        if (!empty($popups)) {
            $laCookiePopupsVisited = $this->getApcCookiePopups();

            foreach ($popups as $popup) {
                if (isset($laCookiePopupsVisited[(int)$popup['id_advancedpopup']])
                    && isset($laCookiePopupsVisited[(int)$popup['id_advancedpopup']]['visits'])) {
                    $laCookiePopupsVisited[(int)$popup['id_advancedpopup']]['visits']++;
                } else {
                    $laCookiePopupsVisited[(int)$popup['id_advancedpopup']]['visits'] = 1;
                }
            }

            $this->setApcCookiePopups($laCookiePopupsVisited);
        }
    }

    public function getApcCookiePopups()
    {
        if (Tools::getValue('APC_COOKIE')) {
            return Tools::jsonDecode($_COOKIE['apc_popup'], true, 512, JSON_BIGINT_AS_STRING);
        }

        return Tools::jsonDecode($this->context->cookie->apc_popup, true, 512, JSON_BIGINT_AS_STRING);
    }

    public function setApcCookiePopups($value)
    {
        if (Tools::getValue('APC_COOKIE')) {
            setcookie('apc_popup', Tools::jsonEncode($value), time()+3600*24*(int)Configuration::get('PS_COOKIE_LIFETIME_FO'), "/");
            $_COOKIE['apc_popup'] = Tools::jsonEncode($value);
        }

        return $this->context->cookie->apc_popup = Tools::jsonEncode($value);
    }

     /**
     * Delete images associated with the object
     */
    public function deletePopupImage($id_language)
    {
        if (!$this->id || !$id_language) {
            return false;
        }

        $lsDestination = _PS_MODULE_DIR_.AdvancedPopupCreator::$image_dir.$this->image[(int)$id_language];

        if (file_exists($lsDestination) && !unlink($lsDestination)) {
            return false;
        }

        $this->image[(int)$id_language] = '';
        if (!$this->save()) {
            return false;
        }

        return true;
    }

    public static function getNbObjects()
    {
        $sql = 'SELECT COUNT(a.`id_advancedpopup`) AS nb
                FROM `'._DB_PREFIX_.'advancedpopup` a
                WHERE `id_shop` = '.(int)Context::getContext()->shop->id;

        return (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }
}

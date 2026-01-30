<?php
/**
 * Affiliates
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @author    FMM Modules
 * @copyright © Copyright 2021 - All right reserved
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @category  FMM Modules
 * @package   affiliates
 */

class AdminLevelsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table = 'affiliate_levels';
        $this->className = 'Levels';
        $this->identifier = 'id_affiliate_levels';
        $this->lang = false;
        $this->deleted = false;
        $this->bootstrap = true;

        parent::__construct();

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Referrals associated with selected customer(s) will also deleted.Delete selected items?')
            ),
        );

        $this->_select = 'a.id_affiliate_levels, a.is_tax, a.active,
        (CASE
            WHEN a.reward_type = 0 THEN "' . $this->l('Fixed') . '"
            WHEN a.reward_type = 1 THEN "' . $this->l('% of Total Order') . '"
            WHEN a.reward_type = 2 THEN "' . $this->l('Product Specific') . '"
            WHEN a.reward_type = 3 THEN "' . $this->l('Category Specific') . '"
        END) as type';
        $this->_use_found_rows = true;

        $this->fields_list = array(
            'id_affiliate_levels' => array(
                'title' => $this->l('ID'),
                'width' => 25,
            ),
            'type' => array(
                'type' => 'text',
                'title' => $this->l('Reward Type'),
                'align' => 'center',
                //'type' => 'price',
            ),
            'reward_value' => array(
                'title' => $this->l('Value'),
            ),
            'is_tax' => array(
                'title' => $this->l('Tax Incl.'),
                'active' => 'is_tax',
                'type' => 'bool',
                'align' => 'center',
                'orderby' => false,
            ),
            'level' => array(
                'title' => $this->l('Level'),
                'align' => 'center',
            ),
            'active' => array(
                'title' => $this->l('Status'),
                'width' => 70,
                'active' => 'active',
                'type' => 'bool',
                'align' => 'center',
                'orderby' => false,
            ),
        );
    }

    public function renderList()
    {
        $this->displayWarning($this->l('Note: You can add one rule per Affiliate Level.'));
        $this->displayWarning($this->l('Note: You can add total 4 rules as module offers 4 level affiliate system.'));
        // Adds an Edit button for each result
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        $menu = $this->getMenu();
        return $menu . parent::renderList();
    }

    public function initToolbar()
    {
        $nbr_levels = Levels::countLevels();
        $this->toolbar_title[] = $this->l('Order Reward');
        parent::initToolbar();
        if ($nbr_levels >= 4) {
            unset($this->toolbar_btn['new']);
        }
    }

    public function renderForm()
    {
        $obj = $this->loadObject(true);
        $back = Tools::safeOutput(Tools::getValue('back', ''));
        $btn_title = $this->l('Save');
        $form_title = (($id_affiliate_levels = (int) Tools::getValue('id_affiliate_levels')) == 0) ? $this->l('Add Order Reward') : $this->l('Edit Order Reward');
        if (empty($back)) {
            $back = self::$currentIndex . '&token=' . $this->token;
        }

        if ($id_affiliate_levels) {
            $btn_title = $this->l('Update');
            $form_title = $this->l('Edit Level');
        }

        $disabled = false;
        $type = (true === Tools::version_compare(_PS_VERSION_, '1.6.0.0', '<')) ? 'radio' : 'switch';
        if ($obj->id) {
            $disabled = true;
        }

        $affiliate_levels = array(
            0 => array('id' => 1, 'name' => $this->l('Level 1st')),
            1 => array('id' => 2, 'name' => $this->l('Level 2nd')),
            2 => array('id' => 3, 'name' => $this->l('Level 3rd')),
            3 => array('id' => 4, 'name' => $this->l('Level 4th')),
        );

        if (!$obj->id) {
            foreach ($affiliate_levels as $k => $lvl) {
                $level = (int) Levels::getAffiliateLevelExistance($lvl['id']);
                if ($level > 0) {
                    unset($affiliate_levels[$k]);
                }
            }
        }
        $default_currency = (int) Configuration::get('PS_CURRENCY_DEFAULT');
        $store_currency = new Currency($default_currency);
        $categories = Category::getSimpleCategories($this->context->language->id);
        $this->displayWarning($this->l('In case of Product specific and Category specific reward type please do use Global value
                                       as for all other products/categories not selected in specific selection area.'));
        $this->displayWarning($this->l('In case of Product specific and Category specific reward type you can also leave empty Global value
                                       field to disable reward for all other products/categories not selected in specific selection area.'));
        $this->fields_form = array(
            'tinymce' => true,
            'legend' => array(
                'title' => $form_title,
                'icon' => 'icon-list',
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Affiliate Level'),
                    'name' => 'level',
                    'disabled' => $disabled,
                    'required' => true,
                    'options' => array(
                        'query' => $affiliate_levels,
                        'id' => 'id',
                        'name' => 'name',
                    ),
                    'desc' => $this->l('Based on child-parent tree, like level 2nd has parent 1st and level 3rd has parent 2nd plus 1st so on....'),
                ),
                array(
                    'type' => $type,
                    'label' => $this->l('Status'),
                    'name' => 'active',
                    'required' => false,
                    'class' => 't',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ),
                    ),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Reward Type'),
                    'name' => 'reward_type',
                    'onchange' => 'rewardTrigger(this.value);',
                    'options' => array(
                        'query' => array(
                            array(
                                'id_option' => 0,
                                'name' => $this->l('Fixed'),
                            ),
                            array(
                                'id_option' => 1,
                                'name' => $this->l('Percentage of Total Order'),
                            ),
                            array(
                                'id_option' => 2,
                                'name' => $this->l('Product Specific'),
                            ),
                            array(
                                'id_option' => 3,
                                'name' => $this->l('Category Specific'),
                            ),
                        ),
                        'id' => 'id_option',
                        'name' => 'name',
                    ),
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'reward_value',
                ),
                array(
                    'type' => 'hidden',
                    'name' => 'id_affiliate_levels',
                ),
                array(
                    'type' => 'text',
                    'col' => '2',
                    'id' => 'pc-value',
                    'label' => $this->l('Value'),
                    'name' => 'reward_value',
                    'required' => false,
                    'desc' => $this->l('Global Value'),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Tax'),
                    'name' => 'is_tax',
                    'options' => array(
                        'query' => array(
                            array(
                                'id_option' => 0,
                                'name' => $this->l('Tax excl.'),
                            ),
                            array(
                                'id_option' => 1,
                                'name' => $this->l('Tax Incl.'),
                            ),
                        ),
                        'id' => 'id_option',
                        'name' => 'name',
                    ),
                ),
                array(
                    'type' => 'text',
                    'prefix' => $store_currency->sign,
                    'col' => '2',
                    'label' => $this->l('Min Order Value'),
                    'name' => 'min_order_value',
                    'required' => false,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Select Products'),
                    'name' => 'products',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Select Categories'),
                    'name' => 'categories',
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Type of Value'),
                    'name' => 'value_type',
                    'class' => 'type_of_value_fld',
                    'options' => array(
                        'query' => array(
                            array(
                                'id_option' => 0,
                                'name' => $this->l('Fixed') . ' (' . $store_currency->sign . ')',
                            ),
                            array(
                                'id_option' => 1,
                                'name' => $this->l('Percentage') . ' %',
                            ),
                        ),
                        'id' => 'id_option',
                        'name' => 'name',
                    ),
                ),
                array(
                    'type' => 'text',
                    'prefix' => '%',
                    'col' => '6',
                    'label' => $this->l('Parent Affiliates Reward'),
                    'placeholder' => $this->l('Not applicable for level 1'),
                    'name' => 'parent_reward',
                    'required' => false,
                    'desc' => $this->l('Percentage reward of what the current level affiliate is getting. Leave empty for 1st level.'),
                ),
            ),
            'submit' => array(
                'title' => $btn_title,
            ),
        );
        $ps_17 = (Tools::version_compare(_PS_VERSION_, '1.7', '>=') == true) ? 1 : 0;
        $url = $this->context->link->getAdminLink('AdminLevels', true);
        if (!empty($categories) && $obj->id) {
            foreach ($categories as &$category) {
                $category['value'] = $obj->needleCheck('affiliate_levels_categories', 'id_category', $category['id_category'], $obj->id, 'value');
            }
        }
        $products = array();
        if ($obj->id) {
            $products = $obj->getCollection($obj->id, 'id_product', 'affiliate_levels_products');
            if (!empty($products) && is_array($products)) {
                foreach ($products as &$product) {
                    $product = new Product((int) $product['id_product'], true, (int) $this->context->language->id);
                    $product->id_product_attribute = (int) Product::getDefaultAttribute($product->id) > 0 ? (int) Product::getDefaultAttribute($product->id) : 0;
                    $_cover = ((int) $product->id_product_attribute > 0) ? Product::getCombinationImageById((int) $product->id_product_attribute, $this->context->language->id) : Product::getCover($product->id);
                    if (!is_array($_cover)) {
                        $_cover = Product::getCover($product->id);
                    }
                    $product->id_image = $_cover['id_image'];
                    $product->value = $obj->needleCheck('affiliate_levels_products', 'id_product', $product->id, $obj->id, 'value');
                }
            }
        }
        $this->context->smarty->assign(array(
            'obj' => $obj,
            'ps_17' => (int) $ps_17,
            'categories' => $categories,
            'products' => $products,
            'action_url' => $url . '&action=getSearchProducts&forceJson=1&disableCombination=1&exclude_packs=0&excludeVirtuals=0&limit=20',
        ));

        $menu = $this->getMenu();
        return $menu . parent::renderForm();
    }

    public function initProcess()
    {
        $action = Tools::getValue('action');
        if (Tools::isSubmit('submitAddaffiliate_levels')) {
            $categories = Tools::getValue('category');
            $affiliate_level = (int) Tools::getValue('level');
            $products = Tools::getValue('related_products');
            $reward_type = (int) Tools::getValue('reward_type');
            $reward_value = (float) Tools::getValue('reward_value');
            $min_order_value = (float) Tools::getValue('min_order_value');
            $id_affiliate_levels = (int) Tools::getValue('id_affiliate_levels');

            if (isset($products) && $products) {
                foreach ($products as $k => $product) {
                    if (empty($product)) {
                        unset($products[$k]);
                    }
                }
            }

            if (isset($categories) && $categories) {
                foreach ($categories as $key => $category) {
                    if (empty($category)) {
                        unset($categories[$key]);
                    }
                }
            }

            if ($affiliate_level > 1) {
                $level = (int) Levels::getAffiliateLevelExistance(1); //check for base rule Lvl 1
                if ($level < 1 || $level == $id_affiliate_levels) {
                    $this->errors[] = $this->l('Please create rule for Level 1st than you can create for other levels.');
                }
            }
            if ($reward_type == 2 && empty($products)) {
                $this->errors[] = $this->l('Please select and fill atleast one product.');
            } elseif ($reward_type == 3 && empty($categories)) {
                $this->errors[] = $this->l('Please fill atleast one category fields.');
            } elseif ((!$reward_value || !Validate::isUnsignedFloat($reward_value)) && $reward_type < 2) {
                $this->errors[] = $this->l('Invalid reward value');
            }

            if (!Validate::isUnsignedFloat($min_order_value)) {
                $this->errors[] = $this->l('Invalid Min Order Value value');
            }
        }
        if ($action == 'getSearchProducts') {
            $this->getSearchProducts();
            die();
        }
        return parent::initProcess();
    }

    public function postProcess()
    {
        parent::postProcess();
        $obj = $this->loadObject(true);
        $categories = Tools::getValue('category');
        $products = Tools::getValue('related_products');
        $reward_type = (int) Tools::getValue('reward_type');
        if ($obj->id && Tools::isSubmit('submitAdd'.$this->table)) {
            $obj->dumpCurrentData($obj->id);
            if (!empty($categories) && $reward_type == 3) {
                foreach ($categories as $key => $category) {
                    if (empty($category)) {
                        unset($categories[$key]);
                    }
                }
                $obj->populateTable('affiliate_levels_categories', 'id_category', $obj->id, $categories, 'category');
            } elseif (!empty($products) && $reward_type == 2) {
                foreach ($products as $k => $product) {
                    if (empty($product)) {
                        unset($products[$k]);
                    }
                }
                $obj->populateTable('affiliate_levels_products', 'id_product', $obj->id, $products, 'product');
            }
        }

        if (Validate::isLoadedObject($obj) && Tools::isSubmit('active'.$this->table)) {
            $obj->active = !$obj->active;
            if (!$obj->update()) {
                $this->errors[] = $this->l('Status update unsuccessful.');
            } else {
                $this->confirmations[] = $this->l('Status updated successfully.');
            }
        }

        if (($levelsBoxes = Tools::getValue('affiliate_levelsBox')) &&
            (Tools::isSubmit('submitBulkdisableSelection'.$this->table) || Tools::isSubmit('submitBulkenableSelection'.$this->table))) {
            $result = true;
            foreach ($levelsBoxes as $id_affiliate_levels) {
                if (Validate::isLoadedObject($level = new Levels((int)$id_affiliate_levels))) {
                    $level->active = Tools::getIsset('submitBulkenableSelection'.$this->table)? true : false;
                    $result &= $level->update();
                }
            }

            if (!$result) {
                $this->errors[] = $this->l('Bulk status update unsuccessful.');
            } else {
                $this->confirmations[] = $this->l('Bulk status updated successfully.');
            }
        }
    }

    protected function getSearchProducts()
    {
        $query = Tools::getValue('q', false);
        if (!$query || $query == '' || Tools::strlen($query) < 1) {
            die(json_encode($this->l('Found Nothing.')));
        }

        if ($pos = strpos($query, ' (ref:')) {
            $query = Tools::substr($query, 0, $pos);
        }

        $excludeIds = Tools::getValue('excludeIds', false);
        if ($excludeIds && $excludeIds != 'NaN') {
            $excludeIds = implode(',', array_map('intval', explode(',', $excludeIds)));
        } else {
            $excludeIds = '';
        }

        // Excluding downloadable products from packs because download from pack is not supported
        $forceJson = Tools::getValue('forceJson', false);
        $disableCombination = Tools::getValue('disableCombination', false);
        $excludeVirtuals = (bool) Tools::getValue('excludeVirtuals', true);
        $exclude_packs = (bool) Tools::getValue('exclude_packs', true);

        $context = Context::getContext();

        $sql = 'SELECT p.`id_product`, pl.`link_rewrite`, p.`reference`, pl.`name`, image_shop.`id_image` id_image, il.`legend`, p.`cache_default_attribute`
                FROM `' . _DB_PREFIX_ . 'product` p
                ' . Shop::addSqlAssociation('product', 'p') . '
                LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (pl.id_product = p.id_product AND pl.id_lang = ' . (int) $context->language->id . Shop::addSqlRestrictionOnLang('pl') . ')
                LEFT JOIN `' . _DB_PREFIX_ . 'image_shop` image_shop
                    ON (image_shop.`id_product` = p.`id_product` AND image_shop.cover=1 AND image_shop.id_shop=' . (int) $context->shop->id . ')
                LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON (image_shop.`id_image` = il.`id_image` AND il.`id_lang` = ' . (int) $context->language->id . ')
                WHERE (pl.name LIKE \'%' . pSQL($query) . '%\' OR p.reference LIKE \'%' . pSQL($query) . '%\')' .
            (!empty($excludeIds) ? ' AND p.id_product NOT IN (' . $excludeIds . ') ' : ' ') .
            ($excludeVirtuals ? 'AND NOT EXISTS (SELECT 1 FROM `' . _DB_PREFIX_ . 'product_download` pd WHERE (pd.id_product = p.id_product))' : '') .
            ($exclude_packs ? 'AND (p.cache_is_pack IS NULL OR p.cache_is_pack = 0)' : '') .
            ' GROUP BY p.id_product';

        $items = Db::getInstance()->executeS($sql);
        if ($items && ($disableCombination || $excludeIds)) {
            $results = array();
            foreach ($items as $item) {
                if (!$forceJson) {
                    $item['name'] = str_replace('|', '&#124;', $item['name']);
                    $results[] = trim($item['name']) . (!empty($item['reference']) ? ' (ref: ' . $item['reference'] . ')' : '') . '|' . (int) $item['id_product'];
                } else {
                    $cover = Product::getCover($item['id_product']);
                    $results[] = array(
                        'id' => $item['id_product'],
                        'name' => $item['name'],
                        'ref' => (!empty($item['reference']) ? $item['reference'] : ''),
                        'image' => str_replace('http://', Tools::getShopProtocol(), $context->link->getImageLink($item['link_rewrite'], (($item['id_image']) ? $item['id_image'] : $cover['id_image']), $this->getFormatedName('home'))),
                    );
                }
            }

            if (!$forceJson) {
                echo implode("\n", $results);
            } else {
                echo json_encode($results);
            }
        } elseif ($items) {
            // packs
            $results = array();
            foreach ($items as $item) {
                // check if product have combination
                if (Combination::isFeatureActive() && $item['cache_default_attribute']) {
                    $sql = 'SELECT pa.`id_product_attribute`, pa.`reference`, ag.`id_attribute_group`, pai.`id_image`, agl.`name` AS group_name, al.`name` AS attribute_name,
                                a.`id_attribute`
                            FROM `' . _DB_PREFIX_ . 'product_attribute` pa
                            ' . Shop::addSqlAssociation('product_attribute', 'pa') . '
                            LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_combination` pac ON pac.`id_product_attribute` = pa.`id_product_attribute`
                            LEFT JOIN `' . _DB_PREFIX_ . 'attribute` a ON a.`id_attribute` = pac.`id_attribute`
                            LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
                            LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = ' . (int) $context->language->id . ')
                            LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = ' . (int) $context->language->id . ')
                            LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_image` pai ON pai.`id_product_attribute` = pa.`id_product_attribute`
                            WHERE pa.`id_product` = ' . (int) $item['id_product'] . '
                            GROUP BY pa.`id_product_attribute`, ag.`id_attribute_group`
                            ORDER BY pa.`id_product_attribute`';

                    $combinations = Db::getInstance()->executeS($sql);
                    if (!empty($combinations)) {
                        foreach ($combinations as $combination) {
                            $cover = Product::getCover($item['id_product']);
                            $results[$combination['id_product_attribute']]['id'] = $item['id_product'];
                            $results[$combination['id_product_attribute']]['id_product_attribute'] = $combination['id_product_attribute'];
                            !empty($results[$combination['id_product_attribute']]['name']) ? $results[$combination['id_product_attribute']]['name'] .= ' ' . $combination['group_name'] . '-' . $combination['attribute_name']
                            : $results[$combination['id_product_attribute']]['name'] = $item['name'] . ' ' . $combination['group_name'] . '-' . $combination['attribute_name'];
                            if (!empty($combination['reference'])) {
                                $results[$combination['id_product_attribute']]['ref'] = $combination['reference'];
                            } else {
                                $results[$combination['id_product_attribute']]['ref'] = !empty($item['reference']) ? $item['reference'] : '';
                            }
                            if (empty($results[$combination['id_product_attribute']]['image'])) {
                                $results[$combination['id_product_attribute']]['image'] = str_replace('http://', Tools::getShopProtocol(), $context->link->getImageLink($item['link_rewrite'], (($combination['id_image']) ? $combination['id_image'] : $cover['id_image']), $this->getFormatedName('home')));
                            }
                        }
                    } else {
                        $results[] = array(
                            'id' => $item['id_product'],
                            'name' => $item['name'],
                            'ref' => (!empty($item['reference']) ? $item['reference'] : ''),
                            'image' => str_replace('http://', Tools::getShopProtocol(), $context->link->getImageLink($item['link_rewrite'], $item['id_image'], $this->getFormatedName('home'))),
                        );
                    }
                } else {
                    $results[] = array(
                        'id' => $item['id_product'],
                        'name' => $item['name'],
                        'ref' => (!empty($item['reference']) ? $item['reference'] : ''),
                        'image' => str_replace('http://', Tools::getShopProtocol(), $context->link->getImageLink($item['link_rewrite'], $item['id_image'], $this->getFormatedName('home'))),
                    );
                }
            }
            echo json_encode(array_values($results));
        } else {
            echo json_encode(array());
        }
    }

    public function getFormatedName($name)
    {
        $theme_name = Context::getContext()->shop->theme_name;
        $name_without_theme_name = str_replace(array('_' . $theme_name, $theme_name . '_'), '', $name);
        //check if the theme name is already in $name if yes only return $name
        if (strstr($name, $theme_name) && ImageType::getByNameNType($name, 'products')) {
            return $name;
        } elseif (ImageType::getByNameNType($name_without_theme_name . '_' . $theme_name, 'products')) {
            return $name_without_theme_name . '_' . $theme_name;
        } elseif (ImageType::getByNameNType($theme_name . '_' . $name_without_theme_name, 'products')) {
            return $theme_name . '_' . $name_without_theme_name;
        } else {
            return $name_without_theme_name . '_default';
        }
    }

    protected function getMenu()
    {
        $this->tpl_list_vars['dashboard_link'] = $this->module->getAffiliateUrl();
        $this->context->smarty->assign('currentMenuTab', 'rewards');
        $this->context->smarty->assign('subMenuTab', 'levels');
        return $this->module->getMenu();
    }
}

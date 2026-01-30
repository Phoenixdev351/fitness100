<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

use af\Toolkit;

class AmazzingFilter extends Module
{
    public $x = [];

    public function __construct()
    {
        if (!defined('_PS_VERSION_')) {
            exit;
        }
        $this->name = 'amazzingfilter';
        $this->tab = 'front_office_features';
        $this->version = '3.3.0';
        $this->ps_versions_compliancy = ['min' => '1.6.0.4', 'max' => _PS_VERSION_];
        $this->author = 'Amazzing';
        $this->need_instance = 0;
        $this->module_key = '';
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Amazzing filter');
        $this->description = $this->l('Powerful layered navigation with flexible settings');
        $this->definePublicVariables();
    }

    public function definePublicVariables()
    {
        $this->indexation_process_file_path = $this->local_path . 'data/index_all';
        $this->db = Db::getInstance();
        $this->saved_txt = $this->l('Saved');
        $this->error_txt = $this->l('Error');
        $this->product_list_class = 'af-product-list';
        $this->is_modern = Tools::substr(_PS_VERSION_, 0, 3) != '1.6';
        $this->param_names = $this->is_modern ? ['p' => 'page', 'n' => 'resultsPerPage'] : ['p' => 'p', 'n' => 'n'];
        $this->id_lang = $this->context->language->id;
        $this->id_shop = $this->context->shop->id;
        $this->qs_min_values = 10;
        $this->i = [
            'table' => _DB_PREFIX_ . 'af_index',
            'variable_keys' => ['p', 'n', 't'],
            'default' => ['g' => 'PS_UNIDENTIFIED_GROUP', 'c' => 'PS_CURRENCY_DEFAULT'],
            'max_column_suffixes' => 15,
            'p_arr_keys' => [],
        ];
        $this->sp_min_v = '1.0.1';
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
        $this->installation_process = true;
        $this->loadDependencies(Module::isInstalled('af_seopages'));
        if (!parent::install()
            || !$this->registerHook('displayLeftColumn')
            || !$this->registerHook('displayHeader')
            || !$this->registerHook('displayBackOfficeHeader')
            || !$this->registerHook('actionProductAdd')
            || !$this->registerHook('actionProductUpdate')
            || !$this->registerHook('actionIndexProduct')
            || !$this->registerHook('actionObjectAddAfter')
            || !$this->registerHook('actionObjectDeleteAfter')
            || !$this->registerHook('actionObjectUpdateAfter')
            || !$this->registerHook('actionObjectCombinationAddAfter')
            || !$this->registerHook('actionAdminTagsControllerSaveAfter')
            || !$this->registerHook('actionAdminTagsControllerDeleteBefore')
            || !$this->registerHook('actionAdminTagsControllerDeleteAfter')
            || !$this->registerHook('actionProductDelete')
            || !$this->prepareDatabaseTables()
            || !$this->installDemoData()) {
            $this->uninstall();

            return false;
        }
        foreach ($this->getSettingsKeys() as $type) {
            $values = $type == 'indexation' ? $this->recommendedIndexationSettings() : [];
            $this->saveSettings($type, $values); // will be saved for all shops, becasue context is set to ALL above
        }
        $this->indexationTable('install'); // should be installed and adjusted after settings are ready
        $this->updatePosition(Hook::getIdByName('displayLeftColumn'), 0, 1);
        if ($this->is_modern) {
            $this->registerHook('productSearchProvider');
            $this->updatePosition(Hook::getIdByName('productSearchProvider'), 0, 1);
        } else {
            $this->registerHook('actionProductListOverride');
        }
        $this->relatedOverrides()->processAll('add');

        return true;
    }

    public function prepareDatabaseTables()
    {
        $sql = [];
        $sql[] = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'af_templates (
                id_template int(10) unsigned NOT NULL AUTO_INCREMENT,
                id_shop int(10) NOT NULL,
                template_controller varchar(128) NOT NULL,
                active tinyint(1) NOT NULL DEFAULT 1,
                template_name text NOT NULL,
                template_filters text NOT NULL,
                additional_settings text NOT NULL,
                PRIMARY KEY (id_template, id_shop),
                KEY template_controller (template_controller),
                KEY active (active)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';
        $sql[] = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'af_templates_lang (
                id_template int(10) unsigned NOT NULL,
                id_shop int(10) NOT NULL,
                id_lang int(10) NOT NULL,
                data text NOT NULL,
                PRIMARY KEY (id_template, id_shop, id_lang)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';
        $sql[] = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'af_settings (
                id_shop int(10) unsigned NOT NULL,
                type varchar(16) NOT NULL,
                value text NOT NULL,
                PRIMARY KEY (id_shop, type)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';
        $sql[] = 'CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'af_customer_filters (
                id_customer int(10) unsigned NOT NULL,
                filters text NOT NULL,
                PRIMARY KEY (id_customer)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';
        foreach ($this->getControllersWithMultipleIDs() as $controller) {
            $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'af_' . bqSQL($controller) . '_templates` (
                `id_' . bqSQL($controller) . '` int(10) unsigned NOT NULL,
                `id_template` int(10) NOT NULL,
                `id_shop` int(10) NOT NULL,
                PRIMARY KEY (`id_' . bqSQL($controller) . '`, `id_template`, `id_shop`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';
        }
        $this->mergedValues()->extendSQL('install', $sql);

        return $this->runSql($sql);
    }

    public function installDemoData()
    {
        $installed = true;
        $default_filters = $this->getDefaultFiltersData();
        foreach ($this->getAvailableControllers(true) as $controller => $controller_name) {
            $template_name = sprintf($this->l('Template for %s'), $controller_name);
            $installed &= (bool) $this->saveTemplate(0, $controller, $template_name, $default_filters);
        }

        return $installed;
    }

    public function recommendedIndexationSettings()
    {
        $settings = [];
        $check_values = [
            'p_c' => [['id_currency', 'specific_price']],
            'p_g' => [['id_group', 'specific_price'], ['reduction', 'group']],
            't' => [['id_tag', 'product_tag']],
        ];
        foreach ($check_values as $key => $data) {
            $settings[$key] = 0;
            foreach ($data as $d) {
                if (!$settings[$key]) {
                    $settings[$key] = (bool) $this->db->getValue('
                        SELECT `' . bqSQL($d[0]) . '` FROM `' . _DB_PREFIX_ . bqSQL($d[1]) . '`
                        ORDER BY `' . bqSQL($d[0]) . '` DESC
                    ') && $this->availableSuffixesNum($key) <= $this->i['max_column_suffixes'] ? 1 : 0;
                }
            }
        }

        return $settings;
    }

    public function getAvailableControllers($include_category_controller = false)
    {
        $controllers = [
            'category' => $this->l('Category pages'),
            'seopage' => $this->l('Custom SEO pages'),
            'manufacturer' => $this->l('Manufacturer pages'),
            'supplier' => $this->l('Supplier pages'),
            'index' => $this->l('Home page'),
            'pricesdrop' => $this->l('Prices drop page'),
            'newproducts' => $this->l('New products page'),
            'bestsales' => $this->l('Best sales page'),
            'search' => $this->l('Search results'),
        ];
        if (!$include_category_controller) {
            unset($controllers['category']);
        }

        return $controllers;
    }

    public function getControllersWithMultipleIDs($only_keys = true)
    {
        $controllers = [
            'category' => $this->l('Selected categories'),
            'seopage' => $this->l('Selected SEO pages'),
            'manufacturer' => $this->l('Selected manufacturers'),
            'supplier' => $this->l('Selected suppliers'),
        ];

        return $only_keys ? array_keys($controllers) : $controllers;
    }

    public function uninstall()
    {
        $sql = [];
        $sql[] = 'DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'af_templates';
        $sql[] = 'DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'af_templates_lang';
        $sql[] = 'DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'af_settings';
        $sql[] = 'DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'af_customer_filters';
        foreach ($this->getControllersWithMultipleIDs() as $controller) {
            $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'af_' . bqSQL($controller) . '_templates`';
        }
        $this->mergedValues()->extendSQL('uninstall', $sql);
        if ($ret = parent::uninstall() && $this->runSql($sql)) {
            $this->indexationTable('uninstall');
            $this->combinationPrices('uninstall', [], true);
            $this->cache('clear', '');
            $this->relatedOverrides()->processAll('remove');
            Configuration::deleteByName('AF_CF');
        }

        return $ret;
    }

    public function runSql($sql)
    {
        foreach ($sql as $s) {
            if (!$this->db->execute($s)) {
                return false;
            }
        }

        return true;
    }

    public function indexationTable($action)
    {
        $ret = true;
        switch ($action) {
            case 'install':
                $required_columns = $this->indexationColumns('getRequired');
                $columns = [];
                foreach ($required_columns['primary'] as $c_name) {
                    $columns[] = '`' . bqSQL($c_name) . '` int(10) unsigned NOT NULL';
                }
                $specific_types = [
                    'w' => 'DECIMAL(20,2)',
                    'd' => 'DATETIME',
                    'q' => 'TINYINT(1)',
                    'v' => 'TINYINT(1)',
                ];
                foreach ($required_columns['main'] as $c_name) {
                    $type = isset($specific_types[$c_name]) ? $specific_types[$c_name] : 'TEXT';
                    $columns[] = '`' . bqSQL($c_name) . '` ' . pSQL($type) . ' NOT NULL';
                }
                $ret &= $this->db->execute('
                    CREATE TABLE IF NOT EXISTS `' . bqSQL($this->i['table']) . '` (' . implode(', ', $columns) . ',
                    PRIMARY KEY(`' . implode('`, `', array_map('bqSQL', $required_columns['primary'])) . '`))
                    ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8
                ');
                $ret &= $this->indexationColumns('adjust');
                break;
            case 'uninstall':
                $ret &= $this->db->execute('DROP TABLE IF EXISTS `' . bqSQL($this->i['table']) . '`');
                break;
        }

        return $ret;
    }

    public function combinationPrices($action, $params = [], $force_action = false)
    {
        if (empty($this->i['p_comb']) && !$force_action) {
            return true;
        }
        $ret = [];
        $base_t_name = 'af_p_comb';
        $t_name = _DB_PREFIX_ . $base_t_name;
        switch ($action) {
            case 'install':
                $primary_columns = $this->combinationPrices('getPrimaryColumns');
                $ret = $this->db->execute('
                    CREATE TABLE IF NOT EXISTS `' . bqSQL($t_name) . '` (
                        ' . implode(', ', $primary_columns) . ',
                        ' . implode(', ', $this->combinationPrices('getVariableColumns')) . ',
                        PRIMARY KEY (' . implode(',', array_keys($primary_columns)) . ')
                    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8
                ');
                break;
            case 'uninstall':
                $ret = $this->db->execute('DROP TABLE IF EXISTS `' . bqSQL($t_name) . '`');
                break;
            case 'adjustColumns':
                $primary_columns = $this->combinationPrices('getPrimaryColumns');
                $variable_columns = [];
                foreach ($this->combinationPrices('getVariableColumns') as $suffix => $column_details) {
                    $variable_columns['p_' . $suffix] = $column_details;
                }
                $required_columns = array_keys($primary_columns + $variable_columns);
                $existing_columns = array_column($this->db->executeS('
                    SHOW COLUMNS FROM `' . bqSQL($t_name) . '`
                '), 'Field');
                if (!$ret = $required_columns == $existing_columns) {
                    $sql = [];
                    if ($to_remove = array_diff($existing_columns, $required_columns)) {
                        $sql[] = 'ALTER TABLE `' . bqSQL($t_name) . '` DROP ' . implode(', DROP ', $to_remove);
                    }
                    if (array_diff($required_columns, $existing_columns)) {
                        $to_add = []; // IMPORTANT: keep original ordering
                        $prev_column = key(array_slice($primary_columns, -1, 1, true)); // last key of $primary_columns
                        foreach ($variable_columns as $c_name => $column_details) {
                            if (!in_array($c_name, $existing_columns)) {
                                $to_add[] = 'ADD ' . $column_details . ' AFTER ' . $prev_column;
                            }
                            $prev_column = $c_name;
                        }
                        if ($to_add) {
                            $sql[] = 'ALTER TABLE `' . bqSQL($t_name) . '` ' . implode(', ', $to_add);
                        }
                    }
                    if ($sql) {
                        $comment = $variable_columns ? 'some columns are not normalized on purpose' : '';
                        $sql[] = 'ALTER TABLE `' . bqSQL($t_name) . '` COMMENT = \'' . pSQL($comment) . '\'';
                        $sql[] = 'OPTIMIZE TABLE `' . bqSQL($t_name) . '`';
                        $ret &= $this->runSql($sql);
                    }
                }
                break;
            case 'prepareRows':
                $combination_ids = [0 => 0] + array_column($this->db->executeS('
                    SELECT id_product_attribute FROM ' . _DB_PREFIX_ . 'product_attribute_shop
                    WHERE id_product = ' . (int) $params['p_obj']->id . '
                    AND id_shop = ' . (int) $params['id_shop'] . ' AND id_product_attribute > 0
                '), 'id_product_attribute', 'id_product_attribute');
                $country_ids = [0 => 0] + array_column($this->db->executeS('
                    SELECT DISTINCT(id_country) FROM ' . _DB_PREFIX_ . 'specific_price
                    WHERE (id_shop = ' . (int) $params['id_shop'] . ' OR id_shop = 0) AND
                    (id_product = ' . (int) $params['p_obj']->id . ' OR id_product = 0)
                '), 'id_country', 'id_country');
                $required_suffixes = array_keys($this->combinationPrices('getVariableColumns', $params));
                $prices = [];
                foreach ($combination_ids as $id_comb) {
                    $params['p_obj']->af_id_comb = $id_comb ?: $params['p_obj']->af_id_comb_default;
                    foreach ($country_ids as $id_country) {
                        $row = [$params['p_obj']->id, $id_comb, $params['id_shop'], $id_country];
                        $params['p_obj']->af_id_country = $id_country;
                        $price_data = $this->prepareIndexationValue($params['p_obj'], $params['id_shop'], 'p');
                        foreach ($required_suffixes as $suffix) {
                            $prices[$id_comb][$id_country][] = isset($price_data[$suffix]) ? $price_data[$suffix] : 0;
                        }
                        $prices[$id_comb][$id_country] = implode(',', $prices[$id_comb][$id_country]);
                        if (($id_country && $prices[$id_comb][0] == $prices[$id_comb][$id_country])
                            || ($id_comb && $prices[0][$id_country] == $prices[$id_comb][$id_country])) {
                            continue;
                        }
                        $ret[implode('-', $row)] = '(' . implode(',', $row)
                            . ',' . $prices[$id_comb][$id_country] . ')';
                    }
                }
                break;
            case 'insertRows':
                if ($params['rows'] && $product_ids_ = $this->sqlIDs($params['product_ids'])) {
                    $ret = $this->db->execute('
                        DELETE FROM `' . bqSQL($t_name) . '`
                        WHERE id_product IN (' . $product_ids_ . ') AND id_shop = ' . (int) $params['id_shop'] . '
                    ');
                    $ret &= $this->db->execute('
                        REPLACE INTO `' . bqSQL($t_name) . '` VALUES ' . implode(', ', $params['rows']) . '
                    ');
                }
                break;
            case 'getPrimaryColumns':
                $ret = [
                    'id_product' => 'id_product int(10) unsigned NOT NULL',
                    'id_comb' => 'id_comb int(10) unsigned NOT NULL',
                    'id_shop' => 'id_shop int(10) unsigned NOT NULL',
                    'id_country' => 'id_country int(10) unsigned NOT NULL',
                ];
                break;
            case 'getVariableColumns':
                foreach ($this->indexationColumns('getPriceSuffuxes', 0, 86400) as $suffix) {
                    $ret[$suffix] = 'p_' . $suffix . ' decimal(20,2) NOT NULL';
                }
                break;
            case 'extendCacheID':
                $ret = empty($this->i['sql_price_column_']) ? ''
                    : '_' . md5($this->context->country->id . '_' . $this->i['sql_price_column_']);
                break;
            case 'define':
                $ret = $params;
                $c_price_rows = empty($this->i['sql_price_column_']) ? [] : $this->db->executeS('
                    SELECT ' . $this->i['sql_price_column_'] . ' as p, id_comb, id_product
                    FROM `' . bqSQL($t_name) . '` WHERE id_country IN (0, ' . (int) $this->context->country->id . ')
                        AND id_shop = ' . (int) $this->id_shop . '
                    ORDER BY id_country ASC
                '); // id_country ASC, so non-0_id_country will override 0_id_country in $all_prices
                if ($c_price_rows) {
                    $all_prices = [];
                    foreach ($c_price_rows as $row) {
                        $all_prices[$row['id_product']][$row['id_comb']] = $row['p'];
                    }
                    foreach ($ret as $id_product => $p_combs) {
                        foreach (array_keys($p_combs) as $id_comb) {
                            if (isset($all_prices[$id_product][$id_comb])) {
                                $ret[$id_product][$id_comb]['p'] = $all_prices[$id_product][$id_comb];
                            } elseif (isset($all_prices[$id_product][0])) {
                                $ret[$id_product][$id_comb]['p'] = $all_prices[$id_product][0];
                            } else {
                                $ret[$id_product][$id_comb]['p'] = 0;
                            }
                        }
                    }
                } else {
                    $this->i['p_comb'] = 0;
                }
                break;
        }

        return $ret;
    }

    public function indexationColumns($action, $id_shop = 0, $cache_time = 0)
    {
        if ($cache_time = (int) $cache_time) {
            $cache_id = 'indexationColumns_' . $action . '_' . $id_shop;
            if (!$ret = $this->cache('get', $cache_id, '', $cache_time)) {
                $ret = $this->indexationColumns($action, $id_shop);
                $this->cache('save', $cache_id, $ret);
            }

            return $ret;
        }
        $this->defineSettings();
        $ret = true;
        switch ($action) {
            case 'adjust':
                $required_columns = $this->indexationColumns('getRequiredFormatted');
                $existing_variable_columns = array_diff(
                    $this->indexationColumns('getExisting'),
                    array_merge($required_columns['primary'], $required_columns['main'])
                );
                $sql = [];
                if ($to_remove = array_diff($existing_variable_columns, $required_columns['variable'])) {
                    $sql[] = 'ALTER TABLE `' . bqSQL($this->i['table']) . '`
                        DROP `' . implode('`, DROP `', array_map('bqSQL', $to_remove)) . '`';
                }
                if (array_diff($required_columns['variable'], $existing_variable_columns)) {
                    $to_add = []; // IMPORTANT: keep original ordering
                    $prev_column = end($required_columns['main']);
                    foreach ($required_columns['variable'] as $c_name) {
                        if (!in_array($c_name, $existing_variable_columns)) {
                            $type = (Tools::substr($c_name, 0, 1) == 'p' ? 'decimal(20,2)' : 'TEXT');
                            $to_add[] = 'ADD `' . bqSQL($c_name) . '` ' . pSQL($type) . ' NOT NULL AFTER `' . bqSQL($prev_column) . '`';
                        }
                        $prev_column = $c_name;
                    }
                    if ($to_add) {
                        $sql[] = 'ALTER TABLE `' . bqSQL($this->i['table']) . '` ' . implode(', ', $to_add);
                    }
                }
                if ($sql) {
                    $comment = $required_columns['variable'] ? 'some columns are not normalized on purpose' : '';
                    $sql[] = 'ALTER TABLE `' . bqSQL($this->i['table']) . '` COMMENT = \'' . pSQL($comment) . '\'';
                    $sql[] = 'OPTIMIZE TABLE `' . bqSQL($this->i['table']) . '`';
                    $ret &= $this->runSql($sql);
                }
                $this->combinationPrices('adjustColumns');
                break;
            case 'getExisting':
                $ret = array_column($this->db->executeS('SHOW COLUMNS FROM `' . bqSQL($this->i['table']) . '`'), 'Field');
                break;
            case 'getRequiredFormatted':
                $ret = $this->indexationColumns('getRequired', $id_shop);
                $formatted_variable_columns = [];
                foreach ($ret['variable'] as $c_name => $identifiers) {
                    foreach ($identifiers as $suffix) {
                        $formatted_variable_columns[] = $c_name . '_' . $suffix;
                    }
                }
                $ret['variable'] = $formatted_variable_columns;
                break;
            case 'getRequired':
                $ret = [
                    'primary' => ['id_product', 'id_shop'],
                    'main' => ['c', 'a', 'f', 'm', 's', 'w', 'r', 'd', 'q', 'v', 'g'],
                    'variable' => $this->indexationColumns('getVariableData', $id_shop),
                ];
                break;
            case 'getVariableData':
                $ret = [];
                foreach ($this->i['variable_keys'] as $c_name) {
                    if (!empty($this->settings['indexation'][$c_name])) {
                        switch ($c_name) {
                            case 'p':
                                $ret[$c_name] = $this->indexationColumns('getPriceSuffuxes', $id_shop);
                                break;
                            case 'n':
                            case 't':
                                if ($suffixes = $this->getSuffixes('lang', $id_shop)) {
                                    $ret[$c_name] = $suffixes;
                                }
                                break;
                        }
                    }
                }
                break;
            case 'getPriceSuffuxes':
                $ret = [];
                foreach ($this->getSuffixes('group', $id_shop) as $id_group) {
                    foreach ($this->getSuffixes('currency', $id_shop) as $id_currency) {
                        $suffix = $id_group . '_' . $id_currency;
                        $ret[$suffix] = $suffix;
                    }
                }
                break;
        }

        return $ret;
    }

    public function indexationData($action, $params = [])
    {
        $ret = true;
        switch ($action) {
            case 'get':
                $query = $this->indexationData('prepareQuery', $params);
                $ret = $this->db->executeS($query);
                break;
            case 'prepareQuery':
                $query = new DbQuery();
                $query->select('i.id_product AS id, g')->from('af_index', 'i');
                foreach (['c', 'a', 'f', 'm', 's', 'q'] as $c_name) {
                    if (isset($params['available_options'][$c_name])) {
                        $this->i['p_arr_keys'][] = $c_name;
                        $query->select($c_name);
                    }
                }
                if ($this->settings['indexation']['t'] && isset($params['available_options']['t'])) {
                    $this->i['p_arr_keys'][] = 't';
                    $query->select('t_' . (int) $params['id_lang'] . ' AS t');
                }
                if (isset($params['available_options']['w']) || isset($params['sliders']['w'])) {
                    $query->select('w');
                }
                if (!empty($this->i['sql_price_column_'])) {
                    $query->select($this->i['sql_price_column_'] . ' AS p');
                }
                switch ($params['order']['by']) {
                    case 'n':
                        if ($this->settings['indexation']['n']) {
                            $query->select('n_' . (int) $params['id_lang'] . ' AS n');
                        } else {
                            $query->select('pl.name AS n');
                            $query->leftJoin('product_lang', 'pl', 'pl.id_product = i.id_product
                                AND pl.id_shop = i.id_shop AND pl.id_lang = ' . (int) $params['id_lang']);
                        }
                        break;
                    case 'd':
                    case 'r':
                    case 'w':
                        $query->select($params['order']['by']);
                        break;
                    case 'manufacturer_name':
                        $query->select('m'); // possible repeating select(m) is removed in $query->build()
                        break;
                    case 'date_upd':
                        $query->select('ps.date_upd');
                        $query->leftJoin('product_shop', 'ps', 'ps.id_product = i.id_product
                            AND ps.id_shop = i.id_shop');
                        break;
                }
                foreach ($this->indexationData('queryRestrictions', $params) as $restriction) {
                    $query->where($restriction);
                }
                $ret = $query;
                break;
            case 'queryRestrictions':
                $ret = [
                    'id_shop' => 'i.id_shop = ' . (int) $params['id_shop'],
                    'visibility' => 'i.v <> ' . ($params['current_controller'] == 'search' ? 1 : 2),
                    // visibility 'none' is excluded during indexation
                ];
                if ($params['current_controller'] == 'category') {
                    $ret['controller'] = 'FIND_IN_SET(' . (int) $params['id_category'] . ', i.c) > 0';
                } elseif ($params['current_controller'] == 'manufacturer') {
                    $ret['controller'] = 'i.m = ' . (int) $params['id_manufacturer'];
                } elseif ($params['current_controller'] == 'supplier') {
                    $ret['controller'] = 'FIND_IN_SET(' . (int) $params['id_supplier'] . ', i.s) > 0';
                } elseif ($params['current_controller'] != 'index' && $params['current_controller'] != 'seopage') {
                    // newproducts, pricesdrop, bestsales, search
                    $controller_product_ids_ = $this->sqlIDs($params['controller_product_ids']) ?: 0;
                    $ret['controller'] = 'i.id_product IN (' . $controller_product_ids_ . ')';
                }
                break;
            case 'sqlPriceColumn':
                $suffixes = ['g' => $params['id_customer_group'], 'c' => $params['id_currency']];
                $multipliers = [];
                foreach (array_keys($suffixes) as $key) {
                    if (!$this->settings['indexation']['p_' . $key]) {
                        $suffixes[$key] = Configuration::get($this->i['default'][$key]);
                        if ($key == 'c' && $this->context->currency->conversion_rate != 1) {
                            $multipliers[] = $this->context->currency->conversion_rate;
                        }
                    }
                }
                if (!empty($params['dynamic_tax'])
                    && $rate = $this->getTaxRate($params['dynamic_tax'], $this->context->country->id)) {
                    $multipliers[] = 1 + $rate;
                }
                $ret = 'p_' . (int) $suffixes['g'] . '_' . (int) $suffixes['c'];
                if ($multipliers) {
                    $ret .= ' * ' . implode(' * ', array_map('floatval', $multipliers));
                }
                break;
            case 'erase':
                $sql = 'DELETE FROM `' . bqSQL($this->i['table']) . '` WHERE 1';
                foreach (['id_product', 'id_shop'] as $c_name) {
                    if (isset($params[$c_name]) && $ids_ = $this->sqlIDs($params[$c_name])) {
                        $sql .= ' AND `' . bqSQL($c_name) . '` IN (' . $ids_ . ')';
                    }
                }
                $ret &= $this->db->execute($sql);
                break;
            case 'get_ids':
                $ret = array_column($this->db->executeS('
                    SELECT id_product AS id FROM `' . bqSQL($this->i['table']) . '`
                    WHERE id_shop = ' . (int) $params['id_shop'] . '
                '), 'id', 'id');
                break;
        }

        return $ret;
    }

    public function indexationInfo($type, $shop_ids = [], $remove_unused = false)
    {
        $ret = [];
        $shop_ids = $shop_ids ?: $this->shopIDs();
        switch ($type) {
            case 'ids':
                foreach ($shop_ids as $id_shop) {
                    $indexed = $this->indexationData('get_ids', ['id_shop' => $id_shop]);
                    $required = $this->getProductIDsForIndexation($id_shop);
                    $ret[$id_shop]['indexed'] = array_intersect($required, $indexed);
                    $ret[$id_shop]['missing'] = array_diff($required, $indexed);
                    if ($remove_unused && $unused_ids = array_diff($indexed, $required)) {
                        $this->unindexProducts($unused_ids, [$id_shop]);
                    }
                }
                break;
            case 'count':
                $ret = $this->indexationInfo('ids', $shop_ids, $remove_unused);
                foreach ($ret as $id_shop => $data) {
                    foreach ($data as $key => $ids) {
                        $ret[$id_shop][$key] = count($ids);
                    }
                }
                break;
        }

        return $ret;
    }

    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('controller') == 'AdminProducts') {
            // reindexProduct after mass combinations generation
            if ($this->is_modern) {
                $this->bo()->addJquery();
                $js_path = $this->_path . 'views/js/product-indexer.js?v=' . $this->version;
                $this->context->controller->js_files[] = $js_path;
                $ajax_path = 'index.php?controller=AdminModules&configure=' . $this->name .
                '&token=' . Tools::getAdminTokenLite('AdminModules') . '&ajax=1';
                Media::addJsDef(['af_ajax_action_path' => $ajax_path]);
            } elseif (!empty($this->context->cookie->af_index_product)) {
                $this->indexProduct($this->context->cookie->af_index_product);
                $this->context->cookie->__unset('af_index_product');
            }
        }
    }

    public function ajaxAction($action)
    {
        $ret = [];
        switch ($action) {
            case 'CallTemplateForm':
                $id_template = Tools::getValue('id_template');
                $ret = $this->callTemplateForm($id_template);
                break;
            case 'RunProductIndexer':
                $this->ajaxRunProductIndexer(Tools::getValue('all_identifier'));
                break;
            case 'SaveMultipleSettings':
                $ret['saved'] = true;
                foreach (Tools::getValue('submitted_forms') as $type => $data) {
                    $submitted_settings = $this->parseStr($data);
                    $ret['saved'] &= $this->saveSettings($type, $submitted_settings, null, true);
                }
                break;
            case 'SaveCustomCode':
                $code_params = ['type' => Tools::getValue('type'), 'code' => Tools::getValue('code')];
                if ($this->bo()->customCode('save', $code_params)) {
                    $ret['notice'] = $this->saved_txt;
                }
                break;
            case 'SaveTemplate':
            case 'DuplicateTemplate':
            case 'DeleteTemplate':
            case 'EraseIndex':
            case 'UpdateHook':
                $method = 'ajax' . $action;
                $this->$method();
                break;
            case 'ToggleActiveStatus':
                $id_template = Tools::getValue('id_template');
                $active = Tools::getValue('active');
                $ret = ['success' => $this->toggleActiveStatus($id_template, $active)];
                break;
            case 'ShowAvailableFilters':
                $this->context->smarty->assign([
                    'available_filters' => $this->getAvailableFiltersSorted(Tools::getValue('cf')),
                    'blocked' => array_fill_keys(array_filter(explode(',', Tools::getValue('blocked', ''))), 1),
                ]);
                $ret['content'] = $this->display(__FILE__, 'views/templates/admin/available-filters.tpl');
                $ret['title'] = $this->l('Available filtering criteria');
                break;
            case 'RenderFilterElements':
                $ret['html'] = '';
                if ($keys = Tools::getValue('keys')) {
                    $this->assignLanguageVariables();
                    foreach (explode(',', $keys) as $key) {
                        $this->context->smarty->assign(['filter' => $this->getFilterData($key)]);
                        $ret['html'] .= $this->display(__FILE__, 'views/templates/admin/filter-form.tpl');
                    }
                }
                break;
            case 'getCustomerFilters':
                if ($keys = Tools::getValue('keys')) {
                    $ret = $this->customerFilters()->adminAction('getTagifyItems', explode(',', $keys));
                }
                break;
            case 'UpdateModulePosition':
            case 'DisableModule':
            case 'UnhookModule':
            case 'UninstallModule':
            case 'EnableModule':
                $id_module = Tools::getValue('id_module');
                $hook_name = Tools::getValue('hook_name');
                $id_hook = Hook::getIdByName($hook_name);
                $module = Module::getInstanceById($id_module);
                if (Validate::isLoadedObject($module)) {
                    if ($action == 'UpdateModulePosition') {
                        $new_position = Tools::getValue('new_position');
                        $way = Tools::getValue('way');
                        $ret['saved'] = $module->updatePosition($id_hook, $way, $new_position);
                    } elseif ($action == 'DisableModule') {
                        $module->disable();
                        $ret['saved'] = !$module->isEnabledForShopContext();
                    } elseif ($action == 'UnhookModule') {
                        $ret['saved'] = $module->unregisterHook($id_hook, $this->shopIDs());
                    } elseif ($action == 'UninstallModule') {
                        if ($id_module != $this->id) {
                            $ret['saved'] = $module->uninstall();
                        }
                    } elseif ($action == 'EnableModule') {
                        $ret['saved'] = $module->enable();
                    }
                }
                break;
            case 'IndexProduct':
                $ret['indexed'] = $this->indexProduct(Tools::getValue('id_product'));
                break;
            case 'addOverride':
            case 'removeOverride':
                $override_path = Tools::getValue('override');
                $ret['processed'] = $this->relatedOverrides()->process($action, $override_path);
                if (is_string($ret['processed'])) {
                    $this->throwError($ret['processed']);
                }
                break;
            case 'clearCache':
                $this->cache('clear', '');
                $ret['notice'] = $this->l('Cleared');
                break;
            case 'getCachingInfo':
                $ret['info'] = [];
                foreach (array_keys($this->getSettingsFields('caching', false)) as $name) {
                    $ret['info'][$name] = $this->cache('info', $name);
                }
                break;
        }
        exit(json_encode($ret));
    }

    public function getAvailableFiltersSorted($cf)
    {
        $filters = $this->getAvailableFilters();
        $sorted = [];
        foreach ($filters as $key => $f) {
            if ($key == 'c') {
                $f['name'] = $this->l('Subcategories of current page');
            }
            $sorted[$f['prefix']][$key] = $f;
        }
        if ($cf) {
            $sorted = $this->customerFilters()->adminAction('adjustAvailableSorted', $sorted);
        }

        return $sorted;
    }

    public function getContent()
    {
        $this->id_shop = $this->context->shop->id; // fix non-default $this->id_shop in PS8+
        $this->defineSettings();
        if (Tools::isSubmit('ajax') && $action = Tools::getValue('action')) {
            if (!empty($this->sp) && Tools::getValue('sp')) {
                $this->sp->ajaxAction($action);
            } elseif (Tools::getValue('mergedValues')) {
                $this->mergedValues()->ajaxAction($action);
            } else {
                $this->ajaxAction($action);
            }
        }
        $this->bo()->extendGetContent();
        $this->indexationColumns('adjust', 0, 86400); // just to be sure
        $settings = [];
        foreach ($this->getSettingsKeys() as $type) {
            $settings[$type] = $this->getSettingsFields($type);
            if ($type == 'general') {
                $this->adjustSortingFields($settings[$type], $this->is_modern);
            }
        }
        $indexation_required = false;
        $indexation_info = $this->indexationInfo('count', $this->shopIDs(), true);
        foreach ($indexation_info as $id_shop => $data) {
            $indexation_info[$id_shop]['shop_name'] = $this->db->getValue('
                SELECT name FROM ' . _DB_PREFIX_ . 'shop WHERE id_shop = ' . (int) $id_shop . '
            ');
            if ($data['missing']) {
                $indexation_required = true;
            }
        }
        $available_controllers = $this->getAvailableControllers(true);
        $smarty_variables = [
            'js_vars' => [
                'af_txt' => array_map('htmlspecialchars_decode', [
                    'saved' => $this->saved_txt,
                    'error' => $this->error_txt,
                    'deleted' => $this->l('Deleted'),
                    'areYouSure' => $this->l('Are you sure?'),
                ]),
                'af_id_lang' => $this->id_lang,
                'af_is_modern' => $this->is_modern,
            ],
            'indexation_data' => $indexation_info,
            'indexation_required' => $indexation_required,
            // 'controller_options' => $available_controllers, // may be used later
            'grouped_templates' => $this->getGroupedTemplates($available_controllers),
            'available_hooks' => $this->getAvailableHooks(),
            'settings' => $settings,
            'custom_code' => $this->bo()->customCode('get'),
            'overrides_data' => $this->relatedOverrides()->getData(true),
            'this' => $this,
            'is_modern' => $this->is_modern,
            'info' => [
                'changelog' => $this->_path . 'Readme.md?v=' . $this->version,
                'documentation' => $this->_path . 'readme_en.pdf?v=' . $this->version,
                'contact' => 'https://addons.prestashop.com/en/write-to-developper?id_product=18575',
                'modules' => 'https://addons.prestashop.com/en/2_community-developer?contributor=64815',
            ],
            'files_update_warnings' => $this->bo()->getFilesUpdadeWarnings(),
        ];
        $this->context->smarty->assign($smarty_variables);
        $this->assignLanguageVariables();
        $this->mergedValues()->assignConfigVariables();
        $html = $this->display(__FILE__, 'views/templates/admin/configure.tpl');

        return $html;
    }

    public function getGroupedTemplates($controllers)
    {
        $grouped_templates = [];
        $templates_multishop = $this->db->executeS('
            SELECT * FROM ' . _DB_PREFIX_ . 'af_templates
            WHERE id_shop IN (' . $this->shopIDs('context', true) . ')
            GROUP BY id_template ORDER BY id_template DESC, id_shop = ' . (int) $this->id_shop . ' DESC
        ');
        $multiple_id_controllers = $this->getControllersWithMultipleIDs(false);
        foreach ($controllers as $c => $title) {
            if (!isset($multiple_id_controllers[$c])) {
                $c = 'other';
                $title = $this->l('other pages');
            }
            $grouped_templates[$c] = [
                'title' => sprintf($this->l('Templates for %s'), Tools::strtolower($title)),
                'first' => !count($grouped_templates),
                'additional_actions' => $c != 'other',
                'templates' => [],
            ];
        }
        foreach ($templates_multishop as $t) {
            $c = $t['template_controller'];
            if (isset($controllers[$c])) {
                $group = isset($multiple_id_controllers[$c]) ? $c : 'other';
                if (isset($grouped_templates[$group])) {
                    $grouped_templates[$group]['templates'][$t['id_template']] = $t;
                }
            }
        }
        foreach ($grouped_templates as $g => $t) {
            if ($t['templates']) {
                $min_id = min(array_keys($t['templates']));
                $grouped_templates[$g]['templates'][$min_id]['first_in_group'] = 1;
            }
        }

        return $grouped_templates;
    }

    public function getGroupOptions($type, $id_lang)
    {
        $group_options = [];
        switch ($type) {
            case 'attribute':
                foreach (AttributeGroup::getAttributesGroups($id_lang) as $g) {
                    $name = $g['public_name'] . ($g['name'] != $g['public_name'] ? ' (' . $g['name'] . ')' : '');
                    $group_options[$g['id_attribute_group']] = $name;
                }
                break;
            case 'feature':
                foreach (Feature::getFeatures($id_lang) as $f) {
                    $group_options[$f['id_feature']] = $f['name'];
                }
                break;
        }

        return $group_options;
    }

    public function getSettingsFields($type, $fill_values = true, $id_shop = false)
    {
        $fields = $saved_settings = [];
        switch ($type) {
            case 'general':
                $fields = $this->getGeneralSettingsFields();
                break;
            case 'caching':
                $fields = $this->getCachingSettingsFields();
                break;
            case 'indexation':
                $fields = $this->getIndexationSettingsFields();
                if ($fill_values) {
                    $this->markBlockedIndexationFields($fields);
                }
                break;
            case 'cf':
                $fields = $this->customerFilters()->getSettingsFields();
                break;
            case 'seopage':
                if (!empty($this->sp)) {
                    $fields = $this->sp->getSettingsFields();
                }
                break;
            default:
                $fields = $this->getSelectorSettingsFields($type);
                break;
        }
        if ($fill_values) {
            if (!$id_shop && isset($this->settings[$type])) {
                $saved_settings = $this->settings[$type];
            } else {
                $saved_settings = $this->getSavedSettings($id_shop, $type);
            }
        }
        foreach ($fields as $name => $f) {
            $fields[$name]['default_value'] = $f['value'];
            if (isset($saved_settings[$name]) && empty($f['blocked'])) {
                $fields[$name]['value'] = $saved_settings[$name];
            }
        }

        return $fields;
    }

    public function markBlockedIndexationFields(&$fields)
    {
        foreach ($this->availableSuffixesNum() as $key => $num) {
            if (isset($fields[$key]) && $num > $this->i['max_column_suffixes']) {
                $fields[$key]['blocked'] = $this->l('Please contact module developer to activate this option');
                $fields[$key]['value'] = 0;
            }
        }
    }

    public function getGeneralSettingsFields()
    {
        $fields = [
            'layout' => [
                'display_name' => $this->l('Display type'),
                'type' => 'select',
                'value' => 'vertical',
                'options' => $this->getOptions('layout'),
            ],
            'count_data' => [
                'display_name' => $this->l('Show numbers of matches'),
                'value' => 1,
                'type' => 'switcher',
            ],
            'hide_zero_matches' => [
                'display_name' => $this->l('Hide options with zero matches'),
                'value' => 1,
                'type' => 'switcher',
            ],
            'dim_zero_matches' => [
                'display_name' => $this->l('Dim options with zero matches'),
                'value' => 1,
                'type' => 'switcher',
            ],
            'sf_position' => [
                'display_name' => $this->l('Display selected filters'),
                'value' => 0,
                'type' => 'select',
                'options' => [
                    0 => $this->l('Above filter block'),
                    1 => $this->l('Above product list'),
                ],
            ],
            'include_group' => [
                'display_name' => $this->l('Show group name in selected filters'),
                'value' => 0,
                'type' => 'switcher',
            ],
            'more_f' => [
                'display_name' => $this->l('Number visible filters'),
                'tooltip' => $this->l('Other filters will be displayed after clicking MORE button'),
                'value' => 10,
                'type' => 'text',
                'validate' => 'isInt',
            ],
            'compact' => [
                'display_name' => $this->l('Screen width for compact layout'),
                'tooltip' => $this->l('Use compact layout if browser width is equal to this value or less'),
                'type' => 'text',
                'input_suffix' => 'px',
                'value' => 767,
                'validate' => 'isInt',
                'related_options' => '.compact-option',
                'subtitle' => $this->l('Responsive compact view'),
            ],
            'compact_offset' => [
                'display_name' => $this->l('Compact panel offset direction'),
                'type' => 'select',
                'value' => 2,
                'options' => [1 => $this->l('Left'), 2 => $this->l('Right')],
                'validate' => 'isInt',
                'class' => 'compact-option hidden-on-0',
            ],
            'compact_btn' => [
                'display_name' => $this->l('Compact button'),
                'type' => 'select',
                'value' => 1,
                'options' => [
                    1 => $this->l('Text + Filter icon'),
                    2 => $this->l('Only text'),
                    3 => $this->l('Only filter icon'),
                ],
                'validate' => 'isInt',
                'class' => 'compact-option hidden-on-0',
            ],
            'npp' => [
                'display_name' => $this->l('Number of products per page'),
                'value' => Configuration::get('PS_PRODUCTS_PER_PAGE'),
                'type' => 'text',
                'validate' => 'isInt',
                'subtitle' => $this->l('Product list'),
            ],
            'default_sorting' => [
                'display_name' => '',
                'value' => Tools::getProductsOrder('by') . '.' . Tools::getProductsOrder('way'),
                'type' => 'hidden',
                'input_class' => 'af-default-sorting-input',
            ],
            'sorting_options' => [
                'display_name' => $this->l('Sort products by'),
                'value' => array_fill_keys(['position.asc', 'date_add.desc', 'name.asc', 'name.desc',
                    'price.asc', 'price.desc', 'quantity.desc', 'random.desc'], 1),
                'type' => 'sorting_options',
                'available_options' => $this->getOptions('sorting'),
            ],
            'random_upd' => [
                'display_name' => $this->l('Update random order'),
                'value' => 1,
                'type' => 'select',
                'options' => [
                    0 => $this->l('On every page load'),
                    1 => $this->l('Every hour'),
                    2 => $this->l('Every day'),
                    3 => $this->l('Every week'),
                ],
                'class' => 'random-upd',
            ],
            'reload_action' => [
                'display_name' => $this->l('Update product list'),
                'value' => 1,
                'type' => 'select',
                'options' => [
                    1 => $this->l('Instantly'),
                    2 => $this->l('On button click'),
                ],
            ],
            'p_type' => [
                'display_name' => $this->l('Pagination type'),
                'value' => 1,
                'type' => 'select',
                'options' => [
                    1 => $this->l('Regular'),
                    2 => $this->l('Load more button'),
                    3 => $this->l('Infinite scroll'),
                ],
            ],
            'autoscroll' => [
                'display_name' => $this->l('Autoscroll to top after filtration'),
                'tooltip' => $this->l('After applying filters, switching pages, changing sorting, etc...'),
                'value' => 1,
                'type' => 'switcher',
            ],
            'combination_results' => [
                'display_name' => $this->l('Display combination prices/images'),
                'tooltip' => $this->l('Display prices/images basing on selected attributes'),
                'value' => 1,
                'type' => 'switcher',
            ],
            'oos_behaviour_' => [
                'display_name' => $this->l('When no filters are applied'),
                'value' => 0,
                'type' => 'select',
                'options' => $this->oos('getOptions'),
                'class' => 'oos-option primary',
                'subtitle' => $this->l('Non-available products'),
            ],
            'oos_behaviour' => [
                'display_name' => $this->l('When at least one filter is applied'),
                'value' => 0,
                'type' => 'select',
                'options' => $this->oos('getOptions'),
                'class' => 'oos-option secondary',
            ],
            'combinations_stock' => [
                'display_name' => $this->l('Check availability based on selected attributes'),
                'value' => 0,
                'type' => 'switcher',
                'class' => 'oos-option combinations-stock toggle-combinations-cache warn-if-1',
                'warning' => $this->l('May increase filtering time'),
            ],
            'new_days' => [
                'display_name' => $this->l('Product is considered new for'),
                'input_suffix' => $this->l('days'),
                'tooltip' => $this->l('Leave it empty to use native value defined in Product settings'),
                'type' => 'text',
                'value' => '',
                'subtitle' => $this->l('Specific product settings'),
            ],
            'sales_days' => [
                'display_name' => $this->l('Count best sales for the last'),
                'input_suffix' => $this->l('days'),
                'tooltip' => $this->l('Leave it empty to count sales for all the time'),
                'type' => 'text',
                'value' => '',
            ],
            'url_filters' => [
                'display_name' => $this->l('Include filter parameters in URL'),
                'value' => 1,
                'type' => 'switcher',
                'subtitle' => $this->l('Dynamic URL params'),
            ],
            'url_sorting' => [
                'display_name' => $this->l('Include sorting parameter in URL'),
                'value' => 1,
                'type' => 'switcher',
            ],
            'url_page' => [
                'display_name' => $this->l('Include page number in URL'),
                'value' => 1,
                'type' => 'switcher',
            ],
            'dec_sep' => [
                'display_name' => $this->l('Decimal separator'),
                'type' => 'text',
                'value' => '.',
                'subtitle' => $this->l('Number format (Used in numeric sliders and sorting by numbers)'),
            ],
            'tho_sep' => [
                'display_name' => $this->l('Thousand separator'),
                'type' => 'text',
                'value' => '',
            ],
        ] + $this->mergedValues()->getGeneralSettingsFields();

        return $fields;
    }

    public function oos($action, $params = [])
    {
        $ret = [];
        switch ($action) {
            case 'getOptions':
                $ext = '(' . $this->l('if not allowed for ordering') . ')';
                $ret[0] = $this->l('Non-available products are processed like others');
                $ret[2] = $this->l('Move non-available products to the end of the list');
                $ret[1] = $ret[2] . ' ' . $ext;
                $ret[4] = $this->l('Exclude non-available products from the list');
                $ret[3] = $ret[4] . ' ' . $ext;
                ksort($ret);
                break;
            case 'prepareParams':
                $ret = [
                    'id_shop' => $params['id_shop'],
                    'check_combinations' => ($params['combinations_stock']
                        && ($params['oos_behaviour'] || $params['order']['by'] == 'quantity'
                        || !empty($params['available_options']['in_stock']))) || $this->i['p_comb'],
                    'behaviour' => $params['oos_behaviour'],
                    'reset_a' => $params['oos_behaviour'] > 2,
                    'pricesdrop' => !empty($params['filters']['pricesdrop'])
                        || $params['current_controller'] == 'pricesdrop',
                    'stock_mng' => Configuration::get('PS_STOCK_MANAGEMENT'),
                ];
                if (!empty($params['in_stock'])) {
                    $ret['behaviour'] = 4;
                    $ret['reset_a'] = $ret['check_combinations'];
                } elseif (!$params['filters']) {
                    $ret['behaviour'] = $params['oos_behaviour_'];
                }
                $ret['move'] = $ret['behaviour'] && $ret['behaviour'] < 3;
                $ret['exclude'] = $ret['behaviour'] > 2;
                if ($ret['behaviour'] == 1 || $ret['behaviour'] == 3) {
                    $ret['allowed_ids'] = $this->oos('getAllowedIds', $ret);
                }
                if ($ret['check_combinations']) {
                    $ret['combinations_to_match'] = $this->getPossibleCombinations($params['selected_atts']);
                }
                $ret['count_only_in_stock'] = $ret['reset_a'];
                if ($this->i['p_comb'] && !$ret['reset_a']) {
                    $ret['reset_a'] = true;
                }
                break;
            case 'extendQuery':
                $ret = $this->oos('stockShopAssociation', $params);
                if ($params['count_only_in_stock'] && $params['stock_mng']) {
                    $allowed_query = isset($params['allowed_ids']) ? ' OR ' . $this->oos('allowedQuery') : '';
                    $ret .= ' AND (sa.quantity > 0' . $allowed_query . ')';
                }
                break;
            case 'getAllowedIds':
                $cache_id = 'allowed_ids_' . implode('_', $params);
                $ret = $this->cache('get', $cache_id);
                if ($ret === false) {
                    $ret = array_column($this->db->executeS('
                        SELECT DISTINCT(sa.id_product) FROM ' . _DB_PREFIX_ . 'stock_available sa
                        INNER JOIN ' . _DB_PREFIX_ . 'product_shop ps
                            ON ps.id_product = sa.id_product AND ps.active = 1
                            AND ps.id_shop = ' . (int) $params['id_shop'] . '
                        WHERE ' . $this->oos('stockShopAssociation', $params) . '
                            AND ' . $this->oos('allowedQuery') . '
                            AND sa.quantity < 1' . ($params['check_combinations'] ? '' : '
                            AND sa.id_product_attribute = 0') . '
                    '), 'id_product', 'id_product');
                    $this->cache('save', $cache_id, $ret);
                }
                break;
            case 'allowedQuery':
                $ret = 'sa.out_of_stock IN (1' . (Configuration::get('PS_ORDER_OUT_OF_STOCK') ? ',2' : '') . ')';
                break;
            case 'stockShopAssociation': // based on StockAvailable::addSqlShopRestriction()
                $shop_group = Shop::getGroupFromShop($params['id_shop'], false);
                $ret = $shop_group['share_stock']
                    ? '(sa.id_shop_group = ' . (int) $shop_group['id'] . ' AND sa.id_shop = 0)'
                    : '(sa.id_shop = ' . (int) $params['id_shop'] . ' AND sa.id_shop_group = 0)';
                break;
        }

        return $ret;
    }

    public function getCachingSettingsFields()
    {
        $fields = [
            'c_list' => ['display_name' => $this->l('Category options'), 'value' => 0],
            'a_list' => ['display_name' => $this->l('Attribute options'), 'value' => 1],
            'f_list' => ['display_name' => $this->l('Feature options'), 'value' => 1],
            'comb_data' => ['display_name' => $this->l('Combinations data'), 'value' => 1],
        ];
        foreach ($fields as $name => &$f) {
            $f += ['type' => 'switcher', 'class' => $name];
        }

        return $fields;
    }

    public function getSelectorSettingsFields($type)
    {
        $fields = [];
        if ($selectors = $this->getSelectors($type)) {
            $input_prefix = '.';
            $validate = 'isImageTypeName'; // a-zA-Z0-9_ -
            if ($type == 'themeid') {
                $input_prefix = '#';
                $validate = 'isHookName'; // a-zA-Z0-9_- no spaces
            } elseif ($type == 'iconclass') {
                $fields['load_font'] = [
                    'display_name' => $this->l('Load icon font'),
                    'tooltip' => $this->l('Use this option if your theme does not support icon-xx classes'),
                    'value' => $this->is_modern ? 1 : 0,
                    'type' => 'switcher',
                ];
            }
            foreach ($selectors as $name => $display_name) {
                $fields[$name] = [
                    'display_name' => $display_name,
                    'value' => $name,
                    'type' => 'text',
                    'input_prefix' => $input_prefix,
                    'validate' => $validate,
                    'required' => 1,
                ];
            }
        }

        return $fields;
    }

    public function getIndexationSettingsFields()
    {
        $fields = [
            'auto' => [
                'display_name' => $this->l('Re-index products on saving programmatically'),
                'info' => $this->l('After calling hook ActionProductUpdate or ActionProductAdd during bulk import'),
                'type' => 'switcher',
                'value' => 1,
             ],
            'subcat_products' => [
                'display_name' => $this->l('Index associations for all products from subcategories'),
                'info' => $this->l('Even if they are not directly associated to current category'),
                'type' => 'switcher',
                'value' => 1,
            ],
            'c_active' => [
                'display_name' => $this->l('Index associations only with active categories'),
                'info' => $this->l('Products without active category associations will not be indexed'),
                'type' => 'switcher',
                'value' => 0,
            ],
            'p' => [
                'display_name' => $this->l('Include price data in indexation'),
                'info' => $this->l('Required if you want to filter/sort products by price'),
                'type' => 'switcher',
                'value' => 1,
                'related_options' => '.indexation-price-option',
            ],
            'p_c' => [
                'display_name' => $this->l('Index prices for different currencies'),
                'info' => $this->l('Required if you have specific price rules only for selected currencies'),
                'type' => 'switcher',
                'value' => 0,
                'class' => 'indexation-price-option hidden-on-0',
            ],
            'p_g' => [
                'display_name' => $this->l('Index prices for different customer groups'),
                'info' => $this->l('Required if you have specific price rules only for selected customer groups'),
                'type' => 'switcher',
                'value' => 0,
                'class' => 'indexation-price-option hidden-on-0',
            ],
            'p_comb' => [
                'display_name' => $this->l('Index prices for different combinations'),
                'info' => $this->l('Required if you have different prices for combinations') . '. ' .
                $this->l('NOTE: when this option is active, price filter type is forced to -slider-'),
                'type' => 'switcher',
                'value' => 0,
                'class' => 'indexation-price-option hidden-on-0 toggle-combinations-cache',
            ],
            'dynamic_tax' => [
                'display_name' => 'Tax rule to apply dynamically',
                'info' => 'It will be applied for all products, depending on current country',
                'type' => 'text',
                'value' => 0,
                'class' => 'indexation-price-option hidden-on-0 advanced-field',
            ],
            't' => [
                'display_name' => $this->l('Include tags data in indexation'),
                'info' => $this->l('Required if you want to filter products by tags'),
                'type' => 'switcher',
                'value' => 0,
            ],
            'n' => [
                'display_name' => $this->l('Include product name in indexation'),
                'info' => $this->l('Can make sorting by name faster on very large catalogues (30 000+ products)'),
                'type' => 'switcher',
                'value' => 0,
            ],
        ];

        return $fields;
    }

    public function getSelectors($type)
    {
        $selectors = [];
        switch ($type) {
            case 'iconclass':
                $selectors = [
                    'icon-filter' => $this->l('Filter icon'),
                    'u-times' => $this->l('Remove one filter icon'),
                    'icon-eraser' => $this->l('Remove all filters icon'),
                    'icon-lock' => $this->l('Locked filters icon'),
                    'icon-unlock-alt' => $this->l('Unlocked filters icon'),
                    // 'icon-refresh icon-spin' => $this->l('Loading indicator icon'), // not used
                    'icon-minus' => $this->l('Minus icon'),
                    'icon-plus' => $this->l('Plus icon'),
                    'icon-check' => $this->l('Checked icon'),
                    'icon-save' => $this->l('Save icon'),
                ];
                break;
            case 'themeclass':
                $selectors = [
                    'js-product-miniature' => $this->l('Product list item'),
                    'pagination' => $this->l('Pagination container'),
                ];
                if (!$this->is_modern) {
                    $selectors = [
                        'ajax_block_product' => $selectors['js-product-miniature'],
                        'pagination' => $selectors['pagination'],
                        'product-count' => $this->l('Product count countainer'),
                        'heading-counter' => $this->l('Total matches container'),
                    ];
                }
                break;
            case 'themeid':
                $selectors = ['main' => $this->l('Main column container')];
                if (!$this->is_modern) {
                    $selectors = [
                        'center_column' => $selectors['main'],
                        'pagination' => $this->l('Top pagination wrapper'),
                        'pagination_bottom' => $this->l('Bottom pagination wrapper'),
                    ];
                }
                break;
        }

        return $selectors;
    }

    public function saveSettings($type, $values = [], $shop_ids = null, $throw_error = false, $fields = null)
    {
        if ($fields = $fields ?: $this->getSettingsFields($type, false)) {
            $settings_to_save = $settings_rows = [];
            foreach ($fields as $name => $field) {
                $settings_to_save[$name] = isset($values[$name]) ? $values[$name] : $field['value'];
            }
            $errors = $this->validateSettings($settings_to_save, $fields); // values that didn't pass validation are updated
            if ($errors && $throw_error) {
                $this->throwError($errors);
            }
            $shop_ids = $shop_ids ?: $this->shopIDs();
            if ($type == 'indexation') {
                $shop_ids = $this->shopIDs('all');
                if (!$settings_to_save['p']) {
                    $settings_to_save['p_comb'] = 0;
                }
            }
            $encoded_settings = json_encode($settings_to_save);
            foreach ($shop_ids as $id_shop) {
                $settings_rows[] = '(' . (int) $id_shop . ', \'' . pSQL($type)
                    . '\', \'' . pSQL($encoded_settings) . '\')';
            }
            if ($settings_rows && $settings_to_save && $saved = $this->db->execute('
                    REPLACE INTO ' . _DB_PREFIX_ . 'af_settings VALUES ' . implode(', ', $settings_rows) . '
                ')) {
                $this->settings[$type] = $settings_to_save;
                if ($type == 'indexation') {
                    if ($settings_to_save['p_comb']) {
                        $this->i['p_comb'] = 1;
                        $this->combinationPrices('install');
                    } else {
                        $this->combinationPrices('uninstall');
                        $this->i['p_comb'] = 0;
                    }
                    if (empty($this->installation_process)) {
                        $this->cache('clear', 'indexationColumns');
                        $this->cache('clear', 'comb_data');
                        $this->indexationColumns('adjust');
                    }
                } elseif ($type == 'cf') {
                    $this->customerFilters()->extraSettingsActions($settings_to_save, $shop_ids);
                } elseif ($type == 'seopage' && !empty($this->sp)) {
                    $this->sp->extraSettingsActions($settings_to_save, $shop_ids);
                }

                return $saved;
            }
        }
    }

    public function defineSettings()
    {
        if (!isset($this->settings)) {
            $this->settings = $this->getSavedSettings();
            $this->loadDependencies(Module::isEnabled('af_seopages'));
            Toolkit::adjustSeparators($this->settings['general']['dec_sep'], $this->settings['general']['tho_sep']);
            $this->i['p_comb'] = !empty($this->settings['indexation']['p_comb']);
        }
    }

    public function loadDependencies($sp = false)
    {
        require_once $this->local_path . 'classes/Toolkit.php';
        if ($sp) {
            $this->sp = Module::getInstanceByName('af_seopages');
        }
    }

    public function getSavedSettings($id_shop = false, $type = false)
    {
        $settings = [];
        $id_shop = $id_shop ?: $this->id_shop;
        $data = $this->db->executeS('
            SELECT * FROM ' . _DB_PREFIX_ . 'af_settings
            WHERE id_shop = ' . (int) $id_shop . ($type ? ' AND type = \'' . pSQL($type) . '\'' : '') . '
        ');
        foreach ($data as $row) {
            $settings[$row['type']] = json_decode($row['value'], true) ?: [];
        }
        if ($type) {
            $settings = isset($settings[$type]) ? $settings[$type] : [];
        }

        return $settings;
    }

    public function getSettingsKeys()
    {
        return ['general', 'iconclass', 'themeclass', 'themeid', 'caching', 'indexation', 'cf', 'seopage'];
    }

    public function getLayoutClasses()
    {
        return $this->settings['iconclass'] + $this->settings['themeclass'];
    }

    public function getProductIDsForIndexation($id_shop)
    {
        return array_column($this->db->executeS('
            SELECT p.id_product AS id FROM ' . _DB_PREFIX_ . 'product p
            INNER JOIN ' . _DB_PREFIX_ . 'product_shop ps
                ON ps.id_product = p.id_product AND ps.id_product > 0
                AND ' . $this->isVisibleQuery('ps', $id_shop) . '
        '), 'id', 'id');
    }

    public function getCurrentHook()
    {
        return current(array_keys(array_filter($this->getAvailableHooks())));
    }

    public function getAvailableHooks()
    {
        $available_hooks = [
            'displayAmazzingFilter' => 0,
            'displayHeaderCategory' => 0,
            'displayLeftColumn' => 0,
            'displayRightColumn' => 0,
            'displayTopColumn' => 0,
        ];
        $registered_hooks = array_fill_keys(array_column($this->db->executeS('
            SELECT h.name FROM ' . _DB_PREFIX_ . 'hook_module hm
            LEFT JOIN ' . _DB_PREFIX_ . 'hook h
                ON (h.id_hook = hm.id_hook)
            WHERE h.name IN (\'' . implode('\', \'', array_map('pSQL', array_keys($available_hooks))) . '\')
                AND hm.id_module = ' . (int) $this->id . '
                AND hm.id_shop IN (' . $this->shopIDs('context', true) . ')
        '), 'name'), 1);
        $available_hooks = $registered_hooks + $available_hooks;
        ksort($available_hooks);

        return $available_hooks;
    }

    public function callTemplateForm($id_template, $full = true)
    {
        $available_controllers = $this->getAvailableControllers(true);
        if (!$id_template) {
            $controller = Tools::getValue('template_controller');
            $name = isset($available_controllers[$controller]) ? $available_controllers[$controller] : $controller;
            $template_name = sprintf($this->l('Template for %s'), $name) . ' - ' . date('Y-m-d H:i:s');
            $template_filters = $this->getDefaultFiltersData();
            $id_template = $this->saveTemplate($id_template, $controller, $template_name, $template_filters);
        }
        $template_data = $this->db->getRow('
            SELECT * FROM ' . _DB_PREFIX_ . 'af_templates WHERE id_template = ' . (int) $id_template . '
            ORDER BY id_shop = ' . (int) $this->id_shop . ' DESC
        ');
        $template_data['first_in_group'] = $id_template == $this->db->getValue('
            SELECT id_template FROM ' . _DB_PREFIX_ . 'af_templates
            WHERE template_controller = \'' . pSQL($template_data['template_controller']) . '\'
            ORDER BY id_template ASC
        ');
        $controller = $template_data['template_controller'];
        $this->context->smarty->assign([
            'controller_options' => $available_controllers,
            't' => $template_data,
            'additional_actions' => in_array($controller, $this->getControllersWithMultipleIDs(true)),
            'is_modern' => $this->is_modern,
        ]);
        if ($full && $template_data) {
            $template_filters = json_decode($template_data['template_filters'], true);
            $template_filters_lang = $this->db->executeS('
                SELECT id_lang, data FROM ' . _DB_PREFIX_ . 'af_templates_lang
                WHERE id_template = ' . (int) $template_data['id_template'] . '
                AND id_shop = ' . (int) $template_data['id_shop'] . '
            ');
            foreach ($template_filters_lang as $multilang_data) {
                $id_lang = $multilang_data['id_lang'];
                $data = json_decode($multilang_data['data'], true);
                foreach ($data as $filter_key => $values) {
                    if (isset($template_filters[$filter_key])) {
                        foreach ($values as $name => $value) {
                            $template_filters[$filter_key][$name][$id_lang] = $value;
                        }
                    }
                }
            }
            foreach ($template_filters as $key => $saved_values) {
                $template_filters[$key] = $this->getFilterData($key, $saved_values);
            }
            $controller_ids = $this->getTemplateControllerIds($id_template, $controller);
            $general_settings_fields = $this->getSettingsFields('general', true);
            $this->adjustSortingFields($general_settings_fields, false);
            $this->context->smarty->assign([
                'template_controller_settings' => $this->getControllerSettingsFields($controller, $controller_ids),
                'template_filters' => $template_filters,
                'additional_settings' => $template_data['additional_settings']
                    ? json_decode($template_data['additional_settings'], true) : [],
                'general_settings_fields' => $general_settings_fields,
            ]);
        }
        $this->assignLanguageVariables();
        $ret = [
            'form_html' => $this->display(__FILE__, 'views/templates/admin/template-form.tpl'),
            'id_template' => $id_template,
        ];

        return $ret;
    }

    public function adjustSortingFields(&$fields, $allow_advanced_configuration = true)
    {
        $fields['sorting_options']['available_options']['position.asc'] .= ' (' . $this->l('position') . ')';
        if ($allow_advanced_configuration) {
            $fields['sorting_options']['value'][$fields['default_sorting']['value']] = 1;
            $fields['sorting_options']['default_value'] = $fields['default_sorting']['value'];
            $fields['sorting_options']['class'] = 'advanced-sorting-config';
        } else {
            $fields['sorting_options']['type'] = 'hidden';
            $fields['sorting_options']['value'] = '';
            $fields['default_sorting']['display_name'] = $this->l('Default sorting');
            $fields['default_sorting']['type'] = 'select';
            $fields['default_sorting']['options'] = $fields['sorting_options']['available_options'];
            $fields['default_sorting']['class'] = 'simple-sorting-config';
        }
    }

    public function getTemplateControllerIds($id_template, $controller, $id_shop = false)
    {
        $ids = [];
        if (in_array($controller, $this->getControllersWithMultipleIDs())) {
            $ids = array_column($this->db->executeS('
                SELECT DISTINCT `id_' . bqSQL($controller) . '` AS id
                FROM `' . _DB_PREFIX_ . 'af_' . bqSQL($controller) . '_templates`
                WHERE id_template = ' . (int) $id_template . ' AND id_shop ' . ($id_shop ? '= ' . (int) $id_shop
                    : 'IN (' . $this->shopIDs('context', true) . ')') . '
            '), 'id', 'id');
        }

        return $ids;
    }

    public function getDefaultAdditionalSettings($controller)
    {
        $additional_settings = [];
        if ($specific_sorting = $this->getSpecificSorting($controller)) {
            $additional_settings['default_sorting'] = $specific_sorting;
        }

        return $additional_settings;
    }

    public function assignLanguageVariables()
    {
        $this->context->smarty->assign([
            'available_languages' => $this->getAvailableLanguages(),
            'id_lang_current' => $this->context->language->id,
        ]);
    }

    public function getAvailableLanguages($only_ids = false, $only_active = false)
    {
        $key = 'l_' . (int) $only_ids . (int) $only_active;
        if (!isset($this->x[$key])) {
            $column = $only_ids ? 'id_lang' : 'iso_code';
            $this->x[$key] = array_column(Language::getLanguages($only_active), $column, 'id_lang');
        }

        return $this->x[$key];
    }

    public function getControllerSettingsFields($controller, $controller_ids)
    {
        $fields = [];
        $multiple_id_controllers = $this->getControllersWithMultipleIDs(false);
        if (isset($multiple_id_controllers[$controller])) {
            $field = [
                'display_name' => $multiple_id_controllers[$controller],
                'value' => $controller_ids,
                'type' => 'multiple_options',
                'options' => $this->getOptions($controller),
            ];
            if ($controller == 'category') {
                $field['id_root'] = Configuration::get('PS_ROOT_CATEGORY');
                $field['checkable_root'] = $this->is_modern;
            }
            $fields['controller_ids'] = $field;
        }

        return $fields;
    }

    public function getOptions($type)
    {
        $options = [];
        switch ($type) {
            case 'manufacturer':
            case 'supplier':
                $options = array_column($this->db->executeS('
                    SELECT `id_' . bqSQL($type) . '` AS id, `name`
                    FROM `' . _DB_PREFIX_ . bqSQL($type) . '` ORDER BY `name` ASC
                '), 'name', 'id');
                break;
            case 'category':
                $categories = $this->db->executeS('
                    SELECT DISTINCT(c.id_category), c.id_parent, cl.name
                    FROM ' . _DB_PREFIX_ . 'category c ' . Shop::addSqlAssociation('category', 'c') . '
                    INNER JOIN ' . _DB_PREFIX_ . 'category_lang cl
                        ON cl.id_category = c.id_category AND cl.id_lang = ' . (int) $this->id_lang . '
                        AND cl.id_shop = category_shop.id_shop
                    ORDER BY c.id_parent, c.position, cl.id_shop = ' . (int) $this->id_shop . ' DESC
                ');
                foreach ($categories as $cat) {
                    $options[$cat['id_parent']][$cat['id_category']] = $cat['name'];
                }
                break;
            case 'seopage':
                if (!empty($this->sp)) {
                    $options = $this->sp->getPageOptions();
                }
                break;
            case 'layout':
                $options = [
                    'vertical' => $this->l('Vertical'),
                    'horizontal' => $this->l('Horizontal'),
                ];
                break;
            case 'sorting':
                $options = [
                    'position.asc' => $this->l('Relevance'),
                    'position.desc' => $this->l('Relevance, reverse'),
                    'date_add.desc' => $this->l('Newest First'),
                    'date_add.asc' => $this->l('Oldest First'),
                    'date_upd.desc' => $this->l('Recently updated'),
                    'date_upd.asc' => $this->l('Earliest updated'),
                    'name.asc' => $this->l('Name, A to Z'),
                    'name.desc' => $this->l('Name, Z to A'),
                    'price.asc' => $this->l('Cheapest first'),
                    'price.desc' => $this->l('Most expensive first'),
                    'weight.asc' => $this->l('Weight'),
                    'weight.desc' => $this->l('Weight, High to Low'),
                    'quantity.desc' => $this->l('In stock first'),
                    'quantity.asc' => $this->l('Out of stock first'),
                    'random.desc' => $this->l('Random'),
                    'sales.desc' => $this->l('Best sales'),
                    'sales.asc' => $this->l('Lowest sales'),
                    'reference.asc' => $this->l('Reference, A to Z'),
                    'reference.desc' => $this->l('Reference, Z to A'),
                    'manufacturer_name.asc' => $this->l('Brand, A to Z'),
                    'manufacturer_name.desc' => $this->l('Brand, Z to A'),
                ];
                break;
        }

        return $options;
    }

    public function getFilterData($key, $saved_values = [])
    {
        if (!isset($this->available_filters)) {
            $this->available_filters = $this->getAvailableFilters();
        }
        if (isset($this->available_filters[$key])) {
            $filter_data = $this->available_filters[$key];
            $filter_data['key'] = $key;
            if ($key == 'c') {
                $filter_data['prefix'] = $this->l('Subcategories of current page');
            }
            $filter_data['name_original'] = $filter_data['name'];
            $filter_data['settings'] = $this->getFilterFields($filter_data, $saved_values);
            $custom_name = $filter_data['settings']['custom_name']['value'];
            if (is_array($custom_name) && !empty($custom_name[$this->context->language->id])) {
                $filter_data['name'] = $custom_name[$this->context->language->id];
            }
        } else {
            $filter_data = [];
        }

        return $filter_data;
    }

    public function getFilterFields($filter_data, $saved_values = [])
    {
        $fields = [
            'custom_name' => [
                'display_name' => $this->l('Custom name'),
                'value' => '',
                'type' => 'text',
                'multilang' => 1,
                'class' => 'custom-name',
            ],
            'quick_search' => [
                'display_name' => $this->l('Quick search for options'),
                'tooltip' => $this->l('If there are more than 10 options'),
                'value' => 0,
                'type' => 'switcher',
                'class' => 'type-exc not-for-3 not-for-4',
            ],
            'slider_prefix' => [
                'display_name' => $this->l('Slider prefix'),
                'value' => '',
                'type' => 'text',
                'multilang' => 1,
                'class' => 'type-exc not-for-1 not-for-2 not-for-3 not-for-5',
            ],
            'slider_suffix' => [
                'display_name' => $this->l('Slider suffix'),
                'value' => '',
                'type' => 'text',
                'multilang' => 1,
                'class' => 'type-exc not-for-1 not-for-2 not-for-3 not-for-5',
            ],
            'slider_step' => [
                'display_name' => $this->l('Slider step'),
                'value' => 1,
                'type' => 'text',
                'class' => 'type-exc not-for-1 not-for-2 not-for-3 not-for-5',
                // 'quick' => 1,
            ],
            'range_step' => [
                'display_name' => $this->l('Range step'),
                'value' => 100,
                'type' => 'text',
                'class' => 'type-exc not-for-4',
                'quick' => 1,
            ],
            'foldered' => [
                'display_name' => $this->l('Foldered structure'),
                'value' => 1,
                'type' => 'switcher',
                'class' => 'type-exc not-for-3',
            ],
            'nesting_lvl' => [
                'display_name' => $this->l('Nesting level'),
                'value' => 0,
                'type' => 'select',
                'options' => [0 => $this->l('All'), 1 => 1, 2 => 2],
                'input_class' => 'nesting-lvl',
            ],
            'color_display' => [
                'display_name' => $this->l('Color display'),
                'value' => 2,
                'type' => 'select',
                'options' => [
                    0 => $this->l('None'),
                    1 => $this->l('Inline color boxes'),
                    2 => $this->l('Color boxes with names'),
                ],
                'class' => 'type-exc not-for-4 not-for-3 not-for-5',
            ],
            'visible_items' => [
                'display_name' => $this->l('Max. visible items'),
                'value' => 15,
                'type' => 'text',
                'class' => 'type-exc not-for-4 not-for-3',
            ],
            'and' => [
                'display_name' => $this->l('Join type'),
                'tooltip' => $this->l('When multiple options within group are selected'),
                'value' => 0,
                'type' => 'select',
                'options' => [0 => 'OR', 1 => 'AND'],
                'class' => 'type-exc not-for-4 not-for-3',
            ],
            'sort_by' => [
                'display_name' => $this->l('Sort by'),
                'value' => 0,
                'type' => 'select',
                'options' => [
                    '0' => $this->l('Name'),
                    'first_num' => $this->l('First number in name'),
                    'numbers_in_name' => $this->l('All numbers in name'),
                    'id' => $this->l('ID'),
                    'position' => $this->l('Position'),
                    'matches' => $this->l('Number of matches'),
                ],
                'class' => 'type-exc not-for-4',
                'input_class' => 'sort-by',
                'quick' => 1,
            ],
            'type' => [
                'display_name' => $this->l('Type'),
                'value' => 1,
                'type' => 'select',
                'options' => [
                    1 => $this->l('Checkbox'),
                    2 => $this->l('Radio button'),
                    3 => $this->l('Select'),
                    4 => $this->l('Slider'),
                    5 => $this->l('Text box'),
                ],
                'quick' => 1,
                'input_class' => 'f-type',
            ],
            'minimized' => [
                'display_name' => $this->l('Minimized'),
                'value' => 0,
                'type' => 'checkbox',
                'quick' => 1,
            ],
        ];
        $filter_data['first_char'] = Tools::substr($filter_data['key'], 0, 1);
        if (!isset($saved_values['slider_prefix']) && !isset($saved_values['slider_suffix'])) {
            if ($slider_extensions = $this->detectSliderExtensions($filter_data['key'])) {
                $fields['slider_prefix']['value'] = $slider_extensions['prefix'];
                $fields['slider_suffix']['value'] = $slider_extensions['suffix'];
            }
        }
        if (!isset($saved_values['visible_items'])
            && !in_array($filter_data['first_char'], ['a', 'f', 'm', 's', 't'])) {
            $fields['visible_items']['value'] = '';
        }
        $this->adjustFilterFields($filter_data, $fields);
        foreach ($fields as $name => &$f) {
            $f['input_name'] = 'filters[' . $filter_data['key'] . '][' . $name . ']';
            $f['value'] = isset($saved_values[$name]) ? $saved_values[$name] : $f['value'];
            if (!empty($f['multilang'])) {
                $f['input_name'] = str_replace('filters', 'filters[multilang]', $f['input_name']);
            }
        }

        return $fields;
    }

    public function detectSliderExtensions($key)
    {
        $extensions = [];
        $first_char = Tools::substr($key, 0, 1);
        switch ($first_char) {
            case 'a': // possible numeric sliders
            case 'f':
                $id_group = Tools::substr($key, 1);
                $method = $first_char == 'a' ? 'getAttributes' : 'getFeatures';
                foreach ($this->getAvailableLanguages(true) as $id_lang) {
                    $values = $this->$method($id_lang, $id_group);
                    foreach ($values as $i => $val) {
                        if ($i > 3 || isset($extensions['prefix'][$id_lang])) {
                            break;  // don't spend many resourses on detecting extensions
                        }
                        if ($numbers = Toolkit::extractNumbers($val['name'])) {
                            $name = explode($numbers[0], $val['name']);
                            $possible_prefix = trim(strip_tags($name[0]));
                            $possible_suffix = isset($name[1]) ? trim(strip_tags($name[1])) : '';
                            if (Tools::strlen($possible_prefix) < 4 && Tools::strlen($possible_suffix) < 4) {
                                $extensions['prefix'][$id_lang] = $possible_prefix;
                                $extensions['suffix'][$id_lang] = $possible_suffix;
                            }
                        }
                    }
                }
                break;
            case 'w': // weight
                foreach ($this->getAvailableLanguages(true) as $id_lang) {
                    $extensions['prefix'][$id_lang] = '';
                    $extensions['suffix'][$id_lang] = Configuration::get('PS_WEIGHT_UNIT');
                }
                break;
        }

        return $extensions;
    }

    public function adjustFilterFields($filter_data, &$fields)
    {
        $special_filters = array_keys($this->getSpecialFilters());
        $range_filters = ['p', 'w'];
        $numeric_slider_filters = ['a', 'f'];
        if ($filter_data['first_char'] == 'c') {
            $fields['type']['value'] = 2; // default radio for categories
        } else {
            unset($fields['foldered']);
            unset($fields['nesting_lvl']);
            if ($filter_data['first_char'] != 'a') {
                unset($fields['sort_by']['options']['position']);
            }
        }
        if (!in_array($filter_data['key'], $range_filters)) {
            unset($fields['range_step']);
            if (!in_array($filter_data['first_char'], $numeric_slider_filters)) {
                unset($fields['slider_step']);
                unset($fields['slider_prefix']);
                unset($fields['slider_suffix']);
                unset($fields['type']['options'][4]);
            }
        } else {
            if ($filter_data['key'] == 'p') { // prefix-suffux for price is based on selected currency
                unset($fields['slider_prefix']);
                unset($fields['slider_suffix']);
                if ($this->i['p_comb']) {
                    unset($fields['type']['options'][1]);
                    unset($fields['type']['options'][2]);
                    unset($fields['type']['options'][3]);
                    unset($fields['type']['options'][5]);
                }
            }
            unset($fields['and']);
            $fields['type']['value'] = 4; // default slider for $range_filters
        }
        if (in_array($filter_data['key'], $special_filters) || in_array($filter_data['key'], $range_filters)) {
            unset($fields['sort_by']);
            unset($fields['and']);
        }
        if (in_array($filter_data['key'], $special_filters)) {
            unset($fields['type']['options'][2]);
            unset($fields['type']['options'][3]);
            unset($fields['type']['options'][4]);
            unset($fields['visible_items']);
            $fields['quick_search']['class'] .= ' force-hidden';
        }
        if (empty($filter_data['is_color_group'])) {
            unset($fields['color_display']);
        }
    }

    public function getParentCategories($id_lang, $id_shop)
    {
        $parents_data = $this->db->executeS('
            SELECT DISTINCT(cl.id_category) AS id, cl.name AS name, c.position
            FROM ' . _DB_PREFIX_ . 'category c
            INNER JOIN ' . _DB_PREFIX_ . 'category_lang cl
                ON cl.id_category = c.id_parent
                AND cl.id_lang = ' . (int) $id_lang . '
                AND cl.id_shop = ' . (int) $id_shop . '
            WHERE c.level_depth > 2
            ORDER BY cl.name ASC
        ');
        $parent_categories = [];
        foreach ($parents_data as $data) {
            $parent_categories['c' . $data['id']] = $data;
        }

        return $parent_categories;
    }

    public function getSpecialFilters()
    {
        return [
            'newproducts' => $this->l('New products'),
            'bestsales' => $this->l('Best sales'),
            'pricesdrop' => $this->l('Prices drop'),
            'on_sale' => $this->l('On sale!'),
            'in_stock' => $this->l('In stock'),
            'online_only' => $this->l('Online only'),
        ];
    }

    public function getStandardFilters()
    {
        return [
            'p' => $this->l('Price'),
            'w' => $this->l('Weight'),
            'm' => $this->l('Manufacturers'),
            's' => $this->l('Suppliers'),
            't' => $this->l('Tags'),
            'q' => $this->l('Condition'),
        ];
    }

    public function getAvailableFilters($include_parents = true)
    {
        $id_lang = $this->context->language->id;
        $id_shop = $this->context->shop->id;
        $available_filters = [];
        // cats
        $categories = [
            'c' => [
                'id' => 0,
                'name' => $this->l('Categories'),
                'position' => -1,
            ],
        ];
        if ($include_parents) {
            $categories += $this->getParentCategories($id_lang, $id_shop);
        }
        foreach ($categories as $key => $c) {
            $c['prefix'] = $this->l('Subcategories');
            $available_filters[$key] = $c;
        }
        // atts
        $attribute_groups = AttributeGroup::getAttributesGroups($id_lang);
        $attribute_groups = $this->sortByKey($attribute_groups, 'position');
        foreach ($attribute_groups as $group) {
            $name = $group['public_name']
                . ($group['name'] != $group['public_name'] ? ' (' . $group['name'] . ')' : '');
            $available_filters['a' . $group['id_attribute_group']] = [
                'id' => $group['id_attribute_group'],
                'name' => $name,
                'position' => $group['position'],
                'prefix' => $this->l('Attribute'),
                'is_color_group' => !empty($group['is_color_group']),
            ];
        }
        // feats
        $features = Feature::getFeatures($id_lang);  // sorted by position initially
        foreach ($features as $f) {
            $available_filters['f' . $f['id_feature']] = [
                'id' => $f['id_feature'],
                'name' => $f['name'],
                'position' => $f['position'],
                'prefix' => $this->l('Feature'),
            ];
        }
        foreach ($this->getStandardFilters() as $key => $name) {
            $available_filters[$key] = [
                'id' => 0,
                'position' => 0,
                'name' => $name,
                'prefix' => $this->l('Standard parameter'),
            ];
            if ($key == 't' && empty($this->settings['indexation']['t'])) {
                $available_filters[$key]['warning'] = $this->l('Please activate tags data in indexation settings');
            }
        }
        foreach ($this->getSpecialFilters() as $key => $name) {
            $available_filters[$key] = [
                'id' => 0,
                'position' => 0,
                'name' => $name,
                'prefix' => $this->l('Special filter'),
            ];
        }

        return $available_filters;
    }

    public function toggleActiveStatus($id_template, $active)
    {
        if ($active) {
            $current_hook = $this->getCurrentHook();
            $controller_name = $this->getTemplateControllerById($id_template);
            if (!$this->isHookAvailableOnControllerPage($current_hook, $controller_name)) {
                // only left/right column hooks are checked
                $col_txt = ($current_hook == 'displayLeftColumn') ? $this->l('Left') : $this->l('Right');
                $error_txt = sprintf($this->l('%s column is not activated on selected page'), $col_txt);
                $error_txt .= '. ' . $this->howToActivateColumnTxt();
                $this->throwError($error_txt);
            }
        }
        $update_query = '
            UPDATE ' . _DB_PREFIX_ . 'af_templates
            SET active = ' . (int) $active . '
            WHERE id_template = ' . (int) $id_template . '
                AND id_shop IN (' . $this->shopIDs('context', true) . ')
        ';

        return $this->db->execute($update_query) && $this->cache('clear', 'tpl-avl-');
    }

    public function getTemplateControllerById($id_template)
    {
        return $this->db->getValue('
            SELECT template_controller FROM ' . _DB_PREFIX_ . 'af_templates
            WHERE id_template = ' . (int) $id_template . '
        ');
    }

    public function isHookAvailableOnControllerPage($hook_name, $controller_name)
    {
        if ($controller_name == 'seopage') {
            return true;
        }
        $available = true;
        $columns = ['left', 'right'];
        foreach ($columns as $col) {
            if (Tools::strtolower($hook_name) == 'display' . $col . 'column') {
                $page_name = $this->getPageName($controller_name);
                if ($this->is_modern) {
                    $layout = $this->context->shop->theme->getLayoutNameForPage($page_name);
                    $available = $layout == 'layout-both-columns' || $layout == 'layout-' . $col . '-column'
                    || $layout == 'layout-' . $col . '-side-column';
                } else {
                    $method_name = 'has' . Tools::ucfirst($col) . 'Column';
                    $available = $this->context->theme->$method_name($page_name);
                }
            }
        }

        return $available;
    }

    public function ajaxDuplicateTemplate()
    {
        $original_id = Tools::getValue('id_template');
        if ($new_id = $this->duplciateTemplate($original_id)) {
            $ret = $this->callTemplateForm($new_id, false);
            exit(json_encode($ret));
        } else {
            $this->throwError('Error');
        }
    }

    public function duplciateTemplate($id_template_original)
    {
        $id_template_new = $this->getNewTemplateId();
        $sql = [];
        foreach ($this->getTemplateAssociatedTables() as $table_name) {
            $data = $this->db->executeS('
                SELECT * FROM `' . _DB_PREFIX_ . bqSQL($table_name) . '`
                WHERE id_template = ' . (int) $id_template_original . '
            ');
            $new_rows = [];
            foreach ($data as $row) {
                $row['id_template'] = $id_template_new;
                if (isset($row['template_name'])) {
                    $row['template_name'] .= ' ' . $this->l('copy');
                }
                $row = array_map('pSQL', $row); // note: all possible HTML is stripped here!!!
                $new_rows[] = '(\'' . implode('\', \'', $row) . '\')';
            }
            if ($new_rows) {
                $sql[$table_name] = 'REPLACE INTO `' . _DB_PREFIX_ . bqSQL($table_name) . '`
                    VALUES ' . implode(', ', $new_rows);
            }
        }

        return $this->runSql($sql) ? $id_template_new : false;
    }

    public function templateCanBeDeleted($id_template)
    {
        return $this->db->getValue('
            SELECT id_template FROM ' . _DB_PREFIX_ . 'af_templates
            WHERE template_controller = \'' . pSQL($this->getTemplateControllerById($id_template)) . '\'
                AND id_template <> ' . (int) $id_template . '
        ');
    }

    public function ajaxDeleteTemplate()
    {
        $id_template = Tools::getValue('id_template');
        if ($this->templateCanBeDeleted($id_template)) {
            exit(json_encode(['success' => $this->deleteTemplate($id_template)]));
        } else {
            $this->throwError($this->l('You can not delete this template, but you can turn it off'));
        }
    }

    public function deleteTemplate($id_template)
    {
        $sql = [];
        foreach ($this->getTemplateAssociatedTables() as $table_name) {
            $sql[] = 'DELETE FROM `' . _DB_PREFIX_ . bqSQL($table_name) . '`
                WHERE id_template = ' . (int) $id_template . '
                AND id_shop IN (' . $this->shopIDs('context', true) . ')';
        }

        return $this->runSql($sql);
    }

    public function getTemplateAssociatedTables()
    {
        $tables = ['af_templates', 'af_templates_lang'];
        foreach ($this->getControllersWithMultipleIDs() as $controller) {
            $tables[] = 'af_' . $controller . '_templates';
        }

        return $tables;
    }

    public function ajaxSaveTemplate()
    {
        $id_template = Tools::getValue('id_template');
        $template_controller = Tools::getValue('template_controller');
        $template_name = Tools::getValue('template_name');
        $filters_data = Tools::getValue('filters', []);
        $controller_ids = Tools::getValue('controller_ids');
        // additional settings
        $available_additional_settings = Tools::getValue('additional_settings');
        $unlocked_additional_settings = Tools::getValue('unlocked_additional_settings', []);
        $additional_settings = [];
        foreach (array_keys($unlocked_additional_settings) as $name) {
            if (isset($available_additional_settings[$name])) {
                $additional_settings[$name] = $available_additional_settings[$name];
            }
        }
        $errors = $this->validateSettings($additional_settings, $this->getSettingsFields('general'));
        if ($template_name == '') {
            $errors['no_name'] = $this->l('Please add a template name');
        }
        if ($errors) {
            $this->throwError($errors);
        }
        if (!$saved = $this->saveTemplate(
            $id_template,
            $template_controller,
            $template_name,
            $filters_data,
            $controller_ids,
            $additional_settings
        )) {
            $this->throwError($this->l('Template not saved'));
        }
        $ret = ['success' => $saved];
        exit(json_encode($ret));
    }

    public function ajaxUpdateHook()
    {
        $ret = $pages_without_this_hook = $warning = [];
        $new_hook = Tools::getValue('hook_name');
        foreach (array_keys(array_filter($this->getAvailableHooks())) as $hook) {
            $this->unregisterHook($hook, $this->shopIDs());
        }
        if ($ret['success'] = $this->registerHook($new_hook, $this->shopIDs())) {
            $this->updatePosition(Hook::getIdByName($new_hook), 0, 1);
            $ret['positions_form_html'] = $this->renderHookPositionsForm($new_hook);
            $horizontal_layout_hooks = ['displayTopColumn', 'displayHeaderCategory'];
            $required_layout = in_array($new_hook, $horizontal_layout_hooks) ? 'horizontal' : 'vertical';
            if ($this->settings['general']['layout'] != $required_layout) {
                $ret['upd_settings'] = ['layout' => $required_layout];
                if ($required_layout == 'horizontal') {
                    $ret['upd_settings']['sf_position'] = 1;
                }
                foreach ($this->shopIDs() as $id_shop) {
                    $settings = $this->db->getValue('
                        SELECT value FROM ' . _DB_PREFIX_ . 'af_settings
                        WHERE type = \'general\' AND id_shop = ' . (int) $id_shop . '
                    ');
                    $settings = $settings ? json_decode($settings, true) : [];
                    $this->saveSettings('general', array_merge($settings, $ret['upd_settings']), [$id_shop]);
                }
                $warning[] = sprintf($this->l('Layout type was updated to "%s"'), $required_layout);
            }
            $active_templates = $this->db->executeS('
                SELECT * FROM ' . _DB_PREFIX_ . 'af_templates WHERE active = 1
            ');
            foreach ($active_templates as $t) {
                if (!$this->isHookAvailableOnControllerPage($new_hook, $t['template_controller'])) {
                    $pages_without_this_hook[$t['template_controller']] = $t['template_controller'];
                }
            }
            if ($pages_without_this_hook) {  // warning if some pages do not have selected hook
                $txt = sprintf($this->l('Module was succesfully hooked to %s'), $new_hook) . ', ';
                $txt .= $this->l('but this column is not activated for the following pages') . ':<br>';
                ksort($pages_without_this_hook);
                foreach ($pages_without_this_hook as $controller_name) {
                    $txt .= '- ' . $controller_name . '<br>';
                }
                $txt .= $this->howToActivateColumnTxt();
                $warning[] = $txt;
            }
            if ($warning) {
                $ret['warning'] = implode('<br>-----<br>', $warning);
            }
        }
        exit(json_encode($ret));
    }

    public function howToActivateColumnTxt()
    {
        $txt = $this->l('You can activate it in %s');
        if ($this->is_modern) {
            $sprintf = $this->l('Design > Theme & Logo > Choose layouts');
        } else {
            $sprintf = $this->l('Preferences > Themes > Advanced settings');
        }

        return sprintf($txt, $sprintf);
    }

    public function renderHookPositionsForm($hook_name)
    {
        $this->context->smarty->assign([
            'hook_modules' => $this->getHookModulesInfos($hook_name),
            'hook_name' => $hook_name,
        ]);

        return $this->display($this->local_path, 'views/templates/admin/hook-positions-form.tpl');
    }

    public function getHookModulesInfos($hook_name)
    {
        $hook_modules = Hook::getModulesFromHook(Hook::getIdByName($hook_name));
        $sorted = [];
        foreach ($hook_modules as $m) {
            if ($instance = Module::getInstanceByName($m['name'])) {
                $logo_src = false;
                if (file_exists(_PS_MODULE_DIR_ . $instance->name . '/logo.png')) {
                    $logo_src = _MODULE_DIR_ . $instance->name . '/logo.png';
                }
                $sorted[$m['id_module']] = [
                    'name' => $instance->name,
                    'position' => $m['m.position'],
                    'enabled' => $instance->isEnabledForShopContext(),
                    'display_name' => $instance->displayName,
                    'description' => $instance->description,
                    'logo_src' => $logo_src,
                ];
                if ($m['id_module'] == $this->id) {
                    $sorted[$m['id_module']]['current'] = 1;
                }
            }
        }

        return $sorted;
    }

    public function getDefaultFiltersData()
    {
        $filters_data = [
            'c' => ['type' => 2, 'nesting_lvl' => 0, 'foldered' => 1],
            'p' => ['type' => 4, 'slider_step' => 1],
            'm' => ['type' => 3],
            'multilang' => [],
        ];

        return $filters_data;
    }

    public function prepareMultilangData(&$filters_data)
    {
        $sorted_data = [];
        if (isset($filters_data['multilang'])) {
            foreach ($filters_data['multilang'] as $filter_key => $multilang_values) {
                foreach ($multilang_values as $name => $values) {
                    foreach ($values as $id_lang => $value) {
                        $sorted_data[$id_lang][$filter_key][$name] = strip_tags($value);
                    }
                }
            }
            unset($filters_data['multilang']);
        }

        return $sorted_data;
    }

    public function validateSettings(&$values, $fields, $update_values = true)
    {
        $errors = [];
        foreach ($values as $name => &$value) {
            if (isset($fields[$name])) {
                if ($error = $this->validateField($value, $fields[$name], $update_values, true)) {
                    $errors[$name] = $error;
                }
            } elseif ($update_values) {
                unset($values[$name]);
            }
        }

        return $errors;
    }

    public function validateField(&$value, $field, $update_value = true, $error_label = true)
    {
        if ($field['type'] == 'cf_dynamic') {
            return $this->customerFilters()->validateDynamicField($value, $field, $update_value, $error_label);
        } elseif (!empty($field['multilang'])) {
            return $this->validateMultilangField($value, $field, $update_value, $error_label);
        }
        $error = false;
        $rule = isset($field['validate']) ? $field['validate'] : false;
        if (!$rule && $field['type'] == 'text' && empty($field['allow_html'])) {
            $rule = 'isGenericName';
        }
        if ($value === '' && !empty($field['required'])) {
            $error = sprintf($this->l('%s: please fill this value'), $field['display_name']);
        } elseif ($rule && !Validate::$rule($value)) {
            $error = ($error_label ? $field['display_name'] . ': ' : '') . $this->l('incorrect value');
        }
        if ($error && $update_value && isset($field['default_value'])) {
            $value = $field['default_value'];
        }

        return $error;
    }

    public function validateMultilangField(&$ml_value, $field, $update_value, $error_label)
    {
        $ml_value = $this->fillMultilangValue($ml_value, !empty($field['required']), !empty($field['skip_empty']));
        $multilang_error = [];
        $field_to_validate = $field;
        unset($field_to_validate['multilang']);
        foreach ($ml_value as $id_lang => &$v) {
            if ($err = $this->validateField($v, $field_to_validate, $update_value, $error_label)) {
                if (!isset($multilang_error[$err])) {
                    $multilang_error[$err] = $err . ' (' . Language::getIsoById($id_lang);
                } else {
                    $multilang_error[$err] .= ', ' . Language::getIsoById($id_lang);
                }
            }
        }

        return $multilang_error ? implode('), ', $multilang_error) . ')' : false;
    }

    public function fillMultilangValue($ml_value, $autofill = false, $skip_empty = false)
    {
        $id_lang_default = Configuration::get('PS_LANG_DEFAULT');
        if (!is_array($ml_value)) {
            $ml_value = [$id_lang_default => $ml_value];
        }
        $ml_value = array_filter($ml_value);
        if (!$skip_empty) {
            $autofill_value = $autofill && isset($ml_value[$id_lang_default]) ? $ml_value[$id_lang_default] : '';
            $ml_value += array_fill_keys($this->getAvailableLanguages(true), $autofill_value);
        }

        return $ml_value;
    }

    public function saveTemplate(
        $id_template,
        $template_controller,
        $template_name,
        $filters_data = [],
        $controller_ids = [],
        $additional_settings = []
    ) {
        if (!$id_template) {
            $id_template = $this->getNewTemplateId();
            $additional_settings += $this->getDefaultAdditionalSettings($template_controller);
        }
        $multilang_data = $this->prepareMultilangData($filters_data);
        $this->validateTempalateFilters($filters_data, $template_controller);
        $current_hook = $this->getCurrentHook();
        // active status is inserted only first time. After that it is updated using toggleActiveStatus
        $active = $this->isHookAvailableOnControllerPage($current_hook, $template_controller);
        $template_rows = $template_lang_rows = $controller_ids_rows = [];
        $shop_ids = $this->shopIDs();
        if (count($shop_ids) > 1 && $controller_ids) {
            // in some cases templates can be associated to cat/man/sup that are not available in all shops
            $shop_table = $template_controller == 'seopage' ? 'af_seopage_lang' : $template_controller . '_shop';
            $shop_ids = array_intersect($shop_ids, array_column($this->db->executeS('
                SELECT DISTINCT(`id_shop`) FROM `' . _DB_PREFIX_ . bqSQL($shop_table) . '`
                WHERE `id_' . bqSQL($template_controller) . '` IN (' . $this->sqlIDs($controller_ids) . ')
            '), 'id_shop'));
        }
        foreach ($shop_ids as $id_shop) {
            $template_rows[] = '(
                ' . (int) $id_template . ',
                ' . (int) $id_shop . ',
                \'' . pSQL($template_controller) . '\',
                ' . (int) $active . ',
                \'' . pSQL($template_name) . '\',
                \'' . pSQL(json_encode($filters_data)) . '\',
                \'' . pSQL(json_encode($additional_settings)) . '\'
            )';
            if (in_array($template_controller, $this->getControllersWithMultipleIDs())) {
                $controller_ids = $controller_ids ?: [0];
                foreach ($controller_ids as $id) {
                    $controller_ids_rows[$id . '_' . $id_shop] = '(' . (int) $id . ', ' . (int) $id_template
                        . ', ' . (int) $id_shop . ')';
                }
            }
            foreach ($multilang_data as $id_lang => $data) {
                $template_lang_rows[] = '(' . (int) $id_template . ', ' . (int) $id_shop . ', ' . (int) $id_lang
                    . ', \'' . pSQL(json_encode($data)) . '\')';
            }
        }
        $sql = [];
        if ($template_rows) {
            $sql['template_data'] = '
                INSERT INTO ' . _DB_PREFIX_ . 'af_templates
                VALUES ' . implode(', ', $template_rows) . '
                ON DUPLICATE KEY UPDATE
                    template_name=VALUES(template_name),
                    template_controller=VALUES(template_controller),
                    template_filters=VALUES(template_filters),
                    additional_settings=VALUES(additional_settings)
            ';
        }
        if ($template_lang_rows) {
            $sql['template_lang_data'] = '
                INSERT INTO ' . _DB_PREFIX_ . 'af_templates_lang
                VALUES ' . implode(', ', $template_lang_rows) . '
                ON DUPLICATE KEY UPDATE
                    data = VALUES(data)
            ';
        }
        if ($controller_ids_rows) {
            $t_name = _DB_PREFIX_ . 'af_' . $template_controller . '_templates';
            $sql['controller_ids_delete'] = '
                DELETE FROM `' . bqSQL($t_name) . '`
                WHERE `id_template` = ' . (int) $id_template . '
                    AND `id_shop` IN (' . $this->shopIDs('context', true) . ')
            ';
            $sql['controller_ids_insert'] = '
                REPLACE INTO `' . bqSQL($t_name) . '` VALUES ' . implode(', ', $controller_ids_rows) . '
            ';
        }

        return $this->runSql($sql) ? $id_template : false;
    }

    public function validateTempalateFilters(&$filters_data, $template_controller)
    {
        if ($template_controller == 'manufacturer' && isset($filters_data['m'])) {
            unset($filters_data['m']);
        }
        if ($template_controller == 'supplier' && isset($filters_data['s'])) {
            unset($filters_data['s']);
        }
        foreach ($filters_data as &$f) {
            if (isset($f['range_step'])) {
                $f['range_step'] = trim(preg_replace('/[^0-9,minmax-]/', '', $f['range_step']), ',') ?: 100;
            }
            if (isset($f['slider_step'])) {
                $step = (float) str_replace(',', '.', $f['slider_step']);
                $f['slider_step'] = Toolkit::removeScientificNotation($step) ?: 1;
            }
            if (in_array($f['type'], [3, 4])) {
                $f['quick_search'] = 0; // no quick_search for sliders and selects
            }
            if (isset($f['visible_items'])) {
                $f['visible_items'] = (int) $f['visible_items'] ?: '';
            }
        }
    }

    public function parseStr($str, $sanitize = false)
    {
        $params = [];
        parse_str(str_replace('&amp;', '&', $str), $params);
        if ($sanitize) {
            $params = $this->sanitizeValue($params);
        }

        return $params;
    }

    /**
     * af_templates table has a composite KEY that cannot be autoincremented.
     **/
    public function getNewTemplateId()
    {
        $max_id = $this->db->getValue('SELECT MAX(id_template) FROM ' . _DB_PREFIX_ . 'af_templates');

        return (int) $max_id + 1;
    }

    public function addJS($file_name, $custom_path = '')
    {
        $path = ($custom_path ? $custom_path : 'modules/' . $this->name . '/views/js/') . $file_name;
        if ($this->is_modern) {
            // priority should be more than 90 in order to be loaded after jqueryUI
            $params = ['server' => $custom_path ? 'remote' : 'local', 'priority' => 100];
            $this->context->controller->registerJavascript(sha1($path), $path, $params);
        } else {
            $path = $custom_path ? $path : __PS_BASE_URI__ . $path;
            $this->context->controller->addJS($path);
        }
    }

    public function addCSS($file_name, $custom_path = '', $media = 'all')
    {
        $path = ($custom_path ? $custom_path : 'modules/' . $this->name . '/views/css/') . $file_name;
        if ($this->is_modern) {
            $params = ['media' => $media, 'server' => $custom_path ? 'remote' : 'local'];
            $this->context->controller->registerStylesheet(sha1($path), $path, $params);
        } else {
            $path = $custom_path ? $path : __PS_BASE_URI__ . $path;
            $this->context->controller->addCSS($path, $media);
        }
    }

    public function isMobilePhone()
    {
        return $this->context->getDevice() == Context::DEVICE_MOBILE;
    }

    public function isTablet()
    {
        return $this->context->getDevice() == Context::DEVICE_TABLET;
    }

    public function addCustomMedia()
    {
        foreach (['css', 'js'] as $type) {
            $path = 'specific/' . $this->getSpecificThemeIdentifier() . '.' . $type;
            if (file_exists(_PS_MODULE_DIR_ . $this->name . '/views/' . $type . '/' . $path)) {
                $method_name = 'add' . Tools::strtoupper($type);
                $this->$method_name($path);
            }
        }
        $this->addJS('custom.js');
        $this->addCSS('custom.css');
    }

    public function loadIconFontIfRequired()
    {
        if (!empty($this->settings['iconclass']['load_font'])) {
            $this->addCSS('icons.css');
        }
    }

    public function hookDisplayHeader()
    {
        $css = '';
        if ($af_displayed = $this->defineFilterParams()) {
            $this->addJS('front.js');
            $this->addCSS('front.css');
            if ($this->context->language->is_rtl) {
                $this->addCSS('rtl.css');
            }
            $this->loadIconFontIfRequired();
            if (!empty($this->slider_required)) {
                $this->addJS('slider.js');
                $this->addCSS('slider.css');
                if ($this->context->language->is_rtl) {
                    $this->addJS('rtl-slider.js');
                }
            }
            $this->addCustomMedia();
            $load_more = $this->settings['general']['p_type'] > 1;
            $js_def = [
                'af_ajax' => $this->ajaxSetup(),
                'load_more' => $load_more,
                'af_product_count_text' => htmlspecialchars_decode($this->products_data['product_count_text']),
                'show_load_more_btn' => !$this->products_data['hide_load_more_btn'],
                'af_product_list_class' => $this->product_list_class,
                'af_param_names' => $this->param_names,
                'af_sep' => Toolkit::$sep,
                'af_classes' => $this->getLayoutClasses(),
                'af_ids' => $this->settings['themeid'],
                'af_is_modern' => (int) $this->is_modern,
            ];
            if (!$this->is_modern) {
                $js_def['af_product_count_text'] = addslashes($js_def['af_product_count_text']);
                $js_def['af_upd_search_form'] = $this->isTemplateAvailable('search');
                $js_def += $this->comparatorJsVars();
                $this->addJS('front-16.js');
                $this->addCSS('front-16.css');
            }
            if ($load_more) { // hide pagination if load more is used
                $css .= ($this->is_modern ? '.af_pl_wrapper ' : '') .
                '.' . $this->settings['themeclass']['pagination'] . '{display:none;}';
            }
            if ($compact_w = (int) $this->settings['general']['compact']) {
                // position:fixed will be used to detect compact view in front.js
                $css .= '@media(max-width:' . (int) $compact_w . 'px){#amazzing_filter{position:fixed;opacity:0;}}';
            }
            Media::addJsDef($js_def);
        }
        if (Configuration::get('AF_CF')) {
            $this->customerFilters()->extendHeader($af_displayed);
        }

        return $css ? '<style type="text/css">' . $css . '</style>' : '';
    }

    public function ajaxSetup()
    {
        return [
            'path' => $this->context->link->getModuleLink($this->name, 'ajax', ['ajax' => 1]),
            'token' => $this->ajaxToken(),
        ];
    }

    public function ajaxToken()
    {
        return md5(_COOKIE_KEY_ . (int) $this->context->customer->id . '_' . $this->context->customer->passwd . '_af');
    }

    public function isTemplateAvailable($controller)
    {
        $cache_id = 'tpl-avl-' . $controller . '-' . $this->id_shop;
        $is_available = $this->cache('get', $cache_id);
        if ($is_available === false) {
            $is_available = (int) $this->db->getValue('
                SELECT id_template FROM ' . _DB_PREFIX_ . 'af_templates
                WHERE template_controller = \'' . pSQL($controller) . '\'
                AND id_shop = ' . (int) $this->id_shop . ' AND active = 1
            ');
            $this->cache('save', $cache_id, $is_available);
        }

        return $is_available;
    }

    public function getInitialFiltersByGroup($filter_group)
    {
        $values = $this->getSafeValue($filter_group);

        return $values ? explode(',', $values) : [];
    }

    public function getSubcategories($id_lang, $id_parent = false, $nesting_lvl = 0, $check_group_access = false)
    {
        $id_parent = $id_parent ?: $this->context->shop->getCategory();
        $current_category_data = $this->db->getRow('
            SELECT * FROM ' . _DB_PREFIX_ . 'category
            WHERE id_category = ' . (int) $id_parent . '
        ');
        $customer_groups_ = $check_group_access ? $this->customerGroupIDs(true) : '';
        $max_depth = $nesting_lvl ? $current_category_data['level_depth'] + $nesting_lvl : 0;
        $categories = $this->db->executeS('
            SELECT c.id_category AS id, c.id_parent, cl.name, cl.link_rewrite AS link, category_shop.position
            FROM ' . _DB_PREFIX_ . 'category c
            ' . Shop::addSqlAssociation('category', 'c') . '
            LEFT JOIN ' . _DB_PREFIX_ . 'category_lang cl
                ON c.id_category = cl.id_category
            ' . ($customer_groups_ ? 'INNER JOIN ' . _DB_PREFIX_ . 'category_group cg
                 ON cg.id_category = c.id_category
                 AND cg.id_group IN (' . $customer_groups_ . ')' : '') . '
            WHERE cl.id_lang = ' . (int) $id_lang . '
            AND c.active = 1
            AND c.nright < ' . (int) $current_category_data['nright'] . '
            AND c.nleft > ' . (int) $current_category_data['nleft'] . '
            ' . ($max_depth ? 'AND c.level_depth <= ' . (int) $max_depth : '') . '
            AND cl.id_shop = ' . (int) $this->id_shop . '
            GROUP BY c.id_category
            ORDER BY cl.name ASC, c.id_category ASC
        ');

        return $categories;
    }

    public function getGroupName($f)
    {
        $t_names = ['c' => 'category', 'a' => 'attribute_group', 'f' => 'feature'];
        $resource_type = isset($t_names[$f['first_char']]) ? $t_names[$f['first_char']] : '';

        return $resource_type ? $this->db->getValue('
            SELECT name FROM `' . _DB_PREFIX_ . bqSQL($resource_type) . '_lang`
            WHERE `id_' . bqSQL($resource_type) . '` = ' . (int) $f['id_group'] . '
            AND `id_lang` = ' . (int) $this->id_lang
                . ($resource_type == 'category' ? ' AND id_shop = ' . (int) $this->id_shop : '') . '
        ') : '';
    }

    public function prepareTplVariables($current_filters)
    {
        $filters = $this->prepareFiltersData($current_filters);
        $initial_params = $this->prepareInitialParams($filters);
        $hidden_inputs = $this->prepareHiddenInputs();
        $f_params = $hidden_inputs + $initial_params + ['count_all_matches' => 1];
        $this->products_data = $this->getFilteredProducts($f_params);
        if (!$this->products_data['filtered_ids_count'] && !array_column($filters, 'has_selection')) {
            $filters = [];
        }
        $this->preparePaginationVars($f_params);
        $this->context->smarty->assign([
            'filters' => $this->prepareFiltersForDisplay($filters, $f_params),
            'hidden_inputs' => $hidden_inputs,
            'extra_hidden_inputs' => [
                'available_options' => $f_params['available_options'],
                'numeric_slider_values' => $f_params['numeric_slider_values'],
                'and' => $f_params['and'],
            ],
            'count_data' => $this->products_data['count_data'],
            'class' => $this->product_list_class, // used in product-list.tpl
            'af_classes' => $this->getLayoutClasses(),
            'af_ids' => $this->settings['themeid'],
            'total_products' => $this->products_data['filtered_ids_count'],
            'is_modern' => $this->is_modern,
            'af_layout_type' => $this->settings['general']['layout'],
            'af' => 1,
            'is_iphone' => $this->isIphone(),
        ]);
        $this->context->filtered_result = [
            'products' => $this->products_data['products'],
            'total' => $this->products_data['filtered_ids_count'],
            'controller' => $this->current_controller,
            'sorting' => $f_params['orderBy'] . '.' . $f_params['orderWay'],
        ];
        if (!empty($this->x['hook_search_query'])) {
            Hook::exec('actionSearch', [
                'searched_query' => $this->x['hook_search_query'],
                'total' => $this->products_data['filtered_ids_count'],
                'expr' => $this->x['hook_search_query'], // deprecated since 1.7.x
            ]);
        }
    }

    public function isIphone()
    {
        if (!isset($this->context->cookie->is_iphone)) {
            $this->context->cookie->__set('is_iphone', (int) $this->context->getMobileDetect()->isIphone());
        }

        return $this->context->cookie->is_iphone;
    }

    public function prepareFiltersData($filters)
    {
        $standard_filters = $this->getStandardFilters();
        $special_filters = $this->getSpecialFilters();
        $range_filters = ['p' => $this->l('Price'), 'w' => $this->l('Weight')];
        $predefined_group_names = $standard_filters + $range_filters;
        $horizontal_layout = $this->settings['general']['layout'] == 'horizontal';
        foreach ($filters as $key => &$f) {
            $f['name'] = !empty($f['custom_name']) ? $f['custom_name'] : '';
            $f['classes'] = [];
            if ($f['type'] == 5) {
                $f['type'] = $f['textbox'] = 1;
                $f['color_display'] = 0;
            }
            if ($f['special'] = isset($special_filters[$key])) {
                $f['first_char'] = $key;
                $f['id_group'] = $f['is_slider'] = 0;
                $f['name'] = $f['name'] ?: $special_filters[$key];
                $f['values'] = [1 => ['name' => $f['name'], 'id' => 1, 'link' => 1, 'identifier' => $key]];
            } else {
                $f['first_char'] = Tools::substr($key, 0, 1);
                $f['id_group'] = (int) Tools::substr($key, 1);
                if ($this->i['p_comb'] && $f['first_char'] == 'p') {
                    $f['type'] = 4; // force slider
                }
                $f['is_slider'] = $f['type'] == 4 ? 1 : 0;
                if ($f['first_char'] == 'c') {
                    if (!$f['id_parent'] = $f['id_group']) {
                        $f['id_parent'] = $f['id_group'] = $this->id_cat_current;
                        $f['name'] = $f['name'] ?: $this->l('Categories');
                    }
                }
                $f['values'] = $this->getFilterValues($f, $key);
                if ($f['is_slider'] || isset($range_filters[$key])) {
                    $this->slider()->setExtensions($f, $this->is_modern);
                }
                if (!$f['name']) {
                    $first_value = current($f['values']) ?: [];
                    if (isset($predefined_group_names[$key])) {
                        $f['name'] = $predefined_group_names[$key];
                    } elseif (isset($first_value['group_name'])) { // attributes, features. Can be optimized
                        $f['name'] = $first_value['group_name'];
                    } else {
                        $f['name'] = $this->getGroupName($f);
                    }
                }
                if ($horizontal_layout) {
                    $f['minimized'] = 1;
                }
            }
            $f['submit_name'] = 'filters[' . $f['first_char'] . '][' . $f['id_group'] . '][]';
            $f['link'] = $this->generateLink($f['name'], $key);
        }

        return $filters;
    }

    public function prepareInitialParams(&$filters)
    {
        $initial_params = array_fill_keys(['available_options', 'numeric_slider_values', 'sliders', 'and'], []);
        foreach ($filters as $key => &$f) {
            $submitted_data = $this->getInitialFiltersByGroup($f['link']);
            foreach ($f['values'] as $id => $v) {
                $initial_params['available_options'][$f['first_char']][$f['id_group']][$id] = $id;
                if ($f['is_slider']) {
                    // NOTE: keep 'numeric_slider_values' synchronized with 'available_options'
                    $initial_params['numeric_slider_values'][$f['first_char']][$f['id_group']][$id] = $v['num'];
                } elseif ($f['values'][$id]['selected'] = in_array($v['link'], $submitted_data)) {
                    $f['has_selection'] = 1;
                    $initial_params['filters'][$f['first_char']][$f['id_group']][$id] = $id;
                }
            }
            if ($f['is_slider']) {
                $f['values'] = [];
                if (!empty($submitted_data)) {
                    $f['has_selection'] = 1;
                    $range = Toolkit::defineRange($submitted_data[0], false, null, true, true);
                    $f['values'] = ['from' => $range[0], 'to' => $range[1]];
                }
                $initial_params['sliders'][$f['first_char']][$f['id_group']] = $f['values'];
            } elseif (isset($f['range_step'])) {
                $initial_params[$key . '_range_step'] = $f['range_step'];
                $initial_params['available_options'][$f['first_char']] = [];
                if (!empty($submitted_data)) {
                    $f['has_selection'] = 1;
                    $initial_params['filters'][$key][0] = $submitted_data;
                    // values will be defined later in rangeFilter()->prepareAll()
                }
            } elseif (!empty($f['and']) && $f['type'] == 1) {
                $initial_params['and'][$f['first_char']][$f['id_group']] = [1]; // array for {$extra_hidden_inputs}
            }
        }
        if (!empty($this->settings['cf']['keys'])) {
            $this->customerFilters()->extendInitialParams($filters, $initial_params);
        }
        if (!empty($this->sp)) {
            $this->sp->extendInitialParams($filters, $initial_params, $this->current_controller);
        }

        return $initial_params;
    }

    public function prepareHiddenInputs()
    {
        $this->defineProductSorting();
        $hidden_inputs = [
            'id_category' => $this->id_cat_current,
            'id_manufacturer' => (int) Tools::getValue('id_manufacturer'),
            'id_supplier' => (int) Tools::getValue('id_supplier'),
            'page' => (int) Tools::getValue($this->param_names['p'], 1),
            'nb_items' => $this->getNbItems(),
            'controller_product_ids' => implode(',', $this->controller_product_ids),
            'current_controller' => $this->current_controller,
            'page_name' => $this->getPageName($this->current_controller),
            'orderBy' => $this->context->forced_sorting['by'],
            'orderWay' => $this->context->forced_sorting['way'],
            'customer_groups' => $this->customerGroupIDs(true),
            'random_seed' => $this->getRandomSeed($this->settings['general']['random_upd']),
        ];
        if (!$this->is_modern) {
            $pb_id = $this->settings['themeid']['pagination_bottom'];
            $pb_suffix = str_replace($this->settings['themeid']['pagination'] . '_', '', $pb_id);
            $hidden_inputs['pagination_bottom_suffix'] = $pb_suffix;
            $hidden_inputs['hide_right_column'] = !$this->context->controller->display_column_right;
            $hidden_inputs['hide_left_column'] = !$this->context->controller->display_column_left;
        }
        $hidden_inputs += $this->settings['general'];
        unset($hidden_inputs['sorting_options']); // TODO: review the list of hidden inputs

        return $hidden_inputs;
    }

    public function getNbItems()
    {
        $nb_items = $this->settings['general']['npp'];
        $this->nb_items_options = [$nb_items, $nb_items * 2, $nb_items * 5];
        if ($custom_nb_items = (int) Tools::getValue($this->param_names['n'])) {
            $nb_items = $custom_nb_items;
            if (!$this->is_modern && !in_array($custom_nb_items, $this->nb_items_options)) {
                $this->nb_items_options[] = $custom_nb_items;
                sort($this->nb_items_options);
            }
        }

        return $nb_items;
    }

    public function preparePaginationVars($params)
    {
        $params['total_products'] = $this->products_data['filtered_ids_count'];
        $this->validatePageNumber($params);
        if ($this->is_modern) {
            $this->context->forced_nb_items = $params['nb_items'];
        } else {
            $this->assignCustomPaginationAndSorting($params);
        }
    }

    public function setClasses(&$f, $key)
    {
        $f['classes'] += array_filter([
            $key => 1,
            'clearfix' => 1,
            'has-slider' => $f['is_slider'],
            'type-' . $f['type'] => !$f['is_slider'],
            'tb' => !empty($f['textbox']),
            'special' => $f['special'],
            'folderable' => isset($f['foldered']),
            'foldered' => !empty($f['foldered']),
            'closed' => !empty($f['minimized']),
            'has-selection' => !empty($f['has_selection']),
            'sort-by-matches' => isset($f['sort_by']) && $f['sort_by'] == 'matches'
                && isset($this->products_data['count_data'][$f['first_char']]),
        ]);
    }

    public function prepareFiltersForDisplay($filters, &$params)
    {
        if (!empty($this->products_data['ranges'])) {
            $this->rangeFilter()->prepareAll($this->products_data['ranges'], $filters, $params);
        }
        $this->prepareSliderFilters($filters, $params);
        foreach ($filters as $key => &$f) {
            $this->setClasses($f, $key);
            if ($f['is_slider']) {
                continue; // processed in prepareSliderFilters
            }
            if ($this->products_data['count_data'] && empty($f['has_selection'])) {
                $f['classes']['no-available-items'] = 1;
            }
            if ($f['first_char'] == 'c' && $f['nesting_lvl'] != 1
                && !$this->settings['indexation']['subcat_products']) {
                $parent_ids = array_keys($this->prepareTreeValues($f['values'], $f['id_parent']));
                foreach ($parent_ids as $id_parent) { // keep upper level categories without matches in tree
                    $this->products_data['all_matches']['c'][$id_parent] = 1;
                }
            }
            $remove_unused_options = in_array($f['first_char'], ['a', 'f', 'c', 'm']);
            foreach ($f['values'] as $i => &$v) {
                if ($remove_unused_options && empty($v['selected'])
                    && !isset($this->products_data['all_matches'][$f['first_char']][$v['id']])) {
                    unset($f['values'][$i]);
                    unset($params['available_options'][$f['first_char']][$f['id_group']][$v['id']]);
                    continue;
                }
                if (!empty($f['classes']['no-available-items'])
                    && !empty($this->products_data['count_data'][$f['first_char']][$v['id']])) {
                    unset($f['classes']['no-available-items']);
                    if (!$remove_unused_options) {
                        break; // will not break for colors
                    }
                }
                if (!empty($f['color_display'])) {
                    $this->setColorStyle($v);
                }
            }
            if (empty($f['values'])) {
                unset($filters[$key]);
                unset($params['available_options'][$f['first_char']][$f['id_group']]);
            } else {
                if (!empty($f['visible_items']) && $f['type'] < 3 && $f['visible_items'] < count($f['values'])) {
                    $f['cut_off'] = $f['classes']['cut-off'] = $f['visible_items'];
                }
                if (!empty($f['sort_by'])) {
                    $f['values'] = $this->sortByKey($f['values'], $f['sort_by'], $f['first_char']);
                }
                if (!empty($f['quick_search'])) {
                    $f['quick_search'] = count($f['values']) >= $this->qs_min_values; // before prepareTreeValues()
                }
                if ($f['first_char'] == 'c'
                    && !$f['values'] = $this->prepareTreeValues($f['values'], $f['id_parent'])) {
                    unset($filters[$key]);
                    unset($params['available_options'][$f['first_char']][$f['id_group']]);
                }
            }
        }

        return $filters;
    }

    public function prepareSliderFilters(&$filters, &$params)
    {
        foreach ($params['sliders'] as $key => $grouped_sliders) {
            foreach ($grouped_sliders as $id_group => $slider) {
                $f_key = $key . ($id_group ?: '');
                if (!isset($filters[$f_key])) {
                    continue;
                }
                if (isset($slider['numeric_values'])) {
                    foreach (array_keys($slider['numeric_values']) as $id) {
                        if (!isset($this->products_data['all_matches'][$key][$id])) {
                            unset($slider['numeric_values'][$id]);
                            unset($params['available_options'][$key][$id_group][$id]);
                            unset($params['numeric_slider_values'][$key][$id_group][$id]);
                        }
                    }
                    $filters[$f_key]['values'] += $this->slider()->getMinMax($slider['numeric_values']);
                } elseif (isset($this->products_data['ranges'][$f_key])) {
                    $filters[$f_key]['values'] += $this->products_data['ranges'][$f_key];
                }
                if (!empty($filters[$f_key]['values'])) {
                    $filters[$f_key]['values'] = $this->slider()->fillValues($filters[$f_key]['values']);
                    if ($filters[$f_key]['values']['max'] == 0) {
                        $filters[$f_key]['classes']['no-available-items'] = 1;
                    }
                    $filters[$f_key]['submit_name'] = 'sliders[' . $key . '][' . $id_group . ']';
                    $this->slider_required = 1; // will be used to load slider script
                }
            }
        }
    }

    public function getCacheIDForFilterValues($f)
    {
        $id = $f['first_char'] . '_list';
        if (!empty($this->settings['caching'][$id])) {
            if ($f['first_char'] == 'c') {
                $id .= '_' . $f['id_parent'] . '_' . $f['nesting_lvl'] . '_' . implode('_', $this->customerGroupIDs());
            } else {
                $id .= '_' . $f['id_group'];
            }

            return $id . '_' . $f['is_slider'] . '_' . $this->id_shop . '_' . $this->id_lang;
        }
    }

    public function getFilterValues($f, $key)
    {
        $cache_id = $this->getCacheIDForFilterValues($f);
        if ($cache_id && $values = $this->cache('get', $cache_id)) {
            return $values;
        }
        $values = [];
        foreach ($this->getRawFilterValues($f) as $v) {
            if ($f['is_slider']) {
                $numbers = Toolkit::extractNumbers($v['name'], true) ?: [0];
                $v['num'] = implode(Toolkit::$sep['all']['range'], $numbers);
            } else {
                $v['identifier'] = $f['first_char'] . '-' . $v['id'];
                if (!isset($v['link'])) {
                    $v['link'] = $this->generateLink($v['name'], $v['id'], $key);
                } else {
                    $v['link'] = $this->getUniqueLink($v['link'], $v['id'], $key);
                }
            }
            $values[$v['id']] = $v;
        }
        if ($cache_id) {
            $this->cache('save', $cache_id, $values);
        }

        return $values;
    }

    public function getRawFilterValues($f)
    {
        $values = [];
        switch ($f['first_char']) {
            case 'c':
                $values = $this->getSubcategories($this->id_lang, $f['id_parent'], $f['nesting_lvl'], true);
                break;
            case 'a':
            case 'f':
                $method_name = $f['first_char'] == 'a' ? 'getAttributes' : 'getFeatures';
                $values = $this->$method_name($this->id_lang, $f['id_group']);
                break;
            case 'm':
            case 's':
                $resource = $f['first_char'] == 'm' ? 'manufacturer' : 'supplier';
                $values = $this->db->executeS('
                    SELECT `' . bqSQL($f['first_char']) . '`.`id_' . bqSQL($resource) . '` as id, `name`
                    FROM `' . _DB_PREFIX_ . bqSQL($resource) . '` `' . bqSQL($f['first_char']) . '`
                    ' . Shop::addSqlAssociation($resource, $f['first_char']) . '
                    WHERE `active` = 1 ORDER BY `name` ASC
                ');
                break;
            case 't':
                $values = $this->db->executeS('
                    SELECT id_tag as id, name FROM ' . _DB_PREFIX_ . 'tag
                    WHERE id_lang = ' . (int) $this->id_lang . ' ORDER BY name ASC
                ');
                break;
            case 'q':
                $values = [
                    ['id' => 1, 'name' => $this->l('New')],
                    ['id' => 2, 'name' => $this->l('Used')],
                    ['id' => 3, 'name' => $this->l('Refurbished')],
                ];
                break;
        }

        return $values;
    }

    public function setColorStyle(&$v)
    {
        $img_name = (isset($v['id_original']) ? $v['id_original'] : $v['id']) . '.jpg';
        if (file_exists(_PS_COL_IMG_DIR_ . $img_name)) {
            $v['color_style'] = 'background-image:url(' . _THEME_COL_DIR_ . $img_name . ');';
        } elseif (isset($v['color'])) {
            $v['color_style'] = 'background-color:' . ($v['color'] ?: '#FFFFFF');
            if (Toolkit::isBrightColor($v['color'])) {
                $v['bright'] = 1;
            }
        }
    }

    public function getRandomSeed($upd_random)
    {
        $patterns = [1 => 'ymdH', 2 => 'ymd', 3 => 'ymW'];

        return isset($patterns[$upd_random]) ? date($patterns[$upd_random]) : mt_rand(0, 100000);
    }

    public function validatePageNumber(&$params)
    {
        $pages_nb = $this->getNumberOfPages($params['total_products'], $params['nb_items']);
        $page_exceeded = $pages_nb && $pages_nb < $params['page'];
        if ($params['page'] < 1 || ($params['page'] == 1 && Tools::isSubmit($this->param_names['p']))
            || $page_exceeded) {
            $updated_page = $page_exceeded ? $pages_nb : 1;
            $url = $this->context->link->getPaginationLink(false, false);
            $url = $this->updateQueryString($url, [$this->param_names['p'] => $updated_page]);
            $this->redirect301($url);
        }
    }

    public function redirect301($url)
    {
        $this->context->cookie->disallowWriting(); // as in $controller->canonicalRedirection()

        return Tools::redirect($url, __PS_BASE_URI__, $this->context->link, [
            'HTTP/1.0 301 Moved Permanently',
            'Cache-Control: no-cache',
        ]);
    }

    public function assignCustomPaginationAndSorting($params)
    {
        // pagination
        $this->assignSmartyVariablesForPagination(
            $params['page'],
            $params['total_products'],
            $params['nb_items'],
            $this->sanitizeURL($_SERVER['REQUEST_URI'])
        );
        if (!empty($this->nb_items_options)) {
            $this->context->smarty->assign(['nArray' => $this->nb_items_options]);
        }
        $this->context->controller->p = $params['page'];
        $this->context->controller->n = $params['nb_items'];
        $this->context->custom_pagination = 1;

        // sorting
        $this->context->controller->orderBy = $params['orderBy'];
        $this->context->controller->orderWay = $params['orderWay'];
        $default_sorting = explode('.', $params['default_sorting']);
        $this->context->smarty->assign([
            'orderby' => $params['orderBy'],
            'orderway' => $params['orderWay'],
            'orderbydefault' => $default_sorting[0],
            'orderwaydefault' => $default_sorting[1],
            'stock_management' => (int) Configuration::get('PS_STOCK_MANAGEMENT'),
        ]);
        $this->context->custom_sorting = 1;
    }

    public function comparatorJsVars()
    {
        $comparator_vars = [];
        $defined_vars = Media::getJsDef();
        if (!isset($defined_vars['min_item'])) {
            $tpl_vars = $this->context->smarty->tpl_vars;
            $max_items = $tpl_vars['comparator_max_item']->value;
            $min_items_txt = $this->l('Please select at least one product');
            $max_items_txt = $this->l('You cannot add more than %d product(s) to the product comparison');
            $comparator_vars = [
                'comparator_max_item' => $max_items,
                'comparedProductsIds' => $tpl_vars['compared_products']->value,
                'min_item' => addslashes(htmlspecialchars_decode($min_items_txt)),
                'max_item' => sprintf(addslashes(htmlspecialchars_decode($max_items_txt)), $max_items),
            ];
        }

        return $comparator_vars;
    }

    public function getPageName($controller_name)
    {
        $custom_names = [
            'bestsales' => 'best-sales',
            'pricesdrop' => 'prices-drop',
            'newproducts' => 'new-products',
            'seopage' => 'category',
        ];

        return isset($custom_names[$controller_name]) ? $custom_names[$controller_name] : $controller_name;
    }

    public function getSafeValue($name, $default = '')
    {
        return $this->sanitizeValue(Tools::getValue($name, $default));
    }

    public function sanitizeValue($value)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->sanitizeValue($v);
            }
        } else {
            $value = htmlspecialchars(strip_tags($value), ENT_QUOTES, 'UTF-8');
        }

        return $value;
    }

    public function sanitizeURL($url, $remove_page_param = true)
    {
        $url = Tools::safeOutput($url); // strip_tags + htmlentities
        if ($remove_page_param) {
            $url = preg_replace('/(?:(\?)|&amp;)' . $this->param_names['p'] . '=\d+/', '$1', $url);
        }

        return $url;
    }

    public function prepareTreeValues($values, $id_root)
    {
        $tree_values = [];
        foreach ($values as $v) {
            $tree_values[$v['id_parent']][$v['id']] = $v;
        }

        return isset($tree_values[$id_root]) ? $tree_values : [];
    }

    public function getSpecificSorting($controller)
    {
        $specific_sorting = [
            'bestsales' => 'sales.desc',
            'newproducts' => 'date_add.desc',
            'pricesdrop' => 'price.asc',
            'search' => 'position.asc',
        ];

        return isset($specific_sorting[$controller]) ? $specific_sorting[$controller] : false;
    }

    public function defineProductSorting()
    {
        if (!isset($this->context->forced_sorting)) {
            // make sure default_sorting option is present in the list of sorting_options
            if (!$this->is_modern) {
                $this->settings['general']['sorting_options'] = $this->getOptions('sorting');
            } else {
                $this->settings['general']['sorting_options'][$this->settings['general']['default_sorting']] = 1;
            }
            $submitted_sorting = $this->is_modern ? Tools::getValue('order')
                : trim('product.' . Tools::getValue('orderby') . '.' . Tools::getValue('orderway'), '.');
            foreach ([$submitted_sorting, 'product.' . $this->settings['general']['default_sorting']] as $s) {
                $s = explode('.', $s);
                if (count($s) == 3 && $this->validateSorting($s)) {
                    $this->context->forced_sorting = ['by' => $s[1], 'way' => $s[2]];
                    break;
                }
            }
        }
    }

    public function validateSorting($split_sorting)
    {
        return isset($this->settings['general']['sorting_options'][$split_sorting[1] . '.' . $split_sorting[2]]);
    }

    public function displayHook($hook_name)
    {
        if (empty($this->params_defined)) {
            return;
        }
        $this->context->smarty->assign([
            'hook_name' => $hook_name,
        ]);
        $html = $this->display(__FILE__, 'amazzingfilter.tpl');
        if ($this->settings['general']['p_type'] > 1 && $hook_name != 'displayHome') {
            $html .= $this->display(__FILE__, 'dynamic-loading.tpl');
        }

        return $html;
    }

    public function sortByKey($array, $key, $first_char = '')
    {
        $method_name = 'sortBy' . Tools::ucfirst($key);
        if (method_exists($this, $method_name)) {
            usort($array, [$this, $method_name]);
        } elseif (($all = $key == 'numbers_in_name') || $key == 'first_num') {
            foreach ($array as &$el) {
                $numbers = Toolkit::extractNumbers($el['name'], true) ?: [0]; // ready for <> comparison
                $el['number'] = $all ? implode('', $numbers) : $numbers[0]; // todo: improve $all
            }
            $array = $this->sortByKey($array, 'number');
        } elseif ($key == 'matches' && isset($this->products_data['count_data'][$first_char])) {
            $count_data = $this->products_data['count_data'][$first_char];
            foreach ($array as &$el) {
                $el['number'] = isset($count_data[$el['id']]) ? $count_data[$el['id']] : 0;
            }
            $array = array_reverse($this->sortByKey($array, 'number'));
        }

        return $array;
    }

    public function sortByPosition($a, $b)
    {
        return $a['position'] - $b['position'];
    }

    public function sortById($a, $b)
    {
        return $a['id'] - $b['id'];
    }

    public function sortByNumber($a, $b)
    {
        return $a['number'] > $b['number'] ? 1 : -1;
    }

    public function sortByName($a, $b)
    {
        return strcmp($a['name'], $b['name']);
    }

    public function assignSearchResultIDs($id_lang)
    {
        // s: 1.7, 8; search_query: 1.6 or some 3rd party modules;
        if ($query = Tools::getValue('s', Tools::getValue('search_query', Tools::getValue('ref')))) {
            $query = $this->x['hook_search_query'] = Tools::replaceAccentedChars(urldecode($query));
            $ajax = $this->context->controller->ajax;
            if ($this->custom_search == 'ambjolisearch' && class_exists('AmbSearch')) {
                $abjolisearchmodule = Module::getInstanceByName('ambjolisearch');
                $searcher = new AmbSearch(true, $this->context, $abjolisearchmodule);
                $id_cat = (int) Tools::getValue('ajs_cat');
                $id_man = (int) Tools::getValue('ajs_man');
                $searcher->search($id_lang, $query, 1, null, 'position', 'desc', $id_cat, $id_man);
                $this->controller_product_ids = $searcher->getResultIds();
            } elseif ($this->custom_search == 'elasticjetsearch') {
                $ejs_module = Module::getInstanceByName('elasticjetsearch');
                $params = [
                    'order_by' => 'position',
                    'order_way' => 'desc',
                    'page' => 1,
                    'items_per_page' => 100000,
                    'af' => 1, // may be used for improved compatibility
                ];
                $this->controller_product_ids = $ejs_module->getElasticManager()->search($query, $params)->getList();
            } else {
                $this->context->properties_not_required = 1;
                if (class_exists('IqitSearch') && !$this->is_modern) {
                    $search_query_cat = (int) Tools::getValue('search_query_cat');
                    $search = IqitSearch::find($id_lang, $query, $search_query_cat, 1, 100000);
                } elseif ($this->custom_search == 'tmsearch' && class_exists('TmSearchSearch')) {
                    $searcher = new TmSearchSearch();
                    $search_query_cat = Tools::getValue('search_categories');
                    $search = $searcher->tmfind($id_lang, $query, $search_query_cat, 1, 100000);
                } elseif ($this->custom_search == 'leoproductsearch' && class_exists('ProductSearch')) {
                    $search = ProductSearch::find(
                        $id_lang,
                        $query,
                        1,
                        100000,
                        'position',
                        'desc',
                        $ajax,
                        true,
                        $this->context,
                        Tools::getValue('cate')
                    );
                } else {
                    $search = Search::find($id_lang, $query, 1, 100000, 'position', 'desc', $ajax);
                }
                $this->context->properties_not_required = 0;
                $search_result = isset($search['result']) ? $search['result'] : $search;
                $this->controller_product_ids = array_column($search_result, 'id_product');
            }
        } elseif ($tag = urldecode(Tools::getValue('tag'))) {
            if (Validate::isValidSearch($tag)) {
                $this->controller_product_ids = array_column($this->db->executeS('
                    SELECT pt.id_product FROM ' . _DB_PREFIX_ . 'tag t
                    INNER JOIN ' . _DB_PREFIX_ . 'product_tag pt ON pt.id_tag = t.id_tag
                    ' . Shop::addSqlAssociation('product', 'pt') . '
                    WHERE t.name LIKE \'%' . pSQL($tag) . '%\'
                    AND t.id_lang = ' . (int) $id_lang . ' AND product_shop.active = 1
                '), 'id_product');
                $this->x['hook_search_query'] = $tag;
            }
        }
    }

    public function detectPossibleCustomSearchController($controller)
    {
        $compatible_modules = [
            'module-ambjolisearch-jolisearch' => 'ambjolisearch',
            'module-leoproductsearch-productsearch' => 'leoproductsearch',
            'module-iqitsearch-searchiqit' => 'iqitsearch',
            'module-tmsearch-tmsearch' => 'tmsearch',
        ];
        $this->custom_search = false;
        if (isset($compatible_modules[$controller])) {
            $this->custom_search = $compatible_modules[$controller];
        } elseif ($controller == 'search' && Module::getModuleIdByName('elasticjetsearch')
            && Module::isEnabled('elasticjetsearch')) {
            $this->custom_search = 'elasticjetsearch';
        }

        return $this->custom_search;
    }

    public function detectController()
    {
        if (!empty($this->context->controller->seopage_data)) {
            $controller = 'seopage';
        } else {
            $c = $this->getSafeValue('controller');
            if (Tools::getValue('fc') == 'module' && Tools::isSubmit('module')) {
                $c = 'module-' . $this->getSafeValue('module') . '-' . $c;
            }
            $available_controllers = $this->getAvailableControllers(true);
            $controller = isset($available_controllers[$c]) ? $c : false;
            if ((!$controller || $controller == 'search') && $this->detectPossibleCustomSearchController($c)) {
                $controller = 'search';
            }
        }

        return $controller;
    }

    public function getTemplateForCurrentPage($controller, $current_id, $id_lang, $id_shop)
    {
        return $this->db->getRow('
            SELECT  t.id_template, t.template_filters AS filters, t.additional_settings, tl.data AS lang
            FROM ' . _DB_PREFIX_ . 'af_templates t
            LEFT JOIN ' . _DB_PREFIX_ . 'af_templates_lang tl
                ON tl.id_template = t.id_template AND tl.id_shop = t.id_shop AND tl.id_lang = ' . (int) $id_lang
            . ($current_id ? '
            INNER JOIN `' . _DB_PREFIX_ . 'af_' . bqSQL($controller) . '_templates` ct
                ON ct.id_template = t.id_template AND ct.id_shop = t.id_shop
                AND ct.`id_' . bqSQL($controller) . '` IN (' . (int) $current_id . ', 0)' : '') . '
            WHERE t.active = 1 AND t.template_controller = \'' . pSQL($controller) . '\'
                AND t.id_shop = ' . (int) $id_shop . '
            ORDER BY  ' . ($current_id ? 'ct.`id_' . bqSQL($controller) . '` DESC, ' : '') . 't.id_template DESC
        ');
    }

    public function defineFilterParams()
    {
        if (isset($this->params_defined)) {
            return $this->params_defined;
        }
        $this->params_defined = false;
        if (!$controller = $this->detectController()) {
            return false;
        }
        $this->id_cat_current = (int) Tools::getValue('id_category', $this->context->shop->getCategory());
        $this->controller_product_ids = [];
        if (!empty($this->context->controller->seopage_data)) {
            $current_id = $this->context->controller->seopage_data['id_seopage'];
        } elseif (in_array($controller, $this->getControllersWithMultipleIDs())) {
            $current_id = $this->getSafeValue('id_' . $controller);
        } else {
            $current_id = 0;
        }
        if (!$template = $this->getTemplateForCurrentPage($controller, $current_id, $this->id_lang, $this->id_shop)) {
            return false;
        }
        $this->defineSettings();
        if ($controller != 'category') {
            switch ($controller) {
                case 'pricesdrop':
                case 'bestsales':
                case 'newproducts':
                    $this->controller_product_ids = $this->getSpecialControllerIds($controller);
                    break;
                case 'search':
                    $this->assignSearchResultIDs($this->id_lang);
                    break;
                case 'manufacturer':
                case 'supplier':
                    if (!$current_id) {
                        return false;
                    }
                    break;
                case 'index':
                    if (!$this->is_modern) {
                        $this->addCSS('product_list.css', _THEME_CSS_DIR_);
                    }
                    break;
            }
        }
        $this->current_controller = $controller;
        $additional_settings = json_decode($template['additional_settings'], true);
        $this->settings['general'] = $additional_settings + $this->settings['general'];
        if ($current_filters = $template['filters'] ? json_decode($template['filters'], true) : []) {
            if ($filters_lang = $template['lang'] ? json_decode($template['lang'], true) : []) {
                $current_filters = array_merge_recursive($current_filters, $filters_lang);
            }
        }
        $this->prepareTplVariables($current_filters);
        $this->params_defined = true;

        return true;
    }

    public function isVisibleQuery($alias = '', $id_shop = false)
    {
        $alias = ltrim($alias . '.', '.');
        $q = $alias . 'active = 1 AND ' . $alias . 'visibility <> \'none\'';
        if ($id_shop) {
            $q .= ' AND ' . $alias . 'id_shop = ' . (int) $id_shop;
        }

        return $q;
    }

    public function isNewQuery($alias = '')
    {
        $days_back = $this->settings['general']['new_days'] ?: Configuration::get('PS_NB_DAYS_NEW_PRODUCT');
        $prefix = $alias ? '`' . bqSQL($alias) . '`.' : '';

        return $prefix . 'date_add > \'' . pSQL($this->getOldestDate($days_back)) . '\'';
    }

    public function getOldestDate($days_back)
    {
        return date('Y-m-d H:i:s', strtotime('-' . (int) $days_back . ' days'));
    }

    public function getBestSalesIDs()
    {
        if ($days_back = $this->settings['general']['sales_days']) {
            $data = $this->db->executeS('
                SELECT od.product_id AS id, SUM(od.product_quantity) AS sales_num
                FROM ' . _DB_PREFIX_ . 'order_detail od
                INNER JOIN ' . _DB_PREFIX_ . 'orders o
                    ON o.id_order = od.id_order
                    AND o.date_add > \'' . pSQL($this->getOldestDate($days_back)) . '\'
                    AND o.id_shop = ' . (int) $this->id_shop . ' AND o.valid = 1
                GROUP BY od.product_id
                ORDER BY sales_num DESC, id DESC
            ');
        } else {
            $data = $this->db->executeS('
                SELECT ps.id_product AS id
                FROM ' . _DB_PREFIX_ . 'product_sale ps ' . Shop::addSqlAssociation('product', 'ps') . '
                WHERE ' . $this->isVisibleQuery('product_shop') . '
                ORDER BY ps.quantity DESC, id DESC
            ');
        }

        return array_column($data, 'id', 'id');
    }

    public function getDiscountedIDs($with_combination_id = false)
    {
        $current_date = date('Y-m-d H:i:00'); // as in Product::getPricesDrop()
        $ids = SpecificPrice::getProductIdByDate(
            $this->id_shop,
            $this->context->currency->id,
            $this->context->country->id,
            $this->context->customer->id_default_group,
            $current_date,
            $current_date,
            $this->context->customer->id,
            $with_combination_id
        );
        if (!$with_combination_id) {
            $ids = array_combine($ids, $ids);
        } else {
            $sorted = [];
            foreach ($ids as $i) {
                $sorted[$i['id_product']][$i['id_product_attribute']] = $i['id_product_attribute'];
            }
            $ids = $sorted;
        }

        return $ids;
    }

    public function getSpecialControllerIds($controller)
    {
        $ids = [];
        switch ($controller) {
            case 'newproducts':
                $ids = array_column($this->db->executeS('
                    SELECT id_product FROM ' . _DB_PREFIX_ . 'product_shop
                    WHERE ' . $this->isNewQuery() . ' AND ' . $this->isVisibleQuery('', $this->id_shop) . '
                    ORDER BY date_add DESC, id_product DESC
                '), 'id_product', 'id_product');
                break;
            case 'bestsales':
                $ids = $this->getBestSalesIDs();
                break;
            case 'pricesdrop':
                $ids = $this->getDiscountedIDs();
                break;
            case 'on_sale':
            case 'online_only':
                $ids = array_column($this->db->executeS('
                    SELECT id_product FROM ' . _DB_PREFIX_ . 'product_shop
                    WHERE `' . bqSQL($controller) . '` = 1 AND ' . $this->isVisibleQuery('', $this->id_shop) . '
                    ORDER BY id_product DESC
                '), 'id_product', 'id_product');
                break;
        }

        return $ids;
    }

    public function hookDisplayCustomerFilters($params = [])
    {
        return $this->customerFilters()->display($params);
    }

    public function hookDisplayLeftColumn()
    {
        return $this->displayHook('displayLeftColumn');
    }

    public function hookDisplayRightColumn()
    {
        return $this->displayHook('displayRightColumn');
    }

    public function hookDisplayHeaderCategory()
    {
        return $this->displayHook('displayHeaderCategory');
    }

    public function hookDisplayTopColumn()
    {
        return $this->displayHook('displayTopColumn');
    }

    public function hookDisplayAmazzingFilter()
    {
        return $this->displayHook('displayAmazzingFilter');
    }

    public function hookDisplayHome()
    {
        return $this->displayHook('displayHome');
    }

    /**
     * index Product when customer clicks save in 1.6
     * actionIndexProduct is defined in /override/controllers/admin/AdminProductController.php
     */
    public function hookActionIndexProduct($params)
    {
        if (!empty($params['product'])) {
            $id_product = is_object($params['product']) ? $params['product']->id : $params['product'];
            // index products only in context shops if not defined otherwise
            $shop_ids = isset($params['shop_ids']) ? $params['shop_ids'] : $this->shopIDs();

            return empty($params['unindex_all']) ? $this->indexProduct($id_product, $shop_ids)
                : $this->unindexProducts($id_product, $shop_ids);
        }
    }

    public function hookActionProductAdd($params)
    {
        return $this->hookActionProductUpdate($params);
    }

    public function hookActionProductUpdate($params)
    {
        $this->defineSettings();
        if (!empty($params['id_product']) && $this->readyToIndexOnProductUpdate()) {
            // this hook can be called anywhere, so make sure product is indexed for all shops if not defined otherwise
            $shop_ids = isset($params['shop_ids']) ? $params['shop_ids'] : $this->shopIDs('all');
            $this->indexProduct((int) $params['id_product'], $shop_ids);
        }
    }

    public function readyToIndexOnProductUpdate()
    {
        if (!empty($this->context->controller) && get_class($this->context->controller) == 'AdminProductsController') {
            $is_product_sheet = Tools::isSubmit('combinations') || Tools::isSubmit('submitted_tabs');
            $ready = !$is_product_sheet;
            // If actionProductUpdate is called from submitting product sheet, re-indexation will happen one step later
            // 1.7: check file /views/js/product-indexer.js
            // 1.6: check file /override_files/controllers/admin/AdminProductsController.php
        } else {
            $ready = $this->settings['indexation']['auto'];
        }

        return $ready;
    }

    public function hookActionObjectCombinationAddAfter($params)
    {
        // save this value for reindexing product after mass combinations generation in 1.6
        if (!$this->is_modern && empty($this->context->cookie->af_index_product)) {
            $this->context->cookie->__set('af_index_product', $params['object']->id_product);
        }
    }

    public function hookActionObjectAddAfter($params)
    {
        $this->hookActionObjectUpdateAfter($params);
    }

    public function hookActionObjectDeleteAfter($params)
    {
        $this->hookActionObjectUpdateAfter($params);
    }

    public function hookActionObjectUpdateAfter($params)
    {
        if (isset($params['object']) && $cls = get_class($params['object'])) {
            $cache_dependencies = [
                'Category' => 'c_list',
                'Attribute' => 'a_list',
                'FeatureValue' => 'f_list',
                'Combination' => 'comb_data',
                'Order' => 'comb_data',
                'StockAvailable' => 'comb_data',
                'Product' => 'allowed_ids',
            ];
            if (isset($cache_dependencies[$cls])) {
                $this->cache('clear', $cache_dependencies[$cls]);
            } elseif (in_array($cls, ['Language', 'Currency', 'Group'])) {
                $this->cache('clear', 'indexationColumns');
                $this->i['suffixes'] = [];
                $this->indexationColumns('adjust');
            }
        }
    }

    public function hookActionAdminTagsControllerSaveAfter()
    {
        $id_lang = Tools::getValue('id_lang');
        $id_tag = Tools::getValue('id_tag');
        $product_ids = Tools::getValue('products');
        $this->updateTagInIndex($id_lang, $id_tag, $product_ids);
    }

    public function hookActionAdminTagsControllerDeleteBefore($params)
    {
        $id_tag = Tools::getValue('id_tag');
        $id_lang = (int) $this->db->getValue('
            SELECT id_lang FROM ' . _DB_PREFIX_ . 'tag WHERE id_tag = ' . (int) $id_tag . '
        ');
        $this->context->tag_to_delete = [
            'id_tag' => $id_tag,
            'id_lang' => $id_lang,
        ];
    }

    public function hookActionAdminTagsControllerDeleteAfter($params)
    {
        if (!empty($this->context->tag_to_delete)) {
            $id_lang = $this->context->tag_to_delete['id_lang'];
            $id_tag = $this->context->tag_to_delete['id_tag'];
            $this->updateTagInIndex($id_lang, $id_tag);
        }
    }

    public function updateTagInIndex($id_lang, $id_tag, $product_ids = [])
    {
        $var_data = $this->indexationColumns('getVariableData');
        if (isset($var_data['t']) && in_array($id_lang, $var_data['t'])) {
            $product_ids = $this->formatIDs($product_ids);
            $upd_rows = [];
            $t_col = 't_' . (int) $id_lang;
            $upd_columns_ = 'id_product, id_shop, `' . bqSQL($t_col) . '`';
            // tag may be removed from some products and added to others, so check all rows
            $data = $this->db->executeS('SELECT ' . $upd_columns_ . ' FROM `' . bqSQL($this->i['table']) . '`');
            foreach ($data as $row) {
                $tags = $this->formatIDs($row[$t_col]);
                if (isset($product_ids[$row['id_product']])) {
                    $tags[$id_tag] = $id_tag;
                } else {
                    unset($tags[$id_tag]);
                }
                $tags = $this->sqlIDs($tags);
                if ($tags != $row[$t_col]) {
                    $row[$t_col] = $tags;
                    $upd_rows[] = '(\'' . implode('\', \'', array_map('pSQL', $row)) . '\')';
                }
            }
            if ($upd_rows) {
                $this->db->execute('
                    INSERT INTO `' . bqSQL($this->i['table']) . '` (' . $upd_columns_ . ')
                    VALUES ' . implode(', ', $upd_rows) . '
                    ON DUPLICATE KEY UPDATE `' . bqSQL($t_col) . '` = VALUES(`' . bqSQL($t_col) . '`)
               ');
            }
        }
    }

    public function hookActionProductDelete($params)
    {
        if (!empty($params['product']->id)) {
            $id_product = $params['product']->id;
            $this->unindexProducts([$id_product]);
        }
    }

    public function hookActionProductListOverride($params)
    {
        if (!isset($this->products_data)) {
            return;
        }
        $params['hookExecuted'] = true;
        $params['catProducts'] = $this->products_data['products'];
        $params['nbProducts'] = $this->products_data['filtered_ids_count'];
    }

    public function getFeatures($id_lang, $id_group = false, $merge_if_required = true)
    {
        $f = $this->db->executeS('
            SELECT v.id_feature_value AS id, v.id_feature AS id_group, v.custom,
            vl.value AS name, fl.name AS group_name
            FROM ' . _DB_PREFIX_ . 'feature_value v
            INNER JOIN ' . _DB_PREFIX_ . 'feature_value_lang vl
                ON (v.id_feature_value = vl.id_feature_value AND vl.id_lang = ' . (int) $id_lang . ')
            INNER JOIN ' . _DB_PREFIX_ . 'feature f
                ON f.id_feature = v.id_feature
            INNER JOIN ' . _DB_PREFIX_ . 'feature_lang fl
                ON (fl.id_feature = v.id_feature AND fl.id_lang = ' . (int) $id_lang . ')
            ' . ($id_group ? ' AND v.id_feature = ' . (int) $id_group : '') . '
            ORDER BY vl.value, v.id_feature_value
        ');
        if ($merge_if_required && !empty($this->settings['general']['merged_features'])) {
            $f = $this->mergedValues()->mapRows($f, $id_lang, $id_group, 'feature');
        }

        return $f;
    }

    public function getAttributes($id_lang, $id_group = false, $merge_if_required = true)
    {
        $a = $this->db->executeS('
            SELECT DISTINCT a.id_attribute AS id, a.position, a.color, al.name,
            agl.public_name AS group_name, ag.id_attribute_group AS id_group, ag.is_color_group
            FROM ' . _DB_PREFIX_ . 'attribute_group ag
            INNER JOIN ' . _DB_PREFIX_ . 'attribute_group_lang agl
                ON (ag.id_attribute_group = agl.id_attribute_group AND agl.id_lang = ' . (int) $id_lang . ')
            INNER JOIN ' . _DB_PREFIX_ . 'attribute a
                ON a.id_attribute_group = ag.id_attribute_group
            INNER JOIN ' . _DB_PREFIX_ . 'attribute_lang al
                ON (a.id_attribute = al.id_attribute AND al.id_lang = ' . (int) $id_lang . ')
            ' . Shop::addSqlAssociation('attribute_group', 'ag') . '
            ' . Shop::addSqlAssociation('attribute', 'a') . '
            WHERE a.id_attribute IS NOT NULL AND al.name IS NOT NULL AND agl.id_attribute_group IS NOT NULL
            ' . ($id_group ? ' AND ag.id_attribute_group = ' . (int) $id_group : '') . '
            ORDER BY al.name, a.id_attribute
        ');
        if ($merge_if_required && !empty($this->settings['general']['merged_attributes'])) {
            $a = $this->mergedValues()->mapRows($a, $id_lang, $id_group, 'attribute');
        }

        return $a;
    }

    public function generateLink($string, $identifier = '', $group_key = 'default')
    {
        $string = str_replace([',', '.', '*'], '-', $string);
        $link = Tools::str2url($string) ?: $identifier;

        return $this->getUniqueLink($link, $identifier, $group_key);
    }

    public function getUniqueLink($link, $identifier, $group_key)
    {
        if (!isset($this->x['generated_links'][$group_key][$link])) {
            $this->x['generated_links'][$group_key][$link] = 1;
        } elseif ($identifier) {
            $link = $identifier . ($link ? '-' . $link : '');
        }

        return $link;
    }

    public function ajaxEraseIndex()
    {
        $id_shop = Tools::getValue('id_shop');
        $deleted = $this->indexationData('erase', ['id_shop' => $id_shop]);
        $indexation_data = $this->indexationInfo('count', [$id_shop]);
        $missing = isset($indexation_data[$id_shop]['missing']) ? $indexation_data[$id_shop]['missing'] : 0;
        $ret = [
            'deleted' => $deleted,
            'missing' => $missing,
        ];
        exit(json_encode($ret));
    }

    public function ajaxRunProductIndexer($all_identifier, $products_per_request = 1000)
    {
        $ret = [];
        if ($all_identifier) {
            $this->reIndexProducts($all_identifier, $products_per_request);
            $ret['indexation_process_data'] = $this->getIndexationProcessData($all_identifier, true);
        } else {
            $this->indexMissingProducts($products_per_request);
        }
        $ret['indexation_data'] = $this->indexationInfo('count');
        exit(json_encode($ret));
    }

    public function reIndexProducts($all_identifier, $products_per_request, $shop_ids = [])
    {
        if (!$saved_data = $this->getIndexationProcessData($all_identifier, false)) {
            $saved_data = ['identifier' => $all_identifier, 'data' => []];
            foreach ($this->indexationInfo('ids', $shop_ids, true) as $id_shop => $data) {
                $saved_data['data'][$id_shop]['missing'] = array_merge($data['indexed'], $data['missing']);
                $saved_data['data'][$id_shop]['indexed'] = [];
            }
        }
        $indexed_num = 0;
        foreach ($saved_data['data'] as $id_shop => &$data) {
            if (empty($data['missing'])) {
                unset($saved_data['data'][$id_shop]);
            } elseif ($ids_to_index = array_slice($data['missing'], 0, $products_per_request)) {
                $indexed_ids = $this->indexProduct($ids_to_index, [$id_shop], false);
                $data['missing'] = array_diff($data['missing'], $indexed_ids);
                $data['indexed'] = array_merge($data['indexed'], $indexed_ids);
                if (empty($data['missing'])) {
                    unset($saved_data['data'][$id_shop]);
                }
                $indexed_num += count($indexed_ids);
                break;
            }
        }
        $saved_data = !empty($saved_data['data']) ? $saved_data : [];
        $this->saveData($this->indexation_process_file_path, $saved_data);

        return $indexed_num;
    }

    public function indexMissingProducts($products_per_request, $shop_ids = [])
    {
        $indexed_num = 0;
        foreach ($this->indexationInfo('ids', $shop_ids, true) as $id_shop => $data) {
            if (!empty($data['missing'])) {
                $product_ids = array_slice($data['missing'], 0, $products_per_request);
                $this->indexProduct($product_ids, [$id_shop]);
                $indexed_num += count($product_ids);
                break;
            }
        }

        return $indexed_num;
    }

    public function getIndexationProcessData($all_identifier, $return_count = true)
    {
        $ret = $indexation_data = $this->getData($this->indexation_process_file_path, $all_identifier);
        if ($return_count && !empty($indexation_data['data'])) {
            $ret = [];
            foreach ($indexation_data['data'] as $id_shop => $data) {
                foreach ($data as $name => $ids) {
                    $ret[$id_shop][$name] = count($ids);
                }
            }
        }

        return $ret;
    }

    public function getData($path, $identifier = false)
    {
        $data = file_exists($path) ? json_decode(Tools::file_get_contents($path), true) : [];
        if ($data && $identifier && (!isset($data['identifier']) || $data['identifier'] . '' != $identifier . '')) {
            $time_before_reset = 60;
            $time_diff = $time_before_reset - (time() - filemtime($path));
            if ($time_diff > 1) {
                $err = $this->l('Please wait, someone else is performing same action') . '. '
                    . sprintf($this->l('%s seconds left before automatic reset.'), $time_diff);
                $this->throwError($err);
                exit($err); // may be used in cron indexation and other non-ajax requests
            } else {
                $data = [];
            }
        }

        return $data;
    }

    public function saveData($path, $data, $append = false)
    {
        if ($data) {
            $data = is_string($data) ? $data : json_encode($data);

            return $append ? file_put_contents($path, $data, FILE_APPEND) : file_put_contents($path, $data);
        } else {
            return unlink($path);
        }
    }

    public function formatIDs($ids, $return_string = false)
    {
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        $ids = array_map('intval', $ids);
        $ids = array_combine($ids, $ids);
        unset($ids[0]);

        return $return_string ? implode(',', $ids) : $ids;
    }

    public function sqlIDs($ids)
    {
        return $this->formatIDs($ids, true);
    }

    public function shopIDs($type = 'context', $implode = false)
    {
        if (!isset($this->x['shop_ids'][$type])) {
            $this->x['shop_ids'][$type] = $type == 'all' ? Shop::getShops(false, null, true)
                : Shop::getContextListShopID();
        }

        return $this->formatIDs($this->x['shop_ids'][$type], $implode);
    }

    public function customerGroupIDs($implode = false)
    {
        if (!isset($this->x['cg'])) {
            $this->x['cg'] = $this->context->customer->getGroups();
        }

        return $this->formatIDs($this->x['cg'], $implode);
    }

    public function getShopsForIndexation($predefined_ids = [])
    {
        if (!$shop_ids = $predefined_ids ?: $this->shopIDs()) {
            $shop_ids = [$this->context->shop->id];
        }

        return $shop_ids;
    }

    public function getAllParents($id_cat, $id_shop = false, $only_active = false)
    {
        $id_shop = $id_shop ?: $this->id_shop;
        if (!isset($this->i['all_parents'][$id_shop][$id_cat])) {
            $cat_obj = new Category($id_cat);
            $this->i['all_parents'][$id_shop][$id_cat] = array_column($this->db->executeS('
                SELECT c.id_category AS id
                FROM ' . _DB_PREFIX_ . 'category c
                INNER JOIN ' . _DB_PREFIX_ . 'category_shop cs
                    ON cs.id_category = c.id_category AND cs.id_shop = ' . (int) $id_shop . '
                WHERE nleft < ' . (int) $cat_obj->nleft . ' AND nright > ' . (int) $cat_obj->nright . '
                    AND id_parent > 0' . ($only_active ? ' AND c.active = 1' : '') . '
                ORDER BY level_depth ASC
            '), 'id', 'id');
        }

        return $this->i['all_parents'][$id_shop][$id_cat];
    }

    public function getDataForPriceCalculation($id_shop)
    {
        if (!isset($this->i['p_data'][$id_shop])) {
            foreach (['group' => 'g', 'currency' => 'c'] as $name => $key) {
                $identifier = 'id_' . $name;
                $default = Configuration::get($this->i['default'][$key], null, null, $id_shop);
                $join_on = '`' . bqSQL($identifier) . '` = main.`' . bqSQL($identifier) . '`';
                $query = new DbQuery();
                $query->select('DISTINCT(main.`' . bqSQL($identifier) . '`), sp.id_specific_price AS has_specific_price');
                $query->select('main.`' . bqSQL($identifier) . '` = ' . (int) $default . ' AS is_default');
                if ($key == 'g') {
                    $query->select('main.reduction, main.price_display_method AS no_tax');
                    $query->select('gr.reduction AS group_reduction');
                    $query->leftJoin('group_reduction', 'gr', 'gr.id_group = main.id_group');
                } else {
                    $query->select('s.conversion_rate');
                }
                $query->from($name, 'main');
                $query->innerJoin($name . '_shop', 's', 's.' . $join_on . ' AND s.id_shop = ' . (int) $id_shop);
                $query->leftJoin('specific_price', 'sp', 'sp.' . $join_on . ' AND sp.id_shop IN (s.id_shop, 0)');
                $query->where($this->specificIndexationQuery($identifier, 'main', $id_shop));
                $query->orderBy('is_default DESC, main.`' . bqSQL($identifier) . '` ASC'); // default first
                $this->i['p_data'][$id_shop][$name] = [];
                foreach ($this->db->executeS($query) as $row) {
                    if ($key == 'g') {
                        if (!$row['has_specific_price']) {
                            $row['has_specific_price'] = $row['reduction'] > 0 || $row['group_reduction'] > 0;
                        }
                        $row['use_tax'] = (!$row['no_tax'] && !$this->settings['indexation']['dynamic_tax']) ? 1 : 0;
                    }
                    $this->i['p_data'][$id_shop][$name][$row[$identifier]] = $row;
                }
            }
        }

        return $this->i['p_data'][$id_shop];
    }

    public function getSuffixes($resource, $id_shop = 0, $validate = true)
    {
        if (!isset($this->i['suffixes'][$id_shop][$resource])) {
            $c_name = 'id_' . $resource;
            $suffixes = array_column($this->db->executeS('
                SELECT main.`' . bqSQL($c_name) . '` FROM `' . _DB_PREFIX_ . bqSQL($resource) . '` main'
                . ($id_shop ? '
                INNER JOIN `' . _DB_PREFIX_ . bqSQL($resource) . '_shop` s
                    ON s.`' . bqSQL($c_name) . '` = main.`' . bqSQL($c_name) . '`
                    AND s.`id_shop` = ' . (int) $id_shop : '') . '
                WHERE ' . $this->specificIndexationQuery($c_name, 'main', $id_shop) . '
                ORDER BY main.`' . bqSQL($c_name) . '` ASC
            '), $c_name, $c_name); // NOTE: result is ordered by ID, not like in getDataForPriceCalculation
            if ($validate) {
                $this->validateSuffixes($suffixes, $resource, $id_shop);
            }
            $this->i['suffixes'][$id_shop][$resource] = $suffixes;
        }

        return $this->i['suffixes'][$id_shop][$resource];
    }

    public function specificIndexationQuery($c_name, $t_alias, $id_shop = 0, $check_settings = true)
    {
        $where = '1';
        $specific_resources = ['id_group' => 'g', 'id_currency' => 'c'];
        if (isset($specific_resources[$c_name])) {
            $key = $specific_resources[$c_name];
            if ($check_settings && !$this->settings['indexation']['p_' . $key] && isset($this->i['default'][$key])) {
                $config_key = $this->i['default'][$key];
                $where .= ' AND `' . bqSQL($t_alias) . '`.`' . bqSQL($c_name) . '`';
                if ($id_shop) {
                    $where .= ' = ' . (int) Configuration::get($config_key, null, null, $id_shop);
                } else {
                    $default_ids = [];
                    foreach ($this->shopIDs('all') as $id_shop) {
                        $default_ids[] = Configuration::get($config_key, null, null, $id_shop);
                    }
                    $where .= ' IN (' . $this->sqlIDs($default_ids) . ')';
                }
            }
            if ($key == 'c') {
                $where .= ' AND `' . bqSQL($t_alias) . '`.deleted = 0 AND `' . bqSQL($t_alias) . '`.active = 1';
            }
        } elseif ($c_name == 'id_lang') {
            $where .= ' AND `' . bqSQL($t_alias) . '`.active = 1';
        }

        return $where;
    }

    public function validateSuffixes(&$suffixes, $resource, $id_shop)
    {
        if (!$suffixes) {
            $suffixes = $this->getDefaultSuffixes($resource, $id_shop); // fix for some complex multishop scenarios
        }
        if (count($suffixes) > $this->i['max_column_suffixes']) {
            $dependent_options = ['group' => 'p_g', 'currency' => 'p_c'];
            if (isset($dependent_options[$resource])) {
                $this->settings['indexation'][$dependent_options[$resource]] = 0;
                $suffixes = $this->getSuffixes($resource, $id_shop, false);
            } else {
                $suffixes = [];
            }
        }
    }

    public function getDefaultSuffixes($resource, $id_shop)
    {
        $suffixes = [];
        $first_char = Tools::substr($resource, 0, 1);
        if (isset($this->i['default'][$first_char])) {
            $shop_ids = $id_shop ? [$id_shop] : $this->shopIDs('all');
            foreach ($shop_ids as $id_shop) {
                if ($suffix = (int) Configuration::get($this->i['default'][$first_char], null, null, $id_shop)) {
                    $suffixes[$suffix] = $suffix;
                }
            }
        }

        return $suffixes;
    }

    public function availableSuffixesNum($specific_key = false, $cache_time = 3600)
    {
        $cache_id = 'suffixesNum';
        if (!$num = $this->cache('get', $cache_id, '', $cache_time)) {
            $num = [];
            $num_keys = ['currency' => ['p_c'], 'group' => ['p_g'], 'lang' => ['t', 'n']];
            foreach ($num_keys as $t_name => $keys) {
                $c_name = 'id_' . $t_name;
                $value = (int) $this->db->getValue('
                    SELECT COUNT(`' . bqSQL($c_name) . '`) FROM `' . _DB_PREFIX_ . bqSQL($t_name) . '` main
                    WHERE ' . $this->specificIndexationQuery($c_name, 'main', 0, false) . '
                ');
                $num += array_fill_keys($keys, $value);
            }
            if ($cache_time) {
                $this->cache('save', $cache_id, $num);
            }
        }
        if ($specific_key) {
            $num = isset($num[$specific_key]) ? $num[$specific_key] : 0;
        }

        return $num;
    }

    public function indexProduct($product_ids, $shop_ids = [], $return_string = true)
    {
        if (Tools::getValue('no_indexation')) {
            $product_ids = [];
        }
        $this->defineSettings();
        foreach ($this->getShopsForIndexation($shop_ids) as $id_shop) {
            $this->updateIndexationData($product_ids, $id_shop);
        }

        return $this->formatIDs($product_ids, $return_string);
    }

    public function instantiateProduct($id_product, $id_shop, $id_combination = 0)
    {
        $p_obj = new Product($id_product, false, null, $id_shop);
        if (!Validate::isLoadedObject($p_obj)) {
            return false;
        }
        $p_obj->af_id_comb_default = (int) $this->db->getValue('
            SELECT pas.id_product_attribute FROM ' . _DB_PREFIX_ . 'product_attribute pa
            INNER JOIN ' . _DB_PREFIX_ . 'product_attribute_shop pas
                ON pas.id_product_attribute = pa.id_product_attribute AND pas.id_shop = ' . (int) $id_shop . '
            WHERE pa.id_product = ' . (int) $p_obj->id . ' AND pas.default_on = 1
        ');
        $p_obj->af_id_comb = $id_combination ?: $p_obj->af_id_comb_default;
        $p_obj->af_id_country = $this->getDefaultCountry($id_shop);
        if ($this->i['p_comb'] || $this->settings['indexation']['dynamic_tax']) {
            $today = date('Y-m-d 00:00:00');
            $discount_for_default_country = $this->db->getValue('
                SELECT id_specific_price FROM ' . _DB_PREFIX_ . 'specific_price sp
                WHERE sp.id_shop = ' . (int) $id_shop . ' AND sp.id_country = ' . (int) $p_obj->af_id_country . '
                AND id_product IN (0, ' . (int) $p_obj->id . ') AND sp.from <= \'' . pSQL($today) . '\'
                AND (sp.to >= \'' . pSQL($today) . '\' OR sp.to = \'0000-00-00 00:00:00\')
            ');
            if ($discount_for_default_country) {
                $p_obj->af_id_country = 0; // include possible discount for 0 country in indexed price
            }
        }
        if ($p_obj->af_id_comb_default && $p_obj->af_id_comb_default != $p_obj->cache_default_attribute) {
            $this->db->execute('
                UPDATE ' . _DB_PREFIX_ . 'product_shop
                SET cache_default_attribute = ' . (int) $p_obj->af_id_comb_default . '
                WHERE id_product = ' . (int) $p_obj->id . ' AND id_shop = ' . (int) $id_shop . '
            ');
        }

        return $p_obj;
    }

    public function updateIndexationData($product_ids, $id_shop, $params = [])
    {
        if (!$product_ids = $this->formatIDs($product_ids)) {
            return;
        }
        $indexation_columns = $this->prepareColumnsForIndexation($id_shop, $params);
        $column_names = $rows = $upd = $cp_rows = [];
        $ids_to_unindex = [];
        foreach ($product_ids as $id) {
            if (!$p_obj = $this->instantiateProduct($id, $id_shop)) {
                continue;
            }
            if (!$p_obj->active || $p_obj->visibility == 'none') {
                $ids_to_unindex[] = $p_obj->id;
                continue;
            }
            $forced_values = isset($params['main_values'][$id]) ?: [];
            $row = ['id_product' => (int) $id, 'id_shop' => (int) $id_shop];
            foreach ($indexation_columns['main'] as $c_name) {
                $value = isset($forced_values[$c_name]) ? $forced_values[$c_name] :
                    $this->prepareIndexationValue($p_obj, $id_shop, $c_name);
                $row[$c_name] = pSQL(is_array($value) ? $this->sqlIDs($value) : $value);
            }
            foreach ($indexation_columns['variable'] as $c_name => $c_suffixes) {
                $value = isset($forced_values[$c_name]) ? $forced_values[$c_name] :
                    $this->prepareIndexationValue($p_obj, $id_shop, $c_name);
                foreach ($c_suffixes as $suffix) {
                    $v = isset($value[$suffix]) ? $value[$suffix] : '';
                    $row[$c_name . '_' . $suffix] = pSQL(is_array($v) ? $this->sqlIDs($v) : $v);
                }
                if ($this->i['p_comb'] && $c_name == 'p') {
                    $cp_rows += $this->combinationPrices('prepareRows', ['p_obj' => $p_obj, 'id_shop' => $id_shop]);
                }
            }
            $rows[] = '(\'' . implode('\', \'', $row) . '\')';
            if (!$column_names) {
                $column_names = array_keys($row);
            }
        }
        if ($ids_to_unindex) {
            $this->unindexProducts($ids_to_unindex, [$id_shop]);
        }
        if ($column_names && $rows && $upd = array_diff($column_names, $indexation_columns['primary'])) {
            foreach ($upd as $i => $c_name) {
                $upd[$i] = '`' . bqSQL($c_name) . '` = VALUES(`' . bqSQL($c_name) . '`)';
            }
            $query = ['
                INSERT INTO `' . bqSQL($this->i['table']) . '`
                (`' . implode('`, `', array_map('bqSQL', $column_names)) . '`)
                VALUES ' . implode(', ', $rows) . ' ON DUPLICATE KEY UPDATE ' . implode(', ', $upd) . '
            '];
            if ($cp_rows) {
                $this->combinationPrices('insertRows', [
                    'rows' => $cp_rows,
                    'product_ids' => $product_ids,
                    'id_shop' => $id_shop,
                ]);
            }

            return $this->runSql($query);
        }
    }

    public function prepareColumnsForIndexation($id_shop, $params = [])
    {
        // $params = ['main_columns' => ['f'], 'variable_columns' => []]; // re-index only features
        // $params = ['main_columns' => ['a'], 'variable_columns' => ['t']]; // re-index only attributes and tags
        $indexation_columns = $this->indexationColumns('getRequired', $id_shop, 3600);
        if (isset($params['main_columns'])) {
            $indexation_columns['main'] = array_intersect($indexation_columns['main'], $params['main_columns']);
        }
        if (isset($params['variable_columns'])) {
            foreach (array_keys($indexation_columns['variable']) as $key) {
                if (!in_array($key, $params['variable_columns'])) {
                    unset($indexation_columns['variable'][$key]);
                }
            }
        }

        return $indexation_columns;
    }

    public function prepareIndexationValue($p_obj, $id_shop, $type)
    {
        $value = [];
        $id_product = $p_obj->id;
        switch ($type) {
            case 'c':
                $only_active = $this->settings['indexation']['c_active'];
                $value = array_column($this->db->executeS('
                    SELECT cp.id_category AS id
                    FROM ' . _DB_PREFIX_ . 'category_product cp
                    INNER JOIN ' . _DB_PREFIX_ . 'category_shop cs
                        ON cs.id_category = cp.id_category AND cs.id_shop = ' . (int) $id_shop . '
                    ' . ($only_active ? 'INNER JOIN ' . _DB_PREFIX_ . 'category c
                        ON c.id_category = cp.id_category AND c.active = 1' : '') . '
                    WHERE id_product = ' . (int) $id_product . '
                '), 'id', 'id');
                if ($this->settings['indexation']['subcat_products']) { // indexation settings are same for all shops
                    foreach ($value as $id_cat) {
                        $value += $this->getAllParents($id_cat, $id_shop, $only_active);
                    }
                }
                break;
            case 'a':
                $atts = $this->db->executeS('
                    SELECT pac.id_attribute, map.id_merged FROM ' . _DB_PREFIX_ . 'product_attribute_combination pac
                    LEFT JOIN ' . _DB_PREFIX_ . 'af_merged_attribute_map map
                        ON map.id_original = pac.id_attribute
                    INNER JOIN ' . _DB_PREFIX_ . 'product_attribute pa
                        ON pa.id_product_attribute = pac.id_product_attribute
                        AND pa.id_product = ' . (int) $id_product . '
                    INNER JOIN ' . _DB_PREFIX_ . 'product_attribute_shop pas
                        ON pas.id_product_attribute = pa.id_product_attribute AND pas.id_shop = ' . (int) $id_shop . '
                ');
                foreach ($atts as $att) {
                    $value[$att['id_attribute']] = $att['id_attribute'];
                    if (!empty($att['id_merged'])) {
                        $value['map' . $att['id_merged']] = 'map' . $att['id_merged'];
                    }
                }
                $value = implode(',', $value); // skip formatIDs in updateIndexationData because of possible map_xx
                break;
            case 'f':
                $feats = $this->db->executeS('
                    SELECT fp.id_feature_value, map.id_merged FROM ' . _DB_PREFIX_ . 'feature_product fp
                    LEFT JOIN ' . _DB_PREFIX_ . 'af_merged_feature_map map
                        ON map.id_original = fp.id_feature_value
                    WHERE fp.id_product = ' . (int) $id_product . '
                ');
                foreach ($feats as $feat) {
                    $value[$feat['id_feature_value']] = $feat['id_feature_value'];
                    if (!empty($feat['id_merged'])) {
                        $value['map' . $feat['id_merged']] = 'map' . $feat['id_merged'];
                    }
                }
                $value = implode(',', $value); // same as atts
                break;
            case 's':
                $value = array_column($this->db->executeS('
                    SELECT id_supplier AS id FROM ' . _DB_PREFIX_ . 'product_supplier
                    WHERE id_product = ' . (int) $id_product . '
                '), 'id', 'id');
                break;
            case 'w':
                $value = (float) $this->db->getValue('
                    SELECT weight FROM ' . _DB_PREFIX_ . 'product WHERE id_product = ' . (int) $id_product . '
                ');
                if ($p_obj->af_id_comb) {
                    $value += (float) $this->db->getValue('
                        SELECT weight FROM ' . _DB_PREFIX_ . 'product_attribute_shop
                        WHERE id_product_attribute = ' . (int) $p_obj->af_id_comb . '
                        AND id_shop = ' . (int) $id_shop . '
                    ');
                }
                break;
            case 'p':
                $default_prices = [];
                $calc_data = $this->getDataForPriceCalculation($id_shop);
                foreach ($calc_data['currency'] as $id_currency => $c) {
                    $group_tax_prices = [];
                    foreach ($calc_data['group'] as $id_group => $g) {
                        if (!$p_obj->show_price) {
                            $value[$id_group . '_' . $id_currency] = -1;
                            continue;
                        }
                        if (!$c['has_specific_price'] && isset($default_prices[$id_group])) {
                            $price = Tools::ps_round($default_prices[$id_group] * $c['conversion_rate'], 2);
                        } elseif (!$g['has_specific_price'] && isset($group_tax_prices[$g['use_tax']])) {
                            $price = $group_tax_prices[$g['use_tax']];
                        } else {
                            $price = Product::priceCalculation(
                                $id_shop,
                                $id_product,
                                $p_obj->af_id_comb,
                                $p_obj->af_id_country,
                                0,
                                0,
                                $id_currency,
                                $id_group,
                                1,
                                $g['use_tax'],
                                2,
                                false,
                                true,
                                true,
                                $p_obj->specificPrice,
                                true
                            );
                            if ($g['use_tax'] && !$p_obj->af_id_country) { // tax was not applied in priceCalculation
                                $price = $this->applyDefaultTaxForIndexedPrice($price, $p_obj, $id_shop);
                            }
                        }
                        if (!$g['has_specific_price']) {
                            $group_tax_prices[$g['use_tax']] = $price;
                        }
                        if ($c['is_default'] && !$c['has_specific_price']) {
                            $default_prices[$id_group] = $price; // default currency is first in loop
                        }
                        $value[$id_group . '_' . $id_currency] = $price;
                    }
                }
                break;
            case 't':
                $tags = $this->db->executeS('
                    SELECT t.id_tag, t.id_lang FROM ' . _DB_PREFIX_ . 'tag t
                    INNER JOIN ' . _DB_PREFIX_ . 'product_tag pt
                        ON (pt.id_tag = t.id_tag AND pt.id_product = ' . (int) $id_product . ')
                ');
                foreach ($tags as $t) {
                    $value[$t['id_lang']][$t['id_tag']] = $t['id_tag'];
                }
                break;
            case 'n':
            case 'r':
            case 'd':
            case 'm':
                $fields = ['n' => 'name', 'r' => 'reference', 'd' => 'date_add', 'm' => 'id_manufacturer'];
                $value = $p_obj->{$fields[$type]};
                break;
            case 'q':
                $q = ['new' => 1, 'used' => 2, 'refurbished' => 3];
                $value = isset($q[$p_obj->condition]) ? $q[$p_obj->condition] : 1;
                break;
            case 'v': // restricted visibility
                $v = ['both' => 0, 'catalog' => 1, 'search' => 2, 'none' => 3];
                $value = isset($v[$p_obj->visibility]) ? $v[$p_obj->visibility] : 0;
                break;
            case 'g': // restricted groups
                $groups_having_access = array_column($this->db->executeS('
                    SELECT DISTINCT(cg.id_group) FROM ' . _DB_PREFIX_ . 'category_group cg
                    INNER JOIN ' . _DB_PREFIX_ . 'category_product cp
                        ON cp.id_category = cg.id_category AND cp.id_product = ' . (int) $id_product . '
                '), 'id_group');
                if (!isset($this->i['available_groups'][$id_shop])) {
                    $this->i['available_groups'][$id_shop] = array_column($this->db->executeS('
                        SELECT DISTINCT(id_group) FROM ' . _DB_PREFIX_ . 'group_shop
                        WHERE id_shop = ' . (int) $id_shop . '
                    '), 'id_group');
                }
                $value = array_diff($this->i['available_groups'][$id_shop], $groups_having_access);
                break;
        }

        return $value;
    }

    public function applyDefaultTaxForIndexedPrice($price, $p_obj, $id_shop)
    {
        $tax_key = 'tax_rate_' . $p_obj->id_tax_rules_group . '_' . $id_shop;
        if (!isset($this->i[$tax_key])) {
            $id_country = $this->getDefaultCountry($id_shop);
            $this->i[$tax_key] = $this->getTaxRate($p_obj->id_tax_rules_group, $id_country);
        }
        if ($tax_rate = $this->i[$tax_key]) {
            $price += $price * $tax_rate;
            if ($p_obj->ecotax > 0) {
                $price -= $p_obj->ecotax * $tax_rate;
            }
            if ($p_obj->specificPrice && $p_obj->specificPrice['reduction_type'] == 'amount'
                && (!isset($p_obj->specificPrice['reduction_tax']) || $p_obj->specificPrice['reduction_tax'])) {
                $price += $p_obj->specificPrice['reduction'] * $tax_rate;
            }
        }

        return $price;
    }

    public function getDefaultCountry($id_shop)
    {
        if (!isset($this->i['default_country'][$id_shop])) {
            $this->i['default_country'][$id_shop] = Configuration::get('PS_COUNTRY_DEFAULT', null, null, $id_shop);
        }

        return $this->i['default_country'][$id_shop];
    }

    public function getTaxRate($id_tax_rules_group, $id_country)
    {
        return (float) $this->db->getValue('
            SELECT rate FROM ' . _DB_PREFIX_ . 'tax t
            INNER JOIN ' . _DB_PREFIX_ . 'tax_rule tr
                ON tr.id_tax = t.id_tax AND tr.id_tax_rules_group = ' . (int) $id_tax_rules_group . '
                AND tr.id_country = ' . (int) $id_country . '
        ') / 100;
    }

    public function unindexProducts($product_ids, $shop_ids = [])
    {
        if ($product_ids = $this->formatIDs($product_ids)) {
            $shop_ids = $shop_ids ? $shop_ids : $this->shopIDs('all');

            return $this->indexationData('erase', ['id_product' => $product_ids, 'id_shop' => $shop_ids]);
        }
    }

    public function assignSmartyVariablesForPagination($page, $products_num, $npp, $current_url = '')
    {
        $pages_nb = $this->getNumberOfPages($products_num, $npp);
        $siblings = 2; // 2 pages before and after active page in pagination
        $this->context->smarty->assign([
            'current_url' => $current_url,
            'p' => $page,
            'start' => ($page - $siblings > 1) ? $page - $siblings : 1,
            'stop' => ($page + $siblings < $pages_nb) ? $page + $siblings : $pages_nb,
            'pages_nb' => $pages_nb,
            'nb_products' => $products_num,
            'n' => $npp,
            'products_per_page' => $npp,
            // 'no_follow'   => 1,
        ]);
    }

    public function getNumberOfPages($products_num, $products_per_page)
    {
        return $products_per_page ? (int) ceil($products_num / $products_per_page) : 0;
    }

    public function prepareAjaxResponse($params)
    {
        $this->current_controller = $params['current_controller'];
        $products_data = $this->getFilteredProducts($params);
        $this->context->forced_sorting = ['by' => $params['orderBy'], 'way' => $params['orderWay']];
        $this->context->controller->addColorsToProductList($products_data['products']);
        $this->context->smarty->assign([
            'link' => $this->context->link,
            'static_token' => Tools::getToken(false),
            'af' => 1,
        ]);
        $page = $products_data['page'];
        $products_num = $products_data['filtered_ids_count'];
        $npp = $products_data['products_per_page'];
        $current_url = $this->sanitizeURL(Tools::getValue('current_url'));
        $ret = [
            'product_count_text' => $products_data['product_count_text'],
            'count_data' => $products_data['count_data'],
            'ranges' => $products_data['ranges'],
            'dynamic_min_max' => $products_data['dynamic_min_max'],
            'products_num' => $products_num,
            'time' => $products_data['time'],
            'hide_load_more_btn' => $products_data['hide_load_more_btn'],
            'trigger' => $products_data['trigger'],
        ];
        if (!empty($params['layout_required'])) {
            $ret['layout'] = $this->renderLayout();
        }
        if (!empty($this->sp)) {
            $this->sp->extendAjaxResponse($ret, $params);
            if (!empty($ret['seo_page']) && !empty($ret['seo_page']['link_rewrite'])) {
                $current_url = $ret['seo_page']['canonical'];
            }
        }
        $this->specificThemeAjaxActions($params);
        if ($this->is_modern) {
            $this->settings['general']['sorting_options'][$params['default_sorting']] = 1;
            $this->context->controller->page_name = $params['page_name']; // often used by third party modules
            Hook::exec('actionProductSearchAfter', ['products' => $products_data['products']]);
            $current_sorting_option = 'product.' . $params['orderBy'] . '.' . $params['orderWay'];
            $options = $this->getSortingOptions($current_sorting_option, $current_url);
            $current_label = isset($options[$current_sorting_option]['label']) ?
            $options[$current_sorting_option]['label'] : '';
            $this->context->smarty->assign([
                'listing' => [
                    'products' => $products_data['products'],
                    'pagination' => $this->getPaginationVariables($page, $products_num, $npp, $current_url),
                    'sort_orders' => $options,
                    'sort_selected' => $current_label,
                    'current_url' => $current_url,
                ],
                'urls' => $this->context->controller->getTemplateVarUrls(),
                'configuration' => $this->context->controller->getTemplateVarConfiguration(),
                'currency' => $this->context->controller->getTemplateVarCurrency(),
                'page' => ['page_name' => $params['page_name']],
            ]);
            $tpl_path = 'templates/catalog/_partials/';
            $ret['product_list_html'] = $this->fetchThemeTpl($tpl_path . 'products.tpl');
            $ret['product_list_top_html'] = $this->fetchThemeTpl($tpl_path . 'products-top.tpl');
            $ret['product_list_bottom_html'] = $this->fetchThemeTpl($tpl_path . 'products-bottom.tpl');
        } else {
            if ($ret['trigger'] != 'af_page') {
                $product_total_text = $products_num == 1 ? $this->l('There is 1 product.') :
                sprintf($this->l('There are %d products.'), $products_num);
                $ret['product_total_text'] = $product_total_text;
            }
            $this->context->smarty->assign([
                'products' => $products_data['products'],
                'class' => $this->product_list_class,
                'page_name' => $params['page_name'],
                'hide_left_column' => $params['hide_left_column'],
                'hide_right_column' => $params['hide_right_column'],
            ]);
            $this->assignSmartyVariablesForPagination($page, $products_num, $npp, $current_url);
            $ret['product_list_html'] = $this->context->smarty->fetch(_PS_THEME_DIR_ . 'product-list.tpl');
            $ret['pagination_html'] = $this->context->smarty->fetch(_PS_THEME_DIR_ . 'pagination.tpl');
            $this->context->smarty->assign('paginationId', $params['pagination_bottom_suffix']);
            $ret['pagination_bottom_html'] = $this->context->smarty->fetch(_PS_THEME_DIR_ . 'pagination.tpl');
        }
        if (!$products_num) {
            $ret['product_list_html'] = $this->display(__FILE__, 'views/templates/front/no-products.tpl');
        }

        return $ret;
    }

    public function specificThemeAjaxActions(&$params)
    {
        $identifier = $this->getSpecificThemeIdentifier();
        switch ($identifier) {
            case 'warehouse-17':
                $this->context->controller->php_self = $params['page_name']; // used in iqitthemeeditor
                $available_views = ['grid' => 1, 'list' => 1];
                $list_view = !empty($params['listView']) && !empty($available_views[$params['listView']]) ?
                $params['listView'] : 'grid';
                $this->context->cookie->__set('product_list_view', $list_view);
                break;
            case 'warehouse-16':
                $this->context->controller->php_self = $params['page_name']; // used in themeeditor
                break;
            case 'ayon-16':
                $this->context->smarty->assign(['nc_p_hover' => Configuration::get('NC_P_HOVERS')]);
                break;
            case 'AngarTheme-17':
                $this->context->smarty->assign([
                    'display_quickview' => (int) Configuration::get('PS_QUICK_VIEW'),
                    'psversion' => Configuration::get('ANGARTHEMECONFIGURATOR_PSVERSION'),
                ]);
                break;
            case 'venedor-17':
                if (!empty($this->context->controller->ajax)
                    && $pkts_module = Module::getInstanceByName('pk_themesettings')) {
                    // $pkts_module->getOptions() returns too many opions.
                    // so we select only options, related to product_miniature
                    // some pm_ options are encoded, but they are not used in dynamic listing
                    $pkts_options = array_column($this->db->executeS('
                        SELECT name, value FROM ' . _DB_PREFIX_ . 'pk_theme_settings
                        WHERE id_shop = ' . (int) $this->context->shop->id . ' AND name LIKE \'pm_%\'
                    '), 'value', 'name');
                    $this->context->smarty->assign(['pkts' => $pkts_options]);
                }
                break;
            case 'at_decor-17':
            case 'at_classico-17':
            case 'at_oreo-17':
            case 'at_movic-17':
                $apb = Module::getInstanceByName('appagebuilder');
                if ($apb->active) {
                    $product_settings = ApPageBuilderProductsModel::getActive($apb->getConfig('USE_MOBILE_THEME'));
                    $grid_cookie_key = $apb->getConfigName('PANEL_CONFIG') . '_grid_list';
                    $this->context->smarty->assign([
                        'productProfileDefault' => $product_settings['plist_key'],
                        'productClassWidget' => $product_settings['class'],
                        'LISTING_GRID_MODE' => isset($_COOKIE[$grid_cookie_key]) ? $_COOKIE[$grid_cookie_key] : 'grid',
                    ]);
                    if (class_exists('apPageHelper')) {
                        apPageHelper::setGlobalVariable($this->context);
                    }
                }
                break;
            case 'ZOneTheme-17':
                if (Module::isEnabled('zonethememanager')) {
                    $z_module = Module::getInstanceByName('zonethememanager');
                    if (method_exists($z_module, 'hookActionFrontControllerSetVariables')) {
                        $this->context->smarty->assign([
                            'modules' => [
                                'zonethememanager' => $z_module->hookActionFrontControllerSetVariables(),
                            ],
                        ]);
                    } else {
                        // based on zonethememanager.php -> hookDisplayHeader
                        require_once _PS_MODULE_DIR_ . 'zonethememanager/classes/ZManager.php';
                        $z = ZManager::getSettingsByShop();
                        $is_mobile = $this->isMobilePhone();
                        $this->context->smarty->assign([
                            'zonevars' => [
                                'product_quickview' => $z->category_settings['product_quickview'],
                                'product_addtocart' => $z->category_settings['addtocart_button'],
                                'product_grid_columns' => $z->category_settings['product_grid_columns'],
                                'cat_default_view' => $z->category_settings['default_product_view'],
                                'product_classes' => trim(implode(' ', array_filter([
                                    $z->category_settings['product_description'] ? 'pg-epd' : '',
                                    $z->category_settings['product_availability'] ? 'pg-eal' : '',
                                    $z->category_settings['product_colors'] ? 'pg-evl' : '',
                                    ($z->category_settings['product_button_new_line'] || $is_mobile) ? 'pg-bnl' : '',
                                ]))),
                                'lazy_loading' => false, // $z->general_settings['lazy_loading']
                                'product_countdown' => $z->product_settings['product_countdown'],
                                'is_mobile' => $is_mobile,
                            ],
                        ]);
                    }
                }
                break;
            case 'alysum-17':
                if (Module::isEnabled('pkthemesettings')) {
                    $pk = Module::getInstanceByName('pkthemesettings');
                    $theme_settings = $pk->helper->repository->getSmartyConfig();
                    $theme_settings['gs_lazy_load'] = 0; // list updated faster + no extra js required
                    $this->context->smarty->assign(['pktheme' => $theme_settings]);
                }
                break;
        }
    }

    public function fetchThemeTpl($path)
    {
        $html = '';
        if (file_exists(_PS_THEME_DIR_ . $path)) {
            $html = $this->context->smarty->fetch(_PS_THEME_DIR_ . $path);
        } elseif (file_exists(_PS_PARENT_THEME_DIR_ . $path)) {
            $html = $this->context->smarty->fetch(_PS_PARENT_THEME_DIR_ . $path);
        }

        return $html;
    }

    public function getSpecificThemeIdentifier()
    {
        return $this->getCurrentThemeName() . '-' . ($this->is_modern ? '17' : '16');
    }

    public function getCurrentThemeName()
    {
        $theme_name = _THEME_NAME_;
        if ($this->is_modern && _PARENT_THEME_NAME_) {
            $theme_name = _PARENT_THEME_NAME_; // _THEME_NAME_ can be different if child theme is used
        }

        return $theme_name;
    }

    public function renderLayout()
    {
        $this->context->smarty->assign([
            'product_list_class' => $this->product_list_class,
            'af_ids' => $this->settings['themeid'],
        ]);
        $tpl_path = 'views/templates/front/basic-layout' . ($this->is_modern ? '-17' : '') . '.tpl';

        return $this->display(__FILE__, $tpl_path);
    }

    public function formatOrder($by, $way)
    {
        $compact_order_names = ['name' => 'n', 'date_add' => 'd', 'reference' => 'r', 'price' => 'p', 'weight' => 'w'];
        if (isset($compact_order_names[$by])) {
            $by = $compact_order_names[$by];
        }
        if (!in_array($way, ['asc', 'desc'])) {
            $way = 'asc';
        }

        return ['by' => $by, 'way' => $way, 'flag' => $by == 'n' ? SORT_NATURAL : SORT_REGULAR];
    }

    public function getFilteredProducts(&$params)
    {
        $start_time = microtime(true);
        $this->prepareParamsForFiltering($params);
        $filtered_data = $this->getFilteredData($params);
        $ret = $this->prepareDataForDisplay($filtered_data, $params);
        $ret['time'] = microtime(true) - $start_time;
        // d($ret['time']);
        return $ret;
    }

    public function prepareParamsForFiltering(&$params)
    {
        $customer_group = Group::getCurrent();
        $params += [
            'id_shop' => $this->id_shop,
            'id_lang' => $this->id_lang,
            'id_currency' => $this->context->currency->id,
            'id_customer_group' => $customer_group->id,
            // 'show_prices' => $customer_group->show_prices,
            'trigger' => $this->getSafeValue('trigger', 'af_page'),
            'order' => $this->formatOrder($params['orderBy'], $params['orderWay']),
            'ranges' => ['p' => [], 'w' => []],
            'special_ids' => [],
            'filters' => [],
            'sliders' => [],
            'available_options' => [],
            'and' => [],
            'required_matching' => [],
            'other_required_matching' => [],
            'ajax' => 0,
            'r_min_max' => [],
        ];
        if ($this->settings['indexation']['dynamic_tax'] && $customer_group->price_display_method < 1) {
            $params['dynamic_tax'] = $this->settings['indexation']['dynamic_tax'];
        }
        $params['customer_groups'] = explode(',', $params['customer_groups']);
        $params['count_data_required'] = $params['count_data'] || $params['hide_zero_matches']
            || $params['dim_zero_matches'];
        if ($params['ajax']) {
            foreach ($params['available_options'] as $key => $grouped_options) {
                foreach ($grouped_options as $id_group => $options) {
                    $options = explode(',', $options);
                    $params['available_options'][$key][$id_group] = array_combine($options, $options);
                    if (isset($params['filters'][$key][$id_group])) {
                        $applied_values = $params['filters'][$key][$id_group];
                        $params['filters'][$key][$id_group] = array_combine($applied_values, $applied_values);
                    }
                }
            }
        }
        if (isset($params['filters']['in_stock'])) {
            $params['in_stock'] = 1;
            unset($params['filters']['in_stock']); // in_stock is processed differently
        }
        if (!empty($params['numeric_slider_values'])) {
            $this->slider()->assignParamsForNumericSliders($params);
        }
        foreach ($params['ranges'] as $identifier => $range) { // price/weight
            if ($range['is_slider'] = isset($params['sliders'][$identifier])) {
                $slider = $params['sliders'][$identifier][0];
                if ($this->slider()->isTriggered($slider)) {
                    $params['filters'][$identifier][0][0] = [$slider['from'], $slider['to']];
                }
                if ($this->i['p_comb'] && $identifier == 'p') {
                    $range['check_combinations'] = 1;
                }
                if (empty($params['filters'][$identifier])) {
                    if (!empty($params['filters']) || !empty($params['in_stock'])) {
                        $params['r_min_max'][$identifier] = 1; // calculate min-max for matching values
                    } else {
                        $params['r_min_max'][$identifier] = 2; // calculate min-max for all values
                    }
                } elseif (!$params['ajax']) {
                    $params['r_min_max'][$identifier] = 2; // calculate min-max for all values
                }
            } elseif (isset($params['available_options'][$identifier])) {
                $this->rangeFilter()->assignParams($identifier, $params, $range);
            } else {
                unset($params['ranges'][$identifier]);
                continue;
            }
            $params['ranges'][$identifier] = $range;
        }
        $params['selected_atts'] = isset($params['filters']['a']) ? $params['filters']['a'] : [];
        $params['oos'] = $this->oos('prepareParams', $params);
        if ($params['oos']['check_combinations'] || ($params['selected_atts'] && $params['combination_results'])) {
            $this->selected_combinations = [];
        }
        $this->use_merged_attributes = !empty($params['merged_attributes']);
        foreach (array_keys($this->getSpecialFilters()) as $s) {
            if ($s != 'in_stock' && !empty($params['available_options'][$s])) {
                $params['special_ids'][$s] = $this->getSpecialControllerIds($s);
            }
        }
        // adjust price-related params based on indexations settings
        if (!$this->settings['indexation']['p']) {
            foreach (['available_options', 'ranges', 'sliders', 'filters', 'r_min_max'] as $param_key) {
                unset($params[$param_key]['p']);
            }
        } elseif (isset($params['available_options']['p']) || isset($params['sliders']['p'])
            || $params['order']['by'] == 'p') {
            $this->i['sql_price_column_'] = $this->indexationData('sqlPriceColumn', $params);
        } elseif ($this->i['p_comb']) {
            $this->i['p_comb'] = 0;
        }
        // define required matches
        foreach ($params['filters'] as $key => $grouped_filters) {
            $params['required_matching'][$key] = array_fill_keys(array_keys($grouped_filters), 1);
        }
        if ($params['count_data_required']) {
            foreach ($params['required_matching'] as $key => $grouped_values) {
                foreach (array_keys($grouped_values) as $id_group) {
                    if (!isset($params['sliders'][$key][$id_group])) {
                        $r_matching = isset($params['and'][$key][$id_group]) ? $params['required_matching']
                            : $this->getOtherMatching($params['required_matching'], $key, $id_group);
                        $params['other_required_matching'][$key][$id_group] = $r_matching;
                        if ($key == 'a' && $params['oos']['reset_a']
                            && !isset($params['required_matching_except_a'])) {
                            unset($r_matching['a']);
                            $params['required_matching_except_a'] = $r_matching;
                        }
                    }
                }
            }
        }
    }

    public function getFilteredData(&$params)
    {
        // &$params is passed by reference because 'ranges' may be updated: min/max and available_range_options
        $filtered_ids = $move_to_the_end = $all_matches = $sorted_combinations = [];
        $count_data = $this->prepareCountData($params);
        if ($params['oos']['check_combinations']) {
            $this->prepareSortedCombinationsData($params['oos'], $sorted_combinations);
        }
        foreach ($this->indexationData('get', $params) as $p) {
            if (!empty($p['g']) && !array_diff($params['customer_groups'], explode(',', $p['g']))) {
                continue;
            }
            $id = $p['id'];
            foreach ($this->i['p_arr_keys'] as $key) {
                $p[$key] = explode(',', $p[$key]);
            }
            if ($params['oos']['check_combinations']) {
                if (isset($sorted_combinations[$id][0])) {
                    $p['combinations'] = $sorted_combinations[$id];
                    $default = $p['combinations'][0];
                    unset($p['combinations'][0]);
                    $p['qty'] = !$p['combinations'] ? $default['qty'] : 0;
                    if ($this->i['p_comb']) {
                        $p['p'] = $default['p'];
                    }
                } elseif (!$params['oos']['exclude']) {
                    $p['qty'] = 0;
                    $p['combinations'] = [];
                } else {
                    continue;
                }
            }
            if (!$params['ajax']) { // first load
                foreach ($this->i['p_arr_keys'] as $key) {
                    foreach ($p[$key] as $param_id) {
                        $all_matches[$key][$param_id] = 1;
                    }
                }
            }
            $current_matching = [];
            foreach ($params['filters'] as $key => $grouped_filters) {
                foreach ($grouped_filters as $id_group => $applied_values) {
                    if (isset($params['ranges'][$key])) {
                        if (isset($params['ranges'][$key]['check_combinations']) && !empty($p['combinations'])) {
                            foreach ($p['combinations'] as $id_comb => $comb) {
                                if (!Toolkit::withinRanges($comb[$key], $applied_values)) {
                                    unset($p['combinations'][$id_comb]);
                                }
                            }
                            $intersect = $p['combinations'] ? true : false;
                        } else {
                            $intersect = Toolkit::withinRanges($p[$key], $applied_values);
                        }
                    } elseif (isset($params['special_ids'][$key])) {
                        $intersect = isset($params['special_ids'][$key][$id]);
                    } elseif (isset($params['and'][$key][$id_group])) {
                        $intersect = array_intersect($p[$key], $applied_values);
                        $intersect = count($intersect) == count($applied_values);
                    } else {
                        $intersect = false;
                        foreach ($p[$key] as $k) {
                            if (isset($applied_values[$k])) {
                                $intersect = true;
                                break;
                            }
                        }
                    }
                    if ($intersect) {
                        $current_matching[$key][$id_group] = 1;
                    }
                }
            }
            if ($params['oos']['check_combinations']) {
                if ($params['oos']['reset_a']) {
                    $p['a'] = [];
                    unset($current_matching['a']);
                    if (isset($params['required_matching_except_a'])
                        && $current_matching == $params['required_matching_except_a']) {
                        $p['extra_count_a'] = [];
                    }
                }
                foreach ($p['combinations'] as $id_comb => $c_data) {
                    $atts = $c_data['a'];
                    if (!$ok = empty($params['oos']['combinations_to_match'])) {
                        foreach ($params['oos']['combinations_to_match'] as $selected_atts) {
                            if (array_intersect($selected_atts, $atts) == $selected_atts) {
                                $ok = true;
                            } elseif (isset($p['extra_count_a'])) {
                                foreach (array_keys($selected_atts) as $id_group) {
                                    if (isset($atts[$id_group])) {
                                        $other_selected_atts = $selected_atts;
                                        unset($other_selected_atts[$id_group]);
                                        if (!$other_selected_atts
                                            || array_intersect($other_selected_atts, $atts) == $other_selected_atts) {
                                            $p['extra_count_a'][$id_group][$atts[$id_group]] = $atts[$id_group];
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if ($ok) {
                        if ($params['oos']['reset_a']) {
                            foreach ($atts as $id_group => $id_att) {
                                $p['a'][$id_att] = $id_att;
                                if (isset($params['required_matching']['a'][$id_group])) {
                                    $current_matching['a'][$id_group] = 1;
                                }
                            }
                        }
                        if ($c_data['qty'] > 0) {
                            $p['qty'] += $c_data['qty'];
                        }
                        if (isset($this->selected_combinations) && !isset($this->selected_combinations[$id])) {
                            $this->selected_combinations[$id] = $id_comb;
                            if ($this->i['p_comb']) {
                                $p['p'] = $c_data['p'];
                            }
                        }
                    }
                }
                if ($params['oos']['reset_a'] && !empty($params['and']['a'])) {
                    foreach (array_keys($params['and']['a']) as $id_group) {
                        if (isset($current_matching['a'][$id_group])) {
                            $atts_to_match = $params['filters']['a'][$id_group];
                            if (count(array_intersect($p['a'], $atts_to_match)) != count($atts_to_match)) {
                                unset($current_matching['a'][$id_group]);
                            }
                        }
                    }
                }
            } elseif ($params['oos']['behaviour'] || isset($count_data['in_stock'])) {
                $p['qty'] = $this->getProductQty($id, $params['oos']);
            }
            $matching = $current_matching == $params['required_matching'];
            if (isset($p['qty']) && $p['qty'] < 1 && !isset($params['oos']['allowed_ids'][$id])) {
                if ($params['oos']['move']) {
                    $move_to_the_end[$id] = $id;
                } elseif ($params['oos']['exclude']) {
                    $matching = false;
                    if (!isset($params['filters']['a'])) {
                        $current_matching['a'] = ['undefined']; // OOS excluded, but atts not checked
                    }
                    // TODO: unset all_matches[c][id] if product is excluded because of OOS and no filters are selected
                }
                if ($params['oos']['count_only_in_stock']) {
                    $p['no_count'] = 1;
                }
            }
            if ($params['count_data_required']) {
                if (empty($p['no_count'])) {
                    foreach ($params['available_options'] as $key => $grouped_options) {
                        if (!isset($count_data[$key])) {
                            continue;
                        }
                        if (isset($params['ranges'][$key])) {
                            $value = $p[$key] . '';
                            if (!isset($count_data[$key][$value])) {
                                $count_data[$key][$value] = 0;
                            }
                            if ($matching) {
                                ++$count_data[$key][$value];
                            } elseif (isset($params['other_required_matching'][$key][0])) {
                                $other_matching = $this->getOtherMatching($current_matching, $key, 0);
                                if ($other_matching == $params['other_required_matching'][$key][0]) {
                                    ++$count_data[$key][$value];
                                }
                            }
                            continue;
                        }
                        if ($matching) {
                            if ($key == 'in_stock') {
                                if ($p['qty'] > 0) {
                                    ++$count_data[$key][1];
                                }
                            } elseif (isset($params['special_ids'][$key])) {
                                if (isset($params['special_ids'][$key][$id])) {
                                    ++$count_data[$key][1];
                                }
                            } else {
                                foreach ($p[$key] as $param_id) {
                                    if (isset($count_data[$key][$param_id])) {
                                        ++$count_data[$key][$param_id];
                                    }
                                }
                            }
                        } elseif (!empty($p[$key])) {
                            foreach ($grouped_options as $id_group => $option_ids) {
                                if (isset($params['other_required_matching'][$key][$id_group])) {
                                    $other_matching = $this->getOtherMatching($current_matching, $key, $id_group);
                                    if ($other_matching == $params['other_required_matching'][$key][$id_group]) {
                                        foreach ($p[$key] as $param_id) {
                                            if (isset($option_ids[$param_id])) {
                                                ++$count_data[$key][$param_id];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                if (!empty($p['extra_count_a'])) {
                    foreach ($p['extra_count_a'] as $id_group_extra => $extra_atts) {
                        if ($matching || !isset($params['and']['a'][$id_group_extra])) {
                            foreach ($extra_atts as $id_att) {
                                if (!isset($p['a'][$id_att])) { // no need || !empty($p['no_count'])
                                    ++$count_data['a'][$id_att];
                                }
                            }
                        }
                    }
                }
            }
            if (empty($p['no_count'])) {
                foreach ($params['r_min_max'] as $key => $action) {
                    if ($p[$key] >= 0 && ($action == 2 || $matching)) {
                        $this->slider()->updRangeMinMax($params['ranges'][$key], $p[$key]);
                    }
                }
            }
            if (!$matching) {
                continue;
            }
            $filtered_ids[$id] = $id;
            if (isset($p[$params['order']['by']])) { // n, d, r, p, w, custom
                $filtered_ids[$id] = $p[$params['order']['by']];
            } else {
                switch ($params['order']['by']) {
                    case 'quantity':
                        $filtered_ids[$id] = isset($p['qty']) ? $p['qty'] : $this->getProductQty($id, $params['oos']);
                        break;
                    case 'sales':
                        $filtered_ids[$id] = $this->getProductSales($id);
                        break;
                    case 'position':
                        $filtered_ids[$id] = $this->getProductPosition($id, $params);
                        break;
                    case 'manufacturer_name':
                        $filtered_ids[$id] = $this->getManufacturerName((int) current($p['m']));
                        break;
                }
            }
        }
        foreach ($params['ranges'] as $identifier => $r) {
            if (isset($r['step'])) { // ranged price/weight
                $this->rangeFilter()->processData($identifier, $params, $count_data);
            }
        }
        $this->slider()->prepareDynamicMinMax($params, $count_data);
        $this->sortFilteredIDs($filtered_ids, $move_to_the_end, $params);

        return [
            'ids' => $filtered_ids,
            'count' => $count_data,
            'all_matches' => $all_matches,
        ];
    }

    public function prepareCountData($params)
    {
        $count_data = [];
        if ($params['count_data_required']) {
            foreach ($params['available_options'] as $key => $grouped_options) {
                $count_data[$key] = [];
                foreach ($grouped_options as $options) {
                    foreach ($options as $id_opt) {
                        $count_data[$key][$id_opt] = 0;
                    }
                }
            }
        }

        return $count_data;
    }

    public function prepareDataForDisplay($filtered_data, $params)
    {
        $page_keepers = ['af_page', 'p_type'];
        if (!empty($params['page']) && in_array($params['trigger'], $page_keepers)) {
            $page = (int) $params['page'];
        } else {
            $page = 1;
        }
        $products_per_page = $params['nb_items'];
        $offset = ($page - 1) * $products_per_page;
        $ids = array_slice($filtered_data['ids'], $offset, $products_per_page);
        if (isset($this->selected_combinations)) {
            if (!$params['oos']['check_combinations'] && empty($this->selected_combinations)) {
                $this->selected_combinations = $this->getSelectedCombinations($ids, $params['selected_atts']);
            } else {
                $this->selected_combinations = array_intersect_key($this->selected_combinations, array_flip($ids));
            }
        }
        $total = count($filtered_data['ids']);
        $ret = [
            'filtered_ids_count' => $total,
            'page' => $page,
            'products_per_page' => $products_per_page,
            'products' => $this->getProductsInfos($ids, $params['id_lang'], $params['id_shop']),
            'count_data' => $filtered_data['count'],
            'all_matches' => $filtered_data['all_matches'],
            'ranges' => $params['ranges'], // TODO: optimize ???
            'dynamic_min_max' => $params['dynamic_min_max'],
            'trigger' => $params['trigger'],
            'product_count_text' => '',
            'hide_load_more_btn' => false,
        ];
        if ($params['p_type'] > 1) { // load more/infinite scroll
            $page_from = isset($params['page_from']) ? $params['page_from'] : $page;
            $page_to = isset($params['page_to']) ? $params['page_to'] : $page;
            $from = $page_from * $products_per_page - $products_per_page + 1;
            $to = $page_to * $products_per_page;
            if ($total <= $to) {
                $to = $total;
                $ret['hide_load_more_btn'] = true;
            }
            if ($total) {
                $txt = $this->l('Showing %1$d - %2$d of %3$d items');
                $ret['product_count_text'] = sprintf($txt, $from, $to, $total);
            }
        }

        return $ret;
    }

    public function sortFilteredIDs(&$filtered_ids, &$move_to_the_end, $params)
    {
        if ($params['order']['by'] == 'random') {
            srand($params['random_seed']);
            shuffle($filtered_ids); // 0 => $id_0, 1 => $id_1, 2 => $id_2 etc...
        } else {
            if (!$this->i['p_comb'] && $params['order']['by'] == 'p' && !empty($params['combination_results'])) {
                $this->adjustCombinationPrices($params['id_shop'], $filtered_ids, $params['selected_atts']);
            }
            $params['order']['way'] == 'asc' ? asort($filtered_ids, $params['order']['flag']) :
            arsort($filtered_ids, $params['order']['flag']);
            $filtered_ids = array_keys($filtered_ids);
        }
        if ($move_to_the_end && $params['order']['by'] != 'quantity') { // instockfirst
            foreach ($filtered_ids as $pos => $id) {
                if (isset($move_to_the_end[$id])) {
                    unset($filtered_ids[$pos]);
                    $filtered_ids[] = $id;
                }
            }
            $filtered_ids = array_values($filtered_ids);
            unset($move_to_the_end);
        }
    }

    public function prepareSortedCombinationsData($params, &$sorted_combinations)
    {
        $cache_id = $this->combinations('getCacheID', $params);
        if (!$cache_id || !$cached_data = $this->cache('get', $cache_id)) {
            $raw_data = $this->combinations('getRawData', $params);
            foreach ($raw_data as $d) {
                if (!$d['id_comb']) {
                    $sorted_combinations[$d['id_product']][0] = [
                        'a' => [],
                        'qty' => $d['qty'],
                    ];
                } elseif ($d['id_group']) {
                    $sorted_combinations[$d['id_product']][$d['id_comb']]['a'][$d['id_group']] = $d['id_att'];
                    $sorted_combinations[$d['id_product']][$d['id_comb']]['qty'] = $d['qty'];
                }
            }
            if ($params['pricesdrop']) {
                $discounted_combinations = [];
                foreach ($this->getDiscountedIDs(true) as $id_product => $comb_ids) {
                    if (isset($sorted_combinations[$id_product])) {
                        $c = $sorted_combinations[$id_product];
                        if (!isset($comb_ids[0]) && $c = array_intersect_key($c, $comb_ids)) {
                            $c[0] = $sorted_combinations[$id_product][0];
                        }
                        if ($c) {
                            $discounted_combinations[$id_product] = $c;
                        }
                    }
                }
                $sorted_combinations = $discounted_combinations;
            }
            if ($this->i['p_comb']) {
                $sorted_combinations = $this->combinationPrices('define', $sorted_combinations);
            }
            if (!empty($this->use_merged_attributes)) {
                $this->mergedValues()->mapAttributesInSortedCombinations($sorted_combinations);
            }
            if ($cache_id) {
                $this->cache('save', $cache_id, $sorted_combinations);
            }
        } else {
            $sorted_combinations = $cached_data;
        }
    }

    public function combinations($action, $params = [])
    {
        $ret = [];
        switch ($action) {
            case 'getCacheID':
                $cache_params = array_diff_key($params, ['combinations_to_match' => 0]);
                if ($ret = $this->cacheID('comb_data', $cache_params)) {
                    if ($this->i['p_comb']) {
                        $ret .= $this->combinationPrices('extendCacheID');
                    }
                    if ($this->use_merged_attributes) {
                        $ret .= '_m';
                    }
                }
                break;
            case 'getRawData':
                $ret = $this->db->executeS($this->combinations('prepareQuery', $params));
                break;
            case 'prepareQuery':
                $ret = new DbQuery();
                if ($params['stock_mng']) {
                    $ret->select('sa.quantity AS qty');
                    $ret->orderBy('sa.quantity > 0 DESC');
                } else {
                    $ret->select('1 AS qty');
                }
                $ret->select('
                    sa.id_product_attribute AS id_comb,
                    pac.id_attribute AS id_att,
                    sa.id_product,
                    a.id_attribute_group AS id_group
                ');
                $ret->from('stock_available', 'sa');
                $ret->innerJoin('product_shop', 'ps', 'ps.id_product = sa.id_product
                    AND ' . $this->isVisibleQuery('ps', $params['id_shop']));
                $ret->leftJoin('product_attribute_shop', 'pas', 'pas.id_product_attribute = sa.id_product_attribute
                    AND pas.id_shop = ps.id_shop');
                $ret->leftJoin(
                    'product_attribute_combination',
                    'pac',
                    'pac.id_product_attribute = pas.id_product_attribute'
                );
                $ret->leftJoin('attribute', 'a', 'a.id_attribute = pac.id_attribute');
                $ret->where($this->oos('extendQuery', $params));
                $ret->orderBy('pas.default_on DESC, pas.price, pac.id_attribute, pac.id_product_attribute');
                break;
        }

        return $ret;
    }

    public function getProductQty($id, $stock_params)
    {
        if (!isset($this->qty_data)) {
            $this->qty_data = array_column($this->db->executeS('
                SELECT sa.id_product, sa.quantity as qty
                FROM ' . _DB_PREFIX_ . 'stock_available sa
                INNER JOIN ' . _DB_PREFIX_ . 'product_shop ps
                    ON ps.id_product = sa.id_product AND ps.active = 1
                    AND ps.id_shop = ' . (int) $stock_params['id_shop'] . '
                WHERE ' . $this->oos('extendQuery', $stock_params) . '
                AND sa.id_product_attribute = 0
            '), 'qty', 'id_product');
        }

        return isset($this->qty_data[$id]) ? $this->qty_data[$id] : 0;
    }

    public function getProductSales($id)
    {
        if (!isset($this->relative_sales_data)) {
            // not real sales_num, but relative number compared to sales of other products
            $this->relative_sales_data = array_flip(array_reverse(array_keys($this->getBestSalesIDs())));
        }

        return isset($this->relative_sales_data[$id]) ? $this->relative_sales_data[$id] : 0;
    }

    public function getProductPosition($id_product, $params)
    {
        if (!isset($this->all_positions)) {
            $this->all_positions = [];
            if (!empty($params['controller_product_ids'])) {
                $position = 1;
                foreach ($this->formatIDs($params['controller_product_ids']) as $id) {
                    $this->all_positions[$id] = $position++;
                }
            } else {
                $position_id_cat = $params['id_category'];
                // if only 1 category is checked, sort by positions in that category
                if (!empty($params['filters']['c'])) {
                    foreach ($params['filters']['c'] as $cat_ids) {
                        if (count($cat_ids) == 1) {
                            $position_id_cat = current($cat_ids);
                            break;
                        }
                    }
                }
                $this->all_positions = array_column($this->db->executeS('
                    SELECT id_product AS id, position FROM ' . _DB_PREFIX_ . 'category_product
                    WHERE id_category = ' . (int) $position_id_cat . '
                '), 'position', 'id');
            }
        }

        return isset($this->all_positions[$id_product]) ? $this->all_positions[$id_product] : 'n';
    }

    public function getManufacturerName($id_manufacturer)
    {
        if (!isset($this->m_names)) {
            $this->m_names = [];
            $raw_data = $this->db->executeS('
                SELECT id_manufacturer AS id, name FROM ' . _DB_PREFIX_ . 'manufacturer WHERE active = 1
            ');
            foreach ($raw_data as $d) {
                $this->m_names[$d['id']] = $d['name'];
            }
        }

        return isset($this->m_names[$id_manufacturer]) ? $this->m_names[$id_manufacturer] : '';
    }

    /* temporary workaround for calculating/predicting combination prices for proper sorting */
    public function adjustCombinationPrices($id_shop, &$filtered_ids, $selected_atts)
    {
        if ($selected_atts) {
            if (empty($this->selected_combinations)) {
                $ids = array_keys($filtered_ids);
                $this->selected_combinations = $this->getSelectedCombinations($ids, $selected_atts);
            }
            if ($combination_ids_ = $this->sqlIDs($this->selected_combinations)) {
                $raw_data = $this->db->executeS('
                    SELECT pa.id_product AS id, pa.id_product_attribute AS ipa, pa.price,
                        pa.default_on AS df, ps.price AS base_price
                    FROM ' . _DB_PREFIX_ . 'product_attribute pa
                    INNER JOIN ' . _DB_PREFIX_ . 'product_shop ps
                        ON ps.id_product = pa.id_product AND ps.id_shop = ' . (int) $id_shop . '
                    WHERE pa.id_product_attribute IN (' . $combination_ids_ . ') OR pa.default_on = 1
                ');
                $non_default_impacts = $rates = [];
                foreach ($raw_data as $d) {
                    $id = $d['id'];
                    $raw_price = $d['base_price'] + $d['price'];
                    if ($d['df']) {
                        $indexed_price = isset($filtered_ids[$id]) ? $filtered_ids[$id] : 0;
                        $rates[$id] = $raw_price ? $indexed_price / $raw_price : 1;
                    } else {
                        $non_default_impacts[$id] = $raw_price;
                    }
                }
                foreach ($non_default_impacts as $id_product => $raw_price) {
                    if (isset($rates[$id_product]) && isset($filtered_ids[$id_product])) {
                        $filtered_ids[$id_product] = $raw_price * $rates[$id_product];
                    }
                }
            }
        }
    }

    public function getSelectedCombinations($product_ids, $selected_atts)
    {
        $selected_combinations = $att_ids = $sorted_combinations = [];
        if (!$product_ids || !$selected_atts) {
            return $selected_combinations;
        }
        if (!empty($this->use_merged_attributes)) {
            $this->mergedValues()->replaceMergedAttsWithOriginalValues($selected_atts);
        }
        $selected_groups_count = count($selected_atts);
        foreach ($selected_atts as $atts) {
            $att_ids += $atts;
        }
        $raw_data = $this->db->executeS('
            SELECT pac.id_attribute, pac.id_product_attribute as id_comb, pa.id_product
            FROM ' . _DB_PREFIX_ . 'product_attribute_combination pac
            LEFT JOIN ' . _DB_PREFIX_ . 'product_attribute pa
                ON pa.id_product_attribute = pac.id_product_attribute
            ' . Shop::addSqlAssociation('product_attribute', 'pa') . '
            WHERE pa.id_product IN (' . $this->sqlIDs($product_ids) . ')
            AND pac.id_attribute IN (' . $this->sqlIDs($att_ids) . ')
            ORDER BY pa.default_on DESC, pa.id_product_attribute ASC
        ');
        foreach ($raw_data as $d) {
            $sorted_combinations[$d['id_product']][$d['id_comb']][$d['id_attribute']] = $d['id_attribute'];
        }
        foreach ($sorted_combinations as $id_product => $combinations) {
            foreach ($combinations as $id_comb => $atts) {
                if (!isset($selected_combinations[$id_product]) && count($atts) == $selected_groups_count) {
                    $selected_combinations[$id_product] = $id_comb;
                }
            }
        }

        return $selected_combinations;
    }

    public function getProductsInfos($ids, $id_lang, $id_shop, $get_all_properties = true)
    {
        if (!$ids) {
            return [];
        }
        $products_infos = [];
        $products_data = $this->db->executeS('
            SELECT p.*, product_shop.*, pl.*, image.id_image, il.legend, m.name AS manufacturer_name,
            ' . $this->isNewQuery('product_shop') . ' AS new
            FROM ' . _DB_PREFIX_ . 'product p
            ' . Shop::addSqlAssociation('product', 'p') . '
            INNER JOIN ' . _DB_PREFIX_ . 'product_lang pl
                ON (pl.id_product = p.id_product' . Shop::addSqlRestrictionOnLang('pl') . '
                AND pl.id_lang = ' . (int) $id_lang . ')
            LEFT JOIN ' . _DB_PREFIX_ . 'image image
                ON (image.id_product = p.id_product AND image.cover = 1)
            LEFT JOIN ' . _DB_PREFIX_ . 'image_lang il
                ON (il.id_image = image.id_image AND il.id_lang = ' . (int) $id_lang . ')
            LEFT JOIN ' . _DB_PREFIX_ . 'manufacturer m
                ON m.id_manufacturer = p.id_manufacturer
            WHERE p.id_product IN (' . $this->sqlIDs($ids) . ')
        ');
        $positions = array_flip($ids);
        if ($this->is_modern && $get_all_properties) {
            $factory = new ProductPresenterFactory($this->context, new TaxConfiguration());
            $factory_presenter = $factory->getPresenter();
            $factory_settings = $factory->getPresentationSettings();
            $lang_obj = new Language($id_lang);
        }
        if (!empty($this->selected_combinations)) {
            $combination_images = $this->getCombinationImages($this->selected_combinations, $id_lang);
        }
        foreach ($products_data as $pd) {
            $id_product = (int) $pd['id_product'];
            // oos data is kept updated in stock_available table
            // joining this table in query significantly increases time if there are many $ids
            $pd['out_of_stock'] = StockAvailable::outOfStock($id_product, $id_shop);
            $pd['id_product_attribute'] = $pd['cache_default_attribute']; // kept up to date in indexProduct()
            if (!empty($this->selected_combinations[$id_product])) {
                $pd['id_product_attribute'] = $id_comb = (int) $this->selected_combinations[$id_product];
                if (!empty($combination_images[$id_comb])) {
                    $pd['id_image'] = $pd['cover_image_id'] = $combination_images[$id_comb]['id_image'];
                    $pd['legend'] = $combination_images[$id_comb]['legend'];
                }
            }
            if ($get_all_properties) {
                if ($this->is_modern) {
                    if (Tools::getValue('controller') == 'ajax') {
                        $pd = Product::getProductProperties($id_lang, $pd);
                        $pd = $factory_presenter->present($factory_settings, $pd, $lang_obj);
                    }
                } else {
                    $pd = Product::getProductProperties($id_lang, $pd);
                    if ($pd['id_product_attribute'] != $pd['cache_default_attribute']) {
                        $pd['link'] .= $this->addAnchor($id_product, (int) $pd['id_product_attribute'], true);
                    }
                }
            }
            $products_infos[$positions[$id_product]] = $pd;
        }
        ksort($products_infos);

        return $products_infos;
    }

    public function getCombinationImages($combination_ids, $id_lang)
    {
        $combination_images = [];
        if ($combination_ids_ = $this->sqlIDs($combination_ids)) {
            $combination_images_data = $this->db->executeS('
                SELECT pai.id_product_attribute, i.id_image, il.legend
                FROM ' . _DB_PREFIX_ . 'image i
                INNER JOIN ' . _DB_PREFIX_ . 'product_attribute_image pai
                    ON pai.id_image = i.id_image
                LEFT JOIN ' . _DB_PREFIX_ . 'image_lang il
                    ON (il.id_image = i.id_image AND il.id_lang = ' . (int) $id_lang . ')
                WHERE pai.id_product_attribute IN (' . $combination_ids_ . ')
                ORDER BY i.cover DESC, i.position ASC
            ');
            foreach ($combination_images_data as $row) {
                if (!isset($combination_images[$row['id_product_attribute']])) {
                    $combination_images[$row['id_product_attribute']] = $row;
                }
            }
        }

        return $combination_images;
    }

    /*
    * Based on $product->getAnchor()
    */
    public function addAnchor($id_product, $id_product_attribute, $with_id = false)
    {
        $attributes = Product::getAttributesParams($id_product, $id_product_attribute);
        $anchor = '#';
        $sep = Configuration::get('PS_ATTRIBUTE_ANCHOR_SEPARATOR');
        foreach ($attributes as &$a) {
            foreach ($a as &$b) {
                $b = str_replace($sep, '_', Tools::link_rewrite($b));
            }
            $id = ($with_id && !empty($a['id_attribute']) ? (int) $a['id_attribute'] . $sep : '');
            $anchor .= '/' . $id . $a['group'] . $sep . $a['name'];
        }

        return $anchor;
    }

    public function hookDisplayCustomerAccount()
    {
        return ''; // deprecated since 3.2.5
    }

    public function cacheID($type, $params)
    {
        if (!empty($this->settings['caching'][$type])) {
            return $type . '_' . implode('_', array_map('intval', $params));
        }
    }

    public function cache($action, $cache_id, $data = '', $cache_time = 3600)
    {
        $ret = true;
        $full_path = $this->local_path . 'cache/' . $cache_id;
        switch ($action) {
            case 'get':
                if ($ret = file_exists($full_path) && (time() - filemtime($full_path) < $cache_time)) {
                    $ret = json_decode(Tools::file_get_contents($full_path), true);
                }
                break;
            case 'save':
                $ret = file_put_contents($full_path, json_encode($data)) !== false;
                break;
            case 'clear':
                // cached file names can include different parameters, so we unlink all files matching main path
                foreach (glob($full_path . '*') as $path) {
                    $ret &= unlink($path);
                }
                break;
            case 'info':
                if ($files = $info = glob($full_path . '*')) {
                    $info = sprintf(
                        $this->l('Cache size: %1$s | last updated: %2$s'),
                        Tools::formatBytes(array_sum(array_map('filesize', $files))),
                        date('Y-m-d H:i:s', max(array_map('filemtime', $files)))
                    );
                }
                $ret = $info ?: $this->l('No data');
                break;
        }

        return $ret;
    }

    public function log($action, $line = '', $type = 'general')
    {
        $path = $this->local_path . 'data/' . $type . '-log';
        $ret = '';
        switch ($action) {
            case 'add':
                $ret = is_writable(dirname($path))
                    && file_put_contents($path, date('Y-m-d H:i:s') . ' - ' . $line . PHP_EOL, FILE_APPEND);
                break;
            case 'view':
                $ret = '<pre>' . (file_exists($path) ? Tools::file_get_contents($path) : '') . '</pre>';
                break;
            case 'clear':
                $ret = !file_exists($path) || unlink($path);
                break;
        }

        return $ret;
    }

    public function getCronToken()
    {
        return Tools::encrypt($this->name);
    }

    public function getCronURL($id_shop, $params = [])
    {
        $required_params = [
            'token' => $this->getCronToken(),
            'id_shop' => $id_shop,
        ];
        foreach ($params as $name => $value) {
            $required_params[$name] = $value;
        }

        return $this->context->link->getModuleLink($this->name, 'cron', $required_params, null, null, $id_shop);
    }

    public function throwError($errors, $render_html = true)
    {
        if (!is_array($errors)) {
            $errors = [$errors];
        }
        if ($render_html) {
            $this->context->smarty->assign(['errors' => $errors]);
            $html = $this->display(__FILE__, 'views/templates/admin/errors.tpl');
            if (!Tools::isSubmit('ajax')) {
                return $html;
            } else {
                $errors = $html;
            }
        }
        exit(json_encode(['errors' => $errors]));
    }

    public function getPaginationVariables($page, $products_num, $products_per_page, $current_url)
    {
        require_once 'src/AmazzingFilterProductSearchProvider.php';
        $provider = new AmazzingFilterProductSearchProvider($this);

        return $provider->getPaginationVariables($page, $products_num, $products_per_page, $current_url);
    }

    public function updateQueryString($url, $new_params = [])
    {
        $url = explode('?', $url);
        $updated_params = !empty($url[1]) ? $this->parseStr($url[1]) : [];
        foreach ($new_params as $name => $value) {
            $updated_params[$name] = $value;
            if (($name == $this->param_names['p'] && $value == 1) || $value === null) {
                unset($updated_params[$name]);
            }
        }
        $replacements = ['%2F' => '/', '%2C' => ','];
        $updated_params = str_replace(array_keys($replacements), $replacements, http_build_query($updated_params));
        $updated_url = $url[0] . (!empty($updated_params) ? '?' . $updated_params : '');

        return $updated_url;
    }

    public function getSortingOptions($current_option, $current_url = '')
    {
        $options = $this->getSortingOptionNames($this->settings['general']['sorting_options']);
        $url_without_order = $this->updateQueryString($current_url, ['order' => null]);
        $url_without_order .= strpos($url_without_order, '?') === false ? '?' : '&';
        $processed_options = [];
        foreach ($options as $k => $opt_name) {
            $k = 'product.' . $k;
            $k_exploded = explode('.', $k);
            $processed_options[$k] = [
                'entity' => $k_exploded[0],
                'field' => $k_exploded[1],
                'direction' => $k_exploded[2],
                'label' => $opt_name,
                'urlParameter' => $k,
                'url' => $url_without_order . 'order=' . $k,
                'current' => $k == $current_option,
            ];
        }

        return $processed_options;
        // this is simplified version of ProductListingFrontController::getTemplateVarSortOrders()
        // standard options can be obtained like that:
        // use PrestaShop\PrestaShop\Core\Product\Search\SortOrderFactory; at the top
        // $options = (new SortOrderFactory($this->getTranslator()))->getDefaultSortOrders();
    }

    public function getSortingOptionNames($keys_noname)
    {
        return array_intersect_key(array_merge($keys_noname, $this->getOptions('sorting')), $keys_noname);
    }

    public function getOtherMatching($required_matching, $key, $id_group)
    {
        $other_matching = $required_matching;
        unset($other_matching[$key][$id_group]);

        return array_filter($other_matching);
    }

    public function getPossibleCombinations($data, &$all = [], $comb = [])
    {
        if ($data) {
            $id_group = current(array_keys($data));
            $atts_in_group = $data[$id_group];
            unset($data[$id_group]);
            foreach ($atts_in_group as $id_att) {
                $comb[$id_group] = $id_att;
                $this->getPossibleCombinations($data, $all, $comb);
            }
        } elseif ($comb) {
            $all[] = $comb;
        }

        return $all;
    }

    public function hookProductSearchProvider($params)
    {
        if ($this->defineFilterParams()) {
            require_once 'src/AmazzingFilterProductSearchProvider.php';

            return new AmazzingFilterProductSearchProvider($this);
        } else {
            return false;
        }
    }

    public function bo()
    {
        if (!isset($this->bo_obj)) {
            require_once $this->local_path . 'classes/bo.php';
            $this->bo_obj = new Bo();
        }

        return $this->bo_obj;
    }

    public function slider()
    {
        if (!isset($this->slider_obj)) {
            require_once $this->local_path . 'classes/AfSlider.php';
            $this->slider_obj = new AfSlider();
        }

        return $this->slider_obj;
    }

    public function rangeFilter()
    {
        if (!isset($this->rf_obj)) {
            require_once $this->local_path . 'classes/RangeFilter.php';
            $this->rf_obj = new RangeFilter();
        }

        return $this->rf_obj;
    }

    public function mergedValues()
    {
        if (!isset($this->merged_values)) {
            require_once $this->local_path . 'classes/MergedValues.php';
            $this->merged_values = new MergedValues($this);
        }

        return $this->merged_values;
    }

    public function relatedOverrides()
    {
        if (!isset($this->related_overrides)) {
            require_once $this->local_path . 'classes/RelatedOverrides.php';
            $this->related_overrides = new RelatedOverrides($this);
        }

        return $this->related_overrides;
    }

    public function customerFilters()
    {
        if (!isset($this->cf)) {
            require_once $this->local_path . 'classes/CustomerFilters.php';
            $this->cf = new CustomerFilters($this);
        }

        return $this->cf;
    }
}

<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class CustomerFilters
{
    public function __construct($af)
    {
        $this->af = $af;
        $this->db = Db::getInstance();
        $this->context = Context::getContext();
        $this->settings = !empty($this->af->settings['cf']) ? $this->af->settings['cf']
            : $this->af->getSavedSettings(false, 'cf');
        $this->group_name_var = '{filter_name}';
    }

    public function isReady()
    {
        if (!isset($this->is_ready)) {
            $this->is_ready = $this->settings['keys'] && ($this->context->customer->id || $this->settings['guest']);
        }

        return $this->is_ready;
    }

    public function extendHeader($af_displayed)
    {
        if ($this->isReady()) {
            $this->af->addCSS('cf.css');
            $this->af->addJS('cf.js');
            $js_def = [
                'cf_url_params' => (int) $this->settings['url'],
                'cf_redirect' => '',
            ];
            if (!$af_displayed) {
                $this->af->addCSS('custom.css'); // Other CSS/JS from af->addCustomMedia() require extra dependencies
                $js_def['af_ajax'] = $this->af->ajaxSetup();
                if ($this->settings['redirect']) {
                    $id_cat = $this->context->shop->getCategory();
                    $js_def['cf_redirect'] = $this->context->link->getCategoryLink($id_cat);
                }
            }
            Media::addJsDef($js_def);
            if ($this->settings['position'] < 3) {
                $key = $this->settings['position'] == 1 ? 'cf_top' : 'cf_bottom';
                $this->context->smarty->assign([$key => 1]);
            }
        }
    }

    public function display($hook_params = [])
    {
        if ($this->isReady()) {
            $smarty_params = $this->prepareSmartyParams($hook_params);
            if (empty($smarty_params['form_only'])) {
                $tpl_name = 'cf-block.tpl';
                if ($smarty_params['count']) {
                    $num = isset($this->saved_groups_num) ? $this->saved_groups_num : count($this->get());
                    if ($num || $smarty_params['count'] == 1) {
                        $smarty_params['num'] = $num;
                    }
                }
            } else {
                $tpl_name = 'cf-form.tpl';
                $smarty_params['groups'] = $this->getConfigurableGroups($smarty_params);
            }
            $this->context->smarty->assign(['cf' => $smarty_params]);

            return $this->af->display(_PS_MODULE_DIR_ . $this->af->name . '/' . $this->af->name . '.php', $tpl_name);
        }
    }

    public function prepareSmartyParams($hook_params = [])
    {
        $acceptable_hook_params = ['wrapper_class' => '', 'btn_class' => '', 'form_only' => ''];
        $params = array_intersect_key($hook_params, $acceptable_hook_params);
        $params['l'] = $this->getRequiredLandFields();
        $id_lang = $this->af->id_lang;
        foreach ($this->settings as $name => $value) {
            if (substr($name, 0, 2) === 'l_' && $key = substr($name, 2)) {
                if ($key == 'dynamic') {
                    foreach ((array) $value as $dynamic_key => $multilang) {
                        if (!empty($multilang[$id_lang])) {
                            $params['l'][$dynamic_key] = $multilang[$id_lang];
                        }
                    }
                } elseif (!empty($value[$id_lang])) {
                    $params['l'][$key] = $value[$id_lang];
                }
            } else {
                $params[$name] = $value;
            }
        }

        return $params;
    }

    public function getConfigurableGroups($params)
    {
        $saved_groups = $this->get(false);
        $groups = $prev_saved = [];
        foreach ($this->getKeys() as $i => $key) {
            $label = !empty($params['l'][$key]) ? $params['l'][$key] : $this->getGroupName($key);
            $first_option = str_replace($this->group_name_var, Tools::strtolower($label), $params['l']['opt']);
            $groups[$key] = $this->prepareGroup($key) + [
                'label' => $label,
                'first_option' => $first_option,
                'blocked' => !empty($params['related']) && count($prev_saved) < $i,
            ];
            $groups[$key]['values'] = !$groups[$key]['blocked'] || isset($saved_groups[$key]) ?
                $this->getGroupValues($groups[$key], $key, $prev_saved) : [];
            if (!$groups[$key]['values']) {
                $groups[$key]['blocked'] = true;
            }
            if ($groups[$key]['saved_value'] = isset($saved_groups[$key]) ? $saved_groups[$key] : '') {
                $prev_saved[$key] = $groups[$key]['saved_value'];
            }
        }

        return $groups;
    }

    public function prepareGroup($key)
    {
        $group = [
            'type' => 0,
            'is_slider' => 0,
            'first_char' => Tools::substr($key, 0, 1),
            'id_group' => (int) Tools::substr($key, 1),
        ];
        if ($group['first_char'] == 'c') {
            $group['id_parent'] = $group['id_group'] ?: $this->context->shop->getCategory();
            $group['nesting_lvl'] = 1;
        }

        return $group;
    }

    public function getGroupValues($group, $key, $applied_filters = [])
    {
        $values = $this->af->getFilterValues($group, $key);
        if ($applied_filters) {
            $matching_ids = $this->getMatchingOptionIDs($key, $applied_filters);
            $values = array_intersect_key($values, array_flip($matching_ids));
        }

        return $values;
    }

    public function getMatchingOptionIDs($key, $filters)
    {
        $col_name = Tools::substr($key, 0, 1);
        $q = new DbQuery();
        $q->select('`id_product`, `' . bqSQL($col_name) . '`')->from('af_index');
        $q->where('`' . bqSQL($col_name) . '` <> \'\'');
        foreach (array_filter($filters) as $f_key => $f_value) {
            $f_col_name = Tools::substr($f_key, 0, 1);
            $f_value = preg_match('/^map\d+$/', $f_value) ? $f_value : (int) $f_value; // allow map** or integers
            $q->where('FIND_IN_SET(\'' . pSQL($f_value) . '\', `' . bqSQL($f_col_name) . '`) > 0');
        }
        $options = implode(',', array_column($this->db->executeS($q), $col_name));

        return explode(',', $options);
    }

    public function getGroupName($key)
    {
        if (!isset($this->avl_f)) {
            $this->avl_f = $this->af->getAvailableFilters();
        }

        return isset($this->avl_f[$key]['name']) ? $this->avl_f[$key]['name'] : '';
    }

    public function prepareHiddenGroup($key, $data = [])
    {
        $f = $this->prepareGroup($key) + ['forced_values' => []];
        if ($key == 'c') {
            $f['nesting_lvl'] = 0;
        }
        $f['values'] = $this->getGroupValues($f, $key);
        $f['name'] = isset($data['name']) ? $data['name'] : $this->getGroupName($key);
        $f['link'] = isset($data['link']) ? $data['link'] : $this->af->generateLink($f['name'], $key);
        $f['submit_name'] = 'filters[' . $f['first_char'] . '][' . $f['id_group'] . '][]';

        return $f;
    }

    public function extendInitialParams(&$filters, &$params)
    {
        if ($this->isReady() && $cf = $this->get()) {
            $this->saved_groups_num = count($cf);
            $hidden_f = $applied_cf = [];
            foreach ($cf as $key => $id) {
                $f = isset($filters[$key]) ? $filters[$key] : [];
                if (!isset($f['values'][$id])) {
                    $f = $hidden_f[$key] = $this->prepareHiddenGroup($key, $f);
                }
                if (isset($f['values'][$id])) {
                    if (isset($hidden_f[$key])) {
                        $hidden_f[$key]['forced_values'][$id] = $f['values'][$id] + ['class' => 'cf-hidden'];
                        // available_options may be used in indexationData('prepareQuery')
                        $params['available_options'][$f['first_char']][$f['id_group']][$id] = $id;
                        if ($f['first_char'] == 'a') {
                            // available options for 'a' may be used in prepareCountData, and then extra_count_a
                            $available_options = array_column($f['values'], 'id', 'id');
                            $params['available_options'][$f['first_char']][$f['id_group']] += $available_options;
                        }
                    } else {
                        $filters[$key]['has_selection'] = 1;
                        $filters[$key]['values'][$id]['selected'] = 1;
                        $applied_cf[$key][$id] = $id;
                    }
                    $params['filters'][$f['first_char']][$f['id_group']][$id] = $id;
                }
            }
            $this->context->smarty->assign([
                'applied_customer_filters' => $applied_cf,
                'hidden_filters' => $hidden_f,
            ]);
        }
    }

    public function ajaxAction()
    {
        $action = Tools::getValue('cf_action');
        $ret = [];
        switch ($action) {
            case 'getGroupValues':
                $applied_filters = $this->af->parseStr(Tools::getValue('applied_filters'), true);
                $key = $this->af->getSafeValue('key');
                $group = $this->prepareGroup($key);
                $ret = array_column($this->getGroupValues($group, $key, $applied_filters), 'name', 'id');
                break;
            case 'save':
                $filters = array_filter($this->af->parseStr(Tools::getValue('filters'), true));
                $ret['saved'] = $this->save($filters);
                break;
            case 'getForm':
                $ret['html'] = $this->display(['form_only' => true]);
                break;
        }
        exit(json_encode($ret));
    }

    public function save($filters)
    {
        if ($id_customer = $this->context->customer->id) {
            $saved = $this->db->execute('
                REPLACE INTO ' . _DB_PREFIX_ . 'af_customer_filters
                VALUES (' . (int) $id_customer . ', \'' . pSQL(json_encode($this->sanitize($filters))) . '\')
            ');
        } else {
            $saved = $this->guestValue('save', $filters);
        }

        return $saved;
    }

    public function get($sanitize = true)
    {
        if ($id_customer = $this->context->customer->id) {
            $customer_filters = $this->toArray($this->db->getValue('
                SELECT filters FROM ' . _DB_PREFIX_ . 'af_customer_filters
                WHERE id_customer = ' . (int) $id_customer . '
            '));
            if ($guest_value = $this->guestValue('get')) {
                if (!$customer_filters) {
                    $customer_filters = $guest_value;
                    $this->save($customer_filters);
                }
                $this->guestValue('save', []);
            }
        } else {
            $customer_filters = $this->guestValue('get');
        }

        return $sanitize ? $this->sanitize($customer_filters) : $customer_filters;
    }

    public function guestValue($action, $params = [])
    {
        $ret = [];
        if ($this->settings['guest']) {
            switch ($action) {
                case 'get':
                    $ret = $this->toArray($this->context->cookie->__get('af_cf'));
                    break;
                case 'save':
                    if ($to_save = $this->sanitize($params)) {
                        $this->context->cookie->__set('af_cf', json_encode($to_save));
                        $ret = (bool) $this->guestValue('get');
                    } else {
                        $this->context->cookie->__unset('af_cf');
                        $ret = !$this->guestValue('get');
                    }
                    break;
            }
        }

        return $ret;
    }

    public function toArray($value)
    {
        return json_decode($value ?: '', true) ?: [];
    }

    public function sanitize($filters)
    {
        return array_map('pSQL', array_intersect_key($filters, array_flip($this->getKeys()))); // pSQL allows map_xx
    }

    public function getKeys()
    {
        if (!isset($this->keys)) {
            $this->keys = array_filter(explode(',', $this->settings['keys']));
        }

        return $this->keys;
    }

    public function adminAction($action, $params = [])
    {
        $ret = [];
        switch ($action) {
            case 'adjustAvailableSorted':
                $to_exclude = array_merge(['p', 'w', 't'], array_keys($this->af->getSpecialFilters()));
                foreach ($params as $prefix => $filters) {
                    if (isset($filters['c'])) {
                        $filters['c']['name'] = $this->l('Main category');
                    }
                    $params[$prefix] = array_diff_key($filters, array_fill_keys($to_exclude, 0));
                }
                $ret = array_filter($params);
                break;
            case 'getTagifyItems':
                $available_filters = $this->af->getAvailableFilters();
                foreach ($params as $key) {
                    if (isset($available_filters[$key])) {
                        $ret[$key] = $available_filters[$key];
                        $ret[$key]['dynamic_name'] = isset($this->settings['l_dynamic'][$key]) ?
                            $this->settings['l_dynamic'][$key] : [];
                    }
                }
                break;
        }

        return $ret;
    }

    public function extraSettingsActions($settings, $shop_ids)
    {
        $at_least_one_key = empty($settings['keys']) ? 0 : 1;
        $hook_method = $at_least_one_key ? 'registerHook' : 'unregisterHook';
        $this->af->$hook_method('displayCustomerFilters', $shop_ids);
        foreach ($shop_ids as $id_shop) {
            Configuration::updateValue('AF_CF', $at_least_one_key, false, null, $id_shop);
        }
    }

    public function getSettingsFields()
    {
        $fields = [
            'keys' => [
                'display_name' => $this->l('Filters that can be saved by users'),
                'type' => 'tagify',
                'value' => '',
                'class' => 'cf-criteria',
                'validate' => 'isTagsList',
            ],
            'related' => [
                'display_name' => $this->l('Step by step selection'),
                'type' => 'switcher',
                'value' => 1,
                'class' => 'trigger-info show-on-1',
                'info' => $this->l('Users can select next filter only after selecting previous'),
            ],
            'guest' => [
                'display_name' => $this->l('Allow guests to save filters'),
                'type' => 'switcher',
                'value' => 0,
            ],
            'url' => [
                'display_name' => $this->l('Include saved filters in URL params'),
                'type' => 'switcher',
                'value' => 1,
                'class' => 'trigger-info show-on-1',
                'info' => $this->l('If \'RED\' filter is saved, then \'color=red\' will be added to URL'),
            ],
            'count' => [
                'display_name' => $this->l('Display number of saved filters'),
                'type' => 'switcher',
                'value' => 2,
                'switcher_options' => [
                    0 => $this->l('No'),
                    1 => $this->l('Yes'),
                    2 => $this->l('Only if at least one filter is saved'),
                ],
            ],
            'position' => [
                'display_name' => $this->l('\'My filters\' button position'),
                'type' => 'select',
                'value' => 1,
                'options' => [
                    1 => $this->l('At the top of filter block'),
                    2 => $this->l('At the bottom of filter block'),
                    3 => $this->l('In a custom position'),
                ],
                'class' => 'trigger-info show-on-3',
                'info' => sprintf($this->l('Add this code in any tpl: %s'), '{hook h=\'displayCustomerFilters\'}'),
                'related_options' => '.cf-redirect',
            ],
            'redirect' => [
                'display_name' => $this->l('After saving \'My filters\''),
                'type' => $this->af->is_modern ? 'select' : 'hidden',
                'value' => 0,
                'options' => [
                    0 => $this->l('User stays on the same page'),
                    1 => $this->l('User is redirected to main category page'),
                ],
                'class' => 'trigger-info show-on-1 cf-redirect visible-on-3',
                'info' => $this->l('If current page has filter block, it will be refreshed'),
            ],
            'l_btn' => [
                'display_name' => $this->l('\'My filters\' button'),
                'type' => 'text',
                'value' => '',
                'multilang' => 1,
                'subtitle' => $this->l('Configure wordings'),
            ],
            'l_dynamic' => [
                'display_name' => $this->l('Custom label for \'%s\''),
                'type' => 'cf_dynamic',
                'value' => '', // might be used as default_value on validation
                'multilang' => 1,
                'skip_empty' => 1,
                'class' => 'hidden cf-dummy',
            ],
            'l_opt' => [
                'display_name' => $this->l('First option in selectors'),
                'type' => 'text',
                'value' => '',
                'multilang' => 1,
                'info' => sprintf($this->l('You can use variable %s'), $this->group_name_var),
                'validate' => 'isMailSubject', // almost like isGenericName, but accepts ={}
            ],
            'l_btn_f' => [
                'display_name' => $this->l('\'Save\' button'),
                'type' => 'text',
                'value' => '',
                'multilang' => 1,
            ],
            'l_btn_r' => [
                'display_name' => $this->l('\'Reset\' button'),
                'type' => 'text',
                'value' => '',
                'multilang' => 1,
            ],
        ];
        foreach ($this->getRequiredLandFields() as $key => $txt) {
            $key = 'l_' . $key;
            if (isset($fields[$key])) {
                $fields[$key]['required'] = 1;
                $fields[$key]['value'] = $txt;
            }
        }

        return $fields;
    }

    public function validateDynamicField(&$multivalue, $field, $update_value, $error_label)
    {
        $error = false;
        $field['display_name'] = $this->l('Custom label');
        $field['type'] = 'text';
        $multivalue = is_array($multivalue) ? $multivalue : [];
        foreach ($multivalue as &$value) {
            if ($error = $this->af->validateField($value, $field, $update_value, $error_label)) {
                break;
            }
        }

        return $error;
    }

    public function getRequiredLandFields()
    {
        return [
            'btn' => 'My filters',
            'opt' => 'Select ' . $this->group_name_var,
            'btn_f' => 'SAVE',
            'btn_r' => 'Reset',
        ];
    }

    public function l($string)
    {
        return $this->af->l($string, 'CustomerFilters');
    }
}

<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class MergedValues
{
    public function __construct($af)
    {
        $this->af = $af;
        $this->context = Context::getContext();
    }

    public function extendSQL($action, &$sql)
    {
        switch ($action) {
            case 'install':
                foreach (['attribute', 'feature'] as $type) {
                    $sql[] = 'CREATE TABLE IF NOT EXISTS ' . $this->qTable($type) . ' (
                        id_merged int(10) unsigned NOT NULL AUTO_INCREMENT,
                        id_group int(10) unsigned NOT NULL,
                        position int(10) unsigned NOT NULL,
                        PRIMARY KEY (id_merged)
                        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';
                    $sql[] = 'CREATE TABLE IF NOT EXISTS ' . $this->qTable($type . '_lang') . ' (
                        id_merged int(10) unsigned NOT NULL,
                        id_lang int(10) unsigned NOT NULL,
                        name text NOT NULL,
                        PRIMARY KEY (id_merged, id_lang), KEY id_lang (id_lang)
                        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';
                    $sql[] = 'CREATE TABLE IF NOT EXISTS ' . $this->qTable($type . '_map') . ' (
                        id_original int(10) unsigned NOT NULL,
                        id_merged int(10) unsigned NOT NULL,
                        PRIMARY KEY (id_original, id_merged),
                        KEY id_original (id_original), KEY id_merged (id_merged)
                        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';
                }
                break;
            case 'uninstall':
                foreach (['attribute', 'feature'] as $type) {
                    foreach (['', '_lang', '_map'] as $ext) {
                        $sql[] = 'DROP TABLE IF EXISTS ' . $this->qTable($type . $ext);
                    }
                }
                break;
        }
    }

    public function getGeneralSettingsFields()
    {
        return [
            'merged_attributes' => [
                'display_name' => $this->l('Activate merged attributes'),
                'tooltip' => $this->l('For example shoe sizes US-10, UK-9.5 and EUR-43 can be merged in one value'),
                'class' => 'mergedattributes',
                'value' => 0,
                'type' => 'switcher',
                'subtitle' => $this->l('Merged parameters'),
            ],
            'merged_features' => [
                'display_name' => $this->l('Activate merged features'),
                'class' => 'mergedfeatures',
                'value' => 0,
                'type' => 'switcher',
            ],
        ];
    }

    public function getTagifyField()
    {
        return [
            'type' => 'tagify',
            'qs_placeholder' => $this->l('start typing...'),
            'input_class' => 'merged-qs',
            'value' => [],
            't_items_data' => [],
        ];
    }

    public function assignConfigVariables()
    {
        $smarty_array = [
            'merged_data' => [
                'attribute' => [
                    'title' => $this->l('Merged attributes'),
                    'groups' => $this->af->getGroupOptions('attribute', $this->af->id_lang),
                    'selected_group' => $this->getGroupWithMaxMergedItems('attribute'),
                ],
                'feature' => [
                    'title' => $this->l('Merged features'),
                    'groups' => $this->af->getGroupOptions('feature', $this->af->id_lang),
                    'selected_group' => $this->getGroupWithMaxMergedItems('feature'),
                ],
            ],
        ];
        $this->context->smarty->assign($smarty_array);
    }

    public function getGroupWithMaxMergedItems($type)
    {
        return (int) $this->af->db->getValue('
            SELECT id_group, COUNT(*) as count FROM ' . $this->qTable($type) . '
            GROUP BY id_group ORDER BY count DESC
        ');
    }

    public function renderItems($type, $id_group, $id_lang, $specific_items = false)
    {
        $this->context->smarty->assign([
            'items' => $specific_items ? $specific_items : $this->getItems($type, $id_group),
            'merging_params' => ['id_group' => $id_group, 'type' => $type],
        ]);
        $this->af->assignLanguageVariables();

        return $this->af->display($this->af->name, 'views/templates/admin/merged-items.tpl');
    }

    public function getItems($type, $id_group)
    {
        $items = [];
        $orig_l = $type == 'attribute' ? ['name', 'attribute_lang', 'id_attribute']
            : ['value', 'feature_value_lang', 'id_feature_value'];
        $data = $this->af->db->executeS('
            SELECT m.*, l.*, main.*, orig_l.`' . bqSQL($orig_l[0]) . '` AS name_original
            FROM ' . $this->qTable($type) . ' main
            LEFT JOIN ' . $this->qTable($type . '_lang') . ' l ON l.id_merged = main.id_merged
            LEFT JOIN ' . $this->qTable($type . '_map') . ' m ON m.id_merged = main.id_merged
            LEFT JOIN `' . _DB_PREFIX_ . bqSQL($orig_l[1]) . '` orig_l
                ON orig_l.`' . bqSQL($orig_l[2]) . '` = m.id_original AND orig_l.id_lang = l.id_lang
            WHERE main.id_group = ' . (int) $id_group . '
            ORDER BY main.position ASC, main.id_merged ASC
        ');
        foreach ($data as $row) {
            $id = $row['id_merged'];
            if (!isset($items[$id])) {
                $items[$id] = [
                    'name' => [],
                    'position' => $row['position'] + 1, // same format as native attribute positions
                    't_field' => $this->getTagifyField(),
                ];
            }
            $items[$id]['name'][$row['id_lang']] = $row['name'];
            $items[$id]['t_field']['value'][$row['id_original']] = $row['id_original'];
            if ($row['id_lang'] == $this->af->id_lang) {
                $items[$id]['t_field']['t_items_data'][$row['id_original']] = $row['name_original'];
            }
        }

        return $items;
    }

    public function saveRow($data)
    {
        $sql = $upd_rows = [];
        $id_merged = $data['id_merged'];
        $type = $data['type'];
        $position = $data['position'] - 1; // same format as native attribute positions
        $this->af->db->execute('
            REPLACE INTO ' . $this->qTable($type) . '
            VALUES (' . (int) $id_merged . ', ' . (int) $data['id_group'] . ', ' . (int) $position . ')
        ');
        if (!$id_merged) {
            $id_merged = $this->af->db->Insert_ID();
        }
        foreach ($data['name'] as $id_lang => $name) {
            if (!$name && isset($data['name'][$this->af->id_lang])) {
                $name = $data['name'][$this->af->id_lang];
            }
            $upd_rows['_lang'][] = '(' . (int) $id_merged . ', ' . (int) $id_lang . ', \'' . pSQL($name) . '\')';
        }
        foreach ($data['merged_values'] as $id_original) {
            $upd_rows['_map'][] = '(' . (int) $id_original . ', ' . (int) $id_merged . ')';
        }
        foreach (['_lang', '_map'] as $ext) {
            $sql[] = 'DELETE FROM ' . $this->qTable($type . $ext) . ' WHERE id_merged = ' . (int) $id_merged;
            if (!empty($upd_rows[$ext])) {
                $sql[] = 'INSERT INTO ' . $this->qTable($type . $ext) . ' VALUES ' . implode(', ', $upd_rows[$ext]);
            }
        }
        $this->af->cache('clear', $type[0] . '_list');

        return $this->af->runSQL($sql) ? $id_merged : false;
    }

    public function deleteRow($type, $id_merged)
    {
        $sql = [];
        foreach (['', '_lang', '_map'] as $ext) {
            $sql[] = 'DELETE FROM ' . $this->qTable($type . $ext) . ' WHERE id_merged = ' . (int) $id_merged;
        }

        return $this->af->runSQL($sql) && $this->af->cache('clear', $type[0] . '_list');
    }

    public function mapRows($original_rows, $id_lang, $id_group, $type)
    {
        $updated_rows = $map = [];
        $merged_data = $this->af->db->executeS('
            SELECT * FROM ' . $this->qTable($type) . ' main
            LEFT JOIN ' . $this->qTable($type . '_map') . ' m
                ON m.id_merged = main.id_merged
            LEFT JOIN ' . $this->qTable($type . '_lang') . ' l
                ON l.id_merged = m.id_merged AND l.id_lang = ' . (int) $id_lang . '
            ' . ($id_group ? 'WHERE main.id_group = ' . (int) $id_group : '') . '
        ');
        if ($merged_data) {
            foreach ($merged_data as $merged_row) {
                $map[$merged_row['id_original']]['map' . $merged_row['id_merged']] = $merged_row;
            }
            if ($type == 'attribute' && !empty($original_rows[0]['is_color_group'])) {
                // use colors/textures of merged atts with highest positions
                $original_rows = $this->af->sortByKey($original_rows, 'position');
            }
            foreach ($original_rows as $orig_row) {
                if (isset($map[$orig_row['id']])) {
                    foreach ($map[$orig_row['id']] as $id_merged => $merged_row) {
                        if (!isset($updated_rows[$id_merged])) {
                            $updated_rows[$id_merged] = ['id' => $id_merged] + $merged_row + $orig_row;
                        }
                    }
                } else {
                    $updated_rows[$orig_row['id']] = $orig_row;
                }
            }
            $updated_rows = $this->af->sortByKey($updated_rows, 'name');
        } else {
            $updated_rows = $original_rows;
        }

        return $updated_rows;
    }

    public function mapAttributesInSortedCombinations(&$sorted_combinations)
    {
        $map = $this->getMap('attribute');
        foreach ($sorted_combinations as $id_product => $combinations) {
            foreach ($combinations as $id_comb => $c_data) {
                $c_data_upd = $c_data;
                foreach ($c_data['a'] as $id_att) {
                    if (isset($map[$id_att])) {
                        $suffix = '';
                        foreach ($map[$id_att] as $id_merged => $id_merged_group) {
                            $c_data_upd['a'][$id_merged_group] = $id_merged;
                            $sorted_combinations[$id_product][$id_comb . $suffix] = $c_data_upd;
                            $suffix .= '_';
                        }
                    }
                }
            }
        }
    }

    public function replaceMergedAttsWithOriginalValues(&$selected_atts)
    {
        $map = $this->getMap('attribute', true);
        foreach ($selected_atts as $id_group => $atts) {
            foreach (array_keys($atts) as $id_att) {
                if (isset($map[$id_att])) {
                    foreach (array_keys($map[$id_att]) as $id_original) {
                        $selected_atts[$id_group][$id_original] = $id_original;
                    }
                    unset($selected_atts[$id_group][$id_att]);
                }
            }
        }
    }

    public function getMap($type = 'attribute', $reverse = false)
    {
        $map = [];
        $data = $this->af->db->executeS('
            SELECT * FROM ' . $this->qTable($type) . ' main
            LEFT JOIN ' . $this->qTable($type . '_map') . ' map
                ON map.id_merged = main.id_merged
        ');
        $keys = !$reverse ? ['id_original', 'id_merged'] : ['id_merged', 'id_original'];
        foreach ($data as $row) {
            $row['id_merged'] = 'map' . $row['id_merged'];
            $map[$row[$keys[0]]][$row[$keys[1]]] = $row['id_group'];
        }

        return $map;
    }

    public function ajaxAction($action)
    {
        $ret = [];
        switch ($action) {
            case 'getItems':
                $type = Tools::getValue('type');
                $id_group = Tools::getValue('id_group');
                $ret['html'] = $this->renderItems($type, $id_group, $this->af->id_lang);
                break;
            case 'addRow':
                $type = Tools::getValue('type');
                $id_group = Tools::getValue('id_group');
                $position = Tools::getValue('position');
                $items = [0 => ['name' => [], 'position' => $position, 't_field' => $this->getTagifyField()]];
                $ret['html'] = $this->renderItems($type, $id_group, $this->af->id_lang, $items);
                break;
            case 'saveRow':
                $data = $this->af->parseStr(Tools::getValue('data'));
                $data['merged_values'] = $this->af->formatIDs($data['merged_values']);
                $ret['saved_id'] = $this->saveRow($data);
                break;
            case 'deleteRow':
                $type = Tools::getValue('type');
                $id_merged = Tools::getValue('id_merged');
                $ret['deleted'] = $this->deleteRow($type, $id_merged);
                break;
            case 'quickSearch':
                $this->context->smarty->assign([
                    'results' => $this->quickSearch($this->af->getSafeValue('q'), $this->af->getSafeValue('type')),
                    'blocked' => $this->af->getSafeValue('blocked', []),
                ]);
                $ret['html'] = $this->af->display($this->af->name, 'views/templates/admin/merged-qs.tpl');
                break;
        }
        exit(json_encode($ret));
    }

    public function quickSearch($q, $type)
    {
        $result = [];
        $get_values_method = 'get' . Tools::ucfirst($type) . 's';
        if (method_exists($this->af, $get_values_method)) {
            foreach ($this->af->$get_values_method($this->af->id_lang, false, false) as $v) {
                if (mb_stripos($v['name'], $q) !== false) {
                    $result[$v['group_name']][$v['id']] = $v['name']
                        . (!empty($v['custom']) ? ' (' . $this->l('custom') . ')' : '');
                }
            }
        }

        return $result;
    }

    protected function qTable($name)
    {
        return '`' . _DB_PREFIX_ . 'af_merged_' . bqSQL($name) . '`';
    }

    public function l($string)
    {
        return $this->af->l($string, 'MergedValues');
    }
}

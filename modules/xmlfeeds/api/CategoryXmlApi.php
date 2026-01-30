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

class CategoryXmlApi
{
    public function getFeed($id = 0, $pref_s = '', $pref_e = '', $html_tags_status = false, $extra_feed_row = false, $one_branch = false, $only_enabled = false, $multistoreString = false)
    {
        $block_name = array();
        $xml_name = array();
        $xml_name_l = array();
        $all_l_iso = array();
        $id_lang = Configuration::get('PS_LANG_DEFAULT');

        $block_n = Db::getInstance()->ExecuteS('SELECT `name`, `value`
            FROM '._DB_PREFIX_.'blmod_xml_block
            WHERE category = "'.(int)$id.'"
        ');

        foreach ($block_n as $bn) {
            $block_name[$bn['name']] = $bn['value'];
        }

        $r = Db::getInstance()->ExecuteS('SELECT `name`, `status`, `title_xml`, `table`
            FROM '._DB_PREFIX_.'blmod_xml_fields
            WHERE category = "'.(int)$id.'" AND `table` != "lang" AND `table` != "category_lang" AND status = 1
            AND `table` != "bl_extra"
            ORDER BY `table` ASC
        ');

        $extra_field = Db::getInstance()->ExecuteS('SELECT `name`, `title_xml`, CONCAT(`table`, "_", `name`) AS field_key
            FROM '._DB_PREFIX_.'blmod_xml_fields
            WHERE category = "'.(int)$id.'" AND `table` = "bl_extra" AND status = 1');

        $field = '';

        if (!empty($r)) {
            foreach ($r as $f) {
                $field .= ' `'._DB_PREFIX_.$f['table'].'`.`'.$f['name'].'` AS '.$f['table'].'_'.$f['name'].' ,';
                $xml_name[$f['table'].'_'.$f['name']] = $f['title_xml'];
            }

            if (empty($field)) {
                exit;
            }

            $field = ','.trim($field, ',');
        }

        $where_only_actyve = '';

        if (!empty($only_enabled)) {
            $where_only_actyve = 'WHERE '._DB_PREFIX_.'category.active = "1"';
        }

        if (!empty($multistoreString)) {
            if (empty($where_only_actyve)) {
                $where_only_actyve = 'WHERE '._DB_PREFIX_.'category.id_shop_default IN ('.(int)$multistoreString.')';
            } else {
                $where_only_actyve .= ' AND '._DB_PREFIX_.'category.id_shop_default IN ('.(int)$multistoreString.')';
            }
        }

        $sql = 'SELECT DISTINCT('._DB_PREFIX_.'category.id_category) AS cat_id '.pSQL($field).'
            FROM '._DB_PREFIX_.'category
            LEFT JOIN '._DB_PREFIX_.'category_group ON
            '._DB_PREFIX_.'category_group.id_category = '._DB_PREFIX_.'category.id_category '.
                $where_only_actyve;

            $xml_d = Db::getInstance()->ExecuteS($sql);

            //Language
            $l = Db::getInstance()->ExecuteS('
            SELECT `name`
            FROM '._DB_PREFIX_.'blmod_xml_fields
            WHERE category = "'.(int)$id.'" AND `table` = "lang"
        ');

        $xml_lf = array();
        $link_class = new Link();

        if (!empty($l)) {
            $l_where = '';
            $count_lang = count($l);

            foreach ($l as $ll) {
                $l_where .= 'OR '._DB_PREFIX_.'category_lang.id_lang='.(int)$ll['name'].' ';
            }

            $l_where = trim($l_where, 'OR');

            $rl = Db::getInstance()->ExecuteS('SELECT `name`, `status`, `title_xml`
                FROM '._DB_PREFIX_.'blmod_xml_fields
                WHERE category = "'.(int)$id.'" AND `table` = "category_lang" AND `status` = 1');

            $field = '';

            if (!empty($rl)) {
                foreach ($rl as $fl) {
                    $field .= ' `'._DB_PREFIX_.'category_lang`.`'.$fl['name'].'`,';
                    $xml_name_l[$fl['name']] = $fl['title_xml'];
                }

                $field = ','.trim($field, ',');
            }

            $xml_l = Db::getInstance()->ExecuteS('SELECT '._DB_PREFIX_.'category_lang.id_category, '._DB_PREFIX_.'lang.iso_code as blmodxml_l '.pSQL($field).'
                FROM '._DB_PREFIX_.'category_lang
                LEFT JOIN '._DB_PREFIX_.'lang ON
                '._DB_PREFIX_.'lang.id_lang = '._DB_PREFIX_.'category_lang.id_lang
                WHERE '.$l_where.'
                ORDER BY '._DB_PREFIX_.'category_lang.id_category ASC');

            foreach ($xml_l as $xll) {
                $id_cat = $xll['id_category'];
                $l_iso = $xll['blmodxml_l'];
                $all_l_iso[] = $l_iso;

                $lang_prefix = '-'.$l_iso;

                if ($count_lang < 2) {
                    $id_lang = $l[0]['name'];
                    $lang_prefix = '';
                }

                if (empty($one_branch)) {
                    $xml_lf[$id_cat.$l_iso] = '<'.$block_name['desc-block-name'].$lang_prefix.'>';
                } else {
                    $xml_lf[$id_cat.$l_iso] = '';
                }

                foreach ($xll as $idl => $vall) {
                    if ($idl == 'id_category' || $idl == 'blmodxml_l') {
                        continue;
                    }

                    $vall = isset($vall) ? $vall : false;

                    if ($html_tags_status) {
                        $vall = strip_tags($vall);
                    }

                    $xml_lf[$id_cat.$l_iso] .= '<'.$xml_name_l[$idl].$lang_prefix.'>'.$pref_s.htmlspecialchars($vall).$pref_e.'</'.$xml_name_l[$idl].$lang_prefix.'>';
                }

                if (empty($one_branch)) {
                    $xml_lf[$id_cat.$l_iso] .= '</'.$block_name['desc-block-name'].$lang_prefix.'>';
                }
            }

            $all_l_iso = array_unique($all_l_iso);
        }

        $xml = '<'.$block_name['file-name'].'>';
        $xml .= $extra_feed_row;

        foreach ($xml_d as $xdd) {
            $xml .= '<'.$block_name['cat-block-name'].'>';

            foreach ($xdd as $id => $val) {
                if ($id == 'cat_id') {
                    continue;
                }

                $val = isset($val) ? $val : false;
                $xml .= '<'.$xml_name[$id].'>'.$pref_s.$val.$pref_e.'</'.$xml_name[$id].'>';
            }

            $id_cat = $xdd['cat_id'];

            if (!empty($all_l_iso)) {
                foreach ($all_l_iso as $iso) {
                    $xml_lf[$id_cat.$iso] = isset($xml_lf[$id_cat.$iso]) ? $xml_lf[$id_cat.$iso] : false;
                    $xml .= $xml_lf[$id_cat.$iso];
                }
            }

            if (!empty($extra_field)) {
                foreach ($extra_field as $b_e) {
                    if ($b_e['name'] == 'category_url_blmod') {
                        $xml .= '<'.$b_e['title_xml'].'>'.$pref_s.$link_class->getCategoryLink($id_cat, null, $id_lang).$pref_e.'</'.$b_e['title_xml'].'>';
                    }
                }
            }

            $xml .= '</'.$block_name['cat-block-name'].'>';
        }

        $xml .= '</'.$block_name['file-name'].'>';

        return $xml;
    }
}

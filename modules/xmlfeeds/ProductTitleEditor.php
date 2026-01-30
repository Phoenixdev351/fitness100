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

if (!defined('_PS_VERSION_')) {
    exit;
}

class ProductTitleEditor
{
    const ADD_ALL_ATTRIBUTES = 1;

    public function save($feedId)
    {
        $titleKey = Tools::getValue('title_replace_key');
        $titleValue = Tools::getValue('title_replace_value');
        $addElements = Tools::getValue('title_editor_add_elements');
        $addAttributes = Tools::getValue('title_editor_add_attributes');
        $options = Tools::getValue('title_editor_options');
        $serializeList = '';
        $newElementsSerialize = '';

        if (empty($titleKey)) {
            return false;
        }

        foreach ($titleKey as $k => $t) {
            if (empty($t)) {
                unset($titleKey[$k]);
                unset($titleValue[$k]);
            }
        }

        if (!empty($titleKey)) {
            $serializeList = serialize(array(
                'key' => $titleKey,
                'value' => $titleValue,
            ));
        }

        if (!empty($addElements) || !empty($addAttributes) || !empty($options)) {
            $newElementsSerialize = serialize(array(
                'elements' => $addElements,
                'attributes' => $addAttributes,
                'options' => $options,
            ));
        }

        return Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.'blmod_xml_feeds SET	
			title_replace = "'.pSQL($serializeList).'", title_new_elements = "'.pSQL($newElementsSerialize).'"
			WHERE id = "'.(int)$feedId.'"');
    }

    public function getByFeedId($feedId, $withEmptyRow = false)
    {
        $list = Db::getInstance()->getValue('SELECT s.title_replace
			FROM '._DB_PREFIX_.'blmod_xml_feeds s
			WHERE s.id = "'.(int)$feedId.'"');

        if (!empty($list)) {
            return unserialize($list);
        }

        if ($withEmptyRow) {
            return array(
                'key' => array(0 => ''),
                'value' => array(0 => ''),
            );
        }

        return array();
    }

    public function replaceTitleByKey($title, $settings)
    {
        if (empty($settings)) {
            return $title;
        }

        if (empty($settings['key'])) {
            return $title;
        }

        foreach ($settings['key'] as $k => $f) {
            $title = str_ireplace($f, $settings['value'][$k], $title);
        }

        return trim(trim($title), ',');
    }

    public function getNewElementsByFeedId($feedId, $withEmptyRow = false)
    {
        $list = Db::getInstance()->getValue('SELECT s.title_new_elements
			FROM '._DB_PREFIX_.'blmod_xml_feeds s
			WHERE s.id = "'.(int)$feedId.'"');

        if (!empty($list)) {
            $array = unserialize($list);
            $array['elements'] = !empty($array['elements']) ? $array['elements'] : array();
            $array['attributes'] = !empty($array['attributes']) ? $array['attributes'] : array();
            $array['options'] = !empty($array['options']) ? $array['options'] : array();

            return $array;
        }

        if ($withEmptyRow) {
            return array(
                'elements' => array(),
                'attributes' => array(),
                'options' => array(),
            );
        }

        return array();
    }

    public function getAvailableNewTitleElementsList()
    {
        return array(
            3 => 'Product ID',
            4 => 'Reference',
            5 => 'EAN-13',
            6 => 'ISBN',
            7 => 'Category',
            8 => 'Manufacturer',
        );
    }

    public function addElementsToTitle($title, $settings, $elementsByKey)
    {
        if (empty($settings['elements'])) {
            return $title;
        }

        $newElements = array();

        foreach ($settings['elements'] as $k) {
            if (empty($elementsByKey[$k])) {
                continue;
            }

            $newElements[] = $elementsByKey[$k];
        }

        return $title.' '.implode(' ', $newElements);
    }

    public function addAttributesToTile($title, $settings, $productAttributes)
    {
        $settings['elements'] = !empty($settings['elements']) ? $settings['elements'] : array();
        $settings['attributes'] = !empty($settings['attributes']) ? $settings['attributes'] : array();
        $settings['options'] = !empty($settings['options']) ? $settings['options'] : array();
        $isAllAttributes = in_array(self::ADD_ALL_ATTRIBUTES, $settings['elements']);
        $isAttributeWithName = in_array('attribute_name', $settings['options']);

        if (empty($settings['attributes']) && !$isAllAttributes) {
            return $title;
        }

        $attributes = array();
        $title = trim($title);

        foreach ($productAttributes as $a) {
            if ($a['quantity'] < 1) {
                continue;
            }

            if (in_array($a['id_attribute_group'], $settings['attributes']) || $isAllAttributes) {
                $attributes[$a['public_group_name']][] = $a['attribute_name'];
            }
        }

        foreach ($attributes as $k => $n) {
            $title .= ', '.($isAttributeWithName ? $k.' ' : '').implode(' ', array_unique($n));
        }

        return $title;
    }
}

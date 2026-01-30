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

class ProductPropertyMap
{
    const TYPE_ATTRIBUTE = 1;
    const TYPE_FEATURE = 2;

    public function createMap()
    {
        Db::getInstance()->insert(
            'blmod_xml_product_property_map',
            array(
                'name' => pSQL(Tools::getValue('product_property_map_name')),
                'type_id' => (int)Tools::getValue('product_property_map_type'),
                'created_at' => pSQL(date('Y-m-d H:i:s')),
            )
        );

        return true;
    }

    public function getMaps($typeId)
    {
        return Db::getInstance()->executeS('
			SELECT m.*
			FROM '._DB_PREFIX_.'blmod_xml_product_property_map m
			WHERE m.type_id = "'.(int)$typeId.'"
			ORDER BY m.name ASC');
    }

    public function updateMapValues()
    {
        $mapId = (int)Tools::getValue('map_id');
        $properties = Tools::getValue('property');

        Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'blmod_xml_product_property_map_value WHERE map_id = "'.(int)$mapId.'"');

        foreach ($properties as $groupId => $groups) {
            foreach ($groups as $propertyId => $value) {
                if (empty($value) && $value != '0') {
                    continue;
                }

                Db::getInstance()->Execute('
                    INSERT INTO '._DB_PREFIX_.'blmod_xml_product_property_map_value
                    (`map_id`, `group_id`, `property_id`, `value`, `created_at`)
                    VALUE
                    ("'.(int)$mapId.'", "'.(int)$groupId.'", "'.(int)$propertyId.'", "'.pSQL($value).'", "'.pSQL(date('Y-m-d H:i:s')).'")
                ');
            }
        }

        return true;
    }

    public function getMapValues($mapId)
    {
        return Db::getInstance()->executeS('
			SELECT m.group_id, m.property_id, m.`value`
			FROM '._DB_PREFIX_.'blmod_xml_product_property_map_value m
			WHERE m.map_id = "'.(int)$mapId.'"');
    }

    public function getMapValuesWithKey($mapId)
    {
        $valuesWithKey = array();

        if (empty($mapId)) {
            return $valuesWithKey;
        }

        $values = $this->getMapValues($mapId);

        if (empty($values)) {
            return $valuesWithKey;
        }

        foreach ($values as $v) {
            $valuesWithKey[$v['group_id'].'-'.$v['property_id']] = $v['value'];
        }

        return $valuesWithKey;
    }

    public function deleteMap($mapId)
    {
        Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'blmod_xml_product_property_map WHERE id = "'.(int)$mapId.'"');
        Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'blmod_xml_product_property_map_value WHERE map_id = "'.(int)$mapId.'"');

        return true;
    }
}

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

class ProductSettings
{
    const DEFAULT_SETTINGS_ID = 0;
    const FIELD_WITHOUT_NAME = '-';

    public function getByProductAndPackageId($productId, $packageId = 0)
    {
        return Db::getInstance()->getRow('
			SELECT s.*, s.product_id AS id_product
			FROM '._DB_PREFIX_.'blmod_xml_product_settings s
			WHERE s.product_id = "'.(int)$productId.'" AND s.package_id = "'.(int)$packageId.'"
		');
    }

    public function getXmlByPackageId($packageId)
    {
        $settings = $this->getByPackageId($packageId);

        if (empty($settings)) {
            return array();
        }

        $settingsDefault = $this->getByPackageId($packageId, self::DEFAULT_SETTINGS_ID);

        $settingsByProduct = array();
        $fields = $this->getXmlFields();
        $products = array();

        foreach ($settings as $s) {
            $settingsByProduct[$s['product_id']] = $s;
            $settingsByProduct[$s['product_id']]['xml_custom'] = htmlspecialchars_decode($settingsByProduct[$s['product_id']]['xml_custom'], ENT_QUOTES);
            $xml = '';
            $fieldDeep = array();

            foreach ($settingsByProduct[$s['product_id']] as $key => $v) {
                if ($key == 'product_id') {
                    continue;
                }

                if (empty($v) && $v !== '0') {
                    $v = $settingsDefault[$key];
                }

                $fieldList = explode('/', $fields[$key]);

                if (empty($fieldList[1])) {
                    if ($fields[$key] == self::FIELD_WITHOUT_NAME) {
                        $xml .= $v;
                        continue;
                    }

                    $xml .= '<'.$fields[$key].'>'.$v.'</'.$fields[$key].'>';
                } else {
                    $fieldDeep[$fieldList[0]][$fieldList[1]] = (empty($v) && $v != 0) ? $settingsByProduct[0][$key] : $v;
                }
            }

            foreach ($fieldDeep as $field => $secondLevel) {
                $xml .= '<'.$field.'>';

                foreach ($secondLevel as $fieldSecond => $v) {
                    $xml .= '<'.$fieldSecond.'>'.$v.'</'.$fieldSecond.'>';
                }

                $xml .= '</'.$field.'>';
            }

            $products[$s['product_id']] = $xml;
        }

        return $products;
    }

    public function getByPackageId($packageId, $productId = false)
    {
        $where = ($productId !== false) ? ' AND s.product_id = "'.(int)$productId.'"' : '';

        $settings = Db::getInstance()->executeS('
			SELECT s.product_id, s.total_budget, s.daily_budget, s.cpc, s.price_type, s.xml_custom
			FROM '._DB_PREFIX_.'blmod_xml_product_settings s
			WHERE s.package_id = "'.(int)$packageId.'"'.$where.'
			ORDER BY s.product_id ASC
		');

        if (!empty($settings[0]) && $productId !== false) {
            return $settings[0];
        }

        return $settings;
    }

    public function getXmlFields()
    {
        return array(
            'total_budget' => 'admarkt:budget/admarkt:totalBudget',
            'daily_budget' => 'admarkt:budget/admarkt:dailyBudget',
            'cpc' => 'admarkt:budget/admarkt:cpc',
            'price_type' => 'admarkt:priceType',
            'xml_custom' => self::FIELD_WITHOUT_NAME,
        );
    }
}

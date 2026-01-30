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

class SkroutzAnalyticsXml
{
    protected $feedSettings;

    public function __construct($feedSettings = [])
    {
        $this->feedSettings = $feedSettings;
    }

    public function getCombinationId($productId, $combinationId)
    {
        $langId = (int)Configuration::get('PS_LANG_DEFAULT');
        $langId = !empty($langId) ? $langId : 1;
        $combinationIdNew = $combinationId;
        $mergeGroupId = $this->getAttributeMergeGroupId();
        $productColorAttributeId = 0;
        $allCombinationsWithTheSameColor = [];

        if (empty($mergeGroupId)) {
            return $combinationId;
        }

        $product_class = new Product($productId, false, $langId);
        $combinations = $product_class->getAttributesResume($langId, ' ', ', ');
        $productAttributes = $product_class->getAttributesGroups($langId);

        if (empty($combinations)) {
            return $combinationId;
        }

        foreach ($productAttributes as $p) {
            if ($p['id_product_attribute'] == $combinationId && $p['id_attribute_group'] == $mergeGroupId) {
                $productColorAttributeId = $p['id_attribute'];
                break;
            }
        }

        foreach ($productAttributes as $p) {
            if ($productColorAttributeId == $p['id_attribute']) {
                $allCombinationsWithTheSameColor[] = $p['id_product_attribute'];
            }
        }

        if (!empty($allCombinationsWithTheSameColor)) {
            foreach ($combinations as $c) {
                if ($c['quantity'] < 1) {
                    continue;
                }

                if (in_array($c['id_product_attribute'], $allCombinationsWithTheSameColor)) {
                    return $c['id_product_attribute'];
                }
            }
        }

        return $combinationIdNew;
    }

    protected function getAttributeMergeGroupId()
    {
        if (empty($this->feedSettings['merge_attributes_parent'])) {
            return 0;
        }

        return $this->feedSettings['merge_attributes_parent'];
    }
}

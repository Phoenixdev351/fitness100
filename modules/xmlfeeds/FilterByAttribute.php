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

class FilterByAttribute
{
    public function isRequiredAttributeExists($filterStatus, $usefulAttributes, $combinationIdProductAttribute, $attributesList = array())
    {
        $requiredAttributeExists = true;

        if (!empty($filterStatus) && !empty($usefulAttributes)) {
            if (empty($attributesList)) {
                return false;
            }

            $requiredAttributeExists = false;

            foreach ($attributesList as $a) {
                if (!empty($combinationIdProductAttribute) && $a['id_product_attribute'] != $combinationIdProductAttribute) {
                    continue;
                }

                if (in_array($a['id_attribute'], $usefulAttributes)) {
                    $requiredAttributeExists = true;
                    break;
                }
            }
        }

        return $requiredAttributeExists;
    }
}

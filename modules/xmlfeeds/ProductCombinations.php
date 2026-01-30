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

class ProductCombinations
{
    const COMBINATION_LIMIT = 100;

    protected $langId = 0;
    protected $product = null;
    protected $isTooMany = false;

    public function getCombinations($product, $langId)
    {
        $this->langId = $langId;

        return $product->getAttributesResume($this->langId, ' ', ', ');
    }

    protected function getCombinationsFromFeatures()
    {
        $attributes = $this->product->getAttributesGroups($this->langId);

        if (empty($attributes)) {
            return [];
        }

        $attributesByGroups = [];
        $attributesByKey = [];

        foreach ($attributes as $a) {
            $id = $a['id_attribute_group'].'-'.$a['id_attribute'];

            if (empty($attributesByGroups[$a['id_attribute_group']])) {
                $attributesByGroups[$a['id_attribute_group']][] = $id;
                $attributesByKey[$a['id_attribute']] = $a;
                continue;
            }

            if (in_array($id, $attributesByGroups[$a['id_attribute_group']])) {
                continue;
            }

            $attributesByGroups[$a['id_attribute_group']][] = $id;
            $attributesByKey[$a['id_attribute']] = $a;
        }

        $attributesByGroups = array_values($attributesByGroups);

        if (empty($attributesByGroups)) {
            return [];
        }

        $combinationsAttributes = $this->getCombinationsFromAttributes($attributesByGroups);

        //echo '<pre>';
        //print_r($combinationsAttributes);
        //die;

        if (empty($combinationsAttributes)) {
            return [];
        }

        return $this->finalCombinationBuilder($combinationsAttributes, $attributesByKey);
    }

    protected function getCombinationsFromAttributes($arrays, $i = 0)
    {
        if (!isset($arrays[$i])) {
            return [];
        }

        if ($i == count($arrays) - 1) {
            return $arrays[$i];
        }

        $tmp = $this->getCombinationsFromAttributes($arrays, $i + 1);

        $result = [];

        foreach ($arrays[$i] as $v) {
            foreach ($tmp as $t) {
                $result[] = is_array($t) ? array_merge([$v,], $t) : [$v, $t,];

                if (count($result) > self::COMBINATION_LIMIT) {
                    $this->isTooMany = true;
                    return [];
                }
            }
        }

        return $result;
    }

    protected function finalCombinationBuilder($combinationsAttributes, $attributesByKey)
    {
        $combinations = [];
        $id = 1;

        foreach ($combinationsAttributes as $attributes) {
            $name = [];
            $url = [];
            $quantity = 9999999999;
            $weight = 0;
            $price = 0;

            if (!is_array($attributes)) {
                list($idAttributeGroup, $idAttribute) = explode('-', $attributes);
                $attribute = $attributesByKey[$idAttribute];
                $name[] = $attribute['public_group_name'].'-'.$attribute['attribute_name'];
                $url[] = $attribute['id_attribute'].'-'.trim($attribute['group_name']).'-'.trim($attribute['attribute_name']);
                $weight = $attribute['weight'];
                $quantity = $attribute['quantity'];
                $price = $attribute['price'];
            } else {
                foreach ($attributes as $a) {
                    list($idAttributeGroup, $idAttribute) = explode('-', $a);
                    $attribute = $attributesByKey[$idAttribute];

                    $name[] = $attribute['public_group_name'] . '-' . $attribute['attribute_name'];
                    $url[] = $attribute['id_attribute'].'-'.trim($attribute['group_name']).'-'.trim($attribute['attribute_name']);
                    $weight += $attribute['weight'];
                    $price += $attribute['price'];

                    if ($quantity > $attribute['quantity']) {
                        $quantity = $attribute['quantity'];
                    }
                }
            }

            $urlFinal = Tools::strtolower(implode('/', $url));
            $urlFinal = str_replace([' |', ')', '('], '', $urlFinal);
            $urlFinal = str_replace(' ', '_', $urlFinal);

            $combinations[$id] = [
                'is_created_manually' => true,
                'id_product_attribute' => $id,
                'name' => $this->product->name.' '.implode(', ', $name),
                'quantity' => $quantity,
                'weight' => $weight,
                'url' => '#/'.$urlFinal,
                'sale_price' => 0,
                'base_price' => $this->product->price,
                'ean13' => $this->product->ean13,
                'reference' => $this->product->reference,
                'supplier_reference' => $this->product->supplier_reference,
                'wholesale_price' => $this->product->wholesale_price,
            ];

            $id++;
        }

        return $combinations;
    }
}

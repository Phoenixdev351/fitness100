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

class ProductList
{
    protected $productListExclude = array();

    public function __construct($productListExclude = array())
    {
        $this->productListExclude = $productListExclude;
    }

    public function getProductsByProductList($productList, $productListExcludeActive)
    {
        $products = $this->getProducts(array_diff($productList, $this->productListExclude));

        return array_diff($products, $productListExcludeActive);
    }

    public function getExcludeProductsByProductList()
    {
        return $this->getProducts($this->productListExclude);
    }

    protected function getProducts($productList)
    {
        $products = array();

        if (empty($productList)) {
            return $products;
        }

        $result = Db::getInstance()->executeS('
			SELECT DISTINCT(lp.product_id)
			FROM `'._DB_PREFIX_.'blmod_xml_product_list_product` lp
			WHERE lp.product_list_id IN ('.pSQL(implode(',', $productList)).')
		');

        foreach ($result as $p) {
            $products[] = $p['product_id'];
        }

        return $products;
    }
}

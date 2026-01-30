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

class ProductListAdmin extends Xmlfeeds
{
    const TYPE_SEARCH = 0;

    protected $imageClassName = 'ImageCore';

    protected function getImages($imageId)
    {
        $type = '-'.XmlFeedsTools::getImageType().'.jpg';
        $imageClassName = $this->imageClassName;

        /**
         * @var $imageClass ImageCore
         */
        $imageClass = new $imageClassName($imageId);
        $name = $imageClass->getExistingImgPath();
        $url = _PS_BASE_URL_._THEME_PROD_DIR_.$name.$type;

        if (!file_exists(_PS_PROD_IMG_DIR_.$name.$type)) {
            $url = _PS_BASE_URL_._THEME_PROD_DIR_.$name.'.jpg';
        }

        return $url;
    }

    public function getProductListSettingsPage($active = array(), $activeExclude = array())
    {
        $this->smarty->assign([
            'productList' => $this->getProductList(),
            'active' => !empty($active) ? $active : array(),
            'activeExclude' => $activeExclude,
        ]);

        return $this->displaySmarty('views/templates/admin/element/filterByProductList.tpl');
    }

    public function insertNewProductList()
    {
        $addNewList = Tools::getValue('add_product_list');
        $listName = Tools::getValue('product_list_name');

        if (empty($addNewList) || empty($listName)) {
            return false;
        }

        Db::getInstance()->Execute('
            INSERT INTO '._DB_PREFIX_.'blmod_xml_product_list
            (`name`)
            VALUE
            ("'.pSQL($listName).'")
        ');

        $_POST['product_list_id'] = Db::getInstance()->Insert_ID();

        return true;
    }

    public function deleteProductList()
    {
        $id = Tools::getValue('delete_product_list');

        if (empty($id)) {
            return false;
        }

        Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'blmod_xml_product_list WHERE id = "'.(int)$id.'"');
        $this->remoteProductFromList($id, self::TYPE_SEARCH);

        return true;
    }

    public function getProductList()
    {
        return Db::getInstance()->executeS('SELECT l.id, l.name, (
                SELECT COUNT(DISTINCT(pl.product_id))
                FROM '._DB_PREFIX_.'blmod_xml_product_list_product pl
                WHERE pl.product_list_id = l.id
            ) AS total_products
			FROM '._DB_PREFIX_.'blmod_xml_product_list l
			ORDER BY l.name ASC');
    }

    public function updateProductList()
    {
        $updateProductList = Tools::getValue('update_product_list');
        $productListId = Tools::getValue('product_list_id');

        if (empty($productListId) || empty($updateProductList)) {
            return false;
        }

        $this->remoteProductFromList($productListId, self::TYPE_SEARCH);

        $products = explode(',', trim(Tools::getValue('product_hidden'), ','));

        if (empty($products)) {
            return true;
        }

        foreach ($products as $p) {
            $p = (int)$p;

            if (empty($p)) {
                continue;
            }

            Db::getInstance()->Execute('
                INSERT INTO '._DB_PREFIX_.'blmod_xml_product_list_product
                (`product_list_id`, `product_id`, `category_id`)
                VALUES
                ("'.(int)$productListId.'", "'.(int)$p.'", "'.(int)self::TYPE_SEARCH.'")
            ');
        }

        return true;
    }

    public function remoteProductFromList($productListId, $categoryId = 0)
    {
        Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'blmod_xml_product_list_product WHERE product_list_id = "'.(int)$productListId.'" AND category_id = '.(int)$categoryId);
    }

    public function getProductListProducts($productListId)
    {
        $id_lang = (int)(Configuration::get('PS_LANG_DEFAULT'));
        $this->imageClassName = (!class_exists('ImageCore', false) || _PS_VERSION_ > '1.5.3') ?  'Image' : 'ImageCore';

        $products = Db::getInstance()->executeS('SELECT DISTINCT(l.id_product), l.name, cl.name AS cat_name, i.id_image
			FROM `'._DB_PREFIX_.'blmod_xml_product_list_product` lp
			LEFT JOIN '._DB_PREFIX_.'product_lang l ON
			l.id_product = lp.product_id
            LEFT JOIN '._DB_PREFIX_.'product p ON
            l.id_product = p.id_product
            LEFT JOIN '._DB_PREFIX_.'category_lang cl ON
            (p.id_category_default = cl.id_category AND cl.id_lang = "'.(int)$id_lang.'")
            LEFT JOIN `'._DB_PREFIX_.'image` i ON
            (p.id_product = i.id_product AND i.`cover`= 1)
			WHERE lp.product_list_id = "'.pSQL($productListId).'" AND lp.category_id = '.(int)self::TYPE_SEARCH.'
			GROUP BY l.id_product
			ORDER BY l.name ASC');

        $productsIdList = array();

        if (empty($products)) {
            return array(
                'productIdList' => '',
                'products' => array(),
            );
        }

        foreach ($products as $k => $p) {
            $productsIdList[] = $p['id_product'];

            $products[$k]['image'] = $this->getImages(!empty($p['id_image']) ? $p['id_image'] : false);

            if (!empty($products[$k]['cat_name'])) {
                $products[$k]['cat_name'] = ', '.$products[$k]['cat_name'];
            }
        }

        return array(
            'productIdList' => implode(',', $productsIdList),
            'products' => $products,
        );
    }

    public function getProductsByCategoryId($categoryId, $productListId, $langId)
    {
        $products = Db::getInstance()->executeS('SELECT p.id_product, pl.name, i.id_image, lp.id AS list_id
            FROM '._DB_PREFIX_.'product p
            LEFT JOIN '._DB_PREFIX_.'product_lang pl ON
            (pl.id_product = p.id_product AND pl.id_lang = '.(int)$langId.')
            LEFT JOIN '._DB_PREFIX_.'image i ON
            (p.id_product = i.id_product AND i.cover = 1)
            LEFT JOIN '._DB_PREFIX_.'blmod_xml_product_list_product lp ON
            (lp.product_id = p.id_product AND lp.product_list_id = '.(int)$productListId.' AND lp.category_id = '.(int)$categoryId.')
            WHERE p.id_category_default = '.(int)$categoryId.'
            ORDER BY pl.name ASC
            LIMIT 2000');

        if (empty($products)) {
            return $products;
        }

        foreach ($products as $k => $p) {
            $products[$k]['image'] = $this->getImages(!empty($p['id_image']) ? $p['id_image'] : false);
        }

        return $products;
    }

    public function updateProductListByCategory()
    {
        $products = Tools::getValue('products_by_category');
        $categoryId = Tools::getValue('product_list_category_id');
        $productListId = Tools::getValue('product_list_id');
        $updateProductList = Tools::getValue('update_product_list');

        if (empty($productListId) || empty($categoryId) || empty($updateProductList)) {
            return false;
        }

        $this->remoteProductFromList($productListId, $categoryId);

        if (empty($products)) {
            return true;
        }

        foreach ($products as $p) {
            $p = (int)$p;

            if (empty($p)) {
                continue;
            }

            Db::getInstance()->Execute('
                INSERT INTO '._DB_PREFIX_.'blmod_xml_product_list_product
                (`product_list_id`, `product_id`, `category_id`)
                VALUES
                ("'.(int)$productListId.'", "'.(int)$p.'", "'.(int)$categoryId.'")
            ');
        }

        return true;
    }

    public function getTotalProductsInCategory($categoryId = 0)
    {
        $total = array();

        if (empty($categoryId)) {
            return $total;
        }

        $list = Db::getInstance()->executeS('SELECT l.category_id, COUNT(l.id) AS total_products
            FROM '._DB_PREFIX_.'blmod_xml_product_list_product l
            WHERE l.product_list_id = '.(int)$categoryId.'
            GROUP BY l.category_id');

        if (empty($list)) {
            return $total;
        }

        foreach ($list as $l) {
            $total[$l['category_id']] = $l['total_products'];
        }

        return $total;
    }
}

<?php
/*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

include_once(_PS_MODULE_DIR_.'stthemeeditor/classes/BaseProductsSlider.php');
class StRelatedProducts extends BaseProductsSlider
{
    protected static $cache_products = array();
    public $_prefix_st = 'ST_RELATED_';
    public $_prefix_stsn = 'STSN_RELATED_';
    public $validation_errors = array();
	function __construct()
	{
		$this->name           = 'strelatedproducts';
		$this->version        = '1.1.0';
        $this->title          = $this->getTranslator()->trans('Related products', array(), 'Shop.Theme.Transformer'); // Front office title block.
		$this->displayName = $this->getTranslator()->trans('Related products', array(), 'Modules.Strelatedproducts.Admin');
		$this->description = $this->getTranslator()->trans('Add related products on product pages.', array(), 'Modules.Strelatedproducts.Admin');
        parent::__construct();
	}
    
    protected function initTabNames()
    {
        $this->_tabs = array(
            array('id'  => '0', 'name' => $this->getTranslator()->trans('General settings', array(), 'Admin.Theme.Transformer')),
            array('id'  => '1', 'name' => $this->getTranslator()->trans('Slider', array(), 'Admin.Theme.Transformer')),
            array('id'  => '2', 'name' => $this->getTranslator()->trans('Left or right column', array(), 'Admin.Theme.Transformer')),
            array('id'  => '3', 'name' => $this->getTranslator()->trans('Hooks', array(), 'Admin.Theme.Transformer')),
        );
    }
    protected function initHookArray()
    {
        $this->_hooks = array(
            'Hooks' => array(
                array(
        			'id' => 'displayLeftColumnProduct',
        			'val' => '1',
        			'name' => $this->getTranslator()->trans('Left column on the product page only', array(), 'Admin.Theme.Transformer')
        		),
                array(
        			'id' => 'displayRightColumnProduct',
        			'val' => '1',
        			'name' => $this->getTranslator()->trans('Right column on the product page only', array(), 'Admin.Theme.Transformer')
        		),
                array(
        			'id' => 'displayProductRightColumn',
        			'val' => '1',
        			'name' => $this->getTranslator()->trans('Product right column', array(), 'Admin.Theme.Transformer')
        		),
                array(
                    'id' => 'displayProductCenterColumn',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Product center column', array(), 'Admin.Theme.Transformer')
                ),
                array(
                    'id' => 'displayProductLeftColumn',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Product left column', array(), 'Admin.Theme.Transformer')
                ),
                array(
                    'id' => 'displayFooterProduct',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Footer product', array(), 'Admin.Theme.Transformer')
                ),
                array(
                    'id' => 'displayMiddleProduct',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Middle product', array(), 'Admin.Theme.Transformer')
                ),
                array(
                    'id' => 'displayProductDescRightColumn',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Right side of product description tab', array(), 'Admin.Theme.Transformer')
                ),
            ),
        );
    }
	function install()
	{
		if (!parent::install() 
            || !$this->registerHook('displayProductRightColumn')
            || !Configuration::updateValue($this->_prefix_st.'NBR_COL', 4)
            || !Configuration::updateValue($this->_prefix_st.'ITEMS_COL', 2)
            || !Configuration::updateValue($this->_prefix_st.'BY_TAG', 1)
        )
			return false;
		$this->clearSliderCache();
		return true;
	}
    public function initFieldsForm()
    {
        parent::initFieldsForm();
        $fields = parent::getFormFields();
		$input = array(
			'type' => 'radio',
			'label' => $this->getTranslator()->trans('Automatically generate related products:', array(), 'Modules.Strelatedproducts.Admin'),
			'name' => 'by_tag',
            'default_value' => 1,
			'values' => array(
				array(
					'id' => 'by_tag_on',
					'value' => 1,
					'label' => $this->getTranslator()->trans('Using tags', array(), 'Admin.Theme.Transformer')),
                array(
                    'id' => 'by_tag_cate',
                    'value' => 2,
                    'label' => $this->getTranslator()->trans('Products from the same category', array(), 'Admin.Theme.Transformer')),
				array(
					'id' => 'by_tag_off',
					'value' => 0,
					'label' => $this->getTranslator()->trans('No', array(), 'Admin.Theme.Transformer')),
			),
            'validation' => 'isUnsignedInt',
		);
        array_unshift($this->fields_form[0]['form']['input'], $input);
        unset($this->fields_form[1]['form']['input']['view_more']);
        $option = array(
            'spacing' => (int)Configuration::get($this->_prefix_st.'SPACING_BETWEEN'),
            'per_lg'  => (int)Configuration::get($this->_prefix_stsn.'PRO_PER_LG'),
            'per_xl'  => (int)Configuration::get($this->_prefix_stsn.'PRO_PER_XL'),
            'per_xxl' => (int)Configuration::get($this->_prefix_stsn.'PRO_PER_XXL'),
            'page'    => 'product',
        );
        $this->fields_form[1]['form']['input']['image_type']['desc'] = $this->calcImageWidth($option);
        $this->fields_form[3]['form'] = array(
			'legend' => array(
				'title' => $this->getTranslator()->trans('Hook manager', array(), 'Admin.Theme.Transformer'),
                'icon' => 'icon-cogs'
			),
            'description' => $this->getTranslator()->trans('Check the hook that you would like this module to display on.', array(), 'Admin.Theme.Transformer').'<br/><a href="'._MODULE_DIR_.'stthemeeditor/img/hook_into_hint.jpg" target="_blank" >'.$this->getTranslator()->trans('Click here to see hook position', array(), 'Admin.Theme.Transformer').'</a>.',
			'input' => $fields['hook'],
			'submit' => array(
				'title' => $this->getTranslator()->trans('   Save all  ', array(), 'Admin.Theme.Transformer')
			),
		);
        unset($this->fields_form[4]);
    }
    public function getProducts($ext='')
    {
        if( Dispatcher::getInstance()->getController() != 'product' )
            return false;
            
        $id_product = (int)Tools::getValue('id_product');
		if (!$id_product)
			return false;
            
        if ($ext && strpos($ext, '_') === false) {
            $ext = '_'.strtoupper($ext);
        }
        if (isset(self::$cache_products[$ext]) && self::$cache_products[$ext])
            return self::$cache_products[$ext];
        $nbr = Configuration::get($this->_prefix_st.'NBR'.$ext);
        if(!$nbr)
            $nbr = 8;
        
        $order_by = 'date_add';
        $order_way = 'DESC';
        $soby = (int)Configuration::get($this->_prefix_st.'SOBY'.$ext);
        if (key_exists($soby, $this->sort_by)) {
            $order_by = $this->sort_by[$soby]['orderBy'];
            $order_way = $this->sort_by[$soby]['orderWay'];
        }
        if ($order_by == 'id_product' || $order_by == 'date_add'  || $order_by == 'date_upd')
			$order_by_prefix = 'p';
		else if ($order_by == 'name')
			$order_by_prefix = 'pl';
        else if ($order_by == 'price')
            $order_by_prefix = 'product_shop';
        $select_num=$nbr;
        $out_of_stock_on=(int)Configuration::get($this->_prefix_st.'OUT_OF_STOCK_ON');
        if($out_of_stock_on)
            $select_num=200;
        $related_products_ids = array();        
        $related_products = Db::getInstance()->executeS('SELECT a.`id_product_2`
            FROM `'._DB_PREFIX_.'accessory` a
            INNER JOIN `'._DB_PREFIX_.'product` p ON (p.`id_product`= a.`id_product_2`)
            '.Shop::addSqlAssociation('product', 'p').'
            LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product`= pl.`id_product` AND pl.`id_lang`='.(int)$this->context->language->id.')
            WHERE a.`id_product_1` = '.(int)$id_product.'
            ORDER BY '.($order_by ? $order_by_prefix.'.'.pSQL($order_by).' '.pSQL($order_way) : 'RAND()')
            );
        foreach($related_products AS $value) {
            $related_products_ids[] = $value['id_product_2'];
        }
        
        $by_tag = Configuration::get($this->_prefix_st.'BY_TAG');
        if($by_tag==1)
		{
            if ($select_num-count($related_products_ids) > 0) {
			     $related_products_by_tags = Db::getInstance()->executeS('SELECT DISTINCT(t.`id_product`)
    				FROM `'._DB_PREFIX_.'product_tag` t
                    LEFT JOIN `'._DB_PREFIX_.'product` p ON (p.`id_product`= t.`id_product`)
                    '.Shop::addSqlAssociation('product', 'p').'
                    LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` '.Shop::addSqlRestrictionOnLang('pl').')
    				WHERE t.`id_product`!='.$id_product.'
    				AND product_shop.`active` = 1 AND product_shop.`visibility` IN ("both", "catalog") AND t.`id_tag` IN (SELECT `id_tag`
    								 FROM `'._DB_PREFIX_.'product_tag`
    								 WHERE `id_product`='.$id_product.')
                    ORDER BY '.($order_by ? $order_by_prefix.'.'.pSQL($order_by).' '.pSQL($order_way) : 'RAND()').'
                    LIMIT '.($select_num-count($related_products_ids)));                                                            
            if(is_array($related_products_by_tags) && count($related_products_by_tags))
                foreach($related_products_by_tags as $v)
                    if(count($related_products_ids)<$select_num && !in_array($v['id_product'], $related_products_ids))
                        $related_products_ids[] = $v['id_product'];
            } 
		}elseif($by_tag==2){
            $id_cate = 0;
            if(isset($this->context->cookie->last_visited_category) && (int) $this->context->cookie->last_visited_category)
                $id_cate = (int) $this->context->cookie->last_visited_category;
            else{
                $category = Context::getContext()->smarty->getTemplateVars('category');
                if(isset($category->id))
                    $id_cate =$category->id;
                else{
                    $id_cate = Db::getInstance()->getValue('
                        SELECT product_shop.`id_category_default`
                        FROM `' . _DB_PREFIX_ . 'product` p
                        ' . Shop::addSqlAssociation('product', 'p') . '
                        WHERE p.`id_product` = ' . (int) $id_product);
                }
            }
            if($id_cate && ($select_num-count($related_products_ids) > 0)){
                $related_products_by_tags = Db::getInstance()->executeS('SELECT cp.`id_product`
                        FROM `' . _DB_PREFIX_ . 'product` p
                        ' . Shop::addSqlAssociation('product', 'p') . '
                        LEFT JOIN `' . _DB_PREFIX_ . 'category_product` cp ON p.`id_product` = cp.`id_product`
                        WHERE cp.`id_category` = ' . (int) $id_cate .
                    ' AND product_shop.`visibility` IN ("both", "catalog")
                     AND product_shop.active = 1
                     AND cp.`id_product` <> '.$id_product.'
                     ORDER BY '.($order_by ? $order_by_prefix.'.'.pSQL($order_by).' '.pSQL($order_way) : 'RAND()').'
                     LIMIT '.($select_num-count($related_products_ids)));
                    
                                                                             
                if(is_array($related_products_by_tags) && count($related_products_by_tags))
                    foreach($related_products_by_tags as $v)
                        if(count($related_products_ids)<$select_num && !in_array($v['id_product'], $related_products_ids))
                            $related_products_ids[] = $v['id_product'];
            }
        }
		
        if(!is_array($related_products_ids) || !count($related_products_ids))
            $related_products_ids = array();
        $products = array();    
        $related_products_ids = array_unique($related_products_ids);
        if (count($related_products_ids))
		{
			$assembler = new ProductAssembler($this->context);
            $presenterFactory = new ProductPresenterFactory($this->context);
            $presentationSettings = $presenterFactory->getPresentationSettings();
            $presenter = new ProductListingPresenter(
                new ImageRetriever(
                    $this->context->link
                ),
                $this->context->link,
                new PriceFormatter(),
                new ProductColorsRetriever(),
                $this->context->getTranslator()
            );

            if (is_array($related_products_ids)) {
                foreach ($related_products_ids as $productId) {
                    if (!$productId) {
                        continue;
                    }
                    $prod = new Product((int)$productId);
                    if (!$prod->id || !$prod->checkAccess(
                            isset($this->context->customer->id) && $this->context->customer->id 
                            ? (int) $this->context->customer->id 
                            : 0
                        )
                    ) {
                        continue;
                    }
                    $product = $presenter->present(
                        $presentationSettings,
                        $assembler->assembleProduct(array(
                            'id_product' => $productId,
                            'id_product_attribute' => Product::getDefaultAttribute($productId),
                        )),
                        $this->context->language
                    );
                    if($out_of_stock_on==1){
                        if($product['quantity']<=0){
                            continue;
                        }
                    }elseif($out_of_stock_on==2){
                        if($product['quantity_all_versions']<=0){
                            continue;
                        }
                    }
                    if ($product['active']) {
                        $products[] = $product;    
                    }
                    if(count($products)>=$nbr)
                        break;
                }
            }
        }
        return self::$cache_products[$ext] = $products;
    }
	public function hookDisplayLeftColumn($params, $hook_hash = '')
	{
	    if( Dispatcher::getInstance()->getController() != 'product' )
            return false;
        $this->smarty->assign(array(
            'from_product_page' => 'isRelatedTo',
        ));
        return parent::hookDisplayLeftColumn($params, $hook_hash.'-'.Tools::getValue('id_product'));
	}
    public function hookDisplayFooterProduct($params)
	{
	    if(Dispatcher::getInstance()->getController() != 'product' || !Tools::getValue('id_product'))
            return false;

        $this->smarty->assign(array(
            'from_product_page' => 'isRelatedTo',
        ));
        return parent::hookDisplayHome($params, __FUNCTION__.'-'.Tools::getValue('id_product'));
	}
    public function hookDisplayMiddleProduct($params)
    {
        return $this->hookDisplayFooterProduct($params);
    }
    public function hookDisplayProductDescRightColumn($params)
    {
        if(Configuration::get('STSN_PRO_DESC_SECONDARY_COLUMN_MD')<5)
            return $this->hookDisplayLeftColumn($params);
        else
            return $this->hookDisplayFooterProduct($params);
    }
    public function getConfigFieldsValues()
    {
        $fields_values = parent::getConfigFieldsValues();
        $fields_values['by_tag'] = Configuration::get($this->_prefix_st.'BY_TAG');
        return $fields_values;
    }
}
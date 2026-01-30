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

include_once(dirname(__FILE__).'/classes/StSpecialSliderClass.php');
include_once(_PS_MODULE_DIR_.'stthemeeditor/classes/BaseProductsSlider.php');
class StSpecialSlider extends BaseProductsSlider
{
    protected static $cache_products = array();
    public $_prefix_st = 'ST_SPECIAL_';
    public $_prefix_stsn = 'STSN_SPECIAL_';
	function __construct()
	{
		$this->name           = 'stspecialslider';
		$this->version        = '1.1.0';
        $this->title          = $this->getTranslator()->trans('Specials', array(), 'Shop.Theme.Transformer'); // Front office title block.
        $this->url_entity     = 'prices-drop';
		$this->displayName = $this->getTranslator()->trans('Special Products Slider', array(), 'Modules.Stspecialslider.Admin');
		$this->description = $this->getTranslator()->trans('Display special products slider on hompage.', array(), 'Modules.Stspecialslider.Admin');
        
        parent::__construct();
    }
	function install()
	{
		if (!parent::install()
            || !$this->installDB()
            || !Configuration::updateValue($this->_prefix_st.'NBR_FOT', 3)
            || !$this->registerHook('displayFooter')
            || !$this->registerHook('displayAdminProductPriceFormFooter')
        )
			return false;
		$this->clearSliderCache();
		return true;
	}
    public function uninstall()
	{
		$this->clearSliderCache();
		if (!parent::uninstall() 
            || !$this->uninstallDB()
        )
			return false;
		return true;
	}
    private function installDB()
	{
		return Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'st_special_product` (
                 `id_product` int(10) NOT NULL,  
                 `id_shop` int(11) NOT NULL,                   
                PRIMARY KEY (`id_product`,`id_shop`),    
                KEY `id_shop` (`id_shop`)       
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;');
	}
	private function uninstallDB()
	{
		return Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'st_special_product`');
	}
    public function getContent()
	{
        if (Tools::getValue('act') == 'gsp' && Tools::getValue('ajax')==1)
        {
            if(!$q = Tools::getValue('q'))
                die;
            $result = $this->getAllSpecialProducts($q, Tools::getValue('excludeIds'));
            foreach ($result AS $value)
		      echo trim($value['name']).'['.trim($value['reference']).']|'.(int)($value['id_product'])."\n";
            die;
        }
        if (Tools::getValue('act') == 'setstspecial' && Tools::getValue('ajax')==1)
        {
            $ret = array('r'=>false,'msg'=>'');
            if(!$id_product = Tools::getValue('id_product'))
                $ret['msg'] = $this->getTranslator()->trans('Product ID error', array(), 'Modules.Stspecialslider.Admin');
            else
            {
                if (StSpecialSliderClass::setByProductId($id_product, (int)Tools::getValue('fl'), $this->context->shop->id))
                {
                    $ret['r'] = true;
                    $ret['msg'] = $this->getTranslator()->trans('Successful update', array(), 'Admin.Theme.Transformer');
                    $this->clearSliderCache();
                }  
                else
                    $ret['msg'] = $this->getTranslator()->trans('Error occurred when updating', array(), 'Admin.Theme.Transformer');
            }
            echo json_encode($ret);
            die;
        }
        $this->context->controller->addJS($this->_path.'views/js/admin.js');
        return parent::getContent();
	}
    protected function saveForm()
    {
        if (isset($_POST['savesliderform'])) {
            StSpecialSliderClass::deleteByShop((int)$this->context->shop->id);
            $res = true;
            if($id_product= Tools::getValue('id_product'))
                foreach($id_product AS $value)
                {
                  $res &= Db::getInstance()->insert('st_special_product', array(
        					'id_product' => (int)$value,
        					'id_shop' => (int)$this->context->shop->id,
        				));  
                }
            if ($res) {
                parent::saveForm();
            }    
        } 
    }
    public function initFieldsForm()
    {
        parent::initFieldsForm();
        $custom_fields['products'] = array(
			'type' => 'text',
			'label' => $this->getTranslator()->trans('Specific special products:', array(), 'Modules.Stspecialslider.Admin'),
			'name' => 'products',
            'autocomplete' => false,
            'class' => 'fixed-width-xxl',
            'desc' => '',
		);
        $this->fields_form[0]['form']['input'] = array_merge($custom_fields, $this->fields_form[0]['form']['input']);
	}
    public function hookDisplayAdminProductPriceFormFooter($params)
    {
        $this->smarty->assign(array(
            'id_product' => Tools::getValue('id_product'),
            'currentIndex' => $this->context->link->getAdminLink('AdminModules', true).'&configure='.$this->name,
            'checked' => StSpecialSliderClass::exists(Tools::getValue('id_product') ,$this->context->shop->id)
            ));
        return $this->display(__FILE__, 'views/templates/admin/stspecialslider.tpl');
    }
    public function getProducts($ext='')
    {
        if ($ext && strpos($ext, '_') === false) {
            $ext = '_'.strtoupper($ext);
        }
        
        if (isset(self::$cache_products[$ext]) && self::$cache_products[$ext])
            return self::$cache_products[$ext];
        $out_of_stock_on=(int)Configuration::get($this->_prefix_st.'OUT_OF_STOCK_ON');
        $out_of=false;
        if (!$products = StSpecialSliderClass::getByShop($this->context->shop->id)) {
            $nbr = Configuration::get($this->_prefix_st.'NBR'.$ext);
            if(!$nbr)
                $nbr = 8;
                
            $order_by = 'price';
            $order_way = 'ASC';
            $soby = (int)Configuration::get($this->_prefix_st.'SOBY'.$ext);
            if (key_exists($soby, $this->sort_by)) {
                $order_by = $this->sort_by[$soby]['orderBy'];
                $order_way = $this->sort_by[$soby]['orderWay'];
            }
            $select_num=$nbr;
            $out_of=true;
            if($out_of_stock_on)
                $select_num=200;
            $products = Product::getPricesDrop(
                (int)Context::getContext()->language->id,
                0,
                $select_num,
                false,
                $order_by,
                $order_way
            );
        }
        
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
        $products_for_template = array();
        if (is_array($products)) {
            foreach ($products as $rawProduct) {
                $prod = new Product((int)$rawProduct['id_product']);
                if (!$prod->id || !$prod->active) {
                    continue;
                }
                if($out_of && $out_of_stock_on==1){
                    if($rawProduct['quantity']<=0){
                        continue;
                    }
                }elseif($out_of && $out_of_stock_on==2){
                    if($rawProduct['quantity_all_versions']<=0){
                        continue;
                    }
                }
                //why? the present function would add images to the data.
                if (!key_exists('id_image', $rawProduct)) {
                    $cover = Product::getCover((int)$rawProduct['id_product']);
                    $rawProduct['id_image'] = $cover['id_image'];
                    $rawProduct['id_image'] = Product::defineProductImage($rawProduct, $this->context->language->id);
                }
                //
                $products_for_template[] = $presenter->present(
                    $presentationSettings,
                    $assembler->assembleProduct($rawProduct),
                    $this->context->language
                );
                if($out_of && count($products_for_template)>=$nbr)
                    break;
            }
        }
        return self::$cache_products[$ext] = $products_for_template;
    }
    public function hookActionObjectSpecificPriceCoreDeleteAfter($params)
    {
        $this->clearSliderCache();
    }
    public function hookActionObjectSpecificPriceCoreAddAfter($params)
    {
        $this->clearSliderCache();
    }
    public function hookActionObjectSpecificPriceCoreUpdateAfter($params)
    {
        $this->clearSliderCache();
    }
    public function getConfigFieldsValues()
    {
        $fields_values = array(
            'products' => '',
        );
        $products_html = '';
        foreach(StSpecialSliderClass::getByShop((int)$this->context->shop->id) AS $value)
        {
            $product = new Product($value['id_product'], false, Context::getContext()->language->id);
            $products_html .= '<li>'.$product->name.'['.$product->reference.']
            <a href="javascript:;" class="del_product"><img src="../img/admin/delete.gif" /></a>
            <input type="hidden" name="id_product[]" value="'.$value['id_product'].'" /></li>';
        }
        
        $this->fields_form[0]['form']['input']['products']['desc'] = $this->getTranslator()->trans('Only display the following products on frontoffice if specified.', array(), 'Admin.Theme.Transformer')
                .': <ul id="curr_products">'.$products_html.'</ul>';
        
        return array_merge($fields_values, parent::getConfigFieldsValues());
    }
    private function getAllSpecialProducts($q = '',$excludeIds = false)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT `id_product`, `id_product_attribute`
			FROM `'._DB_PREFIX_.'specific_price`
			WHERE `reduction` > 0
		    ', false);
		$ids_product = array();
		while ($row = Db::getInstance()->nextRow($result))
			$ids_product[] = (int)$row['id_product'];
        if (!$ids_product)
            return $ids_product;
        
        $sql = '
		SELECT p.`id_product`,pl.`name`,p.`reference`
		FROM `'._DB_PREFIX_.'product` p
		'.Shop::addSqlAssociation('product', 'p').'
		LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (
			p.`id_product` = pl.`id_product`
			AND pl.`id_lang` = '.(int)$this->context->language->id.Shop::addSqlRestrictionOnLang('pl').'
		)
        WHERE p.`id_product` IN('.implode(',', array_unique($ids_product)).')
        AND pl.`name` LIKE "%'.$q.'%"
        '.($excludeIds ? 'AND p.`id_product` NOT IN('.$excludeIds.')' : '').'
        ';
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }
}
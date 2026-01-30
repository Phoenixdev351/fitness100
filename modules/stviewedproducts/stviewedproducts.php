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
class StViewedProducts extends BaseProductsSlider
{
    public $_prefix_st = 'ST_VIEWED_';
    public $_prefix_stsn = 'STSN_VIEWED_';
    public $extra_hooks = array();
	public function __construct()
	{
		$this->name           = 'stviewedproducts';
		$this->version        = '1.2.4';
        $this->title          = $this->getTranslator()->trans('Recently Viewed', array(), 'Shop.Theme.Transformer'); // Front office title block.
		$this->displayName    = $this->getTranslator()->trans('Viewed products block', array(), 'Modules.Stviewedproducts.Admin');
		$this->description    = $this->getTranslator()->trans('Adds a block displaying recently viewed products.', array(), 'Modules.Stviewedproducts.Admin');
		
        $this->extra_hooks = array(
            array(
                'id' => 'displayNav1',
                'val' => '1',
                'name' => $this->getTranslator()->trans('Topbar left - displayNav1', array(), 'Admin.Theme.Transformer')
            ),
            array(
                'id' => 'displayNav2',
                'val' => '1',
                'name' => $this->getTranslator()->trans('Topbar right - displayNav2', array(), 'Admin.Theme.Transformer')
            ),
            array(
                'id' => 'displayNav3',
                'val' => '1',
                'name' => $this->getTranslator()->trans('Topbar center - displayNav3', array(), 'Admin.Theme.Transformer')
            ),
            array(
                'id' => 'displayMainMenuWidget',
                'val' => '1',
                'name' => $this->getTranslator()->trans('Main menu widget', array(), 'Admin.Theme.Transformer')
            ),
            array(
                'id' => 'displaySideBar',
                'val' => '1',
                'name' => $this->getTranslator()->trans('Sibebar', array(), 'Admin.Theme.Transformer')
            ),
            array(
                'id' => 'displayCategoryHeader',
                'val' => '1',
                'name' => $this->getTranslator()->trans('Category header', array(), 'Admin.Theme.Transformer')
            ),
            array(
                'id' => 'displayCategoryFooter',
                'val' => '1',
                'name' => $this->getTranslator()->trans('Category footer', array(), 'Admin.Theme.Transformer')
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
        );
        parent::__construct();
	}
    protected function initHookArray()
    {
        parent::initHookArray();
        $this->_hooks['Hooks'] = array_merge($this->_hooks['Hooks'], $this->extra_hooks);
    }
	public function install()
	{
		$result = parent::install()  
        && $this->registerHook('displaySideBar') 
		&& $this->registerHook('actionProductDelete') 
        && $this->registerHook('displayProductAdditionalInfo');
        
		return $result;
	}
    public function getFormFields()
    {
        $form_fields = parent::getFormFields();
        $custom_fields['viewed_max_nbr'] = array(
            'type' => 'text',
            'label' => $this->getTranslator()->trans('How many recently viewed products to remember:', array(), 'Modules.Stviewedproducts.Admin'),
            'name' => 'viewed_max_nbr',
            'default_value' => 10,
            'validation' => 'isUnsignedInt',
            'class' => 'fixed-width-sm',
            'desc' => $this->getTranslator()->trans('Leave it empty to use the default vaule 10', array(), 'Modules.Stviewedproducts.Admin'),
        );
        $custom_fields['show_viewed_nbr'] = array(
            'type' => 'switch',
            'label' => $this->getTranslator()->trans('Display the number of recently viewed products:', array(), 'Modules.Stviewedproducts.Admin'),
            'name' => 'show_viewed_nbr',
            'default_value' => 1,
            'is_bool' => true,
            'values' => array(
                array(
                    'id' => 'show_viewed_nbr_on',
                    'value' => 1,
                    'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Theme.Transformer')),
                array(
                    'id' => 'show_viewed_nbr_off',
                    'value' => 0,
                    'label' => $this->getTranslator()->trans('No', array(), 'Admin.Theme.Transformer')),
            ),
            'validation' => 'isUnsignedInt',
        );
        $custom_fields['viewed_link_type'] = array(
            'type' => 'radio',
            'label' => $this->getTranslator()->trans('How the "Viewed Products" link on the header works:', array(), 'Modules.Stviewedproducts.Admin'),
            'name' => 'viewed_link_type',
            'default_value' => 0,
            'is_bool' => true,
            'values' => array(
                array(
                    'id' => 'viewed_link_type_on',
                    'value' => 0,
                    'label' => $this->getTranslator()->trans('Drop down menu', array(), 'Admin.Theme.Transformer')),
                array(
                    'id' => 'viewed_link_type_off',
                    'value' => 1,
                    'label' => $this->getTranslator()->trans('Button', array(), 'Admin.Theme.Transformer')),
            ),
            'validation' => 'isUnsignedInt',
        );

        $form_fields['setting'] = array_merge($custom_fields, $form_fields['setting']);

        unset($form_fields['setting']['out_of_stock_on']);
        unset($form_fields['home_slider']['soby']);
        unset($form_fields['column']['soby_col']);
        unset($form_fields['footer']['soby_fot']);
        return $form_fields;
    }
	public function getProducts($ext='')
    {
        if ($ext && strpos($ext, '_') === false) {
            $ext = '_'.strtoupper($ext);
        }
    	$productIds = $this->getViewedProductIds($ext);

		if (count($productIds))
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

            $productsViewed = array();
            if (is_array($productIds) && count($productIds)) {
                foreach ($productIds as $productId) {
                    if(!$productId)
                        continue;
                    $product = new Product((int)$productId);
                    if (!$product->id || !$product->active) {
                        continue;
                    }
                    $productsViewed[] = $presenter->present(
                        $presentationSettings,
                        $assembler->assembleProduct(array(
                            'id_product' => $productId,
                            'id_product_attribute' => Product::getDefaultAttribute($productId),
                        )),
                        $this->context->language
                    );
                }
            }
            return $productsViewed;
		} else {
			return array();
        }
    }
    public function _prepareSimple()
    {
        $this->smarty->assign(array(
            'viewed_products'              => $this->getProducts(),
            'show_viewed_nbr' => Configuration::get($this->_prefix_st.'SHOW_VIEWED_NBR'),
        ));
    }
    public function hookDisplayHome($params, $func = '', $flag = 0)
    {
        return parent::hookDisplayHome($params, false);
    }
    public function hookDisplaySideBar($params)
    {
        $this->_prepareSimple();
        return $this->display(__FILE__, 'stviewedproducts-side.tpl');
    }
    public function hookDisplayFooterProduct($params)
    {
        $this->smarty->assign(array(
            'from_product_page' => 'isSimilarTo',
        ));
        return $this->hookDisplayHome($params, false);
    }
    public function hookDisplayNav1($params)
    {
        $this->_prepareSimple();
        return $this->display(__FILE__, Configuration::get($this->_prefix_st.'VIEWED_LINK_TYPE') ? 'stviewedproducts-link.tpl' : 'stviewedproducts-nav.tpl');
    }
    public function hookDisplayNav2($params)
    {
        return $this->hookDisplayNav1($params);
    }
    public function hookDisplayNav3($params)
    {
        return $this->hookDisplayNav1($params);
    }
    public function hookDisplayMainMenuWidget($params)
    {
        return $this->hookDisplayNav1($params);
    }
	public function hookDisplayCategoryHeader($params, $hook_hash='')
    {
		return $this->hookDisplayHome($params, __FUNCTION__);
    }
    public function hookDisplayCategoryFooter($params, $hook_hash='')
    {
        return $this->hookDisplayHome($params, __FUNCTION__);
    }
    public function hookDisplayLeftColumn($params, $hook_hash = '')
	{
        return parent::hookDisplayLeftColumn($params, false);
	}
    /*public function hookDisplayHome($params, $hook_hash = '', $flag = 0)
	{
        return parent::hookDisplayHome($params, false, $flag);
	}*/
    public function hookDisplayFooter($params, $hook_hash = '')
	{
        return parent::hookDisplayFooter($params, false);
	}
    protected function addViewedProduct($idProduct)
    {
        $arr = array();

        if (isset($this->context->cookie->viewed)) {
            $arr = explode(',', $this->context->cookie->viewed);
        }

        array_unshift($arr, $idProduct);
        $arr = array_unique($arr);
        $max_nbr = Configuration::get($this->_prefix_st.'VIEWED_MAX_NBR');
        $max_nbr || $max_nbr = 10;
        $arr = array_slice($arr, 0, $max_nbr);
        $this->context->cookie->viewed = trim(implode(',', $arr), ',');
        $this->context->cookie->write();
    }
    protected function removeViewedProduct($idProduct)
    {
        $arr = array();

        if (isset($this->context->cookie->viewed)) {
            $this->context->cookie->viewed = preg_replace('/\b,?'.$idProduct.',?\b/', '', $this->context->cookie->viewed);
        }
    }
    protected function getViewedProductIds($ext='')
    {
        $arr = array();
        if(!isset($this->context->cookie->viewed)) {
            return $arr;
        }
        $arr = explode(',', $this->context->cookie->viewed);
        $arr = array_unique($arr);
        $nProducts = Configuration::get($this->_prefix_st.'NBR'.$ext);
        if(!$nProducts) {
            $nProducts = Configuration::get($this->_prefix_st.'VIEWED_MAX_NBR');
        }
        $nProducts || $nProducts = 10;
        
        return array_slice($arr, 0, $nProducts);
    }
    public function renderWidget($hookName = null, array $configuration = [])
    {
        if ('displayProductAdditionalInfo' === $hookName) {
            $this->addViewedProduct(Tools::getvalue('id_product'));
            return;
        }
        return;
    }
    public function hookActionProductDelete($params)
    {
        if (isset($params['id_product']) && $params['id_product']) {
            $this->removeViewedProduct($params['id_product']);
        }
    }
}
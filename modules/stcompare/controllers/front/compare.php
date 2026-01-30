<?php
/*
* 2007-2015 PrestaShop
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
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
class StCompareCompareModuleFrontController extends ModuleFrontController
{
    private $_prefix_st = 'ST_COMP_';
	public $ssl = true;

	public function __construct()
	{
		parent::__construct();
		$this->context = Context::getContext();
	}

	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		parent::initContent();
		$action = Tools::getValue('action');

		if (!Tools::isSubmit('ajax'))
			$this->assign();
		elseif (!empty($action) && method_exists($this, 'ajaxProcess'.Tools::toCamelCase($action)))
			$this->{'ajaxProcess'.Tools::toCamelCase($action)}();
		else
			die(Tools::jsonEncode(array('error' => $this->trans('Method doesn\'t exist', array(), 'Shop.Theme.Transformer'))));
	}
    
	public function assign()
	{
        $errors = array();
		$products = array();
        $arr = array();
        if (isset($this->context->cookie->stcompareids) && $this->context->cookie->stcompareids) {
            $arr = explode(',', $this->context->cookie->stcompareids);
        }
        $arr = array_unique($arr);
        if (count($arr)) {
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

            $listProducts = array();
            $listFeatures = array();
            foreach($arr AS $k=>$id) {
                $prod = new Product((int)$id);
                if (!Validate::isLoadedObject($prod)) {
                    unset($arr[$k]);
                    continue;
                }
                $_p = $presenter->present(
                    $presentationSettings,
                    $assembler->assembleProduct(array('id_product' => $id)),
                    $this->context->language
                );

                foreach ($_p['features'] as $feature) {
                    $listFeatures[$id][$feature['id_feature']] = $feature['value'];
                }
                $products[] = $_p;
            }
            $ordered_features = $this->getFeaturesForComparison($arr, $this->context->language->id);
            $this->context->smarty->assign(array(
                'stcompare_ordered_features' => $ordered_features,
                'stcompare_product_features' => $listFeatures,
                'stcompare_products' => $products,
                'stcompare_items' => Configuration::get($this->_prefix_st.'ITEMS'),
            ));
        }
        
        $this->setTemplate('module:stcompare/views/templates/front/list.tpl');
	}
    public static function getFeaturesForComparison($list_ids_product, $id_lang)
    {
        if (!Feature::isFeatureActive()) {
            return false;
        }

        $ids = '';
        foreach ($list_ids_product as $id) {
            $ids .= (int)$id.',';
        }

        $ids = rtrim($ids, ',');

        if (empty($ids)) {
            return false;
        }

        return Db::getInstance()->executeS('
            SELECT f.*, fl.*
            FROM `'._DB_PREFIX_.'feature` f
            LEFT JOIN `'._DB_PREFIX_.'feature_product` fp
                ON f.`id_feature` = fp.`id_feature`
            LEFT JOIN `'._DB_PREFIX_.'feature_lang` fl
                ON f.`id_feature` = fl.`id_feature`
            WHERE fp.`id_product` IN ('.$ids.')
            AND `id_lang` = '.(int)$id_lang.'
            GROUP BY f.`id_feature`
            ORDER BY f.`position` ASC
        ');
    }
    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();

        $breadcrumb['links'][] = array(
            'title' => $this->trans('Product comparison', array(), 'Shop.Theme.Transformer'),
            'url' => $this->context->link->getModuleLink('stcompare', 'compare'),
        );;

        return $breadcrumb;
    }
    
    public function ajaxProcessDeleteCompareProduct()
    {
        $id_product = (int)Tools::getValue('id_product');
        if(!$id_product)
            die(Tools::jsonEncode(array('success' => 0, 'message' => $this->trans('Failed, product ID is empty', array(), 'Shop.Theme.Transformer'))));
        $arr = array();
        if (isset($this->context->cookie->stcompareids) && $this->context->cookie->stcompareids) {
            $arr = explode(',', $this->context->cookie->stcompareids);
            $arr = array_diff($arr, array($id_product));
            $this->context->cookie->__set('stcompareids', trim(implode(',', $arr), ','));
        }

        die(Tools::jsonEncode(array('success' => 1,
            'action' => 0,
            'message' => $this->trans('Removed from %1%compare list%2%', array('%1%'=>'<a href="'.$this->context->link->getModuleLink('stcompare', 'compare').'" class="stcompare_link_in_popup">','%2%'=>'</a>'), 'Shop.Theme.Transformer'),
            'total' => count($arr),
            )));
    }
    public function ajaxProcessDeleteAllCompareProducts()
    {
        $this->context->cookie->__set('stcompareids', '');

        die(Tools::jsonEncode(array('success' => 1,
            'message' => $this->trans('Emptyed %1%compare list%2%', array('%1%'=>'<a href="'.$this->context->link->getModuleLink('stcompare', 'compare').'" class="stcompare_link_in_popup">','%2%'=>'</a>'), 'Shop.Theme.Transformer'),
            'total' => 0,
            )));
    }
        
    public function ajaxProcessAddCompareProduct()
    {
        $id_product = (int)Tools::getValue('id_product');
        if(!$id_product)
            die(Tools::jsonEncode(array('success' => 0, 'message' => $this->trans('Failed, product ID is empty', array(), 'Shop.Theme.Transformer'))));

        $arr = array();
        if (isset($this->context->cookie->stcompareids) && $this->context->cookie->stcompareids) {
            $arr = explode(',', $this->context->cookie->stcompareids);
        }
        array_unshift($arr, $id_product);
        $arr = array_unique($arr);
        /*$max_nbr = Configuration::get($this->_prefix_st.'MAX');
        $max_nbr || $max_nbr = 10;
        $arr = array_slice($arr, 0, $max_nbr);*/
        $this->context->cookie->__set('stcompareids', trim(implode(',', $arr), ','));
        die(Tools::jsonEncode(array('success' => 1,
            'action' => 1, 
            'message' => $this->trans('Added to %1%compare list%2%', array('%1%'=>'<a href="'.$this->context->link->getModuleLink('stcompare', 'compare').'" class="stcompare_link_in_popup">','%2%'=>'</a>'), 'Shop.Theme.Transformer'),
            'total' => count($arr),
            )));
        
    }
}

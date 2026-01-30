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
 
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

include_once(_PS_MODULE_DIR_.'stthemeeditor/classes/BaseSlider.php');
include_once(dirname(__FILE__).'/classes/StProductCategoriesSliderClass.php');
class StProductCategoriesSlider extends BaseSlider implements WidgetInterface
{
    protected static $cache_product_categories = array();
    public  $fields_list;
    public $_prefix_st = 'ST_PRO_CATE_';
    public $_prefix_stsn = 'STSN_PRO_CATE_';
	public $spacer_size = '5';
    protected $fields_default_stsn = array();
    public $dropdownlistgroup_default = array(
        'pro_per_fw' => 0,
        'pro_per_xxl' => 5,
        'pro_per_xl' => 4,
        'pro_per_lg' => 4,
        'pro_per_md' => 3,
        'pro_per_sm' => 3,
        'pro_per_xs' => 2,
    );
    protected $sort_by = array(
        1 => array('id' =>1 , 'name' => 'Product position DESC', 'orderBy'=>'position', 'orderWay'=>'DESC'),
        2 => array('id' =>2 , 'name' => 'Product position ASC', 'orderBy'=>'position', 'orderWay'=>'ASC'),
        3 => array('id' =>3 , 'name' => 'Product Name: A to Z', 'orderBy'=>'name', 'orderWay'=>'ASC'),
        4 => array('id' =>4 , 'name' => 'Product Name: Z to A', 'orderBy'=>'name', 'orderWay'=>'DESC'),
        5 => array('id' =>5 , 'name' => 'Price: Lowest first', 'orderBy'=>'price', 'orderWay'=>'ASC'),
        6 => array('id' =>6 , 'name' => 'Price: Highest first', 'orderBy'=>'price', 'orderWay'=>'DESC'),
        7 => array('id' =>7 , 'name' => 'Added date ASC', 'orderBy'=>'date_add', 'orderWay'=>'ASC'),
        8 => array('id' =>8 , 'name' => 'Added date DESC', 'orderBy'=>'date_add', 'orderWay'=>'DESC'),
        9 => array('id' =>9 , 'name' => 'Update date ASC', 'orderBy'=>'date_upd', 'orderWay'=>'ASC'),
        10 => array('id' =>10 , 'name' => 'Update date DESC', 'orderBy'=>'date_upd', 'orderWay'=>'DESC'),
    );
        
    public static $items = array(
		array('id' => 2, 'name' => '2'),
		array('id' => 3, 'name' => '3'),
		array('id' => 4, 'name' => '4'),
		array('id' => 5, 'name' => '5'),
		array('id' => 6, 'name' => '6'),
    );
	function __construct()
	{
		$this->name           = 'stproductcategoriesslider';
		$this->version        = '1.7.1';
        $this->displayName = $this->getTranslator()->trans('Product Slider', array(), 'Modules.Stproductcategoriesslider.Admin');
		$this->description = $this->getTranslator()->trans('Display products from different categories on the homepage.', array(), 'Modules.Stproductcategoriesslider.Admin');

		parent::__construct();
        $this->initHookArray();
    }
    protected function initHookArray()
    {
        $this->_hooks = array(
                array(
                    'id' => '',
                    'val' => '268435456',
                    'name' => Module::isInstalled('jscomposer') && Module::isEnabled('jscomposer') ? $this->getTranslator()->trans('For visual composer module', array(), 'Admin.Theme.Transformer') : $this->getTranslator()->trans('--', array(), 'Admin.Theme.Transformer'),
                ),
                array(
                    'id' => 'displayFullWidthTop',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Full width top', array(), 'Admin.Theme.Transformer'),
                    'full_width' => 1,
                ),
                array(
        			'id' => 'displayFullWidthTop2',
        			'val' => '2',
        			'name' => $this->getTranslator()->trans('Full width top2', array(), 'Admin.Theme.Transformer'),
                    'full_width' => 1,
        		),
        		array(
        			'id' => 'displayHomeTop',
        			'val' => '4',
        			'name' => $this->getTranslator()->trans('Home top', array(), 'Admin.Theme.Transformer')
        		),
                array(
        			'id' => 'displayHome',
        			'val' => '8',
        			'name' => $this->getTranslator()->trans('Home', array(), 'Admin.Theme.Transformer')
        		),
        		array(
        			'id' => 'displayHomeLeft',
        			'val' => '16',
        			'name' => $this->getTranslator()->trans('Home left', array(), 'Admin.Theme.Transformer')
        		),
        		array(
        			'id' => 'displayHomeRight',
        			'val' => '32',
        			'name' => $this->getTranslator()->trans('Home right', array(), 'Admin.Theme.Transformer')
        		),
                array(
                    'id' => 'displayHomeFirstQuarter',
                    'val' => '64',
                    'name' => $this->getTranslator()->trans('Home first quarter', array(), 'Admin.Theme.Transformer')
                ),
                array(
                    'id' => 'displayHomeSecondQuarter',
                    'val' => '128',
                    'name' => $this->getTranslator()->trans('Home second quarter', array(), 'Admin.Theme.Transformer')
                ),
                array(
                    'id' => 'displayHomeThirdQuarter',
                    'val' => '256',
                    'name' => $this->getTranslator()->trans('Home third quarter', array(), 'Admin.Theme.Transformer')
                ),
                array(
                    'id' => 'displayHomeFourthQuarter',
                    'val' => '512',
                    'name' => $this->getTranslator()->trans('Home fourth quarter', array(), 'Admin.Theme.Transformer')
                ),
                array(
        			'id' => 'displayHomeBottom',
        			'val' => '1024',
        			'name' => $this->getTranslator()->trans('Home bottom', array(), 'Admin.Theme.Transformer')
        		),
                array(
        			'id' => 'displayFullWidthBottom',
        			'val' => '2048',
        			'name' => $this->getTranslator()->trans('Full width bottom', array(), 'Admin.Theme.Transformer'),
                    'full_width' => 1,
        		),
                array(
                    'id' => 'displayCategoryHeader',
                    'val' => '4096',
                    'name' => $this->getTranslator()->trans('Category header', array(), 'Admin.Theme.Transformer')
                ),
                array(
                    'id' => 'displayCategoryFooter',
                    'val' => '8192',
                    'name' => $this->getTranslator()->trans('Category footer', array(), 'Admin.Theme.Transformer')
                ),
                array(
        			'id' => 'displayFooterProduct',
        			'val' => '33554432',
        			'name' => $this->getTranslator()->trans('Product footer', array(), 'Admin.Theme.Transformer')
        		),
                array(
                    'id' => 'displayOrderConfirmation1',
                    'val' => '536870912',
                    'name' => $this->getTranslator()->trans('Order confirmation 1', array(), 'Admin.Theme.Transformer')
                ),
                array(
                    'id' => 'displayMiddleProduct',
                    'val' => '1073741824',
                    'name' => $this->getTranslator()->trans('Product middle', array(), 'Admin.Theme.Transformer')
                ),
                array(
        			'id' => 'displayLeftColumn',
        			'val' => '32768',
        			'name' => $this->getTranslator()->trans('Left column except the produt page', array(), 'Admin.Theme.Transformer')
        		),
        		array(
        			'id' => 'displayRightColumn',
        			'val' => '65536',
        			'name' => $this->getTranslator()->trans('Right column except the produt page', array(), 'Admin.Theme.Transformer')
        		),
                array(
        			'id' => 'displayLeftColumnProduct',
        			'val' => '67108864',
        			'name' => $this->getTranslator()->trans('Left column on the product page only', array(), 'Admin.Theme.Transformer')
        		),
                array(
        			'id' => 'displayRightColumnProduct',
        			'val' => '16384',
        			'name' => $this->getTranslator()->trans('Right column on the product page only', array(), 'Admin.Theme.Transformer')
        		),
                array(
        			'id' => 'displayProductRightColumn',
        			'val' => '134217728',
        			'name' => $this->getTranslator()->trans('Product right column', array(), 'Admin.Theme.Transformer')
        		),
        		array(
                    'id' => 'displayStackedFooter1',
                    'val' => '131072',
                    'name' => $this->getTranslator()->trans('Stacked footer 1', array(), 'Admin.Theme.Transformer')
                ),
                array(
                    'id' => 'displayStackedFooter2',
                    'val' => '262144',
                    'name' => $this->getTranslator()->trans('Stacked footer 2', array(), 'Admin.Theme.Transformer')
                ),
                array(
                    'id' => 'displayStackedFooter3',
                    'val' => '524288',
                    'name' => $this->getTranslator()->trans('Stacked footer 3', array(), 'Admin.Theme.Transformer')
                ),
                array(
                    'id' => 'displayStackedFooter4',
                    'val' => '1048576',
                    'name' => $this->getTranslator()->trans('Stacked footer 4', array(), 'Admin.Theme.Transformer')
                ),
                array(
                    'id' => 'displayStackedFooter5',
                    'val' => '2097152',
                    'name' => $this->getTranslator()->trans('Stacked footer 5', array(), 'Admin.Theme.Transformer')
                ),
                array(
                    'id' => 'displayStackedFooter6',
                    'val' => '4194304',
                    'name' => $this->getTranslator()->trans('Stacked footer 6', array(), 'Admin.Theme.Transformer')
                ),
                array(
        			'id' => 'displayFooter',
        			'val' => '8388608',
        			'name' => $this->getTranslator()->trans('Footer', array(), 'Admin.Theme.Transformer')
        		),
                array(
        			'id' => 'displayFooterAfter',
        			'val' => '16777216',
        			'name' => $this->getTranslator()->trans('Footer After', array(), 'Admin.Theme.Transformer')
        		),
                array(
                    'id' => 'displayStBlogArticleFooter',
                    'val' => '33554432',
                    'name' => $this->getTranslator()->trans('Blog article footer', array(), 'Admin.Theme.Transformer')
                )
        );
    }
	function install()
	{
		if (!parent::install() 
            || !$this->installDB()
			|| !$this->registerHook('addproduct')
			|| !$this->registerHook('updateproduct')
			|| !$this->registerHook('deleteproduct')
            || !$this->registerHook('actionCategoryDelete')
            || !$this->registerHook('actionObjectCategoryDeleteAfter')
            || !$this->registerHook('vcBeforeInit')
            || !Configuration::updateValue($this->_prefix_st.'RANDOM', 0)
            || !Configuration::updateValue($this->_prefix_st.'TABS', 0)
            || !Configuration::updateValue($this->_prefix_st.'TOP_PADDING', '')
            || !Configuration::updateValue($this->_prefix_st.'BOTTOM_PADDING', '')
            || !Configuration::updateValue($this->_prefix_st.'TOP_MARGIN', '')
            || !Configuration::updateValue($this->_prefix_st.'BOTTOM_MARGIN', '')
            || !Configuration::updateValue($this->_prefix_st.'BG_PATTERN', 0)
            || !Configuration::updateValue($this->_prefix_st.'BG_IMG', '')
            || !Configuration::updateValue($this->_prefix_st.'BG_COLOR', '')
            || !Configuration::updateValue($this->_prefix_st.'SPEED', 0.6)
            || !Configuration::updateValue($this->_prefix_st.'TITLE_ALIGN', 0)

            || !Configuration::updateValue($this->_prefix_st.'HEADER_COLOR', '')
            || !Configuration::updateValue($this->_prefix_st.'HEADER_HOVER_COLOR', '')
            || !Configuration::updateValue($this->_prefix_st.'HEADER_BG', '')
            || !Configuration::updateValue($this->_prefix_st.'HEADER_HOVER_BG', '')
            || !Configuration::updateValue($this->_prefix_st.'HEADER_BORDER', '')
            || !Configuration::updateValue($this->_prefix_st.'HEADER_ACTIVE_BORDER', '')
            || !Configuration::updateValue($this->_prefix_st.'TAB_BOTTOM_BORDER', '')
            || !Configuration::updateValue($this->_prefix_st.'TAB_FONT_SIZE', '')
        )
			return false;
        $res = $this->prepareHooks();
        $this->clearSliderCache();
		return (bool)$res;
	}
	public function installDB()
	{
		$return = (bool)Db::getInstance()->execute('
			CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'st_product_categories_slider` (
				`id_st_product_categories_slider` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `type` tinyint(1) unsigned NOT NULL DEFAULT 1,
                `id_category` int(10) unsigned NOT NULL DEFAULT 0,
                `id_manufacturer` int(10) unsigned NOT NULL DEFAULT 0,
                `id_shop` int(10) unsigned NOT NULL, 
                `active` tinyint(1) unsigned NOT NULL DEFAULT 1, 
                `position` int(10) unsigned NOT NULL DEFAULT 0,
                `display_on` int(10) unsigned NOT NULL DEFAULT 1,
                `top_margin` varchar(10) DEFAULT NULL,
                `bottom_margin` varchar(10) DEFAULT NULL,
                `top_padding` varchar(10) DEFAULT NULL,
                `bottom_padding` varchar(10) DEFAULT NULL,
                `bg_pattern` tinyint(2) unsigned NOT NULL DEFAULT 0, 
                `bg_img` varchar(255) DEFAULT NULL,
                `bg_color` varchar(7) DEFAULT NULL,
                `speed` float(4,1) unsigned NOT NULL DEFAULT 0.1,
                `bg_img_v_offset` int(10) unsigned NOT NULL DEFAULT 0,
                `title_color` varchar(7) DEFAULT NULL,
                `title_hover_color` varchar(7) DEFAULT NULL,
                `text_color` varchar(7) DEFAULT NULL,
                `price_color` varchar(7) DEFAULT NULL,
                `grid_bg` varchar(7) DEFAULT NULL,
                `grid_hover_bg` varchar(7) DEFAULT NULL,
                `link_hover_color` varchar(7) DEFAULT NULL,
                `direction_color` varchar(7) DEFAULT NULL,
                `direction_color_hover` varchar(7) DEFAULT NULL,
                `direction_color_disabled` varchar(7) DEFAULT NULL,
                `direction_bg` varchar(7) DEFAULT NULL,
                `direction_hover_bg` varchar(7) DEFAULT NULL,
                `direction_disabled_bg` varchar(7) DEFAULT NULL,
                `title_align` tinyint(1) unsigned NOT NULL DEFAULT 0, 
                `title_font_size` int(10) unsigned NOT NULL DEFAULT 0, 
                `direction_nav` tinyint(1) unsigned NOT NULL DEFAULT 1,
                `hide_direction_nav_on_mob` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
                `control_nav` tinyint(1) unsigned NOT NULL DEFAULT 0,
                `hide_control_nav_on_mob` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                `control_bg` varchar(7) DEFAULT NULL,
                `control_bg_hover` varchar(7) DEFAULT NULL,
                `pag_nav_bg` varchar(7) DEFAULT NULL,
                `pag_nav_bg_hover` varchar(7) DEFAULT NULL,
                `title_bottom_border` varchar(10) DEFAULT NULL,
                `title_bottom_border_color` varchar(7) DEFAULT NULL,
                `title_bottom_border_color_h` varchar(7) DEFAULT NULL,
                
                `grid` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                `random` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                `nbr` TINYINT(3) unsigned NOT NULL DEFAULT 8, 
                `soby` TINYINT(3) unsigned NOT NULL DEFAULT 1, 
                `spacing_between` SMALLINT(3) UNSIGNED NOT NULL DEFAULT 16,
                `image_type` varchar(64) DEFAULT NULL,
                `slideshow` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1, 
                `s_speed` INT(10) UNSIGNED NOT NULL DEFAULT 7000,
                `a_speed` INT(10) UNSIGNED NOT NULL DEFAULT 400,
                `pause_on_hover` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                `rewind_nav` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                `lazy` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
                `move` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
                `hide_mob` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                `display_sd` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                `countdown_on` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
                `aw_display` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
                
                `display_pro_col` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                `nbr_col` TINYINT(3) UNSIGNED NOT NULL DEFAULT 6,
                `items_col` TINYINT(3) UNSIGNED NOT NULL DEFAULT 2,
                `soby_col` TINYINT(3) UNSIGNED NOT NULL DEFAULT 1,
                `slideshow_col` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
                `s_speed_col` INT(10) UNSIGNED NOT NULL DEFAULT 7000,
                `a_speed_col` INT(10) UNSIGNED NOT NULL DEFAULT 400,
                `pause_on_hover_col` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
                `rewind_nav_col` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                `lazy_col` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
                `move_col` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
                `hide_mob_col` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                `countdown_on_col` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
                `aw_display_col` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
                
                `nbr_fot` TINYINT(3) UNSIGNED NOT NULL DEFAULT 4,
                `soby_fot` TINYINT(3) UNSIGNED NOT NULL DEFAULT 1,
                `aw_display_fot` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
                `hide_mob_fot` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                `footer_wide` varchar(3) DEFAULT NULL,

                `pro_per_fw` tinyint(2) unsigned NOT NULL DEFAULT 0,
                `pro_per_xxl` tinyint(2) unsigned NOT NULL DEFAULT 5,  
                `pro_per_xl` tinyint(2) unsigned NOT NULL DEFAULT 4, 
                `pro_per_lg` tinyint(2) unsigned NOT NULL DEFAULT 4, 
                `pro_per_md` tinyint(2) unsigned NOT NULL DEFAULT 3, 
                `pro_per_sm` tinyint(2) unsigned NOT NULL DEFAULT 3, 
                `pro_per_xs` tinyint(2) unsigned NOT NULL DEFAULT 2, 

                `video_v_offset` int(10) unsigned NOT NULL DEFAULT 0,
                `video_poster` varchar(255) DEFAULT NULL,
                `video_mpfour` varchar(255) DEFAULT NULL,
                `video_webm` varchar(255) DEFAULT NULL,
                `video_ogg` varchar(255) DEFAULT NULL,
                `video_loop` tinyint(1) unsigned NOT NULL DEFAULT 1, 
                `video_muted` tinyint(1) unsigned NOT NULL DEFAULT 0,
                `view_more` tinyint(1) unsigned NOT NULL DEFAULT 0,  

				PRIMARY KEY (`id_st_product_categories_slider`)
			) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;');
		
		return $return;
	}
	public function uninstall()
	{
        $this->clearSliderCache();
		// Delete configuration
		return $this->uninstallDB() && parent::uninstall();
	}
	public function uninstallDB()
	{
		return Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'st_product_categories_slider`');
	}
    public function getContent()
	{
        $check_result = $this->_checkImageDir();
        $this->context->controller->addCSS($this->_path.'views/css/admin.css');
        $this->context->controller->addJS($this->_path.'views/js/admin.js');
		$id_st_product_categories_slider = (int)Tools::getValue('id_st_product_categories_slider');

        if(Tools::getValue('act')=='delete_slider_image' && $id = Tools::getValue('st_s_id'))
        {
            $result = array(
                'r' => false,
                'm' => '',
                'd' => ''
            );
            $k = Tools::getValue('st_s_k');
            
            $product_categories_slider = new StProductCategoriesSliderClass((int)$id);
            if(Validate::isLoadedObject($product_categories_slider))
            {
                $product_categories_slider->$k = '';
                if($product_categories_slider->save())
                {
                    $result['r'] = true;
                }
            }
            die(json_encode($result));
        }
        if(Tools::getValue('act')=='delete_image' && $field=Tools::getValue('field'))
        {
            return $this->AjaxDeleteImage($field);
        }
		if (isset($_POST['savestproductcategoriesslider']) || isset($_POST['savestproductcategoriessliderAndStay']))
		{
            $error = array();
            
			if ($id_st_product_categories_slider)
				$product_categories_slider = new StProductCategoriesSliderClass((int)$id_st_product_categories_slider);
			else
				$product_categories_slider = new StProductCategoriesSliderClass();
                
			$product_categories_slider->copyFromPost();
            $product_categories_slider->id_category = 0;
            $product_categories_slider->id_manufacturer = 0;
            $product_categories_slider->type = 0;
            if (!$type = Tools::getValue('type')) {
                $error[] = $this->displayError($this->getTranslator()->trans('The field "Category / brand" is required', array(), 'Modules.Stproductcategoriesslider.Admin'));
            } else {
                list($type, $id) = explode('_', $type);
                $product_categories_slider->type = (int)$type;
                switch($type) {
                    case 1:
                        $product_categories_slider->id_category = (int)$id;
                        break;
                    case 2:
                        $product_categories_slider->id_manufacturer = (int)$id;
                        break;
                }
            }
            
            $display_on = 0;
            foreach($this->_hooks as $v)
                $display_on += (int)Tools::getValue('display_on_'.$v['id']);
              
            if(!$display_on)
                $error[] = $this->displayError($this->getTranslator()->trans('The field "Display on" is required', array(), 'Modules.Stproductcategoriesslider.Admin'));
                
            $product_categories_slider->display_on = $display_on;
            $product_categories_slider->id_shop = (int)Shop::getContextShopID();

            if(Configuration::get($this->_prefix_st.'GRID')==1)
            {
                if(in_array($product_categories_slider->pro_per_fw, array(7,9,11)))
                    $product_categories_slider->pro_per_fw--;
                if(in_array($product_categories_slider->pro_per_xxl, array(7,9,11)))
                    $product_categories_slider->pro_per_xxl--;
                if(in_array($product_categories_slider->pro_per_xl, array(7,9,11)))
                    $product_categories_slider->pro_per_xl--;
                if(in_array($product_categories_slider->pro_per_lg, array(7,9,11)))
                    $product_categories_slider->pro_per_lg--;
                if(in_array($product_categories_slider->pro_per_md, array(7,9,11)))
                    $product_categories_slider->pro_per_md--;
                if(in_array($product_categories_slider->pro_per_sm, array(7,9,11)))
                    $product_categories_slider->pro_per_sm--;
                if(in_array($product_categories_slider->pro_per_xs, array(7,9,11)))
                    $product_categories_slider->pro_per_xs--;
            }
            
            $defaultLanguage = new Language((int)(Configuration::get('PS_LANG_DEFAULT')));

			if (!count($error) && $product_categories_slider->validateFields(false) && $product_categories_slider->validateFieldsLang(false))
            {
                /*position*/
                !$id_st_product_categories_slider && ($product_categories_slider->position = $product_categories_slider->checkPostion());
                
                $res = $this->stUploadImage('bg_img');
                if(count($res['error']))
                    $error = array_merge($error,$res['error']);
                elseif($res['image'])
                {
                    $product_categories_slider->bg_img = $res['image'];
                }

                $res = $this->stUploadImage('video_poster');
                if(count($res['error']))
                    $error = array_merge($error,$res['error']);
                elseif($res['image'])
                {
                    $product_categories_slider->video_poster = $res['image'];
                }
                
                if($product_categories_slider->save())
                {
                    $this->prepareHooks();
                    $this->clearSliderCache();
                    if(isset($_POST['savestproductcategoriessliderAndStay']))
                        Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&id_st_product_categories_slider='.$product_categories_slider->id.'&conf='.($id_st_product_categories_slider?4:3).'&update'.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'));    
                    else
                        $this->_html .= $this->displayConfirmation($this->getTranslator()->trans('Product categories slider', array(), 'Modules.Stproductcategoriesslider.Admin').' '.($id_st_product_categories_slider ? $this->getTranslator()->trans('updated', array(), 'Admin.Theme.Transformer') : $this->getTranslator()->trans('added', array(), 'Admin.Theme.Transformer')));
                        
                }
                else
                    $this->_html .= $this->displayError($this->getTranslator()->trans('An error occurred during Product slider', array(), 'Modules.Stproductcategoriesslider.Admin').' '.($id_st_product_categories_slider ? $this->getTranslator()->trans('updating', array(), 'Admin.Theme.Transformer') : $this->getTranslator()->trans('creation', array(), 'Admin.Theme.Transformer')));
            }
			else
				$this->_html .= count($error) ? implode('',$error) : $this->displayError($this->getTranslator()->trans('Invalid value for field(s).', array(), 'Admin.Theme.Transformer'));
		}
	    if (Tools::isSubmit('statusstproductcategoriesslider'))
        {
            $product_categories_slider = new StProductCategoriesSliderClass((int)$id_st_product_categories_slider);
            if($product_categories_slider->id && $product_categories_slider->toggleStatus())
            { 
                $this->clearSliderCache();
                Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'));
            }
            else
                $this->_html .= $this->displayError($this->getTranslator()->trans('An error occurred while updating the status.', array(), 'Admin.Theme.Transformer'));
        }
        if (Tools::isSubmit('way') && Tools::isSubmit('id_st_product_categories_slider') && (Tools::isSubmit('position')))
		{
		    $prduct_categories = new StProductCategoriesSliderClass((int)$id_st_product_categories_slider);
            if($prduct_categories->id && $prduct_categories->updatePosition((int)Tools::getValue('way'), (int)Tools::getValue('position')))
            {
                $this->clearSliderCache();
                Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'));    
            }
            else
                $this->_html .= $this->displayError($this->getTranslator()->trans('Failed to update the position.', array(), 'Admin.Theme.Transformer'));
		}
        if (Tools::getValue('action') == 'updatePositions')
        {
            $this->processUpdatePositions();
        }
        if (isset($_POST['savesliderform'])) {
            $this->saveForm();
        }
		if (Tools::isSubmit('updatestproductcategoriesslider') || Tools::isSubmit('addstproductcategoriesslider'))
		{
			$helper = $this->initForm();
            $this->_tabs = array(
                array('id'  => '0', 'name' => $this->getTranslator()->trans('General settings', array(), 'Admin.Theme.Transformer')),
                array('id'  => '1,5', 'name' => $this->getTranslator()->trans('Other settings', array(), 'Admin.Theme.Transformer')),
                array('id'  => '2', 'name' => $this->getTranslator()->trans('Homepage', array(), 'Admin.Theme.Transformer')),
                array('id'  => '3', 'name' => $this->getTranslator()->trans('Left or right column', array(), 'Admin.Theme.Transformer')),
                array('id'  => '4', 'name' => $this->getTranslator()->trans('Footer', array(), 'Admin.Theme.Transformer')),
            );
            $this->smarty->assign(array(
                'bo_tabs' => $this->_tabs,
                'bo_tab_content' => $helper->generateForm($this->fields_form),
            ));
    
            return $this->_html.$this->display(__FILE__, 'bo_tab_layout.tpl');
		}
        elseif (Tools::isSubmit('settingstproductcategoriesslider'))
		{
		    $this->initFieldsForm();
            $this->generateThumbnails();
			$helper = $this->initFormSetting();
            $this->_tabs = array(
                array('id'  => '0,1', 'name' => $this->getTranslator()->trans('Tab settings', array(), 'Admin.Theme.Transformer')),
            );
            $this->smarty->assign(array(
                'bo_tabs' => $this->_tabs,
                'bo_tab_content' => $helper->generateForm($this->fields_form),
            ));
    
            return $this->_html.$this->display(__FILE__, 'bo_tab_layout.tpl');
		}
		elseif (Tools::isSubmit('deletestproductcategoriesslider'))
		{
			$product_categories_slider = new StProductCategoriesSliderClass((int)$id_st_product_categories_slider);
			if ($product_categories_slider->id)
                $product_categories_slider->delete();
            $this->prepareHooks();
            $this->clearSliderCache();
                
			Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'));
		}
		else
		{
			$helper = $this->initList();
            $this->_html .= '<script type="text/javascript">var currentIndex="'.AdminController::$currentIndex.'&configure='.$this->name.'";</script>';
			return $this->_html.$helper->generateList(StProductCategoriesSliderClass::getListContent(), $this->fields_list);
		}
	}
    // Override parent method.
    public function getFormFieldsDefault()
    {
        return array();
    }
    protected function saveForm()
    {
        $this->_hooks = array();
        parent::saveForm();
        if(isset($_POST['savesliderformAndStay'])) {
            Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&conf=4&setting'.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules')); 
        }          
    }
    public static function getCategories()
    {
        $module = new StProductCategoriesSlider();
        $root_category = Category::getRootCategory();
        $category_arr = array();
        $module->getCategoryOption($category_arr,$root_category->id);
        return $category_arr;
    }
    private function getCategoryOption(&$category_arr,$id_category = 1, $id_lang = false, $id_shop = false, $recursive = true,$selected_id_category=0)
	{
		$id_lang = $id_lang ? (int)$id_lang : (int)Context::getContext()->language->id;
		$category = new Category((int)$id_category, (int)$id_lang, (int)$id_shop);

		if (is_null($category->id))
			return;

		if ($recursive)
		{
			$children = Category::getChildren((int)$id_category, (int)$id_lang, true, (int)$id_shop);
			$spacer = str_repeat('&nbsp;', $this->spacer_size * (int)$category->level_depth);
		}

		$shop = (object) Shop::getShop((int)$category->getShopID());
		$category_arr[] = array(
            'id' => $category->id,
            'name' => (isset($spacer) ? $spacer : '').$category->name.' ('.$shop->name.')',
        );
        
		if (isset($children) && count($children))
			foreach ($children as $child)
			{
				$this->getCategoryOption($category_arr,(int)$child['id_category'], (int)$id_lang, (int)$child['id_shop'],$recursive,$selected_id_category);
			}
	}
    public static function getSortBy()
    {
        $sort_by = $this->sort_by;
        if(Configuration::get('PS_CATALOG_MODE'))
            unset($sort_by['5'],$sort_by['6']);                
        return $sort_by;
    }
    public function createLinks()
    {
        $id_lang = $this->context->language->id;
        $category_arr = $this->getCategories();
		foreach ($category_arr as &$category) {
		  $category['id'] = '1_'.$category['id'];
		}
        $manufacturer_arr = array();
		$manufacturers = Manufacturer::getManufacturers(false, $id_lang);
		foreach ($manufacturers as $manufacturer)
            $manufacturer_arr[] = array('id'=>'2_'.$manufacturer['id_manufacturer'],'name'=>$manufacturer['name']);

        $links = array(
            array('name'=>$this->getTranslator()->trans('Category', array(), 'Admin.Theme.Transformer'),'query'=>$category_arr),
            array('name'=>$this->getTranslator()->trans('Manufacturer', array(), 'Admin.Theme.Transformer'),'query'=>$manufacturer_arr),
        );
        return $links;
    }
	protected function initForm()
	{
	    $this->fields_form = array();
        $fields = $this->getFormFields();
        $id_st_product_categories_slider = (int)Tools::getValue('id_st_product_categories_slider');
		$product_categories_slider = new StProductCategoriesSliderClass($id_st_product_categories_slider);
		$this->fields_form[0]['form'] = array(
			'legend' => array(
				'title' => $this->getTranslator()->trans('Product sldier', array(), 'Modules.Stproductcategoriesslider.Admin'),
                'icon' => 'icon-cogs'
			),
			'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->getTranslator()->trans('Category / Brand:', array(), 'Modules.Stproductcategoriesslider.Admin'),
                    'name' => 'type',
                    'class' => 'fixed-width-xxl',
                    'required' => true,
                    'options' => array(
                        'optiongroup' => array (
                            'query' => $this->createLinks(),
                            'label' => 'name'
                        ),
                        'options' => array (
                            'query' => 'query',
                            'id' => 'id',
                            'name' => 'name'
                        ),
                        'default' => array(
                            'value' => '',
                            'label' => $this->getTranslator()->trans('Select a category or brand', array(), 'Modules.Stproductcategoriesslider.Admin')
                        ),
                    )
                ),
				array(
					'type' => 'checkbox',
					'label' => $this->getTranslator()->trans('Display on:', array(), 'Admin.Theme.Transformer'),
					'name' => 'display_on',
                    'required' => true,
					'values' => array(
						'query' => $this->_hooks,
        				'id' => 'id',
        				'name' => 'name',
					),
				),
				array(
					'type' => 'switch',
					'label' => $this->getTranslator()->trans('Status:', array(), 'Admin.Theme.Transformer'),
					'name' => 'active',
					'is_bool' => true,
                    'default_value' => 1,
					'values' => array(
						array(
							'id' => 'active_on',
							'value' => 1,
							'label' => $this->getTranslator()->trans('Enabled', array(), 'Admin.Theme.Transformer')
						),
						array(
							'id' => 'active_off',
							'value' => 0,
							'label' => $this->getTranslator()->trans('Disabled', array(), 'Admin.Theme.Transformer')
						)
					),
				),
                array(
					'type' => 'text',
					'label' => $this->getTranslator()->trans('Position:', array(), 'Admin.Theme.Transformer'),
					'name' => 'position',
                    'default_value' => 0,
                    'class' => 'fixed-width-sm'                    
				),
                array(
					'type' => 'html',
                    'id' => 'a_cancel',
					'label' => '',
					'name' => '<a class="btn btn-default btn-block fixed-width-md" href="'.AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'"><i class="icon-arrow-left"></i> Back to list</a>',                  
				),
			),
            'buttons' => array(
                array(
                    'type' => 'submit',
                    'title'=> $this->getTranslator()->trans('Save', array(), 'Admin.Actions'),
                    'icon' => 'process-icon-save',
                    'class'=> 'pull-right'
                ),
            ),
			'submit' => array(
				'title' => $this->getTranslator()->trans('Save and stay', array(), 'Admin.Actions'),
                'stay' => true
			),
		);
        
        $this->fields_form[1]['form'] = array(
            'legend' => array(
                'title' => $this->getTranslator()->trans('Other settings', array(), 'Admin.Theme.Transformer'),
                'icon' => 'icon-cogs'                
            ),
            'input' => $fields['setting'],
            'buttons' => array(
                array(
                    'type' => 'submit',
                    'title'=> $this->getTranslator()->trans('Save', array(), 'Admin.Actions'),
                    'icon' => 'process-icon-save',
                    'class'=> 'pull-right'
                ),
            ),
            'submit' => array(
                'title' => $this->getTranslator()->trans('Save and stay', array(), 'Admin.Actions'),
                'stay' => true
            ),
        );
        if ($product_categories_slider->id) {
            $option = array(
                'spacing' => (int)$product_categories_slider->spacing_between,
                'per_lg'  => (int)$product_categories_slider->pro_per_lg,
                'per_xl'  => (int)$product_categories_slider->pro_per_xl,
                'per_xxl' => (int)$product_categories_slider->pro_per_xxl,
                'page'    => 'index',
            );
            $fields['home_slider']['image_type']['desc'] = $this->calcImageWidth($option);
        }
        $fields['home_slider']['countdown_on'] = array(
            'type' => 'switch',
            'label' => $this->getTranslator()->trans('Display countdown timers:', array(), 'Admin.Theme.Transformer'),
            'name' => 'countdown_on',
            'is_bool' => true,
            'default_value' => 1,
            'desc' => $this->getTranslator()->trans('Make sure the Coundown module is installed & enabled.', array(), 'Admin.Theme.Transformer'),
            'values' => array(
                array(
                    'id' => 'countdown_on_on',
                    'value' => 1,
                    'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Theme.Transformer')),
                array(
                    'id' => 'countdown_on_off',
                    'value' => 0,
                    'label' => $this->getTranslator()->trans('No', array(), 'Admin.Theme.Transformer')),
            ),
            'validation' => 'isBool',
        );
        $this->fields_form[2]['form'] = array(
            'legend' => array(
                'title' => $this->getTranslator()->trans('Slider on homepage', array(), 'Admin.Theme.Transformer'),
                'icon' => 'icon-cogs'
            ),
            'input' => $fields['home_slider'],
            'buttons' => array(
                array(
                    'type' => 'submit',
                    'title'=> $this->getTranslator()->trans('Save', array(), 'Admin.Actions'),
                    'icon' => 'process-icon-save',
                    'class'=> 'pull-right'
                ),
            ),
			'submit' => array(
				'title' => $this->getTranslator()->trans('Save and stay', array(), 'Admin.Actions'),
                'stay' => true
			),
		);
        $this->fields_form[2]['form']['input'][] = array(
			'type' => 'html',
            'id' => 'a_cancel',
			'label' => '',
			'name' => '<a class="btn btn-default btn-block fixed-width-md" href="'.AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'"><i class="icon-arrow-left"></i> Back to list</a>',                  
		);
        
        $fields['column']['countdown_on_col'] = array(
            'type' => 'switch',
            'label' => $this->getTranslator()->trans('Display countdown timers:', array(), 'Admin.Theme.Transformer'),
            'name' => 'countdown_on_col',
            'is_bool' => true,
            'default_value' => 1,
            'desc' => $this->getTranslator()->trans('Make sure the Coundown module is installed & enabled.', array(), 'Admin.Theme.Transformer'),
            'values' => array(
                array(
                    'id' => 'countdown_on_col_on',
                    'value' => 1,
                    'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Theme.Transformer')),
                array(
                    'id' => 'countdown_on_col_off',
                    'value' => 0,
                    'label' => $this->getTranslator()->trans('No', array(), 'Admin.Theme.Transformer')),
            ),
            'validation' => 'isBool',
        );
		$this->fields_form[3]['form'] = array(
			'legend' => array(
				'title' => $this->getTranslator()->trans('Slide on the left column/right column/X quarter', array(), 'Admin.Theme.Transformer'),
                'icon' => 'icon-cogs'
			),
			'input' => $fields['column'],
            'buttons' => array(
                array(
                    'type' => 'submit',
                    'title'=> $this->getTranslator()->trans('Save', array(), 'Admin.Actions'),
                    'icon' => 'process-icon-save',
                    'class'=> 'pull-right'
                ),
            ),
			'submit' => array(
				'title' => $this->getTranslator()->trans('Save and stay', array(), 'Admin.Actions'),
                'stay' => true
			),
		);
        $this->fields_form[3]['form']['input'][] = array(
			'type' => 'html',
            'id' => 'a_cancel',
			'label' => '',
			'name' => '<a class="btn btn-default btn-block fixed-width-md" href="'.AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'"><i class="icon-arrow-left"></i> Back to list</a>',                  
		);
        
        unset($fields['footer']['aw_display_fot']);
        
        $this->fields_form[4]['form'] = array(
			'legend' => array(
				'title' => $this->getTranslator()->trans('Footer', array(), 'Admin.Theme.Transformer'),
                'icon' => 'icon-cogs'
			),
			'input' => $fields['footer'],
			'submit' => array(
				'title' => $this->getTranslator()->trans('Save all', array(), 'Admin.Theme.Transformer')
			),
		);
        $this->fields_form[4]['form']['input'][] = array(
			'type' => 'html',
            'id' => 'a_cancel',
			'label' => '',
			'name' => '<a class="btn btn-default btn-block fixed-width-md" href="'.AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'"><i class="icon-arrow-left"></i> Back to list</a>',                  
		);
        
        $this->fields_form[5]['form'] = array(
            'legend' => array(
                'title' => $this->getTranslator()->trans('Video background', array(), 'Admin.Theme.Transformer'),
                'icon' => 'icon-cogs'                
            ),
            'description' => $this->getTranslator()->trans('Video background feature can not work on both Android and IOS devices, which is due to restrictions on autoplay and performance, so you also need to upload a video thumbnail, the thumbnail will be displayed on mobile devices.', array(), 'Admin.Theme.Transformer'),
            'input' => $fields['video'],
            'buttons' => array(
                array(
                    'type' => 'submit',
                    'title'=> $this->getTranslator()->trans('Save', array(), 'Admin.Actions'),
                    'icon' => 'process-icon-save',
                    'class'=> 'pull-right'
                ),
            ),
            'submit' => array(
                'title' => $this->getTranslator()->trans('Save and stay', array(), 'Admin.Actions'),
                'stay' => true
            ),
        );
        $this->fields_form[5]['form']['input'][] = array(
			'type' => 'html',
            'id' => 'a_cancel',
			'label' => '',
			'name' => '<a class="btn btn-default btn-block fixed-width-md" href="'.AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'"><i class="icon-arrow-left"></i> Back to list</a>',                  
		);
        
        if($product_categories_slider->id)
        {
            $this->fields_form[0]['form']['input'][] = array('type' => 'hidden', 'name' => 'id_st_product_categories_slider');

            if ($product_categories_slider->bg_img)
            {
                StProductCategoriesSliderClass::fetchMediaServer($product_categories_slider->bg_img);
                $this->fields_form[2]['form']['input']['bg_img']['desc'][] = '<div class="image_thumb_block"><img src="'.($product_categories_slider->bg_img).'" class="st_thumb_nail" /><div><a class="btn btn-default delete_slider_image" href="javascript:;" data-s_id="'.(int)$product_categories_slider->id.'" data-s_k="bg_img"><i class="icon-trash"></i>'.$this->getTranslator()->trans('Delete', array(), 'Admin.Theme.Transformer').'</a></div></div>';
            }
            if ($product_categories_slider->video_poster)
            {
                StProductCategoriesSliderClass::fetchMediaServer($product_categories_slider->video_poster);
                $this->fields_form[5]['form']['input']['video_poster']['desc'][] = '<div class="image_thumb_block"><img src="'.($product_categories_slider->video_poster).'" class="st_thumb_nail" /><div><a class="btn btn-default delete_slider_image" href="javascript:;" data-s_id="'.(int)$product_categories_slider->id.'" data-s_k="video_poster"><i class="icon-trash"></i> '.$this->getTranslator()->trans('Delete', array(), 'Admin.Theme.Transformer').'</a></div></div>';
            }
        }

        $helper = new HelperForm();
		$helper->show_toolbar = false;
        $helper->module = $this;
		$helper->table =  $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;

		$helper->identifier = $this->identifier;
		$helper->submit_action = 'savestproductcategoriesslider';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $this->getFieldsValueSt($product_categories_slider),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);       
        $name = $this->fields_form[2]['form']['input']['dropdownlistgroup']['name'];
        foreach ($this->fields_form[2]['form']['input']['dropdownlistgroup']['values']['medias'] as $v)
        {
            $dropdownlistgroup_key = $name.'_'.$v;
            $helper->tpl_vars['fields_value'][$dropdownlistgroup_key] = $product_categories_slider->id ? $product_categories_slider->$dropdownlistgroup_key : $this->dropdownlistgroup_default[$dropdownlistgroup_key];
        }
        
        if ($product_categories_slider->id) {
            $helper->tpl_vars['fields_value']['type'] = $product_categories_slider->type.'_'.($product_categories_slider->id_category ? $product_categories_slider->id_category : $product_categories_slider->id_manufacturer);
        }

        foreach($this->_hooks as $v)
            $helper->tpl_vars['fields_value']['display_on_'.$v['id']] = (int)$v['val']&(int)$product_categories_slider->display_on; 

		return $helper;
	}
    
    public function initFieldsForm()
    {
        $this->fields_form = array();
        
        if (!Tools::isSubmit('settingstproductcategoriesslider') && !isset($_POST['savesliderform'])) {
            return;
        }
        
        $this->fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->getTranslator()->trans('Tab setting', array(), 'Admin.Theme.Transformer'),
                'icon' => 'icon-cogs'
            ),
            'description' => $this->getTranslator()->trans('These settings will take effect if you have the above "Tab" option enabled.', array(), 'Admin.Theme.Transformer'),
            'input' => array(
                array(
                    'type' => 'switch',
                    'label' => $this->getTranslator()->trans('Tab:', array(), 'Admin.Theme.Transformer'),
                    'name' => 'tabs',
                    'is_bool' => true,
                    'default_value' => 1,
                    'values' => array(
                        array(
                            'id' => 'tabs_on',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Theme.Transformer')),
                        array(
                            'id' => 'tabs_off',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('No', array(), 'Admin.Theme.Transformer')),
                    ),
                    'validation' => 'isBool',
                ), 
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Top padding:', array(), 'Admin.Theme.Transformer'),
                    'name' => 'top_padding',
                    'validation' => 'isNullOrUnsignedId',
                    'prefix' => 'px',
                    'class' => 'fixed-width-lg',
                    'desc' => $this->getTranslator()->trans('Leave it empty to use the default value.', array(), 'Admin.Theme.Transformer'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Bottom padding:', array(), 'Admin.Theme.Transformer'),
                    'name' => 'bottom_padding',
                    'validation' => 'isNullOrUnsignedId',
                    'prefix' => 'px',
                    'class' => 'fixed-width-lg',
                    'desc' => $this->getTranslator()->trans('Leave it empty to use the default value.', array(), 'Admin.Theme.Transformer'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Top spacing:', array(), 'Admin.Theme.Transformer'),
                    'name' => 'top_margin',
                    'validation' => 'isNullOrUnsignedId',
                    'prefix' => 'px',
                    'class' => 'fixed-width-lg',
                    'desc' => $this->getTranslator()->trans('Leave it empty to use the default value.', array(), 'Admin.Theme.Transformer'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Bottom spacing:', array(), 'Admin.Theme.Transformer'),
                    'name' => 'bottom_margin',
                    'validation' => 'isNullOrUnsignedId',
                    'prefix' => 'px',
                    'class' => 'fixed-width-lg',
                    'desc' => $this->getTranslator()->trans('Leave it empty to use the default value.', array(), 'Admin.Theme.Transformer'),
                ),
                 array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Background color:', array(), 'Admin.Theme.Transformer'),
                    'name' => 'bg_color',
                    'class' => 'color',
                    'size' => 20,
                    'validation' => 'isColor',
                 ),
                array(
                    'type' => 'select',
                    'label' => $this->getTranslator()->trans('Select a pattern number:', array(), 'Admin.Theme.Transformer'),
                    'name' => 'bg_pattern',
                    'options' => array(
                        'query' => $this->getPatternsArray(),
                        'id' => 'id',
                        'name' => 'name',
                        'default' => array(
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('None', array(), 'Admin.Theme.Transformer'),
                        ),
                    ),
                    'desc' => $this->getPatterns(),
                    'validation' => 'isUnsignedInt',
                ),
                'bg_img' => array(
                    'type' => 'file',
                    'label' => $this->getTranslator()->trans('Upload your own pattern or background image:', array(), 'Admin.Theme.Transformer'),
                    'name' => 'bg_img',
                    'desc' => '',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Parallax speed factor:', array(), 'Admin.Theme.Transformer'),
                    'name' => 'speed',
                    'default_value' => 0.6,
                    'desc' => $this->getTranslator()->trans('Speed to move relative to vertical scroll. Example: 0.1 is one tenth the speed of scrolling, 2 is twice the speed of scrolling.', array(), 'Admin.Theme.Transformer'),
                    'validation' => 'isFloat',
                    'class' => 'fixed-width-sm'
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->getTranslator()->trans('Tab heading alignment:', array(), 'Modules.Stproductcategoriesslider.Admin'),
                    'name' => 'title_align',
                    'default_value' => 0,
                    'values' => array(
                        array(
                            'id' => 'title_align_left',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('Left', array(), 'Admin.Theme.Transformer')),
                        array(
                            'id' => 'title_align_center',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Center', array(), 'Admin.Theme.Transformer')),
                    ),
                    'validation' => 'isUnsignedInt',
                ),
                array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Tab color:', array(), 'Modules.Stproductcategoriesslider.Admin'),
                    'name' => 'header_color',
                    'size' => 33,
                    'default_value' => '',
                    'validation' => 'isColor',
                ),
                array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Active tab color:', array(), 'Modules.Stproductcategoriesslider.Admin'),
                    'name' => 'header_hover_color',
                    'size' => 33,
                    'default_value' => '',
                    'validation' => 'isColor',
                ),
                array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Tab background:', array(), 'Modules.Stproductcategoriesslider.Admin'),
                    'name' => 'header_bg',
                    'size' => 33,
                    'default_value' => '',
                    'validation' => 'isColor',
                ),
                array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Active tab background:', array(), 'Modules.Stproductcategoriesslider.Admin'),
                    'name' => 'header_hover_bg',
                    'size' => 33,
                    'default_value' => '',
                    'validation' => 'isColor',
                ),
                array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Border color:', array(), 'Admin.Theme.Transformer'),
                    'name' => 'header_border',
                    'size' => 33,
                    'default_value' => '',
                    'validation' => 'isColor',
                ),
                array(
                    'type' => 'color',
                    'label' => $this->getTranslator()->trans('Border highlight color:', array(), 'Modules.Stproductcategoriesslider.Admin'),
                    'name' => 'header_active_border',
                    'size' => 33,
                    'default_value' => '',
                    'validation' => 'isColor',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Border size:', array(), 'Admin.Theme.Transformer'),
                    'name' => 'tab_bottom_border',
                    'prefix' => 'px',
                    'class' => 'fixed-width-lg',
                    'default_value' => '',
                    'validation' => 'isNullOrUnsignedId',
                    'desc' => $this->getTranslator()->trans('Set it empty to use the default value.', array(), 'Admin.Theme.Transformer'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Font size:', array(), 'Admin.Theme.Transformer'),
                    'name' => 'tab_font_size',
                    'prefix' => 'px',
                    'class' => 'fixed-width-lg',
                    'default_value' => '',
                    'validation' => 'isNullOrUnsignedId',
                    'desc' => $this->getTranslator()->trans('Set it empty to use the default value.', array(), 'Admin.Theme.Transformer'),
                ),
                array(
                    'type' => 'html',
                    'id' => 'a_cancel',
                    'label' => '',
                    'name' => '<a class="btn btn-default btn-block fixed-width-md" href="'.AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'"><i class="icon-arrow-left"></i> Back to list</a>',                  
                ),
            ),
            'buttons' => array(
                array(
                    'type' => 'submit',
                    'title'=> $this->getTranslator()->trans('Save', array(), 'Admin.Actions'),
                    'icon' => 'process-icon-save',
                    'class'=> 'pull-right'
                ),
            ),
            'submit' => array(
                'title' => $this->getTranslator()->trans('Save and stay', array(), 'Admin.Actions'),
                'stay' => true
            ),
        );
        
        $this->fields_form[1]['form'] = array(
            'legend' => array(
                'title' => $this->getTranslator()->trans('General', array(), 'Admin.Theme.Transformer'),
                'icon' => 'icon-cogs'
            ),
            'input' => array(
                array(
                    'type' => 'switch',
                    'label' => $this->getTranslator()->trans('Show products on your homepage randomly:', array(), 'Admin.Theme.Transformer'),
                    'name' => 'random',
                    'is_bool' => true,
                    'default_value' => 0,
                    'values' => array(
                        array(
                            'id' => 'random_on',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Theme.Transformer')),
                        array(
                            'id' => 'random_off',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('No', array(), 'Admin.Theme.Transformer')),
                    ),
                    'validation' => 'isBool',
                ), 
                array(
                    'type' => 'html',
                    'id' => 'a_cancel',
                    'label' => '',
                    'name' => '<a class="btn btn-default btn-block fixed-width-md" href="'.AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'"><i class="icon-arrow-left"></i> Back to list</a>',                  
                ),
            ),
            'buttons' => array(
                array(
                    'type' => 'submit',
                    'title'=> $this->getTranslator()->trans('Save', array(), 'Admin.Actions'),
                    'icon' => 'process-icon-save',
                    'class'=> 'pull-right'
                ),
            ),
            'submit' => array(
                'title' => $this->getTranslator()->trans('Save and stay', array(), 'Admin.Actions'),
                'stay' => true
            ),
        );
    }
    protected function initFormSetting()
	{
	    $helper = new HelperForm();
		$helper->show_toolbar = false;
        $helper->module = $this;
		$helper->table =  $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;

		$helper->identifier = $this->identifier;
		$helper->submit_action = 'savesliderform';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);
		return $helper;
	}
    public static function displayManufacturer($value, $row=array())
	{
        if(!$value)
            return '--';
            return Manufacturer::getNameById((int)$value);
		return '';
	}
    public static function displayCategory($value, $row=array())
	{
        if(!$value)
            return '--';
        $id_lang = (int)Context::getContext()->language->id;
        $category = new Category((int)$value,$id_lang);
        if($category->id)
            return $category->name;
		return '';
	}
    public static function displayShowOn($value, $row)
    {
        $html = '<ul>';
        $value = (int)$value;
        $module = new StProductCategoriesSlider;
        foreach($module->_hooks AS $_value) {
            if ($_value['val']&$value) {
                $html .= '<li>'.$_value['name'].'</li>';
            }
        }
        return $html.'</ul>';
    }
	protected function initList()
	{
	    // Fix table drag bug.
        Media::addJsDef(array(
            'currentIndex' => AdminController::$currentIndex.'&configure='.$this->name,
        ));
		$this->fields_list = array(
			'id_st_product_categories_slider' => array(
				'title' => $this->getTranslator()->trans('Id', array(), 'Admin.Theme.Transformer'),
				'width' => 120,
				'type' => 'text',
                'search' => false,
                'orderby' => false
			),
			'id_category' => array(
				'title' => $this->getTranslator()->trans('Category', array(), 'Admin.Theme.Transformer'),
				'width' => 140,
				'type' => 'text',
				'callback' => 'displayCategory',
				'callback_object' => 'StProductCategoriesSlider',
                'search' => false,
                'orderby' => false
			),
            'id_manufacturer' => array(
				'title' => $this->getTranslator()->trans('Manufacturer', array(), 'Admin.Theme.Transformer'),
				'width' => 140,
				'type' => 'text',
				'callback' => 'displayManufacturer',
				'callback_object' => 'StProductCategoriesSlider',
                'search' => false,
                'orderby' => false
			),
            'display_on' => array(
				'title' => $this->getTranslator()->trans('Show on', array(), 'Admin.Theme.Transformer'),
				'width' => 140,
				'type' => 'text',
				'callback' => 'displayShowOn',
				'callback_object' => 'StProductCategoriesSlider',
                'search' => false,
                'orderby' => false
			),
            'position' => array(
				'title' => $this->getTranslator()->trans('Position', array(), 'Admin.Theme.Transformer'),
				'width' => 40,
				'position' => 'position',
				'align' => 'left',
                'search' => false,
                'orderby' => false
            ),
            'active' => array(
				'title' => $this->getTranslator()->trans('Status', array(), 'Admin.Theme.Transformer'),
				'align' => 'center',
				'active' => 'status',
				'type' => 'bool',
				'width' => 25,
                'search' => false,
                'orderby' => false
            ),
		);

		if (Shop::isFeatureActive())
			$this->fields_list['id_shop'] = array(
                'title' => $this->getTranslator()->trans('ID Shop', array(), 'Admin.Theme.Transformer'), 
                'align' => 'center', 
                'width' => 25, 
                'type' => 'int',
                'search' => false,
                'orderby' => false
                );

		$helper = new HelperList();
		$helper->shopLinkType = '';
		$helper->simple_header = false;
		$helper->identifier = 'id_st_product_categories_slider';
		$helper->actions = array('edit', 'delete');
		$helper->show_toolbar = true;
		$helper->toolbar_btn['new'] =  array(
			'href' => AdminController::$currentIndex.'&configure='.$this->name.'&add'.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
			'desc' => $this->getTranslator()->trans('Add new', array(), 'Admin.Theme.Transformer')
		);
		$helper->toolbar_btn['edit'] =  array(
			'href' => AdminController::$currentIndex.'&configure='.$this->name.'&setting'.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules'),
			'desc' => $this->getTranslator()->trans('Setting', array(), 'Admin.Theme.Transformer'),
		);
		$helper->title = $this->displayName;
		$helper->table = $this->name;
		//$helper->orderBy = 'position';
		//$helper->orderWay = 'ASC';
	    //$helper->position_identifier = 'id_st_product_categories_slider';
        
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
		return $helper;
	}
    public function hookDisplayCategoryHeader($params)
    {
		return $this->hookDisplayHome($params, __FUNCTION__);
    }
    public function hookDisplayCategoryFooter($params)
    {
		return $this->hookDisplayHome($params, __FUNCTION__);
    }
	public function hookDisplayHome($params, $func=0, $flag=0)
	{
        $id_st_product_categories_slider = isset($params['id_st_product_categories_slider']) ? $params['id_st_product_categories_slider'] : null;
        $display_on = !is_null($id_st_product_categories_slider) ?  $id_st_product_categories_slider : $this->getDisplayOn($func ? $func : __FUNCTION__);
        if (!$display_on) {
            return false;
        }
        $st_time = isset($params['st_time']) ? $params['st_time'] : "";
        $random = Configuration::get($this->_prefix_st.'RANDOM');
        if(Configuration::get($this->_prefix_st.'TABS'))
        {
            $this->smarty->assign(array(
                'has_background_img'    => ((int)Configuration::get($this->_prefix_st.'BG_PATTERN') || Configuration::get($this->_prefix_st.'BG_IMG')) ? 1 : 0,
                'speed'                 => (float)Configuration::get($this->_prefix_st.'SPEED'),
            ));
            
            if($random)
            {
                if(!$this->_prepareHook('', $display_on, !is_null($id_st_product_categories_slider)))
                    return false;
                $this->smarty->assign(array(
                    'column_slider'         => false,
                    'homeverybottom'         => ($flag==2 ? true : false),
                ));
                return $this->display(__FILE__, 'stproductcategoriesslider_tab.tpl');
            }
            else
            {
                if (!$this->isCached($this->templatePath.'stproductcategoriesslider_tab.tpl', $this->stGetCacheId($display_on.$st_time.'-tab')))
            	{
                    $this->_prepareHook('', $display_on, !is_null($id_st_product_categories_slider));
                    $this->smarty->assign(array(
                        'column_slider'         => false,
                        'homeverybottom'         => ($flag==2 ? true : false),
                    ));
                }
        		return $this->fetch($this->templatePath.'stproductcategoriesslider_tab.tpl', $this->stGetCacheId($display_on.$st_time.'-tab'));
            }
            return false;
        }
        else
        {
            if($random)
            {
                if(!$this->_prepareHook('', $display_on, !is_null($id_st_product_categories_slider)))
                    return false;
                $this->smarty->assign(array(
                    'column_slider'         => false,
                    'homeverybottom'         => ($flag==2 ? true : false),
                ));
                return $this->display(__FILE__, 'stproductcategoriesslider.tpl');
            }
            else
            {
                if(!$this->isCached($this->templatePath.'stproductcategoriesslider.tpl', $this->stGetCacheId($display_on.$st_time)))
                {
                    $this->_prepareHook('', $display_on, !is_null($id_st_product_categories_slider));
                    $this->smarty->assign(array(
                        'column_slider'         => false,
                        'homeverybottom'         => ($flag==2 ? true : false),
                    ));
                }
                return $this->fetch($this->templatePath.'stproductcategoriesslider.tpl', $this->stGetCacheId($display_on.$st_time));
            }
            return false;
        }
	}    
	public function hookDisplayLeftColumn($params, $func='')
	{
	    $display_on = $this->getDisplayOn($func ? $func : __FUNCTION__);
        if (!$display_on) {
            return false;
        }
	    if (!$this->isCached($this->templatePath.'stproductcategoriesslider.tpl', $this->stGetCacheId($display_on)))
    	{
            $this->_prepareHook('col', $display_on);
            $this->smarty->assign(array(
                'column_slider'=> true,
            ));
        }
		return $this->fetch($this->templatePath.'stproductcategoriesslider.tpl', $this->stGetCacheId($display_on));
	}
    private function _prepareHook($ext='',$display_on=0,$display_on_is_id=false)
    {        
        $ext = $ext ? '_'.strtolower($ext) : '';
        if (!$display_on) {
            return false;
        }
        $key = $display_on.($display_on_is_id ? 'id' : '');
        if (isset(self::$cache_product_categories[$key]) && self::$cache_product_categories[$key])
            $result = self::$cache_product_categories[$key];
        else
        {
            $result = StProductCategoriesSliderClass::getListContent(1,$display_on,$display_on_is_id);
            if(is_array($result) && count($result)) {
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
                    
        	    $random = Configuration::get($this->_prefix_st.'RANDOM');
                foreach($result as &$v)
                {
                    if ($v['type'] == 1) {
                        $order_by = $random ? null : (isset($this->sort_by[$v['soby'.$ext]]) ? $this->sort_by[$v['soby'.$ext]]['orderBy'] : 'position');
                        $order_way = $random ? null : (isset($this->sort_by[$v['soby'.$ext]]) ? $this->sort_by[$v['soby'.$ext]]['orderWay'] : 'ASC');
                        $category = new Category((int)$v['id_category'], (int)$this->context->language->id);
                        $products = $category->getProducts($this->context->language->id, 1, (int)$v['nbr'.$ext], $order_by, $order_way, false, true, (bool)$random, (int)$v['nbr'.$ext]);
                        $v['link'] = $category->getLink();
                        $v['name' ] = $category->name;
                        $v['description' ] = $category->description;    
                    } elseif ($v['type'] == 2) {
                        $order_by = isset($this->sort_by[$v['soby'.$ext]]) ? $this->sort_by[$v['soby'.$ext]]['orderBy'] : 'position';
                        $order_way = isset($this->sort_by[$v['soby'.$ext]]) ? $this->sort_by[$v['soby'.$ext]]['orderWay'] : 'ASC';
                        $products = Manufacturer::getProducts((int)$v['id_manufacturer'], (int)$this->context->language->id, 1, (int)$v['nbr'.$ext], $order_by, $order_way);
                        $manufacturer = new Manufacturer((int)$v['id_manufacturer'], (int)$this->context->language->id);
                        $v['link'] = $this->context->link->getManufacturerLink($manufacturer, null, (int)$this->context->language->id);
                        $v['name' ] = $manufacturer->name;
                        $v['description' ] = $manufacturer->description;
                    } else {
                        continue;
                    }
                    if ($v['video_poster']) {
                        $this->fetchMediaServer($v['video_poster']);
                    }
                    
                    if(is_array($products) && count($products))
                    {
                        foreach($products as &$product)
                        {
                            $product = $presenter->present(
                                $presentationSettings,
                                $assembler->assembleProduct($product),
                                $this->context->language
                            );
                        }
                    }
                    $v['products'] = $products;
                }    
            }
            self::$cache_product_categories[$key] = $result;
        }
        $this->smarty->assign(array(
            'product_categories'      => $result,
            'add_prod_display'        => Configuration::get('PS_ATTRIBUTE_CATEGORY_DISPLAY'),
            'title_align'             => Configuration::get($this->_prefix_st.'TITLE_ALIGN'),
            'hook_hash'               => $key,
        ));
        return true;
    }
    
    public function hookDisplayHeader($params)
    {
        if (!$this->isCached($this->templatePath.'header.tpl', $this->getCacheId()))
        {
            $custom_css = '';

            if(Configuration::get('ST_PRO_CATE_TABS'))
            {

                $group_css = '';
                if ($bg_color = Configuration::get($this->_prefix_st.'BG_COLOR'))
                    $group_css .= 'background-color:'.$bg_color.';';
                if ($bg_img = Configuration::get($this->_prefix_st.'BG_IMG'))
                {
                    $this->fetchMediaServer($bg_img);
                    $group_css .= 'background-image: url('.$bg_img.');';
                }
                elseif ($bg_pattern = Configuration::get($this->_prefix_st.'BG_PATTERN'))
                {
                    $img = _MODULE_DIR_.'stthemeeditor/patterns/'.$bg_pattern.'.png';
                    $img = $this->context->link->protocol_content.Tools::getMediaServer($img).$img;
                    $group_css .= 'background-image: url('.$img.');';
                }
                if($group_css)
                    $custom_css .= '.pc_slider_block_container{background-attachment:fixed;'.$group_css.'}';

                if ($top_padding = (int)Configuration::get($this->_prefix_st.'TOP_PADDING'))
                    $custom_css .= '.pc_slider_block_container{padding-top:'.$top_padding.'px;}';
                if ($bottom_padding = (int)Configuration::get($this->_prefix_st.'BOTTOM_PADDING'))
                    $custom_css .= '.pc_slider_block_container{padding-bottom:'.$bottom_padding.'px;}';

                $top_margin = Configuration::get($this->_prefix_st.'TOP_MARGIN');
                if($top_margin || $top_margin===0 || $top_margin==='0')
                    $custom_css .= '.pc_slider_block_container{margin-top:'.$top_margin.'px;}';
                $bottom_margin = Configuration::get($this->_prefix_st.'BOTTOM_MARGIN');
                if($bottom_margin || $bottom_margin===0 || $bottom_margin==='0')
                    $custom_css .= '.pc_slider_block_container{margin-bottom:'.$bottom_margin.'px;}';

                if($header_color  = Configuration::get($this->_prefix_st.'HEADER_COLOR'))
                    $custom_css .= '.pc_slider_block_container .nav-tabs .nav-link{color: '.$header_color.';}';
                if($header_hover_color    = Configuration::get($this->_prefix_st.'HEADER_HOVER_COLOR'))
                    $custom_css .= '.pc_slider_block_container .nav-tabs .nav-link:hover, .pc_slider_block_container .nav-tabs .nav-link.active{color: '.$header_hover_color.';}';
                if($header_bg = Configuration::get($this->_prefix_st.'HEADER_BG'))
                    $custom_css .= '.pc_slider_block_container .nav-tabs .nav-link{background-color: '.$header_bg.';}';
                if($header_hover_bg   = Configuration::get($this->_prefix_st.'HEADER_HOVER_BG'))
                    $custom_css .= '.pc_slider_block_container .nav-tabs .nav-link:hover, .pc_slider_block_container .nav-tabs .nav-link.active{background-color: '.$header_hover_bg.';}';
                if($header_border  = Configuration::get($this->_prefix_st.'HEADER_BORDER'))
                {
                    // $custom_css .= '.pc_slider_block_container .sttab_2_2 .nav-tabs{border-bottom-color: '.$header_border.';}';
                    $custom_css .= '.pc_slider_block_container .sttab_2_3 .nav-tabs{border-bottom-color: '.$header_border.';}';
                    $custom_css .= '.pc_slider_block_container .sttab_2_3 .nav-link{border-bottom-color: '.$header_border.';}';
                }
                if($header_active_border  = Configuration::get($this->_prefix_st.'HEADER_ACTIVE_BORDER'))
                {
                    // $custom_css .= '.pc_slider_block_container .sttab_2_2 .nav-link:hover, .pc_slider_block_container .sttab_2_2 .nav-link.active, .pc_slider_block_container .sttab_2_2 .nav-link:focus{border-top-color: '.$header_active_border.';}';
                    $custom_css .= '.pc_slider_block_container .sttab_2_3 .nav-link:hover, .pc_slider_block_container .sttab_2_3 .nav-link.active, .pc_slider_block_container .sttab_2_3 .nav-link:focus{border-bottom-color: '.$header_active_border.';}';
                }
                $tab_bottom_border = Configuration::get($this->_prefix_st.'TAB_BOTTOM_BORDER');
                if($tab_bottom_border || $tab_bottom_border===0 || $tab_bottom_border==='0')
                {
                    $custom_css .= '.pc_slider_block_container .sttab_2_3 .nav-tabs, .pc_slider_block_container .sttab_2_3 .nav-tabs .nav-link{border-bottom-width: '.$tab_bottom_border.'px;border-bottom-style: solid;}';
                    $custom_css .= '.pc_slider_block_container .sttab_2_3 .nav-tabs .nav-item{margin-bottom: -'.$tab_bottom_border.'px;}';
                }
                if($tab_font_size = Configuration::get($this->_prefix_st.'TAB_FONT_SIZE'))
                    $custom_css .= '.pc_slider_block_container .nav-tabs .nav-link{font-size: '.$tab_font_size.'px;}';
            }
            
                $custom_css_arr = StProductCategoriesSliderClass::getOptions();
                if (is_array($custom_css_arr) && count($custom_css_arr)) {
                    foreach ($custom_css_arr as $v) {
                        /*$full_width = false;
                        foreach($this->_hooks AS $value)
                            if ($value['val'] == $v['display_on'] && isset($v['full_width']) && $v['full_width'])
                                $full_width = true;*/

                        $prefix = '#category_products_container_'.$v['id_st_product_categories_slider'];

            if($v['grid']==1)
            {
                $custom_css .= $prefix.' .product_list.grid .product_list_item{padding-left:'.ceil($v['spacing_between']/2).'px;padding-right:'.floor($v['spacing_between']/2).'px;}';
                $custom_css .= $prefix.' .product_list.grid{margin-left:-'.ceil($v['spacing_between']/2).'px;margin-right:-'.floor($v['spacing_between']/2).'px;}';
            }
            
            $group_css = '';
            if ($v['bg_color'])
                $group_css .= 'background-color:'.$v['bg_color'].';';
            if ($v['bg_img'])
            {
                $this->fetchMediaServer($v['bg_img']);
                $group_css .= 'background-image: url('.$v['bg_img'].');';
            }
            elseif ($v['bg_pattern'])
            {
                $img = _MODULE_DIR_.'stthemeeditor/patterns/'.$v['bg_pattern'].'.png';
                $img = $this->context->link->protocol_content.Tools::getMediaServer($img).$img;
                $group_css .= 'background-image: url('.$img.');background-repeat: repeat;';
            }
            if ($v['bg_img_v_offset']) {
                $custom_css .= $prefix.'.products_container{background-position:center -'.$v['bg_img_v_offset'].'px;}';
            }
            if($group_css)
                $custom_css .= $prefix.'.products_container{'.$group_css.'}';

            if ($v['top_padding'])
                $custom_css .= $prefix.'.products_container{padding-top:'.$v['top_padding'].'px;}';
            if ($v['bottom_padding'])
                $custom_css .= $prefix.'.products_container{padding-bottom:'.$v['bottom_padding'].'px;}';

            if($v['top_margin'] || $v['top_margin']===0 || $v['top_margin']==='0')
                $custom_css .= $prefix.'.products_container{margin-top:'.$v['top_margin'].'px;}';
            if($v['bottom_margin'] || $v['bottom_margin']===0 || $v['bottom_margin']==='0')
                $custom_css .= $prefix.'.products_container{margin-bottom:'.$v['bottom_margin'].'px;}';

            if ($v['title_font_size'])
                 $custom_css .= $prefix.'.products_container .title_block_inner{font-size:'.$v['title_font_size'].'px;}';

            if ($v['title_color'])
                $custom_css .= $prefix.'.products_container .title_block_inner{color:'.$v['title_color'].';}';
            if ($v['title_hover_color'])
                $custom_css .= $prefix.'.products_container .title_block_inner:hover{color:'.$v['title_hover_color'].';}';


            if($v['title_bottom_border'] || $v['title_bottom_border']===0 || $v['title_bottom_border']==='0')
            {
                $custom_css .= $prefix.'.products_container .title_style_0,'.$prefix.'.products_container .title_style_0 .title_block_inner{border-bottom-width:'.$v['title_bottom_border'].'px;}'.$prefix.'.products_container .title_style_0 .title_block_inner{margin-bottom:'.$v['title_bottom_border'].'px;}';
                $custom_css .= $prefix.'.products_container .title_style_1 .flex_child, '.$prefix.'.products_container .title_style_3 .flex_child{border-bottom-width:'.$v['title_bottom_border'].'px;}';
                $custom_css .= $prefix.'.products_container .title_style_2 .flex_child{border-bottom-width:'.$v['title_bottom_border'].'px;border-top-width:'.$v['title_bottom_border'].'px;}';
            }
            
            if($v['title_bottom_border_color'])
                $custom_css .=$prefix.'.products_container .title_style_0, '.$prefix.'.products_container .title_style_1 .flex_child, '.$prefix.'.products_container .title_style_2 .flex_child, '.$prefix.'.products_container .title_style_3 .flex_child{border-bottom-color: '.$v['title_bottom_border_color'].';}'.$prefix.'.products_container .title_style_2 .flex_child{border-top-color: '.$v['title_bottom_border_color'].';}';  
            if($v['title_bottom_border_color_h'])
                $custom_css .=$prefix.'.products_container .title_style_0 .title_block_inner{border-color: '.$v['title_bottom_border_color_h'].';}';

            
            if ($v['text_color'])
                $custom_css .= $prefix.' .ajax_block_product .s_title_block a,
                '.$prefix.' .ajax_block_product .old_price,
                '.$prefix.' .ajax_block_product .product_desc{color:'.$v['text_color'].';}';

            if ($v['price_color'])
                $custom_css .= $prefix.' .ajax_block_product .price{color:'.$v['price_color'].';}';
            if ($v['link_hover_color'])
                $custom_css .= $prefix.' .ajax_block_product .s_title_block a:hover{color:'.$v['link_hover_color'].';}';

            if ($v['grid_bg'])
                $custom_css .= $prefix.' .pro_outer_box .pro_second_box{background-color:'.$v['grid_bg'].';}';
            if ($v['grid_hover_bg'])
                $custom_css .= $prefix.' .pro_outer_box:hover .pro_second_box{background-color:'.$v['grid_hover_bg'].';}';

            if ($v['direction_color'])
                $custom_css .= $prefix.'.block .products_slider .swiper-button{color:'.$v['direction_color'].';}';
            if ($v['direction_color_hover'])
                $custom_css .= $prefix.'.block .products_slider .swiper-button:hover{color:'.$v['direction_color_hover'].';}';
            if ($v['direction_color_disabled'])
                $custom_css .= $prefix.'.block .products_slider .swiper-button.swiper-button-disabled, '.$prefix.'.block .products_slider .swiper-button.swiper-button-disabled:hover{color:'.$v['direction_color_disabled'].';}';
            
            if ($v['direction_bg'])
                $custom_css .= $prefix.'.block .products_slider .swiper-button{background-color:'.$v['direction_bg'].';}';
            if ($v['direction_hover_bg'])
                $custom_css .= $prefix.'.block .products_slider .swiper-button:hover{background-color:'.$v['direction_hover_bg'].';}';
            if ($v['direction_disabled_bg'])
                $custom_css .= $prefix.'.block .products_slider .swiper-button.swiper-button-disabled, '.$prefix.'.block .products_slider .swiper-button.swiper-button-disabled:hover{background-color:'.$v['direction_disabled_bg'].';}';
            /*else
                $custom_css .= $prefix.' .products_slider .swiper-button.swiper-button-disabled, '.$prefix.' .products_slider .swiper-button.swiper-button-disabled:hover{background-color:transparent;}';*/
            if($v['pag_nav_bg']) {
                $custom_css .= $prefix.' .swiper-pagination-bullet{background-color:'.$v['pag_nav_bg'].';}';
            }
            if($v['pag_nav_bg_hover']) {
                $custom_css .= $prefix.' .swiper-pagination-bullet-active{background-color:'.$v['pag_nav_bg_hover'].';}';
            }
            if($v['control_bg'])
            {
                $custom_css .= $prefix.' .swiper-pagination-bullet, '.$prefix.' .swiper-pagination-progress{background-color:'.$v['control_bg'].';}';
                $custom_css .= $prefix.' .swiper-pagination-st-round .swiper-pagination-bullet{background-color:transparent;border-color:'.$v['control_bg'].';}';
                $custom_css .= $prefix.' .swiper-pagination-st-round .swiper-pagination-bullet span{background-color:'.$v['control_bg'].';}';
            }
            if($v['control_bg_hover'])
            {
                $custom_css .= $prefix.' .swiper-pagination-bullet-active, '.$prefix.' .swiper-pagination-progress .swiper-pagination-progressbar{background-color:'.$v['control_bg_hover'].';}';
                $custom_css .= $prefix.' .swiper-pagination-st-round .swiper-pagination-bullet.swiper-pagination-bullet-active{background-color:'.$v['control_bg_hover'].';border-color:'.$v['control_bg_hover'].';}';
                $custom_css .= $prefix.' .swiper-pagination-st-round .swiper-pagination-bullet.swiper-pagination-bullet-active span{background-color:'.$v['control_bg_hover'].';}';
            }

                    }
                }
            
            if($custom_css)
                $this->smarty->assign('custom_css', preg_replace('/\s\s+/', ' ', $custom_css));
        }
        
        return $this->fetch($this->templatePath.'header.tpl', $this->getCacheId());
    }
    public function hookDisplayFooter($params, $func='')
    {
        $display_on = $this->getDisplayOn($func ? $func : __FUNCTION__);
        if (!$display_on) {
            return false;
        }
	    if (!$this->isCached($this->templatePath.'stproductcategoriesslider-footer.tpl', $this->getCacheId($display_on)))
	    {
	        $this->_prepareHook('fot', $display_on);
            $this->smarty->assign(array(
    			'add_prod_display' => Configuration::get('PS_ATTRIBUTE_CATEGORY_DISPLAY'),
    		));
	    }
		return $this->fetch($this->templatePath.'stproductcategoriesslider-footer.tpl', $this->getCacheId($display_on));
    }
    public function hookActionObjectCategoryDeleteAfter($params)
    {
        $this->clearSliderCache();
        
        if(!$params['object']->id)
            return ;
        $res = StProductCategoriesSliderClass::deleteByCategoryId($params['object']->id);
        return $res;
    }
	public function hookActionCategoryDelete($params)
	{
        $params['object'] = $params['category'];
		return $this->hookActionObjectCategoryDeleteAfter($params);
	}
    public function hookAddProduct($params)
	{
        $this->clearSliderCache();
	}

	public function hookUpdateProduct($params)
	{
        $this->clearSliderCache();
	}

	public function hookDeleteProduct($params)
	{
        $this->clearSliderCache();
    }
    
	/**
	 * Return the list of fields value
	 *
	 * @param object $obj Object
	 * @return array
	 */
	public function getFieldsValueSt($obj,$fields_form="fields_form")
	{
		foreach ($this->$fields_form as $fieldset)
			if (isset($fieldset['form']['input']))
				foreach ($fieldset['form']['input'] as $input)
					if (!isset($this->fields_value[$input['name']]))
						if (isset($input['type']) && $input['type'] == 'shop')
						{
							if ($obj->id)
							{
								$result = Shop::getShopById((int)$obj->id, $this->identifier, $this->table);
								foreach ($result as $row)
									$this->fields_value['shop'][$row['id_'.$input['type']]][] = $row['id_shop'];
							}
						}
						elseif (isset($input['lang']) && $input['lang'])
							foreach (Language::getLanguages(false) as $language)
							{
								$fieldValue = $this->getFieldValueSt($obj, $input['name'], $language['id_lang']);
								if (empty($fieldValue))
								{
									if (isset($input['default_value']) && is_array($input['default_value']) && isset($input['default_value'][$language['id_lang']]))
										$fieldValue = $input['default_value'][$language['id_lang']];
									elseif (isset($input['default_value']))
										$fieldValue = $input['default_value'];
								}
								$this->fields_value[$input['name']][$language['id_lang']] = $fieldValue;
							}
						else
						{
							$fieldValue = $this->getFieldValueSt($obj, $input['name']);
							if ($fieldValue===false && isset($input['default_value']))
								$fieldValue = $input['default_value'];
							$this->fields_value[$input['name']] = $fieldValue;
						}

		return $this->fields_value;
	}
    
	/**
	 * Return field value if possible (both classical and multilingual fields)
	 *
	 * Case 1 : Return value if present in $_POST / $_GET
	 * Case 2 : Return object value
	 *
	 * @param object $obj Object
	 * @param string $key Field name
	 * @param integer $id_lang Language id (optional)
	 * @return string
	 */
	public function getFieldValueSt($obj, $key, $id_lang = null)
	{
		if ($id_lang)
			$default_value = ($obj->id && isset($obj->{$key}[$id_lang])) ? $obj->{$key}[$id_lang] : false;
		else
			$default_value = isset($obj->{$key}) ? $obj->{$key} : false;

		return Tools::getValue($key.($id_lang ? '_'.$id_lang : ''), $default_value);
	}
    public function getConfigFieldsValues()
    {
        $fields_values = array(
            'random'             => Configuration::get($this->_prefix_st.'RANDOM'),
            'tabs'               => Configuration::get($this->_prefix_st.'TABS'),
            'top_padding'        => Configuration::get($this->_prefix_st.'TOP_PADDING'),
            'bottom_padding'     => Configuration::get($this->_prefix_st.'BOTTOM_PADDING'),
            'top_margin'         => Configuration::get($this->_prefix_st.'TOP_MARGIN'),
            'bottom_margin'      => Configuration::get($this->_prefix_st.'BOTTOM_MARGIN'),
            'bg_pattern'         => Configuration::get($this->_prefix_st.'BG_PATTERN'),
            'bg_img'             => Configuration::get($this->_prefix_st.'BG_IMG'),
            'bg_color'           => Configuration::get($this->_prefix_st.'BG_COLOR'),
            'speed'              => Configuration::get($this->_prefix_st.'SPEED'),
            'title_align'        => Configuration::get($this->_prefix_st.'TITLE_ALIGN'),
            'header_color'          => Configuration::get($this->_prefix_st.'HEADER_COLOR'),
            'header_hover_color'    => Configuration::get($this->_prefix_st.'HEADER_HOVER_COLOR'),
            'header_bg'             => Configuration::get($this->_prefix_st.'HEADER_BG'),
            'header_hover_bg'       => Configuration::get($this->_prefix_st.'HEADER_HOVER_BG'),
            'header_active_border'  => Configuration::get($this->_prefix_st.'HEADER_ACTIVE_BORDER'),
            'header_border'         => Configuration::get($this->_prefix_st.'HEADER_BORDER'),
            'tab_bottom_border'     => Configuration::get($this->_prefix_st.'TAB_BOTTOM_BORDER'),
            'tab_font_size'         => Configuration::get($this->_prefix_st.'TAB_FONT_SIZE'),
        );
        
        return $fields_values;
    }
    
    private function getDisplayOn($func = '')
    {
        $ret = 0;
        if (!$func)
            return $ret;
        foreach($this->_hooks AS $value)
            if ('hook'.strtolower($value['id']) == strtolower($func))
                return (int)$value['val'];
        return $ret;
    }
    
    public function processUpdatePositions()
	{
		if (Tools::getValue('action') == 'updatePositions' && Tools::getValue('ajax'))
		{
			$way = (int)(Tools::getValue('way'));
			$id = (int)(Tools::getValue('id'));
			$positions = Tools::getValue('st_product_categories_slider');
            $msg = '';
			if (is_array($positions))
				foreach ($positions as $position => $value)
				{
					$pos = explode('_', $value);

					if ((isset($pos[2])) && ((int)$pos[2] === $id))
					{
						if ($object = new StProductCategoriesSliderClass((int)$pos[2]))
							if (isset($position) && $object->updatePosition($way, $position))
								$msg = 'ok position '.(int)$position.' for ID '.(int)$pos[2]."\r\n";	
							else
								$msg = '{"hasError" : true, "errors" : "Can not update position"}';
						else
							$msg = '{"hasError" : true, "errors" : "This object ('.(int)$id.') can t be loaded"}';

						break;
					}
				}
                die($msg);
		}
	}
    public function prepareHooks()
    {
        $display_on = array();
        $rows = Db::getInstance()->executeS('SELECT display_on FROM `'._DB_PREFIX_.'st_product_categories_slider` 
            WHERE id_shop='.(int)$this->context->shop->id);
        if ($rows) {
            foreach($this->_hooks AS $hook) {
                foreach($rows AS $row) {
                    if ((int)$hook['val']&(int)$row['display_on']) {
                        $display_on[] = $hook['id'];
                    }
                }
            }
            $display_on = array_unique($display_on);    
        }
        foreach($this->_hooks AS $hook)
        {
            if (!isset($hook['id']) || !$hook['id'])
                continue;
            $id_hook = Hook::getIdByName($hook['id']);
            if (count($display_on) && in_array($hook['id'], $display_on))
            {
                if ($id_hook && Hook::getModulesFromHook($id_hook, $this->id))
                    continue;
                if (!$this->isHookableOn($hook['id']))
                    $this->validation_errors[] = $this->getTranslator()->trans('This module cannot be transplanted to ', array(), 'Modules.Stbanner.Admin').$hook.'.';
                else
                    $this->registerHook($hook['id'], Shop::getContextListShopID());
            }
            else
            {
                if($id_hook && Hook::getModulesFromHook($id_hook, $this->id))
                {
                    $this->unregisterHook($id_hook, Shop::getContextListShopID());
                    $this->unregisterExceptions($id_hook, Shop::getContextListShopID());
                } 
            }   
        }
        Cache::clean('hook_module_list');
        return true;
    }
    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        return ;
    }
    public function renderWidget($hookName = null, array $configuration = [])
    {
        return ;
    }
    public function hookvcBeforeInit()
    {
        JsComposer::add_shortcode($this->name, array($this,'vc_shortcode_init'));
        if(isset($this->context->controller->admin_webpath) && !empty($this->context->controller->admin_webpath)) {
            $this->vc_map_init();
        }  
    }
    public function vc_shortcode_init($atts, $content = null)
    {
        extract(JsComposer::shortcode_atts(array(
            'hook_name' => '',
            'id_st_product_categories_slider' => 0,
            'st_time' => '',
            ), $atts));
        
        if(!isset($this->vc_hooks[$hook_name]))
            return ;
        $hook = 'hook'.ucfirst($this->vc_hooks[$hook_name]);
        return $this->$hook(array('st_time'=>$st_time,'id_st_product_categories_slider'=>$id_st_product_categories_slider));
    }
    function vc_map_init()
    {
        $content = array();
        $default = 0;
        foreach(StProductCategoriesSliderClass::getListContent(0, 268435456) AS $value) {
            $content[$this->getTranslator()->trans('Block ID:', array(), 'Admin.Theme.Transformer').$value['id_st_product_categories_slider'].'|'.($value['id_category'] ? self::displayCategory($value['id_category']) : "").($value['id_manufacturer'] ? self::displayManufacturer($value['id_manufacturer']) : "")] = $value['id_st_product_categories_slider'];
            !$default && $default = (int)$value['id_st_product_categories_slider'];
        }
        $vc_hooks = array();
        foreach ($this->vc_hooks as $key => $value) {
            $vc_hooks[$key] = $key;
        }
        $params = array(
            'name' => $this->displayName,
            'base' => $this->name,
            'icon' => 'icon-transformer',
            'category' => 'Transformer',
            'params' => array(
                array(
                    'type' => 'dropdown',
                    'holder' => 'div',
                    'class' => 'hide_in_vc_editor',
                    'heading' => $this->getTranslator()->trans('Choose a block', array(), 'Admin.Theme.Transformer'),
                    'param_name' => 'id_st_product_categories_slider',
                    'value' => $content,
                    'std' => $default
                ),
                array(
                    'type' => 'dropdown',
                    'holder' => 'div',
                    'class' => 'hide_in_vc_editor',
                    'heading' => $this->getTranslator()->trans('How to display', array(), 'Admin.Theme.Transformer'),
                    'param_name' => 'hook_name',
                    'value' => $vc_hooks,
                    'std' => 'Block'
                ),
                array(
                    'type' => 'textfield',
                    'holder' => 'div',
                    'param_name' => 'st_time',
                    'value' => time(),
                ),
                array(
                    'type' => 'html',
                    'param_name' => 'st_conf_link_box',
                    'code' => '<a href="'.$this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&module_name='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'" target="_blank" class="st_conf_link">'.$this->getTranslator()->trans('Go to the configuration page of this module to change settings.', array(), 'Admin.Theme.Transformer').'</a>',
                ),
                array(
                    'type' => 'html',
                    'param_name' => 'st_refeash_link_box',
                    'code' => '<a href="javascript:;" class="st_refeash_link">'.$this->getTranslator()->trans('Refresh this window to get new data.', array(), 'Admin.Theme.Transformer').'</a>',
                ),
            )
        );
        vc_map($params);
    }
}
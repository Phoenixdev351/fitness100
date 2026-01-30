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
class StCompare extends Module implements WidgetInterface
{
    private $templateFile = array();
    private $_html = '';
    public $fields_form;
    public $fields_value;
    private $_prefix_st = 'ST_COMP_';
    private $_st_themes = false;
    public $validation_errors = array();
    private static $video_position = array();
    protected static $access_rights = 0775;
    private $_hooks = array();
    private $_items = array();
	function __construct()
	{
		$this->name           = 'stcompare';
		$this->tab            = 'front_office_features';
		$this->version        = '1.0.1';
		$this->author         = 'SUNNYTOO.COM';
		$this->need_instance  = 0;
		$this->bootstrap 	  = true;
		parent::__construct();

		$this->displayName = $this->getTranslator()->trans('Product Comparison', array(), 'Admin.Theme.Transformer');
		$this->description = $this->getTranslator()->trans('Adds a product comparison feature to your PrestaShop 1.7 site.', array(), 'Admin.Theme.Transformer');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->controllers = array('compare');
        $this->initPages();
        $this->initHookArray();

        $this->templateFile = array(
            'module:stcompare/views/templates/hook/link.tpl',
            'module:stcompare/views/templates/hook/fly.tpl',
            'module:stcompare/views/templates/hook/header.tpl',
            );
	}

    private function initPages()
    {
        $this->_items = array(
                array(
                    'id' => 'picture',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Picture', array(), 'Admin.Theme.Transformer')
                ),
                array(
                    'id' => 'name',
                    'val' => '2',
                    'name' => $this->getTranslator()->trans('Name', array(), 'Admin.Theme.Transformer')
                ),
                array(
                    'id' => 'price',
                    'val' => '4',
                    'name' => $this->getTranslator()->trans('Price', array(), 'Admin.Theme.Transformer')
                ),
                array(
                    'id' => 'rating',
                    'val' => '8',
                    'name' => $this->getTranslator()->trans('Rating', array(), 'Admin.Theme.Transformer')
                ),
                array(
                    'id' => 'short_desc',
                    'val' => '16',
                    'name' => $this->getTranslator()->trans('Short description', array(), 'Admin.Theme.Transformer')
                ),
                array(
                    'id' => 'stock',
                    'val' => '32',
                    'name' => $this->getTranslator()->trans('Stock', array(), 'Admin.Theme.Transformer')
                ),
                array(
                    'id' => 'main_variants',
                    'val' => '64',
                    'name' => $this->getTranslator()->trans('Main variants (Color)', array(), 'Admin.Theme.Transformer')
                ),
                array(
                    'id' => 'add_to_cart',
                    'val' => '128',
                    'name' => $this->getTranslator()->trans('Add to cart button', array(), 'Admin.Theme.Transformer')
                ),
            );
    }
        
    private function initHookArray()
    {
        $this->_hooks = array(
            'Header' => array(
                array(
                    'id' => 'displayNav1',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Topbar left - displayNav1', array(), 'Admin.Theme.Transformer'),
                    'in_header' => 1,
                ),
                array(
                    'id' => 'displayNav2',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Topbar right - displayNav2', array(), 'Admin.Theme.Transformer'),
                    'in_header' => 1,
                ),
                array(
                    'id' => 'displayNav3',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Topbar center - displayNav3', array(), 'Admin.Theme.Transformer'),
                    'in_header' => 1,
                ),
                array(
                    'id' => 'displayTop',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayTop', array(), 'Admin.Theme.Transformer'),
                    'in_header' => 1,
                ),
                array(
                    'id' => 'displayHeaderCenter',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayHeaderCenter', array(), 'Admin.Theme.Transformer'),
                    'in_header' => 1,
                ),
                array(
                    'id' => 'displayHeaderLeft',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayHeaderLeft', array(), 'Admin.Theme.Transformer'),
                    'in_header' => 1,
                ),
                array(
                    'id' => 'displayHeaderBottom',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayHeaderBottom', array(), 'Admin.Theme.Transformer'),
                    'in_header' => 1,
                ),
                array(
                    'id' => 'displayMainMenuWidget',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('Main menu widget', array(), 'Admin.Theme.Transformer'),
                    'in_header' => 1,
                ),
            ),
            'Product page' => array(
                array(
                    'id' => 'displayProductNameRight',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayProductNameRight', array(), 'Admin.Theme.Transformer'),
                    'in_product_page' => 1,
                ),
                array(
                    'id' => 'displayUnderProductName',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayUnderProductName', array(), 'Admin.Theme.Transformer'),
                    'in_product_page' => 1,
                ),
                array(
                    'id' => 'displayProductPriceRight',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayProductPriceRight', array(), 'Admin.Theme.Transformer'),
                    'in_product_page' => 1,
                ),
                array(
                    'id' => 'displayProductCartRight',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayProductCartRight', array(), 'Admin.Theme.Transformer'),
                    'in_product_page' => 1,
                ),
                array(
                    'id' => 'displayLeftColumnProduct',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayLeftColumnProduct', array(), 'Admin.Theme.Transformer'),
                    'in_product_page' => 1,
                ),
                array(
                    'id' => 'displayProductLeftColumn',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayProductLeftColumn', array(), 'Admin.Theme.Transformer'),
                    'in_product_page' => 1,
                ),
                array(
                    'id' => 'displayProductCenterColumn',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayProductCenterColumn', array(), 'Admin.Theme.Transformer'),
                    'in_product_page' => 1,
                ),
                array(
                    'id' => 'displayProductRightColumn',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayProductRightColumn', array(), 'Admin.Theme.Transformer'),
                    'in_product_page' => 1,
                ),
                array(
                    'id' => 'displayRightColumnProduct',
                    'val' => '1',
                    'name' => $this->getTranslator()->trans('displayRightColumnProduct', array(), 'Admin.Theme.Transformer'),
                    'in_product_page' => 1,
                ),
            ),
        );
    }
    
    private function saveHook()
    {
        foreach($this->_hooks AS $key => $values)
        {
            if (!$key)
                continue;
            foreach($values AS $value)
            {
                $id_hook = Hook::getIdByName($value['id']);
                $key = str_replace(' ','_', $key);
                if (Tools::getValue($key.'_'.$value['id']))
                {
                    if ($id_hook && Hook::getModulesFromHook($id_hook, $this->id))
                        continue;
                    if (!$this->isHookableOn($value['id']))
                        $this->validation_errors[] = $this->getTranslator()->trans('This module cannot be transplanted to ', array(), 'Admin.Theme.Transformer').$value['id'];
                    else
                        $rs = $this->registerHook($value['id'], Shop::getContextListShopID());
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
        }
        // clear module cache to apply new data.
        Cache::clean('hook_module_list');
    }
            
	function install()
	{
		if (!parent::install() 
            || !Configuration::updateValue($this->_prefix_st.'MAX', 10)
            || !Configuration::updateValue($this->_prefix_st.'HEADER_STYLE', 0)
            || !Configuration::updateValue($this->_prefix_st.'HEADER_ICON_SIZE', 0)
            || !Configuration::updateValue($this->_prefix_st.'HEADER_TEXT_SIZE', 0)
            || !Configuration::updateValue($this->_prefix_st.'PRODUCT_STYLE', 0)
            || !Configuration::updateValue($this->_prefix_st.'WIDTH', 0)
            || !Configuration::updateValue($this->_prefix_st.'ITEMS', 255)
            || !Configuration::updateValue($this->_prefix_st.'FLY_OUT', 0)
            || !$this->registerHook('displayHeader')
            || !$this->registerHook('displayNav2')
            || !$this->registerHook('actionProductSearchAfter')
            || !$this->registerHook('displayProductCenterColumn')
            || !$this->registerHook('actionStAssemble')
        )
			return false;
		return true;
	}

    public function getContent()
    {
        $this->initFieldsForm();
        if (isset($_POST['savestcompare']))
        {
            foreach($this->fields_form as $form)
                foreach($form['form']['input'] as $field)
                    if(isset($field['validation']))
                    {
                        $ishtml = ($field['validation']=='isAnything') ? true : false;
                        $errors = array();       
                        $value = Tools::getValue($field['name']);
                        if (isset($field['required']) && $field['required'] && $value==false && (string)$value != '0')
                                $errors[] = sprintf(Tools::displayError('Field "%s" is required.'), $field['label']);
                        elseif($value)
                        {
                            $field_validation = $field['validation'];
                            if (!Validate::$field_validation($value))
                                $errors[] = sprintf(Tools::displayError('Field "%s" is invalid.'), $field['label']);
                        }
                        // Set default value
                        if ($value === false && isset($field['default_value']))
                            $value = $field['default_value'];
                        
                        if(count($errors))
                        {
                            $this->validation_errors = array_merge($this->validation_errors, $errors);
                        }
                        elseif($value==false)
                        {
                            switch($field['validation'])
                            {
                                case 'isUnsignedId':
                                case 'isUnsignedInt':
                                case 'isInt':
                                case 'isBool':
                                    $value = 0;
                                break;
                                default:
                                    $value = '';
                                break;
                            }
                            Configuration::updateValue($this->_prefix_st.strtoupper($field['name']), $value);
                        }
                        else
                            Configuration::updateValue($this->_prefix_st.strtoupper($field['name']), $value, $ishtml);
                    }
            $items = 0;
            foreach($this->_items as $v)
                $items += (int)Tools::getValue('items_'.$v['id']);
            Configuration::updateValue($this->_prefix_st.'ITEMS', $items);

            $this->saveHook();

            if(count($this->validation_errors))
                $this->_html .= $this->displayError(implode('<br/>',$this->validation_errors));
            else 
                $this->_html .= $this->displayConfirmation($this->getTranslator()->trans('Settings updated', array(), 'Admin.Theme.Transformer'));

            $this->_clearCache('*');
        }

        $helper = $this->initForm();
        
        return $this->_html.$helper->generateForm($this->fields_form);
    }

    protected function initFieldsForm()
    {
        $this->fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->displayName,
                'icon' => 'icon-cogs'
            ),
            'input' => array( 
                /*array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Product comparison:', array(), 'Admin.Theme.Transformer'),
                    'name' => 'max',
                    'class' => 'fixed-width-lg',
                    'validation' => 'isUnsignedId',
                    'desc' => $this->getTranslator()->trans('Set the maximum number of products that can be selected for comparison. Set to "0" to disable this feature.', array(), 'Admin.Theme.Transformer'),
                ),*/
                array(
                    'type' => 'checkbox',
                    'label' => $this->getTranslator()->trans('Compare items', array(), 'Admin.Theme.Transformer'),
                    'name' => 'items',
                    'values' => array(
                        'query' => $this->_items,
                        'id' => 'id',
                        'name' => 'name'
                    ),
                ), 
                array(
                    'type' => 'switch',
                    'label' => $this->getTranslator()->trans('Display a "Add to compare " in the fly-out button:', array(), 'Admin.Theme.Transformer'),
                    'name' => 'fly_out',
                    'is_bool' => true,
                    'values' => array(
                        array(
                            'id' => 'fly_out_on',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Yes', array(), 'Admin.Theme.Transformer')),
                        array(
                            'id' => 'fly_out_off',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('No', array(), 'Admin.Theme.Transformer')),
                    ),
                    'validation' => 'isBool',
                ), 
                array(
                    'type' => 'radio',
                    'label' => $this->getTranslator()->trans('How to display link in the header:', array(), 'Modules.Stcompare.Admin'),
                    'name' => 'header_style',
                    'default_value' => 1,
                    'values' => array(
                        array(
                            'id' => 'header_style_both',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('Icon + Text', array(), 'Admin.Theme.Transformer')),
                        array(
                            'id' => 'header_style_name',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Text', array(), 'Admin.Theme.Transformer')),
                        array(
                            'id' => 'header_style_flag',
                            'value' => 2,
                            'label' => $this->getTranslator()->trans('Icon', array(), 'Admin.Theme.Transformer')),
                        array(
                            'id' => 'header_style_vertical',
                            'value' => 3,
                            'label' => $this->getTranslator()->trans('Icon + Text (Vertical)', array(), 'Admin.Theme.Transformer')),
                        array(
                            'id' => 'header_style_badge',
                            'value' => 4,
                            'label' => $this->getTranslator()->trans('Icon + badge', array(), 'Admin.Theme.Transformer')),
                        array(
                            'id' => 'header_style_badge_text',
                            'value' => 5,
                            'label' => $this->getTranslator()->trans('Icon + badge', array(), 'Admin.Theme.Transformer')),
                    ),
                    'validation' => 'isUnsignedInt',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Icon size in the header:', array(), 'Admin.Theme.Transformer'),
                    'name' => 'header_icon_size',
                    'prefix' => 'px',
                    'default_value' => 0,
                    'validation' => 'isUnsignedInt',
                    'class' => 'fixed-width-sm'
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Text size in the header:', array(), 'Admin.Theme.Transformer'),
                    'name' => 'header_text_size',
                    'prefix' => 'px',
                    'default_value' => 0,
                    'validation' => 'isUnsignedInt',
                    'class' => 'fixed-width-sm'
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->getTranslator()->trans('How to display link on the product page:', array(), 'Modules.Stcompare.Admin'),
                    'name' => 'product_style',
                    'default_value' => 1,
                    'values' => array(
                        array(
                            'id' => 'product_style_both',
                            'value' => 0,
                            'label' => $this->getTranslator()->trans('Icon + Text', array(), 'Admin.Theme.Transformer')),
                        array(
                            'id' => 'product_style_name',
                            'value' => 1,
                            'label' => $this->getTranslator()->trans('Text', array(), 'Admin.Theme.Transformer')),
                        array(
                            'id' => 'product_style_flag',
                            'value' => 2,
                            'label' => $this->getTranslator()->trans('Icon', array(), 'Admin.Theme.Transformer')),
                    ),
                    'validation' => 'isUnsignedInt',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->getTranslator()->trans('Column mini width:', array(), 'Admin.Theme.Transformer'),
                    'name' => 'width',
                    'class' => 'fixed-width-lg',
                    'prefix' => 'px',
                    'validation' => 'isUnsignedId',
                ),
            ),
            'submit' => array(
                'title' => $this->getTranslator()->trans('Save', array(), 'Admin.Actions')
            )
        );

        $this->fields_form[1]['form'] = array(
            'legend' => array(
                'title' => $this->getTranslator()->trans('Hook manager', array(), 'Admin.Theme.Transformer'),
                'icon' => 'icon-cogs'
            ),
            'description' => $this->getTranslator()->trans('Check the hook that you would like this module to display on.', array(), 'Admin.Theme.Transformer').'<br/><a href="'._MODULE_DIR_.'stthemeeditor/img/hook_into_hint.jpg" target="_blank" >'.$this->getTranslator()->trans('Click here to see hook position', array(), 'Admin.Theme.Transformer').'</a>.',
            'input' => array(
            ),
            'submit' => array(
                'title' => $this->getTranslator()->trans('Save', array(), 'Admin.Actions')
            ),
        );
        
        foreach($this->_hooks AS $key => $values)
        {
            if (!is_array($values) || !count($values))
                continue;
            $this->fields_form[1]['form']['input'][] = array(
                    'type' => 'checkbox',
                    'label' => $key,
                    'name' => $key,
                    'lang' => true,
                    'values' => array(
                        'query' => $values,
                        'id' => 'id',
                        'name' => 'name'
                    )
                );
        }
    }
    protected function initForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table =  $this->table;
        $helper->module = $this;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'savestcompare';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        $items = Configuration::get($this->_prefix_st.'ITEMS');
        foreach($this->_items as $v) {
            $helper->tpl_vars['fields_value']['items_'.$v['id']] = (int)$v['val']&(int)$items;
        }
        return $helper;
    }
    
    private function getConfigFieldsValues()
    {
        $fields_values = array(
            'max' => Configuration::get($this->_prefix_st.'MAX'),
            'fly_out'      => Configuration::get($this->_prefix_st.'FLY_OUT'),
            'header_style'      => Configuration::get($this->_prefix_st.'HEADER_STYLE'),
            'header_icon_size'      => Configuration::get($this->_prefix_st.'HEADER_ICON_SIZE'),
            'header_text_size'      => Configuration::get($this->_prefix_st.'HEADER_TEXT_SIZE'),
            'product_style'     => Configuration::get($this->_prefix_st.'PRODUCT_STYLE'),
            'width'     => Configuration::get($this->_prefix_st.'WIDTH'),
        );
        foreach($this->_hooks AS $key => $values)
        {
            if (!$key)
                continue;
            foreach($values AS $value)
            {
                $fields_values[$key.'_'.$value['id']] = 0;
                if($id_hook = Hook::getIdByName($value['id']))
                    if(Hook::getModulesFromHook($id_hook, $this->id))
                        $fields_values[$key.'_'.$value['id']] = 1;
            }
        }
        return $fields_values;
    }
    public function hookDisplayHeader($params)
	{
        $arr = array();
        if (isset($this->context->cookie->stcompareids) && $this->context->cookie->stcompareids) {
            $arr = explode(',', $this->context->cookie->stcompareids);
        }
        Media::addJsDef(array(
            'stcompare' => array(
                'url' => $this->context->link->getModuleLink('stcompare', 'compare'),
                'ids' => $arr,
            ),
        ));
        $this->context->smarty->assign($this->getWidgetVariables());
        $this->context->controller->addJS($this->_path.'views/js/front.js');
        $this->context->controller->addCSS($this->_path.'views/css/front.css');
        if (!$this->isCached($this->templateFile[2], $this->getCacheId()))
        {
            $custom_css = '';
            if($width = (int)Configuration::get($this->_prefix_st.'WIDTH'))
                $custom_css .= '.stcompare_table td{min-width: '.$width.'px;}';
            if($icon_size = Configuration::get($this->_prefix_st.'HEADER_ICON_SIZE')){
                $custom_css .= '.stcompare_link.top_bar_item .header_icon_btn_icon i{font-size:'.$icon_size.'px;}';
            }
            if($text_size = Configuration::get($this->_prefix_st.'HEADER_TEXT_SIZE')){
                $custom_css .= '.stcompare_link.top_bar_item .header_icon_btn_text{font-size:'.$text_size.'px;}';
            }
            $this->smarty->assign('custom_css', preg_replace('/\s\s+/', ' ', $custom_css));
        }
        return $this->fetch($this->templateFile[2], $this->getCacheId());
	}
    public function renderWidget($hookName = null, array $configuration = [])
    {
        if (strpos(strtolower($hookName), 'display') === false) {
            return;
        }
        $in_header = $in_product_page = false;
        foreach ($this->_hooks as $sub_hooks) {
        foreach ($sub_hooks as $v) {
            if (Tools::strtolower($v['id'])==Tools::strtolower($hookName)) {
                $in_header = isset($v['in_header']);
                $in_product_page = isset($v['in_product_page']);
                break 2;
            }
        }
        }
        $vars = array();
        $arr = array();
        if (isset($this->context->cookie->stcompareids) && $this->context->cookie->stcompareids) {
            $arr = explode(',', $this->context->cookie->stcompareids);
        }

        $vars['stcompare_header_style'] = Configuration::get($this->_prefix_st.'HEADER_STYLE');
        $vars['stcompare_product_style'] = Configuration::get($this->_prefix_st.'PRODUCT_STYLE');
        if($in_product_page)
        {
            $id_product=(int)Tools::getValue('id_product');
            $vars = array(
                'id_product' => $id_product,
                'stcompare_with_number' => true,
                'fromnocache' => true,
                );
            if($id_product)
                $vars['classname'] = 'btn_inline '.(in_array($id_product, $arr) ? ' st_added ' : '');
        }
        $vars['stcompare_total'] = count(array_unique($arr));
        $this->smarty->assign($vars);

        return $this->fetch($in_header ? $this->templateFile[0] : $this->templateFile[1]);
    }

    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        return array(
            'stcompare'    => array(
                'url' => $this->context->link->getModuleLink('stcompare', 'compare'),
                'fly_out'      => (int)Configuration::get($this->_prefix_st.'FLY_OUT'),
            ),
        );        
    }
    public function hookActionProductSearchAfter($params){
        $this->context->smarty->assign($this->getWidgetVariables());
        return ;
    }
    public function hookActionStAssemble($product)
    {
        $arr = array();
        if (isset($this->context->cookie->stcompareids) && $this->context->cookie->stcompareids) {
            $arr = explode(',', $this->context->cookie->stcompareids);
        }
        return array(
            'compared' => in_array($product['id_product'], $arr),
            );
    }
}
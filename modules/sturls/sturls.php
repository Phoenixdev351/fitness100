<?php
/*
* 2007-2018 PrestaShop
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
*  @author ST themes <www.sunnytoo.com>
*  @copyright 2018 ST themes team.
*/
if (!defined('_PS_VERSION_'))
	exit;

class StUrls extends Module
{
    public  $fields_list;
    public  $fields_value;
    public  $fields_form;
	private $_html = '';
    private $spacer_size = '5';
    public $_prefix_st = 'ST_URL_';
    public $controller_map = array();
    public $selected_controller = array();
    public $lang_field = array(
        'router_blog'=>'blog', 
        'router_cms'=>'content', 
        'router_manufacturer'=>'manufacturer', 
        'router_supplier'=>'supplier'
    );
	function __construct()
	{
		$this->name          = 'sturls';
		$this->tab           = 'front_office_features';
		$this->version       = '1.1.11';
		$this->author        = 'SUNNYTOO.COM';
		$this->need_instance = 0;
        $this->bootstrap     = true;
        
		parent::__construct();

        // Don't use the l() to translate phrases in the function, that will cause a translation issue when switching language on the front office.
		$this->displayName = 'PrestaShop removing IDs from URLs & SEO friendly url module.';
		$this->description = 'Remove IDs from friendly URLs in Prestashop 1.7 and 1.6, add canonical tag to the head section when in Prestashop 1.6.';
        
        $this->controller_map = array(
            'product' => array(
                'name'=>'Product', 
                'field_rewrite'=>'link_rewrite', 
                'bo_controller'=>'Products', 
                'func'=>'getProductLink',
                'route_id' => 'product_rule',
                'id' => 'product'
            ),
            'category' => array(
                'name'=>'Category', 
                'field_rewrite'=>'link_rewrite', 
                'bo_controller'=>'Categories', 
                'func'=>'getCategoryLink',
                'route_id' => 'category_rule',
                'id' => 'category'
            ),
            'supplier' => array(
                'name'=>'Supplier', 
                'field_rewrite'=>'name', 
                'bo_controller'=>'Suppliers', 
                'func'=>'getSupplierLink',
                'route_id' => 'supplier_rule',
                'id' => 'supplier'
            ),
            'manufacturer' => array(
                'name'=>'Manufacturer', 
                'field_rewrite'=>'name', 
                'bo_controller'=>'Manufacturers', 
                'func'=>'getManufacturerLink',
                'route_id' => 'manufacturer_rule',
                'id' => 'manufacturer'
            ),
            'cms_category' => array(
                'name'=>'CMS Category', 
                'field_rewrite'=>'link_rewrite', 
                'bo_controller'=>'CmsContent', 
                'func'=>'getCMSCategoryLink',
                'route_id' => 'cms_category_rule',
                'id' => 'cms_category'
            ),
            'cms' => array(
                'name'=>'CMS', 
                'field_rewrite'=>'link_rewrite', 
                'bo_controller'=>'CmsContent', 
                'func'=>'getCMSLink',
                'route_id' => 'cms_rule',
                'id' => 'cms'
            ),
            'st_blog' => array(
                'name'=>'Blog article', 
                'field_rewrite'=>'link_rewrite', 
                'bo_controller'=>'StBlog', 
                'func'=>'getModuleLink',
                'route_id' => 'module-stblog-article',
                'id' => 'st_blog'
            ),
            'st_blog_category' => array(
                'name'=>'Blog category', 
                'field_rewrite'=>'link_rewrite', 
                'bo_controller'=>'StBlogCategory', 
                'func'=>'getModuleLink',
                'route_id' => 'module-stblog-category',
                'id' => 'st_blog_category'
            )
        );
	}
    
	function install()
	{
		$res = parent::install()
			&& $this->registerHook('actionDispatcher')
            && $this->registerHook('moduleRoutes')
            && $this->registerHook('displayHeader')
            && $this->registerHook('actionDispatcherBefore')
            && Configuration::updateValue($this->_prefix_st.'CATEGORY_IN_PRODUCT', 1)
            && Configuration::updateValue($this->_prefix_st.'CATEGORIES_IN_PRODUCT', 0)
            && Configuration::updateValue($this->_prefix_st.'CATEGORIES_IN_CATEGORY', 0)
            && Configuration::updateValue($this->_prefix_st.'REDIRECT', 1)
            && Configuration::updateValue($this->_prefix_st.'PAGE_REDIRECT_TYPE', 1)
            && Configuration::updateValue($this->_prefix_st.'REMOVE_ANCHOR', 1)
            && Configuration::updateValue($this->_prefix_st.'CANONICAL', 0)
            && Configuration::updateValue($this->_prefix_st.'ADVANCED', 0)
            && Configuration::updateValue($this->_prefix_st.'ADVANCED_PRODUCT', 'on')
            && Configuration::updateValue($this->_prefix_st.'ADVANCED_CATEGORY', 'on')
            && Configuration::updateValue($this->_prefix_st.'ADVANCED_CMS', 'on')
            && Configuration::updateValue($this->_prefix_st.'ADD_REFERENCE', 0)
            && $this->checkFields();
        $languages = Language::getLanguages(false);
        $fields = array();
        foreach ($languages as $language) {
            foreach($this->lang_field as $k => $v) {
                $fields[$k][$language['id_lang']] = $v;
            }
        }
        foreach ($fields as $k => $v) {
            Configuration::updateValue($this->_prefix_st.strtoupper($k), $v);
        }
        // Install pages.
        foreach(array('product','category','manufacturer','supplier','cms_category','cms','st_blog','st_blog_category') as $v) {
            Configuration::updateValue($this->_prefix_st.strtoupper('page_'.$v), 1);
        }

        return $res;
	}

	public function uninstall()
	{
	    if (parent::uninstall()) {
            return Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'configuration WHERE name LIKE "'.$this->_prefix_st.'sha1_%" AND (id_shop IS NULL OR id_shop='.(int)$this->context->shop->id.')');
        }
        return true; 
	}
    
	public function getContent()
	{
	    if (isset($_POST['save'.$this->name]) || isset($_POST['save'.$this->name.'AndStay'])) {
            $this->initFieldsForm();
            foreach($this->fields_form as $form)
                foreach($form['form']['input'] as $field)
                    if(isset($field['validation']))
                    {
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
                            Configuration::updateValue($this->_prefix_st.strtoupper($field['name']), $value);
                    }
            $languages = Language::getLanguages(false);
            $fields = array();
            foreach ($languages as $language) {
                foreach($this->lang_field as $k => $v) {
                    $fields[$k][$language['id_lang']] = Tools::getValue($k.'_'.$language['id_lang'], $v);
                }
            }
            foreach ($fields as $k => $v) {
                Configuration::updateValue($this->_prefix_st.strtoupper($k), $v);
            }
            foreach($this->controller_map as $k => $v) {
                Configuration::updateValue($this->_prefix_st.strtoupper('advanced_'.$v['id']), Tools::getValue('advanced_'.$v['id']));
                Configuration::updateValue($this->_prefix_st.strtoupper('page_'.$v['id']), Tools::getValue('page_'.$v['id']));
            }
            if(count($this->validation_errors)) {
                $this->_html .= $this->displayError(implode('<br/>',$this->validation_errors));
            } else {
                if(isset($_POST['save'.$this->name.'AndStay']))
                    Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&conf=4&token='.Tools::getAdminTokenLite('AdminModules')); 
                else
                    $this->_html .= $this->displayConfirmation($this->l('Settings updated'));
            }
        }
        if (Tools::getvalue('clear_route') && $this->clearCustomRoutes()) {
            $this->_html .= $this->displayConfirmation($this->l('Custom routes were removed.'));
        }
        if (Tools::getvalue('clear_cache')) {
            $rs = Db::getInstance()->execute('DELETE FROM '._DB_PREFIX_.'configuration WHERE name LIKE "'.$this->_prefix_st.'sha1_%" AND (id_shop IS NULL OR id_shop='.(int)$this->context->shop->id.')');
            if ($rs) {
                $this->_html .= $this->displayConfirmation($this->l('URL cache was cleared.'));
            }
        }
        if (Tools::getvalue('fix_ref')) {
            $references = Db::getInstance()->executeS('SELECT p.id_product, p.reference FROM '._DB_PREFIX_.'product p LEFT JOIN '._DB_PREFIX_.'product_shop ps ON p.id_product=ps.id_product WHERE ps.id_shop='.(int)$this->context->shop->id.' AND (reference LIKE "%-%" OR reference LIKE "%\_%" OR reference LIKE "% %")');
            $rs  = true;
            foreach($references as $value) {
                $reference = str_replace(array('-', '_', ' '), '', $value['reference']);
                $rs &= Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'product SET reference="'.$reference.'" WHERE id_product='.(int)$value['id_product']);
            }
            if ($rs) {
                $this->_html .= $this->displayConfirmation($this->l('Unexpected references were fixed.'));
            }
        }
	    $this->checkFields();
		$helper = $this->initList();
        $data_duplicated = $this->getDuplicated();
        if (count($data_duplicated)) {
            $this->_html .= $this->displayError($this->l('The following friendly URLs are duplicated, please make them be different!'));
        } else {
            $this->_html .= $this->displayConfirmation($this->l('Well done, no duplicated urls.'));
        }
		$this->_html .= $helper->generateList($data_duplicated, $this->fields_list);
        $this->initFieldsForm();
		$helper = $this->initForm();
        $this->_html .= $helper->generateForm($this->fields_form);
        return $this->_html;
	}
	
	protected function initList()
	{
		$this->fields_list = array(
			'id' => array(
				'title' => $this->l('Id'),
				'width' => 120,
				'type' => 'text',
                'search' => false,
                'orderby' => false
			),
            'type' => array(
                'title' => $this->l('Type'),
                'width' => 140,
                'type' => 'text',
                'search' => false,
                'orderby' => false
            ),
            'name' => array(
                'title' => $this->l('Name / Friedly URL'),
                'width' => 200,
                'type' => 'text',
                'search' => false,
                'orderby' => false
            ),
            'id_lang' => array(
				'title' => $this->l('Language'),
				'width' => 140,
				'type' => 'text',
				'callback' => 'displayLang',
				'callback_object' => 'StUrls',
                'search' => false,
                'orderby' => false,
			),
            'buttons' => array(
                'title' => $this->l('Action'),
                'width' => 200,
                'type' => 'text',
                'callback' => 'displayButtons',
				'callback_object' => 'StUrls',
                'search' => false,
                'orderby' => false
            ),
		);

		$helper = new HelperList();
		$helper->shopLinkType = '';
		$helper->simple_header = false;
		$helper->identifier = 'id';
		$helper->actions = array();
		$helper->show_toolbar = false;
        $helper->toolbar_btn =  array();
		$helper->no_link =  true;

		$helper->title = $this->l('Duplicated urls');
		$helper->table = $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
		return $helper;
	}
    
    public function initFieldsForm()
    {
        $custom_route = $this->getCustomRoutes();
        $count_cache = Db::getInstance()->getValue('SELECT COUNT(0) FROM '._DB_PREFIX_.'configuration WHERE name LIKE "'.$this->_prefix_st.'sha1_%" AND (id_shop IS NULL OR id_shop='.(int)$this->context->shop->id.')');
        //$reference = Db::getInstance()->getValue('SELECT COUNT(0) FROM '._DB_PREFIX_.'product p LEFT JOIN '._DB_PREFIX_.'product_shop ps ON p.id_product=ps.id_product WHERE ps.id_shop='.(int)$this->context->shop->id.' AND (reference LIKE "%-%" OR reference LIKE "%\_%" OR reference LIKE "% %")');
        $this->fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Settings'),
                'icon'  => 'icon-cogs'
            ),
            'description' => $this->l('You need to clear the Smarty cache to make changes take effect.'),
            'input' => array(
                array(
                    'type' => 'html',
                    'id' => '',
                    'label' => '',
                    'name' => ($custom_route ? '<div class="alert alert-info">'.sprintf($this->l('There are %d custom route(s) under BO > Shop parameters > SEO & URLs page, which may cause this module not being able to remove IDs from urls, you can %s click on here to clear custom routes %s to make this module work correctly.'), count($custom_route), '<strong><a href="'.AdminController::$currentIndex.'&configure='.$this->name.'&clear_route=1&token='.Tools::getAdminTokenLite('AdminModules').'">', '</a></strong>').'</div>' : '').
                        '<div class="alert alert-info">'.sprintf($this->l('URL cache total: %d, you can %sclear cache%s manually.'), $count_cache, '<strong><a href="'.AdminController::$currentIndex.'&configure='.$this->name.'&clear_cache=1&token='.Tools::getAdminTokenLite('AdminModules').'">', '</a></strong>').'</div>',
                ),
                array(
                    'type' => 'checkbox',
                    'label' => $this->l('Apply to pages'),
                    'name' => 'page',
                    'lang' => true,
                    'values' => array(
                        'query' => $this->controller_map,
                        'id' => 'id',
                        'name' => 'name'
                    ),
                    'desc' => $this->l('Pages that you want to remove ID from URLs.'),
                ),
                array(
					'type' => 'switch',
					'label' => $this->l('Category in product URL'),
					'name' => 'category_in_product',
                    'default_value' => 1,
					'values' => array(
						array(
							'id' => 'category_in_product_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' => 'category_in_product_off',
							'value' => 0,
							'label' => $this->l('No')
						)
					),
                    'desc' => $this->l('URL like "http://www.domain.com/category/rewrite.html"'),
                    'validation' => 'isBool',
				),
                array(
					'type' => 'switch',
					'label' => $this->l('Parent categories in product URL'),
					'name' => 'categories_in_product',
                    'default_value' => 0,
					'values' => array(
						array(
							'id' => 'categories_in_product_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' => 'categories_in_product_off',
							'value' => 0,
							'label' => $this->l('No')
						)
					),
                    'desc' => $this->l('URL like "http://www.domain.com/parent-category/child-category/rewrite.html"'),
                    'validation' => 'isBool',
				),
                array(
					'type' => 'switch',
					'label' => $this->l('Parent categories in category URL'),
					'name' => 'categories_in_category',
                    'default_value' => 0,
					'values' => array(
						array(
							'id' => 'categories_in_category_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' => 'categories_in_category_off',
							'value' => 0,
							'label' => $this->l('No')
						)
					),
                    'desc' => $this->l('URL like "http://www.domain.com/parent-category/rewrite/"'),
                    'validation' => 'isBool',
				),
                array(
                    'type' => 'radio',
                    'label' => $this->l('Redirection'),
                    'desc' => $this->l('Redirect old url with id to new url without id.'),
                    'name' => 'redirect',
                    'default_value' => 0,
                    'values' => array(
                        array(
                            'id' => 'redirect_0',
                            'value' => 0,
                            'label' => $this->l('None')
                        ),
                        array(
                            'id' => 'redirect_1',
                            'value' => 1,
                            'label' => $this->l('301 - Moved Permanently')
                        ),
                        array(
                            'id' => 'redirect_2',
                            'value' => 2,
                            'label' => $this->l('302 - Moved Temporarily')
                        )
                    ),
                    'validation' => 'isUnsignedInt',
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->l('Show 404 page or not'),
                    'name' => 'page_redirect_type',
                    'default_value' => 0,
                    'values' => array(
                        array(
                            'id' => 'page_redirect_type_0',
                            'value' => 0,
                            'label' => $this->l('Yes, show 404 page')
                        ),
                        array(
                            'id' => 'page_redirect_type_1',
                            'value' => 1,
                            'label' => $this->l('No, redirect to a category page')
                        ),
                    ),
                    'validation' => 'isUnsignedInt',
                ),
                array(
					'type' => 'select',
					'label' => $this->l('Redirect to which category:'),
					'name' => 'redirect_category',
					'options' => array(
						'query' => $this->createCategories(),
        				'id' => 'id',
        				'name' => 'name',
					),
                    'validation' => 'isUnsignedInt',
                    'desc' => $this->l('When a page (Product, category, brand, cms etc) was not found, which category do you want redirect to ?'),
				),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Remove Anchor from product URLs'),
                    'name' => 'remove_anchor',
                    'default_value' => 1,
                    'values' => array(
                        array(
                            'id' => 'remove_anchor_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'remove_anchor_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                    'desc' => $this->l('Remove anchor (such as #/1-size-s/2-color-yellow) from product URLs.'),
                    'validation' => 'isBool',
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Add reference'),
                    'name' => 'add_reference',
                    'default_value' => 0,
                    'values' => array(
                        array(
                            'id' => 'add_reference_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'add_reference_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                    'desc' => array(
                        $this->l('If there are a lot of products with same product names and friendly URLs, add reference to URL to aviod to modify friendly URL one by one manually.'),
                        //$this->l('Reference just allowed characters and numbers only (demo1 and demo20 are okay, while demo-1, demo_20 or "demo 20" can not work).').
                        //($reference ? sprintf($this->l('%sUnexpected references: %d, you can %sclick here%s to remove all hyphen, underline and space.%s'), '<br><strong>', $reference, '<a href="'.AdminController::$currentIndex.'&configure='.$this->name.'&fix_ref=1&token='.Tools::getAdminTokenLite('AdminModules').'">', '</a>', '</strong>') : '')
                    ),
                    'validation' => 'isBool',
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Enable advanced dispatcher'),
                    'name' => 'advanced',
                    'default_value' => 0,
                    'values' => array(
                        array(
                            'id' => 'advanced_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'advanced_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                    'desc' => $this->l('Remove .html and slash from URLs.'),
                    'validation' => 'isBool',
                ),
                array(
                    'type' => 'checkbox',
                    'label' => $this->l('Advanced dispatcher for pages'),
                    'name' => 'advanced',
                    'lang' => true,
                    'values' => array(
                        'query' => $this->controller_map,
                        'id' => 'id',
                        'name' => 'name'
                    ),
                    'desc' => $this->l('Remove .html, slash and other parameters from selected page URLs when the "Enable advanced dispatcher" option is enabled.'),
                ),
                'canonical' => array(
					'type' => 'switch',
					'label' => $this->l('Show canonical tag'),
					'name' => 'canonical',
                    'default_value' => 0,
					'values' => array(
						array(
							'id' => 'canonical_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' => 'canonical_off',
							'value' => 0,
							'label' => $this->l('No')
						)
					),
                    'desc' => $this->l('Adding the canonical url tag between head section for each pages.'),
                    'validation' => 'isBool',
				),
                array(
                    'type' => 'text',
                    'label' => $this->l('Router for blog'),
                    'name' => 'router_blog',
                    'size' => 64,
                    'lang' => true,               
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Router for CMS'),
                    'name' => 'router_cms',
                    'size' => 64,
                    'lang' => true,               
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Router for manufacturer'),
                    'name' => 'router_manufacturer',
                    'size' => 64,
                    'lang' => true,               
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Router for supplier'),
                    'name' => 'router_supplier',
                    'size' => 64,
                    'lang' => true,               
                ),
            ),
			'submit' => array(
				'title' => $this->l('Save'),
                'stay' => true
			),
        );
        if (version_compare(_PS_VERSION_, '1.7.0.0', '>')) {
            unset($this->fields_form[0]['form']['input']['canonical']);
        }
    }
    
    protected function initForm()
	{
	    $helper = new HelperForm();
		$helper->show_toolbar = false;
        $helper->module = $this;
		$helper->table =  $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;

		$helper->identifier = $this->identifier;
		$helper->submit_action = 'save'.$this->name;
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);
		return $helper;
	}
    
    private function getConfigFieldsValues()
    {
        $fields_values = array(
            'category_in_product'         => Configuration::get($this->_prefix_st.'CATEGORY_IN_PRODUCT'),
            'categories_in_product'       => Configuration::get($this->_prefix_st.'CATEGORIES_IN_PRODUCT'),
            'categories_in_category'      => Configuration::get($this->_prefix_st.'CATEGORIES_IN_CATEGORY'),
            'redirect'                    => Configuration::get($this->_prefix_st.'REDIRECT'),
			'page_redirect_type'          => Configuration::get($this->_prefix_st.'PAGE_REDIRECT_TYPE'),
            'redirect_category'           => Configuration::get($this->_prefix_st.'REDIRECT_CATEGORY'),
            'remove_anchor'               => Configuration::get($this->_prefix_st.'REMOVE_ANCHOR'),
			'canonical'                   => Configuration::get($this->_prefix_st.'CANONICAL'),
            'advanced'                    => Configuration::get($this->_prefix_st.'ADVANCED'),
            'add_reference'               => Configuration::get($this->_prefix_st.'ADD_REFERENCE'),
        );
        $languages = Language::getLanguages(false);
        foreach($this->lang_field as $field) {
            $fields_values[$field] = array();
        }
        foreach ($languages as $language) {
            foreach($this->lang_field as $k => $v) {
                $fields_values[$k][$language['id_lang']] = Configuration::get($this->_prefix_st.strtoupper($k), $language['id_lang']);
            }
        }
        foreach($this->controller_map as $k => $v) {
            $fields_values['advanced_'.$v['id']] = Configuration::get($this->_prefix_st.strtoupper('advanced_'.$v['id']));
            $fields_values['page_'.$v['id']] = Configuration::get($this->_prefix_st.strtoupper('page_'.$v['id']));
        }
        return $fields_values;
    }
    
    public static function displayLang($value, $row)
    {
        static $langs = array();
        foreach (Language::getLanguages(false) as $lang)
        {
            $langs[$lang['id_lang']] = $lang['name'];
        }
        return key_exists($value, $langs) ? $langs[$value] : '-';
    }

    public static function displayButtons($controller, $row)
    {
        if (!$controller)
            return '-';
        $table = $row['identi'];
        $is_new_router = false;
        $router = '';

        if ($table == 'product' && version_compare(_PS_VERSION_, '1.7.0.0', '>')) {
            $is_new_router = true;
            $router = 'admin_product_form';
            $idt = 'id';
        } elseif ($table == 'category' && version_compare(_PS_VERSION_, '1.7.5.2', '>')) {
            $is_new_router = true;
            $router = 'admin_categories_edit';
            $idt = 'categoryId';
        } elseif ($table == 'cms' && version_compare(_PS_VERSION_, '1.7.6.0', '>')) {
            $is_new_router = true;
            $router = 'admin_cms_pages_edit';
            $idt = 'cmsPageId';
        }
        
        if ($is_new_router && $router) {
            $sfContainer = \PrestaShop\PrestaShop\Adapter\SymfonyContainer::getInstance();
            $url = $sfContainer->get('router')->generate($router, [$idt => $row['id']]); 
        } else {
            $url = Context::getContext()->link->getAdminLink('Admin'.$controller, true).'&id_'.$table.'='.$row['id'].'&update'.$table;
        }
        return '<a href="'.$url.'" target="_blank" class="edit btn btn-default"><i class="icon-pencil"></i>Edit</a>';
    }
    
    public function getDuplicated()
    {
        $res = array();
        $db = Db::getInstance();
        foreach($this->controller_map AS $table => $array)
        {
            if (in_array($table, ['st_blog', 'st_blog_category']) && !Module::isEnabled('stblog') || Configuration::get($this->_prefix_st.'ADD_REFERENCE') && $table == 'product') {
                continue;
            }
            $fileds = $db->executeS('DESC '._DB_PREFIX_.$table.'_lang');
            $is_id_shop = (int)Configuration::get('ST_'.strtoupper($table).'_IS_ID_SHOP');
            if (is_array($fileds) && count($fileds)) {
                $field = $array['field_rewrite'];
                $rows = $db->executeS('SELECT `'.$field.'`, count(0) num, id_lang'.($is_id_shop ? ',`id_shop`' : '').'
                    FROM '._DB_PREFIX_.$table.'_lang l
                    LEFT JOIN '._DB_PREFIX_.$table.' a
                    ON (a.id_'.$table.'=l.id_'.$table.')
                    WHERE '.($is_id_shop ? '`id_shop` = '.$this->context->shop->id : '1').'
                    GROUP BY `'.$field.'`,`id_lang`'.($is_id_shop ? ',`id_shop`' : '').'
                    HAVING num > 1
                    ');
                foreach($rows AS $row) {
                    $result = $db->executeS('SELECT a.id_'.$table.' id, CONCAT("'.$array['name'].'") as type, 
                        CONCAT("'.$table.'") as identi,
                        CONCAT("'.$array['bo_controller'].'") as buttons, 
                        `'.$field.'` as `name`,l.id_lang
                        FROM '._DB_PREFIX_.$table.'_lang l
                        LEFT JOIN '._DB_PREFIX_.$table.' a
                        ON (a.id_'.$table.'=l.id_'.$table.')
                        LEFT JOIN '._DB_PREFIX_.'lang nl
                        ON (l.id_lang=nl.id_lang)
                        WHERE `'.$field.'` = "'.$row[$field].'"
                        AND l.id_lang = '.(int)$row['id_lang'].'
                        AND nl.active = 1
                        '.($is_id_shop ? 'AND `id_shop`='.$row['id_shop'] : '').'
                        GROUP BY id
                        ORDER BY l.id_lang,`'.$field.'`');
                   $res = array_merge($res, $result);    
                }  
            }
        }
        return $res;
    }

    public function hookDisplayHeader($params)
    {
        if (!Configuration::get($this->_prefix_st.'CANONICAL') || version_compare(_PS_VERSION_, '1.7.0.0', '>')) {
            return;
        }
        $html = '';
        $controller = Dispatcher::getInstance()->getController();
        $id_lang = (int)$this->context->shop->id;
        if (key_exists($controller, $this->controller_map) && Tools::getValue('fc') != 'module') {
            $rewrite = Tools::getValue('rewrite');
            $id = Tools::getValue('id_'.$controller);
            $id && $rewrite && $html = '<link rel="canonical" href="'
            .$this->context->link->{$this->controller_map[$controller]['func']}($id, $rewrite).'" />';
        } elseif (Tools::getValue('fc') == 'module' && Tools::getValue('module') == 'stblog') {
            switch($controller)
            {
                case 'article':
                    $id = Tools::getValue('id_blog');
                    $rewrite = Tools::getValue('rewrite_blog_artilce');
                    $id && $rewrite && $html = '<link rel="canonical" href="'
                    . $this->context->link->getModuleLink('stblog', 'article',array('id_blog'=>$id,'rewrite'=>$rewrite)).'" />';
                    break;
                case 'category':
                    $id = Tools::getValue('blog_id_category');
                    $rewrite = Tools::getValue('rewrite_blog_category');
                    $id && $rewrite && $html = '<link rel="canonical" href="'
                    . $this->context->link->getModuleLink('stblog', 'category',array('blog_id_category'=>$id,'rewrite'=>$rewrite)).'" />';
                break;
                default;
            }
        } elseif($controller == 'index') {
            $html = '<link rel="canonical" href="'.$this->context->link->getPageLink('index').'" />';
        }
        return $html;
    }
    
    public function hookModuleRoutes($params)
    {
        $router_key = array();
        foreach($this->lang_field as $k => $v) {
            $router_key[$k] = $v;
        }
        $routers = array(
            'module-stblog-category' => array(
                'controller' =>     'category',
                'rule' =>        $router_key['router_blog'].'/{rewrite}/',
                'keywords' => array(
                    'id_st_blog_category' => array('regexp' => '[0-9]+'),// For 1.7
                    'blog_id_category' => array('regexp' => '[0-9]+'), // For 1.6
                    'rewrite'       =>   array('regexp' => '[_a-zA-Z0-9-\pL]*', 'param' => 'rewrite'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'stblog',
                )
            ),
            'module-stblog-article' => array(
                'controller' =>     'article',
                'rule' =>        $router_key['router_blog'].'/{rewrite}.html',
                'keywords' => array(
                    'id_st_blog' => array('regexp' => '[0-9]+'),// For 1.7
                    'id_blog' => array('regexp' => '[0-9]+'),// For 1.6
                    'rewrite'       =>   array('regexp' => '[_a-zA-Z0-9-\pL]*','param'=>'rewrite'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'stblog',
                )
            ),
            'module-stblogarchives-default' => array(
                'controller' =>  'default',
                'rule' =>        $router_key['router_blog'].'/{m}',
                'keywords' => array(
                    'm'            =>   array('regexp' => '[0-9]+', 'param' => 'm'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'stblogarchives',
                )
            ),
            'module-stblogsearch-default' => array(
                'controller' =>  'default',
                'rule' =>        $router_key['router_blog'].'/search',
                'keywords' => array(
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'stblogsearch',
                )
            ),
            'module-stblog-default' => array(
                'controller' =>  'default',
                'rule' =>        $router_key['router_blog'],
                'keywords' => array(
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'stblog',
                )
            ),
            'module-stblog-rss' => array(
                'controller' =>  'rss',
                'rule' =>        $router_key['router_blog'].'/rss',
                'keywords' => array(
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'stblog',
                )
            ),
            'module' => array(
                'controller' =>    null,
                'rule' =>        'module/{module}{/:controller}',
                'keywords' => array(
                    'module' =>        array('regexp' => '[_a-zA-Z0-9_-]+', 'param' => 'module'),
                    'controller' =>        array('regexp' => '[_a-zA-Z0-9_-]+', 'param' => 'controller'),
                ),
                'params' => array(
                    'fc' => 'module',
                ),
            ),
            'category_rule' => array(
                'controller' =>     'category',
                'rule' =>        '{rewrite}/',
                'keywords' => array(
                    'rewrite' =>        array('regexp' => '[_a-zA-Z0-9\pL\pS-]*', 'param' => 'rewrite'),
                    'meta_keywords' =>    array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'meta_title' =>        array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'id'    =>          array('regexp' => '[0-9]+'),
                ),
                'params' => array(
                ),
            ),
            'supplier_rule' => array(
                'controller' =>     'supplier',
                'rule' =>        $router_key['router_supplier'].'/{rewrite}',
                'keywords' => array(
                    'rewrite' =>        array('regexp' => '[_a-zA-Z0-9\pL\pS-]*', 'param' => 'rewrite'),
                    'meta_keywords' =>    array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'meta_title' =>        array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'id'    =>          array('regexp' => '[0-9]+'),
                ),
                'params' => array(
                ),
            ),
            'manufacturer_rule' => array(
                'controller' =>     'manufacturer',
                'rule' =>        $router_key['router_manufacturer'].'/{rewrite}',
                'keywords' => array(
                    'rewrite' =>        array('regexp' => '[_a-zA-Z0-9\pL\pS-]*', 'param' => 'rewrite'),
                    'meta_keywords' =>    array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'meta_title' =>        array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'id'    =>          array('regexp' => '[0-9]+'),
                ),
                'params' => array(
                ),
            ),
            'cms_rule' => array(
                'controller' =>     'cms',
                'rule' =>        $router_key['router_cms'].'/{rewrite}',
                'keywords' => array(
                    'rewrite' =>        array('regexp' => '[_a-zA-Z0-9\pL\pS-]*', 'param' => 'rewrite'),
                    'meta_keywords' =>    array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'meta_title' =>        array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'id'    =>          array('regexp' => '[0-9]+'),
                ),
                'params' => array(
                ),
            ),
            'cms_category_rule' => array(
                'controller' =>    'cms',
                'rule' =>        $router_key['router_cms'].'/category/{rewrite}',
                'keywords' => array(
                    'rewrite' =>        array('regexp' => '[_a-zA-Z0-9\pL\pS-]*', 'param' => 'rewrite'),
                    'meta_keywords' =>    array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'meta_title' =>        array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'id'    =>          array('regexp' => '[0-9]+'),
                ),
                'params' => array(
                    'category' => 1
                ),
            ),
            'module' => array(
                'controller' =>    null,
                'rule' =>        'module/{module}{/:controller}',
                'keywords' => array(
                    'module' =>        array('regexp' => '[_a-zA-Z0-9_-]+', 'param' => 'module'),
                    'controller' =>        array('regexp' => '[_a-zA-Z0-9_-]+', 'param' => 'controller'),
                ),
                'params' => array(
                    'fc' => 'module',
                ),
            ),
            'product_rule' => array(
                'controller' =>    'product',
                'rule' =>        '{category:/}{rewrite}.html',
                'keywords' => array(
                    'id' =>            array('regexp' => '[0-9]+'),
                    'id_product_attribute' => array('regexp' => '[0-9]+'),
                    'rewrite' =>        array('regexp' => '[_a-zA-Z0-9\pL\pS-]*?', 'param' => 'rewrite'),
                    'ean13' =>        array('regexp' => '[0-9\pL]*'),
                    'category' =>        array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'categories' =>        array('regexp' => '[/_a-zA-Z0-9-\pL]*'),
                    'reference' =>        array('regexp' => '[a-zA-Z0-9\pL\pS-]*'),
                    'meta_keywords' =>    array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'meta_title' =>        array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'manufacturer' =>    array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'supplier' =>        array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'price' =>            array('regexp' => '[0-9\.,]*'),
                    'tags' =>            array('regexp' => '[a-zA-Z0-9-\pL]*'),
                ),
                'params' => array(
                ),
            ),
            'layered_rule' => array(
                'controller' =>    'category',
                'rule' =>        '{rewrite}/{/:selected_filters}',
                'keywords' => array(
                    'id' =>            array('regexp' => '[0-9]+'),
                    /* Selected filters is used by the module blocklayered */
                    'selected_filters' =>    array('regexp' => '.*', 'param' => 'selected_filters'),
                    'rewrite' =>        array('regexp' => '[_a-zA-Z0-9\pL\pS-]+', 'param' => 'rewrite'),
                    'meta_keywords' =>    array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'meta_title' =>        array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                ),
                'params' => array(
                ),
            ),
        );
        $default_routes = array(
            'module-stblog-category' => array(
                'controller' =>  'category',
                'rule' =>        'blog/{id_st_blog_category}-{rewrite}',
                'keywords' => array(
                    'id_st_blog_category'  =>   array('regexp' => '[0-9]+', 'param' => 'id_st_blog_category'),
                    'rewrite'  =>   array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'stblog',
                )
            ),
            'module-stblog-article' => array(
                'controller' =>  'article',
                'rule' =>        'blog/{id_st_blog}_{rewrite}.html',
                'keywords' => array(
                    'id_st_blog'  =>   array('regexp' => '[0-9]+', 'param' => 'id_st_blog'),
                    'rewrite' =>   array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'stblog',
                )
            ),
            'category_rule' => array(
                'controller' =>    'category',
                'rule' =>        '{id}-{rewrite}',
                'keywords' => array(
                    'id' =>            array('regexp' => '[0-9]+', 'param' => 'id_category'),
                    'rewrite' =>        array('regexp' => '[_a-zA-Z0-9\pL\pS-]*'),
                    'meta_keywords' =>    array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'meta_title' =>        array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                ),
                'params' => array(
                ),
            ),
            'supplier_rule' => array(
                'controller' =>    'supplier',
                'rule' =>        '{id}__{rewrite}',
                'keywords' => array(
                    'id' =>            array('regexp' => '[0-9]+', 'param' => 'id_supplier'),
                    'rewrite' =>        array('regexp' => '[_a-zA-Z0-9\pL\pS-]*'),
                    'meta_keywords' =>    array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'meta_title' =>        array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                ),
                'params' => array(
                ),
            ),
            'manufacturer_rule' => array(
                'controller' =>    'manufacturer',
                'rule' =>        '{id}_{rewrite}',
                'keywords' => array(
                    'id' =>            array('regexp' => '[0-9]+', 'param' => 'id_manufacturer'),
                    'rewrite' =>        array('regexp' => '[_a-zA-Z0-9\pL\pS-]*'),
                    'meta_keywords' =>    array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'meta_title' =>        array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                ),
                'params' => array(
                ),
            ),
            'cms_rule' => array(
                'controller' =>    'cms',
                'rule' =>        'content/{id}-{rewrite}',
                'keywords' => array(
                    'id' =>            array('regexp' => '[0-9]+', 'param' => 'id_cms'),
                    'rewrite' =>        array('regexp' => '[_a-zA-Z0-9\pL\pS-]*'),
                    'meta_keywords' =>    array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'meta_title' =>        array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                ),
                'params' => array(
                ),
            ),
            'cms_category_rule' => array(
                'controller' =>    'cms',
                'rule' =>        'content/category/{id}-{rewrite}',
                'keywords' => array(
                    'id' =>            array('regexp' => '[0-9]+', 'param' => 'id_cms_category'),
                    'rewrite' =>        array('regexp' => '[_a-zA-Z0-9\pL\pS-]*'),
                    'meta_keywords' =>    array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'meta_title' =>        array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                ),
                'params' => array(
                ),
            ),
            'module' => array(
                'controller' =>    null,
                'rule' =>        'module/{module}{/:controller}',
                'keywords' => array(
                    'module' =>        array('regexp' => '[_a-zA-Z0-9_-]+', 'param' => 'module'),
                    'controller' =>        array('regexp' => '[_a-zA-Z0-9_-]+', 'param' => 'controller'),
                ),
                'params' => array(
                    'fc' => 'module',
                ),
            ),
            'product_rule' => array(
                'controller' =>    'product',
                'rule' =>        '{category:/}{id}{-:id_product_attribute}-{rewrite}{-:ean13}.html',
                'keywords' => array(
                    'id' =>            array('regexp' => '[0-9]+', 'param' => 'id_product'),
                    'id_product_attribute' => array('regexp' => '[0-9]+', 'param' => 'id_product_attribute'),
                    'rewrite' =>        array('regexp' => '[_a-zA-Z0-9\pL\pS-]*', 'param' => 'rewrite'),
                    'ean13' =>        array('regexp' => '[0-9\pL]*'),
                    'category' =>        array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'categories' =>        array('regexp' => '[/_a-zA-Z0-9-\pL]*'),
                    'reference' =>        array('regexp' => '[_a-zA-Z0-9-\pL\pS-]*'),
                    'meta_keywords' =>    array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'meta_title' =>        array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'manufacturer' =>    array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'supplier' =>        array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'price' =>            array('regexp' => '[0-9\.,]*'),
                    'tags' =>            array('regexp' => '[a-zA-Z0-9-\pL]*'),
                ),
                'params' => array(
                ),
            ),
            /* Must be after the product and category rules in order to avoid conflict */
            'layered_rule' => array(
                'controller' =>    'category',
                'rule' =>        '{id}-{rewrite}{/:selected_filters}',
                'keywords' => array(
                    'id' =>            array('regexp' => '[0-9]+', 'param' => 'id_category'),
                    /* Selected filters is used by the module blocklayered */
                    'selected_filters' =>    array('regexp' => '.*', 'param' => 'selected_filters'),
                    'rewrite' =>        array('regexp' => '[_a-zA-Z0-9\pL\pS-]*'),
                    'meta_keywords' =>    array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                    'meta_title' =>        array('regexp' => '[_a-zA-Z0-9-\pL]*'),
                ),
                'params' => array(
                ),
            ),
        );
        // To be compatible with the smartblog module.
        // If the smartblog module enaled, need to remove routers for our blog modules.
        if (Module::isInstalled('smartblog') && Module::isEnabled('smartblog')) {
            $smartblog = Module::getInstanceByName('smartblog');
            $sb_routes = $smartblog->hookModuleRoutes([]);
            $routers_new = array();
            if ($sb_routes) {
                foreach($routers as $key => $route) {
                    if (strpos($key, 'module-stblog') !== false) {
                        continue;
                    }
                    $routers_new[$key] = $route;
                    if ($key == 'module') {
                        $routers_new = array_merge($routers_new, $sb_routes);
                    }
                }
            }
            $routers = $routers_new;
            unset($routers_new);
        }
        
        if (!Configuration::get($this->_prefix_st.'CATEGORY_IN_PRODUCT')) {
            $routers['product_rule']['rule'] = '{rewrite}.html';
        }
        if (Configuration::get($this->_prefix_st.'CATEGORIES_IN_PRODUCT')) {
            $routers['product_rule']['rule'] = '{categories:/}{rewrite}.html';
            $routers['product_rule']['keywords']['categories'] = array('regexp' => '[/_a-zA-Z0-9-\pL]*');
        }
        if (Configuration::get($this->_prefix_st.'CATEGORIES_IN_CATEGORY')) {
            $routers['category_rule']['rule'] = '{categories:/}{rewrite}/';
            $routers['category_rule']['keywords']['categories'] = array('regexp' => '[/_a-zA-Z0-9-\pL]*');
            $routers['layered_rule']['rule'] = '{categories:/}{rewrite}/{/:selected_filters}';
            $routers['layered_rule']['keywords']['categories'] = array('regexp' => '[/_a-zA-Z0-9-\pL]*');
        }
        // Remove .html and slash from product and category URLs.
        if (Configuration::get($this->_prefix_st.'ADVANCED')) {
            foreach($this->getControllerMap() as $c => $v) {
                if (isset($routers[$v['route_id']])) {
                    $routers[$v['route_id']]['rule'] = str_replace($this->lang_field, '', $routers[$v['route_id']]['rule']);
                    $routers[$v['route_id']]['rule'] = rtrim($routers[$v['route_id']]['rule'], '.html');
                    $routers[$v['route_id']]['rule'] = trim($routers[$v['route_id']]['rule'], '/');
                }
            }
            $routers['layered_rule']['rule'] = $routers['category_rule']['rule'].'{/:selected_filters}';
        }
        if (Configuration::get($this->_prefix_st.'ADD_REFERENCE')) {
            $routers['product_rule']['keywords']['reference']['param'] = 'reference';
            $routers['product_rule']['rule'] = str_replace('{rewrite}', '{rewrite}-p-{reference}', $routers['product_rule']['rule']);
        }
        // Change route via the selected pages.
        if (!empty($params)) {
            // Fix blog route
            if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
                $default_routes['module-stblog-category']['rule'] = 'blog/{blog_id_category}-{rewrite}';
                $default_routes['module-stblog-category']['keywords']['blog_id_category'] = array('regexp' => '[0-9]+', 'param' => 'blog_id_category');
                unset($default_routes['module-stblog-category']['keywords']['id_st_blog_category']);

                $default_routes['module-stblog-article']['rule'] = 'blog/{id_blog}_{rewrite}.html';
                $default_routes['module-stblog-article']['keywords']['id_blog'] = array('regexp' => '[0-9]+', 'param' => 'id_blog');
                unset($default_routes['module-stblog-article']['keywords']['id_st_blog']);
                $default_routes['product_rule']['rule'] = str_replace('{-:id_product_attribute}', '', $default_routes['product_rule']['rule']);
                unset($default_routes['product_rule']['keywords']['id_product_attribute']);
            }
            $selected_pages = array();
            foreach($this->controller_map as $k => $v) {
                if (Configuration::get($this->_prefix_st.strtoupper('page_'.$v['id']))) {
                    $selected_pages[$k] = $v;
                }
            }
            foreach($this->controller_map as $key => $val) {
                if(!array_key_exists($key, $selected_pages)) {
                    $routers[$val['route_id']] = $default_routes[$val['route_id']];
                }
            }
        }
        if (!Module::isEnabled('stblog')) {
            foreach($routers as $key => $value) {
                if (strpos($key, '-stblog') !== false) {
                    unset($routers[$key]);
                }
            }
        }
        return $routers;
    }
    
    public function hookActionDispatcherBefore($params)
    {
        $dispatcher = Dispatcher::getInstance();
        // Use Advanced dispather
        if (Configuration::get($this->_prefix_st.'ADVANCED')) {//print_r($_GET);die;
            $controller_map = $this->getControllerMap();
            $adv_table = '';
            foreach($controller_map as $table => $value) {
                $adv_table = $table;
                break;
            }
            if ($adv_table && Tools::getvalue('controller') == $adv_table && !Tools::getValue('id_'.$adv_table) && Tools::getValue('fc') != 'module') {
                if (!$rewrite = Tools::getvalue('rewrite')) {
                    return;
                }
                $found = false;
                foreach($controller_map as $table => $value) {
                    // if not proudct, but has the reference, should add it to the rewrite
                    if (($reference = Tools::getValue('reference')) && $table != 'product') {
                        $rewrite .= '-'.$reference;
                    }
                    if ($table == 'cms' && Tools::getValue('category') == 1) {
                        $table = 'cms_category';
                    }
                    if ($id = $this->getInstanceId($table, $rewrite, $value['field_rewrite'])) {
                        $_GET['id_'.$table] = $id;
                        $route = $dispatcher->default_routes[$value['route_id']];
                        $_GET['controller'] = $route['controller'];
                        foreach($route['params'] as $k => $v) {
                            $_GET[$k] = $v;
                        }
                        if (isset($_GET['fc']) && $_GET['fc'] == 'module') {
                            $dispatcher->setFrontController(Dispatcher::FC_MODULE);
                        }
                        // Add id_product_attibute less 1.7.4.0
                        if (version_compare(_PS_VERSION_, '1.7.0.0', '>') && $table == 'product') {
                            $this->setIdProductAttribute($id);
                        }
                        $dispatcher->setController($route['controller']);
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $_GET['controller'] = 'pagenotfound';
                    $dispatcher->setController('pagenotfound');
                }
            }
        }
    }

    public function getInstanceId($table, $rewrite, $field)
    {
        if (!$table || !$rewrite || !$field) {
            return false;
        }
        $has_ref = false;
        $sig = sha1($table.$rewrite);
        if ($table == 'product' && ($reference = Tools::getValue('reference'))) {
            $has_ref = true;
            // match a special code in the reference
            $reference = str_replace('-', '_', $reference);
            $sig = sha1($table.$rewrite.$reference);
        }
        $id_parent = array();
        if ($table == 'category' && Configuration::get($this->_prefix_st.'CATEGORIES_IN_CATEGORY')) {
            // Get parent category
            $array = explode('/', trim($this->getRequestUri(), '/'));
            $parent_name = array_pop($array);
            $parent_name = @array_pop($array);
            $top_parent_name = @array_pop($array);
            if ($parent_name) {
                if ($top_parent_name) {
                    $top_parent_id = Db::getInstance()->getValue('SELECT DISTINCT a.`id_category`
                        FROM '._DB_PREFIX_.'category_lang l
                        LEFT JOIN '._DB_PREFIX_.'category a
                        ON (a.id_category=l.id_category)
                        WHERE `link_rewrite` = "'.$top_parent_name.'"
                        AND a.`active` = 1
                        AND `id_lang` = '.(int)$this->context->language->id
                    );
                }
                $array = Db::getInstance()->executeS('SELECT DISTINCT a.`id_category`
                    FROM '._DB_PREFIX_.'category_lang l
                    LEFT JOIN '._DB_PREFIX_.'category a
                    ON (a.id_category=l.id_category)
                    WHERE `link_rewrite` = "'.$parent_name.'"
                    AND a.`active` = 1'.
                    ($top_parent_name ? ' AND `id_parent` = ' . (int)$top_parent_id : '').'
                    AND `id_lang` = '.(int)$this->context->language->id
                );
                foreach($array as $val) {
                    $id_parent[] = $val['id_category'];
                }
            } else {
                $root_category = Category::getRootCategory();
                if ($root_category->link_rewrite != $rewrite) {
                    $id_parent[] = $root_category->id;    
                }
            }
            if ($id_parent) {
                $sig = sha1($table.$rewrite.implode('-', $id_parent));
            }
        }
        if ($id = (int)Configuration::get($this->_prefix_st.'sha1_'.$sig)) {
            return $id;
        }
        $is_id_shop = (int)Configuration::get('ST_'.strtoupper($table).'_IS_ID_SHOP');
        if ($field == 'name') {
            // One manufacturer
            $tmp = Db::getInstance()->executeS('SELECT a.`id_'.$table.'` AS id, `name`
            FROM '._DB_PREFIX_.$table.'_lang l
            LEFT JOIN '._DB_PREFIX_.$table.' a
            ON (a.id_'.$table.'=l.id_'.$table.')
            WHERE `id_lang` = '.(int)$this->context->language->id.'
            '.($is_id_shop ? 'AND id_shop='.(int)$this->context->shop->id : '').'
            GROUP BY `name`');
            foreach($tmp AS $value) {
                if (Tools::str2url($value['name']) == $rewrite) {
                    Configuration::updateValue($this->_prefix_st.'sha1_'.$sig, (int)$value['id']);
                    return (int)$value['id'];
                }
            }
        } else {
            $id = Db::getInstance()->getValue('SELECT a.`id_'.$table.'`
                FROM '._DB_PREFIX_.$table.'_lang l
                LEFT JOIN '._DB_PREFIX_.$table.' a
                ON (a.id_'.$table.'=l.id_'.$table.')
                WHERE `'.$field.'` = "'.$rewrite.'"
                AND `id_lang` = '.(int)$this->context->language->id.'
                '.($is_id_shop ? 'AND id_shop='.(int)$this->context->shop->id : '').'
                '.($has_ref ? 'AND reference="'.$reference.'"' : '').'
                '.($id_parent ? 'AND id_parent IN ('.implode(',',$id_parent).')' : '').'
                ');
            if ($id) {
                Configuration::updateValue($this->_prefix_st.'sha1_'.$sig, (int)$id);
                return $id;
            }
        }
        return false;
    }

    public function getControllerMap()
    {
        if (!Configuration::get($this->_prefix_st.'ADVANCED')) {
            return $this->selected_controller;
        }
        if ($this->selected_controller) {
            return $this->selected_controller;
        }
        foreach($this->controller_map as $k => $v) {
            // Add some filters here
            if (Configuration::get($this->_prefix_st.strtoupper('advanced_'.$v['id']))) {
                $this->selected_controller[$k] = $v;
            }
        }
        return $this->selected_controller;
    }

    public function hookActionDispatcher($params)
    {
        static $array_ids = array();
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_);
        if ($params['controller_type'] == 1 && !$params['is_module']) {
            $controller = Dispatcher::getInstance()->getController();
            $id_lang = (int)$this->context->shop->id;
            
            if ($controller == 'index' || $controller == 'pagenotfound' || $controller == '404') {
                $redirect = Configuration::get($this->_prefix_st.'REDIRECT');
                if ($controller == 'pagenotfound' || $controller == '404') {
                    $this->doRedirection($redirect);
                }
                if (($controller == 'pagenotfound' || $controller == '404') && Configuration::get($this->_prefix_st.'PAGE_REDIRECT_TYPE')) {
                    if ($id_category = Configuration::get($this->_prefix_st.'REDIRECT_CATEGORY')) {
                        $category = new Category($id_category, $this->context->language->id);
                        $root_category = Category::getRootCategory();
                        if ($root_category->id == $id_category) {
                            $url = $this->context->link->getPageLink('index');
                        } else {
                            $url = $this->context->link->getCategoryLink($id_category, $category->link_rewrite);
                        }
                        Tools::redirect($url, '', null, $redirect == 1 ? 'HTTP/1.1 301 Moved Permanently' : 'HTTP/1.1 302 Moved Temporarily');
                    }
                }
                return;
            }
            // Add id_product_attibute to be compatible with less 1.7.4.0
            if (version_compare(_PS_VERSION_, '1.7.0.0', '>') 
                && $controller == 'cart' 
                && ($id_product = Tools::getValue('id_product')) 
                && ($group = Tools::getValue('group'))) {
                unset($_GET['group']);
                $this->context->cookie->st_id_ipa = (int)Product::getIdProductAttributesByIdAttributes((int)$id_product, $group, true);
                $this->context->cookie->write();
            }
            
            if (key_exists($controller, $this->controller_map)) {
                // Old url to new, specially for the Advanched dispatcher
                if ($filter = Tools::getValue('selected_filters')) {
                    $str = @array_pop(explode('/', $filter));
                    if (preg_match('/^(\d+)([-_]{0,2})(.+)/i', $str)) {
                        $this->doRedirection(Configuration::get($this->_prefix_st.'REDIRECT'));
                    }
                }
                
                $rewrite = Tools::getValue('rewrite');
                $table = $controller;
                $field = $this->controller_map[$controller]['field_rewrite'];
                $is_id_shop = (int)Configuration::get('ST_'.strtoupper($table).'_IS_ID_SHOP');
                // For cms category only.
                if ($table == 'cms' && strpos($this->getRequestUri(), '/category/') !== false) {
                    $table = 'cms_category';
                }
                $key = 'id_'.$table.'_'.md5($rewrite);
                if (key_exists($key, $array_ids)) {
                    $_GET['id_'.$table] = $array_ids[$key];
                    return;
                }
                // Add id_product_attribute when changing combination.
                if (Tools::getValue('controller') == $controller && Tools::getValue('id_'.$controller)) {
                    if (version_compare(_PS_VERSION_, '1.7.0.0', '>') 
                        && $controller == 'product' 
                        && ($group = Tools::getValue('group'))) {
                        $this->setIdProductAttribute((int)Tools::getValue('id_'.$controller));
                    }
                    return;
                }
                
                if ($field == 'name') {
                    // All manufacturers
                    if (!$rewrite) {
                        return;
                    }
                    // One manufacturer
                    if ($id = $this->getInstanceId($table, $rewrite, $field)) {
                        $_GET['id_'.$table] = (int)$id;
                        $array_ids[$key] = (int)$id;
                        return;
                    }
                } else {
                    if ($table == 'cms' && Tools::getValue('category') == 1) {
                        $table = 'cms_category';
                    }
                    if ($id = $this->getInstanceId($table, $rewrite, $field)) {
                        $_GET['id_'.$table] = $id;
                        $array_ids[$key] = $id;
                        // Add id_product_attibute less 1.7.4.0
                        if (version_compare(_PS_VERSION_, '1.7.0.0', '>') && $table == 'product') {
                            $this->setIdProductAttribute($id);
                        }
                        return;
                    }
                }
                if ($redirect = Configuration::get($this->_prefix_st.'REDIRECT')) {
                    $id_new = $rewrite_new = '';
                    // require_uri={id}-{rewrite}|{id}_{rewrite}|{id}{rewrite}|{rewrite}-{id}|{rewrite}_{id}|{rewrite}{id}
                    if (preg_match('/^(\d+)([-_]{0,2})(.*)/i', $rewrite, $match) && $match[1] && $match[3]) {
                        $id_new = $match[1];
                        $rewrite_new = $match[3];
                    } elseif (preg_match('/(.*)([-_]){0,2}(\d+)$/i', $rewrite, $match) && $match[1] && $match[3]) {
                        $id_new = $match[3];
                        $rewrite_new = $match[1];
                    }
                    if ($id_new && $rewrite_new) {
                        $func = $this->controller_map[$controller]['func'];
                        $url = $this->context->link->$func($id_new, $rewrite_new);
                        Tools::redirect($url, '', null, $redirect == 1 ? 'HTTP/1.1 301 Moved Permanently' : 'HTTP/1.1 302 Moved Temporarily');   
                    } else {
                        $this->doRedirection($redirect);
                    }
                }
                if (Configuration::get($this->_prefix_st.'PAGE_REDIRECT_TYPE')) {
                    if ($id_category = Configuration::get($this->_prefix_st.'REDIRECT_CATEGORY')) {
                        $category = new Category($id_category, $this->context->language->id);
                        $url = $this->context->link->getCategoryLink($id_category, $category->link_rewrite);
                        Tools::redirect($url, '', null, $redirect == 1 ? 'HTTP/1.1 301 Moved Permanently' : 'HTTP/1.1 302 Moved Temporarily');
                    }
                }
                Tools::redirect($this->context->link->getPageLink('pagenotfound'));
            }
        } elseif ($params['controller_type'] == 1 && $params['is_module']) {
            switch($params['controller_class']) {
                case 'stblogarticleModuleFrontController':
                    if ($rewrite = Tools::getValue('rewrite')) {
                        $id_st_blog = Db::getInstance()->getValue('
                        SELECT bl.id_st_blog FROM '._DB_PREFIX_.'st_blog_lang bl
                        INNER JOIN '._DB_PREFIX_.'st_blog_shop bs
                        ON(bl.`id_st_blog` = bs.`id_st_blog`)
                        WHERE link_rewrite = "'.$rewrite.'"
                        AND id_lang = '.(int)Context::getContext()->language->id.'
                        AND id_shop = '.(int)$this->context->shop->id.'
                        ');
                        if ($id_st_blog) {
                            if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
                                $_GET['id_blog'] = $id_st_blog;
                            } else {
                                $_GET['id_st_blog'] = $id_st_blog;
                            }
                        } else {
                            $this->doRedirection(Configuration::get($this->_prefix_st.'REDIRECT'));
                            Tools::redirect($this->context->link->getPageLink('pagenotfound'));
                        }
                            
                    }
                    break;
                case 'stblogcategoryModuleFrontController':
                    if ($rewrite = Tools::getValue('rewrite')) {
                        $id_st_blog_category = Db::getInstance()->getValue('
                        SELECT l.id_st_blog_category FROM '._DB_PREFIX_.'st_blog_category_lang l
                        INNER JOIN '._DB_PREFIX_.'st_blog_category_shop s
                        ON(l.`id_st_blog_category` = s.`id_st_blog_category`)
                        WHERE link_rewrite = "'.$rewrite.'"
                        AND id_lang = '.(int)Context::getContext()->language->id.'
                        AND id_shop = '.(int)$this->context->shop->id.'
                        ');
                        if ($id_st_blog_category) {
                            if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
                                $_GET['blog_id_category'] = $id_st_blog_category;
                            } else {
                                $_GET['id_st_blog_category'] = $id_st_blog_category;
                            }
                        } else {
                            $this->doRedirection(Configuration::get($this->_prefix_st.'REDIRECT'));
                            Tools::redirect($this->context->link->getPageLink('pagenotfound'));
                        }
                    }
                    break;
                default;
            }
        }
    }

    public function doRedirection($redirect = 0)
    {
        // Redirect old url to new
        $uri = $this->getRequestUri();
        if (preg_match('/\.(gif|jpe?g|png|css|js|ico|eot|woff|woff2|ttf|svg)$/i', parse_url($uri, PHP_URL_PATH))) {
            return;
        }
        if ($redirect) {
            $regexp_old = array(
                'st_blog' => '#^/blog/(?P<id_st_blog>[0-9]+)\_(?P<rewrite>[_a-zA-Z0-9\pL\pS-]*).html$#u',
                'st_blog_category' => '#^/blog/(?P<id_st_blog_category>[0-9]+)\-(?P<rewrite>[_a-zA-Z0-9\pL\pS-]*)$#u',
                'category' => '#^/(?P<id_category>[0-9]+)\-([_a-zA-Z0-9\pL\pS-]*)$#u',
                'supplier' => '#^/(?P<id_supplier>[0-9]+)__([_a-zA-Z0-9\pL\pS-]*)$#u',
                'manufacturer'=> '#^/(?P<id_manufacturer>[0-9]+)_([_a-zA-Z0-9\pL\pS-]*)$#u',
                'cms' => '#^/content/(?P<id_cms>[0-9]+)\-([_a-zA-Z0-9\pL\pS-]*)$#u',
                'product' => '#^/(([_a-zA-Z0-9-\pL]*)/)?(?P<id_product>[0-9]+)\-([_a-zA-Z0-9\pL\pS-]*)(\-([0-9\pL]*))?\.html$#u'
            );
            if (version_compare(_PS_VERSION_, '1.7.0.0', '>')) {
                $regexp_old['supplier'] = '#^/supplier/(?P<id_supplier>[0-9]+)\-([_a-zA-Z0-9\pL\pS-]*)$#u';
                $regexp_old['manufacturer'] = '#^/brand/(?P<id_manufacturer>[0-9]+)\-([_a-zA-Z0-9\pL\pS-]*)$#u';
            }
            foreach($regexp_old as $c => $reg) {
                if (preg_match($reg, $uri, $m)) {
                    if (isset($m['id_'.$c]) && $m['id_'.$c]) {
                        if ($c == 'st_blog' || $c == 'st_blog_category') {
                            $url = $this->context->link->getModuleLink('stblog', $c == 'st_blog'?'article':'category', ['id_'.$c=>$m['id_'.$c], 'rewrite'=>$m['rewrite']]);
                        } else {
                            $func = $this->controller_map[$c]['func'];
                            $url = $this->context->link->$func($m['id_'.$c]);    
                        }
                        Tools::redirect($url, '', null, $redirect == 1 ? 'HTTP/1.1 301 Moved Permanently' : 'HTTP/1.1 302 Moved Temporarily');   
                    }
                }
            }
        }
    }
    
    public function checkFields()
    {
        foreach($this->controller_map AS $table => $value) {
            if (in_array($table, ['st_blog', 'st_blog_category']) && !Module::isEnabled('stblog')) {
                continue;
            }
            $is_id_shop = (int)Configuration::get('ST_'.strtoupper($table).'_IS_ID_SHOP');
            if (!$is_id_shop) {
                $fields = Db::getInstance()->executeS('DESC '._DB_PREFIX_.$table.'_lang id_shop');
                if(is_array($fields) && count($fields)) {
                    Configuration::updateValue('ST_'.strtoupper($table).'_IS_ID_SHOP', 1);
                }    
            }
        }
        return true;
    }
    
    public function createCategories()
    {
        $id_lang = $this->context->language->id;
        $category_arr = array();
		$this->getCategoryOption($category_arr, Category::getRootCategory()->id, (int)$id_lang, (int)Shop::getContextShopID(),true);
        return $category_arr;
    }
    
    private function getCategoryOption(&$category_arr, $id_category = 1, $id_lang = false, $id_shop = false, $recursive = true)
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
		$category_arr[] = array('id'=>(int)$category->id,'name'=>(isset($spacer) ? $spacer : '').$category->name);

		if (isset($children) && is_array($children) && count($children))
			foreach ($children as $child)
			{
				$this->getCategoryOption($category_arr, (int)$child['id_category'], (int)$id_lang, (int)$child['id_shop'],$recursive);
			}
	}

    public function clearCustomRoutes()
    {
        $res = true;
        foreach(Dispatcher::getInstance()->default_routes as $route_id => $route) {
            $res &= Configuration::deleteByName('PS_ROUTE_'.$route_id);
        }
        return $res;
    }

    public function getCustomRoutes()
    {
        $rs = array();
        foreach(Dispatcher::getInstance()->default_routes as $route_id => $route) {
            if ($route = Configuration::get('PS_ROUTE_'.$route_id, null, null, $this->context->shop->id)) {
                $rs[] = $route;
            }
        }
        return $rs;

    }

    public function getRequestUri()
    {
        // Get request uri (HTTP_X_REWRITE_URL is used by IIS)
        if (isset($_SERVER['REQUEST_URI'])) {
            $request_uri = $_SERVER['REQUEST_URI'];
        } elseif (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
            $request_uri = $_SERVER['HTTP_X_REWRITE_URL'];
        }
        $request_uri = rawurldecode($request_uri);

        if (isset(Context::getContext()->shop) && is_object(Context::getContext()->shop)) {
            $request_uri = preg_replace(
                '#^'.preg_quote(Context::getContext()->shop->getBaseURI(), '#').'#i',
                '/',
                $request_uri
            );
        }

        // If there are several languages, get language from uri
        if (Language::isMultiLanguageActivated()) {
            if (preg_match('#^/([a-z]{2})(?:/.*)?$#', $request_uri, $m)) {
                $request_uri = substr($request_uri, 3);
            }
        }
        if (strpos($request_uri, '?') !== false) {
            $request_uri = substr($request_uri, 0, strpos($request_uri, '?'));
        }
        return $request_uri;
    }

    public function setIdProductAttribute($id_product)
    {
        if ($id_product) {
            if (isset($this->context->cookie->st_id_ipa) && $this->context->cookie->st_id_ipa > 0) {
                $_GET['id_product_attribute'] = (int)$this->context->cookie->st_id_ipa;
                $this->context->cookie->st_id_ipa = 0;
                $this->context->cookie->write();
            } elseif ($group = Tools::getValue('group')) {
                $_GET['id_product_attribute'] = (int)Product::getIdProductAttributesByIdAttributes($id_product, $group, true);
                unset($_GET['group']);
            }   
        }
    }
}
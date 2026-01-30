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

class StWebp extends Module
{
    public  $fields_form;
	private $_html = '';
    public $_prefix_st = 'ST_WEBP_';
    public $validation_errors = array();
    public $process = array(
            'categories' => array('dir' => _PS_CAT_IMG_DIR_, 'no_pic'=>1, 'in_db' => 1),
            'manufacturers' => array('dir' => _PS_MANU_IMG_DIR_, 'no_pic'=>1, 'in_db' => 1),
            'suppliers' => array('dir' => _PS_SUPP_IMG_DIR_, 'no_pic'=>1, 'in_db' => 1),
            'products' => array('dir' => _PS_PROD_IMG_DIR_, 'no_pic'=>1, 'in_db' => 1),
            'stores' => array('dir' => _PS_STORE_IMG_DIR_, 'no_pic'=>0, 'in_db' => 1),
            'articles' => array('dir' => _PS_UPLOAD_DIR_.'stblog/', 'no_pic'=>0, 'in_db' => 0),
            'stbanner' => array('dir' => _PS_UPLOAD_DIR_.'stbanner/', 'no_pic'=>0, 'in_db' => 0),
            'stswiper' => array('dir' => _PS_UPLOAD_DIR_.'stswiper/', 'no_pic'=>0, 'in_db' => 0),
            'stowlcarousel' => array('dir' => _PS_UPLOAD_DIR_.'stowlcarousel/', 'no_pic'=>0, 'in_db' => 0),
        );
    private $_st_is_16;
	function __construct()
	{
		$this->name          = 'stwebp';
		$this->tab           = 'front_office_features';
		$this->version       = '1.0.2';
		$this->author        = 'SUNNYTOO.COM';
		$this->need_instance = 0;
        $this->bootstrap     = true;
        
		parent::__construct();

		$this->displayName = $this->l('WebP module');
		$this->description = $this->l('Use webp to speed your site up.');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->_st_is_16      = Tools::version_compare(_PS_VERSION_, '1.7');
	}
    
	function install()
	{
		$res = parent::install()
            && $this->registerHook('displayHeader')
            && $this->registerHook('actionProductSearchAfter')
            && $this->registerHook('actionProductSearchComplete')
            && Configuration::updateValue($this->_prefix_st.'BACKGROUND_COLOR', '#ffffff')
            && Configuration::updateValue($this->_prefix_st.'ENABLE_WEBP', 0)
            && Configuration::updateValue($this->_prefix_st.'WEBP_QUALITY', 90)
            && Configuration::updateValue($this->_prefix_st.'PNG_QUALITY', 7)
            && Configuration::updateValue($this->_prefix_st.'JPEG_QUALITY', 90)
            && Configuration::updateValue($this->_prefix_st.'WEBP_COMPATIBILITY', false)
            && Configuration::updateValue($this->_prefix_st.'WEBP_TYPE', 0)
            && Configuration::updateValue($this->_prefix_st.'CROP', 0)
            && Configuration::updateValue($this->_prefix_st.'ERASE', 0)
            && Configuration::updateValue($this->_prefix_st.'PER_TIME', 3)
            && Configuration::updateValue($this->_prefix_st.'MET', 0)
            && Configuration::updateValue($this->_prefix_st.'THUMB_FORMAT', 0)
            ;
        $this->initImageTypes();
        $this->generateHtaccess();
        if (Configuration::get('TB_USE_WEBP'))
            Configuration::updateValue($this->_prefix_st.'ENABLE_WEBP', 1);

        return (bool)$res;
	}

	public function initImageTypes()
	{
        $imagesTypes = ImageType::getImagesTypes();
        foreach ($imagesTypes as $type) {
            // if(!in_array($type['name'], array('cart_default','cart_default_2x','small_default','small_default_2x','category_default','category_default_2x','brand_default','brand_default_2x')))
                Configuration::updateValue($this->_prefix_st.strtoupper('webp_image_type_'.$type['name']), 1);
        }
        return true; 
	}
    public function uninstall()
    {
        return parent::uninstall(); 
    }

	public function getContent()
	{
        if(!function_exists('imagewebp')){
            $this->_html .= $this->displayError(
                $this->l('Oops, webp extention isn\'t installed on your server, contact your server provider to install it. Although PHP has built in support for WebP since PHP 5.5, but for some reason, webp isn\'t installed on all web servers.')
            );
        }else{
        Media::addJsDef(array(
            'st_re_generate_warning_1' => $this->l('Are you sure, you want to generate thumbnails from the beginning?'),
            'st_re_generate_warning_2' => $this->l('Are you sure, you want to erase images?'),
            'st_re_generate_warning_3' => $this->l('An error occured, try increasing the "Max execution time" field and setting the "How may images per time" field to 1.'),
        ));
        $this->context->controller->addCSS(($this->_path).'views/css/admin.css');
        $this->context->controller->addJS(($this->_path).'views/js/admin.js');
        $this->initFieldsForm();

        if(Tools::getValue('stgenerate'))
        {
            $im_data = $this->stGenerate(array(
                'erase' => Tools::getValue('erase'),
                'thumb_format' => Tools::getValue('thumb_format'),
                // 'start_id' => (int)Tools::getValue('start_id'),
                // 'end_id' => (int)Tools::getValue('end_id'),
                'per_time' => (int)Tools::getValue('per_time'),
                'met' => (int)Tools::getValue('met'),
                'fenlei' => Tools::getValue('fenlei'),
                'image_type' => Tools::getValue('image_type') ? explode(",", Tools::getValue('image_type')) : '',
                'reg' => Tools::getValue('reg'),
            ));
            die(json_encode($im_data));
        }
        if (Tools::getValue('act') == 'gen_htaccess') {
            if ($this->generateHtaccess()) {
                $this->_html .= $this->displayConfirmation($this->l('Modify .htaccess success.'));
            } else {
                $this->_html .= $this->displayError($this->l('Modify .htaccess failed, please make sure the /.htaccess file and the /img/.htaccess file are writeable.'));
            }
        }
	    if (Tools::isSubmit('save'.$this->name)) {
            foreach($this->fields_form as $form)
                foreach($form['form']['input'] as $field)
                    if(isset($field['validation']))
                    {
                        $errors = array();       
                        $value = Tools::getValue($field['name']);
                        if (isset($field['required']) && $field['required'] && $value==false && (string)$value != '0')
        						$errors[] = Tools::displayError(sprintf('Field "%s" is required.', $field['label']));
                        elseif($value)
                        {
                            $field_validation = $field['validation'];
        					if (!Validate::$field_validation($value)) {
        						$errors[] = Tools::displayError(sprintf('Field "%s" is invalid.', $field['label']));
                            }
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

            foreach (ImageType::getImagesTypes() as $k=>$type) {
                Configuration::updateValue($this->_prefix_st.strtoupper('webp_image_type_'.$type['name']), ($this->_st_is_16 || Tools::getValue('webp_image_type_'.$type['name'])) ? 1 : 0);
            }
            $this->_synWithTB();

            if (count($this->validation_errors)) {
                $this->_html .= $this->displayError(implode('<br/>', $this->validation_errors));
            } else {
                $this->_html .= $this->displayConfirmation($this->l('Settings update.'));
            }
        }
		$helper = $this->initForm();

        $this->_html .= $helper->generateForm($this->fields_form);
        }
        return $this->_html;
	}
    private function _synWithTB()
    {
        if ($this->_st_is_16 && defined('_TB_VERSION_'))
            Configuration::updateValue('TB_USE_WEBP', Configuration::get($this->_prefix_st.'ENABLE_WEBP'));
    }
    public function initFieldsForm()
    {
        $status = $this->getIndexationStatus();
        $this->fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('General settings'),
                'icon'  => 'icon-cogs',
            ),
            'input' => array(
                array(
                    'type' => 'switch',
                    'label' => $this->l('Start to use webp'),
                    'name' => 'enable_webp',
                    'default_value' => 0,
                    'values' => array(
                        array(
                            'id' => 'enable_webp_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'enable_webp_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                    'validation' => 'isBool',
                    'desc' => array(
                        $this->l('Don\'t enable this option if you\'ve generated .webp thumbnails yet, otherwise images on the front office will not show out.'),
                        $this->l('Enable this settig when you finish generating .webp thumbnails.'),
                    ),
                ),
                array(
                    'type' => 'html',
                    'id' => '',
                    'label' => '',
                    'name' => sprintf($this->l('If images don\'t show out, they show 403 or 404 error, then click this button to %s modify the .htaccess in the img folder %s to fix the problem.'), '<a class="btn btn-default btn-primary" href="'.AdminController::$currentIndex.'&configure='.$this->name.'&act=gen_htaccess&token='.Tools::getAdminTokenLite('AdminModules').'">', '</a>'),
                ),
                array(
                    'type' => 'color',
                    'label' => $this->l('Image background color'),
                    'name' => 'background_color',
                    'class' => 'color',
                    'default_value' => '#ffffff',
                    'size' => 20,
                    'validation' => 'isColor',
                    'desc' => $this->l('Fill background color when resizing image, default is white color.')
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Webp quality'),
                    'name' => 'webp_quality',
                    'class'      => 'fixed-width-lg',
                    'default_value' => 90,
                    'validation' => 'isUnsignedInt',
                    'desc' => $this->l('90 is recommended.'),
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->l('Use webp for'),
                    'name' => 'webp_type',
                    'default_value' => 0,
                    'values' => array(
                        array(
                            'id' => 'webp_type_0',
                            'value' => 0,
                            'label' => $this->l('Product images, category images, supplier images, manufacturer images and blog images'),
                        ),
                        array(
                            'id' => 'webp_type_1',
                            'value' => 1,
                            'label' => $this->l('The option above + banners, owl sliders and swiper sliders'),
                        ),
                    ),
                    'validation' => 'isUnsignedInt',
                ),
                'webp_image_type' =>array(
                    'type' => 'checkbox',
                    'label' => $this->l('Specific some image types to use webp'),
                    'name' => 'webp_image_type',
                    'class' => 'webp_image_type',
                    'values' => array(
                        'query' => array(),
                        'id' => 'id',
                        'name' => 'name'
                    ),
                    'desc' => array(
                        $this->l('It\'s recommended to select all of them.'),
                        $this->l('Use webp images for checked image types, that will apply to Products, Categories, Manufacturers, Suppliers and Stores.'),
                    ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Croping images instead of resizing'),
                    'name' => 'crop',
                    'default_value' => 0,
                    'values' => array(
                        array(
                            'id' => 'crop_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'crop_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                    'desc'       => array(
                        $this->l('Croping thumbnails instead of resizing.'),
                    ),
                    'validation' => 'isBool',
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'stay' => true,
            ),
        );
        $this->fields_form[1]['form'] = array(
            'legend' => array(
                'title' => $this->l('Ajax generator thumbnails'),
                'icon'  => 'icon-cogs'
            ),
            'description' => $this->l('Generate thumbnails via ajax to avoid timeout error. Settings in this section take effect immediately without saving, if you save them, they will be remembered.'),
            'input' => array(
                array(
                    'type' => 'radio',
                    'label' => $this->l('Formats'),
                    'name' => 'thumb_format',
                    'default_value' => 0,
                    'values' => array(
                        array(
                            'id' => 'thumb_format_0',
                            'value' => 0,
                            'label' => $this->l('Generate both WebP and jpg/png')
                        ),
                        array(
                            'id' => 'thumb_format_1',
                            'value' => 1,
                            'label' => $this->l('Generate WebP only')
                        ),
                        array(
                            'id' => 'thumb_format_2',
                            'value' => 2,
                            'label' => $this->l('Generate Jpg/png only')
                        ),
                    ),
                    'validation' => 'isUnsignedInt',
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Erase previous images'),
                    'name' => 'erase',
                    'default_value' => 0,
                    'values' => array(
                        array(
                            'id' => 'erase_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'erase_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                    'validation' => 'isUnsignedInt',
                    'desc' => $this->l('Enable this when you want to regeneration thumbnails. Disable this when you generate thumbnails for newly added images.'),
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Max execution time'),
                    'name' => 'met',
                    'class'      => 'fixed-width-lg',
                    'default_value' => 0,
                    'desc' => $this->l('If you have animated gif images or you want to generate a large number of images per time, then set a large value for this option, like 120, 240, 600. Otherwise keep this as 0'),
                    'validation' => 'isUnsignedInt',
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('How may images per time'),
                    'name' => 'per_time',
                    'class'      => 'fixed-width-lg',
                    'desc' => $this->l('Set it to 0 to use the default value 3.'),
                    'validation' => 'isUnsignedInt',
                ),
                array(
                    'type' => 'html',
                    'id' => '',
                    'label' => $this->l('What do you want to generate:'),
                    'name' => '<div class="checkbox"><label for="fenlei_products"><input type="checkbox" name="fenlei_products" id="fenlei_products" class="fenlei_group" value="products">'.$this->l('Product images').'<img src="'.$this->_path.'views/img/loading.gif" class="st_loading" /><span class="fenlei_status" id="fenlei_info_products"><span class="indexed_number">'.$status['products']['indexed'].'</span>/<span class="total_number">'.$status['products']['total'].'</span></span></label></div> 
<div class="checkbox"><label for="fenlei_categories"><input type="checkbox" name="fenlei_categories" id="fenlei_categories" class="fenlei_group" value="categories">'.$this->l('Categorie images').'<img src="'.$this->_path.'views/img/loading.gif" class="st_loading" /><span class="fenlei_status" id="fenlei_info_categories"><span class="indexed_number">'.$status['categories']['indexed'].'</span>/<span class="total_number">'.$status['categories']['total'].'</span></span></label></div> 
<div class="checkbox"><label for="fenlei_manufacturers"><input type="checkbox" name="fenlei_manufacturers" id="fenlei_manufacturers" class="fenlei_group" value="manufacturers">'.$this->l('Manufacturer images').'<img src="'.$this->_path.'views/img/loading.gif" class="st_loading" /><span class="fenlei_status" id="fenlei_info_manufacturers"><span class="indexed_number">'.$status['manufacturers']['indexed'].'</span>/<span class="total_number">'.$status['manufacturers']['total'].'</span></span></label></div> 
<div class="checkbox"><label for="fenlei_suppliers"><input type="checkbox" name="fenlei_suppliers" id="fenlei_suppliers" class="fenlei_group" value="suppliers">'.$this->l('Supplier images').'<img src="'.$this->_path.'views/img/loading.gif" class="st_loading" /><span class="fenlei_status" id="fenlei_info_suppliers"><span class="indexed_number">'.$status['suppliers']['indexed'].'</span>/<span class="total_number">'.$status['suppliers']['total'].'</span></span></label></div> 
<div class="checkbox"><label for="fenlei_stores"><input type="checkbox" name="fenlei_stores" id="fenlei_stores" class="fenlei_group" value="stores">'.$this->l('Store images').'<img src="'.$this->_path.'views/img/loading.gif" class="st_loading" /><span class="fenlei_status" id="fenlei_info_stores"><span class="indexed_number">'.$status['stores']['indexed'].'</span>/<span class="total_number">'.$status['stores']['total'].'</span></span></label></div>
<div class="checkbox"><label for="fenlei_nopictures"><input type="checkbox" name="fenlei_nopictures" id="fenlei_nopictures" class="fenlei_group" value="nopictures">'.$this->l('Nopictures').'<img src="'.$this->_path.'views/img/loading.gif" class="st_loading" /><span class="fenlei_status" id="fenlei_info_nopictures"><span class="indexed_number"></span>/<span class="total_number"></span></span></label></div>'.
(Module::isEnabled('stblog')?'<div class="checkbox"><label for="fenlei_articles"><input type="checkbox" name="fenlei_articles" id="fenlei_articles" class="fenlei_group" value="articles">'.$this->l('Blog article images').'<img src="'.$this->_path.'views/img/loading.gif" class="st_loading" /><span class="fenlei_status" id="fenlei_info_articles"><span class="indexed_number">'.$status['articles']['indexed'].'</span>/<span class="total_number">'.$status['articles']['total'].'</span></span></label></div>':'').
(Module::isEnabled('stbanner')?'<div class="checkbox"><label for="fenlei_stbanner"><input type="checkbox" name="fenlei_stbanner" id="fenlei_stbanner" class="fenlei_group" value="stbanner">'.$this->l('Advanced banner images').'<img src="'.$this->_path.'views/img/loading.gif" class="st_loading" /><span class="fenlei_status" id="fenlei_info_stbanner"><span class="indexed_number">'.$status['stbanner']['indexed'].'</span>/<span class="total_number">'.$status['stbanner']['total'].'</span></span></label></div>':'').
(Module::isEnabled('stswiper')?'<div class="checkbox"><label for="fenlei_stswiper"><input type="checkbox" name="fenlei_stswiper" id="fenlei_stswiper" class="fenlei_group" value="stswiper">'.$this->l('Swiper slider images').'<img src="'.$this->_path.'views/img/loading.gif" class="st_loading" /><span class="fenlei_status" id="fenlei_info_stswiper"><span class="indexed_number">'.$status['stswiper']['indexed'].'</span>/<span class="total_number">'.$status['stswiper']['total'].'</span></span></label></div>':'').
(Module::isEnabled('stowlcarousel')?'<div class="checkbox"><label for="fenlei_stowlcarousel"><input type="checkbox" name="fenlei_stowlcarousel" id="fenlei_stowlcarousel" class="fenlei_group" value="stowlcarousel">'.$this->l('Owl Carousel slider images').'<img src="'.$this->_path.'views/img/loading.gif" class="st_loading" /><span class="fenlei_status" id="fenlei_info_stowlcarousel"><span class="indexed_number">'.$status['stowlcarousel']['indexed'].'</span>/<span class="total_number">'.$status['stowlcarousel']['total'].'</span></span></label></div>':''),
                ),
                'image_type'=>array(
                    'type' => 'checkbox',
                    'label' => $this->l('Product Image types:'),
                    'name' => 'image_type',
                    'default_value' => '',
                    'class' => 'image_type_products',
                    'form_group_class' => 'fenlei-products',
                    'values' => array(
                        'query' => array(
                        ),
                        'id' => 'id',
                        'name' => 'name',
                    ),
                    'desc' => array(
                        $this->l('For product images only, generate thumbnails of choosen image types for products.'),
                    ),
                ),
                array(
                    'type' => 'html',
                    'id' => '',
                    'label' => '',
                    'name' => '<a href="javascript:;" class="btn btn-default" id="st_generate_button_start">'.$this->l('Continue generate thumbnails').'</a><a href="javascript:;" class="btn btn-default" id="st_generate_button_re">'.$this->l('Regenerate thumbnails').'</a><a href="javascript:;" class="btn btn-default" id="st_generate_button_stop">'.$this->l('Stop generate thumbnails').'</a><div id="st_generate_info"></div><div id="st_generate_message"></div>',
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'stay' => true,
            ),
        );
        
        
    }
    
    protected function initForm()
	{
        $imagesTypes = ImageType::getImagesTypes();
        foreach ($imagesTypes as $k=>$type) {
            if ($type['products'] && ($this->_st_is_16 || Configuration::get($this->_prefix_st.strtoupper('webp_image_type_'.$type['name'])))) {
                $this->fields_form[1]['form']['input']['image_type']['values']['query'][] = array(
                    'id' => $type['name'],
                    'name' => $type['name'],
                    'val' => $type['name'],
                );   
            }
            $this->fields_form[0]['form']['input']['webp_image_type']['values']['query'][] = array(
                'id' => $type['name'],
                'name' => $type['name'],
                'val' => $type['name'],
            );
        }
        if($this->_st_is_16)
            unset($this->fields_form[0]['form']['input']['webp_image_type']);

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

        foreach ($imagesTypes as $k=>$type) {
            if ($type['products']) {
                $helper->tpl_vars['fields_value']['image_type_'.$type['name']] = 1; 
            }
            $helper->tpl_vars['fields_value']['webp_image_type_'.$type['name']] = (int)Configuration::get($this->_prefix_st.strtoupper('webp_image_type_'.$type['name'])); 
        }

		return $helper;
	}
    
    private function getConfigFieldsValues()
    {
        $fields_values = array(
            'erase'                         => Configuration::get($this->_prefix_st.'ERASE')?:0,
            'start_id'                      => '',
            'end_id'                        => '',
            'per_time'                      => Configuration::get($this->_prefix_st.'PER_TIME')?:3,
            'met'                           => Configuration::get($this->_prefix_st.'MET')?:0,
            'thumb_format'                  => Configuration::get($this->_prefix_st.'THUMB_FORMAT'),
            'background_color'              => Configuration::get($this->_prefix_st.'BACKGROUND_COLOR'),
            'webp_compatibility'            => Configuration::get($this->_prefix_st.'WEBP_COMPATIBILITY'),
            'enable_webp'                   => Configuration::get($this->_prefix_st.'ENABLE_WEBP'),
            'webp_quality'                  => Configuration::get($this->_prefix_st.'WEBP_QUALITY', 90),
            'png_quality'                   => Configuration::get($this->_prefix_st.'PNG_QUALITY', 7),
            'jpeg_quality'                  => Configuration::get($this->_prefix_st.'JPEG_QUALITY', 90),
            'webp_type'                     => (int)Configuration::get($this->_prefix_st.'WEBP_TYPE'),
            'crop'                          => (int)Configuration::get($this->_prefix_st.'CROP'),
        );
        return $fields_values;
    }
    protected function getNextIds($params)
    {
        $id = (int) Configuration::get($this->_prefix_st.strtoupper($params['fenlei']));
        $where = '';
        if ($params['fenlei'] === 'categories') {
            $primary = 'id_category';
            $table = 'category';
            $where = ' AND `level_depth`>1';
        } else {
            $primary = 'id_'.rtrim($params['fenlei'], 's');
            $table = rtrim($params['fenlei'], 's');
        }

        $data = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            (new DbQuery())
                ->select('`'.bqSQL($primary).'`')
                ->from($table)
                ->where('`'.bqSQL($primary).'` > '.(int) $id.$where)
                ->limit($params['per_time'])
                ->orderBy('`'.bqSQL($primary).'` ASC')
        );

        if(!is_array($data) || !count($data))
            return false;
        $res = array();
        foreach ($data as $v) {
            $res[] = $v[$primary];
        }
        return $res;
    }
    protected function _regenerateNewImages($type, $params)
    {
        if($params['met'] && ini_get('max_execution_time')<$params['met'])
            ini_set('max_execution_time', $params['met']);
        $res = array(
            'errors' => array(),
            'messages' => array(),
            'warnings' => array(),
            'indexed' => 0,
            'total' => 0,
            'done' => 0,
        );

        $dir = $this->process[$params['fenlei']]['dir'];

        if (!is_dir($dir)) {
            $res['warnings'][] = sprintf($this->l('%s not exist'), $dir);
            return $res;
        }

        switch($params['fenlei']) {
            case 'products':
                $productsImages = 1;
                break;
            case 'articles':
                $productsImages = 2;
                break;
            case 'stbanner':
            case 'stswiper':
            case 'stowlcarousel':
                $productsImages = $params['fenlei'];
                break;
            default:
                $productsImages = 0;
        }

        $generate_hight_dpi_images = $this->_st_theme ? false : (bool)Configuration::get($this->_prefix_st.'HIGHT_DPI');

        if ($productsImages === 0) {
            $formated_medium = Tools::version_compare(_PS_VERSION_, '1.7') ? ImageType::getFormatedName('medium') : ImageType::getFormattedName('medium');

            $image_ids = $this->getNextIds($params);
            if(!is_array($image_ids) || !count($image_ids)){
                $res['done'] = 1;
                return $res;
            }
            foreach ($image_ids as $id) {
                foreach ($type as $k => $imageType) {
                    $image = $id.'.jpg';
                    // Customizable writing dir
                    $newDir = $dir;

                    if (($dir == _PS_CAT_IMG_DIR_) && ($imageType['name'] == $formated_medium) && is_file(_PS_CAT_IMG_DIR_.str_replace('.', '_thumb.', $image))) {
                        $image = str_replace('.', '_thumb.', $image);
                    }

                    $exists = file_exists($newDir.substr($image, 0, -4).'-'.stripslashes($imageType['name']).'.jpg');
                    $exists_webp = file_exists($newDir.substr($image, 0, -4).'-'.stripslashes($imageType['name']).'.webp');
                    if($params['erase']){
                        if($params['thumb_format']==0 || $params['thumb_format']==2)
                            @unlink($newDir.substr($image, 0, -4).'-'.stripslashes($imageType['name']).'.jpg');
                        if($params['thumb_format']==0 || $params['thumb_format']==1)
                            @unlink($newDir.substr($image, 0, -4).'-'.stripslashes($imageType['name']).'.webp');
                        if ($generate_hight_dpi_images)
                            @unlink($newDir.substr($image, 0, -4).'-'.stripslashes($imageType['name']).'2x.jpg');
                    }
                    if ((!$exists && ($params['thumb_format']==0 || $params['thumb_format']==2)) || (!$exists_webp && ($params['thumb_format']==0 || $params['thumb_format']==1)) || $params['erase']) {
                        if (!file_exists($dir.$image) || !filesize($dir.$image)) {
                            $res['messages'][] = sprintf($this->l('Source file does not exist or is empty (%s).'), $dir.$image);
                            Configuration::updateValue($this->_prefix_st.strtoupper($params['fenlei']), $id);
                        } elseif (!ImageManager::resize($dir.$image, $newDir.substr(str_replace('_thumb.', '.', $image), 0, -4).'-'.stripslashes($imageType['name']).'.jpg', (int)$imageType['width'], (int)$imageType['height'])) {
                            $res['messages'][] = sprintf($this->l('Failed to resize image file (%s).'), $dir.$image);
                            Configuration::updateValue($this->_prefix_st.strtoupper($params['fenlei']), $id);
                        }

                        if ($generate_hight_dpi_images) {
                            if (!ImageManager::resize($dir.$image, $newDir.substr($image, 0, -4).'-'.stripslashes($imageType['name']).'2x.jpg', (int)$imageType['width']*2, (int)$imageType['height']*2)) {
                                $res['messages'][] = sprintf($this->l('Failed to resize image file to high resolution (%s).'), $dir.$image);
                            }
                        }
                    }
                }
                Configuration::updateValue($this->_prefix_st.strtoupper($params['fenlei']), $id);
            }
            $status_temp = $this->getIndexationStatus($params['fenlei']);
            $res['indexed'] = $status_temp[$params['fenlei']]['indexed'];
            $res['total'] = $status_temp[$params['fenlei']]['total'];
        } elseif($productsImages === 1) {
            $id_image_start = (int)Configuration::get($this->_prefix_st.'PRODUCTS');

            $images = Db::getInstance()->executeS('
                SELECT i.`id_image`, i.`id_product`
                FROM `'._DB_PREFIX_.'image` i
                LEFT JOIN `'._DB_PREFIX_.'image_shop` imgs ON i.`id_image` = imgs.`id_image`
                WHERE i.`id_image`>'.$id_image_start.' 
                 AND imgs.`id_shop` = '.$this->context->shop->id.'
                 ORDER BY i.`id_image` ASC
                 LIMIT '.($params['per_time'] ?: 3));
            if(!$images){
                $res['done'] = 1;
                return $res;
            }
            //($params['end_id'] ? ' AND `id_image`<='.$params['end_id'] : '')

            /*$productsImages = array_column((array) Db::getInstance()->executeS(
                (new DbQuery())
                    ->select('`id_image`')
                    ->from('image')
                    ->where('`id_product` = '.(int) $idEntity)
            ), 'id_image');*/
            $watermark_modules = Db::getInstance()->executeS('
            SELECT m.`name` FROM `'._DB_PREFIX_.'module` m
            LEFT JOIN `'._DB_PREFIX_.'hook_module` hm ON hm.`id_module` = m.`id_module`
            LEFT JOIN `'._DB_PREFIX_.'hook` h ON hm.`id_hook` = h.`id_hook`
            WHERE h.`name` = \'actionWatermark\' AND m.`active` = 1');

            foreach ($images as $image) {
                $imageObj = new Image($image['id_image']);
                $existing_img = $dir.$imageObj->getExistingImgPath().'.jpg';
                if (file_exists($existing_img) && filesize($existing_img)) {
                    foreach ($type as $imageType) {
                        $exists = file_exists($dir.$imageObj->getExistingImgPath().'-'.stripslashes($imageType['name']).'.jpg');
                        $exists_webp = file_exists($dir.$imageObj->getExistingImgPath().'-'.stripslashes($imageType['name']).'.webp');
                        if($params['erase']){
                            if($params['thumb_format']==0 || $params['thumb_format']==2)
                                @unlink($dir.$imageObj->getExistingImgPath().'-'.stripslashes($imageType['name']).'.jpg');
                            if($params['thumb_format']==0 || $params['thumb_format']==1)
                                @unlink($dir.$imageObj->getExistingImgPath().'-'.stripslashes($imageType['name']).'.webp');
                            if ($generate_hight_dpi_images)
                                @unlink($dir.$imageObj->getExistingImgPath().'-'.stripslashes($imageType['name']).'2x.webp');
                        }
                        if ((!$exists && ($params['thumb_format']==0 || $params['thumb_format']==2)) || (!$exists_webp && ($params['thumb_format']==0 || $params['thumb_format']==1)) || $params['erase']) {
                            if (!ImageManager::resize($existing_img, $dir.$imageObj->getExistingImgPath().'-'.stripslashes($imageType['name']).'.jpg', (int)$imageType['width'], (int)$imageType['height'])) {
                                $res['messages'][] = sprintf($this->l('Original image is corrupt (%s) for product ID %d or bad permission on folder.'), $existing_img, (int)$imageObj->id_product);
                                Configuration::updateValue($this->_prefix_st.'PRODUCTS', $image['id_image']);
                            }else{
                                if ($generate_hight_dpi_images) {
                                    if (!ImageManager::resize($existing_img, $dir.$imageObj->getExistingImgPath().'-'.stripslashes($imageType['name']).'2x.jpg', (int)$imageType['width']*2, (int)$imageType['height']*2)) {
                                        $res['messages'][] = sprintf($this->l('Can not generation hight dpi image (%s) for product ID %d.'), $existing_img, (int)$imageObj->id_product);
                                    }
                                }
                                //watermark
                                if (is_array($watermark_modules) && count($watermark_modules)) {
                                    foreach ($watermark_modules as $module) {
                                        $moduleInstance = Module::getInstanceByName($module['name']);
                                        if ($moduleInstance && is_callable(array($moduleInstance, 'hookActionWatermark'))) {
                                            call_user_func(array($moduleInstance, 'hookActionWatermark'), array('id_image' => $imageObj->id, 'id_product' => $imageObj->id_product, 'image_type' => $type));
                                        }
                                    }
                                }
                            }
                        }
                    }
                    Configuration::updateValue($this->_prefix_st.'PRODUCTS', $image['id_image']);
                } else {
                    $res['messages'][] = sprintf($this->l('Original image is missing or empty (%s) for product ID %d.'), $existing_img, (int)$imageObj->id_product);
                    Configuration::updateValue($this->_prefix_st.'PRODUCTS', $image['id_image']);
                }
            }
            $status_temp = $this->getIndexationStatus($params['fenlei']);
            $res['indexed'] = $status_temp[$params['fenlei']]['indexed'];
            $res['total'] = $status_temp[$params['fenlei']]['total'];
        } elseif($productsImages === 2) {
            $id_image_start = Configuration::get($this->_prefix_st.'ARTICLES');

            $images = Db::getInstance()->executeS('
            SELECT i.* FROM '._DB_PREFIX_.'st_blog_image `i`
            INNER JOIN '._DB_PREFIX_.'st_blog_image_shop `imgs` ON `i`.`id_st_blog_image` = `imgs`.`id_st_blog_image`
            WHERE imgs.`id_shop`='.$this->context->shop->id.' and `i`.`id_st_blog_image`>'.$id_image_start.' 
            ORDER BY `i`.`id_st_blog_image`
            LIMIT '.($params['per_time'] ?: 3)
            );

            if(!$images){
                $res['done'] = 1;
                return $res;
            }

            $ext  = 'jpg';
            foreach($images AS $image)
            {
                $file = $dir.$image['type'].'/'.$image['id_st_blog'].'/'.$image['id_st_blog_image'].'/'.$image['id_st_blog'].$image['id_st_blog_image'].'.'.$ext;

                if (!file_exists($file))
                {
                    $res['messages'][] = sprintf($this->l('Original image is missing or empty (%s) for blog article ID %d.'), $image['id_st_blog_image'], $image['id_st_blog']);
                    Configuration::updateValue($this->_prefix_st.'ARTICLES', $image['id_st_blog_image']);
                    continue;
                }
                $this->resizeBlogImage($file, $image['type'], $image['id_st_blog'].$image['id_st_blog_image'], $ext);
                Configuration::updateValue($this->_prefix_st.'ARTICLES', $image['id_st_blog_image']);
            }

            $status_temp = $this->getIndexationStatus($params['fenlei']);
            $res['indexed'] = $status_temp[$params['fenlei']]['indexed'];
            $res['total'] = $status_temp[$params['fenlei']]['total'];
        } else {
            // For modules: stbanne|stwiper|stowlcarousel
            $res['total'] = $this->getTotalImage($productsImages);
            $indexed = 0;
            foreach(glob(_PS_UPLOAD_DIR_.$productsImages.'/*.{jpg,png,jpeg}', GLOB_BRACE) as $file) {
                $ext = substr($file, strrpos($file, '.') + 1);
                $dstFile = substr($file, 0, -(strlen($ext))).'webp';
                if (!$params['erase'] && file_exists($dstFile)) {
                    $indexed++;
                    continue;
                } elseif (file_exists($dstFile)) {
                    @unlink($dstFile);
                }
                if (method_exists('ImageManager', 'createWebp')) {
                    $res_gen = ImageManager::createWebp($file, $file, null, null);
                    if (($res_gen && $params['erase']) || (!$res_gen && !$params['erase'])) {
                        $indexed++;
                    }
                }
            }
            Configuration::updateValue($this->_prefix_st.strtoupper($productsImages), $indexed);
            $res['indexed'] = $indexed;
            $res['done'] = 1;
        }

        return $res;
    }
    public function resizeBlogImage($src_file, $image_type = 1, $basename = '', $ext = 'jpg')
    {
        if (!file_exists($src_file))
            return false;
        $ret = true;
        $types = StBlogImageClass::getDefImageTypes();
        if (!count($types) || !key_exists($image_type, $types))
            return false;
        foreach($types[$image_type] AS $key => $type)
        {
            if (!is_array($type) && count($type) < 2)
                continue;
                
            // Is image smaller than dest? fill it with white!
            $tmp_file_new = $src_file;
            list($src_width, $src_height) = getimagesize($src_file);
            if (!$src_width || !$src_height)
                continue;
            
            $width  = (int)$type[0];
            $height = $type[1] > 0 ? (int)$type[1] : $src_height;
            $folder = dirname($src_file).'/';
            $ret &= ImageManager::cut($src_file, $folder.$basename.$key.'.'.$ext, $width, $height);
        }
        return $ret;
    }
    public function regProcess($reg){
        $reg_arr = explode(',', $reg);
        foreach ($reg_arr as $reg) {
            if(array_key_exists($reg, $this->process))
                Configuration::updateValue($this->_prefix_st.strtoupper($reg),0);
        }
    }
    public function stGenerate($params){
        
        $res = array(
            'errors' => array(),
            'messages' => array(),
            'warnings' => array(),
            'indexed' => 0,
            'total' => 0,
            'done' => 0,
        );

        if($params['fenlei']=='nopictures'){
            $languages = Language::getLanguages(false);
            foreach ($this->process as $pk => $pv) {
                if(!$pv['no_pic'])
                    continue;
                $nopictures_error = $this->_regenerateNoPictureImages($pv['dir'],ImageType::getImagesTypes($pk),$languages,$params);
                if(is_array($nopictures_error) && count($nopictures_error))
                    $res['messages'] = array_merge($res['messages'], $nopictures_error);
            }
            $res['done']=1;
            return $res;
        }

        if($params['reg']){
            $this->regProcess($params['reg']);
        }
        
        if(!array_key_exists($params['fenlei'], $this->process)){
            $res['warnings'][] = $this->l('Invalid type');
            return $res;
        }

        $productsImages = $params['fenlei'] == 'products';
        if(!$params['image_type'] && $productsImages){
            $res['warnings'][] = $this->l('Didn\'t select any products image types');
            return $res;
        }

        // Getting format generation
        $formats = array();
        if ($this->process[$params['fenlei']]['in_db']) {
            $formats = ImageType::getImagesTypes($params['fenlei']);   
        }
        
        if ($productsImages && $params['image_type']) {
            foreach ($formats as $k => $form) {
                if (!in_array($form['name'], $params['image_type'])) {
                    unset($formats[$k]);
                }
            }
        }
        if($this->process[$params['fenlei']]['in_db'] && !count($formats)){
            $res['warnings'][] = sprintf($this->l('%s doesn\'t have any image types.'), $params['fenlei']);
            return $res;
        }

        if($params['fenlei'] == 'articles' && (!Module::isInstalled('stblog') || !Module::isEnabled('stblog'))){
            $res['warnings'][] = $this->l('Blog module isn\'t installed or enabled.');
            return $res;
        }
        if($params['fenlei'] == 'stbanner' && (!Module::isInstalled('stbanner') || !Module::isEnabled('stbanner'))){
            $res['warnings'][] = $this->l('Advanced banner module isn\'t installed or enabled.');
            return $res;
        }
        if($params['fenlei'] == 'stswiper' && (!Module::isInstalled('stswiper') || !Module::isEnabled('stswiper'))){
            $res['warnings'][] = $this->l('Swiper slide module isn\'t installed or enabled.');
            return $res;
        }
        if($params['fenlei'] == 'stowlcarousel' && (!Module::isInstalled('stowlcarousel') || !Module::isEnabled('stowlcarousel'))){
            $res['warnings'][] = $this->l('Owl carousel module isn\'t installed or enabled.');
            return $res;
        }

        $res = $this->_regenerateNewImages($formats, $params);

        return $res;
    }
    protected function _regenerateNoPictureImages($dir, $type, $languages,$params)
    {
        $errors = array();
        $generate_hight_dpi_images = $this->_st_theme ? false : (bool)Configuration::get($this->_prefix_st.'HIGHT_DPI');

        foreach ($type as $image_type) {
            foreach ($languages as $language) {
                $file = $dir.$language['iso_code'].'.jpg';
                if (!file_exists($file)) {
                    $file = _PS_PROD_IMG_DIR_.Language::getIsoById((int)Configuration::get('PS_LANG_DEFAULT')).'.jpg';
                }

                $exists = file_exists($dir.$language['iso_code'].'-default-'.stripslashes($image_type['name']).'.jpg');
                $exists_webp = file_exists($dir.$language['iso_code'].'-default-'.stripslashes($image_type['name']).'.webp');
                if($params['erase']){
                    if($params['thumb_format']==0 || $params['thumb_format']==2)
                        @unlink($dir.$language['iso_code'].'-default-'.stripslashes($image_type['name']).'.jpg');
                    if($params['thumb_format']==0 || $params['thumb_format']==1)
                        @unlink($dir.$language['iso_code'].'-default-'.stripslashes($image_type['name']).'.webp');
                }
                if ((!$exists && ($params['thumb_format']==0 || $params['thumb_format']==2)) || (!$exists_webp && ($params['thumb_format']==0 || $params['thumb_format']==1)) || $params['erase']) {
                    if (!ImageManager::resize($file, $dir.$language['iso_code'].'-default-'.stripslashes($image_type['name']).'.jpg', (int)$image_type['width'], (int)$image_type['height'])) {
                        $errors[] = sprintf($this->l('Error when generate no picture for %s %s.'), $language['iso_code'], $image_type['name']);
                    }

                    if ($generate_hight_dpi_images) {
                        if (!ImageManager::resize($file, $dir.$language['iso_code'].'-default-'.stripslashes($image_type['name']).'2x.jpg', (int)$image_type['width']*2, (int)$image_type['height']*2)) {
                            $errors[] = sprintf($this->l('Error when generate hight dpi no picture for %s %s.'), $language['iso_code'], $image_type['name']);
                        }
                    }
                }
            }
        }
        return $errors;
    }
    protected function getIndexationStatus($type=false)
    {
        $data = array();
        if($type=='products' || !$type)
            $data['products'] = [
                    'indexed' => (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                        (new DbQuery())
                            ->select('count(0)')
                            ->from(bqSQL(Image::$definition['table']), 'i')
                            ->leftJoin(bqSQL('image_shop'), 'imgs', 'i.`id_image` = imgs.`id_image`')
                            ->where('i.`'.bqSQL(Image::$definition['primary']).'` <= '.(int) Configuration::get($this->_prefix_st.'PRODUCTS'))
                            ->where('imgs.`id_shop` = '. $this->context->shop->id)
                    ),
                    'total'   => (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                        (new DbQuery())
                            ->select('count(0)')
                            ->from(bqSQL(Image::$definition['table']), 'i')
                            ->leftJoin(bqSQL('image_shop'), 'imgs', 'i.`id_image` = imgs.`id_image`')
                            ->where('imgs.`id_shop` = '. $this->context->shop->id)
                    ),
                ];
        if($type=='categories' || !$type)
            $data['categories'] = [
                    'indexed' => (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                        (new DbQuery())
                            ->select('count(0)')
                            ->from(bqSQL(Category::$definition['table']), 'c')
                            ->leftJoin('category_shop', 'cs', 'c.`id_category` = cs.`id_category`')
                            ->where('c.`'.bqSQL(Category::$definition['primary']).'` <= '.(int) Configuration::get($this->_prefix_st.'CATEGORIES').' AND c.`level_depth`>1')
                            ->where('cs.`id_shop` = '. $this->context->shop->id)
                    ),
                    'total'   => (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                        (new DbQuery())
                            ->select('count(0)')
                            ->from(bqSQL(Category::$definition['table']), 'c')
                            ->leftJoin('category_shop', 'cs', 'c.`id_category` = cs.`id_category`')
                            ->where('c.`level_depth`>1')
                            ->where('cs.`id_shop` = '. $this->context->shop->id)
                    ),
                ];
        if($type=='suppliers' || !$type)
            $data['suppliers'] = [
                    'indexed' => (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                        (new DbQuery())
                            ->select('count(0)')
                            ->from(bqSQL(Supplier::$definition['table']),'s')
                            ->leftJoin('supplier_shop', 'ss', 's.`id_supplier` = ss.`id_supplier`')
                            ->where('s.`'.bqSQL(Supplier::$definition['primary']).'` <= '.(int) Configuration::get($this->_prefix_st.'SUPPLIERS'))
                            ->where('ss.`id_shop` = '. $this->context->shop->id)
                    ),
                    'total'   => (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                        (new DbQuery())
                            ->select('count(0)')
                            ->from(bqSQL(Supplier::$definition['table']),'s')
                            ->leftJoin('supplier_shop', 'ss', 's.`id_supplier` = ss.`id_supplier`')
                            ->where('ss.`id_shop` = '. $this->context->shop->id)
                    ),
                ];
        if($type=='manufacturers' || !$type)
            $data['manufacturers'] = [
                    'indexed' => (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                        (new DbQuery())
                            ->select('count(0)')
                            ->from(bqSQL(Manufacturer::$definition['table']),'m')
                            ->leftJoin('manufacturer_shop', 'ms', 'm.`id_manufacturer` = ms.`id_manufacturer`')
                            ->where('m.`'.bqSQL(Manufacturer::$definition['primary']).'` <= '.(int) Configuration::get($this->_prefix_st.'MANUFACTURERS'))
                            ->where('ms.`id_shop` = '. $this->context->shop->id)
                    ),
                    'total'   => (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                        (new DbQuery())
                            ->select('count(0)')
                            ->from(bqSQL(Manufacturer::$definition['table']),'m')
                            ->leftJoin('manufacturer_shop', 'ms', 'm.`id_manufacturer` = ms.`id_manufacturer`')
                            ->where('ms.`id_shop` = '. $this->context->shop->id)
                    ),
                ];
        if($type=='stores' || !$type)
            $data['stores'] = [
                    'indexed' => (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                        (new DbQuery())
                            ->select('count(0)')
                            ->from(bqSQL(Store::$definition['table']), 's')
                            ->leftJoin('store_shop', 'ss', 's.`id_store` = ss.`id_store`')
                            ->where('s.`'.bqSQL(Store::$definition['primary']).'` <= '.(int) Configuration::get($this->_prefix_st.'STORES'))
                            ->where('ss.`id_shop` = '. $this->context->shop->id)
                    ),
                    'total'   => (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                        (new DbQuery())
                            ->select('count(0)')
                            ->from(bqSQL(Store::$definition['table']),'s')
                            ->leftJoin('store_shop', 'ss', 's.`id_store` = ss.`id_store`')
                            ->where('ss.`id_shop` = '. $this->context->shop->id)
                    ),
                ];
        if(($type=='articles' || !$type) && Module::isEnabled('stblog'))
            $data['articles'] = [
                    'indexed' => (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                        (new DbQuery())
                            ->select('count(0)')
                            ->from(bqSQL('st_blog_image'),'s')
                            ->leftJoin('st_blog_image_shop', 'sbis', 's.`id_st_blog_image` = sbis.`id_st_blog_image`')
                            ->where('s.`id_st_blog_image` <= '.(int) Configuration::get($this->_prefix_st.'ARTICLES'))
                            ->where('sbis.`id_shop` = '. $this->context->shop->id)
                    ),
                    'total'   => (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                        (new DbQuery())
                            ->select('count(0)')
                            ->from(bqSQL('st_blog_image'),'s')
                            ->leftJoin('st_blog_image_shop', 'sbis', 's.`id_st_blog_image` = sbis.`id_st_blog_image`')
                            ->where('sbis.`id_shop` = '. $this->context->shop->id)
                    ),
                ];
        else {
            $data['articles'] = ['indexed' => 0, 'total' => 0];
        }
        if(($type=='stbanner' || !$type) && Module::isEnabled('stbanner'))
            $data['stbanner'] = [
                    'indexed' => (int)Configuration::get($this->_prefix_st.'STBANNER'),
                    'total'   => is_dir(_PS_UPLOAD_DIR_.'stbanner') ? $this->getTotalImage('stbanner') : 0,
                ];
        else {
            $data['stbanner'] = ['indexed' => 0, 'total' => 0];
        }
        if(($type=='stswiper' || !$type) && Module::isEnabled('stswiper'))
            $data['stswiper'] = [
                    'indexed' => (int)Configuration::get($this->_prefix_st.'STSWIPER'),
                    'total'   => is_dir(_PS_UPLOAD_DIR_.'stswiper') ? $this->getTotalImage('stswiper') : 0,
                ];
        else {
            $data['stswiper'] = ['indexed' => 0, 'total' => 0];
        }
        if(($type=='stowlcarousel' || !$type) && Module::isEnabled('stowlcarousel'))
            $data['stowlcarousel'] = [
                    'indexed' => (int)Configuration::get($this->_prefix_st.'STOWLCAROUSEL'),
                    'total'   => is_dir(_PS_UPLOAD_DIR_.'stowlcarousel') ? $this->getTotalImage('stowlcarousel') : 0,
                ];
        else {
            $data['stowlcarousel'] = ['indexed' => 0, 'total' => 0];
        }
        return $data;
    }

    private function getTotalImage($module)
    {
        return count(glob(_PS_UPLOAD_DIR_.$module.'/*.{jpg,png,jpeg}', GLOB_BRACE));
    }
    

    public function getWebpImageTypes()
    {
        $webp_image_types = array();
        $imagesTypes = ImageType::getImagesTypes();
        foreach ($imagesTypes as $type) {
            $webp_image_types[$type['name']] = $this->_st_is_16 ? 1 : (int)Configuration::get($this->_prefix_st.strtoupper('webp_image_type_'.$type['name']));
        }
        return $webp_image_types;
    }
    public function hookDisplayHeader($params)
    {
        if(!ImageManager::webpSupport())
            return;

        $webp_image_types = $this->getWebpImageTypes();
        $stwebp_type = (int)Configuration::get($this->_prefix_st.'WEBP_TYPE');
        Media::addJsDef(array(
            'stwebp' => $webp_image_types,
            'stwebp_type' => $stwebp_type,
            'stwebp_supported' => null,
        ));
        $this->context->smarty->assign(array(
            'stwebp' => $webp_image_types,
            'stwebp_type' => $stwebp_type,
        ));
        if ($this->_st_is_16 && !defined('_TB_VERSION_')){
            //for ps 1.6
            Media::addJsDef(array(
                'useWebp' => null,
            ));
            $this->context->smarty->assign(array(
                'webp' => true,
            ));
        }
        return;
    }
    public function hookActionProductSearchAfter($params){
        if(!ImageManager::webpSupport())
            return;
        $webp_image_types = $this->getWebpImageTypes();
        $this->context->smarty->assign('stwebp', $webp_image_types);
        return ;
    }
    public function hookActionProductSearchComplete($params){
        if(!ImageManager::webpSupport())
            return;
        $webp_image_types = $this->getWebpImageTypes();
        $this->context->smarty->assign('stwebp', $webp_image_types);
        return ;
    }
    function generateHtaccess()
    {
        $result = Tools::generateHtaccess();
        if (file_exists(_PS_IMG_DIR_.'.htaccess')) {
            $content = file_get_contents(_PS_IMG_DIR_.'.htaccess');
            if (strpos($content, '|webp|') === false) {
                $content = str_replace('|gif|', '|gif|webp|', $content);
                $result &= file_put_contents(_PS_IMG_DIR_.'.htaccess', $content);    
            }
        }
        return $result;
    }
    /*public function hookActionStAssemble($params)
    {
        $urls[$image_type['name']]['webp'] = self::getWebpImage($urls[$image_type['name']]['url'], $image_type['name']);
        return $image;
    }

    public static function getWebpImage($url, $type='')
    {
        if (!$url) {
            return false;
        }
        $use_webp = false;
        if (Configuration::get('ST_WEBP_WEBP_TYPE')) {
            // all images
            $use_webp = true;
        } else {
            // Specific images
            if (Configuration::get('ST_WEBP_'.strtoupper('webp_image_type_'.$type))) {
                $use_webp = true;
            }
        }
        if ($use_webp) {
            $ext = substr($url, strrpos($url, '.'));
            $url = substr($url, 0, -(strlen($ext))).'.webp';
            return $url;
        }else{
            return false;
        }
    }

    public static function isModuleEnabled()
    {
        static $enabled = null;
        if ($enabled === null) {
            $enabled = Module::isEnabled('stwebp') && Configuration::get('ST_WEBP_ENABLE_WEBP') && function_exists('imagewebp');
        }
        return $enabled;
    }*/
}
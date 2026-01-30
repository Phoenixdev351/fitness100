<?php
/**
* 2015-2016 NTS
*
* DISCLAIMER
*
* You are NOT allowed to modify the software. 
* It is also not legal to do any changes to the software and distribute it in your own name / brand. 
*
* @author    NTS
* @copyright 2015-2016 NTS
* @license   http://addons.prestashop.com/en/content/12-terms-and-conditions-of-use
* International Registered Trademark & Property of NTS
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Noindexfollow extends Module
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->name = 'noindexfollow';
        $this->tab = 'seo';
        $this->version = '2.2.7';
        $this->author = 'NTS';
        $this->need_instance = 0;
        $this->module_key = 'ed243afc385b95615872aee2447173f6';

        parent::__construct();

        $this->displayName = $this->l('SEO NOindex,follow');
        $this->description = $this->l('Set INDEXATION and FOLLOW by search engines Options for better SEO on each page.');
        
        /* Backward compatibility */
        if (_PS_VERSION_ < '1.5') {
                require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');
        }
    }

    /**
     * Module installation
     *
     * @return boolean Install result
     */
    public function install()
    {
        
        if (version_compare(_PS_VERSION_, '1.5', '<')) {
            $oldline = '<meta name="robots" content="{if isset($nobots)}no{/if}index,follow" />';
            $newline = '<meta name="robots" content="{if isset($nobots)}no{/if}index,{if isset($nofollow) && $nofollow}no{/if}follow" />';
            foreach (glob(_PS_ROOT_DIR_.'/themes/*/header.tpl') as $FilePath) {
                $str=Tools::file_get_contents($FilePath);
                $str=str_replace("$oldline", "$newline", $str);
                @file_put_contents($FilePath, $str);
            }
        }
		
		if (_PS_VERSION_ < 1.7) {
          //delete header template cache file
          $header_cache_file_path = (version_compare(_PS_VERSION_, '1.5', '<')?_PS_ROOT_DIR_.'/tools/smarty/compile/*.file.header.tpl.php':version_compare(_PS_VERSION_, '1.6', '<')?_PS_ROOT_DIR_.'/cache/smarty/compile/*.file.header.tpl.php':_PS_ROOT_DIR_.'/cache/smarty/compile/*/*/*/*.file.header.tpl.php');
            $compiles = glob($header_cache_file_path);
          foreach ($compiles as $file) {
            unlink($file); // delete file
          }
		}
        
        if (_PS_VERSION_ > 1.5) {
            if (Shop::isFeatureActive()) {
                Shop::setContext(Shop::CONTEXT_ALL);
            }
        }
        
        $ret = parent::install() && $this->registerHook('header');
        $cms = CMS::listCms();
		$cms_cats = CMSCategory::getSimpleCategories($this->context->language->id);
        $files = Meta::getPages();
		$categories = Category::getSimpleCategories($this->context->language->id);
		$manufacturers = Manufacturer::getManufacturers();
		$suppliers = Supplier::getSuppliers();
        foreach ($cms as $file) {
            if (version_compare(_PS_VERSION_, '1.6', '>')) {
                @Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'cms` SET `indexation` = 1 WHERE `id_cms` = '.(int)$file['id_cms']);
            }
            @Configuration::updateValue('cms_'.$file['id_cms'].'_index', 1);
            @Configuration::updateValue('cms_'.$file['id_cms'].'_follow', 1);
        }
		foreach ($cms_cats as $file) {
            @Configuration::updateValue('cms_cats_'.$file['id_cms_category'].'_index', 1);
            @Configuration::updateValue('cms_cats_'.$file['id_cms_category'].'_follow', 1);
        }
		foreach ($manufacturers as $file) {
                Configuration::updateValue('man_'.$file['id_manufacturer'].'_follow', 1);
                Configuration::updateValue('man_'.$file['id_manufacturer'].'_index', 1);
            }
		foreach ($suppliers as $file) {
                Configuration::updateValue('sup_'.$file['id_supplier'].'_follow', 1);
                Configuration::updateValue('sup_'.$file['id_supplier'].'_index', 1);
            }
        foreach ($files as $file) {
            $file = $this->truncPageNameBy($file);
            @Configuration::updateValue($file.'_index', 1);
            @Configuration::updateValue($file.'_follow', 1);
        }
        
		foreach ($categories as $cat) {
                Configuration::updateValue($cat['id_category'].'_follow_cat', 1);
                Configuration::updateValue($cat['id_category'].'_index_cat', 1);
				//Configuration::updateValue($cat['id_category'].'_follow_pro', 1);
                //Configuration::updateValue($cat['id_category'].'_index_pro', 1);
            }
		Configuration::updateValue('cat_canonical_for_p', 1);
        /* The hook "displayMobileHeader" has been introduced in v1.5.x - Called separately to fail silently if the hook does not exist */
        $this->registerHook('displayMobileHeader');

        return $ret;
    }
        
    /**
     * Module uninstallation
     *
     * @return boolean Uninstall result
     */
    public function uninstall()
    {
        $cms = CMS::listCms();
		$cms_cats = CMSCategory::getSimpleCategories($this->context->language->id);
        $files = Meta::getPages();
		$categories = Category::getSimpleCategories($this->context->language->id);
		$manufacturers = Manufacturer::getManufacturers();
		$suppliers = Supplier::getSuppliers();
        foreach ($cms as $file) {
            @Configuration::deleteByName('cms_'.$file['id_cms'].'_index');
            @Configuration::deleteByName('cms_'.$file['id_cms'].'_follow');
        }
		foreach ($cms_cats as $file) {
            @Configuration::deleteByName('cms_cats_'.$file['id_cms_category'].'_index');
            @Configuration::deleteByName('cms_cats_'.$file['id_cms_category'].'_follow');
        }
		foreach ($manufacturers as $file) {
                Configuration::deleteByName('man_'.$file['id_manufacturer'].'_follow');
                Configuration::deleteByName('man_'.$file['id_manufacturer'].'_index');
            }
		foreach ($suppliers as $file) {
                Configuration::deleteByName('sup_'.$file['id_supplier'].'_follow');
                Configuration::deleteByName('sup_'.$file['id_supplier'].'_index');
            }
        foreach ($files as $file) {
            $file = $this->truncPageNameBy($file);
            @Configuration::deleteByName($file.'_index');
            @Configuration::deleteByName($file.'_follow');
        }
		foreach ($categories as $cat) {
                @Configuration::deleteByName($cat['id_category'].'_follow_cat');
                @Configuration::deleteByName($cat['id_category'].'_index_cat');
				//@Configuration::deleteByName($cat['id_category'].'_follow_pro');
                //@Configuration::deleteByName($cat['id_category'].'_index_pro');
            }
		@Configuration::deleteByName('cat_canonical_for_p');
		@Configuration::deleteByName('noindex_product_ids');
		@Configuration::deleteByName('nofollow_product_ids');
        return parent::uninstall();
    }
    
    public function hookDisplayMobileHeader()
    {
        return $this->hookHeader();
    }
    
    public static function truncPageNameBy($page_name)
    {
		$page_name = str_replace('.','',$page_name);
		$page_name = str_replace(' ','',$page_name);
        $length = ((Tools::strlen($page_name)-25)>=0?(Tools::strlen($page_name)-25):0);
        return Tools::substr($page_name, $length, 25);
    }

    /**
     * @return string HTML/JS Content
     */
    public function hookHeader()
    {
        $smarty = $this->context->smarty;			
		$robots_index = true;
		$robots_follow = true;
		//echo Tools::getValue('controller');die;
        if (Tools::getValue('controller')=='cms' || (isset($smarty->tpl_vars['page_name']->value) && $smarty->tpl_vars['page_name']->value=='cms')) {
                $id_cms = (version_compare(_PS_VERSION_, '1.5', '<')?Tools::getValue('id_cms'):$this->context->controller->cms->id);
				$id_cms_category = (version_compare(_PS_VERSION_, '1.5', '<')?Tools::getValue('id_cms_category'):$this->context->controller->cms_category->id);
			
            if (Configuration::get('cms_'.$id_cms.'_index')==='0' && $id_cms!='') {
				$robots_index = false;
            }
            if (Configuration::get('cms_'.$id_cms.'_follow')==='0' && $id_cms!='') {
                $robots_follow = false;
            }
			if (Configuration::get('cms_cats_'.$id_cms_category.'_index')==='0' && $id_cms_category!='') {
				$robots_index = false;
            }
            if (Configuration::get('cms_cats_'.$id_cms_category.'_follow')==='0' && $id_cms_category!='') {
                $robots_follow = false;
            }
        } elseif (Tools::getValue('controller')=='supplier' || (isset($smarty->tpl_vars['page_name']->value) && $smarty->tpl_vars['page_name']->value=='supplier')) {
                $id_supplier = (int)Tools::getValue('id_supplier');
            if (Configuration::get('sup_'.$id_supplier.'_index')==='0') {
                $robots_index = false;
            }
            if (Configuration::get('sup_'.$id_supplier.'_follow')==='0') {
                $robots_follow = false;
            }
			
        } elseif (Tools::getValue('controller')=='manufacturer' || (isset($smarty->tpl_vars['page_name']->value) && $smarty->tpl_vars['page_name']->value=='manufacturer')) {
                $id_manufacturer = (int)Tools::getValue('id_manufacturer');
            if (Configuration::get('man_'.$id_manufacturer.'_index')==='0') {
                $robots_index = false;
            }
            if (Configuration::get('man_'.$id_manufacturer.'_follow')==='0') {
                $robots_follow = false;
            }
			
        } elseif (Tools::getValue('controller')=='category' || (isset($smarty->tpl_vars['page_name']->value) && $smarty->tpl_vars['page_name']->value=='category')) {
                $id_category = (int)Tools::getValue('id_category');
            if (Configuration::get($id_category.'_index_cat')==='0') {
                $robots_index = false;
            }
            if (Configuration::get($id_category.'_follow_cat')==='0') {
                $robots_follow = false;
            }
			
        } elseif (Tools::getValue('controller')=='product' || (isset($smarty->tpl_vars['page_name']->value) && $smarty->tpl_vars['page_name']->value=='product')) {
			    $id_product = (int)Tools::getValue('id_product');
				$noindex_product_ids = explode(',',Configuration::get('noindex_product_ids'));
				$nofollow_product_ids = explode(',',Configuration::get('nofollow_product_ids'));
            if (in_array($id_product,$noindex_product_ids)) {
                $robots_index = false;
            }
            if (in_array($id_product,$nofollow_product_ids)) {
                $robots_follow = false;
            }
        } else {
            
            if (version_compare(_PS_VERSION_, '1.6', '<') && version_compare(_PS_VERSION_, '1.5', '>=')) {
                if ($this->context->controller->module->id!='') {
                    $page_name = $this->truncPageNameBy($smarty->tpl_vars['page_name']->value);
                } else {
                    $page_name = $this->truncPageNameBy(Tools::getValue('controller'));
                }
            } elseif (version_compare(_PS_VERSION_, '1.6', '>=') && version_compare(_PS_VERSION_, '1.7', '<')) {
				
                $page_name = $this->truncPageNameBy($smarty->tpl_vars['page_name']->value);
				
            }elseif (version_compare(_PS_VERSION_, '1.7', '>=')) {
				
				if (isset($this->context->controller->module->id) && $this->context->controller->module->id!='') {
                    $page_name = $this->truncPageNameBy($smarty->tpl_vars['page']->value['page_name']);
                } else {
                    $page_name = $this->truncPageNameBy(Tools::getValue('controller'));
                }
            } else {
                $files = Meta::getPages();
                if (!in_array($smarty->tpl_vars['page_name']->value, $files)) {
                    $page_name = 'modules';
                } else {
                    $page_name = $this->truncPageNameBy($smarty->tpl_vars['page_name']->value);
                }
            }

            if (Configuration::get($page_name.'_index')==='0') {
                $robots_index = false;
            }
            if (Configuration::get($page_name.'_follow')==='0') {
                $robots_follow = false;
            }
        }
		
		/******************Apply noindex, follow options******************/
		
		if (version_compare(_PS_VERSION_, '1.7', '<')) {
			if (!$robots_index)
		        $smarty->assign('nobots', true);
			if (!$robots_follow)
				$smarty->assign('nofollow', true);
		} else {
		   $smarty->tpl_vars['page']->value['meta']['robots'] = ($robots_index?'index':'noindex').($robots_follow?',follow':',nofollow');
		}
		
		/******************For category pagings canonical url adding******************/
		
		if ((Tools::getValue('controller')=='category' || (isset($smarty->tpl_vars['page_name']->value) && $smarty->tpl_vars['page_name']->value=='category')) && (Tools::getValue('p')!='' || Tools::getValue('page')!='') && Configuration::get('cat_canonical_for_p')) {
				  if (version_compare(_PS_VERSION_, '1.7', '>=')) {
					 $smarty->tpl_vars['page']->value['canonical'] = $this->context->link->getCategoryLink($id_category);
					 $smarty->tpl_vars['category']->value['canonical_url'] = $this->context->link->getCategoryLink($id_category);
				  }
				  return '<link rel="canonical" href="'.$this->context->link->getCategoryLink($id_category).'" />';
        } elseif ((in_array(Tools::getValue('controller'),array('pricesdrop','newproducts','bestsales', 'supplier', 'manufacturer')) || (isset($smarty->tpl_vars['page_name']->value) && in_array($smarty->tpl_vars['page_name']->value,array('pricesdrop','newproducts','bestsales', 'supplier', 'manufacturer')))) && (Tools::getValue('p')!='' || Tools::getValue('page')!='') && Configuration::get('cat_canonical_for_p')) {
		
		$pageURL = (isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on') ? "https://" : "http://";
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        $pageURL = explode( '?', $pageURL );
		return '<link rel="canonical" href="'.$pageURL[0].'" />';
		}
    }
    /**
    * Loads asset resources
    */
    public function loadAssetCompatibility()
    {
        $css_compatibility = $js_compatibility = array ();

        $css_compatibility = array(
        $this->_path.'views/css/compatibility/font-awesome.min.css',
        $this->_path.'views/css/compatibility/bootstrap-select.min.css',
        $this->_path.'views/css/compatibility/bootstrap-responsive.min.css',
        $this->_path.'views/css/compatibility/bootstrap.min.css',
        $this->_path.'views/css/compatibility/bootstrap.extend.css',
        );
        $this->context->controller->addCSS($css_compatibility, 'all');

        // Load JS
        $js_compatibility = array(
            $this->_path.'views/js/back_1.5.js',
            $this->_path.'views/js/compatibility/bootstrap-select.min.js',
            $this->_path.'views/js/compatibility/bootstrap.min.js',
        );

        $this->context->controller->addJS($js_compatibility);
    }
    /**
     * Display the Back-office interface of the this module
     *
     * @return string HTML/JS Content
     */
    public function getContent()
    {
        $cms = CMS::listCms();
		$cms_cats = CMSCategory::getSimpleCategories($this->context->language->id);
        $files = Meta::getPages();
        $pages = array('common' => array(),'module' => array());
		$categories = Category::getSimpleCategories($this->context->language->id);
		$products = Product::getSimpleProducts($this->context->language->id);
		$manufacturers = Manufacturer::getManufacturers(false,0,false);
		$suppliers = Supplier::getSuppliers(false,0,false);
        foreach ($files as $name => $file) {
            $k = (preg_match('#^module-#', $file)) ? 'module' : 'common';
            $pages[$k][$name] = $file;
        }

        if (version_compare(_PS_VERSION_, '1.5', '>') && version_compare(_PS_VERSION_, '1.6', '<')) {
            $this->loadAssetCompatibility();
        }
        if (version_compare(_PS_VERSION_, '1.5', '>')) {
            $this->context->controller->addCss($this->_path.'views/css/prestashop-admin.css', 'all');
        }
		
		/* Update Configuration Values when settings are updated */
        if (Tools::isSubmit('SubmitNoindexfollowRobots')) {
                $str=Tools::getValue('robots_txt');
                if(file_put_contents(_PS_ROOT_DIR_.'/robots.txt', $str))
				$this->context->smarty->assign('confirmation', $this->l('Settings successfully saved.'));
		}
        /* Update Configuration Values when settings are updated */
        if (Tools::getValue('action')=='SubmitNoindexfollow') {
			
			$products = array();
			if (Tools::getValue('id_config')=='noindex_product_ids') {
				$products = explode(",",Configuration::get('noindex_product_ids'));
				if ((int)Tools::getValue('value') == 1 && ($key = array_search((int)Tools::getValue('id_product'), $products)) !== false)
				   unset($products[$key]);
				else if((int)Tools::getValue('value') == 0 && ($key = array_search((int)Tools::getValue('id_product'), $products)) === false)
				   $products[] = (int)Tools::getValue('id_product');
			    $value = implode(",",$products);
			}else if (Tools::getValue('id_config')=='nofollow_product_ids') {
				$products = explode(",",Configuration::get('nofollow_product_ids'));
				if ((int)Tools::getValue('value') == 1 && ($key = array_search((int)Tools::getValue('id_product'), $products)) !== false)
				   unset($products[$key]);
				else if((int)Tools::getValue('value') == 0 && ($key = array_search((int)Tools::getValue('id_product'), $products)) === false)
				   $products[] = (int)Tools::getValue('id_product');
			    $value = implode(",",$products);
			} else {
			  $value = Tools::getValue('value');
			}
			if (Configuration::updateValue(Tools::getValue('id_config'), $value)) {
			   die(Tools::jsonEncode(array(
                    'code' => '1',
                    'msg' => $this->l('Settings successfully saved.'),
                )));
			} else {
				die(Tools::jsonEncode(array(
                    'code' => '0',
                    'msg' => $this->l('Error occured while updating!'),
                )));
			}
			/*Configuration::updateValue('cat_canonical_for_p', Tools::getValue('cat_canonical_for_p'));
			
			$noindex_product_ids = array();
			$nofollow_product_ids = array();
			foreach(Tools::getValue('noindex_product_ids') as $key=>$p)
			       if($p==0)
				      $noindex_product_ids[] = $key;
			foreach(Tools::getValue('nofollow_product_ids') as $key=>$p)
			       if($p==0)
				      $nofollow_product_ids[] = $key;
			   
			Configuration::updateValue('noindex_product_ids', implode(",",$noindex_product_ids));
			Configuration::updateValue('nofollow_product_ids', implode(",",$nofollow_product_ids));
			
			foreach ($categories as $cat) {
                Configuration::updateValue($cat['id_category'].'_follow_cat', Tools::getValue($cat['id_category'].'_follow_cat'));
                Configuration::updateValue($cat['id_category'].'_index_cat', Tools::getValue($cat['id_category'].'_index_cat'));
				//Configuration::updateValue($cat['id_category'].'_follow_pro', Tools::getValue($cat['id_category'].'_follow_pro'));
                //Configuration::updateValue($cat['id_category'].'_index_pro', Tools::getValue($cat['id_category'].'_index_pro'));
            }
			
            foreach ($cms as $file) {
                if (version_compare(_PS_VERSION_, '1.6', '>')) {
                    @Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'cms` SET `indexation` = '.(int)Tools::getValue('cms_'.$file['id_cms'].'_index').' WHERE `id_cms` = '.(int)$file['id_cms']);
                }
                Configuration::updateValue('cms_'.$file['id_cms'].'_follow', Tools::getValue('cms_'.$file['id_cms'].'_follow'));
                Configuration::updateValue('cms_'.$file['id_cms'].'_index', Tools::getValue('cms_'.$file['id_cms'].'_index'));
            }
			foreach ($cms_cats as $file) {
                Configuration::updateValue('cms_cats_'.$file['id_cms_category'].'_follow', Tools::getValue('cms_cats_'.$file['id_cms_category'].'_follow'));
                Configuration::updateValue('cms_cats_'.$file['id_cms_category'].'_index', Tools::getValue('cms_cats_'.$file['id_cms_category'].'_index'));
            }
			foreach ($manufacturers as $file) {
                Configuration::updateValue('man_'.$file['id_manufacturer'].'_follow', Tools::getValue('man_'.$file['id_manufacturer'].'_follow'));
                Configuration::updateValue('man_'.$file['id_manufacturer'].'_index', Tools::getValue('man_'.$file['id_manufacturer'].'_index'));
            }
			foreach ($suppliers as $file) {
                Configuration::updateValue('sup_'.$file['id_supplier'].'_follow', Tools::getValue('sup_'.$file['id_supplier'].'_follow'));
                Configuration::updateValue('sup_'.$file['id_supplier'].'_index', Tools::getValue('sup_'.$file['id_supplier'].'_index'));
            }
            foreach ($files as $file) {
                $file = $this->truncPageNameBy($file);
                Configuration::updateValue($file.'_follow', Tools::getValue($file.'_follow'));
                Configuration::updateValue($file.'_index', Tools::getValue($file.'_index'));
            }
            
            if (version_compare(_PS_VERSION_, '1.5', '<')) {
                Configuration::updateValue('modules_follow', Tools::getValue('modules_follow'));
                Configuration::updateValue('modules_index', Tools::getValue('modules_index'));
            }*/
            
            //$this->context->smarty->assign('confirmation', $this->l('Settings successfully saved.'));
        }
        $error = '';
        if (strpos(Tools::file_get_contents(_PS_THEME_DIR_.'header.tpl'), '<meta name="robots" content="{if isset($nobots)}no{/if}index,{if isset($nofollow) && $nofollow}no{/if}follow" />') === false && version_compare(_PS_VERSION_, '1.7', '<')) {
            $error = $this->displayError($this->l('Meta tag line of ROBOTS is missing. Remove existing ROBOTS meta tag line and add following line in header file :-> ')._PS_THEME_DIR_.'<b>header.tpl</b><br /><b>'.htmlentities('<meta name="robots" content="{if isset($nobots)}no{/if}index,{if isset($nofollow) && $nofollow}no{/if}follow" /></b>'));
        }
		
        $this->smarty->assign(array(
         'postAction' => $this->context->link->getAdminLink('AdminModules').'&configure=noindexfollow&tab_module=seo&module_name=noindexfollow',
         'cms' => $cms,
		 'cms_cats' => $cms_cats,
         'pages' => $pages,
		 'categories' => $categories,
		 'products' => $products,
		 'manufacturers' => $manufacturers,
		 'suppliers' => $suppliers,
		 'products_noindex' => @explode(',',Configuration::get('noindex_product_ids')),
		 'products_nofollow' => @explode(',',Configuration::get('nofollow_product_ids')),
         'module_path' => $this->_path,
         'PS_VERSION' => _PS_VERSION_,
		 'robots_file' => Tools::file_get_contents(_PS_ROOT_DIR_.'/robots.txt'),
         ));
        
        return $error.$this->display(__FILE__, 'views/templates/admin/configure.tpl');
    }
}

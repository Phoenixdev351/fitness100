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

class StBlogArchives extends Module
{
    public static $moduleRoutes = array();
	public function __construct()
	{
		$this->name          = 'stblogarchives';
		$this->tab           = 'front_office_features';
		$this->version       = '1.1.3';
		$this->author        = 'SUNNYTOO.COM';
		$this->need_instance = 0;
		$this->bootstrap 	 = true;
		parent::__construct();
        
        $route = Configuration::get('ST_BLOG_ROUNT_NAME', $this->context->language->id);
        if (!$route) $route = 'blog';
        self::$moduleRoutes = array(
            'module-stblogarchives-default' => array(
                'controller' =>  'default',
                'rule' =>        $route.'/{m}',
                'keywords' => array(
                    'm'            =>   array('regexp' => '[0-9]+', 'param' => 'm'),
                ),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'stblogarchives',
                )
            ),
        );
        
        $this->displayName = $this->getTranslator()->trans('Blog Module - Archives', array(), 'Modules.Stblog.Admin');
        $this->description = $this->getTranslator()->trans('The archives module allows you to display a tree list of the months and past months.', array(), 'Modules.Stblog.Admin');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->controllers = array('default');
	}

	public function install()
	{
		if (!parent::install()
            || !$this->registerHook('header')
			|| !$this->registerHook('displayStBlogLeftColumn')
			|| !$this->registerHook('displayStBlogRightColumn')
            || !$this->registerHook('moduleRoutes')
            || !$this->registerHook('gSitemapAppendUrls')
        )
			return false;
		return true;
	}
    
	private function _prepareHook()
	{
        include_once(dirname(__FILE__).'/classes/StBlogArchivesClass.php');

        $archives = StBlogArchivesClass::getArchives();  
        
        if(!is_array($archives) || !count($archives))
            return false;
        
		$this->smarty->assign(array(
            'archives' => $archives,
            'current_year' => substr(Tools::getValue('m'),0,4)
        ));
        return true; 
	}
	public function hookDisplayStBlogRightColumn($params)
	{
	    if(!Module::isInstalled('stblog') || !Module::isEnabled('stblog'))
            return false;
            
        if(!$this->_prepareHook())
            return false;
            
	    return $this->display(__FILE__, 'stblogarchives.tpl');
	}
    
	public function hookDisplayStBlogLeftColumn($params)
	{
        return $this->hookDisplayStBlogRightColumn($params); 
	}

    public function hookGSitemapAppendUrls($params)
    {
        if(!Module::isEnabled('stblogarchives')) {
            return true;
        }
        $id_lang = isset($params['lang']) && $params['lang']['id_lang'] ? $params['lang']['id_lang'] : $this->content->language->id;
        $links = array();
        Shop::addTableAssociation('st_blog', array('type' => 'shop'));
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
            SELECT DISTINCT b.`id_st_blog`, DATE_FORMAT(b.`date_add`,"%Y-%m-%M") date_add, b.`id_st_blog`, b.`date_upd`
            FROM `'._DB_PREFIX_.'st_blog` b
            LEFT JOIN `'._DB_PREFIX_.'st_blog_lang` bl ON (b.`id_st_blog` = bl.`id_st_blog`)
            '.Shop::addSqlAssociation('st_blog','b').'
            WHERE bl.`id_lang` = '.(int)$id_lang.'
            AND b.`active` = 1
            GROUP BY DATE_FORMAT(b.`date_add`, "%Y%m")
            ORDER BY b.`date_add` DESC'
        );
        foreach($result AS $row)
        {
            list($Y, $m, $M) =  explode('-', $row['date_add']);
            $url = $this->context->link->getModuleLink('stblogarchives','default',array('m'=>$Y.$m), null, (int)$id_lang);
            $links[]  = array(
                'type' => 'module', 
                'page' => 'stblogarchives', 
                'lastmod' => $row['date_upd'], 
                'link' => $url, 
                'image' => false
            );
        }
        return $links;
    }
    
	public function hookModuleRoutes($params)
    {
        return self::$moduleRoutes;
    }
    
    public function hookHeader()
	{
		$this->context->controller->addJS(_THEME_JS_DIR_.'tools/treeManagement.js');
	}
}
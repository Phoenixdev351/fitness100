<?php
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */

// if (!defined('_PS_VERSION_')) { exit; }
class Ets_superspeed_defines
{
    protected static $instance;
    public $is17 = false;
    public $is16 = false;
    public $isblog = false;
    public $isSlide = false;
    public $isBanner = false;
    public function __construct()
	{
        $this->name= 'ets_superspeed';
	    $this->context = Context::getContext();
        if (version_compare(_PS_VERSION_, '1.7', '>='))
            $this->is17 = true;
        if (version_compare(_PS_VERSION_, '1.7', '<'))
            $this->is16 = true;
        if (Module::isInstalled('ybc_blog') && Module::isEnabled('ybc_blog'))
            $this->isblog = true;
        if ((Module::isInstalled('ps_imageslider') && Module::isEnabled('ps_imageslider')) || (Module::isInstalled('homeslider') && Module::isEnabled('homeslider')))
            $this->isSlide = true;
        if ((Module::isInstalled('blockbanner') && Module::isEnabled('blockbanner')) || (Module::isInstalled('ps_banner') && Module::isEnabled('ps_banner')))
            $this->isBanner = true;
    }
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new Ets_superspeed_defines();
        }
        return self::$instance;
    }
    protected static $pages;
    public function getPages()
    {
        if(!self::$pages)
        {
            $pages = array(
                array(
                    'id' => 'index',
                    'label' => $this->l('Home page'),
                    'value' => 'index',
                    'extra' => 'ETS_SPEED_TIME_CACHE_INDEX'
                ),
                array(
                    'id' => 'category',
                    'label' => $this->l('Category page'),
                    'value' => 'category',
                    'extra' => 'ETS_SPEED_TIME_CACHE_CATEGORY'
                ),
                array(
                    'id' => 'product',
                    'label' => $this->l('Product page'),
                    'value' => 'product',
                    'extra' => 'ETS_SPEED_TIME_CACHE_PRODUCT'
                ),
                array(
                    'id' => 'cms',
                    'label' => $this->l('CMS page'),
                    'value' => 'cms',
                    'extra' => 'ETS_SPEED_TIME_CACHE_CMS',
                ),
                array(
                    'id' => 'newproducts',
                    'label' => $this->l('New products page'),
                    'value' => 'newproducts',
                    'extra' => 'ETS_SPEED_TIME_CACHE_NEWPRODUCTS',
                ),
                array(
                    'id' => 'bestsales',
                    'label' => $this->l('Best-seller page'),
                    'value' => 'bestsales',
                    'extra' => 'ETS_SPEED_TIME_CACHE_BESTSALES',
                ),
                array(
                    'id' => 'supplier',
                    'label' => $this->l('Supplier page'),
                    'value' => 'supplier',
                    'extra' => 'ETS_SPEED_TIME_CACHE_SUPPLIER',
                ),
                array(
                    'id' => 'manufacturer',
                    'label' => $this->l('Manufacturer page'),
                    'value' => 'manufacturer',
                    'extra' => 'ETS_SPEED_TIME_CACHE_MANUFACTURER',
                ),
                array(
                    'id' => 'contact',
                    'label' => $this->l('Contact page'),
                    'value' => 'contact',
                    'extra' => 'ETS_SPEED_TIME_CACHE_CONTACT',
                ),
                array(
                    'id' => 'pricesdrop',
                    'label' => $this->l('Prices drop page'),
                    'value' => 'pricesdrop',
                    'extra' => 'ETS_SPEED_TIME_CACHE_PRICESDROP',
                ),
                array(
                    'id' => 'sitemap',
                    'label' => $this->l('Sitemap page'),
                    'value' => 'sitemap',
                    'extra' => 'ETS_SPEED_TIME_CACHE_SITEMAP',
                ));
            if ($this->isblog) {
                $pages[] = array(
                    'id' => 'blog',
                    'label' => $this->l('Blog pages'),
                    'value' => 'blog',
                    'extra' => 'ETS_SPEED_TIME_CACHE_BLOG',
                );
            }
            if(Module::isEnabled('ph_productfeed'))
            {
                $pages[] = array(
                    'id' => 'productfeed',
                    'label' => $this->l('Product feed pages'),
                    'value' => 'productfeed',
                    'extra' => 'ETS_SPEED_TIME_CACHE_PRODUCTFEED',
                );
            }
            if(Module::isEnabled('ets_collections'))
            {
                $pages[] = array(
                    'id' => 'collection',
                    'label' => $this->l('Collection pages'),
                    'value' => 'collection',
                    'extra' => 'ETS_SPEED_TIME_CACHE_COLLECTION',
                );
            }
            self::$pages = $pages;
        }
        return self::$pages;
    }
    protected static $_cache_image_tabs;
    protected static $_cache_page_tabs;
    protected static $config_gzip;
    protected static $datas_dynamic;
    protected static $dynamic_hooks;
    protected static $hooks;
    protected static $admin_tabs;
    protected static $inputs;
    protected static $minization_inputs;
    public function getFieldConfig($field_type,$render_form = true)
    {
        switch ($field_type) {
            case '_cache_image_tabs':
              if(!self::$_cache_image_tabs)
              {
                  self::$_cache_image_tabs = array(
                      'image_old' => $this->l('Optimize images'),
                      'image_upload'=> $this->l('Upload to optimize'),
                      'image_browse' => $this->l('Browse images'),
                      'image_cleaner' => $this->l('Image cleaner'),
                      'image_lazy_load' => $this->l('Lazy load'),
                  );
              }
            return self::$_cache_image_tabs;
            case '_cache_page_tabs':
              if(!self::$_cache_page_tabs)
              {
                  self::$_cache_page_tabs = array(
                      'page_setting'=> $this->l('Page cache settings'),
                      'dynamic_contents' => $this->l('Exceptions'),
                      'livescript' => $this->l('Live JavaScript'),
                      'page-list-caches' => $this->l('Cached URLs'),
                      'page-list-no-caches' => $this->l('URLs failed to create a cache'),
                      'page-list-log-clear-history' => $this->l('Cache clear history'),
                  );
              }
            return self::$_cache_page_tabs;
            case '_config_images':
            return $this->configFieldImages();
            case '_config_gzip':
              if(!self::$config_gzip)
              {
                  self::$config_gzip = array(
                      array(
                          'type' => 'switch',
                          'label' => $this->l('Enable browser cache and Gzip'),
                          'name' => 'PS_HTACCESS_CACHE_CONTROL',
                          'values' => array(
                              array(
                                  'id' => 'active_on',
                                  'value' => 1,
                                  'label' => $this->l('On')
                              ),
                              array(
                                  'id' => 'active_off',
                                  'value' => 0,
                                  'label' => $this->l('Off')
                              )
                          ),
                          'desc'=> $this->l('Store several resources locally on web browser (images, icons, web fonts, etc.)'),
                      ),
                      array(
                          'type' => 'switch',
                          'label' => $this->l('Use default Prestashop settings'),
                          'name' => 'ETS_SPEED_USE_DEFAULT_CACHE',
                          'values' => array(
                              array(
                                  'id' => 'active_on',
                                  'value' => 1,
                                  'label' => $this->l('Yes')
                              ),
                              array(
                                  'id' => 'active_off',
                                  'value' => 0,
                                  'label' => $this->l('No')
                              )
                          ),
                          'form_group_class'=>'enable_cache',
                          'desc'=> $this->l('Apply default Prestashop settings for browser cache and Gzip'),
                      ),
                      array(
                          'type'=>'range',
                          'label'=>$this->l('Browser cache image lifetime'),
                          'name'=>'ETS_SPEED_LIFETIME_CACHE_IMAGE',
                          'min'=>'1',
                          'max'=>'12',
                          'unit'=> $this->l('Month'),
                          'units'=> $this->l('Months'),
                          'form_group_class'=>'use_default form_group_range_small',
                          'hint' => $this->l('Specify how long web browsers should keep images stored locally'),
                      ),
                      array(
                          'type'=>'range',
                          'label'=>$this->l('Browser cache icon lifetime'),
                          'name'=>'ETS_SPEED_LIFETIME_CACHE_ICON',
                          'min'=>'1',
                          'max'=>'10',
                          'unit'=> $this->l('Year'),
                          'units'=> $this->l('Years'),
                          'form_group_class'=>'use_default form_group_range_small',
                          'hint' => $this->l('Specify how long web browsers should keep icons stored locally'),
                      ),
                      array(
                          'type'=>'range',
                          'label'=>$this->l('Browser cache css lifetime'),
                          'name'=>'ETS_SPEED_LIFETIME_CACHE_CSS',
                          'min'=>'1',
                          'max'=>'48',
                          'unit'=> $this->l('Week'),
                          'units'=> $this->l('Weeks'),
                          'form_group_class'=>'use_default form_group_range_small',
                          'hint' => $this->l('Specify how long web browsers should keep CSS stored locally'
                          ),
                      ),
                      array(
                          'type'=>'range',
                          'label'=>$this->l('Browser cache js lifetime'),
                          'name'=>'ETS_SPEED_LIFETIME_CACHE_JS',
                          'min'=>'1',
                          'max'=>'48',
                          'unit'=> $this->l('Week'),
                          'units'=> $this->l('Weeks'),
                          'form_group_class'=>'use_default form_group_range_small',
                          'hint' => $this->l('Specify how long web browsers should keep JavaScript files stored locally'),
                      ),
                      array(
                          'type'=>'range',
                          'label'=>$this->l('Browser cache font lifetime'),
                          'name'=>'ETS_SPEED_LIFETIME_CACHE_FONT',
                          'min'=>'1',
                          'max'=>'10',
                          'unit'=> $this->l('Year'),
                          'units'=> $this->l('Years'),
                          'form_group_class'=>'use_default form_group_range_small',
                          'hint' => $this->l('Specify how long web browsers should keep text fonts stored locally'),
                      )
                  );
              }
              return self::$config_gzip;
            case '_datas_dynamic':
              if(!self::$datas_dynamic) {
                  self::$datas_dynamic = array(
                      'connections' => array(
                          'table' => 'connections',
                          'name' => $this->l('Connections log'),
                          'desc' => $this->l('The records including info of every connections to your website (each visitor is a connection)'),
                          'where' => '',
                      ),
                      'connections_source' => array(
                          'table' => 'connections_source',
                          'name' => $this->l('Page views'),
                          'desc' => $this->l('Measure the total number of views a particular page has received'),
                          'where' => '',
                      ),
                      'cart_rule' => array(
                          'table' => 'cart_rule',
                          'name' => $this->l('Useless discount codes'),
                          'desc' => $this->l('Expired discount codes'),
                          'where' => ' WHERE date_to!="0000-00-00 00:00:00" AND date_to  < "' . pSQL(date('Y-m-d H:i:s')) . '"',
                          'table2' => 'specific_price',
                          'where2' => ' WHERE `to` !="0000-00-00 00:00:00" AND `to`  < "' . pSQL(date('Y-m-d H:i:s')) . '"',
                      ),
                      'cart' => array(
                          'table' => 'cart',
                          'name' => $this->l('Abandoned carts'),
                          'desc' => $this->l('The online cart that a customer added items to, but exited the website without purchasing those items'),
                          'where' => ' WHERE id_cart NOT IN (SELECT id_cart FROM `' . _DB_PREFIX_ . 'orders`) AND date_add < "' . pSQL(date('Y-m-d H:i:s', strtotime('-3 DAY'))) . '"',
                      ),
                      'guest' => array(
                          'table' => 'guest',
                          'name' => $this->l('Guest data'),
                          'desc' => $this->l('Information of unregistered users (excluding users having orders)'),
                          'where' => ' WHERE id_customer=0',
                      ),
                  );
              }
            return self::$datas_dynamic;
            case '_dynamic_hooks':
              if(!self::$dynamic_hooks)
              {
                  self::$dynamic_hooks=array(
                      'displaytop',
                      'top',
                      'displaynav',
                      'displaynav1',
                      'displaynav2',
                      'displaytopcolumn',
                      'displayhome',
                      'home',
                      'displayBodyBottom',
                      'displayhometab',
                      'displaybanner',
                      'displayhometabcontent',
                      'displayrightcolumn',
                      'displayrightcolumnproduct',
                      'displayBeforeBodyClosingTag',
                      'displayfooterproduct',
                      'displayproductbuttons',
                      'displayleftcolumn',
                      'displayfooter',
                      'footer',
                      'displayCart',
                      'displayRecommendProduct',
                      'displayProductActions',
                      'displayProductButtons',
                      'displayEtsVPCustom',
                      'displayProductAdditionalInfo',
                      'displayCustomProductActions',
                      'displayEtsEptBellowProductTitle',
                      'displayNavFullWidth',
                      'displayProductPriceBlock',
                      'displayReassurance',
                      'displayEstEptCustomize',
                      'displayProductListReviews',
                      'customFlashSaleShortCodeHook',
                      'displayFooterAfter',
                      'displayBelowHeader',
                      'displayFooterBefore',
                      'displayTopBar',
                      'displayMainMenu',
                      'displaySideCartPromo',
                      'displayMobileNav',
                      'displayCheckoutMobileNav'
                  );
              }
              return self::$dynamic_hooks;
            case '_hooks':
              if(!self::$hooks)
              self::$hooks = array(
                'actionHtaccessCreate',
                'actionWatermark',
                'actionProductAdd',
                'displayAdminLeft',
                'displayBackOfficeHeader',
                'displayHeader',
                'actionPageCacheAjax',
                'actionObjectAddAfter',
                'actionObjectUpdateAfter',
                'actionObjectDeleteAfter',
                'actionObjectProductUpdateAfter',
                'actionObjectProductAddAfter',
                'actionObjectProductDeleteAfter',
                'actionObjectCategoryUpdateAfter',
                'actionObjectCategoryAddAfter',
                'actionObjectCategoryDeleteAfter',
                'actionOnImageResizeAfter',
                'actionModuleUnRegisterHookAfter',
                'actionModuleRegisterHookAfter',
                'actionOutputHTMLBefore',
                'actionAdminPerformanceControllerSaveAfter',
                'actionValidateOrder',
                'actionObjectCMSCategoryUpdateAfter',
                'actionObjectCMSCategoryDeleteAfter',
                'displayImagesBrowse',
                'displayImagesUploaded',
                'displayImagesCleaner',
                'actionUpdateBlogImage',
                'actionUpdateBlog',
                'actionCartUpdateQuantityBefore',
                'actionObjectProductInCartDeleteAfter',
                'actionDeleteAllCache',
                'actionPerformancePageSmartySave'
            );
            return self::$hooks;
            case '_admin_tabs':
              if(!self::$admin_tabs)
                self::$admin_tabs=array(
                    array(
                        'class_name' => 'AdminSuperSpeedStatistics',
                        'tab_name' => $this->l('Dashboard'),
                        'tabname' => 'Dashboard',
                        'icon'=>'icon icon-dashboard',
                        'logo' => 'c1.png',
                    ),
                    array(
                        'class_name' => 'AdminSuperSpeedPageCachesAndMinfication',
                        'tab_name' => $this->l('Cache and minfication'),
                        'tabname' => 'Cache and minfication',
                        'icon'=>'icon icon-pagecache',
                        'logo' => 'c2.png',
                        'sub_menu' => array(
                            'AdminSuperSpeedPageCaches' => array(
                                'class_name' => 'AdminSuperSpeedPageCaches',
                                'tab_name' => $this->l('Page cache'),
                                'tabname' => 'Page cache',
                                'icon'=>'icon icon-pagecache',
                                'logo' => 'c2.png',
                            ),
                            'AdminSuperSpeedMinization'=>array(
                                'class_name' => 'AdminSuperSpeedMinization',
                                'tab_name' => $this->l('Server cache and minification'),
                                'tabname' => 'Server cache and minification',
                                'icon'=>'icon icon-speedminization',
                                'logo' => 'c4.png',
                            ),
                            'AdminSuperSpeedGzip'=>array(
                                'class_name' => 'AdminSuperSpeedGzip',
                                'tab_name' => $this->l('Browser cache and Gzip'),
                                'tabname' => 'Browser cache and Gzip',
                                'icon'=>'icon icon-speedgzip',
                                'logo' => 'c5.png',
                            ),
                        ),
                    ),
                    array(
                        'class_name' => 'AdminSuperSpeedImage',
                        'tab_name' => $this->l('Image optimization'),
                        'tabname' => 'Image optimization',
                        'icon'=>'icon icon-speedimage',
                        'logo' => 'c3.png',
                    ),
                    array(
                        'class_name' => 'AdminSuperSpeedDatabase',
                        'tab_name' => $this->l('Database optimization'),
                        'tabname' => 'Database optimization',
                        'icon'=>'icon icon-speeddatabase',
                        'logo' => 'c6.png',
                    ),
                    array(
                        'class_name' => 'AdminSuperSpeedSystemAnalytics',
                        'tab_name' => $this->l('System Analytics'),
                        'tabname' => 'System Analytics',
                        'icon'=>'icon icon-analytics',
                        'logo' => 'c7.png',
                    ),
                    array(
                        'class_name' => 'AdminSuperSpeedHelps',
                        'tab_name' => $this->l('Help'),
                        'tabname' => 'Help',
                        'icon'=>'icon icon-help',
                        'logo' => 'c8.png',
                    ),
                );
                return self::$admin_tabs;
            case '_page_caches':
                if(!self::$inputs)
                {
                    self::$inputs = array(
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Page cache'),
                            'name' => 'ETS_SPEED_ENABLE_PAGE_CACHE',
                            'form_group_class' => 'form_cache_page page_setting',
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->l('On')
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->l('Off')
                                )
                            ),
                        ),
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Compress cache file'),
                            'name' => 'ETS_SPEED_COMPRESS_CACHE_FIIE',
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->l('On')
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->l('Off')
                                )
                            ),
                            'desc' => $this->l('Compress HTML cache files into .zip files, this helps save your disk space but page loading time will be a bit longer (because server needs to unzip compressed files before displaying them to website visitors)'),
                            'form_group_class' => 'form_cache_page page_setting',
                        ),
                        'ETS_SP_KEEP_NAME_CSS_JS' => array(
                            'type' => 'switch',
                            'label' => $this->l('Preserve combined CSS/JS filenames after cache reset'),
                            'name' => 'ETS_SP_KEEP_NAME_CSS_JS',
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->l('On')
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->l('Off')
                                )
                            ),
                            'form_group_class' => 'form_cache_page page_setting',
                            'default' => 0,
                        ),
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Generate particular page cache for each user-agent'),
                            'name' => 'ETS_SPEED_CHECK_USER_AGENT',
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->l('On')
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->l('Off')
                                )
                            ),
                            'desc' => $this->l('Enable this option if your website has particular views for desktop and mobile'),
                            'form_group_class' => 'form_cache_page page_setting',
                        ),
                        array(
                            'type' => 'checkbox',
                            'label' => $this->l('Pages to cache'),
                            'name' => 'ETS_SPEED_PAGES_TO_CACHE',
                            'form_group_class' => 'form_cache_page page_setting',
                            'values' => array(
                                'query' => $render_form ? $this->getPages():array(),
                                'id' => 'value',
                                'name' => 'label',

                            ),
                        ),
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Cache hits'),
                            'name' => 'ETS_RECORD_PAGE_CLICK',
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->l('On')
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->l('Off')
                                )
                            ),
                            'desc' => $this->l('Enable this option to see how many times a page cache is used'),
                            'form_group_class' => 'form_cache_page page_setting',
                            'default' => 1,
                        ),
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Automatically delete page cache when change performance configuration'),
                            'name' => 'SP_DEL_CACHE_CHANGE_PERFORMANCE',
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->l('On')
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->l('Off')
                                )
                            ),
                            'form_group_class' => 'form_cache_page page_setting',
                            'default' => 0,
                        ),
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Automatically delete page cache when install/uninstall hook'),
                            'name' => 'SP_DEL_CACHE_HOOK_CHANGE',
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->l('On')
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->l('Off')
                                )
                            ),
                            'form_group_class' => 'form_cache_page page_setting',
                            'default' => 1,
                        ),
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Automatically delete page cache when editing page data'),
                            'name' => 'ETS_AUTO_DELETE_CACHE_WHEN_UPDATE_OBJ',
                            'desc' => $this->l('Usually, page data will be updated after the cache lifetime you set up above. If you enable this option, page data will immediately update after your edit. Please consider before turning on this option since the cache is continuously updated and may take a lot of server resources.'),
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->l('On')
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->l('Off')
                                )
                            ),
                            'form_group_class' => 'form_cache_page page_setting',
                            'default' => 1,
                        ),
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Automatically delete page cache when adding or deleting a product from cart'),
                            'name' => 'ETS_AUTO_DELETE_CACHE_WHEN_CHECKOUT',
                            'desc' => $this->l('Please consider this before enabling this option. When the option is enabled, the page cache will be updated again whenever the user adds or removes a product from the cart. This option should only be enabled when the admin wants to immediately update the available product quantity on the product detail page or product category page as soon as the user adds/removes products from the cart. If your site does not care about the number of available products, this option should be disabled.'),
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->l('On')
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->l('Off')
                                )
                            ),
                            'form_group_class' => 'form_cache_page page_setting',
                            'default' => 0,
                        ),
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Always reload cart and user information dynamically, even when the shopping cart is empty or the customer is not logged in'),
                            'name' => 'ETS_ALWAYS_LOAD_DYNAMIC_CONTENT',
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->l('On')
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->l('Off')
                                )
                            ),
                            'form_group_class' => 'form_cache_page page_setting',
                            'default' => 0,
                        ),
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Dynamically load product prices via AJAX'),
                            'name' => 'ETS_DYNAMIC_LOAD_PRICES',
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->l('On')
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->l('Off')
                                )
                            ),
                            'form_group_class' => 'form_cache_page page_setting',
                            'default' => 0,
                        ),
                        'ETS_SP_CLEAR_CACHE_CRS' => array(
                            'type' => 'switch',
                            'label' => $this->l('Automatically delete the page cache when modifying "Cross Selling PRO" module'),
                            'name' => 'ETS_SP_CLEAR_CACHE_CRS',
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->l('On')
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->l('Off')
                                )
                            ),
                            'form_group_class' => 'form_cache_page page_setting',
                            'default' => 1,
                        ),
                        'ETS_SP_CLEAR_CACHE_HC' => array(
                            'type' => 'switch',
                            'label' => $this->l('Automatically delete the page cache when modifying "Home Products PRO" module'),
                            'name' => 'ETS_SP_CLEAR_CACHE_HC',
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->l('On')
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->l('Off')
                                )
                            ),
                            'form_group_class' => 'form_cache_page page_setting',
                            'default' => 1,
                        ),
                        'ETS_MM_CLEAR_CACHE_SPEED' => array(
                            'type' => 'switch',
                            'label' => $this->l('Automatically delete the page cache when modifying "Mega Menu PRO" module'),
                            'name' => 'ETS_MM_CLEAR_CACHE_SPEED',
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->l('On')
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->l('Off')
                                )
                            ),
                            'form_group_class' => 'form_cache_page page_setting',
                            'default' => 1,
                        ),
                        'ETS_EPT_ENABLE_CLEAR_CACHE_SS' => array(
                            'type' => 'switch',
                            'label' => $this->l('Automatically delete the page cache when modifying "Custom fields & tabs on product page" module'),
                            'name' => 'ETS_EPT_ENABLE_CLEAR_CACHE_SS',
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->l('On')
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->l('Off')
                                )
                            ),
                            'form_group_class' => 'form_cache_page page_setting',
                            'default' => 1,
                        ),
                        array(
                            'type' => 'text',
                            'label' => $this->l('The delay time between page loading time checking'),
                            'name' => 'ETS_TIME_AJAX_CHECK_SPEED',
                            'desc' => $this->l('You can edit the time amount between 2 page loading time checking using Ajax request. The loading time result will be used to display the "Page speed timeline" on Dashboard. Recommended value: 5 seconds.'),
                            'form_group_class' => 'form_cache_page page_setting ETS_TIME_AJAX_CHECK_SPEED',
                            'suffix' => $this->l('seconds'),
                            'default' => 5,
                            'col' => 6,
                            'required' => true,
                            'validate' => 'isUnsignedFloat',
                        ),
                        array(
                            'type' => 'buttons',
                            'buttons' => array(
                                array(
                                    'type' => 'button',
                                    'name' => 'btnSubmitPageCache',
                                    'title' => $this->l('Save'),
                                    'icon' => 'process-icon-save',
                                    'class' => 'pull-right',
                                ),
                                array(
                                    'type' => 'button',
                                    'name' => 'clear_all_page_caches',
                                    'title' => $this->l('Clear all page caches'),
                                    'icon' => 'icon-trash',
                                    'class' => 'pull-left',
                                ),
                            ),
                            'name' => '',
                            'form_group_class' => 'form_cache_page page_setting group-button',
                        ),
                        array(
                            'type' => 'textarea',
                            'name' => 'ETS_SPEED_PAGES_EXCEPTION',
                            'label' => $this->l('URL exception(s)'),
                            'row' => '4',
                            'desc' => $this->l('Any URL containing at least 1 string entered above will not be cached. Please enter each string on 1 line.'),
                            'form_group_class' => 'form_cache_page dynamic_contents url_exceptions',
                        ),
                        array(
                            'type' => 'buttons',
                            'buttons' => array(
                                array(
                                    'type' => 'button',
                                    'name' => 'btnSubmitSuperSpeedException',
                                    'title' => $this->l('Save'),
                                    'icon' => 'icon-save',
                                    'class' => 'pull-left',
                                ),
                            ),
                            'name' => '',
                            'form_group_class' => 'form_cache_page dynamic_contents group-button button_border_bottom',
                        ),
                        array(
                            'type' => 'list_module',
                            'name' => 'dynamic_modules',
                            'modules' => $render_form ? $this->getModulesDynamic():array(),
                            'form_group_class' => 'form_cache_page dynamic_contents',
                            'col' => 12,
                        ),
                        array(
                            'label' => $this->l('Live JavaScript'),
                            'type' => 'textarea',
                            'name' => 'live_script',
                            'rows' => 32,
                            'form_group_class' => 'form_cache_page livescript',
                            'desc' => $this->l('Enter here custom JavaScript code that you need to execute after non-cached content are fully loaded. Be careful with your code, invalid JavaScript code may result in global JavaScript errors on the front office.'),
                        ),
                        array(
                            'type' => 'buttons',
                            'buttons' => array(
                                array(
                                    'type' => 'button',
                                    'name' => 'btnSubmitPageCache',
                                    'title' => $this->l('Save'),
                                    'icon' => 'process-icon-save',
                                    'class' => 'pull-right',
                                ),
                            ),
                            'name' => '',
                            'form_group_class' => 'form_cache_page livescript group-button',
                        ),

                    );
                    if($render_form && !self::getIDModuleByName('ets_crosssell') && isset(self::$inputs['ETS_SP_CLEAR_CACHE_CRS']))
                    {
                        unset(self::$inputs['ETS_SP_CLEAR_CACHE_CRS']);
                    }
                    if($render_form && !self::getIDModuleByName('ets_homecategories') && isset(self::$inputs['ETS_SP_CLEAR_CACHE_HC']))
                    {
                        unset(self::$inputs['ETS_SP_CLEAR_CACHE_HC']);
                    }
                    if($render_form && !self::getIDModuleByName('ets_extraproducttabs') && isset(self::$inputs['ETS_EPT_ENABLE_CLEAR_CACHE_SS']))
                    {
                        unset(self::$inputs['ETS_EPT_ENABLE_CLEAR_CACHE_SS']);
                    }
                    if($render_form && !self::getIDModuleByName('ets_megamenu') && isset(self::$inputs['ETS_MM_CLEAR_CACHE_SPEED']))
                    {
                        unset(self::$inputs['ETS_MM_CLEAR_CACHE_SPEED']);
                    }
                }
                return self::$inputs;
            case '_minization':
                if(!self::$minization_inputs)
                    self::$minization_inputs =array(
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Smarty Cache'),
                            'name' => 'ETS_SPEED_SMARTY_CACHE',
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->l('On')
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->l('Off')
                                )
                            ),
                            'desc' => $this->l('Reduce template rendering time'),
                        ),
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Server Cache'),
                            'name' => 'PS_SMARTY_CACHE',
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->l('On')
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->l('Off')
                                )
                            ),
                            'desc' => $this->l('Reduce database access time'),
                        ),
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Minify HTML'),
                            'name' => 'PS_HTML_THEME_COMPRESSION',
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->l('On')
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->l('Off')
                                )
                            ),
                            'desc' => $this->l('Compress HTML code by removing repeated line breaks, white spaces, tabs and other unnecessary characters in the HTML code'),
                        ),
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Minify Javascript'),
                            'name' => 'PS_JS_THEME_CACHE',
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->l('On')
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->l('Off')
                                )
                            ),
                            'desc' => $this->l('Compress Javascript code by removing repeated line breaks, white spaces, tabs and other unnecessary characters'),
                        ),
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Minify CSS'),
                            'name' => 'PS_CSS_THEME_CACHE',
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->l('On')
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->l('Off')
                                )
                            ),
                            'desc' => $this->l('Compress CSS code by removing repeated line breaks, white spaces, tabs and other unnecessary characters'),
                        ),
                    );
                return self::$minization_inputs;
        }
        return array();
    }
    protected static $config_images;
    public function configFieldImages()
    {
        if(!self::$config_images)
        {
            $lazys =array(
                array(
                    'value' => 'product_list',
                    'label' => $this->l('Product listing','ets_superspeed_defines')
                )
            );
            if($this->isSlide)
            {
                $lazys[] = array(
                    'value' => 'home_slide',
                    'label' => $this->l('Home slider','ets_superspeed_defines')
                );
            }
            if($this->isBanner)
            {
                $lazys[] = array(
                    'value' => 'home_banner',
                    'label' => $this->l('Home banner','ets_superspeed_defines')
                );
            }
            if(Module::isInstalled('themeconfigurator') && Module::isEnabled('themeconfigurator'))
            {
                $lazys[] = array(
                    'value' => 'home_themeconfig',
                    'label' => $this->l('Home theme configurator','ets_superspeed_defines')
                );
            }
            $config_images=array(
                array(
                    'type' => 'switch',
                    'label' => $this->l('Enable lazy load'),
                    'name' => 'ETS_SPEED_ENABLE_LAYZY_LOAD',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No')
                        )
                    ),
                    'form_group_class'=>'form_cache_page image_lazy_load',
                ),
                array(
                    'type' => 'radio',
                    'label' => $this->l('Preloading image'),
                    'name' => 'ETS_SPEED_LOADING_IMAGE_TYPE',
                    'default' => 'type_1',
                    'values' => array(
                        array(
                            'id' => 'type_1',
                            'value' => 'type_1',
                            'label' => $this->l('Type 1'),
                            'html' => self::displayText('','div',array('class'=>'spinner_1')),
                        ),
                        array(
                            'id' => 'type_2',
                            'value' => 'type_2',
                            'label' => $this->l('Type 2'),
                            'html' => self::displayText(
                                self::displayText('','div','').self::displayText('','div','').self::displayText('','div','').self::displayText('','div'),
                                'div',array('class'=>'lds-ring')),
                        ),
                        array(
                            'id' => 'type_3',
                            'value' => 'type_3',
                            'label' => $this->l('Type 3'),
                            'html' => self::displayText(
                                self::displayText('','div').self::displayText('','div').self::displayText('','div','').self::displayText('','div','').self::displayText('','div','').self::displayText('','div','').self::displayText('','div','').self::displayText('','div',''),
                                'div',array('class'=>'lds-roller')),
                        ),
                        array(
                            'id' => 'type_4',
                            'value' => 'type_4',
                            'label' => $this->l('Type 4'),
                            'html' => self::displayText(
                                self::displayText('','div').self::displayText('','div','').self::displayText('','div','').self::displayText('','div',''),
                                'div',array('class'=>'lds-ellipsis')),
                        ),
                        array(
                            'id' => 'type_5',
                            'value' => 'type_5',
                            'label' => $this->l('Type 5'),
                            'html' => self::displayText(
                                self::displayText('','div','').self::displayText('','div','').self::displayText('','div','').self::displayText('','div','').self::displayText('','div','').self::displayText('','div','').self::displayText('','div','').self::displayText('','div','').self::displayText('','div','').self::displayText('','div','').self::displayText('','div','').self::displayText('','div',''),
                                'div',array('class'=>'lds-spinner')),
                        ),
                    ),
                    'form_group_class'=>'form_cache_page image_lazy_load type',
                ),
                array(
                    'type' => 'checkbox',
                    'label' => $this ->l('Enable Lazy Load for'),
                    'name' => 'ETS_SPEED_LAZY_FOR',
                    'values' => array(
                        'query'=> $lazys,
                        'id' => 'value',
                        'name' => 'label',
                    ),
                    'form_group_class'=>'form_cache_page image_lazy_load',
                )
            );
            if($this->getImageTypes('products',false,false))
                $config_images[]=array(
                    'type'=>'checkbox',
                    'label' => $this->l('Product images'),
                    'name'=>'ETS_SPEED_OPTIMIZE_IMAGE_PRODCUT_TYPE',
                    'values' => array(
                        'query' => $this->getImageTypes('products'),
                        'id' => 'value',
                        'name' => 'label'
                    ),
                    'default'=> $this->getImageTypes('products',true),
                    'isGlobal' => true,
                    'image_old'=>'product',
                    'form_group_class'=>'form_cache_page image_old hasinput_newstyle',
                );
            if($this->getImageTypes('categories',false,false))
                $config_images[]= array(
                    'type'=>'checkbox',
                    'label' => $this->l('Product category images'),
                    'name'=>'ETS_SPEED_OPTIMIZE_IMAGE_CATEGORY_TYPE',
                    'values' => array(
                        'query' => $this->getImageTypes('categories'),
                        'id' => 'value',
                        'name' => 'label'
                    ),
                    'isGlobal' => true,
                    'default'=> $this->getImageTypes('categories',true),
                    'image_old'=>'category',
                    'form_group_class'=>'form_cache_page image_old hasinput_newstyle',
                );
            if($this->getImageTypes('suppliers',false,false))
                $config_images[]= array(
                    'type'=>'checkbox',
                    'label' => $this->l('Supplier images'),
                    'name'=>'ETS_SPEED_OPTIMIZE_IMAGE_SUPPLIER_TYPE',
                    'isGlobal' => true,
                    'values' => array(
                        'query' => $this->getImageTypes('suppliers'),
                        'id' => 'value',
                        'name' => 'label'
                    ),
                    'default'=> $this->getImageTypes('suppliers',true),
                    'image_old'=>'supplier',
                    'form_group_class'=>'form_cache_page image_old hasinput_newstyle',
                );
            if($this->getImageTypes('manufacturers',false,false))
                $config_images[]=array(
                    'type'=>'checkbox',
                    'label' => $this->l('Manufacturer images'),
                    'name'=>'ETS_SPEED_OPTIMIZE_IMAGE_MANUFACTURER_TYPE',
                    'values' => array(
                        'query' => $this->getImageTypes('manufacturers'),
                        'id' => 'value',
                        'name' => 'label'
                    ),
                    'isGlobal' => true,
                    'default'=> $this->getImageTypes('manufacturers',true),
                    'image_old'=>'manufacturer',
                    'form_group_class'=>'form_cache_page image_old manufacturer hasinput_newstyle',
                );

            if($this->isblog)
            {
                if($this->getImageTypes('blog_post',false,false))
                    $config_images[]=array(
                        'type'=>'checkbox',
                        'label' => $this->l('Blog post images'),
                        'name'=>'ETS_SPEED_OPTIMIZE_IMAGE_BLOG_POST_TYPE',
                        'values' => array(
                            'query' => $this->getImageTypes('blog_post'),
                            'id' => 'value',
                            'name' => 'label'
                        ),
                        'isGlobal' => true,
                        'default'=> $this->getImageTypes('blog_post',true),
                        'image_old'=>'blog_post',
                        'form_group_class'=>'form_cache_page image_old blog_post hasinput_newstyle',
                    );
                if($this->getImageTypes('blog_category',false,false))
                    $config_images[]=array(
                        'type'=>'checkbox',
                        'label' => $this->l('Blog category images'),
                        'name'=>'ETS_SPEED_OPTIMIZE_IMAGE_BLOG_CATEGORY_TYPE',
                        'values' => array(
                            'query' => $this->getImageTypes('blog_category'),
                            'id' => 'value',
                            'name' => 'label'
                        ),
                        'isGlobal' => true,
                        'default'=> $this->getImageTypes('blog_category',true),
                        'image_old'=>'blog_category',
                        'form_group_class'=>'form_cache_page image_old blog_category hasinput_newstyle',
                    );
                if($this->getImageTypes('blog_gallery',false,false))
                    $config_images[]=array(
                        'type'=>'checkbox',
                        'label' => $this->l('Blog gallery & slider images'),
                        'name'=>'ETS_SPEED_OPTIMIZE_IMAGE_BLOG_GALLERY_TYPE',
                        'values' => array(
                            'query' => $this->getImageTypes('blog_gallery'),
                            'id' => 'value',
                            'name' => 'label'
                        ),
                        'isGlobal' => true,
                        'default'=> $this->getImageTypes('blog_gallery',true),
                        'image_old'=>'blog_gallery',
                        'form_group_class'=>'form_cache_page image_old blog_gallery hasinput_newstyle',
                    );
                if($this->getImageTypes('blog_slide',false,false))
                    $config_images[]=array(
                        'type'=>'checkbox',
                        'label' => $this->l('Slider images'),
                        'name'=>'ETS_SPEED_OPTIMIZE_IMAGE_BLOG_SLIDE_TYPE',
                        'values' => array(
                            'query' => $this->getImageTypes('blog_slide'),
                            'id' => 'value',
                            'name' => 'label'
                        ),
                        'isGlobal' => true,
                        'default'=> $this->getImageTypes('blog_slide',true),
                        'image_old'=>'blog_slide',
                        'form_group_class'=>'form_cache_page image_old blog_slide hasinput_newstyle',
                    );
            }
            if($this->isSlide)
            {
                if($this->getImageTypes('home_slide',false,false))
                    $config_images[]=array(
                        'type'=>'checkbox',
                        'label' => $this->l('Home slider images'),
                        'name'=>'ETS_SPEED_OPTIMIZE_IMAGE_HOME_SLIDE_TYPE',
                        'values' => array(
                            'query' => $this->getImageTypes('home_slide'),
                            'id' => 'value',
                            'name' => 'label'
                        ),
                        'isGlobal' => true,
                        'default'=> $this->getImageTypes('home_slide',true),
                        'image_old'=>'home_slide',
                        'form_group_class'=>'form_cache_page image_old home_slide',
                    );
            }
            $config_images[] = array(
                'type'=>'checkbox',
                'label' => $this->l('Others images'),
                'name'=>'ETS_SPEED_OPTIMIZE_IMAGE_OTHERS_TYPE',
                'values' => array(
                    'query' => $this->getImageTypes('others'),
                    'id' => 'value',
                    'name' => 'label'
                ),
                'default'=> $this->getImageTypes('others',true),
                'image_old'=>'others',
                'form_group_class'=>'form_cache_page image_old others hasinput_newstyle',
            );
            $whitelist = array(
                '127.0.0.1',
                '::1'
            );
            $optimize_type = array(
                'type'=>'select',
                'label'=>$this->l('Image optimization method'),
                'name'=>'ETS_SPEED_OPTIMIZE_SCRIPT',
                'options' => array(
                    'query' => array(
                        array(
                            'id_option' =>'php',
                            'name' => $this->l('PHP image optimization script')
                        ),
                        array(
                            'id_option' =>'resmush',
                            'name' => $this->l('Resmush - Free image optimization web service API')
                        ),
                        array(
                            'id_option' =>'tynypng',
                            'name' => $this->l('TinyPNG - Premium image optimization web service API (500 images for free per month)')
                        ),
                        array(
                            'id_option' =>'google',
                            'name' => $this->l('Google Webp image optimizer')
                        ),
                    ),
                    'id' => 'id_option',
                    'name' => 'name'
                ),
                'isGlobal' => true,
                'form_group_class'=>'form_cache_page image_old script',
                'default'=>'php',
            );
            if(in_array(Tools::getRemoteAddr(), $whitelist))
                unset($optimize_type['options']['query'][1]);
            $config_images[]= $optimize_type;
            $config_images[]= array(
                'type'=>'range',
                'label'=>$this->l('Image quality'),
                'name'=>'ETS_SPEED_QUALITY_OPTIMIZE',
                'min'=>'1',
                'max'=>'100',
                'unit' => '%',
                'units'=>'%',
                'isGlobal' => true,
                'form_group_class'=>'form_cache_page use_default form_group_range_small quality image_old',
                'desc' => $this->l('The higher image quality, the longer page loading time, 50% is recommended value. Setup image quality up to 100% will restore original images.'),
                'default'=>50,
            );
            $config_images[] = array(
                'type' => 'switch',
                'label' => $this->l('Change file extension to .webp for product images when converting?'),
                'name' => 'ETS_SPEED_ENABLE_WEBP_FORMAT',
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Yes')
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('No')
                    )
                ),
                'isGlobal' => true,
                'form_group_class'=>'form_cache_page image_old webp',
                'default' => 0,
            );
            $config_images[] = array(
                'type' => 'checkbox',
                'label' => '',
                'name' => 'ETS_SPEED_UPDATE_QUALITY',
                'values' => array(
                    'query' => array(
                        array(
                            'value' => 1,
                            'label' => $this->l('Avoid re-optimizing images that have already been optimized using a different image quality or optimization method.'),
                        )
                    ),
                    'id' => 'value',
                    'name' => 'label',
                ),
                'isGlobal' => true,
                'form_group_class'=>'form_cache_page image_old update_quality',
                'default' => 1,
                'desc' => $this->l('Re-optimizing such images may lead to undesired outcomes. Avoid re-optimizing to preserve the original optimization and prevent potential conflicts.'),
            );
            self::$config_images = $config_images;
        }
        return self::$config_images;
    }
    public static function getBaseLink()
    {
        $context = Context::getContext();
        return (Configuration::get('PS_SSL_ENABLED_EVERYWHERE')?'https://':'http://').$context->shop->domain.$context->shop->getBaseURI();
    }
    public function getImageTypes($type='',$string=false,$get_total=false)
    {
        $cache_key = 'Ets_superspeed_defines::getImageTypes_'.$type.'_'.($string ? $string:'').'_'.($get_total ? "1":'0');
        if(!Cache::isStored($cache_key))
        {
            $is_installed = Ets_superspeed::isInstalled('ets_superspeed');
            if($is_installed || version_compare(_PS_VERSION_, '1.7', '<'))
            {
                if(in_array($type,array('products','manufacturers','categories','suppliers')))
                {
                    $sql = 'SELECT name as value,name as label FROM `'._DB_PREFIX_.'image_type` '.($type ? ' WHERE '.pSQL($type).'=1' :'' );
                    $image_types = Db::getInstance()->executeS($sql);
                }
                elseif($type=='home_slide' && $this->isSlide)
                {
                    $image_types = array(
                        array(
                            'value'=> 'image',
                            'label' =>''
                        )
                    );
                }
                elseif($type=='others')
                {
                    $image_types = array(
                        array(
                            'value'=>'logo',
                            'label' => $this->l('Logo image')
                        ),
                        array(
                            'value'=>'banner',
                            'label' => $this->l('Banner image')
                        )
                    );
                    if(version_compare(_PS_VERSION_, '1.7', '<'))
                    {
                        $image_types[]=array(
                            'value'=>'themeconfig',
                            'label' => $this->l('Theme configurator image')
                        );
                    }
                    if($this->isSlide)
                    {
                        $image_types[] = array(
                            'value'=>'home_slide',
                            'label' => $this->l('Home slider images')
                        );
                    }
                }
                elseif(in_array($type,array('blog_post','blog_category','blog_gallery','blog_slide')) &&  $this->isblog)
                {
                    $image_types = array(
                        array(
                            'value'=> 'image',
                            'label' => $this->l('Main image')
                        ),
                        array(
                            'value'=> 'thumb',
                            'label' => $this->l('Thumb image')
                        )
                    );
                    if($type=='blog_slide')
                    {
                        $image_types= array(
                            array(
                                'value'=> 'image',
                                'label' =>''
                            ),
                        );
                    }
                    if($type=='blog_gallery')
                    {
                        $image_types=array(
                            array(
                                'value'=> 'image',
                                'label' => $this->l('Main gallery image')
                            ),
                            array(
                                'value'=> 'thumb',
                                'label' => $this->l('Thumb gallery image')
                            ),
                            array(
                                'value'=> 'blog_slide',
                                'label' => $this->l('Slider images')
                            )
                        );
                    }
                }
                else
                    $image_types=array();

                $total=0;
                if($string)
                {
                    $images='';
                    foreach($image_types as $image_type)
                    {
                        $images .=','.$image_type['value'];
                    }
                    return trim($images,',');
                }
                else
                {
                    if($image_types)
                    {
                        foreach($image_types as &$image)
                        {
                            $total_image=0;
                            $total_image_optimized = 0;
                            switch($type){
                                case 'products':
                                    if($is_installed)
                                        $total_image_optimized = Ets_superspeed_defines::getTotalImage('product',false,true,true,false,$image['value']);
                                    else
                                        $total_image_optimized =0;
                                    $image_product = Ets_superspeed_defines::getTotalImage('product',false,false,false,false,$image['value']);
                                    $total_image =  $image_product- $total_image_optimized;
                                    $total +=$image_product;
                                    break;
                                case 'manufacturers':
                                    if($is_installed)
                                        $total_image_optimized = Ets_superspeed_defines::getTotalImage('manufacturer',false,true,true,false,$image['value']);
                                    else
                                        $total_image_optimized = 0;
                                    $image_manu = Ets_superspeed_defines::getTotalImage('manufacturer',false,false,false,false,$image['value']) ;
                                    $total_image = $image_manu - $total_image_optimized;
                                    $total +=$image_manu;
                                    break;
                                case 'categories':
                                    if($is_installed)
                                        $total_image_optimized = Ets_superspeed_defines::getTotalImage('category',false,true,true,false,$image['value']);
                                    else
                                        $total_image_optimized =0;
                                    $image_cate = Ets_superspeed_defines::getTotalImage('category',false,false,false,false,$image['value']);
                                    $total_image =  $image_cate - $total_image_optimized;
                                    $total +=$image_cate;
                                    break;
                                case 'suppliers':
                                    if($is_installed)
                                        $total_image_optimized = Ets_superspeed_defines::getTotalImage('supplier',false,true,true,false,$image['value']);
                                    else
                                        $total_image_optimized =0;
                                    $image_supplier = Ets_superspeed_defines::getTotalImage('supplier',false,false,false,false,$image['value']);
                                    $total_image = $image_supplier - $total_image_optimized;
                                    $total += $image_supplier;
                                    break;
                                case 'blog_post' :
                                    if($is_installed)
                                        $total_image_optimized = Ets_superspeed_defines::getTotalImage('blog_post',false,true,true,false,$image['value']);
                                    else
                                        $total_image_optimized =0;
                                    $image_post = Ets_superspeed_defines::getTotalImage('blog_post',false,false,false,false,$image['value']);
                                    $total_image = $image_post - $total_image_optimized;
                                    $total += $image_post;
                                    break;
                                case 'blog_category' :
                                    if($is_installed)
                                        $total_image_optimized = Ets_superspeed_defines::getTotalImage('blog_category',false,true,true,false,$image['value']);
                                    else
                                        $total_image_optimized =0;
                                    $image_blog_category = Ets_superspeed_defines::getTotalImage('blog_category',false,false,false,false,$image['value']);
                                    $total_image = $image_blog_category - $total_image_optimized;
                                    $total += $image_blog_category;
                                    break;
                                case 'blog_gallery' :
                                    if($image['value']=='blog_slide')
                                    {
                                        if($is_installed)
                                            $total_image_optimized = Ets_superspeed_defines::getTotalImage('blog_slide',false,true,true,false,'image');
                                        else
                                            $total_image_optimized = 0;
                                        $image_blog_slide = Ets_superspeed_defines::getTotalImage('blog_slide',false,false,false,false,'image');
                                        $total_image = $image_blog_slide - $total_image_optimized;
                                        $total += $image_blog_slide;
                                    }
                                    else
                                    {
                                        if($is_installed)
                                            $total_image_optimized = Ets_superspeed_defines::getTotalImage('blog_gallery',false,true,true,false,$image['value']);
                                        else
                                            $total_image_optimized =0;
                                        $image_blog_gallery = Ets_superspeed_defines::getTotalImage('blog_gallery',false,false,false,false,$image['value']);
                                        $total_image = $image_blog_gallery - $total_image_optimized;
                                        $total += $image_blog_gallery;
                                    }
                                    break;
                                case 'blog_slide' :
                                    if($is_installed)
                                        $total_image_optimized = Ets_superspeed_defines::getTotalImage('blog_slide',false,true,true,false,$image['value']);
                                    else
                                        $total_image_optimized =0;
                                    $image_blog_slide = Ets_superspeed_defines::getTotalImage('blog_slide',false,false,false,false,$image['value']);
                                    $total_image = $image_blog_slide - $total_image_optimized;
                                    $total += $image_blog_slide;
                                    break;
                                case 'home_slide' :
                                    if($is_installed)
                                        $total_image_optimized = Ets_superspeed_defines::getTotalImage('home_slide',false,true,true,false,$image['value']);
                                    else
                                        $total_image_optimized =0;
                                    $image_home_slide = Ets_superspeed_defines::getTotalImage('home_slide',false,false,false,false,$image['value']);
                                    $total_image = $image_home_slide - $total_image_optimized;
                                    $total += $image_home_slide;
                                    break;
                                case 'others' :
                                    if($image['value']=='home_slide')
                                    {
                                        if($is_installed)
                                            $total_image_optimized = Ets_superspeed_defines::getTotalImage('home_slide',false,true,true,false,$image['value']);
                                        else
                                            $total_image_optimized =0;
                                        $image_home_slide = Ets_superspeed_defines::getTotalImage('home_slide',false,false,false,false,$image['value']);
                                        $total_image = $image_home_slide - $total_image_optimized;
                                        $total += $image_home_slide;
                                    }
                                    else
                                    {
                                        if($is_installed)
                                            $total_image_optimized = Ets_superspeed_defines::getTotalImage('others',false,true,true,false,$image['value']);
                                        else
                                            $total_image_optimized =0;
                                        $image_others = Ets_superspeed_defines::getTotalImage('others',false,false,false,false,$image['value']);
                                        $total_image = $image_others - $total_image_optimized;
                                        $total += $image_others;
                                    }

                                    break;
                            }
                            $image['total_image'] = $total_image;
                            $image['total_image_optimized'] = $total_image_optimized;
                        }
                    }
                }
                $result= $get_total ? $total : $image_types;
            }
            else
                $result = false;
            Cache::store($cache_key,$result);
        }
        return Cache::retrieve($cache_key);
        
    }
    public static function getTotalImage_2_1_9($type = 'product', $all_type = false, $optimizaed = false, $check_quality = false, $noconfig = false, $type_image = '')
    {
        $total = 0;
        $quality = (int)Tools::getValue('ETS_SPEED_QUALITY_OPTIMIZE', Configuration::getGlobalValue('ETS_SPEED_QUALITY_OPTIMIZE'));
        $optimize_script = Tools::getValue('ETS_SPEED_OPTIMIZE_SCRIPT', Configuration::getGlobalValue('ETS_SPEED_OPTIMIZE_SCRIPT'));
        $update_quantity = Tools::isSubmit('changeSubmitImageOptimize')|| Tools::isSubmit('btnSubmitImageOptimize') ? Tools::getValue('ETS_SPEED_UPDATE_QUALITY') : Configuration::get('ETS_SPEED_UPDATE_QUALITY');
        if ($update_quantity && ((is_array($update_quantity) && Ets_superspeed::validateArray($update_quantity)) || Validate::isInt($update_quantity)) && $quality != 100) {
            $check_quality = false;
            $check_optimize_script = false;
        } else {
            $check_quality = true;
            if ($quality == 100)
                $check_optimize_script = false;
            else
                $check_optimize_script = true;
        }
        switch ($type) {
            case 'blog_post':
                if (Module::isEnabled('ybc_blog')) {
                    if (Tools::isSubmit('changeSubmitImageOptimize'))
                        $blog_post_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_POST_TYPE') ? Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_POST_TYPE') : array();
                    else
                        $blog_post_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_POST_TYPE', Configuration::getGlobalValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_POST_TYPE') ? explode(',', Configuration::getGlobalValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_POST_TYPE')) : array());
                    $total = 0;
                    if ($all_type && Ets_superspeed::validateArray($blog_post_type)) {
                        if (in_array('image', $blog_post_type) || $noconfig)
                            $total += Db::getInstance()->getValue('SELECT COUNT(DISTINCT id_post) FROM `' . _DB_PREFIX_ . 'ybc_blog_post` WHERE image!=""');
                        if (in_array('thumb', $blog_post_type) || $noconfig)
                            $total += Db::getInstance()->getValue('SELECT COUNT(DISTINCT id_post) FROM `' . _DB_PREFIX_ . 'ybc_blog_post` WHERE thumb!=""');
                    } elseif ($type_image && in_array($type_image, array('image', 'thumb')))
                        $total += Db::getInstance()->getValue('SELECT COUNT(DISTINCT id_post) FROM `' . _DB_PREFIX_ . 'ybc_blog_post` WHERE ' . bqSQL($type_image) . '!=""');
                    if ($optimizaed) {
                        $total_optimized = Db::getInstance()->getValue('SELECT COUNT(sbpi.id_post) FROM `' . _DB_PREFIX_ . 'ets_superspeed_blog_post_image` sbpi
                        INNER JOIN `' . _DB_PREFIX_ . 'ybc_blog_post` bp ON (sbpi.id_post = bp.id_post)
                        WHERE 1' . ($all_type && $blog_post_type && Ets_superspeed::validateArray($blog_post_type) && !$noconfig ? ' AND  type_image IN ("' . implode('","', array_map('pSQL', $blog_post_type)) . '")' : '') . ($type_image ? ' AND type_image="' . pSQL($type_image) . '"' : '') . ($check_quality ? ' AND quality = "' . (int)$quality . '"' : ' AND quality!=100') . ($check_optimize_script ? ' AND optimize_type="' . pSQL($optimize_script) . '"' : ''));
                        return $total_optimized > $total ? $total : $total_optimized;
                    }
                    return $total;
                } else
                    return 0;
            case 'blog_category':
                if (Module::isEnabled('ybc_blog')) {
                    if (Tools::isSubmit('changeSubmitImageOptimize'))
                        $blog_category_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_CATEGORY_TYPE') ? Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_CATEGORY_TYPE') : array();
                    else
                        $blog_category_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_CATEGORY_TYPE', Configuration::getGlobalValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_CATEGORY_TYPE') ? explode(',', Configuration::getGlobalValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_CATEGORY_TYPE')) : array());
                    $total = 0;
                    if ($all_type && Ets_superspeed::validateArray($blog_category_type)) {
                        if (in_array('image', $blog_category_type) || $noconfig)
                            $total += Db::getInstance()->getValue('SELECT COUNT(DISTINCT id_category) FROM `' . _DB_PREFIX_ . 'ybc_blog_category` WHERE image!=""');
                        if (in_array('thumb', $blog_category_type) || $noconfig)
                            $total += Db::getInstance()->getValue('SELECT COUNT(DISTINCT id_category) FROM `' . _DB_PREFIX_ . 'ybc_blog_category` WHERE thumb!=""');
                    } elseif ($type_image && in_array($type_image, array('image', 'thumb')))
                        $total += Db::getInstance()->getValue('SELECT COUNT(DISTINCT id_category) FROM `' . _DB_PREFIX_ . 'ybc_blog_category` WHERE ' . bqSQL($type_image) . '!=""');
                    if ($optimizaed && Validate::isCleanHtml($optimize_script)) {
                        $total_optimized = Db::getInstance()->getValue('SELECT COUNT(sbci.id_category) FROM `' . _DB_PREFIX_ . 'ets_superspeed_blog_category_image` sbci
                        INNER JOIN `' . _DB_PREFIX_ . 'ybc_blog_category` bc ON (bc.id_category = sbci.id_category)
                        WHERE 1 ' . ($all_type && $blog_category_type && Ets_superspeed::validateArray($blog_category_type) && !$noconfig ? ' AND  type_image IN ("' . implode('","', array_map('pSQL', $blog_category_type)) . '")' : '') . ($type_image ? ' AND type_image="' . pSQL($type_image) . '"' : '') . ($check_quality ? ' AND quality = "' . (int)$quality . '"' : ' AND quality!=100') . ($check_optimize_script ? ' AND optimize_type="' . pSQL($optimize_script) . '"' : ''));
                        return $total_optimized > $total ? $total : $total_optimized;
                    }
                    return $total;
                } else
                    return 0;
            case 'blog_gallery':
                if (Module::isEnabled('ybc_blog')) {
                    if (Tools::isSubmit('changeSubmitImageOptimize'))
                        $blog_gallery_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_GALLERY_TYPE') ? Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_GALLERY_TYPE') : array();
                    else
                        $blog_gallery_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_GALLERY_TYPE', Configuration::getGlobalValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_GALLERY_TYPE') ? explode(',', Configuration::getGlobalValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_GALLERY_TYPE')) : array());
                    $total = 0;
                    if ($all_type && Ets_superspeed::validateArray($blog_gallery_type)) {
                        if (in_array('image', $blog_gallery_type) || $noconfig)
                            $total += Db::getInstance()->getValue('SELECT COUNT(DISTINCT id_gallery) FROM `' . _DB_PREFIX_ . 'ybc_blog_gallery` WHERE image!=""');
                        if (in_array('thumb', $blog_gallery_type) || $noconfig)
                            $total += Db::getInstance()->getValue('SELECT COUNT(DISTINCT id_gallery) FROM `' . _DB_PREFIX_ . 'ybc_blog_gallery` WHERE thumb!=""');
                    } elseif ($type_image && in_array($type_image, array('image', 'thumb')))
                        $total += Db::getInstance()->getValue('SELECT COUNT(DISTINCT id_gallery) FROM `' . _DB_PREFIX_ . 'ybc_blog_gallery` WHERE ' . bqSQL($type_image) . '!=""');
                    if ($optimizaed) {
                        $total_optimized = Db::getInstance()->getValue('SELECT COUNT(sbgi.id_gallery) FROM `' . _DB_PREFIX_ . 'ets_superspeed_blog_gallery_image` sbgi
                        INNER JOIN `' . _DB_PREFIX_ . 'ybc_blog_gallery` bg ON (bg.id_gallery = sbgi.id_gallery)
                        WHERE 1' . ($all_type && $blog_gallery_type && Ets_superspeed::validateArray($blog_gallery_type) && !$noconfig ? ' AND  type_image IN ("' . implode('","', array_map('pSQL', $blog_gallery_type)) . '")' : '') . ($type_image ? ' AND type_image="' . pSQL($type_image) . '"' : '') . ($check_quality ? ' AND quality = "' . (int)$quality . '"' : ' AND quality!=100') . ($check_optimize_script ? ' AND optimize_type="' . pSQL($optimize_script) . '"' : ''));
                        return $total_optimized > $total ? $total : $total_optimized;
                    }
                    return $total;
                } else
                    return 0;
            case 'blog_slide':
                if (Module::isEnabled('ybc_blog')) {
                    if (Tools::isSubmit('changeSubmitImageOptimize'))
                        $blog_slide_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_SLIDE_TYPE') ? Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_SLIDE_TYPE') : array();
                    else
                        $blog_slide_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_SLIDE_TYPE', Configuration::getGlobalValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_SLIDE_TYPE') ? explode(',', Configuration::getGlobalValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_SLIDE_TYPE')) : array());
                    $total = 0;
                    if ($all_type && Ets_superspeed::validateArray($blog_slide_type)) {
                        if (in_array('image', $blog_slide_type) || $noconfig)
                            $total += Db::getInstance()->getValue('SELECT COUNT(DISTINCT id_slide) FROM `' . _DB_PREFIX_ . 'ybc_blog_slide` WHERE image!=""');
                    } elseif ($type_image && in_array($type_image, array('image')))
                        $total += Db::getInstance()->getValue('SELECT COUNT(DISTINCT id_slide) FROM `' . _DB_PREFIX_ . 'ybc_blog_slide` WHERE ' . bqSQL($type_image) . '!=""');
                    if ($optimizaed) {
                        $total_optimized = Db::getInstance()->getValue('SELECT COUNT(sbsi.id_slide) FROM `' . _DB_PREFIX_ . 'ets_superspeed_blog_slide_image` sbsi
                        INNER JOIN `' . _DB_PREFIX_ . 'ybc_blog_slide` bs ON (bs.id_slide = sbsi.id_slide)
                        WHERE 1' . ($all_type && $blog_slide_type && Ets_superspeed::validateArray($blog_slide_type) && !$noconfig ? ' AND  type_image IN ("' . implode('","', array_map('pSQL', $blog_slide_type)) . '")' : '') . ($type_image ? ' AND type_image="' . pSQL($type_image) . '"' : '') . ($check_quality ? ' AND quality = "' . (int)$quality . '"' : ' AND quality!=100') . ($check_optimize_script ? ' AND optimize_type="' . pSQL($optimize_script) . '"' : ''));
                        return $total_optimized > $total ? $total : $total_optimized;
                    }
                    return $total;
                } else
                    return 0;
        }
        return $total;
    }
    protected static $_total_image = array();
    public static function getTotalImage($type = 'product', $all_type = false, $optimizaed = false, $check_quality = false, $noconfig = false, $type_image = '')
    {
        $key = $type.'-'.(int)$all_type.'-'.(int)$optimizaed.'-'.(int)$check_quality.'-'.(int)$noconfig.'-'.$type_image;
        if (isset(self::$_total_image[$key]) && self::$_total_image[$key] !== null) {
            return self::$_total_image[$key];
        }
        if(!Module::isInstalled('ets_superspeed'))
            return 1;
        if (in_array($type, array('blog_post','blog_category','blog_gallery','blog_slide'))) {
            $ybc_blog = Module::getInstanceByName('ybc_blog');
            if (version_compare($ybc_blog->version, '3.2.0', '<'))
                return self::getTotalImage_2_1_9($type, $all_type, $optimizaed, $check_quality, $noconfig, $type_image);
        }
        $total = 0;
        $count_type = 1;
        $quality = (int)Tools::getValue('ETS_SPEED_QUALITY_OPTIMIZE', Configuration::getGlobalValue('ETS_SPEED_QUALITY_OPTIMIZE'));
        $optimize_script = Tools::getValue('ETS_SPEED_OPTIMIZE_SCRIPT', Configuration::getGlobalValue('ETS_SPEED_OPTIMIZE_SCRIPT'));
        $update_quantity = Tools::isSubmit('changeSubmitImageOptimize')|| Tools::isSubmit('btnSubmitImageOptimize') ? Tools::getValue('ETS_SPEED_UPDATE_QUALITY') : Configuration::get('ETS_SPEED_UPDATE_QUALITY');
        
        if ($update_quantity && ((is_array($update_quantity) && Ets_superspeed::validateArray($update_quantity)) || Validate::isInt($update_quantity)) && $quality != 100) {
            $check_quality = false;
            $check_optimize_script = false;
        } else {
            $check_quality = true;
            if ($quality == 100)
                $check_optimize_script = false;
            else
                $check_optimize_script = true;
        }
        switch ($type) {
            case 'category': 
                if (Tools::isSubmit('changeSubmitImageOptimize'))
                    $category_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_CATEGORY_TYPE') ? Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_CATEGORY_TYPE') : array();
                else
                    $category_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_CATEGORY_TYPE', Configuration::getGlobalValue('ETS_SPEED_OPTIMIZE_IMAGE_CATEGORY_TYPE') ? explode(',', Configuration::getGlobalValue('ETS_SPEED_OPTIMIZE_IMAGE_CATEGORY_TYPE')) : array());
                if ($all_type && Ets_superspeed::validateArray($category_type)) {
                    if (($category_type && in_array('0', $category_type)) || $noconfig)
                    {
                        $count_type = count(Ets_superspeed_defines::getInstance()->getImageTypes('categories'));
                    }
                    else
                        $count_type = $category_type ? count($category_type) : 0;
                }
                $totalImageCategories = Ets_superspeed_compressor_image::getTotalImageCategories();
                $total = $totalImageCategories * $count_type;
                if ($optimizaed && Validate::isCleanHtml($optimize_script)) {
                    $total_optimized = Db::getInstance()->getValue('SELECT COUNT(sci.id_category) FROM `' . _DB_PREFIX_ . 'ets_superspeed_category_image` sci 
                    INNER JOIN `' . _DB_PREFIX_ . 'category` c ON (c.id_category = sci.id_category)
                    WHERE 1 ' . ($all_type && $category_type && !$noconfig ? ' AND  type_image IN ("' . implode('","', array_map('pSQL', $category_type)) . '")' : '') . ($type_image ? ' AND type_image="' . pSQL($type_image) . '"' : '') . ($check_quality ? ' AND quality = "' . (int)$quality . '"' : ' AND quality!=100') . ($check_optimize_script ? ' AND optimize_type="' . pSQL($optimize_script) . '"' : ''));
                    $total = $count_type ? ($total_optimized > $total ? $total : $total_optimized) : 0;
                    self::$_total_image[$key] = $total;
                    return $total;
                }
                self::$_total_image[$key] = $total;
                return $total;
            case 'manufacturer':
                if (Tools::isSubmit('changeSubmitImageOptimize'))
                    $manufacturer_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_MANUFACTURER_TYPE') ? Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_MANUFACTURER_TYPE') : array();
                else
                    $manufacturer_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_MANUFACTURER_TYPE', Configuration::getGlobalValue('ETS_SPEED_OPTIMIZE_IMAGE_MANUFACTURER_TYPE') ? explode(',', Configuration::getGlobalValue('ETS_SPEED_OPTIMIZE_IMAGE_MANUFACTURER_TYPE')) : array());
                if ($all_type && Ets_superspeed::validateArray($manufacturer_type)) {
                    if (($manufacturer_type && in_array('0', $manufacturer_type)) || $noconfig)
                        $count_type = count(Ets_superspeed_defines::getInstance()->getImageTypes('manufacturers'));
                    else
                        $count_type = $manufacturer_type ? count($manufacturer_type) : 0;
                }
                $totalImageManufacturers = Ets_superspeed_compressor_image::getTotalImagemanufacturers();
                $total = $count_type * $totalImageManufacturers;
                if ($optimizaed) {
                    $total_optimized = Db::getInstance()->getValue('SELECT COUNT(smi.id_manufacturer) FROM `' . _DB_PREFIX_ . 'ets_superspeed_manufacturer_image` smi
                    INNER JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON (smi.id_manufacturer = m.id_manufacturer)
                    WHERE 1 ' . ($all_type && $manufacturer_type && !$noconfig ? ' AND  type_image IN ("' . implode('","', array_map('pSQL', $manufacturer_type)) . '")' : '') . ($type_image ? ' AND type_image="' . pSQL($type_image) . '"' : '') . ($check_quality ? ' AND quality = "' . (int)$quality . '"' : ' AND quality!=100') . ($check_optimize_script ? ' AND optimize_type="' . pSQL($optimize_script) . '"' : ''));
                    $total = $count_type ? ($total_optimized > $total ? $total : $total_optimized) : 0;
                    self::$_total_image[$key] = $total;
                    return $total;
                }
                self::$_total_image[$key] = $total;
                return $total;
            case 'supplier':
                if (Tools::isSubmit('changeSubmitImageOptimize'))
                    $supplier_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_SUPPLIER_TYPE') ? Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_SUPPLIER_TYPE') : array();
                else
                    $supplier_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_SUPPLIER_TYPE', Configuration::getGlobalValue('ETS_SPEED_OPTIMIZE_IMAGE_SUPPLIER_TYPE') ? explode(',', Configuration::getGlobalValue('ETS_SPEED_OPTIMIZE_IMAGE_SUPPLIER_TYPE')) : array());
                if ($all_type && Ets_superspeed::validateArray($supplier_type)) {
                    if (($supplier_type && in_array('0', $supplier_type)) || $noconfig)
                        $count_type = count(Ets_superspeed_defines::getInstance()->getImageTypes('suppliers'));
                    else
                        $count_type = $supplier_type ? count($supplier_type) : 0;
                }
                $totalImageSuppliers = Ets_superspeed_compressor_image::getTotalImagesuppliers();
                $total = $totalImageSuppliers * $count_type;
                if ($optimizaed) {
                    $total_optimized = Db::getInstance()->getValue('SELECT COUNT(ssi.id_supplier) FROM `' . _DB_PREFIX_ . 'ets_superspeed_supplier_image` ssi
                    INNER JOIN `' . _DB_PREFIX_ . 'supplier` s ON (ssi.id_supplier = s.id_supplier)
                    WHERE 1 ' . ($all_type && $supplier_type && !$noconfig ? ' AND  type_image IN ("' . implode('","', array_map('pSQL', $supplier_type)) . '")' : '') . ($type_image ? ' AND type_image="' . pSQL($type_image) . '"' : '') . ($check_quality ? ' AND quality = "' . (int)$quality . '"' : ' AND quality!=100') . ($check_optimize_script ? ' AND optimize_type="' . pSQL($optimize_script) . '"' : ''));
                    $total = $count_type ? ($total_optimized > $total ? $total : $total_optimized) : 0;
                    self::$_total_image[$key] = $total;
                    return $total;
                }
                self::$_total_image[$key] = $total;
                return $total;
            case 'product':
                if (Tools::isSubmit('changeSubmitImageOptimize'))
                    $product_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_PRODCUT_TYPE') ? Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_PRODCUT_TYPE') : array();
                else
                    $product_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_PRODCUT_TYPE', Configuration::getGlobalValue('ETS_SPEED_OPTIMIZE_IMAGE_PRODCUT_TYPE') ? explode(',', Configuration::getGlobalValue('ETS_SPEED_OPTIMIZE_IMAGE_PRODCUT_TYPE')) : array());
                if ($all_type && Ets_superspeed::validateArray($product_type)) {
                    if (($product_type && in_array('0', $product_type)) || $noconfig)
                        $count_type = count(Ets_superspeed_defines::getInstance()->getImageTypes('products'));
                    else
                        $count_type = $product_type ? count($product_type) : 0;

                }
                if ($optimizaed) {
                    $total = Db::getInstance()->getValue('
                        SELECT COUNT(pm.id_image) FROM `' . _DB_PREFIX_ . 'ets_superspeed_product_image` pm
                        INNER JOIN `' . _DB_PREFIX_ . 'image` m ON (pm.id_image= m.id_image)
                        WHERE 1' . ($all_type && $product_type && !$noconfig ? ' AND pm.type_image IN ("' . implode('","', array_map('pSQL', $product_type)) . '")' : '') . ($type_image ? ' AND pm.type_image="' . pSQL($type_image) . '"' : '') . ($check_quality ? ' AND pm.quality = "' . (int)$quality . '"' : ' AND pm.quality!=100') . ($check_optimize_script ? 'AND optimize_type="' . pSQL($optimize_script) . '"' : ''));
                    if (Module::isInstalled('ets_multilangimages') && Module::isEnabled('ets_multilangimages')) {
                        $total += Db::getInstance()->getValue('
                        SELECT COUNT(pm.id_image_lang) FROM `' . _DB_PREFIX_ . 'ets_superspeed_product_image_lang` pm
                        INNER JOIN `' . _DB_PREFIX_ . 'ets_image_lang` m ON (pm.id_image_lang = m.id_image_lang)
                        WHERE 1' . ($all_type && $product_type && !$noconfig ? ' AND pm.type_image IN ("' . implode('","', array_map('pSQL', $product_type)) . '")' : '') . ($type_image ? ' AND pm.type_image="' . pSQL($type_image) . '"' : '') . ($check_quality ? ' AND pm.quality = "' . (int)$quality . '"' : ' AND pm.quality!=100') . ($check_optimize_script ? 'AND optimize_type="' . pSQL($optimize_script) . '"' : ''));
                    }
                    self::$_total_image[$key] = $count_type ? $total : 0;
                    return self::$_total_image[$key];
                }
                $totalimageProducts = Ets_superspeed_compressor_image::getTotalImageProducts();
                self::$_total_image[$key] = $totalimageProducts * $count_type;
                return self::$_total_image[$key];
            case 'blog_post':
                if (Module::isEnabled('ybc_blog')) {
                    if (Tools::isSubmit('changeSubmitImageOptimize'))
                        $blog_post_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_POST_TYPE') ? Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_POST_TYPE') : array();
                    else
                        $blog_post_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_POST_TYPE', Configuration::getGlobalValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_POST_TYPE') ? explode(',', Configuration::getGlobalValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_POST_TYPE')) : array());
                    $total = 0;
                    if ($all_type && Ets_superspeed::validateArray($blog_post_type)) {
                        if (in_array('image', $blog_post_type) || $noconfig)
                            $total += Ets_superspeed_compressor_image::getTotalImageBlogPosts('image');
                        if (in_array('thumb', $blog_post_type) || $noconfig)
                            $total += Ets_superspeed_compressor_image::getTotalImageBlogPosts('thumb');
                    } elseif ($type_image && in_array($type_image, array('image', 'thumb')))
                        $total += Ets_superspeed_compressor_image::getTotalImageBlogPosts($type_image);
                    if ($optimizaed) {
                        $total_optimized = Db::getInstance()->getValue('SELECT COUNT(sbpi.id_post) FROM `' . _DB_PREFIX_ . 'ets_superspeed_blog_post_image` sbpi
                        INNER JOIN `' . _DB_PREFIX_ . 'ybc_blog_post` bp ON (sbpi.id_post = bp.id_post)
                        WHERE 1' . ($all_type && $blog_post_type && Ets_superspeed::validateArray($blog_post_type) && !$noconfig ? ' AND  type_image IN ("' . implode('","', array_map('pSQL', $blog_post_type)) . '")' : '') . ($type_image ? ' AND type_image="' . pSQL($type_image) . '"' : '') . ($check_quality ? ' AND quality = "' . (int)$quality . '"' : ' AND quality!=100') . ($check_optimize_script ? ' AND optimize_type="' . pSQL($optimize_script) . '"' : ''));
                        self::$_total_image[$key] =  $total_optimized > $total ? $total : $total_optimized;
                        return self::$_total_image[$key];
                    }
                    self::$_total_image[$key] = $total;
                    return $total;
                } else
                {
                    self::$_total_image[$key] =0;
                    return 0;
                }
            case 'blog_category':
                if (Module::isEnabled('ybc_blog')) {
                    if (Tools::isSubmit('changeSubmitImageOptimize'))
                        $blog_category_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_CATEGORY_TYPE') ? Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_CATEGORY_TYPE') : array();
                    else
                        $blog_category_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_CATEGORY_TYPE', Configuration::getGlobalValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_CATEGORY_TYPE') ? explode(',', Configuration::getGlobalValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_CATEGORY_TYPE')) : array());
                    $total = 0;
                    if ($all_type && Ets_superspeed::validateArray($blog_category_type)) {
                        if (in_array('image', $blog_category_type) || $noconfig)
                            $total += Ets_superspeed_compressor_image::getTotalImageBlogCategories('image');
                        if (in_array('thumb', $blog_category_type) || $noconfig)
                            $total += Ets_superspeed_compressor_image::getTotalImageBlogCategories('thumb');
                    } elseif ($type_image && in_array($type_image, array('image', 'thumb')))
                        $total += Ets_superspeed_compressor_image::getTotalImageBlogCategories($type_image);
                    if ($optimizaed) {
                        $total_optimized = Db::getInstance()->getValue('SELECT COUNT(sbci.id_category) FROM `' . _DB_PREFIX_ . 'ets_superspeed_blog_category_image` sbci
                        INNER JOIN `' . _DB_PREFIX_ . 'ybc_blog_category` bc ON (bc.id_category = sbci.id_category)
                        WHERE 1 ' . ($all_type && $blog_category_type && Ets_superspeed::validateArray($blog_category_type) && !$noconfig ? ' AND  type_image IN ("' . implode('","', array_map('pSQL', $blog_category_type)) . '")' : '') . ($type_image ? ' AND type_image="' . pSQL($type_image) . '"' : '') . ($check_quality ? ' AND quality = "' . (int)$quality . '"' : ' AND quality!=100') . ($check_optimize_script ? ' AND optimize_type="' . pSQL($optimize_script) . '"' : ''));
                        self::$_total_image[$key] = $total_optimized > $total ? $total : $total_optimized;
                        return self::$_total_image[$key];
                    }
                    self::$_total_image[$key] = $total;
                    return $total;
                } else
                {
                    self::$_total_image[$key] =0;
                    return 0;
                }
            case 'blog_gallery':
                if (Module::isEnabled('ybc_blog')) {
                    if (Tools::isSubmit('changeSubmitImageOptimize'))
                        $blog_gallery_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_GALLERY_TYPE') ? Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_GALLERY_TYPE') : array();
                    else
                        $blog_gallery_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_GALLERY_TYPE', Configuration::getGlobalValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_GALLERY_TYPE') ? explode(',', Configuration::getGlobalValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_GALLERY_TYPE')) : array());
                    $total = 0;
                    if ($all_type && Ets_superspeed::validateArray($blog_gallery_type)) {
                        if (in_array('image', $blog_gallery_type) || $noconfig)
                            $total += Ets_superspeed_compressor_image::getTotalImageBlogGalleries('image');
                        if (in_array('thumb', $blog_gallery_type) || $noconfig)
                            $total += Ets_superspeed_compressor_image::getTotalImageBloggalleries('thumb');
                    } elseif ($type_image && in_array($type_image, array('image', 'thumb')))
                        $total += Ets_superspeed_compressor_image::getTotalImageBlogGalleries($type_image);
                    if ($optimizaed) {
                        $total_optimized = Db::getInstance()->getValue('SELECT COUNT(sbgi.id_gallery) FROM `' . _DB_PREFIX_ . 'ets_superspeed_blog_gallery_image` sbgi
                        INNER JOIN `' . _DB_PREFIX_ . 'ybc_blog_gallery` bg ON (bg.id_gallery = sbgi.id_gallery)
                        WHERE 1' . ($all_type && $blog_gallery_type && Ets_superspeed::validateArray($blog_gallery_type) && !$noconfig ? ' AND  type_image IN ("' . implode('","', array_map('pSQL', $blog_gallery_type)) . '")' : '') . ($type_image ? ' AND type_image="' . pSQL($type_image) . '"' : '') . ($check_quality ? ' AND quality = "' . (int)$quality . '"' : ' AND quality!=100') . ($check_optimize_script ? ' AND optimize_type="' . pSQL($optimize_script) . '"' : ''));
                        self::$_total_image[$key] = $total_optimized > $total ? $total : $total_optimized;
                        return self::$_total_image[$key];
                    }
                    self::$_total_image[$key] = $total;
                    return $total;
                } else
                    return 0;
            case 'blog_slide':
                if (Module::isEnabled('ybc_blog')) {
                    if (Tools::isSubmit('changeSubmitImageOptimize'))
                        $blog_slide_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_SLIDE_TYPE') ? Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_SLIDE_TYPE') : array();
                    else
                        $blog_slide_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_SLIDE_TYPE', Configuration::getGlobalValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_SLIDE_TYPE') ? explode(',', Configuration::getGlobalValue('ETS_SPEED_OPTIMIZE_IMAGE_BLOG_SLIDE_TYPE')) : array());
                    $total = 0;
                    if ($all_type) {
                        if ((in_array('image', $blog_slide_type) && Ets_superspeed::validateArray($blog_slide_type)) || $noconfig)
                            $total += Ets_superspeed_compressor_image::getTotalImageBlogSlide();
                    } elseif ($type_image && in_array($type_image, array('image')))
                        $total += Ets_superspeed_compressor_image::getTotalImageBlogSlide();
                    if ($optimizaed) {
                        $total_optimized = Db::getInstance()->getValue('SELECT COUNT(sbsi.id_slide) FROM `' . _DB_PREFIX_ . 'ets_superspeed_blog_slide_image` sbsi
                        INNER JOIN `' . _DB_PREFIX_ . 'ybc_blog_slide` bs ON (bs.id_slide = sbsi.id_slide)
                        WHERE 1' . ($all_type && $blog_slide_type && Ets_superspeed::validateArray($blog_slide_type) && !$noconfig ? ' AND  type_image IN ("' . implode('","', array_map('pSQL', $blog_slide_type)) . '")' : '') . ($type_image ? ' AND type_image="' . pSQL($type_image) . '"' : '') . ($check_quality ? ' AND quality = "' . (int)$quality . '"' : ' AND quality!=100') . ($check_optimize_script ? ' AND optimize_type="' . pSQL($optimize_script) . '"' : ''));
                        self::$_total_image[$key] = $total_optimized > $total ? $total : $total_optimized;
                        return self::$_total_image[$key];
                    }
                    self::$_total_image[$key] = $total;
                    return self::$_total_image[$key];
                } else
                {
                    self::$_total_image[$key] = 0;
                    return 0;
                }
            case 'home_slide':
                if (Module::isEnabled('blockbanner') ||  Module::isEnabled('ps_banner')) {
                    if (Tools::isSubmit('changeSubmitImageOptimize'))
                        $home_slide_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_HOME_SLIDE_TYPE') ? Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_HOME_SLIDE_TYPE') : array();
                    else
                        $home_slide_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_HOME_SLIDE_TYPE', Configuration::getGlobalValue('ETS_SPEED_OPTIMIZE_IMAGE_HOME_SLIDE_TYPE') ? explode(',', Configuration::getGlobalValue('ETS_SPEED_OPTIMIZE_IMAGE_HOME_SLIDE_TYPE')) : array());
                    $total = 0;
                    if (($home_slide_type && Ets_superspeed::validateArray($home_slide_type)) || ($all_type && $noconfig) || $type_image) {
                        $total += Ets_superspeed_compressor_image::getTotalImageHomeSlides();
                    }
                    if ($optimizaed) {
                        $total_optimized = Ets_superspeed_compressor_image::getTotalImageOptimizedHomeSlides($check_quality,$quality,$check_optimize_script,$optimize_script);
                        return $total_optimized > $total ? $total : $total_optimized;
                    }
                    self::$_total_image[$key] = $total;
                    return $total;
                } else
                {
                    self::$_total_image[$key] = 0;
                    return 0;
                }
            case 'others' :
            {
                if (Tools::isSubmit('changeSubmitImageOptimize'))
                    $orther_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_OTHERS_TYPE') ? Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_OTHERS_TYPE') : array();
                else
                    $orther_type = Tools::getValue('ETS_SPEED_OPTIMIZE_IMAGE_OTHERS_TYPE', Configuration::getGlobalValue('ETS_SPEED_OPTIMIZE_IMAGE_OTHERS_TYPE') ? explode(',', Configuration::getGlobalValue('ETS_SPEED_OPTIMIZE_IMAGE_OTHERS_TYPE')) : array());
                $total = 0;
                if ($all_type && Ets_superspeed::validateArray($orther_type)) {
                    if (in_array('logo', $orther_type) || $noconfig)
                        $total += (Configuration::get('PS_LOGO') ? 1 : 0);
                    if (in_array('banner', $orther_type) || $noconfig) {
                        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
                            if (Module::isInstalled('ps_banner') && Module::isEnabled('ps_banner')) {
                                $languages = Language::getLanguages(false);
                                $banners = array();
                                foreach ($languages as $language) {
                                    if (($image = Configuration::get('BANNER_IMG', $language['id_lang'])) && !in_array($image, $banners)) {
                                        $banners[] = $image;
                                        $total++;
                                    }
                                }
                            }
                        } else {
                            if (Module::isInstalled('blockbanner') && Module::isEnabled('blockbanner')) {
                                $languages = Language::getLanguages(false);
                                $banners = array();
                                foreach ($languages as $language) {
                                    if (($image = Configuration::get('BLOCKBANNER_IMG', $language['id_lang'])) && !in_array($image, $banners)) {
                                        $banners[] = $image;
                                        $total++;
                                    }
                                }
                            }
                        }
                    }
                    if (in_array('themeconfig', $orther_type) || $noconfig) {

                        if (Module::isInstalled('themeconfigurator') && Module::isEnabled('themeconfigurator')) {
                            $themeconfigurators = Db::getInstance()->executeS('SELECT image FROM `' . _DB_PREFIX_ . 'themeconfigurator` WHERE image!="" GROUP BY image');
                            $themes = array();
                            if ($themeconfigurators) {
                                foreach ($themeconfigurators as $themeconfigurator) {
                                    $themes[] = $themeconfigurator['image'];
                                    $total++;
                                }
                            }
                        }
                    }
                } elseif ($type_image && in_array($type_image, array('logo', 'banner', 'themeconfig'))) {
                    if ($type_image == 'logo' && Configuration::get('PS_LOGO'))
                        $total++;
                    elseif ($type_image == 'banner') {
                        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
                            if (Module::isInstalled('ps_banner') && Module::isEnabled('ps_banner')) {
                                $languages = Language::getLanguages(false);
                                $banners = array();
                                foreach ($languages as $language) {
                                    if (($image = Configuration::get('BANNER_IMG', $language['id_lang'])) && !in_array($image, $banners)) {
                                        $banners[] = $image;
                                        $total++;
                                    }
                                }
                            }
                        } else {
                            if (Module::isInstalled('blockbanner') && Module::isEnabled('blockbanner')) {
                                $languages = Language::getLanguages(false);
                                $banners = array();
                                foreach ($languages as $language) {
                                    if (($image = Configuration::get('BLOCKBANNER_IMG', $language['id_lang'])) && !in_array($image, $banners)) {
                                        $banners[] = $image;
                                        $total++;
                                    }
                                }
                            }
                        }
                    } elseif ($type_image == 'themeconfig') {
                        if (Module::isInstalled('themeconfigurator') && Module::isEnabled('themeconfigurator')) {
                            $themeconfigurators = Db::getInstance()->executeS('SELECT image FROM `' . _DB_PREFIX_ . 'themeconfigurator` WHERE image!="" GROUP BY image');
                            $themes = array();
                            if ($themeconfigurators) {
                                foreach ($themeconfigurators as $themeconfigurator) {
                                    $themes[] = $themeconfigurator['image'];
                                    $total++;
                                }
                            }
                        }
                    }
                }
                if ($optimizaed) {
                    if (isset($banners))
                        $images = $banners;
                    else
                        $images = array();
                    if (Configuration::get('PS_LOGO'))
                        $images[] = Configuration::get('PS_LOGO');
                    if (isset($themes))
                        $images = array_merge($images, $themes);
                    $total_optimized = Db::getInstance()->getValue('SELECT COUNT(image) FROM `' . _DB_PREFIX_ . 'ets_superspeed_others_image`
                    WHERE 1' . ($all_type && $orther_type && Ets_superspeed::validateArray($orther_type) && !$noconfig ? ' AND  type_image IN ("' . implode('","', array_map('pSQL', $orther_type)) . '")' : '') . ($type_image ? ' AND type_image="' . pSQL($type_image) . '"' : '') . ($images ? ' AND image IN ("' . implode('","', array_map('pSQL', $images)) . '")' : '') . ($check_quality ? ' AND quality = "' . (int)$quality . '"' : ' AND quality!=100') . ($check_optimize_script ? ' AND optimize_type="' . pSQL($optimize_script) . '"' : ''));
                    self::$_total_image[$key] = $total_optimized > $total ? $total : $total_optimized;
                    return self::$_total_image[$key];
                }
                self::$_total_image[$key] = $total;
                return $total;
            }
        }
        self::$_total_image[$key] = $total;
        return $total;
    }
    public function l($string)
    {
        return Translate::getModuleTranslation('ets_superspeed', $string, pathinfo(__FILE__, PATHINFO_FILENAME));
    }
    public function getModulesDynamic()
    {
        $dynamic_hooks = Ets_superspeed_defines::getInstance()->getFieldConfig('_dynamic_hooks');
        $customerSignin = 'ps_customersignin';
        $shoppingcart = 'ps_shoppingcart';
        $sql = 'SELECT m.id_module,m.name,m.version FROM `' . _DB_PREFIX_ . 'module` m
        INNER JOIN `' . _DB_PREFIX_ . 'hook_module` mh ON (mh.id_module=m.id_module)
        INNER JOIN `' . _DB_PREFIX_ . 'hook` h ON (h.id_hook=mh.id_hook)
        WHERE m.name!="' . pSQL($customerSignin) . '" AND m.name!="' . pSQL($shoppingcart) . '" AND m.name!="blockcart" AND m.name!="blockuserinfo" AND h.name IN ("' . implode('","', array_map('pSQL', $dynamic_hooks)) . '") GROUP BY m.name';
        $modules = Db::getInstance()->executeS($sql);
        if ($modules) {
            foreach ($modules as $key=> &$module) {
                if(!file_exists(_PS_MODULE_DIR_.$module['name'].'/'.$module['name'].'.php'))
                    unset($modules[$key]);
                else
                {
                    $sql = 'SELECT h.id_hook,h.name,d.id_module,d.empty_content FROM `' . _DB_PREFIX_ . 'hook` h
                    INNER JOIN `' . _DB_PREFIX_ . 'hook_module` mh ON (mh.id_hook=h.id_hook)
                    INNER JOIN `' . _DB_PREFIX_ . 'module` m ON (m.id_module=mh.id_module)
                    LEFT JOIN `'._DB_PREFIX_.'ets_superspeed_dynamic` d ON (d.id_module = m.id_module AND d.hook_name = h.name)
                    WHERE h.name IN ("' . implode('","', array_map('pSQL', $dynamic_hooks)) . '") AND m.id_module="' . (int)$module['id_module'] . '" GROUP BY h.name';
                    $module['hooks'] = Db::getInstance()->executeS($sql);
                    if ($module['hooks']) {
                        foreach ($module['hooks'] as &$hook) {
                            $hook['dynamic'] = $hook['id_module'] ? array('id_module' => $hook['id_module'],'empty_content' => $hook['empty_content']): array();
                        }
                    }
                    $module['logo'] = self::getBaseLink() . '/modules/' . $module['name'] . '/logo.png';
                }

            }
        }
        return $modules;
    }
    public static function _installDb()
    {
        $rs = Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_superspeed_cache_page` (
            `id_cache_page` int(11) NOT NULL AUTO_INCREMENT,
            `page` varchar(50) NOT NULL,
            `id_object` int(11) NOT NULL,
            `id_product_attribute` INT(11) NOT NULL,
            `ip` varchar(40) NOT NULL,
            `file_cache`  VARCHAR(65),
            `request_uri` VARCHAR(256) NOT NULL,
            `id_shop` int(11) NOT NULL,
            `id_lang` int(11) NOT NULL,
            `id_currency` int(11) NOT NULL,
            `id_country` int(11) NOT NULL,
            `has_customer` int(1) NOT NULL,
            `has_cart` int(1) NOT NULL,
            `click` int(11) NOT NULL,
            `file_size` FLOAT(10,2),
            `user_agent` VARCHAR(300),
            `date_add` datetime NOT NULL,
            `date_expired` datetime NULL,
          PRIMARY KEY (`id_cache_page`))  ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $rs &=Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_superspeed_cache_page_error` (
            `id_ets_superspeed_cache_page_error` int(11) NOT NULL AUTO_INCREMENT,
            `id_cache_page` int(11) NOT NULL,
            `page` varchar(50) NOT NULL,
            `id_object` int(11) NOT NULL,
            `id_product_attribute` INT(11) NOT NULL,
            `ip` varchar(40) NOT NULL,
            `file_cache`  VARCHAR(33),
            `request_uri` VARCHAR(256) NOT NULL,
            `id_shop` int(11) NOT NULL,
            `id_lang` int(11) NOT NULL,
            `id_currency` int(11) NOT NULL,
            `id_country` int(11) NOT NULL,
            `has_customer` int(1) NOT NULL,
            `has_cart` int(1) NOT NULL,
            `error` TEXT,
            `date_add` datetime NOT NULL, INDEX(file_cache),
          PRIMARY KEY (`id_ets_superspeed_cache_page_error`))  ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $rs &= Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_superspeed_cache_page_hook` (
              `id_cache_page` int(11) NOT NULL,
              `hook_name` varchar(64) NOT NULL,
              PRIMARY KEY( `id_cache_page`, `hook_name`)
            )  ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $rs &= Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_superspeed_category_image` (
          `id_category` int(11) NOT NULL,
          `type_image` varchar(64) NOT NULL,
          `quality` int(11) NOT NULL,
          `size_old` float(10,2),
          `size_new` float(10,2),
          `optimize_type` VARCHAR(8),
          PRIMARY KEY( `id_category`, `type_image`)
          )  ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $rs &= Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_superspeed_dynamic` (
              `id_module` int(11) NOT NULL,
              `hook_name` varchar(64) NOT NULL,
              `empty_content` int(1) DEFAULT NULL,
              PRIMARY KEY( `id_module`, `hook_name`)
            )  ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $rs &= Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_superspeed_manufacturer_image` (
          `id_manufacturer` int(11) NOT NULL,
          `type_image` varchar(64) NOT NULL,
          `quality` int(11) NOT NULL,
          `size_old` float(10,2),
          `size_new` float(10,2),
          `optimize_type` VARCHAR(8),
          PRIMARY KEY( `id_manufacturer`, `type_image`)
        )  ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $rs &= Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_superspeed_product_image` (
          `id_image` int(11) NOT NULL,
          `type_image` varchar(64) NOT NULL,
          `quality` int(11) NOT NULL,
          `size_old` float(10,2),
          `size_new` float(10,2),
          `optimize_type` VARCHAR(8),
          PRIMARY KEY( `id_image`, `type_image`)
        )  ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $rs &= Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_superspeed_product_image_lang` (
          `id_image_lang` int(11) NOT NULL,
          `id_lang` int(11) NULL,
          `type_image` varchar(64) NOT NULL,
          `quality` int(11) NOT NULL,
          `size_old` float(10,2),
          `size_new` float(10,2),
          `optimize_type` VARCHAR(8),
          PRIMARY KEY( `id_image_lang`, `type_image`)
        )  ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $rs &= Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_superspeed_time` (
          `id_shop` int(11) NOT NULL,
          `date` datetime NOT NULL,
          `time` float(10,2) NOT NULL
        )  ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $rs &= Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_superspeed_supplier_image` (
          `id_supplier` int(11) NOT NULL,
          `type_image` varchar(32) NOT NULL,
          `quality` int(11) NOT NULL,
          `size_old` float(10,2),
          `size_new` float(10,2),
          `optimize_type` VARCHAR(8),
          PRIMARY KEY( `id_supplier`, `type_image`)
        )  ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $rs &= Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_superspeed_blog_post_image` (
          `id_post` int(11) NOT NULL,
          `type_image` varchar(32) NOT NULL,
          `image` varchar(222) NOT NULL,
          `thumb` varchar(222) NOT NULL,
          `quality` int(11) NOT NULL,
          `size_old` float(10,2),
          `size_new` float(10,2),
          `optimize_type` VARCHAR(8),
          PRIMARY KEY( `id_post`, `type_image`,`image`,`thumb`)
        )  ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8 COLLATE=utf8_general_ci');
        $rs &= Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_superspeed_blog_category_image` (
          `id_category` int(11) NOT NULL,
          `type_image` varchar(32) NOT NULL,
          `image` varchar(100) NOT NULL,
          `thumb` varchar(100) NOT NULL,
          `quality` int(11) NOT NULL,
          `size_old` float(10,2),
          `size_new` float(10,2),
          `optimize_type` VARCHAR(8),
          PRIMARY KEY( `id_category`, `type_image`,`image`,`thumb`)
        )  ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $rs &= Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_superspeed_blog_gallery_image` (
          `id_gallery` int(11) NOT NULL,
          `type_image` varchar(32) NOT NULL,
          `image` varchar(100) NOT NULL,
          `thumb` varchar(100) NOT NULL,
          `quality` int(11) NOT NULL,
          `size_old` float(10,2),
          `size_new` float(10,2),
          `optimize_type` VARCHAR(8),
          PRIMARY KEY( `id_gallery`, `type_image`,`image`,`thumb`)
        )  ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $rs &= Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_superspeed_blog_slide_image` (
          `id_slide` int(11) NOT NULL,
          `type_image` varchar(32) NOT NULL,
          `image` varchar(100) NOT NULL,
          `quality` int(11) NOT NULL,
          `size_old` float(10,2),
          `size_new` float(10,2),
          `optimize_type` VARCHAR(8),
          PRIMARY KEY( `id_slide`, `type_image`,`image`)
        )  ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $rs &= Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_superspeed_home_slide_image` (
          `id_homeslider_slides` int(11) NOT NULL,
          `image` varchar(165) NOT NULL,
          `type_image` varchar(64) NOT NULL,
          `quality` int(11) NOT NULL,
          `size_old` float(10,2),
          `size_new` float(10,2),
          `optimize_type` VARCHAR(8),
          PRIMARY KEY( `id_homeslider_slides`, `type_image`,`image`)
        )  ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $rs &= Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_superspeed_others_image`(
          `image` varchar(165) NOT NULL,
          `type_image` varchar(64) NOT NULL,
          `quality` int(11) NOT NULL,
          `size_old` float(10,2),
          `size_new` float(10,2),
          `optimize_type` VARCHAR(8),
          PRIMARY KEY(`type_image`,`image`)
        )  ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $rs &= Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS  `' . _DB_PREFIX_ . 'ets_superspeed_hook_time` ( 
        `id_module` INT(11) NOT NULL , 
        `hook_name` VARCHAR(111) NOT NULL , 
        `page` VARCHAR(256) NOT NULL , 
        `id_shop` INT(11) NOT NULL,
        `date_add` datetime NOT NULL , 
        `time` float(10,4) NOT NULL , 
        PRIMARY KEY (`id_module`, `hook_name`,`id_shop`)) ENGINE = InnoDB');
        $rs &= Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_superspeed_hook_module` ( 
        `id_module` INT(11) NOT NULL , 
        `id_shop` INT(11) NOT NULL , 
        `id_hook` INT(11) NOT NULL , 
        `position` INT(2) NOT NULL , 
        PRIMARY KEY (`id_module`, `id_shop`, `id_hook`)) ENGINE = InnoDB');
        $rs &= Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_superspeed_upload_image` (
          `id_ets_superspeed_upload_image` int(11) NOT NULL AUTO_INCREMENT,
          `image_name` varchar(222) NOT NULL,
          `old_size` float(10,2) NOT NULL,
          `new_size` float(10,2) NOT NULL,
          `image_name_new` varchar(222) NOT NULL,
          `date_add` datetime NOT NULL,
           PRIMARY KEY (`id_ets_superspeed_upload_image`))  ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $rs &= Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_superspeed_browse_image` (
          `id_ets_superspeed_browse_image` int(11) NOT NULL AUTO_INCREMENT,
          `image_name` varchar(222) NOT NULL,
          `image_dir` text,
          `image_id` text,
          `old_size` float(10,2) NOT NULL,
          `new_size` float(10,2) NOT NULL,
          `date_add` datetime NOT NULL,
           PRIMARY KEY (`id_ets_superspeed_browse_image`))  ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        $rs &= Db::getInstance()->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'ets_superspeed_cache_page_log` (
            `id_ets_superspeed_cache_page_log` int(11) NOT NULL AUTO_INCREMENT,
            `page` varchar(200) NOT NULL,
            `reason` TEXT NOT NULL,
            `date_add` datetime NOT NULL,
          PRIMARY KEY (`id_ets_superspeed_cache_page_log`))  ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci');
        return $rs;
    }
    public static function _uninstallTable()
    {
        $res = Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'ets_superspeed_cache_page`');
        $res &= Db::getInstance()->execute("DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "ets_superspeed_cache_page_hook`");
        $res &= Db::getInstance()->execute("DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "ets_superspeed_dynamic`");
        $res &= Db::getInstance()->execute("DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "ets_superspeed_category_image`");
        $res &= Db::getInstance()->execute("DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "ets_superspeed_manufacturer_image`");
        $res &= Db::getInstance()->execute("DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "ets_superspeed_product_image`");
        $res &= Db::getInstance()->execute("DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "ets_superspeed_supplier_image`");
        $res &= Db::getInstance()->execute("DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "ets_superspeed_time`");
        $res &= Db::getInstance()->execute("DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "ets_superspeed_hook_module`");
        $res &= Db::getInstance()->execute("DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "ets_superspeed_hook_time`");
        $res &= Db::getInstance()->execute("DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "ets_superspeed_blog_post_image`");
        $res &= Db::getInstance()->execute("DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "ets_superspeed_blog_category_image`");
        $res &= Db::getInstance()->execute("DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "ets_superspeed_blog_gallery_image`");
        $res &= Db::getInstance()->execute("DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "ets_superspeed_blog_slide_image`");
        $res &= Db::getInstance()->execute("DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "ets_superspeed_home_slide_image`");
        $res &= Db::getInstance()->execute("DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "ets_superspeed_others_image`");
        $res &= Db::getInstance()->execute("DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "ets_superspeed_browse_image`");
        $res &= Db::getInstance()->execute("DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "ets_superspeed_upload_image`");
        $res &= Db::getInstance()->execute("DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "ets_superspeed_product_image_lang`");
        $res &= Db::getInstance()->execute("DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "ets_superspeed_cache_page_error`");
        $res &= Db::getInstance()->execute("DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "ets_superspeed_cache_page_log`");
        return $res;
    }
    public static function createIndexDataBase()
    {
        $sqls = array();
        $sqls[] ='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_cache_page`  ADD INDEX (`page`)';
        $sqls[] ='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_cache_page`  ADD INDEX (`id_object`)';
        $sqls[] ='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_cache_page`  ADD INDEX ( `id_product_attribute`)';
        $sqls[] ='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_cache_page`  ADD INDEX (`ip`)';
        $sqls[] ='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_cache_page`  ADD INDEX ( `file_cache`)';
        $sqls[] ='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_cache_page`  ADD INDEX (`id_shop`)';
        $sqls[] ='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_cache_page`  ADD INDEX (`id_lang`)';
        $sqls[] ='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_cache_page`  ADD INDEX ( `id_currency`)';
        $sqls[] ='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_cache_page`  ADD INDEX (`id_country`)';
        $sqls[] ='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_cache_page`  ADD INDEX (`has_customer`)';
        $sqls[] ='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_cache_page`  ADD INDEX (`has_cart`)';
        $sqls[] ='ALTER TABLE `'._DB_PREFIX_.'ets_superspeed_product_image_lang` ADD INDEX (`id_lang`)';
        foreach($sqls as $sql)
        {
            Db::getInstance()->execute($sql);
        }
        return true;
    }
    public static function getSupplierByIdProduct($id_product)
    {
       return  Db::getInstance()->executeS('SELECT id_supplier FROM `' . _DB_PREFIX_ . 'product_supplier` where id_product=' . (int)$id_product);
    }
    public static function getCategoryByIdProduct($id_product)
    {
        return Db::getInstance()->executeS('SELECT id_category FROM `' . _DB_PREFIX_ . 'category_product` WHERE id_product=' . (int)$id_product);
    }
    public static function getProductByIdCategory($id_category)
    {
        return Db::getInstance()->executeS('SELECT id_product FROM `' . _DB_PREFIX_ . 'category_product` WHERE id_category=' . (int)$id_category);
    }
    public static function getCmsByIdCategoryCMS($id_category)
    {
        return Db::getInstance()->executeS('SELECT id_cms FROM `' . _DB_PREFIX_ . 'cms` WHERE id_cms_category=' . (int)$id_category);
    }
    public static function getIDModuleByName($module_name)
    {
        $cache_key ='Ets_superspeed_defines::getIDModuleByName_'.$module_name;
        if(!Cache::isStored($cache_key))
        {
            $result = (int)Db::getInstance()->getValue('SELECT `id_module` FROM `' . _DB_PREFIX_ . 'module` WHERE `name` = "' . pSQL($module_name) . '"');
            Cache::store($cache_key,$result);
        }
        return Cache::retrieve($cache_key);
    }
    public static function checkModuleIsActive($id_module)
    {
        return Db::getInstance()->getValue('SELECT `id_module` FROM `' . _DB_PREFIX_ . 'module_shop` WHERE `id_module` = ' . (int)$id_module . ' AND `id_shop` = ' . (int)Context::getContext()->shop->id);
    }
    public static function getColumnTable($table)
    {
        try {
            return Db::getInstance()->ExecuteS('DESCRIBE ' . _DB_PREFIX_ . bqSQL($table));
        }
        catch(Exception $ex){
            if($ex){
                return false;
            }
        }
        return false;
    }
    public static function getTotalRowTable($table,$where,$count = true)
    {
        try {
            if($count)
                return Db::getInstance()->getValue('SELECT COUNT(*) FROM `' . _DB_PREFIX_ . bqSQL($table) . '`' . (string)$where);
            else
                return Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . bqSQL($table) . '`' . (string)$where);
        }
        catch(Exception $ex){
            if($ex){
                return false;
            }
        }
        return false;
    }
    public static function deleteRowTable($table,$where)
    {
        try {
            Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . bqSQL($table) . '`' . (string)$where);
        }
        catch(Exception $ex){
            if($ex){
                return false;
            }
        }
        return false;

    }
    public static function deleteHookTime()
    {
        return Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'ets_superspeed_hook_time`');
    }
    public static function getHookTimeByFilter($filter='',$total=false,$orderby='',$orderway='',$start=0,$limit=20)
    {
        $sql = 'SELECT '.($total ? 'COUNT(*)':'DISTINCT pht.*, phm.id_module as disabled').' FROM `' . _DB_PREFIX_ . 'ets_superspeed_hook_time` pht
            INNER JOIN `' . _DB_PREFIX_ . 'module` m ON (m.id_module=pht.id_module)
            INNER JOIN `' . _DB_PREFIX_ . 'module_shop` ms ON (pht.id_module = ms.id_module)
            LEFT JOIN `' . _DB_PREFIX_ . 'ets_superspeed_hook_module` phm ON phm.id_module= pht.id_module
            LEFT JOIN `' . _DB_PREFIX_ . 'hook` h ON h.id_hook= phm.id_hook
            WHERE ms.id_shop="' . (int)Context::getContext()->shop->id . '" AND pht.id_module!= "'.(int)Module::getInstanceByName('ets_superspeed')->id.'" AND pht.id_shop="' . (int)Context::getContext()->shop->id . '" '.($filter ? (string)$filter :'');
        if($total)
            return Db::getInstance()->getValue($sql);
        else
        {
            if($orderby && $orderway)
                $sql .= ' ORDER BY ' . bqSQL($orderby) . ' ' . bqSQL($orderway) ;
            if($limit)
                $sql .= ' LIMIT ' . (int)$start . ',' . (int)$limit;
            return Db::getInstance()->executeS($sql);
        }
    }
    public static function changeRegisterHook($change_register_option,$hook_name,$id_module,$id_hook)
    {
        $module = Module::getInstanceById($id_module);
        if($module->id)
        {
            if(Configuration::get('SP_DEL_CACHE_HOOK_CHANGE'))
            {
                Ets_ss_class_cache::getInstance()->deleteCache('', 0, $hook_name);
                Ets_superspeed_cache_page_log::addLog('Page of hook #'.$hook_name,'Change register hook');
            }
            if($change_register_option)
            {
                if(!$module->isRegisteredInHook($hook_name))
                {
                    $module->registerHook($hook_name);
                    $module_hook_old = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'ets_superspeed_hook_module` WHERE id_module="'.(int)$id_module.'" AND id_hook="'.(int)$id_hook.'" AND id_shop='.(int)Context::getContext()->shop->id);
                    if($module_hook_old)
                        $module->updatePosition($id_hook,false,$module_hook_old['position']);
                }
                Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'ets_superspeed_hook_module` WHERE id_module="'.(int)$id_module.'" AND id_hook="'.(int)$id_hook.'" AND id_shop="'.(int)Context::getContext()->shop->id.'"');
            }
            else
            {
                if($module->isRegisteredInHook($hook_name))
                {
                    $postion = $module->getPosition($id_hook);
                    $module->unregisterHook($hook_name);
                }
                else
                    $postion=0;
                Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'ets_superspeed_hook_module`(id_module,id_hook,position,id_shop) values("'.(int)$id_module.'","'.(int)$id_hook.'","'.(int)$postion.'","'.(int)Context::getContext()->shop->id.'")');
            }
            return true;
        }
        return false;
    }
    public static function displayText($content,$tag,$attr_datas= array())
    {
        $text = '<' . $tag . ' ';
        if ($attr_datas) {
            foreach ($attr_datas as $key => $value)
                $text .= $key . '="' . $value . '" ';
        }
        if ($tag == 'img' || $tag == 'br' || $tag == 'path' || $tag == 'input')
            $text .= ' /'.'>';
        else
            $text .= '>';
        if ($tag && $tag != 'img' && $tag != 'input' && $tag != 'br' && !is_null($content))
            $text .= $content;
        if ($tag && $tag != 'img' && $tag != 'path' && $tag != 'input' && $tag != 'br')
            $text .= '<'.'/' . $tag . '>';
        return $text;
    }
    public static function checkEnableOtherShop($id_module)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'module_shop` WHERE `id_module` = ' . (int) $id_module . ' AND `id_shop` NOT IN(' . implode(', ', Shop::getContextListShopID()) . ')';
        return Db::getInstance()->executeS($sql);
    }
    public static function activeTab($module_name)
    {
        return Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'tab` SET enabled=1 where module ="'.pSQL($module_name).'"');
    }
    public static function isPathInAllowedDirectory($path, $allowed_directory = null)
    {
        if (empty($allowed_directory)) {
            $allowed_directory = _PS_ROOT_DIR_;
        }
        $realPath = realpath(dirname($path));
        $realAllowedDir = realpath($allowed_directory);

        return $realPath !== false && $realAllowedDir !== false && Tools::strpos($realPath, $realAllowedDir) === 0;
    }
    public static function unlink($filePath, $baseDir = null)
    {
        if ($baseDir === null) {
            $baseDir = _PS_ROOT_DIR_;
        }
        if (preg_match('/^[a-zA-Z0-9_\-\/\.:\\\]+$/', $filePath) && self::isPathInAllowedDirectory($filePath, $baseDir) && file_exists(realpath($filePath))) {
            return unlink(realpath($filePath));
        }
        return false;
    }
    public static function file_get_contents(
        $url,
        $use_include_path = false,
        $stream_context = null,
        $curl_timeout = 5,
        $fallback = false,
        $allowed_directory = null,
        $allowedExtensions = [],
        $allowed_domains = null
    )
    {
        if (preg_match('/^https?:\/\//', $url) || preg_match('/^http?:\/\//', $url)) {
            $url = self::sanitizeURL($url);

            if (!self::validateURL($url)) {
                error_log('Invalid URL: %s' . $url);
                return false;
            }

            if (!self::isAllowedDomain($url,$allowed_domains)) {
                error_log('Domain not allowed: ' . $url);
                return false;
            }
        } else {
            $url = self::sanitizePath($url);

            if (!$url || !self::isPathInAllowedDirectory($url, $allowed_directory)) {
                error_log('Unauthorized access attempt or invalid path: ' . $url);
                return false;
            }

            if (!empty($allowedExtensions) && !self::checkFileExtension($url, $allowedExtensions)) {
                error_log('Unsupported file extension: ' . $url);
                return false;
            }

            if (!file_exists($url)) {
                return false;
            }
        }
        $content = Tools::file_get_contents($url, $use_include_path, $stream_context, $curl_timeout, $fallback);
        if ($content === false) {
            error_log(sprintf('Failed to read file contents: ', $url), 3);
            return false;
        }
        return $content;
    }
    public static function file_put_contents($path, $data,$flags = 0, $context = null,$allowed_directory = null, $allowedExtensions = [])
    {
        $sanitizedPath = self::sanitizePath($path);

        if (!$sanitizedPath || !self::isPathInAllowedDirectory($sanitizedPath, $allowed_directory)) {
            error_log('Unauthorized access attempt or invalid path: ' . $path);
            return false;
        }

        if (!is_writable(dirname($sanitizedPath))) {
            error_log('Directory is not writable: ' . $path);
            return false;
        }

        if (!empty($allowedExtensions) && !self::checkFileExtension($path, $allowedExtensions)) {
            error_log('Unsupported file extension: ' . $path);
            return false;
        }

        $result = file_put_contents($sanitizedPath, $data, $flags, $context);
        if ($result === false) {
            error_log('Failed to write to file: ' . $path);
            return false;
        }

        return $result;
    }
    public static function checkFileExtension($path, $allowedExtensions)
    {
        $fileExtension = pathinfo($path, PATHINFO_EXTENSION);
        return in_array($fileExtension, $allowedExtensions);
    }
    public static function sanitizeURL($url)
    {
        return filter_var($url, FILTER_SANITIZE_URL);
    }

    public static function validateURL($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL);
    }

    public static function isAllowedDomain($url, $allowed_domains = [])
    {
        if (empty($allowed_domains)) {
            $allowed_domains = [Context::getContext()->shop->domain];
        }
        $host = parse_url($url, PHP_URL_HOST);
        return in_array($host, $allowed_domains);
    }
    public static function sanitizePath($path)
    {
        $sanitizedPath = preg_replace('/[^\w\-\/\.:\\\\]/', '', $path);
        return $sanitizedPath === $path ? $sanitizedPath : false;
    }
}
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
class Ets_superspeed_definesOverride extends Ets_superspeed_defines
{
    public function getFieldConfig1($field_type,$render_form = true)
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
}
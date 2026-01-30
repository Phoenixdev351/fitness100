<?php
/**
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
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2018 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

function getFormFields($object)
{
    $name = substr(strtolower(basename(__FILE__)), 0, -4);
    $form_fields = array();
    $form_fields['general'] = array(
        'html' => array(
            'type' => 'html',
            'id' => '',
            'label' => '',
            'name' => '<a class="boolbtn-0 btn btn-default" href="'.AdminController::$currentIndex.'&configure='.$object->name.'&clear_cache=1&token='.Tools::getAdminTokenLite('AdminModules').'">
                <span><i class="icon-trash"></i> '.$object->l('Clear cache').'</span></a>',
        ),
        'timeout' => array(
            'type' => 'text',
            'label' => $object->l('Cache expired time', $name),
            'name' => 'timeout',
            'default_value' => 60,
            'suffix' => 'mins',
            'validation' => 'isUnsignedInt',
            'class' => 'fixed-width-sm',
            'desc' => $object->l('The cache duration before refresh it automatically.', $name),
        ),
        'skip_logged' => array(
            'type' => 'switch',
            'label' => $object->l('Don\'t use cache for logged users', $name),
            'name' => 'skip_logged',
            'is_bool' => true,
            'default_value' => 1,
            'values' => array(
                array(
                    'id' => 'skip_logged_on',
                    'value' => 1,
                    'label' => $object->l('Yes', $name)),
                array(
                    'id' => 'skip_logged_off',
                    'value' => 0,
                    'label' => $object->l('No', $name)),
            ),
            'validation' => 'isBool',
            'desc' => $object->l('You have two options 1) Don\'t use cache for logged users. 2) Mark all user info related content as dynamic content on the "DYNAMIC CONTENT" tab.', $name),
        ),
        'cache_module' => array(
            'type' => 'switch',
            'label' => $object->l('Enable cache for pages created by modules', $name),
            'name' => 'cache_module',
            'is_bool' => true,
            'default_value' => 0,
            'values' => array(
                array(
                    'id' => 'cache_module_on',
                    'value' => 1,
                    'label' => $object->l('Yes', $name)),
                array(
                    'id' => 'cache_module_off',
                    'value' => 0,
                    'label' => $object->l('No', $name)),
            ),
            'validation' => 'isBool',
            'desc' => $object->l('You need to add the name of pages created by modules to the "Pages to be cached" field when the option is enabled.', $name),
        ),
        'enable_customgrp' => array(
            'type' => 'switch',
            'label' => $object->l('Different caches for different customer groups', $name),
            'name' => 'enable_customgrp',
            'is_bool' => true,
            'default_value' => 1,
            'values' => array(
                array(
                    'id' => 'enable_customgrp_on',
                    'value' => 1,
                    'label' => $object->l('Yes', $name)),
                array(
                    'id' => 'enable_customgrp_off',
                    'value' => 0,
                    'label' => $object->l('No', $name)),
            ),
            'validation' => 'isBool',
            'desc' => $object->l('If your shop offers different discounts or content for different customer groups, enable this option.', $name),
        ),
        'cache_type' => array(
            'type' => 'radio',
            'label' => $object->l('Cache type', $name),
            'name' => 'cache_type',
            'default_value' => 0,
            'values' => array(
                array(
                    'id' => 'cache_type_0',
                    'value' => 0,
                    'label' => $object->l('File system', $name)),
                array(
                    'id' => 'cache_type_1',
                    'value' => 1,
                    'label' => $object->l('Database', $name)),
            ),
            'validation' => 'isUnsignedInt',
            'desc' => $object->l('Using File system is recommended. Although using database is faster than using file system, but it can\'t handle a large amount of cache.', $name),
        ),
        'compress_cache' => array(
            'type' => 'switch',
            'label' => $object->l('Compress cache', $name),
            'name' => 'compress_cache',
            'is_bool' => true,
            'default_value' => 1,
            'values' => array(
                array(
                    'id' => 'compress_cache_on',
                    'value' => 1,
                    'label' => $object->l('Yes', $name)),
                array(
                    'id' => 'compress_cache_off',
                    'value' => 0,
                    'label' => $object->l('No', $name)),
            ),
            'validation' => 'isBool',
            'desc' => $object->l('Compress cache files to save disk space. This option is for using file system cache.', $name),
        ),
        'show_debug' => array(
            'type' => 'switch',
            'label' => $object->l('Show debug info window on the front office', $name),
            'name' => 'show_debug',
            'is_bool' => true,
            'default_value' => 0,
            'values' => array(
                array(
                    'id' => 'show_debug_on',
                    'value' => 1,
                    'label' => $object->l('Yes', $name)),
                array(
                    'id' => 'show_debug_off',
                    'value' => 0,
                    'label' => $object->l('No', $name)),
            ),
            'validation' => 'isBool',
        ),
        'controllers' => array(
            'type' => 'textarea',
            'label' => $object->l('Pages to be cached', $name),
            'name' => 'controllers',
            'cols' => 60,
            'rows' => 6,
            'default_value' => 'index,category,product,cms,search,bestsales,pricesdrop,newproducts,manufacturer,supplier,sitemap,stores',
            'validation' => 'isAnything',
            'desc' => array(
                $object->l('Use Comma to seperate controllers. Use colon to connect module name and module controller, for example: index,category,product,stblog:default,stblog:article and so on.', $name),
                $object->l('Some pages can\'t be cached, like contact us, checkout, shopping cart, etc.', $name),
            ),
        ),
        'hook_master' => array(
            'type' => 'textarea',
            'label' => $object->l('Never cache these hooks', $name),
            'name' => 'hook_master',
            'cols' => 60,
            'rows' => 6,
            'default_value' => 'stthemeeditor:displayHeader,stblogeditor:displayHeader',
            'validation' => 'isAnything',
            'desc' => $object->l('Hooks must be executed on all pages. Use Comma to seperate controllers. Use colon to connect module name and hook name.', $name),
        ),
        'ignores' => array(
            'type' => 'textarea',
            'label' => $object->l('Ignore parameters', $name),
            'name' => 'ignores',
            'cols' => 60,
            'rows' => 6,
            'validation' => 'isAnything',
            'desc' => $object->l('Ignore some URL parameters to avoid the same page got cached for several times just because of slight differences on url.', $name),
        ),
        'cache_debug' => array(
            'type' => 'switch',
            'label' => $object->l('Enable cache when in debug mode', $name),
            'name' => 'cache_debug',
            'is_bool' => true,
            'default_value' => 0,
            'values' => array(
                array(
                    'id' => 'cache_debug_on',
                    'value' => 1,
                    'label' => $object->l('Yes', $name)),
                array(
                    'id' => 'cache_debug_off',
                    'value' => 0,
                    'label' => $object->l('No', $name)),
            ),
            'validation' => 'isBool',
        ),
        'cache_restricted_country' => array(
            'type' => 'switch',
            'label' => $object->l('Cache for restricted countries', $name),
            'name' => 'cache_restricted_country',
            'is_bool' => true,
            'default_value' => 1,
            'values' => array(
                array(
                    'id' => 'cache_restricted_country_on',
                    'value' => 1,
                    'label' => $object->l('Yes', $name)),
                array(
                    'id' => 'cache_restricted_country_off',
                    'value' => 0,
                    'label' => $object->l('No', $name)),
            ),
            'validation' => 'isBool',
            'desc' => $object->l('Seperate cache for restricted countries.', $name),
        ),
        'cache_mobile' => array(
            'type' => 'switch',
            'label' => $object->l('Cache for mobile', $name),
            'name' => 'cache_mobile',
            'is_bool' => true,
            'default_value' => 1,
            'values' => array(
                array(
                    'id' => 'cache_mobile_on',
                    'value' => 1,
                    'label' => $object->l('Yes', $name)),
                array(
                    'id' => 'cache_mobile_off',
                    'value' => 0,
                    'label' => $object->l('No', $name)),
            ),
            'validation' => 'isBool',
            'desc' => $object->l('Seperate cache for mobile devices.', $name),
        ),
        'robots' => array(
            'type' => 'switch',
            'label' => $object->l('Don\'t use cache for robots', $name),
            'name' => 'robots',
            'is_bool' => true,
            'default_value' => 1,
            'values' => array(
                array(
                    'id' => 'robots_on',
                    'value' => 1,
                    'label' => $object->l('Yes', $name)),
                array(
                    'id' => 'robots_off',
                    'value' => 0,
                    'label' => $object->l('No', $name)),
            ),
            'validation' => 'isBool',
        ),
        'cache_front_token' => array(
            'type' => 'switch',
            'label' => $object->l('Enable cache when the Front office security is enabled.', $name),
            'name' => 'cache_front_token',
            'is_bool' => true,
            'default_value' => 1,
            'values' => array(
                array(
                    'id' => 'cache_front_token_on',
                    'value' => 1,
                    'label' => $object->l('Yes', $name)),
                array(
                    'id' => 'cache_front_token_off',
                    'value' => 0,
                    'label' => $object->l('No', $name)),
            ),
            'validation' => 'isBool',
            'desc' => $object->l('Keep this setting enable.', $name),
        ),
        'cron' => array(
            'type' => 'html',
            'id' => '',
            'label' => $object->l('Cron URL', $name),
            'name' => '<a href="javascript:void(0)">'.Context::getContext()->link->getModuleLink($object->name, 'cron', ['token'=>md5($object->name.'-cron')]).'</a>
            <p class="help-block">'.$object->l('Copy the URL and add it to your cron job to clear cache.', $name).'</p>',
        ),
    );
    $form_fields['modules'] = array(
        'html' => array(
            'type' => 'html',
            'id' => '',
            'label' => '',
            'name' => $object->getDynModulesHTML(),
        ),
    );
    $form_fields['stats'] = array(
        'html' => array(
            'type' => 'html',
            'id' => '',
            'label' => '',
            'name' => $object->getStatsHTML(),
        ),
    );
    
    return $form_fields;
}

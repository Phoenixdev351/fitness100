<?php
/**
* 2007-2015 PrestaShop.
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2015 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class Hook extends HookCore
{
    public static function coreCallHook($module, $method, $params)
    {
        $display = parent::coreCallHook($module, $method, $params);
        if (!is_string($display) || is_array($display) || strpos($method, 'displayPDF') !== false || strpos($method, 'displayInvoice') !== false) {
            return $display;
        }
        $dyn_hooks = unserialize(Configuration::get('ST_ADVCACHE_DYN_HOOKS'));
        $dyn_hooks || $dyn_hooks = array();
        $hook_name = lcfirst(str_replace('hook','', $method));
        if (in_array($hook_name, array('header', 'top'))) {
            $hook_name = 'display'.ucfirst($hook_name);
        }
        if (is_string($display) && key_exists($module->name, $dyn_hooks) && in_array($hook_name, $dyn_hooks[$module->name])) {
            $display = '<!--stadvcache:'.$module->name.':'.$hook_name.'['.self::prepareParams($params).']-->'.$display.'<!--stadvcache:'.$module->name.':'.$hook_name.'-->';
        }
        return $display;
    }
    public static function coreRenderWidget($module, $hook_name, $params)
    {
        $display = parent::coreRenderWidget($module, $hook_name, $params);
        if (!is_string($display) || is_array($display) || strpos($hook_name, 'displayPDF') !== false || strpos($hook_name, 'displayInvoice') !== false) {
            return $display;
        }
        $dyn_hooks = unserialize(Configuration::get('ST_ADVCACHE_DYN_HOOKS'));
        $dyn_hooks || $dyn_hooks = array();
        $hook_name = lcfirst(str_replace('hook','', $hook_name));
        if (in_array($hook_name, array('header', 'top'))) {
            $hook_name = 'display'.ucfirst($hook_name);
        }
        if (is_string($display) && key_exists($module->name, $dyn_hooks) && in_array($hook_name, $dyn_hooks[$module->name])) {
            $display = '<!--stadvcache:'.$module->name.':'.$hook_name.'['.self::prepareParams($params).']-->'.$display.'<!--stadvcache:'.$module->name.':'.$hook_name.'-->';
        }
        return $display;
    }
    public static function prepareParams($params)
    {
        $str = '';
        if (!empty($params)) {
            foreach($params as $key => $val) {
                if (in_array($key, array('altern', 'cookie','cart'))) {
                    continue;
                }
                if ($key == 'product') {
                    if (is_object($val)) {
                        $str .= 'ip_o='.$val->id;
                    } else {
                        $str .= 'ip='.$val['id_product'];
                    }
                }elseif ($key == 'category') {
                    if (is_object($val)) {
                        $str .= 'ic_o='.$val->id;
                    } else {
                        $str .= 'ic='.$val['id_category'];
                    }
                } elseif (is_int($val) || is_bool($val)) {
                    $str .= $key . '=' . (int)$val;
                } elseif (is_string($val)) {
                    $str .= $key . '=' . urlencode($val);
                }
                $str && $str .= '*';
            }
        }
        return rtrim($str, '*');
    }
}

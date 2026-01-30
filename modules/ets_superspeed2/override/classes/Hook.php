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

if (!defined('_PS_VERSION_')) { exit; }
class Hook extends HookCore
{
    public static function getMethodName(string $hookName): string
    {
        return 'hook' . ucfirst($hookName);
    }
    public static function callHookOn_parent(Module $module, string $hookName, array $hookArgs)
    {
        try {
            // Note: we need to make sure to call the exact hook name first.
            // This especially important when the module uses __call() to process the right hook.
            // Since is_callable() will always return true when __call() is available,
            // if the module was expecting an aliased hook name to be invoked, but we send
            // the canonical hook name instead, the hook will never be acknowledged by the module.
            $methodName = self::getMethodName($hookName);
            if (is_callable([$module, $methodName])) {
                return static::coreCallHook($module, $methodName, $hookArgs);
            }

            // fall back to all other names
            foreach (static::getAllKnownNames($hookName) as $hook) {
                $methodName = self::getMethodName($hook);
                if (is_callable([$module, $methodName])) {
                    return static::coreCallHook($module, $methodName, $hookArgs);
                }
            }
        } catch (Exception $e) {
            if($e)
                return true;
        }

        return '';
    }
    public static function getDynamicHook($module,$hookName,$hookArgs)
    {
        if(!Ets_superspeed_cache_page::isAjax())
        {
            if(!Ets_superspeed_cache_page::checkPageNoCache())
            {
                $dynamicHook = Ets_superspeed_cache_page::getDynamicHookModule($module->id,$hookName);
                if(Tools::strtolower($hookName)=='cetemplate')
                {
                    $hid = Configuration::get('posthemeoptionsheader_template') ? : 7;
                    if(isset($hookArgs['id']) && ($idCreative = $hookArgs['id']) && $idCreative==$hid)
                    {
                        $dynamicHook = array(
                            'empty_content' => 0,
                        );
                    }
                }
                if($dynamicHook) {
                    $params = array();
                    if(isset($hookArgs['id_product']) && $hookArgs['id_product'])
                        $params['id_product'] = $hookArgs['id_product'];
                    elseif(isset($hookArgs['product']) && is_array($hookArgs['product']) && isset($hookArgs['product']['id_product']) && $hookArgs['product']['id_product'])
                        $params['id_product'] = $hookArgs['product']['id_product'];
                    elseif(isset($hookArgs['product']) && is_object($hookArgs['product']) && isset($hookArgs['product']->id) && $hookArgs['product']->id)
                        $params['id_product'] = $hookArgs['product']->id;
                    if(isset($hookArgs['id_product_attribute']) && $hookArgs['id_product_attribute'])
                        $params['id_product_attribute'] = $hookArgs['id_product_attribute'];
                    elseif(isset($hookArgs['product']) && is_array($hookArgs['product']) && isset($hookArgs['product']['id_product_attribute']) && $hookArgs['product']['id_product_attribute'])
                        $params['id_product_attribute'] = $hookArgs['product']['id_product_attribute'];
                    if(isset($hookArgs['type']) && $hookArgs['type'])
                        $params['type'] = $hookArgs['type'];
                    if(isset($hookArgs['tpl']) && $hookArgs['tpl'])
                        $params['tpl'] = $hookArgs['tpl'];
                    return [
                        'html_before' =>'<div id="ets_speed_dy_'.$module->id.$hookName.(isset($params['id_product']) ? '_'.$params['id_product']:'').(isset($params['id_product_attribute']) ? '_'.$params['id_product_attribute']:'').(isset($params['type']) ? '_'.$params['type']:'').'" data-moudule="'.$module->id.'" data-module-name="'.$module->name.'" data-hook="'.$hookName.'" data-params=\''.json_encode($params).'\' class="ets_speed_dynamic_hook" '.(isset($idCreative) ? ' data-idCreative="'.(int)$idCreative.'"':'').'>',
                        'empty_content' => $dynamicHook['empty_content'],
                    ];
                }
            }
        }
        return false;
    }
    public static function callHookOn(Module $module, string $hookName, array $hookArgs)
    {
        require_once(dirname(__FILE__).'/../../modules/ets_superspeed/ets_superspeed.php');
        $content =self::callHookOn_parent($module,$hookName,$hookArgs);
        if(is_array($content) || is_object($content)  || Tools::strtolower($hookName)=='header' || Tools::strtolower($hookName)=='displayheader' || !Module::isEnabled('ets_superspeed'))
            return $content;
        $html ='';
        $time_start = microtime(true);
        $dynamicHook = self::getDynamicHook($module,$hookName,$hookArgs);
        if($dynamicHook)
            $html .= $dynamicHook['html_before'];
        if(!$dynamicHook || ($dynamicHook && !$dynamicHook['empty_content'])) {
            $html .= $content;
        }
        if($dynamicHook)
        {
            $html .='</div>';
        }
        if(Configuration::get('ETS_SPEED_RECORD_MODULE_PERFORMANCE'))
        {
            $time_end = microtime(true);
            $time= $time_end-$time_start;
            if(Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'ets_superspeed_hook_time` WHERE id_module="'.(int)$module->id.'" AND hook_name="'.pSQL($hookName).'" AND id_shop='.(int)Context::getContext()->shop->id))
            {
                Db:: getInstance()->execute('UPDATE `'._DB_PREFIX_.'ets_superspeed_hook_time` SET page="'.pSQL($_SERVER['REQUEST_URI']).'",time="'.(float)$time.'",date_add ="'.pSQL(date('Y-m-d H:i:s')).'" WHERE id_module="'.(int)$module->id.'" AND hook_name="'.pSQL($hookName).'" AND id_shop='.(int)Context::getContext()->shop->id);
            }
            else
            {
                Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'ets_superspeed_hook_time`(id_module,hook_name,page,time,date_add,id_shop) VALUES("'.(int)$module->id.'","'.pSQL($hookName).'","'.pSQL($_SERVER['REQUEST_URI']).'","'.(float)$time.'","'.pSQL(date('Y-m-d H:i:s')).'","'.(int)Context::getContext()->shop->id.'")');
            }
        }
        return $html;
    }
    public static function coreRenderWidget($module, $hookName, $hookArgs)
    {
        $content = parent::coreRenderWidget($module,$hookName,$hookArgs);
        if(is_array($content) || is_object($content) || Tools::strtolower($hookName)=='header' || Tools::strtolower($hookName)=='displayheader' || !Module::isEnabled('ets_superspeed'))
            return $content;
        if(Tools::strtolower($hookName)=='header' || Tools::strtolower($hookName)=='displayheader' || !Module::isEnabled('ets_superspeed'))
            return $content;
        $html ='';
        $time_start = microtime(true);
        $dynamicHook = self::getDynamicHook($module,$hookName,$hookArgs);
        if($dynamicHook)
            $html .= $dynamicHook['html_before'];
        if(!$dynamicHook || ($dynamicHook && !$dynamicHook['empty_content'])) {
            $html .= $content;
        }
        if($dynamicHook)
        {
            $html .='</div>';
        }
        if(Configuration::get('ETS_SPEED_RECORD_MODULE_PERFORMANCE'))
        {
            $time_end = microtime(true);
            $time= $time_end-$time_start;
            if(Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.'ets_superspeed_hook_time` WHERE id_module="'.(int)$module->id.'" AND hook_name="'.pSQL($hookName).'" AND id_shop='.(int)Context::getContext()->shop->id))
            {
                Db:: getInstance()->execute('UPDATE `'._DB_PREFIX_.'ets_superspeed_hook_time` SET page="'.pSQL($_SERVER['REQUEST_URI']).'",time="'.(float)$time.'",date_add ="'.pSQL(date('Y-m-d H:i:s')).'" WHERE id_module="'.(int)$module->id.'" AND hook_name="'.pSQL($hookName).'" AND id_shop='.(int)Context::getContext()->shop->id);
            }
            else
            {
                Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'ets_superspeed_hook_time`(id_module,hook_name,page,time,date_add,id_shop) VALUES("'.(int)$module->id.'","'.pSQL($hookName).'","'.pSQL($_SERVER['REQUEST_URI']).'","'.(float)$time.'","'.pSQL(date('Y-m-d H:i:s')).'","'.(int)Context::getContext()->shop->id.'")');
            }
        }
        return $html;
    }
}
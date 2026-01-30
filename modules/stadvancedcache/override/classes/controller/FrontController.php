<?php
/**
* 2007-2018 PrestaShop.
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
*  @copyright 2007-2018 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class FrontController extends FrontControllerCore
{
    public $start_time;

    public function run()
    {
        $this->start_time = microtime(true);
        if (!Module::isInstalled('stadvancedcache') || !Module::isEnabled('stadvancedcache')) {
           parent::run();
        } else {
            $instance = Module::getInstanceByName('stadvancedcache');
            if ($instance->cacheAble()) {
                $this->init();
                if ($content = $instance->getCache($this)) {
                    if (!$this->content_only && ($this->display_header || (isset($this->className) && $this->className))) {
                        $this->initHeader();
                    }
                    if (version_compare(_PS_VERSION_, '1.7.0.0', '>')) {
                        $this->assignGeneralPurposeVariables();
                    }
                    // For the Faceted search and Easy filter module.
                    if($this instanceof ProductListingFrontController){
                        if (Module::isEnabled('ps_facetedsearch') && Dispatcher::getInstance()->getController() == 'category') {
                            $this->initContent();    
                        }
                        if (Module::isEnabled('stfacetedsearch')) {
                            $this->initContent();
                        }
                    }
                    $instance->execDynamic($content, $this);
                    $this->context->cookie->write();
                    echo $content;
                    die;
                }
            }
            parent::run();  
        }
    }

    protected function smartyOutputContent($content)
    {
        if (!Module::isInstalled('stadvancedcache') || !Module::isEnabled('stadvancedcache')) {
            parent::smartyOutputContent($content);
        } else {
            $instance = Module::getInstanceByName('stadvancedcache');
            if ($instance->cacheAble()) {
                $html = '';
                if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
                    $js_tag = 'js_def';
                    $this->context->smarty->assign($js_tag, $js_tag);

                    if (is_array($content)) {
                        foreach ($content as $tpl) {
                            $html .= $this->context->smarty->fetch($tpl);
                        }
                    } else {
                        $html = $this->context->smarty->fetch($content);
                    }

                    $html = trim($html);

                    if (in_array($this->controller_type, array('front', 'modulefront')) && !empty($html) && $this->getLayout()) {
                        $live_edit_content = '';
                        if (!$this->useMobileTheme() && $this->checkLiveEditAccess()) {
                            $live_edit_content = $this->getLiveEditFooter();
                        }

                        $dom_available = extension_loaded('dom') ? true : false;
                        $defer = (bool)Configuration::get('PS_JS_DEFER');

                        if ($defer && $dom_available) {
                            $html = Media::deferInlineScripts($html);
                        }
                        $html = trim(str_replace(array('</body>', '</html>'), '', $html))."\n";

                        $this->context->smarty->assign(array(
                            $js_tag => Media::getJsDef(),
                            'js_files' =>  $defer ? array_unique($this->js_files) : array(),
                            'js_inline' => ($defer && $dom_available) ? Media::getInlineScript() : array()
                        ));

                        $javascript = $this->context->smarty->fetch(_PS_ALL_THEMES_DIR_.'javascript.tpl');

                        if ($defer && (!isset($this->ajax) || ! $this->ajax)) {
                            $html .= $javascript;
                        } else {
                            $html = preg_replace('/(?<!\$)'.$js_tag.'/', $javascript, $html);
                        }
                        $html .= $live_edit_content.((!isset($this->ajax) || ! $this->ajax) ? '</body></html>' : '');
                    }
                } else {
                    if (is_array($content)) {
                        foreach ($content as $tpl) {
                            $html .= $this->context->smarty->fetch($tpl, null, $this->getLayout());
                        }
                    } else {
                        $html = $this->context->smarty->fetch($content, null, $this->getLayout());
                    }
                    Hook::exec('actionOutputHTMLBefore', array('html' => &$html));
                }
                $template = trim($html);
                if ($instance->setCache($template, $this)) {
                    $this->context->cookie->write();
                    echo $template;
                    die;
                }
            }
            parent::smartyOutputContent($content);
        }
    }

    public function getRestrictedCountry()
    {
        return $this->restrictedCountry;
    }
}

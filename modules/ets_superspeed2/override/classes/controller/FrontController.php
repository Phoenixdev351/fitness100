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
class FrontController extends FrontControllerCore
{
    public function initContent()
    {
        if(Tools::isSubmit('ets_superseed_load_content') && Module::isEnabled('ets_superspeed'))
        {
            parent::initContent();
            Hook::exec('actionPageCacheAjax');
        }
        parent::initContent();
    }
    protected function smartyOutputContent($content)
    {
        if (version_compare(_PS_VERSION_, '1.7.0', '<')) {
            ob_start();
            parent::smartyOutputContent($content);
            $html = ob_get_contents();
            ob_clean();
            Hook::exec('actionOutputHTMLBefore',  array('html' => &$html));
            echo $html;
        } else
            return parent::smartyOutputContent($content);
    }
}
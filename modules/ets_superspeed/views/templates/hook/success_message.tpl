{*
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
*}
<div class="bootstrap bootstrap_sussec">
    <div class="module_confirmation conf confirm alert alert-success">
    	<button type="button" class="close" data-dismiss="alert">&times;</button>
        {if $msg}{$msg|escape:'html':'UTF-8'}{/if}
        {if $title && $link} | <a href="{$link|escape:'html':'UTF-8'}" target="_blank"><b>{$title|escape:'html':'UTF-8'}</b></a>{/if}
    </div>
</div>
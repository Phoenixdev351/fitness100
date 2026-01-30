{*
* 2007-2019 ETS-Soft ETS-Soft
*
* NOTICE OF LICENSE
*
* This file is not open source! Each license that you purchased is only available for 1 wesite only.
* If you want to use this file on more websites (or projects), you need to purchase additional licenses.
* You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please, contact us for extra customization service at an affordable price
*
*  @author ETS-Soft <etssoft.jsc@gmail.com>
*  @copyright  2007-2019 ETS-Soft ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}
<ul>
    <li{if $controller=='AdminDataMasterGeneral'} class="active"{/if}><a href="{$link->getAdminLink('AdminDataMasterGeneral',true)|escape:'html':'UTF-8'}"><i class="icon icon-database"> </i> {l s='Dashboard' mod='ets_oneclicktomigrate'}</a></li>
    <li{if $controller=='AdminDataMasterExport'} class="active"{/if}><a href="{$link->getAdminLink('AdminDataMasterExport',true)|escape:'html':'UTF-8'}"><i class="icon icon-download"> </i> {l s='Export data' mod='ets_oneclicktomigrate'}</a></li>
    <li{if $controller=='AdminDataMasterImport'} class="active"{/if}><a href="{$link->getAdminLink('AdminDataMasterImport',true)|escape:'html':'UTF-8'}"><i class="icon icon-cloud-upload"> </i> {l s='Import data' mod='ets_oneclicktomigrate'}</a></li>
    <li{if $controller=='AdminDataMasterHistory'} class="active"{/if}><a href="{$link->getAdminLink('AdminDataMasterHistory',true)|escape:'html':'UTF-8'}"><i class="icon icon-history"> </i> {l s='History' mod='ets_oneclicktomigrate'}</a></li>
    <li{if $controller=='AdminDataMasterClean'} class="active"{/if}><a href="{$link->getAdminLink('AdminDataMasterClean',true)|escape:'html':'UTF-8'}"><i class="icon icon-eraser"> </i> {l s='Clean-up' mod='ets_oneclicktomigrate'}</a></li>
    <li{if $controller=='AdminDataMasterHelp'} class="active"{/if}><a href="{$link->getAdminLink('AdminDataMasterHelp',true)|escape:'html':'UTF-8'}"><i class="icon icon-question-circle"> </i> {l s='Help' mod='ets_oneclicktomigrate'}</a></li>
    {if isset($intro) && $intro}
        <li class="li_othermodules ">
            <a class="link_othermodules" href="{$other_modules_link|escape:'html':'UTF-8'}">
                <span class="tab-title">{l s='Other modules' mod='ets_oneclicktomigrate'}</span>
                <span class="tab-sub-title">{l s='Made by ETS-Soft' mod='ets_oneclicktomigrate'}</span>
            </a>
        </li>
    {/if}
</ul>
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

<script type="text/javascript" src="{$dir_path|escape:'quotes':'UTF-8'}views/js/jquery14.js"></script>
<script type="text/javascript" src="{$dir_path|escape:'quotes':'UTF-8'}views/js/datamaster.admin.js"></script>
<script type="text/javascript" src="{$dir_path|escape:'quotes':'UTF-8'}views/js/easytimer.min.js"></script>
<link type="text/css" rel="stylesheet" href="{$dir_path|escape:'quotes':'UTF-8'}views/css/datamaster.admin.css" />
<link type="text/css" rel="stylesheet" href="{$dir_path|escape:'quotes':'UTF-8'}views/css/font-awesome.css" />
<link type="text/css" rel="stylesheet" href="{$dir_path|escape:'quotes':'UTF-8'}views/css/fic14.css" />
<script type="text/javascript" src="{$dir_path|escape:'quotes':'UTF-8'}views/js/jquery-admin.js"></script>
<link type="text/css" rel="stylesheet" href="{$dir_path|escape:'quotes':'UTF-8'}views/css/jquery-admin.css" />
<div class="dtm-left-block">
    <ul>
        <li{if $tabmodule=='general' || !$tabmodule} class="active"{/if}><a href="index.php?tab=AdminModules&configure=ets_oneclicktomigrate&token={$token|escape:'html':'UTF-8'}&tab_module=front_office_features&module_name=ets_oneclicktomigrate&tabmodule=general"><i class="fa fa-database"> </i> {l s='Dashboard' mod='ets_oneclicktomigrate'}</a></li>
        <li{if $tabmodule=='export'} class="active"{/if}><a href="index.php?tab=AdminModules&configure=ets_oneclicktomigrate&token={$token|escape:'html':'UTF-8'}&tab_module=front_office_features&module_name=ets_oneclicktomigrate&tabmodule=export"><i class="fa fa-download"> </i> {l s='Export data' mod='ets_oneclicktomigrate'}</a></li>
        <li{if $tabmodule=='import'} class="active"{/if}><a href="index.php?tab=AdminModules&configure=ets_oneclicktomigrate&token={$token|escape:'html':'UTF-8'}&tab_module=front_office_features&module_name=ets_oneclicktomigrate&tabmodule=import"><i class="fa fa-cloud-upload"> </i> {l s='Import data' mod='ets_oneclicktomigrate'}</a></li>
        <li{if $tabmodule=='history'} class="active"{/if}><a href="index.php?tab=AdminModules&configure=ets_oneclicktomigrate&token={$token|escape:'html':'UTF-8'}&tab_module=front_office_features&module_name=ets_oneclicktomigrate&tabmodule=history"><i class="fa fa-history"> </i> {l s='History' mod='ets_oneclicktomigrate'}</a></li>
        <li{if $tabmodule=='clear_up'} class="active"{/if}><a href="index.php?tab=AdminModules&configure=ets_oneclicktomigrate&token={$token|escape:'html':'UTF-8'}&tab_module=front_office_features&module_name=ets_oneclicktomigrate&tabmodule=clear_up"><i class="fa fa-eraser"> </i> {l s='Clean-up' mod='ets_oneclicktomigrate'}</a></li>
        <li{if $tabmodule=='help'} class="active"{/if}><a href="index.php?tab=AdminModules&configure=ets_oneclicktomigrate&token={$token|escape:'html':'UTF-8'}&tab_module=front_office_features&module_name=ets_oneclicktomigrate&tabmodule=help"><i class="fa fa-question-circle"> </i> {l s='Help' mod='ets_oneclicktomigrate'}</a></li>
        {if isset($intro) && $intro}
            <li class="li_othermodules ">
                <a class="link_othermodules" href="{$other_modules_link|escape:'html':'UTF-8'}">
                    <span class="tab-title">{l s='Other modules' mod='ets_oneclicktomigrate'}</span>
                    <span class="tab-sub-title">{l s='Made by ETS-Soft' mod='ets_oneclicktomigrate'}</span>
                </a>
            </li>
        {/if}
    </ul>
</div>
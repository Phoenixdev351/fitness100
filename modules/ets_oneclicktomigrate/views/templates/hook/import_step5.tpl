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
<p class="alert alert-success">{l s='Data imported successfully. See import result below.' mod='ets_oneclicktomigrate'}</p>
<p class="alert alert-warning">
    {l s='Please clear your Prestashop cache and reindex Prestashop search and everything is done.' mod='ets_oneclicktomigrate'}
</p>
<div class="import_title_step5">{l s='IMPORT RESULT:' mod='ets_oneclicktomigrate'}</div>
<ul class="list-data-to-imported">
    {if $ets_datamaster_import}
        {foreach from=$ets_datamaster_import item='data_import'}
            <li>
                {$assign[$data_import]|escape:'html':'UTF-8'}
                {if $data_import=='employees'}
                    {l s='employees' mod='ets_oneclicktomigrate'}
                {/if}
                {if $data_import=='categories'}
                    {l s='product category' mod='ets_oneclicktomigrate'}
                {/if}
                {if $data_import=='manufactures'}
                    {l s='manufacturers' mod='ets_oneclicktomigrate'}
                {/if}
                {if $data_import=='suppliers'}
                    {l s='suppliers' mod='ets_oneclicktomigrate'}
                {/if}
                {if $data_import=='products'}
                    {l s='products' mod='ets_oneclicktomigrate'}
                {/if}
                {if $data_import=='customers'}
                    {l s='customers' mod='ets_oneclicktomigrate'}
                {/if}
                {if $data_import=='carriers'}
                    {l s='carriers' mod='ets_oneclicktomigrate'}
                {/if}
                {if $data_import=='cart_rules'}
                    {l s='cart rules' mod='ets_oneclicktomigrate'}
                {/if}
                {if $data_import=='catelog_rules'}
                    {l s='catalog rules' mod='ets_oneclicktomigrate'}
                {/if}
                {if $data_import=='orders'}
                    {l s='orders' mod='ets_oneclicktomigrate'}
                {/if}
                {if $data_import=='CMS_categories'}
                    {l s='CMS categories' mod='ets_oneclicktomigrate'}
                {/if}
                {if $data_import=='CMS'}
                    {l s='CMSs' mod='ets_oneclicktomigrate'}
                {/if}
                {if $data_import=='messages'}
                    {l s='contact form messages' mod='ets_oneclicktomigrate'}
                {/if}
                {if $data_import=='vouchers'}
                    {l s='vouchers' mod='ets_oneclicktomigrate'}
                {/if}
                {if $data_import=='shops'}
                    {l s='shops' mod='ets_oneclicktomigrate'}
                {/if}
                {l s='imported' mod='ets_oneclicktomigrate'}
                {if $data_import=='customers'}
                    {if $pres_version==1.4}
                        {if $new_passwd_customer}
                            <a href="index.php?tab=AdminModules&configure=ets_oneclicktomigrate&token={$token|escape:'html':'UTF-8'}&tab_module=front_office_features&module_name=ets_oneclicktomigrate&tabmodule=history&downloadpasscustomer&id_import_history={$id_import_history|intval}"><i class="fa fa-cloud-download"> </i> {l s='Download customer password' mod='ets_oneclicktomigrate'}</a>
                        {/if}
                    {else}
                        {if $new_passwd_customer}
                            <a href="{$link_history|escape:'html':'UTF-8'}&downloadpasscustomer&id_import_history={$id_import_history|intval}"><i class="icon icon-cloud-download"> </i>  {l s='Download customer passwords' mod='ets_oneclicktomigrate'}</a>
                        {/if}
                    {/if}
                {/if}
                {if $data_import=='employees'}
                    {if $pres_version==1.4}
                        {if $new_passwd_employee}
                            <a href="index.php?tab=AdminModules&configure=ets_oneclicktomigrate&token={$token|escape:'html':'UTF-8'}&tab_module=front_office_features&module_name=ets_oneclicktomigrate&tabmodule=history&downloadpassemployee&id_import_history={$id_import_history|intval}"><i class="fa fa-cloud-download"> </i> {l s='Download employee password' mod='ets_oneclicktomigrate'}</a>
                        {/if}
                    {else}
                        {if $new_passwd_employee}
                            <a href="{$link_history|escape:'html':'UTF-8'}&downloadpassemployee&id_import_history={$id_import_history|intval}"><i class="icon icon-cloud-download"> </i> {l s='Download employee passwords' mod='ets_oneclicktomigrate'}</a>
                        {/if}
                    {/if}
                {/if}
            </li>
        {/foreach}
    {/if}
</ul>
{if !Configuration::get('ETS_DATAMASTER_NEW_PASSWD') && (in_array('customers',$ets_datamaster_import) || in_array('employees',$ets_datamaster_import))}
    <p class="link_download_plugin alert alert-info">
        <a href="{$mod_dr_onclickmigrate|escape:'html':'UTF-8'}plugins/ets_pres2prespwkeeper.zip" target="_blank"><b>{l s='Download Prestashop Password Keeper module' mod='ets_oneclicktomigrate'}</b></a>&nbsp;{l s='and install the module on this website to recover customer passwords from Source website' mod='ets_oneclicktomigrate'}
            <br /><span class="cookie_key" >_COOKIE_KEY_ {l s='of source website' mod='ets_oneclicktomigrate'}: <span>{$OLD_COOKIE_KEY|escape:'html':'UTF-8'}</span>
    </p>
{/if}
<p class="list-data-to-imported-history">
    <span class="required">*</span>{l s='See more details of the import in' mod='ets_oneclicktomigrate'}
    {if $pres_version==1.4}
        <a class="imported-history-link" href="index.php?tab=AdminModules&configure=ets_oneclicktomigrate&token={$token|escape:'html':'UTF-8'}&tab_module=front_office_features&module_name=ets_oneclicktomigrate&tabmodule=history">{l s='import history' mod='ets_oneclicktomigrate'}</a>
    {else}
        <a class="imported-history-link" href="{$link_history|escape:'html':'UTF-8'}">{l s='import history' mod='ets_oneclicktomigrate'}</a>
    {/if}
</p>
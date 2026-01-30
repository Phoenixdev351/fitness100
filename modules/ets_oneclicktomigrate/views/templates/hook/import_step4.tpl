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
<p>{l s='Please review and confirm your import before processing it!' mod='ets_oneclicktomigrate'}</p>
<div class="data-to-export">
    <div>{l s='Data to import:' mod='ets_oneclicktomigrate'}</div>
    <ul class="list-data-to-import">
        {if $ets_datamaster_import}
            {foreach from=$ets_datamaster_import item='data_import'}
                <li>
                    {if $data_import=='employees'}
                        {l s='Employees' mod='ets_oneclicktomigrate'}
                    {/if}
                    {if $data_import=='categories'}
                        {l s='Product categories' mod='ets_oneclicktomigrate'}
                    {/if}
                    {if $data_import=='manufactures'}
                        {l s='Manufacturers' mod='ets_oneclicktomigrate'}
                    {/if}
                    {if $data_import=='suppliers'}
                        {l s='Suppliers' mod='ets_oneclicktomigrate'}
                    {/if}
                    {if $data_import=='products'}
                        {l s='Products' mod='ets_oneclicktomigrate'}
                    {/if}
                    {if $data_import=='customers'}
                        {l s='Customers' mod='ets_oneclicktomigrate'}
                    {/if}
                    {if $data_import=='carriers'}
                        {l s='Carriers' mod='ets_oneclicktomigrate'}
                    {/if}
                    {if $data_import=='cart_rules'}
                        {l s='Cart rules' mod='ets_oneclicktomigrate'}
                    {/if}
                    {if $data_import=='catelog_rules'}
                        {l s='Catalog rules' mod='ets_oneclicktomigrate'}
                    {/if}
                    {if $data_import=='orders'}
                        {l s='Orders' mod='ets_oneclicktomigrate'}
                    {/if}
                    {if $data_import=='CMS_categories'}
                        {l s='CMS categories' mod='ets_oneclicktomigrate'}
                    {/if}
                    {if $data_import=='CMS'}
                        {l s='CMSs' mod='ets_oneclicktomigrate'}
                    {/if}
                    {if $data_import=='messages'}
                        {l s='Contact form messages' mod='ets_oneclicktomigrate'}
                    {/if}
                    {if $data_import=='vouchers'}
                        {l s='Vouchers' mod='ets_oneclicktomigrate'}
                    {/if}
                    {if $data_import=='shops'}
                        {l s='Shops' mod='ets_oneclicktomigrate'}
                    {/if}
                ({$assign[$data_import]|escape:'html':'UTF-8'} {if $assign[$data_import]<=1}{l s='item' mod='ets_oneclicktomigrate'}{else}{l s='items' mod='ets_oneclicktomigrate'}{/if})
                </li>
            {/foreach}
        {/if}
    </ul>
</div>
<div class="data-format-to-import">
    <div>{l s='Formatting:' mod='ets_oneclicktomigrate'}</div>
    <ul>
        <li>
            <span>{l s='Delete data before importing:' mod='ets_oneclicktomigrate'}&nbsp;{if $ets_datamaster_import_delete}{l s='YES' mod='ets_oneclicktomigrate'}{else}{l s='NO' mod='ets_oneclicktomigrate'}{/if}</span>
        </li>
        <li>
            <span>{l s='Force all ID numbers:' mod='ets_oneclicktomigrate'}&nbsp;{if $ets_datamaster_import_force_all_id}{l s='YES' mod='ets_oneclicktomigrate'}{else}{l s='NO' mod='ets_oneclicktomigrate'}{/if}</span>
        </li>
        <li>
            <span>{l s='Keep customer passwords:' mod='ets_oneclicktomigrate'}&nbsp;{if $ets_regenerate_customer_passwords}{l s='NO' mod='ets_oneclicktomigrate'}{else}{l s='YES' mod='ets_oneclicktomigrate'}{/if}</span>
        </li>
    </ul>
</div>
<div class="data-format-to-import">
    <div>{l s='Source website information:' mod='ets_oneclicktomigrate'}</div>
    <ul>
        <li>
            <span>{l s='Site URL: ' mod='ets_oneclicktomigrate'}
            {if count($link_sites)>1}
                {foreach from=$link_sites key='key' item='link_site'}
                    <p>{l s='Shop' mod='ets_oneclicktomigrate'}{$key+1|intval}: &nbsp;<a target="_blank" href="{$link_site|escape:'html':'UTF-8'}">{$link_site|escape:'html':'UTF-8'}</a></p>
                {/foreach}
            {else}
                <a target="_blank" href="{$link_sites[0]|escape:'html':'UTF-8'}">{$link_sites[0]|escape:'html':'UTF-8'}</a>
            {/if}
            </span>
        </li>
        <li>
            <span>{l s='Platform: ' mod='ets_oneclicktomigrate'}{$platform|escape:'html':'UTF-8'}</span>
        </li>
        <li>
            <span>{l s='Version: ' mod='ets_oneclicktomigrate'}{$vertion|escape:'html':'UTF-8'}</span>
        </li>
    </ul>
</div>
<div class="alert alert-warning">
    {l s='You are going to make big changes to website database and images.' mod='ets_oneclicktomigrate'}
    {l s='Make sure you have a complete backup of your website (both files and database)' mod='ets_oneclicktomigrate'}
</div>
<div class="form-group">
    <div class="checkbox col-xs-12">
        <label for="have_made_backup" class="one-line">
            <input id="have_made_backup" name="have_made_backup" type="checkbox"/><span class="data_checkbox_style"><i class="icon icon-check"></i></span> {l s='I have made a complete backup of this website' mod='ets_oneclicktomigrate'}
        </label>
    </div>
</div>

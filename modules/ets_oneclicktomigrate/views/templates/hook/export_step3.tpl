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
<p>{l s='Please review and confirm your export before processing it!' mod='ets_oneclicktomigrate'}</p>
<div class="data-to-export">
    <div>{l s='Data to export:' mod='ets_oneclicktomigrate'}</div>
    <ul class="list-data-to-export">
        {if $ets_datamaster_export}
            {foreach from=$ets_datamaster_export item='data_export'}
                <li>
                    {if $data_export=='employees'}
                        {l s='Employees' mod='ets_oneclicktomigrate'}
                    {/if}
                    {if $data_export=='categories'}
                        {l s='Product categories' mod='ets_oneclicktomigrate'}
                    {/if}
                    {if $data_export=='manufactures'}
                        {l s='Manufacturers' mod='ets_oneclicktomigrate'}
                    {/if}
                    {if $data_export=='suppliers'}
                        {l s='Suppliers' mod='ets_oneclicktomigrate'}
                    {/if}
                    {if $data_export=='products'}
                        {l s='Products' mod='ets_oneclicktomigrate'}
                    {/if}
                    {if $data_export=='customers'}
                        {l s='Customers & addresses' mod='ets_oneclicktomigrate'}
                    {/if}
                    {if $data_export=='carriers'}
                        {l s='Carriers & shipping prices' mod='ets_oneclicktomigrate'}
                    {/if}
                    {if $data_export=='cart_rules'}
                        {l s='Cart rules' mod='ets_oneclicktomigrate'}
                    {/if}
                    {if $data_export=='catelog_rules'}
                        {l s='Catalog rules' mod='ets_oneclicktomigrate'}
                    {/if}
                    {if $data_export=='orders'}
                        {l s='Orders & shopping carts' mod='ets_oneclicktomigrate'}
                    {/if}
                    {if $data_export=='CMS_categories'}
                        {l s='CMS categories' mod='ets_oneclicktomigrate'}
                    {/if}
                    {if $data_export=='CMS'}
                        {l s='CMSs' mod='ets_oneclicktomigrate'}
                    {/if}
                    {if $data_export=='messages'}
                        {l s='Contact form messages' mod='ets_oneclicktomigrate'}
                    {/if}
                    {if $data_export=='vouchers'}
                        {l s='Vouchers' mod='ets_oneclicktomigrate'}
                    {/if}
                    {if $data_export=='shops'}
                        {l s='Shops' mod='ets_oneclicktomigrate'}
                    {/if}
                 ({$totalDatas[$data_export]|intval} {if $totalDatas[$data_export]<=1}{l s='Item' mod='ets_oneclicktomigrate'}{else}{l s='Items' mod='ets_oneclicktomigrate'}{/if})
                 </li>
            {/foreach}
        {/if}
    </ul>
</div>
<div class="data-format-to-export" style="display:none;">
    <div>{l s='Formatting:' mod='ets_oneclicktomigrate'}</div>
    <ul>
        <li>
            <span>{l s='Data format:' mod='ets_oneclicktomigrate'}&nbsp;{$ets_datamaster_format|escape:'html':'UTF-8'}</span>
        </li>
    </ul>
</div>
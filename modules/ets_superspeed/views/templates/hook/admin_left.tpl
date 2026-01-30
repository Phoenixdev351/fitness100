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
<script type="text/javascript">
    var link_ajax_submit = "{$link_ajax_submit nofilter}";
    var total_images=  {$total_images|intval};
    var image_loading_text = '{l s='Uploading' mod='ets_superspeed' js='1'}';
    var image_waiting_text = '{l s='Waiting' mod='ets_superspeed' js='1'}';
    var image_compressing_text ='{l s='Compressing' mod='ets_superspeed' js='1'}';
    var image_finished_text = '{l s='Optimized' mod='ets_superspeed' js='1'}';
    var no_image_unused = '{l s='Congratulations! Your website is good here. No unused images found. Nothing to do.' mod='ets_superspeed' js='1'}';
    var download_text = '{l s='Download' mod='ets_superspeed' js='1'}';
    var delete_text ='{l s='Delete' mod='ets_superspeed' js='1'}';
    var cancel_text ='{l s='Cancel' mod='ets_superspeed' js='1'}';
    var save_text ='{l s='Save' mod='ets_superspeed' js='1'}';
    var restore_text='{l s='Restore' mod='ets_superspeed' js='1'}';
    var comfirm_all_image = '{l s='Do you want to optimize all selected images?' mod='ets_superspeed' js='1'}';
    var deleted_successfully ='{l s='Deleted successfully' mod='ets_superspeed' js='1'}';
    var confirm_delete_unused_images = '{l s='Please confirm that you want to clean all unused images?' mod='ets_superspeed' js='1'}';
    var confirm_delete_all_system_analytics = '{l s='Please confirm that you want to clean all analytics?' mod='ets_superspeed' js='1'}';
    var no_data_text = '{l s='No data available' mod='ets_superspeed' js='1'}';
    var link_logo = '{$link_logo nofilter}';
</script>
<script type="text/javascript" src="{$ets_sp_module_dir|escape:'html':'UTF-8'}views/js/admin.js"></script>
<link href="https://fonts.googleapis.com/css?family=Roboto:400,500,700" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600" rel="stylesheet" />
<ul class="sp_tabs">
    {assign var='i' value=0}
    {foreach from=$left_tabs item='tab'}
        {assign var='i' value=$i + 1}
        <li class="{if $tab.class_name==$control || (isset($tab.sub_menu) && isset($tab.sub_menu[$control]))}active{/if} {if isset($tab.custom_li_class)}{$tab.custom_li_class|escape:'html':'UTF-8'}{/if}{if isset($tab.sub_menu)} sp_has-sub{/if}">
            <a {if isset($tab.custom_a_class)}class="{$tab.custom_a_class|escape:'html':'UTF-8'}"{/if} href="{if !isset($tab.other_modules_link)}{$link->getAdminLink($tab.class_name,true) nofilter}{else}{$tab.other_modules_link nofilter}{/if}" {if isset($tab.refsLink) && $tab.refsLink}target="_blank" {/if}>
                {if isset($tab.icon)}
                    <img src="{$ets_sp_module_dir|escape:'html':'UTF-8'}views/img/{$tab.logo|escape:'html':'UTF-8'}" />
                {/if}
                <span class="tab-title">{$tab.tab_name|escape:'html':'UTF-8'}</span>
                {if isset($tab.subtitle)}
                    <span class="tab-sub-title">{$tab.subtitle|escape:'html':'UTF-8'}</span>
                {/if}
            </a>
            {if isset($tab.sub_menu) && $tab.sub_menu}
                <ul class="sub_menu">  
                    {foreach from=$tab.sub_menu item='tab_sub'}
                        <li class="{if $tab_sub.class_name==$control}active{/if} {if isset($tab_sub.custom_li_class)}{$tab_sub.custom_li_class|escape:'html':'UTF-8'}{/if}">
                            <a {if isset($tab_sub.custom_a_class)}class="{$tab_sub.custom_a_class|escape:'html':'UTF-8'}"{/if} href="{if !isset($tab_sub.other_modules_link)}{$link->getAdminLink($tab_sub.class_name,true) nofilter}{else}{$tab_sub.other_modules_link nofilter}{/if}">
                                {if isset($tab_sub.icon)}
                                    <img src="{$ets_sp_module_dir|escape:'html':'UTF-8'}views/img/{$tab_sub.logo|escape:'html':'UTF-8'}" />
                                {/if}
                                <span class="tab-title">{$tab_sub.tab_name|escape:'html':'UTF-8'}</span>
                                {if isset($tab_sub.subtitle)}
                                    <span class="tab-sub-title">{$tab_sub.subtitle|escape:'html':'UTF-8'}</span>
                                {/if}
                            </a>
                        </li>
                    {/foreach}
                </ul>
            {/if}
        </li>
    {/foreach}
</ul>
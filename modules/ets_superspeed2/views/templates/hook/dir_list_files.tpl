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
<ul class="list-browse-images current">
    {if $list_files}
        {assign var='has_file_all' value=false}
        {foreach from=$list_files item='list_file'}
            {if !$has_file_all && $list_file.type=='file'}
                {assign var='has_file_all' value=true}
                <li class="all">
                    <input type="checkbox" value="all"/>
                    <span class="open_close-file">{l s='Select All' mod='ets_superspeed'}</span>
                </li>
            {/if}
            <li class="{$list_file.type|escape:'html':'UTF-8'} folder-hide" id="item-{$list_file.id|escape:'html':'UTF-8'}">
                {if $list_file.type=='file'}
                    <input {if $list_file.uploaed}checked="checked" disabled="disabled"{/if} id="{$list_file.id|escape:'html':'UTF-8'}" type="checkbox" name="browse_images[]" value="{$list_file.dir|escape:'html':'UTF-8'}" data-file_size="{$list_file.file_size|escape:'html':'UTF-8'}"/>
                {/if}
                <span class="{if $list_file.type=='folder'}open-close-folder{else}open_close-file{/if}"{if $list_file.type=='folder'} data-folder="{$list_file.dir|escape:'html':'UTF-8'}"{/if}> {$list_file.name|escape:'html':'UTF-8'}</span>
            </li>
        {/foreach}
    {else} 
        <li class="not-data">{l s='No images found' mod='ets_superspeed'}</li>
    {/if}
</ul>
<script type="text/javascript">
    $(document).ready(function(){
        if($('.list-browse-images.current').find('> li.file input[type="checkbox"]').length==$('.list-browse-images.current').find('> li.file input[type="checkbox"]:disabled').length)
        {
            $('.list-browse-images.current').find('>li.all input[type="checkbox"]').attr('checked','checked');
            $('.list-browse-images.current').find('>li.all input[type="checkbox"]').attr('disabled','disabled');
        }
        $('.list-browse-images.current').removeClass('current');
    });
</script>
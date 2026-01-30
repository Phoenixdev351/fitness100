{*
*  @author    Amazzing <mail@mirindevo.com>
*  @copyright Amazzing
*  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{if $items}
	{foreach $items as $id => $item}
	<div class="merged-row clearfix" data-id="{$id|intval}">
		<form class="merged-form">
		<input type="hidden" name="id_merged" value="{$id|intval}">
		<input type="hidden" name="id_group" value="{$merging_params.id_group|intval}">
		<input type="hidden" name="type" value="{$merging_params.type|escape:'html':'UTF-8'}">
		<div class="col-md-4">
	        <div class="multilang-wrapper">
	            {foreach $available_languages as $id_lang => $iso_code}
	                {if !isset($item.name.$id_lang)}{$item.name.$id_lang = ''}{/if}
	                <div class="multilang lang-{$id_lang|intval}{if $id_lang != $id_lang_current} hidden{/if}">
	                    <input type="text" name="name[{$id_lang|intval}]" value="{$item.name.$id_lang|escape:'html':'UTF-8'}" placeholder="{l s='Merged name' mod='amazzingfilter'}">
	                </div>
	            {/foreach}
	            <div class="multilang-selector">
	                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
	                    {foreach $available_languages as $id_lang => $iso_code}
	                        <span class="multilang lang-{$id_lang|intval}{if $id_lang != $id_lang_current} hidden{/if}">{$iso_code|escape:'html':'UTF-8'}</span>
	                    {/foreach}
	                    <span class="caret"></span>
	                </button>
	                <ul class="dropdown-menu">
	                    {foreach $available_languages as $id_lang => $iso_code}
	                        <li><a href="#" class="selectLanguage" data-lang="{$id_lang|intval}">{$iso_code|escape:'html':'UTF-8'}</a></li>
	                    {/foreach}
	                </ul>
	            </div>
	        </div>
	        <input type="text" name="position" value="{$item.position|intval}" class="item-position">
	    </div>
	    <div class="col-md-8 tagify-wrapper">
			{include file="./input.tpl" name = 'merged_values' value = implode(',', $item.t_field.value) field = $item.t_field}
			<div class="row-actions">
				<button type="button" class="btn btn-default saveMergedRow"><i class="icon-save"></i></button>
				<button type="button" class="btn btn-default deleteMergedRow"><i class="icon-trash"></i></button>
			</div>
		</div>
		</form>
	</div>
	{/foreach}
{else}
	<div class="no-matches">{l s='No values' mod='amazzingfilter'}</div>
{/if}
{* since 3.2.7 *}

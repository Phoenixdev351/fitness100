{*
*  @author    Amazzing <mail@mirindevo.com>
*  @copyright Amazzing
*  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<div class="filter clearfix" data-key="{$filter.key|escape:'html':'UTF-8'}">
	<div class="f-name">
		<span class="prefix">{$filter.prefix|escape:'html':'UTF-8'}</span>
		<span class="name" data-name="{$filter.name_original|escape:'html':'UTF-8'}">{$filter.name|escape:'html':'UTF-8'}</span>
	</div>
	<div class="f-actions pull-right">
		<a href="#" class="icon-cog toggleFilterSettings"></a>
		<a href="#" class="icon-trash removeFilter"></a>
	</div>
	<div class="f-quick-settings pull-right">
		{foreach $filter.settings as $name => $field}
			{if empty($field.quick)}{continue}{/if}
			{include file="./form-group.tpl"
				name = $field.input_name
				group_class = 'inline-block'
				input_wrapper_class = 'inline-block'
				label_class = 'inline-block'
			}
		{/foreach}
	</div>
	<div class="f-settings clearfix">
		{foreach $filter.settings as $name => $field}
			{if !empty($field.quick)}{continue}{/if}
			{include file="./form-group.tpl"
				name = $field.input_name
			}
		{/foreach}
	</div>
</div>
{* since 3.1.0 *}

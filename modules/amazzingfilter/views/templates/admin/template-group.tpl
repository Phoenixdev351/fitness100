{*
*  @author    Amazzing <mail@mirindevo.com>
*  @copyright Amazzing
*  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{$show_sorting = $group_data.additional_actions && $group_data.templates|count > 5}
<div class="template-group{if $show_sorting} not-ready{/if}">
	<div class="tab-title{if !$group_data.first} intermediate{/if}">
		{$group_data.title|escape:'html':'UTF-8'}
		{if $group_data.additional_actions}
			{if $show_sorting}
				<div class="template-sorting inline-block">
					{l s='Sort by' mod='amazzingfilter'}
					<a href="#" class="ts-current-option">{l s='Date added' mod='amazzingfilter'}</a>
					<div class="ts-options">
						<a href="#" class="ts-by current" data-by="date_add">{l s='Date added' mod='amazzingfilter'}</a>
						<a href="#" class="ts-by" data-by="name">{l s='Name' mod='amazzingfilter'}</a>
					</div>
					<a href="#" class="ts-way inline-block">
						<i class="icon-sort-amount-desc current" data-way="desc"></i>
						<i class="icon-sort-amount-asc hidden" data-way="asc"></i>
					</a>
				</div>
			{/if}
			<a href="#" class="btn-add addTemplate" data-controller="{$controller_type|escape:'html':'UTF-8'}">
				<i class="icon-plus-circle"></i> {l s='New' mod='amazzingfilter'}
			</a>
		{/if}
	</div>
	<div class="template-list {$controller_type|escape:'html':'UTF-8'}">
		{foreach $group_data.templates as $t}
			{include file="./template-form.tpl" additional_actions = $group_data.additional_actions}
		{/foreach}
	</div>
</div>
{* since 3.3.0 *}

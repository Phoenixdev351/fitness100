{*
*  @author    Amazzing <mail@mirindevo.com>
*  @copyright Amazzing
*  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{$full = isset($template_filters) && isset($template_controller_settings)}

<div class="af_template{if isset($template_filters)} open{/if}" data-id="{$t.id_template|intval}" data-controller="{$t.template_controller|escape:'html':'UTF-8'}">
	<form class="template-form form-horizontal">
	<div class="template_header clearfix">
		<div class="template-name">
			<div class="t-name list-view inline-block">{$t.template_name|escape:'html':'UTF-8'}</div>
			{if !empty($t.first_in_group) && $t.template_controller == 'seopage'}<span class="grey-note list-view">(default)</span>{/if}
			<div class="open-view">
				<input type="text" name="template_name" value="{$t.template_name|escape:'html':'UTF-8'}">
			</div>
		</div>
		<input type="hidden" name="template_controller" value="{$t.template_controller|escape:'html':'UTF-8'}">
		{*  May be used in next versions
		<div class="template-controller hidden">
			<div class="inline-block open-view">
				<label class="inline-block">Displayed on</label>
				<div class="inline-block">
					<select name="template_controller">
					{foreach $controller_options as $value => $display_name}
						<option value="{$value|escape:'html':'UTF-8'}"{if $value == $t.template_controller} selected{/if}>{$display_name|escape:'html':'UTF-8'}</option>
					{/foreach}
					</select>
				</div>
			</div>
		</div>
		*}
		<div class="template-actions pull-right">
			<a href="#" class="activateTemplate list-action-enable action-{if $t.active == 1}enabled{else}disabled{/if}">
				<i class="icon-check"></i>
				<i class="icon-remove"></i>
				<input type="hidden" name="active" value="{$t.active|intval}">
			</a>
			<a href="#" class="editTemplate"><i class="icon-pencil"></i></a>
			<a href="#" class="scrollUp label-tooltip" title="{l s='Minimize' mod='amazzingfilter'}"><i class="icon-minus"></i></a>
			{if $additional_actions}
				<a href="#" class="toggleExtraActions">• • •</a>
				<div class="extra">
					<a href="#" class="template-action" data-action="Duplicate">
						<i class="icon-copy"></i> {l s='Duplicate' mod='amazzingfilter'}
					</a>
					{if empty($t.first_in_group)}
						<a href="#" class="template-action b-top" data-action="Delete">
							<i class="icon-trash"></i> {l s='Delete' mod='amazzingfilter'}
						</a>
					{/if}
				</div>
			{/if}
		</div>
	</div>
	<div class="template_settings clearfix" style="display:none;">
		{if $full}
		<div class="controller-settings clearfix">
			{if !empty($t.first_in_group) && $t.template_controller == 'seopage'}{$template_controller_settings = []}{/if}
			{foreach $template_controller_settings as $name => $field}
				{include file="./form-group.tpl"
					group_class = 'basic-item'
					label_class = 'basic-label'
					input_wrapper_class = 'basic-input'
				}
			{/foreach}
		</div>
		<a href="#filters" class="template-tab-option first active">{l s='Filters' mod='amazzingfilter'}
		</a><a href="#additional-settings" class="template-tab-option last"><i class="icon-sliders"></i>
		{l s='Additional settings' mod='amazzingfilter'} <span class="additional-settings-count">{$additional_settings|count}</span>
		</a>
		<div id="filters" class="template-tab-content clearfix first active">
			<div class="f-list sortable">
				{foreach $template_filters as $key => $filter}
					{if !empty($filter)}{include file="./filter-form.tpl"}{/if}
				{/foreach}
			</div>
			<button type="button" class="btn btn-primary addNewFilter">
				<i class="icon-plus"></i> {l s='Add filters' mod='amazzingfilter'}
			</button>
		</div>
		<div id="additional-settings" class="template-tab-content clearfix">
			{foreach $general_settings_fields as $name => $field}
				{$locked = !isset($additional_settings[$name])}
				{if !$locked}{$field['value'] = $additional_settings[$name]}{/if}
				{include file="./form-group.tpl"
					name = 'additional_settings['|cat:$name|cat:']'
					group_class = 'settings-item'
					label_class = 'settings-label'
					input_wrapper_class = 'settings-input'
					locked = $locked
				}
			{/foreach}
		</div>
		<div class="tempate-footer clear-both">
			<input type="hidden" name="id_template" value="{$t.id_template|intval}">
			<button type="button" name="saveTemplate" class="saveTemplate btn btn-default">
				<i class="process-icon-save"></i>
				{l s='Save template' mod='amazzingfilter'}
			</button>
		</div>
		{/if}
	</div>
	</form>
</div>
{* since 3.3.0 *}

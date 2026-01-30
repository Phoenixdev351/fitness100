{*
*  @author    Amazzing <mail@mirindevo.com>
*  @copyright Amazzing
*  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{function renderOptionsLevel options=[] is_tree=false root=true checkable=true}
	<div class="opt-level{if $root} root{else} child{/if}">
	{foreach $options as $id => $display_name}
		{$children = []}{if $is_tree && !empty($data.options[$id])}{$children = $data.options[$id]}{/if}
		{$checked = isset($data.value[$id])}
		<div class="opt{if $children} has-children{if !$root} closed{/if}{/if}">
			<label class="opt-label{if $checked} checked{/if}">
				{if $checkable}
					<input type="checkbox" class="opt-checkbox" name="{$name|escape:'html':'UTF-8'}[]" value="{$id|escape:'html':'UTF-8'}"{if $checked} checked{/if}>
				{/if}
				<span class="opt-id hidden">{$id|escape:'html':'UTF-8'}</span>
				<span class="opt-name">{$display_name|escape:'html':'UTF-8'}</span>
				{if $children && !$root}<a href="#" class="icon-folder-open toggleChildren"></a>{/if}
			</label>
			{if $children}
				<span class="checked-num hidden">(<span class="dynamic-num"></span> {l s='checked' mod='amazzingfilter'})</span>
				{if !$root}<label class="checkChildren">{l s='Check all' mod='amazzingfilter'}</label>{/if}
				{renderOptionsLevel options=$children is_tree=$is_tree root=false}
			{/if}
		</div>
	{/foreach}
	</div>
{/function}

<div class="selected-options-inline dynamic-name">
	<span class="all">
		{if !empty($label)}{$label|escape:'html':'UTF-8'}{else}
		{l s='All available (including new created)' mod='amazzingfilter'}{/if}
	</span>
	<span class="selected-items">
		<span class="item-names"></span>
		<span class="total">{l s='Total' mod='amazzingfilter'}: <span class="total-num"></span></span>
	</span>
	<i class="icon-angle-down toggleIndicator"></i>
</div>
<div class="available-options hidden{if !empty($autohide)} autohide{/if}">
	{$options = $data.options}
	{$is_tree = false}
	{$checkable = true}
	{if !empty($data.id_root)}
		{if !empty($data.options[$data.id_root])}
			{$options = $data.options[$data.id_root]}
			{$checkable = !empty($data.checkable_root)}
		{else}
			{$options = []}
		{/if}
		{$is_tree = true}
	{/if}
	{renderOptionsLevel options=$options is_tree=$is_tree root=true checkable=$checkable}
	<div class="qs-container">
		<a href="#" class="icon-search qs-toggle" title="{l s='Quick search' mod='amazzingfilter'}"></a>
		<input type="text" class="qs-input" data-tree="{$is_tree|intval}">
	</div>
	<div class="options-footer clearfix">
		{if empty($no_ids)}
			<div class="opt-single-action pull-right">
				<label class="opt-action"><input type="checkbox" class="toggleIDs"> {l s='Show IDs' mod='amazzingfilter'}</label>
			</div>
		{/if}
		<div class="opt-bulk-actions pull-left">
			{if $is_tree}
				<label class="opt-action bulk" data-bulk-action="open" data-toggle="close">
					<i class="icon-folder-open"></i> {l s='Open all' mod='amazzingfilter'}
				</label>
				<label class="opt-action bulk hidden" data-bulk-action="close" data-toggle="open">
					<i class="icon-folder-close"></i> {l s='Close all' mod='amazzingfilter'}
				</label>
			{/if}
			<label class="opt-action bulk" data-bulk-action="check" data-toggle="uncheck">
				<i class="icon-check-sign"></i> {l s='Check all' mod='amazzingfilter'}
			</label>
			<label class="opt-action bulk hidden" data-bulk-action="uncheck" data-toggle="check">
				<i class="icon-check-empty"></i> {l s='Uncheck' mod='amazzingfilter'}
			</label>
			<label class="opt-action bulk" data-bulk-action="invert">
				<span class="txt"><i class="icon-random"></i> {l s='invert' mod='amazzingfilter'}</span>
			</label>
		</div>
	</div>
</div>
{* since 3.2.3 *}

{*
*  @author    Amazzing <mail@mirindevo.com>
*  @copyright Amazzing
*  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{$p_max = ceil($pagination.total/$pagination.npp)}
{$prev = $pagination.p - 1}
{$next = $pagination.p + 1}

<form class="pagination-form list-params">
	<div class="npp-holder pull-left text-left">
		{$from = ($pagination.p - 1) * $pagination.npp + 1}
		{$to = $from + $pagination.npp - 1}{if $to > $pagination.total}{$to = $pagination.total}{/if}
		<div class="inline-block">{l s='Showing %1$d - %2$d of %3$d' mod='amazzingfilter' sprintf=[$from, $to, $pagination.total]}</div>
		<div class="inline-block npp-selector-wrapper">
			<select name="npp" class="npp inline-block update-list reset-page">
				{foreach $pagination.npp_options as $npp_val}
					<option value="{$npp_val|intval}"{if $pagination.npp == $npp_val} selected{/if}>{$npp_val|intval}</option>
				{/foreach}
				{if !in_array($pagination.total, $pagination.npp_options)}
					<option value="{$pagination.total|intval}"{if $pagination.npp == $pagination.total} selected{/if}>
						{l s='All (%d)' mod='amazzingfilter' sprintf=[$pagination.total]}
					</option>
				{/if}
			</select>
		</div>
		<div class="inline-block">{l s='items per page' mod='amazzingfilter'}</div>
	</div>
	<input type="hidden" name="p" value="{$pagination.p|intval}" class="page-input update-list">
	{if $p_max > 1}
	<div class="pages-holder pull-right">
		<a href="#" class="go-to-page prev" data-page="{if $prev}{$prev|intval}{else}1{/if}"><i class="icon-long-arrow-left"></i></a>
		{if $prev}
			<a href="#" class="go-to-page first" data-page="1">1</a>
			{if $prev > 1}
				{if $prev > 2}<span class="p-dots">...</span>{/if}
				<a href="#" class="go-to-page" data-page="{$prev|intval}">{$prev|intval}</a>
			{/if}
		{/if}
		<span href="#" class="current-page">{$pagination.p|intval}</span>
		{if $next <= $p_max}
			{if $next < $p_max}
				<a href="#" class="go-to-page" data-page="{$next|intval}">{$next|intval}</a>
				{if $next < $p_max - 1}<span class="p-dots">...</span>{/if}
			{/if}
			<a href="#" class="go-to-page last" data-page="{$p_max|intval}">{$p_max|intval}</a>
		{else}
			{$next = $p_max}
		{/if}
		<a href="#" class="go-to-page next" data-page="{$next|intval}"><i class="icon-long-arrow-right"></i></a>
	</div>
	{/if}
</form>
{* since 3.2.9 *}

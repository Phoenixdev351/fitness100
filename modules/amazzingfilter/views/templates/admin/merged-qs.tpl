{*
*  @author    Amazzing <mail@mirindevo.com>
*  @copyright Amazzing
*  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{if $results}
	{foreach $results as $group_name => $values}
		<div class="qs-group">
			<div class="qs-group-title">{$group_name|escape:'html':'UTF-8'}</div>
			{$cut = [before => 10, after => 0]}
			{foreach $values as $id => $name}
				<div class="qs-value{if !$cut.before} cut{/if}{if in_array($id, $blocked)} blocked{/if}" data-id="{$id|escape:'html':'UTF-8'}">
					<span class="qs-id">{$id|intval}</span>
					<span class="qs-name">{$name|escape:'html':'UTF-8'}</span>
				</div>
				{if $cut.before}{$cut.before = $cut.before - 1}{else}{$cut.after = $cut.after + 1}{/if}
			{/foreach}
			{if $cut.after}<a href="#" class="showMoreQs">+{$cut.after|intval} ...</a>{/if}
		</div>
	{/foreach}
{else}
 	<div class="qs-no-matches-">{l s='No matches...' mod='amazzingfilter'}</div>
{/if}
{* since 3.2.7 *}

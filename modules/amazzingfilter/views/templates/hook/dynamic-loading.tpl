{*
*  @author    Amazzing <mail@mirindevo.com>
*  @copyright Amazzing
*  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{* buttons will be moved dynamically above and under the list *}
{if $hidden_inputs.page > 1}
<div class="af dynamic-loading prev hidden">
	<button class="loadMore prev button lnk_view btn btn-primary" data-p="{$hidden_inputs.page|intval}">
		<span>{l s='View previous products' mod='amazzingfilter'}</span>
	</button>
	<span class="loading-indicator"></span>
</div>
{/if}
<div class="af dynamic-loading next{if $hidden_inputs.p_type == 3} infinite-scroll{/if} hidden">
    {if $is_modern}<span class="dynamic-product-count"></span><div class="relative-container">{/if}
    <button class="loadMore next button lnk_view btn btn-primary">
        <span>{l s='Load more' mod='amazzingfilter'}</span>
    </button>
    <span class="loading-indicator"></span>
	{if $is_modern}</div>{/if}
</div>
{* since 3.1.2 *}

{*
*  @author    Amazzing <mail@mirindevo.com>
*  @copyright Amazzing
*  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<div class="af-basic-layout">
	<div class="content_sortPagiBar clearfix">
		<div class="top-pagination-content clearfix">
			{include file="{$tpl_dir}product-compare.tpl"}
			<div id="{$af_ids['pagination']|escape:'html':'UTF-8'}"></div>
		</div>
	</div>
	<ul class="product_list grid row {$product_list_class|escape:'html':'UTF-8'}"></ul>
	<div class="content_sortPagiBar">
		<div class="bottom-pagination-content clearfix">
			{include file="{$tpl_dir}product-compare.tpl" paginationId='bottom'}
			<div id="{$af_ids['pagination_bottom']|escape:'html':'UTF-8'}"></div>
		</div>
	</div>
</div>
{* since 3.1.0 *}

{**
 * Affiliates
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @author    FMM Modules
 * @copyright © Copyright 2021 - All right reserved
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @category  FMM Modules
 * @package   affiliates
 *}
 <tr id="product_rule_{$product_rule_group_id|intval|escape:'htmlall':'UTF-8'}_{$product_rule_id|intval|escape:'htmlall':'UTF-8'}_tr">
	<td>
		<input type="hidden" name="product_rule_{$product_rule_group_id|intval|escape:'htmlall':'UTF-8'}[]" value="{$product_rule_id|escape:'htmlall':'UTF-8'}" />
		<input type="hidden" name="product_rule_{$product_rule_group_id|intval|escape:'htmlall':'UTF-8'}_{$product_rule_id|intval|escape:'htmlall':'UTF-8'}_type" value="{$product_rule_type|escape:'htmlall':'UTF-8'}" />
		{* Everything is on a single line in order to avoid a empty space between the [ ] and the word *}
		[{if $product_rule_type == 'products'}{l s='Products' mod='affiliates'}{elseif $product_rule_type == 'categories'}{l s='Categories' mod='affiliates'}{elseif $product_rule_type == 'manufacturers'}{l s='Brands' mod='affiliates'}{elseif $product_rule_type == 'suppliers'}{l s='Suppliers' mod='affiliates'}{elseif $product_rule_type == 'attributes'}{l s='Attributes' mod='affiliates'}{/if}]
	</td>
	<td>
		<input type="text" id="product_rule_{$product_rule_group_id|intval|escape:'htmlall':'UTF-8'}_{$product_rule_id|intval|escape:'htmlall':'UTF-8'}_match" value="" disabled="disabled" />
	</td>
	<td>
		<a class="btn btn-default" id="product_rule_{$product_rule_group_id|intval|escape:'htmlall':'UTF-8'}_{$product_rule_id|intval|escape:'htmlall':'UTF-8'}_choose_link" href="#product_rule_{$product_rule_group_id|intval|escape:'htmlall':'UTF-8'}_{$product_rule_id|intval|escape:'htmlall':'UTF-8'}_choose_content">
			<i class="icon-list-ul"></i>
			{l s='Choose' mod='affiliates'}
		</a>
		<div>
			<div id="product_rule_{$product_rule_group_id|intval|escape:'htmlall':'UTF-8'}_{$product_rule_id|intval|escape:'htmlall':'UTF-8'}_choose_content">
				{$product_rule_choose_content} {* html content *}
			</div>
		</div>
	</td>
	<td class="text-right">
		<a class="btn btn-default" href="javascript:removeProductRule({$product_rule_group_id|intval|escape:'htmlall':'UTF-8'}, {$product_rule_id|intval|escape:'htmlall':'UTF-8'});">
			<i class="icon-remove"></i>
		</a>
	</td>
</tr>

<script type="text/javascript">
	$("#product_rule_{$product_rule_group_id|intval|escape:'htmlall':'UTF-8'}_{$product_rule_id|intval|escape:'htmlall':'UTF-8'}_choose_content").parent().hide();
  $("#product_rule_{$product_rule_group_id|intval|escape:'htmlall':'UTF-8'}_{$product_rule_id|intval|escape:'htmlall':'UTF-8'}_choose_link").fancybox({
    autoDimensions: false,
    autoSize: false,
    width: 600,
    autoHeight: true,
  });
	$(document).ready(function() { updateProductRuleShortDescription($("#product_rule_select_{$product_rule_group_id|intval|escape:'htmlall':'UTF-8'}_{$product_rule_id|intval|escape:'htmlall':'UTF-8'}_add")); });
</script>

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
<div class="col-lg-12 bootstrap">
	<div class="col-lg-6">
		{l s='Unselected' mod='affiliates'}
		<select multiple size="10" id="product_rule_select_{$product_rule_group_id|intval|escape:'htmlall':'UTF-8'}_{$product_rule_id|intval|escape:'htmlall':'UTF-8'}_1">
			{foreach from=$product_rule_itemlist.unselected item='item'}
				<option value="{$item.id|intval|escape:'htmlall':'UTF-8'}" title="{$item.name|escape:'htmlall':'UTF-8'}">&nbsp;{$item.name|escape:'htmlall':'UTF-8'}</option>
			{/foreach}
		</select>
		<div class="clearfix">&nbsp;</div>
		<a id="product_rule_select_{$product_rule_group_id|intval|escape:'htmlall':'UTF-8'}_{$product_rule_id|intval|escape:'htmlall':'UTF-8'}_add" class="btn btn-default btn-block" >
			{l s='Add' mod='affiliates'}
			<i class="icon-arrow-right"></i>
		</a>
	</div>
	<div class="col-lg-6">
		{l s='Selected' mod='affiliates'}
		<select multiple size="10" name="product_rule_select_{$product_rule_group_id|intval|escape:'htmlall':'UTF-8'}_{$product_rule_id|intval|escape:'htmlall':'UTF-8'}[]" id="product_rule_select_{$product_rule_group_id|intval|escape:'htmlall':'UTF-8'}_{$product_rule_id|intval|escape:'htmlall':'UTF-8'}_2" class="product_rule_toselect" >
			{foreach from=$product_rule_itemlist.selected item='item'}
				<option value="{$item.id|intval|escape:'htmlall':'UTF-8'}" title="{$item.name|escape:'htmlall':'UTF-8'}">&nbsp;{$item.name|escape:'htmlall':'UTF-8'}</option>
			{/foreach}
		</select>
		<div class="clearfix">&nbsp;</div>
		<a id="product_rule_select_{$product_rule_group_id|escape:'htmlall':'UTF-8'}_{$product_rule_id|escape:'htmlall':'UTF-8'}_remove" class="btn btn-default btn-block" >
			<i class="icon-arrow-left"></i>
			{l s='Remove' mod='affiliates'}
		</a>
	</div>
</div>

<script type="text/javascript">
	$("#product_rule_select_{$product_rule_group_id|intval|escape:'htmlall':'UTF-8'}_{$product_rule_id|intval|escape:'htmlall':'UTF-8'}_remove").click(function() { removeCartRuleOption(this); updateProductRuleShortDescription(this); });
	$("#product_rule_select_{$product_rule_group_id|intval|escape:'htmlall':'UTF-8'}_{$product_rule_id|intval|escape:'htmlall':'UTF-8'}_add").click(function() { addCartRuleOption(this); updateProductRuleShortDescription(this); });
	$(document).ready(function() { updateProductRuleShortDescription($("#product_rule_select_{$product_rule_group_id|intval|escape:'htmlall':'UTF-8'}_{$product_rule_id|intval|escape:'htmlall':'UTF-8'}_add")); });
</script>

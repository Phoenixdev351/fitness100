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

<tr id="product_rule_group_{$product_rule_group_id|intval|escape:'htmlall':'UTF-8'}_tr">
	<td>
		<a class="btn btn-default" href="javascript:removeProductRuleGroup({$product_rule_group_id|intval|escape:'htmlall':'UTF-8'});">
			<i class="icon-remove text-danger"></i>
		</a>
	</td>
	<td>

		<div class="form-group">
			<label class="control-label col-lg-4">{l s='Number of products required in the cart to enjoy the discount:' mod='affiliates'}</label>
			<div class="col-lg-1 pull-left">
				<input type="hidden" name="product_rule_group[]" value="{$product_rule_group_id|intval|escape:'htmlall':'UTF-8'}" />
				<input class="form-control" type="text" name="product_rule_group_{$product_rule_group_id|intval|escape:'htmlall':'UTF-8'}_quantity" value="{$product_rule_group_quantity|intval|escape:'htmlall':'UTF-8'}" />
			</div>
		</div>



		<div class="form-group">

			<label class="control-label col-lg-4">{l s='Add a rule concerning' mod='affiliates'}</label>
			<div class="col-lg-4">
				<select class="form-control" id="product_rule_type_{$product_rule_group_id|intval|escape:'htmlall':'UTF-8'}">
					<option value="">{l s='-- Choose --' mod='affiliates'}</option>
					<option value="products">{l s='Products' mod='affiliates'}</option>
					<option value="attributes">{l s='Attributes' mod='affiliates'}</option>
					<option value="categories">{l s='Categories' mod='affiliates'}</option>
					<option value="manufacturers">{l s='Brands' mod='affiliates'}</option>
					<option value="suppliers">{l s='Suppliers' mod='affiliates'}</option>
				</select>
			</div>
			<div class="col-lg-4">
				<a class="btn btn-default" href="javascript:addProductRule({$product_rule_group_id|intval});">
					<i class="icon-plus-sign"></i>
					{l s="Add" mod='affiliates'}
				</a>
			</div>

		</div>

		{l s='The product(s) are matching one of these:' mod='affiliates'}
		<table id="product_rule_table_{$product_rule_group_id|intval|escape:'htmlall':'UTF-8'}" class="table table-bordered">
			{if isset($product_rules) && $product_rules|@count}
				{foreach from=$product_rules item='product_rule'}
					{$product_rule} {* html content *}
				{/foreach}
			{/if}
		</table>

	</td>
</tr>

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
<div class="panel">
	<h3><i class="icon-tag"></i> {l s='Cart rule' mod='affiliates'}</h3>
	<div class="productTabs">
		<ul class="tab nav nav-tabs">
			<li class="tab-row">
				<a class="tab-page" id="cart_rule_link_informations" href="javascript:displayCartRuleTab('informations');"><i class="icon-info"></i> {l s='Information' mod='affiliates'}</a>
			</li>
			<li class="tab-row">
				<a class="tab-page" id="cart_rule_link_conditions" href="javascript:displayCartRuleTab('conditions');"><i class="icon-random"></i> {l s='Conditions' mod='affiliates'}</a>
			</li>
			<li class="tab-row">
				<a class="tab-page" id="cart_rule_link_actions" href="javascript:displayCartRuleTab('actions');"><i class="icon-wrench"></i> {l s='Actions' mod='affiliates'}</a>
			</li>
		</ul>
	</div>
	<form action="{$currentIndex|escape:'htmlall':'UTF-8'}&amp;token={$currentToken|escape:'htmlall':'UTF-8'}&amp;addcart_rule" id="cart_rule_form" class="form-horizontal" method="post">
		{if $currentObject->id}<input type="hidden" name="id_cart_rule" value="{$currentObject->id|intval}" />{/if}
		<input type="hidden" id="currentFormTab" name="currentFormTab" value="informations" />
		<div id="cart_rule_informations" class="panel cart_rule_tab">
			{include file='controllers/cart_rules/informations.tpl'}
		</div>
		<div id="cart_rule_conditions" class="panel cart_rule_tab">
			{include file='controllers/cart_rules/conditions.tpl'}
		</div>
		<div id="cart_rule_actions" class="panel cart_rule_tab">
			{include file='controllers/cart_rules/actions.tpl'}
		</div>
		<button type="submit" class="btn btn-default pull-right" name="submitAddcart_rule" id="{$table|escape:'htmlall':'UTF-8'}_form_submit_btn">{l s='Save' mod='affiliates'}
		</button>
	</form>

	<script type="text/javascript">
		var product_rule_groups_counter = {if isset($product_rule_groups_counter)}{$product_rule_groups_counter|intval}{else}0{/if};
		var product_rule_counters = new Array();
		var currentToken = "{$currentToken|escape:'quotes':'UTF-8'}";
		var currentFormTab = "{if isset($smarty.post.currentFormTab)}{$smarty.post.currentFormTab|escape:'htmlall':'UTF-8'}{else}informations{/if}";
		var currentText = '{l s='Now' js=1 mod='affiliates'}';
		var closeText = '{l s='Done' js=1 mod='affiliates'}';
		var timeOnlyTitle = '{l s='Choose Time' js=1 mod='affiliates'}';
		var timeText = '{l s='Time' js=1 mod='affiliates'}';
		var hourText = '{l s='Hour' js=1 mod='affiliates'}';
		var minuteText = '{l s='Minute' js=1 mod='affiliates'}';

		var languages = new Array();
		{foreach from=$languages item=language key=k}
			languages[{$k|escape:'htmlall':'UTF-8'}] = {
				id_lang: {$language.id_lang|escape:'htmlall':'UTF-8'},
				iso_code: "{$language.iso_code|escape:'quotes':'UTF-8'}",
				name: "{$language.name|escape:'quotes':'UTF-8'}"
			};
		{/foreach}
		displayFlags(languages, {$id_lang_default|escape:'htmlall':'UTF-8'});
	</script>
	<script type="text/javascript" src="{$smarty.const.__PS_BASE_URI__|escape:'htmlall':'UTF-8'}modules/affiliates/views/js/form.js"></script>
	{include file="footer_toolbar.tpl"}
</div>

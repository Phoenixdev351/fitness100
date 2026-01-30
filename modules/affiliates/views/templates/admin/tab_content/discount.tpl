{*
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
<script type="text/javascript">
$(document).ready(function(){
	$('input[name="REFERRAL_DISCOUNT_STATUS"]').click(function(){
		if($(this).val() == 1)
			$('#apply_referral_discount').show();
		else if($(this).val() == 0)
			$('#apply_referral_discount').hide();
	})
})
</script>
<!-- Discount -->
<label class="form-group control-label col-lg-4">
	<span class="label-tooltip" data-toggle="tooltip">{l s='Give discount to Referrals' mod='affiliates'}</span>
</label>
<div class="form-group margin-form">
	<div class="col-lg-8">
		{if $ps_version < 1.6}
			<label for="REFERRAL_DISCOUNT_STATUS_on" class="t">
				<input type="radio" value="1" id="REFERRAL_DISCOUNT_STATUS_on" name="REFERRAL_DISCOUNT_STATUS" {if isset($REFERRAL_DISCOUNT_STATUS) AND $REFERRAL_DISCOUNT_STATUS == 1}checked="checked"{/if}>
				{l s='Yes' mod='affiliates'}
			</label>
			
			<label for="REFERRAL_DISCOUNT_STATUS_off" class="t">
				<input type="radio" value="0" id="REFERRAL_DISCOUNT_STATUS_off" name="REFERRAL_DISCOUNT_STATUS" {if isset($REFERRAL_DISCOUNT_STATUS) AND $REFERRAL_DISCOUNT_STATUS == 0}checked="checked"{/if}>
				{l s='No' mod='affiliates'}
			</label>
		{else}
			<span class="switch prestashop-switch fixed-width-lg">
				<input type="radio" {if isset($REFERRAL_DISCOUNT_STATUS) AND $REFERRAL_DISCOUNT_STATUS == 1}checked="checked"{/if} value="1" id="REFERRAL_DISCOUNT_STATUS_on" name="REFERRAL_DISCOUNT_STATUS">
				<label for="REFERRAL_DISCOUNT_STATUS_on" class="t">{l s='Yes' mod='affiliates'}</label>
				<input type="radio" value="0" {if isset($REFERRAL_DISCOUNT_STATUS) AND $REFERRAL_DISCOUNT_STATUS == 0}checked="checked"{/if} id="REFERRAL_DISCOUNT_STATUS_off" name="REFERRAL_DISCOUNT_STATUS">
				<label for="REFERRAL_DISCOUNT_STATUS_off" class="t">{l s='No' mod='affiliates'}</label>
				<a class="slide-button btn"></a>
			</span>
		{/if}
	</div>
</div>


<div id="apply_referral_discount" style="display:{if isset($REFERRAL_DISCOUNT_STATUS) AND $REFERRAL_DISCOUNT_STATUS == 1}block{else}none{/if};">

	<label class="form-group control-label col-lg-4">
		<span class="label-tooltip" data-toggle="tooltip">{l s='Discount type' mod='affiliates'}</span>
	</label>
	<div class="form-group margin-form ">
		<div class="col-lg-4">
			<select name="REFERRAL_DISCOUNT_TYPE">
				<option value="percent" {if isset($REFERRAL_DISCOUNT_TYPE) AND $REFERRAL_DISCOUNT_TYPE AND $REFERRAL_DISCOUNT_TYPE == 'percent'}selected="selected"{/if}>{l s='Percentage(%)' mod='affiliates'}</option>
				<option value="amount" {if isset($REFERRAL_DISCOUNT_TYPE) AND $REFERRAL_DISCOUNT_TYPE AND $REFERRAL_DISCOUNT_TYPE == 'amount'}selected="selected"{/if}>{l s='Amount' mod='affiliates'}</option>
			</select>
		</div>
	</div>
	<div class="clearfix"></div>

	<label class="form-group control-label col-lg-4">
		<span class="label-tooltip" data-toggle="tooltip">{l s='Discount value' mod='affiliates'}</span>
	</label>
	<div class="form-group margin-form">
		<div class="col-lg-8">
			<div class="input-group col-lg-6">
				<input type="text" name="REFERRAL_DISCOUNT_VALUE" value="{if isset($REFERRAL_DISCOUNT_VALUE) AND $REFERRAL_DISCOUNT_VALUE}{$REFERRAL_DISCOUNT_VALUE|escape:'htmlall':'UTF-8'}{/if}">	
			</div>
		</div>
	</div>

	<label class="form-group control-label col-lg-4 required">
		<span class="label-tooltip" data-toggle="tooltip" title="{l s='Discount will be given in selected currency' mod='affiliates'}">{l s='Discount currrency' mod='affiliates'}</span>
	</label>
	<div class="form-group margin-form">
		<div class="form-group">
			<div class="col-lg-4">
				<select name="REFERRAL_DISCOUNT_CURRENCY" >
				{foreach from=$currencies item='currency'}
					<option value="{$currency.id_currency|escape:'htmlall':'UTF-8'}" {if isset($REFERRAL_DISCOUNT_CURRENCY) AND $REFERRAL_DISCOUNT_CURRENCY AND $REFERRAL_DISCOUNT_CURRENCY == $currency.id_currency}selected="selected"{/if}>{$currency.iso_code|escape:'htmlall':'UTF-8'} ({$currency.sign|escape:'htmlall':'UTF-8'})</option>
				{/foreach}
				</select>
			</div>
		</div>
	</div>
</div>
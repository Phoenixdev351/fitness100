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
<input type="hidden" id="fmm_aff_sign" value="{$currency->iso_code}" />
<input type="hidden" id="fmm_aff_rate" value="{$currency->conversion_rate}" />

<script type="text/javascript">
//<![CDATA[
var error = "<p class='error alert alert-danger'>{l s='Please select a Payment method.' mod='affiliates' js=1}</p>";
var min_error  = "<p class='warning alert alert-warning'>{l s='Please select an amount. Minimum amount to withdraw' mod='affiliates' js=1} : {Tools::displayPrice(Tools::convertPriceFull($MINIMUM_AMOUNT, null, $currency))|escape:'htmlall':'UTF-8'}</p>";
var min_wd = "{($MINIMUM_AMOUNT * $currency->conversion_rate)|escape:'htmlall':'UTF-8'|floatval}";
var affCurrencySign = "{$currency->iso_code}";
$(document).on('click', '.valid_rewards', function()
{
	var total = parseFloat($('#total-withdraw').val());
	console.log(total)
	if(isNaN(total) || parseFloat(total) < 0.000) {
		total = 0;
	}

	if ($(this).is(':checked') == true) {
		total = parseFloat(total) + parseFloat($(this).attr('reward'));
	}
	if ($(this).is(':checked') == false) {
		total = parseFloat(total) - parseFloat($(this).attr('reward'));
	}

	$('#total_withdraw').text(formatAffiliateCurrency(total, affCurrencyFormat, affCurrencySign, affCurrencyBlank));
	$('#total-withdraw').val(total);
});

$(document).on('click', '#submit-payment-request', function(e)
{
	e.preventDefault();
	e.stopImmediatePropagation();
	if (!parseFloat(min_wd) || typeof parseFloat(min_wd) === 'undefined') {
		min_wd = 0.0;
	}

	var pay_det = $('input[name=payment_detail]:checked').val()
	var total_wd = $('#total-withdraw').val();
	if ((typeof pay_det === 'undefined') || pay_det == '' || !pay_det.length) {
		fancyCloseBox(error);
	} else if (parseFloat(total_wd) < parseFloat(min_wd)) {
		fancyCloseBox(min_error);
	} else {
        $('#withdraw-form').submit();
	}
})
//]]>
</script>

<div class="row">
	<div class="center_column col-xs-12 col-sm-12" id="center_column">
		<h4 class="page-heading title_block panel">{l s='My Rewards' mod='affiliates'}</h4>
		<div class="panel-payments">
			{if isset($PAYMENT_DELAY_TIME) AND $PAYMENT_DELAY_TIME AND isset($pending_rewards)}
				<p class="alert alert-info hint_affiliate">
					{l s='Payment Release Time' mod='affiliates'} : {$PAYMENT_DELAY_TIME|escape:'htmlall':'UTF-8'}  
					{if $DELAY_TYPE == 'd'}
						{l s='Day(s)' mod='affiliates'}
					{elseif $DELAY_TYPE == 'h'}
						{l s='Hour(s)' mod='affiliates'}
					{elseif $DELAY_TYPE == 'm'}
						{l s='Minute(s)' mod='affiliates'}
					{/if}
				</p>
			{/if}
			<div class="table-responsive panel">
				<table class="table table-bordered {if $ps_version < 1.6}std{/if}">
					<tbody>
						<tr id="total_rewards">
							<td class="text-right first_row">
								<strong>{l s='Total Reward Amount' mod='affiliates'}</strong>
							</td>
							<td class="amount text-right first_row">
								<strong>{Tools::displayPrice(Tools::convertPriceFull($total_rewards, null, $currency))|escape:'htmlall':'UTF-8'}</strong>
							</td>
						</tr>
						<tr id="pending_rewards">
							<td class="text-right first_row">{l s='Pending Reward Amount' mod='affiliates'}</td>
							<td class="amount text-right first_row">{Tools::displayPrice(Tools::convertPriceFull($pending_rewards, null, $currency))|escape:'htmlall':'UTF-8'}</td>
						</tr>
						<tr id="paid_rewards">
							<td class="text-right first_row">{l s='Paid Amount' mod='affiliates'}</td>
							<td class="amount text-right first_row">{Tools::displayPrice(Tools::convertPriceFull($total_paid, null, $currency))|escape:'htmlall':'UTF-8'}</td>
						</tr>
						<tr id="awaiting_rewards">
							<td class="text-right first_row">{l s='Awaiting Payment Validation' mod='affiliates'}</td>
							<td class="amount text-right first_row">{Tools::displayPrice(Tools::convertPriceFull($awaiting_payments, null, $currency))|escape:'htmlall':'UTF-8'}</td>
						</tr>
						<tr id="reward_balance" class="{if isset($av_balance) AND $av_balance > 0}green_belt{else}low_balance{/if}">
							<td class="text-right">{l s='Available Balance' mod='affiliates'}</td>
							<td class="amount text-right nowrap">
								<strong>{Tools::displayPrice(Tools::convertPriceFull($av_balance, null, $currency))|escape:'htmlall':'UTF-8'}</strong>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<center>
			{if isset($PAYMENT_METHOD) AND $PAYMENT_METHOD}
				{if isset($av_balance) AND $av_balance}
					{if isset($MINIMUM_AMOUNT) AND $MINIMUM_AMOUNT > $av_balance}
						<p class="alert alert-warning warning">{l s='Minimum amount to withdraw' mod='affiliates'} : {Tools::displayPrice(Tools::convertPriceFull($MINIMUM_AMOUNT,  null, $currency))|escape:'htmlall':'UTF-8'}</p>
					{else}
						<a id="request-payment" href="javascript:void(0);" class="button btn btn-primary" onclick="$('#vrewards-list').toggle();">
							<span>{l s='Request Withdraw' mod='affiliates'}</span>
						</a>
					{/if}

					<br><br>
					<div class="clearfix"></div>
					<div id="vrewards-list" class="table-responsive" style="display:none;">
						<form id="withdraw-form" action="{$link->getModuleLink('affiliates', 'myaffiliates', ['withdraw' => '1'])|escape:'htmlall':'UTF-8'}" method="POST" enctype="multipart/form-data">
							<!-- payment method selection -->
							<table class="{if $ps_version >= 1.6}affiliateion_table{/if} table well table-bordered {if $ps_version < 1.6}std{/if}">
							{if (isset($paypal_details) AND !empty($paypal_details.details)) OR (isset($bankwire_details) AND !empty($bankwire_details.details)) OR (isset($ALL_CUSTOM_PAYMENTS) AND $ALL_CUSTOM_PAYMENTS AND isset($selected_custom_pm))}
								<tr>
								{if isset($PAYMENT_METHOD) AND in_array(1, $PAYMENT_METHOD) AND isset($paypal_details) AND $paypal_details.details}
									<td id="pd_1" class="center first_row">
										<label>{l s='Paypal' mod='affiliates'}</label>
									</td>
								{/if}
								{if isset($PAYMENT_METHOD) AND in_array(2, $PAYMENT_METHOD) AND isset($bankwire_details) AND $bankwire_details.details}
									<td id="pd_2" class="center first_row">
										<label>{l s='Bank Wire' mod='affiliates'}</label>
									</td>
								{/if}
								{if isset($PAYMENT_METHOD) AND in_array(3, $PAYMENT_METHOD) AND isset($wire_transfer_details) AND $wire_transfer_details.details}
									<td id="pd_3" class="center first_row">
										<label class="aff_fee_structure">{l s='Wire Transfer' mod='affiliates'} <i>({l s='Charges' mod='affiliates'}: {if $wire_transfer_fee > 0}{$wire_transfer_fee_formatted|escape:'htmlall':'UTF-8'}{else}{l s='Free' mod='affiliates'}{/if})</label>
									</td>
								{/if}
								</tr>
								<tr>
								{if isset($PAYMENT_METHOD) AND in_array(1, $PAYMENT_METHOD) AND isset($paypal_details) AND $paypal_details.details}
									<td class="center">
										<label for="payment_detail_1">
											<center class="imgm img-thumbnail btn btn-default button">
		                                    	<img src="{$smarty.const.__PS_BASE_URI__|escape:'htmlall':'UTF-8'}modules/affiliates/views/img/ppal.png"/>
		                                    </center>
		                                </label>
		                                <br/>
										<input id="payment_detail_1" value="1" type="radio" name="payment_detail" class="radio payment_detail" {if isset($bankwire_details) AND $bankwire_details.type == 1}checked="checked"{/if}>
									</td>
								{/if}
								{if isset($PAYMENT_METHOD) AND in_array(2, $PAYMENT_METHOD) AND isset($bankwire_details) AND $bankwire_details.details}
									<td class="center">
										<label for="payment_detail_2">
											<center class="imgm img-thumbnail btn btn-default button">
		                                    	<img src="{$smarty.const.__PS_BASE_URI__|escape:'htmlall':'UTF-8'}modules/affiliates/views/img/bw.png" width="24"/>
		                                    </center>
		                                </label>
		                                <br/>
										<input id="payment_detail_2" value="2" type="radio" name="payment_detail" class="radio payment_detail" {if isset($bankwire_details) AND $bankwire_details.type == 2}checked="checked"{/if}>
									</td>
								{/if}
								{if isset($PAYMENT_METHOD) AND in_array(3, $PAYMENT_METHOD) AND isset($wire_transfer_details) AND $wire_transfer_details.details}
									<td class="center">
										<label for="payment_detail_3">
											<center class="imgm img-thumbnail btn btn-default button">
		                                    	<img src="{$smarty.const.__PS_BASE_URI__|escape:'htmlall':'UTF-8'}modules/affiliates/views/img/wt.png" width="24"/>
		                                    </center>
		                                </label>
		                                <br/>
										<input id="payment_detail_3" value="3" type="radio" name="payment_detail" class="radio payment_detail" {if isset($wire_transfer_details) AND $wire_transfer_details.type == 3}checked="checked"{/if}>
									</td>
								{/if}
								{assign var='cpm' value=0}
								{if isset($ALL_CUSTOM_PAYMENTS) AND $ALL_CUSTOM_PAYMENTS AND isset($selected_custom_pm)}
									{foreach from=$ALL_CUSTOM_PAYMENTS item=custom_pm}
										{assign var='pm_details' value=PaymentDetails::getPaymentDetailByType($affiliations.id_affiliate, $custom_pm.id_payment_method)}
										{if in_array($custom_pm.id_payment_method, $selected_custom_pm) AND isset($pm_details) AND isset($pm_details.details) AND $pm_details.details}
										{assign var='cpm' value=1}
										<td class="center">
											<label for="payment_detail_{$custom_pm.id_payment_method|escape:'htmlall':'UTF-8'}">
												<center class="imgm img-thumbnail btn btn-default button">
													{$custom_pm.payment_name|escape:'htmlall':'UTF-8'}
												</center>
											</label>
											<br/>
											<input id="payment_detail_{$custom_pm.id_payment_method|escape:'htmlall':'UTF-8'}" value="{$custom_pm.id_payment_method|escape:'htmlall':'UTF-8'}" type="radio" name="payment_detail" class="radio payment_detail">
										</td>
										{/if}
									{/foreach}
								{/if}
								{if (!isset($paypal_details) OR !isset($paypal_details.details)) AND (!isset($bankwire_details) OR !isset($bankwire_details.details)) OR (!isset($ALL_CUSTOM_PAYMENTS) OR !isset($selected_custom_pm) OR !$cpm)}
									<td>
										<p class="text alert alert-warning warning">
											{l s='Please set Payment details first' mod='affiliates'}
										</p>
									</td>
								{/if}
								</tr>
							{else}
								<tr>
									<td>
										<p class="text alert alert-warning warning">
											{l s='Please set Payment details first' mod='affiliates'}
										</p>
									</td>
								</tr>
							{/if}
							</table>

							<!-- rewards amount table -->
							<table class="{if $ps_version >= 1.6}affiliateion_table{/if} table table-bordered {if $ps_version < 1.6}std{/if}">
			                    <thead>
			                        <tr>
			                            <th class="fixed-width-xs">&nbsp;</th>
			                            <th class="fixed-width-xs">
			                            	<span class="title_box">{l s='S.No' mod='affiliates'}</span>
			                            </th>
			                            <th>
			                                <span class="title_box">{l s='Available Amount(s)' mod='affiliates'}</span>
			                            </th>
			                        </tr>
			                    </thead>
			                    <tbody>
			                    {if isset($valid_rewards) AND $valid_rewards}
			                        {foreach from=$valid_rewards item=vreward}
				                        <tr>
				                            <td style="width:6%">
				                                <input type="checkbox" value="{$vreward.id_affiliate_reward|escape:'htmlall':'UTF-8'}" reward="{$vreward.reward_total|escape:'htmlall':'UTF-8'}" id="valid_rewards_{$vreward.id_affiliate_reward|escape:'htmlall':'UTF-8'}" class="valid_rewards" name="valid_rewards[]">
				                            </td>
				                            <td>{$vreward@iteration|escape:'htmlall':'UTF-8'}</td>
				                            <td>
				                                <span for="affiliate_groups_{$vreward.id_affiliate_reward|escape:'htmlall':'UTF-8'}">
				                                	{Tools::displayPrice(Tools::convertPriceFull($vreward.reward_total, null, $currency))|escape:'htmlall':'UTF-8'}
				                                </span>
				                            </td>
				                        </tr>
			                        {/foreach}
			                    {/if}
			                    </tbody>
			                    <tfoot>
			                    	<tr>
			                    		<td colspan="2" class="text-right first_row">
			                    			<strong>{l s='Total' mod='affiliates'}</strong>
			                    		</td>
			                    		<td class="title_box">
			                    			<label id="total_withdraw">{Tools::displayPrice(Tools::convertPriceFull(0, null, $currency))|escape:'htmlall':'UTF-8'}</label>
			                    			<input type="hidden" id="total-withdraw" value="0">
			                    		</td>
			                    	</tr>
			                    </tfoot>
			                </table>

			            {if (isset($paypal_details) AND !empty($paypal_details.details)) OR (isset($bankwire_details) AND !empty($bankwire_details.details)) OR (isset($ALL_CUSTOM_PAYMENTS) AND $ALL_CUSTOM_PAYMENTS AND isset($selected_custom_pm) AND $cpm)}
			                <div class="pull-right">
			                	{if $ps_version < 1.6}
									<input id="submit-payment-request" type="submit" class="button" name="withdraw" value="{l s='Request Withdraw' mod='affiliates'}">
								{else}
									<button id="submit-payment-request" class="btn btn-success button button-medium" name="withdraw">
										<span><i class="icon-money"></i> {l s='Submit Request' mod='affiliates'}</span>
									</button>
								{/if}
			                </div>
			            {/if}
			            </form>
		            </div>
				{/if}
			{else}
				<div class="text panel">
					<p class="alert alert-info hint_affiliate">{l s='No Payment method available' mod='affiliates'}</p>
				</div>
			{/if}
			</center>
		</div>
	</div>
</div>
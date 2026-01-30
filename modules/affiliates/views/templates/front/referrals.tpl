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
//<![CDATA[
var error_msg = "{l s='You must agree to the terms of services of Affiliate Program.' mod='affiliates' js=1}";
var ref_link = "{$ref_link|escape:'htmlall':'UTF-8'}";
var copied_msg = "{l s='Copied' mod='affiliates' js=1}"
$(document).ready(function()
{
	$('#inviteReferral').click(function(e)
	{
		e.preventDefault();
		e.stopImmediatePropagation();
		var html = '<div class="error alert alert-danger">'+ error_msg +'</div>'
		if ($('#conditionsValided').is(':checked') == false)
			fancyCloseBox(html);
		else
			$('#invite-friend').submit();
	});

	// social sharing
	$('button.link-social-share, img.link-social-share').on('click', function(){
		type = $(this).attr('data-type');
		if (type.length)
		{
			switch(type)
			{
				case 'twitter':
					window.open('https://twitter.com/intent/tweet?text='+ encodeURIComponent(htmlEncode(ref_link)), 'sharertwt', 'toolbar=0,status=0,width=640,height=445');
					break;
				case 'facebook':
					window.open('https://www.facebook.com/sharer.php?u=' + encodeURIComponent(htmlEncode(ref_link)), 'sharer', 'toolbar=0,status=0,width=660,height=445');
					break;
				case 'google-plus':
					window.open('https://plus.google.com/share?url=' + encodeURIComponent(htmlEncode(ref_link)), 'sharer', 'toolbar=0,status=0,width=660,height=445');
					break;
				case 'digg':
					window.open('https://digg.com/submit?url=' + encodeURIComponent(htmlEncode(ref_link)), 'sharer', 'toolbar=0,status=0,width=660,height=445');
					break;
			}
		}
	});
})
function htmlEncode(input)
{
    return String(input).replace(/&amp;/g, '&');
}
function copyToClipboard(element, trig) {
  var $temp = $("<input>");
  $("body").append($temp);
  $temp.val($(element).text()).select();
  document.execCommand("copy");
  $temp.remove();
  $(trig).text(copied_msg);
  $(trig).addClass('copied_color');
}
//]]>
</script>
<div id="referrals">
	<h4 class="page-heading title_block panel">{l s='Invitations' mod='affiliates'}</h4>
	<div class="text form-group">
		<ul class="well" style="list-style:inside none disc;">
			<li>{l s='Share this URL to invite your referrals.' mod='affiliates'}</li>
			<li>{l s='URL' mod='affiliates'} : <strong style="word-wrap: break-word;" id="fmm_aff_link_share">{$ref_link|escape:'htmlall':'UTF-8'}</strong> <span id="fmm_aff_copier" class="" onclick="copyToClipboard('#fmm_aff_link_share', this)">{l s='Copy' mod='affiliates'}</span></li>
		</ul>
		<br/>
		<span>{include file='./social_share.tpl'}</span>
		<br/>
		{if $refferal_coupon_state > 0}
		<div id="fmm_aff_quick_code">
			<h4 class="page-heading title_block panel">{l s='Discount code for refferals' mod='affiliates'}</h4>
			<p>{l s='You can share this Discount code with refferals to let them enjoy the discounts.' mod='affiliates'}</p>
			<div class="fmm_code_blk">
				<div class="fmm_code_blk_sub">
					<p>{l s='Refferals will get' mod='affiliates'} <strong>{if $refferal_coupon_type == 'amount'}{$refferal_coupon_curr|escape:'htmlall':'UTF-8'}{/if}{$refferal_coupon_disc|escape:'htmlall':'UTF-8'}{if $refferal_coupon_type == 'percent'}%{/if}</strong> {l s='discount. Using below code:' mod='affiliates'}</p>
					<span class="fmm_code_blk_code">{$refferal_coupon_code|escape:'htmlall':'UTF-8'}</span>
					<span class="fmm_code_blk_redflag">{l s='Do use your special link to share this voucher.' mod='affiliates'}</span>
				</div>
			</div>
		</div>
		{/if}

		<p>{l s='It\'s quick and it\'s easy. Just fill in the first name, last name, and e-mail address(es) of your friend(s) in the fields below. When one of them makes at least 1 order, you will receive your reward as a commission.' mod='affiliates'}</p>
	</div>

	<form id="invite-friend" method="post" action="{$link->getModuleLink('affiliates', 'myaffiliates', ['inviteReferral' => '1'])|escape:'htmlall':'UTF-8'}" class="well">
		<p class="text form-group">
			<label class="col-lg-3">{l s='Your message (optional)' mod='affiliates'}</label>
			<textarea class="form-control col-lg-9" cols="70" rows="10" name="ref_msg"></textarea>
		</p>
		<div class="clearfix"></div><br>
		<div class="table-responsive">
			<table class="{if $ps_version >= 1.6}affiliateion_table{/if} table table table-bordered {if $ps_version <1.6}std{/if}">
			<thead>
				<tr>
					<th class="first_item">&nbsp;</th>
					<th class="item">{l s='Last name' mod='affiliates'}</th>
					<th class="item">{l s='First name' mod='affiliates'}</th>
					<th class="last_item">{l s='E-mail' mod='affiliates'}</th>
				</tr>
			</thead>
			<tbody>
				{section name=referral start=0 loop=5 step=1}
				<tr class="{if $smarty.section.referral.index % 2}item{else}alternate_item{/if}">
					<td class="align_right first_row"><b>{$smarty.section.referral.iteration|escape:'htmlall':'UTF-8'}</b></td>
					<td>
						<input type="text" class="text form-control" name="referralLastName[{$smarty.section.referral.index|escape:'htmlall':'UTF-8'}]" size="14" value="{if isset($smarty.post.referralLastName[$smarty.section.referral.index])}{$smarty.post.referralLastName[$smarty.section.referral.index]|escape:'htmlall':'UTF-8'}{/if}" />
					</td>
					<td>
						<input type="text" class="text form-control" name="referralFirstName[{$smarty.section.referral.index|escape:'htmlall':'UTF-8'}]" size="14" value="{if isset($smarty.post.referralFirstName[$smarty.section.referral.index])}{$smarty.post.referralFirstName[$smarty.section.referral.index]|escape:'htmlall':'UTF-8'}{/if}" />
					</td>
					<td>
						<input type="text" class="text form-control" name="referralEmail[{$smarty.section.referral.index|escape:'htmlall':'UTF-8'}]" size="20" value="{if isset($smarty.post.referralEmail[$smarty.section.referral.index])}{$smarty.post.referralEmail[$smarty.section.referral.index]|escape:'htmlall':'UTF-8'}{/if}" />
					</td>
				</tr>
				{/section}
			</tbody>
			</table>
		</div>
		<p class="bold">
			<strong>{l s='Important: Your friends\' e-mail addresses will only be used in the affiliate program. They will never be used for other purposes.' mod='affiliates'}</strong>
		</p>
		<p class="checkbox">
			<input type="checkbox" name="conditionsValided" id="conditionsValided" value="1"/>
			<label for="conditionsValided">{l s='I agree to the terms of service and adhere to them unconditionally.' mod='affiliates'}</label>

			{if isset($cms) AND $cms}
				<a class="read-conditions" href="#affiliate-cond" class="thickbox" title="{l s='Conditions of the affiliate program' mod='affiliates'}" rel="nofollow">{l s='Read conditions.' mod='affiliates'}</a>

				<div style="display:none;">
					<div id="affiliate-cond">
						{include file="$tpl_dir./cms.tpl"}
					</div>
				</div>
			{/if}
		</p>
		<p class="submit pull-right">
		{if $ps_version < 1.6}
			<input type="submit" id="inviteReferral" name="inviteReferral" class="button_large button" value="{l s='Send Invitation' mod='affiliates'}" />
		{else}
			<button class="btn btn-default button button-medium" name="inviteReferral" id="inviteReferral" type="submit">
				<span>{l s='Send Invitation' mod='affiliates'} <i class="icon-envelope"></i></span>
			</button>
		{/if}
		</p>
		<div class="clearfix"></div>
	</form>
<!-- {*else*} -->
<!-- <p class="warning alert alert-warning">
	{l s='No sponsored friends have accepted your invitation yet.' mod='affiliates'}
</p> -->
<!-- {*/if*} -->
{if isset($levels) AND $levels}
	{include file="./reward-details.tpl"}
{/if}
</div>
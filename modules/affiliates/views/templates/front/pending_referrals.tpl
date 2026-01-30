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
<div id="pendings" class="table-responsive">
	<h4 class="page-heading title_block panel">{l s='Pending Referrals' mod='affiliates'}</h4>
	{if $pendingReferrals AND $pendingReferrals|@count > 0}
		<p>
			{l s='These friends have not yet placed an order on this Website since you invite them, but you can try again! To do so, mark the checkboxes of the referral(s) you want to remind, then click on the button "Send Reminder"' mod='affiliates'}
		</p>
		<form id="pending-referrals" method="post" action="{$link->getModuleLink('affiliates', 'myaffiliates', [], true)|escape:'htmlall':'UTF-8'}" class="well">
			
			<table class="{if $ps_version >= 1.6}affiliateion_table{/if} table table-bordered {if $ps_version <1.6}std{/if}">
				<thead>
					<tr>
						<th class="first_item">&nbsp;</th>
						<th class="item">{l s='Last name' mod='affiliates'}</th>
						<th class="item">{l s='First name' mod='affiliates'}</th>
						<th class="item">{l s='E-mail' mod='affiliates'}</th>
						<th class="last_item"><b>{l s='Last invitation' mod='affiliates'}</b></th>
					</tr>
				</thead>
				<tbody>
				{foreach from=$pendingReferrals item=pendingReferral name=myLoop}
					<tr>
						<td class="first_row">
							<b><input type="checkbox" name="referralChecked[{$pendingReferral.id_affiliate_invitation|escape:'htmlall':'UTF-8'}]" id="referralChecked[{$pendingReferral.id_affiliate_invitation|escape:'htmlall':'UTF-8'}]" value="{$pendingReferral.id_affiliate_invitation|escape:'htmlall':'UTF-8'}" /></b>
						</td>
						<td>{$pendingReferral.lastname|substr:0:22|escape:'htmlall':'UTF-8'}</td>
						<td>{$pendingReferral.firstname|substr:0:22|escape:'htmlall':'UTF-8'}</td>
						<td>{$pendingReferral.email|escape:'htmlall':'UTF-8'}</td>
						<td>{dateFormat date=$pendingReferral.date_upd full=1}</td>
					</tr>
				{/foreach}
				</tbody>
			</table>
			<p class="submit pull-right">
				{if $ps_version < 1.6}
					<input type="submit" value="{l s='Remind my referral(s)' mod='affiliates'}" name="reviveReferral" id="reviveReferral" class="button_large"/>
				{else}
					<button class="btn btn-primary button button-medium" name="reviveReferral" id="reviveReferral" type="submit">
						<span>{l s='Send Reminder' mod='affiliates'} <i class="icon-bell"></i></span>
					</button>
				{/if}
			</p>
			<div class="clearfix"></div>
		</form>
	{else}
		<p class="warning alert alert-warning">
			{if $pendingReferrals AND $pendingReferrals|@count > 0}
				{l s='You have no pending invitations.' mod='affiliates'}
			{else}
				{l s='You have no pending referrals.' mod='affiliates'}
			{/if}
		</p>
	{/if}
</div>
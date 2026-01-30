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
<div id="invited-referrals">
	<h4 class="page-heading title_block panel">{l s='Referrals List' mod='affiliates'}</h4>
	{if $myReferrals AND $myReferrals|@count > 0}
		<div class="table-responsive">
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
			{foreach from=$myReferrals item=my_ref name=myLoop}
				<tr>
					<td style="background:#e6e6e6;"><b>{$smarty.foreach.my_ref.iteration|escape:'htmlall':'UTF-8'}</b></td>
					<td>
						{if isset($my_ref.lastname) AND $my_ref.lastname}
							{$my_ref.lastname|escape:'htmlall':'UTF-8'|substr:0:22}
						{else if isset($my_ref.guest_lname) AND $my_ref.guest_lname}
							{$my_ref.guest_lname|escape:'htmlall':'UTF-8'|substr:0:22}
						{else}
							{l s='Guest' mod='affiliates'}
						{/if}
					</td>
					<td>
						{if isset($my_ref.firstname) AND $my_ref.firstname}
							{$my_ref.firstname|escape:'htmlall':'UTF-8'|substr:0:22}
						{else if isset($my_ref.guest_fname) AND $my_ref.guest_fname}
							{$my_ref.guest_fname|escape:'htmlall':'UTF-8'|substr:0:22}
						{else}
							{l s='Referral' mod='affiliates'}
						{/if}
					</td>
					<td>
						{if isset($my_ref.email) AND $my_ref.email}
							{$my_ref.email|escape:'htmlall':'UTF-8'}
						{else if isset($my_ref.guest_email) AND $my_ref.guest_email}
							{$my_ref.guest_email|escape:'htmlall':'UTF-8'}
						{else}
							--
						{/if}
					</td>
					<td>{dateFormat date=$my_ref.date_add full=1}</td>
				</tr>
			{/foreach}
			</tbody>
			</table>
			<div class="clearfix"></div>
		</div>
	{else}
		<p class="warning alert alert-warning">
			{l s='No sponsored friends have accepted your invitation yet.' mod='affiliates'}
		</p>
	{/if}
</div>
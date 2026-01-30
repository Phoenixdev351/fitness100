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
<div id="stats">
	<h4 class="page-heading title_block panel">{l s='Statistics' mod='affiliates'}</h4>
	{if $rewards AND $rewards|@count > 0}
	<div class="table-responsive">
		<table class="{if $ps_version >= 1.6}affiliateion_table{/if} table table-bordered {if $ps_version <1.6}std{/if}">
			<thead>
				<tr>
					<th class="first_item">&nbsp;</th>
					<th class="item">{l s='Date' mod='affiliates'}</th>
					<th class="item">{l s='Reward By' mod='affiliates'}</th>
					<th class="item">{l s='Reward Value' mod='affiliates'}</th>
					<th class="item">{l s='Status' mod='affiliates'}</th>
					<th class="item">{l s='Paid' mod='affiliates'}</th>
				</tr>
			</thead>
			<tbody>
			{foreach from=$rewards item=reward name=myReward}
				<tr>
					<td class="first_row"><b>{$smarty.foreach.myReward.iteration|escape:'htmlall':'UTF-8'}</b></td>
					<td>
						{$reward.reward_date|escape:'htmlall':'UTF-8'}
					</td>
					<td>
						{if $reward.reward_by_reg == 1}
							{l s='Referral Registration' mod='affiliates'}
						{elseif $reward.reward_by_ord == 1}
							{l s='Referral Order' mod='affiliates'}
						{/if}
					</td>
					<td>
						{if $reward.reward_by_reg == 1}
							{Tools::displayPrice(Tools::convertPriceFull($reward.reg_reward_value,null, $currency))|escape:'htmlall':'UTF-8'}
						{elseif $reward.reward_by_ord == 1}
							{Tools::displayPrice(Tools::convertPriceFull($reward.ord_reward_value,null, $currency))|escape:'htmlall':'UTF-8'}
						{/if}
					</td>
					<td width="25%" class="center">
						{if $reward.status == 'pending'}
							<span class="status_badge" style="background:#fe9126;"><i class="icon-spinner"></i> {l s='Pending' mod='affiliates'}</span>
						{elseif $reward.status == 'approved'}
							<span class="status_badge" style="background:#3aa04b;"><i class="icon-check-circle"></i> {l s='Approved' mod='affiliates'}</span>
						{elseif $reward.status == 'cancel'}
							<span class="status_badge" style="background:#d9534f;"><i class="icon-times-circle"></i> {l s='Cancelled' mod='affiliates'}</span>
						{/if}
					</td>
					<td class="center">
                    {if $reward.is_paid == 1}
                        {if $ps_version < 1.6}
                            <img src="{$smarty.const.__PS_BASE_URI__|escape:'htmlall':'UTF-8'}modules/affiliates/views/img/enabled.gif" alt="{l s='Paid' mod='affiliates'}" title="{l s='Paid' mod='affiliates'}" />
                        {else}
                            <span class="list-action-enable  action-enabled status_badge" style="background:#3aa04b;">
                                <i class="icon-check"></i>
                            </span>
                        {/if}
                    {elseif $reward.is_paid == 0}
                        {if $ps_version < 1.6}
                            <img src="{$smarty.const.__PS_BASE_URI__|escape:'htmlall':'UTF-8'}modules/affiliates/views/img/disabled.gif" alt="{l s='Unpaid' mod='affiliates'}" title="{l s='Unpaid' mod='affiliates'}" />
                        {else}
                            <span class="list-action-enable  action-disabled status_badge" style="background:#fe9126;">
                                <i class="icon-remove"></i>
                            </span>
                        {/if}
                    {/if}
                </td>
				</tr>
			{/foreach}
			</tbody>
			</table>
			<div class="clearfix"></div>
	</div>
	{else}
		<p class="warning alert alert-warning">
			{l s='You did not get any reward yet.' mod='affiliates'}
		</p>
	{/if}
</div>
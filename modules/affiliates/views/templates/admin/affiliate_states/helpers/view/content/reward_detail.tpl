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
<table class="table table-filter-templates std" {if $version < 1.6}style="width: 100%; margin-bottom:10px;"{/if}>
    <thead>
        <tr>
            <th class="title_box center"><strong>{l s='#' mod='affiliates'}</strong></th>
            <th class="title_box center"><strong>{l s='Order Id' mod='affiliates'}</strong></th>
            <th class="title_box left"><strong>{l s='Referral' mod='affiliates'}</strong></th>
            <th class="title_box left"><strong>{l s='Email' mod='affiliates'}</strong></th>
            <th class="title_box center"><strong>{l s='Reward by Registrations' mod='affiliates'}</strong></th>
            <th class="title_box center"><strong>{l s='Reward by Orders' mod='affiliates'}</strong></th>
            <th class="title_box center"><strong>{l s='Reward Status' mod='affiliates'}</strong></th>
            <th class="title_box center"><strong>{l s='Paid' mod='affiliates'}</strong></th>
            <th class="title_box left"><strong>{l s='Reward Date' mod='affiliates'}</strong></th>
        </tr>
    </thead>
    <tbody>
    {if isset($referral_rewards) AND $referral_rewards}
        {foreach from=$referral_rewards item=reward}
            <tr id="rew_{$reward.id_affiliate_reward|escape:'htmlall':'UTF-8'}">
                <td class="center"><strong>{$reward@iteration|escape:'htmlall':'UTF-8'}</strong></td>
                <td class="center">
                    {if $reward.id_order}
                        <a href="?tab=AdminOrders&id_order={$reward.id_order|escape:'htmlall':'UTF-8'}&vieworder&token={$token_order|escape:'htmlall':'UTF-8'}">
                        {$reward.id_order|escape:'htmlall':'UTF-8'}
                    {else}
                        -
                    {/if}
                </td>
                <td class="left">
                    {if $reward.reference AND $reward.ref_name ne 'guest'}
                        {$reward.ref_name|escape:'htmlall':'UTF-8'}
                    {else}
                        {l s='Guest Referral' mod='affiliates'}
                    {/if}
                </td>
                <td class="left">
                    {if $reward.email AND $reward.ref_name ne 'guest'}
                        {$reward.email|escape:'htmlall':'UTF-8'}
                    {else}
                        -
                    {/if}
                </td>
                <td class="center">
                    <span class="badge {if $reward.reg_reward_value AND $reward.reg_reward_value > 0}badge-success {else} badge-danger{/if}">{convertPrice price=$reward.reg_reward_value|escape:'htmlall':'UTF-8'|floatval}</span>
                </td>
                <td class="center">
                    <span class="badge {if $reward.ord_reward_value AND $reward.ord_reward_value > 0}badge-success {else} badge-danger{/if}">{convertPrice price=$reward.ord_reward_value|escape:'htmlall':'UTF-8'|floatval}</span>
                </td>
                <td width="30%" class="center">
                    {if $reward.status == 'pending'}
                        <span class="status_badge" style="background:#fe9126;"><i class="icon-spinner"></i> {l s='Pending' mod='affiliates'}</span>
                    {elseif $reward.status == 'approved'}
                        <span class="status_badge" style="background:#3aa04b;"><i class="icon-check-circle"></i> {l s='Approved' mod='affiliates'}</span>
                    {elseif $reward.status == 'cancel'}
                        <span class="status_badge" style="background:#d9534f;"><i class="icon-times-circle"></i> {l s='Canceled' mod='affiliates'}</span>
                    {/if}
                </td>
                <td class="center">
                    {if $reward.is_paid == 1}
                        {if $version < 1.6}
                            <img src="../img/admin/enabled.gif" alt="{l s='Paid' mod='affiliates'}" title="{l s='Paid' mod='affiliates'}" />
                        {else}
                            <span class="list-action-enable  action-enabled">
                                <i class="icon-check"></i>
                            </span>
                        {/if}
                    {elseif $reward.is_paid == 0}
                        {if $version < 1.6}
                            <img src="../img/admin/disabled.gif" alt="{l s='Unpaid' mod='affiliates'}" title="{l s='Unpaid' mod='affiliates'}" />
                        {else}
                            <span class="list-action-enable  action-disabled">
                                <i class="icon-remove"></i>
                            </span>
                        {/if}
                    {/if}
                </td>
                <td class="left">{$reward.reward_date|escape:'htmlall':'UTF-8'}</td>
            </tr>
        {/foreach}
    {else}
        <tr>
            <td class="center hint-block help-block" colspan="5" style="display:table-cell;color:#999;">{l s='This affiliated cutomer has no rewards.' mod='affiliates'}</td>
        </tr>
    {/if}
    </tbody>
</table>

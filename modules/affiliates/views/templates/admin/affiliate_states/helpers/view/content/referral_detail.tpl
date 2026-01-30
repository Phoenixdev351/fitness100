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
            <th class="title_box center"><strong>{l s='ID' mod='affiliates'}</strong></th>
            <th class="title_box left"><strong>{l s='Referral' mod='affiliates'}</strong></th>
            <th class="title_box left"><strong>{l s='Email' mod='affiliates'}</strong></th>
            <th class="title_box left"><strong>{l s='Visits' mod='affiliates'}</strong></th>
            <th class="title_box left"><strong>{l s='Valid Orders' mod='affiliates'}</strong></th>
            <th class="title_box left"><strong>{l s='Total Spend' mod='affiliates'}</strong></th>
            <th class="title_box left"><strong>{l s='Date added' mod='affiliates'}</strong></th>
            <th class="title_box left"><strong>{l s='Source' mod='affiliates'}</strong></th>
        </tr>
    </thead>
    <tbody>
    {if isset($referrals) AND $referrals}
        {foreach from=$referrals item=referral}
            <tr id="ref_{$referral.id_affiliate_referral|escape:'htmlall':'UTF-8'}">
                <td class="center"><strong>{$referral.id_affiliate_referral|escape:'htmlall':'UTF-8'}</strong></td>
                <td class="left">
                    {if $referral.ref_name AND $referral.ref_name ne 'guest'}
                        {$referral.ref_name|escape:'htmlall':'UTF-8'}
                    {else}
                        {l s='Guest Referral' mod='affiliates'}
                    {/if}
                </td>
                <td class="left">
                    {if $referral.email AND $referral.ref_name ne 'guest'}
                        {$referral.email|escape:'htmlall':'UTF-8'}
                    {else}
                        -
                    {/if}
                </td>
                <td class="center"><span class="badge">{$referral.visits|escape:'htmlall':'UTF-8'}</span></td>
                <td class="center">{$referral.valid_orders|escape:'htmlall':'UTF-8'}</td>
                <td class="left">
                    <span class="badge badge-success">{convertPrice price=$referral.total_purchase|escape:'htmlall':'UTF-8'|floatval}</span>
                </td>
                <td class="left">{$referral.date_add|escape:'htmlall':'UTF-8'}</td>
                <td class="left">{$referral.source|escape:'htmlall':'UTF-8'}</td>
            </tr>
        {/foreach}
    {else}
        <tr>
            <td class="center hint-block help-block" colspan="5" style="display:table-cell;color:#999;">{l s='This affiliated cutomer has no referrals.' mod='affiliates'}</td>
        </tr>
    {/if}    
    </tbody>
</table>

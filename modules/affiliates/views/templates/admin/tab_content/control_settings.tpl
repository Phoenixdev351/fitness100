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

<div class="form-group margin-form">
    <label class="form-group control-label col-lg-4">
        <span class="label-tooltip" data-toggle="tooltip">{l s='Auto Approval' mod='affiliates'}</span>
    </label>
    <div class="col-lg-8">
        {if $ps_version < 1.6}
            <label for="AFFILIATE_AUTO_APPROVAL_on" class="t">
                <input type="radio" value="1" id="AFFILIATE_AUTO_APPROVAL_on" name="AFFILIATE_AUTO_APPROVAL" {if isset($AFFILIATE_AUTO_APPROVAL) AND $AFFILIATE_AUTO_APPROVAL == 1}checked="checked"{/if}>
                {l s='Yes' mod='affiliates'}
            </label>
            
            <label for="AFFILIATE_AUTO_APPROVAL_off" class="t">
                <input type="radio" value="0" id="AFFILIATE_AUTO_APPROVAL_off" name="AFFILIATE_AUTO_APPROVAL" {if isset($AFFILIATE_AUTO_APPROVAL) AND $AFFILIATE_AUTO_APPROVAL == 0}checked="checked"{/if}>
                {l s='No' mod='affiliates'}
            </label>
        {else}
            <span class="switch prestashop-switch fixed-width-lg">
                <input type="radio" {if isset($AFFILIATE_AUTO_APPROVAL) AND $AFFILIATE_AUTO_APPROVAL == 1}checked="checked"{/if} value="1" id="AFFILIATE_AUTO_APPROVAL_on" name="AFFILIATE_AUTO_APPROVAL">
                <label for="AFFILIATE_AUTO_APPROVAL_on" class="t">{l s='Yes' mod='affiliates'}</label>
                <input type="radio" value="0" {if isset($AFFILIATE_AUTO_APPROVAL) AND $AFFILIATE_AUTO_APPROVAL == 0}checked="checked"{/if} id="AFFILIATE_AUTO_APPROVAL_off" name="AFFILIATE_AUTO_APPROVAL">
                <label for="AFFILIATE_AUTO_APPROVAL_off" class="t">{l s='No' mod='affiliates'}</label>
                <a class="slide-button btn"></a>
            </span>
        {/if}
        <p class="hint-block help-block">{l s='Auto approval Affiliate requests.' mod='affiliates'}</p>
    </div>
</div>

<div class="form-group">
    <label class="control-label col-lg-4">
        <span title="" data-html="true" data-toggle="tooltip" class="label-tooltip" data-original-title="{l s='Selected groups will be able to enroll into affiliate program' mod='affiliates'}">{l s='Affiliation program group access' mod='affiliates'}</span>
    </label>
    <div class="col-lg-8">
        <div class="{if $ps_version >= 1.6}row{/if}">
            <div class="col-lg-8">
                <table class="table table-bordered panel">
                    <thead>
                        <tr>
                            <th class="fixed-width-xs">
                                <span class="title_box">
                                    <input type="checkbox" onclick="checkDelBoxes(this.form, 'affiliate_groups[]', this.checked)" id="checkme" name="checkme">
                                </span>
                            </th>
                            <th class="fixed-width-xs"><span class="title_box">{l s='ID' mod='affiliates'}</span></th>
                            <th>
                                <span class="title_box">{l s='Group name' mod='affiliates'}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                    {if isset($groups) AND $groups}
                        {foreach from=$groups item=group}
                        <tr>
                            <td>
                                <input type="checkbox" value="{$group.id_group|escape:'htmlall':'UTF-8'}" id="affiliate_groups_{$group.id_group|escape:'htmlall':'UTF-8'}" class="affiliate_groups" name="affiliate_groups[]" {if isset($affiliate_groups) AND $affiliate_groups AND $affiliate_groups AND in_array($group.id_group, $affiliate_groups)}checked="checked"{/if}>
                            </td>
                            <td>{$group.id_group|escape:'htmlall':'UTF-8'}</td>
                            <td>
                                <label for="affiliate_groups_{$group.id_group|escape:'htmlall':'UTF-8'}">{$group.name|escape:'htmlall':'UTF-8'}</label>
                            </td>
                        </tr>
                        {/foreach}
                    {/if}
                    </tbody>
                </table>
                <p class="help-block hint-block margin-form">{l s='Selected groups will be able to enroll into affiliate program' mod='affiliates'}</p>
            </div>
        </div>
    </div>
</div>
<div class="clearfix"></div><br/>

<div class="form-group">
    <label class="control-label col-lg-4">
        <span title="" data-html="true" data-toggle="tooltip" class="label-tooltip" data-original-title="{l s='Selected groups will be able to enroll into affiliate program' mod='affiliates'}">{l s='Statuses to approve Order rewards' mod='affiliates'}</span>
    </label>
    <div class="col-lg-8">
        <div class="{if $ps_version >= 1.6}row{/if}">
            <div class="col-lg-8">
                <table class="table table-bordered panel">
                    <thead>
                        <tr>
                            <th class="fixed-width-xs">
                                <span class="title_box">
                                    <input type="checkbox" onclick="checkDelBoxes(this.form, 'approval_states[]', this.checked)" id="checkme" name="checkme">
                                </span>
                            </th>
                            <th>
                                <span class="title_box">{l s='Order Status' mod='affiliates'}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                    {if isset($states) AND $states}
                        {foreach from=$states item=state}
                        <tr>
                            <td>
                                <input type="checkbox" value="{$state.id_order_state|escape:'htmlall':'UTF-8'}" id="affiliate_groups_{$state.id_order_state|escape:'htmlall':'UTF-8'}" class="approval_states" name="approval_states[]" {if isset($approval_states) AND $approval_states AND $approval_states AND in_array($state.id_order_state, $approval_states)}checked="checked"{/if}>
                            </td>
                            <td>
                                <label for="affiliate_groups_{$state.id_order_state|escape:'htmlall':'UTF-8'}">{$state.name|escape:'htmlall':'UTF-8'}</label>
                            </td>
                        </tr>
                        {/foreach}
                    {/if}
                    </tbody>
                </table>
                <p class="help-block hint-block margin-form">{l s='Reward will be given to affiliate(s) if status of a referral\'s order is changed to one of the above selected status' mod='affiliates'}</p>
            </div>
        </div>
    </div>
</div>
<div class="clearfix"></div><br/>

<div class="form-group">
    <label class="control-label col-lg-4">
        <span title="" data-html="true" data-toggle="tooltip" class="label-tooltip" data-original-title="{l s='Selected groups will be able to enroll into affiliate program' mod='affiliates'}">{l s='Statuses to cancel Order rewards' mod='affiliates'}</span>
    </label>
    <div class="col-lg-8">
        <div class="{if $ps_version >= 1.6}row{/if}">
            <div class="col-lg-8">
                <table class="table table-bordered panel">
                    <thead>
                        <tr>
                            <th class="fixed-width-xs">
                                <span class="title_box">
                                    <input type="checkbox" onclick="checkDelBoxes(this.form, 'cancel_states[]', this.checked)" id="checkme" name="checkme">
                                </span>
                            </th>
                            <th>
                                <span class="title_box">{l s='Order Status' mod='affiliates'}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                    {if isset($states) AND $states}
                        {foreach from=$states item=state}
                        <tr>
                            <td>
                                <input type="checkbox" value="{$state.id_order_state|escape:'htmlall':'UTF-8'}" id="affiliate_groups_{$state.id_order_state|escape:'htmlall':'UTF-8'}" class="cancel_states" name="cancel_states[]" {if isset($cancel_states) AND $cancel_states AND $cancel_states AND in_array($state.id_order_state, $cancel_states)}checked="checked"{/if}>
                            </td>
                            <td>
                                <label for="affiliate_groups_{$state.id_order_state|escape:'htmlall':'UTF-8'}">{$state.name|escape:'htmlall':'UTF-8'}</label>
                            </td>
                        </tr>
                        {/foreach}
                    {/if}
                    </tbody>
                </table>
                <p class="help-block hint-block margin-form">{l s='Reward will be cancelled if status of a referral\'s order is changed to one of the above selected status' mod='affiliates'}</p>
            </div>
        </div>
    </div>
</div>
<div class="clearfix"></div><br/>
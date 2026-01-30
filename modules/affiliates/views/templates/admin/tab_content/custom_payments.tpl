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

{if isset($payment_methods) AND $payment_methods}
<div class="form-group panel">
    <div class="form-group">
	    <label class="control-label col-lg-3">{l s='Custom Payments' mod='affiliates'}
	    </label>
	    <div class="col-lg-8">
	        <div class="{if $ps_version >= 1.6}row{/if}">
	            <div class="col-lg-8">
	                <table class="table table-bordered panel">
	                    <thead>
	                        <tr>
	                            <th class="fixed-width-xs">
	                                <span class="title_box">
	                                    <input type="checkbox" onclick="checkDelBoxes(this.form, 'selected_payments[]', this.checked)" id="custompayments-checkme" name="custompayments-checkme">
	                                </span>
	                            </th>
	                            <th>
	                                <span class="title_box">{l s='Payment Method' mod='affiliates'}</span>
	                            </th>
                                <th>
	                                <span class="title_box">{l s='Actions' mod='affiliates'}</span>
	                            </th>
	                        </tr>
	                    </thead>
	                    <tbody>
	                        {foreach from=$payment_methods item=pm}
	                        <tr>
	                            <td>
	                                <input type="checkbox" value="{$pm.id_payment_method|escape:'htmlall':'UTF-8'}" id="payment_method_{$pm.id_payment_method|escape:'htmlall':'UTF-8'}" class="selected_payments" name="selected_payments[]" {if isset($selected_payments) AND $selected_payments AND in_array($pm.id_payment_method, $selected_payments)}checked="checked"{/if}>
	                            </td>
	                            <td>
	                                <label for="payment_method_{$pm.id_payment_method|escape:'htmlall':'UTF-8'}">
		                                {$pm.payment_name|escape:'htmlall':'UTF-8'}
		                            </label>
	                            </td>
                                <td>
                                    <a class="btn btn-default" href="{$action_url}&editPayment&id_payment_method={$pm.id_payment_method|escape:'htmlall':'UTF-8'}"><i class="icon-pencil"></i> {l s='Edit' mod='affiliates'}</a>
                                    <a class="btn btn-danger" href="{$action_url}&deletePayment&id_payment_method={$pm.id_payment_method|escape:'htmlall':'UTF-8'}"><i class="icon-trash"></i> {l s='Remove' mod='affiliates'}</a>
	                            </td>
	                        </tr>
	                        {/foreach}
	                    </tbody>
	                </table>
	            </div>
	        </div>
	    </div>
	</div>
</div>
{/if}
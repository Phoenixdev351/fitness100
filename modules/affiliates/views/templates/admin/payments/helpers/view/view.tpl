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
{extends file="helpers/view/view.tpl"}

{block name="override_tpl"}
<script type="text/javascript">
var url = "{$link->getAdminLink('AdminPayments')|escape:'htmlall':'UTF-8'}";
var conf_text = "{l s='You are just about to process a PayPal payment on your behalf. Note that this will transfer money from your account into the selected affiliate\'s account. Double check what you are doing. Please be patient during loading.' mod='affiliates' js=1}";

function change_status(id_payment, status, action)
{
    if (status == 'accepted' && action == 'payNow')
    {
        var agree = confirm(conf_text);

        if (!agree)
            return false;
    }
    $('.payment_process').show();
    var ajaxRequest = {
        url : htmlEncode(url),
        dataType: 'json',
        data : {
            ajax : 1,
            status : status,
            action : action,//'payNow'
            id_affiliate_payment : id_payment,
        },
        success: function(data)
        {
            $('.payment_process').hide();
            if (data)
            {
                $('#id_row_'+ data.rid).remove();
                if (data.res == true)
                    showSuccessMessage(data.message);
                if (data.res == false)
                    showErrorMessage(data.message);
            }
        },
        error: function(jqXHR, textStatus, errorThrown)
        {
            $('.payment_process').hide();
            alert(textStatus);
        }
    };
    $.ajax(ajaxRequest);
}

function changeBulkStatus(new_status)
{
    $("#withdraw-request-form").attr('action', htmlEncode(url) + '&changeBulkStatus&bulk_status='+ new_status)
    $("#withdraw-request-form").submit();
}
function htmlEncode(input)
{
    return String(input).replace(/&amp;/g, '&');
}
</script>
<div class="panel">
    <h4 class="panel-heading"><i class="icon-money"></i> {l s='Withdraw Requests' mod='affiliates'}</h4>
    <div class="table-responsive-row clearfix">
         <form id="withdraw-request-form" method="POST" enctype="multipart/form-data">
            <table class="table affiliate_payment" width="100%">
                <thead>
                    <tr class="nodrag nodrop">
                        <th class="fixed-width-xs">
                            <span class="title_box">
                                <input type="checkbox" onclick="checkDelBoxes(this.form, 'withdraw_requests[]', this.checked)" id="checkme" name="checkme">
                            </span>
                        </th>

                        <th><span class="title_box active">{l s='ID' mod='affiliates'}</span></th>

                        <th><span class="title_box">{l s='Requested Amount' mod='affiliates'}</span></th>

                        <th><span class="title_box">{l s='Requested Date' mod='affiliates'}</span></th>

                        <th><span class="title_box">{l s='Payment Method' mod='affiliates'}</span></th>

                        <th><span class="title_box">{l s='Payment Details' mod='affiliates'}</span></th>

                        <th class="center"><span class="title_box center">{l s='Status' mod='affiliates'}</span></th>

                        <th class="center"><span class="title_box center">{l s='Action' mod='affiliates'}</span></th>
                    </tr>
                </thead>
                <tbody>
                {if isset($wd_requests) AND $wd_requests}
                    {foreach from=$wd_requests item=request}
                    <tr id="id_row_{$request.id_affiliate_payment|escape:'htmlall':'UTF-8'}">
                        <td>
                            <input type="checkbox" value="{$request.id_affiliate_payment|escape:'htmlall':'UTF-8'}" id="withdraw_request_{$request.id_affiliate_payment|escape:'htmlall':'UTF-8'}" class="withdraw_requests" name="withdraw_requests[]">
                        </td>

                        <td>{$request.id_affiliate_payment|escape:'htmlall':'UTF-8'}</td>

                        <td>{convertPrice price=$request.requested_amount|escape:'htmlall':'UTF-8'|floatval}</td>

                        <td>{$request.requested_date|escape:'htmlall':'UTF-8'}</td>

                         <td class="center">
                            {if $request.type == 1}
                                <span class="badge badge-success">{l s='Paypal' mod='affiliates'}</span>
                            {elseif $request.type == 2}
                                <span class="badge">{l s='Bank ACH' mod='affiliates'}</span>
                            {elseif $request.type == 3}
                                <span class="badge">{l s='Wire Transfer' mod='affiliates'}</span>
                            {elseif PaymentMethod::isPmExists($request.type)}
                                {assign var='pm' value=PaymentMethod::isPmExists($request.type, true)}
                                <span class="badge">{$pm.payment_name|escape:'htmlall':'UTF-8'}</span>
                            {else}
                                --
                            {/if}
                        </td>

                        <td>{$request.details|escape:'htmlall':'UTF-8'}</td>

                        <td>
                            <select id="withdraw-rewuest" name="status" onchange="change_status({$request.id_affiliate_payment|escape:'htmlall':'UTF-8'}, $(this).val(), 'changeStatus')">
                                <option value="pending" {if $request.status AND $request.status == 'pending'}selected="selected"{/if}>{l s='Pending' mod='affiliates'}</option>
                                <option value="accepted" {if $request.status AND $request.status == 'accepted'}selected="selected"{/if}>{l s='Paid' mod='affiliates'}</option>
                                <option value="cancelled" {if $request.status AND $request.status == 'cancelled'}selected="selected"{/if}>{l s='Cancalled' mod='affiliates'}</option>
                            </select>
                        </td>

                        <td>
                            {if $request.type == 1 AND isset($PAYPAL_USERNAME) AND PAYPAL_USERNAME AND isset($PAYPAL_API_PASSWORD) AND PAYPAL_API_PASSWORD}

                                <a style="line-height: 1px; padding: 5px 20px; border-radius: 15px; border: 1px solid rgb(220, 168, 42); word-spacing: -3px; background: #e68a00 -moz-linear-gradient(center bottom , #e68a00 0%, #ffd758 52%) repeat scroll 0 0" onclick="change_status({$request.id_affiliate_payment|escape:'htmlall':'UTF-8'}, 'accepted','payNow')" href="javascript:void(0);">
                                    <span style="color: rgb(29, 70, 137); font-weight: bold; font-style: italic; font-size: 15px;">{l s='Pay' mod='affiliates'}</span>
                                    <span style="font-weight: bold; font-style: italic; font-size: 15px; color: rgb(0, 175, 240);">{l s='Now' mod='affiliates'}</span>
                                </a>
                            {else}
                                <center>--</center>
                            {/if}
                        </td>

                    </tr>
                    {/foreach}
                {/if}
                </tbody>
            </table>
        </form>
        <div class="payment_process" style="display:none;"></div>
        <div class="row">
            <div class="col-lg-6">
                <div class="btn-group bulk-actions dropup">
                    <button data-toggle="dropdown" class="button btn btn-default dropdown-toggle" type="button">
                        {l s='Bulk Status Change' mod='affiliates'} <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a onclick="changeBulkStatus('pending');" href="javascript:void(0);">{l s='Pending' mod='affiliates'}
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a onclick="changeBulkStatus('accepted');" href="javascript:void(0);">{l s='Paid' mod='affiliates'}
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a onclick="changeBulkStatus('cancelled');" href="javascript:void(0);">{l s='Cancalled' mod='affiliates'}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
{/block}
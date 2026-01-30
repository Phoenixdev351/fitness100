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
<div class="form-group panel">
<h3>{l s='Default Payments' mod='affiliates'}</h3>
<label class="control-label col-lg-4">
    <span title="{l s='Payment Method' mod='affiliates'}" data-html="true" data-toggle="tooltip" class="label-tooltip" data-original-title="{l s='Allow affiliate customers to withdraw rewards using selected payment method' mod='affiliates'}">{l s='Payment Method' mod='affiliates'}</span></label>                   
    <div class="col-lg-2"  style="float:left; margin-left:10px;white-space:nowrap">
        <div>
            <label class="t" for="PAYMENT_METHOD_1">
                <p>{l s='Paypal' mod='affiliates'}</p>
                <center class="imgm img-thumbnail btn btn-default button">
                    <img src="{$smarty.const.__PS_BASE_URI__|escape:'htmlall':'UTF-8'}modules/affiliates/views/img/ppal.png"/>
                </center>
            </label>
        </div>
        <span style="margin-left:5%;">
            <input type="checkbox" name="PAYMENT_METHOD[paypal]" id="PAYMENT_METHOD_1" value="1" {if isset($PAYMENT_METHOD) AND $PAYMENT_METHOD AND in_array(1, $PAYMENT_METHOD)}checked="checked"{/if}/>
        </span>
    </div>

    <div class="col-lg-2" style="float:left; margin-left:10px;white-space:nowrap">
        <div>
            <label class="t" for="PAYMENT_METHOD_2">
                <p>{l s='Bank Wire' mod='affiliates'}</p>
                <center class="imgm img-thumbnail btn btn-default button">
                    <img src="{$smarty.const.__PS_BASE_URI__|escape:'htmlall':'UTF-8'}modules/affiliates/views/img/bw.png" width="24"/>
                </center>
            </label>
        </div>
        <span style="margin-left:7%">
            <input type="checkbox" name="PAYMENT_METHOD[bankwire]" id="PAYMENT_METHOD_2" value="2" {if isset($PAYMENT_METHOD) AND $PAYMENT_METHOD AND in_array(2, $PAYMENT_METHOD)}checked="checked"{/if}/>
        </span>
    </div>
    
    <div class="col-lg-2" style="float:left; margin-left:10px;white-space:nowrap">
        <div>
            <label class="t" for="PAYMENT_METHOD_3">
                <p>{l s='Wire Transfer' mod='affiliates'}</p>
                <center class="imgm img-thumbnail btn btn-default button">
                    <img src="{$smarty.const.__PS_BASE_URI__|escape:'htmlall':'UTF-8'}modules/affiliates/views/img/wt.png" width="24"/>
                </center>
            </label>
        </div>
        <span style="margin-left:7%">
            <input type="checkbox" name="PAYMENT_METHOD[wiretransfer]" id="PAYMENT_METHOD_3" value="3" {if isset($PAYMENT_METHOD) AND $PAYMENT_METHOD AND in_array(3, $PAYMENT_METHOD)}checked="checked"{/if}/>
        </span>
    </div>
</div>
<div class="clearfix"></div><br/>
{include file="./api_settings.tpl"}
{include file="./wire_settings.tpl"}
{include file="./custom_payments.tpl"}
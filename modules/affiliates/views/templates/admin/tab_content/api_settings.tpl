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
<!-- API Settings tab -->
<div id="paypal-api-settings" class="well" style="{if isset($PAYMENT_METHOD) AND $PAYMENT_METHOD AND in_array(1, $PAYMENT_METHOD)}display:block;{else}display:none;{/if}">
   <!-- paypal API settings -->
    <fieldset>
        <div class="toolbarBox pageTitle">
            <h3 class="tab">
                &nbsp;<img width="16" src="{$smarty.const.__PS_BASE_URI__|escape:'htmlall':'UTF-8'}modules/affiliates/views/img/ppal.png" alt="" /> {l s='PayPal API Settings' mod='affiliates'}
            </h3>
        </div>
        <div id="paypal-settings-tab" class="from-group">
            <label class="control-label col-lg-4" for="PAYPAL_MODE_on">{l s='Mode' mod='affiliates'}</label>
            <div class="margin-form form-group col-lg-6">
                <!-- sandbox mode -->
                <input type="radio" name="PAYPAL_MODE" id="PAYPAL_MODE_on" value="0"{if isset($PAYPAL_MODE) AND $PAYPAL_MODE == 0} checked="checked"{/if} />
                <label for="PAYPAL_MODE_on" class="resetLabel t">{l s='Live' mod='affiliates'}</label>

                <!-- live mode -->
                <input type="radio" name="PAYPAL_MODE" id="PAYPAL_MODE_off" value="1"{if isset($PAYPAL_MODE) AND $PAYPAL_MODE == 1} checked="checked"{/if} />
                <label for="PAYPAL_MODE_off" class="resetLabel t">{l s='Test (Sandbox)' mod='affiliates'}</label>

                <p>{l s='Use the links below to retreive your PayPal API credentials:' mod='affiliates'}<br />
                    <a onclick="window.open(this.href, '1369346829804', 'width=415,height=530,toolbar=0,menubar=0,location=0,status=0,scrollbars=0,resizable=0,left=0,top=0');return false;" href="https://www.paypal.com/cgibin/webscr?cmd=_get-api-signature&generic-flow=true" class="paypal_usa-module-btn">{l s='Live Mode API' mod='affiliates'}</a>
                    &nbsp;&nbsp;&nbsp;//&nbsp;&nbsp;&nbsp;
                    <a onclick="window.open(this.href, '1369346829804','width=415,height=530,toolbar=0,menubar=0,location=0,status=0,scrollbars=0,resizable=0,left=0,top=0');return false;" href="https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_get-api-signature&generic-flow=true" class="paypal_usa-module-btn">{l s='Sandbox Mode API' mod='affiliates'}</a>
                </p>
            </div><div class="clearfix"></div>

            <label for="PAYPAL_EMAIL" class="control-label col-lg-4 required">{l s='PayPal Email:' mod='affiliates'}</label>
            <div class="margin-form form-group col-lg-6">
                <input type="text" name="PAYPAL_EMAIL" class="input-text" value="{if isset($PAYPAL_EMAIL) AND $PAYPAL_EMAIL}{$PAYPAL_EMAIL|escape:'htmlall':'UTF-8'}{/if}" /> {if $ps_version < 1.6}<sup>*</sup>{/if}
            </div><div class="clearfix"></div>

            <label for="PAYPAL_USERNAME" class="control-label col-lg-4 required">{l s='PayPal API Username:' mod='affiliates'}</label>
            <div class="margin-form form-group col-lg-6">
                <input type="text" name="PAYPAL_USERNAME" class="input-text" value="{if isset($PAYPAL_USERNAME) AND $PAYPAL_USERNAME}{$PAYPAL_USERNAME|escape:'htmlall':'UTF-8'}{/if}" /> {if $ps_version < 1.6}<sup>*</sup>{/if}
            </div><div class="clearfix"></div>

            <label for="PAYPAL_API_PASSWORD" class="control-label col-lg-4 required">{l s='PayPal API Password:' mod='affiliates'}</label>
            <div class="margin-form form-group col-lg-6">
                <input type="password" name="PAYPAL_API_PASSWORD" class="input-text" value="{if isset($PAYPAL_API_PASSWORD) AND $PAYPAL_API_PASSWORD}{$PAYPAL_API_PASSWORD|escape:'htmlall':'UTF-8'}{/if}" /> {if $ps_version < 1.6}<sup>*</sup>{/if}
            </div><div class="clearfix"></div>

            <label for="PAYPAL_API_SIGNATURE" class="control-label col-lg-4 required">{l s='PayPal API Signature:' mod='affiliates'}</label>
            <div class="margin-form form-group col-lg-6">
                <input type="password" name="PAYPAL_API_SIGNATURE" class="input-text" value="{if isset($PAYPAL_API_SIGNATURE) AND $PAYPAL_API_SIGNATURE}{$PAYPAL_API_SIGNATURE|escape:'htmlall':'UTF-8'}{/if}" /> {if $ps_version < 1.6}<sup>*</sup>{/if}
            </div><div class="clearfix"></div>

            <label for="PAYPAL_APP_ID" class="control-label col-lg-4 required">{l s='PAYPAL APP ID:' mod='affiliates'}</label>
            <div class="margin-form form-group col-lg-6">
                <input type="text" name="PAYPAL_APP_ID" class="input-text" value="{if isset($PAYPAL_APP_ID) AND $PAYPAL_APP_ID}{$PAYPAL_APP_ID|escape:'htmlall':'UTF-8'}{/if}" /> {if $ps_version < 1.6}<sup>*</sup>{/if}
                <p class="help-block hint-block margin-form">{l s='use Global APP ID \"APP-80W284485P519543T\" for sandbox mode only.' mod='affiliates'}</p>
            </div><div class="clearfix"></div>
        </div>
        <div class="clearfix"></div>
        <div class="small form-group margin-form"><sup style="color: red;">*</sup> {l s='Required fields' mod='affiliates'}</div>
    </fieldset>
    <div class="clearfix"></div>
</div>

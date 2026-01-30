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
<div id="wire-transfer-settings" class="well" style="{if isset($PAYMENT_METHOD) AND $PAYMENT_METHOD AND in_array(3, $PAYMENT_METHOD)}display:block;{else}display:none;{/if}">
   <!-- paypal API settings -->
    <fieldset>
        <div class="toolbarBox pageTitle">
            <h3 class="tab">
                &nbsp;<img width="16" src="{$smarty.const.__PS_BASE_URI__|escape:'htmlall':'UTF-8'}modules/affiliates/views/img/wt.png" alt="" /> {l s='Wire Transfer Settings' mod='affiliates'}
            </h3>
        </div>
        <div class="form-group">
            <label class="form-group control-label col-lg-4">{l s='Fee' mod='affiliates'}</label>
            <div class="col-lg-4">
                <div class="input-group fixed-width-lg">
                    <span class="input-group-addon">{$currency->iso_code|escape:'htmlall':'UTF-8'}</span>
                    <input name="WIRETRANS_FEE" type="text" value="{$WIRETRANS_FEE|escape:'htmlall':'UTF-8'}">
                </div>
                <p class="hint-block help-block">{l s='Leave zero OR empty for no service fee.' mod='affiliates'}</p>
            </div>
        </div>
        <div class="clearfix"></div>
    </fieldset>
    <div class="clearfix"></div>
</div>

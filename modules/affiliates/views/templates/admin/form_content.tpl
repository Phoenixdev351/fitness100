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
<script type="text/javascript">
//<![CDATA[
$(document).ready(function()
{
    $('input#PAYMENT_METHOD_1').click(function() {
        if(this.checked) {
            $('#paypal-api-settings').show();
            window.scrollTo(0, 1500);
        }
        else {
            $('#paypal-api-settings').hide();
        }
    });
    $('input#PAYMENT_METHOD_3').click(function() {
        if(this.checked) {
            $('#wire-transfer-settings').show();
            window.scrollTo(0, 1500);
        }
        else {
            $('#wire-transfer-settings').hide();
        }
    });
});
//]]>
</script>
<!-- Tab Content -->
<form id="affiliates_form"
    class="form-horizontal col-lg-10 panel"
    action="{$action_url|escape:'htmlall':'UTF-8'}&saveSettings"
    enctype="multipart/form-data"
    method="post" {if $ps_version < 1.6}style="margin-left: 145px;"{/if}>
    <div class="affiliate_loader" style="display: none"></div>
    <input type="hidden" id="currentFormTab" name="currentMenuTab" value="{if isset($currentMenuTab) AND $currentMenuTab}home{/if}" />
    <input type="hidden" id="subMenuTab" name="subMenuTab" value="{if isset($subMenuTab) AND $subMenuTab}home{/if}" />

    <div id="affiliates_home" class="affiliates_tab affiliatetab-pane">
        <h3> {l s='Dashboard' mod='affiliates'}</h3><div class="separation"></div>
        {include file="../admin/tab_content/dashboard.tpl"}
    </div>

    <div id="affiliates_general" class="affiliates_tab affiliatetab-pane">
        <h3 class="affiliatetab"> {l s='General Settings' mod='affiliates'}</h3><div class="separation"></div>
        {include file="../admin/tab_content/general_settings.tpl"}
    </div>
    <div id="affiliates_control" class="affiliates_tab affiliatetab-pane" style="display:none;">
        <h3 class="affiliatetab"> {l s='Affiliation and Reward Settings' mod='affiliates'}</h3><div class="separation"></div>
        {include file="../admin/tab_content/control_settings.tpl"}
    </div>
    <div id="affiliates_payments" class="affiliates_tab affiliatetab-pane" style="display:none;">
        <h3>
            {l s='Payment Settings' mod='affiliates'}
            <span class="panel-heading-action">
                <a class="list-toolbar-btn fancybox" id="add-pm-button" href="#payment-hidden-form" title="{l s='Add Payment Method' mod='affiliates'}" data-html="true">
                    <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="{l s='Add Payment Method' mod='affiliates'}">
                        <i class="process-icon-new "></i>
                    </span>
                </a>
            </span>
        </h3><div class="separation"></div>
       {include file="../admin/tab_content/payment_settings.tpl"}
    </div>
     <div id="affiliates_social" class="affiliates_tab affiliatetab-pane" style="display:none;">
        <h3 class="affiliatetab"> {l s='Social Sharing' mod='affiliates'}</h3><div class="separation"></div>
       {include file="../admin/tab_content/social_sharing.tpl"}
    </div>
    <div class="separation"></div>

    {if $multishop == 1 AND isset($shops) AND $shops}
        <div id="affiliates_shops" class="affiliates_tab affiliatetab-pane" style="display:none;">
            <h3 class="affiliatetab"><img src="{$smarty.const.__PS_BASE_URI__|escape:'htmlall':'UTF-8'}modules/affiliates/views/img/shop.png"/> {l s='Shop Association' mod='affiliates'}</h3>
            {include file="../admin/tab_content/shops.tpl"}
        </div><div class="clearfix"></div>
    {/if}

    {if $ps_version >= 1.6}
        <div class="panel-footer">
            <button class="btn btn-default pull-right" name="saveSettings" type="submit">
                <i class="process-icon-save"></i>
                {l s='Save Settings' mod='affiliates'}
            </button>
        </div>
    {else}
        <div style="text-align:center">
            <input type="submit" value="{l s='Save Settings' mod='affiliates'}" class="button" name="saveSettings"/>
        </div>
    {/if}
</form>

<div id="payment-hidden-form" style="display: none;">
    <div class="bootstrap" id="content" style="margin-left:0px;padding: 0px;">
        {$payment_form nofilter} <!-- html content -->
    </div>
</div>
<script type="text/javascript">
{if isset($smarty.get.editPayment) && isset($smarty.get.id_payment_method)}
  window.jQuery(document).ready(function() {
    $.fancybox.open('#payment-hidden-form');
  });
{/if}
$(function(){
  $('.fancybox').fancybox();
})
</script>
<div class="clearfix"></div>
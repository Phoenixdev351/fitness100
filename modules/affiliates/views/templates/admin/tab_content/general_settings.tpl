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
var iso = "{$iso_lang|escape:'htmlall':'UTF-8'}";
var pathCSS = "{$theme_dir|escape:'htmlall':'UTF-8'}";
var ad = "{$path|escape:'htmlall':'UTF-8'}";
var psVersion = parseFloat("{$ps_version|escape:'htmlall':'UTF-8'}");
var pspsVersionInt = parseInt("{$ps_version|escape:'htmlall':'UTF-8'|replace:'.':''}");
$(document).ready(function()
{
    hideOtherLanguage({$id_lang_default|escape:'htmlall':'UTF-8'});
    var file_not_found = "";
    var config = {
        selector: ".autoload_rte",
        theme: "advanced",
        plugins: "advlist autolink lists link image charmap print preview hr anchor pagebreak searchreplace visualblocks visualchars code fullscreen insertdatetime media nonbreaking save table contextmenu directionality emoticons template paste textcolor colorpicker wordcount",
        toolbar1: "insertfile undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify textcolor colorpicker | code bullist numlist outdent indent | link image media table print",
        toolbar2: "forecolor backcolor emoticons | preview",
        image_advtab: true,
    };

    if (pspsVersionInt >= 17 && pspsVersionInt < 178) {
      tinySetup(config);
    } else {
      tinySetup();
    }
});

function hideOtherLanguage(id)
{
    $('.translatable-field').hide();
    $('.lang-' + id).show();

    var id_old_language = id_language;
    id_language = id;

    if (id_old_language != id)
        changeEmployeeLanguage();

    updateCurrentText();
}
function changeEmployeeLanguage()
{
    if (typeof allowEmployeeFormLang !== 'undefined' && allowEmployeeFormLang)
        $.post("index.php", {
            action: 'formLanguage',
            tab: 'AdminEmployees',
            ajax: 1,
            token: employee_token,
            form_language_id: id_language
        });
}
function updateCurrentText()
{
    $('#current_product').html($('#name_' + id_language).val());
}
</script>
<div id="conf_id_AFFILIATE_CONDITION">
<label class="form-group control-label col-lg-4">
    <span data-html="true" data-original-title="{l s='Choose the CMS page which contains contains terms and conditions for affiliate program' mod='affiliates'}" class="label-tooltip" data-toggle="tooltip" title="">{l s='CMS page for the Conditions of use' mod='affiliates'}
    </span>
</label>
<div class="col-lg-8 form-group margin-form">
    {if isset($cms_tabs) AND $cms_tabs}
        <select id="AFFILIATE_CONDITION" name="AFFILIATE_CONDITION" class="form-control fixed-width-xxl ">
            {foreach from=$cms_tabs item=tab}
                <option value="{$tab.id|escape:'htmlall':'UTF-8'}" {if Configuration::get('AFFILIATE_CONDITION') AND (Configuration::get('AFFILIATE_CONDITION') == $tab.id)} selected="selected"{/if}>{$tab.name|escape:'htmlall':'UTF-8'}</option>
            {/foreach}
        </select>
    {else}
        <div class="alert alert-warning warning">{l s='There is no cms page available. Please add a cms page first.' mod='affiliates'}</div>
    {/if}
</div>
</div>

<!-- min amount for withdraw -->
<label class="form-group control-label col-lg-4">
    <span data-html="true" data-original-title="{l s='Affiliate customer can only withdraw amount if available balance is equal to specied amount.' mod='affiliates'}" class="label-tooltip" data-toggle="tooltip" title="">{l s='Minimum amount to allow withdarw rewards' mod='affiliates'}
    </span>
</label>
<div class="col-lg-8 form-group margin-form">
    <div class="col-lg-4">
        <div class="input-group fixed-width-lg">
            <span class="input-group-addon">{$currency->iso_code|escape:'htmlall':'UTF-8'}</span>
            <input name="MINIMUM_AMOUNT" type="text" value="{if isset($MINIMUM_AMOUNT) AND $MINIMUM_AMOUNT}{$MINIMUM_AMOUNT|escape:'htmlall':'UTF-8'}{/if}">
        </div>
    </div>
</div>
<div class="clearfix"></div>

<!-- delay time -->
<label class="form-group control-label col-lg-4">
    <span data-html="true" data-original-title="{l s='Reward amount will be available for withdraw after specified time' mod='affiliates'}" class="label-tooltip" data-toggle="tooltip" title="">{l s='Payment holding time' mod='affiliates'}
    </span>
</label>
<div class="col-lg-8 form-group">
    <div class="col-lg-6" {if $ps_version < 1.6}style="float:left!important;"{/if}>
        <input name="PAYMENT_DELAY_TIME" type="text" value="{if isset($PAYMENT_DELAY_TIME) AND $PAYMENT_DELAY_TIME}{$PAYMENT_DELAY_TIME|escape:'htmlall':'UTF-8'}{/if}">
    </div>
<div class="col-lg-2" {if $ps_version < 1.6}style="float:left!important;"{/if}>
    <select name="DELAY_TYPE">
        <option value="m" {if (Configuration::get('DELAY_TYPE') == 'm')} selected="selected"{/if}>{l s='Minute(s)' mod='affiliates'}</option>
        <option value="h" {if (Configuration::get('DELAY_TYPE') == 'h')} selected="selected"{/if}>{l s='Hour(s)' mod='affiliates'}</option>
        <option value="d" {if (Configuration::get('DELAY_TYPE') == 'd')} selected="selected"{/if}>{l s='Days(s)' mod='affiliates'}</option>
    </select>
</div>
</div>
<div class="clearfix"></div>

<!-- ref key length -->
<br>
<label class="form-group control-label col-lg-4">
    <span data-html="true" data-original-title="{l s='Lenght of referral key' mod='affiliates'}" class="label-tooltip" data-toggle="tooltip" title="">{l s='Referal Key length' mod='affiliates'}
    </span>
</label>
<div class="col-lg-8 form-group">
    <div class="col-lg-6">
        <input name="REFERAK_KEY_LEN" type="text" value="{if isset($REFERAK_KEY_LEN) AND $REFERAK_KEY_LEN}{$REFERAK_KEY_LEN|escape:'htmlall':'UTF-8'}{/if}">
    </div>
</div>
<div class="clearfix"></div>

<div class="form-group">
    <label class="control-label col-lg-4">
        <span data-html="true" data-original-title="{l s='customers with \"X\" orders will be eligible for affiliate program only.' mod='affiliates'}" class="label-tooltip" data-toggle="tooltip">{l s='No. of Orders to enroll in Affiliate Program' mod='affiliates'}
        </span>
    </label>
    <div class="col-lg-4">
        <input name="AFFILIATE_PROGRAM_ORDERS" type="text" value="{if isset($AFFILIATE_PROGRAM_ORDERS) AND $AFFILIATE_PROGRAM_ORDERS}{$AFFILIATE_PROGRAM_ORDERS|escape:'htmlall':'UTF-8'}{/if}">
        <p class="help-block">{l s='leave emmpty or set 0 to disable this option.' mod='affiliates'}</p>
    </div>
</div>
<div class="clearfix"></div>

<div class="form-group margin-form">
    <label class="control-label col-lg-4">
        <span class="label-tooltip" data-toggle="tooltip">{l s='Enable reward for orders with vouchers' mod='affiliates'}</span>
    </label>
	<div class="col-lg-8">
		{if $ps_version < 1.6}
			<label for="REFERRAL_REWARD_VORDERS_on" class="t">
				<input type="radio" value="1" id="REFERRAL_REWARD_VORDERS_on" name="REFERRAL_REWARD_VORDERS" {if isset($REFERRAL_REWARD_VORDERS) AND $REFERRAL_REWARD_VORDERS == 1}checked="checked"{/if}>
				{l s='Yes' mod='affiliates'}
			</label>

			<label for="REFERRAL_REWARD_VORDERS_off" class="t">
				<input type="radio" value="0" id="REFERRAL_REWARD_VORDERS_off" name="REFERRAL_REWARD_VORDERS" {if isset($REFERRAL_REWARD_VORDERS) AND $REFERRAL_REWARD_VORDERS == 0}checked="checked"{/if}>
				{l s='No' mod='affiliates'}
			</label>
		{else}
			<span class="switch prestashop-switch fixed-width-lg">
				<input type="radio" {if isset($REFERRAL_REWARD_VORDERS) AND $REFERRAL_REWARD_VORDERS == 1}checked="checked"{/if} value="1" id="REFERRAL_REWARD_VORDERS_on" name="REFERRAL_REWARD_VORDERS">
				<label for="REFERRAL_REWARD_VORDERS_on" class="t">{l s='Yes' mod='affiliates'}</label>
				<input type="radio" value="0" {if isset($REFERRAL_REWARD_VORDERS) AND $REFERRAL_REWARD_VORDERS == 0}checked="checked"{/if} id="REFERRAL_REWARD_VORDERS_off" name="REFERRAL_REWARD_VORDERS">
				<label for="REFERRAL_REWARD_VORDERS_off" class="t">{l s='No' mod='affiliates'}</label>
				<a class="slide-button btn"></a>
			</span>
            <p class="help-block">{l s='include reward on orders containing a voucher code.' mod='affiliates'}</p>
		{/if}
	</div>
</div>

<div class="form-group margin-form">
    <label class="control-label col-lg-4">
        <span class="label-tooltip" data-toggle="tooltip">{l s='Enable reward for products with specific price' mod='affiliates'}</span>
    </label>
	<div class="col-lg-8">
		{if $ps_version < 1.6}
			<label for="REFERRAL_REWARD_SPPRODUCTS_on" class="t">
				<input type="radio" value="1" id="REFERRAL_REWARD_SPPRODUCTS_on" name="REFERRAL_REWARD_SPPRODUCTS" {if isset($REFERRAL_REWARD_SPPRODUCTS) AND $REFERRAL_REWARD_SPPRODUCTS == 1}checked="checked"{/if}>
				{l s='Yes' mod='affiliates'}
			</label>

			<label for="REFERRAL_REWARD_SPPRODUCTS_off" class="t">
				<input type="radio" value="0" id="REFERRAL_REWARD_SPPRODUCTS_off" name="REFERRAL_REWARD_SPPRODUCTS" {if isset($REFERRAL_REWARD_SPPRODUCTS) AND $REFERRAL_REWARD_SPPRODUCTS == 0}checked="checked"{/if}>
				{l s='No' mod='affiliates'}
			</label>
		{else}
			<span class="switch prestashop-switch fixed-width-lg">
				<input type="radio" {if isset($REFERRAL_REWARD_SPPRODUCTS) AND $REFERRAL_REWARD_SPPRODUCTS == 1}checked="checked"{/if} value="1" id="REFERRAL_REWARD_SPPRODUCTS_on" name="REFERRAL_REWARD_SPPRODUCTS">
				<label for="REFERRAL_REWARD_SPPRODUCTS_on" class="t">{l s='Yes' mod='affiliates'}</label>
				<input type="radio" value="0" {if isset($REFERRAL_REWARD_SPPRODUCTS) AND $REFERRAL_REWARD_SPPRODUCTS == 0}checked="checked"{/if} id="REFERRAL_REWARD_SPPRODUCTS_off" name="REFERRAL_REWARD_SPPRODUCTS">
				<label for="REFERRAL_REWARD_SPPRODUCTS_off" class="t">{l s='No' mod='affiliates'}</label>
				<a class="slide-button btn"></a>
			</span>
            <p class="help-block">{l s='include reward on products having a specific price.' mod='affiliates'}</p>
		{/if}
	</div>
</div>


<!-- Welcome text/content -->
<label class="form-group control-label col-lg-4">
    <span data-html="true" data-original-title="{l s='This message/content will be displayed to referral on landing page' mod='affiliates'}" class="label-tooltip" data-toggle="tooltip" title="">{l s='Welcome message' mod='affiliates'}</span>
</label>
<div class="col-lg-8 form-group margin-form">
    <div class="col-lg-12">
        {include
        file="./textarea_lang.tpl"
        languages=$languages
        input_name='referral_welcom_msg'
        class="autoload_rte rte"
        input_value=$referral_welcom_msg.referral_welcom_msg}
    </div>
    <div class="clearfix"></div>
</div>

<!-- discount section -->
{include file="./discount.tpl"}
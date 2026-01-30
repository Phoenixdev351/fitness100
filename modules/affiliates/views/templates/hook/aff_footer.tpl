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
{if isset($errors) AND count($errors) > 0}
<script type="text/javascript">
//<![CDATA[
$(document).ready(function()
{
	$.fancybox("<div id='affiliation-program' class='clearfix'></div>"+
					"<div class='alert alert-danger error'>"+
						"<ol>"+
							"{foreach from=$errors key=k item=error}"+
								"<li>{$error|escape:'htmlall':'UTF-8'}</li>"+
							"{/foreach}"+
						"</ol>"+
					"</div>"+
			"</div>");
})
//]]>
</script>
{elseif $result == 1}
<script type="text/javascript">
//<![CDATA[
var discount = '';
var code = "{$code|escape:'htmlall':'UTF-8'}";
var signup_label = "{l s='Signup and get' mod='affiliates' js=1}";
var signup = "{l s='Sign Up' mod='affiliates' js=1}";
var discount_label = "{l s='Discount on your first order' mod='affiliates' js=1}";
var code_label = "{l s='Use this coupon code to redeem your discount.' mod='affiliates' js=1}";
var signup_link = "{$link->getPageLink('my-account', true)|escape:'htmlall':'UTF-8'}";
var discount_type = "{if isset($discount_type) && $discount_type}{$discount_type|escape:'htmlall':'UTF-8'}{/if}";
if (discount_type == 'percent') {
	discount = "<span style='font-weight: bold; color: rgb(207, 25, 24); vertical-align: middle; font-size: 14px; font-style: italic;'>{$discount|escape:'htmlall':'UTF-8'}&nbsp;%</span>";
} else if (discount_type == 'amount') {
	discount = "<span style='font-weight: bold; color: rgb(207, 25, 24); vertical-align: middle; font-size: 14px; font-style: italic;'>{convertPrice price=$discount|floatval|escape:'htmlall':'UTF-8'}</span>";
}
$(document).ready(function()
{
	$.fancybox('{if isset($code) AND $code}<div id="affiliation-program" class="clearfix"></div>'+
					'<div class="alert alert-success success">'+
						'<ol>'+ signup_label +'&nbsp;'+ discount +'&nbsp;'+ discount_label +'</ol>'+
					'</div>'+
				'</div>'+
				'<center style="padding: 5px;border: 1px solid #e4e4e4;background: #f6f6f6"><p>'+ code_label +'</p><h2>'+ code +'</h2></center>{/if}'+
				'{if isset($welcom_message) && $welcom_message}<div class="message_content">{$welcom_message|regex_replace:"/[\r\t\n]/":" "}{*html content, cannot escaped*}{/if}<center><p class="referral_signup clearfix">'+
				'<a class="button btn btn-default button-medium" href="'+ signup_link +'">'+
				'<span>'+ signup +'<i class="icon-chevron-right right"></i></span></a></p></center></div>');
})
//]]>
</script>
{literal}
{if $ps_version >= 1.6}
<style type="text/css">
.referral_signup .button-medium {
    border-radius: 4px;
    /*float: right;*/
    font-size: 20px;
    line-height: 24px;
}
.button.button-medium {
    -moz-border-bottom-colors: none;
    -moz-border-left-colors: none;
    -moz-border-right-colors: none;
    -moz-border-top-colors: none;
    background: #43b754 linear-gradient(to bottom, #43b754 0%, #42ac52 100%) repeat-x scroll 0 0;
    border-color: #399a49 #247f32 #1a6d27 #399a49;
    border-image: none;
    border-radius: 0;
    border-style: solid;
    border-width: 1px;
    color: #fff;
    font-size: 17px;
    font-weight: bold;
    line-height: 21px;
    padding: 0;
}
</style>
{/if}
{/literal}
{/if}
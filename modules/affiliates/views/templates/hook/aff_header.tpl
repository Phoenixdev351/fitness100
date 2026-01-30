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
var active_tab      = "{$active_tab|escape:'htmlall':'UTF-8'}";
var ok_label        = "{l s='Ok' mod='affiliates' js=1}";
var req_error_msg   = "{l s='You must agree to the terms and condidions of Affiliate Program.' mod='affiliates' js=1}";
var affCurrencySign    = "{$affCurrencySign|escape:'htmlall':'UTF-8'}";
var affCurrencyRate    = {$affCurrencyRate|floatval|escape:'htmlall':'UTF-8'};
var affCurrencyFormat  = {$affCurrencyFormat|intval|escape:'htmlall':'UTF-8'};
var affCurrencyBlank   = {$affCurrencyBlank|intval|escape:'htmlall':'UTF-8'};

var error = "<p class='error alert alert-danger'>{l s='Please select a Payment method.' mod='affiliates' js=1}</p>";
var min_error  = "<p class='warning alert alert-warning'>{l s='Please select an amount. Minimum amount to withdraw' mod='affiliates' js=1} : {Tools::displayPrice(Tools::convertPriceFull($MINIMUM_AMOUNT, null, $affcurrency))|escape:'htmlall':'UTF-8'}</p>";
var min_wd = "{($MINIMUM_AMOUNT * $affcurrency->conversion_rate)|escape:'htmlall':'UTF-8'|floatval}";
var affCurrencySign = "{$affcurrency->iso_code}";
//]]>
</script>

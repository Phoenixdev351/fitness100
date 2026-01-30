{**
* NOTICE OF LICENSE
*
* This source file is subject to a commercial license from Feed.Biz, Ltd.
* Use, copy, modification or distribution of this source file without written
* license agreement from Feed.Biz, Ltd. is strictly forbidden.
* In order to obtain a license, please contact us: contact@feed.biz
* ...........................................................................
* INFORMATION SUR LA LICENCE D'UTILISATION
*
* L'utilisation de ce fichier source est soumise a une licence commerciale
* concedee par la societe Feed.Biz, Ltd.
* Toute utilisation, reproduction, modification ou distribution du present
* fichier source sans contrat de licence ecrit de la part de la Feed.Biz, Ltd. est
* expressement interdite.
* Pour obtenir une licence, veuillez contacter Feed.Biz, Ltd. a l'adresse: contact@feed.biz
* ...........................................................................
* @package    Feed.Biz
* @author     Olivier B.
* @copyright  Copyright (c) 2011-2022 Feed.Biz - Hong Kong - Head Quarters - Room 1408, 14/F, Tak Shing House - Theatre Lane - 20 des Voeux Road Central, -Central, Hong Kong
* @license    Commercial license
* Support by mail  :  support@feed.biz
*}

<script type="text/javascript" src="https://s3.amazonaws.com/assets.freshdesk.com/widget/freshwidget.js"></script>

<script type="text/javascript">
    var requester="{$header.support_requester|escape:'html':'UTF-8'}";
    var support_subject="{$header.support_subject|escape:'html':'UTF-8'}";
    var support_ps_version="{$header.support_ps_version|escape:'html':'UTF-8'}";
    var support_module_version="{$header.support_module_version|escape:'html':'UTF-8'}";
    var support_site="{$header.support_site|escape:'html':'UTF-8'}";
    var support_product=parseInt({$header.support_product|escape:'html':'UTF-8'});

    {literal}
    FreshWidget.init("", {"queryString":
        "&widgetType=popup&searchArea=no&helpdesk_ticket[requester]="+requester
        +"&helpdesk_ticket[subject]="+support_subject
        +"&helpdesk_ticket[product]="+support_product
        +"&helpdesk_ticket[custom_field][version_prestashop_191912]="+support_ps_version
        +"&helpdesk_ticket[custom_field][version_module_191912]="+support_module_version
        +"&helpdesk_ticket[custom_field][site_191912]="+support_site,
        "utf8": "✓", "widgetType": "popup", "buttonType": "text", "buttonText": "Feed.biz / Support", "buttonColor": "white", "buttonBg": "#484848", "alignment": "3", "offset": "80%", "formHeight": "700px", "url": "https://support.feed.biz"} );
    {/literal}
</script>

<input type="hidden" id="id_lang" value="{$id_lang|intval}"/>
<input type="hidden" id="check_url" value="{$module_url|escape:'quotes':'UTF-8'}functions/check.php"/>
<input type="hidden" id="survey_url" value="{$module_url|escape:'quotes':'UTF-8'}functions/survey.php"/>
<input type="hidden" id="ps_token" value="{$ps_token|escape:'quotes':'UTF-8'}"/>
<script type="text/javascript" src="{$module_url|escape:'quotes':'UTF-8'}views/js/html2canvas.min.js"></script>
<script type="text/javascript" src="{$module_url|escape:'quotes':'UTF-8'}views/js/feedbiz.js"></script>
<script type="text/javascript" src="{$module_url|escape:'quotes':'UTF-8'}views/js/fselect.js"></script>
<link rel="stylesheet" type="text/css" href="{$module_url|escape:'quotes':'UTF-8'}views/css/feedbiz.css">
<link rel="stylesheet" type="text/css" href="{$module_url|escape:'quotes':'UTF-8'}views/css/fselect.css">

{if $branded_module == 'cdiscount'}
{*<div class="alert alert-info">
    <ul>
         <li>{l s='You must have EAN codes or an exemption. Talk to you Cdiscount representation for advice' mod='feedbiz'}.</li>
         <li>{l s='The more you bid on line, the more stock you have, and the more you sell!' mod='feedbiz'}</li>
         <li>{l s='Faster you ship, better are the evaluations, more you will sell !' mod='feedbiz'}</li>
     </ul>
     <br>
     <b style="padding-left:25px;">{l s='Do not hesitate to contact our support team in order to assist you in posting your offers online' mod='feedbiz'} : <a href="https://support.feed.biz" target="_blank">support@feed.biz</a></b>
</div>*}
{/if}

<!-- heading -->
<div style="margin-bottom: 30px;">
    {if $branded_module}
        {*<img src="{$images_url|escape:'quotes':'UTF-8'}feedbiz.png" alt="{l s='Feed.Biz' mod='feedbiz'}" style=""/>*}
        <img src="{$images_url|escape:'quotes':'UTF-8'}marketplaces/{$branded_module|escape:'htmlall':'UTF-8'}_flux.png" alt="{l s='Feed.Biz' mod='feedbiz'}" style="float:right; text-align:right;"/>
    {else}
        <img src="{$images_url|escape:'quotes':'UTF-8'}feedbiz.png" alt="{l s='Feed.Biz' mod='feedbiz'}" style="float:right; text-align:right;"/>
    {/if}
</div>
<div style="clear:both;padding-bottom:10px;"></div>
<!-- ! heading -->
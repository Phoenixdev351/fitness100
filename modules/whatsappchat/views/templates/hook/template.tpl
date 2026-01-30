{**
* NOTICE OF LICENSE
*
* This product is licensed for one customer to use on one installation (test stores and multishop included).
* Site developer has the right to modify this module to suit their needs, but can not redistribute the module in
* whole or in part. Any other use of this module constitues a violation of the user agreement.
*
* DISCLAIMER
*
* NO WARRANTIES OF DATA SAFETY OR MODULE SECURITY
* ARE EXPRESSED OR IMPLIED. USE THIS MODULE IN ACCORDANCE
* WITH YOUR MERCHANT AGREEMENT, KNOWING THAT VIOLATIONS OF
* PCI COMPLIANCY OR A DATA BREACH CAN COST THOUSANDS OF DOLLARS
* IN FINES AND DAMAGE A STORES REPUTATION. USE AT YOUR OWN RISK.
*
*  @author    idnovate.com <info@idnovate.com>
*  @copyright 2020 idnovate.com
*  @license   See above
*}

{if ($custom_js != '' && $from_bo != '1')}
<script>
    {$custom_js nofilter}
</script>
{/if}
{if ($custom_css != '' && $from_bo != '1')}
<style id="whatsappchat_custom_css" type="text/css">
    {$custom_css nofilter}
</style>
{/if}
{if version_compare($smarty.const._PS_VERSION_,'1.5','<')}
    {literal}
    <script>
    $(document).ready(function() {
        $("a.whatsappchat-anchor").click(function(event) {
            event.preventDefault();
            window.open($(this).attr("href"), this.target);
        });
    });
    </script>
    {/literal}
{/if}
{if $agents !== false && $from_bo != '1' && $offline_message == ''}
    <script>
        {if ($whatsapp_action === 'quickview' || $whatsapp_action === 1)}
            $('.jBox-wrapper').each(function(){
                $(this).remove();
            });
            $('.whatsappchat-agents-container').last().remove();
            setAgentsBox("{$whatsappchat_id|escape:'html':'UTF-8'}");
            $('#whatsappchat-agents{$whatsappchat_id|escape:'html':'UTF-8'}{if ($whatsapp_action === 'quickview' || $whatsapp_action === 1)}quickview{/if}').click(function(){
                if ($('.jBox-wrapper').size() > 1) {
                    $('.jBox-wrapper').last().remove();
                }
            });
        {else}
            {if version_compare($smarty.const._PS_VERSION_,'1.7','>=')}
                if (document.addEventListener) {
                    //window.addEventListener('load', setAgentsBox("{$whatsappchat_id|escape:'html':'UTF-8'}"), false);
                    window.addEventListener('load', setAgentsBox, false);
                } else {
                    //window.attachEvent('onload', setAgentsBox("{$whatsappchat_id|escape:'html':'UTF-8'}"));
                    window.attachEvent('onload', setAgentsBox);
                }
            {else if version_compare($smarty.const._PS_VERSION_,'1.5','>=')}
                $(document).ready(function() {
                    setAgentsBox("{$whatsappchat_id|escape:'html':'UTF-8'}");
                });
            {/if}
        {/if}
        {literal}
        function setAgentsBox() {
            //var whatsappchat_id = $("div").data("whatsappchat-agent-id");
            var whatsappchat_id = "{/literal}{$whatsappchat_id|escape:'html':'UTF-8'}{literal}";
            var test = new jBox('Tooltip', {
                id: 'agent_box_' + whatsappchat_id,
                attach: '#whatsappchat-agents' + whatsappchat_id + '{/literal}{if ($whatsapp_action === 'quickview' || $whatsapp_action === 1)}quickview{/if}{literal}',
                position: {
                    x: 'center',
                    y: 'top'
                },
                content: $('.whatsappchat-agents-container' + whatsappchat_id + '{/literal}{if ($whatsapp_action === 'quickview' || $whatsapp_action === 1)}quickview{/if}{literal}'),
                trigger: 'click',
                animation: {open: 'move', close: 'move'},
                closeButton: true,
                closeOnClick: true,
                closeOnEsc: true,
                adjustPosition: true,
                adjustTracker: true,
                adjustDistance: {top: 45, right: 5, bottom: 5, left: 5},
                zIndex: 8000,
                preventDefault: true
            });
        }
        {/literal}
    </script>
    <div class="whatsappchat-agents-container {$whatsapp_theme|escape:'html':'UTF-8'} whatsappchat-agents-container{$whatsappchat_id|escape:'html':'UTF-8'}{if ($whatsapp_action === 'quickview' || $whatsapp_action === 1)}quickview{/if}" data-whatsappchat-agent-id="{$whatsappchat_id|escape:'html':'UTF-8'}" style="display: none;">
        <div class="whatsappchat-agents-title{if version_compare($smarty.const._PS_VERSION_,'1.7','>=')} whatsappchat-agents-title17{/if}" style="background-color: {$color|escape:'html':'UTF-8'}">{l s="Hi! Click one of our agents below and we will get back to you as soon as possible." mod='whatsappchat'}</div>
        <div class="whatsappchat-agents-content">
            {foreach $agents as $agent}
                <a href="{$agent.url|escape:'quotes'}" target="_blank" class="whatsappchat-agents-content-agent" rel="noopener noreferrer">
                    <div class="whatsappchat-agents-content-image">
                        <img src="{$agents_img_src|escape:'html':'UTF-8'}{$agent.image|escape:'html':'UTF-8'}" alt="{$agent.department|escape:'html':'UTF-8'} - {$agent.name|escape:'html':'UTF-8'}" referrerpolicy="no-referrer">
                    </div>
                    <div class="whatsappchat-agents-content-info{if version_compare($smarty.const._PS_VERSION_,'1.7','>=')} whatsappchat-agents-content-info17{/if}">
                        <span class="whatsappchat-agents-content-department">{$agent.department|escape:'html':'UTF-8'}</span>
                        <span class="whatsappchat-agents-content-name{if version_compare($smarty.const._PS_VERSION_,'1.7','>=')} whatsappchat-agents-content-name17{/if}">{$agent.name|escape:'html':'UTF-8'}</span>
                    </div>
                    <div class="clearfix"></div>
                </a>
            {/foreach}
        </div>
    </div>
{/if}
{if $whatsapp_class != 'floating'}
    {if $open_chat && $from_bo != '1' && $offline_link != ''}<a class="whatsappchat-anchor {$whatsapp_theme|escape:'html':'UTF-8'} whatsappchat-anchor{$whatsappchat_id|escape:'html':'UTF-8'}" href="{$offline_link|escape:'html':'UTF-8'}">{/if}
    {if $open_chat && $from_bo != '1' && $offline_message == ''}<a class="whatsappchat-anchor {$whatsapp_theme|escape:'html':'UTF-8'} whatsappchat-anchor{$whatsappchat_id|escape:'html':'UTF-8'}" target="_blank" {if $agents !== false && $from_bo != '1' && version_compare($smarty.const._PS_VERSION_,'1.5','>=')}href="javascript:void(0);" rel="nofollow noopener noreferrer" {else}href="{$url|escape:'html':'UTF-8'}" rel="noopener noreferrer"{/if}>{/if}
        <div class="whatsapp whatsapp_{$whatsappchat_id|escape:'html':'UTF-8'} whatsapp-{if isset($from_bo) && $from_bo != '1'}{$whatsapp_class|escape:'html':'UTF-8'} {$position|escape:'html':'UTF-8'}{/if}{if $offline_message != '' && ($whatsapp_class == 'topWidth' || $whatsapp_class == 'bottomWidth')} whatsapp-offline{/if}"
            {if $color != '' && ($whatsapp_class == 'topWidth' || $whatsapp_class == 'bottomWidth') && $from_bo != '1'}style="background-color: {$color|escape:'html':'UTF-8'}"{/if}>
            <span {if $color != ''}style="background-color: {$color|escape:'html':'UTF-8'}"{/if}{if $offline_message != ''} class="whatsapp-offline"{/if}{if $agents !== false && $from_bo != '1'} id="whatsappchat-agents{$whatsappchat_id|escape:'html':'UTF-8'}{if ($whatsapp_action === 'quickview' || $whatsapp_action === 1)}quickview{/if}"{/if}>
                <i class="whatsapp-icon" {if $button_text == ''}style="padding-right:0px!important;"{/if}></i>
                {if $offline_message != ''}{$offline_message|escape:'html':'UTF-8'}{else}{$button_text|escape:'html':'UTF-8'}{/if}
            </span>
        </div>
    {if $open_chat && $from_bo != '1' && $offline_message == ''}</a>{/if}
    {if $open_chat && $from_bo != '1' && $offline_link != ''}</a>{/if}
{else}
    {if $open_chat && $from_bo != '1' && $offline_message == ''}
        <a{if $agents !== false && $from_bo != '1'} id="whatsappchat-agents{$whatsappchat_id|escape:'html':'UTF-8'}{if ($whatsapp_action === 'quickview' || $whatsapp_action === 1)}quickview{/if}"{/if} target="_blank" href="{$url|escape:'html':'UTF-8'}" class="float {$whatsapp_theme|escape:'html':'UTF-8'} whatsapp_{$whatsappchat_id|escape:'html':'UTF-8'} float-{$position|escape:'html':'UTF-8'} float-{$whatsapp_class|escape:'html':'UTF-8'}{if $offline_message != ''} whatsapp-offline{/if}" style="background-color: {$color|escape:'html':'UTF-8'}" rel="noopener noreferrer">
    {/if}
    {if $open_chat && $from_bo != '1' && $offline_message != ''}
        <a class="float {$whatsapp_theme|escape:'html':'UTF-8'} float-{$position|escape:'html':'UTF-8'} float-{$whatsapp_class|escape:'html':'UTF-8'}{if $offline_message != ''} whatsapp-offline{/if}" {if $offline_link != ''}href="{$offline_link|escape:'html':'UTF-8'}"{/if} style="background-color: {$color|escape:'html':'UTF-8'}">
    {/if}
    {if $from_bo == '1'}
        <a class="float {$whatsapp_theme|escape:'html':'UTF-8'} float-floating floating-bo{if $offline_message != ''} whatsapp-offline{/if}" style="background-color: {$color|escape:'html':'UTF-8'}">
    {/if}
    <i class="whatsapp-icon{if version_compare($smarty.const._PS_VERSION_,'1.5','>=')}-3x{/if}" {if $button_text != ''}style="padding-right:0px!important;"{/if}></i>
    {if $from_bo == '1'}</a>{/if}
    {if $open_chat && $from_bo != '1'}</a>{/if}
    {if ($button_text != '' && $from_bo != '1') || ($offline_message != '' && $from_bo != '1')}
        <div class="label-container label-container-{$position|escape:'html':'UTF-8'} float-{$whatsapp_class|escape:'html':'UTF-8'}">
            {if (strpos($position, 'left') != false || $position == 'left')}
            <i class="icon icon-caret-left label-arrow" style="font-size: x-large;"></i>
            <div class="label-text">{if $offline_message != ''}{$offline_message|escape:'html':'UTF-8'}{else}{$button_text|escape:'html':'UTF-8'}{/if}</div>
            {else}
            <div class="label-text">{if $offline_message != ''}{$offline_message|escape:'html':'UTF-8'}{else}{$button_text|escape:'html':'UTF-8'}{/if}</div>
            <i class="icon icon-play label-arrow"></i>
            {/if}
        </div>
    {/if}
{/if}
{if !$from_bo && $whatsapp_class == 'hookDisplayWhatsAppProductSocialButtons'}
<style type="text/css">
    .social-sharing li.whatsapp-social-button a:hover {
        color: {$color|escape:'html':'UTF-8'};
    }
    .social-icon li.whatsapp-social-button a:hover {
        color: {$color|escape:'html':'UTF-8'};
    }
</style>
<script>
if (document.addEventListener) {
    window.addEventListener('load', setWhatsAppSocialButton, false);
} else {
    window.attachEvent('onload', setWhatsAppSocialButton);
}
function setWhatsAppSocialButton() {
    {if version_compare($smarty.const._PS_VERSION_,'1.7','>=')}
        if ($('li.whatsapp-social').length > 0) {
            return false;
        }
        var element_to_copy = $('div.social-sharing ul li').first().clone();
        var customSocialButtons = false;
        if (element_to_copy.length == 0) {
            element_to_copy = $('div.innovatorySocial-sharing ul li').first().clone();
            parentSocialButtonsElement = 'div.innovatorySocial-sharing ul';
            customSocialButtons = true;
        }
        if (element_to_copy.length == 0) {
            element_to_copy = $('div.social-icon ul li').first().clone();
            parentSocialButtonsElement = 'div.social-icon ul';
            customSocialButtons = true;
        }
        var custom_style = 'max-width: 24px;max-height: 24px;vertical-align: sub;';
        var whatsapp_svg = '<svg aria-hidden="true" focusable="false" data-prefix="fab" data-icon="whatsapp" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="svg-inline--fa fa-whatsapp fa-w-14 fa-lg" style="width: inherit;height: inherit;"><path fill="currentColor" d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z" class=""></path></svg>';
        var whatsapp_svg_width = element_to_copy.width();
        if (typeof element_to_copy === 'undefined') {
            $('.whatsapp-hookDisplayWhatsAppProductSocialButtons').show();
        } else {
            if (customSocialButtons === false) {
                element_to_copy.removeClass().addClass('{if $whatsapp_theme == 'AngarTheme'}whatsapp_custom {$whatsapp_theme|escape:'html':'UTF-8'}{else}fa-whatsapp{/if} icon-gray whatsapp-social {if $whatsapp_theme != 'AngarTheme'}whatsapp-social-button{/if}');//.css('background-color', '{$color|escape:'html':'UTF-8'}');
                {if $whatsapp_theme == 'AngarTheme'}
                    element_to_copy.css('background', '{$color|escape:'html':'UTF-8'}');
                {/if}
            } else {
                element_to_copy.addClass('whatsapp_{$whatsappchat_id|escape:'html':'UTF-8'}');
            }
            //element_to_copy.removeClass().addClass('icon-gray whatsapp-social-button-svg');//.css('background-color', '{$color|escape:'html':'UTF-8'}');
            element_to_copy.children().attr('href', "{$url nofilter}").attr('title', 'WhatsApp');
            //element_to_copy.children().append(whatsapp_svg);
            if ($('.whatsapp-social-button').length === 0) {
                if (customSocialButtons === false) {
                    $(element_to_copy).appendTo('div.social-sharing ul');
                    if ($('div.social-sharing ul li a i').length > 0) {
                        $('div.social-sharing ul li a i').last().removeClass().addClass('fa fa-whatsapp');
                    }
                    $(element_to_copy).appendTo('div.social-icon ul');
                    if ($('div.social-icon ul li a i').length > 0) {
                        $('div.social-icon ul li a i').last().removeClass().addClass('fa fa-whatsapp');
                    }
                } else {
                    element_to_copy.children().css('background-color', '{$color|escape:'html':'UTF-8'}').children().removeClass().addClass('fa fa-whatsapp');
                    $(element_to_copy).appendTo(parentSocialButtonsElement);
                }
            }
            if ($('i.fa-whatsapp').length > 0) {
                $('.fa-whatsapp.icon-gray.whatsapp-social-button').removeClass('fa-whatsapp').removeClass('whatsapp-social-button');
            }
        }
    {else}
        var element_to_copy16 = $('p.socialsharing_product button').first().clone();
        if (typeof element_to_copy16 === 'undefined') {
            $('.whatsapp-hookDisplayWhatsAppProductSocialButtons').show();
        } else {
            element_to_copy16.addClass('whatsapp-social-button');
            element_to_copy.children().attr('href', "{$url nofilter}").attr('title', 'WhatsApp');
            $(element_to_copy16).appendTo('p.socialsharing_product');
        }
    {/if}
}
</script>
{/if}
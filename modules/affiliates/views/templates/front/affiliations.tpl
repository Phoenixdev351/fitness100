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
{capture name=path}<a href="{$link->getPageLink('my-account', true)|escape:'htmlall':'UTF-8'}" title="{l s='Manage my account' mod='affiliates'}" rel="nofollow">{l s='My account' mod='affiliates'}</a><span class="navigation-pipe">{$navigationPipe|escape:'htmlall':'UTF-8'}</span>{l s='My Affiliations' mod='affiliates'}{/capture}
{include file="$tpl_dir./errors.tpl"}

{if $isAccess}
    <script type="text/javascript">
    //<![CDATA[
    var active_tab      = "{$active_tab|escape:'htmlall':'UTF-8'}";
    var ok_label        = "{l s='Ok' mod='affiliates' js=1}";
    var req_error_msg   = "{l s='You must agree to the terms and condidions of Affiliate Program.' mod='affiliates' js=1}";
    var affCurrencySign    = "{$affCurrencySign|escape:'htmlall':'UTF-8'}";
    var affCurrencyRate    = {$affCurrencyRate|floatval|escape:'htmlall':'UTF-8'};
    var affCurrencyFormat  = {$affCurrencyFormat|intval|escape:'htmlall':'UTF-8'};
    var affCurrencyBlank   = {$affCurrencyBlank|intval|escape:'htmlall':'UTF-8'};
    $(document).ready(function()
    {
        $('.read-conditions').fancybox({
            'hideOnContentClick': false
        });

        $('#sendAffiliateRequest').click(function(e)
        {
            e.preventDefault();
            e.stopImmediatePropagation();
            var html = '<div class="error alert alert-danger">'+ req_error_msg +'</div>'
            if ($('#affiliationTerms').is(':checked') == false)
                fancyCloseBox(html);
            else
                $('#affiliation-request').submit();
        });

        $('.affiliation_tabs li').click(function()
        {
            var selected_tab = $(this).children().attr('tab');
            $('.affiliation_heading').text($(this).text());
            $('.affiliation_tabs').each(function()
            {
                $(this).find('.selected').removeClass('selected');
            });

            $(this).addClass('selected');

            $('.tabcontents').each(function()
            {
                $(this).children().hide();
            });

            $('.tabcontents').find(selected_tab).show();

        });

        $('.affiliation_tabs li').find('a[tab="'+active_tab+'"]').trigger('click', function()
        {
            $('.affiliation_heading').text($(this).text());
            $('.affiliation_tabs').each(function()
            {
                $(this).find('.selected').removeClass('selected');
            });

            $(this).addClass('selected');

            $('.tabcontents').each(function()
            {
                $(this).children().hide();
            });

            $('.tabcontents').find(active_tab).show();
        });
    })

    function fancyCloseBox(html)
    {
        var ok_btn = '<p style="text-align:right; padding-bottom: 0" class="submit"><input type="button" onclick="$.fancybox.close();" value="'+ok_label+'" class="button btn btn-warning"></p>'
        $.fancybox(html + ok_btn);
    }
    //]]>
    </script>

    {if !empty($request) AND $request}
        {if $request == 'request failed'}
            <div class="error alert alert-danger">{l s='Request sending failed. Please try again later' mod='affiliates'}</div>
        {elseif $request == 'request success'}
            {if $auto_approve}
                <div class="success alert alert-success">{l s='Your affiliation request has been accepted.' mod='affiliates'}</div>
            {else}
                <div class="success alert alert-success">{l s='Your affiliation request has been sent successfully.' mod='affiliates'}</div>
            {/if}
        {elseif $request ='already sent'}
            <div class="error alert alert-danger">{l s='You have already sent affiliation request.' mod='affiliates'}</div>
        {/if}
    {/if}

    {if !empty($pdetails) AND $pdetails}
        {if $pdetails == 'invalid email'}
            <div class="error alert alert-danger">{l s='Invalid Paypal Email' mod='affiliates'}</div>
        {elseif $pdetails == 'details saved'}
            <div class="success alert alert-success">{l s='Payment details saved successfully.' mod='affiliates'}</div>
        {elseif $pdetails ='details error'}
            <div class="error alert alert-danger">{l s='Operation failed.Payment details error.' mod='affiliates'}</div>
        {/if}
    {/if}

    {if isset($smarty.get.payment_request) AND $smarty.get.payment_request}
        {if $smarty.get.payment_request == 'no_amount'}
            <div class="error alert alert-danger">{l s='Please select an amount first.' mod='affiliates'}</div>
        {elseif $smarty.get.payment_request == 'no_payment_method'}
            <div class="error alert alert-danger">{l s='Empty selected payment method\'s details.' mod='affiliates'}</div>
        {elseif $smarty.get.payment_request == 'request_error'}
            <div class="error alert alert-danger">{l s='Request sending failed.' mod='affiliates'}</div>
        {elseif $smarty.get.payment_request == 'request_already_sent'}
            <div class="error alert alert-danger">{l s='Request already sent.' mod='affiliates'}</div>
        {elseif $smarty.get.payment_request == 'paid'}
            <div class="error alert alert-danger">{l s='Requested amount is already paid.' mod='affiliates'}</div>
        {elseif $smarty.get.payment_request == 'request_cancelled'}
            <div class="error alert alert-danger">{l s='Request has been cancelled.Please try later.' mod='affiliates'}</div>
        {elseif $smarty.get.payment_request ='request_success'}
            <div class="success alert alert-success">{l s='Your withdraw request has been sent successfully.' mod='affiliates'}</div>
        {/if}
    {/if}

    {if isset($affiliations) AND $affiliations}

        {if $affiliations.approved == 1}

        <div class="col-lg-12">
            <h3 class="affiliation_heading"> {l s='My Affiliations' mod='affiliates'}</h3>
            {if $error}
                <div class="error alert alert-danger">
                    {if $error == 'conditions not valided'}
                        {l s='You need to agree to the conditions of the affiliate program!' mod='affiliates'}
                    {elseif $error == 'email invalid'}
                        {l s='One of the provided email address is invalid!' mod='affiliates'}
                    {elseif $error == 'name invalid'}
                        {l s='One of the provided first name or last name is invalid!' mod='affiliates'}
                    {elseif $error == 'email exists'}
                        {l s='Following referral(s) are already associated with a customer' mod='affiliates'}:
                        <ul style="list-style:inside none disc;">
                            {foreach from=$mails_exists item=mail}
                                <li>{$mail|escape:'htmlall':'UTF-8'}</li>
                            {/foreach}
                        </ul>
                    {elseif $error == 'no revive checked'}
                        {l s='Please mark at least one checkbox' mod='affiliates'}
                    {elseif $error == 'cannot add friends'}
                        {l s='Something went wrong' mod='affiliates'}
                    {/if}
                </div>
            {/if}

            {if $invitation_sent}
                <div class="success alert alert-success">
                {if $nbInvitation > 1}
                    {l s='E-mails have been sent to your friends!' mod='affiliates'}
                {else}
                    {l s='An email has been sent to your friend!' mod='affiliates'}
                {/if}
                </div>
            {/if}

            {if $revive_sent}
                <div class="success alert alert-success">
                {if $nbRevive > 1}
                    {l s='Reminder emails have been sent to your friends!' mod='affiliates'}
                {else}
                    {l s='A reminder email has been sent to your friend!' mod='affiliates'}
                {/if}
                </div>
            {/if}

            {* sponsored name *}
            {if isset($affiliations.sponsor) AND $affiliations.sponsor}
                <p>
                    {l s='Your Sponsor' mod='affiliates'} :
                    <strong>
                        {$affiliations.sponsor.firstname|escape:'htmlall':'UTF-8'}&nbsp;
                        {$affiliations.sponsor.lastname|escape:'htmlall':'UTF-8'}
                        ({$affiliations.sponsor.email|escape:'htmlall':'UTF-8'})
                    </strong>
                </p>
            {/if}

            <div id="main" class="left">
                <ul {if $ps_version < 1.6}id="affiliation_tabs_ps"{/if} class="affiliation_tabs" data-persist="true">
                    <li class="{if !$active_tab OR $active_tab == 'affiliation_tab_1'}selected{/if}">
                        <a tab="#affiliation_tab_1">
                            <i class="icon-user-plus"></i>
                            {l s='Invite Friends' mod='affiliates'}
                        </a>
                    </li>
                    <li class="{if $active_tab AND $active_tab == 'affiliation_tab_7'}selected{/if}">
                        <a tab="#affiliation_tab_7">
                            <i class="icon-group"></i>
                            {l s='Banners' mod='affiliates'}
                        </a>
                    </li>
                    <li class="{if $active_tab AND $active_tab == 'affiliation_tab_2'}selected{/if}">
                        <a tab="#affiliation_tab_2">
                            <i class="icon-group"></i>
                            {l s='Pending Referrals' mod='affiliates'}
                        </a>
                    </li>
                    <li class="{if $active_tab AND $active_tab == 'affiliation_tab_3'}selected{/if}">
                        <a tab="#affiliation_tab_3">
                            <i class="icon-list"></i>
                            {l s='Referrals List' mod='affiliates'}
                        </a>
                    </li>
                    <li class="{if $active_tab AND $active_tab == 'affiliation_tab_4'}selected{/if}">
                        <a tab="#affiliation_tab_4">
                            <i class="icon-money"></i>
                            {l s='My Rewards' mod='affiliates'}
                        </a>
                    </li>
                    <li class="{if $active_tab AND $active_tab == 'affiliation_tab_5'}selected{/if}">
                        <a tab="#affiliation_tab_5">
                            <i class="icon-paypal"></i>
                            {l s='Payment Details' mod='affiliates'}
                        </a>
                    </li>
                    <li class="{if $active_tab AND $active_tab == 'affiliation_tab_6'}selected{/if}">
                        <a tab="#affiliation_tab_6">
                            <i class="icon-bar-chart"></i>
                            {l s='My Statstics' mod='affiliates'}
                        </a>
                    </li>
                </ul>

                <div class="tabcontents">
                    <div id="affiliation_tab_1" {if $active_tab AND $active_tab != 'affiliation_tab_1'}style="display:none;"{/if}>
                        {include file="./referrals.tpl"}
                    </div>
                    <div class="clearfix"></div>

                    <div id="affiliation_tab_7" {if $active_tab AND $active_tab != 'affiliation_tab_7'}style="display:none;"{/if}>
                        {include file="./banners.tpl"}
                    </div>
                    <div class="clearfix"></div>

                    <div id="affiliation_tab_2" {if $active_tab AND $active_tab != 'affiliation_tab_2'}style="display:none;"{/if}>
                        {include file="./pending_referrals.tpl"}
                    </div>
                    <div class="clearfix"></div>

                    <div id="affiliation_tab_3" {if $active_tab AND $active_tab != 'affiliation_tab_3'}style="display:none;"{/if}>
                        {include file="./referrals_list.tpl"}
                    </div>

                    <div id="affiliation_tab_4" {if $active_tab AND $active_tab != 'affiliation_tab_4'}style="display:none;"{/if}>
                        {include file="./payments.tpl"}
                    </div>

                    <div id="affiliation_tab_5" {if $active_tab AND $active_tab != 'affiliation_tab_5'}style="display:none;"{/if}>
                        {include file="./payment_details.tpl"}
                    </div>

                    <div class="clearfix"></div>

                    <div id="affiliation_tab_6" {if $active_tab AND $active_tab != 'affiliation_tab_6'}style="display:none;"{/if}>
                        {include file="./stats.tpl"}
                    </div>
                    <div class="clearfix"></div>  
                </div>
            </div>

                <div class="clearfix"></div>
                <ul class="footer_links">
                    <li>
                    {if $ps_version < 1.6}
                        <a href="{$link->getPageLink('my-account', true)|escape:'htmlall':'UTF-8'}">
                            <img src="{$img_dir|escape:'htmlall':'UTF-8'}icon/my-account.gif" alt="{l s='Back to Your Account' mod='affiliates'}" class="icon" />
                        </a>
                    {/if}
                        <a href="{$link->getPageLink('my-account', true)|escape:'htmlall':'UTF-8'}" class="btn btn-primary">
                            <span><i class="icon-chevron-left"></i> {l s='Back to Your Account' mod='affiliates'}</span>
                        </a>
                    </li>
                    <li class="f_right">
                    {if $ps_version < 1.6}
                        <a href="{if $force_ssl == 1}{$base_dir_ssl|escape:'htmlall':'UTF-8'}{else}{$base_dir|escape:'htmlall':'UTF-8'}{/if}">
                            <img src="{$img_dir|escape:'htmlall':'UTF-8'}icon/home.gif" alt="{l s='Home' mod='affiliates'}" class="icon" />
                        </a>
                    {/if}
                        <a href="{if $force_ssl == 1}{$base_dir_ssl|escape:'htmlall':'UTF-8'}{else}{$base_dir|escape:'htmlall':'UTF-8'}{/if}" class="btn btn-primary">
                            <span><i class="icon-home"></i> {l s='Home' mod='affiliates'}</span>
                        </a>
                    </li>
                </ul>
        </div>
        <div class="clearfix"></div>

        {else}
            <!-- pending approval -->
            <div class="success alert alert-success">
                <p>{l s='Your affiliation request is pending.' mod='affiliates'}</p>
                <p>{l s='You can access your Referral account once your request is approved.' mod='affiliates'}</p>
            </div>
        {/if}

    {else}
        <!-- send affiliate request -->
        <div class="warning alert alert-warning">{l s='You are not enrolled in affiliation program.' mod='affiliates'}</div>
        <form id="affiliation-request" method="post" action="{$link->getModuleLink('affiliates', 'myaffiliates', ['sendAffiliateRequest' => '1'])|escape:'htmlall':'UTF-8'}" class="well">
            <!-- <p class="text form-group">
                <label class="col-lg-3">{l s='Send Affiliation Request' mod='affiliates'}</label>
            </p> -->

            <p class="checkbox">
                <label class="col-lg-3"><b>{l s='Send Affiliation Request' mod='affiliates'}</b></label>
                <input type="checkbox" name="affiliationTerms" id="affiliationTerms" value="1"/>
                <label for="affiliationTerms">{l s='I agree to the terms of service and conditions of affiliate program.' mod='affiliates'}</label>
                {if isset($cms) AND $cms}
                    <a class="read-conditions" href="#affiliate-cond" class="thickbox" title="{l s='Conditions of the affiliate program' mod='affiliates'}" rel="nofollow">{l s='Read conditions.' mod='affiliates'}</a>

                    <div style="display:none;">
                        <div id="affiliate-cond">
                            {include file="$tpl_dir./cms.tpl"}
                        </div>
                    </div>
                {/if}
            </p>

            <p class="submit pull-right">
                {if $ps_version < 1.6}
                    <input type="submit" id="sendAffiliateRequest" name="sendAffiliateRequest" class="button_large button" value="{l s='Send Request' mod='affiliates'}" />
                {else}
                    <button class="btn btn-default button button-medium" name="sendAffiliateRequest" id="sendAffiliateRequest" type="submit">
                        <span>{l s='Send Request' mod='affiliates'} <i class="icon-paper-plane"></i></span>
                    </button>
                {/if}
            </p>

            <div class="form-group row">
                <div class="col-lg-12">
                    {hook h='displayGDPRConsent' mod='psgdpr' id_module=$id_module}
                </div>
            </div>
            <div class="clearfix"></div>
        </form>
    {/if}

    {if $ps_version < 1.6}
    {literal}
    <style type="text/css">
    .hint_affiliate{
        background: #bde5f8 url("{/literal}{$smarty.const.__PS_BASE_URI__|escape:'htmlall':'UTF-8'}{literal}modules/affiliates/views/img/icon-info.png") no-repeat scroll 6px 5px;
        border: 1px solid #00529b;
        border-radius: 3px;
        color: #00529b;
        width: 90%;
        margin-bottom: 2px;
        margin-top: 4px;
        min-height: 15px;
        padding: 15px 5px 15px 40px;
        position: absolute;
        z-index: 10;
    }
    </style>
    {/literal}
    {/if}
{else}
    <p class="alert alert-warning warning">{l s='Your are not eligible to enroll to Affiliate Program.' mod='affiliates'}</p>
{/if}
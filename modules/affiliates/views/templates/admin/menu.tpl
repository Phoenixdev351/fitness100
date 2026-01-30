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

{include file="./info.tpl"}

{if ($nbrLevels le 0) OR ($nbrRules le 0)}
    {include file="./instruction.tpl"}
{/if}

<div id="fme-nav-wrap">
    <div id="fme-nav-menu">
        <ul>
            <a class="nav-link affiliatetab-page home"
            id="affiliates_link_home"
            href="{if Tools::getValue('controller') == 'AdminModules'}javascript:displayAffiliateTabs('home', 'home');{else}{$dashboard_link|escape:'htmlall':'UTF-8'}&currentMenuTab=home{/if}">
                <li class="inner-nav"><i class="icon-home"></i> {l s='Home' mod='affiliates'}</li>
            </a>
            <span class="nav-link">
            |
                <li id="configuration" class="inner-nav config"><i class="icon-cogs"></i> {l s='Configuration' mod='affiliates'}
                    <ul class="affiliatetab">
                        <a class="affiliatetab-page" id="affiliates_link_general" href="{if Tools::getValue('controller') == 'AdminModules'}javascript:displayAffiliateTabs('configuration','general');{else}{$dashboard_link|escape:'htmlall':'UTF-8'}&currentMenuTab=configuration&subMenuTab=general{/if}">
                            <li class="nav-row">{l s='General Settings' mod='affiliates'}</li>
                        </a>
                        <a class="affiliatetab-page" id="affiliates_link_control" href="{if Tools::getValue('controller') == 'AdminModules'}javascript:displayAffiliateTabs('configuration','control');{else}{$dashboard_link|escape:'htmlall':'UTF-8'}&currentMenuTab=configuration&subMenuTab=control{/if}">
                            <li class="nav-row">{l s='Affiliation Settings' mod='affiliates'}</li>
                        </a>
                        <a class="affiliatetab-page" id="affiliates_link_payments" href="{if Tools::getValue('controller') == 'AdminModules'}javascript:displayAffiliateTabs('configuration','payments');{else}{$dashboard_link|escape:'htmlall':'UTF-8'}&currentMenuTab=configuration&subMenuTab=payments{/if}">
                            <li class="nav-row">{l s='Payment Settings' mod='affiliates'}</li>
                        </a>
                        <a class="affiliatetab-page" id="affiliates_link_social" href="{if Tools::getValue('controller') == 'AdminModules'}javascript:displayAffiliateTabs('configuration','social');{else}{$dashboard_link|escape:'htmlall':'UTF-8'}&currentMenuTab=configuration&subMenuTab=social{/if}">
                            <li class="nav-row">{l s='Social' mod='affiliates'}</li>
                        </a>
                        {if $multishop == 1}
                        <a class="affiliatetab-page" id="affiliates_link_shops" href="{if Tools::getValue('controller') == 'AdminModules'}javascript:displayAffiliateTabs('configuration','shops');{else}{$dashboard_link|escape:'htmlall':'UTF-8'}&currentMenuTab=configuration&subMenuTab=shops{/if}">
                            <li class="nav-row">{l s='Shops' mod='affiliates'}</li>
                        </a>
                        {/if}
                    </ul>
                </li>
            </span>
             <span class="nav-link">
                <li id="affiliations" class="inner-nav config"><i class="icon-users"></i> {l s='Affiliates' mod='affiliates'}
                    <ul class="affiliatetab">
                        <a id="affiliates_link_manage_affiliates" class="affiliatetab-page" href="{$link->getAdminLink('AdminAffiliates')|escape:'htmlall':'UTF-8'}&currentMenuTab=affiliations&subMenuTab=manage_affiliates" target="_self">
                            <li class="nav-row">{l s='Manage Affiliates' mod='affiliates'}</li>
                        </a>
                        <a id="affiliates_link_convert_affiliates" class="affiliatetab-page" href="{$link->getAdminLink('AdminAffiliatesConversion')|escape:'htmlall':'UTF-8'}&currentMenuTab=affiliations&subMenuTab=convert_affiliates" target="_self">
                            <li class="nav-row">{l s='Convert Affiliates' mod='affiliates'}</li>
                        </a>
                        <a id="affiliates_link_referrals" class="affiliatetab-page" href="{$link->getAdminLink('AdminReferrals')|escape:'htmlall':'UTF-8'}&currentMenuTab=referrals" target="_self">
                            <li class="nav-row">{l s='Referrals' mod='affiliates'}</li>
                        </a>
                    </ul>
                </li>
            </span>
            <span class="nav-link">
                <li id="rewards" class="inner-nav config"><i class="icon-trophy"></i> {l s='Rewards' mod='affiliates'}
                    <ul class="affiliatetab">
                        <a id="affiliates_link_levels" class="affiliatetab-page" href="{$link->getAdminLink('AdminLevels')|escape:'htmlall':'UTF-8'}&currentMenuTab=rewards&subMenuTab=levels" target="_self">
                            <li class="nav-row">{l s='Order Reward' mod='affiliates'}</li>
                        </a>
                        <a id="affiliates_link_rules" class="affiliatetab-page" href="{$link->getAdminLink('AdminRules')|escape:'htmlall':'UTF-8'}&currentMenuTab=rewards&subMenuTab=rules" target="_self">
                            <li class="nav-row">{l s='Rules' mod='affiliates'}</li>
                        </a>
                        <a id="affiliates_link_withdraw" class="affiliatetab-page" href="{$link->getAdminLink('AdminPayments')|escape:'htmlall':'UTF-8'}&currentMenuTab=rewards&subMenuTab=withdraw" target="_self">
                            <li class="nav-row">{l s='Withdrawal Requests' mod='affiliates'}</li>
                        </a>
                    </ul>
                </li>
            </span>

            <a class="nav-link" href="{$link->getAdminLink('AdminAffiliationBanners')|escape:'htmlall':'UTF-8'}&currentMenuTab=banners" target="_self">
                <li id="banners" class="inner-nav"><i class="icon-picture"></i> {l s='Banners' mod='affiliates'}</li>
            </a>
             <a class="nav-link" href="{$link->getAdminLink('AdminAffiliationDiscounts')|escape:'htmlall':'UTF-8'}&currentMenuTab=discounts" target="_self">
                <li id="discounts" class="inner-nav"><i class="icon-ticket"></i> {l s='Dsicounts' mod='affiliates'}</li>
            </a>
             <a class="nav-link" href="{$link->getAdminLink('AdminAffiliateStates')|escape:'htmlall':'UTF-8'}&currentMenuTab=rewardstats" target="_self">
                <li id="rewardstats" class="inner-nav"><i class="icon-bar-chart"></i> {l s='Statistics' mod='affiliates'}</li>
            </a>
        </ul>
    </div>
</div>

<script type="text/javascript">
    var currentMenuTab = "{if isset($currentMenuTab) && $currentMenuTab}{$currentMenuTab|escape:'htmlall':'UTF-8'}{elseif isset($smarty.get.currentMenuTab)}{$smarty.get.currentMenuTab|escape:'htmlall':'UTF-8'}{else}home{/if}";
    var subMenuTab = "{if isset($subMenuTab) && $subMenuTab}{$subMenuTab|escape:'htmlall':'UTF-8'}{elseif isset($smarty.get.subMenuTab)}{$smarty.get.subMenuTab|escape:'htmlall':'UTF-8'}{else}home{/if}";

    $(document).ready(function(){
        displayAffiliateTabs(currentMenuTab, subMenuTab);
        // #FIX - excluding PS css rule from default [.affiliate] class
        $('#form-affiliate').find('#table-affiliate').removeClass('affiliate');
    });

    $(document).on('hover','.config', function(){
        $('.affiliatetab').show();
    });

    $(document).on('click', '.affiliatetab-row', function() {
        $('.inner-nav').removeClass('selected-nav');
        if ($(this).hasClass('home')) {
            $(this).find('.inner-nav').addClass('selected-nav');
        } else {
            $(this).parent().parent().parent().addClass('selected-nav');
        }
    });

    function displayAffiliateTabs(tab, subtab) {
        $('#' + tab).addClass('selected-nav');
        $(".affiliate_loader").show();
        $('.affiliates_tab').hide();
        $('.affiliatetab-page').removeClass('selected');
        $('#affiliates_' + subtab).show();
        $('#affiliates_link_' + subtab).addClass('selected');
        $('#currentFormTab').val(tab);
        $('#subMenuTab').val(subtab);
        $('.affiliatetab').hide();
        $(".affiliate_loader").fadeOut("slow");

        if (tab == 'home') {
            $('.inner-nav').each(function() {
                $(this).removeClass('selected-nav');
            })
        }
    }
</script>
{literal}
<style type="text/css">
    .selected-nav {
        background: #3f485b none repeat scroll 0 0;
        box-shadow: 0 0 2px rgba(0, 0, 0, 0.5);
        color: #fff!important;
        opacity: 1;
    }
    #fme-nav-wrap {
        margin-bottom: 15px;
    }
    #fme-nav-menu {
        background: #fff none repeat scroll 0 0;
        border-radius: 4px;
        box-shadow: 0 0 2px rgba(0, 0, 0, 0.5);
    }
    #fme-nav-menu a.home li {
        color: #00AFF0;
    }
    #fme-nav-wrap a:link {
        color: #4a4a4a;
        text-decoration: none;
    }
    #fme-nav-wrap a:visited {
        color: inherit;
        text-decoration: none;
    }
    #fme-nav-wrap a:hover {
        text-decoration: none;
    }
    #fme-nav-wrap a:active {
        text-decoration: none;
    }
    #fme-nav-wrap ul{
        display: inline;
        list-style: outside none none;
        margin: 0;
        padding: 15px 4px 17px 0;
        text-align: left;
    }
    #fme-nav-wrap ul li {
        color: #4a4a4a;
        cursor: pointer;
        display: inline-block;
        font: bold 12px/18px sans-serif;
        margin-right: -4px;
        padding: 15px 10px;
        position: relative;
    }
    #fme-nav-wrap ul li:hover {
        background: #282B30 none repeat scroll 0 0;
        box-shadow: 0 0 2px rgba(0, 0, 0, 0.5);
        color: #ddd;
        opacity: 1;
    }
    #fme-nav-wrap ul li ul {
        box-shadow: none;
        display: none;
        left: 0;
        opacity: 0;
        padding: 0;
        position: absolute;
        top: 48px;
        /*visibility: hidden;*/
        width: 225px;
    }
    #fme-nav-wrap ul li ul li {
        background: #363A41 none repeat scroll 0 0;
        color: #ccc;
        display: block;
        z-index: 999;
    }
    #fme-nav-wrap ul li ul li:hover,  #fme-nav-wrap ul li ul > a.selected li {
        background: #2eacce none repeat scroll 0 0;
        color: #fff;
        z-index: 999;
    }
    #fme-nav-wrap ul li:hover ul {
        display: block;
        opacity: 1;
    }
    #affilate-dashboard .af-icons {
        font-size: 100px;
    }
    #affilate-dashboard a:hover {
        color: #32cd32;
    }
</style>
{/literal}

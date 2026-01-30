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

<!-- Dashboard tab -->
<div class="clearfix"></div>
<div id="affilate-dashboard">

    <div class="col-lg-12">
        <div class="col-lg-4">
            <div class="panel">
                <center>
                    <a href="{$link->getAdminLink('AdminAffiliates')|escape:'htmlall':'UTF-8'}&currentMenuTab=affiliations&subMenuTab=manage_affiliates" title="{l s='Affiliates' mod='affiliates'}">
                        <i class="icon-user af-icons"></i>
                    </a>
                </center>
                <div class="panel-footer clearfix" style="background: #6f6a6a; color: #ffffff;">
                    <strong>
                        <center>
                        <span class="badge">{$nbrAffiliates|escape:'htmlall':'UTF-8'}</span> {l s='Affiliate(s)' mod='affiliates'}
                        </center>
                    </strong>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="panel">
                <center>
                    <a href="{$link->getAdminLink('AdminReferrals')|escape:'htmlall':'UTF-8'}&currentFormTab=referrals" title="{l s='Referrals' mod='affiliates'}" style="color: #2c96bf;">
                        <i class="icon-users af-icons"></i>
                    </a>
                </center>
                <div class="panel-footer clearfix" style="background: #6f6a6a; color: #ffffff;">
                    <strong>
                        <center>
                        <span class="badge">{$nbrReferrals|escape:'htmlall':'UTF-8'}</span> {l s='Referral(s)' mod='affiliates'}
                        </center>
                    </strong>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="panel">
                <center>
                    <a href="{$link->getAdminLink('AdminPayments')|escape:'htmlall':'UTF-8'}&currentMenuTab=rewards&subMenuTab=withdraw" title="{l s='Withdrawal Requests' mod='affiliates'}" style="color: #6AB233;">
                        <i class="icon-money af-icons"></i>
                    </a>
                </center>
                <div class="panel-footer clearfix" style="background: #6f6a6a; color: #ffffff;">
                    <strong>
                        <center>
                        <span class="badge">{$nbrWithdrawals|escape:'htmlall':'UTF-8'}</span> {l s='Withdrawal Requests' mod='affiliates'}
                        </center>
                    </strong>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-12">
        <div class="col-lg-6">
            <div class="panel">
                <center>
                    <a href="{$link->getAdminLink('AdminLevels')|escape:'htmlall':'UTF-8'}&addaffiliate_levels&currentMenuTab=rewards&subMenuTab=levels" title="{l s='Add Order Reward' mod='affiliates'}" style="color: #FBBB22;">
                        <i class="icon-trophy af-icons"></i>
                    </a>
                </center>
                <div class="panel-footer clearfix" style="background: #6f6a6a; color: #ffffff;">
                    <strong>
                        <center>
                        <i class="icon-plus"></i> {l s='Add Order Reward' mod='affiliates'}
                        </center>
                    </strong>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="panel">
                <center>
                    <a href="{$link->getAdminLink('AdminRules')|escape:'htmlall':'UTF-8'}&addaffiliate_rules&currentMenuTab=rewards&subMenuTab=levels" title="{l s='Add Rule' mod='affiliates'}" style="color: #FB2222;">
                        <i class="icon-bookmark af-icons"></i>
                    </a>
                </center>
                <div class="panel-footer clearfix" style="background: #6f6a6a; color: #ffffff;">
                    <strong>
                        <center>
                        <i class="icon-plus"></i> {l s='Add Rule' mod='affiliates'}
                        </center>
                    </strong>
                </div>
            </div>
        </div>

    </div>
</div>
<div class="clearfix"></div>

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

{include file="$tpl_dir./errors.tpl"}
<div id="my-account">
<div class="row">
	<div class="center_column col-xs-12 col-sm-12" id="center_column">
		<h4 class="page-heading title_block">{l s='My Affiliations' mod='affiliates'}</h4>
		<div class="row addresses-lists">
			<div class="col-xs-12 col-sm-6 col-lg-4">
				<ul class="myaccount-link-list">

					<li class="lnk_affiliates">
						<a title="{l s='My Referrals' mod='affiliates'}" href="#">
							<span>{l s='My Referrals' mod='affiliates'}</span>
							<i class="icon-user-plus"></i>
						</a>
					</li>

					<li class="referralprogram">
						<a rel="nofollow" title="{l s='My Rewards' mod='affiliates'}" href="#">
							<i class="icon-money"></i>
							<span>{l s='My Rewards' mod='affiliates'}</span>
						</a>
					</li>
					
					<li class="referralprogram">
						<a rel="nofollow" title="{l s='My Payments' mod='affiliates'}" href="#">
							<i class="icon-paypal"></i>
							<span>{l s='My Payments' mod='affiliates'}</span>
						</a>
					</li>

					<li class="referralprogram">
						<a rel="nofollow" title="{l s='My Stats' mod='affiliates'}" href="#">
							<i class="icon-bar-chart"></i>
							<span>{l s='My Stats' mod='affiliates'}</span>
						</a>
					</li>

				</ul>
			</div>
		</div>

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
</div>
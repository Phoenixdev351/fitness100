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

<div class="row">
	<div class="center_column col-xs-12 col-sm-12">
		<h4 class="page-heading title_block panel">{l s='My Banners' mod='affiliates'}</h4>
		{if empty($banners)}
			<div class="text panel">
				<p class="alert alert-info hint_affiliate">{l s='No banners available' mod='affiliates'}</p>
			</div>
		{else}
		<p>{l s='Please copy the code from text block to use in your campaigns.' mod='affiliates'}</p>
		{foreach from=$banners item=banner}
			<div class="row affiliate_banner_element">
				<div class="col-lg-6">
					<div class="affiliate_banner_wrap">
						<img src="{$affiliate_img_dir|escape:'htmlall':'UTF-8'}{$banner.id_affiliate_banners|escape:'htmlall':'UTF-8'}.jpg" alt="" />
					</div>
				</div>
				<div class="col-lg-6">
					<textarea class="fmm_affiliate_txt_area form-control" readonly="readonly"><a href="{if empty($banner.href)}{$ref_link|escape:'htmlall':'UTF-8'}{else}{$banner.href|escape:'htmlall':'UTF-8'}{/if}"><img src="{$affiliate_img_dir|escape:'htmlall':'UTF-8'}{$banner.id_affiliate_banners|escape:'htmlall':'UTF-8'}.jpg"/></a></textarea>
				</div>
			</div>
		{/foreach}
		{/if}
	</div>
</div>
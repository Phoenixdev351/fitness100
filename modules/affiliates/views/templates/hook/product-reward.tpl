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

<div class="card card-block box" style="margin-top: 10px;">
	<p style="color: #2fb5d2;margin-top; text-align: center;">
		{if $ps_17}<i class='material-icons'>stars</i>{else}<i class='icon-trophy'></i>{/if}
		{l s='Maximum affiliate reward on this product is:' mod='affiliates'}
		{if $reward.value_type == 1}
			<strong>{$reward.value|round:2|escape:'htmlall':'UTF-8'}%</strong>
		{else}
			<strong>{Tools::displayPrice($reward.value)|escape:'htmlall':'UTF-8'}</strong>
		{/if}
		{if isset($productlink) AND $productlink}
			<br />{l s='Share below link with your friends to get maximum reward.' mod='affiliates'}
		{/if}

		{if isset($productlink) AND $productlink}
			<p><strong>{$productlink|escape:'htmlall':'UTF-8'}</strong></p>
		{/if}
	</p>
</div>
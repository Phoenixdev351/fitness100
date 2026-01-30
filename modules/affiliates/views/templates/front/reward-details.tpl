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

<hr><h5 class="reward-details-heading">{l s='Reward Details' mod='affiliates'}</h5><hr>
<div class="table-responsive">
	<table class="{if $ps_version >= 1.6}affiliateion_table{/if} table table table-bordered {if $ps_version <1.6}std{/if}">
		<thead>
			<tr>
				<th class="center item">{l s='Level' mod='affiliates'}</th>
				<th class="item">{l s='Type' mod='affiliates'}</th>
				<th class="last_item">{l s='Value' mod='affiliates'}</th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$levels item=level name=level}
				<tr class="{if $smarty.foreach.level.index % 2}item{else}alternate_item{/if}">
					<td class="center first_row">
						<strong>{$level.level|escape:'htmlall':'UTF-8'}</strong>
					</td>
					<td>
						{$level_types[$level.reward_type]|escape:'htmlall':'UTF-8'}
					</td>
					<td>
						{if $level.reward_type eq 0}
							{Tools::displayPrice($level.reward_value)|escape:'htmlall':'UTF-8'}
						{elseif $level.reward_type eq 1}
							{$level.reward_value|round:2|escape:'htmlall':'UTF-8'}%
						{elseif $level.reward_type eq 2}
							{if isset($level.products) AND $level.products}
								{foreach from=$level.products item=product}
									<p>
										<strong>
											<span>
												{if $level.value_type eq 0}
													{Tools::displayPrice($product.value)|round:2|escape:'htmlall':'UTF-8'}
												{else}
													{$product.value|round:2|escape:'htmlall':'UTF-8'}%
												{/if}
											</span>
										</strong>
										&nbsp;{l s='on' mod='affiliates'}&nbsp;
										<span>
											<a href="{$product.link|escape:'htmlall':'UTF-8'}">{$product.name|escape:'htmlall':'UTF-8'}</a>
										</span>
									</p>
								{/foreach}
							{/if}
						{elseif $level.reward_type eq 3}
							{if isset($level.categories) AND $level.categories}
								{foreach from=$level.categories item=cat}
									<p>
										<strong>
											<span>
												{if $level.value_type eq 0}
													{Tools::displayPrice($cat.value|round:2)|escape:'htmlall':'UTF-8'}
												{else}
													<stong>{$cat.value|round:2|escape:'htmlall':'UTF-8'}%
												{/if}
											</span>
										</strong>
										&nbsp;{l s='on' mod='affiliates'}&nbsp;
										<span>
											<a href="{$cat.link|escape:'htmlall':'UTF-8'}">{$cat.name|escape:'htmlall':'UTF-8'}</a>
										</span>
									</p>
								{/foreach}
							{/if}
						{/if}
					</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
</div>
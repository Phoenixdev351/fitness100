{**
 * 2007-2016 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2016 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}
{block name='product_middle'}
  {hook h='displayMiddleProduct' product=$product category=$category}
{/block}
{capture name="displayProductDescRightColumn"}{hook h="displayProductDescRightColumn"}{/capture}
{assign var="show_desc_right_column" value=0}
{if $sttheme.pro_desc_secondary_column_md && $smarty.capture.displayProductDescRightColumn|trim}{$show_desc_right_column=1}{/if}
<div class="row product_desc_block">
{if !$sttheme.product_tabs || (isset($steasybuilder) && $steasybuilder.is_editing)}<div class="product_desc_column col-md-{if $show_desc_right_column}{12-$sttheme.pro_desc_secondary_column_md}{else}12{/if}{if $sttheme.product_tabs} display_none {/if}"><div class="bottom_more_info_block pro_more_info p-t-1 p-b-1 {if $sttheme.product_tabs_style==1} accordion_more_info {/if}">{if !$sttheme.product_tabs}{include file='catalog/_partials/product-tabs.tpl'}{/if}</div></div>{/if}

<div class="product_desc_right_column {if $show_desc_right_column}col-md-{$sttheme.pro_desc_secondary_column_md}{else}display_none{/if}">
	{$smarty.capture.displayProductDescRightColumn nofilter}
</div>
</div>
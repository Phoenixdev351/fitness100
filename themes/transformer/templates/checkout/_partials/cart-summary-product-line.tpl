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
{block name='cart_summary_product_line'}
  <a href="{$product.url}" title="{$product.name}" class="mar_r6">
    <picture>
    {if isset($stwebp) && isset($stwebp.small_default) && $stwebp.small_default && isset($product.cover.bySize.small_default.url) && $product.cover.bySize.small_default.url}
    <!--[if IE 9]><video style="display: none;"><![endif]-->
      <source srcset="{$product.cover.bySize.small_default.url|regex_replace:'/\.jpg$/':'.webp'}"
        title="{$product.name}"
        type="image/webp"
        >
    <!--[if IE 9]></video><![endif]-->
    {/if}
    <img class="general_border" src="{if isset($product.cover.bySize.small_default.url) && $product.cover.bySize.small_default.url}{$product.cover.bySize.small_default.url}{elseif isset($urls.no_picture_image)}{$urls.no_picture_image.bySize.small_default.url}{else}{$sttheme.img_prod_url}{$sttheme.lang_iso_code}-default-small_default.jpg{/if}" alt="{$product.name}" />
    </picture>
  </a>
  <div class="product-quantity mar_r4">{$product.quantity}x</div>
  <div class="flex_child mar_r4">
  	<div class="product-name mar_b4">{$product.name}</div>
  	{foreach from=$product.attributes key="attribute" item="value"}
    <div class="small_cart_attr_attr">
        <span class="small_cart_attr_k">{$attribute}:</span>
        <span class="value">{$value}</span>
    </div>
    {/foreach}
  </div>
  <div class="summary-product-price">
    <span class="product-price price">{$product.price}</span>
    {hook h='displayProductPriceBlock' product=$product type="unit_price"}
  </div>
{/block}

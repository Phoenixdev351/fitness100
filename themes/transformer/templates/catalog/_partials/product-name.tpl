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
<div class="product_name_wrap flex_container flex_start">
    <div class="flex_child">
    <h1 {if $sttheme.google_rich_snippets} itemprop="name" {/if} class="product_name {if $sttheme.product_name_at_top} text-center {/if}">{block name='page_title'}{$product.name}{/block}</h1>
    
    {if ($sttheme.show_brand_logo == 4 || $sttheme.show_brand_logo == 5) && isset($product_manufacturer->id)}
      {include file='catalog/_partials/miniatures/product-brand.tpl'}
    {/if}
    </div>

    <section class="pro_name_right">
    <div class="flex_box">
    {foreach $product.extraContent as $extra}
      {if $extra.moduleName == 'stproductlinknav' && ($extra.content.prev || $extra.content.next)}
      {foreach $extra.content as $nav => $nav_product}
          {if $nav_product}
              <div class="product_link_nav with_preview"> 
                  <a href="{$nav_product.url}" title="{$nav_product.name}"><i class="fto-{if $nav=='prev'}left{/if}{if $nav=='next'}right{/if}-open-3"></i>
                      <div class="product_link_nav_preview">
                          <img src="{$nav_product.cover}" alt="{$nav_product.name}" width="{$nav_product.small_default.width}" height="{$nav_product.small_default.height}"/>
                      </div>
                  </a>
              </div>
          {/if}
      {/foreach}
      {/if}
      {if $extra.moduleName=='stvideo'}
          {include file="module:stvideo/views/templates/hook/stvideo_link.tpl" stvideos=$extra.content video_position=array(10)}
      {/if}
    {/foreach}

    {hook h='displayProductNameRight'}
    </div>
    </section>
</div>
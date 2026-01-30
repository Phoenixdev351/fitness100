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
{extends file='catalog/listing/product-list.tpl'}

{block name='full_width_top' prepend}
  {hook h='displayFullWidthCategoryHeader'}
{/block}

{block name='product_list_header'}
      {if $sttheme.display_category_title || (isset($steasybuilder) && $steasybuilder.is_editing)}<h1 class="page_heading category_page_heading mb-3 {if $sttheme.display_category_title==2} text-2 {elseif $sttheme.display_category_title==3} text-3 {else} text-1 {/if} {if !$sttheme.display_category_title} display_none {/if}">{$category.name}</h1>{/if}

      {if ($sttheme.display_category_image || (isset($steasybuilder) && $steasybuilder.is_editing)) && isset($category.image.bySize.category_default.url) && $category.image.bySize.category_default.url}
      <div class="category-cover mb-3 {if !$sttheme.display_category_image} display_none {/if}">
        <img src="{$category.image.bySize.category_default.url}" {if $sttheme.retina && isset($category.image.bySize.category_default_2x.url)} srcset="{$category.image.bySize.category_default_2x.url} 2x" {/if} alt="{if !empty($category.image.legend)}{$category.image.legend}{else}{$category.name}{/if}">
      </div>
      {/if}
      {if ($sttheme.display_cate_desc_full==1 || (isset($steasybuilder) && $steasybuilder.is_editing)) && $category.description}
        <div id="category-description" class="category-description style_content mb-3 truncate_block st_showless_block_{if !empty($sttheme.showless_cate_desc)}1{else}0{/if} truncate_cate_desc_{$sttheme.truncate_cate_desc} {if $sttheme.display_cate_desc_full!=1} display_none {/if}">{if $sttheme.display_cate_desc_full==1 || (!$sttheme.display_cate_desc_full && isset($steasybuilder) && $steasybuilder.is_editing)}<div class="st_read_more_box">{$category.description nofilter}</div>{/if}<a href="javascript:;" title="{l s='Read more' d='Shop.Theme.Transformer'}" class="st_read_more" rel="nofollow"><span class="st_showmore_btn">{l s='Read more' d='Shop.Theme.Transformer'}</span><span class="st_showless_btn">{l s='Show less' d='Shop.Theme.Transformer'}</span></a></div>
      {/if}

      {hook h='displayCategoryHeader'}
      
      {if ($sttheme.display_subcate || (isset($steasybuilder) && $steasybuilder.is_editing)) && $subcategories}
        <div id="subcategories" class="{if !$sttheme.display_subcate} display_none {/if}">
            <h3 class="page_heading mb-3 hidden">{l s='Subcategories' d='Shop.Theme.Transformer'}</h3>
            <ul class="inline_list {if $sttheme.display_subcate==2} subcate_list_view {else} subcate_grid_view row {/if}">
            {foreach $subcategories as $subcategory}
                <li class="clearfix {if $sttheme.display_subcate!=2} {if $sttheme.categories_per_fw} col-fw-{(12/$sttheme.categories_per_fw)|replace:'.':'-'}{/if} {if $sttheme.categories_per_xxl} col-xxl-{(12/$sttheme.categories_per_xxl)|replace:'.':'-'}{/if} {if $sttheme.categories_per_xl} col-xl-{(12/$sttheme.categories_per_xl)|replace:'.':'-'}{/if} col-lg-{(12/$sttheme.categories_per_lg)|replace:'.':'-'} col-md-{(12/$sttheme.categories_per_md)|replace:'.':'-'} col-sm-{(12/$sttheme.categories_per_sm)|replace:'.':'-'} col-{(12/$sttheme.categories_per_xs)|replace:'.':'-'} {if $sttheme.categories_per_fw && $subcategory@iteration%$sttheme.categories_per_fw == 1} first-item-of-screen-line{/if}{if $sttheme.categories_per_xxl &&  $subcategory@iteration%$sttheme.categories_per_xxl == 1} first-item-of-large-line{/if}{if $sttheme.categories_per_xl && $subcategory@iteration%$sttheme.categories_per_xl == 1} first-item-of-desktop-line{/if}{if $subcategory@iteration%$sttheme.categories_per_lg == 1} first-item-of-line{/if}{if $subcategory@iteration%$sttheme.categories_per_md == 1} first-item-of-tablet-line{/if}{if $subcategory@iteration%$sttheme.categories_per_sm == 1} first-item-of-mobile-line{/if}{if $subcategory@iteration%$sttheme.categories_per_xs == 1} first-item-of-portrait-line{/if} {/if}">
                    {if isset($subcategory.image.bySize.category_default.url) && $subcategory.image.bySize.category_default.url}
                    <a href="{$subcategory.url}" title="{$subcategory.name}" class="img">
                        <picture>
                        {if isset($stwebp) && isset($stwebp.category_default) && $stwebp.category_default}
                        <!--[if IE 9]><video style="display: none;"><![endif]-->
                          <source srcset="{$subcategory.image.bySize.category_default.url|regex_replace:'/\.jpg$/':'.webp'}
                            {if $sttheme.retina && isset($subcategory.image.bySize.category_default_2x.url)},{$subcategory.image.bySize.category_default_2x.url|regex_replace:'/\.jpg$/':'.webp'} 2x{/if}"
                            title="{$subcategory.name}"
                            type="image/webp"
                            >
                        <!--[if IE 9]></video><![endif]-->
                        {/if}
                        <img src="{$subcategory.image.bySize.category_default.url}" {if $sttheme.retina && isset($subcategory.image.bySize.category_default_2x.url)} srcset="{$subcategory.image.bySize.category_default_2x.url} 2x" {/if} alt="{$subcategory.name}" width="{$subcategory.image.bySize.category_default.width}" height="{$subcategory.image.bySize.category_default.height}" />
                        </picture>
                    </a>
                    {/if}
                    <h3 class="s_title_block {if $sttheme.display_subcate==3} nohidden {/if}"><a class="subcategory-name" href="{$subcategory.url}" title="{$subcategory.name}">{$subcategory.name}</a></h3>
                    {if $sttheme.display_subcate==2 && $subcategory.description}
                        <div class="subcat_desc">{$subcategory.description nofilter}</div>
                    {/if}
                </li>
            {/foreach}
            </ul>
        </div>
      {/if}
{/block}
{block name='product_list_footer'}
  {if ($sttheme.display_cate_desc_full==2 || (isset($steasybuilder) && $steasybuilder.is_editing)) && $category.description}
    <div id="category-description-bottom" class="category-description style_content mb-3 truncate_block st_showless_block_{if !empty($sttheme.showless_cate_desc)}1{else}0{/if} truncate_cate_desc_{$sttheme.truncate_cate_desc} {if $sttheme.display_cate_desc_full!=2} display_none {/if}">{if $sttheme.display_cate_desc_full==2}<div class="st_read_more_box">{$category.description nofilter}</div>{/if}<a href="javascript:;" title="{l s='Read more' d='Shop.Theme.Transformer'}" class="st_read_more" rel="nofollow"><span class="st_showmore_btn">{l s='Read more' d='Shop.Theme.Transformer'}</span><span class="st_showless_btn">{l s='Show less' d='Shop.Theme.Transformer'}</span></a></div>
  {/if}
  {hook h='displayCategoryFooter'}
  {hook h="displayFooterCategory"}
  
{* Fix #9: ItemList structured data for product categories - Enhanced with complete Schema.org properties *}
{* NOTE: Avoid using $listing.products directly to prevent PHP 7.4 "array offset on value of type null" notices *}
{if isset($products) && $products}
  {* Count products for numberOfItems *}
  {assign var='product_count' value=$products|count}
  <script type="application/ld+json">
  {ldelim}
    "@context": "https://schema.org",
    "@type": "ItemList",
    "name": "{if isset($category.name)}{$category.name|escape:'html':'UTF-8'|escape:'javascript'}{else}{l s='Products' d='Shop.Theme.Catalog'}{/if}",
    {if isset($category.description) && $category.description}
    "description": "{$category.description|strip_tags|escape:'html':'UTF-8'|escape:'javascript'|truncate:160}",
    {/if}
    "numberOfItems": {$product_count},
    "itemListElement": [
      {foreach from=$products item=product name=products}
      {ldelim}
        "@type": "ListItem",
        "position": {$smarty.foreach.products.iteration},
        "url": "{$product.url}"
      {rdelim}{if !$smarty.foreach.products.last},{/if}
      {/foreach}
    ]
  {rdelim}
  </script>
{/if}
{/block}

{block name='full_width_bottom' prepend}
  {hook h='displayFullWidthCategoryFooter'}
{/block}


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

{block name='product_list_header'}
	<div class="flex_container flex_start">
		{if $sttheme.brand_page_image || (isset($steasybuilder) && $steasybuilder.is_editing)}
        <div class="brand-img mr-3 mb-3 {if !$sttheme.brand_page_image} display_none {/if}">
          <picture>
          {assign var='brand_link' value=$link->getManufacturerImageLink($manufacturer.id, 'brand_default')}
          {if isset($stwebp) && isset($stwebp.brand_default) && $stwebp.brand_default}
          <!--[if IE 9]><video style="display: none;"><![endif]-->
            <source srcset="{$brand_link|regex_replace:'/\.jpg$/':'.webp'}"
              title="{$manufacturer.name}"
              type="image/webp"
              >
          <!--[if IE 9]></video><![endif]-->
          {/if}
        	<img src="{$brand_link}" alt="{$manufacturer.name}" class="general_border" width="{$sttheme.brand_default.width}" height="{$sttheme.brand_default.height}">
          </picture>
        </div>
        {/if}
        <div class="flex_child">
           <h1 class="page_heading">{$manufacturer.name}</h1>
           {hook h='displayManufacturerHeader'}
           {if $sttheme.brand_page_short_desc || (isset($steasybuilder) && $steasybuilder.is_editing)}<div id="manufacturer-short_description" class="{if !$sttheme.brand_page_short_desc} display_none {/if}">{$manufacturer.short_description nofilter}</div>{/if}
        </div>
	</div>
	{if $sttheme.brand_page_desc==1 && $manufacturer.description}
    <div id="manufacturer-description" class="manufacturer-description style_content mb-3 truncate_block st_showless_block_{if !empty($sttheme.showless_cate_desc)}1{else}0{/if} truncate_cate_desc_{$sttheme.truncate_cate_desc} {if $sttheme.brand_page_desc!=1} display_none {/if}"><div class="st_read_more_box">{$manufacturer.description nofilter}</div><a href="javascript:;" title="{l s='Read more' d='Shop.Theme.Transformer'}" class="st_read_more" rel="nofollow"><span class="st_showmore_btn">{l s='Read more' d='Shop.Theme.Transformer'}</span><span class="st_showless_btn">{l s='Show less' d='Shop.Theme.Transformer'}</span></a></div>
  {/if}
{/block}

{block name='product_list_footer'}
  {if $sttheme.brand_page_desc==2 && $manufacturer.description}
    <div id="manufacturer-description-bottom" class="manufacturer-description style_content mb-3 truncate_block st_showless_block_{if !empty($sttheme.showless_cate_desc)}1{else}0{/if} truncate_cate_desc_{$sttheme.truncate_cate_desc} {if $sttheme.brand_page_desc!=2} display_none {/if}"><div class="st_read_more_box">{$manufacturer.description nofilter}</div><a href="javascript:;" title="{l s='Read more' d='Shop.Theme.Transformer'}" class="st_read_more" rel="nofollow"><span class="st_showmore_btn">{l s='Read more' d='Shop.Theme.Transformer'}</span><span class="st_showless_btn">{l s='Show less' d='Shop.Theme.Transformer'}</span></a></div>
  {/if}
    {hook h='displayManufacturerFooter'}
{/block}
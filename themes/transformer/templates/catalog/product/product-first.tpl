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
    {if $sttheme.google_rich_snippets}<meta itemprop="url" content="{$product.url}">{/if}
    {if $sttheme.product_name_at_top==1 || ($sttheme.product_name_at_top==2 && $sttheme.is_mobile_device)}{include file='catalog/_partials/product-name.tpl'}{/if}
    <div class="row product_page_container product_page_layout_{(int)$sttheme.product_page_layout} product-container js-product-container">
      <div class="product_left_column col-lg-{$sttheme.pro_image_column_md} mb-2">
        {block name='page_content_container'}
          <section class="product_left_content mb-2">
            {block name='page_content'}

              {block name='product_cover_thumbnails'}
                {if ($product.images && count($product.images)) || (isset($st_pro_video) && isset($st_pro_video.videos))}
                  {include file='catalog/_partials/product-cover-thumbnails.tpl'}
                {else}
                  <img
                      src="{if isset($urls.no_picture_image)}{$urls.no_picture_image.bySize.{$sttheme.gallery_image_type}.url}{else}{$sttheme.img_prod_url}{$sttheme.lang_iso_code}-default-{$sttheme.gallery_image_type}.jpg{/if}"
                      alt="{$product.name}"
                    />
                {/if}
              {/block}

            {/block}
          </section>
          {hook h='displayAfterProductThumbs'}{*moved from the bottom of product-cover-thumbnails.tpl*}
          {hook h='displayProductLeftColumn'}
          {foreach $product.extraContent as $extra}
            {if $extra.moduleName=='stvideo'}
                {include file="module:stvideo/views/templates/hook/stvideo_link.tpl" stvideos=$extra.content video_position=array(13)}
            {/if}
          {/foreach}
        {/block}
        </div>
        <div class="product_middle_column col-lg-{$sttheme.pro_primary_column_md} mb-3">
          <div class="product_middle_column_inner">
          {block name='page_header_container'}
            {block name='page_header'}
              {if !$sttheme.product_name_at_top || ($sttheme.product_name_at_top==2 && !$sttheme.is_mobile_device)}{include file='catalog/_partials/product-name.tpl'}{/if}
            {/block}
          {/block}
          {block name='product_flags_under'}
            {foreach $product.extraContent as $extra}
            {if $extra.moduleName=='ststickers'}
                {include file='catalog/_partials/miniatures/sticker.tpl' stickers=$extra.content sticker_position=array(10,11) is_from_product_page=1 sticker_quantity=$product.quantity sticker_allow_oosp=$product.allow_oosp sticker_quantity_all_versions=$product.quantity_all_versions sticker_stock_text=$product.availability_message}
            {/if}
            {/foreach}
            {hook h='displayUnderProductName'}
          {/block}

          <div class="product-information">
            {block name='product_description_short'}
              <div id="product-description-short-{$product.id}" class="product-description-short mb-3 truncate_block st_showless_block_{if !empty($sttheme.showless_short_desc)}1{else}0{/if} truncate_cate_desc_{$sttheme.truncate_short_desc}" {if $sttheme.google_rich_snippets} itemprop="description" {/if}><div class="st_read_more_box">{$product.description_short nofilter}</div><a href="javascript:;" title="{l s='Read more' d='Shop.Theme.Transformer'}" class="st_read_more" rel="nofollow"><span class="st_showmore_btn">{l s='Read more' d='Shop.Theme.Transformer'}</span><span class="st_showless_btn">{l s='Show less' d='Shop.Theme.Transformer'}</span></a></div>
            {/block}

            <div class="steasy_divider between_short_and_price"><div class="steasy_divider_item"></div></div>

            <div class="mar_b1 pro_price_block flex_container flex_start">
              {block name='product_prices'}
                {include file='catalog/_partials/product-prices.tpl'}
              {/block}

              <div class="pro_price_right ">
                <div class="flex_box">
                {hook h='displayProductPriceRight'}
                {foreach $product.extraContent as $extra}
                  {if $extra.moduleName=='stvideo'}
                      {include file="module:stvideo/views/templates/hook/stvideo_link.tpl" stvideos=$extra.content video_position=array(11)}
                  {/if}
                {/foreach}
                </div>
              </div>
            </div>

            {if $product.is_customizable && count($product.customizations.fields)}
              {block name='product_customization'}
                {include file="catalog/_partials/product-customization.tpl" customizations=$product.customizations}
              {/block}
            {/if}

            {if !$sttheme.product_buy}{include file='catalog/_partials/product-buy.tpl'}{/if}
            
            {*moved from the product-detials.tpl*}
            {block name='product_condition'}
              {if ($sttheme.display_pro_condition || (isset($steasybuilder) && $steasybuilder.is_editing)) && $product.condition}
                <div class="product-condition  pro_extra_info flex_container {if !$sttheme.display_pro_condition} display_none {/if}">
                  <span class="pro_extra_info_label">{l s='Condition' d='Shop.Theme.Catalog'} </span>
                  <div class="pro_extra_info_content flex_child">
                      {$product.condition.label}
                  </div>
                </div>
              {/if}
            {/block}

            {block name='product_reference'}
            {if ($sttheme.display_pro_reference || (isset($steasybuilder) && $steasybuilder.is_editing)) && isset($product.reference_to_display)}
              <div class="product-reference pro_extra_info flex_container {if !$sttheme.display_pro_reference} display_none {/if}">
                <span class="pro_extra_info_label">{l s='Reference' d='Shop.Theme.Transformer'}: </span>
                <div class="pro_extra_info_content flex_child" {if $sttheme.google_rich_snippets} itemprop="sku" {/if}>{$product.reference_to_display}</div>
              </div>
            {/if}
            {if $product.ean13 && $sttheme.google_rich_snippets}<meta itemprop="GTIN13" content="{$product.ean13}">{/if}
            {if ($sttheme.show_brand_logo == 2 || $sttheme.show_brand_logo == 3) && isset($product_manufacturer->id) && $product_manufacturer->active}
              {include file='catalog/_partials/miniatures/product-brand.tpl'}
            {/if}
            {/block}
            {*moved from the product-detials.tpl end*}

            {block name='product_info_tags'}
              {if $sttheme.display_pro_tags==2}
                {foreach $product.extraContent as $extra}
                {if $extra.moduleName=='stthemeeditor' && isset($extra.content.tags)}
                <div class="product-info-tags pro_extra_info flex_container flex_start">
                  <span class="pro_extra_info_label">{l s='Tags' d='Shop.Theme.Transformer'}: </span>
                  <div class="pro_extra_info_content flex_child">
                    {foreach $extra.content.tags as $tag}
                          <a href="{url entity='search' params=['tag' => $tag|urlencode]}" title="{l s='More about' d='Shop.Theme.Transformer'} {$tag}" target="_top">{$tag}</a>{if !$tag@last}, {/if}
                      {/foreach}
                  </div>
                </div>
                {/if}
                {/foreach}
              {/if}
            {/block}

            {*remove displayReassurance from here, use custom content module if needed.*}
            {hook h='displayReassurance'}
            {hook h='displayProductCenterColumn'}
            {foreach $product.extraContent as $extra}
              {if $extra.moduleName=='stvideo'}
                  {include file="module:stvideo/views/templates/hook/stvideo_link.tpl" stvideos=$extra.content video_position=array(14)}
              {/if}
            {/foreach}
            
            {if $sttheme.product_tabs || (isset($steasybuilder) && $steasybuilder.is_editing)}<div class="right_more_info_block pro_more_info m-t-1 {if $sttheme.product_tabs_style==1} accordion_more_info {/if}{if !$sttheme.product_tabs} display_none {/if}">{if $sttheme.product_tabs}{include file='catalog/_partials/product-tabs.tpl'}{/if}</div>{/if}
            
        </div>
        </div>
      </div>

      {if $sttheme.pro_secondary_column_md}
      <div class="product_right_column col-lg-{if (12-$sttheme.pro_image_column_md-$sttheme.pro_primary_column_md) >= $sttheme.pro_secondary_column_md}{$sttheme.pro_secondary_column_md}{else}{12-$sttheme.pro_image_column_md-$sttheme.pro_primary_column_md}{/if}  mb-3">
        {if $sttheme.product_buy}{include file='catalog/_partials/product-buy.tpl'}{/if}

        {if $sttheme.show_brand_logo == 1 && isset($product_manufacturer->id) && $product_manufacturer->active}
          {include file='catalog/_partials/miniatures/product-brand.tpl'}
        {/if}
        {hook h='displayProductRightColumn'}
        {foreach $product.extraContent as $extra}
          {if $extra.moduleName=='stvideo'}
              {include file="module:stvideo/views/templates/hook/stvideo_link.tpl" stvideos=$extra.content video_position=array(15)}
          {/if}
        {/foreach}
      </div>
      {/if}

    </div>
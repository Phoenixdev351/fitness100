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
{block name='product_miniature_item'}
{*shouldEnableAddToCartButton Also in catalog\_partials\miniatures\product-simple.tpl*}
{assign var="has_add_to_cart" value=0}
{if $sttheme.display_add_to_cart!=3 && !$sttheme.is_catalog && $product.add_to_cart_url && ($product.quantity>0 || $product.allow_oosp)}{$has_add_to_cart=1}{/if}{*hao xiang quantity_wanted is not set in homepage and category page, so using add_to_cart_url only is not correct, have to use quantity and allow_oosp*}
{if $has_add_to_cart && !Configuration::get('PS_ATTRIBUTE_CATEGORY_DISPLAY') && isset($product.attributes) && count($product.attributes)}{$has_add_to_cart=0}{/if}

{assign var='list_display_sd' value=0}
{if isset($display_sd) && $display_sd}{$list_display_sd=$display_sd}{elseif !isset($display_sd) && $sttheme.show_short_desc_on_grid}{$list_display_sd=$sttheme.show_short_desc_on_grid}{/if}

{if isset($for_w) && $for_w == 'category'}
  {if $sttheme.cate_pro_image_type_name}
    {assign var="pro_image_type" value=$sttheme.cate_pro_image_type_name}
  {/if}
{elseif isset($image_type) && $image_type}
  {assign var="pro_image_type" value=$image_type}
{/if}
{if !isset($pro_image_type) || !$pro_image_type}
  {assign var="pro_image_type" value='home_default'}
{/if}
{if $sttheme.retina}{$pro_image_type_retina=$pro_image_type|cat:"_2x"}{/if}
{if isset($for_w)}{assign var="is_grid_view" value=1}{else}{assign var="is_grid_view" value=0}{/if}
{assign var='is_lazy' value=(!isset($for_w) && isset($lazy_load) && $lazy_load) || (isset($for_w) && $for_w == 'category' && $sttheme.cate_pro_lazy) || (isset($for_w) && $for_w != 'category' && isset($lazy_load) && $lazy_load)}
{assign var="ststickers_temp" value=false}
{if isset($product.ststickers)}{assign var="ststickers_temp" value=$product.ststickers}{/if}
{*use for_w to check if this file is loaded by product sliders, only products in sliders do not have for_w.*}
{if (!isset($for_w) || $for_w=='steb_pro') && isset($eb_pro_tm_slider) && $eb_pro_tm_slider!=3}{$sttheme.pro_tm_slider=$eb_pro_tm_slider}{/if}
<article class="{if !isset($for_w)} swiper-slide {/if} ajax_block_product js-product-miniature" data-id-product="{$product.id_product}" data-id-product-attribute="{$product.id_product_attribute}" data-minimal-quantity="{$product.minimal_quantity}" {if ($sttheme.google_rich_snippets==2 || (isset($from_product_page) && $from_product_page)) && (!isset($no_google_rich_snippets) || !$no_google_rich_snippets)} {if isset($from_product_page) && $from_product_page} itemprop="{$from_product_page}" {/if} itemscope itemtype="http://schema.org/Product" {/if}>
  <div class="pro_outer_box clearfix {$pro_image_type}">
    <div class="pro_first_box {if $sttheme.flyout_buttons==1}hover_fly_static{/if}{if $sttheme.flyout_buttons_on_mobile==1} moblie_flyout_buttons_show{/if}">
      {block name='product_thumbnail'}
        {if !isset($for_w) && !$sttheme.pro_tm_slider && isset($lazy_load) && $lazy_load}<i class="swiper-lazy-preloader fto-spin5 animate-spin"></i>{/if}
        {if (((!isset($for_w) || (isset($for_w) && $for_w != 'category')) && $sttheme.pro_tm_slider) || (isset($for_w) && $for_w == 'category' && $sttheme.pro_tm_slider_cate)) && isset($product.stthemeeditor.images)}
            {include file='catalog/_partials/miniatures/tm-slider.tpl' tm_thumbs=0 tm_lazyload=$is_lazy tm_cover=$product.cover.id_image is_grid_view=$is_grid_view}
            {block name='product_flags'}
                {include file='catalog/_partials/miniatures/sticker.tpl' stickers=$ststickers_temp sticker_position=array(0,1,2,3,4,5,6,7,8,9,12) sticker_quantity=$product.quantity sticker_allow_oosp=$product.allow_oosp sticker_quantity_all_versions=$product.quantity_all_versions sticker_stock_text=$product.availability_message}
            {/block}
        {else}
          {assign var='tm_stop_lazy' value=false}
          {if (((!isset($for_w) || (isset($for_w) && $for_w != 'category')) && $sttheme.pro_tm_slider) || (isset($for_w) && $for_w == 'category' && $sttheme.pro_tm_slider_cate)) && !isset($product.stthemeeditor.images)}
            {assign var='tm_stop_lazy' value=true}
          {/if}
          <a href="{$product.url}" title="{$product.name}" class="product_img_link {if $is_lazy && !$tm_stop_lazy} is_lazy {/if} {if $sttheme.pro_img_hover_scale} pro_img_hover_scale {/if}" {if isset($for_w) && $for_w == 'category' && $sttheme.infinite_blank} target="_blank" {/if}>
            <picture class="front_image_pic">
            {if isset($stwebp) && isset($stwebp.{$pro_image_type}) && $stwebp.{$pro_image_type} && isset($product.cover.bySize.{$pro_image_type}.url) && $product.cover.bySize.{$pro_image_type}.url}
            <!--[if IE 9]><video style="display: none;"><![endif]-->
              <source
                {if $is_lazy && !$tm_stop_lazy}data-{/if}srcset="{$product.cover.bySize.{$pro_image_type}.url|regex_replace:'/\.jpg$/':'.webp'}
                {if $sttheme.retina && isset($product.cover.bySize.{$pro_image_type_retina}.url) && $product.cover.bySize.{$pro_image_type_retina}.url},{$product.cover.bySize.{$pro_image_type_retina}.url|regex_replace:'/\.jpg$/':'.webp'} 2x{/if}"
                title="{if !empty($product.cover.legend)}{$product.cover.legend}{else}{$product.name}{/if}"
                type="image/webp"
                >
            <!--[if IE 9]></video><![endif]-->
            {/if}
            <img 
            {if $is_lazy && !$tm_stop_lazy}data-src{else}src{/if}="{if isset($product.cover.bySize.{$pro_image_type}.url) && $product.cover.bySize.{$pro_image_type}.url}{$product.cover.bySize.{$pro_image_type}.url}{elseif isset($urls.no_picture_image)}{$urls.no_picture_image.bySize.{$pro_image_type}.url}{else}{$sttheme.img_prod_url}{$sttheme.lang_iso_code}-default-{$pro_image_type}.jpg{/if}"
            {if $sttheme.retina && isset($product.cover.bySize.{$pro_image_type_retina}.url) && $product.cover.bySize.{$pro_image_type_retina}.url}
              {if $is_lazy && !$tm_stop_lazy}data-srcset{else}srcset{/if}="{$product.cover.bySize.{$pro_image_type_retina}.url} 2x"
            {/if}
            width="{$product.cover.bySize.{$pro_image_type}.width}" height="{$product.cover.bySize.{$pro_image_type}.height}" alt="{if !empty($product.cover.legend)}{$product.cover.legend}{else}{$product.name}{/if}" class="front-image {if !isset($for_w) && isset($lazy_load) && $lazy_load} swiper-lazy {/if} {if (isset($for_w) && $for_w == 'category' && $sttheme.cate_pro_lazy && !$tm_stop_lazy) || (isset($for_w) && $for_w != 'category' && isset($lazy_load) && $lazy_load && !$tm_stop_lazy)} cate_pro_lazy {/if}" />
            </picture>
            {if ($sttheme.google_rich_snippets==2 || (isset($from_product_page) && $from_product_page)) && (!isset($no_google_rich_snippets) || !$no_google_rich_snippets)}{if isset($product.cover.bySize.{$pro_image_type}.url) && $product.cover.bySize.{$pro_image_type}.url}<meta itemprop="image" content="{$product.cover.bySize.{$pro_image_type}.url}">{/if}{/if}
            {if isset($product.sthoverimage.hover)}
              {*to do lazy load this*}
              <picture class="back_image_pic">
              {if isset($stwebp) && isset($stwebp.{$pro_image_type}) && $stwebp.{$pro_image_type}}
              <!--[if IE 9]><video style="display: none;"><![endif]-->
                <source
                  {if $is_lazy}data-{/if}srcset="{$product.sthoverimage.bySize.{$pro_image_type}.url|regex_replace:'/\.jpg$/':'.webp'}
                  {if $sttheme.retina && isset($product.sthoverimage.bySize.{$pro_image_type_retina}.url)},{$product.sthoverimage.bySize.{$pro_image_type_retina}.url|regex_replace:'/\.jpg$/':'.webp'} 2x{/if}"
                  title="{if !empty($product.sthoverimage.legend)}{$product.sthoverimage.legend}{else}{$product.name}{/if}"
                  type="image/webp"
                  >
              <!--[if IE 9]></video><![endif]-->
              {/if}
              <img 
                {if $is_lazy}data-src{else}src{/if}="{$product.sthoverimage.bySize.{$pro_image_type}.url}"
                {if $sttheme.retina && isset($product.sthoverimage.bySize.{$pro_image_type_retina}.url)}
                  {if $is_lazy}data-srcset{else}srcset{/if}="{$product.sthoverimage.bySize.{$pro_image_type_retina}.url} 2x"
                {/if}
               alt="{if !empty($product.sthoverimage.legend)}{$product.sthoverimage.legend}{else}{$product.name}{/if}" width="{$product.sthoverimage.bySize.{$pro_image_type}.width}" height="{$product.sthoverimage.bySize.{$pro_image_type}.height}"  class="back-image {if !isset($for_w) && isset($lazy_load) && $lazy_load} swiper-lazy {/if} {if (isset($for_w) && $for_w == 'category' && $sttheme.cate_pro_lazy && !$tm_stop_lazy) || (isset($for_w) && $for_w != 'category' && isset($lazy_load) && $lazy_load && !$tm_stop_lazy)} cate_pro_lazy {/if}" />
               </picture>
            {/if}
            {if $is_lazy && !$tm_stop_lazy}<img src="{$sttheme.img_prod_url}{$sttheme.lang_iso_code}-default-{$pro_image_type}.{if isset($stwebp)}webp{else}jpg{/if}" class="holder" width="{$product.cover.bySize.{$pro_image_type}.width}" height="{$product.cover.bySize.{$pro_image_type}.height}" alt="{if !empty($product.cover.legend)}{$product.cover.legend}{else}{$product.name}{/if}" />{/if}
            {block name='product_flags'}
                {include file='catalog/_partials/miniatures/sticker.tpl' stickers=$ststickers_temp sticker_position=array(0,1,2,3,4,5,6,7,8,9,12) sticker_quantity=$product.quantity sticker_allow_oosp=$product.allow_oosp sticker_quantity_all_versions=$product.quantity_all_versions sticker_stock_text=$product.availability_message}
            {/block}
          </a>
        {/if}
        {if isset($wishlist_position) && $wishlist_position && $wishlist_position!=10}
            {include file='module:stwishlist/views/templates/hook/icon.tpl' is_wished=(isset($product.stwishlist.wished) && $product.stwishlist.wished) fromnocache=(isset($for_w) && $for_w == 'category')}
        {/if}
        {if isset($loved_position) && $loved_position && $loved_position!=10 && $loved_position!=11}
            {include file='module:stlovedproduct/views/templates/hook/icon.tpl' id_source=$product.id_product is_loved=(isset($product.stlovedproduct.loved) && $product.stlovedproduct.loved) fromnocache=(isset($for_w) && $for_w == 'category')}
        {/if}
      {/block}
      {if $sttheme.flyout_buttons==0 || $sttheme.flyout_buttons==1}
        {include file='catalog/_partials/miniatures/hover_fly.tpl'}
      {/if}
      {if isset($countdown_v_alignment) && $countdown_v_alignment!=2}{include file='catalog/_partials/miniatures/countdown.tpl'}{/if}
    </div>
    <div class="pro_second_box pro_block_align_{$sttheme.pro_block_align}">
        {if (((!isset($for_w) || (isset($for_w) && $for_w != 'category')) && $sttheme.pro_tm_slider==2) || (isset($for_w) && $for_w == 'category' && $sttheme.pro_tm_slider_cate==2)) && isset($product.stthemeeditor.images)}
            {include file='catalog/_partials/miniatures/tm-slider.tpl' tm_thumbs=1 tm_lazyload=$is_lazy tm_cover=$product.cover.id_image}
        {/if}
      {block name='product_flags_under'}
        {include file='catalog/_partials/miniatures/sticker.tpl' stickers=$ststickers_temp sticker_position=array(10) sticker_quantity=$product.quantity sticker_allow_oosp=$product.allow_oosp sticker_quantity_all_versions=$product.quantity_all_versions sticker_stock_text=$product.availability_message}
      {/block}

      {if $sttheme.pro_display_category_name || (isset($steasybuilder) && $steasybuilder.is_editing)}<a href="{url entity='category' id=$product.id_category_default params=['alias' => $product.category]}" title="{$product.category_name}" class="pro_mini_cate_name mar_b6 {if !$sttheme.pro_display_category_name} display_none {/if}" {if isset($for_w) && $for_w == 'category' && $sttheme.infinite_blank} target="_blank" {/if}>{$product.category_name}</a>{/if}
      {if isset($product.stproductcomments) && $product.stproductcomments && $product.stproductcomments.pro_posi}
        {include file='catalog/_partials/miniatures/rating-box.tpl'}
      {/if}
      {block name='product_name'}
      {if isset($sttheme.length_of_product_name) && $sttheme.length_of_product_name==1}
          {assign var="length_of_product_name" value=70}
      {/if}
      <div class="flex_box flex_start mini_name">
      <p {if ($sttheme.google_rich_snippets==2 || (isset($from_product_page) && $from_product_page)) && (!isset($no_google_rich_snippets) || !$no_google_rich_snippets)} itemprop="name" {/if} class="s_title_block flex_child {if isset($sttheme.length_of_product_name)}{if $sttheme.length_of_product_name==3} two_rows {elseif $sttheme.length_of_product_name==1 || $sttheme.length_of_product_name==2} nohidden {/if}{/if}"><a href="{$product.url}" title="{$product.name}" {if ($sttheme.google_rich_snippets==2 || (isset($from_product_page) && $from_product_page)) && (!isset($no_google_rich_snippets) || !$no_google_rich_snippets)} itemprop="url" {/if} {if isset($for_w) && $for_w == 'category' && $sttheme.infinite_blank} target="_blank" {/if}>{if isset($sttheme.length_of_product_name) && $sttheme.length_of_product_name==1}{$product.name|truncate:$length_of_product_name:'...'}{else}{$product.name}{/if}</a></p>
      {if (isset($loved_position) && $loved_position && $loved_position==11) || $sttheme.pro_block_align==2}
        {if isset($loved_position) && $loved_position && $loved_position==11}
        {include file='module:stlovedproduct/views/templates/hook/fly.tpl' id_source=$product.id_product classname="btn_inline hide_btn_text" is_loved=(isset($product.stlovedproduct.loved) && $product.stlovedproduct.loved) fromnocache=(isset($for_w) && $for_w == 'category')}
        {/if}
        {include file='catalog/_partials/miniatures/product-price.tpl'}
      {/if}
      </div>
      {/block}

      {if ($sttheme.pro_list_display_brand_name || (isset($steasybuilder) && $steasybuilder.is_editing)) && $product.id_manufacturer}
        <div class="pro_list_manufacturer pad_b6 {if !$sttheme.pro_list_display_brand_name} display_none {/if}">
        {if isset($product.manufacturer_name)}
        {$product.manufacturer_name|truncate:60:'...'}
        {else}
        {Manufacturer::getNameById($product.id_manufacturer)|truncate:60:'...'}
        {/if}
        </div>
      {/if}

      {if ($sttheme.pro_list_display_reference || (isset($steasybuilder) && $steasybuilder.is_editing)) && isset($product.reference) && $product.reference}
        <div class="pro_list_reference pad_b6 {if !$sttheme.pro_list_display_reference} display_none {/if}">{l s='Reference' d='Shop.Theme.Catalog'}: {$product.reference}</div>
      {/if}

      <div class="pro_kuan_box {if $sttheme.pro_block_align==1} flex_box flex_space_between {/if}">
      {block name='product_price_and_shipping'}
        {if $sttheme.pro_block_align!=2}{include file='catalog/_partials/miniatures/product-price.tpl'}{/if}
      {/block}
      {block name='product_variants'}
        {if ($sttheme.display_color_list || (isset($steasybuilder) && $steasybuilder.is_editing)) && $product.main_variants}
          {include file='catalog/_partials/variant-links.tpl' variants=$product.main_variants}
        {/if}
      {/block}
      </div>
      {if isset($product.stproductcomments) && $product.stproductcomments && !$product.stproductcomments.pro_posi}
        {include file='catalog/_partials/miniatures/rating-box.tpl'}
      {/if}
      {if isset($countdown_v_alignment) && $countdown_v_alignment==2}{include file='catalog/_partials/miniatures/countdown.tpl'}{/if}
      {block name='product_reviews'}
        {hook h='displayProductListReviews' product=$product}
      {/block}
      <div class="product-desc pad_b6 {if $list_display_sd} display_sd {/if} " {if ($sttheme.google_rich_snippets==2 || (isset($from_product_page) && $from_product_page)) && (!isset($no_google_rich_snippets) || !$no_google_rich_snippets)} itemprop="description" {/if}>{if $list_display_sd==2}{$product.description_short nofilter}{else}{$product.description_short|strip_tags:false|truncate:220:'...'}{/if}</div>
      
      {if $sttheme.display_add_to_cart!=3 || $sttheme.use_view_more_instead==1 || $sttheme.use_view_more_instead==2}
      <div class="act_box_cart {if $sttheme.display_add_to_cart==2 || $sttheme.display_add_to_cart==5 || $sttheme.use_view_more_instead==2} display_normal {elseif $sttheme.display_add_to_cart==1 || $sttheme.display_add_to_cart==4 || (!$sttheme.display_add_to_cart && ($sttheme.pro_quantity_input==1 || $sttheme.pro_quantity_input==3)) || $sttheme.use_view_more_instead==1} display_when_hover {/if}{if $sttheme.mobile_add_to_cart} add_show_on_mobile {else} add_hide_on_mobile {/if}">
        {if $sttheme.display_add_to_cart!=3}
        {if $has_add_to_cart && ($sttheme.pro_quantity_input==1 || $sttheme.pro_quantity_input==3 || (isset($steasybuilder) && $steasybuilder.is_editing))}
        <div class="s_quantity_wanted qty_wrap {if $sttheme.pro_quantity_input!=1 && $sttheme.pro_quantity_input!=3} display_none {/if}">
            <input
                class="pro_quantity"
                type="text"
                value="{if $product.minimal_quantity}{$product.minimal_quantity}{else}1{/if}"
                name="pro_quantity"
                data-minimal-quantity="{$product.minimal_quantity}"
                data-quantity="{$product.quantity}"
                data-allow-oosp="{$product.allow_oosp}"
              />
        </div>
        {/if}
        {assign var="add_to_cart_class" value="btn btn-default"}
        {if $sttheme.display_add_to_cart==4 || $sttheme.display_add_to_cart==5}{assign var="add_to_cart_class" value="btn btn-link"}{/if}
        {if $has_add_to_cart}
          {include file='catalog/_partials/miniatures/btn-add-to-cart.tpl' classname=$add_to_cart_class}
        {else}
          {include file='catalog/_partials/miniatures/btn-view-more.tpl' classname=$add_to_cart_class}
        {/if}
        {if ($sttheme.use_view_more_instead==1 || $sttheme.use_view_more_instead==2) && $has_add_to_cart}{include file='catalog/_partials/miniatures/btn-view-more.tpl' classname="btn btn-default"}{/if}
        {elseif $sttheme.use_view_more_instead==1 || $sttheme.use_view_more_instead==2}
        {include file='catalog/_partials/miniatures/btn-view-more.tpl' classname="btn btn-default"}
        {/if}
      </div>
      {/if}

      <div class="act_box_inner pad_b6 mar_t4 flex_box">
        {if ($sttheme.flyout_quickview || (isset($steasybuilder) && $steasybuilder.is_editing)) && (!isset($from_product_page) || !$from_product_page)}
          {$classname_act_quickview='btn_inline'}
          {if !$sttheme.flyout_quickview && isset($steasybuilder) && $steasybuilder.is_editing}{$classname_act_quickview='btn_inline display_none'}{/if}
          {include file='catalog/_partials/miniatures/btn-quick-view.tpl' classname=$classname_act_quickview}
        {/if}
        {if !$sttheme.use_view_more_instead && !$sttheme.display_add_to_cart && $has_add_to_cart}{include file='catalog/_partials/miniatures/btn-view-more.tpl' classname="btn_inline"}{/if}
        {if isset($wishlist_position) && !$wishlist_position}
          {include file='module:stwishlist/views/templates/hook/fly.tpl' classname="btn_inline" is_wished=(isset($product.stwishlist.wished) && $product.stwishlist.wished) fromnocache=(isset($for_w) && $for_w == 'category')}
        {/if}
        {if isset($loved_position) && !$loved_position}
          {include file='module:stlovedproduct/views/templates/hook/fly.tpl' id_source=$product.id_product classname="btn_inline" is_loved=(isset($product.stlovedproduct.loved) && $product.stlovedproduct.loved) fromnocache=(isset($for_w) && $for_w == 'category')}
        {/if}
        {if isset($stcompare) && ( $stcompare.fly_out || (isset($steasybuilder) && $steasybuilder.is_editing) )}
          {$classname_act_stcompare='btn_inline'}
          {if !$stcompare.fly_out && isset($steasybuilder) && $steasybuilder.is_editing}{$classname_act_stcompare='btn_inline display_none'}{/if}
          {include file='module:stcompare/views/templates/hook/fly.tpl' id_product=$product.id_product classname=$classname_act_stcompare is_compared=(isset($product.stcompare.compared) && $product.stcompare.compared) fromnocache=(isset($for_w) && $for_w == 'category')}
        {/if}
        {if $sttheme.flyout_share || (isset($steasybuilder) && $steasybuilder.is_editing)}
          {$classname_act_share='btn_inline link_color'}
          {if !$sttheme.flyout_share || (isset($steasybuilder) && $steasybuilder.is_editing)}{$classname_act_share='btn_inline link_color display_none'}{/if}
          {include file='module:stsocial/views/templates/hook/stsocial-drop.tpl' pro_share_drop=true social_label=0 classname=$classname_act_share}
        {/if}
      </div>

      {block name='product_flags_bottom'}
        {include file='catalog/_partials/miniatures/sticker.tpl' stickers=$ststickers_temp sticker_position=array(11) sticker_quantity=$product.quantity sticker_allow_oosp=$product.allow_oosp sticker_quantity_all_versions=$product.quantity_all_versions sticker_stock_text=$product.availability_message}
      {/block}
    </div>
    {if $sttheme.flyout_buttons==2}
      <div class="bottom_hover_fly">
      {include file='catalog/_partials/miniatures/hover_fly.tpl'}
      </div>
    {/if}
  </div>
</article>
{/block}
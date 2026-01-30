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
{assign var="image_count" value=count($product.images)}
{if $sttheme.product_gallerys==2}{$image_count=count($sttheme.pro_images)}{/if}
{if $sttheme.product_thumbnails==6}{if $image_count>1 || (isset($st_pro_video) && isset($st_pro_video.videos))}{$sttheme.product_thumbnails=0}{elseif $image_count<2}{$sttheme.product_thumbnails=5}{/if}{/if}
{if $sttheme.is_mobile_device}
    {if $sttheme.product_thumbnails_mobile==5 && $image_count<2}{$sttheme.product_thumbnails_mobile=3}{elseif $sttheme.product_thumbnails_mobile==5 && $image_count>1}{$sttheme.product_thumbnails_mobile=4}{/if}
    {if $sttheme.product_thumbnails_mobile==1}
      {$sttheme.product_thumbnails=3}
    {elseif $sttheme.product_thumbnails_mobile==2}
      {$sttheme.product_thumbnails=4}
    {elseif $sttheme.product_thumbnails_mobile==3}
      {$sttheme.product_thumbnails=5}
    {elseif $sttheme.product_thumbnails_mobile==4}
      {$sttheme.product_thumbnails=0}
    {elseif $sttheme.product_thumbnails_mobile==0}
      {if $sttheme.product_thumbnails==7}
        {$sttheme.product_thumbnails=0}
      {elseif $sttheme.product_thumbnails==8}
        {$sttheme.product_thumbnails=5}
      {/if}
    {/if}
{/if}
{assign var="do_from_quickview" value=false}
{if isset($from_quickview) && $from_quickview}{$do_from_quickview=true}{/if}
{if $do_from_quickview && ($sttheme.product_thumbnails==7 || $sttheme.product_thumbnails==8)}
  {if $sttheme.product_thumbnails==7}
    {$sttheme.product_thumbnails=0}
  {elseif $sttheme.product_thumbnails==8}
    {$sttheme.product_thumbnails=5}
  {/if}
{/if}
{if $sttheme.pro_thumnbs_per_fw>$image_count}{$sttheme.pro_thumnbs_per_fw=$image_count}{/if}
{if $sttheme.pro_thumnbs_per_xxl>$image_count}{$sttheme.pro_thumnbs_per_xxl=$image_count}{/if}
{if $sttheme.pro_thumnbs_per_xl>$image_count}{$sttheme.pro_thumnbs_per_xl=$image_count}{/if}
{if $sttheme.pro_thumnbs_per_lg>$image_count}{$sttheme.pro_thumnbs_per_lg=$image_count}{/if}
{if $sttheme.pro_thumnbs_per_md>$image_count}{$sttheme.pro_thumnbs_per_md=$image_count}{/if}
{if $sttheme.pro_thumnbs_per_sm>$image_count}{$sttheme.pro_thumnbs_per_sm=$image_count}{/if}
{if $sttheme.pro_thumnbs_per_xs>$image_count}{$sttheme.pro_thumnbs_per_xs=$image_count}{/if}
{if $sttheme.responsive_max==3 && $sttheme.pro_thumnbs_per_fw}
    {assign var='slidesPerView' value=$sttheme.pro_thumnbs_per_fw}
{else}
    {if $sttheme.responsive_max==2}
        {assign var='slidesPerView' value=$sttheme.pro_thumnbs_per_xxl}
    {elseif $sttheme.responsive_max>=1}
        {assign var='slidesPerView' value=$sttheme.pro_thumnbs_per_xl}
    {else}
        {assign var='slidesPerView' value=$sttheme.pro_thumnbs_per_lg}
    {/if}
{/if}
<div class="images-container 
 pro_number_{$slidesPerView}
 pro_number_xxl_{$sttheme.pro_thumnbs_per_xxl}
 pro_number_xl_{$sttheme.pro_thumnbs_per_xl}
 pro_number_lg_{$sttheme.pro_thumnbs_per_lg}
 pro_number_md_{$sttheme.pro_thumnbs_per_md}
 pro_number_sm_{$sttheme.pro_thumnbs_per_sm}
 pro_number_xs_{$sttheme.pro_thumnbs_per_xs}
">
  {if !$do_from_quickview && ($sttheme.enable_thickbox==2 || ($sttheme.enable_thickbox==3 && $sttheme.is_mobile_device))}
  <div class="kk_container">
    <a class="kk_close" href="javascript:;" title="{l s='Close' d='Shop.Theme.Catalog'}">&times;</a>
    <div class="swiper-container pro_gallery_kk swiper-button-lr swiper-navigation-circle" {if $language.is_rtl} dir="rtl" {/if}>
        <div class="swiper-wrapper">
              {assign var="curr_combination_thumbs" value=[]}
              {foreach $product.images as $index => $image}
                {$curr_combination_thumbs[]=$image.id_image}
                {include file='catalog/_partials/product-popup-item.tpl'}
              {/foreach}
              {if $sttheme.product_gallerys==2}
                {foreach $sttheme.pro_images as $index => $image}
                  {if !in_array($image.id_image, $curr_combination_thumbs)}
                    {include file='catalog/_partials/product-popup-item.tpl'}
                  {/if}
                {/foreach}
              {/if}
        </div>
        <div class="swiper-button swiper-button-next"><i class="fto-left-open-3 slider_arrow_left"></i><i class="fto-right-open-3 slider_arrow_right"></i></div>
        <div class="swiper-button swiper-button-prev"><i class="fto-left-open-3 slider_arrow_left"></i><i class="fto-right-open-3 slider_arrow_right"></i></div>
        <div class="swiper-pagination"></div>
    </div>
    <script type="text/javascript">
    //<![CDATA[
        {literal}
        if(typeof(swiper_options) ==='undefined')
        var swiper_options = [];
        {/literal}
        {literal}
        swiper_options.push({
            {/literal}
            id_st: '.pro_gallery_kk',
            spaceBetween: {(int)$sttheme.gallery_spacing},
            {literal}
            navigation:{
              nextEl: '.pro_gallery_kk .swiper-button-next',
              prevEl: '.pro_gallery_kk .swiper-button-prev'
            },
            pagination: {
              el: '.pro_gallery_kk .swiper-pagination',
              type: 'custom',
              clickable: true,
              renderCustom: function (swiper, current, total) {
                return '<span>' + current + ' / ' + total + '</span>';
              }
            },
            {/literal}
            loop: false,
            watchSlidesProgress: true,
            watchSlidesVisibility: true,
            slidesPerView: {if $sttheme.pro_kk_per_xs<$image_count}{$sttheme.pro_kk_per_xs}{else}{$image_count}{/if},
            {if $sttheme.pro_main_image_trans}
              {literal}
              fadeEffect: {
                crossFade: true
              },
              {/literal}
            {/if}
            {if $sttheme.responsive}
            {literal}
            breakpoints: {
                {/literal}
                {literal}1600: {slidesPerView: {/literal}{if $sttheme.pro_kk_per_fw<$image_count}{$sttheme.pro_kk_per_fw}{else}{$image_count}{/if}{literal} },{/literal}
                {literal}1440: {slidesPerView: {/literal}{if $sttheme.pro_kk_per_xxl<$image_count}{$sttheme.pro_kk_per_xxl}{else}{$image_count}{/if}{literal} },{/literal}
                {literal}1200: {slidesPerView: {/literal}{if $sttheme.pro_kk_per_xl<$image_count}{$sttheme.pro_kk_per_xl}{else}{$image_count}{/if}{literal} },{/literal}
                {literal}992: {slidesPerView: {/literal}{if $sttheme.pro_kk_per_lg<$image_count}{$sttheme.pro_kk_per_lg}{else}{$image_count}{/if}{literal} },{/literal}
                768: {literal}{slidesPerView: {/literal}{if $sttheme.pro_kk_per_md<$image_count}{$sttheme.pro_kk_per_md}{else}{$image_count}{/if}{literal} },{/literal}
                480: {literal}{slidesPerView: {/literal}{if $sttheme.pro_kk_per_sm<$image_count}{$sttheme.pro_kk_per_sm}{else}{$image_count}{/if}{literal} }
            },
            {/literal}
            {/if}
            {literal}
            lazy:{
              loadPrevNext: {/literal}{if $sttheme.lazyload_main_gallery}false{else}true{/if}{literal},
              loadPrevNextAmount: 1
            },
            zoom: {
              maxRatio: {/literal}{if $sttheme.pro_kk_maxratio}{$sttheme.pro_kk_maxratio}{else}2{/if}{literal},
            },
            mousewheel: true,
            roundLengths: true,
            centeredSlides: true,
            observer: true,
            observeParents: true
        });
        {/literal} 
    //]]>
    </script>
  </div>
  {/if}
<div class="images-container-{$sttheme.product_thumbnails} {if $sttheme.product_thumbnails==1 || $sttheme.product_thumbnails==2 || $sttheme.product_thumbnails==7} row {/if}">
{if !$sttheme.gallery_thumbnails_width_v}{$sttheme.gallery_thumbnails_width_v=3}{/if}
{assign var="gallery_top_width_v" value=12-$sttheme.gallery_thumbnails_width_v}
{assign var="gallery_thumbnails_width_v_mobile" value=floor($sttheme.gallery_thumbnails_width_v)+1}
{assign var="gallery_top_width_v_mobile" value=12-$gallery_thumbnails_width_v_mobile}
<div class="pro_gallery_top_container {if $sttheme.product_thumbnails==1 || $sttheme.product_thumbnails==2 || $sttheme.product_thumbnails==7} col-{$gallery_top_width_v_mobile|replace:'.':'-'} col-md-{$gallery_top_width_v|replace:'.':'-'} {/if}{if $sttheme.product_thumbnails==1 || $sttheme.product_thumbnails==7} push-{$gallery_thumbnails_width_v_mobile|replace:'.':'-'} push-md-{$sttheme.gallery_thumbnails_width_v|replace:'.':'-'} {/if} {if $sttheme.product_thumbnails!=1 && $sttheme.product_thumbnails!=2 && $sttheme.product_thumbnails!=7} mb-3 {/if}">
  <div class="pro_gallery_top_inner posi_rel">
  {if $sttheme.product_thumbnails==7 || $sttheme.product_thumbnails==8}
    <div class="st_image_scrolling_wrap">
      <div class="row">
        {assign var="curr_combination_thumbs" value=[]}
        {assign var="curr_index" value=1}
        {assign var="cover_id" value=$product.cover.id_image}
        {if isset($product.default_image)}{$cover_id=$product.default_image.id_image}{/if}
          {foreach $product.images as $index => $image}
            {if $image.id_image == $cover_id}{assign var='pro_gallery_initial' value=$index}{/if}
            {$curr_combination_thumbs[]=$image.id_image}
            {include file='catalog/_partials/product-cover-scrolling.tpl'}
            {$curr_index=$curr_index+1}
          {/foreach}
          {if $sttheme.product_gallerys==2}
            {foreach $sttheme.pro_images as $index => $image}
              {if !in_array($image.id_image, $curr_combination_thumbs)}
                {include file='catalog/_partials/product-cover-scrolling.tpl'}
                {$curr_index=$curr_index+1}
              {/if}
            {/foreach}
          {/if}
      </div>
    </div>
  {else}
  {block name='product_flags'}
    {foreach $product.extraContent as $extra}
        {if $extra.moduleName=='ststickers'}
            {include file='catalog/_partials/miniatures/sticker.tpl' stickers=$extra.content sticker_position=array(0,1,2,3,4,5,6,7,8,9,12) is_from_product_page=1 sticker_quantity=$product.quantity sticker_allow_oosp=$product.allow_oosp sticker_quantity_all_versions=$product.quantity_all_versions sticker_stock_text=$product.availability_message}
        {elseif $extra.moduleName=='stvideo'}
            {include file="module:stvideo/views/templates/hook/stvideo.tpl" stvideos=$extra.content.videos video_position=array(1,2,3,4,5,6,7,8,9)}
        {/if}
    {/foreach}
  {/block}

  
  {block name='product_cover'}
    {if !$do_from_quickview && $sttheme.enable_thickbox}
      <div class="pro_popup_trigger_box">
      {*The same code in the product.js, using js to add images fast, but not standard*}
      {assign var="curr_combination_thumbs" value=[]}
      {foreach $product.images as $index => $image}
        {$curr_combination_thumbs[]=$image.id_image}
        <a href="{if isset($stwebp) && isset($stwebp.superlarge_default) && $stwebp.superlarge_default}{$image.bySize.superlarge_default.url|regex_replace:'/\.jpg$/':'.webp'}{else}{$image.bySize.superlarge_default.url}{/if}" class="pro_popup_trigger {if $sttheme.enable_thickbox==1 || ($sttheme.enable_thickbox==3 && !$sttheme.is_mobile_device)} st_popup_image st_pro_popup_image{elseif $sttheme.enable_thickbox==2 || ($sttheme.enable_thickbox==3 && $sttheme.is_mobile_device)}kk_triger{/if} replace-2x layer_icon_wrap" data-group="pro_gallery_popup_trigger" title="{if $image.legend}{$image.legend}{else}{$product.name}{/if}"><i class="fto-resize-full"></i></a>
      {/foreach}
      {if $sttheme.product_gallerys==2}
        {foreach $sttheme.pro_images as $index => $image}
          {if !in_array($image.id_image, $curr_combination_thumbs)}
          <a href="{if isset($stwebp) && isset($stwebp.superlarge_default) && $stwebp.superlarge_default}{$image.bySize.superlarge_default.url|regex_replace:'/\.jpg$/':'.webp'}{else}{$image.bySize.superlarge_default.url}{/if}" class="pro_popup_trigger {if $sttheme.enable_thickbox==1 || ($sttheme.enable_thickbox==3 && !$sttheme.is_mobile_device)} st_popup_image st_pro_popup_image{elseif $sttheme.enable_thickbox==2 || ($sttheme.enable_thickbox==3 && $sttheme.is_mobile_device)}kk_triger{/if} replace-2x layer_icon_wrap" data-group="pro_gallery_popup_trigger" title="{if $image.legend}{$image.legend}{else}{$product.name}{/if}"><i class="fto-resize-full"></i></a>
          {/if}
        {/foreach}
      {/if}
      </div>
    {/if}
    <div class="swiper-container pro_gallery_top swiper-button-lr {if $sttheme.thumbs_direction_nav==3} swiper-navigation-rectangle {elseif $sttheme.thumbs_direction_nav==2} swiper-navigation-arrow {else} swiper-navigation-circle {/if}" {if $language.is_rtl} dir="rtl" {/if}>
        <div class="swiper-wrapper">
            {assign var='pro_gallery_initial' value=0}
            {assign var="cover_id" value=$product.cover.id_image}
            {if isset($product.default_image)}{$cover_id=$product.default_image.id_image}{/if}
              {foreach $product.images as $index => $image}
                {if $image.id_image == $cover_id}{assign var='pro_gallery_initial' value=$index}{/if}
              {/foreach}
              {assign var="curr_combination_thumbs" value=[]}
              {foreach $product.images as $index => $image}
                {$curr_combination_thumbs[]=$image.id_image}
                {include file='catalog/_partials/product-cover-item.tpl' disable_lazyloading=$pro_gallery_initial}
              {/foreach}
              {if $sttheme.product_gallerys==2}
                {foreach $sttheme.pro_images as $index => $image}
                  {if !in_array($image.id_image, $curr_combination_thumbs)}
                    {include file='catalog/_partials/product-cover-item.tpl' disable_lazyloading=$pro_gallery_initial}
                  {/if}
                {/foreach}
              {/if}
        </div>
        <div class="swiper-button swiper-button-next"><i class="fto-left-open-3 slider_arrow_left"></i><i class="fto-right-open-3 slider_arrow_right"></i></div>
        <div class="swiper-button swiper-button-prev"><i class="fto-left-open-3 slider_arrow_left"></i><i class="fto-right-open-3 slider_arrow_right"></i></div>
        {if $sttheme.product_thumbnails==4}<div class="swiper-pagination"></div>{/if}
    </div>
    <script type="text/javascript">
    //<![CDATA[
        {literal}
        if(typeof(swiper_options) ==='undefined')
        var swiper_options = [];
        {/literal}
        {literal}
        swiper_options.push({
            {/literal}
            id_st: '.pro_gallery_top',
            speed: 0,
            spaceBetween: {(int)$sttheme.gallery_spacing},
            {literal}
            navigation:{
              nextEl: '.pro_gallery_top .swiper-button-next',
              prevEl: '.pro_gallery_top .swiper-button-prev'
            },
            {/literal}
            {if $sttheme.product_thumbnails==4}
            {literal}
            pagination: {
              el: '.pro_gallery_top .swiper-pagination',
              clickable: true,
              type: 'bullets'
            },
            {/literal}
            {/if}
            loop: false,
            watchSlidesProgress: true,
            watchSlidesVisibility: true,
            slidesPerView: {$sttheme.pro_thumnbs_per_xs},
            {if $sttheme.pro_main_image_trans}
              {literal}
              fadeEffect: {
                crossFade: true
              },
              {/literal}
            {/if}
            {if $sttheme.responsive}
            {literal}
            breakpoints: {
                {/literal}
                {if $sttheme.responsive_max==3 && $sttheme.pro_thumnbs_per_fw}{literal}1600: {slidesPerView: {/literal}{$sttheme.pro_thumnbs_per_fw}{literal} },{/literal}{/if}
                {if $sttheme.responsive_max==2}{literal}1440: {slidesPerView: {/literal}{$sttheme.pro_thumnbs_per_xxl}{literal} },{/literal}{/if}
                {if $sttheme.responsive_max>=1}{literal}1200: {slidesPerView: {/literal}{$sttheme.pro_thumnbs_per_xl}{literal} },{/literal}{/if}
                {literal}992: {slidesPerView: {/literal}{$sttheme.pro_thumnbs_per_lg}{literal} },{/literal}
                768: {literal}{slidesPerView: {/literal}{$sttheme.pro_thumnbs_per_md}{literal} },{/literal}
                480: {literal}{slidesPerView: {/literal}{$sttheme.pro_thumnbs_per_sm}{literal} }
            },
            {/literal}
            {/if}
            {literal}
            on: {
              init: function (swiper) {
                  prestashop.easyzoom.init(swiper.$wrapperEl.find('.swiper-slide-visible .easyzoom'));
                  $('.pro_popup_trigger_box a').removeClass('st_active').eq(swiper.activeIndex).addClass('st_active');

                  if($(swiper.slides).length==$(swiper.slides).filter('.swiper-slide-visible').length)
                  {
                      $(swiper.params.navigation.nextEl).hide();
                      $(swiper.params.navigation.prevEl).hide();
                  }
                  else
                  {
                      $(swiper.params.navigation.nextEl).show();
                      $(swiper.params.navigation.prevEl).show();
                  }
              },
              slideChangeTransitionStart: function (swiper) {
                  $('.pro_popup_trigger_box a').removeClass('st_active').eq(swiper.activeIndex).addClass('st_active');
              },
              slideChangeTransitionEnd: function (swiper) {
                prestashop.easyzoom.init(swiper.$wrapperEl.find('.swiper-slide-visible .easyzoom'));
              },
              click: function (swiper) {
                if($('.pro_gallery_thumbs').length && typeof($('.pro_gallery_thumbs')[0].swiper)!=='undefined')
                  {
                    $('.pro_gallery_thumbs')[0].swiper.slideTo(swiper.activeIndex);
                    $($('.pro_gallery_thumbs')[0].swiper.slides).removeClass('clicked_thumb').eq(swiper.activeIndex).addClass('clicked_thumb');
                  }
              }
            },
            {/literal}
            roundLengths: true,
            {if $sttheme.lazyload_main_gallery}
            lazy: false,
            {else}
            {literal}
            lazy:{
              loadPrevNext: {/literal}{if $sttheme.lazyload_main_gallery}false{else}true{/if}{literal},
              loadPrevNextAmount: 1
            },
            {/literal}
            {/if}
            initialSlide: {$pro_gallery_initial}
        {literal}
        });
        {/literal} 
    //]]>
    </script>
  {/block}
  {/if}
  </div>
</div>
{if $sttheme.product_thumbnails!=4 && $sttheme.product_thumbnails!=5 && $sttheme.product_thumbnails!=8}
<div class="pro_gallery_thumbs_container {if $sttheme.product_thumbnails==1 || $sttheme.product_thumbnails==2 || $sttheme.product_thumbnails==7} col-{$gallery_thumbnails_width_v_mobile|replace:'.':'-'} col-md-{$sttheme.gallery_thumbnails_width_v|replace:'.':'-'} pro_gallery_thumbs_vertical {elseif $sttheme.product_thumbnails==3} pro_gallery_thumbs_grid {else} pro_gallery_thumbs_horizontal {/if}{if $sttheme.product_thumbnails==1 || $sttheme.product_thumbnails==7} pull-{$gallery_top_width_v_mobile|replace:'.':'-'}  pull-md-{$gallery_top_width_v|replace:'.':'-'} {/if}">
  {block name='product_images'}
    <div class="swiper-container pro_gallery_thumbs swiper-button-lr {if $sttheme.thumbs_direction_nav==3} swiper-navigation-rectangle {elseif $sttheme.thumbs_direction_nav==2} swiper-navigation-arrow {else} swiper-navigation-circle {/if} {if $sttheme.product_thumbnails==0} swiper-small-button {/if} {if $sttheme.product_gallerys} hightlight_curr_thumbs {/if}" {if $language.is_rtl} dir="rtl" {/if}>
        <div class="swiper-wrapper">
            {assign var="curr_combination_thumbs" value=[]}
            {foreach $product.images as $index => $image}
              {$curr_combination_thumbs[]=$image.id_image}
              {include file='catalog/_partials/product-thumbnails-item.tpl' curr_combination_thumb=true disable_lazyloading=$pro_gallery_initial}
            {/foreach}
            {if $sttheme.product_gallerys==2}
              {foreach $sttheme.pro_images as $index => $image}
                {if !in_array($image.id_image, $curr_combination_thumbs)}
                  {include file='catalog/_partials/product-thumbnails-item.tpl' disable_lazyloading=$pro_gallery_initial}
                {/if}
              {/foreach}
            {/if}
        </div>
        {if $sttheme.product_thumbnails==1 || $sttheme.product_thumbnails==2 || $sttheme.product_thumbnails==7}
        <div class="swiper-button swiper-button-top"><i class="fto-up-open slider_arrow_top"></i><i class="fto-down-open slider_arrow_bottom"></i></div>
        <div class="swiper-button swiper-button-bottom"><i class="fto-up-open slider_arrow_top"></i><i class="fto-down-open slider_arrow_bottom"></i></div>
        {elseif $sttheme.product_thumbnails==0}
        <div class="swiper-button swiper-button-next"><i class="fto-left-open-3 slider_arrow_left"></i><i class="fto-right-open-3 slider_arrow_right"></i></div>
        <div class="swiper-button swiper-button-prev"><i class="fto-left-open-3 slider_arrow_left"></i><i class="fto-right-open-3 slider_arrow_right"></i></div>
        {/if}
    </div>
    <script type="text/javascript">
    //<![CDATA[
    sttheme.product_thumbnails = {$sttheme.product_thumbnails};
    {if $sttheme.product_thumbnails!=3}
        {literal}
        if(typeof(swiper_options) ==='undefined')
        var swiper_options = [];
        {/literal}
        {literal}
        swiper_options.push({
            {/literal}
            id_st: '.pro_gallery_thumbs',
            speed: 0,
            spaceBetween: 10,
            slidesPerView: 'auto',
            {if $sttheme.product_thumbnails==1 || $sttheme.product_thumbnails==2 || $sttheme.product_thumbnails==7}
            direction: 'vertical',
            {literal}
            navigation:{
              nextEl: '.pro_gallery_thumbs .swiper-button-bottom',
              prevEl: '.pro_gallery_thumbs .swiper-button-top'
            },
            {/literal}
            {else}
            {literal}
            navigation:{
              nextEl: '.pro_gallery_thumbs .swiper-button-next',
              prevEl: '.pro_gallery_thumbs .swiper-button-prev'
            },
            {/literal}
            {/if}            
            loop: false,
            slideToClickedSlide: false,
            watchSlidesProgress: true,
            watchSlidesVisibility: true,
            {literal}
            on: {
              init: function (swiper) {
                if($(swiper.slides).length==$(swiper.slides).filter('.swiper-slide-visible').length)
                {
                    $(swiper.params.navigation.nextEl).hide();
                    $(swiper.params.navigation.prevEl).hide();
                }
                else
                {
                    $(swiper.params.navigation.nextEl).show();
                    $(swiper.params.navigation.prevEl).show();
                }
                prestashop.emit('thumbsContainerInit');
              },
              click: function (swiper) {
                if(swiper.clickedIndex>=0){
                  if($('.pro_gallery_top').length && typeof($('.pro_gallery_top')[0].swiper)!=='undefined'){
                    $('.pro_gallery_top')[0].swiper.slideTo(swiper.clickedIndex);
                  }else if($('.st_image_scrolling_wrap .st_image_scrolling_item').length){
                    var _to_top = $('.st_image_scrolling_wrap .st_image_scrolling_item').eq(swiper.clickedIndex).offset().top;
                    if(sttheme.is_mobile_device && sttheme.use_mobile_header==1)
                      _to_top -= $('#mobile_bar').outerHeight();
                    else if(sttheme.sticky_option)
                      _to_top -= $((sttheme.sticky_option==2 || sttheme.sticky_option==4) ? '#st_header' : '#top_extra .st_mega_menu_container').outerHeight();
                    $('body,html').animate({
                      scrollTop: _to_top
                    }, 'fast');
                  }
                  $(swiper.slides).removeClass('clicked_thumb').eq(swiper.clickedIndex).addClass('clicked_thumb');
                }
              }
            },
            {/literal}
            roundLengths: true,
            {if $sttheme.product_thumbnails==7 || $sttheme.lazyload_main_gallery || $pro_gallery_initial}
            lazy: false,
            {else}
            {literal}
            lazy:{
              loadPrevNext: {/literal}{if $sttheme.product_thumbnails==7 || $sttheme.lazyload_main_gallery}false{else}true{/if}{literal},
              loadPrevNextAmount: 1
            },
            {/literal}
            {/if}
            initialSlide: {if $sttheme.product_thumbnails==7}0{else}{$pro_gallery_initial}{/if}
        {literal}
        });
        {/literal} 
    {/if}
    //]]>
    </script>
  {/block}
</div>
{/if}
</div>
{if $sttheme.product_gallerys==1 && count($curr_combination_thumbs)<count($sttheme.pro_images)}
  <a href="javascript:;" class="btn btn-link pro_gallery_show_all">{l s='Show all images' d='Shop.Theme.Transformer'}</a>  
  <script type="text/javascript">
  //<![CDATA[
  if(typeof(sttheme)!='undefined')
    sttheme.pro_images = {$sttheme.pro_images|json_encode nofilter};
  //]]>
  </script>
{/if}
</div>
{*displayAfterProductThumbs can not be here, repeat after changing attributes, I moved it to product.tpl*}
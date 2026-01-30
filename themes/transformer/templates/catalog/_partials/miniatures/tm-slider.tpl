{if !isset($tm_lazyload)}{assign var='tm_lazyload' value=1}{/if}
{if $tm_thumbs && isset($product.stthemeeditor.images[0]) && $product.stthemeeditor.images[0].id_image!=$tm_cover}{$tm_lazyload=0}{/if}
	<div class="swiper-container tm_gallery tm_gallery_{if $tm_thumbs}thumbs mar_b10 {else}top{/if} swiper-button-lr swiper-navigation-circle swiper-small-button {if isset($is_grid_view) && $is_grid_view} lazy_swiper {/if}" data-lazyload="{$tm_lazyload}" {if $sttheme.is_rtl} dir="rtl" {/if} data-thumb="{if $tm_thumbs}1{/if}" data-thumb1="{if isset($product.stthemeeditor.images[0])}1{/if}" data-thumb1="{if !$product.stthemeeditor.images[0].cover}1{/if}">
        <div class="swiper-wrapper">
            {foreach $product.stthemeeditor.images as $index => $image}
              <div class="swiper-slide {if $image.id_image==$tm_cover} tm_cover {/if}">
              	{if $tm_lazyload}<i class="swiper-lazy-preloader fto-spin5 animate-spin"></i>{/if}
                    {if !$tm_thumbs}<a href="{$product.url}" class="tm_gallery_item_box" title="{$image.legend}">{else}<div class="pro_gallery_thumb_box general_border">{/if}
                        <picture>
                        {if isset($stwebp) && (($tm_thumbs && $image.bySize.small_default.url) || (!$tm_thumbs && $image.bySize.{$pro_image_type}.url))}
                        <!--[if IE 9]><video style="display: none;"><![endif]-->
                          <source
                            {if $tm_lazyload}data-{/if}srcset="{if $tm_thumbs}{$image.bySize.small_default.url|regex_replace:'/\.jpg$/':'.webp'}{else}{$image.bySize.{$pro_image_type}.url|regex_replace:'/\.jpg$/':'.webp'}{/if}
                            {if $sttheme.retina && (($tm_thumbs && isset($image.bySize.small_default_2x.url)) || (!$tm_thumbs && isset($image.bySize.{$pro_image_type_retina}.url)))},{if $tm_thumbs}{$image.bySize.small_default_2x.url|regex_replace:'/\.jpg$/':'.webp'}{else}{$image.bySize.{$pro_image_type_retina}.url|regex_replace:'/\.jpg$/':'.webp'}{/if} 2x{/if}"
                            title="{$image.legend}"
                            type="image/webp"
                            >
                        <!--[if IE 9]></video><![endif]-->
                        {/if}
                        <img
                          class="tm_gallery_item {if $tm_lazyload} swiper-lazy {/if}"
                          {if $tm_lazyload}data-src{else}src{/if}="{if $tm_thumbs}{$image.bySize.small_default.url}{else}{$image.bySize.{$pro_image_type}.url}{/if}"
                          {if $sttheme.retina && (($tm_thumbs && isset($image.bySize.small_default_2x.url)) || (!$tm_thumbs && isset($image.bySize.{$pro_image_type_retina}.url)))}
                            {if $tm_lazyload}data-srcset{else}srcset{/if}="{if $tm_thumbs}{$image.bySize.small_default_2x.url}{else}{$image.bySize.{$pro_image_type_retina}.url}{/if} 2x"
                          {/if}
                          alt="{$image.legend}"
                          title="{$image.legend}"
                          width="{if $tm_thumbs}{$image.bySize.small_default.width}{else}{$image.bySize.{$pro_image_type}.width}{/if}" 
                          height="{if $tm_thumbs}{$image.bySize.small_default.height}{else}{$image.bySize.{$pro_image_type}.height}{/if}"
                        />
                        </picture>
                    {if !$tm_thumbs}</a>{else}</div>{/if}
              </div>
            {/foreach}
        </div>
        <div class="swiper-button swiper-button-next"><i class="fto-left-open slider_arrow_left"></i><i class="fto-right-open slider_arrow_right"></i></div>
        <div class="swiper-button swiper-button-prev"><i class="fto-left-open slider_arrow_left"></i><i class="fto-right-open slider_arrow_right"></i></div>
    </div>
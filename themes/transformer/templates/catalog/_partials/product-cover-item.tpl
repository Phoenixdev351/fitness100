{*The same code in the product.js, using js to add images fast, but not standard*}
              <div class="swiper-slide {if $sttheme.enable_zoom==1 || ($sttheme.enable_zoom==2 && !$sttheme.is_mobile_device)} swiper-no-swiping {/if}">
                <div class="easyzoom--overlay {if $sttheme.enable_zoom} easyzoom {/if} {if $sttheme.enable_zoom==2} disable_easyzoom_on_mobile {/if}">
                    <a href="{if $sttheme.enable_zoom==1 || ($sttheme.enable_zoom==2 && !$sttheme.is_mobile_device) || ($sttheme.enable_thickbox && !$do_from_quickview)}{$image.bySize.superlarge_default.url}{else}javascript:;{/if}" class="{if ($sttheme.enable_thickbox && !$do_from_quickview) && (!$sttheme.enable_zoom || ($sttheme.enable_zoom==2 && $sttheme.is_mobile_device))}{if $sttheme.enable_thickbox==1 || ($sttheme.enable_thickbox==3 && !$sttheme.is_mobile_device)} st_popup_image st_pro_popup_image{elseif $sttheme.enable_thickbox==2 || ($sttheme.enable_thickbox==3 && $sttheme.is_mobile_device)}kk_triger{/if} {/if} {if $sttheme.retina && isset($image.bySize.superlarge_default_2x.url)} replace-2x {/if}" {if ($sttheme.enable_thickbox==1 || ($sttheme.enable_thickbox==3 && !$sttheme.is_mobile_device)) && (!$sttheme.enable_zoom || ($sttheme.enable_zoom==2 && $sttheme.is_mobile_device))} data-group="pro_gallery_popup" {/if} title="{if $image.legend}{$image.legend}{else}{$product.name}{/if}">
                      <picture>
                        {if isset($stwebp) && isset($stwebp.{$sttheme.gallery_image_type}) && $stwebp.{$sttheme.gallery_image_type}}
                        <!--[if IE 9]><video style="display: none;"><![endif]-->
                          <source
                            {if !$sttheme.lazyload_main_gallery && (!isset($disable_lazyloading) || !$disable_lazyloading)}data-{/if}srcset="{$image.bySize.{$sttheme.gallery_image_type}.url|regex_replace:'/\.jpg$/':'.webp'}
                            {if $sttheme.retina && isset($image.bySize.{$sttheme.gallery_image_type|cat:'_2x'}.url)},{$image.bySize.{$sttheme.gallery_image_type|cat:'_2x'}.url|regex_replace:'/\.jpg$/':'.webp'} 2x{/if}"
                            title="{if $image.legend}{$image.legend}{else}{$product.name}{/if}"
                            type="image/webp"
                            data-id_image="{$image.id_image}"
                            >
                        <!--[if IE 9]></video><![endif]-->
                        {/if}
                        <img
                          class="pro_gallery_item {if !$sttheme.lazyload_main_gallery && (!isset($disable_lazyloading) || !$disable_lazyloading)}{if $sttheme.product_thumbnails==7 || $sttheme.product_thumbnails==8} cate_pro_lazy {else} swiper-lazy {/if}{/if}"
                          {if !$sttheme.lazyload_main_gallery && (!isset($disable_lazyloading) || !$disable_lazyloading) && ($sttheme.product_thumbnails==7 || $sttheme.product_thumbnails==8)} src="{$sttheme.img_prod_url}{$sttheme.lang_iso_code}-default-{$sttheme.gallery_image_type}.{if isset($stwebp) && isset($stwebp.{$sttheme.gallery_image_type}) && $stwebp.{$sttheme.gallery_image_type}}webp{else}jpg{/if}" {/if}
                          {if !$sttheme.lazyload_main_gallery && (!isset($disable_lazyloading) || !$disable_lazyloading)} data-{/if}src="{$image.bySize.{$sttheme.gallery_image_type}.url}"
                          {if $sttheme.retina && isset($image.bySize.{$sttheme.gallery_image_type|cat:'_2x'}.url)} {if !$sttheme.lazyload_main_gallery && (!isset($disable_lazyloading) || !$disable_lazyloading)}data-{/if}srcset="{$image.bySize.{$sttheme.gallery_image_type|cat:'_2x'}.url} 2x" {/if}
                          alt="{if $image.legend}{$image.legend}{else}{$product.name}{/if}"
                          width="{$image.bySize.{$sttheme.gallery_image_type}.width}"
                          height="{$image.bySize.{$sttheme.gallery_image_type}.height}"
                          data-id_image="{$image.id_image}"
                          {if $sttheme.google_rich_snippets} itemprop="image" content="{$image.bySize.{$sttheme.gallery_image_type}.url}" {/if}
                        />
                      </picture>
                    </a>
                </div>
              </div>
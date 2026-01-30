{*The same code in the product.js, using js to add images fast, but not standard*}
{assign var="cover_id" value=$product.cover.id_image}
{if isset($product.default_image)}{$cover_id=$product.default_image.id_image}{/if}
              <div class="swiper-slide {if $image.id_image == $cover_id} clicked_thumb {/if}">
                <div class="pro_gallery_thumb_box general_border {if isset($curr_combination_thumb) && $curr_combination_thumb} curr_combination_thumb {/if}">
                  <picture>
                    {if isset($stwebp) && isset($stwebp.{$sttheme.thumb_image_type}) && $stwebp.{$sttheme.thumb_image_type}}
                    <!--[if IE 9]><video style="display: none;"><![endif]-->
                      <source
                        {if ($sttheme.product_thumbnails!=3 && $sttheme.product_thumbnails!=7) && (!isset($disable_lazyloading) || !$disable_lazyloading) && !$sttheme.lazyload_main_gallery}data-{/if}srcset="{$image.bySize.{$sttheme.thumb_image_type}.url|regex_replace:'/\.jpg$/':'.webp'}
                        {if $sttheme.retina && isset($image.bySize.{$sttheme.thumb_image_type|cat:'_2x'}.url)},{$image.bySize.{$sttheme.thumb_image_type|cat:'_2x'}.url|regex_replace:'/\.jpg$/':'.webp'} 2x{/if}"
                        type="image/webp"
                        >
                    <!--[if IE 9]></video><![endif]-->
                    {/if}
                  <img
                      class="pro_gallery_thumb {if ($sttheme.product_thumbnails!=3 && $sttheme.product_thumbnails!=7) && (!isset($disable_lazyloading) || !$disable_lazyloading) && !$sttheme.lazyload_main_gallery}swiper-lazy{/if}"
                      {if ($sttheme.product_thumbnails!=3 && $sttheme.product_thumbnails!=7) && (!isset($disable_lazyloading) || !$disable_lazyloading) && !$sttheme.lazyload_main_gallery}data-{/if}src="{$image.bySize.{$sttheme.thumb_image_type}.url}"
                      {if $sttheme.retina && isset($image.bySize.{$sttheme.thumb_image_type|cat:'_2x'}.url)} {if ($sttheme.product_thumbnails!=3 && $sttheme.product_thumbnails!=7) && (!isset($disable_lazyloading) || !$disable_lazyloading) && !$sttheme.lazyload_main_gallery}data-{/if}srcset="{$image.bySize.{$sttheme.thumb_image_type|cat:'_2x'}.url} 2x" {/if}
                      alt="{if $image.legend}{$image.legend}{else}{$product.name}{/if}"
                      width="{$image.bySize.{$sttheme.thumb_image_type}.width}"
                      height="{$image.bySize.{$sttheme.thumb_image_type}.height}"
                      {*do not need thumbnail{if $sttheme.google_rich_snippets} itemprop="image" {/if}*}
                    /> 
                  </picture>
                </div>
              </div>
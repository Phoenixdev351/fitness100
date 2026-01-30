{*The same code in the product.js, using js to add images fast, but not standard*}
              <div class="swiper-slide">
                <div class="swiper-zoom-container">
                      <picture>
                        {if isset($stwebp) && isset($stwebp.superlarge_default) && $stwebp.superlarge_default}
                        <!--[if IE 9]><video style="display: none;"><![endif]-->
                          <source
                            {if !$sttheme.lazyload_main_gallery}data-{/if}srcset="{$image.bySize.superlarge_default.url|regex_replace:'/\.jpg$/':'.webp'}
                            {if $sttheme.retina && isset($image.bySize.{$sttheme.gallery_image_type|cat:'_2x'}.url)},{$image.bySize.{$sttheme.gallery_image_type|cat:'_2x'}.url|regex_replace:'/\.jpg$/':'.webp'} 2x{/if}"
                            title="{if $image.legend}{$image.legend}{else}{$product.name}{/if}"
                            type="image/webp"
                            data-id_image="{$image.id_image}"
                            >
                        <!--[if IE 9]></video><![endif]-->
                        {/if}
                        <img
                          class="pro_gallery_kk_item {if !$sttheme.lazyload_main_gallery} swiper-lazy {/if}"
                          {if !$sttheme.lazyload_main_gallery} data-{/if}src="{$image.bySize.superlarge_default.url}"
                          {if $sttheme.retina && isset($image.bySize.{$sttheme.gallery_image_type|cat:'_2x'}.url)} {if !$sttheme.lazyload_main_gallery}data-{/if}srcset="{$image.bySize.{$sttheme.gallery_image_type|cat:'_2x'}.url} 2x" {/if}
                          alt="{if $image.legend}{$image.legend}{else}{$product.name}{/if}"
                          width="{$image.bySize.superlarge_default.width}"
                          height="{$image.bySize.superlarge_default.height}"
                          data-id_image="{$image.id_image}"
                        />
                      </picture>
                </div>
              </div>
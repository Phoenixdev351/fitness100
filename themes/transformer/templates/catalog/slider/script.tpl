    <script>
    //<![CDATA[
        {literal}
        if(typeof(swiper_options) ==='undefined')
        var swiper_options = [];
        swiper_options.push({
            {/literal}
            {if $slider_slideshow}
            {literal}
            autoplay:{
                {/literal}
                delay: {$slider_s_speed|default:5000},
                {if $slider_slideshow==2}stopOnLastSlide:true,{/if}
                disableOnInteraction: {if $slider_pause_on_hover}true{else}false{/if}
                {literal}
            },
            {/literal}
            {/if}
            speed: {if $slider_a_speed}{$slider_a_speed}{else}400{/if},
            loop: {if $rewind_nav && (!isset($one_item_only) || (isset($one_item_only) && $one_item_only>1))}true{else}false{/if},
            {if $lazy_load && ((!isset($eb_pro_tm_slider) && !$sttheme.pro_tm_slider) || (isset($eb_pro_tm_slider) && ($eb_pro_tm_slider==0 || ($eb_pro_tm_slider==3 && !$sttheme.pro_tm_slider))) || ($sttheme.pro_tm_slider && isset($column_slider) && $column_slider && !$display_pro_col) || (isset($is_product_slider) && !$is_product_slider))}
                {literal}
                lazy:{
                {/literal}
                  loadPrevNext: {if $lazy_load==2}false{else}true{/if},
                  loadPrevNextAmount: {if (isset($column_slider) && $column_slider) || $slider_move!=1}1{else}1{/if},
                  loadOnTransitionStart: true
                {literal}
                },
                {/literal}
            {/if}
            {if $direction_nav}
                {literal}
                navigation:{
                {/literal}
                  nextEl: '{$block_name} .swiper-button-outer.swiper-button-next',
                  prevEl: '{$block_name} .swiper-button-outer.swiper-button-prev'
                {literal}
                },
                {/literal}
            {/if}

            {if $control_nav}
            {literal}
            pagination: {
            {/literal}
              el: '{$block_name} .swiper-pagination',
              clickable: true,
              {if $control_nav==2}
                {literal}
                renderBullet: function (index, className) {
                    return '<span class="' + className + '">' + (index + 1) + '</span>';
                },
                {/literal}
                {/if}
              type: {if $control_nav==2}'bullets'{elseif $control_nav==3}'progress'{else}'bullets'{/if} //A bug of swiper, this should be 'custom' when nav==2
            {literal}
            },
            {/literal}
            {/if}

            {if isset($column_slider) && $column_slider}
                slidesPerView : 1,
                observer : true,
                observeParents : true,
            {elseif isset($slides_per_view_auto) && $slides_per_view_auto}
                slidesPerView: 'auto',
                {if $rewind_nav && isset($one_item_only)}
                    loopedSlides: {$one_item_only},
                {/if}
                spaceBetween: {(int)$spacing_between},
            {else}

                freeMode: {if $slider_move==2}true{else}false{/if},
                spaceBetween: {(int)$spacing_between},
                slidesPerView: {if $pro_per_xs}{$pro_per_xs}{else}1{/if},
                {if $slider_move==1}slidesPerGroup: {if $pro_per_xs}{$pro_per_xs}{else}1{/if},{/if}
                {literal}
                breakpoints: {
                    {/literal}
                    {if ((isset($homeverybottom) && $homeverybottom) || $sttheme.responsive_max==3) && $pro_per_fw}
                    {if $sttheme.responsive_max==3}1600{elseif $sttheme.responsive_max==1}1440{else}1200{/if}{literal}: {slidesPerView: {/literal}{$pro_per_fw|default:3}{if $slider_move==1}, slidesPerGroup: {$pro_per_fw|default:3}{/if}{literal} },{/literal}
                    {/if}
                    {if $sttheme.responsive_max>=2 && $pro_per_xxl}{literal}1440: {slidesPerView: {/literal}{$pro_per_xxl|default:3}{if $slider_move==1}, slidesPerGroup: {$pro_per_xxl|default:3}{/if}{literal} },{/literal}{/if}
                    {if $sttheme.responsive_max>=1 && $pro_per_xl}{literal}1200: {slidesPerView: {/literal}{$pro_per_xl|default:2}{if $slider_move==1}, slidesPerGroup: {$pro_per_xl|default:2}{/if}{literal} },{/literal}{/if}
                    992: {literal}{slidesPerView: {/literal}{$pro_per_lg|default:4}{if $slider_move==1}, slidesPerGroup: {$pro_per_lg|default:4}{/if}{literal} },{/literal}
                    768: {literal}{slidesPerView: {/literal}{$pro_per_md|default:3}{if $slider_move==1}, slidesPerGroup: {$pro_per_md|default:3}{/if}{literal} },{/literal}
                    480: {literal}{slidesPerView: {/literal}{$pro_per_sm|default:2}{if $slider_move==1}, slidesPerGroup: {$pro_per_sm|default:2}{/if}{literal} }
                },
                {/literal}

            {/if}
            {if isset($autoHeight) && $autoHeight}autoHeight:true,{/if}
            watchSlidesProgress: true,
            watchSlidesVisibility: true,
            {literal}
            on: {
              init: function (swiper) {
                $(swiper.container).removeClass('swiper_loading').addClass('swiper_loaded');
                {/literal}
                {if $direction_nav}
                {literal}
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
                {/literal}
                {/if}
                {literal}
              },
              lazyImageReady: function (swiper, slide, image) {
                if($(image).hasClass('front-image'))
                        $(image).closest('.is_lazy').removeClass('is_lazy');//also in pro-lazy.js
              }
            },
            {/literal}
            //temp fix, loop breaks when roundlenghts and autoplay
            {if !$slider_slideshow}roundLengths: true,{/if}
            {if isset($autoplay_stop) && $autoplay_stop}autoplayStop:true,{/if}
            inviewwatcher:true,
            id_st: '{$block_name} .products_sldier_swiper'

        {literal}
        });
        {/literal} 

    //]]>
    </script>
<script type="text/javascript">
//<![CDATA[
        {literal}
        if(typeof(swiper_options) ==='undefined')
        var swiper_options = [];
        swiper_options.push({
            {/literal}
            {if $slides.auto_advance}
            {literal}
            autoplay:{
                {/literal}
                delay: {$slides.time|default:5000},
                {if $slides.auto_advance==2}stopOnLastSlide:true,{/if}
                disableOnInteraction: {if $slides.pause}true{else}false{/if}
                {literal}
            },
            {/literal}
            {/if}
            {if $slides.direction}direction: 'vertical',{/if}
            speed: {$slides.trans_period|default:400},
            loop: {if $slides.rewind_nav && $one_item_only>1}true{else}false{/if},
            {if $slides.lazy_load}
            {literal}
            lazy:{
              loadPrevNext: true,
              loadPrevNextAmount: 1,
              loadOnTransitionStart: true
            },
            {/literal}
            {/if}
            grabCursor: {if $slides.mouse_drag}true{else}false{/if},
            autoHeight : {if $slides.auto_height}true{else}false{/if},
            {if $slides.prev_next}
                {literal}
                navigation:{
                {/literal}
                  nextEl: '#st_swiper_{$slides.id_st_swiper_group}{if isset($st_time)}{$st_time}{/if} .swiper-button-next',
                  prevEl: '#st_swiper_{$slides.id_st_swiper_group}{if isset($st_time)}{$st_time}{/if} .swiper-button-prev'
                {literal}
                },
                {/literal}
            {/if}
            {if $slides.pag_nav}
            {literal}
            pagination: {
            {/literal}
              el: '.swiper-pagination',
              clickable: true,
              {if $slides.pag_nav==2}
                {literal}
                renderBullet: function (index, className) {
                    return '<span class="' + className + '">' + (index + 1) + '</span>';
                },
                {/literal}
                {elseif $slides.pag_nav==4}
                {literal}
                renderBullet: function (index, className) {
                    return '<span class="' + className + '"><span></span></span>';
                },
                {/literal}
                {/if}
              type: {if $slides.pag_nav==2 || $slides.pag_nav==4}'bullets'{elseif $slides.pag_nav==3}'progress'{else}'bullets'{/if} //A bug of swiper, this should be 'custom' when nav==2
            {literal}
            },
            {/literal}
            {/if}

            {if isset($column_slider) && $column_slider}
                observer : true,
                observeParents : true,
            {/if}

            {if $slides.templates==3}

            centeredSlides: {if $slides.centered_slides}true{else}false{/if}, //to make sure the center slide showing text out
            freeMode: {if $slides.move==2}true{else}false{/if},
            spaceBetween: {$slides.spacing_between|default:10}, //new

            {if $slides.slides_per_view}
                slidesPerView: 'auto',
                {if $slides.rewind_nav}loopedSlides: {count($slides.slide)},{/if}
            {else}
                slidesPerView: {$slides.items_xs},
                {if $slides.move==1}slidesPerGroup: {$slides.items_xs},{/if}
                {if $sttheme.responsive && !$sttheme.version_switching}
                {literal}
                breakpoints: {
                    {/literal}
                    {if $slides.is_full_width}
                    1900: {literal}{slidesPerView: {/literal}{$slides.items_huge}{if $slides.move==1}, slidesPerGroup: {$slides.items_huge}{/if}{literal} },{/literal}
                    {/if}
                    {if $sttheme.responsive_max==2 || $slides.is_full_width}{literal}1600: {slidesPerView: {/literal}{$slides.items_xxlg}{if $slides.move==1}, slidesPerGroup: {$slides.items_xxlg}{/if}{literal} },{/literal}{/if}
                    {if $sttheme.responsive_max>=1 || $slides.is_full_width}{literal}1440: {slidesPerView: {/literal}{$slides.items_xlg}{if $slides.move==1}, slidesPerGroup: {$slides.items_xlg}{/if}{literal} },{/literal}{/if}
                    {if $sttheme.responsive_max>=1 || $slides.is_full_width}{literal}1200: {slidesPerView: {/literal}{$slides.items_lg}{if $slides.move==1}, slidesPerGroup: {$slides.items_lg}{/if}{literal} },{/literal}{/if}
                    992: {literal}{slidesPerView: {/literal}{$slides.items_md}{if $slides.move==1}, slidesPerGroup: {$slides.items_md}{/if}{literal} },{/literal}
                    768: {literal}{slidesPerView: {/literal}{$slides.items_sm}{if $slides.move==1}, slidesPerGroup: {$slides.items_sm}{/if}{literal} },{/literal}
                    480: {literal}{slidesPerView: {/literal}{$slides.items_xs}{if $slides.move==1}, slidesPerGroup: {$slides.items_xs}{/if}{literal} }
                },

                {/literal}
                {/if}
            {/if}//end slides_per_view

            {else}// else template 0,1,2

            {if $slides.progress_bar}
            custom_progress_bar: true,
            bar_time: {$slides.time|default:5000},
            {/if}
            slidesPerView : 1,
            {if $slides.transition_style==1}
              {literal}
              effect: 'fade',
              fadeEffect: {
                crossFade: true
              },
              {/literal}
            {elseif $slides.transition_style==2}
              {literal}
              effect: 'cube',
              cubeEffect: {
                shadow: false, slideShadows: false
              },
              {/literal}
            {elseif $slides.transition_style==3}
              {literal}
              effect: 'coverflow',
              coverflowEffect: {
                rotate: 50, stretch: 0, depth: 100, modifier: 1, slideShadows : false
              },
              {/literal}
            {elseif $slides.transition_style==4}
              {literal}
              effect: 'flip',
              flipEffect: {
                slideShadows : false, limitRotation: true
              },
              {/literal}
            {/if}//end transition
            
            {/if}// end tempalte
            watchSlidesProgress: true,
            watchSlidesVisibility: true,
            {literal}
            on: {
            {/literal}
            {if $slides.templates!=3}
            {literal}
            slideChangeTransitionStart: function(swiper){
                if($(swiper.slides).filter('.swiper-slide-visible').length>0)
                {
                    $.each(swiper.slides, function(){
                        if(!$(this).hasClass('swiper-slide-visible'))
                        {
                            var style_content = $(this).find('.style_content').removeClass('curr_swiper');
                            style_content.removeClass('animated '+style_content.data('animate'));
                            style_content.find('.layered_content').removeClass('animated '+style_content.data('animate'));
                        }
                    });
                }
            },
            slideChangeTransitionEnd: function(swiper){
                var visible_slides = $(swiper.slides).filter('.swiper-slide-visible');
                $.each(visible_slides, function(){
                    var style_content = $(this).find('.style_content');
                    if(!style_content.hasClass('curr_swiper'))
                    {
                        style_content.addClass('curr_swiper');
                        if(style_content.data('animate')){
                            if(style_content.find('.layered_content').length)
                                style_content.find('.layered_content').addClass('animated '+style_content.data('animate'));
                            else
                                style_content.addClass('animated '+style_content.data('animate'));
                        }
                    }
                });
            },
            {/literal}
            {/if}
            {literal}
              init: function (swiper) {
                {/literal}
                {if $slides.prev_next}
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
                {if $slides.templates!=3}
                {literal}
                //onSlideChanges run first
                if($(swiper.slides).length==$(swiper.slides).filter('.swiper-slide-visible').length)//do this only when the number of items is the same as visible items.
                {
                    var style_content = $(swiper.slides).find('.style_content').addClass('curr_swiper');

                    $.each(style_content, function(){
                        if($(this).data('animate')){
                            if($(this).find('.layered_content').length)
                                $(this).find('.layered_content').addClass('animated '+$(this).data('animate'));
                            else
                                $(this).addClass('animated '+$(this).data('animate'));
                        }
                    });
                }
                else if($(swiper.slides).filter('.swiper-slide-visible').length){
                    var visible_slides = $(swiper.slides).filter('.swiper-slide-visible');
                    $.each(visible_slides, function(){
                        var style_content = $(this).find('.style_content');
                        if(!style_content.hasClass('curr_swiper'))
                        {
                            style_content.addClass('curr_swiper');
                            if(style_content.data('animate')){
                                if(style_content.find('.layered_content').length)
                                    style_content.find('.layered_content').addClass('animated '+style_content.data('animate'));
                                else
                                    style_content.addClass('animated '+style_content.data('animate'));
                            }
                        }
                    });
                }
                {/literal}
                {/if}
                {literal}
              }
            },
            {/literal} 
            st_height: {(int)$slides.height},
            st_full_screen: {(int)$slides.full_screen},
            inviewwatcher:true,
            id_st: '#st_swiper_{$slides.id_st_swiper_group}{if isset($st_time)}{$st_time}{/if}'

        {literal}
        });
        {/literal} 
//]]>
</script>
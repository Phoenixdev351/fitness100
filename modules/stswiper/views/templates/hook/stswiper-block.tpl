<div class="swiper-slide" {if $slides.templates==3 && $slides.slides_per_view && isset($banner_data['width']) && $banner_data['width']}style="width:{$banner_data['width']}px;"{/if}>
{if $banner_data['url'] && !$banner_data['description_has_links']}
    <a id="st_swiper_block_{$banner_data['id_st_swiper']}" href="{$banner_data['url']|escape:'html'}" class="st_swiper_block_{$banner_data['id_st_swiper']} st_swiper_block" target="{if $banner_data['new_window']}_blank{else}_self{/if}" title="{$banner_data['title']|escape:'htmlall':'UTF-8'}" {if (!isset($isbanner) || !$isbanner) && ($slides.height || $slides.full_screen)} style="background-image:url('{$banner_data['image_multi_lang']}');" {/if}>
{else}
    <div id="st_swiper_block_{$banner_data['id_st_swiper']}" class="st_swiper_block_{$banner_data['id_st_swiper']} st_swiper_block" {if (!isset($isbanner) || !$isbanner) && ($slides.height || $slides.full_screen)} style="background-image:url('{$banner_data['image_multi_lang']}');" {/if}>
{/if}
{if (isset($isbanner) && $isbanner) || (!$slides.height && !$slides.full_screen)}
{assign var='is_lazy' value=((!isset($isbanner) || !$isbanner) && $slides.lazy_load)}
<picture>
<!--[if IE 9]><video style="display: none;"><![endif]-->
    {if isset($banner_data['image_multi_lang_xs']) && $banner_data['image_multi_lang_xs']}
    {if isset($stwebp_type) && $stwebp_type}<source {if $is_lazy}data-{/if}srcset="{$banner_data['image_multi_lang_xs']|regex_replace:'/\.(jpg|jpeg|png|gif)$/':'.webp'}" media="(max-width: 480px)"  type="image/webp">{/if}
    <source {if $is_lazy}data-{/if}srcset="{$banner_data['image_multi_lang_xs']}" media="(max-width: 480px)">
    {/if}
    {if isset($banner_data['image_multi_lang_sm']) && $banner_data['image_multi_lang_sm']}
    {if isset($stwebp_type) && $stwebp_type}<source {if $is_lazy}data-{/if}srcset="{$banner_data['image_multi_lang_sm']|regex_replace:'/\.(jpg|jpeg|png|gif)$/':'.webp'}" media="(max-width: 768px)" type="image/webp">{/if}
    <source {if $is_lazy}data-{/if}srcset="{$banner_data['image_multi_lang_sm']}" media="(max-width: 768px)">
    {/if}
    {if isset($stwebp_type) && $stwebp_type && strtolower(substr($banner_data['image_multi_lang'], -3))!='gif'}<source {if $is_lazy}data-{/if}srcset="{$banner_data['image_multi_lang']|regex_replace:'/\.(jpg|jpeg|png|gif)$/':'.webp'}" type="image/webp">{/if}
<!--[if IE 9]></video><![endif]-->
<img class="st_swiper_image {if $is_lazy} swiper-lazy {/if}" {if $is_lazy}data-{/if}src="{$banner_data['image_multi_lang']}" alt="{$banner_data['title']|escape:'htmlall':'UTF-8'}" {if (isset($banner_data['width']) && $banner_data['width'])}width="{$banner_data['width']}"{/if} {if (isset($banner_data['height']) && $banner_data['height'])}height="{$banner_data['height']}"{/if} />
</picture>
{/if}
{if $banner_data['description']}
    <div class="st_image_layered_description {if isset($banner_data.content_width) && $banner_data.content_width} container {/if} {if $banner_data.hide_text_on_mobile} hidden-sm-down {/if} {if $banner_data.text_align==1} text-left {elseif $banner_data.text_align==3} text-right {else} text-center {/if} {if $banner_data.text_position==1} flex_start flex_left {elseif $banner_data.text_position==2} flex_start flex_center {elseif $banner_data.text_position==3} flex_start flex_right {elseif $banner_data.text_position==4} flex_middle flex_left {elseif $banner_data.text_position==6} flex_middle flex_right {elseif $banner_data.text_position==7} flex_end flex_left {elseif $banner_data.text_position==8} flex_end flex_center {elseif $banner_data.text_position==9} flex_end flex_right {else} flex_middle flex_center {/if}">
        <div class="st_image_layered_description_inner {if $banner_data.text_width} width_{$banner_data.text_width} {/if} style_content {if $slides.templates==3} curr_swiper {/if}" {if isset($banner_data.text_animation_name)} data-animate="{$banner_data.text_animation_name}"{/if}>
        {$banner_data['description'] nofilter}
        </div>
    </div>
{/if}
{if $banner_data['url'] && !$banner_data['description_has_links']}
    </a>
{else}
    </div>
{/if}
</div>
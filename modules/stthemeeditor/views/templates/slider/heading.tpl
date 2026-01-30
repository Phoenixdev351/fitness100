{if isset($module)}
<!-- MODULE st {$module} -->
{/if}
{if $title}
<div class="title_block flex_container title_align_{if $column_slider}0{else}{(int)$title_position}{/if} title_style_{if 0 && isset($is_blog) && $is_blog}{(int)$stblog.heading_style}{else}{(int)$sttheme.heading_style}{/if} {if isset($sub_title) && $sub_title} st_has_sub_title {/if}">
    <div class="flex_child title_flex_left"></div>
    {if (isset($title_link) && $title_link) || (isset($url_entity) && $url_entity)}
    <a href="{if isset($title_link) && $title_link}{$title_link}{else}{url entity=$url_entity}{/if}" class="title_block_inner" title="{$title}">{$title}</a>
    {else}
    <div class="title_block_inner">{$title}</div>
    {/if}
    <div class="flex_child title_flex_right"></div>
    {if $pro_or_blog_slider}
        {if isset($view_more) && $view_more==2 && ((isset($title_link) && $title_link) || (isset($url_entity) && $url_entity))}<div class="flex_box"><a href="{if isset($title_link) && $title_link}{$title_link}{else}{url entity=$url_entity}{/if}" class="view_more_at_tr st_slider_view_more inline_block" title="{l s='View more' d='Shop.Theme.Transformer'}">{l s='View More' d='Shop.Theme.Transformer'}</a></div>{/if}
    {/if}
    {if $direction_nav && ((!$display_as_grid && $direction_nav==1) || $column_slider) && ((isset($products) && $products) || (isset($blogs) && $blogs))}
        <div class="swiper-button-tr {if $hide_direction_nav_on_mob} hidden-md-down {/if}"><div class="swiper-button swiper-button-outer swiper-button-prev"><i class="fto-left-open-3 slider_arrow_left"></i><i class="fto-right-open-3 slider_arrow_right"></i></div><div class="swiper-button swiper-button-outer swiper-button-next"><i class="fto-left-open-3 slider_arrow_left"></i><i class="fto-right-open-3 slider_arrow_right"></i></div></div>        
    {/if}
</div>
{if isset($sub_title) && $sub_title}<div class="slider_sub_title">{$sub_title nofilter}</div>{/if}
{/if}
{if isset($module)}
<!-- /MODULE st {$module} -->
{/if}
{*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @author    ST-themes
*  @copyright 2007-2016 ST-themes
*  @license   Use, by you or one client for one Prestashop instance.
*  
*  
*}
{if isset($stins) && $stins|@count > 0}
    <!-- MODULE st stinstagram -->
    {foreach $stins as $in}
<div id="instagram_block_container_{$in.id_st_instagram}" class="instagram_block_center_container block {if $in.hide_on_mobile == 1} hidden-md-down {elseif $in.hide_on_mobile == 2} hidden-lg-up {/if} ins_apply_bg products_container">
{if isset($homeverybottom) && $homeverybottom && !$in.pro_per_fw}<div class="wide_container"><div class="container">{/if}
<section id="instagram_block_center_{$in.id_st_instagram}" class="instagram_block_center">
    {if $in.title!=3}
        <div class="title_block flex_container title_align_{(int)$in.title} title_style_{if isset($is_blog) && $is_blog}{(int)$stblog.heading_style}{else}{(int)$sttheme.heading_style}{/if} {if isset($in.sub_title) && $in.sub_title} st_has_sub_title {/if}">
            <div class="flex_child title_flex_left"></div>
            <div class="title_block_inner">{if $in.block_title}{$in.block_title}{else}{l s='Follow us on Instagram' d='Shop.Theme.Transformer'}{/if}</div>
            <div class="flex_child title_flex_right"></div>
            {if !$in.grid && $in.direction_nav==1}
                <div class="swiper-button-tr"><div class="swiper-button swiper-button-prev"><i class="fto-left-open-3 slider_arrow_left"></i><i class="fto-right-open-3 slider_arrow_right"></i></div><div class="swiper-button swiper-button-next"><i class="fto-left-open-3 slider_arrow_left"></i><i class="fto-right-open-3 slider_arrow_right"></i></div></div>        
            {/if}
        </div>
    {/if}
    {if isset($in.sub_title) && $in.sub_title}<div class="slider_sub_title">{$in.sub_title nofilter}</div>{/if}
    <div id="instagram_block_{$in.id_st_instagram}" class="{if  $in['inst']|@count > 0}ins_connecting{else}ins_ajax_error{/if} static_bullets {if $in.grid} lazy_swiper {/if}">
        <span class="ins_ajax_error_box alert alert-warning">{l s='Can not connect to Instagram or you do not have permissions to get media from Instagram.' d='Shop.Theme.Transformer'}</span>
        {if isset($in['inst']) && $in['inst']|@count > 0}
        {if $in.grid}
        <ul class="instagram_con com_grid_view row" data-iteration="1">
            {if $in['inst']|@count > 0}
                {foreach $in['inst'] as $key=>$item}
                    {include file="module:stinstagram/views/templates/hook/list.tpl" grid_list=1}
                {/foreach}
            {/if}
        </ul>
        {else}
        <div class="instagram_con swiper-container products_sldier_swiper {if $in.direction_nav>1} swiper-button-lr {if $in.direction_nav==6 || $in.direction_nav==7} swiper-navigation-circle {elseif $in.direction_nav==4 || $in.direction_nav==5} swiper-navigation-rectangle  {elseif $in.direction_nav==8 || $in.direction_nav==9} swiper-navigation-arrow {elseif $in.direction_nav==2 || $in.direction_nav==3} swiper-navigation-full {/if} {if $in.direction_nav==2 || $in.direction_nav==4 || $in.direction_nav==6|| $in.direction_nav==8} swiper-navigation_visible {/if}{/if}" {if $sttheme.is_rtl} dir="rtl" {/if}>
            <div class="swiper-wrapper">
            {if isset($in['inst']) && $in['inst']|@count > 0}
            {foreach $in['inst'] as $key=>$item}
                <div class="ins_image_wrap swiper-slide">
                    {include file="module:stinstagram/views/templates/hook/list.tpl"}
                </div>
            {/foreach}
            {/if}
            </div>
            {if $in.direction_nav>1}
                <div class="swiper-button swiper-button-outer swiper-button-next"><i class="fto-left-open-3 slider_arrow_left"></i><i class="fto-right-open-3 slider_arrow_right"></i></div>
                <div class="swiper-button swiper-button-outer swiper-button-prev"><i class="fto-left-open-3 slider_arrow_left"></i><i class="fto-right-open-3 slider_arrow_right"></i></div>
            {/if}
        </div>
        {if $in.control_nav}
            <div class="swiper-pagination {if $in.control_nav==2} swiper-pagination-st-custom {/if}"></div>
        {/if}

        {assign var='view_auto' value=0}
        {if isset($in.slides_per_view_auto)}
        {$view_auto=$in.slides_per_view_auto}
        {/if}

        {include file="catalog/slider/script.tpl" block_name="#instagram_block_{$in.id_st_instagram}"
            slider_s_speed=$in.s_speed 
            slider_slideshow=$in.slideshow
            slider_a_speed=$in.a_speed
            slider_pause_on_hover=$in.pause_on_hover
            rewind_nav=$in.rewind_nav
            slides_per_view_auto=$view_auto 
            lazy_load=1
            direction_nav=$in.direction_nav
            control_nav=$in.control_nav
            slider_move=$in.move
            spacing_between=$in.spacing_between
            pro_per_fw=$in.pro_per_fw 
            pro_per_xxl=$in.pro_per_xxl 
            pro_per_xl=$in.pro_per_xl 
            pro_per_lg=$in.pro_per_lg 
            pro_per_md=$in.pro_per_md 
            pro_per_sm=$in.pro_per_sm 
            pro_per_xs=$in.pro_per_xs
            is_product_slider=false
            }
        {/if}
        {if $in.load_more && $in.grid}<div class="ins_extra_box"><a href="javascript:;" title="{l s='Load more' d='Shop.Theme.Transformer'}" class="ins_load_more ins_btn ins_has_more" rel="nofollow">{l s='Load more' d='Shop.Theme.Transformer'}</a></div>{/if}
        {/if}
    </div>
    {if isset($in['inst']) && $in['inst']|@count > 0}
    <script type="text/javascript">
    //<![CDATA[
    {literal}
    if(typeof(instagram_block_array) ==='undefined')
        var instagram_block_array = {'profile':[],'feed':[]};
        {/literal}
        {literal}
            instagram_block_array.feed.push({ 
            {/literal}
            id_st_ins: '{$in.id_st_instagram}',
            accessToken: '', 
            footer_op: 0,
            list_num: {if isset($in.inst)}{$in.inst|@count}{else}0{/if},
            ins_user_name: '{$in.user_name}',
            ins_user_id: '{$in.user_id}',
            ins_hash_tag: '{$in.hash_tag}',
            count: {if $in.count}{$in.count}{else}8{/if},
            grid: {if $in.grid}1{else}0{/if},
            likes: false,       
            comments: false,    
            username: {if $in.show_username}{$in.show_username}{else}0{/if},   
            timestamp: {if $in.show_timestamp}{$in.show_timestamp}{else}0{/if},   
            caption: {$in.show_caption},   
            ins_lenght_of_caption: {$in.lenght_of_caption},   
            image_size: 2,
            effects: {$in.hover_effect},
            click_action: {$in.click_action},
            show_media_type: {$in.show_media_type},
            time_format: {$in.time_format},
            ins_load_more: {if $in.load_more}1{else}0{/if},
            ins_show_avatar: 1,
            ins_self_liked: {if $in.self_liked}1{else}0{/if},
            force_square: {if $in.force_square}1{else}0{/if},
            ins_items_xxl      : {if $in.pro_per_xxl}{$in.pro_per_xxl}{else}7{/if},
            ins_items_xl      : {if $in.pro_per_xl}{$in.pro_per_xl}{else}6{/if},
            ins_items_lg       : {if $in.pro_per_lg}{$in.pro_per_lg}{else}5{/if},
            ins_items_md       : {if $in.pro_per_md}{$in.pro_per_md}{else}4{/if},
            ins_items_sm       : {if $in.pro_per_sm}{$in.pro_per_sm}{else}3{/if},
            ins_items_xs       : {if $in.pro_per_xs}{$in.pro_per_xs}{else}2{/if}
            {literal}
        });
    {/literal} 
    //]]>
    </script>
    {/if}
</section>
{if isset($homeverybottom) && $homeverybottom && !$in.pro_per_fw}</div></div>{/if}
</div>
    {/foreach}
    <!-- /MODULE st stinstagram -->
{/if}
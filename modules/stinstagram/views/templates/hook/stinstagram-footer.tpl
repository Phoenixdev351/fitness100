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
<div id="instagram_block_container_{$in.id_st_instagram}" class="instagram_block_footer {if $in.hide_mob_col == 1} hidden-md-down {elseif $in.hide_mob_col == 2} hidden-lg-up {elseif $in.hide_mob_col == 3} st_open{/if} {if $footer_slider && !$is_stacked_footer} col-lg-{if $in.wide_on_footer}{$in.wide_on_footer}{else}3{/if} {/if} block {if $column_slider && !$is_quarter} column_block {elseif $footer_slider} footer_block {/if}">
    {if $in.title!=3}
      <div class="title_block {if !$footer_slider} flex_container{/if}">
            <div class="title_block_inner">{if $in.block_title}{$in.block_title}{else}{l s='Follow us on Instagram' d='Shop.Theme.Transformer'}{/if}</div>
            {if $column_slider}<div class="flex_child"></div>{/if}
            {if !$in.grid && $column_slider && $in.direction_nav==1}
                <div class="swiper-button-tr"><div class="swiper-button swiper-button-prev"><i class="fto-left-open-3 slider_arrow_left"></i><i class="fto-right-open-3 slider_arrow_right"></i></div><div class="swiper-button swiper-button-next"><i class="fto-left-open-3 slider_arrow_left"></i><i class="fto-right-open-3 slider_arrow_right"></i></div></div>        
            {/if}
            {if $footer_slider}<div class="opener"><i class="fto-plus-2 plus_sign"></i><i class="fto-minus minus_sign"></i></div>{/if}
        </div>
    {/if}

    <div id="instagram_block_{$in.id_st_instagram}" class="{if !$in.title}keep_open{/if} ins_connecting ins_apply_bg instagram_block_footer_box {if $footer_slider}footer_{/if}block_content">
        {if $in.show_profile}
            <div class="instagram_profile {if $in.show_avatar} ins_show_avatar {/if}{if $in.show_avatar==2} ins_round_avatar {/if} m-b-1"></div>
        {/if}
        <div class="ins_ajax_error_box alert alert-warning">{l s='Can not connect to Instagram or you do not have permissions to get media from Instagram.' d='Shop.Theme.Transformer'}</div>
        {if $in.grid}
            <ul class="instagram_con instagram_list">
            {if $in['inst']|@count > 0}
                {foreach $in['inst'] as $key=>$item}
                {include file="module:stinstagram/views/templates/hook/list-footer.tpl"}
                {/foreach}
            {/if}
            </ul>

        {else}
            <div class="instagram_con products_sldier_swiper swiper-container {if $in.direction_nav>1} swiper-button-lr {if $in.direction_nav==6 || $in.direction_nav==7} swiper-navigation-circle {elseif $in.direction_nav==4 || $in.direction_nav==5} swiper-navigation-rectangle  {elseif $in.direction_nav==8 || $in.direction_nav==9} swiper-navigation-arrow {elseif $in.direction_nav==2 || $in.direction_nav==3} swiper-navigation-full {/if} {if $in.direction_nav==2 || $in.direction_nav==4 || $in.direction_nav==6|| $in.direction_nav==8} swiper-navigation_visible {/if}{/if}" {if $sttheme.is_rtl} dir="rtl" {/if}>
                <div class="swiper-wrapper">
                {if $in['inst']|@count > 0}
           {foreach $in['inst'] as $key=>$item}
                <div class="ins_image_wrap swiper-slide">
                    <div class="ins_image_wrap_inner">

                        <div class="ins_image_box ins_slider_outer">
                            <a href="{if $in.click_action}{$item.media_url}{else}{$item.permalink}{/if}" 
                            {if $in.click_action}data-fancybox-group="ins_fancybox_view_" {/if} 
                            class=" {if $in.click_action} ins_fancybox {/if} {if $in.hover_effect==1} scaling {elseif $in.hover_effect==2} scaling_down {/if} ins_image_link" 
                               {if $in.click_action}
                                    data-ins_link="{$item.permalink}" 
                                    data-ins_caption="{$item.caption}" 
                                    data-ins_full_name="{$in.user_name}"
                                    data-ins_profile_picture=""
                                    data-ins_created_time="{if $in.time_format}{$item.inst_time}{else}{$item.ago_time}{/if}"
                                    data-ins_video="{if $item.media_type=='VIDEO'}{$item.media_url}{/if}"
                                    data-ins_video_width="720" 
                                    data-ins_video_height="720" 
                                    rel="nofollow"
                               {/if}
                                >
                                {if $in.click_action}
                                    <span class="ins_view_larger">
                                        <i class="fto-resize-full"></i>
                                    </span>
                                {/if}
                                {if $item.media_type=='VIDEO' && $in.show_media_type}
                                    <span class="ins_imagetype ins_videoicon">
                                        <i class="fto-play-circled2"></i>
                                    </span>
                                {elseif $item.media_type=='IMAGE' && $in.show_media_type==2}
                                    <span class="ins_imagetype ins_videoicon">
                                        <i class="fto-picture-2"></i>
                                    </span>
                                {elseif $item.media_type=='CAROUSEL_ALBUM' && $in.show_media_type==2}
                                    <span class="ins_imagetype">
                                        <i class="fto-popup"></i>
                                    </span>
                                {/if}
                                
                                    <div class="ins_image_info st_image_layered_description flex_middle flex_center">
                                        <div class="st_image_layered_description_inner clearfix">
                                        {if $in.show_username}
                                            <div class="ins_image_info_item ins_image_info_username {if $item.show_username==2}show_default{/if}">
                                                {$item.username}
                                            </div>
                                        {/if}
                                        {if $in.show_timestamp}
                                            <div class="ins_image_info_item ins_image_info_timestamp {if $in.show_timestamp==2}show_default{/if}">
                                            {if $in.time_format}
                                                {$item.inst_time}
                                            {else}
                                                {$item.ago_time}
                                            {/if}
                                            </div>
                                        {/if}
                                        {if $in.show_caption && $item.caption}
                                            <div class="ins_image_info_item ins_image_info_desc {if $in.show_caption==2 || $in.show_caption==5}show_default{/if}{if $in.show_caption==4 || $in.show_caption==5} hidden-xs
                                            {/if}">
                                                {if $in.lenght_of_caption && $item.caption!='' && strlen($item.caption)>200}
                                                    {$item.caption|substr:'0':'200' nofilter}
                                                {else}
                                                    {$item.caption nofilter}
                                                {/if}
                                            </div>
                                        {/if}
                                        </div>
                                    </div>
                                    <div class="ins_image_layer {if $in.force_square}ins_force_square{/if}"
                                        {if $in.force_square}
                                            style="background-image:url('{if $item.media_type=='VIDEO'}{$item.thumbnail_url}{else}{$item.media_url}{/if}')" 
                                         {/if}>
                                        <img id="recent-{$item.id}-thmb" 
                                            class="ins_image " 
                                            src="{if $item.media_type=='VIDEO'}{$item.thumbnail_url}{else}{$item.media_url}{/if}">
                                    </div>
                                    
                            </a>
                        </div>
                    </div>
                </div>
            {/foreach}
            
            {/if}

                </div>
                {if ($footer_slider && $in.direction_nav) || $in.direction_nav>1}
                    <div class="swiper-button swiper-button-outer swiper-button-next"><i class="fto-left-open-3 slider_arrow_left"></i><i class="fto-right-open-3 slider_arrow_right"></i></div>
                    <div class="swiper-button swiper-button-outer swiper-button-prev"><i class="fto-left-open-3 slider_arrow_left"></i><i class="fto-right-open-3 slider_arrow_right"></i></div>
                {/if}
                {if $in.control_nav}
                    <div class="swiper-pagination {if $in.control_nav==2} swiper-pagination-st-custom {/if}"></div>
                {/if}
            </div>
        {/if}
        <div class="alert alert-warning hidden-xs-up ins_no_data">{l s='No images' d='Shop.Theme.Transformer'}</div>
        {if $in.load_more && $in.grid}
        <div class="ins_extra_box" style="display:block;">
            <a href="javascript:;"  style="display:block;" title="{l s='Load more' d='Shop.Theme.Transformer'}"
            class="ins_load_more ins_btn" rel="nofollow">{l s='Load more' d='Shop.Theme.Transformer'}</a>
            </div>
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
            footer_op:1,
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

</div>

    {/foreach}
    <!-- /MODULE st stinstagram -->
{/if}
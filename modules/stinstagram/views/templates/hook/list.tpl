{if isset($grid_list) && $grid_list==1}
<li class="ins_image_wrap 
        {if $in.pro_per_fw} col-fw-{preg_replace('/\./','-',(12/$in.pro_per_fw))} {/if}
        {if $in.pro_per_xxl} col-xxl-{preg_replace('/\./','-',(12/$in.pro_per_xxl))} {/if}
        {if $in.pro_per_xl} col-xl-{preg_replace('/\./','-',(12/$in.pro_per_xl))} {/if}
        {if $in.pro_per_lg} col-lg-{preg_replace('/\./','-',(12/$in.pro_per_lg))} {/if}
        {if $in.pro_per_md} col-md-{preg_replace('/\./','-',(12/$in.pro_per_md))} {/if}
        {if $in.pro_per_sm} col-sm-{preg_replace('/\./','-',(12/$in.pro_per_sm))} {/if}
        {if $in.pro_per_xs} col-{preg_replace('/\./','-',(12/$in.pro_per_xs))} {/if}
        {if $in.pro_per_fw && (($key+1)%$in.pro_per_fw)==1} first-item-of-screen-line {/if}
        {if $in.pro_per_xxl && (($key+1)%$in.pro_per_xxl)==1} first-item-of-large-line {/if}
        {if $in.pro_per_xl && (($key+1)%$in.pro_per_xl)==1} first-item-of-desktop-line {/if}
        {if $in.pro_per_lg &&  (($key+1)%$in.pro_per_lg)==1} first-item-of-line {/if}
        {if $in.pro_per_md &&  (($key+1)%$in.pro_per_md)==1} first-item-of-tablet-line {/if}
        {if $in.pro_per_sm &&  (($key+1)%$in.pro_per_sm)==1} first-item-of-mobile-line {/if}
        {if $in.pro_per_xs &&  (($key+1)%$in.pro_per_xs)==1} first-item-of-portrait-line {/if}
        ">
{/if}
<div class="ins_image_wrap_inner">
    <div class="ins_image_box ins_slider_outer">
        <a href="{if $in.click_action}{$item.media_url}{else}{$item.permalink}{/if}" 
        {if $in.click_action}data-fancybox-group="ins_fancybox_view_" {/if} 
        class=" {if $in.click_action} ins_fancybox {/if} {if $in.hover_effect==1} scaling {elseif $in.hover_effect==2} scaling_down {/if} ins_image_link" 
           {if $in.click_action}
                data-ins_link="{$item.permalink}" 
                data-ins_caption="{$item.caption}" 
                data-ins_full_name="{$in.user_name}"
                data-ins_username="{$in.user_name}"
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
                        <div class="ins_image_info_item ins_image_info_username {if $in.show_username==2}show_default{/if}">
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
                            {if $in.lenght_of_caption && strlen($item.caption)>200}
                                {$item.caption|substr:'0':'200' nofilter}
                            {else}
                                {$item.caption nofilter}
                            {/if}
                        </div>
                    {/if}
                    </div>
                </div>
                <div class="ins_image_layer {if $in.force_square}ins_force_square{/if}">
                    {if $in.force_square}
                        <div class="{if !$in.grid} swiper-lazy {/if}" {if !$in.grid}data-background="{if $item.media_type=='VIDEO'}{$item.thumbnail_url}{else}{$item.media_url}{/if}"{else}style="background-image:url('{if $item.media_type=='VIDEO'}{$item.thumbnail_url}{else}{$item.media_url}{/if}')"{/if}><img src="{$ins_placeholder}" alt=""></div>
                    {else}
                    <img id="recent-{$item.id}-thmb" 
                        class="ins_image {if $in.grid} cate_pro_lazy {else} swiper-lazy {/if}" {if isset($ajax_list) && $ajax_list==1}src="{if $item.media_type=='VIDEO'}{$item.thumbnail_url}{else}{$item.media_url}{/if}"{else}
                        data-src="{if $item.media_type=='VIDEO'}{$item.thumbnail_url}{else}{$item.media_url}{/if}" {/if}>
                    {/if}
                </div>
                {if ($in.show_caption==3 && $item.caption) || $in.show_timestamp }
                <div class="ins_image_info_block">
                    {if $in.show_caption==3}
                        {if $in.lenght_of_caption && strlen($item.caption)>200}
                            {$item.caption|substr:'0':'200' nofilter}
                        {else}
                            {$item.caption nofilter}
                        {/if}
                    {/if}
                    {if $in.show_timestamp }
                    <div class="ins_image_info_basic">
                        {if $in.time_format}
                            {$item.inst_time}
                        {else}
                            {$item.ago_time}
                        {/if}
                    </div>'
                    {/if}
                </div>
                {/if}

        </a>
    </div>
</div>
{if isset($grid_list) && $grid_list==1}
</li>
{/if}
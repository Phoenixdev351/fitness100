{if $stvideos.size_charts}
    {foreach $stvideos.size_charts as $size_chart}
    {if in_array($size_chart.video_position, $video_position) && $size_chart.title && $size_chart.content}
    <div class="inline_popup_wrap pro_right_item">
    <a class="inline_popup_tri {if $size_chart.hide_on_mobile == 1} hidden-md-down {elseif $size_chart.hide_on_mobile == 2} hidden-lg-up {/if}" href="#inline_popup_content_{$size_chart.id_st_video}" title="{$size_chart.title}" rel="nofollow">{$size_chart.title}</a>
    <div id="inline_popup_content_{$size_chart.id_st_video}" class="inline_popup_content mfp-hide mfp-with-anim">{$size_chart.content nofilter}</div>
    </div>
    {/if}
    {/foreach}
{/if}
{if $stvideos.videos}
    {foreach $stvideos.videos as $video}
    {if in_array($video.video_position, $video_position) && $video.url}
        <a class="st_popup_video {if $video.hide_on_mobile == 1} hidden-md-down {elseif $video.hide_on_mobile == 2} hidden-lg-up {/if}  pro_right_item" href="{$video.url}" title="{l s='View video' d='Shop.Theme.Transformer'}" rel="nofollow">{if count(array_intersect(array(13,14,15), $video_position))}{l s='View video' d='Shop.Theme.Transformer'}{else}<i class="fto-play fs_lg"></i>{/if}</a>
    {/if}
    {/foreach}
{/if}
{foreach $stvideos as $video}
{if $video.url && in_array($video.video_position, $video_position)}
<div class="st_popup_video_wrap st_popup_video_{$video.id_st_video}">
<a class="st_popup_video layer_icon_wrap {if $video.hide_on_mobile == 1} hidden-md-down {elseif $video.hide_on_mobile == 2} hidden-lg-up {/if}" href="{$video.url}" title="{l s='Open video' d='Shop.Theme.Transformer'}" rel="nofollow"><i class="fto-play"></i></a>
</div>
{/if}
{/foreach}

            <li>
                <a href="{if $in.click_action}{$item.media_url}{else}{$item.permalink}{/if}" 
                {if $in.click_action}
                                        data-fancybox-group="ins_fancybox_view_"
                                    {/if} 
                                class="{if $in.click_action}ins_fancybox{/if}{if $in.hover_effect==1} scaling={elseif $in.hover_effect==2} scaling_down={/if} ins_image_link" 
                                {if $in.click_action}
                                        data-ins_link=" {$item.permalink}" 
                                        data-ins_caption="{$item.caption}" 
                                        data-ins_full_name="{$in.user_name}"
                                        data-ins_username="{$in.user_name}"
                                        data-ins_profile_picture=""
                                        data-ins_created_time="{if $in.time_format}
                                            {$item.inst_time}
                                            {else}
                                            {$item.ago_time}
                                            {/if}"
                                        data-ins_video="{if $item.media_type=='VIDEO'}{$item.media_url}{/if}"
                                        data-ins_video_width="720" 
                                        data-ins_video_height="720" 
                                        rel="nofollow"
                                {/if}
                >
                    <img class="ins_image "  src="{if $item.media_type=='VIDEO'}{$item.thumbnail_url}{else}{$item.media_url}{/if}" alt="{$item.caption nofilter}">
                </a>
            </li>

{if $banner_data['url'] && !$banner_data['description_has_links']}
    <a id="st_banner_block_{$banner_data['id_st_banner']}" href="{$banner_data['url']|escape:'html'}" class="st_banner_block_{$banner_data['id_st_banner']} st_banner_block" target="{if $banner_data['new_window']}_blank{else}_self{/if}" title="{$banner_data['title']|escape:'htmlall':'UTF-8'}" style="{if !$banner_style}height:{$banner_height}px;{/if}">
{else}
    <div id="st_banner_block_{$banner_data['id_st_banner']}" class="st_banner_block_{$banner_data['id_st_banner']} st_banner_block" style="{if !$banner_style}height:{$banner_height}px;{/if}">
{/if}
    {if $banner_style}
        <picture>
        <!--[if IE 9]><video style="display: none;"><![endif]-->
            {if isset($banner_data['image_multi_lang_xs']) && $banner_data['image_multi_lang_xs']}
            {if isset($stwebp_type) && $stwebp_type}<source {if $banner_lazy_loading}data-{/if}srcset="{$banner_data['image_multi_lang_xs']|regex_replace:'/\.(jpg|jpeg|png|gif)$/':'.webp'}" media="(max-width: 480px)" type="image/webp">{/if}
            <source {if $banner_lazy_loading}data-{/if}srcset="{$banner_data['image_multi_lang_xs']}" media="(max-width: 480px)">
            {/if}
            {if isset($banner_data['image_multi_lang_sm']) && $banner_data['image_multi_lang_sm']}
            {if isset($stwebp_type) && $stwebp_type}<source {if $banner_lazy_loading}data-{/if}srcset="{$banner_data['image_multi_lang_sm']|regex_replace:'/\.(jpg|jpeg|png|gif)$/':'.webp'}" media="(max-width: 768px)" type="image/webp">{/if}
            <source {if $banner_lazy_loading}data-{/if}srcset="{$banner_data['image_multi_lang_sm']}" media="(max-width: 768px)">
            {/if}
            {if isset($stwebp_type) && $stwebp_type && strtolower(substr($banner_data['image_multi_lang'], -3))!='gif'}<source {if $banner_lazy_loading}data-{/if}srcset="{$banner_data['image_multi_lang']|regex_replace:'/\.(jpg|jpeg|png|gif)$/':'.webp'}" type="image/webp">{/if}
        <!--[if IE 9]></video><![endif]-->
        <img class="adveditor_image {if $banner_lazy_loading} st_banner_lazy_image{/if}" {if $banner_lazy_loading}data-{/if}src="{$banner_data['image_multi_lang']}" alt="{$banner_data['title']|escape:'htmlall':'UTF-8'}" {if isset($banner_data['width']) && $banner_data['width']} width="{$banner_data['width']}"{/if} {if isset($banner_data['height']) && $banner_data['height']} height="{$banner_data['height']}"{/if} />
        </picture>
    {else}
        <div class="st_banner_image" {if $banner_lazy_loading && isset($banner_data['image_multi_lang']) && $banner_data['image_multi_lang']} data-background="{$banner_data['image_multi_lang']}"{/if} style="{if !$banner_lazy_loading && isset($banner_data['image_multi_lang']) && $banner_data['image_multi_lang']}background-image:url({$banner_data['image_multi_lang']});{/if}{if isset($banner_data['bg_color']) && $banner_data['bg_color']}background-color:{$banner_data['bg_color']};{/if}"></div>
    {/if}
    {if $banner_data['description']}
        <div class="st_image_layered_description {if $banner_data.hide_text_on_mobile} hidden-sm-down {/if} {if $banner_data.text_align==1} text-1 {elseif $banner_data.text_align==3} text-3 {else} text-2 {/if} {if $banner_data.text_position==1} flex_start flex_left {elseif $banner_data.text_position==2} flex_start flex_center {elseif $banner_data.text_position==3} flex_start flex_right {elseif $banner_data.text_position==4} flex_middle flex_left {elseif $banner_data.text_position==6} flex_middle flex_right {elseif $banner_data.text_position==7} flex_end flex_left {elseif $banner_data.text_position==8} flex_end flex_center {elseif $banner_data.text_position==9} flex_end flex_right {else} flex_middle flex_center {/if}">
        	<div class="st_image_layered_description_inner {if $banner_data.text_width} width_{$banner_data.text_width} {/if} style_content">
        	{$banner_data['description'] nofilter}
        	</div>
        </div>
    {/if}
{if $banner_data['url'] && !$banner_data['description_has_links']}
    </a>
{else}
    </div>
{/if}
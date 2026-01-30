{if isset($columns_data)}
    <!-- MODULE st banner column -->
    <div class="row">
        {foreach $columns_data as $column}
            {if ( isset($column['columns']) && count($column['columns']) ) || ( isset($column['banners']) && count($column['banners']) )}
                <div id="banner_box_{$column['id_st_banner_group']}" class="col-md-{$column['width']} {if isset($column['width_md'])} col-sm-{if $column['width_md']!=0}{$column['width_md']}{else}{$column['width']}{/if} col-{if $column['width_xs']!=0}{$column['width_xs']}{elseif $column['width_md']!=0}{$column['width_md']}{else}{$column['width']}{/if}{/if} banner_col {if isset($column['banner_b']) && $column['banner_b']} banner_b{/if} {if $column['hide_on_mobile'] == 1} hidden-sm-down {elseif $column['hide_on_mobile'] == 2} hidden-md-up {/if}" data-height="{$column['height']}" >
                    {if isset($column['banners']) && count($column['banners'])}
                        {include file="module:stbanner/views/templates/hook/stbanner-block.tpl" banner_data=$column['banners'][0] banner_height=$column['height_px'] banner_style=$banner_style banner_lazy_loading=$banner_lazy_loading}
                    {else}
                        {include file="module:stbanner/views/templates/hook/stbanner-column.tpl" columns_data=$column['columns'] banner_style=$banner_style banner_lazy_loading=$banner_lazy_loading}
                    {/if}
                </div>
            {/if}
        {/foreach}        
    </div>
    <!--/ MODULE st banner column-->
{/if}
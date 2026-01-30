{*
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
*}
<script type="text/javascript">
    var url_home = "{$url_home nofilter}";
    var dataTimes = [];
    {foreach from=$times item='time'}
    var temp_date= new Date("{$time.time|escape:'html':'UTF-8'}").getTime();
    var temp = [temp_date, {$time.value|escape:'html':'UTF-8'}];
    dataTimes.push(temp);
    {/foreach}
    var dataset;
    var updateInterval = {$updateInterval|intval};
    var request_time=0;
    var error_load= '{l s='Could not connect to your website. Please make sure the front office is accessible.' mod='ets_superspeed' js='1'}';
    var percent_optimized_images ={$percent_optimized_images|floatval};
    var percent_unoptimized_images ={$percent_unoptimized_images|floatval};
    var cache_url_ajax ='{$cache_url_ajax nofilter}';
    var yes_text= '{l s='On' mod='ets_superspeed' js='1'}';
    var no_text = '{l s='Off' mod='ets_superspeed' js='1'}';
    var close_text='{l s='Close' mod='ets_superspeed' js='1'}';
    var chart_image_optimize;
    var total_images ={$total_images|intval};
    var no_data_text ='{l s='No data available' mod='ets_superspeed' js='1'}';
    var images_optimized_text= '{l s='images optimized' mod='ets_superspeed' js='1'}';
    var link_logo = '{$link_logo nofilter}';
</script>
<!-- HTML -->
<form id="configuration_form" action="{$link_optimize_image nofilter}">
    <div class="page-dashboard form-wrapper">
        <div class="col-sm-8 statics_timeline">
            <div class="bg_white">
                <h3>{l s='Page speed timelife' mod='ets_superspeed'}
                    <div class="question-mark-wrapper">
                        <i class="fa fa-question-circle-o"></i>
                        <span class="question-mark">{l s='Calculated base on homepage loading time' mod='ets_superspeed'}</span>
                    </div>
                </h3>
                <span class="time_zone">{l s='Time zone:' mod='ets_superspeed'} UTC{if $time_zone >=0}+{/if}{$time_zone|escape:'html':'UTF-8'}</span>
                <div id="flot-placeholder1" style="width:95%;height:400px;margin:0 auto"></div>
            </div>
        </div>
        <div class="col-sm-4 statics_table_currentpage">
            <div class="speed-meter">
                <h3>{l s='Page loading time' mod='ets_superspeed'}
                    <div class="question-mark-wrapper">
                    <i class="fa fa-question-circle-o"></i>
                    <span class="question-mark">{l s='Calculated base on homepage loading time' mod='ets_superspeed'}</span>
                    </div>
                    </h3>
                <div id="speed-meter-preview">
                    <canvas width=400 height=250 id="speed-canvas-preview"></canvas>
                    <div class="current-speed">{l s='Page loading time' mod='ets_superspeed'}: <div id="speed-preview-textfield"></div></div>
                    <ul class="list-speed-meter">
                        <li>
                            <i class="icon icon-good"></i>
                            {l s='Excellent' mod='ets_superspeed'}
                            <span class="time">{l s='0s - 1s' mod='ets_superspeed'}</span>
                        </li>
                        <li>
                            <i class="icon icon-good"></i>
                            {l s='Good' mod='ets_superspeed'}
                            <span class="time">{l s='1s - 5s' mod='ets_superspeed'}</span>
                        </li>
                        <li>
                            <i class="icon icon-acceptance"></i>
                            {l s='Acceptable' mod='ets_superspeed'}
                            <span class="time">{l s='5s - 10s' mod='ets_superspeed'}</span>
                        </li>
                        <li>
                            <i class="icon icon-not-good"></i>
                            {l s='Bad' mod='ets_superspeed'}
                            <span class="time">{l s='10s - more' mod='ets_superspeed'}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-sm-4 statics_img">
            <div class="sp-dashboard-image">
                <h3>{l s='Image optimization' mod='ets_superspeed'}
                    <div class="question-mark-wrapper">
                    <i class="fa fa-question-circle-o"></i>
                    <span class="question-mark">{l s='Optimize standard Prestashop images such as product image, category image, manufacturer images, etc. Not including theme images and custom module images.' mod='ets_superspeed'}</span>
                    </div>
                    </h3>
                    
                <div id="sp-image-canvas-holder" style="width:100%">
                    <span class="percent-image-in-chart">{$percent_optimized_images|floatval}%</span>
                    <canvas id="sp-image-chart-area"></canvas>

                </div>
                <div class="image-dashboad-image-optimize">
                    <div class="dashboad-image-optimized">
                        <label>{l s='Optimized' mod='ets_superspeed'}</label>
                        <span class="percent-image">
                            {$percent_optimized_images|floatval}%
                            <span class="number-image">{if $total_optimized_images}({$total_optimized_images|intval}&nbsp;{l s='images' mod='ets_superspeed'}){/if}</span>
                        </span>
                    </div>
                    <div class="line_vertical"></div>
                    <div class="dashboad-image-unoptimized">
                        <label>{l s='Unoptimized' mod='ets_superspeed'}</label>
                        <span class="percent-image">
                            {$percent_unoptimized_images|floatval}%
                            <span class="number-image">{if $percent_unoptimized_images}({$total_unoptimized_images|intval}&nbsp;{l s='images' mod='ets_superspeed'}){/if}</span>
                        </span>
                    </div>
                </div>
                {if $total_unoptimized_images}
                    <div class="button-group">
                        <button class="btn btn-default optimize_all_images"><i class="process-icon-cogs"></i>{l s='Optimize all images' mod='ets_superspeed'}</button>
                    </div>
                {/if}
            </div>
        </div>
        <div class="col-sm-4 statics_cache">
            <div class="sp-dashboard-cache">
                <h3>{l s='Speed optimization check list' mod='ets_superspeed'}
                    <div class="question-mark-wrapper">
                    <i class="fa fa-question-circle-o"></i>
                    <span class="question-mark">{l s='Make sure you turn on all speed optimization features to maximize your website speed. Run "Auto Configuration" to quickly set everything up' mod='ets_superspeed'}</span>
                    </div>
                </h3>
                <ul class="list-sp-dashboard-check-cache">
                    <li class="page_cache">
                        <div class="block-left{if $ETS_SPEED_ENABLE_PAGE_CACHE} yes{/if}">{l s='Page cache' mod='ets_superspeed'} <span class="total_cache">{if $total_file}{$total_cache|escape:'html':'UTF-8'} ({$total_file|intval} {if $total_file==1}{l s='file' mod='ets_superspeed'}{else}{l s='files' mod='ets_superspeed'}{/if}){/if}</span></div>
                        <div class="block-right">
                            {if $ETS_SPEED_ENABLE_PAGE_CACHE}
                                <span class="check-yes">{l s='On' mod='ets_superspeed'}</span>
                            {else}
                                <span class="check-no">{l s='Off' mod='ets_superspeed'}</span>
                            {/if}
                        </div>
                    </li>
                    <li class="smarty_cache">
                        <div class="block-left {if $ETS_SPEED_SMARTY_CACHE} yes{/if}">{l s='Smarty Cache' mod='ets_superspeed'}</div>
                        <div class="block-right">
                            {if $ETS_SPEED_SMARTY_CACHE}
                                <span class="check-yes">{l s='On' mod='ets_superspeed'}</span>
                            {else}
                                <span class="check-no">{l s='Off' mod='ets_superspeed'}</span>
                            {/if}
                        </div>
                    </li>
                    <li class="server_cache">
                        <div class="block-left {if $PS_SMARTY_CACHE} yes{/if}">{l s='Server Cache' mod='ets_superspeed'}</div>
                        <div class="block-right">
                            {if $PS_SMARTY_CACHE }
                                <span class="check-yes">{l s='On' mod='ets_superspeed'}</span>
                            {else}
                                <span class="check-no">{l s='Off' mod='ets_superspeed'}</span>
                            {/if}
                        </div>
                    </li>
                    <li class="minify_html">
                        <div class="block-left {if $PS_HTML_THEME_COMPRESSION} yes{/if}">{l s='Minify HTML' mod='ets_superspeed'}</div>
                        <div class="block-right">
                            {if $PS_HTML_THEME_COMPRESSION}
                                <span class="check-yes">{l s='On' mod='ets_superspeed'}</span>
                            {else}
                                <span class="check-no">{l s='Off' mod='ets_superspeed'}</span>
                            {/if}
                        </div>
                    </li>     
                    <li class="minify_javascript">
                        <div class="block-left {if $PS_JS_THEME_CACHE} yes{/if}">{l s='Minify Javascript' mod='ets_superspeed'}</div>
                        <div class="block-right">
                            {if $PS_JS_THEME_CACHE}
                                <span class="check-yes">{l s='On' mod='ets_superspeed'}</span>
                            {else}
                                <span class="check-no">{l s='Off' mod='ets_superspeed'}</span>
                            {/if}
                        </div>
                    </li>
                    <li class="minify_css">
                        <div class="block-left {if $PS_CSS_THEME_CACHE} yes{/if}">{l s='Minify CSS' mod='ets_superspeed'}</div>
                        <div class="block-right">
                            {if $PS_CSS_THEME_CACHE}
                                <span class="check-yes">{l s='On' mod='ets_superspeed'}</span>
                            {else}
                                <span class="check-no">{l s='Off' mod='ets_superspeed'}</span>
                            {/if}
                        </div>
                    </li>
                    <li class="browser_cache">
                        <div class="block-left{if $PS_HTACCESS_CACHE_CONTROL} yes{/if}">{l s='Browser cache and Gzip' mod='ets_superspeed'}
                        </div>
                        <div class="block-right">
                            {if $PS_HTACCESS_CACHE_CONTROL}
                                <span class="check-yes">{l s='On' mod='ets_superspeed'}</span>
                            {else}
                                <span class="check-no">{l s='Off' mod='ets_superspeed'}</span>
                            {/if}
                        </div>
                    </li>
                    <li class="production_mode">
                        <div class="block-left {if !$PS_MODE_DEV} yes{/if}">{l s='Production mode' mod='ets_superspeed'}</div>
                        <div class="block-right">
                            {if !$PS_MODE_DEV}
                                <span class="check-yes">{l s='On' mod='ets_superspeed'}</span>
                            {else}
                                <span class="check-no">{l s='Off' mod='ets_superspeed'}</span>
                            {/if}
                        </div>
                    </li>
                    <li class="optimize_existing_images">
                        <div class="block-left-image block-left{if !$total_unoptimized_images} yes{/if}">
                            {l s='Optimize existing images' mod='ets_superspeed'} <br /> <span class="total_image_optimized">{if $total_optimized_images}{$total_optimized_images|intval} {l s='images optimized' mod='ets_superspeed'}{/if}</span> <span class="total_image_optimized_size">{if $total_optimized_size_images && $total_optimized_images}{$total_optimized_size_images|escape:'html':'UTF-8'}{/if}</span>
                            {if $total_unoptimized_images}
                                <span class="total_unoptimized_images">{$total_unoptimized_images|intval}&nbsp;{l s='unoptimized images' mod='ets_superspeed'}</span>
                            {/if}
                        </div>
                        <div class="block-right block-right-image">
                            {if !$total_unoptimized_images}
                                <span class="check-yes">{l s='On' mod='ets_superspeed'}</span>
                            {else}
                                <span class="check-no">{l s='Off' mod='ets_superspeed'}</span>
                            {/if}
                        </div>
                    </li>
                </ul>
                <div class="button-group">
                    <button class="btn btn-default pull-right" type="button" name="btnSubmitPageCacheDashboard"><i class="process-icon-auto-configure"></i>&nbsp;{l s='Auto configuration' mod='ets_superspeed'}</button>
                    <button class="btn btn-default pull-center" type="button" name="btnSubmitDisabledPageCacheDashboard"><i class="fa fa-times"></i>&nbsp;{l s='Disable all caches' mod='ets_superspeed'}</button>
                    <button class="btn btn-default pull-left" type="button" name="clear_all_page_caches_dashboard"><i class="icon-trash"></i>&nbsp;{l s='Clear all caches' mod='ets_superspeed'}</button>
                </div>
            </div>
        </div>
       <div class="col-sm-4 statics_check_point">
            <div class="sp-dashboard-generation_check_point">
                <div class="page_cache_generation_check_point">
                    <div  class="page_cache_generation_check_point_header">
                        <h3>{l s='System Analytics' mod='ets_superspeed'}
                            <div class="question-mark-wrapper">
                                <i class="fa fa-info-circle"></i>
                                <span class="question-mark">{l s='Extra check points to make sure fastest speed for front office.' mod='ets_superspeed'}</span>
                            </div>
                        </h3>
                        <button class="btn btn-default pull-right" type="button" name="btnRefreshSystemAnalyticsNew"><i class="fa fa-refresh"></i> &nbsp; {l s='Refresh' mod='ets_superspeed'}</button>
                    </div>
                    <div class="page_cache_generation_check_point_body">
                        <table class="table table_analytics" style="display: table;">
                            <thead>
                            <tr>
                                <th>{l s='Check point' mod='ets_superspeed'}</th>
                                <th>{l s='Current data' mod='ets_superspeed'}</th>
                                <th>{l s='Status' mod='ets_superspeed'}</th>
                            </tr>
                            </thead>
                            <tbody>
                                <tr class="sql">
                                    <td>{l s='Number of SQL queries' mod='ets_superspeed'}</td>
                                    <td colspan="2">
                                        <span class="gach">--</span>
                                        <span class="no-cache" style="display:none"><span class="number"></span> ({l s='no-cache' mod='ets_superspeed'})</span><br />
                                        <span class="cached" style="display:none"><span class="number"></span> ({l s='cached - saved' mod='ets_superspeed'} <span class="saved"></span>)</span>
                                    </td>
                                </tr>
                                <tr class="time">
                                    <td>{l s='TTFB' mod='ets_superspeed'}</td>
                                    <td colspan="2">
                                        <span class="gach">--</span>
                                        <span class="no-cache" style="display:none"><span class="number"></span> ({l s='no-cache' mod='ets_superspeed'})</span><br />
                                        <span class="cached" style="display:none"><span class="number"></span> ({l s='cached' mod='ets_superspeed'} - <span class="saved"></span> {l s='faster' mod='ets_superspeed'})</span>
                                    </td>
                                </tr>
                            {if $check_points}
                                {foreach from = $check_points item='check_point'}
                                    <tr {if isset($check_point.name)} class="{$check_point.name|escape:'html':'UTF-8'}"{/if}>
                                        <td>{$check_point.check_point|escape:'html':'UTF-8'}</td>
                                        <td class="number_data">
                                            {if isset($check_point.name) && ($check_point.name=='media_server' || $check_point.name=='caching_server')}
                                                {if $check_point.server}
                                                    {$check_point.server|escape:'html':'UTF-8'}
                                                {else}
                                                    -
                                                {/if}
                                            {elseif isset($check_point.number_data)}
                                                {$check_point.number_data|escape:'html':'UTF-8'}
                                            {else}-{/if}
                                        </td>
                                        <td class="status">
                                            {if isset($check_point.status) && isset($check_point.class_status)}
                                                <span class="{$check_point.class_status|escape:'html':'UTF-8'}">{$check_point.status|escape:'html':'UTF-8'}</span>
                                            {else}
                                                {if isset($check_point.number_data)}
                                                    {if $check_point.number_data == '-'}
                                                        <span>-</span>
                                                    {elseif $check_point.number_data <= $check_point.default}
                                                        <span class="status-good">{l s='Good' mod='ets_superspeed'}</span>
                                                    {elseif $check_point.number_data >= $check_point.bad}
                                                        <span class="status-bad">{l s='Bad' mod='ets_superspeed'}</span>
                                                    {else}
                                                        <span class="status-reputable">{l s='Acceptable' mod='ets_superspeed'}</span>
                                                    {/if}
                                                {elseif isset($check_point.enabled)}
                                                    {if $check_point.enabled}
                                                        <span class="status-good">{l s='Good' mod='ets_superspeed'}</span>
                                                    {else}
                                                        <span class="status-disabled">{l s='Not configured' mod='ets_superspeed'}</span>
                                                    {/if}
                                                {/if}
                                            {/if}
                                        </td>
                                    </tr>
                                {/foreach}
                                <tr >
                                    <td class="text-center" colspan="100%">
                                        <a class="btn btn-default viewmore_config" href="{$link->getAdminLink('AdminSuperSpeedSystemAnalytics')|escape:'html':'UTF-8'}&tab_current=extra_checks">
                                            {l s='View more & configure' mod='ets_superspeed'}
                                        </a>
                                    </td>
                                </tr>
                            {/if}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="panel-footer" style="height:auto">
        <div class="col-sm-12">
            <div class="popup-configuration-cache">
                <div class="popup-content">
                    <div class="popup-content-header popup_run">
                        <h3>{l s='Auto configuration' mod='ets_superspeed'}</h3>
                        <div class="alert alert-info">{l s='We are setting everything up for you, please wait...' mod='ets_superspeed'}</div>
                        <div class="sp_sussec">
                            {l s='Congratulations! Everything is done, your website is now much faster than before.' mod='ets_superspeed'}
                        </div>
                    </div>
                    <div class="popup-content-body">
                        <div class="popup_run">
                            <ul>
                                <li class="page_cache auto{if !$ETS_SPEED_ENABLE_PAGE_CACHE} disabled{/if}"><i class="icon-clock"></i>{l s='Page cache' mod='ets_superspeed'}</li>
                                <li class="smarty_cache auto{if !$ETS_SPEED_SMARTY_CACHE} disabled{/if}"><i class="icon-clock"></i>{l s='Smarty Cache' mod='ets_superspeed'}</li>
                                <li class="server_cache auto{if !$PS_SMARTY_CACHE} disabled{/if}"><i class="icon-clock"></i>{l s='Server Cache' mod='ets_superspeed'}</li>
                                <li class="minify_html auto{if !$PS_HTML_THEME_COMPRESSION} disabled{/if}"><i class="icon-clock"></i>{l s='Minify HTML' mod='ets_superspeed'}</li>
                                <li class="minify_javascript auto{if !$PS_JS_THEME_CACHE} disabled{/if}"><i class="icon-clock"></i>{l s='Minify Javascript' mod='ets_superspeed'}</li>
                                <li class="minify_css auto{if !$PS_CSS_THEME_CACHE} disabled{/if}"><i class="icon-clock"></i>{l s='Minify CSS' mod='ets_superspeed'}</li>
                                <li class="browser_cache auto{if !$PS_HTACCESS_CACHE_CONTROL} disabled{/if}"><i class="icon-clock"></i>{l s='Browser cache and Gzip' mod='ets_superspeed'}</li>
                                <li class="production_mode auto{if $PS_MODE_DEV} disabled{/if}"><i class="icon-clock"></i>{l s='Production mode' mod='ets_superspeed'}</li>
                                {if $total_unoptimized_images}
                                    <li class="optimize_existing_images optimize-image disabled">
                                        <div class="flex">
                                            <span class="optimize-image-text">
                                                {l s='Optimize existing images' mod='ets_superspeed'}
                                            </span>
                                            {if $total_unoptimized_images}
                                                <span class="image-optimizing">
                                                    <span class="image_optimizing">
                                                        {l s='Optimizing' mod='ets_superspeed'} <b class="total_need_optimized_images">{$total_unoptimized_images|intval}</b> {l s='images' mod='ets_superspeed'}: 
                                                        <span class="percent-image-optimized">0%</span>
                                                    </span>
                                                    <span class="number-image-optimized">(<span class="number-image">{$total_optimized_images|intval}</span>&nbsp;{l s='images' mod='ets_superspeed'})</span>
                                                </span>
                                            {/if}
                                        </div>
                                    </li>
                                {/if}
                            </ul>
                            <div class="button-group">
                                <button class="btn btn-default pull-left optimize_pause">
                                    {l s='Pause' mod='ets_superspeed'}
                                </button>
                                <button class="btn btn-default pull-right optimize_stop">
                                    {l s='Stop' mod='ets_superspeed'}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="confirm-popup-configuration-cache confirm-popup">
                <div class="popup-content">
                    <h3>{l s='Auto configuration' mod='ets_superspeed'}</h3>
                    <ul class="list-chonse-configuration-auto">
                        <li>
                           <label for="page_cache">
                                {l s='Page cache' mod='ets_superspeed'}
                                <input type="checkbox" value="1" name="page_cache" {if $ETS_SPEED_ENABLE_PAGE_CACHE} checked="checked"{/if} id="page_cache" />
                                <span class="sp_configuration_switch">
                                    <span class="sp_configuration_label on">{l s='On' mod='ets_superspeed'}</span>
                                    <span class="sp_configuration_label off">{l s='Off' mod='ets_superspeed'}</span>
                                </span>
                           </label>
                        </li>
                        <li>
                           <label for="smarty_cache">
                                {l s='Smarty Cache' mod='ets_superspeed'}
                                <input type="checkbox" value="1" name="smarty_cache" {if $ETS_SPEED_SMARTY_CACHE} checked="checked"{/if} id="smarty_cache" />
                                <span class="sp_configuration_switch">
                                    <span class="sp_configuration_label on">{l s='On' mod='ets_superspeed'}</span>
                                    <span class="sp_configuration_label off">{l s='Off' mod='ets_superspeed'}</span>
                                </span>
                           </label>
                        </li>
                        <li>
                           <label for="server_cache">
                                {l s='Server Cache' mod='ets_superspeed'}
                                <input type="checkbox" value="1" name="server_cache" id="server_cache" {if $PS_SMARTY_CACHE} checked="checked"{/if} />
                                <span class="sp_configuration_switch">
                                    <span class="sp_configuration_label on">{l s='On' mod='ets_superspeed'}</span>
                                    <span class="sp_configuration_label off">{l s='Off' mod='ets_superspeed'}</span>
                                </span>
                           </label>
                        </li>
                        <li>
                           <label for="minify_html">
                                {l s='Minify HTML' mod='ets_superspeed'}
                                <input type="checkbox" value="1" name="minify_html" {if $PS_HTML_THEME_COMPRESSION} checked="checked"{/if} id="minify_html" />
                                <span class="sp_configuration_switch">
                                    <span class="sp_configuration_label on">{l s='On' mod='ets_superspeed'}</span>
                                    <span class="sp_configuration_label off">{l s='Off' mod='ets_superspeed'}</span>
                                </span>
                           </label>
                        </li>
                        <li>
                           <label for="minify_javascript">
                                {l s='Minify Javascript' mod='ets_superspeed'}
                                <input type="checkbox" value="1" name="minify_javascript" {if $PS_JS_THEME_CACHE} checked="checked"{/if} id="minify_javascript"/>
                                <span class="sp_configuration_switch">
                                    <span class="sp_configuration_label on">{l s='On' mod='ets_superspeed'}</span>
                                    <span class="sp_configuration_label off">{l s='Off' mod='ets_superspeed'}</span>
                                </span>
                           </label>
                        </li>
                        <li>
                           <label for="minify_css">
                                {l s='Minify CSS' mod='ets_superspeed'}
                                <input type="checkbox" value="1" name="minify_css" {if $PS_CSS_THEME_CACHE} checked="checked"{/if} id="minify_css" />
                                <span class="sp_configuration_switch">
                                    <span class="sp_configuration_label on">{l s='On' mod='ets_superspeed'}</span>
                                    <span class="sp_configuration_label off">{l s='Off' mod='ets_superspeed'}</span>
                                </span>
                           </label>
                        </li>
                        <li>
                           <label for="browser_cache">
                                {l s='Browser cache and Gzip' mod='ets_superspeed'}
                                <input type="checkbox" value="1" name="browser_cache" {if $PS_HTACCESS_CACHE_CONTROL} checked="checked"{/if} id="browser_cache" />
                                <span class="sp_configuration_switch">
                                    <span class="sp_configuration_label on">{l s='On' mod='ets_superspeed'}</span>
                                    <span class="sp_configuration_label off">{l s='Off' mod='ets_superspeed'}</span>
                                </span>
                           </label>
                        </li>
                        <li>
                           <label for="production_mode">
                                {l s='Production mode' mod='ets_superspeed'}
                                <input type="checkbox" value="1" name="production_mode" {if !$PS_MODE_DEV} checked="checked"{/if} id="production_mode" />
                                <span class="sp_configuration_switch">
                                    <span class="sp_configuration_label on">{l s='On' mod='ets_superspeed'}</span>
                                    <span class="sp_configuration_label off">{l s='Off' mod='ets_superspeed'}</span>
                                </span>
                           </label>
                        </li>
                        {if $total_unoptimized_images}
                            <li>
                               <label for="optimize_existing_images">
                                    {l s='Optimize existing images' mod='ets_superspeed'}
                                    <br /><span class="total_image_optimized">{if $total_optimized_images}{$total_optimized_images|intval} {l s='images optimized' mod='ets_superspeed'}{/if}</span> <span class="total_image_optimized_size">{if $total_optimized_size_images && $total_optimized_images}{$total_optimized_size_images|escape:'html':'UTF-8'}{/if}</span>
                                    <span class="total_unoptimized_images">{$total_unoptimized_images|intval}&nbsp;{l s='unoptimized images' mod='ets_superspeed'}</span>
                                    <input type="checkbox" value="1" name="optimize_existing_images" id="optimize_existing_images" />
                                    <span class="sp_configuration_switch">
                                        <span class="sp_configuration_label on">{l s='On' mod='ets_superspeed'}</span>
                                        <span class="sp_configuration_label off">{l s='Off' mod='ets_superspeed'}</span>
                                    </span>
                               </label>
                            </li>
                        {/if}
                    </ul>
                    <div class="button-group">
                        <button class="btn btn-default pull-left confirm-popup-no">
                            <svg width="14" height="14" style="vertical-align: -2px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/></svg> {l s='Cancel' mod='ets_superspeed'}</button>
                        <button class="btn btn-default pull-right confirm-popup-configuration-cache-yes">
                            <svg width="14" height="14" style="vertical-align: -2px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M403.8 34.4c12-5 25.7-2.2 34.9 6.9l64 64c6 6 9.4 14.1 9.4 22.6s-3.4 16.6-9.4 22.6l-64 64c-9.2 9.2-22.9 11.9-34.9 6.9s-19.8-16.6-19.8-29.6V160H352c-10.1 0-19.6 4.7-25.6 12.8L284 229.3 244 176l31.2-41.6C293.3 110.2 321.8 96 352 96h32V64c0-12.9 7.8-24.6 19.8-29.6zM164 282.7L204 336l-31.2 41.6C154.7 401.8 126.2 416 96 416H32c-17.7 0-32-14.3-32-32s14.3-32 32-32H96c10.1 0 19.6-4.7 25.6-12.8L164 282.7zm274.6 188c-9.2 9.2-22.9 11.9-34.9 6.9s-19.8-16.6-19.8-29.6V416H352c-30.2 0-58.7-14.2-76.8-38.4L121.6 172.8c-6-8.1-15.5-12.8-25.6-12.8H32c-17.7 0-32-14.3-32-32s14.3-32 32-32H96c30.2 0 58.7 14.2 76.8 38.4L326.4 339.2c6 8.1 15.5 12.8 25.6 12.8h32V320c0-12.9 7.8-24.6 19.8-29.6s25.7-2.2 34.9 6.9l64 64c6 6 9.4 14.1 9.4 22.6s-3.4 16.6-9.4 22.6l-64 64z"/></svg> {l s='Run auto configuration' mod='ets_superspeed'}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="confirm-popup-optimize_all_images confirm-popup">
                <div class="popup-content">
                    <div class="popup-title">
                        <h3>{l s='Image optimization' mod='ets_superspeed'}</h3>
                        <span class="confirm-popup-no" title="{l s='Close' mod='ets_superspeed'}">{l s='Close' mod='ets_superspeed'}</span>
                    </div>
                    {l s='All unoptimized images will be replaced by optimized images. You can restore your old images in "Image optimization" tab by adjusting "Image quality" to 100%. Do you want to continue?' mod='ets_superspeed'}
                    <div class="button-group">
                        <a class="btn btn-default pull-left confirm-custom-optimization" href="{$link->getAdminLink('AdminSuperSpeedImage')|escape:'html':'UTF-8'}">{l s='Custom optimization' mod='ets_superspeed'}</a>
                        <button class="btn btn-default pull-right confirm-popup-optimize_all_images-yes">
                            <svg width="14" height="14" style="vertical-align: -2px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M403.8 34.4c12-5 25.7-2.2 34.9 6.9l64 64c6 6 9.4 14.1 9.4 22.6s-3.4 16.6-9.4 22.6l-64 64c-9.2 9.2-22.9 11.9-34.9 6.9s-19.8-16.6-19.8-29.6V160H352c-10.1 0-19.6 4.7-25.6 12.8L284 229.3 244 176l31.2-41.6C293.3 110.2 321.8 96 352 96h32V64c0-12.9 7.8-24.6 19.8-29.6zM164 282.7L204 336l-31.2 41.6C154.7 401.8 126.2 416 96 416H32c-17.7 0-32-14.3-32-32s14.3-32 32-32H96c10.1 0 19.6-4.7 25.6-12.8L164 282.7zm274.6 188c-9.2 9.2-22.9 11.9-34.9 6.9s-19.8-16.6-19.8-29.6V416H352c-30.2 0-58.7-14.2-76.8-38.4L121.6 172.8c-6-8.1-15.5-12.8-25.6-12.8H32c-17.7 0-32-14.3-32-32s14.3-32 32-32H96c30.2 0 58.7 14.2 76.8 38.4L326.4 339.2c6 8.1 15.5 12.8 25.6 12.8h32V320c0-12.9 7.8-24.6 19.8-29.6s25.7-2.2 34.9 6.9l64 64c6 6 9.4 14.1 9.4 22.6s-3.4 16.6-9.4 22.6l-64 64z"/></svg> {l s='Optimize all images now' mod='ets_superspeed'}</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="confirm-popup-configuration-disabled-cache confirm-popup">
                <div class="popup-content">
                    {l s='Disable all caches will slow down the website loading speed and increase the use of server resources. This is only recommended if your website is in development.' mod='ets_superspeed'}
                    <div class="button-group">
                        <button class="btn btn-default pull-left confirm-popup-configuration-disable-cache-yes">{l s='Yes, I agree' mod='ets_superspeed'}</button>
                        <button class="btn btn-default pull-left confirm-popup-no">{l s='Cancel' mod='ets_superspeed'}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<script type="text/javascript">
    var total_need_optimized_images ={$total_unoptimized_images|intval};
    initSpeedMeterNew({$start_time|floatval});
</script>
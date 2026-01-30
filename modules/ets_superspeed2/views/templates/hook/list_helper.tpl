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
var text_update_position='{l s='Successfully updated' mod='ets_superspeed'}';
</script>
<div class="panel ets_sp-panel{if isset($class)} {$class|escape:'html':'UTF-8'}{/if}">
    <div class="panel-heading">{$title nofilter}
        {if isset($totalRecords) && $totalRecords>0}<span class="badge">{$totalRecords|intval}</span>{/if}
        <span class="panel-heading-action">
            {if isset($show_add_new) && $show_add_new}            
                <a class="list-toolbar-btn add_new_link" href="{if isset($link_new)}{$link_new|escape:'html':'UTF-8'}{else}{$currentIndex|escape:'html':'UTF-8'}{/if}">
                    <span data-placement="top" data-html="true" data-original-title="{l s='Add new' mod='ets_superspeed'}" class="label-tooltip" data-toggle="tooltip" title="">
        				<i class="process-icon-new"></i> {l s='Add new' mod='ets_superspeed'}
                    </span>
                </a>            
            {/if}
            {if isset($preview_link) && $preview_link}            
                <a target="_blank" class="list-toolbar-btn" href="{$preview_link|escape:'html':'UTF-8'}">
                    <span data-placement="top" data-html="true" data-original-title="{l s='Preview ' mod='ets_superspeed'} ({$title|escape:'html':'UTF-8'})" class="label-tooltip" data-toggle="tooltip" title="">
        				<i style="margin-left: 5px;" class="icon-search-plus"></i>
                    </span>
                </a>            
            {/if}
            {if isset($link_delete_all) && $link_delete_all}
                <a class="list-toolbar-btn" href="{$link_delete_all|escape:'html':'UTF-8'}"  onclick="return confirm('{l s='If you clear all logs, you can not restore all edited fields. Do you still want to clear all logs?' mod='ets_superspeed' js=1}');">
                    <span data-placement="top" data-html="true" data-original-title="{l s='Clear all log ' mod='ets_superspeed'}" class="label-tooltip" data-toggle="tooltip" title="">
        				<i class="ets_svg_icon svg_fill_gray svg_fill_hover_white">
                            <svg class="w_14 h_14" width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg">
                                <path d="M704 736v576q0 14-9 23t-23 9h-64q-14 0-23-9t-9-23v-576q0-14 9-23t23-9h64q14 0 23 9t9 23zm256 0v576q0 14-9 23t-23 9h-64q-14 0-23-9t-9-23v-576q0-14 9-23t23-9h64q14 0 23 9t9 23zm256 0v576q0 14-9 23t-23 9h-64q-14 0-23-9t-9-23v-576q0-14 9-23t23-9h64q14 0 23 9t9 23zm128 724v-948h-896v948q0 22 7 40.5t14.5 27 10.5 8.5h832q3 0 10.5-8.5t14.5-27 7-40.5zm-672-1076h448l-48-117q-7-9-17-11h-317q-10 2-17 11zm928 32v64q0 14-9 23t-23 9h-96v948q0 83-47 143.5t-113 60.5h-832q-66 0-113-58.5t-47-141.5v-952h-96q-14 0-23-9t-9-23v-64q0-14 9-23t23-9h309l70-167q15-37 54-63t79-26h320q40 0 79 26t54 63l70 167h309q14 0 23 9t9 23z">
                            </svg>
                        </i>
                    </span>
                </a>
            {/if}
        </span>
    </div>
    {if $fields_list}
        {if $name=='file_no_caches'}
            <div class="module_system_analytics log_error">
                <label for="ETS_SPEED_ENABLE_LOG_CACHE_ERROR">
                    <input id="ETS_SPEED_ENABLE_LOG_CACHE_ERROR" value="1" name="ETS_SPEED_ENABLE_LOG_CACHE_ERROR" type="checkbox"{if Configuration::get('ETS_SPEED_ENABLE_LOG_CACHE_ERROR')} checked="checked"{/if}>
                    <span class="sp_configuration_switch">
                            <span class="sp_configuration_label on">{l s='On' mod='ets_superspeed'}</span>
                            <span class="sp_configuration_label off">{l s='Off' mod='ets_superspeed'}</span>
                        </span>
                    {l s='Save log for URLs that failed to create a cache' mod='ets_superspeed'}
                </label>
                <button class="btn btn-default btn-clear-log-cache">
                    <svg width="16" height="16" viewBox="0 0 2048 1792" xmlns="http://www.w3.org/2000/svg"><path d="M960 1408l336-384h-768l-336 384h768zm1013-1077q15 34 9.5 71.5t-30.5 65.5l-896 1024q-38 44-96 44h-768q-38 0-69.5-20.5t-47.5-54.5q-15-34-9.5-71.5t30.5-65.5l896-1024q38-44 96-44h768q38 0 69.5 20.5t47.5 54.5z"/></svg>
                    &nbsp;{l s='Clear log' mod='ets_superspeed'}</button>
            </div>
        {/if}
        {if $name =='clear_history'}
            <div class="module_system_analytics log_clear_history">
                <label for="ETS_SPEED_ENABLE_LOG_CACHE_CLEAR">
                    <input id="ETS_SPEED_ENABLE_LOG_CACHE_CLEAR" value="1" name="ETS_SPEED_ENABLE_LOG_CACHE_CLEAR" type="checkbox"{if Configuration::get('ETS_SPEED_ENABLE_LOG_CACHE_CLEAR')} checked="checked"{/if}>
                    <span class="sp_configuration_switch">
                            <span class="sp_configuration_label on">{l s='On' mod='ets_superspeed'}</span>
                            <span class="sp_configuration_label off">{l s='Off' mod='ets_superspeed'}</span>
                        </span>
                    {l s='Save log clear cache' mod='ets_superspeed'}
                </label>
                <button class="btn btn-default btn-clear-log-clear-history">
                    <svg width="16" height="16" viewBox="0 0 2048 1792" xmlns="http://www.w3.org/2000/svg"><path d="M960 1408l336-384h-768l-336 384h768zm1013-1077q15 34 9.5 71.5t-30.5 65.5l-896 1024q-38 44-96 44h-768q-38 0-69.5-20.5t-47.5-54.5q-15-34-9.5-71.5t30.5-65.5l896-1024q38-44 96-44h768q38 0 69.5 20.5t47.5 54.5z"/></svg>
                    &nbsp;{l s='Clear log' mod='ets_superspeed'}
                </button>
            </div>
        {/if}
        {if $name=='file_caches'}
            <div class="module_system_analytics cache_clear_history">
                <button class="btn btn-default btn-clear-cache-history">
                    <svg width="16" height="16" viewBox="0 0 2048 1792" xmlns="http://www.w3.org/2000/svg"><path d="M960 1408l336-384h-768l-336 384h768zm1013-1077q15 34 9.5 71.5t-30.5 65.5l-896 1024q-38 44-96 44h-768q-38 0-69.5-20.5t-47.5-54.5q-15-34-9.5-71.5t30.5-65.5l896-1024q38-44 96-44h768q38 0 69.5 20.5t47.5 54.5z"/></svg>
                    &nbsp;{l s='Clear log' mod='ets_superspeed'}
                </button>
            </div>
        {/if}
        <div class="table-responsive clearfix">


            <form method="post" action="{$currentIndex|escape:'html':'UTF-8'}">
                {if isset($bulk_action_html)}
                    {$bulk_action_html nofilter}
                {/if}
                <table class="table configuration alltab_ss list-{$name|escape:'html':'UTF-8'}">
                    <thead>
                        <tr class="nodrag nodrop">
                            {assign var ='i' value=1}
                            {foreach from=$fields_list item='field' key='index'}
                                <th class="{$index|escape:'html':'UTF-8'}{if isset($field.class)} {$field.class|escape:'html':'UTF-8'}{/if}" {if $show_action && !$actions && count($fields_list)==$i}colspan="2"{/if}>
                                    <span class="title_box">
                                        {$field.title|escape:'html':'UTF-8'}
                                        {if isset($field.sort) && $field.sort}
                                            <span class="soft">
                                            <a href="{$currentIndex|escape:'html':'UTF-8'}&sort={$index|escape:'html':'UTF-8'}&sort_type=desc{$filter_params nofilter}" {if isset($sort)&& $sort==$index && isset($sort_type) && $sort_type=='desc'} class="active"{/if}><i class="icon-caret-down"></i></a>
                                            <a href="{$currentIndex|escape:'html':'UTF-8'}&sort={$index|escape:'html':'UTF-8'}&sort_type=asc{$filter_params nofilter}" {if isset($sort)&& $sort==$index && isset($sort_type) && $sort_type=='asc'} class="active"{/if}><i class="icon-caret-up"></i></a>
                                            </span>
                                         {/if}
                                    </span>
                                </th>  
                                {assign var ='i' value=$i+1}                          
                            {/foreach}
                            {if $show_action && $actions}
                                <th class="table_action" style="text-align: right;">{l s='Action' mod='ets_superspeed'}</th>
                            {/if}
                        </tr>
                        {if $show_toolbar}
                            <tr class="nodrag nodrop filter row_hover">
                                {foreach from=$fields_list item='field' key='index'}
                                    <th class="{$index|escape:'html':'UTF-8'}{if isset($field.class)} {$field.class|escape:'html':'UTF-8'}{/if}">
                                        {if isset($field.filter) && $field.filter}
                                            {if $field.type=='text'}
                                                <input class="filter" name="{$index|escape:'html':'UTF-8'}" type="text" {if isset($field.width)}style="width: {$field.width|intval}px;"{/if} {if isset($field.active)}value="{$field.active|escape:'html':'UTF-8'}"{/if}/>
                                            {/if}
                                            {if $field.type=='select' || $field.type=='active'}
                                                <select  {if isset($field.width)}style="width: {$field.width|intval}px;"{/if}  name="{$index|escape:'html':'UTF-8'}">
                                                    <option value=""> All </option>
                                                    {if isset($field.filter_list.list) && $field.filter_list.list}
                                                        {assign var='id_option' value=$field.filter_list.id_option}
                                                        {assign var='value' value=$field.filter_list.value}
                                                        {foreach from=$field.filter_list.list item='option'}
                                                            <option {if ($field.active!=='' && $field.active==$option.$id_option) || ($field.active=='' && $index=='has_post' && $option.$id_option==1 )} selected="selected"{/if} value="{$option.$id_option|escape:'html':'UTF-8'}">{$option.$value|escape:'html':'UTF-8'}</option>
                                                        {/foreach}
                                                    {/if}
                                                </select>                                            
                                            {/if}
                                            {if $field.type=='int'}
                                                <label for="{$index|escape:'html':'UTF-8'}_min"><input type="text" placeholder="{l s='Min' mod='ets_superspeed'}" name="{$index|escape:'html':'UTF-8'}_min" value="{$field.active.min|escape:'html':'UTF-8'}" /></label>
                                                <label for="{$index|escape:'html':'UTF-8'}_max"><input type="text" placeholder="{l s='Max' mod='ets_superspeed'}" name="{$index|escape:'html':'UTF-8'}_max" value="{$field.active.max|escape:'html':'UTF-8'}" /></label>
                                            {/if}
                                            {if $field.type=='date'}
                                                <fieldset class="form-group"> 
                                                    <div class="input-group ets_sp_datepicker">
                                                        <input id="{$index|escape:'html':'UTF-8'}_min" autocomplete="off" class="form-control" name="{$index|escape:'html':'UTF-8'}_min" placeholder="{l s='From' mod='ets_superspeed'}" value="{$field.active.min|escape:'html':'UTF-8'}" type="text" autocomplete="off" />
                                                        <div class="input-group-append">
                                                            <div class="input-group-text">
                                                                <i class="ets_svg_icon svg_fill_gray">
                                                                    <svg class="w_14 h_14" width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M192 1664h288v-288h-288v288zm352 0h320v-288h-320v288zm-352-352h288v-320h-288v320zm352 0h320v-320h-320v320zm-352-384h288v-288h-288v288zm736 736h320v-288h-320v288zm-384-736h320v-288h-320v288zm768 736h288v-288h-288v288zm-384-352h320v-320h-320v320zm-352-864v-288q0-13-9.5-22.5t-22.5-9.5h-64q-13 0-22.5 9.5t-9.5 22.5v288q0 13 9.5 22.5t22.5 9.5h64q13 0 22.5-9.5t9.5-22.5zm736 864h288v-320h-288v320zm-384-384h320v-288h-320v288zm384 0h288v-288h-288v288zm32-480v-288q0-13-9.5-22.5t-22.5-9.5h-64q-13 0-22.5 9.5t-9.5 22.5v288q0 13 9.5 22.5t22.5 9.5h64q13 0 22.5-9.5t9.5-22.5zm384-64v1280q0 52-38 90t-90 38h-1408q-52 0-90-38t-38-90v-1280q0-52 38-90t90-38h128v-96q0-66 47-113t113-47h64q66 0 113 47t47 113v96h384v-96q0-66 47-113t113-47h64q66 0 113 47t47 113v96h128q52 0 90 38t38 90z"/></svg>
                                                                </i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </fieldset>
                                                <fieldset class="form-group"> 
                                                    <div class="input-group ets_sp_datepicker">
                                                        <input id="{$index|escape:'html':'UTF-8'}_max" autocomplete="off" class="form-control" name="{$index|escape:'html':'UTF-8'}_max" placeholder="{l s='To' mod='ets_superspeed'}" value="{$field.active.max|escape:'html':'UTF-8'}" type="text" autocomplete="off" />
                                                        <div class="input-group-append">
                                                            <div class="input-group-text">
                                                                <i class="ets_svg_icon svg_fill_gray">
                                                                    <svg class="w_14 h_14" width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M192 1664h288v-288h-288v288zm352 0h320v-288h-320v288zm-352-352h288v-320h-288v320zm352 0h320v-320h-320v320zm-352-384h288v-288h-288v288zm736 736h320v-288h-320v288zm-384-736h320v-288h-320v288zm768 736h288v-288h-288v288zm-384-352h320v-320h-320v320zm-352-864v-288q0-13-9.5-22.5t-22.5-9.5h-64q-13 0-22.5 9.5t-9.5 22.5v288q0 13 9.5 22.5t22.5 9.5h64q13 0 22.5-9.5t9.5-22.5zm736 864h288v-320h-288v320zm-384-384h320v-288h-320v288zm384 0h288v-288h-288v288zm32-480v-288q0-13-9.5-22.5t-22.5-9.5h-64q-13 0-22.5 9.5t-9.5 22.5v288q0 13 9.5 22.5t22.5 9.5h64q13 0 22.5-9.5t9.5-22.5zm384-64v1280q0 52-38 90t-90 38h-1408q-52 0-90-38t-38-90v-1280q0-52 38-90t90-38h128v-96q0-66 47-113t113-47h64q66 0 113 47t47 113v96h384v-96q0-66 47-113t113-47h64q66 0 113 47t47 113v96h128q52 0 90 38t38 90z"/></svg>
                                                                </i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </fieldset>
                                            {/if}
                                        {elseif $field.type=='checkbox'}
                                            <div class="md-checkbox bulk_action_select_all_etsp_manage">
                                                <label>
                                                  <input id="bulk_action_select_all" onclick="$('table').find('td input:checkbox').prop('checked', $(this).prop('checked')); ets_sp_updateBulkMenu();" value="" type="checkbox" />
                                                  <i class="md-checkbox-control"></i>
                                                </label>
                                            </div>
                                        {else}
                                           {l s=' -- ' mod='ets_superspeed'}
                                        {/if}
                                    </th>
                                {/foreach}
                                {if $show_action}
                                    <th class="actions">
                                        <span class="pull-right flex">
                                            <input type="hidden" name="post_filter" value="yes" />
                                            {if $show_reset}<a  class="btn btn-warning"  href="{$currentIndex|escape:'html':'UTF-8'}"><i class="icon-eraser"></i> {l s='Reset' mod='ets_superspeed'}</a> &nbsp;{/if}
                                            <button class="btn btn-default" name="ets_sp_submit_{$name|escape:'html':'UTF-8'}" id="ets_sp_submit_{$name|escape:'html':'UTF-8'}" type="submit">
            									<i class="icon-search"></i> {l s='Filter' mod='ets_superspeed'}
            								</button>
                                        </span>
                                    </th>
                                {/if}
                            </tr>
                        {/if}
                    </thead>
                    <tbody id="list-{$name|escape:'html':'UTF-8'}">
                        {if $field_values}
                        {foreach from=$field_values item='row'}
                            <tr {if isset($row.read) && !$row.read}class="no-read"{/if} data-id="{$row.$identifier|intval}">
                                {assign var='i' value=1}
                                {foreach from=$fields_list item='field' key='key'}                             
                                    <td class="{$key|escape:'html':'UTF-8'} {if isset($sort)&& $sort==$key && isset($sort_type) && $sort_type=='asc' && isset($field.update_position) && $field.update_position}pointer dragHandle center{/if}{if isset($field.class)} {$field.class|escape:'html':'UTF-8'}{/if}" {if $show_action && !$actions && count($fields_list)==$i}colspan="2"{/if} >
                                        {if isset($field.rating_field) && $field.rating_field}
                                            {if isset($row.$key) && $row.$key > 0}
                                                {for $i=1 to (int)$row.$key}
                                                    <div class="star star_on"></div>
                                                {/for}
                                                {if (int)$row.$key < 5}
                                                    {for $i=(int)$row.$key+1 to 5}
                                                        <div class="star"></div>
                                                    {/for}
                                                {/if}
                                            {else}
                                            
                                                {l s=' -- ' mod='ets_superspeed'}
                                            {/if}
                                        {elseif $field.type != 'active'}
                                            {if $field.type=='date'}
                                                {if !$row.$key}
                                                --
                                                {else}
                                                    {if $key!='date_from' && $key!='date_to'}
                                                        {dateFormat date=$row.$key full=1}
                                                    {else}
                                                        {dateFormat date=$row.$key full=0}
                                                    {/if}
                                                {/if}
                                            {elseif $field.type=='checkbox'}
                                                <input type="checkbox" name="{$name|escape:'html':'UTF-8'}_boxs[]" value="{$row.$identifier|escape:'html':'UTF-8'}" class="{$name|escape:'html':'UTF-8'}_boxs" />
                                            {elseif $field.type=='input_number'}
                                                {assign var='field_input' value=$field.field}
                                                <div class="qty edit_quantity" data-v-599c0dc5="">
                                                    <div class="ps-number edit-qty hover-buttons" data-{$identifier|escape:'html':'UTF-8'}="{$row.$identifier|escape:'html':'UTF-8'}">
                                                        <input class="form-control {$name|escape:'html':'UTF-8'}_{$field_input|escape:'html':'UTF-8'}" type="number" name="{$name|escape:'html':'UTF-8'}_{$field_input|escape:'html':'UTF-8'}[{$row.$identifier|escape:'html':'UTF-8'}]" value="" placeholder="0" />
                                                        <div class="ps-number-spinner d-flex">
                                                            <span class="ps-number-up"></span>
                                                            <span class="ps-number-down"></span>
                                                        </div>
                                                    </div>
                                                    <button class="check-button" disabled="disabled"><i class="fa fa-check icon-check"></i></button>
                                                </div>
                                            {else}
                                                {if isset($field.update_position) && $field.update_position}
                                                    <div class="dragGroup">
                                                    <span class="positions">
                                                {/if}
                                                {if isset($row.$key) && $row.$key!=='' && !is_array($row.$key)}
                                                    {if isset($field.strip_tag) && !$field.strip_tag}
                                                        {if isset($view_more_content) && $view_more_content}
                                                            <div class="ets_td">
                                                                <div class="span_change_product s4">
                                                                    <div class="content">
                                                                    <div class="content_info">
                                                        {/if}
                                                        {$row.$key nofilter}
                                                        {if isset($view_more_content) && $view_more_content}
                                                                    </div> 
                                                                    </div>
                                                                    <a class="btn ets_pm_viewmore" href="#" data-more="{l s='View more' mod='ets_superspeed'}" data-less="{l s='View less' mod='ets_superspeed'}"> {l s='View more' mod='ets_superspeed'} </a>
                                                                </div>
                                                            </div>
                                                        {/if}
                                                    {else}
                                                        {$row.$key|strip_tags:'UTF-8'|truncate:120:'...'|escape:'html':'UTF-8'}
                                                    {/if}
                                                {else}--{/if}
                                                {if isset($row.$key) && is_array($row.$key) && isset($row.$key.image_field) && $row.$key.image_field}
                                                    <a class="ets_sp_fancy" href="{$row.$key.img_url|escape:'html':'UTF-8'}"><img style="{if isset($row.$key.height) && $row.$key.height}max-height: {$row.$key.height|intval}px;{/if}{if isset($row.$key.width) && $row.$key.width}max-width: {$row.$key.width|intval}px;{/if}" src="{$row.$key.img_url|escape:'html':'UTF-8'}" /></a>
                                                {/if} 
                                                {if isset($field.update_position) && $field.update_position}
                                                    </div>
                                                    </span>
                                                {/if}  
                                            {/if}                                     
                                        {else}
                                            {if isset($row.$key) && $row.$key}
                                                {if (!isset($row.action_edit) || $row.action_edit)}
                                                    <a name="{$name|escape:'html':'UTF-8'}"  href="{$currentIndex|escape:'html':'UTF-8'}&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}&change_enabled=0&field={$key|escape:'html':'UTF-8'}" class="list-action field-{$key|escape:'html':'UTF-8'} list-action-enable action-enabled list-item-{$row.$identifier|escape:'html':'UTF-8'}" data-id="{$row.$identifier|escape:'html':'UTF-8'}" title="{l s='Click to Disable' mod='ets_superspeed'}">
                                                        <i class="fa fa-check"></i>
                                                    </a>
                                                {else}
                                                    <span class="list-action-enable action-enabled" title="{l s='Enabled' mod='ets_superspeed'}">
                                                        <i class="fa fa-check"></i>
                                                    </span>
                                                {/if}
                                            {else}
                                                {if (!isset($row.action_edit) || $row.action_edit)}
                                                    <a name="{$name|escape:'html':'UTF-8'}" href="{$currentIndex|escape:'html':'UTF-8'}&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}&change_enabled=1&field={$key|escape:'html':'UTF-8'}" class="list-action field-{$key|escape:'html':'UTF-8'} list-action-enable action-disabled  list-item-{$row.$identifier|escape:'html':'UTF-8'}" data-id="{$row.$identifier|escape:'html':'UTF-8'}" title="{l s='Click to Enable' mod='ets_superspeed'}">
                                                        <i class="fa fa-remove"></i>
                                                    </a>
                                                {else}
                                                    <span class="list-action-enable action-disabled" title="{l s='Disabled' mod='ets_superspeed'}">
                                                        <i class="fa fa-remove"></i>
                                                    </span>
                                                {/if}
                                            {/if} 
                                        {/if}
                                    </td>
                                    {assign var='i' value=$i+1}
                                {/foreach}
                                {if $show_action}
                                    {if $actions}  
                                        <td class="text-right">                            
                                            <div class="btn-group-action">
                                                <div class="btn-group pull-right">
                                                        {if $actions[0]=='view'}
                                                            {if isset($row.child_view_url) && $row.child_view_url}
                                                                <a class="btn btn-default link_view" href="{$row.child_view_url|escape:'html':'UTF-8'}" {if isset($view_new_tab) && $view_new_tab} target="_blank" {/if}><i class="icon-search-plus fa fa-search-plus"></i> {l s='View' mod='ets_superspeed'}</a>
                                                            {elseif !isset($row.action_edit) || $row.action_edit}
                                                                <a class="btn btn-default link_edit" href="{$currentIndex|escape:'html':'UTF-8'}&edit{$name|escape:'html':'UTF-8'}=1&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}" ><i class="icon-pencil fa fa-pencil"></i> {l s='Edit' mod='ets_superspeed'}</a>
                                                            {/if}
                                                        {/if}
                                                        {if $actions[0]=='start_massedit'}
                                                            <a class="btn btn-default" href="{$currentIndex|escape:'html':'UTF-8'}&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}&edit{$name|escape:'html':'UTF-8'}=1{if isset($row.type)}&type={$row.type|escape:'html':'UTF-8'}{/if}">
                                                                <i class="ets_svg_icon svg_fill_gray svg_fill_hover_white">
                                                                    <svg class="w_14 h_14" width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1576 927l-1328 738q-23 13-39.5 3t-16.5-36v-1472q0-26 16.5-36t39.5 3l1328 738q23 13 23 31t-23 31z"/></svg>
                                                                </i> {l s='Start Mass Edit' mod='ets_superspeed'}
                                                            </a>
                                                        {/if}
                                                        {if $actions[0]=='delete'}
                                                            <a class="btn btn-default" onclick="return confirm('{l s='Do you want to delete this item?' mod='ets_superspeed' js=1}');" href="{$currentIndex|escape:'html':'UTF-8'}&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}&del=yes"><i class="icon-trash fa fa-trash"></i> {l s='Delete' mod='ets_superspeed'}</a>
                                                        {/if}
                                                        {if $actions[0]=='restore'}
                                                            <a class="btn btn-default" href="{$currentIndex|escape:'html':'UTF-8'}&restore{$name|escape:'html':'UTF-8'}=1&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}">
                                                            <i class="ets_svg_icon svg_fill_gray svg_fill_hover_white">
                                                                <svg class="w_14 h_14" width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1664 256v448q0 26-19 45t-45 19h-448q-42 0-59-40-17-39 14-69l138-138q-148-137-349-137-104 0-198.5 40.5t-163.5 109.5-109.5 163.5-40.5 198.5 40.5 198.5 109.5 163.5 163.5 109.5 198.5 40.5q119 0 225-52t179-147q7-10 23-12 15 0 25 9l137 138q9 8 9.5 20.5t-7.5 22.5q-109 132-264 204.5t-327 72.5q-156 0-298-61t-245-164-164-245-61-298 61-298 164-245 245-164 298-61q147 0 284.5 55.5t244.5 156.5l130-129q29-31 70-14 39 17 39 59z"/></svg>
                                                            </i> {l s='Restore' mod='ets_superspeed'}</a>
                                                        {/if}
                                                        {if $actions[0]=='reply'}
                                                            <a class="btn btn-default link_edit" href="{$currentIndex|escape:'html':'UTF-8'}&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}&reply=yes"><i class="icon-reply fa fa-reply"></i> {l s='Reply' mod='ets_superspeed'}</a>
                                                        {/if}
                                                        {if $actions[0]=='dowloadpdf'}
                                                            <a class="ets_sp_downloadpdf" href="{$currentIndex|escape:'html':'UTF-8'}&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}&dowloadpdf=yes">
                                                                <i class="icon-pdf icon icon-pdf fa fa-file-pdf-o"></i>
                                                                {l s='Download pdf' mod='ets_superspeed'}
                                                            </a>
                                                        {/if}
                                                        {if $actions|count >=2 && (!isset($row.action_edit) || $row.action_edit || in_array('action',$actions) || (isset($row.action_delete) &&$row.action_delete) )}
                                                            <button data-toggle="dropdown" class="btn btn-default dropdown-toggle">
                                        						<i class="icon-caret-down"></i>&nbsp;
                                        					</button>
                                                            <ul class="dropdown-menu">
                                                                {foreach from=$actions item='action' key='key'}
                                                                    {if $key!=0}
                                                                        {if $action=='delete' && (!isset($row.view_order_url) || (isset($row.view_order_url) && !$row.view_order_url) )}
                                                                            <li><a class="btn btn-default" onclick="return confirm('{l s='Do you want to delete this item?' mod='ets_superspeed'}');" href="{$currentIndex|escape:'html':'UTF-8'}&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}&del=yes{if isset($row.type)}&type={$row.type|escape:'html':'UTF-8'}{/if}">
                                                                            <i class="ets_svg_icon svg_fill_gray svg_fill_hover_white">
                                                                                <svg class="w_14 h_14" width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M704 736v576q0 14-9 23t-23 9h-64q-14 0-23-9t-9-23v-576q0-14 9-23t23-9h64q14 0 23 9t9 23zm256 0v576q0 14-9 23t-23 9h-64q-14 0-23-9t-9-23v-576q0-14 9-23t23-9h64q14 0 23 9t9 23zm256 0v576q0 14-9 23t-23 9h-64q-14 0-23-9t-9-23v-576q0-14 9-23t23-9h64q14 0 23 9t9 23zm128 724v-948h-896v948q0 22 7 40.5t14.5 27 10.5 8.5h832q3 0 10.5-8.5t14.5-27 7-40.5zm-672-1076h448l-48-117q-7-9-17-11h-317q-10 2-17 11zm928 32v64q0 14-9 23t-23 9h-96v948q0 83-47 143.5t-113 60.5h-832q-66 0-113-58.5t-47-141.5v-952h-96q-14 0-23-9t-9-23v-64q0-14 9-23t23-9h309l70-167q15-37 54-63t79-26h320q40 0 79 26t54 63l70 167h309q14 0 23 9t9 23z"/></svg>
                                                                            </i> {l s='Delete' mod='ets_superspeed'}</a></li>
                                                                        {/if}
                                                                        {if $action =='start_massedit'}
                                                                            <li>
                                                                                <a class="btn btn-default" href="{$currentIndex|escape:'html':'UTF-8'}&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}&startmassedit=1{if isset($row.type)}&type={$row.type|escape:'html':'UTF-8'}{/if}">
                                                                                    <i class="ets_svg_icon svg_fill_gray svg_fill_hover_white">
                                                                                        <svg class="w_14 h_14" width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1576 927l-1328 738q-23 13-39.5 3t-16.5-36v-1472q0-26 16.5-36t39.5 3l1328 738q23 13 23 31t-23 31z"/></svg>
                                                                                    </i> {l s='Start Mass Edit' mod='ets_superspeed'}
                                                                                </a>
                                                                            </li>
                                                                        {/if}
                                                                        {if $action=='dowloadpdf'}
                                                                            <li>
                                                                                <a href="{$currentIndex|escape:'html':'UTF-8'}&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}&dowloadpdf=yes{if isset($row.type)}&type={$row.type|escape:'html':'UTF-8'}{/if}">
                                                                                    <i class="fa fa-pdf icon icon-pdf"></i> {l s='Download pdf' mod='ets_superspeed'}
                                                                                </a>
                                                                            </li>
                                                                        {/if}
                                                                        {if $action=='view'}
                                                                            {if isset($row.child_view_url) && $row.child_view_url}
                                                                                <li><a class="btn btn-default" href="{$row.child_view_url|escape:'html':'UTF-8'}">
                                                                                <i class="fa fa-search-plus icon-search-plus">
                                                                                </i> {l s='View' mod='ets_superspeed'}</a></li>
                                                                            {else}
                                                                                <li><a class="btn btn-default" href="{$currentIndex|escape:'html':'UTF-8'}&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}">
                                                                                <i class="fa fa-pencil icon-pencil">
                                                                                </i> {l s='Edit' mod='ets_superspeed'}</a></li>
                                                                            {/if}
                                                                        {/if}
                                                                        {if $action =='edit'}
                                                                            <li><a class="btn btn-default" href="{$currentIndex|escape:'html':'UTF-8'}&edit{$name|escape:'html':'UTF-8'}=1&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}">
                                                                            <i class="fa fa-pencil icon-pencil">
                                                                            </i> {l s='Edit' mod='ets_superspeed'}</a></li>
                                                                        {/if}
                                                                        {if $action =='restore'}
                                                                            <li><a class="btn btn-default" href="{$currentIndex|escape:'html':'UTF-8'}&restore{$name|escape:'html':'UTF-8'}=1&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}">
                                                                            <i class="ets_svg_icon svg_fill_gray svg_fill_hover_white">
                                                                                <svg class="w_14 h_14" width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1664 256v448q0 26-19 45t-45 19h-448q-42 0-59-40-17-39 14-69l138-138q-148-137-349-137-104 0-198.5 40.5t-163.5 109.5-109.5 163.5-40.5 198.5 40.5 198.5 109.5 163.5 163.5 109.5 198.5 40.5q119 0 225-52t179-147q7-10 23-12 15 0 25 9l137 138q9 8 9.5 20.5t-7.5 22.5q-109 132-264 204.5t-327 72.5q-156 0-298-61t-245-164-164-245-61-298 61-298 164-245 245-164 298-61q147 0 284.5 55.5t244.5 156.5l130-129q29-31 70-14 39 17 39 59z"/></svg>
                                                                            </i> {l s='Restore' mod='ets_superspeed'}</a></li>
                                                                        {/if}
                                                                        {if $action =='duplicate'}
                                                                            <li><a class="btn btn-default" href="{$currentIndex|escape:'html':'UTF-8'}&duplicate{$name|escape:'html':'UTF-8'}=1&{$identifier|escape:'html':'UTF-8'}={$row.$identifier|escape:'html':'UTF-8'}">
                                                                            <i class="fa fa-copy icon-copy">
                                                                            </i> {l s='Duplicate' mod='ets_superspeed'}</a></li>
                                                                        {/if}
                                                                    {/if}
                                                                {/foreach}
                                                            </ul>
                                                        {/if}
                                                </div>
                                            </div>
                                        </td>
                                    {/if}
                                {/if}
                            </tr>
                        {/foreach}  
                        {/if}  
                        {if !$field_values}
                           <tr class="no-record not_items_found"> <td colspan="100%"><p>{if isset($text_no_item) && $text_no_item}{$text_no_item|escape:'html':'UTF-8'}{else}{l s='No items found' mod='ets_superspeed'}{/if}</p></td></tr>
                        {/if}                
                    </tbody>
                </table>
                {if $name =='pmn_logmassedit' && $field_values}
                    <div id="catalog-actions" class="col order-first bulk-actions">
                        <div class="row">
                            <div class="col">
                                <div class="d-inline-block">
                                    <div class="btn-group dropdown bulk-catalog">
                                        <button id="product_bulk_menu" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown" aria-expanded="false" style="color:black;">
                                            {l s='Bulk actions' mod='ets_superspeed'}
                                            <i class="icon-caret-up"></i>
                                        </button>
                                        <div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 35px, 0px); top: 0px; left: 0px; will-change: transform;">
                                            <div class="dropdown-divider"></div>
                                            <button class="dropdown-item select" onclick="$('table').find('td input:checkbox').prop('checked', true);$('#bulk_action_select_all').prop('checked', true); ets_sp_updateBulkMenu(); return false;">
                                                <i class="ets_svg_icon svg_fill_gray svg_fill_hover_white">
                                                    <svg class="w_14 h_14" width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M813 1299l614-614q19-19 19-45t-19-45l-102-102q-19-19-45-19t-45 19l-467 467-211-211q-19-19-45-19t-45 19l-102 102q-19 19-19 45t19 45l358 358q19 19 45 19t45-19zm851-883v960q0 119-84.5 203.5t-203.5 84.5h-960q-119 0-203.5-84.5t-84.5-203.5v-960q0-119 84.5-203.5t203.5-84.5h960q119 0 203.5 84.5t84.5 203.5z"/></svg>
                                                </i>
                                                {l s='Select all' mod='ets_superspeed'}
                                            </button>
                                            <div class="dropdown-divider"></div>
                                            <button class="dropdown-item un_select" onclick="$('table').find('td input:checkbox').prop('checked', false);$('#bulk_action_select_all').prop('checked', false); ets_sp_updateBulkMenu(); return false;">
                                                <i class="ets_svg_icon svg_fill_gray svg_fill_hover_white">
                                                    <svg class="w_14 h_14" width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1312 256h-832q-66 0-113 47t-47 113v832q0 66 47 113t113 47h832q66 0 113-47t47-113v-832q0-66-47-113t-113-47zm288 160v832q0 119-84.5 203.5t-203.5 84.5h-832q-119 0-203.5-84.5t-84.5-203.5v-832q0-119 84.5-203.5t203.5-84.5h832q119 0 203.5 84.5t84.5 203.5z"/></svg>
                                                </i>
                                                {l s='Unselect all' mod='ets_superspeed'}
                                            </button>
                                            <div class="dropdown-divider"></div>
                                            <button style="display:none" name="btnRestoreSelection" class="dropdown-item action" onclick="return confirm('{l s='Do you want to restore selected item?' js=1 mod='ets_superspeed'}');">
                                                <i class="ets_svg_icon svg_fill_gray svg_fill_hover_white">
                                                    <svg class="w_14 h_14" width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M1664 256v448q0 26-19 45t-45 19h-448q-42 0-59-40-17-39 14-69l138-138q-148-137-349-137-104 0-198.5 40.5t-163.5 109.5-109.5 163.5-40.5 198.5 40.5 198.5 109.5 163.5 163.5 109.5 198.5 40.5q119 0 225-52t179-147q7-10 23-12 15 0 25 9l137 138q9 8 9.5 20.5t-7.5 22.5q-109 132-264 204.5t-327 72.5q-156 0-298-61t-245-164-164-245-61-298 61-298 164-245 245-164 298-61q147 0 284.5 55.5t244.5 156.5l130-129q29-31 70-14 39 17 39 59z">
                                                    </svg>
                                                </i>
                                                {l s='Restore selected' mod='ets_superspeed'}
                                            </button>
                                            <div class="dropdown-divider"></div>
                                            <button style="display:none" name="btnDeleteSelection" class="dropdown-item action" onclick="return confirm('{l s='Do you want to delete selected item?' js=1 mod='ets_superspeed'}');">
                                                <i class="ets_svg_icon svg_fill_gray svg_fill_hover_white">
                                                    <svg class="w_14 h_14" width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M704 736v576q0 14-9 23t-23 9h-64q-14 0-23-9t-9-23v-576q0-14 9-23t23-9h64q14 0 23 9t9 23zm256 0v576q0 14-9 23t-23 9h-64q-14 0-23-9t-9-23v-576q0-14 9-23t23-9h64q14 0 23 9t9 23zm256 0v576q0 14-9 23t-23 9h-64q-14 0-23-9t-9-23v-576q0-14 9-23t23-9h64q14 0 23 9t9 23zm128 724v-948h-896v948q0 22 7 40.5t14.5 27 10.5 8.5h832q3 0 10.5-8.5t14.5-27 7-40.5zm-672-1076h448l-48-117q-7-9-17-11h-317q-10 2-17 11zm928 32v64q0 14-9 23t-23 9h-96v948q0 83-47 143.5t-113 60.5h-832q-66 0-113-58.5t-47-141.5v-952h-96q-14 0-23-9t-9-23v-64q0-14 9-23t23-9h309l70-167q15-37 54-63t79-26h320q40 0 79 26t54 63l70 167h309q14 0 23 9t9 23z">
                                                    </svg>
                                                </i>
                                                {l s='Delete selected' mod='ets_superspeed'}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                {/if}
                {if $pagination}
                    <div class="ets_sp_paggination" style="margin-top: 20px;">
                        {$pagination nofilter}
                    </div>
                {/if}
            </form>
        </div>
    {/if}
    {if isset($link_back_to_list)}
        <div class="panel-footer">
            <a id="desc-attribute-back" class="btn btn-default btn-primary back_massive_list" href="{$link_back_to_list|escape:'html':'UTF-8'}">
        		<i class="ets-icon-svg svg_fill_gray svg_fill_hover_white">
                    <svg class="w_14 h_14" width="14" height="14" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1408 960v-128q0-26-19-45t-45-19h-502l189-189q19-19 19-45t-19-45l-91-91q-18-18-45-18t-45 18l-362 362-91 91q-18 18-18 45t18 45l91 91 362 362q18 18 45 18t45-18l91-91q18-18 18-45t-18-45l-189-189h502q26 0 45-19t19-45zm256-64q0 209-103 385.5t-279.5 279.5-385.5 103-385.5-103-279.5-279.5-103-385.5 103-385.5 279.5-279.5 385.5-103 385.5 103 279.5 279.5 103 385.5z"/></svg>
                </i> <span>{l s='Back to list' mod='ets_superspeed'}</span>
        	</a>
        </div>
    {/if}
</div>
<script type="text/javascript">
    function ets_sp_updateBulkMenu()
    {
        if($('.pmn_logmassedit_boxs:checked').length)
        {
            $('.dropdown-item.action').show();
        }
        else
            $('.dropdown-item.action').hide();
    }
</script>



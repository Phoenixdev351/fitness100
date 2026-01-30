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
{if !isset($ajax)}
<script type="text/javascript">
    var register_text = '{l s='Rehook' mod='ets_superspeed' js='1'}';
    var status_unhooked_text = '{l s='Unhooked' mod='ets_superspeed' js='1'}';
    var status_active_text ='{l s='Active' mod='ets_superspeed' js='1'}';
    var un_register_text ='{l s='Unhook' mod='ets_superspeed' js='1'}';
    var confirm_unhook = '{l s='Unhooking may cause unexpected errors with the module (if the hook is required by the module). You can rehook this module later if you need. Do you want to to continue?' mod='ets_superspeed' js='1'}';
</script>
<div class="module_system_analytics">
    <form class="defaultForm form-horizontal" action="{$link->getAdminLink('AdminSuperSpeedSystemAnalytics')|escape:'html':'UTF-8'}" method="post">
        <div id="fieldset_0" class="panel">
            <div class="ets_sp_confitab_wrap">
                <div class="panel-heading">
                    {l s='System Analytics' mod='ets_superspeed'}
                </div>
            </div>
            <ul class="tab_config_page_cache">
                <li class="confi_tab config_tab_module_performance{if $tab_current =='module_performance'} active{/if}" data-tab-id="module_performance">{l s='Module performance' mod='ets_superspeed'}</li>
                <li class="confi_tab config_tab_extra_checks{if $tab_current =='extra_checks'} active{/if}" data-tab-id="extra_checks">{l s='Extra checks' mod='ets_superspeed'}</li>
                <li class="viewmore_dropdown">
                    <span class="threedots"></span>
                </li>
            </ul>
            <label for="ETS_SPEED_RECORD_MODULE_PERFORMANCE">
                <input id="ETS_SPEED_RECORD_MODULE_PERFORMANCE" value="1" name="ETS_SPEED_RECORD_MODULE_PERFORMANCE" type="checkbox"{if $ETS_SPEED_RECORD_MODULE_PERFORMANCE} checked="checked"{/if} />
                <span class="sp_configuration_switch">
                    <span class="sp_configuration_label on">{l s='On' mod='ets_superspeed'}</span>
                    <span class="sp_configuration_label off">{l s='Off' mod='ets_superspeed'}</span>
                    </span>
                {l s='Record module performance (Should be turned off for production website)' mod='ets_superspeed'}
            </label>
            <div class="responsive tabble" >
            <table class="table table_analytics module_performance{if !$module_hooks} table_nodata{/if}">
                <thead>
                    <tr>
                        <th class="module_name">
                            <span class="title_box">
                            {l s='Module' mod='ets_superspeed'}
                            </span>
                        </th>
                        <th class="hook_name">
                            <span class="title_box">
                            {l s='Hook name' mod='ets_superspeed'}
                                <a {if isset($orderby) && $orderby=='pht.hook_name' && isset($orderway) && $orderway=='desc'}class="active"{/if} href="{$url_base_sort|escape:'html':'UTF-8'}&Orderby=pht.hook_name&Orderway=desc">
                                    <i class="icon-caret-down"></i>
                                </a>
                                <a {if isset($orderby) && $orderby=='pht.hook_name' && isset($orderway) && $orderway=='asc'}class="active"{/if} href="{$url_base_sort|escape:'html':'UTF-8'}&Orderby=pht.hook_name&Orderway=asc">
                                    <i class="icon-caret-up"></i>
                                </a>
                            </span>
                        </th>
                        <th class="page text-center">{l s='Url' mod='ets_superspeed'}</th>
                        <th class="time_run text-center">
                            <span class="title_box">
                                {l s='Execution time' mod='ets_superspeed'}
                                <a {if (isset($orderby) && $orderby=='pht.time' && isset($orderway) && $orderway=='desc') || !isset($orderby)}class="active"{/if} href="{$url_base_sort|escape:'html':'UTF-8'}&Orderby=pht.time&Orderway=desc">
                                    <i class="icon-caret-down"></i>
                                </a>
                                <a {if isset($orderby) && $orderby=='pht.time' && isset($orderway) && $orderway=='asc'}class="active"{/if} href="{$url_base_sort|escape:'html':'UTF-8'}&Orderby=pht.time&Orderway=asc">
                                    <i class="icon-caret-up"></i>
                                </a>
                            </span>
                        </th>
                        <th class="date_add">
                            <span class="title_box">
                                {l s='Date' mod='ets_superspeed'}
                                <a {if isset($orderby) && $orderby=='pht.date_add' && isset($orderway) && $orderway=='desc'}class="active"{/if} href="{$url_base_sort|escape:'html':'UTF-8'}&Orderby=pht.date_add&Orderway=desc">
                                    <i class="icon-caret-down"></i>
                                </a>
                                <a {if isset($orderby) && $orderby=='pht.date_add' && isset($orderway) && $orderway=='asc'}class="active"{/if} href="{$url_base_sort|escape:'html':'UTF-8'}&Orderby=pht.date_add&Orderway=asc">
                                    <i class="icon-caret-up"></i>
                                </a>
                            </span>
                        </th>
                        <th class="status">
                            <span class="title_box">
                                {l s='Status' mod='ets_superspeed'}
                                <a {if isset($orderby) && $orderby=='phm.id_module' && isset($orderway) && $orderway=='asc'}class="active"{/if} href="{$url_base_sort|escape:'html':'UTF-8'}&Orderby=phm.id_module&Orderway=asc">
                                    <i class="icon-caret-down"></i>
                                </a>
                                <a {if isset($orderby) && $orderby=='phm.id_module' && isset($orderway) && $orderway=='desc'}class="active"{/if} href="{$url_base_sort|escape:'html':'UTF-8'}&Orderby=phm.id_module&Orderway=desc">
                                    <i class="icon-caret-up"></i>
                                </a>
                            </span>
                        </th>
                        <th class="action">{l s='Action' mod='ets_superspeed'}</th>
                    </tr>
                    <tr class="nodrag nodrop filter">
                        <th>
                            <input class="filter" name="module_name" value="{if isset($filter.module_name)}{$filter.module_name|escape:'html':'UTF-8'}{/if}" type="text" />
                        </th>
                        <th>
                            <input class="filter" name="hook_name" value="{if isset($filter.hook_name)}{$filter.hook_name|escape:'html':'UTF-8'}{/if}" type="text" />
                        </th>
                        <th>
                            <input type="text" class="filter" name="module_page" value="{if isset($filter.module_page)}{$filter.module_page|escape:'html':'UTF-8'}{/if}" />
                        </th>
                        <th>
                            <div class="row date_range">
                                <div class="input-group">
                                    <input type="text" class="filter form-control" name="module_time_min" value="{if isset($filter.module_time_min)}{$filter.module_time_min|escape:'html':'UTF-8'}{/if}" placeholder="{l s='Min' mod='ets_superspeed'}"/>
                                </div>
                                <div class="input-group">
                                    <input type="text" class="filter form-control" name="module_time_max" value="{if isset($filter.module_time_max)}{$filter.module_time_max|escape:'html':'UTF-8'}{/if}" placeholder="{l s='Max' mod='ets_superspeed'}"/>
                                </div>
                            </div>
                        </th>
                        <th>
                            <div class="date_range row">
    							<div class="input-group">
    								<input class="filter datepicker2 date-input form-control" id="date_add_from" name="date_add_from" value="{if isset($filter.date_add_from)}{$filter.date_add_from|escape:'html':'UTF-8'}{/if}" placeholder="{l s='From' mod='ets_superspeed'}" type="text" />
    								<span class="input-group-addon">
    									<i class="icon-calendar"></i>
    								</span>
    							</div>
    							<div class="input-group ">
    								<input class="filter datepicker2 date-input form-control" id="date_add_to" name="date_add_to" value="{if isset($filter.date_add_to)}{$filter.date_add_to|escape:'html':'UTF-8'}{/if}" placeholder="{l s='To' mod='ets_superspeed'}" type="text"/>
    								<span class="input-group-addon">
    									<i class="icon-calendar"></i>
    								</span>
    							</div>
    						</div>
                        </th>
                        <th>
                            <select name="disabled">
                                <option value="" {if isset($filter.disabled) && $filter.disabled===''}selected="selected"{/if}>{l s='All' mod='ets_superspeed'}</option>
                                <option value="0" {if (isset($filter.disabled) && $filter.disabled==='0') || !isset($filter.disabled)}selected="selected"{/if}>{l s='Active' mod='ets_superspeed'}</option>
                                <option value="1" {if isset($filter.disabled) && $filter.disabled==='1'} selected="selected" {/if}>{l s='Unhooked' mod='ets_superspeed'}</option>
                            </select>
                        </th>
                        <th>
                            <button type="submit" name="submitDeleteSystemAnalytics" class="btn btn-default"><i class="icon-trash"></i> {l s='Clear all' mod='ets_superspeed'}</button>
                            <button type="submit" name="submitFilterModule" class="btn btn-default"><i class="icon-search"></i> {l s='Search' mod='ets_superspeed'}</button>
                            {if isset($filter) && $filter}
                                <button class="btn btn-warning" type="submit" name="submitResetModule" class="btn btn-default">
                                <i class="icon-eraser"></i> {l s='Reset' mod='ets_superspeed'}
                                </button>
                            {/if}
                        </th>
                    </tr>
                </thead>
                <tbody>
{/if}
                    {if $module_hooks}
                        {foreach from=$module_hooks item='module_hook'}
                             <tr>
                                <td class="module_name text-center"><img  style="width:57px;" src="{$module_hook.logo|escape:'html':'UTF-8'}" title="{$module_hook.display_name|escape:'html':'UTF-8'}"/></td>
                                <td class="hook_name">{$module_hook.hook_name|escape:'html':'UTF-8'}</td>
                                <td class="page">{$module_hook.page|escape:'html':'UTF-8'}</td>
                                <td class="time_run text-center"{if $module_hook.time>1}style="color:red;"{/if}>{$module_hook.time|floatval *1000}  {l s='ms' mod='ets_superspeed'}</td>
                                <td class="date_add">{$module_hook.date_add|escape:'html':'UTF-8'}</td>
                                <td class="page_status text-center">{if $module_hook.disabled}<span class="hook-status unhooked">{l s='Unhooked' mod='ets_superspeed'}</span>{else}<span class="hook-status active">{l s='Active' mod='ets_superspeed'}</span>{/if}</td>
                                <td class="page_action">
                                    {if $module_hook.disabled}
                                        <a class="btn btn-default register-option unregister" href="{$link->getAdminLink('AdminSuperSpeedSystemAnalytics')|escape:'html':'UTF-8'}&change_register_option=1&id_module={$module_hook.id_module|intval}&hook_name={$module_hook.hook_name|escape:'html':'UTF-8'}">{l s='Rehook' mod='ets_superspeed'}</a>
                                    {else}
                                        <a class="btn btn-default register-option register" href="{$link->getAdminLink('AdminSuperSpeedSystemAnalytics')|escape:'html':'UTF-8'}&change_register_option=0&id_module={$module_hook.id_module|intval}&hook_name={$module_hook.hook_name|escape:'html':'UTF-8'}" >{l s='Unhook' mod='ets_superspeed'}</a>
                                    {/if}
                                </td>
                            </tr>
                        {/foreach} 
                    {else}
                        <tr class="tr_nodata">
                            <td colspan="7"><p class="not-data">{l s='No data available' mod='ets_superspeed'}</p></td>
                        </tr>
                    {/if}
                    <tr class="paggination">
                        <td colspan="100%">
                            {$paggination nofilter}
                        </td>
                    </tr>
{if !isset($ajax)}
                </tbody>
            </table>
            <table class="table table_analytics extra_checks">
                <thead>
                    <tr>
                        <th>{l s='Check point' mod='ets_superspeed'}</th>
                        <th class="text-center">{l s='Current data' mod='ets_superspeed'}</th>
                        <th class="text-center">{l s='Status' mod='ets_superspeed'}</th>
                        <th class="text-left">{l s='Recommendation' mod='ets_superspeed'}</th>
                        <th class="text-center">{l s='Action' mod='ets_superspeed'}</th>
                    </tr>
                </thead>
                <tbody>
                    {if $extra_hooks}
                        {foreach from = $extra_hooks item='extra_hook'}
                            <tr class="{if $extra_hook.name}{$extra_hook.name|escape:'html':'UTF-8'}{/if}">
                                <td>{$extra_hook.check_point|escape:'html':'UTF-8'}</td>
                                <td class="number_data">
                                    {if $extra_hook.name=='media_server' || $extra_hook.name=='caching_server'}
                                        {if $extra_hook.server}
                                             {$extra_hook.server|escape:'html':'UTF-8'}
                                        {else}
                                            -
                                        {/if}
                                    {elseif isset($extra_hook.number_data)}
                                        {$extra_hook.number_data|escape:'html':'UTF-8'}
                                    {else}-{/if}</td>
                                <td class="status text-center">
                                    {if isset($extra_hook.number_data)}
                                        {if $extra_hook.number_data=='-'}
                                            <span>-</span>
                                        {elseif $extra_hook.number_data <= $extra_hook.default}
                                            <span class="status-good">{l s='Good' mod='ets_superspeed'}</span>
                                        {elseif $extra_hook.number_data > $extra_hook.bad}
                                            <span class="status-bad">{l s='Bad' mod='ets_superspeed'}</span>
                                        {else}
                                            <span class="status-reputable">{l s='Acceptable' mod='ets_superspeed'}</span>
                                        {/if}
                                    {elseif isset($extra_hook.enabled)}
                                        {if $extra_hook.enabled}
                                            <span class="status-good">{l s='Good' mod='ets_superspeed'}</span>
                                        {else}
                                            <span class="status-disabled">{l s='Not configured' mod='ets_superspeed'}</span>
                                        {/if}
                                    {/if}
                                </td>
                                <td class="text-left">{if isset($extra_hook.recommendation)}{$extra_hook.recommendation|escape:'html':'UTF-8'}{/if}</td>
                                <td class="text-right">
                                    {if $extra_hook.url_config}
                                        <a target="_blank" class="btn btn-default" href="{$extra_hook.url_config|escape:'html':'UTF-8'}">{l s='Configure' mod='ets_superspeed'}</a>
                                    {/if}
                                </td>
                            </tr>
                        {/foreach}
                    {/if}
                </tbody>
            </table>
            </div>
          </div>
     </form>
</div>
<script type="text/javascript">
    $(document).ready(function(){
       if ($(".datepicker2").length > 0) {
            var dateToday = new Date();
			$(".datepicker2").datepicker({
				dateFormat: 'yy-mm-dd',
                maxDate: dateToday,
			});
		}
    });
</script>
{/if}

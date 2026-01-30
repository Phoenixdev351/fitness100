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
                {if isset($check_point.name) && ($check_point.name=='caching_server' || $check_point.name=='media_server')}
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
                        {if $check_point.number_data === '-'}
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
        <td class="text-center" colspan="100%"><a class="btn btn-default" href="index.php?controller=AdminSuperSpeedSystemAnalytics&token={Tools::getAdminTokenLite('AdminSuperSpeedSystemAnalytics')|escape:'html':'UTF-8'}&tab_current=extra_checks">{l s='View more & configure' mod='ets_superspeed'}</a></td>
    </tr>
{/if}
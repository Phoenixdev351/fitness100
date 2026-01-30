{*
* Affiliates
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
*
* @author    FMM Modules
* @copyright © Copyright 2021 - All right reserved
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* @category  FMM Modules
* @package   affiliates
*}
{extends file="helpers/view/view.tpl"}

{block name="override_tpl"}

{if isset($rewards_by_month) AND $rewards_by_month}
<script type="text/javascript" src="{$smarty.const.__PS_BASE_URI__|escape:'htmlall':'UTF-8'}modules/affiliates/views/js/graph.js"></script>
<script type="text/javascript">
var x_axis = [];
var rscale = {$scale|escape:'htmlall':'UTF-8'};
for (var i = 0, values = new Array(12); i < 12;) values[i++] = 0;

// x-axis labels
{foreach $months as $month}
    x_axis.push("{$month|escape:'htmlall':'UTF-8'}");
{/foreach}

// y-axis labels

{foreach $rewards_by_month as $rewards}
    values[parseInt({$rewards.months|escape:'htmlall':'UTF-8'} - 1)] = parseFloat({$rewards.total|floatval|escape:'htmlall':'UTF-8'});
{/foreach}

var year                = "{l s='Monthly statisticts for the year of' mod='affiliates' js=1}: {date('Y')|escape:'htmlall':'UTF-8'}";
var x_label             = "{l s='Timeline' mod='affiliates' js=1}";
var data_names_label    = "{l s='Reward' mod='affiliates' js=1} ({$currency_prefix|escape:'htmlall':'UTF-8'})";

$(document).ready(function()
{
    var graphifyData = {
        start: 'linear',
        obj:
        {
            id              : 'stats',
            title           : year,
            width           : '100%',
            legend          : false,
            showPoints      : true,
            pointAnimation  : true,
            // width           : 775,
            legendX         : 450,
            pieSize         : 200,
            shadow          : true,
            height          : 400,
            animations      : true,
            x               : x_axis,//[2000, 2001, 2002, 2003, 2004, 2005, 2010],
            points          : values,//[17, 33, 64, 22, 87, 45, 38],
            scale           : parseInt(rscale),
            xDist           : 60,
            yDist           : 30,
            grid            : true,
            xGrid           : false,
            yGrid           : true,
            xName           : x_label,
            tooltipWidth    : 50,
            dataNames       : [data_names_label],
            design          :
            {
                tooltipColor    : '#555555',
                lineColor       : 'red',
                tooltipFontSize : '20px',
                pointColor      : 'blue',
                barColor        : '#00aff0',
                areaColor       : 'orange',
                lineStrokeColor : 'red',
                gridColor       : '#d1d1d1'
            }
        }
    };

    $('#rewards-stats').graphify(graphifyData);

    // $('#rewards-stats-wrapper').children('button').addClass('btn btn-success');
    $('#rewards-stats-wrapper-g-area').children('table').addClass('table std table-bordered ');
    $('#rewards-stats-wrapper-g-area > table > tbody tr').children('th').addClass('center');
    $('#stats-graphify-button-linear').addClass('btn button btn-default');
    $('#stats-graphify-button-area').addClass('btn button btn-default');
    $('#stats-graphify-button-bar').addClass('btn button btn-default');
    $('#stats-graphify-button-pie').addClass('btn button btn-default');
    $('#stats-graphify-button-donut').addClass('btn button btn-default');
    $('#stats-graphify-button-table').addClass('btn button btn-success');
    $('#rewards-stats-wrapper-g-area > table > tbody tr').css({
        'background':'#f5f5f5', 'height':'10%'
    });
    $('#rewards-stats-wrapper').children('button').wrapAll('<div class="btn-group"></div>')

})

$(document).on('click', function()
{
    $('.graph').css('border', '1px solid #c4c4c4');
    $('#rewards-stats-wrapper-g-area').children('table').addClass('table std table-bordered ');
    $('#rewards-stats-wrapper-g-area > table > tbody tr').children('th').addClass('center');
    $('#rewards-stats-wrapper-g-area > table > tbody tr').css({
        'background':'#f5f5f5', 'height':'10%'
    });
})
</script>

<div class="panel">
    <h4 class="panel-heading">{l s='Statistics' mod='affiliates'}</h4>
    <label class="col-lg-2">{l s='Graph Types' mod='affiliates'} : </label>
	<div id="rewards-stats"></div><div class="clearfix"></div>
</div>
<div class="clearfix"></div>

<!-- total -->
<div class="panel panel-total">
    <h4 class="panel-heading"><i class="icon-money"></i> {l s='Total' mod='affiliates'}</h4>
    <div class="table-responsive">
        <table class="table" width="100%">
            <tbody>
                <tr id="total_registrations">
                    <td class="text-right">
                        <strong>{l s='Total Reward by Registrations' mod='affiliates'}</strong>
                    </td>
                    <td class="amount text-right nowrap">
                        <span>
                            {if isset($total_reward) AND $total_reward}
                                {convertPrice price=$total_reward.total_by_reg|escape:'htmlall':'UTF-8'|floatval}
                            {else}
                                {convertPrice price=0.0}
                            {/if}
                        </span>
                    </td>
                </tr>
                <tr id="total_orders">
                    <td class="text-right">
                        <strong>{l s='Total Reward by Orders' mod='affiliates'}</strong>
                    </td>
                    <td class="amount text-right nowrap">
                        <span>
                            {if isset($total_reward) AND $total_reward}
                                {convertPrice price=$total_reward.total_by_ord|escape:'htmlall':'UTF-8'|floatval}
                            {else}
                                {convertPrice price=0.0}
                            {/if}
                        </span>
                    </td>
                </tr>
                <tr id="total_orders">
                    <td class="text-right">
                        <h4>{l s='Grand Total Reward' mod='affiliates'}</h4>
                    </td>
                    <td class="amount text-right nowrap">
                        <span>
                            {assign var='grand_total' value=($total_reward.total_by_ord|escape:'htmlall':'UTF-8'|floatval) + ($total_reward.total_by_reg|escape:'htmlall':'UTF-8'|floatval)}
                            {if isset($total_reward) AND $total_reward}
                                {convertPrice price=$grand_total|escape:'htmlall':'UTF-8'|floatval}
                            {else}
                                {convertPrice price=0.0}
                            {/if}
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<div class="clearfix"></div>
<!-- /total -->

<div class="panel">
    <h4 class="panel-heading">{l s='Referral Details' mod='affiliates'}</h4>
    {include file='./content/referral_detail.tpl'}
</div>
<div class="clearfix"></div>

<div class="panel">
    <h4 class="panel-heading">{l s='Reward Details' mod='affiliates'}</h4>
    {include file='./content/reward_detail.tpl'}
</div>
<div class="clearfix"></div>
{else}
    <div class="alert alert-warning warning">{l s='No Data' mod='affiliates'}</div>
{/if}
{/block}

{*
 * 2010-2022 Bl Modules.
 *
 * If you wish to customize this module for your needs,
 * please contact the authors first for more information.
 *
 * It's not allowed selling, reselling or other ways to share
 * this file or any other module files without author permission.
 *
 * @author    Bl Modules
 * @copyright 2010-2022 Bl Modules
 * @license
*}
<div class="panel">
    <div class="panel-heading">
        <i class="icon-bar-chart"></i> {l s='Statistics' mod='xmlfeeds'}
    </div>
    <div class="row">
        <div>
            <table border="0" width="100%" cellpadding="3" cellspacing="0">
                <tr>
                    <td>
                        {l s='Feed name:' mod='xmlfeeds'} {$feed.name|escape:'htmlall':'UTF-8'}
                    </td>
                </tr>
                <tr>
                    <td>
                        {l s='Total views:' mod='xmlfeeds'} {$feed.total_views|escape:'htmlall':'UTF-8'}
                    </td>
                </tr>
                <tr><td>&nbsp;</td></tr>
            </table>
        </div>
        {if !empty($stat)}
            <div class="blmod_clicks_list">
                <div id="blmod_clicks_title" class="blmod_clicks_row blmod_clicks_title" style="border-bottom: solid 1px #a0d0eb;">
                    <div class="blmod_clicks_ip"><a href="{$full_address_no_t|escape:'htmlall':'UTF-8'}&statistics={$statistics|escape:'htmlall':'UTF-8'}&order_name=ip_address{$order|escape:'htmlall':'UTF-8'}{$token|escape:'htmlall':'UTF-8'}">{l s='User IP address' mod='xmlfeeds'}</a></div>
                    <div class="blmod_clicks_date"><a href="{$full_address_no_t|escape:'htmlall':'UTF-8'}&statistics={$statistics|escape:'htmlall':'UTF-8'}&order_name=date{$order|escape:'htmlall':'UTF-8'}{$token|escape:'htmlall':'UTF-8'}">{l s='Date' mod='xmlfeeds'}</a></div>
                    <div class="blmod_clicks_url"><a href="{$full_address_no_t|escape:'htmlall':'UTF-8'}&statistics={$statistics|escape:'htmlall':'UTF-8'}&order_name=affiliate_name{$order|escape:'htmlall':'UTF-8'}{$token|escape:'htmlall':'UTF-8'}">{l s='Affiliate' mod='xmlfeeds'}</a></div>
                    <div class="cb"></div>
                </div>
                {foreach $stat as $s}
                    <div class="blmod_clicks_row">
                        <div class="blmod_clicks_ip">{$s.ip_address|escape:'htmlall':'UTF-8'}</div>
                        <div class="blmod_clicks_date">{$s.date|escape:'htmlall':'UTF-8'}</div>
                        <div class="blmod_clicks_url">{$s.affiliate_name|escape:'htmlall':'UTF-8'}</div>
                        <div class="cb"></div>
                    </div>
                {/foreach}
            </div>
            <div class="cb"></div><br/>
            <div class="blmod_pagination">{$pag.2}</div>
        {/if}
    </div>
</div>
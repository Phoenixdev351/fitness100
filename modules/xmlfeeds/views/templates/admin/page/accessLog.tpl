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
<div id="content" class="bootstrap content_blmod">
    <div class="bootstrap">
        <div class="panel">
            <div class="panel-heading">
                <i class="icon-cog"></i> {l s='Access log' mod='xmlfeeds'}
            </div>
            <div class="row">
                <div class="blmod_clicks_list">
                    <div id="blmod_clicks_title" class="blmod_clicks_row blmod_clicks_title">
                        <div class="blmod_clicks_ip">{l s='Action' mod='xmlfeeds'}</div>
                        <div class="blmod_clicks_date">{l s='Date' mod='xmlfeeds'}</div>
                        <div class="blmod_clicks_url">{l s='Param' mod='xmlfeeds'}</div>
                        <div class="cb"></div>
                    </div>
                    {foreach $logs as $l}
                        <div class="blmod_clicks_row">
                            <div class="blmod_clicks_ip">{$l.action|escape:'htmlall':'UTF-8'}{if !empty($l.is_cron)}_cron{/if}</div>
                            <div class="blmod_clicks_date">{$l.created_at|escape:'htmlall':'UTF-8'}</div>
                            <div class="blmod_clicks_url">
                                <span class="button-title blmod_tooltip"><span class="custom info"><em>$_GET param</em><br><code>{$l.get_param|escape:'htmlall':'UTF-8'}</code></span>GET</span>
                                <span class="button-title blmod_ml15 blmod_tooltip"><span class="custom info"><em>$argv param</em><br><code>{$l.argv_param|escape:'htmlall':'UTF-8'}</code></span>ARGV</span>
                                <span class="blmod_ml15" title="{l s='Session ID' mod='xmlfeeds'}">{$l.session_id|escape:'htmlall':'UTF-8'}</span>
                            </div>
                            <div class="cb"></div>
                        </div>
                    {/foreach}
                </div>
                <div class="clear_block"></div>
                <div style="margin-top: 5px; text-align: right;" class="bl_comments">{$limit|escape:'htmlall':'UTF-8'} {l s='rows limit' mod='xmlfeeds'}</div>
            </div>
        </div>
        <div class="clear_block"></div>
    </div>
</div>
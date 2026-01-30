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
<table border="0" width="100%" cellpadding="3" cellspacing="0">
    <tr style="display: none;">
        <td >Feed id:</td>
        <td>
            <input type="text" readonly="readonly" name="feed_id" value="{$page|escape:'htmlall':'UTF-8'}">
        </td>
    </tr>
    <tr>
        <td class="settings-column-name">{l s='Feed name' mod='xmlfeeds'}</td>
        <td>
            <input style="width: 310px;" type="text" name="name" value="{$s.name|escape:'htmlall':'UTF-8'}" required>
            {if !empty($s.feed_mode)}<img class="feed_type_id" alt="Feed type" title="Feed type" src="../modules/{$name|escape:'htmlall':'UTF-8'}/views/img/type_{$s.feed_mode|escape:'htmlall':'UTF-8'}.png" />{/if}
        </td>
    </tr>
    <tr>
        <td class="settings-column-name">{l s='Feed status' mod='xmlfeeds'}</td>
        <td>
            <label for="xmf_feed_status">
                {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='xmf_feed_status' name='status' status=$s.status}
            </label>
        </td>
    </tr>
    <tr class="only-product order-settings">
        <td class="settings-column-name">{l s='Use cron' mod='xmlfeeds'}</td>
        <td>
            <label for="use_cron">
                {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='use_cron' name='use_cron' status=$s.use_cron}
            </label>
        </td>
    </tr>
    <tr class="only-product">
        <td class="settings-column-name">{l s='Split by combination' mod='xmlfeeds'}</td>
        <td>
            <label for="split_by_combination">
                {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='split_by_combination' name='split_by_combination' status=$s.split_by_combination}
            </label>
            <div class="clear_block"></div>
            <div class="bl_comments">{l s='[Display each combination as a separate product]' mod='xmlfeeds'}</div>
        </td>
    </tr>
    <tr class="only-product">
        <td class="settings-column-name">{l s='Split feed' mod='xmlfeeds'}</td>
        <td>
            <label for="split_feed" class="with-input"class="with-input">
                {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='split_feed' name='split_feed' status=$s.split_feed}
            </label>
            <input style="width: 130px; margin-left: 14px;" placeholder="{l s='Products per feed' mod='xmlfeeds'}" type="text" name="split_feed_limit" value="{if !empty($s.split_feed_limit)}{$s.split_feed_limit|escape:'htmlall':'UTF-8'}{/if}" size="6">
            <div class="clear_block"></div>
            <div class="bl_comments">{l s='[Divide feed into few according to the amount of products]' mod='xmlfeeds'}</div>
        </td>
    </tr>
    {if empty($s.use_cron)}
        <tr>
            <td class="settings-column-name">{l s='Use cache' mod='xmlfeeds'}</td>
            <td>
                <label for="use_cache" class="with-input">
                    {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='use_cache' name='use_cache' status=$s.use_cache}
                </label>
                <input style="width: 130px; margin-left: 14px;" placeholder="{l s='Period in minutes' mod='xmlfeeds'}" type="text" name="cache_time" value="{if !empty($s.cache_time)}{$s.cache_time|escape:'htmlall':'UTF-8'}{/if}" size="6">
                {if $s.use_cache eq 1 && empty($s.cache_time)}
                    <div class="alert-small-blmod ">{l s='Please enter cache period in minutes (e.g. 180)' mod='xmlfeeds'}</div>
                {/if}
            </td>
        </tr>
    {/if}
    <tr>
        <td class="settings-column-name">{l s='Protect by IP addresses' mod='xmlfeeds'}</td>
        <td>
            <input type="text" name="protect_by_ip" value="{$s.protect_by_ip|escape:'htmlall':'UTF-8'}" autocomplete="off">
            <div class="bl_comments">{l s='[Use a comma to separate them (e.g. 11.10.1.1, 22.2.2.3)]' mod='xmlfeeds'}</div>
        </td>
    </tr>
    <tr>
        <td class="settings-column-name">{l s='Protect with password' mod='xmlfeeds'}</td>
        <td>
            <label for="use_password" class="with-input">
                {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='use_password' name='use_password' status=$s.use_password}
            </label>
            <input style="display: inline-block; width: 130px; margin-left: 14px;" placeholder="{l s='Password' mod='xmlfeeds'}" type="password" name="password" autocomplete="off" value="{if !empty($s.password)}{$s.password|escape:'htmlall':'UTF-8'}{/if}" size="6">
            {if $s.use_password eq 1 && empty($s.password)}
                <div class="alert-small-blmod">{l s='Please enter a password' mod='xmlfeeds'}</div>
            {/if}
        </td>
    </tr>
    <tr class="only-product">
        <td class="settings-column-name">{l s='Shipping country' mod='xmlfeeds'}</td>
        <td>
            <select name="shipping_country">
                <option value="0">{l s='Default' mod='xmlfeeds'}</option>
                {foreach $countries as $c}
                    <option value="{$c.id_country|escape:'htmlall':'UTF-8'}" {if $s.shipping_country == $c.id_country}selected{/if}>{$c.name|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
        </td>
    </tr>
    {if $s.feed_mode == 'vi'}
        <tr>
            <td class="settings-column-name">{l s='Vivino, bottle size' mod='xmlfeeds'}</td>
            <td>
                <select name="vivino_bottle_size" style="width: 273px; display: inline-block;">
                    <option value="0">{l s='none' mod='xmlfeeds'}</option>
                    {foreach $productFeatures as $f}
                        <option value="{$f.id_feature|escape:'htmlall':'UTF-8'}"{if $s.vivino_bottle_size eq $f.id_feature} selected{/if}>{$f.name|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </select>
                <input style="width: 75px; margin-top: -3px;" type="text" name="vivino_bottle_size_default" value="{$s.vivino_bottle_size_default|escape:'htmlall':'UTF-8'}" placeholder="{l s='Default size' mod='xmlfeeds'}" />
            </td>
        </tr>
        <tr>
            <td class="settings-column-name">{l s='Vivino, lot size' mod='xmlfeeds'}</td>
            <td>
                <select name="vivino_lot_size" style="width: 273px; display: inline-block;">
                    <option value="0">{l s='none' mod='xmlfeeds'}</option>
                    {foreach $productFeatures as $f}
                        <option value="{$f.id_feature|escape:'htmlall':'UTF-8'}"{if $s.vivino_lot_size eq $f.id_feature} selected{/if}>{$f.name|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </select>
                <input style="width: 75px; margin-top: -3px;" type="text" name="vivino_lot_size_default" value="{$s.vivino_lot_size_default|escape:'htmlall':'UTF-8'}" placeholder="{l s='Default size' mod='xmlfeeds'}" />
            </td>
        </tr>
    {/if}
    {if $s.feed_mode == 'spa'}
        <tr>
            <td class="settings-column-name">{l s='Spartoo, size' mod='xmlfeeds'}</td>
            <td>
                <select name="spartoo_size">
                    <option value="0">{l s='none' mod='xmlfeeds'}</option>
                    {foreach $productAttributes as $f}
                        <option value="{$f.id_attribute_group|escape:'htmlall':'UTF-8'}"{if $s.spartoo_size eq $f.id_attribute_group} selected{/if}>{$f.name|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </select>
            </td>
        </tr>
    {/if}
    {if $s.feed_mode == 's'}
        <tr>
            <td class="settings-column-name">{l s='Skroutz Analytics ID' mod='xmlfeeds'}</td>
            <td>
                <input type="text" name="skroutz_analytics_id" value="{$s.skroutz_analytics_id|escape:'htmlall':'UTF-8'}">
                <div class="bl_comments">{l s='[If you want to use Skroutz Analytics, please insert shop account ID]' mod='xmlfeeds'}</div>
            </td>
        </tr>
    {/if}
</table>
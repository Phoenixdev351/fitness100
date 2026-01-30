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
    <tr>
        <td class="settings-column-name">{l s='Currency' mod='xmlfeeds'}</td>
        <td>
            <select name="currency_id">
                <option value="">{l s='Default' mod='xmlfeeds'}</option>
                {foreach $currencyList as $c}
                    <option value="{$c.id|escape:'htmlall':'UTF-8'}"{if $s.currency_id == $c.id} selected{/if}>{$c.name|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
        </td>
    </tr>
    <tr>
        <td class="settings-column-name">{l s='Price with currency' mod='xmlfeeds'}</td>
        <td>
            <label for="price_with_currency">
                {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='price_with_currency' name='price_with_currency' status=$s.price_with_currency}
            </label>
        </td>
    </tr>
    <tr>
        <td class="settings-column-name">{l s='Price format' mod='xmlfeeds'}</td>
        <td>
            <select name="price_format_id">
                <option value="0"{if empty($s.price_format_id)} selected{/if}>{l s='Default' mod='xmlfeeds'}</option>
                {foreach $priceFromList as $pfId => $pf}
                    <option value="{$pfId|escape:'htmlall':'UTF-8'}"{if $s.price_format_id == $pfId} selected{/if}>{$pf|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
        </td>
    </tr>
    <tr class="only-product">
        <td class="settings-column-name">{l s='Round mode' mod='xmlfeeds'}</td>
        <td>
            <label class="blmod_mr20">
                <input type="radio" name="price_rounding_type" value="0"{if empty($s.price_rounding_type)} checked="checked"{/if}> {l s='Tool object' mod='xmlfeeds'}
            </label>
            <label class="blmod_mr20">
                <input type="radio" name="price_rounding_type" value="1"{if $s.price_rounding_type eq 1} checked="checked"{/if}> {l s='Product object' mod='xmlfeeds'}
            </label>
        </td>
    </tr>
    <tr class="only-product">
        <td class="settings-column-name">{l s='Shipping price' mod='xmlfeeds'}</td>
        <td>
            <label class="blmod_mr20">
                <input type="radio" name="shipping_price_mode" value="0"{if $s.shipping_price_mode eq 0} checked="checked"{/if}> {l s='Default carrier' mod='xmlfeeds'}
            </label>
            <label class="blmod_mr20">
                <input type="radio" name="shipping_price_mode" value="1"{if $s.shipping_price_mode eq 1} checked="checked"{/if}> {l s='According to the country' mod='xmlfeeds'}
            </label>
        </td>
    </tr>
    <tr class="only-product">
        <td class="settings-column-name">{l s='Affilaite price' mod='xmlfeeds'}</td>
        <td>
            {if !empty($prices_affiliate)}
                <table cellspacing="0" cellpadding="0" class="table blmod-table-light" id = "radio_div" style="margin-top: 0; margin-bottom: 0;">
                    <tr>
                        <th><input type="checkbox" name="checkme" class="noborder" onclick="checkDelBoxes(this.form, 'affiliate[]', this.checked)"></th>
                        <th>{l s='Name' mod='xmlfeeds'}</th>
                        <th style="width: 400px">{l s='Formula' mod='xmlfeeds'}</th>
                    </tr>
                    {foreach $prices_affiliate as $p}
                        <tr>
                            <td class="center">
                                <input type="checkbox" {if $p.affiliate_id|in_array:$s.affiliate} checked{/if} id="affiliate_{$p.affiliate_id|escape:'htmlall':'UTF-8'}" name="affiliate[]" value="{$p.affiliate_id|escape:'htmlall':'UTF-8'}" class="noborder">
                            </td>
                            <td>
                                <label style="line-height: 26px; padding-left: 0;" for="affiliate_{$p.affiliate_id|escape:'htmlall':'UTF-8'}" class="t">
                                    {$p.affiliate_name|escape:'htmlall':'UTF-8'}
                                </label>
                            </td>
                            <td>
                                <label style="line-height: 26px; padding-left: 0;" for="affiliate_{$p.affiliate_id|escape:'htmlall':'UTF-8'}" class="t">
                                    {$p.affiliate_formula|escape:'htmlall':'UTF-8'}
                                </label>
                            </td>
                        </tr>
                    {/foreach}
                </table>
                <div class="bl_comments">[{l s='Another option: add &affiliate=name at the and of the feed URL' mod='xmlfeeds'}]</div>
            {else}
                <div class="bl_comments">
                    {l s='There is no affiliate price.' mod='xmlfeeds'}<br>{l s='Please use the "' mod='xmlfeeds'}<a class="link-highlighted" target="_blank" href="{$fullAdminUrl|escape:'htmlall':'UTF-8'}&add_affiliate_price=1">Affiliate price</a>{l s='" feature if you need to create it.' mod='xmlfeeds'}
                </div>
            {/if}
        </td>
    </tr>
</table>
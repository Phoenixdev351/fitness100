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
<form action="{$postUrl|escape:'htmlall':'UTF-8'}" method="post">
    <div class="panel">
        <div class="panel-heading">
            <i class="icon-cog"></i> {l s='Create a new affiliate price' mod='xmlfeeds'}
        </div>
        <div class="row">
            <table border="0" width="100%" cellpadding="3" cellspacing="0">
                 <tr>
                    <td class="settings-column-name" style="width: 125px;">
                        {l s='Name' mod='xmlfeeds'}
                    </td>
                    <td>
                        <input style="max-width: 462px;" type="text" name="xml_name" value="" required>
                    </td>
                </tr>
                <tr>
                    <td class="al-t settings-column-name" style="width: 125px; padding-top: 6px;">
                        {l s='Price formula' mod='xmlfeeds'}
                    </td>
                    <td>
                        <input style="max-width: 462px;" type="text" name="price" value="" required>
                        <div class="bl_comments price-formula-text">[{l s='Words' mod='xmlfeeds'} "<span>base_price</span>", "<span>sale_price</span>", "<span>tax_price</span>", "<span>shipping_price</span>", "<span>price_without_discount</span>", "<span>wholesale_price</span>" {l s='will be replaced by appropriate product value. Example of a formula: sale_price - 15' mod='xmlfeeds'}]</div>
                    </td>
                </tr>
            </table>
            <input type="hidden" name="add_affiliate_action" value="1">
            <center><input type="submit" name="add_affiliate_price" value="{l s='Create new' mod='xmlfeeds'}" class="btn btn-primary blmod_mt10i" /></center>
            {if !empty($prices)}
                <table cellspacing="0" cellpadding="0" class="table blmod-table-light" id="radio_div">
                    <tbody>
                    <tr>
                        <th>{l s='Name' mod='xmlfeeds'}</th>
                        <th>{l s='Formula' mod='xmlfeeds'}</th>
                        <th style="text-align: right;">{l s='Delete' mod='xmlfeeds'}</th>
                    </tr>
                    {foreach $prices as $p}
                        <tr>
                            <td>
                                <label style="line-height: 26px; padding-left: 0;" for="affiliate_1" class="t">
                                    {$p.affiliate_name|escape:'htmlall':'UTF-8'}
                                </label>
                            </td>
                            <td>
                                <label style="line-height: 26px; padding-left: 0;" for="affiliate_1" class="t">
                                    {$p.affiliate_formula|escape:'htmlall':'UTF-8'}
                                </label>
                            </td>
                            <td>
                                <a href="{$full_address_no_t|escape:'htmlall':'UTF-8'}&add_affiliate_price=1&delete_affiliate_price={$p.affiliate_id|escape:'htmlall':'UTF-8'}{$token|escape:'htmlall':'UTF-8'}" onclick="return confirm('{l s='Are you sure you want to delete?' mod='xmlfeeds'}')">
                                    <div title="{l s='Remove' mod='xmlfeeds'}" class="search_drop_product product-list-row">
                                        <i class="icon-trash" title="{l s='Remove from list' mod='xmlfeeds'}"></i>
                                    </div>
                                </a>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            {/if}
        </div>
    </div>
</form>
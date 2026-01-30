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
<div style="margin-right: 9px;">
    <table cellspacing="0" cellpadding="0" class="table blmod-table-light" id="radio_div">
        <tr>
            <th style="width: 242px">{l s='Name' mod='xmlfeeds'}</th>
            <th>{l s='Include' mod='xmlfeeds'}</th>
            <th>{l s='Exclude' mod='xmlfeeds'}</th>
        </tr>
        {if !empty($productList)}
            {foreach $productList as $m}
                <tr>
                    <td>
                        <label style="line-height: 26px; padding-left: 0;" for="product_list_{$m.id|escape:'htmlall':'UTF-8'}" class="t">{$m.name|escape:'htmlall':'UTF-8'}
                    </td>
                    <td class="center">
                        <input type="checkbox" id="product_list_{$m.id|escape:'htmlall':'UTF-8'}" name="product_list[]"{if $m.id|in_array:$active} checked {/if}value="{$m.id|escape:'htmlall':'UTF-8'}" class="noborder">
                    </td>
                    <td class="center">
                        <input type="checkbox" id="product_list_exclude_{$m.id|escape:'htmlall':'UTF-8'}" name="product_list_exclude[]"{if $m.id|in_array:$activeExclude} checked {/if}value="{$m.id|escape:'htmlall':'UTF-8'}" class="noborder">
                    </td>
                </tr>
            {/foreach}
        {/if}
    </table>
    <div class="product_list_button" style="cursor: pointer; color: #268CCD; text-align: left; margin-top: 10px;">{l s='[Hide]' mod='xmlfeeds'}</div>
</div>
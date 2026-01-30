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
    <table cellspacing="0" cellpadding="0" class="table blmod-table-light" id = "radio_div">
        <tr>
            <th><input type="checkbox" name="checkme" class="noborder" onclick="checkDelBoxes(this.form, 'order_payment[]', this.checked)"></th>
            <th style="width: 400px">{l s='Name' mod='xmlfeeds'}</th>
        </tr>
        {foreach $paymentModules as $m}
            <tr>
                <td class="center">
                    <input type="checkbox" id="order_payment_{$m.name|escape:'htmlall':'UTF-8'}" name="order_payment[]"{if $m.name|in_array:$activeList} checked{/if} value="{$m.name|escape:'htmlall':'UTF-8'}" class="noborder">
                </td>
                <td>
                    <label style="line-height: 26px; padding-left: 0;" for="order_payment_{$m.name|escape:'htmlall':'UTF-8'}" class="t">{$m.displayName|escape:'htmlall':'UTF-8'}
                </td>
            </tr>
        {/foreach}
    </table>
    <div class="order_payment_list_button" style="cursor: pointer; color: #268CCD; text-align: left; margin-top: 10px;">{l s='[Hide]' mod='xmlfeeds'}</div>
</div>
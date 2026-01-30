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
<table border="0" width="100%" cellpadding="3" cellspacing="0" style="margin-top: -8px;">
    <tr>
        <td class="settings-column-name">{l s='Filter by status' mod='xmlfeeds'}</td>
        <td>
            <script type="text/javascript">
                $(document).ready(function() {
                    boxToggle("order_state_list");
                });
            </script>
            <label for="order_state_status">
                {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='order_state_status' name='order_state_status' status=$s.order_state_status}
            </label>
            <span class="order_state_list_button" style="cursor: pointer; color: #268CCD; margin-left: 10px;">{l s='[Show/Hide status]' mod='xmlfeeds'}</span>
            <div class="order_state_list" style="display: none; margin-top:10px;">
                {$orderStatusList}
            </div>
        </td>
    </tr>
    <tr>
        <td class="settings-column-name">{l s='Filter by payments' mod='xmlfeeds'}</td>
        <td>
            <script type="text/javascript">
                $(document).ready(function() {
                    boxToggle("order_payment_list");
                });
            </script>
            <label for="order_payments_status">
                {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='order_payments_status' name='order_payments_status' status=$s.order_payments_status}
            </label>
            <span class="order_payment_list_button" style="cursor: pointer; color: #268CCD; margin-left: 10px;">{l s='[Show/Hide payments]' mod='xmlfeeds'}</span>
            <div class="order_payment_list" style="display: none; margin-top:10px;">
                {$orderPaymentsList}
            </div>
        </td>
    </tr>
    <tr>
        <td class="settings-column-name">{l s='Filter by date' mod='xmlfeeds'}</td>
        <td>
            <select name="filter_date_type">
                {foreach $filterDateTypes as $id => $v}
                    <option value="{$id|escape:'htmlall':'UTF-8'}"{if $s.filter_date_type == $id} selected{/if}>{$v|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
            <div {if $s.filter_date_type != $FILTER_DATE_DATE_RANGE} style="display: none;" {/if}id="order-filter-date-range" class="blmod_mt10">
            <label>
                From <input style="width: 90px;" type="text" name="filter_date_from" id="filter-date-from" value="{if $s.filter_date_from}{$s.filter_date_from|escape:'htmlall':'UTF-8'}{/if}" class="datepicker-blmod">
            </label>
            <label class="blmod_ml15">
                To <input style="width: 90px;" type="text" name="filter_date_to" id="filter-date-from-to" value="{if $s.filter_date_to}{$s.filter_date_to|escape:'htmlall':'UTF-8'}{/if}" class="datepicker-blmod">
            </label>
            </div>
            <div class="blmod_cb"></div>
            <div {if $s.filter_date_type != $FILTER_DATE_CUSTOM_DAYS} style="display: none;" {/if}id="order-filter-custom-days" class="blmod_mt10">
                <input style="width: 50px;" type="text" name="filter_custom_days" value="{if $s.filter_custom_days}{$s.filter_custom_days|escape:'htmlall':'UTF-8'}{/if}"> {l s='days from today back' mod='xmlfeeds'}
            </div>
        </td>
    </tr>
</table>
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
        <td class="settings-column-name">{l s='Only enabled' mod='xmlfeeds'}</td>
        <td>
            <label for="only_enabled">
                {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='only_enabled' name='only_enabled' status=$s.only_enabled}
            </label>
        </td>
    </tr>
    <tr>
        <td class="settings-column-name">{l s='Only "available for order"' mod='xmlfeeds'}</td>
        <td>
            <label for="only_available_for_order">
                {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='only_available_for_order' name='only_available_for_order' status=$s.only_available_for_order}
            </label>
        </td>
    </tr>
    <tr>
        <td class="settings-column-name">{l s='Only "on sale"' mod='xmlfeeds'}</td>
        <td>
            <label for="only_on_sale">
                {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='only_on_sale' name='only_on_sale' status=$s.only_on_sale}
            </label>
        </td>
    </tr>
    <tr>
        <td class="settings-column-name">{l s='Include by price range' mod='xmlfeeds'}</td>
        <td>
            {l s='From' mod='xmlfeeds'} <input class="price_range_field" type="text" name="price_from" value="{$priceFrom|escape:'htmlall':'UTF-8'}">
            {l s='To' mod='xmlfeeds'} <input class="price_range_field" type="text" name="price_to" value="{$priceTo|escape:'htmlall':'UTF-8'}">
            <div class="bl_comments">{l s='[Specify price range will be active]' mod='xmlfeeds'}</div>
        </td>
    </tr>
    <tr class="filter-by-quantity">
        <td class="settings-column-name">{l s='Include by quantity' mod='xmlfeeds'}</td>
        <td>
            <label for="filter_qty_status" style="position: relative; top: -6px;">
                {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='filter_qty_status' name='filter_qty_status' status=$s.filter_qty_status}
            </label>
            <select name="filter_qty_type">
                <option value=">"{if $s.filter_qty_type eq '>'} selected{/if}>></option>
                <option value="<"{if $s.filter_qty_type eq '<'} selected{/if}><</option>
                <option value="="{if $s.filter_qty_type eq '='} selected{/if}>=</option>
            </select>
            <input style="width: 65px;" class="filter_qty_value" type="text" name="filter_qty_value" placeholder="{l s='Value' mod='xmlfeeds'}" value="{$s.filter_qty_value|escape:'htmlall':'UTF-8'}">
        </td>
    </tr>
    <tr>
        <td class="settings-column-name">{l s='Exclude by minimum order quantity' mod='xmlfeeds'}</td>
        <td>
            {l s='From' mod='xmlfeeds'} <input class="price_range_field" type="text" name="exclude_minimum_order_qty_from" value="{$s.exclude_minimum_order_qty_from|escape:'htmlall':'UTF-8'}">
            {l s='To' mod='xmlfeeds'} <input class="price_range_field" type="text" name="exclude_minimum_order_qty_to" value="{$s.exclude_minimum_order_qty_to|escape:'htmlall':'UTF-8'}">
            <div class="bl_comments">{l s='[Values are included inclusive]' mod='xmlfeeds'}</div>
        </td>
    </tr>
    <tr>
        <td class="settings-column-name">{l s='Include by visibility status' mod='xmlfeeds'}</td>
        <td>
            <select name="filter_visibility">
                <option value=""{if empty($s.filter_visibility)} selected{/if}>{l s='All' mod='xmlfeeds'}</option>
                <option value="both"{if $s.filter_visibility eq 'both'} selected{/if}>{l s='Everywhere' mod='xmlfeeds'}</option>
                <option value="catalog"{if $s.filter_visibility eq 'catalog'} selected{/if}>{l s='Catalog only' mod='xmlfeeds'}</option>
                <option value="search"{if $s.filter_visibility eq 'search'} selected{/if}>{l s='Search only' mod='xmlfeeds'}</option>
                <option value="none"{if $s.filter_visibility eq 'none'} selected{/if}>{l s='Nowhere' mod='xmlfeeds'}</option>
            </select>
        </td>
    </tr>
    <tr>
        <td class="settings-column-name">{l s='Include by manufacturers' mod='xmlfeeds'}</td>
        <td>
            <script type="text/javascript">
                $(document).ready(function(){
                    boxToggle("manufacturers_list");
                });
            </script>
            <label for="manufacturers_status">
                {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='manufacturers_status' name='manufacturers' status=$s.manufacturer}
            </label>
            <span class="manufacturers_list_button" style="cursor: pointer; color: #268CCD; margin-left: 10px;">{l s='[Show/Hide manufacturers]' mod='xmlfeeds'}</span>
            <div class="manufacturers_list" style="display: none; margin-top:10px;">{$manufacturersList}</div>
        </td>
    </tr>
    <tr>
        <td class="settings-column-name">{l s='Include by suppliers' mod='xmlfeeds'}</td>
        <td>
            <script type="text/javascript">
                $(document).ready(function() {
                    boxToggle("supplier_list");
                });
            </script>
            <label for="supplier_status">
                {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='supplier_status' name='suppliers' status=$s.supplier}
            </label>
            <span class="supplier_list_button" style="cursor: pointer; color: #268CCD; margin-left: 10px;">{l s='[Show/Hide suppliers]' mod='xmlfeeds'}</span>
            <div class="supplier_list" style="display: none; margin-top:10px;">{$supplierList}</div>
        </td>
    </tr>
    <tr>
        <td class="settings-column-name">{l s='Include by product lists' mod='xmlfeeds'}</td>
        <td>
            <script type="text/javascript">
                $(document).ready(function() {
                    boxToggle("product_list");
                });
            </script>
            <label for="product_list_status">
                {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='product_list_status' name='product_list_status' status=$s.product_list_status}
            </label>
            <span class="product_list_button" style="cursor: pointer; color: #268CCD; margin-left: 10px;">{l s='[Show/Hide product lists]' mod='xmlfeeds'}</span>
            <div class="product_list" style="display: none; margin-top:10px;">
                {$productListSettingsPage}
            </div>
        </td>
    </tr>
    <tr>
        <td class="settings-column-name">{l s='Include by categories' mod='xmlfeeds'}</td>
        <td>
            <script type="text/javascript">
                $(document).ready(function() {
                    boxToggle("categories_list");
                });
            </script>
            <label for="products_categories">
                {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='products_categories' name='categories' status=$s.categories}
            </label>
            <span class="categories_list_button" style="cursor: pointer; color: #268CCD; margin-left: 10px;">{l s='[Show/Hide categories]' mod='xmlfeeds'}</span>
            <div class="categories_list" style="display: none; margin-top: 20px;">
                <div>
                    <label class="blmod_mr20">
                        <input type="radio" name="filter_category_type" value="0" {if empty($s.filter_category_type)} checked="checked"{/if}> {l s='Filter by main category' mod='xmlfeeds'}
                    </label>
                    <label class="">
                        <input type="radio" name="filter_category_type" value="1" {if !empty($s.filter_category_type)} checked="checked"{/if}> {l s='Filter by all categories' mod='xmlfeeds'}
                    </label>
                    <div class="cb"></div>
                </div>
                {$categoriesTree}
            </div>
        </td>
    </tr>
    <tr>
        <td class="settings-column-name">{l s='Exclude by categories' mod='xmlfeeds'}</td>
        <td>
            <script type="text/javascript">
                $(document).ready(function() {
                    boxToggle("categories_without_list");
                });
            </script>
            <label for="products_categories_without">
                {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='products_categories_without' name='categories_without' status=$s.categories_without}
            </label>
            <span class="categories_without_list_button" style="cursor: pointer; color: #268CCD; margin-left: 10px;">{l s='[Show/Hide categories]' mod='xmlfeeds'}</span>
            <div class="categories_without_list" style="display: none; margin-top: 20px;">
                <div>
                    <label class="blmod_mr20">
                        <input type="radio" name="filter_category_without_type" value="0" {if empty($s.filter_category_without_type)} checked="checked"{/if}> {l s='Filter by main category' mod='xmlfeeds'}
                    </label>
                    <label class="">
                        <input type="radio" name="filter_category_without_type" value="1" {if !empty($s.filter_category_without_type)} checked="checked"{/if}> {l s='Filter by all categories' mod='xmlfeeds'}
                    </label>
                    <div class="cb"></div>
                </div>
                {$categoriesTreeL}
            </div>
        </td>
    </tr>
    <tr>
        <td class="settings-column-name">{l s='Include by discount status' mod='xmlfeeds'}</td>
        <td>
            <label class="blmod_mr20">
                <input type="radio" name="filter_discount" value="0"{if $s.filter_discount eq 0} checked="checked"{/if}> {l s='All' mod='xmlfeeds'}
            </label>
            <label class="blmod_mr20">
                <input type="radio" name="filter_discount" value="1"{if $s.filter_discount eq 1} checked="checked"{/if}> {l s='With discount' mod='xmlfeeds'}
            </label>
            <label>
                <input type="radio" name="filter_discount" value="2"{if $s.filter_discount eq 2} checked="checked"{/if}> {l s='Without discount' mod='xmlfeeds'}
            </label>
        </td>
    </tr>
    <tr>
        <td class="settings-column-name">{l s='Include by attributes' mod='xmlfeeds'}</td>
        <td>
            <script type="text/javascript">
                $(document).ready(function() {
                    boxToggle("only_with_attributes");
                });
            </script>
            <label for="only_with_attributes">
                {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='only_with_attributes' name='only_with_attributes_status' status=$s.only_with_attributes_status}
            </label>
            <span class="only_with_attributes_button" style="cursor: pointer; color: #268CCD; margin-left: 10px;">{l s='[Show/Hide attributes]' mod='xmlfeeds'}</span>
            <div class="only_with_attributes" style="display: none; margin-top: 20px;">
                {$filterAttributes}
            </div>
        </td>
    </tr>
    <tr>
        <td class="settings-column-name">{l s='Exclude by attributes' mod='xmlfeeds'}</td>
        <td>
            <script type="text/javascript">
                $(document).ready(function() {
                    boxToggle("only_without_attributes");
                });
            </script>
            <label for="only_without_attributes">
                {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='only_without_attributes' name='only_without_attributes_status' status=$s.only_without_attributes_status}
            </label>
            <span class="only_without_attributes_button" style="cursor: pointer; color: #268CCD; margin-left: 10px;">{l s='[Show/Hide attributes]' mod='xmlfeeds'}</span>
            <div class="only_without_attributes" style="display: none; margin-top: 20px;">
                {$filterWithoutAttributes}
            </div>
        </td>
    </tr>
    <tr>
        <td class="settings-column-name">{l s='Include by image status' mod='xmlfeeds'}</td>
        <td>
            <label class="blmod_mr20">
                <input type="radio" name="filter_image" value="0"{if $s.filter_image eq 0} checked="checked"{/if}> {l s='All' mod='xmlfeeds'}
            </label>
            <label class="blmod_mr20">
                <input type="radio" name="filter_image" value="1"{if $s.filter_image eq 1} checked="checked"{/if}> {l s='With images' mod='xmlfeeds'}
            </label>
            <label>
                <input type="radio" name="filter_image" value="2"{if $s.filter_image eq 2} checked="checked"{/if}> {l s='Without images' mod='xmlfeeds'}
            </label>
        </td>
    </tr>
    <tr>
        <td class="settings-column-name">{l s='Exclude by empty value' mod='xmlfeeds'}</td>
        <td>
            <label class="attribute-list">
                <input type="checkbox" name="filter_exclude_empty_params[]" value="reference"{if 'reference'|in_array:$s.filter_exclude_empty_params} checked="checked"{/if}> Reference
            </label>
            <label class="attribute-list">
                <input type="checkbox" name="filter_exclude_empty_params[]" value="ean13"{if 'ean13'|in_array:$s.filter_exclude_empty_params} checked="checked"{/if}> EAN-13
            </label>
            <label class="attribute-list">
                <input type="checkbox" name="filter_exclude_empty_params[]" value="isbn"{if 'isbn'|in_array:$s.filter_exclude_empty_params} checked="checked"{/if}> ISBN
            </label>
            <label class="attribute-list">
                <input type="checkbox" name="filter_exclude_empty_params[]" value="upc"{if 'upc'|in_array:$s.filter_exclude_empty_params} checked="checked"{/if}> UPC
            </label>
        </td>
    </tr>
</table>
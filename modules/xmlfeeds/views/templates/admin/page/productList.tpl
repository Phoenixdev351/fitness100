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
        <form action="{$postUrl|escape:'htmlall':'UTF-8'}" method="post">
            <div class="panel">
                <div class="panel-heading">
                    <i class="icon-cog"></i> {l s='Product list' mod='xmlfeeds'}
                </div>
                <div class="row">
                    {assign var="totalProducts" value="0"}
                    {if empty($productListId)}
                        <input style="width: 300px;" type="text" name="product_list_name" placeholder="{l s='Product list name' mod='xmlfeeds'}" />
                        <input style="margin-left: 10px;" type="submit" name="add_product_list" value="{l s='Create new list' mod='xmlfeeds'}" class="btn btn-secondary">
                        <div class="cb"><br></div>
                        <hr>
                    {/if}
                    <select id="product_list_menu" style="width: 300px; float: left;" name="product_list_id">
                        <option value="0" disabled {if empty($productListId)} selected{/if}>{l s='Select product list' mod='xmlfeeds'}</option>
                        {foreach $productListGroup as $p}
                            <option value="{$p.id|escape:'htmlall':'UTF-8'}"{if $p['id'] == $productListId} selected{assign var="totalProducts" value="{$p.total_products|escape:'htmlall':'UTF-8'}"}{/if}>
                                {$p.name|escape:'htmlall':'UTF-8'} ({$p.total_products|escape:'htmlall':'UTF-8'})
                            </option>
                        {/foreach}
                    </select>
                    <input id="product_list_select" style="display: none;" type="submit" name="select_product_list" value="Select" class="button">
                    {if !empty($productListId)}
                        <a href="{$full_address_no_t|escape:'htmlall':'UTF-8'}&product_list_page=1&delete_product_list={$productListId|escape:'htmlall':'UTF-8'}{$token|escape:'htmlall':'UTF-8'}" onclick="return confirm('{l s='Are you sure you want to delete?' mod='xmlfeeds'}')">
                            <span class="delete-button-link" style="float: right">{l s='Delete' mod='xmlfeeds'}</span>
                            <div class="cb"><br></div>
                        </a>
                    {/if}
                    <div class="cb"><br></div>
                    {if !empty($productListId)}
                        <div id="product-list-total-selected" class="blmod_mt10" style="font-size: 13px;">
                            {l s='Total products in the list:' mod='xmlfeeds'} <span>{$totalProducts|escape:'htmlall':'UTF-8'}</span>
                        </div>
                    {/if}
                </div>
            </div>
            {if !empty($productListId)}
                <div class="panel">
                    <div class="panel-heading">
                        <i class="icon-cart-arrow-down"></i> {l s='Search by product name or ID' mod='xmlfeeds'}
                    </div>
                    <div class="row">
                        <div>
                            <div id="search_mask"></div>
                            <div style="float: left; width: 300px; z-index: 101; position: relative;">
                                <input id="search_form_id" autocomplete="off" type="text" class="search_form" name="product" value="" size="50" placeholder="{l s='Search, enter product name or id' mod='xmlfeeds'}"/>
                            </div>
                            <div class="autocomplite_clear">{l s='[Clear]' mod='xmlfeeds'}</div>
                            <div class="cb"></div>
                            <div id="search_result"></div>
                            <div class="search_types" style="margin-bottom: 10px;">
                                <label for="search_name" class="blmod_mr15">
                                    <input id="search_name" type="radio" name="search_type" value="search_name" checked="checked"> <span>{l s='Search by name' mod='xmlfeeds'}</span>
                                </label>
                                <label for="search_id">
                                    <input id="search_id" type="radio" name="search_type" value="search_id"> <span>{l s='Search by id' mod='xmlfeeds'} </span>
                                </label>
                                <div class="cb"></div>
                            </div>
                            <div class="cb"></div>
                            <ul class="show_with_products">
                                {if !empty($productList.products)}
                                    {foreach $productList.products as $p}
                                        <li class="search_p_list" id="search_p-{$p.id_product|escape:'htmlall':'UTF-8'}">
                                            <div title="{l s='Remove' mod='xmlfeeds'}" class="search_drop_product product-list-row" id="search_drop-{$p.id_product|escape:'htmlall':'UTF-8'}">
                                                <i class="icon-trash" title="{l s='Remove from list' mod='xmlfeeds'}"></i>
                                            </div>
                                            <div style="float: left;" class="product-list-row">
                                                <div style="float: left; width: 30px; margin-right: 5px;">
                                                    {if !empty($p.image)}
                                                        <img style="width: 29px;" src="{$p.image|escape:'htmlall':'UTF-8'}" alt="{l s='cover' mod='xmlfeeds'}"/>
                                                    {/if}
                                                </div>
                                                <div style="float: left; width: 300px;" class="search_p_name">
                                                    {$p.name|escape:'htmlall':'UTF-8'}<br/>
                                                    <span class="search_small_text">#{$p.id_product|escape:'htmlall':'UTF-8'}{$p.cat_name|escape:'htmlall':'UTF-8'}</span>
                                                </div>
                                            </div>
                                            <div class="blmod_cb"></div>
                                        </li>
                                    {/foreach}
                                {/if}
                            </ul>
                            <input class="product_hidden" type="hidden" name="product_hidden" value=",{$productList.productIdList|escape:'htmlall':'UTF-8'}," />
                            <input style="float: right; margin-top: 10px;" type="submit" name="update_product_list" value="{l s='Update list' mod='xmlfeeds'}" class="btn btn-primary">
                            <div class="cb"></div>
                        </div>
                    </div>
                </div>
                <div class="panel">
                    <div class="panel-heading">
                        <i class="icon-folder-open"></i> {l s='Search by product category' mod='xmlfeeds'}
                    </div>
                    <div class="row">
                        <div class="">
                            <select style="width: 300px; float: left;" name="product_list_category_id">
                                <option value="0" disabled {if empty($productListCategoryId)} selected{/if}>{l s='Products by category' mod='xmlfeeds'}</option>
                                {foreach $productListCategories as $c}
                                    <option value="{$c.id_category|escape:'htmlall':'UTF-8'}"{if $productListCategoryId == $c.id_category} selected{/if}>
                                        {$c.name|escape:'htmlall':'UTF-8'} ({if !empty($totalProductsInCategory[$c.id_category])}{$totalProductsInCategory[$c.id_category]|escape:'htmlall':'UTF-8'}{else}0{/if})
                                    </option>
                                {/foreach}
                            </select>
                            <input style="float: left; margin-left: 10px;" type="submit" name="load_product_category" value="{l s='Load products' mod='xmlfeeds'}" class="btn btn-secondary">
                            <div class="cb"><br></div>
                        </div>
                        {if !empty($productListCategoryId)}
                            <table class="table table-clean">
                                <thead>
                                    <tr class="cnodrag nodrop">
                                        <th></th>
                                        <th>{l s='ID' mod='xmlfeeds'}</th>
                                        <th>{l s='Image' mod='xmlfeeds'}</th>
                                        <th>{l s='Name' mod='xmlfeeds'}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {if !empty($productListByCategoryId)}
                                        {foreach $productListByCategoryId as $p}
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="products_by_category[]" value="{$p.id_product|escape:'htmlall':'UTF-8'}"{if !empty($p.list_id)} checked{/if}/>
                                                </td>
                                                <td>{$p.id_product|escape:'htmlall':'UTF-8'}</td>
                                                <td>
                                                    {if !empty($p.image)}
                                                        <img style="width: 25px;" src="{$p.image|escape:'htmlall':'UTF-8'}" alt="{l s='cover' mod='xmlfeeds'}"/>
                                                    {/if}
                                                </td>
                                                <td>{$p.name|escape:'htmlall':'UTF-8'}</td>
                                            </tr>
                                        {/foreach}
                                    {/if}
                                </tbody>
                            </table>
                            {if !empty($productListByCategoryId)}
                                <input style="float: right; margin-top: 15px;" type="submit" name="update_product_list" value="{l s='Update list' mod='xmlfeeds'}" class="btn btn-primary">
                                <div class="cb"><br></div>
                            {else}
                                {l s='No products found' mod='xmlfeeds'}
                            {/if}
                        {/if}
                    </div>
                </div>
            {/if}
        </form>
    </div>
</div>





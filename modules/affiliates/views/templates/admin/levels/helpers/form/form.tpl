{*
* Affiliates
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
*
* @author    FMM Modules
* @copyright © Copyright 2021 - All right reserved
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* @category  FMM Modules
* @package   affiliates
*}
{extends file="helpers/form/form.tpl"}

{block name="input"}

{if $input.name == 'categories'}
<div class="col-lg-10 rcg_max_height categories_fld">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>
                    <span class="title_box">
                        {l s='ID' mod='affiliates'}
                    </span>
                </th>
                <th>
                    <span class="title_box">
                        {l s='Name' mod='affiliates'}
                    </span>
                </th>
                <th>
                    <span class="title_box">
                        {l s='Value' mod='affiliates'}
                    </span>
                </th>
            </tr>
        </thead>
        <tbody>
            {if !isset($categories) || empty($categories)}
            <tr>
                <td>{l s='No categories found.' mod='affiliates'}</td>
            </tr>
            {else}
            {foreach from=$categories item=category}
                <tr class="{if isset($category.value) && $category.value > 0}fill_flag{/if}">
                <td>
                    {$category.id_category|escape:'htmlall':'UTF-8'}
                </td>
                <td>
                    {$category.name|escape:'htmlall':'UTF-8'}
                </td>
                <td>
                    <div class="input-group">
                        <input type="text" name="category[{$category.id_category|escape:'htmlall':'UTF-8'}]" value="{if isset($category.value) && $category.value > 0}{$category.value|escape:'htmlall':'UTF-8'}{/if}" />
                    </div>
                </td>
                </tr>
            {/foreach}
            {/if}
        </tbody>
    </table>
    <p class="help-block"><b>*</b> {l s='Only filled values will be considered activated, leave empty to disable category from reward.' mod='affiliates'}</p>
</div>
{elseif $input.name == 'products'}
<div class="products_fld row col-lg-10{if $ps_17 <= 0} ps_16_specific{/if}">
    <div class="col-lg-8 placeholder_holder row">
        <input type="text" placeholder="Example: Blue XL shirt" onkeyup="getRelProducts(this);" />
        <p class="help-block"><b>*</b> {l s='You must select products and fill its relative values.' mod='affiliates'}</p>
        <div id="rel_holder"></div>
        <div id="rel_holder_temp">
            <ul>
                {if (!empty($products))}
                {foreach from=$products item=product}
                    <li id="row_{$product->id|escape:'htmlall':'UTF-8'}" class="media">
                        <div class="media-left">
                            <img src="{$link->getImageLink($product->link_rewrite, $product->id_image, 'home_default')|escape:'htmlall':'UTF-8'}" class="media-object image">
                            <span class="label">{$product->name|escape:'htmlall':'UTF-8'}&nbsp;(ID:{$product->id|escape:'htmlall':'UTF-8'})</span>
                        </div>
                        <div class="media-body media-middle">
                            <input type="text" placeholder="1.234" value="{$product->value|escape:'htmlall':'UTF-8'}" name="related_products[{$product->id|escape:'htmlall':'UTF-8'}]">
                            <i onclick="relDropThis(this);" class="material-icons delete">clear</i>
                        </div>
                    </li>
                {/foreach}
                {/if}
            </ul>
        </div>
    </div>
</div>
{else}
{$smarty.block.parent}
{/if}
<script>
var mod_url = "{$action_url}";
</script>
{/block}
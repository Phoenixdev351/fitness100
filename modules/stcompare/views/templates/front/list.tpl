{*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
{extends file='page.tpl'}

{block name="page_content"}
    <h1 class="page_heading">{l s='Product comparison' d='Shop.Theme.Transformer'}</h1>
    {if isset($stcompare_products) && count($stcompare_products)}
    <div class="stcompare_table">
      <table class="table table-bordered table-striped text-2">
        <tbody>
            <tr>
                <th scope="row"><a href="javascript:;" class="stcompare_remove_all"><i class="fto-cancel-3"></i>{l s='Remove All' d='Shop.Theme.Transformer'}</a></th>
                {foreach $stcompare_products as $product}
                    <td class="stcompare_td_{$product.id_product}">
                        <a href="javascript:;" title="{l s='Remove' d='Shop.Theme.Transformer'}" class="remove_compare_product" data-id-product="{$product.id_product}"><i class="fto-cancel-3"></i>{l s='Remove' d='Shop.Theme.Transformer'}</a>
                    </td>
                {/foreach}
            </tr>
            {if $stcompare_items&1}
            <tr>
                <th scope="row"></th>
                {foreach $stcompare_products as $product}
                    <td class="stcompare_td_{$product.id_product}">
                        <a class="product_image" href="{$product.url}" title="{$product.name}"><img src="{$product.cover.bySize.home_default.url}" {if $sttheme.retina && isset($product.cover.bySize.home_default_2x.url)} srcset="{$product.cover.bySize.home_default_2x.url} 2x" {/if} width="{$product.cover.bySize.home_default.width}" height="{$product.cover.bySize.home_default.height}" alt="{if !empty($product.cover.legend)}{$product.cover.legend}{else}{$product.name}{/if}" /></a>
                    </td>
                {/foreach}
            </tr>
            {/if}
            {if $stcompare_items&2}
            <tr>
                <th scope="row">{l s='Name' d='Shop.Theme.Transformer'}</th>
                {foreach $stcompare_products as $product}
                    <td class="stcompare_td_{$product.id_product}">
                        <h3 class="s_title_block"><a href="{$product.url}" title="{$product.name}">{$product.name}</a></h3>
                    </td>
                {/foreach}
            </tr>
            {/if}
            {if $stcompare_items&4}
            <tr>
                <th scope="row">{l s='Price' d='Shop.Theme.Transformer'}</th>
                {foreach $stcompare_products as $product}
                    <td class="stcompare_td_{$product.id_product}">
                        <span class="price">{$product.price}</span>
                        {if $product.has_discount}
                            <span class="regular-price">{$product.regular_price}</span>
                            {if $product.discount_type === 'percentage'}
                              <span class="discount-percentage">{$product.discount_percentage}</span>
                            {/if}
                        {/if}
                    </td>
                {/foreach}
            </tr>
            {/if}
            {if $stcompare_items&8}
            <tr>
                <th scope="row">{l s='Rating' d='Shop.Theme.Transformer'}</th>
                {foreach $stcompare_products as $product}
                    <td class="stcompare_td_{$product.id_product}">
                        {if isset($product.stproductcomments) && $product.stproductcomments && !$product.stproductcomments.pro_posi}
                            {include file='catalog/_partials/miniatures/rating-box.tpl'}
                        {/if}
                        {hook h='displayProductListReviews' product=$product}
                    </td>
                {/foreach}
            </tr>
            {/if}
            {if $stcompare_items&16}
            <tr>
                <th scope="row">{l s='Short description' d='Shop.Theme.Transformer'}</th>
                {foreach $stcompare_products as $product}
                    <td class="stcompare_td_{$product.id_product}">
                        {$product.description_short nofilter}
                    </td>
                {/foreach}
            </tr>
            {/if}
            {if $stcompare_items&32}
            <tr>
                <th scope="row">{l s='Stock' d='Shop.Theme.Transformer'}</th>
                {foreach $stcompare_products as $product}
                    <td class="stcompare_td_{$product.id_product}">
                        {if $product.show_availability && $product.availability_message}{$product.availability_message}{/if}
                    </td>
                {/foreach}
            </tr>
            {/if}
            {if $stcompare_items&64}
            <tr>
                <th scope="row">{l s='Color' d='Shop.Theme.Transformer'}</th>
                {foreach $stcompare_products as $product}
                    <td class="stcompare_td_{$product.id_product}">
                        {include file='catalog/_partials/variant-links.tpl' variants=$product.main_variants}
                    </td>
                {/foreach}
            </tr>
            {/if}

            {if $stcompare_ordered_features}
                {foreach $stcompare_ordered_features as $feature}
                    <tr>
                        <th scope="row">
                            {$feature.name}
                        </th>
                        {foreach $stcompare_products as $product}
                            {assign var='product_id' value=$product.id_product}
                            {assign var='feature_id' value=$feature.id_feature}
                            {if isset($stcompare_product_features[$product_id])}
                                {assign var='tab' value=$stcompare_product_features[$product_id]}
                                <td class="stcompare_td_{$product.id_product}">{if (isset($tab[$feature_id]))}{$tab[$feature_id]}{/if}</td>
                            {else}
                                <td class="stcompare_td_{$product.id_product}"></td>
                            {/if}
                        {/foreach}
                    </tr>
                {/foreach}
            {else}
                <tr>
                    <th scope="row"></th>
                    <td colspan="{$stcompare_products|@count}" class="text-center">{l s='No features to compare' d='Shop.Theme.Transformer'}</td>
                </tr>
            {/if}
            {if $stcompare_items&128}
            <tr>
                <th scope="row"></th>
                {foreach $stcompare_products as $product}
                    <td class="stcompare_td_{$product.id_product}">
                        {if $sttheme.display_add_to_cart!=3 && !$sttheme.is_catalog && $product.add_to_cart_url && ($product.quantity>0 || $product.allow_oosp)}
                          {include file='catalog/_partials/miniatures/btn-add-to-cart.tpl' classname="btn btn-default"}
                        {else}
                          {include file='catalog/_partials/miniatures/btn-view-more.tpl' classname="btn btn-default"}
                        {/if}
                    </td>
                {/foreach}
            </tr>
            {/if}
        </tbody>
      </table>
    </div>
        <article class="alert alert-warning stcompare_no_products d-none" role="alert" data-alert="warning">
          {l s='There are no products selected for comparison.' d='Shop.Theme.Transformer'}
        </article>
    {else}
        <article class="alert alert-warning stcompare_no_products" role="alert" data-alert="warning">
          {l s='There are no products selected for comparison.' d='Shop.Theme.Transformer'}
        </article>
    {/if}
{/block}
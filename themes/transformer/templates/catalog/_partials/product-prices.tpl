{**
 * 2007-2016 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2016 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}
{if $product.show_price}
  <div class="product-prices">{*important refresh*}
    
    {block name='product_countdown'}
      {if isset($countdown_active) && $countdown_active}
        {if $product.show_price && !$sttheme.is_catalog && $product.has_discount}
          {if ($smarty.now|date_format:'%Y-%m-%d %H:%M:%S' >= $product.specific_prices.from && $smarty.now|date_format:'%Y-%m-%d %H:%M:%S' < $product.specific_prices.to)}
          <div class="countdown_outer_box">
            <div class="countdown_heading">{l s='Limited time offer' d='Shop.Theme.Transformer'}:</div>
            <div class="countdown_box">
              <i class="fto-clock"></i><span class="countdown_pro c_countdown_timer" data-countdown="{$product.specific_prices.to|date_format:'%Y/%m/%d %H:%M:%S'}" data-gmdate="{gmdate('Y/m/d H:i:s',strtotime($product.specific_prices.to))}" data-id-product="{$product.id|intval}"></span>
            </div>
          </div>
          {elseif ($product.specific_prices.to == '0000-00-00 00:00:00') && ($product.specific_prices.from == '0000-00-00 00:00:00') && $countdown_title_aw_display}
            <div class="countdown_outer_box countdown_pro_perm" data-id-product="{$product.id|intval}">
              <div class="countdown_box">
                <i class="fto-clock"></i><span>{l s='Limited special offer' d='Shop.Theme.Transformer'}</span>
              </div>
            </div>
          {/if}
        {/if}
      {/if}
    {/block}
    {block name='product_price'}
      <div
        class="product-price"
        {if $sttheme.google_rich_snippets}
        itemprop="offers"
        itemscope
        itemtype="https://schema.org/Offer"
        {/if}
      >
        {if $sttheme.google_rich_snippets}<link itemprop="availability" href="{if isset($product.seo_availability)}{$product.seo_availability}{else}https://schema.org/{if $product.availability=='unavailable'}OutOfStock{else}InStock{/if}{/if}" content="{if $product.availability=='unavailable'}OutOfStock{else}InStock{/if}" />{/if}
        {if $sttheme.display_pro_condition && $product.condition && $sttheme.google_rich_snippets}<link itemprop="itemCondition" href="{$product.condition.schema_url}"/>{/if}
        {if $sttheme.google_rich_snippets}
        <meta itemprop="priceCurrency" content="{$currency.iso_code}">
        <meta itemprop="url" content="{$product.url}">
        {if $product.has_discount && $product.specific_prices.to && $product.specific_prices.to != '0000-00-00 00:00:00'}<meta itemprop="priceValidUntil" content="{$product.specific_prices.to|date_format:'%Y-%m-%d'}">{/if}
        {/if}

        {hook h='displayProductPriceBlock' product=$product type="before_price"}
        <div class="current-price">
          <span class="price" {if $sttheme.google_rich_snippets} itemprop="price" content="{if isset($product.rounded_display_price)}{$product.rounded_display_price}{else}{$product.price_amount}{/if}" {/if}>{$product.price}</span>
          {if isset($configuration.taxes_enabled) && !$configuration.taxes_enabled}
            <span class="tax_label">{l s='No tax' d='Shop.Theme.Catalog'}</span>
          {elseif $configuration.display_taxes_label}
            <span class="tax_label">{$product.labels.tax_short}</span>
          {/if}
          {block name='product_discount'}
            {if $product.has_discount}
                {hook h='displayProductPriceBlock' product=$product type="old_price"}
                <span class="regular-price">{$product.regular_price}</span>
            {/if}
          {/block}
          {if $product.has_discount}
          {if 0 && !$sttheme.hide_discount}
            {if $product.discount_type === 'percentage'}
              <span class="discount discount-percentage">{$product.discount_percentage}</span>
            {else}
              <span class="discount discount-amount">-{$product.discount_to_display}</span>
            {/if}
          {/if}
          {/if}
          {if isset($product.extraContent)}
          {foreach $product.extraContent as $extra}
            {if $extra.moduleName=='ststickers'}
                {include file='catalog/_partials/miniatures/sticker.tpl' stickers=$extra.content sticker_position=array(13) is_from_product_page=1 sticker_quantity=$product.quantity sticker_allow_oosp=$product.allow_oosp sticker_quantity_all_versions=$product.quantity_all_versions sticker_stock_text=$product.availability_message}
            {/if}
          {/foreach}
          {/if}
        </div>

        {block name='product_unit_price'}
          {if $displayUnitPrice}
            <div class="product-unit-price sub">{l s='(%unit_price%)' d='Shop.Theme.Catalog' sprintf=['%unit_price%' => $product.unit_price_full]}</div>
          {/if}
        {/block}
      </div>
    {/block}

    {block name='product_without_taxes'}
      {if $priceDisplay == 2}
        <div class="product-without-taxes">{l s='%price% tax excl.' d='Shop.Theme.Catalog' sprintf=['%price%' => $product.price_tax_exc]}</div>
      {/if}
    {/block}

    {block name='product_pack_price'}
      {if $displayPackPrice}
        <div class="product-pack-price"><span>{l s='Instead of %price%' d='Shop.Theme.Catalog' sprintf=['%price%' => $noPackPrice]}</span></div>
      {/if}
    {/block}

    {block name='product_ecotax'}
      {if $product.ecotax.amount > 0}
        <div class="price-ecotax">{l s='Including %amount% for ecotax' d='Shop.Theme.Catalog' sprintf=['%amount%' => $product.ecotax.value]}
          {if $product.has_discount}
            {l s='(not impacted by the discount)' d='Shop.Theme.Catalog'}
          {/if}
        </div>
      {/if}
    {/block}

    {hook h='displayProductPriceBlock' product=$product type="weight" hook_origin='product_sheet'}

    <div class="tax-shipping-delivery-label">
      {hook h='displayProductPriceBlock' product=$product type="price"}
      {hook h='displayProductPriceBlock' product=$product type="after_price"}
      {if isset($product.additional_delivery_times)}
      {if $product.additional_delivery_times == 1}
        {if $product.delivery_information}
          <span class="delivery-information">{$product.delivery_information}</span>
        {/if}
      {elseif $product.additional_delivery_times == 2}
        {if $product.quantity > 0}
          <span class="delivery-information">{$product.delivery_in_stock}</span>
        {* Out of stock message should not be displayed if customer can't order the product. *}
        {elseif $product.quantity <= 0 && $product.add_to_cart_url}
          <span class="delivery-information">{$product.delivery_out_stock}</span>
        {/if}
      {/if}
      {/if}
    </div>
  </div>
{/if}

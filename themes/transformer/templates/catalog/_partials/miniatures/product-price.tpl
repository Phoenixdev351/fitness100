{if (!isset($st_display_price) || $st_display_price) && $product.show_price}
  <div class="product-price-and-shipping pad_b6" {if ($sttheme.google_rich_snippets==2 || (isset($from_product_page) && $from_product_page)) && (!isset($no_google_rich_snippets) || !$no_google_rich_snippets)} itemprop="offers" itemscope itemtype="https://schema.org/Offer" {/if}>
    {if ($sttheme.google_rich_snippets==2 || (isset($from_product_page) && $from_product_page)) && (!isset($no_google_rich_snippets) || !$no_google_rich_snippets)}<meta itemprop="priceCurrency" content="{$sttheme.currency_iso_code}">{/if}

    {hook h='displayProductPriceBlock' product=$product type="before_price"}

    <span {if ($sttheme.google_rich_snippets==2 || (isset($from_product_page) && $from_product_page)) && (!isset($no_google_rich_snippets) || !$no_google_rich_snippets)} itemprop="price" content="{$product.price_amount}" {/if} class="price {if $product.has_discount} st_discounted_price {/if}" aria-label="{l s='Price' d='Shop.Theme.Catalog'}">{$product.price}</span>
    {if $configuration.display_taxes_label}
        <span class="tax_label">{$product.labels.tax_short}</span>
    {/if}
    
    {if $product.has_discount}
      {hook h='displayProductPriceBlock' product=$product type="old_price"}

      <span class="regular-price" aria-label="{l s='Regular price' d='Shop.Theme.Catalog'}">{$product.regular_price}</span>
    {/if}
    {hook h='displayProductPriceBlock' product=$product type="price"}
    {hook h='displayProductPriceBlock' product=$product type="after_price"}
    
    {block name='product_flags_price'}
        {include file='catalog/_partials/miniatures/sticker.tpl' stickers=$ststickers_temp sticker_position=array(13) sticker_quantity=$product.quantity sticker_allow_oosp=$product.allow_oosp sticker_quantity_all_versions=$product.quantity_all_versions sticker_stock_text=$product.availability_message}
    {/block}

    {hook h='displayProductPriceBlock' product=$product type='unit_price'}

    {hook h='displayProductPriceBlock' product=$product type='weight'}
  </div>
{/if}
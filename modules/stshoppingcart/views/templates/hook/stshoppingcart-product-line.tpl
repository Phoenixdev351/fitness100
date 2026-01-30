<div class="flex_container flex_start">
    {if (isset($product.cover.bySize.cart_default.url) && $product.cover.bySize.cart_default.url) || isset($urls.no_picture_image)}
    <picture>
    {if isset($stwebp) && isset($stwebp.cart_default) && $stwebp.cart_default && isset($product.cover.bySize.cart_default.url) && $product.cover.bySize.cart_default.url}
    <!--[if IE 9]><video style="display: none;"><![endif]-->
      <source srcset="{$product.cover.bySize.cart_default.url|regex_replace:'/\.jpg$/':'.webp'}"
        title="{$product.cover.legend}"
        type="image/webp"
        >
    <!--[if IE 9]></video><![endif]-->
    {/if}
    <img class="small_cart_product_image" src="{if isset($product.cover.bySize.cart_default.url) && $product.cover.bySize.cart_default.url}{$product.cover.bySize.cart_default.url}{elseif isset($urls.no_picture_image)}{$urls.no_picture_image.bySize.cart_default.url}{/if}" width="{$product.cover.bySize.cart_default.width}" height="{$product.cover.bySize.cart_default.height}" alt="{$product.cover.legend}" title="{$product.cover.legend}" itemprop="image">
    </picture>
    {/if}
    <div class="small_cart_info flex_child">
        <div class="flex_container flex_start mar_b4">
            <span class="product-quantity mar_r4">{$product.quantity}x</span>
            <a href="{$product.url}" rel="nofollow" title="{$product.name}" class="product-name mar_r4 flex_child">{$product.name|truncate:30:'...'}</a>
            <div class="price mar_r4">{if isset($product.is_gift) && $product.is_gift}{l s='Gift' d='Shop.Theme.Checkout'}{else}{$product.price}{/if}</div>
            {if !isset($product.is_gift) || !$product.is_gift}
            <a  class="ajax_remove_button"
                rel="nofollow"
                href="{$product.remove_from_cart_url}"
                data-link-action="remove-from-cart"
                title="{l s="Remove" d="Shop.Theme.Actions"}"
            >
                <i class="fto-cancel"></i>
            </a>
            {/if}
        </div>
        <div class="flex_container flex_start">
        {assign var='pro_quantity_input' value=Configuration::get('STSN_PRO_QUANTITY_INPUT')}
        {if ($pro_quantity_input==2 || $pro_quantity_input==3 || (isset($steasybuilder) && $steasybuilder.is_editing)) && (!isset($product.is_gift) || !$product.is_gift)}
        <div class="qty_wrap mar_r4 {if $pro_quantity_input!=2 && $pro_quantity_input!=3} display_none {/if}">
            <input
                class="cart_quantity cart_quantity_{$product.id_product} {if $product.quantity>=$product.stock_quantity} hits_the_max_limit{/if} {if $product.quantity<=$product.minimal_quantity} hits_the_min_limit{/if}"
                type="text"
                value="{$product.quantity}"
                name="cart_quantity"
              data-down-url="{$product.down_quantity_url}"
              data-up-url="{$product.up_quantity_url}"
              data-update-url="{$product.update_quantity_url}"
              data-product-id="{$product.id_product}"
                data-minimal-quantity="{$product.minimal_quantity}"
                data-quantity="{$product.stock_quantity}"
                data-id-product-attribute="{$product.id_product_attribute}"
                data-id-customization="{$product.id_customization}"
                data-allow-oosp="{if $product.add_to_cart_url && $product.allow_oosp}1{else}0{/if}"
              />
        </div>
        {/if}
        {if count($product.attributes)}
        <div class="flex_child">
        {foreach from=$product.attributes item="property_value" key="property"}
          <div class="small_cart_attr_attr">
              <span class="small_cart_attr_k">{$property}:</span><span>{$property_value}</span>
          </div>
        {/foreach}
        </div>
        {/if}
        </div>
    </div>
</div>
{if is_array($product.customizations) && $product.customizations|count}
    <div class="customizations">
        <ul class="base_list_line">
            {foreach from=$product.customizations item="customization"}
                <li class="line_item">
                    <ul>
                        {foreach from=$customization.fields item="field"}
                            <li>
                                <span class="mar_r6 font-weight-bold">{$field.label}</span>
                                {if $field.type == 'text'}
                                    <span>{$field.text nofilter}</span>
                                {elseif $field.type == 'image'}
                                    <img src="{$field.image.small.url}" alt="{$field.label}" />
                                {/if}
                            </li>
                        {/foreach}
                    </ul>
                </li>
            {/foreach}
        </ul>
    </div>
{/if}

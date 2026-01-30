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
<script>
	{literal}
		skroutz_analytics('ecommerce', 'addOrder', JSON.stringify({
			order_id: '{/literal}{$order->id|escape:'htmlall':'UTF-8'}{literal}',
			revenue: '{/literal}{$order->total_products_wt|escape:'htmlall':'UTF-8' + $order->total_shipping_tax_incl|escape:'htmlall':'UTF-8'}{literal}',
			shipping: '{/literal}{$order->total_shipping_tax_incl|escape:'htmlall':'UTF-8'}{literal}',
			tax: '{/literal}{$taxamt = $order->total_paid_tax_incl|escape:'htmlall':'UTF-8' - $order->total_paid_tax_excl|escape:'htmlall':'UTF-8'}{$taxamt|escape:'htmlall':'UTF-8'}{literal}'
		}));
	{/literal}
    {foreach from=$order_products item=product}
        {literal}
            skroutz_analytics('ecommerce', 'addItem', JSON.stringify({
                order_id: '{/literal}{$order->id|escape:'htmlall':'UTF-8'}{literal}',
                product_id: '{/literal}{$product.id_product|escape:'htmlall':'UTF-8'}{$product.product_attribute_id|escape:'htmlall':'UTF-8'}{literal}',
                name: '{/literal}{$product.product_name|escape:'htmlall':'UTF-8'}{literal}',
                price: '{/literal}{$product.product_price_wt|escape:'htmlall':'UTF-8'}{literal}',
                quantity:'{/literal}{$product.product_quantity|escape:'htmlall':'UTF-8'}{literal}'
            }));
        {/literal}
    {/foreach}
</script>
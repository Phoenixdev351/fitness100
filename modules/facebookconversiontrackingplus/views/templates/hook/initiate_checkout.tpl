{*
 * Facebook Conversion Pixel Tracking Plus
 *
 * NOTICE OF LICENSE
 *
 * @author    Pol Rué
 * @copyright Smart Modules 2014
 * @license   One time purchase Licence (You can modify or resell the product but just one time per licence)
 * @version
 * @category Marketing & Advertising
 * Registered Trademark & Property of smart-modules.com
 *
 * **************************************************
 * *     Facebook Conversion Trackcing Pixel Plus    *
 * *          http://www.smart-modules.com           *
 * *                     V 2.3.3                     *
 * **************************************************
 *
*}

{literal}
    <!-- Initiate Checkout Pixel Call -->
    <script type="text/javascript">
    {/literal}
    var items = {$pcart nofilter}; 
    {literal}
        fctp_initiateCheckout(10);

        function fctp_initiateCheckout(max_tries) {
            if (typeof jQuery == 'undefined' || typeof fbq != 'function') {
                setTimeout(function() {fctp_initiateCheckout(max_tries-1)},500);
            } else {
                $(document).ready(function() {
                {/literal}
                {if $fbp_custom_checkout == 1}
                    {literal}
                    trackInitiateCheckout(items, items.length);
                    {/literal}
                {elseif $entity == 'order'}
                    {if $is_17}
                        {if $ic_mode == 1}
                            trackInitiateCheckout(items, '{if isset($cart_qties)}{$cart_qties|intval}{else}items.length{/if}');
                        {/if}
                    {else}
                        {literal}
                            if ($(".cart_navigation a.standard-checkout").length > 0) {
                                // Was .cart_navigation a.standard-checkout
                                $(document).on('mousedown', ".cart_navigation a.standard-checkout", function(e) {
                                trackInitiateCheckout(items, '{/literal}{if isset($cart_qties)}{$cart_qties|intval}{else}items.length{/if}{literal}');
                                });
                            } else if ($(".cart_navigation a").length > 0) {
                                // Can't find .standard-checkout class try to catch the event
                                $(document).on('mousedown', ".cart_navigation a", function(e) {
                                trackInitiateCheckout(items, '{/literal}{if isset($cart_qties)}{$cart_qties|intval}{else}items.length{/if}{literal}');
                                });
                            }
                        {/literal}
                    {/if}
                {elseif $entity == 'cart' && $ic_mode == 2}
                    {literal}
                        if ($('.checkout a').length > 0) {
                            $('.checkout a').click(function(e) {
                                trackInitiateCheckout(items, items.length);
                            });
                        }
                    {/literal}
                {elseif $entity == 'order-opc'}
                    {literal}
                        if ($('.step-num').length > 0) {
                            trackInitiateCheckout(items, items.length);
                        } else if ($('.checkoutstep.step2').length > 0) {
                            $(document).on('mousedown', '.checkoutstep.step2', function() {
                                trackInitiateCheckout(items, items.length);
                            });
                        } else {
                            trackInitiateCheckout(items, items.length);
                        }
                    {/literal}
                {/if}
            });
        }
    }
    {literal}
        function trackInitiateCheckout(items, num_items) {
            if (isNaN(parseInt(num_items))) {
                num_items = items.length;
            }
            {/literal}{if isset($fb_event_checkout_page)}
            var eid = '{$fb_event_checkout_page|escape:'htmlall':'UTF-8'}';
            fireInitiateCheckout(items, num_items, eid);
            {else}
            {literal}
            jQuery.ajax({
                url: '{/literal}{$ajax_events_url|escape:'htmlall':'UTF-8'}{literal}',
                type: 'POST',
                cache: false,
                data: {
                    event: 'InitiateCheckout',
                    id_cart: {/literal}{$cart->id|intval}{literal},
                    rand: Math.floor((Math.random() * 100000) + 1)
                }
            })
                .done(function(data) {
                    if (data.return == 'ok') {
                        fireInitiateCheckout(items, num_items, getCookieValue('event_id'));
                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown) {
                    console.log(
                        'Conversion could not be sent, contact module developer to check the issue');
                });
            {/literal}
            {/if}
            {literal}
        }
        function fireInitiateCheckout(items, num_items, eid) {
            fbq('track', 'InitiateCheckout', {
                content_ids: items,
                content_type: 'product',
                num_items : num_items,{/literal}
                value: {if $initiate_checkout_value > 0}{$initiate_checkout_value|floatval}{else}{if isset($pcart_value)}{$pcart_value|floatval}{else}1{/if}{/if},
                {if isset($pcart_value)}currency: '{$pcart_currency|escape:'htmlall':'UTF-8'}'{/if},{literal}
                content_category: 'Checkout',
                contents : {/literal}{$pcart_contents nofilter}{literal},
                {/literal}
                {if isset($fpf_id)}
                product_catalog_id :  '{$fpf_id|escape:'htmlall':'UTF-8'}',
                {/if}
                {literal}
            }, {eventID: eid});
        }
        </script>
    {/literal}
    <!-- End Initiate Checkout Pixel Call -->

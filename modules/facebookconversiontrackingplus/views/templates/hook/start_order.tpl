{*
 * Facebook Conversion Pixel Tracking Plus
 *
 * NOTICE OF LICENSE
 *
 * @author    Pol Rué
 * @copyright Smart Modules 2014
 * @license   One time purchase Licence (You can modify or resell the product but just one time per licence)
 * @version 2.0.7
 * @category Marketing & Advertising
 * Registered Trademark & Property of Smart-Modules.pro
 *
 * **************************************************
 * *     Facebook Conversion Trackcing Pixel Plus    *
 * *          http://www.smart-modules.pro           *
 * *                     V 2.0.7                     *
 * **************************************************
 *
*}
    {literal}
    <!-- Initiate Checkout Pixel Call -->
    <script type="text/javascript">
    {/literal}
    var items = [{$pcart nofilter}]; {* Can't escape as it's a json variable*}
    {literal}
    fctp_initiateCheckout(10);
    function fctp_initiateCheckout(max_tries) {
        if (typeof jQuery == 'undefined' || typeof fbq != 'function') {
            setTimeout(function() {fctp_initiateCheckout(max_tries-1)},500);
        } else {
            $(document).ready(function() {
            {/literal}
            {if $entity == 'order'}
                {if $is_17}
                    {if $ic_mode == 1}
                        trackInitiateCheckout(items, '{if isset($cart_qties)}{$cart_qties|intval}{else}items.length{/if}');
                     {/if}
                {else}
                {literal}
                    if ($(".cart_navigation a.standard-checkout").length > 0) { // Was .cart_navigation a.standard-checkout
                        $(".cart_navigation a.standard-checkout").click(function(e) {
                            trackInitiateCheckout(items, '{/literal}{if isset($cart_qties)}{$cart_qties|intval}{else}items.length{/if}{literal}');
                        });
                    } else if ($(".cart_navigation a").length > 0) { // Can't find .standard-checkout class try to catch the event
                        $(".cart_navigation a").click(function(e) {
                            trackInitiateCheckout(items, '{/literal}{if isset($cart_qties)}{$cart_qties|intval}{else}items.length{/if}{literal}');
                        });
                    }
                {/literal}
                {/if}
            {/if}
            {if $entity == 'cart' && $ic_mode == 2}{literal}
                if ($('.checkout a').length > 0) {
                    $('.checkout a').click(function(e) {
                        trackInitiateCheckout(items, items.length);
                    });
                }
            {/literal}
            {/if}
            {if $entity == 'order-opc'}{literal}
                if ($('.step-num').length > 0) {
                    trackInitiateCheckout(items, items.length);
                } else if($('.checkoutstep.step2').length > 0) {
                    $(document).on('mousedown', '.checkoutstep.step2', function() {
                        trackInitiateCheckout(items, items.length);
                    });
                } else if ($('#onepagecheckoutps').length > 0) {
                    trackInitiateCheckout(items, items.length);
                } else {
                    trackInitiateCheckout(items, items.length);
                }
            {/literal}
            {/if}
            {if $entity == 'supercheckout'}{literal}
                trackInitiateCheckout(items, items.length);
            {/literal}
            {/if}
            });
        }
    }
    {literal}
    function trackInitiateCheckout(items, num_items) {
        console.log('Tracking Initiate Checkout');
        if (isNaN(parseInt(num_items))) {
            num_items = items.length;
        }
        fbq('track', 'InitiateCheckout', {
            content_ids : items,
            content_type : 'product',
            num_items : num_items,{/literal}
            value: {if $initiate_checkout_value == ''}{if isset($pcart_value)}{$pcart_value|floatval}{/if}{else}{$initiate_checkout_value|floatval}{/if},
            {if isset($pcart_value)}currency: '{$pcart_currency|escape:'htmlall':'UTF-8'}'{/if},{literal}
            content_name : '{/literal}{l s='Initiate Checkout' mod='facebookconversiontrackingplus' js=1}{literal}',
            content_category : '{/literal}{l s='Checkout' mod='facebookconversiontrackingplus' js=1}{literal}'
        });
    }
    </script>
    {/literal}
    <!-- End Initiate Checkout Pixel Call -->
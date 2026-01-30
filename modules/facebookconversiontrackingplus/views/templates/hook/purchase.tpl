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
<!-- Facebook Register Checkout Pixel -->
{literal}
    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function(event) {
            var fctp_cookie_control = {/literal}{$fctp_cookie_control|intval}{literal}
            trackPurchase(10);

            function trackPurchase(tries) {
                if (typeof fbq != 'undefined' && typeof jQuery != 'undefined') {
                {/literal}
                {if isset($ordervars.aurl) && $ordervars.aurl != ''}
                    jQuery.ajax({
                            url: '{$ordervars.aurl|escape:'htmlall':'UTF-8'}',
                            type: 'POST',
                            cache: false,
                            data: {
                                id_order : '{$ordervars.id_order|intval}',
                                id_customer : '{$ordervars.id_customer|intval}',
                                event: 'Purchase',
                                fctp_token: '{$purchase_token|escape:'htmlall':'UTF-8'}',
                                rand: Math.floor((Math.random() * 100000) + 1)
                            }
                        })
                        .done(function(data) {
                            if (data.return == 'ok') {
                                firePurchase();
                            }
                        })
                        .fail(function(jqXHR, textStatus, errorThrown) {
                            console.log(
                                'Conversion could not be sent, contact module developer to check the issue');
                        });
                {else}
                    firePurchase();
                {/if}
                {literal}
                } else {
                    if (tries > 0) {
                        setTimeout(function() { trackPurchase(tries - 1) }, 500);
                    }
                }
            }

            function firePurchase() {
                if (!fctp_cookie_control || (parseInt(getCookie('purchaseSent')) != parseInt({/literal}{$ordervars.id_order|intval}{literal}))) {
                fbq('track', 'Purchase', {
                    'value':'{/literal}{$ordervars.ordervalue|floatval}{literal}',
                    'currency':'{/literal}{$ordervars.currency|escape:'htmlall':'UTF-8'}{literal}',
                    'order_id':'{/literal}{$ordervars.id_order|intval}{literal}',
                    'num_items':'{/literal}{$ordervars.product_quantity|intval}{literal}',{/literal}
                    {if $dynamic_ads && isset($pcart_contents)}
                        'contents' : {$pcart_contents nofilter},
                        'content_type': 'product',
                        'content_ids' : {$ordervars.product_list nofilter}
                        {if isset($fpf_id)}
                            ,product_catalog_id :  '{$fpf_id|escape:'htmlall':'UTF-8'}'
                        {/if}
                    {/if}
                    {literal}
                    }{/literal}{if isset($fb_event_purchase_page)}{literal},{eventID: '{/literal}{$fb_event_purchase_page|escape:'htmlall':'UTF-8'}'}{/if}{literal});
                    if (fctp_cookie_control) {
                        //setCookie('purchaseSent', {/literal}{$ordervars.id_order|intval}{literal}, 1);
                    }
                } else {
                    console.log('Pixel Plus: User tries to send a duplicate Purchase Event, aborting...');
                }
            }

            function setCookie(name, value, hours) {
                var expires = "";
                if (hours) {
                    var date = new Date();
                    date.setTime(date.getTime() + (hours * 60 * 60 * 1000));
                    expires = "; expires=" + date.toUTCString();
                }
                document.cookie = name + "=" + (value || "") + expires + "; path=/";
            }

            function getCookie(name) {
                var nameEQ = name + "=";
                var ca = document.cookie.split(';');
                for (var i = 0; i < ca.length; i++) {
                    var c = ca[i];
                    while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                    if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
                }
                return null;
            }
            });
        </script>
    {/literal}
    <!-- END Facebook Register Checkout Pixel -->
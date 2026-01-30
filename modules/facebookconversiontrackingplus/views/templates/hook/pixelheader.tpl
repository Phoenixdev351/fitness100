{*
 * Facebook Conversion Pixel Tracking Plus
 *
 * NOTICE OF LICENSE
 *
 * @author    Pol Rué
 * @copyright Smart Modules 2014
 * @license   One time purchase Licence (You can modify or resell the product but just one time per licence)
 * @version 2.3.3
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
<!-- Enable Facebook Pixels -->
<script>
    var pp_price_precision = {if isset($price_precision)}{$price_precision|intval}{else}2{/if};
    var event_time = {$event_time|escape:'htmlall':'UTF-8'};
    var local_time = new Date().getTime();
    var consentStatus = false;
    /* TODO Implement the generation on each event */
    function generateEventId(eventName, round) {
        //return window.event_id_gen;
        round = (typeof round !== 'undefined') ? round : 10;
        return '{$id_customer_or_guest|intval}' + '.' + eventName + '.' + generateEventTime(round);
    }

    function getRandomString(length) {
        var randomChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var result = '';
        for (var i = 0; i < length; i++) {
            result += randomChars.charAt(Math.floor(Math.random() * randomChars.length));
        }
        return result;
    }

    function generateEventTime(round) {
        return Math.floor(((new Date().getTime() - local_time) / 1000 + event_time) / round) * round;
    }
    {literal}
        facebookpixelinit(20);

        function facebookpixelinit(tries) {
            if (typeof fbq == 'undefined') {
                !function(f,b,e,v,n,t,s){if (f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if (!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
                {/literal}
                {foreach from=$fctpid item="pixel_id" name="pixelforeach"}
                    {if !$pixel_consent}
                        fbq('consent', 'revoke');
                    {/if}
                    {literal}
                        fbq('init', '{/literal}{$pixel_id|strip|escape:'htmlall':'UTF-8'}', {$user_data nofilter}{literal});
                    {/literal}
                {/foreach}
                {literal}
                    /* Code to avoid multiple pixels call */
                    /* Used to make it compatible with onepagecheckout */
                    if (typeof window.fbq_pageview == 'undefined') {
                        //console.log('Header initialized');
                        fbq('track', 'PageView', {}, {eventID: '{/literal}{$pageiew_event_id|escape:'htmlall':'UTF-8'}{literal}'});
                        window.fbq_pageview = 1;
                    }
                } else if (typeof fbq == 'function' && typeof window.fbq_pageview == 'undefined') {
                {/literal}
                {foreach from=$fctpid item="pixel_id" name="pixelforeach"}
                    {literal}
                       fbq('init', '{/literal}{$pixel_id|strip|escape:'htmlall':'UTF-8'}',{$user_data nofilter}{literal});
                    {/literal}
                {/foreach}
                {literal}
                    fbq('track', 'PageView', {}, {eventID: '{/literal}{$pageiew_event_id|escape:'htmlall':'UTF-8'}{literal}'});
                } else {
                    if (tries > 0) {
                        setTimeout(function() { facebookpixelinit(tries - 1); }, 200);
                    } else {
                        console.log('Failed to load the Facebook Pixel');
                    }
                }
            }
        {/literal}


        {if !$cookie_reload}
            document.addEventListener('DOMContentLoaded', function() {
            $(document).on('click mousedown', '{$cookie_check_button|escape:'htmlall':'UTF-8'}', function() {
            setTimeout(function() {
                if (!consentStatus) {
                    jQuery.ajax({
                            url: '{$ajax_events_url|escape:'htmlall':'UTF-8'}',
                            type: 'POST',
                            cache: false,
                            data: {
                                cookieConsent: true,
                                token : '{$cookie_token|escape:'htmlall':'UTF-8'}',
                            }
                        })
                        .done(function(data) {
                            consentStatus = true;
                            if (data.return == 'ok') {
                                fbq('consent', 'grant');
                                console.log('Pixel consent granted');
                            } else {
                                console.log('Pixel consent not granted');
                            }
                        })
                        .fail(function(jqXHR, textStatus, errorThrown) {
                            console.log('Pixel Plus: Cookie consent could not be validated');
                        });
                }
            }, 1500);
            });
            });
        {/if}
        // Get cookie by name
        const getCookieValue = (name) => (
            document.cookie.match('(^|;)\\s*' + name + '\\s*=\\s*([^;]+)')?.pop() || ''
        )
    </script>
    <!-- End Enable Facebook Pixels -->
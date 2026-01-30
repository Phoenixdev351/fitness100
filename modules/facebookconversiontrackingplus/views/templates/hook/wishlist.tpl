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
<!-- Registration Pixel Call -->
{literal}
    <script type="text/javascript">
        fctp_addToWishlist(10);
        var fctp_wishlist_act = true;

        function fctp_addToWishlist(max_tries) {
            if (typeof jQuery == 'undefined' || typeof fbq != 'function') {
                setTimeout(function() { fctp_addToWishlist(max_tries - 1); }, 250);
            } else {
                jQuery(document).ready(function() {
                        var wishlist_custom_button = '.btn-iqitwishlist-add';
                        if ($(wishlist_custom_button).length > 0) {
                            $(wishlist_custom_button).click(function(e) {
                                window.fctp_wishlist_act = false;
                                var id_product_wish = $(this).attr('data-id-product');
                                var id_product_attribute_wish = $(this).attr('data-id-product-attribute');
                                var id_combination = '';
                            {/literal}
                            {if $combi_enabled}
                                {literal}
                                    if (id_product_attribute_wish > 0) {
                                        id_combination = '{/literal}{$combi_prefix|escape:'htmlall':'UTF-8'}{literal}' + id_product_attribute_wish;
                                    }
                                {/literal}
                            {/if}
                            {literal}
                                var pid = '{/literal}{$id_prefix|escape:'htmlall':'UTF-8'}{literal}' + id_product_wish + id_combination;
                                trackWishlist(pid);
                            });

                            function trackWishlist(pid_wish) {
                                if (window.fctp_wishlist_act == false) {

                                    window.fb_pixel_wishlist_event_id = window.getRandomString(12);
                                    $.ajaxPrefilter(function(options, originalOptions, jqXHR) {
                                        // When friednly url not enabled fc=module&module=iqitwishlist&controller=actions
                                        // When friednly url is enabled module/iqitwishlist/actions
                                        var wishlistUrl = originalOptions.url;
                                        console.log(originalOptions);
                                        if (typeof wishlistUrl != 'undefined') {
                                            var checkURLSEO = wishlistUrl.search('module/iqitwishlist/actions');
                                            var checkURLnonseo = wishlistUrl.search(
                                                'fc=module&module=iqitwishlist&controller=actions');
                                            if (typeof originalOptions.data !== 'undefined' && (checkURLSEO > -
                                                    1 || checkURLnonseo > -1)) {
                                                console.log("Found wishlist url");
                                                if (options.data.indexOf('&fb_pixel_wishlist_event_id') === -
                                                    1) {
                                                    options.data += '&fb_pixel_wishlist_event_id=' +
                                                        fb_pixel_wishlist_event_id;
                                                }
                                            }
                                        }

                                    });
                                    fbq('track', 'AddToWishlist', {
                                            value: {/literal}{$wishlist_value|floatval}{literal},
                                            currency: '{/literal}{$fctp_currency|escape:'htmlall':'UTF-8'}{literal}',
                                            content_type: 'product',
                                            content_ids: [pid_wish]
                                            }, {eventID: window.fb_pixel_wishlist_event_id});
                                            /* Prevent duplicates */
                                            window.fctp_wishlist_act = true;
                                            setTimeout(function() { window.fctp_wishlist_act = false; }, 500);
                                        }
                                    }
                                }
                            {/literal}
                        });
                }
            }
</script>
<!-- End Registration Pixel Call -->
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
 * ***************************************************
 * *     Facebook Conversion Trackcing Pixel Plus    *
 * *          http://www.smart-modules.com           *
 * *                     V 2.3.3                     *
 * ***************************************************
 *
*}
<!-- Add To cart Pixel Call -->

{literal}
    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function(event) {
            init_add_to_cart(10);

            function unique(array) {
                return $.grep(array, function(el, index) {
                    return index === $.inArray(el, array);
                });
            }
            window.ajaxsetupcalled = false;

            function init_add_to_cart(tries) {
                if (typeof jQuery === 'undefined') {
                    if (tries > 0) {
                        setTimeout(function() { init_add_to_cart(tries - 1) }, 250);
                    } else {
                        console.log('jQuery Could not be detected, AddToCart events will not be triggered');
                    }
                } else {
                    $.ajaxPrefilter(function(options, originalOptions, jqXHR) {

                        var urlData = originalOptions.data;
                        if (typeof urlData !== 'undefined' && typeof urlData !== 'object') {
                            var checkData = urlData.search('controller=cart');
                            if (typeof originalOptions.data !== 'undefined' && checkData > -1) {
                                delete window.content_ids_data;
                                delete window.content_ids_product;
                                delete window.total_products_value;
                                window.fb_pixel_event_id = getRandomString(12);
                                if (options.data.indexOf('&fb_pixel_event_id') === -1) {
                                    options.data += '&fb_pixel_event_id=' + window.fb_pixel_event_id;
                                }
                            }
                        }

                    });

                    $(document).ajaxComplete(function(request, jqXHR, settings) {
                        //1.6 code
                        var r = jqXHR.responseJSON;
                        if (r !== undefined && (typeof r.products === 'object') && r.products.length > 0) {
                            let url_str = settings.url + '&' + settings.data;
                            let url = new URL(url_str);
                            let search_params = url.searchParams;
                            let ignore_combi_check = {/literal}{$module_ignore_combi|intval}{literal};
                            var sel_pid = 0;
                            var ipa = 0;
                            console.log(search_params.get('id_product'));
                            if (search_params.get('id_product') !== null) {
                                sel_pid = search_params.get('id_product');
                                ipa = search_params.get('ipa');
                            } else {
                                sel_pid = parseInt($('#product_page_product_id, #id_product').first().val()) || 0;
                                ipa = parseInt($('#idCombination, #id_product_attribute').first().val()) || 0;
                            }
                            var is_delete = search_params.get('delete');
                            if (is_delete == 1 || is_delete == 'true') {
                                console.log("Removing a product from the cart, no event is needed");
                                return;
                            }
                            if (sel_pid > 0) {
                                window.content_name = '';
                                window.content_category = '{/literal}{$content_category nofilter}{literal}';
                                //cart value should never be 0 or empty, so assigning miniumm value as 1
                                window.content_value = 1;
                                window.content_ids_data = [];
                                window.content_ids_product = [];

                                $.each(jqXHR.responseJSON.products, function(key, value) {
                                    var id_combination = '';
                                    {/literal}
                                        {if $combi_enabled}
                                        {literal}
                                    if ((value.idCombination > 0 && value.idCombination == ipa) || value.idCombination > 0 && ignore_combi_check) {
                                        id_combination = '{/literal}{$combi_prefix|escape:'htmlall':'UTF-8'}{literal}' + value.idCombination;
                                    }
                                        {/literal}
                                        {/if}
                                    {literal}
                                    if ((sel_pid == value.id && value.idCombination == 0) || (sel_pid == value.id && value.idCombination > 0 && value.idCombination == ipa ) || (sel_pid == value.id && ignore_combi_check)) {
                                        content_name = value.name;
                                        //send only one item price, but ps 1.6 returns multiple of the total
                                        content_value = formatedNumberToFloat(value.price, window.currencyFormat, window.currencySign) / value.quantity;
                                        var pid = '{/literal}{$id_prefix|escape:'htmlall':'UTF-8'}{literal}' + value.id + id_combination;
                                        var this_product = {
                                            'id': pid,
                                            'quantity': value.quantity,
                                            'item_price': formatedNumberToFloat(value.price, window
                                                    .currencyFormat, window.currencySign) / value
                                                .quantity
                                        }
                                        content_ids_data.push(this_product);
                                        content_ids_product.push(pid);
                                    }

                                });
                                window.total_products_value = formatedNumberToFloat(jqXHR.responseJSON.total, window.currencyFormat, window.currencySign);
                                var cartValues = {
                                    'content_name': window.content_name,
                                    'content_ids': window.content_ids_product,
                                    'contents' : window.content_ids_data,
                                    'content_type': 'product',
                                    'value': content_value,
                                    'currency': '{/literal}{$currency_format_add_tocart|escape:'htmlall':'UTF-8'}{literal}'
                                };
                            {/literal}
                            {if $fpf_id > 0}{literal}
                                cartValues['product_catalog_id'] = '{/literal}{$fpf_id|escape:'htmlall':'UTF-8'}{literal}';
                            {/literal}{/if}{literal}
                                if (window.content_category != '') {
                                    cartValues['content_category'] = window.content_category;
                                }
                                if (cartValues.content_type != '' && cartValues.contents != '' && cartValues.content_ids != '' && cartValues.value != '' && cartValues.currency != '') {
                                    //console.log(cartValues);
                                    fbq('track', 'AddToCart', cartValues, {eventID: window.fb_pixel_event_id });
                                } else {
                                    // Is not an AddToCart event
                                }
                            } else {
                                console.log('Pixel Plus: Could not locate the Product ID, aborting AddToCart');
                            }
                        }
                    });
                }
            }
        });
    </script>
{/literal}
<!-- End Add to cart pixel call -->
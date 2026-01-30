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
        var qty = false;
        document.addEventListener("DOMContentLoaded", function(event) {
            init_add_to_cart(10);
            $(document).on('change', '#quantity_wanted', function () {
                qty = $(this).val();
                //console.log('Quantity wanted: '+ qty);
            });
            function formatedNumberToFloat(price) {
                price = price.replace(prestashop.currency.sign, '');
                price = price.replace(prestashop.currency.iso_code, '');
                var currencyFormat = {/literal}{$fp_round_mode|escape:'htmlall':'UTF-8'}{literal};
                if (currencyFormat === 1)
                    return parseFloat(price.replace(',', '').replace(' ', ''));
                else if (currencyFormat === 2)
                    return parseFloat(price.replace(' ', '').replace(',', '.'));
                else if (currencyFormat === 3)
                    return parseFloat(price.replace('.', '').replace(' ', '').replace(',', '.'));
                else if (currencyFormat === 4)
                    return parseFloat(price.replace(',', '').replace(' ', ''));
                return price;
            }

            function unique(array) {
                return $.grep(array, function(el, index) {
                    return index === $.inArray(el, array);
                });
            }

            function init_add_to_cart(tries) {
                if (typeof jQuery === 'undefined') {
                    if (tries > 0) {
                        setTimeout(function() { init_add_to_cart(tries - 1) }, 250);
                    } else {
                        console.log('jQuery Could not be detected, AddToCart events will not be triggered');
                    }
                } else {

                    XMLHttpRequest.prototype.open = (function(open) {
                        return function(method, url, async) {

                            var checkURL = url.search('/{/literal}{$fp_cart_endpoint|escape:'htmlall':'UTF-8'}{literal}');
                            if (checkURL > -1) {
                                delete window.content_ids_data;
                                delete window.content_ids_product;
                                delete window.total_products_value;
                                window.fb_pixel_event_id = getRandomString(12);
                                var checkQuestion = url.search('\\?');
                                if (checkQuestion > -1) {
                                    url = url + '&fb_pixel_event_id=' + window.fb_pixel_event_id;
                                } else {
                                    url = url + '?fb_pixel_event_id=' + window.fb_pixel_event_id;
                                }

                            }
                            this.addEventListener('load', function() {
                                if (this.response != '') {
                                    try {
                                        var r = JSON.parse(this.response);
                                        if (typeof r.cart == 'object') {
                                            //console.log(r);
                                            if ((typeof r.cart.products == 'object')) {
                                                window.content_name = '';
                                                window.content_category = "{/literal}{$content_category nofilter}{literal}";
                                                //cart value should never be 0 or empty, so assigning miniumm value as 1
                                                window.content_value = 1;
                                                window.content_ids_data = [];
                                                window.content_ids_product = [];
                                                var selected_product_id = r.id_product;
                                                $.each(r.cart.products, function(key,
                                                    value) {
                                                    var id_combination = '';
                                                {/literal}
                                                {if $combi_enabled}
                                                    {literal}
                                                        if (value.id_product_attribute > 0 &&
                                                            value
                                                            .id_product_attribute ==
                                                            r.id_product_attribute
                                                        ) {
                                                            id_combination = '{/literal}{$combi_prefix|escape:'htmlall':'UTF-8'}{literal}' + value.id_product_attribute;
                                                        }
                                                    {/literal}
                                                {/if}
                                                {literal}
                                                    if ((selected_product_id == value.id_product && value.id_product_attribute == 0)
                                                        || (selected_product_id == value.id_product && value.id_product_attribute > 0 && value.id_product_attribute == r.id_product_attribute)) {
                                                        var pprice = 0;
                                                        if (typeof value.price_with_reduction !== 'undefined') {
                                                            pprice = value.price_with_reduction;
                                                        } else if(typeof value.price_without_reduction !== 'undefined') {
                                                            pprice = value.price_with_reduction;
                                                        } else {
                                                            pprice = formatedNumberToFloat(value.price);
                                                        }
                                                        content_name = value.name;
                                                        content_value = pprice;
                                                        var pid = '{/literal}{$id_prefix|escape:'htmlall':'UTF-8'}{literal}' + value.id_product + id_combination;
                                                        var this_product = {
                                                            'id': pid,
                                                            'quantity': (qty !== false ? qty : value.quantity),
                                                            'category': value.category,
                                                            'item_price': (qty !== false ? qty * pprice : value.quantity * pprice),
                                                        }
                                                        content_ids_data.push(this_product);
                                                        content_ids_product.push(pid);
                                                    }
                                                });

                                                window.total_products_value = r.cart
                                                    .totals.total
                                                    .amount;
                                                //here we suppose to sent the add to cart event
                                                var cartValues = {'content_name': window.content_name, 'content_ids': unique(window.content_ids_product), 'contents' : unique(window.content_ids_data),  'content_type': 'product','value': content_value, 'currency': '{/literal}{$currency_format_add_tocart|escape:'htmlall':'UTF-8'}{literal}'};
                                            {/literal}
                                            {if $fpf_id > 0}
                                                {literal}
                                                cartValues['product_catalog_id'] = '{/literal}{$fpf_id|escape:'htmlall':'UTF-8'}{literal}';
                                                {/literal}{/if}{literal}
                                                if (window.content_category != '') {
                                                    cartValues['content_category'] = window
                                                        .content_category;
                                                }
                                                if (cartValues.content_type != '' && cartValues
                                                    .contents != '' &&
                                                    cartValues
                                                    .content_ids != '' && cartValues.value != '' &&
                                                    cartValues
                                                    .currency != '') {
                                                    fbq('track', 'AddToCart', cartValues, {eventID: window.fb_pixel_event_id });
                                                } else {
                                                    console.log("fbq error: Invalid values in the contents or the cart item is deleted");
                                                }
                                            }
                                        }
                                    } catch (e) {
                                        // Is not an AddToCart event, no action needed
                                        //console.log("Can't be parsed the output to json");
                                    }
                                }
                            });
                            open.apply(this, arguments);
                            };
                    })(XMLHttpRequest.prototype.open);
                }
            }
        });
    </script>
{/literal}
<!-- End Add to cart pixel call -->
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
<!-- Facebook View Content Track -->
<script type="text/javascript">
    var combination = {if isset($id_product_attribute)}{$id_product_attribute|intval}{else}0{/if};
    var combi_change = false;
    var u = document.URL;
    var fb_pixel_event_id_view = '{$fb_pixel_event_id_view|escape:'htmlall':'UTF-8'}';
    var pvalue = {if isset($entityprice)}{$entityprice|floatval}{else}productPrice{/if};
    fctp_viewContent(10);
    {literal}
        function fctp_viewContent(max_tries) {
            if (typeof jQuery == 'undefined' || typeof fbq != 'function') {
                console.log(max_tries);
                setTimeout(function() {fctp_viewContent(max_tries-1)},500);
            } else {
            {/literal}
            {if !$is_17}
                {literal}
                    $(document).ready(function() {
                        if ($("#idCombination").length == 1) {
                            combination = $("#idCombination").val();
                            MutationObserver = window.MutationObserver || window.WebKitMutationObserver;

                            var observer = new MutationObserver(function(mutations, observer) {
                                // fired when a mutation occurs
                                var combi = $("#idCombination").val();
                                if (combination != combi) {
                                    combination = combi;
                                    fb_pixel_event_id_view = getRandomString(12);
                                    trackViewContent();
                                }
                            });

                            // define what element should be observed by the observer
                            // and what types of mutations trigger the callback
                            observer.observe(document.getElementById("idCombination"), {
                                subtree: true,
                                attributes: true
                                //...
                            });
                            //Trigger by default when page loaded
                            trackViewContent();
                        } else {
                            //Trigger when there is no combination loaded on page load
                            trackViewContent();
                        }
                    });

                {/literal}
            {else}
                trackViewContent();
            {/if}
            {literal}
            }
        }

        function trackViewContent() {
            //console.log("Combination changed, sending event to FB for viewcontent");
            fbq('track', 'ViewContent', {
                        content_name : '{/literal}{$entityname nofilter}{literal}',
                    {/literal}
                    {if $content_category != ''}
                        content_category: '{$content_category nofilter}',
                    {/if}
                    {literal}
                        value: pvalue,
                        currency :'{/literal}{$fctp_currency|escape:'htmlall':'UTF-8'}{literal}',
                    {/literal}
                    {if $dynamic_ads}
                        {if isset($product_id) && $product_id != ''}
                            {if isset($hascombi) && $hascombi == 1 && $combi == 1}
                                {literal}
                                    content_type: 'product',
                                    content_ids : ['{/literal}{$id_prefix|escape:'htmlall':'UTF-8'}{literal}' + '{/literal}{$product_id|intval}{literal}' + (combination > 0 ? '{/literal}{$combi_prefix|escape:'htmlall':'UTF-8'}{literal}'+combination : '')],
                                    {/literal}{else}{literal}
                                    content_type: 'product',
                                    content_ids : ['{/literal}{$id_prefix|escape:'htmlall':'UTF-8'}{literal}' + '{/literal}{$product_id|intval}{literal}'],
                                {/literal}
                            {/if}
                        {/if}
                    {/if}
                    {if $fpf_id > 0}
                        {literal}
                            product_catalog_id : '{/literal}{$fpf_id|escape:'htmlall':'UTF-8'}{literal}'
                            {/literal}{/if}{literal}

                            }, {eventID: fb_pixel_event_id_view});
                        }

                        function discoverCombi() {
                            if (combi_change === true) {
                                combi_change = false;
                                return true;
                            }
                            if ($('#product-details').length > 0) {
                                if (typeof $('#product-details').data('product') !== 'undefined') {
                                    combination = $('#product-details').data('product').id_product_attribute;
                                    pvalue = $('#product-details').data('product').price_amount;
                                    return true;
                                }
                            }
                            return false;
                        }
        </script>
    {/literal}
    <!-- END Facebook View Content Track -->
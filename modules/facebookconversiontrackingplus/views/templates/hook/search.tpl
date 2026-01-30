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
<!-- Search Pixel Call -->
<script type="text/javascript">
    var fb_pixel_event_id_search = '{$fb_pixel_event_id_search|escape:'htmlall':'UTF-8'}';
    var content_ids_list = [];

    function getContentIdsList() {
        {if isset($products) && (count($products) > 0)}
            content_ids_list = [{foreach from=$products item=product name=sproducts}'{$id_prefix|escape:'htmlall':'UTF-8'}{$product.id_product|intval}'
                {if !$smarty.foreach.sproducts.last}, 
                {/if}
            {/foreach}];
        {else}
            var listSelector = getListSelector();
            if (typeof listSelector !== 'undefined') {
                content_ids_list = getContentIds(listSelector, '{$id_prefix|escape:'htmlall':'UTF-8'}');
                if (content_ids_list.length == 0) {
                    console.log('Could not locate the product IDs');
                }
            }
        {/if}
    }
    {literal}
        function getListSelector() {
            if ($('#product_list').length > 0) {
                return $('#product_list').children();
            } else if ($('.products article').length > 0) {
                return $('.products article');
            } else if ($('.product_list').length > 0) {
                return $('.product_list').children();
            } else if ($('.ajax_block_product').length > 0) {
                return $('.ajax_block_product');
            }
        }

        function getContentIds(selector, prefix) {
            var tmp = [];
            var id = '';
            selector.each(function() {
                if (tmp.length < 5) {
                    if ($(this).data('idProduct') > 0) {
                        tmp.push(prefix + $(this).data('idProduct'));
                    } else if ($(this).find('[data-id-product]').length > 0) {
                        tmp.push(prefix + $(this).find('[data-id-product]').first().data('idProduct'));
                    } else {
                        $(this).find('a').each(function() {
                            id = $(this).attr('href').match(/\/([0-9]*)-/);
                            if (typeof id[1] !== 'undefined') {
                                tmp.push(prefix + id[1]);
                                return false;
                            }
                        });
                    }
                } else {
                    return false;
                }
            });
            //console.log(tmp);
            return tmp;
        }
    {/literal}
</script>
{literal}
    <script type="text/javascript">
        fctp_search(10);

        function fctp_search(max_tries) {
            if (typeof jQuery == 'undefined' || typeof fbq != 'function') {
                setTimeout(function() {fctp_search(max_tries-1)},500);
            } else {
                $(document).ready(function() {
                        getContentIdsList();
                        console.log("Trigger event to Search");
                        fbq('track', 'Search', {
                            'search_string' : '{/literal}{if isset($search_query) && $search_query != ''}{$search_query|escape:'htmlall':'UTF-8'}{elseif isset($search_string) && $search_string != ''}{$search_string|escape:'htmlall':'UTF-8'}{else}{$search_keywords|escape:'htmlall':'UTF-8'}{/if}',
                                value : {$search_value|floatval},
                                currency : '{$fctp_currency|escape:'htmlall':'UTF-8'}',
                                {if $dynamic_ads}
                                    content_ids: content_ids_list,
                                    content_type: 'product',
                                {/if}
                                {if isset($fpf_id)}
                                    product_catalog_id :  '{$fpf_id|escape:'htmlall':'UTF-8'}',
                                {/if}
                                }, {literal}{eventID: fb_pixel_event_id_search }{/literal});
                            });
                        }
                    }
        </script>
        <!-- End Search Pixel Call -->
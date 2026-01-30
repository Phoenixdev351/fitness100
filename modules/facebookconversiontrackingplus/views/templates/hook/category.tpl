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
<!-- Facebook ViewCategory event tracking -->
<script type="text/javascript">
    var fb_pixel_event_id_view = '{$fb_pixel_event_id_view|escape:'htmlall':'UTF-8'}';
    var content_ids_list = [];
    {if isset($top_sell_ids) && $top_sell_ids}
        content_ids_list = [{foreach from=$top_sell_ids item=product name=top_sell}
            {if $smarty.foreach.top_sell.iteration <= $max_cat_items}'{$product|escape:'htmlall':'UTF-8'}',
            {/if}
        {/foreach}];
    {else}
        init_cat(10);

        function init_cat(tries) {
            if (typeof jQuery === 'undefined') {
                if (tries > 0) {
                    setTimeout(function() { init_cat(tries - 1); }, 250);
                }
            } else {
                $(document).ready(function() {
                    {if isset($products) && (count($products) > 0)}
                        content_ids_list = [{foreach from=$products item=product name=sproducts}
                            {if $smarty.foreach.sproducts.iteration <= $max_cat_items}'{$id_prefix|escape:'htmlall':'UTF-8'}{$product.id_product|intval}
                                {if $combi && $product.id_product_attribute > 0}{$combi_prefix|escape:'htmlall':'UTF-8'}{$product.id_product_attribute|intval}
                                {/if}'
                                {if !$smarty.foreach.sproducts.last}, 
                                {/if}
                            {/if}
                        {/foreach}];
                    {else}
                        var listSelector = getListSelector();
                        if (listSelector !== false) {
                            content_ids_list = getContentIds(listSelector, '{$id_prefix|escape:'htmlall':'UTF-8'}');
                        }
                        if (content_ids_list.length == 0) {
                            console.log('Could not locate the product IDs');
                        }
                    {/if}
                    function getListSelector() {
                        if ($('.products article').length > 0) {
                            return $('.products article');
                        } else if ($('#product_list').length > 0) {
                            return $('#product_list').children();
                        } else if ($('.product_list').length > 0) {
                            return $('.product_list').children();
                        } else if ($('.ajax_block_product').length > 0) {
                            return $('.ajax_block_product');
                        }
                        return false;
                    }

                    function getContentIds(selector, prefix) {
                        var tmp = [];
                        var id = '';
                        selector.each(function() {
                            if (tmp.length < 5) {
                                if ($(this).find('[data-id-product]').length > 0) {
                                    console.log('11');
                                    var e = $(this).find('[data-id-product]').first();
                                    tmp.push(prefix + '' + e.data('idProduct'){if $combi}+'{$combi_prefix|escape:'htmlall':'UTF-8'}'+e.data('idProductAttribute'){/if});
                                } else {
                                    $(this).find('a').each(function() {
                                        id = $(this).attr('href').match(/\/([0-9]*)-/);
                                        if (typeof id[1] !== 'undefined') {
                                            tmp.push(id[1]);
                                            return false;
                                        }
                                    });
                                }
                            } else {
                                return false;
                            }
                        });
                        return tmp;
                    }
                });
            }
        }
    {/if}
</script>
{literal}
    <script type="text/javascript">
        var combination = '';
        fctp_categoryView(10);

        function fctp_categoryView(max_tries) {
            if (typeof jQuery == 'undefined' || typeof fbq != 'function') {
                setTimeout(function() {fctp_categoryView(max_tries-1)},500);
            } else {
                jQuery(document).ready(function() {
                    var edata = {
                        content_name : '{/literal}{$entityname nofilter}{literal}',
                    };
                    edata.value = {/literal}{$category_value|floatval}{literal};
                    edata.currency = '{/literal}{$fctp_currency|escape:'htmlall':'UTF-8'}{literal}';
                    edata.content_type = 'product';
                    edata.content_category = '{/literal}{$entityname nofilter}{literal}';
                {/literal}
                {if $dynamic_ads}
                    {literal}
                        edata.content_ids = content_ids_list;
                    {/literal}
                {/if}
                {if isset($fpf_id)}
                    {literal}
                        edata.product_catalog_id = '{/literal}{$fpf_id|escape:'htmlall':'UTF-8'}{literal}';
                        {/literal}{/if}{literal}
                        console.log(edata);
                        fbq('trackCustom', 'ViewCategory', edata, {eventID: fb_pixel_event_id_view});
                    });
                }
            }
        </script>
    {/literal}
    <!-- END Facebook ViewCategory event tracking -->
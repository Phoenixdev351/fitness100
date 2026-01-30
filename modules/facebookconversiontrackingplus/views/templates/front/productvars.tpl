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
 * ***************************************************
 * *     Facebook Conversion Trackcing Pixel Plus    *
 * *          http://www.smart-modules.com           *
 * *                     V 2.3.3                     *
 * ***************************************************
 *
*}

{if isset($id_product_attribute)}
    <!-- Pixel Plus Product Vars -->
    <script type="text/javascript">
        var fb_pixel_event_id_view = '{$refresh_pixel_id|escape:'htmlall':'UTF-8'}';
        if (typeof combination === 'undefined' || (combination != {$id_product_attribute|intval})) {
        setTimeout(function() {
            if ((typeof discoverCombi !== 'undefined') && discoverCombi() === false) {
                combination = {$id_product_attribute|intval};
                if ($('[itemprop=price]').length > 0) {
                    pvalue = parseFloat($('[itemprop=price]').attr('content'));
                }
            }
            if (typeof trackViewContent !== 'undefined') {
                trackViewContent();
            }
            //console.log(combi_change);
        }, 1200);
        }
    </script>
    <!-- END Pixel Plus Product Vars -->
{else}
    trackViewContent();
{/if}
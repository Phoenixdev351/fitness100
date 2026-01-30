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


{if isset($fcp_product_custom_selector)}
    <!-- Pixel Plus Product Vars -->
    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function(event) {

        function setCustomProductCookie(name, value, hours) {
            var expires = "";
            if (hours) {
                var date = new Date();
                date.setTime(date.getTime() + (hours * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "") + expires + "; path=/";
        }

        function getCustomProductCookie(name) {
            var nameEQ = name + "=";
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }

        $(".{$fcp_product_custom_selector}").on("click", function(){

        var purchase_event_id = getRandomString(12);
        var cP = {};
        cP['content_name']= '{$entityname|escape:'htmlall':'UTF-8'}';
        cP['value'] = pvalue;
        cP['content_ids']= {$product_id|intval};
        cP['content_type']= 'product';
        var eI = {};
        eI['eventID'] = purchase_event_id;

        fbq('track', 'CustomizeProduct', cP, eI);
        console.log("Event triggered customize")

        var sc = {};
        sc[purchase_event_id] = cP;
        var coG = getCustomProductCookie('CustomizeProductSent');
        if (coG == "") {
            coG = {};
            var poc = JSON.parse(coG);
        } else {
            var poc = {};
        };
        poc[purchase_event_id] = cP;
        setCustomProductCookie('CustomizeProductSent', JSON.stringify(poc), 1);
        })

        });
    </script>
    <!-- END Pixel Plus Product Vars -->

{/if}
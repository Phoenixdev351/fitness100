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

{literal}
    <!-- Registration Pixel Call -->
    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function(event) { 
        var registered = false;
        {/literal}
        {if $entity == 'supercheckout'}
        if ($('#email').is(':visible')) {
            $('#supercheckout_confirm_order').click(function() {
                if (isEmail($('#email').val()) && registered == false) {
                    fctp_opc_registration(10);
                    registered = true;
                }
            });
        }
        {/if}
        {literal}
        function fctp_opc_registration(max_tries) {
            if (typeof jQuery == 'undefined' || typeof fbq != 'function') {
                setTimeout(function() {fctp_opc_registration(max_tries-1)},500);
            } else {
                $(document).ready(function() {
                    fbq('track', 'CompleteRegistration', {
                        'content_name' : '{/literal}{l s='Registered Customer' mod='facebookconversiontrackingplus'}{literal}',
                        email : $('#email').val(),
                    }, {eventID: generateEventId('Purchase')});
                });
            }
        }
        function isEmail(email) {
            var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            return regex.test(email);
        }
    });
    </script>
    <!-- End Registration Pixel Call -->
{/literal}

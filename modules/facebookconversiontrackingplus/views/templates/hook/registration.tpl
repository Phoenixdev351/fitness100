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
<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function(event) { 
        init_registrations(10);

        function init_registrations(tries)
        {
            if (typeof jQuery !== 'undefined') {
                $(document).ready(function() {
                    {if isset($registeron) && $registeron == 1}
                    fctp_registration(10);
                    {else}
                    {* Guest Tracking converting to customer *}
                    {literal}
                    $('button[name="submitTransformGuestToCustomer"]').click(function() {
                        if ($('input[name="password"]').val() != '') {
                            fctp_registration(10);
                        }
                    });
                    {/literal}
                    {/if}
                    {literal}
                    function fctp_registration(max_tries)
                    {
                        if (typeof fbq != 'undefined' && typeof jQuery != 'undefined') {
                            {/literal}
                            {if isset($fctp_ajaxurl) && $fctp_ajaxurl != ''}
                            $.ajax('{$fctp_ajaxurl|escape:'htmlall':'UTF-8'}?trackRegister=1')
                            .done(function (data) {
                                // Conversion tracked
                                if (data.return == 'ok') {
                                    trackRegistration();
                                }
                            })
                            .fail(function() {
                                console.log('Conversion could not be sent, contact module developer to check the issue');
                            });
                            {else}
                            trackRegistration();
                            {/if}
                            {literal}
                        } else {
                            if (tries > 0) {
                                setTimeout(function() { fctp_registration(tries-1) }, 500);
                            }
                        }
                    }
                    if (typeof jQuery == 'undefined' || typeof fbq != 'function') {
                        setTimeout(function() {fctp_registration(max_tries-1)},500);
                    }
                });
                        function trackRegistration()
                        {
                            fbq('track', 'CompleteRegistration', {
                                'content_name' : '{/literal}{l s='Registered Customer' mod='facebookconversiontrackingplus'}',
                                value: {$complete_registration_value|floatval},
                                currency : '{$fctp_currency|escape:'htmlall':'UTF-8'}',{literal}
                                status: true,
                            },  {eventID: generateEventId('CompleteRegistration')});
                        }
            {/literal}
            } else {
                if (tries > 0) {
                    setTimeout(function() { init_registrations(tries-1); }, 350);
                }
            }
        }
    });
</script>
<!-- End Registration Pixel Call -->

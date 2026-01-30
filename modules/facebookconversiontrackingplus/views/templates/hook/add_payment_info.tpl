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
    <!-- Start Payment Pixel Call -->
    <script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        fctp_startPayment(25);
    });
    var paymentAdded = false;
    function fctp_startPayment(max_tries) {
        if (typeof jQuery == 'undefined' || typeof fbq != 'function' && max_tries > 0) {
            setTimeout(function() {fctp_startPayment(max_tries-1)},250);
        } else {
            var sel = [
                    ['#supercheckout_confirm_order', '#supercheckout_confirm_order'],
                    ['.payment_module a', '.payment_module a'],
                    ['#HOOK_PAYMENT', '#HOOK_PAYMENT, #HOOK_PAYMENT input'],
                    ['#checkout-payment-step', '#checkout-payment-step input'],
                    ['#opc_payment_methods', '#opc_payment_methods input'],
                    ['#payment_method_container', '#payment_method_container input, .module_payment_container'],
                    ['#module-steasycheckout-default', '.payment-options label, .payment-options input'],
                ];
            var i = 0, len = sel.length;
            while (i < len) {
                if ($(sel[i][0]).length > 0) {
                    $(document).on('mousedown', sel[i][1], function() {
                        trackAddPaymentInfo();
                    });
                    return;
                }
                i++;
            }
            // For onepagecheckout compatibilty
            var tries = 10;
            setTimeout(function() { onePageCheckoutTracking(); }, 1000);
            function onePageCheckoutTracking() {
                if ($("#payment_method_container").length == 1) {
                    if ($("#btn_place_order").length == 1) {
                        $("#btn_place_order").click(function() {
                            if ($("#payment_method_container .selected").length == 1) {
                                trackAddPaymentInfo();
                            }
                        });
                    } else {
                        retry();
                    }
                } else {
                   retry();
                }

            }
            function retry() {
                if (tries > 0) {
                    tries--;
                    setTimeout(function() { onePageCheckoutTracking(); }, 500);
                }
            }
            if (max_tries > 0) {
                setTimeout(function() {fctp_startPayment(max_tries-1)},500);
            }
        }
    }
    function trackAddPaymentInfo()
    {
        if (paymentAdded === false) {
            fbq('track', 'AddPaymentInfo', {{/literal}
                value: {$initiate_payment_value|floatval},
                currency: '{$fctp_currency|escape:'htmlall':'UTF-8'}',{literal}
            }, {eventID: '{/literal}{$fb_event_start_payment|escape:'htmlall':'UTF-8'}{literal}'});
            paymentAdded = true;
            console.log('AddPaymentTracked');
        }
    }
    </script>
    <!-- End Start Payment Pixel Call -->
    {/literal}

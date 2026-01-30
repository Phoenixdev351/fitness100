{*
 * Facebook Conversion Pixel Tracking Plus
 *
 * NOTICE OF LICENSE
 *
 * @author    Pol Rué
 * @copyright Smart Modules 2014
 * @license   One time purchase Licence (You can modify or resell the product but just one time per licence)
 * @version 2.0.7
 * @category Marketing & Advertising
 * Registered Trademark & Property of Smart-Modules.pro
 *
 * **************************************************
 * *     Facebook Conversion Trackcing Pixel Plus    *
 * *          http://www.smart-modules.pro           *
 * *                     V 2.0.7                     *
 * **************************************************
 *
*}
    {literal}
    <!-- Start Payment Pixel Call -->
    <script type="text/javascript">
    fctp_startPayment(10);
    var paymentAdded = false;
    function fctp_startPayment(max_tries) {
        if (typeof jQuery == 'undefined' || typeof fbq != 'function') {
            setTimeout(function() {fctp_startPayment(max_tries-1)},500);
        } else {
            $(document).ready(function() {
                if($('#supercheckout_confirm_order').length > 0) {
                    $(document).on('click', '#supercheckout_confirm_order', function() {
                        trackAddPaymentInfo();
                    });
                }
                if ($(".payment_module a").length > 0) {
                    $(document).on('click', ".payment_module a", function(e) {
                        trackAddPaymentInfo();
                    });
                }
                if ($("#HOOK_PAYMENT").length > 0) {
                    $(document).on('click', "#HOOK_PAYMENT a", function(e) {
                        trackAddPaymentInfo();
                    });
                    $(document).on('keydown', "#HOOK_PAYMENT input", function(e) {
                        trackAddPaymentInfo();
                    });
                }
                if ($('#checkout-payment-step').length > 0) {
                    $(document).on('mousedown', '#checkout-payment-step input', function() {
                        trackAddPaymentInfo();
                    });
                }
                if ($('#opc_payment_methods').length > 0) {
                    $(document).on('keydown', '#opc_payment_methods input', function() {
                        trackAddPaymentInfo();
                    });
                    $(document).on('click', '#opc_payment_methods a', function() {
                        trackAddPaymentInfo();
                    });
                }
                // For onepagecheckout compatibilty
                var tries = 10;
                setTimeout(function() { onePageCheckoutTracking(); }, 1000);
                function onePageCheckoutTracking() {
                    if ($("#payment_method_container").length == 1) {
                        if ($("#btn_place_order").length == 1) {
                            $("#btn_place_order").click(function() {
                                if ($("#payment_method_container .selected").length == 1) {
                                    fbq('track', 'AddPaymentInfo');
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
                function trackAddPaymentInfo()
                {
                    if (paymentAdded === false) {
                        fbq('track', 'AddPaymentInfo', {{/literal}
                            value: {$initiate_payment_value|floatval},
                            currency: '{$fctp_currency|escape:'htmlall':'UTF-8'}',{literal}
                        });
                        paymentAdded = true;
                        console.log('AddPaymentTracked');
                    }
                }
            });
        }
    }
    </script>
    <!-- End Start Payment Pixel Call -->
    {/literal}
/**
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
 */

document.addEventListener('DOMContentLoaded', function() {
    showCookieOptions();
    $(document).on('change', '#FCTP_BLOCK_SCRIPT_on, #FCTP_BLOCK_SCRIPT_off', function() {
        showCookieOptions();
    });

    function showCookieOptions() {
        if (typeof $('#FCTP_BLOCK_SCRIPT_on').attr('checked') !== 'undefined') {
            $('.fctp_cookies').show();
        } else {
            $('.fctp_cookies').hide();
        }
    }
    // Advanced modes
    var adv_modes = {
        FCTP_COOKIE_RELOAD: '.fctp_cookie_reload_inverted',
    };

    function enableDisableOptions(disable, selector) {
        if (selector.indexOf('inverted') !== -1) {
            disable = !disable;
        }
        console.log(selector + ': ' + disable);
        $(selector + ' input, ' + selector + ' select, ' + selector + ' button').prop('disabled', disable);
    }

    for (const selector in adv_modes) {
        $(document).on('change', '#' + selector + '_on, #' + selector + '_off', function() {
            enableDisableOptions(!+$(this).val(), adv_modes[selector]);
        });
        if ($('#' + selector + '_off').prop('checked')) {
            enableDisableOptions(true, adv_modes[selector]);
        }
    }
});

/* ADD IP to Logger */
function addRemoteAddr() {
    var length = $('input[name=FCTP_CONVERSION_IP_LOG]').attr('value').length;
    if (length > 0) {
        if ($('input[name=FCTP_CONVERSION_IP_LOG]').attr('value').indexOf(remoteAddr) < 0) {
            $('input[name=FCTP_CONVERSION_IP_LOG]').attr('value',$('input[name=FCTP_CONVERSION_IP_LOG]').attr('value') + ',' + remoteAddr);
        }
    } else {
        $('input[name=FCTP_CONVERSION_IP_LOG]').attr('value', remoteAddr);
    }
}
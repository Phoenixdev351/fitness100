/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Feed.Biz, Ltd.
 * Use, copy, modification or distribution of this source file without written
 * license agreement from Feed.Biz, Ltd. is strictly forbidden.
 * In order to obtain a license, please contact us: contact@feed.biz
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Feed.Biz, Ltd.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Feed.Biz, Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Feed.Biz, Ltd. a l'adresse: contact@feed.biz
 * ...........................................................................
 * @package    Feed.Biz
 * @copyright  Copyright (c) 2011-2022 Feed.Biz - Hong Kong - Head Quarters - Room 1408, 14/F, Tak Shing House - Theatre Lane - 20 des Voeux Road Central, -Central, Hong Kong
 * @author     Olivier B.
 * @license    Commercial license
 * Support by mail  :  support@feed.biz
 */

$(document).ready(function () {
    // For PS 1.5017
    if ($('select[name="id_address"]'))
        $('select[name="id_address"]').css('width', '400px');

    // get parameters - credits :
    // http://wowmotty.blogspot.com/2010/04/get-parameters-from-your-script-tag.html
    // extract out the parameters
    function gup(n, s) {
        n = n.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
        var p = (new RegExp("[\\?&]" + n + "=([^&#]*)")).exec(s);
        return (p === null) ? "" : p[1];
    }

    var scriptSrc = $('script[src*="orders_sheet.js"]').attr('src');
    var path = gup('path', scriptSrc);

    // load CSS
    //
    $('head').append("<link>");
    var cssi = $("head").children(":last");
    cssi.attr({
        rel: "stylesheet",
        type: "text/css",
        href: path + '/css/orders_sheet.css'
    });


    $('#me-update').click(function () {
        $(this).hide();
        $('#me-update-loader').show();
    });

    $('#me-valid').click(function () {
        $(this).hide();
        $('#me-valid-loader').show();
    });

    $('button[name="feedbiz-cancel-button"], button[name="feedbiz-revert-button"]').click(function () {
    
        var params = 'fbtoken=' + $('#fbtoken').val() + '&id_order=' + $('#seller_order_id').val() + '&mp_order_id=' + $('#mp_order_id').val() + '&action=cancel' + '&reason=' + $('#feedbiz-cancel').val() + '&cancel_status=' + $('#cancel_status').val();
            
        $('#feedbiz-cancel-loader').show();    
        $('#feedbiz-cancel-success').html('').hide();
        $('#feedbiz-cancel-error').html('').hide();
    
        var pAjax = new Object();
        pAjax.url = $('#cancel_url').val();
        pAjax.type = 'POST';
        pAjax.data_type = 'json';
        pAjax.data = params;
    
        $.ajax({
            type: pAjax.type,
            url: pAjax.url,
            dataType: pAjax.data_type,
            data: pAjax.data,
            success: function (data) {
                $('#feedbiz-cancel-loader').hide();                

                if (window.console)
                    console.log(data);

                if (data.result && !data.error)
                {
                    $('#feedbiz-to-cancel').hide();
                    $('#feedbiz-cancel-success').html(data.result).show();
                    $('button[name="feedbiz-cancel-button"]').attr('disabled', true);
                }
                else
                    $('#feedbiz-cancel-error').html(data.result).show();
            },            
            error: function (data) {
                $('#feedbiz-cancel-loader').hide();
                $('#feedbiz-cancel-success').hide();

                if (window.console)
                    console.log(data);
    
                if (data.status == 200 && data.responseText)
                    $('#feedbiz-cancel-error').html(data.responseText).show();
                else {
                    $('#feedbiz-cancel-error').html($('#messaging_ajax_error').val()).show();
                    if (typeof(data) == 'object' && data.responseText)
                        $('#feedbiz-cancel-error').append('<br />' + data.responseText).show();
                }
            }
        });
    });
});

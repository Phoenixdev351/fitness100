/**
*
* NOTICE OF LICENSE
*
* This product is licensed for one customer to use on one installation (test stores and multishop included).
* Site developer has the right to modify this module to suit their needs, but can not redistribute the module in
* whole or in part. Any other use of this module constitutes a violation of the user agreement.
*
* DISCLAIMER
*
* NO WARRANTIES OF DATA SAFETY OR MODULE SECURITY
* ARE EXPRESSED OR IMPLIED. USE THIS MODULE IN ACCORDANCE
* WITH YOUR MERCHANT AGREEMENT, KNOWING THAT VIOLATIONS OF
* PCI COMPLIANCY OR A DATA BREACH CAN COST THOUSANDS OF DOLLARS
* IN FINES AND DAMAGE A STORES REPUTATION. USE AT YOUR OWN RISK.
*
*  @author    idnovate.com <info@idnovate.com>
*  @copyright 2020 idnovate.com
*  @license   See above
*/

$('.textarea-autosize.apc_tiny').removeClass('textarea-autosize');

$(document).ready(function(){
    if (typeof(tinySetup) != "undefined") {
        /*if (typeof(module_dir) == "undefined") {
            var module_dir = '/modules/';
        }*/

        tinySetup({
            editor_selector: "apc_tiny",
            valid_children: "+body[style|script|iframe|section|link],pre[iframe|section|script|div|p|br|span|img|style|h1|h2|h3|h4|h5],*[*]",
            forced_root_block: '',
            external_plugins: {
                "filemanager": ad + "/filemanager/plugin.min.js",
                'codemirror': module_dir + "advancedpopupcreator/lib/tiny_mce/codemirror/plugin.min.js"
            },
            codemirror: {
                indentOnInit: true,
                path: '../../CodeMirror',
            }
        });
    }

    //Apply CodeMirror in CSS field
    $('textarea[name^="css"]').each(function() {
        var myCodeMirror = CodeMirror.fromTextArea($(this)[0], {
            mode: "css"
        });

		myCodeMirror.setSize(700, 225);
    });

    /* Filter switches */
    // Hide all multiselect without selected values
    // If multiselect has selected values, don't hide it
    $('.multiple_select').each(function() {
        $(this).multiselect();
        var fieldName = $(this).attr('name').replace(/[\[\]']+/g,'');

        if (fieldName === 'categories_selected') {
            if ($(this).find('option:selected').length > 0 || $('#nb_products').val() !== '') {
                $('[name="switch_' + fieldName+'"][value=1]').attr('checked', true);
                $('[name="switch_' + fieldName+'"][value=0]').attr('checked', false);
            } else {
                $(this).parents().eq(0).hide().prev().hide().prev().hide().prev().hide();
            }
        } else {
            if ($(this).find('option:selected').length > 0) {
                $('[name="switch_' + fieldName+'"][value=1]').attr('checked', true);
                $('[name="switch_' + fieldName+'"][value=0]').attr('checked', false);
            } else {
                $(this).parents().eq(0).hide().prev().hide();
            }
        }
    });

    if ($('[name^=display_url_string_]').filter(function() { return $(this).val(); }).length > 0) {
        $('#display_url_on').attr('checked', true);
    }

    if ($('[name^=display_referrer_string_]').filter(function() { return $(this).val(); }).length > 0) {
        $('#display_referrer_on').attr('checked', true);
    }

    if ($('[name^=display_ip_string]').filter(function() { return $(this).val(); }).length > 0) {
        $('#display_ip_on').attr('checked', true);
    }

    // Display/hide values when switch changes
    $('[name^=switch_], [name=display_on_load], [name=display_after_cart], [name=display_on_click], [name=cart_amount], [name=product_stock], [name=display_url_string], [name=display_referrer_string], [name=display_ip_string]').change(function() {
        toggleFilters();
    });

    toggleFilters();
});

function toggleFilters() {
    $('[name^=switch_]:checked, [name=display_on_load]:checked, [name=display_after_cart]:checked, [name=display_on_click]:checked, [name=cart_amount]:checked, [name=product_stock]:checked, [name=display_url_string]:checked, [name=display_referrer_string]:checked, [name=display_ip_string]:checked').each(function () {
        var fieldName = $(this).attr('name');
        if (fieldName === 'switch_categories_selected') {
            if ($(this).val() === "0") {
                $(this).parents().eq(0).next().next().hide().next().hide().next().next().hide();
            } else {
                $(this).parents().eq(0).next().next().show().next().show().next().next().show();
            }
        } else if (fieldName === 'cart_amount' || fieldName === 'product_stock') {
            if ($(this).val() === "0") {
				$(this).parents().eq(0).next().next().hide().next().hide().next().next().hide().next().hide();
            } else {
				$(this).parents().eq(0).next().next().show().next().show().next().next().show().next().show();
            }
        } else {
            if ($(this).val() === "0") {
                $(this).parents().eq(0).next().next().hide().next().hide();
            } else {
                $(this).parents().eq(0).next().next().show().next().show();
            }
        }
    });
}

// If multiselect has selected values, display it on load
if ($('#controller_exceptions\\[\\]').find('> option:selected').length > 0) {
    $('#switch_controller_exceptions_on').attr('checked', true);
}

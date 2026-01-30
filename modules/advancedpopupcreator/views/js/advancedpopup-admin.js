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
    $('textarea.css_content').each(function() {
        CodeMirror.fromTextArea($(this)[0], {
            mode: "css",
            autoRefresh: true,
            lineNumbers: true,
            'CodeMirror-lines': 10,
        });
    });

    //Enable switches for inputs/selects with values
    $("[class^='switch_']").not('div').each(function () {
        let elementClass = $(this).attr('class').split(" ").filter(function(n) {
          if(/switch/.test(n)) return n;
        });
        var that = this;
        elementClass.forEach(function(element) {
            if ($(that).is('input')) {
                if ($(that).val().length > 0) {
                    $('#'+element+'_on').attr('checked', true);
                    $(that).closest('.form-wrapper > .form-group').show();
                };
            } else if ($(that).is('select')) {
                if (!$(that).hasClass('selectedSwap')) {
                    return;
                }
                if ($(that).find('option').length > 0) {
                    $('#'+element+'_on').attr('checked', true);
                    $(that).closest('.form-wrapper > .form-group').show().find('*').show();
                }
            }
        });
    });

    // Display/hide values when switch changes
    $("[name^=switch_], [name=display_on_load], [name=display_after_cart], [name=display_on_click], [name=cart_amount], [name=product_stock]").change(function() {
        toggleFilters();
    });

    toggleFilters();
});

/*if (fieldName === 'categories_selected[]_selected[]') {
        	debugger;
            if ($(this).find('option').length === "0") {
                $(this).closest('.form-group').next().hide().next().hide();
            } else {
                $(this).closest('.form-group').next().show().next().show();
            }
        }*/

function toggleFilters() {
    $('[name=display_on_load]:checked, [name=display_after_cart]:checked, [name=display_on_click]:checked, [name=cart_amount]:checked, [name=product_stock]:checked, [name=switch_categories_selected]:checked').each(function () {
        var fieldName = $(this).attr('name');
        if (fieldName === 'switch_categories_selected') {
            if ($(this).val() === "0") {
                $(this).closest('.form-group').next().hide().next().hide();
            } else {
                $(this).closest('.form-group').next().show().next().show();
            }
        } else if (fieldName === 'cart_amount' || fieldName === 'product_stock') {
            if ($(this).val() === "0") {
                $(this).closest('.form-group').nextAll().slice(0,2).hide();
            } else {
                $(this).closest('.form-group').nextAll().slice(0,2).show();
            }
        } else {
            if ($(this).val() === "0") {
                $(this).closest('.form-group').next().hide();
                $('.'+$(this).attr('name')+', #'+$(this).attr('name')+', #'+$(this).attr('name')+'_minimum, #'+$(this).attr('name')+'_maximum').not('div').closest('.form-wrapper > .form-group').hide();
                $('.'+$(this).attr('name')+', #'+$(this).attr('name')+', #'+$(this).attr('name')+'_minimum, #'+$(this).attr('name')+'_maximum').not('div').closest('.margin-group').hide();
                $('.'+$(this).attr('name')+', #'+$(this).attr('name')+', #'+$(this).attr('name')+'_minimum, #'+$(this).attr('name')+'_maximum').not('div').closest('.swap-container-custom').hide();
                $('.'+$(this).attr('name')+', #'+$(this).attr('name')+', #'+$(this).attr('name')+'_minimum, #'+$(this).attr('name')+'_maximum').not('div').closest('.margin-form').hide().prev().hide();
            } else {
                $(this).closest('.form-group').next().show();
                $('.'+$(this).attr('name')+', #'+$(this).attr('name')+', #'+$(this).attr('name')+'_minimum, #'+$(this).attr('name')+'_maximum').not('div').closest('.form-wrapper > .form-group').show();
                $('.'+$(this).attr('name')+', #'+$(this).attr('name')+', #'+$(this).attr('name')+'_minimum, #'+$(this).attr('name')+'_maximum').not('div').closest('.margin-group').show();
                $('.'+$(this).attr('name')+', #'+$(this).attr('name')+', #'+$(this).attr('name')+'_minimum, #'+$(this).attr('name')+'_maximum').not('div').closest('.swap-container-custom').show();
                $('.'+$(this).attr('name')+', #'+$(this).attr('name')+', #'+$(this).attr('name')+'_minimum, #'+$(this).attr('name')+'_maximum').not('div').closest('.margin-form').show().prev().show();
            }
        }
    });

    $('[name^=switch_]:checked').each(function () {
        if ($(this).val() === "0") {
            $('.'+$(this).attr('name')+', #'+$(this).attr('name')+', #'+$(this).attr('name')+'_minimum, #'+$(this).attr('name')+'_maximum').not('div').closest('.form-wrapper > .form-group').hide();
            $('.'+$(this).attr('name')+', #'+$(this).attr('name')+', #'+$(this).attr('name')+'_minimum, #'+$(this).attr('name')+'_maximum').not('div').closest('.margin-group').hide();
            $('.'+$(this).attr('name')+', #'+$(this).attr('name')+', #'+$(this).attr('name')+'_minimum, #'+$(this).attr('name')+'_maximum').not('div').closest('.swap-container-custom').hide();
            $('.'+$(this).attr('name')+', #'+$(this).attr('name')+', #'+$(this).attr('name')+'_minimum, #'+$(this).attr('name')+'_maximum').not('div').closest('.margin-form').hide().prev().hide();
        } else {
            $('.'+$(this).attr('name')+', #'+$(this).attr('name')+', #'+$(this).attr('name')+'_minimum, #'+$(this).attr('name')+'_maximum').not('div').closest('.form-wrapper > .form-group').show();
            $('.'+$(this).attr('name')+', #'+$(this).attr('name')+', #'+$(this).attr('name')+'_minimum, #'+$(this).attr('name')+'_maximum').not('div').closest('.margin-group').show();
            $('.'+$(this).attr('name')+', #'+$(this).attr('name')+', #'+$(this).attr('name')+'_minimum, #'+$(this).attr('name')+'_maximum').not('div').closest('.swap-container-custom').show();
            $('.'+$(this).attr('name')+', #'+$(this).attr('name')+', #'+$(this).attr('name')+'_minimum, #'+$(this).attr('name')+'_maximum').not('div').closest('.margin-form').show().prev().show();
        }
    });
}

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
    var feedbiz_context = $('#feedbiz-product-tab');
    var sub_feedbiz_context = $('#feedbiz-sub-tabs');
    var id_product = parseInt($('#id-product').val());
    var id_product_attribute = 0;
    var complex_id_product = null;

    function getComplexProductId() {
        if(id_product == undefined){
            id_product = parseInt($('#id-product').val());
        } 
        if ($('input[name=complex_id_product]:checked', feedbiz_context) && $('input[name=complex_id_product]:checked', feedbiz_context).val() && $('input[name=complex_id_product]:checked', feedbiz_context).val().length)
            complex_id_product = $('input[name=complex_id_product]:checked', feedbiz_context).val();
        else {
            $('input[name=complex_id_product]:first', feedbiz_context).attr('checked', true);
            complex_id_product = id_product + '_0';
        }

        id_product = $('input[name=complex_id_product]:checked', feedbiz_context).attr('data-id-product');
        id_product_attribute = $('input[name=complex_id_product]:checked', feedbiz_context).attr('data-id-product-attribute');

        if (window.console)
            console.log('getComplexProductId', complex_id_product, id_product, id_product_attribute);

        return (complex_id_product);
    }

    getComplexProductId(); // onload

    $('.table.feedbiz-item tbody tr', feedbiz_context).click(function () {       
        var complex_id = getComplexProductId();

        $('.feedbiz-product-options', sub_feedbiz_context).hide();
        $('.feedbiz-sub-tabs', sub_feedbiz_context).hide();
        $('.feedbiz-product-options[data-complex-id="' + complex_id + '"]', sub_feedbiz_context).show();
    });

    /*
     * Delete
     */

    $('.table.feedbiz-item tbody tr td .delete-product-option', feedbiz_context).click(function () {
        var current_tab = $('.marketplace-tab:visible');
        var current_marketplace = $('input[name=feedbiz_context]', current_tab).val();

        if (current_marketplace != 'feedbiz')
            return;

        if (window.console)
            console.log('Delete Product Option - Feed.biz', current_tab, current_marketplace);

        var id_product_attribute = parseInt($('input[name=id_product_attribute]', current_tab).val());

        if (window.console)
            console.log('Data',  id_product_attribute);

        var target_tab = null;

        if (id_product_attribute) // this is a combination, we delete only the subtab
            target_tab = current_tab;
        else // this is the main product, we delete the product option and options for combinations
            target_tab = $('.feedbiz-product-options', feedbiz_context);

        if (window.console)
            console.log('target_tab', target_tab);

        $(':input[name][type=checkbox]', target_tab).attr('checked', false);
        $(':input[name][type=radio]', target_tab).attr('checked', false);
        $(':input[name][type=text]', target_tab).val(null);

        FeedbizAjaxAction('delete-feedbiz', current_tab);

        return (false);
    });

    /*
     * Update
     */

    $('.feedbiz-product-options', feedbiz_context).delegate('input', 'change', function (ev) {
        var target_tab = ev.delegateTarget;

        FeedbizAjaxAction('set-feedbiz', target_tab);
    });


    $('.feedbiz-product-options', feedbiz_context).delegate('.propagate', 'click', function (ev) {
        var target_tab = ev.delegateTarget;
        var classes = $(this).attr('class').split(" ");
        var params = classes[0].split("-");
        var field = params[2];
        var scope = params[3];

        $('#feedbiz-product-tab .debug').html('');

        var global_values = $('input[name]', $('#feedbiz-global-values')).serialize();

        if (window.console)
            console.log(target_tab, field, scope);

        if (!confirm($('#marketplace-text-propagate-cat').val()))  return (false);

        $.ajax({
            type: 'POST',
            dataType: 'jsonp',
            url: $('#feedbiz-product-options-json-url').val() + '&action=propagate&field=' + field + '&scope=' + scope + '&entity=feedbiz&seed=' + new Date().getTime() + '&callback=?',
            data: global_values + '&' + $('input[name], select[name]', target_tab).serialize(),
            success: function (data) {
                if (window.console)
                    console.log(data);

                if (data.output && data.output.length)
                    $('#feedbiz-product-tab .debug').append('<pre>Response:' + data.output + '</pre>');

                if (data.error)
                    showErrorMessage($('#feedbiz-product-options-message-error').val());
                else
                    showSuccessMessage($('#feedbiz-product-options-message-success').val());
            },
            error: function (data) {
                if (window.console)
                    console.log('Error', data);

                showErrorMessage('Error');

                if (data.status && data.status.length)
                    $('#feedbiz-product-tab .debug').append('<pre>Status Code:' + data.status + '</pre>');
                if (data.statusText && data.statusText.length)
                    $('#feedbiz-product-tab .debug').append('<pre>Status Text:' + data.statusText + '</pre>');
                if (data.responseText && data.responseText.length)
                    $('#feedbiz-product-tab .debug').append('<pre>Response:' + data.responseText + '</pre>');
            }
        });
    });


    function FeedbizAjaxAction(action, target_tab) {
        var global_values = $('input[name]', $('#feedbiz-global-values')).serialize();

        $('#feedbiz-product-tab .debug').html('');

        $.ajax({
            type: 'POST',
            dataType: 'jsonp',
            url: $('#feedbiz-product-options-json-url').val() + '&action=' + action + '&seed=' + new Date().getTime() + '&callback=?',
            data: global_values + '&' + $('input[name], select[name]', target_tab).serialize(),
            success: function (data) {

                if (data.output && data.output.length)
                    $('#feedbiz-product-tab .debug').append('<pre>Response:' + data.output + '</pre>');

                if (data.error)
                    showErrorMessage($('#feedbiz-product-options-message-error').val());
                else
                    showSuccessMessage($('#feedbiz-product-options-message-success').val());
            },
            error: function (data) {
                if (window.console)
                    console.log('Error', data);

                showErrorMessage('Error');

                if (data.status && data.status.length)
                    $('#feedbiz-product-tab .debug').append('<pre>Status Code:' + data.status + '</pre>');
                if (data.statusText && data.statusText.length)
                    $('#feedbiz-product-tab .debug').append('<pre>Status Text:' + data.statusText + '</pre>');
                if (data.responseText && data.responseText.length)
                    $('#feedbiz-product-tab .debug').append('<pre>Response:' + data.responseText + '</pre>');
            }
        });
    }

    /*
     * Misc
     */

    $('.marketplace-price', feedbiz_context).blur(function () {
        DisplayPrice($(this));
    });

    function DisplayPrice(obj) {
        var price = obj.val();
        if (price <= 0 || !price) return;

        price = parseFloat(price.replace(',', '.'));
        price = price.toFixed(2);
        if (isNaN(price)) price = '';
        obj.val(price);
    }

});
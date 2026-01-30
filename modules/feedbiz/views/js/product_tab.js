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
    var id_product = parseInt($('#id-product').val());
    var id_product_attribute = 0;
    var complex_id_product = null;
    var min_force_in_stock = 1; 

    $('input[name=force]',feedbiz_context).each(function(){
        $(this).on('change',function(){
            if($.trim($(this).val()) != '' && $(this).val()*1 < min_force_in_stock){
                $(this).val(min_force_in_stock);
            }
        })
    })
    
    $('.feedbiz-product-options', feedbiz_context).not('.main').find('.propagation').hide();


    if (sessionStorage == null) // browser doesn't support sessionStorage, we hide copy/paste functions
    {
        $('.table.feedbiz-item .copy-product-option').parent().hide();
        $('.table.feedbiz-item .paste-product-option').parent().hide();
    }

    function getComplexProductId() {
        
        if(id_product == undefined){
            id_product = parseInt($('#id-product').val());
        }
        if ($('input[name=complex_id_product]:checked', feedbiz_context) && $('input[name=complex_id_product]:checked', feedbiz_context).val() && $('input[name=complex_id_product]:checked', feedbiz_context).val().length)
            complex_id_product = $('input[name=complex_id_product]:checked', feedbiz_context).val();
        else {
            $('input[name=complex_id_product]:first', feedbiz_context).attr('checked', true).parent().parent().trigger('click');
            complex_id_product = id_product + '_0';
        }

        id_product = $('input[name=complex_id_product]:checked', feedbiz_context).attr('data-id-product');
        id_product_attribute = $('input[name=complex_id_product]:checked', feedbiz_context).attr('data-id-product-attribute');

        if (window.console)
            console.log('getComplexProductId - Feed.biz', complex_id_product, id_product, id_product_attribute);

        return (complex_id_product);
    }

    getComplexProductId(); // onload

    /*
     * Products & combinations lines
     */
    var $last_item_click = 0;
    $('.table.feedbiz-item tbody tr', feedbiz_context).click(function (e) {
        var curr = $.now();
        if($last_item_click>=curr-1800){
            return;
        }
        $last_item_click = curr;
        if (e.target.type == 'checkbox')
            return;
        
        if(id_product == undefined){
            id_product = parseInt($('#id-product').val());
        }
        if(id_product == undefined){
            return;
        }

        var feedbiz_context = $('#feedbiz-product-tab');

        if (window.console)
            console.log('Item Selector', complex_id_product, id_product, id_product_attribute);

        $('.table.feedbiz-item tbody tr', feedbiz_context).find('input[type=radio]').attr('checked', false);
        $('.table.feedbiz-item tbody tr', feedbiz_context).removeClass('highlighted');

        $(this).addClass('highlighted');
        $(this).find('input[type=radio]', feedbiz_context).attr('checked', true).change();

        if ($('#feedbiz-product-tab .language-tab-selector.active:visible').length)
            $('#feedbiz-product-tab .language-tab-selector.active:visible').trigger('click');
        else
            $('#feedbiz-product-tab .active:visible').trigger('click');
    });

    /*
     * Marketplace Selector
     */
    $('.marketplace-selector', feedbiz_context).delegate('td', 'click', function (ev) {
        if (!$(this).hasClass('active')) {
            $('td', ev.delegateTarget).removeClass('active');

            var target_tab = $(this).attr('rel');

            if (window.console)
                console.log('Marketplace Selector', ev, target_tab);

            $('td[rel="' + target_tab + '"]:last', ev.delegateTarget).addClass('active');

            $('.marketplace-subtab.active[rel="' + target_tab + '"]', feedbiz_context).fadeOut('slow');// fade out current tab
            $('.marketplace-subtab', feedbiz_context).hide(); // hide all tabs
            $('.marketplace-subtab[rel="' + target_tab + '"]', feedbiz_context).show(); // show selected tab

        }
    });


    /*
     * Edit functions: copy, paste, delete
     */

    $('.table.feedbiz-item .copy-product-option', feedbiz_context).click(function () {
        var current_tab = $('.marketplace-tab:visible');
        var current_marketplace = $('input[name=feedbiz_context]', current_tab).val();

        var inputs = $(':input[name]:not([type=hidden]), :input[rel]:not([type=hidden])', current_tab);
        var input_values = inputs.serializeArray();

        sessionStorage['feedbiz-copy' + current_marketplace] = JSON.stringify(input_values);

        if (window.console)
            console.log('Copy buffer for' + current_marketplace, input_values);

        showSuccessMessage($('#feedbiz-product-options-copy').val());

        return (false);
    });

    $('.table.feedbiz-item .paste-product-option', feedbiz_context).click(function () {
        var current_tab = $('.marketplace-tab:visible');
        var current_marketplace = $('input[name=feedbiz_context]', current_tab).val();

        var paste_buffer = sessionStorage['feedbiz-copy' + current_marketplace];

        if (window.console)
            console.log('Paste buffer for' + current_marketplace, paste_buffer);

        if (paste_buffer != null) {
            var paste_items = JSON.parse(paste_buffer);

            if (window.console)
                console.log(paste_items);

            if (paste_items) {
                $(':input[name][type=checkbox]', current_tab).attr('checked', false);
                $(':input[name][type=radio]', current_tab).attr('checked', false);
                $(':input[name][type=text]', current_tab).val(null);

                $.each(paste_items, function (i, item) {
                    var target_input = $('input[name="' + item.name + '"]', current_tab);

                    console.log('Paste:', item);

                    if ($(target_input).attr('type') == 'text') {
                        $(target_input).val(item.value);
                        if (!$(target_input).parent().is(':visible') && item.value.length) // for bullet points
                            $(target_input).parent().show();
                    }
                    else if ($(target_input).attr('type') == 'checkbox' || $(target_input).attr('type') == 'radio') {
                        $('input[name="' + item.name + '"][value="' + item.value + '"]', current_tab).attr('checked', true);
                    }

                });
                showSuccessMessage($('#feedbiz-product-options-paste').val());

                $('input[name]:visible:first', current_tab).trigger('change');//triggers ajax post
            }

        }
        return (false);
    });


    /*
     * SKU/EAN/UPC Editor
     */
    $('.table.feedbiz-item', feedbiz_context).delegate('.feedbiz-editable', 'click', function () {
        var target_text = $(this).text().trim();
        var target_field = $(this).attr('rel');
        var target_cell = $(this);

        getComplexProductId();

        var global_values = $('input[name]', $('#feedbiz-global-values')).serialize();

        if (!$(':input', target_cell) || !$(':input', target_cell).length) {
            target_cell.html('<input type="text" value="">');

            $(':input', target_cell).val(target_text).focus();
            target_cell.attr('data-initial', target_text);

            $(':input', target_cell).blur(function () {
                var target_cell = $(this).parent();
                var updated_value = $(this).val().trim();
                var pass = true;

                if (target_cell.attr('data-initial') == updated_value)
                    pass = false;

                $(this).parent().text(updated_value);

                if (pass) {
                    var pAjax = {};
                    pAjax.url = $('#feedbiz-product-options-json-url').val() + '&seed=' + new Date().getTime() + '&callback=?';
                    pAjax.type = 'POST';
                    pAjax.data_type = 'jsonp';

                    var params = {
                        'action': 'update-field',
                        'id_product': id_product,
                        'id_product_attribute': id_product_attribute,
                        'field': target_field,
                        'value': updated_value
                    };

                    $.ajax({
                        success: function (data) {
                            if (window.console)
                                console.log(data);

                            if (data.error) {
                                target_cell.html(target_cell.attr('data-initial'));
                                showErrorMessage($('#feedbiz-product-options-message-error').val());
                            }
                            else
                                showSuccessMessage($('#feedbiz-product-options-message-success').val());

                            if (data.output)
                                $('#feedbiz-product-tab .debug').html(data.output);
                        },
                        type: pAjax.type,
                        url: pAjax.url,
                        dataType: pAjax.data_type,
                        data: $.param(params) + '&' + global_values,
                        error: function (data) {
                            if (window.console)
                                console.log('ERROR', data);
                            target_cell.html(target_cell.attr('data-initial'));
                            showErrorMessage($('#feedbiz-product-options-message-error').val());

                            if (data.status && data.status.length)
                                $('#feedbiz-product-tab .debug').append('<pre>Status Code:' + data.status + '</pre>');
                            if (data.statusText && data.statusText.length)
                                $('#feedbiz-product-tab .debug').append('<pre>Status Text:' + data.statusText + '</pre>');
                            if (data.responseText && data.responseText.length)
                                $('#feedbiz-product-tab .debug').append('<pre>Response:' + data.responseText + '</pre>');
                        }
                    });
                }
            });
        }
    });

});


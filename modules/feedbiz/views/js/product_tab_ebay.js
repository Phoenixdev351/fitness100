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
    var sub_feedbiz_context = $('#feedbiz-product-subtab-ebay');
    var id_product = parseInt($('#id-product').val());
    var id_product_attribute = 0;
    var complex_id_product = null;

    $('.ebay-sub-tab', feedbiz_context).not('.main').find('.propagation').hide();
    var $last_item_click = 0;
    function getComplexProductId() {
        var curr = $.now();
        if($last_item_click>=curr-1800){
            return;
        }
        $last_item_click = curr;
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
            console.log('getComplexProductId - eBay', complex_id_product, id_product, id_product_attribute);

        return (complex_id_product);
    }

    getComplexProductId(); // onload


    $('.table.feedbiz-item tbody tr', feedbiz_context).click(function () {
        if ($('.ebay-tab-selector.active:visible', sub_feedbiz_context).length) {
            if (window.console)
                console.log('Item Selector - eBay', feedbiz_context, sub_feedbiz_context);

            $('.ebay-tab-selector.active:visible', sub_feedbiz_context).trigger('click');
        }
        if($('.country-selector', sub_feedbiz_context).length < 1){
            var complex_id = getComplexProductId();
            var iso_code = $('span.ebay-tab-selector', feedbiz_context).attr('data-iso-code');

            $('.ebay-tab', sub_feedbiz_context).hide();
            $('.ebay-tab[data-iso-code="' + iso_code + '"]', sub_feedbiz_context).show();

            $('.ebay-sub-tab', sub_feedbiz_context).hide();
            $('.ebay-sub-tab[data-complex-id="' + complex_id + '"]', sub_feedbiz_context).show();
        }
    });

    $('.marketplace-selector', feedbiz_context).delegate('td', 'click', function () {
        if ($('.ebay-tab-selector.active:visible', sub_feedbiz_context).length) {
            if (window.console)
                console.log('Marketplace Selector - eBay', feedbiz_context, sub_feedbiz_context);

            $('.ebay-tab-selector.active:visible', sub_feedbiz_context).parent().trigger('click');
        }
    });

    $('.table.feedbiz-item tbody tr td .delete-product-option', feedbiz_context).click(function () {
        var current_tab = $('.marketplace-tab:visible');
        var current_marketplace = $('input[name=feedbiz_context]', current_tab).val();

        if (current_marketplace != 'ebay')
            return;

        if (window.console)
            console.log('Delete Product Option - eBay', current_tab, current_marketplace);

        var id_product_attribute = parseInt($('input[name=id_product_attribute]', current_tab).val());
        var region = $(':input[name=region]', current_tab).val();

        if (window.console)
            console.log('Data', region, id_product_attribute);

        var target_tab = null;

        if (id_product_attribute) // this is a combination, we delete only the subtab
            target_tab = current_tab;
        else // this is the main product, we delete the product option and options for combinations
            target_tab = $('.ebay-tab[data-iso-code="' + region + '"]', feedbiz_context);

        if (window.console)
            console.log('target_tab', target_tab);

        $(':input[name][type=checkbox]', target_tab).attr('checked', false);
        $(':input[name][type=radio]', target_tab).attr('checked', false);
        $(':input[name][type=text]', target_tab).val(null);

        eBayAjaxAction('delete-ebay', current_tab);

        return (false);
    });


    /*
     * Country Tabs
     */
    $('.country-selector', sub_feedbiz_context).delegate('td', 'click', function () {

        var complex_id = getComplexProductId();
        var iso_code = $('span.ebay-tab-selector', this).attr('data-iso-code');

        if (window.console)
            console.log('Country Tabs - eBay', sub_feedbiz_context, complex_id, iso_code);

        $('span.ebay-tab-selector', sub_feedbiz_context).removeClass('active');
        $('span.ebay-tab-selector', this).addClass('active');

        $('.ebay-tab', sub_feedbiz_context).hide();
        $('.ebay-tab[data-iso-code="' + iso_code + '"]', sub_feedbiz_context).show();

        $('.ebay-sub-tab', sub_feedbiz_context).hide();
        $('.ebay-sub-tab[data-complex-id="' + complex_id + '"]', sub_feedbiz_context).show();
    });

    /*
     * Bullet Points
     */

    function DeleteBulletPointItem(obj) {
        var target_section = $(obj).parent().parent();
        target_section.find('input').val('').trigger('change');
        target_section.hide();

        console.log($('input[name^="bullet_point"]', target_section));

        var bullet_point_values = $('span[class^="ebay-bullet-container-"] input', target_section).serializeArray();
        if (bullet_point_values) {
            var i = 1;
            $.each(bullet_point_values, function (idx, bullet_point) {

                if (bullet_point.value.length) {
                    $('input[name=bullet_point' + i.toString() + ']', target_section).val(bullet_point.value);
                    i++;
                }
            });
        }
        $('span[class^=ebay-bullet-container]:last input', target_section).val('');
    }

    $('.ebay-sub-tab', feedbiz_context).delegate('.ebay-bullet-point-del', 'click', function () {
        DeleteBulletPointItem($(this));
    });

    $('.ebay-sub-tab', feedbiz_context).delegate('.ebay-bullet-point-add', 'click', function (ev) {
        var target_sub_tab = ev.delegateTarget;

        if (window.console)
            console.log('Feed.biz - Add Bullet Point', target_sub_tab);

        if ($('span[class^="ebay-bullet-container-"]:visible', target_sub_tab).length >= 5) {
            alert($('input[class="amz-text-max-bullet"]', target_sub_tab).val());
            return (false);
        }

        var target_bullet = $('span[class^=ebay-bullet-container]:not(:visible):first', target_sub_tab).show();

        $('input', target_bullet).val('');

        var bullet_point_values = $('span[class^="ebay-bullet-container-"] input', target_sub_tab).serializeArray();

        if (bullet_point_values) {
            var i = 1;
            $.each(bullet_point_values, function (idx, bullet_point) {
                if (bullet_point.value.length) {
                    $('input[name=bullet_point' + i.toString() + ']', target_sub_tab).val(bullet_point.value);
                    i++;
                }
            });
        }
        $('span[class^=ebay-bullet-container]:last input', target_sub_tab).val('');
    });


    /*
     * Save Form
     */
    $('.ebay-sub-tab', feedbiz_context).delegate('input', 'change', function (ev) {

        var target_subtab = ev.delegateTarget;

        eBayAjaxAction('set-ebay', target_subtab);
    });

    function eBayAjaxAction(action, target_subtab) {
        var global_values = $('input[name]', $('#feedbiz-global-values')).serialize();

        $('#feedbiz-product-tab .debug').html('');

        $('input[rel]', target_subtab).each(function () {
            $(this).attr('name', $(this).attr('rel'));
        });

        $.ajax({
            type: 'POST',
            dataType: 'jsonp',
            url: $('#feedbiz-product-options-json-url').val() + '&action=' + action + '&seed=' + new Date().getTime() + '&callback=?',
            data: global_values + '&' + $('input[name], select[name]', target_subtab).serialize(),
            success: function (data) {

                if (data.error)
                    showErrorMessage($('#feedbiz-product-options-message-error').val());
                else
                    showSuccessMessage($('#feedbiz-product-options-message-success').val());

                if (data.output) {
                    $('#feedbiz-product-tab .debug').append('<pre>' + data.output + '</pre>');
                }
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


    $('.ebay-sub-tab', feedbiz_context).delegate('.propagate', 'click', function (ev) {
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

        $('input[rel]', target_tab).each(function () {
            $(this).attr('name', $(this).attr('rel'));
        });

        $.ajax({
            type: 'POST',
            dataType: 'jsonp',
            url: $('#feedbiz-product-options-json-url').val() + '&action=propagate&field=' + field + '&scope=' + scope + '&entity=ebay&seed=' + new Date().getTime() + '&callback=?',
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


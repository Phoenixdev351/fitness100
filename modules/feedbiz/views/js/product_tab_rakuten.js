/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from Common-Services Co., Ltd.
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the SARL SMC is strictly forbidden.
 * In order to obtain a license, please contact us: contact@common-services.com
 * ...........................................................................
 * INFORMATION SUR LA LICENCE D'UTILISATION
 *
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Common-Services Co., Ltd.
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
 * expressement interdite.
 * Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: contact@common-services.com
 * ...........................................................................
 *
 * @package   feedbiz
 * @author    Alexandre D.
 * @copyright Copyright (c) 2011-2022 Common Services Co Ltd - 90/25 Sukhumvit 81 - 10260 Bangkok - Thailand
 * @license   Commercial license
 * Support by mail  :  support.feedbiz@common-services.com
 */

$(document).ready(function () {

    var feedbiz_context = $('#feedbiz-product-tab');
    var sub_feedbiz_context = $('#feedbiz-product-subtab-rakuten');
    var id_product = parseInt($('#id-product').val());
    var id_product_attribute = 0;
    var complex_id_product = null;

    $('.rakuten-sub-tab', feedbiz_context).not('.main').find('.propagation').hide();

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
            console.log('getComplexProductId - rakuten', complex_id_product, id_product, id_product_attribute);

        return (complex_id_product);
    }

    getComplexProductId(); // onload


    var $last_item_click = 0;
    $('.table.feedbiz-item tbody tr', feedbiz_context).click(function () {
        var curr = $.now();
        if($last_item_click>=curr-1800){
            return;
        }
        $last_item_click = curr;
        if ($('.rakuten-tab-selector.active:visible', sub_feedbiz_context).length) {
            if (window.console)
                console.log('Item Selector - rakuten', feedbiz_context, sub_feedbiz_context);

            $('.rakuten-tab-selector.active:visible', sub_feedbiz_context).trigger('click');
        }
        if($('.country-selector', sub_feedbiz_context).length < 1){
            var complex_id = getComplexProductId();
            var iso_code = $('span.rakuten-tab-selector', feedbiz_context).attr('data-iso-code');

            $('.rakuten-tab', sub_feedbiz_context).hide();
            $('.rakuten-tab[data-iso-code="' + iso_code + '"]', sub_feedbiz_context).show();

            $('.rakuten-sub-tab', sub_feedbiz_context).hide();
            $('.rakuten-sub-tab[data-complex-id="' + complex_id + '"]', sub_feedbiz_context).show();
        }
    });

    $('.marketplace-selector', feedbiz_context).delegate('td', 'click', function () {
        if ($('.rakuten-tab-selector.active:visible', sub_feedbiz_context).length) {
            if (window.console)
                console.log('Marketplace Selector - rakuten', feedbiz_context, sub_feedbiz_context);

            $('.rakuten-tab-selector.active:visible', sub_feedbiz_context).parent().trigger('click');
        }
    });

    $('.table.feedbiz-item tbody tr td .delete-product-option', feedbiz_context).click(function () {
        var current_tab = $('.marketplace-tab:visible');
        var current_marketplace = $('input[name=feedbiz_context]', current_tab).val();

        if (current_marketplace != 'rakuten')
            return;

        if (window.console)
            console.log('Delete Product Option - rakuten', current_tab, current_marketplace);

        var id_product_attribute = parseInt($('input[name=id_product_attribute]', current_tab).val());
        var region = $(':input[name=region]', current_tab).val();

        if (window.console)
            console.log('Data', region, id_product_attribute);

        var target_tab = null;

        if (id_product_attribute) // this is a combination, we delete only the subtab
            target_tab = current_tab;
        else // this is the main product, we delete the product option and options for combinations
            target_tab = $('.rakuten-tab[data-iso-code="' + region + '"]', feedbiz_context);

        if (window.console)
            console.log('target_tab', target_tab);

        $(':input[name][type=checkbox]', target_tab).attr('checked', false);
        $(':input[name][type=radio]', target_tab).attr('checked', false);
        $(':input[name][type=text]', target_tab).val(null);

        rakutenAjaxAction('delete-rakuten', current_tab);

        return (false);
    });


    /*
     * Country Tabs
     */
    $('.country-selector', sub_feedbiz_context).delegate('td', 'click', function () {

        var complex_id = getComplexProductId();
        var iso_code = $('span.rakuten-tab-selector', this).attr('data-iso-code');

        if (window.console)
            console.log('Country Tabs - rakuten', sub_feedbiz_context, complex_id, iso_code);

        $('span.rakuten-tab-selector', sub_feedbiz_context).removeClass('active');
        $('span.rakuten-tab-selector', this).addClass('active');

        $('.rakuten-tab', sub_feedbiz_context).hide();
        $('.rakuten-tab[data-iso-code="' + iso_code + '"]', sub_feedbiz_context).show();

        $('.rakuten-sub-tab', sub_feedbiz_context).hide();
        $('.rakuten-sub-tab[data-complex-id="' + complex_id + '"]', sub_feedbiz_context).show();
    });

    /*
     * Bullet Points
     */

    function DeleteBulletPointItem(obj) {
        var target_section = $(obj).parent().parent();
        target_section.find('input').val('').trigger('change');
        target_section.hide();

        console.log($('input[name^="bullet_point"]', target_section));

        var bullet_point_values = $('span[class^="rakuten-bullet-container-"] input', target_section).serializeArray();
        if (bullet_point_values) {
            var i = 1;
            $.each(bullet_point_values, function (idx, bullet_point) {

                if (bullet_point.value.length) {
                    $('input[name=bullet_point' + i.toString() + ']', target_section).val(bullet_point.value);
                    i++;
                }
            });
        }
        $('span[class^=rakuten-bullet-container]:last input', target_section).val('');
    }

    $('.rakuten-sub-tab', feedbiz_context).delegate('.rakuten-bullet-point-del', 'click', function () {
        DeleteBulletPointItem($(this));
    });

    $('.rakuten-sub-tab', feedbiz_context).delegate('.rakuten-bullet-point-add', 'click', function (ev) {
        var target_sub_tab = ev.delegateTarget;

        if (window.console)
            console.log('Feed.biz - Add Bullet Point', target_sub_tab);

        if ($('span[class^="rakuten-bullet-container-"]:visible', target_sub_tab).length >= 5) {
            alert($('input[class="amz-text-max-bullet"]', target_sub_tab).val());
            return (false);
        }

        var target_bullet = $('span[class^=rakuten-bullet-container]:not(:visible):first', target_sub_tab).show();

        $('input', target_bullet).val('');

        var bullet_point_values = $('span[class^="rakuten-bullet-container-"] input', target_sub_tab).serializeArray();

        if (bullet_point_values) {
            var i = 1;
            $.each(bullet_point_values, function (idx, bullet_point) {
                if (bullet_point.value.length) {
                    $('input[name=bullet_point' + i.toString() + ']', target_sub_tab).val(bullet_point.value);
                    i++;
                }
            });
        }
        $('span[class^=rakuten-bullet-container]:last input', target_sub_tab).val('');
    });


    /*
     * Save Form
     */
    $('.rakuten-sub-tab', feedbiz_context).delegate('input', 'change', function (ev) {

        var target_subtab = ev.delegateTarget;

        rakutenAjaxAction('set-rakuten', target_subtab);
    });
    
    $('.rakuten-sub-tab', feedbiz_context).delegate('select', 'change', function (ev) {

        var target_subtab = ev.delegateTarget;

        rakutenAjaxAction('set-rakuten', target_subtab);
    });

    function rakutenAjaxAction(action, target_subtab) {
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


    $('.rakuten-sub-tab', feedbiz_context).delegate('.propagate', 'click', function (ev) {
        var target_tab = ev.delegateTarget;
        var classes = $(this).attr('class').split(" ");
        var params = classes[0].split("-");
        var field = params[2];
        var scope = params[3];

        console.log(target_tab, classes, params, field, scope);

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
            url: $('#feedbiz-product-options-json-url').val() + '&action=propagate&field=' + field + '&scope=' + scope + '&entity=rakuten&seed=' + new Date().getTime() + '&callback=?',
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
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
    var feedbiz_options = $('meta[name="feedbiz-options"]').attr('content');
    var feedbiz_options_json = $('meta[name="feedbiz-options-json"]').attr('content');

    if (!feedbiz_options.length) return (false);

    var product_form = null;

    // PS 1.4 or 1.5
    if ($('#product').length) {
        product_form = $('#product');
        feedbiz_Init();
    }
    else {
        product_form = $('#product_form');
        setTimeout(feedbiz_Init, 2000);
    }

    function feedbiz_Init() {
        $.ajax({
            type: 'GET',
            url: feedbiz_options,
            data: $('form[name=product]').attr('action') + '&rand=' + new Date().valueOf(),
            beforeSend: function (data) {
            },
            success: function (data) {

                // PS 1.5
                if ($('#step1 .separation:eq(2)').length) {
                    $('#step1 .separation:eq(2)').parent().after().append(data);
                    $('#step1 .separation:eq(2)').parent().after().append('<div class="separation"></div>');
                }
                else if ($('#step1 hr:eq(1)').length) {
                    // PS < 1.5
                    $('#step1 hr:eq(1)').parent().parent().after(data);
                }
                ProductOptionInit();
            }
        });
    }

    function ProductOptionInit() {
        $('#productfeedbiz-save-options').click(function () {
            $.ajax({
                type: 'POST',
                url: feedbiz_options_json,
                data: product_form.serialize() + '&action=set&rand=' + new Date().getTime() + '&callback=?',
                beforeSend: function () {
                    $('#result-feedbiz').html('').hide()
                },
                success: function (data) {
                    $('#result-feedbiz').html(data).show()
                }
            });


        });


        // Disabling Products
        //
        $('.feedbiz-propagate-disable-cat').click(function () {

            if (!confirm($('#feedbiz-text-propagate-cat').val()))  return (false);

            $.ajax({
                type: 'POST',
                url: feedbiz_options_json,
                data: product_form.serialize() + '&action=propagate-disable-cat&rand=' + new Date().valueOf() + '&callback=?',
                beforeSend: function () {
                    $('#feedbiz-extra-disable-loader').show();
                    $('#result-feedbiz').html('').hide()
                },
                success: function (data) {
                    $('#feedbiz-extra-disable-loader').hide();
                    $('#result-feedbiz').html(data).show()
                }
            });
        });

        $('.feedbiz-propagate-disable-shop').click(function () {

            if (!confirm($('#feedbiz-text-propagate-shop').val()))  return (false);

            $.ajax({
                type: 'POST',
                url: feedbiz_options_json,
                data: product_form.serialize() + '&action=propagate-disable-shop&rand=' + new Date().valueOf() + '&callback=?',
                beforeSend: function () {
                    $('#feedbiz-extra-disable-loader').show();
                    $('#result-feedbiz').html('').hide()
                },
                success: function (data) {
                    $('#feedbiz-extra-disable-loader').hide();
                    $('#result-feedbiz').html(data).show()
                }
            });
        });

        $('.feedbiz-propagate-disable-manufacturer').click(function () {

            if (!confirm($('#feedbiz-text-propagate-man').val()))  return (false);

            $.ajax({
                type: 'POST',
                url: feedbiz_options_json,
                data: product_form.serialize() + '&action=propagate-disable-manufacturer&rand=' + new Date().valueOf() + '&callback=?',
                beforeSend: function () {
                    $('#feedbiz-extra-disable-loader').show();
                    $('#result-feedbiz').html('').hide()
                },
                success: function (data) {
                    $('#feedbiz-extra-disable-loader').hide();
                    $('#result-feedbiz').html(data).show()
                }
            });
        });


        // Force Product force
        //
        $('.feedbiz-propagate-force-cat').click(function () {

            if (!confirm($('#feedbiz-text-propagate-cat').val()))  return (false);

            $.ajax({
                type: 'POST',
                url: feedbiz_options_json,
                data: product_form.serialize() + '&action=propagate-force-cat&rand=' + new Date().valueOf() + '&callback=?',
                beforeSend: function () {
                    $('#feedbiz-extra-force-loader').show();
                    $('#result-feedbiz').html('').hide()
                },
                success: function (data) {
                    $('#feedbiz-extra-force-loader').hide();
                    $('#result-feedbiz').html(data).show()
                }
            });
        });

        $('.feedbiz-propagate-force-shop').click(function () {

            if (!confirm($('#feedbiz-text-propagate-shop').val()))  return (false);

            $.ajax({
                type: 'POST',
                url: feedbiz_options_json,
                data: product_form.serialize() + '&action=propagate-force-shop&rand=' + new Date().valueOf() + '&callback=?',
                beforeSend: function () {
                    $('#feedbiz-extra-force-loader').show();
                    $('#result-feedbiz').html('').hide()
                },
                success: function (data) {
                    $('#feedbiz-extra-force-loader').hide();
                    $('#result-feedbiz').html(data).show()
                }
            });
        });

        $('.feedbiz-propagate-force-manufacturer').click(function () {

            if (!confirm($('#feedbiz-text-propagate-man').val()))  return (false);

            $.ajax({
                type: 'POST',
                url: feedbiz_options_json,
                data: product_form.serialize() + '&action=propagate-force-manufacturer&rand=' + new Date().valueOf() + '&callback=?',
                beforeSend: function () {
                    $('#feedbiz-extra-manufacturer-loader').show();
                    $('#result-feedbiz').html('').hide()
                },
                success: function (data) {
                    $('#feedbiz-extra-manufacturer-loader').hide();
                    $('#result-feedbiz').html(data).show()
                }
            });
        });


        $('#productfeedbiz-options').click(function () {
            var image = $('#feedbiz-toggle-img');

            var newImage = image.attr('rel');
            var oldImage = image.attr('src');

            image.attr('src', newImage);
            image.attr('rel', oldImage);

            if ($('.feedbiz-details').is(':visible'))
                $('.feedbiz-details').hide();
            else
                $('.feedbiz-details').show();

        });

        $('input[name^="feedbiz-price-"]').blur(function () {
            DisplayPrice($(this));
        });

        function DisplayPrice(obj) {
            var price = obj.val();
            if (price <= 0 || !price)
                return;
            price = parseFloat(price.replace(',', '.'));

            if (isNaN(price))
                price = 0;

            price = price.toFixed(2);

            obj.val(price);
        }

        function comments(text) {
            text.val(text.val().substr(0, 200));
            var left = 200 - parseInt(text.val().length);
            $('#c-count').html(left);
            return (true);
        }

        $('input[name^="feedbiz-text-"]').keypress(function () {
            return (comments($(this)));
        });
        $('input[name^="feedbiz-text-"]').change(function () {
            return (comments($(this)));
        });

    }
});


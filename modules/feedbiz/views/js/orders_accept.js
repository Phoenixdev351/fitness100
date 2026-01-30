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

    $('#accept-orders').click(function () {
        var url = $('#accept-orders-url').val();

        if (window.console) {
            console.log("Import Orders");
            console.log("URL is :" + url);
        }
        $('#accept-loader').show();
        $('#accept-orders-result').html('').hide();
        $('#accept-orders-error').html('').hide();
        $('#accept_order_list').html('').hide();
        $('#accept-orders-success').hide();

        $('#console').html('').show();

        $.ajax({
            type: 'POST',
            url: url + '&callback=?',
            data: $('#accept-orders-form').serialize() + '&context_key=' + $('input[name=context_key]').val(),
            dataType: 'json',
            success: function (data) {
                $('#accept-loader').hide();
                $('#accept-orders-success').html('');

                if (window.console)
                    console.log(data);

                if (typeof(data.stdout) != 'undefined')
                    $('#console').append(data.stdout);

                if (data.error) {
                    $('#accept-orders-result').hide();
                    $('#accept-orders-hr').show();
                    $('#accept-orders-error').show();
                    $('#accept-orders-error').append(data.output);
                    $('#accept-order').hide();
                    $.each(data.errors, function (e, errormsg) {
                        $('#accept-orders-error').append(errormsg);
                    });
                    $.each(data.output, function (o, output) {
                        $('#accept-orders-result').append(output);
                    });
                }
                else {

                    $('#accept-orders-error').hide();
                    $('#accept-orders-hr').show();
                    $('#accept-orders-result').show();

                    $.each(data.output, function (o, output) {
                        $('#accept-orders-result').append(output);
                    });

                    if (data.orders) {
                        $('#accept-table').show();
                        $('#accept-order').show();
                        $('#accept_order_list').show();

                        $.each(data.orders, function (o, order) {
                            $('#accept_order_list').append(order);
                        });
                        InitOrder();
                    }

                }

            },
            error: function (data) {
                $('#accept-loader').hide();
                $('#accept-orders-error').show();
                $('#accept-orders-error').html('AJAX Error');
                if (window.console)
                    console.log(data);
            }
        });
        return (false);

    });

    function InitOrder() {
        $('#accept-order').unbind('click');
        $('#accept-order').click(function () {
            var url = $('#accept-order-url').val();
            var order_list = $('input[name="selected_orders[]"]').serialize();

            if (window.console) {
                console.log("Import Orders");
                console.log("URL is :" + url);
            }

            if (!order_list.length) {
                alert($('#text-select-orders').val());
                return (false);
            }
            $('#accept-loader').show();
            $('#console').html('').show();

            if (window.console)
                console.log("Orders:" + order_list);

            $.ajax({
                type: 'POST',
                url: url + '&callback=?',
                data: $('#accept-orders-form').serialize() + '&context_key=' + $('input[name=context_key]').val(),
                dataType: 'json',
                success: function (data) {
                    $('#accept-orders-success').html('');
                    $('#accept-orders-result').hide();
                    $('#accept-orders-hr').hide();

                    $('#accept-loader').hide();

                    if (data.stdout)
                        $('#console').append(data.stdout);

                    if (data)
                        console.log(data);

                    if (data.error) {
                        $('#accept-orders-hr').show();
                        $('#accept-orders-error').show();
                        $('#accept-order').hide();
                        $.each(data.errors, function (e, errormsg) {
                            $('#accept-orders-error').append(errormsg);
                        });
                    }
                    if (data.count) {
                        $('#accept-orders-success').show();

                        $.each(data.output, function (o, output) {
                            $('#accept-orders-success').append(output);
                        });
                        $.each(data.orders, function (o, order) {
                            $('#o-' + o).attr('disabled', true);
                        });
                    }
                },
                error: function (data) {
                    $('#accept-loader').hide();
                    $('#accept-orders-success').html('');
                    $('#accept-orders-result').hide().html('');
                    $('#accept-orders-error').html('');
                    $('#order_list').hide().html('');
                    $('#accept-loader').hide();
                    $('#accept-orders-error').html('AJAX Error').show();

                    if (window.console)
                        console.log(data);
                }
            });


        });

    }


});

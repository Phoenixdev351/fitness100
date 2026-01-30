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

    $('#import-orders').click(function () {
        var url = $('#import-orders-url').val();

        if (window.console) {
            console.log("Import Orders");
            console.log("URL is :" + url);
        }
        $('#import-loader').show();
        $('#import-orders-result').html('').hide();
        $('#import-orders-error').html('').hide();
        $('#order_list').html('').hide();
        $('#console').html('').show();

        $.ajax({
            type: 'POST',
            url: url + '&callback=?',
            data: $('#import-orders-form').serialize() + '&context_key=' + $('input[name=context_key]').val(),
            dataType: 'json',
            success: function (data) {
                $('#import-loader').hide();
                $('#import-orders-success').html('');

                if (window.console) {
                    console.log("Success");
                    console.log(data);
                }

                if (data.stdout)
                    $('#console').append(data.stdout);

                if (data.error) {
                    $('#import-orders-error').show();
                    $('#import-orders-error').append(data.output);

                    $.each(data.errors, function (e, errormsg) {
                        $('#import-orders-error').append(errormsg);
                    });
                }
                if (data.orders) {
                    if (data.output && !data.error) {
                        $('#import-orders-hr').show();
                        $('#import-orders-result').show().html('');
                    }

                    $('#import-table').show();
                    $('#order_list').html('').show();
                    $('#import-order').show();

                    $.each(data.orders, function (o, order) {
                        if (window.console)
                            console.log(o);

                        $('#order_list').append(order);
                    });
                    $.each(data.output, function (o, output) {
                        $('#import-orders-result').append(output);
                    });
                    InitOrder();
                }


            },
            error: function (data) {
                $('#import-loader').hide();
                $('#import-orders-error').show();
                $('#import-orders-error').html('AJAX Error');
                if (window.console)
                    console.log(data);
            }
        });
        return (false);

    });

    function InitOrder() {
        $('#import-order').unbind('click');
        $('#import-order').click(function () {
            var url = $('#import-order-url').val();
            var order_list = $('input[name="selected_orders[]"]').serialize();

            if (window.console) {
                console.log("Import Orders");
                console.log("URL is :" + url);
            }

            if (!order_list.length) {
                alert($('#text-select-orders').val());
                return (false);
            }
            $('#import-loader').show();

            if (window.console)
                console.log("Orders:" + order_list);

            $.ajax({
                type: 'POST',
                url: url + '&callback=?',
                data: $('#import-orders-form').serialize() + '&context_key=' + $('input[name=context_key]').val(),
                dataType: 'json',
                success: function (data) {
                    $('#import-orders-success').html('');
                    $('#import-orders-result').hide();
                    $('#import-orders-hr').hide();
                    /*
                     if ( data.msg )
                     $('#import-orders-result').show().html(data.msg);
                     */
                    $('#import-loader').hide();
                    if (data.error) {
                        $('#import-orders-error').show();

                        $.each(data.errors, function (e, errormsg) {
                            $('#import-orders-error').append(errormsg);
                        });
                    }
                    if (data.count) {
                        $.each(data.output, function (o, output) {
                            $('#import-orders-success').append(output);
                        });
                        $.each(data.orders, function (o, order) {
                            $('#o-' + o).attr('disabled', true);
                        });
                    }
                },
                error: function (data) {
                    $('#import-loader').hide();
                    $('#import-orders-success').html('');
                    $('#import-orders-result').hide().html('');
                    $('#import-orders-error').html('');
                    $('#order_list').hide().html('');
                    $('#import-loader').hide();
                    $('#import-orders-error').html('AJAX Error').show();

                    if (window.console)
                        console.log(data);
                }
            });


        });

    }


});

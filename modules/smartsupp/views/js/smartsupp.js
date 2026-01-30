/*
 * Smartsupp Live Chat integration module.
 * 
 * @package   Smartsupp
 * @author    Smartsupp <vladimir@smartsupp.com>
 * @link      http://www.smartsupp.com
 * @copyright 2016 Smartsupp.com
 * @license   GPL-2.0+
 *
 * Plugin Name:       Smartsupp Live Chat
 * Plugin URI:        http://www.smartsupp.com
 * Description:       Adds Smartsupp Live Chat code to PrestaShop.
 * Version:           2.2.0
 * Author:            Smartsupp
 * Author URI:        http://www.smartsupp.com
 * Text Domain:       smartsupp
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
*/

jQuery(document).ready( function($) {
    
    function page_refresh(errMsg) {
        var control = $( "#SMARTSUPP_OPTIONAL_API" ).next();
        var text = control.html();
        control.css('font-style', 'normal');
        control.html(text.replace('#', '<a href="https://developers.smartsupp.com/?utm_source=Prestashop&utm_medium=integration&utm_campaign=link" target="_blank">Smartsupp API</a>'));
        
        if ($( "#smartsupp_key" ).val() === "") {
            $( "#smartsupp_configuration" ).hide();
            $( "#configuration_form.smartsupp" ).hide();
            
            if (errMsg === true) {
                $( "#smartsupp_create_account" ).hide();
                $( "#smartsupp_connect_account" ).show();
            } else {
                $( "#smartsupp_connect_account" ).hide();
                $( "#smartsupp_create_account" ).show();
            }
        }
        else {
            $( "#smartsupp_create_account" ).hide();
            $( "#smartsupp_connect_account" ).hide();
            $( "#smartsupp_configuration" ).show();
            $( "#configuration_form.smartsupp" ).show();        
        }        
    } 
    page_refresh();
    $( "#configuration_form.smartsupp #SMARTSUPP_OPTIONAL_API" ).height("117px");   
    
    $( "#connect_existing_account_btn1, #connect_existing_account_btn2" ).click(function() {
        $("#smartsupp_configuration").next('.bootstrap').hide();
        $("div.messages").hide();
        $( "#smartsupp_create_account" ).hide();
        $( "#smartsupp_connect_account" ).show();
    });
    
    $( "#create_account_btn1, #create_account_btn2" ).click(function() {
        $("#smartsupp_configuration").next('.bootstrap').hide();
        $("div.messages").hide();
        $( "#smartsupp_connect_account" ).hide();
        $( "#smartsupp_create_account" ).show();
    });
    
    $( "#connect_existing_account_do" ).click(function() {
        $("#smartsupp-login-alerts").hide();
        var errMsg = false;

        $.ajax({
            url: ajax_controller_url,
            async: false,
            type: 'POST',
            data: {
                action: 'login',
                email: $( "#smartsupp_connect_account #SMARTSUPP_EMAIL" ).val(),
                password: $( "#smartsupp_connect_account #SMARTSUPP_PASSWORD" ).val()
            },
            dataType: 'json',
            headers: { "cache-control": "no-cache" },
            success: function (response) {
                if (response.error) {
                    $("#smartsupp-login-alerts").show();

                    if (response.message) {
                        $("#smartsupp-login-alert").html(response.message);
                    } else {
                        $("#smartsupp-login-alert").html(smartsupp.genericAjaxErrorMessage);
                    }
                    errMsg = true;

                    return;
                }

                $("input#smartsupp_key").val(response.key);
                $("#smartsupp_configuration p.email").html(response.email);
                $("div.messages").hide();
                errMsg = false;
            },
            error: function (response) {
                console.error(response);
                errMsg = true;

                if (smartsupp.genericAjaxErrorMessage) {
                    $("#smartsupp-login-alerts").show();
                    $("#smartsupp-login-alert").html(smartsupp.genericAjaxErrorMessage);
                }
            }
        });        
        page_refresh(errMsg);
    });

    $( "#create_account_do" ).click(function() {
        $("#smartsupp_create_account .alerts").hide();

        $.ajax({
            url: ajax_controller_url,
            async: false,
            type: 'POST',
            data: {
                action: 'create', 
                email: $( "#smartsupp_create_account #SMARTSUPP_EMAIL" ).val(), 
                password: $( "#smartsupp_create_account #SMARTSUPP_PASSWORD" ).val(),
                marketing: $( "#smartsupp_create_account #SMARTSUPP_MKT" ).val()
            },
            dataType: 'json',
            headers: { "cache-control": "no-cache" },
            success: function (response) {
                if (response.error) {
                    $("#smartsupp_create_account .alerts").show();

                    if (response.message) {
                        $("#smartsupp_create_account .alerts .alert").html(response.message);
                    } else {
                        $("#smartsupp_create_account .alerts .alert").html(smartsupp.genericAjaxErrorMessage);
                    }
                }

                $("input#smartsupp_key").val(response.key);
                $("#smartsupp_configuration p.email").html(response.email);
            },
            error: function (response) {
                console.error(response);

                if (smartsupp.genericAjaxErrorMessage) {
                    $("#smartsupp_create_account .alerts").show();
                    $("#smartsupp_create_account .alerts .alert").html(smartsupp.genericAjaxErrorMessage);
                }
            }
        });        
        page_refresh();    
    });
        
    $( "#deactivate_chat_do" ).click(function() {
        $("#smartsupp_configuration").next('.bootstrap').hide();

        $.ajax({
            url: ajax_controller_url,
            async: false,
            type: 'POST',
            data: {
                action: 'deactivate'
            },
            dataType: 'json',
            headers: { "cache-control": "no-cache" },
            success: function (response) {
                $("input#smartsupp_key").val(response.key);
                $("#smartsupp_configuration p.email").html(response.email);
                page_refresh();
            },
            error: function (response) {
                console.error(response);

                // Since there is no error container in the template, alerting for now
                alert(smartsupp.genericAjaxErrorMessage);

                return;
            }
        });
    });
});    
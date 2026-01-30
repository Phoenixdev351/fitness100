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

    $('.hint').show();


    // AJAX Checker
    //
    $(function () {
        var pAjax = {};
        pAjax.url = $('#env_check_url').val();
        pAjax.type = 'GET';
        pAjax.data_type = 'jsonp';
        pAjax.data = null;

        if (window.console)
            console.log(pAjax);

        if(typeof pAjax.url === 'undefined')
            return false;

        var to_display = '#error-' + $('#env_check_url').attr('id');

        $.ajax({
            type: pAjax.type,
            url: pAjax.url,
            dataType: pAjax.data_type,
            success: function (data) {
                if (window.console)
                    console.log(data);

                if (!data.pass) {
                    $('#fb-env-infos').show();
                    $(to_display).show();
                }
            },
            error: function (data) {
                if (window.console)
                    console.log(data);
                $('#fb-env-infos').show();
                $(to_display).show();
            }
        });
    });


    $('#support-informations-prestashop, #support-informations-php').click(function () {
        $('.support-informations-loader').show();

        $.ajax({
            type: 'POST',
            url: $(this).attr('rel') + '&callback=?',
            success: function (data) {
                $('.support-informations-loader').hide();
                $('#support-informations-content').html(data).slideDown();
            },
            error: function (data) {
                $('.support-informations-loader').hide();
                $('#support-informations-content').html(data).slideDown();
            }
        });
    });

    // max_input_vars checker
    //
    function EnvCheck() {
        var max_input_vars = parseInt($('#max_input_vars').val());
        var cur_input_vars = $('input, select, textarea, button').length;

        if (max_input_vars && max_input_vars < cur_input_vars) {
            $('#error-max_input_vars').show();
            $('#fb-env-infos').show();
        }
        if ($('#fb-env-infos div > div[rel="toshow"]').length) {
            $('#fb-env-infos').show();
        }
    }

    EnvCheck();


    $('input[id^="active-"]').click(function (e) {
        var result = $(this).attr('id').match('^(.*)-(.*)$');
        var lang = result[2];
        var currentTab = $('#menudiv-' + lang);

        if (!parseInt(currentTab.find('input[name^="actives"]:checked').val()))
            tabInactive(currentTab);
        else
            tabActive(currentTab);

    });
    function tabActive(tab) {
        $(tab).find('input, select, textarea').each(function () {
            if ($(this).attr('type') == 'checkbox')
                return (true);
            if ($(this).attr('name') == 'submit')
                return (true);
            $(this).attr('readonly', false).attr('disabled', false).removeClass('disabled');
        });
    }

    function tabInactive(tab) {
        $(tab).find('input, select, textarea').each(function () {
            if ($(this).attr('type') == 'checkbox')
                return (true);
            if ($(this).attr('name') == 'submit')
                return (true);
            $(this).attr('readonly', 'readonly').attr('disabled', 'disabled').addClass('disabled');
        });
    }    
        
    $("#btn_next").on('click', function(e){
        e.preventDefault();
        e.stopPropagation();

        var currentTab =  $("#menuTab").find(".selected").attr('id');
        var menu =  $("#menuTab").find("li");

        if (window.console) console.log($(this).attr('id'));

        if (!$('#feedbiz_form').valid()) {
            return(false);
        }

        menu.each(function(){
            if(currentTab == $(this).attr('id')){
                var $self = $(this);
                var $next = $self.next();
                if(typeof $next.attr('id') !== 'undefined'){
                    var value = $next.attr('id').match('^(.*)-(.*)$');
                    var lang = value[2];
                    $self.removeClass('selected');
                    $next.addClass('selected');
                    $('div[id^="menudiv-"]').hide();
                    $('div[id^="menudiv-' + lang + '"]').show();
                }
            }
        });
    });
    
    $("#btn_back").on('click', function(){
        var currentTab =  $("#menuTab").find(".selected").attr('id');
        var menu =  $("#menuTab").find("li");

        if (window.console) console.log($(this).attr('id'));
        menu.each(function(){
            if(currentTab == $(this).attr('id')){
                var $self = $(this);
                var $prev = $self.prev();
                if(typeof $prev.attr('id') !== 'undefined'){
                    var value = $prev.attr('id').match('^(.*)-(.*)$');
                    var lang = value[2];
                    $self.removeClass('selected');
                    $prev.addClass('selected');
                    $('div[id^="menudiv-"]').hide();
                    $('div[id^="menudiv-' + lang + '"]').show();
                }
            }
        });
    });

    $(".btn-step-back").on("click", function(){
        var self = $(this);
        var itself  = self.closest('[id^=menudiv]').attr('id');
        var back = self.attr('rel');

        if (window.console) console.log($(this).attr('id'));
        $("#"+itself).hide();
        $("#menudiv-"+back).show();
        
        var currentTab =  $("#menuTab").find(".selected").attr('id');
        $("#"+currentTab).removeClass('selected');
        $("#menu-"+back).addClass('selected');
    });
    
    $(".btn-step-next, #cdiscount-already").on("click", function(e){
        var self = $(this);
        var itself  = self.closest('[id^=menudiv]').attr('id');
        var next = self.attr('rel');

        e.preventDefault();

        if (window.console) console.log($(this).attr('id'));

        if (!$('#feedbiz_form').valid()) {
            $('#menu-info_account').click();
            return(false);
        }

        if (window.console) console.log($(this).attr('id'));
        if(typeof self.attr('send_survey') !== 'undefined'){
            send_survey();
        }

        $("#"+itself).hide();
        $("#menudiv-"+next).show();
        
        var currentTab =  $("#menuTab").find(".selected").attr('id');
        $("#"+currentTab).removeClass('selected');
        $("#menu-"+next).addClass('selected');
    });

    $('li[id^="menu-"]').click(function (e) {
        var result = $(this).attr('id').match('^(.*)-(.*)$');
        var lang = result[2];
        
         if(typeof $(this).attr('send_survey') !== 'undefined'){
             e.preventDefault();
             e.stopPropagation();

             if (window.console) console.log($(this).attr('id'));

             if (!$('#feedbiz_form').valid()) {
                 if (window.console) console.log('validation failed');
                 return(false);
             }
            send_survey();
        }

        $('input[name=selected_tab]').val(lang);

        if (!$(this).hasClass('selected')) {
            $('li[id^="menu-"]').removeClass('selected');
            $(this).addClass('selected');
            $('div[id^="menudiv-"]').hide();
            $('div[id^="menudiv-' + lang + '"]').show();
        }
    });

    $('input[name=wcheckme]').click(function () {

        $('input[id^=warnings]').each(function () {
            if ($(this).attr('checked'))
                $(this).attr('checked', false);
            else
                $(this).attr('checked', 'checked');
        });

    });
    
    $('input[class=category]').click(function () {
    
        checkedCategory ($(this));  

    });
    
    function checkedCategory (element) {
    
        if($(element).attr('data-parent')){

            var id_parent = $(element).attr('data-parent');     
            var parent = $('#menudiv-categories').find($('#category_'+id_parent));  

            if(parent.length > 0){
                if( $(element).is(':checked') ){
                    if (!$(parent).attr('checked')) {

                        $(parent).attr('checked', 'checked');

                        if( checkParent( $(parent) ) ){
                            checkedCategory ($(parent));
                        }

                    }
                } else {
                    if(!$('input[data-parent="'+id_parent+'"]').is(':checked')){
                        $(parent).attr('checked', false);
                    }
                }
            }
        }
    }
    
    function checkParent (element) {
    
        var id_parent = $(element).attr('data-parent');     
        var parent = $('#menudiv-categories').find($('#category_'+id_parent));  

        return parent.length > 0;
    }
    
    $('input[name=checkme]').click(function () {
        var state = Boolean($(this).attr('checked'));
        $('input[id^=category]').not(':disabled').attr('checked', state);
    });

    $('#connection-check').click(function () {

        var username = $('#username').val();
        var feedbiz_token = $('#feedbiz_token').val();
        var ps_token = $('#ps_token').val();

        $('#connection-check-loader').show();

        $.ajax({
            type: 'GET',
            url: $('#check_url').val() + '?callback=?',
            dataType: 'json',
            data: {
                'id_lang': $('#id_lang').val(),
                'action': 'check',
                'preprod': ($('#preproduction').is(':checked') ? '1' : '0'),
                'debug': ($('#debug').is(':checked') ? '1' : '0'),
                'username': username,
                'fbtoken': feedbiz_token,
                'pstoken': ps_token
            },
            success: function (data) {
                $('#connection-check-loader').hide();
                $('#feedbiz-response').html('');

                if (window.console)
                    console.log(data);

                if (data.alert) {
                    alert(data.alert);
                    return (false);
                }

                if (data.message && !data.error) {
                    $('#feedbiz-response').html('<div class="' + $('#css_class_success').val() + '">' + data.message + '</div>');
                }
                else if (data.message && data.error) {
                    $('#feedbiz-response').html('<div class="' + $('#css_class_warn').val() + '">' + data.message + '</div>');
                }
                if ($('#debug').is(':checked') && data.debug) {
                    $('#feedbiz-response').after('<hr /><pre>' + data.debug + '</pre>');
                }
            },
            error: function () {
                $('#connection-check-loader').hide();
                $('#feedbiz-response').html('<div class="' + $('#css_class_error').val() + '">Connection Error</div>');
            }
        });

    });

    function comments() {
        $('#comments').val($('#comments').val().substr(0, 200));
        var left = 200 - parseInt($('#comments').val().length);
        $('#c-count').html(left);
        return (true);
    }

    $('#comments').keypress(function () {
        comments();
    });
    $('#comments').change(function () {
        comments();
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

    $('.price').blur(function () {
        DisplayPrice($(this));
    });

    $('input[name="validateForm"]').click(function () {
        if ($('select[name="orderstate[feedbiz_CA]"] :selected').index() == 0) {
            alert($('select[name="orderstate[feedbiz_CA]"] option:eq(0)').val() + ' !');
            return (false);
        }
        if ($('select[name="orderstate[feedbiz_CE]"] :selected').index() == 0) {
            alert($('select[name="orderstate[feedbiz_CE]"] option:eq(0)').val() + ' !');
            return (false);
        }
        if ($('select[name="orderstate[feedbiz_CL]"] :selected').index() == 0) {
            alert($('select[name="orderstate[feedbiz_CL]"] option:eq(0)').val() + ' !');
            return (false);
        }
    });

    //
    // Manufacturer Include/Exclude
    //
    $('#manufacturer-move-right').click(function () {
        return !$('#excluded-manufacturers option:selected').remove().appendTo('#available-manufacturers');
    });
    $('#manufacturer-move-left').click(function () {
        return !$('#available-manufacturers option:selected').remove().appendTo('#excluded-manufacturers');
    });

    $('input[name="submit"]').click(function () {
        $('#available-manufacturers option').attr('selected', true);
        $('#excluded-manufacturers option').attr('selected', true);
    });

    //
    // Suppliers Include/Exclude
    //
    $('#supplier-move-right').click(function () {
        return !$('#selected-suppliers option:selected').remove().appendTo('#available-suppliers');
    });
    $('#supplier-move-left').click(function () {
        return !$('#available-suppliers option:selected').remove().appendTo('#selected-suppliers');
    });

    $('.pmhint').show();

    $('form').submit(function () {
        $('#available-suppliers option').attr('selected', true);
        $('#selected-suppliers option').attr('selected', true);
        $('#available-manufacturers option').attr('selected', true);
        $('#excluded-manufacturers option').attr('selected', true);
    });

    //https://code.google.com/p/jsmessaging/source/browse/trunk/jquery.enableCheckboxRangeSelection.js?spec=svn18&r=18
    //JQuery plugin to shift select and unselect checkboxes
    (function ($) {
        $.fn.enableCheckboxRangeSelection = function () {
            var lastCheckbox = null;
            var lastElement = null;

            var $spec = this;
            $spec.bind("click", function (e) {
                if (lastCheckbox != null && e.shiftKey) {
                    $spec.slice(
                        Math.min($spec.index(lastCheckbox), $spec.index(e.target)),
                        Math.max($spec.index(lastCheckbox), $spec.index(e.target)) + 1
                    ).attr({checked: e.target.checked ? "checked" : ""}).parent().parent().attr({'class': e.target.checked ? "highlight" : "GridItem"});
                }
                lastCheckbox = e.target;
            });
        };
    })(jQuery);
    $('.cat-line input[type=checkbox].category').enableCheckboxRangeSelection();

    // Copy to clipboard
    // http://stackoverflow.com/questions/400212/how-do-i-copy-to-the-clipboard-in-javascript
    $('#button-copy-to-clipboard, #button-copy-to-clipboard2').click(function () {
        var success = false;

        if (typeof($.fn.prop) === 'function') {            
            success = copyText ($('#hdd-copy-to-clipboard').val()) ;
        }

        if (success) {
            if (typeof(showSuccessMessage) === 'function')
                showSuccessMessage($('#msg-success-copy-to-clipboard').val());
            else
                alert($('#msg-success-copy-to-clipboard').val());
        }
        else {
            if (typeof(showSuccessMessage) === 'function')
                showErrorMessage()($('#msg-error-copy-to-clipboard').val());
            else
                alert($('#msg-success-error-to-clipboard').val());
        }
    });
    
    
    $('#btn_survey_connect').click(function () {
        var url_fb_connector = $("#url_fb_connector").val();
        window.open(url_fb_connector, '_blank');
    });

    $('#btn_feedbiz_dashboard').click(function () {
        var url_fb_dashboard = $("#url_fb_dashboard").val();
        window.open(url_fb_dashboard, '_blank');
    });

    $("#feedbiz_form").validate({
        rules: {
            "survey_first_name":{
                "required": true
            },
            "survey_last_name":{
                "required": true
            },
            "survey_company":{
                "required": true
            },
            "survey_telephone":{
                "required": true
            },
            "survey_email":{
                "required": true
            },
            "survey_language": {
                "required": true
            },
            "survey_country": {
                "required": true
            }
        },
        /*submitHandler: function(form) {
            doAjaxLogin($('#redirect').val());
        },*/

        highlight: function ( element, errorClass, validClass ) {
            $( element ).parents( ".col-lg-4" ).addClass( "has-error" ).removeClass( "has-success" );
        },
        unhighlight: function (element, errorClass, validClass) {
            $( element ).parents( ".col-lg-4" ).addClass( "has-success" ).removeClass( "has-error" );
        },

        onfocusout: function(element) { $(element).valid(); },
        errorElement: 'span',
        errorClass: 'help-block',
        errorPlacement: function(error, element) {
            if(element.parent('.input-group').length) {
                error.insertAfter(element.parent());
            } else {
                error.insertAfter(element);
            }
        }
    });

    $('#submit_form').click(function(e){
        e.preventDefault();
        e.stopPropagation();

        if (!$('#feedbiz_form').validate()) {
            if (window.console) console.log('validate failed');
        }
        if (window.console) console.log('validate ok');
        return(false);
    });

    function send_survey(){

        var survey_last_name = $('#survey_last_name').val();
        var survey_company = $('#survey_company').val();
        var survey_first_name = $('#survey_first_name').val();
        var survey_company_num = $('#survey_company_num').val();
        var survey_telephone = $('#survey_telephone').val();
        var survey_web_site = $('#survey_web_site').val();
        var survey_email = $('#survey_email').val();
        var survey_product_num = $('#survey_product_num').val();
        var survey_language = $('#survey_language').val();
        var survey_country = $('#survey_country').val();
        var survey_sales = $('#survey_sales').val();
        var survey_category = $('#survey_category').val();
        var survey_marketplaces = $('#survey_marketplaces').val();
        var url_fb_customer_survey = $('#url_fb_customer_survey').val();
        var is_feedbiz = 0;
        
        if(typeof $('#is_feedbiz') !== 'undefined' && $('#is_feedbiz').val() == 1){
            is_feedbiz = 1
        }

        if (window.console) console.log('send_survey()');

        if ($('#feedbiz_form').valid()) {
            $('#submit_form').click();
        }


        var post_vars = {
                survey_last_name: survey_last_name,
                survey_company: survey_company,
                survey_first_name: survey_first_name,
                survey_company_num: survey_company_num,
                survey_telephone: survey_telephone,
                survey_web_site: survey_web_site,
                survey_email: survey_email,
                survey_product_num: survey_product_num,
                survey_language: survey_language,
                survey_country: survey_country,
                survey_sales: survey_sales,
                survey_category: survey_category,
                survey_marketplaces: survey_marketplaces,
                url_fb_customer_survey:url_fb_customer_survey,
                is_feedbiz:is_feedbiz
        };

        $.ajax({
            type: 'POST',
            url: $('#survey_url').val() + '?callback=?&action=customersurvey',
            // dataType: 'json',
            data: {'object': post_vars},
            success: function (data) {
                if (window.console)
                    console.log(data);
            },
            error: function (err) {
                if (window.console)
                    console.log(err);
            }
        });
    }
    
    if ($('#survey_marketplaces').length) {
        $('#survey_marketplaces').fSelect({
            placeholder: $('#fs_placeholder').val(),
            numDisplayed: 5,
            overflowText: $('#fs_overflowtext').val(),
            noResultsText: $('#fs_noresultstext').val(),
            searchText:$('#fs_search').val(),
            showSearch: false
        });
    }

    $('#menu-support').click(function () {
        setTimeout(function(){
            $('#menudiv-support .support-url').hide();
            $('#menudiv-support .support2-informations-loader').show();

            html2canvas( [ document.body ], {
                onrendered: function( canvas ) {
                    var support_image = canvas.toDataURL("image/png");
                    $.ajax({
                        type: 'POST',
                        url:  $('#url_support_info').val() + '&callback=?',
                        data: {
                            'screenshot': support_image
                        },
                        dataType: 'jsonp',
                        success: function (data) {
                            if (window.console) console.log(data);
                            $('#menudiv-support .support2-informations-loader').hide();
                            $('#menudiv-support .support-url').show();
                            $('#menudiv-support .support-url').attr('href', data.url).show();
                        },
                        error: function (data) {
                            if (window.console) console.log(data);
                            $('#menudiv-support .support2-informations-loader').hide();
                        }
                    });
                }
            });
        }, 1000);
    });
    
    $('#support-mode_dev').click(function () {
	$('.support-information-loader').show();

	var current_status = $('#mode_dev-status').val();

	$('#devmode-response-success').html('').hide();
	$('#devmode-response-danger').html('').hide();

	$.ajax({
	    type: 'POST',
	    dataType: 'jsonp',
	    url: $(this).attr('rel') + '&status=' + current_status + '&callback=?',
	    success: function (data) {
		$('.support-information-loader').hide();
		$('#devmode-response-success').html(data.message).slideDown();
		if (data.status == true) {
		    $('#mode_dev-status').val('0');
		    $('#support-mode_dev').val($('#mode_dev-status-off').val());
		    $('#prestashop-info-dev').show();
		}
		else {
		    $('#mode_dev-status').val('1');
		    $('#support-mode_dev').val($('#mode_dev-status-on').val());
		    $('#prestashop-info-dev').hide();
		}
		$('#devmode-open').hide();
	    },
	    error: function (data) {
		$('.support-information-loader').hide();
		$('#devmode-response-danger').html(data.responseText).slideDown();
	    }
	});
    });
});

function copyText (text) {
    // Create the textarea input to hold our text.
    const element = document.createElement('textarea');
    element.value = text;
    // Add it to the document so that it can be focused.
    document.body.appendChild(element);
    // Focus on the element so that it can be copied.
    element.focus();
    element.setSelectionRange(0, element.value.length);
    // Execute the copy command.
    const success = document.execCommand('copy');
    // Remove the element to keep the document clear.
    document.body.removeChild(element);

    return success;
}
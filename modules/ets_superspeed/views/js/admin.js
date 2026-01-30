/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */
var editor_script=false;
var total_optimize_images=0;
$(document).ready(function(){

    $('input[name="btnSubmitPageCache"]').remove();
    $('.filter input[type="text"]').attr('autocomplete','off');
    if ($(".ets_sp_datepicker input").length > 0) {
        $(".ets_sp_datepicker input").datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true,
        });
    }
    if($('button[name="btnRefreshSystemAnalyticsNew"]').length)
    {
        $.ajax({
            url: link_ajax_submit,
            data: 'btnRefreshSystemAnalyticsNew=1',
            type: 'post',
            dataType: 'json',
            success: function(json){
                $('button[name="btnRefreshSystemAnalyticsNew"]').removeClass('loading');
                if(json.check_points)
                {
                    $('.page_cache_generation_check_point_header').removeClass('refresh');
                    $('.page_cache_generation_check_point_body table tbody').html(json.check_points);
                    
                }
                else
                    $('.page_cache_generation_check_point_body tbody').html('<tr><td colspan="100%"><p class="not-data">'+no_data_text+'</p></td></tr>');
                updateSystemAnalytics();
            },
            error: function(xhr, status, error)
            {     
                $('button[name="btnRefreshSystemAnalyticsNew"]').removeClass('loading');
            }
        });
    }    
    $(document).on('click','button[name="submitDeleteSystemAnalytics"]',function(e){
        e.preventDefault();
        if(!$(this).hasClass('loading'))
        {
            if(!confirm(confirm_delete_all_system_analytics)){
                return false;
            }
            $(this).addClass('loading');
            var $this = $(this);
            $.ajax({
                url: link_ajax_submit,
                data: {
                    submitDeleteSystemAnalytics:1
                },
                type: 'post',
                dataType: 'json',
                success: function(json){
                    $this.removeClass('loading');
                    if(json.success)
                    {
                        $.growl.notice({ message: json.success });
                        $('table.module_performance tbody').html('<tr><td colspan="7"><p class="not-data">'+no_data_text+'</p></td></tr>');
                    }
                    if(json.errors)
                        $.growl.error({message:json.errors});
                    $(this).removeClass('loading');
                },
                error: function(xhr, status, error)
                {
                    $(this).removeClass('loading');
                }
            });
        }
    });
    $(document).on('click','.sp_cleaner_image',function(e){
        e.preventDefault();
        if(!$(this).hasClass('loading'))
        {
            if(!confirm(confirm_delete_unused_images)){
                return false;
            }
            $(this).addClass('loading');
            var $this = $(this);
            $.ajax({
                url: link_ajax_submit,
                data: {
                    btnSubmitCleaneImageUnUsed:1,
                    unused_category_images : $('input[name="unused_category_images"]').length ? 1 :0,
                    unused_supplier_images : $('input[name="unused_supplier_images"]').length ? 1 :0,
                    unused_manufacturer_images : $('input[name="unused_manufacturer_images"]').length ? 1 :0,
                    unused_product_images : $('input[name="unused_product_images"]').length ? 1 :0,
                },
                type: 'post',
                dataType: 'json',
                success: function(json){
                    $this.removeClass('loading');
                    $.growl.notice({ message: json.success });
                    $('.form_cache_page.image_cleaner').html('<div class="alert alert-info">'+no_image_unused+'</div>');
                    $(this).removeClass('loading');
                },
                error: function(xhr, status, error)
                {
                    $(this).removeClass('loading');
                }
            });
        }

    });
    $(document).on('click','.image_upload_otpimize_quality',function(){
        $('.popup-optimize_image_upload').addClass('show');
        ets_sp_change_range($('#ETS_SPEED_QUALITY_OPTIMIZE_UPLOAD'));
        ets_sp_change_range($('#ETS_SPEED_QUALITY_OPTIMIZE_BROWSE'));
    });
    $(document).on('click','button[name="btnSubmitSuperSpeedException"]',function(e){
        e.preventDefault(); 
        if(!$(this).hasClass('loading'))
        {
            $(this).addClass('loading');
            var $this = $(this);
            $.ajax({
                url: '',
                data: {
                    btnSubmitSuperSpeedException:1,
                    ETS_SPEED_PAGES_EXCEPTION: $('#ETS_SPEED_PAGES_EXCEPTION').val(),
                },
                type: 'post',
                dataType: 'json',
                success: function(json){
                    $this.removeClass('loading');
                    if(json.success)
                    {
                        $.growl.notice({ message: json.success });
                    }
                    if(json.errors)
                        $.growl.error({message:json.errors});
                },
                error: function(xhr, status, error)
                {     
                    $this.removeClass('loading');
                }
            });
        }
    });
    if($('.table_analytics').length)
    {
        $.ajax({
            url: site_url_home,
            data: '',
            type: 'post',
            dataType: 'html',
            success: _ets_sp_parseImageCssHome,
            error: function(xhr, status, error)
            {     
            }
        });
    }
    $(document).on('click','.module_performance .paggination .links a',function(e){
        e.preventDefault();
        if(!$('.module_performance tbody').hasClass('loading'))
        {
            var ulr_pagination = $(this).attr('href');
            $('.module_performance tbody').addClass('loading');
            $.ajax({
                url: ulr_pagination,
                data:'paggination_ajax=1',
                type: 'post',
                dataType: 'json',
    
                success: function(json){
                    if(json)
                    {
                        $('.module_performance tbody').html(json.html);
                        $('.module_performance tbody').removeClass('loading');
                    }
                },
                error: function(xhr, status, error)
                {    
                    $('.module_performance tbody').removeClass('loading');
                }
            });
        }
        
    });
    $(document).on('click','#ETS_SPEED_RECORD_MODULE_PERFORMANCE',function(){
        if(!$('.module_system_analytics').hasClass('loading'))
        {
            $('.module_system_analytics').addClass('loading');
            $.ajax({
                url: '',
                data:{
                    'ETS_SPEED_RECORD_MODULE_PERFORMANCE' : $('#ETS_SPEED_RECORD_MODULE_PERFORMANCE:checked').length ? 1 :0,
                },
                type: 'post',
                dataType: 'json',
                success: function(json){
                    if(json)
                    {
                        if(json.success)
                            $.growl.notice({ message: json.success });
                        if(json.error)
                            $.growl.error({message:json.error});
                    }
                    $('.module_system_analytics').removeClass('loading');
                },
                error: function(xhr, status, error)
                {  
                    $('.module_system_analytics').removeClass('loading');
                }
            });
        }
    });
    $(document).on('click','.btn-clear-log-cache',function(e){
        e.preventDefault();
        if(!$(this).hasClass('loading'))
        {
            $(this).addClass('loading');
            $.ajax({
                url: '',
                data:{
                    'deleteLogError':1
                },
                type: 'post',
                dataType: 'json',
                success: function(json){
                    if(json)
                    {
                        if(json.success)
                            $.growl.notice({ message: json.success });
                        if(json.error)
                            $.growl.error({message:json.error});
                    }
                    $('.btn-clear-log-cache').removeClass('loading');
                    $('#list-file_no_caches').html('<tr class="no-record not_items_found"><td colspan="100%"><p>No items found</p></td></tr>');
                },
                error: function(xhr, status, error)
                {
                    $('.btn-clear-log-cache').removeClass('loading');
                }
            });
        }
    });
    $(document).on('click','#ETS_SPEED_ENABLE_LOG_CACHE_ERROR',function(){
        if(!$('.module_system_analytics').hasClass('loading'))
        {
            $('.module_system_analytics').addClass('loading');
            $.ajax({
                url: '',
                data:{
                    'ETS_SPEED_ENABLE_LOG_CACHE_ERROR' : $('#ETS_SPEED_ENABLE_LOG_CACHE_ERROR:checked').length ? 1 :0,
                    'enableLogError':1
                },
                type: 'post',
                dataType: 'json',
                success: function(json){
                    if(json)
                    {
                        if(json.success)
                            $.growl.notice({ message: json.success });
                        if(json.error)
                            $.growl.error({message:json.error});
                    }
                    $('.module_system_analytics').removeClass('loading');
                },
                error: function(xhr, status, error)
                {
                    $('.module_system_analytics').removeClass('loading');
                }
            });
        }
    });
    $(document).on('click','.btn-clear-cache-history',function(e){
        e.preventDefault();
        if(!$(this).hasClass('loading'))
        {
            $(this).addClass('loading');
            $.ajax({
                url: '',
                data:{
                    'deleteAllCache':1
                },
                type: 'post',
                dataType: 'json',
                success: function(json){
                    if(json)
                    {
                        if(json.success)
                            $.growl.notice({ message: json.success });
                        if(json.error)
                            $.growl.error({message:json.error});
                    }
                    $('.btn-clear-cache-history').removeClass('loading');
                    $('#list-file_caches').html('<tr class="no-record not_items_found"><td colspan="100%"><p>No items found</p></td></tr>');
                },
                error: function(xhr, status, error)
                {
                    $('.btn-clear-cache-history').removeClass('loading');
                }
            });
        }
    });
    $(document).on('click','.register-option',function(e){
        e.preventDefault(); 
       if(!$(this).hasClass('loading'))
       {
            var url_ajax = $(this).attr('href');
            var $this= $(this);
            if($this.hasClass('register'))
            {
                if(!confirm(confirm_unhook))
                    return false;
            }
            $this.addClass('loading');
            $.ajax({
                url: url_ajax+'&ajax=1',
                data: '',
                type: 'post',
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function(json){
                    $this.removeClass('loading');
                    if(json.success)
                    {
                        $.growl.notice({ message: json.success });
                        $this.attr('href',json.url);
                        if($this.hasClass('unregister'))
                        {
                            $this.removeClass('unregister').addClass('register');
                            $this.html(un_register_text);
                            $this.parent().parent().find('.hook-status').removeClass('unhooked').addClass('active').html(status_active_text);
                            
                        }
                        else
                        {
                            $this.removeClass('register').addClass('unregister');
                            $this.html(register_text);
                            $this.parent().parent().find('.hook-status').removeClass('active').addClass('unhooked').html(status_unhooked_text);
                        }
                    }
                    if(json.error)
                        $.growl.error({message:json.error});
                },
                error: function(xhr, status, error)
                {    
                    $this.removeClass('loading');
                }
            });
       } 
    });
    $(document).on('click','.btn-clear-log-clear-history',function(e){
        e.preventDefault();
        if(!$(this).hasClass('loading'))
        {
            $(this).addClass('loading');
            $.ajax({
                url: '',
                data:{
                    'deleteLogClear':1
                },
                type: 'post',
                dataType: 'json',
                success: function(json){
                    if(json)
                    {
                        if(json.success)
                            $.growl.notice({ message: json.success });
                        if(json.error)
                            $.growl.error({message:json.error});
                    }
                    $('.btn-clear-log-clear-history').removeClass('loading');
                    $('#list-clear_history').html('<tr class="no-record not_items_found"><td colspan="100%"><p>No items found</p></td></tr>');
                },
                error: function(xhr, status, error)
                {
                    $('.btn-clear-log-clear-history').removeClass('loading');
                }
            });
        }
    });
    $(document).on('click','#ETS_SPEED_ENABLE_LOG_CACHE_CLEAR',function(){
        if(!$('.module_system_analytics').hasClass('loading'))
        {
            $('.module_system_analytics').addClass('loading');
            $.ajax({
                url: '',
                data:{
                    'ETS_SPEED_ENABLE_LOG_CACHE_CLEAR' : $('#ETS_SPEED_ENABLE_LOG_CACHE_CLEAR:checked').length ? 1 :0,
                    'enableLogClear':1
                },
                type: 'post',
                dataType: 'json',
                success: function(json){
                    if(json)
                    {
                        if(json.success)
                            $.growl.notice({ message: json.success });
                        if(json.error)
                            $.growl.error({message:json.error});
                    }
                    $('.module_system_analytics').removeClass('loading');
                },
                error: function(xhr, status, error)
                {
                    $('.module_system_analytics').removeClass('loading');
                }
            });
        }
    });
    $('.sp_button-group').parent().removeClass('col-lg-9').removeClass('col-lg-offset-3');
    if($('.alert.alert-success').length)
        setTimeout(function(){ $('.alert.alert-success').remove(); }, 3000);
    if($('.config_tab_page_setting').length)
    {
        if($('.confi_tab.active').length==0)
            $('.confi_tab.config_tab_page_setting').addClass('active');
        $('.form_cache_page').hide();
        $('.form_cache_page').hide();
        $('.form_cache_page.'+$('.confi_tab.active').attr('data-tab-id')).show();
    }
    if($('.config_tab_module_performance').length)
    {
        if($('.confi_tab.active').length==0)
            $('.confi_tab.config_tab_module_performance').addClass('active');
        $('table.table_analytics').hide();
        $('table.table_analytics.'+$('.confi_tab.active').attr('data-tab-id')).show();
    }
    if($('.config_tab_image_old').length)
    {
        $('.confi_tab.config_tab_image_old').addClass('active');
        $('.form_cache_page').hide();
        $('.form_cache_page.'+$('.confi_tab.active').attr('data-tab-id')).show();
        $('button[name="btnSubmitNewImageOptimize"]').hide();
        $('button[name="btnSubmitOldImageOptimize"]').show();
        $('button[name="btnSubmitImageOptimize"]').show();
        $('button[name="btnSubmitLazyLoadImage"]').hide();
        if(total_images>0)
        {
            $('button[name="btnSubmitImageOptimize"]').removeAttr('disabled');
        }
        else
            $('button[name="btnSubmitImageOptimize"]').attr('disabled','disabled');
    }
    $(document).on('click','.add_api_key',function(e){
       e.preventDefault(); 
       $(this).prev().append('<div class="input-inline"><input type="text" name="ETS_SPEED_API_TYNY_KEY[]" value="" placeholder="'+tiny_label+'"/><button class="delete_api_key"><i class="icon icon-trash"></i></button></div>');
       $('.delete_api_key').show();
    });
    $(document).on('click','.delete_api_key',function(e){
       e.preventDefault();
       $(this).parent().remove(); 
       if($('.delete_api_key').length==1)
            $('.delete_api_key').hide();
    });
    $(document).keyup(function(e) { 
        if(e.keyCode == 27) {
            if($('.popup-configuration-cache.success').length)
                sp_hidePopupDashboard();
            $('.confirm-popup').removeClass('show');
            $('.popup-optimize_image_upload').removeClass('show');
        }
    });

    var block_left_height =  $('.sp_block_left').height();
    $('#content.bootstrap').prepend( "<div class='sp_block_space'></div>" )
    $('.sp_block_space').css('height',block_left_height);
    $(window).resize(function(e){
        var block_left_height =  $('.sp_block_left').height();
        $('.sp_block_space').css('height',block_left_height);
    });

    $(document).on('click','.btn-cancel',function(e){
       e.preventDefault();
       $('.popup-optimize_image_upload').removeClass('show'); 
    });
    $(document).mouseup(function (e)
    {
        if($('.popup-configuration-cache.success').length)
        {
            var container_pop_table=$('.popup-configuration-cache.show.success .popup-content');
            if (!container_pop_table.is(e.target)&& container_pop_table.has(e.target).length === 0)
            {
                sp_hidePopupDashboard();
            }
        }
        var confirm_popup=$('.confirm-popup .popup-content');
        if (!confirm_popup.is(e.target)&& confirm_popup.has(e.target).length === 0)
        {
            $('.confirm-popup').removeClass('show');
        }
        if (!$('.popup-optimize_image_upload .popup-content').is(e.target)&& $('.popup-optimize_image_upload .popup-content').has(e.target).length === 0)
        {
            $('.popup-optimize_image_upload').removeClass('show');
        }
        if (!$('.popup-optimize_image_upload .popup-content').is(e.target)&& $('.popup-optimize_image_upload .popup-content').has(e.target).length === 0)
        {
            $('.popup-optimize_image_upload').removeClass('show');
        }
    });
    $(document).on('click','.list-chonse-configuration-auto input[type="checkbox"]',function(){
        var name= $(this).attr('name');
        if($(this).is(':checked'))
            $('.popup-content-body li.'+name).removeClass('disabled');
        else
            $('.popup-content-body li.'+name).addClass('disabled');
    });
    sp_displayTynyPNG();
    $('#ETS_SPEED_OPTIMIZE_SCRIPT,#ETS_SPEED_OPTIMIZE_SCRIPT_BROWSE,#ETS_SPEED_OPTIMIZE_SCRIPT_UPLOAD').change(function(){
        sp_displayTynyPNG();
    });
    $('.confi_tab').click(function(){
        $('.popup_run').show();
        if(!$(this).hasClass('active'))
        {
            $('.form_cache_page').hide();
            $('.confi_tab').removeClass('active');
            $('.form_cache_page.'+$(this).attr('data-tab-id')).show();
            $(this).addClass('active');
			if($('input[type="range"]').length>0)
			{
				$('input[type="range"]').each(function(){
					ets_sp_change_range($(this));
				});
			}
            if($('.config_tab_image_old').length)
            {
                if($(this).attr('data-tab-id')=='image_new')
                {
                    $('button[name="btnSubmitNewImageOptimize"]').show();
                    $('button[name="btnSubmitOldImageOptimize"]').hide();
                    $('button[name="btnSubmitImageOptimize"]').hide();
                    $('button[name="btnSubmitLazyLoadImage"]').hide();
                }
                else if($(this).attr('data-tab-id')=='image_old')
                {
                    $('button[name="btnSubmitNewImageOptimize"]').hide();
                    $('button[name="btnSubmitOldImageOptimize"]').show();
                    $('button[name="btnSubmitImageOptimize"]').show();
                    $('button[name="btnSubmitLazyLoadImage"]').hide();
                }
                else if($(this).attr('data-tab-id')=='image_lazy_load')
                {
                    $('button[name="btnSubmitNewImageOptimize"]').hide();
                    $('button[name="btnSubmitOldImageOptimize"]').hide();
                    $('button[name="btnSubmitImageOptimize"]').hide();
                    $('button[name="btnSubmitLazyLoadImage"]').show();
                }
                else
                {
                    $('button[name="btnSubmitNewImageOptimize"]').hide();
                    $('button[name="btnSubmitOldImageOptimize"]').hide();
                    $('button[name="btnSubmitImageOptimize"]').hide();
                    $('button[name="btnSubmitLazyLoadImage"]').hide();
                } 
            }
            if($('.config_tab_module_performance').length)
            {
                if($('.confi_tab.active').length==0)
                    $('.confi_tab.config_tab_module_performance').addClass('active');
                $('table.table_analytics').hide();
                $('table.table_analytics.'+$('.confi_tab.active').attr('data-tab-id')).show();
            }
            if($(this).attr('data-tab-id')=='livescript'  && editor_script==false)
            {
                editor_script = CodeMirror.fromTextArea(document.getElementById("live_script"), {
                  lineNumbers: true,
                  mode: "text/html",
                  matchBrackets: true
                });
            }
            ets_sp_change_range($('input[name="ETS_SPEED_QUALITY_OPTIMIZE"]'));
        } 
        sp_displayTynyPNG();        
    });
    $(document).on('click','.sp_close',function(){
        sp_hidePopupDashboard();
        if($('.popup-optimize_image_upload').length)
            $('.popup-optimize_image_upload').removeClass('show');
    });
    $(document).on('click','input[name="ETS_SPEED_ENABLE_PAGE_CACHE"]',function(){
        if($(this).val()==0)
        {
            $('input[name="ETS_SPEED_PAGES_TO_CACHE[]"]').each(function(){
               $(this).prop('checked',false);
            });
        }
        else if($('input[name="ETS_SPEED_PAGES_TO_CACHE[]"]:checked').length==0)
        {
           $('input[name="ETS_SPEED_PAGES_TO_CACHE[]"]').each(function(){
               $(this).prop('checked',true);
           }); 
        }
    });
    $(document).on('click','input[name="ETS_SPEED_PAGES_TO_CACHE[]"]',function(){
        if($('input[name="ETS_SPEED_PAGES_TO_CACHE[]"]:checked').length==0)
        {
            if($('#ETS_SPEED_ENABLE_PAGE_CACHE_off:checked').length==0)
                $('#ETS_SPEED_ENABLE_PAGE_CACHE_off').click();
        }
        if($('input[name="ETS_SPEED_PAGES_TO_CACHE[]"]:checked').length>0)
        {
            if($('#ETS_SPEED_ENABLE_PAGE_CACHE_on:checked').length==0)
                $('#ETS_SPEED_ENABLE_PAGE_CACHE_on').click();
        }
    });
    if(parseInt($('input[name="ETS_SPEED_QUALITY_OPTIMIZE"]').val())==100)
    {
        $('.form_cache_page.update_quality .col-lg-9').hide();
    }
    else
    {
        $('.form_cache_page.update_quality .col-lg-9').show();
    } 
    $(document).on('change','.image_old .checkbox_all input,.unoptimized_image input,input[name="ETS_SPEED_QUALITY_OPTIMIZE"],select[name="ETS_SPEED_OPTIMIZE_SCRIPT"],#ETS_SPEED_UPDATE_QUALITY_1',function(){
        var formData = new FormData($('button[name="btnSubmitImageOptimize"]').parents('form').get(0));
        formData.append('changeSubmitImageOptimize', '1');
        stop_optimized = false;
        continue_optimize = false;
        var url_ajax= link_ajax_submit;
        if(parseInt($('input[name="ETS_SPEED_QUALITY_OPTIMIZE"]').val())==100)
        {
            $('button[name="btnSubmitImageOptimize"]').html('<i class="process-icon-cogs"></i>'+Restore_original_images_text);
            $('.form_cache_page.update_quality .col-lg-9').hide();
        }
        else
        {
            $('button[name="btnSubmitImageOptimize"]').html('<i class="process-icon-cogs"></i>'+Optimize_existing_images_text);
            $('.form_cache_page.update_quality .col-lg-9').show();
        }    
        $('button[name="btnSubmitImageOptimize"]').attr('disabled','disabled');
         $.ajax({
                url: url_ajax,
                data: formData,
                type: 'post',
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function(json){
                    sp_displayInfoImageOptimize(json);
                },
                error: function(xhr, status, error)
                {            
                }
            });
        
    });
    $(document).on('click','.update_tocken_sp',function(){
        $('.bootstrap .module_error').remove();
        if(!$(this).hasClass('loading'))
        {
            $(this).addClass('loading');
            $.ajax({
                url: '',
                data: 'update_tocken_sp&ajax=1&ETS_SPEED_SUPER_TOCKEN='+$('input[name="ETS_SPEED_SUPER_TOCKEN"]').val(),
                type: 'post',
                dataType: 'json',
                success: function(json){
                    if(json.success)
                    {
                        sp_displaySuccessMessage(json.success);
                        $('.tocken_value').text($('input[name="ETS_SPEED_SUPER_TOCKEN"]').val());
                        $('.run_auto_cache').attr('href',json.link_cronjob);
                        $('.update_tocken_sp').removeClass('loading');
                    }
                    else if(json.errors)
                    {
                        sp_displayErrorMessage(json.errors);
                        $('.update_tocken_sp').removeClass('loading');
                    }
                },
                error: function(xhr, status, error)
                {            
                    $('.update_tocken_sp').removeClass('loading');
                }
            });
        }
        return false;
    });
});
$(document).on('click','.toogle-hide-seting-dynamic',function(){
    $(this).next('.list-hooks').toggle();
});
$(document).on('click','.dynamic_modules',function(){
    $('.bootstrap .module_error').remove();
   var url_ajax= $(this).closest('#configuration_form').attr('action');
   var id_module=$(this).attr('data-module');
   var hook_name=$(this).attr('data-hook');
   var add= $(this).is(':checked') ? 1 : 0;
   var empty_content = $('#empty_dynamic_modules_'+id_module+'_'+hook_name).is('checked') ? 1 :0;
   if(!add)
   {
        $('#empty_dynamic_modules_'+id_module+'_'+hook_name).prop('checked',false);
        $('#empty_dynamic_modules_'+id_module+'_'+hook_name).attr('disabled','disabled');
   }
   else
   {
        $('#empty_dynamic_modules_'+id_module+'_'+hook_name).removeAttr('disabled');
   }
   $.ajax({
        url: url_ajax,
        data: 'action=add_dynamic_modules&id_module='+id_module+'&hook_name='+hook_name+'&empty_content='+empty_content+'&add='+add+'&ajax=1',
        type: 'post',
        dataType: 'json',
        success: function(json){
            if(json.success)
            {
                sp_displaySuccessMessage(json.success);
            }
            else if(json.errors)
                sp_displayErrorMessage(json.errors);
        },
        error: function(xhr, status, error)
        {            
        }
    });
});
$(document).on('click','.empty_dynamic_modules',function(){
    var url_ajax= $(this).closest('#configuration_form').attr('action');
    var id_module=$(this).attr('data-module');
    var hook_name=$(this).attr('data-hook');
    var empty_content = $(this).is(':checked') ? 1 :0;
    $('.bootstrap .module_error').remove();
    $.ajax({
        url: url_ajax,
        data: 'action=update_dynamic_modules&id_module='+id_module+'&hook_name='+hook_name+'&empty_content='+empty_content+'&ajax=1',
        type: 'post',
        dataType: 'json',
        success: function(json){
            if(json.success)
            {
                sp_displaySuccessMessage(json.success);
            }
            else if(json.errors)
                sp_displayErrorMessage(json.errors);
        },
        error: function(xhr, status, error)
        {            
        }
    });
});
$(document).on('click','.delete_data_cache',function(e){
    e.preventDefault();
    if(confirm(confirm_delete_data))
    {
        if(!$(this).hasClass('loading'))
        {
            var url_ajax= $(this).attr('href');
            $(this).addClass('loading');
            var $this= $(this);
            $.ajax({
                url: url_ajax,
                data: 'ajax=1',
                type: 'post',
                dataType: 'json',
                success: function(json){
                    $this.removeClass('loading');
                    if(json.success)
                    {
                        sp_displaySuccessMessage(json.success);
                        $this.parents('tr').find('.total_data_row').text('0');
                        $this.parents('tr').find('.total_data_row').text('0');
                        $this.parents('tr').find('.row-status').html('<span class="status-good">'+Good_text+'</span>');
                        $this.parents('td').find('a').remove();
                    }
                    else if(json.errors)
                        sp_displayErrorMessage(json.errors);
                    
                    
                },
                error: function(xhr, status, error)
                {      
                    $this.removeClass('loading');
                }
            });
        }
        
    }
});
$(document).on('click','.delete_all_data_cache',function(e){
    e.preventDefault();
    if(confirm(confirm_delete_all_data) && !$(this).hasClass('loading'))
    {
        var url_ajax= $(this).attr('href');
        $(this).addClass('loading');
        var $this= $(this);
        $.ajax({
            url: url_ajax,
            data: 'ajax=1',
            type: 'post',
            dataType: 'json',
            success: function(json){
                $this.removeClass('loading');
                if(json.success)
                {
                    sp_displaySuccessMessage(json.success);
                    $('.total_data_row').text('0');
                    $('td a').remove();
                }
                else if(json.errors)
                    sp_displayErrorMessage(json.errors);
                
                
            },
            error: function(xhr, status, error)
            {      
                $this.removeClass('loading');
            }
        });
    }
});
$(document).on('click','.run_auto_cache',function(e){
    e.preventDefault();
    if(!$(this).hasClass('loading'))
    {
        var url_ajax= $(this).attr('href');
        $(this).addClass('loading');
        var $this= $(this);
        $('.bootstrap .module_error').remove();
        $.ajax({
            url: url_ajax,
            data: 'ajax=1',
            type: 'post',
            dataType: 'json',
            success: function(json){
                if(json.success)
                {
                    sp_displaySuccessMessage(json.success);
                    if($('.cronjob_last_box .alert-info').length)
                    {
                        $('.cronjob_last_box .alert-info').html(json.message)
                    }
                    else
                        $('.cronjob_last_box').html('<div class="alert alert-info">'+json.message+'</div>');
                }
                else if(json.errors)
                    sp_displayErrorMessage(json.errors);
                $this.removeClass('loading');
            },
            error: function(xhr, status, error)
            {          
                $this.removeClass('loading');
            }
        });
    }
    
});
$(document).on('click','button[name="clear_all_page_caches"]',function(e){
    e.preventDefault();
    if(!$(this).hasClass('loading'))
    {
        var url_ajax= link_ajax_submit;
        $(this).addClass('loading');
        $.ajax({
            url: url_ajax,
            data: 'clear_all_page_caches=1',
            type: 'post',
            dataType: 'json',
            success: function(json){
                if(json.success)
                {
                    sp_displaySuccessMessage(json.success);
                }
                else if(json.errors)
                    sp_displayErrorMessage(json.errors);
                $('button[name="clear_all_page_caches"]').removeClass('loading');
            },
            error: function(xhr, status, error)
            { 
                $('button[name="clear_all_page_caches"]').removeClass('loading');
            }
        });
    }
});
$(document).on('click','button[name="clear_all_page_caches_dashboard"]',function(e){
    e.preventDefault();
    if(!$(this).hasClass('loading'))
    {
        $('.sp-dashboard-cache .bootstrap_sussec').remove();
        $('.bootstrap .module_error').remove();
        $(this).addClass('loading');
        $.ajax({
            url: cache_url_ajax,
            data: 'clear_all_page_caches=1',
            type: 'post',
            dataType: 'json',
            success: function(json){
                $('button[name="clear_all_page_caches_dashboard"]').removeClass('loading');
                if(json.success)
                {
                    $('.sp-dashboard-cache').append(json.success);
                    setTimeout(function(){ $('.sp-dashboard-cache .bootstrap_sussec').remove(); }, 3000);
                    $('.page_cache_generation_recently tbody').html('<tr><td colspan="3"><p class="not-data">'+no_data_text+'</p></td></tr>');
                    $('.total_cache').text('');
                }
                else if(json.errors)
                    sp_displayErrorMessage(json.errors);
            },
            error: function(xhr, status, error)
            {   
                $('button[name="clear_all_page_caches_dashboard"]').removeClass('loading');
            }
        });
    }
    
});
$(document).on('click','button[name="btnSubmitPageCacheDashboard"]',function(e){
    e.preventDefault();
    $('.confirm-popup-configuration-cache').addClass('show');
}); 
$(document).on('click','.confirm-popup-no',function(e){
    e.preventDefault();
     $('.confirm-popup').removeClass('show');
});
$(document).on('click','button[name="btnSubmitDisabledPageCacheDashboard"]',function(e){
    e.preventDefault();
    $('.confirm-popup-configuration-disabled-cache').addClass('show');
});
$(document).on('click','.confirm-popup-configuration-disable-cache-yes',function(e){
    e.preventDefault();
    $('.confirm-popup-configuration-disabled-cache').removeClass('show');
    $('button[name="btnSubmitDisabledPageCacheDashboard"]').addClass('loading');
    $.ajax({
        url: cache_url_ajax,
        data: 'btnSubmitDisabledPageCacheDashboard=1',
        type: 'post',
        dataType: 'json',
        success: function(json){
            $('button[name="btnSubmitDisabledPageCacheDashboard"]').removeClass('loading');
            if(json.success)
            {
                $('.sp-dashboard-cache').append(json.success);
                $('.check-yes').each(function(){
                    if(!$(this).parent().hasClass('block-right-image') && !$(this).parent().parent().hasClass('production_mode') && !$(this).parent().parent().hasClass('lazy_load'))
                    {
                        $(this).removeClass('check-yes').addClass('check-no').html(no_text);
                    }
                });
                $('.list-sp-dashboard-check-cache li .block-left').each(function(){
                    if(!$(this).hasClass('block-left-image') && !$(this).parent().hasClass('production_mode') && !$(this).parent().hasClass('lazy_load'))
                        $(this).removeClass('yes');
                });
                $('.popup-configuration-cache .auto:not(.production_mode):not(.lazy_load):not(.optimize_newly_images):not(.optimize_existing_images)').addClass('disabled');
                $('.popup-configuration-cache .auto .icon-check').removeClass('icon-check').addClass('icon-clock');
                $('.list-chonse-configuration-auto input[type="checkbox"]:checked:not(.optimize_newly_images):not(#production_mode):not(#lazy_load)').prop('checked',false);
                $('.popup-configuration-cache').removeClass('hide');
                setTimeout(function(){ $('.sp-dashboard-cache .bootstrap_sussec').remove(); }, 3000);
                
            }
            else if(json.errors)
            {
                sp_displayErrorMessage(json.errors);
            }
                
        },
        error: function(xhr, status, error)
        {     
        }
    });
});
$(document).on('click','.confirm-popup-configuration-cache-yes',function(e){
    e.preventDefault();
    if(stop_optimized)
    {
        stop_optimized=false;
    }
    else
    {
        optimize_type = 'products';
        limit_optimized =0;
    }
    total_optimize_images = total_need_optimized_images;
    $('.image_optimizing .total_need_optimized_images').html(total_need_optimized_images);
    $('.image_optimizing .percent-image-optimized').html('0%');
    $('.number-image-optimized .number-image').html('0');
    if($('.popup_run .optimize_existing_images').hasClass('disabled') || $('.popup_run .optimize_existing_images').length==0)
        $('.popup-configuration-cache .button-group').hide();
    else
        $('.popup-configuration-cache .button-group').show();
    sp_submitPageCacheDashboard(false);
    continue_optimize = false;
});
$(document).on('click','button[name="btnRefreshSystemAnalyticsNew"]',function(e){
    e.preventDefault();
    $('.page_cache_generation_check_point_header').addClass('refresh');
    if(!$(this).hasClass('loading'))
    {
        $(this).addClass('loading');
        $.ajax({
            url: link_ajax_submit,
            data: 'btnRefreshSystemAnalyticsNew=1',
            type: 'post',
            dataType: 'json',
            success: function(json){
                $('button[name="btnRefreshSystemAnalyticsNew"]').removeClass('loading');
                if(json.check_points)
                {
                    $('.page_cache_generation_check_point_header').removeClass('refresh');
                    $('.page_cache_generation_check_point_body table tbody').html(json.check_points);
                    
                }
                else
                    $('.page_cache_generation_check_point_body tbody').html('<tr><td colspan="100%"><p class="not-data">'+no_data_text+'</p></td></tr>');
                updateSystemAnalytics();    
            },
            error: function(xhr, status, error)
            {     
                $('button[name="btnRefreshSystemAnalyticsNew"]').removeClass('loading');
            }
        });
    }
});
$(document).on('click','button[name="btnSubmitGzip"],button[name="btnSubmitMinization"],button[name="btnSubmitPageCache"],button[name="btnSubmitNewImageOptimize"],button[name="btnSubmitOldImageOptimize"],button[name="btnSaveOptimizeImageUpload"],button[name="btnSaveOptimizeImageBrowse"],button[name="btnSubmitLazyLoadImage"]',function(e){
    e.preventDefault();
    var name=$(this).attr('name');
    sp_submitFormAjax(name,$(this));
});
$(document).on('click','button[name="btnSubmitImageOptimize"]',function(e){
    e.preventDefault();
    if(confirm( parseInt($('input[name="ETS_SPEED_QUALITY_OPTIMIZE"]').val()) ==100 ? popup_restore_image.replace('total_images',total_images) :  popup_optimize_image.replace('total_images',total_images)))
    {
        if(stop_optimized)
        {
            stop_optimized=false;
        }
        else
        {
            optimize_type = 'products';
            limit_optimized =0;
            total_optimize_images = total_images;
        }
        continue_optimize=false;
        sp_ajaxOptimizeImage(false);
        
    }
});
$(document).on('click','.optimize_all_images',function(e){
    e.preventDefault();
    $('.confirm-popup-optimize_all_images').addClass('show');
});
$(document).on('click','.btnSubmitImageOptimize,.confirm-popup-optimize_all_images-yes',function(e){
    $('.confirm-popup-optimize_all_images').removeClass('show');
    e.preventDefault();
    if(stop_optimized)
    {
        stop_optimized=false;
    }
    else
    {
        optimize_type = 'products';
        limit_optimized =0;
        total_optimize_images = total_need_optimized_images;
    }
    continue_optimize=false;
    sp_ajaxOptimizeAllImage(false);    
});
$(document).on('click','.optimize_pause,.optimize_stop',function(e){
    e.preventDefault();
    stop_optimized = true;
    if($(this).hasClass('optimize_pause'))
    {
        $(this).removeClass('optimize_pause').addClass('optimize_resume').html(resume_text);
        $(this).parents('.popup_optimizeing_wapper').addClass('popup_pause');
        if($('.list_optimized_images').length)
            $('.list_optimized_images').html('<li class="stop">'+optimize_pause+'</li>');
    }
    else
    {
        $('.popup_optimizeing_wapper').remove();
        $('.popup-configuration-cache').removeClass('show');
    }
});
$(document).on('click','.optimize_resume',function(e){
   e.preventDefault(); 
   stop_optimized = false;
   $(this).removeClass('optimize_resume').addClass('optimize_pause').html(pause_text);
   $(this).parents('.popup_optimizeing_wapper').removeClass('popup_pause');
   $('.list_optimized_images').html('');
   if($('.confirm-popup-optimize_all_images').length>0)
   {
        if($('.popup-configuration-cache').hasClass('show'))
        {
            sp_submitPageCacheDashboard(true);
        }
        else
        {
            sp_ajaxOptimizeAllImage(true);
        }
        
   }
   else
   {
        sp_ajaxOptimizeImage(true);
   }
});
$(document).on('click','.optimize_continue',function(e){
    e.preventDefault();
    stop_optimized = false;
    continue_optimize=true;
    $('.optimize_resume').removeClass('optimize_resume').addClass('optimize_pause').html(pause_text);
    if($('.confirm-popup-optimize_all_images').length>0)
    {
        if($('.popup-configuration-cache').hasClass('show'))
        {
            sp_submitPageCacheDashboard(true);
        }
        else
        {
            sp_ajaxOptimizeAllImage(true);
        }
        
    }
    else
    {
        sp_ajaxOptimizeImage(true);
    }
});
$(document).on('click','.checkbox_all input',function(){
    if($(this).is(':checked'))
    {
        $(this).closest('.form-group').find('input').prop('checked',true);
    }
    else
    {
        $(this).closest('.form-group').find('input').prop('checked',false);
    }
});
$(document).on('click','.checkbox input',function(){
    if($(this).is(':checked'))
    {
        if($(this).closest('.form-group').find('input:checked').length==$(this).closest('.form-group').find('input[type="checkbox"]').length-1)
             $(this).closest('.form-group').find('.checkbox_all input').prop('checked',true);
    }
    else
    {
        $(this).closest('.form-group').find('.checkbox_all input').prop('checked',false);
    } 
});
$(document).ready(function(){
    $('.viewmore_dropdown').on('click',function(e){
       $(this).parent().toggleClass('show_item_dropdown');
    });
    changeDropdownTab();
    $(window).resize(function(){
        changeDropdownTab();
    });
    if($('.image_old.blog_gallery').find('input:checked').length==$('.image_old.blog_gallery').find('input').length-1)
    {
        $('.image_old.blog_gallery').find('.checkbox_all input').prop('checked',true);
    }
    if($('.image_old.others').find('input:checked').length==$('.image_old.others').find('input').length-1)
    {
        $('.image_old.others').find('.checkbox_all input').prop('checked',true);
    }
    if($('.image_new.blog_gallery').find('input:checked').length==$('.image_new.blog_gallery').find('input').length-1)
    {
        $('.image_new.blog_gallery').find('.checkbox_all input').prop('checked',true);
    }
    
    if($('input[name="PS_HTACCESS_CACHE_CONTROL"]').length)
    {
        $('input[name="PS_HTACCESS_CACHE_CONTROL"]').click(function(){
        $('.form-group.enable_cache').show();
        if($('input[name="ETS_SPEED_USE_DEFAULT_CACHE"]:checked').val()==1)
        {
            $('.form-group.use_default').hide();
        }
        else
        {
            $('.form-group.use_default').show();
        }
        });
        $('.form-group.enable_cache').show();
        if($('input[name="ETS_SPEED_USE_DEFAULT_CACHE"]:checked').val()==1)
        {
            $('.form-group.use_default').hide();
        }
        else
        {
            $('.form-group.use_default').show();
        }
        $('input[name="ETS_SPEED_USE_DEFAULT_CACHE"]').click(function(){
            if($('input[name="ETS_SPEED_USE_DEFAULT_CACHE"]:checked').val()==1)
            {
                $('.form-group.use_default').hide();
            }
            else
            {
                $('.form-group.use_default').show();
            }
        });
    }
    $('input[type="range"]').each(function(){
        ets_sp_change_range($(this));
    });
    $('input[type="range"]').mousemove(function(){
        ets_sp_change_range($(this));
    });
});
function changeDropdownTab(){
    var headingwidth = $('.ets_sp_confitab_wrap').outerWidth();
    var tabwidth = $('.tab_config_page_cache').outerWidth();
    var titlewidth = $('.ets_sp_confitab_wrap > .panel-heading').outerWidth();
    var _dropdownitem = parseInt(0);
    if ( headingwidth < (tabwidth + titlewidth) ){
        $('.tab_config_page_cache .confi_tab:not(.hidden_dropdown)').each(function(){
            var _newWidthtab = $(this).outerWidth();
            _dropdownitem = _dropdownitem + $(this).outerWidth();
            if ( (_dropdownitem + titlewidth) > headingwidth){
                $(this).addClass('hidden_dropdown');
            }
        });
    }
    if ( headingwidth > (tabwidth + titlewidth) ){
        $('.tab_config_page_cache .confi_tab').removeClass('hidden_dropdown');
        $('.tab_config_page_cache .confi_tab:not(.hidden_dropdown)').each(function(){
            var _newWidthtab = $(this).outerWidth();
            _dropdownitem = _dropdownitem + $(this).outerWidth();
            if ( (_dropdownitem + titlewidth) > headingwidth){
                $(this).addClass('hidden_dropdown');
            }
        });
    }
}
function sp_displaySuccessMessage(msg)
{
    if($('form .bootstrap_sussec').length)
    {
        $('form .bootstrap_sussec').replaceWith(msg);
    }
    else
        $('form .form-wrapper').append(msg);
    setTimeout(function(){ $('form .bootstrap_sussec').remove(); }, 3000);
}
function sp_displayErrorMessage(msg)
{
    $('.module_error.alert').remove();
    if($('form .form-wrapper').next('.module_error').length)
        $('form .form-wrapper').next('.module_error').replaceWith(msg);
    else
        $('form .form-wrapper').after(msg);
}
function sp_ajaxOptimizeImage(resume)
{
    if(stop_optimized)
        return true;
    var formData = new FormData($('button[name="btnSubmitImageOptimize"]').parents('form').get(0));
    formData.append('btnSubmitImageOptimize', '1');
    formData.append('optimize_type', optimize_type);
    formData.append('limit_optimized', limit_optimized);
    formData.append('total_optimize_images',total_optimize_images);
    if(resume)
    {
        formData.append('resume', resume);
    } 
    else
    {
       continue_optimize_webp =false;
       $('.list_optimized_images').html('');
    }
    if(continue_optimize_webp)
        formData.append('continue_webp',1);
    if(continue_optimize)
    {
        formData.append('continue', 1);
    }
    if($('.popup_error').length>0)
    {
        $('.popup_error').remove();
        $('.popup_run').show();
    }
    else
        $('.bootstrap .module_error').remove();
    var url_ajax= link_ajax_submit;
    var html = '<div class="popup_optimizeing_wapper"><div class="popup_optimizeing">';
        html += '<div class="popup-title"><h3>'+optimize_title_text+'</h3><span class="optimize_stop" title="Close">Close</span></div> <div class="popup_run"><div class="optimize-wapper-percent"><div class="percentage_optimize">0%</div></div>';
        html += '<div class="popup_optimizeing_pls">'+(parseInt($('input[name="ETS_SPEED_QUALITY_OPTIMIZE"]').val())==100 ? Restoring_text : Optimizing_text )+' '+total_images+' '+please_wait+'<span class="bacham">...</span></div>';
        html += '<div class="button-group"><button class="btn btn-default pull-left optimize_pause">'+pause_text+'</button><button class="btn btn-default pull-right optimize_stop">'+stop_text+'</button></div></div></div></div>';
    if(!$('#configuration_form .popup_optimizeing_wapper').length)
        $('#configuration_form .panel-footer').before(html);
    $.ajax({
        url: url_ajax,
        data: formData,
        type: 'post',
        dataType: 'json',
        processData: false,
        contentType: false,
        success: function(json){
            if(!json || json.restart)
            {
                sp_ajaxOptimizeImage(true);
                if(json.restart)
                {
                    sp_ajaxPercentageImageOptimize(json);
                }
            }
            else
            {
                if(json.resume)
                {
                    optimize_type = json.optimize_type;
                    limit_optimized = json.limit_optimized;
                    sp_ajaxOptimizeImage(true);
                }
                if(json.errors)
                {
                    sp_displayErrorMessage(json.errors);
                    setTimeout(function(){ $('.popup_optimizeing_wapper').remove(); }, 500);
                }                
                if(json.success)
                {
                    sp_displaySuccessMessage(json.success);
                    sp_displayInfoImageOptimize(json);
                    $('#configuration_form .popup_optimizeing .optimize-wapper-percent').css('transition','all 1s ease 0s');
                    $('#configuration_form .popup_optimizeing .optimize-wapper-percent').css('width','100%');
                    $('#configuration_form .popup_optimizeing .percentage_optimize').html('100%');
                    setTimeout(function(){ $('.popup_optimizeing_wapper').remove(); }, 1500);
                    $('.list_optimized_images').html('<li class="stop"></li>');
                }
                if(json.error)
                {
                    if(!$('.popup_optimizeing_wapper .popup_error').length)
                        $('.popup_optimizeing_wapper .popup_run').before('<div class="popup_error"><p>'+popup_error+'</p>'+json.error+'<div class="popup_continue"><p>'+(json.script_continue=='php' ? continue_question : continue_question_webp)+'</p> <button class="btn btn-default optimize_continue">'+continue_text+'</button>  <button class="btn btn-default optimize_stop">'+no_continue_text+'</button></div></div>');
                    if(json.script_continue=='webp')
                        continue_optimize_webp=true;
                    else
                        continue_optimize_webp=false;
                    $('.popup_run').hide();
                }    
            }
        },
        error: function(xhr, status, error)
        {
            sp_ajaxOptimizeImage(true);              
        }
    });
}
function sp_ajaxOptimizeAllImage(resume)
{
    if(stop_optimized)
        return true;
    var url_ajax= link_ajax_submit;
    var html = '<div class="popup_optimizeing_wapper"><div class="popup_optimizeing"><div class="popup-title"><h3>'+optimize_title_text+'</h3><span class="optimize_stop" title="Close">Close</span></div><div class="popup_run">';
        html += '<div class="optimize-wapper-percent"><div class="percentage_optimize">0%</div></div>';
        html +='<div class="popup_optimizeing_pls">'+Optimizing_text+' '+total_need_optimized_images+' '+please_wait+'<span class="bacham">...</span></div>';
        html +='<div class="button-group"><button class="btn btn-default pull-left optimize_pause">'+pause_text+'</button><button class="btn btn-default pull-right optimize_stop">'+stop_text+'</button></div></div></div></div>';
    if($('.popup_error').length>0)
    {
        $('.popup_error').remove();
        $('.popup_run').show();
    }
    else
        $('.bootstrap .module_error').remove();
    if(!$('#configuration_form .popup_optimizeing_wapper').length)
        $('#configuration_form .panel-footer').before(html);
    if(!resume)
    {
        continue_optimize_webp=false;
        $('.list_optimized_images').html('');
    }
    $.ajax({
        url: url_ajax,
        data: 'btnSubmitImageAllOptimize=1&ajax=1&optimize_type='+optimize_type+'&limit_optimized='+limit_optimized+'&total_optimize_images='+total_optimize_images+(resume ? '&resume=1':'')+(continue_optimize ? '&continue=1':'')+(continue_optimize_webp ? '&continue_webp=1':''),
        type: 'post',
        dataType: 'json',
        success: function(json){
            if(!json || json.restart)
            {
                sp_ajaxOptimizeAllImage(true);
                if(json.restart)
                    sp_ajaxPercentageAllImageOptimize(json);
            }
            else
            {
                if(json.resume)
                {
                    optimize_type = json.optimize_type;
                    limit_optimized = json.limit_optimized;
                    sp_ajaxOptimizeAllImage(true);
                }
                if(json.errors)
                {
                    sp_displayErrorMessage(json.errors);
                    setTimeout(function(){ $('.popup_optimizeing_wapper').remove(); }, 500);
                }                
                if(json.success)
                {
                    sp_displaySuccessMessage(json.success);
                    sp_displayInfoImageOptimize(json);
                    $('#configuration_form .popup_optimizeing .optimize-wapper-percent').css('transition','all 1s ease 0s');
                    $('#configuration_form .popup_optimizeing .optimize-wapper-percent').css('width','100%');
                    $('#configuration_form .popup_optimizeing .percentage_optimize').html('100%');
                    $('.total_image_optimized').html(total_images +' '+images_optimized_text);
                    $('.total_image_optimized_size').html(json.total_size_save);
                    $('.list-chonse-configuration-auto input[name="optimize_existing_images"]').parents('li').remove();
                    $('.list_optimized_images').html('<li class="stop"></li>');
                    setTimeout(function(){ $('.popup_optimizeing_wapper').remove(); }, 1500);
                    if($('.optimize_all_images').length)
                        sp_updateDashboardChart(chart_image_optimize,false,[100,0]);
                }
                if(json.error)
                {
                    if(!$('.popup_optimizeing_wapper .popup_error').length)
                        $('.popup_optimizeing_wapper .popup_run').before('<div class="popup_error"><p>'+popup_error+'</p>'+json.error+'<div class="popup_continue"><p>'+(json.script_continue=='php' ? continue_question : continue_question_webp)+'</p> <button class="btn btn-default optimize_continue">'+continue_text+'</button>  <button class="btn btn-default optimize_stop">'+no_continue_text+'</button></div></div>');
                    if(json.script_continue=='webp')
                        continue_optimize_webp=true;
                    else
                        continue_optimize_webp=false;
                    $('.popup_run').hide();
                }
                    
            }
            
        },
        error: function(xhr, status, error)
        {
            sp_ajaxOptimizeAllImage(true);              
        }
    });
}
function sp_displayInfoImageOptimize(image)
{
    if(image.total_images>0)
    {
        $('.info_total_images').removeClass('hide');
        $('.info_total_images .total_images').html(image.total_images);
        $('button[name="btnSubmitImageOptimize"]').removeAttr('disabled');
    }
    else
    {
        $('button[name="btnSubmitImageOptimize"]').attr('disabled','disabled');
        $('.info_total_images').addClass('hide');
    }  
    if($('.unoptimized_image').length>0)  
        total_images = image.total_images;
    if($('label.unoptimized_image').length)
    {
       $('label.unoptimized_image').each(function(){
            var total_image_unoptimized = parseInt(image[$(this).attr('data-image')]);
            var total_image_optimized = parseInt(image[$(this).attr('data-image')+'_optimized']);
            if(total_image_unoptimized > 0)
            {
                if(total_image_optimized)
                    $(this).find('.total_unoptimized_image').html('<span class="alert-blue">'+total_image_optimized+' '+(image.quality_optimize == 100 ? restored_text :optimized_text)+'</span>, '+total_image_unoptimized+' '+(image.quality_optimize ==100 ? restorable_text : unoptimized_text));
                else
                    $(this).find('.total_unoptimized_image').html(total_image_unoptimized +' '+(image.quality_optimize ==100 ? restorable_text : unoptimized_text));
            }
            else
                $(this).find('.total_unoptimized_image').html('<span class="alert-blue">'+(image.quality_optimize==100 ? restored_succ_text: optimized_succ_text)+', </span>'+(image.quality_optimize==100 ? '<span>'+total_image_optimized+' '+unoptimized_text+'</span>' :'' ));
                
       });  
       if(image.check_optimize)
       {
            $('.congratulations_image_success').removeClass('hide');
            $('.congratulations_image_success .total_all_image_optimized').html(image.check_optimize);
            $('.congratulations_image_success .total_all_size_image_optimize').html(image.total_size_save);
            if(image.total_size_save)
                $('.total_all_size_image').removeClass('hide');
            else
                $('.total_all_size_image').addClass('hide');
       }
       else
            $('.congratulations_image_success').addClass('hide');
    }
}
function sp_ajaxPercentageImageOptimize(json)
{
    if(json.percent>0 && json.percent<100)
    {
        $('#configuration_form .popup_optimizeing .optimize-wapper-percent').css('transition','all 3s ease 0s');
        $('#configuration_form .popup_optimizeing .optimize-wapper-percent').css('width',json.percent+'%');
        $('#configuration_form .popup_optimizeing .percentage_optimize').html(json.percent+'%');
        if(json.optimized_images)
        {
            if($('.popup_optimizeing_wapper .popup_optimizeing .list_optimized_images').length==0)
            {
               $('.popup_optimizeing_wapper .popup_optimizeing .button-group').before('<ul class="list_optimized_images"></ul>');
            }
            for (image in json.optimized_images)
            {
                if($('.popup_optimizeing_wapper .popup_optimizeing .list_optimized_images li[data-image="'+json.optimized_images[image]['image']+'"]').length==0 && $('.list_optimized_images li.stop').length==0)
                {
                    if($('.popup_optimizeing_wapper .popup_optimizeing .list_optimized_images li').length >=5)
                       $('.popup_optimizeing_wapper .popup_optimizeing .list_optimized_images li:first-child').remove(); 
                    $('.popup_optimizeing_wapper .popup_optimizeing .list_optimized_images').append('<li data-image="'+json.optimized_images[image]['image']+'">'+json.optimized_images[image]['image_cat']+'</li>');
                }
            }
        }
        if(json.image)
            sp_displayInfoImageOptimize(json.image);
    }
}
function sp_ajaxPercentageAllImageOptimize(json)
{
    if(json.percent>0 && json.percent<100)
    {
        $('#configuration_form .popup_optimizeing .optimize-wapper-percent').css('transition','all 3s ease 0s');
        $('#configuration_form .popup_optimizeing .optimize-wapper-percent').css('width',json.percent2+'%');
        $('#configuration_form .popup_optimizeing .percentage_optimize').html(json.percent2+'%');
        total_need_optimized_images = json.total_unoptimized;    
        $('.percent-image-in-chart').html(json.percent+'%');
        $('.total_image_optimized').html(json.total_optimizeed +' '+images_optimized_text);
        $('.total_image_optimized_size').html(json.total_size_save);
        $('.total_unoptimized_images').html(json.total_unoptimized +' '+ unoptimized_image_text);
        $('.dashboad-image-optimized .percent-image').html(json.percent+'% <span class="number-image">('+json.total_optimizeed+' images)</span>');
        $('.dashboad-image-unoptimized .percent-image').html((json.percent_unoptimized)+'% <span class="number-image">('+json.total_unoptimized+' images)</span>');
        if(json.optimized_images)
        {
            if($('.popup_optimizeing_wapper .popup_optimizeing .list_optimized_images').length==0)
            {
               $('.popup_optimizeing_wapper .popup_optimizeing .button-group').before('<ul class="list_optimized_images"></ul>');
            }
            for (image in json.optimized_images)
            {
                if($('.popup_optimizeing_wapper .popup_optimizeing .list_optimized_images li[data-image="'+json.optimized_images[image]['image']+'"]').length==0 && $('.list_optimized_images li.stop').length==0)
                {
                    if($('.popup_optimizeing_wapper .popup_optimizeing .list_optimized_images li').length >=5)
                       $('.popup_optimizeing_wapper .popup_optimizeing .list_optimized_images li:first-child').remove(); 
                    $('.popup_optimizeing_wapper .popup_optimizeing .list_optimized_images').append('<li data-image="'+json.optimized_images[image]['image']+'">'+json.optimized_images[image]['image_cat']+'</li>');
                }
            }
        }
        sp_updateDashboardChart(chart_image_optimize,false,[json.percent,json.percent_unoptimized]);
    }
}
function sp_ajaxPercentageAllImageOptimizeDashboard(json)
{
    if(json.percent>0 && json.percent<100)
    {
        $('.popup-content-body .percent-image-optimized').html(json.percent2+'%');
        $('.popup-content-body .number-image').html(json.total_optimizeed2);
        total_need_optimized_images= json.total_unoptimized;
        $('.percent-image-in-chart').html(json.percent+'%');
        $('.total_image_optimized').html(json.total_optimizeed +' '+images_optimized_text);
        $('.total_image_optimized_size').html(json.total_size_save);
        $('.total_unoptimized_images').html(json.total_unoptimized +' '+ unoptimized_image_text);
        $('.dashboad-image-optimized .percent-image').html(json.percent+'% <span class="number-image">('+json.total_optimizeed+' images)</span>');
        $('.dashboad-image-unoptimized .percent-image').html((json.percent_unoptimized)+'% <span class="number-image">('+json.total_unoptimized+' images)</span>');
        sp_updateDashboardChart(chart_image_optimize,false,[json.percent,json.percent_unoptimized]);
        if(json.optimized_images)
        {
            if($('.popup-configuration-cache .popup_run .list_optimized_images').length==0)
            {
               $('.popup-configuration-cache .popup_run .button-group').before('<ul class="list_optimized_images"></ul>');
            }
            for (image in json.optimized_images)
            {
                if($('.popup-configuration-cache .popup_run .list_optimized_images li.'+json.optimized_images[image]['image']).length==0 && $('.list_optimized_images li.stop').length==0)
                {
                    if($('.popup-configuration-cache .popup_run .list_optimized_images li').length >=5)
                       $('.popup-configuration-cache .popup_run .list_optimized_images li:first-child').remove(); 
                    $('.popup-configuration-cache .popup_run .list_optimized_images').append('<li class="'+json.optimized_images[image]['image']+'" data-image="'+json.optimized_images[image]['image']+'">'+json.optimized_images[image]['image_cat']+'</li>');
                }
            }
        }
    }
}
function sp_submitPageCacheDashboard(resume)
{
    if(stop_optimized)
        return true;
    if($('.popup_error').length>0)
    {
        $('.popup_error').remove();
        $('.popup_run').show();
    }
    else
        $('.bootstrap .module_error').remove();
    if(!resume)
    {
        continue_optimize_webp=false;
        $('.list_optimized_images').html('');
        $('.confirm-popup-configuration-cache').removeClass('show');
        $('.popup-configuration-cache').addClass('show').removeClass('success');
        $('.popup-configuration-cache .bootstrap_sussec').remove();
        $('.popup-configuration-cache .sp_close').remove();
        var i=1;
        $('.popup-configuration-cache .popup-content-body li.auto').each(function(){
           if(!$(this).hasClass('checked') && !$(this).hasClass('hide'))
           {    
                var $this= $(this);
                setTimeout(function(){$this.find('.icon-clock').removeClass('icon-clock').addClass('icon-check');$this.addClass('checked');},i);
                i=i+200;
           } 
        });
        setTimeout(function(){$('.optimize-image').addClass('runing');},i);
    } 
    var data_post='';
    if($('.list-chonse-configuration-auto input[type="checkbox"]').length >0)
    {
        $('.list-chonse-configuration-auto input[type="checkbox"]').each(function(){
            if($(this).is(':checked'))
                data_post +='&'+$(this).attr('name')+'=1';
            else
                data_post +='&'+$(this).attr('name')+'=0';
        });
    }
    setTimeout(function(){
        $.ajax({
            url: link_ajax_submit,
            data: 'btnSubmitPageCacheDashboard=1&percent_unoptimized_images='+percent_unoptimized_images+data_post+'&total_optimize_images='+total_optimize_images+'&optimize_type='+optimize_type+'&limit_optimized='+limit_optimized+(resume ? '&resume=1':'')+(continue_optimize ? '&continue=1':'')+(continue_optimize_webp ? '&continue_webp=1':''),
            type: 'post',
            dataType: 'json',
            success: function(json){
                if($('.list-chonse-configuration-auto input[type="checkbox"]').length >0)
                {

                    $('.list-chonse-configuration-auto input[type="checkbox"]').each(function(){
                       var name= $(this).attr('name'); 
                       if($(this).is(':checked'))
                       {
                            $('li.'+name+' .check-no').removeClass('check-no').addClass('check-yes').html(yes_text);
                            $('.popup-content-body li.'+name+' .icon-clock').removeClass('icon-clock').addClass('icon-check');
                            $('.list-sp-dashboard-check-cache li.'+name+' .block-left').addClass('yes');
                       }
                       else
                       {
                            $('li.'+name+' .check-yes').removeClass('check-yes').addClass('check-no').html(no_text);
                            $('.popup-content-body li.'+name+' .icon-clock').removeClass('icon-clock').addClass('icon-check');
                            $('.list-sp-dashboard-check-cache li.'+name+' .block-left').removeClass('yes');
                       }
                    });
                }
                if(!json || json.restart)
                {
                    sp_submitPageCacheDashboard(true);
                    if(json.restart)
                    {
                        sp_ajaxPercentageAllImageOptimizeDashboard(json);
                    }
                }
                else
                {
                    if(json.success)
                    {
                        $('.popup-configuration-cache').addClass('success');
                        $('.popup-configuration-cache .button-group').hide();
                        $('.popup-content-body').append('<span class="btn btn-default sp_close" type="button">'+close_text+'</span>');
                        if($('.list-chonse-configuration-auto input[name="optimize_existing_images"]').is(':checked'))
                        {
                            percent_unoptimized_images=0;
                            sp_updateDashboardChart(chart_image_optimize,false,[100,0]);
                            $('.total_image_optimized').html(total_images +' '+images_optimized_text);
                            $('.total_image_optimized_size').html(json.total_image_optimized_size);
                        }
                        $('.list_optimized_images').html('<li class="stop"></li>');
                    }
                    else if(json.errors)
                    {
                        sp_displayErrorMessage(json.errors);
                        sp_hidePopupDashboard();
                    }
                    if(json.error)
                    {
                        if(!$('.popup-configuration-cache .popup_error').length)
                            $('.popup-configuration-cache .popup-content-body .popup_run').before('<div class="popup-title popup_error"><h3>'+optimize_title_text+'</h3><span class="optimize_stop" title="Close">Close</span></div><div class="popup_error"><p>'+popup_error+'</p>'+json.error+'<div class="popup_continue"><p>'+(json.script_continue=='php' ? continue_question : continue_question_webp )+'</p> <button class="btn btn-default optimize_continue">'+continue_text+'</button>  <button class="btn btn-default optimize_stop">'+no_continue_text+'</button></div></div>');
                        if(json.script_continue=='webp')
                            continue_optimize_webp=true;
                        else
                            continue_optimize_webp=false;
                        $('.popup_run').hide();
                    }
                    if(json.resume)
                    {
                        optimize_type = json.optimize_type;
                        limit_optimized = json.limit_optimized;
                        sp_submitPageCacheDashboard(true);
                    }
                }
            },
            error: function(xhr, status, error)
            {
                if($('.list-chonse-configuration-auto input[type="checkbox"]').length >0)
                {

                    $('.list-chonse-configuration-auto input[type="checkbox"]').each(function(){
                       var name= $(this).attr('name'); 
                       if($(this).is(':checked'))
                       {
                            $('li.'+name+' .check-no').removeClass('check-no').addClass('check-yes').html(yes_text);
                            $('.popup-content-body li.'+name+' .icon-clock').removeClass('icon-clock').addClass('icon-check');
                            $('.list-sp-dashboard-check-cache li.'+name+' .block-left').addClass('yes');
                       }
                       else
                       {
                            $('li.'+name+' .check-yes').removeClass('check-yes').addClass('check-no').html(no_text);
                            $('.popup-content-body li.'+name+' .icon-clock').removeClass('icon-clock').addClass('icon-check');
                            $('.list-sp-dashboard-check-cache li.'+name+' .block-left').removeClass('yes');
                       }
                    });
                }
                sp_submitPageCacheDashboard(true);
            }
        });
    },i/2);
}
function sp_updateDashboardChart(chart,label_datas,datas)
{
    if(label_datas)
    {
        chart.data.labels=[];
        $(label_datas).each(function(){
            chart.data.labels.push(this);
        });
    }
    chart.data.datasets.forEach(function(dataset){
        dataset.data=[];
        dataset.data.push(datas[0]);
        dataset.data.push(datas[1]);
        dataset.borderWidth=0;
    });
    chart.update();
    if(datas[0]==100)
    {
        $('.image-dashboad-image-optimize .dashboad-image-unoptimized .percent-image').html('0%');
        $('.image-dashboad-image-optimize .dashboad-image-optimized .percent-image').html('100% <span class="number-image">('+total_images+' images)</span>');
        if($('.total_unoptimized_images').length)
        {
            $('.total_unoptimized_images').parent().addClass('yes');
            $('.total_unoptimized_images').remove();
        } 
        if($('.block-right-image .check-no').length)
            $('.block-right-image .check-no').removeClass('check-no').addClass('check-yes').html(yes_text);
        $('.popup-content-body .percent-image-optimized').html('100%');
        $('.percent-image-in-chart').html('100%');
        $('.popup-content-body .number-image').html(total_images);
        $('.popup-content-body .optimize-image').addClass('checked').removeClass('runing');
        $('.optimize_all_images').remove();
    }
    
}
function sp_submitFormAjax(name,$this)
{
    if($this.hasClass('loading'))
        return false;
    if(editor_script)
        $('#live_script').val(editor_script.getValue());
    var formData = new FormData($this.parents('form').get(0));
    formData.append(name, 1);
    formData.append('ajax', 1);
    var url_ajax= link_ajax_submit;
    $('.bootstrap .module_error').remove();
    $this.addClass('loading');
    $.ajax({
        url: url_ajax,
        data: formData,
        type: 'post',
        dataType: 'json',
        processData: false,
        contentType: false,
        success: function(json){
            $this.removeClass('loading');
            if(json.success)
            {
                sp_displaySuccessMessage(json.success);
                if(name=='btnSaveOptimizeImageUpload')
                    $('.image_upload_otpimize_quality.image_upload').html('<i class="fa fa-cogs"></i> '+$('#ETS_SPEED_OPTIMIZE_SCRIPT_UPLOAD option[value="'+$('#ETS_SPEED_OPTIMIZE_SCRIPT_UPLOAD').val()+'"]').html()+' ('+$('#ETS_SPEED_QUALITY_OPTIMIZE_UPLOAD').val()+'%)');
                if(name=='btnSaveOptimizeImageBrowse')
                    $('.image_upload_otpimize_quality.image_browse').html('<i class="fa fa-cogs"></i> '+$('#ETS_SPEED_OPTIMIZE_SCRIPT_BROWSE option[value="'+$('#ETS_SPEED_OPTIMIZE_SCRIPT_BROWSE').val()+'"]').html()+' ('+$('#ETS_SPEED_QUALITY_OPTIMIZE_BROWSE').val()+'%)');
                $('.popup-optimize_image_upload').removeClass('show');
            }
            else if(json.errors)
            {
                
                if($('.popup-optimize_image_upload').length && $('.popup-optimize_image_upload').hasClass('show'))
                {
                    $('.popup-optimize_image_upload .popup-content-body').append(json.errors);
                }
                else
                {
                    sp_displayErrorMessage(json.errors);
                }
                    
            }
        },
        error: function(xhr, status, error)
        {     
            $this.removeClass('loading');
        }
    });
}
function sp_hidePopupDashboard()
{    
    $('.optimize-image.checked .image-optimizing').remove();
    $('.popup-configuration-cache').removeClass('show').removeClass('success');
    $('.popup-configuration-cache .popup-content-body li').removeClass('checked');
    $('.popup-configuration-cache .popup-content-body li .icon-check').removeClass('icon-check').addClass('icon-clock');
    if($('.list-chonse-configuration-auto input[name="optimize_existing_images"]').is(':checked'))
    {
        $('input[name="optimize_existing_images"]').closest('li').remove();
        $('.popup-content-body li.optimize_existing_images').remove();
    }        
}
function sp_displayTynyPNG()
{
    
    if($('.config_tab_image_old').hasClass('active'))
    {
        if(!$('.form_cache_page.image_old.script').next('.tinypng').length)
        {
            var html_tiny = $('.form-group.tinypng').clone();
            $('.form-group.tinypng').remove();
            $('.form_cache_page.image_old.script').after(html_tiny);
        }
    }
    else if($('.config_tab_image_upload').hasClass('active') ||$('.config_tab_image_browse').hasClass('active'))
    {
        if($('.form_cache_page.image_browse.script').next('.tinypng').length==0)
        {
            var html_tiny = $('.form-group.tinypng').clone();
            $('.form-group.tinypng').remove();
            $('.form_cache_page.image_browse.script').after(html_tiny);
        }
    }
    if($('.config_tab_image_old').hasClass('active'))
    {
        var script_optimize = $('#ETS_SPEED_OPTIMIZE_SCRIPT');
        var type ='';
    }
    else if($('.config_tab_image_upload').hasClass('active'))
    {
        var script_optimize = $('#ETS_SPEED_OPTIMIZE_SCRIPT_UPLOAD');
        var type = 'upload_';
    }
    else if($('.config_tab_image_browse').hasClass('active'))
    {
        var script_optimize = $('#ETS_SPEED_OPTIMIZE_SCRIPT_BROWSE');
        var type = 'browse_';
    }
    else
        var script_optimize= false;
    
    if(script_optimize && script_optimize.length && !$('.config_tab_image_cleaner').hasClass('active') && !$('.config_tab_image_lazy_load').hasClass('active'))
    {
        script_optimize.next('.help-block').find('span').hide();
        $('.help-block #optimize_script_'+type+script_optimize.val()).show();
        if(script_optimize.val()=='tynypng')
        {
            $('.form-group.tinypng').show();
            
        }     
        else
        {
            $('.form-group.tinypng').hide();
        }
        if(type=='new_' || type=='')
        {
            if(script_optimize.val()=='google')
            {
                $('.form-group.webp.'+(type=='new_' ? 'image_new' :'image_old')).show();
                
            }     
            else
            {
                $('.form-group.webp.'+(type=='new_' ? 'image_new' :'image_old')).hide();
            }
        }
    }
    if($('.config_tab_image_cleaner').hasClass('active') || $('.config_tab_image_lazy_load').hasClass('active'))
        $('.form-group.tinypng').hide();
}
function ets_sp_change_range($range)
{
    if($range.val()<=1)
        $range.next('.range_new').next('.input-group-unit').html($range.val()+ ($range.attr('data-unit')!='%' ? ' ':'')+$range.attr('data-unit'));
    else
    {
        if($range.attr('forever')=='1' && $range.val()=='31')
            $range.next('.range_new').next('.input-group-unit').html(Forever_text);
        else
            $range.next('.range_new').next('.input-group-unit').html($range.val()+ ($range.attr('data-units')!='%' ? ' ':'')+$range.attr('data-units'));
    }
    var newPoint = ($range.val() - $range.attr("min")) / ($range.attr("max") - $range.attr("min"));
    var offset = -1;
    var  width = $range.width();
    var newPlace;
    if (newPoint < 0) { newPlace = 0; }
    else if (newPoint > 1) { newPlace = width; }
    else { newPlace = width * newPoint + offset; offset -= newPoint; }
    $range.next('.range_new').find('.range_new_run').css({
         width: newPlace+'px'
    });
}
function ets_substr_count(string,substring)
{
 var count = 0;
 for (var i=0;i<string.length;i++)
 {
      if(substring == string.substr(i,substring.length))
      count++;
 }
 return count;
}
function _ets_sp_parseImageCssHome(json){
    var total_images = ets_substr_count(json,'<img');
    if(total_images <=30)
    {
        var $status = Good_text;
        var $class_name = 'status-good';
    }
    else if(total_images>50)
    {
        var $status = Bad_text;
        var $class_name ='status-bad';
    }
    else
    {
        var $status = Acceptable_text;
        var $class_name = 'status-reputable';
    }
    var total_css = ets_substr_count(json,'.css');
    if(total_css <=5)
    {
        var $status_css = Good_text;
        var $class_name_css = 'status-good';
    }
    else if(total_css>10)
    {
        var $status_css = Bad_text;
        var $class_name_css ='status-bad';
    }
    else
    {
        var $status_css = Acceptable_text;
        var $class_name_css = 'status-reputable';
    }
    var total_script = ets_substr_count(json,'.js');

    if(total_script > 1)
        total_script = total_script-1;
    if(total_script <=5)
    {
        var $status_script = Good_text;
        var $class_name_script = 'status-good';
    }
    else if(total_script>10)
    {
        var $status_script = Bad_text;
        var $class_name_script ='status-bad';
    }
    else
    {
        var $status_script = Acceptable_text;
        var $class_name_script = 'status-reputable';
    }
    if(json)
    {
        $('.table_analytics .image_home .number_data').html(total_images);
        $('.table_analytics .image_home .status span').attr('class','').addClass($class_name).html($status);
        $('.table_analytics .css_home .number_data').html(total_css);
        $('.table_analytics .css_home .status span').attr('class','').addClass($class_name_css).html($status_css);
        $('.table_analytics .script_home .number_data').html(total_script);
        $('.table_analytics .script_home .status span').attr('class','').addClass($class_name_script).html($status_script);
    }
}
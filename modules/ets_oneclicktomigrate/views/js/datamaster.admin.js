/**
 * 2007-2019 ETS-Soft ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 *  @author ETS-Soft <etssoft.jsc@gmail.com>
 *  @copyright  2007-2019 ETS-Soft ETS-Soft
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */
var ajaxPercentImport=false;
var ajaxPercentExport=false;
var ajaxPercentExport2 =false;
var timer;
var percent_export=0;
var percent_export2=0;
var total_export=0;
var max_export=5;
var percent_import=0;
var percent_import2=0;
var total_import=0;
var max_import=5;
var passed_time=false; 
var max_time=5;
var time_counter='';
var import_ok=false;
var export_ok=false;
//var ets_octm_importing_msg = ets_octm_importing_msg || 'Importing data to table %s (%d records/s)';
$(document).ready(function(){
    $(document).on('click','.continue_importing',function(){
        $('.import-wapper-block-3 .ets_datamaster_error').remove();
        processImportData(true);
    });
    $(document).on('change','input[type="file"]',function(){
        var filename = $(this).val().split('\\').pop();
        if($(this).parents('.data_upload_button_wrap').eq(0).next('.form_upfile_val').length)
        {
            $(this).parents('.data_upload_button_wrap').eq(0).next('.form_upfile_val').addClass('show').find('.file_name').html(filename);
        }
        else
        {
            $(this).parents('.data_upload_button_wrap').eq(0).after('<div class="form_upfile_val show"><div class="file_name">'+filename+'</div></div></div>');
        }
    });
    displayFormImport();
    displayFormUpload();
    $(document).on('click','.change_data_import',function(){
        $.ajax({
            url: '',
            data: 'ajax_change_data_import=1',
            type: 'post',
            dataType: 'json',
            success: function(json){
                $('.ybc-form-group.ybc-blog-tab-step1').html(json.upload_form);
                displayFormUpload();
                $('#module_form .popup_uploading .upload-wapper-percent').css('width','0%');
            },
            error: function(xhr, status, error)
            {
            }
        });
    });
    $(document).on('change','#source_type',function(){
        displayFormUpload();
    });
    $(document).on('click','input[name="have_made_backup"]',function(){
       if($('input[name="have_made_backup"]:checked').length)
            $('button[name="submitImport"]').removeAttr('disabled');
       else
            $('button[name="submitImport"]').attr('disabled','disabled');
    });
    $(document).on('click','.ybc-blog-tab-step2 input[type="checkbox"]',function(){
        displayFormImport();
    });
    $(document).on('click','button[name="submitImport"]',function(e){
        e.preventDefault();
        if($('#source_type').length)
        {
            if($('#source_type').val()=='upload_file' && !$('#module_form input[name="file_import"]').val())
            {
                alert('Please select a file');
                return false;
            }
            if($('#source_type').val()=='link' && !$('#module_form input[name="link_file"]').val())
            {
                alert('Please enter a link url');
                return false;
            }
            if($('#source_type').val()=='url_site' && (!$('#module_form input[name="link_site"]').val() || !$('#module_form input[name="secure_access_tocken"]').val()))
            {
                alert('Please enter a site url and Secure access tocken');
                return false;
            }
        }
        if(parseInt($('input[name="step"]').val())>=5)
            return;
        if(parseInt($('input[name="step"]').val())==1 && $('.change_data_import').length==0)
        {
            $('#module_form .popup_uploading').addClass('show');
            $('#module_form .popup_uploading .upload-wapper-percent').css('transition','all 10s ease 0s');
            $('#module_form .popup_uploading .upload-wapper-percent').css('width','90%');
        }
        if(parseInt($('input[name="step"]').val())>1)
            $('input[name="link_site_connector"]').val('');
        if(parseInt($('input[name="step"]').val())==4)
        {
            if($('#have_made_backup:checked').length==0)
            {
                alert('You have not made necessary backup of the website.');
                return false;
            }
            $('#module_form .popup_importing').addClass('show');
            $('#module_form .popup_uploading .upload-wapper-percent').css('transition','all 3s ease 0s');
            $('#module_form .popup_importing .import-wapper-percent').css('width','0%');
            if($('#basicUsageClock').length>0)
            {
                timer = new Timer();
                timer.start();
                timer.addEventListener('secondsUpdated', function (e) {
                    $('#basicUsageClock').html(timer.getTimeValues().toString());
                });
            }
            ajaxPercentImport = setInterval(function(){ ajaxPercentageImport() }, 3000);
        }
        $('.ets_datamaster_error').remove();
        if($('#source_type').val()=='url_site')
        {
            setCookieEts('zip_file_name','oc2m_data_'+gencodeEts(7));
            $('#module_form .popup_uploading').addClass('show');
            processExportConnector();
            ajaxPercentExport2 = setInterval(function(){ ajaxPercentageExport2() }, 3000); 
        }
        else
            processImportData(true);
   });
   $(document).on('click','button[name="submitExport"]',function(e){
        e.preventDefault();
        if(parseInt($('input[name="step"]').val())>=4)
            return;
        if(parseInt($('input[name="step"]').val())==3)
        {
            $('#module_form .popup_exporting').addClass('show');
            $('#module_form .popup_uploading .export-wapper-percent').css('transition','all 5s ease 0s');
            $('#module_form .popup_exporting .export-wapper-percent').css('width','1%');
            ajaxPercentExport = setInterval(function(){ ajaxPercentageExport() }, 1000);
        }
        $('.ets_datamaster_error').remove();
        processExportData(0);
   });
   $(document).on('change','#file_import',function(){
        $('#data_upload_button_input').val($('#file_import').val());
        $('button[name="submitImport"]').removeAttr('disabled');
   });
   $(document).on('change','#link_file',function(){
        if($(this).val()!='')
        {
            $('button[name="submitImport"]').removeAttr('disabled');
        }
   });
   $(document).on('change','#link_site,#secure_access_tocken',function(){
        if($('#link_site').val()!='' && $('#secure_access_tocken').val()!='')
        {
            $('button[name="submitImport"]').removeAttr('disabled');
        }
   });
   $(document).on('click','button[name="submitBack"]',function(e)
   {
        e.preventDefault();
        var step = parseInt($('input[name="step"]').val());
        if(step<=1)
            return;
        if(step==4 && $('button[name="submitImport"]').length)
            $('button[name="submitImport"]').removeAttr('disabled');
        step--;
        $('input[name="step"]').val(step);
        if(step==1)
        {
            $('button[name="submitBack"]').attr('disabled','disabled');
        }
        $('.tab_step_data .data_number_step').removeClass('active');
        for(var i=1;i<=step;i++)
        {
           $('.tab_step_data .data_number_step[data-step="'+i+'"]').addClass('active');
        }
        $('.ybc-form-group').removeClass('active');
        $('.ybc-form-group.ybc-blog-tab-step'+step).addClass('active');
   });
   $(document).on('click','.dtm_history_tab_header .dtm_history_tab',function(){
        if(!$(this).hasClass('active'))
        {
            $('.dtm_history_tab_header .dtm_history_tab').removeClass('active');
            $(this).addClass('active');
            $('.tab_content').removeClass('active');
            $('.tab_content.'+$(this).attr('data-tab')).addClass('active');
        }
   });
   $(document).on('click', '.load_more_import, .load_more_export', function (ev) {
       ev.preventDefault();
       var btn = $(this);
       if (!btn.hasClass('active')) {
           $.ajax({
               type : 'post',
               url : btn.attr('href'),
               dataType : 'json',
               success : function (json) {
                   btn.removeClass('active');
                   if (json) {
                       if (btn.is('.import')) {
                           $('table.import tbody').append(json.html);
                       } else if (btn.is('.export')) {
                           $('table.export tbody').append(json.html);
                       }
                       if (json.link_more) {
                           btn.attr('href', json.link_more)
                       } else {
                           btn.remove();
                       }
                   }
               },
               error : function () {
                   btn.removeClass('active');
               }
           });
       }
   });
});
function  ajaxPercentageImport()
{
    $.ajax({
        url: '',
        data: 'ajax_percentage_import=1',
        type: 'post',
        dataType: 'json',
        success: function(json){
            if(!json)
                return false;
            if(json.percent>0 && json.percent<100 && !import_ok)
            {
                $('#module_form .popup_importing .import-wapper-percent').css('transition','all 3s ease 0s');
                $('#module_form .popup_importing .import-wapper-percent').css('width',json.percent+'%');
                $('#module_form .popup_importing .running').html(json.percent+'%');
                if(json.table_importing)
                    $('#module_form .popup_importing .percentage_import').html(ets_octm_importing_msg.replace(/%s/g, '<strong>"'+json.table_importing+'"</strong>').replace(/%d/g, json.speed));
                if(json.list_import_active!='')
                {
                    var exports_active = json.list_import_active.split(',');
                    if(exports_active.length>0)
                    {
                        for(var i=0;i<exports_active.length;i++)
                        {
                            $('.list-data-to-importing li.'+exports_active[i]).delay(300*i).queue(function(){
                                $(this).addClass('active').dequeue();
                            });
                            $('.list-data-to-importing li.'+exports_active[i]).next().delay(300).queue(function(){
                                $(this).addClass('process').dequeue();
                            });
                        }
                    }
                }

            }
            if(json.percent>0 && json.percent!=100)
            {
                percent_import2=json.totalItemImported;
            }
        },
        error: function(xhr, status, error)
        {
        }
    });
}
function ajaxPercentageExport()
{
    $.ajax({
        url: '',
        data: 'ajax_percentage_export=1',
        type: 'post',
        dataType: 'json',
        success: function(json){
            if(!json)
                return false;
            if(json.percent>0 && json.percent<100 && !export_ok)
            {
                $('#module_form .popup_exporting .export-wapper-percent').css('transition','all 3s ease 0s');
                $('#module_form .popup_exporting .export-wapper-percent').css('width',json.percent+'%');
                $('#module_form .popup_exporting .percentage_export').html(json.percent+'%');
                $('#module_form .popup_exporting .percentage_export_table').html('Exporting data from table <strong>"'+json.table+'"</strong>');
            }
            if(json.percent>0 && json.percent!=100)
            {
                percent_export2=json.totalItemExported;
            }
        },
        error: function(xhr, status, error)
        {
        }
    });
}
function displayFormImport()
{
    if($('#data_import_products').length)
    {
        if($('#data_import_products:checked').length>0)
        {
            if($('#data_import_categories:checked').length>0)
                $('.form-group.category_default').hide();
            else
                $('.form-group.category_default').show();
            if($('#data_import_suppliers:checked').length>0)
                $('.form-group.supplier_default').hide();
            else
                $('.form-group.supplier_default').show();
            if($('#data_import_manufactures:checked').length>0)
                $('.form-group.manufacturer_default').hide();
            else
                $('.form-group.manufacturer_default').show();
        }
        else
        {
            $('.form-group.category_default').hide();
            $('.form-group.supplier_default').hide();
            $('.form-group.manufacturer_default').hide();
        }
    }
    if($('#data_import_CMS').length)
    {
        if($('#data_import_CMS:checked').length)
        {
            if($('#data_import_CMS_categories:checked').length>0)
            {
                $('.form-group.cms_category_default').hide();
            }
            else
                $('.form-group.cms_category_default').show();
        }
        else
            $('.form-group.cms_category_default').hide();
    }
    if($('#file_import').length>0 && $('#link_file').length>0)
        $('button[name="submitImport"]').attr('disabled','disabled');
}
function processImportData(start)
{
    var formData = new FormData($('button[name="submitImport"]').parents('form').get(0));
    formData.append('submitImport', '1');
    formData.append('forceIDs', '1');
    if($('.ybc-form-group.ybc-blog-tab-step5').hasClass('active'))
        return false;
    $('.source-data').hide();
    if(start)
    {
        clearTimeout(time_counter);
        time_counter = setTimeout(
        function(){
            passed_time=true;
        },max_time*60*1000);
    }
    $.ajax({
        url: ETS_DT_MODULE_URL_AJAX,
        data: formData,
        type: 'post',
        dataType: 'json',
        processData: false,
        contentType: false,
        success: function(json){
            if(!json)
            {
                if(checkImportData())
                    processImportData(false);
            }
            else
            {
                if(json.error_xml)
                {
                    var html_errror='<p>Invalid XML file: <a href="'+json.link_xml+'" target="_blank">'+json.file_xml+'</a></p><p>Follow steps below to fix the problems:</p><p>1. Open the XML file on a web browser such as Chrome, Safari or Firefox to see XML errors.</p><p>2. Open the XML located at: <span class="file_url">'+json.file_url+'</span> using a ftp client software such as FileZilla: https://filezilla-project.org/ or using file editor on your hosting management area to edit/remove invalid UTF-8 characters (or XML tags).</p><p>3. Open again the XML file on your web browser and make sure the XML file is valid (no errors represented) then save all the changes </p><p>4. Click on the <span class="continue_importing">"Continue importing"</span> button below to continue.</p>';
                    $('.import-wapper-block-3').append('<div class="ets_datamaster_error"><div class="bootstrap"><div class="module_error alert alert-danger"><button class="close" data-dismiss="alert" type="button">×</button><li>'+html_errror+'</li></div></div><div class="alert alert-warning import-alert alert-warning-xml-error">*Note: The errors are often caused by invalid utf-8 characters or invalid XML tags existing in content of your website in certain items such as product description, product title, category description, CMS content, etc. You need to manually fix the XML files to continue the import process</div></div>');
                }
                else
                {
                    if(json.error)
                    {
                        if($('input[name="link_site_connector"]').length && $('input[name="link_site_connector"]').val()!='' && $('#source_type').val()=='url_site')
                        {
                            clearTimeout(time_counter);
                            $('.ybc-blog-tab-step1').before('<p class="source-data" style="text-align: center; margin-bottom: 30px;">Looks good! Source data is successfully exported. <br/>To continue <a href="'+$('input[name="link_site_connector"]').val()+'" target ="_blank">Donwload Source Data</a> then upload source data using the upload form below:</p>');
                            $('#source_type option').removeAttr('selected');
                            $('#source_type option').each(function(){
                               if($(this).val()=='upload_file')
                                    $(this).attr('selected','selected'); 
                            });
                            $('#source_type').change();
                            $('#source_type').closest('.form-group').hide();
                            $('.form-group.source.upload label').html('Upload source data <span class="required">*</span>');
                        }
                        else
                        {
                            $('#module_form .form-wrapper').append('<div class="ets_datamaster_error">'+json.errors+'</div>');
                        }
                        $('#module_form .popup_uploading').removeClass('show');
                        $('#module_form .popup_exporting').removeClass('show');
                        $('#module_form .popup_importing').removeClass('show');
                        $('#module_form .popup_uploading .upload-wapper-percent').css('width','0%');
                        $('#module_form .popup_uploading .upload-wapper-percent').css('width','0%');
                        $('#module_form .popup_exporting .export-wapper-percent').css('width','0%');
                    }
                    else
                    {
                        var step = json.step;
                        $('input[name="step"]').val(step);
                        $('.tab_step_data .data_number_step').removeClass('active');
                        for(var i=1;i<=step;i++)
                        {
                           $('.tab_step_data .data_number_step[data-step="'+i+'"]').addClass('active');
                        }
                        $('.ybc-form-group').removeClass('active');
                        $('.ybc-form-group.ybc-blog-tab-step'+step).addClass('active');
                        $('.ybc-form-group.ybc-blog-tab-step'+step).html((json.form_step));
                        displayFormImport();
                        if(step==1)
                        {
                            if(ajaxPercentExport2)
                                    clearInterval(ajaxPercentExport2);
                            $('#module_form .popup_uploading .upload-wapper-percent').css('transition','all 0s ease 0s');
                            $('#module_form .popup_uploading .upload-wapper-percent').css('width','100%');
                            $('#module_form .popup_uploading .percentage_export').html('100%');
                            setTimeout(function(){
                                $('#module_form .popup_uploading').removeClass('show');
                                $('button[name="submitBack"]').attr('disabled','disabled');
                            }, 1000);
                        }
                        else
                        {
                            $('button[name="submitBack"]').removeAttr('disabled');
                            if(step==4)
                            {
                                $('.popup_importing').html(json.popup_import);
                            }
                            if(step==5)
                            {
                                if(ajaxPercentImport)
                                    clearInterval(ajaxPercentImport);
                                timer.stop();
                                $('#module_form .panel-footer').hide();
                                $('#module_form .popup_importing .import-wapper-percent').css('transition','all 0s ease 0s');
                                $('#module_form .popup_importing .import-wapper-percent').css('width','100%');
                                $('#module_form .popup_importing .running').html('100%');
                                $('.list-data-to-importing li').addClass('active');
                                import_ok=true;
                                setTimeout(function(){
                                    $('#module_form .popup_importing').removeClass('show');
                                }, 3000);
                            }
                        }
                        if(step==4)
                        {
                            $('button[name="submitImport"]').attr('disabled','disabled');
                        }
                        else
                            $('button[name="submitImport"]').removeAttr('disabled');
                    }
                }
            }
        },
        error: function(xhr, status, error)
        {
            if($('input[name="link_site_connector"]').length && $('input[name="link_site_connector"]').val()!='' && $('#source_type').val()=='url_site')
            {
                clearTimeout(time_counter);
                if(ajaxPercentImport)
                    clearInterval(ajaxPercentImport);
                $('.ybc-blog-tab-step1').before('<p class="source-data" style="text-align: center; margin-bottom: 30px;">Looks good! Source data is successfully exported. <br/>To continue <a href="'+$('input[name="link_site_connector"]').val()+'" target ="_blank">Donwload Source Data</a> then upload source data using the upload form below:</p>');
                $('#source_type option').removeAttr('selected');
                $('#source_type option').each(function(){
                   if($(this).val()=='upload_file')
                        $(this).attr('selected','selected'); 
                });
                $('#source_type').change();
                $('#source_type').closest('.form-group').hide();
                $('.form-group.source.upload label').html('Upload source data <span class="required">*</span>');
                $('#module_form .popup_uploading').removeClass('show');
                $('#module_form .popup_exporting').removeClass('show');
                $('#module_form .popup_importing').removeClass('show');
                $('#module_form .popup_uploading .upload-wapper-percent').css('width','0%'); 
                $('#module_form .popup_uploading .upload-wapper-percent').css('width','0%'); 
                $('#module_form .popup_exporting .export-wapper-percent').css('width','0%');
            }
            else
            {
                if(checkImportData())
                    processImportData(false);
            }
            
        }
    });
}
function processExportData(reload)
{
    var formData = new FormData($('button[name="submitExport"]').parents('form').get(0));
    formData.append('submitExport', '1');
    formData.append('submitExportReload', reload);
    $.ajax({
        url: ETS_DT_MODULE_URL_AJAX,
        data: formData,
        type: 'post',
        dataType: 'json',
        processData: false,
        contentType: false,
        success: function(json){
            if(ajaxPercentExport)
                clearInterval(ajaxPercentExport);
            if(json.error)
            {
                $('#module_form .form-wrapper').append('<div class="ets_datamaster_error">'+json.errors+'</div>');
                $('#module_form .popup_exporting').removeClass('show');
            }
            else
            {
                var step = json.step;
                $('button[name="submitBack"]').removeAttr('disabled');
                $('input[name="step"]').val(step);
                if(step==4)
                {
                    $('#module_form .popup_exporting .export-wapper-percent').css('transition','all 0s ease 0s');
                    $('#module_form .popup_exporting .export-wapper-percent').css('width','100%');
                    $('#module_form .popup_exporting .percentage_export').html('100%');
                    $('#module_form .panel-footer').hide();
                    export_ok=true;
                    setTimeout(function(){
                    $('#module_form .popup_exporting').removeClass('show');
                        $('.tab_step_data .data_number_step').removeClass('active');
                        for(var i=1;i<=step;i++)
                        {
                           $('.tab_step_data .data_number_step[data-step="'+i+'"]').addClass('active');
                        }
                        $('.ybc-form-group.ybc-blog-tab-step'+step).html(json.form_step);
                        $('.ybc-form-group').removeClass('active');
                        $('.ybc-form-group.ybc-blog-tab-step'+step).addClass('active');
                    }, 1000);
                }
                else
                {
                    $('.tab_step_data .data_number_step').removeClass('active');
                    for(var i=1;i<=step;i++)
                    {
                       $('.tab_step_data .data_number_step[data-step="'+i+'"]').addClass('active');
                    }
                    $('.ybc-form-group.ybc-blog-tab-step'+step).html(json.form_step);
                    $('.ybc-form-group').removeClass('active');
                    $('.ybc-form-group.ybc-blog-tab-step'+step).addClass('active');
                }
            }
        },
        error: function(xhr, status, error)
        {
             processExportData(1)
        }
    });
}
function displayFormUpload()
{
    if($('#source_type').length)
    {
        if($('#source_type').val()=='upload_file')
        {
            $('.form-group.source.upload').show();
            $('.form-group.source.link').hide();
            $('.form-group.source.url_site').hide();
            $('.link_download_plugin').hide();
        }
        else if($('#source_type').val()=='link')
        {
            $('.form-group.source.upload').hide();
            $('.form-group.source.link').show();
            $('.form-group.source.url_site').hide();
            $('.link_download_plugin').hide();
        }
        else
        {
            $('.form-group.source.upload').hide();
            $('.form-group.source.link').hide();
            $('.form-group.source.url_site').show();
            $('.link_download_plugin').show();
        }
    }
}
function processExportConnector()
{
    if(getCookieEts('zip_file_name'))
    {
           var zip_file_name= getCookieEts('zip_file_name');
    }
    else
    {
        
        var zip_file_name= 'oc2m_data_'+gencodeEts(7);
        setCookieEts('zip_file_name',zip_file_name);
    }
    $.ajax({
        url:   ETS_DT_MODULE_URL_AJAX,
        data: 'presconnector=1&pres2prestocken='+$('#secure_access_tocken').val()+'&zip_file_name='+zip_file_name+'&link_site='+$('#link_site').val(),
        type: 'post',
        dataType: 'json',
        success: function(json){
            if(!json)
            {
                if(checkExportData())
                    processExportConnector();
            }  
            else
            {
                if(json.link_site_connector)
                {
                    $('#link_site_connector').val(json.link_site_connector);
                    if(ajaxPercentExport2)
                        clearInterval(ajaxPercentExport2);
                    if(time_counter)
                        clearTimeout(time_counter);
                    processImportData(true);
                    setCookieEts('zip_file_name','');
                }
                else
                {
                    if(checkExportData())
                         processExportConnector();
                }
                   
            }  
        },
        error: function(xhr, status, error)
        {
            if(checkExportData())
              processExportConnector();     
        }
    });
}
function gencodeEts(size)
{
	var code_value = '';
	var chars = "123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ";
	for (var i = 1; i <= size; ++i)
		code_value += chars.charAt(Math.floor(Math.random() * chars.length));
    return code_value;
}
function setCookieEts(cname, cvalue) {
    var d = new Date();
    d.setTime(d.getTime() + (24*60*60*1000));
    var expires = "expires="+ d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}
function getCookieEts(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return '';
}
function checkExportData()
{
    if(percent_export==percent_export2)
    {
        total_export++;
        if(time_counter=='')
        {
            time_counter = setTimeout(
            function(){
                passed_time=true;
            },max_time*60*1000);
        }
    }
    else
    {
        percent_export=percent_export2;
        total_export=0;
        clearTimeout(time_counter);
        time_counter = setTimeout(
        function(){
            passed_time=true;
        },max_time*60*1000);
    }
    if(total_export>max_export && passed_time)
    {
        if(ajaxPercentExport)
            clearInterval(ajaxPercentExport);
        $('#module_form .popup_uploading').removeClass('show');
        $('#module_form .popup_exporting').removeClass('show');
        $('#module_form .popup_importing').removeClass('show');
        $('#module_form .popup_uploading .upload-wapper-percent').css('width','0%'); 
        $('#module_form .popup_uploading .upload-wapper-percent').css('width','0%'); 
        $('#module_form .popup_exporting .export-wapper-percent').css('width','0%'); 
        $('.ybc-form-group').removeClass('active');
        $('.ybc-form-group.connector_error').addClass('active');
        return false;
    }
    else
    return true;
    
}
function checkImportData()
{
    if(percent_import==percent_import2)
    {
        total_import++;
    }
    else
    {
        percent_import=percent_import2;
        total_import=0;
        clearTimeout(time_counter);
        time_counter = setTimeout(
        function(){
            passed_time=true;
        },max_time*60*1000);
    }
    if(total_import>max_import && passed_time)
    {
        if(ajaxPercentImport)
            clearInterval(ajaxPercentImport);
        timer.stop();
        $('#module_form .popup_uploading').removeClass('show');
        $('#module_form .popup_exporting').removeClass('show');
        $('#module_form .popup_importing').removeClass('show');
        $('#module_form .popup_uploading .upload-wapper-percent').css('width','0%'); 
        $('#module_form .popup_exporting .export-wapper-percent').css('width','0%');
        $('#module_form .popup_importing .import-wapper-percent').css('width','0%');
        $('.ybc-form-group').removeClass('active');
        $('.ybc-form-group.import_error').addClass('active');
        return false;
    }
    else
        return true;
    
}
function ajaxPercentageExport2()
{
    if(getCookieEts('zip_file_name'))
    {
           var zip_file_name= getCookieEts('zip_file_name');
    }
    else
    {
        
        var zip_file_name= 'oc2m_data_'+gencodeEts(7);
        setCookieEts('zip_file_name',zip_file_name);
    }
    $.ajax({
        url:   ETS_DT_MODULE_URL_AJAX,
        data: 'presconnector=1&zip_file_name='+zip_file_name+'&ajaxPercentageExport=1&link_site='+$('#link_site').val(),
        type: 'post',
        dataType: 'json',
        success: function(json){
            if(!json)
                return false;
            if(json.percent>0 && json.percent<100 && !export_ok)
            {
                $('#module_form .popup_uploading .upload-wapper-percent').css('transition','all 3s ease 0s');
                $('#module_form .popup_uploading .upload-wapper-percent').css('width',json.percent+'%');
                $('#module_form .popup_uploading .percentage_export').html(json.percent+'%');
                if(json.table)
                    $('#module_form .popup_uploading .percentage_export_table').html('Exporting data from table <strong>"'+json.table+'"</strong>');
            }
            if(json.percent && json.percent!=100)
            {
                percent_export2=json.totalItemExported;
            }
        },
        error: function(xhr, status, error)
        {             
        }
    });
}
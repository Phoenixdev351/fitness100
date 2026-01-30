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
var ets_sp_images = "";
var ets_sp_browsed_images = [];
var ets_sp_id_images_uploading_cusrrent='';
var compress_upload_continue =  0;
var compress_browse_continue = 0;
$(document).ready(function(){
    if (window.File && window.FileReader && window.FileList && window.Blob) {	
		 document.getElementById('ets_sp_multiple_imamges').addEventListener("change", ets_sp_read_file, false);
		 //document.getElementById('form_id').addEventListener("submit", vpb_submit_added_files, true);
	}
    $(document).on('click','.optimize_upload_continue',function(e){
        $('.popup_optimizeing_wapper').remove();
        compress_upload_continue = 1;
        vpb_submit_added_files();
    });
    $(document).on('click','.optimize_browse_continue',function(){
        $('.popup_optimizeing_wapper').remove();
        compress_browse_continue = 1;
        sp_ajax_compress_image_browse(); 
    });
    $(document).on('click','.optimize_browse_stop,.optimize_stop',function(){
         $('.popup_optimizeing_wapper').remove();
         $('#list_added_browse_images li .progress.waiting > input[type="hidden"]').each(function(){
            var imageID = $(this).data('id');
             $('#list_added_browse_images li#image-'+imageID).remove();
             $('input#'+imageID).removeAttr('checked').removeAttr('disabled');  
             $('#item-'+imageID).parent().find('li.all input[type="checkbox"]').removeAttr('checked'); 
         });
    });
    $(document).on('click','.optimize_upload_stop,.optimize_stop',function(){
        $('.popup_optimizeing_wapper').remove();
        $('#ets_sp_multiple_imamges').removeAttr('disabled');
        $('#ets_sp_multiple_imamges').val('');
        $('#list_added_images li .progress.waiting').parent().remove(); 
    });
    $(document).on('click','.sp_cancel_upload_image',function(e){
        $(this).parent().parent().remove();
    });
    $(document).on('click','.sp_delete_browse_image',function(){
        $('input#'+$(this).data('id')).removeAttr('checked');
        $(this).parent().parent().remove();
        $.growl.notice({ message: deleted_successfully });
        $('#item-'+$(this).data('id')).parent().find('li.all input[type="checkbox"]').removeAttr('checked');
    });
    $(document).on('click','.sp_delete_image_upload',function(e){
        e.preventDefault();
        var $url_href = $(this).attr('href');
        var $this=$(this);
        if($url_href!='#')
        {
            $.ajax({
                url: $url_href,
                data: 'ajax=1',
                type: 'post',
                dataType: 'json',
                success: function(json){
                    if(json.success)
                    {
                        $.growl.notice({ message: json.success });
                        $this.parent().parent().remove();
                    }
                    if(json.error)
                        showErrorMessage(json.error);
                },
                error: function(xhr, status, error)
                {     
                }
            }); 
        }
        else
        {
           $this.parent().parent().remove(); 
           $.growl.notice({ message: deleted_successfully });
        }
    });
    $(document).on('click','.restore_image_browse',function(e){
        e.preventDefault();
        var $url_href = $(this).attr('href');
        $.ajax({
            url: $url_href,
            data: '',
            type: 'post',
            dataType: 'json',
            success: function(json){
                if(json.success)
                {
                    $.growl.notice({ message: json.success });
                    if(json.image_id)
                    {
                        $('li#image-'+json.image_id).remove();
                        $('input#'+json.image_id).removeAttr('disabled').removeAttr('checked');
                        $('input#'+json.image_id).parent().parent().find('>li.all input[type="checkbox"]').removeAttr('disabled').removeAttr('checked');
                    }
                }
                if(json.error)
                    showErrorMessage(json.error);
            },
            error: function(xhr, status, error)
            {
            }
        }); 
    });
    $(document).on('click','.list-browse-images > li.all > input[type="checkbox"]',function(){
        var check_all = $(this).is(':checked');
        var $checkboxs= $(this).parent().parent().find(' >li.file input[type="checkbox"]');
        if(check_all)
        {
            if(!confirm(comfirm_all_image))
            {
                $(this).prop('checked',false);
                return false;
            }
        }
        if($checkboxs)
        {
            $checkboxs.each(function(){
               if($(this).attr('disabled')!='disabled')
                {
                   if(check_all)
                        $(this).prop('checked',true);
                   else
                        $(this).prop('checked',false);
                   sp_add_image_browse_to_list($(this));
                } 
            });
            sp_ajax_compress_image_browse();
        }
    });
    $(document).on('click','.list-browse-images > li.folder > input[type="checkbox"]',function(){
        var check_all = $(this).is(':checked');
        var item_id = $(this).attr('id');
        if($('.list-browse-images #item-'+item_id).hasClass('folder-hide'))
        {
            var $this= $('.list-browse-images #item-'+item_id);
            $this.removeClass('folder-hide');
            if($this.find('.list-browse-images').length==0)
            {
                $.ajax({
                    url: '',
                    data: {
                        btnSubmitGlobImagesToFolder:1,
                        folder: $('.list-browse-images #item-'+item_id+' > .open-close-folder').data('folder'),
                    },
                    type: 'post',
                    dataType: 'json',
                    success: function(json){
                        $this.append(json.list_files);
                        $('.list-browse-images #item-'+item_id+' > ul li.file input[type="checkbox"]').each(function(){
                            if($(this).attr('disabled')!='disabled')
                            {
                                if(check_all)
                                    $(this).prop('checked',true);
                               else
                                    $(this).prop('checked',false);
                               sp_add_image_browse_to_list($(this));
                            }
                        });
                        sp_ajax_compress_image_browse();
                    },
                    error: function(xhr, status, error)
                    {     
                    }
                });
            }
            else
            {
                $('.list-browse-images #item-'+item_id+' > ul li.file input[type="checkbox"]').each(function(){
                    if($(this).attr('disabled')!='disabled')
                    {
                       if(check_all)
                            $(this).prop('checked',true);
                       else
                            $(this).prop('checked',false);
                       sp_add_image_browse_to_list($(this));
                    }
                });
                sp_ajax_compress_image_browse();
            }
        }
        else
        {
            $('.list-browse-images #item-'+item_id+' > ul li.file input[type="checkbox"]').each(function(){
               if($(this).attr('disabled')!='disabled')
               {
                    if(check_all)
                        $(this).prop('checked',true);
                    else
                        $(this).prop('checked',false);
                    sp_add_image_browse_to_list($(this));
               }
            });
            sp_ajax_compress_image_browse();
        }
        
    });
    $(document).on('click','.list-browse-images > li.file input[type="checkbox"]',function(){
        sp_add_image_browse_to_list($(this));
        if($(this).is(':checked'))
        {
            if($(this).parent().parent().find('> li.file input[type="checkbox"]').length==$(this).parent().parent().find('> li.file input[type="checkbox"]:checked').length)
            {
                $(this).parent().parent().find('>li.all input[type="checkbox"]').prop('checked',true);
            }
        }    
        else
        {  
            $(this).parent().parent().find('>li.all input[type="checkbox"]').prop('checked',false);
        }
        sp_ajax_compress_image_browse();
    });
    $(document).on('click','.list-browse-images > li.folder .open-close-folder',function(){
        var $this= $(this);
        $this.parent().toggleClass('folder-hide');
        if($this.parent().find('.list-browse-images').length==0)
        {
            $('.block-browse-image-left').addClass('loading');
            $.ajax({
                url: '',
                data: {
                    btnSubmitGlobImagesToFolder:1,
                    folder: $this.data('folder'),
                },
                type: 'post',
                dataType: 'json',
                success: function(json){
                    if(json.list_files)
                        $this.parent().append(json.list_files);
                    $('.block-browse-image-left').removeClass('loading');
                },
                error: function(xhr, status, error)
                {     
                    $('.block-browse-image-left').removeClass('loading');
                }
            });
        }
        
    });
});
function ets_sp_show_added_files(file_images)
{
        ets_sp_images = file_images;
		if(ets_sp_images.length > 0)
		{
			var html_li = "";
 			for(var i = 0; i<ets_sp_images.length; i++)
			{
				//Use the names of the files without their extensions as their ids
				var files_name_without_extensions = ets_sp_images[i].name.substr(0, ets_sp_images[i].name.lastIndexOf('.')) || ets_sp_images[i].name;
				image_id = files_name_without_extensions.replace(/[^a-z0-9\s]/gi, '').replace(/[_\s]/g, '');
				var ets_sp_fileSize = (ets_sp_images[i].size / 1024);
				if (ets_sp_fileSize / 1024 > 1)
				{
					if (((ets_sp_fileSize / 1024) / 1024) > 1)
					{
						ets_sp_fileSize = (Math.round(((ets_sp_fileSize / 1024) / 1024) * 100) / 100);
						var ets_sp_actual_fileSize = ets_sp_fileSize + " GB";
					}
					else
					{
						ets_sp_fileSize = (Math.round((ets_sp_fileSize / 1024) * 100) / 100)
						var ets_sp_actual_fileSize = ets_sp_fileSize + " MB";
					}
				}
				else
				{
					ets_sp_fileSize = (Math.round(ets_sp_fileSize * 100) / 100)
					var ets_sp_actual_fileSize = ets_sp_fileSize  + " KB";
				}
				if(typeof ets_sp_images[i] != undefined && ets_sp_images[i].name != "")
				{
					html_li += '<li class="upload" id="image-'+ets_sp_browsed_images.length+'-'+image_id+'">';
                    html_li += '<div class="before"><span class="image_name" title="'+ets_sp_images[i].name+'">'+(ets_sp_images[i].name.length > 23 ? ets_sp_images[i].name.substr(0,11)+' . . . '+ets_sp_images[i].name.substr(ets_sp_images[i].name.length-12):ets_sp_images[i].name  )+' ('+ets_sp_actual_fileSize+')</span></div>';
                    html_li += '<div class="progress waiting"><div class="progress_bar"><div class="bar" style="width: 0%;"></div><div class="status">'+image_waiting_text+'</div></div></div>';
                    html_li +='<div class="after"><span class="size"></span><span class="sp_cancel_upload_image">'+cancel_text+'</span></div>';
                    html_li +='</li>';
				}
			}
            if($("#list_added_images li").length)
                $("#list_added_images li:first").before(html_li);
            else
			     $("#list_added_images").append(html_li);
		}
}
function ets_sp_read_file(vpb_e)
{
    compress_upload_continue=0;
    if(vpb_e.target.files) {
        ets_sp_show_added_files(vpb_e.target.files);
		ets_sp_browsed_images.push(vpb_e.target.files);
        vpb_submit_added_files();
	} else {
		alert('Sorry, a file you have specified could not be read at the moment. Thank You!');
	}
}
function vpb_submit_added_files()
{
    if(ets_sp_browsed_images.length > 0) {
        $('#ets_sp_multiple_imamges').attr('disabled','disabled');
        ets_sp_ajaxuploadmultipleimage(ets_sp_browsed_images[ets_sp_browsed_images.length-1],0);
	}
    
}
function ets_sp_ajaxuploadmultipleimage(file,file_counter)
{
    if(typeof file[file_counter] != undefined && file[file_counter] != '')
	{
		//Use the file names without their extensions as their ids
		var files_name_without_extensions = file[file_counter].name.substr(0, file[file_counter].name.lastIndexOf('.')) || file[file_counter].name;
		ets_sp_id_images_uploading_cusrrent =(ets_sp_browsed_images.length-1)+'-'+files_name_without_extensions.replace(/[^a-z0-9\s]/gi, '').replace(/[_\s]/g, '');
        if($('#list_added_images li#image-'+ets_sp_id_images_uploading_cusrrent+' .progress').length >0 && $('#list_added_images li#image-'+ets_sp_id_images_uploading_cusrrent+' .progress').hasClass('waiting'))
        {
            var dataString = new FormData();
    		dataString.append('upload_image',file[file_counter]);
    		dataString.append('submitUploadImageSave',1);
            dataString.append('ETS_SPEED_OPTIMIZE_SCRIPT_UPLOAD',$('#ETS_SPEED_OPTIMIZE_SCRIPT_UPLOAD').val());
            dataString.append('ETS_SPEED_QUALITY_OPTIMIZE_UPLOAD',$('#ETS_SPEED_QUALITY_OPTIMIZE_UPLOAD').val());
    		$.ajax({
    			type:"POST",
    			url:link_ajax_submit,
    			data:dataString,
                xhr: function() {
                    var myXhr = $.ajaxSettings.xhr();
                    if(myXhr.upload){
                        myXhr.upload.addEventListener('progress',progress, false);
                    }
                    return myXhr;
                },
                dataType: 'json',
    			cache: false,
    			contentType: false,
    			processData: false,
    			beforeSend: function() 
    			{
    			     
                     $('#list_added_images li#image-'+ets_sp_id_images_uploading_cusrrent+' .after .sp_cancel_upload_image').remove();
    			     $('#list_added_images li#image-'+ets_sp_id_images_uploading_cusrrent+' .progress').removeClass('waiting').addClass('uploading');
                     $('#list_added_images li#image-'+ets_sp_id_images_uploading_cusrrent+' .progress .status').html(image_loading_text);
    			},
    			success:function(response) 
    			{
                    if(response.success)
                    {
                        $('#list_added_images li#image-'+ets_sp_id_images_uploading_cusrrent+' .progress').removeClass('uploading').addClass('compressing');
                        $('#list_added_images li#image-'+ets_sp_id_images_uploading_cusrrent+' .progress .status').html(image_compressing_text);
                        $('#list_added_images li#image-'+ets_sp_id_images_uploading_cusrrent+' .progress .bar').css('width','100%'); 
                        $.ajax({
                            url: link_ajax_submit,
                            data:{
                                'submitUploadImageCompress' : 1,
                                'image':response.image,
                                'image_name': response.image_name,
                                'file_size': response.file_size,
                                'continue': compress_upload_continue,
                            },
                            type: 'post',
                            dataType: 'json',
                            success: function(json){
                                if(json.error)
                                {
                                    var html = '<div class="popup_optimizeing_wapper"><div class="popup_optimizeing">';
                                    html += '<div class="popup-title"><h3>'+optimize_title_text+'</h3><span class="optimize_stop" title="Close">Close</span></div>';
                                    html += '<div class="popup_error"><p>'+popup_error+'</p>'+json.error+'<div class="popup_continue"><p>'+(json.script_continue=='php' ? continue_question : continue_question_webp)+'</p> <button class="btn btn-default optimize_upload_continue">'+continue_text+'</button>  <button class="btn btn-default optimize_upload_stop">'+no_continue_text+'</button></div></div>';
                                    html += '</div></div>';
                                    if(!$('#configuration_form .popup_optimizeing_wapper').length)
                                        $('#configuration_form .panel-footer').before(html);
                                    $('#list_added_images li#image-'+ets_sp_id_images_uploading_cusrrent+' .progress').removeClass('uploading').removeClass('compressing').addClass('waiting');
                                    $('#list_added_images li#image-'+ets_sp_id_images_uploading_cusrrent+' .progress .status').html(image_waiting_text);
                                }
                                else
                                {
                                    if(json)
                                    {
                                        $('#list_added_images li#image-'+ets_sp_id_images_uploading_cusrrent+' .progress').removeClass('compressing').removeClass('uploading').addClass('success');
                                        $('#list_added_images li#image-'+ets_sp_id_images_uploading_cusrrent+' .after .size').html(json.file_size);
                                        $('#list_added_images li#image-'+ets_sp_id_images_uploading_cusrrent+' .after .size').after('<a class="sp_delete_image_upload" href="'+json.link_delete+'" title="'+delete_text+'">'+delete_text+'</a>');
                                        $('#list_added_images li#image-'+ets_sp_id_images_uploading_cusrrent+' .after .size').after('<a class="" href="'+json.link_download+'"><i class="fa fa-download"></i> '+download_text+'</a>');
                                        $('#list_added_images li#image-'+ets_sp_id_images_uploading_cusrrent+' .progress .status').html(image_finished_text+' (<span class="saved">'+save_text+' '+json.saved+'</span>)');
                                    }
                                    setTimeout(function() {
                    					if (file_counter+1 < file.length ) {
                    						ets_sp_ajaxuploadmultipleimage(file,file_counter+1); 
                    					}
                                        else
                                        {
                                            $('#ets_sp_multiple_imamges').removeAttr('disabled');
                                            $('#ets_sp_multiple_imamges').val('');
                                        }
                                            
                    				},1000);
                                }
                                
                            },
                            error: function(xhr, status, error)
                            {    
                                $('#list_added_images li#image-'+ets_sp_id_images_uploading_cusrrent+' .progress').removeClass('compressing').removeClass('uploading').addClass('error');
                                $('#list_added_images li#image-'+ets_sp_id_images_uploading_cusrrent+' .progress .status').html('Compress error');
                                $('#list_added_images li#image-'+ets_sp_id_images_uploading_cusrrent+' .after .size').after('<a class="sp_delete_image_upload" href="#" title="'+delete_text+'">'+delete_text+'</a>');
                                setTimeout(function() {
                					if (file_counter+1 < file.length ) {
                						ets_sp_ajaxuploadmultipleimage(file,file_counter+1); 
                					}
                                    else
                                    {
                                        $('#ets_sp_multiple_imamges').removeAttr('disabled');
                                        $('#ets_sp_multiple_imamges').val('');
                                    }
                				},1000);
                            }
                        });
                    }
                    if(response.errors)
                    {
                        $('#list_added_images li#image-'+ets_sp_id_images_uploading_cusrrent+' .progress').removeClass('compressing').removeClass('uploading').addClass('error');
                        $('#list_added_images li#image-'+ets_sp_id_images_uploading_cusrrent+' .progress .status').html(response.errors);
                        $('#list_added_images li#image-'+ets_sp_id_images_uploading_cusrrent+' .after .size').after('<a class="sp_delete_image_upload" href="#" title="'+delete_text+'">'+delete_text+'</a>');
                        setTimeout(function() {
        					if (file_counter+1 < file.length ) {
        						ets_sp_ajaxuploadmultipleimage(file,file_counter+1); 
        					}
                            else
                            {
                                $('#ets_sp_multiple_imamges').removeAttr('disabled');
                                $('#ets_sp_multiple_imamges').val('');
                            }
        				},1000);
                    }
                    
    			},
                error: function(xhr, status, error)
                {
                    $('#list_added_images li#image-'+ets_sp_id_images_uploading_cusrrent+' .progress').removeClass('compressing').removeClass('uploading').addClass('error');
                    $('#list_added_images li#image-'+ets_sp_id_images_uploading_cusrrent+' .progress .status').html('Upload error');
                    $('#list_added_images li#image-'+ets_sp_id_images_uploading_cusrrent+' .after .size').after('<a class="sp_delete_image_upload" href="#" title="'+delete_text+'">'+delete_text+'</a>');
                    setTimeout(function() {
    					if (file_counter+1 < file.length ) {
    						ets_sp_ajaxuploadmultipleimage(file,file_counter+1); 
    					}
                        else
                        {
                            $('#ets_sp_multiple_imamges').removeAttr('disabled');
                            $('#ets_sp_multiple_imamges').val('');
                        }
    				},1000);
                }
    		});
        }
        else
        {
            setTimeout(function() {
				if (file_counter+1 < file.length ) {
					ets_sp_ajaxuploadmultipleimage(file,file_counter+1); 
				}
                else
                {
                    $('#ets_sp_multiple_imamges').removeAttr('disabled');
                    $('#ets_sp_multiple_imamges').val('');
                }
			},1000);
        }
        
	}
}
function progress(e){
    if(e.lengthComputable){
        var max = e.total;
        var current = e.loaded;
        var Percentage = (current * 100)/max;
        if(Percentage < 100)
        {
           $('#list_added_images li#image-'+ets_sp_id_images_uploading_cusrrent+' .progress .bar').css('width',Percentage+'%'); 
        }
    }  
 }
 function ets_sp_file_ext(file) {
	return (/[.]/.exec(file)) ? /[^.]+$/.exec(file.toLowerCase()) : '';
}
function sp_add_image_browse_to_list($input)
{
    if($input.is(':checked'))
    {
        if($('#list_added_browse_images li#image-'+$input.attr('id')).length==0)
        {
            var image_name = $input.next('.open_close-file').html();
            var $html = '<li class="upload" id="image-'+$input.attr('id')+'">';
                    $html += '<div class="before">';
                        $html += '<span class="image_name" title="'+image_name+'">'+(image_name.length >23 ? image_name.substr(0,11)+' . . . '+image_name.substr(image_name.length-12):image_name)+' ('+$input.data('file_size')+')</span>';
                    $html += '</div>';
                    $html += '<div class="progress waiting">';
                        $html += '<div class="progress_bar">';
                            $html += '<input type="hidden" name="browse_images[]" value="'+$input.val()+'" data-id="'+$input.attr('id')+'" />';
                            $html += '<div class="image_dir"></div>';
                            $html += '<div class="bar" style="width: 100%;"></div>';
                            $html += '<div class="status">'+image_waiting_text+'</div>';
                        $html += '</div>';
                    $html += '</div>';
                    $html += '<div class="after">';
                        $html += '<span class="size"></span>';
                        $html += '<span class="sp_delete_browse_image" title="'+delete_text+'" data-id="'+$input.attr('id')+'">'+delete_text+'</span>';
                    $html +='</div>';
                $html +='</li>';
            if($('#list_added_browse_images li').length>0)
                $('#list_added_browse_images li:first').before($html);
            else
                $('#list_added_browse_images').append($html);
        }
    }
    else
    {
        if($('#list_added_browse_images li#image-'+$input.attr('id')).length>0)
                $('#list_added_browse_images li#image-'+$input.attr('id')).remove();
    }
    
}
function sp_ajax_compress_image_browse()
{
    if($('#list_added_browse_images >li .progress.compressing').length==0 && $('#list_added_browse_images >li .progress.waiting').length >0 )
    {
        $('#list_added_browse_images >li .progress.waiting:last').removeClass('waiting').addClass('compressing');
        var dataString = new FormData();
		dataString.append('image',$('#list_added_browse_images >li .progress.compressing input[type="hidden"]').val());
		var ets_sp_id_images_compressing_cusrrent = $('#list_added_browse_images >li .progress.compressing input[type="hidden"]').data('id');
        dataString.append('submitBrowseImageOptimize',1);
        if(compress_browse_continue)
            dataString.append('continue',compress_browse_continue);
		$.ajax({
			type:"POST",
			url:link_ajax_submit,
			data:dataString,
            dataType: 'json',
			cache: false,
			contentType: false,
			processData: false,
            beforeSend: function() 
			{
                 $('input#'+ets_sp_id_images_compressing_cusrrent).attr('disabled','disabled');
                 $('#list_added_browse_images li#image-'+ets_sp_id_images_compressing_cusrrent+' .progress .status').html(image_compressing_text);
                 if($('input#'+ets_sp_id_images_compressing_cusrrent).parent().parent().find('> li.file input[type="checkbox"]').length==$('input#'+ets_sp_id_images_compressing_cusrrent).parent().parent().find('> li.file input[type="checkbox"]:disabled').length)
                 {
                    $('input#'+ets_sp_id_images_compressing_cusrrent).parent().parent().find('>li.all input[type="checkbox"]').attr('disabled','disabled');
                 }
                 $('#list_added_browse_images li#image-'+ets_sp_id_images_compressing_cusrrent+' .after .sp_delete_browse_image').remove();
			},
			success: function(json){
                if(json.error)
                {
                    var html = '<div class="popup_optimizeing_wapper"><div class="popup_optimizeing">';
                    html += '<div class="popup-title"><h3>'+optimize_title_text+'</h3><span class="optimize_stop" title="Close">Close</span></div>';
                    html += '<div class="popup_error"><p>'+popup_error+'</p>'+json.error+'<div class="popup_continue"><p>'+(json.script_continue=='php' ? continue_question : continue_question_webp)+'</p> <button class="btn btn-default optimize_browse_continue">'+continue_text+'</button>  <button class="btn btn-default optimize_browse_stop">'+no_continue_text+'</button></div></div>';
                    html += '</div></div>';
                    if(!$('#configuration_form .popup_optimizeing_wapper').length)
                        $('#configuration_form .panel-footer').before(html);
                    $('#list_added_browse_images li#image-'+ets_sp_id_images_compressing_cusrrent+' .progress').removeClass('compressing').addClass('waiting');
                }
                else{
                    if(json)
                    {
                        $('#list_added_browse_images li#image-'+ets_sp_id_images_compressing_cusrrent+' .progress').removeClass('compressing').addClass('success');
                        $('#list_added_browse_images li#image-'+ets_sp_id_images_compressing_cusrrent+' .after .size').html(json.file_size);
                        $('#list_added_browse_images li#image-'+ets_sp_id_images_compressing_cusrrent+' .after .size').after('<a class="restore_image_browse" href="'+json.link_restore+'"><i class="fa fa-undo" aria-hidden="true"></i> '+restore_text+'</a>');
                        $('#list_added_browse_images li#image-'+ets_sp_id_images_compressing_cusrrent+' .after .size').after('<a class="" href="'+json.link_download+'"><i class="fa fa-download" aria-hidden="true"></i> '+download_text+'</a>');
                        $('#list_added_browse_images li#image-'+ets_sp_id_images_compressing_cusrrent+' .progress .image_dir').html(json.image_dir+' (<span class="saved">'+save_text+' '+json.saved+'</span>)');
                        $('#list_added_browse_images li#image-'+ets_sp_id_images_compressing_cusrrent+' .progress .status').remove();
                    }
                    sp_ajax_compress_image_browse();
                }
            },
            error: function(xhr, status, error)
            {    
                $('#list_added_browse_images li#image-'+ets_sp_id_images_compressing_cusrrent+' .progress').removeClass('compressing').addClass('error');
                $('#list_added_browse_images li#image-'+ets_sp_id_images_compressing_cusrrent+' .progress .status').html('Compress error');
                sp_ajax_compress_image_browse();
            }
		}); 
    }
}
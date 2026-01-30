/*
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    ST-themes <hellolee@gmail.com>
*  @copyright 2007-2017 ST-themes
*  @license   Use, by you or one client for one Prestashop instance.
*/
var imObj = {
    'kaishi': 0,
    'ting': 0,
    'reg': 0,
    'process':{
        'categories':0,
        'manufacturers':0,
        'suppliers':0,
        'products':0,
        'stores':0,
        'nopictures':0,
        'articles':0,
        'stbanner':0,
        'stswiper':0,
        'stowlcarousel':0
    },
    //0/1queue/2ing/3done/4noneselect
    'requestData': {},
    'initProcess': function(){
        $('.fenlei_group').each(function(){
            if($(this).prop("checked"))
                imObj.process[$(this).val()] = 1;
            else
                imObj.process[$(this).val()] = 4;
        });
        return ;
    },
    'restProcess': function(val){
        $.each(imObj.process, function(k,v){
            imObj.process[k] = val;
        });
    },
    'getImageTypes': function(){
        var vals = [];
        $.each($(".image_type_products:checked"), function(){            
            vals.push($(this).val());
        });
        return vals.join(",");
    },
    'start': function(){
        if(imObj.ting){
            imObj.handle_class(false);
            imObj.master(2);
            return false;
        }
        imObj.master(3);
        if(!imObj.kaishi){
            imObj.requestData['erase'] = $('input[name="erase"]:checked').val();
            imObj.requestData['thumb_format'] = $('input[name="thumb_format"]:checked').val();
            // imObj.requestData['start_id'] = $('input[name="start_id"]').val();
            // imObj.requestData['end_id'] = $('input[name="end_id"]').val();
            imObj.requestData['per_time'] = $('input[name="per_time"]').val();
            imObj.requestData['met'] = $('input[name="met"]').val();
            imObj.requestData['image_type'] = imObj.getImageTypes();
            imObj.initProcess();
            if(imObj.reg){
                var reg_arr = [];
                $.each(imObj.process, function(k,v){
                    if(v==1){
                        $('#fenlei_info_'+k+' .indexed_number').html('0');
                        reg_arr.push(k);
                    }
                });
                imObj.requestData['reg'] = reg_arr.join(",");
            }
            imObj.clear_error();
            imObj.clear_message();
        }else{
            imObj.requestData['reg'] = '';
        }
        /*if(imObj.requestData['start_id'] && imObj.requestData['end_id'] && imObj.requestData['start_id']>imObj.requestData['end_id']){
            imObj.master(2);
            return false;
        }*/
        imObj.kaishi++;
        // imObj.requestData['counter'] = imObj.kaishi;
        imObj.requestData['stgenerate'] = new Date().getTime();
        var current = imObj.master(0);
        if(!current){
            imObj.stop(2);//done
            return false;
        }
        imObj.requestData['fenlei'] = current;
        imObj.process[current] = 2;
        imObj.handle_class(current);  
        $.ajax({
          url: window.location.href,
          method: 'POST',
          data: imObj.requestData,
          dataType: 'json'
        }).then(function (resp) {
            if(!resp || typeof(resp)=='undefined' || typeof(resp.errors)=='undefined'){//if not responsed by this module
                imObj.stop(2);
                return false;
            }
            if(resp.errors && resp.errors.length){
                imObj.show_message(resp.errors);
                imObj.stop(2);
            }else if(resp.warnings && resp.warnings.length){
                imObj.show_message(resp.warnings);
                imObj.process[imObj.requestData['fenlei']] = 3;//force end
            }else{
                if(resp.messages && resp.messages.length){
                    imObj.show_message(resp.messages);               
                }
                if(resp.done){
                    imObj.process[imObj.requestData['fenlei']] = 3;  
                    if(resp.indexed && resp.total){
                        $('#fenlei_info_'+imObj.requestData['fenlei']+' .indexed_number').html(resp.indexed);
                        $('#fenlei_info_'+imObj.requestData['fenlei']+' .total_number').html(resp.total);
                    }            
                }else{
                    $('#fenlei_info_'+imObj.requestData['fenlei']+' .indexed_number').html(resp.indexed);
                    $('#fenlei_info_'+imObj.requestData['fenlei']+' .total_number').html(resp.total);
                }
            }
            imObj.start();
        }).fail(function(resp) {
            imObj.show_error([st_re_generate_warning_3]);
            imObj.stop(2);//error
        });
    },
    'show_error': function(messages){
        $.each(messages, function (index, mes) {
          if (mes) {
            $('#st_generate_info').append('<p>'+mes+'</p>');
          }
        });
    },
    'clear_error': function(messages){
        $('#st_generate_info').empty();
    },
    'show_message': function(messages){
        $.each(messages, function (index, mes) {
          if (mes) {
            $('#st_generate_message').append('<p>'+mes+'</p>');
          }
        });
    },
    'clear_message': function(messages){
        $('#st_generate_message').empty();
    },
    'handle_class': function(current){
        $('.fenlei_current').removeClass('fenlei_current');
        if(current)
            $('#fenlei_'+current).parent().addClass('fenlei_current')
    },
    'stop': function(s){
        if(s!=4)
            imObj.handle_class(false);
        imObj.ting=1;
        imObj.kaishi=0;
        imObj.reg=0;
        imObj.master(s);
    },
    'master': function(s){
        var current = next = '';
        var has_more = false;
        $.each(imObj.process, function(k,v){
            if(!has_more && (v==1 || v==2))
                has_more = true;
            if(!current && v==2){
                current = k;
            }
            if(!next && v==1){
                next = k;
            }
        });

        if(s==1){//error
            $('#st_generate_button_stop').attr('disabled', 'disabled');
            $('#st_generate_button_start, #st_generate_button_re').removeAttr('disabled');
        }else if(s==2){//ting
            $('#st_generate_button_stop').attr('disabled', 'disabled');
            $('#st_generate_button_start, #st_generate_button_re').removeAttr('disabled');
        }else if(s==3){//satrt
            $('#st_generate_button_start, #st_generate_button_re').attr('disabled', 'disabled');
            $('#st_generate_button_stop').removeAttr('disabled');
        }else if(s==4){//stop in process
            $('#st_generate_button_start, #st_generate_button_stop, #st_generate_button_re').attr('disabled', 'disabled');
        }
        return current ? current : next;
    }
};
var check_un_another_type = function(type,value,ischeck){
    var image_type = value.slice(-3)=='_2x' ? value.slice(0,-3) : value+'_2x';
    $('#'+type+'_'+image_type).prop('checked', ischeck);
    return;
};
jQuery(function($){
    $(document).on('click', '#st_generate_button_start', function(){
        if(!imObj.kaishi){
            if($('input[name="erase"]:checked').val()==1 && !confirm(st_re_generate_warning_2))
                return false;
            imObj.ting=0;
            imObj.start();
        }
    });
    $(document).on('click', '#st_generate_button_stop', function(){
        if(imObj.kaishi){
            imObj.stop(4);
        }
    });
    $(document).on('click', '#st_generate_button_re', function(){
        if(!imObj.kaishi){
            if(!confirm(st_re_generate_warning_1))
                return false;
            if($('input[name="erase"]:checked').val()==1 && !confirm(st_re_generate_warning_2))
                return false;
            imObj.ting=0;
            imObj.reg=1;
            imObj.start();
        }
    });

    $(document).on('change', '.image_type_products', function(){
        check_un_another_type('image_type',$(this).val(),$(this).prop("checked"));
    });
    $(document).on('change', '.webp_image_type', function(){
        check_un_another_type('webp_image_type',$(this).val(),$(this).prop("checked"));
    });
    $('#fenlei_products').click(function(){
        if ($(this).attr('checked')) {
            $('.fenlei-products').show();
        } else {
            $('.fenlei-products').hide();
        }
    });
});
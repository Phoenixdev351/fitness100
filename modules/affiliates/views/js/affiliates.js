/**
 * Affiliates
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @author    FMM Modules
 * @copyright © Copyright 2021 - All right reserved
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @category  FMM Modules
 * @package   affiliates
 */
function rewardTrigger(el) {
    el = parseInt(el);
    if (el > 1) {
        $('.value_fld').parent().parent().hide();
        $('.type_of_value_fld').parent().parent().show();
        if (el == 2) {
            $('.categories_fld').parent().parent().hide();
            $('.products_fld').parent().parent().show();
        }
        else {
            $('.categories_fld').parent().parent().show();
            $('.products_fld').parent().parent().hide();
        }
    }
    else {
        $('.value_fld').parent().parent().show();
        $('.type_of_value_fld').parent().parent().hide();
        $('.products_fld, .categories_fld').parent().parent().hide();
    }
    //console.log('DD Type: '+el);
}

function getRelProducts(e) {
    var search_q_val = $(e).val();
    if (typeof search_q_val !== 'undefined' && search_q_val) {
        $.ajax({
            type: 'GET',
            dataType: 'json',
            url: mod_url + '&q=' + search_q_val,
            success: function(data)
            {
                var quicklink_list ='<li class="rel_breaker" onclick="relClearData();"><i class="icon-remove"></i></li>';
                $.each(data, function(index,value){
                    if (typeof data[index]['id'] !== 'undefined')
                        quicklink_list += '<li onclick="relSelectThis('+data[index]['id']+','+data[index]['id_product_attribute']+',\''+data[index]['name']+'\',\''+data[index]['image']+'\');"><img src="' + data[index]['image'] + '" width="60"> ' + data[index]['name'] + '</li>';
                });
                if (data.length == 0) {
                    quicklink_list = '';
                }
                $('#rel_holder').html('<ul>'+quicklink_list+'</ul>');
            },
            error : function(XMLHttpRequest, textStatus, errorThrown) {
                console.log(textStatus);
            }
        });
    }
    else {
        $('#rel_holder').html('');
    }
}
function relSelectThis(id, ipa, name, img) {
    if ($('#row_' + id).length > 0) {
        showErrorMessage(error_msg);
    } else {
      var draw_html = '<li id="row_' + id + '" class="media"><div class="media-left"><img src="'+img+'" class="media-object image"><span class="label">'+name+'&nbsp;(ID:'+id+')</span></div><div class="media-body media-middle"><input type="text" placeholder="1.234" name="related_products['+id+']"><i onclick="relDropThis(this);" class="icon-remove"></i></div></li>'
      $('#rel_holder_temp ul').append(draw_html);
    }
}
function relClearData() {
    $('#rel_holder').html('');
}
function relDropThis(e) {
    $(e).parent().parent().remove();
}

$(document).ready(function() {
    $('.type_of_value_fld, .products_fld, .categories_fld').parent().parent().hide();
    $('select#reward_type').trigger('onchange');
});
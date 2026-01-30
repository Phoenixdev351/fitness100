/*
* 2007-2015 PrestaShop
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
* @author PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2015 PrestaShop SA
* @license http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*/

/**
* Add loved product
*
* @return void
*/
function stLovedAddProduct(dom)
{
    if (typeof st_myloved_url == 'undefined')
		  return false;

    var id_source = $(dom).data('id-source');
    if(!id_source)
      return false;
    var unlove = $(dom).hasClass('st_added');//in gird the first click is alawys adding, being ingored if added, the sccond does jian shao 
    var type = $(dom).data('type');

    // $(dom).toggleClass('st_added');
    $('.love_'+type+'_'+id_source).toggleClass('st_added');

    $.ajax({
        type: 'GET',
        url: st_myloved_url,
        headers: { "cache-control": "no-cache" },
        async: true,
        cache: false,
        dataType: "json",
        data: {
            type: $.isNumeric(type) ? type : 1,
            id_source:id_source,
            ajax: 1,
            unloveable: (unlove ? 1 : 0),//do removing function to loved product on the product page
            action: 'addLovedProduct'
        },
        success: function (resp)
        {
          //improve this
            if($('.amount_inline', dom).length ){
              $('.amount_inline', dom).html(resp.total ? resp.total : 0);
            }
            $('.products_loved_nbr').html(resp.all);
            
            if(!resp.success)
            {
                stLovedPopup(resp.message);
                $('.love_'+type+'_'+id_source).toggleClass('st_added');
            }
            else if(resp.success==2)
            {
                stLovedGoLogin();
                $('.love_'+type+'_'+id_source).toggleClass('st_added');
            }
            else if(resp.success==1)
            {
              if(resp.sidebar && $('#side_loved').length){
                $('#side_loved').replaceWith(resp.sidebar);
              }
              /*if(typeof(resp.action)!=='undefined')
              {
                if(resp.action)
                  $('.love_'+type+'_'+id_source).addClass('st_added');
                else
                  $('.love_'+type+'_'+id_source).removeClass('st_added');
              }*/
            }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown)
        {
            $('.love_'+type+'_'+id_source).toggleClass('st_added');
        }
    });

}
function stLovedProductRemove(id_source, type)
{
	if (typeof st_myloved_url == 'undefined')
		return (false);

	$.ajax({
		type: 'GET',
		url: st_myloved_url,
		headers: { "cache-control": "no-cache" },
		async: true,
		cache: false,
		dataType: "json",
		data: {
      type: $.isNumeric(type) ? type : 1,
			id_source:id_source,
			ajax: 1,
			action: 'deleteProduct'
		},
		success: function (data)
		{
      $('.products_loved_nbr').toggleClass('lingge', data.total==0).html(data.all);
      $('.loved_remove_product.active').removeClass('active');
			if (data.success==1) {
                $('.loved_product_item[data-id_source='+id_source+']').empty();
			}
			else if(!data.success)
			{
				stLovedPopup(resp.message);
			}
      else if(resp.success==2)
        stLovedGoLogin();
		},
    error: function(XMLHttpRequest, textStatus, errorThrown)
    {
    }
	});
}
function stLovedPopup(msg)
{
    $.magnificPopup.open({
      removalDelay: 500,
      callbacks: {
        beforeOpen: function() {
           this.st.mainClass = 'mfp-zoom-in';
        }
      },
      items: {
          src: '<div class="inline_popup_content small_popup mfp-with-anim text-center">'+msg+'</div>',
          type: 'inline'
      }
    });
}
function stLovedGoLogin()
{
    $.magnificPopup.open({
      removalDelay: 500,
      callbacks: {
        beforeOpen: function() {
           this.st.mainClass = 'mfp-zoom-in';
        }
      },
      items: {
          src: '#loved_go_login',
          type: 'inline'
      }
    });
}
function stLovedUpdateInCache(){
  var selector = '';
  if(typeof(stlove_pros)!='undefined' && stlove_pros.length){
    selector += '.love_1_'+stlove_pros.join(',.love_1_');
  }
  if(typeof(stlove_bos)!='undefined' && stlove_bos.length){
    selector += (stlove_pros.length ? ',':'')+'.love_2_'+stlove_bos.join(',.love_2_');
  }
  if(selector)
    $(selector).addClass('st_added');
}
$(document).ready(function () {
    $('body').on('click', '.loved_remove_product', function (event) {
        event.preventDefault();
        var dataset = $(this).closest('.loved_product_item').data();
        if(dataset.id_source){
            $(this).addClass('active');
            stLovedProductRemove(dataset.id_source, dataset.type);
        }
    });
    $('body').on('click', '.add_to_love', function (event) {
        event.preventDefault();
        if(typeof(stlove_login)!='undefined' && stlove_login && typeof(prestashop)!='undefined' && !prestashop.customer.is_logged)
        {
            stLovedGoLogin();
            return false;
        }
        /*if($('.add_to_love.active').length)
          return false;*/

        stLovedAddProduct(this);

        
        return false;
    });
    stLovedUpdateInCache();
});
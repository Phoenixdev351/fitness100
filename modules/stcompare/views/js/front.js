function stCompareAddProduct(dom)
{
	var $dom = $(dom);
    var id_product = $dom.data('id-product');
    if(!id_product)
      return false;
    var remove = $dom.hasClass('st_added');//in gird the first click is alawys adding, being ingored if added, the sccond does jian shao 
    $dom.toggleClass('st_added').addClass('active');
    $.ajax({
        type: 'GET',
        url: stcompare.url,
        headers: { "cache-control": "no-cache" },
        async: true,
        cache: false,
        dataType: "json",
        data: {
            id_product:id_product,
            ajax: 1,
            action: remove ? 'deleteCompareProduct' : 'addCompareProduct'
        },
        success: function (resp)
        {
			   $('.stcompare_add.active').removeClass('active');
          //improve this
      		$('.stcompare_quantity').toggleClass('lingge', resp.total==0).html(resp.total);//.toggle(resp.total>0)
            
            if(resp.success){
                // $('stcompare_'+id_product).addClass('st_added');
                stComparePopup(resp.message, 2);
            }else{
                stComparePopup(resp.message, 0);
                $(dom).toggleClass('st_added');
            }
        },
        error: function(XMLHttpRequest, textStatus, errorThrown)
        {
			$('.stcompare_add.active').removeClass('active');
            $(dom).toggleClass('st_added');
        }
    });

}
function stCompareProductRemove(dom, action)
{
	var $dom = $(dom);
    var id_product = $dom.data('id-product');
    if(!id_product && !action)
    	return false;
  	$dom.addClass('active');
	$.ajax({
		type: 'GET',
		url: stcompare.url,
		headers: { "cache-control": "no-cache" },
		async: true,
		cache: false,
		dataType: "json",
		data: {
			id_product:id_product,
			ajax: 1,
			action: action ? 'deleteAllCompareProducts' : 'deleteCompareProduct'
		},
		success: function (resp)
		{
			$('.remove_compare_product.active').removeClass('active');
			if (resp.success==1) {
	            if(!resp.total)
	            	stCompareShowInformation();
	            $('.stcompare_td_'+id_product).remove();
      			$('.stcompare_quantity').toggleClass('lingge', resp.total==0).html(resp.total);//.toggle(resp.total>0)
			}
			else if(!resp.success)
			{
				stComparePopup(resp.message, 0);
			}
		},
	    error: function(XMLHttpRequest, textStatus, errorThrown)
	    {
			$('.remove_compare_product.active').removeClass('active');
	    }
	});
}
function stCompareShowInformation()
{
    $('.stcompare_table').addClass('d-none');
    $('.stcompare_no_products').removeClass('d-none');
}
function stComparePopup(msg, autoclose)
{
    $.magnificPopup.open({
      removalDelay: 500,
      showCloseBtn: autoclose ? false : true,
      callbacks: {
        beforeOpen: function() {
           this.st.mainClass = 'mfp-zoom-in'+(autoclose ? ' mfp-modal-noti ' : '');
        },
        open: function(){
        	var persist=this;
        	if(autoclose)
        		setTimeout(function(){
        			persist.close();
	            }, autoclose*1000);
        }
      },
      items: {
          src: '<div class="inline_popup_content small_popup mfp-with-anim text-center">'+msg+'</div>',
          type: 'inline'
      }
    });
}
function stCompareUpdateInCache(){
  var selector = '';
  if(typeof(stcompare.ids)!='undefined' && stcompare.ids.length){
    selector += '.stcompare_'+stcompare.ids.join(',.stcompare_');
  }
  if(selector)
    $(selector).addClass('st_added');
}
$(document).ready(function () {
	if(typeof(stcompare)!='undefined'){
	    $('body').on('click', '.remove_compare_product', function (event) {
	        event.preventDefault();
            stCompareProductRemove(this,0);
	        return false;
	    });
	    $('body').on('click', '.stcompare_remove_all', function (event) {
	        event.preventDefault();
            stCompareProductRemove(this,1);
	        return false;
	    });
	    $('body').on('click', '.stcompare_add', function (event) {
	        event.preventDefault();
	        stCompareAddProduct(this);
	        return false;
	    });
      stCompareUpdateInCache();
    }
});
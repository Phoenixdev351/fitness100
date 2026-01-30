{*
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
*}
<script type="text/javascript">
var sp_link_base ='{$sp_link_base|escape:'html':'UTF-8'}';
</script>
<style>

    </style>
{literal}
<script type="text/javascript">
function renderDataAjax(jsonData)
{
    for (var key in jsonData) {
	    if(key=='java_script')
        {
            $('body').append(jsonData[key]);
        }
        else
            if($('#ets_speed_dy_'+key).length)
            {
                if($('#ets_speed_dy_'+key+' #layer_cart').length)
                {
                    $('#ets_speed_dy_'+key).before($('#ets_speed_dy_'+key+' #layer_cart').clone());
                    $('#ets_speed_dy_'+key+' #layer_cart').remove();
                    $('#layer_cart').before('<div class="layer_cart_overlay"></div>');
                }
                $('.ets_speed_dynamic_hook[id="ets_speed_dy_'+key+'"]').replaceWith(jsonData[key]);
            }
              
    }
    if($('#header .shopping_cart').length && $('#header .cart_block').length)
    {
        var shopping_cart = new HoverWatcher('#header .shopping_cart');
        var cart_block = new HoverWatcher('#header .cart_block');
        $("#header .shopping_cart a:first").hover(
    		function(){
    			if (ajaxCart.nb_total_products > 0 || parseInt($('.ajax_cart_quantity').html()) > 0)
    				$("#header .cart_block").stop(true, true).slideDown(450);
    		},
    		function(){
    			setTimeout(function(){
    				if (!shopping_cart.isHoveringOver() && !cart_block.isHoveringOver())
    					$("#header .cart_block").stop(true, true).slideUp(450);
    			}, 200);
    		}
    	);
    }
    if(typeof jsonData.custom_js!== undefined && jsonData.custom_js)
        $('head').append('<script src="'+sp_link_base+'/modules/ets_superspeed/views/js/script_custom.js">');
}
</script>
{/literal}
<style>
.layered_filter_ul .radio,.layered_filter_ul .checkbox {
    display: inline-block;
}
.ets_speed_dynamic_hook .cart-products-count{
    display:none!important;
}
.ets_speed_dynamic_hook .ajax_cart_quantity ,.ets_speed_dynamic_hook .ajax_cart_product_txt,.ets_speed_dynamic_hook .ajax_cart_product_txt_s{
    display:none!important;
}
.ets_speed_dynamic_hook .shopping_cart > a:first-child:after {
    display:none!important;
}
</style>
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
var SpeedLoadCache = true;
$(document).ready(function(){
    if($('#quantity_wanted').length && $('.product-add-to-cart').length)
    {
        setTimeout(function(){
            $.ajax({
                type: 'POST',
                url: '',
                async: true,
                cache: false,
                dataType : "json",
                data: 'ajax=1&quickview=0&action=refresh&quantity_wanted='+$('#quantity_wanted').val(),
                success: function(jsonData,textStatus,jqXHR)
                {
                    if(jsonData)
                    {
                        var addToCartBt = ".page-product:not(.modal-open) .row .product-add-to-cart, .page-product:not(.modal-open) .product-container .product-add-to-cart, .page-product:not(.modal-open) .row .js-product-add-to-cart, .page-product:not(.modal-open) .product-container .js-product-add-to-cart";
                        if(jsonData.product_add_to_cart && $(addToCartBt).length)
                        {
                            var t = $(addToCartBt).find('.add');
                            if (!(t.length <= 0)) {
                                var n = $(jsonData.product_add_to_cart).find('.add');
                                if(n.length > 0)
                                    t.replaceWith(n[0].outerHTML);
                                else
                                    t.html("");
                            }
                            t = $(addToCartBt).find('#product-availability');
                            if (!(t.length <= 0)) {
                                var p = $(jsonData.product_add_to_cart).find('#product-availability');
                                if(p.length > 0)
                                    t.replaceWith(p[0].outerHTML);
                                else
                                    t.html("");
                            }
                            t = $(addToCartBt).find('.product-minimal-quantity');
                            if (!(t.length <= 0)) {
                                var m = $(jsonData.product_add_to_cart).find('.product-minimal-quantity');
                                if(m.length > 0)
                                    t.replaceWith(m[0].outerHTML);
                                else
                                    t.html("");
                            }
                            if($('.block-product-attribute-custom .from-group-option.required').length)
                                ets_eto_checkValidate();
                        }
                    }
                },
                error: function(XMLHttpRequest, textStatus, errorThrown)
                {

                }
            });
        },200);
    }
    if($('.ets_speed_dynamic_hook').length || ssIsCeInstalled || (typeof always_load_content!='undefined' && always_load_content))
    {
        ets_superspeed_load_dynamic();
    }
    $(document).on('loadPriceDynamic',function(){
        ets_superspeed_load_dynamic();
    });
});
function ets_superspeed_load_dynamic()
{
    var datas='';

    $('.ets_speed_dynamic_hook').each(function(index, domhook){
        datas = datas + '&hook_' + index + '=' + $(this).attr('data-hook')+'&module_'+index+'='+$(this).attr('data-moudule')+'&params_'+index+'='+JSON.stringify($(this).data('params'));
    });
    var url      = window.location.href;
    var indexphp = url.indexOf('?');
    var indexthang = url.indexOf('#');
    if(indexthang>=0)
        url = url.substr(0,indexthang);
    if(indexphp > 0)
        url +='&ss_nocache=1';
    else
        url +='?ss_nocache=1';
    if(typeof always_load_content!='undefined' && always_load_content)
    {
        var list_products =[];
        if($('.js-product-miniature').length)
        {
            $('.js-product-miniature').each(function(){
                var dataProduct =$(this).data('id-product')+'-'+$(this).data('id-product-attribute');
                if(!list_products.includes(dataProduct))
                    list_products.push(dataProduct);
            });
        }
        datas +='&dataProducts='+list_products.toString();
    }
    $.ajax({
        type: 'POST',
        headers: { "cache-control": "no-cache" },
        url: url,
        async: true,
        cache: false,
        dataType : "json",
        data: 'ajax=1&ets_superseed_load_content=1&ajax=1&count_datas='+$('.ets_speed_dynamic_hook').length+datas,
        success: function(jsonData,textStatus,jqXHR)
        {
            SpeedLoadCache = false;
            if(jsonData)
            {
                renderDataAjax(jsonData);
                if(jsonData.product_price)
                {

                    if($('.current-price > span.current-price-value').length)
                    {
                        $('.current-price > span.current-price-value').html(jsonData.product_price);
                    }
                    else if($('.product-prices > div.product-price.h5').length)
                    {
                        $('.product-prices > div.product-price.h5').html(jsonData.product_price);
                    }
                }
                if(jsonData.price_without_reduction)
                {

                    if($('.product-discount .regular-price').length)
                        $('.product-discount .regular-price').html(jsonData.price_without_reduction)
                }
                if(jsonData.dataProducts)
                {
                    jsonData.dataProducts.map((product)=>{
                        if(product.id_product_attribute)
                        {
                            var blockProduct = $('.js-product-miniature[data-id-product="'+product.id_product+'"][data-id-product-attribute="'+product.id_product_attribute+'"]');
                        }
                        else
                            var blockProduct = $('.js-product-miniature[data-id-product="'+product.id_product+'"]');
                        if(blockProduct.find('.product-price-and-shipping .regular-price').length)
                            blockProduct.find('.product-price-and-shipping .regular-price').html(product.price_without_reduction);
                        if(blockProduct.find('.product-price-and-shipping .price').length)
                            blockProduct.find('.product-price-and-shipping').html(product.price);
                        else {
                            blockProduct.find('.product-price-and-shipping').html(product.price);
                            if( typeof blockProduct.find('.product-price-and-shipping').attr('data-old-price')!= 'undefined')
                            {
                                blockProduct.find('.product-price-and-shipping').attr('data-old-price',product.price_without_reduction);
                            }
                        }
                    });
                }
                $('.js-product-miniature .product-price-and-shipping,.js-product-miniature .regular-price-percent,#product .product-prices').addClass('ss-show');
                if(jsonData.cart_products_count)
                {
                    $('.cart-products-count').text(jsonData.cart_products_count);
                    prestashop.emit('updatedAjaxCart', {});
                }
                if(jsonData.creativeelements_header && $('.elementor-'+jsonData.creativeelements_header.uid).length)
                {
                    var cart_content = $(jsonData.creativeelements_header.content).find('.elementor-widget-shopping-cart');
                    var customer_content = $(jsonData.creativeelements_header.content).find('.elementor-sign-in .menu-item-type-account .elementor-item');
                    if(cart_content.length){
                        $('.elementor-widget-shopping-cart').html(cart_content.html());
                        if(typeof ceFrontend != 'undefined'){
                            ets_superspeed_creativeelements();
                        }
                    }
                    if(customer_content.length){
                        $('.elementor-sign-in .menu-item-type-account .elementor-item').html(customer_content.html());
                    }
                }
                $('.elementor-widget-shopping-cart').show();
                $('.elementor-sign-in').show();
                if($(window).width()<768)
                {
                    $("*[id^='_desktop_']").each(function(t, e){
                        var n = $("#" + e.id.replace("_desktop_", "_mobile_"));
                        if($(this).html().trim()!='' && n.length)
                            n.html($(this).html());
                    });
                }
                $(document).trigger("hooksLoaded");
            }

        },
        error: function(XMLHttpRequest, textStatus, errorThrown)
        {

        }
    });
}
function ets_superspeed_creativeelements(){
    var $container = $('.elementor-cart__container.elementor-lightbox');
    $('.elementor-button.elementor-size-sm').unbind('click');
    $('.elementor-cart__wrapper .elementor-button.elementor-size-sm').on('click', function (event) {
        if (!$(this).hasClass('elementor-cart-hidden')) {
            event.preventDefault();
            $container.toggleClass('elementor-cart--shown');
        }
    });
    // Deactivate topbar mode on click or on esc.
    $container.on('click', function (event) {
        if ($container.hasClass('elementor-cart--shown') && $container[0] === event.target) {
            $container.removeClass('elementor-cart--shown');
        }
    });
    $('.elementor-cart__close-button').on('click', function () {
        $container.removeClass('elementor-cart--shown');
    });
    $container.on('click', '.elementor-cart__product-remove a', function (event) {
        var dataset = $(this).data();
        dataset.linkAction = 'delete-from-cart';

        $(this).closest('.elementor-cart__product').addClass('ce-disabled');

        event.preventDefault();

        $.ajax({
            url: this.href,
            method: 'POST',
            dataType: 'json',
            data: {
                ajax: 1,
                action: 'update',
            },
        }).then(function (resp) {
            prestashop.emit('updateCart', {
                reason: dataset,
                resp: resp,
            });
        }).fail(function (resp) {
            prestashop.emit('handleError', {
                eventType: 'updateProductInCart',
                resp: resp,
                cartAction: dataset.linkAction,
            });
        });
    });
    prestashop.on('updateCart', function(data) {
        if (!data || !data.resp || !data.resp.cart) {
            return;
        }
        var cart = data.resp.cart,
            gift = $container.find('.elementor-cart__products').data('gift'),
            $products = $();
        // Update toggle
        $('.elementor-cart__wrapper .elementor-button.elementor-size-sm').find('.elementor-button-text')
            .html(cart['subtotals']['products']['value'])
        ;
        $('.elementor-cart__wrapper .elementor-button.elementor-size-sm').find('.elementor-button-icon')
            .attr('data-counter', cart['products_count'])
            .data('counter', cart['products_count'])
        ;
        // Update products
        cart.products.forEach(function (product) {
            var $prod = $(
                '<div class="elementor-cart__product">' +
                '<div class="elementor-cart__product-image"></div>' +
                '<div class="elementor-cart__product-name">' +
                '<div class="elementor-cart__product-attrs"></div>' +
                '</div>' +
                '<div class="elementor-cart__product-price"></div>' +
                '<div class="elementor-cart__product-remove ceicon-times"></div>' +
                '</div>'
                ),
                $attrs = $prod.find('.elementor-cart__product-attrs'),
                cover = product.cover || prestashop.urls.no_picture_image;

            if (product.embedded_attributes && product.embedded_attributes.id_image) {
                // PS 1.7.8 fix - product.cover contains wrong image
                var i, id_cover = product.embedded_attributes.id_image.split('-').pop();
                for (i = 0; i < product.images.length; i++) {
                    if (id_cover == product.images[i].id_image) {
                        cover = product.images[i];
                        break;
                    }
                }
            }

            $('<img>').appendTo($prod.find('.elementor-cart__product-image')).attr({
                src: cover.bySize.cart_default && cover.bySize.cart_default.url || cover.small.url,
                alt: cover.legend,
            });
            $('<a>').prependTo($prod.find('.elementor-cart__product-name'))
                .attr('href', product['url'])
                .html(product['name'])
            ;
            // Add product attributes
            for (var label in product['attributes']) {
                $('<div class="elementor-cart__product-attr">').html(
                    '<span class="elementor-cart__product-attr-label">' + label + ':</span> ' +
                    '<span class="elementor-cart__product-attr-value">' + product['attributes'][label] + '</span>'
                ).appendTo($attrs);
            }
            // Add product customizations
            product.customizations && product.customizations.forEach(function (customization) {
                customization.fields.forEach(function (field) {
                    $('<div class="elementor-cart__product-attr">').html(
                        '<span class="elementor-cart__product-attr-label">' + field['label'] + ':</span> ' +
                        '<span class="elementor-cart__product-attr-value">' +
                        ('image' === field['type'] ? $('<img>').attr('src', field['image']['small']['url'])[0].outerHTML : field['text']) +
                        '</span>'
                    ).appendTo($attrs);
                });
            });
            $prod.find('.elementor-cart__product-price').html(
                '<span class="elementor-cart__product-quantity">' + product['quantity'] + '</span> &times; ' + (product['is_gift'] ? gift : product['price']) + ' '
            ).append(product['has_discount'] ? $('<del>').html(product['regular_price']) : []);

            $('<a>').appendTo($prod.find('.elementor-cart__product-remove')).attr({
                href: product['remove_from_cart_url'],
                rel: 'nofollow',
                'data-id-product': product['id_product'],
                'data-id-product-attribute': product['id_product_attribute'],
                'data-id-customization': product['id_customization'],
            }).data({
                'idProduct': product['id_product'],
                'idProductAttribute': product['id_product_attribute'],
                'idCustomization': product['id_customization'],
            });
            $products.push($prod[0]);
        });
        // Update cart
        $container.find('.elementor-cart__products')
            .empty()
            .append($products)
        ;
        $container.find('.elementor-cart__empty-message')
            .toggleClass('elementor-hidden', !!cart['products_count'])
        ;
        $container.find('.elementor-cart__summary').html(
            '<div class="elementor-cart__summary-label">' + cart['summary_string'] + '</div>' +
            '<div class="elementor-cart__summary-value">' + cart['subtotals']['products']['value'] + '</div>' +
            '<span class="elementor-cart__summary-label">' + cart['subtotals']['shipping']['label'] + '</span>' +
            '<span class="elementor-cart__summary-value">' + cart['subtotals']['shipping']['value'] + '</span>' +
            '<strong class="elementor-cart__summary-label">' + cart['totals']['total']['label'] + '</strong>' +
            '<strong class="elementor-cart__summary-value">' + cart['totals']['total']['value'] + '</strong>'
        );
        $container.find('.elementor-alert-warning')
            .toggleClass('elementor-hidden', !cart['minimalPurchaseRequired'])
            .html('<span class="elementor-alert-description">' + cart['minimalPurchaseRequired'] + '</span>');
        ;
        $container.find('.elementor-button--checkout')
            .toggleClass('ce-disabled', cart['minimalPurchaseRequired'] || !cart['products_count'])
        ;
        //
        // // Open shopping cart after updated
        // if (self.getElementSettings('action_open_cart')) {
        //     self.elements.$container.hasClass(classes.isShown) || self.elements.$toggle.triggerHandler('click');
        // }
    });
}
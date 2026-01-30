/**
*
* NOTICE OF LICENSE
*
* This product is licensed for one customer to use on one installation (test stores and multishop included).
* Site developer has the right to modify this module to suit their needs, but can not redistribute the module in
* whole or in part. Any other use of this module constitutes a violation of the user agreement.
*
* DISCLAIMER
*
* NO WARRANTIES OF DATA SAFETY OR MODULE SECURITY
* ARE EXPRESSED OR IMPLIED. USE THIS MODULE IN ACCORDANCE
* WITH YOUR MERCHANT AGREEMENT, KNOWING THAT VIOLATIONS OF
* PCI COMPLIANCY OR A DATA BREACH CAN COST THOUSANDS OF DOLLARS
* IN FINES AND DAMAGE A STORES REPUTATION. USE AT YOUR OWN RISK.
*
*  @author    idnovate.com <info@idnovate.com>
*  @copyright 2020 idnovate.com
*  @license   See above
*/

function getQueryString() {
  var key = false, res = {}, itm = null;
  // get the query string without the ?
  var qs = location.search.substring(1);
  // check for the key as an argument
  if (arguments.length > 0 && arguments[0].length > 1)
    key = arguments[0];
  // make a regex pattern to grab key/value
  var pattern = /([^&=]+)=([^&]*)/g;
  // loop the items in the query string, either
  // find a match to the argument, or build an object
  // with key/value pairs
  while (itm = pattern.exec(qs)) {
    if (key !== false && decodeURIComponent(itm[1]) === key)
      return decodeURIComponent(itm[2]);
    else if (key === false)
      res[decodeURIComponent(itm[1])] = decodeURIComponent(itm[2]);
  }

  return key === false ? res : null;
}

/***** EVENTS ******/
/*
 * 1 = On load
 * 2 = When product added to the cart
 * 3 = Exit popup
 * 4 = On element click
*/

$(document).ready(function() {
    // Preview popup
    if (getQueryString('previewPopup')) {
        displayPopup(getQueryString('popupId'));
        return;
    }

    updateVisits();

    // 1 = On load
    getPopup(1);

    // 4 = On element click
    getPopup(4);


    // 2 = When product added to the cart
    if (typeof ajaxCart !== "undefined") {
        ajaxCart.add = (function() {
            var ajaxCartaddCached = ajaxCart.add;
            return function(idProduct) {
                ajaxCartaddCached.apply(this, arguments);
                setTimeout(function() {
                    productAddedToTheCart(idProduct);
                }, 100)
            }
        })();
    } else if (typeof prestashop !== "undefined") {
        prestashop.on(
            'updateCart',
            function (event) {
                if (event.reason.linkAction == "add-to-cart") {
                    productAddedToTheCart(event.reason.idProduct);
                }
            }
        );

        prestashop.on(
            'stUpdateCart',
            function (event) {
                if (event.reason.linkAction == "add-to-cart") {
                    productAddedToTheCart(event.reason.idProduct);
                }
            }
        );

        //phbuscadorllantas
        $('body').on('click', '.add1', function() {
            idProduct = $(this).parents('.product-actions').find('input[name="id_product"]').val();
            productAddedToTheCart(idProduct);
        });

    } else if ($('#layer_cart').length || $('.layer_cart_overlay').length || /*$('.blockcart').length ||*/ $('.mfp-container').length) {
        $(document).on('click', '#layer_cart .cross, #layer_cart .continue, .layer_cart_overlay, #blockcart-modal, .mfp-container', function() {
            productAddedToTheCart();
        });

        /*$('.ajax_add_to_cart_button span, .add-to-cart').on('click', function(){
            getPopup(2);
        });
        prestashop.on(
            'stUpdateCart',
            function() { getPopup(2); }
        );*/
    } else {
        $(document).on('click', '.add-to-cart, .product-add-to-cart', function(){
            productAddedToTheCart();
        });
    }

    // 3 = Exit popup
    _html = document.documentElement;
    _html.addEventListener('mouseout', handleMouseleave);
});

function productAddedToTheCart(idProduct) {
    getPopup(2, idProduct);
}

function handleMouseleave(e) {
    e = e ? e : window.event;

    // If this is an autocomplete element.
    if (e.target.tagName.toLowerCase() === "input"
        || e.target.tagName.toLowerCase() === "select") {
        return;
    }
    // Get the current viewport width.
    var vpWidth = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);

    // If the current mouse X position is within 50px of the right edge
    // of the viewport, return.
    if (e.clientX >= (vpWidth - 50)) {
        return;
    }

    // If the current mouse Y position is not within 50px of the top
    // edge of the viewport, return.
    if (e.clientY >= 50) {
        return;
    }

    // Reliable, works on mouse exiting window and
    // user switching active program
    var from = e.relatedTarget || e.toElement;
    if (!from) {
        if ($('#apc_content_exit').length && $('#apc_content_exit').html().length) {
            displayPopup(3);
        } else {
            getPopup(3);
        }
    }

    //_html.removeEventListener('mouseleave', handleMouseleave);
}

function updateVisits() {
    $.ajax({
        type: 'POST',
        headers: { "cache-control": "no-cache" },
        url: apc_link,
        async: true,
        cache: false,
        dataType : "json",
        data: {
            token: apc_token,
            responsiveWidth: window.innerWidth,
            url: encodeURIComponent(window.location.href),
            referrer: encodeURIComponent(document.referrer),
            updateVisits: 1,
            time: (new Date()).getTime(),
            fromController: apc_controller,
            id_product: apc_product,
            id_category: apc_category,
            id_manufacturer: apc_manufacturer,
            id_supplier: apc_supplier,
            id_cms: apc_cms,
        },
        error: function(errorThrown)
        {
            console.log(errorThrown);
        }
    });
}

function getPopup(event, idProduct) {
    var availablePopups = [];
    $('.apc_modal').each (function () { availablePopups.push($(this).data('popupId')); });
    availablePopups = availablePopups.join(',');

    if (!availablePopups) { return; }

    if (!idProduct) {
        idProduct = apc_product;
    }

    $.ajax({
        type: 'POST',
        headers: { "cache-control": "no-cache" },
        url: apc_link,
        async: true,
        cache: false,
        dataType : "json",
        data: {
            token: apc_token,
            responsiveWidth: window.innerWidth,
            url: encodeURIComponent(window.location.href),
            referrer: encodeURIComponent(document.referrer),
            getPopup: 1,
            event: event,
            time: (new Date()).getTime(),
            fromController: apc_controller,
            id_product: idProduct,
            id_category: apc_category,
            id_manufacturer: apc_manufacturer,
            id_supplier: apc_supplier,
            availablePopups: availablePopups
        },
        success: function(jsonData)
        {
            if (!jsonData.hasError && typeof jsonData.popups != "undefined") {
                $.each(jsonData.popups, function(i, item) {
                    var popup = JSON.parse(item);
                    if (event == 4 && popup.selector) {
                        var popupSelector = eval(popup.selector);
                        if (popupSelector.length) {
                        	//iOS bug
                        	popupSelector.css('cursor', 'pointer');
                            popupSelector.on('click touch', function () {
                                displayPopup(popup.id);
                            });
                        }
                    } else {
                        displayPopup(popup.id);
                    }
                });
            }
        },
        error: function(errorThrown)
        {
            console.log(errorThrown);
        }
    });
}

function displayPopup(popupId) {
    // Check if modal function is enabled
    // (May fail if overrided by another JQUERY plugin!!)
    if (!$.prototype.fancybox) {
        console.log('Fancybox library does not exist!');
        return;
    }

    var $apcFancybox = '';

    if (typeof $.fancyboxPopup != "undefined") {
        $apcFancybox = $.fancyboxPopup;
    } else {
        $apcFancybox = $.fancybox;
    }

    if ($apcFancybox.current) {
        return;
    }

    var content = $('#apc_modal_'+popupId);

    var popupProperties = content.data();

    if (typeof popupProperties === "undefined") {
        return;
    }

    var preview = false;
    if (getQueryString('previewPopup') !== null) {
        preview = true;
    }

    var maxWidth = '90%';
    var maxHeight = '90%';
    if (typeof(popupProperties.height) == "undefined" || typeof(popupProperties.width) == "undefined") {
        maxWidth = 'auto';
        maxHeight = 'auto';
    } else if (popupProperties.height === '100%' && popupProperties.width === '100%') {
        maxWidth = '100%';
        maxHeight = '100%';
    }

    var autoSize = false;
    if (!popupProperties.height && !popupProperties.width) {
        autoSize = true;
    }

    if (!popupProperties.height) {
        popupProperties.height = 'auto';
    }

    if (!popupProperties.width) {
        popupProperties.width = 'auto';
    }

    if (!popupProperties.openEffect) {
        popupProperties.openEffect = 'zoom';
    }

    var secsDisplay = 0;
    if (popupProperties.secsDisplay) {
        secsDisplay = popupProperties.secsDisplay;
    } else if (popupProperties.secsDisplayCart) {
        secsDisplay = popupProperties.secsDisplayCart;
    }

    setTimeout(function() {
        $apcFancybox.open({
            href        : '#apc_modal_'+popupId,
            src         : '#apc_modal_'+popupId,
            autoSize    : autoSize,
            width       : popupProperties.width,
            height      : popupProperties.height,
            padding     : popupProperties.padding,
            autoCenter  : true,
            aspectRatio : false,
            wrapCSS     : 'apc-popup apc-popup-'+popupProperties.popupId+' '+popupProperties.css,
            margin      : 0,
            maxWidth    : maxWidth,
            maxHeight   : maxHeight,
            openMethod  : popupProperties.openEffect+'In',
            closeMethod : popupProperties.openEffect+'Out',
            beforeShow  : function () {
                if (!preview) {
                    $.ajax({
                        type: 'POST',
                        headers: {"cache-control": "no-cache"},
                        url: apc_link,
                        async: true,
                        cache: false,
                        dataType: "json",
                        data: 'markAsSeen=1&popupId=' + popupProperties.popupId + '&time=' + (new Date()).getTime() + '&token=' + apc_token
                    });
                }

                if (popupProperties.blurBackground) {
                    $("main, header, body > section, footer, #page").addClass("apc-effect-blur");
                }

                // Bug with Uniform
                if (typeof $.fn.uniform === 'function') {
                    $("select.form-control,input[type='checkbox']:not(.comparator), input[type='radio'],input#id_carrier2, input[type='file']").not(".not_unifrom, .not_uniform").uniform();
                }
            },
            afterShow  : function () {
                if (popupProperties.opacity == 0 && !popupProperties.locked) {
                    $('.fancybox-overlay').attr('style', function(i,s) { if (s == undefined) { s = ''; } return s + 'display: none !important;' });
                }

                // Bug with vertical scroll in images
                /*if ($('.apc-popup-'+popupProperties.popupId+' .modal-img').scrollTop() == 0) {
                    $('.apc-popup-'+popupProperties.popupId+' .fancybox-inner').attr('style', 'overflow: hidden !important;');
                }*/

                $apcFancybox.toggle();

                if ($('.apc-popup-'+popupProperties.popupId+' .modal-img').length > 0) {
                    var img = $('.apc-popup-'+popupProperties.popupId+' .modal-img');
                    if (img.get(0).height <= $('.apc-popup-'+popupProperties.popupId+' .fancybox-inner').height()) {
                        $('.apc-popup-'+popupProperties.popupId+' .fancybox-inner').attr('style', 'overflow: hidden !important;');
                    }
                }
            },
            afterClose  : function () {
                if (popupProperties.blurBackground) {
                    $("main, header, body > section, footer, #page").removeClass("apc-effect-blur");
                }

            },
            helpers: {
                overlay : {
                    css: {
                        'background' : 'rgba(0, 0, 0, '+popupProperties.opacity+')',
                    },
                    locked: popupProperties.locked,
                    closeClick: popupProperties.closeBackground
                }
            }
        });
    }, secsDisplay);

    if (typeof(popupProperties.secsClose) != "undefined" && popupProperties.secsClose > 0) {
        setTimeout( function() {$apcFancybox.close(); }, popupProperties.secsClose); // 3000 = 3 secs
    }
}

function dontDisplayAgain(popupId) {
    if (typeof $.fancyboxPopup != "undefined") {
        var $apcFancybox = $.fancyboxPopup;
    } else {
        $apcFancybox = $.fancybox;
    }

    $.ajax({
        type: 'POST',
        headers: {"cache-control": "no-cache"},
        url: apc_link,
        async: true,
        cache: false,
        dataType: "json",
        data: 'dontDisplayAgain=1&popupId=' + popupId + '&time='+(new Date()).getTime()+'&token='+apc_token
    });

    $apcFancybox.close();
}

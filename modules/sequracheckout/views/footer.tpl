<!-- Widgets SeQura -->
<script>
Sequra.onLoad(function() {
    {foreach $widgets as $widget}
        SequraHelper.drawPromotionWidget('{$css_selector_price  nofilter}','{$widget['css_sel'] nofilter}','{$widget['product']}','{$widget['theme'] nofilter}',0,'{$widget['campaign']}');
        if(typeof prestashop != 'undefined' && !!prestashop.on) {
            prestashop.on('updatedProduct',function() {
                if(document.querySelector('[data-product={$widget['product']}]')){
                    SequraHelper.refreshWidgets('{$css_selector_price  nofilter}');
                } else {
                    SequraHelper.waitForElememt('{$widget['css_sel'] nofilter}').then(function() {
                        SequraHelper.drawnWidgets['{$css_selector_price  nofilter}{$widget['css_sel'] nofilter}{$widget['product']}{$widget['theme'] nofilter}0{$widget['campaign']}']=false;
                        SequraHelper.drawPromotionWidget('{$css_selector_price  nofilter}','{$widget['css_sel'] nofilter}','{$widget['product']}','{$widget['theme'] nofilter}',0,'{$widget['campaign']}');
                    }, 1000);
                }
            });
        }
    {/foreach}
});
{if $sq_categories_show}
function sq_categories_show() {
    SequraHelper.waitForElememt('{$sq_categories_css_sel nofilter}').then(function() {
        SequraMiniWidget.add_widgets_to_list(
            '{$sq_categories_css_sel_price nofilter}',
            '{$sq_categories_css_sel nofilter}',
            '{$sq_categories_msg nofilter}',
            '{$sq_msg_below}'
        );
        Sequra.refreshComponents();
    });
}
Sequra.onLoad(function() {
    sq_categories_show();
    if(typeof prestashop != 'undefined' && !!prestashop.on) {
        prestashop.on(
            'updateFacets',
            function() {
                setTimeout(sq_categories_show, 500);
            }
        );
    }
});
{/if}
{if $sq_cart_show}
function sq_cart_show() {
    SequraHelper.waitForElememt('{$sq_cart_css_sel nofilter}').then(function() {
        SequraMiniWidget.add_widget_to_item(
            document.querySelector('{$sq_cart_css_sel_price nofilter}'),
            document.querySelector('{$sq_cart_css_sel nofilter}'),
            '{$sq_cart_msg}',
            '{$sq_msg_below}'
        );
        Sequra.refreshComponents();
    });
}
Sequra.onLoad(()=> {
    sq_cart_show();
    if(typeof prestashop != 'undefined' && !!prestashop.on) {
        prestashop.on(
            'updateCart',
            sq_cart_show
        );
    }
});
{/if}
{if $sq_minicart_show}
function sq_minicart_show() {
    SequraHelper.waitForElememt('{$sq_minicart_css_sel nofilter}').then(function() {
        SequraMiniWidget.add_widget_to_item(
            document.querySelector('{$sq_minicart_css_sel_price nofilter}'),
            document.querySelector('{$sq_minicart_css_sel nofilter}'),
            '{$sq_mincart_msg}',
            '{$sq_msg_below}'
        );
        Sequra.refreshComponents();
    });
}
Sequra.onLoad(sq_minicart_show);
{/if}



{if $sq_categories_show or $sq_cart_show or $sq_minicart_show}
var SequraMiniWidget = {
    product: "{$sq_pp_product}",
    drawnWidgets: [],
    add_widgets_to_list: function (css_sel_price,css_sel, teaser_msg){
        var srcNodes = document.querySelectorAll(css_sel_price);
        var destNodes = document.querySelectorAll(css_sel);
        destNodes.forEach(function (item, index) {
            SequraMiniWidget.add_widget_to_item(srcNodes[index], item, teaser_msg, false);
        });
/* @Todo: add observer to list container.common ancestor for all srcNodes and destNodes
        var MutationObserver = window.MutationObserver || window.WebKitMutationObserver;
        if(MutationObserver){
            if(!list_node.getAttribute('observed-by-sequra-promotion-widget-list')){
                var mo = new MutationObserver(
                    function (mutationsList, observer) {
                        SequraMiniWidget.add_widgets_to_list(srcNodes[index], item, teaser_msg);
                    });
                mo.observe(item, {
                        childList: true,
                        subtree: true
                    });
                mo.observed_as = list_container_css;
                item.setAttribute('observed-by-sequra-promotion-widget-list',1);
            }
        }
*/
    },

    build_teaser_element: function (the_amount) {
        var teaser = document.createElement('small');
        var att = document.createAttribute("class");
        att.value = "sequra-educational-popup";
        teaser.setAttributeNode(att);
        att = document.createAttribute("data-amount");
        att.value = the_amount;
        teaser.setAttributeNode(att);
        att = document.createAttribute("data-product");
        att.value = SequraMiniWidget.product;
        teaser.setAttributeNode(att);
        return teaser;
    },

    update_miniwidget: function(item, miniwidget, teaser_msg, teaser_below){
        var the_amount = "" + SequraHelper.textToCents(item.innerText);

        var creditAgreement = Sequra.computeCreditAgreements({
            amount: the_amount,
            product: SequraMiniWidget.product
        })[SequraMiniWidget.product]
        .filter(function (item){
            return item.default
        })[0];
        miniwidget.innerText = (the_amount>=creditAgreement.min_amount.value)?
            teaser_msg.replace('%s', creditAgreement["instalment_total"]["string"]):
            teaser_below.replace('%s', creditAgreement.min_amount.string);

    },

    add_widget_to_item: function (item, dest, teaser_msg, teaser_below){
        if(!item || item.getAttribute('sequra-widget-teaser-added')){
            //Add only once
            return;
        }

        var the_amount = "" + SequraHelper.textToCents(item.innerText);

        var creditAgreements = Sequra.computeCreditAgreements({
            amount: the_amount,
            product: SequraMiniWidget.product
        })[SequraMiniWidget.product];
        var creditAgreement = creditAgreements.pop();
        while(the_amount<creditAgreement.min_amount.value && creditAgreements.length>1){
            creditAgreement = creditAgreements.pop();
        }
        if(the_amount<creditAgreement.min_amount.value && !teaser_below) {
            return;
        }

        var miniwidget_idx = SequraMiniWidget.drawnWidgets.length;
        SequraMiniWidget.drawnWidgets.push(SequraMiniWidget.build_teaser_element(the_amount));
        SequraMiniWidget.drawnWidgets[miniwidget_idx].innerText = (the_amount>=creditAgreement.min_amount.value)?
            teaser_msg.replace('%s', creditAgreement["instalment_total"]["string"]):
            teaser_below.replace('%s', creditAgreement.min_amount.string);
        dest.appendChild(SequraMiniWidget.drawnWidgets[miniwidget_idx]);

        var MutationObserver = window.MutationObserver || window.WebKitMutationObserver;
        if(MutationObserver){
            if(!item.getAttribute('observed-by-sequra-promotion-miniwidget')){
                var mo = new MutationObserver(
                    function (mutationsList, observer) {
                        SequraMiniWidget.update_miniwidget(
                            item,
                            SequraMiniWidget.drawnWidgets[miniwidget_idx],
                            teaser_msg,
                            teaser_below
                        );
                    }
                );
                mo.observe(item, {
                    childList: true,
                    subtree: true
                });
                mo.observed_as = 'miniwidget_'+miniwidget_idx;
                item.setAttribute('observed-by-sequra-promotion-miniwidget',miniwidget_idx);
            }
        }
        item.setAttribute('sequra-widget-teaser-added',1);
    }
}
{/if}
</script>
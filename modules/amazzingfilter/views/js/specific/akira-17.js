/**
*  @author    Amazzing
*  @copyright Amazzing
*  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

$.extend(customThemeActions, {
    documentReady: function() {
        // page is reloaded after switching grid/list
        af.productItemSelector = this.isListView() ? '.item-product-list' : '.item';
        this.addRequiredClasses();
        this.bindAdditionalEvents();
    },
    isListView: function() {
        let activeOptionHref = $('.gr-list-gird').find('.active-view').attr('href');
        return activeOptionHref && activeOptionHref.endsWith('shop_view=list');
    },
    updateContentAfter: function(r) {
        prestashop.emit('mustUpdateLazyLoad', null);
        this.addRequiredClasses();
    },
    addRequiredClasses: function() {
        $('.wc-ordering-dropdown').addClass('products-sort-order')
        .find('.hidden-md-down').addClass('select-title').end()
        .find('.js-search-link').addClass('select-list');
        $('.gr-per-page').find('.js-search-link').addClass('nb-items-opion');
    },
    bindAdditionalEvents: function() {
        $(document).on('click', '.nb-items-opion', function(e) {
            e.preventDefault();
            let nbItems = parseInt($(this).attr('href').split('resultsPerPage=')[1]);
            if (!isNaN(nbItems)) {
                af.nbItems.current = nbItems;
                $('#af_nb_items').val(af.nbItems.current).change();
            }
        });
        if (prestashop.page.page_name == 'index' && !af.$dynamicContainer.length) {
            this.bindCustomEventsForMainPage();
        }
    },
    bindCustomEventsForMainPage: function() {
        af.$elementorElement = af.$filterBlock.closest('.elementor-element');
        if (af.$elementorElement.length) {
            $.extend(af, {
                orig: {
                    loadProducts: af.loadProducts,
                    updateContent: af.updateContent,
                },
                loadProducts: function(trigger, updateList) {
                    if (updateList && !af.$dynamicContainer.length) {
                        af.$elementorElement.closest('.elementor-section').nextAll('.elementor-section')
                        .addClass('af-to-remove').animate({'opacity': 0.3}, 350);
                    }
                    return af.orig.loadProducts(trigger, updateList);
                },
                updateContent: function(jsonData, trigger, updateList) {
                    if ($('.af-to-remove').length) {
                        $('.af-to-remove').remove();
                        af.$dynamicContainer = $('<div class="af-dynamic-container elementor-element"></div>')
                        .insertAfter(af.$elementorElement);
                    }
                    af.orig.updateContent(jsonData, trigger, updateList);
                    af.$dynamicContainer.find('.nav-products-list-top-left').remove();
                },
            });
        }
    },
});
/* since 3.2.0 */

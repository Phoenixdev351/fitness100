/**
*  @author    Amazzing
*  @copyright Amazzing
*  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

var af_product_list_selector = '.'+af_product_list_class,
	pagination_holder_id = af_ids['pagination'],
	pagination_bottom_holder_id = af_ids['pagination_bottom'];
$.extend(af.events.theme, {
	sorting: function() {
		$(document).off('change', '.selectProductSort').on('change', '.selectProductSort', function() {
			var splitted = $(this).val().split(':');
			af.applySorting(splitted[0], splitted[1]);
		});
	},
	extra_16: function() { // will be called automatically in af.bindEvents(), after af.defineVariables()
		af.$dynamicContainer = $('#center_column');
		af.productItemSelector = '.'+af_classes['ajax_block_product'];
		af.$filterBlock.on('updateProductList', function(e, jsonData) {
			if (!jsonData.products_num) {
				$('#'+pagination_holder_id+', #'+pagination_bottom_holder_id+', .'+af_classes['product-count']).addClass('hidden');
			}
			if ('product_total_text' in jsonData) {
				$('.'+af_classes['heading-counter']).html(jsonData.product_total_text);
			}
			try {
				blockHover(); // defined in global.js
				compareButtonsStatusRefresh(); // defined in product-comparison.js
				totalCompareButtons(); //  defined in product-comparison.js
			} catch(err) {};
		});
		if (af_upd_search_form) {
			af.$w.on('load', function() {
				// in 1.6 sorting is hardcoded in standard search form, that is displayed on all pages
				// the following script removes hardcoded sorting in order to use custom sorting based on filter settings
				$('.search_query').siblings('input[name="orderby"], input[name="orderway"]').remove();
			});
		}
	}
});
/* since 3.2.3 */

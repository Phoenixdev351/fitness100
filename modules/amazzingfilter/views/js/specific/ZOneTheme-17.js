/**
*  @author    Amazzing
*  @copyright Amazzing
*  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

$.extend(customThemeActions, {
	documentReady: function() {
		af.useUniform = false; // Initially uniform is not applied
		customThemeActions.addClassToSortingOptions();
		if (prestashop.page.page_name == 'index' && !af.$dynamicContainer.length) {
			af.homePageLayoutUpdateRequired = 1;
			af.$dynamicContainer = $('#wrapper');
			af.$bcClone = $('.breadcrumb-wrapper ').clone();
		}
	},
	updateContentAfter: function(jsonData) {
		if (af.homePageLayoutUpdateRequired && !af.homePageLayoutUpdated) {
			af.$filterBlock.prependTo(af.$dynamicContainer);
			af.$bcClone.prependTo(af.$dynamicContainer); // required for margin above filter
			af.$dynamicContainer.children().wrap('<div class="container"></div>');
			af.homePageLayoutUpdated = 1;
		}
		customThemeActions.adjustGridView();
		customThemeActions.addClassToSortingOptions();
	},
	addClassToSortingOptions: function() {
		$('.products-sort-order').find('.js-search-link').addClass('select-list');
	},
	adjustGridView: function() { // based on 'updateProductList' in /themes/ZOneTheme/_dev/js/listing.js
		var $gridOptions =  $('#product_display_control').find('a'),
			storage = window.localStorage || window.sessionStorage;
		if (storage && storage.productListView) {
			var $opt = $gridOptions.filter('[data-view="'+storage.productListView+'"]');
			if ($opt.length && !$opt.hasClass('selected')) {
				$opt.click();
			}
		}
	}
});
$(window).on('load', function() {
	// unbind evens bound in /themes/ZOneTheme/_dev/js/listing.js searchFiterFacets()
	$('body').off('click', '.js-search-link');
});
/* since 3.1.6 */

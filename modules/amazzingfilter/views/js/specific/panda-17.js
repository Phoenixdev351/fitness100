/**
*  @author    Amazzing
*  @copyright Amazzing
*  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

$.extend(customThemeActions, {
	documentReady: function () {
		af.productItemSelector = '.product_list_item';
		customThemeActions.addClassToSortingOptions();
	},
	updateContentAfter: function (jsonData) {
		prestashop.emit('updatedProductListDOM');
		customThemeActions.addClassToSortingOptions();
		if (jsonData.seo_page) {
			af.bcSelector = '.breadcrumb_nav';
		}
	},
	addClassToSortingOptions: function() {
		$('.products-sort-order').find('.js-search-link').addClass('select-list');
	},
});
/* since 3.2.0 */

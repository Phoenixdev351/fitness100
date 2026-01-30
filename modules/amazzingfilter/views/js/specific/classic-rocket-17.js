/**
*  @author    Amazzing
*  @copyright Amazzing
*  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

customThemeActions.documentReady = function() {
	$('body').off('change', '#select-sort-order').on('change', '#select-sort-order', function() {
		var value = $(this).val().split('order=')[1].split('&')[0].split('.');
			af.applySorting(value[1], value[2]);
	});
}
/* since 3.1.3 */

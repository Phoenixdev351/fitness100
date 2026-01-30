/**
*  @author    Amazzing
*  @copyright Amazzing
*  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

$.extend(customThemeActions, {
	documentReady: function() {
		af.productItemSelector = '.product';
	},
	updateContentAfter: function(jsonData) {
		var maxColorBoxes = 5;
		$('.js-product-miniature').each(function() {
			var $boxes = $(this).find('.variant-links').find('.color');
			if ($boxes.length > maxColorBoxes) {
				$boxes.eq(maxColorBoxes - 1).nextAll('.color').addClass('hidden');
				$(this).find('.variant-links').find('.js-count').html('+'+($boxes.length - maxColorBoxes));
			}
		});
	},
});
/* since 3.1.8 */

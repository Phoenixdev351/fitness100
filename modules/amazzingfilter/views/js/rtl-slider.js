/**
*  @author    Amazzing
*  @copyright Amazzing
*  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

$.extend(af.slider, {
	orig: {
		setValues: af.slider.setValues,
		valueToPrc: af.slider.valueToPrc,
	},
	setValues: function($bar) {
		this.rtl_percentage = 0;
		this.orig.setValues($bar);
		this.rtl_percentage = 1;
	},
	valueToPrc: function(value, zeroValue, interval) {
		var prc = this.orig.valueToPrc(value, zeroValue, interval);
		if (this.rtl_percentage) {
			prc = 100 - prc;
		}
		return prc;
	},
});
/* since 3.1.5 */

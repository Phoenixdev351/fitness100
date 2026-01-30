/**
*  @author    Amazzing
*  @copyright Amazzing
*  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

if (typeof customThemeActions != 'undefined') {
	$.extend(customThemeActions, {
		updateContentAfter: function(jsonData){
			$(['stlazyloading', 'highdpiInit']).each(function(i, f) {
				if (typeof window[f] == 'function') {
					window[f]();
				}
			});
		},
	});
}
/* since 3.1.5 */

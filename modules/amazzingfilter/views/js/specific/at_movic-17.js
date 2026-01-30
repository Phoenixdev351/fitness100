/**
*  @author    Amazzing
*  @copyright Amazzing
*  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

$.extend(customThemeActions, {
    documentReady: function() {
        af.productItemSelector = '.ajax_block_product';
    },
    updateContentAfter: function(r) {
        if (typeof $.LeoCustomAjax == 'function') {
            var leoCustomAjax = new $.LeoCustomAjax();
            leoCustomAjax.processAjax();
        }
        if (typeof callLeoFeature != 'undefined') {
            callLeoFeature();
        }
    },
});
/* since 3.2.0 */

/**
*  @author    Amazzing
*  @copyright Amazzing
*  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

window.af_custom_updateProductListDOM = 1;
customThemeActions.updateContentAfter = function(r) {
    prestashop.emit('updateProductList', {});
};
/* since 3.1.8 */

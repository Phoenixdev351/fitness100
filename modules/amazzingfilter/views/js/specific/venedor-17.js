/**
*  @author    Amazzing
*  @copyright Amazzing
*  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

customThemeActions.updateContentAfter = function (jsonData) {
    var view = $.cookie('listingView') || 'view_grid';
    $('#'+view).addClass('active');
    $('#view_grid, #view_list').off('click').on('click', function() {
        var view = $(this).attr('id');
        $(this).addClass('active').siblings('.view_btn').removeClass('active');
        $('#products').removeClass('view_grid view_list').addClass(view);
        $.cookie('listingView', view);
    });
}
/* since 3.0.2 */

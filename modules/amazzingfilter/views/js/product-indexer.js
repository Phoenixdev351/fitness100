/**
*  @author    Amazzing
*  @copyright Amazzing
*  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

$(window).on('load', function() {
    if (typeof showSuccessMessage != 'undefined' && typeof translate_javascripts != 'undefined'
        && 'Form update success' in translate_javascripts && $('[name="form[id_product]"]').length) {
        var showSuccessMessageOriginal = showSuccessMessage,
            id_product = $('[name="form[id_product]"]').val();
        showSuccessMessage = function(msg) {
            showSuccessMessageOriginal(msg);
            if (msg == translate_javascripts['Form update success']) {
                $.ajax({
                    url: af_ajax_action_path,
                    data: 'action=IndexProduct&id_product='+id_product,
                    dataType: 'json',
                    success: function(r) {
                        console.dir('Product [ID:'+r.indexed+'] re-indexed');
                    }
                });
            }
        }
    }
});
/* since 3.1.6 */

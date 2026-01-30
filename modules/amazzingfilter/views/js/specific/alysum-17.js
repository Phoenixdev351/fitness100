/**
*  @author    Amazzing
*  @copyright Amazzing
*  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

$.extend(customThemeActions, {
    documentReady: function() {
        $(document).on('click', '.perpage-selector', function() {
            if (!$(this).data('afready')) {
                let $title = $(this);
                $title.parent().find('.js-search-link').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    let nbItems = $(this).text().trim();
                    if (nbItems != af.nbItems.current) {
                        $title.html($title.text().trim().replace(af.nbItems.current, nbItems));
                        af.nbItems.current = nbItems;
                        if (!af.ajax_path_orig) {
                            af.ajax_path_orig = af_ajax.path;
                        }
                        af_ajax.path = af.ajax_path_orig + '&resultsPerPage=' + af.nbItems.current;
                        $('#af_nb_items').val(af.nbItems.current).change();
                    }
                    $title.click();
                });
                $title.data('afready', 1);
            }
        });
    },
    updateContentAfter: function(r) {
        // based on /modules/pkcompare/views/js/scripts.min.js
        $('.bt_compare').prop('disabled', !parseInt($('.total-compare-val').text()));
    },
});
/* since 3.2.6 */

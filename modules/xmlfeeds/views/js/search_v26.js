/**
 * 2010-2022 Bl Modules.
 *
 * If you wish to customize this module for your needs,
 * please contact the authors first for more information.
 *
 * It's not allowed selling, reselling or other ways to share
 * this file or any other module files without author permission.
 *
 * @author    Bl Modules
 * @copyright 2010-2022 Bl Modules
 * @license
 *
 */

$(document).ready(function(){
    var p = $('.search_form');
    var position = p.position();

    if (position == undefined) {
        return false;
    }

    var cssObj = {
        'left' : position.left,
        'display' : 'none'
    };

    $('#search_result').css(cssObj);

    $('.search_form').keyup(function(event){
        add_mask();
        var search_val = $('.search_form').val();
        var search_type = $('input[name=search_type]:checked').val();

        if (search_val.length < 2 && search_type == 'search_name') {
            return false;
        }

        if (search_val != '')
        {
            $('#search_result').css({'display': 'block'});
            $('.autocomplite_clear').css({'display': 'inline'});

            var selected_products = $('.product_hidden').val();

            $.ajax({
                type: 'GET',
                url: '../modules/xmlfeeds/search.php?s_t='+search_type+'&s_p='+selected_products+'&s='+search_val,
                dataType: 'html',
                cache: false,
                success: function(msg){
                    $("#search_result").html(msg);

                    if(msg == '' || msg == null)
                        $('#search_result').css({'display': 'none'});
                }
            });
        }
        else
        {
            $('#search_result').css({'display': 'none'});
            $('.autocomplite_clear').css({'display': 'none'});
        }
    });

    $(window).resize(function(){
        var p = $('.search_form');
        var position = p.position();
        $('#search_result').css({'left' : position.left});
    });

    $('.autocomplite_clear').click(function() {
        autocomplite_clear();
    });

    $('.search_list_autocomplite .search_p_list').live('click', function() {
        var div_id = $(this).attr('id');
        var id = div_id.split('-');
        var productSettingsPage = $('input[name=product_settings_page]').val();
        var currentPageUrl = $('input[name=current_url]').val();
        var packageId = $('input[name=product_setting_package_id]').val();

        if (typeof productSettingsPage !== "undefined" && typeof currentPageUrl !== "undefined") {
            currentPageUrl.replace('product_settings_search_id=', 'product_settings_search_id_old=');
            currentPageUrl = currentPageUrl+'&product_settings_search_id='+id[1];

            if (currentPageUrl.indexOf('product_setting_package_id') < 1) {
                currentPageUrl = currentPageUrl + '&product_setting_package_id=' + packageId;
            }

            window.location.href = currentPageUrl;
            return true;
        }

        $('#search_p-'+id[1]).clone().prependTo('.show_with_products');
        $('#search_p-'+id[1]).hide();

        var product_hidden = $('.product_hidden').val();

        $('.product_hidden').val(product_hidden+id[1]+',');
    });

    $('.show_with_products .search_drop_product').live('click', function(){
        var div_id = $(this).attr('id');
        var id = div_id.split('-');

        $('.show_with_products #search_p-'+id[1]).addClass('productListDeleted');

        var product_hidden = $('.product_hidden').val();
        product_hidden = product_hidden.replace(','+id[1]+',', ',');

        $('.product_hidden').val(product_hidden);
    });

    $('#search_mask').live('click', function(){
        $('#search_mask').hide();

        autocomplite_clear();
    });

    function autocomplite_clear()
    {
        $('#search_result').css({'display': 'none'});
        $('.autocomplite_clear').css({'display': 'none'});
        $('.search_form').val('');
    }

    function add_mask()
    {
        var width = $(document).width();
        var height = $(document).height();

        $('#search_mask').css({'display': 'block', 'width':width, 'height':height});
    }
});
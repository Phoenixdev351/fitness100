/**
 * Facebook Conversion Pixel Tracking Plus
 *
 * NOTICE OF LICENSE
 *
 * @author    Pol Rué
 * @copyright Smart Modules 2014
 * @license   One time purchase Licence (You can modify or resell the product but just one time per licence)
 * @version 2.3.3
 * @category Marketing & Advertising
 * Registered Trademark & Property of smart-modules.com
 *
 * ***************************************************
 * *     Facebook Conversion Trackcing Pixel Plus    *
 * *          http://www.smart-modules.com           *
 * *                     V 2.3.3                     *
 * ***************************************************
 */

$(document).ready(function() {
    // Create the Left menú
    var panels = '';
    setTimeout(function() {
        if ($('#module-body .panel-heading').length > 0) {
            panels = $('#module-body .panel').filter(function() {
                //console.log($(this).parents('.panel').length == 0);
                return $(this).parents('.panel').length == 0;
            });
            //console.log(panels);
            var modContent;
            if ($('#module-body').length > 0) {
                modContent = $('#module-body').append('<div id="module-nav" class="productTabs col-lg-2 col-md-3"><div class="list-group"></div></div>');
            } else {
                modContent = panels.first().before('<div id="module-nav" class="productTabs col-lg-2 col-md-3"><div class="list-group"></div></div>');
            }
            $('#module-nav').after('<div id="module-content" class="col-lg-10 col-md-9"></div>');
            $('#module-body form').appendTo('#module-content');

            panels.each(function(i, e) {
                if (typeof $(this).attr('id') == 'undefined') {
                    $(this).attr('id', 'fieldset_' + i + '_' + i + '_' + i);
                }
                // If the parent element isn't a form move it.
                if ($(this).parents('form').length == 0) {
                    $(this).appendTo('#module_form');
                    //htmlData = htmlData + $(this).html();
                }
                var thisPanelHead = $(this).find(".panel-heading");

                if (typeof thisPanelHead.data('position') !== 'undefined' && $('.productTabs .list-group-item').length >= thisPanelHead.data('position')) {
                    $('.productTabs .list-group-item').eq(thisPanelHead.data('position')).before('<a class="list-group-item" href="#' + thisPanelHead.parent('.panel').attr('id') + '">' + thisPanelHead.html() + '</a>');
                } else {
                    $('.productTabs .list-group').append('<a class="list-group-item" href="#' + thisPanelHead.parent('.panel').attr('id') + '">' + thisPanelHead.html() + '</a>');
                }

            });

            /* Add elements, force the position in the menu if configured. */
            /* Build the navigation menu */
            /*$('#module-content .panel-heading').each(function() {
                if (typeof $(this).data('position') !== 'undefined' && $('.productTabs .list-group-item').length >= $(this).data('position')) {
                    $('.productTabs .list-group-item').eq($(this).data('position')).before('<a class="list-group-item" href="#' + $(this).parent('.panel').attr('id') + '">' + $(this).html() + '</a>');
                } else {
                    $('.productTabs .list-group').append('<a class="list-group-item" href="#' + $(this).parent('.panel').attr('id') + '">' + $(this).html() + '</a>');
                }
            });*/

            // Initialize the tabs
            $('.productTabs a:first').addClass('active');
            panels.hide();
            panels.first().show();

            $('#module-body form').each(function() {
                $(this).append('<input type="hidden" name="selected_menu" value="">');
            });

            $('.productTabs a').click(function(e) {
                $('.productTabs a').removeClass('active');
                $(this).addClass('active');
                e.preventDefault();
                var searchTab = $(this).attr('href');
                panels.hide();
                $(searchTab).show();
                $('input[name="selected_menu"]').val(searchTab);
            });
        // PS 1.5. Versions
        } else if ($('.fieldset legend').length > 0) {
            var form_pos = 100;
            panels = $('#module-body fieldset, #module-body form');
            for (var i = 0; i < panels.length; i++) {
                if (panels[i].tagName == 'FORM') {
                    var form_pos = i;
                }
            }
            panels.each(function(i, e) {
                if (form_pos > i) {
                    $(this).prependTo('#module-body form');
                } else {
                    $(this).appendTo('#module-body form');
                }
            });

            $('form#module_form').addClass('form-horizontal col-lg-10 col-md-9');
            $('form#module_form').before('<div class="productTabs col-lg-2 col-md-3" style="margin-top:11px"><ul class="tab"></ul></div></div>');
            $('fieldset legend').each(function() {
                $('.productTabs .tab').append('<li class="tab-row"><a class="list-group-item tab-page" href="#' + $(this).parent('fieldset').attr('id') + '">' + $(this).html() + '</a></li>');
            });
            $('.productTabs a:first').addClass('selected');
            $('fieldset:not(:first)').hide();
            $('.productTabs a').click(function(e) {
                $('.productTabs a').removeClass('selected');
                $(this).addClass('selected');
                e.preventDefault();
                var searchTab = $(this).attr('href');
                $('fieldset').hide();
                $(searchTab).show();
            });
        }
        selectLastMenu(panels);
    }, 600);

    /* Target menu to directly access the section */
    $(document).on('click', '.target-menu', function() {
        var dest = $(this).attr('href');
        $(this).closest('#module-body').find('#moduleTabs').first().find('a').each(function() {
            if ($(this).attr('href') === dest) {
                $(this).click();
                return;
            }
        });
    });

    function selectLastMenu(panels) {
        var to_select = '';
        if (window.location.hash != '') {
            to_select = window.location.hash;
        } else if (typeof selected_menu !== 'undefined' && selected_menu != '') {
            to_select = selected_menu;
        }
        if (to_select != '') {
            $('#moduleTabs a').each(function() {
                if ($(this).attr('href').indexOf(to_select) !== -1) {
                    $(this).click();
                    window.scrollTo(0, 0);
                    return false;
                }
            });
        } else {
            $('#moduleTabs a').first().click();
        }
    }
});
/**
 * 2010-2021 Bl Modules.
 *
 * If you wish to customize this module for your needs,
 * please contact the authors first for more information.
 *
 * It's not allowed selling, reselling or other ways to share
 * this file or any other module files without author permission.
 *
 * @author    Bl Modules
 * @copyright 2010-2021 Bl Modules
 * @license
 */

$(document).ready(function() {
    $('#indexing-action').on('click', function() {
        $('#indexing-action-error div').text('');
        $('#indexing-action-ok div').text('');
        let val = $('#indexing-page-url').val();

        if (val.length < 4) {
            $('#indexing-action-error div').text('Error: invalid URL');

            return false;
        }

        $.ajax({
            type: 'POST',
            url: $('#indexing-api-url').text(),
            dataType: 'json',
            cache: false,
            data: {url: val},
            success: function(result) {
                if (result.success) {
                    $('#indexing-action-ok div').text(result.message);
                } else {
                    $('#indexing-action-error div').text('Error: '+result.message);
                }
            }
        });
    });
});

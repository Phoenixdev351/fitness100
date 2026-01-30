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

$(document).ready(function() {
	$('#add-title-replace-row').click(function() {
		var id = $('#title-replace-box .cn_line').last().attr('data-id');
		var box = $('#title-replace-new-row').html().replace(/__NEW_ROW__/g, parseInt(id)+1);

		$('#title-replace-box').append(box);
	});

	$('#affiliate_price_url').click(function(){
		$('#affiliate_price_url_list').slideToggle('fast');
		$('#multistore_url_list').hide();
	});
	
	$('#multistore_url').click(function(){
		$('#multistore_url_list').slideToggle('fast');
		$('#affiliate_price_url_list').hide();
	});

	$('#category-map-instruction-action').click(function(){
		$('#category-map-instruction-box').slideToggle('fast');
	});
	
	$('.multistore_url_checkbox').change(function() {
		var div_id = $(this).attr('id');
		var url = $('#feed_url').val().split('&multistore');
		
		if (div_id == 'all_multistore') {
			$('.multistore_url_checkbox').prop('checked', false);
			$('#all_multistore').prop('checked', true);
			$('#feed_url').val(url[0]);
			$('#feed_url_open').attr('href', url[0]);
			$('#feed_url_download').attr('href', url[0]+'&download=1');
		} else if (div_id == 'domain_multistore') {
			$('.multistore_url_checkbox').prop('checked', false);
			$('#domain_multistore').prop('checked', true);
			$('#feed_url').val(url[0]+'&multistore=auto');
            $('#feed_url_open').attr('href', url[0]+'&multistore=auto');
            $('#feed_url_download').attr('href', url[0]+'&multistore=auto&download=1');
		} else {
			$('#all_multistore').prop('checked', false);	
			$('#domain_multistore').prop('checked', false);
            $('.multistore_url_checkbox').not(this).prop('checked', false);

            var count_checked = $('.multistore_url_checkbox:checked').length;
			
			if (count_checked > 0) {
				url[0] = url[0]+'&multistore=';
				
				$('.multistore_url_checkbox:checked').each(function() {
				   url[0] = url[0]+this.value+',';
				});

                url[0] = url[0].slice(0,-1);

				$('#feed_url').val(url[0]);
                $('#feed_url_open').attr('href', url[0]);
                $('#feed_url_download').attr('href', url[0]+'&download=1');
			} else {
				$('#feed_url').val(url[0]);
                $('#feed_url_open').attr('href', url[0]);
                $('#feed_url_download').attr('href', url[0]+'&download=1');
			}
		}
	});

    $(".show_cron_install").click(function(){
        $("#cron_install_instruction").slideToggle();
    });

	$(".google_cat_map_blmod").autocomplete({
		minLength: 3,
		source: function( request, response ) {
			var matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term), "i");
			var select_el = ga_cat_blmod;
			var rep = [];

			for (var i = 0; i < select_el.length; i++) {
				var text = select_el[i];

				if (select_el[i] && (!request.term || matcher.test(text))) {
					rep.push({
						label: text,
						value: text,
						option: select_el[i]
					});
				}
			}

			response(rep);
		}
	});

	$("#product_list_menu").change(function() {
		$('body').css({'cursor':'progress'});
		$("#product_list_select").trigger( "click" );
	});

    $('.affiliate-price-cron-button').click(function() {
        var id = $(this).attr('id');
        var name = $(this).text();

        if ($(this).hasClass('affiliate-price-cron-button-active')) {
            $('#feed_url').val($('#cron_path_original').val());
			$('#feed_url_open').attr('href', $('#cron_path_original').val());
			$('#feed_url_download').attr('href', $('#cron_path_original').val());

            $('#cron_command').val($('#cron_command_original').val());
            $('.affiliate-price-cron-button').removeClass('affiliate-price-cron-button-active');

        	return false;
		}

        $('.affiliate-price-cron-button').removeClass('affiliate-price-cron-button-active');
        $('#'+id).addClass('affiliate-price-cron-button-active');

        $('#feed_url').val($('#cron_path_original').val().replace('.xml', '_'+name+'.xml'));
		$('#feed_url_open').attr('href', $('#cron_path_original').val().replace('.xml', '_'+name+'.xml'));
		$('#feed_url_download').attr('href', $('#cron_path_original').val().replace('.xml', '_'+name+'.xml'));

        $('#cron_command').val($('#cron_command_original').val()+' '+name);
    });

    $("#product_setting_package_id").change(function() {
        $('body').css({'cursor':'progress'});
        $("#product_setting_package_select").trigger( "click" );
    });

    $('.row .option-box-title').click(function() {
        var id = $(this).attr('id').split('_');
        var box = $('#option_box_'+id[1]);
        var titleIcon = $('#option-box-title_'+id[1]+' i');
        var content = $('#option-box-content_'+id[1]);

        if (!content.is(':visible')) {
            content.show('fast');
            box.addClass('option-box-active');
            titleIcon.addClass('icon-angle-up');
            titleIcon.removeClass('icon-angle-down');
		} else {
            content.hide('fast');
            box.removeClass('option-box-active');
            titleIcon.addClass('icon-angle-down');
            titleIcon.removeClass('icon-angle-up');
		}
    });

	$('.datepicker-blmod').datepicker({
		dateFormat: 'yy-mm-dd'
	});

	$('select[name="filter_date_type"]').change(function() {
		var typeId = $(this).val();

		$('#order-filter-date-range').hide();
		$('#order-filter-custom-days').hide();

		if (typeId == 6) {
			$('#order-filter-custom-days').show();
		}

		if (typeId == 7) {
			$('#order-filter-date-range').show();
		}
	});

	$('#merge_attributes_by_group').change(function() {
		if ($(this).prop("checked")) {
			$('#merge-attributes-by-group-select').show();
			$('#merge-attributes-by-group-box').addClass('box-toggle-active');
		} else {
			$('#merge-attributes-by-group-select').hide();
			$('#merge-attributes-by-group-box').removeClass('box-toggle-active');
		}
	});

	$('.feed_url_copy_action').click(function() {
		let copyText = document.getElementById('feed_url');
		copyText.select();
		copyText.setSelectionRange(0, 99999);
		document.execCommand('copy');

		$('#feed_url_copy').css('color', '#72c279');

		setTimeout(function(){
			$('#feed_url_copy').removeAttr('style');
		}, 500);
	});

	$('#search-feed-type').keyup(function(e) {
		var value = $('#search-feed-type').val();

		$.ajax({
			type: 'GET',
			url: '../modules/xmlfeeds/TypeSearch.php?s='+value,
			dataType: 'html',
			cache: false,
			success: function(result){
				$('#types-by-alphabet').html(result);
			}
		});
	});

	$('#title_editor_all_attributes').change(function() {
		if ($(this).prop('checked')) {
			$('.title_editor_attributes').attr('checked', 'checked').attr('readonly', true);
			$('.title_editor_attributes').attr('disabled', 'disabled');
		} else {
			$('.title_editor_attributes').removeAttr('checked').attr('readonly', false);
			$('.title_editor_attributes').removeAttr('disabled', '');
		}
	});

	$('#add-new-feed label.feed_type_icon input').live('click', function() {
		$('.blmod-modal-bg').show();
		$('.blmod-modal').show();

		if ($('#new-feed-name').val().length === 0) {
			$('#new-feed-name').val($(this).attr('title'));
		}

		$('input[name=add_new_feed_insert]').trigger('click');
	});

	$('.open-edit-price-action').click(function() {
		$('#edit-price-box_'+$(this).attr( 'data-pid')).toggle('fast', function(){});
	});
});

function boxToggle(name)
{
	$('.'+name).hide();
	$('.'+name+'_button').show();

	$('.'+name+'_button').click(function() {
		let isHiddenBefore = $('.'+name).is(':hidden');
		let trElement = $('.'+name).closest("tr");

		if (isHiddenBefore) {
			trElement.addClass('box-toggle-active');
		} else {
			trElement.removeClass('box-toggle-active');
		}

		$('.'+name).slideToggle();
	});
}
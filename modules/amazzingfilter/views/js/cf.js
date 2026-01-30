/**
*  @author    Amazzing
*  @copyright Amazzing
*  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

var cf = {
	icons: {
		locked: af_classes['icon-lock'],
		unlocked: af_classes['icon-unlock-alt']
	},
	init: function() {
		cf.bindEvents();
		if (window.af) {
			af.events.filter.cf = cf.afEvents;
		}
	},
	bindEvents: function() {
		$(document).on('click', '.cf-toggle', function(e) {
			e.preventDefault();
			let $parent = $(this).closest('.cf-wrapper');
			if (!$parent.data('ready')) {
				$parent.addClass('loading');
				let data = {action: 'getForm'},
					callback = function(r) {
						$parent.find('.close-modal').before(r.html);
						cf.related.init();
						$parent.data('ready', true).removeClass('loading').addClass('show-modal');
					};
				cf.ajaxRequest(data, callback);
			} else {
				$parent.toggleClass('show-modal');
			}
		}).on('click', '.cf-submit', function() {
			$(this).parent().addClass('loading');
			let data = {
					action: 'save',
					filters: $(this).closest('form').serialize(),
				},
				callback = function(r) {
					if (r.saved) {
						cf.redirectAfterSaving();
					}
				};
			cf.ajaxRequest(data, callback);
		}).on('click', '.cf-reset', function() {
			$(this).addClass('loading');
			cf.ajaxRequest({action: 'save', filters: ''}, function() {
				window.location.reload();
			});
		}).ready(function() {
			cf.related.init();
		});
	},
	afEvents: function() {
		let $cfBottom = af.$filterBlock.find('.cf-wrapper.bottom');
		if ($cfBottom.length) {
			af.on('toggleCompactView', function() {
				let action = af.isCompact ? 'appendTo' : 'insertAfter';
				$cfBottom[action](af.$filterBlock.find('.btn-holder'));
				$('.af-form').toggleClass('has-cf-bottom', af.isCompact);
			});
		}
		if (cf_url_params) {
			af.on('prepareDynamicParams', function(params) {
				cf.getActiveLabels().each(function() {
					let groupURL = $(this).closest('.af_filter').data('url'),
						url = $('#'+$(this).data('id')).data('url');
					if (groupURL in params) {
						params[groupURL] += ','+url;
					} else {
						params[groupURL] = url;
					}
				});
			});
			if (cf.getActiveLabels().length) {
				af.updURLrequired = true;
			}
		}
		$('.af-form').on('click', '.customer-filter-label', function() {
			$(this).toggleClass('unlocked');
			var locked = !$(this).hasClass('unlocked'),
				iconClass = locked ? cf.icons.locked : cf.icons.unlocked,
				$input = $(this).find('input[type="hidden"]');
			$(this).find('a').first().attr('class', iconClass);
			if ($input.length) {
				var name = locked ? $input.data('name') : 'nosubmit';
				$input.attr('name', name).change();
			} else { // selects
				var val = locked ? $('option[id="'+$(this).data('id')+'"]').val() : 0;
				$(this).toggleClass('hidden-name', !locked).next().toggleClass('hidden', locked)
				.find('select').val(val).change();
			}
		});
	},
	getActiveLabels: function() {
		return $('.customer-filter-label').not('.unlocked');
	},
	redirectAfterSaving: function() {
		let url = cf_redirect || (window.af ? af.getStaticURL() : window.location.href);
		if (url != window.location.href) {
			window.location.href = url;
		} else {
			window.location.reload();
		}
	},
	related: {
		init: function() {
			$('.cf-related').not('.ready').find('.cf-select').on('change', function() {
				let $relatedGroup = $(this).closest('.cf-group').next('.cf-group');
				if ($relatedGroup.length) {
					if ($(this).val() == 0) {
						cf.related.toggleGroup($relatedGroup, true);
						return;
					}
					let $relatedSelect = $relatedGroup.find('.cf-select');
						data = {
							action: 'getGroupValues',
							key: $relatedSelect.attr('name'),
							applied_filters: $relatedGroup.prevAll('.cf-group').find('select').serialize(),
						},
						callback = function(r) {
							let html = '';
							$.each(r, function(id, txt) {
								html += '<option value="'+id+'">'+txt+'</option>';
							});
							$relatedSelect.find('option').not('.first').remove();
							$relatedSelect.append(html);
							cf.related.toggleGroup($relatedGroup, !html);
						};
					cf.ajaxRequest(data, callback);
				}
			}).end().addClass('ready');
		},
		toggleGroup: function($group, block) {
			if (block) {
				$group.nextAll('.cf-group').andSelf().addClass('blocked')
				.find('.cf-select').val(0).find('.first').html('--');
			} else {
				let $firstOption = $group.removeClass('blocked').find('.first');
				$firstOption.html($firstOption.data('txt'));
				cf.related.toggleGroup($group.next('.cf-group'), true);
			}
		},
	},
	ajaxRequest(data, callback) {
		data.cf_action = data.action;
		data.action = 'customerFilterAction';
		data.token = af_ajax.token;
		$.ajax({
			type: 'POST',
			url: af_ajax.path,
			dataType: 'json',
			data: data,
			success: function(r) {
				callback(r);
			},
			error: function(r) {
				console.warn($(r.responseText).text() || r.responseText);
			}
		});
	},
};
cf.init();
/* since 3.3.0 */

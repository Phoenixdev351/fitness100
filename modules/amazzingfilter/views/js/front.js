/**
*  @author    Amazzing
*  @copyright Amazzing
*  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

var af_product_list_selector = '#js-product-list',
	pagination_class = af_classes['pagination'],
	customThemeActions = {
		documentReady: function() {},
		updateContentAfter: function(jsonData){}
	};

var af = {
	defineVariables: function() {
		$.extend(af, {
			// basic varibales
			blockAjax: false, resizeTimer: false, mouseLeaveTimer: false, qsTimer: false, cosCounter: 1,
			// misc variables
			isCompact: false,
			autoScroll: false,
			useUniform: !!$.fn.uniform,
			dimZeroMatches: parseInt($('#af_dim_zero_matches').val()),
			hideZeroMatches: parseInt($('#af_hide_zero_matches').val()),
			showCounters: parseInt($('#af_count_data').val()),
			includeGroup: parseInt($('#af_include_group').val()),
			dynamicParams: {
				f: parseInt($('#af_url_filters').val()),
				s: parseInt($('#af_url_sorting').val()),
				p: parseInt($('#af_url_page').val()),
			},
			nbItems: {
				current: $('#af_nb_items').val(),
				orig: $('#af_npp').val(),
			},
			isHorizontal: $('#af_layout').val() == 'horizontal',
			productItemSelector: '.'+af_classes['js-product-miniature'],
			noItemsClass: 'no-available-items',
			popstateURL: af.removeHash(window.location.href),
			reqNum: 0,
			doAfterAjaxOnce: [],
			// basic $elements
			$w: $(window),
			$listWrapper: $('.af_pl_wrapper'), // redefined later in setWrapper()
			$filterBlock: $('#amazzing_filter'),
			$selectedFilters: $('.selectedFilters'),
			$pageInput: $('#af_page'),
			$viewBtn: $('.viewFilteredProducts'),
			$dynamicContainer: $('#products').first(),
		});
		if (!af.$dynamicContainer.length) {
			af.$dynamicContainer = $('#content').length ? $('#content') : $('#main');
		}
	},
	documentReady: function() {
		af.defineVariables();
		customThemeActions.documentReady();
		af.setWrapper();
		af.bindEvents();
		af.emit('documentReady');
		af.toggleCompactView();
		af.cutOff.prepare();
		af.adjustFolderedStructure();
		af.prepareLoadMoreIfRequired();
		af.updateSelectedFilters();
		af.updURLrequired = af.updURLrequired || (!af.dynamicParams.p && af.$pageInput.val() > 1);
		if (af.updURLrequired && af.$listWrapper.length) {
			af.updateURL(af.prepareUrlAndVerifyParams());
		}
	},
	prepareLoadMoreIfRequired: function() {
		if (load_more && af.$listWrapper.length) {
			$('.dynamic-product-count').html(af_product_count_text);
			$('.dynamic-loading.next').removeClass('hidden').insertAfter(af.$listWrapper);
			$('.dynamic-loading.prev').removeClass('hidden').insertBefore(af.$listWrapper);
			if (!show_load_more_btn) {
				$('.loadMore.next').addClass('hidden');
			}
			$('.loadMore').on('click', function() {
				var $parent = $(this).closest('.dynamic-loading'),
					p = parseInt(af.$pageInput.val());
				if ($parent.hasClass('next')) {
					p++;
				} else { // prev
					$(this).data('p', $(this).data('p') - 1);
					if ($(this).data('p') < 2) {
						af.doAfterAjaxOnce.push(function() {
							$parent.addClass('hidden');
						});
					}
				}
				$parent.addClass('loading');
				af.$pageInput.val(p).change();
			});
			af.rememberScrollPosition();
			if ($('#af_p_type').val() == 3) {
				af.infScroll.init();
			}
		}
	},
	rememberScrollPosition: function() {
		$(document).on('click', af.productItemSelector, function(e) {
			var possibleLink = $(e.target).closest('a').attr('href');
			if (possibleLink && !possibleLink.startsWith('#')) {
				af.$lastClickedItem = $(this);
				clearTimeout(af.lastClickedItemTimer);
				af.lastClickedItemTimer = setTimeout(function() {
					delete af.$lastClickedItem; // if pagehide is not triggered within 3s after click
				}, 3000);
			}
		});
		af.$w.on('pagehide', function(e) {
			if (af.$lastClickedItem) {
				if (e.originalEvent && e.originalEvent.persisted) {
					return; // bfcache
				}
				var $currentItems = af.$listWrapper.find(af.productItemSelector),
					prevNum = $currentItems.index(af.$lastClickedItem),
					nbItems = parseInt(af.nbItems.current),
					scrolledPage = Math.floor(prevNum / nbItems),
					initialPage = $('.loadMore.prev').data('p') || 1,
					elIndexInPage = prevNum - (scrolledPage * nbItems),
					rememberPage =  scrolledPage + initialPage,
					scrollCompensation = (af.$w.height() - af.$lastClickedItem.height()) / 2,
					requiredScrollPosition = $currentItems.eq(elIndexInPage).offset().top - scrollCompensation;
				af.dynamicParams.p = 1;
				af.$pageInput.val(rememberPage);
				af.updateURL(af.prepareUrlAndVerifyParams()); // page param is updated in URL and it will be loaded when user clicks back
				window.scrollTo(0, requiredScrollPosition); // scroll position is restored when user clicks back
			}
		});
	},
	bindEvents: function() {
		Object.keys(af.events).forEach(function(type) {
			Object.keys(af.events[type]).forEach(function(event) {
				af.events[type][event]();
			});
		});
	},
	events: {
		filter: {
			general: function() {
				$('.af-form').on('click', 'a[href="#"]', function(e) {
					e.preventDefault();
				}).on('change', 'input, select', function() {
					if ($(this).data('notrigger')) {
						return;
					}
					var trigger = $(this).attr('id'),
						type = $(this).attr('type') || $(this).prop('tagName').toLowerCase(),
						$parent = $(this).closest('.af_filter'),
						isHiddenInput = $(this).parent().hasClass('hidden_inputs'),
						updateList = !af.$viewBtn.data('active') || isHiddenInput;
					if (type == 'checkbox' || type == 'radio') {
						if (type == 'radio') {
							$parent.find('li.active').removeClass('active');
						}
						$(this).closest('li').toggleClass('active', $(this).prop('checked'));
					} else if (type == 'select' && !$(this).closest('.selector-with-customer-filter').hasClass('hidden') &&
						$(this).find('option[value="'+$(this).val()+'"]').hasClass('customer-filter')) {
						$(this).closest('.af_filter').find('.customer-filter-label').click();
						return;
					}
					if (trigger != 'af_page') {
						af.$pageInput.val(1);
					}
					if (!af.blockAjax) {
						if (af.$viewBtn.data('active') && !isHiddenInput) {
							af.$viewBtn.addClass('loading').data('animate-after-loading', 1);
						}
						af.applyFilters(trigger, updateList);
					}
				});
				af.moreFilters.init()
			},
			selectedFilters: function() {
				af.$selectedFilters.on('click', 'a', function(e) {
					e.preventDefault();
					var $parentRow = $(this).parent(),
						$groupBlock = $('.af_filter[data-url="'+$parentRow.data('group')+'"]');
					if ($(this).hasClass('close')) {
						if ($groupBlock.hasClass('type-1') || $groupBlock.hasClass('type-2')) {
							var $input = $groupBlock.find('#'+$parentRow.data('id'));
							$input.prop('checked', false).change();
							if (af.useUniform) {
								$input.parent().removeClass('checked');
							}
						} else if ($groupBlock.hasClass('type-3')) {
							$groupBlock.find('select').val('').change();
						} else if ($groupBlock.hasClass('has-slider')) {
							af.slider.resetValues(af.slider.$[$parentRow.data('id')]);
						}
					} else if ($(this).hasClass('all')) {
						af.blockAjax = true;
						af.$selectedFilters.find('.cf').find('a').click();
						af.blockAjax = false;
						if (af.$viewBtn.data('active')) {
							af.$viewBtn.addClass('loading').data('animate-after-loading', 1);
						}
						af.applyFilters('clear_all', !af.$viewBtn.data('active'));
					}
				});
			},
			dim: function() {
				if (af.dimZeroMatches && !af.hideZeroMatches) { // block visible checkboxes/radio with 0 matches
					af.$filterBlock.on('click', 'input.af.checkbox, input.af.radio', function(e) {
						// prop checked becomes true for unchecked inputs, right after click
						if ($(this).prop('checked') && $(this).closest('li').hasClass('no-matches')) {
							e.preventDefault();
							$(this).prop('checked', false);
							if (af.useUniform) {
								$(this).parent().removeClass('checked').parent().removeClass('focus');
								if ($(this).hasClass('radio')) {
									var $parentBlock = $(this).closest('.af_filter'),
										id = $('.cf[data-group="'+$parentBlock.data('url')+'"]').data('id');
									// keep checked styles on radioboxes that were checked before
									$parentBlock.find('#'+id).parent().addClass('checked').parent().addClass('focus');
								}
							}
						}
					});
				}
			},
			viewBtn: function() {
				af.$viewBtn.on('click', function(e) {
					e.preventDefault();
					if (!$(this).hasClass('loading')) {
						$(this).addClass('loading');
						af.applyFilters('view_products', true);
						if (af.isCompact) {
							$('.af-compact-overlay').click();
						}
					}
				});
			},
			foldered: function() {
				$('.af-toggle-child').on('click', function() {
					$(this).closest('.af-parent-category').toggleClass('open');
				});
			},
			toggleableContent: function() {
				af.$filterBlock.on('click', '.af_subtitle.toggle-content', function(e) {
					e.preventDefault();
					var $filter = $(this).closest('.af_filter');
					if (!$filter.hasClass(af.noItemsClass)) {
						$filter.toggleClass('closed');
						if (!$filter.hasClass('closed')) {
							if ($filter.hasClass('type-3') && af.useUniform) {
								$filter.find('.af-select').uniform();
							}
							if (af.isCompact) {
								$filter.siblings('.af_filter').addClass('closed');
							} else if (af.isHorizontal) {
								af.autoCloseFilterContent($filter);
							}
						}
					}
					af.cutOff.prepare($filter);
				});
			},
			quickSearch: function() {
				af.$filterBlock.on('keyup', '.qsInput', function() {
					var $qsInput = $(this);
					clearTimeout(af.qsTimer);
					af.qsTimer = setTimeout(function() {
						var value = $qsInput.val().toLowerCase(),
							$options = $qsInput.closest('.af_filter_content').find('li');
						$options.removeClass('qs-hidden');
						if (value.length) {
							$options.each(function() {
								if ($(this).children('label').children('.name').text().toLowerCase().indexOf(value) === -1) {
									$(this).addClass('qs-hidden');
								}
							});
						}
						$qsInput.toggleClass('has-value', value.length > 0).siblings('.qs-no-matches').
						toggleClass('hidden', !!$options.not('.qs-hidden, .no-matches').length);
						if ($qsInput.data('tree')) {
							$options.removeClass('qs-half-hidden').filter('.af-parent-category.qs-hidden').each(function() {
								if ($(this).find('li').not('.qs-hidden').length) {
									$(this).addClass('qs-half-hidden open');
								}
							});
						}
						af.cutOff.prepare($qsInput.closest('.af_filter'));
					}, 200);
				});
			},
		},
		theme: {
			nbItems: function() {
				$(document).off('change', 'select[name="n"]').on('change', 'select[name="n"]', function() {
					af.nbItems.current = $(this).val();
					$('#af_nb_items').val(af.nbItems.current).change();
				}).on('submit', '.showall', function(e) {
					e.preventDefault();
					var num = $(this).find('input[name="n"]').val(),
						$nSelect = $('select[name="n"]');
					if (!$nSelect.find('option[value="'+num+'"]').length) {
						var maxNum = $nSelect.find('option').last().val();
						if (parseInt(maxNum) >= parseInt(num)) {
							num = maxNum;
						} else {
							var newOptionHTML = '<option value="'+num+'">'+num+'</option>';
							$nSelect.append(newOptionHTML);
							if (af.useUniform) {
								$nSelect.uniform();
							}
						}
					}
					$nSelect.val(num).change();
				});
			},
			sorting: function() {
				$('body').off('click', '.js-search-link').on('click', '.select-list.js-search-link', function(e) {
					e.preventDefault();
					$(this).addClass('current').siblings().removeClass('current');
					// todo: consider cases when "order=" is not present in href
					var value = $(this).attr('href').split('order=')[1].split('&')[0].split('.');
						orderBy = value[1],
						orderWay = value[2],
						sortingName = $(this).text(),
						$title = $(this).closest('.products-sort-order').find('.select-title'),
						$htmlElementsInTitle = $title.children();
					af.applySorting(orderBy, orderWay);
					$title.html(sortingName).append($htmlElementsInTitle);
				});
			},
			pagination: function() {
				$(document).on('click','.'+pagination_class+' a', function(e) {
					e.preventDefault();
					var page = 1;
					if ($(this).attr('href').indexOf('?') > -1) {
						var params = af.unserialize($(this).attr('href').split('?')[1], false);
						if (af_param_names.p in params && params[af_param_names.p] > 1) {
							page = params[af_param_names.p];
						}
					}
					af.$pageInput.val(page).change();
				});
			},
		},
		browser: {
			popstate: function() {
				af.$w.on('popstate', function() { // when user clicks back/forward in browser
					if (af.removeHash(window.location.href) != af.popstateURL) {
						af.updateFiltersBasingOnURL(window.location.href);
					}
				});
			},
			resize: function() {
				af.$w.on('resize', function() {
					clearTimeout(af.resizeTimer);
					af.resizeTimer = setTimeout(function() {af.toggleCompactView()}, 100);
				});
			},
		},
	},
	autoCloseFilterContent: function($filter) {
		$filter.off('mouseenter mouseleave').on('mouseenter', function() {
			clearTimeout(af.mouseLeaveTimer);
		}).on('mouseleave', function() {
			af.mouseLeaveTimer = setTimeout(function() {
				if (!$filter.hasClass('closed') && !$filter.find('input[type="text"]:focus').length) {
					$filter.find('.af_subtitle.toggle-content').click();
				}
				$filter.off('mouseenter mouseleave');
			}, 1000);
		});
		af.onClickOutSide(
			$filter.find('.af_filter_content'),
			function() {$filter.addClass('closed');}
		);
	},
	onClickOutSide: function($el, action) {
		setTimeout(function() {
			let clickEvent = 'click.outside'+(af.cosCounter++);
			$(document).on(clickEvent, function(e) {
				if (!$el.is(e.target) && $el.has(e.target).length === 0) {
					action();
					$(document).off(clickEvent);
				}
			});
		}, 0);
	},
	applySorting: function (orderBy, orderWay) {
		$('#af_orderBy').val(orderBy);
		$('#af_orderWay').val(orderWay).change();
	},
	toggleSidePanel: function() {
		let $body = $('body');
		if (!$body.hasClass('show-filter')) {
			$body.data('scroll', af.$w.scrollTop());
		}
		$body.toggleClass('show-filter');
		if ($body.hasClass('show-filter')) {
			$body.css('top', '-'+$body.data('scroll')+'px'); // block scrolling to top because of position: fixed
		} else {
			$body.css('top', '');
			window.scrollTo({top:$body.data('scroll'), left:0, behavior: 'instant'});
		}
	},
	prepareCompactView: function() {
		if ($('.af-compact-overlay').hasClass('ready')) {
			return;
		}
		$('.compact-toggle, .af-compact-overlay').on('click', function(e) {
			e.preventDefault();
			af.toggleSidePanel();
		}).addClass('ready');
		if ('ontouchstart' in document.documentElement) {
			var swipeThreshold = 150, xStart = 0, xEnd = 0,
				compactLeft = af.$filterBlock.hasClass('compact-offset-left');
			af.$filterBlock.on('touchstart', function (e) {
				xStart = e.originalEvent.touches[0].clientX;
				xEnd = xStart;
			}).on('touchmove',function (e) {
				xEnd = e.originalEvent.touches[0].clientX;
			}).on('touchend',function (e) {
				var diff = compactLeft ? xStart - xEnd : xEnd - xStart;
				if (diff > swipeThreshold && // detect swipe towards edge
					!$(e.target).closest('.af_filter').not('.closed').hasClass('has-slider')) { // no swiping on sliders
					$('body').removeClass('show-filter');
				} // TODO: else detect swipe from edge to open filter panel
			});
		}
	},
	toggleCompactView: function() {
		var isCompactBefore = af.isCompact;
		af.isCompact = af.$filterBlock.css('position') == 'fixed';
		af.autoScroll = af.isCompact ? 1 : parseInt($('#af_autoscroll').val());
		if (isCompactBefore != af.isCompact) {
			$('body').toggleClass('has-compact-filter', af.isCompact);
			var $afBlocks = af.$filterBlock.find('.af_filter'), $btnHolder = af.$filterBlock.find('.btn-holder');
			if (af.isCompact) {
				af.prepareCompactView();
				$afBlocks.filter('.closed').addClass('cl-orig');
				$afBlocks.filter('.foldered').addClass('fd-orig');
				$afBlocks.addClass('closed').filter('.folderable').addClass('foldered');
				$afBlocks.not('.no-available-items').first().removeClass('closed');
				af.$filterBlock.removeClass('horizontal-layout').before('<span class="af-orig hidden"></span>').appendTo('body');
				$('.af-compact-overlay').appendTo('body');
				$btnHolder.appendTo(af.$filterBlock); // avoid position:absolute + -webkit-overflow-scrolling: touch;
				setTimeout(function() {
					af.$filterBlock.addClass('animation-ready');
					if (typeof accordion == 'function') {af.$filterBlock.find('.block_content').stop().attr('style','');}
				}, 500);
				af.moreFilters.cancel();
			} else {
				af.$filterBlock.removeClass('animation-ready').toggleClass('horizontal-layout', af.isHorizontal);
				$('.af-orig').before(af.$filterBlock).before($('.af-compact-overlay')).remove();
				$btnHolder.insertAfter('#af_form'); // move it to original position for compatibility with accordion()
				$afBlocks.each(function() {
					$(this).toggleClass('closed', $(this).hasClass('cl-orig'))
					.toggleClass('foldered', $(this).hasClass('fd-orig'));
				});
				af.moreFilters.update();
			}
			af.cutOff.prepare($afBlocks);
			af.emit('toggleCompactView');
		}
		af.updateSFPositionIfRequired();
		af.toggleViewBtn();
	},
	toggleViewBtn: function() {
		var showBtn = $('#af_reload_action').val() == 2 || af.isCompact;
		af.$viewBtn.toggleClass('hidden', !showBtn).data('active', showBtn)
		.parent().toggleClass('hidden', !showBtn && !$('.manage-permanent-filters').length);
	},
	setWrapper: function() {
		if (!af.$listWrapper.length) {
			$(af_product_list_selector).first().wrap('<div class="af_pl_wrapper clearfix"></div>');
			af.$listWrapper = $('.af_pl_wrapper');
		}
	},
	updateSFPositionIfRequired: function() {
		if ($('#af_sf_position').val() == 1) {
			if (!af.isCompact) {
				var $before = af_is_modern ? $('#products').first() : $('.content_sortPagiBar').first();
				af.$selectedFilters.addClass('inline af').insertBefore($before);
			} else {
				af.$selectedFilters.removeClass('inline af').insertBefore('#af_form');
			}
		} else if (af.isHorizontal) {
			af.$selectedFilters.toggleClass('inline af', !af.isCompact);
		}
	},
	applyFilters: function(trigger, updateList) {
		if (af.$listWrapper.length && (updateList || af.$viewBtn.data('active'))) {
			af.updateSelectedFilters();
		}
		af.loadProducts(trigger, updateList);
	},
	prepareParamsForAjaxRequest: function(trigger, updateList) {
		if (updateList && !$('.dynamic-loading').hasClass('loading')) {
			af.$listWrapper.stop().animate({'opacity': 0.3}, 350);
		}
		$('.af-select').each(function() {
			if (!$(this).val()) {
				if (!$(this).hasClass('no-submit')) {
					$(this).addClass('no-submit').data('inputname', $(this).attr('name')).
					attr('name', 'no_submit'); // do not submit empty value from select
				}
			} else if ($(this).hasClass('no-submit')) {
				$(this).removeClass('no-submit').attr('name', $(this).data('inputname'));
			}
			if (af.dimZeroMatches) {
				// submit values from selected options without matches
				$(this).find('.no-matches:selected:disabled').prop('disabled', false);
			}
		});
		var params = $('.af-form').serialize();
		if (!updateList) {
			params += '&nb_items=0';
		} else if (!af.$listWrapper.length) {
			if (!af_is_modern && page_name != 'index') {
				af.updateSelectedFilters();
				af.updateURL(af.prepareUrlAndVerifyParams());
				window.location.reload();
				return;
			}
			params += '&layout_required=1';
			af.$dynamicContainer.animate({'opacity': 0.3}, 350);
		}
		if (load_more) {
			var $prevLoader = $('.dynamic-loading.prev');
			if (trigger == 'af_page') {
				if (!$prevLoader.length) {
					params += '&page_from=1';
				} else if ($prevLoader.hasClass('loading')) {
					params += '&page='+$('.loadMore.prev').data('p')+'&page_to='+af.$pageInput.val();
				} else {
					params += '&page_from='+$('.loadMore.prev').data('p');
				}
			} else {
				$prevLoader.remove();
			}
		}
		return params;
	},
	loadProducts: function(trigger, updateList) {
		if (af.blockAjax) {
			return;
		}
		var params = af.prepareParamsForAjaxRequest(trigger, updateList);
			requestIdentifier = ++af.reqNum;
		af.newURL = af.prepareUrlAndVerifyParams();
		$.ajax({
			type: 'POST',
			url: af_ajax.path,
			dataType : 'json',
			data: {
				action: 'getFilteredProducts',
				params: params,
				current_url: af.newURL,
				trigger: trigger,
				token: af_ajax.token,
			},
			success: function(r) {
				if (requestIdentifier != af.reqNum) {
					return; // update layout only for last request if checkboxes are clicked too fast
				}
				af.updateContent(r, trigger, updateList);
				af.updateURL(af.newURL);
				if (af.doAfterAjaxOnce.length > 0) {
					af.doAfterAjaxOnce.forEach(function(f) {f();});
					af.doAfterAjaxOnce = [];
				}
			},
			error: function(r) {
				console.warn($(r.responseText).text() || r.responseText);
			}
		});
	},
	updateContent: function(jsonData, trigger, updateList) {
		// var af_timeStart = new Date().getTime()/1000;
		if (updateList) {
			if ('layout' in jsonData) {
				af.updateListLayout(jsonData.layout);
				af.newURL = af.prepareUrlAndVerifyParams();
			}
			af.updateProductList(jsonData, trigger);
			if (af.autoScroll && (!load_more || trigger != 'af_page')) {
				af.autoscrollToTopOfTheList();
			}
		}
		if (trigger != 'af_page') {
			$('.af-total-count').html(parseInt(jsonData.products_num));
			af.updateFilteringBlocks(jsonData);
		}
		if (af.$viewBtn.data('active')) {
			af.$viewBtn.removeClass('loading');
			if (af.$viewBtn.data('animate-after-loading')) {
				af.$viewBtn.addClass('btn-pulsate').data('animate-after-loading', 0);
				setTimeout(function() {
					af.$viewBtn.removeClass('btn-pulsate');
				}, 300);
			}
		}
		// var af_timeEnd = new Date().getTime()/1000;
		// af_timeEnd = af_timeEnd - af_timeStart
		// console.dir('all elements updated: '+af_timeEnd);
	},
	updateProductList: function(jsonData, trigger) {
		if (load_more && trigger == 'af_page') {
			var $result = $('<div>'+jsonData.product_list_html+'</div>'),
				$items = $result.find(af.productItemSelector);
			if ($('.dynamic-loading.prev').hasClass('loading')) {
				af.$listWrapper.find(af.productItemSelector).first().before($items);
			} else {
				af.$listWrapper.find(af.productItemSelector).last().after($items);
			}
			$('.dynamic-loading.loading').removeClass('loading').find('.loadMore').trigger('blur');
			if (af_is_modern) {
				$('#js-product-list-top').replaceWith(jsonData.product_list_top_html);
			}
		} else {
			af.$listWrapper.html(jsonData.product_list_html);
			if (af_is_modern) {
				$('#js-product-list-top').replaceWith(jsonData.product_list_top_html);
				$('#js-product-list-bottom').replaceWith(jsonData.product_list_bottom_html);
			} else {
				$('.'+af_classes['product-count']).remove();
				$('#'+pagination_holder_id).replaceWith(jsonData.pagination_html);
				$('#'+pagination_bottom_holder_id).replaceWith(jsonData.pagination_bottom_html);
			}
		}
		if (load_more) {
			var $countContainer = af_is_modern ? $('.dynamic-product-count') : $('.'+af_classes['product-count']);
			$countContainer.html(jsonData.product_count_text);
			$('.loadMore.next').toggleClass('hidden', jsonData.hide_load_more_btn);
		}
		var animationTime = af_is_modern ? 500 : 1000;
		af.$listWrapper.animate({'opacity': 1}, animationTime);
		af.updateListAfter(jsonData);
	},
	updateListAfter: function(jsonData) {
		if (typeof display == 'function' && $.totalStorage
			&& $.totalStorage('display') && $.totalStorage('display') != 'grid') {
			display($.totalStorage('display'));
		}
		// prestashop.emit('updateProductList'); //todo: add configurable action
		customThemeActions.updateContentAfter(jsonData);
		if (typeof updateContentAfter == 'function') updateContentAfter(jsonData); // retro compatibility
		af.$filterBlock.trigger('updateProductList', jsonData);
	},
	updateFilteringBlocks: function(jsonData) {
		if ($.isEmptyObject(jsonData.count_data)) {
			return;
		}
		$('.af_filter').each(function() {
			let key = $(this).data('key'),
				sortByMatches = $(this).hasClass('sort-by-matches') && !$(this).hasClass('has-selection');
			if ($.inArray($(this).data('type'), [1,2,5]) > -1) { // checkbox, radio, textbox
				$(this).find('input.af').each(function() {
					var count = jsonData.count_data[key][$(this).val()] || 0,
						$li = $(this).closest('li');
					if (count) {
						$li.removeClass('no-matches');
					} else if (!$li.hasClass('no-matches')) {
						$li.addClass('no-matches');
					}
					if (af.showCounters) {
						$li.find('.count').first().html(count);
					};
					if (sortByMatches) {
						$li.data('count', count);
						if (!$li.next().length) {
							af.sortByMatches($li.parent(), 'li');
						}
					}
				});
				af.checkAvailableItems($(this), false);
			} else if ($(this).data('type') == 3) { // selects
				var $select = $(this).find('.af-select'),
					currentValue = $select.val(),
					html = '';
				$(this).find('.dynamic-select-options').children().each(function() {
					var data = $(this).data(),
						count = jsonData.count_data[key][data.value] || 0;
					if (count || data.value == currentValue || $(this).hasClass('customer-filter') || !af.hideZeroMatches) {
						html += '<option id="'+data.id+'" value="'+data.value+'"';
						html += ' class="'+$(this).attr('class')+(count ? '' : ' no-matches')+'"';
						html += ' data-url="'+data.url+'" data-text="'+data.text+'"';
						if (sortByMatches) {
							html += ' data-count="'+count+'"';
						}
						html += (af.dimZeroMatches && !count ? ' disabled' : '')+'>';
						html += data.prefix+data.text+(af.showCounters ? ' ('+count+')': '')+'</option>';
					}
				});
				$select.children().first().nextAll().remove(); // keep only first option
				if (html) {
					$select.append(html).val(currentValue);
					if (sortByMatches) {
						af.sortByMatches($select, 'option:not(.first)');
					}
					if (af.useUniform) {
						$select.uniform();
					};
				}
				af.checkAvailableItems($(this), true);
			}
		});
		af.cutOff.prepare();
		if (!af.isCompact) {
			af.moreFilters.update();
		}
		af.adjustFolderedStructure();
		if (jsonData.dynamic_min_max && af.slider) {
			$.each(jsonData.dynamic_min_max, function(key, data) {
				if ('min' in data && 'max' in data && af.slider.$[key]) {
					af.slider.updMinMax(af.slider.$[key], data.min, data.max);
					$('.af_filter.'+key).toggleClass(af.noItemsClass, data.max == 0);
				}
			});
		}
	},
	sortByMatches: function($parent, itemSelector) {
		$parent.children(itemSelector).sort(function(a, b) {
			return $(b).data('count') - $(a).data('count');
		}).appendTo($parent);
	},
	prepareUrlAndVerifyParams: function(baseURL) {
		var url = baseURL || af.getStaticURL(),
			dynamicParams = af.prepareDynamicParams();
		if (!$.isEmptyObject(dynamicParams)) {
			url += (url.indexOf('?') > -1 ? '&' : '?')+decodeURIComponent($.param(dynamicParams, true));
		}
		return url;
	},
	updateURL: function(url) {
		if (url && url != window.location.href) {
			window.history.pushState(null, null, url);
			af.popstateURL = url;
		}
	},
	getStaticURL: function() {
		if (!af.staticURL) {
			var splittedUrl = decodeURIComponent(window.location.href).split('?');
			af.staticURL = af.removeHash(splittedUrl[0]);
			if (splittedUrl.length == 2) {
				var staticParams = af.unserialize(splittedUrl[1], true);
				if (!$.isEmptyObject(staticParams)) {
					af.staticURL += '?'+decodeURIComponent($.param(staticParams, true));
				}
			}
		}
		return af.staticURL;
	},
	prepareDynamicParams: function() {
		var dynamicParams = {}, page = af.$pageInput.val();
		if (af.dynamicParams.f) {
			af.$selectedFilters.find('.cf').add('.sp-dynamic-params').each(function() {
				let n = $(this).data('group'), v = $(this).data('url');
				if (n && v) {
					if (n in dynamicParams) {
						dynamicParams[n] += ','+v;
					} else {
						dynamicParams[n] = v;
					}
				}
			});
		}
		if (af.dynamicParams.p && page > 1) {
			dynamicParams[af_param_names.p] = page;
		}
		if (af.dynamicParams.s) {
			var order = {by: $('#af_orderBy').val(), way: $('#af_orderWay').val()};
			if (order.by+'.'+order.way != $('#af_default_sorting').val()) {
				if (af_is_modern) {
					dynamicParams.order = 'product.'+order.by+'.'+order.way;
				} else {
					dynamicParams.orderby = order.by
					dynamicParams.orderway = order.way
				}
			}
		}
		if (af.nbItems.current != af.nbItems.orig) {
			dynamicParams[af_param_names.n] = af.nbItems.current;
		}
		af.emit('prepareDynamicParams', dynamicParams);
		return dynamicParams;
	},
	autoscrollToTopOfTheList: function() {
		if (af.$listWrapper.length) {
			var wrapperTop = af.$listWrapper.offset().top;
			if (af.isCompact || !af.isInViewPort(wrapperTop, wrapperTop)) {
				$('html, body').animate({scrollTop: wrapperTop - 150}, 100);
			}
		}
	},
	isInViewPort: function (top, bottom) {
		var scrollTop = af.$w.scrollTop(), windowHeight = af.$w.height();
		return (scrollTop + windowHeight) > top && scrollTop < bottom;
	},
	checkAvailableItems: function($block, isSelect) {
		if (af.dimZeroMatches || af.hideZeroMatches) {
			var noItems = !isSelect ? $block.find('li').not('.no-matches:not(.active)').length < 1
				: $block.find('option').not('.no-matches:not(:selected), .first').length < 1;
			$block.toggleClass(af.noItemsClass, noItems);
		}
	},
	cutOff: {
		prepare: function($blocks) {
			$blocks = $blocks ? $blocks : $('.af_filter');
			$blocks.not('.closed').find('.toggle-cut-off').each(function() {
				var $filter = $(this).closest('.af_filter');
				af.cutOff.apply($filter, $(this).data('cut'));
				if (!$(this).data('click-ready')) {
					$(this).on('click', function(e) {
						e.preventDefault();
						$filter.toggleClass('cut-off');
					}).data('click-ready', 1);
				}
			});
		},
		apply: function($filter, aboveCut) {
			if (!$filter.hasClass('cut-off')) {
				return;
			}
			let except = '.qs-hidden'+(af.hideZeroMatches ? ', .no-matches' : '');
			$filter.find('ul').first().children('li').not(except).each(function() {
				if (--aboveCut >= 0) {
					$(this).removeClass('cut');
				}  else if (!$(this).hasClass('cut')) {
					$(this).addClass('cut');
				}
			});
			$filter.toggleClass('expandable', aboveCut < 0);
		},
		cancel: function($filter) {
			$filter.find('.cut').removeClass('cut');
		},
	},
	moreFilters: {
		init: function() {
			af.moreFilters.$btn = af.$filterBlock.find('.toggleMoreFilters').on('click', function() {
				$('.af-form').toggleClass('show-more-f');
			});
		},
		update: function() {
			if (af.moreFilters.$btn.length) {
				let $blocks = af.$filterBlock.find('.af_filter');
				if (af.hideZeroMatches) {
					$blocks = $blocks.not('.no-available-items');
				}
				$blocks.each(function(i) {
					if (i < af.moreFilters.$btn.data('num')) {
						$(this).removeClass('more-f');
					}  else if (!$(this).hasClass('more-f')) {
						$(this).addClass('more-f');
					}
				});
				af.moreFilters.$btn.parent().toggleClass('hidden', !$blocks.filter('.more-f').length);
			}
		},
		cancel: function() {
			af.$filterBlock.find('.af_filter').removeClass('more-f');
			af.moreFilters.$btn.parent().addClass('hidden');
		}
	},
	adjustFolderedStructure: function() {
		$('.af_filter.foldered').find('.af-parent-category').each(function() {
			var $children = $(this).children('ul').find('li'),
				childrenWithMatches = $children.not('.no-matches').length,
				checkedChildren = $children.filter('.active').length;
			if (af.hideZeroMatches) {
				// hide foldered trigger if none of subcategories are available
				$(this).children('label').find('.af-toggle-child').toggleClass('hidden', !childrenWithMatches && !checkedChildren);
			}
			if (checkedChildren || (childrenWithMatches && $(this).hasClass('no-matches'))) {
				$(this).addClass('open');
				if (af.hideZeroMatches || af.dimZeroMatches) {
					$(this).removeClass('no-matches');
				}
			}
		});
	},
	updateListLayout: function(layout_html) {
		if ($.contains(af.$dynamicContainer[0], af.$filterBlock[0])) {
			af.$filterBlock.insertBefore(af.$dynamicContainer);
			$('.af.dynamic-loading').insertAfter(af.$filterBlock);
		}
		af.$dynamicContainer.html(layout_html).animate({'opacity': 1}, 350);
		af.setWrapper();
		af.updateSFPositionIfRequired();
		af.updateSelectedFilters();
		af.prepareLoadMoreIfRequired();
	},
	updateSelectedFilters: function() {
		let html = '';
		$('.af_filter').each(function() {
			let groupURL = $(this).data('url'),
				groupText = af.includeGroup && !$(this).hasClass('special') ? $(this).find('.af_subtitle').text()+': ' : '',
				hasSelection = $(this).find('.customer-filter-label').not('.unlocked').length > 0;
			if ($(this).hasClass('has-slider')) {
				let type = $(this).find('.af-slider').data('type'),
					$bar = af.slider.$[type];
				if ($bar.values.from > $bar.values.min || $bar.values.to < $bar.values.max) {
					let nt = af.slider.selectedRangeNotation($bar);
					html += af.renderSelectedOption(nt.url, groupURL, groupText+nt.txt, type);
					hasSelection = true;
				}
			} else {
				$(this).find('input, option:not(.first, .customer-filter)').filter(':checked, :selected').each(function() {
					let text = groupText+($(this).data('text') || $(this).closest('label').find('.name').text());
					html += af.renderSelectedOption($(this).data('url'), groupURL, text, $(this).attr('id'));
					hasSelection = true;
				});
			}
			$(this).toggleClass('has-selection', hasSelection);
		});
		af.$selectedFilters.find('.cf').remove();
		af.$selectedFilters.append(html).toggleClass('hidden', !html);
	},
	renderSelectedOption: function(url, group, text, id) {
		return '<div class="cf" data-url="'+url+'" data-group="'+group+'" data-id="'+id+'">'
			+text+' <a href="#" class="'+af_classes['u-times']+' close"></a></div>';
	},
	infScroll: {
		threshold: [70, 700],
		init: function() {
			this.$dl = $('.dynamic-loading.next');
			this.$btn = $('.loadMore.next');
			this.bind();
		},
		bind: function() {
			this.viewPortTop = af.$w.scrollTop();
			af.$w.on('scroll.afis', function() {
				clearTimeout(af.infScroll.timer);
				af.infScroll.timer = setTimeout(function() {
					if (af.infScroll.isScrollingDown() && !af.infScroll.$dl.hasClass('loading') &&
						!af.infScroll.$btn.hasClass('hidden')) {
						let offset = parseInt(af.infScroll.$dl.offset().top);
							thresholdOffset = {
								top: af.infScroll.viewPortTop + af.infScroll.threshold[0],
								bottom: af.infScroll.viewPortTop + af.$w.height() + af.infScroll.threshold[1],
							};
						if (offset < thresholdOffset.bottom) {
							offset > thresholdOffset.top ? af.infScroll.$btn.click() : af.infScroll.unbind(true);
						}
					}
				}, 50);
			});
		},
		unbind: function(showBtnOnce) {
			af.$w.off('scroll.afis');
			if (showBtnOnce) {
				this.$dl.removeClass('infinite-scroll');
				af.doAfterAjaxOnce.push(function() {
					af.infScroll.$dl.addClass('infinite-scroll');
					af.infScroll.bind();
				});
			}
		},
		isScrollingDown: function() {
			let prevTop = this.viewPortTop;
			this.viewPortTop = af.$w.scrollTop();
			return this.viewPortTop > prevTop;
		},
	},
	unserialize: function(params, excludeDynamicParams) {
		params = params.split('&');
		var result = {},
			dynamicParams = {order:0, orderby:0, orderway:0};
		dynamicParams[af_param_names.p] = 0;
		dynamicParams[af_param_names.n] = 0;
		for (var i in params) {
			if (params.hasOwnProperty(i)) { // make sure params[i] does not come from Array.prototype
				var splitted = params[i].split('='), name = splitted[0];
				if (splitted.length == 2 && (!excludeDynamicParams ||
					!$('.af_filter[data-url="'+name+'"]').length && !(name in dynamicParams))) {
					result[name] = splitted[1];
				}
			}
		}
		return result;
	},
	updateFiltersBasingOnURL: function(url) {
		window.location.reload();
		// TODO: dynamically update filters and product list basing on URL, or may be other global variable
		// var splittedUrl = decodeURIComponent(url).split('?');
		// if (splittedUrl.length == 2) {
		// 	$.each(af.unserialize(splittedUrl[1]), function(name, val) {
		// 		if ($('.af_filter[data-url="'+name+'"]').length) {
		// 			console.dir(val);
		// 		}
		// 	});

		// }
	},
	removeHash: function(url) {
		return url.split('#')[0];
	},
	externalEvents: {},
	emit: function(key, arg) {
		if (af.externalEvents[key]) {
			af.externalEvents[key].forEach(function(callback) {
				callback(arg);
			});
		}
	},
	on: function(key, callback) {
		af.externalEvents[key] = af.externalEvents[key] || [];
		af.externalEvents[key].push(callback);
	},
};

$(document).ready(function() {
	af.documentReady();
});
/* since 3.3.0 */

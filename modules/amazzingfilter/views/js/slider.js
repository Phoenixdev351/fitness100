/**
*  @author    Amazzing
*  @copyright Amazzing
*  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

$.extend(af, {
	slider: {
		$: {},
		precision: 1000000,
		prepareAll: function() {
			$('.af-slider').each(function() {
				af.slider.init($(this).find('.slider-bar'), $(this).data('type'));
			}).find('.slider_value').on('click', function() {
				if (!$(this).hasClass('edit')) {
					var mw = $(this).width()+'px',
						mh = $(this).height()+'px';
					$(this).addClass('edit').find('.input-text').css({'max-width': mw, 'max-height': mh}).trigger('focus');
				}
			}).find('.input-text').on('focusin', function() {
				$(this).data('val', $(this).val()).val('');
			}).on('focusout', function() {
				$(this).css({'max-width': '', 'max-height': ''}).closest('.slider_value').removeClass('edit');
				if ($(this).val() == '') {
					$(this).val($(this).data('val'));
				}
			}).on('change', function() {
				$(this).val(af.slider.unformatNumber($(this).val()));
				if ($(this).is(':focus')) {
					$(this).trigger('blur');
				}
				af.slider.setValues(af.slider.$[$(this).closest('.af-slider').data('type')]);
			}).on('keydown', function(e) {
				let allowed = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '-', af_sep.all.dec, af_sep.all.tho,
					'Delete', 'Backspace', 'Tab', 'Escape', 'Enter', 'End', 'Home', 'ArrowLeft', 'ArrowRight'];
				if (allowed.indexOf(e.key) == -1) {
					e.preventDefault();
				}
			});
		},
		init: function($bar, type) {
			$bar.html(this.renderDynamicElements());
			$.extend($bar, {
				step: af.slider.reduceDecimals($bar.data('step')),
				stepDecimals: af.slider.getDecimalsNum($bar.data('step')),
				values: {},
				$selectedBar: $bar.find('.selected-bar'),
				$inputs: {
					min  : $('#'+type+'_min'),
					max  : $('#'+type+'_max'),
					from : $('#'+type+'_from'),
					to   : $('#'+type+'_to'),
				},
				$displayValues: {
					from: $('.'+type+'_slider .from_display span.value'),
					to: $('.'+type+'_slider .to_display span.value'),
				}
			});
			this.preparePointers($bar);
			this.setValues($bar);
			this.attachAdditionalEvents($bar);
			this.$[type] = $bar;
		},
		preparePointers: function($bar) {
			$bar.$pointers = {
				from : $bar.find('.pointer.from').data('key', 'from').data('prc', 0),
				to   : $bar.find('.pointer.to').data('key', 'to').data('prc', 100),
			};
			$bar.pointerHalfWidth = $bar.$pointers.from.width() / 2;
			$.each($bar.$pointers, function(i, $pointer) {
				$pointer.on('mousedown touchstart', function(e) {
					if (e.type === 'mousedown' && e.which !== 1) {
						return;
					}
					e.stopPropagation();
					e.preventDefault();
					$bar.find('.pointer').removeClass('focused last-active');
					$pointer = $(e.target).addClass('focused last-active');
					$bar.offsetLeftPx = $bar.offset().left + $bar.pointerHalfWidth;
					$bar.widthPx = $bar.width();
					$(document).on('mousemove.afslider touchmove.afslider', function(e) {
						if (e.originalEvent.touches && e.originalEvent.touches.length) {
							e = e.originalEvent.touches[0];
						} else if (e.originalEvent.changedTouches && e.originalEvent.changedTouches.length) {
							e = e.originalEvent.changedTouches[0];
						}
						var percentage = af.slider.valueToPrc(e.clientX, $bar.offsetLeftPx, $bar.widthPx);
						af.slider.setPointerPosition($pointer.data('val', null), $bar, percentage, true, false);
					}).on('mouseup.afslider touchend.afslider touchcancel.afslider', function(e) {
						$(document).off('.afslider');
						af.slider.triggerChage($bar);
						$bar.find('.pointer').removeClass('focused');
					});
				});
			});
		},
		triggerChage: function($bar) {
			$bar.$inputs.max.trigger('change');
		},
		setValues: function($bar) {
			$.each($bar.$inputs, function(name, $el) {
				let value = af.slider.swapToStep(af.slider.reduceDecimals($el.val()), $bar.step, name);
				if (name == 'from' || name == 'to') { // min/max already iterated
					value = af.slider.withinRange(value, $bar.values.min, $bar.values.max);
					$bar.$pointers[name].data('val', value);
				}
				$bar.values[name] = value;
			});
			$bar.interval = $bar.values.max - $bar.values.min;
			var prc = {from: 0, to: 100};
			if (!$bar.interval) {
				$bar.addClass('blocked').data('custom-step', 0);
			} else {
				$bar.removeClass('blocked').data('custom-step', 1);
				prc.from = af.slider.valueToPrc($bar.values.from, $bar.values.min, $bar.interval);
				prc.to = af.slider.valueToPrc($bar.values.to, $bar.values.min, $bar.interval);
			}
			af.slider.setPointerPosition($bar.$pointers.from, $bar, prc.from, false, false);
			af.slider.setPointerPosition($bar.$pointers.to, $bar, prc.to, false, false);
		},
		resetValues: function($bar) {
			$bar.$inputs.from.val($bar.$inputs.min.val());
			$bar.$inputs.to.val($bar.$inputs.max.val()).trigger('change');
		},
		updMinMax: function($bar, min, max) {
			$bar.$inputs.min.val(min);
			$bar.$inputs.from.val(min);
			$bar.$inputs.to.val(max);
			$bar.$inputs.max.val(max);
			this.setValues($bar);
		},
		setPointerPosition: function($pointer, $bar, percentage, swapToStep, animate) {
			var prc = {from: $bar.$pointers.from.data('prc'), to: $bar.$pointers.to.data('prc')},
				applyStyles = animate ? 'animate' : 'css';
			if ($pointer.data('key') == 'from') {
				prc.from = percentage = Math.min(percentage, prc.to);
			} else {
				prc.to = percentage = Math.max(percentage, prc.from);
			}
			$pointer.data('prc', percentage)[applyStyles]({left: percentage+'%'});
			$bar.$selectedBar[applyStyles]({left: prc.from+'%', width: (prc.to - prc.from)+'%'});
			this.updatePointerValue($pointer, $bar, swapToStep);
		},
		updatePointerValue: function($pointer, $bar, swapToStep) {
			let value = $pointer.data('val');
			if (typeof value != 'number') {
				value = this.prcToValue($pointer.data('prc'), $bar);
				if (swapToStep && $bar.data('custom-step')) {
					value = this.swapToStep(value, $bar.step, $pointer.data('key'));
				}
				$bar.values[$pointer.data('key')] = value;
			}
			let realValue = this.round(this.restoreDecimals(value), $bar.stepDecimals, $pointer.data('key') == 'to');
			$bar.$inputs[$pointer.data('key')].val(realValue);
			$bar.$displayValues[$pointer.data('key')].text(this.formatNumber(realValue));
		},
		swapToStep: function(value, step, key) {
			let stepDiff = value % step;
			if (stepDiff) {
				value -= stepDiff;
				if (key == 'to' || key == 'max') {
					value += step;
				}
			}
			return value;
		},
		round: function(value, decimalsNum, up) {
			var method = up ? 'ceil' : 'floor';
			return Math[method](value * Math.pow(10, decimalsNum)) / Math.pow(10, decimalsNum);
		},
		valueToPrc: function(value, zeroValue, interval) {
			return !interval ? 100 : af.slider.withinRange((value - zeroValue) / interval * 100, 0, 100);
		},
		prcToValue: function(percentage, $bar) {
			return $bar.values.min + ($bar.interval * percentage / 100);
		},
		reduceDecimals: function(number) { // avoid decimal rounding issues in JS
			return number * this.precision;
		},
		restoreDecimals: function(number) {
			return number / this.precision;
		},
		formatNumber: function(number, sep) {
			sep = sep || af_sep.all;
			if (sep.tho || sep.dec != '.') {
				number = number.toString().split('.');
				if (sep.tho) {
					number[0] = number[0].replace(/\B(?=(\d{3})+(?!\d))/g, sep.tho);
				}
				number = number.join(sep.dec);
			}
			return number;
		},
		unformatNumber: function(number, sep) {
			sep = sep || af_sep.all;
			if (sep.tho) {
				number = number.split(sep.tho).join('');
			}
			if (sep.dec != '.') {
				number = number.split(sep.dec).join('.');
			}
			return parseFloat(number) || 0;
		},
		withinRange: function(number, min, max) {
			return Math.min(Math.max(number, min), max);
		},
		getDecimalsNum: function(n) {
			var num = 0;
			if (n % 1) {
				n = parseFloat(n).toString();
				if (n.indexOf('.') > -1) {
					num = n.split('.').pop().length;
				} else if (n.indexOf('-') > -1) {
					num = n.split('-').pop();
				}
			}
			return num;
		},
		attachAdditionalEvents: function($bar) {
			$bar.find('.clickable-dummy').on('click', function(e) {
				var percentage = af.slider.valueToPrc(e.clientX, $bar.offset().left + $bar.pointerHalfWidth, $bar.width()),
					fromDiff = Math.abs(percentage - $bar.$pointers.from.data('prc')),
					toDiff = Math.abs(percentage - $bar.$pointers.to.data('prc')),
					closestPointerKey = fromDiff < toDiff ? 'from' : 'to',
					$pointer = $bar.$pointers[closestPointerKey].data('val', null);
				af.slider.setPointerPosition($pointer, $bar, percentage, true, true);
				af.slider.triggerChage($bar);
			});
		},
		selectedRangeNotation: function($bar) {
			let $parent = $bar.closest('.af-slider');
			return {
				url: this.formatNumber($bar.$inputs.from.val(), af_sep.url)
					+af_sep.url.range+this.formatNumber($bar.$inputs.to.val(), af_sep.url),
				txt: $parent.find('.prefix').first().text()+$bar.$displayValues.from.text()
					+af_sep.all.range+$bar.$displayValues.to.text()+$parent.find('.suffix').first().text(),
			};
		},
		renderDynamicElements: function() {
			return '<div class="selected-bar"></div><div class="pointer from"></div>\
				<div class="pointer to"></div><div class="clickable-dummy"></div>';
		},
	},
});
af.on('documentReady', function() {
	af.slider.prepareAll();
});
/* since 3.2.8 */

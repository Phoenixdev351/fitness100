/**
*  @author    Amazzing <mail@mirindevo.com>
*  @copyright Amazzing
*  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

var cf = {
    glue: ',',
    init: function() {
        cf.tagify.init();
        $('.cf-group.trigger-info').find('select').on('change', function() {
            let $parent = $(this).closest('.cf-group');
            $parent.find('.field-info').toggleClass('hidden', !$parent.hasClass('show-on-'+$(this).val()));
        }).addClass('no-autosaving-once').trigger('change');
    },
    addSelectedFilters: function(keys) {
        var params = 'action=getCustomerFilters&keys='+keys.join(cf.glue),
            response = function(items) {
                cf.tagify.addItems(items);
                af.popup.close();
            };
        ajaxRequest(params, response);
    },
    tagify: {
        init: function() {
            cf.tagify.$parent = $('.cf-criteria');
            cf.tagify.$input = cf.tagify.$parent.find('.t-value');
            cf.tagify.bindEvents();
            let keys = cf.tagify.$input.val().split(cf.glue);
            if (keys.length) {
                cf.addSelectedFilters(keys);
            }
        },
        bindEvents: function() {
            cf.tagify.$parent.on('click', '.t-add', function() {
                af.showAvailableFilters('cf=1&blocked='+cf.tagify.$input.val());
            }).on('click', '.t-remove', function(e) {
                e.preventDefault();
                $(this).closest('.t-item').remove();
                cf.tagify.updateValue();
            }).find('.t-items').sortable({
                items: '.t-item',
                update: function() {
                    cf.tagify.updateValue();
                },
            });
        },
        addItems: function(items) {
            $.each(items, function(key, item) {
                cf.tagify.$parent.find('.quick-add').before(cf.tagify.renderItem(key, item));
            });
            cf.tagify.updateValue();
        },
        renderItem: function(key, item) {
            let html = '<span class="t-item type-'+key[0]+'" data-key="'+key+'">';
            if (item.id > 0) {
                html += '<span class="t-id"><span class="t-info">'+item.prefix+'</span>'+item.id+'</span>';
            }
            html += '<span class="t-name" data-name="'+item.name+'"';
            $.each(item.dynamic_name, function(id_lang, txt) {
                if (txt != '') {
                    html += ' data-dynamic-'+id_lang+'="'+txt+'"';
                }
            });
            html += '>'+item.name+'</span> <a href="#" class="t-remove">&times;</a></span>';
            return html;
        },
        updateValue: function() {
            let $tItems = cf.tagify.$parent.find('.t-item'),
                newValue = af.getMultipleData($tItems, 'key').join(cf.glue);
            if (cf.tagify.$input.val() != newValue) {
                cf.tagify.$input.val(newValue).change();
            }
            cf.dynamicFields.update($tItems);
        },
    },
    dynamicFields: {
        update: function($tItems) {
            $('.cf-dynamic-lang-field').remove();
            let $dummy = $('.cf-dummy'),
                labelPattern = $dummy.find('.cf-label').text();
            $tItems.each(function() {
                let $tName = $(this).find('.t-name'),
                    key = $(this).data('key');
                $dummy.clone().toggleClass('cf-dynamic-lang-field cf-dummy hidden')
                .find('.cf-label').html(labelPattern.replace('%s', $tName.data('name')))
                .end().find('input').each(function() {
                    let id_lang = $(this).parent().data('lang'),
                        value = $tName.data('dynamic-'+id_lang) || '';
                    $(this).val(value).attr('name', 'l_dynamic['+key+']['+id_lang+']');
                    cf.dynamicFields.sync($(this), $tName, id_lang);
                }).end().insertBefore($dummy);
            });
        },
        sync: function($input, $nameHolder, id_lang) {
            $input.on('keyup', function() {
                let value = $.trim($(this).val());
                $nameHolder.data('dynamic-'+id_lang, value);
                if (id_lang == af_id_lang) {
                    $nameHolder.html(value || $nameHolder.data('name'));
                }
            }).trigger('keyup');
        },
    },
};
$(document).ready(function() {
    cf.init();
});
/* since 3.2.5 */

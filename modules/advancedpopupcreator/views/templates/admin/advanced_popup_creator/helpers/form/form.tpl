{**
*
* NOTICE OF LICENSE
*
* This product is licensed for one customer to use on one installation (test stores and multishop included).
* Site developer has the right to modify this module to suit their needs, but can not redistribute the module in
* whole or in part. Any other use of this module constitutes a violation of the user agreement.
*
* DISCLAIMER
*
* NO WARRANTIES OF DATA SAFETY OR MODULE SECURITY
* ARE EXPRESSED OR IMPLIED. USE THIS MODULE IN ACCORDANCE
* WITH YOUR MERCHANT AGREEMENT, KNOWING THAT VIOLATIONS OF
* PCI COMPLIANCY OR A DATA BREACH CAN COST THOUSANDS OF DOLLARS
* IN FINES AND DAMAGE A STORES REPUTATION. USE AT YOUR OWN RISK.
*
*  @author    idnovate.com <info@idnovate.com>
*  @copyright 2020 idnovate.com
*  @license   See above
*}


{extends file="helpers/form/form.tpl"}
{block name="field"}
    {if $input.type == 'swap-custom'}
        <div class="col-lg-{if isset($input.col)}{$input.col|intval}{else}9{/if}{if !isset($input.label)} col-lg-offset-3{/if} bootstrap margin-group">
            <div class="form-group swap-container-custom row">
                <div class="col-lg-12">
                    <div class="form-control-static row">
                        <div class="col-xs-6">
                            {if isset($input.search)}
                                <div class="input-group">
                                    <span class="input-group-addon">{l s='Search' mod='advancedpopupcreator'}</span>
                                    <input type="text" class="search_select" id="{$input.name|escape:'html':'utf-8'}_available_search" autocomplete="off">
                                </div>
                            {/if}

                            <select {if isset($input.size)}size="{$input.size|escape:'html':'utf-8'}"{/if}{if isset($input.onchange)} onchange="{$input.onchange|escape:'html':'utf-8'}"{/if} class="{if isset($input.class)}{$input.class|escape:'html':'utf-8'}{/if} availableSwap" name="{$input.name|escape:'html':'utf-8'}_available[]" multiple="multiple">
                                {foreach $input.options.query AS $option}
                                    {if is_object($option)}
                                        {assign var=option value=$option->arrContent}
                                    {/if}

                                    {if !is_array($fields_value[$input.name]) || !in_array($option[$input.options.id], $fields_value[$input.name])}
                                        <option {if isset($input.sort) && isset($option[$input.sort])}data-sort="{$option[$input.sort|escape:'html':'utf-8']}"{/if} value="{$option[$input.options.id]}">{$option[$input.options.name|escape:'html':'utf-8']}</option>
                                    {elseif $option == "-"}
                                        <option value="">-</option>
                                    {/if}
                                {/foreach}
                            </select>
                            {if isset($input.search)}
                                <script type="text/javascript">
                                    $(document).ready(function() {
                                        $('[name="{$input.name|escape:'html':'utf-8'}_available[]"]').filterByText('#{$input.name|escape:'html':'utf-8'}_available_search', true);
                                    });
                                </script>
                            {/if}
                            <a href="#" class="btn btn-default btn-block addSwap">{l s='Add' d='Admin.Actions'} <i class="icon-arrow-right"></i></a>
                        </div>
                        <div class="col-xs-6">
                            {if isset($input.search)}
                                <div class="input-group">
                                    <span class="input-group-addon">{l s='Search' mod='advancedpopupcreator'}</span>
                                    <input type="text" class="search_select" id="{$input.name|escape:'html':'utf-8'}_selected_search" autocomplete="off">
                                </div>
                            {/if}
                            <select {if isset($input.size)}size="{$input.size|escape:'html':'utf-8'}"{/if}{if isset($input.onchange)} onchange="{$input.onchange|escape:'html':'utf-8'}"{/if} class="{if isset($input.class)}{$input.class|escape:'html':'utf-8'}{/if} selectedSwap" name="{$input.name|escape:'html':'utf-8'}_selected[]" multiple="multiple">
                                {foreach $input.options.query AS $option}
                                    {if is_object($option)}
                                        {assign var=option value=$option->arrContent}
                                    {/if}

                                    {if is_array($fields_value[$input.name]) && in_array($option[$input.options.id], $fields_value[$input.name])}
                                        <option {if isset($input.sort) && isset($option[$input.sort])}data-sort="{$option[$input.sort|escape:'html':'utf-8']}"{/if} value="{$option[$input.options.id]}">{$option[$input.options.name|escape:'html':'utf-8']}</option>
                                    {elseif $option == "-"}
                                        <option value="">-</option>
                                    {/if}
                                {/foreach}
                            </select>
                            {if isset($input.search)}
                                <script type="text/javascript">
                                    $(document).ready(function() {
                                        $('[name="{$input.name|escape:'html':'utf-8'}_selected[]"]').filterByText('#{$input.name|escape:'html':'utf-8'}_selected_search', true);
                                    });
                                </script>
                            {/if}
                            <a href="#" class="btn btn-default btn-block removeSwap"><i class="icon-arrow-left"></i> {l s='Remove' mod='advancedpopupcreator'}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {if version_compare($smarty.const._PS_VERSION_,'1.6','<')}
            <div class="clear">&nbsp;</div>
        {/if}
    {else}
        {$smarty.block.parent}
    {/if}
{/block}

{block name="after"}
    {if version_compare($smarty.const._PS_VERSION_,'1.6','<')}
        <style>
            .bootstrap .input-group {
                border-collapse: separate;
                display: table;
                position: relative
            }
            .bootstrap .input-group .form-control,
            .bootstrap .input-group input[type=password],
            .bootstrap .input-group input[type=search],
            .bootstrap .input-group input[type=text],
            .bootstrap .input-group select,
            .bootstrap .input-group textarea {
                float: left;
                margin-bottom: 0;
                position: relative;
                width: 100%;
                z-index: 2
            }
            .bootstrap .input-group-addon,
            .bootstrap .input-group-btn,
            .bootstrap .input-group .form-control,
            .bootstrap .input-group input[type=password],
            .bootstrap .input-group input[type=search],
            .bootstrap .input-group input[type=text],
            .bootstrap .input-group select,
            .bootstrap .input-group textarea {
                display: table-cell
            }
            .bootstrap .input-group-addon:not(:first-child):not(:last-child),
            .bootstrap .input-group-btn:not(:first-child):not(:last-child),
            .bootstrap .input-group .form-control:not(:first-child):not(:last-child),
            .bootstrap .input-group input:not(:first-child):not(:last-child)[type=password],
            .bootstrap .input-group input:not(:first-child):not(:last-child)[type=search],
            .bootstrap .input-group input:not(:first-child):not(:last-child)[type=text],
            .bootstrap .input-group select:not(:first-child):not(:last-child),
            .bootstrap .input-group textarea:not(:first-child):not(:last-child) {
                border-radius: 0
            }
            .bootstrap .input-group-addon {
                vertical-align: middle;
                white-space: nowrap;
                width: 1%
            }
            .bootstrap .input-group-addon {
                background-color: #f5f8f9;
                border: 1px solid #c7d6db;
                border-radius: 3px;
                color: #555;
                font-size: 12px;
                font-weight: 400;
                line-height: 1;
                padding: 6px 8px;
                text-align: center
            }

            .bootstrap .input-group-addon input[type=checkbox],
            .bootstrap .input-group-addon input[type=radio] {
                margin-top: 0
            }
            .bootstrap .input-group-addon:first-child,
            .bootstrap .input-group-btn:first-child > .btn,
            .bootstrap .input-group-btn:first-child > .btn-group > .btn,
            .bootstrap .input-group-btn:first-child > .dropdown-toggle,
            .bootstrap .input-group-btn:last-child > .btn-group:not(:last-child) > .btn,
            .bootstrap .input-group-btn:last-child > .btn:not(:last-child):not(.dropdown-toggle),
            .bootstrap .input-group .form-control:first-child,
            .bootstrap .input-group input:first-child[type=password],
            .bootstrap .input-group input:first-child[type=search],
            .bootstrap .input-group input:first-child[type=text],
            .bootstrap .input-group select:first-child,
            .bootstrap .input-group textarea:first-child {
                border-bottom-right-radius: 0;
                border-top-right-radius: 0
            }
            .bootstrap .input-group-addon:first-child {
                border-right: 0
            }
            .bootstrap .input-group-addon:last-child,
            .bootstrap .input-group-btn:first-child > .btn-group:not(:first-child) > .btn,
            .bootstrap .input-group-btn:first-child > .btn:not(:first-child),
            .bootstrap .input-group-btn:last-child > .btn,
            .bootstrap .input-group-btn:last-child > .btn-group > .btn,
            .bootstrap .input-group-btn:last-child > .dropdown-toggle,
            .bootstrap .input-group .form-control:last-child,
            .bootstrap .input-group input:last-child[type=password],
            .bootstrap .input-group input:last-child[type=search],
            .bootstrap .input-group input:last-child[type=text],
            .bootstrap .input-group select:last-child,
            .bootstrap .input-group textarea:last-child {
                border-bottom-left-radius: 0;
                border-top-left-radius: 0
            }
            .bootstrap .input-group-addon:last-child {
                border-left: 0
            }
            .bootstrap .input-group-btn {
                font-size: 0;
                position: relative;
                white-space: nowrap
            }
            .bootstrap .input-group-btn > .btn {
                position: relative
            }
            .bootstrap .input-group-btn > .btn + .btn {
                margin-left: -1px
            }
            .bootstrap .input-group-btn > .btn:active,
            .bootstrap .input-group-btn > .btn:focus,
            .bootstrap .input-group-btn > .btn:hover {
                z-index: 2
            }
            .bootstrap .input-group-btn:first-child > .btn,
            .bootstrap .input-group-btn:first-child > .btn-group {
                margin-right: -1px
            }
            .bootstrap .input-group-btn:last-child > .btn,
            .bootstrap .input-group-btn:last-child > .btn-group {
                margin-left: -1px
            }
            .bootstrap .btn-block {
                display: block;
                padding-left: 0;
                padding-right: 0;
                width: 100%;
            }
            .bootstrap .btn-default {
                background-color: #fff;
                border-color: #dedede;
                color: #363a41;
            }
            .bootstrap .btn {
                -moz-user-select: none;
                -ms-user-select: none;
                -webkit-user-select: none;
                background-image: none;
                border: 1px solid transparent;
                border-radius: 3px;
                cursor: pointer;
                display: inline-block;
                font-size: 12px;
                font-weight: 400;
                line-height: 1.42857;
                margin-bottom: 0;
                padding: 6px 8px;
                text-align: center;
                user-select: none;
                vertical-align: middle;
                white-space: nowrap;
            }

            .bootstrap select[multiple],
            .bootstrap select[size] {
                height: auto;
            }
            .bootstrap .form-control,
            .bootstrap input[type=password],
            .bootstrap input[type=search],
            .bootstrap input[type=text],
            .bootstrap select,
            .bootstrap textarea {
                -webkit-transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
                -webkit-transition: border-color .15s ease-in-out,-webkit-box-shadow .15s ease-in-out;
                background-color: #f5f8f9;
                background-image: none;
                border: 1px solid #c7d6db;
                border-radius: 3px;
                color: #555;
                display: block;
                font-size: 12px;
                height: 31px;
                line-height: 1.42857;
                padding: 6px 8px;
                transition: border-color .15s ease-in-out,-webkit-box-shadow .15s ease-in-out;
                transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
                transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out,-webkit-box-shadow .15s ease-in-out;
                width: 100%;
            }
        </style>
    {/if}
{/block}

{block name="script"}
    $(document).ready(function() {
        $('.swap-container-custom').each(function () {
            /** make sure that all the swap id is present in the dom to prevent mistake **/
            if (typeof $('.addSwap', this) !== undefined && typeof $(".removeSwap", this) !== undefined &&
                typeof $('.selectedSwap', this) !== undefined && typeof $('.availableSwap', this) !== undefined) {
                bindSwapButtonCustom('add', 'available', 'selected', this);
                bindSwapButtonCustom('remove', 'selected', 'available', this);
            }
        });
    });

    function bindSwapButtonCustom(prefix_button, prefix_select_remove, prefix_select_add, context) {
        $('.'+prefix_button+'Swap', context).on('click', function(e) {
            e.preventDefault();
            $('.' + prefix_select_remove + 'Swap option:selected', context).each(function() {
                var to = $('.' + prefix_select_add + 'Swap', context);
                var from = $('.' + prefix_select_remove + 'Swap', context);

                var selected = from.find('option:selected');
                var selectedVal = [];
                selected.each(function(){
                    selectedVal.push($(this).val());
                });

                var options = from.data('options');
                var tempOption = [];

                var targetOptions = to.data('options');

                $.each(options, function(i) {
                    var option = options[i];
                    if($.inArray(option.value, selectedVal) == -1) {
                        tempOption.push(option);
                    } else {
                        targetOptions.push(option);
                    }
                });

                to.find('option:selected').prop('selected', false);
                from.find('option:selected').remove().appendTo(to).prop('selected', true);

                to.data('options', targetOptions);
                from.data('options', tempOption);

                //Sort select
                to.html(to.find('option').sort(function(x, y) {
                    // to change to descending order switch "<" for ">"
                    if (isNaN($(x).data('sort')) || isNaN($(y).data('sort'))) {
                        return $(x).data('sort') > $(y).data('sort') ? 1 : -1;
                    } else {
                        return $(x).data('sort') - $(y).data('sort');
                    }
                }));

                //Update results if a search is typed in the fields
                if ($('.search_select').val()) {
                    $('.search_select', context).keyup();
                }
            });
        });
    }

    // http://www.lessanvaezi.com/filter-select-list-options/
    jQuery.fn.filterByText = function(textbox, selectSingleMatch) {
        return this.each(function() {
            var select = $(this);
            var options = [];
            select.find('option').each(function() {
                options.push({ value: $(this).val(), text: $(this).text()});
            });
            select.data('options', options);
            textbox = textbox.replace( /(:|\.|\[|\]|,|=|@)/g, "\\$1" );
            $(textbox).bind('keyup', function(e) {
                var options = select.empty().scrollTop(0).data('options');
                var search = $.trim($(this).val());
                var regex = new RegExp(search,'gi');

                var new_options_html = '';
                $.each(options, function(i, option) {
                    if(option.text.match(regex) !== null) {
                        new_options_html += '<option value="' + option.value + '">' + option.text + '</option>';
                    }
                });

                select.append(new_options_html);

                if (selectSingleMatch === true &&
                    select.children().length === 1) {
                    select.children().get(0).selected = true;
                } else if (select.children().length > 0) {
                    select.children().get(0).selected = false;
                }
            })
        })
    };

    $('form').submit(function() {
        //Remove all values from search fields, because if don't the hidden values are not set
        $('.search_select').each(function() {
            $(this).val('').trigger('keyup');
        });

        $('.availableSwap').each(function() {
            $(this).find('option').each(function() {
                $(this).prop('selected', false);
            });
        });

        $('.selectedSwap').each(function() {
            $(this).find('option').each(function() {
                $(this).prop('selected', true);
            });
        });
    });
{/block}

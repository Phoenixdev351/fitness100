{*
*  @author    Amazzing <mail@mirindevo.com>
*  @copyright Amazzing
*  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{if empty($input_class)}{$input_class = ''}{/if}
{if !empty($field.input_class)}{$input_class = trim($input_class|cat:' '|cat:$field.input_class)}{/if}

{if $field.type == 'switcher'}
    <select class="switch-select{if !empty($value)} yes{/if} {$input_class|escape:'html':'UTF-8'}" name="{$name|escape:'html':'UTF-8'}">
        {if !empty($field.switcher_options)}
            {foreach $field.switcher_options as $value => $label}
                <option value="{$value|escape:'html':'UTF-8'}"{if $field.value == $value} selected{/if}>{$label|escape:'html':'UTF-8'}</option>
            {/foreach}
        {else}
            <option value="0"{if empty($value)} selected{/if}>{l s='No' mod='amazzingfilter'}</option>
            <option value="1"{if !empty($value)} selected{/if}>{l s='Yes' mod='amazzingfilter'}</option>
        {/if}
    </select>
{else if $field.type == 'select'}
    <select class="{$input_class|escape:'html':'UTF-8'}" name="{$name|escape:'html':'UTF-8'}">
        {foreach $field.options as $i => $opt}
            <option value="{$i|escape:'html':'UTF-8'}"{if $value|cat:'' == $i} selected{/if}>{$opt|escape:'html':'UTF-8'}</option>
        {/foreach}
    </select>
{else if $field.type == 'sorting_options'}
    {if !isset($field.default_value)}{$field.default_value = current(array_keys($field.value))}{/if}
    <div class="selected-sorting-option">{$field.available_options[$field.default_value]|escape:'html':'UTF-8'}</div>
    <div class="dynamic-sorting-options">
        {foreach array_merge($field.value, $field.available_options) as $sorting_name => $sorting_label}
            {$active = isset($field.value[$sorting_name])}
            <div class="option{if $active} active{if $sorting_name == $field.default_value} current{/if}{/if}" data-value="{$sorting_name|escape:'html':'UTF-8'}">
                <a href="#" class="dragger"></a>
                <span class="name">{$sorting_label|escape:'html':'UTF-8'}</span>
                <input type="checkbox" name="{$name|escape:'html':'UTF-8'}[{$sorting_name|escape:'html':'UTF-8'}]" value="1" class="status-checkbox"{if $active} checked{/if}>
                <a href="#" class="status"></a>
            </div>
        {/foreach}
    </div>
{else if $field.type == 'multiple_options'}
    {include file="./options.tpl" name=$name data=$field}
{else if $field.type == 'tagify'}
    <div class="af-tagify">
        <input type="hidden" class="t-value" name="{$name|escape:'html':'UTF-8'}" value="{$value|escape:'html':'UTF-8'}">
        {if !empty($field.t_items_data)}<input type="hidden" class="t-items-data" value="{json_encode($field.t_items_data)|escape:'html':'UTF-8'}">{/if}
        <div class="t-items">
            <div class="quick-add">
                {if isset($field.qs_placeholder)}
                        {$input_class = trim('quickSearch '|cat:$input_class)}
                        <input type="text" class="{$input_class|escape:'html':'UTF-8'}" placeholder="{$field.qs_placeholder|escape:'html':'UTF-8'}">
                        {if !empty($field.qs_example)}
                            <div class="qs-tooltip">
                                {l s='For example [1]%s[/1]' mod='amazzingfilter' sprintf=[$field.qs_example] tags=['<span class="u">']}
                            </div>
                        {/if}
                        <div class="qs-results">{* filled dynamically *}</div>
                {else}
                    <button type="button" class="btn btn-primary t-add"><i class="icon-plus"></i></button>
                {/if}
            </div>
        </div>
    </div>
{else if $field.type == 'hidden'}
    <input type="hidden" name="{$name|escape:'html':'UTF-8'}" value="{$value|escape:'html':'UTF-8'}" class="{$input_class|escape:'html':'UTF-8'}">
{else if $field.type == 'textarea'}
    <textarea name="{$name|escape:'html':'UTF-8'}" class="{$input_class|escape:'html':'UTF-8'}">{$value|escape:'html':'UTF-8'}</textarea>
{else if $field.type != 'checkbox'} {* checkbox is added inside label*}
    {$use_group = !empty($field.input_prefix) || !empty($field.input_suffix)}
    {if $use_group}<div class="input-group">
        {if !empty($field.input_prefix)}<span class="input-group-addon">{$field.input_prefix|escape:'html':'UTF-8'}</span>{/if}
    {/if}
    <input type="text" name="{$name|escape:'html':'UTF-8'}" value="{$value|escape:'html':'UTF-8'}" class="{$input_class|escape:'html':'UTF-8'}">
    {if $use_group}
        {if !empty($field.input_suffix)}<span class="input-group-addon">{$field.input_suffix|escape:'html':'UTF-8'}</span>{/if}
        </div>
    {/if}
{/if}
{* since 3.3.0 *}

{*
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
*}
<div class="title-editor-type">
    <div class="title-editor-type-name">{l s='Replace elements' mod='xmlfeeds'}</div>
    <div style="display: none" id="title-replace-new-row">
        <div class="cn_line" data-id="__NEW_ROW__">
            {l s='Find what' mod='xmlfeeds'} <input style="width: 200px; margin-right: 16px;" type="text" name="title_replace_key[__NEW_ROW__]"> {l s='replace with' mod='xmlfeeds'} <input style="width: 200px;" type="text" name="title_replace_value[__NEW_ROW__]">
        </div>
    </div>
    <div id="title-replace-box">
        {foreach $productTitleEditorValues.key as $k => $keyValue}
            <div class="cn_line" data-id="{$k|escape:'htmlall':'UTF-8'}">
                {l s='Find what' mod='xmlfeeds'} <input style="width: 200px; margin-right: 16px;" type="text" name="title_replace_key[{$k|escape:'htmlall':'UTF-8'}]" value="{$keyValue|escape:'htmlall':'UTF-8'}"> {l s='replace with' mod='xmlfeeds'} <input style="width: 200px;" type="text" name="title_replace_value[{$k|escape:'htmlall':'UTF-8'}]" value="{$productTitleEditorValues.value[$k]|escape:'htmlall':'UTF-8'}">
            </div>
        {/foreach}
    </div>
    <div style="float: right; margin-right: 3px;" id="add-title-replace-row" class="blmod_button_small">
        {l s='Add more' mod='xmlfeeds'}
    </div>
    <div style="float: left;" class="bl_comments">
        {l s='[Rows with empty "Find what" will be removed]' mod='xmlfeeds'}
    </div>
    <div class="cb"></div>
</div>
<div class="title-editor-type">
    <div class="title-editor-type-name">{l s='Add elements' mod='xmlfeeds'}</div>
    <div>
        {foreach $productTitleEditorElementsList as $k => $n}
            <label class="attribute-list">
                <input type="checkbox" name="title_editor_add_elements[]" value="{$k|escape:'htmlall':'UTF-8'}"{if $k|in_array:$productTitleEditorNewElements.elements} checked{/if}> {$n|escape:'htmlall':'UTF-8'}
            </label>
        {/foreach}
    </div>
</div>
<div class="title-editor-type">
    <div class="title-editor-type-name">{l s='Add attributes' mod='xmlfeeds'}</div>
    <div>
        <div class="mb10">
            <label class="attribute-list">
                <input type="checkbox" name="title_editor_options[]" value="attribute_name"{if attribute_name|in_array:$productTitleEditorNewElements.options} checked{/if}> {l s='Attributes with name' mod='xmlfeeds'}
            </label>
        </div>
        <div class="mb10">
            <label class="attribute-list">
                <input id="title_editor_all_attributes" type="checkbox" name="title_editor_add_elements[]" value="1"{if 1|in_array:$productTitleEditorNewElements.elements} checked{/if}> {l s='All attributes' mod='xmlfeeds'}
            </label>
        </div>
        {foreach $attributesGroups as $a}
            <label class="attribute-list">
                <input type="checkbox" class="title_editor_attributes" name="title_editor_add_attributes[]" value="{$a.id_attribute_group|escape:'htmlall':'UTF-8'}"{if $a.id_attribute_group|in_array:$productTitleEditorNewElements.attributes OR 1|in_array:$productTitleEditorNewElements.elements} checked{/if}{if 1|in_array:$productTitleEditorNewElements.elements} readonly="readonly" disabled="disabled"{/if}> {$a.name|escape:'htmlall':'UTF-8'}
            </label>
        {/foreach}
    </div>
</div>

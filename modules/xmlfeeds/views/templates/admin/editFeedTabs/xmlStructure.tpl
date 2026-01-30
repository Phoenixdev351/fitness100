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
<table border="0" width="100%" cellpadding="3" cellspacing="0">
    <tr class="only-product only-category">
        <td class="settings-column-name">{l s='XML in one branch' mod='xmlfeeds'}</td>
        <td>
            <label for="one_branch">
                {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='one_branch' name='one_branch' status=$s.one_branch}
            </label>
        </td>
    </tr>
    <tr>
        <td class="settings-column-name">{l s='Encoding' mod='xmlfeeds'}</td>
        <td>
            <input type="text" name="encoding_text" value="{$s.encoding_text|escape:'htmlall':'UTF-8'}" placeholder="Default: UTF-8">
        </td>
    </tr>
    <tr class="only-product">
        <td class="settings-column-name">{l s='All images' mod='xmlfeeds'}</td>
        <td>
            <label for="all_images">
                {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='all_images' name='all_images' status=$s.all_images}
            </label>
        </td>
    </tr>
    <tr>
        <td class="settings-column-name">{l s='Last-Modified header' mod='xmlfeeds'}</td>
        <td>
            <label for="last_modified_header">
                {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='last_modified_header' name='last_modified_header' status=$s.last_modified_header}
            </label>
        </td>
    </tr>
    <tr>
        <td class="settings-column-name">{l s='Each item on a new line' mod='xmlfeeds'}</td>
        <td>
            <label>
                {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='item_starts_on_a_new_line' name='item_starts_on_a_new_line' status=$s.item_starts_on_a_new_line}
            </label>
        </td>
    </tr>
    {if !empty($groups)}
        <tr id="merge-attributes-by-group-box" class="only-product{if !empty($s['merge_attributes_by_group'])} box-toggle-active{/if}">
            <td class="settings-column-name">{l s='Merge attributes by group' mod='xmlfeeds'}</td>
            <td>
                <label for="merge_attributes_by_group">
                    {include file="{$tpl_dir}/views/templates/admin/helper/status.tpl" id='merge_attributes_by_group' name='merge_attributes_by_group' status=$s.merge_attributes_by_group}
                </label>
                <div class="cb"></div>
                <div id="merge-attributes-by-group-select"{if empty($s.merge_attributes_by_group)} style="display: none;"{/if}>
                    <div class="blmod_mt10">
                        <select name="merge_attributes_parent">
                            <option value="0">{l s='None' mod='xmlfeeds'}</option>
                            {foreach $groups as $g}
                                <option value="{$g.id_attribute_group|escape:'htmlall':'UTF-8'}"{if $g.id_attribute_group == $s.merge_attributes_parent} selected{/if}>{$g.name|escape:'htmlall':'UTF-8'}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <div class="bl_comments">{l s='[Especially useful for Skroutz/Bestprice colors and sizes]' mod='xmlfeeds'}</div>
            </td>
        </tr>
    {/if}
</table>
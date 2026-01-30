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
<div style="float: left; width: 190px; font-weight: bold;">{l s='File name:' mod='xmlfeeds'}</div><div style="float: left;"><input type="text" name="file-name" value="{$b_name['file-name']|escape:'htmlall':'UTF-8'}" size="30"/></div>
<div class="cb"></div>
<div style="float: left; width: 190px; font-weight: bold;">{l s='Category block name:' mod='xmlfeeds'}</div><div style="float: left;"><input type="text" name="cat-block-name" value="{$b_name['cat-block-name']|escape:'htmlall':'UTF-8'}" size="30"/></div>
<div class="cb"></div>
<div style="float: left; width: 190px; font-weight: bold;">{l s='Description block name:' mod='xmlfeeds'}</div><div style="float: left;"><input type="text"{if !empty($disabled_branch_name)} disabled="disabled" {/if}name="desc-block-name" value="{$b_name['desc-block-name']|escape:'htmlall':'UTF-8'}" size="30"/></div>
<br/><br/>
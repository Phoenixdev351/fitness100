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
{foreach $feedTypeList as $feedId => $f}
    <label class="feed_type_icon" title="{$f.name|escape:'htmlall':'UTF-8'}" data-name="{$f.name|escape:'htmlall':'UTF-8'|lower}">
        <img alt="{$f.name|escape:'htmlall':'UTF-8'} xml feed" src="../modules/xmlfeeds/views/img/type_{$feedId|escape:'htmlall':'UTF-8'}.png" />
        <input type="radio" name="feed_mode" value="{$feedId|escape:'htmlall':'UTF-8'}" title="{$f.name|escape:'htmlall':'UTF-8'}"> {$f.name|escape:'htmlall':'UTF-8'}
    </label>
    {if $f@iteration is div by 4}
        <div class="cb"></div>
    {/if}
{/foreach}
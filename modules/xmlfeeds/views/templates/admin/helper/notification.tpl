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
{if !empty($notifications)}
    {foreach $notifications as $n}
        <div class="{$n.cssClass|escape:'htmlall':'UTF-8'} blmod_mt10">
            <img src="{$moduleImgPath|escape:'htmlall':'UTF-8'}ok.gif" /> {$n.message}
        </div>
    {/foreach}
{/if}
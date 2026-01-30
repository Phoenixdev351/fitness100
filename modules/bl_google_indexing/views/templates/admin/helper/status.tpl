{*
 * 2010-2021 Bl Modules.
 *
 * If you wish to customize this module for your needs,
 * please contact the authors first for more information.
 *
 * It's not allowed selling, reselling or other ways to share
 * this file or any other module files without author permission.
 *
 * @author    Bl Modules
 * @copyright 2010-2021 Bl Modules
 * @license
*}
<input id="{$id|escape:'htmlall':'UTF-8'}" type="checkbox" name="{$name|escape:'htmlall':'UTF-8'}"
{if !empty($status)}
    value = "1" checked{if !empty($disabled)} disabled{/if} /> <img src="{$moduleImgPath|escape:'htmlall':'UTF-8'}enabled.gif" alt="{l s='Enabled' mod='bl_google_indexing'}" />{l s='Enabled' mod='bl_google_indexing'}
{/if}
{if empty($status)}
    value = "1"{if !empty($disabled)} disabled{/if}/> <img src="{$moduleImgPath|escape:'htmlall':'UTF-8'}disabled.gif" alt="{l s='Disabled' mod='bl_google_indexing'}" />{l s='Disabled' mod='bl_google_indexing'}
{/if}
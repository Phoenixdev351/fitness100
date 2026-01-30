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
{if !empty($feedTypeList)}
    {include file="{$tpl_dir}/views/templates/admin/element/feedTypeList.tpl" feedTypeList=$feedTypeList}
{else}
    <div class="feed-type-not-found">
        <div>
            Sorry, we couldn't find any result.
        </div>
        <div style="margin-top: 5px;">
            {l s='Feel free to contact us via' mod='xmlfeeds'} <a href="{$contactUsUrl|escape:'htmlall':'UTF-8'}" target="_blank">{l s='Prestashop Messenger' mod='xmlfeeds'}</a>.
        </div>
        <div style="margin-top: 5px;">
            {l s='We will be very happy to help integrate a new marketplace/channel or just create an individual XML feed.' mod='xmlfeeds'}
        </div>
        <div style="margin-top: 5px;">
            {l s='Of course, if you want, you can always change the settings by yourself and create a custom XML feed by your requirements. Our module will provide a lot of flexibility with XML structure, data filters.' mod='xmlfeeds'}
        </div>
    </div>
{/if}
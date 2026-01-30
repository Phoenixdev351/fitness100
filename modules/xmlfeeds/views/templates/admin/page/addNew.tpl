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
<form id="add-new-feed" action="{$requestUri|escape:'htmlall':'UTF-8'}" method="post">
    <div class="panel">
        <div class="panel-heading">
            <i class="icon-cog"></i> {l s='Create a new XML feed' mod='xmlfeeds'}
        </div>
        <div class="row">
            <table border="0" width="100%" cellpadding="3" cellspacing="0">
                <tr>
                    <td class="settings-column-name" style="width: 125px;">{l s='Feed name' mod='xmlfeeds'}</td>
                    <td>
                        <input id="new-feed-name" style="max-width: 462px;" type="text" name="name" value="" autocomplete="off" required>
                    </td>
                </tr>
                {if $feed_type eq '1'}
                    <tr>
                        <td><br></td>
                        <td>
                            <div class="info-small-blmod blmod_mt15">
                                {l s='Here you can find the most popular XML feeds. They are already fully prepared for you to use with just one click.' mod='xmlfeeds'}<br>
                                {l s='If you need, feel free to customize feeds to suit your individual needs.' mod='xmlfeeds'}<br>
                                {l s='Of course you can also create your own unique from scratch.' mod='xmlfeeds'}<br>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="settings-column-name al-t" style="width: 125px;">
                            {l s='Type' mod='xmlfeeds'}
                        </td>
                        <td>
                            <div class="option-box-title blmod_m20 cd">{l s='Most popular types:' mod='xmlfeeds'}</div>
                            {include file="{$tpl_dir}/views/templates/admin/element/feedTypeList.tpl" feedTypeList=$mostPopularTypeList}
                            <div class="blmod_cb"></div>
                            <div class="option-box-title blmod_m20 cd">{l s='Types by alphabet:' mod='xmlfeeds'}</div>
                            <div id="search-feed-box">
                                <img src="{$moduleImgPath|escape:'htmlall':'UTF-8'}search-blue.png" style="position: relative; top: 0; left: 2px;" />
                                <input id="search-feed-type" class="feed-search-input" type="text" value="" placeholder="{l s='Search by name' mod='xmlfeeds'}">
                            </div>
                            <div id="types-by-alphabet">
                                {include file="{$tpl_dir}/views/templates/admin/element/feedTypeList.tpl" feedTypeList=$feedTypeList}
                            </div>
                            <div class="cb"></div>
                        </td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                    </tr>
                {/if}
            </table>
            <input type="hidden" name="feed_type" value="{$feed_type|escape:'htmlall':'UTF-8'}">
            <center>
                <input {if $feed_type eq '1'}style="display: none;"{/if} type="submit" name="add_new_feed_insert" value="{l s='Create new' mod='xmlfeeds'}" class="btn btn-primary blmod_mt15i" />
            </center>
        </div>
    </div>
</form>
<br/>
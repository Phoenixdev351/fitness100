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
<div class="panel">
    <div class="panel-heading">
        <i class="icon-external-link"></i> {l s='XML File' mod='xmlfeeds'}
    </div>
    <div class="row">
        <table border="0" width="100%" cellpadding="1" cellspacing="0">
            <tr>
                <td>
                    {if empty($s.use_cron)}
                        <div id="url_file_box">
                            <input id="feed_url" style="margin-bottom: 13px;" type="text" name="xml_link" value="{$link|escape:'htmlall':'UTF-8'}" /><br/>
                            <a id="feed_url_open" class="btn btn-info" href="{$link|escape:'htmlall':'UTF-8'}" target="_blank">{l s='Open XML' mod='xmlfeeds'}</a>
                            <a id="feed_url_download" class="btn btn-info blmod_ml10i" href="{$link|escape:'htmlall':'UTF-8'}&download=1" target="_blank">{l s='Download XML' mod='xmlfeeds'}</a>
                            <div id="feed_url_copy" class="feed_url_copy_action btn btn-info blmod_ml10i">{l s='Copy URL' mod='xmlfeeds'}</div>
                            {if !empty($multistore_status)}
                                <div id="multistore_url" class="bl_comments" style="float: right; cursor: pointer; padding-right: 4px;">{l s='[Show/Hide Multistore options]' mod='xmlfeeds'}</div>
                                <div id="multistore_url_list" style="margin-top: 15px; display:none;">
                                    {foreach $multistore as $h}
                                        <div>
                                            <hr/>
                                            <label for="multistore_{$h.id_shop|escape:'htmlall':'UTF-8'}"><input id="multistore_{$h.id_shop|escape:'htmlall':'UTF-8'}" class="multistore_url_checkbox" type="checkbox" name="status" value="{$h.id_shop|escape:'htmlall':'UTF-8'}"> {$h.name|escape:'htmlall':'UTF-8'}</label>
                                        </div>
                                    {/foreach}
                                </div>
                            {/if}
                        </div>
                    {/if}
                    {if $s.feed_mode == 'pub'}
                        <div style="margin-top: 20px;">
                            <div>
                                <div style="margin-bottom: 2px; color: #3586AE; font-size: 13px;">{l s='Products XML feed:' mod='xmlfeeds'}</div>
                                <input style="margin-bottom: 13px;" type="text" value="{$link|escape:'htmlall':'UTF-8'}&type=products" /><br/>
                            </div>
                            <div>
                                <div style="margin-bottom: 2px; color: #3586AE; font-size: 13px;">{l s='Offers XML feed:' mod='xmlfeeds'}</div>
                                <input type="text" value="{$link|escape:'htmlall':'UTF-8'}&type=offers" /><br/>
                            </div>
                        </div>
                    {/if}
                    {if !empty($s.use_cron)}
                        <div id="cron_file_box">
                            <p>
                                <input id="feed_url" style="width: 98%; margin-bottom: 10px;" type="text" value="{$cronXmlFile|escape:'htmlall':'UTF-8'}" />
                                <input id="cron_path_original" type="hidden" value="{$cronXmlFile|escape:'htmlall':'UTF-8'}" />
                            </p>
                            {if empty($s.last_cron_date) || $s.last_cron_date == '-' || $s.last_cron_date == '0000-00-00 00:00:00'}
                                <div class="alert-small-blmod blmod_mt10 blmod_mb15">
                                    {l s='Cron is enabled, but not yet launched.' mod='xmlfeeds'}<br>
                                    {l s='You can read the installation instructions' mod='xmlfeeds'} <span class="show_cron_install" style="cursor: pointer; color: #268CCD;">{l s='here' mod='xmlfeeds'}</span>.<br><br>
                                    {l s='P.S. Cron is useful only when you have a lot of products and server is very slow. Our module can update a feed (products, orders, etc..) automatically by self without a cron.' mod='xmlfeeds'}<br>
                                </div>
                            {else}
                                <a id="feed_url_open" class="xml_feed_kbd" href="{$cronXmlFile|escape:'htmlall':'UTF-8'}" target="_blank">{l s='Open' mod='xmlfeeds'}</a>
                                <a id="feed_url_download" class="xml_feed_kbd blmod_ml10i" href="{$cronXmlFile|escape:'htmlall':'UTF-8'}" download>{l s='Download' mod='xmlfeeds'}</a>
                                <div id="feed_url_copy" class="feed_url_copy_action xml_feed_kbd blmod_ml10i">{l s='Copy url' mod='xmlfeeds'}</div>
                                <p style="font-size: 13px; margin-top: 20px;">{l s='Last cron activity:' mod='xmlfeeds'} {$s.last_cron_date|escape:'htmlall':'UTF-8'}</p>
                            {/if}
                            <div class="show_cron_install" style="float: left; cursor: pointer; color: #268CCD;">{l s='[Show/hide cron installation instructions]' mod='xmlfeeds'}</div>
                            <div style="float: left; cursor: pointer; color: #268CCD; margin-left: 10px;"><a style="color: #268CCD;" target="_blank" href="{$fullAdminUrl|escape:'htmlall':'UTF-8'}&page={$page|escape:'htmlall':'UTF-8'}&access_log=1">{l s='[Access log]' mod='xmlfeeds'}</a></div>
                            <div class="blmod_cb"><br></div>
                            <div id="cron_install_instruction" style="display: none;">
                                <p style="margin-bottom: 10px;">{l s='There are three options for how to execute cron job. The first and most common is run from a server side and the second is call from remote server.' mod='xmlfeeds'}</p>
                                <p><b>{l s='1. Install cron from a server command line (shell) or admin panel' mod='xmlfeeds'}</b></p>
                                <p>{l s='1. 1. Execute crontab -e (cron edit page) command' mod='xmlfeeds'}</p>
                                <p>{l s='1. 2. In a new line enter the cron command (data will be updated every 2 hours):' mod='xmlfeeds'} </p>
                                <p><input id="cron_command" style="width: 98%;" type="text" value="{$cronCommand|escape:'htmlall':'UTF-8'}" /><input id="cron_command_original" type="hidden" value="{$cronCommand|escape:'htmlall':'UTF-8'}" /></p>
                                <p>1. 3. Save the cron (<kbd>Ctrl</kbd> + <kbd>X</kbd>, answer by pressing <kbd>Y</kbd> to save changes and <kbd>Enter</kbd> to confirm)</p>
                                <p style="margin-top: 15px;"><b>{l s='2. Run cron from URL or a remote server' mod='xmlfeeds'}</b></p>
                                <p>{l s='URL to execute XML feed via cron:' mod='xmlfeeds'}</p>
                                <p><input style="width: 98%;" type="text" name="xml_link_cron_outside" value="{$link|escape:'htmlall':'UTF-8'}" /></p>
                                <p style="margin-top: 15px;"><b>{l s='3. Setup cron with cPanel, Plesk, DirectAdmin or another server management tool' mod='xmlfeeds'}</b></p>
                            </div>
                        </div>
                    {/if}
                </td>
            </tr>
        </table>
    </div>
</div>
<div class="cb"></div>
<form action="{$requestUri|escape:'htmlall':'UTF-8'}" method="post" autocomplete="off">
    <div class="panel">
        <div class="panel-heading">
            <i class="icon-cog"></i> {l s='XML Feed settings' mod='xmlfeeds'}
        </div>
        {if $s.feed_type eq 1}
        <div id="product-feed-settings" class="row">
            <div id="option_box_1" class="option-box">
                <div id="option-box-title_1" class="option-box-title">General <i class="icon-angle-up"></i></div>
                <div id="option-box-content_1" class="option-box-content" style="display: block;">
                    {include file="{$tpl_dir}/views/templates/admin/editFeedTabs/general.tpl" s=$s page=$page moduleImgPath=$moduleImgPath name=$name countries=$countries productSettingsPackagesList=$productSettingsPackagesList productAttributes=$productAttributes}
                </div>
            </div>
            <div id="option_box_2" class="option-box">
                <div id="option-box-title_2" class="option-box-title">Fields <i class="icon-angle-down"></i></div>
                <div id="option-box-content_2" class="option-box-content" style="display: none;">
                    {include file="{$tpl_dir}/views/templates/admin/editFeedTabs/fields.tpl" s=$s page=$page moduleImgPath=$moduleImgPath categoryMapList=$categoryMapList}
                </div>
            </div>
            <div id="option_box_3" class="option-box">
                <div id="option-box-title_3" class="option-box-title">XML structure <i class="icon-angle-down"></i></div>
                <div id="option-box-content_3" class="option-box-content" style="display: none;">
                    {include file="{$tpl_dir}/views/templates/admin/editFeedTabs/xmlStructure.tpl" s=$s page=$page moduleImgPath=$moduleImgPath groups=$groups}
                </div>
            </div>
            <div id="option_box_4" class="option-box">
                <div id="option-box-title_4" class="option-box-title">Price <i class="icon-angle-down"></i></div>
                <div id="option-box-content_4" class="option-box-content" style="display: none;">
                    {include file="{$tpl_dir}/views/templates/admin/editFeedTabs/price.tpl" s=$s page=$page moduleImgPath=$moduleImgPath priceFromList=$priceFromList currencyList=$currencyList}
                </div>
            </div>
            <div id="option_box_5" class="option-box">
                <div id="option-box-title_5" class="option-box-title">Filter <i class="icon-angle-down"></i></div>
                <div id="option-box-content_5" class="option-box-content" style="display: none;">
                    {include file="{$tpl_dir}/views/templates/admin/editFeedTabs/filter.tpl" s=$s page=$page moduleImgPath=$moduleImgPath priceFrom=$priceFrom priceTo=$priceTo productListSettingsPage=$productListSettingsPage supplierList=$supplierList categoriesTree=$categoriesTree categoriesTreeL=$categoriesTreeL manufacturersList=$manufacturersList productListSettingsPage=$productListSettingsPage filterAttributes=$filterAttributes filterWithoutAttributes=$filterWithoutAttributes}
                </div>
            </div>
            <div id="option_box_6" class="option-box blmod_b20">
                <div id="option-box-title_6" class="option-box-title">Product title editor <i class="icon-angle-down"></i></div>
                <div id="option-box-content_6" class="option-box-content" style="display: none;">
                    {include file="{$tpl_dir}/views/templates/admin/editFeedTabs/productTitleEditor.tpl" s=$s page=$page productTitleEditorValues=$productTitleEditorValues productTitleEditorElementsList=$productTitleEditorElementsList attributesGroups=$groups productTitleEditorNewElements=$productTitleEditorNewElements}
                </div>
            </div>
            {/if}
            {if $s.feed_type eq 2}
                <div id="category-feed-settings" class="row option-box-content">
                    {include file="{$tpl_dir}/views/templates/admin/editFeedTabs/general.tpl" s=$s page=$page moduleImgPath=$moduleImgPath name=$name countries=$countries productSettingsPackagesList=$productSettingsPackagesList}
                    {include file="{$tpl_dir}/views/templates/admin/editFeedTabs/xmlStructure.tpl" s=$s page=$page moduleImgPath=$moduleImgPath groups=$groups}
                    {include file="{$tpl_dir}/views/templates/admin/editFeedTabs/fields.tpl" s=$s page=$page moduleImgPath=$moduleImgPath}
            {/if}
            {if $s.feed_type eq 3}
                <div id="category-feed-settings" class="row option-box-content order-feed-settings">
                    {include file="{$tpl_dir}/views/templates/admin/editFeedTabs/general.tpl" s=$s page=$page moduleImgPath=$moduleImgPath name=$name countries=$countries productSettingsPackagesList=$productSettingsPackagesList}
                    {include file="{$tpl_dir}/views/templates/admin/editFeedTabs/price.tpl" s=$s page=$page moduleImgPath=$moduleImgPath priceFromList=$priceFromList currencyList=$currencyList}
                    {include file="{$tpl_dir}/views/templates/admin/editFeedTabs/fields.tpl" s=$s page=$page moduleImgPath=$moduleImgPath}
                    {include file="{$tpl_dir}/views/templates/admin/editFeedTabs/xmlStructure.tpl" s=$s page=$page moduleImgPath=$moduleImgPath groups=$groups}
                    {include file="{$tpl_dir}/views/templates/admin/editFeedTabs/orderExtra.tpl" s=$s page=$page moduleImgPath=$moduleImgPath filterDateType=$filterDateTypes FILTER_DATE_DATE_RANGE=$FILTER_DATE_DATE_RANGE FILTER_DATE_CUSTOM_DAYS=$FILTER_DATE_CUSTOM_DAYS orderStatusList=$orderStatusList}
            {/if}
            {if $s.feed_type eq 4 || $s.feed_type eq 5 || $s.feed_type eq 6}
                <div id="category-feed-settings" class="row option-box-content">
                    {include file="{$tpl_dir}/views/templates/admin/editFeedTabs/general.tpl" s=$s page=$page moduleImgPath=$moduleImgPath name=$name countries=$countries productSettingsPackagesList=$productSettingsPackagesList}
                    {include file="{$tpl_dir}/views/templates/admin/editFeedTabs/xmlStructure.tpl" s=$s page=$page moduleImgPath=$moduleImgPath groups=$groups}
            {/if}
                    <div class="cb"></div>
                    <div style="text-align: center;" class="blmod_mt10">
                        <input type="submit" name="update_feeds_s" value="Update" class="btn btn-primary" />
                        <input style="margin-left: 10px; font-weight: normal;" type="submit" name="clear_cache" value="Clear cache" class="btn btn-secondary" />
                    </div>
                    <input type="hidden" name="feeds_name" value="{$page|escape:'htmlall':'UTF-8'}" />
                </div>
            </div>
            <br/>

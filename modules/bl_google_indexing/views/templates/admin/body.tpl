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
{if _PS_VERSION_ >= 1.6}
    <style>
        .blmod_module .bootstrap input[type="checkbox"] {
            margin-top: 2px!important;
        }
    </style>
{/if}
{if _PS_VERSION_ >= 1.5}
    <style>
        .blmod_module .conf img, .blmod_module .warn img, .blmod_module .error img, .alert img{
            display: none;
        }
        .blmod_module .warn, .blmod_module .error, .blmod_module .conf {
            padding-left: 40px;
            padding-right: 0;
        }
    </style>
{/if}
{if _PS_VERSION_ < 1.5}
    <style>
        .blmod_module .row{
            background: #FFF;
        }
        .module_logo{
            margin-top: 0!important;
            margin-bottom: 15px;
        }
        .blmod_module #content{
            border: 0!important;
        }
        .blmod_module .order_table_order{
            width: 900px!important;
        }
        .blmod_module .order_table_date{
            font-size: 11px;
        }
        .order_table_order tr:hover, .order_table_logs tr:hover{
            background-color: #d9edf7!important;
        }
        .info_block_order_status .list_checkbox{
            margin-top: 4px;
        }
        .list_name img{
            margin-right: 4px;
        }
        .icon_menu_box{
            margin-right: 15px!important;
        }
        .blmod_module .pagination{
            margin-bottom: 10px;
        }
    </style>
{/if}
{if _PS_VERSION_ >= '1.5' && _PS_VERSION_ < '1.6'}
    <style>
        .xml_feed_module .conf img, .xml_feed_module .warn img, .xml_feed_module .error img {
            display: none;
        }
    </style>
{/if}
<div class="xml_feed_module">
    <div class="blmod_module">
        <div class="module_logo">
            <img src="{$moduleImgPath|escape:'htmlall':'UTF-8'}icon36w.png" />
        </div>
        <div class="module_title">
            <h2>{$displayName|escape:'htmlall':'UTF-8'}</h2>
            <div class="module_version">{l s='Version:' mod='bl_google_indexing'} {$version|escape:'htmlall':'UTF-8'}</div>
        </div>
        <div class="clear_block"></div>
        <div class="bootstrap">
            <div id="content" class="bootstrap content_blmod">
                {include file="{$tpl_dir}/views/templates/admin/helper/notification.tpl" notifications=$notifications moduleImgPath=$moduleImgPath}
                <div class="bootstrap">
                    <div class="feed_settings_box">
                        {$contentHtml}
                    </div>
                    <div class="cb"></div>
                </div>
            </div>
        </div>
    </div>
</div>
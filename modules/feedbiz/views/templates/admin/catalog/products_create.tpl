{**
* NOTICE OF LICENSE
*
* This source file is subject to a commercial license from Feed.Biz, Ltd.
* Use, copy, modification or distribution of this source file without written
* license agreement from Feed.Biz, Ltd. is strictly forbidden.
* In order to obtain a license, please contact us: contact@feed.biz
* ...........................................................................
* INFORMATION SUR LA LICENCE D'UTILISATION
*
* L'utilisation de ce fichier source est soumise a une licence commerciale
* concedee par la societe Feed.Biz, Ltd.
* Toute utilisation, reproduction, modification ou distribution du present
* fichier source sans contrat de licence ecrit de la part de la Feed.Biz, Ltd. est
* expressement interdite.
* Pour obtenir une licence, veuillez contacter Feed.Biz, Ltd. a l'adresse: contact@feed.biz
* ...........................................................................
* @package    Feed.Biz
* @author     Olivier B.
* @copyright  Copyright (c) 2011-2022 Feed.Biz - Hong Kong - Head Quarters - Room 1408, 14/F, Tak Shing House - Theatre Lane - 20 des Voeux Road Central, -Central, Hong Kong
* @license    Commercial license
* Support by mail  :  support@feed.biz
*}

{if $tab_create_data}
    <div id="menudiv-create"  class="panel form-horizontal" rel="create" style="display:none;">
        <h2>{l s='Products' mod='feedbiz'}</h2>
        <h3>{$tab_create_data.last_export_title|escape:'htmlall':'UTF-8'}{$tab_create_data.last_cron_export_title|escape:'htmlall':'UTF-8'}</h3>
        
        <div id="create-loader"></div>
        <form action="{$tab_create_data.request_uri|escape:'htmlall':'UTF-8'}" method="post" id="create-products-form">
            <h4>{l s='Parameters' mod='feedbiz'}</h4>
            
            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Only Active Products' mod='feedbiz'}</label>
                <div class="margin-form col-lg-9">
                    <input type="checkbox" name="create-active" value="1" /> <span class="ccb-title">{l s='Yes' mod='feedbiz'}</span>
                </div>
            </div>
            
            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Only In Stock Products' mod='feedbiz'}</label>
                <div class="margin-form col-lg-9">
                    <input type="checkbox" name="create-in-stock" value="1" /> <span class="ccb-title">{l s='Yes' mod='feedbiz'}</span>
                </div>
            </div>
                
            <input type="hidden" name="last-update" value="{$tab_create_data.last_export|escape:'htmlall':'UTF-8'}" />

            <div class="{$alert_class.success|escape:'htmlall':'UTF-8'}" id="create-products-result" style="display:none"></div>
            <div class="{$alert_class.warn|escape:'htmlall':'UTF-8'}" id="create-products-error" style="display:none"></div>
            <input type="hidden" id="create-products-url" value="{$tab_create_data.products_url|escape:'htmlall':'UTF-8'}" />
            <div class="form-group">
                <input type="button" id="create-products" value="{l s='Export' mod='feedbiz'}" class="button btn btn-default" />
            </div>
            
            <div class="{$alert_class.success|escape:'htmlall':'UTF-8'}" id="create-products-latest" style="display:none"></div>
        </form>        
    </div>
{/if}
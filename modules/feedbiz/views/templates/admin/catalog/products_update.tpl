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

{if $tab_update_data}
    <div id="menudiv-update"  class="panel form-horizontal" rel="update">
        <h2>{l s='Offers' mod='feedbiz'}</h2>
        <h3>{$tab_update_data.last_export_title|escape:'htmlall':'UTF-8'}{$tab_update_data.last_cron_export_title|escape:'htmlall':'UTF-8'}</h3>
        <div id="update-loader"></div>
        <form action="{$tab_update_data.request_uri|escape:'htmlall':'UTF-8'}" method="post" id="update-products-form">
            <br />
            <h4>{l s='Parameters' mod='feedbiz'}</h4>
            
            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Only Active Products' mod='feedbiz'}</label>
                <div class="margin-form col-lg-9">
                    <input type="checkbox" name="update-active" value="1" /> <span class="ccb-title">{l s='Yes' mod='feedbiz'}</span>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Only In Stock Products' mod='feedbiz'}</label>
                <div class="margin-form col-lg-9">
                    <input type="checkbox" name="update-in-stock" value="1" /> <span class="ccb-title">{l s='Yes' mod='feedbiz'}</span>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Update Type' mod='feedbiz'}</label>
                <div class="margin-form col-lg-9">
                    <input type="radio" name="update-type" value="full-update" id="full-update" checked/> <span class="ccb-title">{l s='Full Update' mod='feedbiz'}</span>
                    &nbsp; &nbsp; 
                    <input type="radio" name="update-type" value="partail-update" id="partail-update" /> <span class="ccb-title">{l s='Partial Update' mod='feedbiz'}</span>
                </div>
            </div>

            <div id="partial-date-range" class="form-group">
                <label class="control-label col-lg-3">{l s='Update Type' mod='feedbiz'}</label>
                <div class="margin-form col-lg-2">
                    <span>{l s='Date Range' mod='feedbiz'} From</span>
                    <input type="text" name="partial-date-range-from" id="datepickerFrom2" value="2014-04-10"><br/>
                    <span>To</span>
                    <input type="text" name="partial-date-range-to" id="datepickerTo2" value="2014-04-22">
                </div>
            </div>

            <input type="hidden" name="last-update" value="{$tab_update_data.last_export|escape:'htmlall':'UTF-8'}" />

            <div class="{$alert_class.success|escape:'htmlall':'UTF-8'}" id="update-products-result" style="display:none"></div>
            <div class="{$alert_class.warn|escape:'htmlall':'UTF-8'}" id="update-products-error" style="display:none"></div>
            <input type="hidden" id="update-products-url" value="{$tab_update_data.products_url|escape:'htmlall':'UTF-8'}" />
            <div class="form-group">
                <input type="button" id="update-products" value="{l s='Export' mod='feedbiz'}" class="button btn btn-default" />
            </div>
            <div class="{$alert_class.success|escape:'htmlall':'UTF-8'}" id="update-products-latest" style="display:none"></div>
        </form>        
    </div>
{/if}
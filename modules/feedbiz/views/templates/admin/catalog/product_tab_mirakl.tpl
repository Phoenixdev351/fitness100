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

<div class="feedbiz-mirakl-product-tab">
  <h3 class="tab feedbiz-title">
    {$marketplace.marketplace_name|escape:'htmlall':'UTF-8'}
  </h3>
  <div class="product-tab">
    <div class="form-group">
      {if $marketplace.show_countries}
          <table class="country-selector">
            <tr>
              {foreach from=$marketplace.marketplaces key=region item=market}
                  <td>
                    <span class="mirakl-tab-selector{if $market.default} active{/if}" data-iso-code="{$market.region|escape:'htmlall':'UTF-8'}">
                      <img src="{$market.image|escape:'htmlall':'UTF-8'}" title="{$market.name|escape:'htmlall':'UTF-8'}" class="marketplace-flag"/>
                      {if isset($marketplace.lang_flag)}
                          <img src="{$marketplace.lang_flag|escape:'htmlall':'UTF-8'}"
                               title="{$marketplace.lang_iso_code|escape:'htmlall':'UTF-8'}"
                               class="marketplace-lang-flag"/>
                      {/if}
                    </span>
                  </td>
              {/foreach}
            </tr>
          </table>

          {foreach from=$marketplace.marketplaces key=region item=market}
              
              <div class="mirakl-tab mirakl-tab-{$market.region|escape:'htmlall':'UTF-8'}" data-iso-code="{$market.region|escape:'htmlall':'UTF-8'}" {*{if $smarty.foreach.marketplace.iteration > 1} style="display:none"{/if}*} class="col-lg-12" {if !$market.default}style="display:none"{/if} >
                <span id="mirakl-action-loader" style="display:none">
                  <img src="{$img|escape:'quotes':'UTF-8'}/green-loader.gif"/>
                </span>
                {if $product_tab.mirakl.show_countries}
                    <h4 class="marketplace-heading"><img src="{$market.image|escape:'quotes':'UTF-8'}" alt="{$market.name|escape:'quotes':'UTF-8'}"/>
                      {$market.name|escape:'quotes':'UTF-8'}
                    </h4>
                    <div class="clearfix"></div>
                {/if}
                {include file="{$module_path|escape:'quotes':'UTF-8'}/views/templates/admin/catalog/product_tab_mirakl_subtab.tpl" data=$market}
              </div>
              
          {/foreach}
      {/if}
    </div>
  </div>
</div>
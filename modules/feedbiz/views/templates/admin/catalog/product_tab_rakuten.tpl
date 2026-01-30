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

{*TODO*}
<link href="{$module_url|escape:'htmlall':'UTF-8'}views/css/product_tab.rakuten.css?v={$version|escape:'htmlall':'UTF-8'}" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="{$module_url|escape:'htmlall':'UTF-8'}views/js/product_tab_rakuten.js?v={$version|escape:'htmlall':'UTF-8'}"></script>

<div id="feedbiz-rakuten-product-tab">
    <h3 class="tab feedbiz-title">
        <img src="{$img|escape:'htmlall':'UTF-8'}rakuten.png" alt=""/> Rakuten Marketplace
    </h3>

    <div class="product-tab">
        <div class="form-group">
            {if $product_tab.rakuten.show_countries}
                <table class="country-selector">
                    <tr>
                        {foreach from=$product_tab.rakuten.marketplaces item=marketplace}
                            <td>
                                <span class="rakuten-tab-selector{if $marketplace.default} active{/if}"
                                      data-iso-code="{$marketplace.region|escape:'htmlall':'UTF-8'}">
                                    <img src="{$marketplace.image|escape:'htmlall':'UTF-8'}"
                                         title="{$marketplace.name|escape:'htmlall':'UTF-8'}" class="marketplace-flag"/>
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
                <div class="col-lg-12">
                    <div class="rakuten-tab-bar"></div>
                </div>
            {else}                
                {foreach from=$product_tab.rakuten.marketplaces item=marketplace}
                    <span class="rakuten-tab-selector{if $marketplace.default} active{/if}" data-iso-code="{$marketplace.region|escape:'htmlall':'UTF-8'}"> &nbsp;</span>
                {/foreach}
            {/if}

            {foreach from=$product_tab.rakuten.marketplaces item=marketplace}
                <div class="rakuten-tab rakuten-tab-{$marketplace.region|escape:'htmlall':'UTF-8'}" data-iso-code="{$marketplace.region|escape:'htmlall':'UTF-8'}" {if $smarty.foreach.marketplace.iteration > 1} style="display:none"{/if} class="col-lg-12">
                    <span id="rakuten-action-loader" style="display:none">
                        <img src="{$img|escape:'quotes':'UTF-8'}/green-loader.gif"/>
                    </span>
                    {if $product_tab.rakuten.show_countries}
                        <h4 class="marketplace-heading"><img src="{$marketplace.image|escape:'quotes':'UTF-8'}" alt="{$marketplace.name|escape:'quotes':'UTF-8'}"/>
                            {$marketplace.name|escape:'quotes':'UTF-8'}
                        </h4>
                        <div class="clearfix"></div>
                    {/if}

                    {include file="{$module_path|escape:'quotes':'UTF-8'}/views/templates/admin/catalog/product_tab_rakuten_subtab.tpl" data=$product_tab.rakuten.product_options.options[$marketplace.region] region=$marketplace.region complex_id=$product_tab.rakuten.complex_id hidden=false}

                    {if is_array($product_tab.rakuten.product_options.combinations_options) && count($product_tab.rakuten.product_options.combinations_options) && isset($product_tab.rakuten.product_options.combinations_options[$marketplace.region]) && count($product_tab.rakuten.product_options.combinations_options[$marketplace.region])}
                        {foreach from=$product_tab.rakuten.product_options.combinations_options[$marketplace.region] key=complex_id item=combination_option}
                            {include file="{$module_path|escape:'quotes':'UTF-8'}/views/templates/admin/catalog/product_tab_rakuten_subtab.tpl" data=$combination_option region=$marketplace.region hidden=true}
                        {/foreach}
                    {/if}
                </div>
            {/foreach}
        </div>
    </div>
</div>
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

{*<div class="mirakl-sub-tab marketplace-tab{if !$hidden} main{/if}" data-iso-code="{$region|escape:'htmlall':'UTF-8'}" data-complex-id="{$complex_id|escape:'quotes':'UTF-8'}" {if $hidden}style="display:none"{/if}>*}

{$index = 0}
{foreach $data.products as $complex_id => $product}
    <div class="mirakl-sub-tab marketplace-tab" data-iso-code="{$region|escape:'htmlall':'UTF-8'}" data-complex-id="{$complex_id|escape:'quotes':'UTF-8'}" {if $index > 0}style="display:none"{/if} >
      <div class="section">
        <div class="mirakl-tab-product-title">{$data['name']|escape:'quotes':'UTF-8'}</div>
        <input type="hidden" name="id_product_attribute" value="{if isset($product['combinations_options']['id_product_attribute'])}{$product['combinations_options']['id_product_attribute']|intval}{/if}"/>
        <input type="hidden" name="region" value="{$data['region']|escape:'htmlall':'UTF-8'}"/>
        <input type="hidden" name="context" value="mirakl"/>
        <input type="hidden" name="sub_marketplace" value="{$data['sub_marketplace']|escape:'htmlall':'UTF-8'}" />
      </div>


      <div class="section">
        <h4>{l s='Options' mod='feedbiz'}</h4>
        <div>
          <table class="mirakl-options">
            {if isset($product['combinations_options']) && !empty($product['combinations_options'])}
                {include file="{$module_path|escape:'quotes':'UTF-8'}views/templates/admin/catalog/mirakl/unavailable.tpl" data=$product['combinations_options']['unavailable']}
                {include file="{$module_path|escape:'quotes':'UTF-8'}views/templates/admin/catalog/mirakl/force_in_stock.tpl" data=$product['combinations_options']['force_in_stock']}
                {include file="{$module_path|escape:'quotes':'UTF-8'}views/templates/admin/catalog/mirakl/extra_price.tpl" data=$product['combinations_options']['extra_price']}
                {include file="{$module_path|escape:'quotes':'UTF-8'}views/templates/admin/catalog/mirakl/shipping_overrides.tpl" data=$product['combinations_options']['shipping_overrides']}
                {include file="{$module_path|escape:'quotes':'UTF-8'}views/templates/admin/catalog/mirakl/logistics_class.tpl" data=$product['combinations_options']['logistics_class']}
                {include file="{$module_path|escape:'quotes':'UTF-8'}views/templates/admin/catalog/mirakl/leadtime_ship.tpl" data=$product['options']['leadtime_ship']}
            {else}
                {include file="{$module_path|escape:'quotes':'UTF-8'}views/templates/admin/catalog/mirakl/unavailable.tpl" data=$product['options']['unavailable']}
                {include file="{$module_path|escape:'quotes':'UTF-8'}views/templates/admin/catalog/mirakl/force_in_stock.tpl" data=$product['options']['force_in_stock']}
                {include file="{$module_path|escape:'quotes':'UTF-8'}views/templates/admin/catalog/mirakl/extra_price.tpl" data=$product['options']['extra_price']}
                {include file="{$module_path|escape:'quotes':'UTF-8'}views/templates/admin/catalog/mirakl/shipping_overrides.tpl" data=$product['options']['shipping_overrides']}
                {include file="{$module_path|escape:'quotes':'UTF-8'}views/templates/admin/catalog/mirakl/logistics_class.tpl" data=$product['options']['logistics_class']}
                {include file="{$module_path|escape:'quotes':'UTF-8'}views/templates/admin/catalog/mirakl/leadtime_ship.tpl" data=$product['options']['leadtime_ship']}
            {/if}
          </table>
        </div>
      </div>
    </div>
    <span style="display:none">{$index++|intval}</span>
{/foreach}






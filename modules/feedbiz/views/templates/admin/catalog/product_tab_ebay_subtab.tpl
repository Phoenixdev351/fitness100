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

<div class="ebay-sub-tab marketplace-tab{if !$hidden} main{/if}" data-iso-code="{$region|escape:'htmlall':'UTF-8'}"
     data-complex-id="{$complex_id|escape:'quotes':'UTF-8'}" {if $hidden}style="display:none"{/if}>

    <div class="section">
        <div class="ebay-tab-product-title">{$data['name']|escape:'quotes':'UTF-8'}</div>
        <input type="hidden" name="id_product_attribute" value="{$data['id_product_attribute']|intval}"/>
        <input type="hidden" name="region" value="{$data['region']|escape:'htmlall':'UTF-8'}"/>
        <input type="hidden" name="context" value="ebay"/>
    </div>

    <div class="section">
        <h4>{l s='Options' mod='feedbiz'}</h4>

        <div>
            <table class="ebay-options">
                {include file="{$module_path|escape:'quotes':'UTF-8'}views/templates/admin/catalog/ebay/extra_price.tpl" data=$data['extra_price']}
                {include file="{$module_path|escape:'quotes':'UTF-8'}views/templates/admin/catalog/ebay/unavailable.tpl" data=$data['unavailable']}
                {include file="{$module_path|escape:'quotes':'UTF-8'}views/templates/admin/catalog/ebay/force_in_stock.tpl" data=$data['force_in_stock']}
            </table>
        </div>
    </div>
</div>
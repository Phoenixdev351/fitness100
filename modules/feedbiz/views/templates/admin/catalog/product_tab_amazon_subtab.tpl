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

<div class="amazon-sub-tab marketplace-tab{if !$hidden} main{/if}" data-iso-code="{$region|escape:'htmlall':'UTF-8'}"
     data-complex-id="{$complex_id|escape:'quotes':'UTF-8'}" {if $hidden}style="display:none"{/if}>

    <div class="section">
        <div class="amazon-tab-product-title">{$data['name']|escape:'quotes':'UTF-8'}</div>
        <input type="hidden" name="id_product_attribute" value="{$data['id_product_attribute']|intval}"/>
        <input type="hidden" name="region" value="{$data['region']|escape:'htmlall':'UTF-8'}"/>
        <input type="hidden" name="context" value="amazon"/>
    </div>

    <div class="section">
        <h4>{l s='Data' mod='feedbiz'}</h4>

        <div>
            <table class="amazon-datas">
                {include file="{$module_path|escape:'quotes':'UTF-8'}views/templates/admin/catalog/amazon/bullet_points.tpl" data=$data['bullet_points']}

                {include file="{$module_path|escape:'quotes':'UTF-8'}views/templates/admin/catalog/amazon/extra_text.tpl" data=$data['extra_text']}
            </table>
        </div>
    </div>

    <div class="section">
        <h4>{l s='Options' mod='feedbiz'}</h4>

        <div>
            <table class="amazon-options">
                {include file="{$module_path|escape:'quotes':'UTF-8'}views/templates/admin/catalog/amazon/asin.tpl" data=$data['asin']}
                {include file="{$module_path|escape:'quotes':'UTF-8'}views/templates/admin/catalog/amazon/extra_price.tpl" data=$data['extra_price']}
                {include file="{$module_path|escape:'quotes':'UTF-8'}views/templates/admin/catalog/amazon/unavailable.tpl" data=$data['unavailable']}
                {include file="{$module_path|escape:'quotes':'UTF-8'}views/templates/admin/catalog/amazon/force_in_stock.tpl" data=$data['force_in_stock']}

                {if isset($data['nopexport'])}
                    {include file="{$module_path|escape:'quotes':'UTF-8'}views/templates/admin/catalog/amazon/nopexport.tpl" data=$data['nopexport']}
                {/if}

                {if isset($data['noqexport'])}
                    {include file="{$module_path|escape:'quotes':'UTF-8'}views/templates/admin/catalog/amazon/noqexport.tpl" data=$data['noqexport']}
                {/if}

                {if isset($data['fba_option'])}
                    {include file="{$module_path|escape:'quotes':'UTF-8'}views/templates/admin/catalog/amazon/fba_option.tpl" data=$data['fba_option']}
                {/if}

                {if isset($data['fba_value'])}
                    {include file="{$module_path|escape:'quotes':'UTF-8'}views/templates/admin/catalog/amazon/fba_value.tpl" data=$data['fba_value']}
                {/if}

                {include file="{$module_path|escape:'quotes':'UTF-8'}views/templates/admin/catalog/amazon/latency.tpl" data=$data['latency']}
                {include file="{$module_path|escape:'quotes':'UTF-8'}views/templates/admin/catalog/amazon/gift.tpl" data=$data['gift']}

                {if isset($data['shipping_group'])}
                    {include file="{$module_path|escape:'quotes':'UTF-8'}views/templates/admin/catalog/amazon/shipping_group.tpl" data=$data['shipping_group']}
                {/if}

                {if isset($data['shipping_overrides'])}
                    {include file="{$module_path|escape:'quotes':'UTF-8'}views/templates/admin/catalog/amazon/shipping_overrides.tpl" data=$data['shipping_overrides']}
                {/if}
                
                {if isset($data['browsenode'])}
                    {include file="{$module_path|escape:'quotes':'UTF-8'}views/templates/admin/catalog/amazon/browsenode.tpl" data=$data['browsenode']}
                {/if}

                {if isset($data['go_amazon'])}
                    {include file="{$module_path|escape:'quotes':'UTF-8'}views/templates/admin/catalog/amazon/go_amazon.tpl" data=$data['go_amazon']}
                {/if}

                {if isset($data['wozapi'])}
                    {include file="{$module_path|escape:'quotes':'UTF-8'}views/templates/admin/catalog/amazon/wozapi.tpl" data=$data['wozapi']}
                {/if}
            </table>
        </div>
    </div>

    <div class="section">
        <h4>{l s='Repricing' mod='feedbiz'}</h4>

        <div>
            <table class="amazon-options">
                {include file="{$module_path|escape:'quotes':'UTF-8'}views/templates/admin/catalog/amazon/repricing.tpl" data=$data['repricing']}
            </table>
        </div>
    </div>
</div>
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

<tr>
            <td colspan="2">
            <div id="productfeedbiz-options">
            <img src="{$images|escape:'htmlall':'UTF-8'}logo.gif" alt="" />
            &nbsp;&nbsp;<b>Feed.biz </b>&nbsp;&nbsp;&nbsp;
            <span style="color:grey">[</span>
            <img src="{$images|escape:'htmlall':'UTF-8'}plus.png" rel="{$images|escape:'htmlall':'UTF-8'}minus.png" alt="" style="position:relative;top:-1px;" id="feedbiz-toggle-img" />
            <span style="color:grey;margin-left:-1px;">]</span>
            </div>
            </td>
        </tr>
        <tr class="feedbiz-details">
            <td style="padding-bottom:5px;"><br />
            <input type="hidden" name="id_product" value="{$id_product|intval}" />
            <input type="hidden" name="feedbiz_option_lang[]" value="{$id_lang|intval}" />
            <input type="hidden" id="feedbiz-text-propagate-cat" value="{l s='Be carefull ! Are you sure to want set this value for all the products of this Category ?' mod='feedbiz'}" />
            <input type="hidden" id="feedbiz-text-propagate-shop" value="{l s='Be carefull ! Are you sure to want to set this value for all the products of the entire Shop ?' mod='feedbiz'}" />
            <input type="hidden" id="feedbiz-text-propagate-man" value="{l s='Be carefull ! Are you sure to want to set this value for all the products for this Manufacturer ?' mod='feedbiz'}" />
            </td>
        </tr>
        <tr class="feedbiz-details">
            <td class="col-left">{l s='Disabled' mod='feedbiz'}: </td>
            <td style="padding-bottom:5px;">
            <input type="checkbox" name="feedbiz-disable-{$id_lang|intval}" value="1" {$forceUnavailableChecked|escape:'htmlall':'UTF-8'} />
            <span style="margin-left:10px">{l s='Check this box to make this product unavailable on Feed.biz' mod='feedbiz'}</span><br />
            <span style="font-size:0.9em;color:grey;line-height:150%">{l s='Make all the products unavailable in this' mod='feedbiz'} :
                <a href="javascript:void(0)" class="feedbiz-propagate-disable-cat propagate">[ {l s='Category' mod='feedbiz'} ]</a>&nbsp;&nbsp;
                <a href="javascript:void(0)" class="feedbiz-propagate-disable-shop propagate">[ {l s='Shop' mod='feedbiz'} ]</a>&nbsp;&nbsp;
                <a href="javascript:void(0)" class="feedbiz-propagate-disable-manufacturer propagate">[ {l s='Manufacturer' mod='feedbiz'} ]</a></span></span>
            <span id="feedbiz-extra-disable-loader" style="display:none"><img src="{$images|escape:'htmlall':'UTF-8'}green-loader.gif" style="margin-left:5px;" alt="" /></span>
            </td>
        </tr>
        <tr class="feedbiz-details">
            <td class="col-left">{l s='Force in Stock' mod='feedbiz'}: </td>
            <td style="padding-bottom:5px;">
            <input type="checkbox" name="feedbiz-force-{$id_lang|intval}" value="1" {$forceInStockChecked|escape:'htmlall':'UTF-8'} />
            <span style="margin-left:10px">{l s='The product will always appear on feedbiz, even it\'s out of Stock' mod='feedbiz'}</span><br />
            <span style="font-size:0.9em;color:grey;line-height:150%">{l s='Force as available in stock for all products in this' mod='feedbiz'} :
                <a href="javascript:void(0)" class="feedbiz-propagate-force-cat propagate">[ {l s='Category' mod='feedbiz'} ]</a>&nbsp;&nbsp;
                <a href="javascript:void(0)" class="feedbiz-propagate-force-shop propagate">[ {l s='Shop' mod='feedbiz'} ]</a>&nbsp;&nbsp;
                <a href="javascript:void(0)" class="feedbiz-propagate-force-manufacturer propagate">[ {l s='Manufacturer' mod='feedbiz'} ]</a></span></span>
            <span id="feedbiz-extra-force-loader" style="display:none"><img src="{$images|escape:'htmlall':'UTF-8'}green-loader.gif" style="margin-left:5px;" alt="" /></span>
            </td>
        </tr>
        
        <tr class="feedbiz-details">
            <td class="col-left">{l s='Price Override' mod='feedbiz'}: </td>
            <td style="padding-bottom:5px;">
            <input type="text" name="feedbiz-price-{$id_lang|intval}" value="{$extraPrice|escape:'htmlall':'UTF-8'}" style="width:95px" /><br />
            <span style="font-size:0.9em;color:grey;line-height:150%">{l s='Net Price for Feed.biz . This value will override your Shop Price' mod='feedbiz'}</span><br />
            </td>
        </tr>
        
        <tr class="feedbiz-details">
            <td class="col-left">{l s='Shipping Delay Override' mod='feedbiz'}: </td>
            <td style="padding-bottom:5px;">
            <input type="text" name="feedbiz-shipping-delay-{$id_lang|intval}" value="{$shippingDelay|escape:'htmlall':'UTF-8'}" style="width:95px" /><br />
            <span style="font-size:0.9em;color:grey;line-height:150%">{l s='' mod='feedbiz'}</span><br />
            </td>
        </tr>

        <tr class="feedbiz-details">
            <td colspan="2" style="padding-bottom:5px;">
                <hr style="margin-left:25%;width:50%" />
                <span style="color:brown;font-weight:bold;font-size:0.8em">{l s='Don\'t forget to click on the record button linked to this sub-tab if you modify this configuration !' mod='feedbiz'}</span>
            </td>
        </tr>
        <tr class="feedbiz-details">
          <td class="col-left"></td>
          <td style="padding:0 30px 5px 0;float:right;">
            <div class="conf" style="display:none" id="result-feedbiz"></div>
            <input type="button" style="float:right" id="productfeedbiz-save-options" class="button" value="{l s='Save Feed.biz  Parameters' mod='feedbiz'}" />
          </td>
        </tr>
        {if isset($PS14)}
        <tr>
            <td colspan="2" style="padding-bottom:5px;"><hr style="width:100%" /></td>
        </tr>
        {/if}
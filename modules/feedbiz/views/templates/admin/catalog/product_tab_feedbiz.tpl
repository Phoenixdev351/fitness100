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

<script type="text/javascript" src="{$module_url|escape:'htmlall':'UTF-8'}views/js/product_tab_feedbiz.js?v={$version|escape:'htmlall':'UTF-8'}"></script>

{if isset($product_tab.feedbiz)}     
    <div id="feedbiz-sub-tabs">
        {foreach from=$product_tab.feedbiz key=complex_id item=product_option}
            <div id="feedbiz-product-options-{$complex_id|escape:'htmlall':'UTF-8'}"
                 class="feedbiz-product-options marketplace-tab{if !$product_option.id_product_attribute} main{/if}"
                 data-complex-id="{$complex_id|escape:'htmlall':'UTF-8'}"
                 {if $product_option.id_product_attribute != 0}style="display:none"{/if}>
                <input type="hidden" name="id_product_attribute"
                       value="{$product_option.id_product_attribute|intval}"/>
                <input type="hidden" name="context" value="feedbiz"/>

                <div style="width:100%;text-align:right">
                    <em>{$product_option.title|escape:'htmlall':'UTF-8'}</em>
                </div>
                <table class="product-options">

                    <tr class="feedbiz-details">
                        <td style="padding-bottom:5px;"><br/>
                            <input type="hidden" name="entity" value="feedbiz"/>
                        </td>
                    </tr>

                    {if array_key_exists('disable', $product_option)}
                        <tr class="feedbiz-details">
                            <td class="column-left">{l s='Disabled' mod='feedbiz'}</td>
                            <td style="padding-bottom:5px;">
                                <input type="checkbox" name="disable" value="1"
                                       {if $product_option.disable}checked{/if} />
                                <span style="margin-left:10px">{l s='Check this box to make this product unavailable on Feed.biz' mod='feedbiz'}</span><br/>
                            <span style="font-size:0.9em;color:grey;line-height:150%"
                                  class="propagation">{l s='Make all the products unavailable in this' mod='feedbiz'}
                                :
                                <a href="javascript:void(0)"
                                   class="feedbiz-propagate-disable-cat propagate">[ {l s='Category' mod='feedbiz'}
                                    ]</a>&nbsp;&nbsp;
                                <a href="javascript:void(0)"
                                   class="feedbiz-propagate-disable-shop propagate">[ {l s='Shop' mod='feedbiz'}
                                    ]</a>&nbsp;&nbsp;
                                <a href="javascript:void(0)"
                                   class="feedbiz-propagate-disable-manufacturer propagate">[ {l s='Manufacturer' mod='feedbiz'}
                                    ]</a></span></span>
                                <span id="feedbiz-extra-disable-loader" style="display:none"><img
                                            src="{$img|escape:'htmlall':'UTF-8'}"/>green-loader.gif" style="margin-left:5px;" alt=""/></span>
                            </td>
                        </tr>
                    {/if}

                    {if array_key_exists('force', $product_option)}
                        <tr class="feedbiz-details">
                            <td class="column-left">{l s='Force in Stock' mod='feedbiz'}</td>
                            <td style="padding-bottom:5px;">


                                <input type="text" name="force"
                                       value="{$product_option.force|escape:'htmlall':'UTF-8'}" style="width:95px"/>
                                <span style="margin-left:10px">{l s='The product will always appear on Feed.biz, even it\'s out of Stock' mod='feedbiz'}</span><br/>
                            <span style="font-size:0.9em;color:grey;line-height:150%"
                                  class="propagation">{l s='Force as available in stock for all products in this' mod='feedbiz'}
                                :
                                <a href="javascript:void(0)"
                                   class="feedbiz-propagate-force-cat propagate">[ {l s='Category' mod='feedbiz'}
                                    ]</a>&nbsp;&nbsp;
                                <a href="javascript:void(0)"
                                   class="feedbiz-propagate-force-shop propagate">[ {l s='Shop' mod='feedbiz'} ]</a>&nbsp;&nbsp;
                                <a href="javascript:void(0)" class="feedbiz-propagate-force-manufacturer propagate">[ {l s='Manufacturer' mod='feedbiz'}
                                    ]</a></span></span>
                                <span id="feedbiz-extra-force-loader" style="display:none"><img
                                            src="{$img|escape:'htmlall':'UTF-8'}"/>green-loader.gif" style="margin-left:5px;" alt=""/></span>
                            </td>
                        </tr>
                    {/if}

                    {if array_key_exists('price', $product_option)}
                        <tr class="feedbiz-details">
                            <td class="column-left">{l s='Price Override' mod='feedbiz'}</td>
                            <td style="padding-bottom:5px;">
                                <input type="text" name="price_override"
                                       value="{$product_option.price|escape:'htmlall':'UTF-8'}"
                                       class="marketplace-price" style="width:95px"/>
                                <span style="font-size:0.9em;color:grey;line-height:150%">{l s='Net Price for Feed.biz . This value will override your Shop Price' mod='feedbiz'}</span><br/>
                            </td>
                        </tr>
                    {/if}

                    {if array_key_exists('shipping', $product_option)}
                        <tr class="feedbiz-details">
                            <td class="column-left">{l s='Shipping Override' mod='feedbiz'}</td>
                            <td style="padding-bottom:5px;">
                                <input type="text" name="shipping"
                                       value="{$product_option.shipping|escape:'htmlall':'UTF-8'}"
                                       class="marketplace-price" style="width:95px"/><br/>
                            </td>
                        </tr>
                    {/if}
                </table>
            </div>
        {/foreach}
    </div>
{/if}
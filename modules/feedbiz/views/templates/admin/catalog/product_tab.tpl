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

<link href="{$module_url|escape:'htmlall':'UTF-8'}views/css/product_tab.css?v={$version|escape:'htmlall':'UTF-8'}" rel="stylesheet" type="text/css"/>

{if isset($shop_warning) && $shop_warning}
    <div class="form-group">
      <div class="margin-form col-lg-12">
        <div class="{$class_warning|escape:'htmlall':'UTF-8'}">
          {$shop_warning|escape:'htmlall':'UTF-8'}
        </div>
      </div>
    </div>
{else}
    <div id="feedbiz-product-tab">

      <script type="text/javascript" src="{$module_url|escape:'quotes':'UTF-8'}views/js/product_tab.js"></script>

      <input type="hidden" value="{$json_url|escape:'quotes':'UTF-8'}" id="feedbiz-product-options-json-url"/>
      <input type="hidden" value="{l s='Parameters successfully saved' mod='feedbiz'}"
             id="feedbiz-product-options-message-success"/>
      <input type="hidden" value="{l s='Unable to save parameters...' mod='feedbiz'}"
             id="feedbiz-product-options-message-error"/>
      <input type="hidden" value="{l s='Copied' mod='feedbiz'}" id="feedbiz-product-options-copy"/>
      <input type="hidden" value="{l s='Pasted' mod='feedbiz'}" id="feedbiz-product-options-paste"/>

      <input type="hidden" id="marketplace-text-propagate-cat"
             value="{l s='Be careful ! Are you sure to want set this value for all the products of this Category ?' mod='feedbiz'}"/>
      <input type="hidden" id="marketplace-text-propagate-shop"
             value="{l s='Be careful ! Are you sure to want to set this value for all the products of the entire Shop ?' mod='feedbiz'}"/>
      <input type="hidden" id="marketplace-text-propagate-man"
             value="{l s='Be careful ! Are you sure to want to set this value for all the products for this Manufacturer ?' mod='feedbiz'}"/>

      <div id="feedbiz-global-values">
        <input type="hidden" name="feedbiz_token" value="{$token|escape:'htmlall':'UTF-8'}" id="feedbiz_token"/>
        <input type="hidden" name="id_product" value="{$product_tab.id_product|intval}" id="id-product"/>
        <input type="hidden" name="feedbiz_id_manufacturer" value="{$product_tab.id_manufacturer|intval}"/>
        <input type="hidden" name="feedbiz_id_category_default" value="{$product_tab.id_category_default|intval}"/>
        <input type="hidden" name="feedbiz_id_supplier" value="{$product_tab.id_supplier|intval}"/>
      </div>

      <div class="panel">
        <h3 class="tab">&nbsp;&nbsp;{l s='Product' mod='feedbiz'}</h3>

        <div class="form-group" style="margin-bottom: 25px;">
          <table id="feedbiz-table-product" class="table feedbiz-item">
            <thead>
              <tr class="nodrag nodrop">
                <th>
                </th>
                <th class="left title">
                  <span class="title_box">{l s='Name' mod='feedbiz'}</span><!-- Validation: Prestashop translations -->
                </th>
                <th class="left reference">
                  <span class="title_box">{l s='Reference code' mod='feedbiz'}</span>
                  <!-- Validation: Prestashop translations -->
                </th>
                <th class="left reference">
                  <span class="title_box">EAN13</span>
                </th>
                <th class="left reference">
                  <span class="title_box">UPC</span>
                </th>
                <th class="center action"></th>
                <th class="center action"></th>
                <th class="center action"></th>
              </tr>
            </thead>

            <tbody>
              <tr class="highlighted" rel="{$product_tab.id_product|escape:'htmlall':'UTF-8'}_0">
                <td class="left">
                  <input type="radio" id="feedbiz-item-radio" name="complex_id_product"
                         value="{$product_tab.product.complex_id|escape:'htmlall':'UTF-8'}"
                         data-id-product="{$product_tab.product.id_product|intval}"
                         data-id-product-attribute="0" checked>
                </td>
                <td class="left title" rel="name">
                  {$product_tab.product.name|escape:'html':'UTF-8'}
                </td>
                <td class="left feedbiz-editable reference" rel="reference">
                  {$product_tab.product.reference|escape:'html':'UTF-8'}
                </td>
                <td class="left feedbiz-editable reference" rel="ean13">
                  {$product_tab.product.ean13|escape:'html':'UTF-8'}
                </td>
                <td class="left feedbiz-editable reference" rel="upc">
                  {$product_tab.product.upc|escape:'html':'UTF-8'}
                </td>
                <td class="center action">
                  <img src="{$img|escape:'htmlall':'UTF-8'}cross.png" class="delete-product-option"
                       rel="{$product_tab.product.complex_id|escape:'htmlall':'UTF-8'}"
                       title="{l s='Delete product option entry' mod='feedbiz'}"/>
                </td>
                <td class="center action">
                  <img src="{$img|escape:'htmlall':'UTF-8'}page_white_copy.png" class="copy-product-option"
                       rel="{$product_tab.product.complex_id|escape:'htmlall':'UTF-8'}"
                       title="{l s='Copy product option entry' mod='feedbiz'}"/>
                </td>
                <td class="center action">
                  <img src="{$img|escape:'htmlall':'UTF-8'}paste_plain.png" class="paste-product-option"
                       rel="{$product_tab.product.complex_id|escape:'htmlall':'UTF-8'}"
                       title="{l s='Paste product option entry' mod='feedbiz'}"/>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        {if is_array($product_tab.combinations) && count($product_tab.combinations)}
            <div class="form-group">
              <h3 class="tab">&nbsp;&nbsp;{l s='Combinations' mod='feedbiz'}</h3><!-- Validation: Prestashop translations -->

              <div class="table-responsive">
                <table id="feedbiz-table-combinations" class="table feedbiz-item">
                  <thead>
                    <tr class="nodrag nodrop">
                      <th></th>
                      <th class="left title">
                        <span class="title_box">{l s='Attribute' mod='feedbiz'}</span>
                        <!-- Validation: Prestashop translations -->
                      </th>
                      <th class="left reference">
                        <span class="title_box">{l s='Reference code' mod='feedbiz'}</span>
                        <!-- Validation: Prestashop translations -->
                      </th>
                      <th class="left reference">
                        <span class="title_box">EAN13</span>
                      </th>
                      <th class="left reference">
                        <span class="title_box">UPC</span>
                      </th>
                      <th class="center action"></th>
                      <th class="center action"></th>
                      <th class="center action"></th>
                    </tr>
                  </thead>

                  <tbody>
                    {foreach from=$product_tab.combinations item=combination}
                        <tr rel="{$combination.complex_id|escape:'htmlall':'UTF-8'}">
                          <td class="left">
                            <input type="radio" id="feedbiz-item-radio" name="complex_id_product"
                                   value="{$combination.complex_id|escape:'htmlall':'UTF-8'}"
                                   data-id-product="{$product_tab.id_product|intval}"
                                   data-id-product-attribute="{$combination.id_product_attribute|intval}">
                          </td>
                          <td class="left" rel="name">
                            {$combination.name|escape:'html':'UTF-8'}
                          </td>
                          <td class="left feedbiz-editable" rel="reference">
                            {$combination.reference|escape:'html':'UTF-8'}
                          </td>
                          <td class="left feedbiz-editable" rel="ean13">
                            {$combination.ean13|escape:'html':'UTF-8'}
                          </td>
                          <td class="left feedbiz-editable" rel="upc">
                            {$combination.upc|escape:'html':'UTF-8'}
                          </td>
                          <td class="center action">
                            <img src="{$img|escape:'htmlall':'UTF-8'}cross.png"
                                 class="delete-product-option"
                                 rel="{$combination.complex_id|escape:'htmlall':'UTF-8'}"
                                 title="{l s='Delete product option entry' mod='feedbiz'}"/>
                          </td>
                          <td class="center action">
                            <img src="{$img|escape:'htmlall':'UTF-8'}page_white_copy.png"
                                 class="copy-product-option"
                                 rel="{$combination.complex_id|escape:'htmlall':'UTF-8'}"
                                 title="{l s='Copy product option entry' mod='feedbiz'}"/>
                          </td>
                          <td class="center action">
                            <img src="{$img|escape:'htmlall':'UTF-8'}paste_plain.png"
                                 class="paste-product-option"
                                 rel="{$combination.complex_id|escape:'htmlall':'UTF-8'}"
                                 title="{l s='Paste product option entry' mod='feedbiz'}"/>
                          </td>
                        </tr>
                    {/foreach}
                  </tbody>

                </table>
              </div>
              <div class="row">
                <div class="col-lg-6">
                </div>
              </div>

              <div class="clearfix"></div>
            </div>
        {/if}
      </div>

      <div class="panel">

        <table class="marketplace-selector">
          <tr>
            {if !$cdiscount_only}
                <td rel="feedbiz" class="active"><img src="{$img|escape:'htmlall':'UTF-8'}tabs/feed.png" title="Feed.Biz"/></td>
                {/if}
                {if isset($product_tab.amazon)}
                <td rel="amazon"><img src="{$img|escape:'htmlall':'UTF-8'}amazon.png" title="Amazon"/></td>
                {/if}
                {if isset($product_tab.ebay)}
                <td rel="ebay"><img src="{$img|escape:'htmlall':'UTF-8'}ebay.png" title="eBay"/></td>
                {/if}
                {if isset($product_tab.cdiscount)}
                <td rel="cdiscount"><img src="{$img|escape:'htmlall':'UTF-8'}cdiscount.png" title="Cdiscount"/></td>
                {/if}
                {if isset($product_tab.fnac)}
                <td rel="fnac"><img src="{$img|escape:'htmlall':'UTF-8'}fnac.png" title="Fnac"/></td>
                {/if}
                {if isset($product_tab.mirakl)}
                <td rel="mirakl"><img src="{$img|escape:'htmlall':'UTF-8'}mirakl.png" title="Mirakl"/></td>
                {/if}
                {if isset($product_tab.rakuten)}
                <td rel="rakuten"><img src="{$img|escape:'htmlall':'UTF-8'}rakuten.png" title="Rakuten"/></td>
                {/if}
          </tr>
          <tr>
            {if !$cdiscount_only}
                <td rel="feedbiz" class="active">Feed.Biz</td>
            {/if}
            {if isset($product_tab.amazon)}
                <td rel="amazon">Amazon</td>
            {/if}
            {if isset($product_tab.ebay)}
                <td rel="ebay">eBay</td>
            {/if}
            {if isset($product_tab.cdiscount)}
                <td rel="cdiscount">Cdiscount</td>
            {/if}
            {if isset($product_tab.fnac)}
                <td rel="fnac">Fnac</td>
            {/if}
            {if isset($product_tab.mirakl)}
                <td rel="mirakl">Mirakl</td>
            {/if}
            {if isset($product_tab.rakuten)}
                <td rel="rakuten">Rakuten</td>
            {/if}
          </tr>
        </table>

        <div class="platform-separator">
          <hr/>
        </div>

        {if !$cdiscount_only}
            <div id="feedbiz-product-subtab" class="marketplace-subtab" rel="feedbiz">
              {include file="{$module_path|escape:'quotes':'UTF-8'}/views/templates/admin/catalog/product_tab_feedbiz.tpl"}
            </div>
        {/if}

        {if isset($product_tab.amazon)}
            <div id="feedbiz-product-subtab-amazon" class="marketplace-subtab" rel="amazon" style="display:none">
              {include file="{$module_path|escape:'quotes':'UTF-8'}/views/templates/admin/catalog/product_tab_amazon.tpl"}
            </div>
        {/if}

        {if isset($product_tab.ebay)}
            <div id="feedbiz-product-subtab-ebay" class="marketplace-subtab" rel="ebay" style="display:none">
              {include file="{$module_path|escape:'quotes':'UTF-8'}/views/templates/admin/catalog/product_tab_ebay.tpl"}
            </div>
        {/if}

        {if isset($product_tab.cdiscount)}
            <div id="feedbiz-product-subtab-cdiscount" class="marketplace-subtab" rel="cdiscount" style="{if !$cdiscount_only}display: none;{/if}">
              {include file="{$module_path|escape:'quotes':'UTF-8'}/views/templates/admin/catalog/product_tab_cdiscount.tpl"}
            </div>
        {/if}

        {if isset($product_tab.fnac)}
            <div id="feedbiz-product-subtab-fnac" class="marketplace-subtab" rel="fnac" style="display: none;">
              {include file="{$module_path|escape:'quotes':'UTF-8'}/views/templates/admin/catalog/product_tab_fnac.tpl"}
            </div>
        {/if}
        {if isset($product_tab.mirakl.mirakl)}
            <div id="feedbiz-product-subtab-mirakl" class="marketplace-subtab" rel="mirakl" style="display: none;">
              <link href="{$module_url|escape:'htmlall':'UTF-8'}views/css/product_tab.mirakl.css?v={$version|escape:'htmlall':'UTF-8'}" rel="stylesheet" type="text/css"/>
              <script type="text/javascript" src="{$module_url|escape:'htmlall':'UTF-8'}views/js/product_tab_mirakl.js?v={$version|escape:'htmlall':'UTF-8'}"></script>
              {foreach from=$product_tab.mirakl.mirakl key=sub_marketplace item=marketplace}
                  {include file="{$module_path|escape:'quotes':'UTF-8'}/views/templates/admin/catalog/product_tab_mirakl.tpl"}
              {/foreach}
            </div>
        {/if}
        {if isset($product_tab.rakuten)}
            <div id="feedbiz-product-subtab-rakuten" class="marketplace-subtab" rel="rakuten" style="display: none;">
              {include file="{$module_path|escape:'quotes':'UTF-8'}/views/templates/admin/catalog/product_tab_rakuten.tpl"}
            </div>
        {/if}
      </div>
      <div class="debug"></div>
    </div>
{/if}
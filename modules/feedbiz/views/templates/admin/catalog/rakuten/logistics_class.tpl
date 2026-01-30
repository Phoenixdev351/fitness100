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

<tr class="rakuten-details rakuten-item-title">
  <td class="col-left" rel="logistics_class"><span>{l s='Logistics Class' mod='feedbiz'}</span></td>
  <td style="padding-bottom:5px;">
    {* <input type="text" name="logistics_class" value="{$data.default|escape:'htmlall':'UTF-8'}" style="width:60px"/>*}

    {*    <select class="modules-list-select" data-toggle="select2" name="shipping_group"> 
    <option value = ''>--</option>
    {foreach $data.shipping_templates as $key => $name} 			
    <option value = '{$key|escape:'htmlall':'UTF-8'}' {if $data.default == $key}selected="selected"{/if}>
    {$name|escape:'htmlall':'UTF-8'}
    </option>
    {/foreach}
    </select>
    *}
    {$value = $data.default|escape:'htmlall':'UTF-8'}
    <select {*class="modules-list-select"*} data-toggle="select2" name="logistics_class">
      <option value="">{l s='Default / None' mod='feedbiz'}</option>
      <optgroup label="{l s='Base categories' mod='feedbiz'}">
        <option value="101" {if $value == '101'}selected{/if}>{l s='Category A' mod='feedbiz'}</option>
        <option value="102" {if $value == '102'}selected{/if}>{l s='Category B' mod='feedbiz'}</option>
        <option value="103" {if $value == '103'}selected{/if}>{l s='Category C' mod='feedbiz'}</option>
        <option value="104" {if $value == '104'}selected{/if}>{l s='Category D' mod='feedbiz'}</option>
        <option value="105" {if $value == '105'}selected{/if}>{l s='Category E' mod='feedbiz'}</option>
        <option value="106" {if $value == '106'}selected{/if}>{l s='Category F' mod='feedbiz'}</option>
        <option value="107" {if $value == '107'}selected{/if}>{l s='Category G' mod='feedbiz'}</option>
        <option value="108" {if $value == '108'}selected{/if}>{l s='Category H' mod='feedbiz'}</option>
        <option value="109" {if $value == '109'}selected{/if}>{l s='Category I' mod='feedbiz'}</option>
        <option value="110" {if $value == '110'}selected{/if}>{l s='Category J' mod='feedbiz'}</option>
        <option value="111" {if $value == '111'}selected{/if}>{l s='Category K' mod='feedbiz'}</option>
      </optgroup>
      <optgroup label="{l s='Custom categories' mod='feedbiz'}">
        <option value="201" {if $value == '201'}selected{/if}>{l s='Custom Category #1' mod='feedbiz'}</option>
        <option value="202" {if $value == '202'}selected{/if}>{l s='Custom Category #2' mod='feedbiz'}</option>
        <option value="203" {if $value == '203'}selected{/if}>{l s='Custom Category #3' mod='feedbiz'}</option>
        <option value="204" {if $value == '204'}selected{/if}>{l s='Custom Category #4' mod='feedbiz'}</option>
        <option value="205" {if $value == '205'}selected{/if}>{l s='Custom Category #5' mod='feedbiz'}</option>
      </optgroup>
      <optgroup label="{l s='Large Products Categorie' mod='feedbiz'}">
        <option value="206" {if $value == '206'}selected{/if}>{l s='Large Products Category #1 (qualified sellers only)' mod='feedbiz'}</option>
        <option value="207" {if $value == '207'}selected{/if}>{l s='Large Products Category #2 (qualified sellers only)' mod='feedbiz'}</option>
        <option value="208" {if $value == '208'}selected{/if}>{l s='Large Products Category #3 (qualified sellers only)' mod='feedbiz'}</option>
        <option value="209" {if $value == '209'}selected{/if}>{l s='Large Products Category #4 (qualified sellers only)' mod='feedbiz'}</option>
        <option value="210" {if $value == '210'}selected{/if}>{l s='Large Products Category #5 (qualified sellers only)' mod='feedbiz'}</option>
      </optgroup>
    </select>

    <br/>
    <span class="amz-small-line">{l s='Rakuten Logistics Class.' mod='feedbiz'}</span><br/>
    <span class="amz-small-line propagation">{l s='Propagate this shipping price to all products in this' mod='feedbiz'}
      :
      <a href="javascript:void(0)"
         class="fb-propagate-shipping-cat fb-link propagate">[ {l s='Category' mod='feedbiz'} ]</a>&nbsp;&nbsp;
      <a href="javascript:void(0)"
         class="fb-propagate-shipping-shop fb-link propagate">[ {l s='Shop' mod='feedbiz'} ]</a>&nbsp;&nbsp;
      <a href="javascript:void(0)"
         class="fb-propagate-shipping-manufacturer fb-link propagate">[ {l s='Manufacturer' mod='feedbiz'} ]</a>&nbsp;&nbsp;
      <a href="javascript:void(0)"
         class="fb-propagate-shipping-supplier fb-link propagate">[ {l s='Supplier' mod='feedbiz'} ]</a>&nbsp;&nbsp;
    </span>
  </td>
</tr>

{**
 * 2007-2016 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2016 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}
<div class="product-variants js-product-variants">{*important refresh*}
  {foreach from=$groups key=id_attribute_group item=group}
    {if !empty($group.attributes)}
    {assign var=selected_keys value=[]}
    {if is_array($product.attributes) && count($product.attributes)}
    {foreach $product.attributes as $av}
        {if !array_key_exists($av['id_attribute'], $group['attributes'])}
          {$selected_keys[]=$av['id_attribute']}
        {/if}
    {/foreach}
    {/if}
    <div class="clearfix product-variants-item">
      <span class="control-label">{$group.name}</span>
      {if $group.group_type == 'select'}
        <select
          class="form-control form-control-select"
          id="group_{$id_attribute_group}"
          data-product-attribute="{$id_attribute_group}"
          name="group[{$id_attribute_group}]">
          {foreach from=$group.attributes key=id_attribute item=group_attribute}
            <option value="{$id_attribute}" title="{$group_attribute.name}"{if $group_attribute.selected} selected="selected"{/if}>{$group_attribute.name}</option>
          {/foreach}
        </select>
      {elseif $group.group_type == 'color'}
        <ul id="group_{$id_attribute_group}" class="clearfix li_fl">
          {foreach from=$group.attributes key=id_attribute item=group_attribute}
            {assign var=temp_selected_keys value=$selected_keys}
            {assign var=is_available value=1}
            {$temp_selected_keys[]=$id_attribute}
            {foreach $combinations as $bv}
              {if count(array_diff($bv['attributes'], $temp_selected_keys)) === 0 && !$bv['quantity']}
                {$is_available=0}
              {/if}
            {/foreach}
            <li class="input-container {if !$is_available} st_unavailable_combination {/if}" title="{$group_attribute.name}">
              <input class="input-color" type="radio" data-product-attribute="{$id_attribute_group}" name="group[{$id_attribute_group}]" value="{$id_attribute}" title="{$group_attribute.name}" {if $group_attribute.selected} checked="checked"{/if}/>
              {if isset($st_attr_img_as_texture) && isset($st_attr_img_as_texture[$id_attribute_group][$id_attribute])}{$group_attribute.texture=$st_attr_img_as_texture[$id_attribute_group][$id_attribute]}{$group_attribute.html_color_code=''}{/if}
              <span class="color {if $group_attribute.texture}texture{/if}"
                {if $group_attribute.html_color_code && !$group_attribute.texture} style="background-color: {$group_attribute.html_color_code}" {/if}
                {if $group_attribute.texture} style="background-image: url({$group_attribute.texture})" {/if}
              ><span class="sr-only">{$group_attribute.name}</span></span>
              <span class="st-input-loading"><i class="fto-spin5 animate-spin"></i></span>
            </li>
          {/foreach}
        </ul>
      {elseif $group.group_type == 'radio'}
        <ul id="group_{$id_attribute_group}" class="clearfix li_fl">
          {foreach from=$group.attributes key=id_attribute item=group_attribute}
            {assign var=temp_selected_keys value=$selected_keys}
            {assign var=is_available value=1}
            {$temp_selected_keys[]=$id_attribute}
            {foreach $combinations as $bv}
              {if count(array_diff($bv['attributes'], $temp_selected_keys)) === 0 && !$bv['quantity']}
                {$is_available=0}
              {/if}
            {/foreach}
            <li class="input-container {if !$is_available} st_unavailable_combination {/if}" title="{$group_attribute.name}">
              <input class="input-radio" type="radio" data-product-attribute="{$id_attribute_group}" name="group[{$id_attribute_group}]" value="{$id_attribute}" title="{$group_attribute.name}" {if $group_attribute.selected} checked="checked"{/if}/>
              <span class="radio-label">{$group_attribute.name}</span>
              <span class="st-input-loading"><i class="fto-spin5 animate-spin"></i></span>
            </li>
          {/foreach}
        </ul>
      {/if}
    </div>
    {/if}
  {/foreach}
  {hook h='displayUnderProductVariants'}
</div>
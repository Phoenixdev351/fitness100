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
{assign var="enable_number_per_page" value=Configuration::get('STSN_ENABLE_NUMBER_PER_PAGE')}
{if $enable_number_per_page || (isset($steasybuilder) && $steasybuilder.is_editing)}
  {assign var="number_per_page_str" value=Configuration::get('STSN_NUMBER_PER_PAGE')}
  {if $number_per_page_str}
    {assign var="number_per_page" value=","|explode:$number_per_page_str}
  {else}
    {assign var="number_per_page" value=[20,40,60,10000]}
  {/if}
  <div class="products-number-per-page dropdown_wrap mar_r1 {if !$enable_number_per_page} display_none {/if}">
    <a href="javascript:" class="dropdown_tri dropdown_tri_in" rel="nofollow" aria-haspopup="true" aria-expanded="false">
      {$listing.products|count}
      <i class="fto-angle-down arrow_down arrow"></i>
      <i class="fto-angle-up arrow_up arrow"></i>
    </a>
    <div class="dropdown_list">
      <ul class="dropdown_list_ul dropdown_box">
      {assign var="number_per_page_url" value=""}
      {foreach from=$listing.sort_orders item=sort_order}
        {if $sort_order.current}{assign var="number_per_page_url" value=$sort_order.url}{break}{/if}
      {/foreach}
      {if !$number_per_page_url}{assign var="number_per_page_url" value=$listing.sort_orders[0]['url']|regex_replace:"/(\W)order=[^\&]+&*/":"$1"|trim:"&"|trim:"?"}{/if}
      {assign var="number_per_page_url" value=$number_per_page_url|regex_replace:"/(\W)resultsPerPage=\d+&*/":"$1"|trim:"&"|trim:"?"}
      {if strpos($number_per_page_url, '?') !== false}
      {assign var="number_per_page_url" value=$number_per_page_url|cat:"&resultsPerPage="}
      {else}
      {assign var="number_per_page_url" value=$number_per_page_url|cat:"?resultsPerPage="}
      {/if}
      {foreach from=$number_per_page item=number}
        {assign var="xnumber" value=$number|trim|intval}
        {if !$xnumber}{continue}{/if}
        <li>
        <a
          rel="nofollow"
          title="{$xnumber}"
          href="{$number_per_page_url}{$xnumber}"
          class="dropdown_list_item {['js-search-link' => true]|classnames} btn-spin js-btn-active"
        >
          <i class="fto-angle-right mar_r4"></i>{if $xnumber<10000}{$xnumber}{else}{l s='Show all' d='Shop.Theme.Actions'}{/if}
        </a>
        </li>
      {/foreach}
      </ul>
    </div>
  </div>
{/if}
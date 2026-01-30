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
{assign var='last_page' value=false}
{assign var='last_page_number' value=''}
{assign var='pagination_pages' value=$pagination.pages}
{if count($pagination_pages)}{assign var='last_page' value=array_pop($pagination_pages)}{/if}
{if $last_page['type']!='page' && count($pagination_pages)}{assign var='last_page' value=array_pop($pagination_pages)}{/if}
{if $last_page && $last_page['type']=='page' }{assign var='last_page_number' value=$last_page['page']}{/if}
  <nav class="paginaton_sample {if $last_page_number && $last_page_number==1} only_one_page{/if}" aria-label="Page navigation">
    <ul class="pagination">
      {foreach from=$pagination.pages item="page"}
        {if $page.type === 'previous' || $page.type === 'next' || $page.current}
        <li class="page-item {if $page.current} active {/if} {['disabled' => !$page.clickable]|classnames}">
            <a
              rel="{if $page.type === 'previous'}prev{elseif $page.type === 'next'}next{else}nofollow{/if}"
              href="{$page.url}"
              class="page-link {if $page.type === 'previous'}previous {elseif $page.type === 'next'}next {/if}{['js-search-link' => true]|classnames}"
              {if $page.type === 'previous'} aria-label="Previous" {elseif $page.type === 'next'} aria-label="Next" {/if}
            >
              {if $page.type === 'previous'}
                <i class="fto-left-open-3"></i><span class="sr-only">{l s='Previous' d='Shop.Theme.Actions'}</span>
              {elseif $page.type === 'next'}
                <i class="fto-right-open-3"></i><span class="sr-only">{l s='Next' d='Shop.Theme.Actions'}</span>
              {else}
                {$page.page}/{$last_page_number}
              {/if}
            </a>
        </li>
        {/if}
      {/foreach}
    </ul>
  </nav>

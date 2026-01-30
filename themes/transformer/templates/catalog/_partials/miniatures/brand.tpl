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
{block name='brand_miniature_item'}
  <li class="brand flex_container flex_start line_item">
    <div class="brand-img mr-3">
      <a href="{$brand.url}" title="{$brand.name}">
        <picture>
          {if isset($stwebp) && isset($stwebp.brand_default) && $stwebp.brand_default}
          <!--[if IE 9]><video style="display: none;"><![endif]-->
            <source {if $sttheme.cate_pro_lazy}data-{/if}srcset="{$brand.image|regex_replace:"/(\/\d+)(?:\-small_default)?\.(jpg)$/":"$1-brand_default.webp"}"
              title="{$brand.name}"
              type="image/webp"
              >
          <!--[if IE 9]></video><![endif]-->
          {/if}
          <img {if $sttheme.cate_pro_lazy}data-{/if}src="{$brand.image|regex_replace:"/(\/\d+)(?:\-small_default)?\.(jpg)$/":"$1-brand_default.$2"}" alt="{$brand.name}" width="{$sttheme.brand_default.width}" height="{$sttheme.brand_default.height}" class="general_border {if $sttheme.cate_pro_lazy} cate_pro_lazy {/if}" />
        </picture>
      </a>
    </div>
    <div class="flex_child">
      <div class="brand-infos">
        <h3 class="s_title_block"><a href="{$brand.url}" title="{$brand.name}">{$brand.name}</a></h3>
        {$brand.text nofilter}
      </div>
      <div class="brand-products">
        <a href="{$brand.url}" title="{l s='View products' d='Shop.Theme.Actions'}">{$brand.nb_products}</a>
        <a href="{$brand.url}" class="go" title="{l s='View products' d='Shop.Theme.Actions'}">{l s='View products' d='Shop.Theme.Actions'}</a>
      </div>
    </div>
  </li>
{/block}

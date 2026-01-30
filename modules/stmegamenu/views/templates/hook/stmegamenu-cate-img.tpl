{*
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @author    ST-themes <hellolee@gmail.com>
*  @copyright 2007-2017 ST-themes
*  @license   Use, by you or one client for one Prestashop instance.
*}
<a href="{$menu_cate.link}"{if !$menu_title} title="{$menu_cate.name}"{/if} class="menu_cate_img"{if $nofollow} rel="nofollow"{/if}{if $new_window} target="_blank"{/if}>
{if $menu_cate.id_image}
	{assign var='cate_image' value=$link->getCatImageLink($menu_cate.link_rewrite, $menu_cate.id_image, 'category_default')}
	{assign var='cate_image_2x' value=$link->getCatImageLink($menu_cate.link_rewrite, $menu_cate.id_image, 'category_default_2x')}
    <picture>
    {if isset($stwebp) && isset($stwebp.category_default) && $stwebp.category_default}
    <!--[if IE 9]><video style="display: none;"><![endif]-->
      <source srcset="{$cate_image|regex_replace:'/\.jpg$/':'.webp'}{if isset($sttheme) && $sttheme.retina},{$cate_image_2x|regex_replace:'/\.jpg$/':'.webp'} 2x{/if}"
        title="{$menu_cate.name}"
        type="image/webp"
        >
    <!--[if IE 9]></video><![endif]-->
    {/if}
    <img src="{$cate_image|escape:'html'}" {if isset($sttheme) && $sttheme.retina} srcset="{$cate_image_2x} 2x"{/if} alt="{$menu_cate.name}" width="{$categorySize.width}" height="{$categorySize.height}" />
    </picture>
{else}
    <img src="{$img_cat_dir}default-category_default.jpg" alt="{$menu_cate.name}" width="{$categorySize.width}" height="{$categorySize.height}" />
{/if}
</a>
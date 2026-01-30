{*
* 2007-2015 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<!-- MODULE st stcompare -->
<a href="{$stcompare.url}" title="{l s='Product comparison' d='Shop.Theme.Transformer'}" class="stcompare_link top_bar_item header_icon_btn_{$stcompare_header_style}" rel="nofollow"><span class="header_item">{if $stcompare_header_style!=1}<span class="header_icon_btn_icon header_v_align_m {if $stcompare_header_style==0 || $stcompare_header_style==5} mar_r4 {/if}">{if $stcompare_header_style==3 || $stcompare_header_style==4 || $stcompare_header_style==5}<span class="stcompare_quantity amount_circle">{if isset($stcompare_total) && $stcompare_total}{$stcompare_total}{else}0{/if}</span>{/if}<i class="header_icon_btn_icon fto-ajust icon_btn {if $stcompare_header_style==0}fs_lg{else}fs_big{/if}"></i></span>{/if}{if $stcompare_header_style!=2 && $stcompare_header_style!=4}<span class="header_v_align_m header_icon_btn_text">{l s='Compare' d='Shop.Theme.Transformer'}</span>{/if}{if $stcompare_header_style!=3 && $stcompare_header_style!=4 && $stcompare_header_style!=5}<span class="stcompare_quantity amount_inline mar_l4">{if isset($stcompare_total) && $stcompare_total}{$stcompare_total}{/if}</span>{/if}</span></a>
<!-- /MODULE st stcompare -->
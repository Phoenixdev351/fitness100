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
{if isset($id_product) && $id_product}
<a class="stcompare_add btn-spin hover_fly_btn {if isset($classname)} {$classname} {/if} {if isset($fromnocache) && $fromnocache && isset($is_compared) && $is_compared} st_added {/if} btn-spin pro_right_item stcompare_{$id_product} {if !isset($fromnocache) || !$fromnocache} stcompare_from_cache_block {/if}" data-id-product="{$id_product}" href="javascript:;" title="{l s='Add to compare' d='Shop.Theme.Transformer'}" rel="nofollow"><div class="hover_fly_btn_inner"><i class="fto-ajust icon_btn"></i>{if !isset($stcompare_product_style) || $stcompare_product_style != 2}<span class="btn_text">{l s='Add to compare' d='Shop.Theme.Transformer'}</span>{/if}{if isset($stcompare_with_number) && $stcompare_with_number}<span class="stcompare_quantity amount_inline mar_l4">{(int)$stcompare_total}</span>{/if}</div></a>
{/if}
<!-- /MODULE st stcompare -->
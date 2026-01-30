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
<!-- MODULE st stsearchbar -->
{capture name="quick_search_info"}
	{if $quick_search_header_style!=1}<span class="header_icon_btn_icon header_v_align_m {if $quick_search_header_style==0} mar_r4 {/if}"><i class="fto-search-1 icon_btn {if $quick_search_header_style==0}fs_lg{else}fs_big{/if}"></i></span>{/if}
    {if $quick_search_header_style!=2}<span class="header_v_align_m header_icon_btn_text">{l s='Search' d='Shop.Theme.Transformer'}</span>{/if}
{/capture}
{if $quick_search_simple==1 || $quick_search_simple==2}
<div class="search_widget_simple stsearchbar_link top_bar_item dropdown_wrap stsearchbar_builder header_icon_btn_{$quick_search_header_style}">
	<div class="dropdown_tri header_item link_color" aria-haspopup="true" aria-expanded="false">
		{$smarty.capture.quick_search_info nofilter}
	</div>
	<div class="dropdown_list" aria-labelledby="">
		{include 'module:stsearchbar/views/templates/hook/stsearchbar-block.tpl'}
	</div>
</div>
{elseif $quick_search_simple==4 || $quick_search_simple==5}
<div class="stsearchbar_link top_bar_item stsearchbar_builder header_icon_btn_{$quick_search_header_style}">
<a href="javascript:;" title="{l s='Search' d='Shop.Theme.Transformer'}" rel="nofollow" class="header_item rightbar_tri" data-name="side_search" data-direction="open_bar_{if $quick_search_simple==5}left{else}right{/if}">{$smarty.capture.quick_search_info nofilter}</a>
</div>
{elseif $quick_search_simple==6}
<div class="stsearchbar_link top_bar_item stsearchbar_builder header_icon_btn_{$quick_search_header_style}">
<a href="javascript:;" title="{l s='Search' d='Shop.Theme.Transformer'}" rel="nofollow" class="header_item popsearch_tri">{$smarty.capture.quick_search_info nofilter}</a>
</div>
{else}
{include 'module:stsearchbar/views/templates/hook/stsearchbar-block.tpl' headerclass="stsearchbar_builder top_bar_item"}
{/if}
<!-- /MODULE st stsearchbar -->
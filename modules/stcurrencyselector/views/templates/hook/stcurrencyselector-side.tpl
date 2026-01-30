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
<!-- MODULE st stcurrencyselector -->
<nav class="st-menu" id="side_currency">
	<div class="st-menu-header">
		<h3 class="st-menu-title">{l s='Currency switcher' d='Shop.Theme.Transformer'}</h3>
    	<a href="javascript:;" class="close_right_side" title="{l s='Close' d='Shop.Theme.Transformer'}"><i class="fto-angle-double-right side_close_right"></i><i class="fto-angle-double-left side_close_left"></i></a>
	</div>
	<div id="side_currency_block" class="mobile_nav_box">
        <ul class="mo_mu_level_0 mobile_menu_ul">
			{foreach from=$currencies key=k item=f_currency}
			<li class="mo_ml_level_0 mo_ml_column">
				<div class="menu_a_wrap">
				<a href="{$f_currency.url}" title="{$f_currency.name}" class="mo_ma_level_0">{$f_currency.sign}&nbsp;{$f_currency.iso_code}</a>
				</div>
			</li>
			{/foreach}
		</ul>
	</div>
</nav>
<!-- /MODULE st stcurrencyselector -->
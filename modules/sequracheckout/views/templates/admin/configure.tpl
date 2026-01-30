{*
* 2007-2018 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2018 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div class="panel">
	<h3><i class="icon icon-credit-card"></i> {l s='Sequra Checkout' mod='sequracheckout'}</h3>
	<p>
		{l s='These are the SeQura payment methods available for the shop' mod='sequracheckout'}
	</p>
	<ul>
		{foreach $methods as $method}
			<li>{$method['title']}</li>
		{/foreach}
	</ul>
</div>
<script>
jQuery(document).ready(function() {
	jQuery('input[id$="_on"]').on('change',
		function(e){
			var id = e.target.id;
			jQuery('input[id^="'+id.substr(0,id.length-8)+'"]:not(input[id$="_off"]):not(input[id$="_on"])' ).parent().parent().show("slow")
		}
	);
	jQuery('input[id$="_off"]').on('change',
		function(e){
			var id = e.target.id;
			jQuery('input[id^="'+id.substr(0,id.length-9)+'"]:not(input[id$="_off"]):not(input[id$="_on"])' ).parent().parent().hide("slow")
		}
	);
	//Hide initially off ones
	jQuery('input[id$="_off"]:checked').each(
		function(i,item){
			var id = item.id;
			jQuery('input[id^="'+id.substr(0,id.length-9)+'"]:not(input[id$="_off"]):not(input[id$="_on"])' ).parent().parent().hide()
		}
	);
});
</script>
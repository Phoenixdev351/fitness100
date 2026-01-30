/**
* 2007-2018 PrestaShop
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2018 PrestaShop SA
* @license   http://addons.prestashop.com/en/content/12-terms-and-conditions-of-use
* International Registered Trademark & Property of PrestaShop SA
*/

$(document).ready(function() {

	$.each($( "input[name='UPS_TRAK_TEST']" ), function(){
		$(this).next().css('float', 'none');
		$(this).next().css('width', '2%');
		$(this).prev().css('float', 'none');
		$(this).prev().css('width', '2%');
	});

	$('.t').css('text-align', 'left');

});

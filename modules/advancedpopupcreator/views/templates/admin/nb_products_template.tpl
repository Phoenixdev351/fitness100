{**
*
* NOTICE OF LICENSE
*
* This product is licensed for one customer to use on one installation (test stores and multishop included).
* Site developer has the right to modify this module to suit their needs, but can not redistribute the module in
* whole or in part. Any other use of this module constitutes a violation of the user agreement.
*
* DISCLAIMER
*
* NO WARRANTIES OF DATA SAFETY OR MODULE SECURITY
* ARE EXPRESSED OR IMPLIED. USE THIS MODULE IN ACCORDANCE
* WITH YOUR MERCHANT AGREEMENT, KNOWING THAT VIOLATIONS OF
* PCI COMPLIANCY OR A DATA BREACH CAN COST THOUSANDS OF DOLLARS
* IN FINES AND DAMAGE A STORES REPUTATION. USE AT YOUR OWN RISK.
*
*  @author    idnovate.com <info@idnovate.com>
*  @copyright 2020 idnovate.com
*  @license   See above
*}

<div class="row">
	<div class="col-lg-1">
		<select name="nb_products_comparator" class="t" id="nb_products_comparator">
			<option value="1" {if $object->nb_products_comparator|escape:'html':'UTF-8' == 1}selected="selected"{/if}>></option>
			<option value="2" {if $object->nb_products_comparator|escape:'html':'UTF-8' == 2}selected="selected"{/if}>=</option>
			<option value="3" {if $object->nb_products_comparator|escape:'html':'UTF-8' == 3}selected="selected"{/if}><</option>
		</select>
	</div>
	<div class="col-lg-1">
		<input type="text" name="nb_products" id="nb_products" value="{$object->nb_products|escape:'html':'UTF-8'}">
	</div>
</div>

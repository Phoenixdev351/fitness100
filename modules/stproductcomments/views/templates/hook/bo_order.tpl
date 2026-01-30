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
*  @author    ST-themes <hellolee@gmail.com>
*  @copyright 2007-2016 ST-themes
*  @license   Use, by you or one client for one Prestashop instance.
*  
*  
*}
<div class="panel">
	<div class="panel-heading">{l s='Review links' d='Modules.Stproductcomments.Admin'}</div>
	<div class="table-responsive">
	    <table class="table" id="product-review-links">
	        <thead>
	            <tr>
	              <th></th>
	              <th><span class="title_box ">{l s='Product' d='Admin.Global'}</span></th>
	              <th>
	                <span class="title_box ">{l s='Review link' d='Modules.Stproductcomments.Admin'}</span>
	              </th>
	          	</tr>
	      	</thead>
	      	<tbody>
	      		{foreach from=$products item=product key=k}
	      		<tr>
	      			<td></td>
	      			<td>
	      				<a href="{$link->getAdminLink('AdminProducts', true, ['id_product' => $product['product_id']|intval, 'updateproduct' => '1'])|escape:'html':'UTF-8'}">
	      					<span>{$product['product_name']}</span>
	      				</a>
	      			</td>
	      			<td><div id="review-link-{$product.id_order_detail}">{$link->getModuleLink('stproductcomments','mycomments',['id_order'=>$order->id,'id_product'=>$product.id_product,'id_order_detail'=>$product.id_order_detail,add_comment=>1,'secure_key'=>$secure_key])}</div></td>
	      		</tr>
	      		{/foreach}
	      	</tbody>
	  </table>
	</div>
	<div class="panel-footer" style="height:auto;">You can select review link and copy it to send to your customers.</div>
</div>
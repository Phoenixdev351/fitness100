{*
* Affiliates
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
*
* @author    FMM Modules
* @copyright © Copyright 2021 - All right reserved
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* @category  FMM Modules
* @package   affiliates
*}
{extends file="helpers/form/form.tpl"}
{block name="after"}

{if $is_customer == 1}
	<!-- Customer Details -->
	{if $version < 1.6}<br>{/if}
	<div id="container-customer" class="col-lg-6">
		<div class="panel clearfix">
			<div class="panel-heading toolbarBox">
				<i class="icon-user"></i>
				{$customer->firstname|escape:'htmlall':'UTF-8'}
				{$customer->lastname|escape:'htmlall':'UTF-8'}
				[{$customer->id|string_format:"%06d"|escape:'htmlall':'UTF-8'}]
				-
				<a href="mailto:{$customer->email|escape:'htmlall':'UTF-8'}"><i class="icon-envelope"></i>
					{$customer->email|escape:'htmlall':'UTF-8'}
				</a>
			</div>
			<div class="form-horizontal">
				<div class="row">
					<label class="control-label col-lg-4">{l s='Social Title' mod='affiliates'} : </label>
					<div class="col-lg-8">
						<p class="form-control-static">{if $gender->name}{$gender->name|escape:'htmlall':'UTF-8'}{else}{l s='Unknown' mod='affiliates'}{/if}</p>
					</div>
				</div>
				<div class="row">
					<label class="control-label col-lg-4">{l s='Age' mod='affiliates'} : </label>
					<div class="col-lg-8">
						<p class="form-control-static">
							{if isset($customer->birthday) && $customer->birthday != '0000-00-00'}
								{l s='%1$d years old (birth date: %2$s)' sprintf=[$customer_stats['age'], $customer_birthday] mod='affiliates'}
							{else}
								{l s='Unknown' mod='affiliates'}
							{/if}
						</p>
					</div>
				</div>
				<div class="row">
					<label class="control-label col-lg-4">{l s='Registration Date' mod='affiliates'} : </label>
					<div class="col-lg-8">
						<p class="form-control-static">{$registration_date|escape:'htmlall':'UTF-8'}</p>
					</div>
				</div>
				<div class="row">
					<label class="control-label col-lg-4">{l s='Last Visit' mod='affiliates'} : </label>
					<div class="col-lg-8">
						<p class="form-control-static">{if $customer_stats['last_visit']}{$last_visit|escape:'htmlall':'UTF-8'}{else}{l s='Never' mod='affiliates'}{/if}</p>
					</div>
				</div>
				{if $count_better_customers != '-'}
					<div class="row">
						<label class="control-label col-lg-4">{l s='Best Customer Rank' mod='affiliates'} : </label>
						<div class="col-lg-8">
							<p class="form-control-static">{$count_better_customers|escape:'htmlall':'UTF-8'}</p>
						</div>
					</div>
				{/if}
				{if $shop_is_feature_active}
					<div class="row">
						<label class="control-label col-lg-4">{l s='Shop' mod='affiliates'} : </label>
						<div class="col-lg-8">
							<p class="form-control-static">{$name_shop|escape:'htmlall':'UTF-8'}</p>
						</div>
					</div>
				{/if}
				<div class="row">
					<label class="control-label col-lg-4">{l s='Language' mod='affiliates'} : </label>
					<div class="col-lg-8">
						<p class="form-control-static">
							{if isset($customerLanguage)}
								{$customerLanguage->name|escape:'htmlall':'UTF-8'}
							{else}
								{l s='Unknown' mod='affiliates'}
							{/if}
						</p>
					</div>
				</div>
				<div class="row">
					<label class="control-label col-lg-4">{l s='Registrations' mod='affiliates'} : </label>
					<div class="col-lg-8">
						<p class="form-control-static">
							{if $customer->newsletter}
								<span class="label label-success">
									<i class="icon-check"></i>
									{l s='Newsletter' mod='affiliates'}
								</span>
							{else}
								<span class="label label-danger">
									<i class="icon-remove"></i>
									{l s='Newsletter' mod='affiliates'}
								</span>
							{/if}
							&nbsp;
							{if $customer->optin}
								<span class="label label-success">
									<i class="icon-check"></i>
									{l s='Opt in' mod='affiliates'}
								</span>
								{else}
								<span class="label label-danger">
									<i class="icon-remove"></i>
									{l s='Opt in' mod='affiliates'}
								</span>
							{/if}
						</p>
					</div>
				</div>
				<div class="row">
					<label class="control-label col-lg-4">{l s='Latest Update' mod='affiliates'} : </label>
					<div class="col-lg-8">
						<p class="form-control-static">{$last_update|escape:'htmlall':'UTF-8'}</p>
					</div>
				</div>
				<div class="row">
					<label class="control-label col-lg-4">{l s='Register Status' mod='affiliates'} : </label>
					<div class="col-lg-8">
						<p class="form-control-static">
							{if $customer->active}
								<span class="label label-success">
									<i class="icon-check"></i>
									{l s='Approved' mod='affiliates'}
								</span>
							{else}
								<span class="label label-danger">
									<i class="icon-remove"></i>
									{l s='Not Approved' mod='affiliates'}
								</span>
							{/if}
						</p>
					</div>
				</div>
			</div>
			{if $customer->isGuest()}
				{l s='This customer is registered as a Guest.' mod='affiliates'}
				{if !$customer_exists}
				<form method="post" action="index.php?tab=AdminCustomers&amp;id_customer={$customer->id|intval|escape:'htmlall':'UTF-8'}&amp;token={$cToken|escape:'htmlall':'UTF-8'}">
					<input type="hidden" name="id_lang" value="{$id_lang|escape:'htmlall':'UTF-8'}" />
					<p class="text-center">
						<input class="button" type="submit" name="submitGuestToCustomer" value="{l s='Transform to a customer account' mod='affiliates'}" />
					</p>
					{l s='This feature generates a random password before sending an email to your customer.' mod='affiliates'}
				</form>
				{else}
				<p class="text-muted text-center">
					{l s='A registered customer account using the defined email address already exists. ' mod='affiliates'}
				</p>
				{/if}
			{/if}
		</div>
	</div>
	{if $version < 1.6}<br>{/if}
	<!-- Orders -->
	<div id="container-customer" class="panel col-lg-6">
		<div class="panel-heading toolbarBox">
			<i class="icon-file"></i> {l s='Orders' mod='affiliates'} <span class="badge">{count($orders)|escape:'htmlall':'UTF-8'}</span>
		</div>
		{if $orders AND count($orders)}
			{assign var=count_ok value=count($orders_ok)}
			{assign var=count_ko value=count($orders_ko)}
			<div class="panel">
				<div class="row">
					<div class="col-lg-6">
						<i class="icon-ok-circle icon-big"></i>
						{l s='Valid orders:' mod='affiliates'}
						<span class="label label-success">{$count_ok|escape:'htmlall':'UTF-8'}</span>
						{l s='for a total amount of %s' sprintf=$total_ok mod='affiliates'}
					</div>
					<div class="col-lg-6">
						<i class="icon-exclamation-sign icon-big"></i>
						{l s='Invalid orders:' mod='affiliates'}
						<span class="label label-danger">{$count_ko|escape:'htmlall':'UTF-8'}</span>
					</div>
				</div>
			</div>

			{if $count_ok}
				<table class="table" {if $version < 1.6}width="100%"{/if}>
					<thead>
						<tr>
							<th class="center"><span class="title_box ">{l s='ID' mod='affiliates'}</span></th>
							<th><span class="title_box">{l s='Date' mod='affiliates'}</span></th>
							<th><span class="title_box">{l s='Payment' mod='affiliates'}</span></th>
							<th><span class="title_box">{l s='Status' mod='affiliates'}</span></th>
							<th><span class="title_box">{l s='Products' mod='affiliates'}</span></th>
							<th><span class="title_box ">{l s='Total spent' mod='affiliates'}</span></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
					{foreach $orders_ok AS $key => $order}
						<tr onclick="document.location = '?tab=AdminOrders&amp;id_order={$order['id_order']|intval|escape:'htmlall':'UTF-8'}&amp;vieworder&amp;token={getAdminToken tab='AdminOrders'}'">
							<td>{$order['id_order']|escape:'htmlall':'UTF-8'}</td>
							<td>{dateFormat date=$order['date_add'] full=0}</td>
							<td>{$order['payment']|escape:'htmlall':'UTF-8'}</td>
							<td>{$order['order_state']|escape:'htmlall':'UTF-8'}</td>
							<td>{$order['nb_products']|escape:'htmlall':'UTF-8'}</td>
							<td>{$order['total_paid_real']|escape:'htmlall':'UTF-8'}</td>
							<td>
								<a class="btn btn-default" href="?tab=AdminOrders&amp;id_order={$order['id_order']|intval|escape:'htmlall':'UTF-8'}&amp;vieworder&amp;token={getAdminToken tab='AdminOrders'}">
									<i class='icon-search'></i> {l s='View' mod='affiliates'}
								</a>
							</td>
						</tr>
					{/foreach}
					</tbody>
				</table>
			{/if}

			{if $count_ko}
				<table class="table" {if $version < 1.6}width="100%"{/if}>
					<thead>
						<tr>
							<th><span class="title_box ">{l s='ID' mod='affiliates'}</span></th>
							<th><span class="title_box ">{l s='Date' mod='affiliates'}</span></th>
							<th><span class="title_box ">{l s='Payment' mod='affiliates'}</span></th>
							<th><span class="title_box ">{l s='Status' mod='affiliates'}</span></th>
							<th><span class="title_box ">{l s='Products' mod='affiliates'}</span></th>
							<th><span class="title_box ">{l s='Total spent' mod='affiliates'}</span></th>
						</tr>
					</thead>
					<tbody>
						{foreach $orders_ko AS $key => $order}
						<tr onclick="document.location = '?tab=AdminOrders&amp;id_order={$order['id_order']|intval|escape:'htmlall':'UTF-8'}&amp;vieworder&amp;token={getAdminToken tab='AdminOrders'}'">
							<td>{$order['id_order']|escape:'htmlall':'UTF-8'}</td>
							<td><a href="?tab=AdminOrders&amp;id_order={$order['id_order']|escape:'htmlall':'UTF-8'}&amp;vieworder&amp;token={getAdminToken tab='AdminOrders'}">{dateFormat date=$order['date_add'] full=0}</a></td>
							<td>{$order['payment']|escape:'htmlall':'UTF-8'}</td>
							<td>{$order['order_state']|escape:'htmlall':'UTF-8'}</td>
							<td>{$order['nb_products']|escape:'htmlall':'UTF-8'}</td>
							<td>{$order['total_paid_real']|escape:'htmlall':'UTF-8'}</td>
						</tr>
						{/foreach}
					</tbody>
				</table>
			{/if}
		{else}
		<p class="text-muted text-center">
			{l s='%1$s %2$s has not placed any orders yet' sprintf=[$customer->firstname, $customer->lastname] mod='affiliates'}
		</p>
		{/if}
	</div>
	{if $version < 1.6}<br>{/if}
	<!-- Carts -->
	<div id="container-customer" class="panel col-lg-6">
		<div class="panel-heading toolbarBox">
			<i class="icon-shopping-cart"></i> {l s='Carts' mod='affiliates'} <span class="badge">{count($carts)|escape:'htmlall':'UTF-8'}</span>
		</div>
		{if $carts AND count($carts)}
			<table class="table" {if $version < 1.6}width="100%"{/if}>
				<thead>
					<tr>
						<th><span class="title_box ">{l s='ID' mod='affiliates'}</span></th>
						<th><span class="title_box ">{l s='Date' mod='affiliates'}</span></th>
						<th><span class="title_box ">{l s='Carrier' mod='affiliates'}</span></th>
						<th><span class="title_box ">{l s='Total' mod='affiliates'}</span></th>
					</tr>
				</thead>
				<tbody>
				{foreach $carts AS $key => $cart}
					<tr onclick="document.location = '?tab=AdminCarts&amp;id_cart={$cart['id_cart']|intval|escape:'htmlall':'UTF-8'}&amp;viewcart&amp;token={getAdminToken tab='AdminCarts'}'">
						<td>{$cart['id_cart']|escape:'htmlall':'UTF-8'}</td>
						<td>
							<a href="index.php?tab=AdminCarts&amp;id_cart={$cart['id_cart']|escape:'htmlall':'UTF-8'}&amp;viewcart&amp;token={getAdminToken tab='AdminCarts'}">
								{dateFormat date=$cart['date_upd'] full=0}
							</a>
						</td>
						<td>{$cart['name']|escape:'htmlall':'UTF-8'}</td>
						<td>{$cart['total_price']|escape:'htmlall':'UTF-8'}</td>
					</tr>
				{/foreach}
				</tbody>
			</table>
		{else}
		<p class="text-muted text-center">
			{l s='No cart is available' mod='affiliates'}
		</p>
		{/if}
	</div>
	{if $version < 1.6}<br>{/if}
	<!-- Purchased Products -->
	{if $products AND count($products)}
	<div id="container-customer" class="panel col-lg-6">
		<div class="panel-heading toolbarBox">
			<i class="icon-archive"></i> {l s='Purchased products' mod='affiliates'} <span class="badge">{count($products)|escape:'htmlall':'UTF-8'}</span>
		</div>
		<table class="table" {if $version < 1.6}width="100%"{/if}>
			<thead>
				<tr>
					<th><span class="title_box">{l s='Date' mod='affiliates'}</span></th>
					<th><span class="title_box">{l s='Name' mod='affiliates'}</span></th>
					<th><span class="title_box">{l s='Quantity' mod='affiliates'}</span></th>
				</tr>
			</thead>
			<tbody>
				{foreach $products AS $key => $product}
				<tr onclick="document.location = '?tab=AdminOrders&amp;id_order={$product['id_order']|intval|escape:'htmlall':'UTF-8'}&amp;vieworder&amp;token={getAdminToken tab='AdminOrders'}'">
					<td>{dateFormat date=$product['date_add'] full=0}</td>
					<td>
						<a href="?tab=AdminOrders&amp;id_order={$product['id_order']|escape:'htmlall':'UTF-8'}&amp;vieworder&amp;token={getAdminToken tab='AdminOrders'}">
							{$product['product_name']|escape:'htmlall':'UTF-8'}
						</a>
					</td>
					<td>{$product['product_quantity']|escape:'htmlall':'UTF-8'}</td>
				</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	{if $version < 1.6}<br>{/if}
	{/if}

	<!-- Visits -->
	{if count($connections)}
	<div id="container-customer" class="panel col-lg-6">
		<div class="panel-heading toolbarBox">
			<i class="icon-time"></i> {l s='Last connections' mod='affiliates' mod='affiliates'}
		</div>
		<table class="table" {if $version < 1.6}width="100%"{/if}>
			<thead>
			<tr>
				<th><span class="title_box"></span></th>
				<th><span class="title_box">{l s='Date' mod='affiliates'}</span></th>
				<th><span class="title_box">{l s='Pages viewed' mod='affiliates'}</span></th>
				<th><span class="title_box">{l s='Total time' mod='affiliates'}</span></th>
				<th><span class="title_box">{l s='Origin' mod='affiliates'}</span></th>
				<th><span class="title_box">{l s='IP Address' mod='affiliates'}</span></th>
			</tr>
			</thead>
			<tbody>
			{foreach $connections as $connection}
				<tr>
					<td></td>
					<td>{dateFormat date=$connection['date_add'] full=0}</td>
					<td>{$connection['pages']|escape:'htmlall':'UTF-8'}</td>
					<td>{$connection['time']|escape:'htmlall':'UTF-8'}</td>
					<td>{$connection['http_referer']|escape:'htmlall':'UTF-8'}</td>
					<td>{$connection['ipaddress']|escape:'htmlall':'UTF-8'}</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>
	{if $version < 1.6}<br>{/if}
	{/if}
	<!-- Address -->
	<div id="container-customer" class="col-lg-12 panel">
		<div class="">
			<div class="panel-heading toolbarBox">
				<i class="icon-map-marker"></i> {l s='Addresses' mod='affiliates' mod='affiliates'} <span class="badge">{count($addresses)|escape:'htmlall':'UTF-8'}</span>
			</div>
			{if count($addresses)}
				<table class="table" {if $version < 1.6}width="100%"{/if}>
					<thead>
						<tr>
							<th><span class="title_box ">{l s='Company' mod='affiliates'}</span></th>
							<th><span class="title_box ">{l s='Name' mod='affiliates'}</span></th>
							<th><span class="title_box ">{l s='Address' mod='affiliates'}</span></th>
							<th><span class="title_box ">{l s='Country' mod='affiliates'}</span></th>
							<th><span class="title_box ">{l s='Phone number(s)' mod='affiliates'}</span></th>
						</tr>
					</thead>
					<tbody>
						{foreach $addresses AS $key => $address}
						<tr>
							<td>{if $address['company']}{$address['company']|escape:'htmlall':'UTF-8'}{else}--{/if}</td>
							<td>{$address['firstname']|escape:'htmlall':'UTF-8'} {$address['lastname']|escape:'htmlall':'UTF-8'}</td>
							<td>{$address['address1']|escape:'htmlall':'UTF-8'} {if $address['address2']}{$address['address2']|escape:'htmlall':'UTF-8'}{/if} {$address['postcode']|escape:'htmlall':'UTF-8'} {$address['city']|escape:'htmlall':'UTF-8'}</td>
							<td>{$address['country']|escape:'htmlall':'UTF-8'}</td>
							<td>
								{if $address['phone']}
									{$address['phone']|escape:'htmlall':'UTF-8'}
									{if $address['phone_mobile']}<br />{$address['phone_mobile']|escape:'htmlall':'UTF-8'}{/if}
								{else}
									{if $address['phone_mobile']}<br />{$address['phone_mobile']|escape:'htmlall':'UTF-8'}{else}--{/if}
								{/if}
							</td>
						</tr>
						{/foreach}
					</tbody>
				</table>
			{else}
				<p class="text-muted text-center">
					{l s='%1$s %2$s has not registered any addresses yet' sprintf=[$customer->firstname, $customer->lastname] mod='affiliates'}
				</p>
			{/if}
		</div>
		<div class="panel-footer">
			<a href="{$link->getAdminLink('AdminReferrals')|escape:'htmlall':'UTF-8'}" class="btn btn-default">
				<i class="process-icon-back "></i> <span>{l s='Back to list' mod='affiliates' mod='affiliates'}</span>
			</a>
		</div>
	</div>
{/if}
{/block}
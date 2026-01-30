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

{if version_compare($smarty.const._PS_VERSION_,'1.6','<')}
	<div class="translatable">
		{foreach $languages as $language}
			<div class="lang_{$language.id_lang|escape:'htmlall':'UTF-8'}" style="display:{if $language.id_lang == $default_form_language}block{else}none{/if}; float: left;">
				{if isset($images[$language['id_lang']]) && $images[$language['id_lang']]}
					<img class="img-responsive" name="image_preview_{$language.id_lang|escape:'htmlall':'UTF-8'}" id="image_preview_{$language.id_lang|escape:'htmlall':'UTF-8'}" src="{$image_dir|escape:'htmlall':'UTF-8'}{$images[$language['id_lang']]|escape:'htmlall':'UTF-8'}?t={$smarty.now}">

					<div class="clear"></div>
					<br />
					<a class="btn btn-default" href="{$delete_url|escape:'htmlall':'UTF-8'}&id_language={$language.id_lang|escape:'htmlall':'UTF-8'}&type=image">
						<i class="icon-trash"></i> {l s='Delete image' mod='advancedpopupcreator'}
					</a>
				{else}
					<img class="img-responsive" name="image_preview_{$language.id_lang|escape:'htmlall':'UTF-8'}" id="image_preview_{$language.id_lang|escape:'htmlall':'UTF-8'}" src="{$image_dir|escape:'htmlall':'UTF-8'}noimage.gif">
				{/if}
			</div>
		{/foreach}
	</div>
{else}
	{foreach $languages as $language}
		{if $languages|count > 1}
		<div class="translatable-field lang-{$language['id_lang']|escape:'htmlall':'UTF-8'}" style="display: {if $language['id_lang'] == $default_form_language}block{else}none{/if};">
		{/if}
			<div class="col-lg-9">
				{if isset($images[$language['id_lang']]) && $images[$language['id_lang']]}
					<img class="img-responsive" name="image_preview_{$language.id_lang|escape:'htmlall':'UTF-8'}" id="image_preview_{$language.id_lang|escape:'htmlall':'UTF-8'}" src="{$image_dir|escape:'htmlall':'UTF-8'}{$images[$language['id_lang']]|escape:'htmlall':'UTF-8'}?t={$smarty.now}">
					<div class="clear"></div>
					<br />
					<a class="btn btn-default" href="{$delete_url|escape:'htmlall':'UTF-8'}&id_language={$language.id_lang|escape:'htmlall':'UTF-8'}&type=image">
						<i class="icon-trash"></i> {l s='Delete image' mod='advancedpopupcreator'}
					</a>
				{else}
					<img class="img-responsive" name="image_preview_{$language.id_lang|escape:'htmlall':'UTF-8'}" id="image_preview_{$language.id_lang|escape:'htmlall':'UTF-8'}" src="{$image_dir|escape:'htmlall':'UTF-8'}noimage.gif">
				{/if}
			</div>
			{if $languages|count > 1}
			<div class="col-lg-2">
				<button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
					{$language['iso_code']|escape:'htmlall':'UTF-8'}
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu">
					{foreach $languages as $language}
					<li><a href="javascript:hideOtherLanguage({$language['id_lang']|escape:'htmlall':'UTF-8'});" tabindex="-1">{$language['name']|escape:'htmlall':'UTF-8'}</a></li>
					{/foreach}
				</ul>
			</div>
		</div>
		{/if}
	{/foreach}
{/if}

<div class="clear"></div>


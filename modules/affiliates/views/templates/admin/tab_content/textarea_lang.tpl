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
{if $ps_version < 1.6}

	<div class="translatable">
	{foreach from=$languages item=language}
	<div id="welcome_{$language.id_lang|escape:'htmlall':'UTF-8'}" class="lang_{$language.id_lang|escape:'htmlall':'UTF-8'}" style="display: {if $language.id_lang == $id_lang_default} block {else} none{/if};float: left;">
		<textarea cols="100" rows="10" type="text" id="{$input_name|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}" 
			name="{$input_name|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}" 
			class="autoload_rte" >{if isset($input_value[$language.id_lang])}{$input_value[$language.id_lang]|htmlentitiesUTF8}{*html content*}{/if}</textarea>
		<span class="hint" name="help_box">{$hint|default:''|escape:'htmlall':'UTF-8'}<span class="hint-pointer">&nbsp;</span></span>
	</div>
	{/foreach}
	{$module->displayFlags($languages, $id_lang_default, welcome, welcome, false)|escape:'htmlall':'UTF-8'}
	</div>
	<script type="text/javascript">
		var iso = '{$iso_tiny_mce|escape:'htmlall':'UTF-8'}';
		var pathCSS = '{$smarty.const._THEME_CSS_DIR_|escape:'htmlall':'UTF-8'}';
		var ad = '{$ad|escape:'htmlall':'UTF-8'}';
	</script>
{else}

	{foreach from=$languages item=language}
		{if $languages|count > 1}
			<div class="translatable-field row lang-{$language.id_lang|escape:'htmlall':'UTF-8'}">
				<div class="col-lg-9">
		{/if}
			<textarea id="{$input_name|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}" name="{$input_name|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}" class="{if isset($class)}{$class|escape:'htmlall':'UTF-8'}{else}textarea-autosize{/if}"{if isset($maxlength) && $maxlength} maxlength="{$maxlength|intval|escape:'htmlall':'UTF-8'}"{/if}>{if isset($input_value[$language.id_lang])}{$input_value[$language.id_lang]|htmlentitiesUTF8}{*html content*}{/if}</textarea>

		{if $languages|count > 1}
				</div>
				<div class="col-lg-2">
					<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
						{$language.iso_code|escape:'htmlall':'UTF-8'}
						<span class="caret"></span>
					</button>
					<ul class="dropdown-menu">
						{foreach from=$languages item=language}
						<li><a href="javascript:hideOtherLanguage({$language.id_lang|escape:'htmlall':'UTF-8'});">{$language.name|escape:'htmlall':'UTF-8'}</a></li>
						{/foreach}
					</ul>
				</div>
			</div>
		{/if}
	{/foreach}
	<script type="text/javascript">
		$(".textarea-autosize").autosize();
	</script>
{/if}
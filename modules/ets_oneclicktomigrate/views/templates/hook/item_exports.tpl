{*
* 2007-2019 ETS-Soft ETS-Soft
*
* NOTICE OF LICENSE
*
* This file is not open source! Each license that you purchased is only available for 1 wesite only.
* If you want to use this file on more websites (or projects), you need to purchase additional licenses.
* You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please, contact us for extra customization service at an affordable price
*
*  @author ETS-Soft <etssoft.jsc@gmail.com>
*  @copyright  2007-2019 ETS-Soft ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}
{if isset($exports) && $exports}{foreach from=$exports item='export'}
	<tr>
		<td>{$export.id_export_history|intval}</td>
		<td>{$export.content nofilter}</td>
		<td>{$export.date_export|escape:'html':'UTF-8'}</td>
		<td>
			<a target="_blank" href="{$url_cache|escape:'html':'UTF-8'}export/{$export.file_name|escape:'html':'UTF-8'}"><i class="fa fa-cloud-download"> </i> {l s='Download' mod='ets_oneclicktomigrate'}</a><br />
			<a href="index.php?tab=AdminModules&configure=ets_oneclicktomigrate&token={$token|escape:'html':'UTF-8'}&tab_module=front_office_features&module_name=ets_oneclicktomigrate&tabmodule=history&deleteexporthistory&id_export_history={$export.id_export_history|intval}"><i class="fa fa-trash"> </i> {l s='Delete' mod='ets_oneclicktomigrate'}</a>
		</td>
	</tr>
{/foreach}{/if}
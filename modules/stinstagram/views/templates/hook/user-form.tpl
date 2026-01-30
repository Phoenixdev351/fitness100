{*
* 2007-2016 PrestaShop
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
*  @author    ST-themes
*  @copyright 2007-2016 ST-themes
*  @license   Use, by you or one client for one Prestashop instance.
*  
*  
*}
<div class="panel {if isset($show_add) && $show_add}add-show{else}add-hide{/if}" style="width: 100%;">
{if isset($error_msg) && $error_msg!=''}<div class="alert alert-danger">{$error_msg}</div>{/if}
<h2>{l s='Binding new instagram' d='Modules.Stinstagram.Admin'}</h2>
<p><a href="{$api_url}?client_id={$client_id}&redirect_uri={$redirect_uri}&scope={$scope}&response_type={$response_type}" id="a_linker" target="_blank">{l s='The first step to use the Instagram module is to get Access token and User ID.' d='Modules.Stinstagram.Admin'}</a></p>
<div>
	<form action="" method="POST">
	{if isset($upData.id_st_instagram_bind)}
		<input type="hidden" name="id_st_instagram_bind" value="{$upData.id_st_instagram_bind}">
	{/if}
<label>{l s='Access token:' d='Modules.Stinstagram.Admin'}<input type="text" name="ac_token" value="{if isset($upData.utoken)}{$upData.utoken}{/if}" /></label>
		<label>{l s='User ID:' d='Modules.Stinstagram.Admin'}<input type="text" name="user_id" value="{if isset($upData.userid)}{$upData.userid}{/if}" /></label>
		<p>
		<label>{l s='Binding Shop:' d='Modules.Stinstagram.Admin'}
			<select class="form-control fixed-width-xxl" name='shop_ids[]' multiple="multiple" size="3">
			{foreach $shop_list as $sval}
			<option value ="{$sval['id_shop']}" {if isset($upData['shop_ids']) && in_array($sval['id_shop'],$upData['shop_ids'])}selected{/if}>{$sval['name']}</option>
			{/foreach}
			</select>
			</label>
		</p>
		<input type="submit" name="add_ac_token" value="{l s='Submit' d='Modules.Stinstagram.Admin'}" />
	</form>
</div>
<p>{l s='By using this module, you are agreeing to the' d='Modules.Stinstagram.Admin'} <a href="http://instagram.com/about/legal/terms/api/" target="_blank">{l s='Instagram API Terms of Use' d='Modules.Stinstagram.Admin'}</a>.</p>
<p>{l s='Cron URL：' d='Modules.Stinstagram.Admin'} <strong>{$cron_url}</strong></p>
</div>
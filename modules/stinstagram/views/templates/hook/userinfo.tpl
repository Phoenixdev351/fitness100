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
<div class="panel col-lg-12">
<a class="clear_cache_btn btn btn-default m-r-2" href="javascrpit:;" data-href="{$update_img_url}" title="{l s='Sync pictures from Instagram' d='Modules.Stinstagram.Admin'}">{l s='Sync pictures from Instagram' d='Modules.Stinstagram.Admin'}</a>&nbsp;&nbsp;<a class="clear_cache_btn btn btn-default"  href="javascrpit:;"  data-href="{$clear_img_url}" title="{l s='Clear cache' d='Modules.Stinstagram.Admin'}">{l s='Clear cache' d='Modules.Stinstagram.Admin'}</a>
</p>
<p>{l s='If pictures not showing on the frontend, then try clicking on the "Sync pictures" button above.' d='Modules.Stinstagram.Admin'}</p>
<div id="clear_cache_warning" class="alert alert-warning">
	{l s='In progress, please do not leave this page' d='Modules.Stinstagram.Admin'}
</div>
<p>{l s='Cron URL：' d='Modules.Stinstagram.Admin'} <strong>{$cron_url}</strong></p>
</div>
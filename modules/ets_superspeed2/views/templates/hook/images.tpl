{*
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
*}
{if $images}
    {foreach from=$images item='image'}
        <li class="upload" id="image-old-{$image.id_ets_superspeed_upload_image|intval}">
            <div class="before">
                <span class="image_name" title="{$image.image_name|escape:'html':'UTF-8'}">{$image.image_name_hide|escape:'html':'UTF-8'} ({$image.old_size|escape:'html':'UTF-8'})</span>
            </div>
            <div class="progress success">
                <div class="progress_bar">
                    <div class="bar" style="width: 100%;"></div>
                    <div class="status">{l s='Optimized' mod='ets_superspeed'} (<span class="saved">{l s='Save' mod='ets_superspeed'} {$image.saved|escape:'html':'UTF-8'}</span>)</div>
                </div>
            </div>
            <div class="after">
                <span class="size">{$image.new_size|escape:'html':'UTF-8'}</span>
                <a class="" href="{$link->getAdminLink('AdminSuperSpeedImage')|escape:'html':'UTF-8'}&download_image_upload={$image.id_ets_superspeed_upload_image|intval}"><i class="fa fa-download"></i> {l s='Download' mod='ets_superspeed'}</a>
                <a class="sp_delete_image_upload" href="{$link->getAdminLink('AdminSuperSpeedImage')|escape:'html':'UTF-8'}&delete_image_upload={$image.id_ets_superspeed_upload_image|intval}" title="{l s='Delete' mod='ets_superspeed'}">{l s='Delete' mod='ets_superspeed'}</a>
            </div>
        </li>
    {/foreach}
{/if}
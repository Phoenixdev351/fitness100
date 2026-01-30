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
<div class="form-wrapper">
	<div class="form-group margin-form">
		<label class="form-group control-label col-lg-3">{l s='Icon Theme' mod='affiliates'}
		</label>
		<div class="col-lg-7">	
		    <select id="AFFILIATE_SOCIAL_THEME" name="AFFILIATE_SOCIAL_THEME" class="form-control fixed-width-xxl ">
		    {foreach from=$icon_themes item=theme}
		         <option value="{$theme.value|escape:'htmlall':'UTF-8'}" {if $AFFILIATE_SOCIAL_THEME == $theme.value}selected="selected"{/if}>{$theme.name|escape:'htmlall':'UTF-8'}</option>
		    {/foreach}
		    </select>
		</div>
	</div>
	<div class="clearfix"></div>

	<div class="form-group">
		<label class="control-label col-lg-3">{l s='Enable Social Labels' mod='affiliates'}</label>
		<div class="col-lg-9">
			<span class="switch prestashop-switch fixed-width-lg">
				<input type="radio" {if isset($AFFILIATE_SOCIAL_LABELS) AND $AFFILIATE_SOCIAL_LABELS == 1}checked="checked"{/if} value="1" id="AFFILIATE_SOCIAL_LABELS_on" name="AFFILIATE_SOCIAL_LABELS">
				<label for="AFFILIATE_SOCIAL_LABELS_on" class="t">{l s='Yes' mod='affiliates'}</label>
				<input type="radio" value="0" {if isset($AFFILIATE_SOCIAL_LABELS) AND $AFFILIATE_SOCIAL_LABELS == 0}checked="checked"{/if} id="AFFILIATE_SOCIAL_LABELS_off" name="AFFILIATE_SOCIAL_LABELS">
				<label for="AFFILIATE_SOCIAL_LABELS_off" class="t">{l s='No' mod='affiliates'}</label>
				<a class="slide-button btn"></a>
			</span>
			<p class="margin-form hint-block help-block">{l s='social network label will be displayed along with icons' mod='affiliates'}</p>
		</div>
	</div>

	<!-- social sharing networks -->
	<div class="form-group">
	    <label class="control-label col-lg-3">{l s='Select Social Networks' mod='affiliates'}
	    </label>
	    <div class="col-lg-8">
	        <div class="{if $ps_version >= 1.6}row{/if}">
	            <div class="col-lg-8">
	                <table class="table table-bordered panel">
	                    <thead>
	                        <tr>
	                            <th class="fixed-width-xs">
	                                <span class="title_box">
	                                    <input type="checkbox" onclick="checkDelBoxes(this.form, 'selected_socials[]', this.checked)" id="socialnetwork-checkme" name="socialnetwork-checkme">
	                                </span>
	                            </th>
	                            <th>
	                                <span class="title_box">{l s='Social Network' mod='affiliates'}</span>
	                            </th>
	                        </tr>
	                    </thead>
	                    <tbody>
	                    {if isset($social_networks) AND $social_networks}
	                        {foreach from=$social_networks item=social}
	                        <tr>
	                            <td>
	                                <input type="checkbox" value="{$social.id|escape:'htmlall':'UTF-8'}" id="social_networks_{$social.id|escape:'htmlall':'UTF-8'}" class="selected_socials" name="selected_socials[]" {if isset($selected_socials) AND $selected_socials AND in_array($social.id, $selected_socials)}checked="checked"{/if}>
	                            </td>
	                            <td>
	                                <label for="social_networks_{$social.id|escape:'htmlall':'UTF-8'}">
	                                	<img class="btn btn-default" src="{$smarty.const.__PS_BASE_URI__|escape:'htmlall':'UTF-8'}modules/affiliates/views/img/socials/{$social.id|escape:'htmlall':'UTF-8'}.png">&nbsp;
		                                {$social.name|escape:'htmlall':'UTF-8'}
		                            </label>
	                            </td>
	                        </tr>
	                        {/foreach}
	                    {/if}
	                    </tbody>
	                </table>
	                <p class="help-block hint-block margin-form">{l s='Selected social networks will be available to share referral link.' mod='affiliates'}</p>
	            </div>
	        </div>
	    </div>
	</div>
<div class="clearfix"></div>
</div>

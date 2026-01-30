{**
* NOTICE OF LICENSE
*
* This source file is subject to a commercial license from Feed.Biz, Ltd.
* Use, copy, modification or distribution of this source file without written
* license agreement from Feed.Biz, Ltd. is strictly forbidden.
* In order to obtain a license, please contact us: contact@feed.biz
* ...........................................................................
* INFORMATION SUR LA LICENCE D'UTILISATION
*
* L'utilisation de ce fichier source est soumise a une licence commerciale
* concedee par la societe Feed.Biz, Ltd.
* Toute utilisation, reproduction, modification ou distribution du present
* fichier source sans contrat de licence ecrit de la part de la Feed.Biz, Ltd. est
* expressement interdite.
* Pour obtenir une licence, veuillez contacter Feed.Biz, Ltd. a l'adresse: contact@feed.biz
* ...........................................................................
* @package    Feed.Biz
* @author     Olivier B.
* @copyright  Copyright (c) 2011-2022 Feed.Biz - Hong Kong - Head Quarters - Room 1408, 14/F, Tak Shing House - Theatre Lane - 20 des Voeux Road Central, -Central, Hong Kong
* @license    Commercial license
* Support by mail  :  support@feed.biz
*}

<div style="clear: both;"></div>

<script type="text/javascript" src="{$js_url|escape:'htmlall':'UTF-8'}"></script>
<link rel="stylesheet" type="text/css" href="{$css_url|escape:'htmlall':'UTF-8'}">

<input type="hidden" id="mp_order_id" name="cd-order-id" value="{$marketplace_order_id|escape:'htmlall':'UTF-8'}"/>
<input type="hidden" id="seller_order_id" value="{$seller_order_id|escape:'quotes':'UTF-8'}"/>
<input type="hidden" id="cancel_status" value="{if isset($cancel_status)}{$cancel_status|intval}{/if}"/>
<input type="hidden" id="cancel_url" value="{if isset($cancel_url)}{$cancel_url|escape:'quotes':'UTF-8'}{/if}"/>
<input type="hidden" id="fbtoken" value="{$fbtoken|escape:'quotes':'UTF-8'}"/>

<br>
<div class="panel">

    {if $ps_version_is_16}
    <div class="panel-heading">
        <img src="{$images_url|escape:'htmlall':'UTF-8'}logo.gif"
             alt="{l s='Feedbiz' mod='feedbiz'}"/>&nbsp;{l s='Feedbiz' mod='feedbiz'}
    </div>
    {else}
    <fieldset>
        <legend><img src="{$images_url|escape:'htmlall':'UTF-8'}logo.gif" width="16" height="16"
                     alt="">{l s='Feedbiz' mod='feedbiz'}</legend>
        {/if}

        <table>
            <tr>
                <td width="150px"><span class="feedbiz_label">{l s='Feedbiz ID' mod='feedbiz'}:</span></td>
                <td><span class="feedbiz_text">{$marketplace_order_id|escape:'htmlall':'UTF-8'}</span></td>
            </tr>
            <tr>
                <td><span class="feedbiz_label">{l s='Reference' mod='feedbiz'}:</span></td>
                <td><span class="feedbiz_text">{$marketplace_reference|escape:'htmlall':'UTF-8'}</span></td>
            </tr>
            
            {if isset($orders_url)}
                <tr>
                    <td><span class="feedbiz_label">{l s='Go To' mod='feedbiz'}</span></td>
                    <td>
    		        <span class="feedbiz_text">
    		            <a href="{$orders_url|escape:'htmlall':'UTF-8'}"
                           title="{$order_ext.channel_name|escape:'htmlall':'UTF-8'}" target="_blank">
                            <img src="{$images_url|escape:'htmlall':'UTF-8'}{$order_ext.channel|escape:'htmlall':'UTF-8'}_100_40.png"
                                 style="position:relative;vertical-align: middle;"
                                 alt="{$order_ext.channel_name|escape:'htmlall':'UTF-8'}"/>
                        </a>
    		    	</span>
                    </td>
                </tr>
            {/if}

            {if isset($scenario)}
                <tr>
                    <td>&nbsp;</td>
                    <td> 
                        <br/>
                        <div class="row" id="feedbiz-to-cancel" >
                            {if $scenario == 'to_cancel'}
                                <div class="feedbiz_text">
                                    <div class="feedbiz_red_text">{l s='Cancelation pending, please provide a reason' mod='feedbiz'}:</div>
                                    <br/>
                                    <select id="feedbiz-cancel" class="chosen form-control">
                                        {foreach from=$reasons key=id item=reason}
                                            <option value="{$id|escape:'html':'UTF-8'}">{l s=$reason mod='feedbiz'}</option>
                                        {/foreach}
                                    </select>
                                    <button type="button" name="feedbiz-cancel-button" class="btn btn-primary">
                                        {l s='Confirm' mod='feedbiz'}
                                    </button>&nbsp;&nbsp;<img src="{$images_url|escape:'quotes':'UTF-8'}loading.gif" id="feedbiz-cancel-loader" style="display:none"/>
                                </div> 
                            {elseif $scenario == 'cancel_cancel'}
                                <div class="feedbiz_red_text">{l s='Cancelation has been scheduled' mod='feedbiz'}</div>
                                <br/>
                                <div class="feedbiz_text">
                                    <button type="button" name="feedbiz-revert-button" class="btn btn-primary">
                                        {l s='Revert' mod='feedbiz'}
                                    </button>&nbsp;&nbsp;<img src="{$images_url|escape:'quotes':'UTF-8'}loading.gif" id="feedbiz-cancel-loader" style="display:none"/>
                                </div>
                            {elseif $scenario == 'canceled'}
                                <div class="feedbiz_red_text">{l s='This order has been canceled' mod='feedbiz'}</div>
                            {/if}
                        </div>  
                        <br/>
                        <div class="alert alert-success" id="feedbiz-cancel-success" class="{$class_success|escape:'htmlall':'UTF-8'}" style="display:none">
                        </div>
                        <br/>
                        <div class="alert alert-danger" id="feedbiz-cancel-error" class="{$class_error|escape:'htmlall':'UTF-8'}" style="display:none">
                        </div>
                    </td>

                </tr>
            {else}

                {if isset($order_ext) && is_array($order_ext)}
                    {if isset($order_ext.channel_id)}
                        <tr>
                            <td><span class="feedbiz_label">{l s='Sales Channel' mod='feedbiz'}:</span></td>
                            <td><span class="feedbiz_text"><em style="color:{$order_ext.channel_color|escape:'htmlall':'UTF-8'}">{$order_ext.channel_name|escape:'htmlall':'UTF-8'}</em></span>
                            </td>
                        </tr>
                    {/if}
                {/if}
            
                {if isset($order_multichannel)}
                    <tr>
                        <td><span class="feedbiz_label">{l s='Multichannel' mod='feedbiz'}:</span></td>
                        <td><span class="feedbiz_text"><em>{$order_multichannel|escape:'htmlall':'UTF-8'}</em></span>
                        </td>
                    </tr>
                {/if}
		
		{if isset($order_fulfillment_center_id)}
                    <tr>
                        <td><span class="feedbiz_label">{l s='FBA Center Id' mod='feedbiz'}:</span></td>
                        <td><span class="feedbiz_text"><em>{$order_fulfillment_center_id|escape:'htmlall':'UTF-8'}</em></span>
                        </td>
                    </tr>
                {/if}
             
                {if isset($order_shipping_type)}
                    <tr>
                        <td><span class="feedbiz_label">{l s='Ship Service Level' mod='feedbiz'}:</span></td>
                        <td><span class="feedbiz_text">{$order_shipping_type|escape:'htmlall':'UTF-8'}</span>
                    </tr>
                {/if}

		{if isset($order_is_prime)}
		    <tr>
			<td><span class="feedbiz_label">{l s='Prime Order' mod='feedbiz'}:</span></td>
			<td>
			    <span class="feedbiz_text">
				{if $order_is_prime}
				    <em class="feedbiz_red_text">{l s='Yes' mod='feedbiz'}</em>
				{else}
				    <em>{l s='No' mod='feedbiz'}</em>
				{/if}
			    </span>
			</td>
		    </tr>
		{/if}

		{if isset($order_is_prime) && $order_is_prime && isset($order_earliest_ship_date)}
                    <tr>
                        <td><span class="feedbiz_label">{l s='Earliest Ship Date' mod='feedbiz'}:</span></td>
                        <td><span class="feedbiz_text feedbiz_red_text">{$order_earliest_ship_date|escape:'htmlall':'UTF-8'}</span>
                        </td>
                    </tr>
                {/if}
        	   
		{if isset($order_is_prime) && $order_is_prime && isset($order_latest_ship_date)}
                    <tr>
                        <td><span class="feedbiz_label">{l s='Latest Ship Date' mod='feedbiz'}:</span></td>
                        <td><span class="feedbiz_text feedbiz_red_text">{$order_latest_ship_date|escape:'htmlall':'UTF-8'}</span>
                        </td>
                    </tr>
                {/if}
		
                {if isset($order_is_premium)}
		    <tr>
			<td><span class="feedbiz_label">{l s='Premium Order' mod='feedbiz'}:</span></td>
			<td>
			    <span class="feedbiz_text">
				{if $order_is_premium}
				    <em class="feedbiz_red_text">{l s='Yes' mod='feedbiz'}</em>
				{else}
				    <em>{l s='No' mod='feedbiz'}</em>
				{/if}
			    </span>
			</td>
		    </tr>
		{/if}
		
                {if isset($order_is_business)}
		    <tr>
			<td><span class="feedbiz_label">{l s='Business Order' mod='feedbiz'}:</span></td>
			<td>
			    <span class="feedbiz_text">
				{if $order_is_business}
				    <em class="feedbiz_red_text">{l s='Yes' mod='feedbiz'}</em>
				{else}
				    <em>{l s='No' mod='feedbiz'}</em>
				{/if}
			    </span>
			</td>
		    </tr>
		{/if}
		
            {/if} 

        </table>

        {if $ps_version_is_15}
    </fieldset>
    {/if}
</div>
</form>
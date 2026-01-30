{*
 * Facebook Conversion Pixel Tracking Plus
 *
 * NOTICE OF LICENSE
 *
 * @author    Pol Rué
 * @copyright Smart Modules 2014
 * @license   One time purchase Licence (You can modify or resell the product but just one time per licence)
 * @version 2.3.3
 * @category Marketing & Advertising
 * Registered Trademark & Property of smart-modules.com
 *
 * ***************************************************
 * *     Facebook Conversion Trackcing Pixel Plus    *
 * *          http://www.smart-modules.com           *
 * *                     V 2.3.3                     *
 * ***************************************************
 *
*}

    <!-- Pixel Plus: Add missing OG microdata -->
    {foreach from=$og_data key=key item=value}
        {if $key == 'og:retailer_item_id'}
            {if $combi_enabled && $product_combi > 0}
                <meta property="og:product:item_group_id" content="{$prefix|escape:'htmlall':'UTF-8'}{$value|escape:'htmlall':'UTF-8'}" />
                <meta property="og:retailer_item_id" content="{$prefix|escape:'htmlall':'UTF-8'}{$value|escape:'htmlall':'UTF-8'}{$combi_prefix|escape:'htmlall':'UTF-8'}{$combi|escape:'htmlall':'UTF-8'}" />
            {else}
                <meta property="{$key|escape:'htmlall':'UTF-8'}" content="{$prefix|escape:'htmlall':'UTF-8'}{$value|escape:'htmlall':'UTF-8'}" />
            {/if}
        {elseif $key == 'og:image'}
            {foreach from=$og_data.$key item=image}
                <meta property="og:image" content="{$image|escape:'htmlall':'UTF-8'}"/>
            {/foreach}
        {else}
            <meta property="{$key|escape:'htmlall':'UTF-8'}" content="{$value|escape:'htmlall':'UTF-8'}"/>
        {/if}
    {/foreach}
    <!-- {* <meta property="product:custom_label_0" content="{$ip} || {$localization_info}" /> *} -->
    <!-- End Pixel Plus: Add missing OG microdata -->

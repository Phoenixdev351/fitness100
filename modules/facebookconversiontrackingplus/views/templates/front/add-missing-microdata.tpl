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

    <!-- Pixel Plus: Add missing microdata -->
{if isset($schema.product) && !$schema.product}
    <div itemscope itemtype="http://schema.org/Product">
{/if}
    {foreach from=$micro_data.product key=key item=value}
        {if $key == 'image'}
            {foreach from=$value item=$image}
                <link itemprop="image" href="{$image|escape:'htmlall':'UTF-8'}">
            {/foreach}
        {else}
            <meta itemprop="{$key|escape:'htmlall':'UTF-8'}" content="{if $key == 'productID'}{$prefix|escape:'htmlall':'UTF-8'}{/if}{$value|escape:'htmlall':'UTF-8'}">
        {/if}
    {/foreach}
        {if isset($schema.offers) && !$schema.offers}
            <div itemprop="offers" itemscope itemtype="http://schema.org/Offer">
        {/if}

            {if isset($micro_data.offers)}
            {foreach from=$micro_data.offers key=key item=value}
                {if $key == 'condition'}
                    <link itemprop="itemCondition" href="http://schema.org/{if $value == 'new'}NewCondition{elseif $value == 'used'}UsedCondition{else}RefubrishedCondition{/if}">
                {elseif $key == 'availability'}
                    <link itemprop="itemAvailability" href="http://schema.org/{if $value == 'in stock'}InStock{else}OutOfStock{/if}">
                {else}
                    <meta itemprop="{$key|escape:'htmlall':'UTF-8'}" content="{$value|escape:'htmlall':'UTF-8'}">
                {/if}
            {/foreach}
            {/if}
        {if isset($schema.offers) && !$schema.offers}
            </div>
        {/if}
{if isset($schema.product) && !$schema.product}
    </div>
{/if}
<!-- End Pixel Plus: Add missing microdata -->

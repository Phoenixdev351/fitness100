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

{foreach from=$missing_micro key=type item=data}
    <h4><strong>{$type|strtoupper|escape:'htmlall':'UTF-8'}</strong></h4>
    <ul>
    {foreach from=$data key=field item=value}
        <li>{$field|escape:'htmlall':'UTF-8'}</li>
    {/foreach}
    </ul>
{/foreach}
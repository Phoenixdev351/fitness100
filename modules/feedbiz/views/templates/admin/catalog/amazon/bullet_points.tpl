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

{assign var="first" value="1"}
{assign var="item" value="1"}

<tr class="amazon-details amazon-item-title">
    <td class="col-left" rel="bullet_point"><span>{l s='Key Product Features' mod='feedbiz'}</span></td>
    <td style="padding-bottom: 5px;">
        {foreach array('bullet_point1', 'bullet_point2', 'bullet_point3', 'bullet_point4', 'bullet_point5') as $key}
            {assign var="bullet" value=null}

            {if $data.default == null}
                {$bullet = null}
            {elseif isset($data.default[$key])}
                {$bullet = trim($data.default[$key])}
            {/if}
            <span class="amazon-bullet-container-{$data.region|escape:'htmlall':'UTF-8'}"
                  {if empty($bullet) && $first != 1}style="display:none"{/if}>
				<input type="text" name="bullet_point{$item|escape:'htmlall':'UTF-8'}" value="{$bullet|escape:'htmlall':'UTF-8'}"
                       class="amazon-bullet-point"/>
				<span class="bulletpoint-action">
					{if $first}
                        <img src="{$img|escape:'htmlall':'UTF-8'}plus.png" alt="{l s='Add' mod='feedbiz'}"
                             class="amazon-bullet-point-add"/>
                        <img src="{$img|escape:'htmlall':'UTF-8'}minus.png" alt="{l s='Remove' mod='feedbiz'}"
                             style="display:none" class="amazon-bullet-point-del"/>

                                                {else}

                        <img src="{$img|escape:'htmlall':'UTF-8'}minus.png" alt="{l s='Add' mod='feedbiz'}"
                             class="amazon-bullet-point-del"/>
                        <img src="{$img|escape:'htmlall':'UTF-8'}plus.png" alt="{l s='Remove' mod='feedbiz'}"
                             style="display:none" class="amazon-bullet-point-add"/>
                    {/if}

                    {$first = false}
				</span><br/>
			</span>
            {$item = $item + 1}
        {/foreach}

        <span class="amz-small-line">{l s='You can add up to 5 bullets points, Up to 2000 characters per line.' mod='feedbiz'}</span><br/>
		<span class="amz-small-line propagation">{l s='Propagate this value to all products in this' mod='feedbiz'} :
			<a href="javascript:void(0)"
               class="fb-propagate-bullet_point-cat fb-link propagate">[ {l s='Category' mod='feedbiz'} ]</a>&nbsp;&nbsp;
			<a href="javascript:void(0)"
               class="fb-propagate-bullet_point-shop fb-link propagate">[ {l s='Shop' mod='feedbiz'} ]</a>&nbsp;&nbsp;
			<a href="javascript:void(0)"
               class="fb-propagate-bullet_point-manufacturer fb-link propagate">[ {l s='Manufacturer' mod='feedbiz'}
                ]</a>&nbsp;&nbsp;
			<a href="javascript:void(0)"
               class="fb-propagate-bullet_point-supplier fb-link propagate">[ {l s='Supplier' mod='feedbiz'} ]</a>&nbsp;&nbsp;
		</span>
        <input type="hidden" value="{l s='You can\'t add more than 5 bullet points !' mod='feedbiz'}"
               class="amz-text-max-bullet"/>
    </td>
</tr>
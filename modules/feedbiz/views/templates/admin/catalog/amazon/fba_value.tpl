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

<tr class="amazon-details amazon-item-title fba" rel="{$data.isFBA|escape:'htmlall':'UTF-8'}"
    {if !strlen($data.isFBA)}style="display:none;"{/if}>
    <td class="col-left">{l s='FBA - Value Added' mod='feedbiz'} </td>
    <td style="padding-bottom:5px;">
        <input type="text" name="fba_value" style="width:95px"
               value="{(floatval($data.default|escape:'htmlall':'UTF-8')) ? sprintf('%.02f', $data.default|escape:'htmlall':'UTF-8') : ''}" class="marketplace-price"/>
        <span style="margin-left:10px">{l s='Additionnal value for FBA handled items' mod='feedbiz'}</span><br/>
        <span class="amz-small-line">{l s='This value will be added to the product price. It overrides FBA Formula.' mod='feedbiz'}</span><br/>
	<span class="amz-small-line propagation">{l s='Propagate this value to all products in this' mod='feedbiz'} :
		<a href="javascript:void(0)"
           class="fb-propagate-fbavalue-cat fb-link propagate">[ {l s='Category' mod='feedbiz'} ]</a>&nbsp;&nbsp;
		<a href="javascript:void(0)" class="fb-propagate-fbavalue-shop fb-link propagate">[ {l s='Shop' mod='feedbiz'}
            ]</a>&nbsp;&nbsp;
		<a href="javascript:void(0)"
           class="fb-propagate-fbavalue-manufacturer fb-link propagate">[ {l s='Manufacturer' mod='feedbiz'} ]</a>&nbsp;&nbsp;
		<a href="javascript:void(0)"
           class="fb-propagate-fbavalue-supplier fb-link propagate">[ {l s='Supplier' mod='feedbiz'} ]</a>&nbsp;&nbsp;
	</span>
    </td>
</tr>
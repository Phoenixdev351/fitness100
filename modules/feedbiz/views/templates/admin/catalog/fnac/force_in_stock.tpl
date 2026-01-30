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

<tr class="fnac-details fnac-item-title">
    <td class="col-left">{l s='Force in Stock' mod='feedbiz'}</td>
    <td style="padding-bottom:5px;">
        <input type="text" name="force" value="{$data.default|escape:'htmlall':'UTF-8'}" style="width:95px"/>
        <span style="margin-left:10px">{l s='The product will always appear on Fnac, even it\'s out of Stock.' mod='feedbiz'}</span><br/>
	<span class="amz-small-line propagation">{l s='Propagate this value to all products in this' mod='feedbiz'} :
		<a href="javascript:void(0)" class="fb-propagate-force-cat fb-link propagate">[ {l s='Category' mod='feedbiz'}
            ]</a>&nbsp;&nbsp;
		<a href="javascript:void(0)" class="fb-propagate-force-shop fb-link propagate">[ {l s='Shop' mod='feedbiz'}
            ]</a>&nbsp;&nbsp;
		<a href="javascript:void(0)"
           class="fb-propagate-force-manufacturer fb-link propagate">[ {l s='Manufacturer' mod='feedbiz'} ]</a>&nbsp;&nbsp;
		<a href="javascript:void(0)"
           class="fb-propagate-force-supplier fb-link propagate">[ {l s='Supplier' mod='feedbiz'} ]</a>&nbsp;&nbsp;
	</span>
    </td>
</tr>
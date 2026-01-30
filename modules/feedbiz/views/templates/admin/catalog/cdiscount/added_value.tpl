{**
* NOTICE OF LICENSE
*
* This source file is subject to a commercial license from Common-Services Co., Ltd.
* Use, copy, modification or distribution of this source file without written
* license agreement from the SARL SMC is strictly forbidden.
* In order to obtain a license, please contact us: support.mondialrelay@common-services.com
* ...........................................................................
* INFORMATION SUR LA LICENCE D'UTILISATION
*
* L'utilisation de ce fichier source est soumise a une licence commerciale
* concedee par la societe Common-Services Co., Ltd.
* Toute utilisation, reproduction, modification ou distribution du present
* fichier source sans contrat de licence ecrit de la part de la Common-Services Co. Ltd. est
* expressement interdite.
* Pour obtenir une licence, veuillez contacter Common-Services Co., Ltd. a l'adresse: support.mondialrelay@common-services.com
* ...........................................................................
*
* @package   feedbiz
* @author    debuss-a
* @copyright Copyright (c) 2011-2022 Common Services Co Ltd - 90/25 Sukhumvit 81 - 10260 Bangkok - Thailand
* @license   Commercial license
* Support by mail  :  support.feedbiz@common-services.com
*}

<tr class="cdiscount-details cdiscount-item-title">
    <td class="col-left" rel="shipping_override"><span>{l s='Added Value' mod='feedbiz'}</span></td>
    <td style="padding-bottom:5px;">
        <input type="text" name="valueadded" value="{$data.default|escape:'htmlall':'UTF-8'}" style="width:60px"/>
        <br/>
        <span class="amz-small-line">{l s='Added value to be taken care by C Logistique.' mod='feedbiz'}</span><br/>
        <span class="amz-small-line propagation">{l s='Propagate this shipping price to all products in this' mod='feedbiz'}
            :
			<a href="javascript:void(0)"
               class="fb-propagate-valueadded-cat fb-link propagate">[ {l s='Category' mod='feedbiz'} ]</a>&nbsp;&nbsp;
			<a href="javascript:void(0)"
               class="fb-propagate-valueadded-shop fb-link propagate">[ {l s='Shop' mod='feedbiz'} ]</a>&nbsp;&nbsp;
			<a href="javascript:void(0)"
               class="fb-propagate-valueadded-manufacturer fb-link propagate">[ {l s='Manufacturer' mod='feedbiz'} ]</a>&nbsp;&nbsp;
			<a href="javascript:void(0)"
               class="fb-propagate-valueadded-supplier fb-link propagate">[ {l s='Supplier' mod='feedbiz'} ]</a>&nbsp;&nbsp;
		</span>
    </td>
</tr>
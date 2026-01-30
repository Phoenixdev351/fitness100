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

<tr class="amazon-details amazon-item-title">
    <td class="col-left" rel="repricing"><span>{l s='Custom Strategy' mod='feedbiz'}</span></td>
    <td style="padding-bottom:5px;" class="repricing">
        <input type="text" name="repricing_min" value="{$data.repricing_min|escape:'htmlall':'UTF-8'}"
               style="width:60px" class="marketplace-price"/>&nbsp;&nbsp;&nbsp;
        <img src="{$img|escape:'htmlall':'UTF-8'}down.png"/>

        <input type="text" name="repricing_max" value="{$data.repricing_max|escape:'htmlall':'UTF-8'}"
               style="width:60px" class="marketplace-price"/>&nbsp;&nbsp;&nbsp;
        <img src="{$img|escape:'htmlall':'UTF-8'}up.png"/>

        <br/>
        <span class="amz-small-line">{l s='Overrides the profile strategy by this custom strategy' mod='feedbiz'}</span><br/>
    </td>
</tr>
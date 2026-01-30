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

{if $ps16x}
    <div class="panel-footer">
        <button type="submit" value="1" name="validateForm"
                class="btn btn-default pull-right">
            <i class="process-icon-save"></i> {l s='Save Configuration' mod='feedbiz'}
        </button>
    </div>
{else}
    <input type="submit" class="button btn" style="width:180px;float:right;margin-right:50px;" name="validateForm"
           id="validateForm" value="{l s='Save Configuration' mod='feedbiz'}"/>
{/if}
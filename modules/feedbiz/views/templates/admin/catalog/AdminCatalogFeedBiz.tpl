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

{*}
<div>
    {if (isset($context_key))}
    <input type="hidden" name="context_key" value="{$context_key}" />
    {/if}
    <input type="hidden" id="serror" value="{l s='A server-side error has occured. Please contact your server administrator, hostmaster or webmaster' mod='feedbiz'}" />
    <input type="hidden" id="sdebug" value="{l s='You should click on this link to submit again in debug mode' mod='feedbiz'}" />
    <img style="float:right;" src="{$images}feedbiz.jpg" alt="{l s='Feed.biz' mod='feedbiz'}'.$this->l('Feed.biz').'" />
</div>
<div style="clear:both;padding-bottom:20px;"></div>
<fieldset class="panel form-horizontal">
    <legend><img src="{$images}logo.gif" alt="" style="vertical-align: middle" />{l s='Export' mod='feedbiz'}</legend>
     <p>{l s='Please choose the context' mod='feedbiz'}</p>
     <br />
     <ul id="menuTab">      
          <li id="menu-update" class="menuTabButton {$tab_selected_update}"><span>&nbsp;<img src="{$images}export.gif" alt="{l s='Update Mode' mod='feedbiz'}" />{l s='Offers' mod='feedbiz'}</span>  </li>
          <li id="menu-create" class="menuTabButton {$tab_selected_create}"><span>&nbsp;<img src="{$images}export.gif" alt="{l s='Creation Mode' mod='feedbiz'}" />{l s='Products' mod='feedbiz'}</span>  </li>
     </ul>
     <div id="tabList">
         {if (isset($tab_update_data))}
         {include file="products_update.tpl" tab_update_data=$tab_update_data}
         {/if}
         {if (isset($tab_create_data))}
         {include file="products_create.tpl" tab_create_data=$tab_create_data}
         {/if}
     </div>
     <br />
</fieldset>
{*}
<fieldset>
         {if (isset($tab_update_data))}
         {include file="products_update.tpl" tab_update_data=$tab_update_data}
         {/if}

         {if (isset($tab_create_data))}
         {include file="products_create.tpl" tab_create_data=$tab_create_data}
         {/if}
</fieldset>
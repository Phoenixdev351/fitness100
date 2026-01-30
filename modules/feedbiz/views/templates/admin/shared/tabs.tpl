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
 *
 * @author    Olivier B.
 * @copyright Copyright (c) 2011-2022 Feed.Biz - Hong Kong - Head Quarters - Room 1408, 14/F,
 *            Tak Shing House - Theatre Lane - 20 des Voeux Road Central, -Central, Hong Kong
 * @license   Commercial license
 * Support by mail  :  support@feed.biz
 *}

{if $ps16x}
    <link href="{$module_url|escape:'htmlall':'UTF-8'}/views/css/shared/shared_conf16.css" rel="stylesheet" type="text/css" media="all">
{else}
    <link href="{$module_url|escape:'htmlall':'UTF-8'}/views/css/shared/shared_conf.css" rel="stylesheet" type="text/css" media="all">
{/if}

<script type="text/javascript" src="{$module_url|escape:'htmlall':'UTF-8'}/views/js/shared/configure_tabs.js"></script>

{if $has_line}
    {for $i = 1 to $line_number}
        {if is_array($tab_list) && count($tab_list)}
            <ul class="nav" id="menuTab">
              {foreach from=$tab_list  item=tab}
                  {if isset($tab.line) && $tab.line == $i || !isset($tab.line) && $i == 1}
                      <li id="menu-{$tab.id|escape:'htmlall':'UTF-8'}" class="menuTabButton {if $tab.selected}selected{/if}">
                        {if $tab.img === $module_name}
                            <a href="#"><span>&nbsp;<img src="{$module_url|escape:'htmlall':'UTF-8'}logo.png" style="max-width: 32px; height: 32px;" alt=""/>&nbsp;{$tab.name|escape:none:'UTF-8'}</span></a>
                              {else}
                            <a href="#"><span>&nbsp;<img src="{$img_dir|escape:'htmlall':'UTF-8'}{$tab.img|escape:'htmlall':'UTF-8'}.png" alt=""/>&nbsp;{$tab.name|escape:none:'UTF-8'}</span></a>
                              {/if}
                      </li>
                  {/if}
              {/foreach}
            </ul>
            {*<div class="clearfix">&nbsp;</div>*}
        {/if}
    {/for}
{else if ($is_feedbiz && $feedbiz_mode == 'SIMPLE') || (isset($mode_use) && $mode_use == 'SIMPLE' && $is_cdiscount)}
    <ul class="nav" id="menuTab">
      {if is_array($tab_list) && count($tab_list)}
          {foreach $tab_list as $tab}
              <li id="menu-{$tab['id']|escape:'htmlall':'UTF-8'}" class="menuTabButton {if $tab['selected']|escape:'htmlall':'UTF-8'}selected{/if}" style="text-align: center; height: 100px;" {if isset($tab['send_survey'])} send_survey='1'{/if}>
                <img src="{$img_dir|escape:'htmlall':'UTF-8'}{$tab['img']|escape:'htmlall':'UTF-8'}.png">
                <a href="javascript:void(0)">{$tab['name']|escape:'htmlall':'UTF-8'}</a>
              </li>
          {/foreach} 
      {/if}
    </ul>
{else}
    <ul class="nav" id="menuTab">
      {if is_array($tab_list) && count($tab_list)}
          {foreach from=$tab_list  item=tab}
              <li id="menu-{$tab.id|escape:'htmlall':'UTF-8'}" class="menuTabButton {if $tab.selected}selected{/if}" {if isset($tab['send_survey'])} send_survey='1'{/if}>
                {if $tab.img === $module_name}
                    <a href="#"><span>&nbsp;<img src="{$module_url|escape:'htmlall':'UTF-8'}logo.png" style="max-width: 32px; height: 32px;" alt=""/>&nbsp;{$tab.name|escape:none:'UTF-8'}</span></a>
                      {else}
                    <a href="#"><span>&nbsp;<img src="{$img_dir|escape:'htmlall':'UTF-8'}{$tab.img|escape:'htmlall':'UTF-8'}.png" alt=""/>&nbsp;{$tab.name|escape:none:'UTF-8'}</span></a>
                      {/if}
              </li>
          {/foreach}
      {/if}
    </ul>
{/if}
<div id="ps16_tabs_separator"></div>
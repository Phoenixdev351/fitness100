{*
* Affiliates
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
*
* @author    FMM Modules
* @copyright © Copyright 2021 - All right reserved
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* @category  FMM Modules
* @package   affiliates
*}

<div class="panel">
  <h3>{l s='info' mod='affiliates'}</h3>
  <p>
    {if ($nbrLevels le 0) AND ($nbrRules le 0)}
      <p class="alert alert-info info">{l s='You do not have any order reward or registration rule for affilaite levels.' mod='affiliates'}</p>
    {elseif $nbrRules le 0}
      <p class="alert alert-info info">{l s='You do not have any registration rule for affilaite levels.' mod='affiliates'}</p>
    {elseif $nbrLevels le 0}
      <p class="alert alert-info info">{l s='You do not have any order reward rule for affilaite levels.' mod='affiliates'}</p>
    {/if}
    <ul>
      {if $nbrLevels le 0}
        <li>
          <a href="{$link->getAdminLink('AdminLevels')|escape:'htmlall':'UTF-8'}&addaffiliate_levels">
            <strong>{l s='Click here' mod='affiliates'}</strong>
          </a>&nbsp;{l s='to add your first order rule.' mod='affiliates'}
        </li>
      {/if}
      {if $nbrRules le 0}
        <li>
          <a href="{$link->getAdminLink('AdminRules')|escape:'htmlall':'UTF-8'}&addaffiliate_rules">
            <strong>{l s='Click here' mod='affiliates'}</strong>
          </a>&nbsp;{l s='to add your first registration rule.' mod='affiliates'}
        </li>
      {/if}
    </ul>
  </p>
</div>
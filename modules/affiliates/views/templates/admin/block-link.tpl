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

<a class="btn btn-default"
    href="{$blockLink|escape:'htmlall':'UTF-8'}"
    title="{if $active}{l s='Temporary Block' mod='affiliates'}{else}{l s='Unblock' mod='affiliates'}{/if}">
    <img src="{$uri|escape:'htmlall':'UTF-8'}views/img/{if $active}tick.png{else}cancel.png{/if}">
</a>
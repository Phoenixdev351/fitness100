{**
*
* NOTICE OF LICENSE
*
* This product is licensed for one customer to use on one installation (test stores and multishop included).
* Site developer has the right to modify this module to suit their needs, but can not redistribute the module in
* whole or in part. Any other use of this module constitutes a violation of the user agreement.
*
* DISCLAIMER
*
* NO WARRANTIES OF DATA SAFETY OR MODULE SECURITY
* ARE EXPRESSED OR IMPLIED. USE THIS MODULE IN ACCORDANCE
* WITH YOUR MERCHANT AGREEMENT, KNOWING THAT VIOLATIONS OF
* PCI COMPLIANCY OR A DATA BREACH CAN COST THOUSANDS OF DOLLARS
* IN FINES AND DAMAGE A STORES REPUTATION. USE AT YOUR OWN RISK.
*
*  @author    idnovate.com <info@idnovate.com>
*  @copyright 2020 idnovate.com
*  @license   See above
*}

<style type="text/css">
    {if isset($imageBackground) && $imageBackground}
        .apc-popup-{$popupId|intval} .fancybox-skin {
            background-image: url({$imageBackground|escape:'html':'UTF-8'}) !important;
            background-size: cover !important;
            background-repeat: no-repeat !important;
            background-position: center center !important;
        }
    {/if}

    {if ($position && $position != 5)}
        .apc-popup-{$popupId|intval} {
        {if ($position|intval == 1)}
            top: 2% !important;
            right: auto !important;
            bottom: auto !important;
            left: 2% !important;
        {elseif ($position|intval == 2)}
            top: 2% !important;
            bottom: auto !important;
        {elseif ($position|intval == 3)}
            top: 2% !important;
            right: 2% !important;
            bottom: auto !important;
            left: auto !important;
        {elseif ($position|intval == 4)}
            right: auto !important;
            left: 2% !important;
        {elseif ($position|intval == 5)}
            {* Centered by default *}
        {elseif ($position|intval == 6)}
            right: 2% !important;
            left: auto !important;
        {elseif ($position|intval == 7)}
            top: auto !important;
            right: auto !important;
            bottom: 2% !important;
            left: 2% !important;
        {elseif ($position|intval == 8)}
            top: auto !important;
            bottom: 2% !important;
        {elseif ($position|intval == 9)}
            top: auto !important;
            right: 2% !important;
            bottom: 2% !important;
            left: auto !important;
        {/if}
    }
    {/if}

    {if ($dontDisplayAgain)}
        .apc-popup-{$popupId|intval} .dont-show-again {
            bottom: 0;
            right: 0;
            position: absolute;
            background-color: white;
            font-size: 0.8em;
        }

        .apc-popup-{$popupId|intval} .dont-show-again a {
            color: black;
        }
    {/if}

    {if ($colorBackground)}
        .apc-popup-{$popupId|intval} .fancybox-skin {
            background-color: {$colorBackground|escape:'html':'UTF-8'} !important;
        }
    {/if}

    .apc-popup-{$popupId|intval} .modal-img {
        max-width: 100%;
        height: auto;
    }

    {if $lsHeight == '100%' && $lsWidth == '100%'}
        .apc-popup-{$popupId|intval} .fancybox-skin .fancybox-close {
            top: 0;
            right: 0;
        }
    {/if}

    {if isset($lsPopupCss) && $lsPopupCss}
        {$lsPopupCss nofilter}
    {/if}
</style>

<div style="display: none" class="apc_modal" id="apc_modal_{$popupId|intval}" tabindex="-1" role="dialog" data-popup-id="{$popupId|intval}" data-secs-close="{$lfSecsToClose|intval}" data-secs-display="{$lfSecsToDisplay|intval}" data-secs-display-cart="{$lfSecsToDisplayCart|intval}" data-opacity="{$lsBackOpacityValue|escape:'html':'UTF-8'}" data-height="{$lsHeight|escape:'html':'UTF-8'}" data-width="{$lsWidth|escape:'html':'UTF-8'}" data-padding="{$lsPadding|intval}" data-locked="{$lbLocked|escape:'html':'UTF-8'}" data-close-background="{$lbCloseOnBackground|escape:'html':'UTF-8'}" data-css="{$lsCssClass|escape:'html':'UTF-8'}" data-blur-background="{$lsBlurBackground|escape:'html':'UTF-8'}" data-open-effect="{$openEffect|escape:'html':'UTF-8'}">
    {if $lsContent}{$lsContent nofilter}{/if}
    {if ($dontDisplayAgain)}<div class="dont-show-again"><a href="#" onclick="dontDisplayAgain({$popupId|intval})">{l s='Don\'t show this message again' mod='advancedpopupcreator'}</a></div>{/if}
</div>

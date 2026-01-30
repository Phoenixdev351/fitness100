{**
 * 2007-2016 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2016 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}
{foreach $stylesheets.external as $stylesheet}
  {if $stylesheet.id == 'stthemeeditor-google-fonts'}
    <!-- connect to domain of font files -->
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- optionally increase loading priority -->
    <link rel="preload" as="style" href="{$stylesheet.uri}&display=swap">

    <!-- async CSS -->
    <link rel="stylesheet" media="print" onload="this.onload=null;this.removeAttribute('media');" href="{$stylesheet.uri}&display=swap">

    <!-- no-JS fallback -->
    <noscript>
        <link rel="stylesheet" href="{$stylesheet.uri}&display=swap">
    </noscript>
  {else}
    <link rel="stylesheet" href="{$stylesheet.uri}" type="text/css" media="{$stylesheet.media}">
  {/if}
{/foreach}

{foreach $stylesheets.inline as $stylesheet}
  <style>
    {$stylesheet.content}
  </style>
{/foreach}


{if isset($sttheme.custom_css) && count($sttheme.custom_css)}
  {foreach $sttheme.custom_css as $css}
  <link href="{$css.url}" id="{$css.id}" rel="stylesheet" media="{$sttheme.custom_css_media}" />
  {/foreach}
{/if}
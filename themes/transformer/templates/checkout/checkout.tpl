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
<!doctype html>
<html lang="{$language.iso_code}">

  <head>
    {block name='head'}
      {include file='_partials/head.tpl'}
    {/block}
  </head>

  <body id="{$page.page_name}" class="{$page.page_name} {$page.body_classes|classnames} lang_{$language.iso_code} {if $language.is_rtl} is_rtl {/if}
  dropdown_menu_event_{(int)$sttheme.drop_down} 
  {if $sttheme.is_mobile_device} mobile_device {if $sttheme.use_mobile_header==1} use_mobile_header {/if}{else} desktop_device {/if}
  {if $sttheme.use_mobile_header==2} use_mobile_header {/if}
  {block name='body_class'} hide-left-column hide-right-column {/block}">

    {block name='hook_after_body_opening_tag'}
      {hook h='displayAfterBodyOpeningTag'}
    {/block}

  <div id="st-container" class="st-container st-effect-{$sttheme.sidebar_transition}">
    <div class="st-pusher">
    <div class="st-content"><!-- this is the wrapper for the content -->
      <div class="st-content-inner">
  <!-- off-canvas-end -->
  <main id="body_wrapper" class="no_sidebar">
    {if isset($sttheme.boxstyle) && $sttheme.boxstyle==2}<div id="page_wrapper">{/if}
    <div class="header-container">
      <header id="st_header" class="animated fast">
      {block name='checkout_header'}
        {if $sttheme.checkout_same_header}{include file='_partials/header.tpl'}{else}{include file='checkout/_partials/header.tpl'}{/if}
      {/block}
      </header>
    </div>

    {block name='notifications'}
      {include file='_partials/notifications.tpl'}
    {/block}

    <section id="wrapper" class="checkout_wrapper">
      <div class="container">

      {block name='content'}
        <section id="content">
          <div class="row">
            <div class="col-lg-4 checkout_right_wrapper flex-last cart-grid-right">
              <div class="checkout_right_column mb-3">

              {block name='cart_summary'}
                {include file='checkout/_partials/cart-summary.tpl' cart = $cart}
              {/block}

              {hook h='displayReassurance'}
              </div>
            </div>
            <div class="col-lg-8 checkout_left_wrapper cart-grid-body">
              <div class="checkout_left_column mb-3">
              {block name='checkout_process'}
                {render file='checkout/checkout-process.tpl' ui=$checkout_process}
              {/block}
              </div>
            </div>
          </div>
        </section>
      {/block}
      </div>
    </section>
    {block name="checkout_full_width_bottom"}
      <div class="full_width_bottom_container">{hook h="displayFullWidthBottom"}</div>
      <div class="wrapper_bottom_container">{hook h="displayWrapperBottom"}</div>
    {/block}
    {block name='checkout_footer'}
      {if $sttheme.checkout_same_footer}
        {include file='_partials/footer.tpl'}
      {else}
        <footer id="footer" class="footer-container">
        {include file='_partials/footer-bottom.tpl'}
        </footer>
      {/if}
    {/block}
    {if isset($sttheme.boxstyle) && $sttheme.boxstyle==2}</div>{/if}
  </main>
  <!-- off-canvas-begin -->
      <div id="st-content-inner-after" data-version="{if isset($sttheme.ps_version)}{$sttheme.ps_version|replace:'.':'-'}{/if}-{if isset($sttheme.theme_version)}{$sttheme.theme_version|replace:'.':'-'}{/if}"></div>
      </div><!-- /st-content-inner -->
    </div><!-- /st-content -->
    <div id="st-pusher-after"></div>
    </div><!-- /st-pusher -->

    {block name="side_bar"}   
    {if $sttheme.checkout_same_header}
      {hook h="displaySideBar"}
      <div id="sidebar_box" class="flex_container">
        {hook h="displayRightBar"}
      </div>
    {else}
    <div class="st-menu" id="checkout_mobile_nav">
      <div class="mobile_nav_box">
      {hook h="displayCheckoutMobileNav"}
      </div>
    </div>
    {/if}
    {/block}

  </div><!-- /st-container -->
  <!-- off-canvas-end -->

    {block name='javascript_bottom'}
      {include file="_partials/javascript.tpl" javascript=$javascript.bottom}
    {/block}
    {if isset($sttheme.custom_js) && $sttheme.custom_js}
      <script type="text/javascript" src="{$sttheme.custom_js}"></script>
    {/if}
    {if isset($sttheme.tracking_code) && $sttheme.tracking_code}{$sttheme.tracking_code nofilter}{/if}

    {block name='hook_before_body_closing_tag'}
      {hook h='displayBeforeBodyClosingTag'}
    {/block}

  </body>

</html>

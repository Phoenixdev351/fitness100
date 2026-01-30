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
{block name='head_charset'}
  <meta charset="utf-8">
{/block}
{block name='head_ie_compatibility'}
  <meta http-equiv="x-ua-compatible" content="ie=edge">
{/block}

{* CRITICAL FIX: Block ALL canonical generation at the very start - BEFORE any output *}
{* This prevents PrestaShop core, parent templates, and modules from outputting canonical *}
{assign var='page.canonical' value='' scope='global'}
{assign var='canonical_printed_global' value=1 scope='global'}
{* Force unset to prevent any code from using it *}
{if isset($page.canonical)}
  {assign var='page.canonical' value='' scope='global'}
{/if}

<link rel="preconnect" href="https://www.100x100fitness.com" />
<link rel="dns-prefetch" href="https://www.100x100fitness.com" />

{* Output OUR canonical FIRST - this is the ONLY canonical that should appear *}
{* Fix #3: Pagination - self-reference canonical (keep pagination in URL) *}
{assign var='has_pagination' value=0}
{if isset($urls.current_url) && ($urls.current_url|strstr:'?page=' || $urls.current_url|strstr:'&page=' || $urls.current_url|strstr:'page-' || $urls.current_url|strstr:'/page/')}
  {assign var='has_pagination' value=1}
{/if}
{* Also check if pagination object exists (for blog) *}
{if !$has_pagination && (isset($pagination) && isset($pagination.current_page) && $pagination.current_page > 1)}
  {assign var='has_pagination' value=1}
{/if}
{* Also check listing pagination (for categories) *}
{if !$has_pagination && (isset($listing.pagination) && isset($listing.pagination.current_page) && $listing.pagination.current_page > 1)}
  {assign var='has_pagination' value=1}
{/if}

{if $has_pagination == 1}
  {* Pagination detected - use current URL as canonical (self-reference) *}
  <link rel="canonical" href="{$urls.current_url}">
{elseif $page.page_name == 'index'}
  {* Home page - always canonical to base URL *}
  <link rel="canonical" href="{$urls.base_url}">
{else}
  {* All other pages (products, categories, etc.) - use current URL *}
  <link rel="canonical" href="{$urls.current_url}">
{/if}

{* CRITICAL: Completely override head_seo block to prevent parent template from outputting canonical *}
{* PrestaShop parent template might output canonical - we prevent it by unsetting BEFORE block starts *}
{assign var='page.canonical' value='' scope='global'}
{assign var='canonical_printed_global' value=1 scope='global'}

{* CRITICAL FIX: Capture head_seo block output and filter out any canonical tags *}
{capture name='head_seo_content'}
{block name='head_seo'}
  {* CRITICAL: Unset $page.canonical IMMEDIATELY - before any parent template code can use it *}
  {assign var='page.canonical' value='' scope='global'}
  {assign var='canonical_printed_global' value=1 scope='global'}
  {* Force unset multiple times to ensure it stays empty *}
  {if isset($page.canonical)}
    {assign var='page.canonical' value='' scope='global'}
  {/if}
  
  <title>{block name='head_seo_title'}{$page.meta.title}{/block}</title>
  <meta name="description" content="{block name='head_seo_description'}{$page.meta.description}{/block}">
  <meta name="keywords" content="{block name='head_seo_keywords'}{$page.meta.keywords}{/block}">
  {if $page.meta.robots !== 'index'}
    <meta name="robots" content="{$page.meta.robots}">
  {/if}
  {* CRITICAL: DO NOT output canonical here - already output above on line ~67 *}
  {* PrestaShop parent template might try to output {if $page.canonical}<link rel="canonical"> - we prevent it *}
  {* CRITICAL FIX: Check if PrestaShop is trying to output canonical and block it completely *}
  {* PrestaShop might output: {if $page.canonical}<link rel="canonical" href="{$page.canonical}">{/if} *}
  {* We prevent this by ensuring $page.canonical is always empty *}
  {assign var='page.canonical' value='' scope='global'}
  {assign var='canonical_printed_global' value=1 scope='global'}
  {* Additional check - if canonical was set by parent, unset it again *}
  {if isset($page.canonical) && $page.canonical != ''}
    {assign var='page.canonical' value='' scope='global'}
  {/if}
  {* CRITICAL: Explicitly prevent any canonical output - even if parent template tries *}
  {* Do NOT output {if $page.canonical} here - we've already output canonical above *}
  
  {block name='head_hreflang'}
    {if isset($urls.alternative_langs)}
      {foreach from=$urls.alternative_langs item=pageUrl key=code}
            <link rel="alternate" href="{$pageUrl}" hreflang="{$code}">
      {/foreach}
    {/if}
  {/block}
  {* NOTE: rel="next" and rel="prev" are output AFTER this block to prevent filtering *}
{/block}
{/capture}

{* CRITICAL: Filter out ANY canonical tags from head_seo block output - COMPLETELY REMOVE them *}
{assign var='head_seo_output' value=$smarty.capture.head_seo_content}
{* Remove all variations of canonical tags - try to remove entire tags *}
{* Since Smarty replace can't easily remove entire tags, we'll replace with empty string for common patterns *}
{* Method 1: Remove canonical tags with common URL patterns *}
{assign var='head_seo_output' value=$head_seo_output|replace:'<link rel="canonical" href="https://100x100fitness.com':'<!-- CANONICAL REMOVED -->'}
{assign var='head_seo_output' value=$head_seo_output|replace:"<link rel='canonical' href='https://100x100fitness.com":"<!-- CANONICAL REMOVED -->"}
{* Method 2: Remove canonical tags with http:// *}
{assign var='head_seo_output' value=$head_seo_output|replace:'<link rel="canonical" href="http://100x100fitness.com':'<!-- CANONICAL REMOVED -->'}
{assign var='head_seo_output' value=$head_seo_output|replace:"<link rel='canonical' href='http://100x100fitness.com":"<!-- CANONICAL REMOVED -->"}
{* Method 3: Remove any remaining canonical tags by replacing the opening part *}
{assign var='head_seo_output' value=$head_seo_output|replace:'<link rel="canonical"':'<!-- CANONICAL REMOVED -->'}
{assign var='head_seo_output' value=$head_seo_output|replace:"<link rel='canonical'":"<!-- CANONICAL REMOVED -->"}
{assign var='head_seo_output' value=$head_seo_output|replace:'<link rel = "canonical"':'<!-- CANONICAL REMOVED -->'}
{assign var='head_seo_output' value=$head_seo_output|replace:"<link rel = 'canonical'":"<!-- CANONICAL REMOVED -->"}
{* Method 4: Remove any remaining rel="canonical" attributes *}
{assign var='head_seo_output' value=$head_seo_output|replace:' rel="canonical"':'<!-- CANONICAL REMOVED -->'}
{assign var='head_seo_output' value=$head_seo_output|replace:" rel='canonical'":"<!-- CANONICAL REMOVED -->"}
{* Method 5: Remove disabled canonical tags completely *}
{assign var='head_seo_output' value=$head_seo_output|replace:'<link rel="canonical-disabled"':'<!-- CANONICAL REMOVED -->'}
{assign var='head_seo_output' value=$head_seo_output|replace:"<link rel='canonical-disabled'":"<!-- CANONICAL REMOVED -->"}
{assign var='head_seo_output' value=$head_seo_output|replace:' rel="canonical-disabled"':'<!-- CANONICAL REMOVED -->'}
{assign var='head_seo_output' value=$head_seo_output|replace:" rel='canonical-disabled'":"<!-- CANONICAL REMOVED -->"}
{* Method 6: Clean up any orphaned href attributes or closing tags left behind *}
{assign var='head_seo_output' value=$head_seo_output|replace:' href="https://100x100fitness.com/airun-z">':'<!-- CANONICAL REMOVED -->'}
{assign var='head_seo_output' value=$head_seo_output|replace:" href='https://100x100fitness.com/airun-z'>":"<!-- CANONICAL REMOVED -->"}

{* Fix #7: Filter out duplicate rel="next" and rel="prev" tags from head_seo block *}
{* PrestaShop core or parent templates might output these - we remove them since we output our own single version *}
{* Remove rel="next" tags - AGGRESSIVE removal to catch all variations and complete tags *}
{* Method 1: Remove complete tags with href attribute (most common) *}
{assign var='head_seo_output' value=$head_seo_output|regex_replace:'/<link[^>]*rel=["\']next["\'][^>]*>/i':'<!-- REL="NEXT" REMOVED -->'}
{assign var='head_seo_output' value=$head_seo_output|regex_replace:'/<link[^>]*rel=["\']next["\'][^>]*\/>/i':'<!-- REL="NEXT" REMOVED -->'}
{* Method 2: Remove tags with rel="next" anywhere in the tag *}
{assign var='head_seo_output' value=$head_seo_output|regex_replace:'/<link[^>]*rel\s*=\s*["\']next["\'][^>]*>/i':'<!-- REL="NEXT" REMOVED -->'}
{* Method 3: Remove any remaining rel="next" patterns *}
{assign var='head_seo_output' value=$head_seo_output|replace:'<link rel="next"':'<!-- REL="NEXT" REMOVED -->'}
{assign var='head_seo_output' value=$head_seo_output|replace:"<link rel='next'":"<!-- REL='NEXT' REMOVED -->"}
{assign var='head_seo_output' value=$head_seo_output|replace:'<link rel = "next"':'<!-- REL="NEXT" REMOVED -->'}
{assign var='head_seo_output' value=$head_seo_output|replace:"<link rel = 'next'":"<!-- REL='NEXT' REMOVED -->"}
{assign var='head_seo_output' value=$head_seo_output|replace:' rel="next"':'<!-- REL="NEXT" REMOVED -->'}
{assign var='head_seo_output' value=$head_seo_output|replace:" rel='next'":"<!-- REL='NEXT' REMOVED -->"}
{* Remove rel="prev" tags - AGGRESSIVE removal to catch all variations and complete tags *}
{* Method 1: Remove complete tags with href attribute (most common) *}
{assign var='head_seo_output' value=$head_seo_output|regex_replace:'/<link[^>]*rel=["\']prev["\'][^>]*>/i':'<!-- REL="PREV" REMOVED -->'}
{assign var='head_seo_output' value=$head_seo_output|regex_replace:'/<link[^>]*rel=["\']prev["\'][^>]*\/>/i':'<!-- REL="PREV" REMOVED -->'}
{* Method 2: Remove tags with rel="prev" anywhere in the tag *}
{assign var='head_seo_output' value=$head_seo_output|regex_replace:'/<link[^>]*rel\s*=\s*["\']prev["\'][^>]*>/i':'<!-- REL="PREV" REMOVED -->'}
{* Method 3: Remove any remaining rel="prev" patterns *}
{assign var='head_seo_output' value=$head_seo_output|replace:'<link rel="prev"':'<!-- REL="PREV" REMOVED -->'}
{assign var='head_seo_output' value=$head_seo_output|replace:"<link rel='prev'":"<!-- REL='PREV' REMOVED -->"}
{assign var='head_seo_output' value=$head_seo_output|replace:'<link rel = "prev"':'<!-- REL="PREV" REMOVED -->'}
{assign var='head_seo_output' value=$head_seo_output|replace:"<link rel = 'prev'":"<!-- REL='PREV' REMOVED -->"}
{assign var='head_seo_output' value=$head_seo_output|replace:' rel="prev"':'<!-- REL="PREV" REMOVED -->'}
{assign var='head_seo_output' value=$head_seo_output|replace:" rel='prev'":"<!-- REL='PREV' REMOVED -->"}

{* Output the filtered content *}
{$head_seo_output nofilter}

{* Fix #7: Single rel="prev" and rel="next" to prevent duplicates - Handle both category and blog pagination *}
{* Output AFTER head_seo block to ensure our tags are the only ones that appear *}
{assign var='prev_found' value=0}
{assign var='next_found' value=0}
{* Category pagination *}
{if isset($listing.pagination.pages)}
  {foreach from=$listing.pagination.pages item="page"}
    {if $page.clickable && $page.type === 'previous' && $prev_found == 0}
      <link rel="prev" href="{$page.url}" />
      {assign var='prev_found' value=1}
    {elseif $page.clickable && $page.type === 'next' && $next_found == 0}
      <link rel="next" href="{$page.url}" />
      {assign var='next_found' value=1}
    {/if}
  {/foreach}
{/if}
{* Blog pagination *}
{if isset($pagination) && isset($pagination.pages) && !$prev_found && !$next_found}
  {foreach from=$pagination.pages item="page"}
    {if isset($page.clickable) && $page.clickable && isset($page.type)}
      {if $page.type === 'previous' && $prev_found == 0}
        <link rel="prev" href="{$page.url}" />
        {assign var='prev_found' value=1}
      {elseif $page.type === 'next' && $next_found == 0}
        <link rel="next" href="{$page.url}" />
        {assign var='next_found' value=1}
      {/if}
    {/if}
  {/foreach}
{/if}

{* Fix #8: Add structured data to Home page *}
{if $page.page_name == 'index'}
<script type="application/ld+json">
{ldelim}
  "@context": "https://schema.org",
  "@type": "OnlineStore",
  "name": "{$shop.name|escape:'html':'UTF-8'|escape:'javascript'}",
  "url": "{$urls.base_url}",
  "logo": "{$shop.logo}",
  "description": "{$page.meta.description|escape:'html':'UTF-8'|escape:'javascript'}",
  "priceRange": "€€",
  "address": {ldelim}
    "@type": "PostalAddress",
    "addressCountry": "ES"
  {rdelim}
{rdelim}
</script>
<script type="application/ld+json">
{ldelim}
  "@context": "https://schema.org",
  "@type": "WebSite",
  "name": "{$shop.name|escape:'html':'UTF-8'|escape:'javascript'}",
  "url": "{$urls.base_url}",
  "potentialAction": {ldelim}
    "@type": "SearchAction",
    "target": "{$urls.pages.search}?controller=search&orderby=position&orderway=desc&search_query={ldelim}search_term_string{rdelim}",
    "query-input": "required name=search_term_string"
  {rdelim}
{rdelim}
</script>
{/if}

{* Fix #10: Blog structured data on blog home page - Always show on blog page *}
{if !isset($stblog_post) && ($page.page_name == 'module-stblog-blogs' || (isset($stblog_posts) && $stblog_posts) || (isset($page.meta.title) && $page.meta.title == 'Blog'))}
<script type="application/ld+json">
{ldelim}
  "@context": "https://schema.org",
  "@type": "Blog",
  "name": "{$shop.name|escape:'html':'UTF-8'|escape:'javascript'} Blog",
  "url": "{if isset($stblog_blog_url)}{$stblog_blog_url}{else}{$urls.base_url}blog{/if}",
  "description": "{if isset($page.meta.description) && $page.meta.description && $page.meta.description != 'Blog'}{$page.meta.description|escape:'html':'UTF-8'|escape:'javascript'}{else}Blog de {$shop.name|escape:'html':'UTF-8'|escape:'javascript'}{/if}"
  {if isset($stblog_posts) && $stblog_posts && count($stblog_posts) > 0},
  "itemListElement": [
    {foreach from=$stblog_posts item=post name=posts}
    {ldelim}
      "@type": "ListItem",
      "position": {$smarty.foreach.posts.iteration},
      "url": "{if isset($post.url)}{$post.url}{else}{$urls.base_url}blog/{$post.id_st_blog}{/if}"
    {rdelim}{if !$smarty.foreach.posts.last},{/if}
    {/foreach}
  ]
  {/if}
{rdelim}
</script>
{/if}

{* Fix #11: Replace NewsArticle/WebPage with BlogPosting in blog articles *}
{if isset($stblog_post) && $stblog_post}
<script type="application/ld+json">
{ldelim}
  "@context": "https://schema.org",
  "@type": "BlogPosting",
  "headline": "{if isset($stblog_post.title)}{$stblog_post.title|escape:'html':'UTF-8'|escape:'javascript'}{else}{$page.meta.title|escape:'html':'UTF-8'|escape:'javascript'}{/if}",
  "description": "{if isset($stblog_post.meta_description) && $stblog_post.meta_description}{$stblog_post.meta_description|escape:'html':'UTF-8'|escape:'javascript'}{else}{$page.meta.description|escape:'html':'UTF-8'|escape:'javascript'}{/if}",
  "url": "{$urls.current_url}",
  "datePublished": "{if isset($stblog_post.date_add)}{$stblog_post.date_add|date_format:'%Y-%m-%d'}{else}{$page.meta.published_time|default:''}{/if}",
  "dateModified": "{if isset($stblog_post.date_upd)}{$stblog_post.date_upd|date_format:'%Y-%m-%d'}{else}{if isset($stblog_post.date_add)}{$stblog_post.date_add|date_format:'%Y-%m-%d'}{else}{$page.meta.modified_time|default:''}{/if}{/if}",
  "author": {ldelim}
    "@type": "Organization",
    "name": "{$shop.name|escape:'html':'UTF-8'|escape:'javascript'}"
  {rdelim},
  "publisher": {ldelim}
    "@type": "Organization",
    "name": "{$shop.name|escape:'html':'UTF-8'|escape:'javascript'}",
    "logo": {ldelim}
      "@type": "ImageObject",
      "url": "{$shop.logo}"
    {rdelim}
  {rdelim}
  {if isset($stblog_post.image) && $stblog_post.image},
  "image": "{if is_array($stblog_post.image)}{$stblog_post.image.large.url}{else}{$stblog_post.image}{/if}"
  {/if}
{rdelim}
</script>
{/if}

<!--st begin -->
{block name='head_viewport'}
{if isset($sttheme.responsive) && $sttheme.responsive && (!$sttheme.enabled_version_swithing || $sttheme.version_switching==0)}
    <meta name="viewport" content="width=device-width, maximum-scale=1, initial-scale=1.0" />
{/if}
{/block}

<!-- Google Tag Manager -->
{literal}
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-NZJPZDN');</script>
{/literal}
<!-- End Google Tag Manager -->

{block name='head_icons'}

  <link rel="icon" type="image/vnd.microsoft.icon" href="{$shop.favicon}?{$shop.favicon_update_time}">
  <link rel="shortcut icon" type="image/x-icon" href="{$shop.favicon}?{$shop.favicon_update_time}">
  <!--st begin -->
  {if isset($sttheme.icon_iphone_180) && $sttheme.icon_iphone_180}
  <link rel="apple-touch-icon" sizes="180x180" href="{$sttheme.icon_iphone_180}?{$sttheme.favicon_update_time}" />
  {/if}
  {if isset($sttheme.icon_iphone_16) && $sttheme.icon_iphone_16}
  <link rel="icon" type="image/png" sizes="16x16" href="{$sttheme.icon_iphone_16}?{$sttheme.favicon_update_time}" />
  {/if}
  {if isset($sttheme.icon_iphone_32) && $sttheme.icon_iphone_32}
  <link rel="icon" type="image/png" sizes="32x32" href="{$sttheme.icon_iphone_32}?{$sttheme.favicon_update_time}" />
  {/if}
  {if isset($sttheme.site_webmanifest) && $sttheme.site_webmanifest}
  <link rel="manifest" href="{$sttheme.site_webmanifest}?{$sttheme.favicon_update_time}">
  {/if}
  {if isset($sttheme.icon_iphone_svg) && $sttheme.icon_iphone_svg}
  <link rel="mask-icon" href="{$sttheme.icon_iphone_svg}?{$sttheme.favicon_update_time}" color="{if $sttheme.favicon_svg_color}{$sttheme.favicon_svg_color}{else}#e54d26{/if}">
  {/if}
  {if isset($sttheme.browserconfig) && $sttheme.browserconfig}
  <meta name="msapplication-config" content="{$sttheme.browserconfig}?{$sttheme.favicon_update_time}">
  {/if}
  {if isset($sttheme.browser_theme_color) && $sttheme.browser_theme_color}
  <meta name="theme-color" content="{$sttheme.browser_theme_color}">
  {/if}
{/block}
<!--st end -->
{block name='stylesheets'}
  {include file="_partials/stylesheets.tpl" stylesheets=$stylesheets}
{/block}

{* Fix #12: Responsive design fix - prevent mobile format on desktop *}
<link rel="stylesheet" href="{$urls.theme_assets}css/custom-responsive-fix.css?v={$smarty.now}">

{block name='javascript_head'}
  {include file="_partials/javascript.tpl" javascript=$javascript.head vars=$js_custom_vars}
{/block}
<!--st end -->
{* CRITICAL FIX: Remove duplicate canonical tags IMMEDIATELY - Must run before any other scripts *}
{* This JavaScript runs immediately and removes any canonical tags that appear after ours *}
<script>
(function() {
  'use strict';
  
  // Function to remove duplicate canonical tags (keep only the FIRST one)
  function removeDuplicateCanonical() {
    var head = document.head || document.getElementsByTagName('head')[0];
    if (!head) return;
    
    var canonicalLinks = head.querySelectorAll('link[rel="canonical"]');
    
    // If we have more than 1 canonical tag, remove all except the first one
    if (canonicalLinks.length > 1) {
      // Keep the first canonical (ours), remove all others
      for (var i = canonicalLinks.length - 1; i >= 1; i--) {
        if (canonicalLinks[i] && canonicalLinks[i].parentNode) {
          canonicalLinks[i].parentNode.removeChild(canonicalLinks[i]);
        }
      }
    }
    
    // Remove duplicate pagination link tags
    var nextLinks = head.querySelectorAll('link[rel="next"]');
    if (nextLinks.length > 1) {
      for (var i = nextLinks.length - 1; i >= 1; i--) {
        if (nextLinks[i] && nextLinks[i].parentNode) {
          nextLinks[i].parentNode.removeChild(nextLinks[i]);
        }
      }
    }
    
    var prevLinks = head.querySelectorAll('link[rel="prev"]');
    if (prevLinks.length > 1) {
      for (var i = prevLinks.length - 1; i >= 1; i--) {
        if (prevLinks[i] && prevLinks[i].parentNode) {
          prevLinks[i].parentNode.removeChild(prevLinks[i]);
        }
      }
    }
  }
  
  // Run immediately - don't wait for anything
  if (document.readyState === 'loading') {
    // Document is still loading - run as soon as head is available
    var checkHead = setInterval(function() {
      if (document.head) {
        clearInterval(checkHead);
        removeDuplicateCanonical();
      }
    }, 10);
  } else {
    // Document is already loaded or interactive
    removeDuplicateCanonical();
  }
  
  // Watch for new canonical tags being added dynamically
  if (document.head) {
    var observer = new MutationObserver(function(mutations) {
      var needsCleanup = false;
      mutations.forEach(function(mutation) {
        mutation.addedNodes.forEach(function(node) {
          if (node.nodeType === 1 && node.tagName === 'LINK') {
            var rel = node.getAttribute('rel');
            if (rel === 'canonical' || rel === 'next' || rel === 'prev') {
              needsCleanup = true;
            }
          }
        });
      });
      if (needsCleanup) {
        removeDuplicateCanonical();
      }
    });
    
    observer.observe(document.head, {
      childList: true,
      subtree: true
    });
  }
  
  // Run multiple times to catch any late additions
  setTimeout(removeDuplicateCanonical, 50);
  setTimeout(removeDuplicateCanonical, 100);
  setTimeout(removeDuplicateCanonical, 200);
  setTimeout(removeDuplicateCanonical, 500);
  setTimeout(removeDuplicateCanonical, 1000);
  
  // Also run when DOM is ready
  if (document.addEventListener) {
    document.addEventListener('DOMContentLoaded', removeDuplicateCanonical);
  }
})();
</script>
{* CRITICAL: Prevent duplicate canonical from hooks - Aggressive filtering *}
{block name='hook_header'}
  {capture name='hook_header_content'}{$HOOK_HEADER nofilter}{/capture}
  {* Remove ALL canonical tags from hook output - multiple replacement methods *}
  {assign var='hook_content' value=$smarty.capture.hook_header_content}
  {* Remove complete canonical link tags with various formats *}
  {assign var='hook_content' value=$hook_content|replace:'<link rel="canonical"':'<!-- CANONICAL REMOVED BY FIX --><link rel="canonical-disabled"'}
  {assign var='hook_content' value=$hook_content|replace:'<link rel = "canonical"':'<!-- CANONICAL REMOVED BY FIX --><link rel="canonical-disabled"'}
  {assign var='hook_content' value=$hook_content|replace:'<link  rel="canonical"':'<!-- CANONICAL REMOVED BY FIX --><link rel="canonical-disabled"'}
  {assign var='hook_content' value=$hook_content|replace:"<link rel='canonical'":"<!-- CANONICAL REMOVED BY FIX --><link rel='canonical-disabled'"}
  {assign var='hook_content' value=$hook_content|replace:"<link rel = 'canonical'":"<!-- CANONICAL REMOVED BY FIX --><link rel='canonical-disabled'"}
  {* Remove rel="canonical" attribute anywhere in link tag *}
  {assign var='hook_content' value=$hook_content|replace:' rel="canonical"':' rel="canonical-disabled"'}
  {assign var='hook_content' value=$hook_content|replace:" rel='canonical'":" rel='canonical-disabled'"}
  {assign var='hook_content' value=$hook_content|replace:' rel = "canonical"':' rel="canonical-disabled"'}
  {assign var='hook_content' value=$hook_content|replace:" rel = 'canonical'":" rel='canonical-disabled'"}
  
  {* Fix #7: COMPLETELY REMOVE duplicate rel="next" and rel="prev" from hook output - Use regex to remove entire tags *}
  {* Remove complete <link rel="next"> tags with all variations *}
  {assign var='hook_content' value=$hook_content|regex_replace:'/<link[^>]*rel\s*=\s*["\']next["\'][^>]*>/i':'<!-- REL="NEXT" REMOVED BY FIX -->'}
  {assign var='hook_content' value=$hook_content|regex_replace:'/<link[^>]*rel\s*=\s*["\']next["\'][^>]*\/>/i':'<!-- REL="NEXT" REMOVED BY FIX -->'}
  {* Also remove any remaining rel="next" attributes *}
  {assign var='hook_content' value=$hook_content|replace:' rel="next"':'<!-- REL="NEXT" REMOVED -->'}
  {assign var='hook_content' value=$hook_content|replace:" rel='next'":"<!-- REL='NEXT' REMOVED -->"}
  {assign var='hook_content' value=$hook_content|replace:' rel = "next"':'<!-- REL="NEXT" REMOVED -->'}
  {assign var='hook_content' value=$hook_content|replace:" rel = 'next'":"<!-- REL='NEXT' REMOVED -->"}
  {* Remove complete <link rel="prev"> tags with all variations *}
  {assign var='hook_content' value=$hook_content|regex_replace:'/<link[^>]*rel\s*=\s*["\']prev["\'][^>]*>/i':'<!-- REL="PREV" REMOVED BY FIX -->'}
  {assign var='hook_content' value=$hook_content|regex_replace:'/<link[^>]*rel\s*=\s*["\']prev["\'][^>]*\/>/i':'<!-- REL="PREV" REMOVED BY FIX -->'}
  {* Also remove any remaining rel="prev" attributes *}
  {assign var='hook_content' value=$hook_content|replace:' rel="prev"':'<!-- REL="PREV" REMOVED -->'}
  {assign var='hook_content' value=$hook_content|replace:" rel='prev'":"<!-- REL='PREV' REMOVED -->"}
  {assign var='hook_content' value=$hook_content|replace:' rel = "prev"':'<!-- REL="PREV" REMOVED -->'}
  {assign var='hook_content' value=$hook_content|replace:" rel = 'prev'":"<!-- REL='PREV' REMOVED -->"}
  
  {$hook_content nofilter}
{/block}

{* CRITICAL: Remove canonical from theme head_code if present *}
{if isset($sttheme.head_code) && $sttheme.head_code}
  {assign var='head_code' value=$sttheme.head_code}
  {assign var='head_code' value=$head_code|replace:'<link rel="canonical"':'<!-- CANONICAL REMOVED BY FIX --><link rel="canonical-disabled"'}
  {assign var='head_code' value=$head_code|replace:"<link rel='canonical'":"<!-- CANONICAL REMOVED BY FIX --><link rel='canonical-disabled'"}
  {assign var='head_code' value=$head_code|replace:' rel="canonical"':' rel="canonical-disabled"'}
  {assign var='head_code' value=$head_code|replace:" rel='canonical'":" rel='canonical-disabled'"}
  
  {* Fix #7: COMPLETELY REMOVE duplicate rel="next" and rel="prev" from head_code - Use regex to remove entire tags *}
  {* Remove complete <link rel="next"> tags with all variations *}
  {assign var='head_code' value=$head_code|regex_replace:'/<link[^>]*rel\s*=\s*["\']next["\'][^>]*>/i':'<!-- REL="NEXT" REMOVED BY FIX -->'}
  {assign var='head_code' value=$head_code|regex_replace:'/<link[^>]*rel\s*=\s*["\']next["\'][^>]*\/>/i':'<!-- REL="NEXT" REMOVED BY FIX -->'}
  {* Also remove any remaining rel="next" attributes *}
  {assign var='head_code' value=$head_code|replace:' rel="next"':'<!-- REL="NEXT" REMOVED -->'}
  {assign var='head_code' value=$head_code|replace:" rel='next'":"<!-- REL='NEXT' REMOVED -->"}
  {assign var='head_code' value=$head_code|replace:' rel = "next"':'<!-- REL="NEXT" REMOVED -->'}
  {assign var='head_code' value=$head_code|replace:" rel = 'next'":"<!-- REL='NEXT' REMOVED -->"}
  {* Remove complete <link rel="prev"> tags with all variations *}
  {assign var='head_code' value=$head_code|regex_replace:'/<link[^>]*rel\s*=\s*["\']prev["\'][^>]*>/i':'<!-- REL="PREV" REMOVED BY FIX -->'}
  {assign var='head_code' value=$head_code|regex_replace:'/<link[^>]*rel\s*=\s*["\']prev["\'][^>]*\/>/i':'<!-- REL="PREV" REMOVED BY FIX -->'}
  {* Also remove any remaining rel="prev" attributes *}
  {assign var='head_code' value=$head_code|replace:' rel="prev"':'<!-- REL="PREV" REMOVED -->'}
  {assign var='head_code' value=$head_code|replace:" rel='prev'":"<!-- REL='PREV' REMOVED -->"}
  {assign var='head_code' value=$head_code|replace:' rel = "prev"':'<!-- REL="PREV" REMOVED -->'}
  {assign var='head_code' value=$head_code|replace:" rel = 'prev'":"<!-- REL='PREV' REMOVED -->"}
  
  {$head_code nofilter}
{/if}

{* CRITICAL: Final check - if $page.canonical still exists, it means PrestaShop set it after our blocks *}
{* Output a comment to help debug if canonical still appears *}
{* This should never execute if our fix is working, but helps identify the source *}
{if isset($page.canonical) && $page.canonical != ''}
  {* WARNING: $page.canonical is still set! This should not happen. *}
  {* Unset it one more time to prevent any output *}
  {assign var='page.canonical' value='' scope='global'}
{/if}

{block name='hook_extra'}{/block}


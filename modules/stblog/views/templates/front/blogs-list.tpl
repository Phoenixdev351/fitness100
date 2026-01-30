{*
* 2007-2012 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 17677 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
{extends file='page.tpl'}

{block name='body_class' append} is_blog {/block}

{block name='page_content_container'}
<section id="content" class="page-blog-category">
    
    {block name='blog_list_header'}{/block}

    {if isset($blogs) && $blogs}         
        <div id="viewmode" class="">
             {include file="module:stblog/views/templates/slider/list-item.tpl" display_sd=$stblog.display_sd}
        </div>
        {include file='_partials/pagination.tpl' pagination=$pagination is_blog_fengye=true}
    {/if}
    
    {* Fix #10: Blog structured data on blog home page *}
    <script type="application/ld+json">
    {ldelim}
      "@context": "https://schema.org",
      "@type": "Blog",
      "name": "{$shop.name|escape:'html':'UTF-8'|escape:'javascript'} Blog",
      "url": "{$urls.current_url}",
      "description": "{if isset($page.meta.description) && $page.meta.description}{$page.meta.description|escape:'html':'UTF-8'|escape:'javascript'}{else}Blog de {$shop.name|escape:'html':'UTF-8'|escape:'javascript'}{/if}"
      {if isset($blogs) && $blogs && count($blogs) > 0},
      "itemListElement": [
        {foreach from=$blogs item=blog name=blogs}
        {ldelim}
          "@type": "ListItem",
          "position": {$smarty.foreach.blogs.iteration},
          "url": "{if isset($blog.url)}{$blog.url}{else}{if isset($blog.link)}{$blog.link}{else}{$urls.base_url}blog/{if isset($blog.id_st_blog)}{$blog.id_st_blog}{else}{$blog.id}{/if}{/if}{/if}"
        {rdelim}{if !$smarty.foreach.blogs.last},{/if}
        {/foreach}
      ]
      {/if}
    {rdelim}
    </script>

</section>
{/block}

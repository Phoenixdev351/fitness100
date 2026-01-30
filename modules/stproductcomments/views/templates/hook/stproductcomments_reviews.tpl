 {*
* 2007-2016 PrestaShop
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
*  @copyright  2007-2016 PrestaShop SA
*  @version  Release: $Revision$
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<!-- MODULE st stproductcomments -->
{if $commentNbr || $can_comment}
<div class="mb-2">
{if $commentNbr}
<a href="javascript:;" class="flex_box flex_left view_all_reviews mr-2" title="{l s='View all reviews'  d='Shop.Theme.Transformer'}" rel="nofollow">
{include file='module:stproductcomments/views/templates/hook/rating_box.tpl' averageTotal=$average.grade g_rich_snippets=false}
<span class="comment_nbr ml-1">{$commentNbr} {if $commentNbr>1}{l s='Reviews'  d='Shop.Theme.Transformer'}{else}{l s='Review'  d='Shop.Theme.Transformer'}{/if}</span>
</a>
{/if}
{if $can_comment}{include file='module:stproductcomments/views/templates/hook/pcomments_write.tpl' classname="ml-2"}{/if}
</div>
{/if}
<!-- /MODULE st stproductcomments -->
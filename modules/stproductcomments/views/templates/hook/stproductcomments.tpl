{*
* 2007-2014 PrestaShop
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
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<!-- MODULE st stproductcomments -->
<section class="pccomment_block">
<script>var st_product_comment_tag_prefilled='{$st_product_comment_tag_prefilled}';</script>
{if $nbComments}
    {include file='module:stproductcomments/views/templates/hook/pcomments_header.tpl'}
    {include file='module:stproductcomments/views/templates/hook/pcomments_filters.tpl'}
	{include file='module:stproductcomments/views/templates/hook/pcomments_list.tpl'}
{else}
    {if $can_comment}<div class="mb-2">{include file='module:stproductcomments/views/templates/hook/pcomments_write.tpl' classname="go" is_first_comment=1}</div>{/if}
  <div class="" role="alert" data-alert="warning">
    {l s='No comments' d='Shop.Theme.Transformer'}
  </div>
{/if}

{if isset($allow_guest) && $allow_guest && (!$logged || $logged && !$can_comment)}
<div class="st_product_comment_wrap">
	<h3>{l s='Leave a review' d='Shop.Theme.Transformer'}</h3>
	<form name="st_product_comment_form_guest" method="post" action="{$link->getModuleLink('stproductcomments','default')}">
		{if $criterions2|@count > 0}
		<ul class="criterions_list li_fl clearfix">
		{foreach from=$criterions2 item='criterion'}
		 	<li class="flex_container flex_left mr-5">
			    <span class="criterion_name mr-2">{$criterion.name}:</span>
			    <div class="star_content clearfix">
					<input class="star" type="radio" name="criterion[{$criterion.id_st_product_comment_criterion|round}]" value="1" />
					<input class="star" type="radio" name="criterion[{$criterion.id_st_product_comment_criterion|round}]" value="2" />
					<input class="star" type="radio" name="criterion[{$criterion.id_st_product_comment_criterion|round}]" value="3" />
					<input class="star" type="radio" name="criterion[{$criterion.id_st_product_comment_criterion|round}]" value="4" />
					<input class="star" type="radio" name="criterion[{$criterion.id_st_product_comment_criterion|round}]" value="5" checked="checked" />
				</div>
			</li>
		{/foreach}
		</ul>
		{/if}
	    <div class="form-group row mb-3">
		    <label class="col-md-2 form-control-label required">
		        {l s='Describe it:' d='Shop.Theme.Transformer'}
		    </label>
		    <div class="col-md-8 tag-wrap">
		        <input type="text" name="tags" placeholder="{l s='Use a comma to seperate words.' d='Shop.Theme.Transformer'}" class="tm-input form-control"/>
		        <div>{l s='Describe this product using simple and short words.' d='Shop.Theme.Transformer'}</div>
		    </div>
	    </div>
	    <div class="form-group row">
		    <label class="col-md-2 form-control-label">
		        {l s='Custom name(required):' d='Shop.Theme.Transformer'}
		    </label>
		    <div class="col-md-8">
		    	<input type="text" name="customer_name" class="form-control" value="{$customer_name}" required />
		    </div>
	    </div>
	    <div class="form-group row">
		    <label class="col-md-2 form-control-label">
		        {l s='Review(required):' d='Shop.Theme.Transformer'}
		    </label>
		    <div class="col-md-8">
		    	<textarea id="comment_content" name="content" rows="6" class="form-control st_comment_box" autocomplete="off"></textarea>
		    </div>
	    </div>
	    <div class="form-group row">
		    <div class="col-md-2">{if isset($upload_image) && $upload_image}{l s='Upload images:' d='Shop.Theme.Transformer'}{/if}</div>
		    <div class="col-md-8">
	        	{if isset($upload_image) && $upload_image}
		        <div class="st-dropzone" id="st_product_comment_uploader">
			        <div class="dz-message needsclick">
			            {l s='Drop images here or click to upload.' d='Shop.Theme.Transformer'}
			        </div>
		        </div>
		        <input name="image" type="hidden" value="" />
		        {/if}
		        <input name="id_product" type="hidden" value="{$id_product}" />
		        <input name="id_order_detail" type="hidden" value="0" />
		        <input name="id_order" type="hidden" value="0" />
		        <input name="id_parent" type="hidden" value="0" />
		        <input name="action" type="hidden" value="add_comment" />
		        {hook h='displayGDPRConsent' mod='psgdpr' id_module=$id_module}
		        <div>
		            <input type="submit" name="st_product_comment_submit" id="st_product_comment_submit" value="{l s='Post comment' d='Shop.Theme.Transformer'}" class="btn btn-default mar_r4" />
		        </div>
	      	</div>
	    </div>
	</form>
</div>
{/if}
</section>
<!-- /MODULE st stproductcomments -->
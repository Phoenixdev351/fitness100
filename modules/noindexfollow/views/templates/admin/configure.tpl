{*
* 2015-2017 NTS
*
* DISCLAIMER
*
* You are NOT allowed to modify the software. 
* It is also not legal to do any changes to the software and distribute it in your own name / brand. 
*
* @author NTS
* @copyright  2015-2017 NTS
* @license   http://addons.prestashop.com/en/content/12-terms-and-conditions-of-use
* International Registered Trademark & Property of NTS
*}
{if $PS_VERSION<'1.5'}
			<link href="{$module_path|escape:'htmlall':'UTF-8'}views/css/compatibility/font-awesome.min.css" rel="stylesheet" type="text/css" media="all" />
			<link href="{$module_path|escape:'htmlall':'UTF-8'}views/css/compatibility/bootstrap-select.min.css" rel="stylesheet" type="text/css" media="all" />
			<link href="{$module_path|escape:'htmlall':'UTF-8'}views/css/compatibility/bootstrap-responsive.min.css" rel="stylesheet" type="text/css" media="all" />
			<link href="{$module_path|escape:'htmlall':'UTF-8'}views/css/compatibility/bootstrap.min.css" rel="stylesheet" type="text/css" media="all" />
			<link href="{$module_path|escape:'htmlall':'UTF-8'}views/css/compatibility/bootstrap.extend.css" rel="stylesheet" type="text/css" media="all" />
            <link href="{$module_path|escape:'htmlall':'UTF-8'}views/css/prestashop-admin.css" rel="stylesheet" type="text/css" media="all" />
			<script type="text/javascript" src="{$module_path|escape:'htmlall':'UTF-8'}views/js/back_1.5.js"></script>
			<script type="text/javascript" src="{$module_path|escape:'htmlall':'UTF-8'}views/js/compatibility/bootstrap-select.min.js"></script>
			<script type="text/javascript" src="{$module_path|escape:'htmlall':'UTF-8'}views/js/compatibility/bootstrap.min.js"></script>
			
	{/if}
<script>
$(document).ready(function(e) {
	
		$("input[type=radio]").change(function(){
			
			if($(this).attr('data-type')=='noindex_product' || $(this).attr('data-type')=='nofollow_product'){
			  var data_url = 'action=SubmitNoindexfollow&ajax=true&id_product='+$(this).attr('data-id')+'&id_config=' + $(this).attr('data-type') + '_ids&value='+this.value;
			}else{
			  var data_url = 'action=SubmitNoindexfollow&ajax=true&id_config=' + this.name + '&value='+this.value;
				}
					$.ajax({
							type: 'POST',
							headers: { "cache-control": "no-cache" },
							url: $('#noindexfollowform').attr('action'),
							async: false,
							cache: false,
							dataType : "json",
							data: data_url,
							success: function(jsonData,textStatus,jqXHR)
							{
								if (jsonData.code=='0')
								{
									showErrorMessage(jsonData.msg);
									return;
								}
								showSuccessMessage(jsonData.msg);
							},
							error: function(jsonData, textStatus, errorThrown)
							{
								if (textStatus != 'error' || errorThrown != '')
									showErrorMessage(textStatus + ': ' + errorThrown);
							}
				    });
		});
		
    $('input[name=noindex_product_checks_index],input[name=noindex_product_checks_follow],input[name=noindex_cat_checks_index],input[name=noindex_cat_checks_follow],input[name=noindex_cms_checks_index],input[name=noindex_cms_checks_follow],input[name=noindex_man_checks_index],input[name=noindex_man_checks_follow],input[name=noindex_sup_checks_index],input[name=noindex_sup_checks_follow],input[name=noindex_cms_cats_checks_index],input[name=noindex_cms_cats_checks_follow],input[name=noindex_defaults_checks_index],input[name=noindex_defaults_checks_follow],input[name=noindex_modules_checks_index],input[name=noindex_modules_checks_follow]').click(function(e) {
		var noindex_follow_element = $(this).attr('name');
        if($(this).is(':checked')){
			$('.'+noindex_follow_element+'_on').click();
		}else{
			$('.'+noindex_follow_element+'_off').click();
			}
    });
	$('a.noindex_product_checks_index,a.noindex_product_checks_follow,a.noindex_cat_checks_index,a.noindex_cat_checks_follow,a.noindex_cms_checks_index,a.noindex_cms_checks_follow,a.noindex_man_checks_index,a.noindex_man_checks_follow,a.noindex_sup_checks_index,a.noindex_sup_checks_follow,a.noindex_cms_cats_checks_index,a.noindex_cms_cats_checks_follow,a.noindex_defaults_checks_index,a.noindex_defaults_checks_follow,a.noindex_modules_checks_index,a.noindex_modules_checks_follow').click(function(e) {
		var noindex_follow_element = $(this).attr('class');
			$('.'+noindex_follow_element+'_on').each(function(index, element) {
                if($(element).attr('data-checked')=='1'){
					 $(element).click();
					}
            });
			$('.'+noindex_follow_element+'_off').each(function(index, element) {
                if($(element).attr('data-checked')=='1'){
					 $(element).click();
					}
            });
    });
});
</script>
{if isset($confirmation) && $confirmation}
	<p class="conf confirmation alert alert-success" style="width: 100%;">{$confirmation|escape:'html':'UTF-8'}</p>
{/if}
<p class="info information alert alert-info" style="width: 100%;">{l s='Options will be auto saved in background when you make any changes.' mod='noindexfollow'}</p>
<!-- MODULE noindexfollow -->
		<div class="row"><div class="col-lg-12"><form action="{$postAction|escape:'html':'UTF-8'}" id="noindexfollowform" method="post" class="form-horizontal">
		<div class="panel"><div class="panel-heading">{l s='Set INDEXATION & FOLLOW Options for search engines' mod='noindexfollow'}</div>
		<div class="form-wrapper">
        <div class="form-group" style="width:100%;margin-bottom: 0px;border: 1px solid #cdcdcd;padding: 5px; background:#EFF9FC">
		<div class="col-lg-12"><strong>{l s='Avoid duplicate content on pages like Categories, New Products, Top Sellers, Price Drops, Manufacturers, Suppliers with paginated URLs like (?p=1,p=2..) or (?page=1,page=2..)' mod='noindexfollow'}</strong></div></div>
        <div class="form-group" style="margin:20px;border:none;">
		<label class="control-label col-lg-6" for="simple_product"><strong>Add Canonical URL</strong></label>
		<div class="col-lg-3"> 
           <span class="switch prestashop-switch fixed-width-lg">
                <input type="radio" name="cat_canonical_for_p" id="cat_canonical_for_p_on" value="1" {if Configuration::get('cat_canonical_for_p')==1}checked="checked"{/if}>
                <label for="cat_canonical_for_p_on">Yes</label>
                <input type="radio" name="cat_canonical_for_p" id="cat_canonical_for_p_off" value="0" {if Configuration::get('cat_canonical_for_p')==0}checked="checked" {/if}>
                <label for="cat_canonical_for_p_off">No</label>
                <a class="slide-button btn"></a>
            </span>
		</div></div>
        
        <div class="form-group" style="float:left; width:100%;margin-bottom: 0px;border: 1px solid #cdcdcd;padding: 5px; background:#EFF9FC">
		<div class="col-lg-6"><strong>{l s='CMS Category Pages' mod='noindexfollow'}</strong> <span class="badge">{count($cms_cats)|intval}</span></div>
		<div class="col-lg-3"><strong>&nbsp;&nbsp;{l s='INDEXATION' mod='noindexfollow'}</strong> &nbsp;<input type="checkbox" title="{l s='Yes/ No ALL' mod='noindexfollow'}" name="noindex_cms_cats_checks_index" /> &nbsp;&nbsp;<a href="javascript:void(0);" title="{l s='UNDO' mod='noindexfollow'}" class="noindex_cms_cats_checks_index"><i class="icon-undo"></i></a></div><div class="col-lg-3"><strong>&nbsp;&nbsp;{l s='FOLLOW' mod='noindexfollow'}</strong> &nbsp;<input type="checkbox" title="{l s='Yes/ No ALL' mod='noindexfollow'}" name="noindex_cms_cats_checks_follow" />&nbsp;&nbsp;<a href="javascript:void(0);" title="{l s='UNDO' mod='noindexfollow'}" class="noindex_cms_cats_checks_follow"><i class="icon-undo"></i></a></div></div>
		
        {foreach from=$cms_cats key=k item=file}
        {assign var=cms_cats_index value=Configuration::get('cms_cats_'|cat:$file.id_cms_category|cat:'_index')}
        {assign var=cms_cats_follow value=Configuration::get('cms_cats_'|cat:$file.id_cms_category|cat:'_follow')}
		<div class="form-group {if $file@iteration is even by 1}odd_label{/if}">
		<label class="control-label col-lg-6" for="simple_product"><strong>{$file.name|escape:'html':'UTF-8'}</strong></label>
		<div class="col-lg-3"> 
           <span class="switch prestashop-switch fixed-width-lg">
                <input type="radio" class="noindex_cms_cats_checks_index_on" name="cms_cats_{$file.id_cms_category|intval}_index" id="cms_cats_{$file.id_cms_category|intval}_index_on" value="1" {if $cms_cats_index==1}data-checked="1" checked="checked"{/if}>
                <label for="cms_cats_{$file.id_cms_category|intval}_index_on">Yes</label>
                <input type="radio" class="noindex_cms_cats_checks_index_off" name="cms_cats_{$file.id_cms_category|intval}_index" id="cms_cats_{$file.id_cms_category|intval}_index_off" value="0" {if $cms_cats_index==0}data-checked="1" checked="checked" {/if}>
                <label for="cms_cats_{$file.id_cms_category|intval}_index_off">No</label>
                <a class="slide-button btn"></a>
            </span>
		</div><div class="col-lg-3">
        <span class="switch prestashop-switch fixed-width-lg">
            <input type="radio" class="noindex_cms_cats_checks_follow_on" name="cms_cats_{$file.id_cms_category|intval}_follow" id="cms_cats_{$file.id_cms_category|intval}_follow_on" value="1" {if $cms_cats_follow==1}data-checked="1" checked="checked"{/if}>
            <label for="cms_cats_{$file.id_cms_category|intval}_follow_on">Yes</label>
            <input type="radio" class="noindex_cms_cats_checks_follow_off" name="cms_cats_{$file.id_cms_category|intval}_follow" id="cms_cats_{$file.id_cms_category|intval}_follow_off" value="0" {if $cms_cats_follow==0}data-checked="1" checked="checked" {/if}>
            <label for="cms_cats_{$file.id_cms_category|intval}_follow_off">No</label>
            <a class="slide-button btn"></a>
          </span>
		</div></div>
			{/foreach}
            
		<div class="form-group" style="float:left; width:100%;margin-bottom: 0px;border: 1px solid #cdcdcd;padding: 5px; background:#EFF9FC">
		<div class="col-lg-6"><strong>{l s='CMS Pages' mod='noindexfollow'}</strong> <span class="badge">{count($cms)|intval}</span></div>
		<div class="col-lg-3"><strong>&nbsp;&nbsp;{l s='INDEXATION' mod='noindexfollow'}</strong> &nbsp;<input title="{l s='Yes/ No ALL' mod='noindexfollow'}" type="checkbox" name="noindex_cms_checks_index" />&nbsp;&nbsp;<a href="javascript:void(0);" title="{l s='UNDO' mod='noindexfollow'}" class="noindex_cms_checks_index"><i class="icon-undo"></i></a></div><div class="col-lg-3"><strong>&nbsp;&nbsp;{l s='FOLLOW' mod='noindexfollow'}</strong> &nbsp;<input title="{l s='Yes/ No ALL' mod='noindexfollow'}" type="checkbox" name="noindex_cms_checks_follow" />&nbsp;&nbsp;<a href="javascript:void(0);" title="{l s='UNDO' mod='noindexfollow'}" class="noindex_cms_checks_follow"><i class="icon-undo"></i></a></div></div>
		
        {foreach from=$cms key=k item=file}
        {assign var=cms_index value=Configuration::get('cms_'|cat:$file.id_cms|cat:'_index')}
        {assign var=cms_follow value=Configuration::get('cms_'|cat:$file.id_cms|cat:'_follow')}
		<div class="form-group {if $file@iteration is even by 1}odd_label{/if}">
		<label class="control-label col-lg-6" for="simple_product"><strong>{$file.meta_title|escape:'html':'UTF-8'}</strong></label>
		<div class="col-lg-3"> 
           <span class="switch prestashop-switch fixed-width-lg">
                <input type="radio" class="noindex_cms_checks_index_on" name="cms_{$file.id_cms|intval}_index" id="cms_{$file.id_cms|intval}_index_on" value="1" {if $cms_index==1}data-checked="1" checked="checked"{/if}>
                <label for="cms_{$file.id_cms|intval}_index_on">Yes</label>
                <input type="radio" class="noindex_cms_checks_index_off" name="cms_{$file.id_cms|intval}_index" id="cms_{$file.id_cms|intval}_index_off" value="0" {if $cms_index==0}data-checked="1" checked="checked" {/if}>
                <label for="cms_{$file.id_cms|intval}_index_off">No</label>
                <a class="slide-button btn"></a>
            </span>
		</div><div class="col-lg-3">
        <span class="switch prestashop-switch fixed-width-lg">
            <input type="radio" class="noindex_cms_checks_follow_on" name="cms_{$file.id_cms|intval}_follow" id="cms_{$file.id_cms|intval}_follow_on" value="1" {if $cms_follow==1}data-checked="1" checked="checked"{/if}>
            <label for="cms_{$file.id_cms|intval}_follow_on">Yes</label>
            <input type="radio" class="noindex_cms_checks_follow_off" name="cms_{$file.id_cms|intval}_follow" id="cms_{$file.id_cms|intval}_follow_off" value="0" {if $cms_follow==0}data-checked="1" checked="checked" {/if}>
            <label for="cms_{$file.id_cms|intval}_follow_off">No</label>
            <a class="slide-button btn"></a>
          </span>
		</div></div>
			{/foreach}	
            
            <div class="form-group" style="float:left; width:100%;margin-bottom: 0px;border: 1px solid #cdcdcd;padding: 5px; background:#EFF9FC">
		<div class="col-lg-6"><strong>{l s='Manufacturers' mod='noindexfollow'}</strong> <span class="badge">{count($manufacturers)|intval}</span></div>
		<div class="col-lg-3"><strong>&nbsp;&nbsp;{l s='INDEXATION' mod='noindexfollow'}</strong> &nbsp;<input title="{l s='Yes/ No ALL' mod='noindexfollow'}" type="checkbox" name="noindex_man_checks_index" />&nbsp;&nbsp;<a href="javascript:void(0);" title="{l s='UNDO' mod='noindexfollow'}" class="noindex_man_checks_index"><i class="icon-undo"></i></a></div><div class="col-lg-3"><strong>&nbsp;&nbsp;{l s='FOLLOW' mod='noindexfollow'}</strong> &nbsp;<input title="{l s='Yes/ No ALL' mod='noindexfollow'}" type="checkbox" name="noindex_man_checks_follow" />&nbsp;&nbsp;<a href="javascript:void(0);" title="{l s='UNDO' mod='noindexfollow'}" class="noindex_man_checks_follow"><i class="icon-undo"></i></a></div></div>
		
        {foreach from=$manufacturers key=k item=file}
        {assign var=man_index value=Configuration::get('man_'|cat:$file.id_manufacturer|cat:'_index')}
        {assign var=man_follow value=Configuration::get('man_'|cat:$file.id_manufacturer|cat:'_follow')}
		<div class="form-group {if $file@iteration is even by 1}odd_label{/if}">
		<label class="control-label col-lg-6" for="simple_product"><strong>{$file.name|escape:'html':'UTF-8'}</strong></label>
		<div class="col-lg-3"> 
           <span class="switch prestashop-switch fixed-width-lg">
                <input type="radio" class="noindex_man_checks_index_on" name="man_{$file.id_manufacturer|intval}_index" id="man_{$file.id_manufacturer|intval}_index_on" value="1" {if $man_index==1}data-checked="1" checked="checked"{/if}>
                <label for="man_{$file.id_manufacturer|intval}_index_on">Yes</label>
                <input type="radio" class="noindex_man_checks_index_off" name="man_{$file.id_manufacturer|intval}_index" id="man_{$file.id_manufacturer|intval}_index_off" value="0" {if $man_index==0}data-checked="1" checked="checked" {/if}>
                <label for="man_{$file.id_manufacturer|intval}_index_off">No</label>
                <a class="slide-button btn"></a>
            </span>
		</div><div class="col-lg-3">
        <span class="switch prestashop-switch fixed-width-lg">
            <input type="radio" class="noindex_man_checks_follow_on" name="man_{$file.id_manufacturer|intval}_follow" id="man_{$file.id_manufacturer|intval}_follow_on" value="1" {if $man_follow==1}data-checked="1" checked="checked"{/if}>
            <label for="man_{$file.id_manufacturer|intval}_follow_on">Yes</label>
            <input type="radio" class="noindex_man_checks_follow_off" name="man_{$file.id_manufacturer|intval}_follow" id="man_{$file.id_manufacturer|intval}_follow_off" value="0" {if $man_follow==0}data-checked="1" checked="checked" {/if}>
            <label for="man_{$file.id_manufacturer|intval}_follow_off">No</label>
            <a class="slide-button btn"></a>
          </span>
		</div></div>
			{/foreach}	
            
            
            <div class="form-group" style="float:left; width:100%;margin-bottom: 0px;border: 1px solid #cdcdcd;padding: 5px; background:#EFF9FC">
		<div class="col-lg-6"><strong>{l s='Suppliers' mod='noindexfollow'}</strong> <span class="badge">{count($suppliers)|intval}</span></div>
		<div class="col-lg-3"><strong>&nbsp;&nbsp;{l s='INDEXATION' mod='noindexfollow'}</strong> &nbsp;<input title="{l s='Yes/ No ALL' mod='noindexfollow'}" type="checkbox" name="noindex_sup_checks_index" />&nbsp;&nbsp;<a href="javascript:void(0);" title="{l s='UNDO' mod='noindexfollow'}" class="noindex_sup_checks_index"><i class="icon-undo"></i></a></div><div class="col-lg-3"><strong>&nbsp;&nbsp;{l s='FOLLOW' mod='noindexfollow'}</strong> &nbsp;<input title="{l s='Yes/ No ALL' mod='noindexfollow'}" type="checkbox" name="noindex_sup_checks_follow" />&nbsp;&nbsp;<a href="javascript:void(0);" title="{l s='UNDO' mod='noindexfollow'}" class="noindex_sup_checks_follow"><i class="icon-undo"></i></a></div></div>
		
        {foreach from=$suppliers key=k item=file}
        {assign var=sup_index value=Configuration::get('sup_'|cat:$file.id_supplier|cat:'_index')}
        {assign var=sup_follow value=Configuration::get('sup_'|cat:$file.id_supplier|cat:'_follow')}
		<div class="form-group {if $file@iteration is even by 1}odd_label{/if}">
		<label class="control-label col-lg-6" for="simple_product"><strong>{$file.name|escape:'html':'UTF-8'}</strong></label>
		<div class="col-lg-3"> 
           <span class="switch prestashop-switch fixed-width-lg">
                <input type="radio" class="noindex_sup_checks_index_on" name="sup_{$file.id_supplier|intval}_index" id="sup_{$file.id_supplier|intval}_index_on" value="1" {if $sup_index==1}data-checked="1" checked="checked"{/if}>
                <label for="sup_{$file.id_supplier|intval}_index_on">Yes</label>
                <input type="radio" class="noindex_sup_checks_index_off" name="sup_{$file.id_supplier|intval}_index" id="sup_{$file.id_supplier|intval}_index_off" value="0" {if $sup_index==0}data-checked="1" checked="checked" {/if}>
                <label for="sup_{$file.id_supplier|intval}_index_off">No</label>
                <a class="slide-button btn"></a>
            </span>
		</div><div class="col-lg-3">
        <span class="switch prestashop-switch fixed-width-lg">
            <input type="radio" class="noindex_sup_checks_follow_on" name="sup_{$file.id_supplier|intval}_follow" id="sup_{$file.id_supplier|intval}_follow_on" value="1" {if $sup_follow==1}data-checked="1" checked="checked"{/if}>
            <label for="sup_{$file.id_supplier|intval}_follow_on">Yes</label>
            <input type="radio" class="noindex_sup_checks_follow_off" name="sup_{$file.id_supplier|intval}_follow" id="sup_{$file.id_supplier|intval}_follow_off" value="0" {if $sup_follow==0}data-checked="1" checked="checked" {/if}>
            <label for="sup_{$file.id_supplier|intval}_follow_off">No</label>
            <a class="slide-button btn"></a>
          </span>
		</div></div>
			{/foreach}	
            
         <div class="form-group" style="float:left; width:100%;margin-top: 10px;border: 1px solid #cdcdcd;padding: 5px; background:#EFF9FC">
		<div class="col-lg-6"><strong>{l s='Category Pages' mod='noindexfollow'}</strong> <span class="badge">{count($categories)|intval}</span></div>
		<div class="col-lg-3"><strong>&nbsp;&nbsp;{l s='INDEXATION' mod='noindexfollow'}</strong> &nbsp;<input title="{l s='Yes/ No ALL' mod='noindexfollow'}" type="checkbox" name="noindex_cat_checks_index" />&nbsp;&nbsp;<a href="javascript:void(0);" title="{l s='UNDO' mod='noindexfollow'}" class="noindex_cat_checks_index"><i class="icon-undo"></i></a></div><div class="col-lg-3"><strong>&nbsp;&nbsp;{l s='FOLLOW' mod='noindexfollow'}</strong> &nbsp;<input title="{l s='Yes/ No ALL' mod='noindexfollow'}" type="checkbox" name="noindex_cat_checks_follow" />&nbsp;&nbsp;<a href="javascript:void(0);" title="{l s='UNDO' mod='noindexfollow'}" class="noindex_cat_checks_follow"><i class="icon-undo"></i></a></div></div>
		
		<div style="max-height:800px;overflow-y: auto;width: 100%;overflow-x: hidden;">
        {foreach from=$categories key=name item=cat}
        {assign var=common_index value=Configuration::get($cat.id_category|cat:'_index_cat')}
        {assign var=common_follow value=Configuration::get($cat.id_category|cat:'_follow_cat')}
		<div class="form-group {if $cat@iteration is even by 1}odd_label{/if}">
		<label class="control-label col-lg-6" for="simple_product"><strong>{$cat.name|escape:'html':'UTF-8'} </strong>({$cat.id_category|escape:'html':'UTF-8'})</label>
		<div class="col-lg-3">
        <span class="switch prestashop-switch fixed-width-lg">
      <input type="radio" class="noindex_cat_checks_index_on" name="{$cat.id_category|escape:'html':'UTF-8'}_index_cat" id="{$cat.id_category|escape:'html':'UTF-8'}_index_cat_on" value="1" {if $common_index==1}data-checked="1" checked="checked"{/if}>
      <label for="{$cat.id_category|escape:'html':'UTF-8'}_index_cat_on">Yes</label>
      <input type="radio" class="noindex_cat_checks_index_off" name="{$cat.id_category|escape:'html':'UTF-8'}_index_cat" id="{$cat.id_category|escape:'html':'UTF-8'}_index_cat_off" value="0" {if $common_index==0}data-checked="1" checked="checked" {/if}>
      <label for="{$cat.id_category|escape:'html':'UTF-8'}_index_cat_off">No</label>
      <a class="slide-button btn"></a>
       </span>
		</div><div class="col-lg-3">
        <span class="switch prestashop-switch fixed-width-lg">
      <input type="radio" class="noindex_cat_checks_follow_on" name="{$cat.id_category|escape:'html':'UTF-8'}_follow_cat" id="{$cat.id_category|escape:'html':'UTF-8'}_follow_cat_on" value="1" {if $common_follow==1}data-checked="1" checked="checked"{/if}>
      <label for="{$cat.id_category|escape:'html':'UTF-8'}_follow_cat_on">Yes</label>
      <input type="radio" class="noindex_cat_checks_follow_off" name="{$cat.id_category|escape:'html':'UTF-8'}_follow_cat" id="{$cat.id_category|escape:'html':'UTF-8'}_follow_cat_off" value="0" {if $common_follow==0}data-checked="1" checked="checked" {/if}>
      <label for="{$cat.id_category|escape:'html':'UTF-8'}_follow_cat_off">No</label>
      <a class="slide-button btn"></a>
       </span>
		</div></div>
        
        
        {assign var=common_index_pro value=Configuration::get($cat.id_category|cat:'_index_pro')}
        {assign var=common_follow_pro value=Configuration::get($cat.id_category|cat:'_follow_pro')}
		<!--<div class="form-group {if $file@iteration is even by 1}odd_label{/if}">
		<label class="control-label col-lg-6" for="simple_product">Products with default category <strong>{$cat.name|escape:'html':'UTF-8'} </strong>({$cat.id_category|escape:'html':'UTF-8'})</label>
		<div class="col-lg-3">
        <span class="switch prestashop-switch fixed-width-lg">
      <input type="radio" name="{$cat.id_category|escape:'html':'UTF-8'}_index_pro" id="{$cat.id_category|escape:'html':'UTF-8'}_index_pro_on" value="1" {if $common_index_pro==1}checked="checked"{/if}>
      <label for="{$cat.id_category|escape:'html':'UTF-8'}_index_pro_on">Yes</label>
      <input type="radio" name="{$cat.id_category|escape:'html':'UTF-8'}_index_pro" id="{$cat.id_category|escape:'html':'UTF-8'}_index_pro_off" value="0" {if $common_index_pro==0}checked="checked" {/if}>
      <label for="{$cat.id_category|escape:'html':'UTF-8'}_index_pro_off">No</label>
      <a class="slide-button btn"></a>
       </span>
		</div><div class="col-lg-3">
        <span class="switch prestashop-switch fixed-width-lg">
      <input type="radio" name="{$cat.id_category|escape:'html':'UTF-8'}_follow_pro" id="{$cat.id_category|escape:'html':'UTF-8'}_follow_pro_on" value="1" {if $common_follow_pro==1}checked="checked"{/if}>
      <label for="{$cat.id_category|escape:'html':'UTF-8'}_follow_pro_on">Yes</label>
      <input type="radio" name="{$cat.id_category|escape:'html':'UTF-8'}_follow_pro" id="{$cat.id_category|escape:'html':'UTF-8'}_follow_pro_off" value="0" {if $common_follow_pro==0}checked="checked" {/if}>
      <label for="{$cat.id_category|escape:'html':'UTF-8'}_follow_pro_off">No</label>
      <a class="slide-button btn"></a>
       </span>
		</div></div>-->
		{/foreach}
		</div>
        
        
        <div class="form-group" style="float:left; width:100%;margin-top: 10px;border: 1px solid #cdcdcd;padding: 5px; background:#EFF9FC">
		<div class="col-lg-6"><strong>{l s='Product Pages' mod='noindexfollow'}</strong> <span class="badge">{count($products)|intval}</span></div>
		<div class="col-lg-3"><strong>&nbsp;&nbsp;{l s='INDEXATION' mod='noindexfollow'}</strong> &nbsp;<input title="{l s='Yes/ No ALL' mod='noindexfollow'}" type="checkbox" name="noindex_product_checks_index" />&nbsp;&nbsp;<a href="javascript:void(0);" title="{l s='UNDO' mod='noindexfollow'}" class="noindex_product_checks_index"><i class="icon-undo"></i></a></div><div class="col-lg-3"><strong>&nbsp;&nbsp;{l s='FOLLOW' mod='noindexfollow'}</strong> &nbsp;<input title="{l s='Yes/ No ALL' mod='noindexfollow'}" type="checkbox" name="noindex_product_checks_follow" />&nbsp;&nbsp;<a href="javascript:void(0);" title="{l s='UNDO' mod='noindexfollow'}" class="noindex_product_checks_follow"><i class="icon-undo"></i></a></div></div>
		
		<div style="max-height:800px;overflow-y: auto;width: 100%;overflow-x: hidden;">
        {foreach from=$products key=name item=pro}
		<div class="form-group {if $pro@iteration is even by 1}odd_label{/if}" id="products_noindex_follow">
		<label class="control-label col-lg-6" for="simple_product"><strong>{$pro.name|escape:'html':'UTF-8'} </strong>({$pro.id_product|escape:'html':'UTF-8'})</label>
		<div class="col-lg-3">
        <span class="switch prestashop-switch fixed-width-lg">
      <input type="radio" class="noindex_product_checks_index_on" data-type="noindex_product" name="noindex_product_ids[{$pro.id_product|escape:'html':'UTF-8'}]" id="{$pro.id_product|escape:'html':'UTF-8'}_index_product_on" data-id="{$pro.id_product|intval}" value="1" {if !in_array($pro.id_product,$products_noindex)}data-checked="1" checked="checked"{/if} />
      <label for="{$pro.id_product|escape:'html':'UTF-8'}_index_product_on">Yes</label>
      <input type="radio" class="noindex_product_checks_index_off" data-type="noindex_product" name="noindex_product_ids[{$pro.id_product|escape:'html':'UTF-8'}]" id="{$pro.id_product|escape:'html':'UTF-8'}_index_product_off" data-id="{$pro.id_product|intval}" value="0" {if in_array($pro.id_product,$products_noindex)}data-checked="1" checked="checked"{/if} />
      <label for="{$pro.id_product|escape:'html':'UTF-8'}_index_product_off">No</label>
      <a class="slide-button btn"></a>
       </span>
		</div><div class="col-lg-3">
        <span class="switch prestashop-switch fixed-width-lg">
      <input type="radio" class="noindex_product_checks_follow_on" data-type="nofollow_product" name="nofollow_product_ids[{$pro.id_product|escape:'html':'UTF-8'}]" id="{$pro.id_product|escape:'html':'UTF-8'}_follow_product_on" data-id="{$pro.id_product|intval}" value="1" {if !in_array($pro.id_product,$products_nofollow)}data-checked="1" checked="checked"{/if}>
      <label for="{$pro.id_product|escape:'html':'UTF-8'}_follow_product_on">Yes</label>
      <input type="radio" class="noindex_product_checks_follow_off" data-type="nofollow_product" name="nofollow_product_ids[{$pro.id_product|escape:'html':'UTF-8'}]" id="{$pro.id_product|escape:'html':'UTF-8'}_follow_product_off" data-id="{$pro.id_product|intval}" value="0" {if in_array($pro.id_product,$products_nofollow)}data-checked="1" checked="checked" {/if}>
      <label for="{$pro.id_product|escape:'html':'UTF-8'}_follow_product_off">No</label>
      <a class="slide-button btn"></a>
       </span>
		</div></div>
		{/foreach}
		</div>
        
        
		<div class="form-group" style="float:left; width:100%;margin-top: 10px;border: 1px solid #cdcdcd;padding: 5px; background:#EFF9FC">
		<div class="col-lg-6"><strong>{l s='Default Pages' mod='noindexfollow'}</strong> <span class="badge">{count($pages.common)+2|intval}</span></div>
		<div class="col-lg-3"><strong>&nbsp;&nbsp;{l s='INDEXATION' mod='noindexfollow'}</strong> &nbsp;<input title="{l s='Yes/ No ALL' mod='noindexfollow'}" type="checkbox" name="noindex_defaults_checks_index" />&nbsp;&nbsp;<a href="javascript:void(0);" title="{l s='UNDO' mod='noindexfollow'}" class="noindex_defaults_checks_index"><i class="icon-undo"></i></a></div><div class="col-lg-3"><strong>&nbsp;&nbsp;{l s='FOLLOW' mod='noindexfollow'}</strong> &nbsp;<input title="{l s='Yes/ No ALL' mod='noindexfollow'}" type="checkbox" name="noindex_defaults_checks_follow" />&nbsp;&nbsp;<a href="javascript:void(0);" title="{l s='UNDO' mod='noindexfollow'}" class="noindex_defaults_checks_follow"><i class="icon-undo"></i></a></div></div>
		
		
        {foreach from=$pages.common key=name item=file}
		{assign var=page value=Noindexfollow::truncPageNameBy($file)}
        {assign var=common_index value=Configuration::get($page|cat:'_index')}
        {assign var=common_follow value=Configuration::get($page|cat:'_follow')}
		<div class="form-group {if $file@iteration is even by 1}odd_label{/if}">
		<label class="control-label col-lg-6" for="simple_product"><strong>{if $PS_VERSION>'1.5'}{$name|escape:'html':'UTF-8'}{/if}</strong> {if $PS_VERSION>'1.5'}[{$file|escape:'html':'UTF-8'}]{else}{$file|escape:'html':'UTF-8'}{/if}</label>
		<div class="col-lg-3">
        <span class="switch prestashop-switch fixed-width-lg">
      <input type="radio" class="noindex_defaults_checks_index_on" name="{$page|escape:'html':'UTF-8'}_index" id="{$page|escape:'html':'UTF-8'}_index_on" value="1" {if $common_index==1}data-checked="1" checked="checked"{/if}>
      <label for="{$page|escape:'html':'UTF-8'}_index_on">Yes</label>
      <input type="radio" class="noindex_defaults_checks_index_off" name="{$page|escape:'html':'UTF-8'}_index" id="{$page|escape:'html':'UTF-8'}_index_off" value="0" {if $common_index==0}data-checked="1" checked="checked" {/if}>
      <label for="{$page|escape:'html':'UTF-8'}_index_off">No</label>
      <a class="slide-button btn"></a>
       </span>
		</div><div class="col-lg-3">
        <span class="switch prestashop-switch fixed-width-lg">
      <input type="radio" class="noindex_defaults_checks_follow_on" name="{$page|escape:'html':'UTF-8'}_follow" id="{$page|escape:'html':'UTF-8'}_follow_on" value="1" {if $common_follow==1}data-checked="1" checked="checked"{/if}>
      <label for="{$page|escape:'html':'UTF-8'}_follow_on">Yes</label>
      <input type="radio" class="noindex_defaults_checks_follow_off" name="{$page|escape:'html':'UTF-8'}_follow" id="{$page|escape:'html':'UTF-8'}_follow_off" value="0" {if $common_follow==0}data-checked="1" checked="checked" {/if}>
      <label for="{$page|escape:'html':'UTF-8'}_follow_off">No</label>
      <a class="slide-button btn"></a>
       </span>
		</div></div>
		{/foreach}	
        
			
		<div class="form-group" style="float:left; width:100%;margin-top: 10px;border: 1px solid #cdcdcd;padding: 5px; background:#EFF9FC">
		<div class="col-lg-6"><strong>{l s='Module Pages' mod='noindexfollow'}</strong>{if $PS_VERSION>'1.5'} <span class="badge">{count($pages['module'])|intval}</span>{/if}</div>
		<div class="col-lg-3"><strong>&nbsp;&nbsp;{l s='INDEXATION' mod='noindexfollow'}</strong> &nbsp;<input title="{l s='Yes/ No ALL' mod='noindexfollow'}" type="checkbox" name="noindex_modules_checks_index" />&nbsp;&nbsp;<a href="javascript:void(0);" title="{l s='UNDO' mod='noindexfollow'}" class="noindex_modules_checks_index"><i class="icon-undo"></i></a></div><div class="col-lg-3"><strong>&nbsp;&nbsp;{l s='FOLLOW' mod='noindexfollow'}</strong> &nbsp;<input title="{l s='Yes/ No ALL' mod='noindexfollow'}" type="checkbox" name="noindex_modules_checks_follow" />&nbsp;&nbsp;<a href="javascript:void(0);" title="{l s='UNDO' mod='noindexfollow'}" class="noindex_modules_checks_follow"><i class="icon-undo"></i></a></div></div>
		
		{if $PS_VERSION<'1.5'}
			<div class="form-group">
		<label class="control-label col-lg-6" for="simple_product"><strong>{l s='For other modules pages(like mywishlist etc.)' mod='noindexfollow'}</strong></label>
        <div class="col-lg-3">
            <span class="switch prestashop-switch fixed-width-lg">
                <input type="radio" class="noindex_modules_checks_index_on" name="modules_index" id="modules_index_on" value="1" {if Configuration::get('modules_index')}data-checked="1" checked="checked"{/if}>
                <label for="modules_index_on">Yes</label>
                <input type="radio" class="noindex_modules_checks_index_off" name="modules_index" id="modules_index_off" value="0" {if !Configuration::get('modules_index')}data-checked="1" checked="checked" {/if}>
                <label for="modules_index_off">No</label>
                <a class="slide-button btn"></a>
            </span>
		</div><div class="col-lg-3">
             <span class="switch prestashop-switch fixed-width-lg">
                <input type="radio" class="noindex_modules_checks_follow_on" name="modules_follow" id="modules_follow_on" value="1" {if Configuration::get('modules_follow')}data-checked="1" checked="checked"{/if}>
                <label for="modules_follow_on">Yes</label>
                <input type="radio" class="noindex_modules_checks_follow_off" name="modules_follow" id="modules_follow_off" value="0" {if !Configuration::get('modules_follow')}data-checked="1" checked="checked" {/if}>
                <label for="modules_follow_off">No</label>
                <a class="slide-button btn"></a>
            </span>
		</div></div>
		{else}
          {foreach from=$pages.module key=name item=file}
          {assign var=page value=Noindexfollow::truncPageNameBy($file)}
          {assign var=module_index value=Configuration::get($page|cat:'_index')}
        {assign var=module_follow value=Configuration::get($page|cat:'_follow')}
          <div class="form-group {if $file@iteration is even by 1}odd_label{/if}">
          <label class="control-label col-lg-6" for="simple_product"><strong>{$name|escape:'html':'UTF-8'}</strong> [{$file|escape:'html':'UTF-8'}]</label>
          <div class="col-lg-3">
        <span class="switch prestashop-switch fixed-width-lg">
      <input type="radio" class="noindex_modules_checks_index_on" name="{$page|escape:'html':'UTF-8'}_index" id="{$page|escape:'html':'UTF-8'}_index_on" value="1" {if $module_index==1}data-checked="1" checked="checked"{/if}>
      <label for="{$page|escape:'html':'UTF-8'}_index_on">Yes</label>
      <input type="radio" class="noindex_modules_checks_index_off" name="{$page|escape:'html':'UTF-8'}_index" id="{$page|escape:'html':'UTF-8'}_index_off" value="0" {if $module_index==0}data-checked="1" checked="checked" {/if}>
      <label for="{$page|escape:'html':'UTF-8'}_index_off">No</label>
      <a class="slide-button btn"></a>
       </span>
		</div><div class="col-lg-3">
        <span class="switch prestashop-switch fixed-width-lg">
      <input type="radio" class="noindex_modules_checks_follow_on" name="{$page|escape:'html':'UTF-8'}_follow" id="{$page|escape:'html':'UTF-8'}_follow_on" value="1" {if $module_follow==1}data-checked="1" checked="checked"{/if}>
      <label for="{$page|escape:'html':'UTF-8'}_follow_on">Yes</label>
      <input type="radio" class="noindex_modules_checks_follow_off" name="{$page|escape:'html':'UTF-8'}_follow" id="{$page|escape:'html':'UTF-8'}_follow_off" value="0" {if $module_follow==0}data-checked="1" checked="checked" {/if}>
      <label for="{$page|escape:'html':'UTF-8'}_follow_off">No</label>
      <a class="slide-button btn"></a>
       </span>
          </div></div>
          {/foreach}
        {/if}
		</div>
         <!-- <div class="panel-footer">
            <button type="submit" name="SubmitNoindexfollow" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save' mod='noindexfollow'}</button>
			</div>-->
		</div></form>
	</div>
</div>
<div class="row"><div class="col-lg-12"><form action="{$postAction|escape:'html':'UTF-8'}" method="post" class="form-horizontal">
		<div class="panel"><div class="panel-heading">{l s='ROBOTS.TXT EDIT' mod='noindexfollow'}</div>
		<div class="form-wrapper">
        <div class="form-group" style="border:none;">
		<label class="control-label col-lg-4" for="simple_product"><strong>{l s='robots.txt file:' mod='noindexfollow'}</strong></label>
		<div class="col-lg-6"><textarea name="robots_txt" rows="20" cols="20">{$robots_file|escape:'html':'UTF-8'}</textarea> 
		</div></div>
        </div>
          <div class="panel-footer">
            <button type="submit" name="SubmitNoindexfollowRobots" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Update' mod='noindexfollow'}</button>
			</div>
		</div></form>
	</div>
</div>
<!-- /MODULE noindexfollow -->


{*
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
*}
{extends file="helpers/form/form.tpl"}
{block name="description"}
    {$smarty.block.parent} 
	{if $input.name=='ETS_SPEED_OPTIMIZE_SCRIPT'}
		<p class="help-block">
            <span id="optimize_script_php">{l s='PHP image optimization script is built-in script included in Super Speed, no API service required, it\'s the fastest way to optimize images.' mod='ets_superspeed'}</span>
            <span id="optimize_script_resmush">{l s='Resmush is completely free image optimization web API service. Read more' mod='ets_superspeed'} <a href="https://resmush.it/" target="_blank">{l s='here' mod='ets_superspeed'}</a></span>
            <span id="optimize_script_tynypng">{l s='TinyPNG offers free optimization for 500 jpg/png images per month, with additional cost, you can optimize more images. You can also enter multi free TinyPNG keys to optimize as many images as you want. see more' mod='ets_superspeed'} <a href="https://tinyjpg.com/" target="_blank" rel="noreferrer noopener"
                >{l s='here' mod='ets_superspeed'}</a></span>
		</p>
	{/if}
{/block}
{block name="input_row"}
    {if $input.name =='ETS_SPEED_ENABLE_LAYZY_LOAD'}
        <div class="form-group form_cache_page image_lazy_load alert alert-info">
            <p>{l s='Enable Lazy Load to defer loading of product images at page load time. Instead, these images are loaded at the moment of need' mod='ets_superspeed'}</p>
        </div>
    {/if}
    {if $input.name=='ETS_SPEED_ENABLE_PAGE_CACHE'}
        {if isset($is_dir_cache) && !$is_dir_cache}
            <div class="form-group form_cache_page page_setting alert alert-warning">
                {l s='Could not create cache directory:' mod='ets_superspeed'} {$sp_dir_cache|escape:'html':'UTF-8'}, {l s='please make sure' mod='ets_superspeed'} {$dir_cache|escape:'html':'UTF-8'} {l s='is writable. You can also try to manually create the cache directory using FTP to fix this issue.' mod='ets_superspeed'}
            </div>
        {/if}
        {if isset($install_logs) && $install_logs}
            <div class="form-group form_cache_page page_setting alert alert-danger">
                {l s='Could not create overridden files:' mod='ets_superspeed'} ({$install_logs|escape:'html':'UTF-8'}), {l s='Try to manually copy the file(s) from' mod='ets_superspeed'} "{$dir_override|escape:'html':'UTF-8'}" {l s='to' mod='ets_superspeed'} "{$sp_dir_override|escape:'html':'UTF-8'}".<br/>
                {l s='If the file(s) already exist in the Prestashop "override" directory, you will need to carefully check code written by other modules on the file(s) and merge the code manually with overridden code on corresponding file(s) of Super Speed.' mod='ets_superspeed'}<br/>
                {l s='When you are done with the fixes, delete this installation log file: ' mod='ets_superspeed'} "{$install_log_file_url|escape:'html':'UTF-8'}" {l s='to remove this message.' mod='ets_superspeed'}<br/>
                {l s='If you are not an Prestashop developer who is familiar with code changes, we recommend you to contact us, we will help you to fix this issue quickly.' mod='ets_superspeed'}
            </div>
        {/if}
        <div class="form-group form_cache_page page_setting alert alert-info">
            <p>{l s='Page cache helps improve your website speed considerably by storing all static contents into HTML files. The HTML files will be displayed instantly to website visitors everytime they query a page.' mod='ets_superspeed'}</p>
        </div>
        {if !$is_blog_installed}
            <div class="form-group form_cache_page page_setting alert alert-warning">
                <p>
                    {l s='Oops! You have not installed ' mod='ets_superspeed'}
                    <a href="https://addons.prestashop.com/en/blog-forum-new/25908-blog.html" target="_blank" rel="noreferrer noopener"
                    >BLOG module</a> -
                    {l s='the most powerful blog module for Prestashop that can help you significantly improve SEO score for your website on Google, Bing, etc. as well as increase traffic to your website.' mod='ets_superspeed'}
                    <br/>
                    <a href="https://addons.prestashop.com/en/blog-forum-new/25908-blog.html" target="_blank" rel="noreferrer noopener"
                    >BLOG module</a> {l s='is perfectly supported by Super Speed\'s page cache so we recommend you to install it to fully take advantages of Super Speed.' mod='ets_superspeed'}
                    <a href="https://addons.prestashop.com/en/blog-forum-new/25908-blog.html" target="_blank" rel="noreferrer noopener"
                    ><b><u>{l s='Get BLOG module now' mod='ets_superspeed'}</u></b></a>
                </p>
            </div>
        {/if}
    {/if}
    {if $input.name=='ETS_SPEED_OPTIMIZE_IMAGE_PRODCUT_TYPE'}
        <div class="col-lg-12">
            <div class="sp_sussec form-group form_cache_page image_old congratulations_image_success {if !$check_optimize} hide{/if}">
                {l s='Congratulations! All' mod='ets_superspeed'} <span class="total_all_image_optimized">{$check_optimize|intval}</span> {l s='image(s) on your website have been optimized to the selected image quality.' mod='ets_superspeed'}
                <span class="total_all_size_image {if !$total_size_save} hide{/if}">{l s='This helps ' mod='ets_superspeed'} <span class="total_all_size_image_optimize">{$total_size_save|escape:'html':'UTF-8'}.</span></span> {l s='Adjust "Image quality" value if you want to change quality of the images.' mod='ets_superspeed'}
            </div>
            <div class="alert alert-info form-group form_cache_page image_old">{l s='Automatically or manually optimize all existing images available on your website. Please select your preferred image quality and types of image to optimize on the following list' mod='ets_superspeed'}</div>
        </div>
    {/if}
    {$smarty.block.parent}
    {if $input.name =='ETS_SPEED_OPTIMIZE_SCRIPT'}
        <div class="form-group tinypng">
            <label class="control-label col-lg-3 required">{l s='TinyPNG API key' mod='ets_superspeed'}</label>
            <div class="col-lg-6 tinypng-input">
                <div class="input-inline">
                    <input placeholder="{l s='TinyPNG API key' mod='ets_superspeed'}" type="text" name="ETS_SPEED_API_TYNY_KEY[]" value="{if $ETS_SPEED_API_TYNY_KEY && isset($ETS_SPEED_API_TYNY_KEY[0])}{$ETS_SPEED_API_TYNY_KEY[0]|escape:'html':'UTF-8'}{/if}"/>
                    <button class="delete_api_key" {if ($ETS_SPEED_API_TYNY_KEY && Count($ETS_SPEED_API_TYNY_KEY)==1) || !$ETS_SPEED_API_TYNY_KEY}style="display:none;"{/if}><i class="icon icon-trash"></i></button>
                </div>
                {if $ETS_SPEED_API_TYNY_KEY}
                    {foreach from = $ETS_SPEED_API_TYNY_KEY key='key' item='api'}
                        {if $key!=0 && $api}
                            <div class="input-inline">
                                <input type="text" name="ETS_SPEED_API_TYNY_KEY[]" value="{$api|escape:'html':'UTF-8'}"/>
                                <button class="delete_api_key"><i class="icon icon-trash"></i></button>
                            </div>
                        {/if}
                    {/foreach}
                {/if}
            </div>
            <button class="add_api_key btn btn-default"  type="button"><i class="icon icon-plus"></i> {l s='Add key' mod='ets_superspeed'}</button>
        </div>
        <div class="form-group form_cache_page image_upload">
            <div class="alert alert-info">{l s='Optimize any images by uploading them via upload form below. You can adjust image optimization method and image quality by clicking on the optimization method name.' mod='ets_superspeed'}</div>
        </div>
        <div class="form-group form_cache_page image_browse">
            <div class="alert alert-info">{l s='Browse images on your server and optimize any images you want.' mod='ets_superspeed'}</div>
        </div>
        <div class="form-group form_cache_page image_upload image_browse optimize">
            <div class="form-group form_cache_page image_upload image_upload_otpimize_quality">
                <i class="fa fa-cogs"></i>
                {if $fields_value['ETS_SPEED_OPTIMIZE_SCRIPT_UPLOAD']=='google'}
                    {l s='Google Webp image optimizer' mod='ets_superspeed'}
                {elseif $fields_value['ETS_SPEED_OPTIMIZE_SCRIPT_UPLOAD']=='php'}
                    {l s='PHP image optimization script' mod='ets_superspeed'}
                {elseif $fields_value['ETS_SPEED_OPTIMIZE_SCRIPT_UPLOAD']=='tynypng'}
                    {l s='TinyPNG - Premium image optimization web service API (500 images for free per month)' mod='ets_superspeed'}
                {elseif $fields_value['ETS_SPEED_OPTIMIZE_SCRIPT_UPLOAD']=='resmush'}
                    {l s='Resmush - Free image optimization web service API' mod='ets_superspeed'}
                {else}
                    {l s='PHP image optimization script' mod='ets_superspeed'}
                {/if}
                {if $fields_value['ETS_SPEED_QUALITY_OPTIMIZE_UPLOAD']}
                    ({$fields_value['ETS_SPEED_QUALITY_OPTIMIZE_UPLOAD']|intval}%)
                {else}
                    (50%)
                {/if}
            </div>
            <div class="form-group form_cache_page image_browse image_upload_otpimize_quality">
                <i class="fa fa-cogs"></i>
                {if $fields_value['ETS_SPEED_OPTIMIZE_SCRIPT_BROWSE']=='google'}
                    {l s='Google Webp image optimizer' mod='ets_superspeed'}
                {elseif $fields_value['ETS_SPEED_OPTIMIZE_SCRIPT_BROWSE']=='php'}
                    {l s='PHP image optimization script' mod='ets_superspeed'}
                {elseif $fields_value['ETS_SPEED_OPTIMIZE_SCRIPT_BROWSE']=='tynypng'}
                    {l s='TinyPNG - Premium image optimization web service API (500 images for free per month)' mod='ets_superspeed'}
                {elseif $fields_value['ETS_SPEED_OPTIMIZE_SCRIPT_BROWSE']=='resmush'}
                    {l s='Resmush - Free image optimization web service API' mod='ets_superspeed'}
                {else}
                   {l s='PHP image optimization script' mod='ets_superspeed'}
                {/if}
                {if $fields_value['ETS_SPEED_QUALITY_OPTIMIZE_BROWSE']}
                    ({$fields_value['ETS_SPEED_QUALITY_OPTIMIZE_BROWSE']|intval}%)
                {else}
                    (50%)
                {/if}
            </div>
            <div class="popup-optimize_image_upload">
                <div class="popup_table">
                    <div class="popup_tablecell">
                        <div class="popup-content">
                            <div class="sp_close">{l s='Close' mod='ets_superspeed'}</div>
                            <div class="popup-content-header popup_run">
                                <h3>{l s='Optimization settings' mod='ets_superspeed'}</h3>
                            </div>
                            <div class="popup-content-body">
                                <div class="form-group form_cache_page image_upload script">
                                    <label class="control-label col-lg-3">{l s='Image optimization method' mod='ets_superspeed'}</label>
                                    <div class="col-lg-9">
                                        <select id="ETS_SPEED_OPTIMIZE_SCRIPT_UPLOAD" class="" name="ETS_SPEED_OPTIMIZE_SCRIPT_UPLOAD">
                                            {foreach $input.options.query AS $option}
                                                {if is_object($option)}
                                                    <option value="{$option->$input.options.id|escape:'html':'UTF-8'}"
                                                        {if isset($input.multiple)}
                                                            {foreach $fields_value['ETS_SPEED_OPTIMIZE_SCRIPT_UPLOAD'] as $field_value}
                                                                {if $field_value == $option->$input.options.id}
                                                                    selected="selected"
                                                                {/if}
                                                            {/foreach}
                                                        {else}
                                                            {if $fields_value['ETS_SPEED_OPTIMIZE_SCRIPT_UPLOAD'] == $option->$input.options.id}
                                                                selected="selected"
                                                            {/if}
                                                        {/if}
                                                    >{$option->$input.options.name|escape:'html':'UTF-8'}</option>
                                                {elseif $option == "-"}
                                                    <option value="">-</option>
                                                {else}
                                                    <option value="{$option[$input.options.id]|escape:'html':'UTF-8'}"
                                                        {if isset($input.multiple)}
                                                            {foreach $fields_value['ETS_SPEED_OPTIMIZE_SCRIPT_UPLOAD'] as $field_value}
                                                                {if $field_value == $option[$input.options.id]}
                                                                    selected="selected"
                                                                {/if}
                                                            {/foreach}
                                                        {else}
                                                            {if $fields_value['ETS_SPEED_OPTIMIZE_SCRIPT_UPLOAD'] == $option[$input.options.id]}
                                                                selected="selected"
                                                            {/if}
                                                        {/if}
                                                    >{$option[$input.options.name]|escape:'html':'UTF-8'}</option>
                                                {/if}
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group form_cache_page image_browse script">
                                    <label class="control-label col-lg-3">{l s='Image optimization method' mod='ets_superspeed'}</label>
                                    <div class="col-lg-9">
                                        <select id="ETS_SPEED_OPTIMIZE_SCRIPT_BROWSE" class="" name="ETS_SPEED_OPTIMIZE_SCRIPT_BROWSE">
                                            {foreach $input.options.query AS $option}
                                                {if is_object($option)}
                                                    <option value="{$option->$input.options.id|escape:'html':'UTF-8'}"
                                                        {if isset($input.multiple)}
                                                            {foreach $fields_value['ETS_SPEED_OPTIMIZE_SCRIPT_BROWSE'] as $field_value}
                                                                {if $field_value == $option->$input.options.id}
                                                                    selected="selected"
                                                                {/if}
                                                            {/foreach}
                                                        {else}
                                                            {if $fields_value['ETS_SPEED_OPTIMIZE_SCRIPT_BROWSE'] == $option->$input.options.id}
                                                                selected="selected"
                                                            {/if}
                                                        {/if}
                                                    >{$option->$input.options.name|escape:'html':'UTF-8'}</option>
                                                {elseif $option == "-"}
                                                    <option value="">-</option>
                                                {else}
                                                    <option value="{$option[$input.options.id]|escape:'html':'UTF-8'}"
                                                        {if isset($input.multiple)}
                                                            {foreach $fields_value['ETS_SPEED_OPTIMIZE_SCRIPT_BROWSE'] as $field_value}
                                                                {if $field_value == $option[$input.options.id]}
                                                                    selected="selected"
                                                                {/if}
                                                            {/foreach}
                                                        {else}
                                                            {if $fields_value['ETS_SPEED_OPTIMIZE_SCRIPT_BROWSE'] == $option[$input.options.id]}
                                                                selected="selected"
                                                            {/if}
                                                        {/if}
                                                    >{$option[$input.options.name]|escape:'html':'UTF-8'}</option>
                                                {/if}
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group form-group form_cache_page image_upload">
                                    <label class="control-label col-lg-3">{l s='Image quality' mod='ets_superspeed'} </label>
                                    <div class="col-lg-9">
                                        <div class="range_custom">
                                            <span class="range_min">1</span>
                                            <span class="range_max">100</span>
                                             <input id="ETS_SPEED_QUALITY_OPTIMIZE_UPLOAD" name="ETS_SPEED_QUALITY_OPTIMIZE_UPLOAD" type="range" min="1" max="100" value="{if $fields_value['ETS_SPEED_QUALITY_OPTIMIZE_UPLOAD']}{$fields_value['ETS_SPEED_QUALITY_OPTIMIZE_UPLOAD']|intval}{else}50{/if}" data-unit="%" data-units="%" />
                                             <div class="range_new">
                                                <span class="range_new_bar"></span>
                                                <span class="range_new_run">
                                                    <span class="range_new_button"></span>
                                                </span>
                                             </div>
                                             <span class="input-group-unit">
                                                ({if $fields_value['ETS_SPEED_QUALITY_OPTIMIZE_UPLOAD']}{$fields_value['ETS_SPEED_QUALITY_OPTIMIZE_UPLOAD']|intval}{else}50{/if}%)
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group form-group form_cache_page image_browse">
                                    <label class="control-label col-lg-3">{l s='Image quality' mod='ets_superspeed'} </label>
                                    <div class="col-lg-9">
                                        <div class="range_custom">
                                            <span class="range_min">1</span>
                                            <span class="range_max">100</span>
                                             <input id="ETS_SPEED_QUALITY_OPTIMIZE_BROWSE" name="ETS_SPEED_QUALITY_OPTIMIZE_BROWSE" type="range" min="1" max="100" value="{if $fields_value['ETS_SPEED_QUALITY_OPTIMIZE_BROWSE']}{$fields_value['ETS_SPEED_QUALITY_OPTIMIZE_BROWSE']|intval}{else}50{/if}" data-unit="%" data-units="%" />
                                             <div class="range_new">
                                                <span class="range_new_bar"></span>
                                                <span class="range_new_run">
                                                    <span class="range_new_button"></span>
                                                </span>
                                             </div>
                                             <span class="input-group-unit">
                                                ({if $fields_value['ETS_SPEED_QUALITY_OPTIMIZE_BROWSE']}{$fields_value['ETS_SPEED_QUALITY_OPTIMIZE_BROWSE']|intval}{else}50{/if}%)
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="popup-content-footer">
                                <div class="form-group form_cache_page image_upload">
                                    <button type="button" class="btn btn-default full-left btn-cancel"><i class="process-icon-cancel"></i> {l s='Cancel' mod='ets_superspeed'}</button>
                                    <button type="button" name="btnSaveOptimizeImageUpload" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save' mod='ets_superspeed'}</button>
                                </div>
                                <div class="form-group form_cache_page image_browse">
                                    <button type="button" class="btn btn-default full-left btn-cancel"><i class="process-icon-cancel"></i> {l s='Cancel' mod='ets_superspeed'}</button>
                                    <button type="button" name="btnSaveOptimizeImageBrowse" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save' mod='ets_superspeed'}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group form_cache_page image_upload optimize">
            <div class="image_upload_form">
                <input type="file" name="multiple_imamges[]" id="ets_sp_multiple_imamges" multiple="multiple"  />
                <i class="fa fa-cloud-upload"></i> {l s='Upload images to optimize' mod='ets_superspeed'}
                <p class="help-block">{l s='Accepted formats: jpg, gif, jpeg, png, webp. Limit' mod='ets_superspeed'} {Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE')|intval}Mb </p>
            </div>
        </div>
        <div class="form-group form_cache_page image_upload optimize">
            <ul id="list_added_images">
                {Module::getInstanceByName('ets_superspeed')->hookDisplayImagesUploaded() nofilter}
            </ul>
        </div>
        <div class="form-group form_cache_page image_browse optimize">
            {Module::getInstanceByName('ets_superspeed')->hookDisplayImagesBrowse() nofilter}
        </div>
        <div class="form-group form_cache_page image_cleaner optimize">
            {Module::getInstanceByName('ets_superspeed')->hookDisplayImagesCleaner() nofilter}
        </div>
    {/if}
{/block}
{block name="label"}
	  {$smarty.block.parent} 
{/block}
{block name="input"}
    {if $input.type == 'switch'}
		<span class="switch prestashop-switch fixed-width-lg">
			{foreach $input.values as $value}
                <input type="radio" name="{$input.name|escape:'html':'UTF-8'}"{if $value.value == 1} id="{$input.name|escape:'html':'UTF-8'}_on"{else} id="{$input.name|escape:'html':'UTF-8'}_off"{/if} value="{$value.value|escape:'html':'UTF-8'}"{if $fields_value[$input.name] == $value.value} checked="checked"{/if}{if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}/>
                {strip}
                    <label {if $value.value == 1} for="{$input.name|escape:'html':'UTF-8'}_on"{else} for="{$input.name|escape:'html':'UTF-8'}_off"{/if}>
                        {$value.label|escape:'html':'UTF-8'}
                    </label>
                {/strip}
			{/foreach}
			<a class="slide-button btn"></a>
            {if isset($input.desc_toltip) && $input.desc_toltip}
                <span class="ets_superspeed_toltip">
                    <i class="fa fa-question-circle"></i>
                    <span class="toltip">{$input.desc_toltip|escape:'html':'UTF-8'}</span>
                </span>
            {/if}
		</span>
    {elseif $input.type == 'radio'}
        {foreach $input.values as $value}
            <div class="radio {if isset($input.class)}{$input.class|escape:'html':'UTF-8'}{/if}">
                {strip}
                    <label>
                        <input type="radio"	name="{$input.name|escape:'html':'UTF-8'}" id="{$value.id|escape:'html':'UTF-8'}" value="{$value.value|escape:'html':'UTF-8'}"{if $fields_value[$input.name] == $value.value} checked="checked"{/if}{if (isset($input.disabled) && $input.disabled) or (isset($value.disabled) && $value.disabled)} disabled="disabled"{/if}/>

                        {if isset($value.html)}
                            <div class="transition_input">{$value.html nofilter}</div>
                        {else}
                            {$value.label|escape:'html':'UTF-8'}
                        {/if}
                    </label>
                {/strip}
            </div>
            {if isset($value.p) && $value.p}<p class="help-block">{$value.p|escape:'html':'UTF-8'}</p>{/if}
        {/foreach}
    {elseif $input.type == 'checkbox'}
            {if isset($input.values.query) && $input.values.query}
                {assign var=id_checkbox value=$input.name|cat:'_'|cat:'all'}
                {assign var=checkall value=true}
				{foreach $input.values.query as $value}
    				{if !(isset($fields_value[$input.name]) && is_array($fields_value[$input.name]) && $fields_value[$input.name] && in_array($value.value,$fields_value[$input.name]))} 
                        {assign var=checkall value=false}
                    {/if}
    			{/foreach}
                {if $input.name=='ETS_SPEED_PAGES_TO_CACHE'}
                <div class="checkbox_group_and_range">
                <div class="col-lg-5 sp_input_checkbox_left header_tit"> 
                {/if}
                {if count($input.values.query) >1}
                    <div class="checkbox_all checkbox">
    					{strip}
    						<label for="{$id_checkbox|escape:'html':'UTF-8'}">                                
    							<input type="checkbox" name="{$input.name|escape:'html':'UTF-8'}[]" id="{$id_checkbox|escape:'html':'UTF-8'}" {if isset($value.value)} value="0"{/if}{if $checkall} checked="checked"{/if} />
    							<i class="md-checkbox-control"></i>
                                {if $input.name=='ETS_SPEED_PAGES_TO_CACHE'}
                                    {l s='All pages' mod='ets_superspeed'}
                                {else}
                                    {l s='All image types' mod='ets_superspeed'}
                                {/if}
    						</label>
    					{/strip}
    				</div>
                {/if}
                 {if $input.name=='ETS_SPEED_PAGES_TO_CACHE'}
                 </div>
                 <div class="col-lg-7 sp_input_checkbox_right header_tit">
                    <h4 class="title">{l s='Cache life time' mod='ets_superspeed'}</h4>
                 </div>
                 {/if}
                {foreach $input.values.query as $value}
                    {if $input.name!='ETS_SPEED_OPTIMIZE_IMAGE_HOME_SLIDE_TYPE' && $input.name!='ETS_SPEED_OPTIMIZE_IMAGE_BLOG_SLIDE_TYPE'}
        				{assign var=id_checkbox value=$input.name|cat:'_'|cat:$value[$input.values.id]|escape:'html':'UTF-8'}
                        {if isset($value.extra)}
                            <div class="col-lg-5 sp_input_checkbox_left">
                        {/if}
                            {if $input.name=='ETS_SPEED_OPTIMIZE_IMAGE_OTHERS_TYPE' && $value.value=='home_slide'}
                                <div class="checkbox{if isset($input.expand) && strtolower($input.expand.default) == 'show'} hidden{/if}">
                					 {strip}
                						<label for="ETS_SPEED_OPTIMIZE_IMAGE_HOME_SLIDE_TYPE_image" class="unoptimized_image" data-image="home_slide_image">                                
                							<input type="checkbox" name="ETS_SPEED_OPTIMIZE_IMAGE_HOME_SLIDE_TYPE[]" id="ETS_SPEED_OPTIMIZE_IMAGE_HOME_SLIDE_TYPE_image" value="image" {if isset($fields_value['ETS_SPEED_OPTIMIZE_IMAGE_HOME_SLIDE_TYPE']) && is_array($fields_value['ETS_SPEED_OPTIMIZE_IMAGE_HOME_SLIDE_TYPE']) && $fields_value['ETS_SPEED_OPTIMIZE_IMAGE_HOME_SLIDE_TYPE'] && in_array('image',$fields_value['ETS_SPEED_OPTIMIZE_IMAGE_HOME_SLIDE_TYPE'])} checked="checked"{/if} />
                                            <i class="md-checkbox-control"></i>
                                            {$value[$input.values.name]|escape:'html':'UTF-8'}
                                            {if isset($input.image_old) && $input.image_old}
                                                {if isset($value.total_image) && $value.total_image}
                                                    {if $value.total_image_optimized}
                                                        &nbsp;<span class="total_unoptimized_image"><span class="alert-blue">{$value.total_image_optimized|intval} {if $quality_optimize==100}{l s='restored' mod='ets_superspeed'}{else}{l s='optimized' mod='ets_superspeed'}{/if}</span>, {$value.total_image|intval} {if $quality_optimize==100}{l s='restorable' mod='ets_superspeed'}{else}{l s='unoptimized' mod='ets_superspeed'}{/if}</span>
                                                    {else}
                                                         &nbsp;<span class="total_unoptimized_image alert-yellow">{$value.total_image|intval} {if $quality_optimize==100}{l s='restorable' mod='ets_superspeed'}{else}{l s='unoptimized' mod='ets_superspeed'}{/if}</span>   
                                                    {/if}
                                                {else}
                                                    <span class="total_unoptimized_image"><span class="alert-blue">{if $quality_optimize==100}{l s='100% restored' mod='ets_superspeed'}{else}{l s='100% optimized' mod='ets_superspeed'}{/if}</span>{if $quality_optimize==100}, <span>{$value.total_image_optimized|intval} {l s='unoptimized' mod='ets_superspeed'}</span>{/if}</span>
                                                {/if}   
                                            {/if}
                						</label>
                					{/strip}
                				</div>
                            {elseif $input.name=='ETS_SPEED_OPTIMIZE_IMAGE_BLOG_GALLERY_TYPE' && $value.value=='blog_slide' }
                                <div class="checkbox{if isset($input.expand) && strtolower($input.expand.default) == 'show'} hidden{/if}">
                					 {strip}
                						<label for="ETS_SPEED_OPTIMIZE_IMAGE_BLOG_SLIDE_TYPE_image" class="unoptimized_image" data-image="blog_slide_image">                                
                							<input type="checkbox" name="ETS_SPEED_OPTIMIZE_IMAGE_BLOG_SLIDE_TYPE[]" id="ETS_SPEED_OPTIMIZE_IMAGE_BLOG_SLIDE_TYPE_image" value="image" {if isset($fields_value['ETS_SPEED_OPTIMIZE_IMAGE_BLOG_SLIDE_TYPE']) && is_array($fields_value['ETS_SPEED_OPTIMIZE_IMAGE_BLOG_SLIDE_TYPE']) && $fields_value['ETS_SPEED_OPTIMIZE_IMAGE_BLOG_SLIDE_TYPE'] && in_array('image',$fields_value['ETS_SPEED_OPTIMIZE_IMAGE_BLOG_SLIDE_TYPE'])} checked="checked"{/if} />
                                            <i class="md-checkbox-control"></i>
                                            {$value[$input.values.name]|escape:'html':'UTF-8'}
                                            {if isset($input.image_old) && $input.image_old}
                                                {if isset($value.total_image) && $value.total_image}
                                                    {if $value.total_image_optimized}
                                                        &nbsp;<span class="total_unoptimized_image"><span class="alert-blue">{$value.total_image_optimized|intval} {if $quality_optimize==100}{l s='restored' mod='ets_superspeed'}{else}{l s='optimized' mod='ets_superspeed'}{/if}</span>, {$value.total_image|intval} {if $quality_optimize==100}{l s='restorable' mod='ets_superspeed'}{else}{l s='unoptimized' mod='ets_superspeed'}{/if}</span>
                                                    {else}
                                                         &nbsp;<span class="total_unoptimized_image alert-yellow">{$value.total_image|intval} {if $quality_optimize==100}{l s='restorable' mod='ets_superspeed'}{else}{l s='unoptimized' mod='ets_superspeed'}{/if}</span>   
                                                    {/if}
                                                {else}
                                                    <span class="total_unoptimized_image"><span class="alert-blue">{if $quality_optimize==100}{l s='100% restored' mod='ets_superspeed'}{else}{l s='100% optimized' mod='ets_superspeed'}{/if}</span>{if $quality_optimize==100}, <span>{$value.total_image_optimized|intval} {l s='unoptimized' mod='ets_superspeed'}</span>{/if}</span>
                                                {/if}   
                                            {/if}
                						</label>
                					{/strip}
                				</div>
                            {else}
                				<div class="checkbox{if isset($input.expand) && strtolower($input.expand.default) == 'show'} hidden{/if}">
                					 {strip}
                						<label for="{$id_checkbox|escape:'html':'UTF-8'}" {if isset($input.image_old) && $input.image_old} class="unoptimized_image" data-image="{$input.image_old|escape:'html':'UTF-8'}_{$value.value|escape:'html':'UTF-8'}"{/if}>                                
                							<input type="checkbox" name="{$input.name|escape:'html':'UTF-8'}[]" id="{$id_checkbox|escape:'html':'UTF-8'}" {if isset($value.value)} value="{$value.value|escape:'html':'UTF-8'}"{/if}{if isset($fields_value[$input.name]) && is_array($fields_value[$input.name]) && $fields_value[$input.name] && in_array($value.value,$fields_value[$input.name])} checked="checked"{/if} />
                                            <i class="md-checkbox-control"></i>
                                            {$value[$input.values.name]|escape:'html':'UTF-8'}
                                            {if isset($input.image_old) && $input.image_old}
                                                {if isset($value.total_image) && $value.total_image}
                                                    {if $value.total_image_optimized}
                                                        &nbsp;<span class="total_unoptimized_image"><span class="alert-blue">{$value.total_image_optimized|intval} {if $quality_optimize==100}{l s='restored' mod='ets_superspeed'}{else}{l s='optimized' mod='ets_superspeed'}{/if}</span>, {$value.total_image|intval} {if $quality_optimize==100}{l s='restorable' mod='ets_superspeed'}{else}{l s='unoptimized' mod='ets_superspeed'}{/if}</span>
                                                    {else}
                                                         &nbsp;<span class="total_unoptimized_image alert-yellow">{$value.total_image|intval} {if $quality_optimize==100}{l s='restorable' mod='ets_superspeed'}{else}{l s='unoptimized' mod='ets_superspeed'}{/if}</span>   
                                                    {/if}
                                                {else}
                                                    <span class="total_unoptimized_image"><span class="alert-blue">{if $quality_optimize==100}{l s='100% restored' mod='ets_superspeed'}{else}{l s='100% optimized' mod='ets_superspeed'}{/if}</span>{if $quality_optimize==100}, <span>{$value.total_image_optimized|intval} {l s='unoptimized' mod='ets_superspeed'}</span>{/if}</span>
                                                {/if}   
                                            {/if}
                						</label>
                					{/strip}
                				</div>
                            {/if}
                        {if isset($value.extra)}
                            </div>
                            <div class="col-lg-7 sp_input_checkbox_right">
                                <div class="range_custom">
                                    <span class="range_min">1</span>
                                    <span class="range_max">30</span>
                                    <input  name="{$value.extra|escape:'html':'UTF-8'}" type="range" min="1" max="31" value="{$fields_value[$value.extra]|intval}" data-unit="{l s='Day' mod='ets_superspeed'}" data-units="{l s='Days' mod='ets_superspeed'}" forever="1" />
                                    <div class="range_new">
                                        <span class="range_new_bar"></span>
                                        <span class="range_new_run">
                                            <span class="range_new_button"></span>
                                        </span>
                                     </div>
                                    <span class="input-group-unit">
                                        {if $fields_value[$value.extra] <=1}
                                            {if $fields_value[$value.extra]}{$fields_value[$value.extra]|intval}{else}1{/if}{l s='Day' mod='ets_superspeed'}
                                        {else}
                                            {if $fields_value[$value.extra]==31}
                                                {l s='Forever' mod='ets_superspeed'}
                                            {else}
                                                {$fields_value[$value.extra]|intval}{l s='Days' mod='ets_superspeed'}
                                            {/if}
                                        {/if}
                                    </span>
                                </div>
                            </div>
                        {/if}
                    {/if}
    			{/foreach} 
                {if $input.name=='ETS_SPEED_PAGES_TO_CACHE'}
                </div>
                {/if}
            {/if} 
    {elseif $input.type=='range'}
        <div class="range_custom">
            <span class="range_min">{$input.min|intval}</span>
            <span class="range_max">{$input.max|intval}</span>
             <input  name="{$input.name|escape:'html':'UTF-8'}" type="range" min="{$input.min|intval}" max="{$input.max|intval}" value="{$fields_value[$input.name]|intval}" data-unit="{if isset($input.unit)}{$input.unit|escape:'html':'UTF-8'}{/if}" data-units="{if isset($input.units)}{$input.units|escape:'html':'UTF-8'}{/if}" />
             <div class="range_new">
                <span class="range_new_bar"></span>
                <span class="range_new_run">
                    <span class="range_new_button"></span>
                </span>
             </div>
             <span class="input-group-unit">
                {if $fields_value[$input.name] <=1}
                    ({if $fields_value[$input.name]}{$fields_value[$input.name]|intval}{else}1{/if}{if isset($input.unit)}&nbsp;{$input.unit|escape:'html':'UTF-8'}{/if})
                {else}
                    ({$fields_value[$input.name]|intval}{if isset($input.units)}&nbsp;{$input.units|escape:'html':'UTF-8'}{/if})
                {/if}
            </span>
        </div>
    {elseif $input.type=='buttons'}
        <div class="sp_button-group">
            {foreach from=$input.buttons item='button'}
                <button type="{$button.type|escape:'html':'UTF-8'}" name="{$button.name|escape:'html':'UTF-8'}" class="btn btn-default{if isset($button.class)} {$button.class|escape:'html':'UTF-8'}{/if}">{if isset($button.icon)}<i class="{$button.icon|escape:'html':'UTF-8'}" ></i> {/if}{$button.title|escape:'html':'UTF-8'}</button>
                {if $button.name=='btnSubmitSuperSpeedException'}
                    <h4 class="title_bg_gray">
                        <span>{l s='Module exceptions' mod='ets_superspeed'}</span>
                    </h4>
                {/if}
            {/foreach}
        </div>
    {else if $input.type=='list_module'}
        {if $input.modules}
            <div class="alert alert-info">
                {l s='Disable cache for modules/hooks you need base on your demands. Those modules/hooks will be dynamically loaded via ajax just after website is displayed to front end user.' mod='ets_superspeed'}
            </div>
            <ul class="list_moudule">
                <li class="list_module_header">
                    <div>{l s='Module' mod='ets_superspeed'}</div>
                    <div>{l s='Hook' mod='ets_superspeed'}</div>
                    <div class="text-center">{l s='Disable cache' mod='ets_superspeed'}</div>
                    <div class="text-center">{l s='Initiate with empty content' mod='ets_superspeed'}</div>
                </li>
                {foreach from=$input.modules item='module'}
                    <li>
                        <div class="module-logo">
                            <img src="{$module.logo|escape:'html':'UTF-8'}"/>
                            <div class="list_module_info">
                                <span class="module-name"><strong>{Module::getModuleName($module.name)|escape:'html':'UTF-8'}</strong></span>
                                <span class="author-module">{l s='Author:' mod='ets_superspeed'} {Ets_superspeed::getModuleAuthor($module.name)|escape:'html':'UTF-8'}</span>
                                <span class="module-version">{l s='Version:' mod='ets_superspeed'} {$module.version|escape:'html':'UTF-8'}</span>
                            </div>
                        </div>
                        <div class="list_module_hook_text">
                            {if $module.hooks}
                                <ul class="list-hooks">
                                    {foreach from=$module.hooks item='hook'}
                                        <li class="hook-item">
                                            <label for="{$input.name|escape:'html':'UTF-8'}_{$module.id_module|intval}_{$hook.name|escape:'html':'UTF-8'}">
                                                {$hook.name|escape:'html':'UTF-8'}
                                            </label>
                                        </li>
                                    {/foreach}
                                </ul>
                            {/if}
                        </div>
                        <div class="list_module_hook">
                            {if $module.hooks}
                                <ul class="list-hooks">
                                    {foreach from=$module.hooks item='hook'}
                                        <li class="hook-item">
                                            <label for="{$input.name|escape:'html':'UTF-8'}_{$module.id_module|intval}_{$hook.name|escape:'html':'UTF-8'}">
                                                <input{if $hook.dynamic} checked="checked"{/if} type="checkbox" id="{$input.name|escape:'html':'UTF-8'}_{$module.id_module|intval}_{$hook.name|escape:'html':'UTF-8'}" name="{$input.name|escape:'html':'UTF-8'}[]" class="{$input.name|escape:'html':'UTF-8'}" value="1" data-module="{$module.id_module|intval}" data-hook="{$hook.name|escape:'html':'UTF-8'}" />
                                                <span class="ets_solo_switch">
                                                    <span class="ets_solo_slider_label on">{l s='Yes' mod='ets_superspeed'}</span>
                                                    <span class="ets_solo_slider_label off">{l s='No' mod='ets_superspeed'}</span>
                                                </span>
                                            </label>
                                        </li>
                                    {/foreach}
                                </ul>
                            {/if}
                        </div>
                        <div class="list_module_hook empty">
                            {if $module.hooks}
                                <ul class="list-hooks">
                                    {foreach from=$module.hooks item='hook'}
                                        <li class="hook-item">
                                            <label for="empty_{$input.name|escape:'html':'UTF-8'}_{$module.id_module|intval}_{$hook.name|escape:'html':'UTF-8'}" >
                                                <input {if !$hook.dynamic} disabled="disabled"{/if} type="checkbox" name="empty_{$input.name|escape:'html':'UTF-8'}[]" id="empty_{$input.name|escape:'html':'UTF-8'}_{$module.id_module|intval}_{$hook.name|escape:'html':'UTF-8'}" value="1" data-module="{$module.id_module|intval}" data-hook="{$hook.name|escape:'html':'UTF-8'}" class="empty_{$input.name|escape:'html':'UTF-8'}" {if $hook.dynamic && $hook.dynamic.empty_content} checked="checked" {/if}/>
                                                <span class="ets_solo_switch">
                                                    <span class="ets_solo_slider_label on">{l s='Yes' mod='ets_superspeed'}</span>
                                                    <span class="ets_solo_slider_label off">{l s='No' mod='ets_superspeed'}</span>
                                                </span>
                                            </label>
                                        </li>
                                    {/foreach}
                                </ul>
                            {/if}
                        </div>
                    </li>
                {/foreach}
            </ul>
        {/if}
    {else if $input.type=='html_block'}
        {$input.html_content nofilter}
    {else}
        {$smarty.block.parent}               
    {/if} 
{/block}
{block name="legend"}
    {if isset($configTabs) && $configTabs}<div class="ets_sp_confitab_wrap">{/if}
    {$smarty.block.parent} 
    {if isset($configTabs) && $configTabs}
        <ul class="tab_config_page_cache">
            {foreach from=$configTabs item='tab' key='tabId'}
                <li class="confi_tab config_tab_{$tabId|escape:'html':'UTF-8'} {if isset($current_tab) && $current_tab==$tabId}active{/if}" data-tab-id="{$tabId|escape:'html':'UTF-8'}" >
                    <span>{$tab|escape:'html':'UTF-8'}</span>
                </li>
            {/foreach}
            <li class="viewmore_dropdown">
                <span class="threedots"></span>
            </li>
        </ul>
        </div>
    {/if}
{/block}
{block name="defaultForm"}
    {$smarty.block.parent}
    {if isset($file_caches) && $file_caches}
        <div class="form-group form_cache_page page-list-caches">
            {$file_caches nofilter}
        </div>
    {/if}
    {if isset($file_no_caches) && $file_no_caches}
        <div class="form-group form_cache_page page-list-no-caches">
            {$file_no_caches nofilter}
        </div>
    {/if}
    {if isset($list_log_clear_history) && $list_log_clear_history}
        <div class="form-group form_cache_page page-list-log-clear-history">
            {$list_log_clear_history nofilter}
        </div>
    {/if}
{/block}

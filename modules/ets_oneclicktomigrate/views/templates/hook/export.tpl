{*
* 2007-2019 ETS-Soft ETS-Soft
*
* NOTICE OF LICENSE
*
* This file is not open source! Each license that you purchased is only available for 1 wesite only.
* If you want to use this file on more websites (or projects), you need to purchase additional licenses.
* You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please, contact us for extra customization service at an affordable price
*
*  @author ETS-Soft <etssoft.jsc@gmail.com>
*  @copyright  2007-2019 ETS-Soft ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}
<script type="text/javascript">
var ETS_DT_MODULE_URL_AJAX ='{$ETS_DT_MODULE_URL_AJAX|escape:'html':'UTF-8'}';
</script>
<div class="dtm-left-block">
    {hook h='datamasterLeftBlok'}
</div>
<div class="dtm-right-block">
    {if isset($errors) && $errors}
    <div class="bootstrap">
        <div class="module_error alert alert-danger">
            <button class="close" data-dismiss="alert" type="button">×</button>
            {foreach from=$errors item='error'}
                {$error|escape:'html':'UTF-8'}<br />
            {/foreach}
        </div>
    </div>
    {/if}
    <form id="module_form" action="{$link->getAdminLink('AdminDataMasterExport')|escape:'html':'UTF-8'}" class="defaultForm form-horizontal" novalidate="" enctype="multipart/form-data" method="post" >
        <input type="hidden" value="{$step|intval}" name="step"/>
        <div id="fieldset_0" class="panel">
            <div class="panel-heading"><i class="icon-export"></i>{l s='Export data' mod='ets_oneclicktomigrate'}</div>
            <ul class="tab_step_data">
                <li class="data_number_step{if $step==1} active{/if}" data-step="1"><span>1</span>{l s='Data to export' mod='ets_oneclicktomigrate'}</li>
                <li class="data_number_step{if $step==2} active{/if}" data-step="2"><span>2</span>{l s='Formatting' mod='ets_oneclicktomigrate'}</li>
                <li class="data_number_step{if $step==3} active{/if}" data-step="3"><span>3</span>{l s='Review export' mod='ets_oneclicktomigrate'}</li>
                <li class="data_number_step{if $step==4} active{/if}" data-step="4"><span>4</span>{l s='Proceed exportation' mod='ets_oneclicktomigrate'}</li>
            </ul>
            <div class="form-wrapper data_export_data">
                <div class="ybc-form-group ybc-blog-tab-step1{if $step==1} active{/if}">
                    <div class="form-group">
    				    <label class="control-label">{l s='Which kind of data do you want to export?' mod='ets_oneclicktomigrate'}</label>
    				    <div class="col-lg-9">
                            {if Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE')}
                                <div class="checkbox">
        				            <label for="data_export_shops"><input{if in_array('shops',$ets_datamaster_export)} checked="checked"{/if} name="data_export[]" value="shops" type="checkbox" id="data_export_shops" /><span class="data_checkbox_style"><i class="icon icon-check"></i></span>{l s='Shops' mod='ets_oneclicktomigrate'}</label>
                                </div>
                            {/if}
                            <div class="checkbox">
    				            <label for="data_export_employees"><input{if in_array('employees',$ets_datamaster_export)} checked="checked"{/if} name="data_export[]" value="employees" type="checkbox" id="data_export_employees" /><span class="data_checkbox_style"><i class="icon icon-check"></i></span>{l s='Employees' mod='ets_oneclicktomigrate'}</label>
                            </div>
                            <div class="checkbox">
    				            <label for="data_export_categories"><input{if in_array('categories',$ets_datamaster_export)} checked="checked"{/if} name="data_export[]" value="categories" type="checkbox" id="data_export_categories" /><span class="data_checkbox_style"><i class="icon icon-check"></i></span>{l s='Product categories' mod='ets_oneclicktomigrate'}</label>
                            </div>
                            <div class="checkbox">
    				            <label for="data_export_manufactures"><input{if in_array('manufactures',$ets_datamaster_export)} checked="checked"{/if} name="data_export[]" value="manufactures" type="checkbox" id="data_export_manufactures" /><span class="data_checkbox_style"><i class="icon icon-check"></i></span>{l s='Manufacturers' mod='ets_oneclicktomigrate'}</label>
                            </div>
                            <div class="checkbox">
    				            <label for="data_export_suppliers"><input{if in_array('suppliers',$ets_datamaster_export)} checked="checked"{/if} name="data_export[]" value="suppliers" type="checkbox" id="data_export_suppliers" /><span class="data_checkbox_style"><i class="icon icon-check"></i></span>{l s='Suppliers' mod='ets_oneclicktomigrate'}</label>
                            </div>
                            <div class="checkbox">
    				            <label for="data_export_products"><input{if in_array('products',$ets_datamaster_export)} checked="checked"{/if} name="data_export[]" value="products" type="checkbox" id="data_export_products" /><span class="data_checkbox_style"><i class="icon icon-check"></i></span>{l s='Products' mod='ets_oneclicktomigrate'}</label>
                            </div>
                            <div class="checkbox">
    				            <label for="data_export_customers"><input{if in_array('customers',$ets_datamaster_export)} checked="checked"{/if} name="data_export[]" value="customers" type="checkbox" id="data_export_customers" /><span class="data_checkbox_style"><i class="icon icon-check"></i></span>{l s='Customers & addresses' mod='ets_oneclicktomigrate'}</label>
                            </div>
                            <div class="checkbox">
    				            <label for="data_export_carriers"><input{if in_array('carriers',$ets_datamaster_export)} checked="checked"{/if} name="data_export[]" value="carriers" type="checkbox" id="data_export_carriers" /><span class="data_checkbox_style"><i class="icon icon-check"></i></span>{l s='Carriers & shipping prices' mod='ets_oneclicktomigrate'}</label>
                            </div>
                            <div class="checkbox">
    				            <label for="data_export_cart_rules"><input{if in_array('cart_rules',$ets_datamaster_export)} checked="checked"{/if} name="data_export[]" value="cart_rules" type="checkbox" id="data_export_cart_rules" /><span class="data_checkbox_style"><i class="icon icon-check"></i></span>{l s='Cart rules' mod='ets_oneclicktomigrate'}</label>
                            </div>
                            <div class="checkbox">
    				            <label for="data_export_catelog_rules"><input{if in_array('catelog_rules',$ets_datamaster_export)} checked="checked"{/if} name="data_export[]" value="catelog_rules" type="checkbox" id="data_export_catelog_rules" /><span class="data_checkbox_style"><i class="icon icon-check"></i></span>{l s='Catalog rules' mod='ets_oneclicktomigrate'}</label>
                            </div>
                            <div class="checkbox">
    				            <label for="data_export_orders"><input{if in_array('orders',$ets_datamaster_export)} checked="checked"{/if} name="data_export[]" value="orders" type="checkbox" id="data_export_orders" /><span class="data_checkbox_style"><i class="icon icon-check"></i></span>{l s='Orders & shopping carts' mod='ets_oneclicktomigrate'}</label>
                            </div>
                            <div class="checkbox">
    				            <label for="data_export_CMS_categories"><input{if in_array('CMS_categories',$ets_datamaster_export)} checked="checked"{/if} name="data_export[]" value="CMS_categories" type="checkbox" id="data_export_CMS_categories" /><span class="data_checkbox_style"><i class="icon icon-check"></i></span>{l s='CMS categories' mod='ets_oneclicktomigrate'}</label>
                            </div>
                            <div class="checkbox">
    				            <label for="data_export_CMS"><input{if in_array('CMS',$ets_datamaster_export)} checked="checked"{/if} name="data_export[]" value="CMS" type="checkbox" id="data_export_CMS" /><span class="data_checkbox_style"><i class="icon icon-check"></i></span>{l s='CMS' mod='ets_oneclicktomigrate'}</label>
                            </div>
                            <div class="checkbox">
    				            <label for="data_export_messages"><input{if in_array('messages',$ets_datamaster_export)} checked="checked"{/if} name="data_export[]" value="messages" type="checkbox" id="data_export_messages" /><span class="data_checkbox_style"><i class="icon icon-check"></i></span>{l s='Contact form messages' mod='ets_oneclicktomigrate'}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ybc-form-group ybc-blog-tab-step2{if $step==2} active{/if}">
                </div>
                <div class="ybc-form-group ybc-blog-tab-step3{if $step==3} active{/if}">
                </div>
                <div class="ybc-form-group ybc-blog-tab-step4{if $step==4} active{/if}">
                </div>
                <div class="popup_exporting">
                    <div class="popup_exporting_table">
                        <div class="popup_exporting_tablecell">
                            <div class="popup_exporting_content">
                                {l s='We are processing the data export. Please be patient and wait, do not close web browser! This process can take some minutes depends on your server speed and your website data size' mod='ets_oneclicktomigrate'}
                                <div class="export-wapper-all">
                                    <div class="export-wapper-percent"></div><samp class="percentage_export">1%</samp>
                                    <div class="noTrespassingOuterBarG">
                                    	<div class="noTrespassingAnimationG">
                                    		<div class="noTrespassingBarLineG"></div>
                                    		<div class="noTrespassingBarLineG"></div>
                                    		<div class="noTrespassingBarLineG"></div>
                                    		<div class="noTrespassingBarLineG"></div>
                                    		<div class="noTrespassingBarLineG"></div>
                                    		<div class="noTrespassingBarLineG"></div>
                                            <div class="noTrespassingBarLineG"></div>
                                            <div class="noTrespassingBarLineG"></div>
                                    		<div class="noTrespassingBarLineG"></div>
                                    		<div class="noTrespassingBarLineG"></div>
                                    		<div class="noTrespassingBarLineG"></div>
                                    		<div class="noTrespassingBarLineG"></div>
                                            <div class="noTrespassingBarLineG"></div>
                                            <div class="noTrespassingBarLineG"></div>
                                    		<div class="noTrespassingBarLineG"></div>
                                            <div class="noTrespassingBarLineG"></div>
                                            <div class="noTrespassingBarLineG"></div>
                                            <div class="noTrespassingBarLineG"></div>
                                            <div class="noTrespassingBarLineG"></div>
                                            <div class="noTrespassingBarLineG"></div>
                                            <div class="noTrespassingBarLineG"></div>
                    		                  <div class="noTrespassingBarLineG"></div>
                                    	</div>
                                    </div>
                                </div>
                                <samp class="percentage_export_table"></samp>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <button id="module_form_submit_btn" class="btn btn-default pull-right" name="submitExport" value="1" type="submit" {if $step==4} disabled="disabled"{/if}>
                    <i class="process-icon-next"></i>{l s='Next' mod='ets_oneclicktomigrate'}
                </button>
                <button id="module_form_submit_btn" class="btn btn-default pull-left" name="submitBack" value="1" type="submit"{if $step==1} disabled="disabled"{/if}>
                    <i class="process-icon-back"></i>{l s='Back' mod='ets_oneclicktomigrate'}
                </button>
                <div class="clearfix"> </div>
            </div>
        </div>
    </form>
</div>
<div class="dtm-clearfix"></div>
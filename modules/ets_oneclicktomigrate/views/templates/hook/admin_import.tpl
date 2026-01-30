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
var ETS_DT_MODULE_URL_AJAX ='';
</script>
<div class="dtm-right-block">
    <form id="module_form" action="index.php?tab=AdminModules&configure=ets_oneclicktomigrate&token={$token|escape:'html':'UTF-8'}&tab_module=front_office_features&module_name=ets_oneclicktomigrate&tabmodule=import" class="defaultForm form-horizontal" novalidate="" enctype="multipart/form-data" method="post" >
        <input type="hidden" value="{$step|intval}" name="step"/>
        <div id="fieldset_0" class="panel">
            <div class="panel-heading"><i class="icon-import"></i>{l s='Import data' mod='ets_oneclicktomigrate'}</div>
            <ul class="tab_step_data">
                <li class="data_number_step{if $step==1} active{/if}" data-step="1"><span>1</span>{l s='Source' mod='ets_oneclicktomigrate'}</li>
                 <li class="data_number_step{if $step==2} active{/if}" data-step="2"><span>2</span>{l s='Data to import' mod='ets_oneclicktomigrate'}</li>
                <li class="data_number_step{if $step==3} active{/if}" data-step="3"><span>3</span>{l s='Import option' mod='ets_oneclicktomigrate'}</li>
                <li class="data_number_step{if $step==4} active{/if}" data-step="4"><span>4</span>{l s='Review import' mod='ets_oneclicktomigrate'}</li>
                <li class="data_number_step{if $step==5} active{/if}" data-step="5"><span>5</span>{l s='Proceed importation' mod='ets_oneclicktomigrate'}</li>
            </ul>
            <div class="form-wrapper">
                <div class="ybc-form-group connector_error">
                    <div class="alert alert-warning error">
                        Sorry! Direct migration can’t be done because there was problems with server to server connection between the target website and the source website. This problem happens because one of the servers (or both servers) is not responding or timed out. <br />
                        Below are suggested solutions for the problem:
                        <ul class="suggested_solutions">
                            <li>
                           	    <p>Update server settings, make sure both servers are configured with <strong>UNLIMITED</strong> (or least 2 minutes) for <a target="_blank" href="http://php.net/manual/en/info.configuration.php#ini.max-execution-time">max_execution_timeand</a> they can execute an <strong>UNLIMITED</strong> number of continuous HTTP requests. Disable any firewall programs or server settings that stop the servers (or give500 Internal Server Error) when a large number of HTTP requests are sent to them during the migration.</p> 
                            </li>
                            <li>
                                <p>Try to download entire data of the source website using the <strong>Prestashop Connector module</strong>, then import the data into the target website with <strong>“Source Type”</strong> is set to <strong>“Upload data file from computer”</strong> </p>
                            </li>
                            <li>
                                <p>Try to copy your websites to a better server or local computer (give maximum server resource usability for the websites) then start the migration again. </p>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="ybc-form-group import_error">
                    <div class="alert alert-warning error">
                        Sorry! The server is not responding or timed out while importing data into the website database. This problem happens because the server is too <strong>LIMITED</strong> in resource usability.<br />
                        Below are suggested solutions for the problem:
                        <ul class="suggested_solutions">
                            <li>
                                <p>Update server settings, make sure both servers are configured with <strong>UNLIMITED</strong> (or least 2 minutes) for <a target="_blank" href="http://php.net/manual/en/info.configuration.php#ini.max-execution-time">max_execution_timeand</a> they can execute an <strong>UNLIMITED</strong> number of continuous HTTP requests. Disable any firewall programs or server settings that stop the servers (or give 500 Internal Server Error) when a large number of HTTP requests are sent to them during the migration. </p>
                            </li>
                            <li>
                                <p>Try to copy the target website to a better server or even  alocal computer (give maximum server resource usability for the website) then start the migration again. </p>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="ybc-form-group ybc-blog-tab-step1 {if $step==1} active{/if}">
                    {if isset($form_step1)}
                        {$form_step1 nofilter}
                    {else}
                        <div class="form-group">
                            <label class="control-label col-lg-4" for="source_type">{l s='Source type' mod='ets_oneclicktomigrate'}</label>
                            <div class="col-lg-8">
                                <select id="source_type" name="source_type">
                                    <option value="upload_file">{l s='Upload data file from computer' mod='ets_oneclicktomigrate'}</option>
                                    <option value="url_site">{l s='Use connector URL' mod='ets_oneclicktomigrate'}</option>
                                    <option value="link">{l s='Upload data file from URL' mod='ets_oneclicktomigrate'}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group source upload">
                            <label class="control-label col-lg-4" for="file_import">{l s='Select data file' mod='ets_oneclicktomigrate'}<span class="required">*</span></label>
                            <div class="col-lg-8">
                                <div class="data_upload_button_wrap">
                                    <input type="file" name="file_import" id="file_import"/>
                                    <div class="data_upload_button">
                                        <input readonly="true" type="text" name="data_upload_button" id="data_upload_button_input" value="{l s='No file selected.' mod='ets_oneclicktomigrate'}" />
                                        <div class="data_upload_button_right">
                                            <i class="fa fa-file"></i> {l s='Choose file' mod='ets_oneclicktomigrate'}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group source link">
                            <label class="control-label col-lg-4" for="link_file">{l s='Enter data file URL' mod='ets_oneclicktomigrate'}<span class="required">*</span></label>
                            <div class="col-lg-8">
                                <input type="text" name="link_file" id="link_file" placeholder="{l s='URL to .zip file' mod='ets_oneclicktomigrate'}" />
                            </div>
                        </div>
                        <div class="form-group source url_site">
                            <label class="control-label col-lg-4" for="link_site">{l s='Connector URL' mod='ets_oneclicktomigrate'}<span class="required">*</span></label>
                            <div class="col-lg-8">
                                <input type="text" style="width: 100%;" name="link_site" id="link_site" placeholder="{l s='Enter site URL (eg: http://yourwebsite.com/modules/ets_pres2presconnector/connector.php)' mod='ets_oneclicktomigrate'}" />
                            </div>
                        </div>
                        <div class="form-group source url_site">
                            <label class="control-label col-lg-4" for="secure_access_tocken">{l s='Secure access token' mod='ets_oneclicktomigrate'}<span class="required">*</span></label>
                            <div class="col-lg-8">
                                <input type="text" name="secure_access_tocken" id="secure_access_tocken" placeholder="{l s='Secure access token' mod='ets_oneclicktomigrate'}" />
                            </div>
                        </div>
                        <input type="hidden" id="link_site_connector" value="" name="link_site_connector" />
                        <p class="link_download_plugin alert alert-info">{l s='Please download' mod='ets_oneclicktomigrate'}&nbsp;<a href="{$mod_dr_onclickmigrate|escape:'html':'UTF-8'}plugins/ets_pres2presconnector.zip" target="_blank">{l s='Prestashop Connector' mod='ets_oneclicktomigrate'}</a>&nbsp;{l s=' module  and install the module on Source Prestashop website. This Connector module gives you the "Secure access token" (and data file) that is required above' mod='ets_oneclicktomigrate'}</p>
                    {/if}
                </div>
                <div class="ybc-form-group ybc-blog-tab-step2 {if $step==2} active{/if}">
                    {if $step >=2}
                        {$form_step2 nofilter}
                    {/if}
                </div>
                <div class="ybc-form-group ybc-blog-tab-step3 {if $step==3} active{/if}">
                    {if $step>=3}
                        {$form_step3 nofilter}
                    {/if}
                </div>
                <div class="ybc-form-group ybc-blog-tab-step4{if $step==4} active{/if}">
                    {if $step>=4}
                        {$form_step4 nofilter}
                    {/if}
                </div>
                <div class="ybc-form-group ybc-blog-tab-step5{if $step==5} active{/if}">
                    <p>{l s='The import has been successfully processed.' mod='ets_oneclicktomigrate'}</p>
                </div>
                <div class="popup_uploading">
                    <div class="popup_uploading_table">
                        <div class="popup_uploading_tablecell">
                            <div class="popup_uploading_content">
                                uploading data, please wait...!
                                <div class="upload-wapper-all">
                                    <div class="upload-wapper-percent" style="width:0%;"></div>
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
                                    	</div>
                                    </div>
                                </div>
                                <samp class="percentage_export_table"></samp>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="popup_importing">
                </div>
            </div>
            <div class="panel-footer">
                <button id="module_form_submit_btn" class="btn btn-default pull-right" name="submitImport" value="1" type="submit" {if $step==5} disabled="disabled"{/if}>
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
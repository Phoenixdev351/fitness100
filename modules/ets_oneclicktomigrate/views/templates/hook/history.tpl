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
<div class="dtm-left-block">
    {hook h='datamasterLeftBlok'}
</div>
<div class="dtm-right-block">
    <div class="dtm_history_tab_header">
        <span class="dtm_history_tab{if $tab_history=='import'} active{/if}" data-tab="import"><i class="icon icon-cloud-upload"> </i> {l s='Import' mod='ets_oneclicktomigrate'}</span>
        <span class="dtm_history_tab{if $tab_history=='export'} active{/if}" data-tab="export"><i class="icon icon-download"> </i> {l s='Export' mod='ets_oneclicktomigrate'}</span>
    </div>
    <div id="export_history" class="panel tab_content export {if $tab_history=='export'} active{/if}">
        {if $exports}
            <table class="export_history_content export">
                <tr>
                    <td>{l s='Export ID' mod='ets_oneclicktomigrate'}</td>
                    <td>{l s='Export details' mod='ets_oneclicktomigrate'}</td>
                    <td>{l s='Execution time' mod='ets_oneclicktomigrate'}</td>
                    <td>{l s='Action' mod='ets_oneclicktomigrate'}</td>
                </tr>
                {foreach from=$exports item='export'}
                    <tr>
                        <td>{$export.id_export_history|intval}</td>
                        <td>{$export.content nofilter}</td>
                        <td>{$export.date_export|escape:'html':'UTF-8'}</td>
                        <td>
                            <a target="_blank" href="{$url_cache|escape:'html':'UTF-8'}export/{$export.file_name|escape:'html':'UTF-8'}"><i class="icon icon-cloud-download"> </i> {l s='Download' mod='ets_oneclicktomigrate'}</a><br />
                            <a href="{$link->getAdminLink('AdminDataMasterHistory',true)|escape:'html':'UTF-8'}&deleteexporthistory&id_export_history={$export.id_export_history|intval}"><i class="icon icon-trash"> </i> {l s='Delete' mod='ets_oneclicktomigrate'}</a>
                        </td>
                    </tr>
                {/foreach}
            </table>
            {if $link_more && $exports|count >= $per_page}<div class="load_more export">
                <a class="load_more_export export btn btn-primary" href="{$link_more|cat:'&load_more=export' nofilter}" title="{l s='Load more' mod='ets_oneclicktomigrate'}">{l s='Load more' mod='ets_oneclicktomigrate'}</a>
            </div>{/if}
        {else}
            <div class="alert alert-warning no-have-history">{l s='Export history is empty' mod='ets_oneclicktomigrate'}</div>
        {/if}
    </div>
    <div id="import_history" class="panel tab_content import{if $tab_history=='import'} active{/if}">
        {if $imports}
            <table class="import_history_content import">
                <tr>
                    <td>{l s='Import ID' mod='ets_oneclicktomigrate'}</td>
                    <td>{l s='Import details' mod='ets_oneclicktomigrate'}</td>
                    <td>{l s='Execution time' mod='ets_oneclicktomigrate'}</td>
                    <td>{l s='Action' mod='ets_oneclicktomigrate'}</td>
                </tr>
                {foreach from=$imports item='import'}
                    <tr>
                        <td>{$import.id_import_history|intval}</td>
                        <td>
                            {$import.content nofilter}
                            {if $import.cookie_key && (in_array('customers',explode(',',$import.data)) || in_array('employees',explode(',',$import.data)))}
                                <br /><span class="cookie_key" >_COOKIE_KEY_ {l s='of source website' mod='ets_oneclicktomigrate'}: <span>{$import.cookie_key|escape:'html':'UTF-8'}</span>
                                <p>{l s='Install' mod='ets_oneclicktomigrate'} <a href="{$mod_dr_onclickmigrate|escape:'html':'UTF-8'}plugins/ets_pres2prespwkeeper.zip" target="_blank">{l s='"Prestashop Password Keeper"' mod='ets_oneclicktomigrate'}</a> {l s='to recover customer passwords' mod='ets_oneclicktomigrate'}</p>
                            {/if}
                        </td>
                        <td>{$import.date_import|escape:'html':'UTF-8'}</td>
                        <td>
                            {if $datamaster_import_last==$import.id_import_history&&!$import.import_ok}
                                <a href="{$link->getAdminLink('AdminDataMasterImport',true)|escape:'html':'UTF-8'}&resumeImport&id_import_history={$import.id_import_history|intval}"><i class="icon icon-undo"> </i> {l s='Resume import' mod='ets_oneclicktomigrate'}</a>
                            {/if}
                            <a href="{$link->getAdminLink('AdminDataMasterImport',true)|escape:'html':'UTF-8'}&restartImport&id_import_history={$import.id_import_history|intval}"><i class="icon icon-refresh"> </i> {l s='Restart import' mod='ets_oneclicktomigrate'}</a>
                            <a href="{$link->getAdminLink('AdminDataMasterHistory',true)|escape:'html':'UTF-8'}&deleteimporthistory&id_import_history={$import.id_import_history|intval}"><i class="icon icon-trash"> </i> {l s='Delete' mod='ets_oneclicktomigrate'}</a>
                            {if $import.new_passwd_customer}
                                <a href="{$link->getAdminLink('AdminDataMasterHistory',true)|escape:'html':'UTF-8'}&downloadpasscustomer&id_import_history={$import.id_import_history|intval}"><i class="icon icon-cloud-download"> </i>  {l s='Download new customer passwords' mod='ets_oneclicktomigrate'}</a>
                            {/if}
                            {if $import.new_passwd_employee}
                                <a href="{$link->getAdminLink('AdminDataMasterHistory',true)|escape:'html':'UTF-8'}&downloadpassemployee&id_import_history={$import.id_import_history|intval}"><i class="icon icon-cloud-download"> </i> {l s='Download new employee passwords' mod='ets_oneclicktomigrate'}</a>
                            {/if}
                        </td>
                    </tr>
                {/foreach}
            </table>
            {if $link_more && $imports|count >= $per_page}<div class="load_more import">
                <a class="load_more_import import btn btn-primary" href="{$link_more|cat:'&load_more=import' nofilter}" title="{l s='Load more' mod='ets_oneclicktomigrate'}">{l s='Load more' mod='ets_oneclicktomigrate'}</a>
            </div>{/if}
        {else}
            <div class="alert alert-warning no-have-history">{l s='Import history is empty' mod='ets_oneclicktomigrate'}</div>
        {/if}
    </div>
</div>
<div class="dtm-clearfix"></div>
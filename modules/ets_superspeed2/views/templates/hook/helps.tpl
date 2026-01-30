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
<div id="fieldset_0" class="panel">
    <div class="panel-heading">
        {l s='Helps' mod='ets_superspeed'}
    </div>
    <form>
        <div class="form-wrapper">
            <p>{l s='Thanks for using' mod='ets_superspeed'} <strong>{l s='Super Speed' mod='ets_superspeed'}</strong>, {l s='the most powerful speed optimization for Prestashop.' mod='ets_superspeed'}</p>    
            <p>{l s='Below are some points you should pay attention when using' mod='ets_superspeed'} <strong>{l s='Super Speed:' mod='ets_superspeed'}</strong></p>
            <ol>
                <li>{l s='Run "Auto configuration" from module\'s dashboard to quickly set everything up' mod='ets_superspeed'}.</li>
                <li>{l s='Contact us if you get into any troubles. We\'re happy to help, we will try to get back to you within 24 hours or as soon as possible' mod='ets_superspeed'}.</li>
                <li>{l s='Read' mod='ets_superspeed'} {l s='the module\'s user-guide document' mod='ets_superspeed'} {l s='carefully to understand how to use the module as well as find out solution for the problem you may meet when using' mod='ets_superspeed'} <strong>{l s='Super Speed' mod='ets_superspeed'}.</strong></li>
                <li>{l s='To make' mod='ets_superspeed'} <strong>{l s='Super Speed' mod='ets_superspeed'}</strong> {l s='works smoothly, please make sure you uninstall any other cache or image optimization modules (if there are any) on your website. Then reinstall' mod='ets_superspeed'} <strong>{l s='Super Speed' mod='ets_superspeed'}</strong> {l s='and configure all necessary settings in the module back office.' mod='ets_superspeed'} <strong>{l s='Super Speed' mod='ets_superspeed'}</strong> {l s='comes with all speed optimization features you need such as page cache, browser cache, server cache, image optimization, etc. You don\'t need any other ones anymore, go ahead to remove them' mod='ets_superspeed'}.</li>
                <li>{l s='When you enable' mod='ets_superspeed'} <strong>"{l s='Page Cache' mod='ets_superspeed'}",</strong> {l s='we recommend you to recheck the front office of your website to make sure all features working as they should be. Especially, carefully check features working in jQueryajax or features based on JavaScript such as' mod='ets_superspeed'} <i>{l s='add to cart button, ordering process, user registration process, etc.' mod='ets_superspeed'}</i> {l s='If any of the features are not working well, disable' mod='ets_superspeed'} <strong>"{l s='Page cache' mod='ets_superspeed'}",</strong> {l s='contact us for help if you can\'t solve the problem yourself.' mod='ets_superspeed'}</li>
                <li><strong>{l s='Super Speed' mod='ets_superspeed'}</strong> {l s='makes your website faster than ever for sure as its name. It also helps raise your speed score on web speed on most common testing tools such as' mod='ets_superspeed'} <a href="https://developers.google.com/speed/pagespeed/insights/" target="_blank" rel="noreferrer noopener">{l s='Google page speed insight' mod='ets_superspeed'}</a>, <a href="https://gtmetrix.com/" target="_blank" rel="noreferrer noopener">{l s='GTmetrix' mod='ets_superspeed'}</a>, {l s='etc. But it doesn\'t guarantee that your website will get highest scores as the scores depend on many other things that are not controlled by' mod='ets_superspeed'} <strong>{l s='Super Speed' mod='ets_superspeed'}</strong> <i>{l s='such as your server processing speed, your server\'s network speed, your website page content optimization, etc.' mod='ets_superspeed'}</i> {l s='So please understand!' mod='ets_superspeed'}</li>
                <li>{l s='Setup a cronjob on your server as below to automatically clean expired page caches. It\'s recommended to configure the cronjob to be executed once per hour.' mod='ets_superspeed'}
                    <div class="clearfix"></div>
                    <p>
                        <label for="ETS_SPEED_SUPER_TOCKEN">{l s='Cronjob secure token:' mod='ets_superspeed'} <input id="ETS_SPEED_SUPER_TOCKEN" type="text" name="ETS_SPEED_SUPER_TOCKEN" value="{$ETS_SPEED_SUPER_TOCKEN|escape:'html':'UTF-8'}" /></label>
                        <button type="button" class="update_tocken_sp">{l s='Update' mod='ets_superspeed'}</button>
                    </p>
                    <div class="clearfix"></div>
                    <p><span class="cronjob-command-label">Cronjob command: </span><span class="bg_greylight"><i>0 * * * * {$php_path|escape:'html':'UTF-8'} {$dir_cronjob|escape:'html':'UTF-8'} token=<span class="tocken_value">{$ETS_SPEED_SUPER_TOCKEN|escape:'html':'UTF-8'}</span></i></span></p>
                    <p>
                        <a href="{$link_cronjob_run|escape:'html':'UTF-8'}" class="btn btn-default custom_cronjob run_auto_cache">
                            <i class="fa fa-hand-pointer-o"></i> {l s='Execute cronjob manually' mod='ets_superspeed'}
                        </a>
                    </p>
                    <div class="cronjob_last_box">
                        {if $cronjob_last}
                            <div class="alert alert-info">
                                {l s='Last time cronjob executed: ' mod='ets_superspeed'}{$cronjob_last|escape:'html':'UTF-8'}
                            </div>
                        {else}
                            <div class="alert alert-warning cronjob">{l s='Cronjob didn\'t run in the last 24 hours. Please check again cronjob configuration to make sure Cronjob run correctly' mod='ets_superspeed'}</div>
                        {/if}
                    </div>
                    <div class="clearfix"></div>
                </li>
            </ol> 
        </div>
    </form>
</div>
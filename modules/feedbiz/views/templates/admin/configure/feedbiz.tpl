{**
* NOTICE OF LICENSE
*
* This source file is subject to a commercial license from Feed.Biz, Ltd.
* Use, copy, modification or distribution of this source file without written
* license agreement from Feed.Biz, Ltd. is strictly forbidden.
* In order to obtain a license, please contact us: contact@feed.biz
* ...........................................................................
* INFORMATION SUR LA LICENCE D'UTILISATION
*
* L'utilisation de ce fichier source est soumise a une licence commerciale
* concedee par la societe Feed.Biz, Ltd.
* Toute utilisation, reproduction, modification ou distribution du present
* fichier source sans contrat de licence ecrit de la part de la Feed.Biz, Ltd. est
* expressement interdite.
* Pour obtenir une licence, veuillez contacter Feed.Biz, Ltd. a l'adresse: contact@feed.biz
* ...........................................................................
* @package    Feed.Biz
* @author     Olivier B.
* @copyright  Copyright (c) 2011-2022 Feed.Biz - Hong Kong - Head Quarters - Room 1408, 14/F, Tak Shing House - Theatre Lane - 20 des Voeux Road Central, -Central, Hong Kong
* @license    Commercial license
* Support by mail  :  support@feed.biz
*}

<!-- body start -->
<input type="hidden" id="css_class_info" value="{$alert_class.info|escape:'htmlall':'UTF-8'}"/>
<input type="hidden" id="css_class_warn" value="{$alert_class.warn|escape:'htmlall':'UTF-8'}"/>
<input type="hidden" id="css_class_error" value="{$alert_class.danger|escape:'htmlall':'UTF-8'}"/>
<input type="hidden" id="css_class_success" value="{$alert_class.success|escape:'htmlall':'UTF-8'}"/>

<div style="clear:both"></div>
<fieldset>
  <div id="tabList">

    {if ($is_cdiscount && $mode_use != 'SIMPLE') || ($is_feedbiz && $feedbiz_mode == 'EXPERT')}
        <form action="{$request_uri|escape:'quotes':'UTF-8'}" id="feedbiz_form" method="post" autocomplete="off">
          <input type="hidden" id="selected_tab" name="selected_tab" value="{$selected_tab|escape:'htmlall':'UTF-8'}"/>

          <!-- INFORMATIONS START -->
          <div id="menudiv-feedbiz" class="panel form-horizontal" style="{*display:none;*}">
            <div class="form-group">
              <h2 class="col-lg-3">{$module_display_name|escape:'htmlall':'UTF-8'} v{$version|escape:'htmlall':'UTF-8'}</h2>
            </div>
            <div class="clearfix">&nbsp;</div>
            <table style="width: 100%" border="0" class="teaser">
              <tbody>
                <tr>
                  {if $is_cdiscount}
                      <td colspan="2" rowspan="1" class="title">{l s='Succeed easily on Cdiscount with Cdiscount Flux in four steps' mod='feedbiz'}:</td>
                  {else}
                      <td colspan="2" rowspan="1" class="title">{l s='Succeed easily on marketplaces with Feed.biz in three steps' mod='feedbiz'}:</td>
                  {/if}
                </tr>
                <tr> <!-- STEP 1 -->
                  <td style="width: 35px;"><img style="width: 130px; height: 130px;" src="{$images_url|escape:'quotes':'UTF-8'}bullet1.png" alt=""></td>

                  {if $is_cdiscount}
                      <td style="width: 1370.58px; top: 12px;" class="bullet"><a target="_blank" title="{l s='Create your Cdiscount Flux account' mod='feedbiz'}" href="https://cdiscount.feed.biz/users/register/french?channel=cdiscount">{l s='Create your Cdiscount Flux account' mod='feedbiz'} {l s='par Feed.biz' mod='feedbiz'}</a></td>
                  {else}
                      <td style="width: 1370.58px; top: 12px;" class="bullet"><a target="_blank" title="{l s='Create your Feed.biz account' mod='feedbiz'}" href="https://client.feed.biz/users/register">{l s='Create your Feed.biz account' mod='feedbiz'}</a></td>
                      {/if}
                </tr>
                <tr> <!-- STEP 2 -->
                  <td style="width: 35px;"><img style="width: 130px; height: 130px;" src="{$images_url|escape:'quotes':'UTF-8'}bullet2.png" alt=""></td>

                  <td  class="bullet" style="top: 50px;">{l s='Follow the connection wizard to your shop, paste this URL' mod='feedbiz'}:

                    <div class="form-group" style="margin-top: 7px;">

                      <label class="control-label col-lg-1">{l s='%s URL' sprintf=[$module_display_name] mod='feedbiz'}</label>

                      <div class="margin-form col-lg-9">
                        <input id="text-copy-to-clipboard" type="text"
                               style="color:grey;background-color:#fdfdfd;width:600px;"
                               value="{$feedbiz_informations.feedbiz_xml_url|escape:'quotes':'UTF-8'}" disabled/>

                        <input id="hdd-copy-to-clipboard" type="hidden"
                               style="color:grey;background-color:#fdfdfd;width:600px;margin-top:5px;"
                               value="{$feedbiz_informations.feedbiz_xml_url|escape:'quotes':'UTF-8'}"/>

                        <input id="msg-success-copy-to-clipboard" type="hidden" value="{l s='URL has been successfully copied to clipboard.' mod='feedbiz'}">
                        <input id="msg-error-copy-to-clipboard"  type="hidden" value="{l s='URL could not be copied to the clipboard, try again or select the URL and use Ctrl + C.' mod='feedbiz'}">

                        <input style="margin-left: 0px; margin-top: 7px;" id="button-copy-to-clipboard" type="button" class="button btn btn-default"
                               value="{l s='Copy to Clipboard' mod='feedbiz'}"/>

                        <br>
                        <label class="control-label">{l s='URL to provide to %s. This is the target XML generator containing the product and offer.' sprintf=[$module_display_name, $module_display_name] mod='feedbiz'}</label>
                      </div>

                    </div>
                  </td>
                </tr>

                {if $is_cdiscount}
                    <tr> <!-- STEP 3 -->
                      <td style="width: 35px;"><img style="width: 130px; height: 130px;" src="{$images_url|escape:'quotes':'UTF-8'}bullet3.png" alt=""></td>
                      <td class="bullet"><a href="https://seller.cdiscount.com/Account_creation.html?referrer=CDS" target="_blank" title="{l s='Create your Cdiscount account' mod='feedbiz'}">{l s='Create your Cdiscount account' mod='feedbiz'}</a> {l s='with the promotion code' mod='feedbiz'}*: <b style="color: #E23A05;">CDISCOUNTFLUX</b></td>
                    </tr>
                    <tr> <!-- STEP 4 -->
                      <td style="width: 35px;"><img style="width: 130px; height: 130px;" src="{$images_url|escape:'quotes':'UTF-8'}bullet4.png" alt=""></td>
                      <td class="bullet">{l s='Start selling on Cdiscount' mod='feedbiz'}</td>
                    </tr>
                {else}
                    <tr> <!-- STEP 3 -->
                      <td style="width: 35px;"><img style="width: 130px; height: 130px;" src="{$images_url|escape:'quotes':'UTF-8'}bullet3.png" alt=""></td>
                      <td class="bullet">{l s='Start selling on marketplaces' mod='feedbiz'} <br> </td>
                    </tr>
                {/if}

              </tbody>
            </table>
            <br>

            {if $is_cdiscount}
                <div class="panel-footer">
                  <a href="{$request_uri|escape:'quotes':'UTF-8'}&is_export=0" class="btn btn-default pull-right"><i class="process-icon-configure"></i> {l s='Simple Mode' mod='feedbiz'}</a>
                </div>
            {/if}

            {if $is_feedbiz}
                <div class="panel-footer">
                  <a href="{$request_uri|escape:'quotes':'UTF-8'}&feedbiz_mode=0" class="btn btn-default pull-right"><i class="process-icon-configure"></i> {l s='Simple Mode' mod='feedbiz'}</a>
                </div>
            {/if}

          </div>

          <!-- INFORMATIONS START -->
          <div id="menudiv-informations" class="panel form-horizontal" style="display:none;">

            <h2>{l s='Informations' mod='feedbiz'}</h2>
            <div class="form-group">
              <label class="control-label col-lg-3">{$module_display_name|escape:'htmlall':'UTF-8'} v{$version|escape:'htmlall':'UTF-8'}</label>

              <div class="margin-form col-lg-9">
                <span style="color:navy">{l s='This module is provided by' mod='feedbiz'} :</span>
                {if $is_cdiscount}
                    Cdiscount Flux, Feed.Biz Ltd., Feed.Biz Inc. US, <a href="https://cdiscount.feed.biz" target="_blank">https://cdiscount.feed.biz</a>
                {else}
                    Feed.Biz Ltd., Feed.Biz Inc. US, <a href="https://feed.biz" target="_blank">https://feed.biz</a>
                {/if}
                <br>
                <span style="color:navy">{l s='Informations, follow up on our blog' mod='feedbiz'} :</span>
                {if $is_cdiscount}
                    <a href="http://marketplace.cdiscount.com/blog/" target="_blank">http://marketplace.cdiscount.com/blog/</a><br>
                {else}
                    <a href="http://blog.feed.biz" target="_blank">http://blog.feed.biz</a><br>
                {/if}
              </div>
            </div>
            <br>

            <div class="form-group">
              <label class="control-label col-lg-3">{l s='Documentation' mod='feedbiz'}</label>
              <div class="margin-form col-lg-9">
                <div class="col-lg-1"><img src="{$images_url|escape:'quotes':'UTF-8'}books.png" alt="docs"/>
                </div>
                <div class="col-lg-11">
                  <span style="color:red; font-weight:bold;">{l s='Please first read the online documentation' mod='feedbiz'} :</span><br>
                  {if $is_cdiscount}
                      <a href="https://documentation.feed.biz/cdiscount-documentation/" target="_blank">https://documentation.feed.biz/cdiscount-documentation/</a><br>
                  {else}
                      <a href="http://documentation.feed.biz/" target="_blank">http://documentation.feed.biz/</a>
                  {/if}
                </div>
              </div>
            </div>
            <br>

            <div class="form-group">
              <label class="control-label col-lg-3">{l s='Support' mod='feedbiz'}</label>

              <div class="margin-form col-lg-9">
                <div class="col-lg-1"><img src="{$images_url|escape:'quotes':'UTF-8'}submit_support_request.png" alt="support"></div>
                <div class="col-lg-11">
                  <span style="color:red; font-weight:bold;">
                    {l s='The technical support is available by e-mail only.' mod='feedbiz'}
                  </span><br>
                  <span style="color: navy;">
                    {l s='For any support, please provide us' mod='feedbiz'} :<br>
                  </span>
                  <ul>
                    <li>{l s='A detailled description of the issue or encountered problem' mod='feedbiz'}</li>
                    <li>{l s='Your Pretashop Addons Order ID available in your Prestashop Addons order history' mod='feedbiz'}</li>
                    <li>{l s='Your Prestashop version' mod='feedbiz'} : <span style="color: red;">Prestashop {$ps_version|escape:'htmlall':'UTF-8'}</span>
                    </li>
                    <li>{l s='Your module version' mod='feedbiz'} : <span
                          style="color: red;">{$module_display_name|escape:'htmlall':'UTF-8'} v{$version|escape:'htmlall':'UTF-8'}</span>
                    </li>
                  </ul>
                  <br>
                  <span style="color:navy">{l s='%s Support' sprintf=[$module_display_name] mod='feedbiz'} :</span>
                  {if $is_cdiscount}
                      <a href="mailto:support@feed.biz?subject={l s='Support for Cdiscount Flux' mod='feedbiz'}&body={l s='Dear Support, I am currently having some trouble with your module v%s on my Prestashop v%s.' sprintf=[$version, $ps_version] mod='feedbiz'}">
                        support@feed.biz
                      </a>
                  {else}
                      <a href="mailto:support@feed.biz?subject={l s='Support for Feed.Biz' mod='feedbiz'}&body={l s='Dear Support, I am currently having some trouble with your module v%s on my Prestashop v%s.' sprintf=[$version, $ps_version] mod='feedbiz'}">
                        support@feed.biz
                      </a>
                  {/if}
                </div>
              </div>
            </div>

            <h2>{l s='Configuration Check' mod='feedbiz'}</h2>

            <div class="form-group">
              <label class="control-label col-lg-3">{l s='PHP Settings' mod='feedbiz'}</label>

              <div align="left" class="margin-form col-lg-9">
                {if ! $feedbiz_informations.php_info_ok}
                    {foreach from=$feedbiz_informations.php_infos item=php_info}
                        <p class="{$alert_class[$php_info.level]|escape:'htmlall':'UTF-8'}">
                          {$php_info.message|escape:'htmlall':'UTF-8'}
                          {if isset($php_info.link)}
                              <br/>
                              <span class="me-info-link">{l s='Please read more about it on:' mod='feedbiz'}: <a
                                    href="{$php_info.link|escape:'quotes':'UTF-8'}" target="_blank">{$php_info.link|escape:'quotes':'UTF-8'}</a></span>
                              {/if}
                        <hr style="width:30%"/>
                        </p>
                    {/foreach}
                {else}
                    <p class="{$alert_class.success|escape:'htmlall':'UTF-8'}">
                      {l s='Your PHP configuration for the module has been checked and passed successfully...' mod='feedbiz'}
                    <hr style="width:30%"/>
                    </p>
                {/if}
              </div>
            </div>


            <h2>{l s='Environment' mod='feedbiz'}</h2>

            <input type="hidden" id="max_input_vars" value="{$feedbiz_informations.max_input_vars|intval}"/>

            <div id="fb-env-infos" class="form-group" style="display:none;">
              <label class="control-label col-lg-3">{l s='Environment' mod='feedbiz'}</label>

              <div align="left" class="margin-form fb-info col-lg-9">
                {if $feedbiz_informations.env_infos}
                    {foreach from=$feedbiz_informations.env_infos key=env_name item=env_info}
                        <div class="{$env_info.level|escape:'quotes':'UTF-8'}"
                             id="error-{$env_info.script.name|escape:'quotes':'UTF-8'}"
                             {if !$env_info.display}style="display:none;" {else}rel="toshow"{/if}>
                          {if isset($env_info.script.url)}
                              <!-- script URL -->
                              <input type="hidden" id="{$env_info.script.name|escape:'htmlall':'UTF-8'}"
                                     value="{$env_info.script.url|escape:'quotes':'UTF-8'}"
                                     rel="{$env_name|escape:'htmlall':'UTF-8'}"/>
                          {/if}
                          <p>
                            <span>{$env_info.message|escape:'html':'UTF-8'}</span>
                            {if isset($env_info.tutorial)}
                                <br/>
                            <pre>{l s='Please read more about it on:' mod='feedbiz'} {$env_info.tutorial|escape:'quotes':'UTF-8'}</pre>
                          {/if}
                          </p>
                        </div>
                    {/foreach}
                {/if}
              </div>
            </div>

            <div class="form-group">
              <label class="control-label col-lg-3">{l s='File Permissions' mod='feedbiz'}</label>

              <div align="left" class="margin-form col-lg-9">
                {if isset($feedbiz_informations.filespermissions) && !empty($feedbiz_informations.filespermissions)}
                    <div class="{$alert_class.danger|escape:'htmlall':'UTF-8'}">
                      {foreach from=$feedbiz_informations.filespermissions item=files_permissions}
                          <p>{$files_permissions|escape:'htmlall':'UTF-8'}</p>
                      {/foreach}
                    </div>
                {else}
                    <p class="{$alert_class.success|escape:'htmlall':'UTF-8'}">
                      {l s='Your file write permissions for the module has been checked and passed successfully...' mod='feedbiz'}
                    </p>
                {/if}
              </div>
            </div>

            <div class="form-group">
              <label class="control-label col-lg-3">{l s='Prestashop Settings' mod='feedbiz'}</label>
              <div align="left" class="margin-form col-lg-9">
                {if ! $feedbiz_informations.prestashop_info_ok}
                    {foreach from=$feedbiz_informations.prestashop_infos item=prestashop_info}
                        <p class="me-info-level-{$prestashop_info.level|escape:'htmlall':'UTF-8'}">
                          <span class="me-info-text-{$prestashop_info.level|escape:'htmlall':'UTF-8'}">{$prestashop_info.message|escape:'htmlall':'UTF-8'}</span>
                          {if isset($prestashop_info.link)}
                              <br/>
                              <span class="me-info-link">{l s='Please read more about it on:' mod='feedbiz'}: <a
                                    href="{$prestashop_info.link|escape:'quotes':'UTF-8'}"
                                    target="_blank">{$prestashop_info.link|escape:'quotes':'UTF-8'}</a></span>
                              {/if}
                        </p>
                    {/foreach}
                {else}
                    <p class="{$alert_class.success|escape:'htmlall':'UTF-8'}">
                      {l s='Your Prestashop configuration for the module has been checked and passed successfully...' mod='feedbiz'}
                    </p>
                {/if}
              </div>
            </div>

            <h2>{l s='Additionnal Support Informations' mod='feedbiz'}</h2>
            <br/>

            <div class="form-group">
              <label class="control-label col-lg-3">&nbsp;</label><br/>

              <div align="left" class="margin-form amz-info col-lg-9">
                <input type="button" class="button btn" id="support-informations-prestashop"
                       value="{l s='Prestashop Info' mod='feedbiz'}"
                       rel="{$feedbiz_informations.support_informations_url|escape:'htmlall':'UTF-8'}&action=prestashop-info"/>&nbsp;&nbsp;
                <input type="button" class="button btn" id="support-informations-php"
                       value="{l s='PHP Info' mod='feedbiz'}"
                       rel="{$feedbiz_informations.support_informations_url|escape:'htmlall':'UTF-8'}&action=php-info"/>&nbsp;&nbsp;
		<span rel="feedbiz-expert-mode" class="feedbiz-expert-mode">
		    <input type="hidden" id="mode_dev-status" value="{if !$feedbiz_informations.mode_dev}1{else}0{/if}"/>
		    <input type="hidden" id="mode_dev-status-on" value="{l s='Switch On DEV_MODE' mod='feedbiz'}"/>
		    <input type="hidden" id="mode_dev-status-off" value="{l s='Switch Off DEV_MODE' mod='feedbiz'}"/>
		    <input type="button" class="button btn" id="support-mode_dev"
			   {if !$feedbiz_informations.mode_dev}value="{l s='Switch On DEV_MODE' mod='feedbiz'}"
			   {else}value="{l s='Switch Off DEV_MODE' mod='feedbiz'}"{/if}
			   rel="{$feedbiz_informations.support_informations_url|escape:'quotes':'UTF-8'}&action=mode-dev"/>&nbsp;&nbsp;
		</span><!--mode_dev-->
		
                <img src="{$feedbiz_informations.img|escape:'htmlall':'UTF-8'}loading.gif"
                     alt="{l s='Support Informations' mod='feedbiz'}" class="support-informations-loader"/><br/><br/>

		<div id="devmode-response">
		    <div id="devmode-response-success" class="{$alert_class.success|escape:'htmlall':'UTF-8'}"
			 style="display: none;"></div>
		    <div id="devmode-response-danger" class="{$alert_class.danger|escape:'htmlall':'UTF-8'}"
			 style="display: none;"></div>
		</div><!--mode_dev response-->
		
		{if $feedbiz_informations.mode_dev}
		    <div id="devmode-open" class="{$alert_class.warn|escape:'htmlall':'UTF-8'}">
			{l s='DEV_MODE is enabled, to disable click \'Switch Off DEV_MODE\' button' mod='feedbiz'}
		    </div>
		{/if} <!--mode_dev-->
		
                <div id="support-informations-content"></div>
              </div>
            </div>

            <div class="form-group">
              <label class="control-label col-lg-3">{l s='Licence' mod='feedbiz'}</label>

              <div class="margin-form col-lg-9">
                <p>
                  {l s='This module is subject to a commercial license from' mod='feedbiz'}
                  {if $is_cdiscount}
                      Cdiscount Flux,
                  {/if}
                  Feed.biz.<br/>
                  {l s='To obtain a license, please contact us:' mod='feedbiz'}
                  {if $is_cdiscount}
                      <a href="mailto:support-cdiscount@feed.biz">support-cdiscount@feed.biz</a>
                  {else}
                      <a href="mailto:contact@feed.biz">contact@feed.biz</a>
                  {/if}
                  <br/>
                  {l s='In case of acquisition on Prestashop Addons, the invoice itself is a proof of license' mod='feedbiz'}
                  <br/>
                </p>
              </div>
            </div>

            {include file="$module_path/views/templates/admin/configure/validate.tpl"}
          </div>
          <!-- INFORMATIONS END -->

          <!--CATEGORIES START -->
          <div id="menudiv-categories" class="panel form-horizontal" style="display:none;">
            <h2>{l s='Categories' mod='feedbiz'}</h2>
            <div class="form-group">
              <div class="col-lg-2">
              </div>
              <div class="col-lg-8">
                <table cellspacing="0" cellpadding="0" width="100%" class="table">
                  <tr>
                    <th style="width:5%"><input type="checkbox" name="checkme"/></th>
                    <th style="width:95%">{l s='Name' mod='feedbiz'}</th>
                  </tr>
                  {if isset($feedbiz_categories) && is_array($feedbiz_categories.list) && count($feedbiz_categories.list)}
                      {foreach $feedbiz_categories.list as $id_category => $details}
                          <tr class="cat-line{($details.alt_row|intval) ? ' alt_row' : ''}">
                            <td>
                              {if !$details.disabled}<input type="checkbox" name="category[]" class="category{($details.id_category_default|intval == $id_category|intval) ? ' id_category_default' : ''}" id="category_{$id_category|intval}" value="{$id_category|intval}" {$details.checked|escape:'htmlall':'UTF-8'} data-parent="{if ($details.id_parent)}{$details.id_parent|intval}{else}''{/if}" id="category_{$id_category|intval}"/>{/if}
                            </td>
                            <td style="cursor:pointer">
                              <img src="{$details.img_level|escape:'htmlall':'UTF-8'}" alt="" /> &nbsp;<label for="category_{$id_category|intval}" class="t">{$details.name|escape:'htmlall':'UTF-8'}</label>
                            </td>
                          </tr>
                      {/foreach}
                  {else}
                      <tr>
                        <td colspan="2">
                          {l s='No category were found.' mod='feedbiz'}
                        </td>
                      </tr>
                  {/if}
                </table>
                <p>{l s='Select the categories you want to export.' sprintf=[$module_display_name] mod='feedbiz'}<br/></p>
              </div>
            </div>
            {include file="$module_path/views/templates/admin/configure/validate.tpl"}
          </div>
          <!--CATEGORIES END-->

          <!--ORDERS START -->
          <div id="menudiv-orders" class="panel form-horizontal" style="display:none;">
            <h2>{l s='Orders Statuses' mod='feedbiz'}</h2>
            <div class="form-group">
              <label class="control-label col-lg-3"></label>
              <div class="margin-form col-lg-9">
                <select name="orderstate[FEEDBIZ_CA]" style="width:500px;">
                  <option value="">{l s='Choose a default incoming order status for Feed.biz' mod='feedbiz'}</option>
                  {foreach from=$feedbiz_orders.feedbiz_mapping_order_states_01 item=feedbiz_mapping_state}
                      <option value="{$feedbiz_mapping_state.value|escape:'htmlall':'UTF-8'}" {$feedbiz_mapping_state.selected|escape:'htmlall':'UTF-8'}>{$feedbiz_mapping_state.desc|escape:'htmlall':'UTF-8'}</option>
                  {/foreach}
                </select>

                <p>{l s='Choose the default order state for new incoming orders' mod='feedbiz'}</p>
              </div>

              {if is_array($feedbiz_orders.feedbiz_mapping_order_states_mc) && count($feedbiz_orders.feedbiz_mapping_order_states_mc)}
                  <label class="control-label col-lg-3"></label>

                  <div class="margin-form col-lg-9">
                    <select name="orderstate[FEEDBIZ_MC]" style="width:500px;">
                      <option value="">{l s='Choose a default status for incoming multichannel orders' mod='feedbiz'}</option>
                      {foreach from=$feedbiz_orders.feedbiz_mapping_order_states_mc item=feedbiz_mapping_state}
                          <option value="{$feedbiz_mapping_state.value|escape:'htmlall':'UTF-8'}" {$feedbiz_mapping_state.selected|escape:'htmlall':'UTF-8'}>{$feedbiz_mapping_state.desc|escape:'htmlall':'UTF-8'}</option>
                      {/foreach}
                    </select>

                    <p>{l s='Choose a default status for incoming multichannel orders' mod='feedbiz'}</p>
                  </div>
              {/if}

              {if is_array($feedbiz_orders.feedbiz_mapping_order_states_fba) && count($feedbiz_orders.feedbiz_mapping_order_states_fba)}
                  <label class="control-label col-lg-3"></label>

                  <div class="margin-form col-lg-9">
                    <select name="orderstate[FEEDBIZ_FBA]" style="width:500px;">
                      <option value="">{l s='Choose a default status for Amazon FBA/Cdiscount clogistique orders' mod='feedbiz'}</option>
                      {foreach from=$feedbiz_orders.feedbiz_mapping_order_states_fba item=feedbiz_mapping_state}
                          <option value="{$feedbiz_mapping_state.value|escape:'htmlall':'UTF-8'}" {$feedbiz_mapping_state.selected|escape:'htmlall':'UTF-8'}>{$feedbiz_mapping_state.desc|escape:'htmlall':'UTF-8'}</option>
                      {/foreach}
                    </select>

                    <p>{l s='Choose a default status for Amazon FBA orders' mod='feedbiz'}</p>
                  </div>
              {/if}

              <label class="control-label col-lg-3"></label>

              <div class="margin-form col-lg-9">
                <select name="orderstate[FEEDBIZ_CE]" style="width:500px;">
                  <option value="">{l s='Choose a default sent order status for Feed.biz' mod='feedbiz'}</option>
                  {foreach from=$feedbiz_orders.feedbiz_mapping_order_states_02 item=feedbiz_mapping_state}
                      <option value="{$feedbiz_mapping_state.value|escape:'htmlall':'UTF-8'}" {$feedbiz_mapping_state.selected|escape:'htmlall':'UTF-8'}>{$feedbiz_mapping_state.desc|escape:'htmlall':'UTF-8'}</option>
                  {/foreach}
                </select>

                <p>{l s='Choose the default order state for sent orders' mod='feedbiz'}</p>
              </div>

              <label class="control-label col-lg-3"></label>

              <div class="margin-form col-lg-9">
                <select name="orderstate[FEEDBIZ_CL]" style="width:500px;">
                  <option value="">{l s='Choose a default sent order status for Feed.biz' mod='feedbiz'}</option>
                  {foreach from=$feedbiz_orders.feedbiz_mapping_order_states_03 item=feedbiz_mapping_state}
                      <option value="{$feedbiz_mapping_state.value|escape:'htmlall':'UTF-8'}" {$feedbiz_mapping_state.selected|escape:'htmlall':'UTF-8'}>{$feedbiz_mapping_state.desc|escape:'htmlall':'UTF-8'}</option>
                  {/foreach}
                </select>

                <p>{l s='Choose the default order state for delivered orders' mod='feedbiz'}</p>
              </div>

              <label class="control-label col-lg-3"></label>

              <div class="margin-form col-lg-9">
                <select name="orderstate[FEEDBIZ_CR]" style="width:500px;">
                  <option value="">{l s='Choose a default canceled order status for Feed.biz' mod='feedbiz'}</option>
                  {foreach from=$feedbiz_orders.feedbiz_mapping_order_states_04 item=feedbiz_mapping_state}
                      <option value="{$feedbiz_mapping_state.value|escape:'htmlall':'UTF-8'}" {$feedbiz_mapping_state.selected|escape:'htmlall':'UTF-8'}>{$feedbiz_mapping_state.desc|escape:'htmlall':'UTF-8'}</option>
                  {/foreach}
                </select>

                <p>{l s='Choose the default order state for canceled orders' mod='feedbiz'}</p>
              </div>

              <label class="control-label col-lg-3"></label>

              <div class="margin-form col-lg-9">
                <select name="orderstate[FEEDBIZ_UR]" style="width:500px;">
                  <option value="">{l s='Choose a default urgent order status for Feed.biz' mod='feedbiz'}</option>
                  {foreach from=$feedbiz_orders.feedbiz_mapping_order_states_ur item=feedbiz_mapping_state}
                      <option value="{$feedbiz_mapping_state.value|escape:'htmlall':'UTF-8'}" {$feedbiz_mapping_state.selected|escape:'htmlall':'UTF-8'}>{$feedbiz_mapping_state.desc|escape:'htmlall':'UTF-8'}</option>
                  {/foreach}
                </select>

                <p>{l s='Choose the default order state for urgent orders' mod='feedbiz'}</p>
              </div>

            </div>

            <h2>{l s='Order Import' mod='feedbiz'}</h2>

            <div class="form-group">
              <label class="control-label col-lg-3">{l s='Force Import' mod='feedbiz'}</label>

              <div class="margin-form col-lg-9">
                <input id="forceimport" type="checkbox" name="forceimport" value="1"
                       style="position:relative;top:+1px" {$feedbiz_orders.feedbiz_forceimport|escape:'htmlall':'UTF-8'}/>
                <span style="font-size:1.2em;">&nbsp;&nbsp;{l s='Yes' mod='feedbiz'}</span>

                <p> {l s='Allow to import order when product is out of stock.' mod='feedbiz'}</p>
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-lg-3">{l s='Create Product' mod='feedbiz'}</label>

              <div class="margin-form col-lg-9">
                <input id="auto_create" type="checkbox" name="auto_create" value="1" 
                       style="position:relative;top:+1px" {$feedbiz_orders.feedbiz_auto_create|escape:'htmlall':'UTF-8'}/>
                <span style="font-size:1.2em;">&nbsp;&nbsp;{l s='Yes' mod='feedbiz'}</span>

                <p> {l s='Allow to create product when SKU/Reference not found in your database.' mod='feedbiz'}</p>
              </div>
            </div>
            {include file="$module_path/views/templates/admin/configure/validate.tpl"}
          </div>
          <!--ORDERS END -->

          <!--SETTINGS START -->
          <div id="menudiv-settings" class="panel form-horizontal" style="display:none;">
            <h2>{l s='Settings' mod='feedbiz'}</h2>

            <div class="form-group">
              <label class="control-label col-lg-3">{l s='Discount/Specials' mod='feedbiz'}</label>

              <div class="margin-form  col-lg-9">
                <input type="checkbox" name="specials"
                       value="1" {$feedbiz_settings.specials|escape:'htmlall':'UTF-8'} />&nbsp;
                <span style="color:black;font-size:12px;">&nbsp;{l s='Yes' mod='feedbiz'}</span>

                <p>{l s='Export specials prices if is sets Yes. If unsets the discounted prices will be ignorates' mod='feedbiz'}</p>
              </div>
              <label class="control-label col-lg-3">{l s='Taxes' mod='feedbiz'}</label>

              <div class="margin-form col-lg-9">
                <input type="checkbox" name="taxes" value="1" {$feedbiz_settings.taxes|escape:'htmlall':'UTF-8'} />&nbsp;
                <span style="color:black;font-size:12px;">&nbsp;{l s='Yes' mod='feedbiz'}</span>

                <p>{l s='Add taxes to products and calculate order\'s taxes if sets to yes' mod='feedbiz'}</p>
              </div>

            </div>

            {if isset($feedbiz_settings.ps_version_gt_15_or_equal)}
                {if isset($feedbiz_settings.ps_advanced_stock_management)}
                    <div class="form-group">
                      <label class="control-label col-lg-3">{l s='Warehouse' mod='feedbiz'}</label>

                      <div class="margin-form col-lg-9">
                        <select name="warehouse" style="width:500px;">
                          <option value="">{l s='Choose' mod='feedbiz'}</option>
                          {foreach from=$feedbiz_settings.warehouse_options item=warehouse_option}
                              <option value="{$warehouse_option.value|escape:'htmlall':'UTF-8'}" {$warehouse_option.selected|escape:'htmlall':'UTF-8'}>{$warehouse_option.desc|escape:'htmlall':'UTF-8'}</option>
                          {/foreach}
                        </select>

                        <p>{l s='Choose a warehouse for Feed.biz products pickup (for Advanced Stock Management)' mod='feedbiz'}</p>
                      </div>
                    </div>
                {/if}
            {/if}

            {* No need, always take the biggest picture *}
            {if $feedbiz_expert}
                {if isset($feedbiz_settings.image_types)}
                    <div class="form-group">
                      <label class="control-label col-lg-3">{l  s='Image Type' mod='feedbiz'}</label>

                      <div class="margin-form col-lg-9">
                        <select name="image_type" id="image_type" style="width:200px;">
                          <option disabled>{l s='Choose' mod='feedbiz'}</option>
                          <option></option>
                          {foreach from=$feedbiz_settings.image_types item=image_type}
                              <option value="{$image_type.value|escape:'htmlall':'UTF-8'}" {$image_type.selected|escape:'htmlall':'UTF-8'}>{$image_type.desc|escape:'htmlall':'UTF-8'}</option>
                          {/foreach}

                        </select>
                        <p>{l s='Kind of image which will be use for Feed (Please refer to Preference > Images for more informations)' mod='feedbiz'}</p>
                      </div>
                    </div>
                {/if}
            {/if}

            <hr style="width:30%"/>

            {*if $feedbiz_expert*}
            <div class="form-group">
              <label class="control-label col-lg-3">{l  s='Export limit per page' mod='feedbiz'}</label>

              <div class="margin-form col-lg-9">
                <input type="text" name="export_limit_per_page"
                       value="{$feedbiz_settings.export_limit_per_page|escape:'htmlall':'UTF-8'}"
                       style="width:100px;"/>

                <p>{l s='Number of product/offer to export per page (Default limit : 500)' mod='feedbiz'}</p>
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-lg-3"><span>{l s='Employee' mod='feedbiz'}</span></label>

              <div class="margin-form col-lg-9">
                <select name="employee" style="width:500px;">
                  <option value="" disabled="disabled">{l s='Choose' mod='feedbiz'}</option>
                  <option></option>
                  {foreach from=$feedbiz_settings.employee key=id_employee item=employee}
                      <option value="{$id_employee|intval}"
                              {if $employee.selected}selected{/if}>{$employee.name|escape:'htmlall':'UTF-8'}</option>
                  {/foreach}
                </select>

                <p>{l s='The module actions will be triggered with this Employee' mod='feedbiz'}</p>
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-lg-3" rel="customer_group"><span>{l s='Customer Group' mod='feedbiz'}</span></label>

              <div class="margin-form col-lg-9">
                <select name="id_group" style="width:500px;">
                  <option value="" disabled="disabled">{l s='Choose' mod='feedbiz'}</option>
                  {foreach from=$feedbiz_settings.customer_groups key=id_customer_group item=customer_group}
                      <option value="{$id_customer_group|intval}"
                              {if $customer_group.selected}selected{/if}>{$customer_group.name|escape:'htmlall':'UTF-8'}</option>
                  {/foreach}
                </select>
                <p>{l s='Customers created during order import also belong to this group' mod='feedbiz'}</p>
              </div>
            </div>

            <div class="form-group">
              <label class="control-label col-lg-3" rel="customer_group"><span>{l s='Carriers' mod='feedbiz'}</span></label>

              <div class="margin-form col-lg-9">
                <select name="carrier" style="width:500px;">
                  <option value="" disabled="disabled">{l s='Choose' mod='feedbiz'}</option>
                  {foreach from=$feedbiz_settings.std_carriers item=std_carriers}
                      <option value="{$std_carriers.value|intval}" {if $std_carriers.selected}selected{/if}>
                        {$std_carriers.desc|escape:'htmlall':'UTF-8'}
                      </option>
                  {/foreach}
                </select>
                <p>{l s='Default carrier for standard shipping, created during order import' mod='feedbiz'}</p>
              </div>
            </div>
            {*/if*}
            {include file="$module_path/views/templates/admin/configure/validate.tpl"}
          </div>
          <!--SETTINGS END -->

          <!-- FILTERS START -->
          <div id="menudiv-filters" class="panel form-horizontal" style="display:none;">
            <h2>{l s='Filters' mod='feedbiz'}</h2>

            <div class="form-group">
              <label class="control-label col-lg-3"></label>

              <div class="margin-form col-lg-9">
                <div class="{$alert_class.info|escape:'htmlall':'UTF-8'}">
                  {l s='This tool allows to exclude selected products matching these criterias to be exported to %s.' sprintf=[$module_display_name] mod='feedbiz'}
                </div>
              </div>
            </div>

            <label class="control-label col-lg-3">{l s='Manufacturers Filters' mod='feedbiz'}</label>

            <div class="form-group">
              <div class=" col-lg-5">

                <div class="manufacturer-heading margin-form col-lg-12">
                  <span class="col-lg-6"><img src="{$images_url|escape:'quotes':'UTF-8'}cross.png"
                                              alt="{l s='Excluded' mod='feedbiz'}"/></span>
                  <span class="col-lg-6"><img src="{$images_url|escape:'quotes':'UTF-8'}checked.png"
                                              alt="{l s='Included' mod='feedbiz'}"/></span>
                </div>

                <div class="margin-form col-lg-12">
                  <div class="col-lg-5">
                    <select name="excluded-manufacturers[]" class="excluded-manufacturers"
                            id="excluded-manufacturers" multiple="multiple">
                      <option value="0" disabled
                              style="color:orange;">{l s='Excluded Manufacturers' mod='feedbiz'}</option>
                      {foreach from=$feedbiz_filters.manufacturers.filtered key=id_manufacturer item=name}
                          <option value="{$id_manufacturer|intval}">{$name|escape:'htmlall':'UTF-8'}</option>
                      {/foreach}
                    </select>
                  </div>
                  <div class="{$filter_sep_class|escape:'htmlall':'UTF-8'} col-lg-2 text-center">
                    <img src="{$images_url|escape:'quotes':'UTF-8'}arrow_left.png" class="move"
                         id="manufacturer-move-left"
                         alt="Left"/><br/><br/>
                    <img src="{$images_url|escape:'quotes':'UTF-8'}arrow_right.png" class="move"
                         id="manufacturer-move-right"
                         alt="Right"/>
                  </div>
                  <div class="col-lg-5">
                    <select name="available-manufacturers[]" class="available-manufacturers"
                            id="available-manufacturers" multiple="multiple">

                      <option value="0" disabled
                              style="color:green;">{l s='Included Manufacturers' mod='feedbiz'}</option>
                      {foreach from=$feedbiz_filters.manufacturers.available key=id_manufacturer item=name}
                          <option value="{$id_manufacturer|intval}">{$name|escape:'htmlall':'UTF-8'}</option>
                      {/foreach}
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <div class="cleaner"></div>

            <div class="form-group">
              <label class="control-label col-lg-3">{l s='Suppliers Filters' mod='feedbiz'}</label>

              <div class="col-lg-5">
                <div class="margin-form supplier-heading col-lg-12">
                  <span class="col-lg-6"><img src="{$images_url|escape:'quotes':'UTF-8'}cross.png"
                                              alt="{l s='Excluded' mod='feedbiz'}"/></span>
                  <span class="col-lg-6"><img src="{$images_url|escape:'quotes':'UTF-8'}checked.png"
                                              alt="{l s='Included' mod='feedbiz'}"/></span>
                </div>

                <div class="margin-form col-lg-12">
                  <div class="col-lg-5">
                    <select name="selected-suppliers[]" class="selected-suppliers" id="selected-suppliers"
                            multiple="multiple">
                      <option value="0" disabled
                              style="color:orange;">{l s='Excluded Suppliers' mod='feedbiz'}</option>
                      {foreach from=$feedbiz_filters.suppliers.filtered key=id_supplier item=name}
                          <option value="{$id_supplier|intval}">{$name|escape:'htmlall':'UTF-8'}</option>
                      {/foreach}
                    </select>
                  </div>
                  <div class="{$filter_sep_class|escape:'htmlall':'UTF-8'} col-lg-2 text-center">
                    <img src="{$images_url|escape:'quotes':'UTF-8'}arrow_left.png" class="move"
                         id="supplier-move-left" alt="Left"/><br/><br/>
                    <img src="{$images_url|escape:'quotes':'UTF-8'}arrow_right.png" class="move"
                         id="supplier-move-right"
                         alt="Right"/>
                  </div>
                  <div class="col-lg-5">
                    <select name="available-suppliers[]" class="available-suppliers"
                            id="available-suppliers" multiple="multiple">
                      <option value="0" disabled
                              style="color:green;">{l s='Included Suppliers' mod='feedbiz'}</option>
                      {foreach from=$feedbiz_filters.suppliers.available key=id_supplier item=name}
                          <option value="{$id_supplier|intval}">{$name|escape:'htmlall':'UTF-8'}</option>
                      {/foreach}
                    </select>
                  </div>
                </div>
              </div>
            </div>
            {include file="$module_path/views/templates/admin/configure/validate.tpl"}
          </div>
          <!-- FILTERS END -->
        </form>
    {/if}

    <!-- START TO SIMPLE MODE FOR CDISCOUNT -->
    {if $mode_use == 'SIMPLE' && $is_cdiscount}
        <!--ACCOUNT CREATION START -->
        <form action="{$request_uri|escape:'quotes':'UTF-8'}" id="feedbiz_form" method="post" autocomplete="off">
          <input type="hidden" id="selected_tab" name="selected_tab" value="{$selected_tab|escape:'htmlall':'UTF-8'}"/>

          <!--INFO START -->
          <div id="menudiv-info_account" class="panel form-horizontal">
            <div class="form-group">
              <div class="col-lg-4">
                <h1>{l s='Informations' mod='feedbiz'}</h1>
              </div>
              <div class="col-lg-4">
              </div>
              <div class="col-lg-4">
                <a href="{$request_uri|escape:'quotes':'UTF-8'}&is_export=1" class="btn btn-default pull-right"><i class="process-icon-configure"></i> {l s='Expert Mode' mod='feedbiz'}</a>
              </div>
            </div>

            <div class="form-group">
              <div class="col-lg-3">
              </div>
              <div class="col-lg-9">
                <h2>{l s='Help us to know you better' mod='feedbiz'}</h2>
              </div>
            </div>

            <div class="form-group">
              <div class="col-lg-3">
              </div>
              <div class="col-lg-4">
                <p>{l s='Last Name' mod='feedbiz'}</p>
                <input type="text" name="survey_last_name" id="survey_last_name" value="{$info['last_name']|escape:'htmlall':'UTF-8'}" class="form-control"/>
              </div>
              <div class="col-lg-4">
                <p>{l s='Company' mod='feedbiz'}</p>
                <input type="text" name="survey_company" id="survey_company" value="{$info['company']|escape:'htmlall':'UTF-8'}" class="form-control"/>
              </div>
            </div>

            <div class="form-group">
              <div class="col-lg-3">
              </div>
              <div class="col-lg-4">
                <p>{l s='First Name' mod='feedbiz'}</p>
                <input type="text" name="survey_first_name" id="survey_first_name" value="{$info['first_name']|escape:'htmlall':'UTF-8'}" class="form-control"/>
              </div>
              <div class="col-lg-4">
                <p>{l s='Company registration number' mod='feedbiz'}</p>
                <input type="text" name="survey_company_num" id="survey_company_num" value="{$info['company_num']|escape:'htmlall':'UTF-8'}" class="form-control"/>
              </div>
            </div>

            <div class="form-group">
              <div class="col-lg-3">
              </div>
              <div class="col-lg-4">
                <p>{l s='Number Phone' mod='feedbiz'}</p>
                <input type="text" name="survey_telephone" id="survey_telephone" value="{$info['telephone']|escape:'htmlall':'UTF-8'}" class="form-control"/>
              </div>
              <div class="col-lg-4">
                <p>{l s='Website' mod='feedbiz'}</p>
                <input type="text" name="survey_web_site" id="survey_web_site" value="{$info['web_site']|escape:'htmlall':'UTF-8'}" class="form-control"/>
              </div>
            </div>

            <div class="form-group">
              <div class="col-lg-3">
              </div>
              <div class="col-lg-4">
                <p>{l s='Email' mod='feedbiz'}</p>
                <input type="text" name="survey_email" id="survey_email" value="{$info['email']|escape:'htmlall':'UTF-8'}" class="form-control"/>
              </div>
              <div class="col-lg-4">
                <p>{l s='Products/Combinations in your catalog' mod='feedbiz'}</p>
                <input type="text" name="survey_product_num" id="survey_product_num" value="{$info['product_num']|escape:'htmlall':'UTF-8'}" class="form-control"/>
              </div>
            </div>

            <div class="form-group">
              <div class="col-lg-3">
              </div>
              <div class="col-lg-4">
                <p>{l s='Language' mod='feedbiz'}</p>
                <select name="survey_language" id="survey_language">
                  {foreach $info['languages'] as  $language}
                      <option value="{$language['name']|escape:'htmlall':'UTF-8'}" {if ($info['language_default'] == $language.id_lang)}selected{/if}>{$language['name']|escape:'htmlall':'UTF-8'}</option>
                  {/foreach}
                </select>
              </div>
              <div class="col-lg-4">
                <p>{l s='Country' mod='feedbiz'}</p>
                <select name="survey_country" id="survey_country">
                  {foreach $info['countries'] as $c => $country}
                      <option value="{$country['name']|escape:'htmlall':'UTF-8'}" {if ({$info['country_default']|escape:'htmlall':'UTF-8'} == $country['iso_code']|escape:'htmlall':'UTF-8')}selected{/if} >{$country['name']|escape:'htmlall':'UTF-8'}</option>
                  {/foreach}
                </select>
              </div>
            </div>

            <div class="form-group">
              <div class="col-lg-3">
              </div>
              <div class="col-lg-4">
                <p>{l s='Sales revenues per year on marketplaces' mod='feedbiz'}</p>
                <select name="survey_sales" id="survey_sales">
                  <option value="">{l s='No sales yet' mod='feedbiz'}</option>
                  {foreach $info['revenues'] as $c => $revenue}
                      <option value="{$revenue|escape:'htmlall':'UTF-8'}" {if ($info['revenue_default'] == $revenue)}selected{/if}>{$revenue|escape:'htmlall':'UTF-8'} €</option>
                  {/foreach}
                </select>
              </div>
              <div class="col-lg-4">
                <p>{l s='Your most important category' mod='feedbiz'}</p>
                <select name="survey_category" id="survey_category">
                  <option value="">{l s='No categories yet' mod='feedbiz'}</option>
                  {foreach $info['categories'] as $c => $category}
                      <option value="{$category|escape:'htmlall':'UTF-8'}"  {if ($info['category_default'] == $category)}selected{/if}>{$category|escape:'htmlall':'UTF-8'}</option>
                  {/foreach}
                </select>
              </div>
            </div>

            <div class="form-group">
              <div class="col-lg-3">
              </div>
              <div class="col-lg-4">
                <p>{l s='Do you sell on marketplaces ? If yes, which ones' mod='feedbiz'}</p>
                {*placeholders for fselect*}
                <input type="hidden" id="fs_placeholder" value="{l s='Select your marketplaces' mod='feedbiz'}" />
                <input type="hidden" id="fs_overflowtext" value="{l s='{n} selected' mod='feedbiz'}" />
                <input type="hidden" id="fs_noresultstext" value="{l s='No result found' mod='feedbiz'}" />
                <input type="hidden" id="fs_search" value="{l s='Search' mod='feedbiz'}" />
                <select name="survey_marketplaces" multiple="multiple" id="survey_marketplaces">
                  {foreach $info['marketplaces'] as $c => $marketplace}
                      <option value="{$marketplace|escape:'htmlall':'UTF-8'}" {if (isset($info.marketplaces_default[$marketplace]))}selected{/if}>{$marketplace|escape:'htmlall':'UTF-8'}</option>
                  {/foreach}
                </select>
              </div>
              <div class="col-lg-8">
              </div>
            </div>

            <div class="panel-footer"  style="margin-top:40px;">
              <div class="form-group">
                <div class="col-lg-4">
                </div>
                <div class="col-lg-4">
                </div>
                <div class="col-lg-4">
                  <button type="button" rel="account" send_survey="1" class="btn btn-default pull-right btn-step-next">
                    <i class="process-icon-next"></i> {l s='Next' mod='feedbiz'}
                  </button>
                </div>
              </div>
            </div>
            <button type="submit" class="btn btn-primary" name="submit_form" id="submit_form" style="display:none;" value="Send Survey">Send Survey</button>
          </div>
          <!--INFO END-->

          <!--ACCOUNT CREATION START-->
          <div id="menudiv-account" class="panel form-horizontal" style="display: none;">

            <div class="form-group">
              <div class="col-lg-4">
                <h1>{l s='Your Cdiscount Marketplace account' mod='feedbiz'}</h1>
              </div>
              <div class="col-lg-4">
              </div>
              <div class="col-lg-4">
              </div>
            </div>
            <div class="form-group">
              <div class="col-lg-1">
              </div>
              <div class="col-lg-7">
                <h2>{l s='Your Cdiscount account' mod='feedbiz'}</h2>
                <em>{l s='Cdiscount is the marketplace you aim to sell to' mod='feedbiz'}...</em>
              </div>
              <div class="col-lg-4">
              </div>
            </div>

            <div class="form-group">
              <div class="col-lg-12">
              </div>
            </div>

            <div class="form-group">
              <div class="col-lg-1">
              </div>
              <div class="col-lg-5 cdiscount-user">
                <p>{l s='You don\'t have a seller account on Cdiscount ?' mod='feedbiz'}</p>
                <a href="{$url_fb_register_cdiscount|escape:'htmlall':'UTF-8'}" class="button btn btn-primary" target="_blank">{l s='Create a seller account' mod='feedbiz'}</a>
              </div>
              <div class="col-lg-5 cdiscount-user">
                <p>{l s='You already have a seller account on Cdiscount' mod='feedbiz'}</p>
                <a href="#" class="button btn btn-primary" rel="cdiscount_flux" id="cdiscount-already" target="_blank">{l s='Continue' mod='feedbiz'}</a>
              </div>
            </div>


            <div class="form-group">
              <div class="col-lg-12">
                <br />
                <br />
                <br />
              </div>
            </div>


            <div class="panel-footer">
              <div class="form-group">
                <div class="col-lg-4">
                  <button type="button" rel="info_account" class="btn btn-default pull-left btn-step-back">
                    <i class="process-icon-back"></i> {l s='previous' mod='feedbiz'}
                  </button>
                </div>
                <div class="col-lg-4">
                </div>
                <div class="col-lg-4">
                  <button type="button" rel="cdiscount_flux" class="btn btn-default pull-right btn-step-next">
                    <i class="process-icon-next"></i> {l s='Next' mod='feedbiz'}
                  </button>
                </div>
              </div>
            </div>
          </div>
          <!--ACCOUNT CREATION END-->

          <!--CDISCOUNT FLUX CREATION START-->
          <div id="menudiv-cdiscount_flux" class="panel form-horizontal" style="display: none;">
            <div class="form-group">
              <h1>{l s='Welcome to %s' sprintf=[$module_display_name] mod='feedbiz'} !</h1>
              <div class="col-lg-1">
              </div>
              <div class="col-lg-7">

              </div>
              <div class="col-lg-4">
              </div>
            </div>
            <div class="form-group">
              <div class="col-lg-1">
              </div>
              <div class="col-lg-7">
                <h2>{l s='Your Cdiscount Flux account' mod='feedbiz'}</h2>
                <em>{l s='%s is the solution to connect your shop to the marketplace' mod='feedbiz' sprintf=[$module_display_name]}...</em>
              </div>
              <div class="col-lg-4">
              </div>
            </div>

            <div class="form-group">
              <div class="col-lg-1">
              </div>
              <div class="col-lg-7 keypoints">
                <ul>
                  <li>
                    {l s='Create easily your catalog' mod='feedbiz'}
                  </li>
                  <li>
                    {l s='Your offers (stock, prices) are updated automatically' mod='feedbiz'}
                  </li>
                  <li>
                    {l s='Your orders are managed directly from you backoffice' mod='feedbiz'}
                  </li>
                </ul>
              </div>
              <div class="col-lg-4">
              </div>
            </div>
            <br>
            <div class="form-group">
              <div class="col-lg-1">
              </div>

              <div align="left" class="col-lg-5  new-user">
                <p>{l s='You don\'t have a %s account ?' mod='feedbiz' sprintf=[$module_display_name]}</p>
                <a href="{$url_fb_register|escape:'htmlall':'UTF-8'}" class="button btn btn-primary" target="_blank">{l s='Create an account' mod='feedbiz'}</a>
              </div>

              <div class="col-lg-5 returning-user">
                <p>{l s='You already have a %s account ?' sprintf=[$module_display_name] mod='feedbiz'}</p>
                <a href="{$url_fb_login|escape:'htmlall':'UTF-8'}" class="button btn btn-primary" target="_blank">{l s='Login' mod='feedbiz'}</a>
              </div>
              <div class="col-lg-1">
              </div>
            </div>

            <div class="panel-footer" style="margin-top:40px">
              <div class="form-group">
                <div class="col-lg-4">
                  <button type="button" rel="account" class="btn btn-default pull-left btn-step-back">
                    <i class="process-icon-back"></i> {l s='previous' mod='feedbiz'}
                  </button>
                </div>
                <div class="col-lg-4">
                </div>
                <div class="col-lg-4">
                  <button type="button" rel="import" class="btn btn-default pull-right btn-step-next">
                    <i class="process-icon-next"></i> {l s='Next' mod='feedbiz'}
                  </button>
                </div>
              </div>
            </div>
          </div>
          <!--CDISCOUNT FLUX CREATION END-->

          <!--IMPORT START -->
          <div id="menudiv-import" class="panel form-horizontal" style="display:none;">


            <div class="form-group">
              <div class="col-lg-4">
                <h1>{l s='Connect your shop' mod='feedbiz'}</h1>
              </div>
              <div class="col-lg-4">
              </div>
              <div class="col-lg-4">
              </div>
            </div>


            <div class="form-group">
              <div class="col-lg-1">
              </div>
              <div class="col-lg-7">
                <h2>{l s='Cdiscount Marketplace and your shop will be both synchonized' mod='feedbiz'} !</h2>
                <em>{l s='Now, if you are now connected to Cdiscount Flux' mod='feedbiz' sprintf=[$module_display_name]}:</em>
              </div>
              <div class="col-lg-4">
              </div>
            </div>

            <div class="form-group">
              <div class="col-lg-1">
              </div>
              <div class="col-lg-7 keypoints">
                <ul>
                  <li>
                    {l s='Click on connect to export your catalog' mod='feedbiz'}
                  </li>
                  <li>
                    {l s='Follow the wizard instructions' mod='feedbiz'}
                  </li>
                  <li>
                    {l s='Your shop is synchronized !' mod='feedbiz'}
                  </li>
                </ul>
              </div>
              <div class="col-lg-4">
              </div>
            </div>


            <div class="form-group">
              <div class="col-lg-1">
              </div>
              <div class="col-lg-5 cdiscount-user">
                <p>{l s='Just click below to connect your shop' mod='feedbiz'}:</p>
                <input type="hidden" id="url_fb_connector" value="{$url_fb_connector|escape:'htmlall':'UTF-8'}" />
                <input type="hidden" id="url_fb_customer_survey" value="{$url_fb_customer_survey|escape:'htmlall':'UTF-8'}" />
                <button type="button" id="btn_survey_connect" class="button btn btn-primary">{l s='Connect !' mod='feedbiz'}</button>
              </div>
              <div class="col-lg-5 cdiscount-user">
                <p>{l s='Then explore your dashboard' mod='feedbiz'}</p>
                <input type="hidden" id="url_fb_dashboard" value="{$url_fb_dahsboard|escape:'htmlall':'UTF-8'}" />
                <button type="button" id="btn_feedbiz_dashboard" class="button btn btn-primary">{l s='Explore' mod='feedbiz'}...</button>
              </div>
            </div>




            <div class="panel-footer" style="margin-top:80px">
              <div class="form-group">
                <div class="col-lg-4">
                  <button type="button" rel="cdiscount_flux" class="btn btn-default pull-left btn-step-back">
                    <i class="process-icon-back"></i> {l s='previous' mod='feedbiz'}
                  </button>
                </div>
                <div class="col-lg-4">
                </div>
                <div class="col-lg-4">
                </div>
              </div>
            </div>

          </div>
          <!--IMPORT END-->

          <!--SUPPORT START -->
          <div id="menudiv-support" class="panel form-horizontal" style="display:none;">

            <div class="form-group">
              <div class="col-lg-4">
                <h1>{l s='Support Informations' mod='feedbiz'}</h1>
              </div>
              <div class="col-lg-4">
              </div>
              <div class="col-lg-4">
              </div>
            </div>

            <div class="form-group">
              <div class="col-lg-1">
              </div>
              <div class="col-lg-7">
                <h2>{l s='Support is free, don\'t hesitate to contact us !' mod='feedbiz'}</h2>
                <em>{l s='Our helpdesk will assist you through a ticketing system, please provide us' mod='feedbiz'}:</em>
              </div>
              <div class="col-lg-4">
              </div>
            </div>

            <div class="form-group">
              <div class="col-lg-1">
              </div>
              <div class="col-lg-7 keypoints">
                <ul>
                  <li>{l s='A detailled description of the issue or encountered problem' mod='feedbiz'}</li>
                  <li>{l s='Your Prestashop version' mod='feedbiz'} : <span style="color: red;">Prestashop {$ps_version|escape:'htmlall':'UTF-8'}</span>
                  </li>
                  <li>{l s='Your module version' mod='feedbiz'} : <span
                        style="color: red;">{$module_display_name|escape:'htmlall':'UTF-8'} v{$version|escape:'htmlall':'UTF-8'}</span>
                  </li>
                </ul>
                <br>
                {if $is_cdiscount}
                    <p>{l s='Send you inquiry to' mod='feedbiz'}:&nbsp;
                      <a href="mailto:support@feed.biz?subject={l s='Support for Cdiscount Flux' mod='feedbiz'}&body={l s='Dear Support, I am currently having some trouble with your module v%s on my Prestashop v%s.' sprintf=[$version, $ps_version] mod='feedbiz'}">
                        support-cdiscount@feed.biz
                      </a>
                    </p>
                {else}
                    <a href="mailto:support@feed.biz?subject={l s='Support for Feed.Biz' mod='feedbiz'}&body={l s='Dear Support, I am currently having some trouble with your module v%s on my Prestashop v%s.' sprintf=[$version, $ps_version] mod='feedbiz'}">
                      support@feed.biz
                    </a>
                {/if}
              </div>
              <div class="col-lg-4">
              </div>
            </div>

            <div class="form-group">
              <div class="col-lg-1">
              </div>
              <div class="col-lg-6 cdiscount-support">
                <p>{l s='Please join with your inquiry this file' mod='feedbiz'}:</p>
                <em>{l s='This file contains support informations we would need for a faster diagnosis' mod='feedbiz'}</em>
                <input type="hidden" id="url_support_info" value="{$feedbiz_informations.support_informations_url|escape:'htmlall':'UTF-8'}&action=support-infos" />
                <p style="text-align:center;margin-top:20px">
                  <img src="{$feedbiz_informations.img|escape:'htmlall':'UTF-8'}loading.gif" style="display:none"  alt="{l s='Support Informations' mod='feedbiz'}" class="support2-informations-loader"/>
                  <a href="#" target="_blank" style="display:none;" class="support-url">
                    <img src="{$feedbiz_informations.img|escape:'htmlall':'UTF-8'}/zip64.png" class="support-file" title="Support Details" />
                    <br />{l s='Download' mod='feedbiz'}
                  </a>
                </p>
                <p style="text-align:center">
                </p>
              </div>
              <div class="col-lg-4 cdiscount-user">
                {*
                <button type="button" class="btn btn-default pull-left btn-test" rel="{$feedbiz_informations.support_informations_url|escape:'htmlall':'UTF-8'}&action=support-infos">
                <i class="process-icon-back"></i> {l s='test' mod='feedbiz'}
                </button>
                *}
              </div>
            </div>

            <div class="panel-footer" style="margin-top:80px">
              <div class="form-group">
                <div class="col-lg-4">
                  <button type="button" rel="cdiscount_flux" class="btn btn-default pull-left btn-step-back">
                    <i class="process-icon-back"></i> {l s='previous' mod='feedbiz'}
                  </button>
                </div>
                <div class="col-lg-4">
                </div>
                <div class="col-lg-4">
                </div>
              </div>
            </div>

          </div>
          <!--IMPORT END-->
        {/if}
    </form>
    <!-- END TO SIMPLE MODE FOR CDISCOUNT -->

    <!-- START TO SIMPLE MODE FOR FEEDBIZ -->
    {if $is_feedbiz && $feedbiz_mode == 'SIMPLE'}
        <!--ACCOUNT CREATION START -->
        <form action="{$request_uri|escape:'quotes':'UTF-8'}" id="feedbiz_form" method="post" autocomplete="off">
          <input type="hidden" id="selected_tab" name="selected_tab" value="{$selected_tab|escape:'htmlall':'UTF-8'}"/>
          <input type="hidden" id="is_feedbiz" value="1"/>

          <!--INFO START -->
          
          <!--INFO END-->

          <!--IMPORT START -->
          <div id="menudiv-import" class="panel form-horizontal">
            
            <div class="form-group">
              <div class="col-lg-4">
                <h1>{l s='Connect your shop' mod='feedbiz'}</h1>
              </div>
              <div class="col-lg-4">
              </div>
              <div class="col-lg-4">
                <a href="{$request_uri|escape:'quotes':'UTF-8'}&feedbiz_mode=1" class="btn btn-default pull-right"><i class="process-icon-configure"></i> {l s='Expert Mode' mod='feedbiz'}</a>
              </div>
            </div>

            <div class="form-group">
              <div class="col-lg-1">
              </div>
              <div class="col-lg-7">
                <h2>{l s='Feedbiz Marketplace and your shop will be both synchonized' mod='feedbiz'} !</h2>
                <em>{l s='Now, if you are now connected to Feedbiz' mod='feedbiz' sprintf=[$module_display_name]}:</em>
              </div>
              <div class="col-lg-4">
              </div>
            </div>

            <div class="form-group">
              <div class="col-lg-1">
              </div>
              <div class="col-lg-7 keypoints">
                <ul>
                  <li>
                    {l s='Click on connect to export your catalog' mod='feedbiz'}
                  </li>
                  <li>
                    {l s='Follow the wizard instructions' mod='feedbiz'}
                  </li>
                  <li>
                    {l s='Your shop is synchronized !' mod='feedbiz'}
                  </li>
                </ul>
              </div>
              <div class="col-lg-4">
              </div>
            </div>

            <div class="form-group">
              <div class="col-lg-1">
              </div>
              <div class="col-lg-5 cdiscount-user">
                <p>{l s='Just click below to connect your shop' mod='feedbiz'}:</p>
                <input type="hidden" id="url_fb_connector" value="{$url_fb_connector|escape:'htmlall':'UTF-8'}" />
                <input type="hidden" id="url_fb_customer_survey" value="{$url_fb_customer_survey|escape:'htmlall':'UTF-8'}" />
                <button type="button" id="btn_survey_connect" class="button btn btn-primary">{l s='Connect !' mod='feedbiz'}</button>
              </div>
              <div class="col-lg-5 cdiscount-user">
                <p>{l s='Then explore your dashboard' mod='feedbiz'}</p>
                <input type="hidden" id="url_fb_dashboard" value="{$url_fb_dahsboard|escape:'htmlall':'UTF-8'}" />
                <button type="button" id="btn_feedbiz_dashboard" class="button btn btn-primary">{l s='Explore' mod='feedbiz'}...</button>
              </div>
            </div>

            <div class="panel-footer" style="margin-top:80px">
              <div class="form-group">
                <div class="col-lg-4">

                </div>
                <div class="col-lg-4">
                </div>
                <div class="col-lg-4">
                </div>
              </div>
            </div>

          </div>
          <!--IMPORT END-->

          <!--SUPPORT START -->
          <div id="menudiv-support" class="panel form-horizontal" style="display:none;">

            <div class="form-group">
              <div class="col-lg-4">
                <h1>{l s='Support Informations' mod='feedbiz'}</h1>
              </div>
              <div class="col-lg-4">
              </div>
              <div class="col-lg-4">
              </div>
            </div>

            <div class="form-group">
              <div class="col-lg-1">
              </div>
              <div class="col-lg-7">
                <h2>{l s='Support is free, don\'t hesitate to contact us !' mod='feedbiz'}</h2>
                <em>{l s='Our helpdesk will assist you through a ticketing system, please provide us' mod='feedbiz'}:</em>
              </div>
              <div class="col-lg-4">
              </div>
            </div>

            <div class="form-group">
              <div class="col-lg-1">
              </div>
              <div class="col-lg-7 keypoints">
                <ul>
                  <li>{l s='A detailled description of the issue or encountered problem' mod='feedbiz'}</li>
                  <li>{l s='Your Prestashop version' mod='feedbiz'} : <span style="color: red;">Prestashop {$ps_version|escape:'htmlall':'UTF-8'}</span>
                  </li>
                  <li>{l s='Your module version' mod='feedbiz'} : <span
                        style="color: red;">{$module_display_name|escape:'htmlall':'UTF-8'} v{$version|escape:'htmlall':'UTF-8'}</span>
                  </li>
                </ul>
                <br>
                {if $is_cdiscount}
                    <p>{l s='Send you inquiry to' mod='feedbiz'}:&nbsp;
                      <a href="mailto:support@feed.biz?subject={l s='Support for Cdiscount Flux' mod='feedbiz'}&body={l s='Dear Support, I am currently having some trouble with your module v%s on my Prestashop v%s.' sprintf=[$version, $ps_version] mod='feedbiz'}">
                        support-cdiscount@feed.biz
                      </a>
                    </p>
                {else}
                    <a href="mailto:support@feed.biz?subject={l s='Support for Feed.Biz' mod='feedbiz'}&body={l s='Dear Support, I am currently having some trouble with your module v%s on my Prestashop v%s.' sprintf=[$version, $ps_version] mod='feedbiz'}">
                      support@feed.biz
                    </a>
                {/if}
              </div>
              <div class="col-lg-4">
              </div>
            </div>

            <div class="form-group">
              <div class="col-lg-1">
              </div>
              <div class="col-lg-6 cdiscount-support">
                <p>{l s='Please join with your inquiry this file' mod='feedbiz'}:</p>
                <em>{l s='This file contains support informations we would need for a faster diagnosis' mod='feedbiz'}</em>
                <input type="hidden" id="url_support_info" value="{$feedbiz_informations.support_informations_url|escape:'htmlall':'UTF-8'}&action=support-infos" />
                <p style="text-align:center;margin-top:20px">
                  <img src="{$feedbiz_informations.img|escape:'htmlall':'UTF-8'}loading.gif" style="display:none"  alt="{l s='Support Informations' mod='feedbiz'}" class="support2-informations-loader"/>
                  <a href="#" target="_blank" style="display:none;" class="support-url">
                    <img src="{$feedbiz_informations.img|escape:'htmlall':'UTF-8'}/zip64.png" class="support-file" title="Support Details" />
                    <br />{l s='Download' mod='feedbiz'}
                  </a>
                </p>
                <p style="text-align:center">
                </p>
              </div>
              <div class="col-lg-4 cdiscount-user">

              </div>
            </div>

            <div class="panel-footer" style="margin-top:80px">
              <div class="form-group">
                <div class="col-lg-4">

                </div>
                <div class="col-lg-4">
                </div>
                <div class="col-lg-4">
                </div>
              </div>
            </div>
          </div>
          <!--IMPORT END-->
        {/if}
    </form>
    <!-- END TO SIMPLE MODE FOR FEEDBIZ -->

</fieldset>

<div class="margin-form">
  <p>
    <span style="color:red;">&nbsp;*</span>&nbsp;:
    {l s='These informations are provided by Feed. Please contact the support for more informations' mod='feedbiz'} :
    {if $is_cdiscount}
        <a href="mailto:support-cdiscount@feed.biz">support-cdiscount@feed.biz</a>
    {else}
        <a href="mailto:support@feed.biz">support@feed.biz</a>
    {/if}
  </p>
  {if isset($feedbiz_memory_peak_usage)}
      <div class="conf confirm">Memory Peak: {$feedbiz_memory_peak_usage|escape:'htmlall':'UTF-8'} MB</div>
  {/if}

</div>
<!-- ! body end -->





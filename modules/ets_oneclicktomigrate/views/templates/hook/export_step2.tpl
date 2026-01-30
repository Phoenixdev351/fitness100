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
<div class="form-group" style="display:none;">
    <label class="control-label col-lg-4 required" for="data_format">{l s='Data format' mod='ets_oneclicktomigrate'} </label>
    <div class="col-lg-8">
        <select name="data_format" id="data_format" class=" fixed-width-xl">
            <option value="xml"{if $ets_datamaster_format=='xml'} selected="selected"{/if}>{l s='XML' mod='ets_oneclicktomigrate'}</option>
            <option value="csv"{if $ets_datamaster_format=='csv'} selected="selected"{/if}>{l s='CSV' mod='ets_oneclicktomigrate'}</option>
        </select>
    </div>
</div>
<div class="divide_file_xml">
    <div class="form-group">
        <label class="control-label col-lg-4">{l s='Split large data files into smaller data files' mod='ets_oneclicktomigrate'}</label>
        <div class="col-lg-8">
            <span class="switch prestashop-switch fixed-width-lg">
                <input id="divide_file_on" type="radio" checked="checked" value="1" name="divide_file" />
                <label for="divide_file_on">{l s='Yes' mod='ets_oneclicktomigrate'}</label>
                <input id="divide_file_off" type="radio"  value="0" name="divide_file" />
                <label for="divide_file_off">{l s='No' mod='ets_oneclicktomigrate'}</label>
                <a class="slide-button btn"></a>
            </span>
            <p class="help-block">{l s='This option is recommended to avoid memory limitation errors happen when importing data if your data is too large.' mod='ets_oneclicktomigrate'} </p>
        </div>
    </div>
    <div class="form-group number_record">
        <label class="control-label col-lg-4">{l s='Maximum number of lines per data file' mod='ets_oneclicktomigrate'}</label>
        <div class="col-lg-8">
            <input name="number_record" id="number_record" type="text" value="{if $number_record}{$number_record|intval}{else}500{/if}" />
            <p class="help-block">{l s='500 is recommended. Enter a smaller number if your server resource (memory limitation and maximum PHP execution time) is low' mod='ets_oneclicktomigrate'} </p>
        </div>
    </div>
    {if in_array('orders',$ets_datamaster_export)}
        <div class="form-group">
            <label class="control-label col-lg-4">{l s='Orders added from' mod='ets_oneclicktomigrate'}</label>
            <div class="col-lg-5">
                <div class="input-group" style="width:70%">
                    <input id="ETS_PRES2PRES_ORDER_FROM" class="col-lg-9 ctf-copy-code datepicker" type="text" value="{$ETS_PRES2PRES_ORDER_FROM|escape:'html':'UTF-8'}" name="ETS_PRES2PRES_ORDER_FROM" />
                    <span class="input-group-addon">
    					<i class="icon-calendar"></i>
    				</span>	
                </div>												
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-lg-4">{l s='Orders added to' mod='ets_oneclicktomigrate'}</label>
            <div class="col-lg-5">
                <div class="input-group" style="width:70%">
                    <input id="ETS_PRES2PRES_ORDER_TO" class="col-lg-9 ctf-copy-code datepicker" type="text" value="{$ETS_PRES2PRES_ORDER_TO|escape:'html':'UTF-8'}" name="ETS_PRES2PRES_ORDER_TO" />
                    <span class="input-group-addon">
    					<i class="icon-calendar"></i>
    				</span>	
                </div>												
            </div>
        </div>
    {/if}
</div>
<script type="text/javascript">
    $(document).ready(function(){
       if($('#data_format').val()=='xml')
       {
            $('.divide_file_xml').show();
       }
       else
           $('.divide_file_xml').hide();
       $(document).on('change','#data_format',function(e){
            if($('#data_format').val()=='xml')
            {
                $('.divide_file_xml').show();
            }
            else
               $('.divide_file_xml').hide();
       });
       $(document).on('click','input[name="divide_file"]',function(){
            if($('input[name="divide_file"]:checked').val()==1)
                $('.form-group.number_record').show();
           else
                $('.form-group.number_record').hide();
       });
       if($('input[name="divide_file"]:checked').val()==1)
            $('.form-group.number_record').show();
       else
            $('.form-group.number_record').hide();
       $(document).ready(function(){
            if ($(".datepicker").length > 0) {
    			$(".datepicker").datepicker({
    				prevText: '',
    				nextText: '',
    				dateFormat: 'yy-mm-dd'
    			});
    		}
        });
    });
</script>
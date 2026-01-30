{*
* 2007-2020 ETS-Soft
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
* needs, please contact us for extra customization service at an affordable price
*
*  @author ETS-Soft <etssoft.jsc@gmail.com>
*  @copyright  2007-2020 ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}
<form id="module_form" class="defaultForm form-horizontal AdminPerformance" novalidate="" enctype="multipart/form-data" method="post" action="">
    <div id="fieldset_0" class="panel">
        <div class="panel-heading">
            <i class="icon-envelope"></i>
            {l s='Image optimization' mod='ets_superspeed'}
        </div>
        <div class="form-wrapper">
            <div class="form-group">											
                <label class="control-label col-lg-3">
         			{l s='Optimize newly uploaded images' mod='ets_superspeed'}
          		</label>
                <div class="col-lg-9">
                    <span class="switch prestashop-switch fixed-width-lg">
						<input type="radio" checked="checked" value="1" id="ETS_SPEED_OPTIMIZE_NEW_IMAGE_on" name="ETS_SPEED_OPTIMIZE_NEW_IMAGE" />
            			<label for="ETS_SPEED_OPTIMIZE_NEW_IMAGE_on">{l s='Yes' mod='ets_superspeed'}</label>
						<input type="radio" value="0" id="ETS_SPEED_OPTIMIZE_NEW_IMAGE_off" name="ETS_SPEED_OPTIMIZE_NEW_IMAGE" />
            			<label for="ETS_SPEED_OPTIMIZE_NEW_IMAGE_off">{l s='No' mod='ets_superspeed'}</label>
						<a class="slide-button btn"></a>
            		</span>
                </div>
            </div>
        </div>
    </div>
</form>
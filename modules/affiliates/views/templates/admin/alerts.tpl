{*
* Affiliates
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
*
* @author    FMM Modules
* @copyright © Copyright 2021 - All right reserved
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* @category  FMM Modules
* @package   affiliates
*}
<script type="text/javascript">
var count = 0;
count = "{$count|escape:'htmlall':'UTF-8'}";
var controller = "{$controller|escape:'htmlall':'UTF-8'}";
var id_affiliate = "{$id_affiliate|escape:'htmlall':'UTF-8'}";
var ref_key = "{$ref_key|escape:'htmlall':'UTF-8'}";
var key_len = "{$key_len|escape:'htmlall':'UTF-8'}";
var code_label = "{l s='Reference Key' mod='affiliates' js=1}";
var generate_label = "{l s='Generate' mod='affiliates' js=1}";
$(document).ready(function()
{
	if (typeof key_len == 'undefined' || key_len == '')
		key_len = 16;

	var alert = $('<span class="badge" id="favorite-count" style="position: absolute;right: 16px;line-height:20px;top:3px;">'+count+'</span>');
	$('#maintab-AdminAffiliation > a').append(alert);

	if (typeof controller != 'undefined' && controller == 'AdminAffiliates' && parseInt(id_affiliate))
	{
		var key = $('<div class="form-group">'+
					'<label class="control-label col-lg-3">'+
						'<span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="">'+
							code_label
						+'</span>'+
					'</label>'+
					'<div class="col-lg-9">'+
						'<div class="input-group col-lg-6">'+
							'<input type="text" value="'+ ref_key +'" name="ref_key" id="code">'+
							'<span class="input-group-btn">'+
								'<a class="btn btn-default button" href="javascript:gencode('+ key_len +')"><i class="icon-random"></i>'+
								generate_label
								+'</a>'+
							'</span>'+
						'</div>'+
					'</div>'+
				'</div><div class="clear"></div><br/>');

	if (parseFloat(_PS_VERSION_) < 1.6)
		$('input[name=approved]').parent().after(key);
	else
		$('input[name=approved]').parent().parent().parent().after(key);
	}
})
</script>
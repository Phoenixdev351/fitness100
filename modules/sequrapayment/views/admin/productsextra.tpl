<style>
	input[pattern]:invalid {
		background-color: #eab3b7;
	}
</style>
<div id="product-sequra" class="panel product-tab">
	<h3 class="tab">{l s='Sequra Extra Fields' mod='sequrapayment'}</h3>
	<div class="form-group">
		<label id="sequra_is_banned_label" for="sequra_is_banned" class="control-label col-lg-3">
				<span class="label-tooltip" data-toggle="tooltip"
							title="{l s='There are some kind of product that can not be sold with SeQura, like live animal, weapons or inlegal stuff.' mod='sequrapayment'}">
					{l s='Prevent this product from being paid by SeQura?:' mod='sequrapayment'}
				</span>
		</label>
		<div class="col-lg-9">
				<span class="switch prestashop-switch fixed-width-lg">
						<input type="radio" name="sequra_is_banned"
									id="sequra_is_banned_on" value="1" {if $sequra_is_banned} checked="checked"{/if}/>
						<label for="sequra_is_banned_on">{l s='Yes'}</label>
						<input type="radio" name="sequra_is_banned"
									id="sequra_is_banned_off" value="0" {if !$sequra_is_banned} checked="checked"{/if}/>
						<label for="sequra_is_banned_off">{l s='No'}</label>
						<a class="slide-button btn"></a>
				</span>
		</div>
	</div>
	{if $sequra_for_services}
	<hr/>
	<div class="form-group">
		<label id="sequra_is_service_label" for="sequra_is_service" class="control-label col-lg-3">
			<span class="label-tooltip" data-toggle="tooltip"
						title="{l s='Sequra should deal with this product as a service' mod='sequrapayment'}">
				{l s='Is service?:' mod='sequrapayment'}
			</span>
		</label>
		<div class="col-lg-9">
			<span class="switch prestashop-switch fixed-width-lg">
					<input onclick="toggleSequraServiceEndDate(true)" type="radio" name="sequra_is_service"
								 id="sequra_is_service_on" value="1" {if $sequra_is_service} checked="checked"{/if}/>
					<label for="sequra_is_service_on">{l s='Yes'}</label>
					<input onclick="toggleSequraServiceEndDate(false)" type="radio" name="sequra_is_service"
								 id="sequra_is_service_off" value="0" {if !$sequra_is_service} checked="checked"{/if}/>
					<label for="sequra_is_service_off">{l s='No'}</label>
					<a class="slide-button btn"></a>
			</span>
		</div>
	</div>
	<div class="form-group" id="sequra_service_end_date_row">
		<label class="control-label col-lg-3" for="sequra_is_service">
			<span class="label-tooltip" data-toggle="tooltip" title="{l s='Service end date or period' mod='sequrapayment'}">
				{l s='Service end date or period' mod='sequrapayment'}:
			</span>
		</label>
		<div class="col-lg-9">
			<input type="text"
						 id="sequra_service_end_date"
						 class="form-control fixed-width-lg"
						 name="sequra_service_end_date"
						 value="{$sequra_service_end_date|htmlentitiesUTF8|default:''}"
						 placeholder="Formato ISO8601"
						 pattern="{$ISO8601_PATTERN}"
			/>
			<p class="preference_description">{l s='Service end date or period' mod='sequrapayment'}:
			<ol>
				<li>
					<strong>{l s='Fecha o plazo en el que se dará el curos por impartido o el servicio por prestado' mod='sequrapayment'}</strong>
				</li>
				<li>{l s='Ejeplo: Fecha como 2017-08-31, plazo como P3M15D (3 meses y 15 días)' mod='sequrapayment'}
				<li>{l s='Dejar vacío si el producto no es un curso o servicio' mod='sequrapayment'}</li>
			</ol>
			</p>
		</div>
	</div>
	<script>
		function toggleSequraServiceEndDate(show) {
			if (show) {
				$('#sequra_service_end_date_row').show();
				$('#sequra_service_end_date').disabled = false;
			} else {
				$('#sequra_service_end_date_row').hide();
				$('#sequra_service_end_date').disabled = true;
			}
		}

		toggleSequraServiceEndDate({$sequra_is_service});
	</script>
	{/if}
	{if $sequra_allow_payment_delay}
	<hr/>
	<div class="form-group" id="sequra_desired_first_charge_date_row">
		<label class="control-label col-lg-3" for="sequra_is_service">
				<span class="label-tooltip" data-toggle="tooltip" title="{l s='First instalment delay or date' mod='sequrapayment'}">
					{l s='First instalment delay or date' mod='sequrapayment'}:
				</span>
		</label>
		<div class="col-lg-9">
			<input type="text"
						id="sequra_desired_first_charge_date"
						class="form-control fixed-width-lg"
						name="sequra_desired_first_charge_date"
						value="{$sequra_desired_first_charge_date|htmlentitiesUTF8|default:''}"
						placeholder="Formato ISO8601"
						pattern="{$ISO8601_PATTERN}"
			/>
			<ol>
				<li>
					<strong>{l s='Fecha o plazo en el que se cobrará la primera cuota' mod='sequrapayment'}</strong>
				</li>
				<li>{l s='Ejeplo: Fecha como 2020-08-31, plazo como P3M15D (3 meses y 15 días)' mod='sequrapayment'}
			</ol>
			</p>
		</div>
	</div>
	{/if}
	{if $sequra_allow_registration_items}
	<div class="form-group" id="sequra_registration_amount_row">
		<label class="control-label col-lg-3" for="sequra_is_service">
				<span class="label-tooltip" data-toggle="tooltip" title="{l s='Registration amount' mod='sequrapayment'}">
					{l s='Registration amount' mod='sequrapayment'}:
				</span>
		</label>
		<div class="col-lg-9">
			<div class="input-group col-lg-2">
				<span class="input-group-addon"> €</span>
				<input type="text" maxlength="27" id="sequra_registration_amount" name="sequra_registration_amount" data-display-price-precision="6" class="form-control" value="{$sequra_registration_amount}">
			</div>
			<ol>
				<li>
					<strong>{l s='Parte del importe del producto que se pagará por adelantado' mod='sequrapayment'}</strong>
				</li>
			</ol>
			</p>
		</div>
	</div>
	{/if}
	<div class="panel-footer">
		<a href="{$link->getAdminLink('AdminProducts')|escape:'html':'UTF-8'}{if isset($smarty.request.page) && $smarty.request.page > 1}&amp;submitFilterproduct={$smarty.request.page|intval}{/if}"
			 class="btn btn-default"><i class="process-icon-cancel"></i> {l s='Cancel'}</a>
		<button type="submit" name="submitAddproduct" class="btn btn-default pull-right" disabled="disabled"><i
					class="process-icon-loading"></i> {l s='Save'}</button>
		<button type="submit" name="submitAddproductAndStay" class="btn btn-default pull-right" disabled="disabled"><i
					class="process-icon-loading"></i> {l s='Save and stay'}</button>
	</div>
</div>
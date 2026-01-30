<style>
	input[pattern]:invalid {
		background-color: #eab3b7;
	}
</style>
<div class="form-group">
	<label id="sequra_is_banned_label" for="sequra_is_banned" class="control-label col-lg-5">
		<span>
			{l s='Prevent this product from being paid by SeQura?:' mod='sequrapayment'}
		</span>
		<span class="help-box" data-toggle="popover"
			data-content="{l s='There are some kind of product that can not be sold with SeQura, like live animal, weapons or inlegal stuff.' mod='sequrapayment'}">
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
	<div class="col-lg-2">
		<input type="text"
					 id="sequra_service_end_date"
					 class="form-control fixed-width-lg"
					 name="sequra_service_end_date"
					 value="{$sequra_service_end_date|htmlentitiesUTF8|default:''}"
					 placeholder="Formato ISO8601"
					 pattern="{$ISO8601_PATTERN}"
		/>
	</div>
	<div class="col-lg-9">
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
<div class="form-group" id="sequra_desired_first_charge_date_row">
	<label class="control-label col-lg-3" for="sequra_is_service">
		<span>
			{l s='First instalment delay or date' mod='sequrapayment'}:
		</span>
		<span class="help-box" data-toggle="popover"
			data-title="{l s='Fecha o plazo en el que se cobrará la primera cuota' mod='sequrapayment'}"
			data-content="{l s='Ejeplo: Fecha como 2020-08-31, plazo como P3M15D (3 meses y 15 días)' mod='sequrapayment'}">
		</span>
	</label>
	<div class="col-lg-2">
		<input type="text"
					 id="sequra_desired_first_charge_date"
					 class="form-control fixed-width-lg"
					 name="sequra_desired_first_charge_date"
					 value="{$sequra_desired_first_charge_date|htmlentitiesUTF8|default:''}"
					 placeholder="Formato ISO8601"
					 pattern="{$ISO8601_PATTERN}"
		/>
	</div>
</div>
{/if}
{if $sequra_allow_registration_items}
<div class="form-group" id="sequra_registration_amount_row">
	<label class="control-label col-lg-5" for="sequra_is_service">
			<span class="label-tooltip" data-toggle="tooltip" title="{l s='Registration amount' mod='sequrapayment'}">
				{l s='Registration amount' mod='sequrapayment'}:
			</span>
			<span class="help-box" data-toggle="popover"
				data-content="{l s='Parte del importe del producto que se pagará por adelantado' mod='sequrapayment'}">
			</span>
	</label>
	<div class="col-lg-2">
		<div class="input-group money-type">
				<input type="text" id="sequra_registration_amount" name="sequra_registration_amount" data-display-price-precision="6" class="form-control" value="{$sequra_registration_amount}">
              <div class="input-group-append">
                <span class="input-group-text"> €</span>
            </div>
    	</div>
	</div>
</div>
{/if}
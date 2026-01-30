<h4>{l s='Sequra Extra Fields' mod='sequrapayment'}</h4>
<div class="separation"></div>
<table>
	<tr>
		<td class="col-left">
			<label>{l s='Is service:'}</label>
		</td>
		<td>
			<div class="input-group">
				<input type="checkbox"
							 {if $is_service}checked{/if}
							 id="is_service"
							 class="form-control"
							 name="is_service"
				onchage="updateSequraFields();">
			</div>
			<p class="preference_description">{l s='Service end date or period' mod='sequrapayment'}:
			<ol>
				<li>
					<strong>{l s='Fecha o plazo en el que se dará el curos por impartido o el servicio por prestado' mod='sequrapayment'}</strong>
				</li>
				<li>{l s='Ejeplo: Fecha como 2017-08-31, plazo como P3M15D (3 meses y 15 días)' mod='sequrapayment'}
				<li>{l s='Dejar vacío para si el producto no es un curso o servicio' mod='sequrapayment'}</li>
			</ol>
			</p>
		</td>
	</tr>
	<tr>
		<td class="col-left">
			<label>{l s='Service End Date:'}</label>
		</td>
		<td>
			<div class="input-group">
				<input type="text"
							 id="service_end_date"
							 class="form-control"
							 name="service_end_date"
							 value="{$service_end_date|htmlentitiesUTF8|default:''}"
							 data-maxchar="16"
							 placeholde="Formato ISO8601"
						{literal}
							 pattern="^((\d{4})-([0-1]\d)-([0-3]\d))+$|P(\d+Y)?(\d+M)?(\d+W)?(\d+D)?(T(\d+H)?(\d+M)?(\d+S)?)?$"
						{/literal}
							 maxlength="16">
			</div>
			<p class="preference_description">{l s='Service end date or period' mod='sequrapayment'}:
			<ol>
				<li>
					<strong>{l s='Fecha o plazo en el que se dará el curos por impartido o el servicio por prestado' mod='sequrapayment'}</strong>
				</li>
				<li>{l s='Ejeplo: Fecha como 2017-08-31, plazo como P3M15D (3 meses y 15 días)' mod='sequrapayment'}
				<li>{l s='Dejar vacío para si el producto no es un curso o servicio' mod='sequrapayment'}</li>
			</ol>
			</p>
		</td>
	</tr>
</table
{literal}
<script>
	function updateSequraFields(){
      if(document.getElementById("is_service").cheked){
          document.getElementById("service_end_date").disabled =false;
      }else{
          document.getElementById("service_end_date").disabled =true;
      }
  }
  updateSequraFields();
</script>
{/literal}

{if $sequra_error != ""}
    {include file="$module_views_dir/$sequra_error.tpl"}
{/if}
<div class="row">
    <div class="col-xs-12 col-md-6">
        <p class="payment_module" id="sequra_invoice">
            <a class="sequra invoicing" tile="{$service_name}">
                {$service_name}
                {if $fee == 0}
                    <span class="price_with_fee"><em>{l s="Sin costes adicionales" mod="sequrapayment"}</em></span>
                {else}
                    <span class="price_with_fee">{l s="por sólo %s€" sprintf=$fee mod="sequrapayment"}</span>
                {/if}
                <span id="sequra_invoice_method_link" class="sequra-educational-popup sequra_more_info"
                      data-amount="{$total_price*100}" data-product="{$sequra_product}"
                      rel="sequra_invoice_popup_checkout" title="Más información"><span class="sequra-more-info"> + info</span></span>
            </a>
        </p>
    </div>
</div>
<script type="text/javascript">
    jQuery(document).undelegate("#sequra_invoice .sequra", "click");
    jQuery(document).delegate("#sequra_invoice .sequra", "click", function () {
        SequraIdentificationPopupLoader.url = "{$ajax_form_url}";
        SequraIdentificationPopupLoader.product = "{$sequra_product}";
        SequraIdentificationPopupLoader.showForm();
    });
    Sequra.onLoad(function(){
        Sequra.refreshComponents();
    });
</script>

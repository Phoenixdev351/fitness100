<script>
Sequra.onLoad( function () {
  Sequra.refreshComponents();
});
</script>
<p>
{$payment_method['description'] nofilter}
{if !in_array($payment_method['product'],['fp1'])}
    <span id="sequra_info_link" class="sequra-educational-popup sequra_more_info"
    data-amount="{$total_price*100}" data-product="{$payment_method['product']}" data-campaign="{$payment_method['campaign']}"
    rel="sequra_invoice_popup_checkout" title="Más información"><span class="sequra-more-info">  {l s='+ info' mod='sequracheckout'}</span>
    </span>
{/if}
</p>

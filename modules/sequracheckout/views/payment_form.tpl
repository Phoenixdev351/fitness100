{if $sequra_error != ""}
    {include file="$module_views_dir/$sequra_error.tpl"}
{/if}
{foreach from=$payment_methods item=method}
<div class="row">
    <div class="col-xs-12">
        <p class="payment_module" id="sequra_{$method['product']}">
            <a class="sequra {$method['product']}" title="{$method['title']}" style='background-image:url({$method['icon']})'>
                {$method['long_title']}

                {if isset($method['cost_description'])}
                    <span id="sequra_cost_link_{$method['product']}_{$method['campaign']}" class="sequra-educational-popup sequra_cost_description"
                        data-amount="{$total_price*100}" data-product="{$method['product']}" data-campaign="{$method['campaign']}"
                        rel="sequra_invoice_popup_checkout" title="Más información">
                        <span class="sequra-cost">{$method['cost_description']}</span>
                    </span>
                {/if}
                <span>
                    {if isset($method['claim']) && $method['claim']}
                        <br/>
                        {$method['claim']}
                    {/if}
                    {if !in_array($method['product'],['fp1'])}
                        <span id="sequra_info_link" class="sequra-educational-popup sequra_more_info"
                        data-amount="{$total_price*100}" data-product="{$method['product']}" data-campaign="{$method['campaign']}"
                        rel="sequra_invoice_popup_checkout" title="Más información"><span class="sequra-more-info"> + info</span>
                        </span>
                    {/if}
                </span>
            </a>
        </p>
    </div>
</div>
<script type="text/javascript">
    jQuery(document).undelegate("#sequra_{$method['product']} .sequra", "click");
    jQuery(document).delegate("#sequra_{$method['product']} .sequra", "click", function () {
        SequraIdentificationPopupLoader.url = "{$ajax_form_url}";
        SequraIdentificationPopupLoader.product = "{$method['product']}";
        SequraIdentificationPopupLoader.campaign = "{$method['campaign']}";
        SequraIdentificationPopupLoader.showForm();
    });
    Sequra.onLoad(function(){
        Sequra.refreshComponents();
    });
</script>
{/foreach}

{if $sequra_error != ""}
    {include file="$module_views_dir/$sequra_error.tpl"}
{/if}
{foreach from=$payment_methods item=method}
    <p class="payment_module" id="sequra_{$method['product']}">
        <a class="link_sequra_{$method['product']}" href="javascript:SequraIdentificationPopupLoader.opcShowForm('{$ajax_form_url}','{$method['product']}','{$method['campaign']}');//"
            {if $opc_module eq "onepagecheckoutps"}
                title="{$method['title']} {if !in_array($method['product'],['fp1'])}&lt;span id=&quot;sequra_{$method['product']}_{$method['campaign']}_method_link&quot; class=&quot;sequra-educational-popup&quot; data-amount=&quot;{$total_price*100}&quot; data-campaign=&quot;{$method['campaign']}&quot; data-product=&quot;{$method['product']}&quot;&gt;+ info&lt;/i&gt;&lt;/span&gt;{/if}">
            {else}
                class="sequra" title="{$method['title']}">
                <img src="{$method['icon']}" height="33">
                {$method['title']}
                {if !in_array($method['product'],['fp1'])}
                    <span id="sequra_{$method['product']}_{$method['campaign']}_method_link" class="sequra-educational-popup" data-amount="{$total_price*100}" data-campaign="{$method['campaign']}" data-product="{$method['product']}"><span class="sequra-more-info"> + info</span></span>
                {/if}
                <br/>
            {/if}
            <span>
                {if isset($method['claim']) && $method['claim']}
                    <br/>
                    {$method['claim']}
                {/if}
            </span>
        </a>
    </p>
    <script type="text/javascript">
    setTimeout(function() {
        SequraHelper.waitForElememt("#payment_method_container img[title*='{$method['title']}']").then(function() {
            var img = document.querySelector("#payment_method_container img[title*='{$method['title']}']");
            img.title = "{$method['title']}";
            img.src = "{$method['icon']}";
            img.style.margin = '0px';
            img.style.padding = '0px';
            img.style.border = '0px';
            Sequra.refreshComponents();
        });},
        1500
    );
    </script>
{/foreach}

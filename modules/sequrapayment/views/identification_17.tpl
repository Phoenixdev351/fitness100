{extends file='page.tpl'}
{block name='page_content'}
    {if isset($error)}
        <div style="background-color: #FAE2E3;border: 1px solid #EC9B9B;line-height: 20px;margin: 0 0 10px;padding: 10px 15px;">{$error}</div>{/if}

    {if isset($nbProducts) && $nbProducts <= 0}
        <p class="warning">{l s='Your shopping cart is empty.'}</p>
    {else}

        {$identity_form nofilter}
        <script type="text/javascript">
            (function () {
                var sequraCallbackFunction = function () {
                    history.go(-1);
                };
                window.SequraFormInstance.setCloseCallback(sequraCallbackFunction);
                window.SequraFormInstance.show();
            })();
        </script>
    {/if}
{/block}
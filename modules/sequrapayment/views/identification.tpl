{capture name=path}{$service_name}{/capture}
{if $render_breadcrumbs_explicitly}
    {include file="$tpl_dir./breadcrumb.tpl"}
    {assign var='current_step' value='payment'}
    {include file="$tpl_dir./order-steps.tpl"}
{/if}

{if isset($error)}
    <div style="background-color: #FAE2E3;border: 1px solid #EC9B9B;line-height: 20px;margin: 0 0 10px;padding: 10px 15px;">{$error}</div>{/if}

{if isset($nbProducts) && $nbProducts <= 0}
    <p class="warning">{l s='Your shopping cart is empty.'}</p>
{else}

    {$identity_form nofilter}
    <script type="text/javascript">
        (function () {
            if (jQuery('#header')) jQuery('#header').css("z-index", 1);//Header has z-index 10 in ps 1.5 and appear on top of the overlay.
            var sequraCallbackFunction = function () {
                history.go(-1);
            }
            window.SequraFormInstance.setCloseCallback(sequraCallbackFunction);
            jQuery('[id^=sq-identification]').appendTo('body');
            window.SequraFormInstance.show();
        })();
    </script>
{/if}

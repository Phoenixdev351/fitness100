{*
 * Facebook Conversion Pixel Tracking Plus
 *
 * NOTICE OF LICENSE
 *
 * @author    Pol Rué
 * @copyright Smart Modules 2014
 * @license   One time purchase Licence (You can modify or resell the product but just one time per licence)
 * @version 2.3.3
 * @category Marketing & Advertising
 * Registered Trademark & Property of smart-modules.com
 *
 * ***************************************************
 * *     Facebook Conversion Trackcing Pixel Plus    *
 * *          http://www.smart-modules.com           *
 * *                     V 2.3.3                     *
 * ***************************************************
 *
*}

{if isset($oldps) && $oldps == 1}
<fieldset id="configuration_fieldset">
<legend>{l s='Test Pixels' mod='facebookconversiontrackingplus'}</legend>
{else}
    <div id="fcp2" class="panel">
    <div class="panel-heading"><i class="icon-facebook"> </i> {l s='Test Pixels' mod='facebookconversiontrackingplus'}</div>
{/if}
    <div class="col-lg-12">
        <h2>{l s='Testing Pixels' mod='facebookconversiontrackingplus'}</h2>
        <div class="row">
            <div class="col-lg-1">
                <img src="../modules/facebookconversiontrackingplus/views/img/pixel-helper-icon-64.png" alt="{l s='Pixel Helper for Google Chrome' mod='facebookconversiontrackingplus'}"/>
            </div>
            <div class="col-lg-11">
                <p>{l s='We do recommend installing' mod='facebookconversiontrackingplus'} <a href="https://chrome.google.com/webstore/detail/fb-pixel-helper/fdgfkebogiimcoedlicjlajpkdmockpc" target="_blank">Pixel Helper</a> {l s='for Google Chrome to test Facebook Pixels. Also note that pixels won\'t reflect immediatlely on your Facebook account, it may take some time' mod='facebookconversiontrackingplus'} ({l s='it\'s updated every 6 hours' mod='facebookconversiontrackingplus'}).</p>
            </div>
        </div>
        <br>
        <div class="form-video-wrapper"><div class="form-video"><iframe width="560" height="420" src="https://www.youtube-nocookie.com/embed/fjbO2RA-OTc" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div></div>
        <br>
        <p>{l s='To test pixels you should install Pixel Helper, an extension for Google Chrome' mod='facebookconversiontrackingplus'}. {l s='This extension can tell you what information is being passed to Facebook from your pixels' mod='facebookconversiontrackingplus'}.</p>
        <p>{l s='Most of the events fire immediately a pixel once the page loads, but there are two special cases' mod='facebookconversiontrackingplus'} AddToCart {l s='and' mod='facebookconversiontrackingplus'} AddToWhishlistTo {l s='If you activate the tracking for those events, and you have ajax enabled you will see a red message on Pixel Helper saying' mod='facebookconversiontrackingplus'} "Facebook Pixel did not load" {l s='that\'s because nobody added any product to the cart/wishlist yet' mod='facebookconversiontrackingplus'} .{l s='once you hit the add to cart / whishlist button the message will problably turn blue saying' mod='facebookconversiontrackingplus'} "Facebook Pixel took too long to load" {l s=' thats because it took the time the user needed to hit the button' mod='facebookconversiontrackingplus'}.</p>
        <p>{l s='So either a green or a blue message is a success message from Facebook and all necessary information has been passed to it' mod='facebookconversiontrackingplus'}.</p>
        <p><a href="#faq" title="{l s='Frequently Asked Questions' mod='facebookconversiontrackingplus'}">{l s='If you have any doubts, please check our FAQ' mod='facebookconversiontrackingplus'}</a>.</p>
    <br><br>
    <h3>{l s='Fire test events' mod='facebookconversiontrackingplus'}</h3>
    <p>{l s='You may need to spend some time until Facebook verifies your pixels, ex. waiting for a conversion, fire an add to cart event' mod='facebookconversiontrackingplus'}... {l s='Here you can send test pixels to Facebook' mod='facebookconversiontrackingplus'}</p>
    {if $pixelsetup}
    <ul id="test_pixels">
    <li><a href="#" class="firePixel" data-type="ViewContent">ViewContent</a></li>
    <li><a href="#" class="firePixel" data-type="Purchase">Purchase</a></li>
    <li><a href="#" class="firePixel" data-type="Search">Search</a></li>
    <li><a href="#" class="firePixel" data-type="AddToCart">AddToCart</a></li>
    <li><a href="#" class="firePixel" data-type="AddToWishlist">AddToWishlist</a></li>
    <li><a href="#" class="firePixel" data-type="InitiateCheckout">InitiateCheckout</a></li>
    <li><a href="#" class="firePixel" data-type="AddPaymentInfo">AddPaymentInfo</a></li>
    <li><a href="#" class="firePixel" data-type="CompleteRegistration">CompleteRegistration</a></li>
    </ul>

    <!-- Basic Pixel Loading -->
    <style>
    #test_pixels {
        display:block;
        width:100%;
        padding:0;
        margin-top:20px;
    }
    #test_pixels li {
        display:inline-block;
    }
    #test_pixels li a {
        padding: 10px 16px;
        margin: 0 2px;
        border-radius: 3px;
        border: 1px solid #ccc;
        text-align: center;
        line-height: 50px;
    }
    #test_pixels li a:hover, .fired {
        background:#00aff0;
        color:#fff !important;
    }
    </style>
    <script>
    {literal}
    !function(f,b,e,v,n,t,s){if (f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if (!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','//connect.facebook.net/en_US/fbevents.js');
    {/literal}
    {foreach from=$fctpid item="pixel_id" name="pixelforeach"}
    {literal}fbq('init', '{/literal}{$pixel_id|strip|escape:'htmlall':'UTF-8'}{literal}');
    {/literal}
    {/foreach}
    {literal}
    fbq('track', 'PageView');
    $(document).ready(function() {
        var number = 1;
        $('#fcp2').after('<div id="fb_messages" style="position:absolute; left:35%; width:50%; z-index:100;"></div>');
        $(".firePixel").click(function(e) {
            var type = $(this).data('type');
            var values = [];
            values['content_name'] = '{/literal}{$fctp_test_values.name|escape:'htmlall':'UTF-8'}{literal}'+' test'; // type +' test'
            values['content_type'] = 'product';
            if (type != 'CompleteRegistration') {
                values['content_ids'] = [{/literal}{$fctp_test_values.id_product|intval}{literal}];
            }
            if (type == 'Purchase') {
                values['num_items'] = '1';
            }
            if (type == 'Search') {
                values['search_string'] = '{/literal}{l s='This is an example of a search' mod='facebookconversiontrackingplus'}{literal}';
            }
            values['value'] = '{/literal}{$fctp_test_values.price|floatval}{literal}';
            values['currency'] = '{/literal}{$currency|escape:'htmlall':'UTF-8'}{literal}';
            values['product_catalog_id'] = '{/literal}{$product_catalog_id|escape:'htmlall':'UTF-8'}{literal}';
            fbq('track', type, values);
            e.preventDefault();
            $("#fb_messages").append('<div id="fb_message_'+number+'" class="module_confirmation conf confirm alert alert-success"><button type="button" class="close" data-dismiss="alert">×</button>'+type+' {/literal}{l s='Pixel Fired' mod='facebookconversiontrackingplus'}{literal}</div>');
            $("#fb_message_"+number).delay(1500).fadeOut(500);
            number++;
            $(this).addClass('fired');
        });
    }); 
    {/literal}
    </script>
    {else}
    <p>{l s='Insert a Facebook Pixel ID to be able to test the pixels' mod='facebookconversiontrackingplus'} </p>
    {/if}
    </div>
    <div class="clearfix"></div>
{if isset($oldps) && $oldps == 1}
</fieldset>
<br />
<br />
<br />
<br />

{else}
</div>
{/if}
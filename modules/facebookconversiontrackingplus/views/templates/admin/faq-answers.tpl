{*
 * Pixel Plus: Events + API + Pixel Catalogue for Prestashop
 *
 * NOTICE OF LICENSE
 *
 * @author    Pol Rué
 * @copyright Smart Modules 2016
 * @license   One time purchase Licence (You can modify or resell the product but just one time per licence)
 * @version 2.3.3
 * @category Advertising & Marketing
 * Registered Trademark & Property of smart-modules.com
 *
 * ****************************************************
 * *                    Pixel Plus                    *
 * *          http://www.smart-modules.com            *
 * *                     V 2.3.3                      *
 * ****************************************************

{* FAQ Answers *}
{capture name=all_yellow assign=all_yellow}
<p>{l s='When this happens, there is a 99% chance that you are using an adBlocker, disable the ad blocker and refresh for your site and events should turn green.' mod='facebookconversiontrackingplus'}</p>
{/capture}

{$faq['all_yellow']['answer'] = $all_yellow scope='global'}

{capture name=some_events_yellow assign=some_events_yellow}
<p>{l s='First of all, open the error/warning to see more information and read it.' mod='facebookconversiontrackingplus'}</p>
<p>{l s='Once you read the message, if you still have doubts check the following FAQ where the most common error/warnings are described' mod='facebookconversiontrackingplus'}</p>
{/capture}

{$faq['some_events_yellow']['answer'] = $some_events_yellow scope='global'}

{capture name=dynamic_event assign=dynamic_event}
<p><u>We detected event code but the pixel has not activated for this event, so no information was sent to Facebook. <br>
   This could be due to an error in the code, but could also occur if the pixel fires on a dynamic event such as a button click</u></p>
<p>{l s='This is the most common message you will see, it is because the module has several events that are dynamic and those events will not be sent to Facebook unless some action happens.' mod='facebookconversiontrackingplus'}. {l s='Some examples are:' mod='facebookconversiontrackingplus'}</p>

<ul>
    <li><u>{l s='Add To Cart' mod='facebookconversiontrackingplus'}</u>: {l s='unless a product is added to the cart it makes no sense to send the data to Facebook' mod='facebookconversiontrackingplus'}</li>

    <li><u>{l s='Add To Wishlist' mod='facebookconversiontrackingplus'}</u>:{l s='Idem, but for wishlist products' mod='facebookconversiontrackingplus'}</li>

    <li><u>{l s='CompleteRegistration' mod='facebookconversiontrackingplus'}</u>:{l s='It fires dynamically after a customer registers, it can be hard to see with the Pixel Helper' mod='facebookconversiontrackingplus'}</li>

    <li><u>{l s='Initiate Checkout' mod='facebookconversiontrackingplus'}</u>:{l s='When the purchase process starts, sometimes it can be hard to see with Pixel Helper' mod='facebookconversiontrackingplus'}</li>

    <li><u>{l s='Add Payment Method' mod='facebookconversiontrackingplus'}'></u>:{l s='When the user selects what payment method to use. Since 3rd party pages can\'t be tracked, like Paypal the module sends the event when the payment method is selected' mod='facebookconversiontrackingplus'}</li>
</ul>
,
<p>{l s='There is an alternative to the %s to check the Pixel events. It\'s called %s' sprintf=['Pixel Helper', {l s='Test Events' mod='facebookconversiontrackingplus'}] mod='facebookconversiontrackingplus'}
    {l s='This feature can be accessed by going to FB Ads Manager > Pixel > Details > Test Events' mod='facebookconversiontrackingplus'}</p>
{/capture}

{$faq['dynamic_event']['answer'] = $dynamic_event scope='global'}

{capture name=wrong_catalogue_id assign=wrong_catalogue_id}
<p><u>The specified product catalog ID is not valid.
    Please check that the product_catalog_id parameter is filled out correctly</u></p>
<p>{l s='When you set the catalogue ID, sometimes the ID is not the right one.' mod='facebookconversiontrackingplus'}</p>
<p>{l s='To get the Catalogue ID / Catalogue Number go to:' mod='facebookconversiontrackingplus'}</p>

<ol>
    <li>{l s='Facebook Ads Manager' mod='facebookconversiontrackingplus'}</li>

    <li>{l s='Catalogue' mod='facebookconversiontrackingplus'}</li>

    <li>{l s='Under each catalogue you will see a long number, this is the Catalogue ID' mod='facebookconversiontrackingplus'}</li>

    <li>{l s='Copy it and go to the module configuration page' mod='facebookconversiontrackingplus'}</li>

    <li>{l s='Click on the Feeds section and enter the catalogue ID in the corresponding field' mod='facebookconversiontrackingplus'}</li>
</ol>
{/capture}

{$faq['wrong_catalogue_id']['answer'] = $wrong_catalogue_id scope='global'}

{capture name=purchase_duplicates assign=purchase_duplicates}
<p>{l s='The module has a triple layer screening to prevent duplicates, but there are several causes that may prevent Facebook from having the exact amount of Purchase events.' mod='facebookconversiontrackingplus'}</p>
<p><strong>{l s='Missing Purchase events:' mod='facebookconversiontrackingplus'}</strong></p>
<p>{l s='This is usually related to the use of AdBlockers, unfortunately the module can\'t do much more in this situation.' mod='facebookconversiontrackingplus'}</p>
<p>{l s='Also, Payment Modules loading an intermediate page may cause this. In this case you just need to enable the advanced option "%s"' sprintf=[{l s='Use Ajax to confirm the conversion is sent' mod='facebookconversiontrackingplus'}] mod='facebookconversiontrackingplus'}</p>
<p><strong>{l s='Duplicate Purchase events:' mod='facebookconversiontrackingplus'}</strong></p>
<p>{l s='This is usually caused by cache engines like non-native Cache Modules or third party software like CloudFlare.' mod='facebookconversiontrackingplus'}</p>
<p>{l s='By default, the module does not relay on the thank you page to display the Purchase event, this makes it compatible with any payment module, regardless of the returning page after the payment, but the cache engines trend to cache the output of the module, making it repeat when not necessary.' mod='facebookconversiontrackingplus'}</p>

<p>{l s='When this happens, enable the advanced option "%s". This should greately mitigate or even fully resolve the quantity of duplicated events.' sprintf=[{l s='Force Basic Mode' mod='facebookconversiontrackingplus'}] mod='facebookconversiontrackingplus'}</p>
<p>{l s='But some caches are quite heavy and may be ignoring the triple screen the module applies' mod='facebookconversiontrackingplus'}</p>
{/capture}

{$faq['purchase_duplicates']['answer'] = $purchase_duplicates scope='global'}

{capture name=custom_events assign=custom_events}
<p>{l s='Sure! The Facebook Pixel is initialized on the page header, that means you can trigger (send) an event on any page. This is useful when you want to add the Lead event, for example by binding it to a certain button click' mod='facebookconversiontrackingplus'}</p>
{/capture}

{$faq['custom_events']['answer'] = $custom_events scope='global'}
{* END FAQ Answers *}
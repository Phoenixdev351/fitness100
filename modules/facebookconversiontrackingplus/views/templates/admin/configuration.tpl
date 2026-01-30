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
        <legend>{l s='Basic module information' mod='facebookconversiontrackingplus'}</legend>
    {else}
        <div id="fcp" class="panel">
            <div class="panel-heading"><i class="icon-info"> </i>
                {l s='Basic module information' mod='facebookconversiontrackingplus'}</div>
        {/if}
        <div class="row">
            <div class="col-lg-12">
                <h2>{l s='How this module works?' mod='facebookconversiontrackingplus'}</h2>
                <p>{l s='This module allows you to [1]track Facebook events through the Pixel and the Covnersions API[/1]' mod='facebookconversiontrackingplus' tags=['<strong>', '</strong>']}.
                    {l s='It also allows you to [1]create a product catalgue based on the Pixel Events[/1]' mod='facebookconversiontrackingplus' tags=['<strong>', '</strong>']}.
                </p>
                <p>{l s='The module also allows EU users to prevent data from being sent unless consent is obtained' mod='facebookconversiontrackingplus'}.
                    {l s='Go to the section [1]GDPR & Cookies consent[/1] to configure this option' mod='facebookconversiontrackingplus' tags=['<strong>', '</strong>']}.
                </p>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6">
                <h3 class="modal-title text-info">{l s='Pixel ID' mod='facebookconversiontrackingplus'}</h3>
                <p>{l s='Configuring the Pixel ID is a requirement for the module to work, since it [1]allows you to send events through the Pixel and work with the Conversions API.[/1]' mod='facebookconversiontrackingplus' tags=['<strong>', '</strong>']}.
                </p>
                <p>{l s='Simply go to the Events Manager to receive your pixel ID' mod='facebookconversiontrackingplus'}.
                </p>
                <p>{l s='Click on the pixel you wish to work with, and you will notice a long number on the right' mod='facebookconversiontrackingplus'}.
                </p>
                <p>{l s='This is the Pixel ID' mod='facebookconversiontrackingplus'}.</p>
                <p>{l s='Click on the number to copy it' mod='facebookconversiontrackingplus'}.</p>
                {l s='Return back to the module and paste it into the %s field to proceed' mod='facebookconversiontrackingplus' sprintf=[{l s='Facebook Pixel\'s ID' mod='facebookconversiontrackingplus'}]}.
                </p>
                <hr>

                <h3 class="modal-title text-info">{l s='Conversions API' mod='facebookconversiontrackingplus'}</h3>
                <p>{l s='[1]The process is similar to that used to acquire the Pixel ID[/1]' mod='facebookconversiontrackingplus' tags=['<strong>', '</strong>']}.
                    {l s='Go to [1]%s[/1]' mod='facebookconversiontrackingplus' tags=['<a href="https://business.facebook.com/events_manager2" target="_blank">', '</a>'] sprintf=['Events Manager']}.
                </p>
                <p>{l s='Select your pixel' mod='facebookconversiontrackingplus'}.</p>
                <p>{l s='Click on manage integrations button on the right of the screen' mod='facebookconversiontrackingplus'}.
                </p>
                <p>{l s='Click on "Conversions API"' mod='facebookconversiontrackingplus'}.</p>
                <p>{l s='Scroll down until you see a button that says "Generate Token"' mod='facebookconversiontrackingplus'}.
                </p>
                <p>{l s='Click on it to generate a token and, click on it again to copy it' mod='facebookconversiontrackingplus'}
                </p>
                <p>{l s='Once you have copied it, return to the module and paste it in the "%s" section' mod='facebookconversiontrackingplus' sprintf=[{l s='Conversions API' mod='facebookconversiontrackingplus'}]}.
                </p>
                <hr>

                <h3 class="modal-title text-info">{l s='Custom Audiences?' mod='facebookconversiontrackingplus'}</h3>
                <p>{l s='Custom Audiences is the most basic type of retargeting, and it continues to grow as long as your site receives visitors' mod='facebookconversiontrackingplus'}.
                </p>
                <p><strong>{l s='There are two types of audiences which are            ' mod='facebookconversiontrackingplus'}:</strong>
                </p>
                <ul>
                    <li><strong>{l s='Visitors' mod='facebookconversiontrackingplus'}</strong></li>
                    <li><strong>{l s='Customers' mod='facebookconversiontrackingplus'}</strong></li>
                </ul>
                <br />
                <strong>{l s='Visitors' mod='facebookconversiontrackingplus'}</strong>
                <p>{l s='Following the setup of your Pixel ID, Facebook will begin developing your audience, which will be utilized in your remarketing / retargeting campaigns later' mod='facebookconversiontrackingplus'}.
                </p>
                <p>{l s='With each visit to your website, your audiences will grow (if they are logged onto Facebook)' mod='facebookconversiontrackingplus'}.
                </p>
                <p>{l s='You can start generating your own Custom Audiences from your Audience data once your audience has grown large enough. You can either build a huge audience or a targeted audience (for example people who have visited the XXX category)' mod='facebookconversiontrackingplus'}.
                </p>
                <p>{l s='You also have the option of creating as many Custom Audiences as you like' mod='facebookconversiontrackingplus'}.
                </p>
                <p>{l s='To access Audiences, go to Ads Manager > Tools > Audiences' mod='facebookconversiontrackingplus'}.
                </p>
                <p>{l s='After creating audiences, you can use them in the "Audiences" section when developing ad campaigns' mod='facebookconversiontrackingplus'}.
                </p>
                <p>{l s='There is a tool called "Lookalike Audiences" that produces an audience by choosing people who share your of behavior and preferences, allowing you to target your %s advertising campaigns to potential customers' mod='facebookconversiontrackingplus' sprintf=[{l s='audience\'s patterns ' mod='facebookconversiontrackingplus'}]}.
                </p>

                <p><strong>{l s='More information is available on' mod='facebookconversiontrackingplus'}</strong> <a
                        href="https://www.facebook.com/business/help/341425252616329" target="_blank"
                        title="{l s='What are the custom audiences' mod='facebookconversiontrackingplus'}">{l s='Facebook' mod='facebookconversiontrackingplus'}</a>
                </p>
                <p>&nbsp;</p>
                <p><strong>{l s='Customers' mod='facebookconversiontrackingplus'}:
                    </strong>{l s=' Export your customers to a CSV file, which you can then import to Facebook' mod='facebookconversiontrackingplus'}.
                </p>
                <ul>
                    <li><a
                            href="{$export_customer_url|escape:'htmlall':'UTF-8'}&typexp=1">{l s='Export Customers' mod='facebookconversiontrackingplus'}</a>
                    </li>
                    {if $newsletter == 1}
                        <li><a
                                href="{$export_customer_url|escape:'htmlall':'UTF-8'}&typexp=2">{l s='Export Newsletter Users' mod='facebookconversiontrackingplus'}</a>
                        </li>
                        <li><a
                                href="{$export_customer_url|escape:'htmlall':'UTF-8'}&typexp=3">{l s='Export All' mod='facebookconversiontrackingplus'}</a>
                        </li>
                    </ul>
                {else}
                    </ul>
                    <p>{l s='You may also export your newsletter subscribers to create an Audience if you activate and use Prestashop\'s BlockNewsletter module' mod='facebookconversiontrackingplus'}
                    </p>
                {/if}
            </div>
            <div class="col-lg-6">
                <h3 class="modal-title text-info">{l s='Creating a catalogue' mod='facebookconversiontrackingplus'}</h3>
                <p>{l s='You may also create an event-based catalogue using the Pixel Plus module. Catalogues of this type are simple to build and manage. In truth, all you need to do is provide the right microdata and get visitors to view your products' mod='facebookconversiontrackingplus'}.
                </p>
                <br>
                <h4 class="modal-title text-info">
                    <strong>{l s='The Microdata' mod='facebookconversiontrackingplus'}</strong>
                </h4>
                <p>{l s='To successfully create an event-based catalogue, you will need to have the right microdata' mod='facebookconversiontrackingplus'}.
                </p>
                <p>{l s='Go to the module\'s microdata section and activate the second selection once, then save.' mod='facebookconversiontrackingplus'}.
                </p>
                <p>{l s='Return to the microdata section to see which data is missing' mod='facebookconversiontrackingplus'}.
                </p>
                <p>{l s='Select the first option if you want the module to fill in the missing microdata' mod='facebookconversiontrackingplus'}.
                </p>
                <br>

                <h4 class="modal-title text-info">
                    <strong>{l s='Creating the catalogue and adding the pixel as a source' mod='facebookconversiontrackingplus'}</strong>
                </h4>
                <p>{l s='The next step is to build the catalogue; to do so, go to the [1]%s[/1]' mod='facebookconversiontrackingplus' tags=['<a href="https://business.facebook.com/commerce/" target="_blank">', '</a>'] sprintf=['Commerce Manager']}
                </p>
                <p>{l s='If you don\'t have a catalogue, you will need to create one. You can create a shop or a catalogue, depending on how you want to use it' mod='facebookconversiontrackingplus'}.
                </p>
                <p>{l s='Whatever you select, it will be the same in our opinion' mod='facebookconversiontrackingplus'}.
                </p>
                <p>{l s='After you\'ve created the catalogue/shop, go to the "source" or "data source" option and select it.' mod='facebookconversiontrackingplus'}.
                </p>
                <p>{l s='Click on Add items > Add Multiple Items' mod='facebookconversiontrackingplus'}.</p>
                <p>{l s='Select "Pixel" from the list of choices' mod='facebookconversiontrackingplus'}.</p>
                <p>{l s='You will see a list of all your pixels to choose from' mod='facebookconversiontrackingplus'}.
                </p>
                <p>{l s='If you see the words "not ready" next to your pixel, it indicates that you will have to wait a little longer' mod='facebookconversiontrackingplus'}.
                </p>
                <p>{l s='Once you\'ve added the necessary microdata to your page, Facebook will hold off for a while (up to 48h to validate the pixel)' mod='facebookconversiontrackingplus'}.
                </p>

                <p>{l s='You may speed up the process by browsing some products and performing some activities on your website' mod='facebookconversiontrackingplus'}.
                </p>

                <p>{l s='When the pixel is ready, you will be able to use it as a product source' mod='facebookconversiontrackingplus'}.
                </p>

                <p>{l s='Event based catalogues works based on the ViewContent event (the event that is sent when you visit a product). As a result, as soon as they receive visitors, the products will be added to the catalogue' mod='facebookconversiontrackingplus'}.
                </p>
                <br>

                <h4 class="modal-title text-info">
                    <strong>{l s='Google Category Association' mod='facebookconversiontrackingplus'}</strong>
                </h4>
                <p>{l s='Google developed a category structure in order to have a standardized category system' mod='facebookconversiontrackingplus'}.
                </p>
                <p>{l s='Several platforms, like Facebook, use this framework to better understand where your products belong' mod='facebookconversiontrackingplus'}
                </p>
                <p>{l s='To establish a link between your categories and those of Google, look for the section "%s"' sprintf=[{l s='Google Category Association' mod='facebookconversiontrackingplus'}]
                    mod='facebookconversiontrackingplus'}.</p>
                <div class="clearfix"></div>
            </div>
        </div>
        {if isset($oldps) && $oldps == 1}
    </fieldset>
    <br />

{else}
    </div>
{/if}
<script>
    var selected_menu = '{$selected_menu|escape:'htmlall':'UTF-8'}';

    window.onmousedown = function(e) {
        var el = e.target;
        if (el.tagName.toLowerCase() == 'option' && el.parentNode.hasAttribute('multiple')) {
            e.preventDefault();

            // toggle selection
            if (el.hasAttribute('selected')) el.removeAttribute('selected');
            else el.setAttribute('selected', '');

            // hack to correct buggy behavior
            var select = el.parentNode.cloneNode(true);
            el.parentNode.parentNode.replaceChild(select, el.parentNode);
        }
    }
</script>
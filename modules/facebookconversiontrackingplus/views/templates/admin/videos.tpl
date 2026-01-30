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
<legend>{l s='How to Videos' mod='facebookconversiontrackingplus'}</legend>
{else}
    <div id="videos" class="panel">
    <div class="panel-heading"><i class="icon-play"> </i> {l s='How to Videos' mod='facebookconversiontrackingplus'}</div>
{/if}
    <div class="col-lg-12">
        <h2>{l s='Installing the Pixel Plus module' mod='facebookconversiontrackingplus'}</h2>
        <div class="form-video-wrapper">
            <div class="form-video">
                <iframe width="560" height="420" src="https://www.youtube-nocookie.com/embed/KpuiRTUjGdM" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
        </div>
        <h2>{l s='Testing the events with the Pixel Helper' mod='facebookconversiontrackingplus'}</h2>
        <div class="form-video-wrapper">
            <div class="form-video">
                <iframe width="560" height="420" src="https://www.youtube-nocookie.com/embed/fjbO2RA-OTc" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
{if isset($oldps) && $oldps == 1}
</fieldset>
{else}
</div>
{/if}
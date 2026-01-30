{*
 * Facebook Products Feed catalogue export for Prestashop
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
 * ****************************************
 * *        Facebook Products Feed        *
 * *   http://www.smart-modules.com       *
 * *               V 2.3.3                *
 * ****************************************
*}

{include file='./faq-answers.tpl'}

{if $old_ps}
<fieldset id="faq"><legend>{l s='FAQ' mod='facebookconversiontrackingplus'}</legend>
{else}
<div id="faq" class="panel">
    <div class="panel-heading">
        <i class="icon-question-circle"></i> {l s='FAQ' mod='facebookconversiontrackingplus'}
    </div>
{/if}
    <h2 class="text-primary">{l s='FAQ' mod='facebookconversiontrackingplus'} - {l s='Frequently Asked Questions' mod='facebookconversiontrackingplus'}</h2>
    <div class="{if $old_ps}hint{else}alert alert-info{/if}">
        <p>{l s='Click on any question to display more information' mod='facebookconversiontrackingplus'}</p>
    </div>
    <div id="accordion">
    {foreach from=$faq name=faqs item=item}
        <div class="card">
            <div class="card-header" id="heading{$smarty.foreach.faqs.index|intval}">
                <h4><a class="btn-link{*if $smarty.foreach.faqs.index == 0*} collapsed{*/if*}" data-toggle="collapse" data-target="#collapse{$smarty.foreach.faqs.index|intval}" aria-expanded="{*if $smarty.foreach.faqs.index == 0}true{else*}false{*/if*}" aria-controls="collapse{$smarty.foreach.faqs.index|intval}">{$item.question|escape:'htmlall':'UTF-8'}</a></h4>
            </div>
            <div id="collapse{$smarty.foreach.faqs.index|intval}" class="collapse{*if $smarty.foreach.faqs.index == 0} in{/if*}" aria-labelledby="heading{$smarty.foreach.faqs.index|intval}" data-parent="#accordion">
            <div class="card-body">
                {if isset($item.image)}
                <div class="col-lg-7 col-sm-12 pull-right text-center">
                    <img src="{$img_path|escape:'htmlall':'UTF-8'}{$item.image|escape:'htmlall':'UTF-8'}" class="img-res" />
                </div>
                {/if}
                {$item.answer nofilter} {* Response has HTML, can't be escaped *}
            </div>
            </div>
        </div>
        <br>
    {/foreach}
    </div>
{if $old_ps}
</fieldset>
{else}
</div>
{/if}

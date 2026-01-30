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
{assign var="skip_header" value=(($tree_type == 'google_categories' || $tree_type == 'exclude') && $is_ajax == true)}
{function name=printTree}
    {foreach from=$fpf_catTree item=node}
        <li class="tree-item{if isset($node->disabled) && $node->disabled == true} tree-item-disable{/if}">
            <span class="{if isset($node->childs)}tree-folder-name{else}tree-item-name{/if}{if isset($node->disabled) && $node->disabled == true} tree-item-name-disable{/if}">
                <span class="item-name">
                {if $tree_type != 'age_group' && $tree_type != 'gender'}
                <input type="checkbox" name="{$input_name|escape:'htmlall':'UTF-8'}[]" value="{$node->id_category|intval}"{if isset($node->disabled) && $node->disabled == true} disabled="disabled"{/if} {if isset($google_cat[$node->id_category].excluded) && $google_cat[$node->id_category].excluded == 1}checked="checked"{/if}/>
                {/if}
                <i class="{if isset($node->childs)}icon-folder-close{else}tree-dot{/if}"></i>
                <label class="tree-toggler">{$node->name|escape:'htmlall':'UTF-8'}</label>
                </span>
                {if $tree_type == 'google_categories'}
                    <input type="text" class="tree_cat_input autotomplete" name="google_cat[{$node->id_category|intval}]" value="{if isset($google_cat[$node->id_category].name)}{$google_cat[$node->id_category].name|escape:'htmlall':'UTF-8'}{/if}"{if isset($node->disabled) && $node->disabled == true} disabled="disabled"{/if} />
                    <input type="hidden" class="autocomplete-id" name="google_cat_id[{$node->id_category|intval}]" value="{*if isset($google_cat[$node->id_category].id)}{$google_cat[$node->id_category].id|intval}{/if*}"{if isset($node->disabled) && $node->disabled == true} disabled="disabled"{/if} />
                {elseif $tree_type == 'age_group'}
                    <select class="fixed-width-xl tree_cat_input" name="age_group[{$node->id_category|intval}]">
                        {foreach from=$select_options key=value item=option_title}
                            <option value="{$value|escape:'htmlall':'UTF-8'}" {if $google_cat[$node->id_category].$tree_type == $value} selected="selected"{/if}>{$option_title|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                {elseif $tree_type == 'gender'}
                    <select class="fixed-width-xl tree_cat_input" name="gender[{$node->id_category|intval}]">
                        {foreach from=$select_options key=value item=option_title}
                            <option value="{$value|escape:'htmlall':'UTF-8'}" {if $google_cat[$node->id_category].$tree_type == $value} selected="selected"{/if}>{$option_title|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                {/if}
            </span>
            {if isset($node->childs)}
                <ul class="tree child">
                {printTree fpf_catTree = $node->childs}
                </ul>
            {/if}
        </li>
    {/foreach}
{/function}
{if $old_ps}<fieldset id="{$main_id|escape:'htmlall':'UTF-8'}">
    <legend>{if isset($tree_title)}{$tree_title|escape:'htmlall':'UTF-8'}{/if}{if isset($toolbar)}{$toolbar|escape:'htmlall':'UTF-8'}{/if}</legend>
{else}
{if !$skip_header}<div class="panel move-to-form" id="{$main_id|escape:'htmlall':'UTF-8'}" data-mode="append">{/if}
    <div class="tree-panel-heading-controls clearfix panel-heading">
        {if isset($tree_title)}<i class="icon-{if $tree_type == 'google_categories'}tag{elseif  $tree_type == 'exclude'}exclamation-triangle{elseif  $tree_type == 'age_group'}users{elseif  $tree_type == 'gender'}venus-mars{/if}"></i>&nbsp;{$tree_title|escape:'htmlall':'UTF-8'}{/if}
        {if isset($toolbar)}{$toolbar|escape:'htmlall':'UTF-8'}{/if}
    </div>
{/if}
    {if isset($fpf_catTree) && !empty($fpf_catTree)}
    <div class="form-wrapper">
        <div class="{if $old_ps}hint{else}alert alert-info{/if}">
           {if $tree_type == 'google_categories'}
                <p><strong>{l s='In this section you will be able to assign a relation from your categories to the Google ones called Google Product Categories.' mod='facebookconversiontrackingplus'}</strong></p>
                <p>{l s='The Google Product Categories is a standarized category and subcategory system and it\'s used by Google, Facebook and other providers.' mod='facebookconversiontrackingplus'} </p>
                <p>{l s='To assign a category just select one field, start typing and you should see a suggested list of categories' mod='facebookconversiontrackingplus'}, {l s='try to select the one that describes better your category' mod='facebookconversiontrackingplus'}.</p>
                <p>{l s='You can also assign that category by selecting the text and pasting it to another category' mod='facebookconversiontrackingplus'}</p>
                <p>{l s='There is also another method to mass assign the categories. Select the checkboxes right before the category title that will share the same Google Taxonomy, then scroll down until you see the "Massive Category Association" box, click the field inside and start typing like you would do with any category' mod='facebookconversiontrackingplus'}, {l s='then save and all the selected categories will be updated' mod='facebookconversiontrackingplus'}.</p>
            {elseif $tree_type == 'exclude'}
                <p><strong>{l s='In this section you will be able to exclude categories from the Feed generation.' mod='facebookconversiontrackingplus'}</strong></p>
                <p>{l s='We do recommend to use Facebook filters to create Product Sets, but there are some times that Facebook doesn\'t like the products inside a category (like lingerie and other adult related products) and it can pose a problem.' mod='facebookconversiontrackingplus'}</p>
                <p>{l s='To exclude a/some categories just click on the checkbox and save after your selection is complete' mod='facebookconversiontrackingplus'}.</p>
            {elseif $tree_type == 'age_group'}
                <p><strong>{l s='Select the age group related to the categories' mod='facebookconversiontrackingplus'}</strong></p>
                <p>{l s='To assign the value the module will take into account the main category of the product' mod='facebookconversiontrackingplus'}</p>
            {elseif $tree_type == 'gender'}
                <p><strong>{l s='Select the gender related to the categories' mod='facebookconversiontrackingplus'}</strong></p>
                <p>{l s='To assign the value the module will take into account the main category of the product' mod='facebookconversiontrackingplus'}</p>
            {/if}
        </div>
        <ul id="categories-treeview" class="tree">
            {printTree $fpf_catTree}
        </ul>
        <div class="tree-bottom">
            {if $tree_type == 'google_categories'}
            <p>{l s='Here you can associate your shop\'s categories with Google Shopping Categories' mod='facebookconversiontrackingplus'}. {l s='This setting is not mandatory but it may help you getting a better performance on ads' mod='facebookconversiontrackingplus'}</p>
            <p>{l s='For products associated with múltiple categories the default one will be taken into account' mod='facebookconversiontrackingplus'}.</p>
            <h2>{l s='Massive category association' mod='facebookconversiontrackingplus'}.</h2>
            <p>{l s='You select mupltiple categories from abobe and then use the following box to assign a Google Shopping Category to all of them' mod='facebookconversiontrackingplus'}.</p>
            <div class="col-lg-9"><input type="text" id="massiveupdate" name="massiveupdate" value="" /><input type="hidden" id="massiveupdate_id" name="massiveupdate_id" value="" /></div>
            {/if}
            <!-- <div class="clearfix"></div>
            <p>&nbsp;</p> -->
        </div>
    </div>
    {else}
    <div class="ajax-replace" data-id="{$main_id|escape:'htmlall':'UTF-8'}" data-token="{$fpf_token|escape:'htmlall':'UTF-8'}">
        <i class="icon icon-spinner icon-pulse icon-4x icon-fw"></i>
        <span class="sr-only">{l s='Loading...' mod='facebookconversiontrackingplus'}</span>
        <!-- Will be replaced by ajax content -->
    </div>
    {/if}
    <div class="panel-footer">
        <button type="submit" value="1" id="{$tree_submit|escape:'htmlall':'UTF-8'}" name="submitfacebookproductsfeed" class="button btn btn-default">
            <i class="process-icon-save"></i> {l s='Update Categories' mod='facebookconversiontrackingplus'}
        </button>
    </div>
{if $old_ps}
</fieldset>
{else}
{if !$skip_header}</div>{/if}
{/if}
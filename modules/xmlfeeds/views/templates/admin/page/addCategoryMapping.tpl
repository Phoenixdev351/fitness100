{*
 * 2010-2022 Bl Modules.
 *
 * If you wish to customize this module for your needs,
 * please contact the authors first for more information.
 *
 * It's not allowed selling, reselling or other ways to share
 * this file or any other module files without author permission.
 *
 * @author    Bl Modules
 * @copyright 2010-2022 Bl Modules
 * @license
*}
<link rel="stylesheet" href="../modules/xmlfeeds/views/css/jquery-ui.css">
<script src="../modules/xmlfeeds/views/js/jquery-ui.js"></script>
<script type="text/javascript">
    var ga_cat_blmod = [{$categoriesList}];
</script>
<div class="panel">
    <div class="panel-heading">
        <i class="icon-cog"></i> {l s='Category map file' mod='xmlfeeds'}
    </div>
    <div class="row">
        <form action="{$requestUri|escape:'htmlall':'UTF-8'}" method="post" enctype="multipart/form-data">
            <input style="width: 282px; margin-right: 15px;" type="text" placeholder="{l s='Category map name' mod='xmlfeeds'}" name="category_map_name" value="" required/>
            <input style="display: inline-block; height: 31px; border-radius: 3px; padding-top: 5px; width: 280px;" type="file" name="map_file" accept=".txt" required>
            <div id="category-map-instruction-action" class="bl_comments" style="margin-top: 5px; cursor: pointer;">[{l s='Show category map file requrement' mod='xmlfeeds'}]</div>
            <div style="display: none;" id="category-map-instruction-box">
                <ul class="blmod-instruction">
                    <li>{l s='Allowed file format is plain text with .txt extension (example: google_En.txt)' mod='xmlfeeds'}</li>
                    <li>
                        {l s='The data row consists of category ID and name separated by a dash ( - )' mod='xmlfeeds'}<br>
                        {l s='(example: 1 - Toys > Puzzles )' mod='xmlfeeds'}
                    </li>
                    <li>
                        {l s='Category and subcategory delimiter is not important, you can use any characters' mod='xmlfeeds'}<br>
                        {l s='(example: 1 - Toys > Puzzles )' mod='xmlfeeds'}, {l s='(example: 1 - Toys | Puzzles )' mod='xmlfeeds'}, {l s='(example: 1 - Toys Puzzles )' mod='xmlfeeds'}
                    </li>
                    <li><a style="color: #268CCD;" target="_blank" href="{$instructionUlr|escape:'htmlall':'UTF-8'}" download>{l s='Download a sample file' mod='xmlfeeds'}</a> </li>
                </ul>
            </div>
            <center>
                <input style="text-align: center; margin-top: 20px;" class="btn btn-secondary" type="submit" name="select_category_type" value="{l s='Upload new' mod='xmlfeeds'}">
            </center>
            <div class="cb"><br></div>
        </form>
    </div>
</div>
<div class="panel">
    <div class="panel-heading">
        <i class="icon-cog"></i> {l s='Category mapping' mod='xmlfeeds'}
    </div>
    <div class="row">
        <form style="border-bottom: solid 1px #EAEDEF; margin-bottom: 10px;" action="{$requestUri|escape:'htmlall':'UTF-8'}" method="post">
            {if !empty($type)}
                <input style="float: right;" class="btn btn-secondary" type="submit" name="select_category_type" value="{l s='Select' mod='xmlfeeds'}">
            {/if}
            <select style="float: left; width: 517px; margin-bottom: 15px;" name="category_type">
                <option value="0">{l s='None' mod='xmlfeeds'}</option>
                {foreach $list as $l}
                    <option value="{$l.id|escape:'htmlall':'UTF-8'}"{if $type eq $l.id} selected{/if}>{$l.title|escape:'htmlall':'UTF-8'}</option>
                    {if $type eq $l.id}
                        {assign var="caregoryName" value={$l.title|escape:'htmlall':'UTF-8'}}
                    {/if}
                {/foreach}
            </select>
            <div class="cb"><br></div>
        </form>
        <form action="{$requestUri|escape:'htmlall':'UTF-8'}" method="post">
            <a style="margin-right: 15px;" href="{$fullAdminUrl|escape:'htmlall':'UTF-8'}&google_cat_assign=1&category_map_delete={$type|escape:'htmlall':'UTF-8'}" class="bl_comments delete-link-alert" onclick="return confirm('{l s='Are you sure you want to delete category map file?' mod='xmlfeeds'}')">{l s='Delete map file' mod='xmlfeeds'}</a>
            <a href="{$mapFileUrl|escape:'htmlall':'UTF-8'}" target="_blank" class="bl_comments" download>{l s='Download map file' mod='xmlfeeds'}</a>
            <input style="float: right;" type="submit" name="update_ga_cat" value="Update" class="btn btn-primary">
            <div class="cb"><br></div>
            <div class="info-small-blmod blmod_b10 blmod_mt10">
                {l s='Please select a category from suggestion list, enter at least 3 letters to display the list.' mod='xmlfeeds'}<br>
                {l s='Letters can be from any part of the word.' mod='xmlfeeds'}<br>
                {if !empty($caregoryName)}
                    <br>{l s='You can view the full list of' mod='xmlfeeds'} <a target="_blank" href="{$mapFileUrl|escape:'htmlall':'UTF-8'}">{$caregoryName|escape:'htmlall':'UTF-8'}</a> {l s='categories.' mod='xmlfeeds'}
                {/if}
            </div>
            {$categoriesTree}
            <input style="float: right;" type="submit" name="update_ga_cat" value="Update" class="btn btn-primary">
            <input type="hidden" name="category_type" value="{$type|escape:'htmlall':'UTF-8'}"/>
            <div class="cb"><br></div>
        </form>
    </div>
</div>
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
<div class="panel">
    <div class="panel-heading">
        <i class="icon-cog"></i> {l s='Create new ' mod='xmlfeeds'}{$typeName|escape:'htmlall':'UTF-8'} {l s='map' mod='xmlfeeds'}
    </div>
    <div class="row">
        <form action="{$requestUri|escape:'htmlall':'UTF-8'}" method="post">
            <input type="text" placeholder="{$typeName|escape:'htmlall':'UTF-8'} {l s='map name' mod='xmlfeeds'}" name="product_property_map_name" value="" required/>
            <center>
                <input style="text-align: center; margin-top: 20px;" class="btn btn-secondary" type="submit" name="create_product_property_map" value="{l s='Create new' mod='xmlfeeds'}">
                <input type="hidden" name="product_property_map_type" value="{$typeId|escape:'htmlall':'UTF-8'}">
            </center>
            <div class="cb"><br></div>
        </form>
    </div>
</div>
<div class="panel">
    <div class="panel-heading">
        <i class="icon-cog"></i> {$typeName|escape:'htmlall':'UTF-8'} {l s='mapping' mod='xmlfeeds'}
    </div>
    <div class="row">
        <form style="border-bottom: solid 1px #EAEDEF; margin-bottom: 10px;" action="{$requestUri|escape:'htmlall':'UTF-8'}" method="post">
            {if !empty($mapList)}
                <input style="float: right;" class="btn btn-secondary" type="submit" name="select_product_property_map" value="{l s='Select' mod='xmlfeeds'}">
            {/if}
            <select style="float: left; width: 517px; margin-bottom: 15px;" name="map_id">
                <option value="0">{l s='None' mod='xmlfeeds'}</option>
                {foreach $mapList as $l}
                    <option value="{$l.id|escape:'htmlall':'UTF-8'}"{if $mapId eq $l.id} selected{/if}>{$l.name|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
            </select>
            <div class="cb"><br></div>
        </form>
        <form action="{$requestUri|escape:'htmlall':'UTF-8'}" method="post">
            {if !empty($mapId)}
                <a style="margin-right: 15px;" href="{$fullAdminUrl|escape:'htmlall':'UTF-8'}&{$typeUrl|escape:'htmlall':'UTF-8'}=1&product_property_map_delete={$mapId|escape:'htmlall':'UTF-8'}" class="bl_comments" onclick="return confirm('{l s='Are you sure you want to delete the map?' mod='xmlfeeds'}')">{l s='Delete the map' mod='xmlfeeds'}</a>
                <input type="hidden" name="map_id" value="{$mapId|escape:'htmlall':'UTF-8'}">
                <input style="float: right;" type="submit" name="update_product_property_map" value="Update" class="btn btn-primary">
                <div class="cb"></div>
                {foreach $productProperties as $g}
                    <div id="option_box_2" class="option-box">
                        <div id="option-box-title_{$g.id|escape:'htmlall':'UTF-8'}" class="option-box-title">{$g.name|escape:'htmlall':'UTF-8'} <i class="icon-angle-down"></i></div>
                        <div id="option-box-content_{$g.id|escape:'htmlall':'UTF-8'}" class="option-box-content" style="display: none;">
                            {foreach $g.properties as $p}
                                <div class="product-property-row">
                                    <label class="t" style="width: 100%!important;">
                                        <div class="product-property-row-name">{$p.name|escape:'htmlall':'UTF-8'}</div>
                                        <input type="text" placeholder="{l s='Enter a new name' mod='xmlfeeds'}" name="property[{$g.id|escape:'htmlall':'UTF-8'}][{$p.id|escape:'htmlall':'UTF-8'}]" value="{if isset($p.value)}{$p.value|escape:'htmlall':'UTF-8'}{/if}">
                                    </label>
                                </div>
                            {/foreach}
                        </div>
                    </div>
                {/foreach}
                <input style="float: right;" type="submit" name="update_product_property_map" value="Update" class="btn btn-primary">
            {/if}
        </form>
    </div>
</div>
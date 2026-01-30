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
<ul class="search_list_autocomplite">
    {if !empty($products)}
        {foreach $products as $p}
            <li class="search_p_list" id="search_p-{$p.id_product|escape:'htmlall':'UTF-8'}">
                <div class="search_drop_product product-list-row" id="search_drop-{$p.id_product|escape:'htmlall':'UTF-8'}">
                    <i class="icon-trash" title="{l s='Remove from list' mod='xmlfeeds'}"></i>
                </div>
                <div style="float: left;" class="product-list-row">
                    <div style="float: left; width: 30px; margin-right: 5px;">
                        <img alt="{l s='Product' mod='xmlfeeds'}" style="width: 29px;" src="{$p.img_url|escape:'htmlall':'UTF-8'}" />
                    </div>
                    <div style="float: left; width: 300px;" class="search_p_name">
                        {$p.name|escape:'htmlall':'UTF-8'}<br/>
                        <span class="search_small_text">#{$p.id_product|escape:'htmlall':'UTF-8'}{$p.cat_name|escape:'htmlall':'UTF-8'}</span>
                    </div>
                </div>
                <div class="blmod_cb"></div>
            </li>
            {if $totalProducts > $limit}
                <li>...</li>
            {/if}
        {/foreach}
    {/if}
</ul>
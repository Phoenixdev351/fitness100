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
        <i class="icon-list-alt"></i> {l s='XML Feeds' mod='xmlfeeds'}
    </div>
    <div class="row">
        <i class="icon-cart-arrow-down menu-top-main-icon menu-top-item-icon"></i><span class="menu-top">{l s='Product feeds' mod='xmlfeeds'}</span><br/>
        <div class="menu-top-feeds">
            {if !empty($products)}
                {foreach $products as $p}
                    <div class="menu-top-item">
                        <a class="menu-top-item-title{if $currentPage.type == 'edit' && $currentPage.id == $p.id} menu-a{/if}" href="{$full_address_no_t|escape:'htmlall':'UTF-8'}&page={$p.id|escape:'htmlall':'UTF-8'}{$token|escape:'htmlall':'UTF-8'}">{$p.name|escape:'htmlall':'UTF-8'}</a>
                        <a title="{l s='Statistics' mod='xmlfeeds'}" class="menu-top-statistics" style="margin-left: 5px;" href="{$full_address_no_t|escape:'htmlall':'UTF-8'}&statistics={$p.id|escape:'htmlall':'UTF-8'}{$token|escape:'htmlall':'UTF-8'}"><i class="icon-bar-chart"></i></a>
                        <a title="{l s='Duplicate feed' mod='xmlfeeds'}" class="menu-top-duplicate" style="margin-left: 1px;" href="{$full_address_no_t|escape:'htmlall':'UTF-8'}&duplicate={$p.id|escape:'htmlall':'UTF-8'}{$token|escape:'htmlall':'UTF-8'}"><i class="icon-copy"></i></a>
                        <a title="{l s='Delete feed' mod='xmlfeeds'}" class="menu-top-delete" href="{$full_address_no_t|escape:'htmlall':'UTF-8'}&delete_feed={$p.id|escape:'htmlall':'UTF-8'}{$token|escape:'htmlall':'UTF-8'}" onclick="return confirm('{l s='Are you sure you want to delete?' mod='xmlfeeds'}')"><i class="icon-trash"></i></a>
                    </div>
                {/foreach}
            {/if}
        </div>
    </div>
    <a class="menu-top-item mb15{if $currentPage.type == 'add_feed' && $currentPage.id == 1} menu-a{/if}" href="{$full_address_no_t|escape:'htmlall':'UTF-8'}&add_feed=1{$token|escape:'htmlall':'UTF-8'}"><i class="icon-plus-circle menu-top-item-icon"></i>{l s='Add new feed' mod='xmlfeeds'}</a>
    <a class="menu-top-item{if $currentPage.type == 'add_affiliate_price'} menu-a{/if}" href="{$full_address_no_t|escape:'htmlall':'UTF-8'}&add_affiliate_price=1{$token|escape:'htmlall':'UTF-8'}"><i class="icon-calculator menu-top-item-icon"></i>{l s='Affiliate prices' mod='xmlfeeds'}</a>
    <a class="menu-top-item{if $currentPage.type == 'google_cat_assign'} menu-a{/if}" style="display: inline-block;" href="{$full_address_no_t|escape:'htmlall':'UTF-8'}&google_cat_assign=1{$token|escape:'htmlall':'UTF-8'}"><i class="icon-folder menu-top-item-icon"></i>{l s='Categories mapping' mod='xmlfeeds'}</a>
    <a class="menu-top-item{if $currentPage.type == 'attributes_mapping'} menu-a{/if}" style="display: inline-block;" href="{$full_address_no_t|escape:'htmlall':'UTF-8'}&attributes_mapping=1{$token|escape:'htmlall':'UTF-8'}"><i class="icon-tag menu-top-item-icon"></i>{l s='Attributes mapping' mod='xmlfeeds'}</a>
    <a class="menu-top-item{if $currentPage.type == 'features_mapping'} menu-a{/if}" style="display: inline-block;" href="{$full_address_no_t|escape:'htmlall':'UTF-8'}&features_mapping=1{$token|escape:'htmlall':'UTF-8'}"><i class="icon-plug menu-top-item-icon"></i>{l s='Features mapping' mod='xmlfeeds'}</a>
    <a class="menu-top-item{if $currentPage.type == 'product_list_page'} menu-a{/if}" style="display: inline-block;" href="{$full_address_no_t|escape:'htmlall':'UTF-8'}&product_list_page=1{$token|escape:'htmlall':'UTF-8'}"><i class="icon-list menu-top-item-icon"></i>{l s='Product list' mod='xmlfeeds'}</a>
    <div>
        <hr/>
        <i class="icon-folder-open menu-top-main-icon menu-top-item-icon"></i><span class="menu-top">{l s='Category feeds' mod='xmlfeeds'}</span><br/>
        <div class="menu-top-feeds">
            {if !empty($categories)}
                {foreach $categories as $c}
                    <div class="menu-top-item">
                        <a class="menu-top-item-title{if $currentPage.type == 'edit' && $currentPage.id == $c.id} menu-a{/if}" href="{$full_address_no_t|escape:'htmlall':'UTF-8'}&page={$c.id|escape:'htmlall':'UTF-8'}{$token|escape:'htmlall':'UTF-8'}">{$c.name|escape:'htmlall':'UTF-8'}</a>
                        <a title="{l s='Statistics' mod='xmlfeeds'}" class="menu-top-statistics" style="margin-left: 5px;" href="{$full_address_no_t|escape:'htmlall':'UTF-8'}&statistics={$c.id|escape:'htmlall':'UTF-8'}{$token|escape:'htmlall':'UTF-8'}"><i class="icon-bar-chart"></i></a>
                        <a title="{l s='Duplicate feed' mod='xmlfeeds'}" class="menu-top-duplicate" style="margin-left: 5px;" href="{$full_address_no_t|escape:'htmlall':'UTF-8'}&duplicate={$c.id|escape:'htmlall':'UTF-8'}{$token|escape:'htmlall':'UTF-8'}"><i class="icon-copy"></i></a>
                        <a title="{l s='Delete feed' mod='xmlfeeds'}" class="menu-top-delete" href="{$full_address_no_t|escape:'htmlall':'UTF-8'}&delete_feed={$c.id|escape:'htmlall':'UTF-8'}{$token|escape:'htmlall':'UTF-8'}" onclick="return confirm('{l s='Are you sure you want to delete?' mod='xmlfeeds'}')"><i class="icon-trash"></i></a>
                    </div>
                {/foreach}
            {/if}
        </div>
        <a class="menu-top-item{if $currentPage.type == 'add_feed' && $currentPage.id == 2} menu-a{/if}" href="{$full_address_no_t|escape:'htmlall':'UTF-8'}&add_feed=2{$token|escape:'htmlall':'UTF-8'}"><i class="icon-plus-circle menu-top-item-icon"></i>{l s='Add new feed' mod='xmlfeeds'}</a>
        <div>
            <hr/>
            <i class="icon-credit-card menu-top-main-icon menu-top-item-icon"></i><span class="menu-top">{l s='Order feeds' mod='xmlfeeds'}</span><br/>
        </div>
        <div class="menu-top-feeds">
            {if !empty($orders)}
                {foreach $orders as $o}
                    <div class="menu-top-item">
                        <a class="menu-top-item-title{if $currentPage.type == 'edit' && $currentPage.id == $o.id} menu-a{/if}" style="" href="{$full_address_no_t|escape:'htmlall':'UTF-8'}&page={$o.id|escape:'htmlall':'UTF-8'}{$token|escape:'htmlall':'UTF-8'}">{$o.name|escape:'htmlall':'UTF-8'}</a>
                        <a title="{l s='Statistics' mod='xmlfeeds'}" class="menu-top-statistics" style="margin-left: 5px;" href="{$full_address_no_t|escape:'htmlall':'UTF-8'}&statistics={$o.id|escape:'htmlall':'UTF-8'}{$token|escape:'htmlall':'UTF-8'}"><i class="icon-bar-chart"></i></a>
                        <a title="{l s='Duplicate feed' mod='xmlfeeds'}" class="menu-top-duplicate" style="margin-left: 1px;" href="{$full_address_no_t|escape:'htmlall':'UTF-8'}&duplicate={$o.id|escape:'htmlall':'UTF-8'}{$token|escape:'htmlall':'UTF-8'}"><i class="icon-copy"></i></a>
                        <a title="{l s='Delete feed' mod='xmlfeeds'}" class="menu-top-delete" href="{$full_address_no_t|escape:'htmlall':'UTF-8'}&delete_feed={$o.id|escape:'htmlall':'UTF-8'}{$token|escape:'htmlall':'UTF-8'}" onclick="return confirm('{l s='Are you sure you want to delete?' mod='xmlfeeds'}')"><i class="icon-trash"></i></a>
                    </div>
                {/foreach}
            {/if}
        </div>
        <a class="menu-top-item{if $currentPage.type == 'add_feed' && $currentPage.id == 3} menu-a{/if}" href="{$full_address_no_t|escape:'htmlall':'UTF-8'}&add_feed=3{$token|escape:'htmlall':'UTF-8'}"><i class="icon-plus-circle menu-top-item-icon"></i>{l s='Add new feed' mod='xmlfeeds'}</a>
        <hr/>
        <div>
            <a class="menu-top-item{if $currentPage.id == 4} menu-a{/if}" href="{$full_address_no_t|escape:'htmlall':'UTF-8'}&page=4{$token|escape:'htmlall':'UTF-8'}"><i class="icon-user menu-top-item-icon"></i>{l s='Export customers' mod='xmlfeeds'}</a>
        </div>
        <div>
            <a class="menu-top-item{if $currentPage.id == 5} menu-a{/if}" href="{$full_address_no_t|escape:'htmlall':'UTF-8'}&page=5{$token|escape:'htmlall':'UTF-8'}"><i class="icon-flag menu-top-item-icon"></i>{l s='Export brands' mod='xmlfeeds'}</a>
        </div>
        <div>
            <a class="menu-top-item{if $currentPage.id == 6} menu-a{/if}" href="{$full_address_no_t|escape:'htmlall':'UTF-8'}&page=6{$token|escape:'htmlall':'UTF-8'}"><i class="icon-truck menu-top-item-icon"></i>{l s='Export suppliers' mod='xmlfeeds'}</a>
        </div>
        <div>
            <a class="menu-top-item{if $currentPage.type == 'about_page'} menu-a{/if}" href="{$full_address_no_t|escape:'htmlall':'UTF-8'}&about_page=1{$token|escape:'htmlall':'UTF-8'}"><i class="icon-info-circle menu-top-item-icon"></i>{l s='About' mod='xmlfeeds'}</a>
        </div>
    </div>
</div><br/><br/>
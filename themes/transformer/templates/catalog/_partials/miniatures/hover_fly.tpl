<div class="hover_fly hover_fly_{(int)$sttheme.flyout_buttons_style} flex_container {if $sttheme.flyout_buttons_on_mobile==1} mobile_hover_fly_show {elseif $sttheme.flyout_buttons_on_mobile==2} mobile_hover_fly_cart {else} mobile_hover_fly_hide {/if}">
          {if !$sttheme.display_add_to_cart && $sttheme.pro_quantity_input!=1 && $sttheme.pro_quantity_input!=3}
            {if $has_add_to_cart}
              {include file='catalog/_partials/miniatures/btn-add-to-cart.tpl'}
            {else}
              {include file='catalog/_partials/miniatures/btn-view-more.tpl'}{*is_select_options=true to do find a way to tell products can not have add to cart button just because of they have attributes *}
            {/if}
          {/if}
          {if ($sttheme.flyout_quickview || (isset($steasybuilder) && $steasybuilder.is_editing)) && (!isset($from_product_page) || !$from_product_page)}
            {$classname_flyout_quickview=''}
            {if !$sttheme.flyout_quickview && isset($steasybuilder) && $steasybuilder.is_editing}{$classname_flyout_quickview='display_none'}{/if}
            {include file='catalog/_partials/miniatures/btn-quick-view.tpl' classname=$classname_flyout_quickview}
          {/if}
          {if !$sttheme.use_view_more_instead && ((!$sttheme.display_add_to_cart && $has_add_to_cart) || $sttheme.display_add_to_cart)}{include file='catalog/_partials/miniatures/btn-view-more.tpl'}{/if}
          {if isset($wishlist_position) && !$wishlist_position}
            {include file='module:stwishlist/views/templates/hook/fly.tpl' is_wished=(isset($product.stwishlist.wished) && $product.stwishlist.wished) fromnocache=(isset($for_w) && $for_w == 'category')}
          {/if}
          {if isset($loved_position) && !$loved_position}
            {include file='module:stlovedproduct/views/templates/hook/fly.tpl' id_source=$product.id_product is_loved=(isset($product.stlovedproduct.loved) && $product.stlovedproduct.loved) fromnocache=(isset($for_w) && $for_w == 'category')}
          {/if}
          {if isset($stcompare) && ( $stcompare.fly_out || (isset($steasybuilder) && $steasybuilder.is_editing) )}
            {$classname_flyout_stcompare=''}
            {if !$stcompare.fly_out && isset($steasybuilder) && $steasybuilder.is_editing}{$classname_flyout_stcompare='display_none'}{/if}
            {include file='module:stcompare/views/templates/hook/fly.tpl' id_product=$product.id_product is_compared=(isset($product.stcompare.compared) && $product.stcompare.compared) fromnocache=(isset($for_w) && $for_w == 'category') classname=$classname_flyout_stcompare}
          {/if}
          {if $sttheme.flyout_share || (isset($steasybuilder) && $steasybuilder.is_editing)}
            {$classname_flyout_share=''}
            {if !$sttheme.flyout_share || (isset($steasybuilder) && $steasybuilder.is_editing)}{$classname_flyout_share='display_none'}{/if}
            {include file='module:stsocial/views/templates/hook/stsocial-hover-fly.tpl' classname=$classname_flyout_share}
          {/if}
      </div>
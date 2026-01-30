<!-- MODULE st stcustomersignin -->
{if $logged}
		{if isset($welcome_logged) && trim($welcome_logged) || (isset($steasybuilder) && $steasybuilder.is_editing)}{if $welcome_link}<a href="{$welcome_link}" class="welcome top_bar_item {if isset($welcome_logged) && !trim($welcome_logged)} display_none {/if}" rel="nofollow" title="{$welcome_logged}">{else}<span class="welcome top_bar_item {if isset($welcome_logged) && !trim($welcome_logged)} display_none {/if}">{/if}<span class="header_item">{if isset($welcome_logged) && trim($welcome_logged)}{$welcome_logged}{/if}</span>{if $welcome_link}</a>{else}</span>{/if}{/if}
		{if $userinfo_dropdown || show_user_info_icons==3}
			<div class="userinfo_mod_top dropdown_wrap top_bar_item header_icon_btn_{if $show_user_info_icons==0}1{elseif $show_user_info_icons==1}2{else}{$show_user_info_icons}{/if}">{strip}
	            <a href="{$my_account_url}" class="dropdown_tri dropdown_tri_in header_item" title="{l s='View my customer account' d='Shop.Theme.Customeraccount'}" rel="nofollow" aria-haspopup="true" aria-expanded="false">
	            	{if $show_user_info_icons!=0}<span class="header_icon_btn_icon header_v_align_m {if $show_user_info_icons==1} mar_r4 {/if}"><i class="fto-user icon_btn {if $show_user_info_icons==1}fs_lg{else}fs_big{/if} mar_r4"></i></span>{/if}
	        		{if $show_user_info_icons!=2}<span class="header_icon_btn_text header_v_align_m">{$customerName}</span>{/if}
		            <i class="fto-angle-down arrow_down arrow"></i>
		            <i class="fto-angle-up arrow_up arrow"></i>
	            </a>
	            {/strip}
		        <div class="dropdown_list">
            		<ul class="dropdown_list_ul dropdown_box custom_links_list">
            			{if $show_user_info_icons==2}<li><span class="dropdown_list_item">{$customerName}</span></li>{/if}
            			<li><a href="{$my_account_url}" title="{l s='View my customer account' d='Shop.Theme.Transformer'}" rel="nofollow" class="dropdown_list_item">{l s='My account' d='Shop.Theme.Transformer'}</a></li>
						{if $show_love}<li><a href="{url entity='module' name='stlovedproduct' controller='myloved'}" rel="nofollow" class="dropdown_list_item" title="{l s='Loved items' d='Shop.Theme.Transformer'}">{l s='Loved items' d='Shop.Theme.Transformer'}</a></li>{/if}
						{if $show_wishlist}<li><a href="{url entity='module' name='stwishlist' controller='mywishlist'}" rel="nofollow" class="dropdown_list_item" title="{l s='Wishlist' d='Shop.Theme.Transformer'}">{l s='Wishlist' d='Shop.Theme.Transformer'}</a></li>{/if}
						<li><a href="{$logout_url}" rel="nofollow" class="dropdown_list_item" title="{l s='Log me out' d='Shop.Theme.Transformer'}">{l s='Sign out' d='Shop.Theme.Transformer'}</a></li>
		    		</ul>
		        </div>
		    </div>
		{else}
			<a href="{$my_account_url}" title="{l s='View my customer account' d='Shop.Theme.Transformer'}" class="account top_bar_item" rel="nofollow"><span class="header_item">{if $show_user_info_icons}<i class="fto-user icon_btn header_v_align_m {if $show_user_info_icons!=2}fs_lg{else}fs_big{/if} mar_r4"></i>{/if}{if $show_user_info_icons!=2}<span class="header_v_align_m">{$customerName}</span>{/if}</span></a>
			<a href="{$my_account_url}" title="{l s='View my customer account' d='Shop.Theme.Transformer'}" class="my_account_link top_bar_item" rel="nofollow"><span class="header_item">{l s='My account' d='Shop.Theme.Transformer'}</span></a>
			<a class="logout top_bar_item" href="{$logout_url}" rel="nofollow" title="{l s='Log me out' d='Shop.Theme.Transformer'}"><span class="header_item">{if $show_user_info_icons}<i class="fto-logout {if $show_user_info_icons!=2}fs_lg{else}fs_big{/if} mar_r4 header_v_align_m"></i>{/if}{if $show_user_info_icons!=2}<span class="header_v_align_m">{l s='Sign out' d='Shop.Theme.Transformer'}</span>{/if}</span></a>
		{/if}
{else}
		{if isset($welcome) && trim($welcome) || (isset($steasybuilder) && $steasybuilder.is_editing)}{if $welcome_link}<a href="{$welcome_link}" class="welcome top_bar_item {if isset($welcome) && !trim($welcome)} display_none {/if}" rel="nofollow" title="{$welcome}">{else}<span class="welcome top_bar_item {if isset($welcome) && !trim($welcome)} display_none {/if}">{/if}<span class="header_item">{if isset($welcome) && trim($welcome)}{$welcome}{/if}</span>{if $welcome_link}</a>{else}</span>{/if}{/if}
		{if $userinfo_login}
			<div class="quick_login dropdown_wrap top_bar_item header_icon_btn_{if $show_user_info_icons==0}1{elseif $show_user_info_icons==1}2{else}{$show_user_info_icons}{/if}">{strip}
	        	<a href="{$my_account_url}" class="dropdown_tri dropdown_tri_in header_item" aria-haspopup="true" aria-expanded="false" rel="nofollow" title="{l s='Log in to your customer account' d='Shop.Theme.Transformer'}">
	            	{if $show_user_info_icons!=0}<span class="header_icon_btn_icon header_v_align_m {if $show_user_info_icons==1} mar_r4 {/if}"><i class="fto-user icon_btn {if $show_user_info_icons==1}fs_lg{else}fs_big{/if} mar_r4"></i></span>{/if}
	        		{if $show_user_info_icons!=2}<span class="header_icon_btn_text header_v_align_m">{l s='Login' d='Shop.Theme.Transformer'}</span>{/if}
		            <i class="fto-angle-down arrow_down arrow"></i>
		            <i class="fto-angle-up arrow_up arrow"></i>
	            </a>
	            {/strip}
		        <div class="dropdown_list">
		            <div class="dropdown_box login_from_block">
		    			<form action="{$authentication_url}" method="post">
						  <div class="form_content">
					        {foreach from=$formFields item="field"}
					            {form_field field=$field file='_partials/form-fields-1.tpl'}
					        {/foreach}
						      <div class="form-group forgot-password">
						          <a href="{$urls.pages.password}" rel="nofollow" title="{l s='Forgot your password?' d='Shop.Theme.Transformer'}">
						            {l s='Forgot your password?' d='Shop.Theme.Transformer'}
						          </a>
						      </div>
						  </div>
						  <div class="form-footer">
						    <input type="hidden" name="submitLogin" value="1">
						    <button class="btn btn-default btn-spin btn-full-width" data-link-action="sign-in" type="submit">
						      <i class="fto-lock fto_small"></i>
						      {l s='Sign in' d='Shop.Theme.Transformer'}
						    </button>
						    <a class="btn btn-link btn-full-width btn-spin js-submit-active" href="{$urls.pages.register}" rel="nofollow" title="{l s='Create an account' d='Shop.Theme.Transformer'}">
								{l s='Create an account' d='Shop.Theme.Transformer'}
							</a>
						  </div>

						</form>

		    		</div>
		        </div>
		    </div>
		{else}
		<a class="login top_bar_item header_icon_btn_{if $show_user_info_icons==0}1{elseif $show_user_info_icons==1}2{else}{$show_user_info_icons}{/if}" href="{$my_account_url}" rel="nofollow" title="{l s='Log in to your customer account' d='Shop.Theme.Transformer'}"><span class="header_item">{if $show_user_info_icons!=0}<span class="header_icon_btn_icon header_v_align_m {if $show_user_info_icons==1} mar_r4 {/if}"><i class="fto-user icon_btn {if $show_user_info_icons==1}fs_lg{else}fs_big{/if}"></i></span>{/if}{if $show_user_info_icons!=2}<span class="header_icon_btn_text header_v_align_m">{l s='Login' d='Shop.Theme.Transformer'}</span>{/if}</span></a>
		{/if}
{/if}
<!-- /MODULE st stcustomersignin -->
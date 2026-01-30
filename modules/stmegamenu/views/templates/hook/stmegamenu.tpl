<!-- MODULE st stmegamenu -->
{if isset($stmenu) && is_array($stmenu) && count($stmenu)}
{if $header_bottom}
<div class="st_mega_menu_container animated fast">
	<div id="st_mega_menu_header_container">
{/if}
	<nav id="st_mega_menu_wrap" class="{if $sttheme.megamenu_position==3} flex_child flex_full {/if}">
		{include file="module:stmegamenu/views/templates/hook/stmegamenu-ul.tpl" is_mega_menu_main=1}
	</nav>
{if $header_bottom}
	</div>
</div>
{/if}
{/if}
<!-- /MODULE st stmegamenu -->
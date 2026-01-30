
<ul class="nav nav-tabs bordered">
<li id="tab_steasycontnet_tab" class="nav-item"><a href="#steasycontnet_tab" data-toggle="tab" class="nav-link description-tab active show">{l s='Tab title' d='Admin.Theme.Transformer'}</a></li>
<li id="tab_steasycontnet_content" class="nav-item"><a href="#steasycontnet_content" data-toggle="tab" class="nav-link description-tab">{l s='Tab content' d='Admin.Theme.Transformer'}</a></li>
</ul>
<div class="tab-content bordered">
	<div class="tab-pane panel panel-default active show" id="steasycontnet_tab">
		{foreach $languages as $language}
		<div class="translatable-field lang-{$language.id_lang} row" {if $language.id_lang != $default_lang}style="display:none;"{/if}>
			<div class="col-lg-9">
				<input type="text" id="ec_title_{$language.id_lang}" name="ec_title_{$language.id_lang}" class="form-control" value="{if isset( $ec_tabs['title'][$language.id_lang])}{$ec_tabs['title'][$language.id_lang]}{/if}" />
			</div>
			<div class="col-lg-2">
				<button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">{$language.iso_code}<i class="icon-caret-down"></i></button>
				<ul class="dropdown-menu">
					{foreach $languages as $language}
					<li><a href="javascript:hideOtherLanguage({$language.id_lang});" tabindex="-1">{$language.name}</a></li>
					{/foreach}
				</ul>
			</div>
		</div>
		{/foreach}
	</div>
	<div class="tab-pane panel panel-default" id="steasycontnet_content">
		{foreach $languages as $language}
		<div class="form-group translatable-field lang-{$language.id_lang} row" {if $language.id_lang != $default_lang}style="display:none;"{/if}>
			<div class="col-lg-9">
				<textarea name="ec_text_{$language.id_lang}" id="ec_text_{$language.id_lang}" class="rte autoload_rte">{if isset( $ec_tabs['text'][$language.id_lang])}{$ec_tabs['text'][$language.id_lang]}{/if}</textarea>
			</div>
			<div class="col-lg-2">
				<button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">{$language.iso_code}<i class="icon-caret-down"></i></button>
				<ul class="dropdown-menu">
					{foreach $languages as $language}
					<li><a href="javascript:hideOtherLanguage({$language.id_lang});" tabindex="-1">{$language.name}</a></li>
					{/foreach}
				</ul>
			</div>
		</div>
		{/foreach}
	</div>
</div>
<input type="hidden" name="id_st_easy_content" value="{$id_st_easy_content}">
<input type="hidden" name="ec_act" value="save_ec_tab">
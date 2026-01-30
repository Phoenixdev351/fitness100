{if isset($ststickers) && $ststickers}
	<!-- MODULE st ststickers -->
	{foreach $ststickers as $ststicker}
		<div class="st_sticker layer_btn st_sticker_{$ststicker.id_st_sticker}"><span class="st_sticker_text">{$ststicker.text}</span></div>
	{/foreach}
	<!-- /MODULE st ststickers -->
{/if}
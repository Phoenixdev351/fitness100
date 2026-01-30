{*
*  @author    Amazzing <mail@mirindevo.com>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<div class="cf-wrapper{if !empty($cf.wrapper_class)} {$cf.wrapper_class|escape:'html':'UTF-8'}{/if}">
	<a href="#" class="cf-btn cf-toggle{if !empty($cf.num)} has-values{/if}">
		{$cf.l.btn|escape:'html':'UTF-8'}{if isset($cf.num)} <span class="cf-num">({$cf.num|intval})</span>{/if}
	</a>
	<div class="cf-modal">
		<div class="cf-modal-overlay cf-toggle"></div>
		<div class="cf-modal-content">
			{* filled dynamically *}
			<a href="#" class="close-modal cf-toggle">&times;</a>
		</div>
	</div>
</div>
{* since 3.2.5 *}

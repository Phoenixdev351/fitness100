<section>
	<section id="dash_comments">
		<header>{l s='Product reviews' d='Modules.Stproductcomments.Admin'}</header>
		<ul class="data_list">
			<li>
				<span class="data_label"><a href="{$link->getAdminLink('AdminModules')|escape:'html':'UTF-8'}&amp;configure=stproductcomments">{l s='All reviews' d='Modules.Stproductcomments.Admin'}</a></span>
				<span class="data_value size_md">{$nbr_all}</span>
			</li>
			{if $moderate}
			<li>
				<span class="data_label"><a href="{$link->getAdminLink('AdminModules')|escape:'html':'UTF-8'}&amp;configure=stproductcomments">{l s='Pending reviews' d='Modules.Stproductcomments.Admin'}</a></span>
				<span class="data_value size_md">{$nbr_pendding}</span>
			</li>
			{/if}
			<li>
				<span class="data_label"><a href="{$link->getAdminLink('AdminModules')|escape:'html':'UTF-8'}&amp;configure=stproductcomments">{l s='Reported reviews' d='Modules.Stproductcomments.Admin'}</a></span>
				<span class="data_value size_md">{$nbr_reported}</span>
			</li>
		</ul>
	</section>
</section>
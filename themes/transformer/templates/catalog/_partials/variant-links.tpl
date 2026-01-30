<div class="variant-links {if !$sttheme.display_color_list} display_none {/if}">
  {foreach from=$variants item=variant}
    <a href="{$variant.url}"
       class="{$variant.type}"
       title="{$variant.name}"
       aria-label="{$variant.name}"
      {if $variant.texture} style="background-image: url({$variant.texture})"
      {elseif $variant.html_color_code} style="background-color: {$variant.html_color_code}" {/if}
    ><span class="sr-only">{$variant.name}</span></a>
  {/foreach}
  <span class="js-count count"></span>
</div>

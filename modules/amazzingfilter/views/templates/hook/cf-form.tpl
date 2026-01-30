{*
*  @author    Amazzing <mail@mirindevo.com>
*  @copyright Amazzing
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<form method="POST" class="cf-form{if !empty($cf.related)} cf-related{/if}">
    <div class="cf-groups{if count($cf.groups) > 4} multirow{/if}">
        {foreach $cf.groups as $key => $group}
            <div class="cf-group{if $group.blocked} blocked{/if}">
                <div class="cf-label">{$group.label|escape:'html':'UTF-8'}</div>
                <div class="cf-value">
                    <select name="{$key|escape:'html':'UTF-8'}" class="cf-select">
                        <option value="0" class="first" data-txt="{$group.first_option|escape:'html':'UTF-8'}">
                            {if $group.blocked}--{else}{$group.first_option|escape:'html':'UTF-8'}{/if}
                        </option>
                        {foreach $group.values as $value}
                            <option value="{$value.id|escape:'html':'UTF-8'}"{if $value.id == $group.saved_value} selected{/if}>
                                {$value.name|escape:'html':'UTF-8'}
                            </option>
                        {/foreach}
                    </select>
                </div>
            </div>
        {/foreach}
        <div class="cf-submit-holder">
            <button type="button" class="btn btn-primary cf-submit">{$cf.l.btn_f|escape:'html':'UTF-8'}</button>
        </div>
    </div>
    {if array_filter(array_column($cf.groups, 'saved_value'))}
        <div class="cf-reset-holder">
            <button type="button" class="cf-reset">{$cf.l.btn_r|escape:'html':'UTF-8'}</button>
        </div>
    {/if}
</form>
{* since 3.2.9 *}

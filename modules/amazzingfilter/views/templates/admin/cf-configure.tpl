{*
*  @author    Amazzing <mail@mirindevo.com>
*  @copyright Amazzing
*  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<form method="post" action="" class="settings_form form-horizontal clearfix" data-type="cf" autocomplete="off">
    <div class="tab-title">{l s='User filters' mod='amazzingfilter'}</div>
    {foreach $settings.cf as $name => $field}
        {include file="./form-group.tpl" group_class = 'cf-group' label_class = 'cf-label' input_wrapper_class = 'cf-value'}
    {/foreach}
</form>
{renderElement type='saveMultipleSettingsBtn'}
{* since 3.2.6 *}

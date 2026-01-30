{**
*
* NOTICE OF LICENSE
*
* This product is licensed for one customer to use on one installation (test stores and multishop included).
* Site developer has the right to modify this module to suit their needs, but can not redistribute the module in
* whole or in part. Any other use of this module constitutes a violation of the user agreement.
*
* DISCLAIMER
*
* NO WARRANTIES OF DATA SAFETY OR MODULE SECURITY
* ARE EXPRESSED OR IMPLIED. USE THIS MODULE IN ACCORDANCE
* WITH YOUR MERCHANT AGREEMENT, KNOWING THAT VIOLATIONS OF
* PCI COMPLIANCY OR A DATA BREACH CAN COST THOUSANDS OF DOLLARS
* IN FINES AND DAMAGE A STORES REPUTATION. USE AT YOUR OWN RISK.
*
*  @author    idnovate.com <info@idnovate.com>
*  @copyright 2020 idnovate.com
*  @license   See above
*}

{if version_compare($smarty.const._PS_VERSION_,'1.6','<')}
    <div class="translatable">
        {foreach $languages as $language}
            <div class="lang_{$language.id_lang|escape:'html':'UTF-8'}" id="{$id_image|escape:'html':'UTF-8'}_{$language.id_lang|escape:'html':'UTF-8'}" style="display:{if $language.id_lang == $default_form_language}block{else}none{/if}; float: left;">
                {assign var="varName" value="image_{$language.id_lang}"}
                <input type="file" name="image_{$language.id_lang|escape:'html':'UTF-8'}" id="{$id_image|escape:'html':'UTF-8'}-name_{$language.id_lang|escape:'htmlall':'UTF-8'}" value="{if isset($smarty.post.{$varName|escape:'html':'UTF-8'})}{$smarty.post.{$varName|escape:'html':'UTF-8'}}{/if}" />
            </div>
        {/foreach}
    </div>
    <div class="clear"></div>
{else}
    {foreach $languages as $language}
        {if $languages|count > 1}
        <div class="translatable-field lang-{$language['id_lang']|escape:'htmlall':'UTF-8'}" style="display: {if $language['id_lang'] == $default_form_language}block{else}none{/if};">
        {/if}
            <div class="col-lg-9">
                <div class="form-group">
                    <div class="col-sm-6">
                        <input id="{$id_image|escape:'html':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}" type="file" name="{$id_image|escape:'html':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}"" class="hide" />
                        <div class="dummyfile input-group">
                            <span class="input-group-addon"><i class="icon-file"></i></span>
                            <input id="{$id_image|escape:'html':'UTF-8'}-name_{$language.id_lang|escape:'htmlall':'UTF-8'}" type="text" readonly="">
                            <span class="input-group-btn">
                                <button id="{$id_image|escape:'html':'UTF-8'}-selectbutton_{$language.id_lang|escape:'htmlall':'UTF-8'}" type="button" name="submitAddAttachments" class="btn btn-default">
                                    <i class="icon-folder-open"></i> {if isset($multiple) && $multiple}{l s='Add files' mod='advancedpopupcreator'}{else}{l s='Add file' mod='advancedpopupcreator'}{/if}
                                </button>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <script type="text/javascript">
                $(document).ready(function(){
                    $('#{$id_image|escape:'html':'UTF-8'}-selectbutton_{$language.id_lang|escape:'htmlall':'UTF-8'}').click(function(e) {
                        $('#{$id_image|escape:'html':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}').trigger('click');
                    });

                    $('#{$id_image|escape:'html':'UTF-8'}-name_{$language.id_lang|escape:'htmlall':'UTF-8'}').click(function(e) {
                        $('#{$id_image|escape:'html':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}').trigger('click');
                    });

                    $('#{$id_image|escape:'html':'UTF-8'}-name_{$language.id_lang|escape:'htmlall':'UTF-8'}').on('dragenter', function(e) {
                        e.stopPropagation();
                        e.preventDefault();
                    });

                    $('#{$id_image|escape:'html':'UTF-8'}-name_{$language.id_lang|escape:'htmlall':'UTF-8'}').on('dragover', function(e) {
                        e.stopPropagation();
                        e.preventDefault();
                    });

                    $('#{$id_image|escape:'html':'UTF-8'}-name_{$language.id_lang|escape:'htmlall':'UTF-8'}').on('drop', function(e) {
                        e.preventDefault();
                        var files = e.originalEvent.dataTransfer.files;
                        $('#{$id_image|escape:'html':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}')[0].files = files;
                        $(this).val(files[0].name);
                    });

                    $('#{$id_image|escape:'html':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}').change(function(e) {
                        if ($(this)[0].files !== undefined)
                        {
                            var files = $(this)[0].files;
                            var name  = '';

                            $.each(files, function(index, value) {
                                name += value.name+', ';
                            });

                            $('#{$id_image|escape:'html':'UTF-8'}-name_{$language.id_lang|escape:'htmlall':'UTF-8'}').val(name.slice(0, -2));
                        }
                        else // Internet Explorer 9 Compatibility
                        {
                            var name = $(this).val().split(/[\\/]/);
                            $('#{$id_image|escape:'html':'UTF-8'}-name_{$language.id_lang|escape:'htmlall':'UTF-8'}').val(name[name.length-1]);
                        }
                    });
                });
            </script>
        {if $languages|count > 1}
        <div class="col-lg-2">
            <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                {$language['iso_code']|escape:'htmlall':'UTF-8'}
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                {foreach $languages as $language}
                <li><a href="javascript:hideOtherLanguage({$language['id_lang']|escape:'htmlall':'UTF-8'});" tabindex="-1">{$language['name']|escape:'htmlall':'UTF-8'}</a></li>
                {/foreach}
            </ul>
        </div>
    </div>
    {/if}
    {/foreach}
{/if}

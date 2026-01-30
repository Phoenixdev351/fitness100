{*
 * 2010-2022 Bl Modules.
 *
 * If you wish to customize this module for your needs,
 * please contact the authors first for more information.
 *
 * It's not allowed selling, reselling or other ways to share
 * this file or any other module files without author permission.
 *
 * @author    Bl Modules
 * @copyright 2010-2022 Bl Modules
 * @license
*}
    <div class="panel">
        <div class="panel-heading">
            <i class="icon-cog"></i> {l s='XML Feed fields' mod='xmlfeeds'}
        </div>
        <div class="row">
            {{$inputsHtml}}
            <br/>
            <input type="hidden" name="feeds_id" value="{$page|escape:'htmlall':'UTF-8'}" />
            <input type="hidden" name="is_order_feed" value="1" />
            <div style="text-align: center;"><input type="submit" name="settings_cat" value="{l s='Update' mod='xmlfeeds'}" class="btn btn-primary" /></div>
        </div>
    </div>
</form>
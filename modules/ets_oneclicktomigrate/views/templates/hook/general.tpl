{*
* 2007-2019 ETS-Soft ETS-Soft
*
* NOTICE OF LICENSE
*
* This file is not open source! Each license that you purchased is only available for 1 wesite only.
* If you want to use this file on more websites (or projects), you need to purchase additional licenses.
* You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please, contact us for extra customization service at an affordable price
*
*  @author ETS-Soft <etssoft.jsc@gmail.com>
*  @copyright  2007-2019 ETS-Soft ETS-Soft
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of ETS-Soft
*}
<div class="dtm-left-block">
    {hook h='datamasterLeftBlok'}
</div>
<div class="dtm-right-block">
    <div id="data-master">
        <h2>{l s='Welcome to One Click To Migrate' mod='ets_oneclicktomigrate'}</h2>
        <p>{l s='One Click To Migrate is #1 Prestashop module you MUST have when you need to migrate Prestashop to latest version, migrate data between Prestashop websites, perform complete data backup, import or export entire website data' mod='ets_oneclicktomigrate'}</p>
        <div class="export">
            <a href="{$link->getAdminLink('AdminDataMasterExport')|escape:'html':'UTF-8'}">{l s='Export data' mod='ets_oneclicktomigrate'}</a>
            <ul>
                <li>{l s='Export data from source website for migration' mod='ets_oneclicktomigrate'}</li>
                <li>{l s='Create complete backup of website data' mod='ets_oneclicktomigrate'}</li>
                <li>{l s='Export data to XML' mod='ets_oneclicktomigrate'}</li>
                <li>{l s='Export data to use for any purpose' mod='ets_oneclicktomigrate'}</li>
            </ul>
        </div>
        <div class="import">
            <a href="{$link->getAdminLink('AdminDataMasterImport')|escape:'html':'UTF-8'}">{l s='Import data' mod='ets_oneclicktomigrate'}</a>
            <ul>
                <li>{l s='Import data into target website for migration' mod='ets_oneclicktomigrate'}</li>
                <li>{l s='Restore backup data' mod='ets_oneclicktomigrate'}</li>
                <li>{l s='Bulk data upload (Products, Categories, Manufacturers, Carriers...)' mod='ets_oneclicktomigrate'}</li>
                <li>{l s='Import data from a third party platform (valid data format required)' mod='ets_oneclicktomigrate'}</li>
            </ul>
        </div>
    </div>
</div>
<div class="dtm-clearfix"></div>
{*
* Affiliates
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
*
* @author    FMM Modules
* @copyright © Copyright 2021 - All right reserved
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* @category  FMM Modules
* @package   affiliates
*}

<script type="text/javascript" src="{$smarty.const.__PS_BASE_URI__|escape:'htmlall':'UTF-8'}js/jquery/plugins/jquery.colorpicker.js"></script>
<script type="text/javascript">
var file_not_found = '';
</script>
<div class="affiliates_shop_container">
    <div class="col-lg-2 " id="affiliates-program">
        <div class="panel">
            <center>
                <a href="https://addons.prestashop.com/en/139_fme-modules" target="_blank" title="FME Modules Catalog">
                    <img src="{$module_path|escape:'htmlall':'UTF-8'}img/fme_logo.png">
                </a>
            </center>
            <div class="panel-footer clearfix">
                <strong>
                    <center>
                        <a href="https://addons.prestashop.com/en/139_fme-modules" target="_blank">FME Modules</a>
                    </center>
                </strong>
            </div>
        </div>
    </div>
    <!-- Tab Content -->
    {include file="./form_content.tpl"}
   <div class="clearfix"></div>
</div>
<br></br>
<div class="clearfix"></div>
{literal}
<style type="text/css">
/*== PS 1.6 ==*/
 #affiliates-program ul.tab { list-style:none; padding:0; margin:0}

 #affiliates-program ul.tab li a {background-color: white;border: 1px solid #DDDDDD;display: block;margin-bottom: -1px;padding: 10px 15px;}
 #affiliates-program ul.tab li a { display:block; color:#555555; text-decoration:none}
 #affiliates-program ul.tab li a.selected { color:#fff; background:#00AFF0}

 #affiliates_toolbar { clear:both; padding-top:20px; overflow:hidden}

 #affiliates_toolbar .pageTitle { min-height:90px}

 #affiliates_toolbar ul { list-style:none; float:right}

 #affiliates_toolbar ul li { display:inline-block; margin-right:10px}

 #affiliates_toolbar ul li .toolbar_btn {background-color: white;border: 1px solid #CCCCCC;color: #555555;-moz-user-select: none;background-image: none;border-radius: 3px 3px 3px 3px;cursor: pointer;display: inline-block;font-size: 12px;font-weight: normal;line-height: 1.42857;margin-bottom: 0;padding: 8px 8px;text-align: center;vertical-align: middle;white-space: nowrap; }

 #affiliates_toolbar ul li .toolbar_btn:hover { background-color:#00AFF0 !important; color:#fff;}

 #affiliates_form .language_flags { display:none}
 form#affiliates_form {
    background-color: #ebedf4;
    border: 1px solid #ccced7;
    /*min-height: 404px;*/
    padding: 5px 10px 10px;
}
</style>
{/literal}
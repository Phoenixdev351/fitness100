{**
* This file is part of the securitypro package.
*
* @author Mathias Reker
* @copyright Mathias Reker
* @license Commercial Software License
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}

<!DOCTYPE html>
<html lang="{$sp_lang|escape:'htmlall':'UTF-8'}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{$sp_shopName|escape:'htmlall':'UTF-8'} {l s='is secured by Security Pro' mod='securitypro'}</title>
        <link rel="icon" type="image/vnd.microsoft.icon" href="{$sp_faviconLink|escape:'htmlall':'UTF-8'}">
        <link rel="shortcut icon" type="image/x-icon" href="{$sp_faviconLink|escape:'htmlall':'UTF-8'}">
        
        {literal}<style>*{margin: 0; padding: 0; box-sizing: border-box; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; text-rendering: optimizeLegibility; user-select: none;}body{font-family: "Roboto", Helvetica, Arial, sans-serif; font-size: 14px; line-height: 24px; color: #191919; background: #eff1f2;}.container{max-width: 450px; width: 100%; margin: 0 auto; position: relative;}#securityForm{background: #fcfcfc; padding: 25px 40px 25px 40px; margin: 120px 0; box-shadow: 0 0 20px 0 rgba(0, 0, 0, 0.2), 0 5px 5px 0 rgba(0, 0, 0, 0.24); border-radius: 2px;}#securityForm h3{display: block; font-size: 30px; font-weight: 300; margin-bottom: 10px;}.text-center{text-align: center;}.g-recaptcha{display: inline-block;}#main{width: 100%; position: relative;}#left{width: 15%; position: absolute; vertical-align: top; display: inline-b lock; box-sizing: border-box; -moz-box-sizing: border-box; -webkit-box-sizing: border-box;}#left img{width: 100%;}#right{padding-left: 70px; padding-top: 6px; display: inline-block; vertical-align: top; box-sizing: border-box; -moz-box-sizing: border-box; -webkit-box-sizing: border-box; font-size: 18px; line-height: 28px;}.confirm{padding-top: 8px; padding-bottom: 8px;}hr{display: block; height: 1px; border: 0; border-top: 1px solid #ccc; margin: 1.3em 0; padding: 0;}</style>{/literal}

        <script src="https://www.google.com/recaptcha/api.js?hl={$sp_lang|escape:'htmlall':'UTF-8'}" async defer></script>
    </head>
    <body>
        <div class="container">
            <form id="securityForm" method="post" name="securityForm">
                <div id="main">
                    <div id="left"><img src="{$sp_imgPath|escape:'htmlall':'UTF-8'}views/img/logo.png" alt=""></div>
                    <div id="right"><strong>{$sp_shopName|escape:'htmlall':'UTF-8'}</strong><br>{l s='is secured by Security Pro' mod='securitypro'}</div>
                </div>
                <hr>
                <div class="text-center">
                    <p>{l s='We detected unusual activity from your' mod='securitypro'} <strong>{l s='IP' mod='securitypro'} {$sp_ip|escape:'htmlall':'UTF-8'}</strong> {l s='and has blocked access to this website.' mod='securitypro'}</p>
                    <p class="confirm">{l s='Please confirm that you are not a robot.' mod='securitypro'}</p>
                    <div id="target" class="g-recaptcha" data-sitekey="{$sp_siteKey|escape:'htmlall':'UTF-8'}" data-callback="submitForm"></div>
                </div>
                <input type="hidden" name="g-recaptcha-submit"> 
            </form>
        </div>
        {literal}<script>var submitForm=function(){document.securityForm.submit();}</script>{/literal} 
    </body>
</html>

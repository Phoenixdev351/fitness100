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

{literal}<style>.grecaptcha-badge{z-index: 1080;}</style>{/literal}

<script src="https://www.google.com/recaptcha/api.js?onload=onLoadCallbackSecurityPro&amp;render=explicit&amp;hl={$sp_lang}" async defer></script>

<script>function onLoadCallbackSecurityPro() {
    var clientId = grecaptcha.render('inline-badge', {
        'sitekey': '{$sp_siteKey}',
        'badge': '{$sp_display}',
        'size': 'invisible',
        'theme': '{$sp_theme}',
    });
    
    grecaptcha.ready(function() {
        grecaptcha.execute(clientId, {literal}{action: 'homepage'}{/literal}).then(function(token) {
            $('[name={$sp_submitButton}]').before($('<input type="hidden" name="g-token">').attr('value', token));
            setInterval(function() {
                grecaptcha.execute(clientId, {literal}{action: 'homepage'}{/literal}).then(function(token) {
                    $("[name='g-token']").attr('value', token);
                });
            }, 90000);
        });
    });
}</script>

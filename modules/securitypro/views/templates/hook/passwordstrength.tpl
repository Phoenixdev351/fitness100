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

<div id="meter">
    <meter max="4" id="password-strength-meter"></meter>
    <p id="password-strength-text"></p>
</div>
<style>
    #password-strength-meter[value="1"]::-webkit-meter-optimum-value,
    #password-strength-meter[value="1"]::-moz-meter-bar {
        background: #ff4500;
    }
    #password-strength-meter[value="2"]::-webkit-meter-optimum-value,
    #password-strength-meter[value="2"]::-moz-meter-bar {
        background: #ffa500;
    }
    #password-strength-meter[value="3"]::-webkit-meter-optimum-value,
    #password-strength-meter[value="3"]::-moz-meter-bar {
        background: #9acd32;
    }
    #password-strength-meter[value="4"]::-webkit-meter-optimum-value,
    #password-strength-meter[value="4"]::-moz-meter-bar {
        background: #008000;
    }
</style>
<script>
    var passwdStrengthConfig = {
        strength: {
            0: "{l s='too easy' mod='securitypro'}", 
            1: "{l s='still easy' mod='securitypro'}",
            2: "{l s='weak' mod='securitypro'}",
            3: "{l s='good' mod='securitypro'}",
            4: "{l s='strong' mod='securitypro'}"
        },
        displayText: true,
        text: "{l s='Password is' mod='securitypro'} %s"
    };
</script>

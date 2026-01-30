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

<div id="login-panel-2">
    <div id="login-header">
        <div id="shop-img2">
            <img src="{$baseUrl|escape:'htmlall':'UTF-8'}modules/securitypro/logo.png" alt="{$shopName|escape:'htmlall':'UTF-8'}" width="100px" height="100px" />
        </div>
    </div>
    <div>
        <div class="flipper">
            <div class="front front_login panel">
                <h4 id="shop_name">{$shopName|escape:'htmlall':'UTF-8'}</h4>
                <form action="" id="login_form" method="post">
                    <div class="form-group">
                        <label class="control-label" for="google">{l s='Two-Factor Authentication' mod='securitypro'}</label>
                        <input autocomplete="off" name="google" type="text" id="google" class="form-control" value="" tabindex="1" placeholder="&#xf084 {l s='Code' mod='securitypro'}" autofocus>
                    </div>
                    <div class="form-group row-padding-top">
                        <button name="submitNext" type="submit" tabindex="2" class="btn btn-primary btn-lg btn-block ladda-button" data-style="slide-up" data-spinner-color="white">
                            <span class="ladda-label">
                                {l s='Next step' mod='securitypro'}
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

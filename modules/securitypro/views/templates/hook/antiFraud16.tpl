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

<div class="panel">
    <div class="panel-heading">
        <i class="icon-shield"></i>
        {l s='Anti Fraud' mod='securitypro'} <span class="badge">{$sp_score|escape:'htmlall':'UTF-8'}</span>
    </div>
    <div class="row">
        <div class="col-md-{$sp_column|escape:'htmlall':'UTF-8'}">
            <p>
                <strong>{l s='Client IP' mod='securitypro'}:</strong>
            </p>
            <p>
                {$sp_ip|escape:'htmlall':'UTF-8'}
            </p>
        </div>
        <div class="col-md-{$sp_column|escape:'htmlall':'UTF-8'}">
            <p>
                <strong>{l s='IP Location' mod='securitypro'}:</strong>
            </p>
            <p>
                {$sp_ipCountry|escape:'htmlall':'UTF-8'}
            </p>
        </div>
        <div class="col-md-{$sp_column|escape:'htmlall':'UTF-8'}">
            <p>
                <strong>{l s='Disposable e-mail provider' mod='securitypro'}:</strong>
            </p>
            <p>
                {$sp_validateEmail|escape:'htmlall':'UTF-8'}
            </p>
        </div>
        <div class="col-md-{$sp_column|escape:'htmlall':'UTF-8'}">
            <p>
                <strong>{l s='Using proxy' mod='securitypro'}:</strong>
            </p>
            <p>
                {$sp_isProxy|escape:'htmlall':'UTF-8'}
            </p>
        </div>
        <div class="col-md-{$sp_column|escape:'htmlall':'UTF-8'}">
            <p>
                <strong>{l s='Platform' mod='securitypro'}:</strong>
            </p>
            <p>
                {$sp_os|escape:'htmlall':'UTF-8'}
            </p>
        </div>
        <div class="col-md-{$sp_column|escape:'htmlall':'UTF-8'}">
            <p>
                <strong>{l s='Browser' mod='securitypro'}:</strong>
            </p>
            <p>
                {$sp_browser|escape:'htmlall':'UTF-8'}
            </p>
        </div>
        <div class="col-md-{$sp_column|escape:'htmlall':'UTF-8'}">
            <p>
                <strong>{l s='Device' mod='securitypro'}:</strong>
            </p>
            <p>
                {$sp_type|escape:'htmlall':'UTF-8'}
            </p>
        </div>
        <div class="col-md-{$sp_column|escape:'htmlall':'UTF-8'}">
            <p>
                <strong>{l s='Blacklisted IP' mod='securitypro'}:</strong>
            </p>
            <p>
                {$sp_isBot|escape:'htmlall':'UTF-8'}
            </p>
        </div>
        <div class="col-md-{$sp_column|escape:'htmlall':'UTF-8'}">
            <p>
                <strong>{l s='TOR IP' mod='securitypro'}:</strong>
            </p>
            <p>
                {$sp_isTor|escape:'htmlall':'UTF-8'}
            </p>
        </div>
        <div class="col-md-{$sp_column|escape:'htmlall':'UTF-8'}">
            <p>
                <strong>{l s='Delivery to high risk country' mod='securitypro'}:</strong>
            </p>
            <p>
                {$sp_risky|escape:'htmlall':'UTF-8'}
            </p>
        </div>
        <div class="col-md-{$sp_column|escape:'htmlall':'UTF-8'}">
            <p>
                <strong>{l s='Distance between delivery and invoice' mod='securitypro'}:</strong>
            </p>
            <p>
                {$sp_distanceDeliveryInvoice|escape:'htmlall':'UTF-8'}
            </p>
        </div>
        <div class="col-md-{$sp_column|escape:'htmlall':'UTF-8'}">
            <p>
                <strong>{l s='Distance between delivery and IP' mod='securitypro'}:</strong>
            </p>
            <p>
                {$sp_distanceDeliveryIp|escape:'htmlall':'UTF-8'}
            </p>
        </div>
        <div class="col-md-{$sp_column|escape:'htmlall':'UTF-8'}">
            <p>
                <strong>{l s='Total orders from this IP address' mod='securitypro'}:</strong>
            </p>
            <p>
                {$sp_countOrders|escape:'htmlall':'UTF-8'}
            </p>
        </div>
        <div class="col-md-{$sp_column|escape:'htmlall':'UTF-8'}">
            <p>
                <strong>{l s='Other customers with the same IP address' mod='securitypro'}:</strong>
            </p>
            <p>
                {$sp_customersWithSameIp nofilter}
            </p>
        </div>
        <div class="col-md-{$sp_column|escape:'htmlall':'UTF-8'}">
            <p>
                <strong>{l s='Other orders with the same IP address' mod='securitypro'}:</strong>
            </p>
            <p>
                {$sp_ordersWithSameIP nofilter}
            </p>
        </div>
    </div>
</div>

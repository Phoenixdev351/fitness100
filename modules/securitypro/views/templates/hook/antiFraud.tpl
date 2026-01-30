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

<div class="customer card">
    <div class="card-header">
        <h3 class="card-header-title">
            {l s='Anti Fraud' mod='securitypro'} ({$sp_score})
        </h3>
    </div>
    <div class="card-body">
        <div class="row mt-3">
            <div class="col-md-{$sp_column}">
                <p class="mb-1">
                    <strong>{l s='Client IP' mod='securitypro'}:</strong>
                </p>
                <p>
                    {$sp_ip}
                </p>
            </div>
            <div class="col-md-{$sp_column}">
                <p class="mb-1">
                    <strong>{l s='IP Location' mod='securitypro'}:</strong>
                </p>
                <p>
                    {$sp_ipCountry}
                </p>
            </div>
            <div class="col-md-{$sp_column}">
                <p class="mb-1">
                    <strong>{l s='Disposable e-mail provider' mod='securitypro'}:</strong>
                </p>
                <p>
                    {$sp_validateEmail}
                </p>
            </div>
            <div class="col-md-{$sp_column}">
                <p class="mb-1">
                    <strong>{l s='Using proxy' mod='securitypro'}:</strong>
                </p>
                <p>
                    {$sp_isProxy}
                </p>
            </div>
            <div class="col-md-{$sp_column}">
                <p class="mb-1">
                    <strong>{l s='Platform' mod='securitypro'}:</strong>
                </p>
                <p>
                    {$sp_os}
                </p>
            </div>
            <div class="col-md-{$sp_column}">
                <p class="mb-1">
                    <strong>{l s='Browser' mod='securitypro'}:</strong>
                </p>
                <p>
                    {$sp_browser}
                </p>
            </div>
            <div class="col-md-{$sp_column}">
                <p class="mb-1">
                    <strong>{l s='Device' mod='securitypro'}:</strong>
                </p>
                <p>
                    {$sp_type}
                </p>
            </div>
            <div class="col-md-{$sp_column}">
                <p class="mb-1">
                    <strong>{l s='Blacklisted IP' mod='securitypro'}:</strong>
                </p>
                <p>
                    {$sp_isBot}
                </p>
            </div>
            <div class="col-md-{$sp_column}">
                <p class="mb-1">
                    <strong>{l s='TOR IP' mod='securitypro'}:</strong>
                </p>
                <p>
                    {$sp_isTor}

                </p>
            </div>
            <div class="col-md-{$sp_column}">
                <p class="mb-1">
                    <strong>{l s='Delivery to high risk country' mod='securitypro'}:</strong>
                </p>
                <p>
                    {$sp_risky}
                </p>
            </div>
            <div class="col-md-{$sp_column}">
                <p class="mb-1">
                    <strong>{l s='Distance between delivery and invoice' mod='securitypro'}:</strong>
                </p>
                <p>
                    {$sp_distanceDeliveryInvoice}
                </p>
            </div>
            <div class="col-md-{$sp_column}">
                <p class="mb-1">
                    <strong>{l s='Distance between delivery and IP' mod='securitypro'}:</strong>
                </p>
                <p>
                    {$sp_distanceDeliveryIp}
                </p>
            </div>
            <div class="col-md-{$sp_column}">
                <p class="mb-1">
                    <strong>{l s='Total orders from this IP address' mod='securitypro'}:</strong>
                </p>
                <p>
                    {$sp_countOrders}
                </p>
            </div>
            <div class="col-md-{$sp_column}">
                <p class="mb-1">
                    <strong>{l s='Other customers with the same IP address' mod='securitypro'}:</strong>
                </p>
                <p>
                    {$sp_customersWithSameIp}
                </p>
            </div>
            <div class="col-md-{$sp_column}">
                <p class="mb-1">
                    <strong>{l s='Other orders with the same IP address' mod='securitypro'}:</strong>
                </p>
                <p>
                    {$sp_ordersWithSameIP}
                </p>
            </div>
        </div>
    </div>
</div>

<?php
if (!defined('_PS_VERSION_')) {
    exit;
}
function upgrade_module_4_2_0($module)
{
    upgradeConfigurationValue_4_2_0('SEQURA_INVOICE_NAME', 'Compra ahora, paga en 7 días');
    upgradeConfigurationValue_4_2_0('SEQURA_PARTPAYMENT_SERVICE_NAME', 'Pago fraccionado');
    return true;
}

function upgradeConfigurationValue_4_2_0($name, $value)
{
    if (_PS_VERSION_ < 1.5) { //For 1.4
        Configuration::updateValue($name, $value);
    } else {
        Configuration::updateGlobalValue($name, $value);
    }
}

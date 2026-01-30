<?php
if (!defined('_PS_VERSION_')) {
    exit;
}
function upgrade_module_4_4_0($module)
{
    upgradeConfigurationValue_4_4_0('SEQURA_PARTPAYMENT_SERVICE_NAME', 'Fracciona tu pago');
    return true;
}

function upgradeConfigurationValue_4_4_0($name, $value)
{
    if (_PS_VERSION_ < 1.5) { //For 1.4
        Configuration::updateValue($name, $value);
    } else {
        Configuration::updateGlobalValue($name, $value);
    }
}

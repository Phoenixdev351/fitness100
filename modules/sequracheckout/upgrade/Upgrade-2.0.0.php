<?php
if (!defined('_PS_VERSION_')) {
    exit;
}
function upgrade_module_2_0_0($module)
{
    /*Just make sure all config variables needed are set*/
    $installer = new SequraInstaller($module);
    $installer->initConfigurationValue( 'SEQURA_PAYMENT_METHODS_ES', Configuration::get('SEQURA_PAYMENT_METHODS'));
    return true;
}

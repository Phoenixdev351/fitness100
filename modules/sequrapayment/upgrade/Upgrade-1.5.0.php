<?php
if (!defined('_PS_VERSION_')) {
    exit;
}
function upgrade_module_1_5_0($module)
{
    /*Just make sure all config variables needed are set*/
    $installer = new SequraInstaller($module);
    if ( _PS_VERSION_ < 1.5 ) {
        $installer->createProductDetailsCustomHook();
    }
    $installer->initConfigurationValue( 'SEQURA_PP_ACTIVE', '0' );
    if ( ! $module->registerHook( 'sequraPartPaymentProductDetails' ) ) {
        return ( false );
    }

    return true;
}
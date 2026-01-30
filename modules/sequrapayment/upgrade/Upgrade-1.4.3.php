<?php
if (!defined('_PS_VERSION_')) {
    exit;
}
function upgrade_module_1_4_3($object)
{
    /*Just make sure all config variables needed are set*/
    $installer = new SequraInstaller(new Sequrapayment());
    $installer->initConfigurationValue( 'SEQURA_AUTOCRON', 1 );
    $installer->initConfigurationValue( 'SEQURA_AUTOCRON_H', round( rand( 2, 8 ) ) );
    $installer->initConfigurationValue( 'SEQURA_AUTOCRON_M', round( rand( 0, 59 ) ) );
    $installer->initConfigurationValue( 'SEQURA_AUTOCRON_NEXT', SequraCrontab::calcNextExecutionTime() );
    $installer->initConfigurationValue( 'SEQURA_STATS_ALLOW', 'S' );
    $installer->initConfigurationValue( 'SEQURA_STATS_AMOUNT', 'S' );
    $installer->initConfigurationValue( 'SEQURA_STATS_COUNTRIES', 'S' );
    $installer->initConfigurationValue( 'SEQURA_STATS_PAYMENTMETHOD', 'S' );
    $installer->initConfigurationValue( 'SEQURA_STATS_STATUS', 'S' );

    return true;
}
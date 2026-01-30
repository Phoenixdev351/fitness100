<?php
if (!defined('_PS_VERSION_')) {
    exit;
}
function upgrade_module_4_0_0($module)
{
    /*Just make sure all config variables needed are set*/
    $installer = new SequraInstaller($module);
    $sequra_methods = array(
        'sequrainovice' =>'i1',
        'sequrapartpayment' => 'pp3',
        'sequracampaign' => 'pp5'
    );
    $active_methods = array();
    foreach ($sequra_methods as $key => $value){
        if(SequraTools::isModuleActive($value)){
            $active_methods[$key]=$value;
        }
    }
    $installer->initConfigurationValue( 'SEQURA_ACTIVE_METHODS', serialize($active_methods) );
    $installer->initConfigurationValue( 'SEQURA_LIVE_SCRIPT_BASE_URI', 'https://live.sequracdn.com/assets/');
    $installer->initConfigurationValue( 'SEQURA_SANDBOX_SCRIPT_BASE_URI', 'https://sandbox.sequracdn.com/assets/');
    $installer->initConfigurationValue( 'SEQURA_CSS_SEL_PRICE', Configuration::get('SEQURA_PARTPAYMENT_CSS_SEL_PRICE'));
    if(strpos(Configuration::get('SEQURA_ENDPOINT'),'live.')){
        Configuration::updateValue('SEQURA_MODE','live');
    }
    if ( preg_match( '/scripts\/' . Configuration::get('SEQURA_MERCHANT_ID') . '\/([^\/]*)\/.*/', Configuration::get('SEQURA_PP_COST_URL'), $m ) ) {
        Configuration::updateValue('SEQURA_ASSETS_KEY',$m[1]);
    }
    return true;
}

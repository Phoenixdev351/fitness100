<?php
if (!defined('_PS_VERSION_')) {
    exit;
}
function upgrade_module_5_0_0($module)
{
    /*Just make sure all config variables needed are set*/
    $installer = new SequraInstaller($module);
    $installer->initConfigurationValue( 'SEQURA_MERCHANT_ID_ES', Configuration::get('SEQURA_MERCHANT_ID'));
    $db = Db::getInstance();
    $res = true;
    
    $results = $db->executeS(
        'SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'sequra_order` LIKE \'merchant_id\''
    );
    if (!$results) {
        $res = $res && (bool)$db->execute(
            'ALTER TABLE `' . _DB_PREFIX_ . 'sequra_order`
            ADD `merchant_id` varchar(64) NOT NULL;'
        );
    }
    $res = $res && (bool)$db->execute(
        'DELETE FROM `' . _DB_PREFIX_ . 'module_country`
        WHERE id_module IN (SELECT id_module FROM ps_module WHERE name="sequracheckout")
         AND  id_country NOT IN (SELECT id_country FROM ps_country WHERE iso_code = "ES")'
    );
    return $res;
}

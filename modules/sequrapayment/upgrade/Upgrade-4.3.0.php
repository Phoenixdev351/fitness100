<?php
if (!defined('_PS_VERSION_')) {
    exit;
}
function upgrade_module_4_3_0($module)
{
    $db = Db::getInstance();
    $res = true;
    
    $results = $db->executeS(
        'SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'product` LIKE \'sequra_service_end_date\''
    );
    if (!$results) {
        $res = $res && (bool)$db->execute(
            'ALTER TABLE `' . _DB_PREFIX_ . 'product`
            ADD COLUMN `sequra_service_end_date` VARCHAR(16) NULL;'
        );
    }

    $results = $db->executeS(
        'SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'product` LIKE \'sequra_is_service\''
    );
    if (!$results) {
        $res = $res && (bool)$db->execute(
            'ALTER TABLE `' . _DB_PREFIX_ . 'product`
            ADD COLUMN `sequra_is_service` BOOLEAN NOT NULL DEFAULT TRUE;'
        );
    } else {
        $res = $res && (bool)$db->execute(
            'ALTER TABLE `' . _DB_PREFIX_ . 'product`
            CHANGE `sequra_is_service` `sequra_is_service` BOOLEAN NOT NULL DEFAULT TRUE;'
        );
    }
    return $res;
}

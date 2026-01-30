<?php
if (!defined('_PS_VERSION_')) {
    exit;
}
function upgrade_module_4_5_0($module)
{
    $db = Db::getInstance();
    $results = $db->executeS(
        'SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'product` LIKE \'sequra_desired_first_charge_date\''
    );
    $res = true;
    if (!$results) {
        $res = $res && (bool)$db->execute(
            'ALTER TABLE `' . _DB_PREFIX_ . 'product`
            ADD COLUMN `sequra_desired_first_charge_date` VARCHAR(16) NULL;'
        );
    }

    $results = $db->executeS(
        'SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'product` LIKE \'sequra_registration_amount\''
    );
    if (!$results) {
        $res = $res && (bool)$db->execute(
            'ALTER TABLE `' . _DB_PREFIX_ . 'product`
            ADD COLUMN `sequra_registration_amount` decimal(20,6) NULL DEFAULT 0;'
        );
    }
    return $res;
}

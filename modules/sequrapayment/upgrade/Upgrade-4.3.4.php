<?php
if (!defined('_PS_VERSION_')) {
    exit;
}
function upgrade_module_4_3_4($module)
{
    $db = Db::getInstance();
    return (bool)$db->execute(
        'ALTER TABLE `' . _DB_PREFIX_ . 'product`
        ADD sequra_is_banned BOOLEAN NOT NULL DEFAULT FALSE;'
    ) &&
    $module->registerHook('displayAdminProductsExtra') &&
    $module->registerHook('actionProductUpdate');
}

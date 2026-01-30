<?php

require(dirname(__FILE__) . '/../../config/config.inc.php');
include_once(_PS_ROOT_DIR_ . '/init.php');
if ($method = Tools::getValue('method', false)) {
    Module::getInstanceByName($method)->confirmOrderFromIpn();
}

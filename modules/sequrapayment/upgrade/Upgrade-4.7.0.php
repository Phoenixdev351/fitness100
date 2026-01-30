<?php
/*
*  @author SeQura <info@sequra.es>
*  @copyright  SeQura Worldwide S.L.
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/
if (!defined('_PS_VERSION_')) {
    exit;
}
function upgrade_module_4_7_0($module)
{
    //Create pending state, using install method to make sure it is created the same.
    $installer = new SequraInstaller($module);
    return $installer->install();
}

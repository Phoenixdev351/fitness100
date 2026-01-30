<?php
if (!defined('_PS_VERSION_')) { exit; }
class AdminSuppliersController extends AdminSuppliersControllerCore
{
    /*
    * module: ets_superspeed
    * date: 2025-03-17 16:01:24
    * version: 2.0.3
    */
    protected function afterImageUpload()
    {
        parent::afterImageUpload();
        if(Module::isInstalled('ets_superspeed') && Module::isEnabled('ets_superspeed'))
        {
            $id_supplier = (int)Tools::getValue('id_supplier');
            Ets_superspeed_compressor_image::optimizeImageSupplier($id_supplier);
        }
    }
}